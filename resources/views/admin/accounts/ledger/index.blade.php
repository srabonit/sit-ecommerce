@extends('layouts.admin')

@section('title', 'Unified General Account Ledger - ' . config('app.name'))

@section('admin_content')
<style>
    #ledger-root {
        --ledger-primary: #2563eb;
        --ledger-primary-soft: #eff6ff;
        --ledger-border: #e2e8f0;
        --ledger-muted: #64748b;
        --ledger-text: #1e293b;
        --ledger-bg: #f8fafc;
        --ledger-radius: 5px;
        color: var(--ledger-text);
        font-size: 12px;
    }

    /* Consistent 5px Radius */
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
        border-radius: var(--ledger-radius) !important;
    }

    #ledger-root .input-group > .input-group-text {
        border-radius: 5px 0 0 5px !important;
    }

    #ledger-root .input-group > .form-control,
    #ledger-root .input-group > .form-select {
        border-radius: 0 5px 5px 0 !important;
    }

    #ledger-root .bn-digit,
    #ledger-root .font-monospace {
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }

    /* Buttons */
    #ledger-root .btn-compact {
        min-height: 32px;
        padding: 6px 11px !important;
        font-size: 11px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #ledger-root .btn-compact:hover {
        transform: translateY(-1px);
    }

    #ledger-root .btn-dark {
        background: #1e293b;
        border-color: #1e293b;
    }

    #ledger-root .btn-dark:hover {
        background: #0f172a;
        border-color: #0f172a;
    }

    /* Cards */
    #ledger-root .report-card {
        background: #fff;
        border: 1px solid var(--ledger-border) !important;
        box-shadow: 0 2px 7px rgba(15, 23, 42, .035);
        margin-bottom: 14px;
    }

    #ledger-root .kpi-stat-card {
        position: relative;
        height: 100%;
        min-height: 104px;
        padding: 13px 14px 12px 16px;
        overflow: hidden;
        border: 1px solid var(--ledger-border);
        background: #fff;
        transition: box-shadow .2s ease, transform .2s ease;
    }

    #ledger-root .kpi-stat-card:hover {
        box-shadow: 0 5px 14px rgba(15, 23, 42, .07);
        transform: translateY(-2px);
    }

    #ledger-root .kpi-stat-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
    }

    #ledger-root .kpi-credit::before { background: #10b981; }
    #ledger-root .kpi-debit::before { background: #ef4444; }
    #ledger-root .kpi-net::before { background: #2563eb; }
    #ledger-root .kpi-bank::before { background: #6366f1; }

    #ledger-root .kpi-label {
        display: block;
        color: var(--ledger-muted);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .035em;
        text-transform: uppercase;
        margin-bottom: 7px;
    }

    #ledger-root .kpi-value {
        font-size: clamp(15px, 1.4vw, 19px);
        font-weight: 750;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }

    #ledger-root .kpi-description {
        display: block;
        color: #94a3b8;
        font-size: 10px;
        margin-top: 5px;
    }

    /* Header */
    #ledger-root .page-heading {
        color: #0f172a;
        font-size: 18px;
        font-weight: 750;
        letter-spacing: -.35px;
    }

    #ledger-root .page-subtitle {
        color: var(--ledger-muted);
        font-size: 11.5px;
        line-height: 1.5;
    }

    #ledger-root .header-icon {
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--ledger-primary-soft);
        color: var(--ledger-primary);
        border-radius: 5px;
        font-size: 15px;
    }

    /* Filter */
    #ledger-root .filter-card {
        padding: 15px;
    }

    #ledger-root .form-label {
        display: block;
        color: #475569;
        font-size: 10.5px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    #ledger-root .form-control,
    #ledger-root .form-select {
        min-height: 34px;
        padding: 6px 9px;
        border: 1px solid #dbe2ea;
        background-color: #fff;
        color: #1e293b;
        font-size: 11.5px;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #ledger-root .form-control:focus,
    #ledger-root .form-select:focus {
        border-color: var(--ledger-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
        outline: none;
    }

    #ledger-root .form-control::placeholder {
        color: #a0aab8;
    }

    /* Ledger Table */
    #ledger-root .table-card {
        overflow: visible !important;
    }

    #ledger-root .table-heading {
        color: #1e293b;
        font-size: 13px;
        font-weight: 750;
    }

    #ledger-root .table-responsive,
    #ledger-root .pc-fit-container {
        width: 100%;
        max-width: 100%;
        overflow-x: auto !important;
        overflow-y: visible !important;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
    }

    #ledger-root .pc-fit-container::-webkit-scrollbar {
        height: 7px;
    }

    #ledger-root .pc-fit-container::-webkit-scrollbar-track {
        background: #f8fafc;
    }

    #ledger-root .pc-fit-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 5px;
    }

    #ledger-root .ledger-table {
        width: 100%;
        min-width: 1050px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: auto;
    }

    #ledger-root .ledger-table thead th {
        padding: 11px 10px;
        background: #f8fafc;
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
        font-size: 10px;
        font-weight: 750;
        letter-spacing: .035em;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }

    #ledger-root .ledger-table tbody td {
        padding: 10px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        font-size: 11px;
        line-height: 1.5;
        vertical-align: middle;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    #ledger-root .ledger-table tbody tr {
        transition: background-color .15s ease;
    }

    #ledger-root .ledger-table tbody tr:hover {
        background: #f8fbff;
    }

    #ledger-root .ledger-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #ledger-root .ledger-table tfoot td {
        padding: 11px 10px;
        border-top: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    #ledger-root .ledger-table .col-ld-id {
        min-width: 85px;
    }

    #ledger-root .ledger-table .col-ld-date {
        min-width: 125px;
    }

    #ledger-root .ledger-table .col-ld-acc {
        min-width: 155px;
    }

    #ledger-root .ledger-table .col-ld-ref {
        min-width: 135px;
    }

    #ledger-root .ledger-table .col-ld-note {
        min-width: 250px;
    }

    #ledger-root .ledger-table .col-ld-debit,
    #ledger-root .ledger-table .col-ld-credit {
        min-width: 145px;
        white-space: nowrap;
    }

    #ledger-root .ledger-table .badge {
        display: inline-block;
        max-width: 100%;
        white-space: normal;
        overflow-wrap: anywhere;
        line-height: 1.45;
    }

    #ledger-root .ledger-table .account-name,
    #ledger-root .ledger-table .transaction-note {
        display: block;
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: normal;
    }

    #ledger-root .ledger-table .account-type {
        display: inline-block;
        margin-top: 4px;
        font-size: 9px;
        padding: 2px 5px;
    }

    /* Pagination */
    #ledger-root .card-footer nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }

    #ledger-root .card-footer nav > div.d-sm-none,
    #ledger-root .card-footer nav > .d-sm-none {
        display: none !important;
    }

    #ledger-root .card-footer nav > div.d-none,
    #ledger-root .card-footer nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #ledger-root .card-footer nav p,
    #ledger-root .card-footer nav .small.text-muted,
    #ledger-root .card-footer nav div:has(> p),
    #ledger-root .card-footer nav > div.d-none > div:first-child,
    #ledger-root .card-footer nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }

    #ledger-root .card-footer .pagination {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    #ledger-root .card-footer .page-item {
        display: inline-flex !important;
    }

    #ledger-root .card-footer .page-item .page-link,
    #ledger-root .card-footer .page-item span {
        min-width: 27px !important;
        height: 27px !important;
        padding: 0 7px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #475569 !important;
        background: #fff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 5px !important;
        font-size: 10.5px !important;
        font-weight: 650 !important;
        line-height: 1 !important;
        text-decoration: none !important;
        transition: all .15s ease !important;
    }

    #ledger-root .card-footer .page-item .page-link:hover {
        color: var(--ledger-primary) !important;
        background: var(--ledger-primary-soft) !important;
        border-color: #bfdbfe !important;
    }

    #ledger-root .card-footer .page-item.active .page-link,
    #ledger-root .card-footer .page-item.active span {
        color: #fff !important;
        background: var(--ledger-primary) !important;
        border-color: var(--ledger-primary) !important;
        box-shadow: 0 2px 5px rgba(37, 99, 235, .2) !important;
    }

    #ledger-root .card-footer .page-item.disabled .page-link,
    #ledger-root .card-footer .page-item.disabled span {
        opacity: .45 !important;
        background: #f8fafc !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }

    #ledger-root .card-footer nav svg {
        width: 11px !important;
        height: 11px !important;
    }

    /* Empty State */
    #ledger-root .empty-state-icon {
        width: 45px;
        height: 45px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: #94a3b8;
        border-radius: 50%;
        font-size: 18px;
    }

    /* Print */
    @media print {
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        body {
            background: #fff !important;
            color: #000 !important;
        }

        #ledger-root .no-print,
        .admin-dark-sidebar,
        .admin-header,
        .main-footer {
            display: none !important;
        }

        #ledger-root {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        #ledger-root .report-card {
            border: 1px solid #000 !important;
            box-shadow: none !important;
            margin-bottom: 10px !important;
        }

        #ledger-root .pc-fit-container {
            overflow: visible !important;
        }

        #ledger-root .ledger-table {
            min-width: 0 !important;
            width: 100% !important;
            font-size: 9px !important;
        }

        #ledger-root .ledger-table th,
        #ledger-root .ledger-table td {
            padding: 5px !important;
        }
    }

    /* Tablet */
    @media (max-width: 991.98px) {
        #ledger-root .page-heading {
            font-size: 17px;
        }

        #ledger-root .filter-card {
            padding: 12px;
        }
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        #ledger-root {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #ledger-root .header-top-wrap {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 12px !important;
        }

        #ledger-root .top-action-btns {
            width: 100%;
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 7px !important;
        }

        #ledger-root .top-action-btns .btn {
            width: 100%;
            min-width: 0;
            justify-content: center;
            padding: 7px 5px !important;
            font-size: 10.5px !important;
        }

        #ledger-root .kpi-stat-card {
            min-height: 100px;
            padding: 11px 10px 10px 13px;
        }

        #ledger-root .kpi-label {
            font-size: 9px;
        }

        #ledger-root .kpi-value {
            font-size: 15px;
        }

        #ledger-root .kpi-description {
            font-size: 9px;
        }

        #ledger-root .filter-card {
            padding: 11px;
        }

        #ledger-root .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        #ledger-root .ledger-table {
            min-width: 1050px !important;
            table-layout: auto !important;
        }

        #ledger-root .card-footer {
            flex-direction: column !important;
            gap: 9px !important;
            justify-content: center !important;
            text-align: center;
        }

        #ledger-root .card-footer nav {
            width: 100% !important;
            justify-content: center !important;
        }
    }

    @media (max-width: 380px) {
        #ledger-root .page-heading {
            font-size: 16px;
        }

        #ledger-root .top-action-btns .btn {
            font-size: 10px !important;
        }

        #ledger-root .kpi-value {
            font-size: 14px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="ledger-root">

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3 header-top-wrap no-print">
        <div class="d-flex align-items-center gap-2">
            <div class="header-icon">
                <i class="fas fa-book-journal-whills"></i>
            </div>
            <div>
                <h5 class="page-heading mb-1">Unified General Account Ledger</h5>
                <small class="page-subtitle d-block">
                    Track cash, bank transactions, debits and credits
                </small>
            </div>
        </div>

        <div class="top-action-btns d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-dark btn-compact shadow-sm" onclick="window.print()">
                <i class="fas fa-print"></i>
                <span>Print</span>
            </button>

            <a href="{{ url('/admin/accounts/ledger/export-csv') }}?{{ http_build_query(request()->all()) }}" class="btn btn-sm btn-outline-success btn-compact shadow-sm">
                <i class="fas fa-file-csv"></i>
                <span>Export CSV</span>
            </a>

            <a href="{{ url('/admin/accounts/bank-balance') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-sm">
                <i class="fas fa-building-columns"></i>
                <span>Bank Balances</span>
            </a>

            <a href="{{ route('admin.report.cash_flow') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-sm">
                <i class="fas fa-money-bill-trend-up text-primary"></i>
                <span>Cash Flow</span>
            </a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-2 mb-3">

        <div class="col-6 col-lg-3">
            <div class="report-card kpi-stat-card kpi-credit mb-0">
                <span class="kpi-label">Total Credits</span>
                <div class="kpi-value text-success font-monospace bn-digit">
                    ৳ {{ number_format($totalCredits, 2) }}
                </div>
                <small class="kpi-description">Income, sales and investments</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="report-card kpi-stat-card kpi-debit mb-0">
                <span class="kpi-label">Total Debits</span>
                <div class="kpi-value text-danger font-monospace bn-digit">
                    ৳ {{ number_format($totalDebits, 2) }}
                </div>
                <small class="kpi-description">Expenses, assets and withdrawals</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="report-card kpi-stat-card kpi-net mb-0">
                <span class="kpi-label">Net Movement</span>
                <div class="kpi-value font-monospace bn-digit {{ $netMovement >= 0 ? 'text-primary' : 'text-danger' }}">
                    {{ $netMovement >= 0 ? '+' : '' }}৳ {{ number_format($netMovement, 2) }}
                </div>
                <small class="kpi-description">Credits minus debits</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="report-card kpi-stat-card kpi-bank mb-0">
                <span class="kpi-label">Closing Reserve</span>
                <div class="kpi-value font-monospace bn-digit" style="color: #6366f1;">
                    ৳ {{ number_format($totalClosingBalance, 2) }}
                </div>
                <small class="kpi-description">Active cash and bank reserves</small>
            </div>
        </div>

    </div>

    <!-- Filter Toolbar -->
    <div class="card report-card filter-card no-print">
        <form action="{{ url()->current() }}" method="GET" class="row g-2 align-items-end">

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

            <div class="col-6 col-sm-3 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="customStartCol">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control font-monospace bn-digit" value="{{ request('start_date', $start->format('Y-m-d')) }}">
            </div>

            <div class="col-6 col-sm-3 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="customEndCol">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control font-monospace bn-digit" value="{{ request('end_date', $end->format('Y-m-d')) }}">
            </div>

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

            <div class="col-6 col-sm-3 col-md-2">
                <label class="form-label">Flow Type</label>
                <select name="transaction_type" class="form-select font-monospace">
                    <option value="all">All Flows (Dr / Cr)</option>
                    <option value="credit" {{ request('transaction_type') === 'credit' ? 'selected' : '' }}>Credit Inflow (+)</option>
                    <option value="debit" {{ request('transaction_type') === 'debit' ? 'selected' : '' }}>Debit Outflow (-)</option>
                </select>
            </div>

            <div class="col-6 col-sm-3 col-md-2">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Note, module..." value="{{ request('search') }}">
            </div>

            <div class="col-12 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-dark btn-compact flex-grow-1 shadow-sm fw-bold">
                    <i class="fas fa-filter"></i>
                    Filter
                </button>

                @if(request()->hasAny(['date_filter', 'bank_account_id', 'transaction_type', 'search', 'start_date', 'end_date']))
                    <a href="{{ url()->current() }}" class="btn btn-light border btn-compact shadow-sm">
                        Reset
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Ledger Table -->
    <div class="card report-card table-card">

        <div class="card-header bg-white py-3 px-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="table-heading mb-0 d-flex align-items-center gap-2">
                <span class="header-icon" style="width: 29px; height: 29px; flex-basis: 29px; font-size: 12px;">
                    <i class="fas fa-list-check"></i>
                </span>
                <span>Transaction Ledger Entries</span>
            </h6>

            <span class="badge bg-light text-secondary border font-monospace px-2 py-2" style="font-size: 10px;">
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
                                    <span class="badge bg-light text-dark border font-monospace px-2 py-1">
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
                                    <strong class="account-name text-dark fw-semibold" style="font-size: 11.5px;" title="{{ $txn->account_name ?? 'Cash / Vault' }}">
                                        {{ $txn->account_name ?? 'Cash / Vault' }}
                                    </strong>

                                    <span class="badge bg-secondary-subtle text-secondary text-uppercase border font-monospace account-type">
                                        {{ $txn->account_type ?? 'Cash' }}
                                    </span>
                                </td>

                                <td class="col-ld-ref">
                                    <span class="badge {{ $refBadgeClass }} font-monospace px-2 py-1" style="font-size: 9.5px;">
                                        {{ $txn->reference_type ?? 'General' }}
                                    </span>
                                </td>

                                <td class="col-ld-note">
                                    <span class="transaction-note text-dark fw-medium" style="font-size: 11px;" title="{{ $txn->note ?? 'Operational Transaction' }}">
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
                                    <span class="empty-state-icon mb-3">
                                        <i class="fas fa-receipt"></i>
                                    </span>
                                    <span class="d-block fw-semibold">
                                        No ledger entries found for these filters.
                                    </span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if($paginatedRecords->isNotEmpty())
                        <tfoot>
                            <tr class="bg-light fw-bold" style="font-size: 11.5px;">
                                <td colspan="5" class="ps-3 text-secondary">
                                    Period Filtered Totals:
                                </td>
                                <td class="text-end font-monospace bn-digit text-danger">
                                    -৳ {{ number_format($totalDebits, 2) }}
                                </td>
                                <td class="text-end pe-3 font-monospace bn-digit text-success">
                                    +৳ {{ number_format($totalCredits, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif

                </table>
            </div>

            <!-- Pagination -->
            @if($paginatedRecords->hasPages())
                <div class="p-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">

                    <small class="text-muted bn-digit font-monospace" style="font-size: 10.5px;">
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