
@forelse($logs as $index => $log)
    @php
        $actionLower = strtolower($log->action);
        $badgeClass = 'badge-soft-misc';

        if (str_contains($actionLower, 'create')) $badgeClass = 'badge-soft-create';
        elseif (str_contains($actionLower, 'update') || str_contains($actionLower, 'edit')) $badgeClass = 'badge-soft-update';
        elseif (str_contains($actionLower, 'delete') || str_contains($actionLower, 'destroy') || str_contains($actionLower, 'purge')) $badgeClass = 'badge-soft-delete';
        elseif (str_contains($actionLower, 'status')) $badgeClass = 'badge-soft-status';
        elseif (str_contains($actionLower, 'login') || str_contains($actionLower, 'logout')) $badgeClass = 'badge-soft-auth';
    @endphp

    <tr id="log-row-{{ $log->id }}" class="align-middle">

        {{-- Serial --}}
        <td class="font-monospace text-muted fw-semibold py-2 px-2 bn-digit" style="font-size: 11px;">
            {{ $logs->firstItem() + $index }}
        </td>

        {{-- Staff --}}
        <td class="py-2 px-2">
            <div class="d-flex flex-column gap-1">
                <strong class="text-dark fw-semibold" style="font-size: 12.5px; line-height: 1.3;">
                    {{ $log->user_name ?: 'System' }}
                </strong>

                <div>
                    <span class="badge bg-light text-secondary border px-2 py-1 fw-semibold" style="font-size: 9.5px;">
                        {{ ucwords(str_replace('_', ' ', $log->role)) }}
                    </span>
                </div>

                @if(!empty($log->branch_name))
                    <small class="text-muted text-truncate" style="font-size: 10.5px; max-width: 145px;">
                        <i class="fas fa-building me-1 opacity-75"></i>{{ $log->branch_name }}
                    </small>
                @endif
            </div>
        </td>

        {{-- Action --}}
        <td class="py-2 px-2 text-nowrap">
            <span class="badge {{ $badgeClass }} px-2 py-1 font-monospace fw-bold" style="font-size: 10px; letter-spacing: 0.2px;">
                {{ strtoupper($log->action) }}
            </span>
        </td>

        {{-- Module --}}
        <td class="py-2 px-2">
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 fw-semibold" style="font-size: 10px;">
                {{ ucfirst(str_replace('_', ' ', $log->module)) }}
            </span>
        </td>

        {{-- Reference --}}
        <td class="py-2 px-2">
            @if($log->record_ref)
                <code class="text-primary bg-light border px-2 py-1 small text-break d-inline-block bn-digit" style="font-size: 10.5px;">
                    {{ $log->record_ref }}
                </code>
            @elseif($log->record_id)
                <code class="text-muted bg-light border px-2 py-1 small d-inline-block bn-digit" style="font-size: 10.5px;">
                    #{{ $log->record_id }}
                </code>
            @else
                <span class="text-muted small">—</span>
            @endif
        </td>

        {{-- Description --}}
        <td class="py-2 px-2" style="min-width: 230px; max-width: 360px;">
            <div class="d-flex flex-wrap align-items-center gap-1">
                <span class="text-dark" style="font-size: 12px; line-height: 1.5; overflow-wrap: anywhere;">
                    {{ $log->description }}
                </span>

                @if($log->result === 'failed')
                    <span class="badge bg-danger text-white px-2 py-1 fw-bold" style="font-size: 9px;">
                        FAILED
                    </span>
                @endif
            </div>
        </td>

        {{-- Device & IP --}}
        <td class="py-2 px-2">
            <div class="d-flex flex-column gap-1">
                <small class="text-dark font-monospace bn-digit text-nowrap" style="font-size: 10.5px;">
                    <i class="fas fa-network-wired text-muted me-1"></i>{{ $log->ip_address ?: 'Unknown' }}
                </small>

                <small class="text-muted text-nowrap" style="font-size: 10.5px;">
                    <i class="fas {{ $log->device_type === 'Mobile' ? 'fa-mobile-screen' : 'fa-laptop' }} me-1"></i>{{ $log->device_type ?: 'Desktop' }}
                </small>
            </div>
        </td>

        {{-- Timestamp --}}
        <td class="py-2 px-2 text-nowrap">
            <div class="d-flex flex-column gap-1">
                <small class="text-dark fw-semibold font-monospace bn-digit" style="font-size: 10.5px;">
                    {{ date('d M, Y', strtotime($log->created_at)) }}
                </small>

                <small class="text-muted font-monospace bn-digit" style="font-size: 10px;">
                    {{ date('h:i:s A', strtotime($log->created_at)) }}
                </small>
            </div>
        </td>

        {{-- Actions --}}
        <td class="text-end text-nowrap py-2 px-2">
            <div class="d-inline-flex align-items-center gap-1">

                @if($log->old_values || $log->new_values)
                    <button type="button"
                            class="btn btn-outline-primary btn-sm px-2 py-1 fw-semibold d-inline-flex align-items-center justify-content-center"
                            style="font-size: 11px; height: 28px;"
                            onclick="viewLogDetail({{ $log->id }})"
                            title="View Snapshot">

                        <i class="fas fa-eye me-1"></i>
                        <span>Diff</span>
                    </button>
                @endif

                @if(auth()->user()->isSuperAdmin())
                    <button type="button"
                            class="btn btn-outline-danger btn-sm p-0 d-inline-flex align-items-center justify-content-center"
                            style="width: 28px; height: 28px;"
                            onclick="deleteSingleLog({{ $log->id }})"
                            title="Delete Log">

                        <i class="fas fa-trash-can" style="font-size: 11px;"></i>
                    </button>
                @endif

            </div>
        </td>

    </tr>

@empty

    <tr>
        <td colspan="9" class="text-center py-5">
            <div class="d-flex flex-column align-items-center justify-content-center">
                <div class="mb-2 text-muted opacity-50">
                    <i class="fas fa-clipboard-check fa-2x"></i>
                </div>

                <span class="text-dark fw-semibold" style="font-size: 13px;">
                    No activity logs found
                </span>

                <small class="text-muted mt-1" style="font-size: 11px;">
                    Try changing your filters.
                </small>
            </div>
        </td>
    </tr>

@endforelse
