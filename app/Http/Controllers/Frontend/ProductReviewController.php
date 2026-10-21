<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductReviewController extends Controller
{
    public function store(Request $request, $productId)
    {
        $userId = Auth::id();

        // ১. অ্যাডমিন রিভিউ পারমিশন সেটিং চেক
        $allowReviewSetting = DB::table('settings')->where('key', 'detail_allow_review')->value('value') ?? '1';
        if ($allowReviewSetting == '0') {
            return back()->with('error', 'Product reviews are currently disabled.');
        }

        // ২. ইউজার প্রোডাক্টটি কিনেছে এবং তা ডেলিভার্ড হয়েছে কি না যাচাই
        $hasPurchased = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', $userId)
            ->where('order_items.product_id', $productId)
            ->whereIn('orders.order_status', ['delivered', 'completed'])
            ->exists();

        if (!$hasPurchased) {
            return back()->with('error', 'Only verified buyers who received this product can write a review!');
        }

        // ৩. অলরেডি রিভিউ দিয়েছে কি না চেক (এক প্রোডাক্টে ১ বার)
        $existing = DB::table('product_reviews')->where('product_id', $productId)->where('user_id', $userId)->first();
        if ($existing) {
            return back()->with('error', 'You have already reviewed this product. You can update your existing review.');
        }

        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        DB::table('product_reviews')->insert([
            'product_id' => $productId,
            'user_id'    => $userId,
            'rating'     => $request->rating,
            'comment'    => trim($request->comment),
            'status'     => 'approved',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back()->with('success', 'Thank you for your valuable feedback!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        $review = DB::table('product_reviews')->where('id', $id)->where('user_id', Auth::id())->first();
        if (!$review) {
            return back()->with('error', 'Unauthorized action.');
        }

        DB::table('product_reviews')->where('id', $id)->update([
            'rating'     => $request->rating,
            'comment'    => trim($request->comment),
            'updated_at' => now()
        ]);

        return back()->with('success', 'Review updated successfully!');
    }

    public function destroy($id)
    {
        DB::table('product_reviews')->where('id', $id)->where('user_id', Auth::id())->delete();
        return back()->with('success', 'Review deleted successfully!');
    }
}