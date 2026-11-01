@forelse($restrictions as $item)
    @php
        $user = $item->user;
        $carbonExpires = !empty($item->expires_at) ? \Carbon\Carbon::parse($item->expires_at) : null;
        $carbonCreated = !empty($item->created_at) ? \Carbon\Carbon::parse($item->created_at) : null;

        $isExpired = $carbonExpires && $carbonExpires->isPast();
        $isCurrentlyActive = $item->is_active && !$isExpired;

        $typeBadge = match($item->restriction_type) {
            'permanent_suspension' => 'bg-danger text-white',
            'temporary_lock'       => 'bg-danger bg-opacity-75 text-white',
            'checkout_restricted'  => 'bg-warning text-dark',
            'login_restricted'     => 'bg-dark text-white',
            'form_restricted'      => 'bg-info text-dark',
            'warning'              => 'bg-secondary text-white',
            default                => 'bg-secondary text-white',
        };
    @endphp
    <tr id="user-restriction-row-{{ $item->id }}" class="{{ !$isCurrentlyActive ? 'table-light opacity-75' : '' }}">
        <!-- User Account -->
        <td class="ps-3">
            @if($user)
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 30px; height: 30px; font-size: 11px;">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-bold text-dark">
                            {{ $user->name }}
                            <span class="badge bg-light text-secondary border font-monospace ms-1" style="font-size: 9px;">#{{ $user->id }}</span>
                        </div>
                        <div class="small text-muted font-monospace" style="font-size: 10.5px;">
                            {{ $user->email ?? $user->phone ?? 'No contact info' }}
                        </div>
                    </div>
                </div>
            @else
                <span class="text-muted small">Deleted User (ID: #{{ $item->user_id }})</span>
            @endif
        </td>

        <!-- Restriction Type -->
        <td>
            <span class="badge {{ $typeBadge }} px-1.5 py-0.5" style="font-size: 9.5px;">
                {{ str_replace('_', ' ', strtoupper($item->restriction_type)) }}
            </span>
        </td>

        <!-- Reason & Notes -->
        <td style="max-width: 250px;">
            <div class="small text-truncate fw-semibold text-dark" style="font-size: 12px;" title="{{ $item->reason }}">
                {{ $item->reason }}
            </div>
            @if(!empty($item->admin_note))
                <small class="text-muted text-truncate d-block font-monospace mt-0.5" style="font-size: 10px;" title="{{ $item->admin_note }}">
                    <i class="fas fa-note-sticky me-1 text-secondary"></i> {{ $item->admin_note }}
                </small>
            @endif
            <small class="text-secondary" style="font-size: 10px;">
                {{ $item->creator ? $item->creator->name : 'System' }}
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
            @if(is_null($item->expires_at))
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
                    Restricted
                </span>
            @else
                <span class="badge bg-light text-secondary border px-2 py-0.5" style="font-size: 10px;">
                    Lifted
                </span>
            @endif
        </td>

        <!-- Actions -->
        <td class="pe-3 text-end text-nowrap">
            @if($isCurrentlyActive)
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 btn-lift-user-restriction" data-id="{{ $item->id }}" data-name="{{ $user?->name ?? 'User #' . $item->user_id }}" style="font-size: 11px;" title="Lift Restriction">
                    Lift
                </button>
            @else
                <span class="text-muted small">—</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center py-5 text-muted">
            <i class="fas fa-user-shield fa-3x text-secondary mb-2 opacity-50"></i>
            <h6 class="fw-semibold mb-1">No Restricted Accounts Found</h6>
            <p class="small text-muted mb-0">No accounts match your filters.</p>
        </td>
    </tr>
@endforelse