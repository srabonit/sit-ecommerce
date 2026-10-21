@extends('layouts.admin')

@section('title', 'Product Categories - ' . config('app.name'))

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #admin-category-root .btn,
    #admin-category-root .btn-sm,
    #admin-category-root button,
    #admin-category-root .form-control,
    #admin-category-root .form-select,
    #admin-category-root .badge,
    #admin-category-root .input-group-text,
    #admin-category-root .page-link,
    #admin-category-root .page-item span,
    #categoryFormModal .btn,
    #categoryFormModal .form-control,
    #categoryFormModal .form-select,
    #categoryFormModal .modal-content,
    #categoryDeleteModal .btn,
    #categoryDeleteModal .modal-content,
    #catNoticeModal .btn,
    #catNoticeModal .modal-content,
    .admin-toast-box {
        border-radius: 5px !important;
    }

    #admin-category-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #admin-category-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Compact & Sleek Button Sizing */
    #admin-category-root .btn-compact,
    #categoryFormModal .btn-compact,
    #categoryDeleteModal .btn-compact,
    #catNoticeModal .btn-compact {
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
    .cat-action-sq-btn {
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
    .cat-main-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }
    .cat-table-box {
        background: #ffffff;
        border-radius: 6px !important;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .cat-table th {
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
    .cat-table td {
        padding: 10px 14px;
        vertical-align: middle;
        white-space: nowrap;
        font-size: 12.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }
    .cat-table tbody tr:hover {
        background-color: #fafbfc;
    }

    /* Filter Bar Box */
    .cat-filter-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px !important;
        padding: 12px 14px;
    }
    .cat-filter-control {
        font-size: 12px !important;
        height: 33px !important;
        padding: 4px 10px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        box-shadow: none !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .cat-filter-control:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        outline: none;
    }
    .cat-addon-icon {
        height: 33px !important;
        background-color: #f8fafc !important;
        border: 1px solid #cbd5e1 !important;
        border-right: 0 !important;
        color: #64748b !important;
    }

    /* Modal Form Controls & Media Chooser Box */
    #categoryFormModal .form-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 4px;
    }
    #categoryFormModal .form-control,
    #categoryFormModal .form-select {
        font-size: 12.5px;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
    }
    #categoryFormModal .form-control:focus,
    #categoryFormModal .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }
    .cat-media-picker-box {
        background: #f8fafc;
        border: 1.5px dashed #cbd5e1;
        border-radius: 6px !important;
        padding: 12px;
        transition: border-color 0.15s ease;
    }
    .cat-media-picker-box:hover {
        border-color: #93c5fd;
    }
    .btn-clear-cat-img {
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
    #categoryFormModal .modal-dialog-scrollable .modal-content {
        max-height: calc(100vh - 40px) !important;
        display: flex !important;
        flex-direction: column !important;
    }
    #categoryFormModal .modal-body {
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
    #cat-pagination-links {
        width: 100% !important;
        display: flex !important;
        align-items: center !important;
    }
    @media (min-width: 768px) {
        #cat-pagination-links {
            width: auto !important;
        }
    }
    #cat-pagination-links nav {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    /* Hide Laravel's mobile-only prev/next block so numbered pagination shows everywhere */
    #cat-pagination-links nav > div:first-child.d-sm-none {
        display: none !important;
    }
    #cat-pagination-links nav > div:last-child,
    #cat-pagination-links nav > div.d-sm-flex {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        gap: 8px !important;
    }
    @media (min-width: 768px) {
        #cat-pagination-links nav > div:last-child,
        #cat-pagination-links nav > div.d-sm-flex {
            flex-direction: row !important;
            justify-content: flex-end !important;
        }
    }
    #cat-pagination-links nav p.text-muted,
    #cat-pagination-links nav div:has(> p.text-muted) {
        display: none !important;
    }
    #cat-pagination-links .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
        align-items: center !important;
        gap: 5px !important;
        padding: 0 !important;
        list-style: none !important;
    }
    #cat-pagination-links .page-item {
        margin: 0 !important;
    }
    #cat-pagination-links .page-item .page-link,
    #cat-pagination-links .page-item span {
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
    #cat-pagination-links .page-item .page-link:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }
    #cat-pagination-links .page-item.active .page-link,
    #cat-pagination-links .page-item.active span {
        color: #ffffff !important;
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        font-weight: 700 !important;
    }
    #cat-pagination-links .page-item.disabled .page-link,
    #cat-pagination-links .page-item.disabled span {
        color: #cbd5e1 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        pointer-events: none !important;
        opacity: 0.7 !important;
    }
    #cat-pagination-links nav svg {
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
        #cat-top-header h5 {
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
        .cat-table th,
        .cat-table td {
            padding: 9px 10px !important;
            font-size: 12px;
        }
        #cat-pagination-links .pagination {
            gap: 4px !important;
        }
        #cat-pagination-links .page-item .page-link,
        #cat-pagination-links .page-item span {
            min-width: 27px !important;
            height: 27px !important;
            padding: 2px 6px !important;
            font-size: 11px !important;
        }
    }
</style>

<div class="cat-main-card p-3 p-md-4 mb-4" id="admin-category-root">
    
    <!-- Top Header & Action -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 pb-3 border-bottom" id="cat-top-header">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center" style="font-size: 18px;">
                    <i class="fas fa-sitemap text-primary me-2"></i>
                    <span>Product Categories</span>
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 bn-digit font-monospace" id="category-total-badge" style="font-size: 11px;">
                    {{ $categories->total() }}
                </span>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">Manage parent collections and sub-categories in structured hierarchy.</small>
        </div>
        <div class="header-add-btn-wrap">
            <button type="button" class="btn btn-warning btn-compact px-3 py-2 fw-bold text-dark shadow-xs" onclick="openAddCategoryModal()">
                <i class="fas fa-plus me-1"></i>
                <span>Add New Category</span>
            </button>
        </div>
    </div>

    <!-- Filter & Live Search Bar -->
    <div class="cat-filter-box mb-3" id="cat-filter-wrapper">
        <form id="cat-filter-form" onsubmit="return false;">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text cat-addon-icon"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                        <input type="text" name="search" id="cat-search-input" class="form-control cat-filter-control" placeholder="Search category name, slug or ID...">
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <select name="parent_filter" id="cat-parent-filter" class="form-select cat-filter-control cat-filter-trigger">
                        <option value="">All Categories (Full Hierarchy)</option>
                        <option value="root">Parent Categories Only</option>
                        <option value="child">Sub-categories Only</option>
                        @foreach($allParentCategories as $pCat)
                            <option value="{{ $pCat->id }}">Under {{ $pCat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 text-md-end">
                    <small class="text-secondary fw-semibold" style="font-size: 11.5px;"> Real-time Search</small>
                </div>
            </div>
        </form>
    </div>

    <!-- Category Table -->
    <div class="cat-table-box">
        <div class="table-responsive" style="-webkit-overflow-scrolling: touch;">
            <table class="table align-middle cat-table mb-0" id="categories-data-table">
                <thead>
                    <tr>
                        <th class="text-center">ID</th>
                        <th class="text-center">Thumb / Icon</th>
                        <th>Category Name</th>
                        <th>Slug</th>
                        <th>Parent Category</th>
                        <th>Items Count</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody id="category-table-tbody">
                    @include('admin.category.partials.table_rows', ['categories' => $categories])
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Footer -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 mt-3 pt-2 border-top" id="cat-pagination-footer">
        <div class="small text-muted text-center text-md-start bn-digit" id="cat-showing-entries" style="font-size: 11.5px;">
            Showing {{ $categories->firstItem() ?? 0 }} to {{ $categories->lastItem() ?? 0 }} of {{$categories->total() }} entries
        </div>
        <div id="cat-pagination-links" class="overflow-auto d-flex justify-content-center justify-content-md-end">
            {{ $categories->links('pagination::bootstrap-5') }}
        </div>
    </div>

</div>

<!-- ================= Modal: Add / Edit Category ================= -->
<div class="modal fade" id="categoryFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <form id="categoryAjaxForm" class="modal-content border-0 shadow-lg overflow-hidden" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="category_id" id="cat_edit_id" value="">
            <input type="hidden" name="category_type" id="cat_edit_type" value="parent">
            
            <!-- Media Library & Remove Image Flags -->
            <input type="hidden" name="image_media" id="cat_input_image_media" value="">
            <input type="hidden" name="remove_image" id="cat_remove_image" value="0">
            
            <div class="modal-header border-bottom px-3 py-2 bg-light">
                <h6 class="modal-title fw-bold text-dark mb-0" id="catModalLabel" style="font-size: 14px;">
                    <i class="fas fa-plus-circle text-primary me-1"></i> Add New Category
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-3">
                <div class="row g-3">
                    <!-- Category Name -->
                    <div class="col-12 col-md-6">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="cat_input_name" class="form-control" placeholder=" Punjabi or Ladies Fashion" required oninput="generateSlugAuto(this.value)">
                    </div>

                    <!-- Slug -->
                    <div class="col-12 col-md-6">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" id="cat_input_slug" class="form-control font-monospace" placeholder="punjabi-collection">
                        <small class="text-muted d-block mt-1" style="font-size: 10.5px;">SEO friendly URL identifier.</small>
                    </div>

                    <!-- Parent Category Dropdown -->
                    <div class="col-12 col-md-6">
                        <label class="form-label">Parent Category</label>
                        <select name="parent_id" id="cat_input_parent_id" class="form-select">
                            <option value="">None (Make this a Parent Category)</option>
                            @foreach($allParentCategories as $pCat)
                                <option value="{{ $pCat->id }}">{{ $pCat->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1" style="font-size: 10.5px;">Select a parent category to make it a sub-category.</small>
                    </div>

                    <!-- Icon Class -->
                    <div class="col-12 col-md-6">
                        <label class="form-label">FontAwesome Icon</label>
                        <input type="text" name="icon" id="cat_input_icon" class="form-control font-monospace" placeholder="fas fa-tshirt">
                        <small class="text-muted d-block mt-1" style="font-size: 10.5px;"> fas fa-tshirt, fas fa-female, fas fa-clock</small>
                    </div>

                    <!-- Image / Thumbnail with Global Media Picker -->
                    <div class="col-12" id="cat-image-upload-wrapper">
                        <label class="form-label">Category Image (For Parent Category)</label>
                        <div class="cat-media-picker-box">
                            <div class="media-trigger-bar d-flex align-items-center gap-2 mb-2">
                                <button type="button" class="btn btn-outline-primary btn-compact shadow-xs" onclick="chooseCategoryImageMedia()">
                                    <i class="fas fa-images me-1"></i>
                                    <span>Choose from Media Library</span>
                                </button>
                                <span class="small text-muted" style="font-size: 11px;">or upload local file:</span>
                            </div>

                            <input type="file" name="image" id="cat_input_image" class="form-control form-control-sm mb-1" accept="image/*" onchange="previewCatLocalImage(this)">
                            
                            <div id="cat_image_preview_box" class="mt-2 position-relative d-inline-block" style="display: none;">
                                <img id="cat_image_preview" src="" class="border object-fit-cover shadow-xs bg-white p-1" style="width: 60px; height: 60px; border-radius: 5px !important;">
                                <button type="button" class="btn-clear-cat-img" onclick="clearCategoryImage()" title="Remove Image">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="cat_input_description" class="form-control" rows="2" placeholder="Write a short summary about this category..."></textarea>
                    </div>

                    <!-- Active Status Switch -->
                    <div class="col-12">
                        <div class="p-2 px-3 bg-light border d-flex justify-content-between align-items-center" style="border-radius: 5px;">
                            <div>
                                <strong class="d-block text-dark" style="font-size: 12px;">Enable / Active Status</strong>
                                <small class="text-muted" style="font-size: 11px;">Visible in storefront and product assignment dropdowns.</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input m-0" type="checkbox" role="switch" name="is_active" id="cat_input_is_active" value="1" checked style="width: 40px; height: 20px; cursor: pointer;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top px-3 py-2 bg-light">
                <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning btn-compact px-3 fw-bold text-dark shadow-xs" id="btn-save-category">
                    <i class="fas fa-save me-1"></i>
                    <span>Save Category</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= Modal: Confirm Delete ================= -->
<div class="modal fade" id="categoryDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg overflow-hidden text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2 bg-danger-subtle text-danger mx-auto" style="width: 48px; height: 48px;">
                    <i class="fas fa-trash-alt fs-5"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Delete Category?</h6>
                <p class="text-secondary small mb-3" id="cat-delete-msg" style="font-size: 12px; line-height: 1.45;">Are you sure you want to delete this category?</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-compact px-3 shadow-xs" id="btn-confirm-delete-cat">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= Modal: Notice (Replaces raw alert()) ================= -->
<div class="modal fade" id="catNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 44px; height: 44px;">
                    <i class="fas fa-exclamation-triangle fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="catNoticeTitle" style="font-size: 14px;">Notice</h6>
                <p class="text-muted small mb-3" id="catNoticeMsg" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="cat-toast-holder"></div>

<script>
    function showCatNotice(message, title = 'Notice') {
        const titleEl = document.getElementById('catNoticeTitle');
        const msgEl = document.getElementById('catNoticeMsg');
        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;
        const modalEl = document.getElementById('catNoticeModal');
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

    // Auto Slug
    function generateSlugAuto(text) {
        const editId = document.getElementById('cat_edit_id').value;
        if (!editId) {
            document.getElementById('cat_input_slug').value = text.toLowerCase().trim()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    }

    // Media Library Trigger
    function chooseCategoryImageMedia() {
        if (typeof window.openMediaPicker === 'function') {
            window.openMediaPicker({
                multiple: false,
                onSelect: function(media) {
                    const picked = extractMediaItem(media);
                    if (picked && picked.path) {
                        document.getElementById('cat_input_image_media').value = picked.path;
                        const fileInput = document.getElementById('cat_input_image');
                        if (fileInput) fileInput.value = '';
                        document.getElementById('cat_remove_image').value = '0';

                        const img = document.getElementById('cat_image_preview');
                        img.src = picked.url;
                        document.getElementById('cat_image_preview_box').style.display = 'inline-block';
                    }
                }
            });
        } else {
            showCatNotice('Media Library is loading, please try again.');
        }
    }

    // Local File Preview
    function previewCatLocalImage(input) {
        if (input.files && input.files[0]) {
            document.getElementById('cat_input_image_media').value = '';
            document.getElementById('cat_remove_image').value = '0';
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('cat_image_preview').src = e.target.result;
                document.getElementById('cat_image_preview_box').style.display = 'inline-block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Clear Selected Image
    function clearCategoryImage() {
        const fileInput = document.getElementById('cat_input_image');
        if (fileInput) fileInput.value = '';
        document.getElementById('cat_input_image_media').value = '';
        document.getElementById('cat_remove_image').value = '1';
        document.getElementById('cat_image_preview').src = '';
        document.getElementById('cat_image_preview_box').style.display = 'none';
    }

    // Open Add Modal
    function openAddCategoryModal() {
        document.getElementById('categoryAjaxForm').reset();
        document.getElementById('cat_edit_id').value = '';
        document.getElementById('cat_edit_type').value = 'parent';
        document.getElementById('cat_input_image_media').value = '';
        document.getElementById('cat_remove_image').value = '0';
        document.getElementById('catModalLabel').innerHTML = '<i class="fas fa-plus-circle text-primary me-1"></i> Add New Category';
        document.getElementById('cat_image_preview').src = '';
        document.getElementById('cat_image_preview_box').style.display = 'none';
        document.getElementById('cat_input_is_active').checked = true;

        fetch("{{ route('admin.product.category') }}?get_parents=1", {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        bootstrap.Modal.getOrCreateInstance(document.getElementById('categoryFormModal')).show();
    }

    // Save or Update Submit
    document.getElementById('categoryAjaxForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const editId = document.getElementById('cat_edit_id').value;
        const formData = new FormData(this);
        const saveBtn = document.getElementById('btn-save-category');

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span><span>Saving...</span>';

        let url = "{{ route('admin.product.category.store') }}";
        if (editId) {
            url = "{{ url('admin/product/category/update') }}/" + editId;
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
            saveBtn.innerHTML = '<i class="fas fa-save me-1"></i><span>Save Category</span>';

            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('categoryFormModal')).hide();
                fetchCategoryList(1);
                showCatToast('Success', data.message, 'success');
            } else {
                showCatToast('Error', data.message || 'Failed to save category.', 'danger');
            }
        })
        .catch(err => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save me-1"></i><span>Save Category</span>';
            showCatToast('Error', 'Validation error or server problem.', 'danger');
        });
    });

    // Edit Category
    document.addEventListener('click', function(e) {
        const editBtn = e.target.closest('.btn-edit-category');
        if (editBtn) {
            const id = editBtn.dataset.id;
            const type = editBtn.dataset.type;

            fetch(`{{ url('admin/product/category/edit') }}/${id}?type=${type}`)
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    const d = res.data;
                    document.getElementById('cat_edit_id').value = d.id;
                    document.getElementById('cat_edit_type').value = d.type;
                    document.getElementById('cat_input_name').value = d.name;
                    document.getElementById('cat_input_slug').value = d.slug;
                    document.getElementById('cat_input_icon').value = d.icon || '';
                    document.getElementById('cat_input_description').value = d.description || '';
                    document.getElementById('cat_input_is_active').checked = (d.is_active == 1);
                    document.getElementById('cat_input_image_media').value = '';
                    document.getElementById('cat_remove_image').value = '0';
                    const fileInput = document.getElementById('cat_input_image');
                    if (fileInput) fileInput.value = '';

                    const parentSelect = document.getElementById('cat_input_parent_id');
                    parentSelect.innerHTML = '<option value="">None (Make this a Parent Category)</option>';
                    if (res.parents && res.parents.length > 0) {
                        res.parents.forEach(p => {
                            const isSelected = (d.parent_id && d.parent_id == p.id) ? 'selected' : '';
                            parentSelect.innerHTML += `<option value="${p.id}" ${isSelected}>${p.name}</option>`;
                        });
                    }

                    if (d.image_url || d.image) {
                        let resolvedImg = d.image_url;
                        if (!resolvedImg) {
                            let imgPath = d.image.replace(/^\/+/, '');
                            if (imgPath.startsWith('http://') || imgPath.startsWith('https://')) {
                                resolvedImg = imgPath;
                            } else if (imgPath.startsWith('uploads/') || imgPath.startsWith('storage/')) {
                                resolvedImg = "{{ asset('') }}" + imgPath;
                            } else {
                                resolvedImg = "{{ asset('uploads/') }}/" + imgPath;
                            }
                        }
                        document.getElementById('cat_image_preview').src = resolvedImg;
                        document.getElementById('cat_image_preview_box').style.display = 'inline-block';
                    } else {
                        document.getElementById('cat_image_preview').src = '';
                        document.getElementById('cat_image_preview_box').style.display = 'none';
                    }

                    document.getElementById('catModalLabel').innerHTML = `<i class="fas fa-edit text-primary me-1"></i> Edit ${d.type === 'child' ? 'Sub-category' : 'Category'}: ${d.name}`;
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('categoryFormModal')).show();
                }
            });
        }
    });

    // Delete Category
    let pendingDeleteId = null;
    let pendingDeleteType = 'parent';
    document.addEventListener('click', function(e) {
        const delBtn = e.target.closest('.btn-delete-category');
        if (delBtn) {
            pendingDeleteId = delBtn.dataset.id;
            pendingDeleteType = delBtn.dataset.type;
            const name = delBtn.dataset.name;
            document.getElementById('cat-delete-msg').innerHTML = `Are you sure you want to delete "<strong>${name}</strong>"?`;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('categoryDeleteModal')).show();
        }
    });

    document.getElementById('btn-confirm-delete-cat').addEventListener('click', function() {
        if (!pendingDeleteId) return;

        fetch(`{{ url('admin/product/category/delete') }}/${pendingDeleteId}?type=${pendingDeleteType}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            bootstrap.Modal.getInstance(document.getElementById('categoryDeleteModal')).hide();
            fetchCategoryList(1);
            showCatToast('Deleted', data.message, 'danger');
        })
        .catch(err => {
            bootstrap.Modal.getInstance(document.getElementById('categoryDeleteModal')).hide();
            showCatToast('Error', 'Failed to delete category.', 'danger');
        });
    });

    // Status Toggle
    function toggleCatStatusAjax(id, type, switchEl) {
        fetch(`{{ url('admin/product/category/toggle-status') }}/${id}?type=${type}`, {
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
                showCatToast('Updated', data.message, 'info');
            }
        });
    }

    // Live Search & Filter
    let catSearchTimer = null;
    document.getElementById('cat-search-input').addEventListener('input', function() {
        clearTimeout(catSearchTimer);
        catSearchTimer = setTimeout(() => fetchCategoryList(1), 350);
    });

    document.getElementById('cat-parent-filter').addEventListener('change', () => fetchCategoryList(1));

    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#cat-pagination-links .page-link');
        if (pageLink) {
            e.preventDefault();
            const href = pageLink.getAttribute('href');
            if (href) {
                const url = new URL(href);
                const page = url.searchParams.get('page') || 1;
                fetchCategoryList(page);
            }
        }
    });

    function fetchCategoryList(page = 1) {
        const form = document.getElementById('cat-filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.append('page', page);

        const tbody = document.getElementById('category-table-tbody');
        tbody.style.opacity = '0.35';

        fetch("{{ route('admin.product.category') }}?" + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            tbody.innerHTML = data.table_html;
            tbody.style.opacity = '1';
            document.getElementById('cat-pagination-links').innerHTML = data.pagination_html;
            document.getElementById('category-total-badge').innerText = data.total_count;
            if (data.entries_text) {
                document.getElementById('cat-showing-entries').innerText = data.entries_text;
            }
        })
        .catch(err => {
            tbody.style.opacity = '1';
        });
    }

    function showCatToast(title, message, type = 'success') {
        const holder = document.getElementById('cat-toast-holder');
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