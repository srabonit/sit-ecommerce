@extends('layouts.admin')

@section('title', 'Email Templates - Email Marketing')

@php
    $totalTemplatesCount = $templates->total();
    $promotionalCount    = \App\Models\EmailTemplate::where('category', 'promotional')->count();
    $cartCount           = \App\Models\EmailTemplate::where('category', 'abandoned_cart')->count();
    $transactionalCount  = \App\Models\EmailTemplate::where('category', 'transactional')->count();
    $newsletterCount     = \App\Models\EmailTemplate::where('category', 'newsletter')->count();
@endphp

@section('admin_content')

<style>
    /* =========================================================
       EMAIL TEMPLATE MANAGEMENT - MODERN ADMIN UI
       Strict 5px Radius | Bootstrap 5 | Fully Responsive
    ========================================================= */

    #email-marketing-templates-root {
        --tpl-primary: #2563eb;
        --tpl-primary-dark: #1d4ed8;
        --tpl-text: #172033;
        --tpl-muted: #64748b;
        --tpl-border: #e2e8f0;
        --tpl-soft: #f8fafc;
        --tpl-radius: 5px;
        color: var(--tpl-text);
        font-size: 13px;
    }

    #email-marketing-templates-root *,
    .template-modal-scope *,
    .admin-template-toast * {
        box-sizing: border-box;
    }

    #email-marketing-templates-root .btn,
    #email-marketing-templates-root button,
    #email-marketing-templates-root .badge,
    #email-marketing-templates-root .alert,
    #email-marketing-templates-root .form-control,
    #email-marketing-templates-root .form-select,
    #email-marketing-templates-root .input-group-text,
    #email-marketing-templates-root .card,
    #email-marketing-templates-root .page-link,
    #email-marketing-templates-root .page-item span,
    #email-marketing-templates-root .collapse-toggle-btn,
    .template-modal-scope .modal-content,
    .template-modal-scope .btn,
    .admin-template-toast {
        border-radius: 5px !important;
    }

    #email-marketing-templates-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #email-marketing-templates-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    #email-marketing-templates-root .font-monospace {
        font-weight: 600;
    }

    #email-marketing-templates-root .text-muted {
        color: #64748b !important;
    }

    /* Header */

    #email-marketing-templates-root .templates-header-wrap {
        padding: 2px 0 4px;
    }

    #email-marketing-templates-root .page-title {
        color: #172033;
        font-size: 19px;
        font-weight: 800;
        letter-spacing: -0.45px;
        margin: 0;
    }

    #email-marketing-templates-root .page-description {
        color: #64748b;
        font-size: 12px;
        line-height: 1.5;
        margin-top: 5px;
    }

    #email-marketing-templates-root .btn-compact {
        min-height: 32px;
        padding: 6px 12px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        font-size: 11.5px !important;
        font-weight: 700 !important;
        line-height: 1.2;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #email-marketing-templates-root .btn-compact:hover {
        transform: translateY(-1px);
    }

    #email-marketing-templates-root .btn-primary {
        background: var(--tpl-primary);
        border-color: var(--tpl-primary);
    }

    #email-marketing-templates-root .btn-primary:hover {
        background: var(--tpl-primary-dark);
        border-color: var(--tpl-primary-dark);
    }

    /* Cards */

    #email-marketing-templates-root .card {
        border: 1px solid var(--tpl-border) !important;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
        margin-bottom: 15px;
        overflow: hidden;
    }

    #email-marketing-templates-root .section-header-wrap {
        min-height: 43px;
        padding: 10px 14px;
        background: #ffffff;
        border-bottom: 1px solid var(--tpl-border);
    }

    #email-marketing-templates-root .section-title {
        font-size: 12px;
        font-weight: 800;
        color: #263449;
        margin: 0;
    }

    #email-marketing-templates-root .collapse-toggle-btn {
        width: 26px;
        height: 26px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #dbe2ea;
        background: #ffffff;
        color: #64748b;
        transition: all .2s ease;
        flex-shrink: 0;
    }

    #email-marketing-templates-root .collapse-toggle-btn:hover {
        color: #ffffff;
        background: var(--tpl-primary);
        border-color: var(--tpl-primary);
    }

    #email-marketing-templates-root .collapse-icon {
        font-size: 10px;
        transition: transform .25s ease;
    }

    #email-marketing-templates-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* KPI Cards */

    #email-marketing-templates-root .metric-card {
        height: 100%;
        min-height: 105px;
        padding: 13px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        border: 1px solid var(--tpl-border) !important;
        border-left: 3px solid transparent !important;
        transition: transform .18s ease, box-shadow .18s ease;
    }

    #email-marketing-templates-root .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(15, 23, 42, .07);
    }

    #email-marketing-templates-root .metric-card-blue {
        background: linear-gradient(145deg, #ffffff, #eff6ff);
        border-left-color: #3b82f6 !important;
    }

    #email-marketing-templates-root .metric-card-green {
        background: linear-gradient(145deg, #ffffff, #f0fdf4);
        border-left-color: #10b981 !important;
    }

    #email-marketing-templates-root .metric-card-amber {
        background: linear-gradient(145deg, #ffffff, #fffbeb);
        border-left-color: #f59e0b !important;
    }

    #email-marketing-templates-root .metric-card-cyan {
        background: linear-gradient(145deg, #ffffff, #ecfeff);
        border-left-color: #06b6d4 !important;
    }

    #email-marketing-templates-root .stat-label-title {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        line-height: 1.3;
    }

    #email-marketing-templates-root .stat-val-num {
        font-size: 21px;
        font-weight: 800;
        color: #172033;
        line-height: 1.2;
        letter-spacing: -.5px;
        margin: 0;
    }

    #email-marketing-templates-root .stat-sub-text {
        font-size: 10px;
        color: #64748b;
        margin-top: 3px;
    }

    #email-marketing-templates-root .metric-icon-square {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        border-radius: 5px !important;
        flex-shrink: 0;
    }

    /* Search & Filter */

    #email-marketing-templates-root .filter-card {
        background: #ffffff;
    }

    #email-marketing-templates-root .form-label {
        display: block;
        color: #334155;
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    #email-marketing-templates-root .template-filter-control {
        height: 36px !important;
        padding: 6px 10px !important;
        color: #334155 !important;
        background-color: #ffffff !important;
        border: 1px solid #dbe2ea !important;
        font-size: 12px !important;
        box-shadow: none !important;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #email-marketing-templates-root .template-filter-control:focus {
        border-color: var(--tpl-primary) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10) !important;
    }

    #email-marketing-templates-root .template-addon-text {
        height: 36px !important;
        min-width: 36px;
        justify-content: center;
        background: #f8fafc !important;
        border-color: #dbe2ea !important;
        color: #64748b !important;
        font-size: 12px;
    }

    /* Table */

    #email-marketing-templates-root .pc-fit-container {
        width: 100%;
        overflow-x: hidden;
    }

    #email-marketing-templates-root .custom-templates-table {
        width: 100%;
        margin: 0;
        table-layout: fixed;
    }

    #email-marketing-templates-root .custom-templates-table thead th {
        padding: 11px 9px;
        background: #f8fafc;
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .45px;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }

    #email-marketing-templates-root .custom-templates-table tbody td {
        padding: 10px 9px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        font-size: 11.5px;
        vertical-align: middle;
    }

    #email-marketing-templates-root .custom-templates-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #email-marketing-templates-root .custom-templates-table tbody tr:hover td {
        background: #f8fbff;
    }

    #email-marketing-templates-root .col-tp-id {
        width: 5%;
    }

    #email-marketing-templates-root .col-tp-name {
        width: 25%;
    }

    #email-marketing-templates-root .col-tp-cat {
        width: 14%;
    }

    #email-marketing-templates-root .col-tp-subject {
        width: 26%;
    }

    #email-marketing-templates-root .col-tp-date {
        width: 16%;
    }

    #email-marketing-templates-root .col-tp-actions {
        width: 14%;
    }

    #email-marketing-templates-root .template-name {
        color: #1e293b;
        font-size: 12px;
        font-weight: 750;
        line-height: 1.4;
    }

    #email-marketing-templates-root .template-subtitle {
        color: #94a3b8;
        font-size: 10px;
        margin-top: 3px;
    }

    #email-marketing-templates-root .category-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 8px !important;
        font-size: 9.5px;
        font-weight: 700;
        white-space: nowrap;
        border-radius: 5px !important;
    }

    #email-marketing-templates-root .template-action-btn {
        width: 29px;
        height: 29px;
        padding: 0 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        transition: all .15s ease;
    }

    #email-marketing-templates-root .template-action-btn:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }

    #email-marketing-templates-root .template-action-btn i {
        font-size: 11px;
    }

    /* Table Footer */

    #email-marketing-templates-root .table-footer {
        min-height: 48px;
        background: #fafbfd;
        border-top: 1px solid #e2e8f0;
    }

    #email-marketing-templates-root .pagination {
        margin: 0 !important;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 3px;
    }

    #email-marketing-templates-root .page-link,
    #email-marketing-templates-root .page-item span {
        min-width: 27px;
        height: 27px;
        padding: 0 7px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #475569 !important;
        background: #ffffff !important;
        border: 1px solid #dbe2ea !important;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        box-shadow: none !important;
    }

    #email-marketing-templates-root .page-item.active .page-link,
    #email-marketing-templates-root .page-item.active span {
        color: #ffffff !important;
        background: var(--tpl-primary) !important;
        border-color: var(--tpl-primary) !important;
    }

    #email-marketing-templates-root .page-item.disabled {
        opacity: .45;
    }

    /* Preview & Delete Modals */

    .template-modal-scope .modal-content {
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }

    .template-modal-scope .modal-header {
        min-height: 52px;
    }

    .template-modal-scope .modal-title {
        font-size: 13px;
    }

    .template-modal-scope .modal-body {
        overflow: auto;
    }

    .template-modal-scope .preview-frame {
        width: 100%;
        height: 560px;
        max-width: 100%;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 5px;
        transition: width .2s ease;
    }

    .template-modal-scope .viewport-btn.active {
        background: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
    }

    .template-modal-scope .delete-icon-wrap {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px !important;
    }

    /* Toast */

    .admin-template-toast {
        position: relative;
        width: min(360px, calc(100vw - 30px));
        margin-top: 10px;
        padding: 12px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .18);
        animation: templateToastIn .25s ease;
    }

    #custom-toast-holder {
        position: fixed;
        right: 20px;
        bottom: 20px;
        z-index: 108000;
        pointer-events: none;
    }

    #custom-toast-holder .admin-template-toast {
        pointer-events: auto;
    }

    @keyframes templateToastIn {
        from {
            opacity: 0;
            transform: translateX(20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Responsive */

    @media (min-width: 992px) {
        #email-marketing-templates-root .custom-templates-table th,
        #email-marketing-templates-root .custom-templates-table td {
            overflow: hidden;
            text-overflow: ellipsis;
        }
    }

    @media (max-width: 991.98px) {
        #email-marketing-templates-root .pc-fit-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        #email-marketing-templates-root .custom-templates-table {
            min-width: 850px;
            table-layout: auto;
        }
    }

    @media (max-width: 767.98px) {
        #email-marketing-templates-root {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #email-marketing-templates-root .page-title {
            font-size: 17px;
        }

        #email-marketing-templates-root .top-action-btns {
            display: grid !important;
            width: 100%;
            grid-template-columns: 1fr 1fr;
            gap: 7px !important;
        }

        #email-marketing-templates-root .top-action-btns .btn {
            width: 100%;
            min-height: 36px;
            padding: 7px 5px !important;
            font-size: 11px !important;
        }

        #email-marketing-templates-root .metric-card {
            min-height: 91px;
            padding: 10px;
        }

        #email-marketing-templates-root .stat-label-title {
            font-size: 10px;
        }

        #email-marketing-templates-root .stat-val-num {
            font-size: 17px;
        }

        #email-marketing-templates-root .stat-sub-text {
            font-size: 9px;
        }

        #email-marketing-templates-root .metric-icon-square {
            width: 29px;
            height: 29px;
            font-size: 12px;
        }

        #email-marketing-templates-root .section-header-wrap {
            padding: 9px 11px;
        }

        #email-marketing-templates-root .table-footer {
            align-items: center !important;
        }

        .template-modal-scope .modal-dialog {
            margin: 8px;
        }

        .template-modal-scope .modal-header {
            flex-wrap: wrap;
            gap: 8px;
        }

        .template-modal-scope .modal-header .btn-group {
            flex-wrap: wrap;
        }

        .template-modal-scope .modal-header .btn-compact {
            padding: 5px 7px !important;
            font-size: 10px !important;
        }

        .template-modal-scope .preview-frame {
            height: 70vh;
        }

        #custom-toast-holder {
            left: 10px;
            right: 10px;
            bottom: 10px;
        }

        .admin-template-toast {
            width: 100%;
        }
    }

    @media (max-width: 380px) {
        #email-marketing-templates-root .stat-val-num {
            font-size: 15px;
        }

        #email-marketing-templates-root .metric-icon-square {
            width: 26px;
            height: 26px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative"
     id="email-marketing-templates-root">

    {{-- =====================================================
         1. PAGE HEADER
    ====================================================== --}}

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3 templates-header-wrap">

        <div class="min-w-0">
            <div class="d-flex align-items-center flex-wrap gap-2">

                <h5 class="page-title d-flex align-items-center gap-2">
                    <i class="fas fa-paintbrush text-primary"></i>
                    Email Templates
                </h5>

                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace"
                      id="templates-total-badge"
                      style="font-size:10px;">
                    {{ $totalTemplatesCount }} Templates
                </span>

            </div>

            <p class="page-description mb-0">
                Design, customize, preview, and manage responsive email marketing layouts.
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 top-action-btns">

            <a href="{{ url('admin/email-marketing/campaigns') }}"
               class="btn btn-sm btn-outline-secondary btn-compact">
                <i class="fas fa-paper-plane"></i>
                Campaigns
            </a>

            <a href="{{ url('admin/email-marketing/templates/create') }}"
               class="btn btn-sm btn-primary btn-compact">
                <i class="fas fa-plus-circle"></i>
                Create Template
            </a>

        </div>
    </div>


    {{-- =====================================================
         2. SESSION FEEDBACK
    ====================================================== --}}

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 mb-3 d-flex align-items-center justify-content-between"
             role="alert">

            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle fs-5"></i>
                <span class="fw-semibold small">{{ session('success') }}</span>
            </div>

            <button type="button"
                    class="btn-close shadow-none"
                    data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 mb-3 d-flex align-items-center justify-content-between"
             role="alert">

            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle fs-5"></i>
                <span class="fw-semibold small">{{ session('error') }}</span>
            </div>

            <button type="button"
                    class="btn-close shadow-none"
                    data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif


    {{-- =====================================================
         3. CATEGORY OVERVIEW
    ====================================================== --}}

    <div class="card mb-3">

        <div class="section-header-wrap d-flex justify-content-between align-items-center">

            <h6 class="section-title">
                <i class="fas fa-chart-pie text-primary me-1"></i>
                Template Library Overview
            </h6>

            <button type="button"
                    class="collapse-toggle-btn"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseTemplatesKpis"
                    aria-expanded="true"
                    aria-controls="collapseTemplatesKpis"
                    title="Toggle Overview">

                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>

        </div>

        <div class="collapse show" id="collapseTemplatesKpis">

            <div class="p-3">

                <div class="row g-2">

                    {{-- Total Templates --}}

                    <div class="col-6 col-xl-3">

                        <div class="card metric-card metric-card-blue mb-0">

                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">

                                <span class="stat-label-title text-truncate">
                                    Total Templates
                                </span>

                                <div class="metric-icon-square bg-primary-subtle text-primary">
                                    <i class="fas fa-file-code"></i>
                                </div>

                            </div>

                            <div>
                                <h4 class="stat-val-num font-monospace"
                                    id="stat-total-templates">
                                    {{ $totalTemplatesCount }}
                                </h4>

                                <small class="stat-sub-text">
                                    Available in builder
                                </small>
                            </div>

                        </div>
                    </div>


                    {{-- Promotional --}}

                    <div class="col-6 col-xl-3">

                        <div class="card metric-card metric-card-green mb-0">

                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">

                                <span class="stat-label-title text-truncate">
                                    Promotional
                                </span>

                                <div class="metric-icon-square bg-success-subtle text-success">
                                    <i class="fas fa-tags"></i>
                                </div>

                            </div>

                            <div>
                                <h4 class="stat-val-num text-success font-monospace"
                                    id="stat-promotional-templates">
                                    {{ $promotionalCount }}
                                </h4>

                                <small class="stat-sub-text">
                                    Sales &amp; discount offers
                                </small>
                            </div>

                        </div>
                    </div>


                    {{-- Abandoned Cart --}}

                    <div class="col-6 col-xl-3">

                        <div class="card metric-card metric-card-amber mb-0">

                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">

                                <span class="stat-label-title text-truncate">
                                    Abandoned Cart
                                </span>

                                <div class="metric-icon-square bg-warning-subtle text-warning">
                                    <i class="fas fa-cart-shopping"></i>
                                </div>

                            </div>

                            <div>
                                <h4 class="stat-val-num text-warning-emphasis font-monospace"
                                    id="stat-cart-templates">
                                    {{ $cartCount }}
                                </h4>

                                <small class="stat-sub-text">
                                    Recovery sequences
                                </small>
                            </div>

                        </div>
                    </div>


                    {{-- Transactional --}}

                    <div class="col-6 col-xl-3">

                        <div class="card metric-card metric-card-cyan mb-0">

                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">

                                <span class="stat-label-title text-truncate">
                                    Transactional
                                </span>

                                <div class="metric-icon-square bg-info-subtle text-info">
                                    <i class="fas fa-receipt"></i>
                                </div>

                            </div>

                            <div>
                                <h4 class="stat-val-num text-info font-monospace"
                                    id="stat-transactional-templates">
                                    {{ $transactionalCount }}
                                </h4>

                                <small class="stat-sub-text">
                                    Order alerts &amp; receipts
                                </small>
                            </div>

                        </div>
                    </div>

                </div>

                {{-- Newsletter information --}}

                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3 pt-2 border-top">

                    <div class="d-flex align-items-center gap-2">
                        <span class="metric-icon-square bg-primary-subtle text-primary"
                              style="width:27px;height:27px;">
                            <i class="fas fa-envelope-open-text"></i>
                        </span>

                        <span class="small fw-semibold text-secondary">
                            Newsletter Templates
                        </span>
                    </div>

                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-3 py-2">
                        {{ $newsletterCount }} Templates
                    </span>

                </div>

            </div>
        </div>
    </div>


    {{-- =====================================================
         4. SEARCH & FILTER TOOLBAR
    ====================================================== --}}

    <div class="card filter-card mb-3">

        <div class="section-header-wrap d-flex justify-content-between align-items-center">

            <h6 class="section-title">
                <i class="fas fa-filter text-primary me-1"></i>
                Search &amp; Filter Templates
            </h6>

            <button type="button"
                    class="collapse-toggle-btn"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseTemplatesFilter"
                    aria-expanded="true"
                    aria-controls="collapseTemplatesFilter"
                    title="Toggle Filters">

                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>

        </div>

        <div class="collapse show" id="collapseTemplatesFilter">

            <div class="p-3">

                <div class="row g-2 align-items-end">

                    {{-- Search --}}

                    <div class="col-12 col-md-5">

                        <label class="form-label" for="templateSearchInput">
                            <i class="fas fa-search text-primary me-1"></i>
                            Search Template
                        </label>

                        <div class="input-group">

                            <span class="input-group-text template-addon-text">
                                <i class="fas fa-search"></i>
                            </span>

                            <input type="text"
                                   id="templateSearchInput"
                                   class="form-control template-filter-control"
                                   placeholder="Search by name or subject..."
                                   autocomplete="off"
                                   oninput="filterTemplatesTable()">

                        </div>
                    </div>


                    {{-- Category --}}

                    <div class="col-6 col-md-4">

                        <label class="form-label" for="templateCategoryFilter">
                            <i class="fas fa-tags text-success me-1"></i>
                            Category
                        </label>

                        <select id="templateCategoryFilter"
                                class="form-select template-filter-control"
                                onchange="filterTemplatesTable()">

                            <option value="all">All Categories</option>
                            <option value="promotional">Promotional</option>
                            <option value="abandoned_cart">Abandoned Cart</option>
                            <option value="newsletter">Newsletter</option>
                            <option value="transactional">Transactional</option>

                        </select>
                    </div>


                    {{-- Reset --}}

                    <div class="col-6 col-md-3">

                        <button type="button"
                                class="btn btn-light border btn-compact w-100"
                                onclick="resetTemplateFilters()">

                            <i class="fas fa-rotate-left"></i>
                            Reset Filters

                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>


    {{-- =====================================================
         5. TEMPLATE TABLE
    ====================================================== --}}

    <div class="card mb-3">

        {{-- Table Header --}}

        <div class="p-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 bg-light bg-opacity-25">

            <div class="min-w-0">

                <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2"
                    style="font-size:13px;">

                    <i class="fas fa-layer-group text-primary"></i>
                    Configured Design Templates

                </h6>

                <small class="text-muted font-monospace"
                       id="showing-entries-text"
                       style="font-size:10px;">

                    Showing {{ $templates->firstItem() ?? 0 }}
                    to {{ $templates->lastItem() ?? 0 }}
                    of {{ $templates->total() }} templates

                </small>

            </div>

            <div class="d-flex align-items-center gap-2">

                <span class="badge bg-white text-secondary border font-monospace px-3 py-2"
                      id="visible-template-count"
                      style="font-size:10px;">

                    {{ $templates->total() }} Total

                </span>

                <button type="button"
                        class="collapse-toggle-btn"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseTemplatesTable"
                        aria-expanded="true"
                        aria-controls="collapseTemplatesTable"
                        title="Toggle Table">

                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>

            </div>

        </div>


        <div class="collapse show" id="collapseTemplatesTable">

            <div class="pc-fit-container" style="min-height:250px;">

                <table class="table align-middle custom-templates-table"
                       id="templates-table">

                    <thead>
                        <tr>
                            <th class="ps-3 col-tp-id text-center">ID</th>
                            <th class="col-tp-name">Template Name</th>
                            <th class="col-tp-cat">Category</th>
                            <th class="col-tp-subject">Default Subject</th>
                            <th class="col-tp-date">Last Modified</th>
                            <th class="text-end pe-3 col-tp-actions">Actions</th>
                        </tr>
                    </thead>


                    <tbody id="templates-tbody">

                        @forelse($templates as $tpl)

                            @php
                                $catBadge = match(strtolower($tpl->category ?? '')) {
                                    'promotional'    => 'bg-success-subtle text-success border border-success-subtle',
                                    'abandoned_cart' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                    'newsletter'     => 'bg-primary-subtle text-primary border border-primary-subtle',
                                    'transactional'  => 'bg-info-subtle text-info border border-info-subtle',
                                    default          => 'bg-light text-secondary border',
                                };

                                $rowSearchData = strtolower(
                                    ($tpl->name ?? '') . ' ' .
                                    ($tpl->category ?? '') . ' ' .
                                    ($tpl->subject ?? '')
                                );
                            @endphp


                            <tr class="template-entry-row"
                                id="template-row-{{ $tpl->id }}"
                                data-search="{{ $rowSearchData }}"
                                data-category="{{ strtolower($tpl->category ?? '') }}">

                                {{-- ID --}}

                                <td class="ps-3 col-tp-id text-center font-monospace text-muted">
                                    {{ $tpl->id }}
                                </td>


                                {{-- Template Name --}}

                                <td class="col-tp-name">

                                    <div class="min-w-0">

                                        <strong class="template-name d-block text-truncate"
                                                title="{{ $tpl->name }}">

                                            {{ $tpl->name }}

                                        </strong>

                                        <small class="template-subtitle d-block">
                                            <i class="fas fa-code me-1"></i>
                                            Responsive HTML Layout
                                        </small>

                                    </div>

                                </td>


                                {{-- Category --}}

                                <td class="col-tp-cat">

                                    <span class="badge category-badge {{ $catBadge }}">

                                        {{ ucfirst(str_replace('_', ' ', $tpl->category)) }}

                                    </span>

                                </td>


                                {{-- Subject --}}

                                <td class="col-tp-subject">

                                    <span class="d-block text-truncate text-dark"
                                          style="font-size:11px;"
                                          title="{{ $tpl->subject ?? '—' }}">

                                        {{ $tpl->subject ?? '—' }}

                                    </span>

                                </td>


                                {{-- Modified Date --}}

                                <td class="col-tp-date">

                                    <span class="d-block text-dark fw-semibold font-monospace"
                                          style="font-size:10.5px;">

                                        {{ $tpl->updated_at->format('M d, Y') }}

                                    </span>

                                    <small class="text-muted font-monospace"
                                           style="font-size:9.5px;">

                                        {{ $tpl->updated_at->format('h:i A') }}

                                    </small>

                                </td>


                                {{-- Actions --}}

                                <td class="text-end pe-3 col-tp-actions">

                                    <div class="d-inline-flex align-items-center gap-1">

                                        {{-- Preview --}}

                                        <button type="button"
                                                class="btn template-action-btn text-info"
                                                onclick="previewTemplate(
                                                    {{ $tpl->id }},
                                                    {{ Js::from($tpl->name) }}
                                                )"
                                                title="Quick Preview"
                                                aria-label="Preview Template">

                                            <i class="fas fa-eye"></i>

                                        </button>


                                        {{-- Edit --}}

                                        <a href="{{ url('admin/email-marketing/templates/edit/' . $tpl->id) }}"
                                           class="btn template-action-btn text-primary"
                                           title="Edit Template"
                                           aria-label="Edit Template">

                                            <i class="fas fa-pencil"></i>

                                        </a>


                                        {{-- Duplicate --}}

                                        <button type="button"
                                                class="btn template-action-btn text-secondary"
                                                onclick="duplicateTemplateAjax(
                                                    {{ $tpl->id }},
                                                    {{ Js::from($tpl->name) }}
                                                )"
                                                title="Duplicate Template"
                                                aria-label="Duplicate Template">

                                            <i class="fas fa-copy"
                                               id="dup-icon-{{ $tpl->id }}"></i>

                                        </button>


                                        {{-- Delete --}}

                                        <button type="button"
                                                class="btn template-action-btn text-danger"
                                                onclick="triggerDeleteTemplate(
                                                    {{ $tpl->id }},
                                                    {{ Js::from($tpl->name) }}
                                                )"
                                                title="Delete Template"
                                                aria-label="Delete Template">

                                            <i class="fas fa-trash-can"></i>

                                        </button>

                                    </div>


                                    {{-- Hidden HTML for Preview --}}

                                    <div id="tpl-raw-html-{{ $tpl->id }}"
                                         class="d-none">{!! $tpl->html_content !!}</div>

                                </td>

                            </tr>

                        @empty

                            <tr id="emptyTemplatesRow">

                                <td colspan="6" class="text-center py-5">

                                    <div class="mb-2">
                                        <i class="fas fa-palette fa-3x text-secondary opacity-25"></i>
                                    </div>

                                    <h6 class="fw-bold text-dark mb-1"
                                        style="font-size:13px;">

                                        No Email Templates Yet

                                    </h6>

                                    <p class="text-muted small mb-3">

                                        Start building your first professional email template.

                                    </p>

                                    <a href="{{ url('admin/email-marketing/templates/create') }}"
                                       class="btn btn-primary btn-compact">

                                        <i class="fas fa-plus"></i>
                                        Create First Template

                                    </a>

                                </td>

                            </tr>

                        @endforelse


                        {{-- No Search Results --}}

                        <tr id="noMatchTemplatesRow" style="display:none;">

                            <td colspan="6" class="text-center py-5">

                                <i class="fas fa-filter-circle-xmark fa-2x text-secondary opacity-25 mb-2"></i>

                                <h6 class="fw-bold text-dark mb-1"
                                    style="font-size:13px;">

                                    No Matching Templates

                                </h6>

                                <p class="text-muted small mb-2">
                                    Try changing your search or category filter.
                                </p>

                                <button type="button"
                                        class="btn btn-light border btn-compact"
                                        onclick="resetTemplateFilters()">

                                    <i class="fas fa-rotate-left"></i>
                                    Clear Filters

                                </button>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>


            {{-- Pagination --}}

            @if($templates->hasPages())

                <div class="table-footer p-3 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 no-print">

                    <small class="text-muted font-monospace"
                           style="font-size:10.5px;">

                        Showing {{ $templates->firstItem() }}
                        to {{ $templates->lastItem() }}
                        of {{ $templates->total() }} templates

                    </small>

                    <div>
                        {{ $templates->links('pagination::bootstrap-5') }}
                    </div>

                </div>

            @endif

        </div>
    </div>

</div>


{{-- =========================================================
     6. TEMPLATE PREVIEW MODAL
========================================================= --}}

<div class="modal fade template-modal-scope"
     id="templatePreviewModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index:106000;">

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content shadow-lg">

            <div class="modal-header bg-light py-2 px-3">

                <div class="d-flex align-items-center gap-2 min-w-0">

                    <i class="fas fa-eye text-primary"></i>

                    <h6 class="modal-title fw-bold text-dark mb-0 text-truncate"
                        id="previewModalTitle">

                        Template Preview

                    </h6>

                </div>


                <div class="d-flex align-items-center gap-2 flex-wrap">

                    <div class="btn-group btn-group-sm" role="group">

                        <button type="button"
                                class="btn btn-outline-secondary btn-compact viewport-btn active"
                                id="btn-view-desktop"
                                onclick="setPreviewViewport('desktop')">

                            <i class="fas fa-desktop"></i>
                            <span>Desktop</span>

                        </button>

                        <button type="button"
                                class="btn btn-outline-secondary btn-compact viewport-btn"
                                id="btn-view-tablet"
                                onclick="setPreviewViewport('tablet')">

                            <i class="fas fa-tablet-screen-button"></i>
                            <span>Tablet</span>

                        </button>

                        <button type="button"
                                class="btn btn-outline-secondary btn-compact viewport-btn"
                                id="btn-view-mobile"
                                onclick="setPreviewViewport('mobile')">

                            <i class="fas fa-mobile-screen"></i>
                            <span>Mobile</span>

                        </button>

                    </div>

                    <button type="button"
                            class="btn-close shadow-none"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>

                </div>

            </div>


            <div class="modal-body bg-light p-2 p-md-3 d-flex justify-content-center">

                <iframe id="previewIframe"
                        class="preview-frame"
                        title="Email Template Preview"
                        sandbox=""
                        referrerpolicy="no-referrer"></iframe>

            </div>

        </div>
    </div>
</div>


{{-- =========================================================
     7. DELETE CONFIRMATION MODAL
========================================================= --}}

<div class="modal fade template-modal-scope"
     id="deleteTemplateModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index:106500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content shadow-lg p-3 text-center">

            <div class="modal-body p-1">

                <div class="delete-icon-wrap bg-danger-subtle text-danger mx-auto mb-3">

                    <i class="fas fa-trash-can fs-4"></i>

                </div>

                <h6 class="fw-bold text-dark mb-2"
                    style="font-size:15px;">

                    Delete Template?

                </h6>

                <p class="text-muted small mb-3"
                   style="line-height:1.6;">

                    Are you sure you want to permanently delete

                    <strong id="deleteTemplateTitle" class="text-dark">
                        this template
                    </strong>?

                    This action cannot be undone.

                </p>

                <div class="d-flex justify-content-center gap-2">

                    <button type="button"
                            class="btn btn-light border btn-compact px-3"
                            data-bs-dismiss="modal">

                        Cancel

                    </button>

                    <button type="button"
                            class="btn btn-danger btn-compact px-3 fw-bold"
                            id="btn-confirm-delete-execute">

                        <i class="fas fa-trash-can me-1"></i>
                        Yes, Delete

                    </button>

                </div>

            </div>

        </div>
    </div>
</div>


{{-- =========================================================
     8. TOAST NOTIFICATION HOLDER
========================================================= --}}

<div id="custom-toast-holder" aria-live="polite"></div>


{{-- =========================================================
     9. JAVASCRIPT
========================================================= --}}

<script>
(function () {
    'use strict';

    let pendingDeleteId = null;

    const csrfToken = @json(csrf_token());

    const duplicateUrl = @json(url('admin/email-marketing/templates/duplicate'));
    const deleteUrl = @json(url('admin/email-marketing/templates/delete'));


    /* =====================================================
       1. TEMPLATE PREVIEW
    ====================================================== */

    window.previewTemplate = function (templateId, templateName = 'Template Preview') {

        const rawHtmlEl = document.getElementById('tpl-raw-html-' + templateId);
        const iframe = document.getElementById('previewIframe');
        const titleEl = document.getElementById('previewModalTitle');
        const modalEl = document.getElementById('templatePreviewModal');

        if (!rawHtmlEl || !iframe || !modalEl) {
            showToast('Error', 'Unable to load template preview.', 'danger');
            return;
        }

        if (titleEl) {
            titleEl.textContent = templateName;
        }

        iframe.srcdoc = rawHtmlEl.innerHTML;

        setPreviewViewport('desktop');

        if (window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    };


    /* =====================================================
       2. PREVIEW VIEWPORT SWITCHER
    ====================================================== */

    window.setPreviewViewport = function (type) {

        const iframe = document.getElementById('previewIframe');

        const btnDesktop = document.getElementById('btn-view-desktop');
        const btnTablet = document.getElementById('btn-view-tablet');
        const btnMobile = document.getElementById('btn-view-mobile');

        if (!iframe) return;

        [btnDesktop, btnTablet, btnMobile].forEach(btn => {
            if (btn) btn.classList.remove('active');
        });

        if (type === 'mobile') {

            iframe.style.width = '375px';

            if (btnMobile) btnMobile.classList.add('active');

        } else if (type === 'tablet') {

            iframe.style.width = '768px';

            if (btnTablet) btnTablet.classList.add('active');

        } else {

            iframe.style.width = '100%';

            if (btnDesktop) btnDesktop.classList.add('active');
        }
    };


    /* =====================================================
       3. SEARCH & CATEGORY FILTER
    ====================================================== */

    window.filterTemplatesTable = function () {

        const searchInput = document.getElementById('templateSearchInput');
        const categoryInput = document.getElementById('templateCategoryFilter');

        const query = (searchInput?.value || '').toLowerCase().trim();
        const category = (categoryInput?.value || 'all').toLowerCase();

        const rows = document.querySelectorAll(
            '#templates-tbody .template-entry-row'
        );

        let visibleCount = 0;

        rows.forEach(row => {

            const rowSearch = (row.dataset.search || '').toLowerCase();
            const rowCategory = (row.dataset.category || '').toLowerCase();

            const matchesQuery = !query || rowSearch.includes(query);

            const matchesCategory =
                category === 'all' || rowCategory === category;

            const visible = matchesQuery && matchesCategory;

            row.style.display = visible ? '' : 'none';

            if (visible) visibleCount++;
        });


        const countBadge = document.getElementById('visible-template-count');

        if (countBadge) {
            countBadge.textContent = `${visibleCount} Showing`;
        }


        const noMatchRow = document.getElementById('noMatchTemplatesRow');

        if (noMatchRow) {

            noMatchRow.style.display =
                (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }


        const showingText = document.getElementById('showing-entries-text');

        if (showingText && (query || category !== 'all')) {
            showingText.textContent = `Showing ${visibleCount} filtered templates`;
        } else if (showingText) {
            showingText.textContent = @json(
                'Showing ' .
                ($templates->firstItem() ?? 0) .
                ' to ' .
                ($templates->lastItem() ?? 0) .
                ' of ' .
                $templates->total() .
                ' templates'
            );
        }
    };


    /* =====================================================
       4. RESET FILTERS
    ====================================================== */

    window.resetTemplateFilters = function () {

        const searchInput = document.getElementById('templateSearchInput');
        const categoryInput = document.getElementById('templateCategoryFilter');

        if (searchInput) searchInput.value = '';

        if (categoryInput) categoryInput.value = 'all';

        filterTemplatesTable();
    };


    /* =====================================================
       5. DUPLICATE TEMPLATE AJAX
    ====================================================== */

    window.duplicateTemplateAjax = function (id, name) {

        const icon = document.getElementById(`dup-icon-${id}`);

        if (icon && icon.dataset.loading === 'true') return;

        if (icon) {

            icon.dataset.loading = 'true';
            icon.className = 'fas fa-spinner fa-spin';
        }

        fetch(`${duplicateUrl}/${encodeURIComponent(id)}`, {

            method: 'POST',

            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }

        })

        .then(async response => {

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(data.message || 'Failed to duplicate template.');
            }

            return data;
        })

        .then(() => {

            showToast(
                'Duplicated',
                `Template "${name}" duplicated successfully.`,
                'success'
            );

            setTimeout(() => window.location.reload(), 700);
        })

        .catch(error => {

            if (icon) {
                icon.className = 'fas fa-copy';
                delete icon.dataset.loading;
            }

            showToast(
                'Error',
                error.message || 'Failed to duplicate template.',
                'danger'
            );
        });
    };


    /* =====================================================
       6. DELETE MODAL
    ====================================================== */

    window.triggerDeleteTemplate = function (id, name) {

        pendingDeleteId = id;

        const titleEl = document.getElementById('deleteTemplateTitle');
        const modalEl = document.getElementById('deleteTemplateModal');

        if (titleEl) {
            titleEl.textContent = name;
        }

        if (modalEl && window.bootstrap) {

            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    };


    /* =====================================================
       7. DELETE TEMPLATE AJAX
    ====================================================== */

    const deleteButton = document.getElementById(
        'btn-confirm-delete-execute'
    );

    if (deleteButton) {

        deleteButton.addEventListener('click', async function () {

            if (!pendingDeleteId) return;

            const btn = this;

            if (btn.disabled) return;

            const deletingId = pendingDeleteId;

            btn.disabled = true;

            btn.innerHTML =
                '<i class="fas fa-spinner fa-spin me-1"></i> Deleting...';

            try {

                const response = await fetch(
                    `${deleteUrl}/${encodeURIComponent(deletingId)}`,
                    {
                        method: 'DELETE',

                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    }
                );

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {

                    throw new Error(
                        data.message || 'Failed to remove template.'
                    );
                }


                const modalEl = document.getElementById('deleteTemplateModal');

                if (modalEl && window.bootstrap) {

                    bootstrap.Modal.getInstance(modalEl)?.hide();
                }


                const row = document.getElementById(
                    `template-row-${deletingId}`
                );

                if (row) row.remove();


                pendingDeleteId = null;

                showToast(
                    'Deleted',
                    'Template removed successfully.',
                    'success'
                );


                filterTemplatesTable();


                // If the current page becomes empty, reload to refresh pagination.
                const remainingRows = document.querySelectorAll(
                    '#templates-tbody .template-entry-row'
                );

                if (remainingRows.length === 0) {

                    setTimeout(() => window.location.reload(), 900);
                }

            } catch (error) {

                showToast(
                    'Error',
                    error.message || 'Failed to remove template.',
                    'danger'
                );

            } finally {

                btn.disabled = false;

                btn.innerHTML =
                    '<i class="fas fa-trash-can me-1"></i> Yes, Delete';
            }
        });
    }


    /* =====================================================
       8. TOAST NOTIFICATIONS
    ====================================================== */

    window.showToast = function (
        title,
        message,
        type = 'success'
    ) {

        const holder = document.getElementById('custom-toast-holder');

        if (!holder) return;

        const toastId = 'toast-' + Date.now() + '-' +
            Math.random().toString(36).slice(2, 7);

        const typeConfigs = {

            success: {
                icon: 'fa-check-circle',
                color: 'bg-success text-white'
            },

            danger: {
                icon: 'fa-circle-exclamation',
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

        const config = typeConfigs[type] || typeConfigs.success;


        const toast = document.createElement('div');

        toast.id = toastId;

        toast.className =
            `admin-template-toast ${config.color}`;


        const content = document.createElement('div');

        content.className = 'd-flex align-items-center gap-2';


        const icon = document.createElement('i');

        icon.className = `fas ${config.icon} fs-5`;


        const textWrapper = document.createElement('div');

        const titleEl = document.createElement('strong');

        titleEl.className = 'd-block';

        titleEl.style.fontSize = '13px';

        titleEl.textContent = title;


        const messageEl = document.createElement('small');

        messageEl.style.fontSize = '11px';

        messageEl.style.opacity = '.95';

        messageEl.textContent = message;


        textWrapper.appendChild(titleEl);

        textWrapper.appendChild(messageEl);

        content.appendChild(icon);

        content.appendChild(textWrapper);


        const closeButton = document.createElement('button');

        closeButton.type = 'button';

        closeButton.className =
            `btn-close ${type === 'warning' ? '' : 'btn-close-white'} shadow-none`;

        closeButton.setAttribute('aria-label', 'Close');

        closeButton.addEventListener('click', () => toast.remove());


        toast.appendChild(content);

        toast.appendChild(closeButton);

        holder.appendChild(toast);


        setTimeout(() => {

            const existingToast = document.getElementById(toastId);

            if (existingToast) existingToast.remove();

        }, 3500);
    };


})();
</script>

@endsection