<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyPoint;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function getMyPoints(): JsonResponse
    {
        $user = auth()->user();
        $history = LoyaltyPoint::where('user_id', $user->id)->latest()->paginate(10);

        return response()->json([
            'status'        => true,
            'reward_points' => $user->reward_points,
            'history'       => $history
        ]);
    }

    public function redeemPoints(Request $request): JsonResponse
    {
        $request->validate(['points' => 'required|integer|min:100']); // Min 100 points to redeem

        $user = User::findOrFail(auth()->id());

        if ($user->reward_points < $request->points) {
            return response()->json(['status' => false, 'message' => 'Insufficient reward points.'], 422);
        }

        $user->decrement('reward_points', $request->points);

        LoyaltyPoint::create([
            'user_id'     => $user->id,
            'points'      => $request->points,
            'type'        => 'redeemed',
            'description' => 'Redeemed points for checkout discount',
        ]);

        return response()->json(['status' => true, 'message' => 'Points redeemed successfully.', 'remaining_points' => $user->reward_points]);
    }
}