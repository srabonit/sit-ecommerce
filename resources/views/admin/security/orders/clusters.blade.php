@extends('layouts.admin')

@section('title', 'Same IP Order Clusters (Multi-Order Detection)')

@section('admin_content')
<div class="container-fluid px-4 py-3" id="security-order-clusters-wrapper">
    <!-- Header & Navigation -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom border-secondary border-opacity-25">
        <div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.security.orders.index') }}" class="btn btn-sm btn-light border text-secondary shadow-sm" title="Back to Suspicious Orders">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center">
                    <i class="fas fa-layer-group text-primary me-2"></i> Same IP Multiple Order Clusters
                </h4>
                <span class="badge bg-warning bg-opacity-15 text-warning-emphasis border border-warning border-opacity-50 px-2.5 py-1" style="font-size: 11px;">
                    <i class="fas fa-cubes me-1"></i> <span id="cluster-total-counter">{{ number_format($clusters->total()) }}</span> Active Clusters
                </span>
            </div>
            <p class="text-muted small mb-0 mt-1">
                Forensic grouping of all online orders sharing identical client IP addresses to detect fake order velocity and customer impersonation
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.security.orders.index') }}" class="btn btn-sm btn-outline-secondary shadow-sm">
                <i class="fas fa-list me-1"></i> All Suspicious Orders
            </a>
            <button type="button" class="btn btn-sm btn-light border shadow-sm text-secondary" id="btn-refresh-clusters" title="Refresh Clusters">
                <i class="fas fa-sync-alt" id="cluster-refresh-icon"></i>
            </button>
        </div>
    </div>

    <!-- Cluster Analytics Guide & Search Toolbar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-3">
            <form id="clusterFilterForm" class="row g-2 align-items-center justify-content-between">
                <div class="col-md-6 col-lg-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control form-control-sm border-start-0" id="filter_cluster_ip" name="search" placeholder="Search by Client IP (192.168.1.1)..." value="{{ request('search') }}">
                        @if(request('search'))
                            <a href="{{ route('admin.security.orders.clusters') }}" class="btn btn-sm btn-light border" title="Clear Filter">
                                <i class="fas fa-times text-danger"></i>
                            </a>
                        @endif
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 text-md-end mt-2 mt-md-0">
                    <span class="small text-muted">
                        <i class="fas fa-circle-info text-primary me-1"></i>
                        Threshold: <strong>&ge; 2 Orders per IP</strong>. Focus on <strong>Multi-Identity</strong> flags.
                    </span>
                </div>
            </form>
        </div>
    </div>

    <!-- MAIN CLUSTER DATA TABLE CARD -->
    <div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="main-clusters-table" style="font-size: 12.5px;">
                <thead class="table-light text-muted border-bottom" style="font-size: 11px;">
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

        <!-- Table Footer / Pagination -->
        <div class="card-footer bg-white border-0 py-3 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-top">
            <span class="small text-muted" id="cluster-showing-text">
                Showing <strong>{{ $clusters->firstItem() ?? 0 }}</strong> to <strong>{{ $clusters->lastItem() ?? 0 }}</strong> of <strong>{{ number_format($clusters->total()) }}</strong> clusters
            </span>
            <div id="cluster-pagination-container">
                {{ $clusters->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- MODAL: CLUSTER QUICK BLOCK IP -->
<div class="modal fade" id="modalClusterBlockIp" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2.5 px-3 bg-light">
                <h6 class="modal-title fw-bold text-danger">
                    <i class="fas fa-ban me-2"></i> Block Cluster Source IP
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formSubmitClusterBlock">
                <input type="hidden" id="cluster_block_ip_target" name="target">
                <input type="hidden" name="action_type" value="block_ip">

                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Target IP Address</label>
                        <input type="text" class="form-control form-control-sm font-monospace bg-light" id="display_cluster_ip" readonly>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold text-secondary">Restriction Duration</label>
                            <select class="form-select form-select-sm" id="cluster_block_duration" name="duration">
                                <option value="60">1 Hour</option>
                                <option value="360">6 Hours</option>
                                <option value="1440" selected>24 Hours (1 Day)</option>
                                <option value="10080">7 Days</option>
                                <option value="0">Permanent</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold text-secondary">Violation Tag</label>
                            <input type="text" class="form-control form-control-sm bg-light" value="multiple_orders" readonly>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-secondary">Block Reason <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="cluster_block_reason" name="reason" required value="Rapid multiple order spamming across fake identities">
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger px-3" id="btn-submit-cluster-block">Enforce Block</button>
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