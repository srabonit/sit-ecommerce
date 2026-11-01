@extends('layouts.admin')

@section('title', 'Security Activity Logs')

@section('admin_content')
<div class="container-fluid px-4 py-3" id="security-logs-wrapper">
    <!-- Header & Action Toolbar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom border-light">
        <div>
            <div class="d-flex align-items-center gap-2">
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

    <!-- Summary -->
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
                        <option value="">All Statuss</option>
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

    <!-- Logs table -->
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

<!-- Log details -->
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

<!-- Log cleanup -->
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