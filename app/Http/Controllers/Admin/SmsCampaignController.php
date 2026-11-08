<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessBulkSmsCampaignJob;
use App\Models\Order;
use App\Models\SmsBlacklist;
use App\Models\SmsCampaign;
use App\Models\SmsCampaignRecipient;
use App\Models\SmsProvider;
use App\Models\SmsTemplate;
use App\Models\User;
use App\Services\Sms\Drivers\OnecodesoftDriver;
use App\Services\Sms\SmsService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class SmsCampaignController extends Controller
{
    /**
     * Strict Bangladeshi mobile & IP telephony phone number validator
     * Valid prefixes: 013, 014, 015, 016, 017, 018, 019, 096 (with or without +88 / 88)
     */
    public static function validateAndFormatBdPhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $clean = preg_replace('/[^0-9]/', '', (string) $phone);

        if (str_starts_with($clean, '00880')) {
            $clean = substr($clean, 2);
        }

        if (strlen($clean) === 13 && str_starts_with($clean, '880')) {
            $local = substr($clean, 2);
            if (preg_match('/^(01[3-9]\d{8}|096\d{8})$/', $local)) {
                return '88' . $local;
            }
        }

        if (strlen($clean) === 11 && preg_match('/^(01[3-9]\d{8}|096\d{8})$/', $clean)) {
            return '88' . $clean;
        }

        if (strlen($clean) === 10 && preg_match('/^(1[3-9]\d{8}|96\d{8})$/', $clean)) {
            return '880' . $clean;
        }

        if (strlen($clean) === 12 && str_starts_with($clean, '88')) {
            $without88 = substr($clean, 2);
            if (preg_match('/^(1[3-9]\d{8})$/', $without88)) {
                return '880' . $without88;
            }
        }

        return null;
    }

    /**
     * Safely extract phone number from an order record in PHP
     */
    protected function extractOrderPhone(object $order): ?string
    {
        foreach (['shipping_phone', 'customer_phone', 'phone', 'billing_phone', 'mobile'] as $col) {
            if (isset($order->{$col}) && trim((string) $order->{$col}) !== '') {
                return trim((string) $order->{$col});
            }
        }
        return null;
    }

    /**
     * Safely extract customer name from an order record in PHP
     */
    protected function extractOrderName(object $order): string
    {
        foreach (['shipping_name', 'customer_name', 'name', 'billing_name'] as $col) {
            if (isset($order->{$col}) && trim((string) $order->{$col}) !== '') {
                return trim((string) $order->{$col});
            }
        }
        return 'Customer';
    }

    /**
     * Safely extract phone number from a user record in PHP
     */
    protected function extractUserPhone(object $user): ?string
    {
        foreach (['phone', 'phone_number', 'mobile'] as $col) {
            if (isset($user->{$col}) && trim((string) $user->{$col}) !== '') {
                return trim((string) $user->{$col});
            }
        }
        return null;
    }

    /**
     * Safe blacklist checker
     */
    protected function isPhoneBlacklisted(string $phone): bool
    {
        try {
            if (class_exists(SmsBlacklist::class)) {
                if (method_exists(SmsBlacklist::class, 'isPhoneBlocked')) {
                    return (bool) SmsBlacklist::isPhoneBlocked($phone);
                }
                if (Schema::hasTable('sms_blacklists')) {
                    return DB::table('sms_blacklists')
                        ->where(function ($q) use ($phone) {
                            foreach (['phone_number', 'phone', 'number'] as $col) {
                                if (Schema::hasColumn('sms_blacklists', $col)) {
                                    $q->orWhere($col, $phone);
                                }
                            }
                        })
                        ->where('is_active', true)
                        ->exists();
                }
            }
        } catch (Throwable $e) {
            return false;
        }
        return false;
    }

    /**
     * Ensure database columns never truncate strings when strict mode is active
     */
    protected function ensureCampaignSchemaCompatibility(): void
    {
        try {
            if (Schema::hasTable('sms_campaigns')) {
                $statusCol = DB::select("SHOW COLUMNS FROM `sms_campaigns` LIKE 'status'");
                if (!empty($statusCol) && str_starts_with(strtolower((string) ($statusCol[0]->Type ?? '')), 'enum(')) {
                    DB::statement("ALTER TABLE `sms_campaigns` MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'draft'");
                }

                $channelCol = DB::select("SHOW COLUMNS FROM `sms_campaigns` LIKE 'channel'");
                if (!empty($channelCol) && str_starts_with(strtolower((string) ($channelCol[0]->Type ?? '')), 'enum(')) {
                    DB::statement("ALTER TABLE `sms_campaigns` MODIFY COLUMN `channel` VARCHAR(50) NULL DEFAULT 'sms'");
                }
            }
        } catch (Throwable $e) {
            // Silently fall back if ALTER is restricted
        }
    }

    /**
     * Helper to dynamically resolve allowed status values
     */
    protected function resolveCampaignStatusValue(string $targetStatus): string
    {
        if (!Schema::hasTable('sms_campaigns') || !Schema::hasColumn('sms_campaigns', 'status')) {
            return $targetStatus;
        }

        try {
            $colInfo = DB::select("SHOW COLUMNS FROM `sms_campaigns` LIKE 'status'");
            if (!empty($colInfo)) {
                $columnData = (object) $colInfo[0];
                $typeStr = strtolower((string) ($columnData->Type ?? ''));

                if (str_starts_with($typeStr, 'enum(')) {
                    preg_match_all("/'([^']+)'/", (string) ($columnData->Type ?? ''), $matches);
                    $allowed = $matches[1] ?? [];

                    foreach ($allowed as $val) {
                        if (strtolower($val) === strtolower($targetStatus)) {
                            return $val;
                        }
                    }

                    if (in_array(strtolower($targetStatus), ['queued', 'queue', 'running', 'processing'])) {
                        foreach (['running', 'pending', 'processing', 'draft'] as $synonym) {
                            foreach ($allowed as $val) {
                                if (strtolower($val) === $synonym) {
                                    return $val;
                                }
                            }
                        }
                    }

                    $defaultVal = $columnData->Default ?? null;
                    if (!empty($defaultVal)) {
                        return (string) $defaultVal;
                    }

                    if (!empty($allowed)) {
                        return $allowed[0];
                    }
                }
            }
        } catch (Throwable $e) {
            Log::warning('resolveCampaignStatusValue exception: ' . $e->getMessage());
        }

        return $targetStatus;
    }

    /**
     * Helper to dynamically resolve allowed channel values
     */
    protected function resolveCampaignChannelValue(): ?string
    {
        if (!Schema::hasTable('sms_campaigns') || !Schema::hasColumn('sms_campaigns', 'channel')) {
            return null;
        }

        try {
            $colInfo = DB::select("SHOW COLUMNS FROM `sms_campaigns` LIKE 'channel'");
            if (!empty($colInfo)) {
                $columnData = (object) $colInfo[0];
                $typeStr = strtolower((string) ($columnData->Type ?? ''));

                if (str_starts_with($typeStr, 'enum(')) {
                    preg_match_all("/'([^']+)'/", (string) ($columnData->Type ?? ''), $matches);
                    $allowed = $matches[1] ?? [];

                    foreach ($allowed as $val) {
                        if (strtolower($val) === 'sms') {
                            return $val;
                        }
                    }

                    $defaultVal = $columnData->Default ?? null;
                    if (!empty($defaultVal)) {
                        return (string) $defaultVal;
                    }

                    foreach (['promotional', 'marketing', 'broadcast', 'bulk', 'general'] as $candidate) {
                        foreach ($allowed as $val) {
                            if (strtolower($val) === $candidate) {
                                return $val;
                            }
                        }
                    }

                    if (!empty($allowed)) {
                        return $allowed[0];
                    }

                    return null;
                }

                if (str_contains($typeStr, 'int')) {
                    return '1';
                }

                return 'sms';
            }
        } catch (Throwable $e) {
            Log::warning('resolveCampaignChannelValue exception: ' . $e->getMessage());
        }

        return 'sms';
    }

    /**
     * Core synchronous campaign sender (Sends pending recipients & syncs live gateway balance without needing terminal)
     */
    protected function executeCampaignDispatch(SmsCampaign $campaign, SmsService $smsService): int
    {
        @set_time_limit(300);

        $provider = SmsProvider::find($campaign->sms_provider_id ?? $campaign->provider_id)
            ?? SmsProvider::where('is_active', true)->first();

        $campaignFk = Schema::hasColumn('sms_campaign_recipients', 'sms_campaign_id') ? 'sms_campaign_id' : 'campaign_id';
        $statusCol  = Schema::hasColumn('sms_campaign_recipients', 'sms_status') ? 'sms_status' : 'status';

        $recipients = DB::table('sms_campaign_recipients')
            ->where($campaignFk, $campaign->id)
            ->where($statusCol, 'pending')
            ->get();

        if ($recipients->isEmpty()) {
            if (in_array($campaign->status, ['running', 'queued', 'scheduled', 'pending'])) {
                $completedStatus = $this->resolveCampaignStatusValue('completed');
                $campaign->update(['status' => $completedStatus, 'updated_at' => now()]);
            }
            return 0;
        }

        $sentCount = 0;
        $failedCount = 0;

        foreach ($recipients as $recipient) {
            $res = $smsService->send(
                $recipient->phone_number,
                $campaign->sms_message,
                'marketing',
                [
                    'sender_id'      => $campaign->sender_id,
                    'campaign_id'    => $campaign->id,
                    'user_id'        => $recipient->customer_id ?? null,
                    'skip_live_sync' => true,
                ]
            );

            $updateRow = [
                'processed_at' => now(),
                'updated_at'   => now(),
            ];

            if (!empty($res['status'])) {
                $updateRow[$statusCol] = 'delivered';
                $sentCount++;
            } else {
                $updateRow[$statusCol] = 'failed';
                $updateRow['failure_reason'] = $res['message'] ?? 'Gateway rejected';
                $failedCount++;
            }

            DB::table('sms_campaign_recipients')->where('id', $recipient->id)->update($updateRow);
        }

        // Sync live balance from Onecodesoft gateway after batch completes
        if ($provider) {
            try {
                $smsService->syncBalance();
            } catch (Throwable $e) {
                Log::warning('Live balance sync failed: ' . $e->getMessage());
            }
        }

        // Recalculate totals accurately from DB
        $totalDelivered = DB::table('sms_campaign_recipients')
            ->where($campaignFk, $campaign->id)
            ->whereIn($statusCol, ['delivered', 'sent'])
            ->count();

        $totalFailed = DB::table('sms_campaign_recipients')
            ->where($campaignFk, $campaign->id)
            ->where($statusCol, 'failed')
            ->count();

        $completedStatus = $this->resolveCampaignStatusValue('completed');
        $campaignUpdate = [
            'status'     => $completedStatus,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('sms_campaigns', 'successful_count')) {
            $campaignUpdate['successful_count'] = $totalDelivered;
        }
        if (Schema::hasColumn('sms_campaigns', 'sent_count')) {
            $campaignUpdate['sent_count'] = $totalDelivered;
        }
        if (Schema::hasColumn('sms_campaigns', 'failed_count')) {
            $campaignUpdate['failed_count'] = $totalFailed;
        }

        $campaign->update($campaignUpdate);

        return $sentCount + $failedCount;
    }

    /**
     * TERMINAL-FREE AUTO SCHEDULER & QUEUE PROCESSOR
     * Automatically dispatches any due scheduled campaigns or stuck running campaigns
     */
    protected function processDueCampaigns(SmsService $smsService): int
    {
        $dispatchedCampaigns = 0;

        try {
            $dueCampaigns = SmsCampaign::where(function ($q) {
                // 1. Scheduled campaigns whose trigger time has arrived
                $q->where(function ($sub) {
                    $sub->where('status', 'scheduled')
                        ->whereNotNull('scheduled_at')
                        ->where('scheduled_at', '<=', now());
                })
                // 2. Immediate campaigns stuck in running/queued/pending state
                ->orWhereIn('status', ['running', 'queued', 'pending']);
            })->get();

            foreach ($dueCampaigns as $campaign) {
                $processed = $this->executeCampaignDispatch($campaign, $smsService);
                if ($processed > 0) {
                    $dispatchedCampaigns++;
                }
            }
        } catch (Throwable $e) {
            Log::warning('processDueCampaigns warning: ' . $e->getMessage());
        }

        return $dispatchedCampaigns;
    }

    /**
     * Web Cron Endpoint (Can be called via cPanel Cron or AJAX without Terminal)
     */
    public function cronRunScheduled(SmsService $smsService): JsonResponse
    {
        $count = $this->processDueCampaigns($smsService);

        return response()->json([
            'success'              => true,
            'dispatched_campaigns' => $count,
            'timestamp'            => now()->toDateTimeString(),
        ]);
    }

    /**
     * Display a paginated listing of SMS campaigns (Auto-processes due/stuck campaigns on load).
     */
    public function index(Request $request, SmsService $smsService): View
    {
        // Auto-dispatch any due scheduled or stuck running campaigns
        $this->processDueCampaigns($smsService);

        $query = SmsCampaign::with('provider')->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where('name', 'like', "%{$search}%");
        }

        $campaigns = $query->paginate(15)->withQueryString();

        $stats = [
            'total'     => SmsCampaign::count(),
            'running'   => SmsCampaign::where('status', 'running')->count(),
            'scheduled' => SmsCampaign::where('status', 'scheduled')->count(),
            'completed' => SmsCampaign::where('status', 'completed')->count(),
        ];

        return view('admin.sms.campaigns.index', compact('campaigns', 'stats'));
    }

    /**
     * Show the form for creating a new SMS campaign.
     */
    public function create(SmsService $smsService): View
    {
        // Auto-dispatch any due scheduled campaigns in background
        $this->processDueCampaigns($smsService);

        $providers = SmsProvider::where('is_active', true)->get();
        $templates = SmsTemplate::where('is_active', true)
            ->whereIn('event_type', ['marketing', 'general'])
            ->get();

        $categories = Schema::hasTable('categories')
            ? DB::table('categories')->select('id', 'name')->orderBy('name', 'asc')->get()
            : collect();

        $products = Schema::hasTable('products')
            ? DB::table('products')->select('id', 'title', 'product_code')->orderBy('title', 'asc')->limit(300)->get()
            : collect();

        $validOrderMap  = [];
        $validRecentMap = [];
        $validRegMap    = [];
        $validStaffMap  = [];

        // 1. Scan Orders Table Safely in PHP
        if (Schema::hasTable('orders')) {
            $allOrders = DB::table('orders')->get();
            $sixtyDaysAgo = now()->subDays(60)->startOfDay();

            foreach ($allOrders as $ord) {
                $rawPhone = $this->extractOrderPhone($ord);
                $clean = self::validateAndFormatBdPhone($rawPhone);
                if ($clean && !$this->isPhoneBlacklisted($clean)) {
                    $validOrderMap[$clean] = true;

                    if (!empty($ord->created_at)) {
                        try {
                            if (Carbon::parse($ord->created_at)->gte($sixtyDaysAgo)) {
                                $validRecentMap[$clean] = true;
                            }
                        } catch (Throwable $e) {}
                    }
                }
            }
        }

        // 2. Scan Users Table Safely in PHP
        if (Schema::hasTable('users')) {
            $allUsers = DB::table('users')->get();
            $staffRoles = ['admin', 'super_admin', 'staff', 'branch_manager', 'manager'];

            foreach ($allUsers as $u) {
                if (property_exists($u, 'deleted_at') && !empty($u->deleted_at)) {
                    continue;
                }

                $rawPhone = $this->extractUserPhone($u);
                $clean = self::validateAndFormatBdPhone($rawPhone);
                if (!$clean || $this->isPhoneBlacklisted($clean)) {
                    continue;
                }

                $role = strtolower(trim((string) ($u->role ?? '')));
                $isSuper = !empty($u->is_super_admin);

                if ($isSuper || in_array($role, $staffRoles, true)) {
                    $validStaffMap[$clean] = true;
                } else {
                    $validRegMap[$clean] = true;
                }
            }
        }

        $allEligibleCount = count(array_unique(array_merge(
            array_keys($validOrderMap),
            array_keys($validRegMap)
        )));

        $audienceCounts = [
            'order_customers'      => count($validOrderMap),
            'recent_buyers'        => count($validRecentMap),
            'product_buyers'       => count($validOrderMap),
            'category_buyers'      => count($validOrderMap),
            'registered_customers' => count($validRegMap),
            'staff_users'          => count($validStaffMap),
            'all_eligible'         => $allEligibleCount,
        ];

        return view('admin.sms.campaigns.create', compact('providers', 'templates', 'categories', 'products', 'audienceCounts'));
    }

    /**
     * Resolve matching order IDs for a specific product or category safely
     */
    protected function resolveOrderIdsByProductOrCategory(string $audienceType, $productId, $categoryId): ?array
    {
        $itemsTable = null;
        if (Schema::hasTable('order_items')) {
            $itemsTable = 'order_items';
        } elseif (Schema::hasTable('order_details')) {
            $itemsTable = 'order_details';
        }

        if (!$itemsTable) {
            return null;
        }

        // A. Product Buyers
        if ($audienceType === 'product_buyers') {
            if (empty($productId)) {
                return null; // All orders when no specific product is selected yet
            }
            return DB::table($itemsTable)
                ->where('product_id', (int) $productId)
                ->pluck('order_id')
                ->unique()
                ->values()
                ->toArray();
        }

        // B. Category Buyers
        if ($audienceType === 'category_buyers') {
            if (empty($categoryId)) {
                return null; // All orders when no specific category is selected yet
            }

            $matchingProductIds = [];
            $catIdInt = (int) $categoryId;

            if (Schema::hasTable('products')) {
                foreach (['category_id', 'sub_category_id', 'child_category_id', 'parent_category_id'] as $col) {
                    if (Schema::hasColumn('products', $col)) {
                        try {
                            $ids = DB::table('products')->where($col, $catIdInt)->pluck('id')->toArray();
                            $matchingProductIds = array_merge($matchingProductIds, $ids);
                        } catch (Throwable $e) {}
                    }
                }

                // Also check if products has a string/slug category column
                if (Schema::hasColumn('products', 'category') && Schema::hasTable('categories')) {
                    try {
                        $catObj = DB::table('categories')->where('id', $catIdInt)->first();
                        if ($catObj) {
                            $ids = DB::table('products')
                                ->where('category', $catIdInt)
                                ->orWhere('category', $catObj->name ?? '')
                                ->pluck('id')
                                ->toArray();
                            $matchingProductIds = array_merge($matchingProductIds, $ids);
                        }
                    } catch (Throwable $e) {}
                }
            }

            foreach (['product_category', 'category_product', 'product_categories'] as $pivotTable) {
                if (Schema::hasTable($pivotTable)) {
                    try {
                        $ids = DB::table($pivotTable)->where('category_id', $catIdInt)->pluck('product_id')->toArray();
                        $matchingProductIds = array_merge($matchingProductIds, $ids);
                    } catch (Throwable $e) {}
                }
            }

            $matchingProductIds = array_values(array_unique(array_filter($matchingProductIds)));

            if (empty($matchingProductIds)) {
                return [];
            }

            return DB::table($itemsTable)
                ->whereIn('product_id', $matchingProductIds)
                ->pluck('order_id')
                ->unique()
                ->values()
                ->toArray();
        }

        return null;
    }

    /**
     * AJAX Live Filter & Audience Recipient Discovery Engine (Zero raw SQL group/coalesce bugs)
     */
    public function filterRecipientsAjax(Request $request): JsonResponse
    {
        try {
            $audienceType = trim((string) $request->input('audience_type', 'all_order_customers'));
            $productId    = $request->input('product_id');
            $categoryId   = $request->input('category_id');
            $datePreset   = trim((string) $request->input('date_preset', 'all'));
            $startDate    = $request->input('start_date');
            $endDate      = $request->input('end_date');
            $search       = strtolower(trim((string) $request->input('search', '')));

            $recipientsMap = [];
            $staffRoles = ['admin', 'super_admin', 'staff', 'branch_manager', 'manager'];

            // -------------------------------------------------------------
            // A. AUDIENCE: STAFF & ADMIN USERS
            // -------------------------------------------------------------
            if ($audienceType === 'staff_users') {
                if (Schema::hasTable('users')) {
                    $users = DB::table('users')->latest('id')->get();

                    foreach ($users as $u) {
                        if (property_exists($u, 'deleted_at') && !empty($u->deleted_at)) {
                            continue;
                        }

                        $role = strtolower(trim((string) ($u->role ?? '')));
                        $isSuper = !empty($u->is_super_admin);

                        if (!$isSuper && !in_array($role, $staffRoles, true)) {
                            continue;
                        }

                        $rawPhone = $this->extractUserPhone($u);
                        $clean = self::validateAndFormatBdPhone($rawPhone);
                        if (!$clean || isset($recipientsMap[$clean]) || $this->isPhoneBlacklisted($clean)) {
                            continue;
                        }

                        $uName = trim((string) ($u->name ?? 'Staff Member'));
                        if ($search !== '' && !str_contains(strtolower($uName), $search) && !str_contains($clean, $search)) {
                            continue;
                        }

                        $recipientsMap[$clean] = [
                            'name'         => $uName ?: 'Staff Member',
                            'phone_number' => $clean,
                            'customer_id'  => $u->id,
                            'source'       => 'Staff (' . ucfirst(str_replace(['_', '-'], ' ', $role ?: 'Admin')) . ')',
                            'orders_count' => '—',
                            'total_spent'  => '—',
                        ];
                    }
                }
            }

            // -------------------------------------------------------------
            // B. AUDIENCE: REGISTERED CUSTOMERS ONLY
            // -------------------------------------------------------------
            elseif ($audienceType === 'registered_customers') {
                if (Schema::hasTable('users')) {
                    $users = DB::table('users')->latest('id')->get();

                    $orderStatsByUser = [];
                    if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'user_id')) {
                        $allOrders = DB::table('orders')->whereNotNull('user_id')->get();
                        foreach ($allOrders as $or) {
                            $uid = $or->user_id;
                            if (!isset($orderStatsByUser[$uid])) {
                                $orderStatsByUser[$uid] = ['count' => 0, 'spent' => 0.0];
                            }
                            $orderStatsByUser[$uid]['count']++;
                            $orderStatsByUser[$uid]['spent'] += (float) ($or->grand_total ?? ($or->total ?? 0));
                        }
                    }

                    foreach ($users as $c) {
                        if (property_exists($c, 'deleted_at') && !empty($c->deleted_at)) {
                            continue;
                        }

                        $role = strtolower(trim((string) ($c->role ?? '')));
                        $isSuper = !empty($c->is_super_admin);

                        if ($isSuper || in_array($role, $staffRoles, true)) {
                            continue;
                        }

                        $rawPhone = $this->extractUserPhone($c);
                        $clean = self::validateAndFormatBdPhone($rawPhone);
                        if (!$clean || isset($recipientsMap[$clean]) || $this->isPhoneBlacklisted($clean)) {
                            continue;
                        }

                        $cName = trim((string) ($c->name ?? ('Customer #' . $c->id)));
                        if ($search !== '' && !str_contains(strtolower($cName), $search) && !str_contains($clean, $search)) {
                            continue;
                        }

                        $uStat = $orderStatsByUser[$c->id] ?? ['count' => 0, 'spent' => 0.0];

                        $recipientsMap[$clean] = [
                            'name'         => $cName ?: ('Customer #' . $c->id),
                            'phone_number' => $clean,
                            'customer_id'  => $c->id,
                            'source'       => 'Registered Customer',
                            'orders_count' => $uStat['count'] . ' Orders',
                            'total_spent'  => $uStat['spent'] > 0 ? $uStat['spent'] : '—',
                        ];
                    }
                }
            }

            // -------------------------------------------------------------
            // C. AUDIENCE: CUSTOM MANUAL NUMBERS LIST
            // -------------------------------------------------------------
            elseif ($audienceType === 'custom_numbers') {
                $rawInput = (string) $request->input('custom_numbers', '');
                $numbers = preg_split('/[\r\n,; ]+/', $rawInput, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($numbers as $num) {
                    $clean = self::validateAndFormatBdPhone(trim($num));
                    if ($clean && !isset($recipientsMap[$clean]) && !$this->isPhoneBlacklisted($clean)) {
                        $recipientsMap[$clean] = [
                            'name'         => 'Manual Entry',
                            'phone_number' => $clean,
                            'customer_id'  => null,
                            'source'       => 'Custom List',
                            'orders_count' => '—',
                            'total_spent'  => '—',
                        ];
                    }
                }
            }

            // -------------------------------------------------------------
            // D. AUDIENCE: ORDER BUYERS, RECENT BUYERS, PRODUCT, CATEGORY & ALL ELIGIBLE
            // -------------------------------------------------------------
            else {
                if (Schema::hasTable('orders')) {
                    $orderQuery = DB::table('orders');

                    // 1. Product or Category Filter
                    if (in_array($audienceType, ['product_buyers', 'category_buyers'], true)) {
                        $filteredOrderIds = $this->resolveOrderIdsByProductOrCategory($audienceType, $productId, $categoryId);
                        if (is_array($filteredOrderIds)) {
                            if (empty($filteredOrderIds)) {
                                $orderQuery->whereRaw('0 = 1');
                            } else {
                                $orderQuery->whereIn('id', $filteredOrderIds);
                            }
                        }
                    }

                    // 2. Fetch Orders & Filter/Aggregate Safely in PHP
                    $orders = $orderQuery->latest('id')->get();

                    // Resolve Date Bounds in PHP
                    $fromDate = null;
                    $toDate   = null;

                    if ($datePreset !== 'all') {
                        switch ($datePreset) {
                            case 'today':
                                $fromDate = Carbon::today()->startOfDay();
                                $toDate   = Carbon::today()->endOfDay();
                                break;
                            case '7_days':
                                $fromDate = now()->subDays(7)->startOfDay();
                                break;
                            case '30_days':
                                $fromDate = now()->subDays(30)->startOfDay();
                                break;
                            case '60_days':
                                $fromDate = now()->subDays(60)->startOfDay();
                                break;
                            case '90_days':
                                $fromDate = now()->subDays(90)->startOfDay();
                                break;
                            case 'this_month':
                                $fromDate = now()->startOfMonth();
                                $toDate   = now()->endOfMonth();
                                break;
                            case 'last_month':
                                $fromDate = now()->subMonth()->startOfMonth();
                                $toDate   = now()->subMonth()->endOfMonth();
                                break;
                            case 'custom':
                                if (!empty($startDate)) {
                                    $fromDate = Carbon::parse($startDate)->startOfDay();
                                }
                                if (!empty($endDate)) {
                                    $toDate = Carbon::parse($endDate)->endOfDay();
                                }
                                break;
                        }
                    } elseif ($audienceType === 'recent_buyers') {
                        $fromDate = now()->subDays(60)->startOfDay();
                    }

                    foreach ($orders as $ord) {
                        // Date check in PHP
                        if ($fromDate || $toDate) {
                            if (empty($ord->created_at)) {
                                continue;
                            }
                            try {
                                $ordTime = Carbon::parse($ord->created_at);
                                if ($fromDate && $ordTime->lt($fromDate)) {
                                    continue;
                                }
                                if ($toDate && $ordTime->gt($toDate)) {
                                    continue;
                                }
                            } catch (Throwable $e) {
                                continue;
                            }
                        }

                        $rawPhone = $this->extractOrderPhone($ord);
                        $clean = self::validateAndFormatBdPhone($rawPhone);
                        if (!$clean || $this->isPhoneBlacklisted($clean)) {
                            continue;
                        }

                        $cName = $this->extractOrderName($ord);
                        if ($search !== '' && !str_contains(strtolower($cName), $search) && !str_contains($clean, $search)) {
                            continue;
                        }

                        $orderTotal = (float) ($ord->grand_total ?? ($ord->total ?? 0));

                        if (!isset($recipientsMap[$clean])) {
                            $recipientsMap[$clean] = [
                                'name'         => $cName,
                                'phone_number' => $clean,
                                'customer_id'  => $ord->user_id ?? null,
                                'source'       => match ($audienceType) {
                                    'product_buyers'  => 'Product Buyer',
                                    'category_buyers' => 'Category Buyer',
                                    'recent_buyers'   => 'Recent Buyer',
                                    default           => 'Order Buyer',
                                },
                                'raw_count'    => 1,
                                'orders_count' => '1 Orders',
                                'total_spent'  => $orderTotal,
                            ];
                        } else {
                            $recipientsMap[$clean]['raw_count']++;
                            $recipientsMap[$clean]['orders_count'] = $recipientsMap[$clean]['raw_count'] . ' Orders';
                            $recipientsMap[$clean]['total_spent'] += $orderTotal;
                        }
                    }

                    // 3. If 'all_eligible', also merge unique registered users
                    if ($audienceType === 'all_eligible' && Schema::hasTable('users')) {
                        $users = DB::table('users')->latest('id')->get();
                        foreach ($users as $u) {
                            if (property_exists($u, 'deleted_at') && !empty($u->deleted_at)) {
                                continue;
                            }

                            $rawPhone = $this->extractUserPhone($u);
                            $clean = self::validateAndFormatBdPhone($rawPhone);
                            if (!$clean || isset($recipientsMap[$clean]) || $this->isPhoneBlacklisted($clean)) {
                                continue;
                            }

                            $uName = trim((string) ($u->name ?? ('User #' . $u->id)));
                            if ($search !== '' && !str_contains(strtolower($uName), $search) && !str_contains($clean, $search)) {
                                continue;
                            }

                            $recipientsMap[$clean] = [
                                'name'         => $uName ?: ('User #' . $u->id),
                                'phone_number' => $clean,
                                'customer_id'  => $u->id,
                                'source'       => 'Registered User',
                                'orders_count' => '0 Orders',
                                'total_spent'  => 0.00,
                            ];
                        }
                    }
                }
            }

            $recipients = array_values($recipientsMap);
            $totalCount = count($recipients);

            return response()->json([
                'success'     => true,
                'total_count' => $totalCount,
                'recipients'  => $recipients,
            ]);

        } catch (Throwable $e) {
            Log::error('filterRecipientsAjax Exception: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success'     => false,
                'message'     => 'Error loading contacts: ' . $e->getMessage(),
                'total_count' => 0,
                'recipients'  => [],
            ]);
        }
    }

    /**
     * Store and immediately dispatch (or schedule) a newly created campaign without needing terminal.
     */
    public function store(Request $request, SmsService $smsService): RedirectResponse
    {
        $this->ensureCampaignSchemaCompatibility();

        $validated = $request->validate([
            'name'            => 'required|string|max:150',
            'provider_id'     => 'required|exists:sms_providers,id',
            'sender_id'       => 'nullable|string|max:30',
            'sms_message'     => 'required|string',
            'target_audience' => 'required|string',
            'custom_numbers'  => 'nullable|string',
            'schedule_type'   => 'required|in:immediate,scheduled',
            'scheduled_at'    => 'required_if:schedule_type,scheduled|nullable|date|after:now',
            'description'     => 'nullable|string|max:255',
            'selected_phones' => 'nullable|string',
        ]);

        $provider = SmsProvider::findOrFail($validated['provider_id']);
        $metrics = $smsService->analyzeMessage($validated['sms_message']);
        $costPerSms = (float) $provider->price_per_sms;

        $recipients = [];

        // 1. PRIORITY 1: CUSTOM NUMBERS LIST (Directly from Textarea)
        if ($validated['target_audience'] === 'custom_numbers') {
            $rawInput = (string) $request->input('custom_numbers', '');
            $numbers = preg_split('/[\r\n,; ]+/', $rawInput, -1, PREG_SPLIT_NO_EMPTY);
            $uniqueMap = [];

            foreach ($numbers as $num) {
                $clean = self::validateAndFormatBdPhone(trim($num));
                if ($clean && !isset($uniqueMap[$clean])) {
                    if (!$this->isPhoneBlacklisted($clean)) {
                        $uniqueMap[$clean] = true;
                        $recipients[] = [
                            'phone_number' => $clean,
                            'customer_id'  => null,
                        ];
                    }
                }
            }

            if (empty($recipients)) {
                return back()
                    ->withInput()
                    ->with('error', 'No valid Bangladeshi mobile numbers found in the custom list (017XXXXXXXX or 88018XXXXXXXX).');
            }
        }
        // 2. PRIORITY 2: BULK CHECKBOX SELECTION FROM TABLE
        elseif ($request->filled('selected_phones')) {
            $selectedArray = json_decode($request->input('selected_phones'), true);
            if (is_array($selectedArray) && count($selectedArray) > 0) {
                $uniqueMap = [];
                foreach ($selectedArray as $item) {
                    $phone = is_array($item) ? ($item['phone'] ?? null) : $item;
                    $clean = self::validateAndFormatBdPhone($phone);
                    if ($clean && !isset($uniqueMap[$clean]) && !$this->isPhoneBlacklisted($clean)) {
                        $uniqueMap[$clean] = true;
                        $recipients[] = [
                            'phone_number' => $clean,
                            'customer_id'  => is_array($item) && !empty($item['customer_id']) ? $item['customer_id'] : null,
                        ];
                    }
                }
            }
        }

        // 3. PRIORITY 3: QUERY RESOLVER FALLBACK
        if (empty($recipients)) {
            $recipients = $this->resolveRecipientsFromRequest($request, $smsService);
        }

        if (empty($recipients)) {
            return back()
                ->withInput()
                ->with('error', 'No valid recipient phone numbers found for the selected filter criteria.');
        }

        $totalRecipients = count($recipients);
        $estimatedCost = $totalRecipients * $metrics['sms_parts'] * $costPerSms;
        $isScheduled = ($validated['schedule_type'] === 'scheduled' && !empty($validated['scheduled_at']));

        $channelVal = $this->resolveCampaignChannelValue();
        $initialStatus = $isScheduled
            ? $this->resolveCampaignStatusValue('scheduled')
            : $this->resolveCampaignStatusValue('running');

        $campaignData = [
            'name'             => $validated['name'],
            'sender_id'        => $validated['sender_id'] ?: ($provider->sender_id ?: null),
            'sms_message'      => $validated['sms_message'],
            'status'           => $initialStatus,
            'total_recipients' => $totalRecipients,
            'estimated_cost'   => $estimatedCost,
            'scheduled_at'     => $isScheduled ? Carbon::parse($validated['scheduled_at']) : null,
            'description'      => $validated['description'] ?? null,
            'created_at'       => now(),
            'updated_at'       => now(),
        ];

        if (Schema::hasColumn('sms_campaigns', 'sms_provider_id')) {
            $campaignData['sms_provider_id'] = $provider->id;
        }
        if (Schema::hasColumn('sms_campaigns', 'provider_id')) {
            $campaignData['provider_id'] = $provider->id;
        }
        if (Schema::hasColumn('sms_campaigns', 'target_audience')) {
            $campaignData['target_audience'] = $validated['target_audience'];
        }
        if (Schema::hasColumn('sms_campaigns', 'audience')) {
            $campaignData['audience'] = $validated['target_audience'];
        }
        if ($channelVal !== null && Schema::hasColumn('sms_campaigns', 'channel')) {
            $campaignData['channel'] = $channelVal;
        }

        DB::beginTransaction();
        try {
            $campaign = new SmsCampaign();
            $campaign->forceFill($campaignData);
            $campaign->save();

            $campaignFk = Schema::hasColumn('sms_campaign_recipients', 'sms_campaign_id') ? 'sms_campaign_id' : 'campaign_id';
            $statusCol  = Schema::hasColumn('sms_campaign_recipients', 'sms_status') ? 'sms_status' : 'status';

            $recipientChunks = array_chunk($recipients, 500);
            $now = Carbon::now();

            foreach ($recipientChunks as $chunk) {
                $batchData = array_map(function ($rec) use ($campaign, $campaignFk, $statusCol, $now) {
                    return [
                        $campaignFk    => $campaign->id,
                        'customer_id'  => !empty($rec['customer_id']) ? $rec['customer_id'] : null,
                        'phone_number' => $rec['phone_number'],
                        $statusCol     => 'pending',
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ];
                }, $chunk);

                SmsCampaignRecipient::insert($batchData);
            }

            DB::commit();

            // IF IMMEDIATE: DISPATCH SYNCHRONOUSLY RIGHT NOW WITHOUT TERMINAL QUEUE WORKER!
            if (!$isScheduled) {
                $this->executeCampaignDispatch($campaign, $smsService);
            }

            $message = $isScheduled
                ? "Campaign successfully scheduled for {$campaign->scheduled_at->format('M d, Y h:i A')} ({$totalRecipients} recipients)."
                : "Campaign dispatched immediately to {$totalRecipients} recipient(s) & gateway balance synced!";

            return redirect()
                ->route('admin.sms.campaigns.show', $campaign->id)
                ->with('success', $message);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('SmsCampaignController store error: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Failed to create SMS campaign: ' . $e->getMessage());
        }
    }

    /**
     * Resolve target recipients based on request filters
     */
    protected function resolveRecipientsFromRequest(Request $request, SmsService $smsService): array
    {
        $response = $this->filterRecipientsAjax($request);
        $data = $response->getData(true);

        return $data['recipients'] ?? [];
    }

    /**
     * Direct browser dispatch action: Sends pending recipients instantly with live balance sync!
     */
    public function dispatchNow($id, SmsService $smsService): RedirectResponse
    {
        $campaign = SmsCampaign::findOrFail($id);
        $processed = $this->executeCampaignDispatch($campaign, $smsService);

        if ($processed === 0) {
            return back()->with('info', 'No pending recipients left to dispatch.');
        }

        return back()->with('success', "Dispatched {$processed} SMS successfully! Live gateway balance synced.");
    }

    /**
     * Display real-time progress and details of a single campaign.
     */
    public function show(SmsCampaign $campaign, Request $request, SmsService $smsService): View
    {
        // Auto-process if this campaign (or any scheduled campaign) is due
        $this->processDueCampaigns($smsService);

        $campaign->refresh();
        $campaign->load('provider');

        $recipientsQuery = $campaign->recipients();

        if ($request->filled('status')) {
            $statusCol = Schema::hasColumn('sms_campaign_recipients', 'sms_status') ? 'sms_status' : 'status';
            $recipientsQuery->where($statusCol, $request->query('status'));
        }

        if ($request->filled('search')) {
            $recipientsQuery->where('phone_number', 'like', '%' . trim($request->query('search')) . '%');
        }

        $recipients = $recipientsQuery->paginate(25)->withQueryString();

        return view('admin.sms.campaigns.show', compact('campaign', 'recipients'));
    }

    /**
     * Cancel a pending, queued, or scheduled campaign.
     */
    public function cancel(SmsCampaign $campaign): RedirectResponse
    {
        if (in_array($campaign->status, ['completed', 'failed', 'cancelled'], true)) {
            return back()->with('error', 'This campaign cannot be cancelled in its current state.');
        }

        $cancelledStatus = $this->resolveCampaignStatusValue('cancelled');
        $campaign->update([
            'status' => $cancelledStatus,
        ]);

        return redirect()
            ->route('admin.sms.campaigns.index')
            ->with('success', "Campaign '{$campaign->name}' has been cancelled.");
    }

    /**
     * Re-queue and immediately retry failed recipients of a campaign.
     */
    public function retryFailed(SmsCampaign $campaign, SmsService $smsService): RedirectResponse
    {
        $statusCol = Schema::hasColumn('sms_campaign_recipients', 'sms_status') ? 'sms_status' : 'status';
        $failedCount = $campaign->recipients()->where($statusCol, 'failed')->count();

        if ($failedCount === 0) {
            return back()->with('info', 'No failed recipients found to retry.');
        }

        $campaign->recipients()->where($statusCol, 'failed')->update([
            $statusCol       => 'pending',
            'failure_reason' => null,
            'processed_at'   => null,
        ]);

        $runningStatus = $this->resolveCampaignStatusValue('running');
        $campaign->update([
            'status' => $runningStatus,
        ]);

        $this->executeCampaignDispatch($campaign, $smsService);

        return back()->with('success', "Retried {$failedCount} failed recipient(s) immediately!");
    }
}