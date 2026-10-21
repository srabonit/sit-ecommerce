@forelse($items as $item)
    @php
        $varParts = array_filter([$item->color ?? null, $item->size ?? null, $item->variant_name ?? null]);
        $varLabel = count($varParts) > 0 ? implode(' / ', $varParts) : null;
        $sku = $item->variant_sku ?? ($item->parent_sku ?? '—');
        $stockInQty  = (int) ($item->stock_in_qty ?? 0);
        $stockInVal  = (float) ($item->stock_in_val ?? 0);
        $stockOutQty = (int) ($item->stock_out_qty ?? 0);
        $salesQty    = (int) ($item->sales_qty ?? 0);
        $salesRev    = (float) ($item->sales_rev ?? 0);
        $salesCogs   = (float) ($item->sales_cogs ?? 0);
        $grossProfit = max(0.00, $salesRev - $salesCogs);

        // Safe image resolver for Global Media Chooser thumbnail
        $rawImg = $item->thumbnail ?? ($item->image ?? null);
        $thumb = 'https://placehold.co/60x60?text=Item';
        if (!empty($rawImg)) {
            if (class_exists(\App\Services\ImagePathService::class)) {
                $thumb = \App\Services\ImagePathService::resolveUrl($rawImg, $thumb);
            } elseif (function_exists('show_image')) {
                $thumb = show_image($rawImg, $thumb);
            } elseif (filter_var($rawImg, FILTER_VALIDATE_URL)) {
                $thumb = $rawImg;
            } else {
                $thumb = asset(ltrim(str_replace(['public_html/', 'public/'], '', $rawImg), '/'));
            }
        }

        // Fail-safe ledger details route resolver
        $ledgerUrl = Route::has('admin.inventory.product.details')
            ? route('admin.inventory.product.details', array_filter(['id' => $item->product_id, 'variant_id' => $item->variant_id ?? null]))
            : url('/admin/inventory/product/' . $item->product_id . ($item->variant_id ? '?variant_id=' . $item->variant_id : ''));
    @endphp
    <tr class="align-middle border-bottom" style="transition: background-color 0.15s ease;">
        <!-- 1. Product ID -->
        <td class="font-monospace bn-digit fw-semibold text-secondary py-2 ps-3" style="font-size: 11px; width: 60px;">
            #{{ $item->product_id }}
        </td>

        <!-- 2. Product, Global Media Thumbnail & Attributes -->
        <td class="py-2" style="min-width: 220px; max-width: 320px;">
            <div class="d-flex align-items-center gap-2">
                <!-- Media Chooser Thumbnail with Quick Zoom -->
                <div class="position-relative flex-shrink-0" style="width: 36px; height: 36px; border-radius: 5px !important; overflow: hidden; border: 1px solid #cbd5e1; background: #ffffff; cursor: pointer;" onclick="if(typeof zoomReportImage === 'function'){ zoomReportImage('{{ $thumb }}'); }" title="Click to Zoom Image">
                    <img src="{{ $thumb }}" alt="{{ $item->product_title }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='https://placehold.co/60x60?text=Item';">
                </div>

                <div class="min-w-0 flex-grow-1">
                    <strong class="text-dark d-block text-truncate" style="font-size: 12px; line-height: 1.3;" title="{{ $item->product_title }}">
                        {{ $item->product_title }}
                    </strong>
                    <div class="d-flex flex-wrap align-items-center gap-1 mt-1">
                        @if($varLabel)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-1 py-0 text-truncate" style="font-size: 10px; border-radius: 5px !important; max-width: 130px;" title="{{ $varLabel }}">
                                <i class="fas fa-layer-group me-1"></i>{{ $varLabel }}
                            </span>
                        @else
                            <span class="badge bg-light text-muted border font-monospace px-1 py-0" style="font-size: 10px; border-radius: 5px !important;">
                                Simple
                            </span>
                        @endif
                        <span class="badge bg-light text-dark border font-monospace bn-digit px-1 py-0 text-truncate" style="font-size: 9px; border-radius: 5px !important; max-width: 120px;" title="{{ $sku }}">
                            SKU: {{ $sku }}
                        </span>
                    </div>
                </div>
            </div>
        </td>

        <!-- 3. Master / Variant SKU -->
        <td class="font-monospace text-nowrap bn-digit py-2" style="font-size: 11px; width: 110px;">
            <span class="badge bg-light text-dark border px-2 py-1 font-monospace" style="border-radius: 5px !important; font-size: 10px;">
                {{ $sku }}
            </span>
        </td>

        <!-- 4. Stock In Quantity -->
        <td class="text-center text-nowrap font-monospace bn-digit py-2 text-success fw-bold" style="font-size: 12px; width: 90px;">
            {{ $stockInQty > 0 ? '+' . number_format($stockInQty) : '0' }}
        </td>

        <!-- 5. Stock In Valuation -->
        <td class="text-end text-nowrap font-monospace bn-digit py-2 text-success" style="font-size: 11px; width: 110px;">
            ৳ {{ number_format($stockInVal, 2) }}
        </td>

        <!-- 6. Stock Out Quantity -->
        <td class="text-center text-nowrap font-monospace bn-digit py-2 text-danger fw-bold" style="font-size: 12px; width: 90px;">
            {{ $stockOutQty > 0 ? '-' . number_format($stockOutQty) : '0' }}
        </td>

        <!-- 7. Sales Units Quantity -->
        <td class="text-center text-nowrap font-monospace bn-digit py-2 text-primary fw-bold" style="font-size: 12px; width: 90px;">
            {{ number_format($salesQty) }}
        </td>

        <!-- 8. Total Sales Revenue -->
        <td class="text-end text-nowrap font-monospace bn-digit py-2 text-dark fw-bold" style="font-size: 11px; width: 120px;">
            ৳ {{ number_format($salesRev, 2) }}
        </td>

        <!-- 9. Cost of Goods Sold (COGS) -->
        <td class="text-end text-nowrap font-monospace bn-digit py-2 text-secondary" style="font-size: 11px; width: 120px;">
            ৳ {{ number_format($salesCogs, 2) }}
        </td>

        <!-- 10. Resulting Gross Profit -->
        <td class="text-end text-nowrap font-monospace bn-digit py-2" style="font-size: 12px; width: 120px;">
            <span class="fw-bold {{ $grossProfit > 0 ? 'text-success' : 'text-muted' }}">
                ৳ {{ number_format($grossProfit, 2) }}
            </span>
        </td>

        <!-- 11. Action / Product Ledger Link -->
        <td class="text-end text-nowrap py-2 pe-3" style="width: 85px;">
            <a href="{{ $ledgerUrl }}" class="btn btn-sm btn-light border py-1 px-2 shadow-xs d-inline-flex align-items-center gap-1" title="View Product Ledger" style="font-size: 11px; border-radius: 5px !important; line-height: 1.2;">
                <i class="fas fa-eye text-primary" style="font-size: 10px;"></i> <span>Ledger</span>
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="11" class="text-center py-5 text-muted small">
            <i class="fas fa-box-open fa-2x mb-2 d-block opacity-25"></i>
            <span class="fw-semibold">No product activity or sales recorded in this selected period.</span>
        </td>
    </tr>
@endforelse