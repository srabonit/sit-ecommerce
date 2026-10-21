<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    /**
     * সকল অ্যাক্টিভিটি লগের তালিকা (Search, Multi-Filter, AJAX Pagination & Statistics)
     */
    public function index(Request $request)
    {
        if (!Schema::hasTable('activity_logs')) {
            if ($request->ajax()) {
                return response()->json([
                    'success'    => false,
                    'html'       => '<tr><td colspan="9" class="text-center py-4 text-muted">Table does not exist.</td></tr>',
                    'pagination' => '',
                    'total'      => 0,
                    'firstItem'  => 0,
                    'lastItem'   => 0
                ]);
            }

            return view('admin.activity_logs.index', [
                'logs'        => collect(),
                'modules'     => collect(),
                'actions'     => collect(),
                'roles'       => collect(),
                'tableExists' => false,
            ]);
        }

        $query = DB::table('activity_logs')
            ->leftJoin('branches', 'activity_logs.branch_id', '=', 'branches.id')
            ->select('activity_logs.*', 'branches.name as branch_name');

        // মডিউল ফিল্টার
        if ($request->filled('module') && $request->module !== 'all') {
            $query->where('activity_logs.module', $request->module);
        }

        // অ্যাকশন ফিল্টার (create, update, delete, status_change, login, etc.)
        if ($request->filled('action_type') && $request->action_type !== 'all') {
            $query->where('activity_logs.action', $request->action_type);
        }

        // রোল ফিল্টার (Space, Underscore ও Hyphen normalize সহ)
        if ($request->filled('role') && $request->role !== 'all') {
            $r = $request->role;
            $query->where(function ($q) use ($r) {
                $q->where('activity_logs.role', $r)
                  ->orWhere('activity_logs.role', str_replace('_', ' ', $r))
                  ->orWhere('activity_logs.role', str_replace('_', '-', $r));
            });
        }

        // ফলাফল ফিল্টার (success / failed)
        if ($request->filled('result') && $request->result !== 'all') {
            $query->where('activity_logs.result', $request->result);
        }

        // নির্দিষ্ট একক তারিখ ফিল্টার
        if ($request->filled('date')) {
            $query->whereDate('activity_logs.created_at', $request->date);
        }

        // গ্লোবাল সার্চ (ইউজার নাম, ইনভয়েস/রেফারেন্স, বিবরণ, আইপি)
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('activity_logs.user_name', 'like', "%{$s}%")
                  ->orWhere('activity_logs.record_ref', 'like', "%{$s}%")
                  ->orWhere('activity_logs.record_id', 'like', "%{$s}%")
                  ->orWhere('activity_logs.description', 'like', "%{$s}%")
                  ->orWhere('activity_logs.ip_address', 'like', "%{$s}%");
            });
        }

        $logs = $query->latest('activity_logs.id')->paginate(25)->withQueryString();

        // AJAX রিকোয়েস্টের ক্ষেত্রে শুধুমাত্র প্রয়োজনীয় অংশ JSON হিসেবে রিটার্ন করা
        if ($request->ajax()) {
            return response()->json([
                'success'    => true,
                'html'       => view('admin.activity_logs.partials.table_rows', compact('logs'))->render(),
                'pagination' => $logs->hasPages() ? $logs->links('pagination::bootstrap-5')->render() : '',
                'total'      => $logs->total(),
                'firstItem'  => $logs->firstItem() ?? 0,
                'lastItem'   => $logs->lastItem() ?? 0,
                'count'      => $logs->count(),
            ]);
        }

        $modules = DB::table('activity_logs')->select('module')->distinct()->pluck('module');
        $actions = DB::table('activity_logs')->select('action')->distinct()->pluck('action');
        $roles   = DB::table('activity_logs')->select('role')->distinct()->pluck('role');

        return view('admin.activity_logs.index', compact('logs', 'modules', 'actions', 'roles') + ['tableExists' => true]);
    }

    /**
     * নির্দিষ্ট লগের সম্পূর্ণ বিফোর / আফটার ডাটা প্রিভিউ (AJAX Modal Endpoint)
     */
    public function show($id)
    {
        $log = DB::table('activity_logs')->where('id', $id)->first();
        if (!$log) {
            return response()->json(['success' => false, 'message' => 'Log record not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'          => $log->id,
                'user_name'   => $log->user_name,
                'role'        => ucwords(str_replace('_', ' ', $log->role)),
                'action'      => strtoupper($log->action),
                'module'      => ucwords(str_replace('_', ' ', $log->module)),
                'record_ref'  => $log->record_ref ?: '#' . $log->record_id,
                'description' => $log->description,
                'old_values'  => $log->old_values ? json_decode($log->old_values, true) : null,
                'new_values'  => $log->new_values ? json_decode($log->new_values, true) : null,
                'ip_address'  => $log->ip_address,
                'device_type' => $log->device_type,
                'user_agent'  => $log->user_agent,
                'created_at'  => date('d M Y, h:i:s A', strtotime($log->created_at)),
            ]
        ]);
    }

    /**
     * নির্দিষ্ট একক লগ স্থায়ীভাবে মুছে ফেলা (Delete Specific Single Log Record)
     */
    public function destroy($id)
    {
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'Access Denied: Only Super Admins are authorized to delete audit log entries.'
            ], 403);
        }

        $log = DB::table('activity_logs')->where('id', $id)->first();
        if (!$log) {
            return response()->json([
                'success' => false,
                'message' => 'Target activity log record not found or already deleted.'
            ], 404);
        }

        DB::table('activity_logs')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => "Log entry #{$id} has been permanently deleted from audit records."
        ]);
    }

    /**
     * লগ ক্লিনআপ সিস্টেম (একক লগ আইডি, একক তারিখ, ডেট রেঞ্জ, রিটেনশন ও অল)
     */
    public function cleanup(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            return response()->json([
                'success' => false, 
                'message' => 'Access Denied: Only Super Admins are authorized to purge audit trail logs.'
            ], 403);
        }

        $cleanupType = $request->input('cleanup_type', 'days');
        $deletedCount = 0;
        $message = '';

        if ($cleanupType === 'single_id') {
            $request->validate([
                'log_id' => 'required|integer'
            ]);

            $logId = (int)$request->input('log_id');
            $deletedCount = DB::table('activity_logs')->where('id', $logId)->delete();
            $message = "Log record #{$logId} successfully delete kora hoyeche.";

        } elseif ($cleanupType === 'single_date') {
            $request->validate([
                'specific_date' => 'required|date'
            ]);

            $targetDate = $request->input('specific_date');
            $deletedCount = DB::table('activity_logs')->whereDate('created_at', $targetDate)->delete();
            $message = "Date {$targetDate}-er mot {$deletedCount} ti activity log successfully delete kora hoyeche.";

        } elseif ($cleanupType === 'date_range') {
            $request->validate([
                'start_date' => 'required|date',
                'end_date'   => 'required|date|after_or_equal:start_date',
            ]);

            $startDate = $request->input('start_date');
            $endDate   = $request->input('end_date');

            $deletedCount = DB::table('activity_logs')
                ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
                ->delete();

            $message = "Date range ({$startDate} theke {$endDate})-er moddhe mot {$deletedCount} ti activity log successfully delete kora hoyeche.";

        } elseif ($cleanupType === 'all') {
            $deletedCount = DB::table('activity_logs')->delete();
            $message = "System-er shokol ({$deletedCount} ti) activity log permanent delete kora hoyeche.";

        } else {
            // Retention period (days)
            $request->validate([
                'days' => 'required|integer|in:7,15,30,60,90,180,365',
            ]);

            $days = (int) $request->input('days');
            $cutoffDate = now()->subDays($days);
            $deletedCount = DB::table('activity_logs')->where('created_at', '<', $cutoffDate)->delete();
            $message = "{$days} diner beshi purono mot {$deletedCount} ti log entry successfully purge kora hoyeche.";
        }

        return response()->json([
            'success'       => true,
            'message'       => $message,
            'deleted_count' => $deletedCount
        ]);
    }
}