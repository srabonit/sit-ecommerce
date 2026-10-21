<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class InvestmentController extends Controller
{
    public function __construct()
    {
        $this->ensureInvestmentTablesExist();
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
     * Investment & Capital Management Dashboard & Ledger List
     */
    public function index(Request $request)
    {
        $query = DB::table('investments');

        // Safe join with bank_accounts if table & column exist
        if (Schema::hasTable('bank_accounts') && Schema::hasColumn('investments', 'bank_account_id')) {
            $query->leftJoin('bank_accounts', 'investments.bank_account_id', '=', 'bank_accounts.id')
                  ->select(
                      'investments.*',
                      'bank_accounts.account_name as bank_account_name'
                  );
        } else {
            $query->select('investments.*');
        }

        // 1. Date Range Filter
        [$start, $end, $dateFilter] = $this->resolveDateRange($request);
        $query->whereBetween('investments.investment_date', [$start->toDateString(), $end->toDateString()]);

        // 2. Payment Method Filter
        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $query->where('investments.payment_method', $request->payment_method);
        }

        // 3. Search Filter
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($sub) use ($s) {
                $sub->where('investments.investor_name', 'like', "%{$s}%")
                    ->orWhere('investments.reference_no', 'like', "%{$s}%")
                    ->orWhere('investments.note', 'like', "%{$s}%");
            });
        }

        // Summary Calculations for KPIs
        $summaryQuery = clone $query;
        $totalInvestment = (float) $summaryQuery->sum('investments.amount');
        $totalRecords    = $summaryQuery->count();
        $uniqueInvestors = (clone $query)->distinct('investments.investor_name')->count('investments.investor_name');

        // All Time Total Capital
        $allTimeCapital = (float) DB::table('investments')->sum('amount');

        $investments = $query->latest('investments.investment_date')->latest('investments.id')->paginate(20)->withQueryString();

        // Active Bank / Cash Accounts for the Add Investment Modal
        $bankAccounts = Schema::hasTable('bank_accounts')
            ? DB::table('bank_accounts')->where('is_active', true)->orderBy('account_name')->get()
            : collect();

        return view('admin.accounts.investment.index', compact(
            'investments',
            'bankAccounts',
            'totalInvestment',
            'totalRecords',
            'uniqueInvestors',
            'allTimeCapital',
            'dateFilter',
            'start',
            'end'
        ));
    }

    /**
     * Store New Investment Entry & Deposit into Bank/Cash Account
     */
    public function store(Request $request)
    {
        $request->validate([
            'investor_name'   => 'required|string|max:150',
            'amount'          => 'required|numeric|min:1',
            'investment_date' => 'required|date',
            'bank_account_id' => 'nullable|integer',
            'payment_method'  => 'nullable|string|max:50',
            'reference_no'    => 'nullable|string|max:100',
            'receipt'         => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:4096',
            'note'            => 'nullable|string|max:1000',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $filename = 'inv_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $dest = public_path('uploads/investments');
            if (!file_exists($dest)) {
                @mkdir($dest, 0775, true);
            }
            $file->move($dest, $filename);
            $receiptPath = 'uploads/investments/' . $filename;
        }

        $amount = (float) $request->amount;
        $refNo  = $request->reference_no ?: ('INV-' . strtoupper(Str::random(6)));

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
            // 1. Insert Investment Record
            $investmentData = [
                'investor_name'   => trim($request->investor_name),
                'amount'          => $amount,
                'investment_date' => $request->investment_date,
                'payment_method'  => $paymentMethod,
                'reference_no'    => $refNo,
                'voucher_receipt' => $receiptPath,
                'created_by'      => auth()->id(),
                'note'            => $request->note,
                'created_at'      => now(),
                'updated_at'      => now(),
            ];

            if (Schema::hasColumn('investments', 'bank_account_id')) {
                $investmentData['bank_account_id'] = $bankAccountId;
            }

            $investmentId = DB::table('investments')->insertGetId($investmentData);

            // 2. Deposit into Bank/Cash Balance & Insert into bank_transactions Ledger
            if ($bankAccountId && Schema::hasTable('bank_accounts')) {
                DB::table('bank_accounts')->where('id', $bankAccountId)->increment('current_balance', $amount);

                if (Schema::hasTable('bank_transactions')) {
                    DB::table('bank_transactions')->insert([
                        'from_account_id'  => null,
                        'to_account_id'    => $bankAccountId,
                        'transaction_type' => 'deposit',
                        'amount'           => $amount,
                        'transaction_date' => $request->investment_date,
                        'reference_no'     => $refNo,
                        'note'             => 'Capital Investment #' . $investmentId . ' from ' . trim($request->investor_name) . ($request->note ? ' (' . $request->note . ')' : ''),
                        'created_by'       => auth()->id(),
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.accounts.investment.index')->with('success', 'Capital investment recorded and account credited successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to record investment: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Delete an Investment Entry & Deduct from Bank/Cash Account
     */
    public function destroy($id)
    {
        $inv = DB::table('investments')->where('id', $id)->first();
        if (!$inv) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Investment record not found.'], 404);
            }
            return back()->with('error', 'Investment record not found.');
        }

        DB::beginTransaction();
        try {
            // 1. Revert/Deduct balance from bank account if it was linked
            if (!empty($inv->bank_account_id) && Schema::hasTable('bank_accounts')) {
                DB::table('bank_accounts')->where('id', $inv->bank_account_id)->decrement('current_balance', (float)$inv->amount);

                if (Schema::hasTable('bank_transactions') && !empty($inv->reference_no)) {
                    DB::table('bank_transactions')->where('reference_no', $inv->reference_no)->delete();
                }
            }

            // 2. Remove Receipt Attachment File
            if (!empty($inv->voucher_receipt) && file_exists(public_path($inv->voucher_receipt))) {
                @unlink(public_path($inv->voucher_receipt));
            }

            // 3. Delete Investment Record
            DB::table('investments')->where('id', $id)->delete();

            DB::commit();

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Investment record #' . $id . ' removed and account balance adjusted.']);
            }
            return back()->with('success', 'Investment record #' . $id . ' removed and account balance adjusted.');
        } catch (\Throwable $e) {
            DB::rollBack();
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete investment: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to delete investment: ' . $e->getMessage());
        }
    }

    /**
     * 1-Click Investment Audit CSV Export
     */
    public function exportCsv(Request $request)
    {
        [$start, $end] = $this->resolveDateRange($request);
        $filename = "Capital_Investments_Audit_" . date('Y_m_d_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $records = DB::table('investments')
            ->whereBetween('investments.investment_date', [$start->toDateString(), $end->toDateString()])
            ->latest('investment_date')
            ->get();

        $callback = function () use ($records, $start, $end) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['CAPITAL & INVESTMENTS AUDIT REPORT', $start->format('d M Y') . ' — ' . $end->format('d M Y')]);
            fputcsv($file, []);
            fputcsv($file, ['ID', 'Date', 'Investor Name / Capital Source', 'Amount (BDT)', 'Payment Method / Account', 'Ref / Slip #', 'Note']);

            $total = 0;
            foreach ($records as $r) {
                $total += (float)$r->amount;
                fputcsv($file, [
                    '#' . $r->id,
                    date('d M Y', strtotime($r->investment_date)),
                    $r->investor_name,
                    number_format((float)$r->amount, 2, '.', ''),
                    $r->payment_method,
                    $r->reference_no ?? '—',
                    $r->note ?? ''
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['TOTAL CAPITAL INVESTED', '', '', number_format($total, 2, '.', '') . ' BDT']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Fail-safe Auto Table & Column Creator
     */
    protected function ensureInvestmentTablesExist(): void
    {
        if (!Schema::hasTable('investments')) {
            Schema::create('investments', function ($table) {
                $table->id();
                $table->string('investor_name');
                $table->decimal('amount', 14, 2)->default(0.00);
                $table->date('investment_date');
                $table->unsignedBigInteger('bank_account_id')->nullable();
                $table->string('payment_method')->default('Cash');
                $table->string('reference_no')->nullable();
                $table->string('voucher_receipt')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('investments', 'bank_account_id')) {
            Schema::table('investments', function ($table) {
                $table->unsignedBigInteger('bank_account_id')->nullable()->after('investment_date');
            });
        }
    }
}