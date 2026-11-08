@php
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

<style>
    /* =============================================
       PRODUCT PERFORMANCE REPORT TABLE
       Modern UI | Strict 5px Radius
    ============================================= */

    .product-performance-table {
        --rp-border: #e8edf4;
        --rp-text: #263247;
        --rp-muted: #7b8799;
        --rp-primary: #4361ee;

        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        vertical-align: middle;
    }

    .product-performance-table > tbody > tr > td {
        padding: 11px 9px;
        border-bottom: 1px solid var(--rp-border);
        background: #fff;
        vertical-align: middle;
        transition: background .18s ease;
    }

    .product-performance-table > tbody > tr:last-child > td {
        border-bottom: 0;
    }

    .product-performance-table > tbody > tr:hover > td {
        background: #f8faff;
    }

    /* Column sizing */
    .product-performance-table .col-rp-id {
        width: 75px;
        color: var(--rp-muted);
        font-size: 10.5px;
        font-weight: 700;
    }

    .product-performance-table .col-rp-prod {
        min-width: 210px;
        max-width: 280px;
    }

    .product-performance-table .col-rp-sku {
        min-width: 105px;
        max-width: 145px;
    }

    .product-performance-table .col-rp-inqty,
    .product-performance-table .col-rp-outqty,
    .product-performance-table .col-rp-sqty {
        min-width: 90px;
    }

    .product-performance-table .col-rp-inval,
    .product-performance-table .col-rp-rev,
    .product-performance-table .col-rp-cogs,
    .product-performance-table .col-rp-profit {
        min-width: 115px;
    }

    .product-performance-table .col-rp-action {
        min-width: 95px;
    }

    /* Product thumbnail */
    .product-performance-table .rp-product-thumb {
        position: relative;
        width: 42px;
        height: 42px;
        min-width: 42px;
        overflow: hidden;
        border: 1px solid #e5eaf2;
        border-radius: 5px;
        background: #f5f7fb;
        cursor: zoom-in;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .product-performance-table .rp-product-thumb:hover {
        border-color: #aabaff;
        box-shadow: 0 3px 10px rgba(67, 97, 238, .12);
    }

    .product-performance-table .rp-product-thumb img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 5px;
    }

    .product-performance-table .rp-product-thumb-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        background: rgba(25, 35, 55, .55);
        opacity: 0;
        transition: opacity .2s ease;
    }

    .product-performance-table .rp-product-thumb:hover .rp-product-thumb-overlay {
        opacity: 1;
    }

    /* Product information */
    .product-performance-table .rp-product-name {
        display: block;
        max-width: 100%;
        color: var(--rp-text);
        font-size: 11.5px;
        font-weight: 700;
        line-height: 1.4;
    }

    .product-performance-table .rp-variant-badge {
        display: inline-block;
        max-width: 140px;
        margin-top: 4px;
        padding: 3px 6px;
        overflow: hidden;
        border: 1px solid #dce4ff;
        border-radius: 5px;
        background: #eef2ff;
        color: #4058c7;
        font-size: 9px;
        font-weight: 600;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
    }

    /* SKU */
    .product-performance-table .rp-sku-badge {
        display: inline-block;
        max-width: 100%;
        padding: 4px 7px;
        overflow: hidden;
        border: 1px solid #e6eaf0;
        border-radius: 5px;
        background: #f6f8fb;
        color: #435066;
        font-size: 10px;
        font-weight: 600;
        text-overflow: ellipsis;
        vertical-align: middle;
    }

    /* Quantity indicators */
    .product-performance-table .rp-quantity {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        padding: 4px 7px;
        border-radius: 5px;
        font-size: 11.5px;
        font-weight: 800;
        font-variant-numeric: tabular-nums;
    }

    .product-performance-table .rp-quantity.in {
        background: #eaf8f0;
        color: #16834a;
    }

    .product-performance-table .rp-quantity.out {
        background: #fff0f0;
        color: #d73545;
    }

    .product-performance-table .rp-quantity.sales {
        background: #eef2ff;
        color: #4058c7;
    }

    /* Financial values */
    .product-performance-table .rp-money {
        font-size: 11px;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .product-performance-table .rp-profit {
        font-size: 11.5px;
        font-weight: 800;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    /* Ledger button */
    .product-performance-table .rp-ledger-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 30px;
        padding: 5px 9px;
        border: 1px solid #e1e7f0;
        border-radius: 5px;
        background: #fff;
        color: #40516b;
        font-size: 10.5px;
        font-weight: 700;
        text-decoration: none;
        transition: all .2s ease;
    }

    .product-performance-table .rp-ledger-btn:hover {
        border-color: #c7d2fe;
        background: #eef2ff;
        color: #3451d1;
        box-shadow: 0 2px 7px rgba(67, 97, 238, .09);
    }

    .product-performance-table .rp-ledger-btn i {
        color: var(--rp-primary);
        font-size: 10px;
    }

    /* Empty state */
    .product-performance-table .rp-empty-state {
        padding: 55px 15px !important;
        background: #fff;
        color: var(--rp-muted);
        text-align: center;
    }

    .product-performance-table .rp-empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 55px;
        height: 55px;
        margin: 0 auto 13px;
        border: 1px solid #e8edf4;
        border-radius: 5px;
        background: #f7f9fc;
        color: #a0aabc;
        font-size: 22px;
    }

    .product-performance-table .rp-empty-title {
        display: block;
        margin-bottom: 4px;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
    }

    .product-performance-table .rp-empty-description {
        color: #94a0b2;
        font-size: 11px;
    }

    /* Strict 5px radius */
    .product-performance-table,
    .product-performance-table *,
    .product-performance-table *::before,
    .product-performance-table *::after {
        border-radius: 5px;
    }

    /* Responsive */
    @media (max-width: 1199.98px) {
        .product-performance-table > tbody > tr > td {
            padding: 9px 7px;
        }

        .product-performance-table .col-rp-prod {
            min-width: 190px;
        }
    }

    @media (max-width: 767.98px) {
        .product-performance-table > tbody > tr > td {
            padding: 9px 7px;
        }

        .product-performance-table .rp-product-thumb {
            width: 37px;
            height: 37px;
            min-width: 37px;
        }

        .product-performance-table .rp-product-name {
            font-size: 11px;
        }

        .product-performance-table .rp-money {
            font-size: 10px;
        }

        .product-performance-table .rp-profit {
            font-size: 10.5px;
        }
    }
</style>

@forelse($items as $item)
    @php
        $varParts = array_filter([
            $item->color ?? null,
            $item->size ?? null,
            $item->variant_name ?? null
        ]);

        $varLabel = count($varParts) > 0 ? implode(' / ', $varParts) : null;

        $sku = $item->variant_sku ?? ($item->parent_sku ?? '—');

        $stockInQty  = (int) ($item->stock_in_qty ?? 0);
        $stockInVal  = (float) ($item->stock_in_val ?? 0);
        $stockOutQty = (int) ($item->stock_out_qty ?? 0);
        $salesQty    = (int) ($item->sales_qty ?? 0);
        $salesRev    = (float) ($item->sales_rev ?? 0);
        $salesCogs   = (float) ($item->sales_cogs ?? 0);

        $grossProfit = max(0.00, $salesRev - $salesCogs);

        $rawImg = $item->thumbnail ?? ($item->image ?? null);
        $thumb = $resolveThumb($rawImg);

        $ledgerUrl = Route::has('admin.inventory.product.details')
            ? route('admin.inventory.product.details', array_filter([
                'id' => $item->product_id,
                'variant_id' => $item->variant_id ?? null
            ]))
            : url('/admin/inventory/product/' . $item->product_id . ($item->variant_id ? '?variant_id=' . $item->variant_id : ''));
    @endphp

    <tr class="align-middle">

        {{-- 1. Product ID --}}
        <td class="col-rp-id font-monospace text-nowrap">
            #{{ $item->product_id }}
        </td>

        {{-- 2. Product, Thumbnail & Attributes --}}
        <td class="col-rp-prod">
            <div class="d-flex align-items-center gap-2 min-w-0">

                <div
                    class="rp-product-thumb flex-shrink-0"
                    onclick="if(typeof zoomReportImage === 'function'){ zoomReportImage('{{ $thumb }}', '{{ addslashes($item->product_title ?? 'Product') }}'); }"
                    title="Zoom Preview"
                    role="button"
                    tabindex="0"
                    aria-label="Preview {{ $item->product_title ?? 'Product' }}"
                >
                    <img
                        src="{{ $thumb }}"
                        alt="{{ $item->product_title ?? 'Product' }}"
                        loading="lazy"
                        onerror="this.onerror=null;this.src='https://placehold.co/60x60?text=Item';"
                    >

                    <div class="rp-product-thumb-overlay">
                        <i class="fas fa-search-plus text-white" style="font-size: 11px;"></i>
                    </div>
                </div>

                <div class="min-w-0 flex-grow-1">
                    <strong
                        class="rp-product-name text-dark text-truncate"
                        title="{{ $item->product_title ?? 'Product' }}"
                    >
                        {{ $item->product_title }}
                    </strong>

                    <div class="d-flex flex-wrap align-items-center gap-1 mt-1">
                        @if($varLabel)
                            <span
                                class="rp-variant-badge font-monospace"
                                title="{{ $varLabel }}"
                            >
                                <i class="fas fa-layer-group me-1"></i>{{ $varLabel }}
                            </span>
                        @endif
                    </div>
                </div>

            </div>
        </td>

        {{-- 3. SKU --}}
        <td class="col-rp-sku font-monospace text-nowrap">
            <span
                class="rp-sku-badge"
                title="{{ $sku }}"
            >
                {{ $sku }}
            </span>
        </td>

        {{-- 4. Stock In Quantity --}}
        <td class="col-rp-inqty text-center text-nowrap font-monospace">
            <span class="rp-quantity in">
                {{ $stockInQty > 0 ? '+' . number_format($stockInQty) : '0' }}
            </span>
        </td>

        {{-- 5. Stock In Valuation --}}
        <td class="col-rp-inval text-end text-nowrap font-monospace">
            <span class="rp-money text-success">
                ৳{{ number_format($stockInVal, 2) }}
            </span>
        </td>

        {{-- 6. Stock Out Quantity --}}
        <td class="col-rp-outqty text-center text-nowrap font-monospace">
            <span class="rp-quantity out">
                {{ $stockOutQty > 0 ? '-' . number_format($stockOutQty) : '0' }}
            </span>
        </td>

        {{-- 7. Sales Quantity --}}
        <td class="col-rp-sqty text-center text-nowrap font-monospace">
            <span class="rp-quantity sales">
                {{ number_format($salesQty) }}
            </span>
        </td>

        {{-- 8. Revenue --}}
        <td class="col-rp-rev text-end text-nowrap font-monospace">
            <span class="rp-money text-dark fw-bold">
                ৳{{ number_format($salesRev, 2) }}
            </span>
        </td>

        {{-- 9. COGS --}}
        <td class="col-rp-cogs text-end text-nowrap font-monospace">
            <span class="rp-money text-secondary">
                ৳{{ number_format($salesCogs, 2) }}
            </span>
        </td>

        {{-- 10. Gross Profit --}}
        <td class="col-rp-profit text-end text-nowrap font-monospace">
            <span class="rp-profit {{ $grossProfit > 0 ? 'text-success' : 'text-muted' }}">
                ৳{{ number_format($grossProfit, 2) }}
            </span>
        </td>

        {{-- 11. Ledger Action --}}
        <td class="col-rp-action text-end text-nowrap">
            <a
                href="{{ $ledgerUrl }}"
                class="rp-ledger-btn"
                title="View Product Lifecycle"
            >
                <i class="fas fa-eye"></i>
                <span>Ledger</span>
            </a>
        </td>

    </tr>

@empty

    <tr>
        <td colspan="11" class="rp-empty-state">

            <div class="rp-empty-icon">
                <i class="fas fa-box-open"></i>
            </div>

            <span class="rp-empty-title">
                No Product Activity Found
            </span>

            <span class="rp-empty-description">
                No product activity or sales recorded in this selected period.
            </span>

        </td>
    </tr>

@endforelse