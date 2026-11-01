@extends('layouts.admin')

@section('title', 'Blocked IP Management')

@section('admin_content')
<div class="container-fluid px-4 py-3" id="security-blocking-wrapper">

    {{-- Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom border-light">
        <div>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center">
                <i class="fas fa-ban text-danger me-2"></i> Blocked IPs
            </h4>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.security.blocking.networks') }}" class="btn btn-sm btn-outline-secondary shadow-sm">
                <i class="fas fa-sitemap me-1 text-danger"></i> Blocked Networks
            </a>

            <a href="{{ route('admin.security.blocking.users') }}" class="btn btn-sm btn-outline-secondary shadow-sm">
                <i class="fas fa-user-lock me-1 text-warning"></i> Restricted Users
            </a>

            <button type="button" class="btn btn-sm btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddBlockedIp">
                <i class="fas fa-plus-circle me-1"></i> Block IP
            </button>

            <button type="button" class="btn btn-sm btn-outline-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEmergencyFlush">
                <i class="fas fa-triangle-exclamation me-1"></i> Emergency Flush
            </button>

            <button type="button" class="btn btn-sm btn-white border shadow-sm text-secondary px-2.5" id="btn-refresh-blocked-ips" title="Refresh">
                <i class="fas fa-sync-alt" id="refresh-icon"></i>
            </button>
        </div>
    </div>

    {{-- Metrics --}}
    <div class="row g-3 mb-4">

        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Active Blocks</span>
                        <h3 class="fw-bold mb-0 text-danger mt-1" id="stat-active-blocks">{{ number_format($stats['total_active_blocks'] ?? 0) }}</h3>
                    </div>
                    <div class="rounded-3 p-3 bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-shield-slash fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Permanent Blocks</span>
                        <h3 class="fw-bold mb-0 text-dark mt-1" id="stat-permanent-blocks">{{ number_format($stats['permanent_blocks'] ?? 0) }}</h3>
                    </div>
                    <div class="rounded-3 p-3 bg-dark bg-opacity-10 text-dark">
                        <i class="fas fa-lock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Temporary Blocks</span>
                        <h3 class="fw-bold mb-0 text-warning mt-1" id="stat-temporary-blocks">{{ number_format($stats['temporary_blocks'] ?? 0) }}</h3>
                    </div>
                    <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Blocked Networks</span>
                        <h3 class="fw-bold mb-0 text-primary mt-1" id="stat-blocked-networks">{{ number_format($stats['total_blocked_networks'] ?? 0) }}</h3>
                    </div>
                    <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-sitemap fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-3">
            <form id="filterBlockedIpsForm" class="row g-2 align-items-end">

                <div class="col-xl-4 col-md-5">
                    <label class="form-label small fw-semibold text-secondary mb-1">Search IP or Reason</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control form-control-sm border-start-0 font-monospace" id="filter_search" name="search" placeholder="IP, scanner, bot..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-xl-2 col-md-3 col-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">Status</label>
                    <select class="form-select form-select-sm" id="filter_status" name="status">
                        <option value="active" {{ request('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Expired / Inactive</option>
                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Records</option>
                    </select>
                </div>

                <div class="col-xl-2 col-md-4 col-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">Block Type</label>
                    <select class="form-select form-select-sm" id="filter_type" name="block_type">
                        <option value="">All Types</option>
                        <option value="permanent" {{ request('block_type') === 'permanent' ? 'selected' : '' }}>Permanent</option>
                        <option value="temporary" {{ request('block_type') === 'temporary' ? 'selected' : '' }}>Temporary</option>
                    </select>
                </div>

                <div class="col-xl-2 col-md-4 col-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">Category</label>
                    <select class="form-select form-select-sm" id="filter_category" name="category">
                        <option value="">All Categories</option>
                        <option value="manual" {{ request('category') === 'manual' ? 'selected' : '' }}>Manual</option>
                        <option value="multiple_orders" {{ request('category') === 'multiple_orders' ? 'selected' : '' }}>Multiple Orders</option>
                        <option value="spam_form" {{ request('category') === 'spam_form' ? 'selected' : '' }}>Spam Forms</option>
                        <option value="brute_force" {{ request('category') === 'brute_force' ? 'selected' : '' }}>Brute Force</option>
                        <option value="url_attack" {{ request('category') === 'url_attack' ? 'selected' : '' }}>URL Probing</option>
                    </select>
                </div>

                <div class="col-xl-1 col-md-2 col-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">Per Page</label>
                    <select class="form-select form-select-sm" id="filter_per_page" name="per_page">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <div class="col-xl-1 col-md-2 col-12">
                    <button type="button" class="btn btn-sm btn-light border w-100" id="btn-reset-filters">
                        <i class="fas fa-rotate-left me-1"></i> Reset
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- Blocked IP Table --}}
    <div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                <thead class="table-light text-secondary border-bottom" style="font-size: 11px;">
                    <tr>
                        <th class="ps-3" style="width: 200px;">IP Address</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Reason &amp; Notes</th>
                        <th>Blocked By</th>
                        <th>Expires</th>
                        <th class="text-center">Status</th>
                        <th class="pe-3 text-end" style="width: 140px;">Actions</th>
                    </tr>
                </thead>

                <tbody id="table-blocked-ips-body">
                    @include('admin.security.blocking.partials.blocked_ip_rows', ['blockedIps' => $blockedIps])
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white border-0 py-3 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-top">
            <span class="small text-muted" id="showing-entries-text">
                Showing <strong>{{ $blockedIps->firstItem() ?? 0 }}</strong> to <strong>{{ $blockedIps->lastItem() ?? 0 }}</strong> of <strong>{{ number_format($blockedIps->total()) }}</strong> entries
            </span>

            <div id="pagination-container">
                {{ $blockedIps->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

{{-- Modal: Add Blocked IP --}}
<div class="modal fade" id="modalAddBlockedIp" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <div class="modal-header py-2.5 px-3 bg-light">
                <h6 class="modal-title fw-bold text-danger">
                    <i class="fas fa-ban me-1.5"></i> Block IP Address
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formStoreBlockedIp">
                @csrf

                <div class="modal-body p-3">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">
                            IP Address <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-sm font-monospace" name="ip_address" required placeholder="103.204.120.50">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold text-secondary">Block Type</label>
                            <select class="form-select form-select-sm" name="block_type" id="modal_block_type" required>
                                <option value="temporary" selected>Temporary</option>
                                <option value="permanent">Permanent</option>
                            </select>
                        </div>

                        <div class="col-sm-6" id="modal_duration_wrapper">
                            <label class="form-label small fw-semibold text-secondary">Duration</label>
                            <select class="form-select form-select-sm" name="duration_minutes">
                                <option value="60">1 Hour</option>
                                <option value="120" selected>2 Hours</option>
                                <option value="360">6 Hours</option>
                                <option value="1440">24 Hours</option>
                                <option value="10080">7 Days</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Category</label>
                        <select class="form-select form-select-sm" name="block_category">
                            <option value="manual" selected>Manual</option>
                            <option value="multiple_orders">Multiple Fraud Orders</option>
                            <option value="spam_form">Spam Forms</option>
                            <option value="brute_force">Brute Force</option>
                            <option value="url_attack">URL Probing</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">
                            Reason <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-sm" name="block_reason" required placeholder="Enter reason for blocking...">
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-secondary">Internal Note (Optional)</label>
                        <textarea class="form-control form-control-sm" name="admin_note" rows="2" placeholder="Add internal notes..."></textarea>
                    </div>

                </div>

                <div class="modal-footer py-2 px-3 bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger px-3" id="btn-submit-block">Block IP</button>
                </div>
            </form>

        </div>
    </div>
</div>

{{-- Modal: Emergency Flush --}}
<div class="modal fade" id="modalEmergencyFlush" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">

            <div class="modal-header py-2.5 px-3 bg-danger text-white">
                <h6 class="modal-title fw-bold">
                    <i class="fas fa-triangle-exclamation me-1.5"></i> Emergency Flush
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="formEmergencyFlush">
                @csrf

                <div class="modal-body p-3">
                    <p class="small text-muted mb-2">
                        This will <strong>deactivate all active IP blocks immediately</strong>. Use only when necessary.
                    </p>

                    <label class="form-label small fw-semibold text-secondary mb-1">
                        Type <code>CONFIRM_FLUSH</code> to proceed:
                    </label>

                    <input type="text" class="form-control form-control-sm font-monospace text-center" id="flush_confirmation" required placeholder="CONFIRM_FLUSH">
                </div>

                <div class="modal-footer py-2 px-3 bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger" id="btn-execute-flush">Flush All Blocks</button>
                </div>
            </form>

        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
(function() {
    const csrfToken = '{{ csrf_token() }}';
    const filterForm = document.getElementById('filterBlockedIpsForm');
    const tbody = document.getElementById('table-blocked-ips-body');
    const paginationContainer = document.getElementById('pagination-container');
    const showingText = document.getElementById('showing-entries-text');
    let searchTimer = null;

    function fetchBlockedIps(targetUrl = null) {
        const refreshIcon = document.getElementById('refresh-icon');
        if (refreshIcon) refreshIcon.classList.add('fa-spin');

        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        const url = targetUrl || `{{ route('admin.security.blocking.ips') }}?${params.toString()}`;

        if (tbody) tbody.style.opacity = '0.5';

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(res => {
            if (refreshIcon) refreshIcon.classList.remove('fa-spin');
            if (tbody) tbody.style.opacity = '1';

            if (res.success) {
                if (tbody) tbody.innerHTML = res.html;
                if (paginationContainer && res.pagination) paginationContainer.innerHTML = res.pagination;

                if (showingText && res.total !== undefined) {
                    showingText.innerHTML = `Showing results of <strong>${Number(res.total).toLocaleString()}</strong> entries`;
                }
            }
        })
        .catch(err => {
            if (refreshIcon) refreshIcon.classList.remove('fa-spin');
            if (tbody) tbody.style.opacity = '1';
            console.error('Fetch error:', err);
        });
    }

    function initEvents() {

        // Search
        const searchInput = document.getElementById('filter_search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => fetchBlockedIps(), 350);
            });
        }

        // Filters
        document.querySelectorAll('#filter_status, #filter_type, #filter_category, #filter_per_page').forEach(el => {
            el.addEventListener('change', () => fetchBlockedIps());
        });

        // Reset
        const resetBtn = document.getElementById('btn-reset-filters');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                filterForm.reset();
                fetchBlockedIps(`{{ route('admin.security.blocking.ips') }}`);
            });
        }

        // Pagination
        document.addEventListener('click', function(e) {
            const pageLink = e.target.closest('#pagination-container .page-link');
            if (pageLink && pageLink.getAttribute('href')) {
                e.preventDefault();
                fetchBlockedIps(pageLink.getAttribute('href'));
            }
        });

        // Refresh
        const refreshBtn = document.getElementById('btn-refresh-blocked-ips');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => fetchBlockedIps());
        }

        // Block type toggle
        const modalBlockType = document.getElementById('modal_block_type');
        const modalDurWrap = document.getElementById('modal_duration_wrapper');

        if (modalBlockType && modalDurWrap) {
            modalBlockType.addEventListener('change', function() {
                modalDurWrap.style.display = this.value === 'permanent' ? 'none' : 'block';
            });
        }

        // Add IP Block
        const addForm = document.getElementById('formStoreBlockedIp');

        if (addForm) {
            addForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const btn = document.getElementById('btn-submit-block');
                btn.disabled = true;
                btn.innerText = 'Blocking...';

                const formData = new FormData(addForm);

                fetch(`{{ route('admin.security.blocking.ips.store') }}`, {
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
                    btn.innerText = 'Block IP';

                    alert(res.message);

                    if (res.success) {
                        addForm.reset();

                        const modalEl = document.getElementById('modalAddBlockedIp');
                        if (modalEl && typeof bootstrap !== 'undefined') {
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }

                        fetchBlockedIps();
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerText = 'Block IP';
                    alert('Unable to create block entry.');
                });
            });
        }

        // Quick Unblock
        document.addEventListener('click', function(e) {
            const unblockBtn = e.target.closest('.btn-ajax-unblock');

            if (unblockBtn) {
                const id = unblockBtn.getAttribute('data-id');
                const ip = unblockBtn.getAttribute('data-ip');

                if (confirm(`Unblock IP: ${ip}?`)) {
                    unblockBtn.disabled = true;
                    unblockBtn.innerText = '...';

                    fetch(`{{ url('/admin/security/blocking/ips/unblock') }}/${id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            note: 'Unblocked from Blocked IPs Manager'
                        })
                    })
                    .then(r => r.json())
                    .then(res => {
                        alert(res.message);

                        if (res.success) {
                            const row = document.getElementById(`row-blocked-ip-${id}`);
                            if (row) row.remove();
                            fetchBlockedIps();
                        } else {
                            unblockBtn.disabled = false;
                            unblockBtn.innerText = 'Unblock';
                        }
                    })
                    .catch(() => {
                        unblockBtn.disabled = false;
                        unblockBtn.innerText = 'Unblock';
                    });
                }
            }
        });

        // Emergency Flush
        const flushForm = document.getElementById('formEmergencyFlush');

        if (flushForm) {
            flushForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const code = document.getElementById('flush_confirmation').value.trim();

                if (code !== 'CONFIRM_FLUSH') {
                    alert('Enter CONFIRM_FLUSH exactly.');
                    return;
                }

                const btn = document.getElementById('btn-execute-flush');
                btn.disabled = true;
                btn.innerText = 'Flushing...';

                fetch(`{{ route('admin.security.blocking.emergency_flush') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        confirmation: code
                    })
                })
                .then(r => r.json())
                .then(res => {
                    btn.disabled = false;
                    btn.innerText = 'Flush All Blocks';

                    alert(res.message);

                    if (res.success) {
                        const modalEl = document.getElementById('modalEmergencyFlush');

                        if (modalEl && typeof bootstrap !== 'undefined') {
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }

                        fetchBlockedIps();
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerText = 'Flush All Blocks';
                });
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEvents);
    } else {
        initEvents();
    }
})();
</script>
@endsection