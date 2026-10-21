@forelse($sessions as $index => $sess)
    @php
        $isThisDevice = ($sess->session_id === $currentSessionId);
        $lastAct = \Carbon\Carbon::parse($sess->last_activity);

        $resolveUserPhoto = function($path) {
            if (empty($path)) return null;
            if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
            $clean = ltrim(str_replace(['storage/', 'public/'], '', $path), '/');
            if (file_exists(public_path($clean))) return asset($clean);
            if (file_exists(public_path('uploads/' . $clean))) return asset('uploads/' . $clean);
            return asset($clean);
        };

        $userPhoto = $resolveUserPhoto($sess->user_photo ?? null);
    @endphp
    <tr id="session-row-{{ $sess->id }}" class="align-middle">
        <!-- Serial Index -->
        <td class="font-monospace text-muted fw-bold py-2.5 px-2 bn-digit" style="font-size: 11.5px;">
            {{ $sessions->firstItem() + $index }}
        </td>

        <!-- Staff Member Identity -->
        <td class="py-2.5 px-2" style="min-width: 200px;">
            <div class="d-flex align-items-center gap-2.5">
                <div class="rounded-circle me-2 bg-light border text-secondary d-flex align-items-center justify-content-center fw-bold overflow-hidden flex-shrink-0 shadow-xs" style="width: 36px; height: 36px; font-size: 12.5px;">
                    @if(!empty($userPhoto))
                        <img src="{{ $userPhoto }}" alt="{{ $sess->user_name }}" class="w-100 h-100 object-fit-cover" onerror="this.style.display='none'; this.parentElement.innerText='{{ strtoupper(substr($sess->user_name, 0, 1)) }}';">
                    @else
                        {{ strtoupper(substr($sess->user_name, 0, 1)) }}
                    @endif
                </div>
                <div class="min-w-0 pe-1">
                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                        <strong class="text-dark text-truncate" style="max-width: 145px; font-size: 13px;">{{ $sess->user_name }}</strong>
                        @if($isThisDevice)
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5 fw-semibold" style="font-size: 9px; border-radius: 5px !important;">Current Device</span>
                        @endif
                        @if(!empty($sess->is_super_admin) || $sess->user_role === 'super_admin')
                            <span class="badge bg-danger p-1 ms-1 rounded-circle" title="Super Admin"><i class="fas fa-crown text-white" style="font-size: 8px;"></i></span>
                        @endif
                    </div>
                    <small class="text-muted d-block font-monospace text-truncate mt-0.5" style="font-size: 11px; max-width: 175px;">{{ $sess->user_email }}</small>
                </div>
            </div>
        </td>

        <!-- Role & Hub -->
        <td class="py-2.5 px-2 text-nowrap">
            <span class="badge badge-soft-primary px-2 py-1 mb-0.5 d-inline-block fw-semibold" style="font-size: 10px; border-radius: 5px !important;">
                {{ ucwords(str_replace('_', ' ', $sess->user_role)) }}
            </span>
            @if(!empty($sess->branch_name))
                <small class="text-secondary d-block text-truncate mt-0.5" style="font-size: 10.5px; max-width: 135px;">
                    <i class="fas fa-building me-1 opacity-75"></i>{{ $sess->branch_name }}
                </small>
            @endif
        </td>

        <!-- Device & OS -->
        <td class="py-2.5 px-2 text-nowrap">
            <div class="d-flex align-items-center">
                <i class="fas {{ $sess->device_type === 'Mobile' ? 'fa-mobile-screen' : ($sess->device_type === 'Tablet' ? 'fa-tablet-screen-button' : 'fa-laptop') }} text-primary me-1" style="font-size: 12px;"></i>
                <strong class="text-dark small" style="font-size: 12.5px;">{{ $sess->platform ?: 'Unknown OS' }}</strong>
            </div>
            <small class="text-muted d-block mt-0.5" style="font-size: 10.5px;">Type: {{ $sess->device_type ?: 'Desktop' }}</small>
        </td>

        <!-- Browser -->
        <td class="py-2.5 px-2">
            <span class="text-dark fw-semibold small d-block" style="font-size: 12.5px;">{{ $sess->browser ?: 'Browser' }}</span>
            <small class="text-muted d-block font-monospace text-truncate mt-0.5" style="font-size: 10px; max-width: 160px;" title="{{ $sess->user_agent }}">
                {{ $sess->user_agent }}
            </small>
        </td>

        <!-- IP Address -->
        <td class="py-2.5 px-2 text-nowrap">
            <code class="font-monospace text-primary bg-light px-2 py-0.5 border small d-inline-block bn-digit" style="font-size: 11px; border-radius: 5px !important;">{{ $sess->ip_address }}</code>
        </td>

        <!-- Last Activity -->
        <td class="py-2.5 px-2 text-nowrap">
            <span class="text-dark small d-block font-monospace fw-semibold bn-digit" style="font-size: 11.5px;">{{ $lastAct->diffForHumans() }}</span>
            <small class="text-muted font-monospace d-block mt-0.5 bn-digit" style="font-size: 10.5px;">{{ $lastAct->format('h:i:s A') }}</small>
        </td>

        <!-- Session Actions -->
        <td class="text-end text-nowrap py-2.5 px-2">
            @if(!$isThisDevice)
                <div class="dropdown d-inline-block">
                    <button class="btn btn-outline-danger btn-sm px-2.5 py-1 dropdown-toggle fw-semibold shadow-xs" 
                            type="button" 
                            data-bs-toggle="dropdown" 
                            data-bs-boundary="viewport"
                            data-bs-display="dynamic"
                            aria-expanded="false" 
                            style="font-size: 11.5px; border-radius: 5px !important;">
                        Revoke
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border small p-1" style="min-width: 195px; z-index: 1060; border-radius: 6px !important;">
                        <li>
                            <button type="button" class="dropdown-item py-1.5 px-2.5 text-danger d-flex align-items-center fw-semibold" style="font-size: 12px; border-radius: 4px;" onclick="revokeSingleSession({{ $sess->id }}, '{{ addslashes($sess->user_name) }}')">
                                <i class="fas fa-right-from-bracket me-2" style="width: 14px; text-align: center;"></i>
                                <span>Terminate This Device</span>
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item py-1.5 px-2.5 text-danger d-flex align-items-center fw-semibold" style="font-size: 12px; border-radius: 4px;" onclick="revokeAllUserSessions({{ $sess->user_id }}, '{{ addslashes($sess->user_name) }}')">
                                <i class="fas fa-ban me-2" style="width: 14px; text-align: center;"></i>
                                <span>Force Logout All Devices</span>
                            </button>
                        </li>
                    </ul>
                </div>
            @else
                <span class="badge bg-light text-muted border px-2.5 py-1 fw-semibold" style="font-size: 10.5px; border-radius: 5px !important;">Active Now</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center py-5 text-muted small">
            <i class="fas fa-tower-broadcast fa-2x mb-2 d-block opacity-40"></i>
            <span class="fw-semibold">No active staff sessions detected matching criteria.</span>
        </td>
    </tr>
@endforelse