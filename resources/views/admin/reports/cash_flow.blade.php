@extends('layouts.admin')

@section('title', 'Statement of Cash Flows (IAS 7) - ' . config('app.name'))

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #cash-flow-root .btn,
    #cash-flow-root .btn-sm,
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
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #cash-flow-root .input-group > .form-control,
    #cash-flow-root .input-group > .form-select {
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
    .kpi-cfo::before { background-color: #10b981; }
    .kpi-cfi::before { background-color: #ef4444; }
    .kpi-cff::before { background-color: #6366f1; }
    .kpi-net::before { background-color: #0284c7; }

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

    @media (min-width: 992px) {
        .statement-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .statement-table th,
        .statement-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 3 Columns Proportional Distribution Summing to Exactly 100% */
        .col-cf-title  { width: 60% !important; }
        .col-cf-impact { width: 18% !important; }
        .col-cf-amount { width: 22% !important; }
    }

    /* Form Controls */
    .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #cash-flow-root .form-control,
    #cash-flow-root .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease;
    }
    #cash-flow-root .form-control:focus,
    #cash-flow-root .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Print Styles */
    @media print {
        @page { size: A4 portrait; margin: 8mm; }
        body { background: #ffffff !important; color: #000000 !important; }
        #cash-flow-root .no-print, .admin-dark-sidebar, .admin-header, .main-footer { display: none !important; }
        #cash-flow-root { padding: 0 !important; margin: 0 !important; width: 100% !important; }
        .report-card { border: 1px solid #000000 !important; box-shadow: none !important; margin-bottom: 10px !important; }
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
            min-width: 520px !important;
            table-layout: auto !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="cash-flow-root">

    <!-- 1. Header (Original Clean Light Theme) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 header-top-wrap no-print">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-money-bill-trend-up text-primary"></i>
                <span>Statement of Cash Flows</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Audited cash inflows and outflows across Operating, Investing, and Financing activities (IAS 7)
            </small>
        </div>
        <div class="top-action-btns d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-dark btn-compact shadow-xs fw-semibold" onclick="window.print()">
                <i class="fas fa-print me-1"></i> <span>Print A4 Statement</span>
            </button>
            <a href="{{ route('admin.report.export_cash_flow') }}?{{ http_build_query(request()->all()) }}" class="btn btn-sm btn-outline-success btn-compact shadow-xs">
                <i class="fas fa-file-csv me-1"></i> <span>Export CSV</span>
            </a>
            <a href="{{ route('admin.report.profit_loss') }}" class="btn btn-sm btn-outline-primary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-file-invoice-dollar me-1"></i> <span>Profit &amp; Loss</span>
            </a>
            <a href="{{ route('admin.report.balance_sheet') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-scale-balanced me-1"></i> <span>Balance Sheet</span>
            </a>
        </div>
    </div>

    <!-- 2. Filter Toolbar (Clean & Symmetric) -->
    <div class="card border-0 shadow-sm report-card mb-3 p-3 no-print">
        <form action="{{ route('admin.report.cash_flow') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label">Reporting Period</label>
                <select name="date_filter" id="cashFlowDateFilter" class="form-select font-monospace" onchange="toggleCustomCashFlowDates(this.value)">
                    <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ $dateFilter === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="this_week" {{ $dateFilter === 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ $dateFilter === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $dateFilter === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="this_year" {{ $dateFilter === 'this_year' ? 'selected' : '' }}>This Year</option>
                    <option value="custom" {{ $dateFilter === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                </select>
            </div>

            <div class="col-6 col-md-3 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="cfStartCol">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control font-monospace bn-digit" value="{{ request('start_date', $start->format('Y-m-d')) }}">
            </div>

            <div class="col-6 col-md-3 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="cfEndCol">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control font-monospace bn-digit" value="{{ request('end_date', $end->format('Y-m-d')) }}">
            </div>

            <div class="col-12 col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-dark btn-compact shadow-xs fw-bold px-3">
                    <i class="fas fa-filter me-1"></i> Apply Filter
                </button>
                @if(request()->filled('date_filter') && request('date_filter') !== 'this_month')
                    <a href="{{ route('admin.report.cash_flow') }}" class="btn btn-light border btn-compact shadow-xs">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- 3. KPI Metric Summary Cards (Collapsible) -->
    <div class="report-card mb-3">
        <div class="report-card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-chart-pie text-primary"></i>
                <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">Cash Flow Movement Summary</h6>
            </div>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseCashFlowKpis" aria-expanded="true" aria-controls="collapseCashFlowKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseCashFlowKpis">
            <div class="p-3 bg-light bg-opacity-25">
                <div class="row g-2">
                    <!-- Operating Cash Flow -->
                    <div class="col-6 col-lg-3">
                        <div class="kpi-stat-card kpi-cfo shadow-xs">
                            <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Operating Cash (CFO)</span>
                            <h5 class="fw-bold mb-0 font-monospace bn-digit {{ $cashFlowSummary['net_operating_cash'] >= 0 ? 'text-success' : 'text-danger' }}">
                                ৳ {{ number_format($cashFlowSummary['net_operating_cash'], 2) }}
                            </h5>
                            <small class="text-muted font-monospace" style="font-size: 10px;">Core ecommerce operations</small>
                        </div>
                    </div>

                    <!-- Investing Cash Flow -->
                    <div class="col-6 col-lg-3">
                        <div class="kpi-stat-card kpi-cfi shadow-xs">
                            <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Investing Cash (CFI)</span>
                            <h5 class="fw-bold text-danger mb-0 font-monospace bn-digit">
                                ৳ {{ number_format($cashFlowSummary['net_investing_cash'], 2) }}
                            </h5>
                            <small class="text-muted font-monospace" style="font-size: 10px;">Fixed assets capital expenditures</small>
                        </div>
                    </div>

                    <!-- Financing Cash Flow -->
                    <div class="col-6 col-lg-3">
                        <div class="kpi-stat-card kpi-cff shadow-xs">
                            <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Financing Cash (CFF)</span>
                            <h5 class="fw-bold mb-0 font-monospace bn-digit" style="color: #6366f1;">
                                ৳ {{ number_format($cashFlowSummary['net_financing_cash'], 2) }}
                            </h5>
                            <small class="text-muted font-monospace" style="font-size: 10px;">Investor &amp; owner capital</small>
                        </div>
                    </div>

                    <!-- Net Cash Surplus / Closing Reserve -->
                    <div class="col-6 col-lg-3">
                        <div class="kpi-stat-card kpi-net shadow-xs">
                            <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Closing Liquid Cash</span>
                            <h5 class="fw-bold text-primary mb-0 font-monospace bn-digit">
                                ৳ {{ number_format($cashFlowSummary['closing_balance'], 2) }}
                            </h5>
                            <small class="text-primary fw-bold font-monospace" style="font-size: 10px;">All Bank &amp; Cash Accounts</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Statement Table Card (Collapsible) -->
    <div class="card border-0 shadow-sm report-card overflow-hidden">
        <div class="report-card-header d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                <i class="fas fa-file-invoice-dollar text-primary"></i> <span>Statement of Cash Flows (IAS 7)</span>
            </h6>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border font-monospace" style="font-size: 10px;">
                    Period: {{ $cashFlowSummary['period_label'] }}
                </span>
                <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseStatementTable" aria-expanded="true" aria-controls="collapseStatementTable" title="Toggle Statement">
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
                                <th class="col-cf-title ps-3">Cash Flow Activity / Particulars</th>
                                <th class="col-cf-impact text-center">Impact</th>
                                <th class="col-cf-amount text-end pe-3">Amount (৳)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- 1. OPERATING ACTIVITIES -->
                            <tr class="statement-section-header">
                                <td colspan="3"><i class="fas fa-cart-shopping me-1 text-success"></i> 1. Cash Flows from Operating Activities</td>
                            </tr>
                            <tr>
                                <td class="ps-3 col-cf-title">
                                    <strong class="d-block text-dark" style="font-size: 12px;">Cash Received from Customers (Delivered Sales)</strong>
                                    <small class="text-muted font-monospace" style="font-size: 10px;">Net sales revenue + delivery charge collected from delivered orders</small>
                                </td>
                                <td class="text-center col-cf-impact">
                                    <span class="badge bg-success-subtle text-success border font-monospace" style="font-size: 9.5px;">Inflow (+)</span>
                                </td>
                                <td class="text-end pe-3 col-cf-amount font-monospace bn-digit text-success">
                                    +৳ {{ number_format($cashFlowSummary['customer_cash_received'], 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-3 col-cf-title">
                                    <strong class="d-block text-dark" style="font-size: 12px;">Other Operating Business Inflows</strong>
                                    <small class="text-muted font-monospace" style="font-size: 10px;">Non-product revenue, commissions, scrap sales, other incomes</small>
                                </td>
                                <td class="text-center col-cf-impact">
                                    <span class="badge bg-success-subtle text-success border font-monospace" style="font-size: 9.5px;">Inflow (+)</span>
                                </td>
                                <td class="text-end pe-3 col-cf-amount font-monospace bn-digit text-success">
                                    +৳ {{ number_format($cashFlowSummary['operating_inflows'], 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-3 col-cf-title">
                                    <strong class="d-block text-dark" style="font-size: 12px;">Courier Shipping &amp; Logistics Outflow</strong>
                                    <small class="text-muted font-monospace" style="font-size: 10px;">Dispatch and delivery fees paid to courier partners</small>
                                </td>
                                <td class="text-center col-cf-impact">
                                    <span class="badge bg-danger-subtle text-danger border font-monospace" style="font-size: 9.5px;">Outflow (-)</span>
                                </td>
                                <td class="text-end pe-3 col-cf-amount font-monospace bn-digit text-danger">
                                    -৳ {{ number_format($cashFlowSummary['courier_cost_paid'], 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-3 col-cf-title">
                                    <strong class="d-block text-dark" style="font-size: 12px;">Operational Expenses Paid (OPEX)</strong>
                                    <small class="text-muted font-monospace" style="font-size: 10px;">Rent, digital ads, utilities, office tea/snacks, staff payments</small>
                                </td>
                                <td class="text-center col-cf-impact">
                                    <span class="badge bg-danger-subtle text-danger border font-monospace" style="font-size: 9.5px;">Outflow (-)</span>
                                </td>
                                <td class="text-end pe-3 col-cf-amount font-monospace bn-digit text-danger">
                                    -৳ {{ number_format($cashFlowSummary['operating_expenses_paid'], 2) }}
                                </td>
                            </tr>
                            <tr class="bg-light fw-bold">
                                <td class="ps-3 text-secondary" colspan="2">Net Cash Provided by Operating Activities (CFO)</td>
                                <td class="text-end pe-3 font-monospace bn-digit {{ $cashFlowSummary['net_operating_cash'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    ৳ {{ number_format($cashFlowSummary['net_operating_cash'], 2) }}
                                </td>
                            </tr>

                            <!-- 2. INVESTING ACTIVITIES -->
                            <tr class="statement-section-header">
                                <td colspan="3"><i class="fas fa-building me-1 text-danger"></i> 2. Cash Flows from Investing Activities</td>
                            </tr>
                            <tr>
                                <td class="ps-3 col-cf-title">
                                    <strong class="d-block text-dark" style="font-size: 12px;">Acquisition of Fixed Assets &amp; Equipment</strong>
                                    <small class="text-muted font-monospace" style="font-size: 10px;">Electronics, computers, printers, office furniture, machinery</small>
                                </td>
                                <td class="text-center col-cf-impact">
                                    <span class="badge bg-danger-subtle text-danger border font-monospace" style="font-size: 9.5px;">Outflow (-)</span>
                                </td>
                                <td class="text-end pe-3 col-cf-amount font-monospace bn-digit text-danger">
                                    -৳ {{ number_format($cashFlowSummary['asset_purchases'], 2) }}
                                </td>
                            </tr>
                            <tr class="bg-light fw-bold">
                                <td class="ps-3 text-secondary" colspan="2">Net Cash Used in Investing Activities (CFI)</td>
                                <td class="text-end pe-3 font-monospace bn-digit text-danger">
                                    ৳ {{ number_format($cashFlowSummary['net_investing_cash'], 2) }}
                                </td>
                            </tr>

                            <!-- 3. FINANCING ACTIVITIES -->
                            <tr class="statement-section-header">
                                <td colspan="3"><i class="fas fa-hand-holding-dollar me-1" style="color: #6366f1;"></i> 3. Cash Flows from Financing Activities</td>
                            </tr>
                            <tr>
                                <td class="ps-3 col-cf-title">
                                    <strong class="d-block text-dark" style="font-size: 12px;">Owner &amp; Investor Capital Contributed</strong>
                                    <small class="text-muted font-monospace" style="font-size: 10px;">Direct equity investments injected into bank/vault accounts</small>
                                </td>
                                <td class="text-center col-cf-impact">
                                    <span class="badge bg-success-subtle text-success border font-monospace" style="font-size: 9.5px;">Inflow (+)</span>
                                </td>
                                <td class="text-end pe-3 col-cf-amount font-monospace bn-digit" style="color: #6366f1;">
                                    +৳ {{ number_format($cashFlowSummary['capital_injections'], 2) }}
                                </td>
                            </tr>
                            <tr class="bg-light fw-bold">
                                <td class="ps-3 text-secondary" colspan="2">Net Cash Provided by Financing Activities (CFF)</td>
                                <td class="text-end pe-3 font-monospace bn-digit" style="color: #6366f1;">
                                    ৳ {{ number_format($cashFlowSummary['net_financing_cash'], 2) }}
                                </td>
                            </tr>

                            <!-- RECONCILIATION SUMMARY -->
                            <tr class="statement-section-header">
                                <td colspan="3"><i class="fas fa-scale-balanced me-1 text-primary"></i> 4. Net Cash Reconciliation</td>
                            </tr>
                            <tr>
                                <td class="ps-3 fw-bold text-dark" colspan="2">Net Increase / (Decrease) in Cash &amp; Cash Equivalents</td>
                                <td class="text-end pe-3 font-monospace bn-digit fw-bold {{ $cashFlowSummary['net_change_in_cash'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $cashFlowSummary['net_change_in_cash'] >= 0 ? '+' : '' }}৳ {{ number_format($cashFlowSummary['net_change_in_cash'], 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-3 text-secondary" colspan="2">Cash &amp; Cash Equivalents at Beginning of Period</td>
                                <td class="text-end pe-3 font-monospace bn-digit text-muted">
                                    ৳ {{ number_format($cashFlowSummary['opening_balance'], 2) }}
                                </td>
                            </tr>
                            <tr class="bg-light border-top border-2 fw-bold" style="font-size: 12.5px;">
                                <td class="py-2.5 ps-3 text-dark" colspan="2">
                                    CASH &amp; CASH EQUIVALENTS AT END OF PERIOD
                                    <small class="d-block text-muted fw-normal" style="font-size: 10px;">Matches active bank and vault balances</small>
                                </td>
                                <td class="text-end pe-3 py-2.5 font-monospace bn-digit text-success fw-bold" style="font-size: 13.5px;">
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