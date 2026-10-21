<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccountLedgerController extends Controller
{
    /**
     * Resolve Date Range Filters based on input
     */
    protected function resolveDateRange(Request $request): array
    {
        $dateFilter = $request->input('date_filter', 'this_month');
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfDay();

        switch ($dateFilter) {
            case 'today':
                $start = Carbon::today()->startOfDay();
                $end   = Carbon::today()->endOfDay();
                break;
            case 'yesterday':
                $start = Carbon::yesterday()->startOfDay();
                $end   = Carbon::yesterday()->endOfDay();
                break;
            case 'this_week':
                $start = Carbon::now()->startOfWeek();
                $end   = Carbon::now()->endOfWeek();
                break;
            case 'last_month':
                $start = Carbon::now()->subMonth()->startOfMonth();
                $end   = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'this_year':
                $start = Carbon::now()->startOfYear();
                $end   = Carbon::now()->endOfYear();
                break;
            case 'all':
                $start = Carbon::parse('2020-01-01')->startOfDay();
                $end   = Carbon::now()->endOfDay();
                break;
            case 'custom':
                if ($request->filled('start_date')) {
                    $start = Carbon::parse($request->start_date)->startOfDay();
                }
                if ($request->filled('end_date')) {
                    $end = Carbon::parse($request->end_date)->endOfDay();
                }
                break;
            case 'this_month':
            default:
                $start = Carbon::now()->startOfMonth();
                $end   = Carbon::now()->endOfDay();
                break;
        }

        return [$start, $end, $dateFilter];
    }

    /**
     * Find existing account foreign key column in a given table
     */
    protected function resolveAccountForeignKey(string $table): ?string
    {
        if (!Schema::hasTable($table)) {
            return null;
        }

        if (Schema::hasColumn($table, 'bank_account_id')) {
            return 'bank_account_id';
        }
        if (Schema::hasColumn($table, 'account_id')) {
            return 'account_id';
        }
        if (Schema::hasColumn($table, 'bank_id')) {
            return 'bank_id';
        }

        return null;
    }

    /**
     * Unified Cashbook & General Account Ledger
     */
    public function index(Request $request)
    {
        [$start, $end, $dateFilter] = $this->resolveDateRange($request);

        // 1. Load active bank/cash accounts for filter dropdown
        $bankAccounts = collect();
        if (Schema::hasTable('bank_accounts')) {
            $bankAccounts = DB::table('bank_accounts')
                ->where('is_active', true)
                ->orderBy('account_name')
                ->get();
        }

        // 2. Fetch or construct unified ledger records
        $transactionsQuery = $this->buildUnifiedLedgerQuery($start, $end, $request);
        $allRecords = $transactionsQuery->sortByDesc('transaction_date')->values();

        // 3. Compute KPI Summary Metrics
        $totalCredits = (float) $allRecords->where('type', 'credit')->sum('amount');
        $totalDebits  = (float) $allRecords->where('type', 'debit')->sum('amount');
        $netMovement  = $totalCredits - $totalDebits;

        $totalClosingBalance = Schema::hasTable('bank_accounts')
            ? (float) DB::table('bank_accounts')->where('is_active', true)->sum('current_balance')
            : 0.00;

        // 4. Manual Pagination for Combined Collections
        $perPage = 25;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $allRecords->slice(($currentPage - 1) * $perPage, $perPage)->all();

        $paginatedRecords = new LengthAwarePaginator(
            $currentItems,
            $allRecords->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('admin.accounts.ledger.index', compact(
            'paginatedRecords',
            'bankAccounts',
            'totalCredits',
            'totalDebits',
            'netMovement',
            'totalClosingBalance',
            'dateFilter',
            'start',
            'end'
        ));
    }

    /**
     * Build unified ledger data across bank_transactions or multi-module fallbacks
     */
    protected function buildUnifiedLedgerQuery(Carbon $start, Carbon $end, Request $request)
    {
        $selectedAccount = $request->input('bank_account_id', 'all');
        $selectedType = $request->input('transaction_type', 'all');
        $search = trim($request->input('search', ''));

        // Primary: bank_transactions table
        if (Schema::hasTable('bank_transactions')) {
            $dateCol = Schema::hasColumn('bank_transactions', 'transaction_date') 
                ? 'transaction_date' 
                : (Schema::hasColumn('bank_transactions', 'date') ? 'date' : 'created_at');

            $typeCol = Schema::hasColumn('bank_transactions', 'transaction_type') 
                ? 'transaction_type' 
                : (Schema::hasColumn('bank_transactions', 'type') ? 'type' : null);

            $hasFromAcc = Schema::hasColumn('bank_transactions', 'from_account_id');
            $hasToAcc   = Schema::hasColumn('bank_transactions', 'to_account_id');
            $accForeignCol = $this->resolveAccountForeignKey('bank_transactions');

            $query = DB::table('bank_transactions');

            // Join bank_accounts based on to_account, from_account or direct foreign key
            $hasJoinedAccounts = false;
            if (Schema::hasTable('bank_accounts')) {
                if ($hasToAcc) {
                    $query->leftJoin('bank_accounts as to_acc', 'bank_transactions.to_account_id', '=', 'to_acc.id');
                    $hasJoinedAccounts = true;
                }
                if ($hasFromAcc) {
                    $query->leftJoin('bank_accounts as from_acc', 'bank_transactions.from_account_id', '=', 'from_acc.id');
                    $hasJoinedAccounts = true;
                }
                if ($accForeignCol && !$hasToAcc && !$hasFromAcc) {
                    $query->leftJoin('bank_accounts', "bank_transactions.{$accForeignCol}", '=', 'bank_accounts.id');
                    $hasJoinedAccounts = true;
                }
            }

            $query->whereBetween("bank_transactions.{$dateCol}", [$start, $end]);

            // Account Filter
            if ($selectedAccount !== 'all' && !empty($selectedAccount)) {
                $accId = (int)$selectedAccount;
                $query->where(function ($sq) use ($accId, $hasFromAcc, $hasToAcc, $accForeignCol) {
                    $hasSubWhere = false;
                    if ($hasFromAcc) {
                        $sq->where('bank_transactions.from_account_id', $accId);
                        $hasSubWhere = true;
                    }
                    if ($hasToAcc) {
                        if ($hasSubWhere) {
                            $sq->orWhere('bank_transactions.to_account_id', $accId);
                        } else {
                            $sq->where('bank_transactions.to_account_id', $accId);
                            $hasSubWhere = true;
                        }
                    }
                    if ($accForeignCol) {
                        if ($hasSubWhere) {
                            $sq->orWhere("bank_transactions.{$accForeignCol}", $accId);
                        } else {
                            $sq->where("bank_transactions.{$accForeignCol}", $accId);
                        }
                    }
                });
            }

            // Transaction Type Filter (Credit / Debit)
            if ($selectedType !== 'all' && !empty($selectedType) && $typeCol) {
                if ($selectedType === 'credit') {
                    $query->whereIn("bank_transactions.{$typeCol}", ['credit', 'deposit', 'inflow', 'investment', 'income']);
                } elseif ($selectedType === 'debit') {
                    $query->whereIn("bank_transactions.{$typeCol}", ['debit', 'withdraw', 'outflow', 'expense', 'asset']);
                }
            }

            // Search
            if (!empty($search)) {
                $query->where(function ($sq) use ($search, $hasJoinedAccounts, $hasToAcc, $hasFromAcc) {
                    if (Schema::hasColumn('bank_transactions', 'note')) {
                        $sq->where('bank_transactions.note', 'LIKE', "%{$search}%");
                    }
                    if (Schema::hasColumn('bank_transactions', 'reference_no')) {
                        $sq->orWhere('bank_transactions.reference_no', 'LIKE', "%{$search}%");
                    }
                    if ($hasJoinedAccounts) {
                        if ($hasToAcc) {
                            $sq->orWhere('to_acc.account_name', 'LIKE', "%{$search}%");
                        }
                        if ($hasFromAcc) {
                            $sq->orWhere('from_acc.account_name', 'LIKE', "%{$search}%");
                        }
                        if (Schema::hasTable('bank_accounts')) {
                            $sq->orWhere('bank_accounts.account_name', 'LIKE', "%{$search}%");
                        }
                    }
                });
            }

            $selects = [
                'bank_transactions.id',
                "bank_transactions.{$dateCol} as transaction_date",
                DB::raw($typeCol ? "bank_transactions.{$typeCol} as raw_type" : "'credit' as raw_type"),
                DB::raw(Schema::hasColumn('bank_transactions', 'amount') ? 'bank_transactions.amount' : '0 as amount'),
                DB::raw(Schema::hasColumn('bank_transactions', 'balance_after') ? 'bank_transactions.balance_after' : '0 as balance_after'),
                DB::raw(Schema::hasColumn('bank_transactions', 'note') ? 'bank_transactions.note' : "'' as note"),
                DB::raw(Schema::hasColumn('bank_transactions', 'reference_type') ? 'bank_transactions.reference_type' : "COALESCE(bank_transactions.reference_no, 'Direct') as reference_type"),
            ];

            // Resolve Account Name and Type Dynamically
            if ($hasToAcc && $hasFromAcc) {
                $selects[] = DB::raw("COALESCE(to_acc.account_name, from_acc.account_name, 'Cash / Vault') as account_name");
                $selects[] = DB::raw("COALESCE(to_acc.account_type, from_acc.account_type, 'Cash') as account_type");
                $selects[] = 'bank_transactions.from_account_id';
                $selects[] = 'bank_transactions.to_account_id';
            } elseif ($hasJoinedAccounts && Schema::hasTable('bank_accounts')) {
                $selects[] = DB::raw("COALESCE(bank_accounts.account_name, 'Cash / Vault') as account_name");
                $selects[] = DB::raw("COALESCE(bank_accounts.account_type, 'Cash') as account_type");
            } else {
                $selects[] = DB::raw("'Cash / Vault' as account_name");
                $selects[] = DB::raw("'Cash' as account_type");
            }

            $rawRecords = $query->select($selects)->get();

            if ($rawRecords->isNotEmpty()) {
                return $rawRecords->map(function ($row) use ($selectedAccount) {
                    $t = strtolower($row->raw_type ?? 'credit');
                    
                    // Determine Credit vs Debit
                    if (in_array($t, ['credit', 'deposit', 'inflow', 'investment', 'income'])) {
                        $row->type = 'credit';
                    } elseif (in_array($t, ['debit', 'withdraw', 'outflow', 'expense', 'asset'])) {
                        $row->type = 'debit';
                    } elseif ($t === 'transfer') {
                        // If internal transfer, evaluate debit vs credit based on the filtered account
                        if ($selectedAccount !== 'all' && isset($row->from_account_id) && (int)$row->from_account_id === (int)$selectedAccount) {
                            $row->type = 'debit';
                        } else {
                            $row->type = 'credit';
                        }
                    } else {
                        $row->type = 'credit';
                    }
                    return $row;
                });
            }
        }

        // Secondary / Fallback: Dynamic Union across operational financial tables
        return $this->buildFallbackMultiModuleLedger($start, $end, $selectedAccount, $selectedType, $search);
    }

    /**
     * Fail-safe multi-module transaction harvester (Incomes, Expenses, Investments, Assets)
     */
    protected function buildFallbackMultiModuleLedger(Carbon $start, Carbon $end, $selectedAccount, $selectedType, $search)
    {
        $union = collect();

        // 1. Incomes (Credit)
        if (Schema::hasTable('incomes') && ($selectedType === 'all' || $selectedType === 'credit')) {
            $dateCol = Schema::hasColumn('incomes', 'income_date') ? 'income_date' : 'created_at';
            $accCol = $this->resolveAccountForeignKey('incomes');

            $q = DB::table('incomes');
            $hasJoin = false;
            if ($accCol && Schema::hasTable('bank_accounts')) {
                $q->leftJoin('bank_accounts', "incomes.{$accCol}", '=', 'bank_accounts.id');
                $hasJoin = true;
            }

            $q->whereBetween("incomes.{$dateCol}", [$start->toDateString(), $end->toDateString()]);

            if ($selectedAccount !== 'all' && !empty($selectedAccount) && $accCol) {
                $q->where("incomes.{$accCol}", $selectedAccount);
            }
            if (!empty($search) && Schema::hasColumn('incomes', 'note')) {
                $q->where('incomes.note', 'LIKE', "%{$search}%");
            }

            $selects = [
                DB::raw("CONCAT('INC-', incomes.id) as id"),
                "incomes.{$dateCol} as transaction_date",
                DB::raw("'credit' as type"),
                DB::raw(Schema::hasColumn('incomes', 'amount') ? 'incomes.amount' : '0 as amount'),
                DB::raw('0 as balance_after'),
                DB::raw(Schema::hasColumn('incomes', 'note') ? "COALESCE(incomes.note, 'Business Income') as note" : "'Business Income' as note"),
                DB::raw("'Income' as reference_type"),
            ];

            if ($hasJoin) {
                $selects[] = DB::raw("COALESCE(bank_accounts.account_name, 'Cash / Vault') as account_name");
                $selects[] = DB::raw("COALESCE(bank_accounts.account_type, 'Income') as account_type");
            } else {
                $selects[] = DB::raw("'Cash / Vault' as account_name");
                $selects[] = DB::raw("'Income' as account_type");
            }

            $incomes = $q->select($selects)->get();
            $union = $union->concat($incomes);
        }

        // 2. Investments (Credit)
        if (Schema::hasTable('investments') && ($selectedType === 'all' || $selectedType === 'credit')) {
            $dateCol = Schema::hasColumn('investments', 'investment_date') ? 'investment_date' : 'created_at';
            $accCol = $this->resolveAccountForeignKey('investments');

            $q = DB::table('investments');
            $hasJoin = false;
            if ($accCol && Schema::hasTable('bank_accounts')) {
                $q->leftJoin('bank_accounts', "investments.{$accCol}", '=', 'bank_accounts.id');
                $hasJoin = true;
            }

            $q->whereBetween("investments.{$dateCol}", [$start->toDateString(), $end->toDateString()]);

            if ($selectedAccount !== 'all' && !empty($selectedAccount) && $accCol) {
                $q->where("investments.{$accCol}", $selectedAccount);
            }
            if (!empty($search) && Schema::hasColumn('investments', 'investor_name')) {
                $q->where('investments.investor_name', 'LIKE', "%{$search}%");
            }

            $invNote = Schema::hasColumn('investments', 'investor_name') 
                ? "CONCAT('Capital: ', investments.investor_name) as note" 
                : "'Capital Investment' as note";

            $selects = [
                DB::raw("CONCAT('INV-', investments.id) as id"),
                "investments.{$dateCol} as transaction_date",
                DB::raw("'credit' as type"),
                DB::raw(Schema::hasColumn('investments', 'amount') ? 'investments.amount' : '0 as amount'),
                DB::raw('0 as balance_after'),
                DB::raw($invNote),
                DB::raw("'Investment' as reference_type"),
            ];

            if ($hasJoin) {
                $selects[] = DB::raw("COALESCE(bank_accounts.account_name, 'Bank / Vault') as account_name");
                $selects[] = DB::raw("COALESCE(bank_accounts.account_type, 'Capital') as account_type");
            } else {
                $selects[] = DB::raw("'Bank / Vault' as account_name");
                $selects[] = DB::raw("'Capital' as account_type");
            }

            $investments = $q->select($selects)->get();
            $union = $union->concat($investments);
        }

        // 3. Operational Expenses (Debit)
        if (Schema::hasTable('expenses') && ($selectedType === 'all' || $selectedType === 'debit')) {
            $dateCol = Schema::hasColumn('expenses', 'expense_date') ? 'expense_date' : 'created_at';
            $accCol = $this->resolveAccountForeignKey('expenses');

            $q = DB::table('expenses');
            $hasJoin = false;
            if ($accCol && Schema::hasTable('bank_accounts')) {
                $q->leftJoin('bank_accounts', "expenses.{$accCol}", '=', 'bank_accounts.id');
                $hasJoin = true;
            }

            $q->whereBetween("expenses.{$dateCol}", [$start->toDateString(), $end->toDateString()]);

            if ($selectedAccount !== 'all' && !empty($selectedAccount) && $accCol) {
                $q->where("expenses.{$accCol}", $selectedAccount);
            }
            if (!empty($search) && Schema::hasColumn('expenses', 'note')) {
                $q->where('expenses.note', 'LIKE', "%{$search}%");
            }

            $selects = [
                DB::raw("CONCAT('EXP-', expenses.id) as id"),
                "expenses.{$dateCol} as transaction_date",
                DB::raw("'debit' as type"),
                DB::raw(Schema::hasColumn('expenses', 'amount') ? 'expenses.amount' : '0 as amount'),
                DB::raw('0 as balance_after'),
                DB::raw(Schema::hasColumn('expenses', 'note') ? "COALESCE(expenses.note, 'Operational Expense') as note" : "'Operational Expense' as note"),
                DB::raw("'Expense' as reference_type"),
            ];

            if ($hasJoin) {
                $selects[] = DB::raw("COALESCE(bank_accounts.account_name, 'Cash / Vault') as account_name");
                $selects[] = DB::raw("COALESCE(bank_accounts.account_type, 'Expense') as account_type");
            } else {
                $selects[] = DB::raw("'Cash / Vault' as account_name");
                $selects[] = DB::raw("'Expense' as account_type");
            }

            $expenses = $q->select($selects)->get();
            $union = $union->concat($expenses);
        }

        // 4. Fixed Assets Purchases (Debit)
        if (Schema::hasTable('assets') && ($selectedType === 'all' || $selectedType === 'debit')) {
            $dateCol = Schema::hasColumn('assets', 'purchase_date') ? 'purchase_date' : 'created_at';
            $accCol = $this->resolveAccountForeignKey('assets');

            $q = DB::table('assets');
            $hasJoin = false;
            if ($accCol && Schema::hasTable('bank_accounts')) {
                $q->leftJoin('bank_accounts', "assets.{$accCol}", '=', 'bank_accounts.id');
                $hasJoin = true;
            }

            $q->whereBetween("assets.{$dateCol}", [$start->toDateString(), $end->toDateString()]);

            if ($selectedAccount !== 'all' && !empty($selectedAccount) && $accCol) {
                $q->where("assets.{$accCol}", $selectedAccount);
            }
            if (!empty($search) && Schema::hasColumn('assets', 'name')) {
                $q->where('assets.name', 'LIKE', "%{$search}%");
            }

            $astNote = Schema::hasColumn('assets', 'name')
                ? "CONCAT('Asset Purchase: ', assets.name) as note"
                : "'Asset Purchase' as note";

            $selects = [
                DB::raw("CONCAT('AST-', assets.id) as id"),
                "assets.{$dateCol} as transaction_date",
                DB::raw("'debit' as type"),
                DB::raw(Schema::hasColumn('assets', 'purchase_cost') ? 'assets.purchase_cost as amount' : '0 as amount'),
                DB::raw('0 as balance_after'),
                DB::raw($astNote),
                DB::raw("'Fixed Asset' as reference_type"),
            ];

            if ($hasJoin) {
                $selects[] = DB::raw("COALESCE(bank_accounts.account_name, 'Cash / Vault') as account_name");
                $selects[] = DB::raw("COALESCE(bank_accounts.account_type, 'Asset') as account_type");
            } else {
                $selects[] = DB::raw("'Cash / Vault' as account_name");
                $selects[] = DB::raw("'Asset' as account_type");
            }

            $assets = $q->select($selects)->get();
            $union = $union->concat($assets);
        }

        return $union;
    }

    /**
     * 1-Click CSV Export for General Ledger & Cashbook
     */
    public function exportCsv(Request $request)
    {
        [$start, $end] = $this->resolveDateRange($request);
        $filename = "General_Ledger_" . date('Y_m_d_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $records = $this->buildUnifiedLedgerQuery($start, $end, $request)->sortByDesc('transaction_date');

        $callback = function () use ($records, $start, $end) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['UNIFIED GENERAL ACCOUNT LEDGER', $start->format('d M Y') . ' — ' . $end->format('d M Y')]);
            fputcsv($file, []);
            fputcsv($file, ['Txn ID', 'Date', 'Bank / Cash Account', 'Module / Ref', 'Particulars / Note', 'Debit (-)', 'Credit (+)']);

            $totalDebit = 0.00;
            $totalCredit = 0.00;

            foreach ($records as $r) {
                $debitAmt  = $r->type === 'debit' ? (float)$r->amount : 0.00;
                $creditAmt = $r->type === 'credit' ? (float)$r->amount : 0.00;
                $totalDebit += $debitAmt;
                $totalCredit += $creditAmt;

                fputcsv($file, [
                    $r->id,
                    date('d M Y', strtotime($r->transaction_date)),
                    $r->account_name ?? 'Cash / Vault',
                    $r->reference_type ?? 'General',
                    $r->note ?? '—',
                    $debitAmt > 0 ? number_format($debitAmt, 2, '.', '') : '0.00',
                    $creditAmt > 0 ? number_format($creditAmt, 2, '.', '') : '0.00',
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['TOTAL AUDITED MOVEMENT', '', '', '', '', number_format($totalDebit, 2, '.', ''), number_format($totalCredit, 2, '.', '')]);
            fputcsv($file, ['NET CASH MOVEMENT', '', '', '', '', '', number_format($totalCredit - $totalDebit, 2, '.', '') . ' BDT']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}