@extends('layouts.admin')

@section('title', 'Role Permissions & Access Control - ' . config('app.name'))

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #roleIndexRoot .btn,
    #roleIndexRoot .btn-sm,
    #roleIndexRoot button,
    #roleIndexRoot .badge,
    #roleIndexRoot code,
    #roleIndexRoot .alert {
        border-radius: 5px !important;
    }

    #roleIndexRoot .role-card-table,
    #roleIndexRoot .modal-content {
        border-radius: 8px !important;
    }

    /* Page Container */
    .role-page-container {
        padding: 18px 20px;
    }

    /* Enterprise Table Card */
    .role-card-table {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 20px;
    }

    .custom-role-table th {
        background-color: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 11px 16px;
        white-space: nowrap;
    }

    .custom-role-table td {
        padding: 12px 16px;
        vertical-align: middle;
        font-size: 12.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }

    .custom-role-table tbody tr:hover {
        background-color: #fafbfc;
    }

    /* Soft Enterprise Badges */
    .badge-soft-primary   { background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; }
    .badge-soft-success   { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-soft-danger    { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .badge-soft-secondary { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

    a.badge-soft-secondary:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    /* Safe Bengali Digits */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 767.98px) {
        .role-page-container {
            padding: 12px 10px;
        }
        .role-header-actions {
            width: 100%;
            display: flex;
            gap: 8px;
        }
        .role-header-actions .btn {
            flex: 1 1 0;
            justify-content: center;
            text-align: center;
            padding: 7px 10px;
            font-size: 12px;
            white-space: nowrap;
        }
        .custom-role-table th,
        .custom-role-table td {
            padding: 10px 12px;
            font-size: 12px;
        }
    }
</style>

<div class="container-fluid role-page-container" id="roleIndexRoot">
    <!-- Top Header & Action Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2.5 mb-2">
        <div>
            <h5 class="fw-bold text-dark mb-0.5 d-flex align-items-center" style="font-size: 18px;">
                <i class="fas fa-user-shield text-primary me-2"></i>
                <span>Role-Based Access</span>
            </h5>
            <small class="text-muted d-block mb-2" style="font-size: 12px; line-height: 1.35;">Manage employee access and permissions.</small>
        </div>
        <div class="role-header-actions d-flex flex-wrap gap-2">
            <a href="{{ route('admin.roles.users') }}" class="btn btn-outline-secondary btn-sm px-3 py-1.5 fw-semibold shadow-xs d-inline-flex align-items-center">
                <i class="fas fa-users-gear me-2"></i>
                <span>Staff Directory</span>
            </a>
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm px-3.5 py-1.5 fw-semibold shadow-xs d-inline-flex align-items-center">
                <i class="fas fa-plus-circle me-2"></i>
                <span>Add New Role</span>
            </a>
        </div>
    </div>

    <!-- Flash Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3 small py-2 px-3 border-0 shadow-xs d-flex align-items-center gap-2" role="alert">
            <i class="fas fa-circle-check fs-6 text-success flex-shrink-0 me-1"></i>
            <div class="fw-semibold">{{ session('success') }}</div>
            <button type="button" class="btn-close btn-sm shadow-none ms-auto py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3 small py-2 px-3 border-0 shadow-xs d-flex align-items-center gap-2" role="alert">
            <i class="fas fa-circle-exclamation fs-6 text-danger flex-shrink-0 me-1"></i>
            <div class="fw-semibold">{{ session('error') }}</div>
            <button type="button" class="btn-close btn-sm shadow-none ms-auto py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Roles Table Card -->
    <div class="role-card-table overflow-hidden">
        <div class="table-responsive">
            <table class="table custom-role-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">SL</th>
                        <th style="min-width: 180px;">Role Name</th>
                        <th style="min-width: 140px;">Role Slug</th>
                        <th class="text-center" style="min-width: 130px;">Assigned Staff</th>
                        <th class="text-center" style="min-width: 150px;">Permissions Status</th>
                        <th class="text-center" style="min-width: 140px;">Privilege Level</th>
                        <th class="text-end" style="min-width: 130px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roleStats as $index =>$role)
                        <tr>
                            <td class="font-monospace text-muted fw-bold bn-digit" style="font-size: 11.5px;">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="rounded-circle d-flex me-2 align-items-center justify-content-center flex-shrink-0 {{ $role['is_protected'] ? 'bg-danger bg-opacity-10 text-danger border border-danger-subtle' : 'bg-primary bg-opacity-10 text-primary border border-primary-subtle' }}" style="width: 34px; height: 34px; font-size: 13px;">
                                        <i class="{{ $role['is_protected'] ? 'fas fa-crown' : 'fas fa-shield' }}"></i>
                                    </div>
                                    <div>
                                        <strong class="text-dark d-block mb-0" style="font-size: 13px;">{{ $role['display'] }}</strong>
                                        <small class="text-muted" style="font-size: 11px;">
                                            {{ $role['is_protected'] ? 'Root Access' : 'Internal Staff' }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code class="font-monospace text-primary bg-light px-2 py-1 border small" style="font-size: 11.5px;">{{ $role['name'] }}</code>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.roles.users', ['role' => $role['name']]) }}" class="badge badge-soft-secondary text-decoration-none px-2.5 py-1 font-monospace d-inline-flex align-items-center bn-digit" style="font-size: 11px;" title="View Assigned Staff">
                                    <i class="fas fa-users me-1 opacity-75"></i>
                                    <span>{{ $role['users_count'] }} Users</span>
                                </a>
                            </td>
                            <td class="text-center">
                                @if($role['is_protected'])
                                    <span class="badge badge-soft-danger px-2.5 py-1 font-monospace d-inline-flex align-items-center" style="font-size: 11px;" title="Full Unrestricted Access">
                                        <i class="fas fa-infinity me-1"></i>
                                        <span>Full Access</span>
                                    </span>
                                @else
                                    <span class="badge {{ $role['perms_count'] > 0 ? 'badge-soft-success' : 'badge-soft-secondary' }} px-2.5 py-1 font-monospace d-inline-flex align-items-center bn-digit" style="font-size: 11px;">
                                        <i class="fas fa-key me-1 opacity-75"></i>
                                        <span>{{ $role['perms_count'] }} Modules</span>
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($role['is_protected'])
                                    <span class="badge bg-danger text-white px-2.5 py-1 d-inline-flex align-items-center fw-semibold" style="font-size: 10px;">
                                        <i class="fas fa-lock me-1"></i>
                                        <span>Main Super Admin</span>
                                    </span>
                                @elseif(in_array($role['name'], ['admin', 'branch_manager', 'staff']))
                                    <span class="badge badge-soft-primary px-2.5 py-1 fw-semibold" style="font-size: 10px;">
                                        Core Role
                                    </span>
                                @else
                                    <span class="badge badge-soft-secondary px-2.5 py-1 fw-semibold" style="font-size: 10px;">
                                        Custom Role
                                    </span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    <a href="{{ route('admin.roles.edit', $role['name']) }}" class="btn btn-outline-primary btn-sm px-2.5 py-1 text-nowrap fw-semibold d-inline-flex align-items-center shadow-xs" style="font-size: 11.5px;" title="Configure Permissions">
                                        <i class="fas fa-sliders me-2"></i>
                                        <span>Configure</span>
                                    </a>
                                    @if(!$role['is_protected'] && !in_array($role['name'], ['admin', 'branch_manager', 'staff']))
                                        <button type="button" class="btn ms-2 btn-outline-danger btn-sm p-0 d-inline-flex align-items-center justify-content-center shadow-xs" 
                                                style="width: 28px; height: 28px;"
                                                onclick="confirmDeleteRole('{{ $role['name'] }}', '{{ $role['display'] }}', {{$role['users_count'] }})" 
                                                title="Delete Custom Role">
                                            <i class="fas fa-trash-can" style="font-size: 11px;"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted small">
                                <i class="fas fa-user-shield fa-2x mb-2 d-block opacity-40"></i>
                                <span class="fw-semibold">No roles defined in the system yet.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal 1: Delete Role Confirmation -->
<div class="modal fade" id="deleteRoleModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2">
                    <i class="fas fa-triangle-exclamation text-danger fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="deleteModalRoleTitle" style="font-size: 14px;">Delete Role</h6>
                <p class="text-muted small mb-3" id="deleteModalMessage" style="font-size: 12px;">Are you sure you want to permanently delete this role?</p>
                
                <form id="deleteRoleForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light btn-sm px-3 py-1 border shadow-xs" data-bs-dismiss="modal" style="font-size: 12px;">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-sm px-3.5 py-1 fw-bold shadow-xs" id="btnConfirmDeleteRole" style="font-size: 12px;">Yes, Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Notice / Warning Modal (Replaces Browser alert()) -->
<div class="modal fade" id="roleNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2 text-warning">
                    <i class="fas fa-circle-exclamation fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Action Restricted</h6>
                <p class="text-muted small mb-3" id="roleNoticeModalMessage" style="font-size: 12px;"></p>
                <button type="button" class="btn btn-primary btn-sm px-4 py-1 fw-semibold shadow-xs" data-bs-dismiss="modal" style="font-size: 12px;">Acknowledge</button>
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