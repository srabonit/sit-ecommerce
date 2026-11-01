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
    /* Strict 5px Element Radius Across All Controls */
    #income-root .btn,
    #income-root .btn-sm,
    #income-root button,
    #income-root .badge,
    #income-root .alert,
    #income-root .form-control,
    #income-root .form-select,
    #income-root .page-link,
    #income-root .page-item span,
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
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #income-root .input-group > .form-control,
    #income-root .input-group > .form-select {
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
    .income-card {
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
    .kpi-total::before   { background-color: #10b981; }
    .kpi-records::before { background-color: #0284c7; }
    .kpi-top-cat::before { background-color: #f59e0b; }

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .income-table {
        width: 100%;
        margin-bottom: 0;
    }
    .income-table thead th {
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
    .income-table tbody td {
        padding: 8px 6px;
        vertical-align: middle;
        font-size: 11.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .income-table tbody tr:hover {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .income-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .income-table th,
        .income-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 9 Columns Proportional Distribution Summing to Exactly 100% */
        .col-inc-id      { width: 5% !important; }
        .col-inc-date    { width: 10% !important; }
        .col-inc-cat     { width: 12% !important; }
        .col-inc-title   { width: 23% !important; }
        .col-inc-account { width: 14% !important; }
        .col-inc-ref     { width: 12% !important; }
        .col-inc-amount  { width: 12% !important; }
        .col-inc-receipt { width: 6% !important; }
        .col-inc-action  { width: 6% !important; }
    }

    /* Receipt Preview Box */
    .proof-thumb-box {
        width: 30px;
        height: 30px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        position: relative;
    }
    .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Form Controls */
    .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #income-root .form-control,
    #income-root .form-select,
    .inc-modal-scope .form-control,
    .inc-modal-scope .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease;
    }
    #income-root .form-control:focus,
    #income-root .form-select:focus,
    .inc-modal-scope .form-control:focus,
    .inc-modal-scope .form-select:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.12);
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
        background-color: #10b981 !important;
        border-color: #10b981 !important;
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
        #income-root .no-print, .admin-dark-sidebar, .admin-header, .main-footer { display: none !important; }
        #income-root { padding: 0 !important; margin: 0 !important; width: 100% !important; }
        .income-card { border: 1px solid #000000 !important; box-shadow: none !important; margin-bottom: 10px !important; }
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
        .income-table {
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

<div class="container-fluid py-3 px-2 px-md-4" id="income-root">

    <!-- 1. Header (Original Clean Light Theme) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 header-top-wrap no-print">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-hand-holding-dollar text-success"></i>
                <span>Business Inflows &amp; Other Income Ledger</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Track non-order cash inflows, commissions, scrap packaging sales, interest and miscellaneous receipts
            </small>
        </div>
        <div class="top-action-btns d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-dark btn-compact shadow-xs" onclick="window.print()">
                <i class="fas fa-print me-1"></i> <span>Print A4</span>
            </button>
            <button type="button" class="btn btn-sm btn-success btn-compact shadow-xs fw-bold" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
                <i class="fas fa-plus-circle me-1"></i> <span>Add New Income</span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-dark btn-compact shadow-xs" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="fas fa-tags me-1"></i> <span>Add Category</span>
            </button>
            <a href="{{ url('/admin/accounts/income/export-csv') }}" class="btn btn-sm btn-outline-success btn-compact shadow-xs">
                <i class="fas fa-file-csv me-1"></i> <span>Export CSV</span>
            </a>
            <a href="{{ route('admin.report.profit_loss') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-chart-line me-1 text-primary"></i> <span>P&amp;L Statement</span>
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
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Total Inflows Recorded</span>
                <h5 class="fw-bold text-success mb-0 font-monospace bn-digit">৳ {{ number_format($totalIncome, 2) }}</h5>
                <small class="text-muted font-monospace" style="font-size: 10px;">{{ $start->format('d M Y') }} — {{ $end->format('d M Y') }}</small>
            </div>
        </div>

        <div class="col-6 col-md-4">
            <div class="kpi-stat-card kpi-records shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Recorded Receipts</span>
                <h5 class="fw-bold text-primary mb-0 font-monospace bn-digit">{{ $totalRecords }} Entries</h5>
                <small class="text-muted" style="font-size: 10px;">Credited business transactions</small>
            </div>
        </div>

        <div class="col-6 col-md-4">
            <div class="kpi-stat-card kpi-top-cat shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Primary Revenue Stream</span>
                <h5 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 15px;" title="{{ $topCat->cat_name ?? 'N/A' }}">{{ $topCat->cat_name ?? 'N/A' }}</h5>
                <small class="text-success fw-bold font-monospace bn-digit" style="font-size: 11px;">
                    ৳ {{ number_format((float)($topCat->cat_total ?? 0), 2) }}
                </small>
            </div>
        </div>
    </div>

    <!-- 3. Filter Bar (Clean & Symmetric) -->
    <div class="card border-0 shadow-sm income-card mb-3 p-3 no-print">
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
                    <option value="custom" {{ $dateFilter === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                </select>
            </div>

            <div class="col-6 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="incomeStartCol">
                <label class="form-label">Start</label>
                <input type="date" name="start_date" class="form-control font-monospace bn-digit" value="{{ request('start_date', $start->format('Y-m-d')) }}">
            </div>

            <div class="col-6 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="incomeEndCol">
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
                    <a href="{{ url('/admin/accounts/income') }}" class="btn btn-light border btn-compact shadow-xs">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- 4. Income Ledger Data Table Card -->
    <div class="card border-0 shadow-sm income-card overflow-hidden">
        <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                <i class="fas fa-receipt text-success"></i> <span>Income Journal Entries</span>
            </h6>
            <span class="badge bg-light text-secondary border font-monospace px-2 py-0.5" style="font-size: 10.5px;">
                Showing {{ $incomes->firstItem() ?? 0 }} - {{ $incomes->lastItem() ?? 0 }} of {{ $incomes->total() }} entries
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
                            <th class="col-inc-title">Income Title &amp; Details</th>
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
                                <td class="ps-3 col-inc-id text-center font-monospace bn-digit text-muted" style="font-size: 10.5px;">
                                    {{ $incomes->firstItem() + $idx }}
                                </td>
                                <td class="col-inc-date font-monospace bn-digit text-muted" style="font-size: 10.5px;">
                                    {{ date('d M Y', strtotime($inc->income_date)) }}
                                </td>
                                <td class="col-inc-cat">
                                    <span class="badge bg-light text-dark border font-monospace text-truncate d-inline-block px-1.5 py-0" style="font-size: 9.5px; max-width: 100%;">
                                        {{ $inc->category_name ?? 'General' }}
                                    </span>
                                </td>
                                <td class="col-inc-title">
                                    <div class="min-w-0">
                                        <strong class="text-dark d-block text-truncate" style="font-size: 12px; line-height: 1.3;" title="{{ $inc->title }}">
                                            {{ $inc->title }}
                                        </strong>
                                        @if(!empty($inc->note))
                                            <small class="text-muted d-block text-truncate font-monospace" style="font-size: 9.5px;" title="{{ $inc->note }}">
                                                {{ $inc->note }}
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td class="col-inc-account">
                                    <span class="badge bg-light text-success border font-monospace px-1.5 py-0 text-truncate d-inline-block" style="font-size: 9.5px; max-width: 100%;" title="{{ $inc->bank_account_name ?? $inc->payment_method }}">
                                        {{ $inc->bank_account_name ?? $inc->payment_method }}
                                    </span>
                                </td>
                                <td class="col-inc-ref font-monospace bn-digit text-muted text-truncate" style="font-size: 10.5px;">
                                    {{ $inc->reference_no ?? '—' }}
                                </td>
                                <td class="text-end col-inc-amount font-monospace bn-digit fw-bold text-success" style="font-size: 12px;">
                                    +৳ {{ number_format((float)$inc->amount, 2) }}
                                </td>
                                <td class="text-center col-inc-receipt no-print">
                                    @if(!empty($inc->voucher_receipt))
                                        @php
                                            $isPdf = str_ends_with(strtolower($inc->voucher_receipt), '.pdf');
                                        @endphp
                                        @if(!$isPdf)
                                            <div class="proof-thumb-box shadow-xs mx-auto" onclick="zoomIncomeSlip('{{ asset($inc->voucher_receipt) }}', 'Income #{{ $inc->id }} Receipt')" title="Click to Zoom Receipt">
                                                <img src="{{ asset($inc->voucher_receipt) }}" alt="Receipt" onerror="this.src='https://placehold.co/80x80?text=Slip';">
                                            </div>
                                        @else
                                            <a href="{{ asset($inc->voucher_receipt) }}" target="_blank" class="btn btn-light border btn-compact py-0 px-1 text-secondary shadow-xs" style="height: 22px; font-size: 10px !important;" title="View Document / Memo">
                                                <i class="fas fa-file-invoice"></i>
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 10px;">—</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3 col-inc-action no-print">
                                    <button type="button" class="btn btn-light border text-danger p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 25px; height: 25px; font-size: 10px;" onclick="triggerDeleteIncomeModal({{ $inc->id }}, '{{ addslashes($inc->title) }}')" title="Delete Record">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted small">
                                    <i class="fas fa-hand-holding-dollar fa-2x mb-2 d-block opacity-25"></i>
                                    <span class="fw-semibold">No business inflows recorded for this timeframe.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($incomes->hasPages())
            <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">
                <small class="text-muted bn-digit font-monospace" style="font-size: 11px;">
                    Showing {{ $incomes->firstItem() }} to {{ $incomes->lastItem() }} of {{ $incomes->total() }} entries
                </small>
                <div>
                    {{ $incomes->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- 5. Modal: Add New Income -->
<div class="modal fade inc-modal-scope" id="addIncomeModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-1 mb-0" style="font-size: 13.5px;">
                    <i class="fas fa-plus-circle text-success"></i> <span>Record Business Inflow / Income</span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ url('/admin/accounts/income/store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-3">
                    <div class="row g-2">
                        <!-- Title -->
                        <div class="col-12">
                            <label class="form-label">
                                Income Title / Source <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="title" class="form-control" placeholder="Scrap carton box sale, Affiliate commission" required>
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
                            <input type="number" name="amount" class="form-control font-monospace bn-digit text-end fw-bold text-success" placeholder="0.00" min="0.01" step="any" required>
                        </div>

                        <!-- Income Date -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="income_date" class="form-control font-monospace bn-digit" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <!-- Deposit To Account -->
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

                        <!-- Payment Mode / Method -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Payment Mode / Method</label>
                            <select name="payment_method" id="incomePaymentMethod" class="form-select font-monospace">
                                <option value="Cash" selected>Cash Handover</option>
                                <option value="bKash">bKash</option>
                                <option value="Nagad">Nagad</option>
                                <option value="Rocket">Rocket</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cheque">Bank Cheque</option>
                            </select>
                        </div>

                        <!-- Reference / Receipt # -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Reference / Receipt #</label>
                            <input type="text" name="reference_no" class="form-control font-monospace" placeholder="INC-0012">
                        </div>

                        <!-- Receipt Attachment -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Attach Document / Slip (Image/PDF)</label>
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
                    <button type="submit" class="btn btn-success btn-compact shadow-xs fw-bold px-3">
                        <i class="fas fa-check-circle me-1"></i> Save Income
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 6. Modal: Add Income Category -->
<div class="modal fade inc-modal-scope" id="addCategoryModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold" style="font-size: 13.5px;">Add Income Category</h6>
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
                    <button type="submit" class="btn btn-dark btn-compact shadow-xs fw-bold px-3">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 7. Modal: Custom Delete Confirmation Modal (Replaces browser confirm) -->
<div class="modal fade inc-modal-scope" id="deleteIncomeConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2 text-danger">
                    <i class="fas fa-trash-can fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Delete Income Record?</h6>
                <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">
                    Are you sure you want to remove <strong id="deleteIncomeTitle" class="text-dark">this record</strong>? The credited amount will be deducted from the account balance.
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-compact shadow-xs fw-bold" id="btnExecuteIncomeDelete">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 8. Modal: Lightbox Zoom Preview Modal -->
<div class="modal fade inc-modal-scope" id="incomeReceiptZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;" id="incomeReceiptZoomTitle">
                    <i class="fas fa-image text-success me-1"></i> Receipt Slip Preview
                </span>
                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="incomeReceiptZoomImgTag" src="" alt="Receipt Memo" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<!-- 9. Modal: Custom Enterprise Notice Modal (Replaces browser alert) -->
<div class="modal fade inc-modal-scope" id="incomeNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2" id="incomeNoticeIconWrap">
                    <i class="fas fa-circle-check text-success fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="incomeNoticeTitle" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="incomeNoticeMessage" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-primary btn-compact px-4" data-bs-dismiss="modal" style="background-color: #10b981; border-color: #10b981;">Acknowledge</button>
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