@extends('layouts.admin')

@section('title', 'Scheduled Emails - Email Marketing')

@section('admin_content')
@php
    $campaigns = $campaigns ?? ($scheduledCampaigns ?? collect());
    $totalCampaignsCount = method_exists($campaigns, 'total') ? $campaigns->total() : $campaigns->count();
    $totalRecipientsCount = $campaigns->sum('total_recipients');

    $allCampaignsRoute = Route::has('admin.email_marketing.campaigns.index') 
        ? route('admin.email_marketing.campaigns.index') 
        : url('admin/email-marketing/campaigns');

    $createCampaignRoute = Route::has('admin.email_marketing.campaigns.create') 
        ? route('admin.email_marketing.campaigns.create') 
        : url('admin/email-marketing/campaigns/create');
@endphp

<style>
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-scheduled-root .btn,
    #email-marketing-scheduled-root .btn-sm,
    #email-marketing-scheduled-root button,
    #email-marketing-scheduled-root .badge,
    #email-marketing-scheduled-root .alert,
    #email-marketing-scheduled-root .form-control,
    #email-marketing-scheduled-root .form-select,
    #email-marketing-scheduled-root .input-group-text,
    #email-marketing-scheduled-root .card,
    #email-marketing-scheduled-root .metric-card,
    #email-marketing-scheduled-root .page-link,
    #email-marketing-scheduled-root .page-item span,
    #email-marketing-scheduled-root .collapse-toggle-btn,
    .scheduled-modal-scope .modal-content,
    .scheduled-modal-scope .btn {
        border-radius: 5px !important;
    }

    #email-marketing-scheduled-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #email-marketing-scheduled-root .input-group > .form-control,
    #email-marketing-scheduled-root .input-group > .form-select {
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

    .scheduled-filter-control {
        font-size: 12px !important;
        height: 32px !important;
        padding: 4px 8px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        box-shadow: none !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .scheduled-filter-control:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        outline: none;
    }

    .scheduled-addon-text {
        height: 32px !important;
        background-color: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: #64748b !important;
        font-size: 11px !important;
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
    #email-marketing-scheduled-root .card {
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

    .metric-card-cyan   { background: linear-gradient(180deg, #ffffff 0%, #ecfeff 100%); border-left: 3.5px solid #06b6d4 !important; }
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-left: 3.5px solid #10b981 !important; }
    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-left: 3.5px solid #f59e0b !important; }

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .custom-scheduled-table {
        width: 100%;
        margin-bottom: 0;
    }
    .custom-scheduled-table thead th {
        font-size: 10px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 8px;
        white-space: nowrap;
    }
    .custom-scheduled-table tbody td {
        padding: 8px 8px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        font-size: 11px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .custom-scheduled-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .custom-scheduled-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .custom-scheduled-table th,
        .custom-scheduled-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 7 Columns Proportional Distribution Summing to Exactly 100% */
        .col-sc-id     { width: 5% !important; }
        .col-sc-camp   { width: 28% !important; }
        .col-sc-aud    { width: 15% !important; }
        .col-sc-time   { width: 16% !important; }
        .col-sc-due    { width: 14% !important; }
        .col-sc-recip  { width: 10% !important; }
        .col-sc-action { width: 12% !important; }
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
    .card-footer nav div:has(> p) {
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
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25) !important;
    }
    .card-footer .page-item.disabled .page-link,
    .card-footer .page-item.disabled span {
        opacity: 0.45 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .scheduled-header-wrap h5 {
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
        .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .custom-scheduled-table {
            min-width: 780px !important;
            table-layout: auto !important;
        }
        #email-marketing-scheduled-root .card {
            margin-bottom: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-scheduled-root">

    {{-- 1. Header & Navigation (Original Clean Light Theme) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 scheduled-header-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-calendar-check text-primary"></i>
                    <span>Scheduled Campaigns</span>
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace" id="scheduled-total-badge" style="font-size: 10px;">
                    {{ $totalCampaignsCount }} Queued Broadcasts
                </span>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                Monitor, reschedule, or immediately dispatch time-locked promotional broadcasts.
            </small>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 top-action-btns">
            <a href="{{ $allCampaignsRoute }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-paper-plane me-1"></i> <span>All Campaigns</span>
            </a>
            <a href="{{ $createCampaignRoute }}" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold">
                <i class="fas fa-plus-circle me-1"></i> <span>Schedule New Campaign</span>
            </a>
        </div>
    </div>

    {{-- 2. Feedback Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 3. Overview Stats Row (Collapsible Section) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-chart-pie text-primary me-1"></i> Scheduled Broadcast Pipeline Overview
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseScheduledKpis" aria-expanded="true" aria-controls="collapseScheduledKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseScheduledKpis">
            <div class="p-3">
                <div class="row g-2">
                    {{-- Upcoming Broadcasts --}}
                    <div class="col-6 col-xl-4">
                        <div class="card metric-card metric-card-cyan h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Upcoming Broadcasts</span>
                                <div class="metric-icon-square bg-info-subtle text-info shadow-xs">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    {{ $totalCampaignsCount }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Awaiting scheduled trigger</small>
                            </div>
                        </div>
                    </div>

                    {{-- Target Audience Reach --}}
                    <div class="col-6 col-xl-4">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Target Reach</span>
                                <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                                    <i class="fas fa-envelope-circle-check"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num">
                                    {{ number_format($totalRecipientsCount) }}
                                </h4>
                                <small class="text-success d-block text-truncate font-monospace stat-sub-text">
                                    <i class="fas fa-check-circle me-1"></i>Legally Consented Inboxes
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- Cron Job Status --}}
                    <div class="col-12 col-xl-4">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Automated Cron Worker</span>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                                    <i class="fas fa-server"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-warning-emphasis font-monospace stat-val-num" style="font-size: 15px;">
                                    <i class="fas fa-arrows-spin me-1"></i> Active Pipeline
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Evaluates queue every minute</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Instant Live Search & Status Filter Toolbar (Collapsible) --}}
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0 !important;">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-filter text-primary me-1"></i> Filter Scheduled Broadcasts &amp; Search Queue
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseScheduledFilter" aria-expanded="true" aria-controls="collapseScheduledFilter" title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseScheduledFilter">
            <div class="p-3">
                <form id="scheduledFilterForm" onsubmit="return false;">
                    <div class="row g-2 align-items-end">
                        {{-- Search Input --}}
                        <div class="col-12 col-md-5">
                            <label class="form-label" for="scheduledSearchInput">
                                <i class="fas fa-search me-1 text-primary"></i> Search Campaign Name, Subject or Segment
                            </label>
                            <div class="input-group">
                                <span class="input-group-text scheduled-addon-text border-end-0"><i class="fas fa-search text-muted" style="font-size: 11px;"></i></span>
                                <input type="text" id="scheduledSearchInput" class="form-control border-start-0 ps-0 scheduled-filter-control shadow-none font-monospace" placeholder="e.g. Flash Sale, Welcome Series, VIP..." onkeyup="filterScheduledTable()">
                            </div>
                        </div>

                        {{-- Dispatch Readiness Dropdown --}}
                        <div class="col-6 col-md-4">
                            <label class="form-label" for="scheduledReadinessFilter">
                                <i class="fas fa-hourglass-half me-1 text-warning"></i> Dispatch Readiness
                            </label>
                            <select id="scheduledReadinessFilter" class="form-select scheduled-filter-control shadow-none font-monospace" onchange="filterScheduledTable()">
                                <option value="all" selected>All Scheduled Items</option>
                                <option value="upcoming">Upcoming (In Queue)</option>
                                <option value="due">Due for Dispatch (Immediate)</option>
                            </select>
                        </div>

                        {{-- Action Reset Button --}}
                        <div class="col-6 col-md-3">
                            <button type="button" class="btn btn-light border btn-compact w-100 shadow-xs fw-semibold text-secondary" onclick="resetScheduledFilters()">
                                <i class="fas fa-rotate-left me-1"></i> <span>Reset Filters</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 5. Scheduled Campaigns Table Card (Desktop 100% Fit Screen with Zero Horizontal Scroll) --}}
    <div class="card border-0 shadow-sm overflow-hidden mb-3">
        <div class="p-2 px-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 bg-light bg-opacity-25">
            <div>
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13px;">
                    <i class="fas fa-calendar-day text-primary"></i> <span>Time-Locked Broadcast Queue</span>
                </h6>
                <small class="text-muted font-monospace bn-digit" id="showing-entries-text" style="font-size: 10px;">
                    Showing {{ method_exists($campaigns, 'firstItem') ? ($campaigns->firstItem() ?? 0) : 1 }} to {{ method_exists($campaigns, 'lastItem') ? ($campaigns->lastItem() ?? 0) : $campaigns->count() }} of {{$totalCampaignsCount }} campaigns
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 10px;" id="visible-scheduled-count">
                    {{ $totalCampaignsCount }} Total
                </span>
                <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseScheduledTable" aria-expanded="true" aria-controls="collapseScheduledTable" title="Toggle Table">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseScheduledTable">
            <div class="table-responsive pc-fit-container" style="min-height: 250px;">
                <table class="table align-middle mb-0 custom-scheduled-table" id="scheduled-table">
                    <thead>
                        <tr>
                            <th class="ps-3 col-sc-id text-center">#ID</th>
                            <th class="col-sc-camp">Campaign &amp; Subject</th>
                            <th class="col-sc-aud">Target Audience</th>
                            <th class="col-sc-time">Scheduled Dispatch Time</th>
                            <th class="col-sc-due">Countdown</th>
                            <th class="text-center col-sc-recip">Recipients</th>
                            <th class="text-end pe-3 col-sc-action">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="scheduled-tbody">
                        @forelse($campaigns as $camp)
                            @php
                                $campViewUrl = Route::has('admin.email_marketing.campaigns.show') 
                                    ? route('admin.email_marketing.campaigns.show', $camp->id) 
                                    : url('admin/email-marketing/campaigns/view/' . $camp->id);

                                $campEditUrl = Route::has('admin.email_marketing.campaigns.edit') 
                                    ? route('admin.email_marketing.campaigns.edit', $camp->id) 
                                    : url('admin/email-marketing/campaigns/' . $camp->id . '/edit');

                                $campDestroyUrl = Route::has('admin.email_marketing.campaigns.destroy') 
                                    ? route('admin.email_marketing.campaigns.destroy', $camp->id) 
                                    : url('admin/email-marketing/campaigns/' . $camp->id);

                                $campDispatchNowUrl = Route::has('admin.email_marketing.campaigns.dispatch_now') 
                                    ? route('admin.email_marketing.campaigns.dispatch_now', $camp->id) 
                                    : url('admin/email-marketing/campaigns/' . $camp->id . '/dispatch-now');

                                $segmentTitle =$camp->segment ? $camp->segment->name : 'All Subscribers';$isDue = $camp->scheduled_at &&$camp->scheduled_at->isPast();
                                $readinessSlug =$isDue ? 'due' : 'upcoming';

                                $rowSearchData = strtolower(
                                    ($camp->name ?? '') . ' ' .
                                    ($camp->subject ?? '') . ' ' .
                                    $segmentTitle . ' ' .$readinessSlug
                                );
                            @endphp
                            <tr class="scheduled-entry-row align-middle" data-search="{{ $rowSearchData }}" data-readiness="{{ $readinessSlug }}">
                                <td class="ps-3 col-sc-id text-center font-monospace bn-digit text-muted" style="font-size: 11px;">
                                    {{ $camp->id }}
                                </td>
                                <td class="col-sc-camp">
                                    <div class="min-w-0" style="line-height: 1.3;">
                                        <a href="{{ $campViewUrl }}" class="fw-bold text-dark text-decoration-none d-block text-truncate" style="font-size: 12px;" title="{{ $camp->name }}">
                                            {{ $camp->name }}
                                        </a>
                                        <small class="text-muted d-block text-truncate font-monospace" style="font-size: 10px;" title="{{ $camp->subject }}">
                                            <i class="fas fa-envelope me-1 text-secondary"></i> {{ $camp->subject }}
                                        </small>
                                    </div>
                                </td>
                                <td class="col-sc-aud">
                                    @if($camp->segment)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-truncate d-inline-block px-2 py-1" style="max-width: 100%; border-radius: 5px !important; font-size: 9.5px;" title="{{ $camp->segment->name }}">
                                            <i class="fas fa-filter me-1"></i> {{ $camp->segment->name }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 9.5px;">
                                            <i class="fas fa-users me-1"></i> All Subscribers
                                        </span>
                                    @endif
                                </td>
                                <td class="col-sc-time font-monospace bn-digit" style="line-height: 1.3;">
                                    @if($camp->scheduled_at)
                                        <span class="text-dark d-block" style="font-size: 11px;">{{ $camp->scheduled_at->format('M d, Y') }}</span>
                                        <small class="text-muted" style="font-size: 9.5px;">
                                            <i class="fas fa-clock me-1 text-secondary"></i>{{ $camp->scheduled_at->format('h:i A') }}
                                        </small>
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 10px;">Unset</span>
                                    @endif
                                </td>
                                <td class="col-sc-due font-monospace">
                                    @if($camp->scheduled_at)
                                        @if($isDue)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 9.5px;">
                                                <i class="fas fa-bolt me-1"></i> Due for dispatch
                                            </span>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 9.5px;">
                                                <i class="fas fa-hourglass-half me-1"></i> In {{ $camp->scheduled_at->diffForHumans(null, true) }}
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center col-sc-recip font-monospace bn-digit fw-bold text-dark" style="font-size: 11.5px;">
                                    {{ number_format($camp->total_recipients) }}
                                </td>
                                <td class="text-end pe-3 col-sc-action">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        {{-- 1. Send Immediately Trigger Modal --}}
                                        <button type="button" class="btn btn-sm btn-light border p-1 text-success shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; border-radius: 5px !important;" onclick="triggerDispatchNowModal('{{ addslashes($camp->name) }}', '{{$campDispatchNowUrl }}')" title="Dispatch Immediately Right Now">
                                            <i class="fas fa-paper-plane" style="font-size: 10px;"></i>
                                        </button>

                                        {{-- 2. Edit Schedule & Content --}}
                                        <a href="{{ $campEditUrl }}" class="btn btn-sm btn-light border p-1 text-primary shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; border-radius: 5px !important;" title="Reschedule or Edit Campaign">
                                            <i class="fas fa-calendar-pen" style="font-size: 10px;"></i>
                                        </a>

                                        {{-- 3. Delete / Cancel Schedule via Modal --}}
                                        <button type="button" class="btn btn-sm btn-light border p-1 text-danger shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; border-radius: 5px !important;" onclick="triggerCancelScheduleModal('{{ addslashes($camp->name) }}', '{{$campDestroyUrl }}')" title="Cancel and Delete Scheduled Broadcast">
                                            <i class="fas fa-trash-can" style="font-size: 10px;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyScheduledRow">
                                <td colspan="7" class="text-center py-5 text-muted small">
                                    <i class="fas fa-calendar-xmark fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                    <span class="fw-semibold text-dark d-block mb-1">No upcoming scheduled campaigns</span>
                                    <span>When scheduling a broadcast with a future date, it will appear here.</span>
                                </td>
                            </tr>
                        @endforelse
                        <tr id="noMatchScheduledRow" style="display: none;">
                            <td colspan="7" class="text-center py-5 text-muted small">
                                <i class="fas fa-filter-circle-xmark fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                <span class="fw-semibold">No scheduled campaigns matched your search or readiness filter.</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Bulletproof Responsive Pagination Footer --}}
            @if(method_exists($campaigns, 'hasPages') &&$campaigns->hasPages())
                <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">
                    <small class="text-muted font-monospace bn-digit" style="font-size: 11px;">
                        Showing {{ $campaigns->firstItem() }} to {{ $campaigns->lastItem() }} of {{$campaigns->total() }} campaigns
                    </small>
                    <div>
                        {{ $campaigns->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- MODAL 1: Instant Dispatch Confirmation Modal --}}
<div class="modal fade scheduled-modal-scope" id="instantDispatchModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 44px; height: 44px; border-radius: 50% !important;">
                    <i class="fas fa-paper-plane fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Dispatch Immediately?</h6>
                <p class="text-muted small mb-3" style="font-size: 11px; line-height: 1.45;">
                    Override time lock and broadcast <strong id="dispatchCampaignName" class="text-dark">this campaign</strong> to all target recipients right now?
                </p>
                <form id="instantDispatchForm" method="POST" action="">
                    @csrf
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success btn-compact shadow-xs fw-bold text-white">Yes, Send Now</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 2: Cancel / Delete Schedule Confirmation Modal --}}
<div class="modal fade scheduled-modal-scope" id="cancelScheduleModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 44px; height: 44px; border-radius: 50% !important;">
                    <i class="fas fa-trash-can fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Cancel Scheduled Broadcast?</h6>
                <p class="text-muted small mb-3" style="font-size: 11px; line-height: 1.45;">
                    Are you sure you want to cancel and remove <strong id="cancelCampaignName" class="text-dark">this campaign</strong> from the queue?
                </p>
                <form id="cancelScheduleForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Keep Scheduled</button>
                        <button type="submit" class="btn btn-danger btn-compact shadow-xs fw-bold">Yes, Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function triggerDispatchNowModal(campaignName, actionUrl) {
        document.getElementById('dispatchCampaignName').innerText = campaignName;
        document.getElementById('instantDispatchForm').action = actionUrl;
        const modalEl = document.getElementById('instantDispatchModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function triggerCancelScheduleModal(campaignName, actionUrl) {
        document.getElementById('cancelCampaignName').innerText = campaignName;
        document.getElementById('cancelScheduleForm').action = actionUrl;
        const modalEl = document.getElementById('cancelScheduleModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function filterScheduledTable() {
        const query     = (document.getElementById('scheduledSearchInput')?.value || '').toLowerCase().trim();
        const readiness = (document.getElementById('scheduledReadinessFilter')?.value || 'all').toLowerCase();

        const rows = document.querySelectorAll('.scheduled-entry-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const searchData    = row.getAttribute('data-search') || '';
            const itemReadiness = row.getAttribute('data-readiness') || '';

            const matchesQuery     = !query || searchData.includes(query);
            const matchesReadiness = readiness === 'all' || itemReadiness === readiness;

            if (matchesQuery && matchesReadiness) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const countBadge = document.getElementById('visible-scheduled-count');
        if (countBadge) {
            countBadge.innerText = `${visibleCount} Showing`;
        }

        const noMatchRow = document.getElementById('noMatchScheduledRow');
        if (noMatchRow) {
            noMatchRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }
    }

    function resetScheduledFilters() {
        const searchInput     = document.getElementById('scheduledSearchInput');
        const readinessFilter = document.getElementById('scheduledReadinessFilter');
        if (searchInput)     searchInput.value = '';
        if (readinessFilter) readinessFilter.value = 'all';
        filterScheduledTable();
    }
</script>
@endsection