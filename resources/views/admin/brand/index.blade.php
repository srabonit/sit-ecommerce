@extends('layouts.admin')

@section('title', 'Brand Management - ' . config('app.name'))

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #admin-brand-root .btn,
    #admin-brand-root .btn-sm,
    #admin-brand-root button,
    #admin-brand-root .form-control,
    #admin-brand-root .form-select,
    #admin-brand-root .badge,
    #admin-brand-root .input-group-text,
    #admin-brand-root .page-link,
    #admin-brand-root .page-item span,
    #brandFormModal .btn,
    #brandFormModal .form-control,
    #brandFormModal .form-select,
    #brandFormModal .modal-content,
    #brandDeleteModal .btn,
    #brandDeleteModal .modal-content,
    #brandNoticeModal .btn,
    #brandNoticeModal .modal-content,
    .admin-toast-box {
        border-radius: 5px !important;
    }

    #admin-brand-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #admin-brand-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Compact & Sleek Button Sizing */
    #admin-brand-root .btn-compact,
    #brandFormModal .btn-compact,
    #brandDeleteModal .btn-compact,
    #brandNoticeModal .btn-compact {
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

    /* Square 26x26 Table Action Buttons */
    .brand-action-sq-btn {
        width: 26px !important;
        height: 26px !important;
        min-width: 26px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 10.5px !important;
        border-radius: 5px !important;
        transition: all 0.15s ease;
    }

    /* Enterprise Card & Table Surface */
    .brand-main-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }
    .brand-table-box {
        background: #ffffff;
        border-radius: 6px !important;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .brand-table th {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        background-color: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        white-space: nowrap;
        padding: 10px 14px;
    }
    .brand-table td {
        padding: 10px 14px;
        vertical-align: middle;
        white-space: nowrap;
        font-size: 12.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }
    .brand-table tbody tr:hover {
        background-color: #fafbfc;
    }

    /* Filter Bar Box */
    .brand-filter-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px !important;
        padding: 12px 14px;
    }
    .brand-filter-control {
        font-size: 12px !important;
        height: 33px !important;
        padding: 4px 10px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        box-shadow: none !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .brand-filter-control:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        outline: none;
    }
    .brand-addon-icon {
        height: 33px !important;
        background-color: #f8fafc !important;
        border: 1px solid #cbd5e1 !important;
        border-right: 0 !important;
        color: #64748b !important;
    }

    /* Modal Form Controls & Media Chooser Box */
    #brandFormModal .form-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    #brandFormModal .form-control,
    #brandFormModal .form-select {
        font-size: 12.5px;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
    }
    #brandFormModal .form-control:focus,
    #brandFormModal .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }
    .brand-media-picker-box {
        background: #f8fafc;
        border: 1.5px dashed #cbd5e1;
        border-radius: 6px !important;
        padding: 12px;
        transition: border-color 0.15s ease;
    }
    .brand-media-picker-box:hover {
        border-color: #93c5fd;
    }
    .btn-clear-brand-logo {
        position: absolute;
        top: -6px;
        right: -6px;
        width: 20px;
        height: 20px;
        border-radius: 50% !important;
        background: #dc2626;
        color: #ffffff;
        border: 2px solid #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        padding: 0;
        z-index: 5;
    }

    /* Modal Scroll Fix */
    #brandFormModal .modal-dialog-scrollable .modal-content {
        max-height: calc(100vh - 40px) !important;
        display: flex !important;
        flex-direction: column !important;
    }
    #brandFormModal .modal-body {
        max-height: calc(100vh - 180px) !important;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch;
    }

    /* Floating Toast */
    .admin-toast-box {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1080;
        min-width: 300px;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.15);
        overflow: hidden;
        animation: slideInRight 0.25s ease;
    }
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    /* ================= 100% BULLETPROOF PAGINATION STYLING ================= */
    #brand-pagination-links {
        width: 100% !important;
        display: flex !important;
        align-items: center !important;
    }
    @media (min-width: 768px) {
        #brand-pagination-links {
            width: auto !important;
        }
    }
    #brand-pagination-links nav {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    /* Hide Laravel's mobile-only prev/next block so numbered pagination shows everywhere */
    #brand-pagination-links nav > div:first-child.d-sm-none {
        display: none !important;
    }
    #brand-pagination-links nav > div:last-child,
    #brand-pagination-links nav > div.d-sm-flex {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        gap: 8px !important;
    }
    @media (min-width: 768px) {
        #brand-pagination-links nav > div:last-child,
        #brand-pagination-links nav > div.d-sm-flex {
            flex-direction: row !important;
            justify-content: flex-end !important;
        }
    }
    #brand-pagination-links nav p.text-muted,
    #brand-pagination-links nav div:has(> p.text-muted) {
        display: none !important;
    }
    #brand-pagination-links .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
        align-items: center !important;
        gap: 5px !important;
        padding: 0 !important;
        list-style: none !important;
    }
    #brand-pagination-links .page-item {
        margin: 0 !important;
    }
    #brand-pagination-links .page-item .page-link,
    #brand-pagination-links .page-item span {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 28px !important;
        height: 28px !important;
        padding: 2px 8px !important;
        font-size: 11.5px !important;
        line-height: 1 !important;
        color: #475569 !important;
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 5px !important;
        text-decoration: none !important;
        transition: all 0.15s ease !important;
    }
    #brand-pagination-links .page-item .page-link:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }
    #brand-pagination-links .page-item.active .page-link,
    #brand-pagination-links .page-item.active span {
        color: #ffffff !important;
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        font-weight: 700 !important;
    }
    #brand-pagination-links .page-item.disabled .page-link,
    #brand-pagination-links .page-item.disabled span {
        color: #cbd5e1 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        pointer-events: none !important;
        opacity: 0.7 !important;
    }
    #brand-pagination-links nav svg {
        width: 12px !important;
        height: 12px !important;
        max-width: 12px !important;
        max-height: 12px !important;
        vertical-align: middle !important;
    }

    /* Safe Bengali Digits */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* 100/100 Mobile Optimizations */
    @media (max-width: 767.98px) {
        #brand-top-header h5 {
            font-size: 16px !important;
        }
        .header-add-btn-wrap {
            width: 100%;
        }
        .header-add-btn-wrap .btn-compact {
            width: 100%;
            padding: 7px 12px !important;
        }
        .media-trigger-bar {
            flex-direction: column;
            align-items: stretch !important;
        }
        .media-trigger-bar .btn-compact {
            width: 100%;
        }
        .brand-table th,
        .brand-table td {
            padding: 9px 10px !important;
            font-size: 12px;
        }
        #brand-pagination-links .pagination {
            gap: 4px !important;
        }
        #brand-pagination-links .page-item .page-link,
        #brand-pagination-links .page-item span {
            min-width: 27px !important;
            height: 27px !important;
            padding: 2px 6px !important;
            font-size: 11px !important;
        }
    }
</style>

<div class="brand-main-card p-3 p-md-4 mb-4" id="admin-brand-root">
    
    <!-- Top Header & Action -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 pb-3 border-bottom" id="brand-top-header">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center" style="font-size: 18px;">
                    <i class="fas fa-tags text-primary me-2"></i>
                    <span>Brand Management</span>
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 bn-digit font-monospace" id="brand-total-badge" style="font-size: 11px;">
                    {{ $brands->total() }}
                </span>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">Manage product brands, logos, active status, and inventory associations.</small>
        </div>
        <div class="header-add-btn-wrap">
            <button type="button" class="btn btn-warning btn-compact px-3 py-2 fw-bold text-dark shadow-xs" onclick="openAddBrandModal()">
                <i class="fas fa-plus me-1"></i>
                <span>Add Brand</span>
            </button>
        </div>
    </div>

    <!-- Filter & Live Search Bar -->
    <div class="brand-filter-box mb-3" id="brand-filter-wrapper">
        <form id="brand-filter-form" onsubmit="return false;">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text brand-addon-icon"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                        <input type="text" name="search" id="brand-search-input" class="form-control brand-filter-control" placeholder="Search brand by name, slug or ID...">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <select name="status" id="brand-status-filter" class="form-select brand-filter-control brand-filter-trigger">
                        <option value="">All Statuses</option>
                        <option value="1">Active Only</option>
                        <option value="0">Inactive Only</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 text-md-end">
                    <small class="text-secondary fw-semibold" style="font-size: 11.5px;"> Real-time Search</small>
                </div>
            </div>
        </form>
    </div>

    <!-- Brand Table -->
    <div class="brand-table-box">
        <div class="table-responsive" style="-webkit-overflow-scrolling: touch;">
            <table class="table align-middle brand-table mb-0 text-nowrap" id="brands-data-table">
                <thead>
                    <tr>
                        <th class="text-center">ID</th>
                        <th class="text-center">Thumbnail (Logo)</th>
                        <th>Brand Name</th>
                        <th>Slug</th>
                        <th>Product Count</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody id="brand-table-tbody">
                    @include('admin.brand.partials.table_rows', ['brands' => $brands])
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Footer -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 mt-3 pt-2 border-top" id="brand-pagination-footer">
        <div class="small text-muted text-center text-md-start bn-digit" id="brand-showing-entries" style="font-size: 11.5px;">
            Showing {{ $brands->firstItem() ?? 0 }} to {{ $brands->lastItem() ?? 0 }} of {{$brands->total() }} entries
        </div>
        <div id="brand-pagination-links" class="overflow-auto d-flex justify-content-center justify-content-md-end">
            {{ $brands->links('pagination::bootstrap-5') }}
        </div>
    </div>

</div>

<!-- ================= Modal: Add / Edit Brand ================= -->
<div class="modal fade" id="brandFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <form id="brandAjaxForm" class="modal-content border-0 shadow-lg overflow-hidden" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="brand_id" id="brand_edit_id" value="">
            
            <!-- Media Library & Remove Logo Flags -->
            <input type="hidden" name="logo_media" id="brand_input_logo_media" value="">
            <input type="hidden" name="remove_logo" id="brand_remove_logo" value="0">
            
            <div class="modal-header border-bottom px-3 py-2 bg-light">
                <h6 class="modal-title fw-bold text-dark mb-0" id="brandModalLabel" style="font-size: 14px;">
                    <i class="fas fa-plus-circle text-primary me-1"></i> Add New Brand
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-3">
                <div class="row g-3">
                    <!-- Brand Name -->
                    <div class="col-12 col-md-6">
                        <label class="form-label">Brand Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="brand_input_name" class="form-control" placeholder=" Nike, Apple, Polo" required oninput="generateBrandSlugAuto(this.value)">
                    </div>

                    <!-- Slug -->
                    <div class="col-12 col-md-6">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" id="brand_input_slug" class="form-control font-monospace" placeholder="nike-brand">
                        <small class="text-muted d-block mt-1" style="font-size: 10.5px;">Unique identifier for URLs.</small>
                    </div>

                    <!-- Thumbnail (Logo) with Global Media Picker -->
                    <div class="col-12" id="brand-logo-upload-wrapper">
                        <label class="form-label">Thumbnail (Logo)</label>
                        <div class="brand-media-picker-box">
                            <div class="media-trigger-bar d-flex align-items-center gap-2 mb-2">
                                <button type="button" class="btn btn-outline-primary btn-compact shadow-xs" onclick="chooseBrandLogoMedia()">
                                    <i class="fas fa-images me-1"></i>
                                    <span>Choose from Media Library</span>
                                </button>
                                <span class="small text-muted" style="font-size: 11px;">or upload local file:</span>
                            </div>

                            <input type="file" name="logo" id="brand_input_logo" class="form-control form-control-sm mb-1" accept="image/*" onchange="previewBrandLocalLogo(this)">
                            
                            <div id="brand_thumb_preview_box" class="mt-2 position-relative d-inline-block" style="display: none;">
                                <img id="brand_thumb_preview" src="" class="border object-fit-cover p-1 bg-white shadow-xs" style="width: 60px; height: 60px; border-radius: 5px !important;">
                                <button type="button" class="btn-clear-brand-logo" onclick="clearBrandLogo()" title="Remove Logo">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="brand_input_description" class="form-control" rows="2" placeholder="Write a short overview about this brand..."></textarea>
                    </div>

                    <!-- Active Status Switch -->
                    <div class="col-12">
                        <div class="p-2 px-3 bg-light border d-flex justify-content-between align-items-center" style="border-radius: 5px;">
                            <div>
                                <strong class="d-block text-dark" style="font-size: 12px;">Enable / Active Status</strong>
                                <small class="text-muted" style="font-size: 11px;">Active brands are visible in product filters and storefront catalog.</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input m-0" type="checkbox" role="switch" name="is_active" id="brand_input_is_active" value="1" checked style="width: 40px; height: 20px; cursor: pointer;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top px-3 py-2 bg-light">
                <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning btn-compact px-3 fw-bold text-dark shadow-xs" id="btn-save-brand">
                    <i class="fas fa-save me-1"></i>
                    <span>Save Brand</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= Modal: Confirm Delete ================= -->
<div class="modal fade" id="brandDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg overflow-hidden text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2 bg-danger-subtle text-danger mx-auto" style="width: 48px; height: 48px;">
                    <i class="fas fa-trash-alt fs-5"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Delete Brand?</h6>
                <p class="text-secondary small mb-3" id="brand-delete-msg" style="font-size: 12px; line-height: 1.45;">Are you sure you want to permanently delete this brand?</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-compact px-3 shadow-xs" id="btn-confirm-delete-brand">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= Modal: Notice (Replaces raw alert()) ================= -->
<div class="modal fade" id="brandNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 44px; height: 44px;">
                    <i class="fas fa-exclamation-triangle fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="brandNoticeTitle" style="font-size: 14px;">Notice</h6>
                <p class="text-muted small mb-3" id="brandNoticeMsg" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Holder -->
<div id="brand-toast-holder"></div>

<script>
    function showBrandNotice(message, title = 'Notice') {
        const titleEl = document.getElementById('brandNoticeTitle');
        const msgEl = document.getElementById('brandNoticeMsg');
        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;
        const modalEl = document.getElementById('brandNoticeModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    // Universal Media Item Extractor
    function extractMediaItem(mediaInput) {
        if (!mediaInput) return null;
        const item = Array.isArray(mediaInput) ? mediaInput[0] : mediaInput;
        if (!item || typeof item !== 'object') return null;
        const rawPath = item.file_path || item.path || item.url || '';
        const rawUrl  = item.url || item.file_path || item.path || '';
        if (!rawPath || rawPath === 'undefined') return null;
        return {
            path: String(rawPath).trim(),
            url:  String(rawUrl).trim()
        };
    }

    function generateBrandSlugAuto(text) {
        const editId = document.getElementById('brand_edit_id').value;
        if (!editId) {
            document.getElementById('brand_input_slug').value = text.toLowerCase().trim()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    }

    // Media Library Trigger
    function chooseBrandLogoMedia() {
        if (typeof window.openMediaPicker === 'function') {
            window.openMediaPicker({
                multiple: false,
                onSelect: function(media) {
                    const picked = extractMediaItem(media);
                    if (picked && picked.path) {
                        document.getElementById('brand_input_logo_media').value = picked.path;
                        const fileInput = document.getElementById('brand_input_logo');
                        if (fileInput) fileInput.value = '';
                        document.getElementById('brand_remove_logo').value = '0';

                        const img = document.getElementById('brand_thumb_preview');
                        img.src = picked.url;
                        document.getElementById('brand_thumb_preview_box').style.display = 'inline-block';
                    }
                }
            });
        } else {
            showBrandNotice('Media Library is loading, please try again.');
        }
    }

    // Local File Preview
    function previewBrandLocalLogo(input) {
        if (input.files && input.files[0]) {
            document.getElementById('brand_input_logo_media').value = '';
            document.getElementById('brand_remove_logo').value = '0';
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('brand_thumb_preview').src = e.target.result;
                document.getElementById('brand_thumb_preview_box').style.display = 'inline-block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Clear Selected Logo
    function clearBrandLogo() {
        const fileInput = document.getElementById('brand_input_logo');
        if (fileInput) fileInput.value = '';
        document.getElementById('brand_input_logo_media').value = '';
        document.getElementById('brand_remove_logo').value = '1';
        document.getElementById('brand_thumb_preview').src = '';
        document.getElementById('brand_thumb_preview_box').style.display = 'none';
    }

    function openAddBrandModal() {
        document.getElementById('brandAjaxForm').reset();
        document.getElementById('brand_edit_id').value = '';
        document.getElementById('brand_input_logo_media').value = '';
        document.getElementById('brand_remove_logo').value = '0';
        document.getElementById('brandModalLabel').innerHTML = '<i class="fas fa-plus-circle text-primary me-1"></i> Add Brand';
        document.getElementById('brand_thumb_preview').src = '';
        document.getElementById('brand_thumb_preview_box').style.display = 'none';
        document.getElementById('brand_input_is_active').checked = true;

        bootstrap.Modal.getOrCreateInstance(document.getElementById('brandFormModal')).show();
    }

    // Submit Store or Update
    document.getElementById('brandAjaxForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const editId = document.getElementById('brand_edit_id').value;
        const formData = new FormData(this);
        const saveBtn = document.getElementById('btn-save-brand');

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span><span>Saving...</span>';

        let url = "{{ route('admin.product.brand.store') }}";
        if (editId) {
            url = "{{ url('admin/product/brand/update') }}/" + editId;
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save me-1"></i><span>Save Brand</span>';

            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('brandFormModal')).hide();
                fetchBrandList(1);
                showBrandToast('Success', data.message, 'success');
            } else {
                showBrandToast('Error', data.message || 'Failed to save brand.', 'danger');
            }
        })
        .catch(err => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save me-1"></i><span>Save Brand</span>';
            showBrandToast('Error', 'Validation error or network problem.', 'danger');
        });
    });

    // Edit Brand
    document.addEventListener('click', function(e) {
        const editBtn = e.target.closest('.btn-edit-brand');
        if (editBtn) {
            const id = editBtn.dataset.id;
            fetch("{{ url('admin/product/brand/edit') }}/" + id)
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    const d = res.data;
                    document.getElementById('brand_edit_id').value = d.id;
                    document.getElementById('brand_input_name').value = d.name;
                    document.getElementById('brand_input_slug').value = d.slug;
                    document.getElementById('brand_input_description').value = d.description || '';
                    document.getElementById('brand_input_is_active').checked = (d.is_active == 1);
                    document.getElementById('brand_input_logo_media').value = '';
                    document.getElementById('brand_remove_logo').value = '0';
                    const fileInput = document.getElementById('brand_input_logo');
                    if (fileInput) fileInput.value = '';

                    if (d.logo_url || d.logo) {
                        let resolvedLogo = d.logo_url;
                        if (!resolvedLogo) {
                            let logoPath = d.logo.replace(/^\/+/, '');
                            if (logoPath.startsWith('http://') || logoPath.startsWith('https://')) {
                                resolvedLogo = logoPath;
                            } else if (logoPath.startsWith('uploads/') || logoPath.startsWith('storage/')) {
                                resolvedLogo = "{{ asset('') }}" + logoPath;
                            } else {
                                resolvedLogo = "{{ asset('uploads/') }}/" + logoPath;
                            }
                        }
                        document.getElementById('brand_thumb_preview').src = resolvedLogo;
                        document.getElementById('brand_thumb_preview_box').style.display = 'inline-block';
                    } else {
                        document.getElementById('brand_thumb_preview').src = '';
                        document.getElementById('brand_thumb_preview_box').style.display = 'none';
                    }

                    document.getElementById('brandModalLabel').innerHTML = `<i class="fas fa-edit text-primary me-1"></i> Edit Brand: ${d.name}`;
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('brandFormModal')).show();
                }
            });
        }
    });

    // Delete Brand
    let pendingDeleteBrandId = null;
    document.addEventListener('click', function(e) {
        const delBtn = e.target.closest('.btn-delete-brand');
        if (delBtn) {
            pendingDeleteBrandId = delBtn.dataset.id;
            const name = delBtn.dataset.name;
            document.getElementById('brand-delete-msg').innerHTML = `Are you sure you want to permanently delete brand "<strong>${name}</strong>"?`;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('brandDeleteModal')).show();
        }
    });

    document.getElementById('btn-confirm-delete-brand').addEventListener('click', function() {
        if (!pendingDeleteBrandId) return;

        fetch("{{ url('admin/product/brand/delete') }}/" + pendingDeleteBrandId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            bootstrap.Modal.getInstance(document.getElementById('brandDeleteModal')).hide();
            fetchBrandList(1);
            showBrandToast('Deleted', data.message, 'danger');
        })
        .catch(err => {
            bootstrap.Modal.getInstance(document.getElementById('brandDeleteModal')).hide();
            showBrandToast('Error', 'Failed to delete brand.', 'danger');
        });
    });

    // Status Toggle
    function toggleBrandStatusAjax(id, switchEl) {
        fetch("{{ url('admin/product/brand/toggle-status') }}/" + id, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showBrandToast('Updated', data.message, 'info');
            }
        });
    }

    // Live Search & Filter
    let brandSearchTimer = null;
    document.getElementById('brand-search-input').addEventListener('input', function() {
        clearTimeout(brandSearchTimer);
        brandSearchTimer = setTimeout(() => fetchBrandList(1), 350);
    });

    document.getElementById('brand-status-filter').addEventListener('change', () => fetchBrandList(1));

    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#brand-pagination-links .page-link');
        if (pageLink) {
            e.preventDefault();
            const href = pageLink.getAttribute('href');
            if (href) {
                const url = new URL(href);
                const page = url.searchParams.get('page') || 1;
                fetchBrandList(page);
            }
        }
    });

    function fetchBrandList(page = 1) {
        const form = document.getElementById('brand-filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.append('page', page);

        const tbody = document.getElementById('brand-table-tbody');
        tbody.style.opacity = '0.35';

        fetch("{{ route('admin.product.brand') }}?" + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            tbody.innerHTML = data.table_html;
            tbody.style.opacity = '1';
            document.getElementById('brand-pagination-links').innerHTML = data.pagination_html;
            document.getElementById('brand-total-badge').innerText = data.total_count;
            if (data.entries_text) {
                document.getElementById('brand-showing-entries').innerText = data.entries_text;
            }
        })
        .catch(err => {
            tbody.style.opacity = '1';
        });
    }

    function showBrandToast(title, message, type = 'success') {
        const holder = document.getElementById('brand-toast-holder');
        const toastId = 'toast-' + Date.now();

        const typeConfigs = {
            success: { icon: 'fa-check-circle', color: 'bg-success text-white' },
            danger:  { icon: 'fa-trash-alt',    color: 'bg-danger text-white' },
            info:    { icon: 'fa-info-circle',   color: 'bg-primary text-white' }
        };
        const config = typeConfigs[type] || typeConfigs.success;

        const html = `
            <div id="${toastId}" class="admin-toast-box ${config.color} p-3 d-flex align-items-center justify-content-between shadow-lg">
                <div class="d-flex align-items-center gap-3">
                    <i class="fas ${config.icon} fa-lg"></i>
                    <div>
                        <strong class="d-block" style="font-size: 13px;">${title}</strong>
                        <small style="font-size: 11.5px;">${message}</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-3" onclick="document.getElementById('${toastId}').remove()"></button>
            </div>
        `;
        holder.insertAdjacentHTML('beforeend', html);
        setTimeout(() => {
            const el = document.getElementById(toastId);
            if (el) el.remove();
        }, 3500);
    }
</script>
@endsection