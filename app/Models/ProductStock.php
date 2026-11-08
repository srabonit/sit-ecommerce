<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStock extends Model
{
    use HasFactory;

    protected $table = 'product_stocks';

    protected $fillable = [
        'product_id',
        'variant_id',
        'sku',
        'branch_id',
        'warehouse_id',
        'quantity',
        'reserved_quantity',
        'alert_quantity',
    ];

    protected $casts = [
        'product_id'        => 'integer',
        'variant_id'        => 'integer',
        'branch_id'         => 'integer',
        'warehouse_id'      => 'integer',
        'quantity'          => 'integer',
        'reserved_quantity' => 'integer',
        'alert_quantity'    => 'integer',
    ];

    protected $appends = [
        'available_quantity',
    ];

    /**
     * The "booted" method of the model.
     * Preserves existing global BranchScope.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new BranchScope);
    }

    // =========================================================================
    // 1. EXISTING ORIGINAL RELATIONSHIPS (PRESERVED 100%)
    // =========================================================================

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    // =========================================================================
    // 2. ADVANCED MULTI-BRANCH VARIANT RELATIONSHIPS
    // =========================================================================

    /**
     * Specific product variant (null for simple products).
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    // =========================================================================
    // 3. ACCESSORS & BUSINESS LOGIC
    // =========================================================================

    /**
     * Calculated available stock = current stock - reserved/committed stock.
     */
    public function getAvailableQuantityAttribute(): int
    {
        $avail = (int) $this->quantity - (int) ($this->reserved_quantity ?? 0);
        return max(0, $avail);
    }

    /**
     * Check if available stock has reached or fallen below the alert threshold.
     */
    public function isLowStock(): bool
    {
        $threshold = (int) ($this->alert_quantity ?? 5);
        return $this->available_quantity <= $threshold;
    }

    // =========================================================================
    // 4. QUERY SCOPES
    // =========================================================================

    /**
     * Scope query for a specific variant (or simple product when null).
     */
    public function scopeForVariant($query, ?int $variantId = null)
    {
        if ($variantId) {
            return $query->where('variant_id', $variantId);
        }

        return $query->whereNull('variant_id');
    }

    /**
     * Scope query for items that have low stock.
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('(`quantity` - COALESCE(`reserved_quantity`, 0)) <= COALESCE(`alert_quantity`, 5)');
    }
}