<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Branch;
use App\Services\ActivityLogger;
use App\Services\BranchAccessService;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $requiredAction
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ?string $requiredAction = null): Response
    {
        // ১. অথেন্টিকেশন চেক (আগের মতো admin.login এ রিডাইরেক্ট)
        if (!Auth::check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated session.'
                ], 401);
            }
            return redirect()->route('admin.login');
        }

        $user = Auth::user();

        // ২. রিকোয়েস্ট থেকে টার্গেট ব্রাঞ্চ আইডি সংগ্রহ করা
        $targetBranchId = $this->resolveTargetBranchId($request);

        // ৩. সুপার অ্যাডমিন, গ্লোবাল 'admin' এবং হেড অফিস ইউজারদের জন্য গ্লোবাল এক্সেস
        $hasGlobalAccess = $user->isSuperAdmin() 
            || strtolower(trim((string)$user->role)) === 'admin' 
            || !empty($user->has_all_branch_access)
            || (empty($user->branch_id) && empty($user->primary_branch_id) && !$user->isCustomer());

        if ($hasGlobalAccess) {
            if (!empty($targetBranchId) && is_numeric($targetBranchId)) {
                BranchAccessService::setCurrentBranchId((int)$targetBranchId, $user);
                $request->attributes->set('current_branch_id', (int)$targetBranchId);
            }
            return $next($request);
        }

        // ৪. যদি রিকোয়েস্টে নির্দিষ্ট কোনো ব্রাঞ্চ আইডি না থাকে, ইউজারের ডিফল্ট ব্রাঞ্চ রেজলভ করা
        if (empty($targetBranchId)) {
            $targetBranchId = BranchAccessService::getCurrentBranchId($user) 
                ?: ($user->primary_branch_id ?: $user->branch_id);
        }

        $targetBranchId = (int) $targetBranchId;

        // ৫. ব্রাঞ্চ আইডি ভ্যালিডেশন এবং ইউজারের অথরাইজেশন যাচাই
        $isAuthorized = false;
        if ($targetBranchId > 0) {
            // ব্রাঞ্চ বিদ্যমান ও সচল কি না যাচাই
            $branch = Branch::find($targetBranchId);
            if ($branch && $branch->isOperational()) {
                // ইউজারের সিঙ্গেল branch_id অথবা multi-branch pivot (assignedBranches) চেক
                $isAuthorized = $user->canAccessBranch($targetBranchId);

                // যদি পিওএস টার্মিনাল এক্সেস হয়, পিওএস পারমিশন ও ব্রাঞ্চ পিওএস স্ট্যাটাস যাচাই
                $action = $requiredAction ?: $this->detectActionFromRoute($request);
                if ($isAuthorized && $action === 'pos') {
                    $isAuthorized = BranchAccessService::canAccessPos($user, $targetBranchId);
                }
            }
        }

        // ৬. অননুমোদিত এক্সেস হলে ActivityLogger-এ সিকিউরিটি ইভেন্ট রেকর্ড ও ব্লক
        if (!$isAuthorized || $targetBranchId <= 0) {
            $assignedBranchId = $user->primary_branch_id ?: $user->branch_id;
            $allowedBranches = method_exists($user, 'getAssignedBranchIds') 
                ? $user->getAssignedBranchIds() 
                : array_filter([$assignedBranchId]);

            // বিদ্যমান ActivityLogger অক্ষুণ্ণ রাখা হয়েছে
            ActivityLogger::log('unauthorized_branch_access', 'security', 'Attempted to access unauthorized branch data', [
                'record_id'   => $targetBranchId,
                'record_ref'  => 'Branch #' . $targetBranchId,
                'old_values'  => [
                    'assigned_branch_id' => $assignedBranchId,
                    'allowed_branches'   => $allowedBranches,
                ],
                'new_values'  => ['target_branch_id' => $targetBranchId],
                'result'      => 'failed',
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access Denied: You are not authorized to view or manage data of another branch.'
                ], 403);
            }

            abort(403, 'Access Denied: You can only access operations for your assigned branch (Hub #' . ($assignedBranchId ?: implode(',', $allowedBranches)) . ').');
        }

        // ৭. সেশন ও রিকোয়েস্টে সক্রিয় ব্রাঞ্চ কনটেক্সট লক করা
        BranchAccessService::setCurrentBranchId($targetBranchId, $user);
        $request->attributes->set('current_branch_id', $targetBranchId);

        return $next($request);
    }

    /**
     * রিকোয়েস্টের সমস্ত সম্ভাব্য ভেক্টর থেকে টার্গেট ব্রাঞ্চ আইডি সংগ্রহ করা
     */
    protected function resolveTargetBranchId(Request $request): mixed
    {
        // Route parameters: {branch_id}, {branch}, {id}
        if ($request->route('branch_id')) {
            $val = $request->route('branch_id');
            return is_object($val) ? $val->id : $val;
        }

        if ($request->route('branch')) {
            $val = $request->route('branch');
            return is_object($val) ? $val->id : $val;
        }

        if ($request->route('id') && (str_contains($request->route()->getName() ?? '', 'branch.') || str_contains($request->path(), 'admin/branch/'))) {
            return $request->route('id');
        }

        // Request Input / Query / JSON
        if ($request->filled('branch_id')) {
            return $request->input('branch_id');
        }

        if ($request->query('branch_id')) {
            return $request->query('branch_id');
        }

        return null;
    }

    /**
     * রিকোয়েস্ট রাউট থেকে প্রয়োজনীয় অপারেশনাল একশন শনাক্ত করা
     */
    protected function detectActionFromRoute(Request $request): string
    {
        $routeName = (string) ($request->route()?->getName() ?? '');
        $uri = (string) $request->getRequestUri();

        if (str_contains($routeName, 'pos.') || str_contains($uri, '/admin/pos')) {
            return 'pos';
        }

        if (str_contains($routeName, 'inventory.') || str_contains($uri, '/admin/inventory')) {
            return 'inventory';
        }

        if (str_contains($routeName, 'report.') || str_contains($uri, '/admin/report')) {
            return 'reports';
        }

        return 'access';
    }
}