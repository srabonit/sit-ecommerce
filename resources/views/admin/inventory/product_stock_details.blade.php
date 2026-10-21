@extends('layouts.admin')

@section('title', 'Stock Ledger - ' . ($product->title ?? 'Product Details') . ' - ' . config('app.name', 'Shop Now BD'))

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

    $thumb = $resolveThumb($product->thumbnail ?? null);
    $isVariable = ($product->product_struct === 'variable') || ($variants->count() > 0);
    $totalStock = (int) ($product->stock ?? 0);
    $parentCost = (float) ($product->purchase_cost ?? ($product->purchase_price ?? 0.00));
    $sellingPrice = (float) ($product->sale_price ?? ($product->regular_price ?? 0.00));

    // Zero double-counting inventory valuation calculation
    if ($isVariable) {
        $totalValuation = $variants->sum(fn($v) => (int)$v->stock * (float)($v->purchase_cost ?? ($v->purchase_price ?? 0)));
        $effectiveStock = $variants->sum('stock');
    } else {
        $totalValuation = $totalStock * $parentCost;
        $effectiveStock = $totalStock;
    }

    $lifetimeIn   = (int) ($aggregates->total_in ?? 0);
    $lifetimeOut  = (int) ($aggregates->total_out ?? 0);
    $lifetimeSold = (int) ($aggregates->total_sold ?? 0);
    $lifetimeRev  = (float) ($aggregates->total_revenue ?? 0);

    $selectedVariantId = $selectedVariantId ?? (request('variant_id') ? (int)request('variant_id') : null);
@endphp

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius Across All Controls */
    #prodLedgerRoot .btn,
    #prodLedgerRoot .btn-sm,
    #prodLedgerRoot button,
    #prodLedgerRoot .form-control,
    #prodLedgerRoot .form-select,
    #prodLedgerRoot .badge,
    #prodLedgerRoot .alert,
    #prodLedgerRoot .card,
    #prodLedgerRoot .metric-card,
    #prodLedgerRoot .metric-icon-box,
    #prodLedgerRoot .product-thumb-box,
    #prodLedgerRoot .page-link,
    #prodLedgerRoot .page-item span,
    #prodLedgerRoot .collapse-toggle-btn,
    .ledger-modal-scope .modal-content,
    .ledger-modal-scope .btn,
    .ledger-modal-scope .form-control {
        border-radius: 5px !important;
    }

    /* Safe Monospace & Bengali Digit Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Enterprise Surface Cards */
    #prodLedgerRoot .card {
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }

    #prodLedgerRoot .metric-card {
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        padding: 12px 14px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    #prodLedgerRoot .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }

    /* Compact Button Styling */
    .btn-compact {
        font-size: 11px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    /* Collapsible Trigger Buttons & Rotating Chevron */
    .collapse-toggle-btn {
        width: 24px;
        height: 24px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        color: #475569;
        transition: all 0.2s ease;
    }
    .collapse-toggle-btn:hover {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }
    .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }
    .collapse-icon {
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        font-size: 10px;
    }

    /* Gradient Palettes for Metric Cards */
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-color: #bbf7d0; }
    .metric-card-red    { background: linear-gradient(180deg, #ffffff 0%, #fef2f2 100%); border-color: #fecaca; }
    .metric-card-blue   { background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%); border-color: #bae6fd; }
    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-color: #fde68a; }

    .metric-icon-box {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }

    /* Global Media Chooser Surface Box for Product Thumbnail */
    .product-thumb-box {
        width: 64px;
        height: 64px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        position: relative;
    }
    .product-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .product-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.15s ease;
        border-radius: 5px !important;
    }
    .product-thumb-box:hover .product-thumb-hover-overlay {
        opacity: 1;
    }

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .ledger-table {
        width: 100%;
        margin-bottom: 0;
    }
    .ledger-table thead th {
        font-size: 10px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 8px;
        white-space: nowrap;
    }
    .ledger-table tbody td {
        padding: 8px 8px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        font-size: 11px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .ledger-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .ledger-movements-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .ledger-movements-table th,
        .ledger-movements-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 10 Columns Proportional Distribution Summing to Exactly 100% */
        .col-lm-id     { width: 5% !important; }
        .col-lm-date   { width: 11% !important; }
        .col-lm-attr   { width: 12% !important; }
        .col-lm-type   { width: 11% !important; }
        .col-lm-change { width: 6% !important; }
        .col-lm-flow   { width: 9% !important; }
        .col-lm-cost   { width: 10% !important; }
        .col-lm-imp    { width: 10% !important; }
        .col-lm-ref    { width: 13% !important; }
        .col-lm-user   { width: 13% !important; }
    }

    .movement-badge {
        font-size: 9px;
        font-weight: 700;
        padding: 2px 6px;
        letter-spacing: 0.2px;
        text-transform: uppercase;
        border-radius: 5px !important;
    }

    /* Bulletproof Responsive Pagination */
    .card-footer nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }
    .card-footer nav > div.d-sm-none,
    .card-footer nav > .d-sm-none {
        display: none !important;
    }
    .card-footer nav > div.d-none,
    .card-footer nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .card-footer nav p,
    .card-footer nav .small.text-muted,
    .card-footer nav div:has(> p),
    .card-footer nav > div.d-none > div:first-child,
    .card-footer nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }
    .card-footer .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 3px !important;
        padding: 0 !important;
        list-style: none !important;
    }
    .card-footer .page-item {
        display: inline-flex !important;
    }
    .card-footer .page-item .page-link,
    .card-footer .page-item span {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 25px !important;
        height: 25px !important;
        padding: 0 6px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        color: #475569 !important;
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 5px !important;
        text-decoration: none !important;
        line-height: 1 !important;
        transition: all 0.12s ease !important;
    }
    .card-footer .page-item .page-link:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }
    .card-footer .page-item.active .page-link,
    .card-footer .page-item.active span {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700 !important;
    }
    .card-footer .page-item.disabled .page-link,
    .card-footer .page-item.disabled span {
        opacity: 0.45 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }
    .card-footer nav svg {
        width: 10px !important;
        height: 10px !important;
    }

    /* Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .ledger-header h5 {
            font-size: 16px !important;
        }
        .header-actions-wrap {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .header-actions-wrap .btn {
            width: 100%;
            padding: 8px 6px !important;
            font-size: 11px !important;
            justify-content: center;
        }
        #prodLedgerRoot .card {
            padding: 12px !important;
            margin-bottom: 12px !important;
        }
        .metric-card {
            padding: 10px !important;
            min-height: 82px !important;
        }
        .metric-card h5 {
            font-size: 13.5px !important;
        }
        .master-details-wrap {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 12px !important;
        }
        .master-stats-wrap {
            width: 100%;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 8px !important;
        }
        .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .ledger-table {
            min-width: 760px !important;
            table-layout: auto !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="prodLedgerRoot">

    <!-- 1. Header & Quick Actions (Original Clean Light Theme) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 ledger-header">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <a href="{{ route('admin.inventory.balance') }}" class="btn btn-sm btn-light border btn-compact text-secondary shadow-xs">
                    <i class="fas fa-arrow-left me-1"></i> Stock Balance
                </a>
                <span class="text-muted small">|</span>
                <span class="badge bg-light text-secondary border font-monospace bn-digit px-2 py-1" style="font-size: 10px;">ID: #{{ $product->id }}</span>
                @if($isVariable)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1" style="font-size: 10px;">Variable ({{ $variants->count() }} Variants)</span>
                @else
                    <span class="badge bg-light text-dark border font-monospace px-2 py-1" style="font-size: 10px;">Simple Product</span>
                @endif
            </div>
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-box-open text-primary"></i>
                <span>{{ $product->title }}</span>
            </h5>
        </div>
        <div class="header-actions-wrap d-flex flex-wrap gap-2">
            <a href="{{ route('admin.inventory.stock_in') }}" class="btn btn-sm btn-success px-3 py-2 shadow-xs fw-bold btn-compact">
                <i class="fas fa-arrow-down-long me-1"></i> <span>Stock In</span>
            </a>
            <a href="{{ route('admin.inventory.stock_out') }}" class="btn btn-sm btn-danger px-3 py-2 shadow-xs fw-bold btn-compact">
                <i class="fas fa-arrow-up-long me-1"></i> <span>Stock Out</span>
            </a>
            <a href="{{ route('admin.product.edit', $product->id) }}" class="btn btn-sm btn-outline-secondary px-3 py-2 shadow-xs btn-compact">
                <i class="fas fa-pen-to-square me-1"></i> <span>Edit Catalog</span>
            </a>
        </div>
    </div>

    <!-- 2. Master Product Details Card with Global Media Chooser Integration -->
    <div class="card p-3 mb-3">
        <div class="d-flex align-items-center justify-content-between master-details-wrap gap-3">
            <div class="d-flex align-items-center gap-3 min-w-0">
                <!-- Global Media Chooser Thumbnail Surface -->
                <div class="position-relative flex-shrink-0">
                    <div class="product-thumb-box shadow-xs" onclick="zoomProductImage()" title="Click to Zoom Image">
                        <img id="productMasterThumb" src="{{ $thumb }}" alt="{{ $product->title }}" onerror="this.src='https://placehold.co/68x68?text=Item';">
                        <div class="product-thumb-hover-overlay">
                            <i class="fas fa-search-plus text-white" style="font-size: 11px;"></i>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-light border btn-compact w-100 mt-1 py-0 px-1 shadow-xs" style="font-size: 9px !important; height: 20px;" onclick="chooseProductMedia()" title="Change Image from Media Library">
                        <i class="fas fa-photo-film me-1 text-primary"></i> Media
                    </button>
                </div>

                <div class="min-w-0 flex-grow-1">
                    <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 14px; line-height: 1.3;" title="{{ $product->title }}">
                        {{ $product->title }}
                    </h6>
                    <div class="small text-muted font-monospace bn-digit mb-1" style="font-size: 11px;">
                        Master SKU: <strong class="text-dark">{{ $product->product_code ?? ($product->sku ?? '—') }}</strong> | 
                        Barcode: <strong class="text-secondary">{{ $product->barcode ?? '—' }}</strong>
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        <span class="badge bg-light text-dark border bn-digit font-monospace px-2 py-0" style="font-size: 10px;">
                            Selling Price: ৳{{ number_format($sellingPrice, 2) }}
                        </span>
                        @if(!$isVariable)
                            <span class="badge bg-light text-dark border bn-digit font-monospace px-2 py-0" style="font-size: 10px;">
                                Unit Cost: ৳{{ number_format($parentCost, 2) }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Total Available Stock & Valuation -->
            <div class="d-flex flex-wrap align-items-center gap-3 master-stats-wrap">
                <div class="text-start text-md-end">
                    <small class="text-muted d-block font-monospace" style="font-size: 11px;">AVAILABLE STOCK</small>
                    <h4 class="fw-bold mb-0 {{ $effectiveStock <= 0 ? 'text-danger' : 'text-success' }} bn-digit font-monospace" style="font-size: 1.35rem;">
                        {{ number_format($effectiveStock) }} <span style="font-size: 11px;" class="fw-normal text-muted">Units</span>
                    </h4>
                </div>
                <div class="vr d-none d-md-block" style="height: 38px;"></div>
                <div class="text-start text-md-end">
                    <small class="text-muted d-block font-monospace" style="font-size: 11px;">INVENTORY VALUATION</small>
                    <h4 class="fw-bold mb-0 text-primary bn-digit font-monospace" style="font-size: 1.35rem;">
                        ৳ {{ number_format($totalValuation, 2) }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Lifetime Stock & Sales Aggregates (Adaptive 2x2 Grid on Mobile) -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="metric-card metric-card-green">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="min-w-0 pe-1">
                        <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 10px;">Lifetime Stock In</small>
                        <h5 class="fw-bold mb-0 text-success bn-digit font-monospace mt-1">+{{ number_format($lifetimeIn) }} <span style="font-size: 10px;">Units</span></h5>
                    </div>
                    <div class="metric-icon-box bg-success text-white shadow-xs">
                        <i class="fas fa-arrow-down-long"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="metric-card metric-card-red">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="min-w-0 pe-1">
                        <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 10px;">Lifetime Stock Out</small>
                        <h5 class="fw-bold mb-0 text-danger bn-digit font-monospace mt-1">-{{ number_format($lifetimeOut) }} <span style="font-size: 10px;">Units</span></h5>
                    </div>
                    <div class="metric-icon-box bg-danger text-white shadow-xs">
                        <i class="fas fa-arrow-up-long"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="metric-card metric-card-blue">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="min-w-0 pe-1">
                        <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 10px;">Total Units Sold</small>
                        <h5 class="fw-bold mb-0 text-primary bn-digit font-monospace mt-1">{{ number_format($lifetimeSold) }} <span style="font-size: 10px;">Units</span></h5>
                    </div>
                    <div class="metric-icon-box bg-primary text-white shadow-xs">
                        <i class="fas fa-cart-shopping"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="metric-card metric-card-amber">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="min-w-0 pe-1">
                        <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 10px;">Sales Revenue</small>
                        <h5 class="fw-bold mb-0 text-dark bn-digit font-monospace mt-1">৳ {{ number_format($lifetimeRev, 2) }}</h5>
                    </div>
                    <div class="metric-icon-box text-dark shadow-xs" style="background-color: #f59e0b;">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Variant Inventory Breakdown (Variable Products Only - Collapsible) -->
    @if($isVariable)
        <div class="card p-0 mb-3 overflow-hidden shadow-xs">
            <div class="p-2 px-3 border-bottom d-flex justify-content-between align-items-center bg-white">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13px;">
                    <i class="fas fa-layer-group text-primary"></i>
                    <span>Variant-wise Stock &amp; Valuation Matrix</span>
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-muted border font-monospace bn-digit px-2 py-1" style="font-size: 10px;">
                        {{ $variants->count() }} Variants
                    </span>
                    <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseVariantMatrix" aria-expanded="true" aria-controls="collapseVariantMatrix" title="Toggle Variant Matrix">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseVariantMatrix">
                <div class="table-responsive pc-fit-container">
                    <table class="table align-middle mb-0 ledger-table">
                        <thead>
                            <tr>
                                <th class="bn-digit ps-3">Variant ID</th>
                                <th>Attributes (Color / Size)</th>
                                <th>Variant SKU</th>
                                <th class="text-center">Available Stock</th>
                                <th class="text-end">Unit Cost (WAC)</th>
                                <th class="text-end">Selling Price</th>
                                <th class="text-end">Inventory Value</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($variants as $v)
                                @php
                                    $vStock = (int) ($v->stock ?? 0);
                                    $vCost  = (float) ($v->purchase_cost ?? ($v->purchase_price ?? 0.00));
                                    $vPrice = (float) ($v->regular_price ?? $sellingPrice);
                                    $vVal   = $vStock * $vCost;
                                    $vAlert = (int) ($v->alert_quantity ?? 5);

                                    $vStatus = match(true) {
                                        $vStock <= 0      => ['label' => 'Out of Stock', 'class' => 'bg-danger-subtle text-danger border-danger-subtle'],
                                        $vStock <= $vAlert=> ['label' => 'Low Stock',    'class' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle'],
                                        default           => ['label' => 'In Stock',     'class' => 'bg-success-subtle text-success border-success-subtle'],
                                    };

                                    $attrParts = array_filter([$v->color, $v->size]);
                                    $attrLabel = count($attrParts) > 0 ? implode(' / ', $attrParts) : ($v->name ?? ('Variant #' . $v->id));
                                    $isActiveVariant = ($selectedVariantId === (int)$v->id);
                                @endphp
                                <tr class="{{ $isActiveVariant ? 'table-primary' : '' }}">
                                    <td class="font-monospace bn-digit fw-semibold text-secondary ps-3" style="font-size: 11px;">
                                        #{{ $v->id }}
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-primary border font-monospace text-truncate d-inline-block px-2 py-1" style="font-size: 10px; max-width: 140px;" title="{{ $attrLabel }}">
                                            <i class="fas fa-tag me-1 text-muted"></i>{{ $attrLabel }}
                                        </span>
                                    </td>
                                    <td class="font-monospace bn-digit">
                                        <span class="badge bg-light text-dark border px-2 py-0 text-truncate d-inline-block" style="font-size: 10px; max-width: 120px;" title="{{ $v->product_code ?? ($v->sku ?? '—') }}">
                                            {{ $v->product_code ?? ($v->sku ?? '—') }}
                                        </span>
                                    </td>
                                    <td class="text-center font-monospace bn-digit" style="font-size: 12px;">
                                        <strong class="{{ $vStock <= 0 ? 'text-danger' : ($vStock <= $vAlert ? 'text-warning-emphasis' : 'text-success') }}">
                                            {{ number_format($vStock) }}
                                        </strong>
                                    </td>
                                    <td class="text-end font-monospace bn-digit text-muted" style="font-size: 11px;">
                                        ৳ {{ number_format($vCost, 2) }}
                                    </td>
                                    <td class="text-end font-monospace bn-digit text-dark" style="font-size: 11px;">
                                        ৳ {{ number_format($vPrice, 2) }}
                                    </td>
                                    <td class="text-end font-monospace bn-digit fw-bold text-dark" style="font-size: 11.5px;">
                                        ৳ {{ number_format($vVal, 2) }}
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <span class="badge border {{ $vStatus['class'] }}" style="font-size: 9px; padding: 2px 6px;">
                                            {{ $vStatus['label'] }}
                                        </span>
                                    </td>
                                    <td class="text-end text-nowrap pe-3">
                                        @if($isActiveVariant)
                                            <a href="{{ route('admin.inventory.product.details', $product->id) }}" class="btn btn-sm btn-outline-danger btn-compact" style="font-size: 10px;">
                                                <i class="fas fa-times me-1"></i> Clear Filter
                                            </a>
                                        @else
                                            <a href="{{ route('admin.inventory.product.details', ['id' => $product->id, 'variant_id' => $v->id]) }}" class="btn btn-sm btn-light border btn-compact shadow-xs" style="font-size: 10px;">
                                                <i class="fas fa-filter text-primary me-1"></i> Filter History
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- 5. Product Movement Audit Trail Table (Collapsible) -->
    <div class="card p-0 mb-3 overflow-hidden shadow-xs">
        <div class="p-2 px-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 bg-white">
            <div>
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13px;">
                    <i class="fas fa-clipboard-list text-primary"></i>
                    <span>Complete Movement Ledger &amp; Stock Flow</span>
                </h6>
                <small class="text-muted" style="font-size: 10.5px;">
                    @if($selectedVariantId)
                        Showing audit logs filtered for <strong>Variant #{{ $selectedVariantId }}</strong>
                    @else
                        Showing all inbound, sale, return, and write-off transactions
                    @endif
                </small>
            </div>

            <div class="d-flex align-items-center gap-2">
                <!-- Variant Filter Dropdown -->
                @if($isVariable)
                    <div class="d-flex align-items-center gap-1">
                        <label class="small text-muted mb-0 font-monospace" style="font-size: 10px;">Filter:</label>
                        <select class="form-select shadow-none font-monospace" style="min-width: 160px; font-size: 11px;" onchange="location = this.value;">
                            <option value="{{ route('admin.inventory.product.details', $product->id) }}" {{ !$selectedVariantId ? 'selected' : '' }}>
                                All Combined
                            </option>
                            @foreach($variants as $v)
                                @php
                                    $attrParts = array_filter([$v->color, $v->size]);
                                    $attrLabel = count($attrParts) > 0 ? implode(' / ', $attrParts) : ('Variant #' . $v->id);
                                @endphp
                                <option value="{{ route('admin.inventory.product.details', ['id' => $product->id, 'variant_id' => $v->id]) }}" {{ $selectedVariantId === (int)$v->id ? 'selected' : '' }}>
                                    {{ $attrLabel }} (Stock: {{ $v->stock }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseMovementsLedger" aria-expanded="true" aria-controls="collapseMovementsLedger" title="Toggle Ledger">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseMovementsLedger">
            <div class="table-responsive pc-fit-container">
                <table class="table align-middle mb-0 ledger-table ledger-movements-table">
                    <thead>
                        <tr>
                            <th class="bn-digit ps-3 col-lm-id">Log ID</th>
                            <th class="col-lm-date">Date &amp; Time</th>
                            <th class="col-lm-attr">Attributes</th>
                            <th class="col-lm-type">Type</th>
                            <th class="text-center col-lm-change">Change</th>
                            <th class="text-center col-lm-flow">Balance Flow</th>
                            <th class="text-end col-lm-cost">Unit Cost</th>
                            <th class="text-end col-lm-imp">Cost Impact</th>
                            <th class="col-lm-ref">Reference</th>
                            <th class="pe-3 col-lm-user">Initiated By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $m)
                            @php
                                $variantParts = array_filter([$m->variant_color, $m->variant_size]);
                                $varLabel = count($variantParts) > 0 ? implode(' / ', $variantParts) : null;
                                $costImpact = abs((int) $m->quantity_change) * (float) ($m->unit_cost ?? 0);

                                $typeMeta = match($m->movement_type) {
                                    'purchase_in'      => ['label' => 'Purchase In',     'class' => 'bg-success-subtle text-success border border-success-subtle'],
                                    'checkout_sale'    => ['label' => 'Online Sale',     'class' => 'bg-primary-subtle text-primary border border-primary-subtle'],
                                    'pos_sale'         => ['label' => 'POS Sale',        'class' => 'bg-info-subtle text-info-emphasis border border-info-subtle'],
                                    'order_cancel'     => ['label' => 'Cancel Restock',  'class' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'],
                                    'order_return'     => ['label' => 'Return Restock',  'class' => 'bg-secondary-subtle text-secondary border border-secondary-subtle'],
                                    'damage_out'       => ['label' => 'Damage Out',      'class' => 'bg-danger-subtle text-danger border border-danger-subtle'],
                                    'expired_out'      => ['label' => 'Expired Out',     'class' => 'bg-danger-subtle text-danger border border-danger-subtle'],
                                    'supplier_return'  => ['label' => 'Supplier Return', 'class' => 'bg-dark-subtle text-dark border border-dark-subtle'],
                                    'adjustment'       => ['label' => 'Adjustment',      'class' => 'bg-light text-dark border'],
                                    default            => ['label' => ucwords(str_replace('_', ' ', $m->movement_type)), 'class' => 'bg-light text-dark border'],
                                };
                            @endphp
                            <tr>
                                <!-- Log ID -->
                                <td class="font-monospace bn-digit fw-semibold text-secondary ps-3 col-lm-id" style="font-size: 11px;">
                                    #{{ $m->id }}
                                </td>

                                <!-- Date & Time -->
                                <td class="col-lm-date" style="font-size: 11px; line-height: 1.3;">
                                    <span class="text-dark fw-semibold d-block bn-digit font-monospace">{{ date('d M Y', strtotime($m->created_at)) }}</span>
                                    <small class="text-muted bn-digit font-monospace" style="font-size: 10px;">{{ date('h:i A', strtotime($m->created_at)) }}</small>
                                </td>

                                <!-- Variant Attributes -->
                                <td class="col-lm-attr">
                                    @if($varLabel)
                                        <span class="badge bg-light text-primary border font-monospace text-truncate d-inline-block px-1 py-0" style="font-size: 9px; max-width: 120px;" title="{{ $varLabel }}">
                                            {{ $varLabel }}
                                        </span>
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 10px;">—</span>
                                    @endif
                                </td>

                                <!-- Movement Type -->
                                <td class="col-lm-type">
                                    <span class="badge movement-badge {{ $typeMeta['class'] }}">
                                        {{ $typeMeta['label'] }}
                                    </span>
                                </td>

                                <!-- Quantity Change -->
                                <td class="text-center font-monospace bn-digit col-lm-change">
                                    @if($m->quantity_change > 0)
                                        <span class="text-success fw-bold">+{{ $m->quantity_change }}</span>
                                    @elseif($m->quantity_change < 0)
                                        <span class="text-danger fw-bold">{{ $m->quantity_change }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>

                                <!-- Flow (Before -> After) -->
                                <td class="text-center font-monospace bn-digit col-lm-flow" style="font-size: 11px;">
                                    <span class="text-muted">{{ $m->quantity_before }}</span>
                                    <i class="fas fa-arrow-right-long text-secondary mx-1 opacity-75" style="font-size: 8px;"></i>
                                    <strong class="text-dark">{{ $m->quantity_after }}</strong>
                                </td>

                                <!-- Unit Cost -->
                                <td class="text-end font-monospace bn-digit text-muted col-lm-cost" style="font-size: 11px;">
                                    ৳ {{ number_format((float) ($m->unit_cost ?? 0), 2) }}
                                </td>

                                <!-- Cost Impact -->
                                <td class="text-end font-monospace bn-digit col-lm-imp" style="font-size: 11.5px;">
                                    <strong class="{{ $m->quantity_change < 0 ? 'text-danger' : 'text-success' }}">
                                        ৳ {{ number_format($costImpact, 2) }}
                                    </strong>
                                </td>

                                <!-- Reference -->
                                <td class="col-lm-ref" style="font-size: 11px;">
                                    @if(!empty($m->reference_id))
                                        <span class="font-monospace fw-semibold text-primary d-block text-truncate" title="{{ $m->reference_id }}">{{ $m->reference_id }}</span>
                                    @else
                                        <span class="text-muted d-block font-monospace">—</span>
                                    @endif
                                    <small class="text-muted text-uppercase font-monospace text-truncate d-block" style="font-size: 9px;">Src: {{ $m->source ?? 'system' }}</small>
                                </td>

                                <!-- Initiated By / Notes -->
                                <td class="col-lm-user pe-3" style="font-size: 11px; line-height: 1.3;">
                                    <span class="text-dark fw-semibold d-block text-truncate" title="{{ $m->creator_name ?? 'System Automated' }}">{{ $m->creator_name ?? 'System Automated' }}</span>
                                    @if(!empty($m->notes))
                                        <small class="text-muted text-truncate d-block mt-0.5 font-monospace" style="font-size: 9.5px;" title="{{ $m->notes }}">
                                            {{ $m->notes }}
                                        </small>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted small">
                                    <i class="fas fa-box-open fa-2x mb-2 d-block opacity-25"></i>
                                    <span class="fw-semibold">No stock movement records found for this product.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Bulletproof Responsive Pagination -->
            @if($movements->hasPages())
                <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">
                    <small class="text-muted bn-digit font-monospace" style="font-size: 11px;">
                        Showing {{ $movements->firstItem() }} to {{ $movements->lastItem() }} of {{ $movements->total() }} records
                    </small>
                    <div>
                        {{ $movements->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal 1: Product Thumbnail Lightbox Zoom Modal -->
<div class="modal fade ledger-modal-scope" id="ledgerZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                    <i class="fas fa-image text-primary me-1"></i> Product Image Preview
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="modalZoomProductImgTag" src="" alt="Product Full Image" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Bootstrap Notice Dialog Modal -->
<div class="modal fade ledger-modal-scope" id="ledgerNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-2" id="ledgerNoticeIconWrap" style="width: 44px; height: 44px; border-radius: 50% !important;">
                    <i class="fas fa-circle-info fs-4" id="ledgerNoticeIcon"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="ledgerNoticeTitle" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="ledgerNoticeMsg" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showLedgerNotice(message, title = 'Notification', type = 'info') {
        const titleEl  = document.getElementById('ledgerNoticeTitle');
        const msgEl    = document.getElementById('ledgerNoticeMsg');
        const iconWrap = document.getElementById('ledgerNoticeIconWrap');
        const iconEl   = document.getElementById('ledgerNoticeIcon');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;

        if (iconWrap && iconEl) {
            if (type === 'error' || type === 'danger') {
                iconWrap.className = 'rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2';
                iconEl.className = 'fas fa-circle-exclamation fs-4';
            } else if (type === 'success') {
                iconWrap.className = 'rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-2';
                iconEl.className = 'fas fa-circle-check fs-4';
            } else {
                iconWrap.className = 'rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-2';
                iconEl.className = 'fas fa-circle-info fs-4';
            }
        }

        const modalEl = document.getElementById('ledgerNoticeModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    // Global Media Chooser Integration for Product Thumbnail
    function extractMediaItem(mediaInput) {
        if (!mediaInput) return null;
        const item = Array.isArray(mediaInput) ? mediaInput[0] : mediaInput;
        if (!item || typeof item !== 'object') return null;
        const rawPath = item.file_path || item.path || item.url || '';
        const rawUrl  = item.url || item.file_path || item.path || '';
        if (!rawPath || rawPath === 'undefined') return null;
        return {
            path: String(rawPath).trim(),
            url:  String(rawUrl).trim()
        };
    }

    function chooseProductMedia() {
        if (typeof window.openMediaPicker === 'function') {
            window.openMediaPicker({
                multiple: false,
                onSelect: function(media) {
                    const picked = extractMediaItem(media);
                    if (picked && picked.path) {
                        const thumbImg = document.getElementById('productMasterThumb');
                        if (thumbImg) thumbImg.src = picked.url;
                        showLedgerNotice('New image selected from Global Media Library. To update product permanently, edit the catalog.', 'Media Chosen', 'success');
                    }
                }
            });
        } else {
            showLedgerNotice('Global Media Library is loading or not available on this layout.', 'Notice');
        }
    }

    function zoomProductImage() {
        const thumbImg = document.getElementById('productMasterThumb');
        const zoomImgTag = document.getElementById('modalZoomProductImgTag');
        if (thumbImg && zoomImgTag && thumbImg.src) {
            zoomImgTag.src = thumbImg.src;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('ledgerZoomModal')).show();
        }
    }
</script>
@endsection