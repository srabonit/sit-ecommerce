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

    <tr id="url-row-{{ $attack->id }}" class="align-middle">

        {{-- URL Path & Endpoint --}}
        <td class="ps-3 py-3">
            <div class="d-flex align-items-start gap-2">
                <span class="badge {{ $methodColor }} px-2 py-1 flex-shrink-0 font-monospace"
                      style="font-size: 9px; font-weight: 700; border-radius: 5px;">
                    {{ strtoupper($attack->request_method ?? 'GET') }}
                </span>

                <div class="min-w-0">
                    <a href="{{ route('admin.security.urls.show', $attack->id) }}"
                       class="font-monospace fw-bold text-dark text-decoration-none hover-primary d-inline-block text-break"
                       title="{{ $attack->url_path }}"
                       style="max-width: 260px; font-size: 11.5px; line-height: 1.5;">
                        {{ Str::limit($attack->url_path, 45) }}
                    </a>

                    @if($hasSuspiciousPayload)
                        <div class="mt-2">
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 font-monospace d-inline-block text-truncate"
                                  style="max-width: 260px; font-size: 9px; font-weight: 600;"
                                  title="{{ $attack->sample_suspicious_payload }}">
                                <i class="fas fa-bug me-1"></i>
                                {{ Str::limit($attack->sample_suspicious_payload, 35) }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </td>

        {{-- Total Requests --}}
        <td class="text-center">
            <span class="fw-bold text-dark font-monospace" style="font-size: 12px;">
                {{ number_format($totalRequests) }}
            </span>
        </td>

        {{-- Failed Requests (4xx / 5xx) --}}
        <td class="text-center">
            @if($failedRequests > 0)
                <span class="badge bg-warning bg-opacity-15 text-warning-emphasis border border-warning border-opacity-50 px-2 py-1 font-monospace"
                      style="font-size: 10px; border-radius: 5px;">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    {{ number_format($failedRequests) }}
                </span>
            @else
                <span class="text-muted font-monospace" style="font-size: 11px;">0</span>
            @endif
        </td>

        {{-- Blocked Requests (403 / 429) --}}
        <td class="text-center">
            @if($blockedRequests > 0)
                <span class="badge bg-danger text-white px-2 py-1 font-monospace"
                      style="font-size: 10px; border-radius: 5px;">
                    <i class="fas fa-shield-alt me-1"></i>
                    {{ number_format($blockedRequests) }}
                </span>
            @else
                <span class="text-muted font-monospace" style="font-size: 11px;">0</span>
            @endif
        </td>

        {{-- Unique Attacking IPs --}}
        <td class="text-center">
            <span class="badge bg-light text-dark border px-2 py-1"
                  style="font-size: 10px; font-weight: 600; border-radius: 5px;">
                <i class="fas fa-network-wired text-secondary me-1"></i>
                {{ number_format($uniqueIps) }} IPs
            </span>
        </td>

        {{-- Last Attack Time --}}
        <td>
            @if($carbonLastAttack)
                <div class="d-flex flex-column gap-1">
                    <span class="text-dark fw-semibold" style="font-size: 11px;">
                        <i class="far fa-clock text-primary me-1"></i>
                        {{ $carbonLastAttack->diffForHumans() }}
                    </span>

                    <span class="text-muted font-monospace" style="font-size: 9.5px;">
                        {{ $carbonLastAttack->format('M d, Y H:i:s') }}
                    </span>
                </div>
            @else
                <span class="text-muted small">N/A</span>
            @endif
        </td>

        {{-- Actions --}}
        <td class="pe-3 text-end text-nowrap">
            <div class="btn-group btn-group-sm" role="group">

                {{-- Inspect Endpoint Details & Attacking IPs --}}
                <a href="{{ route('admin.security.urls.show', $attack->id) }}"
                   class="btn btn-outline-secondary"
                   title="Inspect Attackers & Timeline"
                   aria-label="Inspect Attackers & Timeline">
                    <i class="fas fa-eye"></i>
                </a>

                {{-- Bulk Block Attacking IPs --}}
                <button type="button"
                        class="btn btn-outline-danger btn-trigger-bulk-block"
                        data-id="{{ $attack->id }}"
                        data-path="{{ $attack->url_path }}"
                        data-ips="{{ $uniqueIps }}"
                        title="Bulk Block All Attacking IPs"
                        aria-label="Bulk Block All Attacking IPs"
                        {{ $uniqueIps <= 0 ? 'disabled' : '' }}>
                    <i class="fas fa-shield-virus"></i>
                </button>

                {{-- Reset Metric Counter --}}
                <button type="button"
                        class="btn btn-outline-secondary btn-reset-url-metrics"
                        data-id="{{ $attack->id }}"
                        data-path="{{ $attack->url_path }}"
                        title="Reset Metrics"
                        aria-label="Reset Metrics">
                    <i class="fas fa-rotate-left"></i>
                </button>

            </div>
        </td>
    </tr>

@empty
    <tr>
        <td colspan="7" class="text-center py-5">
            <div class="d-flex flex-column align-items-center justify-content-center">

                <div class="d-flex align-items-center justify-content-center bg-success bg-opacity-10 rounded-circle mb-3"
                     style="width: 58px; height: 58px; border-radius: 5px !important;">
                    <i class="fas fa-shield-halved fa-2x text-success"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1" style="font-size: 13px;">
                    No Attacked URLs Recorded
                </h6>

                <p class="text-muted small mb-0" style="font-size: 11px;">
                    No abnormal endpoint scanning or malicious URL queries detected.
                </p>

            </div>
        </td>
    </tr>
@endforelse