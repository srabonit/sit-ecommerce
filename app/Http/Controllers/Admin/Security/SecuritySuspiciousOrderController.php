<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SecurityAlert;
use App\Models\SecurityIpLog;
use App\Models\SecurityUserRestriction;
use App\Services\Security\IpBlockService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SecuritySuspiciousOrderController extends Controller
{
    protected IpBlockService $blockService;

    public function __construct(IpBlockService $blockService)
    {
        $this->blockService = $blockService;
    }

    /**
     * স্প্যাম ও সন্দেহজনক অর্ডারের তালিকা এবং লাইভ ফিল্টার
     */
    public function index(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;

        // শুধুমাত্র সন্দেহজনক বা সিকিউরিটি রিভিউয়ের আওতায় থাকা অর্ডার কোয়েরি
        $query = Order::query()
            ->with(['customer', 'items'])
            ->where(function ($q) {
                $q->where('is_suspicious', true)
                  ->orWhere('security_risk_score', '>=', 40)
                  ->orWhereIn('security_review_status', ['under_review', 'flagged_spam', 'cancelled_fraud']);
            });

        // ১. সার্চ ফিল্টার (অর্ডার নাম্বার, আইপি, ফোন, ইমেইল, নাম)
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('client_ip', 'like', "%{$search}%");

                if (Schema::hasColumn('orders', 'customer_phone')) {
                    $q->orWhere('customer_phone', 'like', "%{$search}%");
                } elseif (Schema::hasColumn('orders', 'phone')) {
                    $q->orWhere('phone', 'like', "%{$search}%");
                }

                if (Schema::hasColumn('orders', 'customer_email')) {
                    $q->orWhere('customer_email', 'like', "%{$search}%");
                } elseif (Schema::hasColumn('orders', 'email')) {
                    $q->orWhere('email', 'like', "%{$search}%");
                }

                if (Schema::hasColumn('orders', 'customer_name')) {
                    $q->orWhere('customer_name', 'like', "%{$search}%");
                }
            });
        }

        // ২. সিকিউরিটি রিভিউ স্ট্যাটাস ফিল্টার
        if ($request->filled('review_status')) {
            $query->where('security_review_status', $request->get('review_status'));
        }

        // ৩. রিস্ক লেভেল ফিল্টার
        if ($request->filled('risk_level')) {
            match ($request->get('risk_level')) {
                'critical' => $query->where('security_risk_score', '>=', 75),
                'high'     => $query->whereBetween('security_risk_score', [50, 74]),
                'medium'   => $query->whereBetween('security_risk_score', [35, 49]),
                'low'      => $query->where('security_risk_score', '<', 35),
                default    => null,
            };
        }

        // ৪. অর্ডার সোর্স ফিল্টার
        if ($request->filled('order_source') && Schema::hasColumn('orders', 'order_source')) {
            $query->where('order_source', $request->get('order_source'));
        }

        // ৫. ডেট রেঞ্জ ফিল্টার
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = Carbon::parse($request->get('start_date'))->startOfDay();
            $end = Carbon::parse($request->get('end_date'))->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        }

        $orders = $query->latest('id')->paginate($perPage)->withQueryString();

        // আইপি ব্লক স্ট্যাটাস ম্যাপ করা
        $orders->getCollection()->transform(function ($order) {
            $order->is_ip_blocked = !empty($order->client_ip) && $this->blockService->isBlocked($order->client_ip);
            return $order;
        });

        if ($request->ajax()) {
            return response()->json([
                'success'    => true,
                'html'       => view('admin.security.orders.partials.order_table_rows', compact('orders'))->render(),
                'pagination' => (string) $orders->links('pagination::bootstrap-5'),
                'total'      => $orders->total(),
            ]);
        }

        // সিঙ্গেল ফাস্ট এগ্রিগেশন কোয়েরিতে কাউন্টার সামারি সংগ্রহ
        $summary = Order::query()
            ->selectRaw("
                COUNT(CASE WHEN is_suspicious = 1 OR security_risk_score >= 40 OR security_review_status IN ('under_review', 'flagged_spam', 'cancelled_fraud') THEN 1 END) as total_suspicious,
                COUNT(CASE WHEN security_review_status = 'under_review' THEN 1 END) as under_review,
                COUNT(CASE WHEN security_review_status = 'flagged_spam' THEN 1 END) as flagged_spam,
                COUNT(CASE WHEN security_review_status = 'approved_by_admin' THEN 1 END) as approved_clean
            ")
            ->first();

        $counters = [
            'total_suspicious' => (int) ($summary->total_suspicious ?? 0),
            'under_review'     => (int) ($summary->under_review ?? 0),
            'flagged_spam'     => (int) ($summary->flagged_spam ?? 0),
            'approved_clean'   => (int) ($summary->approved_clean ?? 0),
        ];

        return view('admin.security.orders.index', compact('orders', 'counters', 'perPage'));
    }

    /**
     * একই IP থেকে একাধিক অর্ডারের ক্লাস্টার ভিউ (Same IP Multiple Order Clusters)
     */
    public function clusters(Request $request): View|JsonResponse
    {
        // ডাইনামিকালি অর্ডারের অ্যামাউন্ট কলাম সনাক্তকরণ
        $amountCol = '0';
        foreach (['grand_total', 'total', 'payable_amount', 'total_amount', 'paid_amount', 'order_total'] as $col) {
            if (Schema::hasColumn('orders', $col)) {
                $amountCol = $col;
                break;
            }
        }

        // ডাইনামিকালি ফোন নম্বর কলাম সনাক্তকরণ
        $phoneCol = 'id';
        foreach (['customer_phone', 'phone', 'billing_phone', 'mobile'] as $pCol) {
            if (Schema::hasColumn('orders', $pCol)) {
                $phoneCol = $pCol;
                break;
            }
        }

        $query = Order::query()
            ->whereNotNull('client_ip')
            ->where('client_ip', '!=', '');

        // সার্চ ফিল্টার সাপোর্ট (আইপি দিয়ে সার্চ)
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where('client_ip', 'like', "%{$search}%");
        }

        // যেসব IP থেকে একাধিক অর্ডার হয়েছে সেগুলোর এগ্রিগেশন
        $clusters = $query->select(
                'client_ip',
                DB::raw('COUNT(id) as total_orders_count'),
                DB::raw("SUM({$amountCol}) as total_order_value"),
                DB::raw("COUNT(DISTINCT {$phoneCol}) as unique_phones_count"),
                DB::raw('COUNT(DISTINCT user_id) as unique_users_count'),
                DB::raw('MAX(security_risk_score) as highest_risk_score'),
                DB::raw('MAX(created_at) as latest_order_time'),
                DB::raw('MIN(created_at) as first_order_time')
            )
            ->groupBy('client_ip')
            ->having('total_orders_count', '>=', 2)
            ->orderByDesc('total_orders_count')
            ->paginate(15)
            ->withQueryString();

        // আইপি ব্লকিং স্ট্যাটাস ম্যাপ করা
        $clusters->getCollection()->transform(function ($cluster) {
            $cluster->is_blocked = $this->blockService->isBlocked($cluster->client_ip);
            return $cluster;
        });

        if ($request->ajax()) {
            return response()->json([
                'success'    => true,
                'html'       => view('admin.security.orders.partials.cluster_rows', compact('clusters'))->render(),
                'pagination' => (string) $clusters->links('pagination::bootstrap-5'),
            ]);
        }

        return view('admin.security.orders.clusters', compact('clusters'));
    }

    /**
     * নির্দিষ্ট সাসপিশাস অর্ডারের পূর্ণাঙ্গ সিকিউরিটি রিভিউ ও ব্যাকগ্রাউন্ড অডিট
     */
    public function show(int $id): View
    {
        $order = Order::with(['customer', 'items.product', 'items.variant'])->findOrFail($id);
        $clientIp = trim((string) $order->client_ip);

        // আইপি ফাঁকা থাকলে ফলস পজিটিভ কুয়েরি প্রতিরোধ
        if (!empty($clientIp)) {
            $relatedOrders = Order::where('client_ip', $clientIp)
                ->where('id', '!=', $order->id)
                ->latest('id')
                ->limit(10)
                ->get();

            $ipLogs = SecurityIpLog::where('ip_address', $clientIp)
                ->latest('created_at')
                ->limit(15)
                ->get();

            $alerts = SecurityAlert::where('ip_address', $clientIp)
                ->latest('id')
                ->limit(5)
                ->get();

            $isBlocked = $this->blockService->isBlocked($clientIp);
        } else {
            $relatedOrders = collect();
            $ipLogs = collect();
            $alerts = collect();
            $isBlocked = false;
        }

        return view('admin.security.orders.show', compact(
            'order',
            'relatedOrders',
            'ipLogs',
            'alerts',
            'isBlocked'
        ));
    }

    /**
     * অর্ডারের সিকিউরিটি রিভিউ স্ট্যাটাস পরিবর্তন
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'review_status' => 'required|in:clean,under_review,flagged_spam,approved_by_admin,cancelled_fraud',
            'admin_note'    => 'nullable|string|max:500',
            'order_status'  => 'nullable|string|in:pending,processing,on_hold,cancelled',
        ]);

        $order = Order::findOrFail($id);
        $oldStatus = $order->security_review_status;
        $newStatus = $request->input('review_status');

        $updateData = [
            'security_review_status' => $newStatus,
            'security_checked_at'    => Carbon::now(),
        ];

        if ($newStatus === 'approved_by_admin' || $newStatus === 'clean') {
            $updateData['is_suspicious'] = false;
        } elseif ($newStatus === 'flagged_spam' || $newStatus === 'cancelled_fraud') {
            $updateData['is_suspicious'] = true;
        }

        // অর্ডার স্ট্যাটাস কলাম অটো-ডিটেকশন (order_status অথবা status)
        $statusColumn = Schema::hasColumn('orders', 'order_status') 
            ? 'order_status' 
            : (Schema::hasColumn('orders', 'status') ? 'status' : null);

        if ($statusColumn) {
            if ($request->filled('order_status')) {
                $updateData[$statusColumn] = $request->input('order_status');
            } elseif ($newStatus === 'cancelled_fraud') {
                $updateData[$statusColumn] = 'cancelled';
            } elseif ($newStatus === 'under_review') {
                $updateData[$statusColumn] = 'on_hold';
            }
        }

        if ($request->filled('admin_note')) {
            $notePrefix = "[" . Carbon::now()->format('Y-m-d H:i') . " Security Admin]: ";
            $updateData['security_note'] = trim(($order->security_note ?? '') . "\n" . $notePrefix . $request->input('admin_note'));
        }

        $order->update($updateData);

        SecurityAlert::createOrAggregate(
            'order_security_reviewed',
            "Order #{$order->order_number} Reviewed",
            "Security status changed from '{$oldStatus}' to '{$newStatus}' by Admin.",
            $order->client_ip,
            'low',
            $request->user()?->id,
            null,
            $order->id,
            'Order'
        );

        return response()->json([
            'success' => true,
            'message' => "Order #{$order->order_number} security status updated to {$newStatus}.",
            'data'    => $order,
        ]);
    }

    /**
     * কুইক ব্লকিং অ্যাকশন
     */
    public function quickBlockAction(Request $request): JsonResponse
    {
        $request->validate([
            'action_type' => 'required|in:block_ip,block_user,block_network',
            'target'      => 'required|string',
            'reason'      => 'required|string|max:255',
            'duration'    => 'nullable|integer',
        ]);

        $actionType = $request->input('action_type');
        $target = trim($request->input('target'));
        $reason = $request->input('reason');
        $duration = (int) $request->input('duration', 60);
        $adminId = $request->user()?->id;

        switch ($actionType) {
            case 'block_ip':
                $result = $this->blockService->blockIp(
                    $target,
                    $duration > 0 ? 'temporary' : 'permanent',
                    $duration,
                    $reason,
                    'multiple_orders',
                    $adminId,
                    "Triggered from suspicious order management"
                );
                return response()->json($result, $result['success'] ? 200 : 422);

            case 'block_user':
                $userId = (int) $target;
                $hours = $duration > 0 ? max(1, (int) round($duration / 60)) : null;

                SecurityUserRestriction::applyRestriction(
                    $userId,
                    'checkout_restricted',
                    $reason,
                    $hours,
                    $adminId,
                    "Restricted due to repeated fraudulent order attempts"
                );
                return response()->json([
                    'success' => true,
                    'message' => "User ID {$userId} has been restricted from checkout.",
                ]);

            case 'block_network':
                $days = $duration > 0 ? max(1, (int) round($duration / 1440)) : 7;
                $result = $this->blockService->blockNetwork(
                    $target,
                    $days,
                    $reason,
                    $adminId,
                    "CIDR Blocked from suspicious order review"
                );
                return response()->json($result, $result['success'] ? 200 : 422);

            default:
                return response()->json(['success' => false, 'message' => 'Invalid action type.'], 400);
        }
    }
}