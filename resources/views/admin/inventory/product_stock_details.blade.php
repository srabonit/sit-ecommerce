
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
    $warehouseStocks = $warehouseStocks ?? collect();
@endphp

@section('admin_content')

<style>
    /* =========================================================
       STOCK LEDGER - MODERN ADMIN UI
       Strict 5px Radius | Responsive | Scoped Styling
    ========================================================= */

    #prodLedgerRoot {
        --ledger-primary: #2563eb;
        --ledger-primary-soft: #eff6ff;
        --ledger-success: #15803d;
        --ledger-danger: #dc2626;
        --ledger-warning: #d97706;
        --ledger-text: #1e293b;
        --ledger-muted: #64748b;
        --ledger-border: #e2e8f0;
        --ledger-surface: #ffffff;
        --ledger-background: #f8fafc;

        color: var(--ledger-text);
        font-size: 12px;
        padding-bottom: 24px;
    }

    #prodLedgerRoot *,
    #prodLedgerRoot *::before,
    #prodLedgerRoot *::after,
    .ledger-modal-scope *,
    .ledger-modal-scope *::before,
    .ledger-modal-scope *::after {
        border-radius: 5px !important;
        box-sizing: border-box;
    }

    #prodLedgerRoot .font-monospace {
        font-weight: 700 !important;
    }

    #prodLedgerRoot .card {
        background: var(--ledger-surface);
        border: 1px solid var(--ledger-border);
        box-shadow: 0 2px 7px rgba(15, 23, 42, .035);
        margin-bottom: 15px;
    }

    #prodLedgerRoot .card-header {
        background: #fff;
        border-bottom: 1px solid var(--ledger-border);
    }

    #prodLedgerRoot .btn {
        font-weight: 600;
        transition: all .18s ease;
    }

    #prodLedgerRoot .btn:focus,
    #prodLedgerRoot .form-control:focus,
    #prodLedgerRoot .form-select:focus {
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10) !important;
    }

    #prodLedgerRoot .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 6px 11px !important;
        min-height: 30px;
        font-size: 10.5px !important;
        line-height: 1.3;
        white-space: nowrap;
    }

    #prodLedgerRoot .form-select {
        border-color: #dbe2ea;
        font-size: 11px;
        min-height: 34px;
    }

    #prodLedgerRoot .collapse-toggle-btn {
        width: 29px;
        height: 29px;
        flex: 0 0 29px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #dbe2ea;
        color: #64748b;
        transition: all .2s ease;
    }

    #prodLedgerRoot .collapse-toggle-btn:hover {
        background: var(--ledger-primary);
        color: #fff;
        border-color: var(--ledger-primary);
    }

    #prodLedgerRoot .collapse-icon {
        font-size: 10px;
        transition: transform .25s ease;
    }

    #prodLedgerRoot .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* Header */

    #prodLedgerRoot .ledger-header {
        padding: 3px 0 5px;
    }

    #prodLedgerRoot .ledger-header h5 {
        color: #172033;
        font-size: 19px;
        font-weight: 800;
        letter-spacing: -.35px;
    }

    #prodLedgerRoot .header-subtitle {
        font-size: 10.5px;
        color: #64748b;
        margin-top: 4px;
    }

    #prodLedgerRoot .header-actions-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }

    /* Product Overview */

    #prodLedgerRoot .product-overview-card {
        padding: 16px;
        background: linear-gradient(135deg, #fff 0%, #f8fbff 100%);
        border-color: #dce6f2;
    }

    #prodLedgerRoot .product-thumb-box {
        width: 76px;
        height: 76px;
        position: relative;
        overflow: hidden;
        background: #f8fafc;
        border: 1px solid #dbe2ea;
        cursor: pointer;
        flex-shrink: 0;
    }

    #prodLedgerRoot .product-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #prodLedgerRoot .product-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, .55);
        color: #fff;
        opacity: 0;
        transition: opacity .2s ease;
    }

    #prodLedgerRoot .product-thumb-box:hover .product-thumb-hover-overlay {
        opacity: 1;
    }

    #prodLedgerRoot .product-title {
        font-size: 15px;
        font-weight: 800;
        color: #172033;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    #prodLedgerRoot .product-meta {
        color: #64748b;
        font-size: 10.5px;
        line-height: 1.8;
    }

    #prodLedgerRoot .product-meta strong {
        color: #334155;
    }

    #prodLedgerRoot .product-price-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 8px;
    }

    #prodLedgerRoot .product-price-badges .badge {
        font-size: 9.5px;
        font-weight: 700;
        padding: 5px 8px;
    }

    #prodLedgerRoot .master-stats-wrap {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 22px;
        padding-left: 18px;
        border-left: 1px solid #e2e8f0;
    }

    #prodLedgerRoot .master-stat-label {
        color: #64748b;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .7px;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    #prodLedgerRoot .master-stat-value {
        font-size: 21px;
        font-weight: 800;
        line-height: 1.2;
        letter-spacing: -.5px;
    }

    /* Metrics */

    #prodLedgerRoot .metric-card {
        min-height: 95px;
        height: 100%;
        padding: 13px 14px;
        border: 1px solid var(--ledger-border);
        background: #fff;
        box-shadow: 0 2px 7px rgba(15, 23, 42, .035);
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: transform .18s ease, box-shadow .18s ease;
        overflow: hidden;
    }

    #prodLedgerRoot .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(15, 23, 42, .07);
    }

    #prodLedgerRoot .metric-card-green {
        background: linear-gradient(145deg, #fff 0%, #f0fdf4 100%);
        border-color: #bbf7d0;
    }

    #prodLedgerRoot .metric-card-red {
        background: linear-gradient(145deg, #fff 0%, #fef2f2 100%);
        border-color: #fecaca;
    }

    #prodLedgerRoot .metric-card-blue {
        background: linear-gradient(145deg, #fff 0%, #eff6ff 100%);
        border-color: #bfdbfe;
    }

    #prodLedgerRoot .metric-card-amber {
        background: linear-gradient(145deg, #fff 0%, #fffbeb 100%);
        border-color: #fde68a;
    }

    #prodLedgerRoot .metric-label {
        font-size: 10px;
        color: #64748b;
        font-weight: 700;
        margin-bottom: 7px;
    }

    #prodLedgerRoot .metric-value {
        font-size: 17px;
        font-weight: 800;
        line-height: 1.25;
        white-space: nowrap;
    }

    #prodLedgerRoot .metric-icon-box {
        width: 37px;
        height: 37px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 14px;
        box-shadow: 0 2px 5px rgba(15, 23, 42, .10);
    }

    /* Section Heading */

    #prodLedgerRoot .ledger-section-heading {
        padding: 11px 14px;
        min-height: 48px;
        background: #fff;
        border-bottom: 1px solid var(--ledger-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }

    #prodLedgerRoot .ledger-section-title {
        font-size: 12px;
        font-weight: 800;
        color: #253247;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    #prodLedgerRoot .ledger-section-title i {
        color: var(--ledger-primary);
        font-size: 13px;
    }

    #prodLedgerRoot .ledger-section-subtitle {
        display: block;
        color: #64748b;
        font-size: 10px;
        margin-top: 4px;
        line-height: 1.5;
    }

    /* Tables */

    #prodLedgerRoot .pc-fit-container {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #prodLedgerRoot .ledger-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #prodLedgerRoot .ledger-table thead th {
        padding: 11px 9px;
        background: #f8fafc;
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .45px;
        white-space: nowrap;
        vertical-align: middle;
    }

    #prodLedgerRoot .ledger-table tbody td {
        padding: 10px 9px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        font-size: 10.5px;
        vertical-align: middle;
        white-space: nowrap;
    }

    #prodLedgerRoot .ledger-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #prodLedgerRoot .ledger-table tbody tr:hover td {
        background: #f8fbff;
    }

    #prodLedgerRoot .ledger-table .badge {
        font-size: 9px;
        font-weight: 700;
        padding: 4px 7px;
    }

    #prodLedgerRoot .ledger-table .table-primary td {
        background: #eff6ff !important;
    }

    #prodLedgerRoot .movement-badge {
        font-size: 9px;
        font-weight: 800;
        padding: 4px 7px;
        letter-spacing: .15px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    /* Desktop Table Fit */

    @media (min-width: 992px) {
        #prodLedgerRoot .ledger-movements-table {
            table-layout: fixed !important;
            width: 100% !important;
        }

        #prodLedgerRoot .ledger-movements-table th,
        #prodLedgerRoot .ledger-movements-table td {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #prodLedgerRoot .col-lm-id     { width: 5%; }
        #prodLedgerRoot .col-lm-date   { width: 11%; }
        #prodLedgerRoot .col-lm-attr   { width: 12%; }
        #prodLedgerRoot .col-lm-type   { width: 11%; }
        #prodLedgerRoot .col-lm-change { width: 6%; }
        #prodLedgerRoot .col-lm-flow   { width: 9%; }
        #prodLedgerRoot .col-lm-cost   { width: 10%; }
        #prodLedgerRoot .col-lm-imp    { width: 10%; }
        #prodLedgerRoot .col-lm-ref    { width: 13%; }
        #prodLedgerRoot .col-lm-user   { width: 13%; }
    }

    /* Pagination */

    #prodLedgerRoot .card-footer {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    #prodLedgerRoot .pagination {
        margin-bottom: 0;
        gap: 3px;
        flex-wrap: wrap;
    }

    #prodLedgerRoot .page-link {
        border: 1px solid #e2e8f0;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        padding: 6px 10px;
        min-width: 29px;
        text-align: center;
    }

    #prodLedgerRoot .page-item.active .page-link {
        background: var(--ledger-primary);
        border-color: var(--ledger-primary);
        color: #fff;
    }

    #prodLedgerRoot .page-item.disabled .page-link {
        background: #f8fafc;
        color: #94a3b8;
    }

    /* Modal */

    .ledger-modal-scope .modal-content {
        border: 1px solid #e2e8f0;
        box-shadow: 0 15px 45px rgba(15, 23, 42, .18);
    }

    .ledger-modal-scope .modal-header {
        padding: 14px 16px;
    }

    .ledger-modal-scope .modal-body {
        padding: 16px;
    }

    /* Responsive */

    @media (max-width: 991.98px) {
        #prodLedgerRoot .master-stats-wrap {
            border-left: 0;
            padding-left: 0;
            border-top: 1px solid #e2e8f0;
            padding-top: 13px;
            justify-content: space-between;
            width: 100%;
        }

        #prodLedgerRoot .master-details-wrap {
            flex-direction: column;
            align-items: stretch !important;
        }

        #prodLedgerRoot .ledger-movements-table {
            min-width: 1050px;
            table-layout: auto;
        }

        #prodLedgerRoot .ledger-table {
            min-width: 760px;
            table-layout: auto;
        }
    }

    @media (max-width: 767.98px) {
        #prodLedgerRoot {
            padding-top: 10px !important;
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #prodLedgerRoot .ledger-header {
            gap: 12px !important;
        }

        #prodLedgerRoot .ledger-header h5 {
            font-size: 16px;
            line-height: 1.4;
        }

        #prodLedgerRoot .header-actions-wrap {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 7px;
        }

        #prodLedgerRoot .header-actions-wrap .btn {
            width: 100%;
            min-height: 35px;
            padding: 7px 5px !important;
            font-size: 10px !important;
        }

        #prodLedgerRoot .product-overview-card {
            padding: 12px !important;
        }

        #prodLedgerRoot .product-thumb-box {
            width: 65px;
            height: 65px;
        }

        #prodLedgerRoot .product-title {
            font-size: 13px;
        }

        #prodLedgerRoot .product-meta {
            font-size: 9px;
            overflow-wrap: anywhere;
        }

        #prodLedgerRoot .master-stats-wrap {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        #prodLedgerRoot .master-stat-value {
            font-size: 18px;
        }

        #prodLedgerRoot .metric-card {
            padding: 10px;
            min-height: 83px;
        }

        #prodLedgerRoot .metric-label {
            font-size: 9px;
        }

        #prodLedgerRoot .metric-value {
            font-size: 14px;
        }

        #prodLedgerRoot .metric-icon-box {
            width: 30px;
            height: 30px;
            font-size: 11px;
        }

        #prodLedgerRoot .ledger-section-heading {
            padding: 10px;
        }

        #prodLedgerRoot .ledger-section-title {
            font-size: 11px;
        }

        #prodLedgerRoot .ledger-section-subtitle {
            font-size: 9px;
        }

        #prodLedgerRoot .ledger-table {
            min-width: 760px;
        }

        #prodLedgerRoot .ledger-movements-table {
            min-width: 1050px;
        }

        #prodLedgerRoot .card {
            margin-bottom: 11px;
        }
    }

    @media (max-width: 380px) {
        #prodLedgerRoot .metric-value {
            font-size: 12px;
        }

        #prodLedgerRoot .product-price-badges .badge {
            font-size: 8px;
        }

        #prodLedgerRoot .master-stat-value {
            font-size: 16px;
        }
    }

    @media print {
        #prodLedgerRoot .no-print,
        #prodLedgerRoot .header-actions-wrap,
        #prodLedgerRoot .collapse-toggle-btn,
        #prodLedgerRoot .product-thumb-box button {
            display: none !important;
        }

        #prodLedgerRoot .card {
            box-shadow: none !important;
            break-inside: avoid;
        }

        #prodLedgerRoot .collapse:not(.show) {
            display: block !important;
        }

        #prodLedgerRoot .pc-fit-container {
            overflow: visible !important;
        }

        #prodLedgerRoot .ledger-table {
            min-width: 0 !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="prodLedgerRoot">

    {{-- =====================================================
         1. PAGE HEADER
    ====================================================== --}}

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 ledger-header">

        <div class="min-w-0">
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">

                <a href="{{ route('admin.inventory.balance') }}"
                   class="btn btn-sm btn-light border btn-compact text-secondary shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                    Stock Balance
                </a>

                <span class="text-muted">/</span>

                <span class="badge bg-light text-secondary border font-monospace px-2 py-1">
                    ID: #{{ $product->id }}
                </span>

                @if($isVariable)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                        <i class="fas fa-layer-group me-1"></i>
                        Variable ({{ $variants->count() }})
                    </span>
                @else
                    <span class="badge bg-light text-dark border px-2 py-1">
                        <i class="fas fa-box me-1"></i>
                        Simple Product
                    </span>
                @endif
            </div>

            <h5 class="mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-box-open text-primary"></i>
                <span>{{ $product->title }}</span>
            </h5>

            <div class="header-subtitle">
                Product inventory overview, stock valuation and complete movement audit history.
            </div>
        </div>

        <div class="header-actions-wrap">

            <a href="{{ route('admin.inventory.stock_in') }}"
               class="btn btn-sm btn-success btn-compact shadow-sm">
                <i class="fas fa-arrow-down-long"></i>
                Stock In
            </a>

            <a href="{{ route('admin.inventory.stock_out') }}"
               class="btn btn-sm btn-danger btn-compact shadow-sm">
                <i class="fas fa-arrow-up-long"></i>
                Stock Out
            </a>

            <a href="{{ route('admin.product.edit', $product->id) }}"
               class="btn btn-sm btn-outline-secondary btn-compact">
                <i class="fas fa-pen-to-square"></i>
                Edit Catalog
            </a>

        </div>
    </div>

    {{-- =====================================================
         2. PRODUCT MASTER OVERVIEW
    ====================================================== --}}

    <div class="card product-overview-card mb-3">

        <div class="d-flex align-items-center justify-content-between master-details-wrap gap-3">

            <div class="d-flex align-items-center gap-3 min-w-0">

                <div class="position-relative flex-shrink-0">

                    <div class="product-thumb-box shadow-sm"
                         onclick="zoomProductImage()"
                         title="Click to preview product image">

                        <img id="productMasterThumb"
                             src="{{ $thumb }}"
                             alt="{{ $product->title }}"
                             onerror="this.src='https://placehold.co/68x68?text=Item';">

                        <div class="product-thumb-hover-overlay">
                            <i class="fas fa-search-plus"></i>
                        </div>
                    </div>

                    <button type="button"
                            class="btn btn-sm btn-light border btn-compact w-100 mt-1 py-0 px-1"
                            style="font-size:9px!important;min-height:22px;"
                            onclick="chooseProductMedia()"
                            title="Change Image from Media Library">

                        <i class="fas fa-photo-film text-primary"></i>
                        Media
                    </button>

                </div>

                <div class="min-w-0 flex-grow-1">

                    <h6 class="product-title mb-1" title="{{ $product->title }}">
                        {{ $product->title }}
                    </h6>

                    <div class="product-meta font-monospace">

                        <div>
                            Master SKU:
                            <strong>{{ $product->product_code ?? ($product->sku ?? '—') }}</strong>
                        </div>

                        <div>
                            Barcode:
                            <strong>{{ $product->barcode ?? '—' }}</strong>
                        </div>

                    </div>

                    <div class="product-price-badges">

                        <span class="badge bg-light text-dark border">
                            <i class="fas fa-tag text-primary me-1"></i>
                            Selling Price: ৳{{ number_format($sellingPrice, 2) }}
                        </span>

                        @if(!$isVariable)
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-coins text-warning me-1"></i>
                                Unit Cost: ৳{{ number_format($parentCost, 2) }}
                            </span>
                        @endif

                    </div>

                </div>
            </div>

            {{-- Stock & Valuation --}}

            <div class="master-stats-wrap">

                <div>
                    <div class="master-stat-label">
                        <i class="fas fa-cubes me-1"></i>
                        Available Stock
                    </div>

                    <div class="master-stat-value {{ $effectiveStock <= 0 ? 'text-danger' : 'text-success' }} font-monospace">
                        {{ number_format($effectiveStock) }}
                        <span class="text-muted fw-normal" style="font-size:10px;">Units</span>
                    </div>
                </div>

                <div>
                    <div class="master-stat-label">
                        <i class="fas fa-wallet me-1"></i>
                        Inventory Valuation
                    </div>

                    <div class="master-stat-value text-primary font-monospace">
                        ৳ {{ number_format($totalValuation, 2) }}
                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- =====================================================
         3. LIFETIME KPI CARDS
    ====================================================== --}}

    <div class="row g-2 mb-3">

        <div class="col-6 col-md-3">
            <div class="metric-card metric-card-green">

                <div class="d-flex justify-content-between align-items-center gap-2">

                    <div class="min-w-0">
                        <div class="metric-label">
                            Lifetime Stock In
                        </div>

                        <div class="metric-value text-success font-monospace">
                            +{{ number_format($lifetimeIn) }}
                            <span style="font-size:9px;">Units</span>
                        </div>
                    </div>

                    <div class="metric-icon-box bg-success text-white">
                        <i class="fas fa-arrow-down-long"></i>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="metric-card metric-card-red">

                <div class="d-flex justify-content-between align-items-center gap-2">

                    <div class="min-w-0">
                        <div class="metric-label">
                            Lifetime Stock Out
                        </div>

                        <div class="metric-value text-danger font-monospace">
                            -{{ number_format($lifetimeOut) }}
                            <span style="font-size:9px;">Units</span>
                        </div>
                    </div>

                    <div class="metric-icon-box bg-danger text-white">
                        <i class="fas fa-arrow-up-long"></i>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="metric-card metric-card-blue">

                <div class="d-flex justify-content-between align-items-center gap-2">

                    <div class="min-w-0">
                        <div class="metric-label">
                            Total Units Sold
                        </div>

                        <div class="metric-value text-primary font-monospace">
                            {{ number_format($lifetimeSold) }}
                            <span style="font-size:9px;">Units</span>
                        </div>
                    </div>

                    <div class="metric-icon-box bg-primary text-white">
                        <i class="fas fa-cart-shopping"></i>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="metric-card metric-card-amber">

                <div class="d-flex justify-content-between align-items-center gap-2">

                    <div class="min-w-0">
                        <div class="metric-label">
                            Sales Revenue
                        </div>

                        <div class="metric-value text-dark font-monospace">
                            ৳ {{ number_format($lifetimeRev, 2) }}
                        </div>
                    </div>

                    <div class="metric-icon-box text-dark" style="background:#f59e0b;">
                        <i class="fas fa-receipt"></i>
                    </div>

                </div>
            </div>
        </div>

    </div>

    {{-- =====================================================
         4. WAREHOUSE & BRANCH DISTRIBUTION
    ====================================================== --}}

    @if(isset($warehouseStocks) && $warehouseStocks->count() > 0)

        <div class="card p-0 mb-3 overflow-hidden">

            <div class="ledger-section-heading">

                <div>
                    <h6 class="ledger-section-title">
                        <i class="fas fa-warehouse"></i>
                        Warehouse &amp; Branch Stock Breakdown
                    </h6>

                    <small class="ledger-section-subtitle">
                        Current stock availability across warehouses and branches.
                    </small>
                </div>

                <button type="button"
                        class="collapse-toggle-btn no-print"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseWarehouseStockMatrix"
                        aria-expanded="true"
                        aria-controls="collapseWarehouseStockMatrix"
                        title="Toggle Breakdown">

                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>

            </div>

            <div class="collapse show" id="collapseWarehouseStockMatrix">

                <div class="table-responsive pc-fit-container">

                    <table class="table align-middle mb-0 ledger-table">

                        <thead>
                            <tr>
                                <th class="ps-3">Warehouse</th>
                                <th>Branch</th>
                                <th class="text-center">Current Quantity</th>
                                <th class="text-center">Alert Level</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-3">Last Updated</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($warehouseStocks as $ws)

                                @php
                                    $wQty = (int) ($ws->quantity ?? 0);
                                    $wAlert = (int) ($ws->alert_quantity ?? 5);

                                    $wStatus = match(true) {
                                        $wQty <= 0 => [
                                            'label' => 'Out of Stock',
                                            'class' => 'bg-danger-subtle text-danger border border-danger-subtle'
                                        ],
                                        $wQty <= $wAlert => [
                                            'label' => 'Low Stock',
                                            'class' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                                        ],
                                        default => [
                                            'label' => 'In Stock',
                                            'class' => 'bg-success-subtle text-success border border-success-subtle'
                                        ],
                                    };
                                @endphp

                                <tr>

                                    <td class="ps-3 fw-bold text-dark">
                                        <i class="fas fa-building text-secondary me-1"></i>
                                        {{ $ws->warehouse_name ?? ('Warehouse #' . ($ws->warehouse_id ?? '1')) }}
                                    </td>

                                    <td>
                                        <span class="badge bg-light text-secondary border font-monospace">
                                            {{ $ws->branch_name ?? ($ws->branch_id ? 'Branch #' . $ws->branch_id : 'HQ / Central') }}
                                        </span>
                                    </td>

                                    <td class="text-center font-monospace">
                                        <strong class="{{ $wQty <= 0 ? 'text-danger' : ($wQty <= $wAlert ? 'text-warning-emphasis' : 'text-success') }}">
                                            {{ number_format($wQty) }}
                                        </strong>
                                    </td>

                                    <td class="text-center text-muted font-monospace">
                                        {{ $wAlert }} Units
                                    </td>

                                    <td class="text-center">
                                        <span class="badge {{ $wStatus['class'] }}">
                                            {{ $wStatus['label'] }}
                                        </span>
                                    </td>

                                    <td class="text-end pe-3 text-muted font-monospace">
                                        {{ !empty($ws->updated_at) ? \Carbon\Carbon::parse($ws->updated_at)->diffForHumans() : '—' }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>
            </div>
        </div>

    @endif

    {{-- =====================================================
         5. VARIANT INVENTORY BREAKDOWN
    ====================================================== --}}

    @if($isVariable)

        <div class="card p-0 mb-3 overflow-hidden">

            <div class="ledger-section-heading">

                <div>
                    <h6 class="ledger-section-title">
                        <i class="fas fa-layer-group"></i>
                        Variant-wise Stock &amp; Valuation Matrix
                    </h6>

                    <small class="ledger-section-subtitle">
                        Individual stock, purchase cost, selling price and valuation by variant.
                    </small>
                </div>

                <div class="d-flex align-items-center gap-2">

                    <span class="badge bg-light text-muted border font-monospace px-2 py-2">
                        {{ $variants->count() }} Variants
                    </span>

                    <button type="button"
                            class="collapse-toggle-btn no-print"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseVariantMatrix"
                            aria-expanded="true"
                            aria-controls="collapseVariantMatrix"
                            title="Toggle Variant Matrix">

                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>

                </div>
            </div>

            <div class="collapse show" id="collapseVariantMatrix">

                <div class="table-responsive pc-fit-container">

                    <table class="table align-middle mb-0 ledger-table">

                        <thead>
                            <tr>
                                <th class="ps-3">Variant ID</th>
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
                                    $vCost = (float) ($v->purchase_cost ?? ($v->purchase_price ?? 0.00));
                                    $vPrice = (float) ($v->regular_price ?? $sellingPrice);
                                    $vVal = $vStock * $vCost;
                                    $vAlert = (int) ($v->alert_quantity ?? 5);

                                    $vStatus = match(true) {
                                        $vStock <= 0 => [
                                            'label' => 'Out of Stock',
                                            'class' => 'bg-danger-subtle text-danger border border-danger-subtle'
                                        ],
                                        $vStock <= $vAlert => [
                                            'label' => 'Low Stock',
                                            'class' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                                        ],
                                        default => [
                                            'label' => 'In Stock',
                                            'class' => 'bg-success-subtle text-success border border-success-subtle'
                                        ],
                                    };

                                    $attrParts = array_filter([$v->color, $v->size]);
                                    $attrLabel = count($attrParts) > 0
                                        ? implode(' / ', $attrParts)
                                        : ($v->name ?? ('Variant #' . $v->id));

                                    $isActiveVariant = ($selectedVariantId === (int)$v->id);
                                @endphp

                                <tr class="{{ $isActiveVariant ? 'table-primary' : '' }}">

                                    <td class="font-monospace text-secondary ps-3">
                                        #{{ $v->id }}
                                    </td>

                                    <td>
                                        <span class="badge bg-light text-primary border font-monospace text-truncate d-inline-block px-2 py-1"
                                              style="max-width:140px;"
                                              title="{{ $attrLabel }}">

                                            <i class="fas fa-tag me-1 text-muted"></i>
                                            {{ $attrLabel }}
                                        </span>
                                    </td>

                                    <td class="font-monospace">
                                        <span class="badge bg-light text-dark border px-2 py-1 text-truncate d-inline-block"
                                              style="max-width:120px;"
                                              title="{{ $v->product_code ?? ($v->sku ?? '—') }}">

                                            {{ $v->product_code ?? ($v->sku ?? '—') }}
                                        </span>
                                    </td>

                                    <td class="text-center font-monospace">
                                        <strong class="{{ $vStock <= 0 ? 'text-danger' : ($vStock <= $vAlert ? 'text-warning-emphasis' : 'text-success') }}">
                                            {{ number_format($vStock) }}
                                        </strong>
                                    </td>

                                    <td class="text-end font-monospace text-muted">
                                        ৳ {{ number_format($vCost, 2) }}
                                    </td>

                                    <td class="text-end font-monospace text-dark">
                                        ৳ {{ number_format($vPrice, 2) }}
                                    </td>

                                    <td class="text-end font-monospace fw-bold text-dark">
                                        ৳ {{ number_format($vVal, 2) }}
                                    </td>

                                    <td class="text-center">
                                        <span class="badge border {{ $vStatus['class'] }}">
                                            {{ $vStatus['label'] }}
                                        </span>
                                    </td>

                                    <td class="text-end pe-3">

                                        @if($isActiveVariant)

                                            <a href="{{ route('admin.inventory.product.details', $product->id) }}"
                                               class="btn btn-sm btn-outline-danger btn-compact">

                                                <i class="fas fa-times"></i>
                                                Clear Filter
                                            </a>

                                        @else

                                            <a href="{{ route('admin.inventory.product.details', ['id' => $product->id, 'variant_id' => $v->id]) }}"
                                               class="btn btn-sm btn-light border btn-compact">

                                                <i class="fas fa-filter text-primary"></i>
                                                Filter History
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

    {{-- =====================================================
         6. COMPLETE MOVEMENT AUDIT LEDGER
    ====================================================== --}}

    <div class="card p-0 mb-3 overflow-hidden">

        <div class="ledger-section-heading">

            <div class="min-w-0">

                <h6 class="ledger-section-title">
                    <i class="fas fa-clipboard-list"></i>
                    Complete Movement Ledger &amp; Stock Flow
                </h6>

                <small class="ledger-section-subtitle">

                    @if($selectedVariantId)
                        Showing audit logs filtered for
                        <strong>Variant #{{ $selectedVariantId }}</strong>
                    @else
                        Showing all inbound, sale, return, exchange, and write-off transactions
                    @endif

                </small>

            </div>

            <div class="d-flex align-items-center gap-2 flex-shrink-0">

                @if($isVariable)

                    <div class="d-flex align-items-center gap-1">

                        <label class="small text-muted mb-0 font-monospace">
                            Filter:
                        </label>

                        <select class="form-select shadow-none font-monospace"
                                style="min-width:160px;"
                                onchange="location = this.value;">

                            <option value="{{ route('admin.inventory.product.details', $product->id) }}"
                                {{ !$selectedVariantId ? 'selected' : '' }}>
                                All Combined
                            </option>

                            @foreach($variants as $v)

                                @php
                                    $attrParts = array_filter([$v->color, $v->size]);
                                    $attrLabel = count($attrParts) > 0
                                        ? implode(' / ', $attrParts)
                                        : ('Variant #' . $v->id);
                                @endphp

                                <option value="{{ route('admin.inventory.product.details', ['id' => $product->id, 'variant_id' => $v->id]) }}"
                                    {{ $selectedVariantId === (int)$v->id ? 'selected' : '' }}>

                                    {{ $attrLabel }} (Stock: {{ $v->stock }})

                                </option>

                            @endforeach

                        </select>

                    </div>

                @endif

                <button type="button"
                        class="collapse-toggle-btn no-print"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseMovementsLedger"
                        aria-expanded="true"
                        aria-controls="collapseMovementsLedger"
                        title="Toggle Ledger">

                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>

            </div>

        </div>

        <div class="collapse show" id="collapseMovementsLedger">

            <div class="table-responsive pc-fit-container">

                <table class="table align-middle mb-0 ledger-table ledger-movements-table">

                    <thead>
                        <tr>
                            <th class="ps-3 col-lm-id">Log ID</th>
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
                                $varLabel = count($variantParts) > 0
                                    ? implode(' / ', $variantParts)
                                    : null;

                                $costImpact = abs((int) $m->quantity_change) * (float) ($m->unit_cost ?? 0);

                                $typeMeta = match($m->movement_type) {
                                    'purchase_in', 'purchase_receipt_in' => [
                                        'label' => 'Purchase In',
                                        'class' => 'bg-success-subtle text-success border border-success-subtle'
                                    ],

                                    'checkout_sale', 'order_processing_deduct' => [
                                        'label' => 'Online Sale',
                                        'class' => 'bg-primary-subtle text-primary border border-primary-subtle'
                                    ],

                                    'pos_sale' => [
                                        'label' => 'POS Sale',
                                        'class' => 'bg-info-subtle text-info-emphasis border border-info-subtle'
                                    ],

                                    'order_cancel' => [
                                        'label' => 'Cancel Restock',
                                        'class' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                                    ],

                                    'order_return' => [
                                        'label' => 'Return Restock',
                                        'class' => 'bg-secondary-subtle text-secondary border border-secondary-subtle'
                                    ],

                                    'exchange_in' => [
                                        'label' => 'Exchange In',
                                        'class' => 'bg-success-subtle text-success border border-success-subtle'
                                    ],

                                    'exchange_out' => [
                                        'label' => 'Exchange Out',
                                        'class' => 'bg-danger-subtle text-danger border border-danger-subtle'
                                    ],

                                    'purchase_return' => [
                                        'label' => 'RTV Return',
                                        'class' => 'bg-danger-subtle text-danger border border-danger-subtle'
                                    ],

                                    'damage_out', 'damage_loss' => [
                                        'label' => 'Damage Out',
                                        'class' => 'bg-danger-subtle text-danger border border-danger-subtle'
                                    ],

                                    'expired_out' => [
                                        'label' => 'Expired Out',
                                        'class' => 'bg-danger-subtle text-danger border border-danger-subtle'
                                    ],

                                    'supplier_return' => [
                                        'label' => 'Supplier Return',
                                        'class' => 'bg-dark-subtle text-dark border border-dark-subtle'
                                    ],

                                    'adjustment' => [
                                        'label' => 'Adjustment',
                                        'class' => 'bg-light text-dark border'
                                    ],

                                    default => [
                                        'label' => ucwords(str_replace('_', ' ', $m->movement_type)),
                                        'class' => 'bg-light text-dark border'
                                    ],
                                };
                            @endphp

                            <tr>

                                {{-- Log ID --}}

                                <td class="font-monospace text-secondary ps-3 col-lm-id">
                                    #{{ $m->id }}
                                </td>

                                {{-- Date --}}

                                <td class="col-lm-date">

                                    <span class="text-dark fw-bold d-block font-monospace">
                                        {{ date('d M Y', strtotime($m->created_at)) }}
                                    </span>

                                    <small class="text-muted font-monospace" style="font-size:9px;">
                                        {{ date('h:i A', strtotime($m->created_at)) }}
                                    </small>

                                </td>

                                {{-- Attributes --}}

                                <td class="col-lm-attr">

                                    @if($varLabel)

                                        <span class="badge bg-light text-primary border font-monospace text-truncate d-inline-block px-2 py-1"
                                              style="max-width:120px;"
                                              title="{{ $varLabel }}">

                                            {{ $varLabel }}

                                        </span>

                                    @else

                                        <span class="text-muted font-monospace">—</span>

                                    @endif

                                </td>

                                {{-- Movement Type --}}

                                <td class="col-lm-type">

                                    <span class="badge movement-badge {{ $typeMeta['class'] }}">
                                        {{ $typeMeta['label'] }}
                                    </span>

                                </td>

                                {{-- Quantity Change --}}

                                <td class="text-center font-monospace col-lm-change">

                                    @if($m->quantity_change > 0)

                                        <span class="text-success fw-bold">
                                            +{{ $m->quantity_change }}
                                        </span>

                                    @elseif($m->quantity_change < 0)

                                        <span class="text-danger fw-bold">
                                            {{ $m->quantity_change }}
                                        </span>

                                    @else

                                        <span class="text-muted">0</span>

                                    @endif

                                </td>

                                {{-- Stock Flow --}}

                                <td class="text-center font-monospace col-lm-flow">

                                    <span class="text-muted">
                                        {{ $m->quantity_before }}
                                    </span>

                                    <i class="fas fa-arrow-right-long text-secondary mx-1"
                                       style="font-size:8px;"></i>

                                    <strong class="text-dark">
                                        {{ $m->quantity_after }}
                                    </strong>

                                </td>

                                {{-- Unit Cost --}}

                                <td class="text-end font-monospace text-muted col-lm-cost">

                                    ৳ {{ number_format((float) ($m->unit_cost ?? 0), 2) }}

                                </td>

                                {{-- Cost Impact --}}

                                <td class="text-end font-monospace col-lm-imp">

                                    <strong class="{{ $m->quantity_change < 0 ? 'text-danger' : 'text-success' }}">
                                        ৳ {{ number_format($costImpact, 2) }}
                                    </strong>

                                </td>

                                {{-- Reference --}}

                                <td class="col-lm-ref">

                                    @if(!empty($m->reference_id))

                                        <span class="font-monospace fw-bold text-primary d-block text-truncate"
                                              title="{{ $m->reference_id }}">

                                            {{ $m->reference_id }}

                                        </span>

                                    @else

                                        <span class="text-muted d-block font-monospace">
                                            —
                                        </span>

                                    @endif

                                    <small class="text-muted text-uppercase font-monospace text-truncate d-block"
                                           style="font-size:9px;">

                                        Src: {{ $m->source ?? 'system' }}

                                    </small>

                                </td>

                                {{-- Initiated By --}}

                                <td class="col-lm-user pe-3">

                                    <span class="text-dark fw-bold d-block text-truncate"
                                          title="{{ $m->creator_name ?? 'System Automated' }}">

                                        {{ $m->creator_name ?? 'System Automated' }}

                                    </span>

                                    @if(!empty($m->notes))

                                        <small class="text-muted text-truncate d-block font-monospace mt-1"
                                               style="font-size:9px;"
                                               title="{{ $m->notes }}">

                                            {{ $m->notes }}

                                        </small>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">

                                    <div class="mb-3">
                                        <i class="fas fa-box-open fa-3x opacity-25"></i>
                                    </div>

                                    <div class="fw-bold text-dark mb-1">
                                        No Stock Movement Records Found
                                    </div>

                                    <small>
                                        There are no stock movement records available for this product.
                                    </small>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            {{-- Pagination --}}

            @if($movements->hasPages())

                <div class="p-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">

                    <small class="text-muted font-monospace text-center">
                        Showing
                        <strong class="text-dark">{{ $movements->firstItem() }}</strong>
                        to
                        <strong class="text-dark">{{ $movements->lastItem() }}</strong>
                        of
                        <strong class="text-dark">{{ $movements->total() }}</strong>
                        records
                    </small>

                    <div class="d-flex justify-content-center">
                        {{ $movements->links('pagination::bootstrap-5') }}
                    </div>

                </div>

            @endif

        </div>
    </div>

</div>

{{-- =========================================================
     MODAL 1: PRODUCT IMAGE PREVIEW
========================================================= --}}

<div class="modal fade ledger-modal-scope"
     id="ledgerZoomModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index:106000;">

    <div class="modal-dialog modal-dialog-centered modal-md">

        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header border-0 pb-0">

                <h6 class="fw-bold text-dark mb-0">
                    <i class="fas fa-image text-primary me-1"></i>
                    Product Image Preview
                </h6>

                <button type="button"
                        class="btn-close shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>

            </div>

            <div class="modal-body text-center p-3">

                <img id="modalZoomProductImgTag"
                     src=""
                     alt="Product Full Image"
                     class="img-fluid border"
                     style="max-height:480px;object-fit:contain;">

            </div>

        </div>
    </div>
</div>

{{-- =========================================================
     MODAL 2: NOTIFICATION DIALOG
========================================================= --}}

<div class="modal fade ledger-modal-scope"
     id="ledgerNoticeModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index:106500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content border-0 shadow-lg">

            <div class="modal-body text-center p-4">

                <div class="bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-3"
                     id="ledgerNoticeIconWrap"
                     style="width:48px;height:48px;">

                    <i class="fas fa-circle-info fs-4" id="ledgerNoticeIcon"></i>

                </div>

                <h6 class="fw-bold text-dark mb-2"
                    id="ledgerNoticeTitle"
                    style="font-size:14px;">

                    Notification

                </h6>

                <p class="text-muted small mb-3"
                   id="ledgerNoticeMsg"
                   style="font-size:12px;line-height:1.55;">

                </p>

                <button type="button"
                        class="btn btn-dark btn-compact px-4"
                        data-bs-dismiss="modal">

                    <i class="fas fa-check me-1"></i>
                    OK

                </button>

            </div>

        </div>
    </div>
</div>

{{-- =========================================================
     JAVASCRIPT
========================================================= --}}

<script>
    /**
     * =========================================================
     * LEDGER NOTIFICATION
     * =========================================================
     */
    function showLedgerNotice(message, title = 'Notification', type = 'info') {

        const titleEl = document.getElementById('ledgerNoticeTitle');
        const msgEl = document.getElementById('ledgerNoticeMsg');
        const iconWrap = document.getElementById('ledgerNoticeIconWrap');
        const iconEl = document.getElementById('ledgerNoticeIcon');

        if (titleEl) {
            titleEl.innerText = title;
        }

        if (msgEl) {
            msgEl.innerText = message;
        }

        if (iconWrap && iconEl) {

            if (type === 'error' || type === 'danger') {

                iconWrap.className =
                    'bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-3';

                iconEl.className = 'fas fa-circle-exclamation fs-4';

            } else if (type === 'success') {

                iconWrap.className =
                    'bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-3';

                iconEl.className = 'fas fa-circle-check fs-4';

            } else {

                iconWrap.className =
                    'bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-3';

                iconEl.className = 'fas fa-circle-info fs-4';

            }
        }

        const modalEl = document.getElementById('ledgerNoticeModal');

        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }


    /**
     * =========================================================
     * GLOBAL MEDIA LIBRARY ITEM EXTRACTION
     * =========================================================
     */
    function extractMediaItem(mediaInput) {

        if (!mediaInput) return null;

        const item = Array.isArray(mediaInput)
            ? mediaInput[0]
            : mediaInput;

        if (!item || typeof item !== 'object') return null;

        const rawPath = item.file_path || item.path || item.url || '';
        const rawUrl = item.url || item.file_path || item.path || '';

        if (!rawPath || rawPath === 'undefined') return null;

        return {
            path: String(rawPath).trim(),
            url: String(rawUrl).trim()
        };
    }


    /**
     * =========================================================
     * CHOOSE PRODUCT IMAGE FROM GLOBAL MEDIA LIBRARY
     * =========================================================
     */
    function chooseProductMedia() {

        if (typeof window.openMediaPicker === 'function') {

            window.openMediaPicker({

                multiple: false,

                onSelect: function(media) {

                    const picked = extractMediaItem(media);

                    if (picked && picked.path) {

                        const thumbImg = document.getElementById('productMasterThumb');

                        if (thumbImg) {
                            thumbImg.src = picked.url;
                        }

                        showLedgerNotice(
                            'New image selected from Global Media Library. To update product permanently, edit the catalog.',
                            'Media Chosen',
                            'success'
                        );
                    }
                }
            });

        } else {

            showLedgerNotice(
                'Global Media Library is loading or not available on this layout.',
                'Notice',
                'info'
            );

        }
    }


    /**
     * =========================================================
     * PRODUCT IMAGE ZOOM
     * =========================================================
     */
    function zoomProductImage() {

        const thumbImg = document.getElementById('productMasterThumb');
        const zoomImgTag = document.getElementById('modalZoomProductImgTag');
        const modalEl = document.getElementById('ledgerZoomModal');

        if (thumbImg && zoomImgTag && thumbImg.src && modalEl && window.bootstrap) {

            zoomImgTag.src = thumbImg.src;

            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }


    /**
     * =========================================================
     * IMAGE PREVIEW ERROR HANDLING
     * =========================================================
     */
    document.addEventListener('DOMContentLoaded', function() {

        const productImage = document.getElementById('productMasterThumb');

        if (productImage) {

            productImage.addEventListener('error', function() {

                if (!this.dataset.fallbackApplied) {

                    this.dataset.fallbackApplied = '1';
                    this.src = 'https://placehold.co/68x68?text=Item';

                }

            });
        }

    });
</script>

@endsection
