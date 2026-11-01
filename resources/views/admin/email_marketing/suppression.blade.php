@extends('layouts.admin')

@section('title', 'Suppression & Blacklist - Email Marketing')

@php
    $totalCount = method_exists($suppressions, 'total') ? $suppressions->total() : $suppressions->count();
    
    if (class_exists(\App\Models\EmailSuppressionList::class)) {
        $hardBounceCount = \App\Models\EmailSuppressionList::whereIn('reason', ['hard_bounce', 'bounce'])->count();
        $spamCount       = \App\Models\EmailSuppressionList::where('reason', 'spam_complaint')->count();
        $unsubCount      = \App\Models\EmailSuppressionList::whereIn('reason', ['unsubscribe', 'compliance'])->count();
    } else {
        $hardBounceCount = method_exists($suppressions, 'whereIn') ? $suppressions->whereIn('reason', ['hard_bounce', 'bounce'])->count() : 0;
        $spamCount       = method_exists($suppressions, 'where') ? $suppressions->where('reason', 'spam_complaint')->count() : 0;
        $unsubCount      = method_exists($suppressions, 'whereIn') ? $suppressions->whereIn('reason', ['unsubscribe', 'compliance'])->count() : 0;
    }

    $subscribersRoute = Route::has('admin.email_marketing.subscribers.index') 
        ? route('admin.email_marketing.subscribers.index') 
        : url('admin/email-marketing/subscribers');

    $suppressionStoreRoute = Route::has('admin.email_marketing.suppression.store') 
        ? route('admin.email_marketing.suppression.store') 
        : url('admin/email-marketing/suppression/store');
@endphp

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-suppression-root .btn,
    #email-marketing-suppression-root .btn-sm,
    #email-marketing-suppression-root button,
    #email-marketing-suppression-root .badge,
    #email-marketing-suppression-root .alert,
    #email-marketing-suppression-root .form-control,
    #email-marketing-suppression-root .form-select,
    #email-marketing-suppression-root .input-group-text,
    #email-marketing-suppression-root .card,
    #email-marketing-suppression-root .metric-card,
    #email-marketing-suppression-root .page-link,
    #email-marketing-suppression-root .page-item span,
    #email-marketing-suppression-root .collapse-toggle-btn,
    .suppression-modal-scope .modal-content,
    .suppression-modal-scope .btn {
        border-radius: 5px !important;
    }

    #email-marketing-suppression-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #email-marketing-suppression-root .input-group > .form-control,
    #email-marketing-suppression-root .input-group > .form-select {
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

    .suppression-filter-control {
        font-size: 12px !important;
        height: 32px !important;
        padding: 4px 8px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        box-shadow: none !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .suppression-filter-control:focus {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12) !important;
        outline: none;
    }

    .suppression-addon-text {
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
        background-color: #ef4444;
        border-color: #ef4444;
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
    #email-marketing-suppression-root .card {
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

    .metric-card-red    { background: linear-gradient(180deg, #ffffff 0%, #fef2f2 100%); border-left: 3.5px solid #ef4444 !important; }
    .metric-card-dark   { background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); border-left: 3.5px solid #0f172a !important; }
    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-left: 3.5px solid #f59e0b !important; }
    .metric-card-slate  { background: linear-gradient(180deg, #ffffff 0%, #f1f5f9 100%); border-left: 3.5px solid #64748b !important; }

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .custom-suppression-table {
        width: 100%;
        margin-bottom: 0;
    }
    .custom-suppression-table thead th {
        font-size: 10px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 8px;
        white-space: nowrap;
    }
    .custom-suppression-table tbody td {
        padding: 8px 8px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        font-size: 11px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .custom-suppression-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .custom-suppression-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .custom-suppression-table th,
        .custom-suppression-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 6 Columns Proportional Distribution Summing to Exactly 100% */
        .col-sp-id      { width: 5% !important; }
        .col-sp-email   { width: 28% !important; }
        .col-sp-reason  { width: 16% !important; }
        .col-sp-details { width: 25% !important; }
        .col-sp-date    { width: 14% !important; }
        .col-sp-actions { width: 12% !important; }
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
        background-color: #ef4444 !important;
        border-color: #ef4444 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 3px rgba(239, 68, 68, 0.25) !important;
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
        .suppression-header-wrap h5 {
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
        .custom-suppression-table {
            min-width: 780px !important;
            table-layout: auto !important;
        }
        #email-marketing-suppression-root .card {
            margin-bottom: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-suppression-root">

    {{-- 1. Header & Actions (Original Clean Light Theme) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 suppression-header-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-ban text-danger"></i>
                    <span>Email Suppression &amp; Blacklist</span>
                </h5>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 font-monospace" id="suppression-total-badge" style="font-size: 10px;">
                    {{ number_format($totalCount) }} Suppressed
                </span>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                Emails on this list are strictly blocked from all promotional dispatches to protect domain sending reputation.
            </small>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 top-action-btns">
            <a href="{{ $subscribersRoute }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-users me-1"></i> <span>Subscribers</span>
            </a>
            <button type="button" class="btn btn-sm btn-danger btn-compact shadow-xs fw-bold" data-bs-toggle="modal" data-bs-target="#addSuppressionModal">
                <i class="fas fa-plus-circle me-1"></i> <span>Add To Blacklist</span>
            </button>
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

    {{-- 3. Live Suppression KPI Row (Collapsible Section) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-shield-halved text-danger me-1"></i> Suppression &amp; Reputation Protection Overview
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseSuppressionKpis" aria-expanded="true" aria-controls="collapseSuppressionKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseSuppressionKpis">
            <div class="p-3">
                <div class="row g-2">
                    {{-- Total Suppressed --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-red h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Total Suppressed</span>
                                <div class="metric-icon-square bg-danger-subtle text-danger shadow-xs">
                                    <i class="fas fa-ban"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    {{ number_format($totalCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Active blacklist entries</small>
                            </div>
                        </div>
                    </div>

                    {{-- Hard Bounces --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-red h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Hard Bounces</span>
                                <div class="metric-icon-square bg-danger-subtle text-danger shadow-xs">
                                    <i class="fas fa-envelope-circle-check"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-danger bn-digit font-monospace stat-val-num">
                                    {{ number_format($hardBounceCount) }}
                                </h4>
                                <small class="text-danger d-block text-truncate font-monospace stat-sub-text">Invalid / dead addresses</small>
                            </div>
                        </div>
                    </div>

                    {{-- Spam Complaints --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-dark h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Spam Complaints</span>
                                <div class="metric-icon-square bg-dark text-white shadow-xs">
                                    <i class="fas fa-triangle-exclamation"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    {{ number_format($spamCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">FBL &amp; direct flags</small>
                            </div>
                        </div>
                    </div>

                    {{-- Unsubscribed / Compliance --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Opt-Out &amp; Legal</span>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                                    <i class="fas fa-user-slash"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-warning-emphasis bn-digit font-monospace stat-val-num">
                                    {{ number_format($unsubCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Manual / 1-click requests</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Instant Search & Reason Filter Toolbar (Collapsible) --}}
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0 !important;">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-filter text-danger me-1"></i> Search Blacklist &amp; Filter by Suppression Reason
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseSuppressionFilter" aria-expanded="true" aria-controls="collapseSuppressionFilter" title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseSuppressionFilter">
            <div class="p-3">
                <form id="suppressionFilterForm" onsubmit="return false;">
                    <div class="row g-2 align-items-end">
                        {{-- Instant Live Search Input --}}
                        <div class="col-12 col-md-5">
                            <label class="form-label" for="suppressionSearchInput">
                                <i class="fas fa-search me-1 text-danger"></i> Search Blocked Email or Internal Notes
                            </label>
                            <div class="input-group">
                                <span class="input-group-text suppression-addon-text border-end-0"><i class="fas fa-search text-muted" style="font-size: 11px;"></i></span>
                                <input type="text" id="suppressionSearchInput" class="form-control border-start-0 ps-0 suppression-filter-control shadow-none font-monospace" placeholder="user@example.com, bounce, spam report..." onkeyup="filterSuppressionTable()">
                            </div>
                        </div>

                        {{-- Reason Filter Dropdown --}}
                        <div class="col-6 col-md-4">
                            <label class="form-label" for="suppressionReasonFilter">
                                <i class="fas fa-shield-halved me-1 text-danger"></i> Suppression Reason
                            </label>
                            <select id="suppressionReasonFilter" class="form-select suppression-filter-control shadow-none font-monospace" onchange="filterSuppressionTable()">
                                <option value="all" selected>-- All Suppression Reasons --</option>
                                <option value="hard_bounce">Hard Bounce / Dead Address</option>
                                <option value="spam_complaint">Spam Complaint / Flagged</option>
                                <option value="unsubscribe">Manual Unsubscribe</option>
                                <option value="compliance">Compliance / Legal</option>
                            </select>
                        </div>

                        {{-- Action Reset Button --}}
                        <div class="col-6 col-md-3">
                            <button type="button" class="btn btn-light border btn-compact w-100 shadow-xs fw-semibold text-secondary" onclick="resetSuppressionFilters()">
                                <i class="fas fa-rotate-left me-1"></i> <span>Reset Filters</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 5. Suppression List Card (Desktop 100% Fit Screen with Zero Horizontal Scroll) --}}
    <div class="card border-0 shadow-sm overflow-hidden mb-3">
        <div class="p-2 px-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 bg-light bg-opacity-25">
            <div>
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13px;">
                    <i class="fas fa-shield-halved text-danger"></i> <span>Suppressed Inboxes &amp; Quarantine Audit</span>
                </h6>
                <small class="text-muted font-monospace bn-digit" id="showing-entries-text" style="font-size: 10px;">
                    Showing {{ method_exists($suppressions, 'firstItem') ? ($suppressions->firstItem() ?? 0) : 1 }} to {{ method_exists($suppressions, 'lastItem') ? ($suppressions->lastItem() ?? 0) : $suppressions->count() }} of {{$totalCount }} records
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 10px;" id="visible-suppression-count">
                    {{ $totalCount }} Total
                </span>
                <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseSuppressionTable" aria-expanded="true" aria-controls="collapseSuppressionTable" title="Toggle Table">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseSuppressionTable">
            <div class="table-responsive pc-fit-container" style="min-height: 250px;">
                <table class="table align-middle mb-0 custom-suppression-table" id="suppression-table">
                    <thead>
                        <tr>
                            <th class="ps-3 col-sp-id text-center">#ID</th>
                            <th class="col-sp-email">Blocked Recipient Email</th>
                            <th class="col-sp-reason">Suppression Reason</th>
                            <th class="col-sp-details">Audit Notes / Diagnostics</th>
                            <th class="col-sp-date">Blocked At</th>
                            <th class="text-end pe-3 col-sp-actions">Action</th>
                        </tr>
                    </thead>
                    <tbody id="suppression-tbody">
                        @forelse($suppressions as $item)
                            @php
                                $reasonBadge = match(strtolower($item->reason ?? '')) {
                                    'hard_bounce', 'bounce' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    'spam_complaint'       => 'bg-dark text-white border border-dark',
                                    'unsubscribe'          => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                    'compliance'           => 'bg-info-subtle text-info border border-info-subtle',
                                    default                => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                };

                                $normalizedReason = match(strtolower($item->reason ?? '')) {
                                    'bounce' => 'hard_bounce',
                                    default  => strtolower($item->reason ?? ''),
                                };

                                $rowSearchData = strtolower(
                                    ($item->email ?? '') . ' ' .
                                    ($item->reason ?? '') . ' ' .
                                    ($item->details ?? '')
                                );

                                $deleteUrl = Route::has('admin.email_marketing.suppression.destroy') 
                                    ? route('admin.email_marketing.suppression.destroy', $item->id) 
                                    : url('admin/email-marketing/suppression/delete/' . $item->id);
                            @endphp
                            <tr class="suppression-entry-row align-middle" id="suppression-row-{{ $item->id }}" data-search="{{ $rowSearchData }}" data-reason="{{ $normalizedReason }}">
                                <td class="ps-3 col-sp-id text-center font-monospace bn-digit text-muted" style="font-size: 11px;">
                                    {{ $item->id }}
                                </td>
                                <td class="col-sp-email">
                                    <div class="min-w-0" style="line-height: 1.3;">
                                        <div class="d-flex align-items-center gap-1">
                                            <strong class="text-dark d-block text-truncate font-monospace" style="font-size: 12px; max-width: 220px;" title="{{ $item->email }}">
                                                {{ $item->email }}
                                            </strong>
                                            <button type="button" class="btn btn-sm btn-light border p-0 text-muted shadow-none d-inline-flex align-items-center justify-content-center" style="width: 18px; height: 18px;" onclick="copyBlockedEmail('{{ addslashes($item->email) }}')" title="Copy Email">
                                                <i class="fas fa-copy" style="font-size: 8.5px;"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted font-monospace d-block text-truncate" style="font-size: 10px;">
                                            Quarantined Address
                                        </small>
                                    </div>
                                </td>
                                <td class="col-sp-reason">
                                    <span class="badge {{ $reasonBadge }} font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 9.5px;">
                                        @if(in_array(strtolower($item->reason ?? ''), ['hard_bounce', 'bounce']))
                                            <i class="fas fa-triangle-exclamation me-1"></i>
                                        @elseif(strtolower($item->reason ?? '') === 'spam_complaint')
                                            <i class="fas fa-ban me-1"></i>
                                        @elseif(strtolower($item->reason ?? '') === 'unsubscribe')
                                            <i class="fas fa-user-slash me-1"></i>
                                        @endif
                                        {{ strtoupper(str_replace('_', ' ', $item->reason)) }}
                                    </span>
                                </td>
                                <td class="col-sp-details">
                                    <small class="text-muted d-block text-truncate font-monospace" style="font-size: 11px; max-width: 100%;" title="{{ $item->details ?? 'Automated system suppression' }}">
                                        {{ $item->details ?? 'Automated system suppression' }}
                                    </small>
                                </td>
                                <td class="col-sp-date font-monospace bn-digit" style="line-height: 1.3;">
                                    <span class="text-dark d-block" style="font-size: 11px;">{{ $item->created_at->format('M d, Y') }}</span>
                                    <small class="text-muted" style="font-size: 9.5px;">{{ $item->created_at->format('h:i A') }}</small>
                                </td>
                                <td class="text-end pe-3 col-sp-actions">
                                    <button type="button" class="btn btn-sm btn-light border text-danger btn-compact shadow-xs fw-semibold" style="height: 26px; padding: 2px 8px !important;" onclick="triggerUnblockModal({{ $item->id }}, '{{ addslashes($item->email) }}', '{{$deleteUrl }}')" title="Unblock / Remove from Blacklist">
                                        <i class="fas fa-unlock me-1" style="font-size: 10px;"></i> <span>Unblock</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptySuppressionRow">
                                <td colspan="6" class="text-center py-5 text-muted small">
                                    <i class="fas fa-shield-check fa-2x mb-2 d-block text-success opacity-50"></i>
                                    <span class="fw-semibold">No suppressed or blacklisted emails found. Domain reputation is clean.</span>
                                </td>
                            </tr>
                        @endforelse
                        <tr id="noMatchSuppressionRow" style="display: none;">
                            <td colspan="6" class="text-center py-5 text-muted small">
                                <i class="fas fa-filter-circle-xmark fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                <span class="fw-semibold">No quarantined emails matched your search or reason filter.</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Bulletproof Responsive Pagination Footer --}}
            @if(method_exists($suppressions, 'hasPages') &&$suppressions->hasPages())
                <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">
                    <small class="text-muted font-monospace bn-digit" style="font-size: 11px;">
                        Showing {{ $suppressions->firstItem() }} to {{ $suppressions->lastItem() }} of {{$suppressions->total() }} records
                    </small>
                    <div>
                        {{ $suppressions->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal 1: Add Email to Suppression Modal --}}
<div class="modal fade suppression-modal-scope" id="addSuppressionModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom py-2 px-3 bg-light d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                    <i class="fas fa-ban text-danger"></i> <span>Add Email to Blacklist</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ $suppressionStoreRoute }}" method="POST">
                @csrf
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label" for="suppression_email_input">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="suppression_email_input" class="form-control font-monospace shadow-none" placeholder="customer@example.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="suppression_reason_select">Suppression Reason <span class="text-danger">*</span></label>
                        <select name="reason" id="suppression_reason_select" class="form-select font-monospace shadow-none" required>
                            <option value="hard_bounce">Hard Bounce (Invalid Address)</option>
                            <option value="spam_complaint">Spam Complaint / Flagged</option>
                            <option value="unsubscribe">Manual Unsubscribe Request</option>
                            <option value="compliance">Legal / Compliance Request</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="suppression_details_input">Internal Audit Notes</label>
                        <textarea name="details" id="suppression_details_input" rows="2" class="form-control shadow-none font-monospace" placeholder="Reason details or ticket reference..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-compact shadow-xs fw-bold">
                        <i class="fas fa-ban me-1"></i> Confirm Blacklist
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal 2: Custom Enterprise Unblock Confirmation Modal --}}
<div class="modal fade suppression-modal-scope" id="unblockConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-warning-subtle text-warning-emphasis d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 44px; height: 44px; border-radius: 50% !important;">
                    <i class="fas fa-unlock fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Unblock Recipient?</h6>
                <p class="text-muted small mb-3" style="font-size: 11px; line-height: 1.45;">
                    Are you sure you want to remove <strong id="unblockModalEmail" class="text-dark font-monospace">email</strong> from the suppression blacklist? Future campaigns may reach this inbox again.
                </p>
                <form id="unblockModalForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-compact shadow-xs fw-bold">Yes, Unblock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function copyBlockedEmail(email) {
        if (email && navigator.clipboard) {
            navigator.clipboard.writeText(email);
        }
    }

    function triggerUnblockModal(id, email, deleteUrl) {
        document.getElementById('unblockModalEmail').innerText = email;
        document.getElementById('unblockModalForm').action = deleteUrl;
        const modalEl = document.getElementById('unblockConfirmModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function filterSuppressionTable() {
        const query  = (document.getElementById('suppressionSearchInput')?.value || '').toLowerCase().trim();
        const reason = (document.getElementById('suppressionReasonFilter')?.value || 'all').toLowerCase();

        const rows = document.querySelectorAll('.suppression-entry-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const searchData = row.getAttribute('data-search') || '';
            const rowReason  = row.getAttribute('data-reason') || '';

            const matchesQuery  = !query || searchData.includes(query);
            const matchesReason = reason === 'all' || rowReason === reason;

            if (matchesQuery && matchesReason) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const countBadge = document.getElementById('visible-suppression-count');
        if (countBadge) {
            countBadge.innerText = `${visibleCount} Showing`;
        }

        const noMatchRow = document.getElementById('noMatchSuppressionRow');
        if (noMatchRow) {
            noMatchRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }
    }

    function resetSuppressionFilters() {
        const searchInput  = document.getElementById('suppressionSearchInput');
        const reasonFilter = document.getElementById('suppressionReasonFilter');
        if (searchInput)  searchInput.value = '';
        if (reasonFilter) reasonFilter.value = 'all';
        filterSuppressionTable();
    }
</script>
@endsection