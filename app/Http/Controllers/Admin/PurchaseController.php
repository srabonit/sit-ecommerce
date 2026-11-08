<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\StorePurchaseRequest;

class PurchaseController extends Controller
{
    /**
     * Display a listing of Commercial Purchase Bills / Invoices.
     */
    public function index(Request $request)
    {
        $query = DB::table('purchases')
            ->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->leftJoin('warehouses', 'purchases.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('branches', 'purchases.branch_id', '=', 'branches.id')
            ->leftJoin('purchase_orders', 'purchases.purchase_order_id', '=', 'purchase_orders.id')
            ->select(
                'purchases.*',
                'suppliers.name as supplier_name',
                'suppliers.company_name as supplier_company',
                'warehouses.name as warehouse_name',
                'branches.name as branch_name',
                'purchase_orders.po_number'
            );

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                if (Schema::hasColumn('purchases', 'purchase_number')) {
                    $q->orWhere('purchases.purchase_number', 'like', "%{$search}%");
                }
                if (Schema::hasColumn('purchases', 'purchase_no')) {
                    $q->orWhere('purchases.purchase_no', 'like', "%{$search}%");
                }
                if (Schema::hasColumn('purchases', 'supplier_invoice_no')) {
                    $q->orWhere('purchases.supplier_invoice_no', 'like', "%{$search}%");
                }
                $q->orWhere('suppliers.name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('purchases.supplier_id', $request->get('supplier_id'));
        }

        if ($request->filled('warehouse_id')) {
            $query->where('purchases.warehouse_id', $request->get('warehouse_id'));
        }

        if ($request->filled('payment_status')) {
            $query->where('purchases.payment_status', $request->get('payment_status'));
        }

        if ($request->filled('purchase_date')) {
            $query->whereDate('purchases.purchase_date', $request->get('purchase_date'));
        }

        $purchases = $query->orderBy('purchases.id', 'desc')->paginate(15)->withQueryString();

        foreach ($purchases as $p) {
            $billNo = $p->purchase_number ?? ($p->purchase_no ?? ('BILL-' . $p->id));
            $p->purchase_number = $billNo;
            $p->purchase_no     = $billNo;
        }

        // Metrics Summary
        $totalPurchasesCount = DB::table('purchases')->count();
        $totalBilledAmount   = DB::table('purchases')->sum('grand_total') ?? 0;
        $totalPaidAmount     = DB::table('purchases')->sum('paid_amount') ?? 0;
        $totalDueAmount      = max(0, $totalBilledAmount - $totalPaidAmount);

        $suppliers  = DB::table('suppliers')->where('status', 'active')->get();
        $warehouses = Schema::hasTable('warehouses') ? DB::table('warehouses')->get() : collect([]);

        return view('admin.procurement.purchases.index', compact(
            'purchases',
            'totalPurchasesCount',
            'totalBilledAmount',
            'totalPaidAmount',
            'totalDueAmount',
            'suppliers',
            'warehouses'
        ));
    }

    /**
     * Show the form for creating a new Purchase Invoice / Bill.
     */
    public function create(Request $request)
    {
        $suppliers    = DB::table('suppliers')->where('status', 'active')->get();
        $warehouses   = Schema::hasTable('warehouses') ? DB::table('warehouses')->get() : collect([]);
        $branches     = Schema::hasTable('branches') ? DB::table('branches')->get() : collect([]);
        $bankAccounts = Schema::hasTable('bank_accounts') ? DB::table('bank_accounts')->get() : collect([]);
        $categories   = Schema::hasTable('categories') ? DB::table('categories')->get() : collect([]);

        $purchaseOrders = collect([]);
        if (Schema::hasTable('purchase_orders')) {
            $purchaseOrders = DB::table('purchase_orders')
                ->whereIn('status', ['approved', 'received', 'partial', 'pending'])
                ->select('id', 'po_number', 'supplier_id', 'total_amount', 'order_date')
                ->orderBy('id', 'desc')
                ->get();
        }

        $purchaseReceipts = collect([]);
        if (Schema::hasTable('purchase_receipts')) {
            $purchaseReceipts = DB::table('purchase_receipts')
                ->select('id', 'receipt_number', 'supplier_id', 'receipt_date')
                ->orderBy('id', 'desc')
                ->get();
        }

        $selectedPo = null;
        $poItems = collect([]);
        if ($request->filled('purchase_order_id') && Schema::hasTable('purchase_orders')) {
            $selectedPo = DB::table('purchase_orders')->where('id', $request->purchase_order_id)->first();
            if ($selectedPo && Schema::hasTable('purchase_order_items')) {
                $prodTitle = Schema::hasColumn('products', 'title') ? 'products.title' : (Schema::hasColumn('products', 'name') ? 'products.name' : 'products.id');
                $prodSku   = Schema::hasColumn('products', 'sku') ? 'products.sku' : 'products.product_code';

                $poItems = DB::table('purchase_order_items')
                    ->leftJoin('products', 'purchase_order_items.product_id', '=', 'products.id')
                    ->where('purchase_order_items.purchase_order_id', $selectedPo->id)
                    ->select(
                        'purchase_order_items.*',
                        DB::raw("COALESCE({$prodTitle}, 'Product') as product_name"),
                        DB::raw("COALESCE(purchase_order_items.sku, {$prodSku}, '') as product_sku")
                    )
                    ->get();
            }
        }

        return view('admin.procurement.purchases.create', compact(
            'suppliers',
            'warehouses',
            'branches',
            'bankAccounts',
            'categories',
            'purchaseOrders',
            'purchaseReceipts',
            'selectedPo',
            'poItems'
        ));
    }

    /**
     * Store a newly created Purchase Invoice and adjust financial ledgers.
     */
    public function store(StorePurchaseRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $attachmentPath = null;
            if ($request->hasFile('attachment_file')) {
                $attachmentPath = $request->file('attachment_file')->store('procurement/bills', 'public');
            }

            $rawItems = $request->input('items', []);
            $items = array_filter($rawItems, function ($item) {
                return !empty($item['product_id']) && (float)($item['quantity'] ?? 0) > 0;
            });

            if (empty($items)) {
                return redirect()->back()->withInput()->with('error', 'Doyakore at least ekta product select korun.');
            }

            $subtotal = 0;
            $itemsDiscountTotal = 0;
            $itemsTaxTotal = 0;

            foreach ($items as $item) {
                $qty  = (float) ($item['quantity'] ?? 1);
                $rate = (float) ($item['unit_cost'] ?? 0);
                $subtotal += ($qty * $rate);
                $itemsDiscountTotal += (float) ($item['discount'] ?? 0);
                $itemsTaxTotal += (float) ($item['tax'] ?? 0);
            }

            $globalDiscount = (float) ($request->input('discount_amount', 0));
            $globalTax      = (float) ($request->input('tax_amount', 0));
            $shipping       = (float) ($request->input('shipping_cost', 0));
            $roundOff       = (float) ($request->input('round_off', 0));

            $totalDiscount  = $itemsDiscountTotal + $globalDiscount;
            $totalTax       = $itemsTaxTotal + $globalTax;

            $grandTotal     = max(0, $subtotal + $totalTax + $shipping + $roundOff - $totalDiscount);
            $paidAmount     = min($grandTotal, max(0, (float) ($request->input('paid_amount', 0))));
            $dueAmount      = max(0, $grandTotal - $paidAmount);

            $paymentStatus = 'unpaid';
            if ($paidAmount >= $grandTotal && $grandTotal > 0) {
                $paymentStatus = 'paid';
            } elseif ($paidAmount > 0) {
                $paymentStatus = 'partial';
            }

            // Generate unique Bill Number
            $billNo = $validated['purchase_number'] 
                ?? $request->input('purchase_no', 'BILL-' . date('Ymd') . '-' . rand(1000, 9999));

            // Populate purchase record dynamically matching existing columns
            $purchaseData = [
                'supplier_id'         => (int) $validated['supplier_id'],
                'warehouse_id'        => (int) $validated['warehouse_id'],
                'purchase_date'       => $validated['purchase_date'] ?? date('Y-m-d'),
                'subtotal'            => $subtotal,
                'tax_amount'          => $totalTax,
                'discount_amount'     => $totalDiscount,
                'shipping_cost'       => $shipping,
                'grand_total'         => $grandTotal,
                'paid_amount'         => $paidAmount,
                'due_amount'          => $dueAmount,
                'payment_status'      => $paymentStatus,
                'payment_method'      => $request->input('payment_method', 'cash'),
                'status'              => 'received',
                'created_at'          => now(),
                'updated_at'          => now(),
            ];

            // FIX: Populate BOTH purchase_no AND purchase_number dynamically
            if (Schema::hasColumn('purchases', 'purchase_no')) {
                $purchaseData['purchase_no'] = $billNo;
            }
            if (Schema::hasColumn('purchases', 'purchase_number')) {
                $purchaseData['purchase_number'] = $billNo;
            }
            if (Schema::hasColumn('purchases', 'purchase_order_id')) {
                $purchaseData['purchase_order_id'] = !empty($validated['purchase_order_id']) ? (int)$validated['purchase_order_id'] : null;
            }
            if (Schema::hasColumn('purchases', 'purchase_receipt_id')) {
                $purchaseData['purchase_receipt_id'] = !empty($request->purchase_receipt_id) ? (int)$request->purchase_receipt_id : null;
            }
            if (Schema::hasColumn('purchases', 'branch_id')) {
                $purchaseData['branch_id'] = !empty($validated['branch_id']) ? (int)$validated['branch_id'] : null;
            }
            if (Schema::hasColumn('purchases', 'due_date')) {
                $purchaseData['due_date'] = !empty($validated['due_date']) ? $validated['due_date'] : null;
            }
            if (Schema::hasColumn('purchases', 'supplier_invoice_no')) {
                $purchaseData['supplier_invoice_no'] = $validated['supplier_invoice_no'] ?? null;
            }
            if (Schema::hasColumn('purchases', 'bank_account_id')) {
                $purchaseData['bank_account_id'] = !empty($request->bank_account_id) ? (int)$request->bank_account_id : null;
            }
            if (Schema::hasColumn('purchases', 'payment_terms')) {
                $purchaseData['payment_terms'] = $request->input('payment_terms', 'net_30');
            }
            if (Schema::hasColumn('purchases', 'round_off')) {
                $purchaseData['round_off'] = $roundOff;
            }
            if (Schema::hasColumn('purchases', 'attachment_file')) {
                $purchaseData['attachment_file'] = $attachmentPath;
            }
            if (Schema::hasColumn('purchases', 'notes')) {
                $purchaseData['notes'] = $validated['notes'] ?? null;
            }
            if (Schema::hasColumn('purchases', 'created_by')) {
                $purchaseData['created_by'] = \Illuminate\Support\Facades\Auth::id() ?: 1;
            }

            $purchaseId = DB::table('purchases')->insertGetId($purchaseData);

            // Save line items
            foreach ($items as $item) {
                $qty  = (float) ($item['quantity'] ?? 1);
                $cost = (float) ($item['unit_cost'] ?? 0);
                $disc = (float) ($item['discount'] ?? 0);
                $tax  = (float) ($item['tax'] ?? 0);
                $lineTotal = max(0, ($qty * $cost) - $disc + $tax);
                $productId = (int) $item['product_id'];

                $itemData = [
                    'purchase_id' => $purchaseId,
                    'product_id'  => $productId,
                    'quantity'    => $qty,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];

                $variantId = (!empty($item['variant_id']) && (int)$item['variant_id'] > 0) ? (int)$item['variant_id'] : null;
                if (Schema::hasColumn('purchase_items', 'variant_id')) {
                    $itemData['variant_id'] = $variantId;
                }
                if (Schema::hasColumn('purchase_items', 'sku')) {
                    $itemData['sku'] = !empty($item['sku']) ? $item['sku'] : null;
                }
                if (Schema::hasColumn('purchase_items', 'batch_no')) {
                    $itemData['batch_no'] = !empty($item['batch_no']) ? $item['batch_no'] : null;
                }
                if (Schema::hasColumn('purchase_items', 'expiry_date')) {
                    $itemData['expiry_date'] = !empty($item['expiry_date']) ? $item['expiry_date'] : null;
                }
                if (Schema::hasColumn('purchase_items', 'unit_cost')) {
                    $itemData['unit_cost'] = $cost;
                }
                if (Schema::hasColumn('purchase_items', 'unit_price')) {
                    $itemData['unit_price'] = $cost;
                }
                if (Schema::hasColumn('purchase_items', 'rate')) {
                    $itemData['rate'] = $cost;
                }
                if (Schema::hasColumn('purchase_items', 'subtotal')) {
                    $itemData['subtotal'] = $qty * $cost;
                }
                if (Schema::hasColumn('purchase_items', 'discount')) {
                    $itemData['discount'] = $disc;
                }
                if (Schema::hasColumn('purchase_items', 'tax')) {
                    $itemData['tax'] = $tax;
                }
                if (Schema::hasColumn('purchase_items', 'total')) {
                    $itemData['total'] = $lineTotal;
                }

                DB::table('purchase_items')->insert($itemData);

                // Stock increment if direct bill purchase
                if (empty($validated['purchase_order_id'])) {
                    if (Schema::hasColumn('products', 'stock')) {
                        DB::table('products')->where('id', $productId)->increment('stock', $qty);
                    }
                    if (!empty($variantId) && Schema::hasTable('product_variants') && Schema::hasColumn('product_variants', 'stock')) {
                        DB::table('product_variants')->where('id', $variantId)->increment('stock', $qty);
                    }
                }
            }

            // Update Supplier current balance (Accounts Payable Due)
            if (Schema::hasTable('suppliers') && $dueAmount > 0) {
                DB::table('suppliers')->where('id', $validated['supplier_id'])->increment('current_balance', $dueAmount);
            }

            // Ledger double entry postings
            if (Schema::hasTable('account_ledgers')) {
                // Dr: Inventory Asset / Purchase Head
                DB::table('account_ledgers')->insert([
                    'chart_of_account_id' => 1,
                    'reference_type'      => 'purchase_bill',
                    'reference_id'        => $purchaseId,
                    'reference_number'    => $billNo,
                    'date'                => $validated['purchase_date'] ?? date('Y-m-d'),
                    'debit'               => $grandTotal,
                    'credit'              => 0,
                    'description'         => 'Purchase Bill: ' . $billNo,
                    'branch_id'           => $validated['branch_id'] ?? null,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);

                // Cr: Accounts Payable (Supplier Liability)
                DB::table('account_ledgers')->insert([
                    'chart_of_account_id' => (int) $validated['supplier_id'],
                    'reference_type'      => 'purchase_bill',
                    'reference_id'        => $purchaseId,
                    'reference_number'    => $billNo,
                    'date'                => $validated['purchase_date'] ?? date('Y-m-d'),
                    'debit'               => 0,
                    'credit'              => $grandTotal,
                    'description'         => 'Payable for Bill #' . $billNo,
                    'branch_id'           => $validated['branch_id'] ?? null,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);

                // Spot payment settlement ledger entry
                if ($paidAmount > 0) {
                    $disbursedHead = !empty($request->bank_account_id) ? (int)$request->bank_account_id : 1;

                    // Dr: Accounts Payable
                    DB::table('account_ledgers')->insert([
                        'chart_of_account_id' => (int) $validated['supplier_id'],
                        'reference_type'      => 'supplier_payment',
                        'reference_id'        => $purchaseId,
                        'reference_number'    => $billNo,
                        'date'                => $validated['purchase_date'] ?? date('Y-m-d'),
                        'debit'               => $paidAmount,
                        'credit'              => 0,
                        'description'         => 'Spot Payment on Bill #' . $billNo,
                        'branch_id'           => $validated['branch_id'] ?? null,
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ]);

                    // Cr: Cash/Bank Account Outflow
                    DB::table('account_ledgers')->insert([
                        'chart_of_account_id' => $disbursedHead,
                        'reference_type'      => 'supplier_payment',
                        'reference_id'        => $purchaseId,
                        'reference_number'    => $billNo,
                        'date'                => $validated['purchase_date'] ?? date('Y-m-d'),
                        'debit'               => 0,
                        'credit'              => $paidAmount,
                        'description'         => 'Payment Disbursement on Bill #' . $billNo,
                        'branch_id'           => $validated['branch_id'] ?? null,
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.purchases.show', $purchaseId)
                ->with('success', "Purchase Bill #{$billNo} successfully create hoyeche.");
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return redirect()->back()->withInput()->with('error', 'Failed to store purchase invoice: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified Purchase Invoice.
     */
    public function show($id)
    {
        $purchase = DB::table('purchases')
            ->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->leftJoin('warehouses', 'purchases.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('branches', 'purchases.branch_id', '=', 'branches.id')
            ->leftJoin('purchase_orders', 'purchases.purchase_order_id', '=', 'purchase_orders.id')
            ->where('purchases.id', $id)
            ->select(
                'purchases.*',
                'suppliers.name as supplier_name',
                'suppliers.company_name as supplier_company',
                'suppliers.phone as supplier_phone',
                'suppliers.email as supplier_email',
                'suppliers.address as supplier_address',
                'warehouses.name as warehouse_name',
                'branches.name as branch_name',
                'purchase_orders.po_number'
            )
            ->first();

        if (!$purchase) {
            abort(404, 'Purchase invoice not found.');
        }

        $billNo = $purchase->purchase_number ?? ($purchase->purchase_no ?? ('BILL-' . $purchase->id));
        $purchase->purchase_number = $billNo;
        $purchase->purchase_no     = $billNo;

        $prodTitle = Schema::hasColumn('products', 'title') ? 'products.title' : (Schema::hasColumn('products', 'name') ? 'products.name' : 'products.id');
        $prodSku   = Schema::hasColumn('products', 'sku') ? 'products.sku' : 'products.product_code';

        $itemsQuery = DB::table('purchase_items')
            ->leftJoin('products', 'purchase_items.product_id', '=', 'products.id');

        if (Schema::hasTable('product_variants')) {
            $itemsQuery->leftJoin('product_variants', 'purchase_items.variant_id', '=', 'product_variants.id');
        }

        $items = $itemsQuery->where('purchase_items.purchase_id', $id)
            ->select([
                'purchase_items.*',
                DB::raw("COALESCE({$prodTitle}, 'Product') as product_name"),
                DB::raw("COALESCE(purchase_items.sku, {$prodSku}, '') as product_sku")
            ])
            ->get();

        foreach ($items as $item) {
            $item->unit_cost = (float) ($item->unit_cost ?? $item->unit_price ?? $item->rate ?? 0);
            $item->total     = (float) ($item->total ?? ($item->quantity * $item->unit_cost));
        }

        $purchase->supplier = (object)[
            'id'           => $purchase->supplier_id,
            'name'         => $purchase->supplier_name,
            'company_name' => $purchase->supplier_company,
            'phone'        => $purchase->supplier_phone,
            'email'        => $purchase->supplier_email,
            'address'      => $purchase->supplier_address,
        ];
        $purchase->warehouse     = (object)['name' => $purchase->warehouse_name ?? 'Central Warehouse'];
        $purchase->branch        = (object)['name' => $purchase->branch_name ?? 'HQ Branch'];
        $purchase->purchaseOrder = (object)['po_number' => $purchase->po_number ?? '—'];
        $purchase->items         = $items;

        return view('admin.procurement.purchases.show', compact('purchase', 'items'));
    }

    /**
     * AJAX Endpoint: Get items for a specific PO.
     */
    public function getPoItems($purchaseOrder)
    {
        try {
            $prodTitle = Schema::hasColumn('products', 'title') ? 'products.title' : (Schema::hasColumn('products', 'name') ? 'products.name' : 'products.id');
            $prodSku   = Schema::hasColumn('products', 'sku') ? 'products.sku' : 'products.product_code';

            $itemsQuery = DB::table('purchase_order_items')
                ->leftJoin('products', 'purchase_order_items.product_id', '=', 'products.id');

            if (Schema::hasTable('product_variants')) {
                $itemsQuery->leftJoin('product_variants', 'purchase_order_items.variant_id', '=', 'product_variants.id');
            }

            $items = $itemsQuery->where('purchase_order_items.purchase_order_id', $purchaseOrder)
                ->select([
                    'purchase_order_items.*',
                    DB::raw("COALESCE({$prodTitle}, 'Product') as product_name"),
                    DB::raw("COALESCE(purchase_order_items.sku, {$prodSku}, '') as product_sku")
                ])
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $items,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch PO items: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AJAX Endpoint: Get items for a specific Purchase Bill.
     */
    public function getItemsAjax($purchase)
    {
        try {
            $prodTitle = Schema::hasColumn('products', 'title') ? 'products.title' : (Schema::hasColumn('products', 'name') ? 'products.name' : 'products.id');
            $prodSku   = Schema::hasColumn('products', 'sku') ? 'products.sku' : 'products.product_code';

            $itemsQuery = DB::table('purchase_items')
                ->leftJoin('products', 'purchase_items.product_id', '=', 'products.id');

            if (Schema::hasTable('product_variants')) {
                $itemsQuery->leftJoin('product_variants', 'purchase_items.variant_id', '=', 'product_variants.id');
            }

            $items = $itemsQuery->where('purchase_items.purchase_id', $purchase)
                ->select([
                    'purchase_items.id as purchase_item_id',
                    'purchase_items.product_id',
                    'purchase_items.variant_id',
                    'purchase_items.sku',
                    'purchase_items.batch_no',
                    'purchase_items.expiry_date',
                    'purchase_items.quantity',
                    'purchase_items.unit_cost',
                    'purchase_items.tax',
                    'purchase_items.total',
                    DB::raw("COALESCE({$prodTitle}, 'Product') as product_name"),
                    DB::raw("COALESCE(purchase_items.sku, {$prodSku}, '') as product_sku")
                ])
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $items,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch invoice items: ' . $e->getMessage(),
            ], 500);
        }
    }
}