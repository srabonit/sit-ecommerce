
@extends('layouts.admin')

@section('title', 'Cash Flow Statement - ' . config('app.name'))

@section('admin_content')
<style>
    /* =========================================
       CASH FLOW — KAIADMIN CLASSIC
    ========================================= */

    #cash-flow-root {
        --cf-primary: #1572e8;
        --cf-primary-soft: #eaf2ff;
        --cf-success: #31ce36;
        --cf-danger: #f25961;
        --cf-warning: #ffad46;
        --cf-info: #48abf7;
        --cf-purple: #6861ce;
        --cf-text: #2a2f5b;
        --cf-muted: #8d9498;
        --cf-border: #ebedf2;
        --cf-light: #f8f9fa;

        min-width: 0;
        width: 100%;
        color: var(--cf-text);
        box-sizing: border-box;
    }

    #cash-flow-root *,
    #cash-flow-root *::before,
    #cash-flow-root *::after {
        box-sizing: border-box;
    }

    /* Radius consistency */
    #cash-flow-root .btn,
    #cash-flow-root button,
    #cash-flow-root .badge,
    #cash-flow-root .alert,
    #cash-flow-root .form-control,
    #cash-flow-root .form-select,
    #cash-flow-root .card,
    #cash-flow-root .report-card,
    #cash-flow-root .kpi-stat-card,
    #cash-flow-root .collapse-toggle-btn {
        border-radius: 5px !important;
    }

    #cash-flow-root .input-group > .input-group-text {
        border-radius: 5px 0 0 5px !important;
    }

    #cash-flow-root .input-group > .form-control,
    #cash-flow-root .input-group > .form-select {
        border-radius: 0 5px 5px 0 !important;
    }

    #cash-flow-root .bn-digit,
    #cash-flow-root .font-monospace {
        font-variant-numeric: tabular-nums;
    }

    /* =========================================
       HEADER
    ========================================= */

    #cash-flow-root .header-top-wrap {
        min-width: 0;
    }

    #cash-flow-root .page-heading {
        min-width: 0;
    }

    #cash-flow-root .page-title {
        display: flex;
        align-items: center;
        gap: 9px;
        color: var(--cf-text);
        font-size: 18px;
        line-height: 1.35;
        font-weight: 700;
        margin: 0 0 4px;
    }

    #cash-flow-root .page-title-icon {
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--cf-primary-soft);
        color: var(--cf-primary);
        border-radius: 5px;
        font-size: 15px;
    }

    #cash-flow-root .page-subtitle {
        color: var(--cf-muted);
        font-size: 11px;
        line-height: 1.5;
        padding-left: 41px;
    }

    #cash-flow-root .top-action-btns {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 7px;
    }

    #cash-flow-root .btn-compact {
        min-height: 34px;
        padding: 6px 11px !important;
        font-size: 11.5px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        white-space: nowrap;
        transition: all .15s ease;
    }

    #cash-flow-root .btn-compact:hover {
        transform: translateY(-1px);
    }

    /* =========================================
       REPORT CARDS
    ========================================= */

    #cash-flow-root .report-card {
        min-width: 0;
        margin-bottom: 14px;
        background: #fff;
        border: 1px solid var(--cf-border) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035) !important;
        overflow: hidden;
    }

    #cash-flow-root .report-card-header {
        min-height: 46px;
        padding: 10px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        background: #fff;
        border-bottom: 1px solid var(--cf-border);
    }

    #cash-flow-root .report-card-header h6 {
        color: var(--cf-text);
        font-size: 13px;
        font-weight: 700;
        margin: 0;
    }

    #cash-flow-root .section-heading-icon {
        color: var(--cf-primary);
        font-size: 14px;
    }

    /* =========================================
       COLLAPSE BUTTON
    ========================================= */

    #cash-flow-root .collapse-toggle-btn {
        width: 28px;
        height: 28px;
        flex: 0 0 28px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        background: #fff;
        border: 1px solid var(--cf-border);
        transition: all .2s ease;
    }

    #cash-flow-root .collapse-toggle-btn:hover {
        background: var(--cf-primary);
        border-color: var(--cf-primary);
        color: #fff;
    }

    #cash-flow-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    #cash-flow-root .collapse-icon {
        font-size: 10px;
        transition: transform .25s ease;
    }

    /* =========================================
       FILTER PANEL
    ========================================= */

    #cash-flow-root .filter-card-body {
        padding: 14px;
    }

    #cash-flow-root .form-label {
        display: block;
        margin-bottom: 5px;
        color: #525f7f;
        font-size: 11px;
        font-weight: 700;
    }

    #cash-flow-root .form-control,
    #cash-flow-root .form-select {
        width: 100%;
        min-width: 0;
        min-height: 36px;
        padding: 6px 10px;
        color: #2a2f5b;
        background-color: #fff;
        border: 1px solid #e3e6ed;
        box-shadow: none;
        font-size: 12px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #cash-flow-root .form-control:focus,
    #cash-flow-root .form-select:focus {
        border-color: var(--cf-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
        outline: none;
    }

    #cash-flow-root .filter-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 7px;
    }

    /* =========================================
       KPI SUMMARY
    ========================================= */

    #cash-flow-root .kpi-wrapper {
        padding: 14px;
        background: #fbfcfe;
    }

    #cash-flow-root .kpi-stat-card {
        height: 100%;
        min-width: 0;
        position: relative;
        overflow: hidden;
        padding: 14px;
        background: #fff;
        border: 1px solid var(--cf-border);
        box-shadow: 0 1px 3px rgba(0, 0, 0, .025);
        transition: box-shadow .2s ease, transform .2s ease;
    }

    #cash-flow-root .kpi-stat-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, .055);
        transform: translateY(-2px);
    }

    #cash-flow-root .kpi-stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: 3px;
    }

    #cash-flow-root .kpi-cfo::before {
        background: var(--cf-success);
    }

    #cash-flow-root .kpi-cfi::before {
        background: var(--cf-danger);
    }

    #cash-flow-root .kpi-cff::before {
        background: var(--cf-purple);
    }

    #cash-flow-root .kpi-net::before {
        background: var(--cf-primary);
    }

    #cash-flow-root .kpi-label {
        display: block;
        margin-bottom: 7px;
        color: #8d9498;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .035em;
        line-height: 1.5;
        text-transform: uppercase;
    }

    #cash-flow-root .kpi-value {
        margin: 0;
        font-size: clamp(14px, 1.45vw, 19px);
        font-weight: 700;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    #cash-flow-root .kpi-note {
        display: block;
        margin-top: 5px;
        color: #9ba2a8;
        font-size: 10px;
        line-height: 1.4;
    }

    /* =========================================
       STATEMENT TABLE
    ========================================= */

    #cash-flow-root .table-responsive {
        width: 100%;
        min-width: 0;
    }

    #cash-flow-root .pc-fit-container {
        width: 100%;
    }

    #cash-flow-root .statement-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #cash-flow-root .statement-table thead th {
        padding: 11px 12px;
        color: #6b7280;
        background: #f8f9fa;
        border-bottom: 1px solid #ebedf2;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .045em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    #cash-flow-root .statement-table tbody td {
        padding: 10px 12px;
        color: #525f7f;
        background: #fff;
        border-bottom: 1px solid #f0f1f5;
        font-size: 12px;
        vertical-align: middle;
    }

    #cash-flow-root .statement-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #cash-flow-root .statement-table tbody tr:not(.statement-section-header):hover td {
        background-color: #fbfcff;
    }

    #cash-flow-root .statement-section-header td {
        padding: 10px 12px;
        color: #525f7f;
        background: #f7f8fa;
        border-top: 1px solid #ebedf2;
        border-bottom: 1px solid #ebedf2;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .035em;
        text-transform: uppercase;
    }

    #cash-flow-root .statement-section-header:first-child td {
        border-top: 0;
    }

    #cash-flow-root .statement-table .particular-title {
        display: block;
        color: #2a2f5b;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.45;
    }

    #cash-flow-root .statement-table .particular-note {
        display: block;
        margin-top: 3px;
        color: #9ba2a8;
        font-size: 10px;
        font-weight: 400;
        line-height: 1.45;
    }

    #cash-flow-root .statement-table .impact-badge {
        display: inline-block;
        padding: 4px 7px;
        font-size: 9.5px;
        font-weight: 600;
        white-space: nowrap;
    }

    #cash-flow-root .statement-table .bg-light td {
        background: #f8f9fa !important;
    }

    #cash-flow-root .statement-table .border-top {
        border-top: 2px solid #e3e6ed !important;
    }

    #cash-flow-root .statement-table .col-cf-impact {
        text-align: center;
    }

    #cash-flow-root .statement-table .col-cf-amount {
        white-space: nowrap;
        font-size: 11.5px;
    }

    #cash-flow-root .period-badge {
        max-width: 240px;
        padding: 5px 9px;
        overflow: hidden;
        color: #64748b !important;
        background: #f8f9fa !important;
        border: 1px solid var(--cf-border) !important;
        font-size: 10px;
        font-weight: 600;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* =========================================
       DESKTOP
    ========================================= */

    @media (min-width: 992px) {
        #cash-flow-root .statement-table {
            table-layout: fixed;
        }

        #cash-flow-root .pc-fit-container {
            overflow-x: hidden;
        }

        #cash-flow-root .statement-table th,
        #cash-flow-root .statement-table td {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #cash-flow-root .col-cf-title {
            width: 60%;
        }

        #cash-flow-root .col-cf-impact {
            width: 18%;
        }

        #cash-flow-root .col-cf-amount {
            width: 22%;
        }
    }

    /* =========================================
       TABLET
    ========================================= */

    @media (max-width: 991.98px) {
        #cash-flow-root .pc-fit-container {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        #cash-flow-root .statement-table {
            min-width: 620px;
            table-layout: auto;
        }

        #cash-flow-root .statement-table th,
        #cash-flow-root .statement-table td {
            overflow: visible;
            text-overflow: clip;
        }
    }

    /* =========================================
       MOBILE
    ========================================= */

    @media (max-width: 767.98px) {
        #cash-flow-root {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #cash-flow-root .header-top-wrap {
            align-items: stretch !important;
            gap: 12px !important;
        }

        #cash-flow-root .page-title {
            font-size: 16px;
        }

        #cash-flow-root .page-title-icon {
            width: 30px;
            height: 30px;
            flex-basis: 30px;
            font-size: 13px;
        }

        #cash-flow-root .page-subtitle {
            padding-left: 39px;
            font-size: 10.5px;
        }

        #cash-flow-root .top-action-btns {
            width: 100%;
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 6px !important;
        }

        #cash-flow-root .top-action-btns .btn {
            width: 100%;
            min-width: 0;
            min-height: 34px;
            padding: 6px 5px !important;
            font-size: 10.5px !important;
        }

        #cash-flow-root .filter-card-body {
            padding: 11px;
        }

        #cash-flow-root .report-card-header {
            min-height: 42px;
            padding: 9px 10px;
        }

        #cash-flow-root .report-card-header h6 {
            font-size: 12px !important;
        }

        #cash-flow-root .kpi-wrapper {
            padding: 10px;
        }

        #cash-flow-root .kpi-stat-card {
            padding: 11px 10px;
        }

        #cash-flow-root .kpi-label {
            margin-bottom: 5px;
            font-size: 9px;
        }

        #cash-flow-root .kpi-value {
            font-size: 14px;
        }

        #cash-flow-root .kpi-note {
            font-size: 9px;
        }

        #cash-flow-root .statement-table {
            min-width: 560px !important;
        }

        #cash-flow-root .statement-table thead th {
            padding: 9px;
            font-size: 9.5px;
        }

        #cash-flow-root .statement-table tbody td {
            padding: 9px;
        }

        #cash-flow-root .statement-table .particular-title {
            font-size: 11.5px;
        }

        #cash-flow-root .statement-table .particular-note {
            font-size: 9.5px;
        }

        #cash-flow-root .period-badge {
            max-width: 130px;
        }

        #cash-flow-root .filter-actions {
            width: 100%;
        }

        #cash-flow-root .filter-actions .btn {
            flex: 1 1 auto;
        }
    }

    @media (max-width: 380px) {
        #cash-flow-root .kpi-stat-card {
            padding: 9px 8px;
        }

        #cash-flow-root .kpi-label {
            font-size: 8.5px;
        }

        #cash-flow-root .kpi-value {
            font-size: 12px;
        }

        #cash-flow-root .kpi-note {
            font-size: 8.5px;
        }

        #cash-flow-root .top-action-btns .btn {
            font-size: 10px !important;
        }

        #cash-flow-root .period-badge {
            max-width: 105px;
        }
    }

    /* =========================================
       PRINT
    ========================================= */

    @media print {
        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        body {
            background: #fff !important;
            color: #000 !important;
        }

        #cash-flow-root .no-print,
        .admin-dark-sidebar,
        .admin-header,
        .main-footer {
            display: none !important;
        }

        #cash-flow-root {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            color: #000 !important;
        }

        #cash-flow-root .report-card {
            margin-bottom: 10px !important;
            border: 1px solid #000 !important;
            box-shadow: none !important;
            break-inside: avoid;
        }

        #cash-flow-root .report-card-header,
        #cash-flow-root .kpi-stat-card,
        #cash-flow-root .statement-section-header td,
        #cash-flow-root .statement-table .bg-light td {
            background: #fff !important;
            color: #000 !important;
        }

        #cash-flow-root .collapse:not(.show) {
            display: block !important;
            height: auto !important;
        }

        #cash-flow-root .pc-fit-container {
            overflow: visible !important;
        }

        #cash-flow-root .statement-table {
            width: 100% !important;
            min-width: 0 !important;
            table-layout: fixed !important;
        }

        #cash-flow-root .statement-table th,
        #cash-flow-root .statement-table td {
            overflow: visible !important;
            text-overflow: clip !important;
            color: #000 !important;
            border-color: #ccc !important;
        }

        #cash-flow-root .statement-table .particular-title,
        #cash-flow-root .statement-table .particular-note {
            color: #000 !important;
        }

        #cash-flow-root .kpi-stat-card::before {
            display: none !important;
        }

        #cash-flow-root .kpi-value,
        #cash-flow-root .kpi-label,
        #cash-flow-root .kpi-note {
            color: #000 !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="cash-flow-root">

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 header-top-wrap no-print">
        <div class="page-heading">
            <h5 class="page-title">
                <span class="page-title-icon">
                    <i class="fas fa-money-bill-trend-up"></i>
                </span>
                <span>Cash Flow Statement</span>
            </h5>

            <small class="page-subtitle d-block">
                Cash movements by activity (IAS 7)
            </small>
        </div>

        <div class="top-action-btns d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-dark btn-compact shadow-xs" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>

            <a href="{{ route('admin.report.export_cash_flow') }}?{{ http_build_query(request()->all()) }}"
               class="btn btn-sm btn-outline-success btn-compact shadow-xs">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>

            <a href="{{ route('admin.report.profit_loss') }}"
               class="btn btn-sm btn-outline-primary btn-compact shadow-xs">
                <i class="fas fa-file-invoice-dollar"></i> Profit &amp; Loss
            </a>

            <a href="{{ route('admin.report.balance_sheet') }}"
               class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-scale-balanced"></i> Balance Sheet
            </a>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card border-0 shadow-sm report-card mb-3 no-print">
        <div class="filter-card-body">
            <form action="{{ route('admin.report.cash_flow') }}" method="GET" class="row g-2 align-items-end">

                <div class="col-12 col-md-4">
                    <label class="form-label">Period</label>
                    <select name="date_filter" id="cashFlowDateFilter"
                            class="form-select font-monospace"
                            onchange="toggleCustomCashFlowDates(this.value)">
                        <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ $dateFilter === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="this_week" {{ $dateFilter === 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="this_month" {{ $dateFilter === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ $dateFilter === 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="this_year" {{ $dateFilter === 'this_year' ? 'selected' : '' }}>This Year</option>
                        <option value="custom" {{ $dateFilter === 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>

                <div class="col-6 col-md-3 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="cfStartCol">
                    <label class="form-label">From</label>
                    <input type="date" name="start_date"
                           class="form-control font-monospace bn-digit"
                           value="{{ request('start_date', $start->format('Y-m-d')) }}">
                </div>

                <div class="col-6 col-md-3 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="cfEndCol">
                    <label class="form-label">To</label>
                    <input type="date" name="end_date"
                           class="form-control font-monospace bn-digit"
                           value="{{ request('end_date', $end->format('Y-m-d')) }}">
                </div>

                <div class="col-12 col-md-auto">
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-dark btn-compact shadow-xs fw-bold px-3">
                            <i class="fas fa-filter"></i> Apply
                        </button>

                        @if(request()->filled('date_filter') && request('date_filter') !== 'this_month')
                            <a href="{{ route('admin.report.cash_flow') }}"
                               class="btn btn-light border btn-compact shadow-xs">Reset</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Summary -->
    <div class="report-card mb-3">
        <div class="report-card-header">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-chart-pie section-heading-icon"></i>
                <h6>Cash Flow Summary</h6>
            </div>

            <button type="button" class="collapse-toggle-btn shadow-xs no-print"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseCashFlowKpis"
                    aria-expanded="true"
                    aria-controls="collapseCashFlowKpis"
                    title="Toggle">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseCashFlowKpis">
            <div class="kpi-wrapper">
                <div class="row g-2">

                    <div class="col-6 col-lg-3">
                        <div class="kpi-stat-card kpi-cfo shadow-xs">
                            <span class="kpi-label">Operating (CFO)</span>
                            <h5 class="kpi-value font-monospace bn-digit {{ $cashFlowSummary['net_operating_cash'] >= 0 ? 'text-success' : 'text-danger' }}">
                                ৳ {{ number_format($cashFlowSummary['net_operating_cash'], 2) }}
                            </h5>
                            <small class="kpi-note">Business operations</small>
                        </div>
                    </div>

                    <div class="col-6 col-lg-3">
                        <div class="kpi-stat-card kpi-cfi shadow-xs">
                            <span class="kpi-label">Investing (CFI)</span>
                            <h5 class="kpi-value font-monospace bn-digit text-danger">
                                ৳ {{ number_format($cashFlowSummary['net_investing_cash'], 2) }}
                            </h5>
                            <small class="kpi-note">Asset purchases</small>
                        </div>
                    </div>

                    <div class="col-6 col-lg-3">
                        <div class="kpi-stat-card kpi-cff shadow-xs">
                            <span class="kpi-label">Financing (CFF)</span>
                            <h5 class="kpi-value font-monospace bn-digit" style="color: #6366f1;">
                                ৳ {{ number_format($cashFlowSummary['net_financing_cash'], 2) }}
                            </h5>
                            <small class="kpi-note">Owner &amp; investor funds</small>
                        </div>
                    </div>

                    <div class="col-6 col-lg-3">
                        <div class="kpi-stat-card kpi-net shadow-xs">
                            <span class="kpi-label">Closing Cash</span>
                            <h5 class="kpi-value font-monospace bn-digit text-primary">
                                ৳ {{ number_format($cashFlowSummary['closing_balance'], 2) }}
                            </h5>
                            <small class="kpi-note text-primary fw-bold">Bank &amp; cash</small>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Cash Flow Statement -->
    <div class="card border-0 shadow-sm report-card">
        <div class="report-card-header">
            <h6 class="d-flex align-items-center gap-2">
                <i class="fas fa-file-invoice-dollar section-heading-icon"></i>
                <span>Cash Flow Statement</span>
            </h6>

            <div class="d-flex align-items-center gap-2">
                <span class="badge period-badge font-monospace">
                    {{ $cashFlowSummary['period_label'] }}
                </span>

                <button type="button" class="collapse-toggle-btn shadow-xs no-print"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseStatementTable"
                        aria-expanded="true"
                        aria-controls="collapseStatementTable"
                        title="Toggle">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseStatementTable">
            <div class="card-body p-0">
                <div class="table-responsive pc-fit-container">
                    <table class="table align-middle statement-table mb-0">
                        <thead>
                            <tr>
                                <th class="col-cf-title ps-3">Particulars</th>
                                <th class="col-cf-impact text-center">Impact</th>
                                <th class="col-cf-amount text-end pe-3">Amount (৳)</th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- Operating Activities -->
                            <tr class="statement-section-header">
                                <td colspan="3">
                                    <i class="fas fa-cart-shopping me-1 text-success"></i>
                                    1. Operating Activities
                                </td>
                            </tr>

                            <tr>
                                <td class="ps-3 col-cf-title">
                                    <strong class="particular-title">Customer Cash Received</strong>
                                    <small class="particular-note">Delivered sales &amp; delivery charges</small>
                                </td>
                                <td class="text-center col-cf-impact">
                                    <span class="badge bg-success-subtle text-success border font-monospace impact-badge">Inflow (+)</span>
                                </td>
                                <td class="text-end pe-3 col-cf-amount font-monospace bn-digit text-success">
                                    +৳ {{ number_format($cashFlowSummary['customer_cash_received'], 2) }}
                                </td>
                            </tr>

                            <tr>
                                <td class="ps-3 col-cf-title">
                                    <strong class="particular-title">Other Operating Income</strong>
                                    <small class="particular-note">Commissions, scrap sales &amp; other income</small>
                                </td>
                                <td class="text-center col-cf-impact">
                                    <span class="badge bg-success-subtle text-success border font-monospace impact-badge">Inflow (+)</span>
                                </td>
                                <td class="text-end pe-3 col-cf-amount font-monospace bn-digit text-success">
                                    +৳ {{ number_format($cashFlowSummary['operating_inflows'], 2) }}
                                </td>
                            </tr>

                            <tr>
                                <td class="ps-3 col-cf-title">
                                    <strong class="particular-title">Courier &amp; Shipping Costs</strong>
                                    <small class="particular-note">Courier and delivery payments</small>
                                </td>
                                <td class="text-center col-cf-impact">
                                    <span class="badge bg-danger-subtle text-danger border font-monospace impact-badge">Outflow (-)</span>
                                </td>
                                <td class="text-end pe-3 col-cf-amount font-monospace bn-digit text-danger">
                                    -৳ {{ number_format($cashFlowSummary['courier_cost_paid'], 2) }}
                                </td>
                            </tr>

                            <tr>
                                <td class="ps-3 col-cf-title">
                                    <strong class="particular-title">Operating Expenses</strong>
                                    <small class="particular-note">Rent, ads, utilities &amp; staff costs</small>
                                </td>
                                <td class="text-center col-cf-impact">
                                    <span class="badge bg-danger-subtle text-danger border font-monospace impact-badge">Outflow (-)</span>
                                </td>
                                <td class="text-end pe-3 col-cf-amount font-monospace bn-digit text-danger">
                                    -৳ {{ number_format($cashFlowSummary['operating_expenses_paid'], 2) }}
                                </td>
                            </tr>

                            <tr class="bg-light fw-bold">
                                <td class="ps-3 text-secondary" colspan="2">Net Operating Cash (CFO)</td>
                                <td class="text-end pe-3 font-monospace bn-digit {{ $cashFlowSummary['net_operating_cash'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    ৳ {{ number_format($cashFlowSummary['net_operating_cash'], 2) }}
                                </td>
                            </tr>

                            <!-- Investing Activities -->
                            <tr class="statement-section-header">
                                <td colspan="3">
                                    <i class="fas fa-building me-1 text-danger"></i>
                                    2. Investing Activities
                                </td>
                            </tr>

                            <tr>
                                <td class="ps-3 col-cf-title">
                                    <strong class="particular-title">Fixed Assets &amp; Equipment</strong>
                                    <small class="particular-note">Electronics, furniture &amp; machinery</small>
                                </td>
                                <td class="text-center col-cf-impact">
                                    <span class="badge bg-danger-subtle text-danger border font-monospace impact-badge">Outflow (-)</span>
                                </td>
                                <td class="text-end pe-3 col-cf-amount font-monospace bn-digit text-danger">
                                    -৳ {{ number_format($cashFlowSummary['asset_purchases'], 2) }}
                                </td>
                            </tr>

                            <tr class="bg-light fw-bold">
                                <td class="ps-3 text-secondary" colspan="2">Net Investing Cash (CFI)</td>
                                <td class="text-end pe-3 font-monospace bn-digit text-danger">
                                    ৳ {{ number_format($cashFlowSummary['net_investing_cash'], 2) }}
                                </td>
                            </tr>

                            <!-- Financing Activities -->
                            <tr class="statement-section-header">
                                <td colspan="3">
                                    <i class="fas fa-hand-holding-dollar me-1" style="color: #6366f1;"></i>
                                    3. Financing Activities
                                </td>
                            </tr>

                            <tr>
                                <td class="ps-3 col-cf-title">
                                    <strong class="particular-title">Owner &amp; Investor Capital</strong>
                                    <small class="particular-note">Capital added to bank or cash accounts</small>
                                </td>
                                <td class="text-center col-cf-impact">
                                    <span class="badge bg-success-subtle text-success border font-monospace impact-badge">Inflow (+)</span>
                                </td>
                                <td class="text-end pe-3 col-cf-amount font-monospace bn-digit" style="color: #6366f1;">
                                    +৳ {{ number_format($cashFlowSummary['capital_injections'], 2) }}
                                </td>
                            </tr>

                            <tr class="bg-light fw-bold">
                                <td class="ps-3 text-secondary" colspan="2">Net Financing Cash (CFF)</td>
                                <td class="text-end pe-3 font-monospace bn-digit" style="color: #6366f1;">
                                    ৳ {{ number_format($cashFlowSummary['net_financing_cash'], 2) }}
                                </td>
                            </tr>

                            <!-- Reconciliation -->
                            <tr class="statement-section-header">
                                <td colspan="3">
                                    <i class="fas fa-scale-balanced me-1 text-primary"></i>
                                    4. Cash Reconciliation
                                </td>
                            </tr>

                            <tr>
                                <td class="ps-3 fw-bold text-dark" colspan="2">Net Cash Change</td>
                                <td class="text-end pe-3 font-monospace bn-digit fw-bold {{ $cashFlowSummary['net_change_in_cash'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $cashFlowSummary['net_change_in_cash'] >= 0 ? '+' : '' }}৳ {{ number_format($cashFlowSummary['net_change_in_cash'], 2) }}
                                </td>
                            </tr>

                            <tr>
                                <td class="ps-3 text-secondary" colspan="2">Opening Cash Balance</td>
                                <td class="text-end pe-3 font-monospace bn-digit text-muted">
                                    ৳ {{ number_format($cashFlowSummary['opening_balance'], 2) }}
                                </td>
                            </tr>

                            <tr class="bg-light border-top border-2 fw-bold" style="font-size: 12.5px;">
                                <td class="py-2 ps-3 text-dark" colspan="2">
                                    CLOSING CASH BALANCE
                                    <small class="d-block text-muted fw-normal" style="font-size: 10px;">Bank &amp; cash accounts</small>
                                </td>
                                <td class="text-end pe-3 py-2 font-monospace bn-digit text-success fw-bold" style="font-size: 13.5px;">
                                    ৳ {{ number_format($cashFlowSummary['closing_balance'], 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleCustomCashFlowDates(val) {
    const s = document.getElementById('cfStartCol');
    const e = document.getElementById('cfEndCol');

    if (val === 'custom') {
        s.classList.remove('d-none');
        e.classList.remove('d-none');
    } else {
        s.classList.add('d-none');
        e.classList.add('d-none');
    }
}
</script>
@endsection
