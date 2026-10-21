@extends('layouts.admin')

@php
    $siteName = config('app.name', 'Shop Now BD');

    $resolveDashboardImg = function($path, $fallback = 'https://placehold.co/60x60?text=Item') {
        if (empty($path)) return $fallback;
        if (function_exists('show_image')) return show_image($path, $fallback);
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        $clean = ltrim(str_replace(['storage/', 'public/'], '', $path), '/');
        if (file_exists(public_path($clean))) return asset($clean);
        if (file_exists(public_path('uploads/' . $clean))) return asset('uploads/' . $clean);
        return asset($clean);
    };

    $periodLabel = $periodLabel ?? 'This Month';
    $compareLabel = $compareLabel ?? 'Last Month';
    $periodSales = $periodSales ?? 0;
    $prevSales = $prevSales ?? 0;
    $salesGrowth = $salesGrowth ?? 0;
    $periodTotalOrders = $periodTotalOrders ?? 0;
    $prevTotalOrders = $prevTotalOrders ?? 0;
    $ordersGrowth = $ordersGrowth ?? 0;
    $onlineSales = $onlineSales ?? 0;
    $posSales = $posSales ?? 0;
    $onlineOrdersCount = $onlineOrdersCount ?? 0;
    $posOrdersCount = $posOrdersCount ?? 0;
    $estimatedCogs = $estimatedCogs ?? 0;
    $estimatedGrossProfit = $estimatedGrossProfit ?? 0;
    $grossProfitMargin = $grossProfitMargin ?? 0;
    $totalPaidAmount = $totalPaidAmount ?? 0;
    $totalDueAmount = $totalDueAmount ?? 0;
    $newUsersCount = $newUsersCount ?? 0;
    $newUsersGrowth = $newUsersGrowth ?? 0;

    $activeProductsCount  = $activeProductsCount ?? 0;
    $activeProductsValue  = $activeProductsValue ?? 0;
    $lowStockCount        = $lowStockCount ?? 0;
    $outOfStockCount      = $outOfStockCount ?? 0;

    $chartLabels   = $chartLabels ?? [];
    $chartCounts   = $chartCounts ?? [];
    $chartRevenues = $chartRevenues ?? [];

    $topCategories = $topCategories ?? collect();
    $topProducts   = $topProducts ?? collect();
    $topCustomers  = $topCustomers ?? collect();
    $recentOrders  = $recentOrders ?? collect();
    $paymentMethodStats = $paymentMethodStats ?? collect();
    $orderStatusCounts  = $orderStatusCounts ?? [];
    $productSalesLedger = $productSalesLedger ?? collect();
    $customerSalesLedger = $customerSalesLedger ?? collect();
@endphp

@section('title', 'Business Intelligence & Sales Audit - ' . $siteName)

@section('admin_content')
<!-- html2pdf Client-side Instant A4 Landscape Auto Downloader -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
    /* Strict 5px Element Radius */
    #dashboard-analytics-root .btn,
    #dashboard-analytics-root .btn-sm,
    #dashboard-analytics-root button,
    #dashboard-analytics-root .form-control,
    #dashboard-analytics-root .form-select,
    #dashboard-analytics-root .badge,
    #dashboard-analytics-root .growth-badge,
    #dashboard-analytics-root .collapse-toggle-btn {
        border-radius: 5px !important;
    }

    #dashboard-analytics-root .card,
    #dashboard-analytics-root .dash-widget-box,
    #dashboard-analytics-root .filter-toolbar-card,
    #dashboard-analytics-root .spotlight-card,
    #dashboard-analytics-root .metric-soft-card {
        border-radius: 8px !important;
    }

    /* Metric Soft Cards */
    .metric-soft-card {
        padding: 16px 18px;
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        height: 100%;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .metric-soft-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
    }
    .metric-icon-square {
        width: 42px;
        height: 42px;
        border-radius: 6px !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        flex-shrink: 0;
    }

    .metric-val {
        font-size: 1.35rem;
        letter-spacing: -0.02em;
        line-height: 1.25;
    }

    /* Soft Gradient Palettes */
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-color: #bbf7d0; }
    .metric-card-blue   { background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%); border-color: #bae6fd; }
    .metric-card-orange { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-color: #fde68a; }
    .metric-card-purple { background: linear-gradient(180deg, #ffffff 0%, #faf5ff 100%); border-color: #e9d5ff; }
    .metric-card-yellow { background: linear-gradient(180deg, #ffffff 0%, #fef2f2 100%); border-color: #fecaca; }
    .metric-card-mint   { background: linear-gradient(180deg, #ffffff 0%, #f0fdfa 100%); border-color: #99f6e4; }

    .dash-widget-box {
        background: #ffffff;
        padding: 18px 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 20px;
    }
    .filter-toolbar-card {
        background: #ffffff;
        padding: 16px 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 20px;
    }
    .widget-item-thumb {
        width: 38px;
        height: 38px;
        border-radius: 5px !important;
        object-fit: cover;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }
    .growth-badge {
        font-size: 11px;
        padding: 3px 6px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        line-height: 1.2;
    }
    .spotlight-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e2e8f0;
        padding: 16px 18px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
        height: 100%;
    }

    /* Bengali Safe Typography */
    .bn-digit, 
    .stat-amount, 
    .kpi-number {
        font-weight: 700 !important;
    }

    /* Tables */
    .table thead th {
        font-size: 11.5px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #64748b;
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 10px 12px;
    }
    .table tbody td {
        padding: 10px 12px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        font-size: 12.5px;
    }

    /* Row Gaps */
    .analytics-row-gap {
        margin-bottom: 20px !important;
    }

    /* Collapsible Animation & Chevron Rotation */
    .collapse-toggle-btn {
        width: 28px;
        height: 28px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #f8fafc;
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
        font-size: 11px;
    }

    /* Mobile Symmetric Adjustments */
    @media (max-width: 767.98px) {
        .analytics-row-gap {
            margin-bottom: 16px !important;
        }
        .dash-widget-box,
        .filter-toolbar-card {
            padding: 14px 16px !important;
            margin-bottom: 16px !important;
        }
        .metric-soft-card {
            padding: 14px 16px !important;
        }
        .metric-icon-square {
            width: 38px !important;
            height: 38px !important;
            font-size: 15px !important;
        }
        .metric-val {
            font-size: 1.25rem !important;
        }
        .spotlight-card {
            padding: 14px 16px !important;
        }
        .top-action-btn-group {
            width: 100% !important;
        }
        .top-action-btn-group .btn {
            flex: 1 1 0 !important;
            padding: 7px 10px !important;
            font-size: 11.5px !important;
        }
    }

    /* PDF Rendering Optimization */
    .pdf-render-active .no-pdf,
    .pdf-render-active #dashLoadingCover {
        display: none !important;
    }
    .pdf-render-active {
        background: #ffffff !important;
        box-shadow: none !important;
    }
    .pdf-render-active .table-responsive {
        overflow: visible !important;
    }
    .pdf-render-active table {
        page-break-inside: auto !important;
    }
    .pdf-render-active tr {
        page-break-inside: avoid !important;
    }
</style>

<div class="position-relative" id="dashboard-analytics-root">
    <!-- AJAX Loading Spinner Overlay -->
    <div id="dashLoadingCover" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none justify-content-center align-items-center" style="z-index: 50; border-radius: 8px;">
        <div class="spinner-border text-primary" style="width: 2.5rem; height: 2.5rem;" role="status"></div>
    </div>

    <!-- 1. Header & Top Action Buttons -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-0.5 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-chart-line text-primary"></i> Sales Audit &amp; Business Analytics
            </h5>
            <small class="text-muted" style="font-size: 12px; line-height: 1.35; display: block;">Real-time revenue intelligence, timeline performance &amp; customer ledger</small>
        </div>
        <div class="d-flex gap-2 top-action-btn-group no-pdf">
            <button type="button" class="btn btn-sm btn-outline-success d-flex align-items-center justify-content-center gap-1.5 shadow-xs py-1.5 px-3" onclick="exportDashboardAudit('excel')">
                <i class="fas fa-file-excel text-success me-1"></i> <span>Export (Excel)</span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center gap-1.5 shadow-xs py-1.5 px-3" id="btn-dash-instant-pdf" onclick="downloadDashboardInstantPdf()">
                <i class="fas fa-download text-danger me-1"></i> <span id="dash-pdf-btn-text">Report (PDF A4)</span>
            </button>
        </div>
    </div>

    <!-- 2. Filter Toolbar -->
    <div class="filter-toolbar-card no-pdf">
        <form id="dash-filter-form" onsubmit="return false;">
            <div class="row g-2.5 align-items-center">
                <!-- Timeframe Preset -->
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1" style="font-size: 11.5px;"><i class="fas fa-calendar-alt text-primary me-1"></i> Reporting Period</label>
                    <select name="period" id="dash-period-select" class="form-select form-select-sm fw-semibold shadow-none border-secondary-subtle py-1.5">
                        <option value="today">Today's Report</option>
                        <option value="yesterday">Yesterday's Report</option>
                        <option value="this_week">This Week (Mon - Sun)</option>
                        <option value="last_week">Last Week</option>
                        <option value="this_month" selected>This Month (Current)</option>
                        <option value="last_month">Last Month</option>
                        <option value="first_6_months">First 6 Months (Jan - Jun)</option>
                        <option value="last_6_months">Last 6 Months (Jul - Dec)</option>
                        <option value="this_year">This Year (Current)</option>
                        <option value="last_year">Last Year</option>
                        <option value="specific_date">Specific Single Date...</option>
                        <option value="specific_month">Specific Month...</option>
                        <option value="specific_year">Specific Year...</option>
                        <option value="custom">Custom Range (From - To)...</option>
                    </select>
                </div>

                <!-- Comparison Mode Selector -->
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1" style="font-size: 11.5px;"><i class="fas fa-scale-balanced text-success me-1"></i> Comparison Analytics</label>
                    <select name="compare_mode" id="dash-compare-select" class="form-select form-select-sm shadow-none border-secondary-subtle py-1.5">
                        <option value="default" selected>Auto: vs Previous Period</option>
                        <option value="yesterday_vs_today">Yesterday vs Today</option>
                        <option value="last_week_vs_this_week">Last Week vs This Week</option>
                        <option value="last_month_vs_this_month">Last Month vs This Month</option>
                        <option value="last_year_vs_this_year">Last Year vs This Year</option>
                        <option value="first_6_vs_last_6">First 6 Months vs Last 6 Months</option>
                    </select>
                </div>

                <!-- Live Period Indicator & Apply Button -->
                <div class="col-12 col-md-6 pt-1 pt-md-3">
                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center justify-content-md-end gap-2">
                        <div class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 text-center small fw-semibold text-wrap text-break w-100 w-sm-auto" style="font-size: 11px; white-space: normal !important; line-height: 1.4; max-width: 100%;">
                            <span id="activePeriodLabel">{{ $periodLabel }}</span> 
                            <span class="text-muted fw-normal mx-1">vs</span> 
                            <span id="comparePeriodLabel">{{ $compareLabel }}</span>
                        </div>
                        <button type="button" class="btn btn-dark btn-sm px-3.5 py-1.5 fw-bold d-flex align-items-center justify-content-center gap-1.5 shadow-xs flex-shrink-0" style="min-height: 33px; font-size: 12px;" onclick="applyDashboardFilter()">
                            <i class="fas fa-sync-alt me-1" style="font-size: 10px;"></i> <span>Refresh Data</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Panel A: Specific Single Date -->
            <div id="specificDatePanel" class="row g-2 align-items-center mt-2.5 pt-2.5 border-top d-none">
                <div class="col-12 col-sm-auto small fw-bold text-muted" style="font-size: 11.5px;">Select Specific Date:</div>
                <div class="col-12 col-sm-auto">
                    <input type="date" name="specific_date" id="dash-specific-date" class="form-control form-control-sm py-1" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-12 col-sm-auto">
                    <button type="button" class="btn btn-primary btn-sm px-3 py-1" style="font-size: 12px;" onclick="applyDashboardFilter()">Apply Date</button>
                </div>
            </div>

            <!-- Panel B: Specific Month -->
            <div id="specificMonthPanel" class="row g-2 align-items-center mt-2.5 pt-2.5 border-top d-none">
                <div class="col-12 col-sm-auto small fw-bold text-muted" style="font-size: 11.5px;">Select Specific Month:</div>
                <div class="col-12 col-sm-auto">
                    <input type="month" name="specific_month" id="dash-specific-month" class="form-control form-control-sm py-1" value="{{ date('Y-m') }}">
                </div>
                <div class="col-12 col-sm-auto">
                    <button type="button" class="btn btn-primary btn-sm px-3 py-1" style="font-size: 12px;" onclick="applyDashboardFilter()">Apply Month</button>
                </div>
            </div>

            <!-- Panel C: Specific Year -->
            <div id="specificYearPanel" class="row g-2 align-items-center mt-2.5 pt-2.5 border-top d-none">
                <div class="col-12 col-sm-auto small fw-bold text-muted" style="font-size: 11.5px;">Select Year:</div>
                <div class="col-12 col-sm-auto">
                    <input type="number" name="specific_year" id="dash-specific-year" class="form-control form-control-sm py-1" min="2020" max="2035" value="{{ date('Y') }}">
                </div>
                <div class="col-12 col-sm-auto">
                    <button type="button" class="btn btn-primary btn-sm px-3 py-1" style="font-size: 12px;" onclick="applyDashboardFilter()">Apply Year</button>
                </div>
            </div>

            <!-- Panel D: Custom Range -->
            <div id="customRangePanel" class="row g-2 align-items-center mt-2.5 pt-2.5 border-top d-none">
                <div class="col-12 col-sm-auto small fw-bold text-muted" style="font-size: 11.5px;">Custom Date Range:</div>
                <div class="col-6 col-sm-auto">
                    <input type="date" name="start_date" id="dash-start-date" class="form-control form-control-sm py-1">
                </div>
                <div class="col-6 col-sm-auto">
                    <input type="date" name="end_date" id="dash-end-date" class="form-control form-control-sm py-1">
                </div>
                <div class="col-12 col-sm-auto">
                    <button type="button" class="btn btn-primary btn-sm px-3 py-1" style="font-size: 12px;" onclick="applyDashboardFilter()">Apply Range</button>
                </div>
            </div>
        </form>
    </div>

    <!-- 3. Primary KPI Row -->
    <div class="row g-3 analytics-row-gap">
        <!-- Delivered Revenue -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-soft-card metric-card-green">
                <div class="min-w-0 pe-1">
                    <small class="text-muted d-block fw-semibold" style="font-size: 11.5px;">Delivered Revenue</small>
                    <h5 class="fw-bold my-1 text-dark bn-digit metric-val" id="val-period-sales">৳ {{ number_format($periodSales, 2) }}</h5>
                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                        <span class="growth-badge {{ $salesGrowth >= 0 ? 'bg-success text-white' : 'bg-danger text-white me-1' }} bn-digit" id="kpi-growth-sales">
                            <i class="fas {{ $salesGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i> {{ abs($salesGrowth) }}%
                        </span>
                        <small class="text-muted bn-digit text-nowrap" style="font-size: 11px;">vs Prev (৳<span id="val-prev-sales">{{ number_format($prevSales, 0) }}</span>)</small>
                    </div>
                </div>
                <div class="metric-icon-square bg-success text-white shadow-xs"><i class="fas fa-hand-holding-dollar"></i></div>
            </div>
        </div>

        <!-- Orders Volume -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-soft-card metric-card-blue">
                <div class="min-w-0 pe-1">
                    <small class="text-muted d-block fw-semibold" style="font-size: 11.5px;">Total Orders Placed</small>
                    <h5 class="fw-bold my-1 text-dark bn-digit metric-val" id="val-period-orders">{{ $periodTotalOrders }}</h5>
                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                        <span class="growth-badge {{ $ordersGrowth >= 0 ? 'bg-primary text-white' : 'bg-danger text-white me-1' }} bn-digit" id="kpi-growth-orders">
                            <i class="fas {{ $ordersGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i> {{ abs($ordersGrowth) }}%
                        </span>
                        <small class="text-muted bn-digit text-nowrap" style="font-size: 11px;">Prev: <span id="val-prev-orders">{{ $prevTotalOrders }}</span> orders</small>
                    </div>
                </div>
                <div class="metric-icon-square bg-primary text-white shadow-xs"><i class="fas fa-bag-shopping"></i></div>
            </div>
        </div>

        <!-- Estimated Gross Profit -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-soft-card metric-card-orange">
                <div class="min-w-0 pe-1">
                    <small class="text-muted d-block fw-semibold" style="font-size: 11.5px;">Estimated Gross Profit</small>
                    <h5 class="fw-bold my-1 text-dark bn-digit metric-val" id="val-gross-profit">৳ {{ number_format($estimatedGrossProfit, 2) }}</h5>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="growth-badge bg-warning text-dark bn-digit" id="val-profit-margin">
                            Margin: {{ $grossProfitMargin }}%
                        </span>
                        <small class="text-muted bn-digit text-nowrap" style="font-size: 11px;">COGS: ৳<span id="val-cogs">{{ number_format($estimatedCogs, 0) }}</span></small>
                    </div>
                </div>
                <div class="metric-icon-square bg-warning text-dark shadow-xs"><i class="fas fa-scale-balanced"></i></div>
            </div>
        </div>

        <!-- New Customers -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-soft-card metric-card-purple">
                <div class="min-w-0 pe-1">
                    <small class="text-muted d-block fw-semibold" style="font-size: 11.5px;">New Customer Signups</small>
                    <h5 class="fw-bold my-1 text-dark bn-digit metric-val" id="val-new-users">{{ $newUsersCount }}</h5>
                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                        <span class="growth-badge {{ $newUsersGrowth >= 0 ? 'bg-secondary text-white' : 'bg-danger text-white me-1' }} bn-digit" style="background-color: #6b21a8 !important;" id="kpi-growth-users">
                            <i class="fas {{ $newUsersGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i> {{ abs($newUsersGrowth) }}%
                        </span>
                        <small class="text-muted text-nowrap" style="font-size: 11px;">in period</small>
                    </div>
                </div>
                <div class="metric-icon-square text-white shadow-xs" style="background-color: #7c3aed;"><i class="fas fa-user-plus"></i></div>
            </div>
        </div>
    </div>

    <!-- 4. Secondary Row: Channels, Receivables, Inventory -->
    <div class="row g-3 analytics-row-gap">
        <div class="col-12 col-md-4">
            <div class="metric-soft-card metric-card-blue">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold" style="font-size: 11.5px;">Channel Sales Breakdown</small>
                    <span class="fw-bold text-dark d-block mt-1 bn-digit text-truncate" style="font-size: 13px;">
                        <i class="fas fa-globe text-primary me-1"></i> Web: ৳<span id="val-online-sales">{{ number_format($onlineSales, 0) }}</span> (<span id="val-online-count">{{ $onlineOrdersCount }}</span>)
                    </span>
                    <span class="fw-bold text-dark d-block mt-0.5 bn-digit text-truncate" style="font-size: 13px;">
                        <i class="fas fa-cash-register text-success me-1"></i> POS: ৳<span id="val-pos-sales">{{ number_format($posSales, 0) }}</span> (<span id="val-pos-count">{{ $posOrdersCount }}</span>)
                    </span>
                </div>
                <div class="metric-icon-square bg-info text-white shadow-xs"><i class="fas fa-arrows-split-up-and-left"></i></div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="metric-soft-card metric-card-yellow">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold" style="font-size: 11.5px;">Receivables &amp; Due Audit</small>
                    <h6 class="fw-bold my-1 text-danger bn-digit" style="font-size: 14px;">Due: ৳<span id="val-total-due">{{ number_format($totalDueAmount, 2) }}</span></h6>
                    <small class="text-success fw-bold bn-digit text-nowrap d-block" style="font-size: 11.5px;">
                        <i class="fas fa-check-circle me-1"></i> Collected: ৳<span id="val-total-paid">{{ number_format($totalPaidAmount, 2) }}</span>
                    </small>
                </div>
                <div class="metric-icon-square bg-danger-subtle text-danger shadow-xs"><i class="fas fa-file-invoice-dollar"></i></div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="metric-soft-card metric-card-mint">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold" style="font-size: 11.5px;">Catalog Inventory Health</small>
                    <h6 class="fw-bold my-1 text-dark bn-digit" style="font-size: 14px;">{{ $activeProductsCount }} In Stock</h6>
                    <div class="d-flex gap-2 flex-wrap align-items-center mt-1">
                        <span class="badge bg-warning text-dark bn-digit px-1.5 py-0.5" style="font-size: 10px;">Low: {{ $lowStockCount }}</span>
                        <span class="badge bg-danger text-white bn-digit px-1.5 py-0.5" style="font-size: 10px;">Out: {{ $outOfStockCount }}</span>
                        <span class="text-muted bn-digit" style="font-size: 11px;">Val: ৳{{ number_format($activeProductsValue, 0) }}</span>
                    </div>
                </div>
                <div class="metric-icon-square text-white shadow-xs" style="background:#0d9488;"><i class="fas fa-boxes-stacked"></i></div>
            </div>
        </div>
    </div>

    <!-- 5. Period Spotlight -->
    <div class="row g-3 analytics-row-gap">
        <div class="col-12 col-md-6">
            <div class="spotlight-card d-flex align-items-center justify-content-between">
                <div class="min-w-0 pe-2">
                    <span class="badge bg-warning text-dark text-uppercase px-2 py-0.5 mb-1.5 fw-bold mb-2" style="font-size: 9.5px; letter-spacing: 0.4px;">
                        <i class="fas fa-crown me-1"></i> Period Best Selling Product
                    </span>
                    <h6 class="fw-bold text-dark mb-0.5 text-truncate" style="font-size: 13.5px;" id="spotlight-product-title">
                        {{ $bestSellingProduct->product_title ?? 'No sales recorded yet' }}
                    </h6>
                    <small class="text-muted font-monospace d-block" style="font-size: 11px;" id="spotlight-product-code">
                        SKU: {{ $bestSellingProduct->sku ?? ($bestSellingProduct->product_code ?? 'N/A') }}
                    </small>
                    <div class="mt-1.5 text-dark small bn-digit" style="font-size: 12px; line-height: 1.4;">
                        Sold: <strong class="text-primary" id="spotlight-product-qty">{{ $bestSellingProduct->total_qty ?? 0 }} pcs</strong> &bull; 
                        Orders: <strong id="spotlight-product-orders">{{ $bestSellingProduct->times_sold ?? 0 }} times</strong> &bull; 
                        Revenue: <strong class="text-success">৳<span id="spotlight-product-rev">{{ number_format($bestSellingProduct->total_revenue ?? 0, 2) }}</span></strong>
                    </div>
                </div>
                <div class="text-warning opacity-75 pe-1 flex-shrink-0" style="font-size: 28px;"><i class="fas fa-medal"></i></div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="spotlight-card d-flex align-items-center justify-content-between">
                <div class="min-w-0 pe-2">
                    <span class="badge bg-primary text-white mb-2 text-uppercase px-2 py-0.5 mb-1.5 fw-bold" style="font-size: 9.5px; letter-spacing: 0.4px;">
                        <i class="fas fa-star me-1"></i> Period Top Spender
                    </span>
                    <h6 class="fw-bold text-dark mb-0.5 text-truncate" style="font-size: 13.5px;" id="spotlight-customer-name">
                        {{ $bestCustomer->customer_name ?? 'No customer activity yet' }}
                        @if(!empty($bestCustomer->user_id))
                            <span class="badge bg-light text-primary border font-monospace ms-1" style="font-size: 9.5px;">UID: #{{ $bestCustomer->user_id }}</span>
                        @else
                            <span class="badge bg-light text-muted border ms-1" style="font-size: 9.5px;">Guest</span>
                        @endif
                    </h6>
                    <small class="text-muted d-block bn-digit" style="font-size: 11px;" id="spotlight-customer-phone">
                        {{ $bestCustomer->customer_phone ?? 'N/A' }}
                    </small>
                    <div class="mt-1.5 text-dark small bn-digit" style="font-size: 12px; line-height: 1.4;">
                        Orders: <strong class="text-primary" id="spotlight-customer-orders">{{ $bestCustomer->total_orders_placed ?? 0 }} orders</strong> &bull; 
                        Spent: <strong class="text-success">৳<span id="spotlight-customer-spent">{{ number_format($bestCustomer->total_spent ?? 0, 2) }}</span></strong> &bull; 
                        Paid: <strong>৳<span id="spotlight-customer-paid">{{ number_format($bestCustomer->total_paid ?? 0, 2) }}</span></strong>
                    </div>
                </div>
                <div class="text-primary opacity-75 pe-1 flex-shrink-0" style="font-size: 28px;"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
    </div>

    <!-- 6. Revenue & Volume Timeline Activity Chart -->
    <div class="dash-widget-box analytics-row-gap">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-1.5 mb-2.5">
            <div>
                <h6 class="fw-bold text-dark mb-0" style="font-size: 14px;">Sales &amp; Orders Volume Trend</h6>
                <small class="text-muted" style="font-size: 11.5px;">Dynamic chronological curve of customer transactions and revenue</small>
            </div>
            <span class="badge bg-light text-dark border px-2.5 py-1 fw-semibold" style="font-size: 11px;" id="chartPeriodBadge">{{ $periodLabel }}</span>
        </div>
        <div style="height: 280px; position: relative;" class="w-100 rounded-2 p-1.5 bg-light border mt-1">
            <canvas id="dailyOrdersChart"></canvas>
        </div>
    </div>

    <!-- 7. Product-Wise Sales, Quantity & Buyers Audit Ledger (COLLAPSIBLE) -->
    <div class="dash-widget-box analytics-row-gap" id="product-ledger-widget-box">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
            <div>
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5" style="font-size: 14px;">
                    <i class="fas fa-boxes text-primary me-1"></i> Product Sales &amp; Buyers Ledger
                </h6>
                <small class="text-muted" style="font-size: 11.5px;">Itemized audit of product transaction frequency, SKU, quantity and margins</small>
            </div>
            <div class="d-flex gap-2 top-action-btn-group no-pdf align-items-center">
                <button type="button" class="btn btn-outline-success btn-sm px-2.5 py-1 fw-semibold shadow-xs" style="font-size: 11.5px;" onclick="exportDashboardAudit('excel')">
                    <i class="fas fa-file-excel me-1 text-success"></i> Export Excel
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm px-2.5 py-1 fw-semibold shadow-xs" style="font-size: 11.5px;" id="btn-export-ledger-pdf" onclick="downloadProductLedgerInstantPdf()">
                    <i class="fas fa-file-pdf me-1 text-danger"></i> <span id="ledger-pdf-btn-text">Export PDF</span>
                </button>
                <!-- Collapse Trigger Button -->
                <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseProductLedger" aria-expanded="false" aria-controls="collapseProductLedger" title="Toggle Section View">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse" id="collapseProductLedger">
            <div class="table-responsive pt-3">
                <table class="table align-middle table-hover mb-0" id="product-ledger-table">
                    <thead>
                        <tr>
                            <th class="bn-digit">ID</th>
                            <th>Name &amp; SKU</th>
                            <th>Unit Price</th>
                            <th class="text-center">Times Sold</th>
                            <th class="text-center">Units Sold</th>
                            <th class="text-end">Revenue</th>
                            <th class="text-end">Gross Profit</th>
                            <th>Associated Buyers</th>
                        </tr>
                    </thead>
                    <tbody id="product-ledger-tbody">
                        @include('admin.dashboard.partials.product_ledger_rows', ['ledger' => $productSalesLedger])
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 8. Customer Purchase Performance Table (COLLAPSIBLE) -->
    <div class="dash-widget-box analytics-row-gap" id="customer-ledger-widget-box">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5" style="font-size: 14px;">
                    <i class="fas fa-users-viewfinder text-primary me-2"></i> Top Customers Spending &amp; Settlement Performance
                </h6>
                <small class="text-muted" style="font-size: 11.5px;">Ranked customer audit with user identification, orders, and settlements</small>
            </div>
            <div class="d-flex align-items-center gap-2 no-pdf">
                <!-- Collapse Trigger Button -->
                <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseCustomerLedger" aria-expanded="false" aria-controls="collapseCustomerLedger" title="Toggle Section View">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse" id="collapseCustomerLedger">
            <div class="table-responsive pt-3">
                <table class="table align-middle table-hover mb-0" id="customer-ledger-table">
                    <thead>
                        <tr>
                            <th>Customer Name &amp; ID</th>
                            <th class="text-center">Orders Placed</th>
                            <th class="text-end">Total Spent (BDT)</th>
                            <th class="text-end">Total Paid</th>
                            <th class="text-end">Last Order Date</th>
                        </tr>
                    </thead>
                    <tbody id="customer-ledger-tbody">
                        @include('admin.dashboard.partials.customer_ledger_rows', ['customers' => $customerSalesLedger])
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 9. Secondary Row: Status Distribution & Payment Gateway Audit (COLLAPSIBLE) -->
    <div class="row g-3 analytics-row-gap">
        <!-- Status Distribution -->
        <div class="col-12 col-md-6">
            <div class="dash-widget-box h-100 mb-0">
                <div class="d-flex justify-content-between align-items-center pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-chart-pie text-primary" style="font-size: 14px;"></i>
                        <h6 class="fw-bold text-dark mb-0" style="font-size: 13.5px;">Order Status Distribution</h6>
                    </div>
                    <!-- Collapse Trigger Button -->
                    <button type="button" class="collapse-toggle-btn shadow-xs no-pdf" data-bs-toggle="collapse" data-bs-target="#collapseOrderStatus" aria-expanded="false" aria-controls="collapseOrderStatus" title="Toggle Section View">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>

                <div class="collapse" id="collapseOrderStatus">
                    <div class="d-flex flex-column gap-2 pt-3">
                        <div class="d-flex justify-content-between align-items-center py-2 px-2.5 rounded-2 bg-light border border-light-subtle">
                            <span class="small fw-semibold text-warning d-inline-flex align-items-center gap-2" style="font-size: 12px;">
                                <i class="fas fa-clock ms-2"></i> <span>Pending</span>
                            </span>
                            <span class="badge bg-warning text-dark px-2.5 py-1 bn-digit me-2" id="stat-count-pending">{{ $orderStatusCounts['pending'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 px-2.5 rounded-2 bg-light border border-light-subtle">
                            <span class="small fw-semibold text-info d-inline-flex align-items-center gap-2" style="font-size: 12px;">
                                <i class="fas fa-spinner ms-2"></i> <span>Processing</span>
                            </span>
                            <span class="badge bg-info text-dark px-2.5 py-1 bn-digit me-2" id="stat-count-processing">{{ $orderStatusCounts['processing'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 px-2.5 rounded-2 bg-light border border-light-subtle">
                            <span class="small fw-semibold text-success d-inline-flex align-items-center gap-2" style="font-size: 12px;">
                                <i class="fas fa-check-circle ms-2"></i> <span>Delivered</span>
                            </span>
                            <span class="badge bg-success text-white px-2.5 py-1 bn-digit me-2" id="stat-count-completed">{{ $orderStatusCounts['completed'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 px-2.5 rounded-2 bg-light border border-light-subtle">
                            <span class="small fw-semibold text-danger d-inline-flex align-items-center gap-2" style="font-size: 12px;">
                                <i class="fas fa-times-circle ms-2"></i> <span>Cancelled</span>
                            </span>
                            <span class="badge bg-danger text-white px-2.5 py-1 bn-digit me-2" id="stat-count-cancelled">{{ $orderStatusCounts['cancelled'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 px-2.5 rounded-2 bg-light border border-light-subtle">
                            <span class="small fw-semibold text-secondary d-inline-flex align-items-center gap-2" style="font-size: 12px;">
                                <i class="fas fa-rotate-left ms-2"></i> <span>Returned</span>
                            </span>
                            <span class="badge bg-secondary text-white px-2.5 py-1 bn-digit me-2" id="stat-count-returned">{{ $orderStatusCounts['returned'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Gateway Audit -->
        <div class="col-12 col-md-6">
            <div class="dash-widget-box h-100 mb-0">
                <div class="d-flex justify-content-between align-items-start pb-2 border-bottom">
                    <div class="d-flex align-items-start gap-2 min-w-0 flex-grow-1">
                        <i class="fas fa-money-check-dollar text-success mt-1" style="font-size: 14px;"></i>
                        <div class="min-w-0 flex-grow-1">
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 13.5px; line-height: 1.35;">
                                Payment Gateway Audit
                            </h6>
                            <small class="text-muted d-block" style="font-size: 11px;">
                                Period: <span id="gatewayAuditPeriod" class="fw-semibold text-dark">{{ $periodLabel }}</span>
                            </small>
                        </div>
                    </div>
                    <!-- Collapse Trigger Button -->
                    <button type="button" class="collapse-toggle-btn shadow-xs no-pdf flex-shrink-0" data-bs-toggle="collapse" data-bs-target="#collapsePaymentGateway" aria-expanded="false" aria-controls="collapsePaymentGateway" title="Toggle Section View">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>

                <div class="collapse" id="collapsePaymentGateway">
                    <div class="table-responsive pt-3">
                        <table class="table table-sm table-borderless align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Gateway / Method</th>
                                    <th class="text-center">Orders</th>
                                    <th class="text-end">Settled Total</th>
                                </tr>
                            </thead>
                            <tbody id="payment-gateway-tbody">
                                @include('admin.dashboard.partials.payment_method_rows', ['paymentMethodStats' => $paymentMethodStats])
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Responsive Bootstrap 5 Modal Notification -->
<div class="modal fade" id="dashAlertModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3" style="border-radius: 8px;">
            <div class="modal-body p-1">
                <div class="mb-2 text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Notice</h6>
                <p class="text-muted small mb-3" style="font-size: 12px;" id="dashAlertModalMsg">An error occurred during operation.</p>
                <button type="button" class="btn btn-sm btn-dark px-4 py-1 fw-semibold" style="font-size: 12px;" data-bs-dismiss="modal">Acknowledge</button>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Script & Real-Time AJAX Engine -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let myChartInstance = null;

    function showDashboardAlert(message) {
        const modalEl = document.getElementById('dashAlertModal');
        const msgEl = document.getElementById('dashAlertModalMsg');
        if (modalEl && window.bootstrap) {
            if (msgEl) msgEl.innerText = message;
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else {
            console.error(message);
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        const chartElement = document.getElementById('dailyOrdersChart');
        if (chartElement) {
            const ctx = chartElement.getContext('2d');
            const labels = {!! json_encode($chartLabels) !!};
            const counts = {!! json_encode($chartCounts) !!};
            const revenues = {!! json_encode($chartRevenues) !!};

            myChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Revenue (৳)',
                            data: revenues,
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.08)',
                            borderWidth: 2,
                            pointBackgroundColor: '#2563eb',
                            pointBorderColor: '#ffffff',
                            tension: 0.35,
                            fill: true,
                            yAxisID: 'yRevenue'
                        },
                        {
                            label: 'Orders Count',
                            data: counts,
                            borderColor: '#10b981',
                            backgroundColor: 'transparent',
                            borderWidth: 1.8,
                            borderDash: [4, 4],
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#ffffff',
                            tension: 0.35,
                            fill: false,
                            yAxisID: 'yOrders'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        yRevenue: {
                            type: 'linear', position: 'left', beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: { callback: v => '৳' + v, font: { size: 9.5 } }
                        },
                        yOrders: {
                            type: 'linear', position: 'right', beginAtZero: true,
                            grid: { drawOnChartArea: false },
                            ticks: { precision: 0, font: { size: 9.5 } }
                        },
                        x: { grid: { display: false }, ticks: { maxTicksLimit: 12, font: { size: 9.5 } } }
                    },
                    plugins: {
                        legend: { display: true, position: 'top', labels: { boxWidth: 10, font: { size: 10.5, weight: '600' } } }
                    }
                }
            });
        }

        const periodSelect = document.getElementById('dash-period-select');
        periodSelect.addEventListener('change', function() {
            const val = this.value;
            document.getElementById('specificDatePanel').classList.add('d-none');
            document.getElementById('specificMonthPanel').classList.add('d-none');
            document.getElementById('specificYearPanel').classList.add('d-none');
            document.getElementById('customRangePanel').classList.add('d-none');

            if (val === 'specific_date') {
                document.getElementById('specificDatePanel').classList.remove('d-none');
            } else if (val === 'specific_month') {
                document.getElementById('specificMonthPanel').classList.remove('d-none');
            } else if (val === 'specific_year') {
                document.getElementById('specificYearPanel').classList.remove('d-none');
            } else if (val === 'custom') {
                document.getElementById('customRangePanel').classList.remove('d-none');
            } else {
                applyDashboardFilter();
            }
        });

        document.getElementById('dash-compare-select').addEventListener('change', applyDashboardFilter);
    });

    function applyDashboardFilter() {
        const form = document.getElementById('dash-filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        const cover = document.getElementById('dashLoadingCover');
        if (cover) { cover.classList.remove('d-none'); cover.classList.add('d-flex'); }

        fetch("{{ route('admin.dashboard') }}?" + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('activePeriodLabel').innerText = data.periodLabel;
                document.getElementById('comparePeriodLabel').innerText = data.compareLabel;
                document.getElementById('chartPeriodBadge').innerText = data.periodLabel;
                document.getElementById('gatewayAuditPeriod').innerText = data.periodLabel;

                document.getElementById('val-period-sales').innerText = '৳' + data.periodSales;
                document.getElementById('val-prev-sales').innerText = data.prevSales;
                document.getElementById('val-period-orders').innerText = data.periodTotalOrders;
                document.getElementById('val-prev-orders').innerText = data.prevTotalOrders;
                document.getElementById('val-gross-profit').innerText = '৳' + data.estimatedGrossProfit;
                document.getElementById('val-profit-margin').innerText = 'Margin: ' + data.grossProfitMargin + '%';
                document.getElementById('val-cogs').innerText = data.estimatedCogs;
                document.getElementById('val-new-users').innerText = data.newUsersCount;

                updateGrowthBadge('kpi-growth-sales', data.salesGrowth);
                updateGrowthBadge('kpi-growth-orders', data.ordersGrowth);
                updateGrowthBadge('kpi-growth-users', data.newUsersGrowth);

                document.getElementById('val-online-sales').innerText = data.onlineSales;
                document.getElementById('val-online-count').innerText = data.onlineOrdersCount;
                document.getElementById('val-pos-sales').innerText = data.posSales;
                document.getElementById('val-pos-count').innerText = data.posOrdersCount;
                document.getElementById('val-total-due').innerText = data.totalDueAmount;
                document.getElementById('val-total-paid').innerText = data.totalPaidAmount;

                if (data.bestSellingProduct) {
                    document.getElementById('spotlight-product-title').innerText = data.bestSellingProduct.product_title;
                    const prodSku = data.bestSellingProduct.sku || data.bestSellingProduct.product_code || 'N/A';
                    document.getElementById('spotlight-product-code').innerText = 'SKU: ' + prodSku;
                    document.getElementById('spotlight-product-qty').innerText = data.bestSellingProduct.total_qty + ' pcs';
                    document.getElementById('spotlight-product-orders').innerText = data.bestSellingProduct.times_sold + ' orders';
                    document.getElementById('spotlight-product-rev').innerText = Number(data.bestSellingProduct.total_revenue).toLocaleString();
                } else {
                    document.getElementById('spotlight-product-title').innerText = 'No sales recorded in this period';
                    document.getElementById('spotlight-product-code').innerText = 'SKU: N/A';
                    document.getElementById('spotlight-product-qty').innerText = '0 pcs';
                }

                if (data.bestCustomer) {
                    const uidTag = data.bestCustomer.user_id 
                        ? `<span class="badge bg-light text-primary border font-monospace ms-1" style="font-size: 9px;">UID: #${data.bestCustomer.user_id}</span>`
                        : `<span class="badge bg-light text-muted border ms-1" style="font-size: 9px;">Guest</span>`;
                    document.getElementById('spotlight-customer-name').innerHTML = data.bestCustomer.customer_name + ' ' + uidTag;
                    document.getElementById('spotlight-customer-phone').innerText = data.bestCustomer.customer_phone || 'N/A';
                    document.getElementById('spotlight-customer-orders').innerText = data.bestCustomer.total_orders_placed + ' orders';
                    document.getElementById('spotlight-customer-spent').innerText = Number(data.bestCustomer.total_spent).toLocaleString();
                    document.getElementById('spotlight-customer-paid').innerText = Number(data.bestCustomer.total_paid).toLocaleString();
                } else {
                    document.getElementById('spotlight-customer-name').innerText = 'No customer activity in this period';
                    document.getElementById('spotlight-customer-spent').innerText = '0';
                }

                if (myChartInstance && data.chartLabels) {
                    myChartInstance.data.labels = data.chartLabels;
                    myChartInstance.data.datasets[0].data = data.chartRevenues;
                    myChartInstance.data.datasets[1].data = data.chartCounts;
                    myChartInstance.update('none');
                }

                if (data.ledgerHtml) {
                    document.getElementById('product-ledger-tbody').innerHTML = data.ledgerHtml;
                }
                if (data.customerHtml) {
                    document.getElementById('customer-ledger-tbody').innerHTML = data.customerHtml;
                }

                if (data.paymentMethodHtml) {
                    document.getElementById('payment-gateway-tbody').innerHTML = data.paymentMethodHtml;
                }

                if (data.orderStatusCounts) {
                    document.getElementById('stat-count-pending').innerText = data.orderStatusCounts.pending || 0;
                    document.getElementById('stat-count-processing').innerText = data.orderStatusCounts.processing || 0;
                    document.getElementById('stat-count-completed').innerText = data.orderStatusCounts.completed || 0;
                    document.getElementById('stat-count-cancelled').innerText = data.orderStatusCounts.cancelled || 0;
                    document.getElementById('stat-count-returned').innerText = data.orderStatusCounts.returned || 0;
                }
            }
        })
        .catch(err => console.error('Dashboard AJAX error:', err))
        .finally(() => {
            if (cover) { cover.classList.add('d-none'); cover.classList.remove('d-flex'); }
        });
    }

    function updateGrowthBadge(elementId, growth) {
        const el = document.getElementById(elementId);
        if (!el) return;
        const isPositive = growth >= 0;
        el.className = 'growth-badge bn-digit ' + (isPositive ? 'bg-success text-white' : 'bg-danger text-white');
        el.innerHTML = `<i class="fas ${isPositive ? 'fa-arrow-up' : 'fa-arrow-down'}"></i> ${Math.abs(growth)}%`;
    }

    function exportDashboardAudit(type) {
        const form = document.getElementById('dash-filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.append('export', type);
        window.open("{{ route('admin.dashboard') }}?" + params.toString(), '_blank');
    }

    // 1. Instant full dashboard PDF export (Auto-expands all collapses before capture)
    function downloadDashboardInstantPdf() {
        const btn = document.getElementById('btn-dash-instant-pdf');
        const btnText = document.getElementById('dash-pdf-btn-text');
        const origText = btnText.innerText;

        btn.disabled = true;
        btnText.innerText = 'Generating PDF...';

        const rootEl = document.getElementById('dashboard-analytics-root');
        rootEl.classList.add('pdf-render-active');

        // Automatically expand all collapsed tables for complete PDF capture
        const collapses = rootEl.querySelectorAll('.collapse');
        const previousStates = [];
        collapses.forEach((col, idx) => {
            previousStates[idx] = col.classList.contains('show');
            col.classList.add('show');
        });

        const chartCanvas = document.getElementById('dailyOrdersChart');
        let tempChartImg = null;
        if (chartCanvas && myChartInstance) {
            try {
                tempChartImg = document.createElement('img');
                tempChartImg.src = myChartInstance.toBase64Image('image/png', 1);
                tempChartImg.style.width = '100%';
                tempChartImg.style.height = (chartCanvas.offsetHeight || 280) + 'px';
                tempChartImg.style.objectFit = 'contain';
                chartCanvas.parentNode.insertBefore(tempChartImg, chartCanvas);
                chartCanvas.style.display = 'none';
            } catch (e) {
                console.warn('Chart to base64 fallback:', e);
            }
        }

        const opt = {
            margin:       [5, 5, 5, 5],
            filename:     'Dashboard_Analytics_Report_' + (new Date().toISOString().slice(0, 10)) + '.pdf',
            image:        { type: 'jpeg', quality: 0.95 },
            html2canvas:  { 
                scale: 1.2, 
                useCORS: true, 
                allowTaint: true, 
                logging: false,
                scrollX: 0,
                scrollY: 0
            },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' },
            pagebreak:    { mode: ['css', 'legacy'], avoid: ['tr', '.metric-soft-card', '.spotlight-card'] }
        };

        html2pdf().set(opt).from(rootEl).save().then(() => {
            cleanupDashboardPdf();
        }).catch(err => {
            console.error('PDF error:', err);
            cleanupDashboardPdf();
            showDashboardAlert('Failed to generate PDF report. Please try again.');
        });

        function cleanupDashboardPdf() {
            btn.disabled = false;
            btnText.innerText = origText;
            rootEl.classList.remove('pdf-render-active');
            
            // Restore collapse states
            collapses.forEach((col, idx) => {
                if (!previousStates[idx]) {
                    col.classList.remove('show');
                }
            });

            if (tempChartImg && chartCanvas) {
                tempChartImg.remove();
                chartCanvas.style.display = 'block';
            }
        }
    }

    // 2. Product-wise sales ledger instant PDF export
    function downloadProductLedgerInstantPdf() {
        const btn = document.getElementById('btn-export-ledger-pdf');
        const btnText = document.getElementById('ledger-pdf-btn-text');
        const origText = btnText.innerText;

        btn.disabled = true;
        btnText.innerText = 'Generating...';

        const widgetBox = document.getElementById('product-ledger-widget-box');
        widgetBox.classList.add('pdf-render-active');

        const colEl = document.getElementById('collapseProductLedger');
        const wasOpen = colEl.classList.contains('show');
        colEl.classList.add('show');

        const opt = {
            margin:       [5, 5, 5, 5],
            filename:     'Product_Sales_Ledger_' + (new Date().toISOString().slice(0, 10)) + '.pdf',
            image:        { type: 'jpeg', quality: 0.95 },
            html2canvas:  { 
                scale: 1.3, 
                useCORS: true, 
                allowTaint: true, 
                logging: false,
                scrollX: 0,
                scrollY: 0
            },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' },
            pagebreak:    { mode: ['css', 'legacy'], avoid: ['tr'] }
        };

        html2pdf().set(opt).from(widgetBox).save().then(() => {
            btn.disabled = false;
            btnText.innerText = origText;
            widgetBox.classList.remove('pdf-render-active');
            if (!wasOpen) colEl.classList.remove('show');
        }).catch(err => {
            console.error('Ledger PDF error:', err);
            btn.disabled = false;
            btnText.innerText = origText;
            widgetBox.classList.remove('pdf-render-active');
            if (!wasOpen) colEl.classList.remove('show');
            showDashboardAlert('Failed to generate product sales ledger PDF.');
        });
    }
</script>
@endsection