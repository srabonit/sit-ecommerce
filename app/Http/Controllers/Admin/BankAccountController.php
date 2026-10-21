<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BankAccountController extends Controller
{
    public function __construct()
    {
        $this->ensureBankTablesExist();
    }

    /**
     * Resolve Date Range Filters based on request
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
     * Bank Accounts & Cash Balance Overview Dashboard
     */
    public function index(Request $request)
    {
        // 1. Fetch Active Accounts
        $accounts = DB::table('bank_accounts')
            ->orderBy('id', 'asc')
            ->get();

        // Account Summaries by Type
        $totalLiquidAssets = (float) $accounts->sum('current_balance');
        $totalBankBalance  = (float) $accounts->where('account_type', 'bank')->sum('current_balance');
        $totalCashInHand   = (float) $accounts->where('account_type', 'cash')->sum('current_balance');
        $totalMobileMoney  = (float) $accounts->where('account_type', 'mobile_banking')->sum('current_balance');

        // 2. Fetch Transaction Ledger
        $query = DB::table('bank_transactions')
            ->leftJoin('bank_accounts as from_acc', 'bank_transactions.from_account_id', '=', 'from_acc.id')
            ->leftJoin('bank_accounts as to_acc', 'bank_transactions.to_account_id', '=', 'to_acc.id')
            ->select(
                'bank_transactions.*',
                'from_acc.account_name as from_account_name',
                'to_acc.account_name as to_account_name'
            );

        // Date Filter
        [$start, $end, $dateFilter] = $this->resolveDateRange($request);
        $query->whereBetween('bank_transactions.transaction_date', [$start->toDateString(), $end->toDateString()]);

        // Account Filter
        if ($request->filled('account_id') && $request->account_id !== 'all') {
            $accId = (int) $request->account_id;
            $query->where(function ($sub) use ($accId) {
                $sub->where('bank_transactions.from_account_id', $accId)
                    ->orWhere('bank_transactions.to_account_id', $accId);
            });
        }

        // Transaction Type Filter
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('bank_transactions.transaction_type', $request->type);
        }

        // Search Filter
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($sub) use ($s) {
                $sub->where('bank_transactions.reference_no', 'like', "%{$s}%")
                    ->orWhere('bank_transactions.note', 'like', "%{$s}%");
            });
        }

        $transactions = $query->latest('bank_transactions.transaction_date')
            ->latest('bank_transactions.id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.accounts.bank.index', compact(
            'accounts',
            'transactions',
            'totalLiquidAssets',
            'totalBankBalance',
            'totalCashInHand',
            'totalMobileMoney',
            'dateFilter',
            'start',
            'end'
        ));
    }

    /**
     * Itemized Account Passbook / Statement Audit View
     */
    public function statement($id, Request $request)
    {
        $account = DB::table('bank_accounts')->where('id', $id)->first();
        if (!$account) {
            abort(404, 'Bank or cash account not found.');
        }

        [$start, $end, $dateFilter] = $this->resolveDateRange($request);

        // Fetch Transactions for this Specific Account
        $rawRecords = DB::table('bank_transactions')
            ->where(function ($q) use ($id) {
                $q->where('from_account_id', $id)
                  ->orWhere('to_account_id', $id);
            })
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Calculate Totals
        $totalCredits = 0.00;
        $totalDebits  = 0.00;

        foreach ($rawRecords as $trx) {
            $isOutgoing = ((int)$trx->from_account_id === (int)$id);
            if ($isOutgoing) {
                $trx->type = 'debit';
                $totalDebits += (float)$trx->amount;
            } else {
                $trx->type = 'credit';
                $totalCredits += (float)$trx->amount;
            }
        }

        $currentBalance = (float) $account->current_balance;
        $netChange = $totalCredits - $totalDebits;
        $openingBalance = max(0.00, $currentBalance - $netChange);
        $closingBalance = $currentBalance;

        // Calculate Chronological Running Balance for Passbook
        $running = $openingBalance;
        foreach ($rawRecords as $trx) {
            if ($trx->type === 'credit') {
                $running += (float)$trx->amount;
            } else {
                $running -= (float)$trx->amount;
            }
            $trx->balance_after = $running;
        }

        // Order descending for passbook table view
        $descendingRecords = $rawRecords->sortByDesc('transaction_date')->values();

        // Pagination
        $perPage = 25;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $descendingRecords->slice(($currentPage - 1) * $perPage, $perPage)->all();

        $transactions = new LengthAwarePaginator(
            $currentItems,
            $descendingRecords->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('admin.accounts.bank.statement', compact(
            'account',
            'transactions',
            'openingBalance',
            'closingBalance',
            'totalCredits',
            'totalDebits',
            'dateFilter',
            'start',
            'end'
        ));
    }

    /**
     * Store New Bank / Cash Account
     */
    public function storeAccount(Request $request)
    {
        $request->validate([
            'account_name'    => 'required|string|max:150',
            'account_type'    => 'required|in:bank,cash,mobile_banking',
            'account_number'  => 'nullable|string|max:100',
            'bank_name'       => 'nullable|string|max:150',
            'branch_name'     => 'nullable|string|max:150',
            'opening_balance' => 'nullable|numeric|min:0',
        ]);

        $openingBal = (float) $request->input('opening_balance', 0.00);

        DB::beginTransaction();
        try {
            $accId = DB::table('bank_accounts')->insertGetId([
                'account_name'    => trim($request->account_name),
                'account_type'    => $request->account_type,
                'account_number'  => $request->account_number,
                'bank_name'       => $request->bank_name,
                'branch_name'     => $request->branch_name,
                'opening_balance' => $openingBal,
                'current_balance' => $openingBal,
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            if ($openingBal > 0) {
                DB::table('bank_transactions')->insert([
                    'to_account_id'    => $accId,
                    'transaction_type' => 'deposit',
                    'amount'           => $openingBal,
                    'transaction_date' => now()->toDateString(),
                    'reference_no'     => 'OPN-' . strtoupper(Str::random(5)),
                    'note'             => 'Opening balance setup for ' . $request->account_name,
                    'created_by'       => auth()->id(),
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('admin.accounts.bank')->with('success', 'New account created successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create account: ' . $e->getMessage());
        }
    }

    /**
     * Internal Balance Transfer between Accounts (Cash -> Bank Deposit)
     */
    public function transferBalance(Request $request)
    {
        $request->validate([
            'from_account_id' => 'required|integer|different:to_account_id',
            'to_account_id'   => 'required|integer',
            'amount'          => 'required|numeric|min:1',
            'transfer_date'   => 'required|date',
            'reference_no'    => 'nullable|string|max:100',
            'note'            => 'nullable|string|max:500',
        ]);

        $fromAcc = DB::table('bank_accounts')->where('id', $request->from_account_id)->first();
        $toAcc   = DB::table('bank_accounts')->where('id', $request->to_account_id)->first();

        if (!$fromAcc || !$toAcc) {
            return back()->with('error', 'Invalid source or destination account.');
        }

        $transferAmt = (float) $request->amount;

        if ($fromAcc->current_balance < $transferAmt) {
            return back()->with('error', "Insufficient balance in {$fromAcc->account_name}. Available: ৳" . number_format($fromAcc->current_balance, 2));
        }

        DB::beginTransaction();
        try {
            // 1. Deduct from Source
            DB::table('bank_accounts')->where('id', $fromAcc->id)->decrement('current_balance', $transferAmt);

            // 2. Add to Destination
            DB::table('bank_accounts')->where('id', $toAcc->id)->increment('current_balance', $transferAmt);

            // 3. Record Audit Transfer Log
            $ref = $request->reference_no ?: ('TRF-' . strtoupper(Str::random(6)));
            DB::table('bank_transactions')->insert([
                'from_account_id'  => $fromAcc->id,
                'to_account_id'    => $toAcc->id,
                'transaction_type' => 'transfer',
                'amount'           => $transferAmt,
                'transaction_date' => $request->transfer_date,
                'reference_no'     => $ref,
                'note'             => $request->note ?: "Fund transfer from {$fromAcc->account_name} to {$toAcc->account_name}",
                'created_by'       => auth()->id(),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            DB::commit();
            return redirect()->route('admin.accounts.bank')->with('success', "৳" . number_format($transferAmt, 2) . " transferred successfully from {$fromAcc->account_name} to {$toAcc->account_name}!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Transfer failed: ' . $e->getMessage());
        }
    }

    /**
     * Direct Deposit / Adjustment to an Account
     */
    public function deposit(Request $request)
    {
        $request->validate([
            'account_id'       => 'required|integer',
            'amount'           => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'reference_no'     => 'nullable|string|max:100',
            'note'             => 'nullable|string|max:500',
        ]);

        $acc = DB::table('bank_accounts')->where('id', $request->account_id)->first();
        if (!$acc) {
            return back()->with('error', 'Account not found.');
        }

        $amt = (float) $request->amount;

        DB::beginTransaction();
        try {
            DB::table('bank_accounts')->where('id', $acc->id)->increment('current_balance', $amt);

            DB::table('bank_transactions')->insert([
                'to_account_id'    => $acc->id,
                'transaction_type' => 'deposit',
                'amount'           => $amt,
                'transaction_date' => $request->transaction_date,
                'reference_no'     => $request->reference_no ?: ('DEP-' . strtoupper(Str::random(6))),
                'note'             => $request->note ?: "Direct deposit / top-up into {$acc->account_name}",
                'created_by'       => auth()->id(),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            DB::commit();
            return redirect()->route('admin.accounts.bank')->with('success', "৳" . number_format($amt, 2) . " deposited into {$acc->account_name}!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Deposit failed: ' . $e->getMessage());
        }
    }

    /**
     * 1-Click Export Bank & Cash Ledger to CSV
     */
    public function exportCsv(Request $request)
    {
        $filename = "Bank_Ledger_" . date('Y_m_d_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $transactions = DB::table('bank_transactions')
            ->leftJoin('bank_accounts as from_acc', 'bank_transactions.from_account_id', '=', 'from_acc.id')
            ->leftJoin('bank_accounts as to_acc', 'bank_transactions.to_account_id', '=', 'to_acc.id')
            ->select(
                'bank_transactions.*',
                'from_acc.account_name as from_account_name',
                'to_acc.account_name as to_account_name'
            )
            ->latest('bank_transactions.transaction_date')
            ->get();

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['ID', 'Date', 'Type', 'From Account', 'To Account', 'Amount (BDT)', 'Reference #', 'Particulars / Note']);

            foreach ($transactions as $t) {
                fputcsv($file, [
                    '#' . $t->id,
                    date('d M Y', strtotime($t->transaction_date)),
                    strtoupper($t->transaction_type),
                    $t->from_account_name ?? '—',
                    $t->to_account_name ?? '—',
                    number_format((float)$t->amount, 2, '.', ''),
                    $t->reference_no ?? '—',
                    $t->note ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Auto Schema Creator for Zero-Crash Execution
     */
    protected function ensureBankTablesExist(): void
    {
        if (!Schema::hasTable('bank_accounts')) {
            Schema::create('bank_accounts', function ($table) {
                $table->id();
                $table->string('account_name');
                $table->enum('account_type', ['bank', 'cash', 'mobile_banking'])->default('bank');
                $table->string('account_number')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('branch_name')->nullable();
                $table->decimal('opening_balance', 14, 2)->default(0.00);
                $table->decimal('current_balance', 14, 2)->default(0.00);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed standard accounts
            DB::table('bank_accounts')->insert([
                [
                    'account_name'    => 'Cash in Hand (Main Drawer)',
                    'account_type'    => 'cash',
                    'account_number'  => 'CASH-01',
                    'bank_name'       => 'Physical Cash',
                    'branch_name'     => 'Main Outlet',
                    'opening_balance' => 0.00,
                    'current_balance' => 0.00,
                    'is_active'       => true,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ],
                [
                    'account_name'    => 'Main Commercial Bank',
                    'account_type'    => 'bank',
                    'account_number'  => '100-200-3000',
                    'bank_name'       => 'Dutch Bangla Bank / City Bank',
                    'branch_name'     => 'Principal Branch',
                    'opening_balance' => 0.00,
                    'current_balance' => 0.00,
                    'is_active'       => true,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ],
                [
                    'account_name'    => 'bKash Merchant Account',
                    'account_type'    => 'mobile_banking',
                    'account_number'  => '01XXXXXXXXX',
                    'bank_name'       => 'bKash',
                    'branch_name'     => 'Merchant Gateway',
                    'opening_balance' => 0.00,
                    'current_balance' => 0.00,
                    'is_active'       => true,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ],
            ]);
        }

        if (!Schema::hasTable('bank_transactions')) {
            Schema::create('bank_transactions', function ($table) {
                $table->id();
                $table->unsignedBigInteger('from_account_id')->nullable();
                $table->unsignedBigInteger('to_account_id')->nullable();
                $table->enum('transaction_type', ['deposit', 'withdraw', 'transfer', 'expense', 'income'])->default('transfer');
                $table->decimal('amount', 14, 2)->default(0.00);
                $table->date('transaction_date');
                $table->string('reference_no')->nullable();
                $table->text('note')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }
    }
}