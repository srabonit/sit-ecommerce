<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchProductPrice extends Model
{
    use HasFactory;

    protected $table = 'branch_product_prices';

    protected $fillable = [
        'branch_id',
        'product_id',
        'variant_id',
        'price',
        'sale_price',
        'wholesale_price',
        'min_selling_price',
        'discount',
        'discount_type',
    ];

    protected $casts = [
        'branch_id'         => 'integer',
        'product_id'        => 'integer',
        'variant_id'        => 'integer',
        'price'             => 'decimal:2',
        'sale_price'        => 'decimal:2',
        'wholesale_price'   => 'decimal:2',
        'min_selling_price' => 'decimal:2',
        'discount'          => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
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