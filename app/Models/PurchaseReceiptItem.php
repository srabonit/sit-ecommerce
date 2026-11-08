<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReceiptItem extends Model
{
    use HasFactory;

    protected $table = 'purchase_receipt_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'purchase_receipt_id',
        'purchase_order_item_id',
        'purchase_item_id',
        'product_id',
        'variant_id',
        'sku',
        'barcode',
        'batch_no',
        'expiry_date',
        'ordered_quantity',
        'received_quantity',
        'delivered_quantity',
        'received_qty',
        'accepted_quantity',
        'rejected_quantity',
        'rejected_qty',
        'rejection_reason',
        'unit_cost',
        'total_cost',
        'remarks',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expiry_date'        => 'date',
        'ordered_quantity'   => 'decimal:2',
        'received_quantity'  => 'decimal:2',
        'delivered_quantity' => 'decimal:2',
        'received_qty'       => 'decimal:2',
        'accepted_quantity'  => 'decimal:2',
        'rejected_quantity'  => 'decimal:2',
        'rejected_qty'       => 'decimal:2',
        'unit_cost'          => 'decimal:2',
        'total_cost'         => 'decimal:2',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
    ];

    /* =========================================================================
     | RELATIONSHIPS
     ========================================================================= */

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(PurchaseReceipt::class, 'purchase_receipt_id');
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }

    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class, 'purchase_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}