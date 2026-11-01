@extends('layouts.admin')

@section('title', 'Bank & Cash Balance - ' . config('app.name'))

@php
    $totalLiquidAssets = $totalLiquidAssets ?? 0;
    $totalBankBalance  = $totalBankBalance ?? 0;
    $totalCashInHand   = $totalCashInHand ?? 0;
    $totalMobileMoney  = $totalMobileMoney ?? 0;
    $dateFilter        = $dateFilter ?? 'this_month';
@endphp

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #bank-ledger-root .btn,
    #bank-ledger-root .btn-sm,
    #bank-ledger-root button,
    #bank-ledger-root .badge,
    #bank-ledger-root .alert,
    #bank-ledger-root .form-control,
    #bank-ledger-root .form-select,
    #bank-ledger-root .page-link,
    #bank-ledger-root .page-item span,
    #bank-ledger-root .card,
    #bank-ledger-root .bank-card,
    #bank-ledger-root .account-box,
    #bank-ledger-root .kpi-stat-card,
    .bank-modal-scope .modal-content,
    .bank-modal-scope .btn,
    .bank-modal-scope .form-control,
    .bank-modal-scope .form-select,
    .bank-modal-scope .badge {
        border-radius: 5px !important;
    }

    #bank-ledger-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #bank-ledger-root .input-group > .form-control,
    #bank-ledger-root .input-group > .form-select {
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
    .bank-card {
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
    .kpi-liquid::before { background-color: #0284c7; }
    .kpi-bank::before   { background-color: #10b981; }
    .kpi-cash::before   { background-color: #f59e0b; }
    .kpi-mobile::before { background-color: #ec4899; }

    /* Account Grid Box */
    .account-box {
        border: 1px solid #e2e8f0;
        padding: 12px 14px;
        background: #ffffff;
        transition: all 0.15s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
    }
    .account-box:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
    }

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .bank-table {
        width: 100%;
        margin-bottom: 0;
    }
    .bank-table thead th {
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
    .bank-table tbody td {
        padding: 8px 6px;
        vertical-align: middle;
        font-size: 11.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .bank-table tbody tr:hover {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .bank-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .bank-table th,
        .bank-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 8 Columns Proportional Distribution Summing to Exactly 100% */
        .col-bt-id      { width: 5% !important; }
        .col-bt-date    { width: 12% !important; }
        .col-bt-type    { width: 12% !important; }
        .col-bt-from    { width: 18% !important; }
        .col-bt-to      { width: 18% !important; }
        .col-bt-ref     { width: 13% !important; }
        .col-bt-amount  { width: 12% !important; }
        .col-bt-note    { width: 10% !important; }
    }

    /* Form Controls */
    .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #bank-ledger-root .form-control,
    #bank-ledger-root .form-select,
    .bank-modal-scope .form-control,
    .bank-modal-scope .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease;
    }
    #bank-ledger-root .form-control:focus,
    #bank-ledger-root .form-select:focus,
    .bank-modal-scope .form-control:focus,
    .bank-modal-scope .form-select:focus {
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
        .bank-table {
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

<div class="container-fluid py-3 px-2 px-md-4" id="bank-ledger-root">

    <!-- 1. Header (Original Clean White/Light Theme) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 header-top-wrap">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-landmark text-primary"></i> <span>Bank Accounts &amp; Cash Balance Ledger</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Manage physical cash drawers, commercial bank accounts, mobile banking &amp; fund transfers
            </small>
        </div>
        <div class="top-action-btns d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold" data-bs-toggle="modal" data-bs-target="#transferModal">
                <i class="fas fa-money-bill-transfer me-1"></i> <span>Transfer Balance</span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-success btn-compact shadow-xs fw-bold" data-bs-toggle="modal" data-bs-target="#depositModal">
                <i class="fas fa-plus-circle me-1"></i> <span>Deposit / Top-up</span>
            </button>
            <button type="button" class="btn btn-sm btn-dark btn-compact shadow-xs fw-bold" data-bs-toggle="modal" data-bs-target="#createAccountModal">
                <i class="fas fa-building-columns me-1"></i> <span>Add Account</span>
            </button>
            <a href="{{ url('/admin/accounts/bank-balance/export-csv') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-file-csv me-1"></i> <span>Export CSV</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small border-0 shadow-sm d-flex align-items-center justify-content-between mb-3" role="alert" style="border-radius: 5px;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close shadow-none py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-sm d-flex align-items-center justify-content-between mb-3" role="alert" style="border-radius: 5px;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-triangle-exclamation text-danger fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close shadow-none py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- 2. KPI Metric Summary Cards (Original Clean Theme) -->
    <div class="row g-2 mb-3">
        <!-- Total Liquid Assets -->
        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-liquid shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Total Assets</span>
                <h5 class="fw-bold text-primary mb-0 font-monospace bn-digit">৳ {{ number_format($totalLiquidAssets, 2) }}</h5>
                <small class="text-muted" style="font-size: 10px;">Bank + Cash + Mobile Wallets</small>
            </div>
        </div>

        <!-- Bank Balance -->
        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-bank shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Commercial Bank Balance</span>
                <h5 class="fw-bold text-success mb-0 font-monospace bn-digit">৳ {{ number_format($totalBankBalance, 2) }}</h5>
                <small class="text-muted" style="font-size: 10px;">DBBL, City, Brac &amp; others</small>
            </div>
        </div>

        <!-- Cash in Hand -->
        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-cash shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Cash in Hand (Drawers)</span>
                <h5 class="fw-bold text-warning mb-0 font-monospace bn-digit">৳ {{ number_format($totalCashInHand, 2) }}</h5>
                <small class="text-muted" style="font-size: 10px;">Physical counter cash</small>
            </div>
        </div>

        <!-- Mobile Money -->
        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-mobile shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Mobile Merchant Wallets</span>
                <h5 class="fw-bold text-danger mb-0 font-monospace bn-digit">৳ {{ number_format($totalMobileMoney, 2) }}</h5>
                <small class="text-muted" style="font-size: 10px;">bKash &amp; Nagad merchants</small>
            </div>
        </div>
    </div>

    <!-- 3. Active Accounts Grid Cards -->
    <div class="card border-0 shadow-sm bank-card mb-3 p-3">
        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 d-flex align-items-center gap-1" style="font-size: 13.5px;">
            <i class="fas fa-vault text-primary"></i> <span>Active Financial Accounts &amp; Vaults</span>
        </h6>
        <div class="row g-2">
            @foreach($accounts as $acc)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="account-box shadow-xs">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <strong class="text-dark d-block text-truncate" style="font-size: 13px;" title="{{ $acc->account_name }}">{{ $acc->account_name }}</strong>
                                <span class="badge {{ $acc->account_type === 'cash' ? 'bg-warning text-dark' : ($acc->account_type === 'mobile_banking' ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-success-subtle text-success border border-success-subtle') }} font-monospace" style="font-size: 9.5px;">
                                    {{ strtoupper(str_replace('_', ' ', $acc->account_type)) }}
                                </span>
                            </div>
                            <small class="text-muted d-block font-monospace mb-2 text-truncate" style="font-size: 11px;">
                                {{ $acc->bank_name ?? 'Physical Vault' }} {{ $acc->account_number ? '• ' . $acc->account_number : '' }}
                            </small>
                        </div>
                        <div class="d-flex justify-content-between align-items-end pt-2 border-top mt-2">
                            <div>
                                <small class="text-muted d-block" style="font-size: 10px;">Current Balance</small>
                                <span class="fw-bold text-dark font-monospace bn-digit" style="font-size: 14.5px;">৳ {{ number_format((float)$acc->current_balance, 2) }}</span>
                            </div>
                            <div class="d-flex gap-1 flex-wrap">
                                <button type="button" class="btn btn-outline-primary btn-compact py-0 px-2" onclick="openQuickTransfer({{ $acc->id }}, '{{ addslashes($acc->account_name) }}')" title="Transfer from this account" style="height: 24px; font-size: 10px !important;">
                                    Transfer
                                </button>
                                <button type="button" class="btn btn-outline-success btn-compact py-0 px-2" onclick="openQuickDeposit({{ $acc->id }}, '{{ addslashes($acc->account_name) }}')" title="Deposit into this account" style="height: 24px; font-size: 10px !important;">
                                    Deposit
                                </button>
                                <a href="{{ url('/admin/accounts/bank-balance/statement/' . $acc->id) }}" class="btn btn-outline-dark btn-compact py-0 px-2 d-inline-flex align-items-center gap-1 shadow-xs" title="View Account Statement / Passbook" style="height: 24px; font-size: 10px !important;">
                                    <i class="fas fa-file-lines" style="font-size: 9px;"></i> Passbook
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 4. Transaction Filter Bar -->
    <div class="card border-0 shadow-sm bank-card mb-3 p-3">
        <form action="{{ url('/admin/accounts/bank-balance') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label">Period</label>
                <select name="date_filter" id="bankDateFilter" class="form-select font-monospace" onchange="toggleCustomBankDates(this.value)">
                    <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ $dateFilter === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="this_week" {{ $dateFilter === 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ $dateFilter === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $dateFilter === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="this_year" {{ $dateFilter === 'this_year' ? 'selected' : '' }}>This Year</option>
                    <option value="custom" {{ $dateFilter === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                </select>
            </div>

            <div class="col-6 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="bankStartCol">
                <label class="form-label">Start</label>
                <input type="date" name="start_date" class="form-control font-monospace bn-digit" value="{{ request('start_date', $start->format('Y-m-d')) }}">
            </div>

            <div class="col-6 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="bankEndCol">
                <label class="form-label">End</label>
                <input type="date" name="end_date" class="form-control font-monospace bn-digit" value="{{ request('end_date', $end->format('Y-m-d')) }}">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label">Account Filter</label>
                <select name="account_id" class="form-select font-monospace">
                    <option value="all">All Accounts</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ (string)request('account_id') === (string)$acc->id ? 'selected' : '' }}>
                            {{ $acc->account_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label">Type</label>
                <select name="type" class="form-select font-monospace">
                    <option value="all">All Transactions</option>
                    <option value="transfer" {{ request('type') === 'transfer' ? 'selected' : '' }}>Fund Transfer</option>
                    <option value="deposit" {{ request('type') === 'deposit' ? 'selected' : '' }}>Direct Deposit</option>
                    <option value="withdraw" {{ request('type') === 'withdraw' ? 'selected' : '' }}>Withdrawal</option>
                </select>
            </div>

            <div class="col-12 col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-dark btn-compact flex-grow-1 shadow-xs fw-bold">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if(request()->anyFilled(['date_filter', 'account_id', 'type', 'start_date', 'end_date', 'search']))
                    <a href="{{ url('/admin/accounts/bank-balance') }}" class="btn btn-light border btn-compact shadow-xs">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- 5. Transactions Ledger Table Card -->
    <div class="card border-0 shadow-sm bank-card overflow-hidden">
        <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                <i class="fas fa-list-check text-primary"></i> <span>Cash &amp; Bank Transaction Ledger</span>
            </h6>
            <span class="badge bg-light text-secondary border font-monospace px-2 py-0.5" style="font-size: 10.5px;">
                Showing {{ $transactions->firstItem() ?? 0 }} - {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} entries
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive pc-fit-container">
                <table class="table align-middle bank-table mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3 col-bt-id text-center">#</th>
                            <th class="col-bt-date">Date</th>
                            <th class="col-bt-type text-center">Type</th>
                            <th class="col-bt-from">From Account</th>
                            <th class="col-bt-to">To Account</th>
                            <th class="col-bt-ref">Reference #</th>
                            <th class="text-end col-bt-amount">Amount (৳)</th>
                            <th class="col-bt-note">Note / Particulars</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $idx => $trx)
                            <tr>
                                <td class="ps-3 col-bt-id text-center font-monospace bn-digit text-muted" style="font-size: 10.5px;">
                                    {{ $transactions->firstItem() + $idx }}
                                </td>
                                <td class="col-bt-date font-monospace bn-digit text-muted" style="font-size: 10.5px;">
                                    {{ date('d M Y', strtotime($trx->transaction_date)) }}
                                </td>
                                <td class="col-bt-type text-center">
                                    <span class="badge {{ $trx->transaction_type === 'transfer' ? 'bg-primary-subtle text-primary border border-primary-subtle' : ($trx->transaction_type === 'deposit' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-dark border border-warning-subtle') }} font-monospace text-uppercase" style="font-size: 9px;">
                                        {{ strtoupper($trx->transaction_type) }}
                                    </span>
                                </td>
                                <td class="col-bt-from">
                                    <span class="text-dark d-block text-truncate fw-semibold" style="font-size: 11.5px;" title="{{ $trx->from_account_name ?? '—' }}">
                                        {{ $trx->from_account_name ?? '—' }}
                                    </span>
                                </td>
                                <td class="col-bt-to">
                                    <span class="text-dark d-block text-truncate fw-semibold" style="font-size: 11.5px;" title="{{ $trx->to_account_name ?? '—' }}">
                                        {{ $trx->to_account_name ?? '—' }}
                                    </span>
                                </td>
                                <td class="col-bt-ref font-monospace bn-digit text-muted text-truncate" style="font-size: 10.5px;">
                                    {{ $trx->reference_no ?? '—' }}
                                </td>
                                <td class="text-end col-bt-amount font-monospace bn-digit fw-bold {{ $trx->transaction_type === 'deposit' ? 'text-success' : 'text-primary' }}" style="font-size: 12px;">
                                    ৳ {{ number_format((float)$trx->amount, 2) }}
                                </td>
                                <td class="col-bt-note">
                                    <span class="text-muted d-block text-truncate font-monospace" style="font-size: 10px;" title="{{ $trx->note ?? '—' }}">
                                        {{ $trx->note ?? '—' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted small">
                                    <i class="fas fa-receipt fa-2x mb-2 d-block opacity-25"></i>
                                    <span class="fw-semibold">No bank transactions recorded for this period.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($transactions->hasPages())
            <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer">
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

<!-- Modal 1: Balance Transfer between Accounts -->
<div class="modal fade bank-modal-scope" id="transferModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-1 mb-0" style="font-size: 13.5px;">
                    <i class="fas fa-money-bill-transfer text-primary"></i> <span>Internal Fund Transfer</span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ url('/admin/accounts/bank-balance/transfer') }}" method="POST" id="transferBalanceForm">
                @csrf
                <div class="modal-body p-3">
                    <div class="row g-2">
                        <!-- From Account -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Transfer From (Debit) <span class="text-danger">*</span></label>
                            <select name="from_account_id" id="transferFromSelect" class="form-select font-monospace" required onchange="handleFromAccountChange()">
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} (৳{{ number_format((float)$acc->current_balance, 2) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- To Account -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Transfer To (Credit) <span class="text-danger">*</span></label>
                            <select name="to_account_id" id="transferToSelect" class="form-select font-monospace" required onchange="handleToAccountChange()">
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} (৳{{ number_format((float)$acc->current_balance, 2) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Amount -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Transfer Amount (৳) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control font-monospace bn-digit text-end fw-bold text-primary" placeholder="0.00" min="1" step="any" required>
                        </div>

                        <!-- Date -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Transfer Date <span class="text-danger">*</span></label>
                            <input type="date" name="transfer_date" class="form-control font-monospace bn-digit" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <!-- Reference No -->
                        <div class="col-12">
                            <label class="form-label">Reference / Slip #</label>
                            <input type="text" name="reference_no" class="form-control font-monospace" placeholder="SLIP-10293">
                        </div>

                        <!-- Note -->
                        <div class="col-12">
                            <label class="form-label">Particulars / Note</label>
                            <textarea name="note" rows="2" class="form-control" placeholder="Reason for transfer..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3">
                        <i class="fas fa-check-circle me-1"></i> Execute Transfer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Direct Deposit / Top-up -->
<div class="modal fade bank-modal-scope" id="depositModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-1 mb-0" style="font-size: 13.5px;">
                    <i class="fas fa-plus-circle text-success"></i> <span>Direct Deposit / Top-up</span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ url('/admin/accounts/bank-balance/deposit') }}" method="POST">
                @csrf
                <div class="modal-body p-3">
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Destination Account <span class="text-danger">*</span></label>
                            <select name="account_id" id="depositAccountSelect" class="form-select font-monospace" required>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Deposit Amount (৳) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control font-monospace bn-digit text-end fw-bold text-success" placeholder="0.00" min="1" step="any" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Deposit Date <span class="text-danger">*</span></label>
                            <input type="date" name="transaction_date" class="form-control font-monospace bn-digit" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Reference / Slip #</label>
                            <input type="text" name="reference_no" class="form-control font-monospace" placeholder="DEP-9938">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Remarks / Note</label>
                            <textarea name="note" rows="2" class="form-control" placeholder="Additional details..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-compact shadow-xs fw-bold px-3">
                        <i class="fas fa-check-circle me-1"></i> Save Deposit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 3: Add New Bank / Wallet Account -->
<div class="modal fade bank-modal-scope" id="createAccountModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-1 mb-0" style="font-size: 13.5px;">
                    <i class="fas fa-building-columns text-warning"></i> <span>Add Financial Account / Vault</span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ url('/admin/accounts/bank-balance/account/store') }}" method="POST">
                @csrf
                <div class="modal-body p-3">
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Account Name <span class="text-danger">*</span></label>
                            <input type="text" name="account_name" class="form-control" placeholder="City Bank Business, Counter Cash #2" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Account Type <span class="text-danger">*</span></label>
                            <select name="account_type" class="form-select font-monospace" required>
                                <option value="bank" selected>Commercial Bank</option>
                                <option value="cash">Physical Cash Drawer</option>
                                <option value="mobile_banking">Mobile Banking (bKash/Nagad)</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Account / Phone Number</label>
                            <input type="text" name="account_number" class="form-control font-monospace" placeholder="150-101-9988">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Bank / Service Name</label>
                            <input type="text" name="bank_name" class="form-control" placeholder="City Bank, bKash">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Branch / Outlet Name</label>
                            <input type="text" name="branch_name" class="form-control" placeholder="Gulshan Branch">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Opening Balance (৳)</label>
                            <input type="number" name="opening_balance" class="form-control font-monospace bn-digit text-end" placeholder="0.00" min="0" step="any" value="0.00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark btn-compact shadow-xs fw-bold px-3">
                        <i class="fas fa-check-circle me-1"></i> Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 4: Notice Modal (Replaces browser alert) -->
<div class="modal fade bank-modal-scope" id="bankNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2 text-warning">
                    <i class="fas fa-triangle-exclamation fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="bankNoticeTitle" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="bankNoticeMessage" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-primary btn-compact px-4" data-bs-dismiss="modal">Acknowledge</button>
            </div>
        </div>
    </div>
</div>

<script>
function showBankNoticeModal(message, title = 'Notice') {
    const titleEl = document.getElementById('bankNoticeTitle');
    const msgEl   = document.getElementById('bankNoticeMessage');
    if (titleEl) titleEl.innerText = title;
    if (msgEl) msgEl.innerText = message;
    const modalEl = document.getElementById('bankNoticeModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

function toggleCustomBankDates(val) {
    const s = document.getElementById('bankStartCol');
    const e = document.getElementById('bankEndCol');
    if (val === 'custom') {
        s.classList.remove('d-none');
        e.classList.remove('d-none');
    } else {
        s.classList.add('d-none');
        e.classList.add('d-none');
    }
}

// Synchronize dropdowns so "From" and "To" are ALWAYS different
function syncTransferDropdowns(changedField) {
    const fromSelect = document.getElementById('transferFromSelect');
    const toSelect   = document.getElementById('transferToSelect');
    if (!fromSelect || !toSelect) return;

    const fromVal = fromSelect.value;
    const toVal   = toSelect.value;

    Array.from(toSelect.options).forEach(opt => {
        opt.disabled = (opt.value === fromVal);
    });

    Array.from(fromSelect.options).forEach(opt => {
        opt.disabled = (opt.value === toVal);
    });

    if (fromVal === toVal) {
        if (changedField === 'from') {
            const firstAvailableTo = Array.from(toSelect.options).find(opt => opt.value !== fromVal);
            if (firstAvailableTo) {
                toSelect.value = firstAvailableTo.value;
            }
        } else {
            const firstAvailableFrom = Array.from(fromSelect.options).find(opt => opt.value !== toVal);
            if (firstAvailableFrom) {
                fromSelect.value = firstAvailableFrom.value;
            }
        }
        syncTransferDropdowns(null);
    }
}

function handleFromAccountChange() {
    syncTransferDropdowns('from');
}

function handleToAccountChange() {
    syncTransferDropdowns('to');
}

function openQuickTransfer(accId, accName) {
    const fromSelect = document.getElementById('transferFromSelect');
    if (fromSelect) {
        fromSelect.value = accId;
    }
    syncTransferDropdowns('from');
    const modalEl = document.getElementById('transferModal');
    if (modalEl) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

function openQuickDeposit(accId, accName) {
    const select = document.getElementById('depositAccountSelect');
    if (select) {
        select.value = accId;
    }
    const modalEl = document.getElementById('depositModal');
    if (modalEl) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const transferModalEl = document.getElementById('transferModal');
    if (transferModalEl) {
        transferModalEl.addEventListener('show.bs.modal', function() {
            syncTransferDropdowns('from');
        });
    }

    const transferForm = document.getElementById('transferBalanceForm');
    if (transferForm) {
        transferForm.addEventListener('submit', function(e) {
            const fromSelect = document.getElementById('transferFromSelect');
            const toSelect   = document.getElementById('transferToSelect');
            if (fromSelect && toSelect && fromSelect.value === toSelect.value) {
                e.preventDefault();
                showBankNoticeModal('Transfer From and Transfer To accounts must be completely different accounts!', 'Invalid Account Pair');
                return false;
            }
        });
    }

    syncTransferDropdowns('from');
});
</script>
@endsection