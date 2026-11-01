@extends('layouts.admin')

@section('title', 'Restricted User Accounts')

@section('admin_content')
<div class="container-fluid px-4 py-3" id="security-user-restrictions-wrapper">
    <!-- Header & Action Toolbar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom border-light">
        <div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.security.blocking.ips') }}" class="btn btn-sm btn-light border text-secondary shadow-sm" title="Back to Blocked IPs">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center">
                    <i class="fas fa-user-xmark text-danger me-2"></i> Restricted Accounts
                </h4>
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1" style="font-size: 11px;">
                    <span id="user-total-counter">{{ number_format($restrictions->total()) }}</span> Accounts
                </span>
            </div>
            <p class="text-muted small mb-0 mt-1">
                Manage account restrictions for checkout abuse, login attempts, and suspicious activity
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.security.blocking.ips') }}" class="btn btn-sm btn-outline-secondary shadow-sm">
                <i class="fas fa-ban me-1 text-danger"></i> Blocked IPs
            </a>
            <a href="{{ route('admin.security.blocking.networks') }}" class="btn btn-sm btn-outline-secondary shadow-sm">
                <i class="fas fa-sitemap me-1 text-danger"></i> Blocked Networks
            </a>
            <button type="button" class="btn btn-sm btn-danger shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#modalStoreUserRestriction">
                <i class="fas fa-user-slash me-1"></i> Restrict Account
            </button>
            <button type="button" class="btn btn-sm btn-white border shadow-sm text-secondary px-2.5" id="btn-refresh-users" title="Refresh List">
                <i class="fas fa-sync-alt" id="refresh-icon"></i>
            </button>
        </div>
    </div>

    <!-- FILTER TOOLBAR -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-3">
            <form id="userFilterForm" class="row g-2 align-items-end" method="GET" action="{{ route('admin.security.blocking.users') }}">
                <div class="col-xl-4 col-md-5">
                    <label class="form-label small fw-semibold text-secondary mb-1">Search Name / Email / Phone / Reason</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control form-control-sm border-start-0" id="filter_user_search" name="search" placeholder="Name, email, phone..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-xl-3 col-md-3 col-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">Restriction Type</label>
                    <select class="form-select form-select-sm" id="filter_restriction_type" name="type">
                        <option value="">All Types</option>
                        <option value="checkout_restricted" {{ request('type') === 'checkout_restricted' ? 'selected' : '' }}>Checkout Restricted</option>
                        <option value="temporary_lock" {{ request('type') === 'temporary_lock' ? 'selected' : '' }}>Temporary Lock</option>
                        <option value="permanent_suspension" {{ request('type') === 'permanent_suspension' ? 'selected' : '' }}>Permanent Suspension</option>
                        <option value="login_restricted" {{ request('type') === 'login_restricted' ? 'selected' : '' }}>Login Restricted</option>
                        <option value="form_restricted" {{ request('type') === 'form_restricted' ? 'selected' : '' }}>Form Restricted</option>
                        <option value="warning" {{ request('type') === 'warning' ? 'selected' : '' }}>Warning</option>
                    </select>
                </div>

                <div class="col-xl-2 col-md-2 col-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">Status</label>
                    <select class="form-select form-select-sm" id="filter_user_status" name="status">
                        <option value="active" {{ request('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Lifted / Inactive</option>
                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Records</option>
                    </select>
                </div>

                <div class="col-xl-1 col-md-2 col-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">Per Page</label>
                    <select class="form-select form-select-sm" id="filter_user_per_page" name="per_page">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <div class="col-xl-2 col-md-12 col-12">
                    <button type="button" class="btn btn-sm btn-light border w-100" id="btn-reset-user-filters">
                        <i class="fas fa-rotate-left me-1"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MAIN USER RESTRICTIONS TABLE -->
    <div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="main-user-restrictions-table" style="font-size: 12.5px;">
                <thead class="table-light text-secondary border-bottom" style="font-size: 11px;">
                    <tr>
                        <th class="ps-3" style="width: 250px;">User Account</th>
                        <th>Restriction Type</th>
                        <th>Reason &amp; Notes</th>
                        <th>Started At</th>
                        <th>Expires</th>
                        <th class="text-center">Status</th>
                        <th class="pe-3 text-end" style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="user-restriction-table-body">
                    @include('admin.security.blocking.partials.user_restriction_rows', ['restrictions' => $restrictions])
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white border-0 py-3 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-top">
            <span class="small text-muted" id="user-showing-text">
                Showing <strong>{{ $restrictions->firstItem() ?? 0 }}</strong> to <strong>{{ $restrictions->lastItem() ?? 0 }}</strong> of <strong>{{ number_format($restrictions->total()) }}</strong> entries
            </span>
            <div id="user-pagination-container">
                {{ $restrictions->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- MODAL: APPLY NEW USER RESTRICTION -->
<div class="modal fade" id="modalStoreUserRestriction" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2.5 px-3 bg-light">
                <h6 class="modal-title fw-bold text-danger">
                    <i class="fas fa-user-xmark me-2"></i> Restrict User Account
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formStoreUserRestriction">
                @csrf
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">User ID <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-sm font-monospace" id="store_user_id" name="user_id" required placeholder="e.g. 42">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold text-secondary">Restriction Type</label>
                            <select class="form-select form-select-sm" id="store_user_restriction_type" name="restriction_type" required>
                                <option value="checkout_restricted" selected>Checkout Only</option>
                                <option value="temporary_lock">Temporary Account Lock</option>
                                <option value="permanent_suspension">Permanent Suspension</option>
                                <option value="login_restricted">Login Restricted</option>
                                <option value="form_restricted">Form Restricted</option>
                                <option value="warning">Issue Warning</option>
                            </select>
                        </div>
                        <div class="col-sm-6" id="user_duration_wrapper">
                            <label class="form-label small fw-semibold text-secondary">Duration</label>
                            <select class="form-select form-select-sm" id="store_duration_hours" name="duration_hours">
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
                        <label class="form-label small fw-semibold text-secondary">Reason <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="store_user_reason" name="reason" required placeholder="Repeated fraudulent COD orders">
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-secondary">Admin Note (Optional)</label>
                        <textarea class="form-control form-control-sm" id="store_user_admin_note" name="admin_note" rows="2" placeholder="Internal notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger px-3" id="btn-submit-store-user-res">Apply Restriction</button>
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