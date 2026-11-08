<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Branch extends Model
{
    use HasFactory;

    protected $table = 'branches';

    protected $fillable = [
        'name',
        'code',
        'branch_code',
        'type',
        'branch_type',
        'phone',
        'email',
        'address',
        'manager_id',
        'status',
        'is_active',
        'opening_date',
        'opening_hours',
        'branch_logo',
        'description',
        'cash_account_id',
        'bank_account_id',
        'assigned_warehouse_id',
        'settings',
        // Strict Catalog Access Policy (all_catalog, assigned_only)
        'catalog_access_mode',
    ];

    protected $casts = [
        'is_active'             => 'boolean',
        'opening_date'          => 'date',
        'cash_account_id'       => 'integer',
        'bank_account_id'       => 'integer',
        'assigned_warehouse_id' => 'integer',
        'manager_id'            => 'integer',
        'settings'              => 'array',
    ];

    // ==========================================
    // 1. EXISTING ORIGINAL RELATIONSHIPS (PRESERVED)
    // ==========================================

    /**
     * Existing Warehouses belonging to this branch.
     */
    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    /**
     * Existing direct users belonging to this branch (Legacy support).
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // ==========================================
    // 2. ADVANCED MULTI-BRANCH RELATIONSHIPS
    // ==========================================

    /**
     * Branch Manager / In-charge user.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Staff / Cashiers / Sales Persons assigned via multi-branch pivot (branch_user).
     */
    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'branch_user', 'branch_id', 'user_id')
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
     * Products explicitly assigned to this branch (Mode B: Selected Branches).
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'branch_product', 'branch_id', 'product_id')
            ->withPivot([
                'is_active',
                'can_sell',
                'is_pos_enabled',
                'low_stock_alert',
                'branch_sku',
            ])
            ->withTimestamps();
    }

    /**
     * Branch-specific product prices and overrides.
     */
    public function productPrices(): HasMany
    {
        return $this->hasMany(BranchProductPrice::class, 'branch_id');
    }

    /**
     * Branch inventory stocks (including variant-wise stock).
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class, 'branch_id');
    }

    /**
     * Orders placed or fulfilled through this branch.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'branch_id');
    }

    /**
     * Stock movements / Ledger entries tied to this branch.
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'branch_id');
    }

    /**
     * Stock transfers where this branch is the source.
     */
    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'from_branch_id');
    }

    /**
     * Stock transfers where this branch is the destination.
     */
    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'to_branch_id');
    }

    /**
     * Daily cash register closing records.
     */
    public function dailyClosings(): HasMany
    {
        return $this->hasMany(BranchDailyClosing::class, 'branch_id');
    }

    /**
     * Key-value configuration settings for this branch.
     */
    public function branchSettings(): HasMany
    {
        return $this->hasMany(BranchSetting::class, 'branch_id');
    }

    // ==========================================
    // 3. QUERY SCOPES
    // ==========================================

    /**
     * Scope only active and commercially operational branches.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('status')
                  ->orWhere('status', 'active');
            });
    }

    /**
     * Scope by branch operational type.
     */
    public function scopeOfType($query, string $type)
    {
        $t = strtolower($type);
        return $query->where(function ($q) use ($t) {
            $q->where('type', $t)
              ->orWhere('branch_type', $t);
        });
    }

    // ==========================================
    // 4. OPERATIONAL HELPER METHODS
    // ==========================================

    /**
     * Check if this branch is commercially operational.
     */
    public function isOperational(): bool
    {
        return (bool) $this->is_active && ($this->status === 'active' || is_null($this->status));
    }

    /**
     * Check if this branch is designated as Main / Head Office.
     */
    public function isMainBranch(): bool
    {
        $t = strtolower($this->branch_type ?? ($this->type ?? ''));
        return in_array($t, ['main', 'head_office', 'main_branch']);
    }

    /**
     * Retrieve a specific operational setting value with a default fallback.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        if (is_array($this->settings) && array_key_exists($key, $this->settings)) {
            return $this->settings[$key];
        }

        if ($this->relationLoaded('branchSettings')) {
            $record = $this->branchSettings->firstWhere('key', $key);
            if ($record) {
                return $record->value;
            }
        }

        return $default;
    }

    /**
     * Check if POS is enabled for this branch.
     */
    public function isPosEnabled(): bool
    {
        if (!$this->isOperational()) {
            return false;
        }

        return (bool) $this->getSetting('pos_enabled', true);
    }

    /**
     * Get catalog access mode for this branch (all_catalog vs assigned_only).
     */
    public function getCatalogAccessMode(): string
    {
        if (!empty($this->catalog_access_mode)) {
            return strtolower(trim((string)$this->catalog_access_mode));
        }

        $fromSetting = $this->getSetting('catalog_access_mode');
        if (!empty($fromSetting)) {
            return strtolower(trim((string)$fromSetting));
        }

        return 'all_catalog';
    }

    /**
     * Determine if this branch strictly allows only assigned catalog products in POS/Inventory.
     */
    public function isAssignedCatalogOnly(): bool
    {
        return $this->getCatalogAccessMode() === 'assigned_only';
    }
}