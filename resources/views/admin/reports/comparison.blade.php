
@extends('layouts.admin')

@section('title', 'Master Branch Comparison & Benchmarking')

@section('admin_content')
<style>
    /* =========================================================
       KAIADMIN LITE v1.2.0 — CLASSIC / WHITE CLASSIC
       Master Branch Comparison & Benchmarking
    ========================================================= */

    #branchComparisonRoot {
        --branch-primary: var(--theme-primary, #1572e8);
        --branch-success: var(--theme-success, #31ce36);
        --branch-danger: var(--theme-danger, #f25961);
        --branch-warning: var(--theme-warning, #ffad46);
        --branch-info: var(--theme-info, #48abf7);
        --branch-heading: #2a2f5b;
        --branch-text: #495057;
        --branch-muted: #8d9498;
        --branch-border: #ebecec;
        --branch-soft: #f8f9fa;
        --branch-white: #fff;
        --branch-radius: 5px;
        --branch-shadow: 0 1px 3px rgba(0, 0, 0, .04);

        width: 100%;
        min-width: 0;
        color: var(--branch-text);
        font-size: 13px;
    }

    #branchComparisonRoot,
    #branchComparisonRoot *,
    #branchComparisonRoot *::before,
    #branchComparisonRoot *::after {
        box-sizing: border-box;
    }

    /* Consistent Radius */

    #branchComparisonRoot .btn,
    #branchComparisonRoot button,
    #branchComparisonRoot .badge,
    #branchComparisonRoot .form-control,
    #branchComparisonRoot .form-select,
    #branchComparisonRoot .input-group-text,
    #branchComparisonRoot .card,
    #branchComparisonRoot .alert,
    #branchComparisonRoot .table,
    #branchComparisonRoot .table th,
    #branchComparisonRoot .table td,
    #branchComparisonRoot .progress,
    #branchComparisonRoot .benchmark-card-box,
    #branchComparisonRoot .date-preset-link,
    #branchComparisonRoot .section-card,
    #branchComparisonRoot .filter-panel,
    #branchComparisonRoot .comparison-header {
        border-radius: var(--branch-radius) !important;
    }

    /* Typography */

    #branchComparisonRoot .bn-digit,
    #branchComparisonRoot .font-monospace {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
        font-variant-numeric: tabular-nums;
        font-weight: 600;
    }

    #branchComparisonRoot .text-dark {
        color: var(--branch-heading) !important;
    }

    #branchComparisonRoot .text-muted {
        color: var(--branch-muted) !important;
    }

    #branchComparisonRoot h4,
    #branchComparisonRoot h6,
    #branchComparisonRoot p {
        margin-bottom: 0;
    }

    /* Buttons */

    #branchComparisonRoot .btn {
        font-weight: 600;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    #branchComparisonRoot .btn-compact {
        min-height: 33px;
        padding: 6px 11px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        font-size: 11.5px !important;
        font-weight: 600 !important;
        line-height: 1.3 !important;
        white-space: nowrap;
    }

    #branchComparisonRoot .btn:focus-visible,
    #branchComparisonRoot .date-preset-link:focus-visible {
        outline: 2px solid var(--branch-primary);
        outline-offset: 2px;
    }

    #branchComparisonRoot .btn-primary {
        background-color: var(--branch-primary);
        border-color: var(--branch-primary);
    }

    #branchComparisonRoot .btn-primary:hover {
        background-color: #1265d1;
        border-color: #1265d1;
    }

    #branchComparisonRoot .btn-outline-secondary {
        color: #686c71;
        border-color: #dfe1e3;
        background: #fff;
    }

    #branchComparisonRoot .btn-outline-secondary:hover {
        color: var(--branch-heading);
        background: #f3f4f6;
        border-color: #c9cccf;
    }

    #branchComparisonRoot .btn-light {
        color: var(--branch-heading);
        background: #fff;
        border-color: #dfe1e3 !important;
    }

    #branchComparisonRoot .btn-light:hover {
        background: #f8f9fa;
        border-color: #c9cccf !important;
    }

    /* Page Header */

    #branchComparisonRoot .comparison-header {
        min-width: 0;
        padding: 14px 16px;
        background: var(--branch-white);
        border: 1px solid var(--branch-border);
        box-shadow: var(--branch-shadow);
    }

    #branchComparisonRoot .header-meta {
        min-width: 0;
        gap: 9px;
    }

    #branchComparisonRoot .header-actions {
        flex-shrink: 0;
        gap: 6px;
    }

    #branchComparisonRoot .header-title {
        color: var(--branch-heading);
        font-size: 13px;
        font-weight: 700;
        line-height: 1.5;
    }

    #branchComparisonRoot .header-badge {
        display: inline-flex;
        align-items: center;
        min-height: 26px;
        padding: 5px 9px;
        font-size: 10.5px;
        font-weight: 600;
        line-height: 1.4;
        white-space: normal;
    }

    /* Filter Panel */

    #branchComparisonRoot .filter-panel {
        padding: 13px 15px;
        background: var(--branch-white);
        border: 1px solid var(--branch-border);
        box-shadow: var(--branch-shadow);
    }

    #branchComparisonRoot .preset-group {
        min-width: 0;
        gap: 6px;
    }

    #branchComparisonRoot .preset-label {
        color: var(--branch-muted);
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    #branchComparisonRoot .date-preset-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 29px;
        padding: 5px 10px;
        color: #475569;
        background: #fff;
        border: 1px solid #dfe1e3;
        font-size: 10.5px;
        font-weight: 600;
        line-height: 1.3;
        text-decoration: none;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #branchComparisonRoot .date-preset-link:hover {
        color: var(--branch-primary);
        border-color: var(--branch-primary);
        background: #f7faff;
    }

    #branchComparisonRoot .date-preset-link.active {
        color: #fff !important;
        background: var(--branch-primary) !important;
        border-color: var(--branch-primary) !important;
        box-shadow: 0 2px 5px rgba(21, 114, 232, .15);
    }

    #branchComparisonRoot .custom-date-form {
        min-width: 0;
        gap: 7px;
    }

    #branchComparisonRoot .custom-date-form .form-control {
        width: 132px;
        min-width: 0;
        min-height: 33px;
        padding: 5px 8px;
        color: var(--branch-text);
        background: #fff;
        border: 1px solid #ebedf2;
        font-size: 11px;
        box-shadow: none;
    }

    #branchComparisonRoot .custom-date-form .form-control:focus {
        border-color: var(--branch-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
    }

    #branchComparisonRoot .custom-label {
        color: var(--branch-muted);
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    /* KPI Cards */

    #branchComparisonRoot .benchmark-card-box {
        position: relative;
        height: 100%;
        min-width: 0;
        overflow: hidden;
        padding: 15px 14px;
        background: var(--branch-white);
        border: 1px solid var(--branch-border);
        box-shadow: var(--branch-shadow);
        transition: box-shadow .2s ease, transform .2s ease;
    }

    #branchComparisonRoot .benchmark-card-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, .07);
    }

    #branchComparisonRoot .benchmark-card-box.border-start {
        border-left-width: 3px !important;
    }

    #branchComparisonRoot .benchmark-icon-bg {
        position: absolute;
        top: 13px;
        right: 13px;
        font-size: 27px;
        opacity: .10;
        pointer-events: none;
    }

    #branchComparisonRoot .kpi-label {
        position: relative;
        display: block;
        max-width: calc(100% - 25px);
        min-height: 31px;
        color: var(--branch-muted);
        font-size: 10.5px;
        font-weight: 700;
        line-height: 1.5;
    }

    #branchComparisonRoot .kpi-value {
        position: relative;
        margin: 4px 0 0;
        font-size: clamp(14px, 1.3vw, 18px);
        font-weight: 700;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    #branchComparisonRoot .kpi-meta {
        min-width: 0;
        gap: 6px;
        flex-wrap: wrap;
        margin-top: 8px;
    }

    #branchComparisonRoot .kpi-meta small {
        font-size: 9.5px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    /* Section Cards */

    #branchComparisonRoot .section-card {
        min-width: 0;
        background: var(--branch-white);
        border: 1px solid var(--branch-border);
        box-shadow: var(--branch-shadow);
    }

    #branchComparisonRoot .section-heading {
        color: var(--branch-heading);
        font-size: 12.5px;
        font-weight: 700;
        line-height: 1.5;
    }

    /* Sales Contribution */

    #branchComparisonRoot .sales-share-item {
        height: 100%;
        min-width: 0;
        padding: 11px;
        background: #f9fafb;
        border: 1px solid var(--branch-border);
        border-radius: var(--branch-radius);
        transition: border-color .18s ease, background-color .18s ease;
    }

    #branchComparisonRoot .sales-share-item:hover {
        background: #fff;
        border-color: #d5e3f8;
    }

    #branchComparisonRoot .sales-share-name {
        min-width: 0;
        max-width: 75%;
        color: var(--branch-heading);
        font-size: 11.5px;
        font-weight: 700;
    }

    #branchComparisonRoot .sales-share-value {
        flex-shrink: 0;
        font-size: 11.5px;
        font-weight: 700;
    }

    #branchComparisonRoot .progress {
        height: 6px;
        overflow: hidden;
        background: #e9ecef;
    }

    #branchComparisonRoot .progress-bar {
        border-radius: 0 !important;
    }

    #branchComparisonRoot .sales-share-meta {
        gap: 8px;
        font-size: 10px;
        overflow-wrap: anywhere;
    }

    /* Comparison Table */

    #branchComparisonRoot .comparison-table-wrap {
        width: 100%;
        min-width: 0;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #cbd2da transparent;
    }

    #branchComparisonRoot .comparison-table {
        width: 100%;
        min-width: 1250px;
        margin: 0;
        border-collapse: collapse;
        vertical-align: middle;
    }

    #branchComparisonRoot .comparison-table thead th {
        padding: 11px 9px;
        color: #686c71;
        background: #f8f9fa;
        border-bottom: 1px solid var(--branch-border);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .15px;
        line-height: 1.5;
        white-space: nowrap;
        vertical-align: middle;
    }

    #branchComparisonRoot .comparison-table tbody td,
    #branchComparisonRoot .comparison-table tfoot td {
        padding: 10px 9px;
        color: var(--branch-text);
        border-bottom: 1px solid #f1f1f1;
        font-size: 11px;
        line-height: 1.5;
        vertical-align: middle;
        white-space: nowrap;
    }

    #branchComparisonRoot .comparison-table tbody tr {
        transition: background-color .15s ease;
    }

    #branchComparisonRoot .comparison-table tbody tr:hover {
        background: #f9fbfd;
    }

    #branchComparisonRoot .comparison-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #branchComparisonRoot .comparison-table tfoot td {
        padding-top: 12px;
        padding-bottom: 12px;
        background: #f8f9fa;
        border-top: 2px solid #dfe1e5;
        border-bottom: 0;
        font-weight: 700;
    }

    #branchComparisonRoot .comparison-table .branch-name-link {
        color: var(--branch-heading);
        font-weight: 700;
        text-decoration: none;
    }

    #branchComparisonRoot .comparison-table .branch-name-link:hover {
        color: var(--branch-primary);
    }

    #branchComparisonRoot .comparison-table .branch-code {
        display: block;
        margin-top: 3px;
        color: var(--branch-muted);
        font-size: 9.5px;
    }

    #branchComparisonRoot .comparison-table .badge {
        padding: 5px 7px;
        font-size: 9.5px;
        font-weight: 600;
    }

    #branchComparisonRoot .comparison-table .btn-group {
        display: inline-flex;
        gap: 3px;
    }

    #branchComparisonRoot .comparison-table .btn-group .btn {
        min-width: 28px;
        min-height: 27px;
        padding: 3px 7px !important;
        font-size: 10px !important;
    }

    #branchComparisonRoot .comparison-table .btn-group .btn:hover {
        transform: translateY(-1px);
    }

    #branchComparisonRoot .comparison-table .empty-state {
        padding: 32px 12px;
        color: var(--branch-muted);
        text-align: center;
        white-space: normal;
        line-height: 1.6;
    }

    /* General Layout */

    #branchComparisonRoot .card {
        min-width: 0;
        border-color: var(--branch-border) !important;
        box-shadow: var(--branch-shadow);
    }

    #branchComparisonRoot .row {
        min-width: 0;
    }

    #branchComparisonRoot .shadow-xs {
        box-shadow: var(--branch-shadow) !important;
    }

    /* Tablet */

    @media (max-width: 991.98px) {
        #branchComparisonRoot .comparison-header {
            align-items: flex-start !important;
        }

        #branchComparisonRoot .header-actions {
            flex-wrap: wrap;
            justify-content: flex-start;
        }

        #branchComparisonRoot .filter-panel > .d-flex {
            align-items: flex-start !important;
        }

        #branchComparisonRoot .custom-date-form {
            flex-wrap: wrap;
        }

        #branchComparisonRoot .kpi-value {
            font-size: 16px;
        }
    }

    /* Mobile */

    @media (max-width: 767.98px) {
        #branchComparisonRoot {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #branchComparisonRoot .comparison-header {
            flex-direction: column;
            align-items: stretch !important;
            gap: 12px;
            padding: 12px;
        }

        #branchComparisonRoot .header-meta {
            align-items: flex-start !important;
            gap: 7px;
        }

        #branchComparisonRoot .header-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
            gap: 6px;
        }

        #branchComparisonRoot .header-actions .btn {
            width: 100%;
            min-width: 0;
            padding: 7px 5px !important;
            font-size: 10.5px !important;
        }

        #branchComparisonRoot .filter-panel {
            padding: 11px;
        }

        #branchComparisonRoot .filter-panel > .d-flex {
            flex-direction: column;
            align-items: stretch !important;
        }

        #branchComparisonRoot .preset-group {
            width: 100%;
            gap: 6px;
        }

        #branchComparisonRoot .custom-date-form {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
            width: 100%;
            gap: 7px;
        }

        #branchComparisonRoot .custom-date-form .form-control {
            width: 100%;
            min-width: 0;
        }

        #branchComparisonRoot .custom-date-form .custom-label {
            grid-column: 1 / -1;
        }

        #branchComparisonRoot .custom-date-form button {
            grid-column: 1 / -1;
            width: 100%;
        }

        #branchComparisonRoot .benchmark-card-box {
            padding: 12px 10px;
        }

        #branchComparisonRoot .kpi-label {
            font-size: 9.5px;
            min-height: 28px;
        }

        #branchComparisonRoot .kpi-value {
            font-size: 14px;
        }

        #branchComparisonRoot .kpi-meta {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 4px;
        }

        #branchComparisonRoot .kpi-meta small {
            font-size: 9px;
        }

        #branchComparisonRoot .section-card {
            padding: 12px !important;
        }

        #branchComparisonRoot .section-heading {
            font-size: 12px;
        }

        #branchComparisonRoot .sales-share-item {
            padding: 9px;
        }

        #branchComparisonRoot .comparison-table {
            min-width: 1250px;
        }
    }

    /* Small Mobile */

    @media (max-width: 380px) {
        #branchComparisonRoot .header-badge {
            font-size: 9.5px;
        }

        #branchComparisonRoot .header-title {
            font-size: 11.5px;
        }

        #branchComparisonRoot .header-actions .btn {
            font-size: 9.5px !important;
            padding: 6px 4px !important;
        }

        #branchComparisonRoot .date-preset-link {
            padding: 5px 7px;
            font-size: 9.5px;
        }

        #branchComparisonRoot .kpi-value {
            font-size: 12px;
        }

        #branchComparisonRoot .benchmark-icon-bg {
            font-size: 22px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-3" id="branchComparisonRoot">

    <!-- Page Header -->
    <div class="comparison-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div class="header-meta d-flex align-items-center flex-wrap">
            <span class="badge bg-dark text-white header-badge">
                <i class="fas fa-balance-scale me-1"></i> Multi-Branch Benchmarking
            </span>

            <span class="header-title">Enterprise Central Comparison</span>

            <span class="badge bg-light text-secondary border font-monospace header-badge">
                Auditing: {{ date('d M Y', strtotime($startDate)) }} &ndash; {{ date('d M Y', strtotime($endDate)) }}
            </span>

            <span class="badge bg-primary text-white font-monospace header-badge">
                {{ $comparisonData->count() }} Outlets Evaluated
            </span>
        </div>

        <div class="header-actions d-flex align-items-center">
            <a href="{{ route('admin.branch.index') }}" class="btn btn-outline-secondary btn-compact">
                <i class="fas fa-store me-1"></i> Branch Hubs
            </a>

            <a href="{{ route('admin.inventory.dashboard') }}" class="btn btn-light border btn-compact">
                <i class="fas fa-boxes me-1"></i> Central Inventory
            </a>

            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-compact">
                <i class="fas fa-home me-1"></i> HQ Dashboard
            </a>
        </div>
    </div>

    <!-- Date Range & Presets -->
    <div class="filter-panel mb-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <!-- Quick Presets -->
            <div class="preset-group d-flex align-items-center flex-wrap">
                <span class="preset-label me-1">Quick Presets:</span>

                <a href="{{ route('admin.branch_report.comparison', ['start_date' => date('Y-m-d'), 'end_date' => date('Y-m-d')]) }}"
                   class="date-preset-link {{ $startDate === date('Y-m-d') && $endDate === date('Y-m-d') ? 'active' : '' }}">
                    Today
                </a>

                <a href="{{ route('admin.branch_report.comparison', ['start_date' => date('Y-m-d', strtotime('-7 days')), 'end_date' => date('Y-m-d')]) }}"
                   class="date-preset-link {{ $startDate === date('Y-m-d', strtotime('-7 days')) ? 'active' : '' }}">
                    Last 7 Days
                </a>

                <a href="{{ route('admin.branch_report.comparison', ['start_date' => date('Y-m-01'), 'end_date' => date('Y-m-t')]) }}"
                   class="date-preset-link {{ $startDate === date('Y-m-01') && $endDate === date('Y-m-t') ? 'active' : '' }}">
                    This Month
                </a>

                <a href="{{ route('admin.branch_report.comparison', ['start_date' => date('Y-m-01', strtotime('-1 month')), 'end_date' => date('Y-m-t', strtotime('-1 month'))]) }}"
                   class="date-preset-link {{ $startDate === date('Y-m-01', strtotime('-1 month')) ? 'active' : '' }}">
                    Last Month
                </a>

                <a href="{{ route('admin.branch_report.comparison', ['start_date' => date('Y-01-01'), 'end_date' => date('Y-12-31')]) }}"
                   class="date-preset-link {{ $startDate === date('Y-01-01') ? 'active' : '' }}">
                    This Year
                </a>
            </div>

            <!-- Custom Date Range -->
            <form method="GET" action="{{ route('admin.branch_report.comparison') }}" class="custom-date-form d-flex align-items-center">
                <span class="custom-label">Custom:</span>

                <input type="date" name="start_date" class="form-control form-control-sm font-monospace"
                       value="{{ $startDate }}">

                <span class="text-muted small">&ndash;</span>

                <input type="date" name="end_date" class="form-control form-control-sm font-monospace"
                       value="{{ $endDate }}">

                <button type="submit" class="btn btn-primary btn-sm btn-compact">
                    <i class="fas fa-filter me-1"></i> Apply
                </button>
            </form>
        </div>
    </div>

    <!-- Network KPI Summary -->
    <div class="row g-2 mb-3">

        <!-- Gross Sales -->
        <div class="col-6 col-md-3">
            <div class="benchmark-card-box border-start border-primary border-3">
                <i class="fas fa-cash-register benchmark-icon-bg text-primary"></i>

                <span class="kpi-label">Network-Wide Gross Sales</span>

                <h4 class="kpi-value text-dark font-monospace bn-digit">
                    ৳{{ number_format($networkTotals['total_sales'], 2) }}
                </h4>

                <div class="d-flex justify-content-between align-items-center kpi-meta">
                    <small class="text-primary fw-semibold">{{ number_format($networkTotals['total_orders']) }} Total Orders</small>
                    <small class="text-muted">All Outlets</small>
                </div>
            </div>
        </div>

        <!-- Net Profit -->
        <div class="col-6 col-md-3">
            <div class="benchmark-card-box border-start border-success border-3">
                <i class="fas fa-chart-line benchmark-icon-bg text-success"></i>

                <span class="kpi-label">Consolidated Net Profit</span>

                <h4 class="kpi-value {{ $networkTotals['net_profit'] >= 0 ? 'text-success' : 'text-danger' }} font-monospace bn-digit">
                    ৳{{ number_format($networkTotals['net_profit'], 2) }}
                </h4>

                <div class="d-flex justify-content-between align-items-center kpi-meta">
                    <small class="text-muted">Gross Margin: ৳{{ number_format($networkTotals['gross_profit'], 2) }}</small>
                    <small class="text-muted">Net Margin</small>
                </div>
            </div>
        </div>

        <!-- Collection -->
        <div class="col-6 col-md-3">
            <div class="benchmark-card-box border-start border-info border-3">
                <i class="fas fa-hand-holding-usd benchmark-icon-bg text-info"></i>

                <span class="kpi-label">Realized Collection</span>

                <h4 class="kpi-value text-dark font-monospace bn-digit">
                    ৳{{ number_format($networkTotals['total_paid'], 2) }}
                </h4>

                <div class="d-flex justify-content-between align-items-center kpi-meta">
                    <small class="text-success fw-semibold">Cash + Digital Banked</small>
                    <small class="text-danger fw-semibold">Due: ৳{{ number_format($networkTotals['total_due'], 2) }}</small>
                </div>
            </div>
        </div>

        <!-- Stock Value -->
        <div class="col-6 col-md-3">
            <div class="benchmark-card-box border-start border-warning border-3">
                <i class="fas fa-boxes-stacked benchmark-icon-bg text-warning"></i>

                <span class="kpi-label">Total Branch Stock Assets (Cost)</span>

                <h4 class="kpi-value text-dark font-monospace bn-digit">
                    ৳{{ number_format($networkTotals['inventory_value'], 2) }}
                </h4>

                <div class="d-flex justify-content-between align-items-center kpi-meta">
                    <small class="text-warning fw-semibold">{{ number_format($comparisonData->sum('stock_qty')) }} Physical Items</small>
                    <small class="text-muted">Decentralized</small>
                </div>
            </div>
        </div>

    </div>

    <!-- Sales Contribution -->
    <div class="section-card p-3 mb-3">
        <h6 class="section-heading border-bottom pb-2 mb-3">
            <i class="fas fa-chart-bar text-primary me-1"></i>
            Outlet Sales Contribution to Central Network
        </h6>

        @php
            $totalSalesBase = max(0.01, $networkTotals['total_sales']);
        @endphp

        <div class="row g-2">
            @foreach($comparisonData as $b)
                @php
                    $sharePercent = round(($b->total_sales / $totalSalesBase) * 100, 1);
                @endphp

                <div class="col-12 col-md-6 col-lg-4">
                    <div class="sales-share-item">
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                            <span class="sales-share-name text-truncate">
                                {{ $b->branch_name }}
                            </span>

                            <span class="sales-share-value font-monospace text-primary">
                                {{ $sharePercent }}%
                            </span>
                        </div>

                        <div class="progress mb-2">
                            <div class="progress-bar bg-primary"
                                 role="progressbar"
                                 style="width: {{ $sharePercent }}%">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center text-muted font-monospace sales-share-meta">
                            <span>৳{{ number_format($b->total_sales, 2) }}</span>
                            <span>{{ $b->total_orders }} Orders</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Branch Comparison Table -->
    <div class="section-card p-2">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom pb-2 mb-2 px-1">
            <h6 class="section-heading mb-0">
                <i class="fas fa-table text-secondary me-1"></i>
                Outlets Financial &amp; Stock Comparison Matrix
            </h6>

            <small class="text-muted font-monospace" style="font-size: 10px;">
                All currency figures in Bangladeshi Taka (BDT ৳)
            </small>
        </div>

        <div class="comparison-table-wrap">
            <table class="table table-hover table-sm align-middle comparison-table">
                <thead>
                    <tr>
                        <th style="width: 35px;">#</th>
                        <th>Branch / Outlet</th>
                        <th>Type</th>
                        <th class="text-center">Orders</th>
                        <th class="text-end">Gross Sales</th>
                        <th class="text-center">% Share</th>
                        <th class="text-end">Collected</th>
                        <th class="text-end">Due</th>
                        <th class="text-end">Expenses</th>
                        <th class="text-end">Net Profit</th>
                        <th class="text-center">Stock Qty</th>
                        <th class="text-end">Stock Value</th>
                        <th class="text-end" style="width: 120px;">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($comparisonData as $idx => $b)
                        @php
                            $bShare = round(($b->total_sales / $totalSalesBase) * 100, 1);
                            $isNetProfitable = $b->net_profit >= 0;
                        @endphp

                        <tr>
                            <!-- Rank -->
                            <td class="font-monospace text-muted text-center" style="font-size: 10.5px;">
                                {{ $idx + 1 }}
                            </td>

                            <!-- Branch Details -->
                            <td>
                                <a href="{{ route('admin.branch_report.dashboard', ['branch_id' => $b->branch_id]) }}"
                                   class="branch-name-link d-block">
                                    {{ $b->branch_name }}
                                </a>

                                <small class="branch-code font-monospace">
                                    Code: {{ $b->branch_code }}
                                </small>
                            </td>

                            <!-- Branch Type -->
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">
                                    {{ strtoupper($b->branch_type) }}
                                </span>
                            </td>

                            <!-- Orders -->
                            <td class="text-center font-monospace bn-digit fw-bold text-dark">
                                {{ number_format($b->total_orders) }}
                            </td>

                            <!-- Gross Sales -->
                            <td class="text-end font-monospace bn-digit fw-bold text-dark">
                                ৳{{ number_format($b->total_sales, 2) }}
                            </td>

                            <!-- Sales Share -->
                            <td class="text-center">
                                <span class="badge {{ $bShare >= 25 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-light text-muted border' }} font-monospace">
                                    {{ $bShare }}%
                                </span>
                            </td>

                            <!-- Collection -->
                            <td class="text-end font-monospace bn-digit text-success fw-bold">
                                ৳{{ number_format($b->total_paid, 2) }}
                            </td>

                            <!-- Due -->
                            <td class="text-end font-monospace bn-digit {{ $b->total_due > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                ৳{{ number_format($b->total_due, 2) }}
                            </td>

                            <!-- Expenses -->
                            <td class="text-end font-monospace bn-digit text-danger">
                                ৳{{ number_format($b->expenses, 2) }}
                            </td>

                            <!-- Net Profit -->
                            <td class="text-end font-monospace bn-digit fw-bold {{ $isNetProfitable ? 'text-success' : 'text-danger' }}">
                                ৳{{ number_format($b->net_profit, 2) }}
                            </td>

                            <!-- Stock Quantity -->
                            <td class="text-center font-monospace bn-digit text-muted">
                                <span class="badge bg-light text-dark border">
                                    {{ number_format($b->stock_qty) }} pcs
                                </span>
                            </td>

                            <!-- Stock Value -->
                            <td class="text-end font-monospace bn-digit fw-semibold text-dark">
                                ৳{{ number_format($b->inventory_value, 2) }}
                            </td>

                            <!-- Branch Actions -->
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.branch_report.dashboard', ['branch_id' => $b->branch_id]) }}"
                                       class="btn btn-outline-primary"
                                       title="View Hub Analytics"
                                       style="font-size: 10px;">
                                        <i class="fas fa-chart-pie"></i>
                                    </a>

                                    <a href="{{ route('admin.branch_report.sales', ['branch_id' => $b->branch_id]) }}"
                                       class="btn btn-outline-secondary"
                                       title="View Sales Ledger"
                                       style="font-size: 10px;">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </a>

                                    <a href="{{ route('admin.pos.terminal', $b->branch_id) }}"
                                       class="btn btn-outline-warning text-dark"
                                       title="Launch POS"
                                       style="font-size: 10px;">
                                        <i class="fas fa-desktop"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="empty-state">
                                <i class="fas fa-store-slash fa-2x d-block mb-2 text-secondary"></i>
                                No active branches found in the system.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <!-- Consolidated Totals -->
                @if($comparisonData->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-dark">
                                CONSOLIDATED ENTERPRISE TOTALS:
                            </td>

                            <td class="text-center font-monospace bn-digit text-dark">
                                {{ number_format($networkTotals['total_orders']) }}
                            </td>

                            <td class="text-end font-monospace bn-digit text-dark">
                                ৳{{ number_format($networkTotals['total_sales'], 2) }}
                            </td>

                            <td class="text-center font-monospace">
                                100%
                            </td>

                            <td class="text-end font-monospace bn-digit text-success">
                                ৳{{ number_format($networkTotals['total_paid'], 2) }}
                            </td>

                            <td class="text-end font-monospace bn-digit text-danger">
                                ৳{{ number_format($networkTotals['total_due'], 2) }}
                            </td>

                            <td class="text-end font-monospace bn-digit text-danger">
                                ৳{{ number_format($comparisonData->sum('expenses'), 2) }}
                            </td>

                            <td class="text-end font-monospace bn-digit {{ $networkTotals['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                ৳{{ number_format($networkTotals['net_profit'], 2) }}
                            </td>

                            <td class="text-center font-monospace bn-digit text-muted">
                                {{ number_format($comparisonData->sum('stock_qty')) }} pcs
                            </td>

                            <td class="text-end font-monospace bn-digit text-dark">
                                ৳{{ number_format($networkTotals['inventory_value'], 2) }}
                            </td>

                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
@endsection
