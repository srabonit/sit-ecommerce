@php
    // Media image resolver supporting Global Media Library paths
    $resolveLedgerThumb = function($path, $fallback = 'https://placehold.co/60x60?text=Item') {
        if (empty($path)) return $fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) return show_image($path, $fallback);
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset(ltrim(str_replace(['public_html/', 'public/'], '', $path), '/'));
    };
@endphp

@forelse($ledger as $item)
    @php
        $revenue    = (float)($item->total_revenue ?? 0);
        $cost       = (float)($item->total_cost ?? 0);
        $profit     = max(0.00, $revenue - $cost);
        $skuDisplay = $item->sku ?? ($item->product_code ?? '—');
        
        // Resolve product media thumbnail
        $rawThumb = $item->thumbnail ?? ($item->image ?? ($item->product_thumbnail ?? null));
        $thumb    = $resolveLedgerThumb($rawThumb);
        $buyersCount = (int)($item->unique_buyers_count ?? 0);
        $buyersList  = trim($item->buyers_list ?? '');
    @endphp
    <tr class="align-middle border-bottom">
        <!-- Product ID -->
        <td class="font-monospace bn-digit fw-semibold text-secondary py-2 ps-3 text-nowrap" style="width: 65px; font-size: 11px;">
            #{{ $item->product_id }}
        </td>

        <!-- Product Title, Global Media Thumbnail & SKU -->
        <td class="py-2" style="min-width: 220px; max-width: 300px;">
            <div class="d-flex align-items-center gap-2">
                <!-- Media Chooser Thumbnail with Click-to-Zoom -->
                <div class="position-relative flex-shrink-0" style="width: 36px; height: 36px; border-radius: 5px !important; overflow: hidden; border: 1px solid #cbd5e1; background: #ffffff;">
                    <img src="{{ $thumb }}" alt="{{ $item->product_title }}" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;" onclick="if(typeof zoomLedgerImage === 'function'){ zoomLedgerImage('{{ $thumb }}', '{{ addslashes($item->product_title) }}'); }" onerror="this.src='https://placehold.co/60x60?text=Item';">
                </div>

                <div class="min-width-0 flex-grow-1">
                    <strong class="text-dark d-block text-truncate" style="font-size: 12.5px; line-height: 1.3;" title="{{ $item->product_title }}">
                        {{ $item->product_title }}
                    </strong>
                    <div class="d-flex align-items-center gap-1 mt-1">
                        <span class="badge bg-light text-dark border font-monospace bn-digit px-1 py-0" style="font-size: 9.5px; border-radius: 5px !important;">
                            SKU: {{ $skuDisplay }}
                        </span>
                    </div>
                </div>
            </div>
        </td>

        <!-- Regular Unit Price -->
        <td class="text-end text-nowrap font-monospace bn-digit py-2" style="width: 110px; font-size: 11.5px;">
            <span class="text-dark fw-semibold">৳ {{ number_format((float)($item->regular_price ?? 0), 2) }}</span>
        </td>

        <!-- Times Sold Count -->
        <td class="text-center text-nowrap py-2" style="width: 100px;">
            <span class="badge bg-light text-dark border font-monospace bn-digit px-2 py-1" style="font-size: 10.5px; border-radius: 5px !important;">
                {{ number_format((int)($item->times_sold ?? 0)) }} times
            </span>
        </td>

        <!-- Quantity Sold (Units) -->
        <td class="text-center text-nowrap py-2" style="width: 95px;">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace bn-digit px-2 py-1" style="font-size: 10.5px; border-radius: 5px !important;">
                {{ number_format((int)($item->total_qty ?? 0)) }} pcs
            </span>
        </td>

        <!-- Total Revenue -->
        <td class="text-end text-nowrap font-monospace bn-digit py-2" style="width: 125px; font-size: 12px;">
            <strong class="text-dark">৳ {{ number_format($revenue, 2) }}</strong>
        </td>

        <!-- Gross Profit -->
        <td class="text-end text-nowrap font-monospace bn-digit py-2" style="width: 125px; font-size: 12px;">
            <strong class="{{ $profit > 0 ? 'text-success' : 'text-muted' }}">৳ {{ number_format($profit, 2) }}</strong>
        </td>

        <!-- Unique Buyers & Summary List -->
        <td class="py-2 pe-3" style="min-width: 180px; max-width: 240px;">
            <div class="min-width-0">
                <span class="badge bg-secondary-subtle text-dark border font-monospace bn-digit mb-1 px-2 py-1" style="font-size: 10px; border-radius: 5px !important;">
                    <i class="fas fa-users me-1 text-primary"></i>{{ $buyersCount }} Buyer(s)
                </span>
                <small class="d-block text-muted text-truncate font-monospace" style="font-size: 11px; line-height: 1.25;" title="{{ $buyersList ?: 'Guest Customer' }}">
                    {{ $buyersList ?: 'Guest Customer' }}
                </small>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center py-5 text-muted small">
            <i class="fas fa-box-open fa-2x mb-2 d-block opacity-25"></i>
            <span class="fw-semibold">No product sales recorded in the selected period.</span>
        </td>
    </tr>
@endforelse