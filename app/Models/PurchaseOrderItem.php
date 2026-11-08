<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

class PurchaseOrderItem extends Model
{
    use HasFactory, Auditable;

    protected $table = 'purchase_order_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'variant_id',
        'sku',
        'barcode',
        'quantity',
        'unit_cost',
        'discount_amount',
        'tax_amount',
        'total_cost',
        'received_quantity',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity'          => 'decimal:2',
        'unit_cost'         => 'decimal:2',
        'discount_amount'   => 'decimal:2',
        'tax_amount'        => 'decimal:2',
        'total_cost'        => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    /* =========================================================================
     | RELATIONSHIPS
     ========================================================================= */

    /**
     * Parent Purchase Order
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
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
     * Billed purchase line items referencing this order line
     */
    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_order_item_id');
    }

    /**
     * Goods receiving lines associated with this order line
     */
    public function receiptItems(): HasMany
    {
        return $this->hasMany(PurchaseReceiptItem::class, 'purchase_order_item_id');
    }

    /* =========================================================================
     | QUANTITY & RECEIVING HELPERS
     ========================================================================= */

    /**
     * Calculate unfulfilled / remaining quantity
     */
    public function getRemainingQuantityAttribute(): float
    {
        return max(0.0, round((float) $this->quantity - (float) $this->received_quantity, 2));
    }

    /**
     * Check if line item is completely fulfilled
     */
    public function getIsFullyReceivedAttribute(): bool
    {
        return (float) $this->received_quantity >= (float) $this->quantity;
    }
}