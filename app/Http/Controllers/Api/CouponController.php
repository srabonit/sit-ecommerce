<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coupon\CouponRequest;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * Get All Coupons List (Admin View)
     */
    public function index(): JsonResponse
    {
        $coupons = Coupon::latest()->paginate(10);

        return response()->json([
            'status' => true,
            'data'   => $coupons
        ], 200);
    }

    /**
     * Store or Update Coupon
     */
    public function store(CouponRequest $request): JsonResponse
    {
        $coupon = Coupon::create($request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Coupon created successfully.',
            'data'    => $coupon
        ], 201);
    }

    /**
     * Apply and Validate Coupon (POS & E-Commerce Cart)
     */
    public function applyCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'code'       => 'required|string',
            'subtotal'   => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', strtoupper($request->code))
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid or expired coupon code.'
            ], 404);
        }

        // Date Validation
        $now = now();
        if (($coupon->starts_at && $now->lt($coupon->starts_at)) || ($coupon->expires_at && $now->gt($coupon->expires_at))) {
            return response()->json([
                'status'  => false,
                'message' => 'Coupon code is not valid at this time.'
            ], 422);
        }

        // Usage Limit Validation
        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return response()->json([
                'status'  => false,
                'message' => 'Coupon usage limit has been exceeded.'
            ], 422);
        }

        // Minimum Spend Validation
        if ($request->subtotal < $coupon->min_spend) {
            return response()->json([
                'status'  => false,
                'message' => "Minimum subtotal amount to use this coupon is {$coupon->min_spend}."
            ], 422);
        }

        // Calculate Discount
        $discountAmount = 0;
        if ($coupon->type === 'fixed') {
            $discountAmount = min($coupon->value, $request->subtotal);
        } else {
            $discountAmount = ($request->subtotal * $coupon->value) / 100;
        }

        return response()->json([
            'status'  => true,
            'message' => 'Coupon applied successfully.',
            'data'    => [
                'code'            => $coupon->code,
                'discount_amount' => round($discountAmount, 2),
                'type'            => $coupon->type,
                'value'           => $coupon->value,
            ]
        ], 200);
    }
}