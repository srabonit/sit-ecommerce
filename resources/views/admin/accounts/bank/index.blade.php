
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
    /* =========================================================
       KAIADMIN LITE v1.2.0 - CLASSIC / WHITE CLASSIC
       Bank & Cash Balance - Scoped UI
    ========================================================= */

    #bank-ledger-root {
        --bank-primary: #1572e8;
        --bank-primary-soft: #eaf2ff;
        --bank-success: #31ce77;
        --bank-warning: #ffad46;
        --bank-danger: #f25961;
        --bank-info: #48abf7;
        --bank-dark: #1a2035;
        --bank-text: #2a2f5b;
        --bank-muted: #8d9498;
        --bank-border: #ebedf2;
        --bank-bg: #f8f9fa;
        --bank-card: #ffffff;
        --bank-radius: 5px;
        color: var(--bank-text);
        font-size: 13px;
        width: 100%;
        min-width: 0;
    }

    #bank-ledger-root *,
    .bank-modal-scope * {
        box-sizing: border-box;
    }

    /* Global Radius */
    #bank-ledger-root .btn,
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
        border-radius: 5px 0 0 5px !important;
    }

    #bank-ledger-root .input-group > .form-control,
    #bank-ledger-root .input-group > .form-select {
        border-radius: 0 5px 5px 0 !important;
    }

    /* Typography */
    #bank-ledger-root .bn-digit,
    #bank-ledger-root .font-monospace,
    .bank-modal-scope .font-monospace {
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }

    #bank-ledger-root h5,
    #bank-ledger-root h6,
    .bank-modal-scope h6 {
        letter-spacing: -0.2px;
    }

    /* Header */
    #bank-ledger-root .bank-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 2px 0 16px;
        margin-bottom: 16px;
        border-bottom: 1px solid var(--bank-border);
    }

    #bank-ledger-root .bank-page-title {
        display: flex;
        align-items: center;
        gap: 9px;
        font-size: 17px;
        font-weight: 700;
        color: var(--bank-dark);
        margin: 0 0 4px;
    }

    #bank-ledger-root .bank-page-title > i {
        color: var(--bank-primary);
        font-size: 17px;
    }

    #bank-ledger-root .bank-page-subtitle {
        font-size: 11.5px;
        color: var(--bank-muted);
        margin: 0;
        line-height: 1.5;
    }

    #bank-ledger-root .top-action-btns {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 7px;
    }

    /* Buttons */
    #bank-ledger-root .btn-compact,
    .bank-modal-scope .btn-compact {
        min-height: 31px;
        padding: 6px 11px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        line-height: 1.3 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #bank-ledger-root .btn-compact:hover,
    .bank-modal-scope .btn-compact:hover {
        transform: translateY(-1px);
    }

    #bank-ledger-root .btn-primary,
    .bank-modal-scope .btn-primary {
        background-color: var(--bank-primary);
        border-color: var(--bank-primary);
    }

    #bank-ledger-root .btn-primary:hover,
    .bank-modal-scope .btn-primary:hover {
        background-color: #1265d2;
        border-color: #1265d2;
    }

    /* Cards */
    #bank-ledger-root .bank-card {
        background: var(--bank-card);
        border: 1px solid var(--bank-border) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035) !important;
        margin-bottom: 15px;
        min-width: 0;
    }

    #bank-ledger-root .section-card {
        padding: 15px;
    }

    #bank-ledger-root .section-heading {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--bank-dark);
        font-size: 13px;
        font-weight: 700;
        padding-bottom: 11px;
        margin-bottom: 13px;
        border-bottom: 1px solid var(--bank-border);
    }

    #bank-ledger-root .section-heading i {
        color: var(--bank-primary);
        font-size: 14px;
    }

    /* KPI Cards */
    #bank-ledger-root .kpi-stat-card {
        position: relative;
        height: 100%;
        min-height: 100px;
        padding: 14px 15px;
        background: #fff;
        border: 1px solid var(--bank-border);
        box-shadow: 0 1px 3px rgba(0, 0, 0, .025);
        overflow: hidden;
        transition: all .2s ease;
    }

    #bank-ledger-root .kpi-stat-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, .055);
        transform: translateY(-2px);
    }

    #bank-ledger-root .kpi-stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        width: 3px;
    }

    #bank-ledger-root .kpi-liquid::before { background: var(--bank-info); }
    #bank-ledger-root .kpi-bank::before { background: var(--bank-success); }
    #bank-ledger-root .kpi-cash::before { background: var(--bank-warning); }
    #bank-ledger-root .kpi-mobile::before { background: #e83e8c; }

    #bank-ledger-root .kpi-label {
        display: block;
        color: var(--bank-muted);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .35px;
        text-transform: uppercase;
        margin-bottom: 7px;
    }

    #bank-ledger-root .kpi-value {
        font-size: 19px;
        font-weight: 700;
        line-height: 1.3;
        margin: 0 0 5px;
        overflow-wrap: anywhere;
    }

    #bank-ledger-root .kpi-description {
        display: block;
        font-size: 10px;
        color: var(--bank-muted);
        line-height: 1.4;
    }

    /* Account Cards */
    #bank-ledger-root .account-box {
        height: 100%;
        min-width: 0;
        padding: 13px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background: #fff;
        border: 1px solid var(--bank-border);
        transition: all .2s ease;
    }

    #bank-ledger-root .account-box:hover {
        border-color: #b8d2f7;
        box-shadow: 0 4px 14px rgba(21, 114, 232, .07);
        transform: translateY(-2px);
    }

    #bank-ledger-root .account-name {
        font-size: 12px;
        font-weight: 700;
        color: var(--bank-dark);
        max-width: 65%;
    }

    #bank-ledger-root .account-meta {
        font-size: 10.5px;
        color: var(--bank-muted);
    }

    #bank-ledger-root .account-balance-label {
        font-size: 10px;
        color: var(--bank-muted);
        display: block;
        margin-bottom: 3px;
    }

    #bank-ledger-root .account-balance {
        font-size: 14px;
        font-weight: 700;
        color: var(--bank-dark);
        overflow-wrap: anywhere;
    }

    #bank-ledger-root .account-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 4px;
    }

    #bank-ledger-root .account-actions .btn {
        min-height: 25px;
        padding: 3px 7px !important;
        font-size: 9.5px !important;
    }

    #bank-ledger-root .account-box .badge {
        font-size: 9px;
        padding: 4px 6px;
        white-space: nowrap;
    }

    /* Forms */
    #bank-ledger-root .form-label,
    .bank-modal-scope .form-label {
        display: block;
        margin-bottom: 5px;
        color: #495057;
        font-size: 11px;
        font-weight: 600;
    }

    #bank-ledger-root .form-control,
    #bank-ledger-root .form-select,
    .bank-modal-scope .form-control,
    .bank-modal-scope .form-select {
        min-height: 34px;
        padding: 6px 10px;
        font-size: 11.5px;
        color: #495057;
        border: 1px solid #e0e3e8;
        background-color: #fff;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #bank-ledger-root .form-control:focus,
    #bank-ledger-root .form-select:focus,
    .bank-modal-scope .form-control:focus,
    .bank-modal-scope .form-select:focus {
        border-color: var(--bank-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
        outline: none;
    }

    #bank-ledger-root textarea.form-control,
    .bank-modal-scope textarea.form-control {
        min-height: 65px;
        resize: vertical;
    }

    /* Filter */
    #bank-ledger-root .filter-card {
        padding: 14px;
    }

    #bank-ledger-root .filter-actions {
        display: flex;
        gap: 6px;
    }

    /* Table */
    #bank-ledger-root .bank-table-wrapper {
        width: 100%;
        overflow-x: auto !important;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
    }

    #bank-ledger-root .bank-table {
        width: 100%;
        min-width: 1000px;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #bank-ledger-root .bank-table thead th {
        padding: 11px 9px;
        background: #f8f9fa;
        color: #6c757d;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .3px;
        white-space: nowrap;
        border-bottom: 1px solid var(--bank-border);
        vertical-align: middle;
    }

    #bank-ledger-root .bank-table tbody td {
        padding: 10px 9px;
        font-size: 11px;
        color: #495057;
        border-bottom: 1px solid #f0f1f3;
        vertical-align: middle;
        white-space: nowrap;
    }

    #bank-ledger-root .bank-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #bank-ledger-root .bank-table tbody tr {
        transition: background .15s ease;
    }

    #bank-ledger-root .bank-table tbody tr:hover {
        background: #f8fbff;
    }

    #bank-ledger-root .bank-table .badge {
        font-size: 9px;
        padding: 5px 7px;
    }

    #bank-ledger-root .table-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--bank-dark);
        margin: 0;
    }

    #bank-ledger-root .table-count {
        font-size: 10px;
        font-weight: 600;
        color: #6c757d;
        background: #f8f9fa;
        border: 1px solid var(--bank-border);
        padding: 5px 8px;
        border-radius: 5px;
        white-space: nowrap;
    }

    #bank-ledger-root .empty-state {
        padding: 35px 15px !important;
        text-align: center;
        color: var(--bank-muted);
        white-space: normal !important;
    }

    #bank-ledger-root .empty-state i {
        display: block;
        margin-bottom: 10px;
        font-size: 25px;
        opacity: .35;
    }

    /* Pagination */
    #bank-ledger-root .card-footer nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        margin: 0 !important;
    }

    #bank-ledger-root .card-footer nav > div.d-sm-none,
    #bank-ledger-root .card-footer nav > .d-sm-none {
        display: none !important;
    }

    #bank-ledger-root .card-footer nav > div.d-none,
    #bank-ledger-root .card-footer nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #bank-ledger-root .card-footer nav p,
    #bank-ledger-root .card-footer nav .small.text-muted,
    #bank-ledger-root .card-footer nav div:has(> p),
    #bank-ledger-root .card-footer nav > div.d-none > div:first-child,
    #bank-ledger-root .card-footer nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }

    #bank-ledger-root .card-footer .pagination {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        padding: 0 !important;
        margin: 0 !important;
        list-style: none !important;
    }

    #bank-ledger-root .card-footer .page-item {
        display: inline-flex !important;
    }

    #bank-ledger-root .card-footer .page-link,
    #bank-ledger-root .card-footer .page-item span {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 27px !important;
        height: 27px !important;
        padding: 0 7px !important;
        font-size: 10px !important;
        font-weight: 600 !important;
        color: #495057 !important;
        background: #fff !important;
        border: 1px solid #e0e3e8 !important;
        border-radius: 5px !important;
        line-height: 1 !important;
        text-decoration: none !important;
    }

    #bank-ledger-root .card-footer .page-link:hover {
        color: var(--bank-primary) !important;
        background: var(--bank-primary-soft) !important;
        border-color: #c6dafa !important;
    }

    #bank-ledger-root .card-footer .page-item.active .page-link,
    #bank-ledger-root .card-footer .page-item.active span {
        color: #fff !important;
        background: var(--bank-primary) !important;
        border-color: var(--bank-primary) !important;
    }

    #bank-ledger-root .card-footer .page-item.disabled .page-link,
    #bank-ledger-root .card-footer .page-item.disabled span {
        opacity: .4 !important;
        cursor: not-allowed !important;
    }

    #bank-ledger-root .card-footer nav svg {
        width: 11px !important;
        height: 11px !important;
    }

    /* Alerts */
    #bank-ledger-root .alert {
        border: 1px solid transparent !important;
        font-size: 11.5px;
    }

    /* Modals */
    .bank-modal-scope .modal-content {
        border: 1px solid var(--bank-border) !important;
        box-shadow: 0 12px 40px rgba(0, 0, 0, .14) !important;
        overflow: hidden;
    }

    .bank-modal-scope .modal-header {
        padding: 12px 15px !important;
        border-bottom: 1px solid rgba(255,255,255,.08);
    }

    .bank-modal-scope .modal-title {
        font-size: 13px;
        font-weight: 700;
    }

    .bank-modal-scope .modal-body {
        padding: 17px !important;
    }

    .bank-modal-scope .modal-footer {
        padding: 10px 15px !important;
        border-top: 1px solid var(--bank-border);
    }

    .bank-modal-scope .form-label {
        margin-bottom: 5px;
    }

    .bank-modal-scope .modal-dialog {
        max-width: 560px;
    }

    /* Responsive */
    @media (max-width: 991.98px) {
        #bank-ledger-root .bank-page-header {
            align-items: flex-start;
        }

        #bank-ledger-root .kpi-value {
            font-size: 17px;
        }

        #bank-ledger-root .account-actions {
            justify-content: flex-start;
        }
    }

    @media (max-width: 767.98px) {
        #bank-ledger-root {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #bank-ledger-root .bank-page-header {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }

        #bank-ledger-root .bank-page-title {
            font-size: 16px;
        }

        #bank-ledger-root .top-action-btns {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
            justify-content: stretch;
        }

        #bank-ledger-root .top-action-btns .btn {
            width: 100%;
            min-width: 0;
            padding-left: 5px !important;
            padding-right: 5px !important;
        }

        #bank-ledger-root .kpi-stat-card {
            min-height: 95px;
            padding: 11px 10px 11px 12px;
        }

        #bank-ledger-root .kpi-label {
            font-size: 9px;
        }

        #bank-ledger-root .kpi-value {
            font-size: 15px;
        }

        #bank-ledger-root .kpi-description {
            font-size: 9px;
        }

        #bank-ledger-root .section-card,
        #bank-ledger-root .filter-card {
            padding: 11px;
        }

        #bank-ledger-root .account-box {
            padding: 11px;
        }

        #bank-ledger-root .account-actions {
            gap: 4px;
        }

        #bank-ledger-root .filter-actions {
            width: 100%;
        }

        #bank-ledger-root .filter-actions .btn {
            flex: 1;
        }

        #bank-ledger-root .card-footer {
            flex-direction: column !important;
            align-items: center !important;
            gap: 9px !important;
            text-align: center;
        }

        #bank-ledger-root .card-footer nav {
            width: 100%;
            justify-content: center !important;
        }

        #bank-ledger-root .table-count {
            font-size: 9px;
        }

        .bank-modal-scope .modal-dialog {
            margin: 10px;
        }
    }

    @media (max-width: 420px) {
        #bank-ledger-root .kpi-value {
            font-size: 13px;
        }

        #bank-ledger-root .account-balance {
            font-size: 13px;
        }

        #bank-ledger-root .account-actions .btn {
            font-size: 9px !important;
            padding: 3px 5px !important;
        }

        .bank-modal-scope .modal-body {
            padding: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="bank-ledger-root">

    {{-- Page Header --}}
    <div class="bank-page-header">
        <div>
            <h5 class="bank-page-title">
                <i class="fas fa-landmark"></i>
                <span>Bank & Cash Balance</span>
            </h5>
            <p class="bank-page-subtitle">
                Manage accounts, balances and fund transfers
            </p>
        </div>

        <div class="top-action-btns">
            <button type="button" class="btn btn-sm btn-primary btn-compact shadow-sm" data-bs-toggle="modal" data-bs-target="#transferModal">
                <i class="fas fa-money-bill-transfer"></i>
                Transfer
            </button>

            <button type="button" class="btn btn-sm btn-outline-success btn-compact shadow-sm" data-bs-toggle="modal" data-bs-target="#depositModal">
                <i class="fas fa-plus-circle"></i>
                Deposit
            </button>

            <button type="button" class="btn btn-sm btn-dark btn-compact shadow-sm" data-bs-toggle="modal" data-bs-target="#createAccountModal">
                <i class="fas fa-building-columns"></i>
                Add Account
            </button>

            <a href="{{ url('/admin/accounts/bank-balance/export-csv') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-sm">
                <i class="fas fa-file-csv"></i>
                Export CSV
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 shadow-sm d-flex align-items-center justify-content-between mb-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle fs-5"></i>
                <span class="fw-semibold">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close shadow-none py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 shadow-sm d-flex align-items-center justify-content-between mb-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-triangle-exclamation fs-5"></i>
                <span class="fw-semibold">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close shadow-none py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Financial Summary --}}
    <div class="row g-2 mb-3">

        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-liquid">
                <span class="kpi-label">Total Assets</span>
                <h5 class="kpi-value text-primary font-monospace bn-digit">
                    ৳ {{ number_format($totalLiquidAssets, 2) }}
                </h5>
                <small class="kpi-description">Bank, cash & mobile wallets</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-bank">
                <span class="kpi-label">Bank Balance</span>
                <h5 class="kpi-value text-success font-monospace bn-digit">
                    ৳ {{ number_format($totalBankBalance, 2) }}
                </h5>
                <small class="kpi-description">All bank accounts</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-cash">
                <span class="kpi-label">Cash in Hand</span>
                <h5 class="kpi-value text-warning font-monospace bn-digit">
                    ৳ {{ number_format($totalCashInHand, 2) }}
                </h5>
                <small class="kpi-description">Physical cash balance</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-mobile">
                <span class="kpi-label">Mobile Wallets</span>
                <h5 class="kpi-value text-danger font-monospace bn-digit">
                    ৳ {{ number_format($totalMobileMoney, 2) }}
                </h5>
                <small class="kpi-description">bKash, Nagad & others</small>
            </div>
        </div>

    </div>

    {{-- Active Accounts --}}
    <div class="card border-0 shadow-sm bank-card section-card mb-3">

        <h6 class="section-heading">
            <i class="fas fa-vault"></i>
            Active Accounts
        </h6>

        <div class="row g-2">
            @foreach($accounts as $acc)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="account-box">

                        <div>
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <strong class="account-name text-truncate" title="{{ $acc->account_name }}">
                                    {{ $acc->account_name }}
                                </strong>

                                <span class="badge {{ $acc->account_type === 'cash' ? 'bg-warning text-dark' : ($acc->account_type === 'mobile_banking' ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-success-subtle text-success border border-success-subtle') }}">
                                    {{ strtoupper(str_replace('_', ' ', $acc->account_type)) }}
                                </span>
                            </div>

                            <small class="account-meta d-block font-monospace mb-2 text-truncate">
                                {{ $acc->bank_name ?? 'Physical Vault' }} {{ $acc->account_number ? '• ' . $acc->account_number : '' }}
                            </small>
                        </div>

                        <div class="d-flex justify-content-between align-items-end gap-2 pt-2 border-top mt-2 flex-wrap">
                            <div class="flex-grow-1">
                                <small class="account-balance-label">Current Balance</small>
                                <span class="account-balance font-monospace bn-digit">
                                    ৳ {{ number_format((float)$acc->current_balance, 2) }}
                                </span>
                            </div>

                            <div class="account-actions">
                                <button type="button" class="btn btn-outline-primary btn-compact" onclick="openQuickTransfer({{ $acc->id }}, '{{ addslashes($acc->account_name) }}')" title="Transfer from this account">
                                    Transfer
                                </button>

                                <button type="button" class="btn btn-outline-success btn-compact" onclick="openQuickDeposit({{ $acc->id }}, '{{ addslashes($acc->account_name) }}')" title="Deposit into this account">
                                    Deposit
                                </button>

                                <a href="{{ url('/admin/accounts/bank-balance/statement/' . $acc->id) }}" class="btn btn-outline-dark btn-compact d-inline-flex align-items-center gap-1" title="View Account Statement / Passbook">
                                    <i class="fas fa-file-lines"></i>
                                    Passbook
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Transaction Filters --}}
    <div class="card border-0 shadow-sm bank-card filter-card mb-3">

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
                    <option value="custom" {{ $dateFilter === 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>

            <div class="col-6 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="bankStartCol">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control font-monospace bn-digit" value="{{ request('start_date', $start->format('Y-m-d')) }}">
            </div>

            <div class="col-6 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="bankEndCol">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control font-monospace bn-digit" value="{{ request('end_date', $end->format('Y-m-d')) }}">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label">Account</label>
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
                    <option value="transfer" {{ request('type') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                    <option value="deposit" {{ request('type') === 'deposit' ? 'selected' : '' }}>Deposit</option>
                    <option value="withdraw" {{ request('type') === 'withdraw' ? 'selected' : '' }}>Withdrawal</option>
                </select>
            </div>

            <div class="col-12 col-md-3 filter-actions">
                <button type="submit" class="btn btn-dark btn-compact flex-grow-1 shadow-sm">
                    <i class="fas fa-filter"></i>
                    Filter
                </button>

                @if(request()->anyFilled(['date_filter', 'account_id', 'type', 'start_date', 'end_date', 'search']))
                    <a href="{{ url('/admin/accounts/bank-balance') }}" class="btn btn-light border btn-compact shadow-sm">
                        <i class="fas fa-rotate-left"></i>
                        Reset
                    </a>
                @endif
            </div>

        </form>
    </div>

    {{-- Transaction Ledger --}}
    <div class="card border-0 shadow-sm bank-card overflow-hidden">

        <div class="card-header bg-white py-3 px-3 border-bottom d-flex justify-content-between align-items-center gap-2 flex-wrap">
            <h6 class="table-title d-flex align-items-center gap-2">
                <i class="fas fa-list-check text-primary"></i>
                Transactions
            </h6>

            <span class="table-count font-monospace">
                {{ $transactions->firstItem() ?? 0 }} - {{ $transactions->lastItem() ?? 0 }}
                / {{ $transactions->total() }}
            </span>
        </div>

        <div class="card-body p-0">
            <div class="bank-table-wrapper">
                <table class="table align-middle bank-table mb-0">

                    <thead>
                        <tr>
                            <th class="ps-3 text-center">#</th>
                            <th>Date</th>
                            <th class="text-center">Type</th>
                            <th>From Account</th>
                            <th>To Account</th>
                            <th>Reference #</th>
                            <th class="text-end">Amount (৳)</th>
                            <th>Note</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($transactions as $idx => $trx)
                            <tr>
                                <td class="ps-3 text-center font-monospace bn-digit text-muted">
                                    {{ $transactions->firstItem() + $idx }}
                                </td>

                                <td class="font-monospace bn-digit text-muted">
                                    {{ date('d M Y', strtotime($trx->transaction_date)) }}
                                </td>

                                <td class="text-center">
                                    <span class="badge {{ $trx->transaction_type === 'transfer' ? 'bg-primary-subtle text-primary border border-primary-subtle' : ($trx->transaction_type === 'deposit' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-dark border border-warning-subtle') }} font-monospace text-uppercase">
                                        {{ strtoupper($trx->transaction_type) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="text-dark d-block text-truncate fw-semibold" style="max-width: 180px;" title="{{ $trx->from_account_name ?? '—' }}">
                                        {{ $trx->from_account_name ?? '—' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="text-dark d-block text-truncate fw-semibold" style="max-width: 180px;" title="{{ $trx->to_account_name ?? '—' }}">
                                        {{ $trx->to_account_name ?? '—' }}
                                    </span>
                                </td>

                                <td class="font-monospace bn-digit text-muted">
                                    {{ $trx->reference_no ?? '—' }}
                                </td>

                                <td class="text-end font-monospace bn-digit fw-bold {{ $trx->transaction_type === 'deposit' ? 'text-success' : 'text-primary' }}">
                                    ৳ {{ number_format((float)$trx->amount, 2) }}
                                </td>

                                <td>
                                    <span class="text-muted d-block text-truncate" style="max-width: 180px;" title="{{ $trx->note ?? '—' }}">
                                        {{ $trx->note ?? '—' }}
                                    </span>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="8" class="empty-state">
                                    <i class="fas fa-receipt"></i>
                                    <span class="fw-semibold">No transactions found.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>

        @if($transactions->hasPages())
            <div class="p-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-white card-footer">

                <small class="text-muted font-monospace bn-digit" style="font-size: 10px;">
                    Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }}
                </small>

                <div>
                    {{ $transactions->links('pagination::bootstrap-5') }}
                </div>

            </div>
        @endif

    </div>
</div>

{{-- Modal 1: Fund Transfer --}}
<div class="modal fade bank-modal-scope" id="transferModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title d-flex align-items-center gap-2 mb-0">
                    <i class="fas fa-money-bill-transfer text-primary"></i>
                    Fund Transfer
                </h6>

                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ url('/admin/accounts/bank-balance/transfer') }}" method="POST" id="transferBalanceForm">
                @csrf

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-12 col-md-6">
                            <label class="form-label">From Account <span class="text-danger">*</span></label>
                            <select name="from_account_id" id="transferFromSelect" class="form-select font-monospace" required onchange="handleFromAccountChange()">
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} (৳{{ number_format((float)$acc->current_balance, 2) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">To Account <span class="text-danger">*</span></label>
                            <select name="to_account_id" id="transferToSelect" class="form-select font-monospace" required onchange="handleToAccountChange()">
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_name }} (৳{{ number_format((float)$acc->current_balance, 2) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Amount (৳) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control font-monospace bn-digit text-end fw-bold text-primary" placeholder="0.00" min="1" step="any" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="transfer_date" class="form-control font-monospace bn-digit" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Reference / Slip #</label>
                            <input type="text" name="reference_no" class="form-control font-monospace" placeholder="SLIP-10293">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Note</label>
                            <textarea name="note" rows="2" class="form-control" placeholder="Transfer details..."></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary btn-compact shadow-sm px-3">
                        <i class="fas fa-check-circle"></i>
                        Transfer
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

{{-- Modal 2: Deposit --}}
<div class="modal fade bank-modal-scope" id="depositModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title d-flex align-items-center gap-2 mb-0">
                    <i class="fas fa-plus-circle text-success"></i>
                    Deposit / Top-up
                </h6>

                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ url('/admin/accounts/bank-balance/deposit') }}" method="POST">
                @csrf

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-12 col-md-6">
                            <label class="form-label">Account <span class="text-danger">*</span></label>
                            <select name="account_id" id="depositAccountSelect" class="form-select font-monospace" required>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Amount (৳) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control font-monospace bn-digit text-end fw-bold text-success" placeholder="0.00" min="1" step="any" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="transaction_date" class="form-control font-monospace bn-digit" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Reference / Slip #</label>
                            <input type="text" name="reference_no" class="form-control font-monospace" placeholder="DEP-9938">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Note</label>
                            <textarea name="note" rows="2" class="form-control" placeholder="Additional details..."></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-success btn-compact shadow-sm px-3">
                        <i class="fas fa-check-circle"></i>
                        Save Deposit
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

{{-- Modal 3: Add Account --}}
<div class="modal fade bank-modal-scope" id="createAccountModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title d-flex align-items-center gap-2 mb-0">
                    <i class="fas fa-building-columns text-warning"></i>
                    Add Financial Account
                </h6>

                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ url('/admin/accounts/bank-balance/account/store') }}" method="POST">
                @csrf

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-12 col-md-6">
                            <label class="form-label">Account Name <span class="text-danger">*</span></label>
                            <input type="text" name="account_name" class="form-control" placeholder="City Bank Business, Counter Cash #2" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Account Type <span class="text-danger">*</span></label>
                            <select name="account_type" class="form-select font-monospace" required>
                                <option value="bank" selected>Commercial Bank</option>
                                <option value="cash">Cash Drawer</option>
                                <option value="mobile_banking">Mobile Banking (bKash/Nagad)</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Account / Phone</label>
                            <input type="text" name="account_number" class="form-control font-monospace" placeholder="150-101-9988">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Bank / Service</label>
                            <input type="text" name="bank_name" class="form-control" placeholder="City Bank, bKash">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Branch / Outlet</label>
                            <input type="text" name="branch_name" class="form-control" placeholder="Gulshan Branch">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Opening Balance (৳)</label>
                            <input type="number" name="opening_balance" class="form-control font-monospace bn-digit text-end" placeholder="0.00" min="0" step="any" value="0.00">
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-dark btn-compact shadow-sm px-3">
                        <i class="fas fa-check-circle"></i>
                        Create Account
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

{{-- Modal 4: Notice --}}
<div class="modal fade bank-modal-scope" id="bankNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">

            <div class="modal-body p-1">
                <div class="mb-2 text-warning">
                    <i class="fas fa-triangle-exclamation fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1" id="bankNoticeTitle" style="font-size: 14px;">
                    Notification
                </h6>

                <p class="text-muted small mb-3" id="bankNoticeMessage" style="font-size: 12px; line-height: 1.45;"></p>

                <button type="button" class="btn btn-primary btn-compact px-4" data-bs-dismiss="modal">
                    OK
                </button>
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

// Keep From and To accounts different
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

                showBankNoticeModal(
                    'Transfer From and Transfer To accounts must be different!',
                    'Invalid Account Pair'
                );

                return false;
            }
        });
    }

    syncTransferDropdowns('from');
});
</script>

@endsection
