@extends('layouts.admin')

@section('title', 'Blocked CIDR Networks')

@section('admin_content')
<div class="container-fluid px-4 py-3" id="security-blocked-networks-wrapper">

    {{-- Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom border-light">
        <div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.security.blocking.ips') }}" class="btn btn-sm btn-light border text-secondary shadow-sm" title="Back to Blocked IPs">
                    <i class="fas fa-arrow-left"></i>
                </a>

                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center">
                    <i class="fas fa-sitemap text-danger me-2"></i> Blocked Networks
                </h4>

                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1" style="font-size: 11px;">
                    <span id="network-total-counter">{{ number_format($blockedNetworks->total()) }}</span> Subnets
                </span>
            </div>

            <p class="text-muted small mb-0 mt-1">
                Protect your server from botnets, VPN clusters, and coordinated network attacks.
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.security.blocking.ips') }}" class="btn btn-sm btn-outline-secondary shadow-sm">
                <i class="fas fa-ban me-1 text-danger"></i> Blocked IPs
            </a>

            <a href="{{ route('admin.security.blocking.users') }}" class="btn btn-sm btn-outline-secondary shadow-sm">
                <i class="fas fa-user-lock me-1 text-warning"></i> Restricted Users
            </a>

            <button type="button" class="btn btn-sm btn-danger shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#modalStoreBlockedNetwork">
                <i class="fas fa-plus-circle me-1"></i> Block Network
            </button>

            <button type="button" class="btn btn-sm btn-white border shadow-sm text-secondary px-2.5" id="btn-refresh-networks" title="Refresh">
                <i class="fas fa-sync-alt" id="refresh-icon"></i>
            </button>
        </div>
    </div>

    {{-- Security Notice --}}
    <div class="alert alert-warning border-0 shadow-sm rounded-3 py-2.5 px-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2 small">
            <i class="fas fa-triangle-exclamation text-warning-emphasis"></i>

            <span class="text-dark">
                <strong>Network Protection:</strong>
                IPv4 subnets wider than <code>/16</code> (65,536 IPs) are restricted to prevent accidental ISP-wide blocks. Your current admin IP is protected.
            </span>
        </div>

        <span class="badge bg-white text-secondary border px-2 py-0.5" style="font-size: 10px;">
            Admin IP Protected
        </span>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-3">
            <form id="networkFilterForm" class="row g-2 align-items-end" method="GET" action="{{ route('admin.security.blocking.networks') }}">

                <div class="col-xl-5 col-md-5">
                    <label class="form-label small fw-semibold text-secondary mb-1">Search CIDR or Reason</label>

                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>

                        <input type="text" class="form-control form-control-sm border-start-0 font-monospace" id="filter_network_search" name="search" placeholder="CIDR, crawler, bot..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-xl-3 col-md-3 col-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">Status</label>

                    <select class="form-select form-select-sm" id="filter_network_status" name="status">
                        <option value="active" {{ request('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive / Lifted</option>
                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Records</option>
                    </select>
                </div>

                <div class="col-xl-2 col-md-2 col-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">Per Page</label>

                    <select class="form-select form-select-sm" id="filter_network_per_page" name="per_page">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <div class="col-xl-2 col-md-2 col-12">
                    <button type="button" class="btn btn-sm btn-light border w-100" id="btn-reset-network-filters">
                        <i class="fas fa-rotate-left me-1"></i> Reset
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- Blocked Networks Table --}}
    <div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="main-blocked-networks-table" style="font-size: 12.5px;">

                <thead class="table-light text-secondary border-bottom" style="font-size: 11px;">
                    <tr>
                        <th class="ps-3" style="width: 240px;">CIDR Subnet</th>
                        <th class="text-center">IP Range</th>
                        <th>Reason &amp; Admin Note</th>
                        <th>Blocked At</th>
                        <th>Expires</th>
                        <th class="text-center">Status</th>
                        <th class="pe-3 text-end" style="width: 130px;">Actions</th>
                    </tr>
                </thead>

                <tbody id="blocked-network-table-body">
                    @include('admin.security.blocking.partials.blocked_network_rows', ['blockedNetworks' => $blockedNetworks])
                </tbody>

            </table>
        </div>

        <div class="card-footer bg-white border-0 py-3 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-top">

            <span class="small text-muted" id="network-showing-text">
                Showing <strong>{{ $blockedNetworks->firstItem() ?? 0 }}</strong> to <strong>{{ $blockedNetworks->lastItem() ?? 0 }}</strong> of <strong>{{ number_format($blockedNetworks->total()) }}</strong> subnets
            </span>

            <div id="network-pagination-container">
                {{ $blockedNetworks->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
</div>

{{-- Modal: Add Blocked Network --}}
<div class="modal fade" id="modalStoreBlockedNetwork" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <div class="modal-header py-2.5 px-3 bg-light">
                <h6 class="modal-title fw-bold text-danger">
                    <i class="fas fa-sitemap me-2"></i> Block CIDR Network
                </h6>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formStoreBlockedNetwork">
                @csrf

                <div class="modal-body p-3">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">
                            CIDR Subnet <span class="text-danger">*</span>
                        </label>

                        <input type="text" class="form-control form-control-sm font-monospace" id="cidr_input" name="cidr_range" required placeholder="192.168.10.0/24 or 2001:db8::/32">

                        <div class="form-text small text-muted" style="font-size: 11px;">
                            Examples: <code>/24</code> = 256 IPs |
                            <code>/20</code> = 4,096 IPs |
                            <code>/16</code> = 65,536 IPs
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Duration</label>

                        <select class="form-select form-select-sm" name="duration_days">
                            <option value="1">1 Day</option>
                            <option value="3">3 Days</option>
                            <option value="7" selected>7 Days (Recommended)</option>
                            <option value="14">14 Days</option>
                            <option value="30">30 Days</option>
                            <option value="">Permanent</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">
                            Block Reason <span class="text-danger">*</span>
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

                    <button type="submit" class="btn btn-sm btn-danger px-3" id="btn-submit-store-network">
                        Block Network
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
(function() {
    const tableBody = document.getElementById('blocked-network-table-body');
    const paginationContainer = document.getElementById('network-pagination-container');
    const filterForm = document.getElementById('networkFilterForm');
    const searchInput = document.getElementById('filter_network_search');
    const csrfToken = '{{ csrf_token() }}';

    let searchTimer = null;

    function fetchBlockedNetworks(url = null) {
        const refreshIcon = document.getElementById('refresh-icon');

        if (refreshIcon) refreshIcon.classList.add('fa-spin');

        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);

        const targetUrl = url || `{{ route('admin.security.blocking.networks') }}?${params.toString()}`;

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

                const counter = document.getElementById('network-total-counter');

                if (counter && data.total !== undefined) {
                    counter.innerText = Number(data.total).toLocaleString();
                }

                const showing = document.getElementById('network-showing-text');

                if (showing && data.total !== undefined) {
                    showing.innerHTML = `Showing results of <strong>${Number(data.total).toLocaleString()}</strong> subnets`;
                }
            }
        })
        .catch(err => {
            if (refreshIcon) refreshIcon.classList.remove('fa-spin');
            if (tableBody) tableBody.style.opacity = '1';

            console.error('Blocked Networks fetch error:', err);
        });
    }

    function initNetworkEvents() {

        // Search
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => fetchBlockedNetworks(), 350);
            });
        }

        // Filters
        document.querySelectorAll('#filter_network_status, #filter_network_per_page').forEach(sel => {
            sel.addEventListener('change', () => fetchBlockedNetworks());
        });

        // Form Submit
        if (filterForm) {
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                fetchBlockedNetworks();
            });
        }

        // Reset Filters
        const resetBtn = document.getElementById('btn-reset-network-filters');

        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                filterForm.reset();
                fetchBlockedNetworks(`{{ route('admin.security.blocking.networks') }}`);
            });
        }

        // Refresh
        const refreshBtn = document.getElementById('btn-refresh-networks');

        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => fetchBlockedNetworks());
        }

        // Pagination
        document.addEventListener('click', function(e) {
            const pageLink = e.target.closest('#network-pagination-container .page-link');

            if (pageLink && pageLink.getAttribute('href')) {
                e.preventDefault();
                fetchBlockedNetworks(pageLink.getAttribute('href'));
            }
        });

        // Unblock Network
        document.addEventListener('click', function(e) {
            const unblockBtn = e.target.closest('.btn-unblock-network-row');

            if (unblockBtn) {
                const id = unblockBtn.getAttribute('data-id');
                const cidr = unblockBtn.getAttribute('data-cidr');

                if (confirm(`Lift subnet restriction for CIDR: ${cidr}?`)) {
                    unblockBtn.disabled = true;
                    unblockBtn.innerText = '...';

                    fetch(`{{ url('/admin/security/blocking/networks/unblock') }}/${id}`, {
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
                            const row = document.getElementById(`blocked-network-row-${id}`);

                            if (row) row.remove();

                            fetchBlockedNetworks();
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

        // Store Network Block
        const storeForm = document.getElementById('formStoreBlockedNetwork');

        if (storeForm) {
            storeForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const btn = document.getElementById('btn-submit-store-network');

                btn.disabled = true;
                btn.innerText = 'Blocking...';

                const formData = new FormData(storeForm);

                fetch(`{{ route('admin.security.blocking.networks.store') }}`, {
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
                    btn.innerText = 'Block Network';

                    alert(res.message);

                    if (res.success) {
                        const modalEl = document.getElementById('modalStoreBlockedNetwork');

                        if (modalEl && typeof bootstrap !== 'undefined') {
                            const modal = bootstrap.Modal.getInstance(modalEl);

                            if (modal) modal.hide();
                        }

                        storeForm.reset();
                        fetchBlockedNetworks();
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerText = 'Block Network';

                    alert('Unable to create network block.');
                });
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNetworkEvents);
    } else {
        initNetworkEvents();
    }
})();
</script>
@endsection