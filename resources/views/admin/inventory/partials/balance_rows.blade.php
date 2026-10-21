@php
    // Safe thumbnail resolver supporting Global Media Library
    $resolveThumb = function($path, $fallback = 'https://placehold.co/60x60?text=Item') {
        if (empty($path)) return $fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) return show_image($path, $fallback);
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset(ltrim(str_replace(['public_html/', 'public/'], '', $path), '/'));
    };
@endphp

@forelse($items as $item)
    @php
        $thumb = $resolveThumb($item->thumbnail ?? null);
        $stock = (int) ($item->stock ?? 0);
        $alertQty = (int) ($item->alert_quantity ?? 5);
        $purchaseVal = $stock * (float) ($item->purchase_cost ?? 0);

        // Stock status resolution with badge meta
        $statusMeta = match(true) {
            $stock <= 0         => ['label' => 'Out of Stock', 'class' => 'bg-danger-subtle text-danger border-danger-subtle', 'icon' => 'fa-circle-xmark'],
            $stock <= $alertQty => ['label' => 'Low Stock',    'class' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle', 'icon' => 'fa-triangle-exclamation'],
            default             => ['label' => 'In Stock',     'class' => 'bg-success-subtle text-success border-success-subtle', 'icon' => 'fa-circle-check'],
        };

        // Fail-safe ledger details route resolver
        $ledgerUrl = Route::has('admin.inventory.product.details')
            ? route('admin.inventory.product.details', array_filter(['id' => $item->product_id, 'variant_id' => $item->variant_id ?? null]))
            : url('/admin/inventory/product/' . $item->product_id . ($item->variant_id ? '?variant_id=' . $item->variant_id : ''));
    @endphp
    <tr class="align-middle border-bottom" style="transition: background-color 0.15s ease;">
        <!-- 1. Product ID -->
        <td class="font-monospace bn-digit fw-semibold text-secondary py-2 ps-3 text-nowrap" style="font-size: 11px; width: 60px;">
            #{{ $item->product_id }}
        </td>

        <!-- 2. Product, Global Media Thumbnail & Attributes -->
        <td class="py-2" style="min-width: 220px; max-width: 320px;">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <!-- Media Chooser Thumbnail with Quick Zoom -->
                <div class="proof-thumb-box flex-shrink-0" style="width: 36px; height: 36px; border-radius: 5px !important; overflow: hidden; border: 1px solid #cbd5e1; background: #ffffff; cursor: pointer; position: relative;" onclick="if(typeof zoomBalanceImage === 'function'){ zoomBalanceImage('{{ $thumb }}', '{{ addslashes($item->product_title) }}'); }" title="Click to Zoom Thumbnail">
                    <img src="{{ $thumb }}" alt="{{ $item->product_title }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='https://placehold.co/60x60?text=Item';">
                    <div class="proof-thumb-hover-overlay" style="border-radius: 5px !important; position: absolute; inset: 0; background: rgba(15, 23, 42, 0.45); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.15s ease;">
                        <i class="fas fa-search-plus text-white" style="font-size: 8px;"></i>
                    </div>
                </div>

                <div class="min-w-0 flex-grow-1" style="line-height: 1.3;">
                    <strong class="text-dark d-block text-truncate" style="font-size: 12px;" title="{{ $item->product_title }}">
                        {{ $item->product_title }}
                    </strong>
                    @if(!empty($item->variant_attributes))
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-truncate d-inline-block px-1 py-0 mt-1" style="font-size: 9px; max-width: 140px; border-radius: 5px !important;" title="{{ $item->variant_attributes }}">
                            <i class="fas fa-layer-group me-1"></i>{{ $item->variant_attributes }}
                        </span>
                    @else
                        <span class="badge bg-light text-muted border font-monospace px-1 py-0 mt-1" style="font-size: 9px; border-radius: 5px !important;">
                            Simple
                        </span>
                    @endif
                </div>
            </div>
        </td>

        <!-- 3. Item Type -->
        <td class="py-2 text-nowrap" style="width: 80px;">
            @if(($item->item_type ?? '') === 'variant')
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1" style="font-size: 10px; border-radius: 5px !important;">Variant</span>
            @else
                <span class="badge bg-light text-dark border font-monospace px-2 py-1" style="font-size: 10px; border-radius: 5px !important;">Simple</span>
            @endif
        </td>

        <!-- 4. SKU -->
        <td class="font-monospace text-nowrap bn-digit py-2" style="font-size: 11px; width: 110px;">
            <span class="badge bg-light text-dark border px-2 py-1 font-monospace text-truncate d-inline-block" style="border-radius: 5px !important; font-size: 10px; max-width: 100%;" title="{{ $item->sku ?? '—' }}">
                {{ $item->sku ?? '—' }}
            </span>
        </td>

        <!-- 5. Available Stock -->
        <td class="text-center text-nowrap font-monospace bn-digit py-2" style="font-size: 12px; width: 90px;">
            <strong class="{{ $stock <= 0 ? 'text-danger' : ($stock <= $alertQty ? 'text-warning-emphasis' : 'text-success') }}">
                {{ number_format($stock) }}
            </strong>
        </td>

        <!-- 6. Unit Purchase Cost -->
        <td class="text-end text-nowrap font-monospace bn-digit py-2 text-muted" style="font-size: 11px; width: 110px;">
            ৳ {{ number_format((float)($item->purchase_cost ?? 0), 2) }}
        </td>

        <!-- 7. Retail Selling Price -->
        <td class="text-end text-nowrap font-monospace bn-digit py-2 text-dark" style="font-size: 11px; width: 110px;">
            ৳ {{ number_format((float)($item->selling_price ?? 0), 2) }}
        </td>

        <!-- 8. Total Purchase Valuation -->
        <td class="text-end text-nowrap font-monospace bn-digit py-2 fw-bold text-dark" style="font-size: 12px; width: 120px;">
            ৳ {{ number_format($purchaseVal, 2) }}
        </td>

        <!-- 9. Stock Status Badge -->
        <td class="text-center text-nowrap py-2" style="width: 110px;">
            <span class="badge border {{ $statusMeta['class'] }}" style="font-size: 9px; padding: 3px 6px; border-radius: 5px !important; white-space: nowrap;">
                <i class="fas {{ $statusMeta['icon'] }} me-1"></i>{{ $statusMeta['label'] }}
            </span>
        </td>

        <!-- 10. Ledger Action -->
        <td class="text-end text-nowrap py-2 pe-3" style="width: 85px;">
            <a href="{{ $ledgerUrl }}" class="btn btn-sm btn-light border py-1 px-2 shadow-xs d-inline-flex align-items-center gap-1" title="View Detailed Ledger" style="font-size: 11px; border-radius: 5px !important; line-height: 1.2;">
                <i class="fas fa-eye text-primary" style="font-size: 10px;"></i> <span>Ledger</span>
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="10" class="text-center py-5 text-muted small">
            <i class="fas fa-box-open fa-2x mb-2 d-block opacity-25"></i>
            <span class="fw-semibold">No stock balance items found matching the selected filters.</span>
        </td>
    </tr>
@endforelse