<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use App\Models\SecurityBlockedIp;
use App\Models\SecurityBlockedNetwork;
use App\Models\SecurityUserRestriction;
use App\Models\User;
use App\Services\Security\IpBlockService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SecurityBlockingManagementController extends Controller
{
    protected IpBlockService $blockService;

    public function __construct(IpBlockService $blockService)
    {
        $this->blockService = $blockService;
    }

    public function index(Request $request): View|JsonResponse
    {
        return $this->blockedIps($request);
    }

    // ==========================================
    // ১. BLOCKED IPS MANAGEMENT (Single IP Blocking)
    // ==========================================

    public function blockedIps(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;

        $query = SecurityBlockedIp::with('creator');

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('block_reason', 'like', "%{$search}%")
                  ->orWhere('admin_note', 'like', "%{$search}%");
            });
        }

        // Status filter (Active vs Inactive vs All)
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->active();
            } elseif ($status === 'inactive') {
                $query->where(function ($q) {
                    $q->where('is_active', false)
                      ->orWhere(function ($sub) {
                          $sub->whereNotNull('expires_at')
                              ->where('expires_at', '<=', Carbon::now());
                      });
                });
            }
        } else {
            $query->active();
        }

        // Type filter
        if ($request->filled('block_type')) {
            $query->where('block_type', $request->get('block_type'));
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('block_category', $request->get('category'));
        }

        $blockedIps = $query->latest('id')->paginate($perPage)->withQueryString();

        // AJAX Response
        if ($request->ajax()) {
            return response()->json([
                'success'    => true,
                'html'       => view('admin.security.blocking.partials.blocked_ip_rows', compact('blockedIps'))->render(),
                'pagination' => (string) $blockedIps->links('pagination::bootstrap-5'),
                'total'      => $blockedIps->total(),
            ]);
        }

        $stats = [
            'total_active_blocks'    => SecurityBlockedIp::active()->count(),
            'permanent_blocks'       => SecurityBlockedIp::active()->where('block_type', 'permanent')->count(),
            'temporary_blocks'       => SecurityBlockedIp::active()->where('block_type', 'temporary')->count(),
            'total_blocked_networks' => SecurityBlockedNetwork::active()->count(),
        ];

        return view('admin.security.blocking.ips', compact('blockedIps', 'stats', 'perPage'));
    }

    public function storeBlockedIp(Request $request): JsonResponse
    {
        $request->validate([
            'ip_address'       => 'required|ip',
            'block_type'       => 'required|in:temporary,permanent',
            'duration_minutes' => 'nullable|integer|min:5',
            'block_reason'     => 'required|string|max:255',
            'block_category'   => 'nullable|string|in:manual,multiple_orders,spam_form,brute_force,url_attack',
            'admin_note'       => 'nullable|string|max:500',
        ]);

        $duration = (int) $request->input('duration_minutes', 120);

        $result = $this->blockService->blockIp(
            trim($request->input('ip_address')),
            $request->input('block_type'),
            $duration,
            $request->input('block_reason'),
            $request->input('block_category', 'manual'),
            $request->user()?->id,
            $request->input('admin_note')
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function unblockIp(Request $request, int $id): JsonResponse
    {
        $blockedIp = SecurityBlockedIp::findOrFail($id);
        $note = $request->input('note', 'Manually unblocked by Administrator');

        $result = $this->blockService->unblockIp($blockedIp->ip_address, $note, $request->user()?->id);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function updateBlockedIp(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'block_type'     => 'required|in:temporary,permanent',
            'extend_hours'   => 'nullable|integer|min:1',
            'block_reason'   => 'required|string|max:255',
            'admin_note'     => 'nullable|string|max:500',
        ]);

        $blocked = SecurityBlockedIp::findOrFail($id);

        $updateData = [
            'block_type'   => $request->input('block_type'),
            'block_reason' => $request->input('block_reason'),
            'admin_note'   => $request->input('admin_note'),
            'is_active'    => true,
        ];

        if ($request->input('block_type') === 'temporary') {
            $hours = (int) $request->input('extend_hours', 24);
            $baseTime = ($blocked->expires_at && $blocked->expires_at->isFuture()) ? $blocked->expires_at : Carbon::now();
            $updateData['expires_at'] = $baseTime->addHours($hours);
        } else {
            $updateData['expires_at'] = null;
        }

        $blocked->update($updateData);

        // Cache Sync
        Cache::forget('sec_blocked_ip_' . $blocked->ip_address);
        Cache::forget('sec_active_blocked_ips');

        return response()->json([
            'success' => true,
            'message' => "Settings for IP {$blocked->ip_address} updated.",
            'data'    => $blocked,
        ]);
    }

    // ==========================================
    // ২. BLOCKED NETWORKS (CIDR Subnets)
    // ==========================================

    public function blockedNetworks(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;

        $query = SecurityBlockedNetwork::with('creator');

        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('cidr_range', 'like', "%{$search}%")
                  ->orWhere('block_reason', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->get('status') === 'inactive') {
                $query->where(function ($q) {
                    $q->where('is_active', false)
                      ->orWhere(function ($sub) {
                          $sub->whereNotNull('expires_at')
                              ->where('expires_at', '<=', Carbon::now());
                      });
                });
            } elseif ($request->get('status') === 'active') {
                $query->active();
            }
        } else {
            $query->active();
        }

        $blockedNetworks = $query->latest('id')->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'success'    => true,
                'html'       => view('admin.security.blocking.partials.blocked_network_rows', compact('blockedNetworks'))->render(),
                'pagination' => (string) $blockedNetworks->links('pagination::bootstrap-5'),
                'total'      => $blockedNetworks->total(),
            ]);
        }

        return view('admin.security.blocking.networks', compact('blockedNetworks', 'perPage'));
    }

    public function storeBlockedNetwork(Request $request): JsonResponse
    {
        $request->validate([
            'cidr_range'    => 'required|string|max:50',
            'duration_days' => 'nullable|integer|min:1',
            'block_reason'  => 'required|string|max:255',
            'admin_note'    => 'nullable|string|max:500',
        ]);

        $result = $this->blockService->blockNetwork(
            trim($request->input('cidr_range')),
            $request->filled('duration_days') ? (int) $request->input('duration_days') : null,
            $request->input('block_reason'),
            $request->user()?->id,
            $request->input('admin_note')
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function unblockNetwork(Request $request, int $id): JsonResponse
    {
        $network = SecurityBlockedNetwork::findOrFail($id);
        $result = $this->blockService->unblockNetwork($network->cidr_range, $request->user()?->id);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    // ==========================================
    // ৩. USER RESTRICTIONS (User Account Lock)
    // ==========================================

    public function userRestrictions(Request $request): View|JsonResponse
    {
        $perPage = (int) $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;

        $query = SecurityUserRestriction::with(['user', 'creator'])->latest('id');

        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%");
                })->orWhere('reason', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('restriction_type', $request->get('type'));
        }

        if ($request->filled('status')) {
            if ($request->get('status') === 'inactive') {
                $query->where(function ($q) {
                    $q->where('is_active', false)
                      ->orWhere(function ($sub) {
                          $sub->whereNotNull('expires_at')
                              ->where('expires_at', '<=', Carbon::now());
                      });
                });
            } elseif ($request->get('status') === 'active') {
                $query->active();
            }
        } else {
            $query->active();
        }

        $restrictions = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'success'    => true,
                'html'       => view('admin.security.blocking.partials.user_restriction_rows', compact('restrictions'))->render(),
                'pagination' => (string) $restrictions->links('pagination::bootstrap-5'),
                'total'      => $restrictions->total(),
            ]);
        }

        return view('admin.security.blocking.users', compact('restrictions', 'perPage'));
    }

    public function storeUserRestriction(Request $request): JsonResponse
    {
        $request->validate([
            'user_id'          => 'required|exists:users,id',
            'restriction_type' => 'required|in:warning,temporary_lock,permanent_suspension,login_restricted,checkout_restricted,form_restricted',
            'reason'           => 'required|string|max:255',
            'duration_hours'   => 'nullable|integer|min:1',
            'admin_note'       => 'nullable|string|max:500',
        ]);

        $user = User::findOrFail($request->input('user_id'));

        if (in_array($user->role, ['admin', 'super_admin'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Action denied: Administrative accounts cannot be restricted.',
            ], 422);
        }

        $record = SecurityUserRestriction::applyRestriction(
            $user->id,
            $request->input('restriction_type'),
            $request->input('reason'),
            $request->filled('duration_hours') ? (int) $request->input('duration_hours') : null,
            $request->user()?->id,
            $request->input('admin_note')
        );

        return response()->json([
            'success' => true,
            'message' => "Restriction applied to user {$user->name}.",
            'data'    => $record,
        ]);
    }

    public function liftUserRestriction(Request $request, int $id): JsonResponse
    {
        $restriction = SecurityUserRestriction::findOrFail($id);
        $note = $request->input('note', 'Manually lifted by Admin');

        $restriction->update([
            'is_active'  => false,
            'admin_note' => $restriction->admin_note . " | Lifted: {$note}",
        ]);

        Cache::forget('sec_user_restriction_' . $restriction->user_id);

        return response()->json([
            'success' => true,
            'message' => 'Restriction lifted successfully.',
        ]);
    }

    // ==========================================
    // ৪. EMERGENCY RECOVERY FLUSH
    // ==========================================

    public function emergencyFlush(Request $request): JsonResponse
    {
        $request->validate([
            'confirmation' => 'required|in:CONFIRM_FLUSH',
        ]);

        $flushedCount = $this->blockService->emergencyFlushAllBlocks($request->user()?->id);

        return response()->json([
            'success' => true,
            'message' => "Emergency flush completed. {$flushedCount} blocks deactivated.",
        ]);
    }
}