<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Services\ImagePathService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CartController extends Controller
{
    private function getDynamicFreeShippingMin($subtotal = 0)
    {
        $minAmount = 1500.00;
        try {
            $allZones = ShippingZone::with('methods')->get();
            foreach ($allZones as $zone) {
                foreach ($zone->methods as $method) {
                    if ($method->method_type === 'free_shipping' && ($method->is_enabled == 1 || $method->is_enabled === true)) {
                        if ($method->free_shipping_requires === 'min_amount' && $method->min_amount > 0) {
                            $minAmount = (float) $method->min_amount;
                            break 2;
                        } elseif ($method->free_shipping_requires === 'no_requirement') {
                            return 0.00;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $minAmount = 1500.00;
        }
        return $minAmount;
    }

    public function index(Request $request)
    {
        $cart = Session::get('cart', []);

        if ($request->ajax() || $request->wantsJson()) {
            $total = 0;
            $itemsCount = 0;
            foreach ($cart as $item) {
                $total += (float)$item['price'] * (int)$item['quantity'];
                $itemsCount += (int)$item['quantity'];
            }

            $freeShippingMin = $this->getDynamicFreeShippingMin($total);
            $remainingForFree = max(0, $freeShippingMin - $total);
            $isFreeShipping = ($freeShippingMin === 0.00 || $total >= $freeShippingMin);

            return response()->json([
                'status'              => 'success',
                'cart'                => $cart,
                'cart_count'          => $itemsCount,
                'subtotal'            => number_format($total, 2, '.', ''),
                'grand_total'         => number_format($total, 2, '.', ''),
                'free_shipping_min'   => number_format($freeShippingMin, 2, '.', ''),
                'remaining_free_ship' => number_format($remainingForFree, 2, '.', ''),
                'is_free_shipping'    => $isFreeShipping,
                'html'                => view('frontend.partials.cart_drawer_items', compact('cart', 'total'))->render()
            ]);
        }

        return view('frontend.cart', compact('cart'));
    }

    public function add(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $cart = Session::get('cart', []);

        $qty = max(1, (int) $request->input('quantity', 1));
        $variantId = $request->input('variant_id');
        $variantName = null;
        $color = null;
        $size = null;
        $sku = $product->sku ?? ($product->product_code ?? ('PRD-' . $product->id));

        if (!empty($variantId)) {
            $variant = null;
            if (method_exists($product, 'variants')) {
                $variant = $product->variants()->where('id', $variantId)->first();
            }
            if (!$variant && Schema::hasTable('product_variants')) {
                $variant = DB::table('product_variants')->where('id', $variantId)->first();
            }

            if ($variant) {
                $color = $variant->color ?? null;
                $size  = $variant->size ?? null;
                $parts = array_filter([$color, $size]);
                $variantName = count($parts) > 0 ? implode(' / ', $parts) : ($variant->name ?? ($variant->title ?? null));

                if (!empty($variant->product_code)) {
                    $sku = $variant->product_code;
                } elseif (!empty($variant->sku)) {
                    $sku = $variant->sku;
                }
            }
        }

        $cartKey = $variantId ? $id . '_' . $variantId : (string) $id;
        $price = (float) ($product->sale_price ?? $product->price ?? $product->regular_price ?? 0);

        if (!empty($variant)) {
            if (!empty($variant->sale_price) && (float)$variant->sale_price > 0) {
                $price = (float)$variant->sale_price;
            } elseif (!empty($variant->regular_price) && (float)$variant->regular_price > 0) {
                $price = (float)$variant->regular_price;
            }
        }

        // Central ImagePathService integration for variant and thumbnail
        $rawImg = !empty($variant->image) ? $variant->image : ($product->thumbnail ?? null);
        $img = ImagePathService::resolveUrl($rawImg, 'https://placehold.co/400x400?text=No+Image');

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $qty;
        } else {
            $cart[$cartKey] = [
                'id'           => $product->id,
                'cart_key'     => $cartKey,
                'title'        => $product->title ?? $product->name,
                'slug'         => $product->slug ?? $product->id,
                'price'        => $price,
                'quantity'     => $qty,
                'image'        => $img,
                'variant_id'   => $variantId,
                'variant_name' => $variantName,
                'color'        => $color,
                'size'         => $size,
                'sku'          => $sku,
            ];
        }

        Session::put('cart', $cart);

        $totalItems = 0;
        $totalAmount = 0;
        foreach ($cart as $item) {
            $totalItems += (int) $item['quantity'];
            $totalAmount += (float) $item['price'] * (int) $item['quantity'];
        }

        if ($request->input('action') === 'order_now') {
            return redirect()->route('checkout');
        }

        $freeShippingMin = $this->getDynamicFreeShippingMin($totalAmount);
        $remainingForFree = max(0, $freeShippingMin - $totalAmount);
        $isFreeShipping = ($freeShippingMin === 0.00 || $totalAmount >= $freeShippingMin);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'              => 'success',
                'message'             => 'Product added to cart!',
                'cart_count'          => $totalItems,
                'subtotal'            => number_format($totalAmount, 2, '.', ''),
                'grand_total'         => number_format($totalAmount, 2, '.', ''),
                'free_shipping_min'   => number_format($freeShippingMin, 2, '.', ''),
                'remaining_free_ship' => number_format($remainingForFree, 2, '.', ''),
                'is_free_shipping'    => $isFreeShipping,
                'html'                => view('frontend.partials.cart_drawer_items', compact('cart', 'totalAmount'))->render()
            ]);
        }

        return redirect()->back()->with('success', 'Product added to cart!');
    }

    public function updateQty(Request $request)
    {
        $cartKey = $request->input('cart_key') ?? $request->input('id');
        $action  = $request->input('type'); 
        $cart    = Session::get('cart', []);

        if (isset($cart[$cartKey])) {
            if ($action === 'inc') {
                $cart[$cartKey]['quantity'] += 1;
            } elseif ($action === 'dec') {
                $cart[$cartKey]['quantity'] -= 1;
                if ($cart[$cartKey]['quantity'] <= 0) {
                    unset($cart[$cartKey]);
                }
            } else {
                $newQty = (int) $request->input('quantity', 1);
                if ($newQty > 0) {
                    $cart[$cartKey]['quantity'] = $newQty;
                } else {
                    unset($cart[$cartKey]);
                }
            }
            Session::put('cart', $cart);
        }

        $totalItems = 0;
        $totalAmount = 0;
        foreach ($cart as $item) {
            $totalItems += (int) $item['quantity'];
            $totalAmount += (float) $item['price'] * (int) $item['quantity'];
        }

        $itemTotal = isset($cart[$cartKey]) ? ((float)$cart[$cartKey]['price'] * (int)$cart[$cartKey]['quantity']) : 0;

        $freeShippingMin = $this->getDynamicFreeShippingMin($totalAmount);
        $remainingForFree = max(0, $freeShippingMin - $totalAmount);
        $isFreeShipping = ($freeShippingMin === 0.00 || $totalAmount >= $freeShippingMin);

        return response()->json([
            'status'              => 'success',
            'cart_count'          => $totalItems,
            'item_total'          => number_format($itemTotal, 2, '.', ''),
            'subtotal'            => number_format($totalAmount, 2, '.', ''),
            'cart_subtotal'       => number_format($totalAmount, 2, '.', ''),
            'grand_total'         => number_format($totalAmount, 2, '.', ''),
            'free_shipping_min'   => number_format($freeShippingMin, 2, '.', ''),
            'remaining_free_ship' => number_format($remainingForFree, 2, '.', ''),
            'is_free_shipping'    => $isFreeShipping,
            'is_empty'            => empty($cart),
            'html'                => view('frontend.partials.cart_drawer_items', ['cart' => $cart, 'total' => $totalAmount])->render()
        ]);
    }

    public function remove(Request $request)
    {
        $cartKey = $request->input('cart_key') ?? $request->input('id');
        $cart = Session::get('cart', []);
        $removed = false;

        if ($cartKey === 'all_clear') {
            Session::forget('cart');
            $removed = true;
            $cart = [];
        } elseif (isset($cart[$cartKey])) {
            unset($cart[$cartKey]);
            Session::put('cart', $cart);
            $removed = true;
        }

        $totalItems = 0;
        $totalAmount = 0;
        foreach ($cart as $item) {
            $totalItems += (int) $item['quantity'];
            $totalAmount += (float) $item['price'] * (int) $item['quantity'];
        }

        $freeShippingMin = $this->getDynamicFreeShippingMin($totalAmount);
        $remainingForFree = max(0, $freeShippingMin - $totalAmount);
        $isFreeShipping = ($freeShippingMin === 0.00 || $totalAmount >= $freeShippingMin);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'              => $removed ? 'success' : 'error',
                'cart_count'          => $totalItems,
                'subtotal'            => number_format($totalAmount, 2, '.', ''),
                'cart_subtotal'       => number_format($totalAmount, 2, '.', ''),
                'grand_total'         => number_format($totalAmount, 2, '.', ''),
                'free_shipping_min'   => number_format($freeShippingMin, 2, '.', ''),
                'remaining_free_ship' => number_format($remainingForFree, 2, '.', ''),
                'is_free_shipping'    => $isFreeShipping,
                'is_empty'            => empty($cart),
                'html'                => view('frontend.partials.cart_drawer_items', ['cart' => $cart, 'total' => $totalAmount])->render()
            ]);
        }

        return redirect()->back();
    }

    public function clear()
    {
        Session::forget('cart');
        return response()->json([
            'status'              => 'success',
            'cart_count'          => 0,
            'subtotal'            => '0.00',
            'cart_subtotal'       => '0.00',
            'grand_total'         => '0.00',
            'free_shipping_min'   => '1500.00',
            'remaining_free_ship' => '1500.00',
            'is_free_shipping'    => false,
            'is_empty'            => true,
            'html'                => view('frontend.partials.cart_drawer_items', ['cart' => [], 'total' => 0])->render()
        ]);
    }

    public function checkout()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty!');
        }
        return view('frontend.checkout', compact('cart'));
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'required|string|max:20',
            'address'        => 'required|string',
            'delivery_zone'  => 'required|in:inside,outside',
            'payment_method' => 'required|string|in:cod,bkash,card',
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty!');
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += ($item['price'] * $item['quantity']);
        }

        $deliveryCharge = ($request->delivery_zone === 'inside') ? 70 : 130;
        $grandTotal = $subtotal + $deliveryCharge;

        $orderInfo = [
            'order_id'        => 'ORD-' . strtoupper(uniqid()),
            'customer'        => [
                'name'          => $request->name,
                'phone'         => $request->phone,
                'address'       => $request->address,
                'delivery_zone' => $request->delivery_zone,
            ],
            'items'           => $cart,
            'subtotal'        => $subtotal,
            'delivery_charge' => $deliveryCharge,
            'grand_total'     => $grandTotal,
            'payment_method'  => $request->payment_method,
        ];

        if (Schema::hasTable('orders')) {
            try {
                $orderId = DB::table('orders')->insertGetId([
                    'invoice_no'        => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(5)),
                    'customer_name'     => $request->name,
                    'customer_phone'    => $request->phone,
                    'customer_address'  => $request->address,
                    'shipping_name'     => $request->name,
                    'shipping_phone'    => $request->phone,
                    'shipping_address'  => $request->address,
                    'district'          => $request->delivery_zone === 'inside' ? 'Dhaka' : 'Outside Dhaka',
                    'subtotal'          => $subtotal,
                    'shipping_charge'   => $deliveryCharge,
                    'grand_total'       => $grandTotal,
                    'payment_method'    => $request->payment_method,
                    'payment_status'    => 'unpaid',
                    'order_status'      => 'pending',
                    'notes'             => "Customer: {$request->name}, Phone: {$request->phone}, Address: {$request->address}",
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                if (Schema::hasTable('order_items')) {
                    foreach ($cart as $cItem) {
                        $variantId = $cItem['variant_id'] ?? null;
                        $variantTitle = $cItem['variant_name'] ?? null;
                        $color = $cItem['color'] ?? null;
                        $size = $cItem['size'] ?? null;
                        $sku = $cItem['sku'] ?? null;

                        if (!empty($variantId) && (empty($color) || empty($size))) {
                            $vData = DB::table('product_variants')->where('id', $variantId)->first();
                            if ($vData) {
                                $color = $vData->color ?? $color;
                                $size  = $vData->size ?? $size;
                                $sku   = $vData->product_code ?? ($vData->sku ?? $sku);
                                $parts = array_filter([$color, $size]);
                                $variantTitle = count($parts) > 0 ? implode(' / ', $parts) : ($vData->name ?? $variantTitle);
                            }
                        }

                        $pName = $cItem['title'] ?? null;
                        if (empty($pName) && !empty($cItem['id'])) {
                            $pName = DB::table('products')->where('id', $cItem['id'])->value('title');
                        }

                        $itemRow = [
                            'order_id'       => $orderId,
                            'product_id'     => $cItem['id'] ?? ($cItem['product_id'] ?? null),
                            'quantity'       => $cItem['quantity'],
                            'unit_price'     => $cItem['price'],
                            'subtotal'       => (float)$cItem['price'] * (int)$cItem['quantity'],
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ];

                        if (Schema::hasColumn('order_items', 'product_title')) $itemRow['product_title'] = $pName ?? ('Product #' . $cItem['id']);
                        if (Schema::hasColumn('order_items', 'variant_id')) $itemRow['variant_id'] = $variantId;
                        if (Schema::hasColumn('order_items', 'variant_title')) $itemRow['variant_title'] = $variantTitle;
                        if (Schema::hasColumn('order_items', 'color')) $itemRow['color'] = $color;
                        if (Schema::hasColumn('order_items', 'size')) $itemRow['size'] = $size;
                        if (Schema::hasColumn('order_items', 'sku')) $itemRow['sku'] = $sku;

                        DB::table('order_items')->insert($itemRow);
                    }
                }
            } catch (\Exception $e) {
                // error ignored
            }
        }

        session()->forget('cart');
        session()->put('last_order', $orderInfo);

        return redirect()->route('order.success');
    }

    public function success()
    {
        $orderInfo = session()->get('last_order');
        if (!$orderInfo) {
            return redirect()->route('home');
        }
        return view('frontend.order-success', compact('orderInfo'));
    }
}