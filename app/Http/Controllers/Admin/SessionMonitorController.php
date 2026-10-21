<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class SessionMonitorController extends Controller
{
    /**
     * সকল সক্রিয় লাইভ সেশনের তালিকা প্রদর্শন (Search, Multi-Filter, AJAX Pagination & Live Stats)
     */
    public function index(Request $request)
    {
        $currentSessionId = $request->session()->getId();

        if (!Schema::hasTable('active_user_sessions')) {
            if ($request->ajax()) {
                return response()->json([
                    'success'      => false,
                    'html'         => '<tr><td colspan="8" class="text-center py-4 text-muted">Sessions table does not exist.</td></tr>',
                    'pagination'   => '',
                    'total'        => 0,
                    'activeCount'  => 0,
                    'totalDevices' => 0,
                    'desktopCount' => 0,
                    'mobileCount'  => 0,
                    'firstItem'    => 0,
                    'lastItem'     => 0
                ]);
            }

            return view('admin.sessions.index', [
                'sessions'         => collect(),
                'activeCount'      => 0,
                'totalDevices'     => 0,
                'desktopCount'     => 0,
                'mobileCount'      => 0,
                'roles'            => collect(),
                'currentSessionId' => $currentSessionId,
                'tableExists'      => false,
            ]);
        }

        // গত ৬০ মিনিটের মধ্যে সক্রিয় থাকা সেশনগুলো বিবেচনা করা হবে
        $idleCutoff = now()->subMinutes(60);

        $query = DB::table('active_user_sessions')
            ->join('users', 'active_user_sessions.user_id', '=', 'users.id')
            ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
            ->where('active_user_sessions.is_revoked', false)
            ->where('active_user_sessions.last_activity', '>=', $idleCutoff)
            ->whereNull('users.deleted_at')
            ->select(
                'active_user_sessions.*',
                'users.name as user_name',
                'users.email as user_email',
                'users.role as user_role',
                'users.photo as user_photo',
                'users.is_super_admin',
                'branches.name as branch_name'
            );

        if ($request->filled('role') && $request->role !== 'all') {
            $r = $request->role;
            $query->where(function ($q) use ($r) {
                $q->where('users.role', $r)
                  ->orWhere('users.role', str_replace('_', ' ', $r))
                  ->orWhere('users.role', str_replace('_', '-', $r));
            });
        }

        if ($request->filled('device') && $request->device !== 'all') {
            $query->where('active_user_sessions.device_type', $request->device);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('users.name', 'like', "%{$s}%")
                  ->orWhere('users.email', 'like', "%{$s}%")
                  ->orWhere('active_user_sessions.ip_address', 'like', "%{$s}%")
                  ->orWhere('active_user_sessions.browser', 'like', "%{$s}%")
                  ->orWhere('active_user_sessions.platform', 'like', "%{$s}%");
            });
        }

        $sessions = $query->orderByDesc('active_user_sessions.last_activity')->paginate(25)->withQueryString();

        $activeCount = (clone $query)->distinct('active_user_sessions.user_id')->count('active_user_sessions.user_id');
        $totalDevices = $sessions->total();
        $desktopCount = (clone $query)->where('active_user_sessions.device_type', 'Desktop')->count();
        $mobileCount = (clone $query)->whereIn('active_user_sessions.device_type', ['Mobile', 'Tablet'])->count();

        // AJAX রিকোয়েস্ট হ্যান্ডলিং
        if ($request->ajax()) {
            return response()->json([
                'success'      => true,
                'html'         => view('admin.sessions.partials.table_rows', compact('sessions', 'currentSessionId'))->render(),
                'pagination'   => $sessions->hasPages() ? $sessions->links('pagination::bootstrap-5')->render() : '',
                'total'        => $totalDevices,
                'activeCount'  => $activeCount,
                'totalDevices' => $totalDevices,
                'desktopCount' => $desktopCount,
                'mobileCount'  => $mobileCount,
                'firstItem'    => $sessions->firstItem() ?? 0,
                'lastItem'     => $sessions->lastItem() ?? 0,
            ]);
        }

        $roles = DB::table('users')
            ->whereNull('deleted_at')
            ->where('role', '!=', 'customer')
            ->distinct()
            ->pluck('role')
            ->map(fn($r) => str_replace(['-', ' '], '_', strtolower(trim((string)$r))))
            ->unique()
            ->values();

        return view('admin.sessions.index', [
            'sessions'         => $sessions,
            'roles'            => $roles,
            'currentSessionId' => $currentSessionId,
            'tableExists'      => true,
            'activeCount'      => $activeCount,
            'totalDevices'     => $totalDevices,
            'desktopCount'     => $desktopCount,
            'mobileCount'      => $mobileCount,
        ]);
    }

    /**
     * নির্দিষ্ট ডিভাইস সেশন বন্ধ করা (Single Session Revoke)
     */
    public function revoke(Request $request, $id)
    {
        $session = DB::table('active_user_sessions')->where('id', $id)->first();
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Session not found or already terminated.'], 404);
        }

        // নিজের বর্তমান ডিভাইসের সেশন রিভোক করা প্রতিরোধ
        if ($session->session_id === $request->session()->getId()) {
            return response()->json(['success' => false, 'message' => 'Cannot revoke your own active browser session from here. Use Logout.'], 403);
        }

        $targetUser = DB::table('users')->where('id', $session->user_id)->first();
        if ($targetUser && ($targetUser->is_super_admin || $targetUser->role === 'super_admin') && !Auth::user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Access Denied: Only a Super Admin can revoke Super Admin sessions.'], 403);
        }

        DB::table('active_user_sessions')->where('id', $id)->update([
            'is_revoked' => true,
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Session terminated successfully for {$targetUser->name} on this device."
        ]);
    }

    /**
     * ইউজারের সকল ডিভাইসের সেশন একসাথে বন্ধ করা (Force Logout All Devices)
     */
    public function revokeAll(Request $request, $userId)
    {
        $targetUser = DB::table('users')->where('id', $userId)->first();
        if (!$targetUser) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        if (($targetUser->is_super_admin || $targetUser->role === 'super_admin') && !Auth::user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Access Denied: Only a Super Admin can revoke Super Admin sessions.'], 403);
        }

        $currentSessionId = $request->session()->getId();

        // বর্তমান ইউজারের নিজস্ব রানিং সেশন বাদে বাকি সব সেশন টার্মিনেট করা
        DB::table('active_user_sessions')
            ->where('user_id', $userId)
            ->where('session_id', '!=', $currentSessionId)
            ->update([
                'is_revoked' => true,
                'updated_at' => now(),
            ]);

        // অন্য কোনো স্টাফ ইউজার হলে force_logout ফ্ল্যাগ অন করা
        if ($userId != Auth::id() && Schema::hasColumn('users', 'force_logout')) {
            DB::table('users')->where('id', $userId)->update(['force_logout' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => "All other connected device sessions for {$targetUser->name} have been completely revoked."
        ]);
    }
}