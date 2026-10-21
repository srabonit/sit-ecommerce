<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    public function __construct()
    {
        $this->ensureAssetTablesExist();
    }

    /**
     * Resolve Date Range Filters based on request
     */
    protected function resolveDateRange(Request $request): array
    {
        $dateFilter = $request->input('date_filter', 'this_year');
        $start = Carbon::now()->startOfYear();
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
            case 'this_month':
                $start = Carbon::now()->startOfMonth();
                $end   = Carbon::now()->endOfDay();
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
                $start = Carbon::create(2020, 1, 1)->startOfDay();
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
            default:
                $start = Carbon::now()->startOfYear();
                $end   = Carbon::now()->endOfYear();
                break;
        }

        return [$start, $end, $dateFilter];
    }

    /**
     * Fixed Assets Management Dashboard & Ledger List
     */
    public function index(Request $request)
    {
        $query = DB::table('assets');

        // Safe join with bank_accounts if table & column exist
        if (Schema::hasTable('bank_accounts') && Schema::hasColumn('assets', 'bank_account_id')) {
            $query->leftJoin('bank_accounts', 'assets.bank_account_id', '=', 'bank_accounts.id')
                  ->select(
                      'assets.*',
                      'bank_accounts.account_name as bank_account_name'
                  );
        } else {
            $query->select('assets.*');
        }

        // 1. Date Range Filter
        [$start, $end, $dateFilter] = $this->resolveDateRange($request);

        if ($dateFilter !== 'all') {
            $query->whereBetween('assets.purchase_date', [$start->toDateString(), $end->toDateString()]);
        }

        // 2. Asset Type / Category Filter
        if ($request->filled('asset_type') && $request->asset_type !== 'all') {
            $query->where('assets.asset_type', $request->asset_type);
        }

        // 3. Search Filter
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($sub) use ($s) {
                $sub->where('assets.name', 'like', "%{$s}%")
                    ->orWhere('assets.asset_code', 'like', "%{$s}%")
                    ->orWhere('assets.note', 'like', "%{$s}%");
            });
        }

        // Summary Calculations for KPIs
        $allAssetsQuery   = DB::table('assets');
        $totalValuation   = (float) $allAssetsQuery->sum('purchase_cost');
        $totalAssetCount  = $allAssetsQuery->count();

        $periodQuery      = clone $query;
        $periodAdditions  = (float) $periodQuery->sum('assets.purchase_cost');
        $periodCount      = $periodQuery->count();

        $assets = $query->latest('assets.purchase_date')->latest('assets.id')->paginate(20)->withQueryString();

        // Active Bank / Cash Accounts for Payment Selection
        $bankAccounts = Schema::hasTable('bank_accounts')
            ? DB::table('bank_accounts')->where('is_active', true)->orderBy('account_name')->get()
            : collect();

        return view('admin.accounts.assets.index', compact(
            'assets',
            'bankAccounts',
            'totalValuation',
            'totalAssetCount',
            'periodAdditions',
            'periodCount',
            'dateFilter',
            'start',
            'end'
        ));
    }

    /**
     * Store New Fixed Asset Entry & Deduct from Bank/Cash Account
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:200',
            'asset_type'      => 'required|string|max:100',
            'purchase_cost'   => 'required|numeric|min:1',
            'purchase_date'   => 'required|date',
            'asset_code'      => 'nullable|string|max:100',
            'warranty_expiry' => 'nullable|date',
            'bank_account_id' => 'nullable|integer',
            'payment_method'  => 'nullable|string|max:50',
            'receipt'         => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:4096',
            'note'            => 'nullable|string|max:1000',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $filename = 'ast_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $dest = public_path('uploads/assets');
            if (!file_exists($dest)) {
                @mkdir($dest, 0775, true);
            }
            $file->move($dest, $filename);
            $receiptPath = 'uploads/assets/' . $filename;
        }

        $cost   = (float) $request->purchase_cost;
        $codeNo = $request->asset_code ?: ('AST-' . strtoupper(Str::random(6)));

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
            // 1. Insert Asset Record
            $assetData = [
                'name'            => trim($request->name),
                'asset_code'      => $codeNo,
                'asset_type'      => $request->asset_type,
                'purchase_cost'   => $cost,
                'purchase_date'   => $request->purchase_date,
                'warranty_expiry' => $request->warranty_expiry,
                'payment_method'  => $paymentMethod,
                'voucher_receipt' => $receiptPath,
                'created_by'      => auth()->id(),
                'note'            => $request->note,
                'created_at'      => now(),
                'updated_at'      => now(),
            ];

            if (Schema::hasColumn('assets', 'bank_account_id')) {
                $assetData['bank_account_id'] = $bankAccountId;
            }

            $assetId = DB::table('assets')->insertGetId($assetData);

            // 2. Deduct from Bank/Cash Balance & Insert into bank_transactions Ledger
            if ($bankAccountId && Schema::hasTable('bank_accounts')) {
                DB::table('bank_accounts')->where('id', $bankAccountId)->decrement('current_balance', $cost);

                if (Schema::hasTable('bank_transactions')) {
                    DB::table('bank_transactions')->insert([
                        'from_account_id'  => $bankAccountId,
                        'to_account_id'    => null,
                        'transaction_type' => 'expense',
                        'amount'           => $cost,
                        'transaction_date' => $request->purchase_date,
                        'reference_no'     => $codeNo,
                        'note'             => 'Fixed Asset Purchase #' . $assetId . ': ' . trim($request->name) . ($request->note ? ' (' . $request->note . ')' : ''),
                        'created_by'       => auth()->id(),
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.accounts.assets')->with('success', 'Asset record created and account balance deducted successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to register asset: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Delete an Asset Entry & Refund Bank/Cash Account
     */
    public function destroy($id)
    {
        $asset = DB::table('assets')->where('id', $id)->first();
        if (!$asset) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Asset record not found.'], 404);
            }
            return back()->with('error', 'Asset record not found.');
        }

        DB::beginTransaction();
        try {
            // 1. Refund balance to bank account if it was linked
            if (!empty($asset->bank_account_id) && Schema::hasTable('bank_accounts')) {
                DB::table('bank_accounts')->where('id', $asset->bank_account_id)->increment('current_balance', (float)$asset->purchase_cost);

                if (Schema::hasTable('bank_transactions') && !empty($asset->asset_code)) {
                    DB::table('bank_transactions')->where('reference_no', $asset->asset_code)->delete();
                }
            }

            // 2. Remove Receipt Attachment File
            if (!empty($asset->voucher_receipt) && file_exists(public_path($asset->voucher_receipt))) {
                @unlink(public_path($asset->voucher_receipt));
            }

            // 3. Delete Asset Record
            DB::table('assets')->where('id', $id)->delete();

            DB::commit();

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Asset #' . $id . ' removed and account refunded.']);
            }
            return back()->with('success', 'Asset #' . $id . ' removed and account refunded.');
        } catch (\Throwable $e) {
            DB::rollBack();
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete asset: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to delete asset: ' . $e->getMessage());
        }
    }

    /**
     * 1-Click Fixed Assets Inventory CSV Export
     */
    public function exportCsv(Request $request)
    {
        [$start, $end, $dateFilter] = $this->resolveDateRange($request);
        $filename = "Fixed_Assets_Register_" . date('Y_m_d_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $query = DB::table('assets');
        if ($dateFilter !== 'all') {
            $query->whereBetween('purchase_date', [$start->toDateString(), $end->toDateString()]);
        }
        if ($request->filled('asset_type') && $request->asset_type !== 'all') {
            $query->where('asset_type', $request->asset_type);
        }

        $records = $query->latest('purchase_date')->get();

        $callback = function () use ($records, $start, $end, $dateFilter) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            $scope = ($dateFilter === 'all') ? 'All-Time Records' : ($start->format('d M Y') . ' — ' . $end->format('d M Y'));
            fputcsv($file, ['FIXED ASSETS & EQUIPMENT AUDIT REGISTER', $scope]);
            fputcsv($file, []);
            fputcsv($file, ['Asset Tag / Code', 'Asset Name', 'Category / Type', 'Purchase Cost (BDT)', 'Purchase Date', 'Warranty Expiry', 'Payment Account', 'Notes']);

            $total = 0;
            foreach ($records as $r) {
                $total += (float)$r->purchase_cost;
                fputcsv($file, [
                    $r->asset_code ?? '—',
                    $r->name,
                    ucwords(str_replace('_', ' ', $r->asset_type)),
                    number_format((float)$r->purchase_cost, 2, '.', ''),
                    date('d M Y', strtotime($r->purchase_date)),
                    !empty($r->warranty_expiry) ? date('d M Y', strtotime($r->warranty_expiry)) : 'N/A',
                    $r->payment_method ?? '—',
                    $r->note ?? ''
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['TOTAL REGISTERED ASSETS VALUE', '', '', number_format($total, 2, '.', '') . ' BDT']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Fail-safe Auto Table & Column Creator
     */
    protected function ensureAssetTablesExist(): void
    {
        if (!Schema::hasTable('assets')) {
            Schema::create('assets', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('asset_code')->nullable();
                $table->string('asset_type')->default('electronics');
                $table->decimal('purchase_cost', 14, 2)->default(0.00);
                $table->date('purchase_date');
                $table->date('warranty_expiry')->nullable();
                $table->unsignedBigInteger('bank_account_id')->nullable();
                $table->string('payment_method')->default('Cash');
                $table->string('voucher_receipt')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('assets', 'bank_account_id')) {
            Schema::table('assets', function ($table) {
                $table->unsignedBigInteger('bank_account_id')->nullable()->after('warranty_expiry');
            });
        }
    }
}