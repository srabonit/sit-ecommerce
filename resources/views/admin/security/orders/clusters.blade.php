@extends('layouts.admin')

@section('title', 'Same IP Order Clusters (Multi-Order Detection)')

@section('admin_content')
<style>
    /* =========================================================
       SAME IP ORDER CLUSTERS - KAIADMIN CLASSIC WHITE
       Scoped styles | Strict 5px radius
    ========================================================= */
    #security-order-clusters-wrapper {
        --cluster-radius: 5px;
        --cluster-border: #e5e7eb;
        --cluster-muted: #6b7280;
        color: #344054;
        font-size: 13px;
    }

    #security-order-clusters-wrapper .card,
    #security-order-clusters-wrapper .btn,
    #security-order-clusters-wrapper .form-control,
    #security-order-clusters-wrapper .form-select,
    #security-order-clusters-wrapper .input-group-text,
    #modalClusterBlockIp .modal-content,
    #modalClusterBlockIp .btn,
    #modalClusterBlockIp .form-control,
    #modalClusterBlockIp .form-select {
        border-radius: var(--cluster-radius) !important;
    }

    #security-order-clusters-wrapper .cluster-page-header {
        padding-bottom: 15px;
        border-bottom: 1px solid var(--cluster-border);
    }

    #security-order-clusters-wrapper .cluster-page-title {
        font-size: 19px;
        line-height: 1.35;
        letter-spacing: -.35px;
    }

    #security-order-clusters-wrapper .cluster-page-subtitle {
        max-width: 850px;
        font-size: 12px;
        line-height: 1.6;
    }

    #security-order-clusters-wrapper .cluster-counter {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 9px;
        font-size: 10.5px;
        font-weight: 700;
        white-space: nowrap;
        border-radius: 5px;
    }

    #security-order-clusters-wrapper .cluster-toolbar-card,
    #security-order-clusters-wrapper .cluster-table-card {
        border: 1px solid var(--cluster-border) !important;
        box-shadow: 0 2px 8px rgba(16, 24, 40, .045) !important;
    }

    #security-order-clusters-wrapper .cluster-toolbar-card .card-body {
        padding: 15px;
    }

    #security-order-clusters-wrapper .input-group-text {
        min-width: 38px;
        justify-content: center;
        color: #667085;
        background: #f8fafc;
        border-color: #dfe3e8;
    }

    #security-order-clusters-wrapper .form-control,
    #security-order-clusters-wrapper .form-select {
        min-height: 36px;
        border-color: #dfe3e8;
        font-size: 12px;
        box-shadow: none;
    }

    #security-order-clusters-wrapper .form-control:focus,
    #modalClusterBlockIp .form-control:focus,
    #modalClusterBlockIp .form-select:focus {
        border-color: var(--bs-primary, #1572e8);
        box-shadow: 0 0 0 .15rem rgba(21, 114, 232, .10);
    }

    #security-order-clusters-wrapper .btn {
        min-height: 32px;
        font-size: 11.5px;
        font-weight: 600;
        transition: all .18s ease;
    }

    #security-order-clusters-wrapper .btn:hover {
        transform: translateY(-1px);
    }

    #security-order-clusters-wrapper .cluster-table-card {
        overflow: hidden;
        background: #fff;
    }

    #security-order-clusters-wrapper .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #security-order-clusters-wrapper #main-clusters-table {
        min-width: 980px;
        font-size: 12px !important;
    }

    #security-order-clusters-wrapper #main-clusters-table thead th {
        padding: 12px 10px;
        color: #667085;
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        font-size: 10.5px !important;
        font-weight: 700;
        letter-spacing: .25px;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }

    #security-order-clusters-wrapper #main-clusters-table tbody td {
        padding: 12px 10px;
        border-bottom: 1px solid #edf0f2;
        vertical-align: middle;
    }

    #security-order-clusters-wrapper #main-clusters-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #security-order-clusters-wrapper #main-clusters-table tbody tr:hover {
        background-color: #f9fbfd;
    }

    #security-order-clusters-wrapper .cluster-table-footer {
        border-top: 1px solid var(--cluster-border) !important;
        padding: 13px 15px;
    }

    #security-order-clusters-wrapper #cluster-showing-text {
        font-size: 11.5px;
        line-height: 1.5;
    }

    #security-order-clusters-wrapper #cluster-pagination-container .pagination {
        margin-bottom: 0;
        gap: 3px;
    }

    #security-order-clusters-wrapper #cluster-pagination-container .page-link {
        min-width: 30px;
        min-height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 8px;
        border-radius: 5px !important;
        font-size: 11px;
        box-shadow: none;
    }

    /* Modal */
    #modalClusterBlockIp .modal-content {
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }

    #modalClusterBlockIp .modal-header,
    #modalClusterBlockIp .modal-footer {
        background: #f8fafc;
        border-color: #e8ebef;
    }

    #modalClusterBlockIp .modal-title {
        font-size: 14px;
    }

    #modalClusterBlockIp .modal-body {
        padding: 18px;
    }

    #modalClusterBlockIp .form-label {
        margin-bottom: 5px;
        font-size: 11px;
    }

    #modalClusterBlockIp .form-control,
    #modalClusterBlockIp .form-select {
        min-height: 36px;
        font-size: 12px;
    }

    #modalClusterBlockIp .modal-footer .btn {
        min-height: 33px;
        font-size: 11.5px;
    }

    @media (max-width: 767.98px) {
        #security-order-clusters-wrapper {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        #security-order-clusters-wrapper .cluster-page-title {
            font-size: 16px;
        }

        #security-order-clusters-wrapper .cluster-page-header {
            align-items: flex-start !important;
        }

        #security-order-clusters-wrapper .cluster-header-actions {
            width: 100%;
        }

        #security-order-clusters-wrapper .cluster-header-actions .btn {
            flex: 1 1 auto;
        }

        #security-order-clusters-wrapper .cluster-toolbar-card .card-body {
            padding: 12px;
        }

        #security-order-clusters-wrapper .cluster-threshold-note {
            display: block;
            text-align: left !important;
            line-height: 1.7;
        }

        #security-order-clusters-wrapper .cluster-table-footer {
            align-items: flex-start !important;
        }

        #security-order-clusters-wrapper #cluster-pagination-container {
            max-width: 100%;
            overflow-x: auto;
            padding-bottom: 2px;
        }

        #modalClusterBlockIp .modal-dialog {
            margin: 12px;
        }
    }

    @media (max-width: 420px) {
        #security-order-clusters-wrapper .cluster-page-subtitle {
            font-size: 11px;
        }

        #security-order-clusters-wrapper .cluster-counter {
            margin-top: 3px;
        }

        #modalClusterBlockIp .modal-body {
            padding: 14px;
        }
    }
</style>

<div class="container-fluid px-3 px-md-4 py-3" id="security-order-clusters-wrapper">

    {{-- Header & Navigation --}}
    <div class="cluster-page-header d-flex flex-column flex-lg-row align-items-start justify-content-between gap-3 mb-3">
        <div class="flex-grow-1 min-w-0">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <a href="{{ route('admin.security.orders.index') }}"
                   class="btn btn-sm btn-light border text-secondary"
                   title="Back to Suspicious Orders"
                   aria-label="Back to suspicious orders">
                    <i class="fas fa-arrow-left"></i>
                </a>

                <h4 class="cluster-page-title fw-bold mb-0 text-dark d-flex align-items-center">
                    <i class="fas fa-layer-group text-primary me-2"></i>
                    Same IP Multiple Order Clusters
                </h4>

                <span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning border-opacity-25 cluster-counter">
                    <i class="fas fa-cubes"></i>
                    <span id="cluster-total-counter">{{ number_format($clusters->total()) }}</span>
                    Active Clusters
                </span>
            </div>

            <p class="cluster-page-subtitle text-muted mb-0 mt-2">
                Forensic grouping of all online orders sharing identical client IP addresses to detect fake order velocity and customer impersonation
            </p>
        </div>

        <div class="cluster-header-actions d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
            <a href="{{ route('admin.security.orders.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-list me-1"></i>
                All Suspicious Orders
            </a>

            <button type="button"
                    class="btn btn-sm btn-light border text-secondary"
                    id="btn-refresh-clusters"
                    title="Refresh Clusters"
                    aria-label="Refresh clusters">
                <i class="fas fa-sync-alt" id="cluster-refresh-icon"></i>
            </button>
        </div>
    </div>

    {{-- Search & Analytics Toolbar --}}
    <div class="card cluster-toolbar-card mb-3 bg-white">
        <div class="card-body">
            <form id="clusterFilterForm" class="row g-3 align-items-center justify-content-between">
                <div class="col-12 col-md-6 col-lg-5">
                    <label for="filter_cluster_ip" class="form-label small fw-semibold text-secondary mb-1">
                        Search Client IP
                    </label>

                    <div class="input-group input-group-sm">
                        <span class="input-group-text border-end-0">
                            <i class="fas fa-search"></i>
                        </span>

                        <input type="text"
                               class="form-control border-start-0"
                               id="filter_cluster_ip"
                               name="search"
                               placeholder="Search by Client IP (192.168.1.1)..."
                               value="{{ request('search') }}">

                        @if(request('search'))
                            <a href="{{ route('admin.security.orders.clusters') }}"
                               class="btn btn-light border"
                               title="Clear Filter"
                               aria-label="Clear search filter">
                                <i class="fas fa-times text-danger"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-6 text-md-end">
                    <div class="cluster-threshold-note small text-muted">
                        <i class="fas fa-circle-info text-primary me-1"></i>
                        Threshold: <strong class="text-dark">&ge; 2 Orders per IP</strong>.
                        Focus on <strong class="text-dark">Multi-Identity</strong> flags.
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Main Cluster Data Table --}}
    <div class="card cluster-table-card bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="main-clusters-table">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 180px;">Client IP</th>
                        <th class="text-center">Total Orders</th>
                        <th>Cumulative Value</th>
                        <th>Customer Identities</th>
                        <th class="text-center">Accounts</th>
                        <th class="text-center">Highest Risk</th>
                        <th>Order Timeline Span</th>
                        <th class="pe-3 text-end" style="width: 140px;">Actions</th>
                    </tr>
                </thead>

                <tbody id="cluster-table-body">
                    @include('admin.security.orders.partials.cluster_rows', ['clusters' => $clusters])
                </tbody>
            </table>
        </div>

        {{-- Table Footer / Pagination --}}
        <div class="card-footer bg-white border-0 cluster-table-footer d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
            <span class="text-muted" id="cluster-showing-text">
                Showing <strong class="text-dark">{{ $clusters->firstItem() ?? 0 }}</strong>
                to <strong class="text-dark">{{ $clusters->lastItem() ?? 0 }}</strong>
                of <strong class="text-dark">{{ number_format($clusters->total()) }}</strong> clusters
            </span>

            <div id="cluster-pagination-container">
                {{ $clusters->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

{{-- Modal: Cluster Quick Block IP --}}
<div class="modal fade" id="modalClusterBlockIp" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">

            <div class="modal-header py-3 px-3">
                <h6 class="modal-title fw-bold text-danger mb-0">
                    <i class="fas fa-ban me-2"></i>
                    Block Cluster Source IP
                </h6>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <form id="formSubmitClusterBlock">
                <input type="hidden" id="cluster_block_ip_target" name="target">
                <input type="hidden" name="action_type" value="block_ip">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Target IP Address</label>
                        <input type="text"
                               class="form-control form-control-sm font-monospace bg-light"
                               id="display_cluster_ip"
                               readonly>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold text-secondary">Restriction Duration</label>
                            <select class="form-select form-select-sm"
                                    id="cluster_block_duration"
                                    name="duration">
                                <option value="60">1 Hour</option>
                                <option value="360">6 Hours</option>
                                <option value="1440" selected>24 Hours (1 Day)</option>
                                <option value="10080">7 Days</option>
                                <option value="0">Permanent</option>
                            </select>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label fw-semibold text-secondary">Violation Tag</label>
                            <input type="text"
                                   class="form-control form-control-sm bg-light"
                                   value="multiple_orders"
                                   readonly>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold text-secondary">
                            Block Reason <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               class="form-control form-control-sm"
                               id="cluster_block_reason"
                               name="reason"
                               required
                               value="Rapid multiple order spamming across fake identities">
                    </div>
                </div>

                <div class="modal-footer py-2 px-3">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-sm btn-danger px-3"
                            id="btn-submit-cluster-block">
                        <i class="fas fa-ban me-1"></i>
                        Enforce Block
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('cluster-table-body');
    const paginationContainer = document.getElementById('cluster-pagination-container');
    const searchInput = document.getElementById('filter_cluster_ip');
    const filterForm = document.getElementById('clusterFilterForm');
    const csrfToken = '{{ csrf_token() }}';
    let searchTimer = null;

    // ১. AJAX ক্লাস্টার ডাটা ফেচ (Query String Preserve Sahokare)
    function fetchClustersData(url = null) {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        const baseUrl = `{{ route('admin.security.orders.clusters') }}`;
        const targetUrl = url || `${baseUrl}?${params.toString()}`;

        tableBody.style.opacity = '0.5';

        fetch(targetUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            tableBody.style.opacity = '1';
            if (data.success) {
                tableBody.innerHTML = data.html;
                if (paginationContainer && data.pagination) {
                    paginationContainer.innerHTML = data.pagination;
                }
            }
        })
        .catch(err => {
            tableBody.style.opacity = '1';
            console.error('Cluster fetch error:', err);
        });
    }

    // ২. লাইভ সার্চ ফিল্টার
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                fetchClustersData();
            }, 350);
        });
    }

    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            fetchClustersData();
        });
    }

    // ৩. এজাক্স পেজিনেশন হ্যান্ডলার
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#cluster-pagination-container .page-link');
        if (pageLink && pageLink.getAttribute('href')) {
            e.preventDefault();
            fetchClustersData(pageLink.getAttribute('href'));
        }
    });

    // ৪. কুইক আইপি ব্লক মোডাল ওপেন
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-cluster-block-ip');
        if (btn) {
            const ip = btn.getAttribute('data-ip');
            document.getElementById('cluster_block_ip_target').value = ip;
            document.getElementById('display_cluster_ip').value = ip;

            const modalEl = document.getElementById('modalClusterBlockIp');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }

        // কুইক আনব্লক হ্যান্ডলার
        const unblockBtn = e.target.closest('.btn-cluster-unblock-ip');
        if (unblockBtn) {
            const ip = unblockBtn.getAttribute('data-ip');
            if (confirm(`Lift firewall block for IP: ${ip}?`)) {
                unblockBtn.disabled = true;
                fetch(`{{ route('admin.security.ips.unblock') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ ip_address: ip, note: 'Unblocked via cluster monitor' })
                })
                .then(r => r.json())
                .then(res => {
                    alert(res.message);
                    if (res.success) {
                        fetchClustersData();
                    } else {
                        unblockBtn.disabled = false;
                    }
                })
                .catch(() => {
                    unblockBtn.disabled = false;
                });
            }
        }
    });

    // ৫. ব্লক ফর্ম সাবমিট
    const blockForm = document.getElementById('formSubmitClusterBlock');
    if (blockForm) {
        blockForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('btn-submit-cluster-block');
            submitBtn.disabled = true;
            submitBtn.innerText = 'Enforcing...';

            const payload = {
                action_type: 'block_ip',
                target: document.getElementById('cluster_block_ip_target').value,
                duration: document.getElementById('cluster_block_duration').value,
                reason: document.getElementById('cluster_block_reason').value,
            };

            fetch(`{{ route('admin.security.orders.quick_block') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(res => {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Enforce Block';
                alert(res.message);
                if (res.success) {
                    const modalEl = document.getElementById('modalClusterBlockIp');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    fetchClustersData();
                }
            })
            .catch(() => {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Enforce Block';
            });
        });
    }

    // ৬. রিফ্রেশ বাটন
    const refreshBtn = document.getElementById('btn-refresh-clusters');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            const icon = document.getElementById('cluster-refresh-icon');
            if (icon) icon.classList.add('fa-spin');
            fetchClustersData();
            setTimeout(() => {
                if (icon) icon.classList.remove('fa-spin');
            }, 600);
        });
    }
});
</script>
@endpush
