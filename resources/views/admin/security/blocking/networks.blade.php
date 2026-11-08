
@extends('layouts.admin')

@section('title', 'Blocked CIDR Networks')

@section('admin_content')

<style>
    /* =========================================================
       BLOCKED NETWORKS — KAIADMIN CLASSIC / WHITE THEME
    ========================================================= */

    #security-blocked-networks-wrapper {
        --network-primary: var(--bs-primary, #1572e8);
        --network-danger: #f25961;
        --network-text: #2a2f5b;
        --network-muted: #6b7280;
        --network-border: #ebeef4;
        --network-surface: #ffffff;
        --network-soft: #f8f9fc;
        color: var(--network-text);
        font-size: 13px;
    }

    #security-blocked-networks-wrapper .network-page-header {
        padding-bottom: 18px;
        margin-bottom: 22px;
        border-bottom: 1px solid var(--network-border);
    }

    #security-blocked-networks-wrapper .network-page-title {
        color: var(--network-text);
        font-size: 21px;
        font-weight: 700;
        letter-spacing: -0.4px;
    }

    #security-blocked-networks-wrapper .network-page-subtitle {
        color: var(--network-muted);
        font-size: 12px;
        line-height: 1.6;
    }

    #security-blocked-networks-wrapper .network-title-icon {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        background: rgba(242, 89, 97, 0.10);
        color: var(--network-danger);
        font-size: 15px;
        flex-shrink: 0;
    }

    #security-blocked-networks-wrapper .network-total-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 9px;
        border-radius: 5px;
        background: rgba(242, 89, 97, 0.09);
        color: var(--network-danger);
        border: 1px solid rgba(242, 89, 97, 0.18);
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    #security-blocked-networks-wrapper .btn {
        border-radius: 5px !important;
        font-weight: 600;
        font-size: 12px;
        transition: all .2s ease;
    }

    #security-blocked-networks-wrapper .btn-sm {
        min-height: 34px;
        padding: 6px 11px;
    }

    #security-blocked-networks-wrapper .btn-danger {
        background: var(--network-danger);
        border-color: var(--network-danger);
    }

    #security-blocked-networks-wrapper .btn-danger:hover {
        background: #e74750;
        border-color: #e74750;
    }

    #security-blocked-networks-wrapper .network-refresh-btn {
        width: 35px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid var(--network-border);
        color: var(--network-muted);
    }

    #security-blocked-networks-wrapper .network-refresh-btn:hover {
        background: var(--network-soft);
        color: var(--network-primary);
    }

    /* Security notice */
    #security-blocked-networks-wrapper .network-security-notice {
        background: #fffaf0;
        border: 1px solid #f8e6bd !important;
        border-left: 3px solid #f3ad35 !important;
        border-radius: 5px !important;
        color: #72551e;
        padding: 12px 15px;
        box-shadow: none !important;
    }

    #security-blocked-networks-wrapper .network-security-notice code {
        color: #b7791f;
        background: rgba(243, 173, 53, .10);
        padding: 2px 5px;
        border-radius: 3px;
        font-weight: 600;
    }

    #security-blocked-networks-wrapper .network-protected-badge {
        background: #fff;
        border: 1px solid #f2dfb5;
        border-radius: 4px;
        color: #98701f;
        padding: 5px 9px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    /* Cards */
    #security-blocked-networks-wrapper .network-card {
        background: var(--network-surface);
        border: 1px solid var(--network-border) !important;
        border-radius: 5px !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .025) !important;
    }

    #security-blocked-networks-wrapper .network-filter-card .card-body {
        padding: 17px;
    }

    /* Form controls */
    #security-blocked-networks-wrapper .form-label {
        color: #555b73;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    #security-blocked-networks-wrapper .form-control,
    #security-blocked-networks-wrapper .form-select,
    #security-blocked-networks-wrapper .input-group-text {
        min-height: 36px;
        border-radius: 5px !important;
        border-color: #e3e7ef;
        font-size: 12px;
        box-shadow: none !important;
    }

    #security-blocked-networks-wrapper .form-control:focus,
    #security-blocked-networks-wrapper .form-select:focus {
        border-color: rgba(21, 114, 232, .55);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .08) !important;
    }

    #security-blocked-networks-wrapper .input-group-text {
        background: #f8f9fc;
        color: #9298a5;
    }

    #security-blocked-networks-wrapper .network-filter-reset {
        min-height: 36px;
        background: #f8f9fc;
        border: 1px solid #e3e7ef;
        color: #626b7a;
    }

    #security-blocked-networks-wrapper .network-filter-reset:hover {
        background: #eef1f7;
        color: var(--network-text);
    }

    /* Table */
    #security-blocked-networks-wrapper .network-table-card {
        overflow: hidden;
    }

    #security-blocked-networks-wrapper .network-table-scroll {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #security-blocked-networks-wrapper #main-blocked-networks-table {
        width: 100%;
        min-width: 1050px;
        margin-bottom: 0;
        color: #555b73;
        font-size: 12px;
    }

    #security-blocked-networks-wrapper #main-blocked-networks-table thead th {
        padding: 13px 12px;
        background: #f8f9fc;
        color: #737b8c;
        border-bottom: 1px solid var(--network-border);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .45px;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }

    #security-blocked-networks-wrapper #main-blocked-networks-table tbody td {
        padding: 13px 12px;
        border-color: #f0f1f5;
        vertical-align: middle;
    }

    #security-blocked-networks-wrapper #main-blocked-networks-table tbody tr {
        transition: background .15s ease;
    }

    #security-blocked-networks-wrapper #main-blocked-networks-table tbody tr:hover {
        background: #f9faff;
    }

    #security-blocked-networks-wrapper #main-blocked-networks-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #security-blocked-networks-wrapper .network-table-footer {
        background: #fff;
        border-top: 1px solid var(--network-border) !important;
        padding: 14px 17px;
    }

    #security-blocked-networks-wrapper #network-showing-text {
        color: var(--network-muted);
        font-size: 11px;
    }

    #security-blocked-networks-wrapper #network-showing-text strong {
        color: var(--network-text);
        font-weight: 700;
    }

    /* Pagination */
    #security-blocked-networks-wrapper #network-pagination-container .pagination {
        margin: 0;
        gap: 4px;
    }

    #security-blocked-networks-wrapper #network-pagination-container .page-link {
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

    #security-blocked-networks-wrapper #network-pagination-container .page-item.active .page-link {
        background: var(--network-primary);
        border-color: var(--network-primary);
        color: #fff;
    }

    #security-blocked-networks-wrapper #network-pagination-container .page-item.disabled .page-link {
        color: #b7bdc9;
        background: #fafbfc;
    }

    /* Modal */
    #modalStoreBlockedNetwork .modal-content {
        border: 1px solid var(--network-border) !important;
        border-radius: 6px !important;
        overflow: hidden;
    }

    #modalStoreBlockedNetwork .modal-header,
    #modalStoreBlockedNetwork .modal-footer {
        background: #f8f9fc !important;
        border-color: var(--network-border);
    }

    #modalStoreBlockedNetwork .modal-title {
        font-size: 14px;
    }

    #modalStoreBlockedNetwork .modal-body {
        padding: 20px !important;
    }

    #modalStoreBlockedNetwork .form-control,
    #modalStoreBlockedNetwork .form-select {
        min-height: 38px;
        border-radius: 5px !important;
        border-color: #e3e7ef;
        font-size: 12px;
        box-shadow: none;
    }

    #modalStoreBlockedNetwork .form-control:focus,
    #modalStoreBlockedNetwork .form-select:focus {
        border-color: rgba(21, 114, 232, .55);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .08);
    }

    #modalStoreBlockedNetwork textarea.form-control {
        min-height: auto;
    }

    #modalStoreBlockedNetwork .form-text {
        line-height: 1.7;
    }

    #modalStoreBlockedNetwork .modal-footer .btn {
        min-width: 85px;
    }

    /* Responsive */
    @media (max-width: 767.98px) {
        #security-blocked-networks-wrapper {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        #security-blocked-networks-wrapper .network-page-header {
            align-items: flex-start !important;
            margin-bottom: 16px;
            padding-bottom: 15px;
        }

        #security-blocked-networks-wrapper .network-header-main {
            width: 100%;
        }

        #security-blocked-networks-wrapper .network-header-actions {
            width: 100%;
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 7px !important;
        }

        #security-blocked-networks-wrapper .network-header-actions > * {
            width: 100%;
            justify-content: center;
        }

        #security-blocked-networks-wrapper .network-header-actions #btn-refresh-networks {
            grid-column: span 2;
            width: 100%;
        }

        #security-blocked-networks-wrapper .network-page-title {
            font-size: 18px;
        }

        #security-blocked-networks-wrapper .network-security-notice {
            align-items: flex-start !important;
        }

        #security-blocked-networks-wrapper .network-filter-card .card-body {
            padding: 13px;
        }

        #security-blocked-networks-wrapper .network-table-footer {
            align-items: flex-start !important;
            flex-direction: column;
        }

        #security-blocked-networks-wrapper #network-pagination-container {
            width: 100%;
            overflow-x: auto;
        }

        #security-blocked-networks-wrapper #network-pagination-container .pagination {
            flex-wrap: nowrap;
        }
    }

    @media (max-width: 420px) {
        #security-blocked-networks-wrapper .network-header-actions {
            grid-template-columns: 1fr;
        }

        #security-blocked-networks-wrapper .network-header-actions #btn-refresh-networks {
            grid-column: auto;
        }

        #security-blocked-networks-wrapper .network-page-title {
            font-size: 16px;
        }

        #security-blocked-networks-wrapper .network-total-badge {
            font-size: 10px;
            padding: 4px 6px;
        }
    }
</style>

<div class="container-fluid px-4 py-3" id="security-blocked-networks-wrapper">

    {{-- Header --}}
    <div class="network-page-header d-flex flex-wrap align-items-center justify-content-between gap-3">

        <div class="network-header-main">
            <div class="d-flex align-items-center flex-wrap gap-2">

                <a href="{{ route('admin.security.blocking.ips') }}"
                   class="btn btn-sm btn-light border text-secondary"
                   title="Back to Blocked IPs">
                    <i class="fas fa-arrow-left"></i>
                </a>

                <span class="network-title-icon">
                    <i class="fas fa-sitemap"></i>
                </span>

                <h4 class="network-page-title mb-0">
                    Blocked Networks
                </h4>

                <span class="network-total-badge">
                    <i class="fas fa-network-wired"></i>
                    <span id="network-total-counter">{{ number_format($blockedNetworks->total()) }}</span>
                    Subnets
                </span>
            </div>

            <p class="network-page-subtitle mb-0 mt-2">
                Protect your server from botnets, VPN clusters, and coordinated network attacks.
            </p>
        </div>

        <div class="network-header-actions d-flex flex-wrap align-items-center gap-2">

            <a href="{{ route('admin.security.blocking.ips') }}"
               class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-ban me-1 text-danger"></i>
                Blocked IPs
            </a>

            <a href="{{ route('admin.security.blocking.users') }}"
               class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-user-lock me-1 text-warning"></i>
                Restricted Users
            </a>

            <button type="button"
                    class="btn btn-sm btn-danger px-3"
                    data-bs-toggle="modal"
                    data-bs-target="#modalStoreBlockedNetwork">
                <i class="fas fa-plus me-1"></i>
                Block Network
            </button>

            <button type="button"
                    class="btn btn-sm network-refresh-btn"
                    id="btn-refresh-networks"
                    title="Refresh">
                <i class="fas fa-sync-alt" id="refresh-icon"></i>
            </button>
        </div>
    </div>

    {{-- Security Notice --}}
    <div class="alert network-security-notice mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">

        <div class="d-flex align-items-start gap-2">
            <i class="fas fa-shield-alt mt-1 text-warning"></i>

            <span class="small">
                <strong>Network Protection:</strong>
                IPv4 subnets wider than <code>/16</code> (65,536 IPs) are restricted to prevent accidental ISP-wide blocks. Your current admin IP is protected.
            </span>
        </div>

        <span class="network-protected-badge">
            <i class="fas fa-lock me-1"></i>
            Admin IP Protected
        </span>
    </div>

    {{-- Filters --}}
    <div class="card network-card network-filter-card mb-4">

        <div class="card-body">

            <form id="networkFilterForm"
                  class="row g-3 align-items-end"
                  method="GET"
                  action="{{ route('admin.security.blocking.networks') }}">

                <div class="col-xl-5 col-md-5 col-12">

                    <label class="form-label">
                        Search CIDR or Reason
                    </label>

                    <div class="input-group input-group-sm">

                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>

                        <input type="text"
                               class="form-control font-monospace"
                               id="filter_network_search"
                               name="search"
                               placeholder="CIDR, crawler, bot..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-xl-3 col-md-3 col-6">

                    <label class="form-label">
                        Status
                    </label>

                    <select class="form-select form-select-sm"
                            id="filter_network_status"
                            name="status">

                        <option value="active" {{ request('status', 'active') === 'active' ? 'selected' : '' }}>
                            Active
                        </option>

                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                            Inactive / Lifted
                        </option>

                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>
                            All Records
                        </option>
                    </select>
                </div>

                <div class="col-xl-2 col-md-2 col-6">

                    <label class="form-label">
                        Per Page
                    </label>

                    <select class="form-select form-select-sm"
                            id="filter_network_per_page"
                            name="per_page">

                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <div class="col-xl-2 col-md-2 col-12">

                    <button type="button"
                            class="btn btn-sm network-filter-reset w-100"
                            id="btn-reset-network-filters">
                        <i class="fas fa-rotate-left me-1"></i>
                        Reset
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- Blocked Networks Table --}}
    <div class="card network-card network-table-card">

        <div class="table-responsive network-table-scroll">

            <table class="table table-hover align-middle mb-0"
                   id="main-blocked-networks-table">

                <thead>
                    <tr>
                        <th class="ps-3" style="width: 240px;">
                            CIDR Subnet
                        </th>

                        <th class="text-center">
                            IP Range
                        </th>

                        <th>
                            Reason &amp; Admin Note
                        </th>

                        <th>
                            Blocked At
                        </th>

                        <th>
                            Expires
                        </th>

                        <th class="text-center">
                            Status
                        </th>

                        <th class="pe-3 text-end" style="width: 130px;">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody id="blocked-network-table-body">
                    @include('admin.security.blocking.partials.blocked_network_rows', ['blockedNetworks' => $blockedNetworks])
                </tbody>

            </table>
        </div>

        {{-- Table Footer / Pagination --}}
        <div class="card-footer network-table-footer d-flex flex-wrap align-items-center justify-content-between gap-2">

            <span class="small" id="network-showing-text">
                Showing <strong>{{ $blockedNetworks->firstItem() ?? 0 }}</strong>
                to <strong>{{ $blockedNetworks->lastItem() ?? 0 }}</strong>
                of <strong>{{ number_format($blockedNetworks->total()) }}</strong>
                subnets
            </span>

            <div id="network-pagination-container">
                {{ $blockedNetworks->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
</div>

{{-- Modal: Add Blocked Network --}}
<div class="modal fade"
     id="modalStoreBlockedNetwork"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content shadow">

            <div class="modal-header py-3 px-3">

                <h6 class="modal-title fw-bold text-danger mb-0">
                    <i class="fas fa-sitemap me-2"></i>
                    Block CIDR Network
                </h6>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>
            </div>

            <form id="formStoreBlockedNetwork">

                @csrf

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">
                            CIDR Subnet <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               class="form-control font-monospace"
                               id="cidr_input"
                               name="cidr_range"
                               required
                               placeholder="192.168.10.0/24 or 2001:db8::/32">

                        <div class="form-text text-muted">
                            Examples:
                            <code>/24</code> = 256 IPs |
                            <code>/20</code> = 4,096 IPs |
                            <code>/16</code> = 65,536 IPs
                        </div>
                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Duration
                        </label>

                        <select class="form-select" name="duration_days">

                            <option value="1">1 Day</option>
                            <option value="3">3 Days</option>
                            <option value="7" selected>7 Days (Recommended)</option>
                            <option value="14">14 Days</option>
                            <option value="30">30 Days</option>
                            <option value="">Permanent</option>

                        </select>
                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Block Reason <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               class="form-control"
                               name="block_reason"
                               required
                               placeholder="Enter reason for blocking...">
                    </div>

                    <div class="mb-2">

                        <label class="form-label">
                            Internal Note (Optional)
                        </label>

                        <textarea class="form-control"
                                  name="admin_note"
                                  rows="2"
                                  placeholder="Add internal notes..."></textarea>
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
                            id="btn-submit-store-network">
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
