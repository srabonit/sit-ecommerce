<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('purchase_orders')
            ->leftJoin('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
            ->leftJoin('warehouses', 'purchase_orders.warehouse_id', '=', 'warehouses.id')
            ->select(
                'purchase_orders.*',
                'suppliers.name as supplier_name',
                'suppliers.company_name as supplier_company',
                'warehouses.name as warehouse_name'
            );

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('purchase_orders.po_number', 'like', "%{$search}%")
                  ->orWhere('suppliers.name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('purchase_orders.supplier_id', $request->get('supplier_id'));
        }

        if ($request->filled('status')) {
            $query->where('purchase_orders.status', $request->get('status'));
        }

        if ($request->filled('order_date')) {
            $query->whereDate('purchase_orders.order_date', $request->get('order_date'));
        }

        $purchaseOrders = $query->orderBy('purchase_orders.id', 'desc')->paginate(15)->withQueryString();

        $poIds = $purchaseOrders->pluck('id')->toArray();
        $itemsByPo = [];
        if (!empty($poIds) && Schema::hasTable('purchase_order_items')) {
            $allItems = DB::table('purchase_order_items')->whereIn('purchase_order_id', $poIds)->get();
            $itemsByPo = $allItems->groupBy('purchase_order_id');
        }

        foreach ($purchaseOrders as $po) {
            $po->items = $itemsByPo[$po->id] ?? collect([]);
            $po->grand_total = (float) ($po->grand_total ?? $po->total_amount ?? $po->subtotal ?? 0);
            $po->total_amount = $po->grand_total;
            $po->supplier = (object)[
                'id'           => $po->supplier_id,
                'name'         => $po->supplier_name,
                'company_name' => $po->supplier_company,
            ];
            $po->warehouse = (object)[
                'name' => $po->warehouse_name ?? 'Central Warehouse',
            ];
            $po->expected_delivery_date = $po->expected_delivery_date ?? ($po->delivery_date ?? null);
        }

        $amountCol = Schema::hasColumn('purchase_orders', 'grand_total') ? 'grand_total' : (Schema::hasColumn('purchase_orders', 'total_amount') ? 'total_amount' : 'subtotal');
        $totalCommitmentAmount = DB::table('purchase_orders')->where('status', '!=', 'cancelled')->sum($amountCol) ?? 0;
        $totalOrdersCount      = DB::table('purchase_orders')->count();
        $pendingApprovalCount  = DB::table('purchase_orders')->where('status', 'pending')->count();
        $approvedCount         = DB::table('purchase_orders')->where('status', 'approved')->count();

        $suppliers  = DB::table('suppliers')->where('status', 'active')->get();
        $warehouses = Schema::hasTable('warehouses') ? DB::table('warehouses')->get() : collect([]);

        return view('admin.procurement.purchase_orders.index', compact(
            'purchaseOrders',
            'totalOrdersCount',
            'totalCommitmentAmount',
            'pendingApprovalCount',
            'approvedCount',
            'suppliers',
            'warehouses'
        ))->with([
            'totalOrderValue'      => $totalCommitmentAmount,
            'pendingDeliveryCount' => DB::table('purchase_orders')->whereIn('status', ['pending', 'sent', 'confirmed', 'partially_received', 'draft'])->count(),
            'completedOrdersCount' => DB::table('purchase_orders')->where('status', 'completed')->count(),
        ]);
    }

    public function create()
    {
        $suppliers  = DB::table('suppliers')->where('status', 'active')->get();
        $warehouses = Schema::hasTable('warehouses') ? DB::table('warehouses')->get() : collect([]);
        $categories = Schema::hasTable('categories') ? DB::table('categories')->get() : collect([]);

        return view('admin.procurement.purchase_orders.create', compact(
            'suppliers',
            'warehouses',
            'categories'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required',
            'items'       => 'required|array|min:1',
        ]);

        $rawItems = $request->input('items', []);
        $items = array_filter($rawItems, function ($item) {
            return !empty($item['product_id']) && (float)($item['quantity'] ?? 0) > 0;
        });

        if (empty($items)) {
            return redirect()->back()->withInput()->with('error', 'দয়া করে কমপক্ষে একটি প্রোডাক্ট সঠিকভাবে সিলেক্ট করুন।');
        }

        DB::beginTransaction();
        try {
            $supplier = DB::table('suppliers')->where('id', $request->supplier_id)->first();

            $enforceCreditCheck = 1;
            if (Schema::hasTable('procurement_settings')) {
                $enforceCreditCheck = DB::table('procurement_settings')->where('key', 'block_po_on_credit_limit')->value('value') ?? 1;
            }

            if ($enforceCreditCheck && $supplier && (float)($supplier->credit_limit ?? 0) > 0) {
                if ((float)$supplier->current_balance > (float)$supplier->credit_limit) {
                    throw new \Exception("সাপ্লায়ার ক্রেডিট লিমিট অতিক্রম করেছে (৳" . number_format($supplier->credit_limit, 2) . ")");
                }
            }

            $subtotal = 0;
            $itemsDiscountTotal = 0;

            foreach ($items as $item) {
                $qty  = (float) ($item['quantity'] ?? 1);
                $cost = (float) ($item['unit_cost'] ?? 0);
                $disc = (float) ($item['discount'] ?? 0);
                $subtotal += ($qty * $cost);
                $itemsDiscountTotal += $disc;
            }

            $globalDiscount = (float) ($request->input('discount_amount', 0));
            $totalDiscount  = $itemsDiscountTotal + $globalDiscount;
            $grandTotal     = max(0, $subtotal - $totalDiscount);

            $poPrefix = 'PO-';
            if (Schema::hasTable('procurement_settings')) {
                $poPrefix = DB::table('procurement_settings')->where('key', 'po_prefix')->value('value') ?? 'PO-';
            }
            $basePoNumber = $request->input('po_number') ?: ($poPrefix . date('Ymd') . '-' . strtoupper(Str::random(4)));
            $poNumber = $basePoNumber;
            if (DB::table('purchase_orders')->where('po_number', $poNumber)->exists()) {
                $poNumber = $basePoNumber . '-' . strtoupper(Str::random(3));
            }

            $orderDate = $request->order_date ? date('Y-m-d', strtotime($request->order_date)) : date('Y-m-d');
            $deliveryDate = !empty($request->delivery_date) 
                ? date('Y-m-d', strtotime($request->delivery_date)) 
                : (!empty($request->expected_delivery_date) ? date('Y-m-d', strtotime($request->expected_delivery_date)) : date('Y-m-d', strtotime('+7 days')));

            $warehouseId = !empty($request->warehouse_id) ? (int)$request->warehouse_id : null;
            if (!$warehouseId && Schema::hasTable('warehouses')) {
                $warehouseId = DB::table('warehouses')->value('id');
            }

            $poData = [
                'po_number'   => $poNumber,
                'supplier_id' => (int) $request->supplier_id,
            ];

            if (Schema::hasColumn('purchase_orders', 'warehouse_id')) {
                $poData['warehouse_id'] = $warehouseId;
            }
            if (Schema::hasColumn('purchase_orders', 'branch_id')) {
                $poData['branch_id'] = !empty($request->branch_id) ? (int)$request->branch_id : null;
            }
            if (Schema::hasColumn('purchase_orders', 'order_date')) {
                $poData['order_date'] = $orderDate;
            }
            if (Schema::hasColumn('purchase_orders', 'delivery_date')) {
                $poData['delivery_date'] = $deliveryDate;
            }
            if (Schema::hasColumn('purchase_orders', 'expected_delivery_date')) {
                $poData['expected_delivery_date'] = $deliveryDate;
            }
            if (Schema::hasColumn('purchase_orders', 'subtotal')) {
                $poData['subtotal'] = $subtotal;
            }
            if (Schema::hasColumn('purchase_orders', 'total_amount')) {
                $poData['total_amount'] = $grandTotal;
            }
            if (Schema::hasColumn('purchase_orders', 'grand_total')) {
                $poData['grand_total'] = $grandTotal;
            }
            if (Schema::hasColumn('purchase_orders', 'discount_amount')) {
                $poData['discount_amount'] = $totalDiscount;
            }
            if (Schema::hasColumn('purchase_orders', 'tax_amount')) {
                $poData['tax_amount'] = 0.00;
            }
            if (Schema::hasColumn('purchase_orders', 'shipping_cost')) {
                $poData['shipping_cost'] = 0.00;
            }
            if (Schema::hasColumn('purchase_orders', 'status')) {
                $poData['status'] = $request->input('status', 'pending');
            }
            if (Schema::hasColumn('purchase_orders', 'notes')) {
                $poData['notes'] = $request->input('notes');
            }

            if (Schema::hasColumn('purchase_orders', 'created_by')) {
                $userId = Auth::id() ?: (Schema::hasTable('users') ? DB::table('users')->value('id') : null);
                $poData['created_by'] = $userId ?: null;
            }

            if (Schema::hasColumn('purchase_orders', 'created_at')) {
                $poData['created_at'] = now();
                $poData['updated_at'] = now();
            }

            $poId = DB::table('purchase_orders')->insertGetId($poData);

            foreach ($items as $item) {
                $qty       = (float) ($item['quantity'] ?? 1);
                $cost      = (float) ($item['unit_cost'] ?? 0);
                $discount  = (float) ($item['discount'] ?? 0);
                $lineTotal = max(0, ($qty * $cost) - $discount);
                $productId = (int) $item['product_id'];

                $itemInsert = [
                    'purchase_order_id' => $poId,
                    'product_id'        => $productId,
                    'quantity'          => $qty,
                ];

                if (Schema::hasColumn('purchase_order_items', 'variant_id')) {
                    $itemInsert['variant_id'] = (!empty($item['variant_id']) && (int)$item['variant_id'] > 0) ? (int)$item['variant_id'] : null;
                }
                if (Schema::hasColumn('purchase_order_items', 'sku')) {
                    $itemInsert['sku'] = !empty($item['sku']) ? $item['sku'] : null;
                }
                if (Schema::hasColumn('purchase_order_items', 'unit_cost')) {
                    $itemInsert['unit_cost'] = $cost;
                }
                if (Schema::hasColumn('purchase_order_items', 'unit_price')) {
                    $itemInsert['unit_price'] = $cost;
                }
                if (Schema::hasColumn('purchase_order_items', 'rate')) {
                    $itemInsert['rate'] = $cost;
                }
                if (Schema::hasColumn('purchase_order_items', 'subtotal')) {
                    $itemInsert['subtotal'] = $qty * $cost;
                }
                if (Schema::hasColumn('purchase_order_items', 'total')) {
                    $itemInsert['total'] = $lineTotal;
                }
                if (Schema::hasColumn('purchase_order_items', 'discount')) {
                    $itemInsert['discount'] = $discount;
                }
                if (Schema::hasColumn('purchase_order_items', 'tax')) {
                    $itemInsert['tax'] = 0.00;
                }
                if (Schema::hasColumn('purchase_order_items', 'received_quantity')) {
                    $itemInsert['received_quantity'] = 0.00;
                }
                if (Schema::hasColumn('purchase_order_items', 'created_at')) {
                    $itemInsert['created_at'] = now();
                    $itemInsert['updated_at'] = now();
                }

                DB::table('purchase_order_items')->insert($itemInsert);

                if (!empty($item['update_product_price']) && (int)$item['update_product_price'] === 1) {
                    $prodUpdates = [];
                    if (Schema::hasColumn('products', 'purchase_cost') && $cost > 0) {
                        $prodUpdates['purchase_cost'] = $cost;
                    }
                    if (Schema::hasColumn('products', 'purchase_price') && $cost > 0) {
                        $prodUpdates['purchase_price'] = $cost;
                    }
                    if (!empty($item['regular_price']) && (float)$item['regular_price'] > 0) {
                        if (Schema::hasColumn('products', 'regular_price')) {
                            $prodUpdates['regular_price'] = (float)$item['regular_price'];
                        } elseif (Schema::hasColumn('products', 'price')) {
                            $prodUpdates['price'] = (float)$item['regular_price'];
                        }
                    }

                    if (!empty($prodUpdates)) {
                        $prodUpdates['updated_at'] = now();
                        DB::table('products')->where('id', $productId)->update($prodUpdates);
                    }
                }
            }

            DB::commit();

            // Redirects directly to the SHOW page of the created PO with a success banner
            return redirect()->route('admin.purchase-orders.show', $poId)
                ->with('success', "Purchase Order #{$poNumber} সফলভাবে তৈরি হয়েছে।");
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return redirect()->back()->withInput()->with('error', 'Purchase order তৈরি করা যায়নি: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $order = DB::table('purchase_orders')
            ->leftJoin('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
            ->leftJoin('warehouses', 'purchase_orders.warehouse_id', '=', 'warehouses.id')
            ->where('purchase_orders.id', $id)
            ->select(
                'purchase_orders.*',
                'suppliers.name as supplier_name',
                'suppliers.company_name as supplier_company',
                'suppliers.phone as supplier_phone',
                'suppliers.email as supplier_email',
                'suppliers.address as supplier_address',
                'warehouses.name as warehouse_name'
            )
            ->first();

        if (!$order) {
            abort(404, 'Purchase order not found.');
        }

        $items = DB::table('purchase_order_items')
            ->leftJoin('products', 'purchase_order_items.product_id', '=', 'products.id')
            ->where('purchase_order_items.purchase_order_id', $id)
            ->select(
                'purchase_order_items.*',
                'products.title as product_name',
                DB::raw("COALESCE(purchase_order_items.sku, products.product_code) as product_sku")
            )
            ->get();

        foreach ($items as $item) {
            $item->unit_cost = (float) ($item->unit_cost ?? $item->unit_price ?? $item->rate ?? 0);
            $item->total = (float) ($item->total ?? ($item->quantity * $item->unit_cost));
        }

        $order->supplier = (object)[
            'id'           => $order->supplier_id,
            'name'         => $order->supplier_name,
            'company_name' => $order->supplier_company,
            'phone'        => $order->supplier_phone,
            'email'        => $order->supplier_email,
            'address'      => $order->supplier_address,
        ];
        $order->warehouse = (object)['name' => $order->warehouse_name ?? 'Central Warehouse'];
        $order->items     = $items;
        $order->expected_delivery_date = $order->expected_delivery_date ?? ($order->delivery_date ?? null);

        $resolvedTotal = (float) ($order->grand_total ?? $order->total_amount ?? $order->subtotal ?? 0);
        $order->grand_total = $resolvedTotal;
        $order->total_amount = $resolvedTotal;

        return view('admin.procurement.purchase_orders.show', [
            'order'         => $order,
            'purchaseOrder' => $order,
            'items'         => $items,
        ]);
    }

    public function approve(Request $request, $id)
    {
        $order = DB::table('purchase_orders')->where('id', $id)->first();
        if (!$order) {
            return redirect()->back()->with('error', 'Purchase order পাওয়া যায়নি।');
        }

        if ($order->status === 'approved') {
            return redirect()->back()->with('warning', 'Order ইতিমধ্যে approve করা আছে।');
        }

        if ($order->status === 'cancelled') {
            return redirect()->back()->with('error', 'Cancelled order approve করা যাবে না।');
        }

        DB::table('purchase_orders')->where('id', $id)->update([
            'status'     => 'approved',
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', "Purchase order #{$order->po_number} approve করা হয়েছে।");
    }

    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancel_reason' => 'required|string|max:500',
        ]);

        $order = DB::table('purchase_orders')->where('id', $id)->first();
        if (!$order) {
            return redirect()->back()->with('error', 'Purchase order পাওয়া যায়নি।');
        }

        if ($order->status === 'received' || $order->status === 'completed') {
            return redirect()->back()->with('error', 'যে অর্ডার ইতিমধ্যে সম্পন্ন হয়েছে তা বাতিল করা যাবে না।');
        }

        DB::table('purchase_orders')->where('id', $id)->update([
            'status'     => 'cancelled',
            'notes'      => trim(($order->notes ?? '') . "\n[Cancel Reason]: " . $request->cancel_reason),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', "Purchase order #{$order->po_number} cancel করা হয়েছে।");
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $order = DB::table('purchase_orders')->where('id', $id)->first();
            if (!$order) {
                return redirect()->back()->with('error', 'Purchase order পাওয়া যায়নি।');
            }

            if (Schema::hasTable('purchases')) {
                $linkedPurchases = DB::table('purchases')->where('purchase_order_id', $id)->get();
                foreach ($linkedPurchases as $purchase) {
                    $unpaidDue = (float) ($purchase->due_amount ?? max(0, (float)$purchase->grand_total - (float)($purchase->paid_amount ?? 0)));
                    if ($unpaidDue > 0 && Schema::hasTable('suppliers')) {
                        DB::table('suppliers')->where('id', $purchase->supplier_id)->decrement('current_balance', $unpaidDue);
                    }

                    if (Schema::hasTable('purchase_items')) {
                        DB::table('purchase_items')->where('purchase_id', $purchase->id)->delete();
                    }
                }
                DB::table('purchases')->where('purchase_order_id', $id)->delete();
            }

            if (Schema::hasTable('purchase_receipts')) {
                $receiptIds = DB::table('purchase_receipts')->where('purchase_order_id', $id)->pluck('id')->toArray();
                if (!empty($receiptIds) && Schema::hasTable('purchase_receipt_items')) {
                    DB::table('purchase_receipt_items')->whereIn('purchase_receipt_id', $receiptIds)->delete();
                }
                DB::table('purchase_receipts')->where('purchase_order_id', $id)->delete();
            }

            if (Schema::hasTable('purchase_order_items')) {
                DB::table('purchase_order_items')->where('purchase_order_id', $id)->delete();
            }

            DB::table('purchase_orders')->where('id', $id)->delete();

            DB::commit();
            return redirect()->route('admin.purchase-orders.index')
                ->with('success', "Purchase Order #{$order->po_number} সফলভাবে ডিলিট করা হয়েছে এবং সংশ্লিষ্ট বিল ও লেজার অ্যাডজাস্ট করা হয়েছে।");
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return redirect()->back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    public function searchProductsAjax(Request $request)
    {
        $term = trim($request->get('term', $request->get('q', '')));
        if (empty($term)) {
            return response()->json(['results' => []]);
        }

        $cleanSearch = ltrim($term, '#');
        $query = DB::table('products');

        $query->where(function ($q) use ($term, $cleanSearch) {
            $q->where('products.title', 'like', "%{$term}%")
              ->orWhere('products.product_code', 'like', "%{$term}%")
              ->orWhere('products.barcode', 'like', "%{$term}%")
              ->orWhere('products.id', $cleanSearch);
            if (Schema::hasColumn('products', 'sku')) {
                $q->orWhere('products.sku', 'like', "%{$term}%");
            }
        });

        if (Schema::hasColumn('products', 'status')) {
            $query->where(function ($q) {
                $q->where('status', 'active')->orWhere('status', 1);
            });
        }

        $hasSku = Schema::hasColumn('products', 'sku');
        $skuCol = $hasSku ? 'COALESCE(products.sku, products.product_code)' : 'products.product_code';

        $priceCol = Schema::hasColumn('products', 'regular_price') 
            ? 'COALESCE(products.regular_price, 0)' 
            : (Schema::hasColumn('products', 'price') ? 'COALESCE(products.price, 0)' : '0');

        $costCol = Schema::hasColumn('products', 'purchase_cost')
            ? 'COALESCE(NULLIF(products.purchase_cost, 0), products.purchase_price, 0)'
            : (Schema::hasColumn('products', 'purchase_price') ? 'COALESCE(products.purchase_price, 0)' : '0');

        $products = $query->select([
            'products.id',
            'products.title as name',
            DB::raw("{$skuCol} as sku"),
            DB::raw("{$priceCol} as regular_price"),
            DB::raw("{$costCol} as purchase_cost"),
            DB::raw("COALESCE(products.stock, 0) as stock"),
        ])->limit(20)->get();

        return response()->json([
            'success' => true,
            'results' => $products
        ]);
    }

    public function findProductBySkuAjax(Request $request)
    {
        $sku = trim($request->get('sku', ''));
        if (empty($sku)) {
            return response()->json(['success' => false, 'message' => 'SKU is empty']);
        }

        $query = DB::table('products');
        $query->where(function ($q) use ($sku) {
            $q->where('products.product_code', $sku)
              ->orWhere('products.barcode', $sku);
            if (Schema::hasColumn('products', 'sku')) {
                $q->orWhere('products.sku', $sku);
            }
        });

        $hasSku = Schema::hasColumn('products', 'sku');
        $skuCol = $hasSku ? 'COALESCE(products.sku, products.product_code)' : 'products.product_code';

        $priceCol = Schema::hasColumn('products', 'regular_price') 
            ? 'COALESCE(products.regular_price, 0)' 
            : (Schema::hasColumn('products', 'price') ? 'COALESCE(products.price, 0)' : '0');

        $costCol = Schema::hasColumn('products', 'purchase_cost')
            ? 'COALESCE(NULLIF(products.purchase_cost, 0), products.purchase_price, 0)'
            : (Schema::hasColumn('products', 'purchase_price') ? 'COALESCE(products.purchase_price, 0)' : '0');

        $product = $query->select([
            'products.id',
            'products.title as name',
            DB::raw("{$skuCol} as sku"),
            DB::raw("{$priceCol} as regular_price"),
            DB::raw("{$costCol} as purchase_cost"),
            DB::raw("COALESCE(products.stock, 0) as stock"),
        ])->first();

        if ($product) {
            return response()->json([
                'success' => true,
                'data'    => $product
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No product found with this SKU'
        ]);
    }

    public function quickStoreProductAjax(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'regular_price' => 'required|numeric|min:0',
            'purchase_cost' => 'nullable|numeric|min:0',
        ]);

        try {
            $sku = $request->sku ?: ('PRD-' . strtoupper(Str::random(6)));
            $cost = (float) ($request->purchase_cost ?? 0);
            $regPrice = (float) $request->regular_price;

            $insertData = [
                'title'          => $request->title,
                'slug'           => Str::slug($request->title) . '-' . strtolower(Str::random(4)),
                'product_code'   => $sku,
                'barcode'        => rand(100000000000, 999999999999),
                'regular_price'  => $regPrice,
                'purchase_cost'  => $cost,
                'purchase_price' => $cost,
                'stock'          => 0,
                'category_id'    => $request->category_id ?: null,
                'product_type'   => 'physical',
                'product_struct' => 'simple',
                'created_at'     => now(),
                'updated_at'     => now(),
            ];

            if (Schema::hasColumn('products', 'sku')) {
                $insertData['sku'] = $sku;
            }

            $productId = DB::table('products')->insertGetId($insertData);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data'    => [
                    'id'            => $productId,
                    'name'          => $request->title,
                    'sku'           => $sku,
                    'regular_price' => $regPrice,
                    'purchase_cost' => $cost,
                    'stock'         => 0
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage()
            ], 422);
        }
    }
}