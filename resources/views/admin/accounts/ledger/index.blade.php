@extends('layouts.admin')

@section('title', 'Unified General Account Ledger - ' . config('app.name'))

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #ledger-root .btn,
    #ledger-root .btn-sm,
    #ledger-root button,
    #ledger-root .badge,
    #ledger-root .alert,
    #ledger-root .form-control,
    #ledger-root .form-select,
    #ledger-root .page-link,
    #ledger-root .page-item span,
    #ledger-root .card,
    #ledger-root .report-card,
    #ledger-root .kpi-stat-card {
        border-radius: 5px !important;
    }

    #ledger-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #ledger-root .input-group > .form-control,
    #ledger-root .input-group > .form-select {
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
    .kpi-credit::before { background-color: #10b981; }
    .kpi-debit::before  { background-color: #ef4444; }
    .kpi-net::before    { background-color: #0284c7; }
    .kpi-bank::before   { background-color: #6366f1; }

    /* Ledger Table Layout: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .ledger-table {
        width: 100%;
        margin-bottom: 0;
    }
    .ledger-table thead th {
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
    .ledger-table tbody td {
        padding: 8px 6px;
        vertical-align: middle;
        font-size: 11.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .ledger-table tbody tr:hover {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .ledger-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .ledger-table th,
        .ledger-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 7 Columns Proportional Distribution Summing to Exactly 100% */
        .col-ld-id     { width: 7% !important; }
        .col-ld-date   { width: 12% !important; }
        .col-ld-acc    { width: 15% !important; }
        .col-ld-ref    { width: 12% !important; }
        .col-ld-note   { width: 30% !important; }
        .col-ld-debit  { width: 12% !important; }
        .col-ld-credit { width: 12% !important; }
    }

    /* Form Controls */
    .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #ledger-root .form-control,
    #ledger-root .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease;
    }
    #ledger-root .form-control:focus,
    #ledger-root .form-select:focus {
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
        background-color: #03172e !important;
        border-color: #03172e !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 3px rgba(3, 23, 46, 0.25) !important;
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
        #ledger-root .no-print, .admin-dark-sidebar, .admin-header, .main-footer { display: none !important; }
        #ledger-root { padding: 0 !important; margin: 0 !important; width: 100% !important; }
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
        .ledger-table {
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

<div class="container-fluid py-3 px-2 px-md-4" id="ledger-root">

    <!-- 1. Header (Original Clean White/Light Theme) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 header-top-wrap no-print">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-book-journal-whills text-primary"></i>
                <span>Unified General Account Ledger</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Consolidated real-time cashbook, debit &amp; credit audit trails across bank accounts and cash vaults
            </small>
        </div>
        <div class="top-action-btns d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-dark btn-compact shadow-xs fw-semibold" onclick="window.print()">
                <i class="fas fa-print me-1"></i> <span>Print Ledger</span>
            </button>
            <a href="{{ url('/admin/accounts/ledger/export-csv') }}?{{ http_build_query(request()->all()) }}" class="btn btn-sm btn-outline-success btn-compact shadow-xs">
                <i class="fas fa-file-csv me-1"></i> <span>Export CSV</span>
            </a>
            <a href="{{ url('/admin/accounts/bank-balance') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-building-columns me-1"></i> <span>Bank Balances</span>
            </a>
            <a href="{{ route('admin.report.cash_flow') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-money-bill-trend-up me-1 text-primary"></i> <span>Cash Flow</span>
            </a>
        </div>
    </div>

    <!-- 2. KPI Metric Summary Cards (Original Clean Theme) -->
    <div class="row g-2 mb-3">
        <!-- Total Credits (Inflows) -->
        <div class="col-6 col-lg-3">
            <div class="report-card kpi-stat-card kpi-credit shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Total Credits (Inflow)</span>
                <h5 class="fw-bold text-success mb-0 font-monospace bn-digit">৳ {{ number_format($totalCredits, 2) }}</h5>
                <small class="text-muted" style="font-size: 10px;">Incomes, sales &amp; investments</small>
            </div>
        </div>

        <!-- Total Debits (Outflows) -->
        <div class="col-6 col-lg-3">
            <div class="report-card kpi-stat-card kpi-debit shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Total Debits (Outflow)</span>
                <h5 class="fw-bold text-danger mb-0 font-monospace bn-digit">৳ {{ number_format($totalDebits, 2) }}</h5>
                <small class="text-muted" style="font-size: 10px;">Expenses, assets &amp; withdrawals</small>
            </div>
        </div>

        <!-- Net Period Movement -->
        <div class="col-6 col-lg-3">
            <div class="report-card kpi-stat-card kpi-net shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Net Period Movement</span>
                <h5 class="fw-bold mb-0 font-monospace bn-digit {{ $netMovement >= 0 ? 'text-primary' : 'text-danger' }}">
                    {{ $netMovement >= 0 ? '+' : '' }}৳ {{ number_format($netMovement, 2) }}
                </h5>
                <small class="text-muted" style="font-size: 10px;">Total Credits minus Total Debits</small>
            </div>
        </div>

        <!-- Total Liquid Bank Cash -->
        <div class="col-6 col-lg-3">
            <div class="report-card kpi-stat-card kpi-bank shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Closing Reserve</span>
                <h5 class="fw-bold mb-0 font-monospace bn-digit" style="color: #6366f1;">৳ {{ number_format($totalClosingBalance, 2) }}</h5>
                <small class="text-muted" style="font-size: 10px;">All active liquid cash reserves</small>
            </div>
        </div>
    </div>

    <!-- 3. Filter Toolbar (Clean & Symmetric) -->
    <div class="card border-0 shadow-sm report-card mb-3 p-3 no-print">
        <form action="{{ url()->current() }}" method="GET" class="row g-2 align-items-end">
            <!-- Date Filter -->
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label">Reporting Period</label>
                <select name="date_filter" id="ledgerDateFilter" class="form-select font-monospace" onchange="toggleCustomDates(this.value)">
                    <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ $dateFilter === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="this_week" {{ $dateFilter === 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ $dateFilter === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $dateFilter === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="this_year" {{ $dateFilter === 'this_year' ? 'selected' : '' }}>This Year</option>
                    <option value="all" {{ $dateFilter === 'all' ? 'selected' : '' }}>All Time</option>
                    <option value="custom" {{ $dateFilter === 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>

            <!-- Custom Date Range -->
            <div class="col-6 col-sm-3 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="customStartCol">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control font-monospace bn-digit" value="{{ request('start_date', $start->format('Y-m-d')) }}">
            </div>
            <div class="col-6 col-sm-3 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="customEndCol">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control font-monospace bn-digit" value="{{ request('end_date', $end->format('Y-m-d')) }}">
            </div>

            <!-- Bank / Cash Account Dropdown -->
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label">Account / Drawer</label>
                <select name="bank_account_id" class="form-select font-monospace">
                    <option value="all">All Bank &amp; Vaults</option>
                    @foreach($bankAccounts as $acc)
                        <option value="{{ $acc->id }}" {{ (string)request('bank_account_id') === (string)$acc->id ? 'selected' : '' }}>
                            {{ $acc->account_name }} ({{ strtoupper($acc->account_type) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Transaction Flow Type -->
            <div class="col-6 col-sm-3 col-md-2">
                <label class="form-label">Flow Type</label>
                <select name="transaction_type" class="form-select font-monospace">
                    <option value="all">All Flows (Dr / Cr)</option>
                    <option value="credit" {{ request('transaction_type') === 'credit' ? 'selected' : '' }}>Credit Inflow (+)</option>
                    <option value="debit" {{ request('transaction_type') === 'debit' ? 'selected' : '' }}>Debit Outflow (-)</option>
                </select>
            </div>

            <!-- Search Keyword -->
            <div class="col-6 col-sm-3 col-md-2">
                <label class="form-label">Search Particulars</label>
                <input type="text" name="search" class="form-control" placeholder="Note, module..." value="{{ request('search') }}">
            </div>

            <!-- Actions -->
            <div class="col-12 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-dark btn-compact flex-grow-1 shadow-xs fw-bold">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if(request()->hasAny(['date_filter', 'bank_account_id', 'transaction_type', 'search', 'start_date', 'end_date']))
                    <a href="{{ url()->current() }}" class="btn btn-light border btn-compact shadow-xs">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- 4. Ledger Audit Table Card -->
    <div class="card border-0 shadow-sm report-card overflow-hidden">
        <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                <i class="fas fa-list-check text-primary"></i> <span>Transaction Ledger Entries</span>
            </h6>
            <span class="badge bg-light text-secondary border font-monospace px-2 py-0.5" style="font-size: 10.5px;">
                Records: {{ $paginatedRecords->total() }}
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive pc-fit-container">
                <table class="table align-middle ledger-table mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3 col-ld-id text-center">Txn ID</th>
                            <th class="col-ld-date">Date &amp; Time</th>
                            <th class="col-ld-acc">Account / Drawer</th>
                            <th class="col-ld-ref">Module / Ref</th>
                            <th class="col-ld-note">Particulars &amp; Description</th>
                            <th class="text-end col-ld-debit">Debit (-)</th>
                            <th class="text-end pe-3 col-ld-credit">Credit (+)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paginatedRecords as $txn)
                            @php
                                $isCredit = ($txn->type === 'credit');
                                $ref = strtolower($txn->reference_type ?? '');
                                $refBadgeClass = 'bg-light text-secondary border';
                                if (str_contains($ref, 'income') || str_contains($ref, 'inc')) {
                                    $refBadgeClass = 'bg-success-subtle text-success border border-success-subtle';
                                } elseif (str_contains($ref, 'expense') || str_contains($ref, 'exp')) {
                                    $refBadgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                                } elseif (str_contains($ref, 'invest') || str_contains($ref, 'inv')) {
                                    $refBadgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
                                } elseif (str_contains($ref, 'asset') || str_contains($ref, 'ast')) {
                                    $refBadgeClass = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                                } elseif (str_contains($ref, 'transfer') || str_contains($ref, 'trf')) {
                                    $refBadgeClass = 'bg-info-subtle text-info border border-info-subtle';
                                }
                            @endphp
                            <tr>
                                <td class="ps-3 col-ld-id text-center font-monospace bn-digit text-muted" style="font-size: 10.5px;">
                                    <span class="badge bg-light text-dark border font-monospace px-1.5 py-0">
                                        #{{ $txn->id }}
                                    </span>
                                </td>
                                <td class="col-ld-date font-monospace bn-digit text-muted" style="font-size: 10.5px;">
                                    <span class="d-block text-dark">
                                        {{ date('d M Y', strtotime($txn->transaction_date)) }}
                                    </span>
                                    @if(strlen((string)$txn->transaction_date) > 10 && !str_contains($txn->transaction_date, '00:00:00'))
                                        <small class="text-muted font-monospace" style="font-size: 9.5px;">
                                            {{ date('h:i A', strtotime($txn->transaction_date)) }}
                                        </small>
                                    @endif
                                </td>
                                <td class="col-ld-acc">
                                    <strong class="d-block text-dark text-truncate" style="font-size: 12px;" title="{{ $txn->account_name ?? 'Cash / Vault' }}">
                                        {{ $txn->account_name ?? 'Cash / Vault' }}
                                    </strong>
                                    <span class="badge bg-secondary-subtle text-secondary text-uppercase border font-monospace" style="font-size: 8.5px; padding: 1px 4px;">
                                        {{ $txn->account_type ?? 'Cash' }}
                                    </span>
                                </td>
                                <td class="col-ld-ref">
                                    <span class="badge {{ $refBadgeClass }} font-monospace text-truncate d-inline-block px-1.5 py-0" style="font-size: 9.5px; max-width: 100%;">
                                        {{ $txn->reference_type ?? 'General' }}
                                    </span>
                                </td>
                                <td class="col-ld-note">
                                    <span class="text-dark d-block text-truncate fw-medium" style="font-size: 11px;" title="{{ $txn->note ?? 'Operational Transaction' }}">
                                        {{ $txn->note ?? 'Operational Transaction' }}
                                    </span>
                                </td>
                                <td class="text-end col-ld-debit font-monospace bn-digit {{ !$isCredit ? 'text-danger fw-bold' : 'text-muted' }}" style="font-size: 11.5px;">
                                    @if(!$isCredit)
                                        -৳ {{ number_format((float)$txn->amount, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end pe-3 col-ld-credit font-monospace bn-digit {{ $isCredit ? 'text-success fw-bold' : 'text-muted' }}" style="font-size: 11.5px;">
                                    @if($isCredit)
                                        +৳ {{ number_format((float)$txn->amount, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted small">
                                    <i class="fas fa-receipt fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                    <span class="fw-semibold">No ledger entries recorded for the selected period and account criteria.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($paginatedRecords->isNotEmpty())
                        <tfoot>
                            <tr class="bg-light fw-bold" style="font-size: 11.5px;">
                                <td colspan="5" class="ps-3 text-secondary">Period Filtered Totals:</td>
                                <td class="text-end font-monospace bn-digit text-danger">-৳ {{ number_format($totalDebits, 2) }}</td>
                                <td class="text-end pe-3 font-monospace bn-digit text-success">+৳ {{ number_format($totalCredits, 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            <!-- Bulletproof Responsive Pagination -->
            @if($paginatedRecords->hasPages())
                <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">
                    <small class="text-muted bn-digit font-monospace" style="font-size: 11px;">
                        Showing {{ $paginatedRecords->firstItem() }} to {{ $paginatedRecords->lastItem() }} of {{ $paginatedRecords->total() }} entries
                    </small>
                    <div>
                        {{ $paginatedRecords->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function toggleCustomDates(val) {
    const s = document.getElementById('customStartCol');
    const e = document.getElementById('customEndCol');
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