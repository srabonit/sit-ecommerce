@extends('layouts.admin')

@section('title', 'Statement of Financial Position (Balance Sheet) - ' . config('app.name'))

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #balance-sheet-root .btn,
    #balance-sheet-root .btn-sm,
    #balance-sheet-root button,
    #balance-sheet-root .badge,
    #balance-sheet-root .alert,
    #balance-sheet-root .form-control,
    #balance-sheet-root .form-select,
    #balance-sheet-root .card,
    #balance-sheet-root .report-card,
    #balance-sheet-root .kpi-stat-card,
    #balance-sheet-root .collapse-toggle-btn {
        border-radius: 5px !important;
    }

    #balance-sheet-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #balance-sheet-root .input-group > .form-control,
    #balance-sheet-root .input-group > .form-select {
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
    .kpi-current-assets::before { background-color: #0284c7; }
    .kpi-fixed-assets::before   { background-color: #0d9488; }
    .kpi-total-assets::before   { background-color: #10b981; }
    .kpi-total-equity::before   { background-color: #6366f1; }

    /* Statement Tables */
    .statement-table {
        width: 100%;
        margin-bottom: 0;
    }
    .statement-table th {
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
    .statement-table td {
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

    /* Reconciliation Status Alert */
    .equation-status-card {
        border-left: 4px solid #10b981 !important;
        background: #ffffff;
    }
    .equation-status-card.unbalanced {
        border-left: 4px solid #ef4444 !important;
    }

    /* Form Controls */
    .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #balance-sheet-root .form-control,
    #balance-sheet-root .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease;
    }
    #balance-sheet-root .form-control:focus,
    #balance-sheet-root .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Print Styles */
    @media print {
        @page { size: A4 portrait; margin: 8mm; }
        body { background: #ffffff !important; color: #000000 !important; }
        #balance-sheet-root .no-print, .admin-dark-sidebar, .admin-header, .main-footer { display: none !important; }
        #balance-sheet-root { padding: 0 !important; margin: 0 !important; width: 100% !important; }
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
            min-width: 360px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="balance-sheet-root">

    <!-- 1. Header (Original Clean Light Theme) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 header-top-wrap no-print">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-scale-balanced text-primary"></i>
                <span>Statement of Financial Position (Balance Sheet)</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Corporate assets, warehouse stock valuation, liquid bank reserves, capital &amp; retained equity audit
            </small>
        </div>
        <div class="top-action-btns d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-dark btn-compact shadow-xs fw-semibold" onclick="window.print()">
                <i class="fas fa-print me-1"></i> <span>Print A4</span>
            </button>
            @php
                $exportBsUrl = Route::has('admin.report.export_balance_sheet') 
                    ? route('admin.report.export_balance_sheet', ['as_of_date' => $asOfDate])
                    : url('/admin/report/export-balance-sheet?as_of_date=' . $asOfDate);
            @endphp
            <a href="{{ $exportBsUrl }}" class="btn btn-sm btn-outline-success btn-compact shadow-xs">
                <i class="fas fa-file-csv me-1"></i> <span>Export CSV</span>
            </a>
            <a href="{{ route('admin.report.cash_flow') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-money-bill-trend-up me-1 text-primary"></i> <span>Cash Flow</span>
            </a>
            <a href="{{ route('admin.report.profit_loss') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-chart-line me-1 text-primary"></i> <span>P&amp;L Statement</span>
            </a>
        </div>
    </div>

    <!-- 2. Filter Toolbar (Clean & Symmetric) -->
    <div class="card border-0 shadow-sm report-card mb-3 p-3 no-print">
        <form action="{{ route('admin.report.balance_sheet') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label">Reporting / Audit Date (As Of)</label>
                <input type="date" name="as_of_date" class="form-control font-monospace bn-digit" value="{{ $asOfDate }}" required>
            </div>
            <div class="col-12 col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-dark btn-compact shadow-xs fw-bold px-3">
                    <i class="fas fa-calculator me-1"></i> Reconcile Statement
                </button>
                @if(request()->filled('as_of_date') && request('as_of_date') !== date('Y-m-d'))
                    <a href="{{ route('admin.report.balance_sheet') }}" class="btn btn-light border btn-compact shadow-xs">Current Date</a>
                @endif
            </div>
        </form>
    </div>

    <!-- 3. Reconciliation Status Alert -->
    <div class="alert alert-light border py-2 px-3 small d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 equation-status-card shadow-xs {{ abs($balanceSheetData['difference']) > 1 ? 'unbalanced' : '' }}">
        <div>
            <span class="text-muted">Audit Snapshot As Of:</span> 
            <strong class="text-dark font-monospace bn-digit ms-1">{{ $balanceSheetData['as_of_date'] }}</strong>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(abs($balanceSheetData['difference']) <= 1)
                <span class="badge bg-success text-white px-2 py-1 font-monospace" style="font-size: 10px;">
                    <i class="fas fa-check-circle me-1"></i> Balanced: Assets = Liabilities + Equity
                </span>
            @else
                <span class="badge bg-danger text-white px-2 py-1 font-monospace" style="font-size: 10px;">
                    <i class="fas fa-triangle-exclamation me-1"></i> Unreconciled Variance: ৳ {{ number_format($balanceSheetData['difference'], 2) }}
                </span>
            @endif
        </div>
    </div>

    <!-- 4. KPI Metric Summary Cards (Collapsible) -->
    <div class="report-card mb-3">
        <div class="report-card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-chart-pie text-primary"></i>
                <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">Balance Sheet Portfolio Metrics</h6>
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
                            <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Total Current Assets</span>
                            <h5 class="fw-bold text-primary mb-0 font-monospace bn-digit">৳ {{ number_format($balanceSheetData['total_current_assets'], 2) }}</h5>
                            <small class="text-muted font-monospace" style="font-size: 10px;">Liquid Cash, Stock &amp; Receivables</small>
                        </div>
                    </div>

                    <!-- Fixed Assets -->
                    <div class="col-6 col-lg-3">
                        <div class="kpi-stat-card kpi-fixed-assets shadow-xs">
                            <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Fixed / Property Assets</span>
                            <h5 class="fw-bold mb-0 font-monospace bn-digit" style="color: #0d9488;">৳ {{ number_format($balanceSheetData['total_fixed_assets'], 2) }}</h5>
                            <small class="text-muted font-monospace" style="font-size: 10px;">Equipment, Electronics, Furniture</small>
                        </div>
                    </div>

                    <!-- Total Assets -->
                    <div class="col-6 col-lg-3">
                        <div class="kpi-stat-card kpi-total-assets shadow-xs">
                            <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Total Corporate Assets</span>
                            <h5 class="fw-bold text-success mb-0 font-monospace bn-digit">৳ {{ number_format($balanceSheetData['total_assets'], 2) }}</h5>
                            <small class="text-success fw-bold font-monospace" style="font-size: 10px;">Combined Asset Valuation</small>
                        </div>
                    </div>

                    <!-- Total Equity -->
                    <div class="col-6 col-lg-3">
                        <div class="kpi-stat-card kpi-total-equity shadow-xs">
                            <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Total Equity &amp; Reserves</span>
                            <h5 class="fw-bold mb-0 font-monospace bn-digit" style="color: #6366f1;">৳ {{ number_format($balanceSheetData['total_equity'], 2) }}</h5>
                            <small class="text-muted font-monospace" style="font-size: 10px;">Capital + Retained Earnings</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Dual Statement Financial Position Tables (Collapsible) -->
    <div class="row g-3">
        <!-- Left Column: Corporate Assets -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm report-card overflow-hidden h-100">
                <div class="report-card-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                        <i class="fas fa-vault text-primary"></i> <span>1. Corporate Assets Ledger</span>
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
                                        <th>Asset Classification / Account</th>
                                        <th class="text-end pe-3" style="width: 150px;">Valuation (৳)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- CURRENT ASSETS -->
                                    <tr class="statement-section-header">
                                        <td colspan="2"><i class="fas fa-money-bill-transfer me-1 text-primary"></i> Current &amp; Liquid Assets</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-3">
                                            <strong class="d-block text-dark" style="font-size: 12px;">Liquid Cash &amp; Bank Balances</strong>
                                            <small class="text-muted font-monospace" style="font-size: 10px;">Total active cash drawers and commercial bank accounts</small>
                                        </td>
                                        <td class="text-end pe-3 font-monospace bn-digit">৳ {{ number_format($balanceSheetData['total_bank_cash'], 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-3">
                                            <strong class="d-block text-dark" style="font-size: 12px;">Warehouse Merchandise Inventory</strong>
                                            <small class="text-muted font-monospace" style="font-size: 10px;">Current unsold products stock evaluated at purchase cost</small>
                                        </td>
                                        <td class="text-end pe-3 font-monospace bn-digit">৳ {{ number_format($balanceSheetData['inventory_valuation'], 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-3">
                                            <strong class="d-block text-dark" style="font-size: 12px;">Accounts Receivable (Courier In-Transit)</strong>
                                            <small class="text-muted font-monospace" style="font-size: 10px;">Dispatched shipments awaiting delivery &amp; COD collection</small>
                                        </td>
                                        <td class="text-end pe-3 font-monospace bn-digit">৳ {{ number_format($balanceSheetData['courier_receivables'], 2) }}</td>
                                    </tr>
                                    <tr class="bg-light fw-bold">
                                        <td class="ps-3 text-secondary">Total Current Assets</td>
                                        <td class="text-end pe-3 text-primary font-monospace bn-digit">৳ {{ number_format($balanceSheetData['total_current_assets'], 2) }}</td>
                                    </tr>

                                    <!-- NON-CURRENT ASSETS -->
                                    <tr class="statement-section-header">
                                        <td colspan="2"><i class="fas fa-building me-1" style="color: #0d9488;"></i> Non-Current &amp; Fixed Assets</td>
                                    </tr>
                                    @if(isset($fixedAssets) && $fixedAssets->isNotEmpty())
                                        @foreach($fixedAssets as $ast)
                                            <tr>
                                                <td class="ps-3">
                                                    <span class="text-dark">{{ ucwords(str_replace('_', ' ', $ast->asset_type)) }}</span>
                                                    <small class="text-muted font-monospace bn-digit" style="font-size: 10px;">({{ $ast->count }} units registered)</small>
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
                                        <td class="ps-3 text-secondary">Total Fixed Assets (Property &amp; Equipment)</td>
                                        <td class="text-end pe-3 font-monospace bn-digit" style="color: #0d9488;">৳ {{ number_format($balanceSheetData['total_fixed_assets'], 2) }}</td>
                                    </tr>

                                    <!-- TOTAL ASSETS -->
                                    <tr class="bg-light border-top border-2 fw-bold" style="font-size: 12.5px;">
                                        <td class="py-2.5 ps-3">
                                            <span class="text-dark">TOTAL CORPORATE ASSETS</span>
                                            <small class="d-block text-muted fw-normal" style="font-size: 10px;">Current Liquid Assets + Capitalized Fixed Assets</small>
                                        </td>
                                        <td class="text-end pe-3 py-2.5 font-monospace bn-digit text-success fw-bold" style="font-size: 13.5px;">
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

        <!-- Right Column: Liabilities & Equity -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm report-card overflow-hidden h-100">
                <div class="report-card-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                        <i class="fas fa-hand-holding-dollar" style="color: #6366f1;"></i> <span>2. Liabilities &amp; Owner's Equity</span>
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
                                        <th>Obligation / Equity Account</th>
                                        <th class="text-end pe-3" style="width: 150px;">Balance (৳)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- LIABILITIES -->
                                    <tr class="statement-section-header">
                                        <td colspan="2"><i class="fas fa-file-invoice me-1 text-danger"></i> Current &amp; Long-Term Liabilities</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-3">
                                            <strong class="d-block text-dark" style="font-size: 12px;">Accounts Payable &amp; Outstanding Dues</strong>
                                            <small class="text-muted font-monospace" style="font-size: 10px;">Supplier balances, bills due &amp; pending payables</small>
                                        </td>
                                        <td class="text-end pe-3 font-monospace bn-digit">৳ {{ number_format($balanceSheetData['current_liabilities'], 2) }}</td>
                                    </tr>
                                    <tr class="bg-light fw-bold">
                                        <td class="ps-3 text-secondary">Total Liabilities</td>
                                        <td class="text-end pe-3 text-danger font-monospace bn-digit">৳ {{ number_format($balanceSheetData['current_liabilities'], 2) }}</td>
                                    </tr>

                                    <!-- OWNER'S EQUITY -->
                                    <tr class="statement-section-header">
                                        <td colspan="2"><i class="fas fa-chart-pie me-1" style="color: #6366f1;"></i> Equity, Capital &amp; Retained Earnings</td>
                                    </tr>
                                    @if(isset($investorBreakdown) && $investorBreakdown->isNotEmpty())
                                        @foreach($investorBreakdown as $inv)
                                            <tr>
                                                <td class="ps-3">
                                                    <span class="text-dark">{{ $inv->investor_name }}</span>
                                                    <small class="text-muted" style="font-size: 10px;">(Contributed Equity)</small>
                                                </td>
                                                <td class="text-end pe-3 font-monospace bn-digit">৳ {{ number_format((float)$inv->total_capital, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td class="ps-3 text-muted">Contributed Investor Capital</td>
                                            <td class="text-end pe-3 font-monospace bn-digit">৳ {{ number_format($balanceSheetData['total_invested_capital'], 2) }}</td>
                                        </tr>
                                    @endif
                                    <tr class="bg-light">
                                        <td class="ps-3 text-secondary fw-semibold">Total Invested Capital</td>
                                        <td class="text-end pe-3 font-monospace bn-digit fw-bold" style="color: #6366f1;">৳ {{ number_format($balanceSheetData['total_invested_capital'], 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-3">
                                            <strong class="d-block text-dark" style="font-size: 12px;">Cumulative Retained Earnings</strong>
                                            <small class="text-muted font-monospace" style="font-size: 10px;">Net lifetime operating profits reconciled from P&amp;L</small>
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
                                        <td class="py-2.5 ps-3">
                                            <span class="text-dark">TOTAL LIABILITIES &amp; EQUITY</span>
                                            <small class="d-block text-muted fw-normal" style="font-size: 10px;">Current Liabilities + Contributed Capital + Retained Earnings</small>
                                        </td>
                                        <td class="text-end pe-3 py-2.5 font-monospace bn-digit {{ abs($balanceSheetData['difference']) <= 1 ? 'text-success' : 'text-warning' }} fw-bold" style="font-size: 13.5px;">
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

    <!-- 6. Bottom Breakdown Ledger Cards (Collapsible) -->
    <div class="row g-3 mt-1 no-print">
        <!-- Bank Accounts Breakdown -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm report-card p-3">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                        <i class="fas fa-building-columns text-primary"></i> <span>Bank &amp; Cash Accounts Detail</span>
                    </h6>
                    <a href="{{ url('/admin/accounts/bank-balance') }}" class="small text-primary text-decoration-none fw-semibold" style="font-size: 11px;">Manage Accounts</a>
                </div>
                @if(isset($bankAccounts) && $bankAccounts->isNotEmpty())
                    @foreach($bankAccounts as $acc)
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <div>
                                <strong class="d-block text-dark small">{{ $acc->account_name }}</strong>
                                <small class="text-muted text-uppercase font-monospace" style="font-size: 9.5px;">{{ $acc->account_type }}</small>
                            </div>
                            <span class="fw-bold font-monospace bn-digit {{ (float)$acc->current_balance >= 0 ? 'text-dark' : 'text-danger' }}" style="font-size: 12px;">
                                ৳ {{ number_format((float)$acc->current_balance, 2) }}
                            </span>
                        </div>
                    @endforeach
                    <div class="d-flex justify-content-between align-items-center pt-2">
                        <strong class="text-dark small">Total Liquid Reserve:</strong>
                        <strong class="text-primary font-monospace bn-digit">৳ {{ number_format($balanceSheetData['total_bank_cash'], 2) }}</strong>
                    </div>
                @else
                    <p class="text-muted small mb-0 py-2 text-center">No bank accounts configured.</p>
                @endif
            </div>
        </div>

        <!-- Capital Investment Sources Breakdown -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm report-card p-3">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                        <i class="fas fa-handshake" style="color: #6366f1;"></i> <span>Capital Investors Portfolio</span>
                    </h6>
                    <a href="{{ url('/admin/accounts/investment') }}" class="small text-primary text-decoration-none fw-semibold" style="font-size: 11px;">View Ledger</a>
                </div>
                @if(isset($investorBreakdown) && $investorBreakdown->isNotEmpty())
                    @foreach($investorBreakdown as $inv)
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span class="text-dark small">{{ $inv->investor_name }}</span>
                            <span class="fw-bold font-monospace bn-digit" style="color: #6366f1; font-size: 12px;">৳ {{ number_format((float)$inv->total_capital, 2) }}</span>
                        </div>
                    @endforeach
                    <div class="d-flex justify-content-between align-items-center pt-2">
                        <strong class="text-dark small">Total Capital Invested:</strong>
                        <strong class="font-monospace bn-digit" style="color: #6366f1;">৳ {{ number_format($balanceSheetData['total_invested_capital'], 2) }}</strong>
                    </div>
                @else
                    <p class="text-muted small mb-0 py-2 text-center">No investor contributions logged.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection