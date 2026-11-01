<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\ProductReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ProductReviewController extends Controller
{
    public function __construct()
    {
        // ব্যাকগ্রাউন্ডে স্বয়ংক্রিয় স্কিমা যাচাই
        if (class_exists(ProductReviewService::class)) {
            ProductReviewService::ensureSchema();
        }
    }

    public function store(Request $request, $productId)
    {
        $userId = Auth::id();
        $productId = (int) $productId;

        // ১. অ্যাডমিন রিভিউ পারমিশন সেটিং চেক (বিদ্যমান কোড বজায় রাখা হয়েছে)
        $allowReviewSetting = DB::table('settings')->where('key', 'detail_allow_review')->value('value') ?? '1';
        $showReviewSetting  = DB::table('settings')->where('key', 'detail_show_reviews')->value('value') ?? '1';
        
        if ($allowReviewSetting == '0' || $showReviewSetting == '0') {
            return back()->with('error', 'Product reviews are currently disabled.');
        }

        // ২. ইউজার প্রোডাক্টটি কিনেছে এবং তা ডেলিভার্ড হয়েছে কি না যাচাই (বিদ্যমান কোড)
        $purchaseOrder = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', $userId)
            ->where('order_items.product_id', $productId)
            ->whereIn('orders.order_status', ['delivered', 'completed'])
            ->select('orders.id as order_id')
            ->first();

        if (!$purchaseOrder) {
            return back()->with('error', 'Only verified buyers who received this product can write a review!');
        }

        // ৩. অলরেডি রিভিউ দিয়েছে কি না চেক (এক প্রোডাক্টে ১ বার) (বিদ্যমান কোড)
        $existing = DB::table('product_reviews')->where('product_id', $productId)->where('user_id', $userId)->first();
        if ($existing) {
            return back()->with('error', 'You have already reviewed this product. You can update your existing review.');
        }

        // ৪. সেটিংস রেজোলিউশন (ছবি ও অ্যাপ্রুভাল পলিসির জন্য)
        $settings = class_exists(ProductReviewService::class) 
            ? ProductReviewService::resolveProductSettings($productId) 
            : [];

        $maxImages = (int) ($settings['max_images_per_review'] ?? 4);
        $maxSizeKb = (int) ($settings['max_image_size_kb'] ?? 2048);

        $request->validate([
            'rating'          => 'required|integer|min:1|max:5',
            'comment'         => 'required|string|max:1500',
            'title'           => 'nullable|string|max:150',
            'review_images'   => 'nullable|array|max:' . $maxImages,
            'review_images.*' => 'image|mimes:jpeg,jpg,png,webp|max:' . $maxSizeKb,
        ]);

        // ৫. ইমেজ আপলোড হ্যান্ডলিং (নিরাপদ ও ইউনিভার্সাল)
        $uploadedImages = [];
        $imageReviewsEnabled = $settings['image_reviews_enabled'] ?? true;
        if ($imageReviewsEnabled && $request->hasFile('review_images') && class_exists(ProductReviewService::class)) {
            $uploadedImages = ProductReviewService::uploadReviewImages($request->file('review_images'), $productId);
        }

        // ৬. স্প্যাম ও প্রাথমিক স্ট্যাটাস নির্ধারণ (Auto vs Manual Approval)
        $isSuspicious = class_exists(ProductReviewService::class) 
            ? ProductReviewService::checkSuspiciousContent(($request->title ?? '') . ' ' . $request->comment) 
            : false;

        // অ্যাডমিনে ম্যানুয়াল মোড না থাকলে ভেরিফায়েড বায়ারের রিভিউ (ছবিসহ বা ছাড়া) সরাসরি অটো-অ্যাপ্রুভ হবে যাতে পেজে ও অটো-রিপ্লাইতে সাথে সাথে দেখায়
        $approvalMode = $settings['approval_mode'] ?? 'auto';
        if ($approvalMode === 'manual' || $isSuspicious) {
            $initialStatus = 'pending';
        } else {
            $initialStatus = 'approved';
        }

        $reviewData = [
            'product_id'           => $productId,
            'user_id'              => $userId,
            'order_id'             => $purchaseOrder->order_id ?? null,
            'rating'               => (int) $request->rating,
            'title'                => $request->filled('title') ? trim($request->title) : null,
            'comment'              => trim($request->comment),
            'images_json'          => count($uploadedImages) > 0 ? json_encode($uploadedImages) : null,
            'images_visible'       => true,
            'is_verified_purchase' => true,
            'status'               => $initialStatus,
            'is_visible'           => true,
            'is_pinned'            => false,
            'pin_order'            => 0,
            'is_reported'          => false,
            'report_count'         => 0,
            'helpful_count'        => 0,
            'is_suspicious'        => $isSuspicious,
            'created_at'           => now(),
            'updated_at'           => now(),
        ];

        // নিরাপদ কলাম ফিল্টারিং (যাতে কোনো কলাম মিসিং থাকলেও এরর না দেয়)
        $safeReviewData = [];
        foreach ($reviewData as $col => $val) {
            if (Schema::hasColumn('product_reviews', $col)) {
                $safeReviewData[$col] = $val;
            }
        }

        $reviewId = DB::table('product_reviews')->insertGetId($safeReviewData);

        // ৭. অটো-রিপ্লাই, ইমেইল অ্যালার্ট ও ক্যাশ ক্লিয়ার
        if (class_exists(ProductReviewService::class)) {
            if ($initialStatus === 'approved') {
                ProductReviewService::triggerAutoReply((int) $reviewId, 'on_submission');
            }
            ProductReviewService::notifyAdminNewReview((int) $reviewId);
            ProductReviewService::clearReviewCaches($productId);
        }

        $msg = ($initialStatus === 'approved')
            ? 'Thank you for your valuable feedback!'
            : 'Thank you! Your review has been submitted and is pending approval.';

        return back()->with('success', $msg);
    }

    public function update(Request $request, $id)
    {
        $review = DB::table('product_reviews')->where('id', $id)->where('user_id', Auth::id())->first();
        if (!$review) {
            return back()->with('error', 'Unauthorized action.');
        }

        $settings = class_exists(ProductReviewService::class) 
            ? ProductReviewService::resolveProductSettings($review->product_id) 
            : [];

        $maxImages = (int) ($settings['max_images_per_review'] ?? 4);
        $maxSizeKb = (int) ($settings['max_image_size_kb'] ?? 2048);

        $request->validate([
            'rating'          => 'required|integer|min:1|max:5',
            'comment'         => 'required|string|max:1500',
            'title'           => 'nullable|string|max:150',
            'review_images'   => 'nullable|array|max:' . $maxImages,
            'review_images.*' => 'image|mimes:jpeg,jpg,png,webp|max:' . $maxSizeKb,
        ]);

        $existingImages = !empty($review->images_json) ? (json_decode($review->images_json, true) ?: []) : [];

        // নতুন ছবি যুক্ত করার লজিক
        $imageReviewsEnabled = $settings['image_reviews_enabled'] ?? true;
        if ($imageReviewsEnabled && $request->hasFile('review_images') && class_exists(ProductReviewService::class)) {
            $newImages = ProductReviewService::uploadReviewImages($request->file('review_images'), $review->product_id);
            $existingImages = array_slice(array_merge($existingImages, $newImages), 0, $maxImages);
        }

        $updateData = [
            'rating'     => (int) $request->rating,
            'comment'    => trim($request->comment),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('product_reviews', 'title') && $request->filled('title')) {
            $updateData['title'] = trim($request->title);
        }
        if (Schema::hasColumn('product_reviews', 'images_json')) {
            $updateData['images_json'] = count($existingImages) > 0 ? json_encode($existingImages) : null;
        }
        if (Schema::hasColumn('product_reviews', 'images_visible')) {
            $updateData['images_visible'] = true;
        }

        DB::table('product_reviews')->where('id', $id)->update($updateData);

        if (class_exists(ProductReviewService::class)) {
            // যদি আগে অটো-রিপ্লাই না বসে থাকে তবে এডিট করার পর সাথে সাথে অটো-রিপ্লাই বসিয়ে দেবে
            if (empty($review->admin_reply) && ($review->status ?? 'approved') === 'approved') {
                ProductReviewService::triggerAutoReply((int) $id, 'on_submission');
            }
            ProductReviewService::clearReviewCaches($review->product_id);
        }

        return back()->with('success', 'Review updated successfully!');
    }

    public function destroy($id)
    {
        $review = DB::table('product_reviews')->where('id', $id)->where('user_id', Auth::id())->first();
        if (!$review) {
            return back()->with('error', 'Unauthorized action.');
        }

        // ছবি থাকলে সার্ভার থেকে ক্লিনআপ
        if (!empty($review->images_json) && class_exists(ProductReviewService::class)) {
            $imgs = json_decode($review->images_json, true);
            if (is_array($imgs)) {
                ProductReviewService::deleteReviewImages($imgs);
            }
        }

        DB::table('product_reviews')->where('id', $id)->where('user_id', Auth::id())->delete();

        if (class_exists(ProductReviewService::class)) {
            ProductReviewService::clearReviewCaches($review->product_id);
        }

        return back()->with('success', 'Review deleted successfully!');
    }

    /**
     * অতিরিক্ত মেথড ১: হেল্পফুল ভোট অ্যাকশন (AJAX)
     */
    public function toggleHelpful(Request $request, $id)
    {
        $userId = Auth::id();
        $ip = $request->ip();

        // ডুপ্লিকেট ভোট রোধ
        $alreadyVoted = DB::table('product_review_reports')
            ->where('review_id', $id)
            ->where('type', 'helpful')
            ->where(function ($q) use ($userId, $ip) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->where('ip_address', $ip);
                }
            })
            ->exists();

        if ($alreadyVoted) {
            return response()->json(['status' => 'info', 'message' => 'You already marked this review as helpful.']);
        }

        if (Schema::hasTable('product_review_reports')) {
            DB::table('product_review_reports')->insert([
                'review_id'  => $id,
                'user_id'    => $userId,
                'ip_address' => $ip,
                'type'       => 'helpful',
                'reason'     => 'Customer found this review helpful',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasColumn('product_reviews', 'helpful_count')) {
            DB::table('product_reviews')->where('id', $id)->increment('helpful_count');
            $count = (int) DB::table('product_reviews')->where('id', $id)->value('helpful_count');
        } else {
            $count = 1;
        }

        return response()->json([
            'status'        => 'success',
            'message'       => 'Thank you for your feedback!',
            'helpful_count' => $count,
        ]);
    }

    /**
     * অতিরিক্ত মেথড ২: অনুপযুক্ত রিভিউ রিপোর্ট করা (AJAX)
     */
    public function reportReview(Request $request, $id)
    {
        $userId = Auth::id();
        $ip = $request->ip();

        $alreadyReported = DB::table('product_review_reports')
            ->where('review_id', $id)
            ->where('type', 'report')
            ->where(function ($q) use ($userId, $ip) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->where('ip_address', $ip);
                }
            })
            ->exists();

        if ($alreadyReported) {
            return response()->json(['status' => 'info', 'message' => 'You have already reported this review.']);
        }

        if (Schema::hasTable('product_review_reports')) {
            DB::table('product_review_reports')->insert([
                'review_id'  => $id,
                'user_id'    => $userId,
                'ip_address' => $ip,
                'type'       => 'report',
                'reason'     => trim($request->input('reason', 'Reported as inappropriate')),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasColumn('product_reviews', 'report_count')) {
            DB::table('product_reviews')->where('id', $id)->increment('report_count');
            DB::table('product_reviews')->where('id', $id)->update(['is_reported' => 1]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Review has been reported to moderators for inspection.',
        ]);
    }

    /**
     * অতিরিক্ত মেথড ৩: প্রোডাক্ট পেজের জন্য লাইভ AJAX রিভিউ ফেচ
     */
    public function getReviewsAjax(Request $request, $productId)
    {
        $productId = (int) $productId;
        $perPage = (int) $request->input('per_page', 5);
        $sortBy  = $request->input('sort', 'latest');

        $query = DB::table('product_reviews')
            ->join('users', 'product_reviews.user_id', '=', 'users.id')
            ->where('product_reviews.product_id', $productId)
            ->where('product_reviews.status', 'approved');

        if (Schema::hasColumn('product_reviews', 'is_visible')) {
            $query->where('product_reviews.is_visible', 1);
        }

        $query->select('product_reviews.*', 'users.name as user_name');

        // টপ পিনড রিভিউ আগে দেখাবে
        if (Schema::hasColumn('product_reviews', 'is_pinned')) {
            $query->orderBy('product_reviews.is_pinned', 'desc')
                  ->orderBy('product_reviews.pin_order', 'asc');
        }

        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('product_reviews.id', 'asc');
                break;
            case 'rating_high':
                $query->orderBy('product_reviews.rating', 'desc')->latest('product_reviews.id');
                break;
            case 'rating_low':
                $query->orderBy('product_reviews.rating', 'asc')->latest('product_reviews.id');
                break;
            default:
                $query->latest('product_reviews.id');
                break;
        }

        $reviews = $query->paginate($perPage);

        $reviews->getCollection()->transform(function ($rev) {
            $rev->images = !empty($rev->images_json) ? (json_decode($rev->images_json, true) ?: []) : [];
            return $rev;
        });

        $html = view('frontend.partials.product_reviews_list', compact('reviews'))->render();

        return response()->json([
            'status'       => 'success',
            'html'         => $html,
            'total_count'  => $reviews->total(),
            'current_page' => $reviews->currentPage(),
            'has_more'     => $reviews->hasMorePages(),
        ]);
    }
}