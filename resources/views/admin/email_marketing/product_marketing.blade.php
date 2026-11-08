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
    /* =========================================================
       PRODUCT MARKETING - ENTERPRISE UI
       Bootstrap 5 | Strict 5px Radius | Responsive
    ========================================================= */

    #email-marketing-product-root {
        --pm-primary: #2563eb;
        --pm-primary-dark: #1d4ed8;
        --pm-text: #172033;
        --pm-muted: #64748b;
        --pm-border: #e2e8f0;
        --pm-surface: #ffffff;
        --pm-soft: #f8fafc;
        --pm-radius: 5px;
        color: var(--pm-text);
        font-size: 12px;
    }

    #email-marketing-product-root *,
    #productPhotoZoomModal * {
        border-radius: 5px;
    }

    #email-marketing-product-root .btn,
    #email-marketing-product-root .badge,
    #email-marketing-product-root .alert,
    #email-marketing-product-root .form-control,
    #email-marketing-product-root .form-select,
    #email-marketing-product-root .input-group-text,
    #email-marketing-product-root .card,
    #email-marketing-product-root .page-link,
    #email-marketing-product-root .product-thumb-box,
    #email-marketing-product-root .collapse-toggle-btn,
    #productPhotoZoomModal .modal-content,
    #productPhotoZoomModal .btn {
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

    #email-marketing-product-root .font-monospace,
    #email-marketing-product-root .bn-digit {
        font-weight: 600;
    }

    /* Header */
    #email-marketing-product-root .product-header-wrap {
        gap: 12px;
    }

    #email-marketing-product-root .page-title {
        font-size: 18px;
        font-weight: 750;
        letter-spacing: -0.35px;
        color: #172033;
    }

    #email-marketing-product-root .page-description {
        font-size: 11.5px;
        color: #64748b;
        line-height: 1.6;
        max-width: 650px;
    }

    /* Compact Buttons */
    #email-marketing-product-root .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        white-space: nowrap;
        font-size: 11px;
        font-weight: 650;
        padding: 6px 11px;
        line-height: 1.35;
        transition: all .18s ease;
    }

    #email-marketing-product-root .btn-primary {
        background: var(--pm-primary);
        border-color: var(--pm-primary);
    }

    #email-marketing-product-root .btn-primary:hover {
        background: var(--pm-primary-dark);
        border-color: var(--pm-primary-dark);
        transform: translateY(-1px);
    }

    #email-marketing-product-root .btn-outline-primary:hover {
        transform: translateY(-1px);
    }

    #email-marketing-product-root .top-action-btns {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }

    /* Alerts */
    #email-marketing-product-root .alert {
        border: 1px solid transparent !important;
        font-size: 11px;
    }

    #email-marketing-product-root .alert-success {
        background: #f0fdf4;
        border-color: #bbf7d0 !important;
    }

    #email-marketing-product-root .alert-danger {
        background: #fef2f2;
        border-color: #fecaca !important;
    }

    /* Main Cards */
    #email-marketing-product-root .card {
        background: var(--pm-surface);
        border: 1px solid var(--pm-border) !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
        margin-bottom: 14px;
        overflow: hidden;
    }

    #email-marketing-product-root .section-header-wrap {
        min-height: 42px;
        padding: 9px 13px;
        background: #fff;
        border-bottom: 1px solid var(--pm-border);
    }

    #email-marketing-product-root .section-title {
        font-size: 11.5px;
        font-weight: 750;
        color: #334155;
        letter-spacing: .01em;
    }

    /* Collapse Button */
    #email-marketing-product-root .collapse-toggle-btn {
        width: 25px;
        height: 25px;
        padding: 0;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        background: #fff;
        color: #64748b;
        border: 1px solid #cbd5e1;
        transition: all .2s ease;
        flex-shrink: 0;
    }

    #email-marketing-product-root .collapse-toggle-btn:hover {
        background: var(--pm-primary);
        border-color: var(--pm-primary);
        color: #fff;
    }

    #email-marketing-product-root .collapse-icon {
        font-size: 10px;
        transition: transform .25s ease;
    }

    #email-marketing-product-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* KPI Cards */
    #email-marketing-product-root .metric-card {
        min-height: 105px;
        height: 100%;
        padding: 13px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        transition: transform .18s ease, box-shadow .18s ease;
        border: 1px solid var(--pm-border) !important;
    }

    #email-marketing-product-root .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(15, 23, 42, .07);
    }

    #email-marketing-product-root .metric-card-blue {
        background: linear-gradient(145deg, #fff 0%, #eff6ff 100%);
        border-left: 3px solid #2563eb !important;
    }

    #email-marketing-product-root .metric-card-green {
        background: linear-gradient(145deg, #fff 0%, #f0fdf4 100%);
        border-left: 3px solid #10b981 !important;
    }

    #email-marketing-product-root .metric-card-amber {
        background: linear-gradient(145deg, #fff 0%, #fffbeb 100%);
        border-left: 3px solid #f59e0b !important;
    }

    #email-marketing-product-root .metric-card-red {
        background: linear-gradient(145deg, #fff 0%, #fef2f2 100%);
        border-left: 3px solid #ef4444 !important;
    }

    #email-marketing-product-root .stat-label-title {
        font-size: 10.5px;
        font-weight: 750;
        color: #64748b;
        line-height: 1.35;
    }

    #email-marketing-product-root .stat-val-num {
        font-size: 21px;
        font-weight: 800;
        line-height: 1.2;
        letter-spacing: -.5px;
        color: #172033;
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    #email-marketing-product-root .stat-sub-text {
        display: block;
        font-size: 9.5px;
        margin-top: 4px;
        color: #64748b;
    }

    #email-marketing-product-root .metric-icon-square {
        width: 32px;
        height: 32px;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        flex-shrink: 0;
        font-size: 13px;
    }

    /* Filter Form */
    #email-marketing-product-root .form-label {
        display: block;
        font-size: 10.5px;
        font-weight: 750;
        color: #475569;
        margin-bottom: 5px;
    }

    #email-marketing-product-root .form-control,
    #email-marketing-product-root .form-select {
        min-height: 35px;
        padding: 6px 10px;
        font-size: 11.5px;
        color: #1e293b;
        border: 1px solid #cbd5e1;
        background-color: #fff;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #email-marketing-product-root .form-control:focus,
    #email-marketing-product-root .form-select:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
    }

    #email-marketing-product-root .input-group-text {
        min-width: 35px;
        justify-content: center;
        font-size: 11px;
        border-color: #cbd5e1;
    }

    /* Table */
    #email-marketing-product-root .custom-products-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #email-marketing-product-root .custom-products-table thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: 9.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .045em;
        padding: 11px 9px;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
        vertical-align: middle;
    }

    #email-marketing-product-root .custom-products-table tbody td {
        padding: 9px;
        font-size: 11px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        white-space: nowrap;
    }

    #email-marketing-product-root .custom-products-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #email-marketing-product-root .custom-products-table tbody tr {
        transition: background-color .15s ease;
    }

    #email-marketing-product-root .custom-products-table tbody tr:hover {
        background: #f8fbff;
    }

    /* Product Thumbnail */
    #email-marketing-product-root .product-thumb-box {
        width: 43px;
        height: 43px;
        border: 1px solid #e2e8f0;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
        cursor: zoom-in;
        flex-shrink: 0;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #email-marketing-product-root .product-thumb-box:hover {
        border-color: #93c5fd;
        box-shadow: 0 2px 7px rgba(37, 99, 235, .12);
    }

    #email-marketing-product-root .product-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #email-marketing-product-root .product-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .48);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity .15s ease;
        pointer-events: none;
    }

    #email-marketing-product-root .product-thumb-box:hover .product-thumb-hover-overlay {
        opacity: 1;
    }

    /* Product Info */
    #email-marketing-product-root .product-name {
        font-size: 11.5px;
        font-weight: 750;
        color: #1e293b;
        max-width: 100%;
    }

    #email-marketing-product-root .product-meta {
        color: #94a3b8;
        font-size: 9.5px;
        margin-top: 3px;
    }

    #email-marketing-product-root .stock-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 9.5px;
        font-weight: 700;
        padding: 5px 7px;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    #email-marketing-product-root .category-badge {
        max-width: 100%;
        font-size: 9.5px;
        padding: 4px 7px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
    }

    /* Table Width Architecture */
    @media (min-width: 992px) {
        #email-marketing-product-root .custom-products-table {
            table-layout: fixed;
        }

        #email-marketing-product-root .pc-fit-container {
            overflow-x: hidden;
        }

        #email-marketing-product-root .custom-products-table th,
        #email-marketing-product-root .custom-products-table td {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #email-marketing-product-root .col-pr-img    { width: 7%; }
        #email-marketing-product-root .col-pr-info   { width: 31%; }
        #email-marketing-product-root .col-pr-cat    { width: 14%; }
        #email-marketing-product-root .col-pr-price  { width: 14%; }
        #email-marketing-product-root .col-pr-stock  { width: 16%; }
        #email-marketing-product-root .col-pr-action { width: 18%; }
    }

    /* Pagination */
    #email-marketing-product-root .card-footer nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }

    #email-marketing-product-root .card-footer nav > div.d-sm-none,
    #email-marketing-product-root .card-footer nav > .d-sm-none {
        display: none !important;
    }

    #email-marketing-product-root .card-footer nav > div.d-none,
    #email-marketing-product-root .card-footer nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #email-marketing-product-root .card-footer nav p,
    #email-marketing-product-root .card-footer nav .small.text-muted,
    #email-marketing-product-root .card-footer nav div:has(> p),
    #email-marketing-product-root .card-footer nav > div.d-none > div:first-child,
    #email-marketing-product-root .card-footer nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }

    #email-marketing-product-root .card-footer .pagination {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        padding: 0 !important;
        margin: 0 !important;
        list-style: none !important;
    }

    #email-marketing-product-root .card-footer .page-item {
        display: inline-flex !important;
    }

    #email-marketing-product-root .card-footer .page-item .page-link,
    #email-marketing-product-root .card-footer .page-item span {
        min-width: 27px !important;
        height: 27px !important;
        padding: 0 7px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 10.5px !important;
        font-weight: 650 !important;
        line-height: 1 !important;
        color: #475569 !important;
        background: #fff !important;
        border: 1px solid #cbd5e1 !important;
        text-decoration: none !important;
    }

    #email-marketing-product-root .card-footer .page-item.active .page-link,
    #email-marketing-product-root .card-footer .page-item.active span {
        background: #2563eb !important;
        border-color: #2563eb !important;
        color: #fff !important;
    }

    #email-marketing-product-root .card-footer .page-item.disabled .page-link,
    #email-marketing-product-root .card-footer .page-item.disabled span {
        opacity: .4 !important;
        background: #f8fafc !important;
        cursor: not-allowed !important;
    }

    #email-marketing-product-root .card-footer nav svg {
        width: 11px !important;
        height: 11px !important;
    }

    /* Modal */
    #productPhotoZoomModal {
        z-index: 108000;
    }

    #productPhotoZoomModal .modal-content {
        border: 1px solid #e2e8f0;
        background: #fff;
    }

    #productPhotoZoomModal .modal-header {
        padding: 12px 14px 5px;
    }

    #productPhotoZoomModal .modal-body {
        padding: 12px;
    }

    #productPhotoZoomModal #productZoomImgTag {
        max-height: 380px;
        max-width: 100%;
        object-fit: contain;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        #email-marketing-product-root {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #email-marketing-product-root .page-title {
            font-size: 16px;
        }

        #email-marketing-product-root .page-description {
            font-size: 10.5px;
        }

        #email-marketing-product-root .top-action-btns {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 7px;
        }

        #email-marketing-product-root .top-action-btns .btn {
            width: 100%;
            min-height: 34px;
            padding: 7px 5px;
            font-size: 10px;
        }

        #email-marketing-product-root .metric-card {
            min-height: 88px;
            padding: 10px;
        }

        #email-marketing-product-root .stat-label-title {
            font-size: 9.5px;
        }

        #email-marketing-product-root .stat-val-num {
            font-size: 17px;
        }

        #email-marketing-product-root .stat-sub-text {
            font-size: 9px;
        }

        #email-marketing-product-root .metric-icon-square {
            width: 27px;
            height: 27px;
            font-size: 11px;
        }

        #email-marketing-product-root .section-header-wrap {
            padding: 9px 10px;
        }

        #email-marketing-product-root .section-title {
            font-size: 10.5px;
        }

        #email-marketing-product-root .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        #email-marketing-product-root .custom-products-table {
            min-width: 760px;
            table-layout: auto;
        }

        #email-marketing-product-root .custom-products-table thead th {
            padding: 10px 8px;
        }

        #email-marketing-product-root .custom-products-table tbody td {
            padding: 8px;
        }

        #email-marketing-product-root .card-footer {
            padding: 10px !important;
        }

        #email-marketing-product-root .card-footer > div {
            width: 100%;
            justify-content: center !important;
        }
    }

    @media (max-width: 380px) {
        #email-marketing-product-root .metric-card {
            padding: 8px;
        }

        #email-marketing-product-root .stat-val-num {
            font-size: 15px;
        }

        #email-marketing-product-root .stat-label-title {
            font-size: 9px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-product-root">

    {{-- 1. Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 product-header-wrap">

        <div class="min-w-0">
            <h5 class="page-title mb-1 d-flex align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary"
                      style="width:32px;height:32px;border-radius:5px;">
                    <i class="fas fa-bullseye"></i>
                </span>
                <span>Product-Targeted Email Marketing</span>
            </h5>

            <p class="page-description mb-0">
                Select a specific product to target past buyers, launch cross-sell promotions, or announce stock arrivals.
            </p>
        </div>

        <div class="top-action-btns mt-2 mt-md-0">
            <a href="{{ route('admin.email_marketing.campaigns.create') }}"
               class="btn btn-sm btn-outline-primary btn-compact">
                <i class="fas fa-paper-plane"></i>
                <span>Standard Campaign</span>
            </a>

            <a href="{{ route('admin.email_marketing.dashboard') }}"
               class="btn btn-sm btn-outline-secondary btn-compact">
                <i class="fas fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>
        </div>
    </div>

    {{-- 2. Feedback Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 mb-3 d-flex align-items-center justify-content-between shadow-sm"
             role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-6"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 mb-3 d-flex align-items-center justify-content-between shadow-sm"
             role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle text-danger fs-6"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 3. Catalog KPI Overview --}}
    <div class="card mb-3">

        <div class="section-header-wrap d-flex justify-content-between align-items-center">
            <div class="section-title">
                <i class="fas fa-chart-pie text-primary me-1"></i>
                Catalog &amp; Targetable Products Overview
            </div>

            <button type="button"
                    class="collapse-toggle-btn no-print"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseProductKpis"
                    aria-expanded="true"
                    aria-controls="collapseProductKpis"
                    title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseProductKpis">
            <div class="p-3">
                <div class="row g-2">

                    {{-- Total Catalog --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="stat-label-title text-truncate">Total Catalog</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary">
                                    <i class="fas fa-boxes-stacked"></i>
                                </div>
                            </div>

                            <div>
                                <h4 class="stat-val-num font-monospace">
                                    {{ number_format($totalProductsCount) }}
                                </h4>
                                <small class="stat-sub-text">Active store items</small>
                            </div>
                        </div>
                    </div>

                    {{-- In Stock --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="stat-label-title text-truncate">In Stock (Active)</span>
                                <div class="metric-icon-square bg-success-subtle text-success">
                                    <i class="fas fa-circle-check"></i>
                                </div>
                            </div>

                            <div>
                                <h4 class="stat-val-num text-success font-monospace">
                                    {{ number_format($inStockCount) }}
                                </h4>
                                <small class="stat-sub-text text-success">Ready for broadcast</small>
                            </div>
                        </div>
                    </div>

                    {{-- Low Stock --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="stat-label-title text-truncate">Low Stock</span>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis">
                                    <i class="fas fa-triangle-exclamation"></i>
                                </div>
                            </div>

                            <div>
                                <h4 class="stat-val-num text-warning-emphasis font-monospace">
                                    {{ number_format($lowStockCount) }}
                                </h4>
                                <small class="stat-sub-text">Urgency promo items</small>
                            </div>
                        </div>
                    </div>

                    {{-- Out of Stock --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-red mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="stat-label-title text-truncate">Out of Stock</span>
                                <div class="metric-icon-square bg-danger-subtle text-danger">
                                    <i class="fas fa-ban"></i>
                                </div>
                            </div>

                            <div>
                                <h4 class="stat-val-num font-monospace">
                                    {{ number_format($outOfStockCount) }}
                                </h4>
                                <small class="stat-sub-text">Restock notification focus</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- 4. Product Search & Filter --}}
    <div class="card mb-3">

        <div class="section-header-wrap d-flex justify-content-between align-items-center">
            <div class="section-title">
                <i class="fas fa-filter text-primary me-1"></i>
                Filter Products by Name, SKU, Category &amp; Stock Status
            </div>

            <button type="button"
                    class="collapse-toggle-btn no-print"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseProductFilter"
                    aria-expanded="true"
                    aria-controls="collapseProductFilter"
                    title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseProductFilter">
            <div class="p-3">
                <div class="row g-3 align-items-end">

                    <div class="col-12 col-md-5">
                        <label class="form-label" for="productSearchInput">
                            <i class="fas fa-search me-1 text-primary"></i>
                            Search Product Name or SKU
                        </label>

                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">
                                <i class="fas fa-search"></i>
                            </span>

                            <input type="text"
                                   id="productSearchInput"
                                   class="form-control font-monospace"
                                   placeholder="Georgette Dress, SKU-1049..."
                                   onkeyup="filterProductsTable()">
                        </div>
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label" for="productStockFilter">
                            <i class="fas fa-warehouse me-1 text-success"></i>
                            Stock Status
                        </label>

                        <select id="productStockFilter"
                                class="form-select font-monospace"
                                onchange="filterProductsTable()">
                            <option value="all" selected>All Stock Conditions</option>
                            <option value="instock">In Stock Only (&gt; 5)</option>
                            <option value="lowstock">Low Stock Only (1 - 5)</option>
                            <option value="outofstock">Out of Stock (0)</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <button type="button"
                                class="btn btn-light border btn-compact w-100"
                                style="min-height:35px;"
                                onclick="resetProductFilters()">
                            <i class="fas fa-rotate-left"></i>
                            Reset Filters
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- 5. Products Table --}}
    <div class="card mb-3">

        <div class="p-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 bg-light bg-opacity-25">

            <div class="min-w-0">
                <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size:12px;">
                    <i class="fas fa-box-open text-primary"></i>
                    Select Product to Target Audience
                </h6>

                <small class="text-muted font-monospace bn-digit" style="font-size:10px;">
                    Showing {{ $products->firstItem() ?? 0 }}
                    to {{ $products->lastItem() ?? 0 }}
                    of {{ $products->total() }} products
                </small>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-white text-secondary border font-monospace px-2 py-2"
                      style="font-size:10px;"
                      id="visible-products-count">
                    {{ $products->total() }} Total
                </span>

                <button type="button"
                        class="collapse-toggle-btn no-print"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseProductsTable"
                        aria-expanded="true"
                        aria-controls="collapseProductsTable"
                        title="Toggle Table">
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
                                $thumbUrl = $resolveThumb($img);

                                $pTitle = $product->title ?? ($product->name ?? 'Product #' . $product->id);
                                $pSku = $product->sku ?? ($product->product_code ?? 'N/A');

                                // Smart Price Resolution
                                $mrpPrice = (float)($product->mrp ?? ($product->regular_price ?? 0));
                                $salePrice = (float)($product->sale_price ?? ($product->offer_price ?? 0));

                                $resolvedPrice = 0.00;

                                if ($salePrice > 0) {
                                    $resolvedPrice = $salePrice;
                                } elseif ($mrpPrice > 0) {
                                    $resolvedPrice = $mrpPrice;
                                } else {
                                    $resolvedPrice = (float)($product->price ?? ($product->selling_price ?? ($product->unit_price ?? 0)));
                                }

                                // Check variants table if available
                                if ($resolvedPrice <= 0 && \Illuminate\Support\Facades\Schema::hasTable('product_variants')) {
                                    $varPrice = \Illuminate\Support\Facades\DB::table('product_variants')
                                        ->where('product_id', $product->id)
                                        ->value('price')
                                        ?? \Illuminate\Support\Facades\DB::table('product_variants')
                                            ->where('product_id', $product->id)
                                            ->value('mrp');

                                    if ($varPrice) {
                                        $resolvedPrice = (float)$varPrice;
                                    }
                                }

                                $stock = (int)($product->stock ?? ($product->qty ?? 0));

                                $stockSlug = $stock > 5
                                    ? 'instock'
                                    : ($stock > 0 ? 'lowstock' : 'outofstock');

                                $categoryName = optional($product->category)->name ?? 'General';

                                $rowSearchData = strtolower(
                                    $pTitle . ' ' .
                                    $pSku . ' ' .
                                    $categoryName . ' ' .
                                    $stockSlug
                                );
                            @endphp

                            <tr class="product-entry-row"
                                data-search="{{ $rowSearchData }}"
                                data-stock="{{ $stockSlug }}">

                                {{-- Product Image --}}
                                <td class="ps-3 col-pr-img text-center">
                                    <div class="product-thumb-box mx-auto"
                                         onclick="zoomProductPhoto(@js($thumbUrl), @js($pTitle))"
                                         title="Click to Zoom Image">

                                        <img src="{{ $thumbUrl }}"
                                             alt="{{ $pTitle }}"
                                             loading="lazy"
                                             onerror="this.onerror=null;this.src='https://placehold.co/60x60?text=Product';">

                                        <div class="product-thumb-hover-overlay">
                                            <i class="fas fa-search-plus text-white" style="font-size:10px;"></i>
                                        </div>
                                    </div>
                                </td>

                                {{-- Product Details --}}
                                <td class="col-pr-info">
                                    <div class="min-w-0" style="line-height:1.35;">
                                        <strong class="product-name d-block text-truncate"
                                                title="{{ $pTitle }}">
                                            {{ $pTitle }}
                                        </strong>

                                        <small class="product-meta font-monospace d-block text-truncate">
                                            SKU: {{ $pSku }}
                                            <span class="mx-1">&bull;</span>
                                            ID: #{{ $product->id }}
                                        </small>
                                    </div>
                                </td>

                                {{-- Category --}}
                                <td class="col-pr-cat">
                                    <span class="badge bg-light text-secondary border category-badge"
                                          title="{{ $categoryName }}">
                                        {{ $categoryName }}
                                    </span>
                                </td>

                                {{-- Price --}}
                                <td class="col-pr-price font-monospace bn-digit">

                                    @if($salePrice > 0 && $mrpPrice > $salePrice)

                                        <div style="line-height:1.4;">
                                            <strong class="text-dark d-block" style="font-size:12px;">
                                                ৳ {{ number_format($salePrice, 2) }}
                                            </strong>

                                            <small class="text-muted text-decoration-line-through d-block"
                                                   style="font-size:9.5px;">
                                                ৳ {{ number_format($mrpPrice, 2) }}
                                            </small>
                                        </div>

                                    @else

                                        <strong class="text-dark" style="font-size:12px;">
                                            ৳ {{ number_format($resolvedPrice, 2) }}
                                        </strong>

                                    @endif

                                </td>

                                {{-- Stock Status --}}
                                <td class="col-pr-stock">

                                    @if($stock > 5)

                                        <span class="stock-badge bg-success-subtle text-success border-success-subtle">
                                            <i class="fas fa-circle-check"></i>
                                            {{ $stock }} in stock
                                        </span>

                                    @elseif($stock > 0)

                                        <span class="stock-badge bg-warning-subtle text-warning-emphasis border-warning-subtle">
                                            <i class="fas fa-triangle-exclamation"></i>
                                            Low ({{ $stock }})
                                        </span>

                                    @else

                                        <span class="stock-badge bg-danger-subtle text-danger border-danger-subtle">
                                            <i class="fas fa-ban"></i>
                                            Out of stock
                                        </span>

                                    @endif

                                </td>

                                {{-- Marketing Action --}}
                                <td class="text-end pe-3 col-pr-action">

                                    <form action="{{ $targetActionRoute }}"
                                          method="GET"
                                          class="d-inline">

                                        <input type="hidden"
                                               name="product_id"
                                               value="{{ $product->id }}">

                                        <button type="submit"
                                                class="btn btn-sm btn-primary btn-compact"
                                                title="Create Campaign for Previous Buyers">
                                            <i class="fas fa-bullseye"></i>
                                            <span>Target Audience</span>
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr id="emptyProductsRow">
                                <td colspan="6" class="text-center py-5 text-muted">

                                    <div class="mb-2">
                                        <i class="fas fa-box-open fa-2x opacity-25"></i>
                                    </div>

                                    <div class="fw-semibold" style="font-size:12px;">
                                        No active products found to target.
                                    </div>

                                    <small class="text-muted d-block mt-1">
                                        Products will appear here when available.
                                    </small>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($products->hasPages())

                <div class="p-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">

                    <small class="text-muted font-monospace bn-digit" style="font-size:10.5px;">
                        Showing {{ $products->firstItem() }}
                        to {{ $products->lastItem() }}
                        of {{ $products->total() }} products
                    </small>

                    <div>
                        {{ $products->links('pagination::bootstrap-5') }}
                    </div>

                </div>

            @endif

        </div>
    </div>

</div>

{{-- 6. Product Image Preview Modal --}}
<div class="modal fade"
     id="productPhotoZoomModal"
     tabindex="-1"
     aria-labelledby="productZoomTitle"
     aria-hidden="true"
     style="z-index:108000;">

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content shadow-lg">

            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">

                <span class="fw-bold text-dark" style="font-size:12px;" id="productZoomTitle">
                    <i class="fas fa-image text-primary me-1"></i>
                    Product Preview
                </span>

                <button type="button"
                        class="btn-close shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>

            </div>

            <div class="modal-body text-center">

                <img id="productZoomImgTag"
                     src=""
                     alt="Product Photo"
                     class="img-fluid">

            </div>

        </div>
    </div>
</div>

<script>
    /* =========================================================
       PRODUCT MARKETING - INTERACTIVE FUNCTIONS
    ========================================================= */

    /**
     * Product Image Zoom
     */
    function zoomProductPhoto(url, title = 'Product Preview') {
        const modalEl = document.getElementById('productPhotoZoomModal');
        const titleEl = document.getElementById('productZoomTitle');
        const imgTag = document.getElementById('productZoomImgTag');

        if (!url || !imgTag || !modalEl || typeof bootstrap === 'undefined') {
            return;
        }

        if (titleEl) {
            titleEl.textContent = title || 'Product Preview';
        }

        imgTag.onerror = function () {
            this.onerror = null;
            this.src = 'https://placehold.co/400x400?text=Product';
        };

        imgTag.src = url;

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    /**
     * Live Product Search & Stock Filtering
     */
    function filterProductsTable() {
        const searchInput = document.getElementById('productSearchInput');
        const stockFilter = document.getElementById('productStockFilter');

        const query = (searchInput?.value || '').toLowerCase().trim();
        const stock = (stockFilter?.value || 'all').toLowerCase();

        const rows = document.querySelectorAll(
            '#email-marketing-product-root .product-entry-row'
        );

        let visibleCount = 0;

        rows.forEach(row => {
            const rowSearch = (row.getAttribute('data-search') || '').toLowerCase();
            const rowStock = (row.getAttribute('data-stock') || '').toLowerCase();

            const matchesQuery = !query || rowSearch.includes(query);
            const matchesStock = stock === 'all' || rowStock === stock;

            const isVisible = matchesQuery && matchesStock;

            row.style.display = isVisible ? '' : 'none';

            if (isVisible) {
                visibleCount++;
            }
        });

        const countBadge = document.getElementById('visible-products-count');

        if (countBadge) {
            countBadge.textContent = `${visibleCount} Showing`;
        }
    }

    /**
     * Reset All Product Filters
     */
    function resetProductFilters() {
        const searchInput = document.getElementById('productSearchInput');
        const stockFilter = document.getElementById('productStockFilter');

        if (searchInput) {
            searchInput.value = '';
        }

        if (stockFilter) {
            stockFilter.value = 'all';
        }

        filterProductsTable();
    }

    /**
     * Initialize Page Interactions
     */
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('productSearchInput');

        if (searchInput) {
            searchInput.addEventListener('input', filterProductsTable);
        }
    });
</script>

@endsection