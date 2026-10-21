<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->ensureExpenseTablesExist();
    }

    /**
     * Expense Management Dashboard & Ledger List
     */
    public function index(Request $request)
    {
        $query = DB::table('expenses')
            ->leftJoin('expense_categories', 'expenses.category_id', '=', 'expense_categories.id');

        // Safe join with bank_accounts if table & column exist
        if (Schema::hasTable('bank_accounts') && Schema::hasColumn('expenses', 'bank_account_id')) {
            $query->leftJoin('bank_accounts', 'expenses.bank_account_id', '=', 'bank_accounts.id')
                  ->select(
                      'expenses.*',
                      'expense_categories.name as category_name',
                      'bank_accounts.account_name as bank_account_name'
                  );
        } else {
            $query->select(
                'expenses.*',
                'expense_categories.name as category_name'
            );
        }

        // 1. Date Range Filter
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

        $query->whereBetween('expenses.expense_date', [$start->toDateString(), $end->toDateString()]);

        // 2. Category Filter
        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->where('expenses.category_id', (int)$request->category_id);
        }

        // 3. Payment Method Filter
        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $query->where('expenses.payment_method', $request->payment_method);
        }

        // 4. Search Filter
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($sub) use ($s) {
                $sub->where('expenses.title', 'like', "%{$s}%")
                    ->orWhere('expenses.reference_no', 'like', "%{$s}%")
                    ->orWhere('expenses.note', 'like', "%{$s}%");
            });
        }

        // Summary Calculations for KPIs
        $summaryQuery = clone $query;
        $totalExpense = (float) $summaryQuery->sum('expenses.amount');
        $totalRecords = $summaryQuery->count();

        // Category Breakdown
        $categoryBreakdown = (clone $query)
            ->select('expense_categories.name as cat_name', DB::raw('SUM(expenses.amount) as cat_total'))
            ->groupBy('expense_categories.name')
            ->orderByDesc('cat_total')
            ->get();

        $expenses = $query->latest('expenses.expense_date')->latest('expenses.id')->paginate(20)->withQueryString();
        $categories = DB::table('expense_categories')->where('is_active', true)->orderBy('name')->get();

        // Active Bank / Cash Accounts for the Add Expense Modal
        $bankAccounts = Schema::hasTable('bank_accounts')
            ? DB::table('bank_accounts')->where('is_active', true)->orderBy('account_name')->get()
            : collect();

        return view('admin.accounts.expenses.index', compact(
            'expenses',
            'categories',
            'bankAccounts',
            'totalExpense',
            'totalRecords',
            'categoryBreakdown',
            'dateFilter',
            'start',
            'end'
        ));
    }

    /**
     * Store New Expense Entry & Deduct from Bank/Cash Account
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'           => 'required|string|max:255',
            'category_id'     => 'required|integer',
            'amount'          => 'required|numeric|min:0.01',
            'expense_date'    => 'required|date',
            'bank_account_id' => 'nullable|integer',
            'payment_method'  => 'nullable|string|max:50',
            'reference_no'    => 'nullable|string|max:100',
            'receipt'         => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:4096',
            'note'            => 'nullable|string|max:1000',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $filename = 'exp_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $dest = public_path('uploads/expenses');
            if (!file_exists($dest)) {
                @mkdir($dest, 0775, true);
            }
            $file->move($dest, $filename);
            $receiptPath = 'uploads/expenses/' . $filename;
        }

        $amount = (float) $request->amount;
        $refNo  = $request->reference_no ?: ('EXP-' . strtoupper(Str::random(6)));

        // Resolve Bank Account
        $bankAccountId = $request->filled('bank_account_id') ? (int)$request->bank_account_id : null;
        $paymentMethod = $request->payment_method ?: 'Cash';

        if ($bankAccountId && Schema::hasTable('bank_accounts')) {
            $selectedAcc = DB::table('bank_accounts')->where('id', $bankAccountId)->first();
            if ($selectedAcc) {
                $paymentMethod = $selectedAcc->account_name;
            }
        }

        DB::beginTransaction();
        try {
            // 1. Insert Expense Record
            $expenseData = [
                'category_id'     => (int) $request->category_id,
                'title'           => trim($request->title),
                'amount'          => $amount,
                'expense_date'    => $request->expense_date,
                'payment_method'  => $paymentMethod,
                'reference_no'    => $refNo,
                'voucher_receipt' => $receiptPath,
                'created_by'      => auth()->id(),
                'note'            => $request->note,
                'created_at'      => now(),
                'updated_at'      => now(),
            ];

            if (Schema::hasColumn('expenses', 'bank_account_id')) {
                $expenseData['bank_account_id'] = $bankAccountId;
            }

            $expenseId = DB::table('expenses')->insertGetId($expenseData);

            // 2. Deduct from Bank/Cash Balance & Insert into bank_transactions Ledger
            if ($bankAccountId && Schema::hasTable('bank_accounts')) {
                DB::table('bank_accounts')->where('id', $bankAccountId)->decrement('current_balance', $amount);

                if (Schema::hasTable('bank_transactions')) {
                    DB::table('bank_transactions')->insert([
                        'from_account_id'  => $bankAccountId,
                        'to_account_id'    => null,
                        'transaction_type' => 'expense',
                        'amount'           => $amount,
                        'transaction_date' => $request->expense_date,
                        'reference_no'     => $refNo,
                        'note'             => 'Operating Expense #' . $expenseId . ': ' . trim($request->title) . ($request->note ? ' (' . $request->note . ')' : ''),
                        'created_by'       => auth()->id(),
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.accounts.expenses.index')->with('success', 'Operational expense recorded and account balance updated successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to record expense: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Delete an Expense Entry & Refund Bank/Cash Account
     */
    public function destroy($id)
    {
        $exp = DB::table('expenses')->where('id', $id)->first();
        if (!$exp) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Expense record not found.'], 404);
            }
            return back()->with('error', 'Expense record not found.');
        }

        DB::beginTransaction();
        try {
            // 1. Revert/Refund balance to bank account if it was linked
            if (!empty($exp->bank_account_id) && Schema::hasTable('bank_accounts')) {
                DB::table('bank_accounts')->where('id', $exp->bank_account_id)->increment('current_balance', (float)$exp->amount);

                if (Schema::hasTable('bank_transactions') && !empty($exp->reference_no)) {
                    DB::table('bank_transactions')->where('reference_no', $exp->reference_no)->delete();
                }
            }

            // 2. Remove Receipt Attachment File
            if (!empty($exp->voucher_receipt) && file_exists(public_path($exp->voucher_receipt))) {
                @unlink(public_path($exp->voucher_receipt));
            }

            // 3. Delete Expense Record
            DB::table('expenses')->where('id', $id)->delete();

            DB::commit();

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Expense record #' . $id . ' removed and account balance refunded.']);
            }
            return back()->with('success', 'Expense record #' . $id . ' removed and account balance refunded.');
        } catch (\Throwable $e) {
            DB::rollBack();
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete expense: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to delete expense: ' . $e->getMessage());
        }
    }

    /**
     * Store New Expense Category
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:expense_categories,name',
        ]);

        DB::table('expense_categories')->insert([
            'name'       => trim($request->name),
            'slug'       => Str::slug($request->name),
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'New expense category added successfully!');
    }

    /**
     * 1-Click Expense Audit CSV Export
     */
    public function exportCsv(Request $request)
    {
        $filename = "Expenses_Audit_" . date('Y_m_d_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $records = DB::table('expenses')
            ->leftJoin('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->select('expenses.*', 'expense_categories.name as category_name')
            ->latest('expenses.expense_date')
            ->get();

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['ID', 'Date', 'Category', 'Expense Title', 'Amount (BDT)', 'Payment Method / Account', 'Ref / Voucher', 'Note']);

            $total = 0;
            foreach ($records as $r) {
                $total += (float)$r->amount;
                fputcsv($file, [
                    '#' . $r->id,
                    date('d M Y', strtotime($r->expense_date)),
                    $r->category_name ?? 'General',
                    $r->title,
                    number_format((float)$r->amount, 2, '.', ''),
                    $r->payment_method,
                    $r->reference_no ?? '—',
                    $r->note ?? ''
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['TOTAL OPERATING EXPENSES', '', '', '', number_format($total, 2, '.', '') . ' BDT']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Fail-safe Auto Table & Column Creator
     */
    protected function ensureExpenseTablesExist(): void
    {
        if (!Schema::hasTable('expense_categories')) {
            Schema::create('expense_categories', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            DB::table('expense_categories')->insert([
                ['name' => 'Packaging & Boxes', 'slug' => 'packaging', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Facebook & Social Ads', 'slug' => 'social-ads', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Office / Warehouse Rent', 'slug' => 'rent', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Staff Salaries & Bonus', 'slug' => 'salary', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Electricity & Utilities', 'slug' => 'utilities', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Courier Return Damage', 'slug' => 'courier-damage', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Tea, Snacks & Entertainment', 'slug' => 'entertainment', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Other Operational Expenses', 'slug' => 'others', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (!Schema::hasTable('expenses')) {
            Schema::create('expenses', function ($table) {
                $table->id();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->string('title');
                $table->decimal('amount', 12, 2)->default(0.00);
                $table->date('expense_date');
                $table->unsignedBigInteger('bank_account_id')->nullable();
                $table->string('payment_method')->default('Cash');
                $table->string('reference_no')->nullable();
                $table->string('voucher_receipt')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('expenses', 'bank_account_id')) {
            Schema::table('expenses', function ($table) {
                $table->unsignedBigInteger('bank_account_id')->nullable()->after('expense_date');
            });
        }
    }
}