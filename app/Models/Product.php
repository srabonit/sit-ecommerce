<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Schema;
use App\Services\ImagePathService;
use App\Services\BranchProductService;

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
        // Multi-Branch Access & Availability Mode
        'branch_availability_mode', // all, selected, main_only, head_office_only, restricted, branch_only, pos_only
        // Online Storefront Sellable Flag
        'is_sellable_online',
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
        'is_sellable_online'           => 'boolean',
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
        'is_sellable_online',
    ];

    // =========================================================================
    // 1. EXISTING ORIGINAL RELATIONSHIPS (PRESERVED 100%)
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
    // 2. ADVANCED MULTI-BRANCH RELATIONSHIPS
    // =========================================================================

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_product', 'product_id', 'branch_id')
            ->withPivot([
                'is_active',
                'can_sell',
                'is_pos_enabled',
                'low_stock_alert',
                'branch_sku',
            ])
            ->withTimestamps();
    }

    public function branchPrices(): HasMany
    {
        return $this->hasMany(BranchProductPrice::class, 'product_id');
    }

    public function branchStocks(): HasMany
    {
        return $this->hasMany(ProductStock::class, 'product_id');
    }

    // =========================================================================
    // 3. MULTI-BRANCH AVAILABILITY & QUERY SCOPES
    // =========================================================================

    /**
     * Schema-safe active scope (fixes Unknown column products.is_active)
     */
    public function scopeActive($query)
    {
        if (Schema::hasColumn('products', 'is_active')) {
            return $query->where($this->getTable() . '.is_active', true);
        }

        if (Schema::hasColumn('products', 'status')) {
            return $query->where(function ($q) {
                $table = $this->getTable();
                $q->where("{$table}.status", 'active')
                  ->orWhere("{$table}.status", 1)
                  ->orWhere("{$table}.status", '1');
            });
        }

        return $query;
    }

    /**
     * Safe is_active attribute resolver
     */
    public function getIsActiveAttribute(): bool
    {
        if (array_key_exists('is_active', $this->attributes) && !is_null($this->attributes['is_active'])) {
            return (bool) $this->attributes['is_active'];
        }

        if (array_key_exists('status', $this->attributes) && !is_null($this->attributes['status'])) {
            $status = strtolower((string) $this->attributes['status']);
            return in_array($status, ['active', '1', 'true', 'published'], true) || $this->attributes['status'] === 1 || $this->attributes['status'] === true;
        }

        return true;
    }

    /**
     * Safe is_sellable_online attribute resolver
     */
    public function getIsSellableOnlineAttribute(): bool
    {
        if (array_key_exists('is_sellable_online', $this->attributes) && !is_null($this->attributes['is_sellable_online'])) {
            return (bool) $this->attributes['is_sellable_online'];
        }

        $mode = strtolower(trim((string)($this->branch_availability_mode ?? 'all')));
        if (in_array($mode, ['pos_only', 'branch_only'])) {
            return false;
        }

        return true;
    }

    /**
     * Scope for products displayed on central public website / online store.
     */
    public function scopeOnlineSellable($query)
    {
        $this->scopeActive($query);

        if (Schema::hasColumn('products', 'is_sellable_online')) {
            $query->where($this->getTable() . '.is_sellable_online', true);
        }

        if (Schema::hasColumn('products', 'branch_availability_mode')) {
            $query->where(function ($bQ) {
                $table = $this->getTable();
                $bQ->whereNull("{$table}.branch_availability_mode")
                   ->orWhereNotIn("{$table}.branch_availability_mode", ['pos_only', 'branch_only']);
            });
        }

        return $query;
    }

    public function scopeAvailableForBranch($query, int $branchId)
    {
        $branch = Branch::find($branchId);
        $isMain = $branch ? $branch->isMainBranch() : false;
        $isStrict = $branch && class_exists(BranchProductService::class) 
            ? BranchProductService::isBranchStrictCatalogOnly($branch) 
            : false;

        if ($isStrict) {
            return $query->active()
                ->whereHas('branches', function ($bq) use ($branchId) {
                    $bq->where('branches.id', $branchId)
                       ->where('branch_product.is_active', true)
                       ->where('branch_product.can_sell', true);
                });
        }

        return $query->active()
            ->where(function ($q) use ($branchId, $isMain) {
                // Mode A: Available in all branches
                $q->where(function ($sq) {
                    $sq->whereNull('branch_availability_mode')
                       ->orWhere('branch_availability_mode', 'all');
                });

                // Mode B (Selected), Mode E (Restricted), branch_only, pos_only
                $q->orWhere(function ($sq) use ($branchId) {
                    $sq->whereIn('branch_availability_mode', ['selected', 'restricted', 'branch_only', 'pos_only'])
                       ->whereHas('branches', function ($bq) use ($branchId) {
                           $bq->where('branches.id', $branchId)
                              ->where('branch_product.is_active', true)
                              ->where('branch_product.can_sell', true);
                       });
                });

                // Mode C & D: Main / Head Office Branch Only
                if ($isMain) {
                    $q->orWhereIn('branch_availability_mode', ['main_only', 'head_office_only']);
                }
            });
    }

    public function isAvailableInBranch(int $branchId): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $branch = Branch::find($branchId);
        if ($branch && class_exists(BranchProductService::class) && BranchProductService::isBranchStrictCatalogOnly($branch)) {
            if ($this->relationLoaded('branches')) {
                $assignment = $this->branches->firstWhere('id', $branchId);
                return $assignment && $assignment->pivot->is_active && $assignment->pivot->can_sell;
            }

            return $this->branches()
                ->where('branches.id', $branchId)
                ->where('branch_product.is_active', true)
                ->where('branch_product.can_sell', true)
                ->exists();
        }

        $mode = strtolower(trim((string)($this->branch_availability_mode ?? 'all')));

        if ($mode === 'all') {
            return true;
        }

        if (in_array($mode, ['main_only', 'head_office_only'])) {
            return $branch ? $branch->isMainBranch() : false;
        }

        if (in_array($mode, ['selected', 'restricted', 'branch_only', 'pos_only'])) {
            if ($this->relationLoaded('branches')) {
                $assignment = $this->branches->firstWhere('id', $branchId);
                return $assignment && $assignment->pivot->is_active && $assignment->pivot->can_sell;
            }

            return $this->branches()
                ->where('branches.id', $branchId)
                ->where('branch_product.is_active', true)
                ->where('branch_product.can_sell', true)
                ->exists();
        }

        return false;
    }

    public function getBranchStock(int $branchId, ?int $variantId = null): int
    {
        $query = $this->stocks()->where('branch_id', $branchId);

        if ($variantId) {
            $query->where('variant_id', $variantId);
        } else {
            $query->whereNull('variant_id');
        }

        $stockRecord = $query->first();

        if ($stockRecord) {
            $avail = (int) $stockRecord->quantity - (int) ($stockRecord->reserved_quantity ?? 0);
            return max(0, $avail);
        }

        // Fallback: If no separate branch stock entry exists yet, return base catalog stock
        $branch = Branch::find($branchId);
        if ($branch && ($branch->isMainBranch() || !$this->stocks()->exists())) {
            if ($variantId) {
                $variant = $this->variants()->find($variantId);
                return max(0, (int)($variant->stock ?? 0));
            }
            return max(0, (int)($this->stock ?? ($this->total_stock ?? 0)));
        }

        return max(0, (int)($this->stock ?? 0));
    }

    public function getBranchPrice(int $branchId, ?int $variantId = null): float
    {
        $priceOverride = $this->branchPrices()
            ->where('branch_id', $branchId)
            ->where('variant_id', $variantId)
            ->first();

        if ($priceOverride) {
            if (!empty($priceOverride->sale_price) && (float) $priceOverride->sale_price > 0) {
                return (float) $priceOverride->sale_price;
            }
            if (!empty($priceOverride->price) && (float) $priceOverride->price > 0) {
                return (float) $priceOverride->price;
            }
        }

        if ($variantId) {
            $variant = $this->variants()->find($variantId);
            if ($variant && !empty($variant->price) && (float) $variant->price > 0) {
                return (float) $variant->price;
            }
        }

        return $this->effective_price;
    }

    // =========================================================================
    // 4. ACCESSORS & INVENTORY LOGIC (PRESERVED 100%)
    // =========================================================================

    public function getIsVariableAttribute(): bool
    {
        if ($this->relationLoaded('variants')) {
            return $this->variants->isNotEmpty();
        }

        return $this->variants()->exists();
    }

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

    public function getEffectivePriceAttribute(): float
    {
        if (!empty($this->sale_price) && (float) $this->sale_price > 0) {
            return (float) $this->sale_price;
        }

        return (float) ($this->price ?? ($this->regular_price ?? 0.00));
    }

    public function getEffectivePurchaseCostAttribute(): float
    {
        if (!is_null($this->purchase_cost) && (float) $this->purchase_cost > 0) {
            return (float) $this->purchase_cost;
        }

        return (float) ($this->purchase_price ?? 0.00);
    }

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

    public function getIsInStockAttribute(): bool
    {
        return $this->total_stock > 0;
    }

    public function getIsLowStockAttribute(): bool
    {
        $stock = $this->total_stock;
        $threshold = (int) ($this->alert_quantity ?? 5);

        return $stock > 0 && $stock <= $threshold;
    }

    public function getThumbnailUrlAttribute(): string
    {
        return ImagePathService::resolveUrl($this->thumbnail, 'https://placehold.co/600x600?text=No+Image');
    }
}