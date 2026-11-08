<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BranchAccessService
{
    /**
     * Session storage key for holding current active branch ID.
     */
    public const SESSION_BRANCH_KEY = 'active_branch_context_id';

    // =========================================================================
    // 1. BRANCH CONTEXT RESOLUTION & SESSION MANAGEMENT
    // =========================================================================

    /**
     * Resolve the active branch ID for the current request/session.
     * Hierarchy:
     * 1. Explicitly requested & validated branch_id
     * 2. Active Session context ID
     * 3. User's primary_branch_id / branch_id
     * 4. First assigned branch from branch_user pivot
     * 5. Main / Head Office Branch fallback
     */
    public static function getCurrentBranchId(?User $user = null): ?int
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return null;
        }

        // 1. Check Session Context
        $sessionBranchId = Session::get(self::SESSION_BRANCH_KEY);
        if ($sessionBranchId && self::canAccessBranch($user, (int) $sessionBranchId)) {
            return (int) $sessionBranchId;
        }

        // 2. Check Primary Branch direct attribute
        $primaryId = $user->primary_branch_id ?: $user->branch_id;
        if ($primaryId && self::canAccessBranch($user, (int) $primaryId)) {
            Session::put(self::SESSION_BRANCH_KEY, (int) $primaryId);
            return (int) $primaryId;
        }

        // 3. Check first assigned branch from pivot
        $firstAssigned = $user->assignedBranches()
            ->where('branch_user.is_active', true)
            ->first();

        if ($firstAssigned) {
            Session::put(self::SESSION_BRANCH_KEY, (int) $firstAssigned->id);
            return (int) $firstAssigned->id;
        }

        // 4. If Super Admin/Global Admin, fallback to Main or first active branch
        if ($user->isSuperAdmin() || strtolower(trim((string)$user->role)) === 'admin' || !empty($user->has_all_branch_access)) {
            $mainBranch = Branch::active()->ofType('main')->first() 
                ?: Branch::active()->first();

            if ($mainBranch) {
                Session::put(self::SESSION_BRANCH_KEY, (int) $mainBranch->id);
                return (int) $mainBranch->id;
            }
        }

        return null;
    }

    /**
     * Set and lock the active branch context into the session.
     */
    public static function setCurrentBranchId(int $branchId, ?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user || !self::canAccessBranch($user, $branchId)) {
            return false;
        }

        $branch = Branch::find($branchId);
        if (!$branch || !$branch->isOperational()) {
            return false;
        }

        Session::put(self::SESSION_BRANCH_KEY, $branchId);
        return true;
    }

    /**
     * Retrieve the active Branch Model instance.
     */
    public static function getCurrentBranch(?User $user = null): ?Branch
    {
        $branchId = self::getCurrentBranchId($user);
        return $branchId ? Branch::find($branchId) : null;
    }

    // =========================================================================
    // 2. AUTHORIZATION & STRICT PERMISSION GUARDS
    // =========================================================================

    /**
     * Check if user is authorized to access a specific branch.
     */
    public static function canAccessBranch(?User $user, int $branchId): bool
    {
        if (!$user) {
            return false;
        }

        return $user->canAccessBranch($branchId);
    }

    /**
     * Check if user can operate POS in a specific branch.
     */
    public static function canAccessPos(?User $user, int $branchId): bool
    {
        if (!$user) {
            return false;
        }

        $branch = Branch::find($branchId);
        if (!$branch || !$branch->isPosEnabled()) {
            return false;
        }

        return $user->canAccessBranchPos($branchId);
    }

    /**
     * Check if user can manage inventory in a specific branch.
     */
    public static function canManageInventory(?User $user, int $branchId): bool
    {
        if (!$user) {
            return false;
        }

        return $user->canManageBranchInventory($branchId);
    }

    /**
     * Check if user is authorized to view financial and sales reports for a branch.
     */
    public static function canViewReports(?User $user, int $branchId): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->isSuperAdmin() || strtolower(trim((string)$user->role)) === 'admin' || !empty($user->has_all_branch_access)) {
            return true;
        }

        if (!self::canAccessBranch($user, $branchId)) {
            return false;
        }

        $pivot = $user->assignedBranches()->where('branches.id', $branchId)->first();
        if ($pivot) {
            return (bool) $pivot->pivot->can_view_reports 
                || in_array($pivot->pivot->role_in_branch, ['branch_manager', 'manager']);
        }

        return false;
    }

    /**
     * Strict Authorization Enforcer.
     * If user is unauthorized, immediately aborts with 403 Forbidden.
     */
    public static function validateBranchAccessOrAbort(int $branchId, string $action = 'access', ?User $user = null): Branch
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            abort(401, 'Unauthenticated user.');
        }

        $branch = Branch::find($branchId);
        if (!$branch) {
            abort(404, "Branch #{$branchId} not found.");
        }

        if (!$branch->isOperational()) {
            abort(403, "Branch '{$branch->name}' is currently inactive, temporarily closed, or suspended.");
        }

        $authorized = match ($action) {
            'pos'       => self::canAccessPos($user, $branchId),
            'inventory' => self::canManageInventory($user, $branchId),
            'reports'   => self::canViewReports($user, $branchId),
            default     => self::canAccessBranch($user, $branchId),
        };

        if (!$authorized) {
            abort(403, "Access Denied: You do not have authorization to perform [{$action}] on branch: {$branch->name}.");
        }

        return $branch;
    }

    // =========================================================================
    // 3. SERVER-SIDE QUERY DATA ISOLATION
    // =========================================================================

    /**
     * Get array of all branch IDs user is authorized to view data for.
     */
    public static function getAllowedBranchIds(?User $user = null): array
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return [];
        }

        return $user->getAssignedBranchIds();
    }

    /**
     * Apply strict server-side Branch Data Isolation to an Eloquent or Query Builder query.
     * Prevents cross-branch data leaks.
     */
    public static function applyBranchScope(
        Builder|QueryBuilder $query,
        ?int $requestedBranchId = null,
        string $column = 'branch_id',
        ?User $user = null
    ): Builder|QueryBuilder {
        $user = $user ?? Auth::user();

        if (!$user) {
            return $query->whereRaw('1 = 0'); // Block all if guest
        }

        // 1. If user is Super Admin or has global access
        if ($user->isSuperAdmin() || strtolower(trim((string)$user->role)) === 'admin' || !empty($user->has_all_branch_access)) {
            if ($requestedBranchId && $requestedBranchId > 0) {
                return $query->where($column, $requestedBranchId);
            }
            return $query; // View all branches
        }

        // 2. Regular Branch Staff / Manager
        $allowedIds = self::getAllowedBranchIds($user);

        if (empty($allowedIds)) {
            return $query->whereRaw('1 = 0'); // No assigned branches
        }

        // If specific branch is requested, ensure it is within their allowed set
        if ($requestedBranchId && $requestedBranchId > 0) {
            if (!in_array($requestedBranchId, $allowedIds)) {
                abort(403, 'Unauthorized cross-branch data query attempted.');
            }
            return $query->where($column, $requestedBranchId);
        }

        // Otherwise restrict to their allowed branches
        return $query->whereIn($column, $allowedIds);
    }

    /**
     * Get list of active branches selectable by the user for switching or filtering.
     */
    public static function getSelectableBranches(?User $user = null): Collection
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return collect();
        }

        if ($user->isSuperAdmin() || strtolower(trim((string)$user->role)) === 'admin' || !empty($user->has_all_branch_access)) {
            return Branch::active()->orderBy('name', 'asc')->get();
        }

        $allowedIds = self::getAllowedBranchIds($user);

        return Branch::active()
            ->whereIn('id', $allowedIds)
            ->orderBy('name', 'asc')
            ->get();
    }
}