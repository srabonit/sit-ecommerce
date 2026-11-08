<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\BranchProductPrice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BranchProductService
{
    // =========================================================================
    // 1. PRODUCT AVAILABILITY & ACCESS POLICY EVALUATOR (5 MODES + STRICT BRANCH POLICY)
    // =========================================================================

    /**
     * Checks if a branch is strictly limited to explicitly assigned catalog products only.
     */
    public static function isBranchStrictCatalogOnly(Branch|int $branch): bool
    {
        $branchModel = $branch instanceof Branch ? $branch : Branch::find($branch);
        if (!$branchModel) {
            return false;
        }

        if (method_exists($branchModel, 'isAssignedCatalogOnly')) {
            return $branchModel->isAssignedCatalogOnly();
        }

        if (isset($branchModel->catalog_access_mode)) {
            return $branchModel->catalog_access_mode === 'assigned_only';
        }

        if (isset($branchModel->settings)) {
            $settings = is_array($branchModel->settings) 
                ? $branchModel->settings 
                : json_decode((string)$branchModel->settings, true);

            if (isset($settings['catalog_access_mode'])) {
                return $settings['catalog_access_mode'] === 'assigned_only';
            }
        }

        return false;
    }

    /**
     * Evaluates active pivot assignment in branch_product table.
     */
    protected static function hasActivePivotAssignment(Product $product, int $branchId, bool $isPosContext = false): bool
    {
        if ($product->relationLoaded('branches')) {
            $assignment = $product->branches->firstWhere('id', $branchId);
            if (!$assignment) {
                return false;
            }
            $pivotActive = isset($assignment->pivot->is_active) ? (bool)$assignment->pivot->is_active : true;
            $pivotCanSell = isset($assignment->pivot->can_sell) ? (bool)$assignment->pivot->can_sell : true;
            if (!$pivotActive || !$pivotCanSell) {
                return false;
            }
            if ($isPosContext && isset($assignment->pivot->is_pos_enabled) && !$assignment->pivot->is_pos_enabled) {
                return false;
            }
            return true;
        }

        $query = DB::table('branch_product')
            ->where('branch_id', $branchId)
            ->where('product_id', $product->id);

        if (Schema::hasColumn('branch_product', 'is_active')) {
            $query->where('is_active', true);
        }
        if (Schema::hasColumn('branch_product', 'can_sell')) {
            $query->where('can_sell', true);
        }
        if ($isPosContext && Schema::hasColumn('branch_product', 'is_pos_enabled')) {
            $query->where('is_pos_enabled', true);
        }

        return $query->exists();
    }

    public static function isProductSellableInBranch(Product|int $product, int $branchId, bool $isPosContext = false): bool
    {
        $productModel = $product instanceof Product ? $product : Product::find($product);

        if (!$productModel) {
            return false;
        }

        // Schema-safe active check
        if (isset($productModel->is_active) && !$productModel->is_active) {
            return false;
        }
        if (isset($productModel->status) && in_array(strtolower((string)$productModel->status), ['inactive', 'draft', '0', 'disabled'])) {
            return false;
        }

        $branch = Branch::find($branchId);
        if (!$branch) {
            return false;
        }

        $isOperational = method_exists($branch, 'isOperational') 
            ? $branch->isOperational() 
            : (!isset($branch->is_active) || (bool)$branch->is_active);

        if (!$isOperational) {
            return false;
        }

        if ($isPosContext) {
            $isPos = method_exists($branch, 'isPosEnabled')
                ? $branch->isPosEnabled()
                : (!isset($branch->pos_enabled) || (bool)$branch->pos_enabled);

            if (!$isPos) {
                return false;
            }
        }

        // Strict Branch Policy: Branch can only sell products explicitly assigned to it
        if (self::isBranchStrictCatalogOnly($branch)) {
            return self::hasActivePivotAssignment($productModel, $branchId, $isPosContext);
        }

        $mode = strtolower(trim((string)($productModel->branch_availability_mode ?? 'all')));

        // Mode A: All active branches
        if ($mode === 'all') {
            return true;
        }

        // Mode C & D: Main / Head Office Only
        if (in_array($mode, ['main_only', 'head_office_only'])) {
            $isMain = method_exists($branch, 'isMainBranch')
                ? $branch->isMainBranch()
                : (strtolower((string)($branch->type ?? '')) === 'main');
            return $isMain;
        }

        // Mode B (Selected), Mode E (Restricted), branch_only, pos_only: Must have explicit active pivot assignment
        if (in_array($mode, ['selected', 'restricted', 'branch_only', 'pos_only'])) {
            return self::hasActivePivotAssignment($productModel, $branchId, $isPosContext);
        }

        return false;
    }

    public static function validateProductForSaleOrAbort(int $productId, int $branchId, bool $isPosContext = false): Product
    {
        $product = Product::find($productId);

        if (!$product) {
            abort(404, "Product #{$productId} not found.");
        }

        if (!self::isProductSellableInBranch($product, $branchId, $isPosContext)) {
            $branch = Branch::find($branchId);
            $branchName = $branch ? $branch->name : "#{$branchId}";
            abort(403, "Product '{$product->name}' is restricted or not authorized for sale in branch: {$branchName}.");
        }

        return $product;
    }

    // =========================================================================
    // 2. DYNAMIC BRANCH PRICING & DISCOUNTS RESOLVER (ZERO N+1 QUERIES)
    // =========================================================================

    public static function resolvePrice(Product|int $product, int $branchId, ?int $variantId = null): array
    {
        $productModel = $product instanceof Product ? $product : Product::find($product);

        if (!$productModel) {
            return [
                'regular_price'      => 0.00,
                'sale_price'         => null,
                'effective_price'    => 0.00,
                'wholesale_price'    => 0.00,
                'min_selling_price'  => 0.00,
                'is_branch_override' => false,
            ];
        }

        $override = null;

        // 1. In-Memory Resolution if relation 'branchPrices' is already loaded
        if ($productModel->relationLoaded('branchPrices')) {
            $prices = $productModel->branchPrices;
            if ($variantId) {
                $override = $prices->first(function ($p) use ($branchId, $variantId) {
                    return (int)$p->branch_id === $branchId && (int)$p->variant_id === $variantId;
                });
            }

            if (!$override) {
                $override = $prices->first(function ($p) use ($branchId) {
                    return (int)$p->branch_id === $branchId && is_null($p->variant_id);
                });
            }
        }

        // 2. Fallback to direct DB query if relation not loaded
        if (!$override && !$productModel->relationLoaded('branchPrices')) {
            if ($variantId) {
                $override = DB::table('branch_product_prices')
                    ->where('branch_id', $branchId)
                    ->where('product_id', $productModel->id)
                    ->where('variant_id', $variantId)
                    ->first();
            }

            if (!$override) {
                $override = DB::table('branch_product_prices')
                    ->where('branch_id', $branchId)
                    ->where('product_id', $productModel->id)
                    ->whereNull('variant_id')
                    ->first();
            }
        }

        if ($override) {
            $salePrice = (!is_null($override->sale_price) && (float)$override->sale_price > 0)
                ? (float)$override->sale_price
                : null;

            $regularPrice = (!is_null($override->price) && (float)$override->price > 0)
                ? (float)$override->price
                : (float)($productModel->regular_price ?? ($productModel->effective_price ?? 0));

            $effectivePrice = $salePrice ?? $regularPrice;

            return [
                'regular_price'      => round($regularPrice, 2),
                'sale_price'         => $salePrice ? round($salePrice, 2) : null,
                'effective_price'    => round($effectivePrice, 2),
                'wholesale_price'    => round((float)($override->wholesale_price ?? 0.00), 2),
                'min_selling_price'  => round((float)($override->min_selling_price ?? 0.00), 2),
                'discount'           => round((float)($override->discount ?? 0.00), 2),
                'discount_type'      => $override->discount_type ?? 'fixed',
                'is_branch_override' => true,
            ];
        }

        if ($variantId) {
            $variant = null;
            if ($productModel->relationLoaded('variants')) {
                $variant = $productModel->variants->firstWhere('id', $variantId);
            }
            if (!$variant) {
                $variant = ProductVariant::find($variantId);
            }

            if ($variant) {
                $vRegular = (float)($variant->regular_price ?? ($productModel->regular_price ?? 0));
                $vSale = (!empty($variant->sale_price) && (float)$variant->sale_price > 0) ? (float)$variant->sale_price : null;
                $vEffective = $vSale ?? $vRegular;

                return [
                    'regular_price'      => round($vRegular, 2),
                    'sale_price'         => $vSale ? round($vSale, 2) : null,
                    'effective_price'    => round($vEffective, 2),
                    'wholesale_price'    => 0.00,
                    'min_selling_price'  => 0.00,
                    'is_branch_override' => false,
                ];
            }
        }

        $pRegular = (float)($productModel->regular_price ?? ($productModel->price ?? 0));
        $pSale = (!empty($productModel->sale_price) && (float)$productModel->sale_price > 0) ? (float)$productModel->sale_price : null;
        $pEffective = $pSale ?? $pRegular;

        return [
            'regular_price'      => round($pRegular, 2),
            'sale_price'         => $pSale ? round($pSale, 2) : null,
            'effective_price'    => round($pEffective, 2),
            'wholesale_price'    => 0.00,
            'min_selling_price'  => 0.00,
            'is_branch_override' => false,
        ];
    }

    public static function setBranchPrice(int $branchId, int $productId, ?int $variantId, array $pricingData): void
    {
        DB::table('branch_product_prices')->updateOrInsert(
            [
                'branch_id'  => $branchId,
                'product_id' => $productId,
                'variant_id' => $variantId,
            ],
            [
                'price'             => !empty($pricingData['price']) ? (float)$pricingData['price'] : null,
                'sale_price'        => !empty($pricingData['sale_price']) ? (float)$pricingData['sale_price'] : null,
                'wholesale_price'   => !empty($pricingData['wholesale_price']) ? (float)$pricingData['wholesale_price'] : null,
                'min_selling_price' => !empty($pricingData['min_selling_price']) ? (float)$pricingData['min_selling_price'] : null,
                'discount'          => !empty($pricingData['discount']) ? (float)$pricingData['discount'] : null,
                'discount_type'     => $pricingData['discount_type'] ?? 'fixed',
                'updated_at'        => now(),
            ]
        );
    }

    public static function syncBranchAssignments(Product $product, array $branchIds, array $defaultPivotData = []): void
    {
        $syncData = [];

        foreach ($branchIds as $bId) {
            $branchId = (int) $bId;
            if ($branchId <= 0) {
                continue;
            }

            $syncData[$branchId] = array_merge([
                'is_active'       => true,
                'can_sell'        => true,
                'is_pos_enabled'  => true,
                'low_stock_alert' => $product->alert_quantity ?? 5,
            ], $defaultPivotData[$branchId] ?? []);
        }

        $product->branches()->sync($syncData);
    }

    // =========================================================================
    // 4. POS & CATALOG QUERY BUILDERS WITH STOCKS (IMAGE & N+1 OPTIMIZED)
    // =========================================================================

    public static function getBranchProductsQuery(int $branchId, bool $posOnly = true): Builder
    {
        $branch = Branch::find($branchId);
        $isMain = $branch 
            ? (method_exists($branch, 'isMainBranch') ? $branch->isMainBranch() : (strtolower((string)($branch->type ?? '')) === 'main'))
            : false;
        $isStrict = $branch ? self::isBranchStrictCatalogOnly($branch) : false;

        // Ensure products.* is selected so pivot joins never overwrite product columns like thumbnail
        $query = Product::query()->select('products.*');

        // Schema-safe active check
        if (Schema::hasColumn('products', 'is_active')) {
            $query->where('products.is_active', true);
        } elseif (Schema::hasColumn('products', 'status')) {
            $query->where(function ($sq) {
                $sq->where('products.status', 'active')
                   ->orWhere('products.status', 1)
                   ->orWhere('products.status', '1');
            });
        }

        // Strict Branch Catalog Policy: ONLY products explicitly assigned to this branch
        if ($isStrict) {
            $query->whereHas('branches', function ($bq) use ($branchId, $posOnly) {
                $bq->where('branches.id', $branchId);
                if (Schema::hasColumn('branch_product', 'is_active')) {
                    $bq->where('branch_product.is_active', true);
                }
                if (Schema::hasColumn('branch_product', 'can_sell')) {
                    $bq->where('branch_product.can_sell', true);
                }
                if ($posOnly && Schema::hasColumn('branch_product', 'is_pos_enabled')) {
                    $bq->where('branch_product.is_pos_enabled', true);
                }
            });
        } else {
            // Standard / Hybrid Policy: Central + Allowed Modes
            $query->where(function ($q) use ($branchId, $isMain, $posOnly) {
                // Mode A: All branches
                $q->where(function ($sq) {
                    $sq->whereNull('products.branch_availability_mode')
                       ->orWhere('products.branch_availability_mode', 'all');
                });

                // Mode B (Selected), Mode E (Restricted), branch_only, pos_only
                $q->orWhere(function ($sq) use ($branchId, $posOnly) {
                    $sq->whereIn('products.branch_availability_mode', ['selected', 'restricted', 'branch_only', 'pos_only'])
                       ->whereHas('branches', function ($bq) use ($branchId, $posOnly) {
                           $bq->where('branches.id', $branchId);
                           if (Schema::hasColumn('branch_product', 'is_active')) {
                               $bq->where('branch_product.is_active', true);
                           }
                           if (Schema::hasColumn('branch_product', 'can_sell')) {
                               $bq->where('branch_product.can_sell', true);
                           }
                           if ($posOnly && Schema::hasColumn('branch_product', 'is_pos_enabled')) {
                               $bq->where('branch_product.is_pos_enabled', true);
                           }
                       });
                });

                // Mode C & D: Main / Head Office Only
                if ($isMain) {
                    $q->orWhereIn('products.branch_availability_mode', ['main_only', 'head_office_only']);
                }
            });
        }

        return $query->with([
            'variants' => function ($vq) {
                if (Schema::hasColumn('product_variants', 'is_active')) {
                    $vq->where('is_active', true);
                } elseif (Schema::hasColumn('product_variants', 'status')) {
                    $vq->where('status', 'active');
                }
                $vq->orderBy('id', 'asc');
            },
            'stocks' => function ($sq) use ($branchId) {
                $sq->where('branch_id', $branchId);
            },
            'branchPrices' => function ($pq) use ($branchId) {
                $pq->where('branch_id', $branchId);
            },
        ]);
    }

    public static function searchBranchProducts(int $branchId, ?string $keyword = null, int $limit = 24): Collection
    {
        $query = self::getBranchProductsQuery($branchId, true);

        if (!empty($keyword)) {
            $term = trim($keyword);
            $query->where(function ($q) use ($term) {
                $hasTitle = Schema::hasColumn('products', 'title');
                $hasName  = Schema::hasColumn('products', 'name');

                if ($hasTitle && $hasName) {
                    $q->where('products.title', 'LIKE', "%{$term}%")->orWhere('products.name', 'LIKE', "%{$term}%");
                } elseif ($hasTitle) {
                    $q->where('products.title', 'LIKE', "%{$term}%");
                } elseif ($hasName) {
                    $q->where('products.name', 'LIKE', "%{$term}%");
                }

                if (Schema::hasColumn('products', 'sku')) {
                    $q->orWhere('products.sku', 'LIKE', "%{$term}%");
                }
                if (Schema::hasColumn('products', 'product_code')) {
                    $q->orWhere('products.product_code', 'LIKE', "%{$term}%");
                }

                $q->orWhereHas('variants', function ($vq) use ($term) {
                    if (Schema::hasColumn('product_variants', 'sku')) {
                        $vq->where('sku', 'LIKE', "%{$term}%");
                    }
                    if (Schema::hasColumn('product_variants', 'product_code')) {
                        $vq->orWhere('product_code', 'LIKE', "%{$term}%");
                    }
                    if (Schema::hasColumn('product_variants', 'color')) {
                        $vq->orWhere('color', 'LIKE', "%{$term}%");
                    }
                    if (Schema::hasColumn('product_variants', 'size')) {
                        $vq->orWhere('size', 'LIKE', "%{$term}%");
                    }
                });
            });
        }

        // Schema-safe order by title or name
        $orderCol = Schema::hasColumn('products', 'title') 
            ? 'products.title' 
            : (Schema::hasColumn('products', 'name') ? 'products.name' : 'products.id');

        $products = $query->orderBy($orderCol, 'asc')->limit($limit)->get();

        return $products->map(function ($product) use ($branchId) {
            $pricing = self::resolvePrice($product, $branchId);
            $product->resolved_branch_price = $pricing['effective_price'];
            $product->resolved_regular_price = $pricing['regular_price'];
            $product->resolved_sale_price = $pricing['sale_price'];
            $product->is_price_overridden = $pricing['is_branch_override'];

            if ($product->is_variable) {
                $variantStockSum = 0;
                foreach ($product->variants as $variant) {
                    $vStockRecord = $product->stocks
                        ->where('branch_id', $branchId)
                        ->where('variant_id', $variant->id)
                        ->first();

                    $vAvail = $vStockRecord 
                        ? max(0, (int)$vStockRecord->quantity - (int)($vStockRecord->reserved_quantity ?? 0))
                        : 0;

                    $variant->branch_available_stock = $vAvail;
                    $variantStockSum += $vAvail;

                    $vPricing = self::resolvePrice($product, $branchId, $variant->id);
                    $variant->resolved_branch_price = $vPricing['effective_price'];
                }
                $product->branch_available_stock = $variantStockSum;
            } else {
                $stockRecord = $product->stocks
                    ->where('branch_id', $branchId)
                    ->whereNull('variant_id')
                    ->first();

                $product->branch_available_stock = $stockRecord
                    ? max(0, (int)$stockRecord->quantity - (int)($stockRecord->reserved_quantity ?? 0))
                    : 0;
            }

            return $product;
        });
    }
}