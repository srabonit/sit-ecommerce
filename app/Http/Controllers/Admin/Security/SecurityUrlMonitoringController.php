<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use App\Models\SecurityIpLog;
use App\Models\SecurityUrlAttack;
use App\Services\Security\IpBlockService;
use App\Services\Security\IpDetectionService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecurityUrlMonitoringController extends Controller
{
    protected IpBlockService $blockService;
    protected IpDetectionService $ipDetector;

    public function __construct(IpBlockService $blockService, IpDetectionService $ipDetector)
    {
        $this->blockService = $blockService;
        $this->ipDetector = $ipDetector;
    }

    /**
     * আক্রান্ত URL-সমূহের তালিকা, র‍্যাঙ্কিং ও লাইভ ফিল্টার প্রদর্শন
     */
    public function index(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;

        $query = SecurityUrlAttack::query();

        // ফিল্টারিং লজিক প্রয়োগ
        $this->applyFilters($query, $request);

        // সর্টিং (ডিফল্ট: সর্বাধিক আক্রমণ)
        $sortBy = $request->get('sort_by', 'total_requests');
        $sortDir = $request->get('sort_dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['total_requests', 'failed_requests', 'blocked_requests', 'unique_ips_count', 'last_attack_at'];

        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderByDesc('total_requests');
        }

        $urlAttacks = $query->paginate($perPage)->withQueryString();

        // AJAX রিকোয়েস্টে JSON রেসপন্স
        if ($request->ajax()) {
            return response()->json([
                'success'    => true,
                'html'       => view('admin.security.urls.partials.url_table_rows', compact('urlAttacks'))->render(),
                'pagination' => (string) $urlAttacks->links('pagination::bootstrap-5'),
                'total'      => $urlAttacks->total(),
            ]);
        }

        // শীর্ষ আক্রান্ত ৫টি URL (র‍্যাঙ্কিং কার্ডের জন্য)
        $topRanked = SecurityUrlAttack::orderByDesc('total_requests')->limit(5)->get();

        // সিঙ্গেল অপ্টিমাইজড এগ্রিগেশনে মোট পরিসংখ্যান সংগ্রহ
        $summary = SecurityUrlAttack::query()
            ->selectRaw('
                COUNT(*) as total_attacked_endpoints,
                COALESCE(SUM(blocked_requests), 0) as total_blocked_hits,
                COALESCE(SUM(failed_requests), 0) as total_failed_hits
            ')
            ->first();

        $stats = [
            'total_attacked_endpoints' => (int) ($summary->total_attacked_endpoints ?? 0),
            'total_blocked_hits'       => (int) ($summary->total_blocked_hits ?? 0),
            'total_failed_hits'        => (int) ($summary->total_failed_hits ?? 0),
        ];

        return view('admin.security.urls.index', compact('urlAttacks', 'topRanked', 'stats', 'perPage'));
    }

    /**
     * নির্দিষ্ট URL-এর বিস্তারিত বিশ্লেষণ ও আক্রমণকারী IP তালিকা
     */
    public function show(int $id): View
    {
        $urlAttack = SecurityUrlAttack::findOrFail($id);
        $cleanPath = $urlAttack->url_path;

        // এই URL-এ আক্রমণকারী শীর্ষ IP তালিকা (অ্যাডমিন ট্রাফিক বাদ দিয়ে রিয়েল আক্রমণকারীদের গ্রুপ করা)
        $attackingIps = SecurityIpLog::where('url_path', $cleanPath)
            ->where(function ($q) {
                $q->whereNull('user_type')
                  ->orWhereNotIn('user_type', ['admin', 'staff', 'super_admin']);
            })
            ->select(
                'ip_address',
                DB::raw('MAX(country) as country'),
                DB::raw('MAX(device_type) as device_type'),
                DB::raw('MAX(browser) as browser'),
                DB::raw('COUNT(*) as hits_count'),
                DB::raw('MAX(risk_score) as max_risk'),
                DB::raw('MAX(created_at) as last_hit')
            )
            ->groupBy('ip_address')
            ->orderByDesc('hits_count')
            ->limit(15)
            ->get();

        // আক্রমণকারী আইপিগুলোর বর্তমান ব্লক স্ট্যাটাস ম্যাপ করা
        $attackingIps->transform(function ($item) {
            $item->is_blocked = $this->blockService->isBlocked($item->ip_address);
            return $item;
        });

        // সাম্প্রতিক রিকোয়েস্ট লগ টাইমলাইন (সর্বশেষ ২৫টি)
        $recentLogs = SecurityIpLog::where('url_path', $cleanPath)
            ->latest('created_at')
            ->limit(25)
            ->get();

        return view('admin.security.urls.show', compact('urlAttack', 'attackingIps', 'recentLogs'));
    }

    /**
     * নির্দিষ্ট URL-এ আক্রমণকারী সমস্ত সক্রিয় IP এক ক্লিকে Bulk Block করা (Safe Execution)
     */
    public function blockAllAttackingIps(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'block_type'       => 'required|in:temporary,permanent',
            'duration_minutes' => 'nullable|integer|min:15',
            'reason'           => 'nullable|string|max:255',
        ]);

        $urlAttack = SecurityUrlAttack::findOrFail($id);
        $blockType = $request->input('block_type', 'temporary');
        $duration = (int) $request->input('duration_minutes', 120);
        $reason = $request->input('reason') ?: "Bulk blocked for targeted attacks on endpoint: {$urlAttack->url_path}";
        $adminId = $request->user()?->id;

        // আক্রমণকারী আইপি তালিকা সংগ্রহ (অ্যাডমিন বা স্টাফ অ্যাকাউন্টকে দুর্ঘটনাজনিত ব্লক থেকে বাদ রাখা)
        $ipList = SecurityIpLog::where('url_path', $urlAttack->url_path)
            ->whereNotNull('ip_address')
            ->where('ip_address', '!=', '')
            ->where(function ($q) {
                $q->whereNull('user_type')
                  ->orWhereNotIn('user_type', ['admin', 'staff', 'super_admin']);
            })
            ->distinct()
            ->pluck('ip_address')
            ->toArray();

        $blockedCount = 0;
        $skippedCount = 0;

        foreach ($ipList as $ip) {
            $cleanIp = trim($ip);

            // ইতিমধ্যে ব্লকড বা লোকাল আইপি হলে স্কিপ
            if ($this->blockService->isBlocked($cleanIp) || $this->ipDetector->isPrivateOrLocal($cleanIp)) {
                $skippedCount++;
                continue;
            }

            $result = $this->blockService->blockIp(
                $cleanIp,
                $blockType,
                $duration,
                $reason,
                'url_attack',
                $adminId,
                "Bulk action triggered from URL Attack Monitor"
            );

            if ($result['success']) {
                $blockedCount++;
            } else {
                $skippedCount++;
            }
        }

        return response()->json([
            'success'       => true,
            'message'       => "Successfully blocked {$blockedCount} attacking IP addresses. ({$skippedCount} skipped due to existing block, allowlist, or private range).",
            'blocked_count' => $blockedCount,
            'skipped_count' => $skippedCount,
        ]);
    }

    /**
     * নির্দিষ্ট URL-এর কাউন্টার হিস্ট্রি রিসেট করা
     */
    public function resetMetrics(int $id): JsonResponse
    {
        $urlAttack = SecurityUrlAttack::findOrFail($id);
        $urlAttack->delete();

        return response()->json([
            'success' => true,
            'message' => "Attack metrics for URL {$urlAttack->url_path} have been reset.",
        ]);
    }

    /**
     * আক্রান্ত URL-সমূহের রিপোর্ট CSV এক্সপোর্ট (Filter-Aware)
     */
    public function export(Request $request): StreamedResponse
    {
        $fileName = 'url_attacks_report_' . Carbon::now()->format('Y-m-d_His') . '.csv';

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
                'Target URL Path',
                'Method',
                'Total Requests',
                'Failed Requests',
                'Blocked Requests',
                'Unique Attacking IPs',
                'Last Attack Detected At',
                'Sample Suspicious Payload',
            ]);

            $exportQuery = SecurityUrlAttack::query();
            $this->applyFilters($exportQuery, $request);

            $exportQuery->orderByDesc('total_requests')
                ->chunk(200, function ($rows) use ($handle) {
                    foreach ($rows as $row) {
                        $lastAttack = !empty($row->last_attack_at)
                            ? Carbon::parse($row->last_attack_at)->format('Y-m-d H:i:s')
                            : 'N/A';

                        fputcsv($handle, [
                            $row->url_path,
                            $row->request_method,
                            $row->total_requests,
                            $row->failed_requests,
                            $row->blocked_requests,
                            $row->unique_ips_count,
                            $lastAttack,
                            $row->sample_suspicious_payload ?? 'N/A',
                        ]);
                    }
                });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * কমন ফিল্টার হেল্পার
     */
    protected function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where('url_path', 'like', "%{$search}%");
        }

        if ($request->filled('method')) {
            $query->where('request_method', strtoupper($request->get('method')));
        }

        if ($request->filled('filter_type')) {
            match ($request->get('filter_type')) {
                'high_failures' => $query->where('failed_requests', '>=', 10),
                'blocked_only'  => $query->where('blocked_requests', '>', 0),
                'multi_ip'      => $query->where('unique_ips_count', '>=', 3),
                'with_payload'  => $query->whereNotNull('sample_suspicious_payload'),
                default         => null,
            };
        }
    }
}