<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SecurityAlert;
use App\Models\SecurityBlockedIp;
use App\Models\SecurityBlockedNetwork;
use App\Models\SecurityIpLog;
use App\Models\SecurityUrlAttack;
use App\Services\Security\IpBlockService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SecurityDashboardController extends Controller
{
    protected IpBlockService $blockService;

    public function __construct(IpBlockService $blockService)
    {
        $this->blockService = $blockService;
    }

    /**
     * Dashboard live analytics and metrics
     */
    public function index(Request $request): View|JsonResponse
    {
        // 1. Date Range & Traffic Scope (Default 'all' so local testing data shows up instantly)
        [$startDate, $endDate, $filterPeriod] = $this->resolveDateRange($request);
        $trafficType = $request->get('traffic_type', 'all'); // all | visitors | staff

        // 2. Optimized Metrics
        $metrics = $this->calculateMetrics($startDate, $endDate, $trafficType);

        // 3. Most Attacked URLs
        $mostAttackedUrls = SecurityUrlAttack::orderByDesc('total_requests')->limit(6)->get();

        // 4. Most Active IPs Query
        $activeIpsQuery = SecurityIpLog::query()->whereBetween('created_at', [$startDate, $endDate]);
        $this->applyTrafficScopeFilter($activeIpsQuery, $trafficType);

        $mostActiveIps = $activeIpsQuery
            ->select(
                'ip_address',
                DB::raw('MAX(country) as country'),
                DB::raw('MAX(browser) as browser'),
                DB::raw('MAX(platform) as platform'),
                DB::raw('COUNT(*) as total_requests'),
                DB::raw('MAX(risk_score) as max_risk')
            )
            ->groupBy('ip_address')
            ->orderByDesc('total_requests')
            ->limit(6)
            ->get();

        $mostActiveIps->transform(function ($item) {
            $item->is_blocked = $this->blockService->isBlocked($item->ip_address);
            return $item;
        });

        // 5. Recent Alerts & Blocks
        $recentAlerts = SecurityAlert::latest('id')->limit(8)->get();
        $recentlyBlockedIps = SecurityBlockedIp::with('creator')->latest('id')->limit(6)->get();

        // 6. Chart Time-Series Data
        $chartData = $this->generateChartTimeSeries($startDate, $endDate, $trafficType);

        // AJAX JSON Response
        if ($request->ajax()) {
            return response()->json([
                'success'         => true,
                'period'          => $filterPeriod,
                'traffic_type'    => $trafficType,
                'date_range_text' => $startDate->format('M d, Y') . ' — ' . $endDate->format('M d, Y'),
                'metrics'         => $metrics,
                'chart_data'      => $chartData,
                'most_active_ips' => $mostActiveIps,
            ]);
        }

        return view('admin.security.dashboard', compact(
            'metrics',
            'chartData',
            'mostAttackedUrls',
            'mostActiveIps',
            'recentAlerts',
            'recentlyBlockedIps',
            'filterPeriod',
            'trafficType',
            'startDate',
            'endDate'
        ));
    }

    protected function resolveDateRange(Request $request): array
    {
        $period = $request->get('period', 'today');
        $now = Carbon::now();

        switch ($period) {
            case 'yesterday':
                $start = Carbon::yesterday()->startOfDay();
                $end = Carbon::yesterday()->endOfDay();
                break;
            case '7days':
                $start = $now->copy()->subDays(6)->startOfDay();
                $end = $now->endOfDay();
                break;
            case '30days':
                $start = $now->copy()->subDays(29)->startOfDay();
                $end = $now->endOfDay();
                break;
            case 'this_month':
                $start = $now->copy()->startOfMonth();
                $end = $now->endOfDay();
                break;
            case 'this_year':
                $start = $now->copy()->startOfYear();
                $end = $now->endOfDay();
                break;
            case 'custom':
                $start = $request->filled('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : $now->copy()->startOfDay();
                $end = $request->filled('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : $now->copy()->endOfDay();
                break;
            case 'today':
            default:
                $period = 'today';
                $start = $now->copy()->startOfDay();
                $end = $now->endOfDay();
                break;
        }

        return [$start, $end, $period];
    }

    protected function calculateMetrics(Carbon $start, Carbon $end, string $trafficType = 'all'): array
    {
        $logQuery = SecurityIpLog::query()->whereBetween('created_at', [$start, $end]);
        $this->applyTrafficScopeFilter($logQuery, $trafficType);

        $logStats = $logQuery->selectRaw("
            COUNT(*) as total_requests,
            COUNT(DISTINCT ip_address) as total_unique_ips,
            COUNT(DISTINCT CASE WHEN activity_type = 'visit' THEN ip_address END) as total_unique_visitors,
            COUNT(DISTINCT CASE WHEN risk_score >= 40 THEN ip_address END) as total_suspicious_ips,
            COUNT(CASE WHEN security_status = 'flagged' OR risk_score >= 50 THEN 1 END) as total_spam_attempts,
            COUNT(CASE WHEN activity_type = 'login_failed' THEN 1 END) as failed_logins,
            COUNT(CASE WHEN activity_type = 'login_success' THEN 1 END) as successful_logins,
            COUNT(CASE WHEN activity_type = 'form_submit' THEN 1 END) as form_submissions,
            COUNT(CASE WHEN activity_type = 'form_submit' AND risk_score >= 50 THEN 1 END) as suspicious_forms
        ")->first();

        $totalBlockedIps = SecurityBlockedIp::where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', Carbon::now());
        })->count();

        $totalBlockedNetworks = SecurityBlockedNetwork::where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', Carbon::now());
        })->count();

        $suspiciousOrders = Order::whereBetween('created_at', [$start, $end])
            ->where(function ($q) {
                $q->where('is_suspicious', true)
                  ->orWhere('security_risk_score', '>=', 40)
                  ->orWhereIn('security_review_status', ['under_review', 'flagged_spam', 'cancelled_fraud']);
            })
            ->count();

        $bruteForceAttempts = (int) SecurityAlert::whereBetween('created_at', [$start, $end])
            ->where('event_type', 'brute_force')
            ->sum('attempt_count');

        $activeAlerts = method_exists(SecurityAlert::class, 'scopeUnread')
            ? SecurityAlert::unread()->count()
            : SecurityAlert::where('is_read', false)->count();

        return [
            'total_unique_ips'        => (int) ($logStats->total_unique_ips ?? 0),
            'total_unique_visitors'   => (int) ($logStats->total_unique_visitors ?? 0),
            'total_requests'          => (int) ($logStats->total_requests ?? 0),
            'total_blocked_ips'       => $totalBlockedIps,
            'total_blocked_networks'  => $totalBlockedNetworks,
            'total_suspicious_ips'    => (int) ($logStats->total_suspicious_ips ?? 0),
            'total_spam_attempts'     => (int) ($logStats->total_spam_attempts ?? 0),
            'total_suspicious_orders' => $suspiciousOrders,
            'total_form_submissions'  => (int) ($logStats->form_submissions ?? 0),
            'total_suspicious_forms'  => (int) ($logStats->suspicious_forms ?? 0),
            'failed_logins'           => (int) ($logStats->failed_logins ?? 0),
            'successful_logins'       => (int) ($logStats->successful_logins ?? 0),
            'brute_force_attempts'    => $bruteForceAttempts,
            'active_security_alerts'  => $activeAlerts,
        ];
    }

    protected function generateChartTimeSeries(Carbon $start, Carbon $end, string $trafficType = 'all'): array
    {
        $daysDiff = $start->diffInDays($end);

        if ($daysDiff <= 1) {
            $format = '%Y-%m-%d %H:00:00';
            $displayFormat = 'h A';
        } else {
            $format = '%Y-%m-%d';
            $displayFormat = 'M d';
        }

        $seriesQuery = SecurityIpLog::query()->whereBetween('created_at', [$start, $end]);
        $this->applyTrafficScopeFilter($seriesQuery, $trafficType);

        $requests = $seriesQuery
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$format}') as time_slot"),
                DB::raw("COUNT(*) as total_req"),
                DB::raw("SUM(CASE WHEN security_status = 'blocked' THEN 1 ELSE 0 END) as blocked_req"),
                DB::raw("SUM(CASE WHEN risk_score >= 50 THEN 1 ELSE 0 END) as spam_req")
            )
            ->groupBy('time_slot')
            ->orderBy('time_slot')
            ->get()
            ->keyBy('time_slot');

        $labels = [];
        $totalData = [];
        $blockedData = [];
        $spamData = [];

        $current = $start->copy();
        while ($current <= $end) {
            $slotKey = $daysDiff <= 1 ? $current->format('Y-m-d H:00:00') : $current->format('Y-m-d');
            $labels[] = $current->format($displayFormat);

            $matched = $requests->get($slotKey);
            $totalData[] = $matched ? (int) $matched->total_req : 0;
            $blockedData[] = $matched ? (int) $matched->blocked_req : 0;
            $spamData[] = $matched ? (int) $matched->spam_req : 0;

            if ($daysDiff <= 1) {
                $current->addHour();
            } else {
                $current->addDay();
            }
        }

        return [
            'labels'      => $labels,
            'total_req'   => $totalData,
            'blocked_req' => $blockedData,
            'spam_req'    => $spamData,
        ];
    }

    protected function applyTrafficScopeFilter(Builder $query, string $scope): void
    {
        if ($scope === 'visitors') {
            $query->where(function ($q) {
                $q->whereNull('user_type')
                  ->orWhereNotIn('user_type', ['admin', 'staff', 'super_admin']);
            })
            ->where('url_path', 'not like', '/admin%')
            ->where('url_path', 'not like', '/dashboard/auth%');
        } elseif ($scope === 'staff') {
            $query->where(function ($q) {
                $q->whereIn('user_type', ['admin', 'staff', 'super_admin'])
                  ->orWhere('url_path', 'like', '/admin%')
                  ->orWhere('url_path', 'like', '/dashboard/auth%');
            });
        }
    }
}