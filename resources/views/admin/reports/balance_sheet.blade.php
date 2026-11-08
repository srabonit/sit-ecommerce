
@extends('layouts.admin')

@section('title', 'Balance Sheet - ' . config('app.name'))

@section('admin_content')
<style>
    /* =========================================================
       KAIADMIN LITE — CLASSIC WHITE
       Balance Sheet | Scoped Styles
    ========================================================= */

    #balance-sheet-root {
        --ledger-primary: var(--theme-primary, #1572e8);
        --ledger-secondary: var(--theme-secondary, #686c71);
        --ledger-success: var(--theme-success, #31ce36);
        --ledger-danger: var(--theme-danger, #f25961);
        --ledger-warning: var(--theme-warning, #ffad46);
        --ledger-info: var(--theme-info, #48abf7);

        --ledger-heading: #2a2f5b;
        --ledger-text: #495057;
        --ledger-muted: #8d9498;
        --ledger-border: #ebecec;
        --ledger-surface: #ffffff;
        --ledger-soft: #f8f9fa;

        --ledger-radius: 5px;
        --ledger-shadow: 0 1px 3px rgba(0, 0, 0, .04);

        width: 100%;
        min-width: 0;
        color: var(--ledger-text);
        font-size: 13px;
    }

    #balance-sheet-root *,
    #balance-sheet-root *::before,
    #balance-sheet-root *::after {
        box-sizing: border-box;
    }

    #balance-sheet-root .row,
    #balance-sheet-root .card-body,
    #balance-sheet-root .header-top-wrap {
        min-width: 0;
    }

    /* Page Header */

    #balance-sheet-root .page-heading {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        color: var(--ledger-heading);
        font-size: clamp(16px, 1.4vw, 19px);
        font-weight: 700;
        line-height: 1.4;
    }

    #balance-sheet-root .page-subtitle {
        display: block;
        margin-top: 3px;
        color: var(--ledger-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    /* Common Radius */

    #balance-sheet-root .btn,
    #balance-sheet-root button,
    #balance-sheet-root .badge,
    #balance-sheet-root .alert,
    #balance-sheet-root .form-control,
    #balance-sheet-root .form-select,
    #balance-sheet-root .card,
    #balance-sheet-root .report-card,
    #balance-sheet-root .kpi-stat-card,
    #balance-sheet-root .collapse-toggle-btn {
        border-radius: var(--ledger-radius) !important;
    }

    #balance-sheet-root .input-group > .input-group-text {
        border-radius: 5px 0 0 5px !important;
    }

    #balance-sheet-root .input-group > .form-control,
    #balance-sheet-root .input-group > .form-select {
        border-radius: 0 5px 5px 0 !important;
    }

    /* Typography */

    #balance-sheet-root .font-monospace,
    #balance-sheet-root .bn-digit {
        font-variant-numeric: tabular-nums;
    }

    #balance-sheet-root .text-muted {
        color: var(--ledger-muted) !important;
    }

    #balance-sheet-root .text-dark {
        color: var(--ledger-heading) !important;
    }

    #balance-sheet-root .badge {
        font-weight: 600;
    }

    /* Buttons */

    #balance-sheet-root .btn {
        font-weight: 600;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease;
    }

    #balance-sheet-root .btn-compact {
        display: inline-flex;
        min-height: 34px;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 6px 12px !important;
        font-size: 11.5px !important;
        font-weight: 600 !important;
        line-height: 1.3;
        white-space: nowrap;
    }

    #balance-sheet-root .btn:focus-visible,
    #balance-sheet-root .collapse-toggle-btn:focus-visible {
        outline: 2px solid var(--ledger-primary);
        outline-offset: 2px;
    }

    #balance-sheet-root .btn-dark {
        background: #2a2f5b;
        border-color: #2a2f5b;
    }

    #balance-sheet-root .btn-dark:hover {
        background: #1e2348;
        border-color: #1e2348;
    }

    #balance-sheet-root .btn-outline-success {
        color: #31ce36;
        border-color: #31ce36;
    }

    #balance-sheet-root .btn-outline-success:hover {
        background: #31ce36;
        border-color: #31ce36;
        color: #fff;
    }

    #balance-sheet-root .btn-outline-secondary {
        color: #686c71;
        background: #fff;
        border-color: #dfe1e3;
    }

    #balance-sheet-root .btn-outline-secondary:hover {
        color: var(--ledger-heading);
        background: #f3f4f6;
        border-color: #c9cccf;
    }

    /* Report Cards */

    #balance-sheet-root .report-card {
        min-width: 0;
        margin-bottom: 16px;
        background: var(--ledger-surface);
        border: 1px solid var(--ledger-border) !important;
        box-shadow: var(--ledger-shadow) !important;
    }

    #balance-sheet-root .report-card-header {
        display: flex;
        min-height: 46px;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 11px 15px;
        background: #fff;
        border-bottom: 1px solid var(--ledger-border);
    }

    #balance-sheet-root .report-card-header h6 {
        margin: 0;
        color: var(--ledger-heading);
        font-size: 13px;
        font-weight: 700;
        line-height: 1.4;
    }

    /* Collapse Buttons */

    #balance-sheet-root .collapse-toggle-btn {
        display: inline-flex;
        width: 28px;
        height: 28px;
        min-width: 28px;
        align-items: center;
        justify-content: center;
        padding: 0;
        color: #686c71;
        background: #fff;
        border: 1px solid var(--ledger-border);
        transition: all .18s ease;
    }

    #balance-sheet-root .collapse-toggle-btn:hover {
        color: #fff;
        background: var(--ledger-primary);
        border-color: var(--ledger-primary);
    }

    #balance-sheet-root .collapse-icon {
        font-size: 10px;
        transition: transform .25s ease;
    }

    #balance-sheet-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* KPI Cards */

    #balance-sheet-root .kpi-stat-card {
        position: relative;
        height: 100%;
        min-width: 0;
        overflow: hidden;
        padding: 15px 14px 14px 17px;
        background: #fff;
        border: 1px solid var(--ledger-border);
        box-shadow: var(--ledger-shadow);
        transition: box-shadow .2s ease;
    }

    #balance-sheet-root .kpi-stat-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, .06);
    }

    #balance-sheet-root .kpi-stat-card::before {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: 3px;
        content: '';
    }

    #balance-sheet-root .kpi-current-assets::before {
        background: #48abf7;
    }

    #balance-sheet-root .kpi-fixed-assets::before {
        background: #31cebb;
    }

    #balance-sheet-root .kpi-total-assets::before {
        background: #31ce36;
    }

    #balance-sheet-root .kpi-total-equity::before {
        background: #6861ce;
    }

    #balance-sheet-root .kpi-label {
        display: block;
        margin-bottom: 7px;
        color: var(--ledger-muted);
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .35px;
        line-height: 1.4;
        text-transform: uppercase;
    }

    #balance-sheet-root .kpi-value {
        display: block;
        margin: 0 0 5px;
        font-size: clamp(14px, 1.35vw, 19px);
        font-weight: 700;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    #balance-sheet-root .kpi-note {
        display: block;
        color: var(--ledger-muted);
        font-size: 10px;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    /* Reconciliation Status */

    #balance-sheet-root .equation-status-card {
        background: #fff;
        border: 1px solid var(--ledger-border) !important;
        border-left: 4px solid #31ce36 !important;
        box-shadow: var(--ledger-shadow);
    }

    #balance-sheet-root .equation-status-card.unbalanced {
        border-left-color: #f25961 !important;
    }

    #balance-sheet-root .equation-status-card .badge {
        max-width: 100%;
        line-height: 1.5;
        white-space: normal;
    }

    /* Form Controls */

    #balance-sheet-root .form-label {
        display: block;
        margin-bottom: 5px;
        color: #495057;
        font-size: 11.5px;
        font-weight: 600;
    }

    #balance-sheet-root .form-control,
    #balance-sheet-root .form-select {
        min-height: 36px;
        padding: 7px 10px;
        color: #495057;
        background-color: #fff;
        border: 1px solid #ebedf2;
        font-size: 12px;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #balance-sheet-root .form-control:focus,
    #balance-sheet-root .form-select:focus {
        border-color: var(--ledger-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
        outline: none;
    }

    /* Statement Tables */

    #balance-sheet-root .pc-fit-container {
        width: 100%;
        min-width: 0;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #d5d9df transparent;
    }

    #balance-sheet-root .statement-table {
        width: 100%;
        min-width: 390px;
        margin: 0;
        border-collapse: collapse;
    }

    #balance-sheet-root .statement-table th {
        padding: 10px 12px;
        color: #686c71;
        background: var(--ledger-soft);
        border-bottom: 1px solid var(--ledger-border);
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .25px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    #balance-sheet-root .statement-table td {
        padding: 9px 12px;
        color: #495057;
        border-bottom: 1px solid #f1f1f1;
        font-size: 12px;
        line-height: 1.5;
        vertical-align: middle;
    }

    #balance-sheet-root .statement-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #balance-sheet-root .statement-table tbody tr:not(.statement-section-header):hover {
        background: #fafbfc;
    }

    #balance-sheet-root .statement-table .statement-section-header td {
        padding: 8px 12px;
        color: var(--ledger-heading);
        background: #f8f9fa;
        border-bottom: 1px solid var(--ledger-border);
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .3px;
        text-transform: uppercase;
    }

    #balance-sheet-root .statement-table strong {
        line-height: 1.5;
    }

    #balance-sheet-root .statement-table small {
        display: block;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    #balance-sheet-root .statement-table .text-end {
        white-space: nowrap;
    }

    #balance-sheet-root .statement-table .bg-light {
        background-color: #f8f9fa !important;
    }

    #balance-sheet-root .statement-table .border-top {
        border-top-color: #dfe1e5 !important;
    }

    /* Account Detail Cards */

    #balance-sheet-root .detail-card {
        height: 100%;
        padding: 15px;
    }

    #balance-sheet-root .detail-card-heading {
        min-width: 0;
        gap: 8px;
    }

    #balance-sheet-root .detail-card-heading h6 {
        min-width: 0;
        color: var(--ledger-heading);
        font-size: 12.5px;
        font-weight: 700;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    #balance-sheet-root .detail-card a {
        font-size: 11px;
        white-space: nowrap;
    }

    #balance-sheet-root .detail-row {
        min-width: 0;
        gap: 10px;
    }

    #balance-sheet-root .detail-row > div,
    #balance-sheet-root .detail-row > span {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    /* Utilities */

    #balance-sheet-root .shadow-xs {
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04) !important;
    }

    /* Tablet */

    @media (max-width: 991.98px) {
        #balance-sheet-root .kpi-value {
            font-size: 16px;
        }

        #balance-sheet-root .report-card-header {
            padding: 11px 12px;
        }
    }

    /* Mobile */

    @media (max-width: 767.98px) {
        #balance-sheet-root {
            padding-right: 10px !important;
            padding-left: 10px !important;
        }

        #balance-sheet-root .header-top-wrap {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 10px !important;
        }

        #balance-sheet-root .page-heading {
            font-size: 16px;
        }

        #balance-sheet-root .page-subtitle {
            font-size: 11px;
        }

        #balance-sheet-root .top-action-btns {
            display: grid;
            width: 100%;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 7px;
        }

        #balance-sheet-root .top-action-btns .btn {
            width: 100%;
            min-width: 0;
            padding: 7px 6px !important;
            font-size: 11px !important;
        }

        #balance-sheet-root .report-card-header {
            padding: 10px 11px;
        }

        #balance-sheet-root .kpi-stat-card {
            padding: 12px 9px 12px 13px;
        }

        #balance-sheet-root .kpi-label {
            margin-bottom: 5px;
            font-size: 9.5px;
        }

        #balance-sheet-root .kpi-value {
            font-size: 14px;
        }

        #balance-sheet-root .kpi-note {
            font-size: 9px;
        }

        #balance-sheet-root .statement-table th,
        #balance-sheet-root .statement-table td {
            padding: 8px 10px;
        }

        #balance-sheet-root .equation-status-card {
            align-items: flex-start !important;
        }

        #balance-sheet-root .detail-card {
            padding: 12px;
        }

        #balance-sheet-root .form-label {
            font-size: 11px;
        }
    }

    /* Small Mobile */

    @media (max-width: 380px) {
        #balance-sheet-root .page-heading {
            font-size: 15px;
        }

        #balance-sheet-root .top-action-btns .btn {
            padding: 7px 4px !important;
            font-size: 10px !important;
        }

        #balance-sheet-root .kpi-value {
            font-size: 12px;
        }

        #balance-sheet-root .kpi-label {
            font-size: 9px;
        }

        #balance-sheet-root .kpi-note {
            font-size: 8.5px;
        }
    }

    /* Print */

    @media print {
        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        body {
            color: #000 !important;
            background: #fff !important;
        }

        #balance-sheet-root .no-print,
        .admin-dark-sidebar,
        .admin-header,
        .main-footer {
            display: none !important;
        }

        #balance-sheet-root {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        #balance-sheet-root .report-card,
        #balance-sheet-root .kpi-stat-card {
            border: 1px solid #ccc !important;
            box-shadow: none !important;
        }

        #balance-sheet-root .report-card {
            margin-bottom: 8px !important;
            break-inside: avoid;
        }

        #balance-sheet-root .pc-fit-container {
            overflow: visible !important;
        }

        #balance-sheet-root .statement-table {
            min-width: 0 !important;
        }

        #balance-sheet-root .statement-table th,
        #balance-sheet-root .statement-table td {
            color: #000 !important;
        }

        #balance-sheet-root .collapse:not(.show) {
            display: block !important;
            height: auto !important;
            visibility: visible !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="balance-sheet-root">

    <!-- 1. Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 header-top-wrap no-print">
        <div>
            <h5 class="page-heading">
                <i class="fas fa-scale-balanced text-primary"></i>
                <span>Balance Sheet</span>
            </h5>
            <small class="page-subtitle">
                Assets, liabilities, capital and equity overview
            </small>
        </div>

        <div class="top-action-btns d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-dark btn-compact shadow-xs fw-semibold" onclick="window.print()">
                <i class="fas fa-print me-1"></i>
                <span>Print</span>
            </button>

            @php
                $exportBsUrl = Route::has('admin.report.export_balance_sheet')
                    ? route('admin.report.export_balance_sheet', ['as_of_date' => $asOfDate])
                    : url('/admin/report/export-balance-sheet?as_of_date=' . $asOfDate);
            @endphp

            <a href="{{ $exportBsUrl }}" class="btn btn-sm btn-outline-success btn-compact shadow-xs">
                <i class="fas fa-file-csv me-1"></i>
                <span>Export CSV</span>
            </a>

            <a href="{{ route('admin.report.cash_flow') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-money-bill-trend-up me-1 text-primary"></i>
                <span>Cash Flow</span>
            </a>

            <a href="{{ route('admin.report.profit_loss') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-chart-line me-1 text-primary"></i>
                <span>Profit &amp; Loss</span>
            </a>
        </div>
    </div>

    <!-- 2. Date Filter -->
    <div class="card border-0 shadow-sm report-card mb-3 p-3 no-print">
        <form action="{{ route('admin.report.balance_sheet') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label">As of Date</label>
                <input type="date" name="as_of_date" class="form-control font-monospace bn-digit" value="{{ $asOfDate }}" required>
            </div>

            <div class="col-12 col-md-auto d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-dark btn-compact shadow-xs fw-bold px-3">
                    <i class="fas fa-calculator me-1"></i>
                    Generate Report
                </button>

                @if(request()->filled('as_of_date') && request('as_of_date') !== date('Y-m-d'))
                    <a href="{{ route('admin.report.balance_sheet') }}" class="btn btn-light border btn-compact shadow-xs">Today</a>
                @endif
            </div>
        </form>
    </div>

    <!-- 3. Reconciliation Status -->
    <div class="alert alert-light border py-2 px-3 small d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 equation-status-card shadow-xs {{ abs($balanceSheetData['difference']) > 1 ? 'unbalanced' : '' }}">
        <div>
            <span class="text-muted">Report Date:</span>
            <strong class="text-dark font-monospace bn-digit ms-1">{{ $balanceSheetData['as_of_date'] }}</strong>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if(abs($balanceSheetData['difference']) <= 1)
                <span class="badge bg-success text-white px-2 py-1 font-monospace" style="font-size: 10px;">
                    <i class="fas fa-check-circle me-1"></i>
                    Balanced: Assets = Liabilities + Equity
                </span>
            @else
                <span class="badge bg-danger text-white px-2 py-1 font-monospace" style="font-size: 10px;">
                    <i class="fas fa-triangle-exclamation me-1"></i>
                    Variance: ৳ {{ number_format($balanceSheetData['difference'], 2) }}
                </span>
            @endif
        </div>
    </div>

    <!-- 4. Financial Summary -->
    <div class="report-card mb-3">
        <div class="report-card-header">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-chart-pie text-primary"></i>
                <h6 class="fw-bold text-dark mb-0">Financial Summary</h6>
            </div>

            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseBalanceKpis" aria-expanded="true" aria-controls="collapseBalanceKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseBalanceKpis">
            <div class="p-3 bg-light bg-opacity-25">
                <div class="row g-2">

                    <!-- Current Assets -->
                    <div class="col-6 col-lg-3">
                        <div class="kpi-stat-card kpi-current-assets shadow-xs">
                            <span class="kpi-label">Current Assets</span>
                            <h5 class="kpi-value text-primary font-monospace bn-digit">৳ {{ number_format($balanceSheetData['total_current_assets'], 2) }}</h5>
                            <small class="kpi-note font-monospace">Cash, stock &amp; receivables</small>
                        </div>
                    </div>

                    <!-- Fixed Assets -->
                    <div class="col-6 col-lg-3">
                        <div class="kpi-stat-card kpi-fixed-assets shadow-xs">
                            <span class="kpi-label">Fixed Assets</span>
                            <h5 class="kpi-value mb-0 font-monospace bn-digit" style="color: #0d9488;">৳ {{ number_format($balanceSheetData['total_fixed_assets'], 2) }}</h5>
                            <small class="kpi-note font-monospace">Property &amp; equipment</small>
                        </div>
                    </div>

                    <!-- Total Assets -->
                    <div class="col-6 col-lg-3">
                        <div class="kpi-stat-card kpi-total-assets shadow-xs">
                            <span class="kpi-label">Total Assets</span>
                            <h5 class="kpi-value text-success font-monospace bn-digit">৳ {{ number_format($balanceSheetData['total_assets'], 2) }}</h5>
                            <small class="kpi-note font-monospace">Current + fixed assets</small>
                        </div>
                    </div>

                    <!-- Total Equity -->
                    <div class="col-6 col-lg-3">
                        <div class="kpi-stat-card kpi-total-equity shadow-xs">
                            <span class="kpi-label">Total Equity</span>
                            <h5 class="kpi-value mb-0 font-monospace bn-digit" style="color: #6366f1;">৳ {{ number_format($balanceSheetData['total_equity'], 2) }}</h5>
                            <small class="kpi-note font-monospace">Capital + retained earnings</small>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- 5. Financial Statements -->
    <div class="row g-3">

        <!-- Assets -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm report-card overflow-hidden h-100">

                <div class="report-card-header">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1">
                        <i class="fas fa-vault text-primary"></i>
                        <span>1. Assets</span>
                    </h6>

                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-secondary border font-monospace" style="font-size: 9.5px;">ASSETS (৳)</span>

                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseAssetsTable" aria-expanded="true" aria-controls="collapseAssetsTable" title="Toggle Assets">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="collapse show" id="collapseAssetsTable">
                    <div class="card-body p-0">
                        <div class="table-responsive pc-fit-container">
                            <table class="table align-middle statement-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Asset / Account</th>
                                        <th class="text-end pe-3" style="width: 150px;">Amount (৳)</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <!-- CURRENT ASSETS -->
                                    <tr class="statement-section-header">
                                        <td colspan="2">
                                            <i class="fas fa-money-bill-transfer me-1 text-primary"></i>
                                            Current Assets
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="ps-3">
                                            <strong class="d-block text-dark" style="font-size: 12px;">Cash &amp; Bank</strong>
                                            <small class="text-muted font-monospace" style="font-size: 10px;">Cash drawers and bank balances</small>
                                        </td>
                                        <td class="text-end pe-3 font-monospace bn-digit">৳ {{ number_format($balanceSheetData['total_bank_cash'], 2) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="ps-3">
                                            <strong class="d-block text-dark" style="font-size: 12px;">Inventory</strong>
                                            <small class="text-muted font-monospace" style="font-size: 10px;">Unsold stock at purchase cost</small>
                                        </td>
                                        <td class="text-end pe-3 font-monospace bn-digit">৳ {{ number_format($balanceSheetData['inventory_valuation'], 2) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="ps-3">
                                            <strong class="d-block text-dark" style="font-size: 12px;">Courier Receivables</strong>
                                            <small class="text-muted font-monospace" style="font-size: 10px;">COD awaiting collection</small>
                                        </td>
                                        <td class="text-end pe-3 font-monospace bn-digit">৳ {{ number_format($balanceSheetData['courier_receivables'], 2) }}</td>
                                    </tr>

                                    <tr class="bg-light fw-bold">
                                        <td class="ps-3 text-secondary">Total Current Assets</td>
                                        <td class="text-end pe-3 text-primary font-monospace bn-digit">৳ {{ number_format($balanceSheetData['total_current_assets'], 2) }}</td>
                                    </tr>

                                    <!-- FIXED ASSETS -->
                                    <tr class="statement-section-header">
                                        <td colspan="2">
                                            <i class="fas fa-building me-1" style="color: #0d9488;"></i>
                                            Fixed Assets
                                        </td>
                                    </tr>

                                    @if(isset($fixedAssets) && $fixedAssets->isNotEmpty())
                                        @foreach($fixedAssets as $ast)
                                            <tr>
                                                <td class="ps-3">
                                                    <span class="text-dark">{{ ucwords(str_replace('_', ' ', $ast->asset_type)) }}</span>
                                                    <small class="text-muted font-monospace bn-digit" style="font-size: 10px;">({{ $ast->count }} units)</small>
                                                </td>
                                                <td class="text-end pe-3 font-monospace bn-digit">৳ {{ number_format((float)$ast->total_cost, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td class="ps-3 text-muted">Fixed Assets Register</td>
                                            <td class="text-end pe-3 font-monospace bn-digit">৳ {{ number_format($balanceSheetData['total_fixed_assets'], 2) }}</td>
                                        </tr>
                                    @endif

                                    <tr class="bg-light fw-bold">
                                        <td class="ps-3 text-secondary">Total Fixed Assets</td>
                                        <td class="text-end pe-3 font-monospace bn-digit" style="color: #0d9488;">৳ {{ number_format($balanceSheetData['total_fixed_assets'], 2) }}</td>
                                    </tr>

                                    <!-- TOTAL ASSETS -->
                                    <tr class="bg-light border-top border-2 fw-bold" style="font-size: 12.5px;">
                                        <td class="py-2 ps-3">
                                            <span class="text-dark">TOTAL ASSETS</span>
                                            <small class="d-block text-muted fw-normal" style="font-size: 10px;">Current + fixed assets</small>
                                        </td>

                                        <td class="text-end pe-3 py-2 font-monospace bn-digit text-success fw-bold" style="font-size: 13.5px;">
                                            ৳ {{ number_format($balanceSheetData['total_assets'], 2) }}
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liabilities & Equity -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm report-card overflow-hidden h-100">

                <div class="report-card-header">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1">
                        <i class="fas fa-hand-holding-dollar" style="color: #6366f1;"></i>
                        <span>2. Liabilities &amp; Equity</span>
                    </h6>

                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-secondary border font-monospace" style="font-size: 9.5px;">EQUITY (৳)</span>

                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseLiabilitiesTable" aria-expanded="true" aria-controls="collapseLiabilitiesTable" title="Toggle Liabilities">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="collapse show" id="collapseLiabilitiesTable">
                    <div class="card-body p-0">
                        <div class="table-responsive pc-fit-container">
                            <table class="table align-middle statement-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Account</th>
                                        <th class="text-end pe-3" style="width: 150px;">Amount (৳)</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <!-- LIABILITIES -->
                                    <tr class="statement-section-header">
                                        <td colspan="2">
                                            <i class="fas fa-file-invoice me-1 text-danger"></i>
                                            Liabilities
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="ps-3">
                                            <strong class="d-block text-dark" style="font-size: 12px;">Accounts Payable</strong>
                                            <small class="text-muted font-monospace" style="font-size: 10px;">Supplier dues and pending bills</small>
                                        </td>
                                        <td class="text-end pe-3 font-monospace bn-digit">৳ {{ number_format($balanceSheetData['current_liabilities'], 2) }}</td>
                                    </tr>

                                    <tr class="bg-light fw-bold">
                                        <td class="ps-3 text-secondary">Total Liabilities</td>
                                        <td class="text-end pe-3 text-danger font-monospace bn-digit">৳ {{ number_format($balanceSheetData['current_liabilities'], 2) }}</td>
                                    </tr>

                                    <!-- OWNER'S EQUITY -->
                                    <tr class="statement-section-header">
                                        <td colspan="2">
                                            <i class="fas fa-chart-pie me-1" style="color: #6366f1;"></i>
                                            Owner's Equity
                                        </td>
                                    </tr>

                                    @if(isset($investorBreakdown) && $investorBreakdown->isNotEmpty())
                                        @foreach($investorBreakdown as $inv)
                                            <tr>
                                                <td class="ps-3">
                                                    <span class="text-dark">{{ $inv->investor_name }}</span>
                                                    <small class="text-muted" style="font-size: 10px;">(Capital)</small>
                                                </td>
                                                <td class="text-end pe-3 font-monospace bn-digit">৳ {{ number_format((float)$inv->total_capital, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td class="ps-3 text-muted">Investor Capital</td>
                                            <td class="text-end pe-3 font-monospace bn-digit">৳ {{ number_format($balanceSheetData['total_invested_capital'], 2) }}</td>
                                        </tr>
                                    @endif

                                    <tr class="bg-light">
                                        <td class="ps-3 text-secondary fw-semibold">Total Invested Capital</td>
                                        <td class="text-end pe-3 font-monospace bn-digit fw-bold" style="color: #6366f1;">৳ {{ number_format($balanceSheetData['total_invested_capital'], 2) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="ps-3">
                                            <strong class="d-block text-dark" style="font-size: 12px;">Retained Earnings</strong>
                                            <small class="text-muted font-monospace" style="font-size: 10px;">Cumulative net profit</small>
                                        </td>

                                        <td class="text-end pe-3 font-monospace bn-digit {{ $balanceSheetData['retained_earnings'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $balanceSheetData['retained_earnings'] >= 0 ? '+' : '' }}৳ {{ number_format($balanceSheetData['retained_earnings'], 2) }}
                                        </td>
                                    </tr>

                                    <tr class="bg-light fw-bold">
                                        <td class="ps-3 text-secondary">Total Owner's Equity</td>
                                        <td class="text-end pe-3 font-monospace bn-digit" style="color: #6366f1;">৳ {{ number_format($balanceSheetData['total_equity'], 2) }}</td>
                                    </tr>

                                    <!-- TOTAL LIABILITIES & EQUITY -->
                                    <tr class="bg-light border-top border-2 fw-bold" style="font-size: 12.5px;">
                                        <td class="py-2 ps-3">
                                            <span class="text-dark">TOTAL LIABILITIES &amp; EQUITY</span>
                                            <small class="d-block text-muted fw-normal" style="font-size: 10px;">Liabilities + equity</small>
                                        </td>

                                        <td class="text-end pe-3 py-2 font-monospace bn-digit {{ abs($balanceSheetData['difference']) <= 1 ? 'text-success' : 'text-warning' }} fw-bold" style="font-size: 13.5px;">
                                            ৳ {{ number_format($balanceSheetData['total_liab_equity'], 2) }}
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. Account Details -->
    <div class="row g-3 mt-1 no-print">

        <!-- Bank Accounts -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm report-card detail-card">

                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2 detail-card-heading">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1">
                        <i class="fas fa-building-columns text-primary"></i>
                        <span>Bank &amp; Cash</span>
                    </h6>

                    <a href="{{ url('/admin/accounts/bank-balance') }}" class="small text-primary text-decoration-none fw-semibold">Manage</a>
                </div>

                @if(isset($bankAccounts) && $bankAccounts->isNotEmpty())
                    @foreach($bankAccounts as $acc)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom detail-row">
                            <div>
                                <strong class="d-block text-dark small">{{ $acc->account_name }}</strong>
                                <small class="text-muted text-uppercase font-monospace" style="font-size: 9.5px;">{{ $acc->account_type }}</small>
                            </div>

                            <span class="fw-bold font-monospace bn-digit {{ (float)$acc->current_balance >= 0 ? 'text-dark' : 'text-danger' }}" style="font-size: 12px;">
                                ৳ {{ number_format((float)$acc->current_balance, 2) }}
                            </span>
                        </div>
                    @endforeach

                    <div class="d-flex justify-content-between align-items-center pt-3 detail-row">
                        <strong class="text-dark small">Total Cash &amp; Bank:</strong>
                        <strong class="text-primary font-monospace bn-digit">৳ {{ number_format($balanceSheetData['total_bank_cash'], 2) }}</strong>
                    </div>
                @else
                    <p class="text-muted small mb-0 py-3 text-center">No bank accounts found.</p>
                @endif

            </div>
        </div>

        <!-- Investor Details -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm report-card detail-card">

                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2 detail-card-heading">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1">
                        <i class="fas fa-handshake" style="color: #6366f1;"></i>
                        <span>Capital Investors</span>
                    </h6>

                    <a href="{{ url('/admin/accounts/investment') }}" class="small text-primary text-decoration-none fw-semibold">View Ledger</a>
                </div>

                @if(isset($investorBreakdown) && $investorBreakdown->isNotEmpty())
                    @foreach($investorBreakdown as $inv)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom detail-row">
                            <span class="text-dark small">{{ $inv->investor_name }}</span>

                            <span class="fw-bold font-monospace bn-digit" style="color: #6366f1; font-size: 12px;">
                                ৳ {{ number_format((float)$inv->total_capital, 2) }}
                            </span>
                        </div>
                    @endforeach

                    <div class="d-flex justify-content-between align-items-center pt-3 detail-row">
                        <strong class="text-dark small">Total Capital:</strong>
                        <strong class="font-monospace bn-digit" style="color: #6366f1;">৳ {{ number_format($balanceSheetData['total_invested_capital'], 2) }}</strong>
                    </div>
                @else
                    <p class="text-muted small mb-0 py-3 text-center">No investor records found.</p>
                @endif

            </div>
        </div>

    </div>
</div>
@endsection
