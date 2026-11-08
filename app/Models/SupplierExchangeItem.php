<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierExchangeItem extends Model
{
    use HasFactory;

    protected $table = 'supplier_exchange_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'supplier_exchange_id',
        'item_type',
        'product_id',
        'variant_id',
        'sku',
        'barcode',
        'batch_no',
        'expiry_date',
        'quantity',
        'unit_cost',
        'total_cost',
        'total',
        'reason',
        'remarks',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expiry_date' => 'date',
        'quantity'    => 'decimal:2',
        'unit_cost'   => 'decimal:2',
        'total_cost'  => 'decimal:2',
        'total'       => 'decimal:2',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /* =========================================================================
     | RELATIONSHIPS
     ========================================================================= */

    public function exchange(): BelongsTo
    {
        return $this->belongsTo(SupplierExchange::class, 'supplier_exchange_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /* =========================================================================
     | SCOPES
     ========================================================================= */

    public function scopeOutward($query)
    {
        return $query->whereIn('item_type', ['outward', 'returned_out']);
    }

    public function scopeInward($query)
    {
        return $query->whereIn('item_type', ['inward', 'replacement_in']);
    }

    public function scopeReturnedOut($query)
    {
        return $this->scopeOutward($query);
    }

    public function scopeReplacementIn($query)
    {
        return $this->scopeInward($query);
    }
}