<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\ImagePathService;

class ProductVariant extends Model
{
    use HasFactory;

    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'name',
        'title',
        'sku',
        'product_code',
        'color',
        'size',
        'regular_price',
        'sale_price',
        'purchase_cost',
        'purchase_price',
        'stock',
        'alert_quantity',
        'image',
        'is_active',
    ];

    protected $casts = [
        'regular_price'  => 'decimal:2',
        'sale_price'     => 'decimal:2',
        'purchase_cost'  => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'stock'          => 'integer',
        'alert_quantity' => 'integer',
        'is_active'      => 'boolean',
    ];

    protected $appends = [
        'variant_title',
        'effective_price',
        'effective_purchase_cost',
        'stock_purchase_value',
        'profit_per_unit',
        'is_in_stock',
        'is_low_stock',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Parent product of this variant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Order items sold under this variant.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'variant_id');
    }

    /**
     * Audit log of all stock movements for this variant.
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'variant_id')->orderBy('id', 'desc');
    }

    // =========================================================================
    // ACCESSORS & BUSINESS LOGIC HELPERS
    // =========================================================================

    /**
     * Effective selling price (sale price if exists, otherwise regular price).
     */
    public function getEffectivePriceAttribute(): float
    {
        if (!empty($this->sale_price) && (float) $this->sale_price > 0) {
            return (float) $this->sale_price;
        }

        return (float) ($this->regular_price ?? 0.00);
    }

    /**
     * Safe purchase cost resolver (falls back to legacy purchase_price or parent cost).
     */
    public function getEffectivePurchaseCostAttribute(): float
    {
        if (!is_null($this->purchase_cost) && (float) $this->purchase_cost > 0) {
            return (float) $this->purchase_cost;
        }

        if (!is_null($this->purchase_price) && (float) $this->purchase_price > 0) {
            return (float) $this->purchase_price;
        }

        // Fallback to parent product purchase cost
        if ($this->relationLoaded('product') && $this->product) {
            return (float) ($this->product->purchase_cost ?? ($this->product->purchase_price ?? 0.00));
        }

        return 0.00;
    }

    /**
     * Rule 2: Stock Purchase Value = Current Stock × Purchase Cost per Unit
     */
    public function getStockPurchaseValueAttribute(): float
    {
        $currentStock = max(0, (int) ($this->stock ?? 0));
        return round($currentStock * $this->effective_purchase_cost, 2);
    }

    /**
     * Rule 2: Profit per Unit = Selling Price − Purchase Cost per Unit
     */
    public function getProfitPerUnitAttribute(): float
    {
        return round($this->effective_price - $this->effective_purchase_cost, 2);
    }

    /**
     * Check if variant has available stock (> 0).
     */
    public function getIsInStockAttribute(): bool
    {
        return (int) ($this->stock ?? 0) > 0;
    }

    /**
     * Check if variant stock has hit the low stock alert threshold.
     */
    public function getIsLowStockAttribute(): bool
    {
        $stock = (int) ($this->stock ?? 0);
        $threshold = (int) ($this->alert_quantity ?? 5);

        return $stock > 0 && $stock <= $threshold;
    }

    /**
     * Human readable variant title (, "Red / XL" or custom title).
     */
    public function getVariantTitleAttribute(): string
    {
        $parts = array_filter([
            trim((string) ($this->color ?? '')),
            trim((string) ($this->size ?? ''))
        ]);

        if (count($parts) > 0) {
            return implode(' / ', $parts);
        }

        return $this->name ?? ($this->title ?? ('Variant #' . $this->id));
    }

    /**
     * Safe Image URL Resolver using central ImagePathService.
     */
    public function getImageUrlAttribute(): string
    {
        return ImagePathService::resolveUrl($this->image, '');
    }
}