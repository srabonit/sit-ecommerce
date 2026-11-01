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
    <tr id="row-blocked-ip-{{ $item->id }}" class="{{ $isLiveActive ? '' : 'table-light opacity-75' }}">
        <!-- IP Address -->
        <td class="ps-3 font-monospace fw-bold text-dark">
            <a href="{{ route('admin.security.ips.show', $item->ip_address) }}" class="text-decoration-none text-dark hover-primary">
                {{ $item->ip_address }}
            </a>
        </td>

        <!-- Category -->
        <td>
            <span class="badge {{ $badgeCategory }} px-1.5 py-0.5" style="font-size: 9.5px;">
                {{ str_replace('_', ' ', strtoupper($item->block_category ?? 'MANUAL')) }}
            </span>
        </td>

        <!-- Type -->
        <td>
            <span class="badge {{ $item->block_type === 'permanent' ? 'bg-danger' : 'bg-warning text-dark' }} px-1.5 py-0.5" style="font-size: 9.5px;">
                {{ ucfirst($item->block_type) }}
            </span>
        </td>

        <!-- Reason & Note -->
        <td>
            <div class="text-truncate fw-semibold text-dark" style="max-width: 260px;" title="{{ $item->block_reason }}">
                {{ $item->block_reason }}
            </div>
            @if(!empty($item->admin_note))
                <small class="text-muted text-truncate d-block" style="max-width: 260px; font-size: 10.5px;" title="{{ $item->admin_note }}">
                    <i class="fas fa-note-sticky me-1 text-secondary"></i> {{ $item->admin_note }}
                </small>
            @endif
        </td>

        <!-- Creator -->
        <td class="small text-muted">
            {{ $item->creator ? $item->creator->name : 'System' }}
            <span class="d-block text-secondary" style="font-size: 10px;">{{ $item->created_at ? $item->created_at->format('M d, H:i') : '' }}</span>
        </td>

        <!-- Expiry -->
        <td>
            @if($item->block_type === 'permanent')
                <span class="text-danger fw-semibold small">Never</span>
            @elseif($item->expires_at)
                @if($isExpired)
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-1.5 py-0.5" style="font-size: 9.5px;">Expired</span>
                @else
                    <span class="text-dark small fw-semibold">{{ $item->expires_at->diffForHumans() }}</span>
                @endif
            @else
                <span class="text-muted small">N/A</span>
            @endif
        </td>

        <!-- Status -->
        <td class="text-center">
            @if($isLiveActive)
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
            <div class="btn-group btn-group-sm">
                <!-- Inspector Link -->
                <a href="{{ route('admin.security.ips.show', $item->ip_address) }}" class="btn btn-outline-secondary py-0 px-2" style="font-size: 11px;" title="View IP Details">
                    <i class="fas fa-search"></i>
                </a>

                @if($isLiveActive)
                    <!-- Quick Unblock -->
                    <button type="button" class="btn btn-outline-danger py-0 px-2 btn-ajax-unblock" data-id="{{ $item->id }}" data-ip="{{ $item->ip_address }}" style="font-size: 11px;" title="Unblock IP">
                        Unblock
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center py-5 text-muted">
            <i class="fas fa-shield-halved fa-3x text-success mb-2 opacity-50"></i>
            <h6 class="fw-semibold mb-1">No Blocked IPs Found</h6>
            <p class="small text-muted mb-0">No IPs match your filters.</p>
        </td>
    </tr>
@endforelse