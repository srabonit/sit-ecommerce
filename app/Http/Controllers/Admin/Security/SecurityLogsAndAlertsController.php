<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use App\Models\SecurityAlert;
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

class SecurityLogsAndAlertsController extends Controller
{
    protected IpBlockService $blockService;
    protected IpDetectionService $ipDetector;

    public function __construct(IpBlockService $blockService, IpDetectionService $ipDetector)
    {
        $this->blockService = $blockService;
        $this->ipDetector = $ipDetector;
    }

    public function index(Request $request): View|JsonResponse
    {
        return $this->logs($request);
    }

    // ==========================================
    // ১. SECURITY ACTIVITY LOGS
    // ==========================================

    /**
     * সিকিউরিটি অ্যাক্টিভিটি লগ তালিকা ও লাইভ ফিল্টার প্রদর্শন
     */
    public function logs(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->get('per_page', 25);
        $perPage = in_array($perPage, [15, 25, 50, 100], true) ? $perPage : 25;

        $query = $this->buildFilteredLogsQuery($request);

        $logs = $query->paginate($perPage)->withQueryString();

        // আইপি ও কান্ট্রি প্রেজেন্টেশন ফলব্যাক
        $logs->getCollection()->transform(function ($log) {
            if (empty($log->country) || in_array($log->country, ['Unknown', 'Localhost (BD)', 'N/A', ''], true)) {
                $log->country = in_array($log->ip_address, ['127.0.0.1', '::1'], true) ? 'Localhost' : 'Unknown';
            }
            return $log;
        });

        // AJAX রিকোয়েস্টে লাইভ টেবিল রেসপন্স
        if ($request->ajax()) {
            return response()->json([
                'success'    => true,
                'html'       => view('admin.security.logs.partials.log_table_rows', compact('logs'))->render(),
                'pagination' => (string) $logs->links('pagination::bootstrap-5'),
                'total'      => $logs->total(),
            ]);
        }

        // লগ ফিল্টার ও কাউন্টার সামারি
        $summary = [
            'total_logs'        => SecurityIpLog::count(),
            'blocked_today'     => SecurityIpLog::whereDate('created_at', Carbon::today())->where('security_status', 'blocked')->count(),
            'flagged_today'     => SecurityIpLog::whereDate('created_at', Carbon::today())->where('security_status', 'flagged')->count(),
            'critical_events'   => SecurityIpLog::where('risk_score', '>=', 75)->count(),
        ];

        $activityTypes = SecurityIpLog::distinct('activity_type')->pluck('activity_type')->filter()->values();

        return view('admin.security.logs.index', compact('logs', 'activityTypes', 'summary', 'perPage'));
    }

    /**
     * নির্দিষ্ট লগ রেকর্ডের বিস্তারিত মেটাডাটা
     */
    public function showLog(int $id): JsonResponse
    {
        $log = SecurityIpLog::with('user')->findOrFail($id);

        if (empty($log->country) || in_array($log->country, ['Unknown', 'Localhost (BD)', 'N/A', ''], true)) {
            $log->country = in_array($log->ip_address, ['127.0.0.1', '::1'], true) ? 'Localhost' : 'Unknown';
        }

        return response()->json([
            'success' => true,
            'data'    => $log,
            'html'    => view('admin.security.logs.partials.log_details_modal', compact('log'))->render(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        return $this->showLog($id);
    }

    /**
     * ফিল্টার-অ্যাওয়ার CSV এক্সপোর্ট
     */
    public function exportLogs(Request $request): StreamedResponse
    {
        $fileName = 'security_logs_' . Carbon::now()->format('Y-m-d_His') . '.csv';

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
                'Log ID',
                'Timestamp',
                'IP Address',
                'Country',
                'User Type',
                'User ID',
                'Activity Type',
                'Method',
                'URL Path',
                'Status Code',
                'Risk Score',
                'Security Status',
                'Device Type',
                'Browser',
                'Platform',
            ]);

            $query = $this->buildFilteredLogsQuery($request);

            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    $country = (!empty($row->country) && !in_array($row->country, ['Unknown', 'Localhost (BD)', 'N/A', ''], true))
                        ? $row->country
                        : (in_array($row->ip_address, ['127.0.0.1', '::1'], true) ? 'Localhost' : 'Unknown');

                    fputcsv($handle, [
                        $row->id,
                        $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '',
                        $row->ip_address,
                        $country,
                        $row->user_type ?? 'guest',
                        $row->user_id ?? 'N/A',
                        $row->activity_type,
                        $row->request_method,
                        $row->url_path,
                        $row->response_status,
                        $row->risk_score,
                        $row->security_status,
                        $row->device_type ?? 'N/A',
                        $row->browser ?? 'N/A',
                        $row->platform ?? 'N/A',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    public function export(Request $request): StreamedResponse
    {
        return $this->exportLogs($request);
    }

    /**
     * লগ রিটেনশন ও নিরাপদ প্রুনিং
     */
    public function cleanupLogs(Request $request): JsonResponse
    {
        $request->validate([
            'retention_days' => 'required|integer|in:7,15,30,60,90,180',
            'keep_flagged'   => 'nullable|boolean',
        ]);

        $days = (int) $request->input('retention_days');
        $keepFlagged = $request->boolean('keep_flagged', true);
        $cutoffDate = Carbon::now()->subDays($days);

        $query = SecurityIpLog::where('created_at', '<', $cutoffDate);

        if ($keepFlagged) {
            $query->where('security_status', '!=', 'blocked')
                  ->where('risk_score', '<', 50);
        }

        $deletedCount = $query->delete();

        SecurityAlert::createOrAggregate(
            'logs_cleaned_up',
            "Log Cleanup Executed ({$days} Days Retention)",
            "Successfully purged {$deletedCount} old request logs older than {$days} days.",
            $request->ip(),
            'low',
            $request->user()?->id,
            null,
            null,
            null,
            ['purged_count' => $deletedCount, 'retention_days' => $days]
        );

        return response()->json([
            'success' => true,
            'message' => "Successfully purged {$deletedCount} log records older than {$days} days.",
            'deleted_count' => $deletedCount,
        ]);
    }

    public function clean(Request $request): JsonResponse
    {
        return $this->cleanupLogs($request);
    }

    // ==========================================
    // ২. SECURITY ALERTS & NOTIFICATIONS
    // ==========================================

    /**
     * সিকিউরিটি অ্যালার্ট তালিকা ও ফিল্টার
     */
    public function alerts(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;

        $query = SecurityAlert::with('user')->latest('id');

        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->get('event_type'));
        }

        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->get('risk_level'));
        }

        if ($request->filled('status')) {
            if ($request->get('status') === 'unread') {
                $query->where('is_read', false);
            } elseif ($request->get('status') === 'read') {
                $query->where('is_read', true);
            }
        }

        $alerts = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'success'    => true,
                'html'       => view('admin.security.alerts.partials.alert_table_rows', compact('alerts'))->render(),
                'pagination' => (string) $alerts->links('pagination::bootstrap-5'),
                'total'      => $alerts->total(),
            ]);
        }

        $unreadCount = SecurityAlert::unread()->count();
        $eventTypes = SecurityAlert::distinct('event_type')->pluck('event_type')->filter()->values();

        return view('admin.security.alerts.index', compact('alerts', 'unreadCount', 'eventTypes', 'perPage'));
    }

    public function markAlertAsRead(int $id): JsonResponse
    {
        $alert = SecurityAlert::findOrFail($id);
        $alert->update(['is_read' => true]);

        Cache::forget('sec_unread_alerts_badge_count');

        return response()->json([
            'success' => true,
            'message' => 'Alert marked as read.',
        ]);
    }

    public function markAllAlertsAsRead(): JsonResponse
    {
        $count = SecurityAlert::markAllAsRead();

        Cache::forget('sec_unread_alerts_badge_count');

        return response()->json([
            'success' => true,
            'message' => "Successfully marked {$count} alerts as read.",
        ]);
    }

    public function destroyAlert(int $id): JsonResponse
    {
        $alert = SecurityAlert::findOrFail($id);
        $alert->delete();

        Cache::forget('sec_unread_alerts_badge_count');

        return response()->json([
            'success' => true,
            'message' => 'Security alert removed.',
        ]);
    }

    // ==========================================
    // ৩. HELPER QUERY BUILDER
    // ==========================================

    /**
     * লগ কোয়েরি বিল্ডার (ফিল্টার ও স্কোপ রুলস)
     */
    protected function buildFilteredLogsQuery(Request $request): Builder
    {
        $query = SecurityIpLog::with('user')->latest('id');

        // ট্রাফিক স্কোপ (All vs Public Visitors vs Staff)
        $trafficType = $request->get('traffic_type', 'all');
        if ($trafficType === 'visitors') {
            $query->where(function ($q) {
                $q->whereNull('user_type')
                  ->orWhereNotIn('user_type', ['admin', 'staff', 'super_admin']);
            })
            ->where('url_path', 'not like', '/admin%')
            ->where('url_path', 'not like', '/dashboard/auth%');
        } elseif ($trafficType === 'staff') {
            $query->where(function ($q) {
                $q->whereIn('user_type', ['admin', 'staff', 'super_admin'])
                  ->orWhere('url_path', 'like', '/admin%')
                  ->orWhere('url_path', 'like', '/dashboard/auth%');
            });
        }

        // সার্চ ফিল্টার
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('url_path', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%")
                  ->orWhere('user_agent', 'like', "%{$search}%");
            });
        }

        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->get('activity_type'));
        }

        if ($request->filled('security_status')) {
            $query->where('security_status', $request->get('security_status'));
        }

        if ($request->filled('response_status')) {
            $query->where('response_status', (int) $request->get('response_status'));
        }

        if ($request->filled('risk_level')) {
            match ($request->get('risk_level')) {
                'critical' => $query->where('risk_score', '>=', 75),
                'high'     => $query->whereBetween('risk_score', [50, 74]),
                'medium'   => $query->whereBetween('risk_score', [35, 49]),
                'low'      => $query->where('risk_score', '<', 35),
                default    => null,
            };
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = Carbon::parse($request->get('start_date'))->startOfDay();
            $end = Carbon::parse($request->get('end_date'))->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        }

        return $query;
    }
}