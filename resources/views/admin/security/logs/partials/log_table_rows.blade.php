@forelse($logs as $log)
    @php
        $carbonCreated = !empty($log->created_at)
            ? \Carbon\Carbon::parse($log->created_at)
            : null;

        $statusBadge = match($log->security_status) {
            'blocked'    => 'bg-danger text-white',
            'flagged'    => 'bg-warning text-dark',
            'challenged' => 'bg-info text-dark',
            default      => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
        };

        $resp = (int) ($log->response_status ?? 200);

        $httpBadge = match(true) {
            $resp >= 200 && $resp < 300 => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
            $resp === 403 || $resp === 429 => 'bg-danger text-white',
            $resp >= 400 && $resp < 500 => 'bg-warning text-dark',
            $resp >= 500 => 'bg-dark text-white',
            default => 'bg-light text-secondary border',
        };

        $risk = (int) ($log->risk_score ?? 0);

        $riskBadge = match(true) {
            $risk >= 75 => 'bg-danger text-white',
            $risk >= 40 => 'bg-warning text-dark',
            default => 'bg-light text-muted border',
        };
    @endphp

    <tr id="log-row-{{ $log->id }}"
        class="{{ $log->security_status === 'blocked' ? 'table-danger bg-opacity-25' : '' }}">

        {{-- Date & Time --}}
        <td class="ps-3 font-monospace" style="font-size: 11px;">
            @if($carbonCreated)
                <div class="d-flex flex-column text-muted">
                    <span class="text-dark fw-bold">{{ $carbonCreated->format('H:i:s') }}</span>
                    <span>{{ $carbonCreated->format('M d, Y') }}</span>
                </div>
            @else
                <span class="text-muted small">N/A</span>
            @endif
        </td>

        {{-- IP Address --}}
        <td>
            <div class="d-flex flex-column">
                <a href="{{ route('admin.security.ips.show', $log->ip_address) }}"
                   class="font-monospace fw-bold text-dark text-decoration-none hover-primary"
                   style="font-size: 12px;">
                    {{ $log->ip_address }}
                </a>
                <span class="text-secondary small" style="font-size: 10px;">
                    <i class="fas fa-globe me-1"></i>{{ $log->country ?: 'Unknown' }}
                </span>
            </div>
        </td>

        {{-- User --}}
        <td>
            @if($log->user)
                <div class="d-flex align-items-center gap-1.5" style="font-size: 11px;">
                    <span class="badge bg-light text-dark border px-1 py-0.5 font-monospace">
                        #{{ $log->user_id }}
                    </span>
                    <span class="text-dark fw-semibold text-truncate"
                          style="max-width: 110px;"
                          title="{{ $log->user->name }}">
                        {{ $log->user->name }}
                    </span>
                </div>
            @else
                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-1.5 py-0.5"
                      style="font-size: 9.5px;">
                    Guest
                </span>
            @endif
        </td>

        {{-- Request --}}
        <td>
            <div class="d-flex align-items-center gap-1.5">
                <span class="badge bg-light text-secondary border px-1.5 py-0.5 font-monospace"
                      style="font-size: 9px;">
                    {{ strtoupper($log->request_method ?? 'GET') }}
                </span>

                <span class="font-monospace text-dark text-truncate d-inline-block fw-semibold"
                      style="max-width: 220px; font-size: 11.5px;"
                      title="{{ $log->url_path }}">
                    {{ $log->url_path }}
                </span>
            </div>
        </td>

        {{-- HTTP --}}
        <td class="text-center">
            <span class="badge {{ $httpBadge }} font-monospace px-1.5 py-0.5"
                  style="font-size: 10px;">
                {{ $resp }}
            </span>
        </td>

        {{-- Activity --}}
        <td>
            <span class="badge bg-secondary bg-opacity-10 text-secondary border px-1.5 py-0.5 text-uppercase"
                  style="font-size: 9px;">
                {{ str_replace('_', ' ', $log->activity_type ?? 'traffic') }}
            </span>
        </td>

        {{-- Risk --}}
        <td class="text-center font-monospace">
            @if($risk > 0)
                <span class="badge {{ $riskBadge }} px-1.5 py-0.5" style="font-size: 10px;">
                    {{ $risk }}
                </span>
            @else
                <span class="text-muted small">0</span>
            @endif
        </td>

        {{-- Security Status --}}
        <td class="text-center">
            <span class="badge {{ $statusBadge }} px-2 py-0.5 text-uppercase"
                  style="font-size: 9px;">
                {{ $log->security_status }}
            </span>
        </td>

        {{-- Action --}}
        <td class="pe-3 text-end text-nowrap">
            <button type="button"
                    class="btn btn-sm btn-outline-secondary py-0 px-2 btn-inspect-log"
                    data-id="{{ $log->id }}"
                    style="font-size: 11px;"
                    title="View Details">
                <i class="fas fa-search"></i>
            </button>
        </td>
    </tr>

@empty
    <tr>
        <td colspan="9" class="text-center py-5 text-muted">
            <i class="fas fa-list-check fa-3x text-secondary mb-2 opacity-50"></i>
            <h6 class="fw-semibold mb-1">No Logs Found</h6>
            <p class="small text-muted mb-0">Try changing your filters.</p>
        </td>
    </tr>
@endforelse