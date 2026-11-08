@extends('layouts.admin')

@section('title', 'Operational Expenses & Cash Outflow - ' . config('app.name'))

@php
    $totalExpense = $totalExpense ?? 0;
    $totalRecords = $totalRecords ?? 0;
    $topCat       = isset($categoryBreakdown) ? $categoryBreakdown->first() : null;
    $dateFilter   = $dateFilter ?? 'this_month';
@endphp

@section('admin_content')
<style>
    /* =========================================================
       EXPENSES — KAIADMIN CLASSIC / WHITE THEME
    ========================================================= */
    #expenses-root {
        --exp-primary: #2563eb;
        --exp-primary-soft: #eff6ff;
        --exp-danger: #dc2626;
        --exp-success: #059669;
        --exp-warning: #d97706;
        --exp-text: #1e293b;
        --exp-muted: #64748b;
        --exp-border: #e2e8f0;
        --exp-bg: #f8fafc;
        --exp-radius: 5px;
        color: var(--exp-text);
        font-size: 12px;
    }

    #expenses-root *,
    #expenses-root *::before,
    #expenses-root *::after,
    .exp-modal-scope *,
    .exp-modal-scope *::before,
    .exp-modal-scope *::after {
        box-sizing: border-box;
    }

    /* Consistent Radius */
    #expenses-root .btn,
    #expenses-root button,
    #expenses-root .badge,
    #expenses-root .alert,
    #expenses-root .form-control,
    #expenses-root .form-select,
    #expenses-root .page-link,
    #expenses-root .page-item span,
    #expenses-root .card,
    #expenses-root .expense-card,
    #expenses-root .kpi-stat-card,
    .exp-modal-scope .modal-content,
    .exp-modal-scope .btn,
    .exp-modal-scope .form-control,
    .exp-modal-scope .form-select,
    .exp-modal-scope .badge {
        border-radius: 5px !important;
    }

    #expenses-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #expenses-root .input-group > .form-control,
    #expenses-root .input-group > .form-select {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    #expenses-root .font-monospace,
    #expenses-root .bn-digit,
    .exp-modal-scope .font-monospace,
    .exp-modal-scope .bn-digit {
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
    }

    /* Header */
    #expenses-root .expense-page-header {
        gap: 12px;
        margin-bottom: 16px;
    }

    #expenses-root .expense-page-title {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 0 0 4px;
        color: #1e293b;
        font-size: 18px;
        font-weight: 700;
        line-height: 1.4;
    }

    #expenses-root .expense-page-title i {
        color: var(--exp-danger);
        font-size: 17px;
    }

    #expenses-root .expense-page-subtitle {
        display: block;
        color: var(--exp-muted);
        font-size: 11.5px;
        line-height: 1.5;
    }

    /* Buttons */
    #expenses-root .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-height: 32px;
        padding: 6px 11px !important;
        border-radius: 5px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        line-height: 1.3 !important;
        white-space: nowrap;
        transition: all .15s ease;
    }

    #expenses-root .btn-compact i {
        font-size: 11px;
    }

    #expenses-root .btn-danger {
        background: var(--exp-danger);
        border-color: var(--exp-danger);
    }

    #expenses-root .btn-danger:hover {
        background: #b91c1c;
        border-color: #b91c1c;
    }

    #expenses-root .expense-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 7px;
    }

    /* Cards */
    #expenses-root .expense-card {
        margin-bottom: 14px;
        border: 1px solid var(--exp-border) !important;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .04) !important;
    }

    #expenses-root .expense-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        min-height: 44px;
        padding: 10px 14px;
        border-bottom: 1px solid var(--exp-border);
        background: #fff;
    }

    #expenses-root .expense-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        color: #1e293b;
        font-size: 13px;
        font-weight: 700;
    }

    #expenses-root .expense-section-title i {
        color: var(--exp-danger);
    }

    /* Alerts */
    #expenses-root .expense-alert {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 12px;
        margin-bottom: 12px;
        border: 1px solid transparent;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
    }

    #expenses-root .expense-alert-content {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
    }

    #expenses-root .expense-alert-content span {
        overflow-wrap: anywhere;
    }

    /* KPI Cards */
    #expenses-root .kpi-stat-card {
        position: relative;
        height: 100%;
        min-height: 108px;
        padding: 14px 14px 12px 16px;
        overflow: hidden;
        border: 1px solid var(--exp-border);
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
        transition: box-shadow .18s ease, transform .18s ease;
    }

    #expenses-root .kpi-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, .07);
    }

    #expenses-root .kpi-stat-card::before {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: 3px;
        content: '';
    }

    #expenses-root .kpi-total::before { background: #ef4444; }
    #expenses-root .kpi-records::before { background: #0284c7; }
    #expenses-root .kpi-top-cat::before { background: #f59e0b; }

    #expenses-root .kpi-label {
        display: block;
        margin-bottom: 7px;
        color: var(--exp-muted);
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .025em;
        text-transform: uppercase;
    }

    #expenses-root .kpi-value {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    #expenses-root .kpi-description {
        display: block;
        margin-top: 4px;
        color: #94a3b8;
        font-size: 10px;
        line-height: 1.4;
    }

    /* Forms */
    #expenses-root .form-label,
    .exp-modal-scope .form-label {
        display: block;
        margin-bottom: 5px;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
    }

    #expenses-root .form-control,
    #expenses-root .form-select,
    .exp-modal-scope .form-control,
    .exp-modal-scope .form-select {
        min-height: 35px;
        padding: 6px 10px;
        border: 1px solid #cbd5e1;
        background-color: #fff;
        color: #1e293b;
        font-size: 11.5px;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #expenses-root .form-control:focus,
    #expenses-root .form-select:focus,
    .exp-modal-scope .form-control:focus,
    .exp-modal-scope .form-select:focus {
        border-color: var(--exp-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
        outline: none;
    }

    .exp-modal-scope textarea.form-control {
        min-height: auto;
        resize: vertical;
    }

    .exp-modal-scope .modal-content {
        overflow: hidden;
        border: 1px solid var(--exp-border) !important;
        box-shadow: 0 15px 45px rgba(15, 23, 42, .16) !important;
    }

    .exp-modal-scope .modal-header {
        min-height: 48px;
        border-bottom: 1px solid rgba(255,255,255,.08);
    }

    .exp-modal-scope .modal-title {
        font-size: 13px;
        font-weight: 700;
    }

    .exp-modal-scope .modal-body {
        background: #fff;
    }

    .exp-modal-scope .modal-footer {
        border-top: 1px solid var(--exp-border);
    }

    /* Table */
    #expenses-root .pc-fit-container {
        width: 100%;
        overflow-x: auto !important;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
    }

    #expenses-root .pc-fit-container::-webkit-scrollbar {
        height: 7px;
    }

    #expenses-root .pc-fit-container::-webkit-scrollbar-track {
        background: #f8fafc;
    }

    #expenses-root .pc-fit-container::-webkit-scrollbar-thumb {
        border-radius: 5px;
        background: #cbd5e1;
    }

    #expenses-root .expense-table {
        width: 100%;
        min-width: 1150px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #expenses-root .expense-table thead th {
        padding: 11px 10px;
        border-bottom: 1px solid var(--exp-border);
        background: #f8fafc;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .035em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    #expenses-root .expense-table tbody td {
        padding: 10px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 11.5px;
        vertical-align: middle;
        white-space: nowrap;
    }

    #expenses-root .expense-table tbody tr {
        transition: background-color .12s ease;
    }

    #expenses-root .expense-table tbody tr:hover {
        background: #f8fafc;
    }

    #expenses-root .expense-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #expenses-root .expense-table .col-exp-id { min-width: 55px; }
    #expenses-root .expense-table .col-exp-date { min-width: 110px; }
    #expenses-root .expense-table .col-exp-cat { min-width: 130px; }
    #expenses-root .expense-table .col-exp-title { min-width: 230px; }
    #expenses-root .expense-table .col-exp-account { min-width: 155px; }
    #expenses-root .expense-table .col-exp-ref { min-width: 140px; }
    #expenses-root .expense-table .col-exp-amount { min-width: 135px; }
    #expenses-root .expense-table .col-exp-receipt { min-width: 85px; }
    #expenses-root .expense-table .col-exp-action { min-width: 75px; }

    #expenses-root .expense-table .expense-title-text {
        display: block;
        max-width: 260px;
        overflow-wrap: anywhere;
        white-space: normal;
        line-height: 1.45;
    }

    #expenses-root .expense-table .expense-note-text {
        display: block;
        max-width: 260px;
        margin-top: 3px;
        overflow-wrap: anywhere;
        white-space: normal;
        line-height: 1.4;
    }

    #expenses-root .expense-table .expense-badge {
        display: inline-block;
        max-width: 100%;
        padding: 4px 7px;
        overflow-wrap: anywhere;
        white-space: normal;
        font-size: 9.5px;
    }

    #expenses-root .expense-table .expense-empty-state {
        padding: 38px 15px !important;
        color: var(--exp-muted);
        text-align: center;
        white-space: normal !important;
    }

    #expenses-root .expense-table .expense-empty-state i {
        display: block;
        margin-bottom: 10px;
        color: #cbd5e1;
        font-size: 27px;
    }

    /* Pagination */
    #expenses-root .card-footer nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }

    #expenses-root .card-footer nav > div.d-sm-none,
    #expenses-root .card-footer nav > .d-sm-none {
        display: none !important;
    }

    #expenses-root .card-footer nav > div.d-none,
    #expenses-root .card-footer nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #expenses-root .card-footer nav p,
    #expenses-root .card-footer nav .small.text-muted,
    #expenses-root .card-footer nav div:has(> p),
    #expenses-root .card-footer nav > div.d-none > div:first-child,
    #expenses-root .card-footer nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }

    #expenses-root .card-footer .pagination {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    #expenses-root .card-footer .page-item {
        display: inline-flex !important;
    }

    #expenses-root .card-footer .page-item .page-link,
    #expenses-root .card-footer .page-item span {
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

    #expenses-root .card-footer .page-item .page-link:hover {
        border-color: #fecaca !important;
        background: #fef2f2 !important;
        color: var(--exp-danger) !important;
    }

    #expenses-root .card-footer .page-item.active .page-link,
    #expenses-root .card-footer .page-item.active span {
        border-color: var(--exp-danger) !important;
        background: var(--exp-danger) !important;
        color: #fff !important;
    }

    #expenses-root .card-footer .page-item.disabled .page-link,
    #expenses-root .card-footer .page-item.disabled span {
        border-color: #e2e8f0 !important;
        background: #f8fafc !important;
        opacity: .45 !important;
        cursor: not-allowed !important;
    }

    #expenses-root .card-footer nav svg {
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

        #expenses-root .no-print,
        .admin-dark-sidebar,
        .admin-header,
        .main-footer {
            display: none !important;
        }

        #expenses-root {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        #expenses-root .expense-card {
            margin-bottom: 10px !important;
            border: 1px solid #000 !important;
            box-shadow: none !important;
        }

        #expenses-root .pc-fit-container {
            overflow: visible !important;
        }

        #expenses-root .expense-table {
            min-width: 0 !important;
            width: 100% !important;
            table-layout: auto !important;
        }

        #expenses-root .expense-table th,
        #expenses-root .expense-table td {
            padding: 5px !important;
            color: #000 !important;
            font-size: 8px !important;
        }
    }

    /* Tablet */
    @media (max-width: 991.98px) {
        #expenses-root .expense-actions {
            justify-content: flex-start;
        }

        #expenses-root .filter-actions {
            grid-column: 1 / -1;
        }
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        #expenses-root {
            padding-right: 10px !important;
            padding-left: 10px !important;
        }

        #expenses-root .expense-page-header {
            align-items: stretch !important;
            gap: 10px;
        }

        #expenses-root .expense-page-title {
            font-size: 16px;
        }

        #expenses-root .expense-page-subtitle {
            font-size: 11px;
        }

        #expenses-root .expense-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
            gap: 6px;
        }

        #expenses-root .expense-actions .btn {
            width: 100%;
            min-width: 0;
            padding-right: 6px !important;
            padding-left: 6px !important;
        }

        #expenses-root .kpi-stat-card {
            min-height: 100px;
            padding: 12px 10px 10px 13px;
        }

        #expenses-root .kpi-label {
            font-size: 9.5px;
        }

        #expenses-root .kpi-value {
            font-size: 14px;
        }

        #expenses-root .kpi-description {
            font-size: 9px;
        }

        #expenses-root .expense-card-header {
            align-items: flex-start;
            padding: 10px 12px;
        }

        #expenses-root .expense-section-title {
            font-size: 12px;
        }

        #expenses-root .card-footer {
            flex-direction: column !important;
            gap: 9px !important;
            justify-content: center !important;
            padding: 10px !important;
        }

        #expenses-root .card-footer nav {
            width: 100% !important;
            justify-content: center !important;
        }

        .exp-modal-scope .modal-dialog {
            margin: 10px;
        }

        .exp-modal-scope .modal-body {
            padding: 12px !important;
        }

        .exp-modal-scope .modal-footer {
            padding: 9px 12px !important;
        }
    }

    @media (max-width: 380px) {
        #expenses-root .kpi-value {
            font-size: 12.5px;
        }

        #expenses-root .btn-compact {
            font-size: 10px !important;
        }

        #expenses-root .expense-page-title {
            font-size: 15px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="expenses-root">

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center expense-page-header header-top-wrap no-print">
        <div>
            <h5 class="expense-page-title">
                <i class="fas fa-money-bill-wave"></i>
                <span>Operational Expenses</span>
            </h5>
            <small class="expense-page-subtitle">
                Manage business expenses and payments
            </small>
        </div>

        <div class="expense-actions top-action-btns">
            <button type="button" class="btn btn-sm btn-dark btn-compact shadow-xs" onclick="window.print()">
                <i class="fas fa-print"></i>
                <span>Print</span>
            </button>

            <button type="button" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold" data-bs-toggle="modal" data-bs-target="#addExpenseModal" style="background-color: #ef4444; border-color: #ef4444;">
                <i class="fas fa-plus-circle"></i>
                <span>Add Expense</span>
            </button>

            <button type="button" class="btn btn-sm btn-outline-dark btn-compact shadow-xs" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="fas fa-tags"></i>
                <span>Add Category</span>
            </button>

            <a href="{{ route('admin.payroll.index') }}" class="btn btn-sm btn-outline-primary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-money-check-dollar"></i>
                <span>Staff Payroll</span>
            </a>

            <a href="{{ url('/admin/accounts/expenses/export-csv') }}" class="btn btn-sm btn-outline-success btn-compact shadow-xs">
                <i class="fas fa-file-csv"></i>
                <span>Export CSV</span>
            </a>

            <a href="{{ route('admin.report.profit_loss') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-chart-line"></i>
                <span>P&amp;L Report</span>
            </a>
        </div>
    </div>

    <!-- Success Alert -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show expense-alert no-print" role="alert">
            <div class="expense-alert-content">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Error Alert -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show expense-alert no-print" role="alert">
            <div class="expense-alert-content">
                <i class="fas fa-triangle-exclamation text-danger fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- KPI Summary -->
    <div class="row g-2 mb-3">

        <div class="col-12 col-md-4">
            <div class="kpi-stat-card kpi-total">
                <span class="kpi-label">Total Expenses</span>

                <h5 class="kpi-value text-danger font-monospace bn-digit">
                    ৳ {{ number_format($totalExpense, 2) }}
                </h5>

                <small class="kpi-description font-monospace">
                    {{ $start->format('d M Y') }} — {{ $end->format('d M Y') }}
                </small>
            </div>
        </div>

        <div class="col-6 col-md-4">
            <div class="kpi-stat-card kpi-records">
                <span class="kpi-label">Total Records</span>

                <h5 class="kpi-value text-primary font-monospace bn-digit">
                    {{ $totalRecords }} Entries
                </h5>

                <small class="kpi-description">
                    Recorded expense entries
                </small>
            </div>
        </div>

        <div class="col-6 col-md-4">
            <div class="kpi-stat-card kpi-top-cat">
                <span class="kpi-label">Top Category</span>

                <h5 class="kpi-value text-dark text-truncate" style="font-size: 15px;" title="{{ $topCat->cat_name ?? 'N/A' }}">
                    {{ $topCat->cat_name ?? 'N/A' }}
                </h5>

                <small class="kpi-description text-warning fw-bold font-monospace bn-digit" style="font-size: 11px;">
                    ৳ {{ number_format((float)($topCat->cat_total ?? 0), 2) }}
                </small>
            </div>
        </div>

    </div>

    <!-- Expense Filters -->
    <div class="card expense-card filter-card p-3 no-print">
        <form action="{{ url('/admin/accounts/expenses') }}" method="GET" class="row g-2 align-items-end">

            <div class="col-12 col-md-3">
                <label class="form-label">Period</label>

                <select name="date_filter" id="expenseDateFilter" class="form-select font-monospace" onchange="toggleCustomExpenseDates(this.value)">
                    <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ $dateFilter === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="this_week" {{ $dateFilter === 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ $dateFilter === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $dateFilter === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="this_year" {{ $dateFilter === 'this_year' ? 'selected' : '' }}>This Year</option>
                    <option value="custom" {{ $dateFilter === 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>

            <div class="col-6 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="expenseStartCol">
                <label class="form-label">Start Date</label>

                <input
                    type="date"
                    name="start_date"
                    class="form-control font-monospace bn-digit"
                    value="{{ request('start_date', $start->format('Y-m-d')) }}"
                >
            </div>

            <div class="col-6 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="expenseEndCol">
                <label class="form-label">End Date</label>

                <input
                    type="date"
                    name="end_date"
                    class="form-control font-monospace bn-digit"
                    value="{{ request('end_date', $end->format('Y-m-d')) }}"
                >
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
                <button type="submit" class="btn btn-dark btn-compact flex-grow-1 shadow-xs fw-bold">
                    <i class="fas fa-filter"></i>
                    Filter
                </button>

                @if(request()->anyFilled(['date_filter', 'category_id', 'payment_method', 'start_date', 'end_date']))
                    <a href="{{ url('/admin/accounts/expenses') }}" class="btn btn-light border btn-compact shadow-xs">
                        <i class="fas fa-rotate-left"></i>
                        Reset
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Expense Ledger -->
    <div class="card expense-card">

        <div class="expense-card-header">
            <h6 class="expense-section-title">
                <i class="fas fa-receipt"></i>
                <span>Expense Records</span>
            </h6>

            <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 10px;">
                {{ $expenses->firstItem() ?? 0 }} - {{ $expenses->lastItem() ?? 0 }}
                / {{ $expenses->total() }}
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive pc-fit-container">
                <table class="table align-middle expense-table mb-0">

                    <thead>
                        <tr>
                            <th class="ps-3 col-exp-id text-center">#</th>
                            <th class="col-exp-date">Date</th>
                            <th class="col-exp-cat">Category</th>
                            <th class="col-exp-title">Expense Details</th>
                            <th class="col-exp-account">Paid From</th>
                            <th class="col-exp-ref">Reference No.</th>
                            <th class="text-end col-exp-amount">Amount (৳)</th>
                            <th class="text-center col-exp-receipt no-print">Receipt</th>
                            <th class="text-end pe-3 col-exp-action no-print">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($expenses as $idx => $exp)
                            @php
                                $isSalaryExpense = (isset($exp->category_name) && (str_contains(strtolower($exp->category_name), 'salary') || str_contains(strtolower($exp->category_name), 'বেতন') || str_contains(strtolower($exp->category_name), 'payroll')))
                                    || (isset($exp->reference_no) && str_starts_with($exp->reference_no, 'SAL-'))
                                    || (isset($exp->title) && str_contains(strtolower($exp->title), 'staff salary'));
                            @endphp
                            <tr id="expense-row-{{ $exp->id }}">

                                <td class="ps-3 col-exp-id text-center font-monospace bn-digit text-muted" style="font-size: 10.5px;">
                                    {{ $expenses->firstItem() + $idx }}
                                </td>

                                <td class="col-exp-date font-monospace bn-digit text-muted" style="font-size: 10.5px;">
                                    {{ date('d M Y', strtotime($exp->expense_date)) }}
                                </td>

                                <td class="col-exp-cat">
                                    @if($isSalaryExpense)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace expense-badge">
                                            <i class="fas fa-money-check-dollar me-1"></i> {{ $exp->category_name ?? 'Staff Salary' }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark border font-monospace expense-badge">
                                            {{ $exp->category_name ?? 'General' }}
                                        </span>
                                    @endif
                                </td>

                                <td class="col-exp-title">
                                    <div class="min-w-0">
                                        <strong class="text-dark expense-title-text" style="font-size: 12px;" title="{{ $exp->title }}">
                                            {{ $exp->title }}
                                        </strong>

                                        @if(!empty($exp->note))
                                            <small class="text-muted expense-note-text font-monospace" style="font-size: 9.5px;" title="{{ $exp->note }}">
                                                {{ $exp->note }}
                                            </small>
                                        @endif
                                    </div>
                                </td>

                                <td class="col-exp-account">
                                    <span class="badge bg-light text-primary border font-monospace expense-badge" title="{{ $exp->bank_account_name ?? $exp->payment_method }}">
                                        {{ $exp->bank_account_name ?? $exp->payment_method }}
                                    </span>
                                </td>

                                <td class="col-exp-ref font-monospace bn-digit text-muted" style="font-size: 10.5px;">
                                    @if(!empty($exp->reference_no) && str_starts_with($exp->reference_no, 'SAL-'))
                                        <span class="text-primary fw-bold" title="Staff Salary Voucher">
                                            <i class="fas fa-file-invoice me-1"></i>{{ $exp->reference_no }}
                                        </span>
                                    @else
                                        {{ $exp->reference_no ?? '—' }}
                                    @endif
                                </td>

                                <td class="text-end col-exp-amount font-monospace bn-digit fw-bold text-danger" style="font-size: 12px;">
                                    ৳ {{ number_format((float)$exp->amount, 2) }}
                                </td>

                                <td class="text-center col-exp-receipt no-print">
                                    @if(!empty($exp->voucher_receipt))
                                        <a href="{{ asset($exp->voucher_receipt) }}" target="_blank" class="btn btn-light border btn-compact py-0 px-1 text-secondary shadow-xs" style="height: 25px; font-size: 10px !important;" title="View Receipt">
                                            <i class="fas fa-file-invoice"></i>
                                        </a>
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 10px;">—</span>
                                    @endif
                                </td>

                                <td class="text-end pe-3 col-exp-action no-print">
                                    <button type="button" class="btn btn-light border text-danger p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 27px; height: 27px; font-size: 10px;" onclick="deleteExpense({{ $exp->id }})" title="Delete Record">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>

                            </tr>

                        @empty
                            <tr>
                                <td colspan="9" class="expense-empty-state">
                                    <i class="fas fa-money-bill-transfer"></i>
                                    <span class="fw-semibold">No expenses found for this period.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>

        @if($expenses->hasPages())
            <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">

                <small class="text-muted bn-digit font-monospace" style="font-size: 11px;">
                    Showing {{ $expenses->firstItem() }} to {{ $expenses->lastItem() }} of {{ $expenses->total() }} entries
                </small>

                <div>
                    {{ $expenses->links('pagination::bootstrap-5') }}
                </div>

            </div>
        @endif

    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade exp-modal-scope" id="addExpenseModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-2 mb-0">
                    <i class="fas fa-receipt text-danger"></i>
                    <span>Add Expense</span>
                </h6>

                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ url('/admin/accounts/expenses/store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="modal-body p-3">
                    <div class="row g-3">

                        <!-- Title -->
                        <div class="col-12">
                            <label class="form-label">
                                Expense Title <span class="text-danger">*</span>
                            </label>

                            <input type="text" name="title" class="form-control" placeholder="Facebook Ads, Carton Box Purchase" required>
                        </div>

                        <!-- Category -->
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

                        <!-- Amount -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Amount (৳) <span class="text-danger">*</span>
                            </label>

                            <input type="number" name="amount" class="form-control font-monospace bn-digit text-end fw-bold text-danger" placeholder="0.00" min="0.01" step="any" required>
                        </div>

                        <!-- Expense Date -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Expense Date <span class="text-danger">*</span>
                            </label>

                            <input type="date" name="expense_date" class="form-control font-monospace bn-digit" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <!-- Account -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Deduct From <span class="text-danger">*</span>
                            </label>

                            <select name="bank_account_id" id="expenseBankAccountId" class="form-select font-monospace" onchange="handleExpenseAccountChange(this)" required>
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

                        <!-- Payment Method -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Payment Method</label>

                            <select name="payment_method" id="expensePaymentMethod" class="form-select font-monospace">
                                <option value="Cash" selected>Cash Handover</option>
                                <option value="bKash">bKash</option>
                                <option value="Nagad">Nagad</option>
                                <option value="Rocket">Rocket</option>
                                <option value="Bank Transfer">Bank Account</option>
                                <option value="Cheque">Bank Cheque</option>
                            </select>
                        </div>

                        <!-- Reference -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Voucher / Memo No.</label>

                            <input type="text" name="reference_no" class="form-control font-monospace" placeholder="VCH-0012">
                        </div>

                        <!-- Receipt -->
                        <div class="col-12">
                            <label class="form-label">Receipt (Image / PDF)</label>

                            <input type="file" name="receipt" class="form-control" accept="image/*,application/pdf">
                        </div>

                        <!-- Notes -->
                        <div class="col-12">
                            <label class="form-label">Notes</label>

                            <textarea name="note" rows="2" class="form-control" placeholder="Additional details..."></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-danger btn-compact shadow-xs fw-bold px-3">
                        <i class="fas fa-check-circle"></i>
                        Save Expense
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade exp-modal-scope" id="addCategoryModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold mb-0">
                    Add Category
                </h6>

                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ url('/admin/accounts/expenses/category/store') }}" method="POST">
                @csrf

                <div class="modal-body p-3">
                    <label class="form-label">Category Name *</label>

                    <input type="text" name="name" class="form-control" placeholder="Office Stationery" required>
                </div>

                <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-dark btn-compact shadow-xs fw-bold px-3">
                        Save Category
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
function toggleCustomExpenseDates(val) {
    const s = document.getElementById('expenseStartCol');
    const e = document.getElementById('expenseEndCol');

    if (val === 'custom') {
        s.classList.remove('d-none');
        e.classList.remove('d-none');
    } else {
        s.classList.add('d-none');
        e.classList.add('d-none');
    }
}

function handleExpenseAccountChange(select) {
    const selectedOption = select.options[select.selectedIndex];
    const type = selectedOption ? selectedOption.getAttribute('data-type') : null;
    const methodSelect = document.getElementById('expensePaymentMethod');

    if (methodSelect && type) {
        if (type === 'cash') methodSelect.value = 'Cash';
        else if (type === 'mobile_banking') methodSelect.value = 'bKash';
        else if (type === 'bank') methodSelect.value = 'Bank Transfer';
    }
}

function deleteExpense(id) {
    if (!confirm('Are you sure you want to permanently delete this expense record? Account balance will be refunded.')) {
        return;
    }

    const url = "{{ url('/admin/accounts/expenses/delete') }}/" + id;

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
            const row = document.getElementById('expense-row-' + id);
            if (row) row.remove();
        } else {
            alert('Failed to delete expense.');
        }
    })
    .catch(err => console.error(err));
}
</script>
@endsection