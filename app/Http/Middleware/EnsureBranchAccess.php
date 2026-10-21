<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ActivityLogger;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        $user = Auth::user();

        // Super Admin ebong HQ users shokol branch access korte parbe
        if ($user->isSuperAdmin() || empty($user->branch_id)) {
            return $next($request);
        }

        // Route theke target branch ID collect kora ( /admin/pos/terminal/{branch_id} ba /admin/branch/edit/{id})
        $targetBranchId = $request->route('branch_id') 
            ?? $request->route('branch') 
            ?? $request->input('branch_id');

        // Jodi request-e kono branch ID thake ebong ta user-er assigned branch-er shathe na mile
        if (!empty($targetBranchId) && (int)$targetBranchId !== (int)$user->branch_id) {
            
            // Security event audit log-e record kora
            ActivityLogger::log('unauthorized_branch_access', 'security', 'Attempted to access unauthorized branch data', [
                'record_id'   => $targetBranchId,
                'record_ref'  => 'Branch #' . $targetBranchId,
                'old_values'  => ['assigned_branch_id' => $user->branch_id],
                'new_values'  => ['target_branch_id' => $targetBranchId],
                'result'      => 'failed',
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access Denied: You are not authorized to view or manage data of another branch.'
                ], 403);
            }

            abort(403, 'Access Denied: You can only access operations for your assigned branch (Hub #' . $user->branch_id . ').');
        }

        return $next($request);
    }
}