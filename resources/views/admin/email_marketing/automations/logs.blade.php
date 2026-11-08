
@extends('layouts.admin')

@section('title', 'Automation Execution Logs - Email Marketing')

@php
    $totalLogsCount = $logs->total();

    $sentCount = \App\Models\EmailAutomationLog::whereIn('status', ['sent', 'delivered'])->count();
    $queuedCount = \App\Models\EmailAutomationLog::whereIn('status', ['queued', 'pending'])->count();
    $failedCount = \App\Models\EmailAutomationLog::where('status', 'failed')->count();
@endphp

@section('admin_content')

<style>
    #email-marketing-automation-logs-root {
        --logs-primary: #2563eb;
        --logs-border: #e2e8f0;
        --logs-muted: #64748b;
        --logs-dark: #0f172a;
        --logs-radius: 5px;
    }

    /* Global radius */
    #email-marketing-automation-logs-root *,
    #logDetailModal * {
        border-radius: var(--logs-radius);
    }

    #email-marketing-automation-logs-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #email-marketing-automation-logs-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    #email-marketing-automation-logs-root .card,
    #email-marketing-automation-logs-root .btn,
    #email-marketing-automation-logs-root .badge,
    #email-marketing-automation-logs-root .form-control,
    #email-marketing-automation-logs-root .form-select,
    #email-marketing-automation-logs-root .page-link,
    #logDetailModal .modal-content,
    #logDetailModal .btn {
        border-radius: 5px !important;
    }

    #email-marketing-automation-logs-root .card {
        border: 1px solid var(--logs-border);
        box-shadow: 0 2px 7px rgba(15, 23, 42, .035);
        background: #fff;
    }

    /* Header */
    .logs-header-wrap h5 {
        font-size: 18px;
        letter-spacing: -.35px;
    }

    .logs-header-wrap p {
        font-size: 12px;
        color: var(--logs-muted);
    }

    .btn-compact {
        min-height: 32px;
        padding: 6px 12px !important;
        font-size: 11px !important;
        font-weight: 650 !important;
        line-height: 1.3;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        white-space: nowrap;
        transition: all .18s ease;
    }

    .btn-compact:hover {
        transform: translateY(-1px);
    }

    /* Section headers */
    .logs-section-header {
        padding: 11px 15px;
        min-height: 43px;
        border-bottom: 1px solid var(--logs-border);
        background: #fff;
    }

    .logs-section-title {
        font-size: 12px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .collapse-toggle-btn {
        width: 25px;
        height: 25px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #475569;
        transition: all .2s ease;
    }

    .collapse-toggle-btn:hover {
        background: var(--logs-primary);
        border-color: var(--logs-primary);
        color: #fff;
    }

    .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    .collapse-icon {
        font-size: 10px;
        transition: transform .2s ease;
    }

    /* KPI cards */
    .metric-card {
        height: 100%;
        padding: 13px;
        position: relative;
        overflow: hidden;
        border: 1px solid var(--logs-border);
        border-left: 3px solid transparent !important;
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(15, 23, 42, .07);
    }

    .metric-card-blue {
        background: linear-gradient(145deg, #fff, #f0f7ff);
        border-left-color: #2563eb !important;
    }

    .metric-card-green {
        background: linear-gradient(145deg, #fff, #effcf5);
        border-left-color: #10b981 !important;
    }

    .metric-card-amber {
        background: linear-gradient(145deg, #fff, #fffbeb);
        border-left-color: #f59e0b !important;
    }

    .metric-card-red {
        background: linear-gradient(145deg, #fff, #fff1f2);
        border-left-color: #ef4444 !important;
    }

    .stat-label-title {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        line-height: 1.3;
    }

    .stat-val-num {
        font-size: 21px;
        font-weight: 800;
        line-height: 1.3;
        letter-spacing: -.6px;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .stat-sub-text {
        display: block;
        margin-top: 3px;
        font-size: 10px;
        color: #94a3b8;
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

    /* Filters */
    #email-marketing-automation-logs-root .form-label {
        display: block;
        margin-bottom: 5px;
        font-size: 11px;
        font-weight: 700;
        color: #334155;
    }

    #email-marketing-automation-logs-root .form-control,
    #email-marketing-automation-logs-root .form-select {
        min-height: 35px;
        padding: 6px 10px;
        font-size: 12px;
        color: #0f172a;
        border: 1px solid #cbd5e1;
        background-color: #fff;
        box-shadow: none;
    }

    #email-marketing-automation-logs-root .form-control:focus,
    #email-marketing-automation-logs-root .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .11);
    }

    #email-marketing-automation-logs-root .input-group-text {
        border-color: #cbd5e1;
        font-size: 11px;
    }

    /* Table heading */
    .logs-table-heading {
        padding: 12px 15px;
        background: #f8fafc;
        border-bottom: 1px solid var(--logs-border);
    }

    .logs-table-heading h6 {
        font-size: 13px;
        font-weight: 750;
        color: #1e293b;
        margin: 0;
    }

    .logs-table-heading small {
        font-size: 10px;
        color: #64748b;
    }

    /* Logs table */
    .custom-logs-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .custom-logs-table thead th {
        padding: 10px 8px;
        background: #f8fafc;
        color: #64748b;
        font-size: 10px;
        font-weight: 750;
        letter-spacing: .035em;
        text-transform: uppercase;
        white-space: nowrap;
        border-bottom: 1px solid #e2e8f0;
    }

    .custom-logs-table tbody td {
        padding: 10px 8px;
        color: #334155;
        font-size: 11px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }

    .custom-logs-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .custom-logs-table tbody tr:hover td {
        background: #f8fafc;
    }

    .custom-logs-table .text-truncate {
        max-width: 100%;
    }

    @media (min-width: 992px) {
        .custom-logs-table {
            table-layout: fixed;
        }

        .pc-fit-container {
            overflow-x: hidden;
        }

        .custom-logs-table th,
        .custom-logs-table td {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .col-lg-id     { width: 4%; }
        .col-lg-user   { width: 22%; }
        .col-lg-wf     { width: 22%; }
        .col-lg-entity { width: 14%; }
        .col-lg-status { width: 10%; }
        .col-lg-date   { width: 13%; }
        .col-lg-note   { width: 15%; }
    }

    /* Error button */
    .log-error-btn {
        max-width: 100%;
        min-height: 27px;
        padding: 4px 7px !important;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 10px !important;
        overflow: hidden;
    }

    /* Pagination */
    .logs-pagination {
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .logs-pagination nav {
        margin: 0;
    }

    .logs-pagination nav > div.d-sm-none,
    .logs-pagination nav > .d-sm-none {
        display: none !important;
    }

    .logs-pagination nav p,
    .logs-pagination nav .small.text-muted,
    .logs-pagination nav div:has(> p),
    .logs-pagination nav > div.d-none > div:first-child,
    .logs-pagination nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }

    .logs-pagination .pagination {
        margin: 0;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 3px;
    }

    .logs-pagination .page-link,
    .logs-pagination .page-item span {
        min-width: 27px !important;
        height: 27px !important;
        padding: 0 7px !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        font-size: 11px !important;
        font-weight: 650;
        color: #475569 !important;
        background: #fff !important;
        border: 1px solid #cbd5e1 !important;
        text-decoration: none;
    }

    .logs-pagination .page-item.active .page-link,
    .logs-pagination .page-item.active span {
        color: #fff !important;
        background: #2563eb !important;
        border-color: #2563eb !important;
    }

    .logs-pagination .page-item.disabled {
        opacity: .45;
    }

    .logs-pagination svg {
        width: 11px;
        height: 11px;
    }

    /* Modal */
    #logDetailModal {
        z-index: 106500;
    }

    #logDetailModal .modal-content {
        border: 1px solid #e2e8f0;
        border-radius: 5px !important;
    }

    #modalLogErrorMessage {
        white-space: pre-wrap;
        word-break: break-word;
        max-height: 250px;
        overflow-y: auto;
        font-size: 11px;
        line-height: 1.6;
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        .logs-header-wrap h5 {
            font-size: 16px !important;
        }

        .logs-header-wrap p {
            font-size: 11px;
        }

        .top-action-btns {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 7px !important;
        }

        .top-action-btns .btn {
            width: 100%;
            min-height: 35px;
            padding: 7px 5px !important;
            font-size: 10px !important;
        }

        .metric-card {
            min-height: 88px;
            padding: 10px;
        }

        .stat-label-title {
            font-size: 10px;
        }

        .stat-val-num {
            font-size: 17px;
        }

        .stat-sub-text {
            font-size: 9px;
        }

        .metric-icon-square {
            width: 28px;
            height: 28px;
            font-size: 11px;
        }

        .logs-section-header {
            padding: 10px 12px;
        }

        .logs-section-title {
            font-size: 11px;
        }

        .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        .custom-logs-table {
            min-width: 820px;
            table-layout: auto;
        }

        .logs-table-heading {
            padding: 10px 12px;
        }

        .logs-pagination {
            width: 100%;
            justify-content: center;
        }
    }

    @media print {
        .no-print,
        .top-action-btns,
        .logs-filter-card,
        .collapse-toggle-btn {
            display: none !important;
        }

        .pc-fit-container {
            overflow: visible !important;
        }

        .custom-logs-table {
            table-layout: auto !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="email-marketing-automation-logs-root">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3 logs-header-wrap">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="fas fa-list-check text-primary"></i>
                Automation Execution Logs
            </h5>
            <p class="mb-0">
                Audit history of automated lifecycle triggers, drip steps, and delivery status.
            </p>
        </div>

        <div class="d-flex gap-2 top-action-btns">
            <a href="{{ url('admin/email-marketing/automations') }}"
               class="btn btn-outline-secondary btn-compact">
                <i class="fas fa-arrow-left"></i>
                Back to Workflows
            </a>

            <a href="{{ url('admin/email-marketing/automations/logs') }}"
               class="btn btn-dark btn-compact">
                <i class="fas fa-sync-alt"></i>
                Refresh Logs
            </a>
        </div>
    </div>

    {{-- KPI Overview --}}
    <div class="card mb-3">
        <div class="logs-section-header d-flex justify-content-between align-items-center">
            <h6 class="logs-section-title">
                <i class="fas fa-chart-pie text-primary me-1"></i>
                Automation Execution Overview
            </h6>

            <button type="button"
                    class="collapse-toggle-btn no-print"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseLogsKpis"
                    aria-expanded="true">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseLogsKpis">
            <div class="p-3">
                <div class="row g-2">

                    <div class="col-6 col-xl-3">
                        <div class="metric-card metric-card-blue">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="stat-label-title">Total Executions</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary">
                                    <i class="fas fa-list-check"></i>
                                </div>
                            </div>
                            <h4 class="stat-val-num text-dark font-monospace">
                                {{ number_format($totalLogsCount) }}
                            </h4>
                            <small class="stat-sub-text">Lifecycle audit history</small>
                        </div>
                    </div>

                    <div class="col-6 col-xl-3">
                        <div class="metric-card metric-card-green">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="stat-label-title">Dispatched / Sent</span>
                                <div class="metric-icon-square bg-success-subtle text-success">
                                    <i class="fas fa-circle-check"></i>
                                </div>
                            </div>
                            <h4 class="stat-val-num text-success font-monospace">
                                {{ number_format($sentCount) }}
                            </h4>
                            <small class="stat-sub-text">Sent and delivered records</small>
                        </div>
                    </div>

                    <div class="col-6 col-xl-3">
                        <div class="metric-card metric-card-amber">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="stat-label-title">Queued / Pending</span>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <h4 class="stat-val-num text-warning-emphasis font-monospace">
                                {{ number_format($queuedCount) }}
                            </h4>
                            <small class="stat-sub-text">Awaiting execution</small>
                        </div>
                    </div>

                    <div class="col-6 col-xl-3">
                        <div class="metric-card metric-card-red">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="stat-label-title">Failed / Bounced</span>
                                <div class="metric-icon-square bg-danger-subtle text-danger">
                                    <i class="fas fa-triangle-exclamation"></i>
                                </div>
                            </div>
                            <h4 class="stat-val-num text-danger font-monospace">
                                {{ number_format($failedCount) }}
                            </h4>
                            <small class="stat-sub-text">Requires attention</small>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Search & Filters --}}
    <div class="card mb-3 logs-filter-card">
        <div class="logs-section-header d-flex justify-content-between align-items-center">
            <h6 class="logs-section-title">
                <i class="fas fa-filter text-primary me-1"></i>
                Search & Filter Execution Logs
            </h6>

            <button type="button"
                    class="collapse-toggle-btn no-print"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseLogsFilter"
                    aria-expanded="true">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseLogsFilter">
            <div class="p-3">
                <div class="row g-2 align-items-end">

                    <div class="col-12 col-md-5">
                        <label class="form-label" for="logsSearchInput">
                            <i class="fas fa-search text-primary me-1"></i>
                            Search Recipient, Workflow or Order/Cart
                        </label>

                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">
                                <i class="fas fa-search"></i>
                            </span>

                            <input type="text"
                                   id="logsSearchInput"
                                   class="form-control"
                                   placeholder="Search email, workflow, order..."
                                   autocomplete="off">
                        </div>
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label" for="logsStatusFilter">
                            <i class="fas fa-toggle-on text-success me-1"></i>
                            Delivery Status
                        </label>

                        <select id="logsStatusFilter" class="form-select">
                            <option value="all">All Statuses</option>
                            <option value="sent">Sent / Delivered</option>
                            <option value="queued">Queued / Pending</option>
                            <option value="failed">Failed / Bounced</option>
                            <option value="skipped">Skipped</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <button type="button"
                                class="btn btn-light border btn-compact w-100"
                                onclick="resetLogsFilter()">
                            <i class="fas fa-rotate-left"></i>
                            Reset Filters
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Execution Log Table --}}
    <div class="card overflow-hidden mb-3">

        <div class="logs-table-heading d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
            <div>
                <h6>
                    <i class="fas fa-clock-rotate-left text-primary me-1"></i>
                    Workflow Execution Records
                </h6>

                <small class="font-monospace" id="showing-logs-text">
                    Showing {{ $logs->firstItem() ?? 0 }}
                    to {{ $logs->lastItem() ?? 0 }}
                    of {{ $logs->total() }} total records
                </small>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-white text-secondary border px-2 py-2 font-monospace"
                      id="visible-logs-count">
                    {{ $logs->total() }} Logged Entries
                </span>

                <button type="button"
                        class="collapse-toggle-btn no-print"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseLogsTable"
                        aria-expanded="true">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseLogsTable">
            <div class="table-responsive pc-fit-container">
                <table class="table custom-logs-table" id="logs-table">
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
                                $rawStatus = strtolower($log->status ?? '');

                                $statusClass = match($rawStatus) {
                                    'sent', 'delivered' => 'bg-success-subtle text-success border border-success-subtle',
                                    'queued', 'pending' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                    'failed' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    'skipped' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                    default => 'bg-light text-dark border',
                                };

                                $normalizedStatus = match($rawStatus) {
                                    'delivered' => 'sent',
                                    'pending' => 'queued',
                                    default => $rawStatus ?: 'sent',
                                };

                                $rowSearchData = strtolower(
                                    ($log->email ?? '') . ' ' .
                                    ($log->automation->name ?? '') . ' ' .
                                    ($log->order_id ? 'order #' . $log->order_id : '') . ' ' .
                                    ($log->abandoned_cart_id ? 'cart #' . $log->abandoned_cart_id : '') . ' ' .
                                    ($log->error_message ?? '')
                                );
                            @endphp

                            <tr class="log-entry-row"
                                data-search="{{ $rowSearchData }}"
                                data-status="{{ $normalizedStatus }}">

                                <td class="ps-3 col-lg-id text-center text-muted font-monospace">
                                    {{ $log->id }}
                                </td>

                                <td class="col-lg-user">
                                    <div class="text-truncate" style="line-height:1.4;">
                                        <strong class="d-block text-dark text-truncate"
                                                style="font-size:12px;"
                                                title="{{ $log->email }}">
                                            {{ $log->email }}
                                        </strong>

                                        <small class="d-block text-muted font-monospace text-truncate"
                                               style="font-size:10px;">
                                            Subscriber ID: #{{ $log->subscriber_id ?? 'N/A' }}
                                        </small>
                                    </div>
                                </td>

                                <td class="col-lg-wf">
                                    <div class="text-truncate" style="line-height:1.4;">
                                        <span class="d-block fw-semibold text-dark text-truncate"
                                              title="{{ $log->automation->name ?? 'Deleted Workflow' }}">
                                            {{ $log->automation->name ?? 'Deleted Workflow' }}
                                        </span>

                                        <small class="d-block text-muted font-monospace text-truncate"
                                               style="font-size:10px;">
                                            Step:
                                            <strong class="text-dark">{{ $log->step->step_order ?? '1' }}</strong>
                                            &bull;
                                            Delay: +{{ $log->step->delay_minutes ?? 0 }} mins
                                        </small>
                                    </div>
                                </td>

                                <td class="col-lg-entity">
                                    @if($log->order_id)
                                        <span class="badge bg-primary-subtle text-primary border px-2 py-1 text-truncate d-inline-block"
                                              style="font-size:10px; max-width:100%;"
                                              title="Order #{{ $log->order_id }}">
                                            <i class="fas fa-bag-shopping me-1"></i>
                                            Order #{{ $log->order_id }}
                                        </span>

                                    @elseif($log->abandoned_cart_id)
                                        <span class="badge bg-warning-subtle text-dark border px-2 py-1 text-truncate d-inline-block"
                                              style="font-size:10px; max-width:100%;"
                                              title="Cart #{{ $log->abandoned_cart_id }}">
                                            <i class="fas fa-cart-shopping me-1"></i>
                                            Cart #{{ $log->abandoned_cart_id }}
                                        </span>

                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td class="col-lg-status">
                                    <span class="badge {{ $statusClass }} px-2 py-1"
                                          style="font-size:10px;">
                                        @if(in_array($rawStatus, ['sent', 'delivered']))
                                            <i class="fas fa-check-circle me-1"></i>
                                        @elseif($rawStatus === 'failed')
                                            <i class="fas fa-times-circle me-1"></i>
                                        @elseif(in_array($rawStatus, ['queued', 'pending']))
                                            <i class="fas fa-clock me-1"></i>
                                        @endif

                                        {{ strtoupper($log->status ?? 'SENT') }}
                                    </span>
                                </td>

                                <td class="col-lg-date font-monospace">
                                    <span class="d-block text-dark">
                                        {{ optional($log->created_at)->format('M d, Y') ?? 'N/A' }}
                                    </span>

                                    <small class="d-block text-muted" style="font-size:10px;">
                                        {{ optional($log->created_at)->format('h:i:s A') ?? '' }}
                                    </small>
                                </td>

                                <td class="col-lg-note pe-3">
                                    @if(!empty($log->error_message))

                                        <button type="button"
                                                class="btn btn-light border text-danger log-error-btn js-log-error"
                                                data-email="{{ $log->email }}"
                                                data-workflow="{{ $log->automation->name ?? 'Workflow' }}"
                                                data-error="{{ $log->error_message }}"
                                                title="View full error message">

                                            <i class="fas fa-triangle-exclamation flex-shrink-0"></i>

                                            <span class="text-truncate">
                                                {{ $log->error_message }}
                                            </span>
                                        </button>

                                    @else

                                        <span class="text-success d-inline-flex align-items-center gap-1"
                                              style="font-size:10px;">
                                            <i class="fas fa-check-circle"></i>
                                            Successfully dispatched
                                        </span>

                                    @endif
                                </td>

                            </tr>

                        @empty

                            <tr id="emptyLogsRow">
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-list-check fa-2x mb-2 d-block opacity-25"></i>
                                    <strong class="d-block">No automation logs recorded yet.</strong>
                                    <small>Automated triggers will appear here after execution.</small>
                                </td>
                            </tr>

                        @endforelse

                        <tr id="noMatchLogsRow" style="display:none;">
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-filter-circle-xmark fa-2x mb-2 d-block opacity-25"></i>
                                <strong class="d-block">No matching execution logs found.</strong>
                                <small>Try changing your search or status filter.</small>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($logs->hasPages())
                <div class="p-3 border-top bg-light bg-opacity-50 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 no-print">

                    <small class="text-muted font-monospace">
                        Showing {{ $logs->firstItem() }}
                        to {{ $logs->lastItem() }}
                        of {{ $logs->total() }} execution logs
                    </small>

                    <div class="logs-pagination">
                        {{ $logs->links('pagination::bootstrap-5') }}
                    </div>

                </div>
            @endif

        </div>
    </div>

</div>

{{-- Error Detail Modal --}}
<div class="modal fade" id="logDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content shadow-lg p-3">

            <div class="modal-header border-0 pb-2">
                <h6 class="fw-bold text-dark mb-0" style="font-size:14px;">
                    <i class="fas fa-circle-exclamation text-danger me-2"></i>
                    Execution Detail &amp; Error Note
                </h6>

                <button type="button"
                        class="btn-close shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>
            </div>

            <div class="modal-body p-2">

                <div class="mb-3 p-3 bg-light border">
                    <small class="text-muted d-block font-monospace mb-1"
                           style="font-size:10px;">
                        RECIPIENT &amp; WORKFLOW
                    </small>

                    <strong class="d-block text-dark"
                            id="modalLogRecipient"
                            style="font-size:12px;">
                        user@example.com
                    </strong>

                    <span class="text-secondary font-monospace"
                          id="modalLogWorkflow"
                          style="font-size:11px;">
                        Workflow Name
                    </span>
                </div>

                <div>
                    <small class="text-muted d-block font-monospace mb-2"
                           style="font-size:10px;">
                        SYSTEM RESPONSE / EXCEPTION
                    </small>

                    <div class="p-3 bg-danger-subtle text-danger border border-danger-subtle font-monospace"
                         id="modalLogErrorMessage">
                        Error message will be rendered here.
                    </div>
                </div>

            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button"
                        class="btn btn-dark btn-compact px-4"
                        data-bs-dismiss="modal">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    /**
     * Show execution error details.
     * Text is inserted through innerText to prevent HTML injection.
     */
    function showLogDetailModal(email, workflow, errorMessage) {
        document.getElementById('modalLogRecipient').innerText = email || 'N/A';
        document.getElementById('modalLogWorkflow').innerText = workflow || 'Workflow';
        document.getElementById('modalLogErrorMessage').innerText =
            errorMessage || 'No error details recorded.';

        const modalEl = document.getElementById('logDetailModal');

        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    /**
     * Filter currently loaded table rows.
     */
    function filterLogsTable() {
        const query = (
            document.getElementById('logsSearchInput')?.value || ''
        ).toLowerCase().trim();

        const status = (
            document.getElementById('logsStatusFilter')?.value || 'all'
        ).toLowerCase();

        const rows = document.querySelectorAll(
            '#logs-tbody .log-entry-row'
        );

        let visibleCount = 0;

        rows.forEach(row => {
            const searchData = (row.dataset.search || '').toLowerCase();
            const rowStatus = (row.dataset.status || '').toLowerCase();

            const matchesQuery = !query || searchData.includes(query);
            const matchesStatus = status === 'all' || rowStatus === status;

            const isVisible = matchesQuery && matchesStatus;

            row.style.display = isVisible ? '' : 'none';

            if (isVisible) {
                visibleCount++;
            }
        });

        const countBadge = document.getElementById('visible-logs-count');

        if (countBadge) {
            countBadge.innerText = `${visibleCount} Showing`;
        }

        const noMatchRow = document.getElementById('noMatchLogsRow');

        if (noMatchRow) {
            noMatchRow.style.display =
                visibleCount === 0 && rows.length > 0 ? '' : 'none';
        }
    }

    /**
     * Reset search and status filters.
     */
    function resetLogsFilter() {
        const searchInput = document.getElementById('logsSearchInput');
        const statusFilter = document.getElementById('logsStatusFilter');

        if (searchInput) searchInput.value = '';
        if (statusFilter) statusFilter.value = 'all';

        filterLogsTable();
    }

    /**
     * Search event listeners.
     */
    document.addEventListener('DOMContentLoaded', function () {

        const searchInput = document.getElementById('logsSearchInput');
        const statusFilter = document.getElementById('logsStatusFilter');

        searchInput?.addEventListener('input', filterLogsTable);
        statusFilter?.addEventListener('change', filterLogsTable);

        /*
         * Delegated click handler for error details.
         * Dataset values are read safely without inline JavaScript interpolation.
         */
        document.addEventListener('click', function (event) {
            const button = event.target.closest('.js-log-error');

            if (!button) return;

            showLogDetailModal(
                button.dataset.email || '',
                button.dataset.workflow || '',
                button.dataset.error || ''
            );
        });
    });
</script>

@endsection
