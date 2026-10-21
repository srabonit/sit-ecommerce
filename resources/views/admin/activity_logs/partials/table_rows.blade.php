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
        <!-- Serial Index -->
        <td class="font-monospace text-muted fw-bold py-2.5 px-2 bn-digit" style="font-size: 11.5px;">
            {{ $logs->firstItem() + $index }}
        </td>

        <!-- Staff / Performer -->
        <td class="py-2.5 px-2">
            <strong class="text-dark d-block mb-1" style="font-size: 13px; line-height: 1.25;">{{ $log->user_name ?: 'System' }}</strong>
            <span class="badge bg-light text-secondary border px-2 py-0.5 fw-semibold" style="font-size: 9.5px; border-radius: 5px !important;">
                {{ ucwords(str_replace('_', ' ', $log->role)) }}
            </span>
            @if(!empty($log->branch_name))
                <small class="text-muted d-block text-truncate mt-1" style="font-size: 10.5px; max-width: 145px;">
                    <i class="fas fa-building me-1.5 opacity-75"></i>{{ $log->branch_name }}
                </small>
            @endif
        </td>

        <!-- Action Badge -->
        <td class="py-2.5 px-2 text-nowrap">
            <span class="badge {{ $badgeClass }} px-2 py-1 font-monospace fw-bold" style="font-size: 10px; letter-spacing: 0.3px; border-radius: 5px !important;">
                {{ strtoupper($log->action) }}
            </span>
        </td>

        <!-- Module Badge -->
        <td class="py-2.5 px-2 text-nowrap">
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 fw-semibold" style="font-size: 10.5px; border-radius: 5px !important;">
                {{ ucfirst(str_replace('_', ' ', $log->module)) }}
            </span>
        </td>

        <!-- Record Reference -->
        <td class="py-2.5 px-2">
            @if($log->record_ref)
                <code class="text-primary font-monospace bg-light px-2 py-0.5 border small text-break d-inline-block bn-digit" style="font-size: 11px; border-radius: 5px !important;">{{ $log->record_ref }}</code>
            @elseif($log->record_id)
                <code class="text-muted font-monospace bg-light px-2 py-0.5 border small d-inline-block bn-digit" style="font-size: 11px; border-radius: 5px !important;">#{{ $log->record_id }}</code>
            @else
                <span class="text-muted small">—</span>
            @endif
        </td>

        <!-- Activity Description -->
        <td class="py-2.5 px-2" style="min-width: 230px;">
            <span class="text-dark" style="font-size: 12.5px; line-height: 1.4;">{{ $log->description }}</span>
            @if($log->result === 'failed')
                <span class="badge bg-danger text-white px-1.5 py-0.5 ms-1 fw-bold" style="font-size: 9px; border-radius: 5px !important;">FAILED</span>
            @endif
        </td>

        <!-- Device & IP -->
        <td class="py-2.5 px-2 text-nowrap">
            <small class="text-dark d-block font-monospace bn-digit" style="font-size: 11px;">
                <i class="fas fa-network-wired text-muted me-1.5 opacity-75"></i>{{ $log->ip_address ?: 'Unknown' }}
            </small>
            <small class="text-muted d-block mt-0.5" style="font-size: 10.5px;">
                <i class="fas {{ $log->device_type === 'Mobile' ? 'fa-mobile-screen' : 'fa-laptop' }} me-1.5 opacity-75"></i>{{ $log->device_type ?: 'Desktop' }}
            </small>
        </td>

        <!-- Timestamp -->
        <td class="py-2.5 px-2 text-nowrap">
            <small class="text-dark d-block font-monospace fw-semibold bn-digit" style="font-size: 11px;">
                {{ date('d M, Y', strtotime($log->created_at)) }}
            </small>
            <small class="text-muted d-block font-monospace bn-digit mt-0.5" style="font-size: 10.5px;">
                {{ date('h:i:s A', strtotime($log->created_at)) }}
            </small>
        </td>

        <!-- Action / Snapshot -->
        <td class="text-end text-nowrap py-2.5 px-2">
            <div class="d-inline-flex align-items-center gap-1.5">
                @if($log->old_values || $log->new_values)
                    <button type="button" class="btn btn-outline-primary btn-sm px-2.5 py-1 fw-semibold shadow-xs d-inline-flex align-items-center" 
                            style="border-radius: 5px !important; font-size: 11px; height: 28px;"
                            onclick="viewLogDetail({{ $log->id }})" title="View Diff Snapshot">
                        <i class="fas fa-eye fa-xs me-1.5"></i>
                        <span>Diff</span>
                    </button>
                @endif
                @if(auth()->user()->isSuperAdmin())
                    <button type="button" class="btn btn-outline-danger btn-sm p-0 d-inline-flex align-items-center justify-content-center shadow-xs" 
                            style="width: 28px; height: 28px; border-radius: 5px !important;"
                            onclick="deleteSingleLog({{ $log->id }})" title="Delete This Specific Log">
                        <i class="fas fa-trash-can" style="font-size: 11px;"></i>
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="text-center py-5 text-muted small">
            <i class="fas fa-clipboard-check fa-2x mb-2 d-block opacity-40"></i>
            <span class="fw-semibold">No activity log records found matching criteria.</span>
        </td>
    </tr>
@endforelse