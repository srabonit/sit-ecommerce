<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class IncomeController extends Controller
{
    public function __construct()
    {
        $this->ensureIncomeTablesExist();
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
     * Income Management Dashboard & Ledger List
     */
    public function index(Request $request)
    {
        $query = DB::table('incomes')
            ->leftJoin('income_categories', 'incomes.category_id', '=', 'income_categories.id');

        // Safe join with bank_accounts if table & column exist
        if (Schema::hasTable('bank_accounts') && Schema::hasColumn('incomes', 'bank_account_id')) {
            $query->leftJoin('bank_accounts', 'incomes.bank_account_id', '=', 'bank_accounts.id')
                  ->select(
                      'incomes.*',
                      'income_categories.name as category_name',
                      'bank_accounts.account_name as bank_account_name'
                  );
        } else {
            $query->select(
                'incomes.*',
                'income_categories.name as category_name'
            );
        }

        // 1. Date Range Filter
        [$start, $end, $dateFilter] = $this->resolveDateRange($request);
        $query->whereBetween('incomes.income_date', [$start->toDateString(), $end->toDateString()]);

        // 2. Category Filter
        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->where('incomes.category_id', (int)$request->category_id);
        }

        // 3. Payment Method Filter
        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $query->where('incomes.payment_method', $request->payment_method);
        }

        // 4. Search Filter
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($sub) use ($s) {
                $sub->where('incomes.title', 'like', "%{$s}%")
                    ->orWhere('incomes.reference_no', 'like', "%{$s}%")
                    ->orWhere('incomes.note', 'like', "%{$s}%");
            });
        }

        // Summary Calculations for KPIs
        $summaryQuery = clone $query;
        $totalIncome = (float) $summaryQuery->sum('incomes.amount');
        $totalRecords = $summaryQuery->count();

        // Category Breakdown
        $categoryBreakdown = (clone $query)
            ->select('income_categories.name as cat_name', DB::raw('SUM(incomes.amount) as cat_total'))
            ->groupBy('income_categories.name')
            ->orderByDesc('cat_total')
            ->get();

        $incomes = $query->latest('incomes.income_date')->latest('incomes.id')->paginate(20)->withQueryString();
        $categories = DB::table('income_categories')->where('is_active', true)->orderBy('name')->get();

        // Active Bank / Cash Accounts for the Add Income Modal
        $bankAccounts = Schema::hasTable('bank_accounts')
            ? DB::table('bank_accounts')->where('is_active', true)->orderBy('account_name')->get()
            : collect();

        return view('admin.accounts.income.index', compact(
            'incomes',
            'categories',
            'bankAccounts',
            'totalIncome',
            'totalRecords',
            'categoryBreakdown',
            'dateFilter',
            'start',
            'end'
        ));
    }

    /**
     * Store New Income Entry & Deposit into Bank/Cash Account
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'           => 'required|string|max:255',
            'category_id'     => 'required|integer',
            'amount'          => 'required|numeric|min:0.01',
            'income_date'     => 'required|date',
            'bank_account_id' => 'nullable|integer',
            'payment_method'  => 'nullable|string|max:50',
            'reference_no'    => 'nullable|string|max:100',
            'receipt'         => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:4096',
            'note'            => 'nullable|string|max:1000',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $filename = 'inc_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $dest = public_path('uploads/incomes');
            if (!file_exists($dest)) {
                @mkdir($dest, 0775, true);
            }
            $file->move($dest, $filename);
            $receiptPath = 'uploads/incomes/' . $filename;
        }

        $amount = (float) $request->amount;
        $refNo  = $request->reference_no ?: ('INC-' . strtoupper(Str::random(6)));

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
            // 1. Insert Income Record
            $incomeData = [
                'category_id'     => (int) $request->category_id,
                'title'           => trim($request->title),
                'amount'          => $amount,
                'income_date'     => $request->income_date,
                'payment_method'  => $paymentMethod,
                'reference_no'    => $refNo,
                'voucher_receipt' => $receiptPath,
                'created_by'      => auth()->id(),
                'note'            => $request->note,
                'created_at'      => now(),
                'updated_at'      => now(),
            ];

            if (Schema::hasColumn('incomes', 'bank_account_id')) {
                $incomeData['bank_account_id'] = $bankAccountId;
            }

            $incomeId = DB::table('incomes')->insertGetId($incomeData);

            // 2. Deposit into Bank/Cash Balance & Insert into bank_transactions Ledger
            if ($bankAccountId && Schema::hasTable('bank_accounts')) {
                DB::table('bank_accounts')->where('id', $bankAccountId)->increment('current_balance', $amount);

                if (Schema::hasTable('bank_transactions')) {
                    DB::table('bank_transactions')->insert([
                        'from_account_id'  => null,
                        'to_account_id'    => $bankAccountId,
                        'transaction_type' => 'income',
                        'amount'           => $amount,
                        'transaction_date' => $request->income_date,
                        'reference_no'     => $refNo,
                        'note'             => 'Business Inflow #' . $incomeId . ': ' . trim($request->title) . ($request->note ? ' (' . $request->note . ')' : ''),
                        'created_by'       => auth()->id(),
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.accounts.income.index')->with('success', 'Income recorded and account balance credited successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to record income: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Delete an Income Entry & Deduct from Bank/Cash Account
     */
    public function destroy($id)
    {
        $inc = DB::table('incomes')->where('id', $id)->first();
        if (!$inc) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Income record not found.'], 404);
            }
            return back()->with('error', 'Income record not found.');
        }

        DB::beginTransaction();
        try {
            // 1. Revert/Deduct balance from bank account if it was linked
            if (!empty($inc->bank_account_id) && Schema::hasTable('bank_accounts')) {
                DB::table('bank_accounts')->where('id', $inc->bank_account_id)->decrement('current_balance', (float)$inc->amount);

                if (Schema::hasTable('bank_transactions') && !empty($inc->reference_no)) {
                    DB::table('bank_transactions')->where('reference_no', $inc->reference_no)->delete();
                }
            }

            // 2. Remove Receipt Attachment File
            if (!empty($inc->voucher_receipt) && file_exists(public_path($inc->voucher_receipt))) {
                @unlink(public_path($inc->voucher_receipt));
            }

            // 3. Delete Income Record
            DB::table('incomes')->where('id', $id)->delete();

            DB::commit();

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Income record #' . $id . ' removed and account balance adjusted.']);
            }
            return back()->with('success', 'Income record #' . $id . ' removed and account balance adjusted.');
        } catch (\Throwable $e) {
            DB::rollBack();
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete income: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to delete income: ' . $e->getMessage());
        }
    }

    /**
     * Store New Income Category
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:income_categories,name',
        ]);

        DB::table('income_categories')->insert([
            'name'       => trim($request->name),
            'slug'       => Str::slug($request->name),
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'New income category added successfully!');
    }

    /**
     * 1-Click Income Audit CSV Export
     */
    public function exportCsv(Request $request)
    {
        [$start, $end] = $this->resolveDateRange($request);
        $filename = "Incomes_Audit_" . date('Y_m_d_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $records = DB::table('incomes')
            ->leftJoin('income_categories', 'incomes.category_id', '=', 'income_categories.id')
            ->select('incomes.*', 'income_categories.name as category_name')
            ->whereBetween('incomes.income_date', [$start->toDateString(), $end->toDateString()])
            ->latest('incomes.income_date')
            ->get();

        $callback = function () use ($records, $start, $end) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['BUSINESS INFLOWS & OTHER INCOME AUDIT REPORT', $start->format('d M Y') . ' — ' . $end->format('d M Y')]);
            fputcsv($file, []);
            fputcsv($file, ['ID', 'Date', 'Category', 'Income Title / Source', 'Amount (BDT)', 'Payment Method / Account', 'Ref / Voucher', 'Note']);

            $total = 0;
            foreach ($records as $r) {
                $total += (float)$r->amount;
                fputcsv($file, [
                    '#' . $r->id,
                    date('d M Y', strtotime($r->income_date)),
                    $r->category_name ?? 'General',
                    $r->title,
                    number_format((float)$r->amount, 2, '.', ''),
                    $r->payment_method,
                    $r->reference_no ?? '—',
                    $r->note ?? ''
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['TOTAL BUSINESS INFLOWS', '', '', '', number_format($total, 2, '.', '') . ' BDT']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Fail-safe Auto Table & Column Creator
     */
    protected function ensureIncomeTablesExist(): void
    {
        if (!Schema::hasTable('income_categories')) {
            Schema::create('income_categories', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            DB::table('income_categories')->insert([
                ['name' => 'Scrap & Waste Box Sale', 'slug' => 'scrap-box-sale', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Affiliate & Partnership Commission', 'slug' => 'affiliate-commission', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Courier Compensation & Claims', 'slug' => 'courier-compensation', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Bank Interest & Profit', 'slug' => 'bank-interest', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Asset & Equipment Sale', 'slug' => 'asset-sale', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Other Business Inflows', 'slug' => 'others', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (!Schema::hasTable('incomes')) {
            Schema::create('incomes', function ($table) {
                $table->id();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->string('title');
                $table->decimal('amount', 12, 2)->default(0.00);
                $table->date('income_date');
                $table->unsignedBigInteger('bank_account_id')->nullable();
                $table->string('payment_method')->default('Cash');
                $table->string('reference_no')->nullable();
                $table->string('voucher_receipt')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('incomes', 'bank_account_id')) {
            Schema::table('incomes', function ($table) {
                $table->unsignedBigInteger('bank_account_id')->nullable()->after('income_date');
            });
        }
    }
}