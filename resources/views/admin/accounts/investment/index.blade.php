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
    /* Strict 5px Element Radius Across All Controls */
    #investment-root .btn,
    #investment-root .btn-sm,
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
    .investment-card {
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
    .kpi-capital::before   { background-color: #6366f1; }
    .kpi-period::before    { background-color: #0284c7; }
    .kpi-records::before   { background-color: #10b981; }
    .kpi-investors::before { background-color: #f59e0b; }

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .investment-table {
        width: 100%;
        margin-bottom: 0;
    }
    .investment-table thead th {
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
    .investment-table tbody td {
        padding: 8px 6px;
        vertical-align: middle;
        font-size: 11.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .investment-table tbody tr:hover {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .investment-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .investment-table th,
        .investment-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 9 Columns Proportional Distribution Summing to Exactly 100% */
        .col-inv-id      { width: 5% !important; }
        .col-inv-date    { width: 10% !important; }
        .col-inv-name    { width: 23% !important; }
        .col-inv-account { width: 15% !important; }
        .col-inv-method  { width: 11% !important; }
        .col-inv-ref     { width: 12% !important; }
        .col-inv-amount  { width: 12% !important; }
        .col-inv-receipt { width: 6% !important; }
        .col-inv-action  { width: 6% !important; }
    }

    /* Global Media Chooser Component Styles */
    .proof-media-surface {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        padding: 10px;
        transition: all 0.2s ease;
    }
    .proof-media-surface:hover {
        border-color: #6366f1;
        background: #eef2ff;
    }
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
    .proof-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.15s ease;
    }
    .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    /* Form Controls */
    .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #investment-root .form-control,
    #investment-root .form-select,
    .inv-modal-scope .form-control,
    .inv-modal-scope .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease;
    }
    #investment-root .form-control:focus,
    #investment-root .form-select:focus,
    .inv-modal-scope .form-control:focus,
    .inv-modal-scope .form-select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
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
        background-color: #6366f1 !important;
        border-color: #6366f1 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 3px rgba(99, 102, 241, 0.25) !important;
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
        #investment-root .no-print, .admin-dark-sidebar, .admin-header, .main-footer { display: none !important; }
        #investment-root { padding: 0 !important; margin: 0 !important; width: 100% !important; }
        .investment-card { border: 1px solid #000000 !important; box-shadow: none !important; margin-bottom: 10px !important; }
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
        .investment-table {
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

<div class="container-fluid py-3 px-2 px-md-4" id="investment-root">

    <!-- 1. Header (Original Clean Light Theme) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 header-top-wrap no-print">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-chart-line" style="color: #6366f1;"></i>
                <span>Capital &amp; Owner Investment Ledger</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Track founder equity, investor injections, partnership capital, and reserve balances
            </small>
        </div>
        <div class="top-action-btns d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-dark btn-compact shadow-xs" onclick="window.print()">
                <i class="fas fa-print me-1"></i> <span>Print A4</span>
            </button>
            <button type="button" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold" data-bs-toggle="modal" data-bs-target="#addInvestmentModal" style="background-color: #6366f1; border-color: #6366f1;">
                <i class="fas fa-plus-circle me-1"></i> <span>Add New Investment</span>
            </button>
            <a href="{{ url('/admin/accounts/investment/export-csv') }}" class="btn btn-sm btn-outline-success btn-compact shadow-xs">
                <i class="fas fa-file-csv me-1"></i> <span>Export CSV</span>
            </a>
            <a href="{{ url('/admin/accounts/bank-balance') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-landmark me-1"></i> <span>Bank Balances</span>
            </a>
            <a href="{{ route('admin.report.balance_sheet') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-scale-balanced me-1"></i> <span>Balance Sheet</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-xs py-2 px-3 mb-3 small d-flex align-items-center justify-content-between no-print" style="border-radius: 5px;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-xs py-2 px-3 mb-3 small d-flex align-items-center justify-content-between no-print" style="border-radius: 5px;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-triangle-exclamation text-danger fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- 2. KPI Metric Summary Cards (Original Clean Theme) -->
    <div class="row g-2 mb-3">
        <!-- Cumulative All-Time Capital -->
        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-capital shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Total Invested Capital</span>
                <h5 class="fw-bold mb-0 font-monospace bn-digit" style="color: #6366f1;">৳ {{ number_format($allTimeCapital, 2) }}</h5>
                <small class="text-muted" style="font-size: 10px;">Lifetime invested funds</small>
            </div>
        </div>

        <!-- Period Inflow -->
        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-period shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Period Injections</span>
                <h5 class="fw-bold text-primary mb-0 font-monospace bn-digit">৳ {{ number_format($totalInvestment, 2) }}</h5>
                <small class="text-muted font-monospace" style="font-size: 10px;">{{ $start->format('d M Y') }} — {{ $end->format('d M Y') }}</small>
            </div>
        </div>

        <!-- Recorded Vouchers -->
        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-records shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Injection Entries</span>
                <h5 class="fw-bold text-success mb-0 font-monospace bn-digit">{{ $totalRecords }} Records</h5>
                <small class="text-muted" style="font-size: 10px;">Audited capital deposits</small>
            </div>
        </div>

        <!-- Distinct Investors -->
        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-investors shadow-xs">
                <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Active Investors</span>
                <h5 class="fw-bold text-warning mb-0 font-monospace bn-digit">{{ $uniqueInvestors }} Entities</h5>
                <small class="text-muted" style="font-size: 10px;">Equity &amp; angel partners</small>
            </div>
        </div>
    </div>

    <!-- 3. Filter Bar (Clean & Symmetric) -->
    <div class="card border-0 shadow-sm investment-card mb-3 p-3 no-print">
        <form action="{{ url('/admin/accounts/investment') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label">Reporting Period</label>
                <select name="date_filter" id="investmentDateFilter" class="form-select font-monospace" onchange="toggleCustomInvestmentDates(this.value)">
                    <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ $dateFilter === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="this_week" {{ $dateFilter === 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ $dateFilter === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $dateFilter === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="this_year" {{ $dateFilter === 'this_year' ? 'selected' : '' }}>This Year</option>
                    <option value="custom" {{ $dateFilter === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                </select>
            </div>

            <div class="col-6 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="investmentStartCol">
                <label class="form-label">Start</label>
                <input type="date" name="start_date" class="form-control font-monospace bn-digit" value="{{ request('start_date', $start->format('Y-m-d')) }}">
            </div>

            <div class="col-6 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="investmentEndCol">
                <label class="form-label">End</label>
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
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Investor name, reference...">
            </div>

            <div class="col-12 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-dark btn-compact flex-grow-1 shadow-xs fw-bold">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if(request()->anyFilled(['date_filter', 'payment_method', 'search', 'start_date', 'end_date']))
                    <a href="{{ url('/admin/accounts/investment') }}" class="btn btn-light border btn-compact shadow-xs">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- 4. Investment Ledger Table Card -->
    <div class="card border-0 shadow-sm investment-card overflow-hidden">
        <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                <i class="fas fa-money-check-dollar" style="color: #6366f1;"></i> <span>Capital Investment Journal</span>
            </h6>
            <span class="badge bg-light text-secondary border font-monospace px-2 py-0.5" style="font-size: 10.5px;">
                Showing {{ $investments->firstItem() ?? 0 }} - {{ $investments->lastItem() ?? 0 }} of {{ $investments->total() }} entries
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive pc-fit-container">
                <table class="table align-middle investment-table mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3 col-inv-id text-center">#</th>
                            <th class="col-inv-date">Date</th>
                            <th class="col-inv-name">Investor Name / Capital Source</th>
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
                                <td class="ps-3 col-inv-id text-center font-monospace bn-digit text-muted" style="font-size: 10.5px;">
                                    {{ $investments->firstItem() + $idx }}
                                </td>
                                <td class="col-inv-date font-monospace bn-digit text-muted" style="font-size: 10.5px;">
                                    {{ date('d M Y', strtotime($inv->investment_date)) }}
                                </td>
                                <td class="col-inv-name">
                                    <div class="min-w-0">
                                        <strong class="text-dark d-block text-truncate" style="font-size: 12px; line-height: 1.3;" title="{{ $inv->investor_name }}">
                                            {{ $inv->investor_name }}
                                        </strong>
                                        @if(!empty($inv->note))
                                            <small class="text-muted d-block text-truncate font-monospace" style="font-size: 9.5px;" title="{{ $inv->note }}">
                                                {{ $inv->note }}
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td class="col-inv-account">
                                    <span class="badge bg-light text-primary border font-monospace px-1.5 py-0 text-truncate d-inline-block" style="font-size: 9.5px; max-width: 100%;">
                                        {{ $inv->bank_account_name ?? 'Physical Vault' }}
                                    </span>
                                </td>
                                <td class="col-inv-method">
                                    <span class="text-dark small" style="font-size: 11px;">{{ $inv->payment_method }}</span>
                                </td>
                                <td class="col-inv-ref font-monospace bn-digit text-muted text-truncate" style="font-size: 10.5px;">
                                    {{ $inv->reference_no ?? '—' }}
                                </td>
                                <td class="text-end col-inv-amount font-monospace bn-digit fw-bold text-success" style="font-size: 12px;">
                                    +৳ {{ number_format((float)$inv->amount, 2) }}
                                </td>
                                <td class="text-center col-inv-receipt no-print">
                                    @if(!empty($inv->voucher_receipt))
                                        @php
                                            $slipUrl = $resolveInvestmentReceipt($inv->voucher_receipt);
                                            $isPdf = str_ends_with(strtolower($inv->voucher_receipt), '.pdf');
                                        @endphp
                                        @if(!$isPdf)
                                            <div class="proof-thumb-box shadow-xs mx-auto" onclick="zoomInvestmentSlip('{{ $slipUrl }}', 'Investment #{{ $inv->id }} Slip')" title="Click to Zoom Slip">
                                                <img src="{{ $slipUrl }}" alt="Slip" onerror="this.src='https://placehold.co/80x80?text=Slip';">
                                                <div class="proof-thumb-hover-overlay">
                                                    <i class="fas fa-search-plus text-white" style="font-size: 8px;"></i>
                                                </div>
                                            </div>
                                        @else
                                            <a href="{{ $slipUrl }}" target="_blank" class="btn btn-light border btn-compact py-0 px-1 text-danger shadow-xs" style="height: 22px; font-size: 10px !important;" title="View PDF Slip">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 10px;">—</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3 col-inv-action no-print">
                                    <button type="button" class="btn btn-light border text-danger p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 25px; height: 25px; font-size: 10px;" onclick="triggerDeleteInvestmentModal({{ $inv->id }}, '{{ addslashes($inv->investor_name) }}')" title="Delete Investment Entry">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted small">
                                    <i class="fas fa-chart-line fa-2x mb-2 d-block opacity-25"></i>
                                    <span class="fw-semibold">No capital investment records found for this period.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($investments->hasPages())
            <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">
                <small class="text-muted bn-digit font-monospace" style="font-size: 11px;">
                    Showing {{ $investments->firstItem() }} to {{ $investments->lastItem() }} of {{ $investments->total() }} entries
                </small>
                <div>
                    {{ $investments->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modal 1: Add New Capital Investment (With Global Media Chooser Support) -->
<div class="modal fade inv-modal-scope" id="addInvestmentModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-1 mb-0" style="font-size: 13.5px;">
                    <i class="fas fa-plus-circle" style="color: #6366f1;"></i> <span>Log Capital Investment</span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ url('/admin/accounts/investment/store') }}" method="POST" enctype="multipart/form-data" id="addInvestmentForm">
                @csrf
                <!-- Hidden input for Global Media Picker -->
                <input type="hidden" name="voucher_receipt" id="investment_receipt_media_input" value="">

                <div class="modal-body p-3">
                    <div class="row g-2">
                        <!-- Investor Name -->
                        <div class="col-12">
                            <label class="form-label">
                                Investor Name / Capital Source <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="investor_name" class="form-control" placeholder="Founder Equity, Angel Investor (Mr. Rahim)" required>
                        </div>

                        <!-- Amount -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Amount (৳) <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="amount" class="form-control font-monospace bn-digit text-end fw-bold text-success" placeholder="0.00" min="1" step="any" required>
                        </div>

                        <!-- Date -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Injection Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="investment_date" class="form-control font-monospace bn-digit" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <!-- Deposit To Account -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Credit Account (Bank/Vault) <span class="text-danger">*</span>
                            </label>
                            <select name="bank_account_id" id="investmentBankAccountId" class="form-select font-monospace" onchange="handleInvestmentAccountChange(this)" required>
                                @if(isset($bankAccounts) && $bankAccounts->isNotEmpty())
                                    @foreach($bankAccounts as $acc)
                                        <option value="{{ $acc->id }}" data-type="{{ $acc->account_type }}" {{ $acc->account_type === 'bank' ? 'selected' : '' }}>
                                            {{ $acc->account_name }} (৳{{ number_format((float)$acc->current_balance, 2) }})
                                        </option>
                                    @endforeach
                                @else
                                    <option value="">No Accounts Available</option>
                                @endif
                            </select>
                        </div>

                        <!-- Payment Method -->
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

                        <!-- Reference No -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Reference / Slip #</label>
                            <input type="text" name="reference_no" class="form-control font-monospace" placeholder="SLIP-09823">
                        </div>

                        <!-- Modern Global Media Chooser Component for Attachment -->
                        <div class="col-12 col-md-6">
                            <label class="form-label d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-paperclip text-primary me-1"></i> Attach Slip / Agreement</span>
                                <span class="badge bg-light text-muted border font-monospace" style="font-size: 8.5px;">GLOBAL MEDIA</span>
                            </label>

                            <div class="proof-media-surface" style="border-radius: 5px;">
                                <div class="d-flex align-items-center gap-1 mb-1 flex-wrap">
                                    <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" onclick="chooseInvestmentSlipMedia()" style="background-color: #6366f1; border-color: #6366f1;">
                                        <i class="fas fa-photo-film me-1"></i> Media Library
                                    </button>
                                </div>

                                <input type="file" name="receipt" id="investmentFileInput" class="form-control mb-1" accept="image/*,application/pdf" onchange="handleInvestmentFileSlip(event)">

                                <div id="investmentPreviewContainer" class="d-none bg-white border p-1 align-items-center gap-2" style="border-radius: 5px;">
                                    <div class="proof-thumb-box shadow-xs" onclick="zoomInvestmentModalPreview()" title="Click to Zoom Slip">
                                        <img id="investmentPreviewImg" src="" alt="Slip Preview" onerror="this.src='https://placehold.co/80x80?text=Slip';">
                                        <div class="proof-thumb-hover-overlay">
                                            <i class="fas fa-search-plus text-white" style="font-size: 8px;"></i>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-grow-1">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace text-uppercase" style="font-size: 8.5px;">
                                            <i class="fas fa-check-circle me-1"></i>Slip Attached
                                        </span>
                                        <small class="text-muted d-block text-truncate font-monospace" id="investmentPathText" style="font-size: 9.5px;">slip.jpg</small>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger btn-compact py-0 px-2" style="height: 20px; font-size: 9px !important;" onclick="clearInvestmentSlipMedia()">
                                        <i class="fas fa-times me-1"></i>Clear
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Note -->
                        <div class="col-12">
                            <label class="form-label">Notes / Particulars</label>
                            <textarea name="note" rows="2" class="form-control" placeholder="Purpose or terms of investment..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-compact shadow-xs fw-bold px-3">
                        <i class="fas fa-check-circle me-1"></i> Save Investment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Delete Confirmation Modal (Replaces browser confirm) -->
<div class="modal fade inv-modal-scope" id="deleteInvestmentConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2 text-danger">
                    <i class="fas fa-trash-can fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Delete Investment Record?</h6>
                <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">
                    Are you sure you want to permanently delete <strong id="deleteInvestmentTitle" class="text-dark">this record</strong>? The credited balance will be deducted from the account.
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-compact shadow-xs fw-bold" id="btnExecuteInvestmentDelete">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 3: Lightbox Zoom Preview Modal -->
<div class="modal fade inv-modal-scope" id="investmentSlipZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;" id="investmentSlipZoomTitle">
                    <i class="fas fa-image text-primary me-1"></i> Investment Slip Preview
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="investmentSlipZoomImgTag" src="" alt="Slip Memo" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<!-- Modal 4: Custom Enterprise Notice Modal (Replaces browser alert) -->
<div class="modal fade inv-modal-scope" id="investmentNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2" id="investmentNoticeIconWrap">
                    <i class="fas fa-circle-check text-success fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="investmentNoticeTitle" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="investmentNoticeMessage" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-primary btn-compact px-4" data-bs-dismiss="modal" style="background-color: #6366f1; border-color: #6366f1;">Acknowledge</button>
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