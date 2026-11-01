<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SecurityAlert;
use App\Models\SecurityBlockedIp;
use App\Models\SecurityIpLog;
use App\Services\Security\IpBlockService;
use App\Services\Security\IpDetectionService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecurityIpManagementController extends Controller
{
    protected IpBlockService $blockService;
    protected IpDetectionService $ipDetector;

    public function __construct(IpBlockService $blockService, IpDetectionService $ipDetector)
    {
        $this->blockService = $blockService;
        $this->ipDetector = $ipDetector;
    }

    /**
     * Shokol IP thikanar talika, filter ebong live search prodorshon
     */
    public function index(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;

        // IP bhittik aggregation query
        $query = SecurityIpLog::query()
            ->select(
                'ip_address',
                DB::raw('MAX(ip_version) as ip_version'),
                DB::raw('MAX(country) as country'),
                DB::raw('MAX(device_type) as device_type'),
                DB::raw('MAX(browser) as browser'),
                DB::raw('MAX(platform) as platform'),
                DB::raw('COUNT(*) as total_requests'),
                DB::raw('MAX(risk_score) as risk_score'),
                DB::raw('MAX(created_at) as last_activity'),
                DB::raw("SUM(CASE WHEN activity_type = 'order' THEN 1 ELSE 0 END) as total_orders"),
                DB::raw("SUM(CASE WHEN activity_type = 'form_submit' THEN 1 ELSE 0 END) as total_forms"),
                DB::raw("SUM(CASE WHEN activity_type = 'login_failed' THEN 1 ELSE 0 END) as failed_logins"),
                DB::raw("SUM(CASE WHEN risk_score >= 50 OR security_status = 'flagged' THEN 1 ELSE 0 END) as suspicious_attempts"),
                DB::raw("MAX(user_type) as primary_user_type")
            )
            ->groupBy('ip_address');

        // 1. Admin/Staff Traffic Scope Filter (Default: Shudhu public visitor traffic)
        $this->applyTrafficScopeFilter($query, $request->get('traffic_type', 'visitors'));

        // 2. Search filter (IP ba Desh)
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        // 3. IP Version filter (IPv4 / IPv6)
        if ($request->filled('ip_version')) {
            $query->where('ip_version', $request->get('ip_version'));
        }

        // 4. Device Type filter
        if ($request->filled('device_type')) {
            $query->where('device_type', $request->get('device_type'));
        }

        // 5. Risk Score filter
        if ($request->filled('risk_level')) {
            match ($request->get('risk_level')) {
                'high'   => $query->having('risk_score', '>=', 75),
                'medium' => $query->having('risk_score', '>=', 40)->having('risk_score', '<', 75),
                'low'    => $query->having('risk_score', '<', 40),
                default  => null,
            };
        }

        // 6. Date Range filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = Carbon::parse($request->get('start_date'))->startOfDay();
            $end = Carbon::parse($request->get('end_date'))->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        }

        // 7. Sorting
        $sortColumn = $request->get('sort_by', 'last_activity');
        $sortDirection = $request->get('sort_dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['last_activity', 'total_requests', 'risk_score', 'total_orders', 'failed_logins'];

        if (in_array($sortColumn, $allowedSorts, true)) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderByDesc('last_activity');
        }

        $ips = $query->paginate($perPage)->withQueryString();

        // IP Block status ebong dynamic country resolution
        $ips->getCollection()->transform(function ($item) {
            $item->is_blocked = $this->blockService->isBlocked($item->ip_address);
            if (empty($item->country) || in_array($item->country, ['Unknown', 'Localhost (BD)', 'N/A'], true)) {
                $item->country = $this->resolveRealCountry($item->ip_address, false);
            }
            return $item;
        });

        // AJAX request hole JSON return
        if ($request->ajax()) {
            return response()->json([
                'success'    => true,
                'html'       => view('admin.security.partials.ip_table_rows', compact('ips'))->render(),
                'pagination' => (string) $ips->links('pagination::bootstrap-5'),
                'total'      => $ips->total(),
            ]);
        }

        return view('admin.security.ips.index', compact('ips', 'perPage'));
    }

    /**
     * Nirdishto IP-er jonno purnango Details Page o Timeline View
     */
    public function show(string $ip): View
    {
        $ip = trim($ip);

        // Country missing ba Unknown thakle dynamic country resolve kore DB update
        $latestLog = SecurityIpLog::where('ip_address', $ip)->latest('created_at')->first();
        if ($latestLog && (empty($latestLog->country) || in_array($latestLog->country, ['Unknown', 'Localhost (BD)', 'N/A'], true))) {
            $realCountry = $this->resolveRealCountry($ip, true);
            if (!empty($realCountry) && !in_array($realCountry, ['Unknown', 'Localhost'], true)) {
                SecurityIpLog::where('ip_address', $ip)->update(['country' => $realCountry]);
            }
            $latestLog->country = $realCountry;
        }

        // IP-r visitor-specific shomogro statistics (admin working clicks exclude kora)
        $summary = SecurityIpLog::where('ip_address', $ip)
            ->where(function ($q) {
                $q->whereNull('user_type')
                  ->orWhereNotIn('user_type', ['admin', 'staff', 'super_admin']);
            })
            ->where('url_path', 'not like', '/admin%')
            ->select(
                DB::raw('COUNT(*) as total_requests'),
                DB::raw('MAX(risk_score) as max_risk_score'),
                DB::raw('MIN(created_at) as first_seen'),
                DB::raw('MAX(created_at) as last_seen'),
                DB::raw("SUM(CASE WHEN activity_type = 'order' THEN 1 ELSE 0 END) as total_orders"),
                DB::raw("SUM(CASE WHEN activity_type = 'form_submit' THEN 1 ELSE 0 END) as total_forms"),
                DB::raw("SUM(CASE WHEN activity_type = 'login_failed' THEN 1 ELSE 0 END) as failed_logins"),
                DB::raw("SUM(CASE WHEN security_status = 'blocked' THEN 1 ELSE 0 END) as blocked_requests")
            )
            ->first();

        // Jodi visitor summary empty hoy (jemon: purely staff working IP), tobe total overview load hobe
        if (!$summary || $summary->total_requests == 0) {
            $summary = SecurityIpLog::where('ip_address', $ip)
                ->select(
                    DB::raw('COUNT(*) as total_requests'),
                    DB::raw('MAX(risk_score) as max_risk_score'),
                    DB::raw('MIN(created_at) as first_seen'),
                    DB::raw('MAX(created_at) as last_seen'),
                    DB::raw("SUM(CASE WHEN activity_type = 'order' THEN 1 ELSE 0 END) as total_orders"),
                    DB::raw("SUM(CASE WHEN activity_type = 'form_submit' THEN 1 ELSE 0 END) as total_forms"),
                    DB::raw("SUM(CASE WHEN activity_type = 'login_failed' THEN 1 ELSE 0 END) as failed_logins"),
                    DB::raw("SUM(CASE WHEN security_status = 'blocked' THEN 1 ELSE 0 END) as blocked_requests")
                )
                ->first();
        }

        // Block history o bortoman blocking obostha
        $blockDetails = $this->blockService->getBlockReasonDetails($ip);
        $blockRecord = SecurityBlockedIp::where('ip_address', $ip)->latest('id')->first();

        // Shongshlishto Order talika
        $orders = Order::where('client_ip', $ip)->latest('id')->limit(10)->get();

        // Shongshlishto Security Alert
        $alerts = SecurityAlert::where('ip_address', $ip)->latest('id')->limit(10)->get();

        // Activity timeline (shorboshesh 50-ti request)
        $timeline = SecurityIpLog::where('ip_address', $ip)
            ->latest('created_at')
            ->limit(50)
            ->get();

        // Top requested URLs (Public routes prioritizing)
        $topUrls = SecurityIpLog::where('ip_address', $ip)
            ->select('url_path', DB::raw('COUNT(*) as visit_count'), DB::raw('MAX(response_status) as last_status'))
            ->groupBy('url_path')
            ->orderByDesc('visit_count')
            ->limit(8)
            ->get();

        return view('admin.security.ips.show', compact(
            'ip',
            'summary',
            'latestLog',
            'blockDetails',
            'blockRecord',
            'orders',
            'alerts',
            'timeline',
            'topUrls'
        ));
    }

    /**
     * IP shorashori Block kora
     */
    public function block(Request $request): JsonResponse
    {
        $request->validate([
            'ip_address'       => 'required|string',
            'block_type'       => 'nullable|in:temporary,permanent',
            'duration_minutes' => 'nullable|integer',
            'block_reason'     => 'nullable|string|max:255',
            'admin_note'       => 'nullable|string|max:500',
        ]);

        $ip = trim($request->input('ip_address'));
        $blockType = $request->input('block_type', 'temporary');
        $duration = (int) $request->input('duration_minutes', 1440);
        if ($duration <= 0 && $blockType === 'temporary') {
            $duration = 1440;
        }

        $reason = $request->input('block_reason')
            ?: ($request->input('reason') ?: 'Direct block enforced by administrator');
        $adminNote = $request->input('admin_note', 'Restricted via IP Management interface');
        $adminId = $request->user()?->id;

        $result = $this->blockService->blockIp(
            $ip,
            $blockType,
            $duration,
            $reason,
            'manual',
            $adminId,
            $adminNote
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * IP shorashori Unblock kora
     */
    public function unblock(Request $request): JsonResponse
    {
        $request->validate([
            'ip_address' => 'required|string',
            'note'       => 'nullable|string|max:255',
        ]);

        $ip = trim($request->input('ip_address'));
        $note = $request->input('note', 'Unblocked from admin IP management');

        $result = $this->blockService->unblockIp(
            $ip,
            $note,
            $request->user()?->id
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * IP bhittik filtered record CSV export
     */
    public function export(Request $request): StreamedResponse
    {
        $fileName = 'security_ips_report_' . Carbon::now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($request) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, [
                'IP Address',
                'Version',
                'Country',
                'Device Type',
                'Browser',
                'Platform',
                'Total Requests',
                'Risk Score',
                'Total Orders',
                'Failed Logins',
                'Suspicious Attempts',
                'Last Activity',
                'Blocked Status',
            ]);

            $exportQuery = SecurityIpLog::query()
                ->select(
                    'ip_address',
                    DB::raw('MAX(ip_version) as ip_version'),
                    DB::raw('MAX(country) as country'),
                    DB::raw('MAX(device_type) as device_type'),
                    DB::raw('MAX(browser) as browser'),
                    DB::raw('MAX(platform) as platform'),
                    DB::raw('COUNT(*) as total_requests'),
                    DB::raw('MAX(risk_score) as risk_score'),
                    DB::raw('MAX(created_at) as last_activity'),
                    DB::raw("SUM(CASE WHEN activity_type = 'order' THEN 1 ELSE 0 END) as total_orders"),
                    DB::raw("SUM(CASE WHEN activity_type = 'login_failed' THEN 1 ELSE 0 END) as failed_logins"),
                    DB::raw("SUM(CASE WHEN risk_score >= 50 THEN 1 ELSE 0 END) as suspicious_attempts")
                )
                ->groupBy('ip_address');

            // Admin traffic exclude filter CSV-teo apply kora
            $this->applyTrafficScopeFilter($exportQuery, $request->get('traffic_type', 'visitors'));

            $exportQuery->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->ip_address,
                        $row->ip_version,
                        $row->country ?? 'Unknown',
                        $row->device_type ?? 'N/A',
                        $row->browser ?? 'N/A',
                        $row->platform ?? 'N/A',
                        $row->total_requests,
                        $row->risk_score,
                        $row->total_orders,
                        $row->failed_logins,
                        $row->suspicious_attempts,
                        $row->last_activity,
                        $this->blockService->isBlocked($row->ip_address) ? 'Blocked' : 'Active',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Admin/Staff traffic isolation filter helper
     */
    protected function applyTrafficScopeFilter(Builder $query, string $scope): void
    {
        if ($scope === 'visitors') {
            // Shudhu public visitor o customer traffic (admin/staff activity baad)
            $query->where(function ($q) {
                $q->whereNull('user_type')
                  ->orWhereNotIn('user_type', ['admin', 'staff', 'super_admin']);
            })
            ->where('url_path', 'not like', '/admin%')
            ->where('url_path', 'not like', '/dashboard/auth%');
        } elseif ($scope === 'staff') {
            // Shudhu Admin o Staff activity
            $query->where(function ($q) {
                $q->whereIn('user_type', ['admin', 'staff', 'super_admin'])
                  ->orWhere('url_path', 'like', '/admin%')
                  ->orWhere('url_path', 'like', '/dashboard/auth%');
            });
        }
        // $scope === 'all' hole kono where clause lagbe na
    }

    /**
     * IP-r ashil desh shonakto korar dynamic non-blocking function
     */
    protected function resolveRealCountry(string $ip, bool $allowExternalFetch = true): string
    {
        if ($this->ipDetector->isPrivateOrLocal($ip)) {
            if (!$allowExternalFetch) {
                return Cache::get('geoip_wan_environment_country', 'Localhost');
            }

            return Cache::remember('geoip_wan_environment_country', 86400 * 7, function () {
                try {
                    $ctx = stream_context_create([
                        'http' => [
                            'timeout' => 2,
                            'header'  => "User-Agent: Mozilla/5.0\r\n",
                        ],
                    ]);
                    $res = @file_get_contents('http://ip-api.com/json/?fields=status,country', false, $ctx);
                    if ($res) {
                        $json = json_decode($res, true);
                        if (!empty($json['status']) && $json['status'] === 'success' && !empty($json['country'])) {
                            return (string) $json['country'];
                        }
                    }
                } catch (\Throwable $e) {}

                return 'Localhost';
            });
        }

        $cacheKey = 'geoip_country_' . $ip;
        if (!$allowExternalFetch) {
            return Cache::get($cacheKey, 'Unknown');
        }

        return Cache::remember($cacheKey, 86400 * 30, function () use ($ip) {
            try {
                $ctx = stream_context_create([
                    'http' => [
                        'timeout' => 2,
                        'header'  => "User-Agent: Mozilla/5.0\r\n",
                    ],
                ]);
                $res = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country", false, $ctx);
                if ($res) {
                    $json = json_decode($res, true);
                    if (!empty($json['status']) && $json['status'] === 'success' && !empty($json['country'])) {
                        return (string) $json['country'];
                    }
                }
            } catch (\Throwable $e) {}

            return 'Unknown';
        });
    }
}