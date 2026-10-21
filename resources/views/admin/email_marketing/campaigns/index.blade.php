@extends('layouts.admin')

@section('title', 'Campaigns - Email Marketing')

@php
    $totalCampaignsCount = $campaigns->total();
    $activeCount         = \App\Models\EmailCampaign::whereIn('status', ['sending', 'queued', 'scheduled'])->count();
    $completedCount      = \App\Models\EmailCampaign::where('status', 'completed')->count();
    $totalRevenueSum     = (float) \App\Models\EmailCampaign::sum('total_revenue');
@endphp

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-campaigns-root .btn,
    #email-marketing-campaigns-root .btn-sm,
    #email-marketing-campaigns-root button,
    #email-marketing-campaigns-root .badge,
    #email-marketing-campaigns-root .alert,
    #email-marketing-campaigns-root .form-control,
    #email-marketing-campaigns-root .form-select,
    #email-marketing-campaigns-root .input-group-text,
    #email-marketing-campaigns-root .card,
    #email-marketing-campaigns-root .metric-card,
    #email-marketing-campaigns-root .page-link,
    #email-marketing-campaigns-root .page-item span,
    #email-marketing-campaigns-root .collapse-toggle-btn,
    #email-marketing-campaigns-root .progress,
    .campaigns-modal-scope .modal-content,
    .campaigns-modal-scope .btn,
    .admin-campaign-toast {
        border-radius: 5px !important;
    }

    #email-marketing-campaigns-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #email-marketing-campaigns-root .input-group > .form-control,
    #email-marketing-campaigns-root .input-group > .form-select {
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

    .campaign-filter-control {
        font-size: 12px !important;
        height: 32px !important;
        padding: 4px 8px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        box-shadow: none !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .campaign-filter-control:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        outline: none;
    }

    .campaign-addon-text {
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
    #email-marketing-campaigns-root .card {
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

    .metric-card-blue   { background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%); border-left: 3.5px solid #2563eb !important; }
    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-left: 3.5px solid #f59e0b !important; }
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-left: 3.5px solid #10b981 !important; }
    .metric-card-emerald{ background: linear-gradient(180deg, #ffffff 0%, #ecfdf5 100%); border-left: 3.5px solid #059669 !important; }

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .custom-campaigns-table {
        width: 100%;
        margin-bottom: 0;
    }
    .custom-campaigns-table thead th {
        font-size: 10.5px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 9px 8px;
        white-space: nowrap;
    }
    .custom-campaigns-table tbody td {
        padding: 8px 8px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        font-size: 12px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .custom-campaigns-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .custom-campaigns-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .custom-campaigns-table th,
        .custom-campaigns-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 9 Columns Proportional Distribution Summing to Exactly 100% */
        .col-cp-id      { width: 4% !important; }
        .col-cp-name    { width: 25% !important; }
        .col-cp-target  { width: 13% !important; }
        .col-cp-status  { width: 11% !important; }
        .col-cp-recip   { width: 14% !important; }
        .col-cp-open    { width: 7% !important; }
        .col-cp-click   { width: 7% !important; }
        .col-cp-rev     { width: 9% !important; }
        .col-cp-actions { width: 10% !important; }
    }

    /* Bulletproof Responsive Pagination */
    #campaigns-pagination-links nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }
    #campaigns-pagination-links nav > div.d-sm-none,
    #campaigns-pagination-links nav > .d-sm-none,
    #campaigns-pagination-links nav p,
    #campaigns-pagination-links nav .small.text-muted,
    #campaigns-pagination-links nav div:has(> p) {
        display: none !important;
    }
    #campaigns-pagination-links .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 3px !important;
        padding: 0 !important;
        list-style: none !important;
    }
    #campaigns-pagination-links .page-item {
        display: inline-flex !important;
    }
    #campaigns-pagination-links .page-item .page-link,
    #campaigns-pagination-links .page-item span {
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
    #campaigns-pagination-links .page-item .page-link:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }
    #campaigns-pagination-links .page-item.active .page-link,
    #campaigns-pagination-links .page-item.active span {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25) !important;
    }
    #campaigns-pagination-links .page-item.disabled .page-link,
    #campaigns-pagination-links .page-item.disabled span {
        opacity: 0.45 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }

    /* Floating Toast Box */
    .admin-campaign-toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 108000;
        min-width: 280px;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.2);
        animation: slideInRight 0.25s ease;
    }
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .campaign-header-wrap h5 {
            font-size: 16px !important;
        }
        .top-action-btns {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .top-action-btns .btn-create-main {
            grid-column: span 2;
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
        .custom-campaigns-table {
            min-width: 880px !important;
            table-layout: auto !important;
        }
        #email-marketing-campaigns-root .card {
            margin-bottom: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-campaigns-root">

    {{-- 1. Header & Actions (Original Clean Light Theme) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 campaign-header-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-paper-plane text-primary"></i>
                    <span>Marketing Campaigns</span>
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace" id="campaigns-total-badge" style="font-size: 10px;">
                    {{ $totalCampaignsCount }} Campaigns
                </span>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                Create, schedule, dispatch, and track promotional and segment-targeted email broadcasts.
            </small>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 top-action-btns">
            <a href="{{ url('admin/email-marketing/dashboard') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-chart-pie me-1"></i> <span>Dashboard</span>
            </a>
            <a href="{{ url('admin/email-marketing/campaigns/create') }}" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold btn-create-main">
                <i class="fas fa-plus-circle me-1"></i> <span>Create Campaign</span>
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

    {{-- 3. Overview KPI Summary Cards (Collapsible Section) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-chart-pie text-primary me-1"></i> Campaign Portfolio &amp; Dispatch Overview
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseCampaignsKpis" aria-expanded="true" aria-controls="collapseCampaignsKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseCampaignsKpis">
            <div class="p-3">
                <div class="row g-2">
                    {{-- Total Broadcasts --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Total Campaigns</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num" id="stat-total-campaigns">
                                    {{ $totalCampaignsCount }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Configured broadcasts</small>
                            </div>
                        </div>
                    </div>

                    {{-- Active / In-Queue --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Active Engine</span>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-warning-emphasis bn-digit font-monospace stat-val-num" id="stat-active-engine">
                                    {{ $activeCount }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Sending or scheduled</small>
                            </div>
                        </div>
                    </div>

                    {{-- Completed Broadcasts --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Completed</span>
                                <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                                    <i class="fas fa-circle-check"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num" id="stat-completed-campaigns">
                                    {{ $completedCount }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Successfully dispatched</small>
                            </div>
                        </div>
                    </div>

                    {{-- Attributed Revenue --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-emerald h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Attributed Sales</span>
                                <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                                    <i class="fas fa-coins"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num" id="stat-attributed-sales">
                                    ৳ {{ number_format($totalRevenueSum, 2) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Direct conversions</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Status Filter & Live Search Toolbar (Collapsible) --}}
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0 !important;">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-filter text-primary me-1"></i> Multi-Dimensional Filter &amp; Search Toolbar
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseCampaignFilter" aria-expanded="true" aria-controls="collapseCampaignFilter" title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseCampaignFilter">
            <div class="p-3">
                <form id="campaignFilterForm" onsubmit="return false;">
                    <div class="row g-2 align-items-end">
                        {{-- Instant Live Search Input --}}
                        <div class="col-12 col-md-5">
                            <label class="form-label" for="campaignSearchInput">
                                <i class="fas fa-search me-1 text-primary"></i> Search Campaign Name, Subject or Audience
                            </label>
                            <div class="input-group">
                                <span class="input-group-text campaign-addon-text border-end-0"><i class="fas fa-search text-muted" style="font-size: 11px;"></i></span>
                                <input type="text" name="search" id="campaignSearchInput" class="form-control border-start-0 ps-0 campaign-filter-control shadow-none font-monospace" placeholder="e.g. Black Friday, Welcome Offer, VIP Special...">
                            </div>
                        </div>

                        {{-- Status Dropdown (Live Filter) --}}
                        <div class="col-6 col-md-4">
                            <label class="form-label" for="campaignStatusSelect">
                                <i class="fas fa-toggle-on me-1 text-success"></i> Campaign Status
                            </label>
                            <select name="status" id="campaignStatusSelect" class="form-select campaign-filter-control shadow-none font-monospace filter-trigger">
                                <option value="">-- All Campaign Statuses --</option>
                                <option value="draft">Draft</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="queued">Queued / In Queue</option>
                                <option value="sending">Sending Now</option>
                                <option value="completed">Completed</option>
                                <option value="paused">Paused</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        {{-- Action Reset Button --}}
                        <div class="col-6 col-md-3">
                            <button type="button" class="btn btn-light border btn-compact w-100 shadow-xs fw-semibold text-secondary" onclick="resetCampaignFilters()">
                                <i class="fas fa-rotate-left me-1"></i> <span>Reset Filters</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 5. Campaigns Table Card (Desktop 100% Fit Screen with Zero Horizontal Scroll) --}}
    <div class="card border-0 shadow-sm overflow-hidden mb-3">
        <div class="p-2 px-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 bg-light bg-opacity-25">
            <div>
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13px;">
                    <i class="fas fa-envelopes-bulk text-primary"></i> <span>Broadcast Campaigns &amp; Delivery Matrix</span>
                </h6>
                <small class="text-muted font-monospace bn-digit" id="showing-entries-text" style="font-size: 10px;">
                    Showing {{ $campaigns->firstItem() ?? 0 }} to {{ $campaigns->lastItem() ?? 0 }} of {{ $campaigns->total() }} campaigns
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 10px;" id="visible-campaign-count">
                    {{ $campaigns->total() }} Total
                </span>
                <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseCampaignsTable" aria-expanded="true" aria-controls="collapseCampaignsTable" title="Toggle Table">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseCampaignsTable">
            <div class="table-responsive pc-fit-container" style="min-height: 250px;">
                <table class="table align-middle mb-0 custom-campaigns-table" id="campaigns-table">
                    <thead>
                        <tr>
                            <th class="ps-3 col-cp-id text-center">#</th>
                            <th class="col-cp-name">Campaign &amp; Subject</th>
                            <th class="col-cp-target">Target Audience</th>
                            <th class="col-cp-status">Status</th>
                            <th class="col-cp-recip">Delivery Progress</th>
                            <th class="text-center col-cp-open">Open %</th>
                            <th class="text-center col-cp-click">Click %</th>
                            <th class="col-cp-rev">Attributed Revenue</th>
                            <th class="text-end pe-3 col-cp-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="campaigns-tbody">
                        @forelse($campaigns as $camp)
                            @php
                                $badgeClass = match($camp->status) {
                                    'completed'         => 'bg-success-subtle text-success border border-success-subtle',
                                    'sending', 'queued' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                    'scheduled'         => 'bg-info-subtle text-info border border-info-subtle',
                                    'paused'            => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                    'cancelled'         => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    default             => 'bg-light text-dark border',
                                };

                                $rowSearchData = strtolower(
                                    ($camp->name ?? '') . ' ' .
                                    ($camp->subject ?? '') . ' ' .
                                    ($camp->segment->name ?? 'all consented users') . ' ' .
                                    ($camp->status ?? '')
                                );

                                $progress = (float)($camp->progress_percentage ?? 0.0);
                                if ($camp->status === 'completed') {
                                    $progress = 100.0;
                                }
                            @endphp
                            <tr class="campaign-entry-row align-middle" id="campaign-row-{{ $camp->id }}" data-search="{{ $rowSearchData }}" data-status="{{ strtolower($camp->status) }}">
                                <td class="ps-3 col-cp-id text-center font-monospace bn-digit text-muted" style="font-size: 11px;">
                                    {{ $camp->id }}
                                </td>
                                <td class="col-cp-name">
                                    <div class="min-w-0" style="line-height: 1.3;">
                                        <a href="{{ url('admin/email-marketing/campaigns/view/' . $camp->id) }}" class="fw-bold text-dark text-decoration-none d-block text-truncate" style="font-size: 12px;" title="{{ $camp->name }}">
                                            {{ $camp->name }}
                                        </a>
                                        <small class="text-muted d-block text-truncate font-monospace" style="font-size: 10px;" title="{{ $camp->subject }}">
                                            <i class="fas fa-envelope me-1 text-secondary"></i> {{ $camp->subject }}
                                        </small>
                                    </div>
                                </td>
                                <td class="col-cp-target">
                                    @if($camp->segment)
                                        <span class="badge bg-light text-dark border font-monospace text-truncate d-inline-block px-2 py-0" style="font-size: 10px; max-width: 100%; border-radius: 5px !important;" title="{{ $camp->segment->name }}">
                                            <i class="fas fa-filter me-1 text-primary"></i> {{ $camp->segment->name }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border font-monospace text-truncate d-inline-block px-2 py-0" style="font-size: 10px; max-width: 100%; border-radius: 5px !important;">
                                            <i class="fas fa-users me-1"></i> All Consented Users
                                        </span>
                                    @endif
                                </td>
                                <td class="col-cp-status">
                                    <span class="badge {{ $badgeClass }} font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 9.5px;">
                                        @if(in_array($camp->status, ['sending', 'queued']))
                                            <i class="fas fa-spinner fa-spin me-1"></i>
                                        @endif
                                        {{ strtoupper($camp->status) }}
                                    </span>
                                </td>
                                <td class="col-cp-recip">
                                    <div class="d-flex justify-content-between align-items-center font-monospace bn-digit mb-1" style="font-size: 10.5px;">
                                        <span class="fw-bold text-dark">{{ number_format($camp->sent_count ?? 0) }}/{{ number_format($camp->total_recipients ?? 0) }}</span>
                                        <span class="text-muted">{{ round($progress, 1) }}%</span>
                                    </div>
                                    <div class="progress" style="height: 5px; border-radius: 3px; background-color: #e2e8f0;">
                                        <div class="progress-bar {{ $camp->status === 'completed' ? 'bg-success' : 'bg-primary progress-bar-striped progress-bar-animated' }}" 
                                             role="progressbar" 
                                             style="width: {{ $progress }}%;" 
                                             aria-valuenow="{{ $progress }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100"></div>
                                    </div>
                                </td>
                                <td class="text-center col-cp-open font-monospace bn-digit fw-semibold text-success" style="font-size: 11.5px;">
                                    <div>{{ $camp->open_rate }}%</div>
                                    <small class="text-muted d-block" style="font-size: 9px; font-weight: normal;">{{ number_format($camp->opened_count ?? 0) }} opens</small>
                                </td>
                                <td class="text-center col-cp-click font-monospace bn-digit fw-semibold text-primary" style="font-size: 11.5px;">
                                    <div>{{ $camp->click_rate }}%</div>
                                    <small class="text-muted d-block" style="font-size: 9px; font-weight: normal;">{{ number_format($camp->clicked_count ?? 0) }} clicks</small>
                                </td>
                                <td class="col-cp-rev font-monospace bn-digit fw-bold text-dark" style="font-size: 11.5px;">
                                    ৳ {{ number_format($camp->total_revenue ?? 0, 2) }}
                                </td>
                                <td class="text-end pe-3 col-cp-actions">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        {{-- 1. View Analytics / Overview --}}
                                        <a href="{{ url('admin/email-marketing/campaigns/view/' . $camp->id) }}" class="btn btn-sm btn-light border p-1 text-primary shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; border-radius: 5px !important;" title="View Full Report">
                                            <i class="fas fa-chart-line" style="font-size: 10px;"></i>
                                        </a>

                                        {{-- 2. Dispatch Now (for draft/scheduled) --}}
                                        @if(in_array($camp->status, ['draft', 'scheduled']))
                                            <button type="button" class="btn btn-sm btn-light border p-1 text-success shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; border-radius: 5px !important;" onclick="triggerActionConfirm('Dispatch Campaign', 'Queue this campaign for immediate broadcast to all target recipients?', '{{ url('admin/email-marketing/campaigns/dispatch-now/' . $camp->id) }}', 'dispatch')" title="Dispatch / Send Now">
                                                <i class="fas fa-paper-plane" style="font-size: 10px;"></i>
                                            </button>
                                        @endif

                                        {{-- 3. Pause / Resume --}}
                                        @if(in_array($camp->status, ['queued', 'sending']))
                                            <button type="button" class="btn btn-sm btn-light border p-1 text-warning shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; border-radius: 5px !important;" onclick="ajaxCampaignAction('{{ url('admin/email-marketing/campaigns/pause/' . $camp->id) }}', 'Pause Broadcast', 'POST')" title="Pause Dispatch">
                                                <i class="fas fa-pause" style="font-size: 10px;"></i>
                                            </button>
                                        @elseif($camp->status === 'paused')
                                            <button type="button" class="btn btn-sm btn-light border p-1 text-success shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; border-radius: 5px !important;" onclick="ajaxCampaignAction('{{ url('admin/email-marketing/campaigns/resume/' . $camp->id) }}', 'Resume Broadcast', 'POST')" title="Resume Dispatch">
                                                <i class="fas fa-play" style="font-size: 10px;"></i>
                                            </button>
                                        @endif

                                        {{-- 4. Centralized Test Send Trigger --}}
                                        <button type="button" class="btn btn-sm btn-light border p-1 text-info shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; border-radius: 5px !important;" onclick="openTestMailModal({{ $camp->id }}, '{{ addslashes($camp->name) }}', '{{ url('admin/email-marketing/campaigns/send-test/' .$camp->id) }}')" title="Send Test Email">
                                            <i class="fas fa-vial" style="font-size: 10px;"></i>
                                        </button>

                                        {{-- 5. Edit (only if editable) --}}
                                        @if($camp->isEditable())
                                            <a href="{{ url('admin/email-marketing/campaigns/edit/' . $camp->id) }}" class="btn btn-sm btn-light border p-1 text-secondary shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; border-radius: 5px !important;" title="Edit Campaign Settings">
                                                <i class="fas fa-pencil" style="font-size: 10px;"></i>
                                            </a>
                                        @endif

                                        {{-- 6. Delete via Custom Modal --}}
                                        <button type="button" class="btn btn-sm btn-light border p-1 text-danger shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; border-radius: 5px !important;" onclick="triggerActionConfirm('Delete Campaign', 'Are you sure you want to delete this campaign? All recipient delivery records will be removed.', '{{ url('admin/email-marketing/campaigns/delete/' . $camp->id) }}', 'delete')" title="Delete Campaign">
                                            <i class="fas fa-trash-can" style="font-size: 10px;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyCampaignsRow">
                                <td colspan="9" class="text-center py-5 text-muted small">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                    <span class="fw-semibold">No marketing campaigns found. Click "Create Campaign" to launch your first one.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Bulletproof Responsive Pagination Footer --}}
            <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print" id="campaigns-pagination-footer" style="{{ $campaigns->hasPages() ? '' : 'display: none;' }}">
                <small class="text-muted font-monospace bn-digit" id="pagination-info-text" style="font-size: 11px;">
                    Showing {{ $campaigns->firstItem() ?? 0 }} to {{ $campaigns->lastItem() ?? 0 }} of {{$campaigns->total() }} campaigns
                </small>
                <div id="campaigns-pagination-links">
                    @if($campaigns->hasPages())
                        {{ $campaigns->links('pagination::bootstrap-5') }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal 1: Centralized Reusable Test Send Email Modal --}}
<div class="modal fade campaigns-modal-scope" id="centralTestMailModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom py-2 px-3 bg-light d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                    <i class="fas fa-vial text-info"></i> <span>Send Test Email</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="centralTestMailForm" onsubmit="submitTestEmailAjax(event)">
                <div class="modal-body p-3">
                    <p class="small text-muted mb-2" style="font-size: 11px; line-height: 1.45;">
                        Send an instant test preview of <strong id="testModalCampName" class="text-dark">Campaign</strong> to verify design, dynamic tags, and deliverability.
                    </p>
                    <label class="form-label" for="test_email_input">Recipient Test Email <span class="text-danger">*</span></label>
                    <input type="email" name="test_email" id="test_email_input" class="form-control font-monospace shadow-none" placeholder="your-email@example.com" value="{{ auth()->user()->email ?? '' }}" required>
                </div>
                <div class="modal-footer border-top py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info btn-compact text-white shadow-xs fw-bold" id="btn-submit-test-mail">
                        <i class="fas fa-paper-plane me-1"></i> Send Test
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal 2: Reusable Action Confirmation Modal (Replaces Browser Confirm) --}}
<div class="modal fade campaigns-modal-scope" id="campaignActionModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-2" id="actionModalIconWrap" style="width: 44px; height: 44px; border-radius: 50% !important;">
                    <i class="fas fa-circle-question fs-4" id="actionModalIcon"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="actionModalTitle" style="font-size: 14px;">Confirm Action</h6>
                <p class="text-muted small mb-3" id="actionModalMsg" style="font-size: 11px; line-height: 1.45;"></p>
                <form id="actionModalForm" onsubmit="executeActionModalAjax(event)">
                    <div id="actionModalMethodWrap"></div>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold" id="actionModalSubmitBtn">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Floating Toast Feedback Notification Holder --}}
<div id="custom-toast-holder"></div>

<script>
    let searchTimeout = null;
    let pendingActionUrl = '';
    let pendingActionMethod = 'POST';

    // 1. Live Reload-Free Fetch (Uses DOMParser so no controller refactor is needed)
    function fetchFilteredCampaigns(page = 1) {
        const query = (document.getElementById('campaignSearchInput')?.value || '').trim();
        const status = (document.getElementById('campaignStatusSelect')?.value || '').trim();

        const params = new URLSearchParams();
        if (query) params.append('search', query);
        if (status) params.append('status', status);
        if (page > 1) params.append('page', page);

        const tbody = document.getElementById('campaigns-tbody');
        if (tbody) tbody.style.opacity = '0.35';

        fetch("{{ url('admin/email-marketing/campaigns') }}?" + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newTbody = doc.getElementById('campaigns-tbody');
            if (tbody && newTbody) {
                tbody.innerHTML = newTbody.innerHTML;
                tbody.style.opacity = '1';
            }

            const newPagination = doc.getElementById('campaigns-pagination-links');
            const currentPagination = document.getElementById('campaigns-pagination-links');
            const paginationFooter = document.getElementById('campaigns-pagination-footer');

            if (newPagination && currentPagination) {
                currentPagination.innerHTML = newPagination.innerHTML;
                if (paginationFooter) {
                    paginationFooter.style.display = newPagination.innerHTML.trim() === '' ? 'none' : 'flex';
                }
            }

            const newEntries = doc.getElementById('showing-entries-text');
            const currentEntries = document.getElementById('showing-entries-text');
            if (newEntries && currentEntries) {
                currentEntries.innerHTML = newEntries.innerHTML;
            }

            const newTotalBadge = doc.getElementById('campaigns-total-badge');
            const currentTotalBadge = document.getElementById('campaigns-total-badge');
            if (newTotalBadge && currentTotalBadge) {
                currentTotalBadge.innerHTML = newTotalBadge.innerHTML;
            }

            // Client-side visual count sync
            filterCampaignsTableClient();
        })
        .catch(err => {
            console.error('Fetch error:', err);
            if (tbody) tbody.style.opacity = '1';
        });
    }

    // 2. Client-Side Realtime Search
    function filterCampaignsTableClient() {
        const query = (document.getElementById('campaignSearchInput')?.value || '').toLowerCase().trim();
        const status = (document.getElementById('campaignStatusSelect')?.value || '').toLowerCase().trim();

        const rows = document.querySelectorAll('.campaign-entry-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const searchData = row.getAttribute('data-search') || '';
            const rowStatus = row.getAttribute('data-status') || '';

            const matchesQuery = !query || searchData.includes(query);
            const matchesStatus = !status || rowStatus === status;

            if (matchesQuery && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const countBadge = document.getElementById('visible-campaign-count');
        if (countBadge) {
            countBadge.innerText = `${visibleCount} Showing`;
        }
    }

    // 3. Search & Dropdown Listeners
    document.getElementById('campaignSearchInput')?.addEventListener('input', function() {
        filterCampaignsTableClient();
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => fetchFilteredCampaigns(1), 500);
    });

    document.getElementById('campaignStatusSelect')?.addEventListener('change', function() {
        fetchFilteredCampaigns(1);
    });

    function resetCampaignFilters() {
        const searchInput = document.getElementById('campaignSearchInput');
        const statusSelect = document.getElementById('campaignStatusSelect');
        if (searchInput) searchInput.value = '';
        if (statusSelect) statusSelect.value = '';
        fetchFilteredCampaigns(1);
    }

    // 4. Pagination Interceptor
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#campaigns-pagination-links .page-link');
        if (pageLink) {
            e.preventDefault();
            const href = pageLink.getAttribute('href');
            if (href && href !== '#' && !href.startsWith('javascript:')) {
                const url = new URL(href);
                const page = url.searchParams.get('page') || 1;
                fetchFilteredCampaigns(page);
                document.querySelector('.custom-campaigns-table')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    });

    // 5. Test Mail Modal & AJAX Dispatch
    function openTestMailModal(campId, campName, submitUrl) {
        document.getElementById('testModalCampName').innerText = campName;
        pendingActionUrl = submitUrl;
        const modalEl = document.getElementById('centralTestMailModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function submitTestEmailAjax(e) {
        e.preventDefault();
        const email = document.getElementById('test_email_input').value.trim();
        const btn = document.getElementById('btn-submit-test-mail');

        if (!email) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sending...';

        fetch(pendingActionUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ test_email: email })
        })
        .then(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Test';
            bootstrap.Modal.getInstance(document.getElementById('centralTestMailModal'))?.hide();
            showToast('Dispatched', `Test email sent successfully to ${email}.`, 'success');
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Test';
            showToast('Error', 'Failed to send test email. Please check SMTP configuration.', 'danger');
        });
    }

    // 6. Action Confirmation Modal & AJAX Handler
    function triggerActionConfirm(title, message, actionUrl, type = 'dispatch') {
        const titleEl    = document.getElementById('actionModalTitle');
        const msgEl      = document.getElementById('actionModalMsg');
        const btnEl      = document.getElementById('actionModalSubmitBtn');
        const iconWrapEl = document.getElementById('actionModalIconWrap');
        const iconEl     = document.getElementById('actionModalIcon');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;
        pendingActionUrl = actionUrl;

        if (type === 'delete') {
            pendingActionMethod = 'DELETE';
            iconWrapEl.className = 'rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2';
            iconEl.className = 'fas fa-trash-can fs-4';
            btnEl.className = 'btn btn-danger btn-compact shadow-xs fw-bold';
            btnEl.innerText = 'Yes, Delete';
        } else {
            pendingActionMethod = 'POST';
            iconWrapEl.className = 'rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-2';
            iconEl.className = 'fas fa-paper-plane fs-4';
            btnEl.className = 'btn btn-success btn-compact shadow-xs fw-bold';
            btnEl.innerText = 'Yes, Dispatch';
        }

        const modalEl = document.getElementById('campaignActionModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function executeActionModalAjax(e) {
        e.preventDefault();
        const btn = document.getElementById('actionModalSubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';

        fetch(pendingActionUrl, {
            method: pendingActionMethod,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(() => {
            btn.disabled = false;
            bootstrap.Modal.getInstance(document.getElementById('campaignActionModal'))?.hide();
            showToast('Success', 'Campaign operation executed successfully.', 'success');
            fetchFilteredCampaigns(1);
        })
        .catch(() => {
            btn.disabled = false;
            showToast('Error', 'Failed to execute campaign operation.', 'danger');
        });
    }

    // 7. Direct Pause / Resume AJAX
    function ajaxCampaignAction(actionUrl, actionName, method = 'POST') {
        fetch(actionUrl, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(() => {
            showToast('Updated', `${actionName} executed successfully.`, 'info');
            fetchFilteredCampaigns(1);
        })
        .catch(() => {
            showToast('Error', `Failed to execute ${actionName}.`, 'danger');
        });
    }

    // 8. Floating Toast Notifications
    function showToast(title, message, type = 'success') {
        const holder = document.getElementById('custom-toast-holder');
        if (!holder) return;

        const toastId = 'toast-' + Date.now();
        const typeConfigs = {
            success: { icon: 'fa-check-circle', color: 'bg-success text-white' },
            danger:  { icon: 'fa-trash-can',    color: 'bg-danger text-white' },
            warning: { icon: 'fa-triangle-exclamation', color: 'bg-warning text-dark' },
            info:    { icon: 'fa-circle-info',  color: 'bg-primary text-white' }
        };
        const config = typeConfigs[type] || typeConfigs.success;

        const html = `
            <div id="${toastId}" class="admin-campaign-toast ${config.color} p-3 d-flex align-items-center justify-content-between shadow-lg" style="border-radius: 5px !important;">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas ${config.icon} fs-5"></i>
                    <div>
                        <strong class="d-block" style="font-size: 13px;">${title}</strong>
                        <small style="opacity: 0.95; font-size: 11px;">${message}</small>
                    </div>
                </div>
                <button type="button" class="btn-close ${type === 'warning' ? '' : 'btn-close-white'} btn-sm ms-2 shadow-none" onclick="document.getElementById('${toastId}')?.remove()"></button>
            </div>
        `;

        holder.insertAdjacentHTML('beforeend', html);
        setTimeout(() => {
            const el = document.getElementById(toastId);
            if (el) el.remove();
        }, 3500);
    }
</script>
@endsection