@extends('layouts.admin')

@section('title', 'Security Review: Order #' . ($order->order_number ?? $order->id))

@section('admin_content')
@php
    $orderAmount = (float) ($order->grand_total ?? $order->total ?? $order->total_amount ?? $order->payable_amount ?? 0);
    $customerPhone = $order->customer_phone ?? $order->phone ?? $order->billing_phone ?? 'N/A';
    $customerName = $order->customer_name ?? $order->name ?? ($order->customer?->name ?? 'Guest User');
    $customerEmail = $order->customer_email ?? $order->email ?? ($order->customer?->email ?? 'N/A');
    $shippingCity = $order->shipping_city ?? $order->city ?? 'N/A';
    $shippingAddress = $order->shipping_address ?? $order->address ?? 'N/A';

    $riskScore = (int) ($order->security_risk_score ?? 0);
    $riskBadgeClass = match(true) {
        $riskScore >= 75 => 'bg-danger text-white',
        $riskScore >= 50 => 'bg-danger bg-opacity-75 text-white',
        $riskScore >= 35 => 'bg-warning text-dark',
        default          => 'bg-success text-white',
    };

    $secStatus = $order->security_review_status ?? 'under_review';
    $statusBadge = match($secStatus) {
        'approved_by_admin', 'clean' => 'bg-success text-white',
        'under_review'               => 'bg-warning text-dark',
        'flagged_spam'               => 'bg-danger text-white',
        'cancelled_fraud'            => 'bg-dark text-white',
        default                      => 'bg-secondary text-white',
    };
@endphp

<div class="container-fluid px-4 py-3" id="security-order-details-wrapper">
    <!-- Top Breadcrumb & Action Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom border-secondary border-opacity-25">
        <div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.security.orders.index') }}" class="btn btn-sm btn-light border text-secondary shadow-sm" title="Back to Suspicious Orders">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center font-monospace">
                    <i class="fas fa-shield-halved text-danger me-2"></i> Order #{{ $order->order_number ?? $order->id }}
                </h4>
                <span class="badge {{ $statusBadge }} px-2.5 py-1" style="font-size: 11px;">
                    {{ str_replace('_', ' ', ucfirst($secStatus)) }}
                </span>
                <span class="badge {{ $riskBadgeClass }} font-monospace px-2.5 py-1" style="font-size: 11px;">
                    Risk: {{ $riskScore }}/100
                </span>
            </div>
            <p class="text-muted small mb-0 mt-1">
                Placed on <strong>{{ $order->created_at ? $order->created_at->format('M d, Y h:i A') : 'N/A' }}</strong> | Channel: <strong>{{ strtoupper($order->order_source ?? 'online') }}</strong> | Status: <strong>{{ ucfirst($order->order_status ?? ($order->status ?? 'pending')) }}</strong>
            </p>
        </div>

        <!-- Decision & Action Buttons -->
        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- Core Order View Link -->
            @if(Route::has('admin.order.view'))
                <a href="{{ route('admin.order.view', $order->id) }}" class="btn btn-sm btn-light border shadow-sm text-secondary" target="_blank" title="View Full Order Invoice">
                    <i class="fas fa-receipt me-1"></i> Order Details
                </a>
            @endif

            <!-- Approve Clean -->
            <button type="button" class="btn btn-sm btn-success shadow-sm px-3 btn-decision-action" data-status="approved_by_admin" data-order-status="processing">
                <i class="fas fa-check-circle me-1"></i> Approve Clean
            </button>

            <!-- Hold for Verification -->
            <button type="button" class="btn btn-sm btn-warning shadow-sm px-3 btn-decision-action" data-status="under_review" data-order-status="on_hold">
                <i class="fas fa-pause-circle me-1"></i> Hold on Review
            </button>

            <!-- Mark as Spam / Cancel Fraud -->
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-danger shadow-sm btn-decision-action" data-status="flagged_spam" data-order-status="on_hold">
                    <i class="fas fa-flag me-1"></i> Flag Spam
                </button>
                <button type="button" class="btn btn-dark shadow-sm btn-decision-action" data-status="cancelled_fraud" data-order-status="cancelled">
                    <i class="fas fa-ban me-1"></i> Cancel Fraud
                </button>
            </div>
        </div>
    </div>

    <!-- FORENSIC RISK & SECURITY VIOLATION SUMMARY BANNER -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 {{ $riskScore >= 50 ? 'border-start border-danger border-4' : 'border-start border-warning border-4' }} bg-white">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle p-2.5 {{ $riskScore >= 50 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-warning bg-opacity-15 text-warning-emphasis' }}">
                        <i class="fas fa-triangle-exclamation fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Security Risk Engine Diagnostic</h6>
                        <p class="text-danger fw-semibold mb-1 small">
                            {{ $order->suspicious_reason ?? 'Multiple rapid checkout requests or anomalous velocity detected.' }}
                        </p>
                        <small class="text-muted">
                            Security Checked: <strong>{{ !empty($order->security_checked_at) ? \Carbon\Carbon::parse($order->security_checked_at)->format('Y-m-d H:i:s') : 'Automatic on Submission' }}</strong>
                        </small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    @if(!empty($order->client_ip))
                        @if($isBlocked)
                            <span class="badge bg-danger px-3 py-2" style="font-size: 11px;">
                                <i class="fas fa-ban me-1"></i> Client IP Restricted
                            </span>
                        @else
                            <button type="button" class="btn btn-sm btn-outline-danger shadow-sm" id="btn-block-client-ip" data-ip="{{ $order->client_ip }}">
                                <i class="fas fa-shield-virus me-1"></i> Block Client IP
                            </button>
                        @endif
                    @endif

                    @if(!empty($order->user_id))
                        <button type="button" class="btn btn-sm btn-outline-dark shadow-sm" id="btn-restrict-customer-user" data-user-id="{{ $order->user_id }}">
                            <i class="fas fa-user-xmark me-1"></i> Restrict Account
                        </button>
                    @endif
                </div>
            </div>

            @if(!empty($order->security_note))
                <div class="mt-3 pt-2 border-top border-light">
                    <span class="fw-bold text-secondary small d-block mb-1"><i class="fas fa-clipboard-list me-1"></i> Admin Audit Log:</span>
                    <pre class="bg-light p-2 rounded-2 small mb-0 font-monospace text-dark text-break" style="white-space: pre-wrap; font-size: 11px;">{{ $order->security_note }}</pre>
                </div>
            @endif
        </div>
    </div>

    <!-- ROW: CUSTOMER INFO, NETWORK TELEMETRY & FINANCIAL SUMMARY -->
    <div class="row g-4 mb-4">
        <!-- Customer & Delivery Telemetry -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-user-shield text-primary me-2"></i> Customer Identity &amp; Contact</h6>
                </div>
                <div class="card-body p-3">
                    <table class="table table-sm table-borderless mb-0" style="font-size: 12.5px;">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width: 120px;">Customer:</td>
                                <td class="fw-semibold text-dark">{{ $customerName }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Phone Number:</td>
                                <td class="fw-bold font-monospace text-dark">
                                    <i class="fas fa-phone-alt text-secondary me-1" style="font-size: 10px;"></i>
                                    {{ $customerPhone }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email Address:</td>
                                <td class="text-dark text-break">{{ $customerEmail }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Account ID:</td>
                                <td>
                                    @if($order->user_id)
                                        <span class="badge bg-light text-dark border">User #{{ $order->user_id }}</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border">Guest Checkout</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Shipping City:</td>
                                <td class="text-dark">{{ $shippingCity }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Address:</td>
                                <td class="text-muted small text-break">{{ $shippingAddress }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Network & Device Telemetry -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-network-wired text-primary me-2"></i> Client Network Telemetry</h6>
                </div>
                <div class="card-body p-3">
                    <table class="table table-sm table-borderless mb-0" style="font-size: 12.5px;">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width: 130px;">Client IP:</td>
                                <td class="fw-bold font-monospace">
                                    @if(!empty($order->client_ip))
                                        <a href="{{ route('admin.security.ips.show', $order->client_ip) }}" class="text-decoration-none text-dark hover-primary" target="_blank">
                                            {{ $order->client_ip }} <i class="fas fa-external-link-alt ms-1 text-secondary" style="font-size: 9px;"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Firewall State:</td>
                                <td>
                                    @if($isBlocked)
                                        <span class="badge bg-danger px-2 py-0.5">Firewall Blocked</span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-0.5">Normal Traffic</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Channel / POS:</td>
                                <td>
                                    <span class="badge {{ strtolower($order->order_source ?? '') === 'pos' ? 'bg-info text-dark' : 'bg-secondary bg-opacity-10 text-secondary border' }}">
                                        {{ strtoupper($order->order_source ?? 'online') }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Payment:</td>
                                <td class="fw-semibold text-dark">{{ strtoupper($order->payment_method ?? 'COD') }} ({{ ucfirst($order->payment_status ?? 'unpaid') }})</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tracking Token:</td>
                                <td class="font-monospace text-muted small text-break">{{ $order->tracking_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Order Flag:</td>
                                <td>
                                    @if($order->is_suspicious)
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">Flagged Suspicious</span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Clean</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-file-invoice-dollar text-primary me-2"></i> Financial Valuation</h6>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between mb-2 small text-muted">
                        <span>Subtotal:</span>
                        <span class="text-dark fw-semibold">Tk {{ number_format((float) ($order->subtotal ?? ($orderAmount - ($order->shipping_charge ?? 0))), 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small text-muted">
                        <span>Delivery Charge:</span>
                        <span class="text-dark fw-semibold">Tk {{ number_format((float) ($order->shipping_charge ?? 0), 2) }}</span>
                    </div>
                    @if(!empty($order->discount_amount) && $order->discount_amount > 0)
                        <div class="d-flex justify-content-between mb-2 small text-danger">
                            <span>Discount / Coupon:</span>
                            <span class="fw-semibold">- Tk {{ number_format((float) $order->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between pt-2 border-top border-light mb-3">
                        <span class="fw-bold text-dark fs-6">Total Amount:</span>
                        <span class="fw-bold text-dark fs-5">Tk {{ number_format($orderAmount, 2) }}</span>
                    </div>
                    <div class="alert alert-light border small text-muted mb-0 py-2">
                        <i class="fas fa-shield text-secondary me-1"></i>
                        Spam bots often generate multiple rapid COD (Cash on Delivery) orders with zero upfront deposit.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ORDER ITEMS TABLE -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom border-light">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-boxes-stacked text-primary me-2"></i> Ordered Cart Products ({{ $order->items?->count() ?? 0 }})</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                <thead class="table-light text-muted border-bottom" style="font-size: 11px;">
                    <tr>
                        <th class="ps-3">Item Description</th>
                        <th>Variant / SKU</th>
                        <th class="text-center">Unit Price</th>
                        <th class="text-center">Quantity</th>
                        <th class="pe-3 text-end">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->items ?? [] as $item)
                        @php
                            $itemPrice = (float) ($item->unit_price ?? ($item->price ?? 0));
                            $itemQty = (int) ($item->quantity ?? 1);
                        @endphp
                        <tr>
                            <td class="ps-3 fw-semibold text-dark">
                                {{ $item->product_name ?? ($item->product?->name ?? 'Product Item') }}
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace px-2 py-0.5" style="font-size: 10px;">
                                    {{ $item->sku ?? ($item->variant?->sku ?? 'STANDARD') }}
                                </span>
                            </td>
                            <td class="text-center font-monospace">Tk {{ number_format($itemPrice, 2) }}</td>
                            <td class="text-center fw-bold">{{ $itemQty }}</td>
                            <td class="pe-3 text-end font-monospace fw-bold text-dark">
                                Tk {{ number_format($itemQty * $itemPrice, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted small">No line items found for this order.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- RELATED ORDERS CREATED FROM IDENTICAL IP -->
    @if(!empty($relatedOrders) && $relatedOrders->isNotEmpty())
        <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom border-light d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-layer-group text-danger me-2"></i> Other Orders from IP: {{ $order->client_ip }} ({{ $relatedOrders->count() }})</h6>
                    <small class="text-muted" style="font-size: 11px;">Examine customer names and phones across repeated order attempts</small>
                </div>
                <a href="{{ route('admin.security.orders.clusters', ['search' => $order->client_ip]) }}" class="btn btn-sm btn-link text-primary text-decoration-none fw-semibold" style="font-size: 11px;">
                    Open Cluster &rarr;
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 12px;">
                    <thead class="table-light text-muted" style="font-size: 11px;">
                        <tr>
                            <th class="ps-3">Order #</th>
                            <th>Customer Name</th>
                            <th>Phone Number</th>
                            <th>Amount</th>
                            <th>Channel</th>
                            <th>Review Status</th>
                            <th>Placed At</th>
                            <th class="pe-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($relatedOrders as $rel)
                            @php
                                $relAmount = (float) ($rel->grand_total ?? $rel->total ?? $rel->total_amount ?? $rel->payable_amount ?? 0);
                                $relPhone = $rel->customer_phone ?? $rel->phone ?? $rel->billing_phone ?? 'N/A';
                                $relName = $rel->customer_name ?? $rel->name ?? ($rel->customer?->name ?? 'Guest');
                            @endphp
                            <tr>
                                <td class="ps-3 fw-bold font-monospace">
                                    <a href="{{ route('admin.security.orders.show', $rel->id) }}" class="text-decoration-none text-dark hover-primary">
                                        #{{ $rel->order_number ?? $rel->id }}
                                    </a>
                                </td>
                                <td>{{ $relName }}</td>
                                <td class="font-monospace text-secondary">
                                    {{ $relPhone }}
                                    @if($relPhone !== 'N/A' && $customerPhone !== 'N/A' && $relPhone !== $customerPhone)
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-1 py-0 ms-1" style="font-size: 9px;" title="Different phone than current order">Different Phone</span>
                                    @endif
                                </td>
                                <td class="fw-bold">Tk {{ number_format($relAmount, 2) }}</td>
                                <td>
                                    <span class="badge {{ strtolower($rel->order_source ?? '') === 'pos' ? 'bg-info text-dark' : 'bg-secondary bg-opacity-10 text-secondary border' }}" style="font-size: 9.5px;">
                                        {{ strtoupper($rel->order_source ?? 'online') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-0.5" style="font-size: 10px;">
                                        {{ str_replace('_', ' ', ucfirst($rel->security_review_status ?? 'clean')) }}
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $rel->created_at ? $rel->created_at->format('M d, H:i') : 'N/A' }}</td>
                                <td class="pe-3 text-end">
                                    <a href="{{ route('admin.security.orders.show', $rel->id) }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 11px;">
                                        Review
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- RECENT ACTIVITY LOGS FROM THIS IP -->
    @if(!empty($ipLogs) && $ipLogs->isNotEmpty())
        <div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-timeline text-primary me-2"></i> Recent Network Hits from IP (Last 15 Requests)</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 11.5px;">
                    <thead class="table-light text-muted" style="font-size: 10.5px;">
                        <tr>
                            <th class="ps-3">Time</th>
                            <th>Method</th>
                            <th>URL Endpoint</th>
                            <th class="text-center">Status</th>
                            <th>Activity</th>
                            <th class="text-center">Risk</th>
                            <th class="pe-3">User Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ipLogs as $log)
                            <tr class="{{ ($log->security_status ?? '') === 'blocked' ? 'table-danger bg-opacity-25' : '' }}">
                                <td class="ps-3 font-monospace text-muted">{{ $log->created_at ? $log->created_at->format('H:i:s') : 'N/A' }}</td>
                                <td><span class="badge bg-light text-dark border px-1.5 py-0.5" style="font-size: 9.5px;">{{ $log->request_method ?? 'GET' }}</span></td>
                                <td class="font-monospace text-dark text-break" style="max-width: 250px;">{{ $log->url_path ?? '/' }}</td>
                                <td class="text-center">
                                    <span class="badge {{ ($log->response_status ?? 200) >= 400 ? 'bg-danger text-white' : 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' }} px-1.5 py-0.5" style="font-size: 9.5px;">
                                        {{ $log->response_status ?? 200 }}
                                    </span>
                                </td>
                                <td><span class="badge bg-secondary bg-opacity-10 text-secondary border px-1.5 py-0.5" style="font-size: 9.5px;">{{ $log->activity_type ?? 'traffic' }}</span></td>
                                <td class="text-center font-monospace">{{ $log->risk_score ?: 0 }}</td>
                                <td class="pe-3 text-muted text-truncate" style="max-width: 200px;" title="{{ $log->user_agent }}">{{ $log->user_agent ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<!-- MODAL: DECISION REASON NOTE -->
<div class="modal fade" id="modalDecisionNote" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2.5 px-3 bg-light">
                <h6 class="modal-title fw-bold text-dark"><i class="fas fa-pen-to-square me-2 text-primary"></i> Review Reason</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formSubmitDecision">
                <input type="hidden" id="decision_target_review">
                <input type="hidden" id="decision_target_order">

                <div class="modal-body p-3">
                    <p class="small text-muted mb-2" id="decision_modal_prompt">Apply decision to order?</p>
                    <label class="form-label small fw-semibold text-secondary">Audit Note (Optional)</label>
                    <textarea class="form-control form-control-sm" id="decision_admin_note" rows="2" placeholder="Audit reasoning visible in order log..."></textarea>
                </div>
                <div class="modal-footer py-2 px-3 bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3" id="btn-submit-decision-confirm">Confirm Decision</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: ENFORCE QUICK BLOCK (IP / USER) -->
<div class="modal fade" id="modalEnforceQuickBlock" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2.5 px-3 bg-light">
                <h6 class="modal-title fw-bold text-danger" id="enforceModalTitle">
                    <i class="fas fa-shield-virus me-2"></i> Enforcement Action
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formSubmitEnforceQuickBlock">
                <input type="hidden" id="enforce_action_type" name="action_type">
                <input type="hidden" id="enforce_target" name="target">

                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Target Entity</label>
                        <input type="text" class="form-control form-control-sm font-monospace bg-light" id="display_enforce_target" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Restriction Duration</label>
                        <select class="form-select form-select-sm" id="enforce_duration" name="duration">
                            <option value="60">1 Hour</option>
                            <option value="360">6 Hours</option>
                            <option value="1440" selected>24 Hours (1 Day)</option>
                            <option value="10080">7 Days</option>
                            <option value="0">Permanent</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-secondary">Reason <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="enforce_reason" name="reason" required value="Fraudulent multiple order attempt on order #{{ $order->order_number ?? $order->id }}">
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger px-3" id="btn-submit-enforce">Enforce Restriction</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = '{{ csrf_token() }}';

    // ১. ডিসিশন বাটন হ্যান্ডলার (Approve Clean, Hold, Flag Spam, Cancel Fraud)
    document.querySelectorAll('.btn-decision-action').forEach(btn => {
        btn.addEventListener('click', function() {
            const revStatus = this.getAttribute('data-status');
            const ordStatus = this.getAttribute('data-order-status');

            document.getElementById('decision_target_review').value = revStatus;
            document.getElementById('decision_target_order').value = ordStatus;
            document.getElementById('decision_modal_prompt').innerText = `Change review status to "${revStatus.replace(/_/g, ' ')}"?`;

            const modalEl = document.getElementById('modalDecisionNote');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        });
    });

    // ২. ডিসিশন ফর্ম সাবমিট
    const decisionForm = document.getElementById('formSubmitDecision');
    if (decisionForm) {
        decisionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-decision-confirm');
            btn.disabled = true;
            btn.innerText = 'Applying...';

            const payload = {
                review_status: document.getElementById('decision_target_review').value,
                order_status: document.getElementById('decision_target_order').value,
                admin_note: document.getElementById('decision_admin_note').value,
            };

            fetch(`{{ route('admin.security.orders.update_status', $order->id) }}`, {
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
                if (res.success) location.reload();
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerText = 'Confirm Decision';
            });
        });
    }

    // ৩. ক্লায়েন্ট আইপি ব্লক বাটন
    const blockIpBtn = document.getElementById('btn-block-client-ip');
    if (blockIpBtn) {
        blockIpBtn.addEventListener('click', function() {
            const ip = this.getAttribute('data-ip');
            document.getElementById('enforce_action_type').value = 'block_ip';
            document.getElementById('enforce_target').value = ip;
            document.getElementById('display_enforce_target').value = ip;
            document.getElementById('enforceModalTitle').innerHTML = `<i class="fas fa-shield-virus me-2"></i> Block Client IP: ${ip}`;

            const modalEl = document.getElementById('modalEnforceQuickBlock');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        });
    }

    // ৪. কাস্টমার ইউজার রেস্ট্রিক্ট বাটন
    const restrictUserBtn = document.getElementById('btn-restrict-customer-user');
    if (restrictUserBtn) {
        restrictUserBtn.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            document.getElementById('enforce_action_type').value = 'block_user';
            document.getElementById('enforce_target').value = userId;
            document.getElementById('display_enforce_target').value = `User Account ID: ${userId}`;
            document.getElementById('enforceModalTitle').innerHTML = `<i class="fas fa-user-xmark me-2"></i> Restrict Customer Account`;

            const modalEl = document.getElementById('modalEnforceQuickBlock');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        });
    }

    // ৫. কুইক এনফোর্সমেন্ট সাবমিট
    const enforceForm = document.getElementById('formSubmitEnforceQuickBlock');
    if (enforceForm) {
        enforceForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-enforce');
            btn.disabled = true;
            btn.innerText = 'Enforcing...';

            const payload = {
                action_type: document.getElementById('enforce_action_type').value,
                target: document.getElementById('enforce_target').value,
                duration: document.getElementById('enforce_duration').value,
                reason: document.getElementById('enforce_reason').value,
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
                if (res.success) location.reload();
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerText = 'Enforce Restriction';
            });
        });
    }
});
</script>
@endpush