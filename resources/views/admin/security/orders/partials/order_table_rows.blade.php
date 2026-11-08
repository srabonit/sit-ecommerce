
@forelse($orders as $order)
    @php
        $riskScore = (int) ($order->security_risk_score ?? 0);
        $riskBadge = match(true) {
            $riskScore >= 75 => 'bg-danger text-white',
            $riskScore >= 50 => 'bg-danger bg-opacity-75 text-white',
            $riskScore >= 35 => 'bg-warning text-dark',
            default          => 'bg-success text-white',
        };

        $reviewStatus = $order->security_review_status ?? 'under_review';
        $statusBadge = match($reviewStatus) {
            'approved_by_admin', 'clean' => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
            'under_review'               => 'bg-warning bg-opacity-15 text-warning-emphasis border border-warning border-opacity-50',
            'flagged_spam'               => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
            'cancelled_fraud'            => 'bg-dark text-white',
            default                      => 'bg-light text-secondary border',
        };

        $isIpBlocked = $order->is_ip_blocked ?? false;
        $orderSource = strtolower($order->order_source ?? 'online');

        // Dynamic Amount & Customer Information
        $orderAmount = $order->grand_total ?? $order->total ?? $order->total_amount ?? $order->payable_amount ?? 0;
        $customerPhone = $order->customer_phone ?? $order->phone ?? $order->billing_phone ?? 'N/A';
        $customerName = $order->customer_name ?? $order->name ?? ($order->customer?->name ?? 'Guest');
        $customerEmail = $order->customer_email ?? $order->email ?? ($order->customer?->email ?? null);
    @endphp

    <tr
        id="order-row-{{ $order->id }}"
        class="{{ $reviewStatus === 'flagged_spam' ? 'table-danger bg-opacity-25' : '' }}"
    >
        {{-- Order Number & Source --}}
        <td class="ps-3 align-middle">
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                <a
                    href="{{ route('admin.security.orders.show', $order->id) }}"
                    class="fw-bold font-monospace text-dark text-decoration-none hover-primary"
                >
                    #{{ $order->order_number ?? $order->id }}
                </a>

                <span
                    class="badge {{ $orderSource === 'pos' ? 'bg-info bg-opacity-10 text-info border border-info border-opacity-25' : 'bg-secondary bg-opacity-10 text-secondary border' }} px-2 py-1"
                    style="font-size: 10px;"
                >
                    {{ strtoupper($orderSource) }}
                </span>
            </div>

            <div class="d-flex flex-column gap-1">
                <small class="text-muted" style="font-size: 11px;">
                    <i class="far fa-calendar-alt me-1"></i>
                    {{ $order->created_at ? $order->created_at->format('M d, Y h:i A') : 'N/A' }}
                </small>

                @if($order->created_at)
                    <small class="text-muted" style="font-size: 10px;">
                        <i class="far fa-clock me-1"></i>
                        {{ $order->created_at->diffForHumans() }}
                    </small>
                @endif
            </div>
        </td>

        {{-- Customer Identity --}}
        <td class="align-middle">
            <div class="d-flex flex-column gap-1">
                <span class="fw-semibold text-dark" style="font-size: 12.5px;">
                    {{ $customerName }}
                </span>

                <span class="font-monospace text-secondary" style="font-size: 11px;">
                    <i class="fas fa-phone-alt me-1 text-muted"></i>
                    {{ $customerPhone }}
                </span>

                @if(!empty($customerEmail))
                    <span
                        class="text-muted text-truncate"
                        style="font-size: 10.5px; max-width: 170px;"
                        title="{{ $customerEmail }}"
                    >
                        <i class="far fa-envelope me-1"></i>
                        {{ $customerEmail }}
                    </span>
                @endif
            </div>
        </td>

        {{-- Order Financial Summary --}}
        <td class="align-middle">
            <div class="d-flex flex-column gap-1">
                <span class="fw-bold text-dark" style="font-size: 13px;">
                    Tk {{ number_format((float) $orderAmount, 2) }}
                </span>

                <small class="text-muted" style="font-size: 10.5px;">
                    <i class="fas fa-boxes me-1"></i>
                    Items: <strong>{{ $order->items?->sum('quantity') ?: 1 }}</strong>
                </small>

                <span class="text-secondary fw-semibold" style="font-size: 10px;">
                    <i class="far fa-credit-card me-1"></i>
                    {{ strtoupper($order->payment_method ?? 'COD') }}
                </span>
            </div>
        </td>

        {{-- Client IP Address & Firewall Status --}}
        <td class="align-middle">
            @if(!empty($order->client_ip))
                <div class="d-flex flex-column align-items-start gap-1">
                    <a
                        href="{{ route('admin.security.ips.show', $order->client_ip) }}"
                        class="font-monospace fw-semibold text-dark text-decoration-none hover-primary"
                        style="font-size: 11.5px;"
                    >
                        <i class="fas fa-network-wired me-1 text-muted"></i>
                        {{ $order->client_ip }}
                    </a>

                    @if($isIpBlocked)
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1" style="font-size: 9.5px;">
                            <i class="fas fa-ban me-1"></i>
                            IP Blocked
                        </span>
                    @else
                        <span class="text-success fw-medium" style="font-size: 10px;">
                            <i class="fas fa-circle me-1" style="font-size: 6px;"></i>
                            IP Active
                        </span>
                    @endif
                </div>
            @else
                <span class="text-muted small">N/A</span>
            @endif
        </td>

        {{-- Risk Calculation --}}
        <td class="text-center align-middle">
            <div class="d-flex flex-column align-items-center gap-1">
                <span class="badge {{ $riskBadge }} font-monospace px-2 py-1" style="font-size: 11px;">
                    {{ $riskScore }}/100
                </span>

                <small class="text-muted" style="font-size: 9px;">
                    Risk Score
                </small>
            </div>
        </td>

        {{-- Detected Violation / Suspicious Reason --}}
        <td class="align-middle" style="max-width: 220px;">
            <div class="d-flex align-items-start gap-2">
                <i class="fas fa-triangle-exclamation text-warning mt-1" style="font-size: 11px;"></i>

                <div class="min-w-0">
                    <div
                        class="text-dark text-truncate"
                        style="font-size: 11.5px;"
                        title="{{ $order->suspicious_reason ?? 'Velocity anomaly detected' }}"
                    >
                        {{ $order->suspicious_reason ?? 'Multiple rapid order submissions' }}
                    </div>

                    @if(!empty($order->security_note))
                        <small
                            class="text-muted d-block text-truncate mt-1"
                            style="font-size: 10px;"
                            title="{{ $order->security_note }}"
                        >
                            <i class="far fa-sticky-note me-1"></i>
                            Note: {{ $order->security_note }}
                        </small>
                    @endif
                </div>
            </div>
        </td>

        {{-- Current Review Status --}}
        <td class="text-center align-middle">
            <span class="badge {{ $statusBadge }} px-2 py-1 text-nowrap" style="font-size: 10.5px;">
                {{ str_replace('_', ' ', ucfirst($reviewStatus)) }}
            </span>
        </td>

        {{-- Administrative Actions --}}
        <td class="pe-3 text-end text-nowrap align-middle">
            <div class="btn-group btn-group-sm">
                {{-- Inspect Order Details --}}
                <a
                    href="{{ route('admin.security.orders.show', $order->id) }}"
                    class="btn btn-outline-secondary"
                    title="View Security Audit"
                    aria-label="View Security Audit"
                >
                    <i class="fas fa-shield-halved"></i>
                </a>

                {{-- Quick Action Dropdown --}}
                <button
                    type="button"
                    class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Toggle Order Actions"
                >
                    <span class="visually-hidden">Toggle Actions</span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow-sm border" style="font-size: 12px;">
                    <li>
                        <h6 class="dropdown-header text-uppercase fw-semibold" style="font-size: 10px;">
                            Review Decision
                        </h6>
                    </li>

                    <li>
                        <button
                            type="button"
                            class="dropdown-item text-success btn-update-order-status"
                            data-id="{{ $order->id }}"
                            data-status="approved_by_admin"
                            data-order-status="processing"
                        >
                            <i class="fas fa-check-circle me-2"></i>
                            Approve as Clean
                        </button>
                    </li>

                    <li>
                        <button
                            type="button"
                            class="dropdown-item text-warning btn-update-order-status"
                            data-id="{{ $order->id }}"
                            data-status="under_review"
                            data-order-status="on_hold"
                        >
                            <i class="fas fa-pause-circle me-2"></i>
                            Hold for Verification
                        </button>
                    </li>

                    <li>
                        <button
                            type="button"
                            class="dropdown-item text-danger btn-update-order-status"
                            data-id="{{ $order->id }}"
                            data-status="flagged_spam"
                            data-order-status="on_hold"
                        >
                            <i class="fas fa-flag me-2"></i>
                            Flag as Fraud / Spam
                        </button>
                    </li>

                    <li>
                        <button
                            type="button"
                            class="dropdown-item text-dark btn-update-order-status"
                            data-id="{{ $order->id }}"
                            data-status="cancelled_fraud"
                            data-order-status="cancelled"
                        >
                            <i class="fas fa-ban me-2"></i>
                            Cancel Order (Fraud)
                        </button>
                    </li>

                    @if(!empty($order->client_ip) || !empty($order->user_id))
                        <li><hr class="dropdown-divider"></li>

                        <li>
                            <h6 class="dropdown-header text-uppercase fw-semibold" style="font-size: 10px;">
                                Enforcement
                            </h6>
                        </li>

                        @if(!empty($order->client_ip) && !$isIpBlocked)
                            <li>
                                <button
                                    type="button"
                                    class="dropdown-item text-danger btn-quick-block-trigger"
                                    data-type="block_ip"
                                    data-target="{{ $order->client_ip }}"
                                    data-title="Block IP: {{ $order->client_ip }}"
                                >
                                    <i class="fas fa-shield-virus me-2"></i>
                                    Block Client IP
                                </button>
                            </li>
                        @endif

                        @if(!empty($order->user_id))
                            <li>
                                <button
                                    type="button"
                                    class="dropdown-item text-danger btn-quick-block-trigger"
                                    data-type="block_user"
                                    data-target="{{ $order->user_id }}"
                                    data-title="Restrict User Account ID: {{ $order->user_id }}"
                                >
                                    <i class="fas fa-user-slash me-2"></i>
                                    Restrict User Account
                                </button>
                            </li>
                        @endif
                    @endif
                </ul>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center py-5">
            <div class="d-flex flex-column align-items-center justify-content-center gap-2">
                <div
                    class="d-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 text-success"
                    style="width: 58px; height: 58px;"
                >
                    <i class="fas fa-shield-check fa-2x"></i>
                </div>

                <div>
                    <h6 class="fw-semibold text-dark mb-1">
                        No Suspicious Orders Found
                    </h6>

                    <p class="text-muted small mb-0">
                        All current orders comply with velocity limits and security policies.
                    </p>
                </div>
            </div>
        </td>
    </tr>
@endforelse
