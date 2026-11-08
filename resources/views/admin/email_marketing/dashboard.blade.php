
@extends('layouts.admin')

@section('title', 'Email Marketing Dashboard')

@php
    // Safe dynamic fallbacks
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

    $campaignAggregates = $campaignAggregates ?? (object) [];

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
    /* =========================================================
       EMAIL MARKETING DASHBOARD
       Scoped UI | Bootstrap 5 | Strict 5px Radius
    ========================================================= */

    #email-marketing-dashboard-root {
        --emd-primary: #2563eb;
        --emd-primary-dark: #1d4ed8;
        --emd-text: #172033;
        --emd-muted: #64748b;
        --emd-border: #e5eaf1;
        --emd-surface: #ffffff;
        --emd-soft: #f8fafc;
        --emd-radius: 5px;

        color: var(--emd-text);
        font-size: 12px;
    }

    #email-marketing-dashboard-root,
    #email-marketing-dashboard-root * {
        box-sizing: border-box;
    }

    #email-marketing-dashboard-root .btn,
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
    #email-marketing-dashboard-root .collapse-toggle-btn,
    #email-marketing-dashboard-root .metric-icon-square {
        border-radius: var(--emd-radius) !important;
    }

    #email-marketing-dashboard-root .font-monospace,
    #email-marketing-dashboard-root .bn-digit {
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    #email-marketing-dashboard-root .min-w-0 {
        min-width: 0 !important;
    }

    /* Header */

    #email-marketing-dashboard-root .dashboard-header-wrap {
        padding: 2px 0 14px;
    }

    #email-marketing-dashboard-root .dashboard-title {
        color: #111827;
        font-size: 19px;
        font-weight: 800;
        letter-spacing: -0.45px;
        line-height: 1.3;
    }

    #email-marketing-dashboard-root .dashboard-subtitle {
        color: var(--emd-muted);
        font-size: 11.5px;
        line-height: 1.5;
        margin: 3px 0 0;
    }

    /* Buttons */

    #email-marketing-dashboard-root .btn-compact {
        min-height: 32px;
        padding: 6px 12px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        font-size: 11px !important;
        font-weight: 700 !important;
        line-height: 1.3;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #email-marketing-dashboard-root .btn-primary {
        background: var(--emd-primary);
        border-color: var(--emd-primary);
    }

    #email-marketing-dashboard-root .btn-primary:hover {
        background: var(--emd-primary-dark);
        border-color: var(--emd-primary-dark);
        transform: translateY(-1px);
    }

    #email-marketing-dashboard-root .btn-outline-primary:hover,
    #email-marketing-dashboard-root .btn-outline-secondary:hover {
        transform: translateY(-1px);
    }

    #email-marketing-dashboard-root .top-action-bar {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    /* Cards */

    #email-marketing-dashboard-root .card {
        border: 1px solid var(--emd-border) !important;
        background: var(--emd-surface);
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
        margin-bottom: 15px;
    }

    #email-marketing-dashboard-root .section-header-wrap {
        min-height: 43px;
        padding: 9px 13px;
        background: #fff;
        border-bottom: 1px solid var(--emd-border);
    }

    #email-marketing-dashboard-root .section-heading {
        color: #273449;
        font-size: 11.5px;
        font-weight: 800;
        letter-spacing: .01em;
        line-height: 1.4;
    }

    #email-marketing-dashboard-root .section-heading i {
        width: 16px;
        text-align: center;
    }

    /* Collapse Button */

    #email-marketing-dashboard-root .collapse-toggle-btn {
        width: 25px;
        height: 25px;
        padding: 0;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #dbe2ea;
        color: #64748b;
        transition: all .2s ease;
    }

    #email-marketing-dashboard-root .collapse-toggle-btn:hover {
        color: #fff;
        background: var(--emd-primary);
        border-color: var(--emd-primary);
    }

    #email-marketing-dashboard-root .collapse-icon {
        font-size: 9px;
        transition: transform .25s ease;
    }

    #email-marketing-dashboard-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* Metric Cards */

    #email-marketing-dashboard-root .metric-card {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        min-height: 108px;
        padding: 13px 14px;
        overflow: hidden;
        border: 1px solid var(--emd-border) !important;
        transition: transform .18s ease, box-shadow .18s ease;
    }

    #email-marketing-dashboard-root .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(15, 23, 42, .075);
    }

    #email-marketing-dashboard-root .stat-label-title {
        font-size: 10.5px;
        font-weight: 750;
        color: #64748b;
        line-height: 1.35;
        letter-spacing: .015em;
    }

    #email-marketing-dashboard-root .stat-val-num {
        margin: 0;
        font-size: 21px;
        line-height: 1.35;
        font-weight: 800;
        letter-spacing: -.6px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #email-marketing-dashboard-root .stat-sub-text {
        margin-top: 3px;
        font-size: 9.5px;
        line-height: 1.4;
    }

    #email-marketing-dashboard-root .metric-icon-square {
        width: 33px;
        height: 33px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 13px;
    }

    #email-marketing-dashboard-root .metric-card-green {
        background: linear-gradient(145deg, #fff 0%, #f0fdf4 100%);
        border-left: 3px solid #10b981 !important;
    }

    #email-marketing-dashboard-root .metric-card-blue {
        background: linear-gradient(145deg, #fff 0%, #eff6ff 100%);
        border-left: 3px solid #3b82f6 !important;
    }

    #email-marketing-dashboard-root .metric-card-amber {
        background: linear-gradient(145deg, #fff 0%, #fffbeb 100%);
        border-left: 3px solid #f59e0b !important;
    }

    #email-marketing-dashboard-root .metric-card-red {
        background: linear-gradient(145deg, #fff 0%, #fef2f2 100%);
        border-left: 3px solid #ef4444 !important;
    }

    #email-marketing-dashboard-root .metric-card-cyan {
        background: linear-gradient(145deg, #fff 0%, #ecfeff 100%);
        border-left: 3px solid #06b6d4 !important;
    }

    #email-marketing-dashboard-root .metric-card-dark {
        color: #fff;
        background: linear-gradient(135deg, #10243e 0%, #173d67 100%);
        border: 1px solid #1e3a5f !important;
    }

    #email-marketing-dashboard-root .metric-card-rev {
        color: #fff;
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        border: 1px solid #047857 !important;
    }

    #email-marketing-dashboard-root .metric-card-dark .stat-val-num,
    #email-marketing-dashboard-root .metric-card-rev .stat-val-num {
        color: #fff !important;
    }

    /* Progress */

    #email-marketing-dashboard-root .progress {
        height: 5px;
        background: #e8edf4;
        overflow: hidden;
    }

    #email-marketing-dashboard-root .progress-bar {
        transition: width .5s ease;
    }

    /* Form Controls */

    #email-marketing-dashboard-root .form-select {
        min-height: 33px;
        padding: 5px 29px 5px 10px;
        font-size: 11px;
        font-weight: 600;
        color: #334155;
        background-color: #fff;
        border: 1px solid #dbe2ea;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #email-marketing-dashboard-root .form-select:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .11);
    }

    /* Campaign Table */

    #email-marketing-dashboard-root .custom-dashboard-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #email-marketing-dashboard-root .custom-dashboard-table thead th {
        padding: 11px 8px;
        color: #64748b;
        background: #f8fafc;
        border-bottom: 1px solid #e5eaf1;
        font-size: 9.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .045em;
        white-space: nowrap;
    }

    #email-marketing-dashboard-root .custom-dashboard-table tbody td {
        padding: 10px 8px;
        color: #334155;
        border-bottom: 1px solid #f0f3f7;
        font-size: 11px;
        vertical-align: middle;
        white-space: nowrap;
    }

    #email-marketing-dashboard-root .custom-dashboard-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #email-marketing-dashboard-root .custom-dashboard-table tbody tr:hover td {
        background: #f8fbff;
    }

    #email-marketing-dashboard-root .campaign-name {
        color: #1e293b;
        font-size: 11.5px;
        font-weight: 750;
    }

    #email-marketing-dashboard-root .campaign-subject {
        color: #94a3b8;
        font-size: 9.5px;
    }

    #email-marketing-dashboard-root .campaign-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 62px;
        padding: 4px 7px;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .02em;
    }

    @media (min-width: 992px) {
        #email-marketing-dashboard-root .custom-dashboard-table {
            table-layout: fixed;
        }

        #email-marketing-dashboard-root .pc-fit-container {
            overflow-x: hidden;
        }

        #email-marketing-dashboard-root .custom-dashboard-table th,
        #email-marketing-dashboard-root .custom-dashboard-table td {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #email-marketing-dashboard-root .col-dash-name   { width: 30%; }
        #email-marketing-dashboard-root .col-dash-status { width: 12%; }
        #email-marketing-dashboard-root .col-dash-recip  { width: 14%; }
        #email-marketing-dashboard-root .col-dash-open   { width: 11%; }
        #email-marketing-dashboard-root .col-dash-click  { width: 11%; }
        #email-marketing-dashboard-root .col-dash-rev    { width: 14%; }
        #email-marketing-dashboard-root .col-dash-action { width: 8%; }
    }

    /* Subscriber Stream */

    #email-marketing-dashboard-root .subscriber-stream-item {
        min-height: 58px;
        border-color: #f0f3f7;
        transition: background .15s ease;
    }

    #email-marketing-dashboard-root .subscriber-stream-item:hover {
        background: #f8fbff;
    }

    #email-marketing-dashboard-root .subscriber-email {
        color: #1e293b;
        font-size: 11.5px;
        font-weight: 750;
    }

    #email-marketing-dashboard-root .subscriber-meta {
        color: #94a3b8;
        font-size: 9.5px;
    }

    #email-marketing-dashboard-root .empty-state {
        padding: 42px 15px !important;
        color: #94a3b8;
        text-align: center;
    }

    #email-marketing-dashboard-root .empty-state i {
        display: block;
        margin-bottom: 10px;
        font-size: 25px;
        opacity: .35;
    }

    /* Mobile */

    @media (max-width: 767.98px) {
        #email-marketing-dashboard-root {
            padding-top: 12px !important;
        }

        #email-marketing-dashboard-root .dashboard-header-wrap {
            gap: 11px !important;
            padding-bottom: 13px;
        }

        #email-marketing-dashboard-root .dashboard-title {
            font-size: 16px;
        }

        #email-marketing-dashboard-root .dashboard-subtitle {
            font-size: 10.5px;
        }

        #email-marketing-dashboard-root .top-action-bar {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 7px;
        }

        #email-marketing-dashboard-root .top-action-bar form {
            grid-column: 1 / -1;
            width: 100%;
        }

        #email-marketing-dashboard-root .top-action-bar form select,
        #email-marketing-dashboard-root .top-action-bar > a {
            width: 100%;
            min-height: 35px;
        }

        #email-marketing-dashboard-root .top-action-bar .btn {
            padding: 8px 5px !important;
            font-size: 10.5px !important;
        }

        #email-marketing-dashboard-root .section-header-wrap {
            min-height: 41px;
            padding: 8px 10px;
        }

        #email-marketing-dashboard-root .section-heading {
            font-size: 10px;
        }

        #email-marketing-dashboard-root .metric-card {
            min-height: 94px;
            padding: 10px !important;
        }

        #email-marketing-dashboard-root .stat-label-title {
            font-size: 9.5px;
        }

        #email-marketing-dashboard-root .stat-val-num {
            font-size: 17px;
        }

        #email-marketing-dashboard-root .stat-sub-text {
            font-size: 9px;
        }

        #email-marketing-dashboard-root .metric-icon-square {
            width: 27px;
            height: 27px;
            font-size: 11px;
        }

        #email-marketing-dashboard-root .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        #email-marketing-dashboard-root .custom-dashboard-table {
            min-width: 680px;
            table-layout: auto;
        }

        #email-marketing-dashboard-root .custom-dashboard-table thead th {
            padding: 10px 7px;
        }

        #email-marketing-dashboard-root .custom-dashboard-table tbody td {
            padding: 9px 7px;
        }

        #email-marketing-dashboard-root .card {
            margin-bottom: 12px;
        }

        #email-marketing-dashboard-root .row.g-3 {
            --bs-gutter-y: .75rem;
        }
    }

    @media (max-width: 380px) {
        #email-marketing-dashboard-root .metric-card {
            padding: 8px !important;
        }

        #email-marketing-dashboard-root .stat-val-num {
            font-size: 15px;
        }

        #email-marketing-dashboard-root .stat-label-title {
            font-size: 9px;
        }
    }

    /* Accessibility */

    #email-marketing-dashboard-root a:focus-visible,
    #email-marketing-dashboard-root button:focus-visible {
        outline: 2px solid #60a5fa;
        outline-offset: 2px;
    }

    @media (prefers-reduced-motion: reduce) {
        #email-marketing-dashboard-root *,
        #email-marketing-dashboard-root *::before,
        #email-marketing-dashboard-root *::after {
            transition-duration: .01ms !important;
            animation-duration: .01ms !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-dashboard-root">

    {{-- =====================================================
         1. DASHBOARD HEADER & QUICK ACTIONS
    ====================================================== --}}

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 dashboard-header-wrap">

        <div class="min-w-0">
            <h5 class="dashboard-title mb-1 d-flex align-items-center gap-2">
                <i class="fas fa-chart-line text-primary"></i>
                <span>Email Marketing Dashboard</span>
            </h5>

            <p class="dashboard-subtitle">
                Monitor campaigns, subscriber growth, engagement and attributed revenue.
            </p>
        </div>

        <div class="top-action-bar">

            {{-- Date Range Filter --}}
            <form action="{{ $dashboardRoute }}" method="GET" class="m-0">
                <select name="range"
                        class="form-select font-monospace"
                        onchange="this.form.submit()"
                        aria-label="Select dashboard date range">

                    <option value="today" {{ $dateRange === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ $dateRange === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="7_days" {{ $dateRange === '7_days' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="30_days" {{ $dateRange === '30_days' ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="this_month" {{ $dateRange === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $dateRange === 'last_month' ? 'selected' : '' }}>Previous Month</option>

                </select>
            </form>

            <a href="{{ $createCampaignRoute }}" class="btn btn-sm btn-primary btn-compact shadow-sm">
                <i class="fas fa-paper-plane"></i>
                New Campaign
            </a>

            <a href="{{ $subscribersIndexRoute }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-sm">
                <i class="fas fa-user-plus"></i>
                Subscribers
            </a>

        </div>
    </div>


    {{-- =====================================================
         2. AUDIENCE & CONSENT STATISTICS
    ====================================================== --}}

    <div class="card mb-3 overflow-hidden">

        <div class="section-header-wrap d-flex justify-content-between align-items-center">

            <span class="section-heading">
                <i class="fas fa-users text-success me-1"></i>
                Audience &amp; Consent Verification
            </span>

            <button type="button"
                    class="collapse-toggle-btn shadow-sm no-print"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseAudienceStats"
                    aria-expanded="true"
                    aria-controls="collapseAudienceStats"
                    title="Toggle Audience Statistics">

                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>

        </div>

        <div class="collapse show" id="collapseAudienceStats">

            <div class="p-3">
                <div class="row g-2">

                    {{-- Eligible Audience --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green mb-0">

                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="stat-label-title text-truncate">Eligible Audience</span>

                                <div class="metric-icon-square bg-success-subtle text-success">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="stat-val-num text-dark font-monospace bn-digit">
                                    {{ number_format($eligibleSubscribers) }}
                                </h4>

                                <div class="mt-1">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"
                                          style="font-size:9px;">
                                        Consent Verified
                                    </span>
                                </div>

                                <small class="text-muted d-block stat-sub-text font-monospace text-truncate">
                                    Total in store:
                                    <strong>{{ number_format($totalSubscribers) }}</strong>
                                </small>
                            </div>

                        </div>
                    </div>

                    {{-- New Subscribers --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue mb-0">

                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="stat-label-title text-truncate">New Subscribers</span>

                                <div class="metric-icon-square bg-primary-subtle text-primary">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="stat-val-num text-dark font-monospace bn-digit">
                                    {{ number_format($newSubscribersRange) }}
                                </h4>

                                <small class="text-primary d-block stat-sub-text font-monospace text-truncate">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    {{ ucwords(str_replace('_', ' ', $dateRange)) }}
                                </small>

                                <small class="text-muted d-block stat-sub-text">
                                    Subscriber registrations
                                </small>
                            </div>

                        </div>
                    </div>

                    {{-- Unsubscribed --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber mb-0">

                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="stat-label-title text-truncate">Unsubscribed</span>

                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis">
                                    <i class="fas fa-user-slash"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="stat-val-num text-dark font-monospace bn-digit">
                                    {{ number_format($unsubscribedCount) }}
                                </h4>

                                <small class="text-warning-emphasis d-block stat-sub-text text-truncate">
                                    <i class="fas fa-user-minus me-1"></i>
                                    Opted-out profiles
                                </small>

                                <small class="text-muted d-block stat-sub-text">
                                    Suppressed from queue
                                </small>
                            </div>

                        </div>
                    </div>

                    {{-- Bounced / Blocked --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-red mb-0">

                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="stat-label-title text-truncate">Bounced / Blocked</span>

                                <div class="metric-icon-square bg-danger-subtle text-danger">
                                    <i class="fas fa-ban"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="stat-val-num text-dark font-monospace bn-digit">
                                    {{ number_format($bouncedCount) }}
                                </h4>

                                <small class="text-danger d-block stat-sub-text text-truncate">
                                    <i class="fas fa-shield-halved me-1"></i>
                                    Suppression List
                                </small>

                                <small class="text-muted d-block stat-sub-text">
                                    Sender reputation protection
                                </small>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>


    {{-- =====================================================
         3. CAMPAIGN PERFORMANCE & REVENUE
    ====================================================== --}}

    <div class="card mb-3 overflow-hidden">

        <div class="section-header-wrap d-flex justify-content-between align-items-center">

            <span class="section-heading">
                <i class="fas fa-chart-pie text-primary me-1"></i>
                Campaign Performance &amp; Revenue Attribution
            </span>

            <button type="button"
                    class="collapse-toggle-btn shadow-sm no-print"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapsePerformanceMetrics"
                    aria-expanded="true"
                    aria-controls="collapsePerformanceMetrics"
                    title="Toggle Performance Metrics">

                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>

        </div>

        <div class="collapse show" id="collapsePerformanceMetrics">

            <div class="p-3">
                <div class="row g-2">

                    {{-- Delivered Emails --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-dark mb-0">

                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-white text-opacity-75 stat-label-title">
                                    Delivered Broadcasts
                                </span>

                                <div class="metric-icon-square bg-white bg-opacity-15 text-white">
                                    <i class="fas fa-paper-plane text-muted"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="stat-val-num text-white font-monospace bn-digit">
                                    {{ number_format($campaignAggregates->total_delivered ?? 0) }}
                                </h4>

                                <div class="d-flex justify-content-between text-white text-opacity-75 pt-2 mt-2 border-top border-white border-opacity-10 font-monospace"
                                     style="font-size:9px;">
                                    <span>
                                        Sent:
                                        {{ number_format($campaignAggregates->total_sent ?? 0) }}
                                    </span>

                                    <span>
                                        Failed:
                                        {{ number_format($campaignAggregates->total_bounces ?? 0) }}
                                    </span>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Open Rate --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green mb-0">

                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="stat-label-title">Open Rate</span>

                                <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1"
                                      style="font-size:9px;">
                                    {{ $openRate }}%
                                </span>
                            </div>

                            <div class="min-w-0">
                                <h4 class="stat-val-num text-dark font-monospace bn-digit">
                                    {{ number_format($campaignAggregates->total_opened ?? 0) }}
                                </h4>

                                <div class="progress mt-2 mb-2">
                                    <div class="progress-bar bg-success"
                                         style="width: {{ min(max((float) $openRate, 0), 100) }}%;">
                                    </div>
                                </div>

                                <small class="text-muted stat-sub-text">
                                    Delivered pixel opens
                                </small>
                            </div>

                        </div>
                    </div>

                    {{-- Click Rate --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue mb-0">

                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="stat-label-title">Click-Through (CTR)</span>

                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1"
                                      style="font-size:9px;">
                                    {{ $clickRate }}%
                                </span>
                            </div>

                            <div class="min-w-0">
                                <h4 class="stat-val-num text-dark font-monospace bn-digit">
                                    {{ number_format($campaignAggregates->total_clicked ?? 0) }}
                                </h4>

                                <div class="progress mt-2 mb-2">
                                    <div class="progress-bar bg-primary"
                                         style="width: {{ min(max((float) $clickRate, 0), 100) }}%;">
                                    </div>
                                </div>

                                <small class="text-muted stat-sub-text">
                                    Verified store link clicks
                                </small>
                            </div>

                        </div>
                    </div>

                    {{-- Revenue --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-rev mb-0">

                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-white text-opacity-75 stat-label-title">
                                    Attributed Revenue
                                </span>

                                <div class="metric-icon-square bg-white bg-opacity-20 text-white">
                                    <i class="fas fa-coins text-success"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="stat-val-num text-white font-monospace bn-digit">
                                    ৳ {{ number_format($campaignAggregates->total_revenue ?? 0, 2) }}
                                </h4>

                                <div class="d-flex justify-content-between text-white text-opacity-75 pt-2 mt-2 border-top border-white border-opacity-10 font-monospace"
                                     style="font-size:9px;">
                                    <span>Direct conversions</span>
                                    <span>Revenue</span>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>


    {{-- =====================================================
         4. CART RECOVERY & AUTOMATION
    ====================================================== --}}

    <div class="card mb-3 overflow-hidden">

        <div class="section-header-wrap d-flex justify-content-between align-items-center">

            <span class="section-heading">
                <i class="fas fa-cart-shopping text-warning me-1"></i>
                Cart Recovery Engine &amp; Automated Workflows
            </span>

            <button type="button"
                    class="collapse-toggle-btn shadow-sm no-print"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseLifecycleAutomation"
                    aria-expanded="true"
                    aria-controls="collapseLifecycleAutomation"
                    title="Toggle Automation Metrics">

                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>

        </div>

        <div class="collapse show" id="collapseLifecycleAutomation">

            <div class="p-3">
                <div class="row g-2">

                    {{-- Abandoned Carts --}}
                    <div class="col-12 col-md-4">
                        <div class="card metric-card metric-card-amber mb-0">

                            <div class="d-flex justify-content-between align-items-start gap-2">

                                <div class="min-w-0">
                                    <span class="stat-label-title d-block">
                                        Abandoned Carts
                                    </span>

                                    <h4 class="stat-val-num text-dark font-monospace bn-digit mt-1">
                                        {{ number_format($abandonedCartsCount) }}
                                    </h4>

                                    <small class="text-muted stat-sub-text">
                                        Unrecovered shopper sessions
                                    </small>
                                </div>

                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis">
                                    <i class="fas fa-cart-arrow-down"></i>
                                </div>

                            </div>

                            <div class="pt-2 mt-3 border-top">
                                <a href="{{ $cartWishlistRoute }}"
                                   class="text-decoration-none small fw-bold text-primary d-inline-flex align-items-center gap-1">

                                    <span>View Cart Recovery Engine</span>
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>

                        </div>
                    </div>

                    {{-- Recovered Orders --}}
                    <div class="col-12 col-md-4">
                        <div class="card metric-card metric-card-green mb-0">

                            <div class="d-flex justify-content-between align-items-start gap-2">

                                <div class="min-w-0">
                                    <span class="stat-label-title d-block">
                                        Recovered Orders
                                    </span>

                                    <h4 class="stat-val-num text-success font-monospace bn-digit mt-1">
                                        {{ number_format($recoveredCartsCount) }}
                                    </h4>

                                    <small class="text-success stat-sub-text">
                                        Recovered:
                                        <strong>৳ {{ number_format($recoveredRevenue, 2) }}</strong>
                                    </small>
                                </div>

                                <div class="metric-icon-square bg-success-subtle text-success">
                                    <i class="fas fa-bag-shopping"></i>
                                </div>

                            </div>

                            <div class="pt-2 mt-3 border-top">
                                <span class="text-muted small">
                                    <i class="fas fa-bolt text-success me-1"></i>
                                    Closed via automated triggers
                                </span>
                            </div>

                        </div>
                    </div>

                    {{-- Active Automations --}}
                    <div class="col-12 col-md-4">
                        <div class="card metric-card metric-card-cyan mb-0">

                            <div class="d-flex justify-content-between align-items-start gap-2">

                                <div class="min-w-0">
                                    <span class="stat-label-title d-block">
                                        Active Automations
                                    </span>

                                    <h4 class="stat-val-num text-dark font-monospace bn-digit mt-1">
                                        {{ number_format($activeAutomations) }}
                                    </h4>

                                    <small class="text-muted stat-sub-text">
                                        Welcome, Win-back, Order flow
                                    </small>
                                </div>

                                <div class="metric-icon-square bg-info-subtle text-info">
                                    <i class="fas fa-robot"></i>
                                </div>

                            </div>

                            <div class="pt-2 mt-3 border-top">
                                <a href="{{ $automationsIndexRoute }}"
                                   class="text-decoration-none small fw-bold text-primary d-inline-flex align-items-center gap-1">

                                    <span>Configure Workflows</span>
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>


    {{-- =====================================================
         5. RECENT CAMPAIGNS & LATEST SUBSCRIBERS
    ====================================================== --}}

    <div class="row g-3">

        {{-- Recent Campaigns --}}
        <div class="col-12 col-xl-8">

            <div class="card h-100 overflow-hidden mb-0">

                <div class="section-header-wrap d-flex justify-content-between align-items-center gap-2">

                    <h6 class="section-heading mb-0">
                        <i class="fas fa-paper-plane text-primary me-1"></i>
                        Recent Marketing Campaigns
                    </h6>

                    <div class="d-flex align-items-center gap-2 flex-shrink-0">

                        <a href="{{ $campaignsIndexRoute }}"
                           class="btn btn-sm btn-outline-primary btn-compact"
                           style="min-height:27px;padding:4px 9px !important;font-size:10px !important;">
                            View All
                        </a>

                        <button type="button"
                                class="collapse-toggle-btn shadow-sm no-print"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseRecentCampaigns"
                                aria-expanded="true"
                                aria-controls="collapseRecentCampaigns"
                                title="Toggle Campaigns">

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
                                            'completed' => 'bg-success-subtle text-success border border-success-subtle',
                                            'sending', 'queued' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                            'scheduled' => 'bg-info-subtle text-info border border-info-subtle',
                                            'paused' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                            'cancelled' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                            default => 'bg-light text-dark border',
                                        };

                                        $campaignShowUrl = Route::has('admin.email_marketing.campaigns.show')
                                            ? route('admin.email_marketing.campaigns.show', $c->id)
                                            : url('admin/email-marketing/campaigns/' . $c->id);
                                    @endphp

                                    <tr>

                                        {{-- Campaign Name --}}
                                        <td class="ps-3 col-dash-name">

                                            <div class="min-w-0">

                                                <a href="{{ $campaignShowUrl }}"
                                                   class="campaign-name text-decoration-none d-block text-truncate"
                                                   title="{{ $c->name }}">

                                                    {{ $c->name }}
                                                </a>

                                                <small class="campaign-subject d-block text-truncate"
                                                       title="{{ $c->subject }}">

                                                    <i class="fas fa-envelope me-1"></i>
                                                    {{ $c->subject }}
                                                </small>

                                            </div>
                                        </td>

                                        {{-- Status --}}
                                        <td class="col-dash-status">

                                            <span class="badge campaign-status {{ $badgeClass }}">
                                                {{ strtoupper($c->status) }}
                                            </span>

                                        </td>

                                        {{-- Recipients --}}
                                        <td class="col-dash-recip font-monospace bn-digit fw-bold text-dark">
                                            {{ number_format($c->total_recipients) }}
                                        </td>

                                        {{-- Open Rate --}}
                                        <td class="col-dash-open font-monospace bn-digit fw-semibold text-success">
                                            {{ $c->open_rate }}%
                                        </td>

                                        {{-- Click Rate --}}
                                        <td class="col-dash-click font-monospace bn-digit fw-semibold text-primary">
                                            {{ $c->click_rate }}%
                                        </td>

                                        {{-- Revenue --}}
                                        <td class="col-dash-rev font-monospace bn-digit fw-bold text-dark">
                                            ৳ {{ number_format($c->total_revenue, 2) }}
                                        </td>

                                        {{-- Action --}}
                                        <td class="text-end pe-3 col-dash-action">

                                            <a href="{{ $campaignShowUrl }}"
                                               class="btn btn-sm btn-light border text-primary d-inline-flex align-items-center justify-content-center"
                                               style="width:27px;height:27px;padding:0;"
                                               title="View Campaign Analytics"
                                               aria-label="View {{ $c->name }} analytics">

                                                <i class="fas fa-chart-line" style="font-size:10px;"></i>
                                            </a>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="7" class="empty-state">

                                            <i class="fas fa-inbox"></i>

                                            <span class="fw-semibold d-block text-dark">
                                                No campaigns launched yet.
                                            </span>

                                            <small class="d-block mt-1">
                                                Start your first email marketing campaign.
                                            </small>

                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>
                </div>

            </div>
        </div>


        {{-- Latest Consented Subscribers --}}
        <div class="col-12 col-xl-4">

            <div class="card h-100 overflow-hidden mb-0">

                <div class="section-header-wrap d-flex justify-content-between align-items-center gap-2">

                    <h6 class="section-heading mb-0">
                        <i class="fas fa-user-check text-success me-1"></i>
                        Latest Consented Users
                    </h6>

                    <div class="d-flex align-items-center gap-2 flex-shrink-0">

                        <a href="{{ $subscribersIndexRoute }}"
                           class="btn btn-sm btn-outline-secondary btn-compact"
                           style="min-height:27px;padding:4px 9px !important;font-size:10px !important;">
                            Manage
                        </a>

                        <button type="button"
                                class="collapse-toggle-btn shadow-sm no-print"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseRecentSubscribers"
                                aria-expanded="true"
                                aria-controls="collapseRecentSubscribers"
                                title="Toggle Subscribers">

                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>

                    </div>
                </div>

                <div class="collapse show" id="collapseRecentSubscribers">

                    <div class="card-body p-0">

                        <div style="max-height:380px;overflow-y:auto;-webkit-overflow-scrolling:touch;">

                            <ul class="list-group list-group-flush mb-0">

                                @forelse($recentSubscribers as $s)

                                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 subscriber-stream-item">

                                        <div class="min-w-0">

                                            <strong class="subscriber-email d-block text-truncate"
                                                    title="{{ $s->email }}">

                                                {{ $s->email }}
                                            </strong>

                                            <small class="subscriber-meta d-block text-truncate">

                                                Via: {{ ucfirst($s->consent_source ?? 'web') }}

                                                &bull;

                                                {{ $s->created_at ? $s->created_at->diffForHumans() : 'Recently' }}

                                            </small>

                                        </div>

                                        <span class="badge ms-2 px-2 py-1 flex-shrink-0 font-monospace
                                            {{ ($s->status ?? '') === 'subscribed'
                                                ? 'bg-success-subtle text-success border border-success-subtle'
                                                : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }}"
                                              style="font-size:9px;">

                                            {{ ucfirst($s->status ?? 'Active') }}

                                        </span>

                                    </li>

                                @empty

                                    <li class="list-group-item empty-state">

                                        <i class="fas fa-users-slash"></i>

                                        <span class="fw-semibold d-block text-dark">
                                            No subscribers recorded yet.
                                        </span>

                                        <small class="d-block mt-1">
                                            New subscriber registrations will appear here.
                                        </small>

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
