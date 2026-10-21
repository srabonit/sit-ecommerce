<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $table = 'inventory_movements';

    protected $fillable = [
        'product_id',
        'variant_id',
        'sku',
        'movement_type',
        'quantity_before',
        'quantity_change',
        'quantity_after',
        'unit_cost',
        'unit_price',
        'reference_id',
        'source',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'quantity_before' => 'integer',
        'quantity_change' => 'integer',
        'quantity_after'  => 'integer',
        'unit_cost'       => 'decimal:2',
        'unit_price'      => 'decimal:2',
    ];

    protected $appends = [
        'is_deduction',
        'is_addition',
        'total_cost_impact',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Associated Parent Product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Associated Product Variant (if applicable).
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Admin/Staff who initiated this stock movement.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * Check if this movement reduced stock ( sale, damaged stock).
     */
    public function getIsDeductionAttribute(): bool
    {
        return (int) $this->quantity_change < 0;
    }

    /**
     * Check if this movement increased stock ( purchase, return).
     */
    public function getIsAdditionAttribute(): bool
    {
        return (int) $this->quantity_change > 0;
    }

    /**
     * Total cost valuation impact of this specific movement.
     */
    public function getTotalCostImpactAttribute(): float
    {
        return round(abs((int) $this->quantity_change) * (float) $this->unit_cost, 2);
    }

    // =========================================================================
    // QUERY SCOPES FOR REPORTS
    // =========================================================================

    /**
     * Scope to filter by specific product.
     */
    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope to filter by specific variant.
     */
    public function scopeForVariant($query, int $variantId)
    {
        return $query->where('variant_id', $variantId);
    }

    /**
     * Scope to filter sales movements (Checkout & POS).
     */
    public function scopeSalesOnly($query)
    {
        return $query->whereIn('movement_type', ['checkout_sale', 'pos_sale']);
    }

    /**
     * Scope to filter restock / purchase movements.
     */
    public function scopePurchasesOnly($query)
    {
        return $query->where('movement_type', 'purchase_receive');
    }
}