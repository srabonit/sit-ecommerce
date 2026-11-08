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

<style>
    /* Security Order Review — Kaiadmin Classic / White */
    #security-order-details-wrapper {
        --security-primary: var(--bs-primary, #1572e8);
        --security-border: var(--bs-border-color, #e8edf3);
        --security-muted: #7d8795;
        --security-heading: #263445;
        --security-surface: #ffffff;
        --security-soft: #f7f9fc;
        color: var(--security-heading);
        min-width: 0;
    }

    #security-order-details-wrapper *,
    #security-order-details-wrapper *::before,
    #security-order-details-wrapper *::after,
    #security-order-details-wrapper .card,
    #security-order-details-wrapper .btn,
    #security-order-details-wrapper .form-control,
    #security-order-details-wrapper .form-select,
    #modalDecisionNote .modal-content,
    #modalEnforceQuickBlock .modal-content,
    #modalDecisionNote .btn,
    #modalEnforceQuickBlock .btn,
    #modalDecisionNote .form-control,
    #modalEnforceQuickBlock .form-control,
    #modalEnforceQuickBlock .form-select {
        border-radius: 5px !important;
    }

    #security-order-details-wrapper {
        padding: 1.25rem !important;
    }

    #security-order-details-wrapper .page-heading {
        min-width: 0;
    }

    #security-order-details-wrapper .order-title-line {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: .55rem;
    }

    #security-order-details-wrapper h4 {
        color: var(--security-heading) !important;
        font-size: clamp(1.05rem, 1.5vw, 1.35rem);
        letter-spacing: -.025em;
        line-height: 1.35;
    }

    #security-order-details-wrapper .page-meta {
        color: var(--security-muted);
        font-size: .78rem;
        line-height: 1.65;
    }

    #security-order-details-wrapper .card {
        border: 1px solid var(--security-border) !important;
        background: var(--security-surface);
        box-shadow: 0 2px 8px rgba(31, 45, 61, .045) !important;
        min-width: 0;
    }

    #security-order-details-wrapper .card-header {
        padding: .85rem 1rem !important;
        border-bottom: 1px solid var(--security-border) !important;
        background: #fff !important;
    }

    #security-order-details-wrapper .card-header h6 {
        color: var(--security-heading) !important;
        font-size: .86rem;
        font-weight: 700;
        line-height: 1.4;
    }

    #security-order-details-wrapper .card-body {
        min-width: 0;
    }

    #security-order-details-wrapper .btn {
        min-height: 34px;
        font-size: .78rem;
        font-weight: 600;
        padding: .42rem .72rem;
        box-shadow: none !important;
        white-space: normal;
    }

    #security-order-details-wrapper .btn-sm {
        min-height: 31px;
        padding: .35rem .62rem;
    }

    #security-order-details-wrapper .btn-group {
        display: inline-flex;
        flex-wrap: nowrap;
    }

    #security-order-details-wrapper .badge {
        border-radius: 5px !important;
        font-weight: 650;
        letter-spacing: .01em;
        line-height: 1.35;
        white-space: normal;
        vertical-align: middle;
    }

    #security-order-details-wrapper .table {
        --bs-table-color: #465365;
        --bs-table-hover-bg: #f8faff;
        margin-bottom: 0;
        vertical-align: middle;
    }

    #security-order-details-wrapper .table > :not(caption) > * > * {
        padding: .62rem .7rem;
        border-bottom-color: #edf0f4;
    }

    #security-order-details-wrapper .table thead th {
        color: #687587;
        background: #f7f9fc;
        font-size: .69rem;
        font-weight: 700;
        letter-spacing: .035em;
        text-transform: uppercase;
        white-space: nowrap;
        border-bottom: 1px solid var(--security-border);
    }

    #security-order-details-wrapper .table tbody td {
        font-size: .77rem;
        line-height: 1.5;
    }

    #security-order-details-wrapper .table-borderless > :not(caption) > * > * {
        border-bottom: 1px solid #f0f2f5;
        padding: .55rem .35rem;
    }

    #security-order-details-wrapper .table-borderless tbody tr:last-child td {
        border-bottom: 0;
    }

    #security-order-details-wrapper .table-borderless td:first-child {
        color: var(--security-muted);
        font-weight: 500;
        white-space: nowrap;
    }

    #security-order-details-wrapper .table-borderless td:last-child {
        color: #344054;
        overflow-wrap: anywhere;
    }

    #security-order-details-wrapper .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #security-order-details-wrapper .table-responsive > .table {
        min-width: 650px;
    }

    #security-order-details-wrapper .security-summary {
        border-left-width: 4px !important;
    }

    #security-order-details-wrapper .security-summary .card-body {
        padding: 1rem !important;
    }

    #security-order-details-wrapper .security-icon {
        width: 42px;
        height: 42px;
        min-width: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px !important;
    }

    #security-order-details-wrapper .security-summary h6 {
        color: var(--security-heading);
        font-size: .9rem;
    }

    #security-order-details-wrapper .security-summary p {
        overflow-wrap: anywhere;
        line-height: 1.55;
    }

    #security-order-details-wrapper .financial-total {
        background: var(--security-soft);
        border-radius: 5px !important;
        padding: .7rem .75rem;
    }

    #security-order-details-wrapper .audit-note {
        overflow-wrap: anywhere;
        border: 1px solid var(--security-border);
    }

    #security-order-details-wrapper .section-caption {
        color: var(--security-muted);
        font-size: .73rem;
        line-height: 1.5;
    }

    #security-order-details-wrapper .font-monospace {
        overflow-wrap: anywhere;
    }

    #security-order-details-wrapper .text-truncate {
        display: inline-block;
        max-width: 200px;
        vertical-align: middle;
    }

    #modalDecisionNote .modal-content,
    #modalEnforceQuickBlock .modal-content {
        border: 1px solid var(--security-border) !important;
        overflow: hidden;
    }

    #modalDecisionNote .modal-header,
    #modalDecisionNote .modal-footer,
    #modalEnforceQuickBlock .modal-header,
    #modalEnforceQuickBlock .modal-footer {
        background: #f7f9fc !important;
        border-color: var(--security-border);
    }

    #modalDecisionNote .modal-title,
    #modalEnforceQuickBlock .modal-title {
        font-size: .9rem;
    }

    #modalDecisionNote .form-control,
    #modalEnforceQuickBlock .form-control,
    #modalEnforceQuickBlock .form-select {
        min-height: 38px;
        border-color: #dfe4ec;
        font-size: .82rem;
    }

    #modalDecisionNote textarea.form-control {
        min-height: 76px;
    }

    @media (min-width: 1400px) {
        #security-order-details-wrapper {
            padding-left: 1.5rem !important;
            padding-right: 1.5rem !important;
        }
    }

    @media (max-width: 991.98px) {
        #security-order-details-wrapper {
            padding: 1rem !important;
        }

        #security-order-details-wrapper .row.g-4 {
            --bs-gutter-x: 1rem;
            --bs-gutter-y: 1rem;
        }

        #security-order-details-wrapper .page-actions {
            width: 100%;
        }
    }

    @media (max-width: 767.98px) {
        #security-order-details-wrapper {
            padding: .75rem !important;
        }

        #security-order-details-wrapper .page-topbar {
            align-items: flex-start !important;
        }

        #security-order-details-wrapper .page-topbar > div:first-child {
            width: 100%;
        }

        #security-order-details-wrapper .page-actions {
            display: flex;
            width: 100%;
            gap: .45rem !important;
        }

        #security-order-details-wrapper .page-actions > * {
            flex: 1 1 auto;
        }

        #security-order-details-wrapper .page-actions .btn-group {
            flex: 1 1 100%;
        }

        #security-order-details-wrapper .page-actions .btn-group .btn {
            flex: 1 1 50%;
        }

        #security-order-details-wrapper .security-summary .card-body > .d-flex {
            align-items: flex-start !important;
        }

        #security-order-details-wrapper .security-summary .card-body > .d-flex > .d-flex:last-child {
            width: 100%;
            flex-wrap: wrap;
        }

        #security-order-details-wrapper .security-summary .card-body > .d-flex > .d-flex:last-child .btn {
            flex: 1 1 auto;
        }

        #security-order-details-wrapper .card-header {
            padding: .75rem !important;
        }

        #security-order-details-wrapper .card-body {
            padding: .75rem !important;
        }

        #security-order-details-wrapper .table-borderless td:first-child {
            width: 42% !important;
            white-space: normal;
        }

        #security-order-details-wrapper .table-responsive > .table {
            min-width: 680px;
        }
    }

    @media (max-width: 575.98px) {
        #security-order-details-wrapper .order-title-line {
            gap: .4rem;
        }

        #security-order-details-wrapper h4 {
            font-size: 1rem;
        }

        #security-order-details-wrapper .page-meta {
            font-size: .72rem;
        }

        #security-order-details-wrapper .page-actions {
            flex-direction: column;
            align-items: stretch !important;
        }

        #security-order-details-wrapper .page-actions > *,
        #security-order-details-wrapper .page-actions .btn-group {
            width: 100%;
        }

        #security-order-details-wrapper .page-actions > .btn {
            text-align: center;
        }

        #security-order-details-wrapper .security-summary .card-body > .d-flex:first-child {
            gap: .65rem !important;
        }

        #security-order-details-wrapper .security-icon {
            width: 36px;
            height: 36px;
            min-width: 36px;
        }

        #security-order-details-wrapper .table-borderless tbody td {
            font-size: .74rem;
        }

        #security-order-details-wrapper .financial-total {
            gap: .5rem;
            align-items: flex-start !important;
        }

        #modalDecisionNote .modal-dialog,
        #modalEnforceQuickBlock .modal-dialog {
            margin: .6rem;
        }

        #modalDecisionNote .modal-footer,
        #modalEnforceQuickBlock .modal-footer {
            gap: .4rem;
        }

        #modalDecisionNote .modal-footer .btn,
        #modalEnforceQuickBlock .modal-footer .btn {
            flex: 1 1 auto;
        }
    }
</style>

<div class="container-fluid px-4 py-3" id="security-order-details-wrapper">
    <!-- Top Breadcrumb & Action Bar -->
    <div class="page-topbar d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
        <div class="page-heading">
            <div class="order-title-line">
                <a href="{{ route('admin.security.orders.index') }}" class="btn btn-sm btn-light border text-secondary" title="Back to Suspicious Orders">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center font-monospace">
                    <i class="fas fa-shield-halved text-danger me-2"></i> Order #{{ $order->order_number ?? $order->id }}
                </h4>
                <span class="badge {{ $statusBadge }} px-2 py-1" style="font-size: 11px;">
                    {{ str_replace('_', ' ', ucfirst($secStatus)) }}
                </span>
                <span class="badge {{ $riskBadgeClass }} font-monospace px-2 py-1" style="font-size: 11px;">
                    Risk: {{ $riskScore }}/100
                </span>
            </div>
            <p class="page-meta mb-0 mt-2">
                Placed on <strong>{{ $order->created_at ? $order->created_at->format('M d, Y h:i A') : 'N/A' }}</strong> | Channel: <strong>{{ strtoupper($order->order_source ?? 'online') }}</strong> | Status: <strong>{{ ucfirst($order->order_status ?? ($order->status ?? 'pending')) }}</strong>
            </p>
        </div>

        <!-- Decision & Action Buttons -->
        <div class="page-actions d-flex flex-wrap align-items-center gap-2">
            <!-- Core Order View Link -->
            @if(Route::has('admin.order.view'))
                <a href="{{ route('admin.order.view', $order->id) }}" class="btn btn-sm btn-light border text-secondary" target="_blank" title="View Full Order Invoice">
                    <i class="fas fa-receipt me-1"></i> Order Details
                </a>
            @endif

            <!-- Approve Clean -->
            <button type="button" class="btn btn-sm btn-success px-3 btn-decision-action" data-status="approved_by_admin" data-order-status="processing">
                <i class="fas fa-check-circle me-1"></i> Approve Clean
            </button>

            <!-- Hold for Verification -->
            <button type="button" class="btn btn-sm btn-warning px-3 btn-decision-action" data-status="under_review" data-order-status="on_hold">
                <i class="fas fa-pause-circle me-1"></i> Hold on Review
            </button>

            <!-- Mark as Spam / Cancel Fraud -->
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-danger btn-decision-action" data-status="flagged_spam" data-order-status="on_hold">
                    <i class="fas fa-flag me-1"></i> Flag Spam
                </button>
                <button type="button" class="btn btn-dark btn-decision-action" data-status="cancelled_fraud" data-order-status="cancelled">
                    <i class="fas fa-ban me-1"></i> Cancel Fraud
                </button>
            </div>
        </div>
    </div>

    <!-- FORENSIC RISK & SECURITY VIOLATION SUMMARY BANNER -->
    <div class="card security-summary mb-4 {{ $riskScore >= 50 ? 'border-start border-danger border-4' : 'border-start border-warning border-4' }} bg-white">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="security-icon p-2 {{ $riskScore >= 50 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-warning bg-opacity-15 text-warning-emphasis' }}">
                        <i class="fas fa-triangle-exclamation fa-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <h6 class="fw-bold mb-1">Security Risk Diagnostic</h6>
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
                            <button type="button" class="btn btn-sm btn-outline-danger" id="btn-block-client-ip" data-ip="{{ $order->client_ip }}">
                                <i class="fas fa-shield-virus me-1"></i> Block Client IP
                            </button>
                        @endif
                    @endif

                    @if(!empty($order->user_id))
                        <button type="button" class="btn btn-sm btn-outline-dark" id="btn-restrict-customer-user" data-user-id="{{ $order->user_id }}">
                            <i class="fas fa-user-xmark me-1"></i> Restrict Account
                        </button>
                    @endif
                </div>
            </div>

            @if(!empty($order->security_note))
                <div class="mt-3 pt-3 border-top">
                    <span class="fw-bold text-secondary small d-block mb-2"><i class="fas fa-clipboard-list me-1"></i> Admin Audit Log</span>
                    <pre class="audit-note bg-light p-3 small mb-0 font-monospace text-dark text-break" style="white-space: pre-wrap; font-size: 11px;">{{ $order->security_note }}</pre>
                </div>
            @endif
        </div>
    </div>

    <!-- ROW: CUSTOMER INFO, NETWORK TELEMETRY & FINANCIAL SUMMARY -->
    <div class="row g-4 mb-4">
        <!-- Customer & Delivery Telemetry -->
        <div class="col-lg-4">
            <div class="card h-100 bg-white">
                <div class="card-header">
                    <h6 class="fw-bold mb-0"><i class="fas fa-user-shield text-primary me-2"></i> Customer Identity &amp; Contact</h6>
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
            <div class="card h-100 bg-white">
                <div class="card-header">
                    <h6 class="fw-bold mb-0"><i class="fas fa-network-wired text-primary me-2"></i> Client Network Telemetry</h6>
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
                                        <span class="badge bg-danger px-2 py-1">Firewall Blocked</span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">Normal Traffic</span>
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
            <div class="card h-100 bg-white">
                <div class="card-header">
                    <h6 class="fw-bold mb-0"><i class="fas fa-file-invoice-dollar text-primary me-2"></i> Financial Summary</h6>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between mb-3 small text-muted">
                        <span>Subtotal:</span>
                        <span class="text-dark fw-semibold">Tk {{ number_format((float) ($order->subtotal ?? ($orderAmount - ($order->shipping_charge ?? 0))), 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 small text-muted">
                        <span>Delivery Charge:</span>
                        <span class="text-dark fw-semibold">Tk {{ number_format((float) ($order->shipping_charge ?? 0), 2) }}</span>
                    </div>
                    @if(!empty($order->discount_amount) && $order->discount_amount > 0)
                        <div class="d-flex justify-content-between mb-3 small text-danger">
                            <span>Discount / Coupon:</span>
                            <span class="fw-semibold">- Tk {{ number_format((float) $order->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="financial-total d-flex justify-content-between align-items-center border-top mb-3">
                        <span class="fw-bold text-dark">Total Amount:</span>
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
    <div class="card mb-4 bg-white overflow-hidden">
        <div class="card-header">
            <h6 class="fw-bold mb-0"><i class="fas fa-boxes-stacked text-primary me-2"></i> Ordered Products ({{ $order->items?->count() ?? 0 }})</h6>
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
                                <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 10px;">
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
        <div class="card mb-4 bg-white overflow-hidden">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h6 class="fw-bold mb-1"><i class="fas fa-layer-group text-danger me-2"></i> Other Orders from IP: {{ $order->client_ip }} ({{ $relatedOrders->count() }})</h6>
                    <small class="section-caption">Compare customer details across repeated order attempts</small>
                </div>
                <a href="{{ route('admin.security.orders.clusters', ['search' => $order->client_ip]) }}" class="btn btn-sm btn-link text-primary text-decoration-none fw-semibold">
                    Open Cluster <i class="fas fa-arrow-right ms-1"></i>
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
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-1 py-1 ms-1" style="font-size: 9px;" title="Different phone than current order">Different Phone</span>
                                    @endif
                                </td>
                                <td class="fw-bold">Tk {{ number_format($relAmount, 2) }}</td>
                                <td>
                                    <span class="badge {{ strtolower($rel->order_source ?? '') === 'pos' ? 'bg-info text-dark' : 'bg-secondary bg-opacity-10 text-secondary border' }}" style="font-size: 9.5px;">
                                        {{ strtoupper($rel->order_source ?? 'online') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 10px;">
                                        {{ str_replace('_', ' ', ucfirst($rel->security_review_status ?? 'clean')) }}
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $rel->created_at ? $rel->created_at->format('M d, H:i') : 'N/A' }}</td>
                                <td class="pe-3 text-end">
                                    <a href="{{ route('admin.security.orders.show', $rel->id) }}" class="btn btn-sm btn-outline-secondary py-1 px-2">
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
        <div class="card bg-white overflow-hidden mb-4">
            <div class="card-header">
                <h6 class="fw-bold mb-0"><i class="fas fa-timeline text-primary me-2"></i> Recent IP Activity <span class="section-caption">(Last 15 Requests)</span></h6>
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
                                <td><span class="badge bg-light text-dark border px-2 py-1" style="font-size: 9.5px;">{{ $log->request_method ?? 'GET' }}</span></td>
                                <td class="font-monospace text-dark text-break" style="max-width: 250px;">{{ $log->url_path ?? '/' }}</td>
                                <td class="text-center">
                                    <span class="badge {{ ($log->response_status ?? 200) >= 400 ? 'bg-danger text-white' : 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' }} px-2 py-1" style="font-size: 9.5px;">
                                        {{ $log->response_status ?? 200 }}
                                    </span>
                                </td>
                                <td><span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1" style="font-size: 9.5px;">{{ $log->activity_type ?? 'traffic' }}</span></td>
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
            <div class="modal-header py-2 px-3">
                <h6 class="modal-title fw-bold text-dark"><i class="fas fa-pen-to-square me-2 text-primary"></i> Review Reason</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formSubmitDecision">
                <input type="hidden" id="decision_target_review">
                <input type="hidden" id="decision_target_order">

                <div class="modal-body p-3">
                    <p class="small text-muted mb-3" id="decision_modal_prompt">Apply decision to order?</p>
                    <label class="form-label small fw-semibold text-secondary">Audit Note (Optional)</label>
                    <textarea class="form-control form-control-sm" id="decision_admin_note" rows="2" placeholder="Add a reason for the audit log..."></textarea>
                </div>
                <div class="modal-footer py-2 px-3">
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
            <div class="modal-header py-2 px-3">
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
                <div class="modal-footer py-2 px-3">
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
