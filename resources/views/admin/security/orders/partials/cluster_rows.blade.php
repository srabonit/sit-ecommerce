@forelse($clusters as $cluster)
    <tr>
        <!-- 1. Client IP & Telemetry Details -->
        <td class="ps-3">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.security.ips.show', $cluster->client_ip) }}" 
                   class="font-monospace fw-bold text-dark text-decoration-none hover-primary" 
                   title="Inspect IP Details">
                    {{ $cluster->client_ip }}
                </a>
                @if($cluster->is_blocked)
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" style="font-size: 10px;">
                        <i class="fas fa-ban me-1"></i> Blocked
                    </span>
                @else
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" style="font-size: 10px;">
                        <i class="fas fa-check-circle me-1"></i> Active
                    </span>
                @endif
            </div>
            <div class="small text-muted mt-0.5" style="font-size: 11px;">
                <a href="{{ route('admin.security.logs.index', ['ip' => $cluster->client_ip]) }}" class="text-decoration-none text-muted" target="_blank" title="View activity audit trail">
                    <i class="fas fa-external-link-alt me-1"></i> View Logs
                </a>
            </div>
        </td>

        <!-- 2. Total Orders Generated -->
        <td class="text-center">
            <span class="badge bg-primary bg-opacity-15 text-primary fw-bold px-2.5 py-1.5" style="font-size: 12px;">
                {{ number_format($cluster->total_orders_count) }} Orders
            </span>
        </td>

        <!-- 3. Cumulative Value (৳) -->
        <td>
            <span class="fw-bold text-dark font-monospace">
                ৳{{ number_format((float) ($cluster->total_order_value ?? 0), 2) }}
            </span>
        </td>

        <!-- 4. Customer Identities (Phone Variations) -->
        <td>
            @if($cluster->unique_phones_count > 1)
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 d-inline-flex align-items-center gap-1" title="Fraud Warning: Multiple different phone numbers used under same IP">
                    <i class="fas fa-exclamation-triangle"></i> Multi-Identity ({{ $cluster->unique_phones_count }} phones)
                </span>
            @else
                <span class="badge bg-light text-secondary border">
                    <i class="fas fa-phone me-1"></i> 1 Phone
                </span>
            @endif
        </td>

        <!-- 5. User Accounts -->
        <td class="text-center">
            <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                <i class="fas fa-user-friends me-1"></i> {{ $cluster->unique_users_count }}
            </span>
        </td>

        <!-- 6. Highest Risk Score -->
        <td class="text-center">
            @php
                $score = (int) ($cluster->highest_risk_score ?? 0);
                $badgeClass = match(true) {
                    $score >= 75 => 'bg-danger text-white',
                    $score >= 50 => 'bg-warning text-dark',
                    $score >= 35 => 'bg-info text-dark',
                    default      => 'bg-secondary bg-opacity-25 text-secondary',
                };
            @endphp
            <span class="badge {{ $badgeClass }} px-2 py-1 font-monospace" style="font-size: 11px;">
                {{ $score }}/100
            </span>
        </td>

        <!-- 7. Order Timeline Span -->
        <td>
            <div class="small text-muted" style="font-size: 11px;">
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
        <td class="pe-3 text-end">
            <div class="d-inline-flex gap-1">
                <!-- Filter Orders by this IP -->
                <a href="{{ route('admin.security.orders.index', ['search' => $cluster->client_ip]) }}" 
                   class="btn btn-sm btn-light border text-primary shadow-sm" 
                   title="View orders from this IP">
                    <i class="fas fa-list"></i>
                </a>

                <!-- Quick Block / Unblock Action -->
                @if(!$cluster->is_blocked)
                    <button type="button" 
                            class="btn btn-sm btn-outline-danger btn-cluster-block-ip shadow-sm" 
                            data-ip="{{ $cluster->client_ip }}" 
                            title="Block this IP address">
                        <i class="fas fa-ban"></i>
                    </button>
                @else
                    <button type="button" 
                            class="btn btn-sm btn-outline-success btn-cluster-unblock-ip shadow-sm" 
                            data-ip="{{ $cluster->client_ip }}" 
                            title="Lift Firewall Block">
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
            No suspicious multi-order IP clusters found at this moment.
        </td>
    </tr>
@endforelse