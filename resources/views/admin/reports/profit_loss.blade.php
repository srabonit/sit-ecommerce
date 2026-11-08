
@extends('layouts.admin')

@section('title', 'Profit & Loss Statement - ' . config('app.name', 'Shop Now BD'))

@section('admin_content')
<style>
    /* =========================================================
       PROFIT & LOSS — KAIADMIN LITE CLASSIC / WHITE
    ========================================================= */
    #pnl-report-root {
        --pnl-primary: #1572e8;
        --pnl-primary-soft: #eaf2ff;
        --pnl-success: #31ce36;
        --pnl-warning: #ffad46;
        --pnl-danger: #f25961;
        --pnl-border: #e5e7eb;
        --pnl-muted: #6b7280;
        --pnl-text: #2a2f36;
        --pnl-heading: #1f2937;
        --pnl-bg: #f8fafc;
        color: var(--pnl-text);
        min-width: 0;
    }

    #pnl-report-root *,
    #pnl-report-root *::before,
    #pnl-report-root *::after {
        box-sizing: border-box;
    }

    /* Consistent 5px radius */
    #pnl-report-root .btn,
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

    #pnl-report-root .bn-digit,
    #pnl-report-root .font-monospace {
        font-weight: 600 !important;
        font-variant-numeric: tabular-nums;
    }

    /* Page heading */
    #pnl-report-root .page-heading {
        color: var(--pnl-heading);
        font-size: 18px;
        font-weight: 700;
        letter-spacing: -.25px;
    }

    #pnl-report-root .page-subtitle {
        color: var(--pnl-muted);
        font-size: 11.5px;
        line-height: 1.5;
    }

    /* Compact buttons */
    #pnl-report-root .btn-compact {
        min-height: 32px;
        padding: 6px 11px !important;
        font-size: 11.5px !important;
        line-height: 1.35 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 2px;
        white-space: nowrap;
        transition: background-color .15s ease, border-color .15s ease, color .15s ease;
    }

    #pnl-report-root .btn-compact:focus-visible,
    #pnl-report-root .collapse-toggle-btn:focus-visible {
        outline: 2px solid var(--pnl-primary);
        outline-offset: 2px;
    }

    /* Report cards */
    #pnl-report-root .report-card {
        min-width: 0;
        margin-bottom: 16px;
        border: 1px solid var(--pnl-border) !important;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .045) !important;
    }

    #pnl-report-root .report-card-header {
        min-height: 42px;
        padding: 10px 14px;
        background: #fff;
        border-bottom: 1px solid var(--pnl-border);
    }

    #pnl-report-root .report-card-header h6 {
        color: var(--pnl-heading);
        line-height: 1.4;
    }

    /* Collapse button */
    #pnl-report-root .collapse-toggle-btn {
        width: 27px;
        height: 27px;
        min-width: 27px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #d7dce3;
        color: #6b7280;
        transition: all .2s ease;
        flex-shrink: 0;
    }

    #pnl-report-root .collapse-toggle-btn:hover {
        background: var(--pnl-primary);
        border-color: var(--pnl-primary);
        color: #fff;
    }

    #pnl-report-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    #pnl-report-root .collapse-icon {
        font-size: 10px;
        transition: transform .25s ease;
    }

    /* Filter panel */
    #pnl-report-root .filter-card {
        padding: 14px;
        background: #fff;
    }

    #pnl-report-root .form-label {
        display: block;
        margin-bottom: 5px;
        color: #4b5563;
        font-size: 11px;
        font-weight: 700;
    }

    #pnl-report-root .form-control,
    #pnl-report-root .form-select {
        min-height: 35px;
        padding: 6px 10px;
        border: 1px solid #d8dee8;
        background-color: #fff;
        color: #374151;
        font-size: 12px;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #pnl-report-root .form-control:focus,
    #pnl-report-root .form-select:focus {
        border-color: var(--pnl-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .11);
        outline: none;
    }

    #pnl-report-root .form-control::placeholder {
        color: #9ca3af;
    }

    /* Period information */
    #pnl-report-root .period-info {
        border: 1px solid var(--pnl-border);
        background: #fff;
        color: var(--pnl-text);
    }

    /* KPI cards */
    #pnl-report-root .kpi-stat-card {
        position: relative;
        height: 100%;
        min-width: 0;
        overflow: hidden;
        padding: 14px;
        border: 1px solid var(--pnl-border);
        background: #fff;
        transition: box-shadow .2s ease, transform .2s ease;
    }

    #pnl-report-root .kpi-stat-card:hover {
        box-shadow: 0 4px 12px rgba(15, 23, 42, .07) !important;
    }

    #pnl-report-root .kpi-stat-card::before {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: 3px;
        content: '';
    }

    #pnl-report-root .kpi-sales::before {
        background: #1572e8;
    }

    #pnl-report-root .kpi-gross::before {
        background: #ffad46;
    }

    #pnl-report-root .kpi-expense::before {
        background: #f25961;
    }

    #pnl-report-root .kpi-net::before {
        background: #31ce36;
    }

    #pnl-report-root .kpi-stat-card .kpi-label {
        display: block;
        margin-bottom: 7px;
        color: var(--pnl-muted);
        font-size: 10.5px;
        font-weight: 600;
        line-height: 1.4;
    }

    #pnl-report-root .kpi-stat-card h5 {
        margin-bottom: 4px;
        color: var(--pnl-heading);
        font-size: clamp(14px, 1.3vw, 18px);
        font-weight: 700;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    #pnl-report-root .kpi-stat-card small {
        display: block;
        margin-top: 3px;
        font-size: 9.5px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    /* Tables */
    #pnl-report-root .pc-fit-container {
        width: 100%;
        min-width: 0;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #pnl-report-root .statement-table {
        width: 100%;
        min-width: 540px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #pnl-report-root .statement-table thead th {
        padding: 10px 12px;
        border-bottom: 1px solid var(--pnl-border);
        background: #f8fafc;
        color: #5b6472;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .025em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    #pnl-report-root .statement-table tbody td {
        padding: 9px 12px;
        border-bottom: 1px solid #edf0f4;
        color: var(--pnl-text);
        font-size: 12px;
        vertical-align: middle;
    }

    #pnl-report-root .statement-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #pnl-report-root .statement-table tbody tr:not(.statement-section-header):hover {
        background: #fafcff;
    }

    #pnl-report-root .statement-section-header td {
        padding: 9px 12px;
        background: #f3f6fb;
        color: #344054;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .025em;
        text-transform: uppercase;
    }

    #pnl-report-root .statement-table .bg-light {
        background-color: #f8fafc !important;
    }

    #pnl-report-root .statement-table .text-end {
        white-space: nowrap;
    }

    #pnl-report-root .statement-table .text-truncate {
        max-width: 320px;
    }

    /* Main statement */
    #pnl-report-root .main-statement-title {
        min-width: 0;
        line-height: 1.4;
    }

    #pnl-report-root .main-statement-title span {
        overflow-wrap: anywhere;
    }

    /* Side overview cards */
    #pnl-report-root .side-stat-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 9px 0;
        border-bottom: 1px solid #edf0f4;
    }

    #pnl-report-root .side-stat-row:last-child {
        border-bottom: 0;
    }

    #pnl-report-root .side-stat-row > * {
        min-width: 0;
    }

    #pnl-report-root .side-stat-row strong,
    #pnl-report-root .side-stat-row span {
        overflow-wrap: anywhere;
    }

    #pnl-report-root .side-stat-row > div {
        flex: 1;
    }

    #pnl-report-root .side-stat-row > span:last-child,
    #pnl-report-root .side-stat-row > strong:last-child {
        text-align: right;
    }

    #pnl-report-root .side-card-title {
        padding-bottom: 9px;
        margin-bottom: 4px;
        border-bottom: 1px solid var(--pnl-border);
        color: var(--pnl-heading);
        font-size: 13px;
        font-weight: 700;
        line-height: 1.45;
    }

    #pnl-report-root .side-info-box {
        border: 1px solid var(--pnl-border);
        border-radius: 5px !important;
        background: #f8fafc;
    }

    /* Responsive */
    @media (max-width: 1199.98px) {
        #pnl-report-root .top-action-btns {
            gap: 6px !important;
        }

        #pnl-report-root .btn-compact {
            padding-right: 9px !important;
            padding-left: 9px !important;
        }
    }

    @media (max-width: 991.98px) {
        #pnl-report-root .kpi-stat-card {
            padding: 12px;
        }

        #pnl-report-root .main-statement-title {
            font-size: 12px !important;
        }
    }

    @media (max-width: 767.98px) {
        #pnl-report-root {
            padding-right: 10px !important;
            padding-left: 10px !important;
        }

        #pnl-report-root .header-top-wrap {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 11px !important;
        }

        #pnl-report-root .page-heading {
            font-size: 17px;
        }

        #pnl-report-root .top-action-btns {
            display: grid;
            width: 100%;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 7px !important;
        }

        #pnl-report-root .top-action-btns .btn {
            width: 100%;
            min-width: 0;
            min-height: 34px;
            justify-content: center;
            padding: 7px 5px !important;
            font-size: 11px !important;
        }

        #pnl-report-root .filter-card {
            padding: 12px !important;
        }

        #pnl-report-root .filter-actions {
            width: 100%;
        }

        #pnl-report-root .filter-actions .btn {
            flex: 1;
        }

        #pnl-report-root .period-info {
            padding: 10px 12px !important;
            font-size: 11px !important;
        }

        #pnl-report-root .report-card-header {
            padding: 9px 11px;
        }

        #pnl-report-root .kpi-stat-card {
            padding: 11px 12px;
        }

        #pnl-report-root .kpi-stat-card h5 {
            font-size: 14px;
        }

        #pnl-report-root .statement-table {
            min-width: 540px;
            table-layout: auto;
        }

        #pnl-report-root .side-stat-row {
            padding: 8px 0;
        }
    }

    @media (max-width: 420px) {
        #pnl-report-root .page-heading {
            font-size: 16px;
        }

        #pnl-report-root .page-subtitle {
            font-size: 10.5px;
        }

        #pnl-report-root .kpi-stat-card {
            padding: 10px;
        }

        #pnl-report-root .kpi-stat-card h5 {
            font-size: 12px;
        }

        #pnl-report-root .kpi-stat-card .kpi-label {
            font-size: 9.5px;
        }

        #pnl-report-root .kpi-stat-card small {
            font-size: 9px !important;
        }

        #pnl-report-root .period-info {
            gap: 7px !important;
        }

        #pnl-report-root .side-stat-row {
            gap: 8px;
        }
    }

    @media (max-width: 350px) {
        #pnl-report-root .top-action-btns .btn {
            font-size: 10px !important;
        }

        #pnl-report-root .kpi-stat-card h5 {
            font-size: 11px;
        }
    }

    /* Print */
    @media print {
        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        body {
            background: #fff !important;
            color: #000 !important;
        }

        #pnl-report-root .no-print,
        .admin-dark-sidebar,
        .admin-header,
        .main-footer {
            display: none !important;
        }

        #pnl-report-root {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        #pnl-report-root .report-card {
            margin-bottom: 10px !important;
            border: 1px solid #000 !important;
            box-shadow: none !important;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        #pnl-report-root .collapse:not(.show) {
            display: block !important;
        }

        #pnl-report-root .pc-fit-container {
            overflow: visible !important;
        }

        #pnl-report-root .statement-table {
            min-width: 0 !important;
        }

        #pnl-report-root .statement-table thead th,
        #pnl-report-root .statement-section-header td {
            background: #f1f1f1 !important;
            color: #000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        #pnl-report-root .statement-table tbody td {
            color: #000 !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="pnl-report-root">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 header-top-wrap no-print">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2 page-heading">
                <i class="fas fa-file-invoice-dollar text-primary"></i>
                <span>Profit &amp; Loss Statement</span>
            </h5>
            <small class="page-subtitle d-block">
                Audited Real P&amp;L: Sales, Direct Costs, Shop Overheads &amp; Staff Salaries
            </small>
        </div>

        <div class="top-action-btns d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-dark btn-compact shadow-xs" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print
            </button>

            <a href="{{ route('admin.report.export_pnl') }}?{{ http_build_query(request()->all()) }}" class="btn btn-sm btn-outline-success btn-compact shadow-xs">
                <i class="fas fa-file-csv me-1"></i> Export CSV
            </a>

            <a href="{{ route('admin.payroll.index') }}" class="btn btn-sm btn-outline-primary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-money-check-dollar me-1"></i> Staff Payroll
            </a>

            <a href="{{ route('admin.report.balance_sheet') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-scale-balanced me-1"></i> Balance Sheet
            </a>

            <a href="{{ route('admin.report.cash_flow') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-money-bill-trend-up me-1 text-primary"></i> Cash Flow
            </a>

            <a href="{{ route('admin.report.cogs') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-boxes-stacked me-1 text-warning"></i> COGS
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm report-card filter-card mb-3 no-print">
        <form action="{{ route('admin.report.profit_loss') }}" method="GET" class="row g-2 align-items-end">

            <div class="col-6 col-md-3">
                <label class="form-label">Period</label>
                <select name="date_filter" id="dateFilterSelect" class="form-select font-monospace" onchange="toggleCustomDates(this.value)">
                    <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ $dateFilter === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="this_week" {{ $dateFilter === 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ $dateFilter === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $dateFilter === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="this_year" {{ $dateFilter === 'this_year' ? 'selected' : '' }}>This Year</option>
                    <option value="custom" {{ $dateFilter === 'custom' ? 'selected' : '' }}>Custom</option>
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
                <label class="form-label">Channel</label>
                <select name="channel" class="form-select font-monospace">
                    <option value="all">All Channels</option>
                    <option value="web" {{ request('channel') === 'web' ? 'selected' : '' }}>Online Store</option>
                    <option value="manual" {{ request('channel') === 'manual' ? 'selected' : '' }}>Manual / Social</option>
                    <option value="pos" {{ request('channel') === 'pos' ? 'selected' : '' }}>POS Outlet</option>
                </select>
            </div>

            <div class="col-12 col-md-auto d-flex gap-2 filter-actions">
                <button type="submit" class="btn btn-dark btn-compact shadow-xs fw-bold px-3">
                    <i class="fas fa-filter me-1"></i> Apply
                </button>

                @if(request()->anyFilled(['date_filter', 'channel', 'start_date', 'end_date']))
                    <a href="{{ route('admin.report.profit_loss') }}" class="btn btn-light border btn-compact shadow-xs">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Period Info --}}
    <div class="alert alert-light period-info border py-2 px-3 small d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 shadow-xs">
        <div>
            <span class="text-muted">Period:</span>
            <strong class="text-dark ms-1">{{ $summary['period_label'] ?? 'Current Period' }}</strong>
        </div>

        <div>
            <span class="text-muted">Delivered Orders:</span>
            <strong class="font-monospace bn-digit text-primary ms-1">{{ $summary['delivered_orders'] ?? 0 }} Orders</strong>
        </div>
    </div>

    {{-- 4 Core KPI Stat Cards --}}
    <div class="report-card mb-3">
        <div class="report-card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-chart-pie text-primary"></i>
                <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">Executive Profit &amp; Cost Overview</h6>
            </div>

            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapsePnlKpis" aria-expanded="true" aria-controls="collapsePnlKpis" title="Toggle Summary">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapsePnlKpis">
            <div class="p-3 bg-light bg-opacity-25">
                <div class="row g-2">

                    <!-- 1. Total Customer Bill -->
                    <div class="col-6 col-lg-3">
                        <div class="kpi-stat-card kpi-sales shadow-xs">
                            <span class="kpi-label">Customer Total Bill</span>
                            <h5 class="font-monospace bn-digit">৳ {{ number_format((float)($summary['total_customer_bill'] ?? ($summary['net_sales'] ?? 0)), 2) }}</h5>
                            <small class="text-muted">Sales + Delivery + VAT</small>
                        </div>
                    </div>

                    <!-- 2. Estimated Gross Profit -->
                    <div class="col-6 col-lg-3">
                        <div class="kpi-stat-card kpi-gross shadow-xs">
                            <span class="kpi-label">Est. Gross Profit</span>
                            <h5 class="font-monospace bn-digit">৳ {{ number_format((float)($summary['estimated_gross_profit'] ?? ($summary['gross_profit'] ?? 0)), 2) }}</h5>
                            <small class="text-warning fw-bold font-monospace">Margin: {{ $summary['gross_margin'] ?? 0 }}%</small>
                        </div>
                    </div>

                    <!-- 3. Overheads & Staff Salaries -->
                    <div class="col-6 col-lg-3">
                        <div class="kpi-stat-card kpi-expense shadow-xs">
                            <span class="kpi-label">Expenses &amp; Salaries</span>
                            <h5 class="text-danger font-monospace bn-digit">৳ {{ number_format((float)($summary['total_operating_expenses'] ?? ($summary['operating_expenses'] ?? 0)), 2) }}</h5>
                            <small class="text-muted">Shop: ৳{{ number_format((float)($summary['general_expenses'] ?? 0), 0) }} | Staff: ৳{{ number_format((float)($summary['staff_salary_expense'] ?? 0), 0) }}</small>
                        </div>
                    </div>

                    <!-- 4. Real Net Profit -->
                    <div class="col-6 col-lg-3">
                        <div class="kpi-stat-card kpi-net shadow-xs">
                            <span class="kpi-label">Real Net Profit (EBIT)</span>
                            <h5 class="{{ ($summary['net_profit'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }} font-monospace bn-digit">
                                ৳ {{ number_format((float)($summary['net_profit'] ?? 0), 2) }}
                            </h5>
                            <small class="{{ ($summary['net_profit'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }} fw-bold font-monospace">
                                Net Margin: {{ $summary['net_margin'] ?? 0 }}%
                            </small>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Main Statement & Overviews --}}
    <div class="row g-3">

        {{-- Left: Itemized Income Statement Table --}}
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm report-card overflow-hidden">
                <div class="report-card-header d-flex justify-content-between align-items-center gap-2">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1 main-statement-title" style="font-size: 13px;">
                        <i class="fas fa-table-list text-primary"></i>
                        <span>Statement of Real Profit and Loss</span>
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
                                        <th>Account Particulars</th>
                                        <th class="text-end" style="width: 140px;">Debit / Cost (৳)</th>
                                        <th class="text-end pe-3" style="width: 150px;">Credit / Net (৳)</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    {{-- 1. Total Customer Bill --}}
                                    <tr class="statement-section-header">
                                        <td colspan="3"><i class="fas fa-file-invoice me-1 text-primary"></i> 1. Customer Invoiced Bill &amp; Revenue Inflow</td>
                                    </tr>

                                    <tr>
                                        <td class="ps-3">Gross Product Sales (Delivered Orders)</td>
                                        <td class="text-end text-muted font-monospace bn-digit">—</td>
                                        <td class="text-end pe-3 font-monospace bn-digit">৳ {{ number_format((float)($summary['gross_sales'] ?? 0), 2) }}</td>
                                    </tr>

                                    @if(($summary['discounts'] ?? 0) > 0)
                                        <tr>
                                            <td class="ps-3 text-danger">Less: Customer Discounts Granted</td>
                                            <td class="text-end text-danger font-monospace bn-digit">-৳ {{ number_format((float)$summary['discounts'], 2) }}</td>
                                            <td class="text-end pe-3 text-muted font-monospace bn-digit">—</td>
                                        </tr>
                                    @endif

                                    <tr>
                                        <td class="ps-3">Net Product Sales</td>
                                        <td class="text-end text-muted font-monospace bn-digit">—</td>
                                        <td class="text-end pe-3 font-monospace bn-digit">৳ {{ number_format((float)($summary['net_sales'] ?? 0), 2) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="ps-3">Customer Delivery Fees Collected</td>
                                        <td class="text-end text-muted font-monospace bn-digit">—</td>
                                        <td class="text-end pe-3 font-monospace bn-digit">৳ {{ number_format((float)($summary['shipping_collected'] ?? 0), 2) }}</td>
                                    </tr>

                                    @if(($summary['collected_vat'] ?? 0) > 0)
                                        <tr>
                                            <td class="ps-3">Output VAT / Tax Collected from Customer</td>
                                            <td class="text-end text-muted font-monospace bn-digit">—</td>
                                            <td class="text-end pe-3 font-monospace bn-digit">৳ {{ number_format((float)$summary['collected_vat'], 2) }}</td>
                                        </tr>
                                    @endif

                                    <tr class="bg-light fw-bold">
                                        <td class="ps-3 text-dark">Customer Total Billed Inflow</td>
                                        <td class="text-end">—</td>
                                        <td class="text-end pe-3 text-primary font-monospace bn-digit">৳ {{ number_format((float)($summary['total_customer_bill'] ?? ($summary['net_sales'] ?? 0)), 2) }}</td>
                                    </tr>

                                    {{-- 2. Direct Sales Costs --}}
                                    <tr class="statement-section-header">
                                        <td colspan="3"><i class="fas fa-boxes-packing me-1 text-warning"></i> 2. Direct Sales Costs &amp; Deductions</td>
                                    </tr>

                                    @if(($summary['collected_vat'] ?? 0) > 0)
                                        <tr>
                                            <td class="ps-3 text-danger">Less: Output VAT Payable to Govt (Pass-through)</td>
                                            <td class="text-end text-danger font-monospace bn-digit">৳ {{ number_format((float)$summary['collected_vat'], 2) }}</td>
                                            <td class="text-end pe-3 text-muted font-monospace bn-digit">—</td>
                                        </tr>
                                    @endif

                                    <tr>
                                        <td class="ps-3 text-danger">Less: Actual Courier Dispatch &amp; COD Fees</td>
                                        <td class="text-end text-danger font-monospace bn-digit">৳ {{ number_format((float)($summary['total_courier_cost'] ?? ($summary['courier_cost_spent'] ?? 0)), 2) }}</td>
                                        <td class="text-end pe-3 text-muted font-monospace bn-digit">—</td>
                                    </tr>

                                    <tr>
                                        <td class="ps-3 text-danger">Less: Product Purchase Cost (WAC COGS)</td>
                                        <td class="text-end text-danger font-monospace bn-digit">৳ {{ number_format((float)($summary['cogs'] ?? 0), 2) }}</td>
                                        <td class="text-end pe-3 text-muted font-monospace bn-digit">—</td>
                                    </tr>

                                    @if(($summary['damage_loss'] ?? 0) > 0)
                                        <tr>
                                            <td class="ps-3 text-danger">Less: Damaged / Written-Off Inventory Loss</td>
                                            <td class="text-end text-danger font-monospace bn-digit">৳ {{ number_format((float)$summary['damage_loss'], 2) }}</td>
                                            <td class="text-end pe-3 text-muted font-monospace bn-digit">—</td>
                                        </tr>
                                    @endif

                                    <tr class="bg-light fw-bold">
                                        <td class="ps-3 text-dark">Estimated Gross Profit ({{ $summary['gross_margin'] ?? 0 }}%)</td>
                                        <td class="text-end">—</td>
                                        <td class="text-end pe-3 text-warning-emphasis font-monospace bn-digit">৳ {{ number_format((float)($summary['estimated_gross_profit'] ?? ($summary['gross_profit'] ?? 0)), 2) }}</td>
                                    </tr>

                                    {{-- 3. Other Inflows --}}
                                    @if(($summary['other_income'] ?? 0) > 0 || (isset($incomeCategoryBreakdown) && $incomeCategoryBreakdown->isNotEmpty()))
                                        <tr class="statement-section-header">
                                            <td colspan="3"><i class="fas fa-hand-holding-dollar me-1 text-success"></i> 3. Other Business Inflows</td>
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
                                                <td class="ps-3 text-success">Add: Other Operating Income</td>
                                                <td class="text-end text-muted font-monospace bn-digit">—</td>
                                                <td class="text-end pe-3 text-success font-monospace bn-digit">৳ {{ number_format((float)($summary['other_income'] ?? 0), 2) }}</td>
                                            </tr>
                                        @endif
                                    @endif

                                    {{-- 4. Operating Expenses & Staff Payroll --}}
                                    <tr class="statement-section-header">
                                        <td colspan="3"><i class="fas fa-receipt me-1 text-danger"></i> 4. Operating Overheads &amp; Staff Salaries</td>
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
                                            <td class="ps-3 text-danger">Less: Shop, Transport &amp; Utilities</td>
                                            <td class="text-end text-danger font-monospace bn-digit">৳ {{ number_format((float)($summary['general_expenses'] ?? ($summary['operating_expenses'] ?? 0)), 2) }}</td>
                                            <td class="text-end pe-3 text-muted font-monospace bn-digit">—</td>
                                        </tr>
                                    @endif

                                    <tr>
                                        <td class="ps-3 text-danger fw-semibold">
                                            <i class="fas fa-money-check-dollar me-1"></i> Less: Staff Monthly Salaries Paid
                                        </td>
                                        <td class="text-end text-danger font-monospace bn-digit fw-bold">৳ {{ number_format((float)($summary['staff_salary_expense'] ?? 0), 2) }}</td>
                                        <td class="text-end pe-3 text-muted font-monospace bn-digit">—</td>
                                    </tr>

                                    <tr class="bg-light">
                                        <td class="ps-3 text-secondary fw-semibold">Total Operating &amp; Salary Expenses</td>
                                        <td class="text-end text-danger font-monospace bn-digit fw-bold">৳ {{ number_format((float)($summary['total_operating_expenses'] ?? ($summary['operating_expenses'] ?? 0)), 2) }}</td>
                                        <td class="text-end pe-3 text-muted font-monospace bn-digit">—</td>
                                    </tr>

                                    {{-- 5. Real Net Profit --}}
                                    <tr class="bg-light border-top border-2 fw-bold" style="font-size: 12.5px;">
                                        <td class="py-2 ps-3">
                                            <span class="text-dark">REAL NET BOTTOM-LINE PROFIT (EBIT)</span>
                                            <small class="d-block text-muted fw-normal" style="font-size: 10px;">
                                                Bill - VAT - Courier - COGS - Damages + Other Income - Shop Expenses - Staff Salary
                                            </small>
                                        </td>

                                        <td class="text-end py-2">—</td>

                                        <td class="text-end pe-3 py-2 font-monospace bn-digit {{ ($summary['net_profit'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }} fw-bold" style="font-size: 14px;">
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

        {{-- Right: Side Overviews --}}
        <div class="col-12 col-lg-4">

            {{-- Channel Performance --}}
            <div class="card border-0 shadow-sm report-card mb-3 p-3">
                <h6 class="side-card-title d-flex align-items-center gap-1">
                    <i class="fas fa-network-wired text-info"></i> Sales Channels
                </h6>

                <div class="side-stat-row">
                    <div>
                        <strong class="d-block text-dark small">Online Store</strong>
                        <small class="text-muted font-monospace bn-digit" style="font-size: 10px;">{{ $channelStats['web']['count'] ?? 0 }} orders</small>
                    </div>
                    <span class="fw-bold text-primary font-monospace bn-digit" style="font-size: 12px;">৳ {{ number_format((float)($channelStats['web']['revenue'] ?? 0), 2) }}</span>
                </div>

                <div class="side-stat-row">
                    <div>
                        <strong class="d-block text-dark small">Manual / Social</strong>
                        <small class="text-muted font-monospace bn-digit" style="font-size: 10px;">{{ $channelStats['manual']['count'] ?? 0 }} orders</small>
                    </div>
                    <span class="fw-bold text-warning font-monospace bn-digit" style="font-size: 12px;">৳ {{ number_format((float)($channelStats['manual']['revenue'] ?? 0), 2) }}</span>
                </div>

                <div class="side-stat-row">
                    <div>
                        <strong class="d-block text-dark small">POS Outlet</strong>
                        <small class="text-muted font-monospace bn-digit" style="font-size: 10px;">{{ $channelStats['pos']['count'] ?? 0 }} orders</small>
                    </div>
                    <span class="fw-bold text-success font-monospace bn-digit" style="font-size: 12px;">৳ {{ number_format((float)($channelStats['pos']['revenue'] ?? 0), 2) }}</span>
                </div>
            </div>

            {{-- Staff Payroll Quick Card --}}
            <div class="card border-0 shadow-sm report-card mb-3 p-3">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2 gap-2">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13px;">
                        <i class="fas fa-money-check-dollar text-primary"></i> Staff Payroll (Salaries)
                    </h6>
                    <a href="{{ route('admin.payroll.index') }}" class="small text-primary text-decoration-none fw-semibold text-nowrap" style="font-size: 11px;">Manage Sheet</a>
                </div>

                <div class="side-stat-row small">
                    <span class="text-muted">Disbursed This Period:</span>
                    <strong class="font-monospace text-danger bn-digit">৳ {{ number_format((float)($summary['staff_salary_expense'] ?? 0), 2) }}</strong>
                </div>

                <div class="side-info-box p-2 mt-2 small font-monospace text-muted" style="font-size: 10px;">
                    <i class="fas fa-circle-info text-primary me-1"></i> Auto-counted based on joining date closing on 30th.
                </div>
            </div>

            {{-- Expenses Breakdown --}}
            <div class="card border-0 shadow-sm report-card mb-3 p-3">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2 gap-2">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13px;">
                        <i class="fas fa-receipt text-danger"></i> Shop &amp; Overheads
                    </h6>
                    <a href="{{ url('/admin/accounts/expenses') }}" class="small text-primary text-decoration-none fw-semibold text-nowrap" style="font-size: 11px;">View All</a>
                </div>

                @if(isset($expenseCategoryBreakdown) && $expenseCategoryBreakdown->isNotEmpty())
                    @foreach($expenseCategoryBreakdown as $expRow)
                        <div class="side-stat-row">
                            <span class="text-dark small">{{ $expRow->category_name }}</span>
                            <span class="fw-bold text-danger font-monospace bn-digit" style="font-size: 12px;">৳ {{ number_format((float)$expRow->total_amount, 2) }}</span>
                        </div>
                    @endforeach

                    <div class="d-flex justify-content-between align-items-center pt-2 gap-2">
                        <strong class="text-dark small">Total Overheads:</strong>
                        <strong class="text-danger font-monospace bn-digit" style="font-size: 12px;">৳ {{ number_format((float)($summary['general_expenses'] ?? 0), 2) }}</strong>
                    </div>
                @else
                    <p class="text-muted small mb-0 py-2 text-center">No shop expenses recorded.</p>
                @endif
            </div>

            {{-- Shipping & Courier Cost --}}
            <div class="card border-0 shadow-sm report-card p-3">
                <h6 class="side-card-title d-flex align-items-center gap-1">
                    <i class="fas fa-boxes-packing text-primary"></i> Shipping &amp; Courier Summary
                </h6>

                <div class="side-stat-row small">
                    <span class="text-muted">Delivery Fee Collected</span>
                    <strong class="font-monospace text-dark bn-digit">৳ {{ number_format((float)($summary['shipping_collected'] ?? 0), 2) }}</strong>
                </div>

                <div class="side-stat-row small">
                    <span class="text-muted">Actual Courier Paid</span>
                    <strong class="font-monospace text-danger bn-digit">৳ {{ number_format((float)($summary['courier_cost_spent'] ?? 0), 2) }}</strong>
                </div>

                @if(($summary['courier_cod_fee'] ?? 0) > 0)
                    <div class="side-stat-row small">
                        <span class="text-muted">Courier COD Fee</span>
                        <strong class="font-monospace text-danger bn-digit">৳ {{ number_format((float)$summary['courier_cod_fee'], 2) }}</strong>
                    </div>
                @endif

                <div class="d-flex justify-content-between py-2 border-top mt-1 small gap-2">
                    <strong class="text-dark">Net Logistics Margin:</strong>
                    <strong class="font-monospace bn-digit {{ ($summary['logistics_balance'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                        ৳ {{ number_format((float)($summary['logistics_balance'] ?? 0), 2) }}
                    </strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Products Table --}}
    <div class="card border-0 shadow-sm report-card overflow-hidden mt-2">
        <div class="report-card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center gap-2">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13px;">
                <i class="fas fa-ranking-star text-warning"></i>
                <span>Top High-Margin Profitable Products</span>
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
                                <th>Product Title</th>
                                <th class="text-center" style="width: 100px;">Sold Qty</th>
                                <th class="text-end" style="width: 130px;">Sales (৳)</th>
                                <th class="text-end" style="width: 130px;">WAC COGS (৳)</th>
                                <th class="text-end" style="width: 130px;">Gross Profit (৳)</th>
                                <th class="text-end pe-3" style="width: 100px;">Margin</th>
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
                                    <td colspan="7" class="text-center py-4 text-muted small">No product sales found for this period.</td>
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
