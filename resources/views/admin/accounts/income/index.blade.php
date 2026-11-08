@extends('layouts.admin')

@section('title', 'Business Inflows & Other Income - ' . config('app.name'))

@php
    $totalIncome  = $totalIncome ?? 0;
    $totalRecords = $totalRecords ?? 0;
    $topCat       = isset($categoryBreakdown) ? $categoryBreakdown->first() : null;
    $dateFilter   = $dateFilter ?? 'this_month';
@endphp

@section('admin_content')
<style>
    #income-root {
        --inc-primary: #2563eb;
        --inc-primary-soft: #eff6ff;
        --inc-success: #16a34a;
        --inc-success-soft: #f0fdf4;
        --inc-warning: #d97706;
        --inc-danger: #dc2626;
        --inc-text: #1e293b;
        --inc-muted: #64748b;
        --inc-border: #e2e8f0;
        --inc-bg: #f8fafc;
        color: var(--inc-text);
        font-size: 12px;
    }

    #income-root *,
    .inc-modal-scope * {
        box-sizing: border-box;
    }

    #income-root .btn,
    #income-root button,
    #income-root .badge,
    #income-root .alert,
    #income-root .form-control,
    #income-root .form-select,
    #income-root .page-link,
    #income-root .card,
    #income-root .income-card,
    #income-root .kpi-stat-card,
    #income-root .proof-thumb-box,
    .inc-modal-scope .modal-content,
    .inc-modal-scope .btn,
    .inc-modal-scope .form-control,
    .inc-modal-scope .form-select,
    .inc-modal-scope .badge {
        border-radius: 5px !important;
    }

    #income-root .input-group > .input-group-text {
        border-radius: 5px 0 0 5px !important;
    }

    #income-root .input-group > .form-control,
    #income-root .input-group > .form-select {
        border-radius: 0 5px 5px 0 !important;
    }

    #income-root .font-monospace,
    #income-root .bn-digit {
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }

    #income-root .btn-compact,
    .inc-modal-scope .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-height: 31px;
        padding: 6px 11px !important;
        font-size: 11.5px !important;
        font-weight: 600 !important;
        line-height: 1.25;
        white-space: nowrap;
        transition: all .15s ease;
    }

    #income-root .btn-compact:focus,
    .inc-modal-scope .btn-compact:focus {
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .12) !important;
    }

    /* Cards */
    #income-root .income-card {
        background: #fff;
        border: 1px solid var(--inc-border) !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .035) !important;
        margin-bottom: 15px;
    }

    #income-root .card-header {
        min-height: 43px;
        background: #fff !important;
        border-bottom: 1px solid var(--inc-border) !important;
    }

    #income-root .kpi-stat-card {
        position: relative;
        height: 100%;
        padding: 14px 15px;
        background: #fff;
        border: 1px solid var(--inc-border);
        box-shadow: 0 2px 7px rgba(15, 23, 42, .035);
        overflow: hidden;
    }

    #income-root .kpi-stat-card::before {
        content: "";
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: 3px;
    }

    #income-root .kpi-total::before {
        background: var(--inc-success);
    }

    #income-root .kpi-records::before {
        background: var(--inc-primary);
    }

    #income-root .kpi-top-cat::before {
        background: var(--inc-warning);
    }

    #income-root .kpi-label {
        display: block;
        margin-bottom: 6px;
        color: var(--inc-muted);
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .035em;
        text-transform: uppercase;
    }

    #income-root .kpi-value {
        margin: 0;
        font-size: 19px;
        font-weight: 700;
        line-height: 1.35;
    }

    #income-root .kpi-caption {
        display: block;
        margin-top: 4px;
        color: var(--inc-muted);
        font-size: 10.5px;
    }

    /* Header */
    #income-root .page-heading {
        color: var(--inc-text);
        font-size: 17px;
        font-weight: 700;
        line-height: 1.35;
    }

    #income-root .page-subtitle {
        margin-top: 3px;
        color: var(--inc-muted);
        font-size: 11.5px;
        line-height: 1.5;
    }

    #income-root .top-action-btns {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
    }

    /* Forms */
    #income-root .form-label,
    .inc-modal-scope .form-label {
        display: block;
        margin-bottom: 5px;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
    }

    #income-root .form-control,
    #income-root .form-select,
    .inc-modal-scope .form-control,
    .inc-modal-scope .form-select {
        min-height: 34px;
        padding: 6px 9px;
        border: 1px solid #cbd5e1;
        background-color: #fff;
        color: #1e293b;
        font-size: 12px;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #income-root .form-control:focus,
    #income-root .form-select:focus,
    .inc-modal-scope .form-control:focus,
    .inc-modal-scope .form-select:focus {
        border-color: var(--inc-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
        outline: none;
    }

    #income-root .form-control::placeholder,
    .inc-modal-scope .form-control::placeholder {
        color: #94a3b8;
        opacity: 1;
    }

    /* Table */
    #income-root .pc-fit-container {
        width: 100%;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }

    #income-root .income-table {
        width: 100%;
        min-width: 1200px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: auto;
    }

    #income-root .income-table thead th {
        padding: 10px 9px;
        background: #f8fafc;
        color: #64748b;
        border-bottom: 1px solid var(--inc-border);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .035em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    #income-root .income-table tbody td {
        padding: 10px 9px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        font-size: 11.5px;
        vertical-align: middle;
    }

    #income-root .income-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #income-root .income-table tbody tr:hover {
        background: #f8fafc;
    }

    #income-root .income-table .col-inc-title {
        min-width: 190px;
        white-space: normal;
    }

    #income-root .income-table .col-inc-cat,
    #income-root .income-table .col-inc-account,
    #income-root .income-table .col-inc-ref {
        min-width: 125px;
    }

    #income-root .income-table .col-inc-date {
        min-width: 105px;
    }

    #income-root .income-table .col-inc-amount {
        min-width: 125px;
        white-space: nowrap;
    }

    #income-root .income-table .col-inc-receipt,
    #income-root .income-table .col-inc-action {
        min-width: 75px;
    }

    #income-root .income-table .col-inc-title strong,
    #income-root .income-table .col-inc-title small {
        white-space: normal;
        overflow-wrap: anywhere;
    }

    #income-root .income-table .badge {
        padding: 4px 7px !important;
        font-size: 10px !important;
        font-weight: 600;
        white-space: normal;
        text-align: left;
    }

    /* Receipt */
    #income-root .proof-thumb-box {
        display: inline-flex;
        position: relative;
        width: 34px;
        height: 34px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 1px solid #dbe3ed;
        background: #fff;
        cursor: pointer;
        transition: border-color .15s ease, transform .15s ease;
    }

    #income-root .proof-thumb-box:hover {
        border-color: var(--inc-primary);
        transform: translateY(-1px);
    }

    #income-root .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Pagination */
    #income-root .card-footer nav {
        display: flex !important;
        width: auto !important;
        margin: 0 !important;
        align-items: center !important;
        justify-content: flex-end !important;
    }

    #income-root .card-footer nav > div.d-sm-none,
    #income-root .card-footer nav > .d-sm-none {
        display: none !important;
    }

    #income-root .card-footer nav > div.d-none,
    #income-root .card-footer nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #income-root .card-footer nav p,
    #income-root .card-footer nav .small.text-muted,
    #income-root .card-footer nav div:has(> p),
    #income-root .card-footer nav > div.d-none > div:first-child,
    #income-root .card-footer nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }

    #income-root .card-footer .pagination {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    #income-root .card-footer .page-item {
        display: inline-flex !important;
    }

    #income-root .card-footer .page-item .page-link,
    #income-root .card-footer .page-item span {
        display: inline-flex !important;
        min-width: 28px !important;
        height: 28px !important;
        padding: 0 7px !important;
        align-items: center !important;
        justify-content: center !important;
        border: 1px solid #dbe3ed !important;
        border-radius: 5px !important;
        background: #fff !important;
        color: #475569 !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        line-height: 1 !important;
        text-decoration: none !important;
    }

    #income-root .card-footer .page-item .page-link:hover {
        background: var(--inc-primary-soft) !important;
        border-color: #93c5fd !important;
        color: var(--inc-primary) !important;
    }

    #income-root .card-footer .page-item.active .page-link,
    #income-root .card-footer .page-item.active span {
        background: var(--inc-primary) !important;
        border-color: var(--inc-primary) !important;
        color: #fff !important;
    }

    #income-root .card-footer .page-item.disabled .page-link,
    #income-root .card-footer .page-item.disabled span {
        opacity: .45 !important;
        cursor: not-allowed !important;
    }

    #income-root .card-footer nav svg {
        width: 11px !important;
        height: 11px !important;
    }

    /* Modals */
    .inc-modal-scope .modal-content {
        overflow: hidden;
        border: 1px solid #e2e8f0 !important;
        background: #fff;
    }

    .inc-modal-scope .modal-header {
        min-height: 48px;
        border-bottom: 1px solid #e2e8f0;
    }

    .inc-modal-scope .modal-title {
        font-size: 13px;
        font-weight: 700;
    }

    .inc-modal-scope .modal-body {
        background: #fff;
    }

    .inc-modal-scope .modal-footer {
        border-top: 1px solid #e2e8f0;
    }

    #incomeReceiptZoomImgTag {
        max-width: 100%;
        max-height: 480px;
        object-fit: contain;
        border-radius: 5px;
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

        #income-root .no-print,
        .admin-dark-sidebar,
        .admin-header,
        .main-footer {
            display: none !important;
        }

        #income-root {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        #income-root .income-card {
            border: 1px solid #000 !important;
            box-shadow: none !important;
        }

        #income-root .pc-fit-container {
            overflow: visible !important;
        }

        #income-root .income-table {
            min-width: 0 !important;
            width: 100% !important;
            font-size: 9px !important;
        }

        #income-root .income-table th,
        #income-root .income-table td {
            padding: 5px 4px !important;
            white-space: normal !important;
        }
    }

    /* Tablet */
    @media (max-width: 991.98px) {
        #income-root .top-action-btns {
            justify-content: flex-start;
        }

        #income-root .pc-fit-container {
            overflow-x: auto !important;
        }

        #income-root .income-table {
            min-width: 1200px;
        }
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        #income-root {
            padding-top: 12px !important;
        }

        #income-root .header-top-wrap {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 10px !important;
        }

        #income-root .page-heading {
            font-size: 16px;
        }

        #income-root .page-subtitle {
            font-size: 11px;
        }

        #income-root .top-action-btns {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
            gap: 6px;
        }

        #income-root .top-action-btns .btn {
            width: 100%;
            min-width: 0;
            padding: 7px 5px !important;
            font-size: 10.5px !important;
        }

        #income-root .kpi-stat-card {
            padding: 12px 11px;
        }

        #income-root .kpi-value {
            font-size: 16px;
        }

        #income-root .kpi-label {
            font-size: 9.5px;
        }

        #income-root .income-card > .card-header {
            flex-wrap: wrap;
            gap: 7px;
        }

        #income-root .income-card > .card-header .badge {
            white-space: normal;
            text-align: right;
        }

        #income-root .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        #income-root .income-table {
            min-width: 1200px !important;
            table-layout: auto !important;
        }

        #income-root .card-footer {
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 9px !important;
            text-align: center;
        }

        #income-root .card-footer nav {
            width: 100% !important;
            justify-content: center !important;
        }

        #income-root .filter-actions {
            width: 100%;
        }

        #income-root .filter-actions .btn {
            flex: 1;
        }

        .inc-modal-scope .modal-dialog {
            margin: 10px;
        }

        .inc-modal-scope .modal-body {
            padding: 12px !important;
        }
    }

    @media (max-width: 380px) {
        #income-root .kpi-value {
            font-size: 14px;
        }

        #income-root .top-action-btns .btn {
            font-size: 10px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="income-root">

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 header-top-wrap no-print">
        <div>
            <h5 class="page-heading mb-1 d-flex align-items-center gap-2">
                <i class="fas fa-hand-holding-dollar text-primary"></i>
                <span>Business Income</span>
            </h5>
            <small class="page-subtitle d-block">
                Track other income, commissions and receipts
            </small>
        </div>

        <div class="top-action-btns">
            <button type="button" class="btn btn-sm btn-dark btn-compact shadow-sm" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>

            <button type="button" class="btn btn-sm btn-primary btn-compact shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
                <i class="fas fa-plus"></i> Add Income
            </button>

            <button type="button" class="btn btn-sm btn-outline-secondary btn-compact shadow-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="fas fa-tags"></i> Add Category
            </button>

            <a href="{{ url('/admin/accounts/income/export-csv') }}" class="btn btn-sm btn-outline-success btn-compact shadow-sm">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>

            <a href="{{ route('admin.report.profit_loss') }}" class="btn btn-sm btn-outline-primary btn-compact shadow-sm">
                <i class="fas fa-chart-line"></i> P&amp;L Report
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small border-0 shadow-sm d-flex align-items-center justify-content-between mb-3 no-print" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close shadow-none py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-sm d-flex align-items-center justify-content-between mb-3 no-print" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-triangle-exclamation text-danger fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close shadow-none py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- KPI Cards -->
    <div class="row g-2 mb-3">
        <div class="col-12 col-md-4">
            <div class="kpi-stat-card kpi-total">
                <span class="kpi-label">Total Income</span>
                <h5 class="kpi-value text-success font-monospace bn-digit">
                    ৳ {{ number_format($totalIncome, 2) }}
                </h5>
                <small class="kpi-caption font-monospace">
                    {{ $start->format('d M Y') }} — {{ $end->format('d M Y') }}
                </small>
            </div>
        </div>

        <div class="col-6 col-md-4">
            <div class="kpi-stat-card kpi-records">
                <span class="kpi-label">Total Receipts</span>
                <h5 class="kpi-value text-primary font-monospace bn-digit">
                    {{ $totalRecords }}
                </h5>
                <small class="kpi-caption">Recorded entries</small>
            </div>
        </div>

        <div class="col-6 col-md-4">
            <div class="kpi-stat-card kpi-top-cat">
                <span class="kpi-label">Top Income Category</span>
                <h5 class="kpi-value text-dark" style="font-size: 15px;" title="{{ $topCat->cat_name ?? 'N/A' }}">
                    {{ $topCat->cat_name ?? 'N/A' }}
                </h5>
                <small class="kpi-caption text-success fw-bold font-monospace bn-digit">
                    ৳ {{ number_format((float)($topCat->cat_total ?? 0), 2) }}
                </small>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm income-card p-3 no-print">
        <form action="{{ url('/admin/accounts/income') }}" method="GET" class="row g-2 align-items-end">

            <div class="col-12 col-md-3">
                <label class="form-label">Period</label>
                <select name="date_filter" id="incomeDateFilter" class="form-select font-monospace" onchange="toggleCustomIncomeDates(this.value)">
                    <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ $dateFilter === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="this_week" {{ $dateFilter === 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ $dateFilter === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $dateFilter === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="this_year" {{ $dateFilter === 'this_year' ? 'selected' : '' }}>This Year</option>
                    <option value="custom" {{ $dateFilter === 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>

            <div class="col-6 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="incomeStartCol">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control font-monospace bn-digit" value="{{ request('start_date', $start->format('Y-m-d')) }}">
            </div>

            <div class="col-6 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="incomeEndCol">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control font-monospace bn-digit" value="{{ request('end_date', $end->format('Y-m-d')) }}">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select font-monospace">
                    <option value="all">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string)request('category_id') === (string)$cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label">Payment Method</label>
                <select name="payment_method" class="form-select font-monospace">
                    <option value="all">All Methods</option>
                    <option value="Cash" {{ request('payment_method') === 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="bKash" {{ request('payment_method') === 'bKash' ? 'selected' : '' }}>bKash</option>
                    <option value="Nagad" {{ request('payment_method') === 'Nagad' ? 'selected' : '' }}>Nagad</option>
                    <option value="Bank Transfer" {{ request('payment_method') === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                </select>
            </div>

            <div class="col-12 col-md-3 d-flex gap-2 filter-actions">
                <button type="submit" class="btn btn-primary btn-compact flex-grow-1 shadow-sm fw-bold">
                    <i class="fas fa-filter"></i> Apply
                </button>

                @if(request()->anyFilled(['date_filter', 'category_id', 'payment_method', 'start_date', 'end_date']))
                    <a href="{{ url('/admin/accounts/income') }}" class="btn btn-light border btn-compact shadow-sm">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Income Table -->
    <div class="card border-0 shadow-sm income-card">
        <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13px;">
                <i class="fas fa-receipt text-primary"></i>
                <span>Income Records</span>
            </h6>

            <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 10px;">
                {{ $incomes->firstItem() ?? 0 }} - {{ $incomes->lastItem() ?? 0 }} / {{ $incomes->total() }}
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive pc-fit-container">
                <table class="table align-middle income-table mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3 col-inc-id text-center">#</th>
                            <th class="col-inc-date">Date</th>
                            <th class="col-inc-cat">Category</th>
                            <th class="col-inc-title">Income Details</th>
                            <th class="col-inc-account">Credited Account</th>
                            <th class="col-inc-ref">Reference No</th>
                            <th class="text-end col-inc-amount">Amount (৳)</th>
                            <th class="text-center col-inc-receipt no-print">Receipt</th>
                            <th class="text-end pe-3 col-inc-action no-print">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($incomes as $idx => $inc)
                            <tr id="income-row-{{ $inc->id }}">
                                <td class="ps-3 col-inc-id text-center font-monospace bn-digit text-muted">
                                    {{ $incomes->firstItem() + $idx }}
                                </td>

                                <td class="col-inc-date font-monospace bn-digit text-muted">
                                    {{ date('d M Y', strtotime($inc->income_date)) }}
                                </td>

                                <td class="col-inc-cat">
                                    <span class="badge bg-light text-dark border font-monospace">
                                        {{ $inc->category_name ?? 'General' }}
                                    </span>
                                </td>

                                <td class="col-inc-title">
                                    <div>
                                        <strong class="text-dark d-block" style="font-size: 12px; line-height: 1.4;" title="{{ $inc->title }}">
                                            {{ $inc->title }}
                                        </strong>

                                        @if(!empty($inc->note))
                                            <small class="text-muted d-block mt-1" style="font-size: 10px;" title="{{ $inc->note }}">
                                                {{ $inc->note }}
                                            </small>
                                        @endif
                                    </div>
                                </td>

                                <td class="col-inc-account">
                                    <span class="badge bg-success-subtle text-success border">
                                        {{ $inc->bank_account_name ?? $inc->payment_method }}
                                    </span>
                                </td>

                                <td class="col-inc-ref font-monospace bn-digit text-muted">
                                    {{ $inc->reference_no ?? '—' }}
                                </td>

                                <td class="text-end col-inc-amount font-monospace bn-digit fw-bold text-success">
                                    +৳ {{ number_format((float)$inc->amount, 2) }}
                                </td>

                                <td class="text-center col-inc-receipt no-print">
                                    @if(!empty($inc->voucher_receipt))
                                        @php
                                            $isPdf = str_ends_with(strtolower($inc->voucher_receipt), '.pdf');
                                        @endphp

                                        @if(!$isPdf)
                                            <div class="proof-thumb-box shadow-sm mx-auto" onclick="zoomIncomeSlip('{{ asset($inc->voucher_receipt) }}', 'Income #{{ $inc->id }} Receipt')" title="View Receipt">
                                                <img src="{{ asset($inc->voucher_receipt) }}" alt="Receipt" onerror="this.src='https://placehold.co/80x80?text=Slip';">
                                            </div>
                                        @else
                                            <a href="{{ asset($inc->voucher_receipt) }}" target="_blank" class="btn btn-light border btn-compact py-0 px-2 text-secondary shadow-sm" style="height: 26px;" title="View Document / Memo">
                                                <i class="fas fa-file-invoice"></i>
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-muted font-monospace">—</span>
                                    @endif
                                </td>

                                <td class="text-end pe-3 col-inc-action no-print">
                                    <button type="button" class="btn btn-light border text-danger p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 28px; height: 28px; font-size: 11px;" onclick="triggerDeleteIncomeModal({{ $inc->id }}, '{{ addslashes($inc->title) }}')" title="Delete Record">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted small">
                                    <i class="fas fa-hand-holding-dollar fa-2x mb-2 d-block opacity-25"></i>
                                    <span class="fw-semibold">No income records found.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($incomes->hasPages())
            <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">
                <small class="text-muted bn-digit font-monospace" style="font-size: 10.5px;">
                    Showing {{ $incomes->firstItem() }} to {{ $incomes->lastItem() }} of {{ $incomes->total() }}
                </small>

                <div>
                    {{ $incomes->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Add Income Modal -->
<div class="modal fade inc-modal-scope" id="addIncomeModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-2 mb-0">
                    <i class="fas fa-plus-circle text-success"></i>
                    <span>Add Business Income</span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ url('/admin/accounts/income/store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="modal-body p-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">
                                Income Title / Source <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="title" class="form-control" placeholder="Commission, Scrap Sale" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Category <span class="text-danger">*</span>
                            </label>
                            <select name="category_id" class="form-select font-monospace" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Amount (৳) <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="amount" class="form-control font-monospace bn-digit text-end fw-bold text-success" placeholder="0.00" min="0.01" step="any" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="income_date" class="form-control font-monospace bn-digit" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Deposit To Account <span class="text-danger">*</span>
                            </label>
                            <select name="bank_account_id" id="incomeBankAccountId" class="form-select font-monospace" onchange="handleIncomeAccountChange(this)" required>
                                @if(isset($bankAccounts) && $bankAccounts->isNotEmpty())
                                    @foreach($bankAccounts as $acc)
                                        <option value="{{ $acc->id }}" data-type="{{ $acc->account_type }}" {{ $acc->account_type === 'cash' ? 'selected' : '' }}>
                                            {{ $acc->account_name }} (৳{{ number_format((float)$acc->current_balance, 2) }})
                                        </option>
                                    @endforeach
                                @else
                                    <option value="">No Account Found</option>
                                @endif
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" id="incomePaymentMethod" class="form-select font-monospace">
                                <option value="Cash" selected>Cash Handover</option>
                                <option value="bKash">bKash</option>
                                <option value="Nagad">Nagad</option>
                                <option value="Rocket">Rocket</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cheque">Bank Cheque</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Reference No</label>
                            <input type="text" name="reference_no" class="form-control font-monospace" placeholder="INC-0012">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Receipt / Document</label>
                            <input type="file" name="receipt" class="form-control" accept="image/*,application/pdf">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="note" rows="2" class="form-control" placeholder="Additional details..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-compact shadow-sm fw-bold px-3">
                        <i class="fas fa-check-circle"></i> Save Income
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade inc-modal-scope" id="addCategoryModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold">Add Income Category</h6>
                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ url('/admin/accounts/income/category/store') }}" method="POST">
                @csrf

                <div class="modal-body p-3">
                    <label class="form-label">Category Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="Courier Claims" required>
                </div>

                <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-compact shadow-sm fw-bold px-3">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade inc-modal-scope" id="deleteIncomeConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2 text-danger">
                    <i class="fas fa-trash-can fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Delete Income Record?</h6>

                <p class="text-muted small mb-3" style="font-size: 11.5px; line-height: 1.5;">
                    Delete <strong id="deleteIncomeTitle" class="text-dark">this record</strong>? The amount will be deducted from the account balance.
                </p>

                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-compact shadow-sm fw-bold" id="btnExecuteIncomeDelete">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Preview Modal -->
<div class="modal fade inc-modal-scope" id="incomeReceiptZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;" id="incomeReceiptZoomTitle">
                    <i class="fas fa-image text-primary me-1"></i> Receipt Preview
                </span>
                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-2">
                <img id="incomeReceiptZoomImgTag" src="" alt="Receipt Memo" class="img-fluid border">
            </div>
        </div>
    </div>
</div>

<!-- Notice Modal -->
<div class="modal fade inc-modal-scope" id="incomeNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2" id="incomeNoticeIconWrap">
                    <i class="fas fa-circle-check text-success fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1" id="incomeNoticeTitle" style="font-size: 14px;">Notification</h6>

                <p class="text-muted small mb-3" id="incomeNoticeMessage" style="font-size: 12px; line-height: 1.45;"></p>

                <button type="button" class="btn btn-primary btn-compact px-4" data-bs-dismiss="modal">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let activeDeleteIncomeId = null;

function showIncomeNoticeModal(message, title = 'Notice', type = 'info') {
    const titleEl = document.getElementById('incomeNoticeTitle');
    const msgEl   = document.getElementById('incomeNoticeMessage');
    const iconEl  = document.getElementById('incomeNoticeIconWrap');

    if (titleEl) titleEl.innerText = title;
    if (msgEl) msgEl.innerText = message;

    if (iconEl) {
        if (type === 'danger' || type === 'error') {
            iconEl.innerHTML = '<i class="fas fa-circle-xmark text-danger fa-2x"></i>';
        } else if (type === 'success') {
            iconEl.innerHTML = '<i class="fas fa-circle-check text-success fa-2x"></i>';
        } else {
            iconEl.innerHTML = '<i class="fas fa-circle-info text-primary fa-2x"></i>';
        }
    }

    const modalEl = document.getElementById('incomeNoticeModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

function zoomIncomeSlip(url, title = 'Receipt Slip Preview') {
    const titleEl = document.getElementById('incomeReceiptZoomTitle');
    const imgTag  = document.getElementById('incomeReceiptZoomImgTag');
    if (url && imgTag) {
        if (titleEl) titleEl.innerText = title;
        imgTag.src = url;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('incomeReceiptZoomModal')).show();
    }
}

function toggleCustomIncomeDates(val) {
    const s = document.getElementById('incomeStartCol');
    const e = document.getElementById('incomeEndCol');
    if (val === 'custom') {
        s.classList.remove('d-none');
        e.classList.remove('d-none');
    } else {
        s.classList.add('d-none');
        e.classList.add('d-none');
    }
}

function handleIncomeAccountChange(select) {
    const selectedOption = select.options[select.selectedIndex];
    const type = selectedOption ? selectedOption.getAttribute('data-type') : null;
    const methodSelect = document.getElementById('incomePaymentMethod');
    if (methodSelect && type) {
        if (type === 'cash') methodSelect.value = 'Cash';
        else if (type === 'mobile_banking') methodSelect.value = 'bKash';
        else if (type === 'bank') methodSelect.value = 'Bank Transfer';
    }
}

function triggerDeleteIncomeModal(id, title) {
    activeDeleteIncomeId = id;
    document.getElementById('deleteIncomeTitle').innerText = title;
    const modalEl = document.getElementById('deleteIncomeConfirmModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

document.getElementById('btnExecuteIncomeDelete').addEventListener('click', function() {
    if (!activeDeleteIncomeId) return;

    const modalEl = document.getElementById('deleteIncomeConfirmModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getInstance(modalEl)?.hide();
    }

    const url = "{{ url('/admin/accounts/income/delete') }}/" + activeDeleteIncomeId;

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
            const row = document.getElementById('income-row-' + activeDeleteIncomeId);
            if (row) row.remove();
            showIncomeNoticeModal('Income record deleted successfully and balance adjusted.', 'Deleted Successfully', 'success');
        } else {
            showIncomeNoticeModal(data.message || 'Failed to delete income record.', 'Error', 'danger');
        }
    })
    .catch(err => {
        console.error(err);
        showIncomeNoticeModal('Connection error while deleting income record.', 'Error', 'danger');
    })
    .finally(() => {
        activeDeleteIncomeId = null;
    });
});
</script>
@endsection