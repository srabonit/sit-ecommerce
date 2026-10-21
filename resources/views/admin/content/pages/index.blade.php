@extends('layouts.admin')

@section('title', 'Pages - ' . config('app.name'))

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #pagesIndexRoot .btn,
    #pagesIndexRoot .btn-sm,
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
        border-radius: 8px !important;
    }

    /* Compact & Sleek Button Sizing */
    #pagesIndexRoot .btn-compact {
        font-size: 11.5px !important;
        padding: 4px 10px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    #pagesIndexRoot .btn-compact-icon {
        width: 26px !important;
        height: 26px !important;
        padding: 0 !important;
        font-size: 11px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Enterprise Table Card */
    .pages-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 20px;
    }

    .custom-pages-table th {
        background-color: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 10px 14px;
        white-space: nowrap;
    }

    .custom-pages-table td {
        padding: 10px 14px;
        vertical-align: middle;
        font-size: 12.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }

    .custom-pages-table tbody tr:hover {
        background-color: #fafbfc;
    }

    /* Soft Badges */
    .badge-soft-published { background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-soft-draft     { background-color: #fef9c3; color: #854d0e; border: 1px solid #fef08a; }
    .badge-soft-blocks    { background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .badge-soft-raw       { background-color: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

    /* Pagination & SVG Size Control */
    .pages-pagination-wrapper nav {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 10px;
        width: 100% !important;
    }
    @media (min-width: 768px) {
        .pages-pagination-wrapper nav {
            flex-direction: row !important;
            justify-content: space-between !important;
        }
    }
    .pages-pagination-wrapper nav svg {
        width: 15px !important;
        height: 15px !important;
        max-width: 15px !important;
        max-height: 15px !important;
        vertical-align: middle;
        display: inline-block !important;
    }
    .pages-pagination-wrapper .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
        gap: 4px;
        padding-left: 0;
        list-style: none;
    }
    .pages-pagination-wrapper .page-item .page-link,
    .pages-pagination-wrapper .page-item span {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 30px;
        height: 30px;
        padding: 3px 8px;
        font-size: 12px;
        color: #475569;
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .pages-pagination-wrapper .page-item.active .page-link,
    .pages-pagination-wrapper .page-item.active span {
        color: #ffffff !important;
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        font-weight: 700;
    }
    .pages-pagination-wrapper .page-item.disabled .page-link,
    .pages-pagination-wrapper .page-item.disabled span {
        color: #94a3b8 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        pointer-events: none;
    }

    /* Safe Bengali Digits */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 767.98px) {
        .pages-index-header h5 {
            font-size: 16px !important;
        }
        .custom-pages-table th,
        .custom-pages-table td {
            padding: 9px 10px;
            font-size: 12px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="pagesIndexRoot">

    <!-- Header Section -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 pages-index-header">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h5 class="fw-bold text-dark mb-0" style="font-size: 18px;">Pages</h5>
                <a href="{{ route('admin.content.pages.create') }}" class="btn btn-primary btn-compact shadow-xs">
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
                                <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="small text-decoration-none text-primary d-inline-flex align-items-center" style="font-size: 11px;">
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
                                    <a href="{{ route('admin.content.pages.edit', $page->id) }}" class="btn btn-light border text-dark btn-compact shadow-xs" title="Edit Page">
                                        <i class="fas fa-edit me-1 text-primary" style="font-size: 10px;"></i>
                                        <span>Edit</span>
                                    </a>
                                    
                                    {{-- Delete Button opening Modal --}}
                                    <button type="button" class="btn btn-outline-danger btn-compact-icon shadow-xs" title="Delete" onclick="openDeleteModal('{{ route('admin.content.pages.destroy', $page->id) }}', '{{ addslashes($page->title) }}')">
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
                                <a href="{{ route('admin.content.pages.create') }}" class="btn btn-primary btn-compact shadow-xs">Create First Page</a>
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
            // Automatically close after 3 seconds
            setTimeout(function () {
                alertModal.hide();
            }, 3000);
        }
    });
</script>
@endsection