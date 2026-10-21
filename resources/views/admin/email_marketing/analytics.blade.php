@extends('layouts.admin')

@section('title', 'Email Analytics & Events - Email Marketing')

@php
    $totalEventsCount =$events->total();
    $openEventsCount  =$events->where('event_type', 'open')->count();
    $clickEventsCount =$events->where('event_type', 'click')->count();
    $unsubEventsCount =$events->where('event_type', 'unsubscribe')->count();
@endphp

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-analytics-root .btn,
    #email-marketing-analytics-root .btn-sm,
    #email-marketing-analytics-root button,
    #email-marketing-analytics-root .badge,
    #email-marketing-analytics-root .alert,
    #email-marketing-analytics-root .form-control,
    #email-marketing-analytics-root .form-select,
    #email-marketing-analytics-root .input-group-text,
    #email-marketing-analytics-root .card,
    #email-marketing-analytics-root .metric-card,
    #email-marketing-analytics-root .page-link,
    #email-marketing-analytics-root .page-item span,
    #email-marketing-analytics-root .collapse-toggle-btn,
    .analytics-modal-scope .modal-content,
    .analytics-modal-scope .btn {
        border-radius: 5px !important;
    }

    #email-marketing-analytics-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #email-marketing-analytics-root .input-group > .form-control {
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
    #email-marketing-analytics-root .card {
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
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-left: 3.5px solid #10b981 !important; }
    .metric-card-cyan   { background: linear-gradient(180deg, #ffffff 0%, #ecfeff 100%); border-left: 3.5px solid #06b6d4 !important; }
    .metric-card-red    { background: linear-gradient(180deg, #ffffff 0%, #fef2f2 100%); border-left: 3.5px solid #ef4444 !important; }

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .custom-analytics-table {
        width: 100%;
        margin-bottom: 0;
    }
    .custom-analytics-table thead th {
        font-size: 10px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 8px;
        white-space: nowrap;
    }
    .custom-analytics-table tbody td {
        padding: 8px 8px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        font-size: 11px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .custom-analytics-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .custom-analytics-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .custom-analytics-table th,
        .custom-analytics-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 7 Columns Proportional Distribution Summing to Exactly 100% */
        .col-an-id     { width: 4% !important; }
        .col-an-user   { width: 22% !important; }
        .col-an-type   { width: 12% !important; }
        .col-an-source { width: 22% !important; }
        .col-an-url    { width: 16% !important; }
        .col-an-ip     { width: 10% !important; }
        .col-an-date   { width: 14% !important; }
    }

    /* Form Controls */
    #email-marketing-analytics-root .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #email-marketing-analytics-root .form-control,
    #email-marketing-analytics-root .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #email-marketing-analytics-root .form-control:focus,
    #email-marketing-analytics-root .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
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
    .card-footer nav div:has(> p),
    .card-footer nav > div.d-none > div:first-child,
    .card-footer nav .d-none.flex-sm-fill > div:first-child {
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
    .card-footer nav svg {
        width: 10px !important;
        height: 10px !important;
    }

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .analytics-header-wrap h5 {
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
        .custom-analytics-table {
            min-width: 820px !important;
            table-layout: auto !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-analytics-root">

    {{-- 1. Header & Navigation (Original Clean Light Theme) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 analytics-header-wrap">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-chart-line text-primary"></i>
                <span>Email Analytics &amp; Tracking Events</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Audit log of opens, clicks, pixel pings, and delivery telemetry[cite: 22].
            </small>
        </div>

        <div class="d-flex gap-2 top-action-btns">
            <a href="{{ route('admin.email_marketing.analytics.export') }}" class="btn btn-sm btn-outline-success btn-compact bg-white shadow-xs fw-semibold">
                <i class="fas fa-file-csv me-1 text-success"></i> <span>Export Event Logs</span>[cite: 22]
            </a>
            <a href="{{ route('admin.email_marketing.dashboard') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-chart-pie me-1"></i> <span>Dashboard</span>[cite: 22]
            </a>
        </div>
    </div>

    {{-- 2. Engagement Telemetry Overview (Collapsible Section) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-chart-pie text-primary me-1"></i> Real-Time Telemetry &amp; Engagement Metrics
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseAnalyticsKpis" aria-expanded="true" aria-controls="collapseAnalyticsKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseAnalyticsKpis">
            <div class="p-3">
                <div class="row g-2">
                    {{-- Total Tracking Events --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Total Telemetry</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                                    <i class="fas fa-bolt"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    {{ number_format($totalEventsCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Total tracking pings[cite: 22]</small>
                            </div>
                        </div>
                    </div>

                    {{-- Verified Opens --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Verified Opens</span>
                                <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                                    <i class="fas fa-envelope-open"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num">
                                    {{ number_format($openEventsCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Pixel pings recorded</small>
                            </div>
                        </div>
                    </div>

                    {{-- Link Click Events --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-cyan h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Link Clicks</span>
                                <div class="metric-icon-square bg-info-subtle text-info shadow-xs">
                                    <i class="fas fa-mouse-pointer"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-info bn-digit font-monospace stat-val-num">
                                    {{ number_format($clickEventsCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Store clicks tracked</small>
                            </div>
                        </div>
                    </div>

                    {{-- Unsubscribe Opt-outs --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-red h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Opt-outs</span>
                                <div class="metric-icon-square bg-danger-subtle text-danger shadow-xs">
                                    <i class="fas fa-user-xmark"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-danger bn-digit font-monospace stat-val-num">
                                    {{ number_format($unsubEventsCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Unsubscribe clicks[cite: 22]</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Live Search & Event Type Filter Toolbar (Collapsible) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-filter text-primary me-1"></i> Filter Events by Email, Source &amp; Event Type
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseAnalyticsFilter" aria-expanded="true" aria-controls="collapseAnalyticsFilter" title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseAnalyticsFilter">
            <div class="p-3">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-5">
                        <label class="form-label" for="analyticsSearchInput">
                            <i class="fas fa-search me-1 text-primary"></i> Search Email, Source, or IP Address
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                            <input type="text" id="analyticsSearchInput" class="form-control border-start-0 ps-0 shadow-none" placeholder="e.g. user@example.com, 192.168.1.1, Campaign..." onkeyup="filterAnalyticsTable()">
                        </div>
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label" for="analyticsTypeFilter">
                            <i class="fas fa-tag me-1 text-success"></i> Telemetry Event Type
                        </label>
                        <select id="analyticsTypeFilter" class="form-select shadow-none font-monospace" onchange="filterAnalyticsTable()">
                            <option value="all" selected>All Event Types</option>
                            <option value="open">Open (Pixel Ping)</option>
                            <option value="click">Click (Link Tracking)</option>
                            <option value="unsubscribe">Unsubscribe Request</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <button type="button" class="btn btn-light border btn-compact w-100 shadow-xs fw-semibold text-secondary" onclick="resetAnalyticsFilters()">
                            <i class="fas fa-rotate-left me-1"></i> <span>Reset Filters</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Events Table Card (Desktop 100% Fit Screen with Zero Horizontal Scroll) --}}
    <div class="card border-0 shadow-sm overflow-hidden mb-3">
        <div class="p-2 px-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 bg-light bg-opacity-25">
            <div>
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13px;">
                    <i class="fas fa-list-check text-primary"></i> <span>Recent Engagement &amp; Telemetry Logs</span>[cite: 22]
                </h6>
                <small class="text-muted font-monospace bn-digit" style="font-size: 10px;">
                    Showing {{ $events->firstItem() ?? 0 }} to {{ $events->lastItem() ?? 0 }} of {{$events->total() }} events[cite: 22]
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 10px;" id="visible-events-count">
                    {{ $events->total() }} Logged[cite: 22]
                </span>
                <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseAnalyticsTable" aria-expanded="true" aria-controls="collapseAnalyticsTable" title="Toggle Table">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseAnalyticsTable">
            <div class="table-responsive pc-fit-container">
                <table class="table align-middle mb-0 custom-analytics-table" id="analytics-table">
                    <thead>
                        <tr>
                            <th class="ps-3 col-an-id text-center">#ID</th>[cite: 22]
                            <th class="col-an-user">Recipient Email</th>[cite: 22]
                            <th class="col-an-type">Event Type</th>[cite: 22]
                            <th class="col-an-source">Broadcast / Journey Source</th>[cite: 22]
                            <th class="col-an-url">Target URL / Data</th>[cite: 22]
                            <th class="col-an-ip">IP Address</th>[cite: 22]
                            <th class="pe-3 col-an-date">Timestamp</th>[cite: 22]
                        </tr>
                    </thead>
                    <tbody id="analytics-tbody">
                        @forelse($events as $event)
                            @php
                                $badgeClass = match(strtolower($event->event_type ?? '')) {
                                    'open'        => 'bg-success-subtle text-success border border-success-subtle',
                                    'click'       => 'bg-primary-subtle text-primary border border-primary-subtle',
                                    'unsubscribe' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    default       => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                };

                                $sourceName = $event->campaign->name ?? ($event->automation->name ?? 'Direct / System');

                                $rowSearchData = strtolower(
                                    ($event->email ?? '') . ' ' .
                                    ($event->event_type ?? '') . ' ' .
                                    $sourceName . ' ' .
                                    ($event->target_url ?? '') . ' ' .
                                    ($event->ip_address ?? '')
                                );
                            @endphp
                            <tr class="event-entry-row" data-search="{{ $rowSearchData }}" data-type="{{ strtolower($event->event_type ?? '') }}">
                                <td class="ps-3 col-an-id text-center font-monospace bn-digit text-muted" style="font-size: 11px;">
                                    {{ $event->id }}[cite: 22]
                                </td>
                                <td class="col-an-user">
                                    <div class="min-w-0" style="line-height: 1.3;">
                                        <strong class="text-dark d-block text-truncate" style="font-size: 12px;" title="{{ $event->email }}">
                                            {{ $event->email }}[cite: 22]
                                        </strong>
                                        <small class="text-muted font-monospace d-block text-truncate" style="font-size: 10px;">
                                            Audit ID: #{{ $event->id }}[cite: 22]
                                        </small>
                                    </div>
                                </td>
                                <td class="col-an-type">
                                    <span class="badge {{ $badgeClass }} font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 9.5px;">
                                        @if(strtolower($event->event_type ?? '') === 'open')
                                            <i class="fas fa-envelope-open me-1"></i>[cite: 22]
                                        @elseif(strtolower($event->event_type ?? '') === 'click')
                                            <i class="fas fa-mouse-pointer me-1"></i>[cite: 22]
                                        @elseif(strtolower($event->event_type ?? '') === 'unsubscribe')
                                            <i class="fas fa-user-xmark me-1"></i>[cite: 22]
                                        @else
                                            <i class="fas fa-bolt me-1"></i>
                                        @endif
                                        {{ strtoupper($event->event_type) }}[cite: 22]
                                    </span>
                                </td>
                                <td class="col-an-source">
                                    <div class="min-w-0" style="line-height: 1.3;">
                                        @if($event->campaign)
                                            <a href="{{ route('admin.email_marketing.campaigns.show', $event->campaign->id) }}" class="text-decoration-none fw-bold text-primary d-block text-truncate" style="font-size: 12px;" title="{{ $event->campaign->name }}">
                                                <i class="fas fa-paper-plane me-1"></i> {{ $event->campaign->name }}[cite: 22]
                                            </a>
                                            <small class="text-muted font-monospace d-block text-truncate" style="font-size: 10px;">Broadcast Campaign</small>
                                        @elseif($event->automation)
                                            <span class="text-dark fw-semibold d-block text-truncate" style="font-size: 12px;" title="{{ $event->automation->name }}">
                                                <i class="fas fa-diagram-project text-secondary me-1"></i> {{ $event->automation->name }}[cite: 22]
                                            </span>
                                            <small class="text-muted font-monospace d-block text-truncate" style="font-size: 10px;">Drip Workflow</small>
                                        @else
                                            <span class="text-muted font-monospace small" style="font-size: 11px;">Direct / System</span>[cite: 22]
                                        @endif
                                    </div>
                                </td>
                                <td class="col-an-url">
                                    @if($event->target_url)
                                        <button type="button" class="btn btn-sm btn-light border p-1 text-primary shadow-xs d-inline-flex align-items-center gap-1 text-truncate" style="max-width: 100%; font-size: 10.5px; border-radius: 5px !important;" onclick="showUrlDetailModal('{{ addslashes($event->email) }}', '{{ strtoupper($event->event_type) }}', '{{ addslashes($event->target_url) }}', '{{$event->ip_address ?? 'N/A' }}')" title="Click to view full destination URL">
                                            <i class="fas fa-link flex-shrink-0" style="font-size: 9.5px;"></i>
                                            <span class="text-truncate font-monospace">{{ $event->target_url }}</span>[cite: 22]
                                        </button>
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 11px;">—</span>[cite: 22]
                                    @endif
                                </td>
                                <td class="col-an-ip">
                                    <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 10px;">
                                        {{ $event->ip_address ?? '—' }}[cite: 22]
                                    </span>
                                </td>
                                <td class="pe-3 col-an-date font-monospace bn-digit" style="line-height: 1.3;">
                                    <span class="text-dark d-block" style="font-size: 11px;">{{ $event->created_at->format('M d, Y') }}</span>[cite: 22]
                                    <small class="text-muted" style="font-size: 9.5px;">{{ $event->created_at->format('h:i:s A') }}</small>[cite: 22]
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyEventsRow">
                                <td colspan="7" class="text-center py-5 text-muted small">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block text-secondary opacity-25"></i>[cite: 22]
                                    <span class="fw-semibold">No tracking events recorded yet. Open and click events will appear here in real-time.</span>[cite: 22]
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Bulletproof Responsive Pagination Footer --}}
            @if($events->hasPages())
                <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">
                    <small class="text-muted font-monospace bn-digit" style="font-size: 11px;">
                        Showing {{ $events->firstItem() }} to {{ $events->lastItem() }} of {{$events->total() }} events[cite: 22]
                    </small>
                    <div>
                        {{ $events->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: URL & Telemetry Lightbox Modal (Zero Cut-off on Mobile) -->
<div class="modal fade analytics-modal-scope" id="telemetryDetailModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg p-3">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 14px;">
                    <i class="fas fa-circle-info text-primary"></i> <span>Telemetry Event Details</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2 pt-3">
                <div class="mb-2 p-2 bg-light border" style="border-radius: 5px;">
                    <small class="text-muted d-block font-monospace" style="font-size: 10px;">RECIPIENT &amp; EVENT TYPE</small>
                    <div class="d-flex justify-content-between align-items-center">
                        <strong class="text-dark" id="modalEventRecipient" style="font-size: 12.5px;">user@example.com</strong>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace" id="modalEventTypeBadge" style="font-size: 9.5px;">OPEN</span>
                    </div>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block font-monospace mb-1" style="font-size: 10px;">CLIENT IP ADDRESS</small>
                    <div class="p-2 bg-light border font-monospace text-dark" style="border-radius: 5px; font-size: 11px;" id="modalEventIpAddress">
                        127.0.0.1
                    </div>
                </div>
                <div>
                    <small class="text-muted d-block font-monospace mb-1" style="font-size: 10px;">DESTINATION / TARGET URL</small>
                    <div class="p-2 bg-light border font-monospace text-break" style="border-radius: 5px; font-size: 11px; max-height: 180px; overflow-y: auto;" id="modalEventTargetUrl">
                        https://shopnowbd.online/...
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 d-flex justify-content-end">
                <button type="button" class="btn btn-dark btn-compact px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showUrlDetailModal(email, type, targetUrl, ipAddress) {
        document.getElementById('modalEventRecipient').innerText = email;
        document.getElementById('modalEventTypeBadge').innerText = type;
        document.getElementById('modalEventIpAddress').innerText = ipAddress;
        document.getElementById('modalEventTargetUrl').innerText = targetUrl || '—';

        const modalEl = document.getElementById('telemetryDetailModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function filterAnalyticsTable() {
        const query = (document.getElementById('analyticsSearchInput')?.value || '').toLowerCase().trim();
        const type  = (document.getElementById('analyticsTypeFilter')?.value || 'all').toLowerCase();

        const rows = document.querySelectorAll('.event-entry-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowSearch = row.getAttribute('data-search') || '';
            const rowType   = row.getAttribute('data-type') || '';

            const matchesQuery = !query || rowSearch.includes(query);
            const matchesType  = type === 'all' || rowType === type;

            if (matchesQuery && matchesType) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const countBadge = document.getElementById('visible-events-count');
        if (countBadge) {
            countBadge.innerText = `${visibleCount} Showing`;
        }
    }

    function resetAnalyticsFilters() {
        const searchInput = document.getElementById('analyticsSearchInput');
        const typeFilter  = document.getElementById('analyticsTypeFilter');
        if (searchInput) searchInput.value = '';
        if (typeFilter)  typeFilter.value = 'all';
        filterAnalyticsTable();
    }
</script>
@endsection