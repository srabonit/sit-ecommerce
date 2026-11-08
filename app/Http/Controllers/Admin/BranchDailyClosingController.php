<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchDailyClosing;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\BranchAccessService;
use App\Services\BranchReportService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BranchDailyClosingController extends Controller
{
    /**
     * ১. সকল দৈনিক ক্লোজিং হিস্ট্রি ও অডিট লগ
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $selectableBranches = BranchAccessService::getSelectableBranches($user);

        $defaultBranchId = BranchAccessService::getCurrentBranchId($user) 
            ?: ($selectableBranches->first()->id ?? null);

        $branchId = $request->filled('branch_id') ? (int) $request->branch_id : $defaultBranchId;

        if ($branchId && !BranchAccessService::canAccessBranch($user, $branchId)) {
            abort(403, 'Unauthorized access to the requested branch closing records.');
        }

        $query = DB::table('branch_daily_closings')
            ->leftJoin('branches', 'branch_daily_closings.branch_id', '=', 'branches.id')
            ->leftJoin('users as cashier', 'branch_daily_closings.user_id', '=', 'cashier.id')
            ->leftJoin('users as verifier', 'branch_daily_closings.verified_by', '=', 'verifier.id')
            ->select(
                'branch_daily_closings.*',
                'branches.name as branch_name',
                'branches.code as branch_code',
                'cashier.name as cashier_name',
                'verifier.name as verifier_name'
            );

        if ($branchId) {
            $query->where('branch_daily_closings.branch_id', $branchId);
        } else {
            $allowedIds = BranchAccessService::getAllowedBranchIds($user);
            if (!empty($allowedIds)) {
                $query->whereIn('branch_daily_closings.branch_id', $allowedIds);
            }
        }

        // Status Filter (open, closed, verified)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('branch_daily_closings.status', $request->status);
        }

        // Date Range Filter
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('branch_daily_closings.closing_date', [
                $request->from_date,
                $request->to_date,
            ]);
        }

        $closings = $query->latest('branch_daily_closings.closing_date')
            ->latest('branch_daily_closings.id')
            ->paginate((int) $request->get('per_page', 20))
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.daily_closing.partials.closing_rows', compact('closings'))->render();
        }

        return view('admin.daily_closing.index', compact('closings', 'selectableBranches', 'branchId'));
    }

    /**
     * ২. চলতি দিনের লাইভ ক্যাশ রেজিস্টার সেশন স্টেটাস
     */
    public function currentSession(Request $request, $branch_id = null)
    {
        $user = Auth::user();

        $branchId = (int) ($branch_id 
            ?: ($request->get('branch_id') 
            ?: BranchAccessService::getCurrentBranchId($user)));

        if (!$branchId || !BranchAccessService::canAccessBranch($user, $branchId)) {
            abort(403, 'Unauthorized access to branch cash register session.');
        }

        $branch = Branch::findOrFail($branchId);
        $liveData = BranchReportService::calculateLiveSessionCash($branchId);

        $sessionRecord = DB::table('branch_daily_closings')
            ->where('branch_id', $branchId)
            ->where('closing_date', now()->toDateString())
            ->first();

        return view('admin.daily_closing.session', compact('branch', 'liveData', 'sessionRecord'));
    }

    /**
     * ৩. দৈনিক ক্যাশ রেজিস্টার সেশন ওপেন করা
     */
    public function open(Request $request)
    {
        $request->validate([
            'branch_id'    => 'required|integer|exists:branches,id',
            'opening_cash' => 'required|numeric|min:0',
            'notes'        => 'nullable|string|max:500',
        ]);

        $branchId = (int) $request->branch_id;

        if (!BranchAccessService::canAccessPos(Auth::user(), $branchId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to open register.'], 403);
        }

        try {
            $session = BranchReportService::openDailyRegister(
                branchId: $branchId,
                openingCash: (float) $request->opening_cash,
                note: $request->notes,
                userId: Auth::id()
            );

            if (class_exists(ActivityLogger::class)) {
                ActivityLogger::log('register_opened', 'pos', "Cash register opened for branch #{$branchId} with ৳{$request->opening_cash}", [
                    'record_id'  => $session->id,
                    'record_ref' => "Branch #{$branchId}",
                    'new_values' => (array) $session,
                ]);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cash register opened successfully.',
                    'session' => $session,
                ]);
            }

            return redirect()->back()->with('success', 'Cash register opened successfully for today.');
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * ৪. বাস্তব ক্যাশ গণনা সাবমিট ও রেজিস্টার সেশন ক্লোজ করা
     */
    public function close(Request $request)
    {
        $request->validate([
            'branch_id'   => 'required|integer|exists:branches,id',
            'actual_cash' => 'required|numeric|min:0',
            'notes'       => 'nullable|string|max:500',
        ]);

        $branchId = (int) $request->branch_id;

        if (!BranchAccessService::canAccessPos(Auth::user(), $branchId)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to close register.'], 403);
        }

        try {
            $session = BranchReportService::closeDailyRegister(
                branchId: $branchId,
                actualCash: (float) $request->actual_cash,
                note: $request->notes,
                userId: Auth::id()
            );

            if (class_exists(ActivityLogger::class)) {
                ActivityLogger::log('register_closed', 'pos', "Cash register closed for branch #{$branchId}. Diff: ৳{$session->difference}", [
                    'record_id'  => $session->id,
                    'record_ref' => "Branch #{$branchId}",
                    'new_values' => (array) $session,
                ]);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success'    => true,
                    'message'    => 'Cash register successfully closed.',
                    'difference' => $session->difference,
                    'session'    => $session,
                    'print_url'  => route('admin.daily_closing.print', $session->id),
                ]);
            }

            return redirect()->route('admin.daily_closing.index', ['branch_id' => $branchId])
                ->with('success', "Cash register closed. Difference: ৳{$session->difference}");
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * ৫. রেজিস্টার ক্লোজিং অডিট ভেরিফিকেশন (ম্যানেজার / অ্যাডমিন দ্বারা)
     */
    public function verify(Request $request, $id)
    {
        $user = Auth::user();

        $session = DB::table('branch_daily_closings')->where('id', $id)->first();
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Session record not found.'], 404);
        }

        if (!BranchAccessService::canViewReports($user, $session->branch_id)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to verify register records.'], 403);
        }

        DB::table('branch_daily_closings')->where('id', $id)->update([
            'status'      => 'verified',
            'verified_by' => $user->id,
            'updated_at'  => now(),
        ]);

        if (class_exists(ActivityLogger::class)) {
            ActivityLogger::log('register_verified', 'audit', "Register session #{$id} verified by {$user->name}", [
                'record_id'  => $id,
                'record_ref' => "Branch #{$session->branch_id}",
            ]);
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Daily closing session verified and locked.',
            'verifier' => $user->name,
        ]);
    }

    /**
     * ৬. নির্দিষ্ট ক্লোজিং সেশনের বিস্তারিত তথ্য (AJAX Modal View)
     */
    public function show($id)
    {
        $session = DB::table('branch_daily_closings')
            ->leftJoin('branches', 'branch_daily_closings.branch_id', '=', 'branches.id')
            ->leftJoin('users as cashier', 'branch_daily_closings.user_id', '=', 'cashier.id')
            ->leftJoin('users as verifier', 'branch_daily_closings.verified_by', '=', 'verifier.id')
            ->where('branch_daily_closings.id', $id)
            ->select(
                'branch_daily_closings.*',
                'branches.name as branch_name',
                'branches.code as branch_code',
                'cashier.name as cashier_name',
                'verifier.name as verifier_name'
            )
            ->first();

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Closing session not found.'], 404);
        }

        if (!BranchAccessService::canAccessBranch(Auth::user(), $session->branch_id)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        return response()->json([
            'success' => true,
            'session' => $session,
        ]);
    }

    /**
     * ৭. দৈনিক ক্যাশ ক্লোজিং সামারি প্রিন্ট শিট
     */
    public function printSummary($id)
    {
        $session = DB::table('branch_daily_closings')
            ->leftJoin('branches', 'branch_daily_closings.branch_id', '=', 'branches.id')
            ->leftJoin('users as cashier', 'branch_daily_closings.user_id', '=', 'cashier.id')
            ->leftJoin('users as verifier', 'branch_daily_closings.verified_by', '=', 'verifier.id')
            ->where('branch_daily_closings.id', $id)
            ->select(
                'branch_daily_closings.*',
                'branches.name as branch_name',
                'branches.code as branch_code',
                'branches.address as branch_address',
                'branches.phone as branch_phone',
                'cashier.name as cashier_name',
                'verifier.name as verifier_name'
            )
            ->first();

        if (!$session) {
            abort(404, 'Closing report not found.');
        }

        if (!BranchAccessService::canAccessBranch(Auth::user(), $session->branch_id)) {
            abort(403, 'Unauthorized access to closing report.');
        }

        return view('admin.daily_closing.print_summary', compact('session'));
    }
}