@extends('layouts.admin')

@section('title', 'Capital & Owner Investment Ledger - ' . config('app.name'))

@php
    // Safe media resolver supporting Global Media Library paths
    $resolveInvestmentReceipt = function($path, $fallback = 'https://placehold.co/80x80?text=Slip') {
        if (empty($path) || $path === 'undefined' || $path === 'null') return $fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) return show_image($path, $fallback);
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset(ltrim(str_replace(['public_html/', 'public/'], '', $path), '/'));
    };

    $allTimeCapital  = $allTimeCapital ?? 0;
    $totalInvestment = $totalInvestment ?? 0;
    $totalRecords    = $totalRecords ?? 0;
    $uniqueInvestors = $uniqueInvestors ?? 0;
    $dateFilter      = $dateFilter ?? 'this_month';
@endphp

@section('admin_content')

<style>
    /* =====================================================
       INVESTMENT LEDGER — WHITE CLASSIC THEME
    ===================================================== */

    #investment-root {
        --inv-primary: #4e73df;
        --inv-primary-soft: #eef2ff;
        --inv-success: #16a36a;
        --inv-warning: #d99a16;
        --inv-danger: #dc3545;
        --inv-text: #344054;
        --inv-muted: #667085;
        --inv-border: #e5e9f0;
        --inv-bg: #f8fafc;
        --inv-radius: 5px;
        color: var(--inv-text);
        font-size: 12px;
    }

    #investment-root *,
    .inv-modal-scope * {
        box-sizing: border-box;
    }

    /* Consistent Radius */
    #investment-root .btn,
    #investment-root button,
    #investment-root .badge,
    #investment-root .alert,
    #investment-root .form-control,
    #investment-root .form-select,
    #investment-root .page-link,
    #investment-root .page-item span,
    #investment-root .card,
    #investment-root .investment-card,
    #investment-root .kpi-stat-card,
    #investment-root .proof-thumb-box,
    #investment-root .proof-media-surface,
    .inv-modal-scope .modal-content,
    .inv-modal-scope .btn,
    .inv-modal-scope .form-control,
    .inv-modal-scope .form-select,
    .inv-modal-scope .badge {
        border-radius: 5px !important;
    }

    #investment-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #investment-root .input-group > .form-control,
    #investment-root .input-group > .form-select {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Typography */
    #investment-root .bn-digit,
    #investment-root .font-monospace {
        font-weight: 600 !important;
    }

    #investment-root h5,
    #investment-root h6 {
        letter-spacing: -0.2px;
    }

    /* Buttons */
    #investment-root .btn-compact {
        min-height: 31px;
        padding: 6px 11px !important;
        font-size: 11px !important;
        line-height: 1.25 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        white-space: nowrap;
        transition: all 0.18s ease;
    }

    #investment-root .btn-compact:hover {
        transform: translateY(-1px);
    }

    #investment-root .btn-primary,
    .inv-modal-scope .btn-primary {
        background-color: var(--inv-primary);
        border-color: var(--inv-primary);
    }

    /* Main Cards */
    #investment-root .investment-card {
        background: #fff;
        border: 1px solid var(--inv-border) !important;
        box-shadow: 0 2px 8px rgba(16, 24, 40, 0.035) !important;
        margin-bottom: 14px;
    }

    #investment-root .card-header {
        background: #fff;
        border-bottom: 1px solid var(--inv-border);
    }

    /* Header */
    #investment-root .page-heading {
        color: #1d2939;
        font-size: 17px;
        font-weight: 700;
        line-height: 1.35;
    }

    #investment-root .page-subtitle {
        color: var(--inv-muted);
        font-size: 11.5px;
        line-height: 1.5;
    }

    /* KPI Cards */
    #investment-root .kpi-stat-card {
        position: relative;
        height: 100%;
        min-height: 105px;
        padding: 13px 14px;
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--inv-border);
        box-shadow: 0 2px 7px rgba(16, 24, 40, 0.035);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    #investment-root .kpi-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(16, 24, 40, 0.07);
    }

    #investment-root .kpi-stat-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
    }

    #investment-root .kpi-capital::before {
        background: #6366f1;
    }

    #investment-root .kpi-period::before {
        background: #0d8bd9;
    }

    #investment-root .kpi-records::before {
        background: #16a36a;
    }

    #investment-root .kpi-investors::before {
        background: #e5a323;
    }

    #investment-root .kpi-label {
        display: block;
        margin-bottom: 7px;
        color: #667085;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .45px;
        text-transform: uppercase;
    }

    #investment-root .kpi-value {
        margin: 0;
        font-size: 17px;
        font-weight: 700;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    #investment-root .kpi-caption {
        display: block;
        margin-top: 5px;
        color: #98a2b3;
        font-size: 10px;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    /* Form Controls */
    #investment-root .form-label,
    .inv-modal-scope .form-label {
        display: block;
        margin-bottom: 5px;
        color: #475467;
        font-size: 11px;
        font-weight: 600;
    }

    #investment-root .form-control,
    #investment-root .form-select,
    .inv-modal-scope .form-control,
    .inv-modal-scope .form-select {
        min-height: 34px;
        padding: 6px 10px;
        color: #344054;
        background-color: #fff;
        border: 1px solid #d0d5dd;
        font-size: 12px;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #investment-root .form-control:focus,
    #investment-root .form-select:focus,
    .inv-modal-scope .form-control:focus,
    .inv-modal-scope .form-select:focus {
        border-color: var(--inv-primary);
        box-shadow: 0 0 0 3px rgba(78, 115, 223, .11);
        outline: none;
    }

    /* Filter Card */
    #investment-root .filter-card {
        padding: 14px;
        background: #fff;
        border: 1px solid var(--inv-border);
        box-shadow: 0 2px 8px rgba(16, 24, 40, .035);
    }

    /* Table */
    #investment-root .investment-table {
        width: 100%;
        min-width: 1150px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: auto;
    }

    #investment-root .investment-table thead th {
        padding: 11px 9px;
        color: #667085;
        background: #f8fafc;
        border-bottom: 1px solid #e5e9f0;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .35px;
        white-space: nowrap;
        vertical-align: middle;
    }

    #investment-root .investment-table tbody td {
        padding: 10px 9px;
        color: #344054;
        border-bottom: 1px solid #f0f2f5;
        font-size: 11.5px;
        vertical-align: middle;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    #investment-root .investment-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #investment-root .investment-table tbody tr {
        transition: background-color .15s ease;
    }

    #investment-root .investment-table tbody tr:hover {
        background: #f8faff;
    }

    #investment-root .investment-table .col-inv-id {
        min-width: 55px;
    }

    #investment-root .investment-table .col-inv-date {
        min-width: 105px;
    }

    #investment-root .investment-table .col-inv-name {
        min-width: 190px;
    }

    #investment-root .investment-table .col-inv-account {
        min-width: 145px;
    }

    #investment-root .investment-table .col-inv-method {
        min-width: 115px;
    }

    #investment-root .investment-table .col-inv-ref {
        min-width: 135px;
    }

    #investment-root .investment-table .col-inv-amount {
        min-width: 125px;
    }

    #investment-root .investment-table .col-inv-receipt {
        min-width: 75px;
    }

    #investment-root .investment-table .col-inv-action {
        min-width: 75px;
    }

    #investment-root .table-responsive.pc-fit-container {
        width: 100%;
        max-width: 100%;
        overflow-x: auto !important;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
    }

    #investment-root .investment-table .investor-name,
    #investment-root .investment-table .investor-note,
    #investment-root .investment-table .account-name,
    #investment-root .investment-table .reference-value {
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    #investment-root .investment-table .investor-name {
        color: #344054;
        font-size: 11.5px;
        font-weight: 700;
        line-height: 1.45;
    }

    #investment-root .investment-table .investor-note {
        display: block;
        margin-top: 3px;
        color: #98a2b3;
        font-size: 10px;
        line-height: 1.4;
    }

    #investment-root .investment-table .account-name {
        display: inline-block;
        max-width: 100%;
        padding: 4px 7px;
        color: #475467;
        background: #f1f5ff;
        border: 1px solid #e0e7ff;
        border-radius: 5px;
        font-size: 10px;
        line-height: 1.4;
    }

    /* Receipt Preview */
    #investment-root .proof-media-surface {
        padding: 11px;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        transition: all .2s ease;
    }

    #investment-root .proof-media-surface:hover {
        background: #f1f5ff;
        border-color: var(--inv-primary);
    }

    #investment-root .proof-thumb-box {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        flex-shrink: 0;
        overflow: hidden;
        background: #fff;
        border: 1px solid #d0d5dd;
        cursor: pointer;
    }

    #investment-root .proof-thumb-box img,
    .inv-modal-scope .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #investment-root .proof-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(16, 24, 40, .48);
        opacity: 0;
        transition: opacity .15s ease;
    }

    #investment-root .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    /* Pagination */
    #investment-root .card-footer nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }

    #investment-root .card-footer nav > div.d-sm-none,
    #investment-root .card-footer nav > .d-sm-none {
        display: none !important;
    }

    #investment-root .card-footer nav > div.d-none,
    #investment-root .card-footer nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #investment-root .card-footer nav p,
    #investment-root .card-footer nav .small.text-muted,
    #investment-root .card-footer nav div:has(> p),
    #investment-root .card-footer nav > div.d-none > div:first-child,
    #investment-root .card-footer nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }

    #investment-root .card-footer .pagination {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    #investment-root .card-footer .page-item {
        display: inline-flex !important;
    }

    #investment-root .card-footer .page-item .page-link,
    #investment-root .card-footer .page-item span {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 28px !important;
        height: 28px !important;
        padding: 0 7px !important;
        color: #475467 !important;
        background: #fff !important;
        border: 1px solid #e0e5ec !important;
        border-radius: 5px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        line-height: 1 !important;
        text-decoration: none !important;
        transition: all .15s ease !important;
    }

    #investment-root .card-footer .page-item .page-link:hover {
        color: var(--inv-primary) !important;
        background: #f1f5ff !important;
        border-color: #c7d2fe !important;
    }

    #investment-root .card-footer .page-item.active .page-link,
    #investment-root .card-footer .page-item.active span {
        color: #fff !important;
        background: var(--inv-primary) !important;
        border-color: var(--inv-primary) !important;
        box-shadow: 0 2px 5px rgba(78, 115, 223, .2) !important;
    }

    #investment-root .card-footer .page-item.disabled .page-link,
    #investment-root .card-footer .page-item.disabled span {
        opacity: .45 !important;
        background: #f8fafc !important;
        cursor: not-allowed !important;
    }

    #investment-root .card-footer nav svg {
        width: 11px !important;
        height: 11px !important;
    }

    /* Modal */
    .inv-modal-scope .modal-content {
        overflow: hidden;
        border: 1px solid #e5e9f0 !important;
        background: #fff;
        box-shadow: 0 15px 45px rgba(16, 24, 40, .16) !important;
    }

    .inv-modal-scope .modal-header {
        border-bottom: 1px solid #e5e9f0;
    }

    .inv-modal-scope .modal-title {
        font-size: 13px;
    }

    .inv-modal-scope .modal-footer {
        border-top: 1px solid #e5e9f0;
    }

    .inv-modal-scope .modal-body {
        color: #344054;
    }

    /* Print */
    @media print {
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        body {
            color: #000 !important;
            background: #fff !important;
        }

        #investment-root .no-print,
        .admin-dark-sidebar,
        .admin-header,
        .main-footer {
            display: none !important;
        }

        #investment-root {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        #investment-root .investment-card {
            margin-bottom: 10px !important;
            border: 1px solid #000 !important;
            box-shadow: none !important;
        }

        #investment-root .table-responsive.pc-fit-container {
            overflow: visible !important;
        }

        #investment-root .investment-table {
            min-width: 0 !important;
            width: 100% !important;
        }

        #investment-root .investment-table th,
        #investment-root .investment-table td {
            padding: 5px !important;
            font-size: 9px !important;
        }
    }

    /* Tablet */
    @media (max-width: 991.98px) {
        #investment-root .header-top-wrap {
            align-items: flex-start !important;
        }

        #investment-root .investment-table {
            min-width: 1100px;
        }
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        #investment-root {
            padding-right: 10px !important;
            padding-left: 10px !important;
        }

        #investment-root .header-top-wrap {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 10px !important;
        }

        #investment-root .page-heading {
            font-size: 16px;
        }

        #investment-root .page-subtitle {
            font-size: 11px;
        }

        #investment-root .top-action-btns {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 7px !important;
            width: 100%;
        }

        #investment-root .top-action-btns .btn {
            width: 100%;
            min-width: 0;
            white-space: normal;
        }

        #investment-root .kpi-stat-card {
            min-height: 100px;
            padding: 11px 10px;
        }

        #investment-root .kpi-value {
            font-size: 14px;
        }

        #investment-root .kpi-label {
            font-size: 9px;
        }

        #investment-root .kpi-caption {
            font-size: 9px;
        }

        #investment-root .filter-card {
            padding: 11px !important;
        }

        #investment-root .investment-table {
            min-width: 1100px !important;
        }

        #investment-root .card-footer {
            flex-direction: column !important;
            gap: 9px !important;
            justify-content: center !important;
            align-items: center !important;
        }

        #investment-root .card-footer nav {
            width: 100% !important;
            justify-content: center !important;
        }

        .inv-modal-scope .modal-dialog {
            margin: 10px;
        }

        .inv-modal-scope .modal-body {
            padding: 12px !important;
        }
    }

    @media (max-width: 380px) {
        #investment-root .top-action-btns {
            grid-template-columns: 1fr 1fr;
        }

        #investment-root .top-action-btns .btn {
            font-size: 10px !important;
            padding: 6px 5px !important;
        }

        #investment-root .kpi-value {
            font-size: 12px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="investment-root">

    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3 header-top-wrap no-print">
        <div>
            <h5 class="page-heading mb-1 d-flex align-items-center gap-2">
                <i class="fas fa-chart-line" style="color: #4e73df;"></i>
                <span>Capital &amp; Owner Investment Ledger</span>
            </h5>
            <p class="page-subtitle mb-0">
                Manage owner capital and investor deposits
            </p>
        </div>

        <div class="top-action-btns d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-dark btn-compact shadow-sm" onclick="window.print()">
                <i class="fas fa-print"></i>
                <span>Print A4</span>
            </button>

            <button type="button" class="btn btn-sm btn-primary btn-compact shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addInvestmentModal">
                <i class="fas fa-plus-circle"></i>
                <span>Add Investment</span>
            </button>

            <a href="{{ url('/admin/accounts/investment/export-csv') }}" class="btn btn-sm btn-outline-success btn-compact shadow-sm">
                <i class="fas fa-file-csv"></i>
                <span>Export CSV</span>
            </a>

            <a href="{{ url('/admin/accounts/bank-balance') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-sm">
                <i class="fas fa-landmark"></i>
                <span>Bank Balances</span>
            </a>

            <a href="{{ route('admin.report.balance_sheet') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-sm">
                <i class="fas fa-scale-balanced"></i>
                <span>Balance Sheet</span>
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm py-2 px-3 mb-3 small d-flex align-items-center justify-content-between no-print">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm py-2 px-3 mb-3 small d-flex align-items-center justify-content-between no-print">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-triangle-exclamation text-danger fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="row g-2 mb-3">

        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-capital">
                <span class="kpi-label">Total Invested Capital</span>
                <h5 class="kpi-value font-monospace bn-digit" style="color: #6366f1;">
                    ৳ {{ number_format($allTimeCapital, 2) }}
                </h5>
                <small class="kpi-caption">Lifetime capital</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-period">
                <span class="kpi-label">Period Investment</span>
                <h5 class="kpi-value text-primary font-monospace bn-digit">
                    ৳ {{ number_format($totalInvestment, 2) }}
                </h5>
                <small class="kpi-caption font-monospace">
                    {{ $start->format('d M Y') }} — {{ $end->format('d M Y') }}
                </small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-records">
                <span class="kpi-label">Investment Entries</span>
                <h5 class="kpi-value text-success font-monospace bn-digit">
                    {{ $totalRecords }} Records
                </h5>
                <small class="kpi-caption">Recorded deposits</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-investors">
                <span class="kpi-label">Active Investors</span>
                <h5 class="kpi-value text-warning font-monospace bn-digit">
                    {{ $uniqueInvestors }} Entities
                </h5>
                <small class="kpi-caption">Unique capital sources</small>
            </div>
        </div>

    </div>

    {{-- Filters --}}
    <div class="card investment-card filter-card mb-3 no-print">
        <form action="{{ url('/admin/accounts/investment') }}" method="GET" class="row g-2 align-items-end">

            <div class="col-12 col-md-3">
                <label class="form-label">Date Range</label>
                <select name="date_filter" id="investmentDateFilter" class="form-select font-monospace" onchange="toggleCustomInvestmentDates(this.value)">
                    <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ $dateFilter === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="this_week" {{ $dateFilter === 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ $dateFilter === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $dateFilter === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="this_year" {{ $dateFilter === 'this_year' ? 'selected' : '' }}>This Year</option>
                    <option value="custom" {{ $dateFilter === 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>

            <div class="col-6 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="investmentStartCol">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control font-monospace bn-digit" value="{{ request('start_date', $start->format('Y-m-d')) }}">
            </div>

            <div class="col-6 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="investmentEndCol">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control font-monospace bn-digit" value="{{ request('end_date', $end->format('Y-m-d')) }}">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label">Payment Method</label>
                <select name="payment_method" class="form-select font-monospace">
                    <option value="all">All Methods</option>
                    <option value="Cash" {{ request('payment_method') === 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="bKash" {{ request('payment_method') === 'bKash' ? 'selected' : '' }}>bKash</option>
                    <option value="Nagad" {{ request('payment_method') === 'Nagad' ? 'selected' : '' }}>Nagad</option>
                    <option value="Bank Transfer" {{ request('payment_method') === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="Cheque" {{ request('payment_method') === 'Cheque' ? 'selected' : '' }}>Cheque</option>
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label">Search Investor / Ref / Note</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Investor, reference or note...">
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-compact flex-grow-1 shadow-sm fw-bold">
                    <i class="fas fa-filter"></i> Filter
                </button>

                @if(request()->anyFilled(['date_filter', 'payment_method', 'search', 'start_date', 'end_date']))
                    <a href="{{ url('/admin/accounts/investment') }}" class="btn btn-light border btn-compact shadow-sm">
                        Reset
                    </a>
                @endif
            </div>

        </form>
    </div>

    {{-- Investment Ledger --}}
    <div class="card investment-card border-0">

        <div class="card-header py-3 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13px;">
                <i class="fas fa-money-check-dollar" style="color: #4e73df;"></i>
                Capital Investment Journal
            </h6>

            <span class="badge bg-light text-secondary border font-monospace px-2 py-2" style="font-size: 10px;">
                {{ $investments->firstItem() ?? 0 }} - {{ $investments->lastItem() ?? 0 }}
                of {{ $investments->total() }} entries
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive pc-fit-container">
                <table class="table align-middle investment-table mb-0">

                    <thead>
                        <tr>
                            <th class="ps-3 col-inv-id text-center">#</th>
                            <th class="col-inv-date">Date</th>
                            <th class="col-inv-name">Investor / Capital Source</th>
                            <th class="col-inv-account">Credited Account</th>
                            <th class="col-inv-method">Payment Method</th>
                            <th class="col-inv-ref">Reference / Slip #</th>
                            <th class="text-end col-inv-amount">Amount (৳)</th>
                            <th class="text-center col-inv-receipt no-print">Receipt</th>
                            <th class="text-end pe-3 col-inv-action no-print">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($investments as $idx => $inv)

                            <tr id="investment-row-{{ $inv->id }}">

                                <td class="ps-3 col-inv-id text-center font-monospace bn-digit text-muted">
                                    {{ $investments->firstItem() + $idx }}
                                </td>

                                <td class="col-inv-date font-monospace bn-digit text-muted">
                                    {{ date('d M Y', strtotime($inv->investment_date)) }}
                                </td>

                                <td class="col-inv-name">
                                    <div>
                                        <strong class="investor-name" title="{{ $inv->investor_name }}">
                                            {{ $inv->investor_name }}
                                        </strong>

                                        @if(!empty($inv->note))
                                            <small class="investor-note font-monospace" title="{{ $inv->note }}">
                                                {{ $inv->note }}
                                            </small>
                                        @endif
                                    </div>
                                </td>

                                <td class="col-inv-account">
                                    <span class="account-name font-monospace">
                                        {{ $inv->bank_account_name ?? 'Physical Vault' }}
                                    </span>
                                </td>

                                <td class="col-inv-method">
                                    <span class="text-dark">
                                        {{ $inv->payment_method }}
                                    </span>
                                </td>

                                <td class="col-inv-ref font-monospace text-muted">
                                    <span class="reference-value">
                                        {{ $inv->reference_no ?? '—' }}
                                    </span>
                                </td>

                                <td class="text-end col-inv-amount font-monospace bn-digit fw-bold text-success">
                                    +৳ {{ number_format((float)$inv->amount, 2) }}
                                </td>

                                <td class="text-center col-inv-receipt no-print">
                                    @if(!empty($inv->voucher_receipt))
                                        @php
                                            $slipUrl = $resolveInvestmentReceipt($inv->voucher_receipt);
                                            $isPdf = str_ends_with(strtolower($inv->voucher_receipt), '.pdf');
                                        @endphp

                                        @if(!$isPdf)
                                            <div class="proof-thumb-box shadow-sm mx-auto"
                                                 onclick="zoomInvestmentSlip('{{ $slipUrl }}', 'Investment #{{ $inv->id }} Slip')"
                                                 title="View Receipt">

                                                <img src="{{ $slipUrl }}"
                                                     alt="Slip"
                                                     onerror="this.src='https://placehold.co/80x80?text=Slip';">

                                                <div class="proof-thumb-hover-overlay">
                                                    <i class="fas fa-search-plus text-white" style="font-size: 10px;"></i>
                                                </div>
                                            </div>
                                        @else
                                            <a href="{{ $slipUrl }}"
                                               target="_blank"
                                               class="btn btn-light border btn-compact py-0 px-2 text-danger shadow-sm"
                                               style="height: 25px;"
                                               title="View PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-muted font-monospace">—</span>
                                    @endif
                                </td>

                                <td class="text-end pe-3 col-inv-action no-print">
                                    <button type="button"
                                            class="btn btn-light border text-danger d-inline-flex align-items-center justify-content-center shadow-sm"
                                            style="width: 29px; height: 29px; padding: 0;"
                                            onclick="triggerDeleteInvestmentModal({{ $inv->id }}, '{{ addslashes($inv->investor_name) }}')"
                                            title="Delete Entry">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fas fa-chart-line fa-2x mb-2 d-block opacity-25"></i>
                                    <span class="fw-semibold">No investment records found.</span>
                                </td>
                            </tr>

                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>

        @if($investments->hasPages())
            <div class="card-footer p-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 no-print">

                <small class="text-muted font-monospace bn-digit" style="font-size: 10.5px;">
                    Showing {{ $investments->firstItem() }} to {{ $investments->lastItem() }} of {{ $investments->total() }} entries
                </small>

                <div>
                    {{ $investments->links('pagination::bootstrap-5') }}
                </div>

            </div>
        @endif

    </div>
</div>

{{-- Add Investment Modal --}}
<div class="modal fade inv-modal-scope" id="addInvestmentModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-dark text-white py-3 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-2 mb-0">
                    <i class="fas fa-plus-circle" style="color: #8da5ff;"></i>
                    Add Capital Investment
                </h6>

                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ url('/admin/accounts/investment/store') }}" method="POST" enctype="multipart/form-data" id="addInvestmentForm">
                @csrf

                {{-- Global Media Picker --}}
                <input type="hidden" name="voucher_receipt" id="investment_receipt_media_input" value="">

                <div class="modal-body p-3">
                    <div class="row g-3">

                        {{-- Investor Name --}}
                        <div class="col-12">
                            <label class="form-label">
                                Investor / Capital Source <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="investor_name"
                                   class="form-control"
                                   placeholder="Founder or investor name"
                                   required>
                        </div>

                        {{-- Amount --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Amount (৳) <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="amount"
                                   class="form-control font-monospace bn-digit text-end fw-bold text-success"
                                   placeholder="0.00"
                                   min="1"
                                   step="any"
                                   required>
                        </div>

                        {{-- Date --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Investment Date <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   name="investment_date"
                                   class="form-control font-monospace bn-digit"
                                   value="{{ date('Y-m-d') }}"
                                   required>
                        </div>

                        {{-- Deposit Account --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Credit Account <span class="text-danger">*</span>
                            </label>
                            <select name="bank_account_id"
                                    id="investmentBankAccountId"
                                    class="form-select font-monospace"
                                    onchange="handleInvestmentAccountChange(this)"
                                    required>

                                @if(isset($bankAccounts) && $bankAccounts->isNotEmpty())
                                    @foreach($bankAccounts as $acc)
                                        <option value="{{ $acc->id }}"
                                                data-type="{{ $acc->account_type }}"
                                                {{ $acc->account_type === 'bank' ? 'selected' : '' }}>
                                            {{ $acc->account_name }} (৳{{ number_format((float)$acc->current_balance, 2) }})
                                        </option>
                                    @endforeach
                                @else
                                    <option value="">No Accounts Available</option>
                                @endif

                            </select>
                        </div>

                        {{-- Payment Method --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" id="investmentPaymentMethod" class="form-select font-monospace">
                                <option value="Bank Transfer" selected>Bank Transfer</option>
                                <option value="Cash">Cash Handover</option>
                                <option value="Cheque">Bank Cheque</option>
                                <option value="bKash">bKash</option>
                                <option value="Nagad">Nagad</option>
                            </select>
                        </div>

                        {{-- Reference --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label">Reference / Slip #</label>
                            <input type="text"
                                   name="reference_no"
                                   class="form-control font-monospace"
                                   placeholder="SLIP-09823">
                        </div>

                        {{-- Receipt Attachment --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label d-flex justify-content-between align-items-center flex-wrap gap-1">
                                <span>
                                    <i class="fas fa-paperclip text-primary me-1"></i>
                                    Attach Receipt
                                </span>
                                <span class="badge bg-light text-muted border font-monospace" style="font-size: 9px;">
                                    GLOBAL MEDIA
                                </span>
                            </label>

                            <div class="proof-media-surface">

                                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                    <button type="button"
                                            class="btn btn-primary btn-compact shadow-sm fw-bold"
                                            onclick="chooseInvestmentSlipMedia()">
                                        <i class="fas fa-photo-film"></i>
                                        Media Library
                                    </button>
                                </div>

                                <input type="file"
                                       name="receipt"
                                       id="investmentFileInput"
                                       class="form-control mb-2"
                                       accept="image/*,application/pdf"
                                       onchange="handleInvestmentFileSlip(event)">

                                <div id="investmentPreviewContainer"
                                     class="d-none bg-white border p-2 align-items-center gap-2">

                                    <div class="proof-thumb-box shadow-sm"
                                         onclick="zoomInvestmentModalPreview()"
                                         title="Preview Receipt">

                                        <img id="investmentPreviewImg"
                                             src=""
                                             alt="Slip Preview"
                                             onerror="this.src='https://placehold.co/80x80?text=Slip';">

                                        <div class="proof-thumb-hover-overlay">
                                            <i class="fas fa-search-plus text-white" style="font-size: 10px;"></i>
                                        </div>
                                    </div>

                                    <div class="min-w-0 flex-grow-1">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace text-uppercase" style="font-size: 9px;">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Slip Attached
                                        </span>

                                        <small class="text-muted d-block font-monospace text-break"
                                               id="investmentPathText"
                                               style="font-size: 10px;">
                                            slip.jpg
                                        </small>
                                    </div>

                                    <button type="button"
                                            class="btn btn-outline-danger btn-compact py-1 px-2"
                                            onclick="clearInvestmentSlipMedia()">
                                        <i class="fas fa-times"></i>
                                        Clear
                                    </button>

                                </div>
                            </div>
                        </div>

                        {{-- Note --}}
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="note"
                                      rows="2"
                                      class="form-control"
                                      placeholder="Investment purpose or details..."></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-success btn-compact shadow-sm fw-bold px-3">
                        <i class="fas fa-check-circle me-1"></i>
                        Save Investment
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade inv-modal-scope" id="deleteInvestmentConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">

            <div class="modal-body p-1">

                <div class="mb-3 text-danger">
                    <i class="fas fa-trash-can fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-2" style="font-size: 14px;">
                    Delete Investment?
                </h6>

                <p class="text-muted small mb-3" style="font-size: 11.5px; line-height: 1.5;">
                    Delete <strong id="deleteInvestmentTitle" class="text-dark">this record</strong> permanently?
                    The credited balance will be deducted.
                </p>

                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="button"
                            class="btn btn-danger btn-compact shadow-sm fw-bold"
                            id="btnExecuteInvestmentDelete">
                        Yes, Delete
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Receipt Lightbox Modal --}}
<div class="modal fade inv-modal-scope" id="investmentSlipZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">

            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;" id="investmentSlipZoomTitle">
                    <i class="fas fa-image text-primary me-1"></i>
                    Investment Slip Preview
                </span>

                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-2">
                <img id="investmentSlipZoomImgTag"
                     src=""
                     alt="Slip Memo"
                     class="img-fluid border"
                     style="max-height: 480px; object-fit: contain; border-radius: 5px;">
            </div>

        </div>
    </div>
</div>

{{-- Notice Modal --}}
<div class="modal fade inv-modal-scope" id="investmentNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">

            <div class="modal-body p-1">

                <div class="mb-2" id="investmentNoticeIconWrap">
                    <i class="fas fa-circle-check text-success fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1" id="investmentNoticeTitle" style="font-size: 14px;">
                    Notification
                </h6>

                <p class="text-muted small mb-3"
                   id="investmentNoticeMessage"
                   style="font-size: 12px; line-height: 1.45;"></p>

                <button type="button"
                        class="btn btn-primary btn-compact px-4"
                        data-bs-dismiss="modal">
                    OK
                </button>

            </div>
        </div>
    </div>
</div>

<script>
let activeDeleteInvestmentId = null;

function showInvestmentNoticeModal(message, title = 'Notice', type = 'info') {
    const titleEl = document.getElementById('investmentNoticeTitle');
    const msgEl   = document.getElementById('investmentNoticeMessage');
    const iconEl  = document.getElementById('investmentNoticeIconWrap');

    if (titleEl) titleEl.innerText = title;
    if (msgEl) msgEl.innerText = message;

    if (iconEl) {
        if (type === 'danger' || type === 'error') {
            iconEl.innerHTML = '<i class="fas fa-circle-xmark text-danger fa-2x"></i>';
        } else if (type === 'success') {
            iconEl.innerHTML = '<i class="fas fa-circle-check text-success fa-2x"></i>';
        } else if (type === 'warning') {
            iconEl.innerHTML = '<i class="fas fa-triangle-exclamation text-warning fa-2x"></i>';
        } else {
            iconEl.innerHTML = '<i class="fas fa-circle-info text-primary fa-2x"></i>';
        }
    }

    const modalEl = document.getElementById('investmentNoticeModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

// Lightbox Zoom Helper for Investment Slips
function zoomInvestmentSlip(url, title = 'Investment Slip Preview') {
    const titleEl = document.getElementById('investmentSlipZoomTitle');
    const imgTag  = document.getElementById('investmentSlipZoomImgTag');
    if (url && imgTag) {
        if (titleEl) titleEl.innerText = title;
        imgTag.src = url;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('investmentSlipZoomModal')).show();
    }
}

function zoomInvestmentModalPreview() {
    const imgTag = document.getElementById('investmentPreviewImg');
    if (imgTag && imgTag.src) {
        zoomInvestmentSlip(imgTag.src, 'Selected Slip Preview');
    }
}

// Global Media Extractor
function extractMediaItem(mediaInput) {
    if (!mediaInput) return null;
    const item = Array.isArray(mediaInput) ? mediaInput[0] : mediaInput;
    if (!item || typeof item !== 'object') return null;
    const rawPath = item.file_path || item.path || item.url || '';
    const rawUrl  = item.url || item.file_path || item.path || '';
    if (!rawPath || rawPath === 'undefined') return null;
    return {
        path: String(rawPath).trim(),
        url:  String(rawUrl).trim()
    };
}

// Global Media Chooser Integration for Investment Slip
function chooseInvestmentSlipMedia() {
    if (typeof window.openMediaPicker === 'function') {
        window.openMediaPicker({
            multiple: false,
            onSelect: function(media) {
                const picked = extractMediaItem(media);
                if (picked && picked.path) {
                    document.getElementById('investment_receipt_media_input').value = picked.path;
                    const fileInput = document.getElementById('investmentFileInput');
                    if (fileInput) fileInput.value = '';

                    const previewBox = document.getElementById('investmentPreviewContainer');
                    const previewImg = document.getElementById('investmentPreviewImg');
                    const pathText   = document.getElementById('investmentPathText');

                    if (previewImg) previewImg.src = picked.url;
                    if (pathText) {
                        pathText.innerText = picked.path.split('/').pop() || picked.path;
                    }
                    if (previewBox) {
                        previewBox.classList.remove('d-none');
                        previewBox.classList.add('d-flex');
                    }

                    showInvestmentNoticeModal('Investment slip attached from Global Media Library.', 'Slip Attached', 'success');
                }
            }
        });
    } else {
        showInvestmentNoticeModal('Global Media Library is loading, please try again.', 'Notice', 'warning');
    }
}

function handleInvestmentFileSlip(event) {
    const file = event.target.files[0];
    const previewBox = document.getElementById('investmentPreviewContainer');
    const previewImg = document.getElementById('investmentPreviewImg');
    const pathText   = document.getElementById('investmentPathText');

    if (file) {
        document.getElementById('investment_receipt_media_input').value = '';
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (previewImg) previewImg.src = e.target.result;
                if (pathText) pathText.innerText = file.name;
                if (previewBox) {
                    previewBox.classList.remove('d-none');
                    previewBox.classList.add('d-flex');
                }
            };
            reader.readAsDataURL(file);
        } else {
            if (previewImg) previewImg.src = 'https://placehold.co/80x80?text=PDF';
            if (pathText) pathText.innerText = file.name;
            if (previewBox) {
                previewBox.classList.remove('d-none');
                previewBox.classList.add('d-flex');
            }
        }
    }
}

function clearInvestmentSlipMedia() {
    document.getElementById('investment_receipt_media_input').value = '';
    const fileInput = document.getElementById('investmentFileInput');
    if (fileInput) fileInput.value = '';

    const previewBox = document.getElementById('investmentPreviewContainer');
    if (previewBox) {
        previewBox.classList.add('d-none');
        previewBox.classList.remove('d-flex');
    }
}

function toggleCustomInvestmentDates(val) {
    const s = document.getElementById('investmentStartCol');
    const e = document.getElementById('investmentEndCol');
    if (val === 'custom') {
        s.classList.remove('d-none');
        e.classList.remove('d-none');
    } else {
        s.classList.add('d-none');
        e.classList.add('d-none');
    }
}

function handleInvestmentAccountChange(select) {
    const selectedOption = select.options[select.selectedIndex];
    const type = selectedOption ? selectedOption.getAttribute('data-type') : null;
    const methodSelect = document.getElementById('investmentPaymentMethod');
    if (methodSelect && type) {
        if (type === 'cash') methodSelect.value = 'Cash';
        else if (type === 'mobile_banking') methodSelect.value = 'bKash';
        else if (type === 'bank') methodSelect.value = 'Bank Transfer';
    }
}

function triggerDeleteInvestmentModal(id, investorName) {
    activeDeleteInvestmentId = id;
    document.getElementById('deleteInvestmentTitle').innerText = investorName;
    const modalEl = document.getElementById('deleteInvestmentConfirmModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

document.getElementById('btnExecuteInvestmentDelete').addEventListener('click', function() {
    if (!activeDeleteInvestmentId) return;

    const modalEl = document.getElementById('deleteInvestmentConfirmModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getInstance(modalEl)?.hide();
    }

    const url = "{{ url('/admin/accounts/investment/delete') }}/" + activeDeleteInvestmentId;

    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const row = document.getElementById('investment-row-' + activeDeleteInvestmentId);
            if (row) row.remove();
            showInvestmentNoticeModal('Capital investment entry deleted successfully and balance adjusted.', 'Deleted Successfully', 'success');
        } else {
            showInvestmentNoticeModal(data.message || 'Failed to delete investment entry.', 'Error', 'danger');
        }
    })
    .catch(err => {
        console.error(err);
        showInvestmentNoticeModal('Connection error while deleting investment record.', 'Error', 'danger');
    })
    .finally(() => {
        activeDeleteInvestmentId = null;
    });
});
</script>
@endsection