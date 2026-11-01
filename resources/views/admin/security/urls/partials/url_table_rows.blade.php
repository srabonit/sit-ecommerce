@forelse($urlAttacks as $attack)
    @php
        $methodColor = match(strtoupper($attack->request_method ?? 'GET')) {
            'POST'   => 'bg-primary text-white',
            'GET'    => 'bg-secondary bg-opacity-10 text-secondary border',
            'PUT'    => 'bg-warning text-dark',
            'DELETE' => 'bg-danger text-white',
            default  => 'bg-light text-dark border',
        };

        $hasSuspiciousPayload = !empty($attack->sample_suspicious_payload);
        $totalRequests = (int) ($attack->total_requests ?? 0);
        $failedRequests = (int) ($attack->failed_requests ?? 0);
        $blockedRequests = (int) ($attack->blocked_requests ?? 0);
        $uniqueIps = (int) ($attack->unique_ips_count ?? 0);
        
        $carbonLastAttack = !empty($attack->last_attack_at) 
            ? \Carbon\Carbon::parse($attack->last_attack_at) 
            : null;
    @endphp
    <tr id="url-row-{{ $attack->id }}">
        <!-- URL Path & Endpoint -->
        <td class="ps-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge {{ $methodColor }} px-1.5 py-0.5" style="font-size: 9.5px;">
                    {{ strtoupper($attack->request_method ?? 'GET') }}
                </span>
                <a href="{{ route('admin.security.urls.show', $attack->id) }}" 
                   class="font-monospace fw-bold text-dark text-decoration-none hover-primary text-break" 
                   title="{{ $attack->url_path }}" 
                   style="max-width: 260px;">
                    {{ Str::limit($attack->url_path, 45) }}
                </a>
            </div>
            @if($hasSuspiciousPayload)
                <div class="mt-1">
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-1.5 py-0.5 font-monospace text-truncate d-inline-block" 
                          style="max-width: 260px; font-size: 9.5px;" 
                          title="{{ $attack->sample_suspicious_payload }}">
                        <i class="fas fa-bug me-1"></i> {{ Str::limit($attack->sample_suspicious_payload, 35) }}
                    </span>
                </div>
            @endif
        </td>

        <!-- Total Requests -->
        <td class="text-center fw-bold text-dark font-monospace">
            {{ number_format($totalRequests) }}
        </td>

        <!-- Failed Requests (4xx / 5xx) -->
        <td class="text-center font-monospace">
            @if($failedRequests > 0)
                <span class="badge bg-warning bg-opacity-15 text-warning-emphasis border border-warning border-opacity-50 px-2 py-0.5">
                    {{ number_format($failedRequests) }}
                </span>
            @else
                <span class="text-muted small">0</span>
            @endif
        </td>

        <!-- Blocked Requests (403 / 429) -->
        <td class="text-center font-monospace">
            @if($blockedRequests > 0)
                <span class="badge bg-danger text-white px-2 py-0.5">
                    {{ number_format($blockedRequests) }}
                </span>
            @else
                <span class="text-muted small">0</span>
            @endif
        </td>

        <!-- Unique Attacking IPs -->
        <td class="text-center">
            <span class="badge bg-light text-dark border px-2 py-1">
                <i class="fas fa-network-wired text-secondary me-1"></i> {{ number_format($uniqueIps) }} IPs
            </span>
        </td>

        <!-- Last Attack Time -->
        <td>
            @if($carbonLastAttack)
                <div class="d-flex flex-column text-muted" style="font-size: 11px;">
                    <span class="text-dark fw-semibold">{{ $carbonLastAttack->diffForHumans() }}</span>
                    <span class="text-secondary" style="font-size: 10px;">{{ $carbonLastAttack->format('M d, Y H:i:s') }}</span>
                </div>
            @else
                <span class="text-muted small">N/A</span>
            @endif
        </td>

        <!-- Actions -->
        <td class="pe-3 text-end text-nowrap">
            <div class="btn-group btn-group-sm shadow-sm">
                <!-- Inspect Endpoint Details & Attacking IPs -->
                <a href="{{ route('admin.security.urls.show', $attack->id) }}" 
                   class="btn btn-outline-secondary" 
                   title="Inspect Attackers & Timeline">
                    <i class="fas fa-eye"></i>
                </a>

                <!-- Bulk Block Attacking IPs -->
                <button type="button" 
                        class="btn btn-outline-danger btn-trigger-bulk-block" 
                        data-id="{{ $attack->id }}" 
                        data-path="{{ $attack->url_path }}" 
                        data-ips="{{ $uniqueIps }}" 
                        title="Bulk Block All Attacking IPs"
                        {{ $uniqueIps <= 0 ? 'disabled' : '' }}>
                    <i class="fas fa-shield-virus"></i>
                </button>

                <!-- Reset Metric Counter -->
                <button type="button" 
                        class="btn btn-outline-light text-muted border btn-reset-url-metrics" 
                        data-id="{{ $attack->id }}" 
                        data-path="{{ $attack->url_path }}" 
                        title="Reset Metrics">
                    <i class="fas fa-rotate-left"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center py-5 text-muted">
            <div class="d-flex flex-column align-items-center">
                <i class="fas fa-shield-halved fa-3x text-success mb-2 opacity-50"></i>
                <h6 class="fw-semibold mb-1">No Attacked URLs Recorded</h6>
                <p class="small text-muted mb-0">No abnormal endpoint scanning or malicious URL queries detected.</p>
            </div>
        </td>
    </tr>
@endforelse