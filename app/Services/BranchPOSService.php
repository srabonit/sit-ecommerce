<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductStock;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BranchPOSService
{
    // =========================================================================
    // 1. TERMINAL ACCESS & SESSION VALIDATION
    // =========================================================================

    public static function validateTerminalAccess(int $branchId, ?User $user = null): Branch
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            abort(401, 'Unauthenticated user.');
        }

        $branch = BranchAccessService::validateBranchAccessOrAbort($branchId, 'pos', $user);

        BranchAccessService::setCurrentBranchId($branchId, $user);

        return $branch;
    }

    // =========================================================================
    // 2. BARCODE SCAN & PRODUCT RESOLUTION (Barcode, SKU, Code, ID)
    // =========================================================================

    public static function scanBarcode(int $branchId, string $scannedCode): array
    {
        $code = trim($scannedCode);
        if (empty($code)) {
            throw new Exception("Scanned barcode cannot be empty.");
        }

        // 1. Check in Product Variants first (SKU, product_code, barcode)
        $variant = ProductVariant::with(['product'])
            ->where(function ($q) use ($code) {
                if (Schema::hasColumn('product_variants', 'sku')) {
                    $q->where('sku', $code);
                }
                if (Schema::hasColumn('product_variants', 'product_code')) {
                    $q->orWhere('product_code', $code);
                }
                if (Schema::hasColumn('product_variants', 'barcode')) {
                    $q->orWhere('barcode', $code);
                }
            })
            ->whereHas('product', function ($pq) {
                if (Schema::hasColumn('products', 'is_active')) {
                    $pq->where('products.is_active', true);
                } elseif (Schema::hasColumn('products', 'status')) {
                    $pq->where(function ($sq) {
                        $sq->where('products.status', 'active')
                           ->orWhere('products.status', 1)
                           ->orWhere('products.status', '1');
                    });
                }
            })
            ->first();

        if ($variant && $variant->product) {
            return self::formatCartItemPayload($branchId, $variant->product, $variant);
        }

        // 2. Check in Base Products (Barcode, SKU, product_code, ID)
        $cleanId = ltrim($code, '#');
        $productQuery = Product::with(['variants'])
            ->where(function ($q) use ($code, $cleanId) {
                if (is_numeric($cleanId)) {
                    $q->where('id', (int)$cleanId);
                }
                if (Schema::hasColumn('products', 'barcode')) {
                    $q->orWhere('barcode', $code);
                }
                if (Schema::hasColumn('products', 'sku')) {
                    $q->orWhere('sku', $code);
                }
                if (Schema::hasColumn('products', 'product_code')) {
                    $q->orWhere('product_code', $code);
                }
            });

        if (Schema::hasColumn('products', 'is_active')) {
            $productQuery->where('products.is_active', true);
        } elseif (Schema::hasColumn('products', 'status')) {
            $productQuery->where(function ($sq) {
                $sq->where('products.status', 'active')
                   ->orWhere('products.status', 1)
                   ->orWhere('products.status', '1');
            });
        }

        $product = $productQuery->first();

        if ($product) {
            // Main SKU scan on variable product triggers variant selection modal
            if ($product->is_variable) {
                return [
                    'success'       => false,
                    'matched'       => 'multiple',
                    'is_variable'   => true,
                    'product_id'    => $product->id,
                    'title'         => $product->title ?? ($product->name ?? 'Product'),
                    'message'       => 'Variable product found. Please select color/size.',
                    'variants'      => $product->variants->map(function ($v) use ($branchId, $product) {
                        $pricing = BranchProductService::resolvePrice($product, $branchId, $v->id);
                        $availStock = $v->getBranchStock($branchId);
                        $skuCode = $v->sku ?? $v->product_code;
                        return [
                            'product_id'          => $product->id,
                            'variant_id'          => $v->id,
                            'main_title'          => $product->title ?? ($product->name ?? 'Product'),
                            'title'               => $v->variant_title,
                            'sku'                 => $skuCode,
                            'variant_sku'         => $skuCode,
                            'price'               => $pricing['effective_price'],
                            'variant_sale_price'  => $pricing['effective_price'],
                            'regular_price'       => $pricing['regular_price'],
                            'available_stock'     => $availStock,
                            'branch_stock'        => $availStock,
                            'stock'               => $availStock,
                            'is_in_stock'         => $availStock > 0,
                            'color'               => $v->color,
                            'size'                => $v->size,
                        ];
                    }),
                ];
            }

            return self::formatCartItemPayload($branchId, $product, null);
        }

        throw new Exception("No product found matching barcode or SKU: '{$code}'.");
    }

    protected static function formatCartItemPayload(int $branchId, Product $product, ?ProductVariant $variant = null): array
    {
        if (!BranchProductService::isProductSellableInBranch($product, $branchId, true)) {
            $branch = Branch::find($branchId);
            $branchName = $branch ? $branch->name : "#{$branchId}";
            $pName = $product->title ?? ($product->name ?? 'Product');
            throw new Exception("Product '{$pName}' is restricted or not authorized for sale in {$branchName}.");
        }

        $variantId = $variant?->id;
        $availableStock = $variant ? $variant->getBranchStock($branchId) : $product->getBranchStock($branchId);
        $pricing = BranchProductService::resolvePrice($product, $branchId, $variantId);

        $title = $product->title ?? ($product->name ?? 'Product');
        if ($variant) {
            $title .= ' (' . $variant->variant_title . ')';
        }

        $thumbnail = $variant && !empty($variant->image) 
            ? $variant->image_url 
            : $product->thumbnail_url;

        $skuCode = $variant?->sku ?? ($variant?->product_code ?? ($product->sku ?? ($product->product_code ?? 'PRD-' . $product->id)));

        return [
            'success'               => true,
            'is_variable'           => false,
            'cart_key'              => $variantId ? "{$product->id}_{$variantId}" : "{$product->id}_0",
            'product_id'            => $product->id,
            'id'                    => $product->id,
            'variant_id'            => $variantId,
            'title'                 => $title,
            'sku'                   => $skuCode,
            'product_code'          => $skuCode,
            'unit_price'            => $pricing['effective_price'],
            'price'                 => $pricing['effective_price'],
            'regular_price'         => $pricing['regular_price'],
            'sale_price'            => $pricing['sale_price'],
            'purchase_cost'         => $variant ? $variant->effective_purchase_cost : $product->effective_purchase_cost,
            'available_stock'       => $availableStock,
            'stock'                 => $availableStock,
            'is_in_stock'           => $availableStock > 0,
            'is_branch_override'    => $pricing['is_branch_override'],
            'min_selling_price'     => $pricing['min_selling_price'],
            'thumbnail'             => $thumbnail,
            'color'                 => $variant?->color,
            'size'                  => $variant?->size,
        ];
    }

    // =========================================================================
    // 3. CART VALIDATION & TOTALS CALCULATION
    // =========================================================================

    public static function validateCartItems(int $branchId, array $cartItems): array
    {
        if (empty($cartItems)) {
            throw new Exception("Cart cannot be empty.");
        }

        $validatedItems = [];

        foreach ($cartItems as $index => $item) {
            $productId = (int) ($item['product_id'] ?? ($item['id'] ?? 0));
            $variantId = !empty($item['variant_id']) ? (int) $item['variant_id'] : null;
            $quantity  = max(1, (int) ($item['qty'] ?? 1));

            $product = Product::find($productId);
            if (!$product || !$product->is_active) {
                throw new Exception("Product at item #" . ($index + 1) . " is inactive or not found.");
            }

            if (!BranchProductService::isProductSellableInBranch($product, $branchId, true)) {
                $pName = $product->title ?? ($product->name ?? 'Product');
                throw new Exception("Product '{$pName}' is not authorized for sale in this branch.");
            }

            $availableStock = $variantId ? (ProductVariant::find($variantId)?->getBranchStock($branchId) ?? 0) : $product->getBranchStock($branchId);
            if ($availableStock > 0 && $availableStock < $quantity) {
                $variantInfo = '';
                if ($variantId) {
                    $variant = ProductVariant::find($variantId);
                    $variantInfo = $variant ? " ({$variant->variant_title})" : '';
                }
                $pName = $product->title ?? ($product->name ?? 'Product');
                throw new Exception("Insufficient stock for '{$pName}{$variantInfo}'. Available: {$availableStock}, In Cart: {$quantity}.");
            }

            $pricing = BranchProductService::resolvePrice($product, $branchId, $variantId);
            $unitPrice = !empty($item['unit_price']) && (float)$item['unit_price'] > 0
                ? (float)$item['unit_price']
                : $pricing['effective_price'];

            $lineTotal = round($unitPrice * $quantity, 2);

            $variant = $variantId ? ProductVariant::find($variantId) : null;
            $purchaseCost = $variant ? $variant->effective_purchase_cost : $product->effective_purchase_cost;
            $skuCode = $item['sku'] ?? ($variant?->sku ?? ($variant?->product_code ?? ($product->sku ?? $product->product_code)));

            $validatedItems[] = [
                'product_id'    => $productId,
                'variant_id'    => $variantId,
                'title'         => $product->title ?? ($product->name ?? 'Product'),
                'sku'           => $skuCode,
                'color'         => $variant?->color,
                'size'          => $variant?->size,
                'qty'           => $quantity,
                'unit_price'    => $unitPrice,
                'regular_price' => $pricing['regular_price'],
                'purchase_cost' => $purchaseCost,
                'total_price'   => $lineTotal,
            ];
        }

        return $validatedItems;
    }

    public static function calculateCartTotals(
        array $validatedItems,
        float $cartDiscount = 0.00,
        float $taxAmount = 0.00,
        float $deliveryCharge = 0.00
    ): array {
        $subtotal = 0.00;
        $totalCost = 0.00;

        foreach ($validatedItems as $item) {
            $subtotal += (float) $item['total_price'];
            $totalCost += (float) ($item['purchase_cost'] * $item['qty']);
        }

        $subtotal = round($subtotal, 2);
        $cartDiscount = round(max(0, $cartDiscount), 2);
        $taxAmount = round(max(0, $taxAmount), 2);
        $deliveryCharge = round(max(0, $deliveryCharge), 2);

        $grandTotal = max(0.00, ($subtotal - $cartDiscount) + $taxAmount + $deliveryCharge);
        $grandTotal = round($grandTotal, 2);

        return [
            'subtotal'        => $subtotal,
            'total_cost'      => round($totalCost, 2),
            'discount_amount' => $cartDiscount,
            'tax_amount'      => $taxAmount,
            'delivery_charge' => $deliveryCharge,
            'grand_total'     => $grandTotal,
        ];
    }

    // =========================================================================
    // 4. ATOMIC POS CHECKOUT ORCHESTRATION
    // =========================================================================

    public static function processCheckout(int $branchId, array $data, ?User $cashier = null): array
    {
        $cashier = $cashier ?? Auth::user();
        $branch = self::validateTerminalAccess($branchId, $cashier);

        return DB::transaction(function () use ($branch, $data, $cashier) {
            $branchId = $branch->id;

            // 1. Validate Cart Items & Check Stock
            $rawItems = $data['items'] ?? [];
            $validatedItems = self::validateCartItems($branchId, $rawItems);

            // 2. Calculate Totals
            $cartDiscount = (float) ($data['discount_amount'] ?? 0.00);
            $taxAmount    = (float) ($data['tax_amount'] ?? 0.00);
            $delivery     = (float) ($data['delivery_charge'] ?? 0.00);

            $totals = self::calculateCartTotals($validatedItems, $cartDiscount, $taxAmount, $delivery);

            $grandTotal  = $totals['grand_total'];
            $paidAmount  = round(min($grandTotal, max(0.00, (float) ($data['paid_amount'] ?? $grandTotal))), 2);
            $dueAmount   = round(max(0.00, $grandTotal - $paidAmount), 2);

            // 3. Customer Resolution
            $customerId = !empty($data['customer_id']) ? (int) $data['customer_id'] : null;
            if (!$customerId) {
                $walkIn = User::where('email', 'walkin@store.local')->orWhere('phone', '01000000000')->first();
                $customerId = $walkIn ? $walkIn->id : $cashier->id;
            }

            $salesPersonId = !empty($data['sales_person_id']) ? (int) $data['sales_person_id'] : $cashier->id;
            $paymentMethod = $data['payment_method'] ?? 'cash';

            // 4. Generate Unique Sequential Invoice Number
            $invoiceNo = self::generateInvoiceNumber($branch);

            // 5. Create Order Record (Schema-Safe)
            $orderData = [
                'invoice_no'        => $invoiceNo,
                'branch_id'         => $branchId,
                'user_id'           => $customerId,
                'sales_person_id'   => $salesPersonId,
                'order_type'        => 'pos',
                'subtotal'          => $totals['subtotal'],
                'discount_amount'   => $totals['discount_amount'],
                'tax_amount'        => $totals['tax_amount'],
                'grand_total'       => $grandTotal,
                'paid_amount'       => $paidAmount,
                'due_amount'        => $dueAmount,
                'payment_method'    => $paymentMethod,
                'transaction_id'    => $data['transaction_id'] ?? null,
                'sender_number'     => $data['sender_number'] ?? null,
                'payment_status'    => $dueAmount <= 0 ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid'),
                'order_status'      => 'delivered',
                'notes'             => $data['notes'] ?? 'POS In-Store Sale',
                'created_at'        => now(),
                'updated_at'        => now(),
            ];

            if (Schema::hasColumn('orders', 'warehouse_id')) {
                $orderData['warehouse_id'] = $branch->assigned_warehouse_id;
            }
            if (Schema::hasColumn('orders', 'created_by')) {
                $orderData['created_by'] = $cashier->id;
            }
            if (Schema::hasColumn('orders', 'shipping_charge')) {
                $orderData['shipping_charge'] = $totals['delivery_charge'];
            }
            if (Schema::hasColumn('orders', 'delivery_charge')) {
                $orderData['delivery_charge'] = $totals['delivery_charge'];
            }

            $safeOrderData = [];
            foreach ($orderData as $col => $val) {
                if (Schema::hasColumn('orders', $col)) {
                    $safeOrderData[$col] = $val;
                }
            }

            $order = Order::create($safeOrderData);

            // 6. Create Order Items (Schema-Safe)
            foreach ($validatedItems as $item) {
                $candidateItemData = [
                    'order_id'      => $order->id,
                    'product_id'    => $item['product_id'],
                    'variant_id'    => $item['variant_id'],
                    'product_title' => $item['title'],
                    'title'         => $item['title'],
                    'name'          => $item['title'],
                    'sku'           => $item['sku'] ?? null,
                    'quantity'      => $item['qty'],
                    'qty'           => $item['qty'],
                    'unit_price'    => $item['unit_price'],
                    'price'         => $item['unit_price'],
                    'subtotal'      => $item['total_price'],
                    'total'         => $item['total_price'],
                    'total_price'   => $item['total_price'],
                    'purchase_cost' => $item['purchase_cost'],
                    'color'         => $item['color'],
                    'size'          => $item['size'],
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];

                $safeItemData = [];
                foreach ($candidateItemData as $col => $val) {
                    if (Schema::hasColumn('order_items', $col)) {
                        $safeItemData[$col] = $val;
                    }
                }

                DB::table('order_items')->insert($safeItemData);

                // Auto-create ProductStock record if not existing yet for this branch
                if (Schema::hasTable('product_stocks')) {
                    $stockCheck = [
                        'branch_id'  => $branchId,
                        'product_id' => $item['product_id'],
                        'variant_id' => $item['variant_id'],
                    ];
                    $stockExists = ProductStock::where($stockCheck)->exists();
                    if (!$stockExists) {
                        $initStock = (int)($item['qty'] + 50);
                        ProductStock::create(array_merge($stockCheck, [
                            'quantity'          => $initStock,
                            'reserved_quantity' => 0,
                            'alert_quantity'    => 5,
                        ]));
                    }
                }

                // Deduct stock safely
                BranchInventoryService::stockOut(
                    branchId: $branchId,
                    productId: $item['product_id'],
                    variantId: $item['variant_id'],
                    quantity: $item['qty'],
                    unitCost: $item['purchase_cost'],
                    movementType: 'pos_sale',
                    referenceType: 'order',
                    referenceId: $order->id,
                    note: "POS Sale Invoice #{$invoiceNo}",
                    userId: $cashier->id,
                    allowNegative: false
                );
            }

            // 7. Sync with Branch Daily Cash Register
            self::attributeToDailyClosing($branchId, $paidAmount, $paymentMethod);

            return [
                'success'      => true,
                'order_id'     => $order->id,
                'invoice_no'   => $order->invoice_no,
                'grand_total'  => $grandTotal,
                'paid_amount'  => $paidAmount,
                'due_amount'   => $dueAmount,
                'print_url'    => route('admin.pos.print.thermal', $order->id),
                'memo_url'     => route('admin.pos.print.a5', $order->id),
            ];
        });
    }

    // =========================================================================
    // 5. HELPER UTILITIES: INVOICING, CUSTOMER & DAILY CLOSING
    // =========================================================================

    // public static function generateInvoiceNumber(Branch $branch): string
    // {
    //     $code = strtoupper(trim((string)($branch->branch_code ?: 'BR' . $branch->id)));
    //     $dateStr = now()->format('Ymd');
    //     $prefix = "POS-{$code}-{$dateStr}-";

    //     $count = DB::table('orders')
    //         ->where('branch_id', $branch->id)
    //         ->whereDate('created_at', now()->toDateString())
    //         ->count() + 1;

    //     $invoiceNo = $prefix . str_pad((string)$count, 4, '0', STR_PAD_LEFT);

    //     while (DB::table('orders')->where('invoice_no', $invoiceNo)->exists()) {
    //         $count++;
    //         $invoiceNo = $prefix . str_pad((string)$count, 4, '0', STR_PAD_LEFT);
    //     }

    //     return $invoiceNo;
    // }

// public static function generateInvoiceNumber(Branch $branch): string
//     {
//         // ব্রাঞ্চের কোড রিড করা (না থাকলে ডিফল্ট 'BR' অথবা ব্রাঞ্চের আইডি ব্যবহার হবে)
//         $branchCode = strtolower(trim((string)($branch->code ?? ($branch->branch_code ?? 'br' . $branch->id))));

//         // ব্রাঞ্চ কোড + হাইফেন + ৪ ডিজিটের ইউনিক রেন্ডম সংখ্যা (যেমন: mai-0978)
//         do {
//             $randomNum = mt_rand(1000, 9999);
//             $invoiceNo = $branchCode . '-' . $randomNum;
//         } while (DB::table('orders')->where('invoice_no', $invoiceNo)->exists());

//         return $invoiceNo;
//     }

public static function generateInvoiceNumber($branch = null): string
    {
        // 'pos-' এবং ১ থেকে ৯৯৯৯৯ এর মধ্যে ইউনিক রেন্ডম সংখ্যা নিশ্চিতকরণ
        do {
            $randomNum = mt_rand(1, 99999);
            $invoiceNo = 'POS-' . $randomNum;
        } while (DB::table('orders')->where('invoice_no', $invoiceNo)->exists());

        return $invoiceNo;
    }


    public static function quickCreateCustomer(array $customerData): User
    {
        $phone = trim($customerData['phone'] ?? '');
        $name  = trim($customerData['name'] ?? 'Guest Customer');

        if (empty($phone)) {
            throw new Exception("Customer phone number is required.");
        }

        $existing = User::where('phone', $phone)->first();
        if ($existing) {
            return $existing;
        }

        // Duplicate email validation with explicit exception
        $inputEmail = !empty($customerData['email']) ? strtolower(trim($customerData['email'])) : null;
        if ($inputEmail) {
            if (User::where('email', $inputEmail)->exists()) {
                throw new Exception("This email address ('{$inputEmail}') is already in use by another customer. Please use a different email or select the existing customer.");
            }
            $email = $inputEmail;
        } else {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            $email = 'c_' . $cleanPhone . '_' . substr(uniqid(), -4) . '@customer.local';
        }

        $userData = [
            'name'              => $name,
            'phone'             => $phone,
            'email'             => $email,
            'password'          => Hash::make(Str::random(12)),
            'phone_verified_at' => now(),
        ];

        // Schema-Safe Address / Bio Mapping
        $addressVal = $customerData['address'] ?? null;
        if (Schema::hasColumn('users', 'address')) {
            $userData['address'] = $addressVal;
        } elseif (Schema::hasColumn('users', 'bio')) {
            $userData['bio'] = $addressVal;
        }

        if (Schema::hasColumn('users', 'role')) {
            $userData['role'] = 'customer';
        }
        if (Schema::hasColumn('users', 'user_type')) {
            $userData['user_type'] = 'customer';
        }
        if (Schema::hasColumn('users', 'monthly_salary')) {
            $userData['monthly_salary'] = 0;
        }
        if (Schema::hasColumn('users', 'created_by')) {
            $userData['created_by'] = Auth::id();
        }
        if (Schema::hasColumn('users', 'is_active')) {
            $userData['is_active'] = true;
        }
        if (Schema::hasColumn('users', 'status')) {
            $userData['status'] = 'active';
        }

        return User::create($userData);
    }

    protected static function attributeToDailyClosing(int $branchId, float $paidAmount, string $paymentMethod): void
    {
        if ($paidAmount <= 0) {
            return;
        }

        try {
            if (!Schema::hasTable('branch_daily_closings')) {
                return;
            }

            $todayClosing = DB::table('branch_daily_closings')
                ->where('branch_id', $branchId)
                ->where('closing_date', now()->toDateString())
                ->where('status', 'open')
                ->first();

            if ($todayClosing) {
                $isCash = strtolower($paymentMethod) === 'cash';
                $cashPaid = $isCash ? $paidAmount : 0.00;
                $digitalPaid = !$isCash ? $paidAmount : 0.00;

                DB::table('branch_daily_closings')
                    ->where('id', $todayClosing->id)
                    ->update([
                        'pos_cash_sales'       => DB::raw("pos_cash_sales + {$cashPaid}"),
                        'digital_sales_amount' => DB::raw("digital_sales_amount + {$digitalPaid}"),
                        'total_sales_amount'   => DB::raw("total_sales_amount + {$paidAmount}"),
                        'total_orders_count'   => DB::raw("total_orders_count + 1"),
                        'expected_cash'        => DB::raw("expected_cash + {$cashPaid}"),
                        'updated_at'           => now(),
                    ]);
            }
        } catch (\Throwable $e) {
            // Non-blocking fallback
        }
    }

    public static function getTodayPosStats(int $branchId): array
    {
        $today = now()->toDateString();

        $stats = DB::table('orders')
            ->where('branch_id', $branchId)
            ->where('order_type', 'pos')
            ->whereDate('created_at', $today)
            ->selectRaw("
                COUNT(id) as total_orders,
                COALESCE(SUM(grand_total), 0) as total_sales,
                COALESCE(SUM(paid_amount), 0) as total_paid,
                COALESCE(SUM(due_amount), 0) as total_due
            ")
            ->first();

        return [
            'total_orders' => (int) ($stats->total_orders ?? 0),
            'total_sales'  => (float) ($stats->total_sales ?? 0.00),
            'total_paid'   => (float) ($stats->total_paid ?? 0.00),
            'total_due'    => (float) ($stats->total_due ?? 0.00),
        ];
    }
}