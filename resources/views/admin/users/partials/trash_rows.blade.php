
@forelse($trashedUsers as $index => $u)
    <tr id="trashRow{{ $u->id }}" class="align-middle trash-user-row">

        {{-- Serial Index --}}
        <td class="font-monospace text-muted fw-semibold ps-3 py-3 trash-serial-cell bn-digit">
            {{ $trashedUsers->firstItem() + $index }}
        </td>

        {{-- User Identity & Contact --}}
        <td class="py-3 px-3 trash-identity-cell">
            <div class="d-flex align-items-center gap-2">

                <div class="trash-user-avatar rounded-circle bg-danger-subtle border border-danger-subtle text-danger d-flex align-items-center justify-content-center fw-bold flex-shrink-0">
                    {{ strtoupper(substr($u->name ?? 'U', 0, 1)) }}
                </div>

                <div class="min-w-0">
                    <strong class="text-dark d-block text-truncate trash-user-name">
                        {{ $u->name }}
                    </strong>

                    <small class="d-block text-muted font-monospace text-truncate trash-user-contact">
                        <i class="far fa-envelope me-1"></i>
                        {{ $u->email }}

                        <span class="mx-1 text-secondary">&bull;</span>

                        <span class="bn-digit">
                            <i class="fas fa-phone me-1"></i>
                            {{ $u->phone ?: 'No Phone' }}
                        </span>
                    </small>
                </div>
            </div>
        </td>

        {{-- Role & Branch Info --}}
        <td class="py-3 px-3 trash-role-cell">

            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle trash-role-badge">
                <i class="fas fa-user-tag me-1"></i>
                <span>{{ ucwords(str_replace(['_', '-'], ' ', $u->role)) }}</span>
            </span>

            @if(!empty($u->branch_name))
                <small class="text-muted d-block mt-2 text-truncate trash-branch-name">
                    <i class="fas fa-code-branch me-1"></i>
                    {{ $u->branch_name }}
                </small>
            @endif
        </td>

        {{-- Deleted Timestamp --}}
        <td class="py-3 px-3 trash-deleted-cell">
            <div class="text-dark fw-semibold font-monospace trash-deleted-date bn-digit">
                <i class="far fa-calendar-xmark text-danger me-1"></i>
                {{ date('d M Y', strtotime($u->deleted_at)) }}
            </div>

            <small class="text-muted d-block font-monospace trash-deleted-time bn-digit">
                <i class="far fa-clock me-1"></i>
                {{ date('h:i A', strtotime($u->deleted_at)) }}
            </small>
        </td>

        {{-- Action Buttons --}}
        <td class="text-end pe-3 py-3 text-nowrap trash-action-cell">
            <div class="d-inline-flex align-items-center gap-2">

                {{-- Restore User --}}
                <button
                    type="button"
                    class="btn btn-outline-success btn-sm trash-action-btn trash-restore-btn"
                    title="Restore User"
                    aria-label="Restore User"
                    onclick="executeRestoreAction({{ $u->id }})"
                >
                    <i class="fas fa-rotate-left"></i>
                </button>

                {{-- Permanently Delete User --}}
                <button
                    type="button"
                    class="btn btn-outline-danger btn-sm trash-action-btn trash-delete-btn"
                    title="Permanently Delete"
                    aria-label="Permanently Delete"
                    onclick="openDeleteDialog({{ $u->id }}, '{{ addslashes($u->name) }}')"
                >
                    <i class="fas fa-trash-can"></i>
                </button>

            </div>
        </td>
    </tr>

@empty
    <tr id="emptyRow">
        <td colspan="5" class="text-center py-5 text-muted small">
            <div class="d-flex flex-column align-items-center justify-content-center">

                <div class="trash-empty-icon mb-3">
                    <i class="fas fa-trash-can"></i>
                </div>

                <span class="fw-semibold text-secondary">
                    Trash is completely empty.
                </span>

            </div>
        </td>
    </tr>
@endforelse

<style>
    /*
    |--------------------------------------------------------------------------
    | Trash Users Table - Kaiadmin Classic White
    |--------------------------------------------------------------------------
    | Scoped to this partial's unique classes.
    |--------------------------------------------------------------------------
    */

    .trash-user-row {
        transition: background-color .18s ease;
    }

    .trash-user-row:hover {
        background-color: #f8fafc;
    }

    .trash-user-row > td {
        border-bottom: 1px solid #eef0f3;
        vertical-align: middle;
    }

    /* Serial Number */
    .trash-serial-cell {
        font-size: 11.5px;
        white-space: nowrap;
    }

    /* User Avatar */
    .trash-user-avatar {
        width: 42px;
        height: 42px;
        min-width: 42px;
        font-size: 14px;
        border-radius: 50%;
    }

    /* User Identity */
    .trash-user-name {
        max-width: 190px;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.45;
    }

    .trash-user-contact {
        max-width: 235px;
        margin-top: 4px;
        font-size: 10.5px;
        line-height: 1.5;
    }

    /* Role */
    .trash-role-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 9px;
        font-size: 10px;
        font-weight: 600;
        line-height: 1.2;
        border-radius: 5px;
        white-space: nowrap;
    }

    .trash-branch-name {
        max-width: 160px;
        font-size: 11px;
        line-height: 1.4;
    }

    /* Deleted Date & Time */
    .trash-deleted-date {
        font-size: 11.5px;
        line-height: 1.5;
        white-space: nowrap;
    }

    .trash-deleted-time {
        margin-top: 5px;
        font-size: 10.5px;
        line-height: 1.5;
        white-space: nowrap;
    }

    /* Action Buttons */
    .trash-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 35px;
        height: 34px;
        padding: 0;
        font-size: 12px;
        border-radius: 6px !important;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease;
    }

    .trash-restore-btn:hover {
        color: #fff;
        background-color: #198754;
        border-color: #198754;
    }

    .trash-delete-btn:hover {
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
    }

    /* Empty State */
    .trash-empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        color: #9ba4b2;
        background: #f3f5f8;
        border: 1px solid #e9edf2;
        border-radius: 50%;
        font-size: 23px;
    }

    /* Responsive */
    @media (max-width: 767.98px) {
        .trash-user-row > td {
            padding-top: 11px !important;
            padding-bottom: 11px !important;
        }

        .trash-user-avatar {
            width: 36px;
            height: 36px;
            min-width: 36px;
            font-size: 12px;
        }

        .trash-user-name {
            max-width: 155px;
            font-size: 12px;
        }

        .trash-user-contact {
            max-width: 190px;
            font-size: 10px;
        }

        .trash-role-badge {
            font-size: 9.5px;
        }

        .trash-action-btn {
            width: 34px;
            height: 34px;
        }
    }

    @media (max-width: 575.98px) {
        .trash-empty-icon {
            width: 52px;
            height: 52px;
            font-size: 20px;
        }
    }
</style>
