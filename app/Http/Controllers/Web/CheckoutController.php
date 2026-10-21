<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ManualPaymentMethod;
use App\Models\ShippingZone;
use App\Models\ShippingClassRate;
use App\Models\ShippingZoneMethod;
use App\Models\Product;
use App\Models\EmailAbandonedCart;
use App\Mail\OrderPlacedMail;
use App\Mail\AdminOrderNotificationMail;
use App\Services\DigitalProductService;
use App\Services\PaymentTransactionService;
use App\Services\ImagePathService;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /**
     * Universal Upload Helper for Checkout across all public roots & auto-registers in Media Library
     */
    protected function saveCheckoutFile($file, $subFolder): string
    {
        $originalName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $cleanBase = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'proof';
        $filename = time() . '_' . Str::random(8) . '_' . $cleanBase . '.' . $ext;
        $relativeDir = trim($subFolder, '/');
        $relativePath = 'uploads/' . $relativeDir . '/' . $filename;

        $primaryRoot = ImagePathService::resolvePublicRoot();
        $primaryDir = $primaryRoot . '/uploads/' . $relativeDir;
        if (!is_dir($primaryDir)) {
            @mkdir($primaryDir, 0777, true);
        }

        $primaryFullPath = $primaryDir . '/' . $filename;
        $file->move($primaryDir, $filename);

        // Mirror to all other candidate public roots (cPanel and XAMPP compatibility)
        foreach (ImagePathService::getAllPublicRoots() as $root) {
            $mirrorDir = $root . '/uploads/' . $relativeDir;
            $mirrorFile = $mirrorDir . '/' . $filename;
            if ($mirrorFile !== $primaryFullPath) {
                if (!is_dir($mirrorDir)) {
                    @mkdir($mirrorDir, 0777, true);
                }
                @copy($primaryFullPath, $mirrorFile);
            }
        }

        try {
            $storageDir = storage_path('app/public/' . $relativeDir);
            if (!is_dir($storageDir)) {
                @mkdir($storageDir, 0777, true);
            }
            @copy($primaryFullPath, $storageDir . '/' . $filename);
        } catch (\Throwable $e) {
            // ignore safely
        }

        // Auto-register user submitted file in media_files table
        if (Schema::hasTable('media_files')) {
            try {
                $mimeType = @mime_content_type($primaryFullPath) ?: 'image/jpeg';
                $fileSize = @filesize($primaryFullPath) ?: 0;
                $imgInfo  = @getimagesize($primaryFullPath);
                $dims     = $imgInfo ? ($imgInfo[0] . ' × ' . $imgInfo[1]) : null;

                $mediaData = [
                    'file_name'  => $originalName,
                    'file_path'  => $relativePath,
                    'file_type'  => $mimeType,
                    'file_size'  => $fileSize,
                    'dimensions' => $dims,
                    'title'      => 'Payment Proof - ' . $originalName,
                    'alt_text'   => 'Payment Proof',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('media_files', 'media_category')) {
                    $mediaData['media_category'] = 'user_submitted';
                }
                if (Schema::hasColumn('media_files', 'source_module')) {
                    $mediaData['source_module'] = 'payment_proof';
                }
                if (Schema::hasColumn('media_files', 'file_hash')) {
                    $mediaData['file_hash'] = @hash_file('sha256', $primaryFullPath) ?: md5($relativePath);
                }
                if (Schema::hasColumn('media_files', 'uploaded_by')) {
                    $mediaData['uploaded_by'] = auth()->id() ?? null;
                }

                DB::table('media_files')->insert($mediaData);
            } catch (\Throwable $e) {
                // ignore safely
            }
        }

        return $relativePath;
    }

    public static function getAllDistricts()
    {
        $districtsList = [];
        $zones = ShippingZone::with('locations')->orderBy('id', 'desc')->get();

        foreach ($zones as $zone) {
            foreach ($zone->locations as $loc) {
                if (!empty($loc->districts)) {
                    $split = array_map('trim', explode(',', $loc->districts));
                    foreach ($split as $d) {
                        if (!empty($d) && !in_array($d, $districtsList)) {
                            $districtsList[] = $d;
                        }
                    }
                }
            }
        }

        if (empty($districtsList)) {
            $districtsList = ['Dhaka', 'Gazipur', 'Narayanganj', 'Chattogram', 'Sylhet', 'Rajshahi', 'Khulna', 'Barishal', 'Rangpur', 'Mymensingh', 'Other Districts'];
        }

        sort($districtsList);
        return $districtsList;
    }

    public function index()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty!');
        }

        $cartModified = false;
        $hasPhysical = false;
        $hasDigital = false;

        foreach ($cart as $id => &$item) {
            $productId = !empty($item['product_id']) ? (int)$item['product_id'] : (!empty($item['id']) && is_numeric($item['id']) ? (int)$item['id'] : (is_numeric($id) ? (int)$id : (int)explode('_', (string)$id)[0]));
            $prod = Product::find($productId);
            
            $isDownloadable = !empty($item['is_downloadable']) 
                || ($prod && (!empty($prod->is_downloadable) || !empty($prod->downloadable) || str_contains(strtolower($prod->product_type ?? ''), 'download')));

            $isItemDigital = ($item['is_digital'] ?? false) 
                || ($prod && ($prod->is_virtual || $prod->is_downloadable || in_array(strtolower($prod->product_type ?? ''), ['virtual', 'downloadable', 'license'])));
            
            if ($isDownloadable && ($item['quantity'] ?? 1) > 1) {
                $item['quantity'] = 1;
                $cartModified = true;
            }

            if ($isItemDigital) {
                $hasDigital = true;
            } else {
                $hasPhysical = true;
            }
        }
        unset($item);

        if ($cartModified) {
            session()->put('cart', $cart);
        }

        $isMixed = ($hasPhysical && $hasDigital);

        $districts = self::getAllDistricts();
        $defaultDistrict = in_array('Dhaka', $districts) ? 'Dhaka' : ($districts[0] ?? 'Dhaka');
        $initialShipping = $this->calculateDynamicShipping($defaultDistrict, $cart);

        $manualPayments = [];
        if (Schema::hasTable('manual_payment_methods')) {
            $manualPayments = DB::table('manual_payment_methods')->where('is_active', true)->get();
        }

        $gateways = [];
        if (Schema::hasTable('payment_gateways')) {
            $gateways = DB::table('payment_gateways')->where('is_active', true)->get();
        }

        return view('frontend.checkout', compact('cart', 'districts', 'initialShipping', 'manualPayments', 'gateways', 'hasPhysical', 'hasDigital', 'isMixed'));
    }

    public function calculateDynamicShipping($districtName, $cart)
    {
        $districtName = strtolower(trim($districtName));
        $totalWeight = 0.000;
        $customShippingTotal = 0.00;
        $hasCustomShipping = false;
        $hasPhysical = false;
        $subtotal = 0.00;

        foreach ($cart as $id => $item) {
            $rawPrice = $item['price'] ?? 0;
            if (is_string($rawPrice)) {
                $rawPrice = preg_replace('/[^0-9.]/', '', $rawPrice);
            }
            $price = (float) $rawPrice;
            $qty = (int) ($item['quantity'] ?? 1);

            $productId = !empty($item['product_id']) ? (int)$item['product_id'] : (!empty($item['id']) && is_numeric($item['id']) ? (int)$item['id'] : (is_numeric($id) ? (int)$id : (int)explode('_', (string)$id)[0]));
            $product = Product::find($productId);

            $isDownloadable = !empty($item['is_downloadable']) 
                || ($product && (!empty($product->is_downloadable) || !empty($product->downloadable) || str_contains(strtolower($product->product_type ?? ''), 'download')));
            if ($isDownloadable) {
                $qty = 1;
            }

            $subtotal += ($price * $qty);

            $isDigital = ($item['is_digital'] ?? false) || ($product && ($product->is_virtual || $product->is_downloadable || in_array(strtolower($product->product_type ?? ''), ['virtual', 'downloadable', 'license'])));

            if (!$isDigital) {
                $hasPhysical = true;
            }

            if ($product && !$isDigital) {
                if ($product->custom_delivery_charge !== null && $product->custom_delivery_charge > 0) {
                    $hasCustomShipping = true;
                    if ($product->multiply_qty_delivery_charge) {
                        $customShippingTotal += ((float)$product->custom_delivery_charge * $qty);
                    } else {
                        $customShippingTotal += (float)$product->custom_delivery_charge;
                    }
                }

                $itemWeight = (float) ($product->weight ?? 0.000);
                $totalWeight += ($itemWeight * $qty);
            }
        }

        if (!$hasPhysical) {
            return [
                'cost'         => 0.00,
                'zone_name'    => 'Digital Delivery',
                'is_digital'   => true,
                'total_weight' => 0,
                'is_free'      => true
            ];
        }

        $allZones = ShippingZone::with(['locations', 'methods'])->orderBy('id', 'desc')->get();
        $matchingZones = [];

        foreach ($allZones as $zone) {
            foreach ($zone->locations as $loc) {
                if (!empty($loc->districts)) {
                    $districtsArray = array_map('trim', explode(',', strtolower($loc->districts)));
                    if (in_array($districtName, $districtsArray)) {
                        $matchingZones[] = $zone;
                        break;
                    }
                }
            }
        }

        if (empty($matchingZones)) {
            $fallback = ShippingZone::with(['locations', 'methods'])->where('name', 'LIKE', '%Outside%')->first() ?? $allZones->first();
            if ($fallback) {
                $matchingZones[] = $fallback;
            }
        }

        $isFreeShipping = false;
        $selectedZone = !empty($matchingZones) ? $matchingZones[0] : null;

        foreach ($matchingZones as $zone) {
            foreach ($zone->methods as $method) {
                if ($method->method_type === 'free_shipping' && ($method->is_enabled == 1 || $method->is_enabled === true)) {
                    if ($method->free_shipping_requires === 'no_requirement') {
                        $isFreeShipping = true;
                        $selectedZone = $zone;
                        break 2;
                    } elseif ($method->free_shipping_requires === 'min_amount') {
                        $minAmount = (float) $method->min_amount;
                        if ($subtotal >= $minAmount) {
                            $isFreeShipping = true;
                            $selectedZone = $zone;
                            break 2;
                        }
                    }
                }
            }
        }

        if ($isFreeShipping) {
            return [
                'cost'         => 0.00,
                'zone_name'    => ($selectedZone ? $selectedZone->name : 'Standard') . ' (Free Shipping)',
                'is_digital'   => false,
                'total_weight' => $totalWeight,
                'is_free'      => true
            ];
        }

        $calculatedCost = 0.00;

        if ($selectedZone) {
            $rate = ShippingClassRate::where('shipping_zone_id', $selectedZone->id)
                ->where('weight_from', '<=', $totalWeight)
                ->where('weight_to', '>=', $totalWeight)
                ->first();

            if ($rate) {
                $calculatedCost = (float) $rate->cost;
            } else {
                $maxSlab = ShippingClassRate::where('shipping_zone_id', $selectedZone->id)
                    ->orderBy('weight_to', 'desc')
                    ->first();

                if ($maxSlab && $totalWeight > (float)$maxSlab->weight_to) {
                    $extraWeight = ceil($totalWeight - (float)$maxSlab->weight_to);
                    $perKgExtra = (float)($maxSlab->per_kg_extra > 0 ? $maxSlab->per_kg_extra : 20.00);
                    $calculatedCost = (float)$maxSlab->cost + ($extraWeight * $perKgExtra);
                } else {
                    $flatMethod = ShippingZoneMethod::where('shipping_zone_id', $selectedZone->id)
                        ->where('method_type', 'flat_rate')
                        ->where('is_enabled', 1)
                        ->first();
                    $calculatedCost = $flatMethod ? (float)$flatMethod->cost : (stripos($selectedZone->name, 'Inside') !== false ? 60.00 : 120.00);
                }
            }
        }

        if ($hasCustomShipping) {
            $calculatedCost += $customShippingTotal;
        }

        return [
            'cost'         => $calculatedCost,
            'zone_name'    => $selectedZone ? $selectedZone->name : 'Standard Delivery',
            'is_digital'   => false,
            'total_weight' => $totalWeight,
            'is_free'      => false
        ];
    }

    public function getDeliveryCharge(Request $request)
    {
        $cart = session()->get('cart', []);
        if ($request->has('cart') && is_array($request->input('cart'))) {
            $cart = $request->input('cart');
        }

        $district = $request->input('district', 'Dhaka');

        $subtotal = 0.00;
        foreach ($cart as $id => $item) {
            $rawPrice = $item['price'] ?? 0;
            if (is_string($rawPrice)) {
                $rawPrice = preg_replace('/[^0-9.]/', '', $rawPrice);
            }
            $qty = (int)($item['quantity'] ?? 1);
            $pId = !empty($item['product_id']) ? (int)$item['product_id'] : (!empty($item['id']) && is_numeric($item['id']) ? (int)$item['id'] : (is_numeric($id) ? (int)$id : (int)explode('_', (string)$id)[0]));
            $p = Product::find($pId);
            if (!empty($item['is_downloadable']) || ($p && (!empty($p->is_downloadable) || str_contains(strtolower($p->product_type ?? ''), 'download')))) {
                $qty = 1;
            }
            $subtotal += ((float)$rawPrice * $qty);
        }

        $shippingData = $this->calculateDynamicShipping($district, $cart);
        $deliveryCharge = (float) $shippingData['cost'];
        $grandTotal = $subtotal + $deliveryCharge;

        return response()->json([
            'status'          => 'success',
            'zone'            => $shippingData['zone_name'],
            'total_weight'    => $shippingData['total_weight'],
            'is_free'         => $shippingData['is_free'],
            'is_digital'      => $shippingData['is_digital'],
            'subtotal'        => number_format($subtotal, 2, '.', ''),
            'item_total'      => number_format($subtotal, 2, '.', ''),
            'total'           => number_format($grandTotal, 2, '.', ''),
            'delivery_charge' => number_format($deliveryCharge, 2, '.', ''),
            'grand_total'     => number_format($grandTotal, 2, '.', '')
        ]);
    }

    public function processOrder(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return response()->json(['status' => 'error', 'message' => 'Your cart is empty!'], 400);
        }

        $hasPhysical = false;
        $hasDigital = false;
        foreach ($cart as $id => $item) {
            $productId = !empty($item['product_id']) ? (int)$item['product_id'] : (!empty($item['id']) && is_numeric($item['id']) ? (int)$item['id'] : (is_numeric($id) ? (int)$id : (int)explode('_', (string)$id)[0]));
            $prod = Product::find($productId);
            $isItemDigital = ($item['is_digital'] ?? false) || ($prod && ($prod->is_virtual || $prod->is_downloadable || in_array(strtolower($prod->product_type ?? ''), ['virtual', 'downloadable', 'license'])));
            
            if ($isItemDigital) {
                $hasDigital = true;
            } else {
                $hasPhysical = true;
            }
        }

        if ($hasDigital && strtolower(trim($request->payment_method)) === 'cash on delivery') {
            return response()->json([
                'status'  => 'error',
                'message' => 'আপনার কার্টে ডিজিটাল পণ্য রয়েছে। ডিজিটাল পণ্যের অ্যাক্সেস পেতে সম্পূর্ণ অর্ডারটির পেমেন্ট অনলাইন বা ম্যানুয়াল মাধ্যমে পরিশোধ করতে হবে।'
            ], 422);
        }

        $rules = [
            'name'           => 'required|string|max:255',
            'phone'          => 'required|string|max:20',
            'payment_method' => 'required|string|max:100',
            'email'          => $hasDigital ? 'required|email|max:255' : 'nullable|email|max:255',
        ];

        if ($hasPhysical) {
            $rules['address'] = 'required|string|max:500';
            $rules['district'] = 'required|string|max:100';
        } else {
            $request->merge([
                'district' => $request->filled('district') ? $request->district : 'Digital',
                'address'  => $request->filled('address') ? $request->address : 'Digital Delivery',
            ]);
        }

        if ($request->input('payment_type') === 'manual') {
            $rules['sender_number'] = 'nullable|string|max:20';
            $rules['transaction_id'] = 'nullable|string|max:100';
            $rules['payment_screenshot_file'] = 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096';

            if (!$request->filled('sender_number') && !$request->filled('transaction_id') && !$request->hasFile('payment_screenshot_file')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sender Number, Transaction ID অথবা Payment Screenshot-এর যেকোনো একটি পূরণ করুন।'
                ], 422);
            }
        }

        $request->validate($rules);

        // Strict duplicate transaction protection
        if ($request->filled('transaction_id')) {
            $dupCheck = PaymentTransactionService::checkDuplicateTransactionId(
                $request->input('transaction_id'),
                $request->input('payment_method')
            );

            if ($dupCheck['is_duplicate']) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $dupCheck['message']
                ], 422);
            }
        }

        // =========================================================================
        // 1. PRE-CHECK STOCK AVAILABILITY (Compound Cart Keys Supported)
        // =========================================================================
        foreach ($cart as $id => $item) {
            $productId = !empty($item['product_id']) ? (int)$item['product_id'] : (!empty($item['id']) && is_numeric($item['id']) ? (int)$item['id'] : (is_numeric($id) ? (int)$id : (int)explode('_', (string)$id)[0]));
            $prod = Product::find($productId);
            $isItemDigital = ($item['is_digital'] ?? false) || ($prod && ($prod->is_virtual || $prod->is_downloadable || in_array(strtolower($prod->product_type ?? ''), ['virtual', 'downloadable', 'license'])));
            $qty = (int)($item['quantity'] ?? 1);
            if (!empty($item['is_downloadable']) || ($prod && (!empty($prod->is_downloadable) || str_contains(strtolower($prod->product_type ?? ''), 'download')))) {
                $qty = 1;
            }
            $variantId = !empty($item['variant_id']) ? (int)$item['variant_id'] : null;

            if (!$isItemDigital && $productId) {
                $availability = InventoryService::checkStockAvailability((int)$productId, $variantId, $qty);
                if (!$availability['available']) {
                    $itemLabel = !empty($availability['title']) ? $availability['title'] : ($prod->title ?? 'পণ্য');
                    return response()->json([
                        'status'  => 'error',
                        'message' => "দুঃখিত, '{$itemLabel}'-এর পর্যাপ্ত স্টক নেই। বর্তমান অবশিষ্ট স্টক: {$availability['stock']} টি।"
                    ], 422);
                }
            }
        }

        $subtotal = 0.00;
        foreach ($cart as $id => $item) {
            $rawPrice = $item['price'] ?? 0;
            if (is_string($rawPrice)) {
                $rawPrice = preg_replace('/[^0-9.]/', '', $rawPrice);
            }
            $qty = (int)($item['quantity'] ?? 1);
            $pId = !empty($item['product_id']) ? (int)$item['product_id'] : (!empty($item['id']) && is_numeric($item['id']) ? (int)$item['id'] : (is_numeric($id) ? (int)$id : (int)explode('_', (string)$id)[0]));
            $p = Product::find($pId);
            if (!empty($item['is_downloadable']) || ($p && (!empty($p->is_downloadable) || str_contains(strtolower($p->product_type ?? ''), 'download')))) {
                $qty = 1;
            }
            $subtotal += ((float)$rawPrice * $qty);
        }

        $district = $request->input('district', 'Dhaka');
        $shippingInfo = $this->calculateDynamicShipping($district, $cart);
        $deliveryCharge = (float) $shippingInfo['cost'];

        $grandTotal = $subtotal + $deliveryCharge;
        $invoiceNo = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        $userId = auth()->check() ? auth()->id() : null;

        $screenshotPath = null;
        if ($request->hasFile('payment_screenshot_file')) {
            $screenshotPath = $this->saveCheckoutFile($request->file('payment_screenshot_file'), 'payment_proofs');
        }

        $noteDetails = "Customer: {$request->name}, Phone: {$request->phone}, Address: {$request->address}, District: {$district}";
        if ($request->filled('order_note')) {
            $noteDetails .= " | Special Note: " . trim($request->order_note);
        }

        $isImmediatelyPaid = ($grandTotal <= 0);
        $isCOD = in_array(strtolower(trim($request->payment_method)), ['cash on delivery', 'cod']);
        $expectedCOD = $isCOD ? $grandTotal : ($isImmediatelyPaid ? 0.00 : $grandTotal);

        DB::beginTransaction();
        try {
            if ($request->filled('transaction_id')) {
                $raceCheck = PaymentTransactionService::checkDuplicateTransactionId(
                    $request->input('transaction_id'),
                    $request->input('payment_method')
                );
                if ($raceCheck['is_duplicate']) {
                    DB::rollBack();
                    return response()->json([
                        'status'  => 'error',
                        'message' => $raceCheck['message']
                    ], 422);
                }
            }

            $orderData = [
                'invoice_no'         => $invoiceNo,
                'user_id'            => $userId,
                'subtotal'           => $subtotal,
                'grand_total'        => $grandTotal,
                'payment_method'     => $request->payment_method,
                'payment_status'     => $isImmediatelyPaid ? 'paid' : 'unpaid',
                'order_status'       => $isImmediatelyPaid ? 'completed' : 'pending',
                'transaction_id'     => $request->transaction_id ? trim($request->transaction_id) : null,
                'sender_number'      => $request->sender_number ? trim($request->sender_number) : null,
                'payment_screenshot' => $screenshotPath,
                'created_at'         => now(),
                'updated_at'         => now(),
            ];

            $conditionalFields = [
                'order_type'          => 'online',
                'product_subtotal'    => $subtotal,
                'delivery_charge'     => $deliveryCharge,
                'shipping_charge'     => $deliveryCharge,
                'expected_cod_amount' => $expectedCOD,
                'actual_cod_received' => 0.00,
                'is_stock_deducted'   => false,
                'is_stock_restored'   => false,
                'is_cod_settled'      => false,
                'paid_amount'         => 0.00,
                'due_amount'          => $isImmediatelyPaid ? 0.00 : $grandTotal,
                'customer_name'       => $request->name,
                'name'                => $request->name,
                'customer_phone'      => $request->phone,
                'phone'               => $request->phone,
                'customer_email'      => $request->email,
                'email'               => $request->email,
                'customer_address'    => $request->address,
                'shipping_address'    => $request->address,
                'address'             => $request->address,
                'district'            => $district,
                'shipping_district'   => $district,
                'notes'               => $noteDetails,
                'order_note'          => $request->order_note,
            ];

            foreach ($conditionalFields as $col => $val) {
                if (Schema::hasColumn('orders', $col)) {
                    $orderData[$col] = $val;
                }
            }

            $orderId = DB::table('orders')->insertGetId($orderData);

            // =========================================================================
            // 2. ORDER ITEMS INSERTION & ESTIMATED STOCK RESERVATION
            // =========================================================================
            foreach ($cart as $id => $item) {
                $rawPrice = $item['price'] ?? 0;
                if (is_string($rawPrice)) {
                    $rawPrice = preg_replace('/[^0-9.]/', '', $rawPrice);
                }
                $unitPrice = (float) $rawPrice;
                $qty = (int) ($item['quantity'] ?? 1);

                $productId = !empty($item['product_id']) ? (int)$item['product_id'] : (!empty($item['id']) && is_numeric($item['id']) ? (int)$item['id'] : (is_numeric($id) ? (int)$id : (int)explode('_', (string)$id)[0]));
                $pModel = Product::find($productId);

                $isDownloadable = !empty($item['is_downloadable']) 
                    || ($pModel && (!empty($pModel->is_downloadable) || !empty($pModel->downloadable) || str_contains(strtolower($pModel->product_type ?? ''), 'download')));
                
                if ($isDownloadable) {
                    $qty = 1;
                }

                $variantId = !empty($item['variant_id']) ? (int)$item['variant_id'] : null;
                $variantTitle = $item['variant_name'] ?? null;
                $color = $item['color'] ?? null;
                $size  = $item['size'] ?? null;
                $sku   = $item['sku'] ?? null;

                $pTitle = $item['title'] ?? null;
                if ((empty($pTitle) || $pTitle === 'Product') && !empty($productId)) {
                    $pTitle = DB::table('products')->where('id', $productId)->value('title');
                }

                if (!empty($variantId) && (empty($color) || empty($size) || empty($sku))) {
                    $vRecord = DB::table('product_variants')->where('id', $variantId)->first();
                    if ($vRecord) {
                        $color = $vRecord->color ?? $color;
                        $size  = $vRecord->size ?? $size;
                        $sku   = $vRecord->product_code ?? ($vRecord->sku ?? $sku);
                        $parts = array_filter([$color, $size]);
                        $variantTitle = count($parts) > 0 ? implode(' / ', $parts) : ($vRecord->name ?? $variantTitle);
                    }
                }

                $isItemDigital = ($item['is_digital'] ?? false) || ($pModel && ($pModel->is_virtual || $pModel->is_downloadable || in_array(strtolower($pModel->product_type ?? ''), ['virtual', 'downloadable', 'license'])));

                $purchaseCost = 0.00;
                $cogs = 0.00;
                $isCostEstimated = false;

                if (!$isItemDigital && $productId) {
                    InventoryService::recordOrderEstimate((int)$productId, $variantId, $qty, $invoiceNo);

                    if (!empty($variantId)) {
                        $vRecord = DB::table('product_variants')->where('id', $variantId)->first();
                        $rawVariantCost = $vRecord->purchase_cost ?? ($vRecord->purchase_price ?? null);
                        if (!is_null($rawVariantCost) && (float)$rawVariantCost > 0) {
                            $purchaseCost = (float)$rawVariantCost;
                        } else {
                            $parentCost = $pModel ? ($pModel->purchase_cost ?? ($pModel->purchase_price ?? 0.00)) : 0.00;
                            $purchaseCost = (float)$parentCost;
                            $isCostEstimated = true;
                        }
                        if (empty($sku) && $vRecord) {
                            $sku = $vRecord->product_code ?? ($vRecord->sku ?? ('VAR-' . $variantId));
                        }
                    } else {
                        $parentCost = $pModel ? ($pModel->purchase_cost ?? ($pModel->purchase_price ?? 0.00)) : 0.00;
                        $purchaseCost = (float)$parentCost;
                        $isCostEstimated = ($purchaseCost <= 0);
                        if (empty($sku) && $pModel) {
                            $sku = $pModel->sku ?? ($pModel->product_code ?? ('PRD-' . $productId));
                        }
                    }

                    $cogs = round($purchaseCost * $qty, 2);
                } else {
                    $costVal = $pModel ? ($pModel->purchase_cost ?? ($pModel->purchase_price ?? 0.00)) : 0.00;
                    $purchaseCost = (float)$costVal;
                    $cogs = round($purchaseCost * $qty, 2);
                    $isCostEstimated = ($purchaseCost <= 0);
                }

                $itemData = [
                    'order_id'   => $orderId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $condItem = [
                    'product_id'        => $productId,
                    'product_title'     => $pTitle ?? 'Product',
                    'product_name'      => $pTitle ?? 'Product',
                    'name'              => $pTitle ?? 'Product',
                    'title'             => $pTitle ?? 'Product',
                    'variant_id'        => $variantId,
                    'variant_title'     => $variantTitle,
                    'color'             => $color,
                    'size'              => $size,
                    'sku'               => $sku,
                    'quantity'          => $qty,
                    'qty'               => $qty,
                    'unit_price'        => $unitPrice,
                    'price'             => $unitPrice,
                    'product_price'     => $unitPrice,
                    'subtotal'          => $unitPrice * $qty,
                    'total'             => $unitPrice * $qty,
                    'purchase_cost'     => $purchaseCost,
                    'cogs'              => $cogs,
                    'is_cost_estimated' => $isCostEstimated,
                ];

                foreach ($condItem as $col => $val) {
                    if (Schema::hasColumn('order_items', $col)) {
                        $itemData[$col] = $val;
                    }
                }

                DB::table('order_items')->insert($itemData);
            }

            // =========================================================================
            // 3. RECORD TRANSACTION & FULFILLMENT
            // =========================================================================
            $savedOrder = DB::table('orders')->where('id', $orderId)->first();
            if ($savedOrder) {
                PaymentTransactionService::recordCheckoutTransaction($savedOrder, [
                    'payment_type'     => $request->input('payment_type'),
                    'payment_method'   => $request->input('payment_method'),
                    'sender_number'    => $request->input('sender_number'),
                    'transaction_id'   => $request->input('transaction_id'),
                    'screenshot_path'  => $screenshotPath,
                    'submitted_amount' => $grandTotal,
                ]);
            }

            // =========================================================================
            // 4. ABANDONED CART RECOVERY RECONCILIATION (ISSUE 4 FIX)
            // =========================================================================
            if (Schema::hasTable('email_abandoned_carts')) {
                try {
                    $sessionRecoveryToken = session()->get('active_abandoned_cart_token');
                    $custEmail = !empty($request->email) ? strtolower(trim($request->email)) : null;
                    $custPhone = !empty($request->phone) ? trim($request->phone) : null;
                    $custCleanPhone = preg_replace('/[^0-9]/', '', $custPhone ?? '');

                    EmailAbandonedCart::where('is_recovered', false)
                        ->where(function ($q) use ($sessionRecoveryToken, $custEmail, $custPhone, $custCleanPhone, $userId) {
                            $hasCondition = false;
                            if ($sessionRecoveryToken) {
                                $q->orWhere('recovery_token', $sessionRecoveryToken);
                                $hasCondition = true;
                            }
                            if ($custEmail && filter_var($custEmail, FILTER_VALIDATE_EMAIL)) {
                                $q->orWhere('email', $custEmail);
                                $hasCondition = true;
                            }
                            if (!empty($custPhone)) {
                                $q->orWhere('phone', $custPhone);
                                $q->orWhere('email', $custPhone . '@guest.shopnowbd.online');
                                $hasCondition = true;
                            }
                            if (!empty($custCleanPhone) && $custCleanPhone !== $custPhone) {
                                $q->orWhere('phone', $custCleanPhone);
                                $q->orWhere('email', $custCleanPhone . '@guest.shopnowbd.online');
                                $hasCondition = true;
                            }
                            if ($userId) {
                                $q->orWhere('user_id', $userId);
                                $hasCondition = true;
                            }
                            if (!$hasCondition) {
                                $q->whereRaw('0 = 1');
                            }
                        })
                        ->update([
                            'is_recovered'       => true,
                            'recovered_at'       => now(),
                            'recovered_order_id' => $orderId,
                        ]);
                } catch (\Throwable $cartReconcileEx) {
                    \Log::warning('Abandoned cart reconciliation error: ' . $cartReconcileEx->getMessage());
                }
            }

            if ($isImmediatelyPaid) {
                DigitalProductService::fulfillDigitalOrder($orderId);
            }

            DB::commit();

            // সেশনগুলো সম্পূর্ণ পরিষ্কার করা
            session()->forget('cart');
            session()->forget('active_abandoned_cart_token');

            $savedOrderItems = DB::table('order_items')->where('order_id', $orderId)->get();
            $digitalDetails = DigitalProductService::getOrderDigitalDetails($orderId);

            $recipientEmail = $request->email ?? (auth()->check() ? auth()->user()->email : null);
            if ($recipientEmail && filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::to($recipientEmail)->send(new OrderPlacedMail($savedOrder, $savedOrderItems, $digitalDetails));
                } catch (\Throwable $mailEx) {
                    \Log::error('Customer order email failed: ' . $mailEx->getMessage());
                }
            }

            try {
                Mail::to('info.srabonit@gmail.com')->send(new AdminOrderNotificationMail($savedOrder, $savedOrderItems));
            } catch (\Throwable $adminMailEx) {
                \Log::error('Admin order alert email failed: ' . $adminMailEx->getMessage());
            }

            $methodName = strtolower($request->payment_method ?? '');
            $isGateway = ($request->input('payment_type') === 'gateway') || str_contains($methodName, 'pgw') || str_contains($methodName, 'api') || str_contains($methodName, 'ssl') || str_contains($methodName, 'direct');

            if ($isGateway) {
                if (str_contains($methodName, 'ssl')) {
                    return response()->json(['status' => 'success', 'redirect' => route('payment.sslcommerz.pay', $invoiceNo)]);
                } elseif (str_contains($methodName, 'bkash')) {
                    return response()->json(['status' => 'success', 'redirect' => route('payment.bkash.pay', $invoiceNo)]);
                } elseif (str_contains($methodName, 'nagad')) {
                    return response()->json(['status' => 'success', 'redirect' => route('payment.nagad.pay', $invoiceNo)]);
                } elseif (str_contains($methodName, 'upay')) {
                    return response()->json(['status' => 'success', 'redirect' => route('payment.upay.pay', $invoiceNo)]);
                } elseif (str_contains($methodName, 'eps')) {
                    return response()->json(['status' => 'success', 'redirect' => route('payment.eps.pay', $invoiceNo)]);
                } elseif (str_contains($methodName, 'shurjo')) {
                    return response()->json(['status' => 'success', 'redirect' => route('payment.shurjopay.pay', $invoiceNo)]);
                } elseif (str_contains($methodName, 'stripe')) {
                    return response()->json(['status' => 'success', 'redirect' => route('payment.stripe.pay', $invoiceNo)]);
                } elseif (str_contains($methodName, 'paypal')) {
                    return response()->json(['status' => 'success', 'redirect' => route('payment.paypal.pay', $invoiceNo)]);
                }
            }

            return response()->json([
                'status'     => 'success',
                'invoice_no' => $invoiceNo,
                'redirect'   => route('order.confirmation', $invoiceNo)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Checkout process order failed: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function confirmation($invoiceNo)
    {
        $order = DB::table('orders')->where('invoice_no', $invoiceNo)->first();
        if (!$order) return redirect()->route('home')->with('error', 'Invoice not found!');

        // কনফার্মেশন পেজে সেশন নিশ্চিতভাবে রিমুভ করা
        session()->forget('cart');
        session()->forget('active_abandoned_cart_token');

        // সেফটি নেট: পেন্ডিং কার্ট থাকলে কনফার্মেশন থেকেও রিকভার্ড মার্ক করা
        if (Schema::hasTable('email_abandoned_carts')) {
            try {
                $phone = $order->customer_phone ?? ($order->phone ?? null);
                $cleanPhone = preg_replace('/[^0-9]/', '', $phone ?? '');
                $email = $order->customer_email ?? ($order->email ?? null);

                EmailAbandonedCart::where('is_recovered', false)
                    ->where(function ($q) use ($phone, $cleanPhone, $email) {
                        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $q->orWhere('email', $email);
                        }
                        if ($phone) {
                            $q->orWhere('phone', $phone);
                            $q->orWhere('email', $phone . '@guest.shopnowbd.online');
                        }
                        if ($cleanPhone && $cleanPhone !== $phone) {
                            $q->orWhere('phone', $cleanPhone);
                        }
                    })
                    ->update([
                        'is_recovered'       => true,
                        'recovered_at'       => now(),
                        'recovered_order_id' => $order->id,
                    ]);
            } catch (\Throwable $e) {}
        }

        if (in_array(strtolower($order->payment_status ?? ''), ['paid', 'completed'])) {
            DigitalProductService::fulfillDigitalOrder($order->id);
        }

        $digitalDetails = DigitalProductService::getOrderDigitalDetails($order->id);

        $itemsQuery = DB::table('order_items')->where('order_id', $order->id);
        if (Schema::hasTable('products') && Schema::hasColumn('order_items', 'product_id')) {
            $itemsQuery->leftJoin('products', 'order_items.product_id', '=', 'products.id')
                ->select('order_items.*', 'products.title as product_table_title');
        }
        $orderItems = $itemsQuery->get();

        $viewName = view()->exists('frontend.order-confirmation') ? 'frontend.order-confirmation' : 'frontend.confirmation';
        return view($viewName, compact('order', 'orderItems', 'digitalDetails'));
    }
}