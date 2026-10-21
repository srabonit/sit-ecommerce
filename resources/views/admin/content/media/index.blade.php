@extends('layouts.admin')

@section('title', 'Media Library - ' . config('app.name'))

@section('admin_content')
@php
    $currentCategory = $activeCategory ?? request('category', 'site_content');
    $counts = $categoryCounts ?? [
        'site_content'   => 0,
        'user_submitted' => 0,
        'all'            => $files->total(),
    ];
@endphp

<style>
    /* Global Strict 5px Element Radius */
    #mediaLibraryRoot .btn,
    #mediaLibraryRoot .btn-sm,
    #mediaLibraryRoot .btn-xs,
    #mediaLibraryRoot button,
    #mediaLibraryRoot .form-control,
    #mediaLibraryRoot .form-select,
    #mediaLibraryRoot .badge,
    #mediaLibraryRoot .input-group-text,
    #replaceMediaModal .btn,
    #replaceMediaModal .form-control,
    #usageTrackingModal .btn,
    #mediaNoticeModal .btn,
    #mediaConfirmModal .btn {
        border-radius: 5px !important;
    }

    #mediaLibraryRoot .input-group > .form-control {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #mediaLibraryRoot .input-group > .btn {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Compact & Sleek Button Sizing */
    #mediaLibraryRoot .btn-compact,
    #replaceMediaModal .btn-compact,
    #usageTrackingModal .btn-compact,
    #mediaNoticeModal .btn-compact,
    #mediaConfirmModal .btn-compact {
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

    /* Enterprise Card Surface */
    .media-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    /* Category Section Tabs (Site Content vs User Submitted) */
    .media-section-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 14px;
    }
    .media-section-tab {
        padding: 8px 16px;
        font-size: 12.5px;
        font-weight: 700;
        color: #475569;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 6px !important;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.15s ease;
    }
    .media-section-tab:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    .media-section-tab.active-site {
        background: #03172e;
        color: #ffffff !important;
        border-color: #03172e;
        box-shadow: 0 2px 6px rgba(3, 23, 46, 0.15);
    }
    .media-section-tab.active-user {
        background: #0f766e;
        color: #ffffff !important;
        border-color: #0f766e;
        box-shadow: 0 2px 6px rgba(15, 118, 110, 0.18);
    }

    /* Form Controls */
    #mediaLibraryRoot .form-label {
        font-size: 11px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    #mediaLibraryRoot .form-control,
    #mediaLibraryRoot .form-select {
        font-size: 12.5px;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #mediaLibraryRoot .form-control:focus,
    #mediaLibraryRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Collapsible Upload Dropzone */
    .wp-upload-collapsible {
        border: 2px dashed #94a3b8;
        border-radius: 8px !important;
        padding: 28px 20px;
        text-align: center;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        margin-bottom: 16px;
        transition: all 0.2s ease;
        position: relative;
    }
    .wp-upload-collapsible:hover,
    .wp-upload-collapsible.dragover {
        border-color: #2563eb;
        background: #eff6ff;
    }
    .upload-icon-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #e0f2fe;
        color: #2563eb;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 10px;
    }

    /* Media Grid System */
    .media-card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(125px, 1fr));
        gap: 10px;
        max-height: 72vh;
        overflow-y: auto;
        padding: 2px;
    }
    .media-card-grid::-webkit-scrollbar {
        width: 5px;
    }
    .media-card-grid::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 4px;
    }

    .media-card-item {
        aspect-ratio: 1 / 1;
        border: 2px solid #e2e8f0;
        border-radius: 6px !important;
        overflow: hidden;
        cursor: pointer;
        position: relative;
        background: #f8fafc;
        transition: transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .media-card-item:hover {
        border-color: #94a3b8;
        transform: translateY(-1px);
    }
    .media-card-item.selected {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.25);
    }
    .media-card-item.selected::after {
        content: "\f00c";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        position: absolute;
        top: 6px;
        right: 6px;
        width: 20px;
        height: 20px;
        background: #2563eb;
        color: #ffffff;
        border-radius: 4px;
        font-size: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        z-index: 4;
    }
    .bulk-select-active .media-card-item.selected::after {
        display: none;
    }
    .media-card-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .media-card-source-badge {
        position: absolute;
        bottom: 4px;
        left: 4px;
        right: 4px;
        background: rgba(15, 23, 42, 0.72);
        color: #ffffff;
        font-size: 9.5px;
        padding: 2px 5px;
        border-radius: 4px;
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
        pointer-events: none;
    }

    /* Bulk Checkbox */
    .bulk-checkbox {
        position: absolute;
        top: 6px;
        left: 6px;
        width: 18px;
        height: 18px;
        display: none;
        z-index: 6;
        cursor: pointer;
        border: 1.5px solid #475569;
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }
    .bulk-select-active .bulk-checkbox {
        display: block;
    }

    /* Bulk Action Bar */
    .bulk-toolbar-strip {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 6px;
        padding: 8px 12px;
    }

    /* Attachment Details Sidebar */
    .attachment-details-sidebar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        padding: 16px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        position: sticky;
        top: 76px;
    }
    .sidebar-preview-stage {
        background-color: #f8fafc;
        background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
        background-size: 10px 10px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 10px;
        text-align: center;
    }
    .sidebar-meta-list {
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 6px;
        padding: 10px 12px;
        font-size: 11.5px;
    }

    /* Pagination Styling */
    .media-pagination-wrap nav {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px;
        width: 100% !important;
    }
    @media (min-width: 768px) {
        .media-pagination-wrap nav {
            flex-direction: row !important;
            justify-content: space-between !important;
        }
    }
    .media-pagination-wrap .pagination {
        margin: 0 !important;
        gap: 4px;
        flex-wrap: wrap;
        justify-content: center;
    }
    .media-pagination-wrap .page-link {
        border-radius: 5px !important;
        font-size: 12px;
        padding: 4px 10px;
        color: #475569;
        border-color: #e2e8f0;
    }
    .media-pagination-wrap .page-item.active .page-link {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700;
    }

    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    @media (max-width: 767.98px) {
        .header-top-actions {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .header-top-actions .btn-compact {
            flex: 1 1 auto;
            padding: 7px 10px !important;
        }
        .media-card-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            max-height: 55vh;
        }
        .attachment-details-sidebar {
            position: static;
            padding: 14px;
        }
        .wp-upload-collapsible {
            padding: 20px 14px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="mediaLibraryRoot">
    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <div>
            <h5 class="fw-bold mb-1 text-dark d-flex align-items-center" style="font-size: 18px;">
                <i class="fas fa-photo-video text-primary me-2"></i>
                <span>Central Media &amp; File Library</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Control all store images, product variants, banners, and user-submitted files (payment proofs, forms, profiles) from one place.
            </small>
        </div>
        <div class="header-top-actions d-flex gap-2">
            <a href="{{ route('admin.media.index', ['category' => $currentCategory, 'resync' => 1]) }}" class="btn btn-outline-primary btn-compact bg-white shadow-xs" title="Scan & Sync All Site Folders">
                <i class="fas fa-arrows-rotate me-1"></i>
                <span>Sync All Files</span>
            </a>
            <button type="button" class="btn btn-primary btn-compact shadow-xs" onclick="toggleUploadBox()">
                <i class="fas fa-plus me-1" style="font-size: 10px;"></i>
                <span>Add New Media</span>
            </button>
            <button type="button" id="btn-bulk-toggle" class="btn btn-outline-secondary btn-compact bg-white shadow-xs" onclick="toggleBulkMode()">
                <span>Bulk Select</span>
            </button>
        </div>
    </div>

    <!-- Separate Sections: Site Content Media vs User Submitted Files -->
    <div class="media-section-tabs">
        <a href="{{ route('admin.media.index', ['category' => 'site_content']) }}"
           class="media-section-tab {{ $currentCategory === 'site_content' ? 'active-site' : '' }}">
            <i class="fas fa-images text-info"></i>
            <span>Site Content Media (Products, Variants, Theme, Pages)</span>
            <span class="badge bg-primary text-white px-2 py-0.5">{{ $counts['site_content'] ?? 0 }}</span>
        </a>

        <a href="{{ route('admin.media.index', ['category' => 'user_submitted']) }}"
           class="media-section-tab {{ $currentCategory === 'user_submitted' ? 'active-user' : '' }}">
            <i class="fas fa-file-shield text-warning"></i>
            <span>User Submitted Files (Payment Proofs, Forms, Profiles)</span>
            <span class="badge bg-warning text-dark px-2 py-0.5">{{ $counts['user_submitted'] ?? 0 }}</span>
        </a>

        <a href="{{ route('admin.media.index', ['category' => 'all']) }}"
           class="media-section-tab {{ $currentCategory === 'all' ? 'active-site' : '' }}">
            <i class="fas fa-layer-group"></i>
            <span>All Site Files</span>
            <span class="badge bg-secondary text-white px-2 py-0.5">{{ $counts['all'] ?? 0 }}</span>
        </a>
    </div>

    <!-- Collapsible Dropzone -->
    <div id="wp-collapsible-upload-box" class="wp-upload-collapsible shadow-xs" style="display: none;">
        <div class="upload-icon-circle">
            <i class="fas fa-cloud-upload-alt"></i>
        </div>
        <h6 class="fw-bold text-dark mb-1" style="font-size: 15px;">Drop files anywhere to upload</h6>
        <p class="text-muted small mb-2" style="font-size: 12px;">
            Duplicate filenames are automatically renamed as <strong>(1), (2)</strong> — no file is ever rejected!
        </p>
        <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
            <select id="upload-target-category" class="form-select form-select-sm" style="max-width: 220px;">
                <option value="site_content" {{ $currentCategory !== 'user_submitted' ? 'selected' : '' }}>Upload to: Site Content Media</option>
                <option value="user_submitted" {{ $currentCategory === 'user_submitted' ? 'selected' : '' }}>Upload to: User Submitted Files</option>
            </select>
            <label class="btn btn-primary btn-compact px-4 py-2 shadow-xs mb-0" style="cursor: pointer;">
                <i class="fas fa-folder-open me-1"></i>
                <span>Select Files</span>
                <input type="file" multiple class="d-none" accept="image/*,.pdf" onchange="uploadDashboardMedia(this.files)">
            </label>
        </div>
        <div class="text-muted bn-digit" style="font-size: 11px;">Maximum upload file size: 20 MB (JPG, PNG, WEBP, GIF, SVG, PDF).</div>
        <div id="upload-progress-indicator" class="d-none mt-2 text-primary small fw-semibold">
            <i class="fas fa-spinner fa-spin me-1"></i> Uploading media files, please wait...
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="media-panel-card p-3 mb-3">
        <form method="GET" action="{{ route('admin.media.index') }}" class="row g-2 align-items-center">
            <input type="hidden" name="category" value="{{ $currentCategory }}">

            <div class="col-6 col-md-2">
                <select name="type" class="form-select form-select-sm fw-semibold" onchange="this.form.submit()">
                    <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>All File Types</option>
                    <option value="image" {{ request('type') == 'image' ? 'selected' : '' }}>Images Only</option>
                    <option value="pdf" {{ request('type') == 'pdf' ? 'selected' : '' }}>PDF Documents</option>
                </select>
            </div>

            <div class="col-6 col-md-3">
                <select name="source_module" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all">All Modules / Folders</option>
                    @if($currentCategory === 'user_submitted')
                        <option value="payment_proof" {{ request('source_module') == 'payment_proof' ? 'selected' : '' }}>Payment Proofs (Checkout / Manual)</option>
                        <option value="form_submission" {{ request('source_module') == 'form_submission' ? 'selected' : '' }}>Form Submissions</option>
                        <option value="support_ticket" {{ request('source_module') == 'support_ticket' ? 'selected' : '' }}>Support Ticket Attachments</option>
                        <option value="user_profile" {{ request('source_module') == 'user_profile' ? 'selected' : '' }}>User / Staff Profiles</option>
                    @else
                        <option value="media_library" {{ request('source_module') == 'media_library' ? 'selected' : '' }}>Media Library Uploads</option>
                        <option value="product" {{ request('source_module') == 'product' ? 'selected' : '' }}>Product Main / Thumbnails</option>
                        <option value="product_variant" {{ request('source_module') == 'product_variant' ? 'selected' : '' }}>Product Variant Images</option>
                        <option value="product_gallery" {{ request('source_module') == 'product_gallery' ? 'selected' : '' }}>Product Gallery Images</option>
                        <option value="category" {{ request('source_module') == 'category' ? 'selected' : '' }}>Categories</option>
                        <option value="brand" {{ request('source_module') == 'brand' ? 'selected' : '' }}>Brands</option>
                        <option value="slider" {{ request('source_module') == 'slider' ? 'selected' : '' }}>Hero Sliders</option>
                        <option value="theme_offer" {{ request('source_module') == 'theme_offer' ? 'selected' : '' }}>Theme / Offer Banners</option>
                        <option value="blog" {{ request('source_module') == 'blog' ? 'selected' : '' }}>Blog Posts</option>
                        <option value="page" {{ request('source_module') == 'page' ? 'selected' : '' }}>Pages</option>
                    @endif
                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="date" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all">All Dates</option>
                    @foreach($dates as $d)
                        <option value="{{ $d->date_val }}" {{ request('date') == $d->date_val ? 'selected' : '' }}>{{ $d->date_label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-5 ms-auto">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search by file name, title, order ref, or path...">
                    <button class="btn btn-primary px-3" type="submit" title="Search">
                        <i class="fas fa-search" style="font-size: 11px;"></i>
                    </button>
                </div>
            </div>
        </form>

        <!-- Bulk Action Bar -->
        <div id="bulk-action-bar" class="d-none mt-3 bulk-toolbar-strip d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
            <span class="small fw-semibold text-danger">
                <span id="bulk-selected-count" class="bn-digit">0</span> media item(s) selected for bulk action
            </span>
            <button type="button" class="btn btn-danger btn-compact shadow-xs" onclick="executeBulkDelete()">
                <i class="fas fa-trash-alt me-1"></i>
                <span>Delete Selected</span>
            </button>
        </div>
    </div>

    <!-- Main Content Area: Grid + Sidebar -->
    <div class="row g-3">
        <!-- Left Column: Media Grid -->
        <div class="col-12 col-lg-8 col-xl-9">
            <div class="media-panel-card p-3">
                <div class="media-card-grid" id="main-media-grid">
                    @forelse($files as $file)
                        <div class="media-card-item" data-id="{{ $file->id }}" onclick="openDashboardDetails({{ json_encode($file) }}, this)">
                            <input type="checkbox" class="bulk-checkbox form-check-input mt-0" value="{{ $file->id }}" onclick="event.stopPropagation(); updateBulkCount();">
                            @if(str_starts_with($file->file_type ?? '', 'image/'))
                                <img src="{{ $file->url }}" alt="{{ $file->alt_text }}" loading="lazy" onerror="this.src='https://placehold.co/300x300?text=Missing'">
                            @else
                                <div class="h-100 d-flex flex-column align-items-center justify-content-center p-2 text-center bg-light">
                                    <i class="fas fa-file-pdf fa-2x text-danger mb-1"></i>
                                    <small class="text-truncate w-100 fw-semibold text-dark px-1" style="font-size: 10px;">{{ $file->file_name }}</small>
                                </div>
                            @endif
                            <div class="media-card-source-badge">
                                {{ $file->title ?: $file->file_name }}
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted py-5" style="grid-column: 1 / -1;">
                            <i class="far fa-images fa-2x mb-2 d-block opacity-25"></i>
                            <span class="small fw-semibold">No media files found in this section.</span>
                        </div>
                    @endforelse
                </div>

                @if($files->hasPages())
                    <div class="mt-3 pt-3 border-top media-pagination-wrap">
                        {{ $files->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Attachment Details Inspector -->
        <div class="col-12 col-lg-4 col-xl-3" id="inspector-column-anchor">
            <div class="attachment-details-sidebar" id="dashboard-sidebar-panel" style="display: none;">
                <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-uppercase text-dark" style="font-size: 12px; letter-spacing: 0.04em;">Attachment Details</h6>
                    <button type="button" class="btn-close btn-sm d-lg-none shadow-none" onclick="closeSidebarPanel()" aria-label="Close"></button>
                </div>
                
                <div class="sidebar-preview-stage mb-3">
                    <img id="sidebar-preview-img" src="" class="img-fluid rounded" style="max-height: 165px; object-fit: contain;">
                </div>

                <div class="sidebar-meta-list text-muted lh-base mb-3">
                    <div class="text-truncate"><strong class="text-dark" id="sidebar-meta-name"></strong></div>
                    <div id="sidebar-meta-module" class="text-primary fw-semibold"></div>
                    <div id="sidebar-meta-date" class="bn-digit"></div>
                    <div id="sidebar-meta-size" class="bn-digit"></div>
                    <div id="sidebar-meta-dims" class="bn-digit"></div>
                </div>

                <!-- Actions: Download, Open, Replace & Usage Check -->
                <div class="row g-1.5 mb-3">
                    <div class="col-6">
                        <a href="#" id="sidebar-download-btn" class="btn btn-success btn-compact w-100 shadow-xs text-decoration-none">
                            <i class="fas fa-download me-1"></i>
                            <span>Download</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="#" target="_blank" id="sidebar-open-btn" class="btn btn-outline-dark btn-compact w-100 shadow-xs text-decoration-none">
                            <i class="fas fa-external-link-alt me-1"></i>
                            <span>View Full</span>
                        </a>
                    </div>
                    <div class="col-6 mt-1.5">
                        <button type="button" class="btn btn-outline-primary btn-compact w-100 shadow-xs" onclick="openReplaceModal()">
                            <i class="fas fa-sync-alt me-1"></i>
                            <span>Replace</span>
                        </button>
                    </div>
                    <div class="col-6 mt-1.5">
                        <button type="button" class="btn btn-outline-info btn-compact w-100 shadow-xs" onclick="checkMediaUsage()">
                            <i class="fas fa-link me-1"></i>
                            <span>Usage</span>
                        </button>
                    </div>
                </div>

                <!-- Metadata Form -->
                <form id="sidebar-metadata-form" onsubmit="saveDashboardMeta(event)">
                    <input type="hidden" id="sidebar-media-id">

                    <div class="mb-2">
                        <label class="form-label">Section / Category</label>
                        <select id="sidebar-category" class="form-select form-select-sm">
                            <option value="site_content">Site Content Media</option>
                            <option value="user_submitted">User Submitted File</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Title</label>
                        <input type="text" id="sidebar-title" class="form-control form-control-sm" placeholder="Media title">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Alternative Text (Alt)</label>
                        <textarea id="sidebar-alt" class="form-control form-control-sm" rows="2" placeholder="Describe image for SEO..."></textarea>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Caption</label>
                        <textarea id="sidebar-caption" class="form-control form-control-sm" rows="2" placeholder="Optional caption..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">File URL</label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="sidebar-url" class="form-control font-monospace bg-light" style="font-size: 11px;" readonly>
                            <button type="button" class="btn btn-outline-secondary btn-compact" onclick="copySidebarUrl()">Copy</button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top gap-2">
                        <button type="submit" class="btn btn-primary btn-compact shadow-xs" id="btn-save-meta">
                            <span>Save Metadata</span>
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-compact" onclick="deleteDashboardMedia()">
                            <span>Delete</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Empty Inspector Placeholder -->
            <div class="media-panel-card p-4 text-center text-muted" id="sidebar-placeholder">
                <i class="far fa-image fa-2x mb-2 d-block opacity-25"></i>
                <p class="small mb-0" style="font-size: 12px;">Select any file from the grid to view, download, replace, or edit its metadata.</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Replace Media -->
<div class="modal fade" id="replaceMediaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg p-3" style="border-radius: 8px !important;">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center" style="font-size: 13.5px;">
                    <i class="fas fa-sync-alt text-primary me-2"></i>
                    <span>Replace Media File</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <p class="small text-muted mb-2" style="font-size: 11.5px; line-height: 1.45;">Upload a new file to replace this item. All existing products and variants will display the new image immediately.</p>
            <input type="file" id="replace-file-input" class="form-control form-control-sm mb-3" accept="image/*,.pdf">
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light border btn-compact" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-compact shadow-xs" id="btn-exec-replace" onclick="executeFileReplace()">Replace</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Usage Tracking -->
<div class="modal fade" id="usageTrackingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg p-3" style="border-radius: 8px !important;">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center" style="font-size: 14px;">
                    <i class="fas fa-link text-info me-2"></i>
                    <span>Media Usage Locations</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div id="usage-list-container" class="my-2" style="max-height: 260px; overflow-y: auto;"></div>
            <div class="text-end pt-2 border-top">
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 3: Notice / Alert Dialog -->
<div class="modal fade" id="mediaNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3" style="border-radius: 8px !important;">
            <div class="modal-body p-1">
                <div class="mb-2 text-success" id="mediaNoticeIcon">
                    <i class="fas fa-check-circle fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="mediaNoticeTitle" style="font-size: 14px;">Notice</h6>
                <p class="text-muted small mb-3" id="mediaNoticeMsg" style="font-size: 12px;"></p>
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 4: Confirmation Dialog -->
<div class="modal fade" id="mediaConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3" style="border-radius: 8px !important;">
            <div class="modal-body p-1">
                <div class="mb-2 text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="mediaConfirmTitle" style="font-size: 14px;">Confirm Delete</h6>
                <p class="text-muted small mb-3" id="mediaConfirmMsg" style="font-size: 12px; line-height: 1.45;"></p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-compact px-3 shadow-xs" id="btnMediaConfirmProceed">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentDashboardMedia = null;
let isBulkMode = false;
let pendingMediaConfirmCallback = null;

function showMediaNotice(message, title = 'Success', isError = false, onHiddenCallback = null) {
    document.getElementById('mediaNoticeTitle').innerText = title;
    document.getElementById('mediaNoticeMsg').innerText = message;
    const iconWrap = document.getElementById('mediaNoticeIcon');
    iconWrap.className = isError ? 'mb-2 text-danger' : 'mb-2 text-success';
    iconWrap.innerHTML = isError ? '<i class="fas fa-exclamation-circle fa-2x"></i>' : '<i class="fas fa-check-circle fa-2x"></i>';

    const modalEl = document.getElementById('mediaNoticeModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    if (onHiddenCallback) {
        modalEl.addEventListener('hidden.bs.modal', function handler() {
            modalEl.removeEventListener('hidden.bs.modal', handler);
            onHiddenCallback();
        });
    }
    modal.show();
}

function showMediaConfirm(title, message, onConfirm) {
    document.getElementById('mediaConfirmTitle').innerText = title;
    document.getElementById('mediaConfirmMsg').innerText = message;
    pendingMediaConfirmCallback = onConfirm;

    const modalEl = document.getElementById('mediaConfirmModal');
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('btnMediaConfirmProceed').addEventListener('click', function() {
        const modalEl = document.getElementById('mediaConfirmModal');
        const instance = bootstrap.Modal.getInstance(modalEl);
        if (instance) instance.hide();

        if (typeof pendingMediaConfirmCallback === 'function') {
            const cb = pendingMediaConfirmCallback;
            pendingMediaConfirmCallback = null;
            cb();
        }
    });

    const dropBox = document.getElementById('wp-collapsible-upload-box');
    if (dropBox) {
        ['dragenter', 'dragover'].forEach(evt => {
            dropBox.addEventListener(evt, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropBox.classList.add('dragover');
            });
        });
        ['dragleave', 'drop'].forEach(evt => {
            dropBox.addEventListener(evt, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropBox.classList.remove('dragover');
            });
        });
        dropBox.addEventListener('drop', function(e) {
            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                uploadDashboardMedia(e.dataTransfer.files);
            }
        });
    }
});

function toggleUploadBox() {
    const box = document.getElementById('wp-collapsible-upload-box');
    box.style.display = box.style.display === 'none' ? 'block' : 'none';
}

function closeSidebarPanel() {
    document.getElementById('dashboard-sidebar-panel').style.display = 'none';
    document.getElementById('sidebar-placeholder').style.display = 'block';
    document.querySelectorAll('.media-card-item').forEach(el => el.classList.remove('selected'));
}

function openDashboardDetails(media, element) {
    if (isBulkMode) {
        const cb = element.querySelector('.bulk-checkbox');
        if (cb) {
            cb.checked = !cb.checked;
            element.classList.toggle('selected', cb.checked);
            updateBulkCount();
        }
        return;
    }

    document.querySelectorAll('.media-card-item').forEach(el => el.classList.remove('selected'));
    element.classList.add('selected');

    currentDashboardMedia = media;
    document.getElementById('sidebar-placeholder').style.display = 'none';
    const panel = document.getElementById('dashboard-sidebar-panel');
    panel.style.display = 'block';

    document.getElementById('sidebar-media-id').value = media.id;
    document.getElementById('sidebar-preview-img').src = media.url;
    document.getElementById('sidebar-meta-name').innerText = media.file_name;
    document.getElementById('sidebar-meta-module').innerText = 'Module: ' + ((media.source_module || 'media_library').replace(/_/g, ' ').toUpperCase());
    document.getElementById('sidebar-meta-date').innerText = 'Uploaded: ' + (media.formatted_date || '');
    document.getElementById('sidebar-meta-size').innerText = 'File size: ' + (media.human_size || '');
    document.getElementById('sidebar-meta-dims').innerText = 'Dimensions: ' + (media.dimensions || 'N/A');

    document.getElementById('sidebar-download-btn').href = media.download_url || (`{{ url('admin/media') }}/${media.id}/download`);
    document.getElementById('sidebar-open-btn').href = media.url;

    document.getElementById('sidebar-category').value = media.media_category || 'site_content';
    document.getElementById('sidebar-alt').value = media.alt_text || '';
    document.getElementById('sidebar-title').value = media.title || '';
    document.getElementById('sidebar-caption').value = media.caption || '';
    document.getElementById('sidebar-url').value = media.url;

    if (window.innerWidth < 992) {
        document.getElementById('inspector-column-anchor').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function copySidebarUrl() {
    const url = document.getElementById('sidebar-url');
    url.select();
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url.value);
    } else {
        document.execCommand('copy');
    }
    showMediaNotice('File URL copied to clipboard!', 'Copied');
}

function saveDashboardMeta(e) {
    e.preventDefault();
    const id = document.getElementById('sidebar-media-id').value;
    const btn = document.getElementById('btn-save-meta');
    if (btn) btn.disabled = true;

    fetch(`{{ url('admin/media') }}/${id}/update`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            media_category: document.getElementById('sidebar-category').value,
            alt_text: document.getElementById('sidebar-alt').value,
            title: document.getElementById('sidebar-title').value,
            caption: document.getElementById('sidebar-caption').value
        })
    })
    .then(res => res.json())
    .then(data => {
        showMediaNotice(data.message || 'Metadata saved successfully.');
    })
    .finally(() => {
        if (btn) btn.disabled = false;
    });
}

function uploadDashboardMedia(files) {
    if (!files.length) return;
    const prog = document.getElementById('upload-progress-indicator');
    if (prog) prog.classList.remove('d-none');

    const targetCategory = document.getElementById('upload-target-category')?.value || '{{ $currentCategory }}';
    const formData = new FormData();
    for (let i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }
    formData.append('media_category', targetCategory);
    formData.append('_token', '{{ csrf_token() }}');

    fetch("{{ route('admin.media.upload') }}", {
        method: 'POST',
        body: formData
    }).then(res => res.json()).then(data => {
        if (data.status === 'success') {
            location.reload();
        } else {
            if (prog) prog.classList.add('d-none');
            showMediaNotice('Failed to upload selected files.', 'Upload Error', true);
        }
    }).catch(() => {
        if (prog) prog.classList.add('d-none');
        showMediaNotice('Error occurred while uploading files.', 'Upload Error', true);
    });
}

function openReplaceModal() {
    bootstrap.Modal.getOrCreateInstance(document.getElementById('replaceMediaModal')).show();
}

function executeFileReplace() {
    const file = document.getElementById('replace-file-input').files[0];
    if (!file || !currentDashboardMedia) return;

    const btn = document.getElementById('btn-exec-replace');
    if (btn) btn.disabled = true;

    const formData = new FormData();
    formData.append('replacement_file', file);
    formData.append('_token', '{{ csrf_token() }}');

    fetch(`{{ url('admin/media') }}/${currentDashboardMedia.id}/replace`, {
        method: 'POST',
        body: formData
    }).then(res => res.json()).then(data => {
        const repModal = bootstrap.Modal.getInstance(document.getElementById('replaceMediaModal'));
        if (repModal) repModal.hide();
        showMediaNotice(data.message || 'File replaced successfully.', 'Replaced', false, () => {
            location.reload();
        });
    }).finally(() => {
        if (btn) btn.disabled = false;
    });
}

function checkMediaUsage() {
    if (!currentDashboardMedia) return;
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('usageTrackingModal'));
    const container = document.getElementById('usage-list-container');
    container.innerHTML = '<div class="text-center py-3 text-muted small"><i class="fas fa-spinner fa-spin me-1"></i> Checking usages...</div>';
    modal.show();

    fetch(`{{ url('admin/media') }}/${currentDashboardMedia.id}/usage`)
    .then(res => res.json())
    .then(data => {
        if (data.usages && data.usages.length > 0) {
            let html = '<ul class="list-group list-group-flush small">';
            data.usages.forEach(u => {
                html += `<li class="list-group-item d-flex justify-content-between align-items-center px-1 py-2">
                    <div class="text-truncate pe-2"><span class="badge bg-secondary me-1">${u.type}</span> <span class="fw-semibold text-dark">${u.title}</span></div>
                    <a href="${u.url}" target="_blank" class="btn btn-outline-primary btn-compact py-1 px-2">View</a>
                </li>`;
            });
            html += '</ul>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="text-center py-3 text-success small fw-semibold"><i class="fas fa-check-circle me-1"></i> This media is not actively linked or is safe to delete.</div>';
        }
    });
}

function deleteDashboardMedia() {
    if (!currentDashboardMedia) return;

    showMediaConfirm(
        'Delete Media File?',
        'Are you sure you want to permanently delete this media file?',
        () => {
            fetch(`{{ url('admin/media') }}/${currentDashboardMedia.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            })
            .then(async res => {
                const data = await res.json();
                if (data.status === 'in_use') {
                    setTimeout(() => {
                        showMediaConfirm(
                            'File Currently In Use!',
                            data.message + ' Do you still want to force delete it anyway?',
                            () => {
                                fetch(`{{ url('admin/media') }}/${currentDashboardMedia.id}?force_delete=1`, {
                                    method: 'DELETE',
                                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                                }).then(() => location.reload());
                            }
                        );
                    }, 250);
                } else {
                    location.reload();
                }
            });
        }
    );
}

// Bulk Mode
function toggleBulkMode() {
    isBulkMode = !isBulkMode;
    const grid = document.getElementById('main-media-grid');
    const bar = document.getElementById('bulk-action-bar');
    const btn = document.getElementById('btn-bulk-toggle');

    if (isBulkMode) {
        grid.classList.add('bulk-select-active');
        bar.classList.remove('d-none');
        btn.classList.replace('btn-outline-secondary', 'btn-secondary');
        btn.innerText = 'Cancel Selection';
        document.querySelectorAll('.media-card-item').forEach(el => el.classList.remove('selected'));
    } else {
        grid.classList.remove('bulk-select-active');
        bar.classList.add('d-none');
        btn.classList.replace('btn-secondary', 'btn-outline-secondary');
        btn.innerText = 'Bulk Select';
        document.querySelectorAll('.bulk-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.media-card-item').forEach(el => el.classList.remove('selected'));
        updateBulkCount();
    }
}

function updateBulkCount() {
    const checkboxes = document.querySelectorAll('.bulk-checkbox');
    checkboxes.forEach(cb => {
        const card = cb.closest('.media-card-item');
        if (card) card.classList.toggle('selected', cb.checked);
    });
    const checked = document.querySelectorAll('.bulk-checkbox:checked').length;
    document.getElementById('bulk-selected-count').innerText = checked;
}

function executeBulkDelete() {
    const checked = Array.from(document.querySelectorAll('.bulk-checkbox:checked')).map(cb => cb.value);
    if (!checked.length) return;

    showMediaConfirm(
        'Delete Selected Files?',
        `Are you sure you want to permanently delete ${checked.length} selected media file(s)?`,
        () => {
            fetch("{{ route('admin.media.bulk.destroy') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ ids: checked })
            }).then(res => res.json()).then(() => location.reload());
        }
    );
}
</script>
@endsection