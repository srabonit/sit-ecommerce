@extends('layouts.admin')

@section('title', 'Manage Staff - ' . $branch->name)

@section('admin_content')
<style>
    #branchStaffRoot .btn,
    #branchStaffRoot .btn-sm,
    #branchStaffRoot button,
    #branchStaffRoot .badge,
    #branchStaffRoot .form-control,
    #branchStaffRoot .form-select,
    #branchStaffRoot .input-group-text,
    #branchStaffRoot .card,
    #branchStaffRoot .alert,
    #branchStaffRoot .table,
    #branchStaffRoot .modal-content {
        border-radius: 5px !important;
    }

    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
    }

    .btn-compact {
        font-size: 11.5px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    .perm-badge {
        font-size: 10px !important;
        padding: 3px 6px !important;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }
</style>

<div class="container-fluid py-2 px-2 px-md-3" id="branchStaffRoot">
    <!-- Top Navigation Header -->
    <div class="d-flex justify-content-between align-items-center mb-3 bg-white p-2.5 px-3 shadow-xs border">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-primary text-white fw-bold px-2.5 py-1.5" style="font-size: 12px;">
                <i class="fas fa-users-cog me-1"></i> Branch Personnel
            </span>
            <span class="fw-bold text-dark fs-6">{{ $branch->name }}</span>
            <span class="badge bg-light text-dark border font-monospace" style="font-size: 11px;">
                {{ $branch->code ?? $branch->branch_code }}
            </span>
            @if($branch->manager)
                <span class="badge bg-info-subtle text-info border font-monospace" style="font-size: 10.5px;">
                    <i class="fas fa-user-tie me-1"></i> Manager: {{ $branch->manager->name }}
                </span>
            @endif
        </div>

        <div class="d-flex align-items-center gap-1.5">
            <a href="{{ route('admin.branch.index') }}" class="btn btn-outline-secondary btn-compact py-1 px-2.5">
                <i class="fas fa-arrow-left me-1"></i> Back to Branches
            </a>
            <a href="{{ route('admin.pos.terminal', $branch->id) }}" class="btn btn-warning text-white btn-compact py-1 px-2.5">
                <i class="fas fa-desktop me-1"></i> POS Terminal
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 mb-3" style="font-size: 12px;">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 mb-3" style="font-size: 12px;">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">
        <!-- Left Column: Assign New Staff / Update Permissions Form -->
        <div class="col-12 col-lg-4">
            <div class="card border shadow-xs bg-white p-3">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3" style="font-size: 13px;">
                    <i class="fas fa-user-plus text-primary me-1"></i> Assign Staff to Branch
                </h6>

                <form action="{{ route('admin.branch.staff.assign', $branch->id) }}" method="POST">
                    @csrf

                    <!-- Select Staff Member -->
                    <div class="mb-2.5">
                        <label class="form-label fw-bold text-dark" style="font-size: 11.5px;">
                            Select User / Employee <span class="text-danger">*</span>
                        </label>
                        <select name="user_id" class="form-select form-select-sm" required style="font-size: 11.5px;">
                            <option value="">-- Choose User --</option>
                            @foreach($availableUsers as $u)
                                <option value="{{ $u->id }}">
                                    {{ $u->name }} ({{ $u->phone ?? $u->email }}) [{{ ucfirst($u->role) }}]
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Role in this Branch -->
                    <div class="mb-2.5">
                        <label class="form-label fw-bold text-dark" style="font-size: 11.5px;">
                            Role in this Branch <span class="text-danger">*</span>
                        </label>
                        <select name="role_in_branch" class="form-select form-select-sm" required style="font-size: 11.5px;">
                            <option value="staff">Branch Staff</option>
                            <option value="cashier">POS Cashier</option>
                            <option value="sales_person">Sales Representative</option>
                            <option value="branch_manager">Branch Manager / Supervisor</option>
                        </select>
                    </div>

                    <!-- Granular Permission Checkboxes -->
                    <div class="mb-3 p-2.5 bg-light border" style="border-radius: 5px;">
                        <span class="text-muted fw-bold d-block mb-2" style="font-size: 11px;">Operational Permissions</span>

                        <div class="form-check form-switch mb-1.5">
                            <input class="form-check-input" type="checkbox" name="can_access_pos" value="1" id="permPos" checked>
                            <label class="form-check-label fw-semibold text-dark" for="permPos" style="font-size: 11px;">
                                Allow POS Terminal Access
                            </label>
                        </div>

                        <div class="form-check form-switch mb-1.5">
                            <input class="form-check-input" type="checkbox" name="can_manage_inventory" value="1" id="permInv">
                            <label class="form-check-label fw-semibold text-dark" for="permInv" style="font-size: 11px;">
                                Allow Inventory Stock Management
                            </label>
                        </div>

                        <div class="form-check form-switch mb-1.5">
                            <input class="form-check-input" type="checkbox" name="can_view_reports" value="1" id="permRep">
                            <label class="form-check-label fw-semibold text-dark" for="permRep" style="font-size: 11px;">
                                Allow Viewing Sales &amp; Financial Reports
                            </label>
                        </div>

                        <div class="form-check form-switch mb-0 border-top pt-1.5 mt-1.5">
                            <input class="form-check-input" type="checkbox" name="is_primary" value="1" id="permPrimary">
                            <label class="form-check-label fw-semibold text-primary" for="permPrimary" style="font-size: 11px;">
                                Set as User's Primary Working Hub
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary fw-bold w-100 py-2 btn-compact">
                        <i class="fas fa-save me-1"></i> Save Staff Assignment
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Column: Currently Assigned Personnel List -->
        <div class="col-12 col-lg-8">
            <div class="card border shadow-xs bg-white p-3">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                    <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">
                        <i class="fas fa-id-badge text-warning me-1"></i> Active Branch Personnel
                    </h6>
                    @php
                        $assignedList = $branch->assignedUsers ?? ($branch->users ?? collect());
                    @endphp
                    <span class="badge bg-light text-dark border font-monospace" style="font-size: 11px;">
                        Total Assigned: {{ $assignedList->count() }}
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0" style="font-size: 11.5px;">
                        <thead class="table-light">
                            <tr>
                                <th class="py-2">Employee</th>
                                <th class="py-2">Role in Branch</th>
                                <th class="py-2">Permissions</th>
                                <th class="py-2 text-center">Primary</th>
                                <th class="py-2 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignedList as $staff)
                                @php
                                    $pivot = $staff->pivot ?? null;
                                    $role = $pivot->role_in_branch ?? ($staff->role ?? 'staff');
                                    $canPos = $pivot ? (bool)$pivot->can_access_pos : true;
                                    $canInv = $pivot ? (bool)$pivot->can_manage_inventory : false;
                                    $canRep = $pivot ? (bool)$pivot->can_view_reports : false;
                                    $isPrimary = $pivot ? (bool)$pivot->is_primary : ((int)$staff->primary_branch_id === (int)$branch->id);
                                @endphp
                                <tr>
                                    <td class="py-2">
                                        <div class="fw-bold text-dark">{{ $staff->name }}</div>
                                        <small class="text-muted font-monospace bn-digit">{{ $staff->phone ?? $staff->email }}</small>
                                    </td>
                                    <td class="py-2">
                                        <span class="badge bg-light text-dark border font-monospace">
                                            {{ strtoupper(str_replace('_', ' ', $role)) }}
                                        </span>
                                    </td>
                                    <td class="py-2">
                                        <div class="d-flex gap-1 flex-wrap">
                                            @if($canPos)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle perm-badge">
                                                    <i class="fas fa-desktop"></i> POS
                                                </span>
                                            @endif
                                            @if($canInv)
                                                <span class="badge bg-info-subtle text-info border border-info-subtle perm-badge">
                                                    <i class="fas fa-boxes"></i> Inventory
                                                </span>
                                            @endif
                                            @if($canRep)
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle perm-badge">
                                                    <i class="fas fa-chart-line"></i> Reports
                                                </span>
                                            @endif
                                            @if(!$canPos && !$canInv && !$canRep)
                                                <span class="text-muted" style="font-size: 10px;">View Only</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-2 text-center">
                                        @if($isPrimary)
                                            <span class="badge bg-primary text-white" style="font-size: 9.5px;">Primary</span>
                                        @else
                                            <span class="text-muted" style="font-size: 11px;">-</span>
                                        @endif
                                    </td>
                                    <td class="py-2 text-end">
                                        <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 btn-compact" 
                                                title="Remove Staff" onclick="detachStaffMember({{ $staff->id }}, '{{ addslashes($staff->name) }}')">
                                            <i class="fas fa-user-minus"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        No personnel assigned to this branch yet. Use the left form to assign staff members.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const CSRF = "{{ csrf_token() }}";
const BRANCH_ID = {{ $branch->id }};

function detachStaffMember(userId, userName) {
    if (!confirm(`Are you sure you want to detach '${userName}' from this branch?`)) {
        return;
    }

    const url = "{{ url('admin/branch/remove-staff') }}/" + BRANCH_ID + "/" + userId;

    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Failed to remove staff.');
        }
    })
    .catch(() => {
        alert('Server connection error. Please try again.');
    });
}
</script>
@endsection