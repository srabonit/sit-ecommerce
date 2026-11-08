<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

class PurchaseItem extends Model
{
    use HasFactory, Auditable;

    protected $table = 'purchase_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'purchase_id',
        'purchase_order_item_id',
        'product_id',
        'variant_id',
        'sku',
        'barcode',
        'batch_no',
        'expiry_date',
        'quantity',
        'unit_cost',
        'unit_cost_snapshot',
        'discount_amount',
        'tax_amount',
        'subtotal',
        'total_cost',
        'landed_cost_share',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expiry_date'        => 'date',
        'quantity'           => 'decimal:2',
        'unit_cost'          => 'decimal:2',
        'unit_cost_snapshot' => 'decimal:2',
        'discount_amount'    => 'decimal:2',
        'tax_amount'         => 'decimal:2',
        'subtotal'           => 'decimal:2',
        'total_cost'         => 'decimal:2',
        'landed_cost_share'  => 'decimal:2',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
    ];

    /* =========================================================================
     | RELATIONSHIPS
     ========================================================================= */

    /**
     * Parent Purchase Invoice / Bill
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    /**
     * Originating Purchase Order line item (if converted from PO)
     */
    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }

    /**
     * Targeted Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Targeted Product Variant (if any)
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Goods receiving lines associated with this purchase line
     */
    public function receiptItems(): HasMany
    {
        return $this->hasMany(PurchaseReceiptItem::class, 'purchase_item_id');
    }

    /**
     * Return items linked to this purchase line
     */
    public function returnItems(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class, 'purchase_item_id');
    }

    /**
     * Stock movements generated from this purchase line
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'purchase_item_id');
    }

    /* =========================================================================
     | COST & VALUATION HELPERS
     ========================================================================= */

    /**
     * Calculate effective landed unit cost including allocated freight/shipping charges
     */
    public function getEffectiveUnitCostAttribute(): float
    {
        $qty = (float) $this->quantity;
        if ($qty <= 0) {
            return (float) $this->unit_cost;
        }

        $totalWithLanded = (float) $this->total_cost + (float) $this->landed_cost_share;
        return round($totalWithLanded / $qty, 4);
    }
}