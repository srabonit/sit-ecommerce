<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductReviewService;
use App\Services\ActivityLogger;
use App\Services\ImagePathService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminProductReviewController extends Controller
{
    /**
     * কনস্ট্রাক্টর - ডাটাবেস স্কিমা গার্ড স্বয়ংক্রিয়ভাবে নিশ্চিত করা
     */
    public function __construct()
    {
        ProductReviewService::ensureSchema();
    }

    /**
     * ১. মূল রিভিউ ম্যানেজমেন্ট টেবিল, মাল্টি-লেভেল ফিল্টারিং, সর্টিং ও পেজিনেশন
     */
    public function index(Request $request)
    {
        $query = DB::table('product_reviews')
            ->leftJoin('products', 'product_reviews.product_id', '=', 'products.id')
            ->leftJoin('users', 'product_reviews.user_id', '=', 'users.id')
            ->select(
                'product_reviews.*',
                'products.title as product_title',
                'products.slug as product_slug',
                'products.thumbnail as product_thumbnail',
                'products.product_code as product_code',
                'users.name as customer_name',
                'users.email as customer_email',
                'users.phone as customer_phone'
            );

        if (Schema::hasColumn('products', 'sku')) {
            $query->addSelect('products.sku as product_sku');
        } else {
            $query->addSelect(DB::raw('NULL as product_sku'));
        }

        // ক. সার্চ ফিল্টার (Review ID, Product Title, SKU, Customer Name, Email, Phone)
        if ($request->filled('search')) {
            $s = trim($request->search);
            $cleanId = ltrim($s, '#');
            $query->where(function ($q) use ($s, $cleanId) {
                if (is_numeric($cleanId)) {
                    $q->where('product_reviews.id', $cleanId)
                      ->orWhere('product_reviews.product_id', $cleanId);
                }
                $q->orWhere('products.title', 'like', "%{$s}%")
                  ->orWhere('products.product_code', 'like', "%{$s}%")
                  ->orWhere('users.name', 'like', "%{$s}%")
                  ->orWhere('users.email', 'like', "%{$s}%")
                  ->orWhere('users.phone', 'like', "%{$s}%")
                  ->orWhere('product_reviews.comment', 'like', "%{$s}%");

                if (Schema::hasColumn('products', 'sku')) {
                    $q->orWhere('products.sku', 'like', "%{$s}%");
                }
                if (Schema::hasColumn('product_reviews', 'title')) {
                    $q->orWhere('product_reviews.title', 'like', "%{$s}%");
                }
            });
        }

        // খ. স্ট্যাটাস ফিল্টার (All, Pending, Approved, Rejected)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('product_reviews.status', strtolower(trim($request->status)));
        }

        // গ. ভিজিবিলিটি ফিল্টার (Visible, Hidden)
        if ($request->filled('visibility') && $request->visibility !== 'all') {
            $query->where('product_reviews.is_visible', $request->visibility === 'visible' ? 1 : 0);
        }

        // ঘ. স্টার রেটিং ফিল্টার (1, 2, 3, 4, 5)
        if ($request->filled('rating') && $request->rating !== 'all') {
            $query->where('product_reviews.rating', (int)$request->rating);
        }

        // ঙ. ইমেজ রিভিউ ফিল্টার (With Images, Without Images)
        if ($request->filled('has_images') && $request->has_images !== 'all') {
            if ($request->has_images === 'yes') {
                $query->whereNotNull('product_reviews.images_json')
                      ->where('product_reviews.images_json', '!=', '[]')
                      ->where('product_reviews.images_json', '!=', '');
            } else {
                $query->where(function ($sub) {
                    $sub->whereNull('product_reviews.images_json')
                        ->orWhere('product_reviews.images_json', '[]')
                        ->orWhere('product_reviews.images_json', '');
                });
            }
        }

        // চ. ভেরিফায়েড পারচেজ ফিল্টার
        if ($request->filled('verified') && $request->verified !== 'all') {
            $query->where('product_reviews.is_verified_purchase', $request->verified === 'yes' ? 1 : 0);
        }

        // ছ. অ্যাডমিন রিপ্লাই ফিল্টার (Replied, Unanswered)
        if ($request->filled('replied') && $request->replied !== 'all') {
            if ($request->replied === 'yes') {
                $query->whereNotNull('product_reviews.admin_reply')
                      ->where('product_reviews.admin_reply', '!=', '');
            } else {
                $query->where(function ($sub) {
                    $sub->whereNull('product_reviews.admin_reply')
                        ->orWhere('product_reviews.admin_reply', '');
                });
            }
        }

        // জ. পিনড ও রিপোর্টেড ফিল্টার
        if ($request->filled('pinned') && $request->pinned === 'yes') {
            $query->where('product_reviews.is_pinned', 1);
        }
        if ($request->filled('reported') && $request->reported === 'yes') {
            $query->where('product_reviews.is_reported', 1);
        }

        // ঝ. স্পেসিফিক প্রোডাক্ট বা কাস্টমার ফিল্টার
        if ($request->filled('product_id')) {
            $query->where('product_reviews.product_id', (int)$request->product_id);
        }
        if ($request->filled('user_id')) {
            $query->where('product_reviews.user_id', (int)$request->user_id);
        }

        // ঞ. তারিখ অনুযায়ী ফিল্টারিং (Date Range)
        if ($request->filled('date_filter') && $request->date_filter !== 'all') {
            $now = Carbon::now();
            switch ($request->date_filter) {
                case 'today':
                    $query->whereDate('product_reviews.created_at', Carbon::today());
                    break;
                case 'yesterday':
                    $query->whereDate('product_reviews.created_at', Carbon::yesterday());
                    break;
                case 'this_week':
                    $query->whereBetween('product_reviews.created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
                    break;
                case 'last_week':
                    $query->whereBetween('product_reviews.created_at', [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereBetween('product_reviews.created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
                    break;
                case 'last_month':
                    $query->whereBetween('product_reviews.created_at', [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()]);
                    break;
                case 'last_30_days':
                    $query->where('product_reviews.created_at', '>=', Carbon::now()->subDays(30)->startOfDay());
                    break;
                case 'single_date':
                    if ($request->filled('single_date')) {
                        $query->whereDate('product_reviews.created_at', Carbon::parse($request->single_date)->toDateString());
                    }
                    break;
                case 'custom':
                    if ($request->filled('start_date')) {
                        $query->where('product_reviews.created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
                    }
                    if ($request->filled('end_date')) {
                        $query->where('product_reviews.created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
                    }
                    break;
            }
        }

        // টপ স্ট্যাটাস ট্যাবসের লাইভ কাউন্টস
        $countBase = clone $query;
        $tabCounts = [
            'all'         => (clone $countBase)->count(),
            'pending'     => (clone $countBase)->where('product_reviews.status', 'pending')->count(),
            'approved'    => (clone $countBase)->where('product_reviews.status', 'approved')->where('product_reviews.is_visible', 1)->count(),
            'hidden'      => (clone $countBase)->where('product_reviews.is_visible', 0)->count(),
            'rejected'    => (clone $countBase)->where('product_reviews.status', 'rejected')->count(),
            'reported'    => (clone $countBase)->where('product_reviews.is_reported', 1)->count(),
            'with_images' => (clone $countBase)->whereNotNull('product_reviews.images_json')->where('product_reviews.images_json', '!=', '[]')->where('product_reviews.images_json', '!=', '')->count(),
            'pinned'      => (clone $countBase)->where('product_reviews.is_pinned', 1)->count(),
        ];

        // সর্টিং (Sorting)
        $sortBy = $request->input('sort_by', 'latest');
        switch ($sortBy) {
            case 'rating_high':
                $query->orderBy('product_reviews.rating', 'desc')->latest('product_reviews.id');
                break;
            case 'rating_low':
                $query->orderBy('product_reviews.rating', 'asc')->latest('product_reviews.id');
                break;
            case 'oldest':
                $query->orderBy('product_reviews.id', 'asc');
                break;
            case 'helpful':
                $query->orderBy('product_reviews.helpful_count', 'desc')->latest('product_reviews.id');
                break;
            case 'pinned_first':
                $query->orderBy('product_reviews.is_pinned', 'desc')
                      ->orderBy('product_reviews.pin_order', 'asc')
                      ->latest('product_reviews.id');
                break;
            case 'latest':
            default:
                $query->latest('product_reviews.id');
                break;
        }

        // এক্সেল / CSV এক্সপোর্ট
        if ($request->input('export') === 'csv' || $request->input('export') === 'excel') {
            return $this->exportReviewsToCsv($query);
        }

        // পেজিনেশন
        $perPageInput = $request->input('per_page', 20);
        $perPage = ($perPageInput === 'all') ? 100000 : max(5, (int)$perPageInput);

        $reviews = $query->paginate($perPage)->withQueryString();

        // ইমেজ পাথ ও ফরম্যাটিং হাইড্রেশন
        $reviews->getCollection()->transform(function ($rev) {
            $rev->images = !empty($rev->images_json) ? (json_decode($rev->images_json, true) ?: []) : [];
            $rev->product_image_url = !empty($rev->product_thumbnail) 
                ? (class_exists(ImagePathService::class) ? ImagePathService::resolveUrl($rev->product_thumbnail) : asset($rev->product_thumbnail)) 
                : 'https://placehold.co/100x100?text=Product';
            return $rev;
        });

        // AJAX রেসপন্স
        if ($request->ajax()) {
            $from = $reviews->firstItem() ?? 0;
            $to   = $reviews->lastItem() ?? 0;
            $total = $reviews->total();

            return response()->json([
                'status'          => 'success',
                'table_html'      => view('admin.reviews.partials.review_rows', compact('reviews'))->render(),
                'pagination_html' => ($perPageInput === 'all' || !$reviews->hasPages()) ? '' : $reviews->links('pagination::bootstrap-5')->render(),
                'total_count'     => $total,
                'entries_text'    => "Showing {$from} to {$to} of {$total} reviews",
                'counts'          => $tabCounts,
            ]);
        }

        $globalSettings = ProductReviewService::getGlobalSettings();

        return view('admin.reviews.index', compact('reviews', 'tabCounts', 'globalSettings', 'perPage'));
    }

    /**
     * ২. রিভিউ ড্যাশবোর্ড ও অ্যানালিটিক্স পরিসংখ্যান
     */
    public function dashboard(Request $request)
    {
        $summary = ProductReviewService::getGlobalDashboardSummary();

        // চার্ট ১: লাস্ট ৩০ দিনের রিভিউ সাবমিশন ট্রেন্ড
        $trendData = DB::table('product_reviews')
            ->select(DB::raw('DATE(created_at) as date_key'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', Carbon::now()->subDays(30)->startOfDay())
            ->groupBy('date_key')
            ->get()
            ->keyBy('date_key');

        $chartDates = [];
        $chartCounts = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = Carbon::now()->subDays($i)->format('Y-m-d');
            $chartDates[] = Carbon::now()->subDays($i)->format('d M');
            $chartCounts[] = isset($trendData[$d]) ? (int)$trendData[$d]->count : 0;
        }

        // টপ রেটেড ও মোস্ট রিভিউড প্রোডাক্ট
        $topProducts = DB::table('products')
            ->join('product_reviews', 'products.id', '=', 'product_reviews.product_id')
            ->where('product_reviews.status', 'approved')
            ->where('product_reviews.is_visible', 1)
            ->select(
                'products.id',
                'products.title',
                'products.thumbnail',
                DB::raw('COUNT(product_reviews.id) as total_reviews_count'),
                DB::raw('ROUND(AVG(product_reviews.rating), 1) as avg_rating')
            )
            ->groupBy('products.id', 'products.title', 'products.thumbnail')
            ->orderByDesc('total_reviews_count')
            ->take(5)
            ->get();

        // সাম্প্রতিক পেন্ডিং বা নতুন রিভিউ
        $recentReviews = DB::table('product_reviews')
            ->leftJoin('products', 'product_reviews.product_id', '=', 'products.id')
            ->leftJoin('users', 'product_reviews.user_id', '=', 'users.id')
            ->select(
                'product_reviews.*',
                'products.title as product_title',
                'users.name as customer_name'
            )
            ->latest('product_reviews.id')
            ->take(6)
            ->get();

        return view('admin.reviews.dashboard', compact('summary', 'chartDates', 'chartCounts', 'topProducts', 'recentReviews'));
    }

    /**
     * ৩. নির্দিষ্ট রিভিউর ফুল ডিটেইলস (AJAX Modal-এর জন্য)
     */
    public function show($id)
    {
        $review = DB::table('product_reviews')
            ->leftJoin('products', 'product_reviews.product_id', '=', 'products.id')
            ->leftJoin('users', 'product_reviews.user_id', '=', 'users.id')
            ->leftJoin('orders', 'product_reviews.order_id', '=', 'orders.id')
            ->where('product_reviews.id', $id)
            ->select(
                'product_reviews.*',
                'products.title as product_title',
                'products.thumbnail as product_thumbnail',
                'users.name as customer_name',
                'users.email as customer_email',
                'users.phone as customer_phone',
                'orders.invoice_no as order_invoice_no'
            )
            ->first();

        if (!$review) {
            return response()->json(['status' => 'error', 'message' => 'Review not found.'], 404);
        }

        $review->images = !empty($review->images_json) ? (json_decode($review->images_json, true) ?: []) : [];
        $review->replies = DB::table('product_review_replies')
            ->leftJoin('users', 'product_review_replies.admin_id', '=', 'users.id')
            ->where('product_review_replies.review_id', $id)
            ->select('product_review_replies.*', 'users.name as admin_name')
            ->latest('product_review_replies.id')
            ->get();

        return response()->json(['status' => 'success', 'review' => $review]);
    }

    /**
     * ৪. রিভিউ স্ট্যাটাস আপডেট (Approve, Reject, Pending)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,pending,rejected',
            'reason' => 'nullable|string|max:500',
        ]);

        $review = DB::table('product_reviews')->where('id', $id)->first();
        if (!$review) {
            return response()->json(['status' => 'error', 'message' => 'Review not found.'], 404);
        }

        $targetStatus = $request->status;

        DB::table('product_reviews')->where('id', $id)->update([
            'status'            => $targetStatus,
            'moderation_reason' => $request->reason ?: $review->moderation_reason,
            'moderated_by'      => Auth::id(),
            'moderated_at'      => now(),
            'updated_at'        => now(),
        ]);

        // যদি সদ্য Approved হয়, অটো-রিপ্লাই ট্রিগার নিশ্চিত করা
        if ($targetStatus === 'approved' && $review->status !== 'approved') {
            ProductReviewService::triggerAutoReply((int)$id, 'on_approved');
        }

        ProductReviewService::clearReviewCaches($review->product_id);

        if (class_exists(ActivityLogger::class)) {
            ActivityLogger::log('review_status_update', 'product_reviews', "Review #{$id} status updated to {$targetStatus}", [
                'record_id'  => $id,
                'old_values' => ['status' => $review->status],
                'new_values' => ['status' => $targetStatus, 'reason' => $request->reason],
            ]);
        }

        return response()->json([
            'status'     => 'success',
            'message'    => 'Review marked as ' . ucfirst($targetStatus) . ' successfully.',
            'new_status' => $targetStatus,
        ]);
    }

    /**
     * ৫. রিভিউ ভিজিবিলিটি টগল (Hide / Unhide)
     */
    public function toggleVisibility(Request $request, $id)
    {
        $review = DB::table('product_reviews')->where('id', $id)->first();
        if (!$review) {
            return response()->json(['status' => 'error', 'message' => 'Review not found.'], 404);
        }

        $newVisibility = $review->is_visible ? 0 : 1;

        DB::table('product_reviews')->where('id', $id)->update([
            'is_visible'   => $newVisibility,
            'moderated_by' => Auth::id(),
            'moderated_at' => now(),
            'updated_at'   => now(),
        ]);

        ProductReviewService::clearReviewCaches($review->product_id);

        if (class_exists(ActivityLogger::class)) {
            ActivityLogger::log('review_visibility_toggle', 'product_reviews', "Review #{$id} visibility changed to " . ($newVisibility ? 'Visible' : 'Hidden'), [
                'record_id'      => $id,
                'new_visibility' => $newVisibility,
            ]);
        }

        return response()->json([
            'status'        => 'success',
            'is_visible'    => $newVisibility,
            'message'       => $newVisibility ? 'Review is now visible to customers.' : 'Review has been hidden from public storefront.',
        ]);
    }

    /**
     * ৬. রিভিউ ডিলিট (ফাইল ও রিলেশনশিপ ক্লিনআপ সহ)
     */
    public function destroy($id)
    {
        $review = DB::table('product_reviews')->where('id', $id)->first();
        if (!$review) {
            return response()->json(['status' => 'error', 'message' => 'Review not found.'], 404);
        }

        // সংযুক্ত ছবিগুলো নিরাপদে ডিলিট করা
        if (!empty($review->images_json)) {
            $imgs = json_decode($review->images_json, true);
            if (is_array($imgs) && count($imgs) > 0) {
                ProductReviewService::deleteReviewImages($imgs);
            }
        }

        // রিপ্লাই ও রিপোর্ট হিস্ট্রি ক্লিনআপ
        if (Schema::hasTable('product_review_replies')) {
            DB::table('product_review_replies')->where('review_id', $id)->delete();
        }
        if (Schema::hasTable('product_review_reports')) {
            DB::table('product_review_reports')->where('review_id', $id)->delete();
        }

        DB::table('product_reviews')->where('id', $id)->delete();

        ProductReviewService::clearReviewCaches($review->product_id);

        if (class_exists(ActivityLogger::class)) {
            ActivityLogger::log('review_delete', 'product_reviews', "Deleted review #{$id} of Product #{$review->product_id}", [
                'record_id' => $id,
                'comment'   => $review->comment,
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Review permanently deleted.']);
    }

    /**
     * ৭. বাল্ক অ্যাকশন (Bulk Approve, Bulk Hide, Bulk Unhide, Bulk Reject, Bulk Delete)
     * বাল্ক অ্যাপ্রুভ করলেও যাতে অটো-রিপ্লাই কাজ করে তা যোগ করা হয়েছে
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,hide,unhide,reject,delete',
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer',
        ]);

        $ids = $request->ids;
        $action = $request->action;
        $affectedProducts = DB::table('product_reviews')->whereIn('id', $ids)->pluck('product_id')->unique();

        switch ($action) {
            case 'approve':
                // যেসব রিভিউ এখনো Approved হয়নি তাদের আগে ট্র্যাক করা
                $unapprovedIds = DB::table('product_reviews')
                    ->whereIn('id', $ids)
                    ->where('status', '!=', 'approved')
                    ->pluck('id');

                DB::table('product_reviews')->whereIn('id', $ids)->update([
                    'status'       => 'approved',
                    'moderated_by' => Auth::id(),
                    'moderated_at' => now(),
                    'updated_at'   => now(),
                ]);

                // বাল্ক অ্যাপ্রুভের পর অটো-রিপ্লাই ট্রিগার
                foreach ($unapprovedIds as $uId) {
                    ProductReviewService::triggerAutoReply((int)$uId, 'on_approved');
                }

                $msg = count($ids) . ' reviews approved successfully.';
                break;

            case 'hide':
                DB::table('product_reviews')->whereIn('id', $ids)->update([
                    'is_visible'   => 0,
                    'moderated_by' => Auth::id(),
                    'moderated_at' => now(),
                    'updated_at'   => now(),
                ]);
                $msg = count($ids) . ' reviews hidden from public display.';
                break;

            case 'unhide':
                DB::table('product_reviews')->whereIn('id', $ids)->update([
                    'is_visible'   => 1,
                    'moderated_by' => Auth::id(),
                    'moderated_at' => now(),
                    'updated_at'   => now(),
                ]);
                $msg = count($ids) . ' reviews restored to visible status.';
                break;

            case 'reject':
                DB::table('product_reviews')->whereIn('id', $ids)->update([
                    'status'       => 'rejected',
                    'moderated_by' => Auth::id(),
                    'moderated_at' => now(),
                    'updated_at'   => now(),
                ]);
                $msg = count($ids) . ' reviews marked as rejected.';
                break;

            case 'delete':
                $reviewsToDelete = DB::table('product_reviews')->whereIn('id', $ids)->get();
                foreach ($reviewsToDelete as $r) {
                    if (!empty($r->images_json)) {
                        $imgs = json_decode($r->images_json, true);
                        if (is_array($imgs)) {
                            ProductReviewService::deleteReviewImages($imgs);
                        }
                    }
                }
                if (Schema::hasTable('product_review_replies')) {
                    DB::table('product_review_replies')->whereIn('review_id', $ids)->delete();
                }
                if (Schema::hasTable('product_review_reports')) {
                    DB::table('product_review_reports')->whereIn('review_id', $ids)->delete();
                }
                DB::table('product_reviews')->whereIn('id', $ids)->delete();
                $msg = count($ids) . ' reviews permanently deleted.';
                break;
        }

        foreach ($affectedProducts as $pId) {
            ProductReviewService::clearReviewCaches($pId);
        }

        return response()->json(['status' => 'success', 'message' => $msg]);
    }

    /**
     * ৮. ইনস্ট্যান্ট অ্যাডমিন রিপ্লাই প্রদান ও এডিট
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'reply_text' => 'required|string|max:2000',
            'is_visible' => 'nullable|boolean',
        ]);

        $review = DB::table('product_reviews')->where('id', $id)->first();
        if (!$review) {
            return response()->json(['status' => 'error', 'message' => 'Review not found.'], 404);
        }

        $replyText = trim($request->reply_text);
        $isVisible = $request->boolean('is_visible', true);

        // মূল টেবিলে রিপ্লাই আপডেট
        DB::table('product_reviews')->where('id', $id)->update([
            'admin_reply'      => $replyText,
            'replied_by'       => Auth::id(),
            'replied_at'       => now(),
            'is_reply_visible' => $isVisible,
            'is_auto_replied'  => false,
            'updated_at'       => now(),
        ]);

        // হিস্ট্রি টেবিলে লগ রাখা
        if (Schema::hasTable('product_review_replies')) {
            DB::table('product_review_replies')->insert([
                'review_id'        => $id,
                'admin_id'         => Auth::id(),
                'reply_text'       => $replyText,
                'is_auto_reply'    => false,
                'is_visible'       => $isVisible,
                'notified_customer'=> false,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        ProductReviewService::clearReviewCaches($review->product_id);

        return response()->json([
            'status'     => 'success',
            'message'    => 'Admin reply posted successfully.',
            'reply_text' => $replyText,
            'replied_at' => date('d M Y, h:i A'),
            'admin_name' => Auth::user()->name ?? 'Admin',
        ]);
    }

    /**
     * ৯. অ্যাডমিন রিপ্লাই মুছে ফেলা
     */
    public function deleteReply($id)
    {
        $review = DB::table('product_reviews')->where('id', $id)->first();
        if (!$review) {
            return response()->json(['status' => 'error', 'message' => 'Review not found.'], 404);
        }

        DB::table('product_reviews')->where('id', $id)->update([
            'admin_reply'      => null,
            'replied_by'       => null,
            'replied_at'       => null,
            'is_reply_visible' => false,
            'is_auto_replied'  => false,
            'updated_at'       => now(),
        ]);

        ProductReviewService::clearReviewCaches($review->product_id);

        return response()->json(['status' => 'success', 'message' => 'Admin reply deleted.']);
    }

    /**
     * ১০. প্রোডাক্ট-ওয়াইজ টপ ৫ রিভিউ পিন / আনপিন কন্ট্রোল
     */
    public function togglePin(Request $request, $id)
    {
        $review = DB::table('product_reviews')->where('id', $id)->first();
        if (!$review) {
            return response()->json(['status' => 'error', 'message' => 'Review not found.'], 404);
        }

        if ($review->is_pinned) {
            // আনপিন করা
            DB::table('product_reviews')->where('id', $id)->update([
                'is_pinned'  => 0,
                'pin_order'  => 0,
                'updated_at' => now(),
            ]);
            $isPinned = false;
            $msg = 'Review unpinned successfully.';
        } else {
            // সর্বোচ্চ ৫টি পিন লিমিট চেক করা
            $currentPinnedCount = DB::table('product_reviews')
                ->where('product_id', $review->product_id)
                ->where('is_pinned', 1)
                ->count();

            if ($currentPinnedCount >= 5) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Maximum 5 reviews can be pinned for this product! Please unpin an existing review first.'
                ], 422);
            }

            $nextOrder = $currentPinnedCount + 1;
            DB::table('product_reviews')->where('id', $id)->update([
                'is_pinned'  => 1,
                'pin_order'  => $nextOrder,
                'updated_at' => now(),
            ]);
            $isPinned = true;
            $msg = 'Review pinned to top (Order: #' . $nextOrder . ').';
        }

        ProductReviewService::clearReviewCaches($review->product_id);

        return response()->json([
            'status'    => 'success',
            'is_pinned' => $isPinned,
            'message'   => $msg,
        ]);
    }

    /**
     * ১১. পিন করা রিভিউগুলোর ডিসপ্লে অর্ডার আপডেট
     */
    public function updatePinOrder(Request $request)
    {
        $request->validate([
            'ordered_ids'   => 'required|array',
            'ordered_ids.*' => 'integer',
        ]);

        foreach ($request->ordered_ids as $index => $rId) {
            DB::table('product_reviews')->where('id', $rId)->update([
                'pin_order'  => $index + 1,
                'updated_at' => now(),
            ]);
        }

        ProductReviewService::clearReviewCaches();

        return response()->json(['status' => 'success', 'message' => 'Pinned reviews reordered successfully.']);
    }

    /**
     * ১২. প্রোডাক্ট-ভিত্তিক রিভিউ ম্যানেজমেন্ট ও ওভাররাইড সেটিংস
     */
    public function productReviews(Request $request, $productId)
    {
        $product = DB::table('products')->where('id', $productId)->first();
        if (!$product) {
            return redirect()->route('admin.product.reviews.index')->with('error', 'Product not found.');
        }

        $ratingStats = ProductReviewService::getProductRatingSummary($productId);
        $settings = ProductReviewService::resolveProductSettings($productId);

        $pinnedReviews = DB::table('product_reviews')
            ->leftJoin('users', 'product_reviews.user_id', '=', 'users.id')
            ->where('product_reviews.product_id', $productId)
            ->where('product_reviews.is_pinned', 1)
            ->select('product_reviews.*', 'users.name as customer_name')
            ->orderBy('product_reviews.pin_order', 'asc')
            ->get();

        return view('admin.reviews.product_reviews', compact('product', 'ratingStats', 'settings', 'pinnedReviews'));
    }

    /**
     * ১৩. প্রোডাক্ট-ভিত্তিক আলাদা সেটিংস সেভ করা
     */
    public function saveProductSettings(Request $request, $productId)
    {
        $overrideData = [
            'reviews_enabled'       => $request->has('reviews_enabled') ? (int)$request->boolean('reviews_enabled') : null,
            'show_existing_reviews' => $request->boolean('show_existing_reviews', true),
            'image_reviews_enabled' => $request->has('image_reviews_enabled') ? (int)$request->boolean('image_reviews_enabled') : null,
            'approval_policy'       => $request->input('approval_policy', 'default'),
            'max_images'            => $request->filled('max_images') ? (int)$request->max_images : null,
            'max_image_size_kb'     => $request->filled('max_image_size_kb') ? (int)$request->max_image_size_kb : null,
            'per_page'              => $request->filled('per_page') ? (int)$request->per_page : null,
            'auto_reply_enabled'    => $request->has('auto_reply_enabled') ? (int)$request->boolean('auto_reply_enabled') : null,
            'updated_at'            => now(),
        ];

        DB::table('product_review_settings_overrides')->updateOrInsert(
            ['product_id' => $productId],
            $overrideData + ['created_at' => now()]
        );

        ProductReviewService::clearReviewCaches($productId);

        return back()->with('success', 'Product review configuration saved successfully.');
    }

    /**
     * ১৪. কাস্টমার-ভিত্তিক রিভিউ প্রোফাইল ও ইতিহাস
     */
    public function customerReviews(Request $request, $userId)
    {
        $customer = DB::table('users')->where('id', $userId)->first();
        if (!$customer) {
            return redirect()->route('admin.product.reviews.index')->with('error', 'Customer not found.');
        }

        $customerReviews = DB::table('product_reviews')
            ->leftJoin('products', 'product_reviews.product_id', '=', 'products.id')
            ->where('product_reviews.user_id', $userId)
            ->select(
                'product_reviews.*',
                'products.title as product_title',
                'products.thumbnail as product_thumbnail'
            )
            ->latest('product_reviews.id')
            ->paginate(15);

        $customerStats = [
            'total_reviews' => DB::table('product_reviews')->where('user_id', $userId)->count(),
            'avg_rating'    => round((float)DB::table('product_reviews')->where('user_id', $userId)->avg('rating'), 1),
            'approved'      => DB::table('product_reviews')->where('user_id', $userId)->where('status', 'approved')->count(),
            'rejected'      => DB::table('product_reviews')->where('user_id', $userId)->where('status', 'rejected')->count(),
        ];

        return view('admin.reviews.customer_reviews', compact('customer', 'customerReviews', 'customerStats'));
    }

    /**
     * ১৫. ফেসবুক স্টাইল অটো-রিপ্লাই রুলস ম্যানেজমেন্ট
     */
    public function autoRulesIndex()
    {
        $rules = DB::table('product_review_auto_rules')
            ->leftJoin('products', 'product_review_auto_rules.product_id', '=', 'products.id')
            ->select('product_review_auto_rules.*', 'products.title as product_title')
            ->latest('product_review_auto_rules.id')
            ->get();

        return view('admin.reviews.auto_rules', compact('rules'));
    }

    public function storeAutoRule(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'ratings'       => 'nullable|array',
            'keywords'      => 'nullable|string',
            'template_text' => 'required|string|max:2000',
            'trigger_event' => 'required|in:on_submission,on_approved,both',
        ]);

        $kwArray = null;
        if ($request->filled('keywords')) {
            $kwArray = array_values(array_filter(array_map('trim', explode(',', $request->keywords))));
        }

        $ruleData = [
            'name'                   => trim($request->name),
            'product_id'             => $request->filled('product_id') ? (int)$request->product_id : null,
            'ratings_json'           => $request->has('ratings') ? json_encode(array_map('intval', $request->ratings)) : null,
            'keywords_json'          => !empty($kwArray) ? json_encode($kwArray) : null,
            'verified_only'          => $request->boolean('verified_only', false),
            'trigger_event'          => $request->trigger_event,
            'delay_minutes'          => (int)$request->input('delay_minutes', 0),
            'require_admin_approval' => $request->boolean('require_admin_approval', false),
            'template_text'          => trim($request->template_text),
            'send_email'             => $request->boolean('send_email', false),
            'is_active'              => $request->boolean('is_active', true),
            'created_at'             => now(),
            'updated_at'             => now(),
        ];

        DB::table('product_review_auto_rules')->insert($ruleData);

        return back()->with('success', 'Auto reply rule created successfully.');
    }

    public function toggleAutoRule($id)
    {
        $rule = DB::table('product_review_auto_rules')->where('id', $id)->first();
        if ($rule) {
            DB::table('product_review_auto_rules')->where('id', $id)->update([
                'is_active'  => !$rule->is_active,
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Rule status updated.');
    }

    public function deleteAutoRule($id)
    {
        DB::table('product_review_auto_rules')->where('id', $id)->delete();
        return back()->with('success', 'Auto reply rule deleted.');
    }

    /**
     * ১৬. গ্লোবাল রিভিউ সেটিংস আপডেট (Frontend ও Backend সাথে ১০০% কার্যকর)
     */
    public function settingsIndex()
    {
        $settings = ProductReviewService::getGlobalSettings();
        return view('admin.reviews.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $showReviews   = $request->boolean('reviews_enabled') ? '1' : '0';
        $allowSubmit   = $request->boolean('allow_new_submission') ? '1' : '0';
        $verifiedOnly  = $request->boolean('verified_buyer_only') ? '1' : '0';
        $imageReviews  = $request->boolean('image_reviews_enabled') ? '1' : '0';
        $autoReplyVal  = $request->boolean('auto_reply_enabled') ? '1' : '0';

        $updates = [
            'detail_show_reviews'         => $showReviews,
            'detail_allow_review'         => $allowSubmit,
            'review_verified_only'        => $verifiedOnly,
            'review_image_enabled'        => $imageReviews,
            'image_reviews_enabled'       => $imageReviews,
            'review_max_images'           => (string)max(1, min(10, (int)$request->input('max_images_per_review', 4))),
            'review_max_image_size_kb'    => (string)max(512, min(10240, (int)$request->input('max_image_size_kb', 2048))),
            'review_approval_mode'        => $request->input('approval_mode', 'auto'),
            'review_req_approval_image'   => $request->boolean('require_approval_image') ? '1' : '0',
            'review_req_approval_first'   => $request->boolean('require_approval_first') ? '1' : '0',
            'review_auto_hide_reported'   => $request->boolean('auto_hide_reported') ? '1' : '0',
            'review_report_threshold'     => (string)max(1, (int)$request->input('report_threshold_hide', 3)),
            'review_auto_reply_enabled'   => $autoReplyVal,
            'auto_reply_enabled'          => $autoReplyVal,
            'review_email_admin'          => $request->boolean('email_notification_admin') ? '1' : '0',
            'review_notify_email'         => trim((string)$request->input('admin_notification_email', '')),
            'review_frontend_per_page'    => (string)max(2, min(50, (int)$request->input('frontend_per_page', 5))),
            'review_allow_voting'         => $request->boolean('allow_voting') ? '1' : '0',
            'review_allow_reporting'      => $request->boolean('allow_reporting') ? '1' : '0',
        ];

        foreach ($updates as $key => $val) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $val, 'updated_at' => now()]
            );
        }

        ProductReviewService::clearReviewCaches();

        return back()->with('success', 'Global product review settings updated successfully.');
    }

    /**
     * ১৭. রিভিউ এক্সেল / CSV এক্সপোর্ট হেল্পার
     */
    protected function exportReviewsToCsv($query)
    {
        $reviews = $query->get();
        $filename = "Product_Reviews_Export_" . date('Y_m_d_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Review ID',
            'Date',
            'Product ID',
            'Product Name',
            'SKU',
            'Customer Name',
            'Email',
            'Phone',
            'Verified Buyer',
            'Star Rating',
            'Review Title',
            'Review Comment',
            'Attached Images Count',
            'Status',
            'Visibility',
            'Admin Reply',
            'Helpful Votes',
            'Reports Count',
            'Pinned',
        ];

        $callback = function () use ($reviews, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            foreach ($reviews as $rev) {
                $imagesCount = 0;
                if (!empty($rev->images_json)) {
                    $decoded = json_decode($rev->images_json, true);
                    $imagesCount = is_array($decoded) ? count($decoded) : 0;
                }

                fputcsv($file, [
                    '#' . $rev->id,
                    date('d M Y, h:i A', strtotime($rev->created_at)),
                    '#' . $rev->product_id,
                    $rev->product_title ?? 'Product #' . $rev->product_id,
                    $rev->product_sku ?? ($rev->product_code ?? '—'),
                    $rev->customer_name ?? 'Guest User',
                    $rev->customer_email ?? '—',
                    $rev->customer_phone ?? '—',
                    $rev->is_verified_purchase ? 'YES' : 'NO',
                    $rev->rating . ' Stars',
                    $rev->title ?? '—',
                    $rev->comment,
                    $imagesCount,
                    strtoupper($rev->status),
                    $rev->is_visible ? 'VISIBLE' : 'HIDDEN',
                    $rev->admin_reply ?? 'No Reply',
                    $rev->helpful_count ?? 0,
                    $rev->report_count ?? 0,
                    $rev->is_pinned ? 'PINNED (#' . $rev->pin_order . ')' : 'NO',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}