
@extends('admin.layouts.app')

@section('title', isset($page) ? 'Edit Page Builder' : 'New Page Builder')

@section('content')
<style>
    /* =========================================================
       PAGE BUILDER — KAIADMIN CLASSIC / WHITE THEME
    ========================================================= */

    #pageBuilderRoot {
        --pb-primary: var(--bs-primary, #1572e8);
        --pb-primary-soft: rgba(21, 114, 232, .08);
        --pb-text: #1c2434;
        --pb-muted: #6b7280;
        --pb-border: #e6e9ef;
        --pb-surface: #ffffff;
        --pb-soft: #f7f8fa;
        --pb-radius: 8px;

        width: 100%;
        min-width: 0;
        color: var(--pb-text);
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
    }

    #pageBuilderRoot *,
    #pageBuilderRoot *::before,
    #pageBuilderRoot *::after {
        box-sizing: border-box;
    }

    #pageBuilderRoot .text-muted {
        color: var(--pb-muted) !important;
    }

    /* Consistent Border Radius */
    #pageBuilderRoot .btn,
    #pageBuilderRoot .btn-sm,
    #pageBuilderRoot button,
    #pageBuilderRoot .form-control,
    #pageBuilderRoot .form-select,
    #pageBuilderRoot .input-group-text,
    #pageBuilderRoot .dropdown-menu,
    #pageBuilderRoot .dropdown-item {
        border-radius: 5px !important;
    }

    #pageBuilderRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #pageBuilderRoot .input-group > .form-control {
        min-width: 0;
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Header */
    #pageBuilderRoot .builder-header {
        min-width: 0;
    }

    #pageBuilderRoot .builder-header h5 {
        color: var(--pb-text) !important;
        font-size: 18px !important;
        font-weight: 800 !important;
        letter-spacing: -.4px;
        line-height: 1.4;
    }

    #pageBuilderRoot .builder-header small {
        color: var(--pb-muted) !important;
        font-size: 11.5px !important;
    }

    #pageBuilderRoot .builder-top-actions {
        flex-shrink: 0;
    }

    /* Compact Buttons */
    #pageBuilderRoot .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 3px;
        min-height: 32px;
        padding: 6px 12px !important;
        font-size: 11.5px !important;
        font-weight: 700 !important;
        line-height: 1.35 !important;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #pageBuilderRoot .btn-primary {
        background-color: var(--pb-primary);
        border-color: var(--pb-primary);
    }

    #pageBuilderRoot .btn-primary:hover {
        filter: brightness(.95);
    }

    #pageBuilderRoot .btn-outline-primary {
        color: var(--pb-primary);
        border-color: rgba(21, 114, 232, .45);
    }

    #pageBuilderRoot .btn-outline-primary:hover {
        color: #fff;
        background: var(--pb-primary);
        border-color: var(--pb-primary);
    }

    #pageBuilderRoot .shadow-xs {
        box-shadow: 0 1px 2px rgba(16, 24, 40, .05) !important;
    }

    /* Main Cards */
    #pageBuilderRoot .builder-card {
        min-width: 0;
        overflow: hidden;
        background: var(--pb-surface);
        border: 1px solid var(--pb-border);
        border-radius: var(--pb-radius) !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
    }

    #pageBuilderRoot .builder-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 15px;
        padding-bottom: 11px;
        color: var(--pb-text);
        border-bottom: 1px solid var(--pb-border);
        font-size: 12.5px;
        font-weight: 800;
        line-height: 1.5;
    }

    #pageBuilderRoot .builder-section-title i {
        color: var(--pb-primary);
        font-size: 13px;
    }

    /* Section Toolbar */
    #pageBuilderRoot .layout-toolbar {
        min-width: 0;
        padding: 12px 14px;
        background: #fff;
        border: 1px solid var(--pb-border);
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .025);
    }

    #pageBuilderRoot .layout-toolbar h6 {
        color: var(--pb-text) !important;
        font-size: 13px !important;
        font-weight: 800 !important;
    }

    #pageBuilderRoot .add-block-dropdown-wrap {
        flex-shrink: 0;
    }

    #pageBuilderRoot .dropdown-menu {
        min-width: 225px;
        padding: 5px !important;
        border: 1px solid var(--pb-border);
        box-shadow: 0 8px 24px rgba(15, 23, 42, .10);
    }

    #pageBuilderRoot .dropdown-item {
        display: flex;
        align-items: center;
        min-height: 35px;
        padding: 7px 9px !important;
        color: #475467;
        font-size: 11.5px !important;
        font-weight: 600;
        transition: background .15s ease, color .15s ease;
    }

    #pageBuilderRoot .dropdown-item:hover,
    #pageBuilderRoot .dropdown-item:focus {
        color: var(--pb-primary);
        background: var(--pb-primary-soft);
    }

    /* Dynamic Block Cards */
    #pageBuilderRoot .block-item-card {
        min-width: 0;
        overflow: hidden;
        background: #fff;
        border: 1px solid #e1e6ed;
        border-radius: 8px !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .025);
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    #pageBuilderRoot .block-item-card:hover {
        border-color: #c3d0df;
        box-shadow: 0 3px 10px rgba(15, 23, 42, .045);
    }

    #pageBuilderRoot .block-item-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        min-height: 43px;
        padding: 8px 12px;
        background: #f8fafc;
        border-bottom: 1px solid var(--pb-border);
    }

    #pageBuilderRoot .block-item-header > span {
        min-width: 0;
        overflow-wrap: anywhere;
        color: #344054 !important;
        font-size: 11.5px !important;
        font-weight: 800 !important;
        letter-spacing: .025em;
    }

    #pageBuilderRoot .block-item-header .fa-grip-vertical {
        color: #98a2b3 !important;
    }

    #pageBuilderRoot .block-item-card > .p-3 {
        min-width: 0;
        padding: 14px !important;
    }

    /* Block Controls */
    #pageBuilderRoot .btn-block-ctrl {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 28px !important;
        height: 28px !important;
        padding: 0 !important;
        color: #667085;
        background: #fff;
        border: 1px solid #dce1e8;
        border-radius: 5px !important;
        font-size: 10px !important;
        transition: all .15s ease;
    }

    #pageBuilderRoot .btn-block-ctrl:hover {
        color: var(--pb-primary);
        background: var(--pb-primary-soft);
        border-color: #b8d1f6;
    }

    #pageBuilderRoot .btn-block-ctrl.btn-ctrl-danger:hover {
        color: #dc2626;
        background: #fef2f2;
        border-color: #fca5a5;
    }

    #pageBuilderRoot .btn-block-ctrl:focus-visible {
        outline: 3px solid rgba(21, 114, 232, .15);
        outline-offset: 2px;
    }

    /* Empty Canvas */
    #pageBuilderRoot .empty-canvas-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 205px;
        padding: 30px 20px;
        text-align: center;
        color: #667085;
        background: #fff;
        border: 1px dashed #cbd5e1;
        border-radius: 8px !important;
    }

    #pageBuilderRoot .empty-canvas-box > i {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 55px;
        height: 55px;
        margin-bottom: 13px;
        color: var(--pb-primary);
        background: var(--pb-primary-soft);
        border-radius: 50%;
        font-size: 21px;
    }

    #pageBuilderRoot .empty-canvas-box p {
        max-width: 330px;
        color: #667085;
        font-size: 11.5px !important;
        line-height: 1.7;
    }

    #pageBuilderRoot .empty-canvas-box strong {
        color: #344054;
    }

    /* Form Controls */
    #pageBuilderRoot .form-label {
        display: block;
        margin-bottom: 5px;
        color: #475467;
        font-size: 11.5px;
        font-weight: 700;
        line-height: 1.5;
    }

    #pageBuilderRoot .form-control,
    #pageBuilderRoot .form-select {
        min-height: 34px;
        padding: 7px 10px;
        color: #344054;
        background-color: #fff;
        border: 1px solid #dce1e8;
        box-shadow: none;
        font-family: inherit;
        font-size: 12px;
        font-weight: 500;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    #pageBuilderRoot .form-control::placeholder {
        color: #98a2b3;
        opacity: 1;
    }

    #pageBuilderRoot .form-control:hover,
    #pageBuilderRoot .form-select:hover {
        border-color: #b8c3d1;
    }

    #pageBuilderRoot .form-control:focus,
    #pageBuilderRoot .form-select:focus {
        color: #344054;
        background: #fff;
        border-color: var(--pb-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
        outline: none;
    }

    #pageBuilderRoot textarea.form-control {
        min-height: auto;
        resize: vertical;
        line-height: 1.6;
    }

    #pageBuilderRoot .input-group-text {
        color: #667085;
        background: #f8fafc;
        border-color: #dce1e8;
        font-size: 11px;
    }

    #pageBuilderRoot .form-check-input {
        cursor: pointer;
        border-color: #cbd5e1;
    }

    #pageBuilderRoot .form-check-input:checked {
        background-color: var(--pb-primary);
        border-color: var(--pb-primary);
    }

    #pageBuilderRoot .form-check-input:focus {
        border-color: var(--pb-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
    }

    #pageBuilderRoot .form-check-label {
        cursor: pointer;
    }

    #pageBuilderRoot .form-switch {
        min-height: 23px;
        padding-left: 2.5em;
    }

    #pageBuilderRoot .form-switch .form-check-input {
        width: 2em;
        margin-left: -2.5em;
    }

    /* Monospace */
    #pageBuilderRoot .bn-digit,
    #pageBuilderRoot .font-monospace {
        font-weight: 600 !important;
    }

    /* Sidebar */
    #pageBuilderRoot .sidebar-builder-card {
        padding: 16px;
    }

    /* Focus Visibility */
    #pageBuilderRoot button:focus-visible,
    #pageBuilderRoot a:focus-visible {
        outline: 3px solid rgba(21, 114, 232, .18);
        outline-offset: 2px;
    }

    /* Grid Safety */
    #pageBuilderRoot .row > * {
        min-width: 0;
    }

    /* Tablet */
    @media (max-width: 991.98px) {
        #pageBuilderRoot {
            padding-right: 12px !important;
            padding-left: 12px !important;
        }

        #pageBuilderRoot .builder-card {
            margin-bottom: 12px;
        }

        #pageBuilderRoot .block-item-card > .p-3 {
            padding: 12px !important;
        }
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        #pageBuilderRoot {
            padding-top: 12px !important;
            padding-right: 10px !important;
            padding-left: 10px !important;
        }

        #pageBuilderRoot .builder-header h5 {
            font-size: 16px !important;
        }

        #pageBuilderRoot .builder-top-actions {
            display: flex;
            width: 100%;
            gap: 8px;
        }

        #pageBuilderRoot .builder-top-actions .btn-compact {
            flex: 1 1 0;
            min-width: 0;
        }

        #pageBuilderRoot .layout-toolbar {
            align-items: stretch !important;
            padding: 11px;
        }

        #pageBuilderRoot .add-block-dropdown-wrap {
            width: 100%;
        }

        #pageBuilderRoot .add-block-dropdown-wrap .btn-compact {
            width: 100%;
        }

        #pageBuilderRoot .add-block-dropdown-wrap .dropdown-menu {
            width: 100%;
        }

        #pageBuilderRoot .builder-card.sidebar-builder-card {
            padding: 13px !important;
        }

        #pageBuilderRoot .block-item-header {
            padding: 8px 9px;
        }

        #pageBuilderRoot .block-item-header > span {
            font-size: 10.5px !important;
        }

        #pageBuilderRoot .btn-block-ctrl {
            width: 27px !important;
            height: 27px !important;
        }

        #pageBuilderRoot .empty-canvas-box {
            min-height: 175px;
            padding: 24px 14px;
        }
    }

    /* Small Mobile */
    @media (max-width: 420px) {
        #pageBuilderRoot .builder-top-actions {
            flex-direction: column;
        }

        #pageBuilderRoot .builder-top-actions .btn-compact {
            width: 100%;
        }

        #pageBuilderRoot .block-item-card > .p-3 {
            padding: 10px !important;
        }

        #pageBuilderRoot .block-item-header {
            gap: 5px;
        }

        #pageBuilderRoot .btn-block-ctrl {
            width: 25px !important;
            height: 25px !important;
        }

        #pageBuilderRoot .builder-card.sidebar-builder-card {
            padding: 11px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="pageBuilderRoot">

    <form action="{{ isset($page) ? route('admin.content.pages.update', $page->id) : route('admin.content.pages.store') }}" method="POST" id="pageBuilderForm">
        @csrf

        <!-- Top Header -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 builder-header">

            <div>
                <h5 class="fw-bold text-dark mb-1" style="font-size: 18px;">
                    <i class="fas fa-file-circle-plus text-primary me-1"></i>
                    {{ isset($page) ? 'Edit Page Builder' : 'Create New Page' }}
                </h5>

                <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                    Assemble responsive block sections for this page
                </small>
            </div>

            <div class="d-flex gap-2 builder-top-actions align-self-start align-self-sm-auto">
                <a href="{{ route('admin.content.pages.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs">
                    <i class="fas fa-arrow-left me-1"></i>
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary btn-compact shadow-xs">
                    <i class="fas fa-save me-1"></i>
                    Save Page
                </button>
            </div>
        </div>

        <div class="row g-3">

            <!-- Left Side: Page Info & Block Canvas -->
            <div class="col-12 col-lg-8">

                <!-- Page Information -->
                <div class="builder-card p-3 mb-3">

                    <div class="mb-3">
                        <label class="form-label">Page Title</label>

                        <input
                            type="text"
                            name="title"
                            id="pageTitle"
                            value="{{ old('title', $page->title ?? '') }}"
                            class="form-control fw-semibold"
                            placeholder="About Us / Terms of Service"
                            required
                        >
                    </div>

                    <div class="mb-0">
                        <label class="form-label">URL Slug</label>

                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted">/page/</span>

                            <input
                                type="text"
                                name="slug"
                                id="pageSlug"
                                value="{{ old('slug', $page->slug ?? '') }}"
                                class="form-control font-monospace"
                                placeholder="about-us"
                            >
                        </div>
                    </div>
                </div>

                <!-- Block Container Toolbar -->
                <div class="layout-toolbar d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">

                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center">
                        <i class="fas fa-layer-group text-primary me-2"></i>
                        <span>Page Layout Blocks</span>
                    </h6>

                    <div class="dropdown add-block-dropdown-wrap align-self-start align-self-sm-auto">

                        <button class="btn btn-primary btn-compact dropdown-toggle shadow-xs" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-plus me-1"></i>
                            <span>Add Section Block</span>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow border p-1">

                            <li>
                                <a class="dropdown-item small py-2 px-2 d-flex align-items-center" href="javascript:void(0)" onclick="addBlock('hero_banner')">
                                    <i class="fas fa-image text-primary me-2" style="width: 16px; text-align: center;"></i>
                                    <span>Hero Banner Section</span>
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item small py-2 px-2 d-flex align-items-center" href="javascript:void(0)" onclick="addBlock('rich_text')">
                                    <i class="fas fa-align-left text-success me-2" style="width: 16px; text-align: center;"></i>
                                    <span>Rich Text Content</span>
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item small py-2 px-2 d-flex align-items-center" href="javascript:void(0)" onclick="addBlock('features_grid')">
                                    <i class="fas fa-th-large text-warning me-2" style="width: 16px; text-align: center;"></i>
                                    <span>4-Column Feature Icons</span>
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item small py-2 px-2 d-flex align-items-center" href="javascript:void(0)" onclick="addBlock('products_grid')">
                                    <i class="fas fa-box text-danger me-2" style="width: 16px; text-align: center;"></i>
                                    <span>Featured Products Grid</span>
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item small py-2 px-2 d-flex align-items-center" href="javascript:void(0)" onclick="addBlock('recent_posts')">
                                    <i class="fas fa-newspaper text-info me-2" style="width: 16px; text-align: center;"></i>
                                    <span>Recent Blog Posts</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                </div>

                <!-- Dynamic Blocks Canvas -->
                <div id="blocksCanvas" class="d-flex flex-column gap-3 mb-3">
                    <!-- Dynamic Blocks Injected Here via JS -->
                </div>

                <input type="hidden" name="content_blocks" id="contentBlocksJson">

            </div>

            <!-- Right Side: Sidebar Meta Controls -->
            <div class="col-12 col-lg-4">

                <!-- Publish Settings -->
                <div class="builder-card sidebar-builder-card mb-3">

                    <h6 class="builder-section-title">
                        <i class="fas fa-sliders"></i>
                        Publish Settings
                    </h6>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_published" id="is_published" {{ old('is_published', $page->is_published ?? true) ? 'checked' : '' }} style="cursor: pointer;">

                        <label class="form-check-label fw-semibold small text-dark" for="is_published" style="font-size: 12px; cursor: pointer;">
                            Publish Online
                        </label>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Page Template</label>

                        <select name="template" class="form-select form-select-sm">
                            <option value="default" {{ ($page->template ?? '') == 'default' ? 'selected' : '' }}>Default (Contained Box)</option>
                            <option value="full-width" {{ ($page->template ?? '') == 'full-width' ? 'selected' : '' }}>Full Width Canvas</option>
                        </select>
                    </div>
                </div>

                <!-- SEO Configuration -->
                <div class="builder-card sidebar-builder-card">

                    <h6 class="builder-section-title">
                        <i class="fas fa-search"></i>
                        SEO Configuration
                    </h6>

                    <div class="mb-3">
                        <label class="form-label">Meta Title</label>

                        <input
                            type="text"
                            name="meta_title"
                            value="{{ old('meta_title', $page->meta_title ?? '') }}"
                            class="form-control form-control-sm"
                        >
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Meta Description</label>

                        <textarea name="meta_description" rows="3" class="form-control form-control-sm">{{ old('meta_description', $page->meta_description ?? '') }}</textarea>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<!-- Simple Block Builder Engine (Pure Vanilla JS) -->
<script>
    let activeBlocks = @json($page->content_blocks ?? []);

    document.addEventListener('DOMContentLoaded', () => {
        renderBlocks();

        document.getElementById('pageBuilderForm').addEventListener('submit', () => {
            saveCurrentInputs();
            document.getElementById('contentBlocksJson').value = JSON.stringify(activeBlocks);
        });
    });

    function renderBlocks() {
        const canvas = document.getElementById('blocksCanvas');
        canvas.innerHTML = '';

        if (activeBlocks.length === 0) {
            canvas.innerHTML = `
                <div class="empty-canvas-box">
                    <i class="fas fa-puzzle-piece fa-2x mb-2 opacity-50"></i>
                    <p class="mb-0 small">No blocks added yet. Click <strong>Add Section Block</strong> above to start building.</p>
                </div>`;
            return;
        }

        activeBlocks.forEach((b, idx) => {
            let blockCard = document.createElement('div');
            blockCard.className = 'block-item-card';
            blockCard.dataset.index = idx;

            let headerTitle = b.type.replace('_', ' ').toUpperCase();

            blockCard.innerHTML = `
                <div class="block-item-header">
                    <span class="fw-bold small text-dark d-flex align-items-center" style="font-size: 11.5px; letter-spacing: 0.03em;">
                        <i class="fas fa-grip-vertical text-muted me-2"></i>
                        <span>${headerTitle}</span>
                    </span>
                    <div class="d-inline-flex align-items-center gap-1">
                        <button type="button" class="btn-block-ctrl" onclick="moveBlock(${idx}, -1)" title="Move Up"><i class="fas fa-arrow-up"></i></button>
                        <button type="button" class="btn-block-ctrl" onclick="moveBlock(${idx}, 1)" title="Move Down"><i class="fas fa-arrow-down"></i></button>
                        <button type="button" class="btn-block-ctrl btn-ctrl-danger" onclick="removeBlock(${idx})" title="Remove Block"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                <div class="p-3">
                    ${renderBlockFields(b, idx)}
                </div>
            `;

            canvas.appendChild(blockCard);
        });
    }

    function renderBlockFields(b, idx) {
        if (b.type === 'hero_banner') {
            return `
                <div class="row g-2">
                    <div class="col-12 col-md-6"><input type="text" class="form-control form-control-sm" placeholder="Title" value="${b.data.title || ''}" onchange="updateData(${idx}, 'title', this.value)"></div>
                    <div class="col-12 col-md-6"><input type="text" class="form-control form-control-sm" placeholder="Subtitle" value="${b.data.subtitle || ''}" onchange="updateData(${idx}, 'subtitle', this.value)"></div>
                    <div class="col-12"><textarea class="form-control form-control-sm" placeholder="Description" rows="2" onchange="updateData(${idx}, 'description', this.value)">${b.data.description || ''}</textarea></div>
                    <div class="col-12 col-md-6"><input type="text" class="form-control form-control-sm" placeholder="Button Text" value="${b.data.btn_text || ''}" onchange="updateData(${idx}, 'btn_text', this.value)"></div>
                    <div class="col-12 col-md-6"><input type="text" class="form-control form-control-sm" placeholder="Button URL" value="${b.data.btn_url || ''}" onchange="updateData(${idx}, 'btn_url', this.value)"></div>
                </div>`;
        }

        if (b.type === 'rich_text') {
            return `<textarea class="form-control form-control-sm" rows="5" placeholder="HTML or Text Content" onchange="updateData(${idx}, 'content', this.value)">${b.data.content || ''}</textarea>`;
        }

        if (b.type === 'products_grid') {
            return `
                <div class="row g-2">
                    <div class="col-12 col-md-8"><input type="text" class="form-control form-control-sm" placeholder="Heading Title" value="${b.data.title || 'Featured Products'}" onchange="updateData(${idx}, 'title', this.value)"></div>
                    <div class="col-12 col-md-4"><input type="number" class="form-control form-control-sm bn-digit" placeholder="Items Limit" value="${b.data.limit || 8}" onchange="updateData(${idx}, 'limit', this.value)"></div>
                </div>`;
        }

        if (b.type === 'recent_posts') {
            return `
                <div class="row g-2">
                    <div class="col-12 col-md-8"><input type="text" class="form-control form-control-sm" placeholder="Heading Title" value="${b.data.title || 'From Our Blog'}" onchange="updateData(${idx}, 'title', this.value)"></div>
                    <div class="col-12 col-md-4"><input type="number" class="form-control form-control-sm bn-digit" placeholder="Posts Limit" value="${b.data.limit || 3}" onchange="updateData(${idx}, 'limit', this.value)"></div>
                </div>`;
        }

        if (b.type === 'features_grid') {
            return `
                <input type="text" class="form-control form-control-sm mb-2" placeholder="Section Title ( Why Choose Us)" value="${b.data.title || ''}" onchange="updateData(${idx}, 'title', this.value)">
                <small class="text-muted d-block" style="font-size: 11px;">4-Item features will be automatically displayed based on defaults.</small>
            `;
        }

        return '';
    }

    function addBlock(type) {
        saveCurrentInputs();

        let defaultData = {};

        if (type === 'features_grid') {
            defaultData = {
                title: 'Why Choose Us',
                items: [
                    { icon: 'fas fa-truck', title: 'Free Delivery', description: 'On all orders above 1500৳' },
                    { icon: 'fas fa-shield-alt', title: '100% Secure', description: 'Safe payment gateways' },
                    { icon: 'fas fa-undo', title: '7 Days Return', description: 'Instant refund guarantee' },
                    { icon: 'fas fa-headset', title: '24/7 Support', description: 'Dedicated hotline help' },
                ]
            };
        }

        activeBlocks.push({ type: type, data: defaultData });

        renderBlocks();
    }

    function removeBlock(idx) {
        saveCurrentInputs();

        activeBlocks.splice(idx, 1);

        renderBlocks();
    }

    function moveBlock(idx, dir) {
        saveCurrentInputs();

        const target = idx + dir;

        if (target >= 0 && target < activeBlocks.length) {
            const temp = activeBlocks[idx];

            activeBlocks[idx] = activeBlocks[target];
            activeBlocks[target] = temp;

            renderBlocks();
        }
    }

    function updateData(idx, key, val) {
        if (!activeBlocks[idx].data) activeBlocks[idx].data = {};

        activeBlocks[idx].data[key] = val;
    }

    function saveCurrentInputs() {
        // DOM values auto-sync via onchange
    }
</script>
@endsection
