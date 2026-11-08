
@extends('layouts.admin')

@section('title', 'Branch Analytics Dashboard - ' . $branch->name)

@section('admin_content')
<style>
    /* =========================================================
       Branch Analytics - Kaiadmin Lite Classic / White Classic
    ========================================================= */

    #branchReportRoot {
        --report-primary: #1572e8;
        --report-primary-soft: #eaf2ff;
        --report-success: #31ce36;
        --report-border: #e5e7eb;
        --report-text: #2a2f36;
        --report-muted: #6b7280;
        --report-surface: #ffffff;
        --report-bg: #f8fafc;
        color: var(--report-text);
        font-size: 13px;
    }

    #branchReportRoot .btn,
    #branchReportRoot .btn-sm,
    #branchReportRoot button,
    #branchReportRoot .badge,
    #branchReportRoot .form-control,
    #branchReportRoot .form-select,
    #branchReportRoot .input-group-text,
    #branchReportRoot .card,
    #branchReportRoot .alert,
    #branchReportRoot .table,
    #branchReportRoot .table th,
    #branchReportRoot .table td,
    #branchReportRoot .progress,
    #branchReportRoot .dropdown-menu {
        border-radius: 5px !important;
    }

    #branchReportRoot .card {
        border-color: var(--report-border) !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.035);
    }

    #branchReportRoot .font-monospace,
    #branchReportRoot .bn-digit {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
        font-weight: 600 !important;
    }

    #branchReportRoot .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        min-height: 32px;
        padding: 6px 11px !important;
        font-size: 11.5px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        white-space: nowrap;
        transition: background-color .15s ease, border-color .15s ease, color .15s ease;
    }

    #branchReportRoot .metric-card-box {
        position: relative;
        min-height: 112px;
        height: 100%;
        padding: 15px 16px;
        overflow: hidden;
        background: var(--report-surface);
        border: 1px solid var(--report-border);
        border-radius: 5px;
        transition: box-shadow .15s ease, transform .15s ease;
    }

    #branchReportRoot .metric-card-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 14px rgba(15, 23, 42, .06);
    }

    #branchReportRoot .metric-icon-bg {
        position: absolute;
        top: 13px;
        right: 14px;
        font-size: 27px;
        opacity: .10;
        pointer-events: none;
    }

    #branchReportRoot .metric-card-box > span,
    #branchReportRoot .metric-card-box > h4,
    #branchReportRoot .metric-card-box > div {
        position: relative;
        z-index: 1;
    }

    #branchReportRoot .metric-card-box h4 {
        margin-top: 5px;
        font-size: clamp(15px, 1.45vw, 19px);
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    #branchReportRoot .period-tab-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 30px;
        padding: 5px 10px !important;
        border: 1px solid #dce2ea;
        border-radius: 5px !important;
        background: #fff;
        color: #475569;
        font-size: 11px !important;
        font-weight: 600;
        line-height: 1.2;
        text-decoration: none;
        white-space: nowrap;
        transition: all .15s ease;
    }

    #branchReportRoot .period-tab-btn:hover {
        color: var(--report-primary);
        border-color: var(--report-primary);
        background: var(--report-primary-soft);
    }

    #branchReportRoot .period-tab-btn.active {
        color: #fff !important;
        background: var(--report-primary) !important;
        border-color: var(--report-primary) !important;
    }

    #branchReportRoot .branch-header {
        gap: 12px;
        min-width: 0;
        border-color: var(--report-border) !important;
        border-radius: 5px;
    }

    #branchReportRoot .branch-header-info,
    #branchReportRoot .branch-header-actions {
        min-width: 0;
    }

    #branchReportRoot .branch-header-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 6px;
    }

    #branchReportRoot .report-filter-card {
        border-radius: 5px;
    }

    #branchReportRoot .report-filter-content {
        gap: 12px;
    }

    #branchReportRoot .report-period-options {
        gap: 5px;
    }

    #branchReportRoot .report-date-form {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
    }

    #branchReportRoot .report-date-form .form-control {
        min-height: 32px;
        border-color: #dce2ea;
        font-size: 11px;
    }

    #branchReportRoot .form-control:focus,
    #branchReportRoot .form-select:focus {
        border-color: #80b5ff;
        box-shadow: 0 0 0 .15rem rgba(21, 114, 232, .12);
    }

    #branchReportRoot .report-section-title {
        color: var(--report-text);
        font-size: 12.5px;
        font-weight: 700;
        line-height: 1.4;
    }

    #branchReportRoot .report-panel {
        height: 100%;
        padding: 15px !important;
        background: #fff;
    }

    #branchReportRoot .report-panel-heading {
        display: flex;
        align-items: center;
        gap: 6px;
        min-height: 29px;
        margin-bottom: 12px !important;
        padding-bottom: 9px !important;
        border-bottom: 1px solid var(--report-border) !important;
    }

    #branchReportRoot .report-data-list {
        font-size: 11.5px;
    }

    #branchReportRoot .report-data-list li {
        gap: 12px;
        min-height: 33px;
        padding-top: 7px !important;
        padding-bottom: 7px !important;
    }

    #branchReportRoot .report-data-list li > span {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    #branchReportRoot .report-data-list li > strong {
        flex-shrink: 0;
        white-space: nowrap;
    }

    #branchReportRoot .progress {
        overflow: hidden;
        background-color: #edf1f5;
    }

    #branchReportRoot .table-responsive {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #branchReportRoot .table {
        margin-bottom: 0;
        vertical-align: middle;
        border-color: #edf0f3;
    }

    #branchReportRoot .table thead th {
        padding: 10px 9px;
        color: #5b6472;
        background: #f8fafc;
        border-bottom: 1px solid var(--report-border);
        font-size: 10.5px;
        font-weight: 700;
        white-space: nowrap;
        vertical-align: middle;
    }

    #branchReportRoot .table tbody td {
        padding: 9px;
        border-bottom-color: #edf0f3;
        color: #4b5563;
        vertical-align: middle;
    }

    #branchReportRoot .table-hover tbody tr:hover {
        background-color: #f8fafc;
    }

    #branchReportRoot .top-products-table {
        min-width: 390px;
    }

    #branchReportRoot .recent-orders-table {
        min-width: 940px;
    }

    #branchReportRoot .product-name {
        max-width: 180px;
        overflow-wrap: anywhere;
    }

    #branchReportRoot .report-table-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 21px;
        padding: 4px 7px;
        font-size: 9.5px;
        font-weight: 600;
        line-height: 1.2;
        white-space: nowrap;
    }

    #branchReportRoot .report-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 30px;
        min-height: 30px;
        padding: 4px 8px;
    }

    #branchReportRoot .report-empty-state {
        padding: 22px 12px !important;
        color: var(--report-muted);
        text-align: center;
    }

    #branchReportRoot .report-bottom-heading {
        gap: 10px;
    }

    #branchReportRoot .report-bottom-heading h6 {
        line-height: 1.4;
    }

    @media (max-width: 991.98px) {
        #branchReportRoot .branch-header {
            align-items: flex-start !important;
        }

        #branchReportRoot .branch-header-actions {
            justify-content: flex-start;
        }

        #branchReportRoot .report-filter-content {
            align-items: flex-start !important;
        }

        #branchReportRoot .metric-card-box {
            min-height: 108px;
        }
    }

    @media (max-width: 767.98px) {
        #branchReportRoot {
            padding-right: 10px !important;
            padding-left: 10px !important;
        }

        #branchReportRoot .branch-header {
            flex-direction: column;
            align-items: stretch !important;
            padding: 12px !important;
        }

        #branchReportRoot .branch-header-info {
            gap: 7px !important;
        }

        #branchReportRoot .branch-header-actions {
            width: 100%;
            justify-content: flex-start;
        }

        #branchReportRoot .branch-header-actions .btn,
        #branchReportRoot .branch-header-actions .dropdown {
            flex: 0 0 auto;
        }

        #branchReportRoot .report-filter-content {
            flex-direction: column;
            align-items: stretch !important;
        }

        #branchReportRoot .report-period-options {
            width: 100%;
        }

        #branchReportRoot .report-date-form {
            width: 100%;
        }

        #branchReportRoot .report-date-form input[type="date"] {
            flex: 1 1 115px;
            width: auto !important;
            min-width: 0;
        }

        #branchReportRoot .report-date-form button {
            min-width: 36px;
        }

        #branchReportRoot .metric-card-box {
            padding: 12px;
        }

        #branchReportRoot .metric-card-box h4 {
            font-size: 15px;
        }

        #branchReportRoot .report-panel {
            padding: 13px !important;
        }

        #branchReportRoot .report-bottom-heading {
            align-items: flex-start !important;
        }
    }

    @media (max-width: 420px) {
        #branchReportRoot .metric-card-box {
            min-height: 105px;
            padding: 10px;
        }

        #branchReportRoot .metric-card-box h4 {
            font-size: 13px;
        }

        #branchReportRoot .metric-card-box .metric-icon-bg {
            top: 10px;
            right: 9px;
            font-size: 22px;
        }

        #branchReportRoot .metric-card-box > div {
            gap: 3px;
            flex-wrap: wrap;
        }

        #branchReportRoot .branch-header-actions .btn-compact {
            padding-right: 8px !important;
            padding-left: 8px !important;
            font-size: 10.5px !important;
        }

        #branchReportRoot .report-bottom-heading {
            flex-direction: column;
        }
    }
</style>

<div class="container-fluid py-2 px-2 px-md-3" id="branchReportRoot">

    <!-- Branch Header -->
    <div class="branch-header d-flex justify-content-between align-items-center mb-3 bg-white p-3 shadow-xs border">

        <div class="branch-header-info d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-primary text-white fw-bold px-2 py-2" style="font-size: 11px;">
                <i class="fas fa-chart-pie me-1"></i> Branch Analytics
            </span>

            <span class="fw-bold text-dark fs-6">{{ $branch->name }}</span>

            <span class="badge bg-light text-dark border font-monospace" style="font-size: 10.5px;">
                {{ $branch->code ?? $branch->branch_code }}
            </span>

            <span class="badge bg-light text-secondary border font-monospace" style="font-size: 10px;">
                <i class="far fa-clock me-1"></i>
                {{ date('d M Y', strtotime($metrics['start_date'] ?? now())) }}
                &ndash;
                {{ date('d M Y', strtotime($metrics['end_date'] ?? now())) }}
            </span>
        </div>

        <div class="branch-header-actions">

            @if(isset($selectableBranches) && $selectableBranches->count() > 1)
                <div class="dropdown d-inline-block">
                    <button class="btn btn-outline-primary btn-compact dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-store-alt me-1"></i> Switch Branch
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border" style="font-size: 11px;">
                        @foreach($selectableBranches as $sb)
                            <li>
                                <a class="dropdown-item py-2 {{ $sb->id == $branch->id ? 'active fw-bold' : '' }}"
                                   href="{{ route('admin.branch_report.dashboard', ['branch_id' => $sb->id, 'period' => $period]) }}">
                                    {{ $sb->name }} ({{ $sb->code ?? $sb->branch_code }})
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <a href="{{ route('admin.branch_report.sales', ['branch_id' => $branch->id]) }}" class="btn btn-outline-secondary btn-compact">
                <i class="fas fa-file-invoice-dollar me-1"></i> Sales Ledger
            </a>

            <a href="{{ route('admin.pos.terminal', $branch->id) }}" class="btn btn-warning text-white btn-compact">
                <i class="fas fa-desktop me-1"></i> POS Terminal
            </a>
        </div>
    </div>

    <!-- Period Selection & Filters -->
    <div class="card report-filter-card border shadow-xs bg-white p-3 mb-3">
        <div class="report-filter-content d-flex justify-content-between align-items-center flex-wrap">

            <div class="report-period-options d-flex align-items-center flex-wrap">
                <span class="text-muted fw-semibold me-1" style="font-size: 11px;">Timeline:</span>

                @foreach([
                    'today'      => 'Today',
                    'yesterday'  => 'Yesterday',
                    'this_week'  => 'This Week',
                    'this_month' => 'This Month',
                    'last_month' => 'Last Month',
                    'this_year'  => 'This Year'
                ] as $pKey => $pLabel)
                    <a href="{{ route('admin.branch_report.dashboard', ['branch_id' => $branch->id, 'period' => $pKey]) }}"
                       class="period-tab-btn {{ $period === $pKey ? 'active' : '' }}">
                        {{ $pLabel }}
                    </a>
                @endforeach
            </div>

            <!-- Custom Date Range Form -->
            <form method="GET" action="{{ route('admin.branch_report.dashboard') }}" class="report-date-form">
                <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                <input type="hidden" name="period" value="custom">

                <input type="date" name="start_date" class="form-control form-control-sm font-monospace"
                       value="{{ request('start_date', date('Y-m-01')) }}" style="height: 32px; font-size: 11px; width: 130px;">

                <span class="text-muted small">&ndash;</span>

                <input type="date" name="end_date" class="form-control form-control-sm font-monospace"
                       value="{{ request('end_date', date('Y-m-d')) }}" style="height: 32px; font-size: 11px; width: 130px;">

                <button type="submit" class="btn btn-primary btn-sm btn-compact px-3" title="Apply Date Filter">
                    <i class="fas fa-filter"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- 1. Core Financial KPI Cards -->
    <div class="row g-2 mb-3">

        <!-- Total Sales Turnover -->
        <div class="col-6 col-md-3">
            <div class="metric-card-box border-start border-primary border-3">
                <i class="fas fa-shopping-bag metric-icon-bg text-primary"></i>

                <span class="text-muted d-block small" style="font-size: 11px;">Gross Turnover (Sales)</span>

                <h4 class="fw-bold text-dark mb-0 font-monospace bn-digit">
                    ৳{{ number_format($metrics['total_sales'] ?? 0, 2) }}
                </h4>

                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-primary fw-semibold" style="font-size: 10px;">
                        {{ $metrics['total_orders'] ?? 0 }} Orders
                    </small>
                    <small class="text-muted" style="font-size: 10px;">
                        {{ $metrics['total_items_sold'] ?? 0 }} Items
                    </small>
                </div>
            </div>
        </div>

        <!-- Net Profit -->
        <div class="col-6 col-md-3">
            <div class="metric-card-box border-start border-success border-3">
                <i class="fas fa-chart-line metric-icon-bg text-success"></i>

                <span class="text-muted d-block small" style="font-size: 11px;">Net Operational Profit</span>

                <h4 class="fw-bold {{ ($metrics['net_profit'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }} mb-0 font-monospace bn-digit">
                    ৳{{ number_format($metrics['net_profit'] ?? 0, 2) }}
                </h4>

                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-muted" style="font-size: 10px;">
                        Gross: ৳{{ number_format($metrics['gross_profit'] ?? 0, 2) }}
                    </small>
                    <small class="text-muted" style="font-size: 10px;">
                        COGS: ৳{{ number_format($metrics['cogs'] ?? 0, 2) }}
                    </small>
                </div>
            </div>
        </div>

        <!-- Collections & Receivables -->
        <div class="col-6 col-md-3">
            <div class="metric-card-box border-start border-info border-3">
                <i class="fas fa-hand-holding-usd metric-icon-bg text-info"></i>

                <span class="text-muted d-block small" style="font-size: 11px;">Total Realized Revenue</span>

                <h4 class="fw-bold text-dark mb-0 font-monospace bn-digit">
                    ৳{{ number_format($metrics['total_paid'] ?? 0, 2) }}
                </h4>

                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-muted" style="font-size: 10px;">
                        Collected: ৳{{ number_format($metrics['total_paid'] ?? 0, 2) }}
                    </small>
                    <small class="text-danger fw-semibold" style="font-size: 10px;">
                        Due: ৳{{ number_format($metrics['total_due'] ?? 0, 2) }}
                    </small>
                </div>
            </div>
        </div>

        <!-- Branch Inventory Asset Value -->
        <div class="col-6 col-md-3">
            <div class="metric-card-box border-start border-warning border-3">
                <i class="fas fa-boxes-stacked metric-icon-bg text-warning"></i>

                <span class="text-muted d-block small" style="font-size: 11px;">Inventory Stock Value (Cost)</span>

                <h4 class="fw-bold text-dark mb-0 font-monospace bn-digit">
                    ৳{{ number_format($metrics['inventory_value'] ?? 0, 2) }}
                </h4>

                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="text-warning fw-semibold" style="font-size: 10px;">
                        {{ number_format($metrics['current_stock_qty'] ?? 0) }} Physical Units
                    </small>
                    <small class="text-muted" style="font-size: 10px;">In Branch</small>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Payment Channels & Operational Outflow -->
    <div class="row g-2 mb-3">

        <!-- Cash vs Digital Collection -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card report-panel border shadow-xs h-100">

                <h6 class="report-panel-heading report-section-title">
                    <i class="fas fa-money-bill-wave text-success"></i>
                    Cash vs Digital Collections
                </h6>

                @php
                    $totRealized = max(0.01, (float)($metrics['total_paid'] ?? 0));
                    $cashPercent = round(((float)($metrics['cash_collection'] ?? 0) / $totRealized) * 100);
                    $digitalPercent = max(0, 100 - $cashPercent);
                @endphp

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center small text-muted mb-1" style="font-size: 11px;">
                        <span>Cash at Counter (৳{{ number_format($metrics['cash_collection'] ?? 0, 2) }})</span>
                        <strong class="font-monospace text-dark">{{ $cashPercent }}%</strong>
                    </div>

                    <div class="progress" style="height: 7px;">
                        <div class="progress-bar bg-success" style="width: {{ $cashPercent }}%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center small text-muted mb-1" style="font-size: 11px;">
                        <span>Digital Collection (৳{{ number_format($metrics['digital_collection'] ?? 0, 2) }})</span>
                        <strong class="font-monospace text-primary">{{ $digitalPercent }}%</strong>
                    </div>

                    <div class="progress" style="height: 7px;">
                        <div class="progress-bar bg-primary" style="width: {{ $digitalPercent }}%"></div>
                    </div>
                </div>

                <div class="row g-1 pt-3 mt-auto border-top text-center" style="font-size: 11px;">
                    <div class="col-4 border-end">
                        <small class="text-muted d-block mb-1" style="font-size: 10px;">bKash</small>
                        <strong class="font-monospace text-dark">৳{{ number_format($metrics['bkash_collection'] ?? 0, 0) }}</strong>
                    </div>

                    <div class="col-4 border-end">
                        <small class="text-muted d-block mb-1" style="font-size: 10px;">Nagad</small>
                        <strong class="font-monospace text-dark">৳{{ number_format($metrics['nagad_collection'] ?? 0, 0) }}</strong>
                    </div>

                    <div class="col-4">
                        <small class="text-muted d-block mb-1" style="font-size: 10px;">Bank / Card</small>
                        <strong class="font-monospace text-dark">৳{{ number_format($metrics['bank_collection'] ?? 0, 0) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cost & Expense Deductions -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card report-panel border shadow-xs h-100">

                <h6 class="report-panel-heading report-section-title">
                    <i class="fas fa-receipt text-danger"></i>
                    Cost &amp; Expense Deductions
                </h6>

                <ul class="report-data-list list-unstyled mb-0">
                    <li class="d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted">Total COGS (Product Cost):</span>
                        <strong class="font-monospace text-dark">৳{{ number_format($metrics['cogs'] ?? 0, 2) }}</strong>
                    </li>

                    <li class="d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted">Store Operating Expenses:</span>
                        <strong class="font-monospace text-danger">৳{{ number_format($metrics['total_expenses'] ?? 0, 2) }}</strong>
                    </li>

                    <li class="d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted">Discounts Given to Customers:</span>
                        <strong class="font-monospace text-muted">৳{{ number_format($metrics['total_discount'] ?? 0, 2) }}</strong>
                    </li>

                    <li class="d-flex justify-content-between align-items-center border-bottom">
                        <span class="text-muted">Govt. Tax &amp; VAT Collected:</span>
                        <strong class="font-monospace text-primary">৳{{ number_format($metrics['total_tax'] ?? 0, 2) }}</strong>
                    </li>

                    <li class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Average Order Value (AOV):</span>
                        <strong class="font-monospace text-dark">৳{{ number_format($metrics['average_order_value'] ?? 0, 2) }}</strong>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Top Selling Products -->
        <div class="col-12 col-lg-4">
            <div class="card report-panel border shadow-xs h-100">

                <h6 class="report-panel-heading report-section-title">
                    <i class="fas fa-trophy text-warning"></i>
                    Top Selling Products
                </h6>

                <div class="table-responsive">
                    <table class="table table-sm table-borderless top-products-table" style="font-size: 11px;">
                        <tbody>
                            @forelse($topProducts as $idx => $tp)
                                <tr class="border-bottom">

                                    <td class="py-2" style="width: 30px;">
                                        <span class="badge {{ $idx == 0 ? 'bg-warning text-dark' : 'bg-light text-muted border' }} font-monospace" style="font-size: 9px; padding: 3px 5px;">
                                            #{{ $idx + 1 }}
                                        </span>
                                    </td>

                                    <td class="py-2">
                                        <div class="product-name fw-semibold text-dark">
                                            {{ $tp->name ?? ($tp->title ?? 'Product') }}
                                        </div>
                                        <small class="text-muted font-monospace bn-digit" style="font-size: 9.5px;">
                                            {{ $tp->sku ?? '—' }}
                                        </small>
                                    </td>

                                    <td class="py-2 text-center font-monospace bn-digit text-muted text-nowrap">
                                        {{ $tp->total_sold_qty }} pcs
                                    </td>

                                    <td class="py-2 text-end font-monospace bn-digit text-primary fw-bold text-nowrap">
                                        ৳{{ number_format($tp->total_sold_amount, 0) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="report-empty-state" style="font-size: 11px;">
                                        No sales records found for this period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Recent Orders -->
    <div class="card border shadow-xs bg-white p-3">

        <div class="report-bottom-heading d-flex justify-content-between align-items-center border-bottom pb-3 mb-2">
            <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">
                <i class="fas fa-history text-secondary me-1"></i>
                Recent Orders (Last 10)
            </h6>

            <a href="{{ route('admin.branch_report.sales', ['branch_id' => $branch->id]) }}"
               class="text-primary text-decoration-none fw-semibold"
               style="font-size: 11px;">
                View Full Sales Report &rarr;
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle recent-orders-table" style="font-size: 11.5px;">

                <thead class="table-light">
                    <tr>
                        <th class="py-2">Invoice #</th>
                        <th class="py-2">Date &amp; Time</th>
                        <th class="py-2">Customer</th>
                        <th class="py-2">Sales Person</th>
                        <th class="py-2 text-center">Channel</th>
                        <th class="py-2 text-end">Amount</th>
                        <th class="py-2 text-end">Paid</th>
                        <th class="py-2 text-center">Payment</th>
                        <th class="py-2 text-center">Status</th>
                        <th class="py-2 text-end">Print</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($recentOrders as $order)
                        <tr>
                            <td class="py-2">
                                <a href="{{ \Illuminate\Support\Facades\Route::has('admin.order.view') ? route('admin.order.view', $order->id) : url('admin/orders/' . $order->id) }}"
                                   class="fw-bold font-monospace bn-digit text-primary text-decoration-none">
                                    {{ $order->invoice_no ?? ('#' . $order->id) }}
                                </a>
                            </td>

                            <td class="py-2 font-monospace text-muted text-nowrap" style="font-size: 10.5px;">
                                {{ $order->created_at->format('d M Y, h:i A') }}
                            </td>

                            <td class="py-2">
                                <div class="fw-semibold text-dark">
                                    {{ $order->user?->name ?? 'Walk-in Customer' }}
                                </div>
                                <small class="text-muted font-monospace bn-digit">
                                    {{ $order->user?->phone ?? 'N/A' }}
                                </small>
                            </td>

                            <td class="py-2">
                                <span class="badge bg-light text-dark border font-monospace report-table-badge">
                                    {{ $order->salesPerson?->name ?? 'Cashier' }}
                                </span>
                            </td>

                            <td class="py-2 text-center">
                                <span class="badge {{ ($order->order_type ?? '') === 'pos' ? 'bg-warning text-dark' : 'bg-info text-white' }} report-table-badge">
                                    {{ strtoupper($order->order_type ?? 'ONLINE') }}
                                </span>
                            </td>

                            <td class="py-2 text-end font-monospace bn-digit fw-bold text-dark text-nowrap">
                                ৳{{ number_format($order->grand_total, 2) }}
                            </td>

                            <td class="py-2 text-end font-monospace bn-digit fw-bold text-success text-nowrap">
                                ৳{{ number_format($order->paid_amount, 2) }}
                            </td>

                            <td class="py-2 text-center">
                                <span class="badge bg-light text-dark border font-monospace report-table-badge">
                                    {{ strtoupper($order->payment_method) }}
                                </span>
                            </td>

                            <td class="py-2 text-center">
                                @if(($order->order_status ?? '') === 'delivered')
                                    <span class="badge bg-success report-table-badge">Delivered</span>
                                @elseif(($order->order_status ?? '') === 'cancelled')
                                    <span class="badge bg-danger report-table-badge">Cancelled</span>
                                @else
                                    <span class="badge bg-primary report-table-badge">
                                        {{ ucfirst($order->order_status ?? 'completed') }}
                                    </span>
                                @endif
                            </td>

                            <td class="py-2 text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.pos.print.thermal') ? route('admin.pos.print.thermal', $order->id) : url('admin/pos/print-thermal/' . $order->id) }}"
                                       target="_blank"
                                       class="btn btn-light border text-dark report-action-btn"
                                       title="Thermal Receipt">
                                        <i class="fas fa-receipt"></i>
                                    </a>

                                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.pos.print.a5') ? route('admin.pos.print.a5', $order->id) : url('admin/pos/print-a5/' . $order->id) }}"
                                       target="_blank"
                                       class="btn btn-light border text-primary report-action-btn"
                                       title="A5 Memo">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="report-empty-state">
                                No recent sales found for this branch.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
