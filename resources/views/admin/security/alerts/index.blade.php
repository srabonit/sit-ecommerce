
@extends('layouts.admin')

@section('title', 'Security Alerts - ' . config('app.name'))

@section('admin_content')

<style>
/* =========================================================
   SECURITY ALERTS — KAIADMIN CLASSIC WHITE
   Scoped Page Styling | Bootstrap 5 Compatible
========================================================= */

#security-alerts-wrapper {
    --sa-primary: var(--bs-primary, #1572e8);
    --sa-success: var(--bs-success, #31ce36);
    --sa-danger: var(--bs-danger, #f25961);
    --sa-warning: var(--bs-warning, #ffad46);
    --sa-info: var(--bs-info, #48abf7);

    --sa-bg: var(--bs-body-bg, #f8f9fa);
    --sa-card: #ffffff;
    --sa-heading: #2a2f5b;
    --sa-text: #495057;
    --sa-muted: #8d9498;
    --sa-border: #ebedf2;
    --sa-radius: 5px;

    width: 100%;
    max-width: 100%;
    min-width: 0;
    padding: 20px 24px !important;
    color: var(--sa-text);
    font-size: 13px;
    overflow-x: clip;
}

/* Page Header */
#security-alerts-wrapper .sa-page-header {
    padding-bottom: 17px;
    margin-bottom: 22px;
    border-bottom: 1px solid var(--sa-border);
}

#security-alerts-wrapper h4 {
    color: var(--sa-heading) !important;
    font-size: clamp(19px, 2vw, 23px);
    font-weight: 700;
    letter-spacing: -0.35px;
}

#security-alerts-wrapper .sa-page-description {
    color: var(--sa-muted);
    font-size: 12px;
    margin-top: 5px;
}

#security-alerts-wrapper .sa-header-actions {
    gap: 8px;
}

#security-alerts-wrapper .btn {
    min-height: 35px;
    border-radius: var(--sa-radius);
    font-size: 12px;
    font-weight: 600;
    padding: 7px 12px;
    box-shadow: none !important;
    transition: background-color .2s ease, border-color .2s ease;
}

#security-alerts-wrapper .btn-sm {
    min-height: 32px;
    padding: 6px 10px;
}

#security-alerts-wrapper .btn:focus-visible,
#security-alerts-wrapper a:focus-visible,
#security-alerts-wrapper input:focus-visible,
#security-alerts-wrapper select:focus-visible {
    outline: 2px solid var(--sa-primary);
    outline-offset: 2px;
}

/* Unread Badge */
#security-alerts-wrapper #badge-unread-alerts-counter {
    border-radius: 4px;
    font-size: 10px !important;
    font-weight: 700;
    padding: 5px 8px !important;
    letter-spacing: .15px;
}

/* Summary Cards */
#security-alerts-wrapper .sa-stat-card {
    background: var(--sa-card);
    border: 1px solid var(--sa-border) !important;
    border-radius: var(--sa-radius) !important;
    padding: 17px !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .035) !important;
    transition: box-shadow .2s ease, transform .2s ease;
}

#security-alerts-wrapper .sa-stat-card:hover {
    box-shadow: 0 5px 15px rgba(0, 0, 0, .065) !important;
    transform: translateY(-2px);
}

#security-alerts-wrapper .sa-stat-label {
    display: block;
    color: var(--sa-muted);
    font-size: 10px !important;
    font-weight: 700;
    letter-spacing: .65px;
    line-height: 1.5;
}

#security-alerts-wrapper .sa-stat-value {
    color: var(--sa-heading);
    font-size: clamp(23px, 2.5vw, 29px);
    font-weight: 700;
    line-height: 1.3;
    margin-top: 5px;
}

#security-alerts-wrapper .sa-stat-icon {
    width: 48px;
    height: 48px;
    min-width: 48px;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    border-radius: var(--sa-radius) !important;
}

#security-alerts-wrapper .sa-stat-icon i {
    font-size: 20px !important;
}

#security-alerts-wrapper .sa-stat-link {
    font-size: 13px !important;
    font-weight: 700;
    white-space: nowrap;
}

#security-alerts-wrapper .sa-stat-link:hover {
    text-decoration: underline !important;
}

/* General Cards */
#security-alerts-wrapper .card {
    background: var(--sa-card);
    border: 1px solid var(--sa-border) !important;
    border-radius: var(--sa-radius) !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .035) !important;
}

#security-alerts-wrapper .card-body {
    padding: 16px !important;
}

#security-alerts-wrapper .card-footer {
    border-top: 1px solid var(--sa-border) !important;
}

/* Filters */
#security-alerts-wrapper .sa-filter-label {
    display: block;
    color: var(--sa-text);
    font-size: 11px !important;
    font-weight: 700;
    margin-bottom: 6px !important;
}

#security-alerts-wrapper .form-control,
#security-alerts-wrapper .form-select,
#security-alerts-wrapper .input-group-text {
    min-height: 36px;
    border-color: #e6e9ef;
    border-radius: var(--sa-radius);
    font-size: 12px;
    box-shadow: none;
}

#security-alerts-wrapper .form-control,
#security-alerts-wrapper .form-select {
    color: var(--sa-text);
    background-color: #fff;
}

#security-alerts-wrapper .form-control::placeholder {
    color: #aab0b8;
    opacity: 1;
}

#security-alerts-wrapper .form-control:focus,
#security-alerts-wrapper .form-select:focus {
    border-color: var(--sa-primary);
    box-shadow: 0 0 0 2px rgba(21, 114, 232, .09);
}

#security-alerts-wrapper .input-group-text {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 11px;
    background: #f8f9fc !important;
}

#security-alerts-wrapper .input-group > .form-control {
    min-width: 0;
}

/* Table */
#security-alerts-wrapper .sa-table-card {
    overflow: hidden;
}

#security-alerts-wrapper .table-responsive {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
}

#security-alerts-wrapper #main-alerts-table {
    width: 100%;
    min-width: 850px;
    margin-bottom: 0;
    color: var(--sa-text);
    font-size: 12px !important;
    border-collapse: separate;
    border-spacing: 0;
}

#security-alerts-wrapper #main-alerts-table thead th {
    padding: 12px 10px;
    background: #f8f9fc;
    color: #6c757d;
    border-bottom: 1px solid var(--sa-border);
    font-size: 10px !important;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .35px;
    white-space: nowrap;
    vertical-align: middle;
}

#security-alerts-wrapper #main-alerts-table tbody td {
    padding: 11px 10px;
    border-bottom: 1px solid #f0f1f5;
    font-size: 12px;
    line-height: 1.5;
    vertical-align: middle;
    overflow-wrap: anywhere;
}

#security-alerts-wrapper #main-alerts-table tbody tr:last-child td {
    border-bottom: 0;
}

#security-alerts-wrapper #main-alerts-table tbody tr {
    transition: background-color .15s ease;
}

#security-alerts-wrapper #main-alerts-table tbody tr:hover {
    background-color: #f8faff;
}

#security-alerts-wrapper #main-alerts-table th:first-child,
#security-alerts-wrapper #main-alerts-table td:first-child {
    padding-left: 15px;
}

#security-alerts-wrapper #main-alerts-table th:last-child,
#security-alerts-wrapper #main-alerts-table td:last-child {
    padding-right: 15px;
}

#security-alerts-wrapper #main-alerts-table .badge {
    display: inline-block;
    border-radius: 4px;
    padding: 5px 7px;
    font-size: 10px;
    font-weight: 700;
    line-height: 1.3;
    white-space: nowrap;
}

/* Table Actions */
#security-alerts-wrapper #main-alerts-table .btn {
    min-height: 28px;
    padding: 4px 8px;
    font-size: 11px;
}

#security-alerts-wrapper #main-alerts-table .btn i {
    font-size: 11px;
}

/* Pagination */
#security-alerts-wrapper #alert-showing-text {
    color: var(--sa-muted);
    font-size: 11px !important;
    line-height: 1.6;
}

#security-alerts-wrapper #alert-showing-text strong {
    color: var(--sa-heading);
    font-weight: 700;
}

#security-alerts-wrapper #alert-pagination-container {
    max-width: 100%;
}

#security-alerts-wrapper #alert-pagination-container .pagination {
    flex-wrap: wrap;
    gap: 3px;
    margin-bottom: 0;
}

#security-alerts-wrapper #alert-pagination-container .page-link {
    min-width: 30px;
    min-height: 30px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 4px !important;
    border-color: var(--sa-border);
    color: var(--sa-primary);
    font-size: 11px;
    padding: 5px 9px;
}

#security-alerts-wrapper #alert-pagination-container .active .page-link {
    background: var(--sa-primary);
    border-color: var(--sa-primary);
    color: #fff;
}

#security-alerts-wrapper #alert-pagination-container .disabled .page-link {
    color: #adb5bd;
}

/* Loading State */
#security-alerts-wrapper #alert-table-body {
    transition: opacity .2s ease;
}

/* Scrollbar */
#security-alerts-wrapper .table-responsive::-webkit-scrollbar {
    height: 7px;
}

#security-alerts-wrapper .table-responsive::-webkit-scrollbar-track {
    background: #f1f2f6;
}

#security-alerts-wrapper .table-responsive::-webkit-scrollbar-thumb {
    background: #c9ced8;
    border-radius: 10px;
}

/* =========================================================
   RESPONSIVE DESIGN
========================================================= */

/* Large Desktop */
@media (min-width: 1400px) {
    #security-alerts-wrapper {
        padding: 24px 28px !important;
    }

    #security-alerts-wrapper .sa-stat-card {
        padding: 20px !important;
    }
}

/* Laptop / Desktop */
@media (max-width: 1199.98px) {
    #security-alerts-wrapper {
        padding: 18px !important;
    }
}

/* Tablet */
@media (max-width: 767.98px) {
    #security-alerts-wrapper {
        padding: 15px 12px !important;
    }

    #security-alerts-wrapper .sa-page-header {
        align-items: flex-start !important;
    }

    #security-alerts-wrapper .sa-stat-card {
        padding: 14px !important;
    }

    #security-alerts-wrapper .sa-stat-icon {
        width: 42px;
        height: 42px;
        min-width: 42px;
    }

    #security-alerts-wrapper .sa-stat-icon i {
        font-size: 17px !important;
    }

    #security-alerts-wrapper .card-body {
        padding: 13px !important;
    }

    #security-alerts-wrapper #main-alerts-table {
        min-width: 850px;
    }
}

/* Mobile */
@media (max-width: 575.98px) {
    #security-alerts-wrapper {
        padding: 12px 9px !important;
    }

    #security-alerts-wrapper .sa-page-header {
        gap: 12px !important;
        margin-bottom: 16px;
        padding-bottom: 13px;
    }

    #security-alerts-wrapper h4 {
        font-size: 19px;
    }

    #security-alerts-wrapper .sa-page-description {
        font-size: 11px;
    }

    #security-alerts-wrapper .sa-header-actions {
        width: 100%;
    }

    #security-alerts-wrapper .sa-header-actions .btn {
        flex: 1;
    }

    #security-alerts-wrapper .sa-stat-card {
        padding: 12px !important;
    }

    #security-alerts-wrapper .sa-stat-label {
        font-size: 9px !important;
        letter-spacing: .35px;
    }

    #security-alerts-wrapper .sa-stat-value {
        font-size: 23px;
    }

    #security-alerts-wrapper .sa-stat-link {
        font-size: 11px !important;
    }

    #security-alerts-wrapper .row.g-3 {
        --bs-gutter-x: 10px;
        --bs-gutter-y: 10px;
    }

    #security-alerts-wrapper .card-body {
        padding: 11px !important;
    }

    #security-alerts-wrapper .form-control,
    #security-alerts-wrapper .form-select,
    #security-alerts-wrapper .input-group-text {
        min-height: 38px;
    }

    #security-alerts-wrapper #alert-showing-text {
        width: 100%;
    }

    #security-alerts-wrapper #alert-pagination-container {
        width: 100%;
    }

    #security-alerts-wrapper #alert-pagination-container .pagination {
        justify-content: flex-start;
    }
}

/* Small Mobile */
@media (max-width: 359.98px) {
    #security-alerts-wrapper {
        padding: 10px 7px !important;
    }

    #security-alerts-wrapper .sa-stat-card {
        padding: 10px !important;
    }

    #security-alerts-wrapper .sa-stat-icon {
        width: 35px;
        height: 35px;
        min-width: 35px;
    }

    #security-alerts-wrapper .sa-stat-icon i {
        font-size: 15px !important;
    }

    #security-alerts-wrapper .sa-stat-value {
        font-size: 20px;
    }

    #security-alerts-wrapper .btn {
        font-size: 11px;
    }
}
</style>

<div class="container-fluid px-4 py-3" id="security-alerts-wrapper">

    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom border-light sa-page-header">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center">
                    <i class="fas fa-bell text-warning me-2"></i> Security Alerts
                </h4>

                <span class="badge {{ $unreadCount > 0 ? 'bg-danger' : 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' }} px-2.5 py-1"
                      id="badge-unread-alerts-counter"
                      style="font-size: 11px;">
                    <span id="text-unread-count">{{ number_format($unreadCount) }}</span> Unread
                </span>
            </div>

            <p class="text-muted small mb-0 mt-1 sa-page-description">
                Monitor threats and security incidents.
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 sa-header-actions">

            <button type="button"
                    class="btn btn-sm btn-outline-primary shadow-sm {{ $unreadCount === 0 ? 'd-none' : '' }}"
                    id="btn-mark-all-alerts-read">
                <i class="fas fa-check-double me-1"></i> Mark All Read
            </button>

            <button type="button"
                    class="btn btn-sm btn-white border shadow-sm text-secondary px-2.5"
                    id="btn-refresh-alerts"
                    title="Refresh Alerts">
                <i class="fas fa-sync-alt" id="alerts-refresh-icon"></i>
            </button>

        </div>
    </div>


    <!-- Summary Cards -->
    <div class="row g-3 mb-4">

        <!-- Total Alerts -->
        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 sa-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-w-0">
                        <span class="text-secondary fw-semibold text-uppercase sa-stat-label">
                            Total Alerts
                        </span>

                        <h3 class="fw-bold mb-0 text-dark sa-stat-value" id="stat-total-alerts">
                            {{ number_format($alerts->total()) }}
                        </h3>
                    </div>

                    <div class="sa-stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-bell"></i>
                    </div>
                </div>
            </div>
        </div>


        <!-- Unread Alerts -->
        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 sa-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-w-0">
                        <span class="text-secondary fw-semibold text-uppercase sa-stat-label">
                            Unread Alerts
                        </span>

                        <h3 class="fw-bold mb-0 {{ $unreadCount > 0 ? 'text-danger' : 'text-success' }} sa-stat-value"
                            id="stat-unread-alerts">
                            {{ number_format($unreadCount) }}
                        </h3>
                    </div>

                    <div class="sa-stat-icon {{ $unreadCount > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-light text-secondary' }}">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                </div>
            </div>
        </div>


        <!-- Security Logs -->
        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 sa-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-w-0">
                        <span class="text-secondary fw-semibold text-uppercase sa-stat-label">
                            Security Activity
                        </span>

                        <h3 class="fw-bold mb-0 text-secondary sa-stat-value">
                            <a href="{{ route('admin.security.logs.index') }}"
                               class="text-decoration-none text-dark hover-primary sa-stat-link">
                                View Logs &rarr;
                            </a>
                        </h3>
                    </div>

                    <div class="sa-stat-icon bg-secondary bg-opacity-10 text-secondary">
                        <i class="fas fa-list-check"></i>
                    </div>
                </div>
            </div>
        </div>


        <!-- Firewall -->
        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 sa-stat-card">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-w-0">
                        <span class="text-secondary fw-semibold text-uppercase sa-stat-label">
                            Firewall
                        </span>

                        <h3 class="fw-bold mb-0 text-danger sa-stat-value">
                            <a href="{{ route('admin.security.blocking.ips') }}"
                               class="text-decoration-none text-danger hover-primary sa-stat-link">
                                Manage Blocks &rarr;
                            </a>
                        </h3>
                    </div>

                    <div class="sa-stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-shield-slash"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">

            <form id="alertFilterForm"
                  class="row g-2 align-items-end"
                  method="GET"
                  action="{{ route('admin.security.alerts.index') }}">

                <!-- Search -->
                <div class="col-xl-4 col-md-5">
                    <label class="form-label small fw-semibold text-secondary mb-1 sa-filter-label">
                        Search
                    </label>

                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>

                        <input type="text"
                               class="form-control form-control-sm border-start-0"
                               id="filter_alert_search"
                               name="search"
                               placeholder="Search incident or IP..."
                               value="{{ request('search') }}">
                    </div>
                </div>


                <!-- Category -->
                <div class="col-xl-2 col-md-3 col-6">
                    <label class="form-label small fw-semibold text-secondary mb-1 sa-filter-label">
                        Category
                    </label>

                    <select class="form-select form-select-sm"
                            id="filter_alert_event_type"
                            name="event_type">

                        <option value="">All Categories</option>

                        @foreach($eventTypes as $eType)
                            <option value="{{ $eType }}"
                                {{ request('event_type') === $eType ? 'selected' : '' }}>
                                {{ str_replace('_', ' ', ucfirst($eType)) }}
                            </option>
                        @endforeach

                    </select>
                </div>


                <!-- Severity -->
                <div class="col-xl-2 col-md-4 col-6">
                    <label class="form-label small fw-semibold text-secondary mb-1 sa-filter-label">
                        Severity
                    </label>

                    <select class="form-select form-select-sm"
                            id="filter_alert_risk_level"
                            name="risk_level">

                        <option value="">All Levels</option>

                        <option value="critical" {{ request('risk_level') === 'critical' ? 'selected' : '' }}>
                            Critical
                        </option>

                        <option value="high" {{ request('risk_level') === 'high' ? 'selected' : '' }}>
                            High
                        </option>

                        <option value="medium" {{ request('risk_level') === 'medium' ? 'selected' : '' }}>
                            Medium
                        </option>

                        <option value="low" {{ request('risk_level') === 'low' ? 'selected' : '' }}>
                            Low / Info
                        </option>

                    </select>
                </div>


                <!-- Status -->
                <div class="col-xl-2 col-md-4 col-6">
                    <label class="form-label small fw-semibold text-secondary mb-1 sa-filter-label">
                        Status
                    </label>

                    <select class="form-select form-select-sm"
                            id="filter_alert_status"
                            name="status">

                        <option value="">All Statuses</option>

                        <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>
                            Unread
                        </option>

                        <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>
                            Read
                        </option>

                    </select>
                </div>


                <!-- Per Page -->
                <div class="col-xl-1 col-md-2 col-6">
                    <label class="form-label small fw-semibold text-secondary mb-1 sa-filter-label">
                        Per Page
                    </label>

                    <select class="form-select form-select-sm"
                            id="filter_alert_per_page"
                            name="per_page">

                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>

                    </select>
                </div>


                <!-- Reset -->
                <div class="col-xl-1 col-md-6 col-12">
                    <button type="button"
                            class="btn btn-sm btn-light border w-100"
                            id="btn-reset-alert-filters">
                        <i class="fas fa-rotate-left me-1"></i> Reset
                    </button>
                </div>

            </form>

        </div>
    </div>


    <!-- Alerts Table -->
    <div class="card sa-table-card">

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0"
                   id="main-alerts-table"
                   style="font-size: 12px;">

                <thead class="table-light text-secondary border-bottom" style="font-size: 11px;">
                    <tr>
                        <th class="ps-3" style="width: 130px;">Severity</th>
                        <th>Incident</th>
                        <th>Category</th>
                        <th>Source IP</th>
                        <th>Target User</th>
                        <th>Date & Time</th>
                        <th class="pe-3 text-end" style="width: 120px;">Actions</th>
                    </tr>
                </thead>

                <tbody id="alert-table-body">
                    @include('admin.security.alerts.partials.alert_table_rows', ['alerts' => $alerts])
                </tbody>

            </table>
        </div>


        <!-- Table Footer -->
        <div class="card-footer bg-white border-0 py-3 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-top">

            <span class="small text-muted" id="alert-showing-text">
                Showing
                <strong>{{ $alerts->firstItem() ?? 0 }}</strong>
                to
                <strong>{{ $alerts->lastItem() ?? 0 }}</strong>
                of
                <strong>{{ number_format($alerts->total()) }}</strong>
                alerts
            </span>

            <div id="alert-pagination-container">
                {{ $alerts->links('pagination::bootstrap-5') }}
            </div>

        </div>

    </div>

</div>


<script>
(function() {

    const tableBody = document.getElementById('alert-table-body');
    const paginationContainer = document.getElementById('alert-pagination-container');
    const filterForm = document.getElementById('alertFilterForm');
    const searchInput = document.getElementById('filter_alert_search');
    const csrfToken = '{{ csrf_token() }}';

    let searchTimer = null;


    // Fetch Alerts
    function fetchAlertsData(url = null) {

        const refreshIcon = document.getElementById('alerts-refresh-icon');

        if (refreshIcon) {
            refreshIcon.classList.add('fa-spin');
        }

        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);

        const targetUrl = url || `{{ route('admin.security.alerts.index') }}?${params.toString()}`;

        if (tableBody) {
            tableBody.style.opacity = '0.5';
        }

        fetch(targetUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })

        .then(res => res.json())

        .then(data => {

            if (refreshIcon) {
                refreshIcon.classList.remove('fa-spin');
            }

            if (tableBody) {
                tableBody.style.opacity = '1';
            }

            if (data.success) {

                if (tableBody) {
                    tableBody.innerHTML = data.html;
                }

                if (paginationContainer && data.pagination) {
                    paginationContainer.innerHTML = data.pagination;
                }

                const counter = document.getElementById('stat-total-alerts');

                if (counter && data.total !== undefined) {
                    counter.innerText = Number(data.total).toLocaleString();
                }

                const showing = document.getElementById('alert-showing-text');

                if (showing && data.total !== undefined) {
                    showing.innerHTML = `Showing results of <strong>${Number(data.total).toLocaleString()}</strong> alerts`;
                }

            }

        })

        .catch(err => {

            if (refreshIcon) {
                refreshIcon.classList.remove('fa-spin');
            }

            if (tableBody) {
                tableBody.style.opacity = '1';
            }

            console.error('Alerts fetch error:', err);

        });

    }


    // Initialize Events
    function initAlertEvents() {

        // Live Search
        if (searchInput) {

            searchInput.addEventListener('input', function() {

                clearTimeout(searchTimer);

                searchTimer = setTimeout(() => {
                    fetchAlertsData();
                }, 350);

            });

        }


        // Filter Changes
        document.querySelectorAll(
            '#filter_alert_event_type, #filter_alert_risk_level, #filter_alert_status, #filter_alert_per_page'
        ).forEach(sel => {

            sel.addEventListener('change', () => {
                fetchAlertsData();
            });

        });


        // Form Submit
        if (filterForm) {

            filterForm.addEventListener('submit', function(e) {

                e.preventDefault();
                fetchAlertsData();

            });

        }


        // Reset Filters
        const resetBtn = document.getElementById('btn-reset-alert-filters');

        if (resetBtn) {

            resetBtn.addEventListener('click', function() {

                filterForm.reset();

                fetchAlertsData(`{{ route('admin.security.alerts.index') }}`);

            });

        }


        // Refresh
        const refreshBtn = document.getElementById('btn-refresh-alerts');

        if (refreshBtn) {

            refreshBtn.addEventListener('click', () => {
                fetchAlertsData();
            });

        }


        // Pagination
        document.addEventListener('click', function(e) {

            const pageLink = e.target.closest('#alert-pagination-container .page-link');

            if (pageLink && pageLink.getAttribute('href')) {

                e.preventDefault();

                fetchAlertsData(pageLink.getAttribute('href'));

            }

        });


        // Mark Individual Alert as Read
        document.addEventListener('click', function(e) {

            const readBtn = e.target.closest('.btn-mark-alert-read');

            if (readBtn) {

                const id = readBtn.getAttribute('data-id');

                readBtn.disabled = true;

                fetch(`{{ url('/admin/security/alerts/mark-read') }}/${id}`, {

                    method: 'POST',

                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }

                })

                .then(r => r.json())

                .then(res => {

                    if (res.success) {

                        const row = document.getElementById(`alert-row-${id}`);

                        if (row) {

                            row.classList.remove(
                                'table-warning',
                                'bg-opacity-10',
                                'fw-semibold'
                            );

                            row.classList.add('text-muted');

                            const dot = row.querySelector('.status-dot');

                            if (dot) {

                                dot.classList.remove('bg-danger');

                                dot.classList.add(
                                    'bg-secondary',
                                    'opacity-25'
                                );

                            }

                            readBtn.remove();

                        }


                        // Update Unread Counters
                        const unreadBadges = document.querySelectorAll(
                            '#text-unread-count, #stat-unread-alerts'
                        );

                        unreadBadges.forEach(el => {

                            let cur = parseInt(el.innerText.replace(/,/g, '')) || 0;

                            el.innerText = Math.max(0, cur - 1).toLocaleString();

                        });

                    }

                });

            }


            // Delete / Dismiss Alert
            const delBtn = e.target.closest('.btn-delete-alert-row');

            if (delBtn) {

                const id = delBtn.getAttribute('data-id');

                if (confirm('Remove this security alert?')) {

                    delBtn.disabled = true;

                    fetch(`{{ url('/admin/security/alerts/destroy') }}/${id}`, {

                        method: 'DELETE',

                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }

                    })

                    .then(r => r.json())

                    .then(res => {

                        if (res.success) {

                            const row = document.getElementById(`alert-row-${id}`);

                            if (row) {
                                row.remove();
                            }

                            fetchAlertsData();

                        }

                    });

                }

            }

        });


        // Mark All as Read
        const markAllBtn = document.getElementById('btn-mark-all-alerts-read');

        if (markAllBtn) {

            markAllBtn.addEventListener('click', function() {

                if (confirm('Mark all alerts as read?')) {

                    markAllBtn.disabled = true;
                    markAllBtn.innerText = 'Updating...';

                    fetch(`{{ route('admin.security.alerts.mark_all_read') }}`, {

                        method: 'POST',

                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }

                    })

                    .then(r => r.json())

                    .then(res => {

                        alert(res.message);

                        if (res.success) {

                            fetchAlertsData();

                            markAllBtn.classList.add('d-none');

                            const unreadBadges = document.querySelectorAll(
                                '#text-unread-count, #stat-unread-alerts'
                            );

                            unreadBadges.forEach(el => {
                                el.innerText = '0';
                            });

                        }

                    })

                    .catch(() => {

                        markAllBtn.disabled = false;
                        markAllBtn.innerText = 'Mark All Read';

                    });

                }

            });

        }

    }


    // Start
    if (document.readyState === 'loading') {

        document.addEventListener('DOMContentLoaded', initAlertEvents);

    } else {

        initAlertEvents();

    }

})();
</script>
@endsection
