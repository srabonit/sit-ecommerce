@extends('layouts.admin')

@section('title', 'Profit & Loss Statement (Income Statement) - ' . config('app.name'))

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #pnl-report-root .btn,
    #pnl-report-root .btn-sm,
    #pnl-report-root button,
    #pnl-report-root .badge,
    #pnl-report-root .alert,
    #pnl-report-root .form-control,
    #pnl-report-root .form-select,
    #pnl-report-root .card,
    #pnl-report-root .report-card,
    #pnl-report-root .kpi-stat-card,
    #pnl-report-root .collapse-toggle-btn {
        border-radius: 5px !important;
    }

    #pnl-report-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #pnl-report-root .input-group > .form-control,
    #pnl-report-root .input-group > .form-select {
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
        font-size: 11.5px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    /* Original Theme Card Styling */
    .report-card {
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }
    .report-card-header {
        padding: 10px 14px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
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

    /* Original KPI Stat Cards */
    .kpi-stat-card {
        padding: 14px 16px;
        position: relative;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        height: 100%;
    }
    .kpi-stat-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3.5px;
    }
    .kpi-sales::before   { background-color: #0284c7; }
    .kpi-gross::before   { background-color: #f59e0b; }
    .kpi-expense::before { background-color: #ef4444; }
    .kpi-net::before     { background-color: #10b981; }

    /* Statement Tables */
    .statement-table {
        width: 100%;
        margin-bottom: 0;
    }
    .statement-table thead th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 12px;
        white-space: nowrap;
    }
    .statement-table tbody td {
        padding: 8px 12px;
        vertical-align: middle;
        font-size: 12px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }
    .statement-section-header td {
        background-color: #f8fafc;
        font-weight: 700;
        font-size: 11px;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 6px 12px;
    }

    /* Form Controls */
    .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #pnl-report-root .form-control,
    #pnl-report-root .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease;
    }
    #pnl-report-root .form-control:focus,
    #pnl-report-root .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Print Styles */
    @media print {
        @page { size: A4 portrait; margin: 8mm; }
        body { background: #ffffff !important; color: #000000 !important; }
        #pnl-report-root .no-print, .admin-dark-sidebar, .admin-header, .main-footer { display: none !important; }
        #pnl-report-root { padding: 0 !important; margin: 0 !important; width: 100% !important; }
        .report-card { border: 1px solid #000000 !important; box-shadow: none !important; page-break-inside: avoid; margin-bottom: 10px !important; }
    }

    /* Mobile Responsive Optimizations (< 768px) */
    @media (max-width: 767.98px) {
        .header-top-wrap {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 8px !important;
        }
        .top-action-btns {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .top-action-btns .btn {
            width: 100%;
            justify-content: center;
        }
        .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .statement-table {
            min-width: 480px !important;
            table-layout: auto !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="pnl-report-root">

    <!-- 1. Header (Original Clean Light Theme) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 header-top-wrap no-print">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-file-invoice-dollar text-primary"></i>
                <span>Profit &amp; Loss Statement</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Comprehensive Gross Margin, COGS breakdown, Operating Expenses &amp; Net Operating Income
            </small>
        </div>
        <div class="top-action-btns d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-dark btn-compact shadow-xs fw-semibold" onclick="window.print()">
                <i class="fas fa-print me-1"></i> <span>Print A4</span>
            </button>
            <a href="{{ route('admin.report.export_pnl') }}?{{ http_build_query(request()->all()) }}" class="btn btn-sm btn-outline-success btn-compact shadow-xs">
                <i class="fas fa-file-csv me-1"></i> <span>Export CSV</span>
            </a>
            <a href="{{ route('admin.report.balance_sheet') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-scale-balanced me-1"></i> <span>Balance Sheet</span>
            </a>
            <a href="{{ route('admin.report.cash_flow') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-money-bill-trend-up me-1 text-primary"></i> <span>Cash Flow</span>
            </a>
            <a href="{{ route('admin.report.cogs') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-boxes-stacked me-1 text-warning"></i> <span>COGS Analysis</span>
            </a>
        </div>
    </div>

    <!-- 2. Filter Toolbar (Clean & Symmetric) -->
    <div class="card border-0 shadow-sm report-card mb-3 p-3 no-print">
        <form action="{{ route('admin.report.profit_loss') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label">Reporting Period</label>
                <select name="date_filter" id="dateFilterSelect" class="form-select font-monospace" onchange="toggleCustomDates(this.value)">
                    <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ $dateFilter === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="this_week" {{ $dateFilter === 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ $dateFilter === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $dateFilter === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="this_year" {{ $dateFilter === 'this_year' ? 'selected' : '' }}>This Year</option>
                    <option value="custom" {{ $dateFilter === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                </select>
            </div>

            <div class="col-6 col-md-3 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="customStartCol">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control font-monospace bn-digit" value="{{ request('start_date', $start->format('Y-m-d')) }}">
            </div>

            <div class="col-6 col-md-3 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="customEndCol">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control font-monospace bn-digit" value="{{ request('end_date', $end->format('Y-m-d')) }}">
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label">Sales Channel</label>
                <select name="channel" class="form-select font-monospace">
                    <option value="all">All Channels</option>
                    <option value="web" {{ request('channel') === 'web' ? 'selected' : '' }}>Online Store</option>
                    <option value="manual" {{ request('channel') === 'manual' ? 'selected' : '' }}>Manual / Social</option>
                    <option value="pos" {{ request('channel') === 'pos' ? 'selected' : '' }}>POS Outlet</option>
                </select>
            </div>

            <div class="col-12 col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-dark btn-compact shadow-xs fw-bold px-3">
                    <i class="fas fa-filter me-1"></i> Apply Filter
                </button>
                @if(request()->anyFilled(['date_filter', 'channel', 'start_date', 'end_date']))
                    <a href="{{ route('admin.report.profit_loss') }}" class="btn btn-light border btn-compact shadow-xs">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- 3. Active Statement Period Header -->
    <div class="alert alert-light border py-2 px-3 small d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 shadow-xs">
        <div>
            <span class="text-muted">Audit Scope:</span> 
            <strong class="text-dark ms-1">{{ $summary['period_label'] ?? 'Current Period' }}</strong>
        </div>
        <div>
            <span class="text-muted">Delivered Orders Reconciled:</span> 
            <strong class="font-monospace bn-digit text-primary ms-1">{{ $summary['delivered_orders'] ?? 0 }} Orders</strong>
        </div>
    </div>

    <!-- 4. KPI Metric Summary Cards (Collapsible) -->
    <div class="report-card mb-3">
        <div class="report-card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-chart-pie text-primary"></i>
                <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">Profitability Performance Metrics</h6>
            </div>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapsePnlKpis" aria-expanded="true" aria-controls="collapsePnlKpis" title="Toggle Metrics">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapsePnlKpis">
            <div class="p-3 bg-light bg-opacity-25">
                <div class="row g-2">
                    <!-- Net Sales -->
                    <div class="col-6 col-lg-3">
                        <div class="kpi-stat-card kpi-sales shadow-xs">
                            <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Net Sales Revenue</span>
                            <h5 class="fw-bold text-dark mb-0 font-monospace bn-digit">৳ {{ number_format((float)($summary['net_sales'] ?? 0), 2) }}</h5>
                            <small class="text-muted" style="font-size: 10px;">Delivered subtotals excl. discounts</small>
                        </div>
                    </div>

                    <!-- Gross Margin -->
                    <div class="col-6 col-lg-3">
                        <div class="kpi-stat-card kpi-gross shadow-xs">
                            <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Gross Product Margin</span>
                            <h5 class="fw-bold text-dark mb-0 font-monospace bn-digit">৳ {{ number_format((float)($summary['gross_profit'] ?? 0), 2) }}</h5>
                            <small class="text-warning fw-bold font-monospace" style="font-size: 10px;">Gross Margin: {{ $summary['gross_margin'] ?? 0 }}%</small>
                        </div>
                    </div>

                    <!-- Operating Expenses -->
                    <div class="col-6 col-lg-3">
                        <div class="kpi-stat-card kpi-expense shadow-xs">
                            <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Operating Overheads</span>
                            <h5 class="fw-bold text-danger mb-0 font-monospace bn-digit">৳ {{ number_format((float)($summary['operating_expenses'] ?? 0), 2) }}</h5>
                            <small class="text-muted" style="font-size: 10px;">Rent, ads, salary, bills &amp; others</small>
                        </div>
                    </div>

                    <!-- Net Operating Profit -->
                    <div class="col-6 col-lg-3">
                        <div class="kpi-stat-card kpi-net shadow-xs">
                            <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Net Profit (EBIT)</span>
                            <h5 class="fw-bold {{ ($summary['net_profit'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }} mb-0 font-monospace bn-digit">
                                ৳ {{ number_format((float)($summary['net_profit'] ?? 0), 2) }}
                            </h5>
                            <small class="{{ ($summary['net_profit'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }} fw-bold font-monospace" style="font-size: 10px;">
                                Net Margin: {{ $summary['net_margin'] ?? 0 }}%
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Main Content Grid -->
    <div class="row g-3">
        <!-- Left: Official P&L Financial Statement Table -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm report-card overflow-hidden">
                <div class="report-card-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                        <i class="fas fa-table-list text-primary"></i> <span>Income Statement (Statement of Profit &amp; Loss)</span>
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-secondary border font-monospace" style="font-size: 9.5px;">BDT (৳)</span>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseIncomeStatement" aria-expanded="true" aria-controls="collapseIncomeStatement" title="Toggle Table">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                </div>
                <div class="collapse show" id="collapseIncomeStatement">
                    <div class="card-body p-0">
                        <div class="table-responsive pc-fit-container">
                            <table class="table align-middle mb-0 statement-table">
                                <thead>
                                    <tr>
                                        <th>Account / Ledger Particulars</th>
                                        <th class="text-end" style="width: 140px;">Debit (৳)</th>
                                        <th class="text-end pe-3" style="width: 150px;">Credit / Net (৳)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- 1. REVENUE SECTION -->
                                    <tr class="statement-section-header">
                                        <td colspan="3"><i class="fas fa-coins me-1 text-primary"></i> Operating Revenue &amp; Product Sales</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-3">Gross Product Sales (Delivered Subtotals)</td>
                                        <td class="text-end text-muted font-monospace bn-digit">—</td>
                                        <td class="text-end pe-3 font-monospace bn-digit">৳ {{ number_format((float)($summary['gross_sales'] ?? 0), 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-3 text-danger">Less: Customer Discounts &amp; Coupons Granted</td>
                                        <td class="text-end text-danger font-monospace bn-digit">৳ {{ number_format((float)($summary['discounts'] ?? 0), 2) }}</td>
                                        <td class="text-end pe-3 text-muted font-monospace bn-digit">—</td>
                                    </tr>
                                    <tr class="bg-light fw-bold">
                                        <td class="ps-3 text-dark">Net Sales Revenue</td>
                                        <td class="text-end">—</td>
                                        <td class="text-end pe-3 text-primary font-monospace bn-digit">৳ {{ number_format((float)($summary['net_sales'] ?? 0), 2) }}</td>
                                    </tr>

                                    <!-- 2. COGS SECTION -->
                                    <tr class="statement-section-header">
                                        <td colspan="3"><i class="fas fa-dolly me-1 text-warning"></i> Cost of Goods Sold (COGS)</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-3 text-danger">Purchased Product Cost of Delivered Items</td>
                                        <td class="text-end text-danger font-monospace bn-digit">৳ {{ number_format((float)($summary['cogs'] ?? 0), 2) }}</td>
                                        <td class="text-end pe-3 text-muted font-monospace bn-digit">—</td>
                                    </tr>
                                    <tr class="bg-light fw-bold">
                                        <td class="ps-3 text-dark">Gross Product Profit (Margin: {{ $summary['gross_margin'] ?? 0 }}%)</td>
                                        <td class="text-end">—</td>
                                        <td class="text-end pe-3 text-dark font-monospace bn-digit">৳ {{ number_format((float)($summary['gross_profit'] ?? 0), 2) }}</td>
                                    </tr>

                                    <!-- 3. LOGISTICS SECTION -->
                                    <tr class="statement-section-header">
                                        <td colspan="3"><i class="fas fa-truck-fast me-1 text-info"></i> Logistics &amp; Courier Operations</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-3">Delivery Fees Collected from Customers</td>
                                        <td class="text-end text-muted font-monospace bn-digit">—</td>
                                        <td class="text-end pe-3 font-monospace bn-digit">৳ {{ number_format((float)($summary['shipping_collected'] ?? 0), 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-3 text-danger">Less: Actual Courier Delivery Charges Paid</td>
                                        <td class="text-end text-danger font-monospace bn-digit">৳ {{ number_format((float)($summary['courier_cost_spent'] ?? 0), 2) }}</td>
                                        <td class="text-end pe-3 text-muted font-monospace bn-digit">—</td>
                                    </tr>
                                    @if(($summary['courier_cod_fee'] ?? 0) > 0)
                                        <tr>
                                            <td class="ps-3 text-danger">Less: Courier COD Collection Commission (1%)</td>
                                            <td class="text-end text-danger font-monospace bn-digit">৳ {{ number_format((float)$summary['courier_cod_fee'], 2) }}</td>
                                            <td class="text-end pe-3 text-muted font-monospace bn-digit">—</td>
                                        </tr>
                                    @endif
                                    @if(($summary['courier_return_loss'] ?? 0) > 0)
                                        <tr>
                                            <td class="ps-3 text-danger">Less: Return Parcel Shipping Charges</td>
                                            <td class="text-end text-danger font-monospace bn-digit">৳ {{ number_format((float)$summary['courier_return_loss'], 2) }}</td>
                                            <td class="text-end pe-3 text-muted font-monospace bn-digit">—</td>
                                        </tr>
                                    @endif
                                    <tr class="bg-light">
                                        <td class="ps-3 text-secondary fw-semibold">Net Shipping Operation Balance</td>
                                        <td class="text-end">—</td>
                                        <td class="text-end pe-3 font-monospace bn-digit {{ ($summary['logistics_balance'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ ($summary['logistics_balance'] ?? 0) >= 0 ? '+' : '' }}৳ {{ number_format((float)($summary['logistics_balance'] ?? 0), 2) }}
                                        </td>
                                    </tr>

                                    <!-- 4. LOSSES & WRITE-OFFS -->
                                    @if(($summary['damage_loss'] ?? 0) > 0)
                                        <tr class="statement-section-header">
                                            <td colspan="3"><i class="fas fa-triangle-exclamation me-1 text-danger"></i> Returns &amp; Damage Write-Offs</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-3 text-danger">Damaged / Scrapped Return Write-Off Loss</td>
                                            <td class="text-end text-danger font-monospace bn-digit">৳ {{ number_format((float)$summary['damage_loss'], 2) }}</td>
                                            <td class="text-end pe-3 text-muted font-monospace bn-digit">—</td>
                                        </tr>
                                    @endif

                                    <!-- 5. OTHER BUSINESS INFLOWS & INCOME -->
                                    @if(($summary['other_income'] ?? 0) > 0 || (isset($incomeCategoryBreakdown) && $incomeCategoryBreakdown->isNotEmpty()))
                                        <tr class="statement-section-header">
                                            <td colspan="3"><i class="fas fa-hand-holding-dollar me-1 text-success"></i> Other Business Inflows &amp; Non-Operating Income</td>
                                        </tr>
                                        @if(isset($incomeCategoryBreakdown) && $incomeCategoryBreakdown->isNotEmpty())
                                            @foreach($incomeCategoryBreakdown as $catInc)
                                                <tr>
                                                    <td class="ps-3 text-success">Add: {{ $catInc->category_name }}</td>
                                                    <td class="text-end text-muted font-monospace bn-digit">—</td>
                                                    <td class="text-end pe-3 text-success font-monospace bn-digit">৳ {{ number_format((float)$catInc->total_amount, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td class="ps-3 text-success">Add: Total Miscellaneous Business Inflows</td>
                                                <td class="text-end text-muted font-monospace bn-digit">—</td>
                                                <td class="text-end pe-3 text-success font-monospace bn-digit">৳ {{ number_format((float)($summary['other_income'] ?? 0), 2) }}</td>
                                            </tr>
                                        @endif
                                        <tr class="bg-light">
                                            <td class="ps-3 text-secondary fw-semibold">Total Additional Inflows</td>
                                            <td class="text-end">—</td>
                                            <td class="text-end pe-3 text-success font-monospace bn-digit fw-bold">+৳ {{ number_format((float)($summary['other_income'] ?? 0), 2) }}</td>
                                        </tr>
                                    @endif

                                    <!-- 6. OPERATIONAL EXPENSES & OVERHEADS -->
                                    <tr class="statement-section-header">
                                        <td colspan="3"><i class="fas fa-receipt me-1 text-danger"></i> Operating Expenses &amp; Business Overheads</td>
                                    </tr>
                                    @if(isset($expenseCategoryBreakdown) && $expenseCategoryBreakdown->isNotEmpty())
                                        @foreach($expenseCategoryBreakdown as $catExp)
                                            <tr>
                                                <td class="ps-3 text-danger">Less: {{ $catExp->category_name }}</td>
                                                <td class="text-end text-danger font-monospace bn-digit">৳ {{ number_format((float)$catExp->total_amount, 2) }}</td>
                                                <td class="text-end pe-3 text-muted font-monospace bn-digit">—</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td class="ps-3 text-danger">Less: Total General Operating Expenses</td>
                                            <td class="text-end text-danger font-monospace bn-digit">৳ {{ number_format((float)($summary['operating_expenses'] ?? 0), 2) }}</td>
                                            <td class="text-end pe-3 text-muted font-monospace bn-digit">—</td>
                                        </tr>
                                    @endif
                                    <tr class="bg-light">
                                        <td class="ps-3 text-secondary fw-semibold">Total Operating Overheads</td>
                                        <td class="text-end text-danger font-monospace bn-digit fw-bold">৳ {{ number_format((float)($summary['operating_expenses'] ?? 0), 2) }}</td>
                                        <td class="text-end pe-3 text-muted font-monospace bn-digit">—</td>
                                    </tr>

                                    <!-- 7. FINAL NET BOTTOM-LINE PROFIT -->
                                    <tr class="bg-light border-top border-2 fw-bold" style="font-size: 12.5px;">
                                        <td class="py-2.5 ps-3">
                                            <span class="text-dark">NET BOTTOM-LINE PROFIT (EBIT)</span>
                                            <small class="d-block text-muted fw-normal" style="font-size: 10px;">Final retained profit after COGS, logistics, damage loss, other income, and operational overheads</small>
                                        </td>
                                        <td class="text-end py-2.5">—</td>
                                        <td class="text-end pe-3 py-2.5 font-monospace bn-digit {{ ($summary['net_profit'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }} fw-bold" style="font-size: 13.5px;">
                                            ৳ {{ number_format((float)($summary['net_profit'] ?? 0), 2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Channel Breakdown & Operational Cards -->
        <div class="col-12 col-lg-4">
            <!-- Channel Performance -->
            <div class="card border-0 shadow-sm report-card mb-3 p-3">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-2 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                    <i class="fas fa-network-wired text-info"></i> <span>Sales by Channel</span>
                </h6>
                <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                    <div>
                        <strong class="d-block text-dark small">Online Web Orders</strong>
                        <small class="text-muted font-monospace bn-digit" style="font-size: 10px;">{{ $channelStats['web']['count'] ?? 0 }} orders</small>
                    </div>
                    <span class="fw-bold text-primary font-monospace bn-digit" style="font-size: 12px;">৳ {{ number_format((float)($channelStats['web']['revenue'] ?? 0), 2) }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                    <div>
                        <strong class="d-block text-dark small">Facebook &amp; Phone (Manual)</strong>
                        <small class="text-muted font-monospace bn-digit" style="font-size: 10px;">{{ $channelStats['manual']['count'] ?? 0 }} orders</small>
                    </div>
                    <span class="fw-bold text-warning font-monospace bn-digit" style="font-size: 12px;">৳ {{ number_format((float)($channelStats['manual']['revenue'] ?? 0), 2) }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-1">
                    <div>
                        <strong class="d-block text-dark small">POS Outlets / Showroom</strong>
                        <small class="text-muted font-monospace bn-digit" style="font-size: 10px;">{{ $channelStats['pos']['count'] ?? 0 }} orders</small>
                    </div>
                    <span class="fw-bold text-success font-monospace bn-digit" style="font-size: 12px;">৳ {{ number_format((float)($channelStats['pos']['revenue'] ?? 0), 2) }}</span>
                </div>
            </div>

            <!-- Other Business Inflows Breakdown Card -->
            @if(($summary['other_income'] ?? 0) > 0 || (isset($incomeCategoryBreakdown) && $incomeCategoryBreakdown->isNotEmpty()))
                <div class="card border-0 shadow-sm report-card mb-3 p-3">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                            <i class="fas fa-hand-holding-dollar text-success"></i> <span>Other Inflows Breakdown</span>
                        </h6>
                        <a href="{{ url('/admin/accounts/income') }}" class="small text-success text-decoration-none fw-semibold" style="font-size: 11px;">View All</a>
                    </div>
                    @if(isset($incomeCategoryBreakdown) && $incomeCategoryBreakdown->isNotEmpty())
                        @foreach($incomeCategoryBreakdown as $incRow)
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                <span class="text-dark small">{{ $incRow->category_name }}</span>
                                <span class="fw-bold text-success font-monospace bn-digit" style="font-size: 12px;">+৳ {{ number_format((float)$incRow->total_amount, 2) }}</span>
                            </div>
                        @endforeach
                        <div class="d-flex justify-content-between align-items-center pt-2">
                            <strong class="text-dark small">Total Inflows:</strong>
                            <strong class="text-success font-monospace bn-digit" style="font-size: 12px;">+৳ {{ number_format((float)($summary['other_income'] ?? 0), 2) }}</strong>
                        </div>
                    @else
                        <p class="text-muted small mb-0 py-2 text-center">No additional inflows recorded for this period.</p>
                    @endif
                </div>
            @endif

            <!-- Operating Expenses Breakdown Card -->
            <div class="card border-0 shadow-sm report-card mb-3 p-3">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                        <i class="fas fa-receipt text-danger"></i> <span>Expense Breakdown</span>
                    </h6>
                    <a href="{{ url('/admin/accounts/expenses') }}" class="small text-primary text-decoration-none fw-semibold" style="font-size: 11px;">View All</a>
                </div>
                @if(isset($expenseCategoryBreakdown) && $expenseCategoryBreakdown->isNotEmpty())
                    @foreach($expenseCategoryBreakdown as $expRow)
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span class="text-dark small">{{ $expRow->category_name }}</span>
                            <span class="fw-bold text-danger font-monospace bn-digit" style="font-size: 12px;">৳ {{ number_format((float)$expRow->total_amount, 2) }}</span>
                        </div>
                    @endforeach
                    <div class="d-flex justify-content-between align-items-center pt-2">
                        <strong class="text-dark small">Total Overheads:</strong>
                        <strong class="text-danger font-monospace bn-digit" style="font-size: 12px;">৳ {{ number_format((float)($summary['operating_expenses'] ?? 0), 2) }}</strong>
                    </div>
                @else
                    <p class="text-muted small mb-0 py-2 text-center">No operating expenses recorded for this period.</p>
                @endif
            </div>

            <!-- Logistics Audit Summary -->
            <div class="card border-0 shadow-sm report-card p-3">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-2 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                    <i class="fas fa-boxes-packing text-primary"></i> <span>Shipping Operations Audit</span>
                </h6>
                <div class="d-flex justify-content-between py-1 small">
                    <span class="text-muted">Customer Delivery Collected:</span>
                    <strong class="font-monospace text-dark bn-digit">৳ {{ number_format((float)($summary['shipping_collected'] ?? 0), 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between py-1 small">
                    <span class="text-muted">Courier Dispatch Cost Paid:</span>
                    <strong class="font-monospace text-danger bn-digit">৳ {{ number_format((float)($summary['courier_cost_spent'] ?? 0), 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between py-1 small">
                    <span class="text-muted">Courier 1% COD Fee:</span>
                    <strong class="font-monospace text-danger bn-digit">৳ {{ number_format((float)($summary['courier_cod_fee'] ?? 0), 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between py-1 small border-top pt-1 mt-1">
                    <strong class="text-dark">Net Logistics Margin:</strong>
                    <strong class="font-monospace bn-digit {{ ($summary['logistics_balance'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                        ৳ {{ number_format((float)($summary['logistics_balance'] ?? 0), 2) }}
                    </strong>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. Top High-Margin Products Table (Collapsible) -->
    <div class="card border-0 shadow-sm report-card overflow-hidden mt-2">
        <div class="report-card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                <i class="fas fa-ranking-star text-warning"></i> <span>Top High-Margin Delivered Products in this Period</span>
            </h6>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseTopProducts" aria-expanded="true" aria-controls="collapseTopProducts" title="Toggle Products">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseTopProducts">
            <div class="card-body p-0">
                <div class="table-responsive pc-fit-container">
                    <table class="table align-middle mb-0 text-nowrap statement-table">
                        <thead>
                            <tr>
                                <th class="ps-3" style="width: 50px;">#</th>
                                <th>Product Description</th>
                                <th class="text-center" style="width: 100px;">Units Sold</th>
                                <th class="text-end" style="width: 130px;">Sales (৳)</th>
                                <th class="text-end" style="width: 130px;">COGS (৳)</th>
                                <th class="text-end" style="width: 130px;">Net Profit (৳)</th>
                                <th class="text-end pe-3" style="width: 100px;">Margin %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProfitableProducts as $idx => $prod)
                                <tr>
                                    <td class="ps-3 font-monospace text-muted" style="font-size: 10.5px;">{{ $idx + 1 }}</td>
                                    <td>
                                        <strong class="text-dark d-block text-truncate" style="max-width: 320px; font-size: 12px;" title="{{ $prod->title }}">
                                            {{ $prod->title }}
                                        </strong>
                                    </td>
                                    <td class="text-center font-monospace bn-digit" style="font-size: 11px;">&times; {{ $prod->total_qty }}</td>
                                    <td class="text-end font-monospace bn-digit" style="font-size: 11.5px;">৳ {{ number_format((float)$prod->total_revenue, 2) }}</td>
                                    <td class="text-end font-monospace bn-digit text-muted" style="font-size: 11.5px;">৳ {{ number_format((float)$prod->total_cogs, 2) }}</td>
                                    <td class="text-end font-monospace bn-digit text-success fw-bold" style="font-size: 11.5px;">৳ {{ number_format((float)$prod->profit, 2) }}</td>
                                    <td class="text-end pe-3">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-1 py-0" style="font-size: 9.5px;">
                                            {{ $prod->margin }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted small">No delivered products recorded in this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleCustomDates(val) {
    const startCol = document.getElementById('customStartCol');
    const endCol   = document.getElementById('customEndCol');
    if (val === 'custom') {
        startCol.classList.remove('d-none');
        endCol.classList.remove('d-none');
    } else {
        startCol.classList.add('d-none');
        endCol.classList.add('d-none');
    }
}
</script>
@endsection