<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Traits\HasBranchScope;
use App\Http\Controllers\Admin\RolePermissionController;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, HasBranchScope;

    protected $fillable = [
        'branch_id',
        'warehouse_id',
        'name',
        'email',
        'phone',
        'phone_alt',
        'email_alt',
        'password',
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
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
        'is_super_admin'    => 'boolean',
        'is_locked'         => 'boolean',
        'monthly_salary'    => 'decimal:2',
        'daily_rate'        => 'decimal:2',
        'last_login_at'     => 'datetime',
        'last_activity_at'  => 'datetime',
        'joining_date'      => 'date',
        'resignation_date'  => 'date',
        'deleted_at'        => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class, 'branch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

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

    /**
     * কেন্দ্রীয় পারমিশন ভ্যালিডেশন ইঞ্জিন (ড্যাশবোর্ড হার্ডকোড মুক্ত)[cite: 42]
     */
    public function hasAccess(?string $permissionOrRoute): bool
    {
        if (empty($permissionOrRoute)) {
            return true;
        }

        // ১. সুপার অ্যাডমিনদের সকল ক্ষেত্রে পূর্ণ অনুমোদন
        if ($this->isSuperAdmin()) {
            return true;
        }

        // ২. কাস্টমারদের কোনো ইন্টারনাল অ্যাক্সেস নেই
        if ($this->isCustomer()) {
            return false;
        }

        // ৩. 'admin' রোল সরাসরি বাইপাস
        $role = str_replace(['-', ' '], '_', strtolower(trim((string)$this->role)));
        if ($role === 'admin') {
            return true;
        }

        // ৪. সাধারণ ইউটিলিটি রাউট
        $commonRoutes = [
            'admin.search.ajax',
            'admin.profile.index',
            'admin.profile.password',
        ];
        if (in_array($permissionOrRoute, $commonRoutes)) {
            return true;
        }

        // ৫. অ্যাকাউন্ট নিষ্ক্রিয় বা লক থাকলে অ্যাক্সেস বাতিল
        if (!$this->is_active || !empty($this->is_locked)) {
            return false;
        }

        // ৬. Spatie role_has_permissions চেক
        if (Schema::hasTable('role_has_permissions') && Schema::hasTable('roles') && Schema::hasTable('permissions')) {
            $hasExact = DB::table('role_has_permissions')
                ->join('roles', 'role_has_permissions.role_id', '=', 'roles.id')
                ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
                ->where(function ($q) use ($role) {
                    $q->where('roles.name', $this->role)
                      ->orWhere('roles.name', $role)
                      ->orWhere('roles.name', str_replace('_', '-', $role));
                })
                ->where('permissions.name', $permissionOrRoute)
                ->exists();

            if ($hasExact) {
                return true;
            }
        }

        // ৭. role_route_permissions টেবিলের CRUD স্কিমা চেক
        if (Schema::hasTable('role_route_permissions')) {
            $tableCols = Schema::getColumnListing('role_route_permissions');
            $roleCol = in_array('role_name', $tableCols) ? 'role_name' : 'role';

            if (in_array('can_read', $tableCols) && in_array('route_module', $tableCols)) {
                $systemModules = RolePermissionController::getSystemModules();
                $actionType = RolePermissionController::detectActionType($permissionOrRoute);

                $targetModule = null;
                foreach ($systemModules as $moduleTitle => $moduleInfo) {
                    if (isset($moduleInfo['permissions'][$permissionOrRoute])) {
                        $targetModule = Str::slug($moduleTitle, '_');
                        break;
                    }
                }

                if (!$targetModule) {
                    $parts = explode('.', $permissionOrRoute);
                    $targetModule = count($parts) >= 2 ? $parts[1] : 'dashboard';
                }

                $permRow = DB::table('role_route_permissions')
                    ->where(function ($q) use ($roleCol, $role) {
                        $q->where($roleCol, $this->role)
                          ->orWhere($roleCol, $role)
                          ->orWhere($roleCol, str_replace('_', '-', $role))
                          ->orWhere($roleCol, str_replace('_', ' ', $role));
                    })
                    ->where('route_module', $targetModule)
                    ->first();

                if (!$permRow) {
                    return false;
                }

                return match ($actionType) {
                    'create' => (bool) $permRow->can_create,
                    'update' => (bool) $permRow->can_update,
                    'delete' => (bool) $permRow->can_delete,
                    default  => (bool) $permRow->can_read,
                };
            }
        }

        return false;
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