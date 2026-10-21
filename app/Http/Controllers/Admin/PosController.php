<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Services\ActivityLogger;
use App\Services\PaymentTransactionService;
use App\Services\InventoryService;

class PosController extends Controller
{
    /**
     * Dhaf 1: Branch Select Screen / Auto Redirect
     */
    public function index()
    {
        $user = Auth::user();

        // Branch staff ba manager hole auto redirect
        if (!$user->isSuperAdmin() && !empty($user->branch_id)) {
            return redirect()->route('admin.pos.terminal', ['branch_id' => $user->branch_id]);
        }

        // Super Admin-er jonno shob shokriyo branch
        $branches = DB::table('branches')->where('is_active', true)->get();
        return view('admin.pos.select_branch', compact('branches'));
    }

    /**
     * Dhaf 2: Main POS Terminal (Security Guard shoho)
     */
    public function terminal($branch_id)
    {
        $user = Auth::user();

        // Branch security check
        if (!$user->isSuperAdmin() && !empty($user->branch_id) && (int)$user->branch_id !== (int)$branch_id) {
            ActivityLogger::log('unauthorized_branch_pos_access', 'pos', 'Attempted to open unauthorized POS terminal', [
                'record_id'  => $branch_id,
                'record_ref' => 'Branch #' . $branch_id,
                'old_values' => ['assigned_branch_id' => $user->branch_id],
                'new_values' => ['attempted_branch_id' => $branch_id],
                'result'     => 'failed',
            ]);
            abort(403, 'Access Denied: You are not authorized to operate POS Terminal for Branch #' . $branch_id);
        }

        $branch = DB::table('branches')->where('id', $branch_id)->first();
        if (!$branch) abort(404, 'Branch not found');

        $categories = DB::table('categories')->where('is_active', true)->orderBy('name', 'asc')->get();
        $brands = DB::table('brands')->where('is_active', true)->orderBy('name', 'asc')->get();
        $customers = DB::table('users')->where('role', 'customer')->latest('id')->take(60)->get();
        $paymentMethods = ['Cash', 'bKash', 'Nagad', 'Rocket', 'Bank Card'];

        return view('admin.pos.terminal', compact('branch', 'categories', 'brands', 'customers', 'paymentMethods'));
    }

    /**
     * Product Live Filtering & Grid (AJAX - Branch Scoped + Schema-safe Variant Data)
     */
    public function filterProductsAjax(Request $request)
    {
        $user = Auth::user();
        
        $branchId = (!$user->isSuperAdmin() && !empty($user->branch_id)) 
            ? $user->branch_id 
            : $request->branch_id;

        $search = trim($request->get('search', ''));
        $categoryId = $request->get('category_id');
        $brandId = $request->get('brand_id');

        $query = DB::table('products')
            ->leftJoin('product_stocks', function ($join) use ($branchId) {
                $join->on('products.id', '=', 'product_stocks.product_id')
                     ->where('product_stocks.branch_id', '=', $branchId);
            })
            ->select(
                'products.id',
                'products.title',
                'products.product_code',
                'products.regular_price',
                'products.sale_price',
                'products.thumbnail',
                DB::raw('COALESCE(product_stocks.quantity, products.stock) as branch_stock')
            );

        if (Schema::hasColumn('products', 'product_struct')) {
            $query->addSelect('products.product_struct');
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                if (is_numeric($search)) {
                    $q->where('products.id', $search);
                }
                $q->orWhere('products.title', 'like', "%{$search}%")
                  ->orWhere('products.product_code', 'like', "%{$search}%");
                if (Schema::hasColumn('products', 'sku')) {
                    $q->orWhere('products.sku', 'like', "%{$search}%");
                }
            });
        }

        if (!empty($categoryId)) {
            $query->where('products.category_id', $categoryId);
        }

        if (!empty($brandId)) {
            $query->where('products.brand_id', $brandId);
        }

        $products = $query->latest('products.id')->paginate(16);

        // Product-er shathe tar variants attach kora (Schema-safe)
        if (Schema::hasTable('product_variants')) {
            $pIds = $products->pluck('id')->toArray();
            $hasVarRegPrice  = Schema::hasColumn('product_variants', 'regular_price');
            $hasVarSalePrice = Schema::hasColumn('product_variants', 'sale_price');
            $hasVarCode      = Schema::hasColumn('product_variants', 'product_code');

            $variantRows = DB::table('product_variants')
                ->whereIn('product_id', $pIds)
                ->get()
                ->groupBy('product_id');

            $products->getCollection()->transform(function ($p) use ($variantRows, $hasVarRegPrice, $hasVarSalePrice, $hasVarCode) {
                $pVars = $variantRows->get($p->id, collect());
                $p->variants = $pVars->map(function ($v) use ($p, $hasVarRegPrice, $hasVarSalePrice, $hasVarCode) {
                    $parts = array_filter([$v->color ?? null, $v->size ?? null]);
                    $vLabel = !empty($parts) ? implode(' / ', $parts) : ($v->name ?? ('Variant #' . $v->id));
                    
                    $mainPrice = (float) (!empty($p->sale_price) && $p->sale_price > 0 ? $p->sale_price : $p->regular_price);
                    $vPrice = $mainPrice;

                    if ($hasVarSalePrice && !empty($v->sale_price) && (float)$v->sale_price > 0) {
                        $vPrice = (float)$v->sale_price;
                    } elseif ($hasVarRegPrice && !empty($v->regular_price) && (float)$v->regular_price > 0) {
                        $vPrice = (float)$v->regular_price;
                    }

                    $vCode = $hasVarCode ? ($v->product_code ?? '') : ('VAR-' . $v->id);
                    $vStock = (int) ($v->stock ?? 0);

                    return (object) [
                        'id'    => $v->id,
                        'name'  => $vLabel,
                        'price' => $vPrice,
                        'stock' => $vStock,
                        'code'  => $vCode,
                    ];
                });
                return $p;
            });
        } else {
            $products->getCollection()->transform(function ($p) {
                $p->variants = collect();
                return $p;
            });
        }

        return view('admin.pos.partials.product_grid', compact('products'))->render();
    }

    /**
     * Barcode Scanner Lookup (AJAX - Branch Scoped)
     */
    public function scanBarcodeAjax(Request $request)
    {
        $user = Auth::user();
        $barcode = trim((string) $request->input('barcode', ''));
        $branchId = (!$user->isSuperAdmin() && !empty($user->branch_id)) 
            ? $user->branch_id 
            : $request->input('branch_id');

        if (empty($barcode)) {
            return response()->json([
                'success' => false,
                'matched' => 'none',
                'message' => 'বারকোড খালি হতে পারে না।'
            ], 422);
        }

        // ১. প্রোডাক্ট ভ্যারিয়েন্ট টেবিলে বারকোড / SKU / কোড চেক
        if (Schema::hasTable('product_variants')) {
            $hasVarBarcode = Schema::hasColumn('product_variants', 'barcode');
            $hasVarCode    = Schema::hasColumn('product_variants', 'product_code');
            $hasVarSku     = Schema::hasColumn('product_variants', 'sku');

            $variantQuery = DB::table('product_variants')
                ->join('products', 'product_variants.product_id', '=', 'products.id')
                ->leftJoin('product_stocks', function ($join) use ($branchId) {
                    $join->on('products.id', '=', 'product_stocks.product_id')
                         ->where('product_stocks.branch_id', '=', $branchId);
                })
                ->where(function ($q) use ($barcode, $hasVarBarcode, $hasVarCode, $hasVarSku) {
                    if ($hasVarBarcode) {
                        $q->where('product_variants.barcode', $barcode);
                    }
                    if ($hasVarCode) {
                        $q->orWhere('product_variants.product_code', $barcode);
                    }
                    if ($hasVarSku) {
                        $q->orWhere('product_variants.sku', $barcode);
                    }
                })
                ->select(
                    'products.id as product_id',
                    'products.title as main_title',
                    'products.thumbnail',
                    'product_variants.id as variant_id',
                    'product_variants.color',
                    'product_variants.size',
                    'product_variants.stock as variant_stock',
                    'products.regular_price as main_regular_price',
                    'products.sale_price as main_sale_price',
                    DB::raw('COALESCE(product_stocks.quantity, products.stock) as branch_stock')
                );

            if ($hasVarCode) {
                $variantQuery->addSelect('product_variants.product_code as variant_sku');
            } elseif ($hasVarSku) {
                $variantQuery->addSelect('product_variants.sku as variant_sku');
            }

            if (Schema::hasColumn('product_variants', 'regular_price')) {
                $variantQuery->addSelect('product_variants.regular_price as variant_regular_price');
            }
            if (Schema::hasColumn('product_variants', 'sale_price')) {
                $variantQuery->addSelect('product_variants.sale_price as variant_sale_price');
            }

            $matchedVariants = $variantQuery->get();

            if ($matchedVariants->count() > 1) {
                return response()->json([
                    'success'  => false,
                    'matched'  => 'multiple',
                    'message'  => 'একই বারকোডে একাধিক ভ্যারিয়েন্ট পাওয়া গেছে! অনুগ্রহ করে ম্যানুয়াল নির্বাচন করুন।',
                    'variants' => $matchedVariants
                ]);
            }

            if ($matchedVariants->count() === 1) {
                $v = $matchedVariants->first();
                $vPrice = (float) (!empty($v->variant_sale_price) && $v->variant_sale_price > 0 
                    ? $v->variant_sale_price 
                    : (!empty($v->variant_regular_price) && $v->variant_regular_price > 0 
                        ? $v->variant_regular_price 
                        : (!empty($v->main_sale_price) && $v->main_sale_price > 0 ? $v->main_sale_price : $v->main_regular_price)));

                $variantTitle = $v->main_title;
                $varDetails = array_filter([$v->color, $v->size]);
                if (!empty($varDetails)) {
                    $variantTitle .= ' (' . implode(' / ', $varDetails) . ')';
                }

                $availableStock = (int) ($v->variant_stock ?? 0);

                return response()->json([
                    'success'    => true,
                    'matched'    => 'single',
                    'is_variant' => true,
                    'product'    => [
                        'id'          => $v->product_id,
                        'variant_id'  => $v->variant_id,
                        'title'       => $variantTitle,
                        'product_code'=> $v->variant_sku ?? ('VAR-' . $v->variant_id),
                        'barcode'     => $v->variant_sku ?? '',
                        'price'       => $vPrice,
                        'stock'       => $availableStock,
                        'thumbnail'   => $v->thumbnail ?? ''
                    ],
                    'message' => 'ভ্যারিয়েন্ট সফলভাবে পাওয়া গেছে।'
                ]);
            }
        }

        // ২. মূল Products টেবিলে বারকোড / SKU / Product Code লুকআপ
        $productQuery = DB::table('products')
            ->leftJoin('product_stocks', function ($join) use ($branchId) {
                $join->on('products.id', '=', 'product_stocks.product_id')
                     ->where('product_stocks.branch_id', '=', $branchId);
            })
            ->where(function ($q) use ($barcode) {
                if (Schema::hasColumn('products', 'barcode')) {
                    $q->where('products.barcode', $barcode);
                }
                $q->orWhere('products.product_code', $barcode);
                if (Schema::hasColumn('products', 'sku')) {
                    $q->orWhere('products.sku', $barcode);
                }
                if (is_numeric($barcode)) {
                    $q->orWhere('products.id', $barcode);
                }
            })
            ->select(
                'products.id',
                'products.title',
                'products.product_code',
                'products.regular_price',
                'products.sale_price',
                'products.thumbnail',
                'products.stock',
                DB::raw('COALESCE(product_stocks.quantity, products.stock) as branch_stock')
            );

        if (Schema::hasColumn('products', 'product_struct')) {
            $productQuery->addSelect('products.product_struct');
        }

        $matchedProducts = $productQuery->get();

        if ($matchedProducts->count() > 1) {
            return response()->json([
                'success'  => false,
                'matched'  => 'multiple',
                'message'  => 'একই বারকোডে একাধিক প্রোডাক্ট রয়েছে! ম্যানুয়াল গ্রিড থেকে নির্বাচন করুন।',
                'products' => $matchedProducts
            ]);
        }

        if ($matchedProducts->count() === 1) {
            $p = $matchedProducts->first();
            $pPrice = (float) (!empty($p->sale_price) && $p->sale_price > 0 ? $p->sale_price : $p->regular_price);
            $availableStock = (int) ($p->branch_stock ?? ($p->stock ?? 0));

            return response()->json([
                'success'    => true,
                'matched'    => 'single',
                'is_variant' => false,
                'product'    => [
                    'id'          => $p->id,
                    'variant_id'  => null,
                    'title'       => $p->title,
                    'product_code'=> $p->product_code,
                    'barcode'     => $p->product_code,
                    'price'       => $pPrice,
                    'stock'       => $availableStock,
                    'thumbnail'   => $p->thumbnail ?? ''
                ],
                'message' => 'পণ্য সফলভাবে পাওয়া গেছে।'
            ]);
        }

        return response()->json([
            'success' => false,
            'matched' => 'none',
            'message' => "বারকোড '{$barcode}' এর বিপরীতে কোনো পণ্য বা ভ্যারিয়েন্ট পাওয়া যায়নি।"
        ], 404);
    }

    /**
     * Customer Live AJAX Search
     */
    public function searchCustomersAjax(Request $request)
    {
        $q = trim($request->q);
        $customers = DB::table('users')
            ->where('role', 'customer')
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            })
            ->select('id', 'name', 'phone', 'email')
            ->take(15)
            ->get();

        return response()->json($customers);
    }

    /**
     * Customer Registration (AJAX)
     */
    public function createCustomerAjax(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'phone' => 'required|string|max:20',
        ]);

        $email = $request->filled('email') 
            ? strtolower(trim($request->email)) 
            : preg_replace('/[^0-9]/', '', $request->phone) . '@customer.local';

        $userId = DB::table('users')->insertGetId([
            'name'           => trim($request->name),
            'email'          => $email,
            'phone'          => trim($request->phone),
            'password'       => Hash::make('123456'),
            'role'           => 'customer',
            'bio'            => $request->address,
            'monthly_salary' => 0,
            'created_by'     => Auth::id(),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        ActivityLogger::log('create', 'pos_customers', "Walk-in customer registered from POS: {$request->name}", [
            'record_id'  => $userId,
            'record_ref' => $request->phone,
            'new_values' => ['name' => $request->name, 'phone' => $request->phone],
        ]);

        return response()->json([
            'success'  => true,
            'customer' => [
                'id'    => $userId,
                'name'  => $request->name,
                'phone' => $request->phone,
            ]
        ]);
    }

    /**
     * POS Order Place & Stock Adjustment (InventoryService Engine & Cost Snapshotting shoho)
     */
    public function placeOrder(Request $request)
    {
        $user = Auth::user();

        // Branch security
        if (!$user->isSuperAdmin() && !empty($user->branch_id) && (int)$request->branch_id !== (int)$user->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied: You cannot place POS orders on another branch.'
            ], 403);
        }

        $request->validate([
            'branch_id'         => 'required|exists:branches,id',
            'items'             => 'required|array|min:1',
            'items.*.id'        => 'required|exists:products,id',
            'items.*.variant_id'=> 'nullable|integer',
            'items.*.qty'       => 'required|integer|min:1',
            'items.*.price'     => 'required|numeric|min:0',
            'payment_method'    => 'required|string',
            'paid_amount'       => 'required|numeric|min:0',
            'transaction_id'    => 'nullable|string|max:100',
        ]);

        // Transaction ID duplicate check
        if ($request->filled('transaction_id')) {
            $dupCheck = PaymentTransactionService::checkDuplicateTransactionId(
                $request->input('transaction_id'),
                $request->input('payment_method')
            );
            if ($dupCheck['is_duplicate']) {
                return response()->json([
                    'success' => false,
                    'message' => $dupCheck['admin_warning'] ?? $dupCheck['message']
                ], 422);
            }
        }

        // 1. Stock Pre-Check
        foreach ($request->items as $it) {
            $pId = (int) $it['id'];
            $vId = !empty($it['variant_id']) ? (int) $it['variant_id'] : null;
            $qty = (int) $it['qty'];

            $availability = InventoryService::checkStockAvailability($pId, $vId, $qty);
            if (!$availability['available']) {
                $itemTitle = $availability['title'] ?? 'পণ্য';
                return response()->json([
                    'success' => false,
                    'message' => "দুঃখিত, '{$itemTitle}'-এর পর্যাপ্ত স্টক নেই। বর্তমান অবশিষ্ট স্টক: {$availability['stock']} টি।"
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $invoiceNo = 'POS-' . date('Ymd') . '-' . mt_rand(1000, 9999);
            $subtotal = 0;

            foreach ($request->items as $it) {
                $subtotal += ($it['price'] * $it['qty']);
            }

            $shipping = (float) ($request->shipping_charge ?? 0);
            $discount = (float) ($request->discount ?? 0);
            $grandTotal = max(0, ($subtotal + $shipping) - $discount);
            $paid = (float) $request->paid_amount;
            $due = max(0, $grandTotal - $paid);
            $paymentStatus = ($paid >= $grandTotal) ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid');

            $customerName = 'Walk-in Customer';
            $customerPhone = null;
            $customerEmail = null;
            $customerAddress = null;

            if ($request->filled('customer_id')) {
                $cust = DB::table('users')->where('id', $request->customer_id)->first();
                if ($cust) {
                    $customerName = $cust->name;
                    $customerPhone = $cust->phone;
                    $customerEmail = $cust->email;
                    $customerAddress = $cust->bio ?? null;
                }
            }

            $orderData = [
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('orders', 'invoice_no')) {
                $orderData['invoice_no'] = $invoiceNo;
            } elseif (Schema::hasColumn('orders', 'order_number')) {
                $orderData['order_number'] = $invoiceNo;
            }

            if (Schema::hasColumn('orders', 'user_id')) $orderData['user_id'] = $request->customer_id ?? null;
            if (Schema::hasColumn('orders', 'branch_id')) $orderData['branch_id'] = $request->branch_id;

            if (Schema::hasColumn('orders', 'customer_name')) $orderData['customer_name'] = $customerName;
            if (Schema::hasColumn('orders', 'shipping_name')) $orderData['shipping_name'] = $customerName;
            if (Schema::hasColumn('orders', 'shipping_phone')) $orderData['shipping_phone'] = $customerPhone;
            if (Schema::hasColumn('orders', 'phone') && !isset($orderData['shipping_phone'])) $orderData['phone'] = $customerPhone;
            if (Schema::hasColumn('orders', 'shipping_email')) $orderData['shipping_email'] = $customerEmail;
            if (Schema::hasColumn('orders', 'shipping_address')) $orderData['shipping_address'] = $customerAddress;

            if (Schema::hasColumn('orders', 'subtotal')) {
                $orderData['subtotal'] = $subtotal;
            } elseif (Schema::hasColumn('orders', 'sub_total')) {
                $orderData['sub_total'] = $subtotal;
            }

            if (Schema::hasColumn('orders', 'shipping_charge')) {
                $orderData['shipping_charge'] = $shipping;
            } elseif (Schema::hasColumn('orders', 'shipping_cost')) {
                $orderData['shipping_cost'] = $shipping;
            } elseif (Schema::hasColumn('orders', 'delivery_charge')) {
                $orderData['delivery_charge'] = $shipping;
            }

            if (Schema::hasColumn('orders', 'discount')) {
                $orderData['discount'] = $discount;
            } elseif (Schema::hasColumn('orders', 'discount_amount')) {
                $orderData['discount_amount'] = $discount;
            }

            if (Schema::hasColumn('orders', 'grand_total')) {
                $orderData['grand_total'] = $grandTotal;
            } elseif (Schema::hasColumn('orders', 'total_amount')) {
                $orderData['total_amount'] = $grandTotal;
            } elseif (Schema::hasColumn('orders', 'total')) {
                $orderData['total'] = $grandTotal;
            }

            if (Schema::hasColumn('orders', 'paid_amount')) $orderData['paid_amount'] = $paid;
            if (Schema::hasColumn('orders', 'due_amount')) $orderData['due_amount'] = $due;

            if (Schema::hasColumn('orders', 'payment_method')) $orderData['payment_method'] = $request->payment_method;
            if (Schema::hasColumn('orders', 'payment_status')) $orderData['payment_status'] = $paymentStatus;
            if (Schema::hasColumn('orders', 'order_status')) $orderData['order_status'] = 'delivered';
            if (Schema::hasColumn('orders', 'status') && !Schema::hasColumn('orders', 'order_status')) $orderData['status'] = 'delivered';
            if (Schema::hasColumn('orders', 'order_type')) $orderData['order_type'] = 'pos';
            if (Schema::hasColumn('orders', 'order_source')) $orderData['order_source'] = 'pos';
            if ($request->filled('transaction_id') && Schema::hasColumn('orders', 'transaction_id')) {
                $orderData['transaction_id'] = trim($request->transaction_id);
            }

            $orderId = DB::table('orders')->insertGetId($orderData);
            $itemsTable = Schema::hasTable('order_items') ? 'order_items' : (Schema::hasTable('order_details') ? 'order_details' : null);

            // 2. Order Item o Central Stock Deductions (InventoryService)
            foreach ($request->items as $item) {
                $productId = (int) $item['id'];
                $variantId = !empty($item['variant_id']) ? (int) $item['variant_id'] : null;
                $qty = (int) $item['qty'];
                $unitPrice = (float) $item['price'];

                $deduction = InventoryService::deductStock(
                    $productId,
                    $variantId,
                    $qty,
                    'pos_sale',
                    $invoiceNo,
                    $unitPrice,
                    'pos',
                    Auth::id(),
                    "POS Sale #{$invoiceNo} at Branch #{$request->branch_id}"
                );

                if (Schema::hasTable('product_stocks')) {
                    $bStockQuery = DB::table('product_stocks')
                        ->where('product_id', $productId)
                        ->where('branch_id', $request->branch_id);

                    if ($variantId && Schema::hasColumn('product_stocks', 'variant_id')) {
                        $bStockQuery->where('variant_id', $variantId);
                    }

                    $branchStock = $bStockQuery->first();
                    if ($branchStock) {
                        DB::table('product_stocks')->where('id', $branchStock->id)->decrement('quantity', $qty);
                    }
                }

                $variantTitle = null;
                $color = null;
                $size  = null;
                $sku   = $deduction['sku'] ?? null;

                $pTitle = DB::table('products')->where('id', $productId)->value('title') ?? 'Product';

                if ($variantId) {
                    $vRow = DB::table('product_variants')->where('id', $variantId)->first();
                    if ($vRow) {
                        $color = $vRow->color ?? null;
                        $size  = $vRow->size ?? null;
                        $parts = array_filter([$color, $size]);
                        $variantTitle = count($parts) > 0 ? implode(' / ', $parts) : ($vRow->name ?? null);
                        $sku = $vRow->product_code ?? ($vRow->sku ?? $sku);
                    }
                }

                if ($itemsTable) {
                    $itemData = [
                        'order_id'   => $orderId,
                        'product_id' => $productId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $condFields = [
                        'variant_id'        => $variantId,
                        'product_title'     => $pTitle,
                        'product_name'      => $pTitle,
                        'title'             => $pTitle,
                        'name'              => $pTitle,
                        'variant_title'     => $variantTitle,
                        'color'             => $color,
                        'size'              => $size,
                        'sku'               => $sku,
                        'quantity'          => $qty,
                        'qty'               => $qty,
                        'unit_price'        => $unitPrice,
                        'price'             => $unitPrice,
                        'subtotal'          => $unitPrice * $qty,
                        'total'             => $unitPrice * $qty,
                        'total_price'       => $unitPrice * $qty,
                        'purchase_cost'     => $deduction['purchase_cost'],
                        'cogs'              => $deduction['cogs'],
                        'is_cost_estimated' => $deduction['is_cost_estimated'],
                    ];

                    foreach ($condFields as $col => $val) {
                        if (Schema::hasColumn($itemsTable, $col)) {
                            $itemData[$col] = $val;
                        }
                    }

                    DB::table($itemsTable)->insert($itemData);
                }
            }

            // 3. Payment Record
            $savedPosOrder = DB::table('orders')->where('id', $orderId)->first();
            if ($savedPosOrder) {
                PaymentTransactionService::recordPosTransaction(
                    $savedPosOrder,
                    $paid,
                    (string) $request->payment_method,
                    $request->filled('transaction_id') ? trim($request->transaction_id) : null
                );
            }

            ActivityLogger::log('pos_order_place', 'orders', "POS Order completed with Invoice #{$invoiceNo}", [
                'record_id'  => $orderId,
                'record_ref' => $invoiceNo,
                'branch_id'  => $request->branch_id,
                'new_values' => [
                    'grand_total'    => $grandTotal,
                    'paid_amount'    => $paid,
                    'payment_method' => $request->payment_method,
                    'items_count'    => count($request->items),
                ],
            ]);

            DB::commit();

            return response()->json([
                'success'    => true,
                'message'    => 'Order placed successfully.',
                'invoice_no' => $invoiceNo,
                'order_id'   => $orderId
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Thermal Receipt Print View
     */
    public function printPosThermal($id)
    {
        $user = Auth::user();

        $order = DB::table('orders')
            ->leftJoin('branches', 'orders.branch_id', '=', 'branches.id')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->where('orders.id', $id)
            ->select(
                'orders.*', 
                'branches.name as branch_name', 
                'branches.phone as branch_phone', 
                'branches.address as branch_address', 
                'users.name as customer_name', 
                'users.phone as customer_phone'
            )
            ->first();

        if (!$order) abort(404, 'Order not found');

        if (!$user->isSuperAdmin() && !empty($user->branch_id) && (int)$order->branch_id !== (int)$user->branch_id) {
            abort(403, 'Unauthorized access to this branch memo.');
        }

        $globalSettings = DB::table('settings')->pluck('value', 'key');
        $siteTitle = $globalSettings['site_title'] ?? ($globalSettings['logo_title'] ?? config('app.name', 'Shop Now BD'));

        $order->subtotal = $order->subtotal ?? ($order->sub_total ?? 0);
        $order->shipping_charge = $order->shipping_charge ?? ($order->shipping_cost ?? ($order->delivery_charge ?? 0));
        $order->discount = $order->discount ?? ($order->discount_amount ?? 0);
        $order->grand_total = $order->grand_total ?? ($order->total_amount ?? ($order->total ?? 0));
        $order->paid_amount = $order->paid_amount ?? 0;
        $order->due_amount = $order->due_amount ?? max(0, $order->grand_total - $order->paid_amount);

        $itemsTable = Schema::hasTable('order_items') ? 'order_items' : 'order_details';

        $itemsQuery = DB::table($itemsTable)
            ->leftJoin('products', "{$itemsTable}.product_id", '=', 'products.id');

        if (Schema::hasColumn($itemsTable, 'variant_id')) {
            $itemsQuery->leftJoin('product_variants', "{$itemsTable}.variant_id", '=', 'product_variants.id')
                ->select(
                    "{$itemsTable}.*",
                    'products.title as main_title', 
                    'products.product_code as main_sku',
                    'product_variants.color as pv_color',
                    'product_variants.size as pv_size',
                    'product_variants.product_code as pv_sku'
                );
        } else {
            $itemsQuery->select(
                "{$itemsTable}.*",
                'products.title as main_title', 
                'products.product_code as main_sku'
            );
        }

        $items = $itemsQuery->where("{$itemsTable}.order_id", $id)->get();

        return view('admin.pos.print_thermal', compact('order', 'items', 'siteTitle'));
    }

    /**
     * A5 Memo Print View
     */
    public function printA5Memo($id)
    {
        $user = Auth::user();

        $order = DB::table('orders')
            ->leftJoin('branches', 'orders.branch_id', '=', 'branches.id')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->where('orders.id', $id)
            ->select(
                'orders.*', 
                'branches.name as branch_name', 
                'branches.phone as branch_phone', 
                'branches.address as branch_address', 
                'users.name as customer_name', 
                'users.phone as customer_phone', 
                'users.bio as customer_address'
            )
            ->first();

        if (!$order) abort(404, 'Order not found');

        if (!$user->isSuperAdmin() && !empty($user->branch_id) && (int)$order->branch_id !== (int)$user->branch_id) {
            abort(403, 'Unauthorized access to this branch memo.');
        }

        $globalSettings = DB::table('settings')->pluck('value', 'key');
        $siteTitle = $globalSettings['site_title'] ?? ($globalSettings['logo_title'] ?? config('app.name', 'Shop Now BD'));

        $order->subtotal = $order->subtotal ?? ($order->sub_total ?? 0);
        $order->shipping_charge = $order->shipping_charge ?? ($order->shipping_cost ?? ($order->delivery_charge ?? 0));
        $order->discount = $order->discount ?? ($order->discount_amount ?? 0);
        $order->grand_total = $order->grand_total ?? ($order->total_amount ?? ($order->total ?? 0));
        $order->paid_amount = $order->paid_amount ?? 0;
        $order->due_amount = $order->due_amount ?? max(0, $order->grand_total - $order->paid_amount);

        $itemsTable = Schema::hasTable('order_items') ? 'order_items' : 'order_details';

        $itemsQuery = DB::table($itemsTable)
            ->leftJoin('products', "{$itemsTable}.product_id", '=', 'products.id');

        if (Schema::hasColumn($itemsTable, 'variant_id')) {
            $itemsQuery->leftJoin('product_variants', "{$itemsTable}.variant_id", '=', 'product_variants.id')
                ->select(
                    "{$itemsTable}.*",
                    'products.title as main_title', 
                    'products.product_code as main_sku',
                    'product_variants.color as pv_color',
                    'product_variants.size as pv_size',
                    'product_variants.product_code as pv_sku'
                );
        } else {
            $itemsQuery->select(
                "{$itemsTable}.*",
                'products.title as main_title', 
                'products.product_code as main_sku'
            );
        }

        $items = $itemsQuery->where("{$itemsTable}.order_id", $id)->get();

        return view('admin.pos.print_a5', compact('order', 'items', 'siteTitle'));
    }
}