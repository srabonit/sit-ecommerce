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
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-customer-groups-root .btn,
    #email-marketing-customer-groups-root .btn-sm,
    #email-marketing-customer-groups-root button,
    #email-marketing-customer-groups-root .badge,
    #email-marketing-customer-groups-root .alert,
    #email-marketing-customer-groups-root .form-control,
    #email-marketing-customer-groups-root .form-select,
    #email-marketing-customer-groups-root .input-group-text,
    #email-marketing-customer-groups-root .card,
    #email-marketing-customer-groups-root .metric-card,
    #email-marketing-customer-groups-root .persona-card,
    #email-marketing-customer-groups-root .collapse-toggle-btn,
    .cohort-modal-scope .modal-content,
    .cohort-modal-scope .btn {
        border-radius: 5px !important;
    }

    #email-marketing-customer-groups-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #email-marketing-customer-groups-root .input-group > .form-control {
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
        font-size: 11px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    /* Collapsible Trigger Buttons & Rotating Chevron */
    .collapse-toggle-btn {
        width: 24px;
        height: 24px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        color: #475569;
        transition: all 0.2s ease;
    }
    .collapse-toggle-btn:hover {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }
    .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }
    .collapse-icon {
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        font-size: 10px;
    }

    /* Enterprise Surface Cards */
    #email-marketing-customer-groups-root .card {
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }
    .section-header-wrap {
        padding: 10px 14px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
    }

    /* Metric Cards - Zero Overflow Architecture */
    .metric-card {
        padding: 12px 14px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }
    .stat-label-title {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.02em;
        line-height: 1.2;
    }
    .stat-val-num {
        font-size: 18px;
        line-height: 1.25;
        letter-spacing: -0.02em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .stat-sub-text {
        font-size: 10px;
        margin-top: 2px;
    }
    .metric-icon-square {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
        border-radius: 5px !important;
    }

    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-left: 3.5px solid #f59e0b !important; }
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-left: 3.5px solid #10b981 !important; }
    .metric-card-blue   { background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%); border-left: 3.5px solid #2563eb !important; }
    .metric-card-purple { background: linear-gradient(180deg, #ffffff 0%, #faf5ff 100%); border-left: 3.5px solid #8b5cf6 !important; }

    /* Persona Grid Cards */
    .persona-card {
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .persona-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
    }
    .strategy-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 8px 10px;
        border-radius: 5px !important;
    }

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .persona-header-wrap h5 {
            font-size: 16px !important;
        }
        .top-action-btns {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .top-action-btns .btn {
            width: 100%;
            padding: 8px 6px !important;
            font-size: 11px !important;
            justify-content: center;
        }
        .metric-card {
            padding: 10px !important;
            min-height: 84px !important;
        }
        .stat-label-title {
            font-size: 10px !important;
        }
        .stat-val-num {
            font-size: 14px !important;
        }
        .stat-sub-text {
            font-size: 9px !important;
        }
        .metric-icon-square {
            width: 28px !important;
            height: 28px !important;
            font-size: 11px !important;
        }
        #email-marketing-customer-groups-root .card {
            margin-bottom: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-customer-groups-root">

    {{-- 1. Header & Navigation --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 persona-header-wrap">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-users-rectangle text-primary"></i>
                <span>Customer Personas &amp; Groups</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Strategic customer groupings based on purchase frequency, spending threshold, and engagement lifecycle.
            </small>
        </div>

        <div class="d-flex gap-2 top-action-btns">
            <a href="{{ $segmentsIndexRoute }}" class="btn btn-sm btn-outline-primary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-filter-circle-dollar me-1"></i> <span>Active Segments</span>
            </a>
            <a href="{{ $campaignsCreateRoute }}" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold">
                <i class="fas fa-plus-circle me-1"></i> <span>New Campaign</span>
            </a>
        </div>
    </div>

    {{-- 2. Value Proposition Banner (Collapsible Section) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-compass text-primary me-1"></i> Targeted Customer Lifecycle Cohorts &amp; Strategy
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseValueBanner" aria-expanded="true" aria-controls="collapseValueBanner" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseValueBanner">
            <div class="p-3">
                <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2">
                    <div class="rounded bg-primary-subtle text-primary p-2 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; border-radius: 5px !important;">
                        <i class="fas fa-sitemap fs-5"></i>
                    </div>
                    <div>
                        <strong class="text-dark d-block mb-1" style="font-size: 13px;">Deploy Intelligent Lifecycle Segments</strong>
                        <p class="text-muted small mb-0" style="font-size: 11px; line-height: 1.45;">
                            Instead of sending generic mass blasts, isolate buyers by their lifecycle stage. Click <strong>"Deploy Segment"</strong> on any cohort below to generate an automated dynamic segment with pre-set criteria ready for immediate broadcast targeting.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Telemetry KPI Overview (Collapsible Section) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-chart-pie text-primary me-1"></i> Pre-Engineered Cohort Blueprints
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseCohortKpis" aria-expanded="true" aria-controls="collapseCohortKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseCohortKpis">
            <div class="p-3">
                <div class="row g-2">
                    {{-- Total Blueprints --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Active Blueprints</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    5 Presets + 1 Custom
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Ready to deploy</small>
                            </div>
                        </div>
                    </div>

                    {{-- Retention & High Margin --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">High-LTV Cohorts</span>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                                    <i class="fas fa-crown"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-warning-emphasis bn-digit font-monospace stat-val-num">
                                    VIP &amp; Repeat
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">2+ &amp; 3+ orders criteria</small>
                            </div>
                        </div>
                    </div>

                    {{-- Churn Prevention --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Churn Protection</span>
                                <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                                    <i class="fas fa-clock-rotate-left"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num">
                                    Win-Back (60d)
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Reactivation triggers</small>
                            </div>
                        </div>
                    </div>

                    {{-- Conversion Acceleration --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-purple h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">High-Intent Funnel</span>
                                <div class="metric-icon-square bg-purple-subtle text-primary shadow-xs" style="background-color: #f3e8ff; color: #7e22ce;">
                                    <i class="fas fa-cart-shopping"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    Cart Recovery
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Abandoned basket focus</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Instant Live Search Filter Bar --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="p-3">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                        <input type="text" id="personaSearchInput" class="form-control border-start-0 ps-0 shadow-none font-monospace" placeholder="Search cohorts by title, criteria or strategy..." onkeyup="filterPersonaGrid()">
                    </div>
                </div>
                <div class="col-12 col-md-6 text-md-end">
                    <span class="text-muted font-monospace small" style="font-size: 10px;" id="visible-persona-count">
                        6 Strategic Cohorts Available
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. Persona Grid --}}
    <div class="row g-3" id="personaCardsContainer">
        {{-- Group 1: VIP High-Spenders --}}
        <div class="col-12 col-md-6 col-xl-4 persona-grid-item" data-search="vip high spenders 3+ orders lifetime ltv top margin">
            <div class="card persona-card border-0 shadow-sm h-100 mb-0" style="border-top: 3.5px solid #f59e0b !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace px-2 py-1" style="font-size: 9.5px;">
                                <i class="fas fa-crown me-1 text-warning"></i> VIP TIER
                            </span>
                            <span class="text-muted font-monospace small" style="font-size: 10px;">
                                <i class="fas fa-coins me-1 text-warning"></i> High LTV
                            </span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 13.5px;">VIP &amp; High Spenders</h6>
                        <p class="text-muted small mb-2" style="font-size: 11px; line-height: 1.45;">
                            Top-tier spenders who have placed 3+ completed orders and represent maximum lifetime margin for your store.
                        </p>
                        <div class="strategy-box mb-3" style="font-size: 11px; line-height: 1.4;">
                            <span class="fw-bold text-dark d-block mb-1">Recommended Strategy:</span>
                            <span class="text-muted">Early access to launches, concierge VIP vouchers, and exclusive gift tokens.</span>
                        </div>
                    </div>
                    <form action="{{ $segmentsStoreRoute }}" method="POST" id="formCohortVip">
                        @csrf
                        <input type="hidden" name="name" value="VIP High-Spenders (3+ Orders)">
                        <input type="hidden" name="type" value="dynamic">
                        <input type="hidden" name="description" value="Automated segment for top customers with at least 3 completed orders.">
                        <input type="hidden" name="filter_criteria[min_orders]" value="3">
                        <button type="button" class="btn btn-sm btn-outline-dark btn-compact w-100 py-2 shadow-xs fw-semibold" onclick="confirmCohortDeploy('VIP High-Spenders (3+ Orders)', 'formCohortVip', '3+ completed orders')">
                            <i class="fas fa-layer-group me-1 text-warning"></i> <span>Deploy Segment &rarr;</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Group 2: Repeat Loyal Customers --}}
        <div class="col-12 col-md-6 col-xl-4 persona-grid-item" data-search="repeat loyal buyers 2+ orders retention trust cross selling">
            <div class="card persona-card border-0 shadow-sm h-100 mb-0" style="border-top: 3.5px solid #10b981 !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1" style="font-size: 9.5px;">
                                <i class="fas fa-repeat me-1"></i> RETENTION
                            </span>
                            <span class="text-muted font-monospace small" style="font-size: 10px;">
                                <i class="fas fa-shield-halved me-1 text-success"></i> Verified Trust
                            </span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 13.5px;">Repeat Loyal Buyers</h6>
                        <p class="text-muted small mb-2" style="font-size: 11px; line-height: 1.45;">
                            Customers who placed at least 2 completed orders. They have validated your shipping, packaging, and quality.
                        </p>
                        <div class="strategy-box mb-3" style="font-size: 11px; line-height: 1.4;">
                            <span class="fw-bold text-dark d-block mb-1">Recommended Strategy:</span>
                            <span class="text-muted">Cross-selling accessories, monthly refill alerts, and seasonal loyalty promotions.</span>
                        </div>
                    </div>
                    <form action="{{ $segmentsStoreRoute }}" method="POST" id="formCohortLoyal">
                        @csrf
                        <input type="hidden" name="name" value="Loyal Repeat Buyers (2+ Orders)">
                        <input type="hidden" name="type" value="dynamic">
                        <input type="hidden" name="description" value="Automated segment for customers who placed at least 2 orders.">
                        <input type="hidden" name="filter_criteria[min_orders]" value="2">
                        <button type="button" class="btn btn-sm btn-outline-success btn-compact w-100 py-2 shadow-xs fw-semibold" onclick="confirmCohortDeploy('Loyal Repeat Buyers (2+ Orders)', 'formCohortLoyal', '2+ completed orders')">
                            <i class="fas fa-layer-group me-1 text-success"></i> <span>Deploy Segment &rarr;</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Group 3: At-Risk & Churn Inactive --}}
        <div class="col-12 col-md-6 col-xl-4 persona-grid-item" data-search="inactive customers win back 60+ days churn risk re-engagement discount">
            <div class="card persona-card border-0 shadow-sm h-100 mb-0" style="border-top: 3.5px solid #ef4444 !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace px-2 py-1" style="font-size: 9.5px;">
                                <i class="fas fa-triangle-exclamation me-1"></i> CHURN RISK
                            </span>
                            <span class="text-muted font-monospace small" style="font-size: 10px;">
                                <i class="fas fa-clock-rotate-left me-1 text-danger"></i> 60+ Days
                            </span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 13.5px;">Inactive Customers (Win-Back)</h6>
                        <p class="text-muted small mb-2" style="font-size: 11px; line-height: 1.45;">
                            Subscribers who haven't placed an order in the last 60 days. They are at risk of churning without a re-activation trigger.
                        </p>
                        <div class="strategy-box mb-3" style="font-size: 11px; line-height: 1.4;">
                            <span class="fw-bold text-dark d-block mb-1">Recommended Strategy:</span>
                            <span class="text-muted">"We Miss You" sequence with a steep 15% discount code and trending arrivals.</span>
                        </div>
                    </div>
                    <form action="{{ $segmentsStoreRoute }}" method="POST" id="formCohortWinback">
                        @csrf
                        <input type="hidden" name="name" value="Inactive Win-Back (60+ Days)">
                        <input type="hidden" name="type" value="dynamic">
                        <input type="hidden" name="description" value="Customers who have not completed an order in 60 days.">
                        <input type="hidden" name="filter_criteria[inactive_days]" value="60">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-compact w-100 py-2 shadow-xs fw-semibold" onclick="confirmCohortDeploy('Inactive Win-Back (60+ Days)', 'formCohortWinback', '60+ days without order')">
                            <i class="fas fa-layer-group me-1 text-danger"></i> <span>Deploy Segment &rarr;</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Group 4: Cart Abandoners --}}
        <div class="col-12 col-md-6 col-xl-4 persona-grid-item" data-search="active cart abandoners high intent high conversion checkout basket">
            <div class="card persona-card border-0 shadow-sm h-100 mb-0" style="border-top: 3.5px solid #2563eb !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1" style="font-size: 9.5px;">
                                <i class="fas fa-cart-shopping me-1"></i> HIGH INTENT
                            </span>
                            <span class="text-muted font-monospace small" style="font-size: 10px;">
                                <i class="fas fa-bolt me-1 text-primary"></i> High Conversion
                            </span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 13.5px;">Active Cart Abandoners</h6>
                        <p class="text-muted small mb-2" style="font-size: 11px; line-height: 1.45;">
                            Shoppers who added products to their shopping cart but did not complete the checkout order.
                        </p>
                        <div class="strategy-box mb-3" style="font-size: 11px; line-height: 1.4;">
                            <span class="fw-bold text-dark d-block mb-1">Recommended Strategy:</span>
                            <span class="text-muted">Automated 1-click cart restoration link with free shipping or a time-sensitive incentive.</span>
                        </div>
                    </div>
                    <form action="{{ $segmentsStoreRoute }}" method="POST" id="formCohortCart">
                        @csrf
                        <input type="hidden" name="name" value="Cart Abandoners Segment">
                        <input type="hidden" name="type" value="dynamic">
                        <input type="hidden" name="description" value="Audience with active unrecovered abandoned shopping sessions.">
                        <input type="hidden" name="filter_criteria[has_abandoned_cart]" value="1">
                        <button type="button" class="btn btn-sm btn-outline-primary btn-compact w-100 py-2 shadow-xs fw-semibold" onclick="confirmCohortDeploy('Cart Abandoners Segment', 'formCohortCart', 'unrecovered cart session')">
                            <i class="fas fa-layer-group me-1 text-primary"></i> <span>Deploy Segment &rarr;</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Group 5: Cold Subscribers (0 Purchases) --}}
        <div class="col-12 col-md-6 col-xl-4 persona-grid-item" data-search="newsletter prospects 0 orders cold subscribers welcome first order">
            <div class="card persona-card border-0 shadow-sm h-100 mb-0" style="border-top: 3.5px solid #8b5cf6 !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-purple-subtle text-primary border font-monospace px-2 py-1" style="background-color: #f3e8ff; color: #7e22ce; font-size: 9.5px;">
                                <i class="fas fa-envelope-open-text me-1"></i> PROSPECTS
                            </span>
                            <span class="text-muted font-monospace small" style="font-size: 10px;">
                                <i class="fas fa-user-plus me-1 text-primary"></i> Consented
                            </span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 13.5px;">Newsletter Prospects (0 Orders)</h6>
                        <p class="text-muted small mb-2" style="font-size: 11px; line-height: 1.45;">
                            Subscribers who opted-in via newsletter popup or footer but have never placed their first purchase.
                        </p>
                        <div class="strategy-box mb-3" style="font-size: 11px; line-height: 1.4;">
                            <span class="fw-bold text-dark d-block mb-1">Recommended Strategy:</span>
                            <span class="text-muted">First-order introductory discount code, best-seller showcases, and trust badges.</span>
                        </div>
                    </div>
                    <a href="{{ $segmentsCreateRoute }}" class="btn btn-sm btn-outline-secondary btn-compact w-100 py-2 shadow-xs fw-semibold">
                        <i class="fas fa-sliders me-1 text-primary"></i> <span>Configure Manually &rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Group 6: Custom Segment Builder --}}
        <div class="col-12 col-md-6 col-xl-4 persona-grid-item" data-search="custom audience rule multi variable segment builder filter category order">
            <div class="card border-0 shadow-sm h-100 mb-0" style="border: 1.5px dashed #cbd5e1; background: #f8fafc;">
                <div class="card-body p-3 d-flex flex-column justify-content-center text-center">
                    <div class="text-primary mb-2">
                        <div class="bg-primary-subtle text-primary p-2 rounded-circle d-inline-flex align-items-center justify-content-center mx-auto" style="width: 44px; height: 44px; border-radius: 50% !important;">
                            <i class="fas fa-plus fs-5"></i>
                        </div>
                    </div>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 13.5px;">Custom Audience Rule</h6>
                    <p class="text-muted small mb-3" style="font-size: 11px; line-height: 1.45;">
                        Build custom multi-variable segment filters combining categories, order totals, and cart triggers.
                    </p>
                    <div>
                        <a href="{{ $segmentsCreateRoute }}" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold px-3 py-2">
                            <i class="fas fa-plus-circle me-1"></i> <span>Create Custom Segment</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Cohort Segment Deployment Confirmation (Replaces raw submit) -->
<div class="modal fade cohort-modal-scope" id="deployCohortModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 44px; height: 44px; border-radius: 50% !important;">
                    <i class="fas fa-layer-group fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Deploy Dynamic Segment?</h6>
                <p class="text-muted small mb-2" style="font-size: 11px; line-height: 1.45;">
                    Generate an automated dynamic segment for <strong id="modalCohortName" class="text-dark">Cohort</strong> with rule <code id="modalCohortRule">criteria</code>?
                </p>
                <div class="d-flex justify-content-center gap-2 mt-3">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" id="btnConfirmDeploy">Yes, Deploy</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let activeFormId = null;

    function confirmCohortDeploy(cohortName, formId, ruleText) {
        activeFormId = formId;
        document.getElementById('modalCohortName').innerText = cohortName;
        document.getElementById('modalCohortRule').innerText = ruleText;

        const modalEl = document.getElementById('deployCohortModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    document.getElementById('btnConfirmDeploy')?.addEventListener('click', function() {
        if (activeFormId) {
            document.getElementById(activeFormId)?.submit();
        }
    });

    function filterPersonaGrid() {
        const query = (document.getElementById('personaSearchInput')?.value || '').toLowerCase().trim();
        const items = document.querySelectorAll('.persona-grid-item');
        let visibleCount = 0;

        items.forEach(item => {
            const searchData = item.getAttribute('data-search') || '';
            if (!query || searchData.includes(query)) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        const badge = document.getElementById('visible-persona-count');
        if (badge) {
            badge.innerText = `${visibleCount} of 6 Cohorts Showing`;
        }
    }
</script>
@endsection