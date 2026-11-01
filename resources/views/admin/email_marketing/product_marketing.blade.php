@extends('layouts.admin')

@section('title', 'Product-Based Marketing - Email Marketing')

@php
    $totalProductsCount = $products->total();
    $inStockCount = $products->filter(fn($p) => (int)($p->stock ?? ($p->qty ?? 0)) > 5)->count();
    $lowStockCount = $products->filter(fn($p) => (int)($p->stock ?? ($p->qty ?? 0)) > 0 && (int)($p->stock ?? ($p->qty ?? 0)) <= 5)->count();
    $outOfStockCount = $products->filter(fn($p) => (int)($p->stock ?? ($p->qty ?? 0)) <= 0)->count();

    // Fail-safe thumbnail resolver
    $resolveThumb = function($path, $fallback = 'https://placehold.co/60x60?text=Product') {
        if (empty($path)) return $fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) return show_image($path, $fallback);
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset(ltrim(str_replace(['public_html/', 'public/'], '', $path), '/'));
    };

    $targetActionRoute = Route::has('admin.email_marketing.product_campaign.create') 
        ? route('admin.email_marketing.product_campaign.create') 
        : (Route::has('admin.email_marketing.campaigns.create_product_targeted') 
            ? route('admin.email_marketing.campaigns.create_product_targeted') 
            : url('admin/email-marketing/campaigns/create-product-targeted'));
@endphp

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-product-root .btn,
    #email-marketing-product-root .btn-sm,
    #email-marketing-product-root button,
    #email-marketing-product-root .badge,
    #email-marketing-product-root .alert,
    #email-marketing-product-root .form-control,
    #email-marketing-product-root .form-select,
    #email-marketing-product-root .input-group-text,
    #email-marketing-product-root .card,
    #email-marketing-product-root .metric-card,
    #email-marketing-product-root .product-thumb-box,
    #email-marketing-product-root .page-link,
    #email-marketing-product-root .page-item span,
    #email-marketing-product-root .collapse-toggle-btn,
    .product-modal-scope .modal-content,
    .product-modal-scope .btn {
        border-radius: 5px !important;
    }

    #email-marketing-product-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #email-marketing-product-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Safe Monospace & Bengali Digit Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Compact Sleek Button Styling */
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

    /* Enterprise Surface Cards */
    #email-marketing-product-root .card {
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }
    .section-header-wrap {
        padding: 10px 14px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
    }

    /* Metric Cards - Zero Overflow Architecture */
    .metric-card {
        padding: 12px 14px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }
    .stat-label-title {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.02em;
        line-height: 1.2;
    }
    .stat-val-num {
        font-size: 18px;
        line-height: 1.25;
        letter-spacing: -0.02em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .stat-sub-text {
        font-size: 10px;
        margin-top: 2px;
    }
    .metric-icon-square {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
        border-radius: 5px !important;
    }

    .metric-card-blue   { background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%); border-left: 3.5px solid #2563eb !important; }
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-left: 3.5px solid #10b981 !important; }
    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-left: 3.5px solid #f59e0b !important; }
    .metric-card-red    { background: linear-gradient(180deg, #ffffff 0%, #fef2f2 100%); border-left: 3.5px solid #ef4444 !important; }

    /* Product Thumbnail Box */
    .product-thumb-box {
        width: 44px;
        height: 44px;
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
        background: rgba(15, 23, 42, 0.45);
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
    .custom-products-table {
        width: 100%;
        margin-bottom: 0;
    }
    .custom-products-table thead th {
        font-size: 10px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 8px;
        white-space: nowrap;
    }
    .custom-products-table tbody td {
        padding: 8px 8px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        font-size: 11px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .custom-products-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .custom-products-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .custom-products-table th,
        .custom-products-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 6 Columns Proportional Distribution Summing to Exactly 100% */
        .col-pr-img    { width: 6% !important; }
        .col-pr-info   { width: 32% !important; }
        .col-pr-cat    { width: 14% !important; }
        .col-pr-price  { width: 15% !important; }
        .col-pr-stock  { width: 15% !important; }
        .col-pr-action { width: 18% !important; }
    }

    /* Form Controls */
    #email-marketing-product-root .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #email-marketing-product-root .form-control,
    #email-marketing-product-root .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #email-marketing-product-root .form-control:focus,
    #email-marketing-product-root .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
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
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25) !important;
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

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .product-header-wrap h5 {
            font-size: 16px !important;
        }
        .top-action-btns {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .top-action-btns .btn {
            width: 100%;
            padding: 8px 6px !important;
            font-size: 11px !important;
            justify-content: center;
        }
        .metric-card {
            padding: 10px !important;
            min-height: 84px !important;
        }
        .stat-label-title {
            font-size: 10px !important;
        }
        .stat-val-num {
            font-size: 14px !important;
        }
        .stat-sub-text {
            font-size: 9px !important;
        }
        .metric-icon-square {
            width: 28px !important;
            height: 28px !important;
            font-size: 11px !important;
        }
        .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .custom-products-table {
            min-width: 760px !important;
            table-layout: auto !important;
        }
        #email-marketing-product-root .card {
            margin-bottom: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-product-root">

    {{-- 1. Header & Navigation (Original Clean Light Theme) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 product-header-wrap">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-bullseye text-primary"></i>
                <span>Product-Targeted Email Marketing</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Select a specific product to target past buyers, launch cross-sell promotions, or announce stock arrivals.
            </small>
        </div>

        <div class="d-flex gap-2 top-action-btns">
            <a href="{{ route('admin.email_marketing.campaigns.create') }}" class="btn btn-sm btn-outline-primary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-paper-plane me-1"></i> <span>Standard Campaign</span>
            </a>
            <a href="{{ route('admin.email_marketing.dashboard') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-chart-pie me-1"></i> <span>Dashboard</span>
            </a>
        </div>
    </div>

    {{-- 2. Feedback Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 3. Telemetry KPI Overview (Collapsible Section) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-chart-pie text-primary me-1"></i> Catalog &amp; Targetable Products Overview
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseProductKpis" aria-expanded="true" aria-controls="collapseProductKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseProductKpis">
            <div class="p-3">
                <div class="row g-2">
                    {{-- Total Catalog --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Total Catalog</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                                    <i class="fas fa-boxes-stacked"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    {{ number_format($totalProductsCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Active store items</small>
                            </div>
                        </div>
                    </div>

                    {{-- In Stock Items --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">In Stock (Active)</span>
                                <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                                    <i class="fas fa-circle-check"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num">
                                    {{ number_format($inStockCount) }}
                                </h4>
                                <small class="text-success d-block text-truncate font-monospace stat-sub-text">Ready for broadcast</small>
                            </div>
                        </div>
                    </div>

                    {{-- Low Stock Items --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Low Stock</span>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                                    <i class="fas fa-triangle-exclamation"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-warning-emphasis bn-digit font-monospace stat-val-num">
                                    {{ number_format($lowStockCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Urgency promo items</small>
                            </div>
                        </div>
                    </div>

                    {{-- Out of Stock Items --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-red h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Out of Stock</span>
                                <div class="metric-icon-square bg-danger-subtle text-danger shadow-xs">
                                    <i class="fas fa-ban"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    {{ number_format($outOfStockCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Restock notification focus</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Instant Live Search & Filter Toolbar (Collapsible) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-filter text-primary me-1"></i> Filter Products by Name, SKU, Category &amp; Stock Status
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseProductFilter" aria-expanded="true" aria-controls="collapseProductFilter" title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseProductFilter">
            <div class="p-3">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-5">
                        <label class="form-label" for="productSearchInput">
                            <i class="fas fa-search me-1 text-primary"></i> Search Product Name or SKU
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                            <input type="text" id="productSearchInput" class="form-control border-start-0 ps-0 shadow-none font-monospace" placeholder="Georgette Dress, SKU-1049..." onkeyup="filterProductsTable()">
                        </div>
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label" for="productStockFilter">
                            <i class="fas fa-warehouse me-1 text-success"></i> Stock Status
                        </label>
                        <select id="productStockFilter" class="form-select shadow-none font-monospace" onchange="filterProductsTable()">
                            <option value="all" selected>All Stock Conditions</option>
                            <option value="instock">In Stock Only (> 5)</option>
                            <option value="lowstock">Low Stock Only (1 - 5)</option>
                            <option value="outofstock">Out of Stock (0)</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <button type="button" class="btn btn-light border btn-compact w-100 shadow-xs fw-semibold text-secondary" onclick="resetProductFilters()">
                            <i class="fas fa-rotate-left me-1"></i> <span>Reset Filters</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. Products Table Card (Desktop 100% Fit Screen with Zero Horizontal Scroll) --}}
    <div class="card border-0 shadow-sm overflow-hidden mb-3">
        <div class="p-2 px-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 bg-light bg-opacity-25">
            <div>
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13px;">
                    <i class="fas fa-box-open text-primary"></i> <span>Select Product to Target Audience</span>
                </h6>
                <small class="text-muted font-monospace bn-digit" style="font-size: 10px;">
                    Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{$products->total() }} products
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 10px;" id="visible-products-count">
                    {{ $products->total() }} Total
                </span>
                <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseProductsTable" aria-expanded="true" aria-controls="collapseProductsTable" title="Toggle Table">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseProductsTable">
            <div class="table-responsive pc-fit-container">
                <table class="table align-middle mb-0 custom-products-table" id="products-table">
                    <thead>
                        <tr>
                            <th class="ps-3 col-pr-img text-center">Image</th>
                            <th class="col-pr-info">Product Details</th>
                            <th class="col-pr-cat">Category</th>
                            <th class="col-pr-price">Price</th>
                            <th class="col-pr-stock">Stock Status</th>
                            <th class="text-end pe-3 col-pr-action">Marketing Action</th>
                        </tr>
                    </thead>
                    <tbody id="products-tbody">
                        @forelse($products as $product)
                            @php
                                $img = $product->thumbnail ?? ($product->image ?? null);
                                $thumbUrl =$resolveThumb($img);$pTitle = $product->title ?? ($product->name ?? 'Product #' . $product->id);$pSku = $product->sku ?? ($product->product_code ?? 'N/A');

                                // Smart Price Resolution (MRP, Sale Price, Regular Price)
                                $mrpPrice = (float)($product->mrp ?? ($product->regular_price ?? 0));
                                $salePrice = (float)($product->sale_price ?? ($product->offer_price ?? 0));
                                
                                $resolvedPrice = 0.00;
                                if ($salePrice > 0) {
                                    $resolvedPrice =$salePrice;
                                } elseif ($mrpPrice > 0) {
                                    $resolvedPrice =$mrpPrice;
                                } else {
                                    $resolvedPrice = (float)($product->price ?? ($product->selling_price ?? ($product->unit_price ?? 0)));
                                }

                                // Check variants table if available
                                if ($resolvedPrice <= 0 && \Illuminate\Support\Facades\Schema::hasTable('product_variants')) {$varPrice = \Illuminate\Support\Facades\DB::table('product_variants')
                                        ->where('product_id', $product->id)
                                        ->value('price') 
                                        ?? \Illuminate\Support\Facades\DB::table('product_variants')->where('product_id', $product->id)->value('mrp');
                                    if ($varPrice) {
                                        $resolvedPrice = (float)$varPrice;
                                    }
                                }

                                $stock = (int)($product->stock ?? ($product->qty ?? 0));
                                $stockSlug = $stock > 5 ? 'instock' : ($stock > 0 ? 'lowstock' : 'outofstock');

                                $categoryName = optional($product->category)->name ?? 'General';

                                $rowSearchData = strtolower(
                                    $pTitle . ' ' .$pSku . ' ' .
                                    $categoryName . ' ' .$stockSlug
                                );
                            @endphp
                            <tr class="product-entry-row" data-search="{{ $rowSearchData }}" data-stock="{{ $stockSlug }}">
                                <td class="ps-3 col-pr-img text-center">
                                    <div class="product-thumb-box shadow-xs mx-auto" onclick="zoomProductPhoto('{{ $thumbUrl }}', '{{ addslashes($pTitle) }}')" title="Click to Zoom Image">
                                        <img src="{{ $thumbUrl }}" alt="{{ $pTitle }}" onerror="this.src='https://placehold.co/60x60?text=Product';">
                                        <div class="product-thumb-hover-overlay">
                                            <i class="fas fa-search-plus text-white" style="font-size: 9px;"></i>
                                        </div>
                                    </div>
                                </td>
                                <td class="col-pr-info">
                                    <div class="min-w-0" style="line-height: 1.3;">
                                        <strong class="text-dark d-block text-truncate" style="font-size: 12px;" title="{{ $pTitle }}">
                                            {{ $pTitle }}
                                        </strong>
                                        <small class="text-muted font-monospace d-block text-truncate" style="font-size: 10px;">
                                            SKU: {{ $pSku }} &bull; ID: #{{ $product->id }}
                                        </small>
                                    </div>
                                </td>
                                <td class="col-pr-cat">
                                    <span class="badge bg-light text-secondary border font-monospace text-truncate d-inline-block px-2 py-0" style="font-size: 10px; max-width: 100%; border-radius: 5px !important;" title="{{ $categoryName }}">
                                        {{ $categoryName }}
                                    </span>
                                </td>
                                <td class="col-pr-price font-monospace bn-digit">
                                    @if($salePrice > 0 && $mrpPrice >$salePrice)
                                        <div style="line-height: 1.25;">
                                            <strong class="text-dark d-block" style="font-size: 12px;">৳ {{ number_format($salePrice, 2) }}</strong>
                                            <small class="text-muted text-decoration-line-through d-block" style="font-size: 9.5px;">৳ {{ number_format($mrpPrice, 2) }}</small>
                                        </div>
                                    @else
                                        <strong class="text-dark font-monospace" style="font-size: 12px;">
                                            ৳ {{ number_format($resolvedPrice, 2) }}
                                        </strong>
                                    @endif
                                </td>
                                <td class="col-pr-stock">
                                    @if($stock > 5)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 9.5px;">
                                            <i class="fas fa-circle-check me-1"></i> {{ $stock }} in stock
                                        </span>
                                    @elseif($stock > 0)
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 9.5px;">
                                            <i class="fas fa-triangle-exclamation me-1"></i> Low ({{ $stock }})
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 9.5px;">
                                            <i class="fas fa-ban me-1"></i> Out of stock
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-3 col-pr-action">
                                    <form action="{{ $targetActionRoute }}" method="GET" class="d-inline">
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <button type="submit" class="btn btn-sm btn-primary btn-compact shadow-xs fw-semibold" title="Create Campaign for Previous Buyers">
                                            <i class="fas fa-bullseye me-1"></i> <span>Target Audience</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyProductsRow">
                                <td colspan="6" class="text-center py-5 text-muted small">
                                    <i class="fas fa-box-open fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                    <span class="fw-semibold">No active products found to target.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Bulletproof Responsive Pagination Footer --}}
            @if($products->hasPages())
                <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">
                    <small class="text-muted font-monospace bn-digit" style="font-size: 11px;">
                        Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{$products->total() }} products
                    </small>
                    <div>
                        {{ $products->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Lightbox Zoom Modal for Product Photo -->
<div class="modal fade product-modal-scope" id="productPhotoZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12px;" id="productZoomTitle">
                    <i class="fas fa-image text-primary me-1"></i> Product Preview
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="productZoomImgTag" src="" alt="Product Photo" class="img-fluid border" style="max-height: 380px; object-fit: contain; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<script>
    function zoomProductPhoto(url, title = 'Product Preview') {
        const titleEl = document.getElementById('productZoomTitle');
        const imgTag  = document.getElementById('productZoomImgTag');
        if (url && imgTag) {
            if (titleEl) titleEl.innerText = title;
            imgTag.src = url;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('productPhotoZoomModal')).show();
        }
    }

    function filterProductsTable() {
        const query = (document.getElementById('productSearchInput')?.value || '').toLowerCase().trim();
        const stock = (document.getElementById('productStockFilter')?.value || 'all').toLowerCase();

        const rows = document.querySelectorAll('.product-entry-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowSearch = row.getAttribute('data-search') || '';
            const rowStock  = row.getAttribute('data-stock') || '';

            const matchesQuery = !query || rowSearch.includes(query);
            const matchesStock = stock === 'all' || rowStock === stock;

            if (matchesQuery && matchesStock) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const countBadge = document.getElementById('visible-products-count');
        if (countBadge) {
            countBadge.innerText = `${visibleCount} Showing`;
        }
    }

    function resetProductFilters() {
        const searchInput = document.getElementById('productSearchInput');
        const stockFilter = document.getElementById('productStockFilter');
        if (searchInput) searchInput.value = '';
        if (stockFilter) stockFilter.value = 'all';
        filterProductsTable();
    }
</script>
@endsection