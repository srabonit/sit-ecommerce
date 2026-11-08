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
    /* =========================================================
       COURIER SETTLEMENT — KAIADMIN CLASSIC / WHITE
    ========================================================= */

    #courier-settlement-root {
        --cs-primary: #2563eb;
        --cs-primary-soft: #eff6ff;
        --cs-success: #059669;
        --cs-danger: #dc2626;
        --cs-warning: #d97706;
        --cs-text: #1e293b;
        --cs-muted: #64748b;
        --cs-border: #e2e8f0;
        --cs-surface: #ffffff;
        --cs-soft: #f8fafc;
        color: var(--cs-text);
        font-size: 13px;
        min-width: 0;
    }

    #courier-settlement-root *,
    .settlement-modal-scope * {
        box-sizing: border-box;
    }

    /* Strict 5px radius */
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

    #courier-settlement-root .card {
        border: 1px solid var(--cs-border) !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
    }

    #courier-settlement-root .shadow-xs {
        box-shadow: 0 1px 3px rgba(15, 23, 42, .05) !important;
    }

    /* Typography */
    #courier-settlement-root .bn-digit,
    #courier-settlement-root .font-monospace,
    .settlement-modal-scope .bn-digit,
    .settlement-modal-scope .font-monospace {
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    #courier-settlement-root h5,
    #courier-settlement-root h6 {
        letter-spacing: -.2px;
    }

    /* Buttons */
    #courier-settlement-root .btn-compact {
        min-height: 30px;
        font-size: 11.5px !important;
        padding: 5px 11px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        white-space: nowrap;
        transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease;
    }

    #courier-settlement-root .btn-compact:focus-visible,
    #courier-settlement-root .btn:focus-visible {
        outline: 0;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .15) !important;
    }

    #courier-settlement-root .btn-primary {
        background: var(--cs-primary);
        border-color: var(--cs-primary);
    }

    #courier-settlement-root .btn-primary:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
    }

    /* KPI Cards */
    #courier-settlement-root .kpi-card {
        background: var(--cs-surface);
        border: 1px solid var(--cs-border);
        padding: 13px 14px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .04);
        position: relative;
        overflow: hidden;
        height: 100%;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 3px;
        transition: box-shadow .18s ease, transform .18s ease;
    }

    #courier-settlement-root .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(15, 23, 42, .07);
    }

    #courier-settlement-root .kpi-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
    }

    #courier-settlement-root .kpi-expected::before { background: #0284c7; }
    #courier-settlement-root .kpi-settled::before { background: #10b981; }
    #courier-settlement-root .kpi-due::before { background: #ef4444; }
    #courier-settlement-root .kpi-cost::before { background: #f59e0b; }

    #courier-settlement-root .kpi-card h5 {
        overflow-wrap: anywhere;
        line-height: 1.35;
    }

    /* Status Navigation */
    #courier-settlement-root .settle-nav-strip {
        display: flex;
        flex-wrap: nowrap;
        gap: 7px;
        overflow-x: auto;
        padding: 2px 0 4px;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
        -webkit-overflow-scrolling: touch;
    }

    #courier-settlement-root .settle-nav-strip::-webkit-scrollbar {
        height: 3px;
    }

    #courier-settlement-root .settle-nav-strip::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 5px;
    }

    #courier-settlement-root .settle-nav-pill {
        flex: 0 0 auto;
        font-size: 11.5px;
        font-weight: 600;
        padding: 7px 12px;
        color: #475569;
        background: #f8fafc;
        border: 1px solid var(--cs-border);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        white-space: nowrap;
        transition: all .15s ease;
    }

    #courier-settlement-root .settle-nav-pill:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #cbd5e1;
    }

    #courier-settlement-root .settle-nav-pill.active {
        color: #ffffff !important;
        background: var(--cs-primary) !important;
        border-color: var(--cs-primary) !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, .2);
    }

    /* Filter Controls */
    #courier-settlement-root .form-control,
    #courier-settlement-root .form-select {
        min-height: 34px;
        height: 34px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        color: #0f172a;
        background-color: #fff;
        box-shadow: none;
        padding: 5px 9px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #courier-settlement-root .form-control::placeholder {
        color: #94a3b8;
        opacity: 1;
    }

    #courier-settlement-root .form-control:focus,
    #courier-settlement-root .form-select:focus {
        border-color: var(--cs-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .11);
        outline: none;
    }

    #courier-settlement-root .input-group-text {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #64748b;
    }

    /* Bulk Settlement Bar */
    #courier-settlement-root .settlement-bulk-bar {
        background: #172033;
        color: #ffffff;
        padding: 10px 14px;
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
        border: 1px solid #263449;
        border-radius: 5px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, .12);
    }

    /* Table */
    #courier-settlement-root .settlement-table-card {
        overflow: hidden;
        background: #fff;
    }

    #courier-settlement-root .pc-fit-container {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #courier-settlement-root .custom-settlement-table {
        width: 100%;
        min-width: 1050px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #courier-settlement-root .custom-settlement-table th {
        background: #f8fafc;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .035em;
        border-bottom: 1px solid #e2e8f0;
        padding: 11px 7px;
        white-space: nowrap;
        vertical-align: middle;
    }

    #courier-settlement-root .custom-settlement-table td {
        padding: 9px 7px;
        vertical-align: middle;
        font-size: 11px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }

    #courier-settlement-root .custom-settlement-table tbody tr {
        transition: background-color .12s ease;
    }

    #courier-settlement-root .custom-settlement-table tbody tr:hover {
        background: #f8fbff;
    }

    #courier-settlement-root .custom-settlement-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #courier-settlement-root .custom-settlement-table .badge {
        max-width: 100%;
        vertical-align: middle;
    }

    #courier-settlement-root .custom-settlement-table .form-check-input {
        border-color: #cbd5e1;
        cursor: pointer;
    }

    #courier-settlement-root .custom-settlement-table .form-check-input:checked {
        background-color: var(--cs-primary);
        border-color: var(--cs-primary);
    }

    @media (min-width: 992px) {
        #courier-settlement-root .custom-settlement-table {
            table-layout: fixed;
            width: 100%;
            min-width: 1050px;
        }

        #courier-settlement-root .custom-settlement-table th,
        #courier-settlement-root .custom-settlement-table td {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #courier-settlement-root .col-w-chk { width: 3%; }
        #courier-settlement-root .col-w-ord { width: 13%; }
        #courier-settlement-root .col-w-trk { width: 13%; }
        #courier-settlement-root .col-w-cust { width: 15%; }
        #courier-settlement-root .col-w-exp { width: 9%; }
        #courier-settlement-root .col-w-set { width: 9%; }
        #courier-settlement-root .col-w-due { width: 8%; }
        #courier-settlement-root .col-w-dst { width: 8%; }
        #courier-settlement-root .col-w-sst { width: 8%; }
        #courier-settlement-root .col-w-act { width: 14%; }
    }

    /* Proof Media */
    #courier-settlement-root .proof-media-surface {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        padding: 11px;
        transition: border-color .2s ease, background-color .2s ease;
    }

    #courier-settlement-root .proof-media-surface:hover {
        border-color: var(--cs-primary);
        background: #f5f9ff;
    }

    #courier-settlement-root .proof-thumb-box {
        width: 26px;
        height: 26px;
        background: #fff;
        border: 1px solid #cbd5e1;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        position: relative;
    }

    #courier-settlement-root .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #courier-settlement-root .proof-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .55);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity .15s ease;
    }

    #courier-settlement-root .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    /* Pagination */
    #courier-settlement-root #courierSettlementPagination {
        display: flex;
        align-items: center;
        min-width: 0;
        max-width: 100%;
    }

    #courier-settlement-root #courierSettlementPagination nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }

    #courier-settlement-root #courierSettlementPagination nav > div.d-sm-none,
    #courier-settlement-root #courierSettlementPagination nav > .d-sm-none {
        display: none !important;
    }

    #courier-settlement-root #courierSettlementPagination nav > div.d-none,
    #courier-settlement-root #courierSettlementPagination nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #courier-settlement-root #courierSettlementPagination nav p,
    #courier-settlement-root #courierSettlementPagination nav .small.text-muted,
    #courier-settlement-root #courierSettlementPagination nav div:has(> p),
    #courier-settlement-root #courierSettlementPagination nav > div.d-none > div:first-child,
    #courier-settlement-root #courierSettlementPagination nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }

    #courier-settlement-root #courierSettlementPagination .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        padding: 0 !important;
        list-style: none !important;
    }

    #courier-settlement-root #courierSettlementPagination .page-item {
        display: inline-flex !important;
    }

    #courier-settlement-root #courierSettlementPagination .page-item .page-link,
    #courier-settlement-root #courierSettlementPagination .page-item span {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 28px !important;
        height: 28px !important;
        padding: 0 7px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        color: #475569 !important;
        background: #fff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 5px !important;
        text-decoration: none !important;
        line-height: 1 !important;
        transition: all .12s ease !important;
    }

    #courier-settlement-root #courierSettlementPagination .page-item .page-link:hover {
        background: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }

    #courier-settlement-root #courierSettlementPagination .page-item.active .page-link,
    #courier-settlement-root #courierSettlementPagination .page-item.active span {
        background: var(--cs-primary) !important;
        border-color: var(--cs-primary) !important;
        color: #fff !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, .2) !important;
    }

    #courier-settlement-root #courierSettlementPagination .page-item.disabled .page-link,
    #courier-settlement-root #courierSettlementPagination .page-item.disabled span {
        opacity: .45 !important;
        background: #f8fafc !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }

    #courier-settlement-root #courierSettlementPagination nav svg {
        width: 11px !important;
        height: 11px !important;
    }

    /* Modal */
    .settlement-modal-scope .modal-content {
        border: 1px solid #e2e8f0;
        box-shadow: 0 12px 35px rgba(15, 23, 42, .18);
        overflow: hidden;
    }

    .settlement-modal-scope .modal-header {
        border-bottom: 1px solid rgba(255, 255, 255, .08);
    }

    .settlement-modal-scope .modal-body {
        max-height: calc(100vh - 170px);
        overflow-y: auto;
    }

    .settlement-modal-scope .form-control,
    .settlement-modal-scope .form-select {
        min-height: 35px;
        border-color: #cbd5e1;
        font-size: 12px;
    }

    .settlement-modal-scope .form-control:focus,
    .settlement-modal-scope .form-select:focus {
        border-color: var(--cs-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .11);
    }

    .settlement-modal-scope .form-label {
        color: #475569 !important;
    }

    /* Responsive */
    @media (max-width: 767.98px) {
        #courier-settlement-root {
            font-size: 12px;
        }

        #courier-settlement-root .kpi-card {
            padding: 10px !important;
        }

        #courier-settlement-root .kpi-card h5 {
            font-size: 14px !important;
        }

        #courier-settlement-root .top-action-bar-wrap {
            width: 100% !important;
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 7px !important;
        }

        #courier-settlement-root .top-action-bar-wrap .btn {
            width: 100%;
            min-width: 0;
            padding-left: 7px !important;
            padding-right: 7px !important;
        }

        #courier-settlement-root .settlement-bulk-bar {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 10px !important;
        }

        #courier-settlement-root .settlement-bulk-bar > div {
            flex-wrap: wrap;
        }

        #courier-settlement-root .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        #courier-settlement-root .custom-settlement-table {
            min-width: 1050px !important;
            table-layout: auto !important;
        }

        #courier-settlement-root #courierSettlementPagination {
            width: 100% !important;
            justify-content: center !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            padding: 4px 0 !important;
        }

        #courier-settlement-root #courierSettlementPagination nav {
            justify-content: center !important;
            width: 100% !important;
        }

        #courier-settlement-root #courierSettlementPagination .pagination {
            flex-wrap: nowrap !important;
            gap: 3px !important;
        }

        #courier-settlement-root #courierSettlementPagination .page-item .page-link,
        #courier-settlement-root #courierSettlementPagination .page-item span {
            min-width: 25px !important;
            height: 25px !important;
            font-size: 10.5px !important;
            padding: 0 5px !important;
        }

        #courier-settlement-root .settlement-table-card > .p-2.px-3 {
            align-items: flex-start !important;
        }

        #courier-settlement-root .settlement-table-card > .p-2.px-3 > small {
            width: 100%;
            text-align: center;
        }
    }

    @media (max-width: 420px) {
        #courier-settlement-root.container-fluid {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }

        #courier-settlement-root .kpi-card {
            padding: 9px 8px !important;
        }

        #courier-settlement-root .kpi-card span {
            font-size: 9px !important;
        }

        #courier-settlement-root .kpi-card h5 {
            font-size: 12.5px !important;
        }

        #courier-settlement-root .kpi-card small {
            font-size: 8.5px !important;
        }

        #courier-settlement-root .top-action-bar-wrap .btn {
            font-size: 10px !important;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #courier-settlement-root *,
        .settlement-modal-scope * {
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
            animation-duration: .01ms !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="courier-settlement-root">

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
        <div class="min-w-0">
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-money-bill-transfer text-primary"></i>
                <span>COD & Cost Settlement</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.5;">
               COD & Payout Reconciliation
            </small>
        </div>

        <div class="top-action-bar-wrap d-flex flex-wrap gap-2">
            <a href="{{ route('admin.courier.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-boxes-packing me-1"></i>
                <span>Courier Orders</span>
            </a>
            <a href="{{ route('admin.courier.dashboard') }}" class="btn btn-dark btn-compact shadow-xs">
                <i class="fas fa-chart-pie me-1"></i>
                <span>Dashboard</span>
            </a>
        </div>
    </div>

    <!-- Alert -->
    <div id="settlementAlert" class="alert d-none py-2 px-3 small border-0 shadow-sm mb-3" style="font-size: 12px;"></div>

    <!-- KPI Cards -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-expected">
                <span class="text-muted small d-block mb-1 text-truncate" style="font-size: 10.5px; text-transform: uppercase; font-weight: 700;">Expected COD</span>
                <h5 class="fw-bold text-dark mb-1 font-monospace bn-digit" style="font-size: 15px;">৳ {{ number_format($totalExpectedCod, 2) }}</h5>
                <small class="text-secondary text-truncate d-block" style="font-size: 9.5px;">Customer receivables</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-settled">
                <span class="text-muted small d-block mb-1 text-truncate" style="font-size: 10.5px; text-transform: uppercase; font-weight: 700;">Settled COD</span>
                <h5 class="fw-bold text-success mb-1 font-monospace bn-digit" style="font-size: 15px;">৳ {{ number_format($totalSettledCod, 2) }}</h5>
                <small class="text-muted text-truncate d-block" style="font-size: 9.5px;">Merchant deposits</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-due">
                <span class="text-muted small d-block mb-1 text-truncate" style="font-size: 10.5px; text-transform: uppercase; font-weight: 700;">Due COD</span>
                <h5 class="fw-bold text-danger mb-1 font-monospace bn-digit" style="font-size: 15px;">৳ {{ number_format($totalDueCod, 2) }}</h5>
                <small class="text-danger text-truncate d-block" style="font-size: 9.5px;">Pending with couriers</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-cost">
                <span class="text-muted small d-block mb-1 text-truncate" style="font-size: 10.5px; text-transform: uppercase; font-weight: 700;">Courier Costs</span>
                <h5 class="fw-bold text-dark mb-1 font-monospace bn-digit" style="font-size: 15px;">৳ {{ number_format($totalCourierCost, 2) }}</h5>
                <small class="text-muted text-truncate d-block" style="font-size: 9.5px;">Delivery &amp; returns</small>
            </div>
        </div>
    </div>
<!-- Status Navigation -->
    @php
        $currStatus = request('status', 'all');
    @endphp

    <div class="card p-2 mb-3 bg-white">
        <div class="settle-nav-strip d-flex flex-wrap gap-1">
            <a href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}" class="settle-nav-pill flex-fill text-center d-flex align-items-center justify-content-center gap-1 px-2 py-1 {{ $currStatus === 'all' ? 'active' : '' }}">
                <span>All</span>
                <span class="badge {{ $currStatus === 'all' ? 'bg-white text-dark' : 'bg-secondary-subtle text-dark border' }} px-1 py-0 font-monospace bn-digit" style="font-size: 9.5px;">{{ $counts['all'] ?? 0 }}</span>
            </a>

            <a href="{{ request()->fullUrlWithQuery(['status' => 'unsettled']) }}" class="settle-nav-pill flex-fill text-center d-flex align-items-center justify-content-center gap-1 px-2 py-1 {{ $currStatus === 'unsettled' ? 'active' : '' }}">
                <span>Unsettled</span>
                <span class="badge bg-danger text-white px-1 py-0 font-monospace bn-digit" style="font-size: 9.5px;">{{ $counts['unsettled'] ?? 0 }}</span>
            </a>

            <a href="{{ request()->fullUrlWithQuery(['status' => 'partial']) }}" class="settle-nav-pill flex-fill text-center d-flex align-items-center justify-content-center gap-1 px-2 py-1 {{ $currStatus === 'partial' ? 'active' : '' }}">
                <span>Partial</span>
                <span class="badge bg-warning text-dark px-1 py-0 font-monospace bn-digit" style="font-size: 9.5px;">{{ $counts['partial'] ?? 0 }}</span>
            </a>

            <a href="{{ request()->fullUrlWithQuery(['status' => 'settled']) }}" class="settle-nav-pill flex-fill text-center d-flex align-items-center justify-content-center gap-1 px-2 py-1 {{ $currStatus === 'settled' ? 'active' : '' }}">
                <span>Settled</span>
                <span class="badge bg-success text-white px-1 py-0 font-monospace bn-digit" style="font-size: 9.5px;">{{ $counts['settled'] ?? 0 }}</span>
            </a>
        </div>
    </div>
    <!-- Search & Filters -->
    <div class="card mb-3 p-3 bg-white">
        <form action="{{ route('admin.courier.settlements') }}" method="GET" class="row g-2 align-items-center">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted border-end-0">
                        <i class="fas fa-search" style="font-size: 11px;"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0 shadow-none" placeholder="Search invoice, tracking or phone..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <select name="courier" class="form-select form-select-sm shadow-none">
                    <option value="all">All Couriers</option>
                    @foreach($couriersList as $cr)
                        <option value="{{ $cr }}" {{ strtolower(request('courier')) === strtolower($cr) ? 'selected' : '' }}>
                            {{ $cr }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-3">
                <select name="date_filter" class="form-select form-select-sm shadow-none">
                    <option value="">All Dates</option>
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

    <!-- Bulk Settlement Bar -->
    <div class="settlement-bulk-bar shadow-xs" id="settlementBulkActionBar" style="display: none;">
        <div class="d-flex align-items-center gap-2">
            <span class="small fw-bold text-white d-flex align-items-center" style="font-size: 12px;">
                <i class="fas fa-check-double text-warning me-2"></i>
                <span>Bulk Settlement</span>
            </span>
            <span class="badge bg-warning text-dark px-2 py-1 bn-digit font-monospace" id="selectedCounterBadge" style="font-size: 11px;">0 selected</span>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-success btn-compact px-3 shadow-xs" onclick="executeBulkSettle()">
                <i class="fas fa-check-circle me-1"></i> Settle Selected
            </button>
            <button type="button" class="btn btn-outline-light btn-compact" onclick="clearAllBulkSettlementSelections()">
                Cancel
            </button>
        </div>
    </div>

    <!-- Settlement Table -->
    <div class="card border settlement-table-card shadow-xs">
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
                            <th class="col-w-cust">Customer</th>
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
                                        <!-- Settlement Proof Thumbnail -->
                                        @if($proofUrl)
                                            <div class="proof-thumb-box" onclick="zoomSettlementProof('{{ $proofUrl }}', 'Invoice #{{ $row->invoice_no ?? $row->order_id }} Settlement Proof')" title="View Proof">
                                                <img src="{{ $proofUrl }}" alt="Proof" onerror="this.src='https://placehold.co/40x40?text=Slip';">
                                                <div class="proof-thumb-hover-overlay">
                                                    <i class="fas fa-search-plus text-white" style="font-size: 8.5px;"></i>
                                                </div>
                                            </div>
                                        @endif

                                        <button type="button" class="btn btn-outline-success btn-compact py-0 px-2 shadow-xs" style="height: 22px; font-size: 10px !important;" onclick="openSettlementModal({{ $row->id }}, {{ $codAmount }}, {{ $settledAmount }}, '{{ $row->courier_name }}', '{{ $row->tracking_code }}', {{ (float)$row->courier_cost }}, {{ (float)$row->cod_charge }}, '{{$proofRaw ?? '' }}')" title="Reconcile &amp; Settle">
                                            <i class="fas fa-hand-holding-dollar me-1"></i>
                                            <span>Settle</span>
                                        </button>

                                        <a href="{{ route('admin.order.view', $row->order_id) }}" class="btn btn-light border p-0 shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 10px;" title="View Order">
                                            <i class="fas fa-eye text-muted"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted small">
                                    <i class="fas fa-money-check fa-2x mb-2 d-block opacity-25"></i>
                                    <span class="fw-semibold">No courier bookings found.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50">
            <small class="text-muted bn-digit font-monospace" style="font-size: 11px;">
                Showing {{ $bookings->firstItem() ?? 0 }} to {{ $bookings->lastItem() ?? 0 }} of {{$bookings->total() }} records
            </small>

            <div id="courierSettlementPagination">
                {{ $bookings->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Settlement Modal -->
<div class="modal fade settlement-modal-scope" id="settlementModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2 px-3">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-2 mb-0" style="font-size: 13.5px;">
                    <i class="fas fa-file-invoice-dollar text-warning"></i>
                    <span>COD Settlement &amp; Proof</span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <form id="settlementForm" onsubmit="handleSettlementSubmit(event)">
                @csrf
                <input type="hidden" id="modalBookingId" name="booking_id">
                <input type="hidden" id="modalSettlementProofMedia" name="settlement_proof" value="">

                <div class="modal-body p-3">
                    <div class="bg-light p-2 border mb-3" style="border-radius: 5px;">
                        <div class="d-flex justify-content-between mb-1 gap-2">
                            <span class="text-muted small" style="font-size: 11px;">Courier</span>
                            <strong class="text-dark font-monospace text-end" id="modalCourierName">—</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-1 gap-2">
                            <span class="text-muted small" style="font-size: 11px;">Tracking</span>
                            <span class="font-monospace text-primary fw-bold text-end" id="modalTrackingCode">—</span>
                        </div>

                        <div class="d-flex justify-content-between gap-2">
                            <span class="text-muted small" style="font-size: 11px;">Expected COD</span>
                            <strong class="text-danger font-monospace bn-digit" id="modalExpectedCod">৳ 0.00</strong>
                        </div>
                    </div>

                    <div class="row g-2">
                        <!-- Settled Amount -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">
                                Received Amount (৳) <span class="text-danger">*</span>
                            </label>
                            <input type="number" id="modalSettledAmount" name="settled_amount" class="form-control form-control-sm font-monospace bn-digit text-end fw-bold text-success shadow-none" required step="any" min="0">
                        </div>

                        <!-- Settlement Date -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">
                                Deposit Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" id="modalSettlementDate" name="settlement_date" class="form-control form-control-sm font-monospace bn-digit shadow-none" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <!-- Courier Cost -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">Delivery Fee (৳)</label>
                            <input type="number" id="modalCourierCost" name="courier_cost" class="form-control form-control-sm font-monospace bn-digit text-end shadow-none" step="any" min="0" value="0">
                        </div>

                        <!-- COD Charge -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">COD Charge (1% / Fee)</label>
                            <input type="number" id="modalCodCharge" name="cod_charge" class="form-control form-control-sm font-monospace bn-digit text-end shadow-none" step="any" min="0" value="0">
                        </div>

                        <!-- Payment Method -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">Deposit Method</label>
                            <select name="payment_method" class="form-select form-select-sm shadow-none" style="font-size: 11.5px;">
                                <option value="Bank Transfer (Courier COD)" selected>Bank Transfer (Courier COD)</option>
                                <option value="bKash Merchant">bKash Merchant</option>
                                <option value="Nagad Business">Nagad Business</option>
                                <option value="Cash Handover">Cash Handover</option>
                            </select>
                        </div>

                        <!-- Transaction Reference -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">Transaction ID (Optional)</label>
                            <input type="text" name="transaction_id" class="form-control form-control-sm font-monospace bn-digit shadow-none" placeholder="STLM-98234">
                        </div>

                        <!-- Proof Attachment -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted mb-1 d-flex justify-content-between align-items-center" style="font-size: 11px;">
                                <span><i class="fas fa-paperclip text-primary me-1"></i> Remittance Proof</span>
                                <span class="badge bg-light text-muted border font-monospace" style="font-size: 8.5px;">GLOBAL MEDIA</span>
                            </label>

                            <div class="proof-media-surface">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <button type="button" class="btn btn-outline-primary btn-compact shadow-xs" onclick="chooseSettlementProofMedia()">
                                        <i class="fas fa-photo-film me-1"></i>
                                        <span>Choose Media</span>
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
                                            <i class="fas fa-check-circle me-1"></i> Attached
                                        </span>
                                        <small class="text-muted d-block text-truncate font-monospace" id="settlement_proof_preview_txt" style="font-size: 9px;"></small>
                                    </div>

                                    <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-auto text-decoration-none fw-bold" style="font-size: 11px;" onclick="clearSettlementProofMedia()">&times; Clear</button>
                                </div>
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">Note</label>
                            <textarea name="remarks" id="modalRemarks" rows="2" class="form-control form-control-sm shadow-none" placeholder="Add payment or bank reference notes..."></textarea>
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

<!-- Proof Zoom Modal -->
<div class="modal fade settlement-modal-scope" id="settlementProofZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 1080;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;" id="settlementProofZoomTitle">
                    <i class="fas fa-image text-primary me-1"></i> Settlement Proof
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