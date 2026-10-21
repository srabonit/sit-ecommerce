@php
    // 1. Performance Optimization: Batch preload categories, brands, and variants for all products on current page
    $productIds = $products->pluck('id')->toArray();
    $productCategoriesMap = [];
    $productBrandsMap = [];
    $productVariantsMap = [];

    // Universal Thumbnail Resolver Helper supporting Global Media Library paths
    $resolveTableThumb = function($path) {
        $fallback = 'https://placehold.co/50x50?text=No+Img';
        if (empty($path)) {
            return $fallback;
        }
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) {
            return show_image($path, $fallback);
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        return asset(ltrim($path, '/'));
    };

    if (!empty($productIds)) {
        // Batch fetch categories and sub-categories
        if (\Illuminate\Support\Facades\Schema::hasTable('product_category')) {
            $pivotCategories = \Illuminate\Support\Facades\DB::table('product_category')
                ->whereIn('product_id', $productIds)
                ->get();
            
            $parentCatIds = $pivotCategories->where('type', 'parent')->pluck('category_id')->unique()->toArray();
            $childCatIds = $pivotCategories->where('type', 'child')->pluck('category_id')->unique()->toArray();
            
            $parentMap = \Illuminate\Support\Facades\DB::table('categories')->whereIn('id', $parentCatIds)->pluck('name', 'id')->toArray();
            $childMap = \Illuminate\Support\Facades\Schema::hasTable('sub_categories') 
                ? \Illuminate\Support\Facades\DB::table('sub_categories')->whereIn('id', $childCatIds)->pluck('name', 'id')->toArray() 
                : [];

            foreach ($pivotCategories as $pc) {
                $cName = ($pc->type === 'child') 
                    ? ($childMap[$pc->category_id] ?? null) 
                    : ($parentMap[$pc->category_id] ?? null);
                
                if ($cName) {
                    $productCategoriesMap[$pc->product_id][] = $cName;
                }
            }
        }

        // Batch fetch brands
        if (\Illuminate\Support\Facades\Schema::hasTable('product_brand')) {
            $pivotBrands = \Illuminate\Support\Facades\DB::table('product_brand')
                ->whereIn('product_id', $productIds)
                ->get();
            
            $bIds = $pivotBrands->pluck('brand_id')->unique()->toArray();
            $bMap = \Illuminate\Support\Facades\DB::table('brands')->whereIn('id', $bIds)->pluck('name', 'id')->toArray();

            foreach ($pivotBrands as $pb) {
                $bName = $bMap[$pb->brand_id] ?? null;
                if ($bName) {
                    $productBrandsMap[$pb->product_id][] = $bName;
                }
            }
        }

        // Batch fetch variants (Single source of truth for variant stock & costs)
        if (\Illuminate\Support\Facades\Schema::hasTable('product_variants')) {
            $variants = \Illuminate\Support\Facades\DB::table('product_variants')
                ->whereIn('product_id', $productIds)
                ->select(
                    'product_id',
                    'id',
                    'stock',
                    'purchase_cost',
                    'purchase_price',
                    'alert_quantity'
                )
                ->get();

            foreach ($variants as $v) {
                if (!isset($productVariantsMap[$v->product_id])) {
                    $productVariantsMap[$v->product_id] = [
                        'count'              => 0,
                        'total_stock'        => 0,
                        'low_stock_count'    => 0,
                        'out_of_stock_count' => 0,
                        'min_cost'           => null,
                        'max_cost'           => null,
                    ];
                }

                $cost = !is_null($v->purchase_cost) && (float)$v->purchase_cost > 0 
                    ? (float)$v->purchase_cost 
                    : (float)($v->purchase_price ?? 0);

                $stock = max(0, (int)($v->stock ?? 0));
                $alertLimit = isset($v->alert_quantity) ? (int)$v->alert_quantity : 5;

                $productVariantsMap[$v->product_id]['count']++;
                $productVariantsMap[$v->product_id]['total_stock'] += $stock;

                if ($stock <= 0) {
                    $productVariantsMap[$v->product_id]['out_of_stock_count']++;
                } elseif ($stock <= $alertLimit) {
                    $productVariantsMap[$v->product_id]['low_stock_count']++;
                }

                if ($cost > 0) {
                    if (is_null($productVariantsMap[$v->product_id]['min_cost']) || $cost < $productVariantsMap[$v->product_id]['min_cost']) {
                        $productVariantsMap[$v->product_id]['min_cost'] = $cost;
                    }
                    if (is_null($productVariantsMap[$v->product_id]['max_cost']) || $cost > $productVariantsMap[$v->product_id]['max_cost']) {
                        $productVariantsMap[$v->product_id]['max_cost'] = $cost;
                    }
                }
            }
        }
    }
@endphp

@forelse($products as $prod)
    @php
        $img = $resolveTableThumb($prod->thumbnail ?? null);

        // Filter and deduplicate categories
        $cats = array_values(array_unique($productCategoriesMap[$prod->id] ?? (!empty($prod->category_name) ? [$prod->category_name] : [])));
        $totalCats = count($cats);

        // Filter and deduplicate brands
        $bList = array_values(array_unique($productBrandsMap[$prod->id] ?? (!empty($prod->brand_name) ? [$prod->brand_name] : [])));
        $totalBrands = count($bList);

        // Variant metadata resolution
        $varData = $productVariantsMap[$prod->id] ?? null;
        $hasVariants = ($prod->product_struct === 'variable') || (!empty($varData) && $varData['count'] > 0);
        $totalStock = $hasVariants && !empty($varData) ? $varData['total_stock'] : (int)($prod->stock ?? 0);

        // Purchase cost calculation
        $costDisplay = '—';
        if ($hasVariants && !empty($varData) && !is_null($varData['min_cost'])) {
            if ($varData['min_cost'] == $varData['max_cost']) {
                $costDisplay = '৳' . number_format($varData['min_cost'], 2);
            } else {
                $costDisplay = '৳' . number_format($varData['min_cost'], 2) . ' – ৳' . number_format($varData['max_cost'], 2);
            }
        } else {
            $baseCost = !empty($prod->purchase_cost) ? (float)$prod->purchase_cost : (!empty($prod->purchase_price) ? (float)$prod->purchase_price : null);
            if (!is_null($baseCost) && $baseCost > 0) {
                $costDisplay = '৳' . number_format($baseCost, 2);
            }
        }
    @endphp
    <tr id="product-row-{{ $prod->id }}" class="align-middle border-bottom">
        <!-- 1. Row Selection Checkbox -->
        <td class="col-checkbox text-center py-1 px-1">
            <input type="checkbox" class="form-check-input product-select-checkbox m-0 shadow-none" value="{{ $prod->id }}" id="chk-prod-{{ $prod->id }}" style="border-radius: 5px !important; cursor: pointer; width: 14px; height: 14px;">
        </td>

        <!-- 2. Product ID -->
        <td class="col-id font-monospace fw-bold text-secondary text-center text-nowrap bn-digit py-1 px-1" style="font-size: 11px;">
            #{{ $prod->id }}
        </td>

        <!-- 3. Global Media Chooser Interactive Thumbnail & Quick Attach -->
        <td class="col-thumb text-center py-1 px-1">
            <div class="d-inline-flex align-items-center justify-content-center position-relative">
                <div class="proof-thumb-box" style="border-radius: 5px !important;" onclick="zoomProductImage('{{ $img }}', '{{ addslashes($prod->title) }}')" title="Click to Zoom Image">
                    <img src="{{ $img }}" alt="{{ $prod->title }}" onerror="this.src='https://placehold.co/50x50?text=No+Img'">
                    <div class="proof-thumb-hover-overlay" style="border-radius: 5px !important;">
                        <i class="fas fa-search-plus text-white" style="font-size: 8.5px;"></i>
                    </div>
                </div>
                <button type="button" class="btn btn-primary btn-sm p-0 position-absolute shadow-xs d-flex align-items-center justify-content-center" style="width: 15px; height: 15px; bottom: -3px; right: -3px; border-radius: 5px !important; font-size: 7.5px;" onclick="chooseProductThumbnailMedia({{ $prod->id }})" title="Change via Media Library">
                    <i class="fas fa-camera"></i>
                </button>
            </div>
        </td>

        <!-- 4. Product Name & Metadata -->
        <td class="col-title py-1 px-1" style="overflow: hidden;">
            <div class="min-w-0">
                <a href="{{ route('admin.product.edit', $prod->id) }}" class="fw-bold text-dark text-decoration-none d-block text-truncate" style="font-size: 11.5px; line-height: 1.3;" title="{{ $prod->title }}">
                    {{ $prod->title }}
                </a>
                <div class="small text-muted d-flex align-items-center gap-1 mt-1 text-nowrap" style="font-size: 9.5px;">
                    <span>{{ ucfirst($prod->product_type ?? 'physical') }}</span>
                    <span>•</span>
                    @if($hasVariants)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-1 py-0 font-monospace" style="font-size: 8.5px; border-radius: 5px !important;">Variable</span>
                    @else
                        <span class="badge bg-light text-dark border px-1 py-0 font-monospace" style="font-size: 8.5px; border-radius: 5px !important;">Simple</span>
                    @endif
                </div>
            </div>
        </td>

        <!-- 5. Product SKU / Code -->
        <td class="col-sku font-monospace small text-nowrap bn-digit py-1 px-1" style="font-size: 10.5px; overflow: hidden;" title="{{ $prod->product_code ?? ($prod->sku ?? '—') }}">
            <span class="text-truncate d-block">{{ $prod->product_code ?? ($prod->sku ?? '—') }}</span>
        </td>

        <!-- 6. Categories -->
        <td class="col-category small py-1 px-1" style="font-size: 10.5px; overflow: hidden;">
            @if($totalCats > 0)
                @if($totalCats <= 2)
                    <span class="text-dark d-block text-truncate" title="{{ implode(', ', $cats) }}">{{ implode(', ', $cats) }}</span>
                @else
                    @php
                        $firstCats = array_slice($cats, 0, 2);
                        $moreCatsCount = $totalCats - 2;
                    @endphp
                    <span class="text-dark d-block text-truncate" title="{{ implode(', ', $cats) }}">
                        {{ implode(', ', $firstCats) }}
                        <span class="badge bg-light text-primary border ms-1 bn-digit font-monospace px-1 py-0" style="font-size: 9px; border-radius: 5px !important;">
                            +{{ $moreCatsCount }}
                        </span>
                    </span>
                @endif
            @else
                <span class="text-muted font-monospace" style="font-size: 10px;">Uncategorized</span>
            @endif
        </td>

        <!-- 7. Brands -->
        <td class="col-brand small py-1 px-1" style="font-size: 10.5px; overflow: hidden;">
            @if($totalBrands > 0)
                @if($totalBrands <= 2)
                    <span class="text-dark d-block text-truncate" title="{{ implode(', ', $bList) }}">{{ implode(', ', $bList) }}</span>
                @else
                    @php
                        $firstBrands = array_slice($bList, 0, 2);
                        $moreBrandsCount = $totalBrands - 2;
                    @endphp
                    <span class="text-dark d-block text-truncate" title="{{ implode(', ', $bList) }}">
                        {{ implode(', ', $firstBrands) }}
                        <span class="badge bg-light text-primary border ms-1 bn-digit font-monospace px-1 py-0" style="font-size: 9px; border-radius: 5px !important;">
                            +{{ $moreBrandsCount }}
                        </span>
                    </span>
                @endif
            @else
                <span class="text-muted font-monospace" style="font-size: 10px;">—</span>
            @endif
        </td>

        <!-- 8. Stock Status (Single Source of Truth) -->
        <td class="col-stock text-nowrap py-1 px-1" style="overflow: hidden;">
            @if($hasVariants && !empty($varData))
                @if($totalStock > 0)
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-1 py-0 bn-digit font-monospace" style="font-size: 9px; border-radius: 5px !important;">
                        In stock ({{ $totalStock }})
                    </span>
                    <div class="mt-1 d-flex flex-column" style="line-height: 1.1;">
                        <span class="text-muted font-monospace bn-digit" style="font-size: 9px;">{{ $varData['count'] }} vars</span>
                        @if($varData['out_of_stock_count'] > 0)
                            <span class="text-danger fw-semibold font-monospace bn-digit text-truncate" style="font-size: 8.5px;" title="{{ $varData['out_of_stock_count'] }} out of stock">
                                <i class="fas fa-exclamation-triangle me-1"></i>{{ $varData['out_of_stock_count'] }} out
                            </span>
                        @elseif($varData['low_stock_count'] > 0)
                            <span class="text-warning-emphasis fw-semibold font-monospace bn-digit text-truncate" style="font-size: 8.5px;" title="{{ $varData['low_stock_count'] }} low stock">
                                <i class="fas fa-bell me-1"></i>{{ $varData['low_stock_count'] }} low
                            </span>
                        @endif
                    </div>
                @else
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-1 py-0 font-monospace" style="font-size: 9px; border-radius: 5px !important;">
                        Out of stock
                    </span>
                    <small class="d-block text-muted bn-digit font-monospace mt-1" style="font-size: 8.5px;">All {{ $varData['count'] }} vars 0</small>
                @endif
            @else
                @if($totalStock > 0)
                    @if($totalStock <= 5)
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-1 py-0 bn-digit font-monospace" style="font-size: 9px; border-radius: 5px !important;">
                            Low stock ({{ $totalStock }})
                        </span>
                    @else
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-1 py-0 bn-digit font-monospace" style="font-size: 9px; border-radius: 5px !important;">
                            In stock ({{ $totalStock }})
                        </span>
                    @endif
                @else
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-1 py-0 font-monospace" style="font-size: 9px; border-radius: 5px !important;">
                        Out of stock
                    </span>
                @endif
            @endif
        </td>

        <!-- 9. Retail Selling Price -->
        <td class="col-price text-nowrap font-monospace bn-digit py-1 px-1" style="overflow: hidden;">
            <div class="fw-bold text-dark" style="font-size: 11px;">৳{{ number_format((float)$prod->regular_price, 2) }}</div>
            @if(!empty($prod->sale_price))
                <small class="text-danger fw-bold d-block font-monospace bn-digit" style="font-size: 9.5px;">Sale: ৳{{ number_format((float)$prod->sale_price, 2) }}</small>
            @endif
        </td>

        <!-- 10. Purchase Cost -->
        <td class="col-purchase-price text-secondary text-nowrap font-monospace bn-digit py-1 px-1" style="font-size: 10.5px; overflow: hidden;" title="{{ $costDisplay }}">
            <span class="text-truncate d-block">{{ $costDisplay }}</span>
        </td>

        <!-- 11. Featured Star Toggle -->
        <td class="col-featured text-center text-nowrap py-1 px-1">
            <button type="button" class="btn btn-sm border-0 p-0 text-warning shadow-none" onclick="toggleFeaturedAjax({{ $prod->id }}, this)" id="btn-feat-{{ $prod->id }}" title="Click to toggle featured" style="line-height: 1;">
                @if($prod->is_featured)
                    <i class="fas fa-star" style="font-size: 13px;"></i>
                @else
                    <i class="far fa-star text-secondary opacity-50" style="font-size: 13px;"></i>
                @endif
            </button>
        </td>

        <!-- 12. Actions Column -->
        <td class="col-action text-end text-nowrap pe-2 py-1">
            <div class="d-inline-flex justify-content-end align-items-center gap-1">
                <!-- Front Live View -->
                <a href="{{ route('product.details', $prod->slug ?? $prod->id) }}" target="_blank" class="btn btn-light border text-info p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 24px; height: 24px; border-radius: 5px !important;" title="View Live on Storefront" id="btn-view-{{ $prod->id }}">
                    <i class="fas fa-eye" style="font-size: 9.5px;"></i>
                </a>

                <!-- Edit Product -->
                <a href="{{ route('admin.product.edit', $prod->id) }}" class="btn btn-light border text-primary p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 24px; height: 24px; border-radius: 5px !important;" title="Edit Product" id="btn-edit-{{ $prod->id }}">
                    <i class="fas fa-edit" style="font-size: 9.5px;"></i>
                </a>

                <!-- Delete Product -->
                <button type="button" class="btn btn-light border text-danger btn-delete-product p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 24px; height: 24px; border-radius: 5px !important;" data-id="{{ $prod->id }}" data-title="{{ $prod->title }}" title="Delete Product" id="btn-delete-{{ $prod->id }}">
                    <i class="fas fa-trash-alt" style="font-size: 9.5px;"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="12" class="text-center py-5 text-muted small">
            <i class="fas fa-box-open fa-2x mb-2 d-block opacity-25"></i>
            <span class="fw-semibold">No matching products found.</span>
        </td>
    </tr>
@endforelse