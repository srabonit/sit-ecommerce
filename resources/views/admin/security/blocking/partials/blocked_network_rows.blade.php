
@forelse($blockedNetworks as $network)
    @php
        $carbonExpires = !empty($network->expires_at) ? \Carbon\Carbon::parse($network->expires_at) : null;
        $carbonCreated = !empty($network->created_at) ? \Carbon\Carbon::parse($network->created_at) : null;

        $isExpired = $carbonExpires && $carbonExpires->isPast();
        $isCurrentlyActive = $network->is_active && !$isExpired;
        $isPermanent = is_null($network->expires_at);
        $version = $network->ip_version ?? (str_contains($network->cidr_range, ':') ? 'v6' : 'v4');
    @endphp

    <tr id="blocked-network-row-{{ $network->id }}"
        class="{{ !$isCurrentlyActive ? 'table-light opacity-75' : '' }}">

        {{-- CIDR Subnet & IP Version --}}
        <td class="ps-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1 font-monospace"
                      style="font-size: 10px; font-weight: 600;">
                    <i class="fas fa-network-wired me-1"></i>
                    IP{{ strtoupper($version) }}
                </span>

                <span class="font-monospace fw-semibold text-dark"
                      style="font-size: 13px;">
                    {{ $network->cidr_range }}
                </span>
            </div>

            @if(!empty($network->admin_note))
                <small class="text-muted text-truncate d-block mt-1"
                       style="max-width: 250px; font-size: 10.5px;"
                       title="{{ $network->admin_note }}">
                    <i class="fas fa-sticky-note me-1"></i>
                    {{ $network->admin_note }}
                </small>
            @endif
        </td>

        {{-- IP Coverage --}}
        <td class="text-center">
            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 font-monospace"
                  style="font-size: 10.5px; font-weight: 600;">
                <i class="fas fa-layer-group me-1"></i>
                ~{{ number_format((int) ($network->estimated_ip_count ?? 1)) }} IPs
            </span>
        </td>

        {{-- Reason & Creator --}}
        <td style="max-width: 250px;">
            <div class="small text-truncate fw-semibold text-dark"
                 style="font-size: 12px;"
                 title="{{ $network->block_reason }}">
                {{ $network->block_reason }}
            </div>

            <small class="d-block text-muted mt-1" style="font-size: 10.5px;">
                <i class="fas fa-user-circle me-1"></i>
                {{ $network->creator ? $network->creator->name : 'System' }}
            </small>
        </td>

        {{-- Blocked At --}}
        <td>
            @if($carbonCreated)
                <div class="d-flex flex-column">
                    <span class="text-dark fw-semibold" style="font-size: 11px;">
                        <i class="far fa-clock me-1 text-muted"></i>
                        {{ $carbonCreated->diffForHumans() }}
                    </span>

                    <span class="text-muted mt-1" style="font-size: 10px;">
                        {{ $carbonCreated->format('M d, H:i') }}
                    </span>
                </div>
            @else
                <span class="text-muted small">N/A</span>
            @endif
        </td>

        {{-- Expiration --}}
        <td>
            @if($isPermanent)
                <span class="text-danger fw-semibold small">
                    <i class="fas fa-infinity me-1"></i>Never
                </span>
            @elseif($carbonExpires)
                @if($isExpired)
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1"
                          style="font-size: 10px;">
                        Expired
                    </span>
                @else
                    <span class="text-dark small fw-semibold">
                        <i class="far fa-calendar-alt me-1 text-muted"></i>
                        {{ $carbonExpires->diffForHumans() }}
                    </span>
                @endif
            @else
                <span class="text-muted small">N/A</span>
            @endif
        </td>

        {{-- Status --}}
        <td class="text-center">
            @if($isCurrentlyActive)
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1"
                      style="font-size: 10px; font-weight: 600;">
                    <i class="fas fa-ban me-1"></i>
                    Blocked
                </span>
            @else
                <span class="badge bg-light text-secondary border px-2 py-1"
                      style="font-size: 10px; font-weight: 600;">
                    <i class="fas fa-check-circle me-1"></i>
                    Inactive
                </span>
            @endif
        </td>

        {{-- Actions --}}
        <td class="pe-3 text-end text-nowrap">
            @if($isCurrentlyActive)
                <button type="button"
                        class="btn btn-sm btn-outline-danger px-2 py-1 btn-unblock-network-row"
                        data-id="{{ $network->id }}"
                        data-cidr="{{ $network->cidr_range }}"
                        style="font-size: 11px;"
                        title="Unblock Network">
                    <i class="fas fa-unlock-alt me-1"></i>
                    Unblock
                </button>
            @else
                <span class="text-muted small">—</span>
            @endif
        </td>
    </tr>

@empty
    <tr>
        <td colspan="7" class="text-center py-5">
            <div class="d-flex flex-column align-items-center justify-content-center">
                <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mb-3"
                     style="width: 64px; height: 64px;">
                    <i class="fas fa-sitemap fa-2x text-secondary"></i>
                </div>

                <h6 class="fw-semibold text-dark mb-1">
                    No Blocked Networks Found
                </h6>

                <p class="small text-muted mb-0">
                    No networks match your filters.
                </p>
            </div>
        </td>
    </tr>
@endforelse
