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
    /* =========================================================
       BANK STATEMENT — KAIADMIN CLASSIC
    ========================================================= */
    #bank-stmt-root {
        --stmt-primary: #2563eb;
        --stmt-primary-soft: #eff6ff;
        --stmt-success: #059669;
        --stmt-danger: #dc2626;
        --stmt-text: #1e293b;
        --stmt-muted: #64748b;
        --stmt-border: #e2e8f0;
        --stmt-bg: #f8fafc;
        --stmt-radius: 5px;
        color: var(--stmt-text);
        font-size: 12px;
    }

    #bank-stmt-root *,
    #bank-stmt-root *::before,
    #bank-stmt-root *::after {
        box-sizing: border-box;
    }

    /* Consistent Radius */
    #bank-stmt-root .btn,
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
        border-radius: var(--stmt-radius) !important;
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

    #bank-stmt-root .font-monospace,
    #bank-stmt-root .bn-digit {
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
    }

    /* Page Header */
    #bank-stmt-root .stmt-page-header {
        gap: 12px;
        margin-bottom: 16px;
    }

    #bank-stmt-root .stmt-page-title {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 0 0 4px;
        color: #1e293b;
        font-size: 18px;
        font-weight: 700;
        line-height: 1.4;
    }

    #bank-stmt-root .stmt-page-title i {
        color: var(--stmt-primary);
        font-size: 17px;
    }

    #bank-stmt-root .stmt-page-subtitle {
        display: block;
        color: var(--stmt-muted);
        font-size: 11.5px;
        line-height: 1.5;
    }

    /* Compact Buttons */
    #bank-stmt-root .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-height: 32px;
        padding: 6px 12px !important;
        border-radius: 5px !important;
        font-size: 11.5px !important;
        font-weight: 600 !important;
        line-height: 1.3 !important;
        white-space: nowrap;
        transition: all .15s ease;
    }

    #bank-stmt-root .btn-compact i {
        font-size: 11px;
    }

    #bank-stmt-root .btn-primary {
        background: var(--stmt-primary);
        border-color: var(--stmt-primary);
    }

    #bank-stmt-root .btn-primary:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
    }

    #bank-stmt-root .stmt-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 7px;
    }

    /* Common Cards */
    #bank-stmt-root .stmt-card {
        margin-bottom: 14px;
        border: 1px solid var(--stmt-border) !important;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .04) !important;
    }

    #bank-stmt-root .stmt-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        min-height: 43px;
        padding: 10px 14px;
        border-bottom: 1px solid var(--stmt-border);
        background: #fff;
    }

    #bank-stmt-root .stmt-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        color: #1e293b;
        font-size: 13px;
        font-weight: 700;
    }

    #bank-stmt-root .stmt-section-title i {
        color: var(--stmt-primary);
    }

    /* Account Information */
    #bank-stmt-root .account-info-card {
        padding: 15px;
    }

    #bank-stmt-root .account-info-grid {
        row-gap: 14px;
    }

    #bank-stmt-root .account-info-label {
        display: block;
        margin-bottom: 5px;
        color: var(--stmt-muted);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .035em;
        text-transform: uppercase;
    }

    #bank-stmt-root .account-info-value {
        display: block;
        color: #1e293b;
        font-size: 12.5px;
        font-weight: 700;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    #bank-stmt-root .account-info-meta {
        display: block;
        margin-top: 3px;
        color: var(--stmt-muted);
        font-size: 10.5px;
        overflow-wrap: anywhere;
    }

    #bank-stmt-root .account-type-badge {
        display: inline-flex;
        margin-top: 6px;
        padding: 3px 7px;
        border: 1px solid #e2e8f0;
        background: #f1f5f9;
        color: #475569;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .03em;
    }

    #bank-stmt-root .account-balance-value {
        color: var(--stmt-primary);
        font-size: 17px;
        font-weight: 800;
        line-height: 1.4;
    }

    /* Filter */
    #bank-stmt-root .filter-card {
        padding: 14px;
    }

    #bank-stmt-root .form-label {
        display: block;
        margin-bottom: 5px;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
    }

    #bank-stmt-root .form-control,
    #bank-stmt-root .form-select {
        min-height: 34px;
        padding: 6px 10px;
        border: 1px solid #cbd5e1;
        background-color: #fff;
        color: #1e293b;
        font-size: 11.5px;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #bank-stmt-root .form-control:focus,
    #bank-stmt-root .form-select:focus {
        border-color: var(--stmt-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
        outline: none;
    }

    /* KPI Cards */
    #bank-stmt-root .kpi-stat-card {
        position: relative;
        height: 100%;
        min-height: 105px;
        padding: 14px 14px 12px 16px;
        overflow: hidden;
        border: 1px solid var(--stmt-border);
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
        transition: box-shadow .18s ease, transform .18s ease;
    }

    #bank-stmt-root .kpi-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, .07);
    }

    #bank-stmt-root .kpi-stat-card::before {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: 3px;
        content: '';
    }

    #bank-stmt-root .kpi-opening::before { background: #64748b; }
    #bank-stmt-root .kpi-inflow::before { background: #10b981; }
    #bank-stmt-root .kpi-outflow::before { background: #ef4444; }
    #bank-stmt-root .kpi-closing::before { background: #0284c7; }

    #bank-stmt-root .kpi-label {
        display: block;
        margin-bottom: 7px;
        color: var(--stmt-muted);
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .025em;
        text-transform: uppercase;
    }

    #bank-stmt-root .kpi-value {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    #bank-stmt-root .kpi-description {
        display: block;
        margin-top: 4px;
        color: #94a3b8;
        font-size: 10px;
        line-height: 1.4;
    }

    /* Statement Table */
    #bank-stmt-root .pc-fit-container {
        width: 100%;
        overflow-x: auto !important;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
    }

    #bank-stmt-root .pc-fit-container::-webkit-scrollbar {
        height: 7px;
    }

    #bank-stmt-root .pc-fit-container::-webkit-scrollbar-track {
        background: #f8fafc;
    }

    #bank-stmt-root .pc-fit-container::-webkit-scrollbar-thumb {
        border-radius: 5px;
        background: #cbd5e1;
    }

    #bank-stmt-root .stmt-table {
        width: 100%;
        min-width: 980px;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #bank-stmt-root .stmt-table thead th {
        padding: 11px 10px;
        border-bottom: 1px solid var(--stmt-border);
        background: #f8fafc;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .035em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    #bank-stmt-root .stmt-table tbody td {
        padding: 10px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 11.5px;
        vertical-align: middle;
        white-space: nowrap;
    }

    #bank-stmt-root .stmt-table tbody tr {
        transition: background-color .12s ease;
    }

    #bank-stmt-root .stmt-table tbody tr:hover {
        background: #f8fafc;
    }

    #bank-stmt-root .stmt-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #bank-stmt-root .stmt-table tfoot td {
        padding: 11px 10px;
        border-top: 1px solid var(--stmt-border);
        background: #f8fafc;
        font-size: 11px;
        white-space: nowrap;
    }

    #bank-stmt-root .stmt-table .col-st-date { min-width: 105px; }
    #bank-stmt-root .stmt-table .col-st-ref { min-width: 145px; }
    #bank-stmt-root .stmt-table .col-st-type { min-width: 105px; }
    #bank-stmt-root .stmt-table .col-st-note { min-width: 250px; }
    #bank-stmt-root .stmt-table .col-st-debit,
    #bank-stmt-root .stmt-table .col-st-credit { min-width: 135px; }
    #bank-stmt-root .stmt-table .col-st-balance { min-width: 145px; }

    #bank-stmt-root .stmt-table .col-st-note span {
        white-space: normal;
        overflow-wrap: anywhere;
        line-height: 1.5;
    }

    #bank-stmt-root .stmt-table .reference-badge {
        display: inline-block;
        max-width: 100%;
        padding: 4px 7px;
        overflow-wrap: anywhere;
        white-space: normal;
        font-size: 9.5px;
    }

    #bank-stmt-root .stmt-table .type-badge {
        padding: 4px 7px;
        font-size: 9px;
        font-weight: 700;
    }

    /* Empty State */
    #bank-stmt-root .stmt-empty-state {
        padding: 38px 15px !important;
        color: var(--stmt-muted);
        text-align: center;
        white-space: normal !important;
    }

    #bank-stmt-root .stmt-empty-state i {
        display: block;
        margin-bottom: 10px;
        color: #cbd5e1;
        font-size: 27px;
    }

    /* Pagination */
    #bank-stmt-root .card-footer nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }

    #bank-stmt-root .card-footer nav > div.d-sm-none,
    #bank-stmt-root .card-footer nav > .d-sm-none {
        display: none !important;
    }

    #bank-stmt-root .card-footer nav > div.d-none,
    #bank-stmt-root .card-footer nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #bank-stmt-root .card-footer nav p,
    #bank-stmt-root .card-footer nav .small.text-muted,
    #bank-stmt-root .card-footer nav div:has(> p),
    #bank-stmt-root .card-footer nav > div.d-none > div:first-child,
    #bank-stmt-root .card-footer nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }

    #bank-stmt-root .card-footer .pagination {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    #bank-stmt-root .card-footer .page-item {
        display: inline-flex !important;
    }

    #bank-stmt-root .card-footer .page-item .page-link,
    #bank-stmt-root .card-footer .page-item span {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 27px !important;
        height: 27px !important;
        padding: 0 7px !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 5px !important;
        background: #fff !important;
        color: #475569 !important;
        font-size: 10.5px !important;
        font-weight: 600 !important;
        line-height: 1 !important;
        text-decoration: none !important;
        transition: all .12s ease !important;
    }

    #bank-stmt-root .card-footer .page-item .page-link:hover {
        border-color: #bfdbfe !important;
        background: #eff6ff !important;
        color: var(--stmt-primary) !important;
    }

    #bank-stmt-root .card-footer .page-item.active .page-link,
    #bank-stmt-root .card-footer .page-item.active span {
        border-color: var(--stmt-primary) !important;
        background: var(--stmt-primary) !important;
        color: #fff !important;
    }

    #bank-stmt-root .card-footer .page-item.disabled .page-link,
    #bank-stmt-root .card-footer .page-item.disabled span {
        border-color: #e2e8f0 !important;
        background: #f8fafc !important;
        opacity: .45 !important;
        cursor: not-allowed !important;
    }

    #bank-stmt-root .card-footer nav svg {
        width: 11px !important;
        height: 11px !important;
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

        #bank-stmt-root .no-print,
        .admin-dark-sidebar,
        .admin-header,
        .main-footer {
            display: none !important;
        }

        #bank-stmt-root {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        #bank-stmt-root .stmt-card {
            margin-bottom: 10px !important;
            border: 1px solid #000 !important;
            box-shadow: none !important;
        }

        #bank-stmt-root .pc-fit-container {
            overflow: visible !important;
        }

        #bank-stmt-root .stmt-table {
            min-width: 0 !important;
            width: 100% !important;
            font-size: 9px !important;
        }

        #bank-stmt-root .stmt-table thead th,
        #bank-stmt-root .stmt-table tbody td,
        #bank-stmt-root .stmt-table tfoot td {
            padding: 5px !important;
            color: #000 !important;
        }
    }

    /* Tablet */
    @media (max-width: 991.98px) {
        #bank-stmt-root .stmt-actions {
            justify-content: flex-start;
        }

        #bank-stmt-root .account-balance-column {
            border-top: 1px solid var(--stmt-border);
            padding-top: 10px;
        }
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        #bank-stmt-root {
            padding-right: 10px !important;
            padding-left: 10px !important;
        }

        #bank-stmt-root .stmt-page-header {
            align-items: stretch !important;
            gap: 10px;
        }

        #bank-stmt-root .stmt-page-title {
            font-size: 16px;
        }

        #bank-stmt-root .stmt-page-subtitle {
            font-size: 11px;
        }

        #bank-stmt-root .stmt-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
            gap: 6px;
        }

        #bank-stmt-root .stmt-actions .btn {
            width: 100%;
            min-width: 0;
            padding-right: 7px !important;
            padding-left: 7px !important;
        }

        #bank-stmt-root .account-info-card {
            padding: 12px;
        }

        #bank-stmt-root .account-info-grid {
            row-gap: 13px;
        }

        #bank-stmt-root .account-balance-column {
            padding-top: 10px;
            border-top: 1px solid var(--stmt-border);
        }

        #bank-stmt-root .filter-card {
            padding: 12px;
        }

        #bank-stmt-root .filter-actions {
            display: flex;
            width: 100%;
        }

        #bank-stmt-root .filter-actions .btn {
            flex: 1;
        }

        #bank-stmt-root .kpi-stat-card {
            min-height: 100px;
            padding: 12px 10px 10px 13px;
        }

        #bank-stmt-root .kpi-label {
            font-size: 9.5px;
        }

        #bank-stmt-root .kpi-value {
            font-size: 14px;
        }

        #bank-stmt-root .kpi-description {
            font-size: 9px;
        }

        #bank-stmt-root .stmt-card-header {
            align-items: flex-start;
            padding: 10px 12px;
        }

        #bank-stmt-root .stmt-section-title {
            font-size: 12px;
        }

        #bank-stmt-root .card-footer {
            flex-direction: column !important;
            gap: 9px !important;
            justify-content: center !important;
            padding: 10px !important;
        }

        #bank-stmt-root .card-footer nav {
            width: 100% !important;
            justify-content: center !important;
        }
    }

    @media (max-width: 380px) {
        #bank-stmt-root .kpi-value {
            font-size: 12.5px;
        }

        #bank-stmt-root .btn-compact {
            font-size: 10.5px !important;
        }

        #bank-stmt-root .stmt-page-title {
            font-size: 15px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="bank-stmt-root">

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center stmt-page-header header-top-wrap no-print">
        <div>
            <h5 class="stmt-page-title">
                <i class="fas fa-file-invoice"></i>
                <span>Account Statement: {{ $account->account_name ?? 'Account Passbook' }}</span>
            </h5>
            <small class="stmt-page-subtitle">
                Account transactions, credits, debits and running balance
            </small>
        </div>

        <div class="stmt-actions top-action-btns">
            <button type="button" class="btn btn-sm btn-dark btn-compact shadow-xs fw-semibold" onclick="window.print()">
                <i class="fas fa-print"></i>
                <span>Print</span>
            </button>

            <a href="{{ url('/admin/accounts/bank-balance') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-arrow-left"></i>
                <span>All Accounts</span>
            </a>

            <a href="{{ url('/admin/accounts/ledger') }}" class="btn btn-sm btn-outline-primary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-book-journal-whills"></i>
                <span>General Ledger</span>
            </a>
        </div>
    </div>

    <!-- Account Information -->
    <div class="card stmt-card account-info-card">
        <div class="row g-3 align-items-center account-info-grid">

            <div class="col-12 col-md-4">
                <span class="account-info-label">Account Name</span>

                <strong class="account-info-value" title="{{ $account->account_name ?? '—' }}">
                    {{ $account->account_name ?? '—' }}
                </strong>

                <span class="badge account-type-badge text-uppercase">
                    {{ str_replace('_', ' ', $account->account_type ?? 'Cash') }}
                </span>
            </div>

            <div class="col-6 col-md-3">
                <span class="account-info-label">Bank / Institution</span>

                <strong class="account-info-value">
                    {{ $account->bank_name ?? 'Physical Drawer / Vault' }}
                </strong>

                <small class="account-info-meta font-monospace">
                    {{ $account->account_number ? '#' . $account->account_number : 'Internal Cash' }}
                </small>
            </div>

            <div class="col-6 col-md-3">
                <span class="account-info-label">Branch / Outlet</span>

                <strong class="account-info-value">
                    {{ $account->branch_name ?? 'Main Corporate Office' }}
                </strong>

                <small class="account-info-meta">
                    Operational Vault
                </small>
            </div>

            <div class="col-12 col-md-2 text-md-end account-balance-column">
                <span class="account-info-label">Current Balance</span>

                <h5 class="account-balance-value font-monospace bn-digit mb-0">
                    ৳ {{ number_format((float)($account->current_balance ?? 0), 2) }}
                </h5>
            </div>

        </div>
    </div>

    <!-- Date Filter -->
    <div class="card stmt-card filter-card no-print">
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

                <input
                    type="date"
                    name="start_date"
                    class="form-control font-monospace bn-digit"
                    value="{{ request('start_date', isset($start) ? $start->format('Y-m-d') : date('Y-m-01')) }}"
                >
            </div>

            <div class="col-6 col-sm-3 col-md-3 {{ ($dateFilter ?? '') === 'custom' ? '' : 'd-none' }}" id="stmtEndCol">
                <label class="form-label">End Date</label>

                <input
                    type="date"
                    name="end_date"
                    class="form-control font-monospace bn-digit"
                    value="{{ request('end_date', isset($end) ? $end->format('Y-m-d') : date('Y-m-d')) }}"
                >
            </div>

            <div class="col-12 col-md-auto d-flex gap-2 filter-actions">
                <button type="submit" class="btn btn-dark btn-compact shadow-xs fw-bold px-3">
                    <i class="fas fa-filter"></i>
                    Filter
                </button>

                @if(request()->hasAny(['date_filter', 'start_date', 'end_date']))
                    <a href="{{ url()->current() }}" class="btn btn-light border btn-compact shadow-xs">
                        <i class="fas fa-rotate-left"></i>
                        Reset
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Balance Summary -->
    <div class="row g-2 mb-3">

        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-opening">
                <span class="kpi-label">Opening Balance</span>

                <h5 class="kpi-value text-secondary font-monospace bn-digit">
                    ৳ {{ number_format($openingBalance, 2) }}
                </h5>

                <small class="kpi-description">Period start</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-inflow">
                <span class="kpi-label">Total Inflow</span>

                <h5 class="kpi-value text-success font-monospace bn-digit">
                    +৳ {{ number_format($totalCredits, 2) }}
                </h5>

                <small class="kpi-description">Credits and deposits</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-outflow">
                <span class="kpi-label">Total Outflow</span>

                <h5 class="kpi-value text-danger font-monospace bn-digit">
                    -৳ {{ number_format($totalDebits, 2) }}
                </h5>

                <small class="kpi-description">Debits and withdrawals</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-closing">
                <span class="kpi-label">Closing Balance</span>

                <h5 class="kpi-value text-primary font-monospace bn-digit">
                    ৳ {{ number_format($closingBalance, 2) }}
                </h5>

                <small class="kpi-description">Period closing balance</small>
            </div>
        </div>

    </div>

    <!-- Transaction Statement -->
    <div class="card stmt-card">

        <div class="stmt-card-header">
            <h6 class="stmt-section-title">
                <i class="fas fa-list-check"></i>
                <span>Transaction Statement</span>
            </h6>

            <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 10px;">
                Entries:
                {{ isset($transactions) && method_exists($transactions, 'total') ? $transactions->total() : (isset($transactions) ? count($transactions) : 0) }}
            </span>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive pc-fit-container">
                <table class="table align-middle stmt-table mb-0">

                    <thead>
                        <tr>
                            <th class="ps-3 col-st-date">Date</th>
                            <th class="col-st-ref">Reference #</th>
                            <th class="col-st-type text-center">Type</th>
                            <th class="col-st-note">Description</th>
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
                                    <span class="badge bg-light text-secondary border font-monospace reference-badge">
                                        {{ $trx->reference_no ?? ($trx->reference_type ?? '#TRX-' . $trx->id) }}
                                    </span>
                                </td>

                                <td class="col-st-type text-center">
                                    <span class="badge {{ $isCredit ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }} font-monospace text-uppercase type-badge">
                                        {{ strtoupper($type) }}
                                    </span>
                                </td>

                                <td class="col-st-note">
                                    <span class="text-dark d-block fw-medium" title="{{ $trx->note ?? ($trx->description ?? 'Account transaction entry') }}">
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
                                <td colspan="7" class="stmt-empty-state">
                                    <i class="fas fa-receipt"></i>
                                    <span class="fw-semibold">No transactions found for this period.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="4" class="ps-3 text-secondary">
                                Period Summary:
                            </td>

                            <td class="text-end font-monospace bn-digit text-danger">
                                -৳ {{ number_format($totalDebits, 2) }}
                            </td>

                            <td class="text-end font-monospace bn-digit text-success">
                                +৳ {{ number_format($totalCredits, 2) }}
                            </td>

                            <td class="text-end pe-3 font-monospace bn-digit text-primary">
                                ৳ {{ number_format($closingBalance, 2) }}
                            </td>
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