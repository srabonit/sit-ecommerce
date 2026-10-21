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
    /* Strict 5px Element Radius Across All Controls */
    #expenses-root .btn,
    #expenses-root .btn-sm,
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
    .expense-card {
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
    .kpi-total::before   { background-color: #ef4444; }
    .kpi-records::before { background-color: #0284c7; }
    .kpi-top-cat::before { background-color: #f59e0b; }

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .expense-table {
        width: 100%;
        margin-bottom: 0;
    }
    .expense-table thead th {
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
    .expense-table tbody td {
        padding: 8px 6px;
        vertical-align: middle;
        font-size: 11.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .expense-table tbody tr:hover {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .expense-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .expense-table th,
        .expense-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 9 Columns Proportional Distribution Summing to Exactly 100% */
        .col-exp-id      { width: 5% !important; }
        .col-exp-date    { width: 10% !important; }
        .col-exp-cat     { width: 12% !important; }
        .col-exp-title   { width: 23% !important; }
        .col-exp-account { width: 14% !important; }
        .col-exp-ref     { width: 12% !important; }
        .col-exp-amount  { width: 12% !important; }
        .col-exp-receipt { width: 6% !important; }
        .col-exp-action  { width: 6% !important; }
    }

    /* Form Controls */
    .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #expenses-root .form-control,
    #expenses-root .form-select,
    .exp-modal-scope .form-control,
    .exp-modal-scope .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease;
    }
    #expenses-root .form-control:focus,
    #expenses-root .form-select:focus,
    .exp-modal-scope .form-control:focus,
    .exp-modal-scope .form-select:focus {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12);
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
        background-color: #ef4444 !important;
        border-color: #ef4444 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
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

    /* Print Styles */
    @media print {
        @page { size: A4 landscape; margin: 8mm; }
        body { background: #ffffff !important; color: #000000 !important; }
        #expenses-root .no-print, .admin-dark-sidebar, .admin-header, .main-footer { display: none !important; }
        #expenses-root { padding: 0 !important; margin: 0 !important; width: 100% !important; }
        .expense-card { border: 1px solid #000000 !important; box-shadow: none !important; margin-bottom: 10px !important; }
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
        .expense-table {
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

<div class="container-fluid py-3 px-2 px-md-4" id="expenses-root">

    <!-- 1. Header (Original Clean Light Theme) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 header-top-wrap no-print">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-money-bill-wave text-danger"></i>
                <span>Operational Expenses &amp; Overhead Ledger</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Track Facebook ads, packaging, rent, salaries, utilities and miscellaneous business overheads
            </small>
        </div>
        <div class="top-action-btns d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-dark btn-compact shadow-xs" onclick="window.print()">
                <i class="fas fa-print me-1"></i> <span>Print A4</span>
            </button>
            <button type="button" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold" data-bs-toggle="modal" data-bs-target="#addExpenseModal" style="background-color: #ef4444; border-color: #ef4444;">
                <i class="fas fa-plus-circle me-1"></i> <span>Add New Expense</span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-dark btn-compact shadow-xs" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="fas fa-tags me-1"></i> <span>Add Category</span>
            </button>
            <a href="{{ url('/admin/accounts/expenses/export-csv') }}" class="btn btn-sm btn-outline-success btn-compact shadow-xs">
                <i class="fas fa-file-csv me-1"></i> <span>Export CSV</span>
            </a>
            <a href="{{ route('admin.report.profit_loss') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-chart-line me-1"></i> <span>P&amp;L Statement</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small border-0 shadow-sm d-flex align-items-center justify-content-between mb-3 no-print" role="alert" style="border-radius: 5px;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close shadow-none py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-sm d-flex align-items-center justify-content-between mb-3 no-print" role="alert" style="border-radius: 5px;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-triangle-exclamation text-danger fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close shadow-none py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- 2. KPI Metric Summary Cards (Original Clean Structure) -->
    <div class="row g-2 mb-3">
        <div class="col-12 col-md-4">
            <div class="kpi-stat-card kpi-total shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Total Incurred Expenses</span>
                <h5 class="fw-bold text-danger mb-0 font-monospace bn-digit">৳ {{ number_format($totalExpense, 2) }}</h5>
                <small class="text-muted font-monospace" style="font-size: 10px;">{{ $start->format('d M Y') }} — {{ $end->format('d M Y') }}</small>
            </div>
        </div>

        <div class="col-6 col-md-4">
            <div class="kpi-stat-card kpi-records shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Recorded Vouchers</span>
                <h5 class="fw-bold text-primary mb-0 font-monospace bn-digit">{{ $totalRecords }} Entries</h5>
                <small class="text-muted" style="font-size: 10px;">Audited business transactions</small>
            </div>
        </div>

        <div class="col-6 col-md-4">
            <div class="kpi-stat-card kpi-top-cat shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Primary Expense Driver</span>
                <h5 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 15px;" title="{{ $topCat->cat_name ?? 'N/A' }}">{{ $topCat->cat_name ?? 'N/A' }}</h5>
                <small class="text-warning fw-bold font-monospace bn-digit" style="font-size: 11px;">
                    ৳ {{ number_format((float)($topCat->cat_total ?? 0), 2) }}
                </small>
            </div>
        </div>
    </div>

    <!-- 3. Filter Bar (Clean & Symmetric) -->
    <div class="card border-0 shadow-sm expense-card mb-3 p-3 no-print">
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
                    <option value="custom" {{ $dateFilter === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                </select>
            </div>

            <div class="col-6 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="expenseStartCol">
                <label class="form-label">Start</label>
                <input type="date" name="start_date" class="form-control font-monospace bn-digit" value="{{ request('start_date', $start->format('Y-m-d')) }}">
            </div>

            <div class="col-6 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="expenseEndCol">
                <label class="form-label">End</label>
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

            <div class="col-12 col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-dark btn-compact flex-grow-1 shadow-xs fw-bold">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if(request()->anyFilled(['date_filter', 'category_id', 'payment_method', 'start_date', 'end_date']))
                    <a href="{{ url('/admin/accounts/expenses') }}" class="btn btn-light border btn-compact shadow-xs">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- 4. Expense Ledger Data Table Card -->
    <div class="card border-0 shadow-sm expense-card overflow-hidden">
        <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                <i class="fas fa-receipt text-danger"></i> <span>Expense Journal Entries</span>
            </h6>
            <span class="badge bg-light text-secondary border font-monospace px-2 py-0.5" style="font-size: 10.5px;">
                Showing {{ $expenses->firstItem() ?? 0 }} - {{ $expenses->lastItem() ?? 0 }} of {{ $expenses->total() }} entries
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
                            <th class="col-exp-title">Expense Title &amp; Details</th>
                            <th class="col-exp-account">Paid From / Account</th>
                            <th class="col-exp-ref">Reference No</th>
                            <th class="text-end col-exp-amount">Amount (৳)</th>
                            <th class="text-center col-exp-receipt no-print">Receipt</th>
                            <th class="text-end pe-3 col-exp-action no-print">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $idx => $exp)
                            <tr id="expense-row-{{ $exp->id }}">
                                <td class="ps-3 col-exp-id text-center font-monospace bn-digit text-muted" style="font-size: 10.5px;">
                                    {{ $expenses->firstItem() + $idx }}
                                </td>
                                <td class="col-exp-date font-monospace bn-digit text-muted" style="font-size: 10.5px;">
                                    {{ date('d M Y', strtotime($exp->expense_date)) }}
                                </td>
                                <td class="col-exp-cat">
                                    <span class="badge bg-light text-dark border font-monospace text-truncate d-inline-block px-1.5 py-0" style="font-size: 9.5px; max-width: 100%;">
                                        {{ $exp->category_name ?? 'General' }}
                                    </span>
                                </td>
                                <td class="col-exp-title">
                                    <div class="min-w-0">
                                        <strong class="text-dark d-block text-truncate" style="font-size: 12px; line-height: 1.3;" title="{{ $exp->title }}">
                                            {{ $exp->title }}
                                        </strong>
                                        @if(!empty($exp->note))
                                            <small class="text-muted d-block text-truncate font-monospace" style="font-size: 9.5px;" title="{{ $exp->note }}">
                                                {{ $exp->note }}
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td class="col-exp-account">
                                    <span class="badge bg-light text-primary border font-monospace px-1.5 py-0 text-truncate d-inline-block" style="font-size: 9.5px; max-width: 100%;" title="{{ $exp->bank_account_name ?? $exp->payment_method }}">
                                        {{ $exp->bank_account_name ?? $exp->payment_method }}
                                    </span>
                                </td>
                                <td class="col-exp-ref font-monospace bn-digit text-muted text-truncate" style="font-size: 10.5px;">
                                    {{ $exp->reference_no ?? '—' }}
                                </td>
                                <td class="text-end col-exp-amount font-monospace bn-digit fw-bold text-danger" style="font-size: 12px;">
                                    ৳ {{ number_format((float)$exp->amount, 2) }}
                                </td>
                                <td class="text-center col-exp-receipt no-print">
                                    @if(!empty($exp->voucher_receipt))
                                        <a href="{{ asset($exp->voucher_receipt) }}" target="_blank" class="btn btn-light border btn-compact py-0 px-1 text-secondary shadow-xs" style="height: 22px; font-size: 10px !important;" title="View Voucher / Receipt">
                                            <i class="fas fa-file-invoice"></i>
                                        </a>
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 10px;">—</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3 col-exp-action no-print">
                                    <button type="button" class="btn btn-light border text-danger p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 25px; height: 25px; font-size: 10px;" onclick="deleteExpense({{ $exp->id }})" title="Delete Record">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted small">
                                    <i class="fas fa-money-bill-transfer fa-2x mb-2 d-block opacity-25"></i>
                                    <span class="fw-semibold">No operational expenses recorded for this timeframe.</span>
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

<!-- 5. Modal: Add New Expense -->
<div class="modal fade exp-modal-scope" id="addExpenseModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-1 mb-0" style="font-size: 13.5px;">
                    <i class="fas fa-receipt text-danger"></i> <span>Log Operational Expense</span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ url('/admin/accounts/expenses/store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-3">
                    <div class="row g-2">
                        <!-- Title -->
                        <div class="col-12">
                            <label class="form-label">
                                Expense Description / Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Facebook Ads Campaign #4, Carton Box Purchase" required>
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

                        <!-- Deduct From Account -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Deduct From Account <span class="text-danger">*</span>
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

                        <!-- Payment Mode -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Payment Mode / Method</label>
                            <select name="payment_method" id="expensePaymentMethod" class="form-select font-monospace">
                                <option value="Cash" selected>Cash Handover</option>
                                <option value="bKash">bKash</option>
                                <option value="Nagad">Nagad</option>
                                <option value="Rocket">Rocket</option>
                                <option value="Bank Transfer">Bank Account</option>
                                <option value="Cheque">Bank Cheque</option>
                            </select>
                        </div>

                        <!-- Voucher / Memo # -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Voucher / Memo #</label>
                            <input type="text" name="reference_no" class="form-control font-monospace" placeholder="e.g. VCH-0012">
                        </div>

                        <!-- Receipt Attachment -->
                        <div class="col-12">
                            <label class="form-label">Attach Receipt (Image/PDF)</label>
                            <input type="file" name="receipt" class="form-control" accept="image/*,application/pdf">
                        </div>

                        <!-- Note -->
                        <div class="col-12">
                            <label class="form-label">Notes / Remarks</label>
                            <textarea name="note" rows="2" class="form-control" placeholder="Additional details..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-compact shadow-xs fw-bold px-3">
                        <i class="fas fa-check-circle me-1"></i> Save Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 6. Modal: Add Expense Category -->
<div class="modal fade exp-modal-scope" id="addCategoryModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold" style="font-size: 13.5px;">Add Expense Category</h6>
                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ url('/admin/accounts/expenses/category/store') }}" method="POST">
                @csrf
                <div class="modal-body p-3">
                    <label class="form-label">Category Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Office Stationery" required>
                </div>
                <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark btn-compact shadow-xs fw-bold px-3">Save Category</button>
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