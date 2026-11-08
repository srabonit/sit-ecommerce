<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Requests\StorePurchaseReceiptRequest;

class PurchaseReceiptController extends Controller
{
    /**
     * Display a listing of Goods Received Notes (GRN).
     */
    public function index(Request $request)
    {
        if (!Schema::hasTable('purchase_receipts')) {
            $purchaseReceipts = new LengthAwarePaginator([], 0, 15);
            $totalDeliveredQty = 0;
            $totalAcceptedQty  = 0;
            $totalRejectedQty  = 0;
            $pendingQcCount    = 0;
            $suppliers         = collect([]);
            $warehouses        = collect([]);

            return view('admin.procurement.purchase_receipts.index', compact(
                'purchaseReceipts',
                'totalDeliveredQty',
                'totalAcceptedQty',
                'totalRejectedQty',
                'pendingQcCount',
                'suppliers',
                'warehouses'
            ))->with(['receipts' => $purchaseReceipts]);
        }

        $query = DB::table('purchase_receipts');

        if (Schema::hasTable('suppliers')) {
            $query->leftJoin('suppliers', 'purchase_receipts.supplier_id', '=', 'suppliers.id')
                  ->addSelect('suppliers.name as supplier_name', 'suppliers.company_name as supplier_company');
        }

        if (Schema::hasTable('warehouses')) {
            $query->leftJoin('warehouses', 'purchase_receipts.warehouse_id', '=', 'warehouses.id')
                  ->addSelect('warehouses.name as warehouse_name');
        }

        if (Schema::hasTable('branches')) {
            $query->leftJoin('branches', 'purchase_receipts.branch_id', '=', 'branches.id')
                  ->addSelect('branches.name as branch_name');
        }

        if (Schema::hasTable('purchase_orders')) {
            $query->leftJoin('purchase_orders', 'purchase_receipts.purchase_order_id', '=', 'purchase_orders.id')
                  ->addSelect('purchase_orders.po_number');
        }

        $query->addSelect('purchase_receipts.*');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('purchase_receipts.receipt_number', 'like', "%{$search}%");
                if (Schema::hasColumn('purchase_receipts', 'challan_no')) {
                    $q->orWhere('purchase_receipts.challan_no', 'like', "%{$search}%");
                }
                if (Schema::hasColumn('purchase_receipts', 'supplier_challan_no')) {
                    $q->orWhere('purchase_receipts.supplier_challan_no', 'like', "%{$search}%");
                }
                if (Schema::hasTable('purchase_orders')) {
                    $q->orWhere('purchase_orders.po_number', 'like', "%{$search}%");
                }
                if (Schema::hasTable('suppliers')) {
                    $q->orWhere('suppliers.name', 'like', "%{$search}%");
                }
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('purchase_receipts.supplier_id', $request->get('supplier_id'));
        }

        if ($request->filled('warehouse_id')) {
            $query->where('purchase_receipts.warehouse_id', $request->get('warehouse_id'));
        }

        if ($request->filled('status')) {
            $statusVal = $request->get('status');
            $query->where(function ($sq) use ($statusVal) {
                if (Schema::hasColumn('purchase_receipts', 'status')) {
                    $sq->where('purchase_receipts.status', $statusVal);
                }
                if (Schema::hasColumn('purchase_receipts', 'receiving_status')) {
                    $sq->orWhere('purchase_receipts.receiving_status', $statusVal);
                }
            });
        }

        if ($request->filled('receipt_date')) {
            $dateVal = $request->get('receipt_date');
            $query->where(function ($dq) use ($dateVal) {
                if (Schema::hasColumn('purchase_receipts', 'receipt_date')) {
                    $dq->whereDate('purchase_receipts.receipt_date', $dateVal);
                }
                if (Schema::hasColumn('purchase_receipts', 'receiving_date')) {
                    $dq->orWhereDate('purchase_receipts.receiving_date', $dateVal);
                }
            });
        }

        $receipts = $query->orderBy('purchase_receipts.id', 'desc')->paginate(15)->withQueryString();

        // Calculate line items quantities per receipt
        foreach ($receipts as $r) {
            $r->supplier = (object)[
                'id'           => $r->supplier_id,
                'name'         => $r->supplier_name ?? '—',
                'company_name' => $r->supplier_company ?? '',
            ];
            $r->warehouse = (object)['name' => $r->warehouse_name ?? 'Central'];
            $r->branch    = (object)['name' => $r->branch_name ?? 'HQ Branch'];
            $r->purchaseOrder = (object)['po_number' => $r->po_number ?? ''];

            $r->challan_no = $r->challan_no ?? ($r->supplier_challan_no ?? '—');
            $r->receipt_date = $r->receipt_date ?? ($r->receiving_date ?? date('Y-m-d'));
            $r->status = $r->status ?? ($r->receiving_status ?? 'received');

            if (Schema::hasTable('purchase_receipt_items')) {
                $sums = DB::table('purchase_receipt_items')->where('purchase_receipt_id', $r->id)->selectRaw("
                    COALESCE(SUM(CASE WHEN delivered_quantity > 0 THEN delivered_quantity ELSE received_quantity END), 0) as delivered_sum,
                    COALESCE(SUM(accepted_quantity), 0) as accepted_sum,
                    COALESCE(SUM(rejected_quantity), 0) as rejected_sum
                ")->first();

                $r->items_sum_delivered_quantity = (float) ($sums->delivered_sum ?? 0);
                $r->items_sum_accepted_quantity  = (float) ($sums->accepted_sum ?? 0);
                $r->items_sum_rejected_quantity  = (float) ($sums->rejected_sum ?? 0);
            } else {
                $r->items_sum_delivered_quantity = 0;
                $r->items_sum_accepted_quantity  = 0;
                $r->items_sum_rejected_quantity  = 0;
            }
        }

        // Metrics Summary Calculations
        $totalDeliveredQty = 0;
        $totalAcceptedQty  = 0;
        $totalRejectedQty  = 0;

        if (Schema::hasTable('purchase_receipt_items')) {
            $totalDeliveredQty = (float) (DB::table('purchase_receipt_items')->sum(
                Schema::hasColumn('purchase_receipt_items', 'delivered_quantity') ? 'delivered_quantity' : 'received_quantity'
            ) ?? 0);
            $totalAcceptedQty  = (float) (DB::table('purchase_receipt_items')->sum('accepted_quantity') ?? 0);
            $totalRejectedQty  = (float) (DB::table('purchase_receipt_items')->sum('rejected_quantity') ?? 0);
        }

        $pendingQcCount = 0;
        if (Schema::hasColumn('purchase_receipts', 'status')) {
            $pendingQcCount = DB::table('purchase_receipts')->whereIn('status', ['pending', 'pending_qc', 'under_inspection'])->count();
        } elseif (Schema::hasColumn('purchase_receipts', 'receiving_status')) {
            $pendingQcCount = DB::table('purchase_receipts')->whereIn('receiving_status', ['under_inspection', 'draft'])->count();
        }

        $suppliers  = Schema::hasTable('suppliers') ? DB::table('suppliers')->where('status', 'active')->get() : collect([]);
        $warehouses = Schema::hasTable('warehouses') ? DB::table('warehouses')->get() : collect([]);

        // Pass both $purchaseReceipts and $receipts
        return view('admin.procurement.purchase_receipts.index', [
            'purchaseReceipts'    => $receipts,
            'receipts'            => $receipts,
            'totalDeliveredQty'   => $totalDeliveredQty,
            'totalAcceptedQty'    => $totalAcceptedQty,
            'totalRejectedQty'    => $totalRejectedQty,
            'pendingQcCount'      => $pendingQcCount,
            'suppliers'           => $suppliers,
            'warehouses'          => $warehouses,
        ]);
    }

    /**
     * Show the form for creating a new GRN.
     */
    public function create(Request $request)
    {
        $suppliers  = Schema::hasTable('suppliers') ? DB::table('suppliers')->where('status', 'active')->get() : collect([]);
        $warehouses = Schema::hasTable('warehouses') ? DB::table('warehouses')->get() : collect([]);
        $branches   = Schema::hasTable('branches') ? DB::table('branches')->get() : collect([]);

        $purchaseOrders = collect([]);
        if (Schema::hasTable('purchase_orders')) {
            $purchaseOrders = DB::table('purchase_orders')
                ->whereIn('status', ['approved', 'partial', 'pending'])
                ->select('id', 'po_number', 'supplier_id', 'total_amount', 'order_date')
                ->orderBy('id', 'desc')
                ->get();
        }

        $selectedPo = null;
        $poItems = collect([]);
        if ($request->filled('purchase_order_id')) {
            $selectedPo = DB::table('purchase_orders')->where('id', $request->purchase_order_id)->first();
            if ($selectedPo && Schema::hasTable('purchase_order_items')) {
                $poItems = DB::table('purchase_order_items')
                    ->leftJoin('products', 'purchase_order_items.product_id', '=', 'products.id')
                    ->where('purchase_order_items.purchase_order_id', $selectedPo->id)
                    ->select(
                        'purchase_order_items.*',
                        'products.name as product_name',
                        'products.sku as product_sku'
                    )
                    ->get();
            }
        }

        return view('admin.procurement.purchase_receipts.create', compact(
            'suppliers',
            'warehouses',
            'branches',
            'purchaseOrders',
            'selectedPo',
            'poItems'
        ));
    }

    /**
     * Store a newly created GRN in storage.
     */
    public function store(StorePurchaseReceiptRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $attachmentPath = null;
            if ($request->hasFile('attachment_file')) {
                $attachmentPath = $request->file('attachment_file')->store('procurement/grn_attachments', 'public');
            }

            $items = $request->input('items', []);
            $receiptNumber = $validated['receipt_number'] ?? ('GRN-' . date('Ymd') . '-' . strtoupper(Str::random(4)));
            $challanNo = $validated['challan_no'] ?? ($request->input('challan_no') ?? null);
            $receiptDate = $validated['receipt_date'] ?? ($request->input('receipt_date') ?? date('Y-m-d'));
            $status = $validated['status'] ?? ($request->input('status') ?? 'received');

            $insertData = [
                'receipt_number'      => $receiptNumber,
                'purchase_order_id'   => !empty($validated['purchase_order_id']) ? (int)$validated['purchase_order_id'] : null,
                'supplier_id'         => (int) $validated['supplier_id'],
                'warehouse_id'        => (int) $validated['warehouse_id'],
                'branch_id'           => !empty($validated['branch_id']) ? (int)$validated['branch_id'] : null,
                'challan_no'          => $challanNo,
                'supplier_challan_no' => $challanNo,
                'receipt_date'        => $receiptDate,
                'receiving_date'      => $receiptDate,
                'status'              => $status,
                'receiving_status'    => ($status === 'received' ? 'accepted' : 'under_inspection'),
                'received_by'         => auth()->id() ?? 1,
                'inspected_by'        => auth()->id() ?? 1,
                'vehicle_number'      => $request->input('vehicle_number'),
                'attachment_file'     => $attachmentPath,
                'notes'               => $request->input('notes'),
                'inspection_notes'    => $request->input('notes'),
                'created_at'          => now(),
                'updated_at'          => now(),
            ];

            $existingColumns = Schema::getColumnListing('purchase_receipts');
            $safeData = array_intersect_key($insertData, array_flip($existingColumns));

            $receiptId = DB::table('purchase_receipts')->insertGetId($safeData);

            // Save line items
            $itemCols = Schema::getColumnListing('purchase_receipt_items');
            foreach ($items as $item) {
                $delivered = (float) ($item['delivered_quantity'] ?? ($item['received_qty'] ?? ($item['quantity'] ?? 0)));
                $accepted  = (float) ($item['accepted_quantity'] ?? $delivered);
                $rejected  = (float) ($item['rejected_quantity'] ?? ($item['rejected_qty'] ?? max(0, $delivered - $accepted)));
                $cost      = (float) ($item['unit_cost'] ?? 0);

                $lineData = [
                    'purchase_receipt_id'    => $receiptId,
                    'purchase_order_item_id' => !empty($item['po_item_id']) ? (int)$item['po_item_id'] : null,
                    'product_id'             => (int) $item['product_id'],
                    'variant_id'             => !empty($item['variant_id']) ? (int)$item['variant_id'] : null,
                    'sku'                    => $item['sku'] ?? null,
                    'batch_no'               => $item['batch_no'] ?? null,
                    'expiry_date'            => !empty($item['expiry_date']) ? $item['expiry_date'] : null,
                    'delivered_quantity'     => $delivered,
                    'received_quantity'      => $delivered,
                    'received_qty'           => $delivered,
                    'accepted_quantity'      => $accepted,
                    'rejected_quantity'      => $rejected,
                    'rejected_qty'           => $rejected,
                    'unit_cost'              => $cost,
                    'total_cost'             => $accepted * $cost,
                    'remarks'                => $item['remarks'] ?? null,
                    'rejection_reason'       => $item['remarks'] ?? ($rejected > 0 ? 'QC Discrepancy' : null),
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ];

                $safeLineData = array_intersect_key($lineData, array_flip($itemCols));
                DB::table('purchase_receipt_items')->insert($safeLineData);
            }

            // Post inventory stock inward if accepted
            if ($status === 'received' || $status === 'accepted') {
                $this->processStockInward($receiptId, (int)$validated['warehouse_id'], $items);
            }

            // Sync Purchase Order status if linked
            if (!empty($validated['purchase_order_id'])) {
                $this->syncPurchaseOrderStatus((int)$validated['purchase_order_id']);
            }

            DB::commit();
            return redirect()->route('admin.purchase-receipts.show', $receiptId)
                ->with('success', "Goods Received Note #{$receiptNumber} recorded successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to store GRN: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified GRN details.
     */
    public function show($id)
    {
        $query = DB::table('purchase_receipts')->where('purchase_receipts.id', $id);

        if (Schema::hasTable('suppliers')) {
            $query->leftJoin('suppliers', 'purchase_receipts.supplier_id', '=', 'suppliers.id')
                  ->addSelect(
                      'suppliers.name as supplier_name',
                      'suppliers.company_name as supplier_company',
                      'suppliers.phone as supplier_phone',
                      'suppliers.email as supplier_email',
                      'suppliers.address as supplier_address'
                  );
        }

        if (Schema::hasTable('warehouses')) {
            $query->leftJoin('warehouses', 'purchase_receipts.warehouse_id', '=', 'warehouses.id')
                  ->addSelect('warehouses.name as warehouse_name');
        }

        if (Schema::hasTable('branches')) {
            $query->leftJoin('branches', 'purchase_receipts.branch_id', '=', 'branches.id')
                  ->addSelect('branches.name as branch_name');
        }

        if (Schema::hasTable('purchase_orders')) {
            $query->leftJoin('purchase_orders', 'purchase_receipts.purchase_order_id', '=', 'purchase_orders.id')
                  ->addSelect('purchase_orders.po_number');
        }

        $query->addSelect('purchase_receipts.*');
        $receipt = $query->first();

        if (!$receipt) {
            abort(404, 'Goods Received Note not found.');
        }

        $receipt->challan_no = $receipt->challan_no ?? ($receipt->supplier_challan_no ?? '—');
        $receipt->receipt_date = $receipt->receipt_date ?? ($receipt->receiving_date ?? date('Y-m-d'));
        $receipt->status = $receipt->status ?? ($receipt->receiving_status ?? 'received');

        // Fetch line items
        $itemsQuery = DB::table('purchase_receipt_items')
            ->where('purchase_receipt_items.purchase_receipt_id', $id);

        if (Schema::hasTable('products')) {
            $itemsQuery->leftJoin('products', 'purchase_receipt_items.product_id', '=', 'products.id')
                       ->addSelect('products.title as product_name', 'products.sku as product_sku');
        }

        if (Schema::hasTable('product_variants')) {
            $itemsQuery->leftJoin('product_variants', 'purchase_receipt_items.variant_id', '=', 'product_variants.id')
                       ->addSelect('product_variants.color as variant_color', 'product_variants.size as variant_size');
        }

        $itemsQuery->addSelect('purchase_receipt_items.*');
        $items = $itemsQuery->get();

        foreach ($items as $item) {
            $item->product = (object)[
                'name' => $item->product_name ?? ('Product #' . $item->product_id),
                'sku'  => $item->product_sku ?? ($item->sku ?? '—'),
            ];
            $item->variant = !empty($item->variant_id) ? (object)[
                'name' => trim(($item->variant_color ?? '') . ' ' . ($item->variant_size ?? '')),
            ] : null;

            $item->delivered_quantity = (float) ($item->delivered_quantity ?? ($item->received_quantity ?? 0));
            $item->accepted_quantity  = (float) ($item->accepted_quantity ?? 0);
            $item->rejected_quantity  = (float) ($item->rejected_quantity ?? 0);
        }

        // View Object wrappers
        $receipt->supplier = (object)[
            'id'           => $receipt->supplier_id,
            'name'         => $receipt->supplier_name ?? '—',
            'company_name' => $receipt->supplier_company ?? '',
            'phone'        => $receipt->supplier_phone ?? '—',
            'email'        => $receipt->supplier_email ?? '—',
            'address'      => $receipt->supplier_address ?? '—',
        ];
        $receipt->warehouse     = (object)['name' => $receipt->warehouse_name ?? 'Central Warehouse'];
        $receipt->branch        = (object)['name' => $receipt->branch_name ?? 'HQ Branch'];
        $receipt->purchaseOrder = !empty($receipt->po_number) ? (object)['po_number' => $receipt->po_number] : null;
        $receipt->creator       = (object)['name' => 'System Staff'];
        $receipt->items         = $items;
        $receipt->purchases     = collect([]);

        return view('admin.procurement.purchase_receipts.show', [
            'purchaseReceipt' => $receipt,
            'receipt'         => $receipt,
            'items'           => $items,
        ]);
    }

    /**
     * Approve GRN, verify QC, and post inward stock adjustments.
     */
    public function approve(Request $request, $id)
    {
        $receipt = DB::table('purchase_receipts')->where('id', $id)->first();
        if (!$receipt) {
            return redirect()->back()->with('error', 'Goods Received Note not found.');
        }

        if ($receipt->status === 'received' || $receipt->status === 'approved' || ($receipt->receiving_status ?? '') === 'accepted') {
            return redirect()->back()->with('warning', 'This GRN has already been approved and stock was admitted.');
        }

        DB::beginTransaction();
        try {
            $updateData = ['updated_at' => now()];
            if (Schema::hasColumn('purchase_receipts', 'status')) {
                $updateData['status'] = 'received';
            }
            if (Schema::hasColumn('purchase_receipts', 'receiving_status')) {
                $updateData['receiving_status'] = 'accepted';
            }

            DB::table('purchase_receipts')->where('id', $id)->update($updateData);

            $items = DB::table('purchase_receipt_items')
                ->where('purchase_receipt_id', $id)
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray();

            $this->processStockInward($receipt->id, (int)$receipt->warehouse_id, $items);

            if (!empty($receipt->purchase_order_id)) {
                $this->syncPurchaseOrderStatus((int)$receipt->purchase_order_id);
            }

            DB::commit();
            return redirect()->back()->with('success', "GRN #{$receipt->receipt_number} has been approved. Stock updated in warehouse.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Approval failed: ' . $e->getMessage());
        }
    }

    /**
     * AJAX Endpoint: Get items for a specific GRN.
     */
    public function getItemsAjax($id)
    {
        try {
            $items = DB::table('purchase_receipt_items')
                ->leftJoin('products', 'purchase_receipt_items.product_id', '=', 'products.id')
                ->where('purchase_receipt_items.purchase_receipt_id', $id)
                ->select(
                    'purchase_receipt_items.id as receipt_item_id',
                    'purchase_receipt_items.product_id',
                    'purchase_receipt_items.variant_id',
                    'purchase_receipt_items.sku',
                    'purchase_receipt_items.batch_no',
                    'purchase_receipt_items.expiry_date',
                    DB::raw("COALESCE(purchase_receipt_items.accepted_quantity, purchase_receipt_items.received_quantity, 1) as quantity"),
                    'purchase_receipt_items.unit_cost',
                    'products.title as product_name'
                )
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $items,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch GRN items: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper: Process stock addition into inventory tables.
     */
    protected function processStockInward($receiptId, $warehouseId, array $items)
    {
        foreach ($items as $item) {
            $qty = (float) ($item['accepted_quantity'] ?? ($item['received_qty'] ?? ($item['received_quantity'] ?? ($item['delivered_quantity'] ?? 0))));
            if ($qty <= 0) continue;

            $productId = (int) $item['product_id'];
            $variantId = !empty($item['variant_id']) ? (int)$item['variant_id'] : null;

            if (Schema::hasTable('products')) {
                DB::table('products')->where('id', $productId)->increment('stock', $qty);
            }

            if ($variantId && Schema::hasTable('product_variants')) {
                DB::table('product_variants')->where('id', $variantId)->increment('stock', $qty);
            }

            if (Schema::hasTable('product_stocks')) {
                $stockQuery = DB::table('product_stocks')->where('product_id', $productId);
                if ($warehouseId) {
                    $stockQuery->where('warehouse_id', $warehouseId);
                }
                $exists = $stockQuery->first();
                if ($exists) {
                    $stockQuery->increment('quantity', $qty);
                } else {
                    DB::table('product_stocks')->insert([
                        'product_id'   => $productId,
                        'warehouse_id' => $warehouseId,
                        'quantity'     => $qty,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }
            }

            if (Schema::hasTable('inventory_movements')) {
                DB::table('inventory_movements')->insert([
                    'product_id'     => $productId,
                    'variant_id'     => $variantId,
                    'movement_type'  => 'purchase_receipt_in',
                    'reference_id'   => $receiptId,
                    'quantity_change'=> $qty,
                    'unit_cost'      => $item['unit_cost'] ?? 0,
                    'notes'          => 'Inward GRN Stock Admission #' . $receiptId,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }
    }

    /**
     * Helper: Sync parent Purchase Order status upon receipt.
     */
    protected function syncPurchaseOrderStatus($poId)
    {
        if (!Schema::hasTable('purchase_orders') || !Schema::hasTable('purchase_receipt_items')) return;

        $po = DB::table('purchase_orders')->where('id', $poId)->first();
        if (!$po) return;

        $totalOrdered = DB::table('purchase_order_items')->where('purchase_order_id', $poId)->sum('quantity') ?? 0;
        $totalReceived = DB::table('purchase_receipt_items')
            ->join('purchase_receipts', 'purchase_receipt_items.purchase_receipt_id', '=', 'purchase_receipts.id')
            ->where('purchase_receipts.purchase_order_id', $poId)
            ->sum('purchase_receipt_items.accepted_quantity') ?? 0;

        $status = 'partial';
        if ($totalReceived >= $totalOrdered && $totalOrdered > 0) {
            $status = 'received';
        }

        DB::table('purchase_orders')->where('id', $poId)->update([
            'status'     => $status,
            'updated_at' => now(),
        ]);
    }
}