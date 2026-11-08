
@extends('layouts.admin')

@section('title', 'Pages - ' . config('app.name'))

@section('admin_content')
<style>
    /* =========================================================
       PAGES INDEX — KAIADMIN CLASSIC / WHITE CLASSIC
    ========================================================= */

    #pagesIndexRoot {
        --pages-primary: var(--bs-primary, #1572e8);
        --pages-text: #1c2434;
        --pages-muted: #6b7280;
        --pages-border: #e6e9ef;
        --pages-surface: #ffffff;
        --pages-soft: #f7f8fa;
        --pages-hover: #f8fafc;
        --pages-radius: 8px;
        --pages-shadow: 0 1px 3px rgba(20, 32, 50, 0.05);

        min-width: 0;
        color: var(--pages-text);
        font-size: 13px;
    }

    #pagesIndexRoot *,
    #deleteConfirmModal *,
    #sessionAlertModal * {
        box-sizing: border-box;
    }

    /* Consistent Radius */
    #pagesIndexRoot .btn,
    #pagesIndexRoot button,
    #pagesIndexRoot .badge,
    #pagesIndexRoot .page-link,
    #pagesIndexRoot .page-item span,
    #deleteConfirmModal .btn,
    #sessionAlertModal .btn {
        border-radius: 5px !important;
    }

    #pagesIndexRoot .pages-table-card,
    #deleteConfirmModal .modal-content,
    #sessionAlertModal .modal-content {
        border-radius: var(--pages-radius) !important;
    }

    /* Header */
    #pagesIndexRoot .pages-index-header {
        min-width: 0;
    }

    #pagesIndexRoot .pages-index-header h5 {
        color: var(--pages-text) !important;
        font-size: 18px !important;
        font-weight: 700 !important;
        letter-spacing: -0.25px;
    }

    #pagesIndexRoot .pages-index-header small {
        color: var(--pages-muted) !important;
        font-size: 12px !important;
        line-height: 1.5 !important;
    }

    #pagesIndexRoot .pages-total-counter {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 7px 11px !important;
        background: var(--pages-surface);
        border: 1px solid var(--pages-border) !important;
        border-radius: 6px !important;
        color: var(--pages-muted);
        white-space: nowrap;
    }

    /* Buttons */
    #pagesIndexRoot .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        min-height: 30px;
        padding: 5px 11px !important;
        font-size: 11.5px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        white-space: nowrap;
        transition: all 0.18s ease;
    }

    #pagesIndexRoot .btn-compact-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 29px !important;
        min-width: 29px;
        height: 29px !important;
        padding: 0 !important;
        font-size: 11px !important;
        transition: all 0.18s ease;
    }

    #pagesIndexRoot .btn-primary {
        background-color: var(--pages-primary);
        border-color: var(--pages-primary);
    }

    #pagesIndexRoot .btn-primary:hover {
        filter: brightness(0.94);
    }

    #pagesIndexRoot .btn-light {
        background-color: #fff;
    }

    #pagesIndexRoot .btn-light:hover {
        background-color: #f1f5f9;
        border-color: #cbd5e1 !important;
    }

    #pagesIndexRoot .btn-outline-danger:hover {
        color: #fff;
    }

    #pagesIndexRoot .shadow-xs {
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06) !important;
    }

    /* Table Card */
    #pagesIndexRoot .pages-table-card {
        width: 100%;
        min-width: 0;
        margin-bottom: 20px;
        overflow: hidden;
        background: var(--pages-surface);
        border: 1px solid var(--pages-border);
        box-shadow: var(--pages-shadow);
    }

    #pagesIndexRoot .table-responsive {
        width: 100%;
        min-width: 0;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #pagesIndexRoot .custom-pages-table {
        width: 100%;
        min-width: 1050px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #pagesIndexRoot .custom-pages-table thead th {
        padding: 12px 14px;
        background-color: #f8fafc;
        color: #64748b;
        border-top: 0;
        border-bottom: 1px solid var(--pages-border);
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.045em;
        white-space: nowrap;
        vertical-align: middle;
    }

    #pagesIndexRoot .custom-pages-table tbody td {
        padding: 12px 14px;
        color: #334155;
        border-bottom: 1px solid #edf0f4;
        background-color: #fff;
        font-size: 12.5px;
        vertical-align: middle;
    }

    #pagesIndexRoot .custom-pages-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #pagesIndexRoot .custom-pages-table tbody tr {
        transition: background-color 0.15s ease;
    }

    #pagesIndexRoot .custom-pages-table tbody tr:hover td {
        background-color: #fafbfd;
    }

    #pagesIndexRoot .custom-pages-table th:first-child,
    #pagesIndexRoot .custom-pages-table td:first-child {
        width: 42px;
        min-width: 42px;
        padding-left: 14px;
        padding-right: 10px;
    }

    #pagesIndexRoot .custom-pages-table td:nth-child(2) {
        min-width: 190px;
    }

    #pagesIndexRoot .custom-pages-table td:last-child {
        min-width: 110px;
    }

    /* Table Checkbox */
    #pagesIndexRoot .form-check-input {
        width: 15px;
        height: 15px;
        margin-top: 0;
        border: 1px solid #cbd5e1;
        cursor: pointer;
        box-shadow: none;
    }

    #pagesIndexRoot .form-check-input:checked {
        background-color: var(--pages-primary);
        border-color: var(--pages-primary);
    }

    #pagesIndexRoot .form-check-input:focus {
        border-color: var(--pages-primary);
        box-shadow: 0 0 0 2px rgba(21, 114, 232, 0.12);
    }

    /* Page Title & Links */
    #pagesIndexRoot .custom-pages-table td strong {
        color: var(--pages-text) !important;
        font-size: 13px !important;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    #pagesIndexRoot .custom-pages-table td a.text-primary {
        color: var(--pages-primary) !important;
    }

    #pagesIndexRoot .custom-pages-table td a.text-primary:hover {
        text-decoration: underline !important;
    }

    /* Soft Badges */
    #pagesIndexRoot .badge-soft-published {
        color: #15803d;
        background-color: #dcfce7;
        border: 1px solid #bbf7d0;
    }

    #pagesIndexRoot .badge-soft-draft {
        color: #854d0e;
        background-color: #fef9c3;
        border: 1px solid #fef08a;
    }

    #pagesIndexRoot .badge-soft-blocks {
        color: #0369a1;
        background-color: #e0f2fe;
        border: 1px solid #bae6fd;
    }

    #pagesIndexRoot .badge-soft-raw {
        color: #475569;
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
    }

    #pagesIndexRoot .custom-pages-table .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        max-width: 100%;
        font-weight: 600;
        white-space: nowrap;
    }

    /* Monospace Values */
    #pagesIndexRoot .font-monospace,
    #pagesIndexRoot .bn-digit {
        font-weight: 600 !important;
        letter-spacing: -0.15px;
    }

    /* Pagination */
    #pagesIndexRoot .pages-pagination-wrapper {
        display: block;
        width: 100%;
        padding: 13px 16px !important;
        background-color: #fafbfc !important;
        border-top: 1px solid var(--pages-border) !important;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #pagesIndexRoot .pages-pagination-wrapper nav {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 10px;
        width: 100% !important;
        min-width: 0;
    }

    #pagesIndexRoot .pages-pagination-wrapper nav > div {
        max-width: 100%;
    }

    #pagesIndexRoot .pages-pagination-wrapper nav svg {
        display: inline-block !important;
        width: 15px !important;
        height: 15px !important;
        max-width: 15px !important;
        max-height: 15px !important;
        vertical-align: middle;
    }

    #pagesIndexRoot .pages-pagination-wrapper .pagination {
        display: flex !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
        gap: 4px;
        margin: 0 !important;
        padding-left: 0;
        list-style: none;
    }

    #pagesIndexRoot .pages-pagination-wrapper .page-item .page-link,
    #pagesIndexRoot .pages-pagination-wrapper .page-item span {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 30px;
        height: 30px;
        padding: 3px 8px;
        color: #475569;
        background-color: #fff;
        border: 1px solid #e2e8f0;
        font-size: 12px;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    #pagesIndexRoot .pages-pagination-wrapper .page-item .page-link:hover {
        color: var(--pages-primary);
        background-color: #eff6ff;
        border-color: #bfdbfe;
    }

    #pagesIndexRoot .pages-pagination-wrapper .page-item.active .page-link,
    #pagesIndexRoot .pages-pagination-wrapper .page-item.active span {
        color: #fff !important;
        background-color: var(--pages-primary) !important;
        border-color: var(--pages-primary) !important;
        font-weight: 700;
    }

    #pagesIndexRoot .pages-pagination-wrapper .page-item.disabled .page-link,
    #pagesIndexRoot .pages-pagination-wrapper .page-item.disabled span {
        color: #94a3b8 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        pointer-events: none;
    }

    /* Empty State */
    #pagesIndexRoot .custom-pages-table .text-center.py-5 {
        padding-top: 42px !important;
        padding-bottom: 42px !important;
    }

    #pagesIndexRoot .custom-pages-table .text-center.py-5 > i {
        color: #94a3b8;
    }

    /* Modals */
    #deleteConfirmModal .modal-content,
    #sessionAlertModal .modal-content {
        border: 1px solid rgba(226, 232, 240, 0.9) !important;
        box-shadow: 0 12px 35px rgba(15, 23, 42, 0.16) !important;
        overflow: hidden;
    }

    #deleteConfirmModal .modal-body,
    #sessionAlertModal .modal-content {
        padding: 22px 20px !important;
    }

    #deleteConfirmModal h6,
    #sessionAlertModal h6 {
        color: var(--pages-text, #1c2434);
        font-weight: 700;
    }

    #deleteConfirmModal p,
    #sessionAlertModal p {
        color: #64748b !important;
        line-height: 1.55;
    }

    #deleteConfirmModal .btn,
    #sessionAlertModal .btn {
        min-height: 31px;
        font-weight: 600;
    }

    #deleteConfirmModal form {
        margin: 0;
    }

    /* Tablet */
    @media (min-width: 768px) {
        #pagesIndexRoot .pages-pagination-wrapper nav {
            flex-direction: row !important;
            justify-content: space-between !important;
        }
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        #pagesIndexRoot {
            padding-top: 12px !important;
        }

        #pagesIndexRoot .pages-index-header {
            align-items: flex-start !important;
            gap: 10px !important;
        }

        #pagesIndexRoot .pages-index-header h5 {
            font-size: 16px !important;
        }

        #pagesIndexRoot .pages-index-header > div:first-child {
            min-width: 0;
        }

        #pagesIndexRoot .pages-index-header small {
            max-width: 100%;
            font-size: 11.5px !important;
        }

        #pagesIndexRoot .pages-total-counter {
            align-self: flex-start !important;
            font-size: 11px !important;
        }

        #pagesIndexRoot .custom-pages-table {
            min-width: 1000px;
        }

        #pagesIndexRoot .custom-pages-table th {
            padding: 10px 11px;
            font-size: 10px;
        }

        #pagesIndexRoot .custom-pages-table td {
            padding: 10px 11px;
            font-size: 12px;
        }

        #pagesIndexRoot .pages-pagination-wrapper {
            padding: 12px 10px !important;
        }

        #pagesIndexRoot .pages-pagination-wrapper nav {
            gap: 9px;
        }

        #pagesIndexRoot .pages-pagination-wrapper .pagination {
            gap: 3px;
        }

        #pagesIndexRoot .pages-pagination-wrapper .page-item .page-link,
        #pagesIndexRoot .pages-pagination-wrapper .page-item span {
            min-width: 28px;
            height: 28px;
            padding: 3px 6px;
            font-size: 11px;
        }

        #deleteConfirmModal .modal-dialog,
        #sessionAlertModal .modal-dialog {
            margin-right: 14px;
            margin-left: 14px;
        }

        #deleteConfirmModal .modal-body,
        #sessionAlertModal .modal-content {
            padding: 18px 15px !important;
        }
    }

    /* Extra Small Devices */
    @media (max-width: 380px) {
        #pagesIndexRoot .pages-index-header h5 {
            font-size: 15px !important;
        }

        #pagesIndexRoot .btn-compact {
            padding: 5px 8px !important;
            font-size: 10.5px !important;
        }

        #pagesIndexRoot .pages-total-counter {
            padding: 6px 8px !important;
            font-size: 10px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="pagesIndexRoot">

    <!-- Header Section -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 pages-index-header">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h5 class="fw-bold text-dark mb-0" style="font-size: 18px;">Pages</h5>
                @php
                    $createPageUrl = Route::has('admin.content.pages.create') 
                        ? route('admin.content.pages.create') 
                        : (Route::has('admin.pages.create') ? route('admin.pages.create') : url('admin/content/pages/create'));
                @endphp
                <a href="{{ $createPageUrl }}" class="btn btn-primary btn-compact shadow-xs">
                    <i class="fas fa-plus me-1" style="font-size: 10px;"></i>
                    <span>Add Page</span>
                </a>
            </div>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">Manage all static and builder pages, view SEO scores, and edit content.</small>
        </div>
        <div class="text-muted small align-self-start align-self-sm-center bg-white border px-2 py-1" style="font-size: 11.5px; border-radius: 5px;">
            Total Pages: <strong class="text-dark font-monospace bn-digit">{{ isset($pages) ? $pages->total() : 0 }}</strong>
        </div>
    </div>

    <!-- Pages Data Table Card -->
    <div class="pages-table-card overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0 custom-pages-table text-nowrap">
                <thead>
                    <tr>
                        <th style="width: 38px;" class="text-center">
                            <input type="checkbox" class="form-check-input mt-0" style="border-radius: 4px; cursor: pointer;">
                        </th>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Engine</th>
                        <th>SEO Title</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Date</th>
                        <th class="text-end" style="width: 110px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pages as $page)
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input mt-0" style="border-radius: 4px; cursor: pointer;">
                            </td>
                            <td style="min-width: 190px;">
                                <strong class="text-dark d-block mb-1 text-wrap" style="font-size: 13px; line-height: 1.3;">{{ $page->title }}</strong>
                                @php
                                    $viewPageUrl = Route::has('pages.show') ? route('pages.show', $page->slug) : url('/' . $page->slug);
                                @endphp
                                <a href="{{ $viewPageUrl }}" target="_blank" class="small text-decoration-none text-primary d-inline-flex align-items-center" style="font-size: 11px;">
                                    <i class="fas fa-external-link-alt me-1" style="font-size: 9.5px;"></i>
                                    <span>View Page</span>
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 11px;">/{{ $page->slug }}</span>
                            </td>
                            <td>
                                <span class="badge {{ ($page->editor_type ?? '') === 'blocks' ? 'badge-soft-blocks' : 'badge-soft-raw' }} px-2 py-1 fw-semibold" style="font-size: 10px;">
                                    {{ ($page->editor_type ?? '') === 'blocks' ? 'Visual Blocks' : 'HTML/Raw' }}
                                </span>
                            </td>
                            <td class="small text-muted text-truncate" style="max-width: 210px; font-size: 12px;" title="{{ $page->seo_title ?? '' }}">
                                {{ $page->seo_title ?? 'N/A' }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ ($page->status ?? '') === 'published' ? 'badge-soft-published' : 'badge-soft-draft' }} px-2 py-1 text-uppercase fw-bold" style="font-size: 9.5px; letter-spacing: 0.03em;">
                                    {{ $page->status ?? 'draft' }}
                                </span>
                            </td>
                            <td class="text-end text-muted small font-monospace bn-digit" style="font-size: 11.5px;">
                                {{ !empty($page->created_at) ? date('d M, Y', strtotime($page->created_at)) : 'N/A' }}
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex justify-content-end align-items-center gap-1">
                                    @php
                                        $editPageUrl = Route::has('admin.content.pages.edit')
                                            ? route('admin.content.pages.edit', $page->id)
                                            : (Route::has('admin.pages.edit') ? route('admin.pages.edit', $page->id) : url('admin/content/pages/' . $page->id . '/edit'));

                                        $destroyPageUrl = Route::has('admin.content.pages.destroy')
                                            ? route('admin.content.pages.destroy', $page->id)
                                            : (Route::has('admin.pages.destroy') ? route('admin.pages.destroy', $page->id) : url('admin/content/pages/' . $page->id));
                                    @endphp

                                    <a href="{{ $editPageUrl }}" class="btn btn-light border text-dark btn-compact shadow-xs" title="Edit Page">
                                        <i class="fas fa-edit me-1 text-primary" style="font-size: 10px;"></i>
                                        <span>Edit</span>
                                    </a>
                                    
                                    {{-- Delete Button opening Modal --}}
                                    <button type="button" class="btn btn-outline-danger btn-compact-icon shadow-xs" title="Delete" onclick="openDeleteModal('{{ $destroyPageUrl }}', '{{ addslashes($page->title) }}')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="far fa-file-alt fa-2x mb-2 d-block opacity-25"></i>
                                <p class="mb-2 small fw-semibold">No pages found.</p>
                                <a href="{{ $createPageUrl }}" class="btn btn-primary btn-compact shadow-xs">Create First Page</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($pages, 'hasPages') && $pages->hasPages())
            <div class="p-3 border-top bg-light bg-opacity-50 pages-pagination-wrapper overflow-auto">
                {{ $pages->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

<!-- ================= 1. DELETE CONFIRMATION MODAL ================= -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="modal-body p-3 text-center">
                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mb-2" style="width: 48px; height: 48px;">
                    <i class="fas fa-trash-alt fs-5"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Delete Page?</h6>
                <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">
                    Are you sure you want to permanently delete <strong id="deletePageTitle" class="text-dark"></strong>? This action cannot be reversed.
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-sm px-3 py-1 fw-semibold border" style="font-size: 12px;" data-bs-dismiss="modal">Cancel</button>
                    <form id="deletePageForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm px-3 py-1 fw-bold shadow-xs" style="font-size: 12px;">Delete Permanently</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= 2. SUCCESS / ERROR ALERT MODAL ================= -->
@if(session('success') || session('error'))
<div class="modal fade" id="sessionAlertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="rounded-circle {{ session('success') ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 48px; height: 48px;">
                <i class="fas {{ session('success') ? 'fa-check-circle' : 'fa-exclamation-triangle' }} fs-5"></i>
            </div>
            <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">{{ session('success') ? 'Success!' : 'Error!' }}</h6>
            <p class="text-muted small mb-3" style="font-size: 12px;">{{ session('success') ?? session('error') }}</p>
            <div>
                <button type="button" class="btn btn-dark btn-sm px-4 py-1 fw-semibold" style="font-size: 12px;" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
@endif

<script>
    // Delete Confirmation Modal Logic
    function openDeleteModal(actionUrl, pageTitle) {
        document.getElementById('deletePageForm').action = actionUrl;
        document.getElementById('deletePageTitle').innerText = '"' + pageTitle + '"';
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        deleteModal.show();
    }

    // Auto Trigger Session Alert Modal on page load
    document.addEventListener("DOMContentLoaded", function () {
        var alertModalEl = document.getElementById('sessionAlertModal');
        if (alertModalEl) {
            var alertModal = new bootstrap.Modal(alertModalEl);
            alertModal.show();
            setTimeout(function () {
                alertModal.hide();
            }, 3000);
        }
    });
</script>
@endsection
