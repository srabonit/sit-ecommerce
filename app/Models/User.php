<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Traits\HasBranchScope;
use App\Http\Controllers\Admin\RolePermissionController;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, HasBranchScope;

    /**
     * Request-level runtime cache for instant rendering without duplicate queries
     */
    protected static array $runtimePermCache = [];

    protected $fillable = [
        'branch_id',
        'warehouse_id',
        'primary_branch_id',
        'has_all_branch_access',
        'name',
        'email',
        'email_verified_at',
        'phone',
        'phone_verified_at',
        'phone_alt',
        'email_alt',
        'password',
        'google_id',
        'google_avatar',
        'two_factor_enabled',
        'two_factor_type',
        'two_factor_secret',
        'two_factor_confirmed_at',
        'role',
        'user_type',
        'designation',
        'gender',
        'dob',
        'nid_number',
        'joining_date',
        'resignation_date',
        'reference_name',
        'reference_phone',
        'photo',
        'bio',
        'address',
        'district',
        'note',
        'monthly_salary',
        'daily_rate',
        'salary_pay_day',
        'is_super_admin',
        'is_active',
        'status',
        'is_locked',
        'locked_reason',
        'password_attempts',
        'otp_attempts',
        'last_login_at',
        'last_login_ip',
        'last_activity_at',
        'created_by',
        'deleted_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at'         => 'datetime',
        'phone_verified_at'         => 'datetime',
        'password'                  => 'hashed',
        'two_factor_enabled'        => 'boolean',
        'two_factor_confirmed_at'   => 'datetime',
        'is_active'                 => 'boolean',
        'is_super_admin'            => 'boolean',
        'is_locked'                 => 'boolean',
        'has_all_branch_access'     => 'boolean',
        'primary_branch_id'         => 'integer',
        'monthly_salary'            => 'decimal:2',
        'daily_rate'                => 'decimal:2',
        'last_login_at'             => 'datetime',
        'last_activity_at'          => 'datetime',
        'joining_date'              => 'date',
        'resignation_date'          => 'date',
        'deleted_at'                => 'datetime',
    ];

    // =========================================================================
    // 1. EXISTING ORIGINAL RELATIONSHIPS (PRESERVED)
    // =========================================================================

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class, 'branch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // =========================================================================
    // 2. ADVANCED MULTI-BRANCH RELATIONSHIPS
    // =========================================================================

    /**
     * Primary / Default home branch for this user.
     */
    public function primaryBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'primary_branch_id');
    }

    /**
     * All branches assigned to this user via multi-branch pivot.
     */
    public function assignedBranches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_user', 'user_id', 'branch_id')
            ->withPivot([
                'is_primary',
                'role_in_branch',
                'can_access_pos',
                'can_manage_inventory',
                'can_view_reports',
                'is_active',
            ])
            ->withTimestamps();
    }

    /**
     * Orders attributed to this user as Sales Person.
     */
    public function salesOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'sales_person_id');
    }

    /**
     * Daily cash register closing sessions submitted by this user.
     */
    public function closedRegisters(): HasMany
    {
        return $this->hasMany(BranchDailyClosing::class, 'user_id');
    }

    // =========================================================================
    // 3. MULTI-BRANCH ACCESS & SECURITY HELPERS
    // =========================================================================

    /**
     * Check if user is authorized to view/operate inside a specific branch.
     */
    public function canAccessBranch(int $branchId): bool
    {
        // 1. Super Admin and global 'admin' role can access all branches
        if ($this->isSuperAdmin() || strtolower(trim((string)$this->role)) === 'admin') {
            return true;
        }

        // 2. Accounts/Management with all-branch authorization flag
        if (!empty($this->has_all_branch_access)) {
            return true;
        }

        // 3. Check primary branch direct assignment
        if ((int)$this->primary_branch_id === $branchId || (int)$this->branch_id === $branchId) {
            return true;
        }

        // 4. Check branch_user pivot assignment
        if ($this->relationLoaded('assignedBranches')) {
            $assignment = $this->assignedBranches->firstWhere('id', $branchId);
            return $assignment && (bool) $assignment->pivot->is_active;
        }

        return $this->assignedBranches()
            ->where('branches.id', $branchId)
            ->where('branch_user.is_active', true)
            ->exists();
    }

    /**
     * Get list of all branch IDs this user has permission to access.
     */
    public function getAssignedBranchIds(): array
    {
        if ($this->isSuperAdmin() || strtolower(trim((string)$this->role)) === 'admin' || !empty($this->has_all_branch_access)) {
            return Branch::pluck('id')->toArray();
        }

        $ids = [];
        if ($this->primary_branch_id) {
            $ids[] = (int) $this->primary_branch_id;
        }
        if ($this->branch_id) {
            $ids[] = (int) $this->branch_id;
        }

        $pivotIds = $this->assignedBranches()
            ->where('branch_user.is_active', true)
            ->pluck('branches.id')
            ->toArray();

        return array_values(array_unique(array_merge($ids, $pivotIds)));
    }

    /**
     * Verify if user has permission to operate POS in a specific branch.
     */
    public function canAccessBranchPos(int $branchId): bool
    {
        if ($this->isSuperAdmin() || strtolower(trim((string)$this->role)) === 'admin') {
            return true;
        }

        if (!$this->canAccessBranch($branchId)) {
            return false;
        }

        $pivot = $this->assignedBranches()->where('branches.id', $branchId)->first();
        if ($pivot) {
            return (bool) $pivot->pivot->can_access_pos;
        }

        return true;
    }

    /**
     * Verify if user has permission to manage inventory in a specific branch.
     */
    public function canManageBranchInventory(int $branchId): bool
    {
        if ($this->isSuperAdmin() || strtolower(trim((string)$this->role)) === 'admin') {
            return true;
        }

        if (!$this->canAccessBranch($branchId)) {
            return false;
        }

        $pivot = $this->assignedBranches()->where('branches.id', $branchId)->first();
        if ($pivot) {
            return (bool) $pivot->pivot->can_manage_inventory;
        }

        return false;
    }

    // =========================================================================
    // 4. USER ROLE & CUSTOMER IDENTIFIERS (PRESERVED 100%)
    // =========================================================================

    public function isSuperAdmin(): bool
    {
        $role = str_replace(['-', ' '], '_', strtolower(trim((string)$this->role)));
        return !empty($this->is_super_admin) || $role === 'super_admin';
    }

    public function isCustomer(): bool
    {
        if ($this->isSuperAdmin()) {
            return false;
        }

        $staffRoles = [
            'admin',
            'super_admin',
            'branch_manager',
            'staff',
            'manager',
            'cashier',
            'pos_operator',
            'operator',
            'moderator',
            'order_manager',
            'courier_manager',
            'inventory_manager',
            'accounts'
        ];

        $role = str_replace(['-', ' '], '_', strtolower(trim((string)$this->role)));
        if (in_array($role, $staffRoles)) {
            return false;
        }

        $userType = str_replace(['-', ' '], '_', strtolower(trim((string)($this->user_type ?? ''))));
        if (in_array($userType, ['admin', 'staff'])) {
            return false;
        }

        if (Schema::hasTable('role_route_permissions')) {
            $hasRole = DB::table('role_route_permissions')
                ->where('role_name', $this->role)
                ->orWhere('role_name', $role)
                ->orWhere('role_name', str_replace('_', '-', $role))
                ->exists();

            if ($hasRole) {
                return false;
            }
        }

        return true;
    }

    public function isStaffOrAdmin(): bool
    {
        return !$this->isCustomer();
    }

    // --- Authentication & Verification Helper Methods ---

    public function hasVerifiedPhone(): bool
    {
        return !is_null($this->phone_verified_at);
    }

    public function markPhoneAsVerified(): bool
    {
        $this->phone_verified_at = now();
        return $this->save();
    }

    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    public function hasGoogleLinked(): bool
    {
        return !empty($this->google_id);
    }

    public function hasTwoFactorEnabled(): bool
    {
        return (bool) $this->two_factor_enabled && !is_null($this->two_factor_confirmed_at);
    }

    // --- Authentication Scopes ---

    public function scopePhoneVerified($query)
    {
        return $query->whereNotNull('phone_verified_at');
    }

    public function scopeEmailVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeGoogleLinked($query)
    {
        return $query->whereNotNull('google_id');
    }

    /**
     * Resolve all companion routes for granular actions (.show, AJAX search, .store, .update)
     */
    protected function getCompanionCandidateRoutes(string $route): array
    {
        $candidates = [$route];

        if (str_ends_with($route, '.show') || str_ends_with($route, '.view') || str_ends_with($route, '.details')) {
            $prefix = preg_replace('/\.(show|view|details)$/', '', $route);
            if (!empty($prefix)) {
                $candidates[] = $prefix . '.index';
                $candidates[] = $prefix . '.view';
                $candidates[] = $prefix;
            }
        }

        if (str_contains($route, '_ajax') || str_contains($route, '.ajax')) {
            $basePrefix = preg_replace('/(\.?[a-zA-Z0-9_-]+_ajax|\.ajax)$/', '', $route);
            if (!empty($basePrefix)) {
                $candidates[] = $basePrefix . '.create';
                $candidates[] = $basePrefix . '.index';
                $candidates[] = $basePrefix . '.edit';
                $candidates[] = $basePrefix . '.show';
            }
            $parts = explode('.', $route);
            if (count($parts) >= 3) {
                $candidates[] = $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.create';
                $candidates[] = $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.index';
            }
        }

        if (str_ends_with($route, '.store')) {
            $candidates[] = substr($route, 0, -6) . '.create';
            $candidates[] = substr($route, 0, -6) . '.index';
        }

        if (str_ends_with($route, '.update')) {
            $candidates[] = substr($route, 0, -7) . '.edit';
            $candidates[] = substr($route, 0, -7) . '.index';
        }

        if (str_contains($route, '.toggle') || str_contains($route, '.status') || str_contains($route, '.check_status')) {
            $candidates[] = preg_replace('/\.(toggle.*|status.*|check_status.*)$/', '.index', $route);
            $candidates[] = preg_replace('/\.(toggle.*|status.*|check_status.*)$/', '.edit', $route);
        }

        if (str_contains($route, '.search') || str_contains($route, '.print') || str_contains($route, '.download') || str_contains($route, '.export') || str_contains($route, '.receipt') || str_contains($route, '.thermal')) {
            $parts = explode('.', $route);
            if (count($parts) >= 2) {
                $candidates[] = $parts[0] . '.' . $parts[1] . '.index';
                $candidates[] = $parts[0] . '.' . $parts[1] . '.create';
                if (isset($parts[2])) {
                    $candidates[] = $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.index';
                    $candidates[] = $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.create';
                }
            }
        }

        if ($route === 'admin.order.update_status')          $candidates[] = 'admin.order.status.update';
        if ($route === 'admin.order.send_courier')           $candidates[] = 'admin.order.courier.send';
        if ($route === 'admin.order.invoice')                $candidates[] = 'admin.order.invoice.print';
        if ($route === 'admin.order.delete')                 $candidates[] = 'admin.order.destroy';
        if ($route === 'admin.order.bulk_action')            $candidates[] = 'admin.order.bulk.action';
        if ($route === 'admin.order.search_products')        $candidates[] = 'admin.order.create_manual';
        if ($route === 'admin.order.search_customers')       $candidates[] = 'admin.order.create_manual';
        if ($route === 'admin.order.store_manual')           $candidates[] = 'admin.order.create_manual';

        if ($route === 'admin.sms.campaigns.filter_recipients_ajax') {
            $candidates[] = 'admin.sms.campaigns.create';
            $candidates[] = 'admin.sms.campaigns.index';
        }
        if ($route === 'admin.sms.campaigns.show') {
            $candidates[] = 'admin.sms.campaigns.index';
            $candidates[] = 'admin.sms.campaigns.create';
        }
        if ($route === 'admin.sms.logs.show' || $route === 'admin.sms.logs.check_status') {
            $candidates[] = 'admin.sms.logs.index';
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    /**
     * Professional Granular Permission Engine with Enterprise Strict Isolation
     */
    public function hasAccess(?string $permissionOrRoute): bool
    {
        if (empty($permissionOrRoute)) {
            return true;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isCustomer()) {
            return false;
        }

        $role = str_replace(['-', ' '], '_', strtolower(trim((string)$this->role)));
        if ($role === 'admin') {
            return true;
        }

        if (!$this->is_active || !empty($this->is_locked)) {
            return false;
        }

        $commonRoutes = [
            'admin.search.ajax',
            'admin.profile.index',
            'admin.profile.update',
            'admin.profile.password',
        ];
        if (in_array($permissionOrRoute, $commonRoutes)) {
            return true;
        }

        $cacheKey = "u_{$this->id}_" . md5($permissionOrRoute);
        if (isset(static::$runtimePermCache[$cacheKey])) {
            return static::$runtimePermCache[$cacheKey];
        }

        $candidateRoutes = $this->getCompanionCandidateRoutes($permissionOrRoute);

        if (Schema::hasTable('role_has_permissions') && Schema::hasTable('roles') && Schema::hasTable('permissions')) {
            $hasExact = DB::table('role_has_permissions')
                ->join('roles', 'role_has_permissions.role_id', '=', 'roles.id')
                ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
                ->where(function ($q) use ($role) {
                    $q->where('roles.name', $this->role)
                      ->orWhere('roles.name', $role)
                      ->orWhere('roles.name', str_replace('_', '-', $role))
                      ->orWhere('roles.name', str_replace('_', ' ', $role));
                })
                ->whereIn('permissions.name', $candidateRoutes)
                ->exists();

            if ($hasExact) {
                return static::$runtimePermCache[$cacheKey] = true;
            }
        }

        if (Schema::hasTable('role_route_permissions')) {
            $tableCols = Schema::getColumnListing('role_route_permissions');
            $roleCol = in_array('role_name', $tableCols) ? 'role_name' : 'role';

            if (in_array('can_read', $tableCols) && in_array('route_module', $tableCols)) {
                $targetModule = class_exists(RolePermissionController::class) && method_exists(RolePermissionController::class, 'resolveModuleSlugForRoute')
                    ? RolePermissionController::resolveModuleSlugForRoute($permissionOrRoute)
                    : null;

                if (!$targetModule) {
                    $systemModules = RolePermissionController::getSystemModules();
                    foreach ($systemModules as $moduleTitle => $moduleInfo) {
                        foreach ($candidateRoutes as $cRoute) {
                            if (isset($moduleInfo['permissions'][$cRoute])) {
                                $targetModule = Str::slug($moduleTitle, '_');
                                break 2;
                            }
                        }
                    }
                }

                if ($targetModule) {
                    $permRow = DB::table('role_route_permissions')
                        ->where(function ($q) use ($roleCol, $role) {
                            $q->where($roleCol, $this->role)
                              ->orWhere($roleCol, $role)
                              ->orWhere($roleCol, str_replace('_', '-', $role))
                              ->orWhere($roleCol, str_replace('_', ' ', $role));
                        })
                        ->where('route_module', $targetModule)
                        ->first();

                    if ($permRow) {
                        $actionType = RolePermissionController::detectActionType($permissionOrRoute);

                        $allowed = match ($actionType) {
                            'create' => (bool) ($permRow->can_create || $permRow->can_read),
                            'update' => (bool) ($permRow->can_update || $permRow->can_read),
                            'delete' => (bool) $permRow->can_delete,
                            default  => (bool) ($permRow->can_read || $permRow->can_create),
                        };

                        if ($allowed) {
                            return static::$runtimePermCache[$cacheKey] = true;
                        }
                    }
                }
            }
        }

        return static::$runtimePermCache[$cacheKey] = false;
    }

    /**
     * পুরো মডিউলে কোনো অ্যাক্সেস আছে কি না তা দ্রুত যাচাই করার হেল্পার
     */
    public function canAccessModule(string $moduleTitleOrSlug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $role = str_replace(['-', ' '], '_', strtolower(trim((string)$this->role)));
        if ($role === 'admin') {
            return true;
        }

        if (!$this->is_active || !empty($this->is_locked) || $this->isCustomer()) {
            return false;
        }

        $slug = Str::slug($moduleTitleOrSlug, '_');

        if ($slug === 'dashboard') {
            return $this->hasAccess('admin.dashboard');
        }

        $cacheKey = "u_{$this->id}_mod_" . $slug;
        if (isset(static::$runtimePermCache[$cacheKey])) {
            return static::$runtimePermCache[$cacheKey];
        }

        if (Schema::hasTable('role_has_permissions') && Schema::hasTable('roles') && Schema::hasTable('permissions')) {
            $systemModules = RolePermissionController::getSystemModules();
            $moduleRoutes = [];
            foreach ($systemModules as $title => $data) {
                if (Str::slug($title, '_') === $slug) {
                    $moduleRoutes = array_keys($data['permissions'] ?? []);
                    break;
                }
            }

            if (!empty($moduleRoutes)) {
                $hasSpatie = DB::table('role_has_permissions')
                    ->join('roles', 'role_has_permissions.role_id', '=', 'roles.id')
                    ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
                    ->where(function ($q) use ($role) {
                        $q->where('roles.name', $this->role)
                          ->orWhere('roles.name', $role);
                    })
                    ->whereIn('permissions.name', $moduleRoutes)
                    ->exists();

                if ($hasSpatie) {
                    return static::$runtimePermCache[$cacheKey] = true;
                }
            }
        }

        if (Schema::hasTable('role_route_permissions')) {
            $hasModuleAccess = DB::table('role_route_permissions')
                ->where(function ($q) use ($role) {
                    $q->where('role_name', $this->role)
                      ->orWhere('role_name', $role)
                      ->orWhere('role_name', str_replace('_', '-', $role))
                      ->orWhere('role_name', str_replace('_', ' ', $role));
                })
                ->where(function ($q) use ($slug) {
                    $q->where('route_module', $slug)
                      ->orWhere('route_module', 'like', "%{$slug}%");
                })
                ->where(function ($q) {
                    $q->where('can_read', 1)
                      ->orWhere('can_create', 1)
                      ->orWhere('can_update', 1)
                      ->orWhere('can_delete', 1);
                })
                ->exists();

            return static::$runtimePermCache[$cacheKey] = $hasModuleAccess;
        }

        return static::$runtimePermCache[$cacheKey] = false;
    }

    public function hasAnyAccess(array $permissions): bool
    {
        if ($this->isSuperAdmin() || strtolower(trim((string)$this->role)) === 'admin') {
            return true;
        }

        foreach ($permissions as $p) {
            if ($this->hasAccess($p)) {
                return true;
            }
        }

        return false;
    }
}