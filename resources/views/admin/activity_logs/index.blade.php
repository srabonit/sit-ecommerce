@extends('layouts.admin')

@section('title', 'Live Audit Trail & Activity Logs - ' . config('app.name'))

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #auditLogsRoot .btn,
    #auditLogsRoot .btn-sm,
    #auditLogsRoot button,
    #auditLogsRoot .form-control,
    #auditLogsRoot .form-select,
    #auditLogsRoot .badge,
    #auditLogsRoot .input-group,
    #auditLogsRoot .input-group-text,
    #auditLogsRoot .alert,
    #logDiffModal .btn,
    #cleanupModal .btn,
    #cleanupModal .form-control,
    #cleanupModal .form-select,
    #cleanupModal .alert,
    #auditConfirmModal .btn {
        border-radius: 5px !important;
    }

    #auditLogsRoot .audit-card-table,
    #auditLogsRoot .filter-card-box,
    #logDiffModal .modal-content,
    #cleanupModal .modal-content,
    #auditConfirmModal .modal-content {
        border-radius: 8px !important;
    }

    /* Page Container */
    .audit-page-container {
        padding: 18px 20px;
    }

    /* Filter Card */
    .filter-card-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        padding: 16px 18px;
        margin-bottom: 18px;
    }

    #auditLogsRoot .form-control,
    #auditLogsRoot .form-select,
    #cleanupModal .form-control,
    #cleanupModal .form-select {
        font-size: 12.5px;
        border-color: #cbd5e1;
        padding: 6px 11px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    #auditLogsRoot .form-control:focus,
    #auditLogsRoot .form-select:focus,
    #cleanupModal .form-control:focus,
    #cleanupModal .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Audit Table Card */
    .audit-card-table {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        position: relative;
        margin-bottom: 20px;
    }

    .custom-audit-table th {
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

    .custom-audit-table td {
        padding: 11px 14px;
        vertical-align: middle;
        font-size: 12.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }

    .custom-audit-table tbody tr:hover {
        background-color: #fafbfc;
    }

    /* Soft Enterprise Badges */
    .badge-soft-create { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-soft-update { background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; }
    .badge-soft-delete { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .badge-soft-status { background: #fef9c3; color: #854d0e; border: 1px solid #fef08a; }
    .badge-soft-auth   { background: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff; }
    .badge-soft-misc   { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

    /* Code Comparison Box */
    .diff-box {
        background: #0f172a;
        color: #f8fafc;
        border-radius: 6px;
        padding: 12px 14px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 11.5px;
        line-height: 1.5;
        max-height: 260px;
        overflow-y: auto;
        border: 1px solid #1e293b;
        margin-bottom: 0;
    }

    /* Cleanup Mode Radio Selector Box */
    .cleanup-radio-group .form-check {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 5px;
        padding: 6px 10px 6px 28px;
        transition: all 0.15s ease;
        cursor: pointer;
    }
    .cleanup-radio-group .form-check:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    /* Safe Bengali Digits */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* AJAX Loading Spinner Cover */
    #auditLoadingOverlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(2px);
        z-index: 20;
        display: none;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 767.98px) {
        .audit-page-container {
            padding: 12px 10px;
        }
        .audit-header-actions {
            width: 100%;
            display: flex;
            gap: 8px;
        }
        .audit-header-actions .btn {
            flex: 1 1 0;
            justify-content: center;
            text-align: center;
            padding: 7px 10px;
            font-size: 12px;
            white-space: nowrap;
        }
        .filter-card-box {
            padding: 12px 14px !important;
            margin-bottom: 14px;
        }
        .custom-audit-table th,
        .custom-audit-table td {
            padding: 9px 10px;
            font-size: 12px;
        }
        .cleanup-radio-group {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

<div class="container-fluid audit-page-container" id="auditLogsRoot">
    <!-- Top Header & Actions -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2.5 mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-0.5 d-flex align-items-center" style="font-size: 18px;">
                <i class="fas fa-clipboard-list text-primary me-2"></i>
                <span>Admin & Employee Activity log</span>
            </h5>
            <small class="text-muted d-block mb-2" style="font-size: 11.5px; line-height: 1.35;">View administrative edited tasks logs</small>
        </div>
        <div class="audit-header-actions d-flex gap-2">
            @if(auth()->user()->isSuperAdmin())
                <button type="button" class="btn mb-2 btn-outline-danger btn-sm px-3 py-1.5 fw-semibold shadow-xs d-inline-flex align-items-center" onclick="openCleanupModal()">
                    <i class="fas fa-broom me-2"></i>
                    <span>Clear Logs</span>
                </button>
            @endif
            <button type="button" class="btn mb-2 btn-light btn-sm px-3 py-1.5 border text-muted fw-semibold shadow-xs d-inline-flex align-items-center" onclick="triggerFilterAjax(true)">
                <i class="fas fa-rotate me-2"></i>
                <span>Refresh</span>
            </button>
        </div>
    </div>

    <!-- Alert Box (Dynamic AJAX Messages) -->
    <div id="ajaxAlertBox" class="alert d-none alert-dismissible fade show mb-3 small py-2 px-3 border-0 shadow-xs align-items-center gap-2" role="alert">
        <i id="ajaxAlertIcon" class="fas fa-circle-check fs-6 flex-shrink-0 me-1"></i>
        <div id="ajaxAlertMessage" class="fw-semibold"></div>
        <button type="button" class="btn-close btn-sm shadow-none ms-auto py-2" onclick="document.getElementById('ajaxAlertBox').classList.add('d-none')"></button>
    </div>

    <!-- Multi-Filter Toolbar Card -->
    <div class="filter-card-box">
        <form id="auditFilterForm" onsubmit="event.preventDefault(); triggerFilterAjax();">
            <div class="row g-2 align-items-center">
                <!-- Search Box -->
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm overflow-hidden border shadow-xs">
                        <span class="input-group-text bg-white border-0 text-muted ps-2.5"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                        <input type="text" name="search" id="filter_search" class="form-control border-0 py-1.5 shadow-none" 
                               value="{{ request('search') }}" placeholder="User, ref, invoice, IP..." autocomplete="off">
                    </div>
                </div>

                <!-- Module Filter -->
                <div class="col-6 col-sm-3 col-md-2">
                    <select name="module" id="filter_module" class="form-select form-select-sm py-1.5 shadow-none">
                        <option value="all">All Modules</option>
                        @foreach($modules as $m)
                            <option value="{{ $m }}" {{ request('module') === $m ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $m)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Filter -->
                <div class="col-6 col-sm-3 col-md-2">
                    <select name="action_type" id="filter_action" class="form-select form-select-sm py-1.5 shadow-none">
                        <option value="all">All Actions</option>
                        @foreach($actions as $a)
                            <option value="{{ $a }}" {{ request('action_type') === $a ? 'selected' : '' }}>
                                {{ strtoupper($a) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Role Filter -->
                <div class="col-6 col-sm-3 col-md-2">
                    <select name="role" id="filter_role" class="form-select form-select-sm py-1.5 shadow-none">
                        <option value="all">All Roles</option>
                        @foreach($roles as $r)
                            <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>
                                {{ ucwords(str_replace(['_', '-'], ' ', $r)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Filter -->
                <div class="col-6 col-sm-3 col-md-2">
                    <input type="date" name="date" id="filter_date" class="form-control form-control-sm py-1.5 shadow-none bn-digit" value="{{ request('date') }}">
                </div>

                <!-- Submit & Reset -->
                <div class="col-12 col-md-1 d-flex gap-1.5 justify-content-end">
                    <button type="submit" class="btn btn-primary btn-sm px-2.5 py-1.5 w-100 fw-semibold shadow-xs" style="font-size: 12px;">
                        Filter
                    </button>
                    <button type="button" class="btn-light ms-2 btn-sm border px-2.5 py-1.5 text-muted shadow-xs" onclick="resetFilterForm()" title="Reset Filters">
                        <i class="fas fa-rotate-left"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Data Table Card with Loading Overlay -->
    <div class="audit-card-table overflow-hidden">
        <!-- AJAX Overlay -->
        <div id="auditLoadingOverlay">
            <div class="spinner-border text-primary" role="status" style="width: 2.2rem; height: 2.2rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table custom-audit-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="min-width: 170px;">Staff / Performer</th>
                        <th style="width: 110px;">Action</th>
                        <th style="width: 130px;">Module</th>
                        <th style="min-width: 130px;">Record Ref</th>
                        <th style="min-width: 250px;">Activity Description</th>
                        <th style="min-width: 140px;">Device & IP</th>
                        <th style="width: 140px;">Timestamp</th>
                        <th class="text-end" style="width: 110px;">Action / Snapshot</th>
                    </tr>
                </thead>
                <tbody id="auditTableBody">
                    @include('admin.activity_logs.partials.table_rows', ['logs' => $logs])
                </tbody>
            </table>
        </div>

        <!-- Pagination Container (AJAX Driven) -->
        <div class="p-2.5 p-md-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50" id="auditPaginationWrapper">
            <small class="text-muted bn-digit" id="auditStatsText" style="font-size: 11.5px;">
                Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{$logs->total() }} audit entries
            </small>
            <div id="auditPaginationLinks">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Diff / JSON Snapshot Modal -->
<div class="modal fade" id="logDiffModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg p-3">
            <div class="d-flex justify-content-between align-items-center mb-2.5 pb-2 border-bottom">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center" style="font-size: 14px;">
                    <i class="fas fa-code-compare text-primary me-2"></i>
                    <span>Audit Data Snapshot Comparison</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="p-2.5 bg-light rounded-2 border mb-3 small d-flex flex-wrap gap-3 justify-content-between" style="font-size: 12px;">
                <div><span class="text-muted">Staff:</span> <strong id="modal_diff_user" class="text-dark ms-1"></strong></div>
                <div><span class="text-muted">Action:</span> <strong id="modal_diff_action" class="text-primary ms-1"></strong></div>
                <div><span class="text-muted">Module:</span> <strong id="modal_diff_module" class="text-secondary ms-1"></strong></div>
                <div><span class="text-muted">Reference:</span> <strong id="modal_diff_ref" class="text-danger font-monospace bn-digit ms-1"></strong></div>
            </div>

            <div class="row g-2.5 mb-3">
                <div class="col-12 col-md-6">
                    <span class="small fw-bold text-danger d-flex align-items-center mb-2" style="font-size: 11.5px;">
                        <i class="fas fa-clock-rotate-left me-2"></i>
                        <span>Prior State (Old Values)</span>
                    </span>
                    <pre class="diff-box" id="modal_diff_old">None</pre>
                </div>
                <div class="col-12 col-md-6">
                    <span class="small fw-bold text-success d-flex align-items-center mb-2" style="font-size: 11.5px;">
                        <i class="fas fa-check-double me-2"></i>
                        <span>Modified State (New Values)</span>
                    </span>
                    <pre class="diff-box" id="modal_diff_new">None</pre>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-2.5 border-top">
                @if(auth()->user()->isSuperAdmin())
                    <button type="button" class="btn btn-sm btn-outline-danger px-3 py-1 fw-semibold shadow-xs d-inline-flex align-items-center" id="btnModalDeleteLog" style="font-size: 12px;" onclick="deleteCurrentModalLog()">
                        <i class="fas fa-trash-can me-2"></i>
                        <span>Delete This Record</span>
                    </button>
                @else
                    <div></div>
                @endif
                <button type="button" class="btn btn-sm btn-primary px-4 py-1 fw-semibold shadow-xs" style="font-size: 12px;" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Advanced Super Admin Cleanup Modal -->
<div class="modal fade" id="cleanupModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg p-3">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                <h6 class="fw-bold text-danger mb-0 d-flex align-items-center" style="font-size: 14px;">
                    <i class="fas fa-broom me-2"></i>
                    <span>Super Admin Log Cleanup & Purge</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <form id="logCleanupForm" onsubmit="event.preventDefault(); executeLogCleanup();">
                <!-- Cleanup Mode Selection Radio Pills -->
                <label class="form-label small fw-bold text-dark mb-2" style="font-size: 11.5px;">Select Purge / Cleanup Mode:</label>
                <div class="d-flex flex-wrap gap-1.5 mb-3 cleanup-radio-group">
                    <div class="form-check form-check-inline mb-1">
                        <input class="form-check-input" type="radio" name="cleanup_type" id="type_days" value="days" checked onchange="switchCleanupMode('days')">
                        <label class="form-check-label small fw-semibold" for="type_days" style="font-size: 11.5px; cursor: pointer;">Retention Days</label>
                    </div>
                    <div class="form-check form-check-inline mb-1">
                        <input class="form-check-input" type="radio" name="cleanup_type" id="type_single_id" value="single_id" onchange="switchCleanupMode('single_id')">
                        <label class="form-check-label small fw-semibold text-primary" for="type_single_id" style="font-size: 11.5px; cursor: pointer;">Specific Log ID (#)</label>
                    </div>
                    <div class="form-check form-check-inline mb-1">
                        <input class="form-check-input" type="radio" name="cleanup_type" id="type_date" value="single_date" onchange="switchCleanupMode('single_date')">
                        <label class="form-check-label small fw-semibold" for="type_date" style="font-size: 11.5px; cursor: pointer;">Specific Single Date</label>
                    </div>
                    <div class="form-check form-check-inline mb-1">
                        <input class="form-check-input" type="radio" name="cleanup_type" id="type_range" value="date_range" onchange="switchCleanupMode('date_range')">
                        <label class="form-check-label small fw-semibold" for="type_range" style="font-size: 11.5px; cursor: pointer;">Date Range</label>
                    </div>
                    <div class="form-check form-check-inline mb-1">
                        <input class="form-check-input" type="radio" name="cleanup_type" id="type_all" value="all" onchange="switchCleanupMode('all')">
                        <label class="form-check-label small fw-semibold text-danger" for="type_all" style="font-size: 11.5px; cursor: pointer;">Purge All Logs</label>
                    </div>
                </div>

                <!-- Panel 1: Retention Days -->
                <div id="panel_days" class="cleanup-mode-panel mb-3">
                    <label class="form-label small fw-bold text-muted mb-1" style="font-size: 11.5px;">Purge Logs Older Than:</label>
                    <select name="days" id="cleanupDaysSelect" class="form-select form-select-sm fw-semibold">
                        <option value="7">Older than 7 Days</option>
                        <option value="15">Older than 15 Days</option>
                        <option value="30">Older than 30 Days</option>
                        <option value="60">Older than 60 Days</option>
                        <option value="90" selected>Older than 90 Days</option>
                        <option value="180">Older than 180 Days</option>
                        <option value="365">Older than 1 Year</option>
                    </select>
                </div>

                <!-- Panel 2: Specific Single Log ID (#) -->
                <div id="panel_single_id" class="cleanup-mode-panel mb-3 d-none">
                    <label class="form-label small fw-bold text-muted mb-1" style="font-size: 11.5px;">Enter Log Record ID (#):</label>
                    <input type="number" name="log_id" id="cleanupLogIdInput" class="form-control form-control-sm font-monospace bn-digit" placeholder=" 1, 5, 25">
                    <small class="text-muted d-block mt-1" style="font-size: 11px;">Only this specific audit log entry will be permanently deleted.</small>
                </div>

                <!-- Panel 3: Specific Single Date -->
                <div id="panel_single_date" class="cleanup-mode-panel mb-3 d-none">
                    <label class="form-label small fw-bold text-muted mb-1" style="font-size: 11.5px;">Select Single Date to Clear:</label>
                    <input type="date" name="specific_date" id="cleanupSpecificDate" class="form-control form-control-sm bn-digit" value="{{ date('Y-m-d') }}">
                    <small class="text-muted d-block mt-1" style="font-size: 11px;">All logs recorded on this specific day will be permanently deleted.</small>
                </div>

                <!-- Panel 4: Date Range -->
                <div id="panel_date_range" class="cleanup-mode-panel mb-3 d-none">
                    <label class="form-label small fw-bold text-muted mb-1" style="font-size: 11.5px;">Select Date Range (From - To):</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="date" name="start_date" id="cleanupStartDate" class="form-control form-control-sm bn-digit" placeholder="From Date">
                        </div>
                        <div class="col-6">
                            <input type="date" name="end_date" id="cleanupEndDate" class="form-control form-control-sm bn-digit" placeholder="To Date">
                        </div>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 11px;">All logs within this date range will be permanently erased.</small>
                </div>

                <!-- Panel 5: Warning for Purge All -->
                <div id="panel_all" class="cleanup-mode-panel mb-3 d-none">
                    <div class="alert alert-danger py-2 px-3 mb-0 small border-0 bg-danger-subtle text-danger" style="font-size: 11.5px;">
                        <i class="fas fa-triangle-exclamation me-2"></i>
                        <strong>Critical Warning:</strong> This action will permanently wipe out <u>ALL</u> recorded activity and audit logs from the database.
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-2.5 border-top">
                    <button type="button" class="btn btn-light btn-sm px-3 py-1 border text-muted shadow-xs" style="font-size: 12px;" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3.5 py-1 fw-bold shadow-xs d-inline-flex align-items-center" id="btnExecuteCleanup" style="font-size: 12px;">
                        <i class="fas fa-trash-can me-2"></i>
                        <span>Execute Purge</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 3: Confirmation Dialog (Replaces browser confirm()) -->
<div class="modal fade" id="auditConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2 text-danger">
                    <i class="fas fa-triangle-exclamation fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="auditConfirmModalTitle" style="font-size: 14px;">Confirm Action</h6>
                <p class="text-muted small mb-3" id="auditConfirmModalMsg" style="font-size: 12px;">Are you sure you want to proceed?</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-sm px-3 py-1 border shadow-xs" data-bs-dismiss="modal" style="font-size: 12px;">Cancel</button>
                    <button type="button" class="btn btn-danger btn-sm px-3.5 py-1 fw-bold shadow-xs" id="btnAuditConfirmProceed" style="font-size: 12px;">Proceed</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let filterDebounceTimer = null;
    let currentAjaxUrl = "{{ route('admin.activity.logs') }}";
    let activeModalLogId = null;
    let pendingAuditConfirmCallback = null;

    function showAuditConfirmModal(title, message, onConfirm) {
        document.getElementById('auditConfirmModalTitle').innerText = title;
        document.getElementById('auditConfirmModalMsg').innerText = message;
        pendingAuditConfirmCallback = onConfirm;

        const modalEl = document.getElementById('auditConfirmModal');
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    document.getElementById('btnAuditConfirmProceed').addEventListener('click', function () {
        const modalEl = document.getElementById('auditConfirmModal');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();

        if (typeof pendingAuditConfirmCallback === 'function') {
            pendingAuditConfirmCallback();
            pendingAuditConfirmCallback = null;
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        // 1. Real-time debounced AJAX search filter (350ms)
        const searchInput = document.getElementById('filter_search');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(filterDebounceTimer);
                filterDebounceTimer = setTimeout(() => {
                    triggerFilterAjax();
                }, 350);
            });
        }

        // 2. Instant AJAX trigger on dropdown and date changes
        ['filter_module', 'filter_action', 'filter_role', 'filter_date'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('change', () => triggerFilterAjax());
            }
        });

        // 3. AJAX pagination click interceptor
        document.addEventListener('click', function (e) {
            const pageLink = e.target.closest('#auditPaginationLinks a');
            if (pageLink) {
                e.preventDefault();
                const url = pageLink.getAttribute('href');
                if (url && url !== '#') {
                    fetchLogsByUrl(url);
                }
            }
        });
    });

    // Execute AJAX filter query
    function triggerFilterAjax(resetPage = true) {
        const form = document.getElementById('auditFilterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        const baseUrl = "{{ route('admin.activity.logs') }}";
        const targetUrl = baseUrl + '?' + params.toString();

        fetchLogsByUrl(targetUrl);
    }

    // Fetch logs from specific URL via AJAX
    function fetchLogsByUrl(url) {
        currentAjaxUrl = url;
        const overlay = document.getElementById('auditLoadingOverlay');
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
                document.getElementById('auditTableBody').innerHTML = data.html;
                document.getElementById('auditPaginationLinks').innerHTML = data.pagination;
                document.getElementById('auditStatsText').innerText = 
                    `Showing ${data.firstItem} to ${data.lastItem} of ${data.total} audit entries`;

                // Update browser history state without page reload
                window.history.replaceState({}, '', url);
            }
        })
        .catch(err => {
            console.error('AJAX Log Fetch Error:', err);
        })
        .finally(() => {
            if (overlay) overlay.style.display = 'none';
        });
    }

    // Reset filter form controls
    function resetFilterForm() {
        document.getElementById('filter_search').value = '';
        document.getElementById('filter_module').value = 'all';
        document.getElementById('filter_action').value = 'all';
        document.getElementById('filter_role').value = 'all';
        document.getElementById('filter_date').value = '';
        triggerFilterAjax();
    }

    // Open Diff Snapshot Modal
    function viewLogDetail(logId) {
        activeModalLogId = logId;
        fetch("{{ url('admin/activity-logs/show') }}/" + logId, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                const d = res.data;
                document.getElementById('modal_diff_user').innerText = `${d.user_name} (${d.role})`;
                document.getElementById('modal_diff_action').innerText = d.action;
                document.getElementById('modal_diff_module').innerText = d.module;
                document.getElementById('modal_diff_ref').innerText = d.record_ref;

                document.getElementById('modal_diff_old').innerText = d.old_values ? JSON.stringify(d.old_values, null, 2) : 'No prior state recorded.';
                document.getElementById('modal_diff_new').innerText = d.new_values ? JSON.stringify(d.new_values, null, 2) : 'No new state recorded.';

                const modal = new bootstrap.Modal(document.getElementById('logDiffModal'));
                modal.show();
            } else {
                showAlertNotification(res.message || 'Failed to load details.', 'danger');
            }
        })
        .catch(err => showAlertNotification('Communication error with server.', 'danger'));
    }

    // Delete specific log from inside the Diff Modal
    function deleteCurrentModalLog() {
        if (!activeModalLogId) return;
        const modalEl = document.getElementById('logDiffModal');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();

        deleteSingleLog(activeModalLogId);
    }

    // Delete specific single log entry
    function deleteSingleLog(logId) {
        showAuditConfirmModal(
            'Delete Audit Log?',
            `Are you sure you want to permanently delete audit log record #${logId}?`,
            () => {
                const overlay = document.getElementById('auditLoadingOverlay');
                if (overlay) overlay.style.display = 'flex';

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

                fetch("{{ url('admin/activity-logs/destroy') }}/" + logId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showAlertNotification(data.message, 'success');
                        const rowEl = document.getElementById('log-row-' + logId);
                        if (rowEl) {
                            rowEl.style.transition = 'all 0.3s ease';
                            rowEl.style.opacity = '0';
                            setTimeout(() => rowEl.remove(), 300);
                        }
                        fetchLogsByUrl(currentAjaxUrl);
                    } else {
                        showAlertNotification(data.message || 'Failed to delete log entry.', 'danger');
                    }
                })
                .catch(err => {
                    console.error('Delete Log Error:', err);
                    showAlertNotification('Server communication error during log deletion.', 'danger');
                })
                .finally(() => {
                    if (overlay) overlay.style.display = 'none';
                });
            }
        );
    }

    // Open Cleanup Modal
    function openCleanupModal() {
        const modal = new bootstrap.Modal(document.getElementById('cleanupModal'));
        modal.show();
    }

    // Switch Cleanup Mode Panels
    function switchCleanupMode(mode) {
        document.querySelectorAll('.cleanup-mode-panel').forEach(p => p.classList.add('d-none'));

        if (mode === 'single_id') {
            document.getElementById('panel_single_id').classList.remove('d-none');
        } else if (mode === 'single_date') {
            document.getElementById('panel_single_date').classList.remove('d-none');
        } else if (mode === 'date_range') {
            document.getElementById('panel_date_range').classList.remove('d-none');
        } else if (mode === 'all') {
            document.getElementById('panel_all').classList.remove('d-none');
        } else {
            document.getElementById('panel_days').classList.remove('d-none');
        }
    }

    // Execute Log Cleanup via AJAX
    function executeLogCleanup() {
        const form = document.getElementById('logCleanupForm');
        const formData = new FormData(form);
        const cleanupType = formData.get('cleanup_type');

        const confirmTitle = cleanupType === 'all' ? 'Purge All Audit Logs?' : 'Confirm Log Cleanup';
        const confirmMsg = cleanupType === 'all'
            ? 'Are you absolutely sure you want to permanently delete ALL audit activity logs? This action cannot be reversed!'
            : 'Confirm purging selected activity log records?';

        showAuditConfirmModal(confirmTitle, confirmMsg, () => {
            const btn = document.getElementById('btnExecuteCleanup');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Purging...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            fetch("{{ route('admin.activity.cleanup') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(Object.fromEntries(formData))
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const modalEl = document.getElementById('cleanupModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();

                    showAlertNotification(data.message, 'success');
                    fetchLogsByUrl(currentAjaxUrl);
                } else {
                    showAlertNotification(data.message || 'Cleanup operation failed.', 'danger');
                }
            })
            .catch(err => {
                console.error(err);
                showAlertNotification('Failed to execute log cleanup.', 'danger');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });
    }

    // Display dynamic alert notification
    function showAlertNotification(msg, type = 'success') {
        const box = document.getElementById('ajaxAlertBox');
        const msgEl = document.getElementById('ajaxAlertMessage');
        const icon = document.getElementById('ajaxAlertIcon');

        if (box && msgEl) {
            box.className = `alert alert-${type} alert-dismissible fade show mb-3 small py-2 px-3 border-0 shadow-xs d-flex align-items-center gap-2`;
            icon.className = type === 'success' ? 'fas fa-circle-check fs-6 text-success flex-shrink-0 me-1' : 'fas fa-circle-exclamation fs-6 text-danger flex-shrink-0 me-1';
            msgEl.innerText = msg;
            box.classList.remove('d-none');

            setTimeout(() => {
                box.classList.add('d-none');
            }, 5000);
        }
    }
</script>
@endsection