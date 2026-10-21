@extends('layouts.admin')

@section('title', 'Live Active User Sessions - ' . config('app.name'))

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #liveSessionsRoot .btn,
    #liveSessionsRoot .btn-sm,
    #liveSessionsRoot button,
    #liveSessionsRoot .form-control,
    #liveSessionsRoot .form-select,
    #liveSessionsRoot .badge,
    #liveSessionsRoot .input-group,
    #liveSessionsRoot .input-group-text,
    #liveSessionsRoot .alert,
    #sessionConfirmModal .btn {
        border-radius: 5px !important;
    }

    #liveSessionsRoot .session-card-table,
    #liveSessionsRoot .stat-pill,
    #liveSessionsRoot .filter-card-box,
    #sessionConfirmModal .modal-content {
        border-radius: 8px !important;
    }

    /* Page Container */
    .session-page-container {
        padding: 18px 20px;
    }

    /* Stat Cards */
    .stat-pill {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        height: 100%;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-pill:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
    }
    .stat-icon-wrap {
        width: 40px;
        height: 40px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        flex-shrink: 0;
    }

    /* Filter Card */
    .filter-card-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        padding: 16px 18px;
        margin-bottom: 18px;
    }

    #liveSessionsRoot .form-control,
    #liveSessionsRoot .form-select {
        font-size: 12.5px;
        border-color: #cbd5e1;
        padding: 6px 11px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    #liveSessionsRoot .form-control:focus,
    #liveSessionsRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Sessions Table Card */
    .session-card-table {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        position: relative;
        margin-bottom: 20px;
    }

    .custom-session-table th {
        background-color: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 11px 14px;
        white-space: nowrap;
    }

    .custom-session-table td {
        padding: 11px 14px;
        vertical-align: middle;
        font-size: 12.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }

    .custom-session-table tbody tr:hover {
        background-color: #fafbfc;
    }

    .badge-soft-primary {
        background: #e0f2fe;
        color: #0284c7;
        border: 1px solid #bae6fd;
    }

    /* Live Pulse Indicator */
    .pulse-dot {
        width: 8px;
        height: 8px;
        background-color: #22c55e;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
        animation: pulseAnimation 1.6s infinite;
    }
    @keyframes pulseAnimation {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }

    /* Safe Bengali Digits */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* AJAX Loading Spinner Overlay */
    #sessionLoadingOverlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(2px);
        z-index: 20;
        display: none;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 767.98px) {
        .session-page-container {
            padding: 12px 10px;
        }
        .session-header-actions {
            width: 100%;
        }
        .session-header-actions .btn {
            width: 100%;
            justify-content: center;
            text-align: center;
            padding: 7px 12px;
            font-size: 12px;
        }
        .stat-pill {
            padding: 12px 14px;
        }
        .stat-pill h5 {
            font-size: 1.2rem;
        }
        .stat-icon-wrap {
            width: 36px;
            height: 36px;
            font-size: 15px;
        }
        .filter-card-box {
            padding: 12px 14px !important;
            margin-bottom: 14px;
        }
        .custom-session-table th,
        .custom-session-table td {
            padding: 9px 10px;
            font-size: 12px;
        }
    }
</style>

<div class="container-fluid session-page-container" id="liveSessionsRoot">
    <!-- Top Header & Action Bar -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2.5 mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-0.5 d-flex align-items-center" style="font-size: 18px;">
                <i class="fas fa-tower-broadcast text-success me-2"></i>
                <span>Live Employee Monitoring</span>
                <span class="pulse-dot ms-2" title="Real-time Surveillance"></span>
            </h5>
            <small class=" mb-1" style="font-size: 11.5px; line-height: 1.35;">Real-time admin or employee login session monitoring</small>
        </div>
        <div class="session-header-actions d-flex gap-2">
            <button type="button" class="btn mb-1 btn-light btn-sm px-3 py-1.5 border text-muted fw-semibold shadow-xs d-inline-flex align-items-center" onclick="triggerSessionFilterAjax(true)">
                <i class="fas fa-rotate me-2"></i>
                <span>Refresh List</span>
            </button>
        </div>
    </div>

    <!-- Alert Notification Box -->
    <div id="sessionAlertBox" class="alert d-none alert-dismissible fade show mb-3 small py-2 px-3 border-0 shadow-xs align-items-center gap-2" role="alert">
        <i id="sessionAlertIcon" class="fas fa-circle-check fs-6 flex-shrink-0 me-1"></i>
        <div id="sessionAlertMessage" class="fw-semibold"></div>
        <button type="button" class="btn-close btn-sm shadow-none ms-auto py-2" onclick="document.getElementById('sessionAlertBox').classList.add('d-none')"></button>
    </div>

    <!-- Quick Stats Cards (Stacked on Narrow Mobile to Prevent Clipping) -->
    <div class="row g-2.5 mb-3">
        <div class="col-12 col-sm-6 col-lg-3 mb-2">
            <div class="stat-pill">
                <div class="min-w-0 pe-1">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 11px;">Active Logged-In Staff</small>
                    <h5 class="fw-bold mb-0 text-success font-monospace bn-digit mt-0.5" id="stat-active-staff">{{ $activeCount ?? 0 }}</h5>
                </div>
                <div class="stat-icon-wrap bg-success-subtle text-success"><i class="fas fa-users-viewfinder"></i></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 mb-2">
            <div class="stat-pill">
                <div class="min-w-0 pe-1">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 11px;">Connected Devices</small>
                    <h5 class="fw-bold mb-0 text-primary font-monospace bn-digit mt-0.5" id="stat-total-devices">{{ $totalDevices ?? 0 }}</h5>
                </div>
                <div class="stat-icon-wrap bg-primary-subtle text-primary"><i class="fas fa-laptop-code"></i></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 mb-2">
            <div class="stat-pill">
                <div class="min-w-0 pe-1">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 11px;">Desktop Clients</small>
                    <h5 class="fw-bold mb-0 text-dark font-monospace bn-digit mt-0.5" id="stat-desktop-clients">{{ $desktopCount ?? 0 }}</h5>
                </div>
                <div class="stat-icon-wrap bg-secondary-subtle text-dark"><i class="fas fa-display"></i></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 mb-2">
            <div class="stat-pill">
                <div class="min-w-0 pe-1">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 11px;">Mobile & Handheld</small>
                    <h5 class="fw-bold mb-0 text-warning font-monospace bn-digit mt-0.5" id="stat-mobile-clients">{{ $mobileCount ?? 0 }}</h5>
                </div>
                <div class="stat-icon-wrap bg-warning-subtle text-warning"><i class="fas fa-mobile-screen-button"></i></div>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar Card -->
    <div class="filter-card-box">
        <form id="sessionFilterForm" onsubmit="event.preventDefault(); triggerSessionFilterAjax();">
            <div class="row g-2 align-items-center">
                <!-- Search Box -->
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm overflow-hidden border shadow-xs">
                        <span class="input-group-text bg-white border-0 text-muted ps-2.5"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                        <input type="text" name="search" id="filter_session_search" class="form-control border-0 py-1.5 shadow-none" 
                               value="{{ request('search') }}" placeholder="Search by staff name, email, IP..." autocomplete="off">
                    </div>
                </div>

                <!-- Device Filter -->
                <div class="col-6 col-md-3">
                    <select name="device" id="filter_session_device" class="form-select form-select-sm py-1.5 shadow-none">
                        <option value="all">All Devices</option>
                        <option value="Desktop" {{ request('device') === 'Desktop' ? 'selected' : '' }}>Desktop</option>
                        <option value="Mobile" {{ request('device') === 'Mobile' ? 'selected' : '' }}>Mobile</option>
                        <option value="Tablet" {{ request('device') === 'Tablet' ? 'selected' : '' }}>Tablet</option>
                    </select>
                </div>

                <!-- Role Filter -->
                <div class="col-6 col-md-3">
                    <select name="role" id="filter_session_role" class="form-select form-select-sm py-1.5 shadow-none">
                        <option value="all">All Staff Roles</option>
                        @foreach($roles as $r)
                            <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>
                                {{ ucwords(str_replace(['_', '-'], ' ', $r)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="col-12 col-md-2 d-flex gap-1.5 justify-content-end">
                    <button type="submit" class="btn btn-primary btn-sm px-3 py-1.5 w-100 fw-semibold shadow-xs" style="font-size: 12px;">Filter</button>
                    <button type="button" class="btn ms-2 btn-light btn-sm border px-2.5 py-1.5 text-muted shadow-xs" onclick="resetSessionFilterForm()" title="Reset Filters">
                        <i class="fas fa-rotate-left"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Live Sessions Table Card with Loading Overlay -->
    <div class="session-card-table overflow-hidden">
        <!-- AJAX Overlay -->
        <div id="sessionLoadingOverlay">
            <div class="spinner-border text-primary" role="status" style="width: 2.2rem; height: 2.2rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table custom-session-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="min-width: 200px;">Staff Member</th>
                        <th style="min-width: 140px;">Role & Hub</th>
                        <th style="min-width: 160px;">Device & OS</th>
                        <th style="min-width: 140px;">Browser</th>
                        <th style="min-width: 130px;">IP Address</th>
                        <th style="min-width: 140px;">Last Activity</th>
                        <th class="text-end" style="width: 130px;">Session Actions</th>
                    </tr>
                </thead>
                <tbody id="sessionTableBody">
                    @include('admin.sessions.partials.table_rows', ['sessions' => $sessions, 'currentSessionId' =>$currentSessionId])
                </tbody>
            </table>
        </div>

        <!-- Pagination Container (AJAX Driven) -->
        <div class="p-2.5 p-md-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50" id="sessionPaginationWrapper">
            <small class="text-muted bn-digit" id="sessionStatsText" style="font-size: 11.5px;">
                Showing {{ $sessions->firstItem() ?? 0 }} to {{ $sessions->lastItem() ?? 0 }} of {{$sessions->total() }} connected device(s)
            </small>
            <div id="sessionPaginationLinks">
                {{ $sessions->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal: Session Revoke Confirmation Dialog (Replaces browser confirm()) -->
<div class="modal fade" id="sessionConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2 text-danger">
                    <i class="fas fa-triangle-exclamation fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="sessionConfirmTitle" style="font-size: 14px;">Terminate Session?</h6>
                <p class="text-muted small mb-3" id="sessionConfirmMsg" style="font-size: 12px;">Are you sure you want to terminate this session?</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-sm px-3 py-1 border shadow-xs" data-bs-dismiss="modal" style="font-size: 12px;">Cancel</button>
                    <button type="button" class="btn btn-danger btn-sm px-3.5 py-1 fw-bold shadow-xs" id="btnSessionConfirmProceed" style="font-size: 12px;">Terminate</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let sessionFilterTimer = null;
    let currentSessionAjaxUrl = "{{ route('admin.sessions.index') }}";
    let pendingSessionConfirmCallback = null;

    function showSessionConfirmModal(title, message, onConfirm) {
        document.getElementById('sessionConfirmTitle').innerText = title;
        document.getElementById('sessionConfirmMsg').innerText = message;
        pendingSessionConfirmCallback = onConfirm;

        const modalEl = document.getElementById('sessionConfirmModal');
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    document.getElementById('btnSessionConfirmProceed').addEventListener('click', function () {
        const modalEl = document.getElementById('sessionConfirmModal');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();

        if (typeof pendingSessionConfirmCallback === 'function') {
            pendingSessionConfirmCallback();
            pendingSessionConfirmCallback = null;
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        // 1. Real-time debounced AJAX search filter (350ms)
        const searchInput = document.getElementById('filter_session_search');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(sessionFilterTimer);
                sessionFilterTimer = setTimeout(() => {
                    triggerSessionFilterAjax();
                }, 350);
            });
        }

        // 2. Instant AJAX trigger on dropdown filter changes
        ['filter_session_device', 'filter_session_role'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('change', () => triggerSessionFilterAjax());
            }
        });

        // 3. AJAX pagination click interceptor
        document.addEventListener('click', function (e) {
            const pageLink = e.target.closest('#sessionPaginationLinks a');
            if (pageLink) {
                e.preventDefault();
                const url = pageLink.getAttribute('href');
                if (url && url !== '#') {
                    fetchSessionsByUrl(url);
                }
            }
        });
    });

    // Submit filter form via AJAX
    function triggerSessionFilterAjax(resetPage = true) {
        const form = document.getElementById('sessionFilterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        const baseUrl = "{{ route('admin.sessions.index') }}";
        const targetUrl = baseUrl + '?' + params.toString();

        fetchSessionsByUrl(targetUrl);
    }

    // Fetch session data from specific URL
    function fetchSessionsByUrl(url) {
        currentSessionAjaxUrl = url;
        const overlay = document.getElementById('sessionLoadingOverlay');
        if (overlay) overlay.style.display = 'flex';

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('sessionTableBody').innerHTML = data.html;
                document.getElementById('sessionPaginationLinks').innerHTML = data.pagination;
                document.getElementById('sessionStatsText').innerText = 
                    `Showing ${data.firstItem} to ${data.lastItem} of ${data.total} connected device(s)`;

                // Sync live counter stats
                document.getElementById('stat-active-staff').innerText = data.activeCount;
                document.getElementById('stat-total-devices').innerText = data.totalDevices;
                document.getElementById('stat-desktop-clients').innerText = data.desktopCount;
                document.getElementById('stat-mobile-clients').innerText = data.mobileCount;

                window.history.replaceState({}, '', url);
            }
        })
        .catch(err => {
            console.error('AJAX Session Fetch Error:', err);
        })
        .finally(() => {
            if (overlay) overlay.style.display = 'none';
        });
    }

    // Reset filter form controls
    function resetSessionFilterForm() {
        document.getElementById('filter_session_search').value = '';
        document.getElementById('filter_session_device').value = 'all';
        document.getElementById('filter_session_role').value = 'all';
        triggerSessionFilterAjax();
    }

    // Terminate a single device session via AJAX
    function revokeSingleSession(sessionId, userName) {
        showSessionConfirmModal(
            'Terminate Device Session?',
            `Are you sure you want to terminate the device session for "${userName}"?`,
            () => {
                const overlay = document.getElementById('sessionLoadingOverlay');
                if (overlay) overlay.style.display = 'flex';

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

                fetch("{{ url('admin/user-sessions/revoke') }}/" + sessionId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    showSessionAlert(data.message, data.success ? 'success' : 'danger');
                    if (data.success) {
                        const rowEl = document.getElementById('session-row-' + sessionId);
                        if (rowEl) {
                            rowEl.style.transition = 'all 0.3s ease';
                            rowEl.style.opacity = '0';
                            setTimeout(() => rowEl.remove(), 300);
                        }
                        fetchSessionsByUrl(currentSessionAjaxUrl);
                    }
                })
                .catch(err => {
                    console.error(err);
                    showSessionAlert('Failed to terminate device session.', 'danger');
                })
                .finally(() => {
                    if (overlay) overlay.style.display = 'none';
                });
            }
        );
    }

    // Force logout all active devices for a user via AJAX
    function revokeAllUserSessions(userId, userName) {
        showSessionConfirmModal(
            'Force Logout All Devices?',
            `Are you sure you want to FORCE LOGOUT all active devices for "${userName}"?`,
            () => {
                const overlay = document.getElementById('sessionLoadingOverlay');
                if (overlay) overlay.style.display = 'flex';

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

                fetch("{{ url('admin/user-sessions/revoke-all') }}/" + userId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    showSessionAlert(data.message, data.success ? 'success' : 'danger');
                    if (data.success) {
                        fetchSessionsByUrl(currentSessionAjaxUrl);
                    }
                })
                .catch(err => {
                    console.error(err);
                    showSessionAlert('Failed to terminate all user sessions.', 'danger');
                })
                .finally(() => {
                    if (overlay) overlay.style.display = 'none';
                });
            }
        );
    }

    // Display alert notification banner
    function showSessionAlert(msg, type = 'success') {
        const box = document.getElementById('sessionAlertBox');
        const msgEl = document.getElementById('sessionAlertMessage');
        const icon = document.getElementById('sessionAlertIcon');

        if (box && msgEl) {
            box.className = `alert alert-${type} alert-dismissible fade show mb-3 small py-2 px-3 border-0 shadow-xs d-flex align-items-center gap-2`;
            icon.className = type === 'success' ? 'fas fa-circle-check fs-6 text-success flex-shrink-0 me-1' : 'fas fa-circle-exclamation fs-6 text-danger flex-shrink-0 me-1';
            msgEl.innerText = msg;
            box.classList.remove('d-none');

            setTimeout(() => {
                box.classList.add('d-none');
            }, 5000);
        }
    }
</script>
@endsection