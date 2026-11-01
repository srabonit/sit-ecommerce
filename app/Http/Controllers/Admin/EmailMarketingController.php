<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

// Marketing Models
use App\Models\EmailSubscriber;
use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Models\EmailSegment;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailTrackingEvent;
use App\Models\EmailAbandonedCart;
use App\Models\EmailAutomation;
use App\Models\EmailAutomationStep;
use App\Models\EmailAutomationLog;
use App\Models\EmailProviderSetting;
use App\Models\EmailConsentLog;
use App\Models\EmailSuppressionList;

// Existing Core Models
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Coupon;
use App\Models\User;

class EmailMarketingController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 1. DASHBOARD & LIVE ANALYTICS
    |--------------------------------------------------------------------------
    */
    public function dashboard(Request $request)
    {
        $dateRange = $request->get('range', '30_days');
        $startDate = match ($dateRange) {
            'today'        => now()->startOfDay(),
            'yesterday'    => now()->subDay()->startOfDay(),
            '7_days'       => now()->subDays(7)->startOfDay(),
            'this_month'   => now()->startOfMonth(),
            'last_month'   => now()->subMonth()->startOfMonth(),
            default        => now()->subDays(30)->startOfDay(),
        };

        // Audience Statistics
        $totalSubscribers    = EmailSubscriber::count();
        $eligibleSubscribers = EmailSubscriber::eligibleForMarketing()->count();
        $unsubscribedCount   = EmailSubscriber::where('status', 'unsubscribed')->count();
        $bouncedCount        = EmailSubscriber::where('status', 'bounced')->count();
        $newSubscribersRange = EmailSubscriber::where('created_at', '>=', $startDate)->count();

        // Campaign Metrics
        $totalCampaigns = EmailCampaign::count();
        $activeSending  = EmailCampaign::sending()->count();
        $completedCount = EmailCampaign::completed()->count();

        $campaignAggregates = EmailCampaign::selectRaw("
            COALESCE(SUM(sent_count), 0) as total_sent,
            COALESCE(SUM(delivered_count), 0) as total_delivered,
            COALESCE(SUM(opened_count), 0) as total_opened,
            COALESCE(SUM(clicked_count), 0) as total_clicked,
            COALESCE(SUM(unsubscribed_count), 0) as total_unsubs,
            COALESCE(SUM(bounced_count), 0) as total_bounces,
            COALESCE(SUM(total_revenue), 0) as total_revenue
        ")->first();

        $delivered = (int) $campaignAggregates->total_delivered;
        $openRate  = $delivered > 0 ? round(($campaignAggregates->total_opened / $delivered) * 100, 2) : 0;
        $clickRate = $delivered > 0 ? round(($campaignAggregates->total_clicked / $delivered) * 100, 2) : 0;

        // Automation Metrics
        $activeAutomations   = EmailAutomation::active()->count();
        $abandonedCartsCount = EmailAbandonedCart::pendingRecovery()->count();
        $recoveredCartsCount = EmailAbandonedCart::recovered()->count();

        // Safe dynamic order total column detection
        $orderTotalCol = collect(['grand_total', 'total', 'payable_amount', 'net_total', 'amount', 'total_amount', 'total_price', 'paid_amount'])
            ->first(fn($col) => Schema::hasTable('orders') && Schema::hasColumn('orders', $col));

        $recoveredRevenue = 0.0;
        if ($orderTotalCol) {
            $recoveredRevenue = (float) EmailAbandonedCart::recovered()
                ->join('orders', 'email_abandoned_carts.recovered_order_id', '=', 'orders.id')
                ->sum("orders.{$orderTotalCol}");
        } else {
            $recoveredRevenue = (float) EmailAbandonedCart::recovered()->sum('cart_value');
        }

        // Recent Dispatches
        $recentCampaigns = EmailCampaign::with(['template', 'segment'])
            ->latest()
            ->take(6)
            ->get();

        $recentSubscribers = EmailSubscriber::latest()->take(8)->get();

        return view('admin.email_marketing.dashboard', compact(
            'totalSubscribers',
            'eligibleSubscribers',
            'unsubscribedCount',
            'bouncedCount',
            'newSubscribersRange',
            'totalCampaigns',
            'activeSending',
            'completedCount',
            'campaignAggregates',
            'openRate',
            'clickRate',
            'activeAutomations',
            'abandonedCartsCount',
            'recoveredCartsCount',
            'recoveredRevenue',
            'recentCampaigns',
            'recentSubscribers',
            'dateRange'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | 2. ALL SUBSCRIBERS (ADVANCED AJAX FILTERING & BULK ACTIONS)
    |--------------------------------------------------------------------------
    */
    public function subscribersIndex(Request $request)
    {
        $query = $this->buildSubscribersQuery($request);

        // Sorting
        $sortBy = $request->get('sort_by', 'latest');
        match ($sortBy) {
            'oldest'        => $query->oldest('id'),
            'email_asc'     => $query->orderBy('email', 'asc'),
            'email_desc'    => $query->orderBy('email', 'desc'),
            'most_emailed'  => $query->orderBy('campaign_deliveries_count', 'desc'),
            default         => $query->latest('id'),
        };

        // Per page items
        $perPage = $request->get('per_page', 25);
        $subscribers = ($perPage === 'all')
            ? $query->paginate(2000)->withQueryString()
            : $query->paginate((int)$perPage)->withQueryString();

        // Real-Time Global Stats
        $stats = [
            'total'        => EmailSubscriber::count(),
            'subscribed'   => EmailSubscriber::where('status', 'subscribed')->count(),
            'unsubscribed' => EmailSubscriber::where('status', 'unsubscribed')->count(),
            'bounced'      => EmailSubscriber::where('status', 'bounced')->count(),
            'registered'   => EmailSubscriber::whereNotNull('user_id')->count(),
            'guest'        => EmailSubscriber::whereNull('user_id')->count(),
            'emailed'      => EmailSubscriber::has('campaignDeliveries')->count(),
        ];

        // AJAX Response for 100% Reload-free Filtering
        if ($request->ajax()) {
            $viewPath = view()->exists('admin.email_marketing.subscribers.partials.table_rows')
                ? 'admin.email_marketing.subscribers.partials.table_rows'
                : 'admin.email_marketing.subscribers._table_rows';

            $tableHtml = view()->exists($viewPath)
                ? view($viewPath, compact('subscribers'))->render()
                : '';

            return response()->json([
                'success'         => true,
                'table_html'      => $tableHtml,
                'pagination_html' => $subscribers->hasPages() ? (string)$subscribers->links('pagination::bootstrap-5') : '',
                'total_count'     => $subscribers->total(),
                'stats'           => $stats,
                'entries_text'    => "Showing {$subscribers->firstItem()} to {$subscribers->lastItem()} of {$subscribers->total()} subscribers",
            ]);
        }

        return view('admin.email_marketing.subscribers.index', compact('subscribers', 'stats'));
    }

    /**
     * Reusable High-Performance Query Builder for Subscribers
     */
    protected function buildSubscribersQuery(Request $request)
    {
        $query = EmailSubscriber::with('user')
            ->withCount('campaignDeliveries');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('email', 'like', "%{$s}%")
                  ->orWhere('first_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");

                if (Schema::hasColumn('email_subscribers', 'name')) {
                    $q->orWhere('name', 'like', "%{$s}%");
                }
                if (Schema::hasColumn('email_subscribers', 'tags')) {
                    $q->orWhere('tags', 'like', "%{$s}%");
                }
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Support both source column and consent_source
        if ($request->filled('source')) {
            $src = $request->source;
            $query->where(function ($q) use ($src) {
                if (Schema::hasColumn('email_subscribers', 'source')) {
                    $q->where('source', $src);
                }
                if (Schema::hasColumn('email_subscribers', 'consent_source')) {
                    $q->orWhere('consent_source', $src);
                }
            });
        }

        if ($request->filled('account_type')) {
            if ($request->account_type === 'registered') {
                $query->whereNotNull('user_id');
            } elseif ($request->account_type === 'guest') {
                $query->whereNull('user_id');
            }
        }

        if ($request->filled('emailed_status')) {
            if ($request->emailed_status === 'emailed') {
                $query->has('campaignDeliveries');
            } elseif ($request->emailed_status === 'never_emailed') {
                $query->doesntHave('campaignDeliveries');
            }
        }

        if ($request->filled('date_filter')) {
            match ($request->date_filter) {
                'today'        => $query->whereDate('created_at', Carbon::today()),
                'yesterday'    => $query->whereDate('created_at', Carbon::yesterday()),
                'this_week'    => $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]),
                'last_week'    => $query->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]),
                'this_month'   => $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]),
                'last_month'   => $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]),
                'this_year'    => $query->whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()]),
                default        => null,
            };
        }

        if ($request->filled('specific_date')) {
            $query->whereDate('created_at', Carbon::parse($request->specific_date)->toDateString());
        }

        if ($request->filled('specific_month')) {
            $parts = explode('-', $request->specific_month);
            if (count($parts) === 2) {
                $query->whereYear('created_at', (int)$parts[0])
                      ->whereMonth('created_at', (int)$parts[1]);
            }
        }

        if ($request->filled('specific_year')) {
            $query->whereYear('created_at', (int)$request->specific_year);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        } elseif ($request->filled('start_date')) {
            $query->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        } elseif ($request->filled('end_date')) {
            $query->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        return $query;
    }

    public function subscriberStore(Request $request)
    {
        $validated = $request->validate([
            'email'          => 'required|email|max:191|unique:email_subscribers,email',
            'first_name'     => 'nullable|string|max:100',
            'last_name'      => 'nullable|string|max:100',
            'phone'          => 'nullable|string|max:30',
            'consent_source' => 'nullable|string|max:50',
            'source'         => 'nullable|string|max:50',
        ]);

        $src = $validated['source'] ?? ($validated['consent_source'] ?? 'admin_manual');

        $subscriber = EmailSubscriber::create([
            'email'             => strtolower(trim($validated['email'])),
            'first_name'        => $validated['first_name'] ?? null,
            'last_name'         => $validated['last_name'] ?? null,
            'phone'             => $validated['phone'] ?? null,
            'status'            => 'subscribed',
            'marketing_consent' => true,
            'consent_at'        => now(),
            'consent_source'    => $src,
            'source'            => $src,
            'consent_ip'        => $request->ip(),
        ]);

        EmailConsentLog::logConsent($subscriber->email, 'opt_in', $src, $subscriber->id);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Subscriber added successfully.']);
        }

        return redirect()->back()->with('success', 'Subscriber added successfully.');
    }

    public function subscriberUpdate(Request $request, $id)
    {
        $subscriber = EmailSubscriber::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'nullable|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'phone'      => 'nullable|string|max:30',
            'status'     => 'required|in:subscribed,unsubscribed,bounced,pending',
        ]);

        $oldStatus = $subscriber->status;
        $subscriber->update($validated);

        if ($oldStatus !== $subscriber->status) {
            EmailConsentLog::logConsent(
                $subscriber->email,
                $subscriber->status === 'subscribed' ? 'resubscribe' : 'opt_out',
                'admin_update',
                $subscriber->id
            );
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Subscriber profile updated successfully.']);
        }

        return redirect()->back()->with('success', 'Subscriber profile updated successfully.');
    }

    public function subscriberDestroy(Request $request, $id)
    {
        $subscriber = EmailSubscriber::findOrFail($id);
        $subscriber->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Subscriber record removed.']);
        }

        return redirect()->back()->with('success', 'Subscriber record removed.');
    }

    public function subscriberToggleStatus(Request $request, $id)
    {
        $subscriber = EmailSubscriber::findOrFail($id);
        if ($subscriber->status === 'subscribed') {
            $subscriber->unsubscribe('Manually toggled by administrator');
            EmailConsentLog::logConsent($subscriber->email, 'opt_out', 'admin_toggle', $subscriber->id);
        } else {
            $subscriber->resubscribe('admin_toggle');
            EmailConsentLog::logConsent($subscriber->email, 'resubscribe', 'admin_toggle', $subscriber->id);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'status' => $subscriber->status, 'message' => 'Status updated.']);
        }

        return redirect()->back()->with('success', 'Subscriber status toggled successfully.');
    }

    public function subscribersExport(Request $request)
    {
        $fileName = 'subscribers_' . date('Y-m-d_His') . '.csv';
        $query = $this->buildSubscribersQuery($request);
        $subscribers = $query->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0",
        ];

        return response()->stream(function () use ($subscribers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Email', 'First Name', 'Last Name', 'Phone', 'Status', 'Account Type', 'Consent Source', 'Times Emailed', 'Joined Date']);
            foreach ($subscribers as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->email,
                    $row->first_name,
                    $row->last_name,
                    $row->phone,
                    ucfirst($row->status),
                    $row->user_id ? 'Registered User' : 'Guest Subscriber',
                    $row->source ?: ($row->consent_source ?: 'Organic'),
                    $row->campaign_deliveries_count ?? 0,
                    $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '',
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    public function bulkSendEmailInstant(Request $request)
    {
        $validated = $request->validate([
            'subscriber_ids'   => 'required|array|min:1',
            'subscriber_ids.*' => 'integer|exists:email_subscribers,id',
            'subject'          => 'required|string|max:255',
            'message'          => 'required|string',
        ]);

        $subscribers = EmailSubscriber::whereIn('id', $validated['subscriber_ids'])
            ->where('status', 'subscribed')
            ->get();

        if ($subscribers->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No active eligible subscribers selected.'], 422);
        }

        $storeName = config('app.name', 'Shop Now BD');
        $sentCount = 0;
        $failCount = 0;

        foreach ($subscribers as $sub) {
            if (EmailSuppressionList::isSuppressed($sub->email)) {
                continue;
            }

            $cName = $sub->first_name ?: 'Valued Customer';
            $emailBody = str_replace(
                ['{name}', '{customer_name}', '{first_name}', '{email}', '{store_name}'],
                [$cName, $cName, $cName, $sub->email, $storeName],
                $validated['message']
            );

            try {
                Mail::html($emailBody, function ($msg) use ($sub, $validated, $storeName) {
                    $msg->to($sub->email)
                        ->subject($validated['subject']);
                });
                $sentCount++;
            } catch (\Throwable $e) {
                $failCount++;
            }

            usleep(25000);
        }

        return response()->json([
            'success' => true,
            'message' => "Bulk email processed: {$sentCount} sent successfully, {$failCount} failed."
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 3. AUDIENCE SEGMENTS (ADVANCED AJAX FILTERING, LIVE RECALC & BULK ACTIONS)
    |--------------------------------------------------------------------------
    */
    public function segmentsIndex(Request $request)
    {
        $query = $this->buildSegmentsQuery($request);

        // Sorting options
        $sortBy = $request->get('sort_by', 'latest');
        match ($sortBy) {
            'oldest'         => $query->oldest('id'),
            'name_asc'       => $query->orderBy('name', 'asc'),
            'name_desc'      => $query->orderBy('name', 'desc'),
            'audience_high'  => $query->orderBy('matching_subscribers_count', 'desc'),
            'audience_low'   => $query->orderBy('matching_subscribers_count', 'asc'),
            'most_campaigns' => $query->orderBy('campaigns_count', 'desc'),
            default          => $query->latest('id'),
        };

        // Per page items control
        $perPage = $request->get('per_page', 15);
        $segments = ($perPage === 'all')
            ? $query->paginate(2000)->withQueryString()
            : $query->paginate((int)$perPage)->withQueryString();

        // Real-Time Global Stats
        $stats = [
            'total'       => EmailSegment::count(),
            'dynamic'     => EmailSegment::where('type', 'dynamic')->count(),
            'snapshot'    => EmailSegment::where('type', 'snapshot')->count(),
            'total_reach' => (int) EmailSegment::sum('matching_subscribers_count'),
        ];

        // 100% Reload-Free AJAX Response
        if ($request->ajax()) {
            $viewPath = view()->exists('admin.email_marketing.segments.partials.table_rows')
                ? 'admin.email_marketing.segments.partials.table_rows'
                : 'admin.email_marketing.segments._table_rows';

            $tableHtml = view()->exists($viewPath)
                ? view($viewPath, compact('segments'))->render()
                : '';

            return response()->json([
                'success'         => true,
                'table_html'      => $tableHtml,
                'pagination_html' => $segments->hasPages() ? (string)$segments->links('pagination::bootstrap-5') : '',
                'total_count'     => $segments->total(),
                'stats'           => $stats,
                'entries_text'    => "Showing {$segments->firstItem()} to {$segments->lastItem()} of {$segments->total()} segments",
            ]);
        }

        return view('admin.email_marketing.segments.index', compact('segments', 'stats'));
    }

    /**
     * Reusable High-Performance Query Builder for Segments
     */
    protected function buildSegmentsQuery(Request $request)
    {
        $query = EmailSegment::withCount('campaigns');

        // 1. Live Text Search (Name, Description)
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        // 2. Rule Type Filter (Dynamic vs Snapshot)
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // 3. Predefined Date Filter
        if ($request->filled('date_filter')) {
            match ($request->date_filter) {
                'today'        => $query->whereDate('created_at', Carbon::today()),
                'yesterday'    => $query->whereDate('created_at', Carbon::yesterday()),
                'this_week'    => $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]),
                'last_week'    => $query->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]),
                'this_month'   => $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]),
                'last_month'   => $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]),
                'this_year'    => $query->whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()]),
                default        => null,
            };
        }

        // 4. Specific Single Date
        if ($request->filled('specific_date')) {
            $query->whereDate('created_at', Carbon::parse($request->specific_date)->toDateString());
        }

        // 5. Specific Month (YYYY-MM)
        if ($request->filled('specific_month')) {
            $parts = explode('-', $request->specific_month);
            if (count($parts) === 2) {
                $query->whereYear('created_at', (int)$parts[0])
                      ->whereMonth('created_at', (int)$parts[1]);
            }
        }

        // 6. Specific Year
        if ($request->filled('specific_year')) {
            $query->whereYear('created_at', (int)$request->specific_year);
        }

        // 7. Custom Date Range (Start Date - End Date)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        } elseif ($request->filled('start_date')) {
            $query->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        } elseif ($request->filled('end_date')) {
            $query->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        return $query;
    }

    public function segmentCreate()
    {
        $categories = Category::where('is_active', true)->get(['id', 'name']);
        $brands     = Brand::where('is_active', true)->get(['id', 'name']);
        return view('admin.email_marketing.segments.create', compact('categories', 'brands'));
    }

    public function segmentStore(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:191',
            'description'     => 'nullable|string|max:500',
            'type'            => 'required|in:dynamic,snapshot',
            'filter_criteria' => 'required|array',
        ]);

        $segment = EmailSegment::create([
            'name'            => $validated['name'],
            'description'     => $validated['description'] ?? null,
            'type'            => $validated['type'],
            'filter_criteria' => $validated['filter_criteria'],
            'created_by'      => auth()->id(),
        ]);

        $count = $this->evaluateAudienceCount($segment->filter_criteria);
        $segment->updateMatchingCount($count);

        return redirect()->route('admin.email_marketing.segments.index')->with('success', "Audience segment saved with {$count} matching subscribers.");
    }

    public function segmentEdit($id)
    {
        $segment    = EmailSegment::findOrFail($id);
        $categories = Category::where('is_active', true)->get(['id', 'name']);
        $brands     = Brand::where('is_active', true)->get(['id', 'name']);

        return view('admin.email_marketing.segments.edit', compact('segment', 'categories', 'brands'));
    }

    public function segmentUpdate(Request $request, $id)
    {
        $segment = EmailSegment::findOrFail($id);

        $validated = $request->validate([
            'name'            => 'required|string|max:191',
            'description'     => 'nullable|string|max:500',
            'type'            => 'required|in:dynamic,snapshot',
            'filter_criteria' => 'required|array',
        ]);

        $segment->update([
            'name'            => $validated['name'],
            'description'     => $validated['description'] ?? null,
            'type'            => $validated['type'],
            'filter_criteria' => $validated['filter_criteria'],
        ]);

        $count = $this->evaluateAudienceCount($segment->filter_criteria);
        $segment->updateMatchingCount($count);

        return redirect()->route('admin.email_marketing.segments.index')->with('success', 'Segment updated successfully.');
    }

    public function segmentDestroy($id)
    {
        $segment = EmailSegment::findOrFail($id);
        $segment->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Segment #{$id} deleted successfully."
            ]);
        }

        return redirect()->back()->with('success', 'Audience segment deleted.');
    }

    public function segmentPreviewCount(Request $request)
    {
        $criteria = $request->input('filter_criteria', []);
        $count = $this->evaluateAudienceCount($criteria);

        return response()->json([
            'success' => true,
            'count'   => $count,
        ]);
    }

    /**
     * Live Single Segment Audience Recalculator AJAX
     */
    public function segmentRecalculate($id)
    {
        $segment = EmailSegment::findOrFail($id);
        $criteria = $segment->filter_criteria ?? [];
        $count = $this->evaluateAudienceCount($criteria);
        $segment->updateMatchingCount($count);

        return response()->json([
            'success'         => true,
            'count'           => $count,
            'last_calculated' => $segment->last_calculated_at ? $segment->last_calculated_at->format('M d, Y h:i A') : 'Just now',
            'message'         => "Audience recalculated: {$count} matching subscriber(s)."
        ]);
    }

    /**
     * Bulk Delete Selected Audience Segments
     */
    public function segmentBulkDelete(Request $request)
    {
        $validated = $request->validate([
            'segment_ids'   => 'required|array|min:1',
            'segment_ids.*' => 'integer|exists:email_segments,id',
        ]);

        $deletedCount = EmailSegment::whereIn('id', $validated['segment_ids'])->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} audience segment(s) deleted successfully."
        ]);
    }

    /**
     * Bulk Recalculate Matching Count for Selected Segments
     */
    public function segmentBulkRecalculate(Request $request)
    {
        $validated = $request->validate([
            'segment_ids'   => 'required|array|min:1',
            'segment_ids.*' => 'integer|exists:email_segments,id',
        ]);

        $segments = EmailSegment::whereIn('id', $validated['segment_ids'])->get();
        $refreshed = 0;

        foreach ($segments as $segment) {
            $count = $this->evaluateAudienceCount($segment->filter_criteria ?? []);
            $segment->updateMatchingCount($count);
            $refreshed++;
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully refreshed audience reach for {$refreshed} segment(s)."
        ]);
    }

    /**
     * Filter-Aware Segments CSV Exporter
     */
    public function segmentsExport(Request $request)
    {
        $fileName = 'audience_segments_' . date('Y-m-d_His') . '.csv';
        $query = $this->buildSegmentsQuery($request);
        $segments = $query->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0",
        ];

        return response()->stream(function () use ($segments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Segment Name', 'Description', 'Rule Type', 'Matching Subscribers', 'Used in Campaigns', 'Created At', 'Last Calculated At']);
            foreach ($segments as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->name,
                    $row->description ?? '',
                    ucfirst($row->type),
                    $row->matching_subscribers_count ?? 0,
                    $row->campaigns_count ?? 0,
                    $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '',
                    $row->last_calculated_at ? $row->last_calculated_at->format('Y-m-d H:i:s') : 'Pending',
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Centralized Query Builder for Evaluating Segments Strictly
     */
    public function getSegmentSubscribersQuery(array $criteria)
    {
        $query = EmailSubscriber::eligibleForMarketing();

        $orderEmailCol = collect(['customer_email', 'email', 'billing_email'])
            ->first(fn($c) => Schema::hasTable('orders') && Schema::hasColumn('orders', $c)) ?? 'customer_email';

        $orderStatusCol = collect(['order_status', 'status'])
            ->first(fn($c) => Schema::hasTable('orders') && Schema::hasColumn('orders', $c)) ?? 'order_status';

        // 1. Target Specific Product Purchase
        if (!empty($criteria['purchased_product_id']) && Schema::hasTable('orders') && Schema::hasTable('order_items')) {
            $productId = (int) $criteria['purchased_product_id'];
            $query->whereExists(function ($sub) use ($productId, $orderEmailCol, $orderStatusCol) {
                $sub->selectRaw(1)
                    ->from('orders')
                    ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                    ->whereColumn("orders.{$orderEmailCol}", 'email_subscribers.email')
                    ->where('order_items.product_id', $productId)
                    ->whereIn("orders.{$orderStatusCol}", ['completed', 'delivered']);
            });
        }

        // 2. Target Specific Category
        if (!empty($criteria['purchased_category_id']) && Schema::hasTable('orders') && Schema::hasTable('order_items')) {
            $catId = (int) $criteria['purchased_category_id'];
            $query->whereExists(function ($sub) use ($catId, $orderEmailCol, $orderStatusCol) {
                $sub->selectRaw(1)
                    ->from('orders')
                    ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->whereColumn("orders.{$orderEmailCol}", 'email_subscribers.email')
                    ->where('products.category_id', $catId)
                    ->whereIn("orders.{$orderStatusCol}", ['completed', 'delivered']);
            });
        }

        // 3. Inactivity Days
        if (!empty($criteria['inactive_days']) && Schema::hasTable('orders')) {
            $days = (int) $criteria['inactive_days'];
            $cutoff = now()->subDays($days);
            $query->whereNotExists(function ($sub) use ($cutoff, $orderEmailCol) {
                $sub->selectRaw(1)
                    ->from('orders')
                    ->whereColumn("orders.{$orderEmailCol}", 'email_subscribers.email')
                    ->where('orders.created_at', '>=', $cutoff);
            });
        }

        // 4. Minimum Orders
        if (!empty($criteria['min_orders']) && Schema::hasTable('orders')) {
            $minOrders = (int) $criteria['min_orders'];
            $query->whereRaw("(SELECT COUNT(*) FROM orders WHERE orders.{$orderEmailCol} = email_subscribers.email AND orders.{$orderStatusCol} IN ('completed', 'delivered')) >= ?", [$minOrders]);
        }

        // 5. Cart Abandoners
        if (!empty($criteria['has_abandoned_cart']) && Schema::hasTable('email_abandoned_carts')) {
            $query->whereExists(function ($sub) {
                $sub->selectRaw(1)
                    ->from('email_abandoned_carts')
                    ->whereColumn('email_abandoned_carts.email', 'email_subscribers.email')
                    ->where('email_abandoned_carts.is_recovered', false);
            });
        }

        return $query;
    }

    /**
     * Centralized Audience Criteria Evaluator
     */
    protected function evaluateAudienceCount(array $criteria): int
    {
        return $this->getSegmentSubscribersQuery($criteria)->count();
    }

    /*
    |--------------------------------------------------------------------------
    | 4. EMAIL CAMPAIGNS
    |--------------------------------------------------------------------------
    */
    public function campaignsIndex(Request $request)
    {
        $query = EmailCampaign::with(['template', 'segment'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $campaigns = $query->paginate(15)->withQueryString();
        return view('admin.email_marketing.campaigns.index', compact('campaigns'));
    }

    public function campaignCreate()
    {
        $templates = EmailTemplate::active()->get(['id', 'name']);
        $segments  = EmailSegment::all(['id', 'name', 'matching_subscribers_count']);
        
        $coupons = collect();
        if (Schema::hasTable('coupons')) {
            $couponQuery = Coupon::query();
            if (Schema::hasColumn('coupons', 'status')) {
                $couponQuery->where('status', 1);
            }
            $coupons = $couponQuery->get();
        }

        return view('admin.email_marketing.campaigns.create', compact('templates', 'segments', 'coupons'));
    }

    public function campaignStore(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:191',
            'subject'      => 'required|string|max:255',
            'preview_text' => 'nullable|string|max:255',
            'sender_name'  => 'nullable|string|max:100',
            'sender_email' => 'nullable|email|max:150',
            'template_id'  => 'nullable|exists:email_templates,id',
            'segment_id'   => 'nullable|exists:email_segments,id',
            'coupon_id'    => 'nullable',
            'scheduled_at' => 'nullable|date',
            'batch_size'   => 'nullable|integer|min:10|max:1000',
        ]);

        $status = !empty($validated['scheduled_at']) && strtotime($validated['scheduled_at']) > time()
            ? 'scheduled'
            : 'draft';

        $campaign = EmailCampaign::create(array_merge($validated, [
            'status'     => $status,
            'created_by' => auth()->id(),
        ]));

        return redirect()->route('admin.email_marketing.campaigns.show', $campaign->id)
                         ->with('success', 'Campaign configuration saved.');
    }

    public function campaignShow($id)
    {
        $campaign = EmailCampaign::with(['template', 'segment', 'coupon', 'creator'])
            ->withCount(['recipients', 'trackingEvents'])
            ->findOrFail($id);

        return view('admin.email_marketing.campaigns.show', compact('campaign'));
    }

    public function campaignEdit($id)
    {
        $campaign  = EmailCampaign::findOrFail($id);
        $templates = EmailTemplate::active()->get(['id', 'name']);
        $segments  = EmailSegment::all(['id', 'name', 'matching_subscribers_count']);
        
        $coupons = collect();
        if (Schema::hasTable('coupons')) {
            $couponQuery = Coupon::query();
            if (Schema::hasColumn('coupons', 'status')) {
                $couponQuery->where('status', 1);
            }
            $coupons = $couponQuery->get();
        }

        return view('admin.email_marketing.campaigns.edit', compact('campaign', 'templates', 'segments', 'coupons'));
    }

    public function campaignUpdate(Request $request, $id)
    {
        $campaign = EmailCampaign::findOrFail($id);
        if (!$campaign->isEditable()) {
            return redirect()->back()->with('error', 'Campaign cannot be edited in its current state.');
        }

        $validated = $request->validate([
            'name'         => 'required|string|max:191',
            'subject'      => 'required|string|max:255',
            'preview_text' => 'nullable|string|max:255',
            'sender_name'  => 'nullable|string|max:100',
            'sender_email' => 'nullable|email|max:150',
            'template_id'  => 'nullable|exists:email_templates,id',
            'segment_id'   => 'nullable|exists:email_segments,id',
            'coupon_id'    => 'nullable',
            'scheduled_at' => 'nullable|date',
        ]);

        $campaign->update($validated);

        return redirect()->route('admin.email_marketing.campaigns.show', $campaign->id)
                         ->with('success', 'Campaign updated successfully.');
    }

    public function campaignDestroy($id)
    {
        $campaign = EmailCampaign::findOrFail($id);
        $campaign->delete();

        return redirect()->route('admin.email_marketing.campaigns.index')->with('success', 'Campaign deleted.');
    }

    public function campaignDispatchNow($id)
    {
        $campaign = EmailCampaign::with(['template', 'coupon', 'segment'])->findOrFail($id);

        $existingCount = EmailCampaignRecipient::where('campaign_id', $campaign->id)->count();

        if ($existingCount === 0) {
            if ($campaign->segment_id && $campaign->segment) {
                $criteria = $campaign->segment->filter_criteria ?? [];
                $subscribers = $this->getSegmentSubscribersQuery($criteria)->get();
            } else {
                $subscribers = EmailSubscriber::eligibleForMarketing()->get();
            }

            if ($subscribers->isEmpty()) {
                return redirect()->back()->with('error', 'No eligible recipients found matching this campaign segment.');
            }

            $campaign->update([
                'status'           => 'sending',
                'started_at'       => now(),
                'total_recipients' => $subscribers->count(),
            ]);

            $recipientsData = [];
            foreach ($subscribers as $sub) {
                $recipientsData[] = [
                    'campaign_id'    => $campaign->id,
                    'subscriber_id'  => $sub->id,
                    'email'          => $sub->email,
                    'status'         => 'queued',
                    'tracking_token' => Str::random(64),
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }

            foreach (array_chunk($recipientsData, 500) as $chunk) {
                EmailCampaignRecipient::insertOrIgnore($chunk);
            }
        } else {
            $campaign->update([
                'status'     => 'sending',
                'started_at' => $campaign->started_at ?? now(),
            ]);
        }

        $queuedRecipients = EmailCampaignRecipient::where('campaign_id', $campaign->id)
            ->where('status', 'queued')
            ->get();

        $sentCount  = 0;
        $failCount  = 0;
        $storeName  = config('app.name', 'Shop Now BD');
        $couponCode = $campaign->coupon ? $campaign->coupon->code : '';

        foreach ($queuedRecipients as $recipient) {
            if (EmailSuppressionList::isSuppressed($recipient->email)) {
                $recipient->update([
                    'status'        => 'bounced',
                    'error_message' => 'Address is on suppression blacklist.',
                ]);
                $campaign->increment('bounced_count');
                continue;
            }

            $subscriber      = EmailSubscriber::find($recipient->subscriber_id);
            $cName           = $subscriber ? ($subscriber->first_name ?: 'Valued Customer') : 'Valued Customer';
            $trackingToken   = $recipient->tracking_token;
            $targetUrl       = url('/shop');
            $trackedClickUrl = url('/email/track/click/' . $trackingToken . '?target=' . urlencode($targetUrl));
            $unsubscribeUrl  = url('/email/unsubscribe/' . ($subscriber->unsubscribe_token ?? $trackingToken));
            $openPixel       = '<img src="' . url('/email/track/open/' . $trackingToken) . '" width="1" height="1" style="display:none;" alt="" />';

            if ($campaign->template) {
                $htmlContent = $campaign->template->render([
                    'customer_name'   => $cName,
                    'first_name'      => $cName,
                    'email'           => $recipient->email,
                    'store_name'      => $storeName,
                    'coupon_code'     => $couponCode,
                    'target_url'      => $trackedClickUrl,
                    'unsubscribe_url' => $unsubscribeUrl,
                ]);
                $htmlContent = str_replace(
                    ['{coupon_code}', '{target_url}', '{unsubscribe_url}'],
                    [$couponCode, $trackedClickUrl, $unsubscribeUrl],
                    $htmlContent
                );
                $htmlContent .= $openPixel;
            } else {
                $htmlContent = "<p>Hello {$cName},<br>Check out our latest collection at {$storeName}!</p><p><a href='{$trackedClickUrl}' style='display:inline-block;padding:10px 20px;background:#2563eb;color:#fff;text-decoration:none;border-radius:5px;'>Shop The Collection &rarr;</a></p>" . $openPixel;
            }

            try {
                Mail::html($htmlContent, function ($msg) use ($campaign, $recipient, $storeName) {
                    $senderEmail = $campaign->sender_email ?: config('mail.from.address');
                    $senderName  = $campaign->sender_name ?: $storeName;

                    $msg->to($recipient->email)
                        ->from($senderEmail, $senderName)
                        ->subject($campaign->subject);
                });

                $recipient->update([
                    'status'  => 'sent',
                    'sent_at' => now(),
                ]);
                $campaign->increment('sent_count');
                $campaign->increment('delivered_count');
                $sentCount++;
            } catch (\Throwable $e) {
                $recipient->update([
                    'status'        => 'failed',
                    'error_message' => substr($e->getMessage(), 0, 250),
                ]);
                $campaign->increment('bounced_count');
                $failCount++;
            }

            usleep(25000);
        }

        $campaign->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()->back()->with('success', "Campaign processed successfully: {$sentCount} sent to targeted segment, {$failCount} failed.");
    }

    public function campaignPause($id)
    {
        $campaign = EmailCampaign::findOrFail($id);
        $campaign->update(['status' => 'paused']);

        return redirect()->back()->with('success', 'Campaign paused.');
    }

    public function campaignResume($id)
    {
        return $this->campaignDispatchNow($id);
    }

    public function campaignCancel($id)
    {
        $campaign = EmailCampaign::findOrFail($id);
        $campaign->update(['status' => 'cancelled']);

        return redirect()->back()->with('success', 'Campaign cancelled.');
    }

    public function campaignSendTest(Request $request, $id)
    {
        $request->validate(['test_email' => 'required|email']);
        $campaign = EmailCampaign::with(['template', 'coupon'])->findOrFail($id);
        $storeName = config('app.name', 'Shop Now BD');
        $couponCode = $campaign->coupon ? $campaign->coupon->code : 'TEST10';
        $targetUrl = url('/shop');

        $htmlContent = $campaign->template ? $campaign->template->render([
            'customer_name'   => 'Valued Customer',
            'first_name'      => 'Test User',
            'email'           => $request->test_email,
            'store_name'      => $storeName,
            'coupon_code'     => $couponCode,
            'target_url'      => $targetUrl,
            'unsubscribe_url' => url('/'),
        ]) : '<p>Test Campaign Preview Content</p>';

        $htmlContent = str_replace(
            ['{coupon_code}', '{target_url}', '{unsubscribe_url}'],
            [$couponCode, $targetUrl, url('/')],
            $htmlContent
        );

        try {
            Mail::html($htmlContent, function ($msg) use ($campaign, $request) {
                $msg->to($request->test_email)
                    ->subject('[TEST] ' . $campaign->subject);
            });
            return redirect()->back()->with('success', "Test email sent successfully to {$request->test_email}.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Test mail failed: ' . $e->getMessage());
        }
    }

    public function campaignRecipients(Request $request, $id)
    {
        $campaign   = EmailCampaign::findOrFail($id);
        $recipients = EmailCampaignRecipient::where('campaign_id', $id)->paginate(50);

        return view('admin.email_marketing.campaigns.recipients', compact('campaign', 'recipients'));
    }

    /*
    |--------------------------------------------------------------------------
    | 5. EMAIL TEMPLATES
    |--------------------------------------------------------------------------
    */
    public function templatesIndex()
    {
        $templates = EmailTemplate::latest()->paginate(15);
        return view('admin.email_marketing.templates.index', compact('templates'));
    }

    public function templateCreate()
    {
        return view('admin.email_marketing.templates.create');
    }

    public function templateStore(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:191',
            'category'     => 'required|string|max:100',
            'subject'      => 'nullable|string|max:255',
            'builder_data' => 'nullable',
            'html_content' => 'required|string',
        ]);

        $template = EmailTemplate::create([
            'name'         => $validated['name'],
            'category'     => $validated['category'],
            'subject'      => $validated['subject'] ?? null,
            'builder_data' => is_string($validated['builder_data']) ? json_decode($validated['builder_data'], true) : $validated['builder_data'],
            'html_content' => $validated['html_content'],
            'created_by'   => auth()->id(),
        ]);

        return redirect()->route('admin.email_marketing.templates.index')->with('success', 'Email template created.');
    }

    public function templateEdit($id)
    {
        $template = EmailTemplate::findOrFail($id);
        return view('admin.email_marketing.templates.edit', compact('template'));
    }

    public function templateUpdate(Request $request, $id)
    {
        $template = EmailTemplate::findOrFail($id);

        $validated = $request->validate([
            'name'         => 'required|string|max:191',
            'category'     => 'required|string|max:100',
            'subject'      => 'nullable|string|max:255',
            'builder_data' => 'nullable',
            'html_content' => 'required|string',
        ]);

        $template->update([
            'name'         => $validated['name'],
            'category'     => $validated['category'],
            'subject'      => $validated['subject'] ?? null,
            'builder_data' => is_string($validated['builder_data']) ? json_decode($validated['builder_data'], true) : $validated['builder_data'],
            'html_content' => $validated['html_content'],
        ]);

        return redirect()->route('admin.email_marketing.templates.index')->with('success', 'Template updated successfully.');
    }

    public function templateDestroy($id)
    {
        $template = EmailTemplate::findOrFail($id);
        $template->delete();

        return redirect()->back()->with('success', 'Template removed.');
    }

    public function templatePreviewAjax(Request $request)
    {
        $html = $request->input('html_content', '');
        return response($html);
    }

    public function templateDuplicate($id)
    {
        $source = EmailTemplate::findOrFail($id);
        $copy = $source->replicate(['name']);
        $copy->name = $source->name . ' (Copy)';
        $copy->created_by = auth()->id();
        $copy->save();

        return redirect()->back()->with('success', 'Template duplicated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | 6. AUTOMATION & WORKFLOWS
    |--------------------------------------------------------------------------
    */
    public function automationsIndex()
    {
        $automations = EmailAutomation::withCount('steps')->latest()->paginate(15);
        return view('admin.email_marketing.automations.index', compact('automations'));
    }

    public function automationCreate()
    {
        $templates = EmailTemplate::active()->get(['id', 'name']);
        return view('admin.email_marketing.automations.create', compact('templates'));
    }

    public function automationStore(Request $request)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:191',
            'trigger_event'      => 'required|string',
            'description'        => 'nullable|string|max:500',
            'steps'              => 'required|array|min:1',
            'steps.*.template_id'=> 'nullable|exists:email_templates,id',
            'steps.*.delay'      => 'nullable|integer|min:0',
            'steps.*.subject'    => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $automation = EmailAutomation::create([
                'name'          => $validated['name'],
                'trigger_event' => $validated['trigger_event'],
                'description'   => $validated['description'] ?? null,
                'is_active'     => true,
                'created_by'    => auth()->id(),
            ]);

            foreach ($validated['steps'] as $idx => $step) {
                EmailAutomationStep::create([
                    'automation_id' => $automation->id,
                    'step_order'    => $idx + 1,
                    'step_type'     => 'send_email',
                    'delay_minutes' => $step['delay'] ?? 0,
                    'template_id'   => $step['template_id'] ?? null,
                    'subject'       => $step['subject'] ?? null,
                ]);
            }
        });

        return redirect()->route('admin.email_marketing.automations.index')->with('success', 'Automation workflow configured successfully.');
    }

    public function automationEdit($id)
    {
        $automation = EmailAutomation::with('steps')->findOrFail($id);
        $templates  = EmailTemplate::active()->get(['id', 'name']);

        return view('admin.email_marketing.automations.edit', compact('automation', 'templates'));
    }

    public function automationUpdate(Request $request, $id)
    {
        $automation = EmailAutomation::findOrFail($id);

        $validated = $request->validate([
            'name'                => 'required|string|max:191',
            'description'         => 'nullable|string|max:500',
            'steps'               => 'nullable|array|min:1',
            'steps.*.template_id' => 'nullable|exists:email_templates,id',
            'steps.*.delay'       => 'nullable|integer|min:0',
            'steps.*.subject'     => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($automation, $validated) {
            $automation->update([
                'name'        => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            if (!empty($validated['steps'])) {
                $automation->steps()->delete();
                foreach ($validated['steps'] as $idx => $step) {
                    EmailAutomationStep::create([
                        'automation_id' => $automation->id,
                        'step_order'    => $idx + 1,
                        'step_type'     => 'send_email',
                        'delay_minutes' => $step['delay'] ?? 0,
                        'template_id'   => $step['template_id'] ?? null,
                        'subject'       => $step['subject'] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('admin.email_marketing.automations.index')->with('success', 'Automation workflow updated successfully.');
    }

    public function automationDestroy($id)
    {
        $automation = EmailAutomation::findOrFail($id);
        $automation->delete();

        return redirect()->back()->with('success', 'Automation deleted.');
    }

    public function automationToggleStatus($id)
    {
        $automation = EmailAutomation::findOrFail($id);
        $automation->toggleActive();

        return redirect()->back()->with('success', 'Automation status updated.');
    }

    public function automationLogs()
    {
        $logs = EmailAutomationLog::with(['automation', 'step', 'order', 'abandonedCart'])
            ->latest()
            ->paginate(30);

        return view('admin.email_marketing.automations.logs', compact('logs'));
    }

    /*
    |--------------------------------------------------------------------------
    | 7. PRODUCT-BASED MARKETING
    |--------------------------------------------------------------------------
    */
    public function productMarketing(Request $request)
    {
        $query = Product::query();
        if (Schema::hasColumn('products', 'is_active')) {
            $query->where('is_active', true);
        } elseif (Schema::hasColumn('products', 'status')) {
            $query->where(function ($q) {
                $q->where('status', 1)->orWhere('status', 'active');
            });
        }

        $products = $query->paginate(20);
        return view('admin.email_marketing.product_marketing', compact('products'));
    }

    public function createProductCampaign(Request $request)
    {
        $productId = $request->input('product_id');
        $product = Product::findOrFail($productId);

        $orderEmailCol = collect(['customer_email', 'email', 'billing_email'])
            ->first(fn($c) => Schema::hasTable('orders') && Schema::hasColumn('orders', $c)) ?? 'customer_email';

        $orderStatusCol = collect(['order_status', 'status'])
            ->first(fn($c) => Schema::hasTable('orders') && Schema::hasColumn('orders', $c)) ?? 'order_status';

        $buyerEmails = collect();
        if (Schema::hasTable('orders') && Schema::hasTable('order_items')) {
            $buyerEmails = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('order_items.product_id', $productId)
                ->whereIn("orders.{$orderStatusCol}", ['completed', 'delivered'])
                ->whereNotNull("orders.{$orderEmailCol}")
                ->distinct()
                ->pluck("orders.{$orderEmailCol}");
        }

        return view('admin.email_marketing.campaigns.create_product_targeted', compact('product', 'buyerEmails'));
    }

    /*
    |--------------------------------------------------------------------------
    | 8. CART & WISHLIST MARKETING (ADVANCED AJAX FILTERING & BULK ACTIONS)
    |--------------------------------------------------------------------------
    */
    public function cartWishlistMarketing(Request $request)
    {
        $query = $this->buildAbandonedCartQuery($request);

        $sortBy = $request->get('sort_by', 'latest');
        match ($sortBy) {
            'oldest'         => $query->oldest('id'),
            'value_high'     => $query->orderBy('cart_value', 'desc'),
            'value_low'      => $query->orderBy('cart_value', 'asc'),
            'most_reminders' => $query->orderBy('reminder_sent_count', 'desc'),
            default          => $query->latest('id'),
        };

        $perPage = $request->get('per_page', 25);
        $abandonedCarts = ($perPage === 'all')
            ? $query->paginate(2000)->withQueryString()
            : $query->paginate((int)$perPage)->withQueryString();

        $orderTotalCol = collect(['grand_total', 'total', 'payable_amount', 'net_total', 'amount', 'total_amount', 'total_price', 'paid_amount'])
            ->first(fn($col) => Schema::hasTable('orders') && Schema::hasColumn('orders', $col));

        $recoveredRevenue = 0.0;
        if ($orderTotalCol) {
            $recoveredRevenue = (float) EmailAbandonedCart::recovered()
                ->join('orders', 'email_abandoned_carts.recovered_order_id', '=', 'orders.id')
                ->sum("orders.{$orderTotalCol}");
        } else {
            $recoveredRevenue = (float) EmailAbandonedCart::recovered()->sum('cart_value');
        }

        $stats = [
            'total'             => EmailAbandonedCart::count(),
            'recovered'         => EmailAbandonedCart::where('is_recovered', true)->count(),
            'pending'           => EmailAbandonedCart::where('is_recovered', false)->count(),
            'monitored_value'   => (float) EmailAbandonedCart::sum('cart_value'),
            'recovered_revenue' => $recoveredRevenue,
            'reminded_carts'    => EmailAbandonedCart::where('reminder_sent_count', '>', 0)->count(),
        ];

        if ($request->ajax()) {
            $viewPath = view()->exists('admin.email_marketing.cart_wishlist_partials.table_rows')
                ? 'admin.email_marketing.cart_wishlist_partials.table_rows'
                : 'admin.email_marketing.cart_wishlist.partials.table_rows';

            $tableHtml = view()->exists($viewPath)
                ? view($viewPath, compact('abandonedCarts'))->render()
                : '';

            return response()->json([
                'success'         => true,
                'table_html'      => $tableHtml,
                'pagination_html' => $abandonedCarts->hasPages() ? (string)$abandonedCarts->links('pagination::bootstrap-5') : '',
                'total_count'     => $abandonedCarts->total(),
                'stats'           => $stats,
                'entries_text'    => "Showing {$abandonedCarts->firstItem()} to {$abandonedCarts->lastItem()} of {$abandonedCarts->total()} carts",
            ]);
        }

        return view('admin.email_marketing.cart_wishlist', compact('abandonedCarts', 'stats'));
    }

    protected function buildAbandonedCartQuery(Request $request)
    {
        $query = EmailAbandonedCart::with('user');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('email', 'like', "%{$s}%")
                  ->orWhere('customer_name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->where('is_recovered', false);
            } elseif ($request->status === 'recovered') {
                $query->where('is_recovered', true);
            }
        }

        if ($request->filled('reminder_status')) {
            if ($request->reminder_status === 'never_sent') {
                $query->where(function ($q) {
                    $q->where('reminder_sent_count', 0)->orWhereNull('reminder_sent_count');
                });
            } elseif ($request->reminder_status === 'sent') {
                $query->where('reminder_sent_count', '>', 0);
            }
        }

        if ($request->filled('date_filter')) {
            match ($request->date_filter) {
                'today'        => $query->whereDate('created_at', Carbon::today()),
                'yesterday'    => $query->whereDate('created_at', Carbon::yesterday()),
                'this_week'    => $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]),
                'last_week'    => $query->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]),
                'this_month'   => $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]),
                'last_month'   => $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]),
                'this_year'    => $query->whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()]),
                default        => null,
            };
        }

        if ($request->filled('specific_date')) {
            $query->whereDate('created_at', Carbon::parse($request->specific_date)->toDateString());
        }

        if ($request->filled('specific_month')) {
            $parts = explode('-', $request->specific_month);
            if (count($parts) === 2) {
                $query->whereYear('created_at', (int)$parts[0])
                      ->whereMonth('created_at', (int)$parts[1]);
            }
        }

        if ($request->filled('specific_year')) {
            $query->whereYear('created_at', (int)$request->specific_year);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        } elseif ($request->filled('start_date')) {
            $query->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        } elseif ($request->filled('end_date')) {
            $query->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        return $query;
    }

    public function sendCartRecoveryDirect($id)
    {
        $cart = EmailAbandonedCart::findOrFail($id);

        if (empty($cart->email) || str_contains($cart->email, '@guest.') || str_contains($cart->email, '@temporary.') || !filter_var($cart->email, FILTER_VALIDATE_EMAIL)) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Valid customer email address not found.'], 422);
            }
            return redirect()->back()->with('error', 'Valid customer email address not found.');
        }

        try {
            $recoveryUrl = $cart->recovery_url;
            $items = is_array($cart->cart_items) ? $cart->cart_items : json_decode($cart->cart_items, true);
            $itemsHtml = '';

            if (!empty($items) && is_array($items)) {
                foreach ($items as $it) {
                    $pName  = htmlspecialchars($it['name'] ?? ($it['title'] ?? 'Product'));
                    $pQty   = (int)($it['quantity'] ?? 1);
                    $pPrice = number_format((float)($it['price'] ?? 0), 2);
                    $itemsHtml .= "<tr>
                        <td style='padding: 10px; border-bottom: 1px solid #f1f5f9; color: #334155;'>{$pName}</td>
                        <td style='padding: 10px; border-bottom: 1px solid #f1f5f9; text-align: center; color: #64748b;'>{$pQty}</td>
                        <td style='padding: 10px; border-bottom: 1px solid #f1f5f9; text-align: right; font-weight: bold; color: #0f172a;'>৳ {$pPrice}</td>
                    </tr>";
                }
            }

            $storeName = config('app.name', 'Shop Now BD');

            $template = EmailTemplate::where('category', 'abandoned_cart')
                ->orWhere('name', 'like', '%abandoned%')
                ->orWhere('name', 'like', '%recovery%')
                ->latest()
                ->first();

            $cName  = htmlspecialchars($cart->customer_name ?: 'Valued Customer');
            $cTotal = number_format((float)$cart->cart_value, 2);

            if ($template && !empty($template->html_content)) {
                $htmlContent = $template->render([
                    'customer_name' => $cName,
                    'email'         => $cart->email,
                    'cart_value'    => $cTotal,
                    'recovery_url'  => $recoveryUrl,
                    'cart_items'    => $itemsHtml,
                    'store_name'    => $storeName,
                ]);
            } else {
                $htmlContent = "
                <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 25px; border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff;'>
                    <div style='text-align: center; margin-bottom: 25px;'>
                        <h2 style='color: #0f172a; margin-bottom: 6px; font-weight: 800;'>{$storeName}</h2>
                        <p style='color: #64748b; font-size: 14px; margin-top: 0;'>Did you forget something in your shopping cart?</p>
                    </div>
                    <p style='color: #1e293b; font-size: 15px;'>Hello <strong>{$cName}</strong>,</p>
                    <p style='color: #475569; font-size: 14px; line-height: 1.6;'>
                        We noticed you left items in your shopping cart without completing your order. We have reserved them for you so you can easily complete your purchase!
                    </p>
                    <div style='margin: 22px 0; border: 1px solid #f1f5f9; border-radius: 8px; overflow: hidden;'>
                        <table style='width: 100%; border-collapse: collapse; font-size: 13.5px;'>
                            <thead>
                                <tr style='background-color: #f8fafc; color: #475569;'>
                                    <th style='padding: 10px; text-align: left; border-bottom: 2px solid #e2e8f0;'>Item</th>
                                    <th style='padding: 10px; text-align: center; border-bottom: 2px solid #e2e8f0;'>Qty</th>
                                    <th style='padding: 10px; text-align: right; border-bottom: 2px solid #e2e8f0;'>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                {$itemsHtml}
                            </tbody>
                            <tfoot>
                                <tr style='background-color: #f8fafc;'>
                                    <td colspan='2' style='padding: 12px 10px; text-align: right; font-weight: bold; color: #1e293b;'>Total:</td>
                                    <td style='padding: 12px 10px; text-align: right; font-weight: 800; color: #2563eb;'>৳ {$cTotal}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$recoveryUrl}' style='background-color: #f59e0b; color: #ffffff; padding: 13px 32px; text-decoration: none; font-size: 15px; font-weight: bold; border-radius: 6px; display: inline-block; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.35);'>
                            Complete Your Purchase Now &rarr;
                        </a>
                    </div>
                    <p style='color: #94a3b8; font-size: 12px; text-align: center; margin-top: 30px; border-top: 1px solid #f1f5f9; padding-top: 15px; line-height: 1.5;'>
                        If you already completed this order, please disregard this email.<br>
                        &copy; " . date('Y') . " {$storeName}. All rights reserved.
                    </p>
                </div>";
            }

            Mail::html($htmlContent, function ($msg) use ($cart, $storeName) {
                $msg->to($cart->email)
                    ->subject("Complete your order at {$storeName} - Items waiting in your cart!");
            });

            $cart->recordReminderSent();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Recovery reminder dispatched successfully to {$cart->email}."
                ]);
            }

            return redirect()->back()->with('success', "Recovery reminder email dispatched successfully to {$cart->email}.");
        } catch (\Throwable $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => "Failed: " . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', "Failed to dispatch recovery email: " . $e->getMessage());
        }
    }

    public function cartWishlistBulkDelete(Request $request)
    {
        $validated = $request->validate([
            'cart_ids'   => 'required|array|min:1',
            'cart_ids.*' => 'integer|exists:email_abandoned_carts,id',
        ]);

        $deletedCount = EmailAbandonedCart::whereIn('id', $validated['cart_ids'])->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} abandoned cart session(s) deleted successfully."
        ]);
    }

    public function cartWishlistBulkSend(Request $request)
    {
        $validated = $request->validate([
            'cart_ids'   => 'required|array|min:1',
            'cart_ids.*' => 'integer|exists:email_abandoned_carts,id',
            'subject'    => 'required|string|max:255',
            'message'    => 'required|string',
        ]);

        $carts = EmailAbandonedCart::whereIn('id', $validated['cart_ids'])
            ->where('is_recovered', false)
            ->get();

        if ($carts->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No pending eligible carts selected.'], 422);
        }

        $storeName = config('app.name', 'Shop Now BD');
        $sentCount = 0;
        $failCount = 0;

        foreach ($carts as $cart) {
            $email = strtolower(trim($cart->email));

            if (empty($email) || str_contains($email, '@guest.') || str_contains($email, '@temporary.') || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $failCount++;
                continue;
            }

            if (EmailSuppressionList::isSuppressed($email)) {
                $failCount++;
                continue;
            }

            $cName       = htmlspecialchars($cart->customer_name ?: 'Valued Customer');
            $recoveryUrl = $cart->recovery_url;
            $cTotal      = number_format((float)$cart->cart_value, 2);

            $emailBody = str_replace(
                ['{name}', '{customer_name}', '{first_name}', '{email}', '{store_name}', '{recovery_url}', '{cart_value}'],
                [$cName, $cName, $cName, $email, $storeName, $recoveryUrl, $cTotal],
                $validated['message']
            );

            if (!str_contains($emailBody, $recoveryUrl)) {
                $emailBody .= "<div style='text-align: center; margin: 25px 0;'>
                    <a href='{$recoveryUrl}' style='background-color: #f59e0b; color: #ffffff; padding: 12px 28px; text-decoration: none; font-size: 14px; font-weight: bold; border-radius: 6px; display: inline-block;'>Complete Purchase &rarr;</a>
                </div>";
            }

            try {
                Mail::html($emailBody, function ($msg) use ($email, $validated) {
                    $msg->to($email)->subject($validated['subject']);
                });
                $cart->recordReminderSent();
                $sentCount++;
            } catch (\Throwable $e) {
                $failCount++;
            }

            usleep(25000);
        }

        return response()->json([
            'success' => true,
            'message' => "Bulk recovery reminder processed: {$sentCount} sent successfully, {$failCount} failed."
        ]);
    }

    public function cartWishlistDestroy(Request $request, $id)
    {
        $cart = EmailAbandonedCart::findOrFail($id);
        $cart->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Cart record #{$id} deleted successfully."
            ]);
        }

        return redirect()->back()->with('success', "Cart record #{$id} deleted.");
    }

    public function cartWishlistExport(Request $request)
    {
        $fileName = 'abandoned_carts_' . date('Y-m-d_His') . '.csv';
        $query = $this->buildAbandonedCartQuery($request);
        $carts = $query->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0",
        ];

        return response()->stream(function () use ($carts) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Customer Name', 'Email', 'Phone', 'Cart Value (BDT)', 'Items Count', 'Status', 'Reminders Sent', 'Abandoned Date', 'Last Reminder At']);
            foreach ($carts as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->customer_name ?: 'Guest',
                    $row->email,
                    $row->phone,
                    number_format((float)$row->cart_value, 2),
                    $row->items_count,
                    $row->is_recovered ? 'Recovered' : 'Pending',
                    $row->reminder_sent_count ?? 0,
                    $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '',
                    $row->last_reminder_at ? $row->last_reminder_at->format('Y-m-d H:i:s') : 'Never',
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    /*
    |--------------------------------------------------------------------------
    | 9. CUSTOMER GROUPS, SCHEDULED & QUEUE LOGS
    |--------------------------------------------------------------------------
    */
    public function customerGroups()
    {
        return view('admin.email_marketing.customer_groups');
    }

    public function scheduledCampaigns()
    {
        $scheduled = EmailCampaign::where('status', 'scheduled')->latest()->paginate(20);
        return view('admin.email_marketing.scheduled', compact('scheduled'));
    }

    public function queueLogs()
    {
        $queueItems = EmailCampaignRecipient::with('campaign')->latest()->paginate(30);
        return view('admin.email_marketing.queue_logs', compact('queueItems'));
    }

    /*
    |--------------------------------------------------------------------------
    | 10. ANALYTICS, A/B TESTING & PROMOTIONS
    |--------------------------------------------------------------------------
    */
    public function analytics()
    {
        $events = EmailTrackingEvent::with(['campaign', 'automation'])->latest()->paginate(40);
        return view('admin.email_marketing.analytics', compact('events'));
    }

    public function analyticsExport()
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Email', 'Event Type', 'Target URL', 'IP Address', 'Timestamp']);
            EmailTrackingEvent::chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $r) {
                    fputcsv($handle, [$r->email, $r->event_type, $r->target_url, $r->ip_address, $r->created_at]);
                }
            });
            fclose($handle);
        }, 'email_tracking_export_' . date('Y-m-d') . '.csv');
    }

    public function abTestingIndex()
    {
        return view('admin.email_marketing.ab_testing');
    }

    public function abTestingStore(Request $request)
    {
        return redirect()->back()->with('success', 'A/B test initialized.');
    }

    public function couponsIndex()
    {
        $coupons = Coupon::latest()->paginate(20);
        return view('admin.email_marketing.coupons', compact('coupons'));
    }

    public function createCouponCampaign(Request $request)
    {
        return redirect()->route('admin.email_marketing.campaigns.create', ['coupon_id' => $request->coupon_id]);
    }

    /*
    |--------------------------------------------------------------------------
    | 11. SUPPRESSION & UNSUBSCRIBE LIST
    |--------------------------------------------------------------------------
    */
    public function suppressionIndex()
    {
        $suppressions = EmailSuppressionList::latest()->paginate(25);
        return view('admin.email_marketing.suppression', compact('suppressions'));
    }

    public function suppressionStore(Request $request)
    {
        $request->validate([
            'email'  => 'required|email|max:191',
            'reason' => 'required|string',
        ]);

        EmailSuppressionList::suppress($request->email, $request->reason, $request->details, auth()->id());
        return redirect()->back()->with('success', 'Email added to suppression list.');
    }

    public function suppressionDestroy($id)
    {
        $item = EmailSuppressionList::findOrFail($id);
        EmailSuppressionList::unsuppress($item->email);

        return redirect()->back()->with('success', 'Email removed from suppression blacklist.');
    }

    /*
    |--------------------------------------------------------------------------
    | 12. PROVIDER, TRACKING & GENERAL SETTINGS
    |--------------------------------------------------------------------------
    */
    public function providersIndex()
    {
        $providers = EmailProviderSetting::all();
        return view('admin.email_marketing.providers', compact('providers'));
    }

    public function providerStore(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:100',
            'driver'       => 'required|in:smtp,ses,brevo,mailgun,sendgrid,postmark',
            'host'         => 'nullable|string',
            'port'         => 'nullable|integer',
            'encryption'   => 'nullable|string',
            'username'     => 'nullable|string',
            'password'     => 'nullable|string',
            'api_key'      => 'nullable|string',
            'sender_name'  => 'nullable|string|max:100',
            'sender_email' => 'nullable|email|max:150',
        ]);

        EmailProviderSetting::create($validated);
        return redirect()->back()->with('success', 'Email provider created.');
    }

    public function providerUpdate(Request $request, $id)
    {
        $provider = EmailProviderSetting::findOrFail($id);
        $validated = $request->validate([
            'name'         => 'required|string|max:100',
            'driver'       => 'required|in:smtp,ses,brevo,mailgun,sendgrid,postmark',
            'host'         => 'nullable|string',
            'port'         => 'nullable|integer',
            'encryption'   => 'nullable|string',
            'username'     => 'nullable|string',
            'password'     => 'nullable|string',
            'api_key'      => 'nullable|string',
            'sender_name'  => 'nullable|string|max:100',
            'sender_email' => 'nullable|email|max:150',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }
        if (empty($validated['api_key'])) {
            unset($validated['api_key']);
        }

        $provider->update($validated);
        return redirect()->back()->with('success', 'Provider credentials updated securely.');
    }

    public function providerDestroy($id)
    {
        $provider = EmailProviderSetting::findOrFail($id);

        if ($provider->is_active && EmailProviderSetting::count() > 1) {
            $another = EmailProviderSetting::where('id', '!=', $id)->first();
            if ($another) {
                $another->makeActive();
            }
        }

        $provider->delete();
        return redirect()->back()->with('success', 'Email delivery provider removed successfully.');
    }

    public function providerMakeActive($id)
    {
        $provider = EmailProviderSetting::findOrFail($id);
        $provider->makeActive();

        return redirect()->back()->with('success', "{$provider->name} is now the active sending mailer.");
    }

    public function providerTestConnection(Request $request)
    {
        $request->validate(['test_email' => 'required|email']);
        try {
            Mail::raw('This is a connection verification test from your Advanced Email Marketing Suite.', function ($msg) use ($request) {
                $msg->to($request->test_email)->subject('SMTP & Provider Connection Verified');
            });
            return response()->json(['success' => true, 'message' => 'Connection verified successfully.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function trackingSettings()
    {
        return view('admin.email_marketing.tracking_settings');
    }

    public function updateTrackingSettings(Request $request)
    {
        return redirect()->back()->with('success', 'Tracking & consent parameters saved.');
    }

    public function settingsIndex()
    {
        return view('admin.email_marketing.settings');
    }

    public function updateSettings(Request $request)
    {
        return redirect()->back()->with('success', 'Email marketing settings saved.');
    }

    /*
    |--------------------------------------------------------------------------
    | 13. PUBLIC TRACKING, RECOVERY & UNSUBSCRIBE HANDLERS
    |--------------------------------------------------------------------------
    */
    public function trackOpen($token)
    {
        $recipient = EmailCampaignRecipient::where('tracking_token', $token)->first();
        if ($recipient) {
            $recipient->recordOpen();
            EmailTrackingEvent::logEvent('open', $recipient->email, [
                'campaign_id'  => $recipient->campaign_id,
                'recipient_id' => $recipient->id,
            ]);
        }

        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        return response($pixel, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }

    public function trackClick(Request $request, $token)
    {
        $recipient = EmailCampaignRecipient::where('tracking_token', $token)->first();
        $targetUrl = $request->get('target', url('/'));

        if ($recipient) {
            $recipient->recordClick();
            EmailTrackingEvent::logEvent('click', $recipient->email, [
                'campaign_id'  => $recipient->campaign_id,
                'recipient_id' => $recipient->id,
                'target_url'   => $targetUrl,
            ]);
        }

        return redirect()->away($targetUrl);
    }
public function unsubscribePage($token)
    {
        $subscriber = EmailSubscriber::where('unsubscribe_token', $token)->firstOrFail();
        
        $view = view()->exists('frontend.email.unsubscribe')
            ? 'frontend.email.unsubscribe'
            : (view()->exists('emails.unsubscribe') ? 'emails.unsubscribe' : null);

        if ($view) {
            return view($view, compact('subscriber', 'token'));
        }

        return response()->view('emails.unsubscribe', compact('subscriber', 'token'));
    }

    public function processUnsubscribe(Request $request, $token)
    {
        $subscriber = EmailSubscriber::where('unsubscribe_token', $token)->firstOrFail();
        $reason = $request->input('reason', 'User requested unsubscription');

        $subscriber->unsubscribe($reason);
        if (class_exists(EmailConsentLog::class)) {
            EmailConsentLog::logConsent($subscriber->email, 'opt_out', 'one_click_unsubscribe', $subscriber->id);
        }

        if ($request->boolean('block_all') && class_exists(EmailSuppressionList::class)) {
            EmailSuppressionList::suppress($subscriber->email, 'unsubscribe', $reason);
        }

        $view = view()->exists('frontend.email.unsubscribe_success')
            ? 'frontend.email.unsubscribe_success'
            : (view()->exists('emails.unsubscribe_success') ? 'emails.unsubscribe_success' : null);

        if ($view) {
            return view($view, compact('subscriber'));
        }

        return redirect()->route('home')->with('success', 'You have been successfully unsubscribed.');
    }

    public function recoverCart($token)
    {
        $cart = EmailAbandonedCart::where('recovery_token', $token)
            ->where('is_recovered', false)
            ->firstOrFail();

        if (!empty($cart->cart_items)) {
            $items = is_array($cart->cart_items) ? $cart->cart_items : json_decode($cart->cart_items, true);
            if (is_array($items)) {
                $restoredSession = [];
                foreach ($items as $idx => $it) {
                    $key = $it['product_id'] ?? ($it['id'] ?? $idx);
                    $restoredSession[$key] = [
                        'id'             => $it['product_id'] ?? ($it['id'] ?? $key),
                        'product_id'     => $it['product_id'] ?? ($it['id'] ?? $key),
                        'title'          => $it['name'] ?? ($it['title'] ?? 'Product'),
                        'name'           => $it['name'] ?? ($it['title'] ?? 'Product'),
                        'price'          => (float)($it['price'] ?? 0),
                        'quantity'       => (int)($it['quantity'] ?? 1),
                        'image'          => $it['image'] ?? null,
                        'color'          => $it['color'] ?? null,
                        'size'           => $it['size'] ?? null,
                    ];
                }
                Session::put('cart', $restoredSession);
            }
        }

        return redirect()->route('cart.index')->with('success', 'Your cart has been restored successfully!');
    }
}