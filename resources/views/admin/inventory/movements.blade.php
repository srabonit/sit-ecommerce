@extends('layouts.admin')

@section('title', 'Inventory Movements & Stock Audit Ledger - ' . config('app.name', 'Shop Now BD'))

@php
    $siteName = config('app.name', 'Shop Now BD');

    $resolveThumb = function($path, $fallback = 'https://placehold.co/50x50?text=Item') {
        if (empty($path)) return $fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) return show_image($path, $fallback);
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset(ltrim(str_replace(['public_html/', 'public/'], '', $path), '/'));
    };

    $valuation = $valuation ?? ['total_units' => 0, 'total_valuation' => 0];
@endphp

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #inventoryMovementsRoot .btn,
    #inventoryMovementsRoot .btn-sm,
    #inventoryMovementsRoot button,
    #inventoryMovementsRoot .form-control,
    #inventoryMovementsRoot .form-select,
    #inventoryMovementsRoot .badge,
    #inventoryMovementsRoot .alert,
    #inventoryMovementsRoot .input-group-text,
    #inventoryMovementsRoot .card,
    #inventoryMovementsRoot .metric-soft-card,
    #inventoryMovementsRoot .filter-toolbar-card,
    #inventoryMovementsRoot .audit-widget-card,
    #inventoryMovementsRoot .metric-icon-square,
    #inventoryMovementsRoot .page-link,
    #inventoryMovementsRoot .page-item span,
    #inventoryMovementsRoot .proof-media-surface,
    #inventoryMovementsRoot .proof-thumb-box,
    #inventoryMovementsRoot .collapse-toggle-btn,
    .movement-modal-scope .modal-content,
    .movement-modal-scope .btn,
    .movement-modal-scope .form-control,
    .movement-modal-scope .form-select {
        border-radius: 5px !important;
    }

    #inventoryMovementsRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #inventoryMovementsRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Safe Monospace & Bengali Digit Typography */
    .bn-digit,
    .stat-amount,
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

    /* Enterprise Metric Surface Cards - Zero Overflow Architecture */
    .metric-soft-card {
        padding: 12px 14px;
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        position: relative;
        overflow: hidden;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .metric-soft-card:hover {
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
    }

    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-color: #bbf7d0; }
    .metric-card-red    { background: linear-gradient(180deg, #ffffff 0%, #fef2f2 100%); border-color: #fecaca; }
    .metric-card-blue   { background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%); border-color: #bae6fd; }
    .metric-card-purple { background: linear-gradient(180deg, #ffffff 0%, #faf5ff 100%); border-color: #e9d5ff; }

    .audit-widget-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }
    .filter-toolbar-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }
    .section-header-wrap {
        padding: 10px 14px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
    }

    /* Desktop View Optimization: Balanced 100% Fit Screen with Zero Horizontal Scroll */
    .custom-movements-table {
        width: 100%;
        margin-bottom: 0;
    }
    .custom-movements-table th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 6px;
        white-space: nowrap;
    }
    .custom-movements-table td {
        padding: 8px 6px;
        vertical-align: middle;
        font-size: 11px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .custom-movements-table tbody tr:hover {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .custom-movements-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .custom-movements-table th,
        .custom-movements-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 11 Balanced Proportional Columns (Zero Header & Zero Data Clipping) */
        .col-m-id     { width: 4% !important; }
        .col-m-date   { width: 9% !important; }
        .col-m-prod   { width: 22% !important; }
        .col-m-sku    { width: 8% !important; }
        .col-m-type   { width: 9% !important; }
        .col-m-change { width: 5% !important; }
        .col-m-flow   { width: 7% !important; }
        .col-m-cost   { width: 7% !important; }
        .col-m-imp    { width: 8% !important; }
        .col-m-ref    { width: 11% !important; }
        .col-m-user   { width: 10% !important; }
    }

    .movement-badge {
        font-size: 9px;
        font-weight: 700;
        padding: 2px 6px;
        letter-spacing: 0.2px;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        border-radius: 5px !important;
    }

    /* Enhanced Global Media Chooser Component */
    .proof-media-surface {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        padding: 10px;
        transition: all 0.2s ease;
    }
    .proof-media-surface:hover {
        border-color: #2563eb;
        background: #f1f5f9;
    }
    .proof-empty-icon-box {
        width: 34px;
        height: 34px;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }
    .proof-thumb-box {
        width: 24px;
        height: 24px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        position: relative;
    }
    .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .proof-thumb-hover-overlay {
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
    .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    /* Form Controls */
    #inventoryMovementsRoot .form-label,
    .movement-modal-scope .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #inventoryMovementsRoot .form-control,
    #inventoryMovementsRoot .form-select,
    .movement-modal-scope .form-control,
    .movement-modal-scope .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #inventoryMovementsRoot .form-control:focus,
    #inventoryMovementsRoot .form-select:focus,
    .movement-modal-scope .form-control:focus,
    .movement-modal-scope .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Bulletproof Responsive Pagination */
    #invPaginationLinks {
        display: flex;
        align-items: center;
    }
    #invPaginationLinks nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }
    #invPaginationLinks nav > div.d-sm-none,
    #invPaginationLinks nav > .d-sm-none {
        display: none !important;
    }
    #invPaginationLinks nav > div.d-none,
    #invPaginationLinks nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    #invPaginationLinks nav p,
    #invPaginationLinks nav .small.text-muted,
    #invPaginationLinks nav div:has(> p),
    #invPaginationLinks nav > div.d-none > div:first-child,
    #invPaginationLinks nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }
    #invPaginationLinks .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 3px !important;
        padding: 0 !important;
        list-style: none !important;
    }
    #invPaginationLinks .page-item {
        display: inline-flex !important;
    }
    #invPaginationLinks .page-item .page-link,
    #invPaginationLinks .page-item span {
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
    #invPaginationLinks .page-item .page-link:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }
    #invPaginationLinks .page-item.active .page-link,
    #invPaginationLinks .page-item.active span {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25) !important;
    }
    #invPaginationLinks .page-item.disabled .page-link,
    #invPaginationLinks .page-item.disabled span {
        opacity: 0.45 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }
    #invPaginationLinks nav svg {
        width: 10px !important;
        height: 10px !important;
    }

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .movement-header h5 {
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
        .metric-soft-card {
            padding: 10px !important;
            min-height: 86px !important;
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
        .custom-movements-table {
            min-width: 860px !important;
            table-layout: auto !important;
        }
        #invPaginationLinks {
            width: 100% !important;
            justify-content: center !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            padding: 4px 0 !important;
        }
        #invPaginationLinks nav {
            justify-content: center !important;
            width: 100% !important;
        }
        #invPaginationLinks .pagination {
            flex-wrap: nowrap !important;
            gap: 3px !important;
        }
        #invPaginationLinks .page-item .page-link,
        #invPaginationLinks .page-item span {
            min-width: 24px !important;
            height: 24px !important;
            font-size: 10px !important;
            padding: 0 5px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="inventoryMovementsRoot">
    <!-- AJAX Loading Spinner Overlay -->
    <div id="invLoadingCover" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none justify-content-center align-items-center" style="z-index: 50; border-radius: 5px;">
        <div class="spinner-border text-primary" style="width: 2.2rem; height: 2.2rem;" role="status"></div>
    </div>

    <!-- 1. Header & Top Action Buttons (Original Clean Light Theme) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 movement-header">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-boxes-packing text-primary"></i>
                <span>Inventory Movements &amp; Stock Audit</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Track real-time stock deductions, restocks, cancellations, returns, and manual adjustments.
            </small>
        </div>
        <div class="d-flex gap-2 top-action-btns">
            <button type="button" class="btn btn-sm btn-primary shadow-xs fw-bold btn-compact" data-bs-toggle="modal" data-bs-target="#stockAdjustmentModal">
                <i class="fas fa-sliders me-1"></i>
                <span>Manual Adjustment</span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-success shadow-xs bg-white fw-bold btn-compact" onclick="exportMovementsCsv()">
                <i class="fas fa-file-excel text-success me-1"></i>
                <span>Export (CSV)</span>
            </button>
        </div>
    </div>

    <!-- 2. KPI Summary Cards (Collapsible Section) -->
    <div class="audit-widget-card overflow-hidden mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-chart-pie text-primary me-1"></i> Movement Audits &amp; Valuation Summary
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseMovementKpis" aria-expanded="true" aria-controls="collapseMovementKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseMovementKpis">
            <div class="p-3">
                <div class="row g-2">
                    <!-- Stock Additions -->
                    <div class="col-6 col-xl-3">
                        <div class="metric-soft-card metric-card-green">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Stock In / Additions</span>
                                <div class="metric-icon-square bg-success text-white shadow-xs">
                                    <i class="fas fa-arrow-down-long"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h5 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num" id="val-inv-additions">+{{ number_format($totalAdditions) }}</h5>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Restock, returns &amp; audits</small>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Deductions -->
                    <div class="col-6 col-xl-3">
                        <div class="metric-soft-card metric-card-red">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Stock Out / Deductions</span>
                                <div class="metric-icon-square bg-danger text-white shadow-xs">
                                    <i class="fas fa-arrow-up-long"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h5 class="fw-bold my-0 text-danger bn-digit font-monospace stat-val-num" id="val-inv-deductions">-{{ number_format($totalDeductions) }}</h5>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Sales, POS &amp; damages</small>
                            </div>
                        </div>
                    </div>

                    <!-- Net Stock Balance Change -->
                    <div class="col-6 col-xl-3">
                        <div class="metric-soft-card metric-card-blue">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Net Movement</span>
                                <div class="metric-icon-square bg-primary text-white shadow-xs">
                                    <i class="fas fa-scale-balanced"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h5 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num" id="val-inv-net">{{ ($netChange >= 0 ? '+' : '') . number_format($netChange) }}</h5>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Net stock variation</small>
                            </div>
                        </div>
                    </div>

                    <!-- Total Current Inventory Valuation -->
                    <div class="col-6 col-xl-3">
                        <div class="metric-soft-card metric-card-purple">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Inventory Valuation</span>
                                <div class="metric-icon-square text-white shadow-xs" style="background-color: #7c3aed;">
                                    <i class="fas fa-vault"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h5 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num" id="val-inv-valuation">৳ {{ number_format($valuation['total_valuation'], 2) }}</h5>
                                <small class="text-muted bn-digit font-monospace d-block text-truncate stat-sub-text">Units: {{ number_format($valuation['total_units']) }} in stock</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Filter Toolbar (Collapsible) -->
    <div class="filter-toolbar-card overflow-hidden">
        <div class="section-header-wrap d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-filter text-primary me-1"></i> Filter Audit Trails
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseFilterToolbar" aria-expanded="true" aria-controls="collapseFilterToolbar" title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseFilterToolbar">
            <div class="p-3">
                <form id="movement-filter-form" onsubmit="return false;">
                    <div class="row g-2 align-items-end">
                        <!-- Search Input -->
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="inv-search">
                                <i class="fas fa-search me-1 text-primary"></i> Search Audit Records
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                                <input type="text" name="search" id="inv-search" class="form-control border-start-0 ps-0 shadow-none" placeholder="Invoice #, SKU, Product, Notes..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <!-- Movement Type Selector -->
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="inv-movement-type">
                                <i class="fas fa-tag me-1 text-success"></i> Movement Type
                            </label>
                            <select name="movement_type" id="inv-movement-type" class="form-select shadow-none font-monospace" onchange="applyMovementFilter()">
                                <option value="all" selected>All Movements</option>
                                <option value="checkout_sale">Online Checkout Sale</option>
                                <option value="pos_sale">POS Counter Sale</option>
                                <option value="order_cancel">Order Cancel (Restock)</option>
                                <option value="order_return">Order Return (Restock)</option>
                                <option value="adjustment">Manual Adjustment</option>
                                <option value="purchase_receive">Purchase Receive / Inbound</option>
                                <option value="damage_loss">Damage / Scrap / Loss</option>
                            </select>
                        </div>

                        <!-- Date Filter Selector -->
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="inv-date-filter">
                                <i class="fas fa-calendar-alt me-1 text-info"></i> Date Range
                            </label>
                            <select name="date_filter" id="inv-date-filter" class="form-select shadow-none font-monospace" onchange="handleDateFilterChange(this.value)">
                                <option value="all" selected>All Time</option>
                                <option value="today">Today</option>
                                <option value="yesterday">Yesterday</option>
                                <option value="7_days">Last 7 Days</option>
                                <option value="30_days">Last 30 Days</option>
                                <option value="this_month">This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="custom">Custom Range...</option>
                            </select>
                        </div>

                        <!-- Action Button -->
                        <div class="col-12 col-md-2">
                            <button type="button" class="btn btn-dark btn-compact w-100 fw-bold shadow-xs" onclick="applyMovementFilter()">
                                <i class="fas fa-sync-alt me-1" style="font-size: 10px;"></i>
                                <span>Filter</span>
                            </button>
                        </div>
                    </div>

                    <!-- Custom Date Range Panel (Hidden by default) -->
                    <div id="invCustomDatePanel" class="row g-2 align-items-center mt-2 pt-2 border-top d-none">
                        <div class="col-12 col-sm-auto small fw-bold text-muted font-monospace" style="font-size: 11px;">Custom Dates:</div>
                        <div class="col-6 col-sm-auto">
                            <input type="date" name="start_date" id="inv-start-date" class="form-control shadow-none font-monospace bn-digit">
                        </div>
                        <div class="col-6 col-sm-auto">
                            <input type="date" name="end_date" id="inv-end-date" class="form-control shadow-none font-monospace bn-digit">
                        </div>
                        <div class="col-12 col-sm-auto">
                            <button type="button" class="btn btn-primary btn-compact shadow-xs" onclick="applyMovementFilter()">Apply</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 4. Movements Ledger Table (Desktop 100% Fit Screen with Zero Horizontal Scroll) -->
    <div class="audit-widget-card p-0 overflow-hidden shadow-xs">
        <div class="p-2 px-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 bg-light bg-opacity-25">
            <div>
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13px;">
                    <i class="fas fa-list-check text-primary"></i>
                    <span>Real-Time Stock Movement Audit Trail</span>
                </h6>
                <small class="text-muted font-monospace bn-digit" style="font-size: 10px;" id="inv-entries-text">
                    Showing {{ $movements->firstItem() ?? 0 }} to {{ $movements->lastItem() ?? 0 }} of {{ $movements->total() }} movements
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0 font-monospace" style="font-size: 11px;">Show:</label>
                <select name="per_page" id="inv-per-page" class="form-select shadow-none bn-digit font-monospace" style="width: 75px; font-size: 11px;" onchange="applyMovementFilter()">
                    <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ $perPage == 100000 ? 'selected' : '' }}>All</option>
                </select>
            </div>
        </div>

        <div class="table-responsive pc-fit-container">
            <table class="table align-middle mb-0 custom-movements-table" id="movements-table">
                <thead>
                    <tr>
                        <th class="ps-2 col-m-id text-center">#ID</th>
                        <th class="col-m-date">Date &amp; Time</th>
                        <th class="col-m-prod">Product &amp; Attributes</th>
                        <th class="col-m-sku">SKU / Code</th>
                        <th class="col-m-type">Movement</th>
                        <th class="text-center col-m-change">Qty</th>
                        <th class="text-center col-m-flow">Stock Flow</th>
                        <th class="text-end col-m-cost">Unit Cost</th>
                        <th class="text-end col-m-imp">Impact</th>
                        <th class="col-m-ref">Ref &amp; Proof</th>
                        <th class="pe-2 col-m-user">Initiated By</th>
                    </tr>
                </thead>
                <tbody id="movements-tbody">
                    @include('admin.inventory.partials.movement_rows', ['movements' => $movements])
                </tbody>
            </table>
        </div>

        <!-- Pagination & Footer (Exact Responsive Standard) -->
        <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50" id="inv-pagination-container">
            <div class="small text-muted font-monospace bn-digit" style="font-size: 11px;">
                Total <strong>{{ $movements->total() }}</strong> records logged
            </div>
            <div id="invPaginationLinks">
                {{ $movements->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- 5. Manual Stock Adjustment Modal with Global Media Chooser Integration -->
<div class="modal fade movement-modal-scope" id="stockAdjustmentModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom py-2 px-3 bg-light d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-1 mb-0" style="font-size: 13.5px;">
                    <i class="fas fa-sliders text-primary"></i> <span>Manual Stock Adjustment &amp; Voucher Proof</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="stockAdjustmentForm" action="{{ route('admin.inventory.adjust') }}" method="POST">
                @csrf
                <!-- Hidden Input for Proof Attachment synced with Global Media Chooser -->
                <input type="hidden" name="proof_attachment" id="adj_proof_attachment" value="">

                <div class="modal-body p-3">
                    <div class="mb-2">
                        <label class="form-label" for="adj_product_id">
                            Select Product <span class="text-danger">*</span>
                        </label>
                        <select name="product_id" id="adj_product_id" class="form-select shadow-none" required onchange="loadProductVariantsForAdjustment(this.value)">
                            <option value="">— Select Product —</option>
                            @php
                                $allProducts = DB::table('products')->orderBy('title', 'asc')->get(['id', 'title', 'product_code', 'stock', 'product_struct']);
                            @endphp
                            @foreach($allProducts as $p)
                                <option value="{{ $p->id }}" data-struct="{{ $p->product_struct }}" data-stock="{{ $p->stock }}">
                                    #{{ $p->id }} — {{ Str::limit($p->title, 42) }} ({{ $p->product_code ?? 'No SKU' }}) [Current: {{ $p->stock }}]
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Dynamic Variant Selector (Visible if product is variable) -->
                    <div class="mb-2 d-none" id="adjVariantBox">
                        <label class="form-label text-primary" for="adj_variant_id">
                            <i class="fas fa-layer-group me-1"></i> Select Variant <span class="text-danger">*</span>
                        </label>
                        <select name="variant_id" id="adj_variant_id" class="form-select shadow-none">
                            <option value="">— Choose Variant —</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label" for="adj_movement_type">
                                Movement Reason <span class="text-danger">*</span>
                            </label>
                            <select name="movement_type" id="adj_movement_type" class="form-select shadow-none" required>
                                <option value="adjustment" selected>Manual Adjustment</option>
                                <option value="purchase_receive">Purchase Inbound / Restock</option>
                                <option value="damage_loss">Damage / Scrap / Loss</option>
                                <option value="inventory_audit">Periodic Audit Reconciliation</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="adj_new_stock">
                                New Total Stock Count <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="new_stock" id="adj_new_stock" class="form-control bn-digit font-monospace shadow-none" min="0" placeholder="50" required>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" for="adj_purchase_cost">
                            Unit Purchase Cost (৳) <span class="text-muted fw-normal font-monospace">(Optional)</span>
                        </label>
                        <input type="number" step="0.01" name="purchase_cost" id="adj_purchase_cost" class="form-control bn-digit font-monospace shadow-none" placeholder="350.00">
                    </div>

                    <!-- Modern Global Media Chooser Component for Stock Adjustment Proof -->
                    <div class="mb-2">
                        <label class="form-label d-flex align-items-center justify-content-between mb-1">
                            <span>
                                <i class="fas fa-paperclip text-primary me-1"></i> Physical Count / Audit Slip Voucher:
                            </span>
                            <span class="badge bg-light text-muted border font-monospace" style="font-size: 8.5px;">GLOBAL MEDIA</span>
                        </label>
                        <div class="proof-media-surface" style="border-radius: 5px;">
                            <!-- Empty State -->
                            <div id="noAdjProofNotice" class="text-center py-2">
                                <div class="proof-empty-icon-box mx-auto mb-2" style="border-radius: 5px;">
                                    <i class="fas fa-cloud-arrow-up text-muted"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1" style="font-size: 11px;">No Audit Proof Attached</h6>
                                <p class="text-muted small mb-2" style="font-size: 10px;">Attach physical stock sheet, damaged items photo, or voucher copy.</p>
                                <button type="button" class="btn btn-primary btn-compact shadow-xs" onclick="chooseAdjustmentMedia()">
                                    <i class="fas fa-photo-film me-1"></i>
                                    <span>Choose from Media Library</span>
                                </button>
                            </div>

                            <!-- Attached Preview Card (100% Mobile & PC Responsive) -->
                            <div id="adjProofPreviewContainer" class="d-none bg-white border p-1" style="border-radius: 5px;">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <div class="proof-thumb-box" onclick="zoomAdjustmentMedia()" title="Click to Zoom Image">
                                        <img id="adjProofPreviewImg" src="https://placehold.co/120x120?text=Audit+Proof" alt="Proof" onerror="this.src='https://placehold.co/120x120?text=Audit+Proof';">
                                        <div class="proof-thumb-hover-overlay">
                                            <i class="fas fa-search-plus text-white" style="font-size: 9px;"></i>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-grow-1">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 8.5px;">
                                            <i class="fas fa-check-circle me-1"></i>Proof Attached
                                        </span>
                                        <strong class="d-block text-dark text-truncate font-monospace" id="adjProofPathText" style="font-size: 10px;">audit_voucher.jpg</strong>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1 border-top pt-1">
                                    <button type="button" class="btn btn-outline-secondary btn-compact flex-grow-1 py-0 px-1" style="height: 22px; font-size: 9px !important;" onclick="zoomAdjustmentMedia()">
                                        <i class="fas fa-expand me-1"></i><span>Zoom</span>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-compact flex-grow-1 py-0 px-1" style="height: 22px; font-size: 9px !important;" onclick="chooseAdjustmentMedia()">
                                        <i class="fas fa-arrows-rotate me-1"></i><span>Change</span>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-compact flex-grow-1 py-0 px-1" style="height: 22px; font-size: 9px !important;" onclick="clearAdjustmentMedia()">
                                        <i class="fas fa-trash-alt me-1"></i><span>Remove</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes / Audit Memo -->
                    <div class="mb-2">
                        <label class="form-label" for="adj_notes">Adjustment Notes / Audit Memo</label>
                        <textarea name="notes" id="adj_notes" class="form-control shadow-none" rows="2" style="font-size: 11px;" placeholder="Received new stock consignment or periodic physical count adjustment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold" id="btn-submit-adj">Apply Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 6: Proof Lightbox Zoom Modal -->
<div class="modal fade movement-modal-scope" id="proofZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12px;">
                    <i class="fas fa-image text-primary me-1"></i> Audit Slip / Attachment Preview
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="modalZoomProofImgTag" src="" alt="Proof Preview" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<!-- Modal 7: Bootstrap Notice Dialog Modal -->
<div class="modal fade movement-modal-scope" id="movementNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-2" id="movementNoticeIconWrap" style="width: 44px; height: 44px; border-radius: 50% !important;">
                    <i class="fas fa-circle-info fs-4" id="movementNoticeIcon"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="movementNoticeTitle" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="movementNoticeMsg" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showMovementNotice(message, title = 'Notification', type = 'info') {
        const titleEl  = document.getElementById('movementNoticeTitle');
        const msgEl    = document.getElementById('movementNoticeMsg');
        const iconWrap = document.getElementById('movementNoticeIconWrap');
        const iconEl   = document.getElementById('movementNoticeIcon');

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

        const modalEl = document.getElementById('movementNoticeModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    // Global Media Chooser Integration for Adjustment Proof
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

    function chooseAdjustmentMedia() {
        if (typeof window.openMediaPicker === 'function') {
            window.openMediaPicker({
                multiple: false,
                onSelect: function(media) {
                    const picked = extractMediaItem(media);
                    if (picked && picked.path) {
                        const inputHidden = document.getElementById('adj_proof_attachment');
                        const previewImg  = document.getElementById('adjProofPreviewImg');
                        const pathText    = document.getElementById('adjProofPathText');
                        const container   = document.getElementById('adjProofPreviewContainer');
                        const emptyNotice = document.getElementById('noAdjProofNotice');

                        if (inputHidden) inputHidden.value = picked.path;
                        if (previewImg) previewImg.src = picked.url;
                        if (pathText) {
                            pathText.innerText = picked.path.split('/').pop() || picked.path;
                            pathText.title = picked.path;
                        }

                        if (container) {
                            container.classList.remove('d-none');
                            container.classList.add('d-block');
                        }
                        if (emptyNotice) {
                            emptyNotice.classList.add('d-none');
                            emptyNotice.classList.remove('d-block');
                        }

                        showMovementNotice('Audit evidence voucher attached from Global Media Library.', 'Attached', 'success');
                    }
                }
            });
        } else {
            showMovementNotice('Global Media Library is loading or not available on this layout.', 'Notice');
        }
    }

    function clearAdjustmentMedia() {
        const inputHidden = document.getElementById('adj_proof_attachment');
        const container   = document.getElementById('adjProofPreviewContainer');
        const emptyNotice = document.getElementById('noAdjProofNotice');

        if (inputHidden) inputHidden.value = '';
        if (container) {
            container.classList.add('d-none');
            container.classList.remove('d-block');
        }
        if (emptyNotice) {
            emptyNotice.classList.remove('d-none');
            emptyNotice.classList.add('d-block');
        }

        showMovementNotice('Audit evidence voucher cleared.', 'Cleared', 'info');
    }

    function zoomAdjustmentMedia() {
        const previewImg = document.getElementById('adjProofPreviewImg');
        const zoomImgTag = document.getElementById('modalZoomProofImgTag');
        if (previewImg && zoomImgTag && previewImg.src) {
            zoomImgTag.src = previewImg.src;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('proofZoomModal')).show();
        }
    }

    function zoomProofAttachment(url, title = 'Audit Voucher') {
        const zoomImgTag = document.getElementById('modalZoomProofImgTag');
        if (url && zoomImgTag) {
            zoomImgTag.src = url;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('proofZoomModal')).show();
        }
    }

    function handleDateFilterChange(val) {
        const customPanel = document.getElementById('invCustomDatePanel');
        if (val === 'custom') {
            customPanel.classList.remove('d-none');
        } else {
            customPanel.classList.add('d-none');
            applyMovementFilter();
        }
    }

    function applyMovementFilter(targetUrl = null) {
        const form = document.getElementById('movement-filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.append('per_page', document.getElementById('inv-per-page').value);

        const cover = document.getElementById('invLoadingCover');
        if (cover) {
            cover.classList.remove('d-none');
            cover.classList.add('d-flex');
        }

        const url = targetUrl || ("{{ route('admin.inventory.movements') }}?" + params.toString());

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('movements-tbody').innerHTML = data.table_html;
                document.getElementById('inv-pagination-container').innerHTML = `
                    <div class="small text-muted font-monospace bn-digit" style="font-size: 11px;">
                        Total <strong>${data.total_count}</strong> records logged
                    </div>
                    <div id="invPaginationLinks">${data.pagination_html}</div>
                `;

                if (data.summary) {
                    document.getElementById('val-inv-additions').innerText = '+' + data.summary.additions;
                    document.getElementById('val-inv-deductions').innerText = '-' + data.summary.deductions;
                    document.getElementById('val-inv-net').innerText = data.summary.net_change;
                }
            }
        })
        .catch(err => console.error('Inventory movement filter error:', err))
        .finally(() => {
            if (cover) {
                cover.classList.add('d-none');
                cover.classList.remove('d-flex');
            }
        });
    }

    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#invPaginationLinks a');
        if (pageLink) {
            e.preventDefault();
            const href = pageLink.getAttribute('href');
            if (href && href !== '#' && !href.startsWith('javascript:')) {
                applyMovementFilter(href);
            }
        }
    });

    function exportMovementsCsv() {
        const form = document.getElementById('movement-filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.append('export', 'csv');
        window.open("{{ route('admin.inventory.movements') }}?" + params.toString(), '_blank');
    }

    function loadProductVariantsForAdjustment(productId) {
        const variantBox = document.getElementById('adjVariantBox');
        const variantSelect = document.getElementById('adj_variant_id');
        variantSelect.innerHTML = '<option value="">— Choose Variant —</option>';

        if (!productId) {
            variantBox.classList.add('d-none');
            return;
        }

        fetch(`/admin/api/products/${productId}/variants`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(variants => {
            if (Array.isArray(variants) && variants.length > 0) {
                variants.forEach(v => {
                    const label = (v.color ? v.color + ' ' : '') + (v.size ? '/ ' + v.size + ' ' : '') + `[Stock: ${v.stock}] (SKU: ${v.sku || v.product_code || 'N/A'})`;
                    variantSelect.innerHTML += `<option value="${v.id}" data-stock="${v.stock}">${label}</option>`;
                });
                variantBox.classList.remove('d-none');
                variantSelect.setAttribute('required', 'required');
            } else {
                variantBox.classList.add('d-none');
                variantSelect.removeAttribute('required');
            }
        })
        .catch(() => {
            variantBox.classList.add('d-none');
            variantSelect.removeAttribute('required');
        });
    }

    document.getElementById('stockAdjustmentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const btn = document.getElementById('btn-submit-adj');
        btn.disabled = true;
        btn.innerText = 'Applying...';

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('stockAdjustmentModal')).hide();
                form.reset();
                clearAdjustmentMedia();
                applyMovementFilter();
                showMovementNotice('Stock adjustment applied successfully.', 'Adjustment Confirmed', 'success');
            } else {
                showMovementNotice(data.message || 'Failed to adjust stock.', 'Adjustment Error', 'error');
            }
        })
        .catch(err => {
            console.error('Adjustment error:', err);
            showMovementNotice('Server connection error while applying adjustment.', 'Server Error', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = 'Apply Adjustment';
        });
    });
</script>
@endsection