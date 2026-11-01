@extends('layouts.admin')

@section('title', 'Suspicious Orders & Multiple Order Detection')

@section('admin_content')
<div class="container-fluid px-4 py-3" id="security-orders-wrapper">
    <!-- Header & Action Controls -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom border-secondary border-opacity-25">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center">
                    <i class="fas fa-cart-shopping text-danger me-2"></i> Suspicious Orders &amp; Multiple Order Detection
                </h4>
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1" style="font-size: 11px;">
                    <i class="fas fa-shield-virus me-1"></i> <span id="orders-total-counter">{{ number_format($orders->total()) }}</span> Orders Flagged
                </span>
            </div>
            <p class="text-muted small mb-0 mt-1">
                Automated velocity throttling, multi-phone verification from same IP, and non-destructive spam hold engine
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- Same IP Order Clusters Shortcut -->
            <a href="{{ route('admin.security.orders.clusters') }}" class="btn btn-sm btn-outline-primary shadow-sm px-3" id="btn-view-clusters">
                <i class="fas fa-layer-group me-1"></i> Same IP Clusters
            </a>

            <!-- Refresh Button -->
            <button type="button" class="btn btn-sm btn-light border shadow-sm text-secondary" id="btn-refresh-orders" title="Refresh Table">
                <i class="fas fa-sync-alt" id="orders-refresh-icon"></i>
            </button>
        </div>
    </div>

    <!-- SUMMARY COUNTERS BAR -->
    <div class="row g-3 mb-4">
        <!-- Total Flagged Suspicious -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted fw-semibold" style="font-size: 11px;">Total Flagged</span>
                    <i class="fas fa-triangle-exclamation text-danger"></i>
                </div>
                <h3 class="fw-bold mb-0 text-danger" id="count-total-suspicious">{{ number_format($counters['total_suspicious'] ?? 0) }}</h3>
                <small class="text-muted" style="font-size: 10.5px;">Orders with security flags</small>
            </div>
        </div>

        <!-- Under Review / Verification Hold -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted fw-semibold" style="font-size: 11px;">Under Review</span>
                    <i class="fas fa-clock text-warning"></i>
                </div>
                <h3 class="fw-bold mb-0 text-warning" id="count-under-review">{{ number_format($counters['under_review'] ?? 0) }}</h3>
                <small class="text-muted" style="font-size: 10.5px;">Awaiting staff verification</small>
            </div>
        </div>

        <!-- Confirmed Spam / Cancelled -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted fw-semibold" style="font-size: 11px;">Confirmed Spam</span>
                    <i class="fas fa-ban text-dark"></i>
                </div>
                <h3 class="fw-bold mb-0 text-dark" id="count-flagged-spam">{{ number_format($counters['flagged_spam'] ?? 0) }}</h3>
                <small class="text-muted" style="font-size: 10.5px;">Marked as fraudulent traffic</small>
            </div>
        </div>

        <!-- Approved by Admin -->
        <div class="col-xl-3 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted fw-semibold" style="font-size: 11px;">Approved Clean</span>
                    <i class="fas fa-check-circle text-success"></i>
                </div>
                <h3 class="fw-bold mb-0 text-success" id="count-approved-clean">{{ number_format($counters['approved_clean'] ?? 0) }}</h3>
                <small class="text-muted" style="font-size: 10.5px;">Cleared for delivery</small>
            </div>
        </div>
    </div>

    <!-- FILTER TOOLBAR CARD -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-3">
            <form id="orderFilterForm" class="row g-2 align-items-end">
                <!-- Search Box -->
                <div class="col-xl-3 col-md-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">Search Order / Phone / IP</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control form-control-sm border-start-0" id="filter_order_search" name="search" placeholder="#ORD-1001, 01700000000..." value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Review Status -->
                <div class="col-xl-2 col-md-3 col-sm-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">Review Status</label>
                    <select class="form-select form-select-sm" id="filter_review_status" name="review_status">
                        <option value="">All Review Statuses</option>
                        <option value="under_review" {{ request('review_status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                        <option value="flagged_spam" {{ request('review_status') === 'flagged_spam' ? 'selected' : '' }}>Flagged Spam</option>
                        <option value="approved_by_admin" {{ request('review_status') === 'approved_by_admin' ? 'selected' : '' }}>Approved by Admin</option>
                        <option value="cancelled_fraud" {{ request('review_status') === 'cancelled_fraud' ? 'selected' : '' }}>Cancelled Fraud</option>
                    </select>
                </div>

                <!-- Risk Level -->
                <div class="col-xl-2 col-md-3 col-sm-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">Risk Level</label>
                    <select class="form-select form-select-sm" id="filter_order_risk_level" name="risk_level">
                        <option value="">All Risk Levels</option>
                        <option value="critical" {{ request('risk_level') === 'critical' ? 'selected' : '' }}>Critical (75 - 100)</option>
                        <option value="high" {{ request('risk_level') === 'high' ? 'selected' : '' }}>High (50 - 74)</option>
                        <option value="medium" {{ request('risk_level') === 'medium' ? 'selected' : '' }}>Medium (35 - 49)</option>
                        <option value="low" {{ request('risk_level') === 'low' ? 'selected' : '' }}>Low (&lt; 35)</option>
                    </select>
                </div>

                <!-- Order Source -->
                <div class="col-xl-2 col-md-3 col-sm-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">Channel Source</label>
                    <select class="form-select form-select-sm" id="filter_order_source" name="order_source">
                        <option value="">All Channels</option>
                        <option value="online" {{ request('order_source') === 'online' ? 'selected' : '' }}>Online Store</option>
                        <option value="pos" {{ request('order_source') === 'pos' ? 'selected' : '' }}>POS Cashier (Safe)</option>
                    </select>
                </div>

                <!-- Per Page -->
                <div class="col-xl-1 col-md-3 col-sm-6">
                    <label class="form-label small fw-semibold text-secondary mb-1">Show</label>
                    <select class="form-select form-select-sm" id="filter_order_per_page" name="per_page">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="col-xl-2 col-md-6 d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-light border w-100" id="btn-reset-order-filters">
                        <i class="fas fa-rotate-left me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Apply
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MAIN SUSPICIOUS ORDERS TABLE CARD -->
    <div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="main-orders-security-table" style="font-size: 12.5px;">
                <thead class="table-light text-muted border-bottom" style="font-size: 11px;">
                    <tr>
                        <th class="ps-3" style="width: 170px;">Order #</th>
                        <th>Customer Info</th>
                        <th>Amount</th>
                        <th>Client IP</th>
                        <th class="text-center">Risk Score</th>
                        <th>Violation Flag</th>
                        <th class="text-center">Review Status</th>
                        <th class="pe-3 text-end" style="width: 110px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="order-table-body">
                    @include('admin.security.orders.partials.order_table_rows', ['orders' => $orders])
                </tbody>
            </table>
        </div>

        <!-- Table Footer / Pagination -->
        <div class="card-footer bg-white border-0 py-3 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-top">
            <span class="small text-muted" id="orders-showing-text">
                Showing <strong>{{ $orders->firstItem() ?? 0 }}</strong> to <strong>{{ $orders->lastItem() ?? 0 }}</strong> of <strong>{{ number_format($orders->total()) }}</strong> orders
            </span>
            <div id="order-pagination-container">
                {{ $orders->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- MODAL: QUICK STATUS REASON NOTE -->
<div class="modal fade" id="modalOrderStatusUpdate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2.5 px-3 bg-light">
                <h6 class="modal-title fw-bold text-dark"><i class="fas fa-pen-to-square me-2 text-primary"></i> Review Reason</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formSubmitOrderStatus">
                <input type="hidden" id="status_order_id">
                <input type="hidden" id="status_target_review">
                <input type="hidden" id="status_target_order">
                
                <div class="modal-body p-3">
                    <p class="small text-muted mb-2" id="status_modal_text">Apply security review decision to order?</p>
                    <label class="form-label small fw-semibold text-secondary">Internal Note (Optional)</label>
                    <textarea class="form-control form-control-sm" id="status_admin_note" rows="2" placeholder="Audit reasoning..."></textarea>
                </div>
                <div class="modal-footer py-2 px-3 bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3" id="btn-confirm-status-update">Confirm Decision</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: QUICK BLOCK (IP / USER / NETWORK) -->
<div class="modal fade" id="modalQuickBlockTarget" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2.5 px-3 bg-light">
                <h6 class="modal-title fw-bold text-danger" id="quickBlockModalTitle">
                    <i class="fas fa-shield-virus me-2"></i> Security Restriction Enforcement
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formExecuteQuickBlock">
                <input type="hidden" id="quick_block_action_type" name="action_type">
                <input type="hidden" id="quick_block_target" name="target">

                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Target Entity</label>
                        <input type="text" class="form-control form-control-sm font-monospace bg-light" id="display_target_entity" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Restriction Duration</label>
                        <select class="form-select form-select-sm" id="quick_block_duration" name="duration">
                            <option value="60">1 Hour</option>
                            <option value="360">6 Hours</option>
                            <option value="1440" selected>24 Hours (1 Day)</option>
                            <option value="10080">7 Days</option>
                            <option value="0">Permanent</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-secondary">Reason <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="quick_block_reason" name="reason" required value="Repeated fraudulent order attempts">
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger px-3" id="btn-submit-quick-block">Enforce Restriction</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('order-table-body');
    const paginationContainer = document.getElementById('order-pagination-container');
    const filterForm = document.getElementById('orderFilterForm');
    const searchInput = document.getElementById('filter_order_search');
    const csrfToken = '{{ csrf_token() }}';

    let searchTimer = null;

    // ১. AJAX ডেটা লোড ফাংশন
    function fetchOrdersData(url = null) {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        const targetUrl = url || `{{ route('admin.security.orders.index') }}?${params.toString()}`;

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
                const counter = document.getElementById('orders-total-counter');
                if (counter && data.total !== undefined) {
                    counter.innerText = Number(data.total).toLocaleString();
                }
            }
        })
        .catch(err => {
            tableBody.style.opacity = '1';
            console.error('Suspicious Orders fetch error:', err);
        });
    }

    // ২. সার্চ বক্সে টাইপিংয়ের পর স্বয়ংক্রিয় রিফ্রেশ
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                fetchOrdersData();
            }, 350);
        });
    }

    // ৩. ড্রপডাউন পরিবর্তনের সাথে সাথে ডেটা রিফ্রেশ
    document.querySelectorAll('#filter_review_status, #filter_order_risk_level, #filter_order_source, #filter_order_per_page').forEach(sel => {
        sel.addEventListener('change', () => fetchOrdersData());
    });

    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            fetchOrdersData();
        });
    }

    const resetBtn = document.getElementById('btn-reset-order-filters');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            filterForm.reset();
            fetchOrdersData(`{{ route('admin.security.orders.index') }}`);
        });
    }

    // ৪. পেজিনেশন ক্লিকে এজাক্স পেজ ট্রানজিশন
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#order-pagination-container .page-link');
        if (pageLink && pageLink.getAttribute('href')) {
            e.preventDefault();
            fetchOrdersData(pageLink.getAttribute('href'));
        }
    });

    // ৫. সিকিউরিটি রিভিউ স্ট্যাটাস পরিবর্তনের ট্রিগার
    document.addEventListener('click', function(e) {
        const statusBtn = e.target.closest('.btn-update-order-status');
        if (statusBtn) {
            const orderId = statusBtn.getAttribute('data-id');
            const reviewStatus = statusBtn.getAttribute('data-status');
            const orderStatus = statusBtn.getAttribute('data-order-status');

            document.getElementById('status_order_id').value = orderId;
            document.getElementById('status_target_review').value = reviewStatus;
            document.getElementById('status_target_order').value = orderStatus;
            document.getElementById('status_modal_text').innerText = `Change review status to "${reviewStatus.replace(/_/g, ' ')}"?`;

            const modalEl = document.getElementById('modalOrderStatusUpdate');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }

        // কুইক ব্লক ট্রিগার
        const blockTrigger = e.target.closest('.btn-quick-block-trigger');
        if (blockTrigger) {
            const actionType = blockTrigger.getAttribute('data-type');
            const target = blockTrigger.getAttribute('data-target');
            const modalTitle = blockTrigger.getAttribute('data-title');

            document.getElementById('quick_block_action_type').value = actionType;
            document.getElementById('quick_block_target').value = target;
            document.getElementById('display_target_entity').value = target;
            document.getElementById('quickBlockModalTitle').innerHTML = `<i class="fas fa-shield-virus me-2"></i> ${modalTitle}`;

            const modalEl = document.getElementById('modalQuickBlockTarget');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    });

    // ৬. রিভিউ স্ট্যাটাস আপডেট ফর্ম সাবমিট
    const statusForm = document.getElementById('formSubmitOrderStatus');
    if (statusForm) {
        statusForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-confirm-status-update');
            btn.disabled = true;
            btn.innerText = 'Updating...';

            const orderId = document.getElementById('status_order_id').value;
            const payload = {
                review_status: document.getElementById('status_target_review').value,
                order_status: document.getElementById('status_target_order').value,
                admin_note: document.getElementById('status_admin_note').value,
            };

            const statusUrl = `{{ url('/admin/security/orders/status') }}/${orderId}`;

            fetch(statusUrl, {
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
                btn.disabled = false;
                btn.innerText = 'Confirm Decision';
                alert(res.message);
                if (res.success) {
                    const modalEl = document.getElementById('modalOrderStatusUpdate');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    statusForm.reset();
                    fetchOrdersData();
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerText = 'Confirm Decision';
            });
        });
    }

    // ৭. কুইক ব্লকিং ফর্ম সাবমিট
    const quickBlockForm = document.getElementById('formExecuteQuickBlock');
    if (quickBlockForm) {
        quickBlockForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-quick-block');
            btn.disabled = true;
            btn.innerText = 'Enforcing...';

            const payload = {
                action_type: document.getElementById('quick_block_action_type').value,
                target: document.getElementById('quick_block_target').value,
                duration: document.getElementById('quick_block_duration').value,
                reason: document.getElementById('quick_block_reason').value,
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
                btn.disabled = false;
                btn.innerText = 'Enforce Restriction';
                alert(res.message);
                if (res.success) {
                    const modalEl = document.getElementById('modalQuickBlockTarget');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    fetchOrdersData();
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerText = 'Enforce Restriction';
            });
        });
    }

    // ৮. রিফ্রেশ বাটন হ্যান্ডলার
    const refreshBtn = document.getElementById('btn-refresh-orders');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            const icon = document.getElementById('orders-refresh-icon');
            if (icon) icon.classList.add('fa-spin');
            fetchOrdersData();
            setTimeout(() => {
                if (icon) icon.classList.remove('fa-spin');
            }, 600);
        });
    }
});
</script>
@endpush