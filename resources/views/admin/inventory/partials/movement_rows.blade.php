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
       INVENTORY MOVEMENT LEDGER — MODERN TABLE
       Strict 5px border-radius
    ============================================= */

    .inventory-movement-table {
        --im-border: #e8edf4;
        --im-text: #263247;
        --im-muted: #7b8799;
        --im-primary: #4361ee;
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        vertical-align: middle;
    }

    .inventory-movement-table > tbody > tr > td {
        padding: 11px 9px;
        border-bottom: 1px solid var(--im-border);
        background: #fff;
        vertical-align: middle;
        transition: background .18s ease;
    }

    .inventory-movement-table > tbody > tr:last-child > td {
        border-bottom: 0;
    }

    .inventory-movement-table > tbody > tr:hover > td {
        background: #f8faff;
    }

    /* Column sizing */
    .inventory-movement-table .col-m-id {
        width: 65px;
        color: var(--im-muted);
        font-size: 10.5px;
        font-weight: 700;
    }

    .inventory-movement-table .col-m-date {
        min-width: 105px;
    }

    .inventory-movement-table .col-m-prod {
        min-width: 205px;
        max-width: 270px;
    }

    .inventory-movement-table .col-m-sku {
        min-width: 105px;
        max-width: 140px;
    }

    .inventory-movement-table .col-m-type {
        min-width: 125px;
    }

    .inventory-movement-table .col-m-change {
        min-width: 75px;
    }

    .inventory-movement-table .col-m-flow {
        min-width: 105px;
    }

    .inventory-movement-table .col-m-cost,
    .inventory-movement-table .col-m-imp {
        min-width: 110px;
    }

    .inventory-movement-table .col-m-ref {
        min-width: 145px;
        max-width: 190px;
    }

    .inventory-movement-table .col-m-user {
        min-width: 140px;
        max-width: 180px;
    }

    /* Product image */
    .inventory-movement-table .proof-thumb-box {
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

    .inventory-movement-table .proof-thumb-box:hover {
        border-color: #aabaff;
        box-shadow: 0 3px 10px rgba(67, 97, 238, .12);
    }

    .inventory-movement-table .proof-thumb-box img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 5px;
    }

    .inventory-movement-table .proof-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        background: rgba(25, 35, 55, .58);
        opacity: 0;
        transition: opacity .2s ease;
    }

    .inventory-movement-table .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    /* Product information */
    .inventory-movement-table .movement-product-name {
        display: block;
        max-width: 100%;
        color: var(--im-text);
        font-size: 11.5px;
        font-weight: 700;
        line-height: 1.4;
        text-decoration: none;
    }

    .inventory-movement-table .movement-product-name:hover {
        color: var(--im-primary) !important;
    }

    .inventory-movement-table .movement-variant {
        display: inline-block;
        max-width: 145px;
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

    /* Badges */
    .inventory-movement-table .movement-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        max-width: 100%;
        padding: 4px 7px;
        border-radius: 5px;
        font-size: 9.5px;
        font-weight: 700;
        line-height: 1.2;
        white-space: nowrap;
    }

    .inventory-movement-table .movement-sku-badge {
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

    /* Date */
    .inventory-movement-table .movement-date {
        display: block;
        color: #344054;
        font-size: 10.5px;
        font-weight: 700;
        line-height: 1.4;
    }

    .inventory-movement-table .movement-time {
        display: block;
        margin-top: 3px;
        color: var(--im-muted);
        font-size: 9.5px;
    }

    /* Quantity change */
    .inventory-movement-table .movement-quantity {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 35px;
        padding: 4px 6px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 800;
        font-variant-numeric: tabular-nums;
    }

    .inventory-movement-table .movement-quantity.in {
        background: #eaf8f0;
        color: #16834a;
    }

    .inventory-movement-table .movement-quantity.out {
        background: #fff0f0;
        color: #d73545;
    }

    .inventory-movement-table .movement-quantity.neutral {
        background: #f1f3f6;
        color: #778296;
    }

    /* Balance flow */
    .inventory-movement-table .movement-flow {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        color: #8490a1;
        font-size: 10.5px;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .inventory-movement-table .movement-flow strong {
        color: #263247;
        font-size: 11px;
        font-weight: 800;
    }

    /* Cost */
    .inventory-movement-table .movement-money {
        color: #687589;
        font-size: 10.5px;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .inventory-movement-table .movement-impact {
        font-size: 11.5px;
        font-weight: 800;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    /* Reference and proof */
    .inventory-movement-table .movement-proof-thumb {
        position: relative;
        width: 25px;
        height: 25px;
        min-width: 25px;
        overflow: hidden;
        border: 1px solid #e4e9f1;
        border-radius: 5px;
        background: #f5f7fb;
        cursor: zoom-in;
    }

    .inventory-movement-table .movement-proof-thumb img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
        border-radius: 5px;
    }

    .inventory-movement-table .movement-proof-thumb .proof-thumb-hover-overlay {
        border-radius: 5px;
    }

    .inventory-movement-table .movement-reference {
        display: block;
        max-width: 100%;
        color: #4058c7;
        font-size: 10.5px;
        font-weight: 800;
        text-decoration: none;
    }

    .inventory-movement-table .movement-source {
        display: block;
        margin-top: 3px;
        color: #8994a5;
        font-size: 8.5px;
        font-weight: 600;
    }

    /* User and notes */
    .inventory-movement-table .movement-creator {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #344054;
        font-size: 10.5px;
        font-weight: 700;
    }

    .inventory-movement-table .movement-notes {
        display: block;
        margin-top: 4px;
        color: #8490a1;
        font-size: 8.5px;
        line-height: 1.4;
    }

    /* Empty state */
    .inventory-movement-table .movement-empty-state {
        padding: 55px 15px !important;
        background: #fff;
        color: var(--im-muted);
        text-align: center;
    }

    .inventory-movement-table .movement-empty-icon {
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

    .inventory-movement-table .movement-empty-title {
        display: block;
        margin-bottom: 4px;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
    }

    .inventory-movement-table .movement-empty-description {
        color: #94a0b2;
        font-size: 11px;
    }

    /* Strict 5px radius */
    .inventory-movement-table,
    .inventory-movement-table *,
    .inventory-movement-table *::before,
    .inventory-movement-table *::after {
        border-radius: 5px;
    }

    /* Responsive */
    @media (max-width: 1199.98px) {
        .inventory-movement-table > tbody > tr > td {
            padding: 9px 7px;
        }

        .inventory-movement-table .col-m-prod {
            min-width: 190px;
        }
    }

    @media (max-width: 767.98px) {
        .inventory-movement-table > tbody > tr > td {
            padding: 9px 7px;
        }

        .inventory-movement-table .proof-thumb-box {
            width: 37px;
            height: 37px;
            min-width: 37px;
        }

        .inventory-movement-table .movement-product-name {
            font-size: 11px;
        }

        .inventory-movement-table .movement-money {
            font-size: 10px;
        }

        .inventory-movement-table .movement-impact {
            font-size: 10.5px;
        }
    }
</style>

@forelse($movements as $m)
    @php
        $thumb = $resolveThumb($m->product_thumbnail ?? null);

        $variantParts = array_filter([
            $m->variant_color ?? null,
            $m->variant_size ?? null
        ]);

        $hasVariant = count($variantParts) > 0;
        $variantText = $hasVariant ? implode(' / ', $variantParts) : null;

        $displaySku = $m->sku ?? ($m->variant_sku ?? ($m->parent_code ?? '—'));

        $costImpact = abs((int) ($m->quantity_change ?? 0)) * (float) ($m->unit_cost ?? 0);

        $proofRaw = $m->proof_attachment ?? (
            $m->document ?? (
                $m->proof ?? (
                    $m->attachment ?? null
                )
            )
        );

        $proofUrl = !empty($proofRaw) ? $resolveThumb($proofRaw, '') : null;

        $typeMeta = match($m->movement_type ?? 'adjustment') {
            'checkout_sale', 'order_processing_deduct' => [
                'label' => 'Online Sale',
                'class' => 'bg-primary-subtle text-primary border border-primary-subtle',
                'icon' => 'fa-globe'
            ],
            'pos_sale' => [
                'label' => 'POS Sale',
                'class' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                'icon' => 'fa-cash-register'
            ],
            'order_cancel' => [
                'label' => 'Cancel Restock',
                'class' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                'icon' => 'fa-ban'
            ],
            'order_return', 'customer_return' => [
                'label' => 'Return Restock',
                'class' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                'icon' => 'fa-rotate-left'
            ],
            'adjustment', 'stock_correction', 'inventory_audit' => [
                'label' => 'Adjustment',
                'class' => 'bg-dark-subtle text-dark border border-dark-subtle',
                'icon' => 'fa-sliders'
            ],
            'purchase_in', 'purchase_receive', 'purchase_receipt_in', 'opening_stock', 'other_addition' => [
                'label' => 'Purchase In',
                'class' => 'bg-success-subtle text-success border border-success-subtle',
                'icon' => 'fa-arrow-down'
            ],
            'exchange_in', 'supplier_replacement' => [
                'label' => 'Exchange In',
                'class' => 'bg-success-subtle text-success border border-success-subtle',
                'icon' => 'fa-right-left'
            ],
            'exchange_out' => [
                'label' => 'Exchange Out',
                'class' => 'bg-danger-subtle text-danger border border-danger-subtle',
                'icon' => 'fa-right-left'
            ],
            'purchase_return', 'supplier_return' => [
                'label' => 'Return (RTV)',
                'class' => 'bg-danger-subtle text-danger border border-danger-subtle',
                'icon' => 'fa-truck-arrow-right'
            ],
            'damage_loss', 'damage_out', 'expired_out', 'lost_stock', 'adjustment_out', 'other_removal', 'internal_use' => [
                'label' => 'Damage / Loss',
                'class' => 'bg-danger-subtle text-danger border border-danger-subtle',
                'icon' => 'fa-triangle-exclamation'
            ],
            default => [
                'label' => ucwords(str_replace('_', ' ', $m->movement_type ?? 'movement')),
                'class' => 'bg-light text-dark border',
                'icon' => 'fa-circle-dot'
            ],
        };

        $productEditUrl = Route::has('admin.product.edit') && !empty($m->product_id)
            ? route('admin.product.edit', $m->product_id)
            : 'javascript:void(0);';
    @endphp

    <tr id="movement-row-{{ $m->id }}" class="align-middle">

        {{-- 1. Audit Log ID --}}
        <td class="col-m-id font-monospace text-center text-nowrap">
            #{{ $m->id }}
        </td>

        {{-- 2. Transaction Timestamp --}}
        <td class="col-m-date text-nowrap">
            <span class="movement-date font-monospace">
                {{ date('d M Y', strtotime($m->created_at)) }}
            </span>

            <small class="movement-time font-monospace">
                {{ date('h:i A', strtotime($m->created_at)) }}
            </small>
        </td>

        {{-- 3. Product & Attributes --}}
        <td class="col-m-prod">
            <div class="d-flex align-items-center gap-2 min-w-0">

                <div
                    class="proof-thumb-box flex-shrink-0"
                    onclick="if(typeof zoomInvImage === 'function'){ zoomInvImage('{{ $thumb }}', '{{ addslashes($m->product_title ?? 'Product') }}'); }"
                    title="Zoom Preview"
                    role="button"
                    tabindex="0"
                    aria-label="Preview {{ $m->product_title ?? 'Product' }}"
                >
                    <img
                        src="{{ $thumb }}"
                        alt="{{ $m->product_title ?? 'Product' }}"
                        loading="lazy"
                        onerror="this.onerror=null;this.src='https://placehold.co/60x60?text=Item';"
                    >

                    <div class="proof-thumb-hover-overlay">
                        <i class="fas fa-search-plus text-white" style="font-size: 11px;"></i>
                    </div>
                </div>

                <div class="min-w-0 flex-grow-1">
                    <a
                        href="{{ $productEditUrl }}"
                        class="movement-product-name text-truncate"
                        title="{{ $m->product_title ?? 'Product #' . $m->product_id }}"
                    >
                        {{ $m->product_title ?? 'Product #' . $m->product_id }}
                    </a>

                    @if($hasVariant)
                        <span
                            class="movement-variant font-monospace"
                            title="{{ $variantText }}"
                        >
                            <i class="fas fa-layer-group me-1"></i>{{ $variantText }}
                        </span>
                    @endif
                </div>

            </div>
        </td>

        {{-- 4. SKU --}}
        <td class="col-m-sku font-monospace text-nowrap">
            <span
                class="movement-sku-badge"
                title="{{ $displaySku }}"
            >
                {{ $displaySku }}
            </span>
        </td>

        {{-- 5. Movement Type Badge --}}
        <td class="col-m-type text-nowrap">
            <span class="movement-badge {{ $typeMeta['class'] }}">
                <i class="fas {{ $typeMeta['icon'] }}"></i>
                {{ $typeMeta['label'] }}
            </span>
        </td>

        {{-- 6. Change Indicator (+ / -) --}}
        <td class="col-m-change text-center text-nowrap font-monospace">
            @if($m->quantity_change > 0)
                <span class="movement-quantity in">
                    +{{ $m->quantity_change }}
                </span>
            @elseif($m->quantity_change < 0)
                <span class="movement-quantity out">
                    {{ $m->quantity_change }}
                </span>
            @else
                <span class="movement-quantity neutral">
                    0
                </span>
            @endif
        </td>

        {{-- 7. Balance Flow (Before -> After) --}}
        <td class="col-m-flow text-center text-nowrap font-monospace">
            <span class="movement-flow">
                <span>{{ $m->quantity_before }}</span>

                <i class="fas fa-arrow-right-long text-secondary opacity-75" style="font-size: 9px;"></i>

                <strong>{{ $m->quantity_after }}</strong>
            </span>
        </td>

        {{-- 8. Unit Cost --}}
        <td class="col-m-cost text-end text-nowrap font-monospace">
            <span class="movement-money">
                ৳{{ number_format((float) ($m->unit_cost ?? 0), 2) }}
            </span>
        </td>

        {{-- 9. Total Cost Impact --}}
        <td class="col-m-imp text-end text-nowrap font-monospace">
            <strong class="movement-impact {{ $m->quantity_change < 0 ? 'text-danger' : 'text-success' }}">
                ৳{{ number_format($costImpact, 2) }}
            </strong>
        </td>

        {{-- 10. Reference ID & Proof Voucher --}}
        <td class="col-m-ref text-nowrap">
            <div class="d-flex align-items-center gap-2 min-w-0">

                @if($proofUrl)
                    <div
                        class="movement-proof-thumb flex-shrink-0"
                        onclick="if(typeof zoomProofAttachment === 'function'){ zoomProofAttachment('{{ $proofUrl }}', 'Voucher #{{ $m->reference_id ?? $m->id }}'); }"
                        title="Zoom Voucher Proof"
                        role="button"
                        tabindex="0"
                        aria-label="Preview voucher proof"
                    >
                        <img
                            src="{{ $proofUrl }}"
                            alt="Voucher"
                            loading="lazy"
                            onerror="this.onerror=null;this.src='https://placehold.co/40x40?text=Slip';"
                        >

                        <div class="proof-thumb-hover-overlay">
                            <i class="fas fa-search-plus text-white" style="font-size: 8px;"></i>
                        </div>
                    </div>
                @endif

                <div class="min-w-0 flex-grow-1">
                    @if(!empty($m->reference_id))
                        <span
                            class="movement-reference font-monospace text-truncate"
                            title="{{ $m->reference_id }}"
                        >
                            {{ $m->reference_id }}
                        </span>
                    @else
                        <span class="movement-reference text-muted font-monospace">
                            —
                        </span>
                    @endif

                    <small class="movement-source text-uppercase text-truncate font-monospace">
                        Src: {{ $m->source ?? 'sys' }}
                    </small>
                </div>

            </div>
        </td>

        {{-- 11. Initiator & Notes --}}
        <td class="col-m-user text-nowrap">
            <div class="movement-creator min-w-0">
                <i class="fas fa-user-circle text-secondary opacity-75 flex-shrink-0"></i>

                <span
                    class="text-truncate"
                    title="{{ $m->creator_name ?? 'System Automated' }}"
                >
                    {{ $m->creator_name ?? 'System Automated' }}
                </span>
            </div>

            @if(!empty($m->notes))
                <small
                    class="movement-notes text-truncate font-monospace"
                    title="{{ $m->notes }}"
                >
                    <i class="fas fa-comment-dots me-1"></i>{{ $m->notes }}
                </small>
            @endif
        </td>

    </tr>

@empty

    <tr>
        <td colspan="11" class="movement-empty-state">

            <div class="movement-empty-icon">
                <i class="fas fa-box-open"></i>
            </div>

            <span class="movement-empty-title">
                No Stock Movement Records Found
            </span>

            <span class="movement-empty-description">
                No stock movement logs found matching the selected criteria.
            </span>

        </td>
    </tr>

@endforelse