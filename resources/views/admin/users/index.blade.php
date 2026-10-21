@extends('layouts.admin')

@section('title', 'Staff & User Management - ' . config('app.name'))

@section('admin_content')
<!-- Pure Text Data Table PDF Generator (jsPDF + AutoTable) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<style>
    /* Global Strict 5px Element Radius */
    #usersIndexRoot .btn,
    #usersIndexRoot .btn-sm,
    #usersIndexRoot button,
    #usersIndexRoot .form-control,
    #usersIndexRoot .form-select,
    #usersIndexRoot .badge,
    #usersIndexRoot .input-group-text,
    #usersIndexRoot .page-link,
    #usersIndexRoot .page-item span {
        border-radius: 5px !important;
    }

    #usersIndexRoot .card,
    #usersIndexRoot .users-card-table,
    #usersIndexRoot .bulk-action-bar,
    #usersIndexRoot .filter-card-box,
    #usersIndexRoot .modal-content {
        border-radius: 8px !important;
    }

    /* Page Canvas */
    .users-page-container {
        padding: 18px 20px;
    }

    .filter-card-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        padding: 16px 18px;
        margin-bottom: 20px;
    }

    .users-card-table {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        position: relative;
        margin-bottom: 20px;
    }

    /* Table Styling */
    .custom-users-table th {
        background-color: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 11px 14px;
        white-space: nowrap;
    }
    .custom-users-table td {
        padding: 11px 14px;
        vertical-align: middle;
        font-size: 12.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }

    /* Soft Color Badges */
    .badge-soft-primary   { background: #e0f2fe !important; color: #0284c7 !important; border: 1px solid #bae6fd !important; }
    .badge-soft-success   { background: #dcfce7 !important; color: #15803d !important; border: 1px solid #bbf7d0 !important; }
    .badge-soft-danger    { background: #fee2e2 !important; color: #b91c1c !important; border: 1px solid #fecaca !important; }
    .badge-soft-warning   { background: #fef9c3 !important; color: #854d0e !important; border: 1px solid #fef08a !important; }
    .badge-soft-info      { background: #e0f2fe !important; color: #0284c7 !important; border: 1px solid #bae6fd !important; }
    .badge-soft-secondary { background: #f1f5f9 !important; color: #475569 !important; border: 1px solid #e2e8f0 !important; }

    /* Dropdown Menus */
    .table-responsive {
        min-height: 280px;
    }
    @media (min-width: 992px) {
        .table-responsive,
        .users-card-table {
            overflow: visible !important;
        }
    }

    .custom-users-table .dropdown-menu {
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.12) !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 6px !important;
        padding: 6px 0;
    }

    .custom-users-table .dropdown-item {
        font-size: 12px;
        padding: 7px 14px;
        color: #334155;
        transition: background-color 0.15s ease;
    }
    .custom-users-table .dropdown-item:hover {
        background-color: #f8fafc;
        color: #0f172a;
    }

    /* Bulk Action Toolbar */
    .bulk-action-bar {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #ffffff;
        padding: 10px 16px;
        display: none;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.12);
        animation: fadeInToolbar 0.25s ease-in-out;
    }
    @keyframes fadeInToolbar {
        from { opacity: 0; transform: translateY(-6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Safe Bengali Digits */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* AJAX Overlay */
    #usersLoadingOverlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(2px);
        z-index: 25;
        display: none;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 767.98px) {
        .users-page-container {
            padding: 12px 10px;
        }
        .users-top-actions {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .users-top-actions .btn {
            flex: 1 1 calc(50% - 6px);
            justify-content: center;
            font-size: 11.5px;
            padding: 7px 8px;
        }
        .users-top-actions .btn-add-staff {
            flex: 1 1 100%;
        }
        .bulk-action-bar {
            flex-direction: column;
            gap: 10px;
            align-items: stretch;
            padding: 12px;
        }
        .bulk-action-tools {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .filter-card-box {
            padding: 12px 14px !important;
            margin-bottom: 16px !important;
        }
        .custom-users-table th, .custom-users-table td {
            padding: 9px 10px;
            font-size: 12px;
        }
    }
</style>

<div class="container-fluid users-page-container position-relative" id="usersIndexRoot">
    <!-- Header & Action Bar -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-0.5 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-users-gear text-primary"></i> <span>Staff & User Directory</span>
            </h5>
            <small class="text-muted" style="font-size: 11.5px; line-height: 1.35; display: block;">Manage system staff accounts, security restrictions, status controls, and credentials</small>
        </div>
        <div class="users-top-actions d-flex flex-wrap gap-2 no-pdf">
            <button type="button" class="btn btn-outline-success btn-sm px-3 py-1.5 fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5" onclick="exportUsersReport('excel')">
                <i class="fas fa-file-excel text-success me-1"></i> <span>Export Excel</span>
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm px-3 py-1.5 fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5" id="btn-export-users-pdf" onclick="downloadUsersPdf()">
                <i class="fas fa-file-pdf text-danger me-1"></i> <span id="users-pdf-text">Report PDF</span>
            </button>

            <a href="{{ route('admin.roles.trash') }}" class="btn btn-outline-secondary btn-sm px-3 py-1.5 fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5">
                <i class="fas fa-trash-can text-danger me-1"></i> <span>Trash</span>
            </a>
            <a href="{{ route('admin.roles.users.create_customer') }}" class="btn btn-outline-primary btn-sm px-3 py-1.5 fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5">
                <i class="fas fa-user-plus me-1"></i> <span>Add Customer</span>
            </a>
            <a href="{{ route('admin.roles.users.create_staff') }}" class="btn btn-primary btn-sm px-3 py-1.5 fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5 btn-add-staff">
                <i class="fas fa-user-shield me-1"></i> <span>Add Staff</span>
            </a>
        </div>
    </div>

    <!-- Alert Box (Dynamic AJAX Messages) -->
    <div id="usersAjaxAlertBox" class="alert d-none alert-dismissible fade show mb-3 small align-items-center gap-2 shadow-xs border-0 py-2 px-3" role="alert" style="border-radius: 5px;">
        <i id="usersAjaxAlertIcon" class="fas fa-circle-check fs-6 flex-shrink-0"></i>
        <div id="usersAjaxAlertMsg" class="fw-semibold"></div>
        <button type="button" class="btn-close btn-sm shadow-none ms-auto py-2" onclick="document.getElementById('usersAjaxAlertBox').classList.add('d-none')"></button>
    </div>

    <!-- Bulk Action Toolbar -->
    <div class="bulk-action-bar no-pdf" id="bulkActionBar">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-check-double text-warning fs-5"></i>
            <div>
                <strong class="font-monospace text-white bn-digit" id="selectedUsersCount">0</strong>
                <span class="small text-light ms-1">user(s) selected</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 bulk-action-tools flex-wrap">
            <select id="bulkActionSelect" class="form-select form-select-sm bg-light text-dark fw-semibold shadow-none border-0 py-1.5" style="min-width: 180px; font-size: 12px;">
                <option value="" disabled selected>-- Select Bulk Action --</option>
                <optgroup label="Account Status">
                    <option value="active">Change Status: Active</option>
                    <option value="inactive">Change Status: Inactive</option>
                    <option value="suspended">Change Status: Suspended</option>
                    <option value="frozen">Change Status: Frozen</option>
                    <option value="blocked">Change Status: Blocked</option>
                </optgroup>
                <optgroup label="Security Actions">
                    <option value="unlock">Unlock Locked Accounts</option>
                    <option value="force_logout">Force Logout All Sessions</option>
                </optgroup>
                <optgroup label="Danger Zone">
                    <option value="trash">Move Selected to Trash</option>
                </optgroup>
            </select>
            <button type="button" class="btn btn-warning btn-sm px-3 py-1.5 fw-bold shadow-xs text-dark" id="btnExecuteBulkAction" style="font-size: 12px;" onclick="executeBulkAction()">
                Apply to Selected
            </button>
            <button type="button" class="btn btn-outline-light btn-sm px-2.5 py-1.5" onclick="clearAllSelections()" title="Cancel Selection">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Multi-Filter Toolbar Card -->
    <div class="filter-card-box no-pdf">
        <form id="userFilterForm" onsubmit="event.preventDefault(); triggerUsersFilterAjax();">
            <div class="row g-2 align-items-center">
                <!-- Search Box -->
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm overflow-hidden border shadow-xs" style="border-radius: 5px;">
                        <span class="input-group-text bg-white border-0 text-muted ps-2.5"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                        <input type="text" name="search" id="userFilterSearch" class="form-control border-0 py-1.5 shadow-none" 
                               value="{{ request('search') }}" placeholder="Search name, email, phone, role..." autocomplete="off" style="font-size: 12px;">
                    </div>
                </div>

                <!-- User Type -->
                <div class="col-6 col-sm-3 col-md-2">
                    <select name="user_type" id="userFilterType" class="form-select form-select-sm py-1.5 shadow-none" style="font-size: 12px;">
                        <option value="all">All User Types</option>
                        <option value="staff" {{ request('user_type') === 'staff' ? 'selected' : '' }}>Staff & Admins</option>
                        <option value="customer" {{ request('user_type') === 'customer' ? 'selected' : '' }}>Customers Only</option>
                    </select>
                </div>

                <!-- Role Selector -->
                <div class="col-6 col-sm-3 col-md-2">
                    <select name="role" id="userFilterRole" class="form-select form-select-sm py-1.5 shadow-none" style="font-size: 12px;">
                        <option value="all">All Roles</option>
                        @foreach($allRoles as $rName)
                            <option value="{{ $rName }}" {{ request('role') == $rName ? 'selected' : '' }}>
                                {{ ucwords(str_replace(['_', '-'], ' ', $rName)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Selector -->
                <div class="col-6 col-sm-3 col-md-1">
                    <select name="status" id="userFilterStatus" class="form-select form-select-sm py-1.5 shadow-none" style="font-size: 12px;">
                        <option value="all">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="frozen" {{ request('status') == 'frozen' ? 'selected' : '' }}>Frozen</option>
                        <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                    </select>
                </div>

                <!-- Branch Selector -->
                <div class="col-6 col-sm-3 col-md-2">
                    <select name="branch_id" id="userFilterBranch" class="form-select form-select-sm py-1.5 shadow-none" style="font-size: 12px;">
                        <option value="all">All Branches</option>
                        <option value="hq" {{ request('branch_id') === 'hq' ? 'selected' : '' }}>Head Office / Online</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Per-Page Display Selector -->
                <div class="col-6 col-sm-3 col-md-1">
                    <select name="per_page" id="userFilterPerPage" class="form-select form-select-sm py-1.5 shadow-none fw-semibold" style="font-size: 12px;">
                        <option value="20" {{ request('per_page', '20') == '20' ? 'selected' : '' }}>20 / page</option>
                        <option value="30" {{ request('per_page', '30') == '30' ? 'selected' : '' }}>30 / page</option>
                        <option value="50" {{ request('per_page', '50') == '50' ? 'selected' : '' }}>50 / page</option>
                        <option value="100" {{ request('per_page', '100') == '100' ? 'selected' : '' }}>100 / page</option>
                        <option value="200" {{ request('per_page', '200') == '200' ? 'selected' : '' }}>200 / page</option>
                        <option value="500" {{ request('per_page', '500') == '500' ? 'selected' : '' }}>500 / page</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All Users</option>
                    </select>
                </div>

                <!-- Filter Actions -->
                <div class="col-12 col-md-1 d-flex gap-1.5 justify-content-end">
                    <button type="submit" class="btn btn-primary btn-sm px-2.5 py-1.5 w-100 fw-semibold shadow-xs" style="font-size: 12px;">
                        Filter
                    </button>
                    <button type="button" class="btn btn-light btn-sm border px-2.5 py-1.5 text-muted shadow-xs ms-2" onclick="resetUserFilters()" title="Reset All Filters">
                        <i class="fas fa-rotate-left"></i>
                    </button>
                </div>
            </div>

            <!-- Secondary Criteria: Dates & Activity -->
            <div class="row g-2 align-items-center mt-2 pt-2 border-top">
                <div class="col-6 col-md-2">
                    <label class="form-label text-muted small mb-0.5 fw-semibold" style="font-size: 10.5px;">Created Exact Date</label>
                    <input type="date" name="created_date" id="filter_created_date" class="form-control form-control-sm py-1 shadow-none" value="{{ request('created_date') }}" style="font-size: 11.5px;">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label text-muted small mb-0.5 fw-semibold" style="font-size: 10.5px;">Created Range: From</label>
                    <input type="date" name="created_from" id="filter_created_from" class="form-control form-control-sm py-1 shadow-none" value="{{ request('created_from') }}" style="font-size: 11.5px;">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label text-muted small mb-0.5 fw-semibold" style="font-size: 10.5px;">Created Range: To</label>
                    <input type="date" name="created_to" id="filter_created_to" class="form-control form-control-sm py-1 shadow-none" value="{{ request('created_to') }}" style="font-size: 11.5px;">
                </div>

                <div class="col-6 col-md-3">
                    <label class="form-label text-muted small mb-0.5 fw-semibold" style="font-size: 10.5px;">Login Activity Status</label>
                    <select name="inactivity" id="filter_inactivity" class="form-select form-select-sm py-1 shadow-none" onchange="toggleInactiveDateInput(this.value)" style="font-size: 11.5px;">
                        <option value="all">Any Activity (All)</option>
                        <option value="never_logged_in">Never Logged In</option>
                        <option value="inactive_30">No Login for 30+ Days</option>
                        <option value="inactive_60">No Login for 60+ Days</option>
                        <option value="inactive_90">No Login for 90+ Days</option>
                        <option value="before_date">No Login Since Specific Date...</option>
                    </select>
                </div>

                <div class="col-12 col-md-3 d-none" id="inactiveBeforeDateWrap">
                    <label class="form-label text-muted small mb-0.5 fw-semibold" style="font-size: 10.5px;">Inactive Since Date</label>
                    <input type="date" name="inactive_before_date" id="filter_inactive_date" class="form-control form-control-sm py-1 shadow-none" style="font-size: 11.5px;">
                </div>
            </div>
        </form>
    </div>

    <!-- Users Table Card with Loading Overlay -->
    <div class="users-card-table overflow-hidden" id="users-render-area">
        <!-- AJAX Overlay -->
        <div id="usersLoadingOverlay">
            <div class="spinner-border text-primary" role="status" style="width: 2.2rem; height: 2.2rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table custom-users-table align-middle mb-0" id="users-table">
                <thead>
                    <tr>
                        <th style="width: 40px;" class="text-center">
                            <input type="checkbox" id="selectAllUsers" class="form-check-input mt-0 shadow-none" onchange="toggleSelectAllUsers(this.checked)" title="Select / Deselect All" style="border-radius: 4px; cursor: pointer;">
                        </th>
                        <th style="width: 45px;">#</th>
                        <th style="min-width: 210px;">Staff / User</th>
                        <th style="min-width: 170px;">Role, Hub & Creator</th>
                        <th style="min-width: 140px;">Contact Info</th>
                        <th class="text-center" style="width: 130px;">Account Status</th>
                        <th style="min-width: 140px;">Activity / Created</th>
                        <th class="text-end" style="width: 130px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableTbody">
                    @include('admin.users.partials.user_rows', ['users' => $users])
                </tbody>
            </table>
        </div>

        <!-- Pagination with Per-Page Stats -->
        <div class="p-2.5 p-md-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50" id="usersPaginationWrapper">
            <small class="text-muted bn-digit" id="usersStatsText" style="font-size: 11.5px;">
                Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} user(s)
            </small>
            <div id="usersPaginationLinks">
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Account Status Change Modal -->
<div class="modal fade" id="statusChangeModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow p-3" style="border-radius: 8px;">
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5" style="font-size: 13.5px;">
                    <i class="fas fa-shield-halved text-primary"></i> <span>Account Status Control</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            
            <input type="hidden" id="modal_status_user_id">
            <p class="small text-muted mb-2" style="font-size: 11.5px;">Changing access level for: <strong class="text-dark" id="modal_status_user_name"></strong></p>

            <div class="mb-2">
                <label class="form-label small fw-semibold text-dark mb-1" style="font-size: 11.5px;">Select Status *</label>
                <select id="modal_status_select" class="form-select form-select-sm fw-semibold shadow-none py-1.5" style="font-size: 12px;">
                    <option value="active">Active (Normal Access)</option>
                    <option value="inactive">Inactive (Access Suspended)</option>
                    <option value="suspended">Suspended (Under Review)</option>
                    <option value="frozen">Frozen (Temporary Freeze)</option>
                    <option value="blocked">Blocked (Permanent Lockdown)</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-dark mb-1" style="font-size: 11.5px;">Reason / Admin Note</label>
                <textarea id="modal_status_reason" class="form-control form-control-sm shadow-none" rows="2" style="font-size: 12px;" placeholder=" Policy violation, staff leave..."></textarea>
            </div>

            <div class="d-flex justify-content-end gap-1.5">
                <button type="button" class="btn btn-sm btn-light py-1 px-3 border shadow-xs" data-bs-dismiss="modal" style="font-size: 12px;">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary fw-bold py-1 px-3 shadow-xs" id="btnSubmitStatusChange" onclick="executeStatusChange()" style="font-size: 12px;">
                    Update Status
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow p-3" style="border-radius: 8px;">
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5" style="font-size: 13.5px;">
                    <i class="fas fa-key text-warning"></i> <span>Reset Password</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            
            <input type="hidden" id="modal_reset_user_id">
            <p class="small text-muted mb-2" style="font-size: 11.5px;">Resetting credentials for: <strong class="text-dark" id="modal_reset_user_name"></strong></p>

            <div class="mb-2">
                <label class="form-label small fw-semibold text-dark mb-1" style="font-size: 11.5px;">New Password (Min 6 chars) *</label>
                <input type="password" id="modal_reset_password" class="form-control form-control-sm py-1.5 shadow-none" style="font-size: 12px;" placeholder="••••••••">
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-dark mb-1" style="font-size: 11.5px;">Confirm Password *</label>
                <input type="password" id="modal_reset_password_confirm" class="form-control form-control-sm py-1.5 shadow-none" style="font-size: 12px;" placeholder="••••••••">
            </div>

            <div class="d-flex justify-content-end gap-1.5">
                <button type="button" class="btn btn-sm btn-light py-1 px-3 border shadow-xs" data-bs-dismiss="modal" style="font-size: 12px;">Cancel</button>
                <button type="button" class="btn btn-sm btn-warning text-dark fw-bold py-1 px-3 shadow-xs" id="btnSubmitPasswordReset" onclick="executePasswordReset()" style="font-size: 12px;">
                    Save Password
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 3: Confirmation Dialog -->
<div class="modal fade" id="userConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow text-center p-3" style="border-radius: 8px;">
            <div class="modal-body p-1">
                <div class="mb-2" id="confirmModalIcon">
                    <i class="fas fa-triangle-exclamation text-danger fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="confirmModalTitle" style="font-size: 14px;">Confirm Action</h6>
                <p class="text-muted small mb-3" id="confirmModalMessage" style="font-size: 12px;">Are you sure you want to proceed?</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-sm px-3 py-1 border shadow-xs" data-bs-dismiss="modal" style="font-size: 12px;">Cancel</button>
                    <button type="button" class="btn btn-danger btn-sm px-3 py-1 fw-bold shadow-xs" id="btnExecuteUserConfirm" style="font-size: 12px;">Proceed</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 4: Notice / Alert Dialog -->
<div class="modal fade" id="userNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow text-center p-3" style="border-radius: 8px;">
            <div class="modal-body p-1">
                <div class="mb-2" id="userNoticeModalIcon">
                    <i class="fas fa-circle-exclamation text-warning fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="userNoticeModalTitle" style="font-size: 14px;">Notice</h6>
                <p class="text-muted small mb-3" id="userNoticeModalMsg" style="font-size: 12px;">Notice message.</p>
                <button type="button" class="btn btn-primary btn-sm px-4 py-1 fw-semibold shadow-xs" data-bs-dismiss="modal" style="font-size: 12px;">Acknowledge</button>
            </div>
        </div>
    </div>
</div>

<script>
    let filterDebounceTimer = null;
    let currentUserAjaxUrl = "{{ route('admin.roles.users') }}";
    let pendingConfirmAction = null;

    function showUsersNoticeModal(message, type = 'warning', title = 'Notice') {
        const modalEl = document.getElementById('userNoticeModal');
        const titleEl = document.getElementById('userNoticeModalTitle');
        const msgEl = document.getElementById('userNoticeModalMsg');
        const iconEl = document.getElementById('userNoticeModalIcon');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;
        if (iconEl) {
            if (type === 'danger' || type === 'error') {
                iconEl.innerHTML = '<i class="fas fa-circle-xmark text-danger fa-2x"></i>';
            } else if (type === 'success') {
                iconEl.innerHTML = '<i class="fas fa-circle-check text-success fa-2x"></i>';
            } else {
                iconEl.innerHTML = '<i class="fas fa-triangle-exclamation text-warning fa-2x"></i>';
            }
        }

        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById('userFilterSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(filterDebounceTimer);
                filterDebounceTimer = setTimeout(() => {
                    triggerUsersFilterAjax();
                }, 350);
            });
        }

        const autoTriggerIds = [
            'userFilterType', 'userFilterRole', 'userFilterStatus', 'userFilterBranch', 'userFilterPerPage',
            'filter_created_date', 'filter_created_from', 'filter_created_to',
            'filter_inactivity', 'filter_inactive_date'
        ];

        autoTriggerIds.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('change', () => triggerUsersFilterAjax());
            }
        });

        document.addEventListener('click', function (e) {
            const pageLink = e.target.closest('#usersPaginationLinks a');
            if (pageLink) {
                e.preventDefault();
                const url = pageLink.getAttribute('href');
                if (url && url !== '#') {
                    fetchUsersByUrl(url);
                }
            }
        });
    });

    function toggleInactiveDateInput(val) {
        const wrap = document.getElementById('inactiveBeforeDateWrap');
        if (val === 'before_date') {
            wrap.classList.remove('d-none');
        } else {
            wrap.classList.add('d-none');
            document.getElementById('filter_inactive_date').value = '';
        }
    }

    function triggerUsersFilterAjax() {
        const form = document.getElementById('userFilterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        const baseUrl = "{{ route('admin.roles.users') }}";
        const targetUrl = baseUrl + '?' + params.toString();

        fetchUsersByUrl(targetUrl);
    }

    function fetchUsersByUrl(url) {
        currentUserAjaxUrl = url;
        const overlay = document.getElementById('usersLoadingOverlay');
        if (overlay) overlay.style.display = 'flex';

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('usersTableTbody').innerHTML = data.html;
                document.getElementById('usersPaginationLinks').innerHTML = data.pagination;
                document.getElementById('usersStatsText').innerText = 
                    `Showing ${data.firstItem} to ${data.lastItem} of ${data.total} user(s)`;

                clearAllSelections();
                window.history.replaceState({}, '', url);
            }
        })
        .catch(err => {
            console.error('AJAX Users Fetch Error:', err);
        })
        .finally(() => {
            if (overlay) overlay.style.display = 'none';
        });
    }

    function resetUserFilters() {
        document.getElementById('userFilterSearch').value = '';
        document.getElementById('userFilterType').value = 'all';
        document.getElementById('userFilterRole').value = 'all';
        document.getElementById('userFilterStatus').value = 'all';
        document.getElementById('userFilterBranch').value = 'all';
        document.getElementById('userFilterPerPage').value = '20';
        document.getElementById('filter_created_date').value = '';
        document.getElementById('filter_created_from').value = '';
        document.getElementById('filter_created_to').value = '';
        document.getElementById('filter_inactivity').value = 'all';
        document.getElementById('inactiveBeforeDateWrap').classList.add('d-none');
        document.getElementById('filter_inactive_date').value = '';

        triggerUsersFilterAjax();
    }

    // 1. Direct Excel Download (Without Blank Tab Crash)
    function exportUsersReport(type) {
        const form = document.getElementById('userFilterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.append('export', type);
        window.location.href = "{{ route('admin.roles.users') }}?" + params.toString();
    }

    // 2. Pure Text Data Table PDF Generator (No UI Screenshots, Only Structured Data Table)
    function downloadUsersPdf() {
        const btn = document.getElementById('btn-export-users-pdf');
        const btnText = document.getElementById('users-pdf-text');
        const origText = btnText.innerText;

        btn.disabled = true;
        btnText.innerText = 'Preparing PDF...';

        const form = document.getElementById('userFilterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.append('export', 'pdf_data');

        fetch("{{ route('admin.roles.users') }}?" + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success || !data.records) {
                throw new Error('Failed to load report data');
            }

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({
                orientation: 'landscape',
                unit: 'mm',
                format: 'a4'
            });

            // Document Header (Pure Text)
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(14);
            doc.setTextColor(15, 23, 42);
            doc.text(data.title || 'Staff & User Directory Report', 14, 14);

            doc.setFont('helvetica', 'normal');
            doc.setFontSize(9);
            doc.setTextColor(100, 116, 139);
            doc.text(`Generated At: ${data.generated_at}   |   Total Users: ${data.total}`, 14, 20);

            // Prepare Pure Text Rows
            const tableBody = data.records.map(r => [
                r.sl,
                '#' + r.id,
                r.name,
                r.role,
                r.designation,
                r.branch,
                r.creator,
                r.phone,
                r.email,
                r.status,
                r.created_at,
                r.last_login
            ]);

            // Render Clean Bordered Data Table
            doc.autoTable({
                startY: 25,
                head: [[
                    'SL',
                    'ID',
                    'Full Name',
                    'Role',
                    'Designation',
                    'Branch Hub',
                    'Registered By',
                    'Phone',
                    'Email Address',
                    'Status',
                    'Created',
                    'Last Login'
                ]],
                body: tableBody,
                theme: 'grid',
                styles: {
                    font: 'helvetica',
                    fontSize: 8,
                    cellPadding: 2.2,
                    textColor: [30, 41, 59],
                    lineColor: [203, 213, 225],
                    lineWidth: 0.15,
                    valign: 'middle'
                },
                headStyles: {
                    fillColor: [241, 245, 249],
                    textColor: [15, 23, 42],
                    fontStyle: 'bold',
                    lineColor: [148, 163, 184],
                    lineWidth: 0.2
                },
                alternateRowStyles: {
                    fillColor: [248, 250, 252]
                },
                columnStyles: {
                    0: { cellWidth: 10, halign: 'center' },
                    1: { cellWidth: 14, halign: 'center' },
                    2: { cellWidth: 32, fontStyle: 'bold' },
                    3: { cellWidth: 22 },
                    4: { cellWidth: 26 },
                    5: { cellWidth: 25 },
                    6: { cellWidth: 24 },
                    7: { cellWidth: 24 },
                    8: { cellWidth: 40 },
                    9: { cellWidth: 16, halign: 'center' },
                    10: { cellWidth: 20, halign: 'center' },
                    11: { cellWidth: 24, halign: 'center' }
                },
                margin: { top: 14, right: 10, bottom: 14, left: 10 },
                didDrawPage: function (hookData) {
                    const pageCount = doc.internal.getNumberOfPages();
                    doc.setFontSize(8);
                    doc.setTextColor(148, 163, 184);
                    doc.text(
                        `Page ${hookData.pageNumber} of ${pageCount}`,
                        doc.internal.pageSize.getWidth() - 28,
                        doc.internal.pageSize.getHeight() - 6
                    );
                }
            });

            const fileName = 'Staff_and_Users_Report_' + (new Date().toISOString().slice(0, 10)) + '.pdf';
            doc.save(fileName);
        })
        .catch(err => {
            console.error('PDF Export error:', err);
            showUsersNoticeModal('Failed to generate PDF data table. Please try again.', 'danger', 'PDF Export Error');
        })
        .finally(() => {
            btn.disabled = false;
            btnText.innerText = origText;
        });
    }

    function toggleSelectAllUsers(isChecked) {
        document.querySelectorAll('.user-select-checkbox').forEach(cb => {
            cb.checked = isChecked;
        });
        handleUserSelectionChange();
    }

    function handleUserSelectionChange() {
        const selectedCheckboxes = document.querySelectorAll('.user-select-checkbox:checked');
        const count = selectedCheckboxes.length;
        const bar = document.getElementById('bulkActionBar');
        const countSpan = document.getElementById('selectedUsersCount');
        const masterCb = document.getElementById('selectAllUsers');

        if (countSpan) countSpan.innerText = count;

        if (count > 0) {
            bar.style.display = 'flex';
        } else {
            bar.style.display = 'none';
        }

        const allCheckboxes = document.querySelectorAll('.user-select-checkbox');
        if (masterCb && allCheckboxes.length > 0) {
            masterCb.checked = (count === allCheckboxes.length);
        }
    }

    function clearAllSelections() {
        document.querySelectorAll('.user-select-checkbox').forEach(cb => cb.checked = false);
        const masterCb = document.getElementById('selectAllUsers');
        if (masterCb) masterCb.checked = false;
        handleUserSelectionChange();
    }

    function executeBulkAction() {
        const selectedCheckboxes = document.querySelectorAll('.user-select-checkbox:checked');
        const ids = Array.from(selectedCheckboxes).map(cb => parseInt(cb.value));
        const actionSelect = document.getElementById('bulkActionSelect');
        const action = actionSelect.value;

        if (!action) {
            showUsersNoticeModal('Please select an action from the dropdown first.', 'warning', 'Action Required');
            return;
        }

        if (ids.length === 0) {
            showUsersNoticeModal('No users selected for bulk action.', 'warning', 'No Users Selected');
            return;
        }

        const confirmTitle = action === 'trash' ? 'Move Users to Trash?' : 'Confirm Bulk Action';
        const confirmMsg = action === 'trash' 
            ? `Are you sure you want to move ${ids.length} selected user(s) to trash?` 
            : `Apply [${action.toUpperCase()}] action to ${ids.length} selected user(s)?`;

        document.getElementById('confirmModalTitle').innerText = confirmTitle;
        document.getElementById('confirmModalMessage').innerText = confirmMsg;

        pendingConfirmAction = () => {
            const btn = document.getElementById('btnExecuteBulkAction');
            const origText = btn.innerText;
            btn.disabled = true;
            btn.innerText = 'Processing...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            fetch("{{ route('admin.roles.users.bulk_action') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ids: ids, action: action })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showUsersAlert(data.message, 'success');
                    fetchUsersByUrl(currentUserAjaxUrl);
                } else {
                    showUsersAlert(data.message || 'Bulk action failed.', 'danger');
                }
            })
            .catch(err => {
                console.error(err);
                showUsersAlert('Server error occurred during bulk operation.', 'danger');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerText = origText;
                actionSelect.value = '';
            });
        };

        const modal = new bootstrap.Modal(document.getElementById('userConfirmModal'));
        modal.show();
    }

    function openStatusModal(id, currentStatus, userName) {
        document.getElementById('modal_status_user_id').value = id;
        document.getElementById('modal_status_user_name').innerText = userName;
        document.getElementById('modal_status_select').value = currentStatus || 'active';
        document.getElementById('modal_status_reason').value = '';

        const modal = new bootstrap.Modal(document.getElementById('statusChangeModal'));
        modal.show();
    }

    function executeStatusChange() {
        const userId = document.getElementById('modal_status_user_id').value;
        const newStatus = document.getElementById('modal_status_select').value;
        const reason = document.getElementById('modal_status_reason').value;
        const btn = document.getElementById('btnSubmitStatusChange');

        btn.disabled = true;
        btn.innerText = 'Updating...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        fetch("{{ url('admin/user-roles/users/update-status') }}/" + userId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: newStatus, reason: reason })
        })
        .then(res => res.json())
        .then(data => {
            const modalEl = document.getElementById('statusChangeModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            if (data.success) {
                showUsersAlert(data.message, 'success');
                fetchUsersByUrl(currentUserAjaxUrl);
            } else {
                showUsersAlert(data.message || 'Status update failed.', 'danger');
            }
        })
        .catch(err => {
            console.error(err);
            showUsersAlert('Server error occurred.', 'danger');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = 'Update Status';
        });
    }

    function openResetPasswordModal(id, userName) {
        document.getElementById('modal_reset_user_id').value = id;
        document.getElementById('modal_reset_user_name').innerText = userName;
        document.getElementById('modal_reset_password').value = '';
        document.getElementById('modal_reset_password_confirm').value = '';

        const modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
        modal.show();
    }

    function executePasswordReset() {
        const userId = document.getElementById('modal_reset_user_id').value;
        const password = document.getElementById('modal_reset_password').value;
        const passwordConfirm = document.getElementById('modal_reset_password_confirm').value;
        const btn = document.getElementById('btnSubmitPasswordReset');

        if (!password || password.length < 6) {
            showUsersNoticeModal('Password must be at least 6 characters long.', 'warning', 'Password Validation');
            return;
        }

        if (password !== passwordConfirm) {
            showUsersNoticeModal('Passwords do not match. Please verify both fields.', 'warning', 'Password Mismatch');
            return;
        }

        btn.disabled = true;
        btn.innerText = 'Saving...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        fetch("{{ url('admin/user-roles/users/reset-password') }}/" + userId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ password: password, password_confirmation: passwordConfirm })
        })
        .then(res => res.json())
        .then(data => {
            const modalEl = document.getElementById('resetPasswordModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            if (data.success) {
                showUsersAlert(data.message, 'success');
            } else {
                showUsersAlert(data.message || 'Password reset failed.', 'danger');
            }
        })
        .catch(err => {
            console.error(err);
            showUsersAlert('Server error occurred.', 'danger');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = 'Save Password';
        });
    }

    function confirmForceLogout(userId, userName) {
        document.getElementById('confirmModalTitle').innerText = 'Force Logout?';
        document.getElementById('confirmModalMessage').innerText = `Are you sure you want to terminate all active sessions for "${userName}" immediately?`;
        
        pendingConfirmAction = () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            fetch("{{ url('admin/user-roles/users/force-logout') }}/" + userId, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                showUsersAlert(data.message, 'success');
                fetchUsersByUrl(currentUserAjaxUrl);
            })
            .catch(err => showUsersAlert('Failed to revoke session.', 'danger'));
        };

        const modal = new bootstrap.Modal(document.getElementById('userConfirmModal'));
        modal.show();
    }

    function unlockUser(userId) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        fetch("{{ url('admin/user-roles/users/unlock') }}/" + userId, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            showUsersAlert(data.message, 'success');
            fetchUsersByUrl(currentUserAjaxUrl);
        })
        .catch(err => showUsersAlert('Failed to unlock account.', 'danger'));
    }

    function confirmTrashUser(userId, userName) {
        document.getElementById('confirmModalTitle').innerText = 'Move to Trash?';
        document.getElementById('confirmModalMessage').innerText = `Are you sure you want to move "${userName}" to trash? Associated orders and history remain safe.`;
        
        pendingConfirmAction = () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            fetch("{{ url('admin/user-roles/users/delete') }}/" + userId, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                showUsersAlert(data.message, 'success');
                fetchUsersByUrl(currentUserAjaxUrl);
            })
            .catch(err => showUsersAlert('Failed to delete account.', 'danger'));
        };

        const modal = new bootstrap.Modal(document.getElementById('userConfirmModal'));
        modal.show();
    }

    document.getElementById('btnExecuteUserConfirm').addEventListener('click', function() {
        const modalEl = document.getElementById('userConfirmModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        if (typeof pendingConfirmAction === 'function') {
            pendingConfirmAction();
            pendingConfirmAction = null;
        }
    });

    function showUsersAlert(msg, type = 'success') {
        const box = document.getElementById('usersAjaxAlertBox');
        const msgEl = document.getElementById('usersAjaxAlertMsg');
        const icon = document.getElementById('usersAjaxAlertIcon');

        if (box && msgEl) {
            box.className = `alert alert-${type} alert-dismissible fade show mb-3 small d-flex align-items-center gap-2 shadow-xs border-0 py-2 px-3`;
            icon.className = type === 'success' ? 'fas fa-circle-check fs-6 text-success flex-shrink-0' : 'fas fa-circle-exclamation fs-6 text-danger flex-shrink-0';
            msgEl.innerText = msg;
            box.classList.remove('d-none');

            setTimeout(() => {
                box.classList.add('d-none');
            }, 5000);
        }
    }
</script>
@endsection