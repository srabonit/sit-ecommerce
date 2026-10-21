<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Services\ImagePathService;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'title',
        'name',
        'slug',
        'sku',
        'product_code',
        'short_description',
        'description',
        'regular_price',
        'sale_price',
        'price',
        'purchase_cost',
        'purchase_price',
        'stock',
        'alert_quantity',
        'category_id',
        'sub_category_id',
        'brand_id',
        'thumbnail',
        'video_url',
        'product_type',
        'is_virtual',
        'is_downloadable',
        'download_limit',
        'download_expiry_days',
        'customer_instructions',
        'access_instructions',
        'weight',
        'custom_delivery_charge',
        'multiply_qty_delivery_charge',
        'is_featured',
        'is_active',
        'status',
    ];

    protected $casts = [
        'regular_price'                => 'decimal:2',
        'sale_price'                   => 'decimal:2',
        'price'                        => 'decimal:2',
        'purchase_cost'                => 'decimal:2',
        'purchase_price'               => 'decimal:2',
        'stock'                        => 'integer',
        'alert_quantity'               => 'integer',
        'is_virtual'                   => 'boolean',
        'is_downloadable'              => 'boolean',
        'is_featured'                  => 'boolean',
        'is_active'                    => 'boolean',
        'multiply_qty_delivery_charge' => 'boolean',
        'custom_delivery_charge'       => 'decimal:2',
        'weight'                       => 'decimal:3',
    ];

    protected $appends = [
        'total_stock',
        'is_variable',
        'effective_price',
        'effective_purchase_cost',
        'stock_purchase_value',
        'is_in_stock',
        'is_low_stock',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_category');
    }

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'product_brand');
    }

    /**
     * Product Tags Relationship
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(ProductGallery::class, 'product_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(ProductDownload::class, 'product_id');
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(ProductLicense::class, 'product_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class, 'product_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'product_id')->orderBy('id', 'desc');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class, 'product_id');
    }

    // =========================================================================
    // ACCESSORS & SINGLE SOURCE OF TRUTH INVENTORY LOGIC
    // =========================================================================

    /**
     * Check if product is a variable product (has variants).
     */
    public function getIsVariableAttribute(): bool
    {
        if ($this->relationLoaded('variants')) {
            return $this->variants->isNotEmpty();
        }

        return $this->variants()->exists();
    }

    /**
     * Single Source of Truth Stock Resolver:
     * - Variable Product: Sum of all variants' stock.
     * - Simple Product: Product base stock.
     */
    public function getTotalStockAttribute(): int
    {
        if ($this->is_variable) {
            if ($this->relationLoaded('variants')) {
                return (int) $this->variants->sum('stock');
            }
            return (int) $this->variants()->sum('stock');
        }

        return max(0, (int) ($this->stock ?? 0));
    }

    /**
     * Effective selling price.
     */
    public function getEffectivePriceAttribute(): float
    {
        if (!empty($this->sale_price) && (float) $this->sale_price > 0) {
            return (float) $this->sale_price;
        }

        return (float) ($this->price ?? ($this->regular_price ?? 0.00));
    }

    /**
     * Safe base purchase cost.
     */
    public function getEffectivePurchaseCostAttribute(): float
    {
        if (!is_null($this->purchase_cost) && (float) $this->purchase_cost > 0) {
            return (float) $this->purchase_cost;
        }

        return (float) ($this->purchase_price ?? 0.00);
    }

    /**
     * Total Inventory Purchase Value:
     * - Variable Product: Sum of each variant's (stock * purchase_cost).
     * - Simple Product: product.stock * product.effective_purchase_cost.
     */
    public function getStockPurchaseValueAttribute(): float
    {
        if ($this->is_variable) {
            $variantList = $this->relationLoaded('variants') ? $this->variants : $this->variants()->get();
            $val = 0.00;
            foreach ($variantList as $v) {
                $val += $v->stock_purchase_value;
            }
            return round($val, 2);
        }

        return round($this->total_stock * $this->effective_purchase_cost, 2);
    }

    /**
     * Real-time stock status.
     */
    public function getIsInStockAttribute(): bool
    {
        return $this->total_stock > 0;
    }

    /**
     * Low stock threshold checker.
     */
    public function getIsLowStockAttribute(): bool
    {
        $stock = $this->total_stock;
        $threshold = (int) ($this->alert_quantity ?? 5);

        return $stock > 0 && $stock <= $threshold;
    }

    /**
     * Safe Thumbnail URL Resolver using ImagePathService.
     */
    public function getThumbnailUrlAttribute(): string
    {
        return ImagePathService::resolveUrl($this->thumbnail, 'https://placehold.co/600x600?text=No+Image');
    }
}