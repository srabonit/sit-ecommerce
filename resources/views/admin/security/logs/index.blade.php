@extends('layouts.admin')

@section('title', 'Security Activity Logs')

@section('admin_content')

<style>
/* =========================================================
   SECURITY ACTIVITY LOGS
   Kaiadmin Lite v1.2.0 - Classic White Design
========================================================= */

#security-logs-wrapper {
    --sl-primary: var(--bs-primary, #1572e8);
    --sl-secondary: #6c757d;
    --sl-success: #31ce36;
    --sl-danger: #f25961;
    --sl-warning: #ffad46;
    --sl-info: #48abf7;
    --sl-body: #f8f9fa;
    --sl-card: #ffffff;
    --sl-heading: #212529;
    --sl-text: #495057;
    --sl-muted: #8d9498;
    --sl-border: #e5e7eb;
    --sl-radius: 5px;

    width: 100%;
    max-width: 100%;
    padding: 20px 22px !important;
    color: var(--sl-text);
    font-size: 13px;
}

/* Global scoped typography */

#security-logs-wrapper h4,
#security-logs-wrapper h3,
#security-logs-wrapper h5,
#security-logs-wrapper h6 {
    color: var(--sl-heading);
}

#security-logs-wrapper .text-muted {
    color: var(--sl-muted) !important;
}

#security-logs-wrapper .text-secondary {
    color: var(--sl-secondary) !important;
}

/* Header */

#security-logs-wrapper > .d-flex:first-child {
    padding-bottom: 15px !important;
    margin-bottom: 20px !important;
    border-color: var(--sl-border) !important;
}

#security-logs-wrapper h4 {
    font-size: clamp(17px, 1.5vw, 21px);
    font-weight: 700 !important;
    letter-spacing: -.35px;
}

#security-logs-wrapper > .d-flex:first-child p {
    font-size: 12px !important;
    margin-top: 5px !important;
}

#security-logs-wrapper .badge {
    border-radius: 4px;
    font-weight: 600;
    letter-spacing: .1px;
}

/* Buttons */

#security-logs-wrapper .btn,
#modalInspectLogDetails .btn,
#modalPurgeLogs .btn {
    min-height: 34px;
    padding: 7px 12px;
    border-radius: var(--sl-radius) !important;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.4;
    transition: background-color .2s ease, border-color .2s ease,
                color .2s ease, box-shadow .2s ease;
}

#security-logs-wrapper .btn-sm {
    min-height: 32px;
    padding: 6px 11px;
}

#security-logs-wrapper .btn:focus-visible,
#modalInspectLogDetails .btn:focus-visible,
#modalPurgeLogs .btn:focus-visible {
    outline: 2px solid var(--sl-primary);
    outline-offset: 2px;
    box-shadow: none;
}

#security-logs-wrapper .btn-outline-danger {
    border-color: #f2c6c9;
}

#security-logs-wrapper .btn-outline-danger:hover {
    color: #fff;
}

#security-logs-wrapper .btn-outline-secondary {
    border-color: var(--sl-border);
    background: #fff;
}

#security-logs-wrapper .btn-outline-secondary:hover {
    background: #f4f6f9;
    color: var(--sl-heading);
}

#security-logs-wrapper .btn-white {
    background: #fff;
    border-color: var(--sl-border) !important;
}

#security-logs-wrapper .btn-white:hover {
    background: #f5f7fa;
}

/* Summary cards */

#security-logs-wrapper .row.g-3.mb-4 > div {
    min-width: 0;
}

#security-logs-wrapper .card {
    background: var(--sl-card);
    border: 1px solid var(--sl-border) !important;
    border-radius: var(--sl-radius) !important;
    box-shadow: 0 2px 7px rgba(0, 0, 0, .035) !important;
}

#security-logs-wrapper .row.g-3.mb-4 .card {
    padding: 17px !important;
    transition: box-shadow .2s ease, transform .2s ease;
}

#security-logs-wrapper .row.g-3.mb-4 .card:hover {
    box-shadow: 0 4px 14px rgba(0, 0, 0, .065) !important;
}

#security-logs-wrapper .row.g-3.mb-4 .card span {
    font-size: 10.5px !important;
    letter-spacing: .65px !important;
    font-weight: 700 !important;
}

#security-logs-wrapper .row.g-3.mb-4 .card h3 {
    font-size: clamp(22px, 2vw, 27px);
    line-height: 1.25;
    font-weight: 700 !important;
    letter-spacing: -.5px;
}

#security-logs-wrapper .row.g-3.mb-4 .card .rounded-3 {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--sl-radius) !important;
}

#security-logs-wrapper .row.g-3.mb-4 .card .rounded-3 i {
    font-size: 19px !important;
}

/* Filter card */

#security-logs-wrapper > .card.mb-4 {
    margin-bottom: 20px !important;
}

#security-logs-wrapper > .card.mb-4 .card-body {
    padding: 17px !important;
}

#security-logs-wrapper .form-label {
    display: block;
    margin-bottom: 6px !important;
    color: #5c636a !important;
    font-size: 11.5px !important;
    font-weight: 600 !important;
}

#security-logs-wrapper .form-control,
#security-logs-wrapper .form-select,
#modalPurgeLogs .form-control,
#modalPurgeLogs .form-select {
    min-height: 36px;
    border: 1px solid #e1e5eb;
    border-radius: var(--sl-radius) !important;
    background-color: #fff;
    color: #495057;
    font-size: 12px;
    box-shadow: none;
}

#security-logs-wrapper .form-control:focus,
#security-logs-wrapper .form-select:focus,
#modalPurgeLogs .form-control:focus,
#modalPurgeLogs .form-select:focus {
    border-color: var(--sl-primary);
    box-shadow: 0 0 0 2px rgba(21, 114, 232, .09);
}

#security-logs-wrapper .input-group {
    flex-wrap: nowrap;
}

#security-logs-wrapper .input-group-text {
    min-height: 36px;
    padding: 0 11px;
    background: #f8f9fa !important;
    border: 1px solid #e1e5eb !important;
    border-right: 0 !important;
    border-radius: var(--sl-radius) 0 0 var(--sl-radius) !important;
}

#security-logs-wrapper .input-group .form-control {
    border-left: 0;
    border-radius: 0 var(--sl-radius) var(--sl-radius) 0 !important;
}

#security-logs-wrapper .font-monospace {
    font-size: 12px;
}

/* Reset filter button */

#security-logs-wrapper #btn-reset-log-filters {
    min-height: 36px;
    background: #f8f9fa;
    border-color: #e1e5eb !important;
    color: #5c636a;
}

#security-logs-wrapper #btn-reset-log-filters:hover {
    background: #eef1f5;
}

/* Table card */

#security-logs-wrapper > .card.overflow-hidden {
    overflow: hidden !important;
}

#security-logs-wrapper .table-responsive {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    overflow-y: visible;
    scrollbar-width: thin;
    scrollbar-color: #cbd0d6 #f5f6f8;
    -webkit-overflow-scrolling: touch;
}

#security-logs-wrapper .table-responsive::-webkit-scrollbar {
    height: 7px;
}

#security-logs-wrapper .table-responsive::-webkit-scrollbar-track {
    background: #f5f6f8;
}

#security-logs-wrapper .table-responsive::-webkit-scrollbar-thumb {
    background: #cbd0d6;
    border-radius: 10px;
}

#security-logs-wrapper #main-security-logs-table {
    width: 100%;
    min-width: 950px;
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
    color: #495057;
    font-size: 12px !important;
}

#security-logs-wrapper #main-security-logs-table thead th {
    padding: 12px 10px;
    background: #f8f9fa;
    color: #606770;
    border-bottom: 1px solid var(--sl-border);
    border-top: 0;
    font-size: 10.5px !important;
    font-weight: 700;
    letter-spacing: .35px;
    text-transform: uppercase;
    white-space: nowrap;
    vertical-align: middle;
}

#security-logs-wrapper #main-security-logs-table tbody td {
    padding: 10px;
    border-bottom: 1px solid #edf0f2;
    color: #555d66;
    font-size: 11.5px;
    line-height: 1.5;
    vertical-align: middle;
}

#security-logs-wrapper #main-security-logs-table tbody tr:last-child td {
    border-bottom: 0;
}

#security-logs-wrapper #main-security-logs-table tbody tr {
    transition: background-color .15s ease;
}

#security-logs-wrapper #main-security-logs-table tbody tr:hover {
    background-color: #f8faff;
}

#security-logs-wrapper #main-security-logs-table tbody td:first-child {
    white-space: nowrap;
    padding-left: 14px;
}

#security-logs-wrapper #main-security-logs-table tbody td:last-child {
    padding-right: 14px;
}

#security-logs-wrapper #main-security-logs-table .font-monospace {
    font-size: 11px;
    overflow-wrap: anywhere;
}

/* Dynamic status badges */

#security-logs-wrapper #main-security-logs-table .badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 21px;
    padding: 4px 7px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    line-height: 1.2;
}

/* Action buttons */

#security-logs-wrapper #main-security-logs-table .btn {
    min-width: 30px;
    min-height: 29px;
    padding: 4px 8px;
    font-size: 11px;
}

/* Pagination */

#security-logs-wrapper .card-footer {
    padding: 13px 16px !important;
    border-top: 1px solid var(--sl-border) !important;
}

#security-logs-wrapper #log-showing-text {
    color: #7a828a;
    font-size: 11.5px;
}

#security-logs-wrapper #log-showing-text strong {
    color: #343a40;
    font-weight: 700;
}

#security-logs-wrapper #log-pagination-container {
    max-width: 100%;
}

#security-logs-wrapper #log-pagination-container .pagination {
    margin-bottom: 0;
    flex-wrap: wrap;
    gap: 3px;
}

#security-logs-wrapper #log-pagination-container .page-link {
    min-width: 30px;
    min-height: 30px;
    padding: 6px 9px;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    border-radius: 4px !important;
    border-color: #e5e7eb;
    color: #5c636a;
    font-size: 11px;
}

#security-logs-wrapper #log-pagination-container .active > .page-link {
    background: var(--sl-primary);
    border-color: var(--sl-primary);
    color: #fff;
}

/* Modals */

#modalInspectLogDetails .modal-content,
#modalPurgeLogs .modal-content {
    border: 1px solid var(--sl-border) !important;
    border-radius: 6px !important;
    overflow: hidden;
    box-shadow: 0 10px 35px rgba(0, 0, 0, .12) !important;
}

#modalInspectLogDetails .modal-header,
#modalPurgeLogs .modal-header {
    min-height: 54px;
    padding: 12px 17px !important;
    background: #fff !important;
    border-bottom: 1px solid var(--sl-border);
}

#modalInspectLogDetails .modal-title,
#modalPurgeLogs .modal-title {
    font-size: 14px;
    font-weight: 700 !important;
}

#modalInspectLogDetails .modal-body,
#modalPurgeLogs .modal-body {
    padding: 18px !important;
}

#modalPurgeLogs .modal-footer {
    padding: 12px 17px !important;
    background: #fff !important;
    border-top: 1px solid var(--sl-border);
}

#modalPurgeLogs .form-check-input {
    cursor: pointer;
}

#modalPurgeLogs .form-check-input:focus {
    box-shadow: 0 0 0 2px rgba(21, 114, 232, .15);
}

#modalPurgeLogs .form-check-input:checked {
    background-color: var(--sl-primary);
    border-color: var(--sl-primary);
}

#modalPurgeLogs .rounded-2 {
    border-radius: var(--sl-radius) !important;
    border-color: var(--sl-border) !important;
    background: #f8f9fa !important;
}

/* Loading and AJAX states */

#security-logs-wrapper #security-log-table-body {
    transition: opacity .2s ease;
}

#modal-log-details-content {
    min-height: 100px;
}

/* Dynamic content inside details modal */

#modal-log-details-content pre {
    max-width: 100%;
    overflow-x: auto;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
}

#modal-log-details-content table {
    width: 100%;
    max-width: 100%;
}

#modal-log-details-content img {
    max-width: 100%;
    height: auto;
}

/* Tablet */

@media (max-width: 991.98px) {
    #security-logs-wrapper {
        padding: 17px !important;
    }

    #security-logs-wrapper .row.g-3.mb-4 .card {
        padding: 15px !important;
    }

    #security-logs-wrapper > .card.mb-4 .card-body {
        padding: 14px !important;
    }
}

/* Mobile */

@media (max-width: 767.98px) {
    #security-logs-wrapper {
        padding: 12px !important;
    }

    #security-logs-wrapper > .d-flex:first-child {
        align-items: flex-start !important;
        gap: 12px !important;
    }

    #security-logs-wrapper > .d-flex:first-child > div:first-child {
        width: 100%;
    }

    #security-logs-wrapper > .d-flex:first-child > .d-flex {
        width: 100%;
        justify-content: flex-start;
    }

    #security-logs-wrapper h4 {
        font-size: 17px;
    }

    #security-logs-wrapper .row.g-3.mb-4 {
        --bs-gutter-x: 10px;
        --bs-gutter-y: 10px;
    }

    #security-logs-wrapper .row.g-3.mb-4 .card {
        padding: 13px !important;
    }

    #security-logs-wrapper .row.g-3.mb-4 .card h3 {
        font-size: 23px;
    }

    #security-logs-wrapper .row.g-3.mb-4 .card .rounded-3 {
        width: 40px;
        height: 40px;
        padding: 8px !important;
    }

    #security-logs-wrapper .row.g-3.mb-4 .card .rounded-3 i {
        font-size: 16px !important;
    }

    #security-logs-wrapper > .card.mb-4 .card-body {
        padding: 12px !important;
    }

    #security-logs-wrapper #main-security-logs-table {
        min-width: 950px;
    }

    #security-logs-wrapper .card-footer {
        align-items: flex-start !important;
    }

    #security-logs-wrapper #log-pagination-container {
        width: 100%;
    }

    #security-logs-wrapper #log-pagination-container .pagination {
        justify-content: flex-start;
    }
}

/* Small mobile */

@media (max-width: 420px) {
    #security-logs-wrapper {
        padding: 9px !important;
    }

    #security-logs-wrapper > .d-flex:first-child > .d-flex {
        gap: 6px !important;
    }

    #security-logs-wrapper > .d-flex:first-child .btn {
        padding: 6px 9px;
        font-size: 11px;
    }

    #security-logs-wrapper .row.g-3.mb-4 .card span {
        font-size: 9.5px !important;
    }

    #security-logs-wrapper .row.g-3.mb-4 .card h3 {
        font-size: 21px;
    }

    #modalInspectLogDetails .modal-dialog,
    #modalPurgeLogs .modal-dialog {
        margin: 10px;
    }

    #modalInspectLogDetails .modal-header,
    #modalPurgeLogs .modal-header {
        padding: 11px 13px !important;
    }

    #modalInspectLogDetails .modal-body,
    #modalPurgeLogs .modal-body {
        padding: 13px !important;
    }

    #modalPurgeLogs .modal-footer {
        padding: 10px 13px !important;
    }
}

/* Reduced motion accessibility */

@media (prefers-reduced-motion: reduce) {
    #security-logs-wrapper *,
    #modalInspectLogDetails *,
    #modalPurgeLogs * {
        transition-duration: .01ms !important;
        animation-duration: .01ms !important;
    }
}
</style>

<div class="container-fluid px-4 py-3" id="security-logs-wrapper">

    <!-- Header & Action Toolbar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom border-light">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center">
                    <i class="fas fa-list-check text-primary me-2"></i> Security Activity Logs
                </h4>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1" style="font-size: 11px;">
                    <span id="logs-total-counter">{{ number_format($logs->total()) }}</span> Events
                </span>
            </div>
            <p class="text-muted small mb-0 mt-1">
                Monitor requests, threats, and security events.
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">

            <!-- Log cleanup -->
            <button type="button" class="btn btn-sm btn-outline-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#modalPurgeLogs">
                <i class="fas fa-trash-can me-1"></i> Clean Logs
            </button>

            <!-- Export filtered logs -->
            <a href="{{ route('admin.security.logs.export') }}" class="btn btn-sm btn-outline-secondary shadow-sm" id="btn-export-logs-csv">
                <i class="fas fa-file-csv me-1 text-success"></i> Export CSV
            </a>

            <!-- Refresh Button -->
            <button type="button" class="btn btn-sm btn-white border shadow-sm text-secondary px-2.5" id="btn-refresh-logs" title="Refresh List">
                <i class="fas fa-sync-alt" id="logs-refresh-icon"></i>
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">

        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Total Logs</span>
                        <h3 class="fw-bold mb-0 text-dark mt-1" id="stat-total-logs">{{ number_format($summary['total_logs'] ?? 0) }}</h3>
                    </div>
                    <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-database fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Blocked Today</span>
                        <h3 class="fw-bold mb-0 text-danger mt-1" id="stat-blocked-today">{{ number_format($summary['blocked_today'] ?? 0) }}</h3>
                    </div>
                    <div class="rounded-3 p-3 bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-ban fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Flagged Today</span>
                        <h3 class="fw-bold mb-0 text-warning mt-1" id="stat-flagged-today">{{ number_format($summary['flagged_today'] ?? 0) }}</h3>
                    </div>
                    <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-triangle-exclamation fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Critical Threats</span>
                        <h3 class="fw-bold mb-0 text-danger mt-1" id="stat-critical-events">{{ number_format($summary['critical_events'] ?? 0) }}</h3>
                    </div>
                    <div class="rounded-3 p-3 bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-shield-virus fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-3">
            <form id="logFilterForm" class="row g-2 align-items-end" method="GET" action="{{ route('admin.security.logs.index') }}">

                <!-- Search Box -->
                <div class="col-xl-3 col-md-4">
                    <label class="form-label small fw-semibold text-secondary mb-1">Search IP, Route, Browser</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control form-control-sm border-start-0 font-monospace" id="filter_log_search" name="search" placeholder="IP or route..." value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Traffic -->
                <div class="col-xl-2 col-md-4 col-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">Traffic</label>
                    <select class="form-select form-select-sm" id="filter_log_traffic_type" name="traffic_type">
                        <option value="all" {{ request('traffic_type', 'all') === 'all' ? 'selected' : '' }}>All</option>
                        <option value="visitors" {{ request('traffic_type') === 'visitors' ? 'selected' : '' }}>Visitors</option>
                        <option value="staff" {{ request('traffic_type') === 'staff' ? 'selected' : '' }}>Staff</option>
                    </select>
                </div>

                <!-- Activity -->
                <div class="col-xl-2 col-md-4 col-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">Activity</label>
                    <select class="form-select form-select-sm" id="filter_log_activity_type" name="activity_type">
                        <option value="">All Activities</option>
                        @foreach($activityTypes as $type)
                            <option value="{{ $type }}" {{ request('activity_type') === $type ? 'selected' : '' }}>
                                {{ str_replace('_', ' ', ucfirst($type)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Security Status -->
                <div class="col-xl-2 col-md-3 col-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">Status</label>
                    <select class="form-select form-select-sm" id="filter_log_security_status" name="security_status">
                        <option value="">All Statuses</option>
                        <option value="allowed" {{ request('security_status') === 'allowed' ? 'selected' : '' }}>Allowed</option>
                        <option value="flagged" {{ request('security_status') === 'flagged' ? 'selected' : '' }}>Flagged</option>
                        <option value="blocked" {{ request('security_status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                        <option value="challenged" {{ request('security_status') === 'challenged' ? 'selected' : '' }}>Challenged</option>
                    </select>
                </div>

                <!-- HTTP -->
                <div class="col-xl-1 col-md-3 col-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">HTTP</label>
                    <select class="form-select form-select-sm" id="filter_log_response_status" name="response_status">
                        <option value="">All</option>
                        <option value="200" {{ request('response_status') == 200 ? 'selected' : '' }}>200 OK</option>
                        <option value="403" {{ request('response_status') == 403 ? 'selected' : '' }}>403 Denied</option>
                        <option value="404" {{ request('response_status') == 404 ? 'selected' : '' }}>404 Missing</option>
                        <option value="429" {{ request('response_status') == 429 ? 'selected' : '' }}>429 Flood</option>
                        <option value="500" {{ request('response_status') == 500 ? 'selected' : '' }}>500 Error</option>
                    </select>
                </div>

                <!-- Risk -->
                <div class="col-xl-1 col-md-3 col-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">Risk</label>
                    <select class="form-select form-select-sm" id="filter_log_risk_level" name="risk_level">
                        <option value="">All</option>
                        <option value="critical" {{ request('risk_level') === 'critical' ? 'selected' : '' }}>Critical (75+)</option>
                        <option value="high" {{ request('risk_level') === 'high' ? 'selected' : '' }}>High (50–74)</option>
                        <option value="medium" {{ request('risk_level') === 'medium' ? 'selected' : '' }}>Medium (35–49)</option>
                        <option value="low" {{ request('risk_level') === 'low' ? 'selected' : '' }}>Low (<35)</option>
                    </select>
                </div>

                <!-- Action Button -->
                <div class="col-xl-1 col-md-3 col-6">
                    <button type="button" class="btn btn-sm btn-light border w-100" id="btn-reset-log-filters" title="Reset Filters">
                        <i class="fas fa-rotate-left me-1"></i> Reset
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="main-security-logs-table" style="font-size: 12px;">
                <thead class="table-light text-secondary border-bottom" style="font-size: 11px;">
                    <tr>
                        <th class="ps-3" style="width: 130px;">Date & Time</th>
                        <th style="width: 180px;">IP Address</th>
                        <th>User</th>
                        <th>Route</th>
                        <th class="text-center">HTTP</th>
                        <th>Activity</th>
                        <th class="text-center">Risk</th>
                        <th class="text-center">Status</th>
                        <th class="pe-3 text-end" style="width: 70px;">Action</th>
                    </tr>
                </thead>
                <tbody id="security-log-table-body">
                    @include('admin.security.logs.partials.log_table_rows', ['logs' => $logs])
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        <div class="card-footer bg-white border-0 py-3 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-top">
            <span class="small text-muted" id="log-showing-text">
                Showing <strong>{{ $logs->firstItem() ?? 0 }}</strong>–<strong>{{ $logs->lastItem() ?? 0 }}</strong> of <strong>{{ number_format($logs->total()) }}</strong>
            </span>
            <div id="log-pagination-container">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

</div>

<!-- Log Details Modal -->
<div class="modal fade" id="modalInspectLogDetails" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2.5 px-3 bg-light">
                <h6 class="modal-title fw-bold text-dark">
                    <i class="fas fa-fingerprint text-primary me-2"></i> Event Details
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="modal-log-details-content">
                <div class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i>
                    <p class="text-muted small mb-0">Loading details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Log Cleanup Modal -->
<div class="modal fade" id="modalPurgeLogs" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2.5 px-3 bg-light">
                <h6 class="modal-title fw-bold text-danger">
                    <i class="fas fa-trash-can me-2"></i> Clean Old Logs
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formSubmitLogCleanup">
                @csrf

                <div class="modal-body p-3">
                    <p class="small text-muted mb-3">
                        Delete old logs to save database storage.
                    </p>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Delete logs older than</label>
                        <select class="form-select form-select-sm" name="retention_days" required>
                            <option value="7">7 Days</option>
                            <option value="15">15 Days</option>
                            <option value="30" selected>30 Days (Recommended)</option>
                            <option value="60">60 Days</option>
                            <option value="90">90 Days</option>
                            <option value="180">180 Days (6 Months)</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center justify-content-between p-2.5 rounded-2 border bg-light mb-2">
                        <div>
                            <span class="fw-semibold text-dark small d-block">Keep Threat Logs</span>
                            <small class="text-muted" style="font-size: 10.5px;">Keep flagged and blocked events</small>
                        </div>
                        <div class="form-check form-switch fs-5 m-0">
                            <input class="form-check-input" type="checkbox" name="keep_flagged" value="1" checked>
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-2 px-3 bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger px-3" id="btn-submit-cleanup">Clean Logs</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    const tableBody = document.getElementById('security-log-table-body');
    const paginationContainer = document.getElementById('log-pagination-container');
    const filterForm = document.getElementById('logFilterForm');
    const searchInput = document.getElementById('filter_log_search');
    const exportBtn = document.getElementById('btn-export-logs-csv');
    const csrfToken = '{{ csrf_token() }}';
    const exportBaseUrl = `{{ route('admin.security.logs.export') }}`;

    let searchTimer = null;

    function syncExportLink(params) {
        if (exportBtn) {
            exportBtn.href = `${exportBaseUrl}?${params.toString()}`;
        }
    }

    function fetchLogsData(url = null) {
        const refreshIcon = document.getElementById('logs-refresh-icon');
        if (refreshIcon) refreshIcon.classList.add('fa-spin');

        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        const targetUrl = url || `{{ route('admin.security.logs.index') }}?${params.toString()}`;

        syncExportLink(params);
        if (tableBody) tableBody.style.opacity = '0.5';

        fetch(targetUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Unable to load logs.');
            return data;
        })
        .then(data => {
            if (refreshIcon) refreshIcon.classList.remove('fa-spin');
            if (tableBody) tableBody.style.opacity = '1';

            if (data.success) {
                if (tableBody) tableBody.innerHTML = data.html;
                if (paginationContainer && data.pagination) {
                    paginationContainer.innerHTML = data.pagination;
                }
                const counter = document.getElementById('logs-total-counter');
                if (counter && data.total !== undefined) {
                    counter.innerText = Number(data.total).toLocaleString();
                }
                const showingText = document.getElementById('log-showing-text');
                if (showingText && data.total !== undefined) {
                    showingText.innerHTML = `<strong>${Number(data.total).toLocaleString()}</strong> logs found`;
                }
            }
        })
        .catch(err => {
            if (refreshIcon) refreshIcon.classList.remove('fa-spin');
            if (tableBody) tableBody.style.opacity = '1';
            console.error('Logs fetch error:', err);
        });
    }

    function initLogEvents() {
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => fetchLogsData(), 350);
            });
        }

        document.querySelectorAll('#filter_log_traffic_type, #filter_log_activity_type, #filter_log_security_status, #filter_log_response_status, #filter_log_risk_level').forEach(sel => {
            sel.addEventListener('change', () => fetchLogsData());
        });

        if (filterForm) {
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                fetchLogsData();
            });
        }

        const resetBtn = document.getElementById('btn-reset-log-filters');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                filterForm.reset();
                fetchLogsData(`{{ route('admin.security.logs.index') }}`);
            });
        }

        document.addEventListener('click', function(e) {
            const pageLink = e.target.closest('#log-pagination-container .page-link');
            if (pageLink && pageLink.getAttribute('href')) {
                e.preventDefault();
                fetchLogsData(pageLink.getAttribute('href'));
            }
        });

        document.addEventListener('click', function(e) {
            const inspectBtn = e.target.closest('.btn-inspect-log');
            if (inspectBtn) {
                const logId = inspectBtn.getAttribute('data-id');
                const modalEl = document.getElementById('modalInspectLogDetails');
                const contentBox = document.getElementById('modal-log-details-content');

                if (modalEl && typeof bootstrap !== 'undefined') {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }

                if (!contentBox) return;

                contentBox.innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i>
                        <p class="text-muted small mb-0">Loading details...</p>
                    </div>
                `;

                fetch(`{{ url('/admin/security/logs/show') }}/${logId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(async r => {
                    const data = await r.json();
                    if (!r.ok) throw new Error(data.message || 'Unable to load details.');
                    return data;
                })
                .then(res => {
                    if (res.success && res.html) {
                        contentBox.innerHTML = res.html;
                    }
                })
                .catch(() => {
                    contentBox.innerHTML = `<div class="p-4 text-center text-danger small">Could not load event details.</div>`;
                });
            }
        });

        const cleanupForm = document.getElementById('formSubmitLogCleanup');
        if (cleanupForm) {
            cleanupForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = document.getElementById('btn-submit-cleanup');
                btn.disabled = true;
                btn.innerText = 'Cleaning...';

                const payload = {
                    retention_days: cleanupForm.querySelector('[name="retention_days"]').value,
                    keep_flagged: cleanupForm.querySelector('[name="keep_flagged"]').checked ? 1 : 0,
                };

                fetch(`{{ route('admin.security.logs.cleanup') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(async r => {
                    const data = await r.json();
                    if (!r.ok) throw new Error(data.message || 'Cleanup failed.');
                    return data;
                })
                .then(res => {
                    btn.disabled = false;
                    btn.innerText = 'Clean Logs';
                    alert(res.message || (res.success ? 'Logs cleaned successfully.' : 'Cleanup failed.'));
                    if (res.success) {
                        const modalEl = document.getElementById('modalPurgeLogs');
                        if (modalEl && typeof bootstrap !== 'undefined') {
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }
                        fetchLogsData();
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerText = 'Clean Logs';
                });
            });
        }

        const refreshBtn = document.getElementById('btn-refresh-logs');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => fetchLogsData());
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLogEvents);
    } else {
        initLogEvents();
    }
})();
</script>

@endsection