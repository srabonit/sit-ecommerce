@extends('layouts.admin')

@section('title', 'Email Marketing Dashboard')

@php
    // Safe dynamic fallbacks for all metrics
    $dateRange           = $dateRange ?? request('range', '30_days');
    $eligibleSubscribers = $eligibleSubscribers ?? 0;
    $totalSubscribers    = $totalSubscribers ?? 0;
    $newSubscribersRange = $newSubscribersRange ?? 0;
    $unsubscribedCount   = $unsubscribedCount ?? 0;
    $bouncedCount        = $bouncedCount ?? 0;

    $openRate  = $openRate ?? 0;
    $clickRate = $clickRate ?? 0;

    $abandonedCartsCount = $abandonedCartsCount ?? 0;
    $recoveredCartsCount = $recoveredCartsCount ?? 0;
    $recoveredRevenue    = $recoveredRevenue ?? 0;
    $activeAutomations   = $activeAutomations ?? 0;

    $recentCampaigns   = $recentCampaigns ?? collect();
    $recentSubscribers = $recentSubscribers ?? collect();

    // Safe route fallbacks
    $dashboardRoute = Route::has('admin.email_marketing.dashboard') 
        ? route('admin.email_marketing.dashboard') 
        : url('admin/email-marketing/dashboard');

    $createCampaignRoute = Route::has('admin.email_marketing.campaigns.create') 
        ? route('admin.email_marketing.campaigns.create') 
        : url('admin/email-marketing/campaigns/create');

    $subscribersIndexRoute = Route::has('admin.email_marketing.subscribers.index') 
        ? route('admin.email_marketing.subscribers.index') 
        : url('admin/email-marketing/subscribers');

    $campaignsIndexRoute = Route::has('admin.email_marketing.campaigns.index') 
        ? route('admin.email_marketing.campaigns.index') 
        : url('admin/email-marketing/campaigns');

    $cartWishlistRoute = Route::has('admin.email_marketing.cart_wishlist_marketing') 
        ? route('admin.email_marketing.cart_wishlist_marketing') 
        : url('admin/email-marketing/cart-wishlist');

    $automationsIndexRoute = Route::has('admin.email_marketing.automations.index') 
        ? route('admin.email_marketing.automations.index') 
        : url('admin/email-marketing/automations');
@endphp

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-dashboard-root .btn,
    #email-marketing-dashboard-root .btn-sm,
    #email-marketing-dashboard-root button,
    #email-marketing-dashboard-root .badge,
    #email-marketing-dashboard-root .alert,
    #email-marketing-dashboard-root .form-control,
    #email-marketing-dashboard-root .form-select,
    #email-marketing-dashboard-root .input-group-text,
    #email-marketing-dashboard-root .card,
    #email-marketing-dashboard-root .metric-card,
    #email-marketing-dashboard-root .progress,
    #email-marketing-dashboard-root .progress-bar,
    #email-marketing-dashboard-root .collapse-toggle-btn {
        border-radius: 5px !important;
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
    #email-marketing-dashboard-root .card {
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

    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-left: 3.5px solid #10b981 !important; }
    .metric-card-blue   { background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%); border-left: 3.5px solid #2563eb !important; }
    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-left: 3.5px solid #f59e0b !important; }
    .metric-card-red    { background: linear-gradient(180deg, #ffffff 0%, #fef2f2 100%); border-left: 3.5px solid #ef4444 !important; }
    .metric-card-cyan   { background: linear-gradient(180deg, #ffffff 0%, #ecfeff 100%); border-left: 3.5px solid #06b6d4 !important; }
    .metric-card-dark   { background: linear-gradient(135deg, #03172e 0%, #0b2d52 100%); color: #ffffff; border: 1px solid #1e293b; }
    .metric-card-rev    { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; border: 1px solid #047857; }

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .custom-dashboard-table {
        width: 100%;
        margin-bottom: 0;
    }
    .custom-dashboard-table thead th {
        font-size: 10.5px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 9px 8px;
        white-space: nowrap;
    }
    .custom-dashboard-table tbody td {
        padding: 8px 8px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        font-size: 12px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .custom-dashboard-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .custom-dashboard-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .custom-dashboard-table th,
        .custom-dashboard-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 7 Columns Proportional Distribution Summing to Exactly 100% */
        .col-dash-name    { width: 30% !important; }
        .col-dash-status  { width: 12% !important; }
        .col-dash-recip   { width: 14% !important; }
        .col-dash-open    { width: 11% !important; }
        .col-dash-click   { width: 11% !important; }
        .col-dash-rev     { width: 14% !important; }
        .col-dash-action  { width: 8% !important; }
    }

    /* Form Controls */
    #email-marketing-dashboard-root .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #email-marketing-dashboard-root .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Subscriber Stream Items */
    .subscriber-stream-item {
        transition: background-color 0.15s ease;
    }
    .subscriber-stream-item:hover {
        background-color: #f8fafc;
    }

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .dashboard-header-wrap h5 {
            font-size: 16px !important;
        }
        .top-action-bar {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .top-action-bar form {
            grid-column: span 2;
        }
        .top-action-bar .btn,
        .top-action-bar select {
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
        .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .custom-dashboard-table {
            min-width: 680px !important;
            table-layout: auto !important;
        }
        #email-marketing-dashboard-root .card {
            margin-bottom: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-dashboard-root">

    {{-- 1. Header & Quick Actions Toolbar (Original Clean Light Theme) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 dashboard-header-wrap">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-chart-line text-primary"></i>
                <span>Email Marketing Dashboard</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Monitor marketing campaigns, subscriber growth, and revenue attribution in real-time.
            </small>
        </div>
        
        <div class="d-flex align-items-center gap-2 top-action-bar">
            {{-- Date Range Filter Dropdown --}}
            <form action="{{ $dashboardRoute }}" method="GET" class="d-inline-flex m-0">
                <select name="range" class="form-select shadow-xs font-monospace" onchange="this.form.submit()" style="font-size: 11px;">
                    <option value="today" {{ $dateRange === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ $dateRange === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="7_days" {{ $dateRange === '7_days' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="30_days" {{ $dateRange === '30_days' ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="this_month" {{ $dateRange === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $dateRange === 'last_month' ? 'selected' : '' }}>Previous Month</option>
                </select>
            </form>

            <a href="{{ $createCampaignRoute }}" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold">
                <i class="fas fa-paper-plane me-1"></i> <span>New Campaign</span>
            </a>
            <a href="{{ $subscribersIndexRoute }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-user-plus me-1"></i> <span>Subscribers</span>
            </a>
        </div>
    </div>

    {{-- 2. ROW 1: Audience & Consent Statistics (Collapsible Section) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-users-check text-success me-1"></i> Audience &amp; Consent Verification Telemetry
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseAudienceStats" aria-expanded="true" aria-controls="collapseAudienceStats" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseAudienceStats">
            <div class="p-3">
                <div class="row g-2">
                    {{-- Eligible Audience --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Eligible Audience</span>
                                <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    {{ number_format($eligibleSubscribers) }}
                                </h4>
                                <div class="d-flex align-items-center gap-1 mt-1">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-1 py-0" style="font-size: 9px;">
                                        Consent Verified
                                    </span>
                                </div>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">
                                    Total in store: <strong>{{ number_format($totalSubscribers) }}</strong>
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- New Subscribers --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">New Subscribers</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    {{ number_format($newSubscribersRange) }}
                                </h4>
                                <small class="text-primary d-block text-truncate font-monospace stat-sub-text">
                                    <i class="fas fa-calendar-alt me-1"></i>{{ ucwords(str_replace('_', ' ', $dateRange)) }}
                                </small>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Growth rate active</small>
                            </div>
                        </div>
                    </div>

                    {{-- Unsubscribed --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Unsubscribed</span>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                                    <i class="fas fa-user-slash"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    {{ number_format($unsubscribedCount) }}
                                </h4>
                                <small class="text-warning-emphasis d-block text-truncate font-monospace stat-sub-text">
                                    <i class="fas fa-user-minus me-1"></i>Opted-out profiles
                                </small>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Suppressed from queue</small>
                            </div>
                        </div>
                    </div>

                    {{-- Bounced / Blocked --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-red h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Bounced / Blocked</span>
                                <div class="metric-icon-square bg-danger-subtle text-danger shadow-xs">
                                    <i class="fas fa-ban"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    {{ number_format($bouncedCount) }}
                                </h4>
                                <small class="text-danger d-block text-truncate font-monospace stat-sub-text">
                                    <i class="fas fa-shield-halved me-1"></i>Suppression List
                                </small>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Protects sender score</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. ROW 2: Campaign Performance & Conversion Metrics (Collapsible Section) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-chart-pie text-primary me-1"></i> Campaign Broadcast Performance &amp; Revenue Attribution
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapsePerformanceMetrics" aria-expanded="true" aria-controls="collapsePerformanceMetrics" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapsePerformanceMetrics">
            <div class="p-3">
                <div class="row g-2">
                    {{-- Delivered Emails --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-dark h-100 mb-0 shadow-xs">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-white text-opacity-75 stat-label-title text-truncate">Delivered Broadcasts</span>
                                <div class="metric-icon-square bg-white bg-opacity-15 text-white shadow-xs">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-white bn-digit font-monospace stat-val-num">
                                    {{ number_format($campaignAggregates->total_delivered ?? 0) }}
                                </h4>
                                <div class="d-flex justify-content-between text-white text-opacity-75 pt-1 mt-1 border-top border-white border-opacity-10 font-monospace" style="font-size: 9.5px;">
                                    <span>Sent: {{ number_format($campaignAggregates->total_sent ?? 0) }}</span>
                                    <span>Failed: {{ number_format($campaignAggregates->total_bounces ?? 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Open Rate --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Open Rate</span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-1 py-0" style="font-size: 9.5px;">
                                    {{ $openRate }}%
                                </span>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    {{ number_format($campaignAggregates->total_opened ?? 0) }}
                                </h4>
                                <div class="progress mt-1 mb-1" style="height: 4px;">
                                    <div class="progress-bar bg-success" style="width: {{ min($openRate, 100) }}%;"></div>
                                </div>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Delivered pixel opens</small>
                            </div>
                        </div>
                    </div>

                    {{-- Click-Through Rate --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Click-Through (CTR)</span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-1 py-0" style="font-size: 9.5px;">
                                    {{ $clickRate }}%
                                </span>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    {{ number_format($campaignAggregates->total_clicked ?? 0) }}
                                </h4>
                                <div class="progress mt-1 mb-1" style="height: 4px;">
                                    <div class="progress-bar bg-primary" style="width: {{ min($clickRate, 100) }}%;"></div>
                                </div>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Verified store link clicks</small>
                            </div>
                        </div>
                    </div>

                    {{-- Attributed Sales Revenue --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-rev h-100 mb-0 shadow-xs">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-white text-opacity-75 stat-label-title text-truncate">Attributed Revenue</span>
                                <div class="metric-icon-square bg-white bg-opacity-20 text-white shadow-xs">
                                    <i class="fas fa-coins"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-white bn-digit font-monospace stat-val-num">
                                    ৳ {{ number_format($campaignAggregates->total_revenue ?? 0, 2) }}
                                </h4>
                                <div class="d-flex justify-content-between text-white text-opacity-75 pt-1 mt-1 border-top border-white border-opacity-10 font-monospace" style="font-size: 9.5px;">
                                    <span>Direct conversions</span>
                                    <span>ROI: High</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. ROW 3: Lifecycle Automation & Abandoned Cart Recovery (Collapsible Section) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-cart-shopping text-warning me-1"></i> Cart Recovery Engine &amp; Automated Drip Pipelines
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseLifecycleAutomation" aria-expanded="true" aria-controls="collapseLifecycleAutomation" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseLifecycleAutomation">
            <div class="p-3">
                <div class="row g-2">
                    {{-- Abandoned Carts --}}
                    <div class="col-12 col-md-4">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <div>
                                    <span class="text-muted stat-label-title text-truncate d-block">Abandoned Carts</span>
                                    <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                        {{ number_format($abandonedCartsCount) }}
                                    </h4>
                                    <small class="text-muted font-monospace stat-sub-text">Unrecovered shopper sessions</small>
                                </div>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                                    <i class="fas fa-cart-arrow-down"></i>
                                </div>
                            </div>
                            <div class="pt-2 border-top mt-2">
                                <a href="{{ $cartWishlistRoute }}" class="text-decoration-none small fw-bold text-primary d-inline-flex align-items-center font-monospace" style="font-size: 11px;">
                                    <span>View Cart Recovery Engine</span> <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Recovered Carts & Value --}}
                    <div class="col-12 col-md-4">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <div>
                                    <span class="text-muted stat-label-title text-truncate d-block">Recovered Orders</span>
                                    <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num">
                                        {{ number_format($recoveredCartsCount) }}
                                    </h4>
                                    <small class="text-success font-monospace stat-sub-text">
                                        Recovered: <strong>৳ {{ number_format($recoveredRevenue, 2) }}</strong>
                                    </small>
                                </div>
                                <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                                    <i class="fas fa-bag-shopping"></i>
                                </div>
                            </div>
                            <div class="pt-2 border-top mt-2">
                                <span class="text-muted small font-monospace" style="font-size: 10.5px;">
                                    <i class="fas fa-bolt text-success me-1"></i> Closed via automated triggers
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Active Automations --}}
                    <div class="col-12 col-md-4">
                        <div class="card metric-card metric-card-cyan h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <div>
                                    <span class="text-muted stat-label-title text-truncate d-block">Active Automations</span>
                                    <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                        {{ number_format($activeAutomations) }}
                                    </h4>
                                    <small class="text-muted font-monospace stat-sub-text">Welcome, Win-back, Order flow</small>
                                </div>
                                <div class="metric-icon-square bg-info-subtle text-info shadow-xs">
                                    <i class="fas fa-robot"></i>
                                </div>
                            </div>
                            <div class="pt-2 border-top mt-2">
                                <a href="{{ $automationsIndexRoute }}" class="text-decoration-none small fw-bold text-primary d-inline-flex align-items-center font-monospace" style="font-size: 11px;">
                                    <span>Configure Workflows</span> <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. ROW 4: Recent Marketing Campaigns & Latest Consented Opt-ins --}}
    <div class="row g-3">
        {{-- Recent Marketing Campaigns Table --}}
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm h-100 overflow-hidden mb-0">
                <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                    <div class="d-flex align-items-center gap-1">
                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 13px;">
                            <i class="fas fa-paper-plane text-primary me-1"></i> Recent Marketing Campaigns
                        </h6>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ $campaignsIndexRoute }}" class="btn btn-sm btn-outline-primary btn-compact shadow-xs" style="font-size: 10.5px !important;">
                            View All
                        </a>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseRecentCampaigns" aria-expanded="true" aria-controls="collapseRecentCampaigns" title="Toggle Campaigns">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="collapse show" id="collapseRecentCampaigns">
                    <div class="table-responsive pc-fit-container">
                        <table class="table align-middle mb-0 custom-dashboard-table">
                            <thead>
                                <tr>
                                    <th class="ps-3 col-dash-name">Campaign &amp; Subject</th>
                                    <th class="col-dash-status">Status</th>
                                    <th class="col-dash-recip">Recipients</th>
                                    <th class="col-dash-open">Open %</th>
                                    <th class="col-dash-click">Click %</th>
                                    <th class="col-dash-rev">Revenue</th>
                                    <th class="text-end pe-3 col-dash-action">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentCampaigns as $c)
                                    @php
                                        $badgeClass = match($c->status) {
                                            'completed'         => 'bg-success-subtle text-success border border-success-subtle',
                                            'sending', 'queued' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                            'scheduled'         => 'bg-info-subtle text-info border border-info-subtle',
                                            'paused'            => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                            'cancelled'         => 'bg-danger-subtle text-danger border border-danger-subtle',
                                            default             => 'bg-light text-dark border',
                                        };

                                        $campaignShowUrl = Route::has('admin.email_marketing.campaigns.show') 
                                            ? route('admin.email_marketing.campaigns.show', $c->id) 
                                            : url('admin/email-marketing/campaigns/' . $c->id);
                                    @endphp
                                    <tr>
                                        <td class="ps-3 col-dash-name">
                                            <div class="min-w-0" style="line-height: 1.3;">
                                                <a href="{{ $campaignShowUrl }}" class="fw-bold text-dark text-decoration-none d-block text-truncate" style="font-size: 12px;" title="{{ $c->name }}">
                                                    {{ $c->name }}
                                                </a>
                                                <small class="text-muted d-block text-truncate font-monospace" style="font-size: 10px;" title="{{ $c->subject }}">
                                                    <i class="fas fa-envelope me-1 text-secondary"></i> {{ $c->subject }}
                                                </small>
                                            </div>
                                        </td>
                                        <td class="col-dash-status">
                                            <span class="badge {{ $badgeClass }} font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 9.5px;">
                                                {{ strtoupper($c->status) }}
                                            </span>
                                        </td>
                                        <td class="col-dash-recip font-monospace bn-digit fw-bold text-dark" style="font-size: 11.5px;">
                                            {{ number_format($c->total_recipients) }}
                                        </td>
                                        <td class="col-dash-open font-monospace bn-digit fw-semibold text-success" style="font-size: 11px;">
                                            {{ $c->open_rate }}%
                                        </td>
                                        <td class="col-dash-click font-monospace bn-digit fw-semibold text-primary" style="font-size: 11px;">
                                            {{ $c->click_rate }}%
                                        </td>
                                        <td class="col-dash-rev font-monospace bn-digit fw-bold text-dark" style="font-size: 11.5px;">
                                            ৳ {{ number_format($c->total_revenue, 2) }}
                                        </td>
                                        <td class="text-end pe-3 col-dash-action">
                                            <a href="{{ $campaignShowUrl }}" class="btn btn-sm btn-light border p-1 text-primary shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; border-radius: 5px !important;" title="View Campaign Analytics">
                                                <i class="fas fa-chart-line" style="font-size: 10px;"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted small">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                            <span class="fw-semibold">No campaigns launched yet. Start your first broadcast!</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Latest Consented Subscribers Stream --}}
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm h-100 overflow-hidden mb-0">
                <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 13px;">
                        <i class="fas fa-user-check text-success me-1"></i> Latest Consented Users
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ $subscribersIndexRoute }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs" style="font-size: 10.5px !important;">
                            Manage
                        </a>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseRecentSubscribers" aria-expanded="true" aria-controls="collapseRecentSubscribers" title="Toggle Subscribers">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="collapse show" id="collapseRecentSubscribers">
                    <div class="card-body p-0">
                        <div style="max-height: 380px; overflow-y: auto; -webkit-overflow-scrolling: touch;">
                            <ul class="list-group list-group-flush" style="font-size: 11.5px;">
                                @forelse($recentSubscribers as $s)
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 subscriber-stream-item">
                                        <div class="min-w-0" style="line-height: 1.3;">
                                            <strong class="text-dark d-block text-truncate" style="font-size: 12px;" title="{{ $s->email }}">
                                                {{ $s->email }}
                                            </strong>
                                            <small class="text-muted font-monospace d-block text-truncate" style="font-size: 10px;">
                                                Via: {{ ucfirst($s->consent_source ?? 'web') }} &bull; {{ $s->created_at ? $s->created_at->diffForHumans() : 'Recently' }}
                                            </small>
                                        </div>
                                        <span class="badge {{ ($s->status ?? '') === 'subscribed' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} font-monospace ms-2 px-2 py-0.5" style="border-radius: 5px !important; font-size: 9.5px;">
                                            {{ ucfirst($s->status ?? 'Active') }}
                                        </span>
                                    </li>
                                @empty
                                    <li class="list-group-item text-center text-muted py-5 small">
                                        <i class="fas fa-users-slash fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                        <span>No subscribers recorded yet.</span>
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection