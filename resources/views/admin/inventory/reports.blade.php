@extends('layouts.admin')

@section('title', 'Date-wise Inventory & COGS Profit Reports - ' . config('app.name', 'Shop Now BD'))

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #reportRoot .btn,
    #reportRoot .btn-sm,
    #reportRoot button,
    #reportRoot .form-control,
    #reportRoot .form-select,
    #reportRoot .badge,
    #reportRoot .alert,
    #reportRoot .input-group-text,
    #reportRoot .card,
    #reportRoot .metric-card,
    #reportRoot .filter-toolbar-card,
    #reportRoot .metric-icon-box,
    #reportRoot .page-link,
    #reportRoot .page-item span,
    #reportRoot .collapse-toggle-btn,
    .report-modal-scope .modal-content,
    .report-modal-scope .btn {
        border-radius: 5px !important;
    }

    #reportRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #reportRoot .input-group > .form-control,
    #reportRoot .input-group > .form-select {
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

    /* Original Enterprise Surface Cards */
    #reportRoot .card,
    #reportRoot .metric-card,
    #reportRoot .filter-toolbar-card {
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }
    .report-card-header {
        padding: 10px 14px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
    }

    /* Executive Timeline Metric Cards - Zero Overflow Architecture */
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

    .metric-card-purple { background: linear-gradient(180deg, #ffffff 0%, #faf5ff 100%); border-color: #e9d5ff; }
    .metric-card-blue   { background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%); border-color: #bae6fd; }
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-color: #bbf7d0; }
    .metric-card-red    { background: linear-gradient(180deg, #ffffff 0%, #fef2f2 100%); border-color: #fecaca; }
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

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .report-table {
        width: 100%;
        margin-bottom: 0;
    }
    .report-table thead th {
        font-size: 10px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 6px;
        white-space: nowrap;
    }
    .report-table tbody td {
        padding: 8px 6px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        font-size: 11px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .report-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .report-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .report-table th,
        .report-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 11 Columns Proportional Distribution Summing to Exactly 100% */
        .col-rp-id     { width: 4% !important; }
        .col-rp-prod   { width: 22% !important; }
        .col-rp-sku    { width: 8% !important; }
        .col-rp-inqty  { width: 8% !important; }
        .col-rp-inval  { width: 9% !important; }
        .col-rp-outqty { width: 8% !important; }
        .col-rp-sqty   { width: 8% !important; }
        .col-rp-rev    { width: 10% !important; }
        .col-rp-cogs   { width: 9% !important; }
        .col-rp-profit { width: 9% !important; }
        .col-rp-action { width: 5% !important; }
    }

    /* Form Controls */
    #reportRoot .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #reportRoot .form-control,
    #reportRoot .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #reportRoot .form-control:focus,
    #reportRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Bulletproof Responsive Pagination */
    #report-pagination-container nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }
    #report-pagination-container nav > div.d-sm-none,
    #report-pagination-container nav > .d-sm-none {
        display: none !important;
    }
    #report-pagination-container nav > div.d-none,
    #report-pagination-container nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    #report-pagination-container nav p,
    #report-pagination-container nav .small.text-muted,
    #report-pagination-container nav div:has(> p),
    #report-pagination-container nav > div.d-none > div:first-child,
    #report-pagination-container nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }
    #report-pagination-container .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 3px !important;
        padding: 0 !important;
        list-style: none !important;
    }
    #report-pagination-container .page-item {
        display: inline-flex !important;
    }
    #report-pagination-container .page-item .page-link,
    #report-pagination-container .page-item span {
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
    #report-pagination-container .page-item .page-link:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }
    #report-pagination-container .page-item.active .page-link,
    #report-pagination-container .page-item.active span {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25) !important;
    }
    #report-pagination-container .page-item.disabled .page-link,
    #report-pagination-container .page-item.disabled span {
        opacity: 0.45 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }
    #report-pagination-container nav svg {
        width: 10px !important;
        height: 10px !important;
    }

    /* Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .report-header h5 {
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
        .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .report-table {
            min-width: 860px !important;
            table-layout: auto !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="reportRoot">
    <!-- AJAX Loading Spinner Overlay -->
    <div id="reportLoadingCover" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none justify-content-center align-items-center" style="z-index: 50; border-radius: 5px;">
        <div class="spinner-border text-primary" style="width: 2.2rem; height: 2.2rem;" role="status"></div>
    </div>

    <!-- 1. Header & Actions (Original Clean Light Theme) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 report-header">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-file-invoice-dollar text-primary"></i> <span>Inventory Valuation, COGS &amp; Profit Reports</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Audited timeline showing Opening Stock, Inbound consignments, Outbound write-offs, COGS, and Closing Balance.
            </small>
        </div>
        <div class="header-actions-wrap d-flex flex-wrap gap-2">
            <a href="{{ route('admin.inventory.balance') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-scale-balanced me-1"></i> <span>Stock Balance</span>
            </a>
            <a href="{{ route('admin.inventory.movements') }}" class="btn btn-sm btn-outline-primary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-clipboard-list me-1"></i> <span>Full Ledger</span>
            </a>
            <button type="button" class="btn btn-sm btn-outline-success btn-compact shadow-xs fw-bold" onclick="exportReportCsv()">
                <i class="fas fa-file-excel text-success me-1"></i> <span>Export (CSV)</span>
            </button>
        </div>
    </div>

    <!-- 2. Date Filter Toolbar (Collapsible) -->
    <div class="filter-toolbar-card overflow-hidden">
        <div class="report-card-header d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                <i class="fas fa-calendar-days text-primary me-1"></i> Report Period &amp; Date Parameters
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseReportFilter" aria-expanded="true" aria-controls="collapseReportFilter" title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseReportFilter">
            <div class="p-3">
                <form id="report-filter-form" onsubmit="return false;">
                    <div class="row g-2 align-items-end">
                        <!-- Date Presets -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label" for="report-period-select">
                                <i class="fas fa-calendar-check me-1 text-primary"></i> Period Preset
                            </label>
                            <select name="period" id="report-period-select" class="form-select font-monospace shadow-none" onchange="handlePeriodPresetChange()">
                                <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                                <option value="yesterday" {{ $period === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                                <option value="this_week" {{ $period === 'this_week' ? 'selected' : '' }}>This Week</option>
                                <option value="last_week" {{ $period === 'last_week' ? 'selected' : '' }}>Last Week</option>
                                <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>This Month</option>
                                <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>Last Month</option>
                                <option value="this_year" {{ $period === 'this_year' ? 'selected' : '' }}>This Year</option>
                                <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                            </select>
                        </div>

                        <!-- Start Date -->
                        <div class="col-6 col-sm-6 col-md-3" id="wrapper-start-date">
                            <label class="form-label" for="report-start-date">Start Date</label>
                            <input type="date" name="start_date" id="report-start-date" class="form-control font-monospace bn-digit shadow-none" value="{{ $startDate }}">
                        </div>

                        <!-- End Date -->
                        <div class="col-6 col-sm-6 col-md-3" id="wrapper-end-date">
                            <label class="form-label" for="report-end-date">End Date</label>
                            <input type="date" name="end_date" id="report-end-date" class="form-control font-monospace bn-digit shadow-none" value="{{ $endDate }}">
                        </div>

                        <!-- Filter Button -->
                        <div class="col-12 col-md-3">
                            <button type="button" class="btn btn-dark btn-compact w-100 fw-bold shadow-xs" onclick="applyReportFilter()">
                                <i class="fas fa-filter me-1" style="font-size: 10px;"></i> <span>Generate Report</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 3. Executive Timeline & Ledger Balance Summary Cards (Collapsible) -->
    <div class="card border-0 shadow-sm mb-3" style="border: 1px solid #e2e8f0; background: #ffffff;">
        <div class="report-card-header d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                <i class="fas fa-chart-line text-primary me-1"></i> Executive Timeline Balance &amp; COGS Summary
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseReportTimeline" aria-expanded="true" aria-controls="collapseReportTimeline" title="Toggle Timeline">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseReportTimeline">
            <div class="p-3">
                <div class="row g-2">
                    <!-- 1. Opening Balance -->
                    <div class="col-6 col-md-4 col-xl">
                        <div class="card metric-card metric-card-blue h-100">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="text-muted stat-label-title text-truncate">Opening Stock</span>
                                <div class="metric-icon-box bg-primary text-white shadow-xs">
                                    <i class="fas fa-box-open"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h5 class="fw-bold my-0 text-primary bn-digit font-monospace stat-val-num" id="val-opening-qty">
                                    {{ number_format($ledger['opening_qty']) }} <span style="font-size: 11px;">Units</span>
                                </h5>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text bn-digit">
                                    Valuation: <strong class="text-dark" id="val-opening-val">৳ {{ number_format($ledger['opening_value'], 2) }}</strong>
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Stock In Additions -->
                    <div class="col-6 col-md-4 col-xl">
                        <div class="card metric-card metric-card-green h-100">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="text-muted stat-label-title text-truncate">Inbound (+Stock In)</span>
                                <div class="metric-icon-box bg-success text-white shadow-xs">
                                    <i class="fas fa-arrow-down-long"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h5 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num" id="val-in-qty">
                                    +{{ number_format($ledger['stock_in_qty']) }} <span style="font-size: 11px;">Units</span>
                                </h5>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text bn-digit">
                                    Cost: <strong class="text-dark" id="val-in-val">৳ {{ number_format($ledger['stock_in_value'], 2) }}</strong>
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Stock Out Deductions -->
                    <div class="col-6 col-md-4 col-xl">
                        <div class="card metric-card metric-card-red h-100">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="text-muted stat-label-title text-truncate">Outbound (-Stock Out)</span>
                                <div class="metric-icon-box bg-danger text-white shadow-xs">
                                    <i class="fas fa-arrow-up-long"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h5 class="fw-bold my-0 text-danger bn-digit font-monospace stat-val-num" id="val-out-qty">
                                    -{{ number_format($ledger['stock_out_qty']) }} <span style="font-size: 11px;">Units</span>
                                </h5>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text bn-digit">
                                    Cost: <strong class="text-dark" id="val-out-val">৳ {{ number_format($ledger['stock_out_value'], 2) }}</strong>
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Sales & Gross Profit -->
                    <div class="col-6 col-md-6 col-xl">
                        <div class="card metric-card metric-card-amber h-100">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="text-muted stat-label-title text-truncate">Gross Profit</span>
                                <div class="metric-icon-box text-dark shadow-xs" style="background-color: #f59e0b;">
                                    <i class="fas fa-coins"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h5 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num" id="val-gross-profit">
                                    ৳ {{ number_format($ledger['gross_profit'], 2) }}
                                </h5>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text bn-digit">
                                    Rev: <span id="val-sales-rev">৳{{ number_format($ledger['sales_revenue'], 0) }}</span> | COGS: <span id="val-sales-cogs">৳{{ number_format($ledger['sales_cogs'], 0) }}</span>
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Closing Balance -->
                    <div class="col-12 col-md-6 col-xl">
                        <div class="card metric-card metric-card-purple h-100">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="text-muted stat-label-title text-truncate">Closing Stock</span>
                                <div class="metric-icon-box text-white shadow-xs" style="background-color: #7c3aed;">
                                    <i class="fas fa-vault"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h5 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num" id="val-closing-qty">
                                    {{ number_format($ledger['closing_qty']) }} <span style="font-size: 11px;">Units</span>
                                </h5>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text bn-digit">
                                    Valuation: <strong class="text-dark" id="val-closing-val">৳ {{ number_format($ledger['closing_value'], 2) }}</strong>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Itemized Product Activity Table (Collapsible) -->
    <div class="card p-0 mb-3 overflow-hidden shadow-xs">
        <div class="p-2 px-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 bg-white">
            <div>
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13px;">
                    <i class="fas fa-table text-primary"></i> <span>Product-wise Activity &amp; Profit Breakdown</span>
                </h6>
                <small class="text-muted" style="font-size: 10.5px;" id="report-period-caption">
                    Period: <strong class="text-dark">{{ $periodLabel }}</strong>
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-muted border font-monospace bn-digit px-2 py-1" style="font-size: 10px;">
                    {{ $reportItems->total() }} Line Items
                </span>
                <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseReportTable" aria-expanded="true" aria-controls="collapseReportTable" title="Toggle Table">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseReportTable">
            <div class="table-responsive pc-fit-container">
                <table class="table align-middle mb-0 report-table">
                    <thead>
                        <tr>
                            <th class="bn-digit ps-3 col-rp-id">ID</th>
                            <th class="col-rp-prod">Product &amp; Attributes</th>
                            <th class="col-rp-sku">SKU</th>
                            <th class="text-center text-success col-rp-inqty">Stock In (Qty)</th>
                            <th class="text-end text-success col-rp-inval">Stock In Val</th>
                            <th class="text-center text-danger col-rp-outqty">Stock Out (Qty)</th>
                            <th class="text-center text-primary col-rp-sqty">Sales Qty</th>
                            <th class="text-end text-dark col-rp-rev">Revenue</th>
                            <th class="text-end text-secondary col-rp-cogs">COGS</th>
                            <th class="text-end text-success col-rp-profit">Gross Profit</th>
                            <th class="text-end pe-3 col-rp-action">Ledger</th>
                        </tr>
                    </thead>
                    <tbody id="report-tbody">
                        @include('admin.inventory.partials.report_rows', ['items' => $reportItems])
                    </tbody>
                </table>
            </div>

            <!-- Bulletproof Responsive Pagination Footer -->
            <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50" id="report-pagination-container">
                <div class="small text-muted font-monospace bn-digit" style="font-size: 11px;">
                    Showing <strong>{{ $reportItems->firstItem() ?? 0 }}</strong> to <strong>{{ $reportItems->lastItem() ?? 0 }}</strong> of <strong>{{$reportItems->total() }}</strong> records
                </div>
                <div>
                    {{ $reportItems->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Lightbox Zoom Modal for Product Thumbnails in Report Rows -->
<div class="modal fade report-modal-scope" id="reportImageZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;" id="reportZoomModalTitle">
                    <i class="fas fa-image text-primary me-1"></i> Product Preview
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="reportZoomModalImgTag" src="" alt="Product Photo" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<script>
    function zoomReportImage(url, title = 'Product Image') {
        const titleEl = document.getElementById('reportZoomModalTitle');
        const imgTag  = document.getElementById('reportZoomModalImgTag');
        if (url && imgTag) {
            if (titleEl) titleEl.innerText = title;
            imgTag.src = url;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('reportImageZoomModal')).show();
        }
    }

    function handlePeriodPresetChange() {
        const period = document.getElementById('report-period-select').value;
        const now = new Date();

        function formatDate(d) {
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        if (period === 'today') {
            document.getElementById('report-start-date').value = formatDate(now);
            document.getElementById('report-end-date').value = formatDate(now);
        } else if (period === 'yesterday') {
            const y = new Date(now);
            y.setDate(y.getDate() - 1);
            document.getElementById('report-start-date').value = formatDate(y);
            document.getElementById('report-end-date').value = formatDate(y);
        } else if (period === 'this_week') {
            const first = new Date(now.setDate(now.getDate() - now.getDay()));
            const last = new Date(now.setDate(now.getDate() - now.getDay() + 6));
            document.getElementById('report-start-date').value = formatDate(first);
            document.getElementById('report-end-date').value = formatDate(last);
        } else if (period === 'this_month') {
            const first = new Date(now.getFullYear(), now.getMonth(), 1);
            const last = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            document.getElementById('report-start-date').value = formatDate(first);
            document.getElementById('report-end-date').value = formatDate(last);
        } else if (period === 'last_month') {
            const first = new Date(now.getFullYear(), now.getMonth() - 1, 1);
            const last = new Date(now.getFullYear(), now.getMonth(), 0);
            document.getElementById('report-start-date').value = formatDate(first);
            document.getElementById('report-end-date').value = formatDate(last);
        } else if (period === 'this_year') {
            const first = new Date(now.getFullYear(), 0, 1);
            const last = new Date(now.getFullYear(), 11, 31);
            document.getElementById('report-start-date').value = formatDate(first);
            document.getElementById('report-end-date').value = formatDate(last);
        }

        applyReportFilter();
    }

    function applyReportFilter(targetUrl = null) {
        const form = document.getElementById('report-filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        const cover = document.getElementById('reportLoadingCover');
        if (cover) { cover.classList.remove('d-none'); cover.classList.add('d-flex'); }

        const url = targetUrl || ("{{ route('admin.inventory.reports') }}?" + params.toString());

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('report-tbody').innerHTML = data.table_html;
                document.getElementById('report-pagination-container').innerHTML = `
                    <div class="small text-muted font-monospace bn-digit" style="font-size: 11px;">Updated Report Results (${data.total_count ?? 0} records)</div>
                    <div>${data.pagination}</div>
                `;

                if (data.ledger) {
                    const l = data.ledger;
                    document.getElementById('val-opening-qty').innerHTML = `${Number(l.opening_qty).toLocaleString()} <span style="font-size: 11px;">Units</span>`;
                    document.getElementById('val-opening-val').innerText = `৳ ${Number(l.opening_value).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                    
                    document.getElementById('val-in-qty').innerHTML = `+${Number(l.stock_in_qty).toLocaleString()} <span style="font-size: 11px;">Units</span>`;
                    document.getElementById('val-in-val').innerText = `৳ ${Number(l.stock_in_value).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

                    document.getElementById('val-out-qty').innerHTML = `-${Number(l.stock_out_qty).toLocaleString()} <span style="font-size: 11px;">Units</span>`;
                    document.getElementById('val-out-val').innerText = `৳ ${Number(l.stock_out_value).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

                    document.getElementById('val-gross-profit').innerText = `৳ ${Number(l.gross_profit).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                    document.getElementById('val-sales-rev').innerText = `৳${Number(l.sales_revenue).toLocaleString(undefined, {maximumFractionDigits: 0})}`;
                    document.getElementById('val-sales-cogs').innerText = `৳${Number(l.sales_cogs).toLocaleString(undefined, {maximumFractionDigits: 0})}`;

                    document.getElementById('val-closing-qty').innerHTML = `${Number(l.closing_qty).toLocaleString()} <span style="font-size: 11px;">Units</span>`;
                    document.getElementById('val-closing-val').innerText = `৳ ${Number(l.closing_value).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                }
            }
        })
        .catch(err => console.error('Report filter error:', err))
        .finally(() => {
            if (cover) { cover.classList.add('d-none'); cover.classList.remove('d-flex'); }
        });
    }

    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#report-pagination-container a');
        if (pageLink) {
            e.preventDefault();
            const href = pageLink.getAttribute('href');
            if (href && href !== '#' && !href.startsWith('javascript:')) {
                applyReportFilter(href);
            }
        }
    });

    function exportReportCsv() {
        const form = document.getElementById('report-filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.append('export', 'csv');
        window.open("{{ route('admin.inventory.reports') }}?" + params.toString(), '_blank');
    }
</script>
@endsection