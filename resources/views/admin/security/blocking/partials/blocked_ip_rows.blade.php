
@forelse($blockedIps as $item)
    @php
        $isExpired = $item->expires_at && $item->expires_at->isPast();
        $isLiveActive = $item->is_active && !$isExpired;

        $badgeCategory = match($item->block_category ?? 'manual') {
            'multiple_orders' => 'bg-danger text-white',
            'brute_force'     => 'bg-danger bg-opacity-75 text-white',
            'url_attack'      => 'bg-warning text-dark',
            'spam_form'       => 'bg-info text-dark',
            default           => 'bg-secondary bg-opacity-10 text-secondary border',
        };
    @endphp

    <tr id="row-blocked-ip-{{ $item->id }}"
        class="{{ $isLiveActive ? '' : 'table-light opacity-75' }}">

        {{-- IP Address --}}
        <td class="ps-3">
            <a href="{{ route('admin.security.ips.show', $item->ip_address) }}"
               class="text-decoration-none text-dark fw-semibold font-monospace hover-primary">
                <i class="fas fa-globe me-1 text-muted small"></i>
                {{ $item->ip_address }}
            </a>
        </td>

        {{-- Category --}}
        <td>
            <span class="badge {{ $badgeCategory }} px-2 py-1"
                  style="font-size: 10px; font-weight: 600; letter-spacing: .2px;">
                {{ str_replace('_', ' ', strtoupper($item->block_category ?? 'MANUAL')) }}
            </span>
        </td>

        {{-- Type --}}
        <td>
            <span class="badge {{ $item->block_type === 'permanent' ? 'bg-danger' : 'bg-warning text-dark' }} px-2 py-1"
                  style="font-size: 10px; font-weight: 600;">
                <i class="fas {{ $item->block_type === 'permanent' ? 'fa-infinity' : 'fa-clock' }} me-1"></i>
                {{ ucfirst($item->block_type) }}
            </span>
        </td>

        {{-- Reason & Note --}}
        <td>
            <div class="fw-semibold text-dark text-truncate"
                 style="max-width: 260px; font-size: 12px;"
                 title="{{ $item->block_reason }}">
                {{ $item->block_reason }}
            </div>

            @if(!empty($item->admin_note))
                <small class="d-block text-muted text-truncate mt-1"
                       style="max-width: 260px; font-size: 10.5px;"
                       title="{{ $item->admin_note }}">
                    <i class="fas fa-sticky-note me-1"></i>
                    {{ $item->admin_note }}
                </small>
            @endif
        </td>

        {{-- Creator --}}
        <td>
            <div class="small fw-semibold text-dark">
                <i class="fas fa-user-circle me-1 text-muted"></i>
                {{ $item->creator ? $item->creator->name : 'System' }}
            </div>
            <span class="d-block text-muted mt-1" style="font-size: 10px;">
                <i class="far fa-calendar-alt me-1"></i>
                {{ $item->created_at ? $item->created_at->format('M d, H:i') : '' }}
            </span>
        </td>

        {{-- Expiry --}}
        <td>
            @if($item->block_type === 'permanent')
                <span class="text-danger fw-semibold small">
                    <i class="fas fa-infinity me-1"></i>Never
                </span>
            @elseif($item->expires_at)
                @if($isExpired)
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1"
                          style="font-size: 10px;">
                        Expired
                    </span>
                @else
                    <span class="text-dark small fw-semibold">
                        <i class="far fa-clock me-1 text-muted"></i>
                        {{ $item->expires_at->diffForHumans() }}
                    </span>
                @endif
            @else
                <span class="text-muted small">N/A</span>
            @endif
        </td>

        {{-- Status --}}
        <td class="text-center">
            @if($isLiveActive)
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1"
                      style="font-size: 10px; font-weight: 600;">
                    <i class="fas fa-ban me-1"></i>Blocked
                </span>
            @else
                <span class="badge bg-light text-secondary border px-2 py-1"
                      style="font-size: 10px; font-weight: 600;">
                    <i class="fas fa-check-circle me-1"></i>Inactive
                </span>
            @endif
        </td>

        {{-- Actions --}}
        <td class="pe-3 text-end text-nowrap">
            <div class="btn-group btn-group-sm" role="group">

                {{-- Inspector Link --}}
                <a href="{{ route('admin.security.ips.show', $item->ip_address) }}"
                   class="btn btn-outline-secondary px-2 py-1"
                   style="font-size: 11px;"
                   title="View IP Details">
                    <i class="fas fa-search"></i>
                </a>

                @if($isLiveActive)
                    {{-- Quick Unblock --}}
                    <button type="button"
                            class="btn btn-outline-danger px-2 py-1 btn-ajax-unblock"
                            data-id="{{ $item->id }}"
                            data-ip="{{ $item->ip_address }}"
                            style="font-size: 11px;"
                            title="Unblock IP">
                        <i class="fas fa-unlock-alt me-1"></i>Unblock
                    </button>
                @endif

            </div>
        </td>
    </tr>

@empty
    <tr>
        <td colspan="8" class="text-center py-5">
            <div class="d-flex flex-column align-items-center justify-content-center">
                <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center mb-3"
                     style="width: 64px; height: 64px;">
                    <i class="fas fa-shield-alt fa-2x text-success"></i>
                </div>

                <h6 class="fw-semibold text-dark mb-1">No Blocked IPs Found</h6>
                <p class="small text-muted mb-0">No IPs match your filters.</p>
            </div>
        </td>
    </tr>
@endforelse
