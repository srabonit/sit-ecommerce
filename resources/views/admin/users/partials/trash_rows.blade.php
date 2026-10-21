@forelse($trashedUsers as $index => $u)
    <tr id="trashRow{{ $u->id }}" class="align-middle">
        <!-- Serial Index -->
        <td class="font-monospace text-muted fw-bold ps-3 py-2.5 bn-digit" style="font-size: 11.5px;">
            {{ $trashedUsers->firstItem() + $index }}
        </td>

        <!-- User Identity & Contact -->
        <td class="py-2.5 px-2 " style="min-width: 220px;">
            <div class="d-flex align-items-center gap-2.5 ">
                <div class="rounded-circle me-2 bg-danger-subtle border border-danger-subtle text-danger d-flex align-items-center justify-content-center fw-bold flex-shrink-0 shadow-xs" style="width: 34px; height: 34px; font-size: 12px;">
                    {{ strtoupper(substr($u->name ?? 'U', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <strong class="text-dark d-block text-truncate" style="font-size: 13px; max-width: 180px;">
                        {{ $u->name }}
                    </strong>
                    <small class="d-block text-muted font-monospace text-truncate mt-0.5" style="font-size: 11px; max-width: 210px;">
                        {{ $u->email }} 
                        <span class="mx-1">&bull;</span> 
                        <span class="bn-digit">{{ $u->phone ?: 'No Phone' }}</span>
                    </small>
                </div>
            </div>
        </td>

        <!-- Role & Branch Info -->
        <td class="py-2.5 px-2 text-nowrap">
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 text-capitalize font-monospace d-inline-flex align-items-center fw-semibold" style="font-size: 9.5px; border-radius: 5px !important;">
             
                <span>{{ ucwords(str_replace(['_', '-'], ' ', $u->role)) }}</span>
            </span>
            @if(!empty($u->branch_name))
                <small class="text-muted d-block mt-1 text-truncate" style="font-size: 11px; max-width: 150px;">
                {{ $u->branch_name }}
                </small>
            @endif
        </td>

        <!-- Deleted Timestamp -->
        <td class="text-muted font-monospace small py-2.5 px-2 text-nowrap">
            <div class="text-dark fw-semibold bn-digit" style="font-size: 11.5px;">
             {{ date('d M Y', strtotime($u->deleted_at)) }}
            </div>
            <small class="text-muted d-block mt-0.5 bn-digit" style="font-size: 10.5px;">
                {{ date('h:i A', strtotime($u->deleted_at)) }}
            </small>
        </td>

        <!-- Action Buttons (Strictly 5px Radius & Touch Friendly) -->
        <td class="text-end pe-3 py-2.5 text-nowrap">
            <div class="d-inline-flex align-items-center gap-1.5">
                <button type="button" class="btn btn-outline-success btn-sm px-2.5 py-1 fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5" style="border-radius: 5px !important; font-size: 11.5px;" onclick="executeRestoreAction({{ $u->id }})">
                    <i class="fas fa-rotate-left"></i>
                </button>
                <button type="button" class="btn ms-2 btn-outline-danger btn-sm px-2.5 py-1 fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5" style="border-radius: 5px !important; font-size: 11.5px;" onclick="openDeleteDialog({{ $u->id }}, '{{ addslashes($u->name) }}')">
                    <i class="fas fa-trash-can"></i> 
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr id="emptyRow">
        <td colspan="5" class="text-center py-5 text-muted small">
            <i class="fas fa-trash-can fa-2x mb-2 d-block opacity-40"></i>
            <span class="fw-semibold">Trash is completely empty.</span>
        </td>
    </tr>
@endforelse