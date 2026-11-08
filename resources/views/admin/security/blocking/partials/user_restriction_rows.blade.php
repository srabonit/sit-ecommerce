
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

    <tr id="user-restriction-row-{{ $item->id }}"
        class="{{ !$isCurrentlyActive ? 'table-light opacity-75' : '' }}">

        {{-- User Account --}}
        <td class="ps-3">
            @if($user)
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                         style="width: 36px; height: 36px; font-size: 12px;">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    </div>

                    <div class="min-w-0">
                        <div class="fw-semibold text-dark text-nowrap" style="font-size: 12px;">
                            {{ $user->name }}

                            <span class="badge bg-light text-secondary border font-monospace ms-1"
                                  style="font-size: 9px;">
                                #{{ $user->id }}
                            </span>
                        </div>

                        <div class="small text-muted font-monospace text-truncate mt-1"
                             style="font-size: 10.5px; max-width: 190px;">
                            <i class="fas fa-envelope me-1"></i>
                            {{ $user->email ?? $user->phone ?? 'No contact info' }}
                        </div>
                    </div>
                </div>
            @else
                <span class="text-muted small">
                    <i class="fas fa-user-slash me-1"></i>
                    Deleted User (ID: #{{ $item->user_id }})
                </span>
            @endif
        </td>

        {{-- Restriction Type --}}
        <td>
            <span class="badge {{ $typeBadge }} px-2 py-1"
                  style="font-size: 10px; font-weight: 600; letter-spacing: .2px;">
                {{ str_replace('_', ' ', strtoupper($item->restriction_type)) }}
            </span>
        </td>

        {{-- Reason & Notes --}}
        <td style="max-width: 250px;">
            <div class="small text-truncate fw-semibold text-dark"
                 style="font-size: 12px;"
                 title="{{ $item->reason }}">
                {{ $item->reason }}
            </div>

            @if(!empty($item->admin_note))
                <small class="text-muted text-truncate d-block mt-1"
                       style="font-size: 10.5px;"
                       title="{{ $item->admin_note }}">
                    <i class="fas fa-sticky-note me-1"></i>
                    {{ $item->admin_note }}
                </small>
            @endif

            <small class="d-block text-muted mt-1" style="font-size: 10px;">
                <i class="fas fa-user-circle me-1"></i>
                {{ $item->creator ? $item->creator->name : 'System' }}
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
            @if(is_null($item->expires_at))
                <span class="text-danger fw-semibold small">
                    <i class="fas fa-infinity me-1"></i>
                    Never
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
                    <i class="fas fa-user-lock me-1"></i>
                    Restricted
                </span>
            @else
                <span class="badge bg-light text-secondary border px-2 py-1"
                      style="font-size: 10px; font-weight: 600;">
                    <i class="fas fa-check-circle me-1"></i>
                    Lifted
                </span>
            @endif
        </td>

        {{-- Actions --}}
        <td class="pe-3 text-end text-nowrap">
            @if($isCurrentlyActive)
                <button type="button"
                        class="btn btn-sm btn-outline-danger px-2 py-1 btn-lift-user-restriction"
                        data-id="{{ $item->id }}"
                        data-name="{{ $user?->name ?? 'User #' . $item->user_id }}"
                        style="font-size: 11px;"
                        title="Lift Restriction">
                    <i class="fas fa-unlock-alt me-1"></i>
                    Lift
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
                    <i class="fas fa-user-shield fa-2x text-secondary"></i>
                </div>

                <h6 class="fw-semibold text-dark mb-1">
                    No Restricted Accounts Found
                </h6>

                <p class="small text-muted mb-0">
                    No accounts match your filters.
                </p>
            </div>
        </td>
    </tr>
@endforelse
