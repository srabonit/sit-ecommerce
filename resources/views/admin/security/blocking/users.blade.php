
@extends('layouts.admin')

@section('title', 'Restricted User Accounts')

@section('admin_content')

<style>
    /* =========================================================
       RESTRICTED USER ACCOUNTS — KAIADMIN CLASSIC / WHITE THEME
    ========================================================= */

    #security-user-restrictions-wrapper {
        --user-primary: var(--bs-primary, #1572e8);
        --user-danger: #f25961;
        --user-text: #2a2f5b;
        --user-muted: #6b7280;
        --user-border: #ebeef4;
        --user-surface: #ffffff;
        --user-soft: #f8f9fc;
        color: var(--user-text);
        font-size: 13px;
    }

    #security-user-restrictions-wrapper .user-page-header {
        padding-bottom: 18px;
        margin-bottom: 22px;
        border-bottom: 1px solid var(--user-border);
    }

    #security-user-restrictions-wrapper .user-page-title {
        color: var(--user-text);
        font-size: 21px;
        font-weight: 700;
        letter-spacing: -.4px;
    }

    #security-user-restrictions-wrapper .user-page-subtitle {
        color: var(--user-muted);
        font-size: 12px;
        line-height: 1.6;
    }

    #security-user-restrictions-wrapper .user-title-icon {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 5px;
        background: rgba(242, 89, 97, .10);
        color: var(--user-danger);
        font-size: 15px;
    }

    #security-user-restrictions-wrapper .user-total-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 9px;
        border-radius: 5px;
        background: rgba(242, 89, 97, .09);
        color: var(--user-danger);
        border: 1px solid rgba(242, 89, 97, .18);
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    #security-user-restrictions-wrapper .btn {
        border-radius: 5px !important;
        font-size: 12px;
        font-weight: 600;
        transition: all .2s ease;
    }

    #security-user-restrictions-wrapper .btn-sm {
        min-height: 34px;
        padding: 6px 11px;
    }

    #security-user-restrictions-wrapper .btn-danger {
        background: var(--user-danger);
        border-color: var(--user-danger);
    }

    #security-user-restrictions-wrapper .btn-danger:hover {
        background: #e74750;
        border-color: #e74750;
    }

    #security-user-restrictions-wrapper .user-refresh-btn {
        width: 35px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid var(--user-border);
        color: var(--user-muted);
    }

    #security-user-restrictions-wrapper .user-refresh-btn:hover {
        background: var(--user-soft);
        color: var(--user-primary);
    }

    /* Filter card */
    #security-user-restrictions-wrapper .user-card {
        background: var(--user-surface);
        border: 1px solid var(--user-border) !important;
        border-radius: 5px !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .025) !important;
    }

    #security-user-restrictions-wrapper .user-filter-card .card-body {
        padding: 17px;
    }

    /* Form controls */
    #security-user-restrictions-wrapper .form-label {
        color: #555b73;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    #security-user-restrictions-wrapper .form-control,
    #security-user-restrictions-wrapper .form-select,
    #security-user-restrictions-wrapper .input-group-text {
        min-height: 36px;
        border-radius: 5px !important;
        border-color: #e3e7ef;
        font-size: 12px;
        box-shadow: none !important;
    }

    #security-user-restrictions-wrapper .form-control:focus,
    #security-user-restrictions-wrapper .form-select:focus {
        border-color: rgba(21, 114, 232, .55);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .08) !important;
    }

    #security-user-restrictions-wrapper .input-group-text {
        background: #f8f9fc;
        color: #9298a5;
    }

    #security-user-restrictions-wrapper .user-filter-reset {
        min-height: 36px;
        background: #f8f9fc;
        border: 1px solid #e3e7ef;
        color: #626b7a;
    }

    #security-user-restrictions-wrapper .user-filter-reset:hover {
        background: #eef1f7;
        color: var(--user-text);
    }

    /* Table */
    #security-user-restrictions-wrapper .user-table-card {
        overflow: hidden;
    }

    #security-user-restrictions-wrapper .user-table-scroll {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #security-user-restrictions-wrapper #main-user-restrictions-table {
        width: 100%;
        min-width: 1080px;
        margin-bottom: 0;
        color: #555b73;
        font-size: 12px;
    }

    #security-user-restrictions-wrapper #main-user-restrictions-table thead th {
        padding: 13px 12px;
        background: #f8f9fc;
        color: #737b8c;
        border-bottom: 1px solid var(--user-border);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .45px;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }

    #security-user-restrictions-wrapper #main-user-restrictions-table tbody td {
        padding: 13px 12px;
        border-color: #f0f1f5;
        vertical-align: middle;
    }

    #security-user-restrictions-wrapper #main-user-restrictions-table tbody tr {
        transition: background .15s ease;
    }

    #security-user-restrictions-wrapper #main-user-restrictions-table tbody tr:hover {
        background: #f9faff;
    }

    #security-user-restrictions-wrapper #main-user-restrictions-table tbody tr:last-child td {
        border-bottom: 0;
    }

    /* Table footer */
    #security-user-restrictions-wrapper .user-table-footer {
        background: #fff;
        border-top: 1px solid var(--user-border) !important;
        padding: 14px 17px;
    }

    #security-user-restrictions-wrapper #user-showing-text {
        color: var(--user-muted);
        font-size: 11px;
    }

    #security-user-restrictions-wrapper #user-showing-text strong {
        color: var(--user-text);
        font-weight: 700;
    }

    /* Pagination */
    #security-user-restrictions-wrapper #user-pagination-container .pagination {
        margin: 0;
        gap: 4px;
    }

    #security-user-restrictions-wrapper #user-pagination-container .page-link {
        min-width: 30px;
        min-height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 9px;
        border-radius: 5px !important;
        border: 1px solid #e8eaf0;
        color: #626b7a;
        font-size: 11px;
        box-shadow: none;
    }

    #security-user-restrictions-wrapper #user-pagination-container .page-item.active .page-link {
        background: var(--user-primary);
        border-color: var(--user-primary);
        color: #fff;
    }

    #security-user-restrictions-wrapper #user-pagination-container .page-item.disabled .page-link {
        color: #b7bdc9;
        background: #fafbfc;
    }

    /* Restriction modal */
    #modalStoreUserRestriction .modal-content {
        border: 1px solid var(--user-border) !important;
        border-radius: 6px !important;
        overflow: hidden;
    }

    #modalStoreUserRestriction .modal-header,
    #modalStoreUserRestriction .modal-footer {
        background: #f8f9fc !important;
        border-color: var(--user-border);
    }

    #modalStoreUserRestriction .modal-title {
        font-size: 14px;
    }

    #modalStoreUserRestriction .modal-body {
        padding: 20px !important;
    }

    #modalStoreUserRestriction .form-label {
        color: #555b73;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    #modalStoreUserRestriction .form-control,
    #modalStoreUserRestriction .form-select {
        min-height: 38px;
        border-radius: 5px !important;
        border-color: #e3e7ef;
        font-size: 12px;
        box-shadow: none;
    }

    #modalStoreUserRestriction .form-control:focus,
    #modalStoreUserRestriction .form-select:focus {
        border-color: rgba(21, 114, 232, .55);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .08);
    }

    #modalStoreUserRestriction textarea.form-control {
        min-height: auto;
    }

    #modalStoreUserRestriction .modal-footer .btn {
        min-width: 85px;
    }

    /* Responsive */
    @media (max-width: 767.98px) {
        #security-user-restrictions-wrapper {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        #security-user-restrictions-wrapper .user-page-header {
            align-items: flex-start !important;
            margin-bottom: 16px;
            padding-bottom: 15px;
        }

        #security-user-restrictions-wrapper .user-header-main {
            width: 100%;
        }

        #security-user-restrictions-wrapper .user-header-actions {
            width: 100%;
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 7px !important;
        }

        #security-user-restrictions-wrapper .user-header-actions > * {
            width: 100%;
            justify-content: center;
        }

        #security-user-restrictions-wrapper .user-header-actions #btn-refresh-users {
            grid-column: span 2;
            width: 100%;
        }

        #security-user-restrictions-wrapper .user-page-title {
            font-size: 18px;
        }

        #security-user-restrictions-wrapper .user-filter-card .card-body {
            padding: 13px;
        }

        #security-user-restrictions-wrapper .user-table-footer {
            align-items: flex-start !important;
            flex-direction: column;
        }

        #security-user-restrictions-wrapper #user-pagination-container {
            width: 100%;
            overflow-x: auto;
        }

        #security-user-restrictions-wrapper #user-pagination-container .pagination {
            flex-wrap: nowrap;
        }
    }

    @media (max-width: 420px) {
        #security-user-restrictions-wrapper .user-header-actions {
            grid-template-columns: 1fr;
        }

        #security-user-restrictions-wrapper .user-header-actions #btn-refresh-users {
            grid-column: auto;
        }

        #security-user-restrictions-wrapper .user-page-title {
            font-size: 16px;
        }

        #security-user-restrictions-wrapper .user-total-badge {
            font-size: 10px;
            padding: 4px 6px;
        }
    }
</style>

<div class="container-fluid px-4 py-3" id="security-user-restrictions-wrapper">

    <!-- Header & Action Toolbar -->
    <div class="user-page-header d-flex flex-wrap align-items-center justify-content-between gap-3">

        <div class="user-header-main">

            <div class="d-flex align-items-center flex-wrap gap-2">

                <a href="{{ route('admin.security.blocking.ips') }}"
                   class="btn btn-sm btn-light border text-secondary"
                   title="Back to Blocked IPs">
                    <i class="fas fa-arrow-left"></i>
                </a>

                <span class="user-title-icon">
                    <i class="fas fa-user-xmark"></i>
                </span>

                <h4 class="user-page-title mb-0">
                    Restricted Accounts
                </h4>

                <span class="user-total-badge">
                    <i class="fas fa-user-lock"></i>
                    <span id="user-total-counter">{{ number_format($restrictions->total()) }}</span>
                    Accounts
                </span>
            </div>

            <p class="user-page-subtitle mb-0 mt-2">
                Manage account restrictions for checkout abuse, login attempts, and suspicious activity
            </p>
        </div>

        <div class="user-header-actions d-flex flex-wrap align-items-center gap-2">

            <a href="{{ route('admin.security.blocking.ips') }}"
               class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-ban me-1 text-danger"></i>
                Blocked IPs
            </a>

            <a href="{{ route('admin.security.blocking.networks') }}"
               class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-sitemap me-1 text-danger"></i>
                Blocked Networks
            </a>

            <button type="button"
                    class="btn btn-sm btn-danger px-3"
                    data-bs-toggle="modal"
                    data-bs-target="#modalStoreUserRestriction">
                <i class="fas fa-user-slash me-1"></i>
                Restrict Account
            </button>

            <button type="button"
                    class="btn btn-sm user-refresh-btn"
                    id="btn-refresh-users"
                    title="Refresh List">
                <i class="fas fa-sync-alt" id="refresh-icon"></i>
            </button>
        </div>
    </div>

    <!-- FILTER TOOLBAR -->
    <div class="card user-card user-filter-card mb-4">

        <div class="card-body">

            <form id="userFilterForm"
                  class="row g-3 align-items-end"
                  method="GET"
                  action="{{ route('admin.security.blocking.users') }}">

                <div class="col-xl-4 col-md-5 col-12">

                    <label class="form-label">
                        Search Name / Email / Phone / Reason
                    </label>

                    <div class="input-group input-group-sm">

                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>

                        <input type="text"
                               class="form-control"
                               id="filter_user_search"
                               name="search"
                               placeholder="Name, email, phone..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-xl-3 col-md-3 col-6">

                    <label class="form-label">
                        Restriction Type
                    </label>

                    <select class="form-select form-select-sm"
                            id="filter_restriction_type"
                            name="type">

                        <option value="">All Types</option>

                        <option value="checkout_restricted" {{ request('type') === 'checkout_restricted' ? 'selected' : '' }}>
                            Checkout Restricted
                        </option>

                        <option value="temporary_lock" {{ request('type') === 'temporary_lock' ? 'selected' : '' }}>
                            Temporary Lock
                        </option>

                        <option value="permanent_suspension" {{ request('type') === 'permanent_suspension' ? 'selected' : '' }}>
                            Permanent Suspension
                        </option>

                        <option value="login_restricted" {{ request('type') === 'login_restricted' ? 'selected' : '' }}>
                            Login Restricted
                        </option>

                        <option value="form_restricted" {{ request('type') === 'form_restricted' ? 'selected' : '' }}>
                            Form Restricted
                        </option>

                        <option value="warning" {{ request('type') === 'warning' ? 'selected' : '' }}>
                            Warning
                        </option>
                    </select>
                </div>

                <div class="col-xl-2 col-md-2 col-6">

                    <label class="form-label">
                        Status
                    </label>

                    <select class="form-select form-select-sm"
                            id="filter_user_status"
                            name="status">

                        <option value="active" {{ request('status', 'active') === 'active' ? 'selected' : '' }}>
                            Active
                        </option>

                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                            Lifted / Inactive
                        </option>

                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>
                            All Records
                        </option>
                    </select>
                </div>

                <div class="col-xl-1 col-md-2 col-6">

                    <label class="form-label">
                        Per Page
                    </label>

                    <select class="form-select form-select-sm"
                            id="filter_user_per_page"
                            name="per_page">

                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <div class="col-xl-2 col-md-12 col-12">

                    <button type="button"
                            class="btn btn-sm user-filter-reset w-100"
                            id="btn-reset-user-filters">
                        <i class="fas fa-rotate-left me-1"></i>
                        Reset
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- MAIN USER RESTRICTIONS TABLE -->
    <div class="card user-card user-table-card">

        <div class="table-responsive user-table-scroll">

            <table class="table table-hover align-middle mb-0"
                   id="main-user-restrictions-table">

                <thead>
                    <tr>
                        <th class="ps-3" style="width: 250px;">
                            User Account
                        </th>

                        <th>
                            Restriction Type
                        </th>

                        <th>
                            Reason &amp; Notes
                        </th>

                        <th>
                            Started At
                        </th>

                        <th>
                            Expires
                        </th>

                        <th class="text-center">
                            Status
                        </th>

                        <th class="pe-3 text-end" style="width: 140px;">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody id="user-restriction-table-body">
                    @include('admin.security.blocking.partials.user_restriction_rows', ['restrictions' => $restrictions])
                </tbody>

            </table>
        </div>

        <div class="card-footer user-table-footer d-flex flex-wrap align-items-center justify-content-between gap-2">

            <span class="small" id="user-showing-text">
                Showing <strong>{{ $restrictions->firstItem() ?? 0 }}</strong>
                to <strong>{{ $restrictions->lastItem() ?? 0 }}</strong>
                of <strong>{{ number_format($restrictions->total()) }}</strong>
                entries
            </span>

            <div id="user-pagination-container">
                {{ $restrictions->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- MODAL: APPLY NEW USER RESTRICTION -->
<div class="modal fade"
     id="modalStoreUserRestriction"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content shadow">

            <div class="modal-header py-3 px-3">

                <h6 class="modal-title fw-bold text-danger mb-0">
                    <i class="fas fa-user-xmark me-2"></i>
                    Restrict User Account
                </h6>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>
            </div>

            <form id="formStoreUserRestriction">

                @csrf

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">
                            User ID <span class="text-danger">*</span>
                        </label>

                        <input type="number"
                               class="form-control font-monospace"
                               id="store_user_id"
                               name="user_id"
                               required
                               placeholder="42">
                    </div>

                    <div class="row g-3 mb-3">

                        <div class="col-sm-6">

                            <label class="form-label">
                                Restriction Type
                            </label>

                            <select class="form-select"
                                    id="store_user_restriction_type"
                                    name="restriction_type"
                                    required>

                                <option value="checkout_restricted" selected>
                                    Checkout Only
                                </option>

                                <option value="temporary_lock">
                                    Temporary Account Lock
                                </option>

                                <option value="permanent_suspension">
                                    Permanent Suspension
                                </option>

                                <option value="login_restricted">
                                    Login Restricted
                                </option>

                                <option value="form_restricted">
                                    Form Restricted
                                </option>

                                <option value="warning">
                                    Issue Warning
                                </option>
                            </select>
                        </div>

                        <div class="col-sm-6" id="user_duration_wrapper">

                            <label class="form-label">
                                Duration
                            </label>

                            <select class="form-select"
                                    id="store_duration_hours"
                                    name="duration_hours">

                                <option value="1">1 Hour</option>
                                <option value="6">6 Hours</option>
                                <option value="24" selected>24 Hours (1 Day)</option>
                                <option value="72">72 Hours (3 Days)</option>
                                <option value="168">7 Days</option>
                                <option value="">Permanent / Indefinite</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Reason <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               class="form-control"
                               id="store_user_reason"
                               name="reason"
                               required
                               placeholder="Repeated fraudulent COD orders">
                    </div>

                    <div class="mb-2">

                        <label class="form-label">
                            Admin Note (Optional)
                        </label>

                        <textarea class="form-control"
                                  id="store_user_admin_note"
                                  name="admin_note"
                                  rows="2"
                                  placeholder="Internal notes..."></textarea>
                    </div>

                </div>

                <div class="modal-footer py-3 px-3">

                    <button type="button"
                            class="btn btn-sm btn-secondary"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-sm btn-danger px-3"
                            id="btn-submit-store-user-res">
                        Apply Restriction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    const tableBody = document.getElementById('user-restriction-table-body');
    const paginationContainer = document.getElementById('user-pagination-container');
    const filterForm = document.getElementById('userFilterForm');
    const searchInput = document.getElementById('filter_user_search');
    const csrfToken = '{{ csrf_token() }}';

    let searchTimer = null;

    function fetchUserRestrictions(url = null) {
        const refreshIcon = document.getElementById('refresh-icon');
        if (refreshIcon) refreshIcon.classList.add('fa-spin');

        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        const targetUrl = url || `{{ route('admin.security.blocking.users') }}?${params.toString()}`;

        if (tableBody) tableBody.style.opacity = '0.5';

        fetch(targetUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (refreshIcon) refreshIcon.classList.remove('fa-spin');
            if (tableBody) tableBody.style.opacity = '1';

            if (data.success) {
                if (tableBody) tableBody.innerHTML = data.html;
                if (paginationContainer && data.pagination) {
                    paginationContainer.innerHTML = data.pagination;
                }
                const counter = document.getElementById('user-total-counter');
                if (counter && data.total !== undefined) counter.innerText = Number(data.total).toLocaleString();
                const showing = document.getElementById('user-showing-text');
                if (showing && data.total !== undefined) {
                    showing.innerHTML = `Showing results of <strong>${Number(data.total).toLocaleString()}</strong> entries`;
                }
            }
        })
        .catch(err => {
            if (refreshIcon) refreshIcon.classList.remove('fa-spin');
            if (tableBody) tableBody.style.opacity = '1';
            console.error('User restrictions fetch error:', err);
        });
    }

    function initUserEvents() {
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => fetchUserRestrictions(), 350);
            });
        }

        document.querySelectorAll('#filter_restriction_type, #filter_user_status, #filter_user_per_page').forEach(sel => {
            sel.addEventListener('change', () => fetchUserRestrictions());
        });

        if (filterForm) {
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                fetchUserRestrictions();
            });
        }

        const resetBtn = document.getElementById('btn-reset-user-filters');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                filterForm.reset();
                fetchUserRestrictions(`{{ route('admin.security.blocking.users') }}`);
            });
        }

        const refreshBtn = document.getElementById('btn-refresh-users');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => fetchUserRestrictions());
        }

        document.addEventListener('click', function(e) {
            const pageLink = e.target.closest('#user-pagination-container .page-link');
            if (pageLink && pageLink.getAttribute('href')) {
                e.preventDefault();
                fetchUserRestrictions(pageLink.getAttribute('href'));
            }
        });

        document.addEventListener('click', function(e) {
            const liftBtn = e.target.closest('.btn-lift-user-restriction');
            if (liftBtn) {
                const id = liftBtn.getAttribute('data-id');
                const name = liftBtn.getAttribute('data-name');

                if (confirm(`Lift security restriction for ${name}?`)) {
                    liftBtn.disabled = true;
                    liftBtn.innerText = '...';

                    fetch(`{{ url('/admin/security/blocking/users/lift') }}/${id}`, {
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
                            const row = document.getElementById(`user-restriction-row-${id}`);
                            if (row) row.remove();
                            fetchUserRestrictions();
                        } else {
                            liftBtn.disabled = false;
                            liftBtn.innerText = 'Lift Restriction';
                        }
                    })
                    .catch(() => {
                        liftBtn.disabled = false;
                        liftBtn.innerText = 'Lift Restriction';
                    });
                }
            }
        });

        const resTypeSelect = document.getElementById('store_user_restriction_type');
        const resDurWrapper = document.getElementById('user_duration_wrapper');
        if (resTypeSelect && resDurWrapper) {
            resTypeSelect.addEventListener('change', function() {
                resDurWrapper.style.display = this.value === 'permanent_suspension' ? 'none' : 'block';
            });
        }

        const storeForm = document.getElementById('formStoreUserRestriction');
        if (storeForm) {
            storeForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = document.getElementById('btn-submit-store-user-res');
                btn.disabled = true;
                btn.innerText = 'Applying...';

                const formData = new FormData(storeForm);

                fetch(`{{ route('admin.security.blocking.users.store') }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    btn.disabled = false;
                    btn.innerText = 'Apply Restriction';
                    alert(res.message);
                    if (res.success) {
                        const modalEl = document.getElementById('modalStoreUserRestriction');
                        if (modalEl && typeof bootstrap !== 'undefined') {
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }
                        storeForm.reset();
                        fetchUserRestrictions();
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerText = 'Apply Restriction';
                    alert('Error applying restriction.');
                });
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initUserEvents);
    } else {
        initUserEvents();
    }
})();
</script>
@endsection