@extends('layouts.admin')

@section('title', 'Bank Account Statement - ' . ($account->account_name ?? 'Passbook') . ' - ' . config('app.name'))

@php
    $openingBalance = (float)($openingBalance ?? 0);
    $totalCredits   = (float)($totalCredits ?? 0);
    $totalDebits    = (float)($totalDebits ?? 0);
    $closingBalance = (float)($closingBalance ?? ($account->current_balance ?? 0));
    $dateFilter     = $dateFilter ?? 'this_month';
@endphp

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #bank-stmt-root .btn,
    #bank-stmt-root .btn-sm,
    #bank-stmt-root button,
    #bank-stmt-root .badge,
    #bank-stmt-root .alert,
    #bank-stmt-root .form-control,
    #bank-stmt-root .form-select,
    #bank-stmt-root .page-link,
    #bank-stmt-root .page-item span,
    #bank-stmt-root .card,
    #bank-stmt-root .stmt-card,
    #bank-stmt-root .kpi-stat-card {
        border-radius: 5px !important;
    }

    #bank-stmt-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #bank-stmt-root .input-group > .form-control,
    #bank-stmt-root .input-group > .form-select {
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
    .stmt-card {
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
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
    .kpi-opening::before { background-color: #64748b; }
    .kpi-inflow::before  { background-color: #10b981; }
    .kpi-outflow::before { background-color: #ef4444; }
    .kpi-closing::before { background-color: #0284c7; }

    /* Statement Table */
    .stmt-table {
        width: 100%;
        margin-bottom: 0;
    }
    .stmt-table thead th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 6px;
        white-space: nowrap;
    }
    .stmt-table tbody td {
        padding: 8px 6px;
        vertical-align: middle;
        font-size: 11.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .stmt-table tbody tr:hover {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .stmt-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .stmt-table th,
        .stmt-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 7 Columns Proportional Distribution Summing to Exactly 100% */
        .col-st-date    { width: 12% !important; }
        .col-st-ref     { width: 14% !important; }
        .col-st-type    { width: 11% !important; }
        .col-st-note    { width: 27% !important; }
        .col-st-debit   { width: 12% !important; }
        .col-st-credit  { width: 12% !important; }
        .col-st-balance { width: 12% !important; }
    }

    /* Form Controls */
    .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #bank-stmt-root .form-control,
    #bank-stmt-root .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease;
    }
    #bank-stmt-root .form-control:focus,
    #bank-stmt-root .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Bulletproof Responsive Pagination */
    .card-footer nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }
    .card-footer nav > div.d-sm-none,
    .card-footer nav > .d-sm-none {
        display: none !important;
    }
    .card-footer nav > div.d-none,
    .card-footer nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .card-footer nav p,
    .card-footer nav .small.text-muted,
    .card-footer nav div:has(> p),
    .card-footer nav > div.d-none > div:first-child,
    .card-footer nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }
    .card-footer .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 3px !important;
        padding: 0 !important;
        list-style: none !important;
    }
    .card-footer .page-item {
        display: inline-flex !important;
    }
    .card-footer .page-item .page-link,
    .card-footer .page-item span {
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
    .card-footer .page-item .page-link:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }
    .card-footer .page-item.active .page-link,
    .card-footer .page-item.active span {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25) !important;
    }
    .card-footer .page-item.disabled .page-link,
    .card-footer .page-item.disabled span {
        opacity: 0.45 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }
    .card-footer nav svg {
        width: 10px !important;
        height: 10px !important;
    }

    /* Print Optimization */
    @media print {
        @page { size: A4 landscape; margin: 8mm; }
        body { background: #ffffff !important; color: #000000 !important; }
        #bank-stmt-root .no-print, .admin-dark-sidebar, .admin-header, .main-footer { display: none !important; }
        #bank-stmt-root { padding: 0 !important; margin: 0 !important; width: 100% !important; }
        .stmt-card { border: 1px solid #000000 !important; box-shadow: none !important; margin-bottom: 10px !important; }
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
        .account-banner-grid {
            row-gap: 8px !important;
        }
        .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .stmt-table {
            min-width: 760px !important;
            table-layout: auto !important;
        }
        .card-footer {
            flex-direction: column !important;
            gap: 8px !important;
            justify-content: center !important;
        }
        .card-footer nav {
            width: 100% !important;
            justify-content: center !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="bank-stmt-root">

    <!-- 1. Header (Original Clean White/Light Theme) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 header-top-wrap no-print">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-file-invoice text-primary"></i> 
                <span>Account Statement: {{ $account->account_name ?? 'Account Passbook' }}</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Itemized audit passbook, credit deposits, debit withdrawals &amp; running balance
            </small>
        </div>
        <div class="top-action-btns d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-dark btn-compact shadow-xs fw-semibold" onclick="window.print()">
                <i class="fas fa-print me-1"></i> <span>Print Passbook</span>
            </button>
            <a href="{{ url('/admin/accounts/bank-balance') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-arrow-left me-1"></i> <span>All Accounts</span>
            </a>
            <a href="{{ url('/admin/accounts/ledger') }}" class="btn btn-sm btn-outline-primary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-book-journal-whills me-1"></i> <span>General Ledger</span>
            </a>
        </div>
    </div>

    <!-- 2. Account Details Banner Card (Original Clean Theme) -->
    <div class="card border-0 shadow-sm stmt-card p-3 mb-3">
        <div class="row g-2 align-items-center account-banner-grid">
            <div class="col-12 col-md-4">
                <span class="text-muted small d-block font-monospace" style="font-size: 10.5px; text-transform: uppercase;">ACCOUNT / VAULT NAME</span>
                <strong class="text-dark d-block text-truncate" style="font-size: 13.5px;" title="{{ $account->account_name ?? '—' }}">
                    {{ $account->account_name ?? '—' }}
                </strong>
                <span class="badge bg-secondary-subtle text-secondary border font-monospace text-uppercase" style="font-size: 9px;">
                    {{ str_replace('_', ' ', $account->account_type ?? 'Cash') }}
                </span>
            </div>
            <div class="col-6 col-md-3">
                <span class="text-muted small d-block font-monospace" style="font-size: 10.5px; text-transform: uppercase;">BANK / INSTITUTION</span>
                <strong class="text-dark d-block text-truncate" style="font-size: 12.5px;">{{ $account->bank_name ?? 'Physical Drawer / Vault' }}</strong>
                <small class="text-muted d-block font-monospace text-truncate" style="font-size: 10px;">{{ $account->account_number ? '#' . $account->account_number : 'Internal Cash' }}</small>
            </div>
            <div class="col-6 col-md-3">
                <span class="text-muted small d-block font-monospace" style="font-size: 10.5px; text-transform: uppercase;">BRANCH / OUTLET</span>
                <strong class="text-dark d-block text-truncate" style="font-size: 12.5px;">{{ $account->branch_name ?? 'Main Corporate Office' }}</strong>
                <small class="text-muted d-block" style="font-size: 10px;">Operational Vault</small>
            </div>
            <div class="col-12 col-md-2 text-md-end border-top-sm pt-2 pt-md-0">
                <span class="text-muted small d-block font-monospace" style="font-size: 10.5px; text-transform: uppercase;">CURRENT BALANCE</span>
                <h5 class="fw-bold text-primary mb-0 font-monospace bn-digit" style="font-size: 15px;">
                    ৳ {{ number_format((float)($account->current_balance ?? 0), 2) }}
                </h5>
            </div>
        </div>
    </div>

    <!-- 3. Filter Toolbar (Clean & Symmetric) -->
    <div class="card border-0 shadow-sm stmt-card mb-3 p-3 no-print">
        <form action="{{ url()->current() }}" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-sm-6 col-md-4">
                <label class="form-label">Reporting Period</label>
                <select name="date_filter" id="stmtDateFilter" class="form-select font-monospace" onchange="toggleCustomDates(this.value)">
                    <option value="today" {{ ($dateFilter ?? '') === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ ($dateFilter ?? '') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="this_week" {{ ($dateFilter ?? '') === 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ ($dateFilter ?? 'this_month') === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ ($dateFilter ?? '') === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="this_year" {{ ($dateFilter ?? '') === 'this_year' ? 'selected' : '' }}>This Year</option>
                    <option value="all" {{ ($dateFilter ?? '') === 'all' ? 'selected' : '' }}>All Time</option>
                    <option value="custom" {{ ($dateFilter ?? '') === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                </select>
            </div>

            <div class="col-6 col-sm-3 col-md-3 {{ ($dateFilter ?? '') === 'custom' ? '' : 'd-none' }}" id="stmtStartCol">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control font-monospace bn-digit" value="{{ request('start_date', isset($start) ? $start->format('Y-m-d') : date('Y-m-01')) }}">
            </div>

            <div class="col-6 col-sm-3 col-md-3 {{ ($dateFilter ?? '') === 'custom' ? '' : 'd-none' }}" id="stmtEndCol">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control font-monospace bn-digit" value="{{ request('end_date', isset($end) ? $end->format('Y-m-d') : date('Y-m-d')) }}">
            </div>

            <div class="col-12 col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-dark btn-compact shadow-xs fw-bold px-3">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if(request()->hasAny(['date_filter', 'start_date', 'end_date']))
                    <a href="{{ url()->current() }}" class="btn btn-light border btn-compact shadow-xs">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- 4. KPI Summary Cards (Original Clean Theme) -->
    <div class="row g-2 mb-3">
        <!-- Opening Balance -->
        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-opening shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Opening Balance</span>
                <h5 class="fw-bold text-secondary mb-0 font-monospace bn-digit">৳ {{ number_format($openingBalance, 2) }}</h5>
                <small class="text-muted" style="font-size: 10px;">At start of period</small>
            </div>
        </div>

        <!-- Total Inflows (Credit) -->
        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-inflow shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Total Inflow (Credit)</span>
                <h5 class="fw-bold text-success mb-0 font-monospace bn-digit">+৳ {{ number_format($totalCredits, 2) }}</h5>
                <small class="text-muted" style="font-size: 10px;">Deposits, income &amp; transfers in</small>
            </div>
        </div>

        <!-- Total Outflows (Debit) -->
        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-outflow shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Total Outflow (Debit)</span>
                <h5 class="fw-bold text-danger mb-0 font-monospace bn-digit">-৳ {{ number_format($totalDebits, 2) }}</h5>
                <small class="text-muted" style="font-size: 10px;">Expenses, assets &amp; transfers out</small>
            </div>
        </div>

        <!-- Closing Balance -->
        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-closing shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Closing Balance</span>
                <h5 class="fw-bold text-primary mb-0 font-monospace bn-digit">৳ {{ number_format($closingBalance, 2) }}</h5>
                <small class="text-muted" style="font-size: 10px;">Reconciled account reserve</small>
            </div>
        </div>
    </div>

    <!-- 5. Passbook / Statement Table -->
    <div class="card border-0 shadow-sm stmt-card overflow-hidden">
        <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                <i class="fas fa-list-check text-primary"></i> <span>Transaction Statement Entries</span>
            </h6>
            <span class="badge bg-light text-secondary border font-monospace px-2 py-0.5" style="font-size: 10.5px;">
                Entries: {{ isset($transactions) && method_exists($transactions, 'total') ? $transactions->total() : (isset($transactions) ? count($transactions) : 0) }}
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive pc-fit-container">
                <table class="table align-middle stmt-table mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3 col-st-date">Date</th>
                            <th class="col-st-ref">Reference #</th>
                            <th class="col-st-type text-center">Txn Type</th>
                            <th class="col-st-note">Particulars / Description</th>
                            <th class="text-end col-st-debit">Debit Out (-)</th>
                            <th class="text-end col-st-credit">Credit In (+)</th>
                            <th class="text-end pe-3 col-st-balance">Balance (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions ?? [] as $trx)
                            @php
                                $type = strtolower($trx->transaction_type ?? ($trx->type ?? 'credit'));
                                $isCredit = in_array($type, ['credit', 'deposit', 'inflow', 'investment', 'income']);
                                $amount = (float)($trx->amount ?? 0);
                            @endphp
                            <tr>
                                <td class="ps-3 col-st-date font-monospace bn-digit text-muted" style="font-size: 10.5px;">
                                    {{ date('d M Y', strtotime($trx->transaction_date ?? $trx->created_at)) }}
                                </td>
                                <td class="col-st-ref">
                                    <span class="badge bg-light text-secondary border font-monospace text-truncate d-inline-block px-1.5 py-0" style="font-size: 9.5px; max-width: 100%;">
                                        {{ $trx->reference_no ?? ($trx->reference_type ?? '#TRX-' . $trx->id) }}
                                    </span>
                                </td>
                                <td class="col-st-type text-center">
                                    <span class="badge {{ $isCredit ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }} font-monospace text-uppercase" style="font-size: 9px;">
                                        {{ strtoupper($type) }}
                                    </span>
                                </td>
                                <td class="col-st-note">
                                    <span class="text-dark d-block text-truncate fw-medium" style="font-size: 11px;" title="{{ $trx->note ?? ($trx->description ?? 'Account transaction entry') }}">
                                        {{ $trx->note ?? ($trx->description ?? 'Account transaction entry') }}
                                    </span>
                                </td>
                                <td class="text-end col-st-debit font-monospace bn-digit {{ !$isCredit ? 'text-danger fw-bold' : 'text-muted' }}" style="font-size: 11.5px;">
                                    {{ !$isCredit ? '-৳ ' . number_format($amount, 2) : '—' }}
                                </td>
                                <td class="text-end col-st-credit font-monospace bn-digit {{ $isCredit ? 'text-success fw-bold' : 'text-muted' }}" style="font-size: 11.5px;">
                                    {{ $isCredit ? '+৳ ' . number_format($amount, 2) : '—' }}
                                </td>
                                <td class="text-end pe-3 col-st-balance font-monospace bn-digit fw-bold text-dark" style="font-size: 11.5px;">
                                    ৳ {{ number_format((float)($trx->balance_after ?? 0), 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted small">
                                    <i class="fas fa-receipt fa-2x mb-2 d-block opacity-25"></i>
                                    <span class="fw-semibold">No transaction entries recorded for this account in the selected period.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-light fw-bold" style="font-size: 11.5px;">
                            <td colspan="4" class="ps-3 text-secondary">Period Summary Reconciled:</td>
                            <td class="text-end font-monospace bn-digit text-danger">-৳ {{ number_format($totalDebits, 2) }}</td>
                            <td class="text-end font-monospace bn-digit text-success">+৳ {{ number_format($totalCredits, 2) }}</td>
                            <td class="text-end pe-3 font-monospace bn-digit text-primary">৳ {{ number_format($closingBalance, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if(isset($transactions) && method_exists($transactions, 'hasPages') && $transactions->hasPages())
                <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">
                    <small class="text-muted bn-digit font-monospace" style="font-size: 11px;">
                        Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }} entries
                    </small>
                    <div>
                        {{ $transactions->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function toggleCustomDates(val) {
    const s = document.getElementById('stmtStartCol');
    const e = document.getElementById('stmtEndCol');
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