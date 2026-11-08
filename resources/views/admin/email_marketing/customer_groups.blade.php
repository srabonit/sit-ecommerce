
@extends('layouts.admin')

@section('title', 'Customer Groups & Personas - Email Marketing')

@php
    $segmentsIndexRoute = Route::has('admin.email_marketing.segments.index')
        ? route('admin.email_marketing.segments.index')
        : (Route::has('admin.email_marketing.segments')
            ? route('admin.email_marketing.segments')
            : url('admin/email-marketing/segments'));

    $segmentsStoreRoute = Route::has('admin.email_marketing.segments.store')
        ? route('admin.email_marketing.segments.store')
        : url('admin/email-marketing/segments');

    $segmentsCreateRoute = Route::has('admin.email_marketing.segments.create')
        ? route('admin.email_marketing.segments.create')
        : url('admin/email-marketing/segments/create');

    $campaignsCreateRoute = Route::has('admin.email_marketing.campaigns.create')
        ? route('admin.email_marketing.campaigns.create')
        : url('admin/email-marketing/campaigns/create');
@endphp

@section('admin_content')

<style>
    /* =========================================================
       CUSTOMER PERSONAS - SCOPED DESIGN SYSTEM
    ========================================================= */

    #email-marketing-customer-groups-root {
        --cg-primary: #2563eb;
        --cg-primary-dark: #1d4ed8;
        --cg-text: #172033;
        --cg-muted: #64748b;
        --cg-border: #e2e8f0;
        --cg-surface: #ffffff;
        --cg-soft: #f8fafc;
        --cg-radius: 5px;
        color: var(--cg-text);
    }

    #email-marketing-customer-groups-root *,
    #deployCohortModal * {
        box-sizing: border-box;
    }

    /* Global Radius */
    #email-marketing-customer-groups-root .btn,
    #email-marketing-customer-groups-root button,
    #email-marketing-customer-groups-root .badge,
    #email-marketing-customer-groups-root .alert,
    #email-marketing-customer-groups-root .form-control,
    #email-marketing-customer-groups-root .form-select,
    #email-marketing-customer-groups-root .input-group-text,
    #email-marketing-customer-groups-root .card,
    #email-marketing-customer-groups-root .persona-card,
    #email-marketing-customer-groups-root .metric-card,
    #email-marketing-customer-groups-root .strategy-box,
    #email-marketing-customer-groups-root .collapse-toggle-btn,
    #deployCohortModal .modal-content,
    #deployCohortModal .btn {
        border-radius: var(--cg-radius) !important;
    }

    #email-marketing-customer-groups-root .input-group > .input-group-text {
        border-radius: 5px 0 0 5px !important;
    }

    #email-marketing-customer-groups-root .input-group > .form-control {
        border-radius: 0 5px 5px 0 !important;
    }

    #email-marketing-customer-groups-root a {
        text-decoration: none;
    }

    #email-marketing-customer-groups-root .font-monospace,
    #email-marketing-customer-groups-root .bn-digit {
        font-weight: 700;
    }

    /* Buttons */
    #email-marketing-customer-groups-root .btn-compact,
    #deployCohortModal .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        min-height: 32px;
        padding: 6px 12px !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        line-height: 1.35;
        white-space: nowrap;
        transition: all .2s ease;
    }

    #email-marketing-customer-groups-root .btn-compact:hover {
        transform: translateY(-1px);
    }

    #email-marketing-customer-groups-root .btn-primary {
        background: var(--cg-primary);
        border-color: var(--cg-primary);
    }

    #email-marketing-customer-groups-root .btn-primary:hover {
        background: var(--cg-primary-dark);
        border-color: var(--cg-primary-dark);
    }

    /* Page Header */
    #email-marketing-customer-groups-root .page-heading {
        font-size: 19px;
        font-weight: 800;
        letter-spacing: -.45px;
        color: #172033;
    }

    #email-marketing-customer-groups-root .page-description {
        color: var(--cg-muted);
        font-size: 11.5px;
        line-height: 1.5;
        max-width: 680px;
    }

    #email-marketing-customer-groups-root .header-icon {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #2563eb;
        background: #eff6ff;
        border: 1px solid #dbeafe;
        border-radius: 5px;
        font-size: 15px;
    }

    /* Surface Cards */
    #email-marketing-customer-groups-root .surface-card {
        background: var(--cg-surface);
        border: 1px solid var(--cg-border);
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
        overflow: hidden;
    }

    #email-marketing-customer-groups-root .section-header {
        min-height: 43px;
        padding: 9px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        border-bottom: 1px solid var(--cg-border);
        background: #fff;
    }

    #email-marketing-customer-groups-root .section-title {
        font-size: 12px;
        font-weight: 800;
        color: #273449;
        margin: 0;
    }

    #email-marketing-customer-groups-root .section-title i {
        color: var(--cg-primary);
        margin-right: 6px;
    }

    /* Collapse Button */
    #email-marketing-customer-groups-root .collapse-toggle-btn {
        width: 25px;
        height: 25px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        color: #64748b;
        border: 1px solid #dbe2ea;
        flex-shrink: 0;
        transition: all .2s ease;
    }

    #email-marketing-customer-groups-root .collapse-toggle-btn:hover {
        background: #eff6ff;
        color: var(--cg-primary);
        border-color: #bfdbfe;
    }

    #email-marketing-customer-groups-root .collapse-icon {
        font-size: 10px;
        transition: transform .25s ease;
    }

    #email-marketing-customer-groups-root
    .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* Strategy Banner */
    #email-marketing-customer-groups-root .strategy-banner {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    #email-marketing-customer-groups-root .strategy-banner-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #2563eb;
        background: #eff6ff;
        border: 1px solid #dbeafe;
        border-radius: 5px;
        font-size: 17px;
    }

    #email-marketing-customer-groups-root .strategy-banner-title {
        font-size: 13px;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 4px;
    }

    #email-marketing-customer-groups-root .strategy-banner-text {
        font-size: 11.5px;
        color: #64748b;
        line-height: 1.6;
        margin: 0;
    }

    /* KPI Cards */
    #email-marketing-customer-groups-root .metric-card {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        min-height: 105px;
        padding: 13px;
        border: 1px solid var(--cg-border);
        overflow: hidden;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    #email-marketing-customer-groups-root .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(15, 23, 42, .07);
    }

    #email-marketing-customer-groups-root .metric-card-blue {
        background: linear-gradient(145deg, #fff 0%, #f0f7ff 100%);
        border-left: 3px solid #2563eb;
    }

    #email-marketing-customer-groups-root .metric-card-amber {
        background: linear-gradient(145deg, #fff 0%, #fffbeb 100%);
        border-left: 3px solid #f59e0b;
    }

    #email-marketing-customer-groups-root .metric-card-green {
        background: linear-gradient(145deg, #fff 0%, #f0fdf4 100%);
        border-left: 3px solid #10b981;
    }

    #email-marketing-customer-groups-root .metric-card-purple {
        background: linear-gradient(145deg, #fff 0%, #faf5ff 100%);
        border-left: 3px solid #8b5cf6;
    }

    #email-marketing-customer-groups-root .metric-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
    }

    #email-marketing-customer-groups-root .stat-label-title {
        font-size: 10.5px;
        font-weight: 800;
        color: #64748b;
        letter-spacing: .15px;
    }

    #email-marketing-customer-groups-root .metric-icon-square {
        width: 31px;
        height: 31px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 5px;
        font-size: 13px;
    }

    #email-marketing-customer-groups-root .stat-val-num {
        font-size: 17px;
        line-height: 1.3;
        font-weight: 800;
        color: #172033;
        letter-spacing: -.35px;
        overflow-wrap: anywhere;
        margin: 0;
    }

    #email-marketing-customer-groups-root .stat-sub-text {
        display: block;
        margin-top: 4px;
        font-size: 9.5px;
        color: #8491a5;
    }

    /* Search */
    #email-marketing-customer-groups-root .search-card {
        padding: 12px 14px;
    }

    #email-marketing-customer-groups-root .search-input {
        height: 39px;
        font-size: 11.5px;
        border-color: #dbe2ea;
        background: #fff;
    }

    #email-marketing-customer-groups-root .search-input:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .08) !important;
    }

    #email-marketing-customer-groups-root .search-counter {
        font-size: 10.5px;
        color: #64748b;
        font-weight: 700;
    }

    /* Persona Cards */
    #email-marketing-customer-groups-root .persona-card {
        height: 100%;
        border: 1px solid var(--cg-border);
        background: #fff;
        overflow: hidden;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    #email-marketing-customer-groups-root .persona-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 7px 18px rgba(15, 23, 42, .075);
    }

    #email-marketing-customer-groups-root .persona-card .card-body {
        padding: 15px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 260px;
    }

    #email-marketing-customer-groups-root .persona-topline {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 7px;
        margin-bottom: 12px;
    }

    #email-marketing-customer-groups-root .persona-label {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 8px;
        border-radius: 5px;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .25px;
        white-space: nowrap;
    }

    #email-marketing-customer-groups-root .persona-meta {
        font-size: 9.5px;
        color: #64748b;
        white-space: nowrap;
    }

    #email-marketing-customer-groups-root .persona-title {
        font-size: 13.5px;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.4;
        margin-bottom: 6px;
    }

    #email-marketing-customer-groups-root .persona-description {
        color: #64748b;
        font-size: 11px;
        line-height: 1.65;
        margin-bottom: 12px;
    }

    #email-marketing-customer-groups-root .strategy-box {
        padding: 9px 10px;
        background: #f8fafc;
        border: 1px solid #e8edf3;
        border-radius: 5px;
        font-size: 10.5px;
        line-height: 1.55;
        margin-bottom: 14px;
    }

    #email-marketing-customer-groups-root .strategy-box strong {
        display: block;
        color: #334155;
        font-size: 10.5px;
        margin-bottom: 3px;
    }

    #email-marketing-customer-groups-root .strategy-box span {
        color: #64748b;
    }

    #email-marketing-customer-groups-root .persona-action {
        width: 100%;
        min-height: 35px;
    }

    /* Card Accent Colors */
    #email-marketing-customer-groups-root .accent-amber {
        border-top: 3px solid #f59e0b !important;
    }

    #email-marketing-customer-groups-root .accent-green {
        border-top: 3px solid #10b981 !important;
    }

    #email-marketing-customer-groups-root .accent-red {
        border-top: 3px solid #ef4444 !important;
    }

    #email-marketing-customer-groups-root .accent-blue {
        border-top: 3px solid #2563eb !important;
    }

    #email-marketing-customer-groups-root .accent-purple {
        border-top: 3px solid #8b5cf6 !important;
    }

    /* Custom Segment Card */
    #email-marketing-customer-groups-root .custom-segment-card {
        height: 100%;
        min-height: 260px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        text-align: center;
        border: 1px dashed #b8c6d8;
        background: #f8fafc;
        transition: all .2s ease;
    }

    #email-marketing-customer-groups-root .custom-segment-card:hover {
        background: #eff6ff;
        border-color: #60a5fa;
    }

    #email-marketing-customer-groups-root .custom-segment-icon {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eff6ff;
        border: 1px solid #dbeafe;
        color: #2563eb;
        border-radius: 5px;
        font-size: 18px;
        margin-bottom: 12px;
    }

    /* Empty Search State */
    #email-marketing-customer-groups-root .empty-search-state {
        display: none;
        padding: 35px 15px;
        text-align: center;
        border: 1px dashed #cbd5e1;
        background: #fff;
        border-radius: 5px;
    }

    #email-marketing-customer-groups-root .empty-search-state i {
        font-size: 25px;
        color: #94a3b8;
        margin-bottom: 10px;
    }

    #email-marketing-customer-groups-root .empty-search-state h6 {
        font-size: 13px;
        font-weight: 800;
        color: #334155;
    }

    #email-marketing-customer-groups-root .empty-search-state p {
        font-size: 11px;
        color: #64748b;
        margin-bottom: 12px;
    }

    /* Modal */
    #deployCohortModal {
        z-index: 106500;
    }

    #deployCohortModal .modal-content {
        border: 1px solid #e2e8f0;
        box-shadow: 0 15px 45px rgba(15, 23, 42, .16);
    }

    #deployCohortModal .modal-icon {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid #dbeafe;
        border-radius: 5px;
        font-size: 18px;
    }

    #deployCohortModal .modal-title {
        font-size: 14px;
        font-weight: 800;
        color: #172033;
    }

    #deployCohortModal .modal-description {
        font-size: 11px;
        color: #64748b;
        line-height: 1.6;
    }

    /* Responsive */
    @media (max-width: 767.98px) {
        #email-marketing-customer-groups-root {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #email-marketing-customer-groups-root .page-heading {
            font-size: 16px;
        }

        #email-marketing-customer-groups-root .page-description {
            font-size: 10.5px;
        }

        #email-marketing-customer-groups-root .top-action-btns {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 7px !important;
        }

        #email-marketing-customer-groups-root .top-action-btns .btn {
            width: 100%;
            min-height: 35px;
            padding: 7px 5px !important;
            font-size: 10px !important;
        }

        #email-marketing-customer-groups-root .metric-card {
            min-height: 92px;
            padding: 10px;
        }

        #email-marketing-customer-groups-root .stat-label-title {
            font-size: 9px;
        }

        #email-marketing-customer-groups-root .stat-val-num {
            font-size: 13px;
        }

        #email-marketing-customer-groups-root .stat-sub-text {
            font-size: 8.5px;
        }

        #email-marketing-customer-groups-root .metric-icon-square {
            width: 26px;
            height: 26px;
            font-size: 10px;
        }

        #email-marketing-customer-groups-root .persona-card .card-body {
            min-height: 245px;
            padding: 12px;
        }

        #email-marketing-customer-groups-root .persona-meta {
            font-size: 8px;
        }

        #email-marketing-customer-groups-root .persona-title {
            font-size: 12.5px;
        }

        #email-marketing-customer-groups-root .section-header {
            padding: 9px 11px;
        }

        #email-marketing-customer-groups-root .strategy-banner-text {
            font-size: 10.5px;
        }

        #email-marketing-customer-groups-root .custom-segment-card {
            min-height: 225px;
        }
    }

    @media (max-width: 380px) {
        #email-marketing-customer-groups-root .persona-topline {
            align-items: flex-start;
            flex-direction: column;
        }

        #email-marketing-customer-groups-root .metric-card {
            min-height: 88px;
        }

        #email-marketing-customer-groups-root .stat-val-num {
            font-size: 12px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #email-marketing-customer-groups-root *,
        #deployCohortModal * {
            transition: none !important;
            animation: none !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative"
     id="email-marketing-customer-groups-root">

    {{-- =====================================================
         1. PAGE HEADER
    ====================================================== --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">

        <div class="d-flex align-items-start gap-2">
            <div class="header-icon">
                <i class="fas fa-users-rectangle"></i>
            </div>

            <div>
                <h5 class="page-heading mb-1">
                    Customer Personas &amp; Groups
                </h5>

                <p class="page-description mb-0">
                    Strategic customer groupings based on purchase frequency, spending threshold, and engagement lifecycle.
                </p>
            </div>
        </div>

        <div class="d-flex gap-2 top-action-btns">
            <a href="{{ $segmentsIndexRoute }}"
               class="btn btn-sm btn-outline-primary btn-compact">
                <i class="fas fa-filter-circle-dollar"></i>
                Active Segments
            </a>

            <a href="{{ $campaignsCreateRoute }}"
               class="btn btn-sm btn-primary btn-compact">
                <i class="fas fa-plus-circle"></i>
                New Campaign
            </a>
        </div>
    </div>

    {{-- =====================================================
         2. STRATEGY BANNER
    ====================================================== --}}
    <div class="surface-card mb-3">

        <div class="section-header">
            <h6 class="section-title">
                <i class="fas fa-compass"></i>
                Targeted Customer Lifecycle Cohorts &amp; Strategy
            </h6>

            <button type="button"
                    class="collapse-toggle-btn"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseValueBanner"
                    aria-expanded="true"
                    aria-controls="collapseValueBanner"
                    aria-label="Toggle strategy information">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseValueBanner">
            <div class="p-3">
                <div class="strategy-banner">

                    <div class="strategy-banner-icon">
                        <i class="fas fa-sitemap"></i>
                    </div>

                    <div>
                        <div class="strategy-banner-title">
                            Deploy Intelligent Lifecycle Segments
                        </div>

                        <p class="strategy-banner-text">
                            Instead of sending generic mass blasts, isolate buyers by their lifecycle stage.
                            Click <strong>Deploy Segment</strong> on any cohort below to generate an automated
                            dynamic segment with pre-set criteria ready for campaign targeting.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- =====================================================
         3. KPI OVERVIEW
    ====================================================== --}}
    <div class="surface-card mb-3">

        <div class="section-header">
            <h6 class="section-title">
                <i class="fas fa-chart-pie"></i>
                Pre-Engineered Cohort Blueprints
            </h6>

            <button type="button"
                    class="collapse-toggle-btn"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseCohortKpis"
                    aria-expanded="true"
                    aria-controls="collapseCohortKpis"
                    aria-label="Toggle cohort overview">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseCohortKpis">
            <div class="p-3">

                <div class="row g-2">

                    {{-- Total Blueprints --}}
                    <div class="col-6 col-xl-3">
                        <div class="metric-card metric-card-blue">

                            <div class="metric-top">
                                <span class="stat-label-title">
                                    Active Blueprints
                                </span>

                                <div class="metric-icon-square bg-primary-subtle text-primary">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                            </div>

                            <div>
                                <h4 class="stat-val-num">
                                    5 Presets + 1 Custom
                                </h4>
                                <small class="stat-sub-text">
                                    Ready to deploy
                                </small>
                            </div>

                        </div>
                    </div>

                    {{-- High LTV --}}
                    <div class="col-6 col-xl-3">
                        <div class="metric-card metric-card-amber">

                            <div class="metric-top">
                                <span class="stat-label-title">
                                    High-LTV Cohorts
                                </span>

                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis">
                                    <i class="fas fa-crown"></i>
                                </div>
                            </div>

                            <div>
                                <h4 class="stat-val-num text-warning-emphasis">
                                    VIP &amp; Repeat
                                </h4>
                                <small class="stat-sub-text">
                                    2+ &amp; 3+ orders criteria
                                </small>
                            </div>

                        </div>
                    </div>

                    {{-- Churn Protection --}}
                    <div class="col-6 col-xl-3">
                        <div class="metric-card metric-card-green">

                            <div class="metric-top">
                                <span class="stat-label-title">
                                    Churn Protection
                                </span>

                                <div class="metric-icon-square bg-success-subtle text-success">
                                    <i class="fas fa-clock-rotate-left"></i>
                                </div>
                            </div>

                            <div>
                                <h4 class="stat-val-num text-success">
                                    Win-Back (60d)
                                </h4>
                                <small class="stat-sub-text">
                                    Reactivation triggers
                                </small>
                            </div>

                        </div>
                    </div>

                    {{-- Cart Recovery --}}
                    <div class="col-6 col-xl-3">
                        <div class="metric-card metric-card-purple">

                            <div class="metric-top">
                                <span class="stat-label-title">
                                    High-Intent Funnel
                                </span>

                                <div class="metric-icon-square"
                                     style="background:#f3e8ff;color:#7e22ce;">
                                    <i class="fas fa-cart-shopping"></i>
                                </div>
                            </div>

                            <div>
                                <h4 class="stat-val-num">
                                    Cart Recovery
                                </h4>
                                <small class="stat-sub-text">
                                    Abandoned basket focus
                                </small>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- =====================================================
         4. SEARCH FILTER
    ====================================================== --}}
    <div class="surface-card search-card mb-3">

        <div class="row g-2 align-items-center">

            <div class="col-12 col-md-7">
                <div class="input-group">

                    <span class="input-group-text bg-light text-muted border-end-0">
                        <i class="fas fa-search" style="font-size:11px;"></i>
                    </span>

                    <input type="text"
                           id="personaSearchInput"
                           class="form-control search-input border-start-0 ps-0 shadow-none"
                           placeholder="Search cohorts by title, criteria or strategy..."
                           autocomplete="off"
                           aria-label="Search customer cohorts">
                </div>
            </div>

            <div class="col-12 col-md-5 text-md-end">
                <span class="search-counter" id="visible-persona-count" aria-live="polite">
                    6 of 6 Cohorts Showing
                </span>
            </div>

        </div>
    </div>

    {{-- =====================================================
         5. PERSONA GRID
    ====================================================== --}}
    <div class="row g-3" id="personaCardsContainer">

        {{-- GROUP 1: VIP HIGH SPENDERS --}}
        <div class="col-12 col-md-6 col-xl-4 persona-grid-item"
             data-search="vip high spenders 3+ orders lifetime ltv top margin">

            <div class="persona-card accent-amber">

                <div class="card-body">

                    <div>
                        <div class="persona-topline">

                            <span class="persona-label bg-warning-subtle text-warning-emphasis">
                                <i class="fas fa-crown"></i>
                                VIP TIER
                            </span>

                            <span class="persona-meta">
                                <i class="fas fa-coins text-warning me-1"></i>
                                High LTV
                            </span>

                        </div>

                        <h6 class="persona-title">
                            VIP &amp; High Spenders
                        </h6>

                        <p class="persona-description">
                            Top-tier spenders who have placed 3+ completed orders and represent maximum lifetime margin for your store.
                        </p>

                        <div class="strategy-box">
                            <strong>Recommended Strategy</strong>
                            <span>
                                Early access to launches, concierge VIP vouchers, and exclusive gift tokens.
                            </span>
                        </div>
                    </div>

                    <form action="{{ $segmentsStoreRoute }}"
                          method="POST"
                          id="formCohortVip">
                        @csrf

                        <input type="hidden" name="name" value="VIP High-Spenders (3+ Orders)">
                        <input type="hidden" name="type" value="dynamic">
                        <input type="hidden" name="description" value="Automated segment for top customers with at least 3 completed orders.">
                        <input type="hidden" name="filter_criteria[min_orders]" value="3">

                        <button type="button"
                                class="btn btn-outline-dark btn-compact persona-action"
                                onclick="confirmCohortDeploy('VIP High-Spenders (3+ Orders)', 'formCohortVip', '3+ completed orders')">
                            <i class="fas fa-layer-group text-warning"></i>
                            Deploy Segment
                            <i class="fas fa-arrow-right ms-auto"></i>
                        </button>
                    </form>

                </div>
            </div>
        </div>

        {{-- GROUP 2: REPEAT LOYAL BUYERS --}}
        <div class="col-12 col-md-6 col-xl-4 persona-grid-item"
             data-search="repeat loyal buyers 2+ orders retention trust cross selling">

            <div class="persona-card accent-green">

                <div class="card-body">

                    <div>
                        <div class="persona-topline">

                            <span class="persona-label bg-success-subtle text-success">
                                <i class="fas fa-repeat"></i>
                                RETENTION
                            </span>

                            <span class="persona-meta">
                                <i class="fas fa-shield-halved text-success me-1"></i>
                                Verified Trust
                            </span>

                        </div>

                        <h6 class="persona-title">
                            Repeat Loyal Buyers
                        </h6>

                        <p class="persona-description">
                            Customers who placed at least 2 completed orders. They have validated your shipping, packaging, and quality.
                        </p>

                        <div class="strategy-box">
                            <strong>Recommended Strategy</strong>
                            <span>
                                Cross-selling accessories, monthly refill alerts, and seasonal loyalty promotions.
                            </span>
                        </div>
                    </div>

                    <form action="{{ $segmentsStoreRoute }}"
                          method="POST"
                          id="formCohortLoyal">
                        @csrf

                        <input type="hidden" name="name" value="Loyal Repeat Buyers (2+ Orders)">
                        <input type="hidden" name="type" value="dynamic">
                        <input type="hidden" name="description" value="Automated segment for customers who placed at least 2 orders.">
                        <input type="hidden" name="filter_criteria[min_orders]" value="2">

                        <button type="button"
                                class="btn btn-outline-success btn-compact persona-action"
                                onclick="confirmCohortDeploy('Loyal Repeat Buyers (2+ Orders)', 'formCohortLoyal', '2+ completed orders')">
                            <i class="fas fa-layer-group text-success"></i>
                            Deploy Segment
                            <i class="fas fa-arrow-right ms-auto"></i>
                        </button>
                    </form>

                </div>
            </div>
        </div>

        {{-- GROUP 3: WIN BACK --}}
        <div class="col-12 col-md-6 col-xl-4 persona-grid-item"
             data-search="inactive customers win back 60+ days churn risk re-engagement discount">

            <div class="persona-card accent-red">

                <div class="card-body">

                    <div>
                        <div class="persona-topline">

                            <span class="persona-label bg-danger-subtle text-danger">
                                <i class="fas fa-triangle-exclamation"></i>
                                CHURN RISK
                            </span>

                            <span class="persona-meta">
                                <i class="fas fa-clock-rotate-left text-danger me-1"></i>
                                60+ Days
                            </span>

                        </div>

                        <h6 class="persona-title">
                            Inactive Customers (Win-Back)
                        </h6>

                        <p class="persona-description">
                            Subscribers who haven't placed an order in the last 60 days. They are at risk of churning without a re-activation trigger.
                        </p>

                        <div class="strategy-box">
                            <strong>Recommended Strategy</strong>
                            <span>
                                "We Miss You" sequence with a steep 15% discount code and trending arrivals.
                            </span>
                        </div>
                    </div>

                    <form action="{{ $segmentsStoreRoute }}"
                          method="POST"
                          id="formCohortWinback">
                        @csrf

                        <input type="hidden" name="name" value="Inactive Win-Back (60+ Days)">
                        <input type="hidden" name="type" value="dynamic">
                        <input type="hidden" name="description" value="Customers who have not completed an order in 60 days.">
                        <input type="hidden" name="filter_criteria[inactive_days]" value="60">

                        <button type="button"
                                class="btn btn-outline-danger btn-compact persona-action"
                                onclick="confirmCohortDeploy('Inactive Win-Back (60+ Days)', 'formCohortWinback', '60+ days without order')">
                            <i class="fas fa-layer-group text-danger"></i>
                            Deploy Segment
                            <i class="fas fa-arrow-right ms-auto"></i>
                        </button>
                    </form>

                </div>
            </div>
        </div>

        {{-- GROUP 4: CART ABANDONERS --}}
        <div class="col-12 col-md-6 col-xl-4 persona-grid-item"
             data-search="active cart abandoners high intent high conversion checkout basket">

            <div class="persona-card accent-blue">

                <div class="card-body">

                    <div>
                        <div class="persona-topline">

                            <span class="persona-label bg-primary-subtle text-primary">
                                <i class="fas fa-cart-shopping"></i>
                                HIGH INTENT
                            </span>

                            <span class="persona-meta">
                                <i class="fas fa-bolt text-primary me-1"></i>
                                High Conversion
                            </span>

                        </div>

                        <h6 class="persona-title">
                            Active Cart Abandoners
                        </h6>

                        <p class="persona-description">
                            Shoppers who added products to their shopping cart but did not complete the checkout order.
                        </p>

                        <div class="strategy-box">
                            <strong>Recommended Strategy</strong>
                            <span>
                                Automated 1-click cart restoration link with free shipping or a time-sensitive incentive.
                            </span>
                        </div>
                    </div>

                    <form action="{{ $segmentsStoreRoute }}"
                          method="POST"
                          id="formCohortCart">
                        @csrf

                        <input type="hidden" name="name" value="Cart Abandoners Segment">
                        <input type="hidden" name="type" value="dynamic">
                        <input type="hidden" name="description" value="Audience with active unrecovered abandoned shopping sessions.">
                        <input type="hidden" name="filter_criteria[has_abandoned_cart]" value="1">

                        <button type="button"
                                class="btn btn-outline-primary btn-compact persona-action"
                                onclick="confirmCohortDeploy('Cart Abandoners Segment', 'formCohortCart', 'unrecovered cart session')">
                            <i class="fas fa-layer-group text-primary"></i>
                            Deploy Segment
                            <i class="fas fa-arrow-right ms-auto"></i>
                        </button>
                    </form>

                </div>
            </div>
        </div>

        {{-- GROUP 5: NEWSLETTER PROSPECTS --}}
        <div class="col-12 col-md-6 col-xl-4 persona-grid-item"
             data-search="newsletter prospects 0 orders cold subscribers welcome first order">

            <div class="persona-card accent-purple">

                <div class="card-body">

                    <div>
                        <div class="persona-topline">

                            <span class="persona-label"
                                  style="background:#f3e8ff;color:#7e22ce;">
                                <i class="fas fa-envelope-open-text"></i>
                                PROSPECTS
                            </span>

                            <span class="persona-meta">
                                <i class="fas fa-user-plus text-primary me-1"></i>
                                Consented
                            </span>

                        </div>

                        <h6 class="persona-title">
                            Newsletter Prospects (0 Orders)
                        </h6>

                        <p class="persona-description">
                            Subscribers who opted-in via newsletter popup or footer but have never placed their first purchase.
                        </p>

                        <div class="strategy-box">
                            <strong>Recommended Strategy</strong>
                            <span>
                                First-order introductory discount code, best-seller showcases, and trust badges.
                            </span>
                        </div>
                    </div>

                    <a href="{{ $segmentsCreateRoute }}"
                       class="btn btn-outline-secondary btn-compact persona-action">
                        <i class="fas fa-sliders text-primary"></i>
                        Configure Manually
                        <i class="fas fa-arrow-right ms-auto"></i>
                    </a>

                </div>
            </div>
        </div>

        {{-- GROUP 6: CUSTOM AUDIENCE --}}
        <div class="col-12 col-md-6 col-xl-4 persona-grid-item"
             data-search="custom audience rule multi variable segment builder filter category order">

            <div class="custom-segment-card">

                <div>
                    <div class="custom-segment-icon">
                        <i class="fas fa-plus"></i>
                    </div>

                    <h6 class="persona-title">
                        Custom Audience Rule
                    </h6>

                    <p class="persona-description mb-3">
                        Build custom multi-variable segment filters combining categories, order totals, and cart triggers.
                    </p>

                    <a href="{{ $segmentsCreateRoute }}"
                       class="btn btn-primary btn-compact px-3">
                        <i class="fas fa-plus-circle"></i>
                        Create Custom Segment
                    </a>
                </div>

            </div>
        </div>

    </div>

    {{-- SEARCH EMPTY STATE --}}
    <div class="empty-search-state mt-3" id="personaEmptyState">
        <i class="fas fa-search d-block"></i>

        <h6>No Matching Cohorts Found</h6>

        <p>
            Try another keyword or clear your search to view all available customer cohorts.
        </p>

        <button type="button"
                class="btn btn-sm btn-outline-primary btn-compact"
                id="clearPersonaSearch">
            <i class="fas fa-rotate-left"></i>
            Clear Search
        </button>
    </div>

</div>

{{-- =========================================================
     6. DEPLOYMENT CONFIRMATION MODAL
========================================================= --}}
<div class="modal fade"
     id="deployCohortModal"
     tabindex="-1"
     aria-labelledby="deployCohortModalTitle"
     aria-hidden="true"
     style="z-index:106500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content p-3">

            <div class="modal-body p-1 text-center">

                <div class="modal-icon mx-auto mb-3">
                    <i class="fas fa-layer-group"></i>
                </div>

                <h6 class="modal-title mb-2" id="deployCohortModalTitle">
                    Deploy Dynamic Segment?
                </h6>

                <p class="modal-description mb-0">
                    Generate an automated dynamic segment for
                    <strong id="modalCohortName" class="text-dark">Cohort</strong>
                    with the following rule:
                </p>

                <div class="bg-light border rounded p-2 mt-2">
                    <code id="modalCohortRule"
                          class="small fw-bold text-primary">criteria</code>
                </div>

                <div class="d-flex justify-content-center gap-2 mt-3">

                    <button type="button"
                            class="btn btn-light border btn-compact"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="button"
                            class="btn btn-primary btn-compact"
                            id="btnConfirmDeploy">
                        <i class="fas fa-check"></i>
                        Yes, Deploy
                    </button>

                </div>

            </div>
        </div>
    </div>
</div>

{{-- =========================================================
     7. PAGE JAVASCRIPT
========================================================= --}}
<script>
(function () {
    'use strict';

    const root = document.getElementById('email-marketing-customer-groups-root');

    if (!root || root.dataset.initialized === 'true') {
        return;
    }

    root.dataset.initialized = 'true';

    let activeFormId = null;

    const modalElement = document.getElementById('deployCohortModal');
    const modalName = document.getElementById('modalCohortName');
    const modalRule = document.getElementById('modalCohortRule');
    const confirmButton = document.getElementById('btnConfirmDeploy');

    const searchInput = root.querySelector('#personaSearchInput');
    const searchCounter = root.querySelector('#visible-persona-count');
    const emptyState = root.querySelector('#personaEmptyState');
    const clearSearchButton = root.querySelector('#clearPersonaSearch');

    const personaItems = Array.from(
        root.querySelectorAll('.persona-grid-item')
    );

    const totalCohorts = personaItems.length;

    /**
     * Open deployment confirmation modal.
     */
    window.confirmCohortDeploy = function (cohortName, formId, ruleText) {

        const form = document.getElementById(formId);

        if (!form) {
            console.error('Cohort form not found:', formId);
            return;
        }

        activeFormId = formId;

        if (modalName) {
            modalName.textContent = cohortName;
        }

        if (modalRule) {
            modalRule.textContent = ruleText;
        }

        if (modalElement && window.bootstrap && bootstrap.Modal) {

            bootstrap.Modal.getOrCreateInstance(modalElement).show();

        } else {

            const shouldSubmit = window.confirm(
                'Deploy dynamic segment for ' + cohortName + '?'
            );

            if (shouldSubmit) {
                form.requestSubmit();
            }
        }
    };

    /**
     * Confirm and submit selected cohort form.
     */
    if (confirmButton) {

        confirmButton.addEventListener('click', function () {

            if (!activeFormId) {
                return;
            }

            const selectedForm = document.getElementById(activeFormId);

            if (!selectedForm) {
                console.error('Selected cohort form does not exist.');
                return;
            }

            confirmButton.disabled = true;

            const originalText = confirmButton.innerHTML;

            confirmButton.innerHTML =
                '<i class="fas fa-spinner fa-spin"></i> Deploying...';

            selectedForm.requestSubmit();

            /*
             * The page normally navigates after successful form submission.
             * Restore the button if submission is prevented by another script.
             */
            window.setTimeout(function () {

                if (document.body.contains(confirmButton)) {
                    confirmButton.disabled = false;
                    confirmButton.innerHTML = originalText;
                }

            }, 3000);

        });
    }

    /**
     * Live persona search.
     */
    function filterPersonaGrid() {

        const query = (searchInput?.value || '')
            .toLowerCase()
            .trim();

        let visibleCount = 0;

        personaItems.forEach(function (item) {

            const searchData = (
                item.getAttribute('data-search') || ''
            ).toLowerCase();

            const cardText = (
                item.textContent || ''
            ).toLowerCase();

            const isVisible = !query ||
                searchData.includes(query) ||
                cardText.includes(query);

            item.style.display = isVisible ? '' : 'none';

            if (isVisible) {
                visibleCount++;
            }

        });

        if (searchCounter) {

            searchCounter.textContent =
                visibleCount + ' of ' + totalCohorts + ' Cohorts Showing';

        }

        if (emptyState) {
            emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
        }

    }

    /**
     * Search input event.
     */
    if (searchInput) {

        searchInput.addEventListener('input', filterPersonaGrid);

        searchInput.addEventListener('keydown', function (event) {

            if (event.key === 'Escape') {
                searchInput.value = '';
                filterPersonaGrid();
            }

        });

    }

    /**
     * Clear search.
     */
    if (clearSearchButton) {

        clearSearchButton.addEventListener('click', function () {

            if (searchInput) {
                searchInput.value = '';
                searchInput.focus();
            }

            filterPersonaGrid();

        });

    }

})();
</script>

@endsection
