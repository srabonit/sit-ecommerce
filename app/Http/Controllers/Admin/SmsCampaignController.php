<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessBulkSmsCampaignJob;
use App\Models\CommunicationPreference;
use App\Models\Order;
use App\Models\SmsBlacklist;
use App\Models\SmsCampaign;
use App\Models\SmsCampaignRecipient;
use App\Models\SmsProvider;
use App\Models\SmsTemplate;
use App\Models\User;
use App\Services\Sms\SmsService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SmsCampaignController extends Controller
{
    /**
     * Display a paginated listing of SMS campaigns.
     */
    public function index(Request $request): View
    {
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
    public function create(): View
    {
        $providers = SmsProvider::where('is_active', true)->get();
        $templates = SmsTemplate::where('is_active', true)
            ->whereIn('event_type', ['marketing', 'general'])
            ->get();

        // Safe phone column detection in users table
        $userPhoneCol = Schema::hasColumn('users', 'phone') ? 'phone' : (Schema::hasColumn('users', 'phone_number') ? 'phone_number' : (Schema::hasColumn('users', 'mobile') ? 'mobile' : null));
        $allUsersCount = $userPhoneCol ? User::whereNotNull($userPhoneCol)->where($userPhoneCol, '!=', '')->count() : 0;

        // Safe subscriber count detection without crashing if sms_marketing column is missing
        $subscribedCount = 0;
        if (Schema::hasTable('communication_preferences') && Schema::hasColumn('communication_preferences', 'sms_marketing')) {
            $subscribedCount = CommunicationPreference::where('sms_marketing', true)->count();
        } elseif (Schema::hasTable('users') && Schema::hasColumn('users', 'sms_subscribed')) {
            $subscribedCount = User::where('sms_subscribed', true)
                ->when($userPhoneCol, fn($q) => $q->whereNotNull($userPhoneCol)->where($userPhoneCol, '!=', ''))
                ->count();
        } else {
            $subscribedCount = $allUsersCount;
        }

        // Safe recent buyers detection from orders table
        $orderPhoneCol = Schema::hasColumn('orders', 'phone_number') ? 'phone_number' : (Schema::hasColumn('orders', 'phone') ? 'phone' : (Schema::hasColumn('orders', 'billing_phone') ? 'billing_phone' : null));
        $recentBuyersCount = 0;
        if ($orderPhoneCol && Schema::hasTable('orders')) {
            $recentBuyersCount = Order::where('created_at', '>=', now()->subDays(60))
                ->whereNotNull($orderPhoneCol)
                ->where($orderPhoneCol, '!=', '')
                ->distinct($orderPhoneCol)
                ->count($orderPhoneCol);
        }

        $audienceCounts = [
            'all_users'        => $allUsersCount,
            'subscribed_users' => $subscribedCount,
            'recent_buyers'    => $recentBuyersCount,
        ];

        return view('admin.sms.campaigns.create', compact('providers', 'templates', 'audienceCounts'));
    }

    /**
     * Store and optionally queue/schedule a newly created campaign.
     */
    public function store(Request $request, SmsService $smsService): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:150',
            'provider_id'     => 'required|exists:sms_providers,id',
            'sender_id'       => 'nullable|string|max:30',
            'sms_message'     => 'required|string',
            'target_audience' => 'required|in:all_users,subscribed_only,recent_buyers,custom_numbers',
            'custom_numbers'  => 'required_if:target_audience,custom_numbers|nullable|string',
            'schedule_type'   => 'required|in:immediate,scheduled',
            'scheduled_at'    => 'required_if:schedule_type,scheduled|nullable|date|after:now',
            'description'     => 'nullable|string|max:255',
        ]);

        $provider = SmsProvider::findOrFail($validated['provider_id']);
        $metrics = $smsService->analyzeMessage($validated['sms_message']);
        $costPerSms = (float) $provider->price_per_sms;

        // Resolve recipients based on audience selection safely
        $recipients = $this->resolveRecipients($validated['target_audience'], $validated['custom_numbers'] ?? null, $smsService);

        if (empty($recipients)) {
            return back()
                ->withInput()
                ->with('error', 'No valid recipient phone numbers found for the selected target audience.');
        }

        $totalRecipients = count($recipients);
        $estimatedCost = $totalRecipients * $metrics['sms_parts'] * $costPerSms;
        $isScheduled = ($validated['schedule_type'] === 'scheduled' && !empty($validated['scheduled_at']));

        DB::beginTransaction();
        try {
            $campaign = SmsCampaign::create([
                'name'                 => $validated['name'],
                'provider_id'          => $provider->id,
                'sender_id'            => $validated['sender_id'] ?: $provider->sender_id,
                'sms_message'          => $validated['sms_message'],
                'channel'              => 'sms',
                'target_audience'      => $validated['target_audience'],
                'status'               => $isScheduled ? 'scheduled' : 'draft',
                'total_recipients'     => $totalRecipients,
                'estimated_cost'       => $estimatedCost,
                'scheduled_at'         => $isScheduled ? Carbon::parse($validated['scheduled_at']) : null,
                'description'          => $validated['description'] ?? null,
            ]);

            // Bulk insert recipients in chunks
            $recipientChunks = array_chunk($recipients, 500);
            $now = Carbon::now();

            foreach ($recipientChunks as $chunk) {
                $batchData = array_map(function ($rec) use ($campaign, $now) {
                    return [
                        'campaign_id'    => $campaign->id,
                        'customer_id'    => $rec['customer_id'] ?? null,
                        'phone_number'   => $rec['phone_number'],
                        'sms_status'     => 'pending',
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];
                }, $chunk);

                SmsCampaignRecipient::insert($batchData);
            }

            // If immediate execution, dispatch directly to background queue
            if (!$isScheduled) {
                $campaign->update(['status' => 'queued']);
                ProcessBulkSmsCampaignJob::dispatch($campaign->id)->onQueue('sms-campaigns');
            }

            DB::commit();

            $message = $isScheduled
                ? "Campaign successfully scheduled for {$campaign->scheduled_at->format('M d, Y h:i A')}."
                : 'Campaign queued and background dispatching started.';

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
     * Display real-time progress and details of a single campaign.
     */
    public function show(SmsCampaign $campaign, Request $request): View
    {
        $campaign->load('provider');

        $recipientsQuery = $campaign->recipients();

        if ($request->filled('status')) {
            $recipientsQuery->where('sms_status', $request->query('status'));
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

        $campaign->update([
            'status' => 'cancelled',
        ]);

        return redirect()
            ->route('admin.sms.campaigns.index')
            ->with('success', "Campaign '{$campaign->name}' has been cancelled.");
    }

    /**
     * Re-queue failed recipients of an already completed/failed campaign.
     */
    public function retryFailed(SmsCampaign $campaign): RedirectResponse
    {
        $failedCount = $campaign->recipients()->where('sms_status', 'failed')->count();

        if ($failedCount === 0) {
            return back()->with('info', 'No failed recipients found to retry.');
        }

        $campaign->recipients()->where('sms_status', 'failed')->update([
            'sms_status'     => 'pending',
            'failure_reason' => null,
            'processed_at'   => null,
        ]);

        $campaign->update([
            'status' => 'queued',
        ]);

        ProcessBulkSmsCampaignJob::dispatch($campaign->id)->onQueue('sms-campaigns');

        return back()->with('success', "Retrying {$failedCount} failed recipient(s) in background.");
    }

    /**
     * Resolve target recipients based on selection safely.
     */
    protected function resolveRecipients(string $audience, ?string $customNumbers, SmsService $smsService): array
    {
        $recipients = [];

        // 1. Custom Numbers List
        if ($audience === 'custom_numbers' && !empty($customNumbers)) {
            $numbers = preg_split('/[\r\n,]+/', $customNumbers, -1, PREG_SPLIT_NO_EMPTY);
            $uniqueMap = [];

            foreach ($numbers as $num) {
                $clean = $smsService->formatNumber(trim($num));
                if (preg_match('/^8801[3-9]\d{8}$/', $clean) && !isset($uniqueMap[$clean])) {
                    if (!SmsBlacklist::isPhoneBlocked($clean)) {
                        $uniqueMap[$clean] = true;
                        $recipients[] = [
                            'phone_number' => $clean,
                            'customer_id'  => null,
                        ];
                    }
                }
            }

            return $recipients;
        }

        $userPhoneCol = Schema::hasColumn('users', 'phone') ? 'phone' : (Schema::hasColumn('users', 'phone_number') ? 'phone_number' : (Schema::hasColumn('users', 'mobile') ? 'mobile' : null));

        // 2. All Registered Users
        if ($audience === 'all_users') {
            if (!$userPhoneCol) {
                return [];
            }

            return User::whereNotNull($userPhoneCol)
                ->where($userPhoneCol, '!=', '')
                ->get(['id', $userPhoneCol])
                ->filter(fn($u) => preg_match('/^8801[3-9]\d{8}$/', $smsService->formatNumber($u->{$userPhoneCol})))
                ->filter(fn($u) => !SmsBlacklist::isPhoneBlocked($smsService->formatNumber($u->{$userPhoneCol})))
                ->map(fn($u) => [
                    'phone_number' => $smsService->formatNumber($u->{$userPhoneCol}),
                    'customer_id'  => $u->id,
                ])
                ->unique('phone_number')
                ->values()
                ->toArray();
        }

        // 3. Subscribed Users Only
        if ($audience === 'subscribed_only') {
            if (Schema::hasTable('communication_preferences') && Schema::hasColumn('communication_preferences', 'sms_marketing')) {
                $phoneCol = Schema::hasColumn('communication_preferences', 'phone') ? 'phone' : 'phone_number';
                return CommunicationPreference::where('sms_marketing', true)
                    ->get([$phoneCol, 'user_id'])
                    ->filter(fn($p) => preg_match('/^8801[3-9]\d{8}$/', $smsService->formatNumber($p->{$phoneCol})))
                    ->filter(fn($p) => !SmsBlacklist::isPhoneBlocked($smsService->formatNumber($p->{$phoneCol})))
                    ->map(fn($p) => [
                        'phone_number' => $smsService->formatNumber($p->{$phoneCol}),
                        'customer_id'  => $p->user_id,
                    ])
                    ->unique('phone_number')
                    ->values()
                    ->toArray();
            }

            // Fallback to all users if communication_preferences table doesn't have sms_marketing
            return $this->resolveRecipients('all_users', null, $smsService);
        }

        // 4. Recent Buyers (Last 60 Days)
        if ($audience === 'recent_buyers') {
            $orderPhoneCol = Schema::hasColumn('orders', 'phone_number') ? 'phone_number' : (Schema::hasColumn('orders', 'phone') ? 'phone' : (Schema::hasColumn('orders', 'billing_phone') ? 'billing_phone' : null));
            if (!$orderPhoneCol || !Schema::hasTable('orders')) {
                return [];
            }

            $userFkCol = Schema::hasColumn('orders', 'user_id') ? 'user_id' : (Schema::hasColumn('orders', 'customer_id') ? 'customer_id' : null);
            $selectCols = array_filter([$orderPhoneCol, $userFkCol]);

            $buyers = Order::where('created_at', '>=', now()->subDays(60))
                ->whereNotNull($orderPhoneCol)
                ->where($orderPhoneCol, '!=', '')
                ->get($selectCols);

            $uniqueMap = [];
            foreach ($buyers as $order) {
                $clean = $smsService->formatNumber($order->{$orderPhoneCol});
                if (preg_match('/^8801[3-9]\d{8}$/', $clean) && !isset($uniqueMap[$clean])) {
                    if (!SmsBlacklist::isPhoneBlocked($clean)) {
                        $uniqueMap[$clean] = true;
                        $recipients[] = [
                            'phone_number' => $clean,
                            'customer_id'  => $userFkCol ? $order->{$userFkCol} : null,
                        ];
                    }
                }
            }

            return $recipients;
        }

        return $recipients;
    }
}