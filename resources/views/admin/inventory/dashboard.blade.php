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
    /* Strict 5px Element Radius Across Controls */
    #invDashRoot .btn,
    #invDashRoot .btn-sm,
    #invDashRoot button,
    #invDashRoot .badge,
    #invDashRoot .alert,
    #invDashRoot .card,
    #invDashRoot .metric-card,
    #invDashRoot .action-card,
    #invDashRoot .action-quick-btn,
    #invDashRoot .metric-icon-box,
    #invDashRoot .product-thumb-sm,
    #invDashRoot .health-stat-card,
    #invDashRoot .health-icon-box,
    #invDashRoot .collapse-toggle-btn,
    .inv-modal-scope .modal-content,
    .inv-modal-scope .btn {
        border-radius: 5px !important;
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
    #invDashRoot .metric-card {
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        padding: 12px 14px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    #invDashRoot .metric-card:hover {
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
        font-size: 17px;
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
    .metric-icon-box {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }

    /* Gradient Palettes for Primary KPIs */
    .metric-card-purple { background: linear-gradient(180deg, #ffffff 0%, #faf5ff 100%); border-color: #e9d5ff; }
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-color: #bbf7d0; }
    .metric-card-red    { background: linear-gradient(180deg, #ffffff 0%, #fef2f2 100%); border-color: #fecaca; }
    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-color: #fde68a; }

    /* Health Stat Action Boxes */
    .health-stat-card {
        border: 1px solid #e2e8f0;
        text-decoration: none;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        display: block;
        overflow: hidden;
        position: relative;
        padding: 10px 12px;
        background: #ffffff;
    }
    .health-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }
    .health-icon-box {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }

    /* Operations Workflow Quick Action Link */
    .action-quick-btn {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        text-decoration: none;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #1e293b;
        font-weight: 600;
        font-size: 12px;
        transition: all 0.15s ease;
    }
    .action-quick-btn:hover {
        background: #ffffff;
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
        transform: translateY(-1px);
        color: #0f172a;
    }

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .dashboard-table {
        width: 100%;
        margin-bottom: 0;
    }
    .dashboard-table thead th {
        font-size: 10px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 10px;
        white-space: nowrap;
    }
    .dashboard-table tbody td {
        padding: 8px 10px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        font-size: 11px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .dashboard-table tbody tr:hover {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .dashboard-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .dashboard-table th,
        .dashboard-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 6 Columns Proportional Distribution Summing to Exactly 100% */
        .col-dt-item   { width: 30% !important; }
        .col-dt-type   { width: 17% !important; }
        .col-dt-change { width: 10% !important; }
        .col-dt-flow   { width: 14% !important; }
        .col-dt-cost   { width: 15% !important; }
        .col-dt-time   { width: 14% !important; }
    }

    .movement-badge-sm {
        font-size: 9px;
        font-weight: 700;
        padding: 2px 6px;
        letter-spacing: 0.2px;
        text-transform: uppercase;
        border-radius: 5px !important;
    }

    /* Interactive Product Image Thumbnail */
    .product-thumb-sm {
        width: 30px;
        height: 30px;
        object-fit: cover;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        flex-shrink: 0;
        cursor: pointer;
        position: relative;
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
    .position-relative:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    /* Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .inv-header-title h5 {
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
            justify-content: center;
        }
        .metric-card {
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
        .metric-icon-box {
            width: 28px !important;
            height: 28px !important;
            font-size: 11px !important;
        }
        .health-stat-card {
            padding: 8px !important;
        }
        .health-stat-card h5 {
            font-size: 14px !important;
        }
        .health-icon-box {
            width: 28px !important;
            height: 28px !important;
            font-size: 11px !important;
        }
        .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .dashboard-table {
            min-width: 540px !important;
            table-layout: auto !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="invDashRoot">

    <!-- 1. Header & Quick Workflow Actions (Original Clean Light Theme) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 inv-header-title">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-boxes-packing text-primary"></i>
                <span>Inventory Intelligence &amp; Stock Dashboard</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Real-time stock valuation, inbound consignments, non-sale write-offs, and catalog health.
            </small>
        </div>
        <div class="header-actions-wrap d-flex flex-wrap gap-2">
            <a href="{{ route('admin.inventory.stock_in') }}" class="btn btn-sm btn-success btn-compact shadow-xs fw-bold">
                <i class="fas fa-arrow-down-long me-1"></i> <span>Stock In</span>
            </a>
            <a href="{{ route('admin.inventory.stock_out') }}" class="btn btn-sm btn-danger btn-compact shadow-xs fw-bold">
                <i class="fas fa-arrow-up-long me-1"></i> <span>Stock Out</span>
            </a>
            <a href="{{ route('admin.inventory.balance') }}" class="btn btn-sm btn-primary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-scale-balanced me-1"></i> <span>Balance Sheet</span>
            </a>
            <a href="{{ route('admin.inventory.reports') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-file-invoice-dollar me-1"></i> <span>Reports</span>
            </a>
        </div>
    </div>

    <!-- 2. Primary KPI Valuation Cards (Collapsible Section) -->
    <div class="card border-0 shadow-sm mb-3" style="border: 1px solid #e2e8f0; background: #ffffff;">
        <div class="p-2 px-3 border-bottom d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                <i class="fas fa-vault text-primary me-1"></i> Core Inventory Valuation &amp; Daily Flow
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapsePrimaryKpis" aria-expanded="true" aria-controls="collapsePrimaryKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapsePrimaryKpis">
            <div class="p-3">
                <div class="row g-2">
                    <!-- Total Inventory Valuation -->
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-purple h-100">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Inventory Valuation</span>
                                <div class="metric-icon-box text-white shadow-xs" style="background-color: #7c3aed;">
                                    <i class="fas fa-vault"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    ৳ {{ number_format($valuation['total_valuation'], 2) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">
                                    <strong>{{ number_format($valuation['total_units']) }}</strong> Units in Stock
                                </small>
                            </div>
                            <div class="mt-2 pt-1 border-top d-flex justify-content-between align-items-center small text-muted font-monospace" style="font-size: 9px;">
                                <span>Simple: ৳{{ number_format($valuation['simple_valuation'], 0) }}</span>
                                <span>•</span>
                                <span>Var: ৳{{ number_format($valuation['variant_valuation'], 0) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Inbound Stock Additions -->
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Today's Stock In</span>
                                <div class="metric-icon-box bg-success text-white shadow-xs">
                                    <i class="fas fa-arrow-down-long"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num">
                                    +{{ number_format($todayInUnits) }} <span style="font-size: 11px;">Units</span>
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">
                                    Cost: ৳ {{ number_format($todayInVal, 2) }}
                                </small>
                            </div>
                            <div class="mt-2 pt-1 border-top d-flex justify-content-between align-items-center small" style="font-size: 9px;">
                                <a href="{{ route('admin.inventory.stock_in') }}" class="text-success text-decoration-none fw-semibold">
                                    <i class="fas fa-plus me-1"></i> Receive In
                                </a>
                                <span class="text-muted font-monospace">Today</span>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Deductions & Write-Offs -->
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-red h-100">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Today's Stock Out</span>
                                <div class="metric-icon-box bg-danger text-white shadow-xs">
                                    <i class="fas fa-arrow-up-long"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-danger bn-digit font-monospace stat-val-num">
                                    -{{ number_format($todayOutUnits) }} <span style="font-size: 11px;">Units</span>
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">
                                    Impact: ৳ {{ number_format($todayOutVal, 2) }}
                                </small>
                            </div>
                            <div class="mt-2 pt-1 border-top d-flex justify-content-between align-items-center small" style="font-size: 9px;">
                                <a href="{{ route('admin.inventory.stock_out') }}" class="text-danger text-decoration-none fw-semibold">
                                    <i class="fas fa-minus me-1"></i> Write-Off
                                </a>
                                <span class="text-muted font-monospace">Sales &amp; Loss</span>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Estimated Gross Profit -->
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber h-100">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Today's Profit</span>
                                <div class="metric-icon-box text-dark shadow-xs" style="background-color: #f59e0b;">
                                    <i class="fas fa-coins"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    ৳ {{ number_format($todayProfit, 2) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">
                                    Rev: ৳{{ number_format($todaySalesRev, 0) }} | COGS: ৳{{ number_format($todaySalesCogs, 0) }}
                                </small>
                            </div>
                            <div class="mt-2 pt-1 border-top d-flex justify-content-between align-items-center small" style="font-size: 9px;">
                                <a href="{{ route('admin.inventory.reports', ['period' => 'today']) }}" class="text-dark text-decoration-none fw-semibold">
                                    Daily P&amp;L <i class="fas fa-arrow-right ms-1" style="font-size: 8px;"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Stock Health & Catalog Status Cards (Adaptive Grid) -->
    <div class="row g-2 mb-3">
        <!-- Low Stock Items Alert -->
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.inventory.balance', ['stock_status' => 'low_stock']) }}" class="health-stat-card border-warning border-opacity-50 bg-warning bg-opacity-10 shadow-xs">
                <div class="d-flex align-items-center gap-2">
                    <div class="health-icon-box bg-warning bg-opacity-25 text-warning-emphasis">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="d-block text-dark fw-bold text-truncate" style="font-size: 11px;">Low Stock Alert</span>
                        <h5 class="fw-bold mb-0 text-warning-emphasis bn-digit font-monospace" style="font-size: 15px;">{{ number_format($lowStockCount) }}</h5>
                    </div>
                </div>
            </a>
        </div>

        <!-- Out of Stock Items Alert -->
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.inventory.balance', ['stock_status' => 'out_of_stock']) }}" class="health-stat-card border-danger border-opacity-50 bg-danger bg-opacity-10 shadow-xs">
                <div class="d-flex align-items-center gap-2">
                    <div class="health-icon-box bg-danger bg-opacity-25 text-danger">
                        <i class="fas fa-circle-xmark"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="d-block text-dark fw-bold text-truncate" style="font-size: 11px;">Out of Stock</span>
                        <h5 class="fw-bold mb-0 text-danger bn-digit font-monospace" style="font-size: 15px;">{{ number_format($outOfStockCount) }}</h5>
                    </div>
                </div>
            </a>
        </div>

        <!-- Total Active Products -->
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.product.index') }}" class="health-stat-card border-info border-opacity-50 bg-info bg-opacity-10 shadow-xs">
                <div class="d-flex align-items-center gap-2">
                    <div class="health-icon-box bg-info bg-opacity-25 text-info-emphasis">
                        <i class="fas fa-box-archive"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="d-block text-dark fw-bold text-truncate" style="font-size: 11px;">Total Products</span>
                        <h5 class="fw-bold mb-0 text-dark bn-digit font-monospace" style="font-size: 15px;">{{ number_format($totalProducts) }}</h5>
                    </div>
                </div>
            </a>
        </div>

        <!-- Total Distinct Variants -->
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.inventory.balance') }}" class="health-stat-card border-primary border-opacity-50 bg-primary bg-opacity-10 shadow-xs">
                <div class="d-flex align-items-center gap-2">
                    <div class="health-icon-box bg-primary bg-opacity-25 text-primary">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="d-block text-dark fw-bold text-truncate" style="font-size: 11px;">Active Variants</span>
                        <h5 class="fw-bold mb-0 text-primary bn-digit font-monospace" style="font-size: 15px;">{{ number_format($totalVariants) }}</h5>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- 4. Quick Workflow Operations Hub & Recent Movements -->
    <div class="row g-2 g-md-3">
        <!-- Quick Operations Hub -->
        <div class="col-12 col-xl-4">
            <div class="card p-3 h-100 border-0 shadow-sm" style="border: 1px solid #e2e8f0; background: #ffffff;">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                    <i class="fas fa-bolt text-warning"></i> <span>Inventory Operations Hub</span>
                </h6>
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('admin.inventory.stock_in') }}" class="action-quick-btn shadow-xs">
                        <div class="metric-icon-box bg-success bg-opacity-15 text-success" style="width: 32px; height: 32px; font-size: 13px;">
                            <i class="fas fa-truck-ramp-box"></i>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <span class="d-block text-dark fw-bold" style="font-size: 12px;">Stock In / Purchase</span>
                            <small class="text-muted" style="font-size: 10px;">Add new shipments with WAC costing</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted opacity-50" style="font-size: 10px;"></i>
                    </a>

                    <a href="{{ route('admin.inventory.stock_out') }}" class="action-quick-btn shadow-xs">
                        <div class="metric-icon-box bg-danger bg-opacity-15 text-danger" style="width: 32px; height: 32px; font-size: 13px;">
                            <i class="fas fa-arrow-up-from-bracket"></i>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <span class="d-block text-dark fw-bold" style="font-size: 12px;">Stock Out / Write-Off</span>
                            <small class="text-muted" style="font-size: 10px;">Damages, expired &amp; supplier return</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted opacity-50" style="font-size: 10px;"></i>
                    </a>

                    <a href="{{ route('admin.inventory.balance') }}" class="action-quick-btn shadow-xs">
                        <div class="metric-icon-box bg-info bg-opacity-15 text-info-emphasis" style="width: 32px; height: 32px; font-size: 13px;">
                            <i class="fas fa-scale-balanced"></i>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <span class="d-block text-dark fw-bold" style="font-size: 12px;">Current Stock Sheet</span>
                            <small class="text-muted" style="font-size: 10px;">Variant-wise valuation &amp; balance</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted opacity-50" style="font-size: 10px;"></i>
                    </a>

                    <a href="{{ route('admin.inventory.reports') }}" class="action-quick-btn shadow-xs">
                        <div class="metric-icon-box bg-secondary bg-opacity-15 text-secondary" style="width: 32px; height: 32px; font-size: 13px;">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <span class="d-block text-dark fw-bold" style="font-size: 12px;">Period Audit Reports</span>
                            <small class="text-muted" style="font-size: 10px;">Opening, closing, COGS &amp; profits</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted opacity-50" style="font-size: 10px;"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Movements Table -->
        <div class="col-12 col-xl-8">
            <div class="card p-0 h-100 overflow-hidden shadow-sm" style="border: 1px solid #e2e8f0; background: #ffffff;">
                <div class="p-2 px-3 border-bottom d-flex justify-content-between align-items-center bg-white">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                        <i class="fas fa-clock-rotate-left text-primary"></i> <span>Recent Stock Movement Audits</span>
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
                                        'purchase_in'      => 'bg-success-subtle text-success border border-success-subtle',
                                        'checkout_sale'    => 'bg-primary-subtle text-primary border border-primary-subtle',
                                        'pos_sale'         => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                                        'order_cancel'     => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                        'order_return'     => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                        'damage_out'       => 'bg-danger-subtle text-danger border border-danger-subtle',
                                        default            => 'bg-light text-dark border',
                                    };
                                @endphp
                                <tr>
                                    <!-- Item with Thumbnail Preview -->
                                    <td class="col-dt-item ps-3">
                                        <div class="d-flex align-items-center gap-2 min-w-0">
                                            <div class="position-relative flex-shrink-0" style="width: 30px; height: 30px; border-radius: 5px !important; overflow: hidden; border: 1px solid #cbd5e1; background: #ffffff;">
                                                <img src="{{ $thumb }}" class="product-thumb-sm" alt="thumb" onclick="zoomInvImage('{{ $thumb }}', '{{ addslashes($m->product_title ?? 'Item') }}')" onerror="this.src='https://placehold.co/60x60?text=Item';">
                                                <div class="proof-thumb-hover-overlay" onclick="zoomInvImage('{{ $thumb }}', '{{ addslashes($m->product_title ?? 'Item') }}')">
                                                    <i class="fas fa-search-plus text-white" style="font-size: 8px;"></i>
                                                </div>
                                            </div>
                                            <div class="min-w-0 flex-grow-1">
                                                <strong class="text-dark d-block text-truncate" style="font-size: 11.5px; line-height: 1.3;" title="{{ $m->product_title ?? 'Product #' . $m->product_id }}">
                                                    {{ $m->product_title ?? 'Product #' . $m->product_id }}
                                                </strong>
                                                @if($varText)
                                                    <span class="badge bg-light text-secondary border font-monospace px-1 py-0 text-truncate d-inline-block" style="font-size: 8.5px; max-width: 120px;">{{ $varText }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Movement Badge -->
                                    <td class="col-dt-type text-nowrap">
                                        <span class="badge movement-badge-sm {{ $badgeClass }}">
                                            {{ ucwords(str_replace('_', ' ', $m->movement_type)) }}
                                        </span>
                                    </td>

                                    <!-- Quantity Change -->
                                    <td class="col-dt-change text-center text-nowrap font-monospace bn-digit">
                                        @if($m->quantity_change > 0)
                                            <span class="text-success fw-bold">+{{ $m->quantity_change }}</span>
                                        @elseif($m->quantity_change < 0)
                                            <span class="text-danger fw-bold">{{ $m->quantity_change }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>

                                    <!-- Flow -->
                                    <td class="col-dt-flow text-center text-nowrap font-monospace bn-digit text-muted" style="font-size: 10.5px;">
                                        <span>{{ $m->quantity_before }}</span> <i class="fas fa-arrow-right-long mx-1 opacity-50" style="font-size: 8px;"></i> <strong class="text-dark">{{ $m->quantity_after }}</strong>
                                    </td>

                                    <!-- Cost Impact -->
                                    <td class="col-dt-cost text-end text-nowrap font-monospace bn-digit" style="font-size: 11px;">
                                        ৳ {{ number_format($costImpact, 2) }}
                                    </td>

                                    <!-- Time -->
                                    <td class="col-dt-time text-end pe-3 text-nowrap text-muted bn-digit" style="font-size: 10px;">
                                        {{ \Carbon\Carbon::parse($m->created_at)->diffForHumans() }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted small">
                                        <i class="fas fa-box-open fa-2x mb-2 d-block opacity-25"></i>
                                        No recent movements logged.
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

<!-- Modal: Lightbox Zoom Modal for Images -->
<div class="modal fade inv-modal-scope" id="invImageZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;" id="invZoomModalTitle">
                    <i class="fas fa-image text-primary me-1"></i> Image Preview
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="invZoomModalImgTag" src="" alt="Photo" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 5px;">
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