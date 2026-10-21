@extends('layouts.admin')

@section('title', 'Courier COD & Cost Settlement - ' . config('app.name'))

@php
    // Safe media resolver supporting Global Media Library paths
    $resolveCourierMedia = function($path, $fallback = '') {
        if (empty($path)) return $fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) return show_image($path, $fallback);
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset(ltrim(str_replace(['public_html/', 'public/'], '', $path), '/'));
    };
@endphp

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #courier-settlement-root .btn,
    #courier-settlement-root .btn-sm,
    #courier-settlement-root button,
    #courier-settlement-root .badge,
    #courier-settlement-root .form-control,
    #courier-settlement-root .form-select,
    #courier-settlement-root .page-link,
    #courier-settlement-root .page-item span,
    #courier-settlement-root .input-group-text,
    #courier-settlement-root .card,
    #courier-settlement-root .settlement-table-card,
    #courier-settlement-root .kpi-card,
    #courier-settlement-root .settle-nav-pill,
    #courier-settlement-root .proof-thumb-box,
    #courier-settlement-root .proof-media-surface,
    .settlement-modal-scope .modal-content,
    .settlement-modal-scope .btn,
    .settlement-modal-scope .form-control,
    .settlement-modal-scope .form-select,
    .settlement-modal-scope .input-group-text,
    .settlement-modal-scope .badge,
    .settlement-modal-scope .alert {
        border-radius: 5px !important;
    }

    #courier-settlement-root .input-group > .input-group-text,
    .settlement-modal-scope .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #courier-settlement-root .input-group > .form-control,
    .settlement-modal-scope .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Safe Monospace & Bengali Digit Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Compact Button Styling */
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

    /* KPI Summary Cards - Overflow Safe Mobile & Desktop Hierarchy */
    .kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 10px 12px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        position: relative;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s ease;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
    }
    .kpi-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3.5px;
    }
    .kpi-expected::before { background-color: #0284c7; }
    .kpi-settled::before  { background-color: #10b981; }
    .kpi-due::before      { background-color: #ef4444; }
    .kpi-cost::before     { background-color: #f59e0b; }

    /* Status Navigation Strip */
    .settle-nav-strip {
        display: flex;
        flex-wrap: nowrap;
        gap: 6px;
        overflow-x: auto;
        padding: 2px 0;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }
    .settle-nav-strip::-webkit-scrollbar {
        display: none;
    }
    .settle-nav-pill {
        font-size: 11.5px;
        font-weight: 600;
        padding: 6px 12px;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        transition: all 0.15s ease;
    }
    .settle-nav-pill:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #cbd5e1;
    }
    .settle-nav-pill.active {
        color: #ffffff !important;
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25);
    }

    /* Multi-Filter Form Controls */
    #courier-settlement-root .form-control,
    #courier-settlement-root .form-select {
        min-height: 32px;
        height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        color: #0f172a;
        box-shadow: none;
        padding: 4px 8px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #courier-settlement-root .form-control:focus,
    #courier-settlement-root .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Floating Bulk Settle Bar */
    .settlement-bulk-bar {
        background: #0f172a;
        color: #ffffff;
        padding: 8px 14px;
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.16);
    }

    /* Desktop View Optimization: 100% Fit Screen with Zero Horizontal Scroll */
    .custom-settlement-table {
        width: 100%;
        margin-bottom: 0;
    }
    .custom-settlement-table th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 5px;
        white-space: nowrap;
    }
    .custom-settlement-table td {
        padding: 7px 5px;
        vertical-align: middle;
        font-size: 11px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .custom-settlement-table tbody tr:hover {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .custom-settlement-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .custom-settlement-table th,
        .custom-settlement-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 10 Columns Proportional Distribution Summing to Exactly 100% */
        .col-w-chk  { width: 3%; }
        .col-w-ord  { width: 13%; }
        .col-w-trk  { width: 13%; }
        .col-w-cust { width: 15%; }
        .col-w-exp  { width: 9%; }
        .col-w-set  { width: 9%; }
        .col-w-due  { width: 8%; }
        .col-w-dst  { width: 8%; }
        .col-w-sst  { width: 8%; }
        .col-w-act  { width: 14%; }
    }

    /* Global Media Chooser Component Styles */
    .proof-media-surface {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        padding: 10px;
        transition: all 0.2s ease;
    }
    .proof-media-surface:hover {
        border-color: #2563eb;
        background: #f1f5f9;
    }
    .proof-thumb-box {
        width: 22px;
        height: 22px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        overflow: hidden;
        display: flex;
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
        background: rgba(15, 23, 42, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.15s ease;
    }
    .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    /* Bulletproof Responsive Pagination */
    #courierSettlementPagination {
        display: flex;
        align-items: center;
    }
    #courierSettlementPagination nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }
    #courierSettlementPagination nav > div.d-sm-none,
    #courierSettlementPagination nav > .d-sm-none {
        display: none !important;
    }
    #courierSettlementPagination nav > div.d-none,
    #courierSettlementPagination nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    #courierSettlementPagination nav p,
    #courierSettlementPagination nav .small.text-muted,
    #courierSettlementPagination nav div:has(> p),
    #courierSettlementPagination nav > div.d-none > div:first-child,
    #courierSettlementPagination nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }
    #courierSettlementPagination .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 3px !important;
        padding: 0 !important;
        list-style: none !important;
    }
    #courierSettlementPagination .page-item {
        display: inline-flex !important;
    }
    #courierSettlementPagination .page-item .page-link,
    #courierSettlementPagination .page-item span {
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
    #courierSettlementPagination .page-item .page-link:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }
    #courierSettlementPagination .page-item.active .page-link,
    #courierSettlementPagination .page-item.active span {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25) !important;
    }
    #courierSettlementPagination .page-item.disabled .page-link,
    #courierSettlementPagination .page-item.disabled span {
        opacity: 0.45 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }
    #courierSettlementPagination nav svg {
        width: 10px !important;
        height: 10px !important;
    }

    /* Mobile Responsive Optimizations (< 768px) */
    @media (max-width: 767.98px) {
        .kpi-card {
            padding: 8px 10px !important;
        }
        .top-action-bar-wrap {
            width: 100% !important;
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 6px !important;
        }
        .settlement-bulk-bar {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 8px !important;
        }
        .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .custom-settlement-table {
            min-width: 820px !important;
            table-layout: auto !important;
        }
        #courierSettlementPagination {
            width: 100% !important;
            justify-content: center !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            padding: 4px 0 !important;
        }
        #courierSettlementPagination nav {
            justify-content: center !important;
            width: 100% !important;
        }
        #courierSettlementPagination .pagination {
            flex-wrap: nowrap !important;
            gap: 3px !important;
        }
        #courierSettlementPagination .page-item .page-link,
        #courierSettlementPagination .page-item span {
            min-width: 24px !important;
            height: 24px !important;
            font-size: 10.5px !important;
            padding: 0 5px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="courier-settlement-root">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-money-bill-transfer text-primary"></i>
                <span>Courier COD &amp; Cost Settlement</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Audit courier disbursements, reconcile collected COD receivables, attach remittance proof &amp; log merchant payouts.
            </small>
        </div>
        <div class="top-action-bar-wrap d-flex flex-wrap gap-2">
            <a href="{{ route('admin.courier.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-boxes-packing me-1"></i> <span>All Courier Orders</span>
            </a>
            <a href="{{ route('admin.courier.dashboard') }}" class="btn btn-dark btn-compact shadow-xs">
                <i class="fas fa-chart-pie me-1"></i> <span>Courier Dashboard</span>
            </a>
        </div>
    </div>

    <!-- Alert Box -->
    <div id="settlementAlert" class="alert d-none py-2 px-3 small border-0 shadow-sm mb-3" style="font-size: 12px;"></div>

    <!-- 1. KPI Financial Overview Cards -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-expected">
                <span class="text-muted small d-block mb-1 text-truncate" style="font-size: 10.5px; text-transform: uppercase; font-weight: 700;">Total Expected COD</span>
                <h5 class="fw-bold text-dark mb-1 font-monospace bn-digit" style="font-size: 15px;">৳ {{ number_format($totalExpectedCod, 2) }}</h5>
                <small class="text-secondary text-truncate d-block" style="font-size: 9.5px;">Receivable customer cash</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-settled">
                <span class="text-muted small d-block mb-1 text-truncate" style="font-size: 10.5px; text-transform: uppercase; font-weight: 700;">Disbursed &amp; Settled</span>
                <h5 class="fw-bold text-success mb-1 font-monospace bn-digit" style="font-size: 15px;">৳ {{ number_format($totalSettledCod, 2) }}</h5>
                <small class="text-muted text-truncate d-block" style="font-size: 9.5px;">Deposited into merchant ledger</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-due">
                <span class="text-muted small d-block mb-1 text-truncate" style="font-size: 10.5px; text-transform: uppercase; font-weight: 700;">Pending / Due COD</span>
                <h5 class="fw-bold text-danger mb-1 font-monospace bn-digit" style="font-size: 15px;">৳ {{ number_format($totalDueCod, 2) }}</h5>
                <small class="text-danger text-truncate d-block" style="font-size: 9.5px;">Held by courier partners</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-cost">
                <span class="text-muted small d-block mb-1 text-truncate" style="font-size: 10.5px; text-transform: uppercase; font-weight: 700;">Total Courier Expense</span>
                <h5 class="fw-bold text-dark mb-1 font-monospace bn-digit" style="font-size: 15px;">৳ {{ number_format($totalCourierCost, 2) }}</h5>
                <small class="text-muted text-truncate d-block" style="font-size: 9.5px;">Delivery charges &amp; returns</small>
            </div>
        </div>
    </div>

    <!-- 2. Status Navigation Tabs -->
    @php
        $currStatus = request('status', 'all');
    @endphp
    <div class="card p-2 mb-3 border bg-white shadow-xs">
        <div class="settle-nav-strip">
            <a href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}" class="settle-nav-pill {{ $currStatus === 'all' ? 'active' : '' }}">
                <span>All Bookings</span>
                <span class="badge {{ $currStatus === 'all' ? 'bg-white text-dark' : 'bg-secondary-subtle text-dark border' }} px-1 py-0 font-monospace bn-digit" style="font-size: 9.5px;">{{ $counts['all'] ?? 0 }}</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'unsettled']) }}" class="settle-nav-pill {{ $currStatus === 'unsettled' ? 'active' : '' }}">
                <span>Unsettled / Pending</span>
                <span class="badge bg-danger text-white px-1 py-0 font-monospace bn-digit" style="font-size: 9.5px;">{{ $counts['unsettled'] ?? 0 }}</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'partial']) }}" class="settle-nav-pill {{ $currStatus === 'partial' ? 'active' : '' }}">
                <span>Partially Settled</span>
                <span class="badge bg-warning text-dark px-1 py-0 font-monospace bn-digit" style="font-size: 9.5px;">{{ $counts['partial'] ?? 0 }}</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'settled']) }}" class="settle-nav-pill {{ $currStatus === 'settled' ? 'active' : '' }}">
                <span>Fully Settled</span>
                <span class="badge bg-success text-white px-1 py-0 font-monospace bn-digit" style="font-size: 9.5px;">{{ $counts['settled'] ?? 0 }}</span>
            </a>
        </div>
    </div>

    <!-- 3. Search & Filter Bar -->
    <div class="card border mb-3 p-3 bg-white shadow-xs">
        <form action="{{ route('admin.courier.settlements') }}" method="GET" class="row g-2 align-items-center">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0 shadow-none" placeholder="Search tracking #, invoice, phone..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <select name="courier" class="form-select form-select-sm shadow-none">
                    <option value="all">All Courier Partners</option>
                    @foreach($couriersList as $cr)
                        <option value="{{ $cr }}" {{ strtolower(request('courier')) === strtolower($cr) ? 'selected' : '' }}>
                            {{ $cr }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-3">
                <select name="date_filter" class="form-select form-select-sm shadow-none">
                    <option value="">All Time Period</option>
                    <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="this_week" {{ request('date_filter') === 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ request('date_filter') === 'this_month' ? 'selected' : '' }}>This Month</option>
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-compact flex-grow-1 shadow-xs">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if(request()->anyFilled(['search', 'courier', 'date_filter']))
                    <a href="{{ route('admin.courier.settlements') }}" class="btn btn-light btn-compact border text-muted shadow-xs">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- 4. Floating Bulk Settle Action Bar (Appears Only When Rows Are Checked) -->
    <div class="settlement-bulk-bar shadow-xs" id="settlementBulkActionBar" style="display: none;">
        <div class="d-flex align-items-center gap-2">
            <span class="small fw-bold text-white d-flex align-items-center" style="font-size: 12px;">
                <i class="fas fa-check-double text-warning me-2"></i>
                <span>Bulk Settlement:</span>
            </span>
            <span class="badge bg-warning text-dark px-2 py-1 bn-digit font-monospace" id="selectedCounterBadge" style="font-size: 11px;">0 selected</span>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-success btn-compact px-3 shadow-xs" onclick="executeBulkSettle()">
                <i class="fas fa-check-circle me-1"></i> Settle Selected Parcels
            </button>
            <button type="button" class="btn btn-outline-light btn-compact" onclick="clearAllBulkSettlementSelections()">
                Cancel
            </button>
        </div>
    </div>

    <!-- 5. Settlement Data Table Card (Desktop 100% Fit Screen with Zero Horizontal Scroll) -->
    <div class="card border settlement-table-card overflow-hidden shadow-xs">
        <div class="card-body p-0">
            <div class="table-responsive pc-fit-container">
                <table class="table align-middle mb-0 custom-settlement-table" id="settlementMainTable">
                    <thead>
                        <tr>
                            <th class="ps-3 text-center col-w-chk">
                                <input type="checkbox" id="selectAllCheckbox" class="form-check-input mt-0 shadow-none" onclick="toggleSelectAll(this)" style="cursor: pointer; width: 14px; height: 14px; border-radius: 4px;">
                            </th>
                            <th class="col-w-ord">Booking &amp; Order</th>
                            <th class="col-w-trk">Courier &amp; Tracking</th>
                            <th class="col-w-cust">Customer Details</th>
                            <th class="col-w-exp">Expected COD</th>
                            <th class="col-w-set">Settled COD</th>
                            <th class="col-w-due">Due COD</th>
                            <th class="text-center col-w-dst">Delivery</th>
                            <th class="text-center col-w-sst">Settlement</th>
                            <th class="text-end pe-3 col-w-act">Proof &amp; Action</th>
                        </tr>
                    </thead>
                    <tbody id="settlementTableBody">
                        @forelse($bookings as $row)
                            @php
                                $codAmount     = (float) ($row->cod_amount ?? 0);
                                $settledAmount = (float) ($row->settled_amount ?? 0);
                                $dueCod        = max(0.00, $codAmount - $settledAmount);

                                $settleStat = strtolower($row->cod_settlement_status ?? 'unsettled');
                                $settleBadge = match($settleStat) {
                                    'settled' => 'bg-success-subtle text-success border border-success-subtle',
                                    'partial' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                    default   => 'bg-danger-subtle text-danger border border-danger-subtle'
                                };

                                $delivStat = strtolower($row->delivery_status ?? 'in_transit');
                                $delivBadge = match($delivStat) {
                                    'delivered', 'completed' => 'badge-soft-success',
                                    'returned', 'return'     => 'badge-soft-danger',
                                    'cancelled'              => 'badge-soft-danger',
                                    default                  => 'badge-soft-info'
                                };

                                // Resolve settlement proof voucher / POD slip supporting Global Media Library
                                $proofRaw = $row->settlement_proof ?? ($row->consignment_slip ?? ($row->screenshot ?? null));
                                $proofUrl = !empty($proofRaw) ? $resolveCourierMedia($proofRaw) : null;
                            @endphp
                            <tr id="booking-row-{{ $row->id }}">
                                <td class="ps-3 text-center col-w-chk">
                                    <input type="checkbox" name="booking_ids[]" value="{{ $row->id }}" class="form-check-input booking-checkbox mt-0 shadow-none" onchange="updateSelectedCounter()" style="cursor: pointer; width: 14px; height: 14px; border-radius: 4px;">
                                </td>
                                <td class="col-w-ord">
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.order.view', $row->order_id) }}" class="fw-bold text-primary text-decoration-none font-monospace bn-digit d-block text-truncate" style="font-size: 11.5px;" title="#{{ $row->invoice_no ?? $row->order_id }}">
                                            #{{ $row->invoice_no ?? $row->order_id }}
                                        </a>
                                        <small class="text-muted font-monospace bn-digit d-block text-truncate" style="font-size: 9.5px;">
                                            Total: ৳{{ number_format((float)($row->order_grand_total ?? 0), 2) }}
                                        </small>
                                    </div>
                                </td>
                                <td class="col-w-trk">
                                    <div class="min-w-0">
                                        <span class="badge bg-light text-dark border text-capitalize px-1 py-0 fw-semibold text-truncate d-inline-block" style="font-size: 9px; max-width: 90px;" title="{{ $row->courier_name }}">
                                            {{ $row->courier_name }}
                                        </span>
                                        <small class="d-block font-monospace text-primary fw-bold mt-1 bn-digit text-truncate" style="font-size: 10px;" title="{{ $row->tracking_code }}">
                                            {{ $row->tracking_code }}
                                        </small>
                                        @if(!empty($row->consignment_id) && $row->consignment_id !== $row->tracking_code)
                                            <small class="d-block text-secondary font-monospace text-truncate bn-digit" style="font-size: 9px;">CN: {{ $row->consignment_id }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td class="col-w-cust">
                                    <div class="min-w-0">
                                        <strong class="text-dark d-block text-truncate mb-1" style="font-size: 11.5px;" title="{{ $row->shipping_name ?? 'Customer' }}">
                                            {{ $row->shipping_name ?? 'Customer' }}
                                        </strong>
                                        <span class="text-muted small text-nowrap font-monospace bn-digit d-block text-truncate" style="font-size: 10px;">
                                            {{ $row->shipping_phone ?? 'N/A' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="col-w-exp">
                                    <span class="fw-bold text-dark font-monospace bn-digit d-block" style="font-size: 11.5px;">
                                        ৳ {{ number_format($codAmount, 2) }}
                                    </span>
                                    @if((float)$row->courier_cost > 0)
                                        <small class="text-muted d-block bn-digit font-monospace" style="font-size: 9px;">Cost: ৳{{ number_format((float)$row->courier_cost, 0) }}</small>
                                    @endif
                                </td>
                                <td class="col-w-set">
                                    <span class="fw-bold text-success font-monospace bn-digit d-block" style="font-size: 11.5px;">
                                        ৳ {{ number_format($settledAmount, 2) }}
                                    </span>
                                    @if(!empty($row->settlement_date))
                                        <small class="text-muted d-block font-monospace bn-digit" style="font-size: 9px;">{{ date('d M Y', strtotime($row->settlement_date)) }}</small>
                                    @endif
                                </td>
                                <td class="col-w-due">
                                    <span class="fw-bold {{ $dueCod > 0 ? 'text-danger' : 'text-muted' }} font-monospace bn-digit" style="font-size: 11.5px;">
                                        ৳ {{ number_format($dueCod, 2) }}
                                    </span>
                                </td>
                                <td class="text-center col-w-dst">
                                    <span class="badge {{ $delivBadge }} px-1 py-0.5 text-uppercase fw-semibold font-monospace" style="font-size: 8.5px; border-radius: 5px !important;">
                                        {{ str_replace('_', ' ', $delivStat) }}
                                    </span>
                                </td>
                                <td class="text-center col-w-sst">
                                    <span class="badge {{ $settleBadge }} px-1 py-0.5 text-uppercase fw-semibold font-monospace" id="settle-badge-{{ $row->id }}" style="font-size: 8.5px; border-radius: 5px !important;">
                                        {{ $settleStat }}
                                    </span>
                                </td>
                                <td class="text-end pe-2 col-w-act">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <!-- Global Media Chooser Interactive Proof Thumbnail -->
                                        @if($proofUrl)
                                            <div class="proof-thumb-box" onclick="zoomSettlementProof('{{ $proofUrl }}', 'Invoice #{{ $row->invoice_no ?? $row->order_id }} Settlement Proof')" title="Click to Zoom Remittance Proof">
                                                <img src="{{ $proofUrl }}" alt="Proof" onerror="this.src='https://placehold.co/40x40?text=Slip';">
                                                <div class="proof-thumb-hover-overlay">
                                                    <i class="fas fa-search-plus text-white" style="font-size: 8.5px;"></i>
                                                </div>
                                            </div>
                                        @endif

                                        <button type="button" class="btn btn-outline-success btn-compact py-0 px-2 shadow-xs" style="height: 22px; font-size: 10px !important;" onclick="openSettlementModal({{ $row->id }}, {{ $codAmount }}, {{ $settledAmount }}, '{{ $row->courier_name }}', '{{ $row->tracking_code }}', {{ (float)$row->courier_cost }}, {{ (float)$row->cod_charge }}, '{{$proofRaw ?? '' }}')" title="Reconcile &amp; Settle">
                                            <i class="fas fa-hand-holding-dollar me-1"></i> <span>Settle</span>
                                        </button>
                                        <a href="{{ route('admin.order.view', $row->order_id) }}" class="btn btn-light border p-0 shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 10px;" title="View Customer Order">
                                            <i class="fas fa-eye text-muted"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted small">
                                    <i class="fas fa-money-check fa-2x mb-2 d-block opacity-25"></i>
                                    <span class="fw-semibold">No courier bookings found for the selected settlement criteria.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination & Footer (Exact Transaction Standard) -->
        <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50">
            <small class="text-muted bn-digit font-monospace" style="font-size: 11px;">
                Showing {{ $bookings->firstItem() ?? 0 }} to {{ $bookings->lastItem() ?? 0 }} of {{$bookings->total() }} record(s)
            </small>
            <div id="courierSettlementPagination">
                {{ $bookings->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- 6. Single Settlement Modal with 200% Global Media Chooser Support -->
<div class="modal fade settlement-modal-scope" id="settlementModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2 px-3">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-2 mb-0" style="font-size: 13.5px;">
                    <i class="fas fa-file-invoice-dollar text-warning"></i> <span>Courier COD Reconciliation &amp; Proof</span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="settlementForm" onsubmit="handleSettlementSubmit(event)">
                @csrf
                <input type="hidden" id="modalBookingId" name="booking_id">
                <input type="hidden" id="modalSettlementProofMedia" name="settlement_proof" value="">

                <div class="modal-body p-3">
                    <div class="bg-light p-2 border mb-3" style="border-radius: 5px;">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small" style="font-size: 11px;">Courier Partner:</span>
                            <strong class="text-dark font-monospace" id="modalCourierName">—</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small" style="font-size: 11px;">Tracking Code:</span>
                            <span class="font-monospace text-primary fw-bold" id="modalTrackingCode">—</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small" style="font-size: 11px;">Expected Customer COD:</span>
                            <strong class="text-danger font-monospace bn-digit" id="modalExpectedCod">৳ 0.00</strong>
                        </div>
                    </div>

                    <div class="row g-2">
                        <!-- Settled Amount Deposited -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">
                                Actual Received / Settled (৳) <span class="text-danger">*</span>
                            </label>
                            <input type="number" id="modalSettledAmount" name="settled_amount" class="form-control form-control-sm font-monospace bn-digit text-end fw-bold text-success shadow-none" required step="any" min="0">
                        </div>

                        <!-- Settlement Date -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">
                                Deposit / Handover Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" id="modalSettlementDate" name="settlement_date" class="form-control form-control-sm font-monospace bn-digit shadow-none" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <!-- Courier Delivery Fee / Cost -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">Courier Delivery Fee (৳)</label>
                            <input type="number" id="modalCourierCost" name="courier_cost" class="form-control form-control-sm font-monospace bn-digit text-end shadow-none" step="any" min="0" value="0">
                        </div>

                        <!-- COD Fee / Commission -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">Courier COD Charge (1% / Fee)</label>
                            <input type="number" id="modalCodCharge" name="cod_charge" class="form-control form-control-sm font-monospace bn-digit text-end shadow-none" step="any" min="0" value="0">
                        </div>

                        <!-- Payment Deposit Account -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">Merchant Deposit Method</label>
                            <select name="payment_method" class="form-select form-select-sm shadow-none" style="font-size: 11.5px;">
                                <option value="Bank Transfer (Courier COD)" selected>Bank Transfer (Courier COD)</option>
                                <option value="bKash Merchant">bKash Merchant</option>
                                <option value="Nagad Business">Nagad Business</option>
                                <option value="Cash Handover">Cash Handover</option>
                            </select>
                        </div>

                        <!-- Bank Reference / Transaction ID -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">Bank Trx / Batch ID (Optional)</label>
                            <input type="text" name="transaction_id" class="form-control form-control-sm font-monospace bn-digit shadow-none" placeholder="STLM-98234">
                        </div>

                        <!-- 200% Global Media Chooser Component: Attach Bank Voucher / Remittance Slip -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted mb-1 d-flex justify-content-between align-items-center" style="font-size: 11px;">
                                <span><i class="fas fa-paperclip text-primary me-1"></i> Bank Voucher / Remittance Proof</span>
                                <span class="badge bg-light text-muted border font-monospace" style="font-size: 8.5px;">GLOBAL MEDIA</span>
                            </label>
                            <div class="proof-media-surface">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <button type="button" class="btn btn-outline-primary btn-compact shadow-xs" onclick="chooseSettlementProofMedia()">
                                        <i class="fas fa-photo-film me-1"></i>
                                        <span>Choose from Media Library</span>
                                    </button>
                                </div>

                                <div id="settlement_proof_preview_wrap" class="mt-2 d-none align-items-center gap-2 bg-white border p-1" style="border-radius: 5px;">
                                    <div class="proof-thumb-box" onclick="zoomSettlementProof(document.getElementById('settlement_proof_preview_img').src, 'Settlement Slip Preview')">
                                        <img id="settlement_proof_preview_img" src="" alt="Proof Preview">
                                        <div class="proof-thumb-hover-overlay">
                                            <i class="fas fa-search-plus text-white" style="font-size: 9px;"></i>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-grow-1">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 8.5px;">
                                            <i class="fas fa-check-circle me-1"></i>Voucher Attached
                                        </span>
                                        <small class="text-muted d-block text-truncate font-monospace" id="settlement_proof_preview_txt" style="font-size: 9px;"></small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-auto text-decoration-none fw-bold" style="font-size: 11px;" onclick="clearSettlementProofMedia()">&times; Clear</button>
                                </div>
                            </div>
                        </div>

                        <!-- Remarks / Notes -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">Settlement Note</label>
                            <textarea name="remarks" id="modalRemarks" rows="2" class="form-control form-control-sm shadow-none" placeholder="Disbursement invoice number, bank statement note..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2 px-3">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="saveSettlementBtn" class="btn btn-primary btn-compact shadow-xs fw-bold">
                        <i class="fas fa-check-circle me-1"></i> Verify &amp; Settle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 7. Lightbox Zoom Modal for Consignment & Settlement Slips -->
<div class="modal fade settlement-modal-scope" id="settlementProofZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 1080;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;" id="settlementProofZoomTitle">
                    <i class="fas fa-image text-primary me-1"></i> Settlement Remittance Proof
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="settlementProofZoomImgTag" src="" alt="Proof Preview" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<script>
// Checkbox and Bulk Settle Management
function toggleSelectAll(master) {
    const checkboxes = document.querySelectorAll('.booking-checkbox');
    checkboxes.forEach(cb => cb.checked = master.checked);
    updateSelectedCounter();
}

function updateSelectedCounter() {
    const selected = document.querySelectorAll('.booking-checkbox:checked').length;
    const badge = document.getElementById('selectedCounterBadge');
    const bulkBar = document.getElementById('settlementBulkActionBar');
    if (badge) badge.innerText = `${selected} selected`;
    if (bulkBar) {
        bulkBar.style.display = selected > 0 ? 'flex' : 'none';
    }
}

function clearAllBulkSettlementSelections() {
    const master = document.getElementById('selectAllCheckbox');
    if (master) master.checked = false;
    document.querySelectorAll('.booking-checkbox').forEach(cb => cb.checked = false);
    updateSelectedCounter();
}

function executeBulkSettle() {
    const selectedCbs = document.querySelectorAll('.booking-checkbox:checked');
    if (selectedCbs.length === 0) {
        showAlert('warning', 'Please select at least one courier parcel to settle.');
        return;
    }

    if (!confirm(`Are you sure you want to mark ${selectedCbs.length} selected parcel(s) as SETTLED? This will synchronize order payment status and accounting ledgers.`)) {
        return;
    }

    const bookingIds = Array.from(selectedCbs).map(cb => parseInt(cb.value));

    fetch("{{ route('admin.courier.bulk.action') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            action_type: 'bulk_settle',
            booking_ids: bookingIds
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            clearAllBulkSettlementSelections();
            setTimeout(() => window.location.reload(), 1200);
        } else {
            showAlert('danger', data.message || 'Bulk settlement failed.');
        }
    })
    .catch(err => {
        console.error(err);
        showAlert('danger', 'Network error during bulk settlement.');
    });
}

// Global Media Chooser Helper Functions
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

function chooseSettlementProofMedia() {
    if (typeof window.openMediaPicker === 'function') {
        window.openMediaPicker({
            multiple: false,
            onSelect: function(media) {
                const picked = extractMediaItem(media);
                if (picked && picked.path) {
                    document.getElementById('modalSettlementProofMedia').value = picked.path;
                    const wrap = document.getElementById('settlement_proof_preview_wrap');
                    const img  = document.getElementById('settlement_proof_preview_img');
                    const txt  = document.getElementById('settlement_proof_preview_txt');
                    if (img) img.src = picked.url;
                    if (txt) txt.innerText = picked.path.split('/').pop() || picked.path;
                    if (wrap) {
                        wrap.classList.remove('d-none');
                        wrap.classList.add('d-flex');
                    }
                }
            }
        });
    } else {
        alert('Global Media Library is loading, please try again.');
    }
}

function clearSettlementProofMedia() {
    document.getElementById('modalSettlementProofMedia').value = '';
    const wrap = document.getElementById('settlement_proof_preview_wrap');
    if (wrap) {
        wrap.classList.add('d-none');
        wrap.classList.remove('d-flex');
    }
}

function zoomSettlementProof(url, title = 'Settlement Remittance Proof') {
    const titleEl = document.getElementById('settlementProofZoomTitle');
    const imgTag  = document.getElementById('settlementProofZoomImgTag');
    if (url && imgTag) {
        if (titleEl) titleEl.innerText = title;
        imgTag.src = url;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('settlementProofZoomModal')).show();
    }
}

// Single Settlement Modal Operations
function openSettlementModal(bookingId, expectedCod, settledAmount, courier, tracking, cost, codCharge, proof = '') {
    document.getElementById('modalBookingId').value = bookingId;
    document.getElementById('modalCourierName').innerText = courier;
    document.getElementById('modalTrackingCode').innerText = tracking;
    document.getElementById('modalExpectedCod').innerText = '৳ ' + parseFloat(expectedCod).toFixed(2);

    document.getElementById('modalSettledAmount').value = (settledAmount > 0 ? settledAmount : expectedCod).toFixed(2);
    document.getElementById('modalCourierCost').value = (cost || 0).toFixed(2);
    document.getElementById('modalCodCharge').value = (codCharge || 0).toFixed(2);

    clearSettlementProofMedia();
    if (proof) {
        document.getElementById('modalSettlementProofMedia').value = proof;
        const wrap = document.getElementById('settlement_proof_preview_wrap');
        const img  = document.getElementById('settlement_proof_preview_img');
        const txt  = document.getElementById('settlement_proof_preview_txt');
        if (img) img.src = proof;
        if (txt) txt.innerText = proof.split('/').pop();
        if (wrap) {
            wrap.classList.remove('d-none');
            wrap.classList.add('d-flex');
        }
    }

    const modal = new bootstrap.Modal(document.getElementById('settlementModal'));
    modal.show();
}

function handleSettlementSubmit(e) {
    e.preventDefault();

    const bookingId = document.getElementById('modalBookingId').value;
    const form = document.getElementById('settlementForm');
    const formData = new FormData(form);

    const submitBtn = document.getElementById('saveSettlementBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Settling...';

    const url = "{{ route('admin.courier.settlement.update', ':id') }}".replace(':id', bookingId);

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Settlement update failed.');
        return data;
    })
    .then(data => {
        const modalEl = document.getElementById('settlementModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        showAlert('success', data.message);
        setTimeout(() => window.location.reload(), 1000);
    })
    .catch(err => {
        alert(err.message);
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Verify &amp; Settle';
    });
}

function showAlert(type, message) {
    const box = document.getElementById('settlementAlert');
    box.className = `alert alert-${type} py-2 px-3 small border-0 shadow-sm mb-3`;
    box.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'} me-1"></i> ${message}`;
    box.classList.remove('d-none');
    box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
</script>
@endsection