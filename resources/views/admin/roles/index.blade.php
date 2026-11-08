
@extends('layouts.admin')

@section('title', 'Role Management - ' . config('app.name'))

@section('admin_content')
<style>
    /* =========================================
       ROLE MANAGEMENT - KAIADMIN WHITE CLASSIC
    ========================================= */

    #roleIndexRoot {
        --role-primary: var(--bs-primary, #1572e8);
        --role-bg: #f5f7fd;
        --role-card: #ffffff;
        --role-border: #ebecec;
        --role-heading: #2a2f5b;
        --role-text: #495057;
        --role-muted: #8d9498;
        --role-radius: 7px;
        color: var(--role-text);
        width: 100%;
        min-width: 0;
    }

    #roleIndexRoot *,
    #roleIndexRoot *::before,
    #roleIndexRoot *::after {
        box-sizing: border-box;
    }

    /* Page Layout */
    #roleIndexRoot .role-page-container {
        padding: 18px 20px;
        max-width: 100%;
    }

    #roleIndexRoot .role-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 18px;
    }

    #roleIndexRoot .role-page-title {
        display: flex;
        align-items: center;
        gap: 9px;
        color: var(--role-heading);
        font-size: 19px;
        font-weight: 600;
        line-height: 1.4;
        margin: 0;
    }

    #roleIndexRoot .role-page-title i {
        color: var(--role-primary);
        font-size: 18px;
    }

    #roleIndexRoot .role-page-subtitle {
        display: block;
        margin-top: 4px;
        color: var(--role-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    /* Header Actions */
    #roleIndexRoot .role-header-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    /* Buttons */
    #roleIndexRoot .btn,
    #roleIndexRoot button,
    #roleIndexRoot .badge,
    #roleIndexRoot code,
    #roleIndexRoot .alert {
        border-radius: 5px !important;
    }

    #roleIndexRoot .role-header-actions .btn {
        min-height: 34px;
        padding: 6px 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
        box-shadow: none;
    }

    #roleIndexRoot .btn-primary {
        background-color: var(--role-primary);
        border-color: var(--role-primary);
    }

    #roleIndexRoot .btn-primary:hover {
        filter: brightness(.94);
    }

    #roleIndexRoot .btn-outline-secondary,
    #roleIndexRoot .btn-outline-primary,
    #roleIndexRoot .btn-outline-danger {
        box-shadow: none !important;
    }

    /* Alerts */
    #roleIndexRoot .alert {
        padding: 10px 13px;
        font-size: 12px;
        border: 1px solid transparent !important;
        box-shadow: none !important;
    }

    #roleIndexRoot .alert-success {
        border-color: #d1e7dd !important;
    }

    #roleIndexRoot .alert-danger {
        border-color: #f5c2c7 !important;
    }

    /* Table Card */
    #roleIndexRoot .role-card-table {
        background: var(--role-card);
        border: 1px solid var(--role-border);
        border-radius: var(--role-radius) !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .035);
        margin-bottom: 20px;
        width: 100%;
        min-width: 0;
    }

    #roleIndexRoot .role-table-scroll {
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
    }

    /* Table */
    #roleIndexRoot .custom-role-table {
        width: 100%;
        min-width: 790px;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #roleIndexRoot .custom-role-table th {
        background: #f8f9fa;
        color: #6c757d;
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .045em;
        white-space: nowrap;
        padding: 12px 13px;
        border-bottom: 1px solid var(--role-border);
        vertical-align: middle;
    }

    #roleIndexRoot .custom-role-table td {
        color: var(--role-text);
        font-size: 12px;
        font-weight: 400;
        padding: 12px 13px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f1f3;
        line-height: 1.5;
    }

    #roleIndexRoot .custom-role-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #roleIndexRoot .custom-role-table tbody tr {
        transition: background-color .15s ease;
    }

    #roleIndexRoot .custom-role-table tbody tr:hover {
        background: #fafbfd;
    }

    #roleIndexRoot .custom-role-table strong {
        color: var(--role-heading) !important;
        font-weight: 600;
    }

    #roleIndexRoot .custom-role-table code {
        display: inline-block;
        max-width: 100%;
        padding: 4px 7px !important;
        background: #f1f4f9 !important;
        border: 1px solid #e8ebf0;
        color: #1572e8 !important;
        font-size: 11px !important;
        overflow-wrap: anywhere;
    }

    /* Role Icon */
    #roleIndexRoot .role-icon {
        width: 34px;
        height: 34px;
        min-width: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 7px !important;
        font-size: 13px;
    }

    /* Status Badges */
    #roleIndexRoot .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 5px 8px !important;
        font-size: 10.5px;
        font-weight: 600;
        line-height: 1.3;
        white-space: nowrap;
    }

    #roleIndexRoot .badge-soft-primary {
        background: #e8f1ff;
        color: #1572e8;
        border: 1px solid #d5e5ff;
    }

    #roleIndexRoot .badge-soft-success {
        background: #e8f7ef;
        color: #198754;
        border: 1px solid #d1eddd;
    }

    #roleIndexRoot .badge-soft-danger {
        background: #fcebec;
        color: #dc3545;
        border: 1px solid #f5d0d3;
    }

    #roleIndexRoot .badge-soft-secondary {
        background: #f0f1f3;
        color: #6c757d;
        border: 1px solid #e4e6e9;
    }

    #roleIndexRoot a.badge-soft-secondary:hover {
        background: #e5e7eb;
        color: #495057;
    }

    #roleIndexRoot .role-action-btn {
        min-height: 29px;
        padding: 4px 9px !important;
        font-size: 11px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    #roleIndexRoot .role-delete-btn {
        width: 29px;
        height: 29px;
        padding: 0 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Empty State */
    #roleIndexRoot .role-empty-state {
        padding: 42px 15px !important;
        color: var(--role-muted);
    }

    #roleIndexRoot .role-empty-state i {
        color: #b8c0cc;
    }

    #roleIndexRoot .role-empty-state span {
        display: block;
        margin-top: 8px;
        font-size: 12px;
        font-weight: 500;
    }

    /* Modals */
    #roleIndexRoot .modal-content,
    #deleteRoleModal .modal-content,
    #roleNoticeModal .modal-content {
        border: 1px solid var(--role-border) !important;
        border-radius: 9px !important;
        box-shadow: 0 10px 35px rgba(0, 0, 0, .12);
    }

    #deleteRoleModal .modal-dialog,
    #roleNoticeModal .modal-dialog {
        width: calc(100% - 24px);
        max-width: 340px;
        margin-left: auto;
        margin-right: auto;
    }

    #deleteRoleModal .modal-body,
    #roleNoticeModal .modal-body {
        padding: 12px !important;
    }

    #deleteRoleModal .modal-title,
    #roleNoticeModal h6 {
        color: var(--role-heading);
        font-size: 14px;
        font-weight: 600;
    }

    #deleteRoleModal p,
    #roleNoticeModal p {
        font-size: 12px;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    #deleteRoleModal .btn,
    #roleNoticeModal .btn {
        min-height: 32px;
        font-size: 11.5px !important;
        font-weight: 600;
    }

    /* Responsive */
    @media (min-width: 1400px) {
        #roleIndexRoot .role-page-container {
            padding: 20px 24px;
        }

        #roleIndexRoot .custom-role-table th,
        #roleIndexRoot .custom-role-table td {
            padding-left: 15px;
            padding-right: 15px;
        }
    }

    @media (max-width: 991.98px) {
        #roleIndexRoot .role-page-container {
            padding: 16px 14px;
        }

        #roleIndexRoot .custom-role-table {
            min-width: 790px;
        }
    }

    @media (max-width: 767.98px) {
        #roleIndexRoot .role-page-container {
            padding: 13px 10px;
        }

        #roleIndexRoot .role-page-header {
            align-items: flex-start;
            gap: 12px;
        }

        #roleIndexRoot .role-page-title {
            font-size: 17px;
        }

        #roleIndexRoot .role-header-actions {
            width: 100%;
            display: flex;
        }

        #roleIndexRoot .role-header-actions .btn {
            flex: 1 1 0;
            min-width: 0;
            padding: 7px 8px;
            font-size: 11px;
        }

        #roleIndexRoot .custom-role-table th {
            padding: 10px 11px;
            font-size: 10px;
        }

        #roleIndexRoot .custom-role-table td {
            padding: 10px 11px;
            font-size: 11.5px;
        }

        #roleIndexRoot .role-card-table {
            margin-bottom: 14px;
        }
    }

    @media (max-width: 380px) {
        #roleIndexRoot .role-page-container {
            padding-left: 8px;
            padding-right: 8px;
        }

        #roleIndexRoot .role-page-title {
            font-size: 16px;
        }

        #roleIndexRoot .role-header-actions .btn {
            font-size: 10px;
            padding-left: 5px;
            padding-right: 5px;
        }

        #roleIndexRoot .role-header-actions .btn i {
            margin-right: 3px !important;
        }
    }
</style>

<div class="container-fluid role-page-container" id="roleIndexRoot">

    <!-- Page Header -->
    <div class="role-page-header">

        <div>
            <h5 class="role-page-title">
                <i class="fas fa-user-shield"></i>
                <span>Role Management</span>
            </h5>

            <small class="role-page-subtitle">
                Manage staff roles and permissions.
            </small>
        </div>

        <div class="role-header-actions">

            <a href="{{ route('admin.roles.users') }}"
               class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-users-gear"></i>
                <span>Staff Directory</span>
            </a>

            <a href="{{ route('admin.roles.create') }}"
               class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i>
                <span>Add Role</span>
            </a>

        </div>
    </div>

    <!-- Flash Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3 d-flex align-items-center gap-2"
             role="alert">
            <i class="fas fa-circle-check text-success flex-shrink-0"></i>
            <div class="fw-semibold">{{ session('success') }}</div>
            <button type="button"
                    class="btn-close btn-sm shadow-none ms-auto"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3 d-flex align-items-center gap-2"
             role="alert">
            <i class="fas fa-circle-exclamation text-danger flex-shrink-0"></i>
            <div class="fw-semibold">{{ session('error') }}</div>
            <button type="button"
                    class="btn-close btn-sm shadow-none ms-auto"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Roles Table -->
    <div class="role-card-table">

        <div class="role-table-scroll">
            <table class="table custom-role-table align-middle mb-0">

                <thead>
                    <tr>
                        <th style="width: 50px;">SL</th>
                        <th style="min-width: 180px;">Role Name</th>
                        <th style="min-width: 140px;">Role Slug</th>
                        <th class="text-center" style="min-width: 130px;">Assigned Staff</th>
                        <th class="text-center" style="min-width: 150px;">Permissions</th>
                        <th class="text-center" style="min-width: 140px;">Role Type</th>
                        <th class="text-end" style="min-width: 130px;">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($roleStats as $index =>$role)
                        <tr>

                            <td class="font-monospace text-muted fw-bold bn-digit"
                                style="font-size: 11.5px;">
                                {{ $index + 1 }}
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-2">

                                    <div class="role-icon flex-shrink-0 {{ $role['is_protected'] ? 'bg-danger bg-opacity-10 text-danger border border-danger-subtle' : 'bg-primary bg-opacity-10 text-primary border border-primary-subtle' }}">
                                        <i class="{{ $role['is_protected'] ? 'fas fa-crown' : 'fas fa-shield' }}"></i>
                                    </div>

                                    <div class="min-w-0">
                                        <strong class="d-block mb-0"
                                                style="font-size: 12.5px;">
                                            {{ $role['display'] }}
                                        </strong>

                                        <small class="text-muted"
                                               style="font-size: 10.5px;">
                                            {{ $role['is_protected'] ? 'Root Access' : 'Internal Staff' }}
                                        </small>
                                    </div>

                                </div>
                            </td>

                            <td>
                                <code class="font-monospace">
                                    {{ $role['name'] }}
                                </code>
                            </td>

                            <td class="text-center">
                                <a href="{{ route('admin.roles.users', ['role' => $role['name']]) }}"
                                   class="badge badge-soft-secondary text-decoration-none font-monospace"
                                   title="View Assigned Staff">

                                    <i class="fas fa-users"></i>
                                    <span>{{ $role['users_count'] }} Users</span>

                                </a>
                            </td>

                            <td class="text-center">
                                @if($role['is_protected'])

                                    <span class="badge badge-soft-danger"
                                          title="Full Unrestricted Access">
                                        <i class="fas fa-infinity"></i>
                                        <span>Full Access</span>
                                    </span>

                                @else

                                    <span class="badge {{ $role['perms_count'] > 0 ? 'badge-soft-success' : 'badge-soft-secondary' }}">
                                        <i class="fas fa-key"></i>
                                        <span>{{ $role['perms_count'] }} Modules</span>
                                    </span>

                                @endif
                            </td>

                            <td class="text-center">
                                @if($role['is_protected'])

                                    <span class="badge bg-danger text-white">
                                        <i class="fas fa-lock"></i>
                                        <span>Super Admin</span>
                                    </span>

                                @elseif(in_array($role['name'], ['admin', 'branch_manager', 'staff']))

                                    <span class="badge badge-soft-primary">
                                        Core Role
                                    </span>

                                @else

                                    <span class="badge badge-soft-secondary">
                                        Custom Role
                                    </span>

                                @endif
                            </td>

                            <td class="text-end text-nowrap">

                                <div class="d-inline-flex align-items-center gap-1">

                                    <a href="{{ route('admin.roles.edit', $role['name']) }}"
                                       class="btn btn-outline-primary btn-sm role-action-btn"
                                       title="Configure Permissions">

                                        <i class="fas fa-sliders"></i>
                                        <span>Configure</span>

                                    </a>

                                    @if(!$role['is_protected'] && !in_array($role['name'], ['admin', 'branch_manager', 'staff']))

                                        <button type="button"
                                                class="btn btn-outline-danger btn-sm role-delete-btn"
                                                onclick="confirmDeleteRole('{{ $role['name'] }}', '{{ $role['display'] }}', {{$role['users_count'] }})"
                                                title="Delete Custom Role">

                                            <i class="fas fa-trash-can"
                                               style="font-size: 11px;"></i>

                                        </button>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="text-center role-empty-state">

                                <i class="fas fa-user-shield fa-2x d-block"></i>

                                <span>No roles found.</span>

                            </td>
                        </tr>

                    @endforelse
                </tbody>

            </table>
        </div>

    </div>

</div>

<!-- Modal 1: Delete Role Confirmation -->
<div class="modal fade"
     id="deleteRoleModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 106000;">

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content border-0 shadow-lg text-center p-3">

            <div class="modal-body p-1">

                <div class="mb-2">
                    <i class="fas fa-triangle-exclamation text-danger fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1"
                    id="deleteModalRoleTitle"
                    style="font-size: 14px;">
                    Delete Role
                </h6>

                <p class="text-muted small mb-3"
                   id="deleteModalMessage"
                   style="font-size: 12px;">
                    Are you sure you want to permanently delete this role?
                </p>

                <form id="deleteRoleForm" method="POST" action="">

                    @csrf
                    @method('DELETE')

                    <div class="d-flex justify-content-center gap-2">

                        <button type="button"
                                class="btn btn-light btn-sm px-3 py-1 border shadow-none"
                                data-bs-dismiss="modal"
                                style="font-size: 12px;">
                            Cancel
                        </button>

                        <button type="submit"
                                class="btn btn-danger btn-sm px-3 py-1 fw-bold shadow-none"
                                id="btnConfirmDeleteRole"
                                style="font-size: 12px;">
                            Yes, Delete
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>
</div>

<!-- Modal 2: Notice / Warning -->
<div class="modal fade"
     id="roleNoticeModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 106500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content border-0 shadow-lg text-center p-3">

            <div class="modal-body p-1">

                <div class="mb-2 text-warning">
                    <i class="fas fa-circle-exclamation fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1"
                    style="font-size: 14px;">
                    Action Restricted
                </h6>

                <p class="text-muted small mb-3"
                   id="roleNoticeModalMessage"
                   style="font-size: 12px;">
                </p>

                <button type="button"
                        class="btn btn-primary btn-sm px-4 py-1 fw-semibold shadow-none"
                        data-bs-dismiss="modal"
                        style="font-size: 12px;">
                    Got It
                </button>

            </div>

        </div>

    </div>
</div>

<script>
    function confirmDeleteRole(roleName, roleDisplay, userCount) {
        if (userCount > 0) {
            document.getElementById('roleNoticeModalMessage').innerText =
                `Cannot delete role "${roleDisplay}". There are ${userCount} active staff member(s) assigned to this role. Reassign them first.`;
            const noticeModal = new bootstrap.Modal(document.getElementById('roleNoticeModal'));
            noticeModal.show();
            return;
        }

        document.getElementById('deleteModalRoleTitle').innerText = 'Delete ' + roleDisplay + '?';
        document.getElementById('deleteModalMessage').innerText = `Are you sure you want to remove the role [${roleName}] and all its associated permissions?`;

        const form = document.getElementById('deleteRoleForm');
        form.action = "{{ url('admin/user-roles/roles/delete') }}/" + roleName;

        const modal = new bootstrap.Modal(document.getElementById('deleteRoleModal'));
        modal.show();
    }
</script>
@endsection
