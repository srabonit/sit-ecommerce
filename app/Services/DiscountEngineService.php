<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DiscountEngineService
{
    /**
     * ১. কুপন কোড যাচাই ও ডিসকাউন্ট হিসাব (Target User, Multi-Product & Category Supported)
     */
    public static function validateAndCalculateCoupon(
        string $code,
        float $subtotal,
        array $cartItems = [],
        ?int $userId = null,
        float $deliveryCharge = 0.0
    ): array {
        $cleanCode = strtoupper(trim($code));

        if (empty($cleanCode)) {
            return [
                'valid'   => false,
                'message' => 'Please enter a valid coupon code.',
                'discount' => 0.0,
            ];
        }

        if (!Schema::hasTable('coupons')) {
            return [
                'valid'   => false,
                'message' => 'Coupon system is not available right now.',
                'discount' => 0.0,
            ];
        }

        $coupon = DB::table('coupons')->where('code', $cleanCode)->first();

        if (!$coupon) {
            return [
                'valid'   => false,
                'message' => 'Invalid coupon code. Please check and try again.',
                'discount' => 0.0,
            ];
        }

        // ক. অ্যাক্টিভেশন ও তারিখ যাচাই
        $isActive = isset($coupon->is_active) ? (bool)$coupon->is_active : (isset($coupon->status) ? (bool)$coupon->status : true);
        if (!$isActive) {
            return [
                'valid'   => false,
                'message' => 'This coupon has been disabled.',
                'discount' => 0.0,
            ];
        }

        $now = Carbon::now();
        if (!empty($coupon->start_date) && Carbon::parse($coupon->start_date)->isFuture()) {
            return [
                'valid'   => false,
                'message' => 'This coupon campaign has not started yet.',
                'discount' => 0.0,
            ];
        }

        $expireDate = $coupon->expire_date ?? ($coupon->expires_at ?? null);
        if (!empty($expireDate) && Carbon::parse($expireDate)->isPast()) {
            return [
                'valid'   => false,
                'message' => 'This coupon has expired.',
                'discount' => 0.0,
            ];
        }

        // খ. টার্গেট কাস্টমার গ্রুপ ভ্যালিডেশন (All vs Registered Only vs Guest Only)
        if (!empty($coupon->target_user_type)) {
            if ($coupon->target_user_type === 'registered_only' && !$userId) {
                return [
                    'valid'   => false,
                    'message' => 'এই কুপনটি শুধুমাত্র রেজিস্টার্ড ও লগইন করা গ্রাহকদের জন্য প্রযোজ্য। অনুগ্রহ করে প্রথমে লগইন করুন।',
                    'discount' => 0.0,
                ];
            }
            if ($coupon->target_user_type === 'guest_only' && $userId) {
                return [
                    'valid'   => false,
                    'message' => 'এই কুপনটি শুধুমাত্র নতুন গেস্ট গ্রাহকদের জন্য প্রযোজ্য।',
                    'discount' => 0.0,
                ];
            }
        }

        // গ. টোটাল ইউসেজ লিমিট যাচাই
        $totalLimit = $coupon->total_usage_limit ?? ($coupon->usage_limit ?? null);
        $usedCount = $coupon->used_count ?? ($coupon->current_usage_count ?? 0);
        if (!is_null($totalLimit) && (int)$totalLimit > 0 && (int)$usedCount >= (int)$totalLimit) {
            return [
                'valid'   => false,
                'message' => 'This coupon usage limit has already been reached.',
                'discount' => 0.0,
            ];
        }

        // ঘ. পার-ইউজার লিমিট যাচাই (লগইন করা ব্যবহারকারীর ক্ষেত্রে)
        $perUserLimit = (int)($coupon->per_user_limit ?? ($coupon->usage_limit_per_user ?? 1));
        if ($userId && $perUserLimit > 0 && Schema::hasTable('coupon_voucher_redemptions')) {
            $rCols = Schema::getColumnListing('coupon_voucher_redemptions');
            $cCol = collect(['code_used', 'coupon_code', 'code_applied', 'code'])->first(fn($c) => in_array($c, $rCols));
            $typeCol = collect(['discount_type', 'type'])->first(fn($c) => in_array($c, $rCols));

            $userQuery = DB::table('coupon_voucher_redemptions')
                ->where('user_id', $userId);

            if (in_array('status', $rCols)) {
                $userQuery->whereNotIn('status', ['restored', 'void', 'voided', 'cancelled', 'reversed', 'failed']);
            }
            if ($typeCol) {
                $userQuery->where(function ($q) use ($typeCol) {
                    $q->whereNull($typeCol)->orWhere($typeCol, '!=', 'voucher');
                });
            }
            if ($cCol) {
                $userQuery->where(function ($q) use ($cCol, $cleanCode, $coupon, $rCols) {
                    $q->where($cCol, $cleanCode);
                    if (in_array('coupon_id', $rCols)) {
                        $q->orWhere('coupon_id', $coupon->id);
                    }
                });
            }

            if ($userQuery->count() >= $perUserLimit) {
                return [
                    'valid'   => false,
                    'message' => 'You have already reached the maximum usage limit for this coupon.',
                    'discount' => 0.0,
                ];
            }
        }

        // ঙ. এক্সক্লুসিভ ইউজার রেস্ট্রিকশন পিভট চেক
        if (Schema::hasTable('coupon_users')) {
            $hasRestrictedUsers = DB::table('coupon_users')->where('coupon_id', $coupon->id)->exists();
            if ($hasRestrictedUsers) {
                if (!$userId) {
                    return [
                        'valid'   => false,
                        'message' => 'Please login to use this exclusive coupon.',
                        'discount' => 0.0,
                    ];
                }

                $isEligibleUser = DB::table('coupon_users')
                    ->where('coupon_id', $coupon->id)
                    ->where('user_id', $userId)
                    ->exists();

                if (!$isEligibleUser) {
                    return [
                        'valid'   => false,
                        'message' => 'This coupon is not valid for your account.',
                        'discount' => 0.0,
                    ];
                }
            }
        }

        // চ. অর্ডার অ্যামাউন্ট লিমিট চেক
        $minOrder = (float)($coupon->min_order_amount ?? ($coupon->min_spend ?? 0));
        if ($minOrder > 0 && $subtotal < $minOrder) {
            return [
                'valid'   => false,
                'message' => 'Minimum order amount of ৳' . number_format($minOrder, 2) . ' is required to use this coupon.',
                'discount' => 0.0,
            ];
        }

        $maxOrder = (float)($coupon->max_order_amount ?? 0);
        if ($maxOrder > 0 && $subtotal > $maxOrder) {
            return [
                'valid'   => false,
                'message' => 'Maximum order limit for this coupon is ৳' . number_format($maxOrder, 2) . '.',
                'discount' => 0.0,
            ];
        }

        // ছ. এলিজিবল প্রোডাক্ট / ক্যাটাগরি অ্যামাউন্ট নির্ধারণ
        $eligibleAmount = self::calculateEligibleSubtotal($coupon, $cartItems, $subtotal);

        if ($eligibleAmount <= 0) {
            return [
                'valid'   => false,
                'message' => 'None of the items in your cart are eligible for this coupon.',
                'discount' => 0.0,
            ];
        }

        // জ. ডিসকাউন্ট হিসাব
        $discount = 0.0;
        $discountType = strtolower($coupon->discount_type ?? ($coupon->type ?? 'fixed'));
        $rawDiscountAmount = (float)($coupon->discount_amount ?? ($coupon->value ?? 0));

        switch ($discountType) {
            case 'percentage':
                $discount = ($eligibleAmount * $rawDiscountAmount) / 100;
                $maxCap = (float)($coupon->max_discount_amount ?? ($coupon->max_discount ?? 0));
                if ($maxCap > 0) {
                    $discount = min($discount, $maxCap);
                }
                break;

            case 'free_shipping':
                $discount = ($rawDiscountAmount > 0)
                    ? min($rawDiscountAmount, $deliveryCharge)
                    : $deliveryCharge;
                break;

            case 'fixed':
            default:
                $discount = min($rawDiscountAmount, $eligibleAmount);
                break;
        }

        $discount = max(0.0, round($discount, 2));

        return [
            'valid'               => true,
            'message'             => 'Coupon applied successfully!',
            'coupon'              => $coupon,
            'coupon_id'           => $coupon->id,
            'code'                => $cleanCode,
            'discount_type'       => $discountType,
            'discount'            => $discount,
            'is_stackable'        => (bool)($coupon->is_stackable ?? false),
            'eligible_subtotal'   => $eligibleAmount,
        ];
    }

    /**
     * ২. কাস্টমার ওয়ালেট ভাউচার যাচাই ও ডিসকাউন্ট হিসাব
     */
    public static function validateAndCalculateVoucher(
        string $voucherCode,
        float $subtotal,
        array $cartItems = [],
        ?int $userId = null,
        float $deliveryCharge = 0.0
    ): array {
        $cleanCode = strtoupper(trim($voucherCode));

        if (empty($cleanCode)) {
            return [
                'valid'   => false,
                'message' => 'Please enter or select a valid voucher code.',
                'discount' => 0.0,
            ];
        }

        if (!Schema::hasTable('customer_vouchers') || !Schema::hasTable('vouchers')) {
            return [
                'valid'   => false,
                'message' => 'Voucher wallet system is not available right now.',
                'discount' => 0.0,
            ];
        }

        // কাস্টমার ওয়ালেটে ভাউচারটি আছে কিনা যাচাই
        $voucherQuery = DB::table('customer_vouchers')
            ->join('vouchers', 'customer_vouchers.voucher_id', '=', 'vouchers.id')
            ->where('customer_vouchers.voucher_code', $cleanCode);

        if ($userId) {
            $voucherQuery->where('customer_vouchers.user_id', $userId);
        }

        $vSelect = [
            'customer_vouchers.id as customer_voucher_id',
            'customer_vouchers.user_id',
            'customer_vouchers.voucher_code',
            'customer_vouchers.status as voucher_status',
            'customer_vouchers.expires_at',
            'customer_vouchers.order_id',
            'vouchers.id as voucher_id',
            'vouchers.title',
            'vouchers.voucher_type',
            'vouchers.discount_amount',
            'vouchers.max_discount',
            'vouchers.min_spend',
            'vouchers.is_active',
        ];

        if (Schema::hasColumn('vouchers', 'target_user_type')) {
            $vSelect[] = 'vouchers.target_user_type';
        }

        $userVoucher = $voucherQuery->select($vSelect)->first();

        if (!$userVoucher) {
            return [
                'valid'   => false,
                'message' => 'Voucher not found in your wallet.',
                'discount' => 0.0,
            ];
        }

        // টার্গেট অডিয়েন্স যাচাই
        if (!empty($userVoucher->target_user_type)) {
            if ($userVoucher->target_user_type === 'registered_only' && !$userId) {
                return [
                    'valid'   => false,
                    'message' => 'এই ভাউচারটি শুধুমাত্র রেজিস্টার্ড গ্রাহকদের ওয়ালেটের জন্য প্রযোজ্য।',
                    'discount' => 0.0,
                ];
            }
            if ($userVoucher->target_user_type === 'guest_only' && $userId) {
                return [
                    'valid'   => false,
                    'message' => 'এই ভাউচারটি শুধুমাত্র গেস্ট গ্রাহকদের জন্য প্রযোজ্য।',
                    'discount' => 0.0,
                ];
            }
        }

        if (!$userVoucher->is_active) {
            return [
                'valid'   => false,
                'message' => 'This voucher campaign has been disabled by the store.',
                'discount' => 0.0,
            ];
        }

        if (!in_array($userVoucher->voucher_status, ['claimed', 'restored'])) {
            return [
                'valid'   => false,
                'message' => 'This voucher has already been ' . $userVoucher->voucher_status . '.',
                'discount' => 0.0,
            ];
        }

        if (!empty($userVoucher->expires_at) && Carbon::parse($userVoucher->expires_at)->isPast()) {
            return [
                'valid'   => false,
                'message' => 'This voucher has expired.',
                'discount' => 0.0,
            ];
        }

        if ($userVoucher->min_spend > 0 && $subtotal < (float)$userVoucher->min_spend) {
            return [
                'valid'   => false,
                'message' => 'Minimum spend of ৳' . number_format($userVoucher->min_spend, 2) . ' is required for this voucher.',
                'discount' => 0.0,
            ];
        }

        // ডিসকাউন্ট ক্যালকুলেশন
        $discount = 0.0;
        $voucherType = strtolower($userVoucher->voucher_type ?? 'fixed');

        switch ($voucherType) {
            case 'percentage':
                $discount = ($subtotal * (float)$userVoucher->discount_amount) / 100;
                if (!empty($userVoucher->max_discount) && $userVoucher->max_discount > 0) {
                    $discount = min($discount, (float)$userVoucher->max_discount);
                }
                break;

            case 'free_shipping':
                $discount = ($userVoucher->discount_amount > 0)
                    ? min((float)$userVoucher->discount_amount, $deliveryCharge)
                    : $deliveryCharge;
                break;

            case 'fixed':
            default:
                $discount = min((float)$userVoucher->discount_amount, $subtotal);
                break;
        }

        $discount = max(0.0, round($discount, 2));

        return [
            'valid'               => true,
            'message'             => 'Voucher applied successfully!',
            'voucher'             => $userVoucher,
            'customer_voucher_id' => $userVoucher->customer_voucher_id,
            'voucher_id'          => $userVoucher->voucher_id,
            'voucher_code'        => $cleanCode,
            'voucher_type'        => $voucherType,
            'discount'            => $discount,
        ];
    }

    /**
     * ৩. কার্ট ও চেকআউটের কম্বাইন্ড ডিসকাউন্ট ও নেট পেয়্যাবল হিসাব
     */
    public static function calculateCartDiscounts(
        float $subtotal,
        array $cartItems = [],
        ?string $couponCode = null,
        ?string $voucherCode = null,
        ?int $userId = null,
        float $deliveryCharge = 0.0
    ): array {
        $couponResult  = ['valid' => false, 'discount' => 0.0];
        $voucherResult = ['valid' => false, 'discount' => 0.0];

        $appliedCouponDiscount  = 0.0;
        $appliedVoucherDiscount = 0.0;

        // কুপন চেক
        if (!empty($couponCode)) {
            $couponResult = self::validateAndCalculateCoupon($couponCode, $subtotal, $cartItems, $userId, $deliveryCharge);
            if ($couponResult['valid']) {
                $appliedCouponDiscount = $couponResult['discount'];
            }
        }

        // ভাউচার চেক
        if (!empty($voucherCode)) {
            $voucherResult = self::validateAndCalculateVoucher($voucherCode, $subtotal, $cartItems, $userId, $deliveryCharge);
            if ($voucherResult['valid']) {
                $appliedVoucherDiscount = $voucherResult['discount'];
            }
        }

        // স্ট্যাকিং গার্ড: যদি কোনোটি স্ট্যাক্যাবল না হয়, তবে শুধুমাত্র সবচেয়ে ভালো ছাড়টি কার্যকর হবে
        if ($appliedCouponDiscount > 0 && $appliedVoucherDiscount > 0) {
            $isCouponStackable = !empty($couponResult['is_stackable']);
            if (!$isCouponStackable) {
                if ($appliedCouponDiscount >= $appliedVoucherDiscount) {
                    $appliedVoucherDiscount = 0.0;
                    $voucherResult = [
                        'valid'   => false,
                        'message' => 'Coupon and voucher cannot be combined on this order.',
                        'discount' => 0.0,
                    ];
                } else {
                    $appliedCouponDiscount = 0.0;
                    $couponResult = [
                        'valid'   => false,
                        'message' => 'Coupon and voucher cannot be combined on this order.',
                        'discount' => 0.0,
                    ];
                }
            }
        }

        // শিপিং ডিসকাউন্ট চেক
        $adjustedDeliveryCharge = $deliveryCharge;
        if (($couponResult['valid'] && ($couponResult['discount_type'] ?? '') === 'free_shipping') ||
            ($voucherResult['valid'] && ($voucherResult['voucher_type'] ?? '') === 'free_shipping')) {
            $adjustedDeliveryCharge = 0.0;
        }

        $totalDiscount = min($subtotal, ($appliedCouponDiscount + $appliedVoucherDiscount));
        $netPayable    = max(0.0, ($subtotal - $totalDiscount) + $adjustedDeliveryCharge);

        return [
            'subtotal'                 => round($subtotal, 2),
            'coupon_code'              => $couponResult['valid'] ? ($couponResult['code'] ?? null) : null,
            'coupon_discount'          => round($appliedCouponDiscount, 2),
            'coupon_result'            => $couponResult,
            'voucher_code'             => $voucherResult['valid'] ? ($voucherResult['voucher_code'] ?? null) : null,
            'voucher_discount'         => round($appliedVoucherDiscount, 2),
            'voucher_result'           => $voucherResult,
            'total_discount'           => round($totalDiscount, 2),
            'delivery_charge'          => round($deliveryCharge, 2),
            'adjusted_delivery_charge' => round($adjustedDeliveryCharge, 2),
            'final_payable'            => round($netPayable, 2),
        ];
    }

    /**
     * ৪. কাস্টমার ভাউচার ক্লেইম মেথড (Atomic Transaction & Lock)
     */
    public static function claimVoucher(int $voucherId, int $userId): array
    {
        return DB::transaction(function () use ($voucherId, $userId) {
            $voucher = DB::table('vouchers')->where('id', $voucherId)->lockForUpdate()->first();

            if (!$voucher || !$voucher->is_active) {
                return ['success' => false, 'message' => 'This voucher is not available.'];
            }

            // গেস্ট-অনলি ভাউচার রেজিস্টার্ড ইউজার ক্লেইম করতে পারবে না
            if (!empty($voucher->target_user_type) && $voucher->target_user_type === 'guest_only') {
                return ['success' => false, 'message' => 'This voucher campaign is only available for guest customers.'];
            }

            $now = Carbon::now();
            if (!empty($voucher->claim_start) && Carbon::parse($voucher->claim_start)->isFuture()) {
                return ['success' => false, 'message' => 'Voucher claim period has not started yet.'];
            }

            if (!empty($voucher->claim_end) && Carbon::parse($voucher->claim_end)->isPast()) {
                return ['success' => false, 'message' => 'Voucher claim period has ended.'];
            }

            // স্টক / টোটাল লিমিট চেক
            if (!is_null($voucher->total_vouchers_count) && $voucher->claimed_count >= $voucher->total_vouchers_count) {
                return ['success' => false, 'message' => 'All vouchers for this campaign have already been claimed.'];
            }

            // ব্যবহারকারীর ক্লেইম লিমিট চেক
            $userClaimCount = DB::table('customer_vouchers')
                ->where('voucher_id', $voucherId)
                ->where('user_id', $userId)
                ->count();

            if ($userClaimCount >= $voucher->max_claims_per_user) {
                return ['success' => false, 'message' => 'You have already reached the maximum claim limit for this voucher.'];
            }

            // এক্সপায়ার ডেট নির্ধারণ
            $validityDays = max(1, (int)$voucher->validity_days);
            $expiresAt    = $now->copy()->addDays($validityDays)->endOfDay();

            // ইউনিক ভাউচার কোড জেনারেশন (VCH-100-AB12CD)
            $prefix = !empty($voucher->code_prefix) ? strtoupper(trim($voucher->code_prefix)) : 'VCH';
            $uniqueCode = $prefix . '-' . $userId . '-' . strtoupper(Str::random(6));

            $customerVoucherId = DB::table('customer_vouchers')->insertGetId([
                'user_id'             => $userId,
                'voucher_id'          => $voucherId,
                'voucher_code'        => $uniqueCode,
                'status'              => 'claimed',
                'claimed_at'          => $now,
                'expires_at'          => $expiresAt,
                'redeemed_at'         => null,
                'order_id'            => null,
                'transaction_lock_at' => null,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);

            // কাউন্টার ইনক্রিমেন্ট
            DB::table('vouchers')->where('id', $voucherId)->increment('claimed_count');

            return [
                'success'             => true,
                'message'             => 'Voucher claimed successfully and added to your wallet!',
                'customer_voucher_id' => $customerVoucherId,
                'voucher_code'        => $uniqueCode,
                'expires_at'          => $expiresAt->format('d M Y, h:i A'),
            ];
        });
    }

    /**
     * ৫. ফাইনাল অর্ডার প্লেসমেন্টের সময় ডিসকাউন্ট স্ন্যাপশট ও রিডেম্পশন সেভ করা (Fail-Safe Column Protection)
     */
    public static function recordOrderDiscountRedemptions(
        int $orderId,
        ?string $couponCode,
        ?string $voucherCode,
        ?int $userId,
        float $subtotal,
        float $finalTotal,
        float $deliveryCharge = 0.0,
        array $cartItems = []
    ): void {
        DB::transaction(function () use ($orderId, $couponCode, $voucherCode, $userId, $subtotal, $finalTotal, $deliveryCharge, $cartItems) {
            $now = Carbon::now();
            $rCols = Schema::hasTable('coupon_voucher_redemptions')
                ? Schema::getColumnListing('coupon_voucher_redemptions')
                : [];

            // কুপন রিডেম্পশন
            if (!empty($couponCode)) {
                $couponRes = self::validateAndCalculateCoupon($couponCode, $subtotal, $cartItems, $userId, $deliveryCharge);
                if ($couponRes['valid']) {
                    $coupon = DB::table('coupons')->where('id', $couponRes['coupon_id'])->lockForUpdate()->first();
                    if ($coupon) {
                        // উভয় কলাম সাপোর্ট
                        if (Schema::hasColumn('coupons', 'used_count')) {
                            DB::table('coupons')->where('id', $coupon->id)->increment('used_count');
                        }
                        if (Schema::hasColumn('coupons', 'current_usage_count')) {
                            DB::table('coupons')->where('id', $coupon->id)->increment('current_usage_count');
                        }

                        if (!empty($rCols)) {
                            $rawRedemption = [
                                'order_id'            => $orderId,
                                'user_id'             => $userId,
                                'type'                => 'coupon',
                                'discount_type'       => $couponRes['discount_type'] ?? 'coupon',
                                'reference_id'        => $coupon->id,
                                'coupon_id'           => $coupon->id,
                                'customer_voucher_id' => null,
                                'code_applied'        => $couponRes['code'],
                                'code_used'           => $couponRes['code'],
                                'coupon_code'         => $couponRes['code'],
                                'code'                => $couponRes['code'],
                                'discount_amount'     => $couponRes['discount'],
                                'discount'            => $couponRes['discount'],
                                'amount'              => $couponRes['discount'],
                                'value'               => $couponRes['discount'],
                                'order_subtotal'      => $subtotal,
                                'order_final_total'   => $finalTotal,
                                'snapshot_data'       => json_encode([
                                    'coupon_name'         => $coupon->name ?? null,
                                    'raw_discount_amount' => $coupon->discount_amount ?? 0,
                                    'min_order_amount'    => $coupon->min_order_amount ?? 0,
                                    'applied_at'          => $now->toDateTimeString(),
                                ]),
                                'status'              => 'completed',
                                'created_at'          => $now,
                                'updated_at'          => $now,
                            ];

                            $insertData = array_intersect_key($rawRedemption, array_flip($rCols));
                            DB::table('coupon_voucher_redemptions')->insert($insertData);
                        }
                    }
                }
            }

            // ভাউচার রিডেম্পশন
            if (!empty($voucherCode)) {
                $voucherRes = self::validateAndCalculateVoucher($voucherCode, $subtotal, $cartItems, $userId, $deliveryCharge);
                if ($voucherRes['valid']) {
                    $customerVoucher = DB::table('customer_vouchers')
                        ->where('id', $voucherRes['customer_voucher_id'])
                        ->lockForUpdate()
                        ->first();

                    if ($customerVoucher && in_array($customerVoucher->status, ['claimed', 'restored'])) {
                        DB::table('customer_vouchers')->where('id', $customerVoucher->id)->update([
                            'status'      => 'used',
                            'redeemed_at' => $now,
                            'order_id'    => $orderId,
                            'updated_at'  => $now,
                        ]);

                        if (!empty($rCols)) {
                            $rawVoucherRedemption = [
                                'order_id'            => $orderId,
                                'user_id'             => $userId,
                                'type'                => 'voucher',
                                'discount_type'       => 'voucher',
                                'reference_id'        => $voucherRes['voucher_id'],
                                'voucher_id'          => $voucherRes['voucher_id'],
                                'customer_voucher_id' => $customerVoucher->id,
                                'code_applied'        => $voucherRes['voucher_code'],
                                'code_used'           => $voucherRes['voucher_code'],
                                'coupon_code'         => $voucherRes['voucher_code'],
                                'voucher_code'        => $voucherRes['voucher_code'],
                                'code'                => $voucherRes['voucher_code'],
                                'discount_amount'     => $voucherRes['discount'],
                                'discount'            => $voucherRes['discount'],
                                'amount'              => $voucherRes['discount'],
                                'value'               => $voucherRes['discount'],
                                'order_subtotal'      => $subtotal,
                                'order_final_total'   => $finalTotal,
                                'snapshot_data'       => json_encode([
                                    'voucher_title'       => $voucherRes['voucher']->title ?? 'Personal Voucher',
                                    'raw_discount_amount' => $voucherRes['voucher']->discount_amount ?? 0,
                                    'min_spend'           => $voucherRes['voucher']->min_spend ?? 0,
                                    'applied_at'          => $now->toDateTimeString(),
                                ]),
                                'status'              => 'completed',
                                'created_at'          => $now,
                                'updated_at'          => $now,
                            ];

                            $insertVoucherData = array_intersect_key($rawVoucherRedemption, array_flip($rCols));
                            DB::table('coupon_voucher_redemptions')->insert($insertVoucherData);
                        }
                    }
                }
            }
        });
    }

    /**
     * ৬. অর্ডার ক্যানসেল বা রিফান্ড হলে ভাউচার ও কুপন রিস্টোরেশন
     */
    public static function restoreOrderDiscounts(int $orderId, string $reason = 'Order Cancelled'): void
    {
        DB::transaction(function () use ($orderId, $reason) {
            $redemptions = DB::table('coupon_voucher_redemptions')
                ->where('order_id', $orderId)
                ->whereIn('status', ['completed', 'applied'])
                ->get();

            foreach ($redemptions as $redemption) {
                // কুপন ইউসেজ কাউন্টার রিভার্স
                $couponId = $redemption->coupon_id ?? ($redemption->reference_id ?? null);
                if ($couponId && Schema::hasTable('coupons')) {
                    if (Schema::hasColumn('coupons', 'used_count')) {
                        DB::table('coupons')->where('id', $couponId)->where('used_count', '>', 0)->decrement('used_count');
                    }
                    if (Schema::hasColumn('coupons', 'current_usage_count')) {
                        DB::table('coupons')->where('id', $couponId)->where('current_usage_count', '>', 0)->decrement('current_usage_count');
                    }
                }

                // ভাউচার ওয়ালেটে ফেরত দেওয়া
                if ($redemption->customer_voucher_id && Schema::hasTable('customer_vouchers')) {
                    $voucher = DB::table('customer_vouchers')->where('id', $redemption->customer_voucher_id)->first();
                    if ($voucher) {
                        $isStillValid = empty($voucher->expires_at) || Carbon::parse($voucher->expires_at)->isFuture();
                        DB::table('customer_vouchers')->where('id', $voucher->id)->update([
                            'status'      => $isStillValid ? 'claimed' : 'expired',
                            'redeemed_at' => null,
                            'order_id'    => null,
                            'updated_at'  => Carbon::now(),
                        ]);
                    }
                }

                DB::table('coupon_voucher_redemptions')->where('id', $redemption->id)->update([
                    'status'     => 'restored',
                    'updated_at' => Carbon::now(),
                ]);
            }
        });
    }

    /**
     * ৭. প্রাইভেট হেল্পার: নির্দিষ্ট প্রোডাক্ট বা ক্যাটাগরি অনুযায়ী এলিজিবল অ্যামাউন্ট ফিল্টারিং (Real Product & Category Hierarchy)
     */
    protected static function calculateEligibleSubtotal(object $coupon, array $cartItems, float $subtotal): float
    {
        $applicableTo = $coupon->applicable_to ?? 'all';

        if ($applicableTo === 'all' || empty($cartItems)) {
            return $subtotal;
        }

        $couponId = $coupon->id;

        // ১. এলিজিবল প্রোডাক্ট আইডি সংগ্রহ (পিভট টেবিল ও JSON কলাম উভয়েই চেক)
        $includedProductIds = [];
        $excludedProductIds = [];

        if (Schema::hasTable('coupon_products')) {
            $includedProductIds = DB::table('coupon_products')
                ->where('coupon_id', $couponId)
                ->where('is_excluded', 0)
                ->pluck('product_id')
                ->map(fn($id) => (int)$id)
                ->toArray();

            $excludedProductIds = DB::table('coupon_products')
                ->where('coupon_id', $couponId)
                ->where('is_excluded', 1)
                ->pluck('product_id')
                ->map(fn($id) => (int)$id)
                ->toArray();
        }

        if (empty($includedProductIds) && !empty($coupon->product_ids)) {
            $parsed = is_array($coupon->product_ids)
                ? $coupon->product_ids
                : (json_decode($coupon->product_ids, true) ?? explode(',', $coupon->product_ids));
            $includedProductIds = array_map('intval', array_filter($parsed));
        }

        // ২. এলিজিবল ক্যাটাগরি আইডি সংগ্রহ (পিভট টেবিল ও JSON কলাম উভয়েই চেক)
        $includedCategoryIds = [];
        $excludedCategoryIds = [];

        if (Schema::hasTable('coupon_categories')) {
            $includedCategoryIds = DB::table('coupon_categories')
                ->where('coupon_id', $couponId)
                ->where('is_excluded', 0)
                ->pluck('category_id')
                ->map(fn($id) => (int)$id)
                ->toArray();

            $excludedCategoryIds = DB::table('coupon_categories')
                ->where('coupon_id', $couponId)
                ->where('is_excluded', 1)
                ->pluck('category_id')
                ->map(fn($id) => (int)$id)
                ->toArray();
        }

        if (empty($includedCategoryIds) && !empty($coupon->category_ids)) {
            $parsedCat = is_array($coupon->category_ids)
                ? $coupon->category_ids
                : (json_decode($coupon->category_ids, true) ?? explode(',', $coupon->category_ids));
            $includedCategoryIds = array_map('intval', array_filter($parsedCat));
        }

        $eligibleSum = 0.0;

        foreach ($cartItems as $item) {
            $productId = (int)($item['id'] ?? ($item['product_id'] ?? 0));
            $price     = (float)($item['price'] ?? 0);
            $qty       = (int)($item['quantity'] ?? ($item['qty'] ?? 1));
            $lineTotal = $price * $qty;

            // এক্সক্লুডেড প্রোডাক্ট হলে বাদ
            if (!empty($excludedProductIds) && in_array($productId, $excludedProductIds)) {
                continue;
            }

            $isEligible = false;

            if ($applicableTo === 'specific_products') {
                if (in_array($productId, $includedProductIds)) {
                    $isEligible = true;
                }
            } elseif ($applicableTo === 'specific_categories') {
                // প্রোডাক্টের ক্যাটাগরি ডাটাবেজ থেকে রিয়েল-টাইম ডিটেক্ট করা
                $productCategoryIds = [];

                if (isset($item['category_id']) && (int)$item['category_id'] > 0) {
                    $productCategoryIds[] = (int)$item['category_id'];
                }

                if (Schema::hasTable('products') && $productId > 0) {
                    $prodRecord = DB::table('products')->where('id', $productId)->first();
                    if ($prodRecord) {
                        if (!empty($prodRecord->category_id)) {
                            $productCategoryIds[] = (int)$prodRecord->category_id;
                        }
                        if (!empty($prodRecord->sub_category_id)) {
                            $productCategoryIds[] = (int)$prodRecord->sub_category_id;
                        }
                    }
                }

                if (Schema::hasTable('product_category') && $productId > 0) {
                    $pivotCats = DB::table('product_category')->where('product_id', $productId)->pluck('category_id')->toArray();
                    $productCategoryIds = array_merge($productCategoryIds, array_map('intval', $pivotCats));
                }

                $productCategoryIds = array_unique(array_filter($productCategoryIds));

                // এক্সক্লুডেড ক্যাটাগরি চেক
                if (!empty($excludedCategoryIds) && !empty(array_intersect($productCategoryIds, $excludedCategoryIds))) {
                    continue;
                }

                // ইনক্লুডেড ক্যাটাগরি ম্যাচ
                if (!empty(array_intersect($productCategoryIds, $includedCategoryIds))) {
                    $isEligible = true;
                }
            } else {
                $isEligible = true;
            }

            if ($isEligible) {
                $eligibleSum += $lineTotal;
            }
        }

        return round($eligibleSum, 2);
    }
}