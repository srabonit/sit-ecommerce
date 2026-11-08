
@forelse($clusters as $cluster)
    <tr class="align-middle">
        <!-- 1. Client IP & Telemetry Details -->
        <td class="ps-3 py-2">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <a href="{{ route('admin.security.ips.show', $cluster->client_ip) }}"
                   class="font-monospace fw-bold text-dark text-decoration-none hover-primary"
                   title="Inspect IP Details">
                    {{ $cluster->client_ip }}
                </a>

                @if($cluster->is_blocked)
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1"
                          style="font-size: 10px; border-radius: 5px;">
                        <i class="fas fa-ban me-1"></i> Blocked
                    </span>
                @else
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1"
                          style="font-size: 10px; border-radius: 5px;">
                        <i class="fas fa-check-circle me-1"></i> Active
                    </span>
                @endif
            </div>

            <div class="small text-muted mt-1">
                <a href="{{ route('admin.security.logs.index', ['ip' => $cluster->client_ip]) }}"
                   class="text-decoration-none text-muted"
                   target="_blank"
                   title="View activity audit trail">
                    <i class="fas fa-external-link-alt me-1"></i> View Logs
                </a>
            </div>
        </td>

        <!-- 2. Total Orders Generated -->
        <td class="text-center py-2">
            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-2"
                  style="font-size: 11px; border-radius: 5px;">
                {{ number_format($cluster->total_orders_count) }} Orders
            </span>
        </td>

        <!-- 3. Cumulative Value (৳) -->
        <td class="py-2">
            <span class="fw-bold text-dark font-monospace text-nowrap">
                ৳{{ number_format((float) ($cluster->total_order_value ?? 0), 2) }}
            </span>
        </td>

        <!-- 4. Customer Identities (Phone Variations) -->
        <td class="py-2">
            @if($cluster->unique_phones_count > 1)
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 d-inline-flex align-items-center gap-1 px-2 py-1"
                      title="Fraud Warning: Multiple different phone numbers used under same IP"
                      style="font-size: 10px; border-radius: 5px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    Multi-Identity ({{ $cluster->unique_phones_count }} phones)
                </span>
            @else
                <span class="badge bg-light text-secondary border px-2 py-1"
                      style="font-size: 10px; border-radius: 5px;">
                    <i class="fas fa-phone me-1"></i> 1 Phone
                </span>
            @endif
        </td>

        <!-- 5. User Accounts -->
        <td class="text-center py-2">
            <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1"
                  style="font-size: 11px; border-radius: 5px;">
                <i class="fas fa-user-friends me-1"></i>
                {{ $cluster->unique_users_count }}
            </span>
        </td>

        <!-- 6. Highest Risk Score -->
        <td class="text-center py-2">
            @php
                $score = (int) ($cluster->highest_risk_score ?? 0);
                $badgeClass = match(true) {
                    $score >= 75 => 'bg-danger text-white',
                    $score >= 50 => 'bg-warning text-dark',
                    $score >= 35 => 'bg-info text-dark',
                    default      => 'bg-secondary bg-opacity-25 text-secondary',
                };
            @endphp

            <span class="badge {{ $badgeClass }} px-2 py-1 font-monospace"
                  style="font-size: 11px; border-radius: 5px;">
                {{ $score }}/100
            </span>
        </td>

        <!-- 7. Order Timeline Span -->
        <td class="py-2">
            <div class="small text-muted" style="font-size: 11px; line-height: 1.7;">
                <div>
                    <strong class="text-secondary">First:</strong>
                    {{ !empty($cluster->first_order_time) ? \Carbon\Carbon::parse($cluster->first_order_time)->format('d M, Y H:i') : 'N/A' }}
                </div>
                <div>
                    <strong class="text-secondary">Latest:</strong>
                    {{ !empty($cluster->latest_order_time) ? \Carbon\Carbon::parse($cluster->latest_order_time)->diffForHumans() : 'N/A' }}
                </div>
            </div>
        </td>

        <!-- 8. Actions -->
        <td class="pe-3 py-2 text-end">
            <div class="d-inline-flex align-items-center gap-1">
                <!-- Filter Orders by this IP -->
                <a href="{{ route('admin.security.orders.index', ['search' => $cluster->client_ip]) }}"
                   class="btn btn-sm btn-light border text-primary"
                   style="width: 32px; height: 32px; padding: 0; border-radius: 5px;"
                   title="View orders from this IP"
                   aria-label="View orders from this IP">
                    <i class="fas fa-list"></i>
                </a>

                <!-- Quick Block / Unblock Action -->
                @if(!$cluster->is_blocked)
                    <button type="button"
                            class="btn btn-sm btn-outline-danger btn-cluster-block-ip"
                            data-ip="{{ $cluster->client_ip }}"
                            style="width: 32px; height: 32px; padding: 0; border-radius: 5px;"
                            title="Block this IP address"
                            aria-label="Block this IP address">
                        <i class="fas fa-ban"></i>
                    </button>
                @else
                    <button type="button"
                            class="btn btn-sm btn-outline-success btn-cluster-unblock-ip"
                            data-ip="{{ $cluster->client_ip }}"
                            style="width: 32px; height: 32px; padding: 0; border-radius: 5px;"
                            title="Lift Firewall Block"
                            aria-label="Lift Firewall Block">
                        <i class="fas fa-lock-open"></i>
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center py-5 text-muted">
            <i class="fas fa-shield-alt fa-2x text-muted opacity-50 mb-2 d-block"></i>
            <span class="fw-semibold d-block">No Suspicious IP Clusters Found</span>
            <small class="text-muted">No matching multi-order activity detected.</small>
        </td>
    </tr>
@endforelse
