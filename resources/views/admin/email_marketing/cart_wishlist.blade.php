@extends('layouts.admin')

@section('title', 'Cart & Abandoned Recovery - Email Marketing')

@php
    $abandonedCarts = $abandonedCarts ?? collect();
    $stats = $stats ?? [];

    $totalCartsCount = $stats['total'] ?? (
        method_exists($abandonedCarts, 'total')
            ? $abandonedCarts->total()
            : $abandonedCarts->count()
    );

    $automationsIndexRoute = Route::has('admin.email_marketing.automations.index')
        ? route('admin.email_marketing.automations.index')
        : url('admin/email-marketing/automations');

    $dashboardRoute = Route::has('admin.email_marketing.dashboard')
        ? route('admin.email_marketing.dashboard')
        : url('admin/email-marketing/dashboard');

    $indexUrl = Route::has('admin.email_marketing.cart_wishlist_marketing')
        ? route('admin.email_marketing.cart_wishlist_marketing')
        : url('admin/email-marketing/cart-wishlist');

    $exportCsvUrl = Route::has('admin.email_marketing.cart_wishlist.export_csv')
        ? route('admin.email_marketing.cart_wishlist.export_csv')
        : url('admin/email-marketing/cart-wishlist/export-csv');

    $sendRecoveryBaseUrl = url('admin/email-marketing/cart-wishlist/send-recovery');
    $bulkSendUrl         = url('admin/email-marketing/cart-wishlist/bulk-send');
    $deleteBaseUrl       = url('admin/email-marketing/cart-wishlist/delete');
    $bulkDeleteUrl       = url('admin/email-marketing/cart-wishlist/bulk-delete');
@endphp

@section('admin_content')

<style>
    /* =========================================================
       CART & ABANDONED RECOVERY - MODERN ADMIN UI
    ========================================================= */

    #email-marketing-cart-wishlist-root {
        --cart-primary: #2563eb;
        --cart-warning: #f59e0b;
        --cart-success: #059669;
        --cart-danger: #dc2626;
        --cart-text: #1e293b;
        --cart-muted: #64748b;
        --cart-border: #e2e8f0;
        --cart-soft: #f8fafc;
        --cart-radius: 5px;
        color: var(--cart-text);
    }

    #email-marketing-cart-wishlist-root *,
    .recovery-modal-scope * {
        box-sizing: border-box;
    }

    /* Strict 5px Radius */

    #email-marketing-cart-wishlist-root .btn,
    #email-marketing-cart-wishlist-root .form-control,
    #email-marketing-cart-wishlist-root .form-select,
    #email-marketing-cart-wishlist-root .badge,
    #email-marketing-cart-wishlist-root .input-group-text,
    #email-marketing-cart-wishlist-root .page-link,
    #email-marketing-cart-wishlist-root .card,
    #email-marketing-cart-wishlist-root .metric-card,
    #email-marketing-cart-wishlist-root .tag-chip,
    #email-marketing-cart-wishlist-root .collapse-toggle-btn,
    .recovery-modal-scope .modal-content,
    .recovery-modal-scope .btn,
    .admin-cart-toast {
        border-radius: 5px !important;
    }

    #email-marketing-cart-wishlist-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #email-marketing-cart-wishlist-root .input-group > .form-control,
    #email-marketing-cart-wishlist-root .input-group > .form-select {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Typography */

    #email-marketing-cart-wishlist-root .font-monospace,
    #email-marketing-cart-wishlist-root .bn-digit {
        font-weight: 600;
    }

    /* Main Header */

    .cart-header-wrap {
        min-width: 0;
    }

    .cart-page-title {
        font-size: 19px;
        font-weight: 750;
        letter-spacing: -0.45px;
        color: #0f172a;
    }

    .cart-page-subtitle {
        color: #64748b;
        font-size: 12px;
        line-height: 1.5;
    }

    .cart-header-icon {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff7ed;
        color: #ea580c;
        border: 1px solid #fed7aa;
        border-radius: 5px;
        flex-shrink: 0;
    }

    /* Compact Buttons */

    #email-marketing-cart-wishlist-root .btn-compact {
        min-height: 32px;
        padding: 5px 11px !important;
        font-size: 11.5px !important;
        line-height: 1.35 !important;
        font-weight: 650 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #email-marketing-cart-wishlist-root .btn-compact:hover {
        transform: translateY(-1px);
    }

    #email-marketing-cart-wishlist-root .btn:focus,
    #email-marketing-cart-wishlist-root .form-control:focus,
    #email-marketing-cart-wishlist-root .form-select:focus {
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10) !important;
    }

    /* General Cards */

    #email-marketing-cart-wishlist-root .card {
        background: #ffffff;
        border: 1px solid var(--cart-border) !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
        margin-bottom: 15px;
        overflow: hidden;
    }

    .section-header-wrap {
        min-height: 43px;
        padding: 9px 13px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #ffffff;
        border-bottom: 1px solid var(--cart-border);
    }

    .section-heading {
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        margin: 0;
    }

    .collapse-toggle-btn {
        width: 27px;
        height: 27px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        border: 1px solid #dbe3ed;
        color: #64748b;
        transition: all .2s ease;
    }

    .collapse-toggle-btn:hover {
        background: #eff6ff;
        color: #2563eb;
        border-color: #bfdbfe;
    }

    .collapse-icon {
        font-size: 10px;
        transition: transform .25s ease;
    }

    .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* =========================================================
       ANALYTICS CARDS
    ========================================================= */

    .metric-card {
        min-height: 108px;
        height: 100%;
        padding: 12px 13px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(15, 23, 42, .07);
    }

    .metric-card::after {
        content: "";
        position: absolute;
        width: 65px;
        height: 65px;
        right: -25px;
        bottom: -30px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .55);
        pointer-events: none;
    }

    .metric-card-amber {
        background: linear-gradient(135deg, #ffffff, #fffbeb);
        border-left: 3px solid #f59e0b !important;
    }

    .metric-card-green {
        background: linear-gradient(135deg, #ffffff, #f0fdf4);
        border-left: 3px solid #10b981 !important;
    }

    .metric-card-blue {
        background: linear-gradient(135deg, #ffffff, #eff6ff);
        border-left: 3px solid #3b82f6 !important;
    }

    .metric-card-cyan {
        background: linear-gradient(135deg, #ffffff, #ecfeff);
        border-left: 3px solid #06b6d4 !important;
    }

    .metric-card-emerald {
        background: linear-gradient(135deg, #ffffff, #ecfdf5);
        border-left: 3px solid #059669 !important;
    }

    .stat-label-title {
        font-size: 10.5px;
        font-weight: 700;
        color: #64748b;
        letter-spacing: .025em;
        text-transform: uppercase;
        line-height: 1.35;
    }

    .stat-val-num {
        font-size: 19px;
        font-weight: 750;
        letter-spacing: -.5px;
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #0f172a;
    }

    .stat-sub-text {
        font-size: 10px;
        margin-top: 3px;
        color: #94a3b8;
    }

    .metric-icon-square {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
        border-radius: 5px !important;
    }

    /* =========================================================
       FILTER TOOLBAR
    ========================================================= */

    .cart-filter-card {
        background: #f8fafc !important;
    }

    .cart-filter-control {
        height: 34px !important;
        min-height: 34px !important;
        padding: 5px 9px !important;
        border: 1px solid #dbe3ed !important;
        background-color: #ffffff !important;
        color: #334155 !important;
        font-size: 11.5px !important;
        font-weight: 500;
        box-shadow: none !important;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .cart-filter-control::placeholder {
        color: #94a3b8;
    }

    .cart-filter-control:focus {
        border-color: #f59e0b !important;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, .12) !important;
    }

    .cart-addon-text {
        height: 34px !important;
        background: #f1f5f9 !important;
        border-color: #dbe3ed !important;
        color: #64748b !important;
        font-size: 10.5px !important;
        white-space: nowrap;
    }

    .filter-label {
        color: #64748b;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
        margin-bottom: 5px;
        display: block;
    }

    /* Quick Placeholder Chips */

    .tag-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
        font-size: 10px;
        font-weight: 650;
        color: #475569;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        user-select: none;
        transition: all .15s ease;
    }

    .tag-chip:hover {
        background: #fff7ed;
        border-color: #fdba74;
        color: #c2410c;
    }

    /* =========================================================
       TABLE
    ========================================================= */

    .cart-table-container {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 5px !important;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
    }

    .custom-carts-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .custom-carts-table thead th {
        padding: 11px 8px;
        background: #f8fafc;
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
        font-size: 10px;
        font-weight: 750;
        letter-spacing: .035em;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    .custom-carts-table tbody td {
        padding: 10px 8px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        font-size: 11.5px;
        vertical-align: middle;
        white-space: nowrap;
    }

    .custom-carts-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .custom-carts-table tbody tr {
        transition: background .15s ease;
    }

    .custom-carts-table tbody tr:hover td {
        background: #f8fafc;
    }

    .custom-carts-table .form-check-input {
        width: 15px;
        height: 15px;
        margin: 0;
        cursor: pointer;
        border-color: #cbd5e1;
        border-radius: 4px !important;
        box-shadow: none !important;
    }

    .custom-carts-table .form-check-input:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    @media (min-width: 992px) {
        .custom-carts-table {
            table-layout: fixed !important;
        }

        .pc-fit-container {
            overflow-x: hidden !important;
        }

        .custom-carts-table th,
        .custom-carts-table td {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .col-cb-select { width: 3% !important; }
        .col-cb-id     { width: 5% !important; }
        .col-cb-user   { width: 22% !important; }
        .col-cb-items  { width: 15% !important; }
        .col-cb-val    { width: 12% !important; }
        .col-cb-status { width: 12% !important; }
        .col-cb-sent   { width: 10% !important; }
        .col-cb-date   { width: 10% !important; }
        .col-cb-action { width: 11% !important; }
    }

    /* =========================================================
       PAGINATION
    ========================================================= */

    #carts-pagination-links nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }

    #carts-pagination-links nav > div.d-sm-none,
    #carts-pagination-links nav > .d-sm-none,
    #carts-pagination-links nav p,
    #carts-pagination-links nav .small.text-muted,
    #carts-pagination-links nav div:has(> p) {
        display: none !important;
    }

    #carts-pagination-links .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 3px !important;
        padding: 0 !important;
        list-style: none !important;
    }

    #carts-pagination-links .page-item {
        display: inline-flex !important;
    }

    #carts-pagination-links .page-item .page-link,
    #carts-pagination-links .page-item span {
        min-width: 27px !important;
        height: 27px !important;
        padding: 0 7px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: #ffffff !important;
        color: #475569 !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 5px !important;
        font-size: 10.5px !important;
        font-weight: 650 !important;
        line-height: 1 !important;
        text-decoration: none !important;
    }

    #carts-pagination-links .page-item .page-link:hover {
        background: #eff6ff !important;
        border-color: #bfdbfe !important;
        color: #2563eb !important;
    }

    #carts-pagination-links .page-item.active .page-link,
    #carts-pagination-links .page-item.active span {
        background: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
    }

    #carts-pagination-links .page-item.disabled .page-link,
    #carts-pagination-links .page-item.disabled span {
        opacity: .45 !important;
        cursor: not-allowed !important;
    }

    /* =========================================================
       MODALS
    ========================================================= */

    .recovery-modal-scope .modal-content {
        border: 1px solid #e2e8f0;
        overflow: hidden;
        box-shadow: 0 15px 45px rgba(15, 23, 42, .16);
    }

    .recovery-modal-scope .modal-header,
    .recovery-modal-scope .modal-footer {
        background: #f8fafc;
        border-color: #e2e8f0 !important;
    }

    .recovery-modal-scope .modal-title {
        font-size: 13px;
        font-weight: 750;
    }

    .recovery-modal-scope .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #475569;
        margin-bottom: 6px;
    }

    .recovery-modal-scope .form-control {
        font-size: 12px;
        border-color: #dbe3ed;
        box-shadow: none;
    }

    .recovery-modal-scope .form-control:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .09);
    }

    /* =========================================================
       TOAST
    ========================================================= */

    .admin-cart-toast {
        position: relative;
        width: 100%;
        min-width: 260px;
        max-width: 380px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .18);
        animation: cartToastIn .25s ease;
    }

    #custom-toast-holder {
        position: fixed;
        bottom: 18px;
        right: 18px;
        z-index: 108000;
        display: flex;
        flex-direction: column;
        gap: 9px;
        max-width: calc(100vw - 36px);
    }

    @keyframes cartToastIn {
        from {
            transform: translateX(25px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    /* Loading */

    .cart-loading {
        opacity: .45;
        pointer-events: none;
        transition: opacity .2s ease;
    }

    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 991.98px) {
        .custom-carts-table {
            min-width: 820px;
            table-layout: auto !important;
        }

        .pc-fit-container {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
    }

    @media (max-width: 767.98px) {
        #email-marketing-cart-wishlist-root {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .cart-page-title {
            font-size: 16px;
        }

        .cart-page-subtitle {
            font-size: 11px;
        }

        .cart-header-icon {
            width: 31px;
            height: 31px;
        }

        .header-actions-wrap {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 7px;
        }

        .header-actions-wrap .btn {
            width: 100%;
            min-height: 34px;
            font-size: 10.5px !important;
        }

        .metric-card {
            min-height: 88px;
            padding: 9px !important;
        }

        .stat-label-title {
            font-size: 9px;
        }

        .stat-val-num {
            font-size: 15px;
        }

        .stat-sub-text {
            font-size: 9px;
        }

        .metric-icon-square {
            width: 27px;
            height: 27px;
            font-size: 11px;
        }

        .section-header-wrap {
            padding: 9px 10px;
        }

        .section-heading {
            font-size: 10.5px;
        }

        .bulk-actions-wrap {
            width: 100%;
        }

        .bulk-actions-wrap .btn {
            flex: 1;
            font-size: 10px !important;
            padding: 6px 7px !important;
        }

        .bulk-summary {
            width: 100%;
            text-align: right;
        }

        #carts-pagination-links nav {
            justify-content: center !important;
        }

        #custom-toast-holder {
            left: 10px;
            right: 10px;
            bottom: 12px;
            max-width: none;
        }

        .admin-cart-toast {
            min-width: 0;
            max-width: none;
        }
    }

    @media (max-width: 420px) {
        .metric-card {
            min-height: 82px;
        }

        .stat-val-num {
            font-size: 13px;
        }

        .cart-filter-control {
            font-size: 10.5px !important;
        }

        .custom-carts-table {
            min-width: 780px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative"
     id="email-marketing-cart-wishlist-root">

    {{-- =====================================================
         1. PAGE HEADER
    ====================================================== --}}

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3 cart-header-wrap">

        <div class="d-flex align-items-center gap-2">

            <div class="cart-header-icon">
                <i class="fas fa-cart-shopping"></i>
            </div>

            <div class="min-w-0">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h5 class="cart-page-title mb-0">
                        Abandoned Cart Recovery
                    </h5>

                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle font-monospace px-2 py-1"
                          id="carts-total-badge"
                          style="font-size: 10px;">
                        {{ number_format($totalCartsCount) }} Sessions
                    </span>
                </div>

                <p class="cart-page-subtitle mb-0 mt-1">
                    Monitor abandoned carts, send recovery emails and track converted revenue.
                </p>
            </div>
        </div>

        <div class="d-flex gap-2 header-actions-wrap">

            <button type="button"
                    class="btn btn-sm btn-outline-success btn-compact bg-white shadow-sm"
                    onclick="exportCartsCsv()">
                <i class="fas fa-file-csv"></i>
                Export CSV
            </button>

            <a href="{{ $automationsIndexRoute }}"
               class="btn btn-sm btn-outline-primary btn-compact bg-white shadow-sm">
                <i class="fas fa-robot"></i>
                Automations
            </a>

            <a href="{{ $dashboardRoute }}"
               class="btn btn-sm btn-outline-secondary btn-compact bg-white shadow-sm">
                <i class="fas fa-chart-pie"></i>
                Dashboard
            </a>

        </div>
    </div>

    {{-- =====================================================
         2. ANALYTICS
    ====================================================== --}}

    <div class="card mb-3">

        <div class="section-header-wrap">

            <h6 class="section-heading">
                <i class="fas fa-chart-line text-warning me-1"></i>
                Recovery Performance Overview
            </h6>

            <button type="button"
                    class="collapse-toggle-btn shadow-sm"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseCartKpis"
                    aria-expanded="true"
                    aria-controls="collapseCartKpis"
                    title="Toggle Analytics">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>

        </div>

        <div class="collapse show" id="collapseCartKpis">

            <div class="p-3">

                <div class="row g-2">

                    {{-- Total Abandoned --}}

                    <div class="col-6 col-xl">

                        <div class="card metric-card metric-card-amber mb-0">

                            <div class="d-flex align-items-center justify-content-between gap-1 mb-2">

                                <span class="stat-label-title text-truncate">
                                    Total Abandoned
                                </span>

                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis">
                                    <i class="fas fa-cart-arrow-down"></i>
                                </div>

                            </div>

                            <div>
                                <h4 class="stat-val-num mb-0 font-monospace"
                                    id="stat-total-abandoned">
                                    {{ number_format($stats['total'] ?? 0) }}
                                </h4>

                                <small class="stat-sub-text d-block">
                                    Recorded sessions
                                </small>
                            </div>

                        </div>
                    </div>

                    {{-- Recovered Orders --}}

                    <div class="col-6 col-xl">

                        <div class="card metric-card metric-card-green mb-0">

                            <div class="d-flex align-items-center justify-content-between gap-1 mb-2">

                                <span class="stat-label-title text-truncate">
                                    Recovered Orders
                                </span>

                                <div class="metric-icon-square bg-success-subtle text-success">
                                    <i class="fas fa-circle-check"></i>
                                </div>

                            </div>

                            <div>
                                <h4 class="stat-val-num text-success mb-0 font-monospace"
                                    id="stat-recovered-orders">
                                    {{ number_format($stats['recovered'] ?? 0) }}
                                </h4>

                                <small class="stat-sub-text d-block text-success">
                                    Successfully converted
                                </small>
                            </div>

                        </div>
                    </div>

                    {{-- Pending Recovery --}}

                    <div class="col-6 col-xl">

                        <div class="card metric-card metric-card-blue mb-0">

                            <div class="d-flex align-items-center justify-content-between gap-1 mb-2">

                                <span class="stat-label-title text-truncate">
                                    Pending Recovery
                                </span>

                                <div class="metric-icon-square bg-primary-subtle text-primary">
                                    <i class="fas fa-clock"></i>
                                </div>

                            </div>

                            <div>
                                <h4 class="stat-val-num text-primary mb-0 font-monospace"
                                    id="stat-pending-recovery">
                                    {{ number_format($stats['pending'] ?? 0) }}
                                </h4>

                                <small class="stat-sub-text d-block">
                                    Open opportunities
                                </small>
                            </div>

                        </div>
                    </div>

                    {{-- Monitored Value --}}

                    <div class="col-6 col-xl">

                        <div class="card metric-card metric-card-cyan mb-0">

                            <div class="d-flex align-items-center justify-content-between gap-1 mb-2">

                                <span class="stat-label-title text-truncate">
                                    Monitored Value
                                </span>

                                <div class="metric-icon-square bg-info-subtle text-info">
                                    <i class="fas fa-coins"></i>
                                </div>

                            </div>

                            <div>
                                <h4 class="stat-val-num mb-0 font-monospace"
                                    id="stat-monitored-value">
                                    ৳ {{ number_format($stats['monitored_value'] ?? 0, 2) }}
                                </h4>

                                <small class="stat-sub-text d-block">
                                    Unrecovered value
                                </small>
                            </div>

                        </div>
                    </div>

                    {{-- Recovered Revenue --}}

                    <div class="col-12 col-xl">

                        <div class="card metric-card metric-card-emerald mb-0">

                            <div class="d-flex align-items-center justify-content-between gap-1 mb-2">

                                <span class="stat-label-title text-truncate">
                                    Recovered Sales
                                </span>

                                <div class="metric-icon-square bg-success-subtle text-success">
                                    <i class="fas fa-bag-shopping"></i>
                                </div>

                            </div>

                            <div>
                                <h4 class="stat-val-num text-success mb-0 font-monospace"
                                    id="stat-recovered-revenue">
                                    ৳ {{ number_format($stats['recovered_revenue'] ?? 0, 2) }}
                                </h4>

                                <small class="stat-sub-text d-block text-success">
                                    Direct converted sales
                                </small>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- =====================================================
         3. FILTER PANEL
    ====================================================== --}}

    <div class="card cart-filter-card mb-3">

        <div class="section-header-wrap">

            <h6 class="section-heading">
                <i class="fas fa-filter text-primary me-1"></i>
                Search & Advanced Filters
            </h6>

            <button type="button"
                    class="collapse-toggle-btn shadow-sm"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseCartFilters"
                    aria-expanded="true"
                    aria-controls="collapseCartFilters"
                    title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>

        </div>

        <div class="collapse show" id="collapseCartFilters">

            <div class="p-3">

                <form id="cart-filter-form" onsubmit="return false;">

                    {{-- Main Filters --}}

                    <div class="row g-2">

                        <div class="col-12 col-md-4">

                            <label class="filter-label">
                                Search Customers
                            </label>

                            <div class="input-group">

                                <span class="input-group-text cart-addon-text border-end-0">
                                    <i class="fas fa-search"></i>
                                </span>

                                <input type="text"
                                       name="search"
                                       id="filter-search-input"
                                       class="form-control cart-filter-control border-start-0 ps-0"
                                       placeholder="Search customer, email, phone...">

                            </div>
                        </div>

                        <div class="col-6 col-md-2">

                            <label class="filter-label">
                                Recovery Status
                            </label>

                            <select name="status"
                                    id="filter-status"
                                    class="form-select cart-filter-control filter-trigger">

                                <option value="">All Carts</option>
                                <option value="pending">Pending Recovery</option>
                                <option value="recovered">Recovered</option>

                            </select>
                        </div>

                        <div class="col-6 col-md-2">

                            <label class="filter-label">
                                Reminder Status
                            </label>

                            <select name="reminder_status"
                                    id="filter-reminder-status"
                                    class="form-select cart-filter-control filter-trigger">

                                <option value="">All Reminders</option>
                                <option value="never_sent">Never Sent</option>
                                <option value="sent">Already Sent</option>

                            </select>
                        </div>

                        <div class="col-6 col-md-2">

                            <label class="filter-label">
                                Sort By
                            </label>

                            <select name="sort_by"
                                    id="filter-sort-by"
                                    class="form-select cart-filter-control filter-trigger">

                                <option value="latest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                                <option value="value_high">Value: High to Low</option>
                                <option value="value_low">Value: Low to High</option>
                                <option value="most_reminders">Most Reminders</option>

                            </select>
                        </div>

                        <div class="col-6 col-md-2">

                            <label class="filter-label">
                                Records Per Page
                            </label>

                            <select name="per_page"
                                    id="filter-per-page"
                                    class="form-select cart-filter-control filter-trigger fw-bold">

                                <option value="10">10 Records</option>
                                <option value="25" selected>25 Records</option>
                                <option value="50">50 Records</option>
                                <option value="100">100 Records</option>
                                <option value="all">Show All</option>

                            </select>
                        </div>

                    </div>

                    {{-- Date Filter --}}

                    <div class="row g-2 align-items-end mt-2 pt-3 border-top">

                        <div class="col-12 col-md-4">

                            <label class="filter-label">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Abandoned Date
                            </label>

                            <select name="date_filter"
                                    id="filter-date-select"
                                    class="form-select cart-filter-control">

                                <option value="">All Time</option>
                                <option value="today">Today</option>
                                <option value="yesterday">Yesterday</option>
                                <option value="this_week">This Week</option>
                                <option value="last_week">Last Week</option>
                                <option value="this_month">This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="this_year">This Year</option>
                                <option value="specific_date">Specific Date...</option>
                                <option value="specific_month">Specific Month...</option>
                                <option value="specific_year">Specific Year...</option>
                                <option value="custom_range">Custom Date Range...</option>

                            </select>
                        </div>

                        <div class="col-12 col-md-8 d-flex justify-content-md-end">

                            <button type="button"
                                    class="btn btn-light border btn-compact text-secondary shadow-sm"
                                    onclick="resetAllFilters()">

                                <i class="fas fa-rotate-left"></i>
                                Reset All Filters

                            </button>

                        </div>
                    </div>

                    {{-- Specific Date --}}

                    <div id="singleDateWrapper"
                         class="row g-2 align-items-end mt-2 pt-3 border-top d-none">

                        <div class="col-12 col-sm-auto">

                            <label class="filter-label">
                                Specific Date
                            </label>

                            <input type="date"
                                   name="specific_date"
                                   id="filter-specific-date"
                                   class="form-control cart-filter-control">

                        </div>

                        <div class="col-12 col-sm-auto d-flex gap-2">

                            <button type="button"
                                    class="btn btn-warning text-dark btn-compact fw-bold"
                                    onclick="fetchFilteredCarts(1)">
                                Apply Date
                            </button>

                            <button type="button"
                                    class="btn btn-light border btn-compact"
                                    onclick="resetDatePanels()">
                                Clear
                            </button>

                        </div>
                    </div>

                    {{-- Specific Month --}}

                    <div id="specificMonthWrapper"
                         class="row g-2 align-items-end mt-2 pt-3 border-top d-none">

                        <div class="col-12 col-sm-auto">

                            <label class="filter-label">
                                Specific Month
                            </label>

                            <input type="month"
                                   name="specific_month"
                                   id="filter-specific-month"
                                   class="form-control cart-filter-control">

                        </div>

                        <div class="col-12 col-sm-auto d-flex gap-2">

                            <button type="button"
                                    class="btn btn-warning text-dark btn-compact fw-bold"
                                    onclick="fetchFilteredCarts(1)">
                                Apply Month
                            </button>

                            <button type="button"
                                    class="btn btn-light border btn-compact"
                                    onclick="resetDatePanels()">
                                Clear
                            </button>

                        </div>
                    </div>

                    {{-- Specific Year --}}

                    <div id="specificYearWrapper"
                         class="row g-2 align-items-end mt-2 pt-3 border-top d-none">

                        <div class="col-12 col-sm-auto">

                            <label class="filter-label">
                                Specific Year
                            </label>

                            <input type="number"
                                   min="2020"
                                   max="2035"
                                   name="specific_year"
                                   id="filter-specific-year"
                                   class="form-control cart-filter-control"
                                   placeholder="{{ date('Y') }}">

                        </div>

                        <div class="col-12 col-sm-auto d-flex gap-2">

                            <button type="button"
                                    class="btn btn-warning text-dark btn-compact fw-bold"
                                    onclick="fetchFilteredCarts(1)">
                                Apply Year
                            </button>

                            <button type="button"
                                    class="btn btn-light border btn-compact"
                                    onclick="resetDatePanels()">
                                Clear
                            </button>

                        </div>
                    </div>

                    {{-- Custom Range --}}

                    <div id="customRangeWrapper"
                         class="row g-2 align-items-end mt-2 pt-3 border-top d-none">

                        <div class="col-6 col-sm-auto">

                            <label class="filter-label">
                                From Date
                            </label>

                            <input type="date"
                                   name="start_date"
                                   id="filter-start-date"
                                   class="form-control cart-filter-control">

                        </div>

                        <div class="col-6 col-sm-auto">

                            <label class="filter-label">
                                To Date
                            </label>

                            <input type="date"
                                   name="end_date"
                                   id="filter-end-date"
                                   class="form-control cart-filter-control">

                        </div>

                        <div class="col-12 col-sm-auto d-flex gap-2">

                            <button type="button"
                                    class="btn btn-warning text-dark btn-compact fw-bold"
                                    onclick="fetchFilteredCarts(1)">
                                Apply Range
                            </button>

                            <button type="button"
                                    class="btn btn-light border btn-compact"
                                    onclick="resetDatePanels()">
                                Clear
                            </button>

                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- =====================================================
         4. BULK ACTIONS
    ====================================================== --}}

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 px-1">

        <div class="d-flex flex-wrap align-items-center gap-2 bulk-actions-wrap">

            <button type="button"
                    class="btn btn-sm btn-outline-warning text-dark btn-compact bg-white shadow-sm"
                    onclick="openBulkRecoveryModal()">

                <i class="fas fa-paper-plane text-warning"></i>
                Bulk Send Recovery

            </button>

            <button type="button"
                    class="btn btn-sm btn-outline-danger btn-compact bg-white shadow-sm"
                    onclick="openBulkDeleteModal()">

                <i class="fas fa-trash-can"></i>
                Bulk Delete

            </button>

            <span class="badge bg-warning text-dark font-monospace d-none px-2 py-2"
                  id="selected-carts-badge"
                  style="font-size: 10px;">
                0 selected
            </span>

        </div>

        <div class="text-muted font-monospace small bulk-summary"
             style="font-size: 10.5px;">

            <i class="fas fa-list-ul me-1"></i>

            <span id="showing-entries-text">
                Showing
                {{ method_exists($abandonedCarts, 'firstItem') ? ($abandonedCarts->firstItem() ?? 0) : 1 }}
                to
                {{ method_exists($abandonedCarts, 'lastItem') ? ($abandonedCarts->lastItem() ?? 0) : $abandonedCarts->count() }}
                of
                {{ number_format($totalCartsCount) }} carts
            </span>

        </div>
    </div>

    {{-- =====================================================
         5. ABANDONED CARTS TABLE
    ====================================================== --}}

    <div class="cart-table-container mb-3">

        <div class="table-responsive pc-fit-container"
             style="min-height: 250px;">

            <table class="table custom-carts-table align-middle"
                   id="abandoned-carts-main-table">

                <thead>
                    <tr>

                        <th class="text-center col-cb-select">
                            <input type="checkbox"
                                   class="form-check-input"
                                   id="check-all-carts"
                                   title="Select All">
                        </th>

                        <th class="text-center col-cb-id">
                            #ID
                        </th>

                        <th class="col-cb-user">
                            Customer / Contact
                        </th>

                        <th class="col-cb-items">
                            Cart Items
                        </th>

                        <th class="col-cb-val">
                            Cart Value
                        </th>

                        <th class="text-center col-cb-status">
                            Recovery Status
                        </th>

                        <th class="text-center col-cb-sent">
                            Reminders
                        </th>

                        <th class="col-cb-date">
                            Abandoned Date
                        </th>

                        <th class="text-end pe-3 col-cb-action">
                            Actions
                        </th>

                    </tr>
                </thead>

                <tbody id="abandoned-carts-table-tbody">

                    @include('admin.email_marketing.cart_wishlist_partials.table_rows', [
                        'abandonedCarts' => $abandonedCarts
                    ])

                </tbody>

            </table>

        </div>
    </div>

    {{-- =====================================================
         6. PAGINATION
    ====================================================== --}}

    <div class="d-flex justify-content-center justify-content-md-end mb-4"
         id="carts-pagination-footer"
         style="{{ method_exists($abandonedCarts, 'hasPages') && $abandonedCarts->hasPages() ? '' : 'display: none;' }}">

        <div id="carts-pagination-links">

            @if(method_exists($abandonedCarts, 'hasPages') && $abandonedCarts->hasPages())
                {{ $abandonedCarts->links('pagination::bootstrap-5') }}
            @endif

        </div>

    </div>

</div>

{{-- =========================================================
     MODAL 1: BULK RECOVERY EMAIL
========================================================= --}}

<div class="modal fade recovery-modal-scope"
     id="bulkRecoveryModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 106000;">

    <div class="modal-dialog modal-dialog-centered modal-lg">

        <div class="modal-content">

            <div class="modal-header py-3 px-3">

                <h6 class="modal-title d-flex align-items-center gap-2 mb-0">

                    <span class="text-warning">
                        <i class="fas fa-paper-plane"></i>
                    </span>

                    Bulk Cart Recovery Email

                </h6>

                <button type="button"
                        class="btn-close shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>

            </div>

            <form id="bulkRecoveryForm"
                  onsubmit="submitBulkRecoveryForm(event)">

                <div class="modal-body p-3">

                    <div class="alert alert-warning border-0 py-2 px-3 mb-3 d-flex align-items-center gap-2">

                        <i class="fas fa-cart-shopping fs-5"></i>

                        <div class="small">
                            Selected Sessions:
                            <strong id="bulk-modal-cart-count"
                                    class="font-monospace">
                                0
                            </strong>
                            cart(s).
                        </div>

                    </div>

                    <div class="mb-3">

                        <label class="form-label"
                               for="bulk-recovery-subject">

                            Email Subject
                            <span class="text-danger">*</span>

                        </label>

                        <input type="text"
                               id="bulk-recovery-subject"
                               class="form-control font-monospace"
                               placeholder="Complete your order at Shop Now BD!"
                               required>

                    </div>

                    <div class="mb-2">

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">

                            <label class="form-label mb-0"
                                   for="bulk-recovery-body">

                                Email Message
                                <span class="text-danger">*</span>

                            </label>

                            <div class="d-flex flex-wrap gap-1">

                                <span class="tag-chip font-monospace"
                                      onclick="insertPlaceholderToBody('{name}')">
                                    +{name}
                                </span>

                                <span class="tag-chip font-monospace"
                                      onclick="insertPlaceholderToBody('{cart_value}')">
                                    +{cart_value}
                                </span>

                                <span class="tag-chip font-monospace"
                                      onclick="insertPlaceholderToBody('{recovery_url}')">
                                    +{recovery_url}
                                </span>

                                <span class="tag-chip font-monospace"
                                      onclick="insertPlaceholderToBody('{store_name}')">
                                    +{store_name}
                                </span>

                            </div>

                        </div>

                        <textarea id="bulk-recovery-body"
                                  rows="7"
                                  class="form-control font-monospace"
                                  style="font-size: 11.5px;"
                                  placeholder="Hello {name},

We noticed you left items in your shopping cart valued at ৳ {cart_value}.

Complete your purchase:
{recovery_url}"
                                  required></textarea>

                        <small class="text-muted d-block mt-2"
                               style="font-size: 10px;">

                            <i class="fas fa-circle-info text-primary me-1"></i>

                            A stylized Complete Purchase button is automatically appended if {recovery_url} is omitted.

                        </small>

                    </div>

                </div>

                <div class="modal-footer py-2 px-3">

                    <button type="button"
                            class="btn btn-light border btn-compact"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-warning text-dark btn-compact fw-bold px-3"
                            id="btn-submit-bulk-recovery">

                        <i class="fas fa-paper-plane me-1"></i>
                        Send Reminders

                    </button>

                </div>

            </form>

        </div>
    </div>
</div>

{{-- =========================================================
     MODAL 2: DELETE CONFIRMATION
========================================================= --}}

<div class="modal fade recovery-modal-scope"
     id="customConfirmModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 106500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content text-center p-3">

            <div class="modal-body p-2">

                <div class="d-inline-flex align-items-center justify-content-center mb-3 bg-danger-subtle text-danger"
                     style="width: 48px; height: 48px; border-radius: 5px;">

                    <i class="fas fa-trash-can fs-4"></i>

                </div>

                <h6 class="fw-bold text-dark mb-2"
                    id="confirm-modal-title"
                    style="font-size: 14px;">

                    Delete Cart Session

                </h6>

                <p class="text-secondary small mb-3"
                   id="confirm-modal-msg"
                   style="font-size: 11px; line-height: 1.5;">

                    Are you sure you want to proceed?

                </p>

                <div class="d-flex justify-content-center gap-2">

                    <button type="button"
                            class="btn btn-light border btn-compact"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="button"
                            class="btn btn-danger btn-compact fw-bold"
                            id="btn-confirm-action-execute">

                        <i class="fas fa-trash-can me-1"></i>
                        Yes, Delete

                    </button>

                </div>

            </div>
        </div>
    </div>
</div>

{{-- TOAST HOLDER --}}

<div id="custom-toast-holder" aria-live="polite"></div>

{{-- =========================================================
     JAVASCRIPT
========================================================= --}}

<script>
(function () {
    'use strict';

    let searchTimeout = null;
    let pendingConfirmCallback = null;
    let isFetchingCarts = false;

    const indexUrl            = @json($indexUrl);
    const exportCsvUrl        = @json($exportCsvUrl);
    const sendRecoveryBaseUrl = @json($sendRecoveryBaseUrl);
    const bulkSendUrl         = @json($bulkSendUrl);
    const deleteBaseUrl       = @json($deleteBaseUrl);
    const bulkDeleteUrl       = @json($bulkDeleteUrl);
    const csrfToken           = @json(csrf_token());

    const root = document.getElementById('email-marketing-cart-wishlist-root');

    if (!root) return;

    const getElement = (id) => document.getElementById(id);

    /* =====================================================
       1. FETCH FILTERED CARTS
    ====================================================== */

    function fetchFilteredCarts(page = 1) {

        const form = getElement('cart-filter-form');

        if (!form || isFetchingCarts) return;

        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        params.set('page', page);

        const tbody = getElement('abandoned-carts-table-tbody');

        if (tbody) {
            tbody.classList.add('cart-loading');
        }

        isFetchingCarts = true;

        fetch(indexUrl + '?' + params.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(async response => {

            if (!response.ok) {
                throw new Error('Unable to fetch cart records.');
            }

            return response.json();

        })
        .then(data => {

            if (tbody) {
                tbody.innerHTML = data.table_html || '';
            }

            /* Pagination */

            const paginationFooter = getElement('carts-pagination-footer');
            const paginationLinks = getElement('carts-pagination-links');

            const perPage = getElement('filter-per-page')?.value || '25';

            if (paginationLinks && paginationFooter) {

                if (
                    perPage === 'all' ||
                    !data.pagination_html ||
                    data.pagination_html.trim() === ''
                ) {
                    paginationLinks.innerHTML = '';
                    paginationFooter.style.display = 'none';
                } else {
                    paginationLinks.innerHTML = data.pagination_html;
                    paginationFooter.style.display = 'flex';
                }
            }

            /* Analytics */

            if (data.stats) {

                const stats = data.stats;

                const totalEl = getElement('stat-total-abandoned');
                const recoveredEl = getElement('stat-recovered-orders');
                const pendingEl = getElement('stat-pending-recovery');
                const valueEl = getElement('stat-monitored-value');
                const revenueEl = getElement('stat-recovered-revenue');
                const badgeEl = getElement('carts-total-badge');

                const numberFormat = value =>
                    Number(value || 0).toLocaleString();

                const currencyFormat = value =>
                    '৳ ' + Number(value || 0).toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });

                if (totalEl) {
                    totalEl.textContent = numberFormat(stats.total);
                }

                if (recoveredEl) {
                    recoveredEl.textContent = numberFormat(stats.recovered);
                }

                if (pendingEl) {
                    pendingEl.textContent = numberFormat(stats.pending);
                }

                if (valueEl) {
                    valueEl.textContent = currencyFormat(stats.monitored_value);
                }

                if (revenueEl) {
                    revenueEl.textContent = currencyFormat(stats.recovered_revenue);
                }

                if (badgeEl) {
                    badgeEl.textContent = numberFormat(stats.total) + ' Sessions';
                }
            }

            /* Entry Information */

            if (data.entries_text) {

                const entriesEl = getElement('showing-entries-text');

                if (entriesEl) {
                    entriesEl.textContent = data.entries_text;
                }
            }

            /* Reset Select All */

            const checkAll = getElement('check-all-carts');

            if (checkAll) {
                checkAll.checked = false;
                checkAll.indeterminate = false;
            }

            updateSelectionBadge();

        })
        .catch(error => {

            console.error('Cart fetch error:', error);

            showToast(
                'Request Failed',
                'Unable to load cart records. Please try again.',
                'danger'
            );

        })
        .finally(() => {

            isFetchingCarts = false;

            if (tbody) {
                tbody.classList.remove('cart-loading');
            }

        });
    }

    window.fetchFilteredCarts = fetchFilteredCarts;

    /* =====================================================
       2. SEARCH DEBOUNCE
    ====================================================== */

    getElement('filter-search-input')?.addEventListener('input', function () {

        clearTimeout(searchTimeout);

        searchTimeout = setTimeout(() => {
            fetchFilteredCarts(1);
        }, 350);

    });

    root.querySelectorAll('.filter-trigger').forEach(element => {

        element.addEventListener('change', () => {
            fetchFilteredCarts(1);
        });

    });

    /* =====================================================
       3. DATE FILTER SWITCHER
    ====================================================== */

    getElement('filter-date-select')?.addEventListener('change', function () {

        const value = this.value;

        const panels = {
            specific_date: 'singleDateWrapper',
            specific_month: 'specificMonthWrapper',
            specific_year: 'specificYearWrapper',
            custom_range: 'customRangeWrapper'
        };

        Object.values(panels).forEach(id => {
            getElement(id)?.classList.add('d-none');
        });

        if (panels[value]) {

            getElement(panels[value])?.classList.remove('d-none');

        } else {

            fetchFilteredCarts(1);

        }

    });

    /* =====================================================
       4. RESET DATE PANELS
    ====================================================== */

    function resetDatePanels() {

        const fields = [
            'filter-specific-date',
            'filter-specific-month',
            'filter-specific-year',
            'filter-start-date',
            'filter-end-date'
        ];

        fields.forEach(id => {

            const element = getElement(id);

            if (element) {
                element.value = '';
            }

        });

        const dateSelect = getElement('filter-date-select');

        if (dateSelect) {
            dateSelect.value = '';
        }

        [
            'singleDateWrapper',
            'specificMonthWrapper',
            'specificYearWrapper',
            'customRangeWrapper'
        ].forEach(id => {

            getElement(id)?.classList.add('d-none');

        });

        fetchFilteredCarts(1);
    }

    window.resetDatePanels = resetDatePanels;

    function resetAllFilters() {

        getElement('cart-filter-form')?.reset();

        clearTimeout(searchTimeout);

        resetDatePanels();
    }

    window.resetAllFilters = resetAllFilters;

    /* =====================================================
       5. PAGINATION INTERCEPTOR
    ====================================================== */

    document.addEventListener('click', function (event) {

        const pageLink = event.target.closest(
            '#carts-pagination-links .page-link'
        );

        if (!pageLink) return;

        const href = pageLink.getAttribute('href');

        if (!href || href === '#' || href.startsWith('javascript:')) {
            return;
        }

        event.preventDefault();

        try {

            const url = new URL(href, window.location.origin);
            const page = url.searchParams.get('page') || 1;

            fetchFilteredCarts(page);

            document.querySelector('.cart-table-container')?.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });

        } catch (error) {

            console.error('Pagination error:', error);

        }

    });

    /* =====================================================
       6. CHECKBOX SELECTION
    ====================================================== */

    getElement('check-all-carts')?.addEventListener('change', function () {

        root.querySelectorAll('.cart-select-cb').forEach(checkbox => {

            checkbox.checked = this.checked;

        });

        updateSelectionBadge();

    });

    document.addEventListener('change', function (event) {

        if (event.target.classList.contains('cart-select-cb')) {

            updateSelectionBadge();

        }

    });

    function getSelectedCartIds() {

        return Array.from(
            root.querySelectorAll('.cart-select-cb:checked')
        ).map(checkbox => checkbox.value);

    }

    function updateSelectionBadge() {

        const selected = getSelectedCartIds();

        const badge = getElement('selected-carts-badge');
        const checkAll = getElement('check-all-carts');

        if (badge) {

            if (selected.length > 0) {

                badge.textContent = selected.length + ' selected';

                badge.classList.remove('d-none');

            } else {

                badge.classList.add('d-none');

            }
        }

        if (checkAll) {

            const allCheckboxes = root.querySelectorAll('.cart-select-cb');

            const checkedCount = root.querySelectorAll(
                '.cart-select-cb:checked'
            ).length;

            checkAll.checked =
                allCheckboxes.length > 0 &&
                checkedCount === allCheckboxes.length;

            checkAll.indeterminate =
                checkedCount > 0 &&
                checkedCount < allCheckboxes.length;

        }

    }

    /* =====================================================
       7. CSV EXPORT
    ====================================================== */

    function exportCartsCsv() {

        const form = getElement('cart-filter-form');

        if (!form) return;

        const params = new URLSearchParams(new FormData(form));

        window.open(
            exportCsvUrl + '?' + params.toString(),
            '_blank',
            'noopener,noreferrer'
        );

    }

    window.exportCartsCsv = exportCartsCsv;

    /* =====================================================
       8. SINGLE CART RECOVERY
    ====================================================== */

    function sendCartRecoveryDirectAjax(cartId, email) {

        fetch(`${sendRecoveryBaseUrl}/${encodeURIComponent(cartId)}`, {

            method: 'POST',

            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }

        })
        .then(async response => {

            if (!response.ok) {
                throw new Error('Recovery request failed.');
            }

            return response.json();

        })
        .then(data => {

            if (data.success) {

                showToast(
                    'Dispatched',
                    data.message || 'Recovery reminder sent successfully.',
                    'success'
                );

                fetchFilteredCarts(1);

            } else {

                showToast(
                    'Failed',
                    data.message || 'Could not send recovery reminder.',
                    'danger'
                );

            }

        })
        .catch(error => {

            console.error(error);

            showToast(
                'Error',
                'Server or mailer delivery error.',
                'danger'
            );

        });

    }

    window.sendCartRecoveryDirectAjax = sendCartRecoveryDirectAjax;

    /* =====================================================
       9. BULK RECOVERY MODAL
    ====================================================== */

    function openBulkRecoveryModal() {

        const selected = getSelectedCartIds();

        if (selected.length === 0) {

            showToast(
                'Selection Required',
                'Please select at least one cart from the table.',
                'warning'
            );

            return;
        }

        const countEl = getElement('bulk-modal-cart-count');

        if (countEl) {
            countEl.textContent = selected.length;
        }

        const modalEl = getElement('bulkRecoveryModal');

        if (modalEl && window.bootstrap) {

            bootstrap.Modal.getOrCreateInstance(modalEl).show();

        }

    }

    window.openBulkRecoveryModal = openBulkRecoveryModal;

    /* =====================================================
       10. PLACEHOLDER INSERTION
    ====================================================== */

    function insertPlaceholderToBody(tag) {

        const textarea = getElement('bulk-recovery-body');

        if (!textarea) return;

        const start = textarea.selectionStart || 0;
        const end = textarea.selectionEnd || 0;

        const currentText = textarea.value;

        textarea.value =
            currentText.substring(0, start) +
            tag +
            currentText.substring(end);

        textarea.focus();

        const cursorPosition = start + tag.length;

        textarea.setSelectionRange(
            cursorPosition,
            cursorPosition
        );

    }

    window.insertPlaceholderToBody = insertPlaceholderToBody;

    /* =====================================================
       11. SUBMIT BULK RECOVERY
    ====================================================== */

    function submitBulkRecoveryForm(event) {

        event.preventDefault();

        const selected = getSelectedCartIds();

        const subject = getElement('bulk-recovery-subject')?.value.trim();

        const message = getElement('bulk-recovery-body')?.value.trim();

        const button = getElement('btn-submit-bulk-recovery');

        if (selected.length === 0) {

            showToast(
                'Selection Required',
                'Please select at least one cart.',
                'warning'
            );

            return;
        }

        if (!subject || !message) {

            showToast(
                'Required Fields',
                'Email subject and message are required.',
                'warning'
            );

            return;
        }

        if (button) {

            button.disabled = true;

            button.innerHTML =
                '<i class="fas fa-spinner fa-spin me-1"></i> Sending...';

        }

        fetch(bulkSendUrl, {

            method: 'POST',

            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },

            body: JSON.stringify({

                cart_ids: selected,
                subject: subject,
                message: message

            })

        })
        .then(async response => {

            if (!response.ok) {
                throw new Error('Bulk recovery request failed.');
            }

            return response.json();

        })
        .then(data => {

            if (data.success) {

                const modalEl = getElement('bulkRecoveryModal');

                if (modalEl && window.bootstrap) {

                    bootstrap.Modal.getInstance(modalEl)?.hide();

                }

                getElement('bulkRecoveryForm')?.reset();

                showToast(
                    'Success',
                    data.message || 'Recovery emails dispatched successfully.',
                    'success'
                );

                fetchFilteredCarts(1);

            } else {

                showToast(
                    'Dispatch Failed',
                    data.message || 'Failed to dispatch recovery emails.',
                    'danger'
                );

            }

        })
        .catch(error => {

            console.error(error);

            showToast(
                'Server Error',
                'Network or server error while dispatching emails.',
                'danger'
            );

        })
        .finally(() => {

            if (button) {

                button.disabled = false;

                button.innerHTML =
                    '<i class="fas fa-paper-plane me-1"></i> Send Reminders';

            }

        });

    }

    window.submitBulkRecoveryForm = submitBulkRecoveryForm;

    /* =====================================================
       12. CONFIRMATION MODAL
    ====================================================== */

    function showConfirmModal(title, message, callback) {

        const titleEl = getElement('confirm-modal-title');
        const messageEl = getElement('confirm-modal-msg');

        if (titleEl) {
            titleEl.textContent = title;
        }

        if (messageEl) {
            messageEl.textContent = message;
        }

        pendingConfirmCallback = callback;

        const modalEl = getElement('customConfirmModal');

        if (modalEl && window.bootstrap) {

            bootstrap.Modal.getOrCreateInstance(modalEl).show();

        }

    }

    /* =====================================================
       13. SINGLE CART DELETE
    ====================================================== */

    function deleteCartAjax(cartId, email) {

        showConfirmModal(

            'Delete Cart Session',

            `Are you sure you want to delete abandoned session #${cartId} (${email})?`,

            function () {

                const confirmButton = getElement('btn-confirm-action-execute');

                if (confirmButton) {
                    confirmButton.disabled = true;
                }

                fetch(`${deleteBaseUrl}/${encodeURIComponent(cartId)}`, {

                    method: 'DELETE',

                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }

                })
                .then(async response => {

                    if (!response.ok) {
                        throw new Error('Delete request failed.');
                    }

                    return response.json();

                })
                .then(data => {

                    if (data.success === false) {

                        showToast(
                            'Delete Failed',
                            data.message || 'Unable to delete cart session.',
                            'danger'
                        );

                        return;
                    }

                    showToast(
                        'Deleted',
                        data.message || 'Cart session deleted successfully.',
                        'success'
                    );

                    fetchFilteredCarts(1);

                    bootstrap.Modal.getInstance(
                        getElement('customConfirmModal')
                    )?.hide();

                })
                .catch(error => {

                    console.error(error);

                    showToast(
                        'Error',
                        'Unable to delete cart session.',
                        'danger'
                    );

                })
                .finally(() => {

                    if (confirmButton) {
                        confirmButton.disabled = false;
                    }

                });

            }

        );

    }

    window.deleteCartAjax = deleteCartAjax;

    /* =====================================================
       14. BULK DELETE
    ====================================================== */

    function openBulkDeleteModal() {

        const selected = getSelectedCartIds();

        if (selected.length === 0) {

            showToast(
                'Selection Required',
                'Please select at least one cart session.',
                'warning'
            );

            return;
        }

        showConfirmModal(

            'Bulk Delete Sessions',

            `Are you sure you want to delete ${selected.length} selected abandoned cart session(s)?`,

            function () {

                const confirmButton = getElement('btn-confirm-action-execute');

                if (confirmButton) {
                    confirmButton.disabled = true;
                }

                fetch(bulkDeleteUrl, {

                    method: 'POST',

                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },

                    body: JSON.stringify({
                        cart_ids: selected
                    })

                })
                .then(async response => {

                    if (!response.ok) {
                        throw new Error('Bulk delete request failed.');
                    }

                    return response.json();

                })
                .then(data => {

                    if (data.success === false) {

                        showToast(
                            'Delete Failed',
                            data.message || 'Unable to delete selected sessions.',
                            'danger'
                        );

                        return;
                    }

                    showToast(
                        'Deleted',
                        data.message || 'Selected cart sessions deleted.',
                        'success'
                    );

                    fetchFilteredCarts(1);

                    bootstrap.Modal.getInstance(
                        getElement('customConfirmModal')
                    )?.hide();

                })
                .catch(error => {

                    console.error(error);

                    showToast(
                        'Error',
                        'Unable to delete selected cart sessions.',
                        'danger'
                    );

                })
                .finally(() => {

                    if (confirmButton) {
                        confirmButton.disabled = false;
                    }

                });

            }

        );

    }

    window.openBulkDeleteModal = openBulkDeleteModal;

    /* =====================================================
       15. CONFIRM ACTION BUTTON
    ====================================================== */

    getElement('btn-confirm-action-execute')?.addEventListener(
        'click',
        function () {

            if (typeof pendingConfirmCallback === 'function') {

                const callback = pendingConfirmCallback;

                pendingConfirmCallback = null;

                callback();

            }

        }
    );

    /* =====================================================
       16. TOAST NOTIFICATIONS
    ====================================================== */

    function showToast(title, message, type = 'success') {

        const holder = getElement('custom-toast-holder');

        if (!holder) return;

        const configs = {

            success: {
                icon: 'fa-circle-check',
                color: 'bg-success text-white'
            },

            danger: {
                icon: 'fa-circle-xmark',
                color: 'bg-danger text-white'
            },

            warning: {
                icon: 'fa-triangle-exclamation',
                color: 'bg-warning text-dark'
            },

            info: {
                icon: 'fa-circle-info',
                color: 'bg-primary text-white'
            }

        };

        const config = configs[type] || configs.success;

        const toast = document.createElement('div');

        toast.className =
            `admin-cart-toast ${config.color} p-3 d-flex align-items-center justify-content-between gap-2`;

        toast.setAttribute('role', 'status');

        const content = document.createElement('div');

        content.className = 'd-flex align-items-center gap-2';

        const icon = document.createElement('i');

        icon.className = `fas ${config.icon} fs-5`;

        const textWrapper = document.createElement('div');

        const titleElement = document.createElement('strong');

        titleElement.className = 'd-block';

        titleElement.style.fontSize = '12px';

        titleElement.textContent = title;

        const messageElement = document.createElement('small');

        messageElement.style.cssText = 'font-size:11px;opacity:.95;';

        messageElement.textContent = message || '';

        textWrapper.appendChild(titleElement);
        textWrapper.appendChild(messageElement);

        content.appendChild(icon);
        content.appendChild(textWrapper);

        const closeButton = document.createElement('button');

        closeButton.type = 'button';

        closeButton.className =
            `btn-close btn-sm ${type === 'warning' ? '' : 'btn-close-white'}`;

        closeButton.setAttribute('aria-label', 'Close');

        closeButton.addEventListener('click', () => {
            toast.remove();
        });

        toast.appendChild(content);
        toast.appendChild(closeButton);

        holder.appendChild(toast);

        setTimeout(() => {

            if (toast.isConnected) {
                toast.remove();
            }

        }, 4000);

    }

    window.showToast = showToast;

})();
</script>

@endsection