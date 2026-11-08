<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    use HasFactory;

    protected $table = 'assets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Identification & Classification
        'asset_code',
        'name',
        'asset_category_id',
        'category_id',
        'brand',
        'brand_name',
        'model_no',
        'model_number',
        'serial_number',

        // Procurement & Supplier Integration
        'supplier_id',
        'purchase_id',
        'purchase_date',
        'in_service_date',
        'purchase_cost',
        'installation_cost',
        'shipping_cost',
        'salvage_value',
        'capitalized_cost',
        'current_value',

        // Location & Assignment
        'branch_id',
        'department_id',
        'department',
        'custodian_id',
        'assigned_user_id',
        'location',
        'physical_location',

        // Condition & Lifecycle Status
        'condition',
        'status',
        'disposal_type',
        'disposal_value',
        'disposal_date',

        // Valuation & Depreciation
        'depreciation_method',
        'useful_life_years',
        'depreciation_rate',
        'accumulated_depreciation',
        'asset_account_id',
        'accumulated_depreciation_account_id',
        'depreciation_expense_account_id',

        // Warranty & Documentation
        'warranty_expiry_date',
        'insurance_policy_no',
        'attachment_file',
        'warranty_document',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'purchase_date'            => 'date',
        'in_service_date'          => 'date',
        'warranty_expiry_date'     => 'date',
        'disposal_date'            => 'date',
        'purchase_cost'            => 'decimal:2',
        'installation_cost'        => 'decimal:2',
        'shipping_cost'            => 'decimal:2',
        'salvage_value'            => 'decimal:2',
        'capitalized_cost'         => 'decimal:2',
        'current_value'            => 'decimal:2',
        'useful_life_years'        => 'decimal:2',
        'depreciation_rate'        => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'disposal_value'           => 'decimal:2',
        'created_at'               => 'datetime',
        'updated_at'               => 'datetime',
    ];

    /* =========================================================================
     | RELATIONSHIPS
     ========================================================================= */

    /**
     * Asset Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    /**
     * Supplier who provided the asset
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Purchase Bill / Invoice through which this asset was procured
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    /**
     * Branch where this asset is deployed
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Department
     */
    public function departmentRel(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * User/Employee currently custodian of this asset
     */
    public function custodian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'custodian_id');
    }

    /**
     * Backward-compatible alias for assigned user
     */
    public function assignedUser(): BelongsTo
    {
        return $this->custodian();
    }

    /* =========================================================================
     | SCOPES & HELPERS
     ========================================================================= */

    public function scopeInUse($query)
    {
        return $query->whereIn('status', ['active', 'in_use']);
    }

    public function scopeUnderWarranty($query)
    {
        return $query->whereNotNull('warranty_expiry_date')
            ->where('warranty_expiry_date', '>=', now()->toDateString());
    }

    public function scopeSearch($query, $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhere('asset_code', 'LIKE', "%{$term}%")
              ->orWhere('serial_number', 'LIKE', "%{$term}%")
              ->orWhere('model_no', 'LIKE', "%{$term}%")
              ->orWhere('brand', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Check if asset is still covered by warranty
     */
    public function isUnderWarranty(): bool
    {
        return $this->warranty_expiry_date !== null && $this->warranty_expiry_date->isFuture();
    }

    /**
     * Calculate Net Book Value (Capitalized Cost - Accumulated Depreciation)
     */
    public function getNetBookValueAttribute(): float
    {
        $base = (float) ($this->capitalized_cost > 0 ? $this->capitalized_cost : $this->purchase_cost);
        return max((float) ($this->salvage_value ?? 0), round($base - (float) $this->accumulated_depreciation, 2));
    }
}