@extends('layouts.admin')

@section('title', 'Automation Execution Logs - Email Marketing')

@php
    $totalLogsCount   = $logs->total();$sentCount        = \App\Models\EmailAutomationLog::whereIn('status', ['sent', 'delivered'])->count();
    $queuedCount      = \App\Models\EmailAutomationLog::whereIn('status', ['queued', 'pending'])->count();$failedCount      = \App\Models\EmailAutomationLog::where('status', 'failed')->count();
@endphp

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-automation-logs-root .btn,
    #email-marketing-automation-logs-root .btn-sm,
    #email-marketing-automation-logs-root button,
    #email-marketing-automation-logs-root .badge,
    #email-marketing-automation-logs-root .alert,
    #email-marketing-automation-logs-root .form-control,
    #email-marketing-automation-logs-root .form-select,
    #email-marketing-automation-logs-root .input-group-text,
    #email-marketing-automation-logs-root .card,
    #email-marketing-automation-logs-root .metric-card,
    #email-marketing-automation-logs-root .page-link,
    #email-marketing-automation-logs-root .page-item span,
    #email-marketing-automation-logs-root .collapse-toggle-btn,
    .log-modal-scope .modal-content,
    .log-modal-scope .btn {
        border-radius: 5px !important;
    }

    #email-marketing-automation-logs-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #email-marketing-automation-logs-root .input-group > .form-control {
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
    #email-marketing-automation-logs-root .card {
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
    .custom-logs-table {
        width: 100%;
        margin-bottom: 0;
    }
    .custom-logs-table thead th {
        font-size: 10px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 8px;
        white-space: nowrap;
    }
    .custom-logs-table tbody td {
        padding: 8px 8px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        font-size: 11px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .custom-logs-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .custom-logs-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .custom-logs-table th,
        .custom-logs-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 7 Columns Proportional Distribution Summing to Exactly 100% */
        .col-lg-id     { width: 4% !important; }
        .col-lg-user   { width: 22% !important; }
        .col-lg-wf     { width: 22% !important; }
        .col-lg-entity { width: 14% !important; }
        .col-lg-status { width: 10% !important; }
        .col-lg-date   { width: 13% !important; }
        .col-lg-note   { width: 15% !important; }
    }

    /* Form Controls */
    #email-marketing-automation-logs-root .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #email-marketing-automation-logs-root .form-control,
    #email-marketing-automation-logs-root .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #email-marketing-automation-logs-root .form-control:focus,
    #email-marketing-automation-logs-root .form-select:focus {
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
        .logs-header-wrap h5 {
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
        .custom-logs-table {
            min-width: 820px !important;
            table-layout: auto !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-automation-logs-root">

    <!-- 1. Header & Navigation (Original Clean Light Theme) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 logs-header-wrap">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-list-check text-primary"></i>
                <span>Automation Execution Logs</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Audit history of automated lifecycle triggers, drip steps, and delivery status.
            </small>
        </div>

        <div class="d-flex gap-2 top-action-btns">
            <a href="{{ url('admin/email-marketing/automations') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-arrow-left me-1"></i> <span>Back to Workflows</span>
            </a>
            <a href="{{ url('admin/email-marketing/automations/logs') }}" class="btn btn-sm btn-dark btn-compact shadow-xs fw-bold">
                <i class="fas fa-sync-alt me-1"></i> <span>Refresh Logs</span>
            </a>
        </div>
    </div>

    <!-- 2. Execution KPI Summary Cards (Collapsible Section) -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-chart-pie text-primary me-1"></i> Automation Execution Engine Audit Overview
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseLogsKpis" aria-expanded="true" aria-controls="collapseLogsKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseLogsKpis">
            <div class="p-3">
                <div class="row g-2">
                    <!-- Total Execution Records -->
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Total Executions</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                                    <i class="fas fa-list-check"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    {{ number_format($totalLogsCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Lifecycle audit history</small>
                            </div>
                        </div>
                    </div>

                    <!-- Successfully Dispatched -->
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Dispatched / Sent</span>
                                <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                                    <i class="fas fa-circle-check"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num">
                                    {{ number_format($sentCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Current batch delivered</small>
                            </div>
                        </div>
                    </div>

                    <!-- Queued / Pending Delivery -->
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Queued / Drip</span>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-warning-emphasis bn-digit font-monospace stat-val-num">
                                    {{ number_format($queuedCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Awaiting delay execution</small>
                            </div>
                        </div>
                    </div>

                    <!-- Failed Deliveries -->
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-red h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Bounced / Failed</span>
                                <div class="metric-icon-square bg-danger-subtle text-danger shadow-xs">
                                    <i class="fas fa-triangle-exclamation"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-danger bn-digit font-monospace stat-val-num">
                                    {{ number_format($failedCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Check error logs</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Real-time Search & Filter Toolbar (Collapsible) -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-filter text-primary me-1"></i> Filter Logs by Recipient, Workflow &amp; Status
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseLogsFilter" aria-expanded="true" aria-controls="collapseLogsFilter" title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseLogsFilter">
            <div class="p-3">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-5">
                        <label class="form-label" for="logsSearchInput">
                            <i class="fas fa-search me-1 text-primary"></i> Search Email, Workflow, or Order/Cart #
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                            <input type="text" id="logsSearchInput" class="form-control border-start-0 ps-0 shadow-none font-monospace" placeholder="e.g. user@example.com, Order #104, Cart #22..." onkeyup="filterLogsTable()">
                        </div>
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label" for="logsStatusFilter">
                            <i class="fas fa-toggle-on me-1 text-success"></i> Delivery Status
                        </label>
                        <select id="logsStatusFilter" class="form-select shadow-none font-monospace" onchange="filterLogsTable()">
                            <option value="all" selected>All Statuses</option>
                            <option value="sent">Sent / Delivered</option>
                            <option value="queued">Queued / Pending</option>
                            <option value="failed">Failed / Bounced</option>
                            <option value="skipped">Skipped</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <button type="button" class="btn btn-light border btn-compact w-100 shadow-xs fw-semibold text-secondary" onclick="resetLogsFilter()">
                            <i class="fas fa-rotate-left me-1"></i> <span>Reset Filters</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Execution Logs Table Card (Desktop 100% Fit Screen with Zero Horizontal Scroll) -->
    <div class="card border-0 shadow-sm overflow-hidden mb-3">
        <div class="p-2 px-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 bg-light bg-opacity-25">
            <div>
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13px;">
                    <i class="fas fa-clock-rotate-left text-primary"></i>
                    <span>Triggered Workflow Execution Log Entries</span>
                </h6>
                <small class="text-muted font-monospace bn-digit" id="showing-logs-text" style="font-size: 10px;">
                    Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{$logs->total() }} total records
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 10px;" id="visible-logs-count">
                    {{ $logs->total() }} Logged Entries
                </span>
                <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseLogsTable" aria-expanded="true" aria-controls="collapseLogsTable" title="Toggle Table">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseLogsTable">
            <div class="table-responsive pc-fit-container">
                <table class="table align-middle mb-0 custom-logs-table" id="logs-table">
                    <thead>
                        <tr>
                            <th class="ps-3 col-lg-id text-center">#</th>
                            <th class="col-lg-user">Recipient Email</th>
                            <th class="col-lg-wf">Workflow &amp; Step</th>
                            <th class="col-lg-entity">Associated Entity</th>
                            <th class="col-lg-status">Status</th>
                            <th class="col-lg-date">Executed At</th>
                            <th class="pe-3 col-lg-note">Error / Note</th>
                        </tr>
                    </thead>
                    <tbody id="logs-tbody">
                        @forelse($logs as $log)
                            @php
                                $statusClass = match(strtolower($log->status ?? '')) {
                                    'sent', 'delivered' => 'bg-success-subtle text-success border border-success-subtle',
                                    'queued', 'pending' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                    'failed'            => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    'skipped'           => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                    default             => 'bg-light text-dark border',
                                };

                                $rowSearchData = strtolower(
                                    ($log->email ?? '') . ' ' .
                                    ($log->automation->name ?? '') . ' ' .
                                    ($log->order_id ? 'order #' . $log->order_id : '') . ' ' .
                                    ($log->abandoned_cart_id ? 'cart #' . $log->abandoned_cart_id : '') . ' ' .
                                    ($log->error_message ?? '')
                                );

                                $normalizedStatus = match(strtolower($log->status ?? '')) {
                                    'delivered' => 'sent',
                                    'pending'   => 'queued',
                                    default     => strtolower($log->status ?? 'sent'),
                                };
                            @endphp
                            <tr class="log-entry-row align-middle" data-search="{{ $rowSearchData }}" data-status="{{ $normalizedStatus }}">
                                <td class="ps-3 col-lg-id text-center font-monospace bn-digit text-muted" style="font-size: 11px;">
                                    {{ $log->id }}
                                </td>
                                <td class="col-lg-user">
                                    <div class="min-w-0" style="line-height: 1.3;">
                                        <strong class="text-dark d-block text-truncate" style="font-size: 12px;" title="{{ $log->email }}">
                                            {{ $log->email }}
                                        </strong>
                                        <small class="text-muted font-monospace bn-digit d-block text-truncate" style="font-size: 10px;">
                                            Subscriber ID: #{{ $log->subscriber_id ?? 'N/A' }}
                                        </small>
                                    </div>
                                </td>
                                <td class="col-lg-wf">
                                    <div class="min-w-0" style="line-height: 1.3;">
                                        <span class="fw-semibold text-dark d-block text-truncate" style="font-size: 11.5px;" title="{{ $log->automation->name ?? 'Deleted Workflow' }}">
                                            {{ $log->automation->name ?? 'Deleted Workflow' }}
                                        </span>
                                        <small class="text-muted d-block text-truncate font-monospace" style="font-size: 10px;">
                                            Step: <strong class="text-dark">{{ $log->step->step_order ?? '1' }}</strong> &bull; 
                                            Delay: +{{ $log->step->delay_minutes ?? 0 }} mins
                                        </small>
                                    </div>
                                </td>
                                <td class="col-lg-entity">
                                    @if($log->order_id)
                                        <span class="badge bg-light text-primary border font-monospace text-truncate d-inline-block px-2 py-1" style="font-size: 10px; max-width: 100%; border-radius: 5px !important;" title="Order #{{ $log->order_id }}">
                                            <i class="fas fa-bag-shopping me-1"></i> Order #{{ $log->order_id }}
                                        </span>
                                    @elseif($log->abandoned_cart_id)
                                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle font-monospace text-truncate d-inline-block px-2 py-1" style="font-size: 10px; max-width: 100%; border-radius: 5px !important;" title="Cart #{{ $log->abandoned_cart_id }}">
                                            <i class="fas fa-cart-shopping me-1"></i> Cart #{{ $log->abandoned_cart_id }}
                                        </span>
                                    @else
                                        <span class="text-muted font-monospace" style="font-size: 10px;">—</span>
                                    @endif
                                </td>
                                <td class="col-lg-status">
                                    <span class="badge {{ $statusClass }} font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 10px;">
                                        @if(in_array(strtolower($log->status ?? ''), ['sent', 'delivered']))
                                            <i class="fas fa-check-circle me-1"></i>
                                        @elseif(strtolower($log->status ?? '') === 'failed')
                                            <i class="fas fa-times-circle me-1"></i>
                                        @elseif(in_array(strtolower($log->status ?? ''), ['queued', 'pending']))
                                            <i class="fas fa-clock me-1"></i>
                                        @endif
                                        {{ strtoupper($log->status ?? 'SENT') }}
                                    </span>
                                </td>
                                <td class="col-lg-date font-monospace bn-digit" style="line-height: 1.3;">
                                    <span class="text-dark d-block" style="font-size: 11px;">{{ $log->created_at->format('M d, Y') }}</span>
                                    <small class="text-muted" style="font-size: 9.5px;">{{ $log->created_at->format('h:i:s A') }}</small>
                                </td>
                                <td class="col-lg-note pe-3">
                                    @if(!empty($log->error_message))
                                        <button type="button" class="btn btn-sm btn-light border p-1 text-danger shadow-xs d-inline-flex align-items-center gap-1 text-truncate" style="max-width: 100%; font-size: 10px; border-radius: 5px !important;" onclick="showLogDetailModal('{{ addslashes($log->email) }}', '{{ addslashes($log->automation->name ?? 'Workflow') }}', '{{ addslashes($log->error_message) }}')" title="Click to view full error message">
                                            <i class="fas fa-triangle-exclamation flex-shrink-0"></i>
                                            <span class="text-truncate">{{ $log->error_message }}</span>
                                        </button>
                                    @else
                                        <span class="text-success small d-flex align-items-center gap-1 font-monospace" style="font-size: 10px;">
                                            <i class="fas fa-check-circle"></i> <span>Successfully dispatched</span>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyLogsRow">
                                <td colspan="7" class="text-center py-5 text-muted small">
                                    <i class="fas fa-list-check fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                    <span class="fw-semibold">No automation logs recorded yet. Automated triggers will log their execution here.</span>
                                </td>
                            </tr>
                        @endforelse
                        <tr id="noMatchLogsRow" style="display: none;">
                            <td colspan="7" class="text-center py-5 text-muted small">
                                <i class="fas fa-filter-circle-xmark fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                <span class="fw-semibold">No execution logs matched your filter criteria.</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Bulletproof Responsive Pagination Footer -->
            @if($logs->hasPages())
                <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">
                    <small class="text-muted font-monospace bn-digit" style="font-size: 11px;">
                        Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{$logs->total() }} execution logs
                    </small>
                    <div>
                        {{ $logs->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Error / Execution Log Detail Modal -->
<div class="modal fade log-modal-scope" id="logDetailModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg p-3">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 14px;">
                    <i class="fas fa-circle-exclamation text-danger"></i> <span>Execution Detail &amp; Error Note</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2 pt-3">
                <div class="mb-2 p-2 bg-light border" style="border-radius: 5px;">
                    <small class="text-muted d-block font-monospace" style="font-size: 10px;">RECIPIENT &amp; WORKFLOW</small>
                    <strong class="text-dark d-block" id="modalLogRecipient" style="font-size: 12.5px;">user@example.com</strong>
                    <span class="text-secondary small font-monospace" id="modalLogWorkflow">Workflow Name</span>
                </div>
                <div>
                    <small class="text-muted d-block font-monospace mb-1" style="font-size: 10px;">SYSTEM RESPONSE / EXCEPTION</small>
                    <div class="p-2 bg-danger-subtle text-danger border border-danger-subtle font-monospace small" style="border-radius: 5px; font-size: 11px; white-space: pre-wrap; word-break: break-all; max-height: 220px; overflow-y: auto;" id="modalLogErrorMessage">
                        Error message will be rendered here.
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
    function showLogDetailModal(email, workflow, errorMessage) {
        document.getElementById('modalLogRecipient').innerText = email;
        document.getElementById('modalLogWorkflow').innerText = workflow;
        document.getElementById('modalLogErrorMessage').innerText = errorMessage || 'No error details recorded.';
        const modalEl = document.getElementById('logDetailModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function filterLogsTable() {
        const query  = (document.getElementById('logsSearchInput')?.value || '').toLowerCase().trim();
        const status = (document.getElementById('logsStatusFilter')?.value || 'all').toLowerCase();

        const rows = document.querySelectorAll('.log-entry-row');
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

        const countBadge = document.getElementById('visible-logs-count');
        if (countBadge) {
            countBadge.innerText = `${visibleCount} Showing`;
        }

        const noMatchRow = document.getElementById('noMatchLogsRow');
        if (noMatchRow) {
            noMatchRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }
    }

    function resetLogsFilter() {
        const searchInput = document.getElementById('logsSearchInput');
        const statusFilter = document.getElementById('logsStatusFilter');
        if (searchInput) searchInput.value = '';
        if (statusFilter) statusFilter.value = 'all';
        filterLogsTable();
    }
</script>
@endsection