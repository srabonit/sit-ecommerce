@extends('layouts.admin')

@section('title', 'Queue & Delivery Logs - Email Marketing')

@section('admin_content')
@php
    $logs = $queueItems ?? ($logs ?? ($queueLogs ?? ($deliveryLogs ?? collect())));
    $totalLogsCount = method_exists($logs, 'total') ? $logs->total() : $logs->count();

    // Graceful query resolution for recipients telemetry
    if (class_exists(\App\Models\EmailCampaignRecipient::class)) {
        $deliveredOpenedCount = \App\Models\EmailCampaignRecipient::whereIn('status', ['delivered', 'opened', 'clicked'])->count();
        $pendingQueueCount    = \App\Models\EmailCampaignRecipient::whereIn('status', ['queued', 'pending'])->count();
        $bouncedFailedCount   = \App\Models\EmailCampaignRecipient::whereIn('status', ['bounced', 'failed'])->count();
    } else {
        $deliveredOpenedCount = method_exists($logs, 'whereIn') ? $logs->whereIn('status', ['delivered', 'opened', 'clicked'])->count() : 0;
        $pendingQueueCount    = method_exists($logs, 'whereIn') ? $logs->whereIn('status', ['queued', 'pending'])->count() : 0;
        $bouncedFailedCount   = method_exists($logs, 'whereIn') ? $logs->whereIn('status', ['bounced', 'failed'])->count() : 0;
    }

    $dashboardRoute = Route::has('admin.email_marketing.dashboard') 
        ? route('admin.email_marketing.dashboard') 
        : url('admin/email-marketing/dashboard');

    $queueLogsRoute = Route::has('admin.email_marketing.queue_logs') 
        ? route('admin.email_marketing.queue_logs') 
        : url('admin/email-marketing/queue-logs');
@endphp

<style>
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-queue-logs-root .btn,
    #email-marketing-queue-logs-root .btn-sm,
    #email-marketing-queue-logs-root button,
    #email-marketing-queue-logs-root .badge,
    #email-marketing-queue-logs-root .alert,
    #email-marketing-queue-logs-root .form-control,
    #email-marketing-queue-logs-root .form-select,
    #email-marketing-queue-logs-root .input-group-text,
    #email-marketing-queue-logs-root .card,
    #email-marketing-queue-logs-root .metric-card,
    #email-marketing-queue-logs-root .page-link,
    #email-marketing-queue-logs-root .page-item span,
    #email-marketing-queue-logs-root .collapse-toggle-btn,
    .queue-modal-scope .modal-content,
    .queue-modal-scope .btn {
        border-radius: 5px !important;
    }

    #email-marketing-queue-logs-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #email-marketing-queue-logs-root .input-group > .form-control,
    #email-marketing-queue-logs-root .input-group > .form-select {
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

    .queue-filter-control {
        font-size: 12px !important;
        height: 32px !important;
        padding: 4px 8px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        box-shadow: none !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .queue-filter-control:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        outline: none;
    }

    .queue-addon-text {
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
    #email-marketing-queue-logs-root .card {
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
    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-left: 3.5px solid #f59e0b !important; }
    .metric-card-red    { background: linear-gradient(180deg, #ffffff 0%, #fef2f2 100%); border-left: 3.5px solid #ef4444 !important; }

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .custom-queue-table {
        width: 100%;
        margin-bottom: 0;
    }
    .custom-queue-table thead th {
        font-size: 10px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 8px;
        white-space: nowrap;
    }
    .custom-queue-table tbody td {
        padding: 8px 8px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        font-size: 11px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .custom-queue-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .custom-queue-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .custom-queue-table th,
        .custom-queue-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 8 Columns Proportional Distribution Summing to Exactly 100% */
        .col-qk-id     { width: 5% !important; }
        .col-qk-email  { width: 25% !important; }
        .col-qk-camp   { width: 20% !important; }
        .col-qk-status { width: 11% !important; }
        .col-qk-date   { width: 15% !important; }
        .col-qk-opens  { width: 6% !important; }
        .col-qk-clicks { width: 6% !important; }
        .col-qk-diag   { width: 12% !important; }
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
        .queue-header-wrap h5 {
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
        .custom-queue-table {
            min-width: 820px !important;
            table-layout: auto !important;
        }
        #email-marketing-queue-logs-root .card {
            margin-bottom: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-queue-logs-root">

    {{-- 1. Header & Navigation (Original Clean Light Theme) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 queue-header-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-server text-primary"></i>
                    <span>Email Queue &amp; Delivery Logs</span>
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace" id="queue-total-badge" style="font-size: 10px;">
                    {{ number_format($totalLogsCount) }} Recipient Items
                </span>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                Live audit trail of background queue dispatches, SMTP transmissions, and recipient event trackers.
            </small>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 top-action-btns">
            <a href="{{ $dashboardRoute }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-chart-pie me-1"></i> <span>Dashboard</span>
            </a>
            <a href="{{ $queueLogsRoute }}" class="btn btn-sm btn-dark btn-compact shadow-xs fw-bold" onclick="this.innerHTML='<i class=\'fas fa-spinner fa-spin me-1\'></i> Refreshing...'">
                <i class="fas fa-arrows-rotate me-1"></i> <span>Refresh Queue</span>
            </a>
        </div>
    </div>

    {{-- 2. System Status Alerts --}}
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

    {{-- 3. Live Queue KPI Row (Collapsible Section) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-chart-pie text-primary me-1"></i> Delivery Pipeline &amp; Transmission Overview
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseQueueKpis" aria-expanded="true" aria-controls="collapseQueueKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseQueueKpis">
            <div class="p-3">
                <div class="row g-2">
                    {{-- Total Logged Items --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Total Queue Items</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                                    <i class="fas fa-envelopes-bulk"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    {{ number_format($totalLogsCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Total recipient queue items</small>
                            </div>
                        </div>
                    </div>

                    {{-- Delivered / Opened --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Delivered / Opened</span>
                                <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num">
                                    {{ number_format($deliveredOpenedCount) }}
                                </h4>
                                <small class="text-success d-block text-truncate font-monospace stat-sub-text">
                                    <i class="fas fa-circle-check me-1"></i>Verified inboxes
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- Pending in Queue --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Pending in Queue</span>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-warning-emphasis bn-digit font-monospace stat-val-num">
                                    {{ number_format($pendingQueueCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Awaiting worker pickup</small>
                            </div>
                        </div>
                    </div>

                    {{-- Bounced / Failed --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-red h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Bounced / Failed</span>
                                <div class="metric-icon-square bg-danger-subtle text-danger shadow-xs">
                                    <i class="fas fa-ban"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-danger bn-digit font-monospace stat-val-num">
                                    {{ number_format($bouncedFailedCount) }}
                                </h4>
                                <small class="text-danger d-block text-truncate font-monospace stat-sub-text">
                                    <i class="fas fa-triangle-exclamation me-1"></i>Suppression active
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Instant Search & Status Filter Toolbar (Collapsible Section) --}}
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0 !important;">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-filter text-primary me-1"></i> Filter Queue Dispatches &amp; Search Transmissions
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseQueueFilter" aria-expanded="true" aria-controls="collapseQueueFilter" title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseQueueFilter">
            <div class="p-3">
                <form id="queueFilterForm" onsubmit="return false;">
                    <div class="row g-2 align-items-end">
                        {{-- Instant Live Search Input --}}
                        <div class="col-12 col-md-5">
                            <label class="form-label" for="queueSearchInput">
                                <i class="fas fa-search me-1 text-primary"></i> Search Email, Broadcast Name, or Token
                            </label>
                            <div class="input-group">
                                <span class="input-group-text queue-addon-text border-end-0"><i class="fas fa-search text-muted" style="font-size: 11px;"></i></span>
                                <input type="text" id="queueSearchInput" class="form-control border-start-0 ps-0 queue-filter-control shadow-none font-monospace" placeholder="customer@example.com, Spring Promo..." onkeyup="filterQueueTable()">
                            </div>
                        </div>

                        {{-- Status Filter Dropdown --}}
                        <div class="col-6 col-md-4">
                            <label class="form-label" for="queueStatusFilter">
                                <i class="fas fa-toggle-on me-1 text-success"></i> Delivery Status
                            </label>
                            <select id="queueStatusFilter" class="form-select queue-filter-control shadow-none font-monospace" onchange="filterQueueTable()">
                                <option value="all" selected>-- All Delivery Statuses --</option>
                                <option value="queued">Queued / In Process</option>
                                <option value="sent">Sent to SMTP</option>
                                <option value="delivered">Delivered Successfully</option>
                                <option value="opened">Opened</option>
                                <option value="clicked">Clicked</option>
                                <option value="bounced">Bounced</option>
                                <option value="failed">Failed Dispatch</option>
                            </select>
                        </div>

                        {{-- Action Reset Button --}}
                        <div class="col-6 col-md-3">
                            <button type="button" class="btn btn-light border btn-compact w-100 shadow-xs fw-semibold text-secondary" onclick="resetQueueFilters()">
                                <i class="fas fa-rotate-left me-1"></i> <span>Reset Filters</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 5. Queue Delivery Log Table Card (Desktop 100% Fit Screen with Zero Horizontal Scroll) --}}
    <div class="card border-0 shadow-sm overflow-hidden mb-3">
        <div class="p-2 px-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 bg-light bg-opacity-25">
            <div>
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13px;">
                    <i class="fas fa-list-check text-primary"></i> <span>Queue Audit Log &amp; Transmission Details</span>
                </h6>
                <small class="text-muted font-monospace bn-digit" id="showing-entries-text" style="font-size: 10px;">
                    Showing {{ method_exists($logs, 'firstItem') ? ($logs->firstItem() ?? 0) : 1 }} to {{ method_exists($logs, 'lastItem') ? ($logs->lastItem() ?? 0) : $logs->count() }} of {{$totalLogsCount }} records
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 10px;" id="visible-queue-count">
                    {{ $totalLogsCount }} Total
                </span>
                <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseQueueTable" aria-expanded="true" aria-controls="collapseQueueTable" title="Toggle Table">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseQueueTable">
            <div class="table-responsive pc-fit-container" style="min-height: 250px;">
                <table class="table align-middle mb-0 custom-queue-table" id="queue-table">
                    <thead>
                        <tr>
                            <th class="ps-3 col-qk-id text-center">#ID</th>
                            <th class="col-qk-email">Recipient Email</th>
                            <th class="col-qk-camp">Associated Broadcast</th>
                            <th class="col-qk-status">Status</th>
                            <th class="col-qk-date">Queued / Sent At</th>
                            <th class="text-center col-qk-opens">Opens</th>
                            <th class="text-center col-qk-clicks">Clicks</th>
                            <th class="pe-3 col-qk-diag">Diagnostic</th>
                        </tr>
                    </thead>
                    <tbody id="queue-tbody">
                        @forelse($logs as $log)
                            @php
                                $statusSlug = strtolower($log->status ?? '');
                                $statusBadge = match($statusSlug) {
                                    'opened', 'clicked' => 'bg-success-subtle text-success border border-success-subtle',
                                    'delivered'         => 'bg-info-subtle text-info border border-info-subtle',
                                    'sent'              => 'bg-primary-subtle text-primary border border-primary-subtle',
                                    'queued', 'pending' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                    'bounced', 'failed' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    default             => 'bg-light text-secondary border',
                                };

                                $campaignName =$log->campaign->name ?? 'Lifecycle / System Trigger';
                                $campUrl =$log->campaign ? (Route::has('admin.email_marketing.campaigns.show') ? route('admin.email_marketing.campaigns.show', $log->campaign->id) : url('admin/email-marketing/campaigns/view/' .$log->campaign->id)) : null;

                                $rowSearchData = strtolower(
                                    ($log->email ?? '') . ' ' .
                                    $campaignName . ' ' .
                                    ($log->tracking_token ?? '') . ' ' .
                                    $statusSlug . ' ' .
                                    ($log->error_message ?? '')
                                );
                            @endphp
                            <tr class="queue-entry-row align-middle" data-search="{{ $rowSearchData }}" data-status="{{ $statusSlug }}">
                                <td class="ps-3 col-qk-id text-center font-monospace bn-digit text-muted" style="font-size: 11px;">
                                    {{ $log->id }}
                                </td>
                                <td class="col-qk-email">
                                    <div class="min-w-0" style="line-height: 1.3;">
                                        <strong class="text-dark d-block text-truncate" style="font-size: 12px;" title="{{ $log->email }}">
                                            {{ $log->email }}
                                        </strong>
                                        <div class="d-flex align-items-center gap-1 mt-0">
                                            <small class="text-muted font-monospace text-truncate" style="font-size: 10px; max-width: 140px;" title="{{ $log->tracking_token ?? 'N/A' }}">
                                                Tk: {{ substr($log->tracking_token ?? 'N/A', 0, 14) }}...
                                            </small>
                                            @if(!empty($log->tracking_token))
                                                <button type="button" class="btn btn-sm btn-light border p-0 text-muted shadow-none d-inline-flex align-items-center justify-content-center" style="width: 18px; height: 18px;" onclick="copyLogToken('{{ addslashes($log->tracking_token) }}')" title="Copy Tracking Token">
                                                    <i class="fas fa-copy" style="font-size: 8.5px;"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="col-qk-camp">
                                    @if($log->campaign &&$campUrl)
                                        <a href="{{ $campUrl }}" class="text-decoration-none fw-semibold text-primary d-block text-truncate" style="font-size: 12px;" title="{{ $campaignName }}">
                                            <i class="fas fa-paper-plane me-1 text-primary"></i> {{ $campaignName }}
                                        </a>
                                    @else
                                        <span class="text-muted small font-monospace d-block text-truncate">{{ $campaignName }}</span>
                                    @endif
                                </td>
                                <td class="col-qk-status">
                                    <span class="badge {{ $statusBadge }} font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 9.5px;">
                                        @if(in_array($statusSlug, ['queued', 'pending']))
                                            <i class="fas fa-clock me-1"></i>
                                        @elseif(in_array($statusSlug, ['opened', 'clicked', 'delivered']))
                                            <i class="fas fa-circle-check me-1"></i>
                                        @elseif(in_array($statusSlug, ['bounced', 'failed']))
                                            <i class="fas fa-triangle-exclamation me-1"></i>
                                        @endif
                                        {{ strtoupper($log->status ?? 'UNKNOWN') }}
                                    </span>
                                </td>
                                <td class="col-qk-date font-monospace bn-digit" style="line-height: 1.3;">
                                    @if($log->sent_at)
                                        <span class="text-dark d-block" style="font-size: 11px;">{{ $log->sent_at->format('M d, Y') }}</span>
                                        <small class="text-muted" style="font-size: 9.5px;">{{ $log->sent_at->format('h:i:s A') }}</small>
                                    @elseif($log->created_at)
                                        <span class="text-muted d-block" style="font-size: 10.5px;">Queued: {{ $log->created_at->format('M d, Y') }}</span>
                                        <small class="text-muted" style="font-size: 9.5px;">{{ $log->created_at->format('h:i A') }}</small>
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 10px;">Pending Dispatch</span>
                                    @endif
                                </td>
                                <td class="text-center col-qk-opens font-monospace bn-digit">
                                    @php $openCount = (int)($log->opens_count ?? 0); @endphp
                                    @if($openCount > 0)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0" style="border-radius: 4px; font-size: 11px;">
                                            {{ $openCount }}
                                        </span>
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 11px;">0</span>
                                    @endif
                                </td>
                                <td class="text-center col-qk-clicks font-monospace bn-digit">
                                    @php $clickCount = (int)($log->clicks_count ?? 0); @endphp
                                    @if($clickCount > 0)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0" style="border-radius: 4px; font-size: 11px;">
                                            {{ $clickCount }}
                                        </span>
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 11px;">0</span>
                                    @endif
                                </td>
                                <td class="pe-3 col-qk-diag">
                                    @if(!empty($log->error_message))
                                        <button type="button" class="btn btn-sm btn-light border p-1 text-danger shadow-xs d-inline-flex align-items-center gap-1 text-truncate" style="max-width: 100%; font-size: 10px; border-radius: 5px !important;" onclick="showQueueDetailModal('{{ addslashes($log->email) }}', '{{ addslashes($campaignName) }}', '{{ addslashes($log->error_message) }}')" title="Click to view full error message">
                                            <i class="fas fa-triangle-exclamation flex-shrink-0"></i>
                                            <span class="text-truncate">{{ $log->error_message }}</span>
                                        </button>
                                    @else
                                        <span class="text-success small d-flex align-items-center gap-1 font-monospace" style="font-size: 10px;">
                                            <i class="fas fa-check-circle"></i> <span>OK</span>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyQueueRow">
                                <td colspan="8" class="text-center py-5 text-muted small">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                    <span class="fw-semibold">No queue or delivery logs found matching the filter criteria.</span>
                                </td>
                            </tr>
                        @endforelse
                        <tr id="noMatchQueueRow" style="display: none;">
                            <td colspan="8" class="text-center py-5 text-muted small">
                                <i class="fas fa-filter-circle-xmark fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                <span class="fw-semibold">No queue logs matched your search or status filter.</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Bulletproof Responsive Pagination Footer --}}
            @if(method_exists($logs, 'hasPages') &&$logs->hasPages())
                <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">
                    <small class="text-muted font-monospace bn-digit" style="font-size: 11px;">
                        Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{$logs->total() }} records
                    </small>
                    <div>
                        {{ $logs->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal: Error / Transmission Diagnostic Detail Modal --}}
<div class="modal fade queue-modal-scope" id="queueDetailModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg p-3">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 14px;">
                    <i class="fas fa-circle-exclamation text-danger"></i> <span>Transmission Diagnostic &amp; Error Note</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2 pt-3">
                <div class="mb-2 p-2 bg-light border" style="border-radius: 5px;">
                    <small class="text-muted d-block font-monospace" style="font-size: 10px;">RECIPIENT &amp; BROADCAST</small>
                    <strong class="text-dark d-block" id="modalQueueRecipient" style="font-size: 12.5px;">user@example.com</strong>
                    <span class="text-secondary small font-monospace" id="modalQueueCampaign">Campaign Name</span>
                </div>
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted font-monospace" style="font-size: 10px;">SMTP SERVER RESPONSE / ERROR</small>
                        <button type="button" class="btn btn-link btn-compact p-0 text-decoration-none text-primary font-monospace" style="font-size: 10.5px;" onclick="copyModalError()">
                            <i class="fas fa-copy me-1"></i> Copy Error
                        </button>
                    </div>
                    <div class="p-2 bg-danger-subtle text-danger border border-danger-subtle font-monospace small" style="border-radius: 5px; font-size: 11px; white-space: pre-wrap; word-break: break-all; max-height: 220px; overflow-y: auto;" id="modalQueueErrorMessage">
                        Diagnostic details will be rendered here.
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
    function showQueueDetailModal(email, campaign, errorMessage) {
        document.getElementById('modalQueueRecipient').innerText = email;
        document.getElementById('modalQueueCampaign').innerText = campaign;
        document.getElementById('modalQueueErrorMessage').innerText = errorMessage || 'No diagnostic error details recorded.';
        const modalEl = document.getElementById('queueDetailModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function copyModalError() {
        const text = document.getElementById('modalQueueErrorMessage')?.innerText || '';
        if (text && navigator.clipboard) {
            navigator.clipboard.writeText(text);
        }
    }

    function copyLogToken(token) {
        if (token && navigator.clipboard) {
            navigator.clipboard.writeText(token);
        }
    }

    function filterQueueTable() {
        const query  = (document.getElementById('queueSearchInput')?.value || '').toLowerCase().trim();
        const status = (document.getElementById('queueStatusFilter')?.value || 'all').toLowerCase();

        const rows = document.querySelectorAll('.queue-entry-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const searchData = row.getAttribute('data-search') || '';
            const rowStatus  = row.getAttribute('data-status') || '';

            const matchesQuery  = !query || searchData.includes(query);
            const matchesStatus = status === 'all' || rowStatus === status;

            if (matchesQuery && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const countBadge = document.getElementById('visible-queue-count');
        if (countBadge) {
            countBadge.innerText = `${visibleCount} Showing`;
        }

        const noMatchRow = document.getElementById('noMatchQueueRow');
        if (noMatchRow) {
            noMatchRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }
    }

    function resetQueueFilters() {
        const searchInput  = document.getElementById('queueSearchInput');
        const statusFilter = document.getElementById('queueStatusFilter');
        if (searchInput)  searchInput.value = '';
        if (statusFilter) statusFilter.value = 'all';
        filterQueueTable();
    }
</script>
@endsection