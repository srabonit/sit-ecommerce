@extends('layouts.admin')

@section('title', 'Trash - Users & Staff - ' . config('app.name'))

@section('admin_content')
<!-- Pure Text Data Table PDF Generator (jsPDF + AutoTable) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<style>
    /* Global Strict 5px Element Radius */
    #trashIndexRoot .btn,
    #trashIndexRoot .btn-sm,
    #trashIndexRoot button,
    #trashIndexRoot .form-control,
    #trashIndexRoot .form-select,
    #trashIndexRoot .badge,
    #trashIndexRoot .input-group-text,
    #trashIndexRoot .page-link,
    #trashIndexRoot .page-item span {
        border-radius: 5px !important;
    }

    #trashIndexRoot .card,
    #trashIndexRoot .trash-card-table,
    #trashIndexRoot .filter-card-box,
    #trashIndexRoot .modal-content {
        border-radius: 8px !important;
    }

    /* Page Canvas */
    .trash-page-container {
        padding: 18px 20px;
    }

    .filter-card-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        padding: 16px 18px;
        margin-bottom: 20px;
    }

    .trash-card-table {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        position: relative;
        margin-bottom: 20px;
    }

    /* Table Styling */
    .custom-trash-table th {
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
    .custom-trash-table td {
        padding: 11px 14px;
        vertical-align: middle;
        font-size: 12.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }

    /* Safe Bengali Digits */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* AJAX Overlay */
    #trashLoadingOverlay {
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
        .trash-page-container {
            padding: 12px 10px;
        }
        .trash-top-actions {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .trash-top-actions .btn {
            flex: 1 1 calc(50% - 6px);
            justify-content: center;
            font-size: 11.5px;
            padding: 7px 8px;
        }
        .trash-top-actions .btn-back-users {
            flex: 1 1 100%;
        }
        .filter-card-box {
            padding: 12px 14px !important;
            margin-bottom: 16px !important;
        }
        .custom-trash-table th, .custom-trash-table td {
            padding: 9px 10px;
            font-size: 12px;
        }
    }
</style>

<div class="container-fluid trash-page-container position-relative" id="trashIndexRoot">
    <!-- Header & Action Bar -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-0.5 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-trash-alt text-danger"></i> <span>Trashed Users & Staff</span>
            </h5>
            <small class="text-muted" style="font-size: 11.5px; line-height: 1.35; display: block;">Restore accidentally removed accounts or permanently purge from database</small>
        </div>
        <div class="trash-top-actions d-flex flex-wrap gap-2 no-pdf">
            <button type="button" class="btn btn-outline-success btn-sm px-3 py-1.5 fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5" onclick="exportTrashReport('excel')">
                <i class="fas fa-file-excel text-success me-1"></i> <span>Export Excel</span>
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm px-3 py-1.5 fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5" id="btn-export-trash-pdf" onclick="downloadTrashPdf()">
                <i class="fas fa-file-pdf text-danger me-1"></i> <span id="trash-pdf-text">Report PDF</span>
            </button>
            <a href="{{ route('admin.roles.users') }}" class="btn btn-outline-secondary btn-sm px-3 py-1.5 fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5 btn-back-users">
                <i class="fas fa-arrow-left me-1"></i> <span>Back to Users</span>
            </a>
        </div>
    </div>

    <!-- Alert Box -->
    <div id="trashAjaxAlertBox" class="alert d-none alert-dismissible fade show mb-3 small align-items-center gap-2 shadow-xs border-0 py-2 px-3" role="alert" style="border-radius: 5px;">
        <i id="trashAjaxAlertIcon" class="fas fa-circle-check fs-6 flex-shrink-0"></i>
        <div id="trashAjaxAlertMsg" class="fw-semibold"></div>
        <button type="button" class="btn-close btn-sm shadow-none ms-auto py-2" onclick="document.getElementById('trashAjaxAlertBox').classList.add('d-none')"></button>
    </div>

    <!-- Multi-Filter Toolbar Card -->
    <div class="filter-card-box no-pdf">
        <form id="trashFilterForm" onsubmit="event.preventDefault(); triggerTrashFilterAjax();">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm overflow-hidden border shadow-xs" style="border-radius: 5px;">
                        <span class="input-group-text bg-white border-0 text-muted ps-2.5"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                        <input type="text" name="search" id="trashFilterSearch" class="form-control border-0 py-1.5 shadow-none" 
                               value="{{ request('search') }}" placeholder="Search name, email, phone..." autocomplete="off" style="font-size: 12px;">
                    </div>
                </div>

                <div class="col-6 col-sm-3 col-md-2">
                    <select name="role" id="trashFilterRole" class="form-select form-select-sm py-1.5 shadow-none" style="font-size: 12px;">
                        <option value="all">All Roles</option>
                        @foreach($allRoles as $rName)
                            <option value="{{ $rName }}" {{ request('role') == $rName ? 'selected' : '' }}>
                                {{ ucwords(str_replace(['_', '-'], ' ', $rName)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-sm-3 col-md-2">
                    <input type="date" name="deleted_date" id="trashFilterDate" class="form-control form-control-sm py-1 shadow-none" value="{{ request('deleted_date') }}" title="Specific Deleted Date" style="font-size: 11.5px;">
                </div>

                <div class="col-6 col-sm-3 col-md-2">
                    <input type="date" name="deleted_from" id="trashFilterFrom" class="form-control form-control-sm py-1 shadow-none" placeholder="Deleted From" title="Deleted Range From" style="font-size: 11.5px;">
                </div>
                <div class="col-6 col-sm-3 col-md-2">
                    <input type="date" name="deleted_to" id="trashFilterTo" class="form-control form-control-sm py-1 shadow-none" placeholder="Deleted To" title="Deleted Range To" style="font-size: 11.5px;">
                </div>

                <!-- Per-Page Selector -->
                <div class="col-6 col-sm-3 col-md-1">
                    <select name="per_page" id="trashFilterPerPage" class="form-select form-select-sm py-1.5 shadow-none fw-semibold" style="font-size: 12px;">
                        <option value="20" {{ request('per_page', '20') == '20' ? 'selected' : '' }}>20 / page</option>
                        <option value="30" {{ request('per_page', '30') == '30' ? 'selected' : '' }}>30 / page</option>
                        <option value="50" {{ request('per_page', '50') == '50' ? 'selected' : '' }}>50 / page</option>
                        <option value="100" {{ request('per_page', '100') == '100' ? 'selected' : '' }}>100 / page</option>
                        <option value="200" {{ request('per_page', '200') == '200' ? 'selected' : '' }}>200 / page</option>
                        <option value="500" {{ request('per_page', '500') == '500' ? 'selected' : '' }}>500 / page</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                    </select>
                </div>

                <div class="col-12 d-flex gap-1.5 justify-content-end mt-2 pt-2 border-top">
                    <button type="submit" class="btn me-1 w-50 btn-primary btn-sm px-3.5 py-1.5 fw-semibold shadow-xs" style="font-size: 12px;">
                        Filter
                    </button>
                    <button type="button" class="btn ms-1 w-50 btn-light btn-sm border px-3 py-1.5 text-muted shadow-xs d-inline-flex align-items-center gap-1.5" onclick="resetTrashFilters()" title="Reset Filters" style="font-size: 12px;">
                        <i class="fas fa-rotate-left"></i> <span>Reset</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Table Card with Overlay -->
    <div class="trash-card-table overflow-hidden" id="trash-render-area">
        <div id="trashLoadingOverlay">
            <div class="spinner-border text-danger" role="status" style="width: 2.2rem; height: 2.2rem;">
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

        <!-- Pagination with Stats -->
        <div class="p-2.5 p-md-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50" id="trashPaginationWrapper">
            <small class="text-muted bn-digit" id="trashStatsText" style="font-size: 11.5px;">
                Showing {{ $trashedUsers->firstItem() ?? 0 }} to {{ $trashedUsers->lastItem() ?? 0 }} of {{$trashedUsers->total() }} trashed user(s)
            </small>
            <div id="trashPaginationLinks">
                {{ $trashedUsers->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Permanent Delete Confirmation -->
<div class="modal fade" id="deleteConfirmDialog" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3" style="border-radius: 8px;">
            <div class="modal-body p-1">
                <div class="text-danger mb-2">
                    <i class="fas fa-triangle-exclamation fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Permanent Delete?</h6>
                <p class="text-muted small mb-3" id="deleteDialogUserText" style="font-size: 12px;"></p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-sm btn-light px-3 py-1 border shadow-xs" data-bs-dismiss="modal" style="font-size: 12px;">Cancel</button>
                    <button type="button" class="btn btn-sm btn-danger px-3 py-1 fw-bold shadow-xs" id="runDeleteBtn" onclick="runForceDelete()" style="font-size: 12px;">Delete Forever</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: General Notice / Alert Dialog -->
<div class="modal fade" id="trashNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3" style="border-radius: 8px;">
            <div class="modal-body p-1">
                <div class="mb-2" id="trashNoticeModalIcon">
                    <i class="fas fa-circle-exclamation text-warning fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="trashNoticeModalTitle" style="font-size: 14px;">Notice</h6>
                <p class="text-muted small mb-3" id="trashNoticeModalMsg" style="font-size: 12px;">Notice message.</p>
                <button type="button" class="btn btn-primary btn-sm px-4 py-1 fw-semibold shadow-xs" data-bs-dismiss="modal" style="font-size: 12px;">Acknowledge</button>
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