<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProductReviewService
{
    private static bool $schemaCheckedInMemory = false;

    /**
     * cPanel ও Localhost-এ ডাটা অক্ষত রেখে স্কিমা ও কলাম নিশ্চিত করার স্বয়ংক্রিয় গার্ড
     * (Admin\ProductController-এর ensurePivotTables প্যাটার্ন অনুকরণে তৈরি)
     * Cache guard যোগ করা হয়েছে যাতে প্রতি পেজ লোডে ডাটাবেস লক ও ইনফিনিট লোডিং না হয়
     */
    public static function ensureSchema(): void
    {
        if (self::$schemaCheckedInMemory) {
            return;
        }
        self::$schemaCheckedInMemory = true;

        if (Cache::has('product_reviews_schema_ensured_v2')) {
            return;
        }

        try {
            // ১. মূল product_reviews টেবিলে প্রয়োজনীয় নতুন কলাম সংযোজন
            if (Schema::hasTable('product_reviews')) {
                Schema::table('product_reviews', function ($table) {
                    if (!Schema::hasColumn('product_reviews', 'order_id')) {
                        $table->unsignedBigInteger('order_id')->nullable()->index()->after('user_id');
                    }
                    if (!Schema::hasColumn('product_reviews', 'title')) {
                        $table->string('title')->nullable()->after('rating');
                    }
                    if (!Schema::hasColumn('product_reviews', 'images_json')) {
                        $table->text('images_json')->nullable()->after('comment');
                    }
                    if (!Schema::hasColumn('product_reviews', 'images_visible')) {
                        $table->boolean('images_visible')->default(true)->after('images_json');
                    }
                    if (!Schema::hasColumn('product_reviews', 'is_verified_purchase')) {
                        $table->boolean('is_verified_purchase')->default(true)->index()->after('images_visible');
                    }
                    if (!Schema::hasColumn('product_reviews', 'is_visible')) {
                        $table->boolean('is_visible')->default(true)->index()->after('status');
                    }
                    if (!Schema::hasColumn('product_reviews', 'is_pinned')) {
                        $table->boolean('is_pinned')->default(false)->index()->after('is_visible');
                    }
                    if (!Schema::hasColumn('product_reviews', 'pin_order')) {
                        $table->integer('pin_order')->default(0)->after('is_pinned');
                    }
                    if (!Schema::hasColumn('product_reviews', 'is_reported')) {
                        $table->boolean('is_reported')->default(false)->index()->after('pin_order');
                    }
                    if (!Schema::hasColumn('product_reviews', 'report_count')) {
                        $table->integer('report_count')->default(0)->after('is_reported');
                    }
                    if (!Schema::hasColumn('product_reviews', 'helpful_count')) {
                        $table->integer('helpful_count')->default(0)->after('report_count');
                    }
                    if (!Schema::hasColumn('product_reviews', 'is_suspicious')) {
                        $table->boolean('is_suspicious')->default(false)->after('helpful_count');
                    }
                    if (!Schema::hasColumn('product_reviews', 'moderation_reason')) {
                        $table->text('moderation_reason')->nullable()->after('is_suspicious');
                    }
                    if (!Schema::hasColumn('product_reviews', 'moderated_by')) {
                        $table->unsignedBigInteger('moderated_by')->nullable()->after('moderation_reason');
                    }
                    if (!Schema::hasColumn('product_reviews', 'moderated_at')) {
                        $table->timestamp('moderated_at')->nullable()->after('moderated_by');
                    }
                    if (!Schema::hasColumn('product_reviews', 'admin_reply')) {
                        $table->text('admin_reply')->nullable()->after('moderated_at');
                    }
                    if (!Schema::hasColumn('product_reviews', 'replied_by')) {
                        $table->unsignedBigInteger('replied_by')->nullable()->after('admin_reply');
                    }
                    if (!Schema::hasColumn('product_reviews', 'replied_at')) {
                        $table->timestamp('replied_at')->nullable()->after('replied_by');
                    }
                    if (!Schema::hasColumn('product_reviews', 'is_reply_visible')) {
                        $table->boolean('is_reply_visible')->default(true)->after('replied_at');
                    }
                    if (!Schema::hasColumn('product_reviews', 'is_auto_replied')) {
                        $table->boolean('is_auto_replied')->default(false)->after('is_reply_visible');
                    }
                });
            }

            // ২. রিপ্লাই হিস্ট্রি টেবিল
            if (!Schema::hasTable('product_review_replies')) {
                Schema::create('product_review_replies', function ($table) {
                    $table->id();
                    $table->unsignedBigInteger('review_id')->index();
                    $table->unsignedBigInteger('admin_id')->nullable();
                    $table->text('reply_text');
                    $table->boolean('is_auto_reply')->default(false);
                    $table->boolean('is_visible')->default(true);
                    $table->boolean('notified_customer')->default(false);
                    $table->timestamps();
                });
            }

            // ৩. রিপোর্ট ও হেল্পফুল ভোট ট্র্যাকিং টেবিল (ডুপ্লিকেট প্রতিরোধে)
            if (!Schema::hasTable('product_review_reports')) {
                Schema::create('product_review_reports', function ($table) {
                    $table->id();
                    $table->unsignedBigInteger('review_id')->index();
                    $table->unsignedBigInteger('user_id')->nullable()->index();
                    $table->string('ip_address', 45)->nullable();
                    $table->string('type', 20)->default('report')->index(); // 'report' বা 'helpful'
                    $table->text('reason')->nullable();
                    $table->timestamps();
                });
            }

            // ৪. অটো রিপ্লাই রুলস টেবিল
            if (!Schema::hasTable('product_review_auto_rules')) {
                Schema::create('product_review_auto_rules', function ($table) {
                    $table->id();
                    $table->string('name');
                    $table->unsignedBigInteger('product_id')->nullable()->index();
                    $table->text('ratings_json')->nullable(); // যেমন: [5, 4]
                    $table->text('keywords_json')->nullable();
                    $table->boolean('verified_only')->default(false);
                    $table->string('trigger_event', 30)->default('both'); // on_submission, on_approved, both
                    $table->integer('delay_minutes')->default(0);
                    $table->boolean('require_admin_approval')->default(false);
                    $table->text('template_text');
                    $table->boolean('send_email')->default(false);
                    $table->boolean('is_active')->default(true);
                    $table->timestamps();
                });

                self::seedDefaultAutoRules();
            }

            // ৫. প্রোডাক্ট-ভিত্তিক আলাদা রিভিউ সেটিংস ওভাররাইড টেবিল
            if (!Schema::hasTable('product_review_settings_overrides')) {
                Schema::create('product_review_settings_overrides', function ($table) {
                    $table->id();
                    $table->unsignedBigInteger('product_id')->unique();
                    $table->boolean('reviews_enabled')->nullable();
                    $table->boolean('show_existing_reviews')->default(true);
                    $table->boolean('image_reviews_enabled')->nullable();
                    $table->string('approval_policy', 30)->nullable(); // auto, manual, default
                    $table->integer('max_images')->nullable();
                    $table->integer('max_image_size_kb')->nullable();
                    $table->integer('per_page')->nullable();
                    $table->boolean('auto_reply_enabled')->nullable();
                    $table->timestamps();
                });
            }

            Cache::forever('product_reviews_schema_ensured_v2', true);
        } catch (\Throwable $e) {
            Log::error('ProductReviewService::ensureSchema Error: ' . $e->getMessage());
        }
    }

    /**
     * ডিফল্ট অটো রিপ্লাই রুলস সিডার
     */
    public static function seedDefaultAutoRules(): void
    {
        $defaultRules = [
            [
                'name'                   => '5-Star Appreciative Auto Reply',
                'ratings_json'           => json_encode([5]),
                'keywords_json'          => null,
                'verified_only'          => false,
                'trigger_event'          => 'both',
                'delay_minutes'          => 0,
                'require_admin_approval' => false,
                'template_text'          => 'প্রিয় {customer_name}, আপনার মূল্যবান ৫-স্টার পর্যালোচনার জন্য আন্তরিক ধন্যবাদ! {store_name}-এর সাথেই থাকুন।',
                'send_email'             => false,
                'is_active'              => true,
                'created_at'             => now(),
                'updated_at'             => now(),
            ],
            [
                'name'                   => '4-Star Thankful Auto Reply',
                'ratings_json'           => json_encode([4]),
                'keywords_json'          => null,
                'verified_only'          => false,
                'trigger_event'          => 'both',
                'delay_minutes'          => 0,
                'require_admin_approval' => false,
                'template_text'          => 'ধন্যবাদ {customer_name}! আপনার ইতিবাচক ফিডব্যাক আমাদের আরও উন্নত সেবা দিতে উৎসাহিত করবে।',
                'send_email'             => false,
                'is_active'              => true,
                'created_at'             => now(),
                'updated_at'             => now(),
            ],
            [
                'name'                   => '1-2 Star Empathy & Support Route',
                'ratings_json'           => json_encode([1, 2, 3]),
                'keywords_json'          => null,
                'verified_only'          => false,
                'trigger_event'          => 'both',
                'delay_minutes'          => 0,
                'require_admin_approval' => false,
                'template_text'          => 'প্রিয় {customer_name}, আপনার অসন্তুষ্টির জন্য আমরা আন্তরিকভাবে দুঃখিত। বিষয়টি দ্রুত সমাধানে অনুগ্রহ করে আমাদের হেল্পলাইনে ({support_contact}) যোগাযোগ করুন।',
                'send_email'             => false,
                'is_active'              => true,
                'created_at'             => now(),
                'updated_at'             => now(),
            ]
        ];

        DB::table('product_review_auto_rules')->insert($defaultRules);
    }

    /**
     * গ্লোবাল রিভিউ সেটিংস রিড করার মেথড (ডিফল্ট ফলব্যাক সহ)
     */
    public static function getGlobalSettings(): array
    {
        $settings = DB::table('settings')->pluck('value', 'key')->toArray();

        return [
            'reviews_enabled'          => ($settings['detail_show_reviews'] ?? '1') == '1',
            'allow_new_submission'     => ($settings['detail_allow_review'] ?? '1') == '1',
            'verified_buyer_only'      => ($settings['review_verified_only'] ?? '1') == '1',
            'image_reviews_enabled'    => ($settings['review_image_enabled'] ?? '1') == '1',
            'max_images_per_review'    => (int) ($settings['review_max_images'] ?? 4),
            'max_image_size_kb'        => (int) ($settings['review_max_image_size_kb'] ?? 2048),
            'allowed_image_mimes'      => ['jpeg', 'jpg', 'png', 'webp'],
            'approval_mode'            => $settings['review_approval_mode'] ?? 'auto', // 'auto', 'manual'
            'require_approval_image'   => ($settings['review_req_approval_image'] ?? '1') == '1',
            'require_approval_first'   => ($settings['review_req_approval_first'] ?? '0') == '1',
            'auto_hide_reported'       => ($settings['review_auto_hide_reported'] ?? '1') == '1',
            'report_threshold_hide'    => (int) ($settings['review_report_threshold'] ?? 3),
            'auto_reply_enabled'       => ($settings['review_auto_reply_enabled'] ?? ($settings['auto_reply_enabled'] ?? '1')) == '1',
            'email_notification_admin' => ($settings['review_email_admin'] ?? '1') == '1',
            'admin_notification_email' => $settings['review_notify_email'] ?? ($settings['footer_email'] ?? config('mail.from.address')),
            'frontend_per_page'        => (int) ($settings['review_frontend_per_page'] ?? 5),
            'allow_voting'             => ($settings['review_allow_voting'] ?? '1') == '1',
            'allow_reporting'          => ($settings['review_allow_reporting'] ?? '1') == '1',
        ];
    }

    /**
     * প্রোডাক্ট-ভিত্তিক কনফিগারেশন রেজলভার (প্রোডাক্ট ওভাররাইড থাকলে তা অগ্রাধিকার পাবে)
     */
    public static function resolveProductSettings(int $productId): array
    {
        $global = self::getGlobalSettings();

        if (!Schema::hasTable('product_review_settings_overrides')) {
            return $global;
        }

        $override = DB::table('product_review_settings_overrides')->where('product_id', $productId)->first();
        if (!$override) {
            return $global;
        }

        if (!is_null($override->reviews_enabled)) {
            $global['reviews_enabled'] = (bool) $override->reviews_enabled;
        }
        if (!is_null($override->image_reviews_enabled)) {
            $global['image_reviews_enabled'] = (bool) $override->image_reviews_enabled;
        }
        if (!empty($override->approval_policy) && $override->approval_policy !== 'default') {
            $global['approval_mode'] = $override->approval_policy;
        }
        if (!empty($override->max_images)) {
            $global['max_images_per_review'] = (int) $override->max_images;
        }
        if (!empty($override->max_image_size_kb)) {
            $global['max_image_size_kb'] = (int) $override->max_image_size_kb;
        }
        if (!empty($override->per_page)) {
            $global['frontend_per_page'] = (int) $override->per_page;
        }
        if (!is_null($override->auto_reply_enabled)) {
            $global['auto_reply_enabled'] = (bool) $override->auto_reply_enabled;
        }

        return $global;
    }

    /**
     * কাস্টমার প্রোডাক্টটি ডেলিভার্ড হিসেবে পেয়েছে কি না সার্ভার-সাইড যাচাই
     * (বিদ্যমান বিজনেস লজিক অক্ষত রাখা হয়েছে)
     */
    public static function verifyCustomerPurchase(int $userId, int $productId): array
    {
        $deliveredOrder = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.user_id', $userId)
            ->where('order_items.product_id', $productId)
            ->whereIn('orders.order_status', ['delivered', 'completed'])
            ->select('orders.id as order_id', 'orders.invoice_no', 'orders.created_at')
            ->latest('orders.id')
            ->first();

        if ($deliveredOrder) {
            return [
                'is_verified' => true,
                'order_id'    => $deliveredOrder->order_id,
                'invoice_no'  => $deliveredOrder->invoice_no,
            ];
        }

        return [
            'is_verified' => false,
            'order_id'    => null,
            'invoice_no'  => null,
        ];
    }

    /**
     * প্রোডাক্টের নির্ভুল রেটিং ক্যালকুলেটর (শুধুমাত্র Approved ও Visible রিভিউ গণনা হবে)
     */
    public static function getProductRatingSummary(int $productId): array
    {
        self::ensureSchema();

        $query = DB::table('product_reviews')
            ->where('product_id', $productId)
            ->where('status', 'approved');

        if (Schema::hasColumn('product_reviews', 'is_visible')) {
            $query->where('is_visible', 1);
        }

        $allApproved = $query->get(['id', 'rating', 'images_json', 'is_verified_purchase', 'is_pinned', 'pin_order']);
        $totalCount  = $allApproved->count();

        $avgScore = $totalCount > 0 ? round((float) $allApproved->avg('rating'), 1) : 0.0;

        // স্টার ব্রেকডাউন হিসাব (১ থেকে ৫ স্টার)
        $distribution = [
            5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0
        ];
        $percentages = [
            5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0
        ];

        foreach ($allApproved as $rev) {
            $r = (int) $rev->rating;
            if ($r >= 1 && $r <= 5) {
                $distribution[$r]++;
            }
        }

        if ($totalCount > 0) {
            foreach ($distribution as $star => $count) {
                $percentages[$star] = round(($count / $totalCount) * 100);
            }
        }

        $withImagesCount = $allApproved->filter(function ($item) {
            if (empty($item->images_json)) return false;
            $decoded = json_decode($item->images_json, true);
            return is_array($decoded) && count($decoded) > 0;
        })->count();

        $verifiedCount = $allApproved->where('is_verified_purchase', 1)->count();

        return [
            'total_reviews'     => $totalCount,
            'average_rating'    => $avgScore,
            'distribution'      => $distribution,
            'percentages'       => $percentages,
            'with_images_count' => $withImagesCount,
            'verified_count'    => $verifiedCount,
        ];
    }

    /**
     * ড্যাশবোর্ডের সার্বিক পরিসংখ্যান ও কাউন্টার সামারি
     */
    public static function getGlobalDashboardSummary(): array
    {
        self::ensureSchema();

        $allReviews = DB::table('product_reviews')->get();

        $totalReviews = $allReviews->count();
        $approvedReviews = $allReviews->where('status', 'approved');

        $avgRating = $approvedReviews->count() > 0 ? round((float)$approvedReviews->avg('rating'), 1) : 0.0;

        $starCounts = [
            5 => $allReviews->where('rating', 5)->count(),
            4 => $allReviews->where('rating', 4)->count(),
            3 => $allReviews->where('rating', 3)->count(),
            2 => $allReviews->where('rating', 2)->count(),
            1 => $allReviews->where('rating', 1)->count(),
        ];

        $withImagesCount = $allReviews->filter(function ($r) {
            if (empty($r->images_json)) return false;
            $arr = json_decode($r->images_json, true);
            return is_array($arr) && count($arr) > 0;
        })->count();

        $todayCount = DB::table('product_reviews')->whereDate('created_at', Carbon::today())->count();
        $unansweredCount = $allReviews->filter(fn($r) => empty($r->admin_reply))->count();

        return [
            'total_reviews'        => $totalReviews,
            'average_rating'       => $avgRating,
            'pending_count'        => $allReviews->where('status', 'pending')->count(),
            'approved_count'       => $allReviews->where('status', 'approved')->count(),
            'hidden_count'         => $allReviews->where('is_visible', 0)->count(),
            'rejected_count'       => $allReviews->where('status', 'rejected')->count(),
            'reported_count'       => $allReviews->where('is_reported', 1)->count(),
            'verified_count'       => $allReviews->where('is_verified_purchase', 1)->count(),
            'with_images_count'    => $withImagesCount,
            'today_count'          => $todayCount,
            'unanswered_count'     => $unansweredCount,
            'star_counts'          => $starCounts,
        ];
    }

    /**
     * নতুন রিভিউ সাবমিশনে Approval পলিসি প্রয়োগ
     */
    public static function determineInitialStatus(array $reviewData, array $settings): string
    {
        // ১. ম্যানুয়াল অ্যাপ্রুভাল পলিসি থাকলে সরাসরি pending
        if (($settings['approval_mode'] ?? 'auto') === 'manual') {
            return 'pending';
        }

        // ২. রিভিউর সাথে ছবি থাকলে এবং সেটি অ্যাপ্রুভাল শর্তাধীন হলে
        if (!empty($reviewData['has_images']) && !empty($settings['require_approval_image'])) {
            return 'pending';
        }

        // ৩. প্রথম রিভিউ সবসময় অ্যাপ্রুভাল শর্তাধীন হলে
        if (!empty($settings['require_approval_first'])) {
            $userPrevReviewCount = DB::table('product_reviews')->where('user_id', $reviewData['user_id'])->count();
            if ($userPrevReviewCount === 0) {
                return 'pending';
            }
        }

        // ৪. স্প্যাম বা সন্দেহজনক হলে
        if (!empty($reviewData['is_suspicious'])) {
            return 'pending';
        }

        return 'approved';
    }

    /**
     * স্প্যাম ও সন্দেহজনক শব্দ শনাক্তকারী ফিল্টার
     */
    public static function checkSuspiciousContent(string $text): bool
    {
        $badWords = [
            'http://', 'https://', 'www.', '.com', '.xyz', '.net', 'casino', 'betting', 
            'free money', 'loan', 'hack', 'whatsapp me', 'call me', 'scam'
        ];

        $lower = strtolower($text);
        foreach ($badWords as $bw) {
            if (str_contains($lower, $bw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * অটো রিভিউ রিপ্লাই ইঞ্জিন (Star Rating ও Keyword ভিত্তিক ডুপ্লিকেটলেস এক্সিকিউশন)
     */
    public static function triggerAutoReply(int $reviewId, string $event = 'on_submission'): bool
    {
        try {
            self::ensureSchema();

            // Table empty thakle automatic default rules seed hobe
            if (Schema::hasTable('product_review_auto_rules') && DB::table('product_review_auto_rules')->count() === 0) {
                self::seedDefaultAutoRules();
            }

            $review = DB::table('product_reviews')->where('id', $reviewId)->first();
            if (!$review) return false;

            // যদি ইতোমধ্যে কোনো রিপ্লাই থেকে থাকে, ওভাররাইট হবে না
            if (!empty($review->admin_reply)) {
                return false;
            }

            $settings = self::resolveProductSettings($review->product_id);
            if (empty($settings['auto_reply_enabled'])) {
                return false;
            }

            $user = DB::table('users')->where('id', $review->user_id)->first();
            $product = DB::table('products')->where('id', $review->product_id)->first();

            $rating = (int) $review->rating;
            $commentLower = strtolower($review->comment ?? '');

            // প্রযোজ্য অ্যাক্টিভ রুলস খোঁজা
            $rules = DB::table('product_review_auto_rules')
                ->where('is_active', true)
                ->where(function ($q) use ($review) {
                    $q->whereNull('product_id')->orWhere('product_id', $review->product_id);
                })
                ->orderByRaw('product_id IS NOT NULL DESC')
                ->get();

            $matchedRule = null;

            foreach ($rules as $rule) {
                if ($rule->trigger_event !== $event && $rule->trigger_event !== 'both') {
                    continue;
                }

                if ($rule->verified_only && empty($review->is_verified_purchase)) {
                    continue;
                }

                $matchedRating = true;
                if (!empty($rule->ratings_json)) {
                    $arr = json_decode($rule->ratings_json, true);
                    if (is_array($arr)) {
                        $arrInts = array_map('intval', $arr);
                        if (!in_array($rating, $arrInts, true)) {
                            $matchedRating = false;
                        }
                    }
                }

                $matchedKeyword = true;
                if (!empty($rule->keywords_json)) {
                    $matchedKeyword = false;
                    $kwList = json_decode($rule->keywords_json, true);
                    if (is_array($kwList) && count($kwList) > 0) {
                        foreach ($kwList as $kw) {
                            if (str_contains($commentLower, strtolower(trim($kw)))) {
                                $matchedKeyword = true;
                                break;
                            }
                        }
                    } else {
                        $matchedKeyword = true;
                    }
                }

                if ($matchedRating && $matchedKeyword) {
                    $matchedRule = $rule;
                    break;
                }
            }

            if (!$matchedRule) {
                return false;
            }

            if ($matchedRule->require_admin_approval) {
                DB::table('product_review_replies')->insert([
                    'review_id'        => $reviewId,
                    'admin_id'         => null,
                    'reply_text'       => self::parseTemplateVariables($matchedRule->template_text, $user, $product, $review),
                    'is_auto_reply'    => true,
                    'is_visible'       => false,
                    'notified_customer'=> false,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
                return true;
            }

            $finalReplyText = self::parseTemplateVariables($matchedRule->template_text, $user, $product, $review);

            DB::table('product_reviews')->where('id', $reviewId)->update([
                'admin_reply'      => $finalReplyText,
                'replied_by'       => null,
                'replied_at'       => now(),
                'is_reply_visible' => true,
                'is_auto_replied'  => true,
                'updated_at'       => now(),
            ]);

            DB::table('product_review_replies')->insert([
                'review_id'        => $reviewId,
                'admin_id'         => null,
                'reply_text'       => $finalReplyText,
                'is_auto_reply'    => true,
                'is_visible'       => true,
                'notified_customer'=> (bool) $matchedRule->send_email,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            self::clearReviewCaches($review->product_id);
            return true;

        } catch (\Throwable $e) {
            Log::error('Auto reply dispatch error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * অটো রিপ্লাইয়ের ভেরিয়েবল পার্সার
     */
    protected static function parseTemplateVariables(string $tpl, $user, $product, $review): string
    {
        $globalSettings = DB::table('settings')->pluck('value', 'key');
        $storeName = $globalSettings['footer_brand_title'] ?? ($globalSettings['site_title'] ?? config('app.name', 'Mayra'));
        $supportContact = $globalSettings['footer_phone'] ?? ($globalSettings['company_phone'] ?? '+880 1774-937404');

        $replacements = [
            '{customer_name}'  => $user->name ?? 'গ্রাহক',
            '{customer_first}' => explode(' ', trim($user->name ?? 'গ্রাহক'))[0],
            '{product_name}'   => $product->title ?? 'পণ্য',
            '{rating}'         => $review->rating ?? 5,
            '{store_name}'     => $storeName,
            '{support_contact}'=> $supportContact,
            '{review_date}'    => date('d M, Y', strtotime($review->created_at ?? now())),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $tpl);
    }

    /**
     * নতুন রিভিউ আসার পর অ্যাডমিনকে ইমেইল অ্যালার্ট পাঠানো
     */
    public static function notifyAdminNewReview(int $reviewId): void
    {
        try {
            $settings = self::getGlobalSettings();
            if (empty($settings['email_notification_admin']) || empty($settings['admin_notification_email'])) {
                return;
            }

            $review = DB::table('product_reviews')->where('id', $reviewId)->first();
            if (!$review) return;

            $user = DB::table('users')->where('id', $review->user_id)->first();
            $product = DB::table('products')->where('id', $review->product_id)->first();

            $recipientEmail = trim($settings['admin_notification_email']);
            if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                return;
            }

            $mailData = [
                'review_id'     => $review->id,
                'product_title' => $product->title ?? ('Product #' . $review->product_id),
                'customer_name' => $user->name ?? 'Customer',
                'rating'        => $review->rating,
                'comment'       => $review->comment,
                'status'        => $review->status,
                'date'          => date('d M, Y h:i A', strtotime($review->created_at)),
                'admin_url'     => route('admin.product.reviews.index'),
            ];

            Mail::send([], [], function ($message) use ($mailData, $recipientEmail) {
                $message->to($recipientEmail)
                    ->subject("⭐ New Product Review: [{$mailData['rating']} Stars] - {$mailData['product_title']}")
                    ->html("
                        <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #1e293b; max-width: 600px; margin: auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 10px; background: #ffffff;'>
                            <h3 style='color: #0f172a; margin-top: 0;'>New Product Review Received</h3>
                            <p><strong>Product:</strong> {$mailData['product_title']}</p>
                            <p><strong>Customer:</strong> {$mailData['customer_name']}</p>
                            <p><strong>Rating:</strong> ⭐ {$mailData['rating']} / 5</p>
                            <p><strong>Status:</strong> <span style='text-transform: uppercase; font-weight: bold; color: #2563eb;'>{$mailData['status']}</span></p>
                            <div style='background: #f8fafc; border-left: 4px solid #3b82f6; padding: 12px; margin: 16px 0; border-radius: 4px;'>
                                <em>\"{$mailData['comment']}\"</em>
                            </div>
                            <p style='font-size: 12px; color: #64748b;'>Submitted on: {$mailData['date']}</p>
                            <div style='margin-top: 24px;'>
                                <a href='{$mailData['admin_url']}' style='background: #2563eb; color: #ffffff; padding: 10px 18px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;'>Moderate Review in Admin</a>
                            </div>
                        </div>
                    ");
            });
        } catch (\Throwable $e) {
            Log::error('Admin review email notification failed: ' . $e->getMessage());
        }
    }

    /**
     * ইউনিভার্সাল রিভিউ ইমেজ আপলোডার
     * (Admin\ProductController-এর uploadProductImage অনুযায়ী লাইভ ও লোকালে ইমেজ সুরক্ষিতভাবে সংরক্ষণ)
     */
    public static function uploadReviewImages(array $uploadedFiles, int $productId): array
    {
        $savedPaths = [];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        $primaryRoot = (!empty($_SERVER['DOCUMENT_ROOT']) && is_dir($_SERVER['DOCUMENT_ROOT']))
            ? rtrim($_SERVER['DOCUMENT_ROOT'], '/\\')
            : rtrim(public_path(), '/\\');

        $relativeDir = 'uploads/reviews/' . $productId;
        $primaryDir = $primaryRoot . '/' . $relativeDir;

        if (!is_dir($primaryDir)) {
            @mkdir($primaryDir, 0777, true);
        }

        $candidateRoots = array_unique(array_filter([
            rtrim(public_path(), '/\\'),
            is_dir(base_path('../public_html')) ? rtrim(base_path('../public_html'), '/\\') : null,
        ]));

        foreach ($uploadedFiles as $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                continue;
            }

            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            if (!in_array($ext, $allowedExtensions, true)) {
                continue; // SVG বা বিপজ্জনক ফাইল বাদ দেওয়া হবে
            }

            $filename = time() . '_' . uniqid() . '_' . Str::random(6) . '.' . $ext;
            $primaryFullPath = $primaryDir . '/' . $filename;
            $relativePath = $relativeDir . '/' . $filename;

            $file->move($primaryDir, $filename);

            // cPanel public_html এবং অন্যান্য পাবলিক রুটে মিররিং
            foreach ($candidateRoots as $root) {
                $mirrorDir = $root . '/' . $relativeDir;
                $mirrorFile = $mirrorDir . '/' . $filename;
                if ($mirrorFile !== $primaryFullPath) {
                    if (!is_dir($mirrorDir)) {
                        @mkdir($mirrorDir, 0777, true);
                    }
                    @copy($primaryFullPath, $mirrorFile);
                }
            }

            // Storage ফোল্ডারে ব্যাকআপ কপি
            try {
                $storageDir = storage_path('app/public/reviews/' . $productId);
                if (!is_dir($storageDir)) {
                    @mkdir($storageDir, 0777, true);
                }
                @copy($primaryFullPath, $storageDir . '/' . $filename);
            } catch (\Throwable $e) {}

            $savedPaths[] = $relativePath;
        }

        return $savedPaths;
    }

    /**
     * রিভিউ ইমেজ ডিলিট হেল্পার
     */
    public static function deleteReviewImages(array $imagePaths): void
    {
        $roots = array_unique(array_filter([
            (!empty($_SERVER['DOCUMENT_ROOT']) && is_dir($_SERVER['DOCUMENT_ROOT'])) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') : null,
            rtrim(public_path(), '/\\'),
            is_dir(base_path('../public_html')) ? rtrim(base_path('../public_html'), '/\\') : null,
        ]));

        foreach ($imagePaths as $path) {
            if (empty($path) || filter_var($path, FILTER_VALIDATE_URL)) continue;
            $clean = ltrim($path, '/');

            foreach ($roots as $root) {
                $file = $root . '/' . $clean;
                if (is_file($file)) {
                    @unlink($file);
                }
            }

            $storageFile = storage_path('app/public/' . str_replace('uploads/', '', $clean));
            if (is_file($storageFile)) {
                @unlink($storageFile);
            }
        }
    }

    /**
     * রিভিউ ক্যাশ ক্লিয়ার হেল্পার (Frontend Product Details-এর সিঙ্ক্রোনাইজেশনের জন্য)
     */
    public static function clearReviewCaches(?int $productId = null): void
    {
        try {
            Cache::forget('product_detail_theme_settings_cache');

            if ($productId) {
                Cache::forget('product_reviews_pdp_' . $productId);
                Cache::forget('product_rating_summary_' . $productId);
            }
        } catch (\Throwable $e) {
            Log::error('Review Cache Invalidation Error: ' . $e->getMessage());
        }
    }
}