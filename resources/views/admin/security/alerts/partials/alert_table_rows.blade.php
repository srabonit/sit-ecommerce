@forelse($alerts as $alert)
    @php
        $isUnread = !$alert->is_read;
        $carbonCreated = !empty($alert->created_at) ? \Carbon\Carbon::parse($alert->created_at) : null;

        $severityBadge = match($alert->risk_level) {
            'critical' => 'bg-danger text-white',
            'high'     => 'bg-danger bg-opacity-75 text-white',
            'medium'   => 'bg-warning text-dark',
            'low'      => 'bg-info text-dark',
            default    => 'bg-secondary text-white',
        };

        $eventCategoryBadge = match($alert->event_type) {
            'brute_force'     => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
            'multiple_orders' => 'bg-warning bg-opacity-15 text-warning-emphasis border border-warning border-opacity-50',
            'spam_form'       => 'bg-info bg-opacity-10 text-info border border-info border-opacity-25',
            'url_attack'      => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
            default           => 'bg-secondary bg-opacity-10 text-secondary border',
        };
    @endphp

    <tr id="alert-row-{{ $alert->id }}" class="{{ $isUnread ? 'table-warning bg-opacity-10 fw-semibold' : 'text-muted' }}">

        {{-- Severity --}}
        <td class="ps-3 text-nowrap" style="width: 130px;">
            <div class="d-flex align-items-center gap-1.5">
                <span class="badge rounded-circle p-1 status-dot {{ $isUnread ? 'bg-danger' : 'bg-secondary opacity-25' }}" style="width: 8px; height: 8px;"></span>

                <span class="badge {{ $severityBadge }} px-2 py-0.5 text-uppercase" style="font-size: 9.5px;">
                    {{ $alert->risk_level }}
                </span>
            </div>
        </td>

        {{-- Incident --}}
        <td style="max-width: 320px;">
            <div class="d-flex align-items-center gap-1.5 mb-0.5">
                <span class="text-dark {{ $isUnread ? 'fw-bold' : 'fw-semibold' }}" style="font-size: 12.5px;">
                    {{ $alert->title }}
                </span>

                @if($alert->attempt_count > 1)
                    <span class="badge bg-light text-dark border px-1.5 py-0 font-monospace" style="font-size: 9px;" title="Total Attempts">
                        &times;{{ $alert->attempt_count }}
                    </span>
                @endif
            </div>

            <p class="mb-0 small text-secondary text-truncate" style="font-size: 11.5px;" title="{{ $alert->message }}">
                {{ $alert->message }}
            </p>
        </td>

        {{-- Category --}}
        <td>
            <span class="badge {{ $eventCategoryBadge }} px-2 py-0.5 text-uppercase font-monospace" style="font-size: 9.5px;">
                {{ str_replace('_', ' ', $alert->event_type) }}
            </span>
        </td>

        {{-- Source IP --}}
        <td>
            @if(!empty($alert->ip_address))
                <a href="{{ route('admin.security.ips.show', $alert->ip_address) }}" class="font-monospace fw-bold text-dark text-decoration-none hover-primary" style="font-size: 12px;">
                    {{ $alert->ip_address }}
                </a>
            @else
                <span class="text-muted small">System</span>
            @endif
        </td>

        {{-- User --}}
        <td>
            @if($alert->user)
                <div class="d-flex align-items-center gap-1 text-dark" style="font-size: 11px;">
                    <span class="badge bg-light text-dark border px-1 py-0 font-monospace">
                        #{{ $alert->user_id }}
                    </span>

                    <span class="text-truncate" style="max-width: 110px;" title="{{ $alert->user->name }}">
                        {{ $alert->user->name }}
                    </span>
                </div>
            @else
                <span class="text-muted small">—</span>
            @endif
        </td>

        {{-- Date & Time --}}
        <td>
            @if($carbonCreated)
                <div class="d-flex flex-column text-muted" style="font-size: 11px;">
                    <span class="text-dark fw-semibold">
                        {{ $carbonCreated->diffForHumans() }}
                    </span>

                    <span class="text-secondary" style="font-size: 10px;">
                        {{ $carbonCreated->format('M d, H:i') }}
                    </span>
                </div>
            @else
                <span class="text-muted small">N/A</span>
            @endif
        </td>

        {{-- Actions --}}
        <td class="pe-3 text-end text-nowrap" style="width: 120px;">
            <div class="btn-group btn-group-sm">

                @if($isUnread)
                    <button
                        type="button"
                        class="btn btn-outline-secondary py-0 px-2 btn-mark-alert-read"
                        data-id="{{ $alert->id }}"
                        style="font-size: 11px;"
                        title="Mark as Read">
                        <i class="far fa-check-circle"></i>
                    </button>
                @endif

                @if(!empty($alert->ip_address))
                    <a
                        href="{{ route('admin.security.ips.show', $alert->ip_address) }}"
                        class="btn btn-outline-secondary py-0 px-2"
                        style="font-size: 11px;"
                        title="Inspect IP">
                        <i class="fas fa-search"></i>
                    </a>
                @endif

                <button
                    type="button"
                    class="btn btn-outline-danger py-0 px-2 btn-delete-alert-row"
                    data-id="{{ $alert->id }}"
                    style="font-size: 11px;"
                    title="Dismiss Alert">
                    <i class="fas fa-trash-can"></i>
                </button>

            </div>
        </td>
    </tr>

@empty
    <tr>
        <td colspan="7" class="text-center py-5 text-muted">
            <i class="fas fa-bell-slash fa-3x text-secondary mb-2 opacity-50"></i>

            <h6 class="fw-semibold mb-1">No Alerts Found</h6>

            <p class="small text-muted mb-0">
                No security incidents match your filters.
            </p>
        </td>
    </tr>
@endforelse