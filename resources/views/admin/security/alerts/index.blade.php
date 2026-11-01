@extends('layouts.admin')

@section('title', 'Security Alerts - ' . config('app.name'))

@section('admin_content')
<div class="container-fluid px-4 py-3" id="security-alerts-wrapper">

    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom border-light">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center">
                    <i class="fas fa-bell text-warning me-2"></i> Security Alerts
                </h4>

                <span class="badge {{ $unreadCount > 0 ? 'bg-danger' : 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' }} px-2.5 py-1"
                      id="badge-unread-alerts-counter"
                      style="font-size: 11px;">
                    <span id="text-unread-count">{{ number_format($unreadCount) }}</span> Unread
                </span>
            </div>

            <p class="text-muted small mb-0 mt-1">
                Monitor threats, suspicious activity, and security incidents.
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">

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
            <div class="card h-100 border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                            Total Alerts
                        </span>

                        <h3 class="fw-bold mb-0 text-dark mt-1" id="stat-total-alerts">
                            {{ number_format($alerts->total()) }}
                        </h3>
                    </div>

                    <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-bell fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>


        <!-- Unread Alerts -->
        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                            Unread Alerts
                        </span>

                        <h3 class="fw-bold mb-0 {{ $unreadCount > 0 ? 'text-danger' : 'text-success' }} mt-1"
                            id="stat-unread-alerts">
                            {{ number_format($unreadCount) }}
                        </h3>
                    </div>

                    <div class="rounded-3 p-3 {{ $unreadCount > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-light text-secondary' }}">
                        <i class="fas fa-envelope-open-text fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>


        <!-- Security Logs -->
        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                            Security Activity
                        </span>

                        <h3 class="fw-bold mb-0 text-secondary mt-1">
                            <a href="{{ route('admin.security.logs.index') }}"
                               class="text-decoration-none text-dark hover-primary fs-5">
                                View Logs &rarr;
                            </a>
                        </h3>
                    </div>

                    <div class="rounded-3 p-3 bg-secondary bg-opacity-10 text-secondary">
                        <i class="fas fa-list-check fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>


        <!-- Firewall -->
        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                            Firewall
                        </span>

                        <h3 class="fw-bold mb-0 text-danger mt-1">
                            <a href="{{ route('admin.security.blocking.ips') }}"
                               class="text-decoration-none text-danger hover-primary fs-5">
                                Manage Blocks &rarr;
                            </a>
                        </h3>
                    </div>

                    <div class="rounded-3 p-3 bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-shield-slash fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <!-- Filters -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-3">

            <form id="alertFilterForm"
                  class="row g-2 align-items-end"
                  method="GET"
                  action="{{ route('admin.security.alerts.index') }}">

                <!-- Search -->
                <div class="col-xl-4 col-md-5">
                    <label class="form-label small fw-semibold text-secondary mb-1">
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
                    <label class="form-label small fw-semibold text-secondary mb-1">
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
                    <label class="form-label small fw-semibold text-secondary mb-1">
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
                    <label class="form-label small fw-semibold text-secondary mb-1">
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
                    <label class="form-label small fw-semibold text-secondary mb-1">
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
    <div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden">

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