
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
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-circle p-1 status-dot {{ $isUnread ? 'bg-danger' : 'bg-secondary opacity-25' }}"
                      style="width: 8px; height: 8px;"></span>

                <span class="badge {{ $severityBadge }} px-2 py-1 text-uppercase"
                      style="font-size: 10px; letter-spacing: .3px;">
                    {{ $alert->risk_level }}
                </span>
            </div>
        </td>

        {{-- Incident --}}
        <td style="max-width: 320px;">
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                <span class="text-dark {{ $isUnread ? 'fw-bold' : 'fw-semibold' }}"
                      style="font-size: 12px; line-height: 1.5;">
                    {{ $alert->title }}
                </span>

                @if($alert->attempt_count > 1)
                    <span class="badge bg-light text-dark border px-2 py-0 font-monospace"
                          style="font-size: 10px;"
                          title="Total Attempts">
                        &times;{{ $alert->attempt_count }}
                    </span>
                @endif
            </div>

            <p class="mb-0 small text-secondary text-truncate"
               style="font-size: 11px; max-width: 300px;"
               title="{{ $alert->message }}">
                {{ $alert->message }}
            </p>
        </td>

        {{-- Category --}}
        <td>
            <span class="badge {{ $eventCategoryBadge }} px-2 py-1 text-uppercase font-monospace"
                  style="font-size: 9.5px; letter-spacing: .2px;">
                {{ str_replace('_', ' ', $alert->event_type) }}
            </span>
        </td>

        {{-- Source IP --}}
        <td>
            @if(!empty($alert->ip_address))
                <a href="{{ route('admin.security.ips.show', $alert->ip_address) }}"
                   class="font-monospace fw-semibold text-dark text-decoration-none hover-primary"
                   style="font-size: 11.5px;">
                    {{ $alert->ip_address }}
                </a>
            @else
                <span class="text-muted small">System</span>
            @endif
        </td>

        {{-- User --}}
        <td>
            @if($alert->user)
                <div class="d-flex align-items-center gap-2 text-dark"
                     style="font-size: 11px;">

                    <span class="badge bg-light text-secondary border px-2 py-1 font-monospace"
                          style="font-size: 10px;">
                        #{{ $alert->user_id }}
                    </span>

                    <span class="text-truncate fw-medium"
                          style="max-width: 110px;"
                          title="{{ $alert->user->name }}">
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
                <div class="d-flex flex-column gap-1 text-muted"
                     style="font-size: 11px;">

                    <span class="text-dark fw-semibold">
                        {{ $carbonCreated->diffForHumans() }}
                    </span>

                    <span class="text-secondary"
                          style="font-size: 10px;">
                        <i class="far fa-clock me-1"></i>{{ $carbonCreated->format('M d, H:i') }}
                    </span>
                </div>
            @else
                <span class="text-muted small">N/A</span>
            @endif
        </td>

        {{-- Actions --}}
        <td class="pe-3 text-end text-nowrap" style="width: 120px;">
            <div class="btn-group btn-group-sm" role="group">

                @if($isUnread)
                    <button
                        type="button"
                        class="btn btn-outline-secondary py-1 px-2 btn-mark-alert-read"
                        data-id="{{ $alert->id }}"
                        style="font-size: 11px; min-width: 30px;"
                        title="Mark as Read"
                        aria-label="Mark as Read">
                        <i class="far fa-check-circle"></i>
                    </button>
                @endif

                @if(!empty($alert->ip_address))
                    <a
                        href="{{ route('admin.security.ips.show', $alert->ip_address) }}"
                        class="btn btn-outline-secondary py-1 px-2"
                        style="font-size: 11px; min-width: 30px;"
                        title="Inspect IP"
                        aria-label="Inspect IP">
                        <i class="fas fa-search"></i>
                    </a>
                @endif

                <button
                    type="button"
                    class="btn btn-outline-danger py-1 px-2 btn-delete-alert-row"
                    data-id="{{ $alert->id }}"
                    style="font-size: 11px; min-width: 30px;"
                    title="Dismiss Alert"
                    aria-label="Dismiss Alert">
                    <i class="fas fa-trash-can"></i>
                </button>

            </div>
        </td>
    </tr>

@empty
    <tr>
        <td colspan="7" class="text-center py-5 text-muted">

            <div class="d-flex flex-column align-items-center justify-content-center py-2">

                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mb-3"
                     style="width: 62px; height: 62px;">
                    <i class="fas fa-bell-slash text-secondary opacity-50"
                       style="font-size: 25px;"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1">
                    No Alerts Found
                </h6>

                <p class="small text-muted mb-0">
                    No incidents match your filters.
                </p>

            </div>

        </td>
    </tr>
@endforelse