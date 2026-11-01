@forelse($blockedNetworks as $network)
    @php
        $carbonExpires = !empty($network->expires_at) ? \Carbon\Carbon::parse($network->expires_at) : null;
        $carbonCreated = !empty($network->created_at) ? \Carbon\Carbon::parse($network->created_at) : null;

        $isExpired = $carbonExpires && $carbonExpires->isPast();
        $isCurrentlyActive = $network->is_active && !$isExpired;
        $isPermanent = is_null($network->expires_at);
        $version = $network->ip_version ?? (str_contains($network->cidr_range, ':') ? 'v6' : 'v4');
    @endphp
    <tr id="blocked-network-row-{{ $network->id }}" class="{{ !$isCurrentlyActive ? 'table-light opacity-75' : '' }}">
        <!-- CIDR Subnet & IP Version -->
        <td class="ps-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-1.5 py-0.5 font-monospace" style="font-size: 9.5px;">
                    IP{{ strtoupper($version) }}
                </span>
                <span class="font-monospace fw-bold text-dark fs-6">
                    {{ $network->cidr_range }}
                </span>
            </div>
            @if(!empty($network->admin_note))
                <small class="text-muted text-truncate d-block font-monospace mt-0.5" style="max-width: 250px; font-size: 10px;" title="{{ $network->admin_note }}">
                    <i class="fas fa-note-sticky me-1 text-secondary"></i> {{ $network->admin_note }}
                </small>
            @endif
        </td>

        <!-- IP Coverage -->
        <td class="text-center">
            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-0.5 font-monospace" style="font-size: 10.5px;">
                ~{{ number_format((int) ($network->estimated_ip_count ?? 1)) }} IPs
            </span>
        </td>

        <!-- Reason & Creator -->
        <td style="max-width: 250px;">
            <div class="small text-truncate fw-semibold text-dark" style="font-size: 12px;" title="{{ $network->block_reason }}">
                {{ $network->block_reason }}
            </div>
            <small class="text-secondary" style="font-size: 10px;">
                {{ $network->creator ? $network->creator->name : 'System' }}
            </small>
        </td>

        <!-- Blocked At -->
        <td>
            @if($carbonCreated)
                <div class="d-flex flex-column text-muted" style="font-size: 11px;">
                    <span class="text-dark fw-semibold">{{ $carbonCreated->diffForHumans() }}</span>
                    <span class="text-secondary" style="font-size: 10px;">{{ $carbonCreated->format('M d, H:i') }}</span>
                </div>
            @else
                <span class="text-muted small">N/A</span>
            @endif
        </td>

        <!-- Expiration -->
        <td>
            @if($isPermanent)
                <span class="text-danger fw-semibold small">Never</span>
            @elseif($carbonExpires)
                @if($isExpired)
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-1.5 py-0.5" style="font-size: 9.5px;">Expired</span>
                @else
                    <span class="text-dark small fw-semibold">{{ $carbonExpires->diffForHumans() }}</span>
                @endif
            @else
                <span class="text-muted small">N/A</span>
            @endif
        </td>

        <!-- Status -->
        <td class="text-center">
            @if($isCurrentlyActive)
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-0.5" style="font-size: 10px;">
                    Blocked
                </span>
            @else
                <span class="badge bg-light text-secondary border px-2 py-0.5" style="font-size: 10px;">
                    Inactive
                </span>
            @endif
        </td>

        <!-- Actions -->
        <td class="pe-3 text-end text-nowrap">
            @if($isCurrentlyActive)
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 btn-unblock-network-row" data-id="{{ $network->id }}" data-cidr="{{ $network->cidr_range }}" style="font-size: 11px;" title="Unblock Network">
                    Unblock
                </button>
            @else
                <span class="text-muted small">—</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center py-5 text-muted">
            <i class="fas fa-sitemap fa-3x text-secondary mb-2 opacity-50"></i>
            <h6 class="fw-semibold mb-1">No Blocked Networks Found</h6>
            <p class="small text-muted mb-0">No networks match your filters.</p>
        </td>
    </tr>
@endforelse