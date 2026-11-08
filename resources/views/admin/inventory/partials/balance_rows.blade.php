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
    /* ============================================
       STOCK BALANCE TABLE — MODERN UI
       Strict 5px radius
    ============================================ */

    .stock-balance-table {
        --sb-border: #e9edf3;
        --sb-muted: #748094;
        --sb-text: #263247;
        --sb-primary: #4361ee;
        --sb-surface: #f8faff;
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        vertical-align: middle;
    }

    .stock-balance-table > tbody > tr > td {
        padding: 11px 9px;
        border-bottom: 1px solid var(--sb-border);
        vertical-align: middle;
        background: #fff;
        transition: background .18s ease;
    }

    .stock-balance-table > tbody > tr:last-child > td {
        border-bottom: 0;
    }

    .stock-balance-table > tbody > tr:hover > td {
        background: #f8faff;
    }

    .stock-balance-table .col-b-id {
        width: 75px;
        color: var(--sb-muted);
        font-size: 11px;
        font-weight: 600;
    }

    .stock-balance-table .col-b-prod {
        min-width: 205px;
        max-width: 290px;
    }

    .stock-balance-table .col-b-type {
        width: 85px;
    }

    .stock-balance-table .col-b-sku {
        min-width: 105px;
        max-width: 145px;
    }

    .stock-balance-table .col-b-stock {
        min-width: 85px;
    }

    .stock-balance-table .col-b-cost,
    .stock-balance-table .col-b-price {
        min-width: 105px;
    }

    .stock-balance-table .col-b-val {
        min-width: 120px;
    }

    .stock-balance-table .col-b-stat {
        min-width: 105px;
    }

    .stock-balance-table .col-b-act {
        min-width: 95px;
    }

    .stock-balance-table .proof-thumb-box {
        position: relative;
        width: 43px;
        height: 43px;
        min-width: 43px;
        overflow: hidden;
        border: 1px solid #e7ebf2;
        border-radius: 5px;
        background: #f5f7fb;
        cursor: zoom-in;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .stock-balance-table .proof-thumb-box:hover {
        border-color: #aabaff;
        box-shadow: 0 3px 10px rgba(67, 97, 238, .12);
    }

    .stock-balance-table .proof-thumb-box img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
        border-radius: 5px;
    }

    .stock-balance-table .proof-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(25, 35, 55, .55);
        opacity: 0;
        transition: opacity .2s ease;
        border-radius: 5px;
    }

    .stock-balance-table .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    .stock-balance-table .product-name {
        color: var(--sb-text);
        font-size: 12px;
        font-weight: 700;
        line-height: 1.4;
        max-width: 100%;
    }

    .stock-balance-table .product-variant {
        display: inline-block;
        max-width: 155px;
        margin-top: 4px;
        padding: 3px 6px;
        overflow: hidden;
        border-radius: 5px;
        font-size: 9.5px;
        font-weight: 600;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
    }

    .stock-balance-table .stock-type-badge,
    .stock-balance-table .stock-sku-badge,
    .stock-balance-table .stock-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        max-width: 100%;
        padding: 4px 7px;
        border-radius: 5px;
        font-size: 9.5px;
        font-weight: 700;
        line-height: 1.2;
        white-space: nowrap;
    }

    .stock-balance-table .stock-sku-badge {
        overflow: hidden;
        font-size: 10px;
        text-overflow: ellipsis;
    }

    .stock-balance-table .stock-number {
        font-size: 13px;
        font-weight: 800;
        letter-spacing: -.2px;
    }

    .stock-balance-table .stock-money {
        font-size: 11px;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .stock-balance-table .stock-valuation {
        color: #263247;
        font-size: 12px;
        font-weight: 800;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .stock-balance-table .stock-ledger-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 29px;
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

    .stock-balance-table .stock-ledger-btn:hover {
        border-color: #c7d2fe;
        background: #eef2ff;
        color: #3451d1;
        box-shadow: 0 2px 7px rgba(67, 97, 238, .09);
    }

    .stock-balance-table .stock-ledger-btn i {
        color: var(--sb-primary);
        font-size: 10px;
    }

    .stock-balance-table .empty-stock-state {
        padding: 55px 15px !important;
        color: var(--sb-muted);
        background: #fff;
        text-align: center;
    }

    .stock-balance-table .empty-stock-state .empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 54px;
        height: 54px;
        margin: 0 auto 13px;
        border: 1px solid #e9edf5;
        border-radius: 5px;
        background: #f7f9fc;
        color: #a2adbd;
        font-size: 22px;
    }

    .stock-balance-table .empty-stock-state .empty-title {
        display: block;
        margin-bottom: 4px;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
    }

    .stock-balance-table .empty-stock-state .empty-description {
        color: #94a0b2;
        font-size: 11px;
    }

    /* Strict radius for all nested elements */
    .stock-balance-table,
    .stock-balance-table *,
    .stock-balance-table *::before,
    .stock-balance-table *::after {
        border-radius: 5px;
    }

    /* Responsive table */
    @media (max-width: 1199.98px) {
        .stock-balance-table > tbody > tr > td {
            padding: 9px 7px;
        }

        .stock-balance-table .col-b-prod {
            min-width: 190px;
        }
    }

    @media (max-width: 767.98px) {
        .stock-balance-table > tbody > tr > td {
            padding: 9px 7px;
        }

        .stock-balance-table .proof-thumb-box {
            width: 38px;
            height: 38px;
            min-width: 38px;
        }

        .stock-balance-table .product-name {
            font-size: 11px;
        }

        .stock-balance-table .stock-number {
            font-size: 12px;
        }

        .stock-balance-table .stock-money,
        .stock-balance-table .stock-valuation {
            font-size: 10.5px;
        }
    }
</style>

@forelse($items as $item)
    @php
        $thumb = $resolveThumb($item->thumbnail ?? null);
        $stock = (int) ($item->stock ?? 0);
        $alertQty = (int) ($item->alert_quantity ?? 5);
        $purchaseVal = $stock * (float) ($item->purchase_cost ?? 0);

        $statusMeta = match(true) {
            $stock <= 0 => [
                'label' => 'Out of Stock',
                'class' => 'bg-danger-subtle text-danger border border-danger-subtle',
                'icon' => 'fa-circle-xmark'
            ],
            $stock <= $alertQty => [
                'label' => 'Low Stock',
                'class' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                'icon' => 'fa-triangle-exclamation'
            ],
            default => [
                'label' => 'In Stock',
                'class' => 'bg-success-subtle text-success border border-success-subtle',
                'icon' => 'fa-circle-check'
            ],
        };

        $ledgerUrl = Route::has('admin.inventory.product.details')
            ? route('admin.inventory.product.details', array_filter([
                'id' => $item->product_id,
                'variant_id' => $item->variant_id ?? null
            ]))
            : url('/admin/inventory/product/' . $item->product_id . ($item->variant_id ? '?variant_id=' . $item->variant_id : ''));
    @endphp

    <tr class="align-middle">

        {{-- 1. Product ID --}}
        <td class="font-monospace text-muted text-nowrap col-b-id">
            #{{ $item->product_id }}
        </td>

        {{-- 2. Product Title & Attributes --}}
        <td class="col-b-prod">
            <div class="d-flex align-items-center gap-2 min-w-0">

                <div
                    class="proof-thumb-box"
                    onclick="if(typeof zoomBalanceImage === 'function'){ zoomBalanceImage('{{ $thumb }}', '{{ addslashes($item->product_title) }}'); }"
                    title="Zoom Preview"
                    role="button"
                    tabindex="0"
                    aria-label="Preview {{ $item->product_title }}"
                >
                    <img
                        src="{{ $thumb }}"
                        alt="{{ $item->product_title }}"
                        loading="lazy"
                        onerror="this.onerror=null;this.src='https://placehold.co/60x60?text=Item';"
                    >

                    <div class="proof-thumb-hover-overlay">
                        <i class="fas fa-search-plus text-white" style="font-size: 11px;"></i>
                    </div>
                </div>

                <div class="min-w-0 flex-grow-1">
                    <strong
                        class="product-name d-block text-truncate"
                        title="{{ $item->product_title }}"
                    >
                        {{ $item->product_title }}
                    </strong>

                    @if(!empty($item->variant_attributes))
                        <span
                            class="product-variant bg-primary-subtle text-primary border border-primary-subtle font-monospace"
                            title="{{ $item->variant_attributes }}"
                        >
                            <i class="fas fa-layer-group me-1"></i>{{ $item->variant_attributes }}
                        </span>
                    @endif
                </div>

            </div>
        </td>

        {{-- 3. Type --}}
        <td class="text-nowrap col-b-type">
            @if(($item->item_type ?? '') === 'variant')
                <span class="stock-type-badge bg-primary-subtle text-primary border border-primary-subtle font-monospace">
                    <i class="fas fa-layer-group"></i>
                    Variant
                </span>
            @else
                <span class="stock-type-badge bg-light text-secondary border font-monospace">
                    <i class="fas fa-box"></i>
                    Simple
                </span>
            @endif
        </td>

        {{-- 4. SKU --}}
        <td class="font-monospace text-nowrap col-b-sku">
            <span
                class="stock-sku-badge bg-light text-dark border"
                title="{{ $item->sku ?? '—' }}"
            >
                {{ $item->sku ?? '—' }}
            </span>
        </td>

        {{-- 5. Available Stock --}}
        <td class="text-center text-nowrap font-monospace col-b-stock">
            <strong class="stock-number {{ $stock <= 0 ? 'text-danger' : ($stock <= $alertQty ? 'text-warning-emphasis' : 'text-success') }}">
                {{ number_format($stock) }}
            </strong>
        </td>

        {{-- 6. Unit Cost --}}
        <td class="text-end text-nowrap font-monospace text-muted col-b-cost">
            <span class="stock-money">
                ৳{{ number_format((float) ($item->purchase_cost ?? 0), 2) }}
            </span>
        </td>

        {{-- 7. Selling Price --}}
        <td class="text-end text-nowrap font-monospace text-dark col-b-price">
            <span class="stock-money">
                ৳{{ number_format((float) ($item->selling_price ?? 0), 2) }}
            </span>
        </td>

        {{-- 8. Total Valuation --}}
        <td class="text-end text-nowrap font-monospace col-b-val">
            <strong class="stock-valuation">
                ৳{{ number_format($purchaseVal, 2) }}
            </strong>
        </td>

        {{-- 9. Status --}}
        <td class="text-center text-nowrap col-b-stat">
            <span class="stock-status-badge {{ $statusMeta['class'] }}">
                <i class="fas {{ $statusMeta['icon'] }}"></i>
                {{ $statusMeta['label'] }}
            </span>
        </td>

        {{-- 10. Ledger Action --}}
        <td class="text-end text-nowrap col-b-act">
            <a
                href="{{ $ledgerUrl }}"
                class="stock-ledger-btn"
                title="View Detailed Stock Lifecycle"
            >
                <i class="fas fa-eye"></i>
                <span>Ledger</span>
            </a>
        </td>

    </tr>

@empty

    <tr>
        <td colspan="10" class="empty-stock-state">
            <div class="empty-icon">
                <i class="fas fa-box-open"></i>
            </div>

            <span class="empty-title">
                No Stock Balance Items Found
            </span>

            <span class="empty-description">
                No stock balance items found matching current criteria.
            </span>
        </td>
    </tr>

@endforelse