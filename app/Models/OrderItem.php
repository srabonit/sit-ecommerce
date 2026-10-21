<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'product_title',
        'product_name',
        'name',
        'title',
        'variant_title',
        'color',
        'size',
        'sku',
        'quantity',
        'qty',
        'unit_price',
        'price',
        'product_price',
        'subtotal',
        'total',
        'purchase_cost',
        'cogs',
        'is_cost_estimated',
    ];

    protected $casts = [
        'unit_price'         => 'decimal:2',
        'price'              => 'decimal:2',
        'product_price'      => 'decimal:2',
        'subtotal'           => 'decimal:2',
        'total'              => 'decimal:2',
        'purchase_cost'      => 'decimal:2',
        'cogs'               => 'decimal:2',
        'quantity'           => 'integer',
        'qty'                => 'integer',
        'is_cost_estimated'  => 'boolean',
    ];

    protected $appends = [
        'effective_quantity',
        'effective_unit_price',
        'effective_subtotal',
        'effective_purchase_cost',
        'effective_cogs',
        'gross_profit',
        'profit_margin_percent',
        'full_item_title',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Parent Order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Purchased Product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Purchased Variant (if variable product).
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    // =========================================================================
    // ACCESSORS & BUSINESS LOGIC
    // =========================================================================

    /**
     * Effective Quantity resolver.
     */
    public function getEffectiveQuantityAttribute(): int
    {
        return (int) ($this->quantity ?? ($this->qty ?? 1));
    }

    /**
     * Effective Selling Price per unit.
     */
    public function getEffectiveUnitPriceAttribute(): float
    {
        return (float) ($this->unit_price ?? ($this->price ?? ($this->product_price ?? 0.00)));
    }

    /**
     * Effective Subtotal (Selling Price × Quantity).
     */
    public function getEffectiveSubtotalAttribute(): float
    {
        if (!empty($this->subtotal) && (float) $this->subtotal > 0) {
            return (float) $this->subtotal;
        }

        if (!empty($this->total) && (float) $this->total > 0) {
            return (float) $this->total;
        }

        return round($this->effective_unit_price * $this->effective_quantity, 2);
    }

    /**
     * Snapshot purchase cost per unit.
     */
    public function getEffectivePurchaseCostAttribute(): float
    {
        return (float) ($this->purchase_cost ?? 0.00);
    }

    /**
     * Total Cost of Goods Sold (COGS = purchase_cost × quantity).
     */
    public function getEffectiveCogsAttribute(): float
    {
        if (!empty($this->cogs) && (float) $this->cogs > 0) {
            return (float) $this->cogs;
        }

        return round($this->effective_purchase_cost * $this->effective_quantity, 2);
    }

    /**
     * Gross Profit = Revenue − COGS.
     */
    public function getGrossProfitAttribute(): float
    {
        return round($this->effective_subtotal - $this->effective_cogs, 2);
    }

    /**
     * Profit Margin Percentage.
     */
    public function getProfitMarginPercentAttribute(): float
    {
        if ($this->effective_subtotal <= 0) {
            return 0.00;
        }

        return round(($this->gross_profit / $this->effective_subtotal) * 100, 2);
    }

    /**
     * Full item title with variant attributes.
     */
    public function getFullItemTitleAttribute(): string
    {
        $base = $this->product_title ?? ($this->product_name ?? ($this->title ?? 'Product'));
        $variant = $this->variant_title;

        if (empty($variant)) {
            $parts = array_filter([$this->color, $this->size]);
            if (count($parts) > 0) {
                $variant = implode(' / ', $parts);
            }
        }

        return !empty($variant) ? "{$base} ({$variant})" : $base;
    }
}