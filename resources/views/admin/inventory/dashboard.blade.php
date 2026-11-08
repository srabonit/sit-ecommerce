
@extends('layouts.admin')

@section('title', 'Inventory Dashboard & Stock Health - ' . config('app.name', 'Shop Now BD'))

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

    $todayInUnits   = (int) ($todayMovements->stock_in_units ?? 0);
    $todayInVal     = (float) ($todayMovements->stock_in_val ?? 0);
    $todayOutUnits  = (int) ($todayMovements->stock_out_units ?? 0);
    $todayOutVal    = (float) ($todayMovements->stock_out_val ?? 0);
    $todaySalesRev  = (float) ($todayMovements->sales_rev ?? 0);
    $todaySalesCogs = (float) ($todayMovements->sales_cogs ?? 0);
    $todayProfit    = max(0.00, $todaySalesRev - $todaySalesCogs);
@endphp

@section('admin_content')

<style>
    /* =========================================================
       INVENTORY DASHBOARD
       Kaiadmin Lite - Classic / White Classic
    ========================================================= */

    #invDashRoot {
        --inv-primary: var(--bs-primary, #1572e8);
        --inv-secondary: var(--bs-secondary, #6861ce);
        --inv-success: var(--bs-success, #31ce36);
        --inv-danger: var(--bs-danger, #f25961);
        --inv-warning: var(--bs-warning, #ffad46);
        --inv-info: var(--bs-info, #48abf7);

        --inv-body-bg: var(--bs-body-bg, #f8f9fa);
        --inv-card-bg: #ffffff;
        --inv-heading: #1a2035;
        --inv-text: #495057;
        --inv-muted: #8d9498;
        --inv-border: #e6e9ed;
        --inv-soft-bg: #f8f9fa;

        --inv-radius: 5px;
        --inv-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        --inv-shadow-hover: 0 4px 12px rgba(0, 0, 0, .07);

        width: 100%;
        max-width: 100%;
        min-width: 0;
        color: var(--inv-text);
        font-size: 13px;
    }

    #invDashRoot *,
    #invDashRoot *::before,
    #invDashRoot *::after {
        box-sizing: border-box;
    }

    #invDashRoot .btn,
    #invDashRoot .badge,
    #invDashRoot .card,
    #invDashRoot .metric-card,
    #invDashRoot .health-stat-card,
    #invDashRoot .action-quick-btn,
    #invDashRoot .product-thumb-sm,
    #invDashRoot .metric-icon-box,
    #invDashRoot .collapse-toggle-btn,
    .inv-modal-scope .modal-content,
    .inv-modal-scope .btn {
        border-radius: var(--inv-radius) !important;
    }

    #invDashRoot .font-monospace {
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }

    #invDashRoot .min-w-0 {
        min-width: 0;
    }

    /* Page Header */

    #invDashRoot .inv-page-header {
        margin-bottom: 18px;
    }

    #invDashRoot .inv-page-title {
        color: var(--inv-heading);
        font-size: clamp(17px, 1.5vw, 21px);
        font-weight: 700;
        line-height: 1.35;
        letter-spacing: -.3px;
        margin: 0 0 4px;
    }

    #invDashRoot .inv-page-subtitle {
        color: var(--inv-muted);
        font-size: 12px;
        line-height: 1.6;
        margin: 0;
    }

    #invDashRoot .header-actions-wrap {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 7px;
    }

    #invDashRoot .btn-compact {
        min-height: 34px;
        padding: 7px 12px !important;
        font-size: 11px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        white-space: nowrap;
        transition: all .2s ease;
    }

    #invDashRoot .btn-compact:hover {
        transform: translateY(-1px);
        box-shadow: var(--inv-shadow-hover);
    }

    /* Common Cards */

    #invDashRoot .inv-panel,
    #invDashRoot .inv-section-card {
        background: var(--inv-card-bg);
        border: 1px solid var(--inv-border) !important;
        border-radius: var(--inv-radius) !important;
        box-shadow: var(--inv-shadow);
    }

    #invDashRoot .inv-panel-header {
        min-height: 43px;
        padding: 10px 15px;
        background: #fff;
        border-bottom: 1px solid var(--inv-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    #invDashRoot .inv-section-title {
        color: var(--inv-heading);
        font-size: 13px;
        font-weight: 700;
        line-height: 1.4;
        margin: 0;
    }

    #invDashRoot .inv-section-title i {
        margin-right: 5px;
    }

    #invDashRoot .collapse-toggle-btn {
        width: 28px;
        height: 28px;
        padding: 0;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid var(--inv-border);
        color: var(--inv-muted);
        transition: all .2s ease;
    }

    #invDashRoot .collapse-toggle-btn:hover {
        background: var(--inv-primary);
        border-color: var(--inv-primary);
        color: #fff;
    }

    #invDashRoot .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    #invDashRoot .collapse-icon {
        font-size: 10px;
        transition: transform .2s ease;
    }

    /* KPI Cards */

    #invDashRoot .metric-card {
        min-width: 0;
        min-height: 126px;
        padding: 13px 14px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background: #fff;
        border: 1px solid var(--inv-border) !important;
        box-shadow: var(--inv-shadow);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    #invDashRoot .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--inv-shadow-hover);
    }

    #invDashRoot .metric-card-purple {
        background: #fff;
        border-top: 3px solid #6861ce !important;
    }

    #invDashRoot .metric-card-green {
        background: #fff;
        border-top: 3px solid #31ce36 !important;
    }

    #invDashRoot .metric-card-red {
        background: #fff;
        border-top: 3px solid #f25961 !important;
    }

    #invDashRoot .metric-card-amber {
        background: #fff;
        border-top: 3px solid #ffad46 !important;
    }

    #invDashRoot .stat-label-title {
        color: var(--inv-muted);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .1px;
        line-height: 1.4;
    }

    #invDashRoot .stat-val-num {
        color: var(--inv-heading);
        font-size: clamp(15px, 1.4vw, 19px);
        line-height: 1.35;
        letter-spacing: -.35px;
        font-weight: 700;
        margin: 0;
        overflow-wrap: anywhere;
    }

    #invDashRoot .stat-sub-text {
        color: var(--inv-muted);
        font-size: 10px;
        line-height: 1.5;
        margin-top: 3px;
    }

    #invDashRoot .metric-icon-box {
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
    }

    #invDashRoot .metric-card-footer {
        margin-top: 10px;
        padding-top: 8px;
        border-top: 1px solid var(--inv-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 6px;
        color: var(--inv-muted);
        font-size: 9px;
    }

    #invDashRoot .metric-card-footer a {
        font-size: 10px;
        font-weight: 600;
        text-decoration: none;
    }

    #invDashRoot .metric-card-footer a:hover {
        text-decoration: underline;
    }

    /* Stock Health Cards */

    #invDashRoot .health-stat-card {
        min-height: 70px;
        height: 100%;
        padding: 12px;
        display: flex;
        align-items: center;
        background: #fff;
        border: 1px solid var(--inv-border) !important;
        text-decoration: none;
        box-shadow: var(--inv-shadow);
        transition: all .2s ease;
    }

    #invDashRoot .health-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--inv-shadow-hover);
    }

    #invDashRoot .health-stat-card .health-label {
        display: block;
        color: var(--inv-text);
        font-size: 11px;
        font-weight: 600;
        line-height: 1.4;
        margin-bottom: 3px;
    }

    #invDashRoot .health-stat-card .health-value {
        font-size: 17px;
        font-weight: 700;
        line-height: 1.3;
        margin: 0;
    }

    /* Quick Operations */

    #invDashRoot .action-quick-btn {
        width: 100%;
        min-height: 62px;
        padding: 10px 12px;
        display: flex;
        align-items: center;
        gap: 11px;
        background: #fff;
        border: 1px solid var(--inv-border);
        color: var(--inv-heading);
        text-decoration: none;
        transition: all .2s ease;
    }

    #invDashRoot .action-quick-btn:hover {
        background: #f8faff;
        border-color: var(--inv-primary);
        box-shadow: var(--inv-shadow);
        color: var(--inv-heading);
        transform: translateX(2px);
    }

    #invDashRoot .action-quick-btn .action-title {
        display: block;
        color: var(--inv-heading);
        font-size: 12px;
        font-weight: 700;
        line-height: 1.4;
    }

    #invDashRoot .action-quick-btn .action-description {
        display: block;
        color: var(--inv-muted);
        font-size: 10px;
        line-height: 1.5;
        margin-top: 2px;
        overflow-wrap: anywhere;
    }

    /* Data Table */

    #invDashRoot .pc-fit-container {
        width: 100%;
        max-width: 100%;
        overflow-x: auto !important;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #cbd2da #f5f6f8;
    }

    #invDashRoot .dashboard-table {
        width: 100%;
        min-width: 760px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #invDashRoot .dashboard-table thead th {
        padding: 11px 10px;
        color: #6861ce;
        background: #f8f9fa;
        border-bottom: 1px solid var(--inv-border);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .35px;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }

    #invDashRoot .dashboard-table tbody td {
        padding: 10px;
        color: var(--inv-text);
        background: #fff;
        border-bottom: 1px solid #f0f1f3;
        font-size: 11px;
        line-height: 1.45;
        vertical-align: middle;
    }

    #invDashRoot .dashboard-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #invDashRoot .dashboard-table tbody tr:hover td {
        background: #fafbfc;
    }

    #invDashRoot .dashboard-table .col-dt-item {
        width: 30%;
    }

    #invDashRoot .dashboard-table .col-dt-type {
        width: 18%;
    }

    #invDashRoot .dashboard-table .col-dt-change {
        width: 9%;
    }

    #invDashRoot .dashboard-table .col-dt-flow {
        width: 15%;
    }

    #invDashRoot .dashboard-table .col-dt-cost {
        width: 15%;
    }

    #invDashRoot .dashboard-table .col-dt-time {
        width: 13%;
    }

    #invDashRoot .movement-badge-sm {
        display: inline-block;
        max-width: 100%;
        padding: 4px 7px;
        border-radius: 4px !important;
        font-size: 9px;
        font-weight: 700;
        line-height: 1.3;
        letter-spacing: .1px;
        text-transform: uppercase;
        white-space: normal;
    }

    #invDashRoot .product-thumb-sm {
        width: 32px;
        height: 32px;
        object-fit: cover;
        background: #fff;
        border: 1px solid var(--inv-border);
        flex-shrink: 0;
        cursor: pointer;
    }

    #invDashRoot .proof-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        background: rgba(26, 32, 53, .5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity .2s ease;
        border-radius: 5px !important;
        cursor: pointer;
    }

    #invDashRoot .position-relative:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    #invDashRoot .inv-product-title {
        display: block;
        color: var(--inv-heading);
        font-size: 11px;
        font-weight: 600;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    #invDashRoot .inv-variant-badge {
        display: inline-block;
        max-width: 140px;
        padding: 2px 5px;
        margin-top: 3px;
        font-size: 9px;
        overflow-wrap: anywhere;
    }

    /* Responsive Layout */

    @media (max-width: 1199.98px) {
        #invDashRoot .metric-card {
            min-height: 122px;
        }
    }

    @media (max-width: 991.98px) {
        #invDashRoot .inv-page-header {
            align-items: flex-start !important;
        }

        #invDashRoot .header-actions-wrap {
            justify-content: flex-start;
        }

        #invDashRoot .metric-card {
            min-height: 120px;
        }

        #invDashRoot .inv-section-card {
            height: auto !important;
        }
    }

    @media (max-width: 767.98px) {
        #invDashRoot {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #invDashRoot .inv-page-header {
            gap: 12px !important;
            margin-bottom: 15px;
        }

        #invDashRoot .inv-page-title {
            font-size: 17px;
        }

        #invDashRoot .inv-page-subtitle {
            font-size: 11px;
        }

        #invDashRoot .header-actions-wrap {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 7px;
        }

        #invDashRoot .header-actions-wrap .btn {
            width: 100%;
            min-height: 38px;
            padding: 7px 5px !important;
            font-size: 10px !important;
        }

        #invDashRoot .metric-card {
            min-height: 116px;
            padding: 10px !important;
        }

        #invDashRoot .stat-label-title {
            font-size: 10px;
        }

        #invDashRoot .stat-val-num {
            font-size: 15px;
        }

        #invDashRoot .stat-sub-text {
            font-size: 9px;
        }

        #invDashRoot .metric-icon-box {
            width: 29px;
            height: 29px;
            flex-basis: 29px;
            font-size: 11px;
        }

        #invDashRoot .metric-card-footer {
            font-size: 8px;
        }

        #invDashRoot .metric-card-footer a {
            font-size: 9px;
        }

        #invDashRoot .health-stat-card {
            min-height: 67px;
            padding: 9px;
        }

        #invDashRoot .health-stat-card .health-label {
            font-size: 10px;
        }

        #invDashRoot .health-stat-card .health-value {
            font-size: 15px;
        }

        #invDashRoot .inv-panel-header {
            padding: 10px 12px;
        }

        #invDashRoot .inv-section-title {
            font-size: 12px;
        }

        #invDashRoot .action-quick-btn {
            min-height: 58px;
            padding: 9px;
        }

        #invDashRoot .dashboard-table {
            min-width: 760px;
        }

        #invDashRoot .dashboard-table thead th {
            padding: 9px 8px;
            font-size: 9px;
        }

        #invDashRoot .dashboard-table tbody td {
            padding: 9px 8px;
            font-size: 10px;
        }
    }

    @media (max-width: 399.98px) {
        #invDashRoot .metric-card {
            min-height: 110px;
            padding: 8px !important;
        }

        #invDashRoot .stat-val-num {
            font-size: 13px;
        }

        #invDashRoot .stat-label-title {
            font-size: 9px;
        }

        #invDashRoot .metric-icon-box {
            width: 26px;
            height: 26px;
            flex-basis: 26px;
            font-size: 10px;
        }

        #invDashRoot .metric-card-footer {
            gap: 3px;
            flex-wrap: wrap;
        }

        #invDashRoot .health-stat-card {
            padding: 8px;
        }

        #invDashRoot .health-stat-card .health-label {
            font-size: 9px;
        }
    }

    /* Image Preview Modal */

    .inv-modal-scope .modal-content {
        background: #fff;
        border: 1px solid var(--inv-border);
        box-shadow: 0 10px 35px rgba(0, 0, 0, .15);
        overflow: hidden;
    }

    .inv-modal-scope .modal-header {
        padding: 14px 16px 8px;
    }

    .inv-modal-scope .modal-body {
        padding: 12px;
    }

    .inv-modal-scope .inv-modal-image {
        max-width: 100%;
        max-height: min(65vh, 480px);
        object-fit: contain;
        border-radius: 5px;
        border: 1px solid var(--inv-border);
    }

    @media (max-width: 575.98px) {
        .inv-modal-scope .modal-dialog {
            margin: 12px;
        }

        .inv-modal-scope .modal-header {
            padding: 12px 12px 6px;
        }

        .inv-modal-scope .modal-body {
            padding: 10px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="invDashRoot">

    <!-- 1. Page Header & Quick Actions -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center inv-page-header">
        <div class="min-w-0">
            <h5 class="inv-page-title d-flex align-items-center gap-2">
                <i class="fas fa-boxes-packing text-primary"></i>
                <span>Inventory &amp; Stock Dashboard</span>
            </h5>
            <p class="inv-page-subtitle">
                Monitor stock valuation, inventory movements, write-offs and catalog health.
            </p>
        </div>

        <div class="header-actions-wrap">
            <a href="{{ route('admin.inventory.stock_in') }}" class="btn btn-sm btn-success btn-compact shadow-xs fw-bold">
                <i class="fas fa-arrow-down-long"></i> <span>Stock In</span>
            </a>
            <a href="{{ route('admin.inventory.stock_out') }}" class="btn btn-sm btn-danger btn-compact shadow-xs fw-bold">
                <i class="fas fa-arrow-up-long"></i> <span>Stock Out</span>
            </a>
            <a href="{{ route('admin.inventory.balance') }}" class="btn btn-sm btn-primary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-scale-balanced"></i> <span>Balance Sheet</span>
            </a>
            <a href="{{ route('admin.inventory.reports') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-file-invoice-dollar"></i> <span>Reports</span>
            </a>
        </div>
    </div>

    <!-- 2. Core Inventory Valuation -->
    <div class="card inv-panel mb-3">
        <div class="inv-panel-header">
            <span class="inv-section-title">
                <i class="fas fa-vault text-primary"></i>
                Inventory Valuation &amp; Daily Flow
            </span>

            <button type="button" class="collapse-toggle-btn shadow-xs no-print"
                data-bs-toggle="collapse"
                data-bs-target="#collapsePrimaryKpis"
                aria-expanded="true"
                aria-controls="collapsePrimaryKpis"
                title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapsePrimaryKpis">
            <div class="p-3">
                <div class="row g-2">

                    <!-- Total Inventory Valuation -->
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-purple h-100">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">Inventory Valuation</span>
                                <div class="metric-icon-box text-white shadow-xs" style="background-color: #6861ce;">
                                    <i class="fas fa-vault"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="fw-bold stat-val-num font-monospace">
                                    ৳ {{ number_format($valuation['total_valuation'], 2) }}
                                </h4>
                                <small class="stat-sub-text d-block text-truncate font-monospace">
                                    <strong>{{ number_format($valuation['total_units']) }}</strong> Units in Stock
                                </small>
                            </div>

                            <div class="metric-card-footer font-monospace">
                                <span>Simple: ৳{{ number_format($valuation['simple_valuation'], 0) }}</span>
                                <span>Variant: ৳{{ number_format($valuation['variant_valuation'], 0) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Stock In -->
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">Today's Stock In</span>
                                <div class="metric-icon-box bg-success text-white shadow-xs">
                                    <i class="fas fa-arrow-down-long"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success font-monospace stat-val-num">
                                    +{{ number_format($todayInUnits) }} <span style="font-size: 11px;">Units</span>
                                </h4>
                                <small class="stat-sub-text d-block text-truncate font-monospace">
                                    Cost: ৳ {{ number_format($todayInVal, 2) }}
                                </small>
                            </div>

                            <div class="metric-card-footer">
                                <a href="{{ route('admin.inventory.stock_in') }}" class="text-success">
                                    <i class="fas fa-plus me-1"></i> Receive Stock
                                </a>
                                <span>Today</span>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Stock Out -->
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-red h-100">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">Today's Stock Out</span>
                                <div class="metric-icon-box bg-danger text-white shadow-xs">
                                    <i class="fas fa-arrow-up-long"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-danger font-monospace stat-val-num">
                                    -{{ number_format($todayOutUnits) }} <span style="font-size: 11px;">Units</span>
                                </h4>
                                <small class="stat-sub-text d-block text-truncate font-monospace">
                                    Impact: ৳ {{ number_format($todayOutVal, 2) }}
                                </small>
                            </div>

                            <div class="metric-card-footer">
                                <a href="{{ route('admin.inventory.stock_out') }}" class="text-danger">
                                    <i class="fas fa-minus me-1"></i> Write-Off
                                </a>
                                <span>Sales &amp; Loss</span>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Estimated Gross Profit -->
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber h-100">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">Today's Profit</span>
                                <div class="metric-icon-box text-dark shadow-xs" style="background-color: #ffad46;">
                                    <i class="fas fa-coins"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark font-monospace stat-val-num">
                                    ৳ {{ number_format($todayProfit, 2) }}
                                </h4>
                                <small class="stat-sub-text d-block text-truncate font-monospace">
                                    Rev: ৳{{ number_format($todaySalesRev, 0) }} | COGS: ৳{{ number_format($todaySalesCogs, 0) }}
                                </small>
                            </div>

                            <div class="metric-card-footer">
                                <a href="{{ route('admin.inventory.reports', ['period' => 'today']) }}" class="text-dark">
                                    Daily P&amp;L <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- 3. Stock Health & Catalog Status -->
    <div class="row g-2 mb-3">

        <div class="col-6 col-md-3">
            <a href="{{ route('admin.inventory.balance', ['stock_status' => 'low_stock']) }}"
                class="health-stat-card border-warning border-opacity-50">
                <div class="d-flex align-items-center gap-2 w-100">
                    <div class="metric-icon-box bg-warning bg-opacity-10 text-warning-emphasis">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="health-label">Low Stock Alert</span>
                        <h5 class="health-value text-warning-emphasis font-monospace">
                            {{ number_format($lowStockCount) }}
                        </h5>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="{{ route('admin.inventory.balance', ['stock_status' => 'out_of_stock']) }}"
                class="health-stat-card border-danger border-opacity-50">
                <div class="d-flex align-items-center gap-2 w-100">
                    <div class="metric-icon-box bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-circle-xmark"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="health-label">Out of Stock</span>
                        <h5 class="health-value text-danger font-monospace">
                            {{ number_format($outOfStockCount) }}
                        </h5>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="{{ route('admin.product.index') }}"
                class="health-stat-card border-info border-opacity-50">
                <div class="d-flex align-items-center gap-2 w-100">
                    <div class="metric-icon-box bg-info bg-opacity-10 text-info-emphasis">
                        <i class="fas fa-box-archive"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="health-label">Total Products</span>
                        <h5 class="health-value text-dark font-monospace">
                            {{ number_format($totalProducts) }}
                        </h5>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="{{ route('admin.inventory.balance') }}"
                class="health-stat-card border-primary border-opacity-50">
                <div class="d-flex align-items-center gap-2 w-100">
                    <div class="metric-icon-box bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="health-label">Active Variants</span>
                        <h5 class="health-value text-primary font-monospace">
                            {{ number_format($totalVariants) }}
                        </h5>
                    </div>
                </div>
            </a>
        </div>

    </div>

    <!-- 4. Operations Hub & Recent Movements -->
    <div class="row g-2 g-md-3">

        <!-- Quick Operations Hub -->
        <div class="col-12 col-xl-4">
            <div class="card inv-section-card p-3 h-100">
                <h6 class="inv-section-title border-bottom pb-3 mb-3">
                    <i class="fas fa-bolt text-warning"></i>
                    Inventory Operations
                </h6>

                <div class="d-flex flex-column gap-2">

                    <a href="{{ route('admin.inventory.stock_in') }}" class="action-quick-btn shadow-xs">
                        <div class="metric-icon-box bg-success bg-opacity-10 text-success">
                            <i class="fas fa-truck-ramp-box"></i>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <span class="action-title">Stock In / Purchase</span>
                            <small class="action-description">Receive shipments with WAC costing</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted opacity-50" style="font-size: 10px;"></i>
                    </a>

                    <a href="{{ route('admin.inventory.stock_out') }}" class="action-quick-btn shadow-xs">
                        <div class="metric-icon-box bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-arrow-up-from-bracket"></i>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <span class="action-title">Stock Out / Write-Off</span>
                            <small class="action-description">Manage damage, expiry and returns</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted opacity-50" style="font-size: 10px;"></i>
                    </a>

                    <a href="{{ route('admin.inventory.balance') }}" class="action-quick-btn shadow-xs">
                        <div class="metric-icon-box bg-info bg-opacity-10 text-info-emphasis">
                            <i class="fas fa-scale-balanced"></i>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <span class="action-title">Current Stock Sheet</span>
                            <small class="action-description">View variant-wise stock and valuation</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted opacity-50" style="font-size: 10px;"></i>
                    </a>

                    <a href="{{ route('admin.inventory.reports') }}" class="action-quick-btn shadow-xs">
                        <div class="metric-icon-box bg-secondary bg-opacity-10 text-secondary">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <span class="action-title">Inventory Audit Reports</span>
                            <small class="action-description">Opening, closing, COGS and profit</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted opacity-50" style="font-size: 10px;"></i>
                    </a>

                </div>
            </div>
        </div>

        <!-- Recent Stock Movements -->
        <div class="col-12 col-xl-8">
            <div class="card inv-section-card p-0 h-100 overflow-hidden">

                <div class="inv-panel-header">
                    <h6 class="inv-section-title">
                        <i class="fas fa-clock-rotate-left text-primary"></i>
                        Recent Stock Movements
                    </h6>

                    <a href="{{ route('admin.inventory.movements') }}" class="btn btn-sm btn-light border btn-compact shadow-xs">
                        View Ledger <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="table-responsive pc-fit-container">
                    <table class="table align-middle mb-0 dashboard-table">
                        <thead>
                            <tr>
                                <th class="col-dt-item ps-3">Item</th>
                                <th class="col-dt-type">Movement</th>
                                <th class="col-dt-change text-center">Change</th>
                                <th class="col-dt-flow text-center">Balance Flow</th>
                                <th class="col-dt-cost text-end">Cost Impact</th>
                                <th class="col-dt-time text-end pe-3">Time</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($recentMovements as $m)
                                @php
                                    $thumb = $resolveThumb($m->product_thumbnail ?? null);
                                    $variantParts = array_filter([$m->variant_color, $m->variant_size]);
                                    $varText = count($variantParts) > 0 ? implode(' / ', $variantParts) : null;
                                    $costImpact = abs((int)$m->quantity_change) * (float)($m->unit_cost ?? 0);

                                    $badgeClass = match($m->movement_type) {
                                        'purchase_in'              => 'bg-success-subtle text-success border border-success-subtle',
                                        'purchase_receipt_in'      => 'bg-success-subtle text-success border border-success-subtle',
                                        'checkout_sale'            => 'bg-primary-subtle text-primary border border-primary-subtle',
                                        'pos_sale'                 => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                                        'order_processing_deduct'  => 'bg-primary-subtle text-primary border border-primary-subtle',
                                        'order_cancel'             => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                        'order_return'             => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                        'exchange_in'              => 'bg-success-subtle text-success border border-success-subtle',
                                        'exchange_out'             => 'bg-danger-subtle text-danger border border-danger-subtle',
                                        'damage_out'               => 'bg-danger-subtle text-danger border border-danger-subtle',
                                        default                    => 'bg-light text-dark border',
                                    };
                                @endphp

                                <tr>
                                    <td class="col-dt-item ps-3">
                                        <div class="d-flex align-items-center gap-2 min-w-0">
                                            <div class="position-relative flex-shrink-0"
                                                style="width: 32px; height: 32px; border-radius: 5px !important; overflow: hidden; border: 1px solid #e6e9ed; background: #ffffff;">
                                                <img src="{{ $thumb }}"
                                                    class="product-thumb-sm"
                                                    alt="thumb"
                                                    onclick="zoomInvImage('{{ $thumb }}', '{{ addslashes($m->product_title ?? 'Item') }}')"
                                                    onerror="this.src='https://placehold.co/60x60?text=Item';">

                                                <div class="proof-thumb-hover-overlay"
                                                    onclick="zoomInvImage('{{ $thumb }}', '{{ addslashes($m->product_title ?? 'Item') }}')">
                                                    <i class="fas fa-search-plus text-white" style="font-size: 9px;"></i>
                                                </div>
                                            </div>

                                            <div class="min-w-0 flex-grow-1">
                                                <strong class="inv-product-title"
                                                    title="{{ $m->product_title ?? 'Product #' . $m->product_id }}">
                                                    {{ $m->product_title ?? 'Product #' . $m->product_id }}
                                                </strong>

                                                @if($varText)
                                                    <span class="badge bg-light text-secondary border font-monospace inv-variant-badge">
                                                        {{ $varText }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <td class="col-dt-type">
                                        <span class="badge movement-badge-sm {{ $badgeClass }}">
                                            {{ ucwords(str_replace('_', ' ', $m->movement_type)) }}
                                        </span>
                                    </td>

                                    <td class="col-dt-change text-center text-nowrap font-monospace">
                                        @if($m->quantity_change > 0)
                                            <span class="text-success fw-bold">+{{ $m->quantity_change }}</span>
                                        @elseif($m->quantity_change < 0)
                                            <span class="text-danger fw-bold">{{ $m->quantity_change }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>

                                    <td class="col-dt-flow text-center text-nowrap font-monospace text-muted">
                                        <span>{{ $m->quantity_before }}</span>
                                        <i class="fas fa-arrow-right-long mx-1 opacity-50" style="font-size: 8px;"></i>
                                        <strong class="text-dark">{{ $m->quantity_after }}</strong>
                                    </td>

                                    <td class="col-dt-cost text-end text-nowrap font-monospace">
                                        ৳ {{ number_format($costImpact, 2) }}
                                    </td>

                                    <td class="col-dt-time text-end pe-3 text-nowrap text-muted">
                                        {{ \Carbon\Carbon::parse($m->created_at)->diffForHumans() }}
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted small">
                                        <i class="fas fa-box-open fa-2x mb-2 d-block opacity-25"></i>
                                        No recent stock movements found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- Product Image Preview Modal -->
<div class="modal fade inv-modal-scope" id="invImageZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">

            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 13px;" id="invZoomModalTitle">
                    <i class="fas fa-image text-primary me-1"></i> Image Preview
                </span>

                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-2">
                <img id="invZoomModalImgTag"
                    src=""
                    alt="Photo"
                    class="img-fluid inv-modal-image">
            </div>

        </div>
    </div>
</div>

<script>
    function zoomInvImage(url, title = 'Image Preview') {
        const titleEl = document.getElementById('invZoomModalTitle');
        const imgTag  = document.getElementById('invZoomModalImgTag');
        if (url && imgTag) {
            if (titleEl) titleEl.innerText = title;
            imgTag.src = url;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('invImageZoomModal')).show();
        }
    }
</script>
@endsection
