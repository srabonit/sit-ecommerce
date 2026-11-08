
@extends('layouts.admin')

@section('title', 'Email Analytics & Events - Email Marketing')

@php
    $events = $events ?? collect();

    $totalEventsCount = method_exists($events, 'total')
        ? $events->total()
        : $events->count();

    $hasTrackingModel = class_exists(\App\Models\EmailTrackingEvent::class);

    $openEventsCount = $hasTrackingModel
        ? \App\Models\EmailTrackingEvent::where('event_type', 'open')->count()
        : $events->where('event_type', 'open')->count();

    $clickEventsCount = $hasTrackingModel
        ? \App\Models\EmailTrackingEvent::where('event_type', 'click')->count()
        : $events->where('event_type', 'click')->count();

    $unsubEventsCount = $hasTrackingModel
        ? \App\Models\EmailTrackingEvent::where('event_type', 'unsubscribe')->count()
        : $events->where('event_type', 'unsubscribe')->count();

    $exportUrl = Route::has('admin.email_marketing.analytics.export')
        ? route('admin.email_marketing.analytics.export')
        : url('admin/email-marketing/analytics/export');

    $dashboardUrl = Route::has('admin.email_marketing.dashboard')
        ? route('admin.email_marketing.dashboard')
        : url('admin/email-marketing/dashboard');

    $firstItem = method_exists($events, 'firstItem')
        ? ($events->firstItem() ?? 0)
        : ($events->count() ? 1 : 0);

    $lastItem = method_exists($events, 'lastItem')
        ? ($events->lastItem() ?? 0)
        : $events->count();
@endphp

@section('admin_content')

<style>
    /* =========================================================
       EMAIL ANALYTICS - MODERN ADMIN UI
    ========================================================= */

    #email-marketing-analytics-root {
        --an-primary: #2563eb;
        --an-primary-soft: #eff6ff;
        --an-success: #059669;
        --an-success-soft: #ecfdf5;
        --an-info: #0891b2;
        --an-info-soft: #ecfeff;
        --an-danger: #dc2626;
        --an-danger-soft: #fef2f2;
        --an-dark: #0f172a;
        --an-muted: #64748b;
        --an-border: #e2e8f0;
        --an-surface: #ffffff;
        --an-soft: #f8fafc;

        color: var(--an-dark);
        font-size: 13px;
    }

    /* Global 5px Radius */
    #email-marketing-analytics-root *,
    #telemetryDetailModal * {
        border-radius: 5px;
    }

    #email-marketing-analytics-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #email-marketing-analytics-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    #email-marketing-analytics-root .card,
    #telemetryDetailModal .modal-content {
        border: 1px solid var(--an-border) !important;
        background: var(--an-surface);
        box-shadow: 0 1px 3px rgba(15, 23, 42, .045);
    }

    #email-marketing-analytics-root .card {
        margin-bottom: 15px;
    }

    /* Header */
    .analytics-header-wrap {
        gap: 12px;
    }

    .analytics-page-title {
        font-size: 18px;
        font-weight: 750;
        letter-spacing: -.35px;
        color: var(--an-dark);
    }

    .analytics-page-description {
        font-size: 12px;
        color: var(--an-muted);
        margin-top: 5px;
        line-height: 1.5;
    }

    .analytics-total-badge {
        font-size: 10px;
        font-weight: 700;
        padding: 5px 9px;
        background: var(--an-primary-soft);
        color: var(--an-primary);
        border: 1px solid #dbeafe;
    }

    /* Buttons */
    #email-marketing-analytics-root .btn,
    #telemetryDetailModal .btn {
        border-radius: 5px !important;
        transition: all .18s ease;
    }

    .btn-compact {
        min-height: 32px;
        padding: 6px 12px !important;
        font-size: 11.5px !important;
        line-height: 1.25 !important;
        font-weight: 650 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        white-space: nowrap;
    }

    .btn-compact:hover {
        transform: translateY(-1px);
    }

    .top-action-btns {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    /* Section Header */
    .section-header-wrap {
        padding: 11px 15px;
        min-height: 43px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fff;
        border-bottom: 1px solid var(--an-border);
    }

    .section-header-title {
        font-size: 12px;
        font-weight: 750;
        color: var(--an-dark);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-header-title i {
        font-size: 13px;
        color: var(--an-primary);
    }

    /* Collapse Button */
    .collapse-toggle-btn {
        width: 25px;
        height: 25px;
        padding: 0;
        border: 1px solid #dbe3ed;
        background: #fff;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        cursor: pointer;
        transition: all .2s ease;
    }

    .collapse-toggle-btn:hover {
        color: var(--an-primary);
        border-color: var(--an-primary);
        background: var(--an-primary-soft);
    }

    .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    .collapse-icon {
        font-size: 10px;
        transition: transform .2s ease;
    }

    /* =========================================================
       METRIC CARDS
    ========================================================= */

    .metric-card {
        height: 100%;
        min-height: 100px;
        padding: 13px 14px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        border: 1px solid var(--an-border) !important;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(15, 23, 42, .07);
    }

    .metric-card-blue {
        background: linear-gradient(135deg, #fff 0%, #f0f6ff 100%);
        border-left: 3px solid #2563eb !important;
    }

    .metric-card-green {
        background: linear-gradient(135deg, #fff 0%, #effdf6 100%);
        border-left: 3px solid #10b981 !important;
    }

    .metric-card-cyan {
        background: linear-gradient(135deg, #fff 0%, #effcff 100%);
        border-left: 3px solid #06b6d4 !important;
    }

    .metric-card-red {
        background: linear-gradient(135deg, #fff 0%, #fff3f3 100%);
        border-left: 3px solid #ef4444 !important;
    }

    .stat-label-title {
        font-size: 11px;
        font-weight: 750;
        color: #64748b;
        letter-spacing: .2px;
    }

    .stat-val-num {
        font-size: 21px;
        line-height: 1.3;
        font-weight: 800;
        letter-spacing: -.5px;
        margin: 5px 0 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .stat-sub-text {
        font-size: 10px;
        color: #94a3b8;
        margin-top: 3px;
    }

    .metric-icon-square {
        width: 34px;
        height: 34px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    /* =========================================================
       FILTER AREA
    ========================================================= */

    .analytics-filter-control {
        height: 35px !important;
        padding: 6px 10px !important;
        font-size: 12px !important;
        color: #1e293b !important;
        border: 1px solid #cbd5e1 !important;
        background: #fff !important;
        box-shadow: none !important;
    }

    .analytics-filter-control:focus {
        border-color: var(--an-primary) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10) !important;
        outline: none;
    }

    .analytics-addon-text {
        height: 35px !important;
        padding: 0 11px !important;
        background: #f8fafc !important;
        border: 1px solid #cbd5e1 !important;
        border-right: 0 !important;
        color: #64748b !important;
        font-size: 11px;
    }

    #email-marketing-analytics-root .form-label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #475569;
        margin-bottom: 6px;
    }

    /* =========================================================
       TABLE
    ========================================================= */

    .analytics-table-card {
        overflow: hidden;
    }

    .analytics-table-toolbar {
        min-height: 58px;
        padding: 10px 15px;
        background: #fff;
        border-bottom: 1px solid var(--an-border);
    }

    .analytics-table-title {
        font-size: 13px;
        font-weight: 750;
        color: var(--an-dark);
        margin: 0;
    }

    .analytics-table-subtitle {
        font-size: 10px;
        color: #94a3b8;
        margin-top: 4px;
    }

    .custom-analytics-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .custom-analytics-table thead th {
        padding: 11px 9px;
        background: #f8fafc;
        color: #64748b;
        font-size: 10px;
        font-weight: 750;
        letter-spacing: .35px;
        text-transform: uppercase;
        white-space: nowrap;
        border-bottom: 1px solid var(--an-border);
        vertical-align: middle;
    }

    .custom-analytics-table tbody td {
        padding: 10px 9px;
        font-size: 11.5px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        white-space: nowrap;
    }

    .custom-analytics-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .custom-analytics-table tbody tr:hover td {
        background: #f8fbff;
    }

    .custom-analytics-table .event-entry-row {
        transition: background .15s ease;
    }

    .event-email {
        font-size: 11.5px;
        font-weight: 700;
        color: #1e293b;
        display: block;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .event-secondary-text {
        display: block;
        margin-top: 3px;
        font-size: 9.5px;
        color: #94a3b8;
    }

    .event-type-badge {
        padding: 5px 8px !important;
        font-size: 9px !important;
        font-weight: 750 !important;
        letter-spacing: .2px;
        border: 1px solid transparent;
    }

    .event-url-button {
        max-width: 100%;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 8px !important;
        font-size: 10px !important;
        overflow: hidden;
    }

    .event-url-button span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .event-ip-badge {
        padding: 5px 7px !important;
        font-size: 9.5px !important;
        font-weight: 600;
        background: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    .event-date-main {
        display: block;
        font-size: 10.5px;
        font-weight: 700;
        color: #334155;
    }

    .event-date-time {
        display: block;
        margin-top: 3px;
        font-size: 9px;
        color: #94a3b8;
    }

    /* Desktop Table Fit */
    @media (min-width: 992px) {
        .pc-fit-container {
            overflow-x: hidden !important;
        }

        .custom-analytics-table {
            table-layout: fixed !important;
        }

        .custom-analytics-table th,
        .custom-analytics-table td {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .col-an-id     { width: 5%; }
        .col-an-user   { width: 23%; }
        .col-an-type   { width: 11%; }
        .col-an-source { width: 22%; }
        .col-an-url    { width: 16%; }
        .col-an-ip     { width: 10%; }
        .col-an-date   { width: 13%; }
    }

    /* =========================================================
       PAGINATION
    ========================================================= */

    .analytics-pagination-footer {
        padding: 11px 15px;
        background: #f8fafc;
        border-top: 1px solid var(--an-border);
    }

    .analytics-pagination-footer nav {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        margin: 0;
    }

    .analytics-pagination-footer .pagination {
        margin: 0;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }

    .analytics-pagination-footer .page-link,
    .analytics-pagination-footer .page-item span {
        min-width: 27px;
        height: 27px;
        padding: 0 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #dbe3ed;
        border-radius: 5px !important;
        background: #fff;
        color: #475569;
        font-size: 10px;
        font-weight: 650;
        text-decoration: none;
    }

    .analytics-pagination-footer .page-item.active .page-link,
    .analytics-pagination-footer .page-item.active span {
        background: var(--an-primary);
        border-color: var(--an-primary);
        color: #fff;
    }

    .analytics-pagination-footer .page-item.disabled .page-link,
    .analytics-pagination-footer .page-item.disabled span {
        opacity: .4;
        pointer-events: none;
    }

    /* =========================================================
       MODAL
    ========================================================= */

    #telemetryDetailModal {
        z-index: 106500;
    }

    #telemetryDetailModal .modal-content {
        border-radius: 5px !important;
        overflow: hidden;
    }

    #telemetryDetailModal .modal-header {
        padding: 15px 16px 10px;
    }

    #telemetryDetailModal .modal-body {
        padding: 12px 16px;
    }

    #telemetryDetailModal .modal-footer {
        padding: 5px 16px 15px;
    }

    .telemetry-detail-label {
        display: block;
        font-size: 9px;
        font-weight: 750;
        letter-spacing: .5px;
        color: #94a3b8;
        margin-bottom: 6px;
    }

    .telemetry-detail-value {
        padding: 10px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #334155;
        font-size: 11px;
        overflow-wrap: anywhere;
    }

    .telemetry-recipient {
        font-size: 12px;
        font-weight: 750;
        color: #1e293b;
        overflow-wrap: anywhere;
    }

    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 991.98px) {
        .pc-fit-container {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        .custom-analytics-table {
            min-width: 900px;
            table-layout: auto;
        }
    }

    @media (max-width: 767.98px) {
        #email-marketing-analytics-root {
            padding-top: 12px !important;
        }

        .analytics-header-wrap {
            align-items: stretch !important;
        }

        .analytics-page-title {
            font-size: 16px;
        }

        .analytics-page-description {
            font-size: 11px;
        }

        .top-action-btns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 7px;
        }

        .top-action-btns .btn {
            width: 100%;
            min-height: 35px;
            font-size: 10.5px !important;
            padding: 7px 5px !important;
        }

        .metric-card {
            min-height: 87px;
            padding: 10px !important;
        }

        .stat-label-title {
            font-size: 9.5px;
        }

        .stat-val-num {
            font-size: 17px;
        }

        .stat-sub-text {
            font-size: 9px;
        }

        .metric-icon-square {
            width: 29px;
            height: 29px;
            font-size: 11px;
        }

        .section-header-wrap {
            padding: 10px 12px;
        }

        .section-header-title {
            font-size: 11px;
        }

        .analytics-filter-control,
        .analytics-addon-text {
            height: 36px !important;
        }

        .analytics-table-toolbar {
            padding: 10px 12px;
        }

        .analytics-pagination-footer {
            padding: 10px;
        }

        .analytics-pagination-footer > div {
            width: 100%;
            text-align: center;
        }

        .analytics-pagination-footer nav {
            justify-content: center;
        }

        #email-marketing-analytics-root .card {
            margin-bottom: 12px;
        }
    }

    @media (max-width: 380px) {
        .analytics-page-title {
            font-size: 14px;
        }

        .metric-card {
            padding: 8px !important;
        }

        .stat-val-num {
            font-size: 15px;
        }

        .metric-icon-square {
            width: 26px;
            height: 26px;
        }

        .top-action-btns .btn {
            font-size: 9.5px !important;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #email-marketing-analytics-root *,
        #telemetryDetailModal * {
            transition: none !important;
            animation: none !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative"
     id="email-marketing-analytics-root">

    {{-- =====================================================
         1. PAGE HEADER
    ====================================================== --}}

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 analytics-header-wrap">

        <div class="min-w-0">
            <div class="d-flex align-items-center flex-wrap gap-2">

                <h5 class="analytics-page-title mb-0 d-flex align-items-center gap-2">
                    <i class="fas fa-chart-line text-primary"></i>
                    <span>Email Analytics &amp; Tracking Events</span>
                </h5>

                <span class="badge analytics-total-badge font-monospace"
                      id="analytics-total-badge">
                    {{ number_format($totalEventsCount) }} Events
                </span>

            </div>

            <p class="analytics-page-description mb-0">
                Monitor email opens, link clicks, unsubscribe activity and tracking events.
            </p>
        </div>

        <div class="top-action-btns">

            <a href="{{ $exportUrl }}"
               class="btn btn-outline-success btn-compact bg-white"
               title="Export event logs to CSV">

                <i class="fas fa-file-csv"></i>
                <span>Export Event Logs</span>
            </a>

            <a href="{{ $dashboardUrl }}"
               class="btn btn-outline-secondary btn-compact bg-white">

                <i class="fas fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>

        </div>
    </div>


    {{-- =====================================================
         2. ENGAGEMENT METRICS
    ====================================================== --}}

    <div class="card mb-3">

        <div class="section-header-wrap">

            <div class="section-header-title">
                <i class="fas fa-chart-pie"></i>
                <span>Engagement Overview &amp; Telemetry Metrics</span>
            </div>

            <button type="button"
                    class="collapse-toggle-btn no-print"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseAnalyticsKpis"
                    aria-expanded="true"
                    aria-controls="collapseAnalyticsKpis"
                    title="Toggle Metrics">

                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>

        </div>

        <div class="collapse show" id="collapseAnalyticsKpis">

            <div class="p-3">

                <div class="row g-2">

                    {{-- Total Events --}}
                    <div class="col-6 col-xl-3">

                        <div class="metric-card metric-card-blue">

                            <div class="d-flex align-items-center justify-content-between gap-2">

                                <span class="stat-label-title text-truncate">
                                    Total Telemetry
                                </span>

                                <div class="metric-icon-square bg-primary-subtle text-primary">
                                    <i class="fas fa-bolt"></i>
                                </div>

                            </div>

                            <div>
                                <h4 class="stat-val-num text-dark font-monospace">
                                    {{ number_format($totalEventsCount) }}
                                </h4>

                                <small class="stat-sub-text">
                                    Total tracking events
                                </small>
                            </div>

                        </div>

                    </div>


                    {{-- Open Events --}}
                    <div class="col-6 col-xl-3">

                        <div class="metric-card metric-card-green">

                            <div class="d-flex align-items-center justify-content-between gap-2">

                                <span class="stat-label-title text-truncate">
                                    Verified Opens
                                </span>

                                <div class="metric-icon-square bg-success-subtle text-success">
                                    <i class="fas fa-envelope-open"></i>
                                </div>

                            </div>

                            <div>
                                <h4 class="stat-val-num text-success font-monospace">
                                    {{ number_format($openEventsCount) }}
                                </h4>

                                <small class="stat-sub-text">
                                    Recorded pixel pings
                                </small>
                            </div>

                        </div>

                    </div>


                    {{-- Click Events --}}
                    <div class="col-6 col-xl-3">

                        <div class="metric-card metric-card-cyan">

                            <div class="d-flex align-items-center justify-content-between gap-2">

                                <span class="stat-label-title text-truncate">
                                    Link Clicks
                                </span>

                                <div class="metric-icon-square bg-info-subtle text-info">
                                    <i class="fas fa-mouse-pointer"></i>
                                </div>

                            </div>

                            <div>
                                <h4 class="stat-val-num text-info font-monospace">
                                    {{ number_format($clickEventsCount) }}
                                </h4>

                                <small class="stat-sub-text">
                                    Tracked link clicks
                                </small>
                            </div>

                        </div>

                    </div>


                    {{-- Unsubscribe Events --}}
                    <div class="col-6 col-xl-3">

                        <div class="metric-card metric-card-red">

                            <div class="d-flex align-items-center justify-content-between gap-2">

                                <span class="stat-label-title text-truncate">
                                    Opt-outs
                                </span>

                                <div class="metric-icon-square bg-danger-subtle text-danger">
                                    <i class="fas fa-user-xmark"></i>
                                </div>

                            </div>

                            <div>
                                <h4 class="stat-val-num text-danger font-monospace">
                                    {{ number_format($unsubEventsCount) }}
                                </h4>

                                <small class="stat-sub-text">
                                    Unsubscribe events
                                </small>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =====================================================
         3. SEARCH & FILTERS
    ====================================================== --}}

    <div class="card mb-3">

        <div class="section-header-wrap">

            <div class="section-header-title">
                <i class="fas fa-filter"></i>
                <span>Search &amp; Filter Tracking Events</span>
            </div>

            <button type="button"
                    class="collapse-toggle-btn no-print"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseAnalyticsFilter"
                    aria-expanded="true"
                    aria-controls="collapseAnalyticsFilter"
                    title="Toggle Filters">

                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>

        </div>

        <div class="collapse show" id="collapseAnalyticsFilter">

            <div class="p-3">

                <form id="analyticsFilterForm" onsubmit="return false;">

                    <div class="row g-3 align-items-end">

                        {{-- Search --}}
                        <div class="col-12 col-md-5">

                            <label class="form-label" for="analyticsSearchInput">
                                <i class="fas fa-search text-primary me-1"></i>
                                Search Events
                            </label>

                            <div class="input-group">

                                <span class="input-group-text analytics-addon-text">
                                    <i class="fas fa-search"></i>
                                </span>

                                <input type="text"
                                       id="analyticsSearchInput"
                                       class="form-control analytics-filter-control"
                                       placeholder="Email, campaign, URL or IP address..."
                                       autocomplete="off"
                                       oninput="filterAnalyticsTable()">

                            </div>

                        </div>


                        {{-- Event Type --}}
                        <div class="col-12 col-sm-6 col-md-4">

                            <label class="form-label" for="analyticsTypeFilter">
                                <i class="fas fa-tag text-success me-1"></i>
                                Event Type
                            </label>

                            <select id="analyticsTypeFilter"
                                    class="form-select analytics-filter-control"
                                    onchange="filterAnalyticsTable()">

                                <option value="all">All Event Types</option>
                                <option value="open">Open (Pixel Ping)</option>
                                <option value="click">Click (Link Tracking)</option>
                                <option value="unsubscribe">Unsubscribe Request</option>

                            </select>

                        </div>


                        {{-- Reset --}}
                        <div class="col-12 col-sm-6 col-md-3">

                            <button type="button"
                                    class="btn btn-light border btn-compact w-100"
                                    onclick="resetAnalyticsFilters()">

                                <i class="fas fa-rotate-left"></i>
                                Reset Filters

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>


    {{-- =====================================================
         4. TRACKING EVENTS TABLE
    ====================================================== --}}

    <div class="card analytics-table-card mb-3">

        {{-- Table Header --}}
        <div class="analytics-table-toolbar d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">

            <div class="min-w-0">

                <h6 class="analytics-table-title">
                    <i class="fas fa-list-check text-primary me-1"></i>
                    Recent Engagement &amp; Telemetry Logs
                </h6>

                <div class="analytics-table-subtitle font-monospace"
                     id="showing-entries-text">

                    Showing {{ $firstItem }}
                    to {{ $lastItem }}
                    of {{ number_format($totalEventsCount) }} events

                </div>

            </div>

            <div class="d-flex align-items-center gap-2">

                <span class="badge bg-light text-secondary border px-2 py-2 font-monospace"
                      id="visible-events-count">

                    {{ number_format($totalEventsCount) }} Logged

                </span>

                <button type="button"
                        class="collapse-toggle-btn no-print"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseAnalyticsTable"
                        aria-expanded="true"
                        aria-controls="collapseAnalyticsTable"
                        title="Toggle Table">

                    <i class="fas fa-chevron-down collapse-icon"></i>

                </button>

            </div>

        </div>


        <div class="collapse show" id="collapseAnalyticsTable">

            <div class="table-responsive pc-fit-container">

                <table class="table custom-analytics-table"
                       id="analytics-table">

                    <thead>
                        <tr>
                            <th class="ps-3 col-an-id text-center">#ID</th>
                            <th class="col-an-user">Recipient Email</th>
                            <th class="col-an-type">Event Type</th>
                            <th class="col-an-source">Broadcast / Journey Source</th>
                            <th class="col-an-url">Target URL / Data</th>
                            <th class="col-an-ip">IP Address</th>
                            <th class="pe-3 col-an-date">Timestamp</th>
                        </tr>
                    </thead>

                    <tbody id="analytics-tbody">

                        @forelse($events as $event)

                            @php
                                $eventType = strtolower($event->event_type ?? '');

                                $badgeClass = match($eventType) {
                                    'open' => 'bg-success-subtle text-success border-success-subtle',
                                    'click' => 'bg-primary-subtle text-primary border-primary-subtle',
                                    'unsubscribe' => 'bg-danger-subtle text-danger border-danger-subtle',
                                    default => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                                };

                                $sourceName = $event->campaign->name
                                    ?? ($event->automation->name ?? 'Direct / System');

                                $rowSearchData = strtolower(
                                    ($event->email ?? '') . ' ' .
                                    $eventType . ' ' .
                                    $sourceName . ' ' .
                                    ($event->target_url ?? '') . ' ' .
                                    ($event->ip_address ?? '')
                                );

                                $campViewUrl = $event->campaign
                                    ? url('admin/email-marketing/campaigns/view/' . $event->campaign->id)
                                    : '#';

                                $eventEmail = $event->email ?? 'Unknown recipient';
                                $targetUrl = $event->target_url ?? '';
                                $ipAddress = $event->ip_address ?? 'N/A';
                                $createdAt = $event->created_at;
                            @endphp

                            <tr class="event-entry-row"
                                data-search="{{ $rowSearchData }}"
                                data-type="{{ $eventType }}">

                                {{-- ID --}}
                                <td class="ps-3 col-an-id text-center font-monospace text-muted">
                                    {{ $event->id }}
                                </td>


                                {{-- Recipient --}}
                                <td class="col-an-user">

                                    <div class="min-w-0">

                                        <span class="event-email"
                                              title="{{ $eventEmail }}">

                                            {{ $eventEmail }}

                                        </span>

                                        <small class="event-secondary-text font-monospace">
                                            Audit ID: #{{ $event->id }}
                                        </small>

                                    </div>

                                </td>


                                {{-- Event Type --}}
                                <td class="col-an-type">

                                    <span class="badge event-type-badge {{ $badgeClass }}">

                                        @if($eventType === 'open')
                                            <i class="fas fa-envelope-open me-1"></i>
                                        @elseif($eventType === 'click')
                                            <i class="fas fa-mouse-pointer me-1"></i>
                                        @elseif($eventType === 'unsubscribe')
                                            <i class="fas fa-user-xmark me-1"></i>
                                        @else
                                            <i class="fas fa-bolt me-1"></i>
                                        @endif

                                        {{ strtoupper($eventType ?: 'UNKNOWN') }}

                                    </span>

                                </td>


                                {{-- Source --}}
                                <td class="col-an-source">

                                    <div class="min-w-0">

                                        @if($event->campaign)

                                            <a href="{{ $campViewUrl }}"
                                               class="text-decoration-none fw-bold text-primary d-block text-truncate"
                                               style="font-size: 11.5px;"
                                               title="{{ $event->campaign->name }}">

                                                <i class="fas fa-paper-plane me-1"></i>
                                                {{ $event->campaign->name }}

                                            </a>

                                            <small class="event-secondary-text">
                                                Broadcast Campaign
                                            </small>

                                        @elseif($event->automation)

                                            <span class="text-dark fw-semibold d-block text-truncate"
                                                  style="font-size: 11.5px;"
                                                  title="{{ $event->automation->name }}">

                                                <i class="fas fa-diagram-project text-secondary me-1"></i>
                                                {{ $event->automation->name }}

                                            </span>

                                            <small class="event-secondary-text">
                                                Drip Workflow
                                            </small>

                                        @else

                                            <span class="text-muted font-monospace"
                                                  style="font-size: 10.5px;">

                                                Direct / System

                                            </span>

                                        @endif

                                    </div>

                                </td>


                                {{-- Target URL --}}
                                <td class="col-an-url">

                                    @if($targetUrl)

                                        <button type="button"
                                                class="btn btn-light border text-primary event-url-button"
                                                onclick="showUrlDetailModal(
                                                    {{ \Illuminate\Support\Js::from($eventEmail) }},
                                                    {{ \Illuminate\Support\Js::from(strtoupper($eventType)) }},
                                                    {{ \Illuminate\Support\Js::from($targetUrl) }},
                                                    {{ \Illuminate\Support\Js::from($ipAddress) }}
                                                )"
                                                title="View complete destination URL">

                                            <i class="fas fa-link flex-shrink-0"></i>

                                            <span class="font-monospace">
                                                {{ $targetUrl }}
                                            </span>

                                        </button>

                                    @else

                                        <span class="text-muted">—</span>

                                    @endif

                                </td>


                                {{-- IP Address --}}
                                <td class="col-an-ip">

                                    <span class="badge event-ip-badge font-monospace">
                                        {{ $event->ip_address ?? '—' }}
                                    </span>

                                </td>


                                {{-- Timestamp --}}
                                <td class="pe-3 col-an-date">

                                    <span class="event-date-main">
                                        {{ $createdAt ? $createdAt->format('M d, Y') : '—' }}
                                    </span>

                                    <small class="event-date-time">
                                        {{ $createdAt ? $createdAt->format('h:i:s A') : '' }}
                                    </small>

                                </td>

                            </tr>

                        @empty

                            <tr id="emptyEventsRow">

                                <td colspan="7" class="text-center py-5">

                                    <div class="mb-3">
                                        <i class="fas fa-inbox fa-3x text-secondary opacity-25"></i>
                                    </div>

                                    <h6 class="fw-bold text-dark mb-1" style="font-size: 12px;">
                                        No Tracking Events Found
                                    </h6>

                                    <p class="text-muted mb-0" style="font-size: 11px;">
                                        Email opens and clicks will appear here when tracking events are recorded.
                                    </p>

                                </td>

                            </tr>

                        @endforelse


                        {{-- Filter Empty State --}}
                        <tr id="noMatchEventsRow" style="display: none;">

                            <td colspan="7" class="text-center py-5">

                                <div class="mb-3">
                                    <i class="fas fa-filter-circle-xmark fa-2x text-secondary opacity-25"></i>
                                </div>

                                <h6 class="fw-bold text-dark mb-1" style="font-size: 12px;">
                                    No Matching Events
                                </h6>

                                <p class="text-muted mb-0" style="font-size: 11px;">
                                    Try changing your search keyword or event type.
                                </p>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>


            {{-- Pagination --}}
            @if(method_exists($events, 'hasPages') && $events->hasPages())

                <div class="analytics-pagination-footer d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 no-print">

                    <small class="text-muted font-monospace"
                           style="font-size: 10px;">

                        Showing {{ $events->firstItem() }}
                        to {{ $events->lastItem() }}
                        of {{ number_format($events->total()) }} events

                    </small>

                    <div>
                        {{ $events->links('pagination::bootstrap-5') }}
                    </div>

                </div>

            @endif

        </div>

    </div>

</div>


{{-- =========================================================
     5. TELEMETRY DETAIL MODAL
========================================================= --}}

<div class="modal fade"
     id="telemetryDetailModal"
     tabindex="-1"
     aria-labelledby="telemetryDetailModalLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-md">

        <div class="modal-content shadow-lg">

            {{-- Modal Header --}}
            <div class="modal-header border-0 pb-2">

                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2"
                    id="telemetryDetailModalLabel">

                    <i class="fas fa-circle-info text-primary"></i>
                    Telemetry Event Details

                </h6>

                <button type="button"
                        class="btn-close shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>

            </div>


            {{-- Modal Body --}}
            <div class="modal-body pt-2">

                {{-- Recipient & Type --}}
                <div class="p-3 bg-light border mb-3">

                    <span class="telemetry-detail-label">
                        RECIPIENT &amp; EVENT TYPE
                    </span>

                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">

                        <strong class="telemetry-recipient"
                                id="modalEventRecipient">
                            —
                        </strong>

                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace"
                              id="modalEventTypeBadge"
                              style="font-size: 9px;">
                            EVENT
                        </span>

                    </div>

                </div>


                {{-- IP Address --}}
                <div class="mb-3">

                    <span class="telemetry-detail-label">
                        CLIENT IP ADDRESS
                    </span>

                    <div class="telemetry-detail-value font-monospace"
                         id="modalEventIpAddress">
                        —
                    </div>

                </div>


                {{-- Target URL --}}
                <div>

                    <span class="telemetry-detail-label">
                        DESTINATION / TARGET URL
                    </span>

                    <div class="telemetry-detail-value font-monospace"
                         id="modalEventTargetUrl"
                         style="max-height: 180px; overflow-y: auto;">
                        —
                    </div>

                </div>

            </div>


            {{-- Modal Footer --}}
            <div class="modal-footer border-0 pt-0">

                <button type="button"
                        class="btn btn-dark btn-compact px-4"
                        data-bs-dismiss="modal">

                    <i class="fas fa-times me-1"></i>
                    Close

                </button>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     6. JAVASCRIPT
========================================================= --}}

<script>
(function () {
    'use strict';

    const root = document.getElementById('email-marketing-analytics-root');

    if (!root) {
        return;
    }

    /**
     * Open Telemetry Detail Modal
     */
    window.showUrlDetailModal = function (email, type, targetUrl, ipAddress) {

        const recipientElement = document.getElementById('modalEventRecipient');
        const typeElement = document.getElementById('modalEventTypeBadge');
        const ipElement = document.getElementById('modalEventIpAddress');
        const urlElement = document.getElementById('modalEventTargetUrl');

        if (!recipientElement || !typeElement || !ipElement || !urlElement) {
            return;
        }

        // Use textContent to prevent HTML injection.
        recipientElement.textContent = email || 'Unknown recipient';
        typeElement.textContent = type || 'UNKNOWN';
        ipElement.textContent = ipAddress || 'N/A';
        urlElement.textContent = targetUrl || '—';

        const modalElement = document.getElementById('telemetryDetailModal');

        if (modalElement && window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalElement).show();
        }
    };


    /**
     * Filter Analytics Table
     */
    window.filterAnalyticsTable = function () {

        const searchInput = document.getElementById('analyticsSearchInput');
        const typeFilter = document.getElementById('analyticsTypeFilter');

        const query = (searchInput?.value || '').toLowerCase().trim();
        const selectedType = (typeFilter?.value || 'all').toLowerCase();

        const rows = root.querySelectorAll('.event-entry-row');

        let visibleCount = 0;

        rows.forEach(function (row) {

            const rowSearch = (row.dataset.search || '').toLowerCase();
            const rowType = (row.dataset.type || '').toLowerCase();

            const matchesSearch = !query || rowSearch.includes(query);
            const matchesType = selectedType === 'all' || rowType === selectedType;

            const isVisible = matchesSearch && matchesType;

            row.style.display = isVisible ? '' : 'none';

            if (isVisible) {
                visibleCount++;
            }

        });

        // Update visible count.
        const countBadge = document.getElementById('visible-events-count');

        if (countBadge) {
            countBadge.textContent = visibleCount.toLocaleString() + ' Showing';
        }

        // Update no-match message.
        const noMatchRow = document.getElementById('noMatchEventsRow');

        if (noMatchRow) {
            noMatchRow.style.display =
                (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }

    };


    /**
     * Reset Filters
     */
    window.resetAnalyticsFilters = function () {

        const searchInput = document.getElementById('analyticsSearchInput');
        const typeFilter = document.getElementById('analyticsTypeFilter');

        if (searchInput) {
            searchInput.value = '';
        }

        if (typeFilter) {
            typeFilter.value = 'all';
        }

        window.filterAnalyticsTable();

    };


    /**
     * Initial State
     */
    document.addEventListener('DOMContentLoaded', function () {

        window.filterAnalyticsTable();

    });

})();
</script>

@endsection
