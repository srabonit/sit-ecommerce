<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasBranchScope
{
    /**
     * Eloquent Model Query-te automated branch scoping
     */
    public function scopeForBranch(Builder $query, ?int $branchId = null): Builder
    {
        $user = Auth::user();

        // 1. Jodi authenticated user na thake
        if (!$user) {
            return $query;
        }

        // 2. Explicit branch ID pathale shei branch filter hobe
        if (!empty($branchId)) {
            return $query->where($this->getTable() . '.branch_id', $branchId);
        }

        // 3. Super Admin ebong Central Admin-der jonno all branch data open thakbe
        if ($user->isSuperAdmin() || empty($user->branch_id)) {
            return $query;
        }

        // 4. Branch Manager ebong Staff-der shudhu tader assigned branch filter kora hobe
        return $query->where($this->getTable() . '.branch_id', $user->branch_id);
    }

    /**
     * Nirdishto branch access check korar helper method
     */
    public static function canAccessBranch(int|string|null $targetBranchId): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Super Admin ba central office user shob branch access korte pare
        if ($user->isSuperAdmin() || empty($user->branch_id)) {
            return true;
        }

        return (int)$user->branch_id === (int)$targetBranchId;
    }
}