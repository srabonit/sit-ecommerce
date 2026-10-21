@extends('layouts.admin')

@php
    $sectionTitles = [
        'dashboard'         => 'Payment & Transaction Control Dashboard',
        'checkout-requests' => 'Checkout Payment Requests',
        'pos-cash'          => 'POS Terminal Cash & Counter Orders',
        'cod-orders'        => 'Cash on Delivery (COD) Orders & Settlements',
        'manual'            => 'Manual Transactions Ledger',
        'all'               => 'All Payment Transactions (Unified Ledger)',
        'unchecked'         => 'Unchecked & Pending Verification Queue',
        'history'           => 'Immutable Payment Verification Audit History',
        'reports'           => 'Financial Transaction Reports & Analytics',
        'trash'             => 'Trashed & Replaced Transactions',
    ];
    $pageTitle = $sectionTitles[$activeSection ?? 'all'] ?? 'Payment & Transactions Management';
    $gatewayOptions = isset($activeGatewayMethods) && count($activeGatewayMethods) > 0 ? $activeGatewayMethods : $availableMethods;
@endphp

@section('title', $pageTitle . ' - ' . config('app.name'))

@section('admin_content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<style>
    .txn-page-wrapper {
        padding: 12px 14px 28px;
        background-color: #f8fafc;
        min-height: calc(100vh - 65px);
    }

    /* Strict 5px Global Radius System Across All Elements */
    #txnRootContainer .btn,
    #txnRootContainer .btn-sm,
    #txnRootContainer button,
    #txnRootContainer .form-control,
    #txnRootContainer .form-select,
    #txnRootContainer .input-group-text,
    #txnRootContainer .page-link,
    #txnRootContainer .page-item span,
    #txnRootContainer .badge,
    #txnRootContainer .txn-section-pill,
    #txnRootContainer .txn-card,
    #txnRootContainer .txn-stat-box,
    #txnRootContainer .proof-media-surface,
    #txnRootContainer .proof-thumb-box,
    .txn-modal-scope .modal-content,
    .txn-modal-scope .btn,
    .txn-modal-scope .form-control,
    .txn-modal-scope .form-select,
    .txn-modal-scope .input-group-text,
    .txn-modal-scope .badge,
    .txn-modal-scope .alert {
        border-radius: 5px !important;
    }

    #txnRootContainer .input-group > .input-group-text,
    .txn-modal-scope .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #txnRootContainer .input-group > .form-control,
    .txn-modal-scope .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Compact Button Styling */
    #txnRootContainer .btn-compact,
    .txn-modal-scope .btn-compact {
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

    #txnRootContainer .txn-card,
    .txn-modal-scope .modal-content {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    /* Section Navigation Horizontal Strip */
    .txn-section-nav {
        display: flex;
        flex-wrap: nowrap;
        gap: 6px;
        overflow-x: auto;
        padding: 2px;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }
    .txn-section-nav::-webkit-scrollbar {
        display: none;
    }
    .txn-section-pill {
        white-space: nowrap;
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
        transition: all 0.15s ease;
    }
    .txn-section-pill:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #cbd5e1;
    }
    .txn-section-pill.active {
        background: #2563eb;
        color: #ffffff !important;
        border-color: #2563eb;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
    }
    .txn-section-pill.active i {
        color: #ffffff !important;
    }

    /* Summary KPI Cards */
    .txn-stat-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 10px 12px;
        height: 100%;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .txn-stat-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
    }
    .txn-stat-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.03em;
        margin-bottom: 2px;
        display: block;
    }
    .txn-stat-value {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        line-height: 1.2;
        margin-bottom: 2px;
    }

    /* Filter Form Controls */
    .txn-filter-label {
        font-size: 11px;
        font-weight: 700;
        color: #475569;
        margin-bottom: 4px;
        display: block;
    }
    #txnFilterForm .form-control,
    #txnFilterForm .form-select {
        min-height: 32px;
        height: 32px;
        padding: 5px 10px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #txnFilterForm .form-control:focus,
    #txnFilterForm .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Soft Badges */
    .badge-soft-primary   { background: #e0f2fe !important; color: #0284c7 !important; border: 1px solid #bae6fd !important; }
    .badge-soft-success   { background: #dcfce7 !important; color: #15803d !important; border: 1px solid #bbf7d0 !important; }
    .badge-soft-danger    { background: #fee2e2 !important; color: #b91c1c !important; border: 1px solid #fecaca !important; }
    .badge-soft-warning   { background: #fef9c3 !important; color: #854d0e !important; border: 1px solid #fef08a !important; }
    .badge-soft-info      { background: #e0f2fe !important; color: #0369a1 !important; border: 1px solid #bae6fd !important; }
    .badge-soft-secondary { background: #f1f5f9 !important; color: #475569 !important; border: 1px solid #e2e8f0 !important; }

    /* Optimized Table Architecture for Desktop Fit */
    .custom-txn-table {
        width: 100%;
        margin-bottom: 0;
    }
    .custom-txn-table th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 9px 8px;
        white-space: nowrap;
    }
    .custom-txn-table td {
        padding: 8px;
        vertical-align: middle;
        font-size: 11.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }
    .custom-txn-table tbody tr:hover {
        background-color: #fafbfc;
    }

    .txn-action-btn {
        height: 26px;
        padding: 0 8px;
        font-size: 11px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        border-radius: 5px !important;
        white-space: nowrap;
    }
    .txn-icon-btn {
        width: 26px;
        height: 26px;
        padding: 0;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px !important;
    }

    /* Floating Bulk Action Bar */
    .txn-bulk-bar {
        background: #0f172a;
        color: #ffffff;
        border-radius: 5px !important;
        padding: 8px 14px;
        margin-bottom: 10px;
        display: none;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.18);
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
        min-height: 280px;
    }

    #txnLoadingOverlay {
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(2px);
        z-index: 30;
        display: none;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
    }

    /* Global Media Chooser Surface inside Modal */
    .proof-media-surface {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        padding: 10px;
        transition: all 0.2s ease;
    }
    .proof-media-surface:hover {
        border-color: #3b82f6;
        background: #f1f5f9;
    }
    .proof-thumb-box {
        width: 44px;
        height: 44px;
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

    /* Desktop View Optimization: Table Column Distribution */
    @media (min-width: 1200px) {
        .custom-txn-table {
            table-layout: fixed;
        }
        .col-w-check    { width: 34px; }
        .col-w-sl       { width: 38px; }
        .col-w-txn      { width: 155px; }
        .col-w-cust     { width: 145px; }
        .col-w-method   { width: 165px; }
        .col-w-fin      { width: 150px; }
        .col-w-status   { width: 135px; }
        .col-w-audit    { width: 150px; }
        .col-w-actions  { width: 105px; }
    }

    /* Ultra-Clean Compact Pagination (Fixed for Both Mobile & Desktop) */
    #txnPaginationLinks {
        display: flex;
        align-items: center;
    }
    #txnPaginationLinks nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }
    /* Hide Laravel's mobile simple prev/next block to prevent duplicate layout */
    #txnPaginationLinks nav > div.d-sm-none,
    #txnPaginationLinks nav > .d-sm-none {
        display: none !important;
    }
    /* Force Laravel's full-number block to render on mobile screens */
    #txnPaginationLinks nav > div.d-none,
    #txnPaginationLinks nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    /* Suppress duplicate text inside Laravel default pagination template */
    #txnPaginationLinks nav p,
    #txnPaginationLinks nav .small.text-muted,
    #txnPaginationLinks nav div:has(> p),
    #txnPaginationLinks nav > div.d-none > div:first-child,
    #txnPaginationLinks nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }
    #txnPaginationLinks .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 3px !important;
        padding: 0 !important;
        list-style: none !important;
    }
    #txnPaginationLinks .page-item {
        display: inline-flex !important;
    }
    #txnPaginationLinks .page-item .page-link,
    #txnPaginationLinks .page-item span {
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
    #txnPaginationLinks .page-item .page-link:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }
    #txnPaginationLinks .page-item.active .page-link,
    #txnPaginationLinks .page-item.active span {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25) !important;
    }
    #txnPaginationLinks .page-item.disabled .page-link,
    #txnPaginationLinks .page-item.disabled span {
        opacity: 0.45 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }
    #txnPaginationLinks nav svg {
        width: 10px !important;
        height: 10px !important;
    }

    /* Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .txn-page-wrapper {
            padding: 8px 8px 20px;
        }
        .txn-top-actions {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .txn-top-actions .btn-primary {
            grid-column: span 2;
        }
        .txn-stat-value {
            font-size: 13.5px;
        }
        #txnPaginationLinks {
            width: 100% !important;
            justify-content: center !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            padding: 4px 0 !important;
        }
        #txnPaginationLinks nav {
            justify-content: center !important;
            width: 100% !important;
        }
        #txnPaginationLinks .pagination {
            flex-wrap: nowrap !important;
            gap: 3px !important;
        }
        #txnPaginationLinks .page-item .page-link,
        #txnPaginationLinks .page-item span {
            min-width: 24px !important;
            height: 24px !important;
            font-size: 10.5px !important;
            padding: 0 5px !important;
        }
    }
</style>

<div class="container-fluid txn-page-wrapper" id="txnRootContainer">
    <!-- 1. Page Header & Top Action Buttons -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-money-check-dollar text-primary"></i>
                <span>{{ $pageTitle }}</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Verify customer payment requests, prevent duplicate TrxIDs, synchronize online orders &amp; manage audit trails.
            </small>
        </div>

        <div class="txn-top-actions d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-primary btn-compact shadow-xs gap-1" onclick="openManualTransactionModal()">
                <i class="fas fa-plus-circle me-1"></i>
                <span>Add Manual Transaction</span>
            </button>
            <button type="button" class="btn btn-outline-success btn-compact bg-white shadow-xs gap-1" onclick="exportTransactionsExcel()">
                <i class="fas fa-file-excel text-success me-1"></i>
                <span>Export Excel</span>
            </button>
            <button type="button" class="btn btn-outline-danger btn-compact bg-white shadow-xs gap-1" id="btnExportTxnPdf" onclick="exportTransactionsPdf()">
                <i class="fas fa-file-pdf text-danger me-1"></i>
                <span id="btnExportTxnPdfText">Export PDF (A4)</span>
            </button>
        </div>
    </div>

    <!-- 2. Section Navigation Strip -->
    <div class="txn-card p-2 mb-3">
        <div class="txn-section-nav">
            <a href="{{ route('admin.transactions.dashboard') }}" class="txn-section-pill {{ $activeSection === 'dashboard' ? 'active' : '' }}">
                <i class="fas fa-chart-pie text-warning"></i> <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.transactions.checkout') }}" class="txn-section-pill {{ $activeSection === 'checkout-requests' ? 'active' : '' }}">
                <i class="fas fa-globe text-info"></i> <span>Checkout Requests</span>
                <span class="badge bg-warning text-dark px-2 py-0 bn-digit font-monospace" id="nav-badge-checkout">{{ $alertCounters['pending_checkout_count'] ?? 0 }}</span>
            </a>
            <a href="{{ route('admin.transactions.unchecked') }}" class="txn-section-pill {{ $activeSection === 'unchecked' ? 'active' : '' }}">
                <i class="fas fa-clock text-danger"></i> <span>Unchecked Queue</span>
                <span class="badge bg-danger text-white px-2 py-0 bn-digit font-monospace" id="nav-badge-unchecked">{{ $alertCounters['total_unchecked_count'] ?? 0 }}</span>
            </a>
            <a href="{{ route('admin.transactions.pos') }}" class="txn-section-pill {{ $activeSection === 'pos-cash' ? 'active' : '' }}">
                <i class="fas fa-cash-register text-success"></i> <span>POS Cash Orders</span>
            </a>
            <a href="{{ route('admin.transactions.cod') }}" class="txn-section-pill {{ $activeSection === 'cod-orders' ? 'active' : '' }}">
                <i class="fas fa-truck text-primary"></i> <span>COD Orders</span>
            </a>
            <a href="{{ route('admin.transactions.manual') }}" class="txn-section-pill {{ $activeSection === 'manual' ? 'active' : '' }}">
                <i class="fas fa-hand-holding-dollar text-secondary"></i> <span>Manual Entries</span>
            </a>
            <a href="{{ route('admin.transactions.index') }}" class="txn-section-pill {{ $activeSection === 'all' ? 'active' : '' }}">
                <i class="fas fa-layer-group"></i> <span>All Transactions</span>
            </a>
            <a href="{{ route('admin.transactions.history') }}" class="txn-section-pill {{ $activeSection === 'history' ? 'active' : '' }}">
                <i class="fas fa-shield-halved text-info"></i> <span>Verification History</span>
            </a>
            <a href="{{ route('admin.transactions.reports') }}" class="txn-section-pill {{ $activeSection === 'reports' ? 'active' : '' }}">
                <i class="fas fa-file-contract text-success"></i> <span>Reports</span>
            </a>
            <a href="{{ route('admin.transactions.trash') }}" class="txn-section-pill {{ $activeSection === 'trash' ? 'active' : '' }}">
                <i class="fas fa-trash-can text-danger"></i> <span>Trashed</span>
                <span class="badge bg-secondary text-white px-2 py-0 bn-digit font-monospace" id="nav-badge-trash">{{ $alertCounters['trashed_count'] ?? 0 }}</span>
            </a>
        </div>
    </div>

    <!-- 3. Summary KPI Cards (Adaptive Grid on Mobile & Desktop) -->
    <div class="row g-2 mb-3" id="txnSummaryCardsRow">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="txn-stat-box">
                <span class="txn-stat-label">Active Records</span>
                <div class="txn-stat-value text-primary bn-digit font-monospace" id="sum-total-count">{{ $summary['total_transactions'] ?? 0 }}</div>
                <small class="text-muted d-block text-truncate bn-digit font-monospace" style="font-size: 10px;">Payable: ৳<span id="sum-expected-amt">{{ number_format((float)($summary['total_expected_amount'] ?? 0), 2) }}</span></small>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="txn-stat-box border-start border-success border-3">
                <span class="txn-stat-label text-success">Verified Paid</span>
                <div class="txn-stat-value text-success bn-digit font-monospace">৳<span id="sum-paid-amt">{{ number_format((float)($summary['total_paid_amount'] ?? 0), 2) }}</span></div>
                <small class="text-muted d-block text-truncate bn-digit font-monospace" style="font-size: 10px;">Paid Orders: <strong id="sum-paid-count">{{ $summary['count_paid'] ?? 0 }}</strong></small>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="txn-stat-box border-start border-danger border-3">
                <span class="txn-stat-label text-danger">Unpaid / Due</span>
                <div class="txn-stat-value text-danger bn-digit font-monospace">৳<span id="sum-unpaid-amt">{{ number_format((float)($summary['total_unpaid_amount'] ?? 0), 2) }}</span></div>
                <small class="text-muted d-block text-truncate bn-digit font-monospace" style="font-size: 10px;">Partial: ৳<span id="sum-partial-amt">{{ number_format((float)($summary['total_partial_amount'] ?? 0), 2) }}</span></small>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="txn-stat-box border-start border-warning border-3">
                <span class="txn-stat-label text-warning-emphasis">Unchecked Queue</span>
                <div class="txn-stat-value text-warning-emphasis bn-digit font-monospace" id="sum-pending-count">{{ $summary['count_pending'] ?? 0 }}</div>
                <small class="text-muted d-block text-truncate bn-digit font-monospace" style="font-size: 10px;">Review: <strong id="sum-review-count">{{ $summary['count_under_review'] ?? 0 }}</strong></small>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="txn-stat-box">
                <span class="txn-stat-label text-info">Admin Checked</span>
                <div class="txn-stat-value text-info bn-digit font-monospace">
                    <span id="sum-checked-count">{{ $summary['count_checked'] ?? 0 }}</span> <small class="text-muted fw-normal" style="font-size:11px;">/ App: <span id="sum-approved-count">{{ $summary['count_approved'] ?? 0 }}</span></small>
                </div>
                <small class="text-muted d-block text-truncate" style="font-size: 10px;">Verified by Staff</small>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="txn-stat-box">
                <span class="txn-stat-label text-danger">Fake / Unmatched</span>
                <div class="txn-stat-value text-danger bn-digit font-monospace">
                    <span id="sum-fake-count">{{ $summary['count_fake'] ?? 0 }}</span> / <span id="sum-unmatched-count">{{ $summary['count_unmatched'] ?? 0 }}</span>
                </div>
                <small class="text-muted d-block text-truncate bn-digit font-monospace" style="font-size: 10px;">Refunded: ৳<span id="sum-refunded-amt">{{ number_format((float)($summary['total_refunded_amount'] ?? 0), 2) }}</span></small>
            </div>
        </div>
    </div>

    <!-- 4. Filter Toolbar -->
    <div class="txn-card p-3 mb-3">
        <form id="txnFilterForm" onsubmit="event.preventDefault(); triggerTxnAjaxFilter(1);">
            <input type="hidden" name="section" id="filter_section" value="{{ $activeSection }}">

            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4 col-lg-3">
                    <label class="txn-filter-label" for="filter_search">Global Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                        <input type="text" name="search" id="filter_search" class="form-control border-start-0 ps-0 shadow-none" value="{{ request('search') }}" placeholder="Invoice#, Order ID, Name, Phone...">
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <label class="txn-filter-label" for="filter_trx_id">Transaction ID (TrxID)</label>
                    <input type="text" name="trx_id" id="filter_trx_id" class="form-control shadow-none font-monospace" value="{{ request('trx_id') }}" placeholder="Search TrxID...">
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <label class="txn-filter-label" for="filter_verification_status">Verification Status</label>
                    <select name="verification_status" id="filter_verification_status" class="form-select shadow-none">
                        <option value="all">All Statuses</option>
                        <option value="unchecked" {{ request('verification_status') === 'unchecked' ? 'selected' : '' }}>Unchecked Queue</option>
                        <option value="pending_verification" {{ request('verification_status') === 'pending_verification' ? 'selected' : '' }}>Pending Verification</option>
                        <option value="checked" {{ request('verification_status') === 'checked' ? 'selected' : '' }}>Admin Checked (Unpaid)</option>
                        <option value="approved" {{ request('verification_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="paid" {{ request('verification_status') === 'paid' ? 'selected' : '' }}>Paid / Received</option>
                        <option value="partially_paid" {{ request('verification_status') === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="unpaid" {{ request('verification_status') === 'unpaid' ? 'selected' : '' }}>All Unpaid</option>
                        <option value="unmatched" {{ request('verification_status') === 'unmatched' ? 'selected' : '' }}>Unmatched</option>
                        <option value="fake" {{ request('verification_status') === 'fake' ? 'selected' : '' }}>Fake / Rejected</option>
                        <option value="under_review" {{ request('verification_status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                        <option value="refunded" {{ request('verification_status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        <option value="duplicate" {{ request('verification_status') === 'duplicate' ? 'selected' : '' }}>Duplicate Flagged</option>
                    </select>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <label class="txn-filter-label" for="filter_date_preset">Date Range Preset</label>
                    <select name="date_preset" id="filter_date_preset" class="form-select shadow-none" onchange="handleDatePresetChange(this.value)">
                        <option value="all">All Dates</option>
                        <option value="today" {{ request('date_preset') === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ request('date_preset') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="last_3_days" {{ request('date_preset') === 'last_3_days' ? 'selected' : '' }}>Last 3 Days</option>
                        <option value="this_week" {{ request('date_preset') === 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="this_month" {{ request('date_preset') === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ request('date_preset') === 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="custom" {{ request('date_preset') === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                    </select>
                </div>

                <div class="col-6 col-md-2 col-lg-1">
                    <label class="txn-filter-label" for="filter_per_page">Rows</label>
                    <select name="per_page" id="filter_per_page" class="form-select shadow-none fw-bold bn-digit font-monospace">
                        <option value="20" {{ ($perPage ?? 20) == 20 ? 'selected' : '' }}>20</option>
                        <option value="40" {{ ($perPage ?? 20) == 40 ? 'selected' : '' }}>40</option>
                        <option value="60" {{ ($perPage ?? 20) == 60 ? 'selected' : '' }}>60</option>
                        <option value="100" {{ ($perPage ?? 20) == 100 ? 'selected' : '' }}>100</option>
                        <option value="200" {{ ($perPage ?? 20) == 200 ? 'selected' : '' }}>200</option>
                        <option value="500" {{ ($perPage ?? 20) == 500 ? 'selected' : '' }}>500</option>
                        <option value="all" {{ request('per_page') === 'all' ? 'selected' : '' }}>All</option>
                    </select>
                </div>

                <div class="col-12 col-md-6 col-lg-2 d-flex gap-1">
                    <button type="button" class="btn btn-outline-secondary btn-compact w-100 gap-1" style="height: 32px;" data-bs-toggle="collapse" data-bs-target="#moreTxnFiltersDrawer" aria-expanded="false">
                        <i class="fas fa-sliders me-1"></i> <span>More Filters</span>
                    </button>
                    <button type="submit" class="btn btn-primary btn-compact px-3" style="height: 32px;" title="Apply Filter">
                        <i class="fas fa-filter"></i>
                    </button>
                    <button type="button" class="btn btn-light btn-compact border px-3 text-secondary" style="height: 32px;" onclick="resetTxnFilters()" title="Reset All Filters">
                        <i class="fas fa-rotate-left"></i>
                    </button>
                </div>
            </div>

            <!-- Collapsible Advanced Filters -->
            <div class="collapse mt-2 pt-3 border-top" id="moreTxnFiltersDrawer">
                <div class="row g-2 align-items-end">
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="txn-filter-label" for="filter_sender">Sender Number</label>
                        <input type="text" name="sender_number" id="filter_sender" class="form-control shadow-none font-monospace bn-digit" value="{{ request('sender_number') }}" placeholder="01XXXXXXXXX...">
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="txn-filter-label" for="filter_payment_method">Payment Method</label>
                        <select name="payment_method" id="filter_payment_method" class="form-select shadow-none">
                            <option value="all">All Methods</option>
                            @foreach($availableMethods as $pm)
                                <option value="{{ $pm }}" {{ request('payment_method') == $pm ? 'selected' : '' }}>{{ $pm }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="txn-filter-label" for="filter_source_type">Transaction Source</label>
                        <select name="source_type" id="filter_source_type" class="form-select shadow-none">
                            <option value="all">All Sources</option>
                            <option value="checkout">Checkout Request</option>
                            <option value="pos_cash">POS Counter</option>
                            <option value="cod">COD Order</option>
                            <option value="manual">Manual Entry</option>
                            <option value="gateway">Online Gateway</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="txn-filter-label" for="filter_order_status">Order Status</label>
                        <select name="order_status" id="filter_order_status" class="form-select shadow-none">
                            <option value="all">All Order Status</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="returned">Returned</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="txn-filter-label" for="filter_checked_by">Checked / Verified By</label>
                        <select name="checked_by" id="filter_checked_by" class="form-select shadow-none">
                            <option value="all">Any Admin / Staff</option>
                            @foreach($staffUsers as $stf)
                                <option value="{{ $stf->id }}">{{ $stf->name }} ({{ ucfirst($stf->role) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-1">
                        <label class="txn-filter-label" for="filter_month">Month</label>
                        <select name="filter_month" id="filter_month" class="form-select shadow-none">
                            <option value="all">All</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">{{ date('M', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-1">
                        <label class="txn-filter-label" for="filter_year">Year</label>
                        <select name="filter_year" id="filter_year" class="form-select shadow-none bn-digit font-monospace">
                            <option value="all">All</option>
                            @for($y = date('Y'); $y >= 2023; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="txn-filter-label" for="filter_start_date">Date From</label>
                        <input type="date" name="start_date" id="filter_start_date" class="form-control shadow-none" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="txn-filter-label" for="filter_end_date">Date To</label>
                        <input type="date" name="end_date" id="filter_end_date" class="form-control shadow-none" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="txn-filter-label" for="filter_verification_date">Verification Date</label>
                        <input type="date" name="verification_date" id="filter_verification_date" class="form-control shadow-none" value="{{ request('verification_date') }}">
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="txn-filter-label" for="filter_min_amount">Min Amount (৳)</label>
                        <input type="number" name="min_amount" id="filter_min_amount" class="form-control shadow-none bn-digit font-monospace" placeholder="0">
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="txn-filter-label" for="filter_max_amount">Max Amount (৳)</label>
                        <input type="number" name="max_amount" id="filter_max_amount" class="form-control shadow-none bn-digit font-monospace" placeholder="Any">
                    </div>
                    <div class="col-12 col-md-6 col-lg-2">
                        <label class="txn-filter-label" for="filter_branch_id">Branch Hub</label>
                        <select name="branch_id" id="filter_branch_id" class="form-select shadow-none">
                            <option value="all">All Branches &amp; Online</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- 5. Floating Bulk Selection Action Bar -->
    <div class="txn-bulk-bar" id="txnBulkActionBar">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark px-2 py-1 fw-bold bn-digit font-monospace" id="bulkSelectedCountBadge">0</span>
            <span class="fw-semibold" style="font-size: 12px;">Transaction(s) Selected</span>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if($activeSection === 'trash')
                <button type="button" class="btn btn-compact btn-success" onclick="executeBulkTxnAction('restore')">
                    <i class="fas fa-trash-arrow-up me-1"></i> Restore Selected
                </button>
                <button type="button" class="btn btn-compact btn-danger" onclick="executeBulkTxnAction('force_delete')">
                    <i class="fas fa-skull-crossbones me-1"></i> Delete Permanently
                </button>
            @else
                <button type="button" class="btn btn-compact btn-info text-white" onclick="executeBulkTxnAction('mark_checked')">
                    <i class="fas fa-eye me-1"></i> Mark Checked
                </button>
                <button type="button" class="btn btn-compact btn-primary" onclick="executeBulkTxnAction('approve')">
                    <i class="fas fa-thumbs-up me-1"></i> Approve
                </button>
                <button type="button" class="btn btn-compact btn-success" onclick="executeBulkTxnAction('mark_paid')">
                    <i class="fas fa-circle-check me-1"></i> Mark Paid / Received
                </button>
                <button type="button" class="btn btn-compact btn-danger" onclick="executeBulkTxnAction('trash')">
                    <i class="fas fa-trash-can me-1"></i> Move to Trash
                </button>
            @endif
            <button type="button" class="btn btn-compact btn-outline-light" onclick="clearAllBulkSelections()">
                Cancel
            </button>
        </div>
    </div>

    <!-- 6. Main Data Table Card (Desktop 100% Fit, Mobile Smooth Scroll) -->
    <div class="txn-card position-relative overflow-hidden">
        <div id="txnLoadingOverlay">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table custom-txn-table align-middle mb-0" id="txnMainTable">
                <thead>
                    @if($activeSection === 'history')
                        <tr>
                            <th class="ps-3 col-w-sl">#</th>
                            <th style="min-width: 140px;">Timestamp &amp; IP</th>
                            <th style="min-width: 160px;">Transaction &amp; Order</th>
                            <th style="min-width: 150px;">Customer &amp; Method</th>
                            <th style="min-width: 150px;">Action &amp; Status Change</th>
                            <th style="min-width: 130px;">Amount</th>
                            <th style="min-width: 130px;">Performed By</th>
                            <th class="pe-3" style="min-width: 160px;">Reason / Remarks</th>
                        </tr>
                    @else
                        <tr>
                            <th class="ps-3 text-center col-w-check">
                                <input type="checkbox" class="form-check-input shadow-none m-0" id="txnSelectAllCheckbox" onchange="toggleSelectAllTransactions(this)" style="cursor: pointer; width: 14px; height: 14px; border-radius: 5px !important;">
                            </th>
                            <th class="col-w-sl">#</th>
                            <th class="col-w-txn">Transaction &amp; Order</th>
                            <th class="col-w-cust">Customer Info</th>
                            <th class="col-w-method">Method, TrxID &amp; Proof</th>
                            <th class="col-w-fin">Financial Breakdown</th>
                            <th class="text-center col-w-status">Verification &amp; Status</th>
                            <th class="col-w-audit">Checked / Verified By</th>
                            <th class="text-end pe-3 col-w-actions">Actions</th>
                        </tr>
                    @endif
                </thead>
                <tbody id="txnTableTbody">
                    @if($activeSection === 'history')
                        @include('admin.transactions.partials.history_rows', ['histories' => $histories ?? $transactions])
                    @else
                        @include('admin.transactions.partials.table_rows', ['transactions' => $transactions, 'activeSection' => $activeSection])
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer (Fixed: Always visible on Mobile & Compact Desktop) -->
        <div class="p-2 px-3 border-top d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50">
            <span class="text-muted fw-semibold bn-digit font-monospace" style="font-size: 11px;" id="txnPaginationStats">
                Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} record(s)
            </span>
            <div id="txnPaginationLinks" class="w-100 w-md-auto d-flex justify-content-center justify-content-md-end py-1">
                {{ $transactions->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- MODAL 1: FULL TRANSACTION VERIFICATION & DETAILS MODAL -->
<div class="modal fade txn-modal-scope" id="txnDetailModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light py-2 px-3 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-shield-halved text-primary fs-5"></i>
                    <div>
                        <h6 class="fw-bold text-dark mb-0 font-monospace bn-digit" style="font-size: 14px;" id="mdl_txn_uid">TXN-000000</h6>
                        <small class="text-muted bn-digit font-monospace" style="font-size: 11px;" id="mdl_txn_submitted_at">Submitted: —</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-3">
                <div id="mdl_duplicate_alert" class="alert alert-danger d-none py-2 px-3 mb-3 border-0 shadow-xs">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-triangle-exclamation fs-5 text-danger"></i>
                        <div>
                            <strong class="d-block" style="font-size: 12.5px;">Security Alert: Duplicate Transaction ID Detected!</strong>
                            <span style="font-size: 12px;" id="mdl_duplicate_text"></span>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-lg-7">
                        <div class="row g-2 mb-3">
                            <div class="col-12 col-md-6">
                                <div class="p-3 border bg-light bg-opacity-50 h-100" style="border-radius: 5px;">
                                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-2" style="font-size: 12.5px;">
                                        <i class="fas fa-user text-primary me-1"></i> Customer Information
                                    </h6>
                                    <div class="mb-1" style="font-size: 12px;"><span class="text-muted">Name:</span> <strong id="mdl_cust_name">—</strong></div>
                                    <div class="mb-1" style="font-size: 12px;"><span class="text-muted">Phone:</span> <strong class="font-monospace bn-digit" id="mdl_cust_phone">—</strong></div>
                                    <div class="mb-1" style="font-size: 12px;"><span class="text-muted">Email:</span> <span class="font-monospace" id="mdl_cust_email">—</span></div>
                                    <div style="font-size: 12px;"><span class="text-muted">Branch Hub:</span> <span id="mdl_branch_name">—</span></div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="p-3 border bg-light bg-opacity-50 h-100" style="border-radius: 5px;">
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                        <h6 class="fw-bold text-dark mb-0" style="font-size: 12.5px;">
                                            <i class="fas fa-shopping-bag text-success me-1"></i> Linked Order Details
                                        </h6>
                                        <a href="#" target="_blank" id="mdl_order_link" class="btn btn-compact btn-outline-primary py-0 px-2 d-none" style="font-size: 10.5px !important;">
                                            Open Order <i class="fas fa-external-link-alt ms-1"></i>
                                        </a>
                                    </div>
                                    <div class="mb-1" style="font-size: 12px;"><span class="text-muted">Invoice / Order:</span> <strong class="font-monospace bn-digit" id="mdl_order_inv">—</strong></div>
                                    <div class="mb-1" style="font-size: 12px;"><span class="text-muted">Order Status:</span> <span class="badge bg-secondary" id="mdl_order_status">—</span></div>
                                    <div class="mb-1" style="font-size: 12px;"><span class="text-muted">Order Payment:</span> <span class="badge bg-secondary" id="mdl_order_pay_status">—</span></div>
                                    <div style="font-size: 12px;"><span class="text-muted">Order Due:</span> <strong class="text-danger font-monospace bn-digit" id="mdl_order_due">৳0.00</strong></div>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 border mb-3" style="border-radius: 5px;">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3" style="font-size: 13px;">
                                <i class="fas fa-file-invoice-dollar text-warning me-1"></i> Payment &amp; Verification Breakdown
                            </h6>
                            <div class="row g-2" style="font-size: 12px;">
                                <div class="col-6 col-sm-4">
                                    <span class="text-muted d-block mb-1">Source Type:</span>
                                    <strong id="mdl_source_label">—</strong>
                                </div>
                                <div class="col-6 col-sm-4">
                                    <span class="text-muted d-block mb-1">Payment Method:</span>
                                    <strong class="text-primary text-uppercase font-monospace" id="mdl_pay_method">—</strong>
                                </div>
                                <div class="col-6 col-sm-4">
                                    <span class="text-muted d-block mb-1">Current Status:</span>
                                    <span class="badge bg-warning text-dark px-2 py-1 font-monospace" id="mdl_ver_status">—</span>
                                </div>
                                <div class="col-6 col-sm-4">
                                    <span class="text-muted d-block mb-1">Sender Number:</span>
                                    <strong class="font-monospace text-dark bn-digit" id="mdl_sender_no">—</strong>
                                </div>
                                <div class="col-12 col-sm-8">
                                    <span class="text-muted d-block mb-1">Transaction ID (TrxID):</span>
                                    <strong class="font-monospace text-primary bn-digit" style="font-size: 13.5px;" id="mdl_trx_id">—</strong>
                                </div>
                            </div>

                            <hr class="my-3">

                            <div class="row g-2 text-center">
                                <div class="col-6 col-sm-3">
                                    <div class="p-2 bg-light border" style="border-radius: 5px;">
                                        <small class="text-muted d-block fw-bold" style="font-size: 10px;">EXPECTED PAYABLE</small>
                                        <strong class="font-monospace text-dark bn-digit" id="mdl_expected_amt">৳0.00</strong>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <div class="p-2 bg-light border" style="border-radius: 5px;">
                                        <small class="text-muted d-block fw-bold" style="font-size: 10px;">SUBMITTED CLAIM</small>
                                        <strong class="font-monospace text-primary bn-digit" id="mdl_submitted_amt">৳0.00</strong>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <div class="p-2 bg-light border" style="border-radius: 5px;">
                                        <small class="text-muted d-block fw-bold" style="font-size: 10px;">VERIFIED PAID</small>
                                        <strong class="font-monospace text-success bn-digit" id="mdl_verified_amt">৳0.00</strong>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <div class="p-2 bg-light border" style="border-radius: 5px;">
                                        <small class="text-muted d-block fw-bold" style="font-size: 10px;">REFUNDED</small>
                                        <strong class="font-monospace text-danger bn-digit" id="mdl_refunded_amt">৳0.00</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mt-2 pt-2 border-top" style="font-size: 12px;">
                                <div class="col-6">
                                    <span class="text-muted">Checked By:</span> <strong id="mdl_checked_by">—</strong>
                                    <small class="text-muted d-block bn-digit font-monospace" id="mdl_checked_at"></small>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">Verified / Paid By:</span> <strong class="text-success" id="mdl_verified_by">—</strong>
                                    <small class="text-muted d-block bn-digit font-monospace" id="mdl_verified_at"></small>
                                </div>
                                <div class="col-12">
                                    <span class="text-muted fw-semibold">Admin Remarks / Rejection Reason:</span>
                                    <div class="p-2 bg-light border mt-1 text-dark" id="mdl_remarks" style="white-space: pre-line; border-radius: 5px;">—</div>
                                </div>
                            </div>
                        </div>

                        <!-- Verification Action Controls -->
                        <div class="p-3 border bg-light" style="border-radius: 5px;">
                            <h6 class="fw-bold text-dark mb-1" style="font-size: 13px;">
                                <i class="fas fa-sliders text-primary me-1"></i> Execute Verification Action
                            </h6>
                            <p class="text-muted mb-2" style="font-size: 11.5px;">
                                Marking an already Paid POS/Online record as <strong>Checked</strong> records your admin check without changing its Paid status.
                            </p>
                            <div class="d-flex flex-wrap gap-2" id="mdl_action_buttons_wrap">
                                <button type="button" class="btn btn-compact btn-info text-white" onclick="triggerModalVerifyAction('mark_checked')">
                                    <i class="fas fa-eye me-1"></i> Mark Admin Checked
                                </button>
                                <button type="button" class="btn btn-compact btn-primary" onclick="triggerModalVerifyAction('approve')">
                                    <i class="fas fa-thumbs-up me-1"></i> Approve
                                </button>
                                <button type="button" class="btn btn-compact btn-success" onclick="triggerModalVerifyAction('mark_paid')">
                                    <i class="fas fa-circle-check me-1"></i> Mark Paid / Received
                                </button>
                                <button type="button" class="btn btn-compact btn-outline-primary" onclick="triggerModalVerifyAction('mark_partially_paid')">
                                    <i class="fas fa-adjust me-1"></i> Partial Paid
                                </button>
                                <button type="button" class="btn btn-compact btn-warning text-dark" onclick="triggerModalVerifyAction('under_review')">
                                    <i class="fas fa-magnifying-glass me-1"></i> Under Review
                                </button>
                                <button type="button" class="btn btn-compact btn-danger" onclick="triggerModalVerifyAction('mark_unmatched')">
                                    <i class="fas fa-not-equal me-1"></i> Unmatched
                                </button>
                                <button type="button" class="btn btn-compact btn-dark" onclick="triggerModalVerifyAction('mark_fake')">
                                    <i class="fas fa-ban me-1 text-danger"></i> Fake / Reject
                                </button>
                                <button type="button" class="btn btn-compact btn-outline-secondary" onclick="triggerModalVerifyAction('refund')">
                                    <i class="fas fa-rotate-left me-1"></i> Refund
                                </button>
                                <button type="button" class="btn btn-compact btn-outline-dark" onclick="triggerModalVerifyAction('add_note')">
                                    <i class="fas fa-comment-medical me-1"></i> Add Note
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Media Preview & Order History Timeline -->
                    <div class="col-12 col-lg-5">
                        <div class="p-3 border mb-3 text-center bg-light" style="border-radius: 5px;">
                            <h6 class="fw-bold text-dark text-start border-bottom pb-2 mb-2" style="font-size: 12.5px;">
                                <i class="fas fa-image text-primary me-1"></i> Payment Screenshot Proof
                            </h6>
                            <div id="mdl_screenshot_wrap">
                                <img id="mdl_screenshot_img" src="" alt="Payment Proof" class="img-fluid border shadow-xs d-none" style="max-height: 220px; border-radius: 5px; cursor: zoom-in;" onclick="window.open(this.src, '_blank')">
                                <div id="mdl_no_screenshot" class="py-4 text-muted" style="font-size: 12px;">
                                    <i class="far fa-file-image fa-2x mb-2 d-block opacity-50"></i>
                                    No screenshot uploaded for this transaction.
                                </div>
                            </div>
                        </div>

                        <div class="p-3 border mb-3" style="border-radius: 5px;">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-2" style="font-size: 12.5px;">
                                <i class="fas fa-receipt text-secondary me-1"></i> Previous / Trashed Records for This Order
                            </h6>
                            <div id="mdl_order_history_list" style="max-height: 140px; overflow-y: auto; font-size: 12px;"></div>
                        </div>

                        <div class="p-3 border" style="border-radius: 5px;">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-2" style="font-size: 12.5px;">
                                <i class="fas fa-clock-rotate-left text-info me-1"></i> Verification Audit History
                            </h6>
                            <div id="mdl_audit_timeline" style="max-height: 220px; overflow-y: auto; font-size: 12px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 2: VERIFICATION CONFIRMATION & REASON INPUT MODAL -->
<div class="modal fade txn-modal-scope" id="txnActionConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 1075;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg p-3">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                <h6 class="fw-bold text-dark mb-0" style="font-size: 14.5px;" id="actModalTitle">Confirm Action</h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <input type="hidden" id="act_txn_id">
            <input type="hidden" id="act_type">

            <p class="text-muted mb-3 bn-digit font-monospace" style="font-size: 12.5px;" id="actModalSubtitle"></p>

            <div class="mb-3 d-none" id="actAmountWrap">
                <label class="form-label fw-bold text-dark mb-1" style="font-size: 12px;" id="actAmountLabel">Verified Received Amount (৳) *</label>
                <input type="number" step="0.01" id="act_verified_amount" class="form-control font-monospace fw-bold bn-digit">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold text-dark mb-1" style="font-size: 12px;" id="actReasonLabel">Reason / Admin Note</label>
                <textarea id="act_reason" rows="3" class="form-control" placeholder="Write verification note or reason..."></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-compact px-3 shadow-xs" id="btnConfirmVerifySubmit" onclick="submitVerificationActionAjax()">
                    Confirm &amp; Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 3: ADD MANUAL TRANSACTION MODAL (WITH 200% GLOBAL MEDIA CHOOSER) -->
<div class="modal fade txn-modal-scope" id="manualTxnModal" tabindex="-1" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light py-2 px-3 border-bottom">
                <div>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">
                        <i class="fas fa-hand-holding-dollar text-primary me-1"></i> Record Manual Payment Transaction
                    </h6>
                    <small class="text-muted d-block" style="font-size: 11.5px;">
                        Only recorded Online Orders can be linked. Any previous transaction for the selected order will automatically move to Trash.
                    </small>
                </div>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <form id="manualTxnForm" onsubmit="event.preventDefault(); submitManualTransactionAjax();" enctype="multipart/form-data">
                <input type="hidden" name="screenshot_media" id="manual_screenshot_media" value="">
                <div class="modal-body p-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark mb-1" style="font-size: 12px;">
                                Select Recorded Online Order <span class="text-danger">* (Mandatory — Click or Type Invoice# / Phone)</span>
                            </label>
                            <div class="position-relative">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="fas fa-magnifying-glass text-primary"></i></span>
                                    <input type="text" id="manual_order_search" class="form-control shadow-none" placeholder="Click here or type Invoice No, Order ID, or Customer Phone..." autocomplete="off" required>
                                </div>
                                <input type="hidden" name="order_id" id="manual_order_id">
                                <input type="hidden" name="invoice_no" id="manual_invoice_no">
                                <div id="manualOrderSearchResults" class="list-group position-absolute w-100 shadow-lg border d-none mt-1" style="z-index: 1095; max-height: 220px; overflow-y: auto; border-radius: 5px;"></div>
                            </div>

                            <div class="alert alert-success py-2 px-3 mt-2 mb-0 d-none" id="manualLinkedOrderBadge" style="font-size: 12px; border-radius: 5px;"></div>
                            <div class="alert alert-warning py-2 px-3 mt-2 mb-0 d-none" id="manualExistingTxnWarning" style="font-size: 12px; border-radius: 5px;"></div>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold text-dark mb-1" style="font-size: 12px;">Active Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select form-select-sm shadow-none" required>
                                @foreach($gatewayOptions as $pm)
                                    <option value="{{ $pm }}">{{ $pm }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold text-dark mb-1" style="font-size: 12px;">Received Amount (৳) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" id="manual_amount" class="form-control form-control-sm shadow-none font-monospace fw-bold bn-digit" required placeholder="0.00">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold text-dark mb-1" style="font-size: 12px;">Verification Status <span class="text-danger">*</span></label>
                            <select name="verification_status" class="form-select form-select-sm shadow-none" required>
                                <option value="paid">Paid / Received (Verified)</option>
                                <option value="partially_paid">Partially Paid</option>
                                <option value="approved">Approved</option>
                                <option value="checked">Admin Checked</option>
                                <option value="pending_verification">Pending Verification</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-dark mb-1" style="font-size: 12px;">Transaction ID (TrxID / Reference)</label>
                            <input type="text" name="transaction_id" class="form-control form-control-sm shadow-none font-monospace bn-digit" placeholder="Unique TrxID (Duplicate protected)">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-dark mb-1" style="font-size: 12px;">Sender Number / Account</label>
                            <input type="text" name="sender_number" class="form-control form-control-sm shadow-none font-monospace bn-digit" placeholder="01XXXXXXXXX">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold text-dark mb-1" style="font-size: 12px;">Customer Name</label>
                            <input type="text" name="customer_name" id="manual_customer_name" class="form-control form-control-sm shadow-none" placeholder="Auto-filled from order">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold text-dark mb-1" style="font-size: 12px;">Customer Phone</label>
                            <input type="text" name="customer_phone" id="manual_customer_phone" class="form-control form-control-sm shadow-none font-monospace bn-digit" placeholder="Auto-filled from order">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold text-dark mb-1" style="font-size: 12px;">Customer Email</label>
                            <input type="email" name="customer_email" id="manual_customer_email" class="form-control form-control-sm shadow-none" placeholder="Optional email">
                        </div>

                        <!-- 200% Modern Global Media Chooser Component for Payment Proof Slip -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-dark mb-1 d-flex justify-content-between align-items-center" style="font-size: 12px;">
                                <span><i class="fas fa-paperclip text-primary me-1"></i> Payment Receipt Slip (Media Chooser):</span>
                                <span class="badge bg-light text-muted border font-monospace" style="font-size: 9px;">GLOBAL MEDIA</span>
                            </label>
                            <div class="proof-media-surface">
                                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                    <button type="button" class="btn btn-primary btn-compact shadow-xs" onclick="chooseManualTxnProofMedia()">
                                        <i class="fas fa-photo-film me-1"></i>
                                        <span>Choose from Media Library</span>
                                    </button>
                                    <span class="small text-muted" style="font-size: 10.5px;">or upload directly:</span>
                                </div>
                                <input type="file" name="screenshot_file" id="manual_screenshot_file" class="form-control form-control-sm shadow-none" accept="image/*" onchange="previewManualTxnLocalProof(this)">
                                
                                <div id="manual_proof_preview_wrap" class="mt-2 d-none align-items-center gap-2 bg-white border p-2" style="border-radius: 5px;">
                                    <div class="proof-thumb-box" onclick="zoomManualProof()" title="Click to Zoom Slip">
                                        <img id="manual_proof_preview_img" src="" alt="Proof Preview">
                                        <div class="proof-thumb-hover-overlay">
                                            <i class="fas fa-search-plus text-white" style="font-size: 11px;"></i>
                                        </div>
                                    </div>
                                    <div class="min-width-0 flex-grow-1">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 9.5px;">
                                            <i class="fas fa-check-circle me-1"></i>Slip Attached
                                        </span>
                                        <small class="text-muted d-block text-truncate font-monospace" id="manual_proof_preview_txt" style="font-size: 9.5px;"></small>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger btn-compact py-0.5 px-2" style="font-size: 10px !important;" onclick="clearManualTxnProof()">
                                        <i class="fas fa-trash-alt me-1"></i>Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-dark mb-1" style="font-size: 12px;">Remarks / Note</label>
                            <textarea name="remarks" rows="3" class="form-control form-control-sm shadow-none" placeholder="Enter payment reference note..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light">
                    <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-compact px-3 shadow-xs" id="btnSaveManualTxn">
                        <i class="fas fa-check-circle me-1"></i> Save Manual Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 4: SCREENSHOT PREVIEW & NOTICE MODALS -->
<div class="modal fade txn-modal-scope" id="screenshotPreviewModal" tabindex="-1" aria-hidden="true" style="z-index: 1080;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header py-2 px-3 bg-dark text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold" style="font-size: 13.5px;" id="ssPreviewTitle">Payment Proof</h6>
                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2 text-center bg-light">
                <img id="ssPreviewImage" src="" alt="Proof" class="img-fluid border" style="max-height: 75vh; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<div class="modal fade txn-modal-scope" id="txnUiNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 1095;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="mb-2" id="txnNoticeIcon"><i class="fas fa-circle-check text-success fa-2x"></i></div>
            <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;" id="txnNoticeTitle">Notification</h6>
            <p class="text-muted mb-3" style="font-size: 12px; line-height: 1.45;" id="txnNoticeMessage"></p>
            <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
        </div>
    </div>
</div>

<script>
    const CSRF_TOKEN = "{{ csrf_token() }}";
    let txnFilterDebounce = null;
    let activeModalTxnData = null;

    function extractMediaItem(mediaInput) {
        if (!mediaInput) return null;
        const item = Array.isArray(mediaInput) ? mediaInput[0] : mediaInput;
        if (!item || typeof item !== 'object') return null;
        const rawPath = item.file_path || item.path || item.url || '';
        const rawUrl  = item.url || item.file_path || item.path || '';
        if (!rawPath || rawPath === 'undefined') return null;
        return { path: String(rawPath).trim(), url: String(rawUrl).trim() };
    }

    // Global Media Chooser Integration for Manual Payment Transaction Slip
    function chooseManualTxnProofMedia() {
        if (typeof window.openMediaPicker === 'function') {
            window.openMediaPicker({
                multiple: false,
                onSelect: function(media) {
                    const picked = extractMediaItem(media);
                    if (picked && picked.path) {
                        document.getElementById('manual_screenshot_media').value = picked.path;
                        const fileEl = document.getElementById('manual_screenshot_file');
                        if (fileEl) fileEl.value = '';
                        const wrap = document.getElementById('manual_proof_preview_wrap');
                        const img  = document.getElementById('manual_proof_preview_img');
                        const txt  = document.getElementById('manual_proof_preview_txt');
                        if (img) img.src = picked.url;
                        if (txt) txt.innerText = picked.path.split('/').pop() || picked.path;
                        if (wrap) {
                            wrap.classList.remove('d-none');
                            wrap.classList.add('d-flex');
                        }
                        showTxnNotice('Proof Attached', 'Payment receipt slip attached from Global Media Library.', 'success');
                    }
                }
            });
        } else {
            showTxnNotice('Notice', 'Global Media Library is loading, please try again.', 'warning');
        }
    }

    function previewManualTxnLocalProof(input) {
        if (input.files && input.files[0]) {
            document.getElementById('manual_screenshot_media').value = '';
            const reader = new FileReader();
            reader.onload = function(e) {
                const wrap = document.getElementById('manual_proof_preview_wrap');
                const img  = document.getElementById('manual_proof_preview_img');
                const txt  = document.getElementById('manual_proof_preview_txt');
                if (img) img.src = e.target.result;
                if (txt) txt.innerText = input.files[0].name;
                if (wrap) {
                    wrap.classList.remove('d-none');
                    wrap.classList.add('d-flex');
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function clearManualTxnProof() {
        document.getElementById('manual_screenshot_media').value = '';
        const fileEl = document.getElementById('manual_screenshot_file');
        if (fileEl) fileEl.value = '';
        const wrap = document.getElementById('manual_proof_preview_wrap');
        if (wrap) {
            wrap.classList.add('d-none');
            wrap.classList.remove('d-flex');
        }
    }

    function zoomManualProof() {
        const img = document.getElementById('manual_proof_preview_img');
        if (img && img.src) {
            openScreenshotPreviewModal(img.src, 'Manual Slip Preview');
        }
    }

    function showTxnNotice(title, message, type = 'success') {
        document.getElementById('txnNoticeTitle').innerText = title;
        document.getElementById('txnNoticeMessage').innerText = message;
        const iconWrap = document.getElementById('txnNoticeIcon');
        if (type === 'danger' || type === 'error') {
            iconWrap.innerHTML = '<i class="fas fa-triangle-exclamation text-danger fa-2x"></i>';
        } else if (type === 'warning') {
            iconWrap.innerHTML = '<i class="fas fa-circle-exclamation text-warning fa-2x"></i>';
        } else {
            iconWrap.innerHTML = '<i class="fas fa-circle-check text-success fa-2x"></i>';
        }
        bootstrap.Modal.getOrCreateInstance(document.getElementById('txnUiNoticeModal')).show();
    }

    document.addEventListener('DOMContentLoaded', function () {
        ['filter_search', 'filter_trx_id', 'filter_sender', 'filter_min_amount', 'filter_max_amount'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', function () {
                    clearTimeout(txnFilterDebounce);
                    txnFilterDebounce = setTimeout(() => triggerTxnAjaxFilter(1), 350);
                });
            }
        });

        [
            'filter_verification_status', 'filter_per_page', 'filter_payment_method',
            'filter_source_type', 'filter_order_status', 'filter_checked_by',
            'filter_month', 'filter_year', 'filter_start_date', 'filter_end_date',
            'filter_verification_date', 'filter_branch_id'
        ].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('change', () => triggerTxnAjaxFilter(1));
            }
        });

        document.addEventListener('click', function (e) {
            const pageLink = e.target.closest('#txnPaginationLinks a');
            if (pageLink) {
                e.preventDefault();
                const href = pageLink.getAttribute('href');
                if (href && href !== '#') {
                    fetchTransactionsByUrl(href);
                }
            }

            const searchWrap = e.target.closest('#manual_order_search, #manualOrderSearchResults');
            if (!searchWrap) {
                document.getElementById('manualOrderSearchResults')?.classList.add('d-none');
            }
        });

        const orderSearchInput = document.getElementById('manual_order_search');
        if (orderSearchInput) {
            let orderSearchTimer = null;
            const loadOrderList = (q) => {
                const resBox = document.getElementById('manualOrderSearchResults');
                fetch("{{ route('admin.transactions.search_order') }}?q=" + encodeURIComponent(q), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(list => {
                    resBox.innerHTML = '';
                    if (!list.length) {
                        resBox.innerHTML = '<div class="list-group-item text-danger small py-2">No online order found matching query. Please create an order first.</div>';
                        resBox.classList.remove('d-none');
                        return;
                    }
                    list.forEach(item => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'list-group-item list-group-item-action py-2 px-3';
                        btn.innerHTML = `<div class="d-flex justify-content-between align-items-center">
                            <strong class="text-dark" style="font-size:12.5px;">#${item.invoice_no} — ${item.customer_name}</strong>
                            <span class="badge ${item.has_existing_txn ? 'bg-warning text-dark' : 'bg-primary'} font-monospace bn-digit">Payable: ৳${item.grand_total}</span>
                        </div>
                        <div class="d-flex justify-content-between text-muted mt-1" style="font-size:11px;">
                            <span class="font-monospace bn-digit">Phone: ${item.customer_phone || 'N/A'} | Status: ${item.payment_status}</span>
                            ${item.has_existing_txn ? `<span class="text-danger fw-semibold font-monospace"><i class="fas fa-rotate me-1"></i>Active Txn: ${item.existing_txn_uid}</span>` : '<span class="text-success fw-semibold">No Active Txn</span>'}
                        </div>`;
                        btn.onclick = () => {
                            document.getElementById('manual_order_id').value = item.id;
                            document.getElementById('manual_invoice_no').value = item.invoice_no;
                            document.getElementById('manual_order_search').value = `#${item.invoice_no} — ${item.customer_name} (${item.customer_phone})`;
                            document.getElementById('manual_customer_name').value = item.customer_name;
                            document.getElementById('manual_customer_phone').value = item.customer_phone;
                            document.getElementById('manual_customer_email').value = item.customer_email || '';
                            document.getElementById('manual_amount').value = parseFloat(item.due_amount) > 0 ? item.due_amount : item.grand_total;

                            const badge = document.getElementById('manualLinkedOrderBadge');
                            badge.innerHTML = `<i class="fas fa-circle-check me-1"></i> Linked to Online Order <strong>#${item.invoice_no}</strong> (Order Total: ৳${item.grand_total})`;
                            badge.classList.remove('d-none');

                            const warn = document.getElementById('manualExistingTxnWarning');
                            if (item.has_existing_txn) {
                                warn.innerHTML = `<i class="fas fa-triangle-exclamation me-1"></i> <strong>Warning:</strong> This order already has an active transaction <strong>(${item.existing_txn_uid} - ${item.existing_txn_status})</strong>. Saving a new one will automatically move previous record to Trash to avoid duplication.`;
                                warn.classList.remove('d-none');
                            } else {
                                warn.classList.add('d-none');
                            }

                            resBox.classList.add('d-none');
                        };
                        resBox.appendChild(btn);
                    });
                    resBox.classList.remove('d-none');
                });
            };

            orderSearchInput.addEventListener('focus', function () {
                loadOrderList(this.value.trim());
            });

            orderSearchInput.addEventListener('input', function () {
                document.getElementById('manual_order_id').value = '';
                document.getElementById('manual_invoice_no').value = '';
                document.getElementById('manualLinkedOrderBadge').classList.add('d-none');
                document.getElementById('manualExistingTxnWarning').classList.add('d-none');
                clearTimeout(orderSearchTimer);
                orderSearchTimer = setTimeout(() => loadOrderList(this.value.trim()), 280);
            });
        }
    });

    function toggleSelectAllTransactions(masterCheckbox) {
        document.querySelectorAll('.txn-row-checkbox').forEach(cb => {
            cb.checked = masterCheckbox.checked;
        });
        handleRowCheckboxChange();
    }

    function handleRowCheckboxChange() {
        const checkedBoxes = document.querySelectorAll('.txn-row-checkbox:checked');
        const totalBoxes   = document.querySelectorAll('.txn-row-checkbox');
        const masterCb     = document.getElementById('txnSelectAllCheckbox');
        const bulkBar      = document.getElementById('txnBulkActionBar');
        const countBadge   = document.getElementById('bulkSelectedCountBadge');

        if (masterCb) {
            masterCb.checked = totalBoxes.length > 0 && checkedBoxes.length === totalBoxes.length;
        }
        if (checkedBoxes.length > 0) {
            countBadge.innerText = checkedBoxes.length;
            bulkBar.style.display = 'flex';
        } else {
            bulkBar.style.display = 'none';
        }
    }

    function clearAllBulkSelections() {
        const masterCb = document.getElementById('txnSelectAllCheckbox');
        if (masterCb) masterCb.checked = false;
        document.querySelectorAll('.txn-row-checkbox').forEach(cb => cb.checked = false);
        handleRowCheckboxChange();
    }

    function executeBulkTxnAction(actionType) {
        const ids = Array.from(document.querySelectorAll('.txn-row-checkbox:checked')).map(cb => parseInt(cb.value));
        if (!ids.length) return;

        fetch("{{ route('admin.transactions.bulk_action') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                ids: ids,
                action_type: actionType,
                reason: 'Bulk operation executed by admin'
            })
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw data;
            return data;
        })
        .then(data => {
            clearAllBulkSelections();
            if (data.alerts) updateSidebarAlertCounters(data.alerts);
            showTxnNotice('Bulk Action Completed', data.message, 'success');
            triggerTxnAjaxFilter(1);
        })
        .catch(err => {
            showTxnNotice('Bulk Action Failed', err.message || 'Could not process bulk action.', 'danger');
        });
    }

    function singleTrashOrDeleteAction(id, mode) {
        fetch("{{ url('admin/transactions/destroy') }}/" + id, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ mode: mode })
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw data;
            return data;
        })
        .then(data => {
            if (data.alerts) updateSidebarAlertCounters(data.alerts);
            showTxnNotice('Success', data.message, 'success');
            triggerTxnAjaxFilter(1);
        })
        .catch(err => {
            showTxnNotice('Error', err.message || 'Operation failed.', 'danger');
        });
    }

    function handleDatePresetChange(val) {
        if (val === 'custom') {
            const drawer = document.getElementById('moreTxnFiltersDrawer');
            if (drawer && !drawer.classList.contains('show')) {
                bootstrap.Collapse.getOrCreateInstance(drawer).show();
            }
        } else {
            document.getElementById('filter_start_date').value = '';
            document.getElementById('filter_end_date').value = '';
            triggerTxnAjaxFilter(1);
        }
    }

    function triggerTxnAjaxFilter(page = 1) {
        const form = document.getElementById('txnFilterForm');
        const params = new URLSearchParams(new FormData(form));
        if (page > 1) params.set('page', page);
        const url = window.location.pathname + '?' + params.toString();
        fetchTransactionsByUrl(url);
    }

    function fetchTransactionsByUrl(url) {
        const overlay = document.getElementById('txnLoadingOverlay');
        if (overlay) overlay.style.display = 'flex';

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                clearAllBulkSelections();
                document.getElementById('txnTableTbody').innerHTML = data.html;
                document.getElementById('txnPaginationLinks').innerHTML = data.pagination || '';
                document.getElementById('txnPaginationStats').innerText =
                    `Showing ${data.firstItem} to ${data.lastItem} of ${data.total} record(s)`;

                if (data.summary) updateSummaryCards(data.summary);
                if (data.alerts) updateSidebarAlertCounters(data.alerts);
                window.history.replaceState({}, '', url);
            }
        })
        .catch(err => {
            console.error('Filter error:', err);
            showTxnNotice('Request Error', 'Failed to load filtered transactions.', 'danger');
        })
        .finally(() => {
            if (overlay) overlay.style.display = 'none';
        });
    }

    function updateSummaryCards(s) {
        const formatNum = n => parseFloat(n || 0).toLocaleString('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('sum-total-count').innerText     = s.total_transactions ?? 0;
        document.getElementById('sum-expected-amt').innerText    = formatNum(s.total_expected_amount);
        document.getElementById('sum-paid-amt').innerText        = formatNum(s.total_paid_amount);
        document.getElementById('sum-paid-count').innerText      = s.count_paid ?? 0;
        document.getElementById('sum-unpaid-amt').innerText      = formatNum(s.total_unpaid_amount);
        document.getElementById('sum-partial-amt').innerText     = formatNum(s.total_partial_amount);
        document.getElementById('sum-pending-count').innerText   = s.count_pending ?? 0;
        document.getElementById('sum-review-count').innerText    = s.count_under_review ?? 0;
        document.getElementById('sum-checked-count').innerText   = s.count_checked ?? 0;
        document.getElementById('sum-approved-count').innerText  = s.count_approved ?? 0;
        document.getElementById('sum-fake-count').innerText      = s.count_fake ?? 0;
        document.getElementById('sum-unmatched-count').innerText = s.count_unmatched ?? 0;
        document.getElementById('sum-refunded-amt').innerText   = formatNum(s.total_refunded_amount);
    }

    function updateSidebarAlertCounters(alerts) {
        const navCheckout  = document.getElementById('nav-badge-checkout');
        const navUnchecked = document.getElementById('nav-badge-unchecked');
        const navTrash     = document.getElementById('nav-badge-trash');

        if (navCheckout) navCheckout.innerText   = alerts.pending_checkout_count ?? 0;
        if (navUnchecked) navUnchecked.innerText = alerts.total_unchecked_count ?? 0;
        if (navTrash) navTrash.innerText         = alerts.trashed_count ?? 0;

        const sbMain = document.getElementById('sidebar-unchecked-txn-badge');
        const sbSub  = document.getElementById('sidebar-sub-unchecked-badge');
        const sbChk  = document.getElementById('sidebar-checkout-txn-badge');

        if (sbMain) {
            sbMain.innerText = alerts.total_unchecked_count ?? 0;
            sbMain.classList.toggle('d-none', (alerts.total_unchecked_count ?? 0) <= 0);
        }
        if (sbSub) {
            sbSub.innerText = alerts.total_unchecked_count ?? 0;
            sbSub.classList.toggle('d-none', (alerts.total_unchecked_count ?? 0) <= 0);
        }
        if (sbChk) {
            sbChk.innerText = alerts.pending_checkout_count ?? 0;
            sbChk.classList.toggle('d-none', (alerts.pending_checkout_count ?? 0) <= 0);
        }
    }

    function resetTxnFilters() {
        const form = document.getElementById('txnFilterForm');
        form.reset();
        document.getElementById('filter_search').value = '';
        document.getElementById('filter_trx_id').value = '';
        document.getElementById('filter_sender').value = '';
        document.getElementById('filter_date_preset').value = 'all';
        document.getElementById('filter_verification_status').value = 'all';
        document.getElementById('filter_payment_method').value = 'all';
        document.getElementById('filter_source_type').value = 'all';
        document.getElementById('filter_order_status').value = 'all';
        document.getElementById('filter_checked_by').value = 'all';
        document.getElementById('filter_month').value = 'all';
        document.getElementById('filter_year').value = 'all';
        document.getElementById('filter_start_date').value = '';
        document.getElementById('filter_end_date').value = '';
        document.getElementById('filter_verification_date').value = '';
        document.getElementById('filter_branch_id').value = 'all';
        document.getElementById('filter_per_page').value = '20';
        triggerTxnAjaxFilter(1);
    }

    function openTransactionDetailModal(id) {
        fetch("{{ url('admin/transactions/show') }}/" + id, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(res => {
            if (!res.success) {
                showTxnNotice('Error', res.message || 'Could not load details.', 'danger');
                return;
            }
            const d = res.data;
            activeModalTxnData = d;

            document.getElementById('mdl_txn_uid').innerText = d.transaction_uid;
            document.getElementById('mdl_txn_submitted_at').innerText = 'Submitted: ' + d.submitted_at;

            const dupBox = document.getElementById('mdl_duplicate_alert');
            if (d.duplicate_info && d.duplicate_info.is_duplicate) {
                document.getElementById('mdl_duplicate_text').innerText = d.duplicate_info.warning_text || 'This Transaction ID is already used in another record.';
                dupBox.classList.remove('d-none');
            } else {
                dupBox.classList.add('d-none');
            }

            document.getElementById('mdl_cust_name').innerText   = d.customer_name;
            document.getElementById('mdl_cust_phone').innerText  = d.customer_phone;
            document.getElementById('mdl_cust_email').innerText  = d.customer_email;
            document.getElementById('mdl_branch_name').innerText = d.branch_name;

            const ordLink = document.getElementById('mdl_order_link');
            if (d.order_info) {
                document.getElementById('mdl_order_inv').innerText        = '#' + d.order_info.invoice_no;
                document.getElementById('mdl_order_status').innerText     = d.order_info.order_status;
                document.getElementById('mdl_order_pay_status').innerText = d.order_info.payment_status;
                document.getElementById('mdl_order_due').innerText        = '৳' + d.order_info.due_amount;
                if (d.order_view_url) {
                    ordLink.href = d.order_view_url;
                    ordLink.classList.remove('d-none');
                }
            } else {
                document.getElementById('mdl_order_inv').innerText        = 'Unlinked Record';
                document.getElementById('mdl_order_status').innerText     = 'N/A';
                document.getElementById('mdl_order_pay_status').innerText = 'N/A';
                document.getElementById('mdl_order_due').innerText        = '৳0.00';
                ordLink.classList.add('d-none');
            }

            document.getElementById('mdl_source_label').innerText  = d.source_label;
            document.getElementById('mdl_pay_method').innerText    = d.payment_method;
            document.getElementById('mdl_ver_status').innerText    = d.status_label;
            document.getElementById('mdl_sender_no').innerText     = d.sender_number;
            document.getElementById('mdl_trx_id').innerText        = d.transaction_id;
            document.getElementById('mdl_expected_amt').innerText  = '৳' + d.expected_amount;
            document.getElementById('mdl_submitted_amt').innerText = '৳' + d.submitted_amount;
            document.getElementById('mdl_verified_amt').innerText  = '৳' + d.verified_amount;
            document.getElementById('mdl_refunded_amt').innerText  = '৳' + d.refunded_amount;
            document.getElementById('mdl_checked_by').innerText    = d.checked_by_name;
            document.getElementById('mdl_checked_at').innerText    = d.checked_at;
            document.getElementById('mdl_verified_by').innerText   = d.verified_by_name;
            document.getElementById('mdl_verified_at').innerText   = d.verified_at;
            document.getElementById('mdl_remarks').innerText       = d.admin_remarks || d.rejection_reason || 'No remarks added yet.';

            const ssImg  = document.getElementById('mdl_screenshot_img');
            const ssNone = document.getElementById('mdl_no_screenshot');
            if (d.screenshot_url) {
                ssImg.src = d.screenshot_url;
                ssImg.classList.remove('d-none');
                ssNone.classList.add('d-none');
            } else {
                ssImg.src = '';
                ssImg.classList.add('d-none');
                ssNone.classList.remove('d-none');
            }

            const ordHistBox = document.getElementById('mdl_order_history_list');
            if (d.order_payment_history && d.order_payment_history.length > 0) {
                ordHistBox.innerHTML = d.order_payment_history.map(item => `
                    <div class="d-flex justify-content-between align-items-center border-bottom py-1">
                        <div>
                            <strong class="font-monospace text-primary">${item.uid}</strong> (${item.method})
                            <div class="text-muted font-monospace" style="font-size:11px;">TrxID: ${item.trx_id} | ${item.date}</div>
                        </div>
                        <div class="text-end">
                            <span class="badge ${item.is_trashed ? 'bg-danger' : 'bg-secondary'} font-monospace" style="font-size:10px;">${item.status}</span>
                            <div class="font-monospace fw-bold bn-digit">৳${item.verified_amount}</div>
                        </div>
                    </div>
                `).join('');
            } else {
                ordHistBox.innerHTML = '<div class="text-muted py-2 text-center">No other payment records for this order.</div>';
            }

            const auditBox = document.getElementById('mdl_audit_timeline');
            if (d.verification_history && d.verification_history.length > 0) {
                auditBox.innerHTML = d.verification_history.map(h => `
                    <div class="border-start border-2 border-primary ps-2 mb-2 pb-1">
                        <div class="d-flex justify-content-between">
                            <strong class="text-dark">${h.action_label}</strong>
                            <small class="text-muted font-monospace bn-digit" style="font-size:10.5px;">${h.created_at}</small>
                        </div>
                        <div class="text-muted" style="font-size:11px;">
                            Status: <span class="font-monospace">${h.old_status} &rarr; <strong>${h.new_status}</strong></span> | By: <strong>${h.performer}</strong>
                        </div>
                        ${h.reason ? `<div class="text-danger" style="font-size:11px;">Reason: ${h.reason}</div>` : ''}
                        ${h.remarks ? `<div class="text-secondary" style="font-size:11px;">Note: ${h.remarks}</div>` : ''}
                    </div>
                `).join('');
            } else {
                auditBox.innerHTML = '<div class="text-muted py-2 text-center">No verification history available.</div>';
            }

            bootstrap.Modal.getOrCreateInstance(document.getElementById('txnDetailModal')).show();
        });
    }

    function triggerModalVerifyAction(action) {
        if (!activeModalTxnData) return;
        promptVerificationAction(
            activeModalTxnData.id,
            action,
            activeModalTxnData.transaction_uid,
            parseFloat(activeModalTxnData.expected_amount || 0),
            parseFloat(activeModalTxnData.submitted_amount || 0)
        );
    }

    function promptVerificationAction(id, action, uid, expectedAmt, submittedAmt) {
        document.getElementById('act_txn_id').value = id;
        document.getElementById('act_type').value = action;
        document.getElementById('act_reason').value = '';

        const amtWrap     = document.getElementById('actAmountWrap');
        const amtInput    = document.getElementById('act_verified_amount');
        const amtLabel    = document.getElementById('actAmountLabel');
        const reasonLabel = document.getElementById('actReasonLabel');

        const titles = {
            'mark_checked':        'Mark Transaction as Admin Checked',
            'approve':             'Approve Transaction',
            'mark_paid':           'Verify & Mark Paid / Received',
            'mark_partially_paid': 'Mark as Partially Paid',
            'under_review':        'Move to Under Review',
            'mark_unmatched':      'Mark as Unmatched',
            'mark_fake':           'Reject / Mark as Fake',
            'refund':              'Issue Payment Refund',
            'add_note':            'Add Internal Admin Note',
        };

        document.getElementById('actModalTitle').innerText = titles[action] || 'Confirm Action';
        document.getElementById('actModalSubtitle').innerText = `Transaction: ${uid} (Order Payable: ৳${expectedAmt})`;

        if (action === 'mark_paid' || action === 'mark_partially_paid' || action === 'refund') {
            amtWrap.classList.remove('d-none');
            amtLabel.innerText = action === 'refund' ? 'Refund Amount (৳) *' : 'Verified Received Amount (৳) *';
            amtInput.value = submittedAmt > 0 ? submittedAmt : expectedAmt;
        } else {
            amtWrap.classList.add('d-none');
        }

        if (['mark_unmatched', 'mark_fake', 'refund', 'add_note'].includes(action)) {
            reasonLabel.innerHTML = 'Reason / Note <span class="text-danger">* (Mandatory)</span>';
        } else {
            reasonLabel.innerHTML = 'Admin Note / Remarks (Optional)';
        }

        bootstrap.Modal.getOrCreateInstance(document.getElementById('txnActionConfirmModal')).show();
    }

    function submitVerificationActionAjax() {
        const id     = document.getElementById('act_txn_id').value;
        const action = document.getElementById('act_type').value;
        const reason = document.getElementById('act_reason').value.trim();
        const amtVal = document.getElementById('act_verified_amount').value;
        const btn    = document.getElementById('btnConfirmVerifySubmit');

        if (['mark_unmatched', 'mark_fake', 'refund', 'add_note'].includes(action) && !reason) {
            showTxnNotice('Reason Required', 'Please provide a valid verification reason or note.', 'warning');
            return;
        }

        btn.disabled = true;
        btn.innerText = 'Processing...';

        const payload = { action: action, reason: reason, remarks: reason };
        if (action === 'refund') {
            payload.refund_amount = amtVal;
        } else if (action === 'mark_paid' || action === 'mark_partially_paid') {
            payload.verified_amount = amtVal;
        }

        fetch("{{ url('admin/transactions/verify') }}/" + id, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw data;
            return data;
        })
        .then(data => {
            bootstrap.Modal.getInstance(document.getElementById('txnActionConfirmModal'))?.hide();
            if (data.alerts) updateSidebarAlertCounters(data.alerts);
            showTxnNotice('Updated', data.message, 'success');
            triggerTxnAjaxFilter(1);

            const detailModalEl = document.getElementById('txnDetailModal');
            if (detailModalEl.classList.contains('show')) {
                openTransactionDetailModal(id);
            }
        })
        .catch(err => {
            const msg = err.errors ? Object.values(err.errors)[0][0] : (err.message || 'Verification action failed.');
            showTxnNotice('Action Blocked', msg, 'danger');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = 'Confirm & Save';
        });
    }

    function openManualTransactionModal() {
        document.getElementById('manualTxnForm').reset();
        document.getElementById('manual_order_id').value = '';
        document.getElementById('manual_invoice_no').value = '';
        clearManualTxnProof();
        document.getElementById('manualLinkedOrderBadge').classList.add('d-none');
        document.getElementById('manualExistingTxnWarning').classList.add('d-none');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('manualTxnModal')).show();
    }

    function submitManualTransactionAjax() {
        const orderId   = document.getElementById('manual_order_id').value;
        const invoiceNo = document.getElementById('manual_invoice_no').value;

        if (!orderId && !invoiceNo) {
            showTxnNotice(
                'Order Selection Required',
                'Selecting an existing Online Order is required! Please click the search input above to choose an order.',
                'warning'
            );
            return;
        }

        const form = document.getElementById('manualTxnForm');
        const btn  = document.getElementById('btnSaveManualTxn');
        const formData = new FormData(form);

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

        fetch("{{ route('admin.transactions.store_manual') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw data;
            return data;
        })
        .then(data => {
            bootstrap.Modal.getInstance(document.getElementById('manualTxnModal'))?.hide();
            if (data.alerts) updateSidebarAlertCounters(data.alerts);
            showTxnNotice('Saved Successfully', data.message, 'success');
            triggerTxnAjaxFilter(1);
        })
        .catch(err => {
            const msg = err.errors ? Object.values(err.errors)[0][0] : (err.message || 'Failed to save manual transaction.');
            showTxnNotice('Validation Error', msg, 'danger');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Save Manual Transaction';
        });
    }

    function openScreenshotPreviewModal(url, uid) {
        document.getElementById('ssPreviewTitle').innerText = 'Payment Proof — ' + uid;
        document.getElementById('ssPreviewImage').src = url;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('screenshotPreviewModal')).show();
    }

    function exportTransactionsExcel() {
        const form = document.getElementById('txnFilterForm');
        const params = new URLSearchParams(new FormData(form));
        params.set('export', 'excel');
        window.location.href = "{{ route('admin.transactions.index') }}?" + params.toString();
    }

    function exportTransactionsPdf() {
        const btn = document.getElementById('btnExportTxnPdf');
        const txt = document.getElementById('btnExportTxnPdfText');
        const orig = txt.innerText;

        btn.disabled = true;
        txt.innerText = 'Preparing PDF...';

        const form = document.getElementById('txnFilterForm');
        const params = new URLSearchParams(new FormData(form));
        params.set('export', 'pdf_data');

        fetch("{{ route('admin.transactions.index') }}?" + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success || !data.records) throw new Error('Invalid PDF data');

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });

            doc.setFont('helvetica', 'bold');
            doc.setFontSize(13);
            doc.setTextColor(15, 23, 42);
            doc.text(data.title, 12, 13);

            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8.5);
            doc.setTextColor(71, 85, 105);
            const s = data.summary || {};
            doc.text(
                `Generated: ${data.generated_at}  |  Records: ${s.total_transactions || 0}  |  Expected: BDT ${s.total_expected_amount || '0.00'}  |  Verified Paid: BDT ${s.total_paid_amount || '0.00'}  |  Unpaid/Due: BDT ${s.total_unpaid_amount || '0.00'}`,
                12,
                19
            );

            const bodyRows = data.records.map(r => [
                r.sl, r.uid, r.order_ref, r.customer, r.source, r.method,
                r.sender, r.trx_id, r.expected, r.verified, r.status,
                r.order_st, r.verified_by, r.date, r.remarks
            ]);

            doc.autoTable({
                startY: 23,
                head: [[
                    'SL', 'UID', 'Order/Inv', 'Customer', 'Source', 'Method',
                    'Sender', 'TrxID', 'Expected', 'Verified', 'Txn Status',
                    'Order St.', 'Verified By', 'Date', 'Remarks'
                ]],
                body: bodyRows,
                theme: 'grid',
                styles: {
                    font: 'helvetica',
                    fontSize: 7.2,
                    cellPadding: 1.8,
                    textColor: [30, 41, 59],
                    lineColor: [203, 213, 225],
                    lineWidth: 0.15
                },
                headStyles: {
                    fillColor: [241, 245, 249],
                    textColor: [15, 23, 42],
                    fontStyle: 'bold'
                },
                margin: { top: 12, right: 8, bottom: 12, left: 8 }
            });

            doc.save('Payment_Transactions_Report_' + (new Date().toISOString().slice(0, 10)) + '.pdf');
        })
        .catch(err => {
            console.error(err);
            showTxnNotice('Export Error', 'Failed to generate PDF report.', 'danger');
        })
        .finally(() => {
            btn.disabled = false;
            txt.innerText = orig;
        });
    }
</script>
@endsection