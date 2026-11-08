<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Requests\StoreAssetProcurementRequest;

class AssetProcurementController extends Controller
{
    /**
     * Display fixed asset capitalization register.
     */
    public function index(Request $request)
    {
        if (!Schema::hasTable('assets')) {
            $assets = new LengthAwarePaginator([], 0, 15);
            $totalGrossValue = 0;
            $totalAccumulatedDepreciation = 0;
            $totalNetBookValue = 0;
            $assetCategories = collect([]);
            $branches = collect([]);

            return view('admin.procurement.asset_procurements.index', compact(
                'assets',
                'totalGrossValue',
                'totalAccumulatedDepreciation',
                'totalNetBookValue',
                'assetCategories',
                'branches'
            ));
        }

        $query = DB::table('assets');

        if (Schema::hasTable('asset_categories')) {
            $query->leftJoin('asset_categories', 'assets.asset_category_id', '=', 'asset_categories.id')
                  ->addSelect('asset_categories.name as category_name');
        }

        if (Schema::hasTable('branches')) {
            $query->leftJoin('branches', 'assets.branch_id', '=', 'branches.id')
                  ->addSelect('branches.name as branch_name');
        }

        if (Schema::hasTable('purchases')) {
            $numberCol = Schema::hasColumn('purchases', 'purchase_number') ? 'purchases.purchase_number' : (Schema::hasColumn('purchases', 'purchase_no') ? 'purchases.purchase_no' : 'purchases.id');
            $query->leftJoin('purchases', 'assets.purchase_id', '=', 'purchases.id')
                  ->addSelect(DB::raw("COALESCE({$numberCol}, assets.purchase_id) as purchase_number"));
        }

        $query->addSelect('assets.*');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('assets.asset_code', 'like', "%{$search}%")
                  ->orWhere('assets.name', 'like', "%{$search}%")
                  ->orWhere('assets.serial_number', 'like', "%{$search}%")
                  ->orWhere('assets.brand', 'like', "%{$search}%");
            });
        }

        if ($request->filled('asset_category_id')) {
            $query->where('assets.asset_category_id', $request->get('asset_category_id'));
        }

        if ($request->filled('branch_id')) {
            $query->where('assets.branch_id', $request->get('branch_id'));
        }

        if ($request->filled('status')) {
            $query->where('assets.status', $request->get('status'));
        }

        if ($request->filled('purchase_date')) {
            $query->whereDate('assets.purchase_date', $request->get('purchase_date'));
        }

        $assets = $query->orderBy('assets.id', 'desc')->paginate(15)->withQueryString();

        foreach ($assets as $a) {
            $a->category = (object)['name' => $a->category_name ?? 'General Asset'];
            $a->branch   = (object)['name' => $a->branch_name ?? 'HQ Branch'];
            $a->purchase = (object)['purchase_number' => $a->purchase_number ?? '—'];
        }

        // Metrics Summary
        $costCol = Schema::hasColumn('assets', 'capitalized_cost') ? 'capitalized_cost' : 'purchase_cost';
        $totalGrossValue = DB::table('assets')->sum($costCol) ?? 0;
        $totalAccumulatedDepreciation = Schema::hasColumn('assets', 'accumulated_depreciation') ? (DB::table('assets')->sum('accumulated_depreciation') ?? 0) : 0;
        $totalNetBookValue = max(0, $totalGrossValue - $totalAccumulatedDepreciation);

        $assetCategories = Schema::hasTable('asset_categories') ? DB::table('asset_categories')->get() : collect([]);
        $branches = Schema::hasTable('branches') ? DB::table('branches')->get() : collect([]);

        return view('admin.procurement.asset_procurements.index', compact(
            'assets',
            'totalGrossValue',
            'totalAccumulatedDepreciation',
            'totalNetBookValue',
            'assetCategories',
            'branches'
        ));
    }

    /**
     * Show asset capitalization form.
     */
    public function create()
    {
        $assetCategories = Schema::hasTable('asset_categories') ? DB::table('asset_categories')->get() : collect([]);
        $suppliers = Schema::hasTable('suppliers') ? DB::table('suppliers')->where('status', 'active')->get() : collect([]);
        $branches = Schema::hasTable('branches') ? DB::table('branches')->get() : collect([]);
        $departments = Schema::hasTable('departments') ? DB::table('departments')->get() : collect([]);
        $employees = DB::table('users')->select('id', 'name')->get();

        $purchases = collect([]);
        if (Schema::hasTable('purchases')) {
            $numberCol = Schema::hasColumn('purchases', 'purchase_number') ? 'purchase_number' : (Schema::hasColumn('purchases', 'purchase_no') ? 'purchase_no' : 'id');
            $purchases = DB::table('purchases')
                ->select('id', DB::raw("COALESCE({$numberCol}, id) as purchase_number"), 'supplier_id', 'grand_total', 'purchase_date')
                ->orderBy('id', 'desc')
                ->limit(50)
                ->get();
        }

        // GL Accounts
        $assetAccounts = collect([]);
        $accumulatedDeprAccounts = collect([]);
        $deprExpenseAccounts = collect([]);

        if (Schema::hasTable('chart_of_accounts')) {
            $assetAccounts = DB::table('chart_of_accounts')->where('type', 'asset')->get();
            $accumulatedDeprAccounts = DB::table('chart_of_accounts')->where('type', 'contra_asset')->get();
            $deprExpenseAccounts = DB::table('chart_of_accounts')->where('type', 'expense')->get();
        }

        return view('admin.procurement.asset_procurements.create', compact(
            'assetCategories',
            'suppliers',
            'branches',
            'departments',
            'employees',
            'purchases',
            'assetAccounts',
            'accumulatedDeprAccounts',
            'deprExpenseAccounts'
        ));
    }

    /**
     * Store capitalized asset and post ledger entries.
     */
    public function store(StoreAssetProcurementRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            if ($request->hasFile('attachment_file')) {
                $validated['attachment_file'] = $request->file('attachment_file')->store('assets/attachments', 'public');
            }

            $validated['status'] = 'active';
            $validated['accumulated_depreciation'] = 0.00;
            $validated['created_at'] = now();
            $validated['updated_at'] = now();

            $existingColumns = Schema::getColumnListing('assets');
            $safeData = array_intersect_key($validated, array_flip($existingColumns));

            $assetId = DB::table('assets')->insertGetId($safeData);

            // Automated Capitalization Journal Entry
            if (Schema::hasTable('account_ledgers')) {
                $capitalizedCost = (float) ($validated['capitalized_cost'] ?? ($validated['purchase_cost'] ?? 0));

                if (!empty($validated['asset_account_id'])) {
                    // Debit: Fixed Asset Account
                    DB::table('account_ledgers')->insert([
                        'chart_of_account_id' => $validated['asset_account_id'],
                        'reference_type'      => 'asset_procurement',
                        'reference_id'        => $assetId,
                        'reference_number'    => $validated['asset_code'] ?? ('AST-' . $assetId),
                        'date'                => $validated['purchase_date'] ?? date('Y-m-d'),
                        'debit'               => $capitalizedCost,
                        'credit'              => 0,
                        'description'         => 'Fixed Asset Capitalization: ' . ($validated['name'] ?? 'Asset'),
                        'branch_id'           => $validated['branch_id'] ?? null,
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ]);

                    // Credit: Vendor Clearing or Cash/Bank
                    $creditAccount = !empty($validated['purchase_id']) ? ($validated['supplier_id'] ?? 1) : 1;
                    DB::table('account_ledgers')->insert([
                        'chart_of_account_id' => $creditAccount,
                        'reference_type'      => 'asset_procurement',
                        'reference_id'        => $assetId,
                        'reference_number'    => $validated['asset_code'] ?? ('AST-' . $assetId),
                        'date'                => $validated['purchase_date'] ?? date('Y-m-d'),
                        'debit'               => 0,
                        'credit'              => $capitalizedCost,
                        'description'         => 'Procurement Liability / Cash Outflow for: ' . ($validated['name'] ?? 'Asset'),
                        'branch_id'           => $validated['branch_id'] ?? null,
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ]);
                }
            }

            DB::commit();
            $targetRoute = \Illuminate\Support\Facades\Route::has('admin.procurement-assets.show') 
                ? route('admin.procurement-assets.show', $assetId) 
                : route('admin.asset-procurements.show', $assetId);

            return redirect($targetRoute)->with('success', 'Asset successfully capitalized and recorded.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Capitalization failed: ' . $e->getMessage());
        }
    }

    /**
     * Show asset card and depreciation details.
     */
    public function show($id)
    {
        $query = DB::table('assets')->where('assets.id', $id);

        if (Schema::hasTable('asset_categories')) {
            $query->leftJoin('asset_categories', 'assets.asset_category_id', '=', 'asset_categories.id')
                  ->addSelect('asset_categories.name as category_name');
        }

        if (Schema::hasTable('branches')) {
            $query->leftJoin('branches', 'assets.branch_id', '=', 'branches.id')
                  ->addSelect('branches.name as branch_name');
        }

        if (Schema::hasTable('suppliers')) {
            $query->leftJoin('suppliers', 'assets.supplier_id', '=', 'suppliers.id')
                  ->addSelect('suppliers.name as supplier_name');
        }

        if (Schema::hasTable('purchases')) {
            $numberCol = Schema::hasColumn('purchases', 'purchase_number') ? 'purchases.purchase_number' : (Schema::hasColumn('purchases', 'purchase_no') ? 'purchases.purchase_no' : 'purchases.id');
            $query->leftJoin('purchases', 'assets.purchase_id', '=', 'purchases.id')
                  ->addSelect(DB::raw("COALESCE({$numberCol}, assets.purchase_id) as purchase_number"));
        }

        if (Schema::hasTable('departments')) {
            $query->leftJoin('departments', 'assets.department_id', '=', 'departments.id')
                  ->addSelect('departments.name as department_name');
        }

        $query->leftJoin('users as custodians', 'assets.custodian_id', '=', 'custodians.id')
              ->addSelect('custodians.name as custodian_name')
              ->addSelect('assets.*');

        $asset = $query->first();

        if (!$asset) {
            abort(404, 'Asset not found.');
        }

        $asset->category   = (object)['name' => $asset->category_name ?? 'General Asset'];
        $asset->branch     = (object)['name' => $asset->branch_name ?? 'HQ Branch'];
        $asset->supplier   = (object)['id' => $asset->supplier_id, 'name' => $asset->supplier_name ?? 'Vendor'];
        $asset->department = (object)['name' => $asset->department_name ?? 'Corporate'];
        $asset->custodian  = (object)['name' => $asset->custodian_name ?? 'Unassigned'];
        $asset->purchase   = (object)['purchase_number' => $asset->purchase_number ?? '—'];

        $asset->assetAccount = (object)['name' => 'Fixed Asset Account'];
        $asset->accumulatedDepreciationAccount = (object)['name' => 'Accumulated Depreciation Contra-Asset'];

        return view('admin.procurement.asset_procurements.show', compact('asset'));
    }

    /**
     * Dispose or write off asset.
     */
    public function dispose(Request $request, $id)
    {
        $request->validate([
            'disposal_type'   => 'required|in:sale,write_off',
            'disposal_amount' => 'required|numeric|min:0',
            'disposal_date'   => 'required|date',
            'disposal_notes'  => 'required|string|max:500',
        ]);

        $asset = DB::table('assets')->where('id', $id)->first();
        if (!$asset) {
            abort(404);
        }

        DB::beginTransaction();
        try {
            DB::table('assets')->where('id', $id)->update([
                'status'         => $request->disposal_type === 'sale' ? 'disposed' : 'written_off',
                'disposal_type'  => $request->disposal_type,
                'disposal_value' => $request->disposal_amount,
                'disposal_date'  => $request->disposal_date,
                'notes'          => trim(($asset->notes ?? '') . "\n[Disposal Note]: " . $request->disposal_notes),
                'updated_at'     => now(),
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Asset successfully decommissioned / written off.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Disposal failed: ' . $e->getMessage());
        }
    }

    /**
     * Post periodic depreciation entry.
     */
    public function depreciate(Request $request, $id)
    {
        $request->validate([
            'depreciation_amount' => 'required|numeric|gt:0',
            'posting_date'        => 'required|date',
        ]);

        $asset = DB::table('assets')->where('id', $id)->first();
        if (!$asset) {
            abort(404);
        }

        $newAccum = (float) ($asset->accumulated_depreciation ?? 0) + (float) $request->depreciation_amount;

        DB::table('assets')->where('id', $id)->update([
            'accumulated_depreciation' => $newAccum,
            'updated_at'               => now(),
        ]);

        return redirect()->back()->with('success', 'Depreciation expense posted successfully.');
    }

    /**
     * Transfer physical asset location or custodian.
     */
    public function transfer(Request $request, $id)
    {
        $request->validate([
            'branch_id'     => 'nullable',
            'department_id' => 'nullable',
            'custodian_id'  => 'nullable',
            'location'      => 'nullable|string|max:255',
        ]);

        DB::table('assets')->where('id', $id)->update(array_merge(
            $request->only(['branch_id', 'department_id', 'custodian_id', 'location']),
            ['updated_at' => now()]
        ));

        return redirect()->back()->with('success', 'Asset location and custody updated successfully.');
    }
}