
@extends('layouts.admin')

@section('title', 'Trash - Users & Staff - ' . config('app.name'))

@section('admin_content')

<!-- Pure Text Data Table PDF Generator (jsPDF + AutoTable) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<style>
    /* =========================================================
       TRASHED USERS & STAFF
       KAIADMIN LITE v1.2.0 - CLASSIC / WHITE CLASSIC
       Page-specific styling
    ========================================================= */

    #trashIndexRoot {
        --trash-primary: var(--bs-primary, #1572e8);
        --trash-danger: var(--bs-danger, #f25961);
        --trash-success: var(--bs-success, #31ce36);
        --trash-text: #1a2035;
        --trash-muted: #7b8190;
        --trash-border: #e5e7eb;
        --trash-surface: #ffffff;
        --trash-soft: #f8f9fc;
        color: var(--trash-text);
        font-size: 13px;
    }

    /* Page container */
    #trashIndexRoot.trash-page-container {
        padding: 18px 20px;
    }

    /* Consistent element radius */
    #trashIndexRoot .btn,
    #trashIndexRoot button,
    #trashIndexRoot .form-control,
    #trashIndexRoot .form-select,
    #trashIndexRoot .badge,
    #trashIndexRoot .input-group-text,
    #trashIndexRoot .page-link,
    #trashIndexRoot .page-item span {
        border-radius: 5px !important;
    }

    /* Header */
    #trashIndexRoot .trash-page-header {
        padding-bottom: 15px;
        border-bottom: 1px solid var(--trash-border);
    }

    #trashIndexRoot .trash-page-title {
        color: var(--trash-text);
        font-size: 19px;
        font-weight: 700;
        line-height: 1.4;
        letter-spacing: -.25px;
    }

    #trashIndexRoot .trash-page-subtitle {
        display: block;
        margin-top: 4px;
        color: var(--trash-muted);
        font-size: 11.5px;
        line-height: 1.5;
    }

    /* Header actions */
    #trashIndexRoot .trash-top-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }

    #trashIndexRoot .trash-top-actions .btn {
        min-height: 34px;
        padding: 7px 12px !important;
        font-size: 11.5px;
        font-weight: 600;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #trashIndexRoot .trash-top-actions .btn:hover {
        transform: translateY(-1px);
    }

    /* Main cards */
    #trashIndexRoot .filter-card-box,
    #trashIndexRoot .trash-card-table {
        position: relative;
        background: var(--trash-surface);
        border: 1px solid var(--trash-border);
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035);
    }

    #trashIndexRoot .filter-card-box {
        padding: 17px 18px;
        margin-bottom: 18px;
    }

    #trashIndexRoot .trash-card-table {
        margin-bottom: 20px;
    }

    /* Filter controls */
    #trashIndexRoot .form-control,
    #trashIndexRoot .form-select {
        min-height: 34px;
        border-color: #e4e7ec;
        background-color: #fff;
        color: #344054;
        font-size: 12px;
        box-shadow: none;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    #trashIndexRoot .form-control:focus,
    #trashIndexRoot .form-select:focus {
        border-color: var(--trash-primary);
        box-shadow: 0 0 0 2px rgba(21, 114, 232, .09);
    }

    #trashIndexRoot .form-control::placeholder {
        color: #a0a5b1;
        opacity: 1;
    }

    #trashIndexRoot .input-group {
        border: 1px solid #e4e7ec !important;
        border-radius: 5px !important;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    #trashIndexRoot .input-group:focus-within {
        border-color: var(--trash-primary) !important;
        box-shadow: 0 0 0 2px rgba(21, 114, 232, .09);
    }

    #trashIndexRoot .input-group .form-control {
        min-width: 0;
    }

    #trashIndexRoot .input-group-text {
        min-width: 34px;
        justify-content: center;
        color: #8b909b;
    }

    #trashIndexRoot .filter-actions {
        padding-top: 12px;
        border-top: 1px solid #edf0f3;
    }

    #trashIndexRoot .filter-actions .btn {
        min-height: 34px;
        font-size: 12px;
        font-weight: 600;
    }

    /* Alert */
    #trashIndexRoot #trashAjaxAlertBox {
        border-radius: 6px !important;
        font-size: 12px;
    }

    /* Table */
    #trashIndexRoot .custom-trash-table {
        width: 100%;
        min-width: 760px;
        margin-bottom: 0;
        vertical-align: middle;
    }

    #trashIndexRoot .custom-trash-table thead th {
        padding: 12px 14px;
        background: #f8f9fc;
        color: #7b8190;
        border-top: 0;
        border-bottom: 1px solid var(--trash-border);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .45px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    #trashIndexRoot .custom-trash-table tbody td {
        padding: 11px 14px;
        color: #4b5563;
        border-bottom: 1px solid #f0f1f4;
        font-size: 12px;
        line-height: 1.5;
        vertical-align: middle;
    }

    #trashIndexRoot .custom-trash-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #trashIndexRoot .custom-trash-table tbody tr:hover {
        background: #fafbfe;
    }

    #trashIndexRoot .custom-trash-table .badge {
        font-size: 10px;
        font-weight: 700;
    }

    /* Table responsive */
    #trashIndexRoot .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* Pagination area */
    #trashIndexRoot #trashPaginationWrapper {
        padding: 12px 16px !important;
        background: #fbfcfe !important;
        border-top: 1px solid var(--trash-border) !important;
    }

    #trashIndexRoot #trashStatsText {
        color: var(--trash-muted);
        font-size: 11.5px;
        font-weight: 500;
    }

    #trashIndexRoot #trashPaginationLinks {
        max-width: 100%;
    }

    #trashIndexRoot #trashPaginationLinks .pagination {
        margin-bottom: 0;
        flex-wrap: wrap;
        gap: 3px;
    }

    #trashIndexRoot #trashPaginationLinks .page-link {
        min-width: 30px;
        min-height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 9px;
        color: #6b7280;
        border-color: #e5e7eb;
        font-size: 11px;
        box-shadow: none;
    }

    #trashIndexRoot #trashPaginationLinks .page-item.active .page-link {
        color: #fff;
        background: var(--trash-primary);
        border-color: var(--trash-primary);
    }

    #trashIndexRoot #trashPaginationLinks .page-item.disabled .page-link {
        color: #b4b8c1;
        background: #f8f9fc;
    }

    /* Loading overlay */
    #trashIndexRoot #trashLoadingOverlay {
        position: absolute;
        inset: 0;
        z-index: 25;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, .82);
        backdrop-filter: blur(2px);
        border-radius: 8px;
    }

    #trashIndexRoot #trashLoadingOverlay .spinner-border {
        width: 2.2rem;
        height: 2.2rem;
    }

    /* Numeric data */
    #trashIndexRoot .bn-digit,
    #trashIndexRoot .font-monospace {
        font-weight: 600;
    }

    /* Modal styling */
    #deleteConfirmDialog .modal-content,
    #trashNoticeModal .modal-content {
        border: 1px solid #e5e7eb;
        border-radius: 9px !important;
        box-shadow: 0 12px 35px rgba(15, 23, 42, .14);
    }

    #deleteConfirmDialog .modal-body,
    #trashNoticeModal .modal-body {
        padding: 8px 5px;
    }

    #deleteConfirmDialog .modal-body h6,
    #trashNoticeModal .modal-body h6 {
        color: #1a2035;
        font-size: 14px;
        font-weight: 700;
    }

    #deleteConfirmDialog .modal-body p,
    #trashNoticeModal .modal-body p {
        color: #7b8190;
        font-size: 12px;
        line-height: 1.6;
    }

    #deleteConfirmDialog .btn,
    #trashNoticeModal .btn {
        min-height: 32px;
        font-size: 11.5px;
        font-weight: 600;
        border-radius: 5px !important;
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        #trashIndexRoot.trash-page-container {
            padding: 12px 10px;
        }

        #trashIndexRoot .trash-page-header {
            align-items: stretch !important;
            padding-bottom: 12px;
        }

        #trashIndexRoot .trash-page-title {
            font-size: 17px;
        }

        #trashIndexRoot .trash-page-subtitle {
            font-size: 11px;
        }

        #trashIndexRoot .trash-top-actions {
            width: 100%;
            display: flex;
            gap: 6px;
        }

        #trashIndexRoot .trash-top-actions .btn {
            flex: 1 1 calc(50% - 6px);
            justify-content: center;
            padding: 8px 7px !important;
            font-size: 11px;
        }

        #trashIndexRoot .trash-top-actions .btn-back-users {
            flex: 1 1 100%;
        }

        #trashIndexRoot .filter-card-box {
            padding: 13px 12px;
            margin-bottom: 14px;
        }

        #trashIndexRoot .filter-actions {
            margin-top: 4px;
        }

        #trashIndexRoot .custom-trash-table thead th,
        #trashIndexRoot .custom-trash-table tbody td {
            padding: 9px 10px;
            font-size: 11.5px;
        }

        #trashIndexRoot #trashPaginationWrapper {
            align-items: flex-start !important;
            padding: 12px !important;
        }

        #trashIndexRoot #trashPaginationLinks {
            width: 100%;
            overflow-x: auto;
            padding-bottom: 2px;
        }

        #trashIndexRoot #trashPaginationLinks .pagination {
            flex-wrap: nowrap;
            width: max-content;
        }
    }

    @media (max-width: 400px) {
        #trashIndexRoot .trash-top-actions .btn {
            font-size: 10.5px;
        }

        #trashIndexRoot .filter-card-box {
            padding: 11px 10px;
        }
    }
</style>

<div class="container-fluid trash-page-container position-relative" id="trashIndexRoot">

    <!-- Page Header & Actions -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-3 trash-page-header">

        <div>
            <h5 class="trash-page-title mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-trash-alt text-danger"></i>
                <span>Trashed Users & Staff</span>
            </h5>

            <small class="trash-page-subtitle">
              Restore deleted accounts or permanently delete them.
            </small>
        </div>

        <div class="trash-top-actions no-pdf">

            <button type="button"
                    class="btn btn-outline-success btn-sm shadow-sm d-inline-flex align-items-center"
                    onclick="exportTrashReport('excel')">
                <i class="fas fa-file-excel text-success me-2"></i>
                <span>Export Excel</span>
            </button>

            <button type="button"
                    class="btn btn-outline-danger btn-sm shadow-sm d-inline-flex align-items-center"
                    id="btn-export-trash-pdf"
                    onclick="downloadTrashPdf()">
                <i class="fas fa-file-pdf text-danger me-2"></i>
                <span id="trash-pdf-text">Report PDF</span>
            </button>

            <a href="{{ route('admin.roles.users') }}"
               class="btn btn-outline-secondary btn-sm shadow-sm d-inline-flex align-items-center btn-back-users">
                <i class="fas fa-arrow-left me-2"></i>
                <span>Back to Users</span>
            </a>

        </div>
    </div>

    <!-- AJAX Alert -->
    <div id="trashAjaxAlertBox"
         class="alert d-none alert-dismissible fade show mb-3 small align-items-center gap-2 shadow-sm border-0 py-2 px-3"
         role="alert">

        <i id="trashAjaxAlertIcon" class="fas fa-circle-check fs-6 flex-shrink-0"></i>

        <div id="trashAjaxAlertMsg" class="fw-semibold"></div>

        <button type="button"
                class="btn-close btn-sm shadow-none ms-auto py-2"
                onclick="document.getElementById('trashAjaxAlertBox').classList.add('d-none')">
        </button>
    </div>

    <!-- Filter Toolbar -->
    <div class="filter-card-box no-pdf">

        <form id="trashFilterForm" onsubmit="event.preventDefault(); triggerTrashFilterAjax();">

            <div class="row g-2 align-items-center">

                <!-- Search -->
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm overflow-hidden">

                        <span class="input-group-text bg-white border-0">
                            <i class="fas fa-search" style="font-size: 11px;"></i>
                        </span>

                        <input type="text"
                               name="search"
                               id="trashFilterSearch"
                               class="form-control border-0 py-1 shadow-none"
                               value="{{ request('search') }}"
                               placeholder="Search name, email, phone..."
                               autocomplete="off">
                    </div>
                </div>

                <!-- Role -->
                <div class="col-6 col-sm-3 col-md-2">
                    <select name="role"
                            id="trashFilterRole"
                            class="form-select form-select-sm py-1 shadow-none">

                        <option value="all">All Roles</option>

                        @foreach($allRoles as $rName)
                            <option value="{{ $rName }}" {{ request('role') == $rName ? 'selected' : '' }}>
                                {{ ucwords(str_replace(['_', '-'], ' ', $rName)) }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <!-- Specific Deleted Date -->
                <div class="col-6 col-sm-3 col-md-2">
                    <input type="date"
                           name="deleted_date"
                           id="trashFilterDate"
                           class="form-control form-control-sm py-1 shadow-none"
                           value="{{ request('deleted_date') }}"
                           title="Specific Deleted Date">
                </div>

                <!-- Deleted From -->
                <div class="col-6 col-sm-3 col-md-2">
                    <input type="date"
                           name="deleted_from"
                           id="trashFilterFrom"
                           class="form-control form-control-sm py-1 shadow-none"
                           placeholder="Deleted From"
                           title="Deleted Range From">
                </div>

                <!-- Deleted To -->
                <div class="col-6 col-sm-3 col-md-2">
                    <input type="date"
                           name="deleted_to"
                           id="trashFilterTo"
                           class="form-control form-control-sm py-1 shadow-none"
                           placeholder="Deleted To"
                           title="Deleted Range To">
                </div>

                <!-- Per Page -->
                <div class="col-6 col-sm-3 col-md-1">
                    <select name="per_page"
                            id="trashFilterPerPage"
                            class="form-select form-select-sm py-1 shadow-none fw-semibold">

                        <option value="20" {{ request('per_page', '20') == '20' ? 'selected' : '' }}>20 / page</option>
                        <option value="30" {{ request('per_page', '20') == '30' ? 'selected' : '' }}>30 / page</option>
                        <option value="50" {{ request('per_page', '20') == '50' ? 'selected' : '' }}>50 / page</option>
                        <option value="100" {{ request('per_page', '20') == '100' ? 'selected' : '' }}>100 / page</option>
                        <option value="200" {{ request('per_page', '20') == '200' ? 'selected' : '' }}>200 / page</option>
                        <option value="500" {{ request('per_page', '20') == '500' ? 'selected' : '' }}>500 / page</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>

                    </select>
                </div>

                <!-- Filter Actions -->
                <div class="col-12 d-flex gap-2 justify-content-end mt-2 filter-actions">

                    <button type="submit"
                            class="btn btn-primary btn-sm px-4 flex-fill fw-semibold shadow-sm">
                        <i class="fas fa-filter me-1"></i>
                        Filter
                    </button>

                    <button type="button"
                            class="btn btn-light btn-sm border px-4 flex-fill text-muted shadow-sm d-inline-flex align-items-center justify-content-center gap-2"
                            onclick="resetTrashFilters()"
                            title="Reset Filters">
                        <i class="fas fa-rotate-left"></i>
                        <span>Reset</span>
                    </button>

                </div>

            </div>
        </form>
    </div>

    <!-- Trash Table Card -->
    <div class="trash-card-table overflow-hidden" id="trash-render-area">

        <!-- AJAX Loading Overlay -->
        <div id="trashLoadingOverlay">
            <div class="spinner-border text-danger" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div class="table-responsive">

            <table class="table custom-trash-table align-middle mb-0" id="trash-table">

                <thead>
                    <tr>
                        <th style="width: 45px;" class="ps-3">#</th>
                        <th style="min-width: 200px;">User Details</th>
                        <th style="min-width: 140px;">Role</th>
                        <th style="min-width: 140px;">Deleted Timestamp</th>
                        <th class="text-end pe-3" style="width: 170px;">Actions</th>
                    </tr>
                </thead>

                <tbody id="trashTableBody">
                    @include('admin.users.partials.trash_rows', ['trashedUsers' => $trashedUsers])
                </tbody>

            </table>
        </div>

        <!-- Pagination & Statistics -->
        <div class="p-2 p-md-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50"
             id="trashPaginationWrapper">

            <small class="text-muted bn-digit" id="trashStatsText">
                Showing {{ $trashedUsers->firstItem() ?? 0 }} to {{ $trashedUsers->lastItem() ?? 0 }} of {{$trashedUsers->total() }} trashed user(s)
            </small>

            <div id="trashPaginationLinks">
                {{ $trashedUsers->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
</div>

<!-- Modal 1: Permanent Delete Confirmation -->
<div class="modal fade"
     id="deleteConfirmDialog"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 106000;">

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content shadow-lg text-center p-3">

            <div class="modal-body p-1">

                <div class="text-danger mb-3">
                    <i class="fas fa-triangle-exclamation fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-2">
                    Permanent Delete?
                </h6>

                <p class="text-muted small mb-3" id="deleteDialogUserText"></p>

                <div class="d-flex justify-content-center gap-2">

                    <button type="button"
                            class="btn btn-sm btn-light px-3 py-1 border shadow-sm"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="button"
                            class="btn btn-sm btn-danger px-3 py-1 fw-bold shadow-sm"
                            id="runDeleteBtn"
                            onclick="runForceDelete()">
                        Delete Forever
                    </button>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: General Notice / Alert Dialog -->
<div class="modal fade"
     id="trashNoticeModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 106500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content shadow-lg text-center p-3">

            <div class="modal-body p-1">

                <div class="mb-3" id="trashNoticeModalIcon">
                    <i class="fas fa-circle-exclamation text-warning fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-2"
                    id="trashNoticeModalTitle">
                    Notice
                </h6>

                <p class="text-muted small mb-3"
                   id="trashNoticeModalMsg">
                    Notice message.
                </p>

                <button type="button"
                        class="btn btn-primary btn-sm px-4 py-1 fw-semibold shadow-sm"
                        data-bs-dismiss="modal">
                    Acknowledge
                </button>

            </div>
        </div>
    </div>
</div>

<script>
    const CSRF = "{{ csrf_token() }}";
    let targetDeleteId = null;
    let dialogModal = null;
    let trashDebounceTimer = null;
    let currentTrashAjaxUrl = "{{ route('admin.roles.trash') }}";

    function showTrashNoticeModal(message, type = 'warning', title = 'Notice') {
        const modalEl = document.getElementById('trashNoticeModal');
        const titleEl = document.getElementById('trashNoticeModalTitle');
        const msgEl = document.getElementById('trashNoticeModalMsg');
        const iconEl = document.getElementById('trashNoticeModalIcon');

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

    document.addEventListener('DOMContentLoaded', function() {
        dialogModal = new bootstrap.Modal(document.getElementById('deleteConfirmDialog'));

        const searchInput = document.getElementById('trashFilterSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(trashDebounceTimer);
                trashDebounceTimer = setTimeout(() => {
                    triggerTrashFilterAjax();
                }, 350);
            });
        }

        ['trashFilterRole', 'trashFilterDate', 'trashFilterFrom', 'trashFilterTo', 'trashFilterPerPage'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('change', () => triggerTrashFilterAjax());
            }
        });

        document.addEventListener('click', function (e) {
            const pageLink = e.target.closest('#trashPaginationLinks a');
            if (pageLink) {
                e.preventDefault();
                const url = pageLink.getAttribute('href');
                if (url && url !== '#') {
                    fetchTrashByUrl(url);
                }
            }
        });
    });

    function triggerTrashFilterAjax() {
        const form = document.getElementById('trashFilterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        const baseUrl = "{{ route('admin.roles.trash') }}";
        const targetUrl = baseUrl + '?' + params.toString();

        fetchTrashByUrl(targetUrl);
    }

    function fetchTrashByUrl(url) {
        currentTrashAjaxUrl = url;
        const overlay = document.getElementById('trashLoadingOverlay');
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
                document.getElementById('trashTableBody').innerHTML = data.html;
                document.getElementById('trashPaginationLinks').innerHTML = data.pagination;
                document.getElementById('trashStatsText').innerText =
                    `Showing ${data.firstItem} to ${data.lastItem} of ${data.total} trashed user(s)`;

                window.history.replaceState({}, '', url);
            }
        })
        .catch(err => {
            console.error('AJAX Trash Fetch Error:', err);
        })
        .finally(() => {
            if (overlay) overlay.style.display = 'none';
        });
    }

    function resetTrashFilters() {
        document.getElementById('trashFilterSearch').value = '';
        document.getElementById('trashFilterRole').value = 'all';
        document.getElementById('trashFilterDate').value = '';
        document.getElementById('trashFilterFrom').value = '';
        document.getElementById('trashFilterTo').value = '';
        document.getElementById('trashFilterPerPage').value = '20';

        triggerTrashFilterAjax();
    }

    // 1. Direct Excel Download
    function exportTrashReport(type) {
        const form = document.getElementById('trashFilterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.append('export', type);
        window.location.href = "{{ route('admin.roles.trash') }}?" + params.toString();
    }

    // 2. Pure Text Data Table PDF Generator
    function downloadTrashPdf() {
        const btn = document.getElementById('btn-export-trash-pdf');
        const btnText = document.getElementById('trash-pdf-text');
        const origText = btnText.innerText;

        btn.disabled = true;
        btnText.innerText = 'Preparing PDF...';

        const form = document.getElementById('trashFilterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.append('export', 'pdf_data');

        fetch("{{ route('admin.roles.trash') }}?" + params.toString(), {
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

            doc.setFont('helvetica', 'bold');
            doc.setFontSize(14);
            doc.setTextColor(15, 23, 42);
            doc.text(data.title || 'Trashed Users & Staff Report', 14, 14);

            doc.setFont('helvetica', 'normal');
            doc.setFontSize(9);
            doc.setTextColor(100, 116, 139);
            doc.text(`Generated At: ${data.generated_at}   |   Total Trashed Users: ${data.total}`, 14, 20);

            const tableBody = data.records.map(r => [
                r.sl,
                '#' + r.id,
                r.name,
                r.role,
                r.branch,
                r.phone,
                r.email,
                r.deleted_at
            ]);

            doc.autoTable({
                startY: 25,
                head: [[
                    'SL',
                    'User ID',
                    'Full Name',
                    'Role',
                    'Branch Hub',
                    'Phone Number',
                    'Email Address',
                    'Deleted Date & Time'
                ]],
                body: tableBody,
                theme: 'grid',
                styles: {
                    font: 'helvetica',
                    fontSize: 8.5,
                    cellPadding: 2.5,
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
                    0: { cellWidth: 12, halign: 'center' },
                    1: { cellWidth: 18, halign: 'center' },
                    2: { cellWidth: 45, fontStyle: 'bold' },
                    3: { cellWidth: 32 },
                    4: { cellWidth: 38 },
                    5: { cellWidth: 32 },
                    6: { cellWidth: 60 },
                    7: { cellWidth: 38, halign: 'center' }
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

            const fileName = 'Trashed_Users_Report_' + (new Date().toISOString().slice(0, 10)) + '.pdf';
            doc.save(fileName);
        })
        .catch(err => {
            console.error('PDF error:', err);
            showTrashNoticeModal('Failed to generate PDF data table. Please try again.', 'danger', 'PDF Export Error');
        })
        .finally(() => {
            btn.disabled = false;
            btnText.innerText = origText;
        });
    }

    function openDeleteDialog(id, name) {
        targetDeleteId = id;
        document.getElementById('deleteDialogUserText').textContent = `Permanently delete "${name}"? This action cannot be undone!`;
        if (dialogModal) dialogModal.show();
    }

    function runForceDelete() {
        if (!targetDeleteId) return;

        const btn = document.getElementById('runDeleteBtn');
        btn.disabled = true;
        btn.textContent = 'Deleting...';

        fetch("{{ url('admin/user-roles/trash/force-delete') }}/" + targetDeleteId, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF
            }
        })
        .then(res => res.json())
        .then(data => {
            if (dialogModal) dialogModal.hide();
            btn.disabled = false;
            btn.textContent = 'Delete Forever';

            if (data.success) {
                showTrashAlert(data.message, 'success');
                fetchTrashByUrl(currentTrashAjaxUrl);
            } else {
                showTrashAlert(data.message || 'Deletion failed.', 'danger');
            }
        })
        .catch(err => {
            if (dialogModal) dialogModal.hide();
            btn.disabled = false;
            btn.textContent = 'Delete Forever';
            showTrashAlert('Server communication error.', 'danger');
        });
    }

    function executeRestoreAction(id) {
        fetch("{{ url('admin/user-roles/trash/restore') }}/" + id, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showTrashAlert(data.message, 'success');
                fetchTrashByUrl(currentTrashAjaxUrl);
            }
        })
        .catch(err => console.error(err));
    }

    function showTrashAlert(msg, type = 'success') {
        const box = document.getElementById('trashAjaxAlertBox');
        const msgEl = document.getElementById('trashAjaxAlertMsg');
        const icon = document.getElementById('trashAjaxAlertIcon');

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
