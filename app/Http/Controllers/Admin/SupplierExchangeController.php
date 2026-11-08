<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Http\Requests\StoreSupplierExchangeRequest;

class SupplierExchangeController extends Controller
{
    /**
     * Display a listing of Supplier Product Exchanges (Swaps).
     */
    public function index(Request $request)
    {
        $query = DB::table('supplier_exchanges');

        if (Schema::hasTable('suppliers')) {
            $query->leftJoin('suppliers', 'supplier_exchanges.supplier_id', '=', 'suppliers.id')
                  ->addSelect('suppliers.name as supplier_name', 'suppliers.company_name as supplier_company');
        }

        if (Schema::hasTable('warehouses')) {
            $query->leftJoin('warehouses', 'supplier_exchanges.warehouse_id', '=', 'warehouses.id')
                  ->addSelect('warehouses.name as warehouse_name');
        }

        if (Schema::hasTable('branches')) {
            $query->leftJoin('branches', 'supplier_exchanges.branch_id', '=', 'branches.id')
                  ->addSelect('branches.name as branch_name');
        }

        $query->addSelect('supplier_exchanges.*');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('supplier_exchanges.exchange_number', 'like', "%{$search}%");
                if (Schema::hasColumn('supplier_exchanges', 'reference_no')) {
                    $q->orWhere('supplier_exchanges.reference_no', 'like', "%{$search}%");
                }
                if (Schema::hasTable('suppliers')) {
                    $q->orWhere('suppliers.name', 'like', "%{$search}%");
                }
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_exchanges.supplier_id', $request->get('supplier_id'));
        }

        if ($request->filled('warehouse_id')) {
            $query->where('supplier_exchanges.warehouse_id', $request->get('warehouse_id'));
        }

        if ($request->filled('status')) {
            $query->where('supplier_exchanges.status', $request->get('status'));
        }

        if ($request->filled('exchange_date')) {
            $query->whereDate('supplier_exchanges.exchange_date', $request->get('exchange_date'));
        }

        $exchanges = $query->orderBy('supplier_exchanges.id', 'desc')->paginate(15)->withQueryString();

        foreach ($exchanges as $ex) {
            $outCol = $ex->outward_total ?? ($ex->returned_total_value ?? 0);
            $inCol  = $ex->inward_total ?? ($ex->replacement_total_value ?? 0);
            $diffCol = $ex->differential_amount ?? ($ex->price_difference ?? ($inCol - $outCol));

            $ex->outward_total       = (float) $outCol;
            $ex->inward_total        = (float) $inCol;
            $ex->differential_amount = (float) $diffCol;
            $ex->supplier            = (object)[
                'id'           => $ex->supplier_id,
                'name'         => $ex->supplier_name ?? '—',
                'company_name' => $ex->supplier_company ?? '',
            ];
            $ex->warehouse           = (object)[
                'name' => $ex->warehouse_name ?? 'Central Warehouse',
            ];
            $ex->branch              = (object)[
                'name' => $ex->branch_name ?? 'HQ Branch',
            ];
        }

        // Defensive Metrics Summary Calculation
        $totalExchangesCount = DB::table('supplier_exchanges')->count();

        $outwardField = Schema::hasColumn('supplier_exchanges', 'outward_total') 
            ? 'outward_total' 
            : (Schema::hasColumn('supplier_exchanges', 'returned_total_value') ? 'returned_total_value' : null);

        $inwardField = Schema::hasColumn('supplier_exchanges', 'inward_total') 
            ? 'inward_total' 
            : (Schema::hasColumn('supplier_exchanges', 'replacement_total_value') ? 'replacement_total_value' : null);

        $totalOutwardValue = $outwardField 
            ? (DB::table('supplier_exchanges')->where('status', '!=', 'cancelled')->sum($outwardField) ?? 0) 
            : 0;

        $totalInwardValue  = $inwardField 
            ? (DB::table('supplier_exchanges')->where('status', '!=', 'cancelled')->sum($inwardField) ?? 0) 
            : 0;

        $pendingCount  = DB::table('supplier_exchanges')->where('status', 'pending')->count();
        $approvedCount = DB::table('supplier_exchanges')->where('status', 'approved')->count();

        $suppliers  = Schema::hasTable('suppliers') ? DB::table('suppliers')->where('status', 'active')->get() : collect([]);
        $warehouses = Schema::hasTable('warehouses') ? DB::table('warehouses')->get() : collect([]);

        // Pass both $supplierExchanges and $exchanges to satisfy any view syntax
        return view('admin.procurement.supplier_exchanges.index', [
            'supplierExchanges'   => $exchanges,
            'exchanges'           => $exchanges,
            'totalExchangesCount' => $totalExchangesCount,
            'totalOutwardValue'   => $totalOutwardValue,
            'totalInwardValue'    => $totalInwardValue,
            'pendingCount'        => $pendingCount,
            'approvedCount'       => $approvedCount,
            'suppliers'           => $suppliers,
            'warehouses'          => $warehouses,
        ]);
    }

    /**
     * Show the form for creating a new product exchange / swap note.
     */
    public function create(Request $request)
    {
        $suppliers    = Schema::hasTable('suppliers') ? DB::table('suppliers')->where('status', 'active')->get() : collect([]);
        $warehouses   = Schema::hasTable('warehouses') ? DB::table('warehouses')->get() : collect([]);
        $branches     = Schema::hasTable('branches') ? DB::table('branches')->get() : collect([]);
        $bankAccounts = Schema::hasTable('bank_accounts') ? DB::table('bank_accounts')->get() : collect([]);
        $products     = Schema::hasTable('products') 
            ? DB::table('products')->select('id', 'title as name', 'sku', 'purchase_cost', 'stock')->limit(100)->get() 
            : collect([]);

        return view('admin.procurement.supplier_exchanges.create', compact(
            'suppliers',
            'warehouses',
            'branches',
            'bankAccounts',
            'products'
        ));
    }

    /**
     * Store a newly created Product Exchange voucher.
     */
    public function store(StoreSupplierExchangeRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $attachmentPath = null;
            if ($request->hasFile('attachment_file')) {
                $attachmentPath = $request->file('attachment_file')->store('procurement/exchanges', 'public');
            }

            $outwardItems = $request->input('outward_items', []);
            $inwardItems  = $request->input('inward_items', []);

            $outwardTotal = 0;
            foreach ($outwardItems as $item) {
                $qty  = (float) ($item['quantity'] ?? 1);
                $cost = (float) ($item['unit_cost'] ?? 0);
                $outwardTotal += ($qty * $cost);
            }

            $inwardTotal = 0;
            foreach ($inwardItems as $item) {
                $qty  = (float) ($item['quantity'] ?? 1);
                $cost = (float) ($item['unit_cost'] ?? 0);
                $inwardTotal += ($qty * $cost);
            }

            $differentialAmount = $inwardTotal - $outwardTotal;
            $exchangeNumber = $validated['exchange_number'] ?? ('EXC-' . date('Ymd') . '-' . strtoupper(Str::random(4)));

            $insertData = [
                'exchange_number'         => $exchangeNumber,
                'supplier_id'             => (int) $validated['supplier_id'],
                'warehouse_id'            => (int) $validated['warehouse_id'],
                'branch_id'               => !empty($validated['branch_id']) ? (int)$validated['branch_id'] : null,
                'exchange_date'           => $validated['exchange_date'] ?? date('Y-m-d'),
                'reference_no'            => $validated['reference_no'] ?? null,
                'settlement_type'         => $validated['settlement_type'] ?? 'ledger_adjustment',
                'difference_settlement'   => $validated['settlement_type'] ?? 'ledger_adjustment',
                'bank_account_id'         => !empty($validated['bank_account_id']) ? (int)$validated['bank_account_id'] : null,
                'outward_total'           => $outwardTotal,
                'returned_total_value'    => $outwardTotal,
                'inward_total'            => $inwardTotal,
                'replacement_total_value' => $inwardTotal,
                'differential_amount'     => $differentialAmount,
                'price_difference'        => $differentialAmount,
                'status'                  => $validated['status'] ?? 'approved',
                'attachment_file'         => $attachmentPath,
                'notes'                   => $validated['notes'] ?? null,
                'created_by'              => auth()->id() ?? 1,
                'created_at'              => now(),
                'updated_at'              => now(),
            ];

            $existingColumns = Schema::getColumnListing('supplier_exchanges');
            $safeData = array_intersect_key($insertData, array_flip($existingColumns));

            $exchangeId = DB::table('supplier_exchanges')->insertGetId($safeData);

            // Save Outward items (Stock Out / Return to Vendor)
            $itemCols = Schema::getColumnListing('supplier_exchange_items');
            foreach ($outwardItems as $item) {
                $qty  = (float) ($item['quantity'] ?? 1);
                $cost = (float) ($item['unit_cost'] ?? 0);
                $lineTotal = $qty * $cost;

                $outItemData = [
                    'supplier_exchange_id' => $exchangeId,
                    'item_type'            => 'outward',
                    'product_id'           => (int) ($item['product_id'] ?? 1),
                    'variant_id'           => !empty($item['variant_id']) ? (int)$item['variant_id'] : null,
                    'sku'                  => $item['sku'] ?? null,
                    'batch_no'             => $item['batch_no'] ?? null,
                    'quantity'             => $qty,
                    'unit_cost'            => $cost,
                    'total_cost'           => $lineTotal,
                    'total'                => $lineTotal,
                    'reason'               => $item['reason'] ?? 'defective',
                    'remarks'              => $item['reason'] ?? 'defective',
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ];

                $safeOutData = array_intersect_key($outItemData, array_flip($itemCols));
                DB::table('supplier_exchange_items')->insert($safeOutData);
            }

            // Save Inward items (Stock In / Replacement from Vendor)
            foreach ($inwardItems as $item) {
                $qty  = (float) ($item['quantity'] ?? 1);
                $cost = (float) ($item['unit_cost'] ?? 0);
                $lineTotal = $qty * $cost;

                $inItemData = [
                    'supplier_exchange_id' => $exchangeId,
                    'item_type'            => 'inward',
                    'product_id'           => (int) ($item['product_id'] ?? 1),
                    'variant_id'           => !empty($item['variant_id']) ? (int)$item['variant_id'] : null,
                    'sku'                  => $item['sku'] ?? null,
                    'batch_no'             => $item['batch_no'] ?? null,
                    'expiry_date'          => !empty($item['expiry_date']) ? $item['expiry_date'] : null,
                    'quantity'             => $qty,
                    'unit_cost'            => $cost,
                    'total_cost'           => $lineTotal,
                    'total'                => $lineTotal,
                    'reason'               => null,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ];

                $safeInData = array_intersect_key($inItemData, array_flip($itemCols));
                DB::table('supplier_exchange_items')->insert($safeInData);
            }

            // Execute inventory swap & balance adjustments if approved
            if (($insertData['status']) === 'approved') {
                $this->processApprovedExchange($exchangeId);
            }

            DB::commit();
            return redirect()->route('admin.supplier-exchanges.show', $exchangeId)
                ->with('success', "Exchange voucher #{$exchangeNumber} successfully recorded.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to store exchange voucher: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified Supplier Product Exchange voucher.
     */
    public function show($id)
    {
        $query = DB::table('supplier_exchanges')->where('supplier_exchanges.id', $id);

        if (Schema::hasTable('suppliers')) {
            $query->leftJoin('suppliers', 'supplier_exchanges.supplier_id', '=', 'suppliers.id')
                  ->addSelect(
                      'suppliers.name as supplier_name',
                      'suppliers.company_name as supplier_company',
                      'suppliers.phone as supplier_phone',
                      'suppliers.email as supplier_email',
                      'suppliers.address as supplier_address'
                  );
        }

        if (Schema::hasTable('warehouses')) {
            $query->leftJoin('warehouses', 'supplier_exchanges.warehouse_id', '=', 'warehouses.id')
                  ->addSelect('warehouses.name as warehouse_name');
        }

        if (Schema::hasTable('branches')) {
            $query->leftJoin('branches', 'supplier_exchanges.branch_id', '=', 'branches.id')
                  ->addSelect('branches.name as branch_name');
        }

        if (Schema::hasTable('bank_accounts')) {
            $query->leftJoin('bank_accounts', 'supplier_exchanges.bank_account_id', '=', 'bank_accounts.id')
                  ->addSelect('bank_accounts.account_name as bank_account_name');
        }

        $query->addSelect('supplier_exchanges.*');
        $exchange = $query->first();

        if (!$exchange) {
            abort(404, 'Supplier exchange voucher not found.');
        }

        $itemsQuery = DB::table('supplier_exchange_items')
            ->where('supplier_exchange_items.supplier_exchange_id', $id);

        if (Schema::hasTable('products')) {
            $itemsQuery->leftJoin('products', 'supplier_exchange_items.product_id', '=', 'products.id')
                       ->addSelect('products.title as product_name', 'products.sku as product_sku');
        }

        if (Schema::hasTable('product_variants')) {
            $itemsQuery->leftJoin('product_variants', 'supplier_exchange_items.variant_id', '=', 'product_variants.id')
                       ->addSelect('product_variants.color as variant_color', 'product_variants.size as variant_size');
        }

        $itemsQuery->addSelect('supplier_exchange_items.*');
        $items = $itemsQuery->get();

        $outwardItems = $items->filter(fn($item) => in_array($item->item_type, ['outward', 'returned_out']));
        $inwardItems  = $items->filter(fn($item) => in_array($item->item_type, ['inward', 'replacement_in']));

        $outCol = $exchange->outward_total ?? ($exchange->returned_total_value ?? 0);
        $inCol  = $exchange->inward_total ?? ($exchange->replacement_total_value ?? 0);
        $diffCol = $exchange->differential_amount ?? ($exchange->price_difference ?? ($inCol - $outCol));

        $exchange->outward_total       = (float) $outCol;
        $exchange->inward_total        = (float) $inCol;
        $exchange->differential_amount = (float) $diffCol;

        // View object wrappers
        $exchange->supplier = (object)[
            'id'           => $exchange->supplier_id,
            'name'         => $exchange->supplier_name ?? '—',
            'company_name' => $exchange->supplier_company ?? '',
            'phone'        => $exchange->supplier_phone ?? '—',
            'email'        => $exchange->supplier_email ?? '—',
            'address'      => $exchange->supplier_address ?? '—',
        ];
        $exchange->warehouse    = (object)['name' => $exchange->warehouse_name ?? 'Central Warehouse'];
        $exchange->branch       = (object)['name' => $exchange->branch_name ?? 'HQ Branch'];
        $exchange->bankAccount  = (object)['account_name' => $exchange->bank_account_name ?? 'Cash in Hand'];
        $exchange->outwardItems = $outwardItems;
        $exchange->inwardItems  = $inwardItems;

        return view('admin.procurement.supplier_exchanges.show', [
            'supplierExchange' => $exchange,
            'exchange'         => $exchange,
            'outwardItems'     => $outwardItems,
            'inwardItems'      => $inwardItems,
        ]);
    }

    /**
     * Approve exchange note, swap physical inventory, and settle balance difference.
     */
    public function approve(Request $request, $id)
    {
        $exchange = DB::table('supplier_exchanges')->where('id', $id)->first();
        if (!$exchange) {
            return redirect()->back()->with('error', 'Exchange voucher not found.');
        }

        if ($exchange->status === 'approved') {
            return redirect()->back()->with('warning', 'This exchange note has already been approved.');
        }

        if ($exchange->status === 'cancelled') {
            return redirect()->back()->with('error', 'Cancelled exchange notes cannot be approved.');
        }

        DB::beginTransaction();
        try {
            DB::table('supplier_exchanges')->where('id', $id)->update([
                'status'     => 'approved',
                'updated_at' => now(),
            ]);

            $this->processApprovedExchange($id);

            DB::commit();
            return redirect()->back()->with('success', "Exchange #{$exchange->exchange_number} approved. Inventory swapped and balances adjusted.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Cancel / Void Exchange note and reverse inventory/financial impacts.
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancel_reason' => 'required|string|max:500',
        ]);

        $exchange = DB::table('supplier_exchanges')->where('id', $id)->first();
        if (!$exchange) {
            return redirect()->back()->with('error', 'Exchange voucher not found.');
        }

        if ($exchange->status === 'cancelled') {
            return redirect()->back()->with('warning', 'This exchange note has already been voided.');
        }

        DB::beginTransaction();
        try {
            if ($exchange->status === 'approved') {
                $items = DB::table('supplier_exchange_items')->where('supplier_exchange_id', $id)->get();

                // Reverse Stock Movements
                foreach ($items as $item) {
                    $qty = (float) $item->quantity;

                    if (in_array($item->item_type, ['outward', 'returned_out'])) {
                        // Restore deducted stock
                        DB::table('products')->where('id', $item->product_id)->increment('stock', $qty);
                        if ($item->variant_id && Schema::hasTable('product_variants')) {
                            DB::table('product_variants')->where('id', $item->variant_id)->increment('stock', $qty);
                        }
                    } else {
                        // Deduct added replacement stock
                        DB::table('products')->where('id', $item->product_id)->decrement('stock', $qty);
                        if ($item->variant_id && Schema::hasTable('product_variants')) {
                            DB::table('product_variants')->where('id', $item->variant_id)->decrement('stock', $qty);
                        }
                    }
                }

                // Reverse Differential balance on supplier ledger
                $delta = (float) ($exchange->differential_amount ?? ($exchange->price_difference ?? 0));
                if (Schema::hasTable('suppliers') && Schema::hasColumn('suppliers', 'current_balance') && $delta != 0) {
                    DB::table('suppliers')->where('id', $exchange->supplier_id)->decrement('current_balance', $delta);
                }

                // Reverse General Ledger entry
                if (Schema::hasTable('account_ledgers')) {
                    DB::table('account_ledgers')->where([
                        'reference_type' => 'supplier_exchange',
                        'reference_id'   => $id,
                    ])->delete();
                }
            }

            $updateData = [
                'status'     => 'cancelled',
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('supplier_exchanges', 'cancelled_reason')) {
                $updateData['cancelled_reason'] = $request->cancel_reason;
            }
            if (Schema::hasColumn('supplier_exchanges', 'notes')) {
                $updateData['notes'] = trim(($exchange->notes ?? '') . "\n[Void Reason]: " . $request->cancel_reason);
            }

            DB::table('supplier_exchanges')->where('id', $id)->update($updateData);

            DB::commit();
            return redirect()->back()->with('success', "Exchange #{$exchange->exchange_number} has been voided successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Cancellation failed: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Execute stock swap and ledger postings for approved exchange note.
     */
    protected function processApprovedExchange($exchangeId)
    {
        $exchange = DB::table('supplier_exchanges')->where('id', $exchangeId)->first();
        if (!$exchange) return;

        $items = DB::table('supplier_exchange_items')->where('supplier_exchange_id', $exchangeId)->get();

        foreach ($items as $item) {
            $qty = (float) $item->quantity;
            if ($qty <= 0) continue;

            $isOutward = in_array($item->item_type, ['outward', 'returned_out']);

            if ($isOutward) {
                // Deduct defective/returned stock
                DB::table('products')->where('id', $item->product_id)->decrement('stock', $qty);
                if ($item->variant_id && Schema::hasTable('product_variants')) {
                    DB::table('product_variants')->where('id', $item->variant_id)->decrement('stock', $qty);
                }

                if (Schema::hasTable('inventory_movements')) {
                    DB::table('inventory_movements')->insert([
                        'product_id'     => $item->product_id,
                        'variant_id'     => $item->variant_id,
                        'movement_type'  => 'exchange_out',
                        'reference_id'   => $exchangeId,
                        'quantity_change'=> -$qty,
                        'unit_cost'      => $item->unit_cost ?? 0,
                        'notes'          => 'Supplier Product Exchange Outward #' . $exchange->exchange_number,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }
            } else {
                // Add replacement stock
                DB::table('products')->where('id', $item->product_id)->increment('stock', $qty);
                if ($item->variant_id && Schema::hasTable('product_variants')) {
                    DB::table('product_variants')->where('id', $item->variant_id)->increment('stock', $qty);
                }

                if (Schema::hasTable('inventory_movements')) {
                    DB::table('inventory_movements')->insert([
                        'product_id'     => $item->product_id,
                        'variant_id'     => $item->variant_id,
                        'movement_type'  => 'exchange_in',
                        'reference_id'   => $exchangeId,
                        'quantity_change'=> $qty,
                        'unit_cost'      => $item->unit_cost ?? 0,
                        'notes'          => 'Supplier Product Exchange Inward Replacement #' . $exchange->exchange_number,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }
            }
        }

        // Adjust supplier running balance for the differential value
        $delta = (float) ($exchange->differential_amount ?? ($exchange->price_difference ?? 0));
        if (Schema::hasTable('suppliers') && Schema::hasColumn('suppliers', 'current_balance') && $delta != 0) {
            DB::table('suppliers')->where('id', $exchange->supplier_id)->increment('current_balance', $delta);
        }

        // Record in General Ledger
        if (Schema::hasTable('account_ledgers') && $delta != 0) {
            $absDelta = abs($delta);
            $isDueIncrease = ($delta > 0);

            DB::table('account_ledgers')->insert([
                'chart_of_account_id' => $exchange->supplier_id,
                'reference_type'      => 'supplier_exchange',
                'reference_id'        => $exchangeId,
                'reference_number'    => $exchange->exchange_number,
                'date'                => $exchange->exchange_date,
                'debit'               => $isDueIncrease ? 0 : $absDelta,
                'credit'              => $isDueIncrease ? $absDelta : 0,
                'description'         => 'Product Exchange Valuation Delta #' . $exchange->exchange_number,
                'branch_id'           => $exchange->branch_id ?? null,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }
    }
}