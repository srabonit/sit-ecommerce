
@extends('layouts.admin')

@section('title', 'Edit Page - ' . $page->title)

@section('admin_content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">

<!-- Summernote Rich Text Editor -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

<!-- CodeMirror CSS for Code Mode -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/dracula.min.css">

<style>
    /* =========================================================
       PAGE EDIT — KAIADMIN CLASSIC / WHITE THEME
    ========================================================= */

    #pageEditRoot {
        --pe-primary: var(--bs-primary, #1572e8);
        --pe-primary-soft: rgba(21, 114, 232, .08);
        --pe-text: #1c2434;
        --pe-muted: #6b7280;
        --pe-border: #e6e9ef;
        --pe-surface: #ffffff;
        --pe-soft: #f7f8fa;
        --pe-radius: 8px;

        min-width: 0;
        width: 100%;
        color: var(--pe-text);
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
    }

    #pageEditRoot *,
    #pageEditRoot *::before,
    #pageEditRoot *::after {
        box-sizing: border-box;
    }

    #pageEditRoot .text-muted {
        color: var(--pe-muted) !important;
    }

    #pageEditRoot .btn,
    #pageEditRoot .btn-sm,
    #pageEditRoot .btn-xs,
    #pageEditRoot button:not(.btn-clear-featured):not(.note-btn),
    #pageEditRoot .form-control,
    #pageEditRoot .form-select,
    #pageEditRoot .badge,
    #pageEditRoot .input-group-text,
    #pageEditRoot .alert,
    #pageEditorNoticeModal .btn {
        border-radius: 5px !important;
    }

    #pageEditRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #pageEditRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
        min-width: 0;
    }

    /* Compact Buttons */
    #pageEditRoot .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 3px;
        min-height: 32px;
        padding: 6px 12px !important;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 11.5px !important;
        font-weight: 700 !important;
        line-height: 1.35 !important;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #pageEditRoot .btn-compact:focus-visible,
    #pageEditRoot button:focus-visible {
        outline: 3px solid rgba(21, 114, 232, .2);
        outline-offset: 2px;
    }

    #pageEditRoot .btn-primary {
        background-color: var(--pe-primary);
        border-color: var(--pe-primary);
    }

    #pageEditRoot .btn-primary:hover {
        filter: brightness(.95);
    }

    #pageEditRoot .btn-outline-primary {
        color: var(--pe-primary);
        border-color: rgba(21, 114, 232, .45);
    }

    #pageEditRoot .btn-outline-primary:hover {
        color: #fff;
        background: var(--pe-primary);
        border-color: var(--pe-primary);
    }

    #pageEditRoot .shadow-xs {
        box-shadow: 0 1px 2px rgba(16, 24, 40, .05) !important;
    }

    /* Page Header */
    #pageEditRoot .page-edit-heading {
        min-width: 0;
    }

    #pageEditRoot .page-edit-heading h5 {
        color: var(--pe-text) !important;
        font-size: 18px !important;
        font-weight: 800 !important;
        letter-spacing: -.45px;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    #pageEditRoot .page-edit-heading .slug-badge {
        display: inline-flex;
        align-items: center;
        max-width: 100%;
        overflow-wrap: anywhere;
        font-size: 10.5px;
        font-weight: 600;
        border: 1px solid var(--pe-border) !important;
        background: var(--pe-soft) !important;
        color: var(--pe-primary) !important;
    }

    #pageEditRoot .header-top-actions {
        flex-wrap: wrap;
    }

    /* Classic White Cards */
    #pageEditRoot .panel-card {
        min-width: 0;
        margin-bottom: 16px;
        overflow: hidden;
        background: var(--pe-surface);
        border: 1px solid var(--pe-border);
        border-radius: var(--pe-radius) !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
    }

    #pageEditRoot .panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        min-height: 43px;
        padding: 11px 15px;
        color: var(--pe-text);
        background: #fff;
        border-bottom: 1px solid var(--pe-border);
        font-size: 12.5px;
        font-weight: 800;
        line-height: 1.4;
    }

    #pageEditRoot .panel-body {
        min-width: 0;
        padding: 16px;
    }

    #pageEditRoot .panel-header[data-bs-toggle="collapse"] {
        cursor: pointer;
        transition: background .18s ease;
    }

    #pageEditRoot .panel-header[data-bs-toggle="collapse"]:hover {
        background: var(--pe-soft);
    }

    #pageEditRoot .panel-header[data-bs-toggle="collapse"] > i:last-child {
        transition: transform .2s ease;
    }

    #pageEditRoot .panel-header[data-bs-toggle="collapse"][aria-expanded="true"] > i:last-child {
        transform: rotate(180deg);
    }

    /* Form Controls */
    #pageEditRoot .form-label {
        display: block;
        margin-bottom: 5px;
        color: #475467;
        font-size: 11.5px;
        font-weight: 700;
        line-height: 1.5;
    }

    #pageEditRoot .form-control,
    #pageEditRoot .form-select {
        min-height: 34px;
        padding: 7px 10px;
        color: #344054;
        background-color: #fff;
        border: 1px solid #dce1e8;
        box-shadow: none;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12px;
        font-weight: 500;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    #pageEditRoot .form-control::placeholder {
        color: #98a2b3;
        opacity: 1;
    }

    #pageEditRoot .form-control:hover,
    #pageEditRoot .form-select:hover {
        border-color: #b8c3d1;
    }

    #pageEditRoot .form-control:focus,
    #pageEditRoot .form-select:focus {
        color: #344054;
        background-color: #fff;
        border-color: var(--pe-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
        outline: none;
    }

    #pageEditRoot textarea.form-control {
        min-height: auto;
        resize: vertical;
    }

    #pageEditRoot #pageTitle {
        min-height: 40px;
        padding: 9px 12px;
        color: var(--pe-text);
        font-size: 14px;
        font-weight: 700;
    }

    #pageEditRoot .input-group-text {
        color: #667085;
        background: #f8fafc;
        border-color: #dce1e8;
        font-size: 11px;
    }

    #pageEditRoot .form-check-input {
        cursor: pointer;
        border-color: #cbd5e1;
    }

    #pageEditRoot .form-check-input:checked {
        background-color: var(--pe-primary);
        border-color: var(--pe-primary);
    }

    #pageEditRoot .form-check-input:focus {
        border-color: var(--pe-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
    }

    #pageEditRoot .form-check-label {
        cursor: pointer;
    }

    #pageEditRoot .form-switch {
        min-height: 23px;
        padding-left: 2.5em;
    }

    #pageEditRoot .form-switch .form-check-input {
        width: 2em;
        margin-left: -2.5em;
    }

    /* Validation / Success Alert */
    #pageEditRoot .alert {
        border: 1px solid transparent !important;
        font-size: 12px;
    }

    #pageEditRoot .alert-success {
        color: #157347;
        background: #eaf7ef;
        border-color: #ccebd8 !important;
    }

    /* Editor Toolbar Header */
    #pageEditRoot .editor-toolbar-header {
        min-height: 48px;
        padding: 8px 12px !important;
        background: #fff;
    }

    #pageEditRoot .editor-mode-group {
        flex-wrap: wrap;
    }

    #pageEditRoot .shortcode-btn-wrap {
        flex-shrink: 0;
    }

    /* Summernote */
    #pageEditRoot .note-editor.note-frame {
        margin-bottom: 0 !important;
        overflow: hidden;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    #pageEditRoot .note-editor .note-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 2px;
        padding: 7px 9px !important;
        background: #f8fafc !important;
        border-bottom: 1px solid var(--pe-border) !important;
    }

    #pageEditRoot .note-editor .note-btn-group {
        margin: 1px 2px;
    }

    #pageEditRoot .note-editor .note-btn {
        min-height: 28px;
        padding: 4px 7px;
        color: #475467;
        background: #fff;
        border: 1px solid #e1e6ed;
        border-radius: 4px;
        box-shadow: none;
        font-size: 11px;
    }

    #pageEditRoot .note-editor .note-btn:hover,
    #pageEditRoot .note-editor .note-btn:focus {
        color: var(--pe-primary);
        background: #f1f6ff;
        border-color: #c9dcfa;
    }

    #pageEditRoot .note-editor .note-editing-area {
        background: #fff;
    }

    #pageEditRoot .note-editor .note-editable {
        min-height: 480px !important;
        padding: 16px 18px;
        color: #344054;
        background: #fff;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        line-height: 1.75;
        overflow-wrap: anywhere;
    }

    #pageEditRoot .note-editor .note-statusbar {
        border-top: 1px solid var(--pe-border);
        background: #fafbfc;
    }

    #pageEditRoot .note-editor .note-resizebar {
        height: 8px;
    }

    /* CodeMirror */
    #pageEditRoot .CodeMirror {
        width: 100%;
        height: 500px !important;
        font-family: 'Fira Code', monospace !important;
        font-size: 12px !important;
        line-height: 1.65 !important;
        border-radius: 0 !important;
    }

    #pageEditRoot .CodeMirror-gutters {
        border-right: 1px solid rgba(255,255,255,.08);
    }

    #pageEditRoot .CodeMirror-scroll {
        min-height: 100%;
    }

    #pageEditRoot .code-editor-area {
        min-height: 100px;
        color: #344054 !important;
        background: #f8fafc !important;
        border: 1px solid #dce1e8 !important;
        font-family: 'Fira Code', monospace !important;
        font-size: 11.5px !important;
        line-height: 1.6;
    }

    /* Editor Footer */
    #pageEditRoot .editor-footer {
        color: #667085;
        background: #f8fafc;
        border-top: 1px solid var(--pe-border) !important;
        font-size: 11px !important;
        line-height: 1.5;
    }

    /* SEO Google Preview */
    #pageEditRoot .google-preview-card {
        padding: 13px;
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--pe-border);
        border-radius: 7px;
    }

    #pageEditRoot .google-url {
        overflow: hidden;
        color: #475467;
        font-size: 10.5px;
        line-height: 1.5;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    #pageEditRoot .google-title {
        margin-top: 4px;
        overflow: hidden;
        color: #1a0dab;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.45;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    #pageEditRoot .google-desc {
        display: -webkit-box;
        margin-top: 4px;
        overflow: hidden;
        color: #545b66;
        font-size: 11px;
        line-height: 1.55;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
    }

    /* Featured Image Preview */
    #pageEditRoot .preview-featured-box {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 115px;
        padding: 12px;
        overflow: hidden;
        text-align: center;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 7px;
    }

    #pageEditRoot .preview-featured-box img {
        display: block;
        max-width: 100%;
        max-height: 150px;
        object-fit: contain;
        border-radius: 5px;
    }

    #pageEditRoot .btn-clear-featured {
        position: absolute;
        top: 7px;
        right: 7px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 25px;
        height: 25px;
        padding: 0;
        color: #fff;
        background: #e5484d;
        border: 2px solid #fff;
        border-radius: 50% !important;
        box-shadow: 0 2px 5px rgba(0,0,0,.15);
        font-size: 10px;
        cursor: pointer;
        transition: transform .15s ease, background .15s ease;
    }

    #pageEditRoot .btn-clear-featured:hover {
        background: #c92a32;
        transform: scale(1.08);
    }

    /* Custom Code Fields */
    #pageEditRoot .code-editor-area:focus {
        border-color: var(--pe-primary) !important;
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .08) !important;
    }

    /* Safe Bengali Digits / Monospace */
    #pageEditRoot .bn-digit,
    #pageEditRoot .font-monospace {
        font-weight: 600 !important;
    }

    /* Modal */
    #pageEditorNoticeModal .modal-content {
        border: 1px solid var(--pe-border);
        border-radius: 9px !important;
    }

    #pageEditorNoticeModal .modal-body {
        padding: 12px 8px;
    }

    /* Bootstrap Grid Safety */
    #pageEditRoot .row > * {
        min-width: 0;
    }

    #pageEditRoot .collapse {
        min-width: 0;
    }

    /* Tablet */
    @media (max-width: 991.98px) {
        #pageEditRoot {
            padding-right: 12px !important;
            padding-left: 12px !important;
        }

        #pageEditRoot .panel-body {
            padding: 14px;
        }

        #pageEditRoot .CodeMirror {
            height: 430px !important;
        }

        #pageEditRoot .note-editor .note-editable {
            min-height: 430px !important;
        }
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        #pageEditRoot {
            padding-top: 12px !important;
            padding-right: 10px !important;
            padding-left: 10px !important;
        }

        #pageEditRoot .header-top-actions {
            display: flex;
            width: 100%;
            gap: 8px;
        }

        #pageEditRoot .header-top-actions .btn-compact {
            flex: 1 1 0;
            min-width: 0;
            padding-right: 7px !important;
            padding-left: 7px !important;
        }

        #pageEditRoot .page-edit-heading h5 {
            font-size: 16px !important;
        }

        #pageEditRoot .panel-card {
            margin-bottom: 12px;
        }

        #pageEditRoot .panel-header {
            min-height: 41px;
            padding: 10px 12px;
        }

        #pageEditRoot .panel-body {
            padding: 12px;
        }

        #pageEditRoot .editor-toolbar-header {
            align-items: stretch !important;
            flex-direction: column;
            gap: 9px;
            padding: 10px !important;
        }

        #pageEditRoot .editor-mode-group {
            display: flex;
            width: 100%;
            gap: 6px;
        }

        #pageEditRoot .editor-mode-group .btn {
            flex: 1 1 0;
            min-width: 0;
            white-space: normal;
        }

        #pageEditRoot .shortcode-btn-wrap,
        #pageEditRoot .shortcode-btn-wrap .btn {
            width: 100%;
        }

        #pageEditRoot .note-editor .note-toolbar {
            padding: 6px !important;
        }

        #pageEditRoot .note-editor .note-btn {
            min-height: 27px;
            padding: 3px 5px;
        }

        #pageEditRoot .note-editor .note-editable {
            min-height: 360px !important;
            padding: 12px;
            font-size: 12.5px;
        }

        #pageEditRoot .CodeMirror {
            height: 370px !important;
            font-size: 11px !important;
        }

        #pageEditRoot .input-group > .input-group-text {
            max-width: 125px !important;
            font-size: 9px !important;
        }

        #pageEditRoot .google-preview-card {
            padding: 10px;
        }
    }

    /* Small Mobile */
    @media (max-width: 420px) {
        #pageEditRoot .header-top-actions {
            flex-direction: column;
        }

        #pageEditRoot .header-top-actions .btn-compact {
            width: 100%;
        }

        #pageEditRoot .panel-body {
            padding: 10px;
        }

        #pageEditRoot .note-editor .note-editable {
            min-height: 320px !important;
        }

        #pageEditRoot .CodeMirror {
            height: 330px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="pageEditRoot">

    <!-- Top Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
        <div class="page-edit-heading">
            <h5 class="mb-1 fw-bold text-dark" style="font-size: 18px;">
                <i class="fas fa-file-pen text-primary me-1"></i>
                Edit Page: {{ $page->title }}
            </h5>

            <small class="text-muted" style="font-size: 11.5px;">
                Slug:
                <span class="badge bg-light text-primary border font-monospace px-2 py-1 slug-badge">/{{ $page->slug }}</span>
            </small>
        </div>

        <div class="header-top-actions d-flex gap-2">
            <a href="{{ route('pages.show', $page->slug) }}" target="_blank" class="btn btn-outline-info btn-compact shadow-xs">
                <i class="fas fa-eye me-1"></i>
                <span>Preview Live</span>
            </a>

            <a href="{{ route('admin.content.pages.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-arrow-left me-1"></i>
                <span>Back to Pages</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small shadow-xs mb-3 d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <span class="fw-semibold">{{ session('success') }}</span>
            <button type="button" class="btn-close btn-sm shadow-none ms-auto py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.content.pages.update', $page->id) }}" method="POST" enctype="multipart/form-data" id="pageEditForm">
        @csrf
        @method('PUT')

        <input type="hidden" name="editor_type" id="activeEditorType" value="{{ old('editor_type', $page->editor_type ?? 'visual') }}">

        <div class="row g-3">

            <!-- Left Side: Main Content & Dual Editor -->
            <div class="col-12 col-lg-8 col-xl-9">

                <!-- Page Information -->
                <div class="panel-card">
                    <div class="panel-body">
                        <div class="mb-3">
                            <label class="form-label">Page Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="pageTitle" class="form-control fw-bold" value="{{ old('title', $page->title) }}" required oninput="syncSeoPreview()">
                        </div>

                        <div class="mb-0">
                            <label class="form-label">Permalink Slug <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted small text-truncate" style="max-width: 180px;">{{ url('/') }}/</span>
                                <input type="text" name="slug" id="pageSlug" class="form-control font-monospace" value="{{ old('slug', $page->slug) }}" required oninput="syncSeoPreview()">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dual Mode Editor Box -->
                <div class="panel-card overflow-hidden">

                    <div class="panel-header editor-toolbar-header">
                        <div class="editor-mode-group d-flex gap-1" role="group">
                            <button type="button" class="btn btn-primary btn-compact" id="btnModeVisual" onclick="switchEditorMode('visual')">
                                <i class="fas fa-eye me-1"></i>
                                <span>Visual Editor</span>
                            </button>

                            <button type="button" class="btn btn-outline-secondary btn-compact" id="btnModeCode" onclick="switchEditorMode('code')">
                                <i class="fas fa-code me-1"></i>
                                <span>HTML / Code Editor</span>
                            </button>
                        </div>

                        <div class="shortcode-btn-wrap">
                            <button type="button" class="btn btn-outline-primary btn-compact" onclick="insertShortcode('[products limit=\'4\']')">
                                <i class="fas fa-cubes me-1"></i>
                                Products Shortcode
                            </button>
                        </div>
                    </div>

                    <!-- 1. Visual Editor Canvas -->
                    <div id="wrapperVisualEditor" class="p-0">
                        <textarea id="summernoteEditor">{{ old('content', $page->content) }}</textarea>
                    </div>

                    <!-- 2. Pure Code Editor Canvas -->
                    <div id="wrapperCodeEditor" class="p-0" style="display: none;">
                        <textarea id="codeMirrorArea">{{ old('content', $page->content) }}</textarea>
                    </div>

                    <!-- Final Submission Output Container -->
                    <textarea name="content" id="finalContentInput" style="display: none;">{{ old('content', $page->content) }}</textarea>

                    <div class="editor-footer py-2 px-3 small">
                        <i class="fas fa-circle-info me-1 text-primary"></i>
                        Write using either Visual Editor or Code Editor. Both sync automatically on save.
                    </div>
                </div>
            </div>

            <!-- Right Side: Settings Sidebar -->
            <div class="col-12 col-lg-4 col-xl-3">

                <!-- Publish Options -->
                <div class="panel-card">
                    <div class="panel-header">
                        <span><i class="fas fa-sliders me-2 text-primary"></i>Publish Options</span>
                    </div>

                    <div class="panel-body">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select form-select-sm fw-semibold">
                                <option value="published" {{ ($page->status ?? '') == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="draft" {{ ($page->status ?? '') == 'draft' ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Page Width Layout</label>
                            <select name="page_width" class="form-select form-select-sm">
                                <option value="contained-wide" {{ ($page->page_width ?? 'contained-wide') == 'contained-wide' ? 'selected' : '' }}>Contained Wide</option>
                                <option value="full-width" {{ ($page->page_width ?? '') == 'full-width' ? 'selected' : '' }}>Full Width (100% Canvas)</option>
                                <option value="boxed" {{ ($page->page_width ?? '') == 'boxed' ? 'selected' : '' }}>Boxed (Narrow Container)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Page Title Font Size</label>
                            <input type="text" name="title_font_size" class="form-control form-control-sm font-monospace bn-digit" value="{{ $page->title_font_size ?? '32px' }}" placeholder="32px or 2rem">
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="show_title" id="showTitle" value="1" {{ ($page->show_title ?? 1) ? 'checked' : '' }} style="cursor: pointer;">
                            <label class="form-check-label small fw-semibold text-dark" for="showTitle" style="font-size: 12px; cursor: pointer;">Show Page Title</label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="show_sidebar_widget" id="showSidebar" value="1" {{ !empty($page->show_sidebar_widget) ? 'checked' : '' }} style="cursor: pointer;">
                            <label class="form-check-label small fw-semibold text-dark" for="showSidebar" style="font-size: 12px; cursor: pointer;">Show Right Sidebar</label>
                        </div>

                        <div class="d-grid pt-3 border-top">
                            <button type="submit" class="btn btn-primary btn-compact py-2 shadow-xs">
                                <i class="fas fa-save me-1"></i>
                                <span>Update Page</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Featured Image with Media Library Integration -->
                <div class="panel-card">
                    <div class="panel-header">
                        <span><i class="fas fa-image me-2 text-primary"></i>Featured Image</span>
                    </div>

                    <div class="panel-body">
                        <button type="button" class="btn btn-outline-primary btn-compact w-100 mb-2 shadow-xs" onclick="choosePageFeaturedMedia()">
                            <i class="fas fa-images me-1"></i>
                            <span>Choose from Media Library</span>
                        </button>

                        <span class="small text-muted d-block text-center mb-2" style="font-size: 11px;">or upload local file:</span>

                        <div class="mb-3">
                            <input type="file" name="featured_image_file" id="featuredImageFileInput" class="form-control form-control-sm" accept="image/*" onchange="previewImage(this)">
                        </div>

                        @php
                            $pageImgSrc = null;
                            if (!empty($page->featured_image)) {
                                if (function_exists('show_image')) {
                                    $pageImgSrc = show_image($page->featured_image);
                                } elseif (filter_var($page->featured_image, FILTER_VALIDATE_URL)) {
                                    $pageImgSrc = $page->featured_image;
                                } else {
                                    $pi = ltrim(str_replace(['storage/', 'public/'], '', $page->featured_image), '/');
                                    $publicRoot = is_dir(base_path('../public_html')) ? base_path('../public_html') : public_path();
                                    if (file_exists($publicRoot . '/uploads/' . $pi)) {
                                        $pageImgSrc = asset('uploads/' . $pi);
                                    } elseif (file_exists($publicRoot . '/' . $pi)) {
                                        $pageImgSrc = asset($pi);
                                    } else {
                                        $pageImgSrc = asset('uploads/' . $pi);
                                    }
                                }
                            }
                        @endphp

                        <!-- Image Preview Box -->
                        <div id="imagePreviewContainer" class="preview-featured-box my-2 {{ empty($pageImgSrc) ? 'd-none' : '' }}">
                            <img id="imagePreview" src="{{ $pageImgSrc ?? '#' }}" alt="Featured Image Preview" onerror="this.onerror=null; this.src='https://placehold.co/150x100?text=No+Image';">

                            <button type="button" class="btn-clear-featured" onclick="clearFeaturedImage()" title="Remove Image">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Or Image Path / URL</label>
                            <input type="text" name="featured_image" id="pageFeaturedImageInput" class="form-control form-control-sm font-monospace" placeholder="uploads/media/... or https://" value="{{ old('featured_image', $page->featured_image) }}" oninput="handleManualImageUrl(this.value)">
                        </div>

                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="show_featured_image" id="showFeaturedImage" value="1" {{ ($page->show_featured_image ?? 1) ? 'checked' : '' }} style="cursor: pointer;">
                            <label class="form-check-label small fw-semibold text-dark" for="showFeaturedImage" style="font-size: 12px; cursor: pointer;">Show In Page Header</label>
                        </div>
                    </div>
                </div>

                <!-- SEO (Optional) -->
                <div class="panel-card">
                    <div class="panel-header" data-bs-toggle="collapse" data-bs-target="#seoPanel" style="cursor: pointer;">
                        <span><i class="fas fa-search text-success me-2"></i>SEO (Optional)</span>
                        <i class="fas fa-chevron-down small text-muted"></i>
                    </div>

                    <div class="collapse" id="seoPanel">
                        <div class="panel-body">

                            <div class="google-preview-card mb-3">
                                <div class="google-url text-truncate">{{ url('/') }}/<span id="preview-slug">{{ $page->slug }}</span></div>
                                <div class="google-title text-truncate" id="preview-title">{{ $page->seo_title ?: $page->title }}</div>
                                <div class="google-desc text-truncate" id="preview-desc">{{ $page->seo_description ?: 'Meta description will appear here.' }}</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">SEO Title</label>
                                <input type="text" name="seo_title" id="seoTitle" class="form-control form-control-sm" value="{{ old('seo_title', $page->seo_title) }}" oninput="syncSeoPreview()">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Focus Keyphrase</label>
                                <input type="text" name="focus_keyphrase" class="form-control form-control-sm" value="{{ old('focus_keyphrase', $page->focus_keyphrase) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Meta Description</label>
                                <textarea name="seo_description" id="seoDesc" class="form-control form-control-sm" rows="3" oninput="syncSeoPreview()">{{ old('seo_description', $page->seo_description) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Meta Keywords</label>
                                <input type="text" name="seo_keywords" class="form-control form-control-sm" value="{{ old('seo_keywords', $page->seo_keywords) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Canonical URL</label>
                                <input type="url" name="seo_canonical_url" class="form-control form-control-sm font-monospace" value="{{ old('seo_canonical_url', $page->seo_canonical_url) }}">
                            </div>

                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" name="allow_indexing" id="allowIndexing" value="1" {{ ($page->allow_indexing ?? 1) ? 'checked' : '' }}>
                                <label class="form-check-label small fw-semibold text-dark" for="allowIndexing" style="font-size: 12px; cursor: pointer;">Google Indexing</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Custom CSS & JS -->
                <div class="panel-card mb-0">
                    <div class="panel-header" data-bs-toggle="collapse" data-bs-target="#customCodePanel" style="cursor: pointer;">
                        <span><i class="fas fa-code text-info me-2"></i>Custom CSS &amp; JS</span>
                        <i class="fas fa-chevron-down small text-muted"></i>
                    </div>

                    <div class="collapse" id="customCodePanel">
                        <div class="panel-body">
                            <div class="mb-3">
                                <label class="form-label">Custom CSS (without &lt;style&gt;)</label>
                                <textarea name="custom_css" class="form-control code-editor-area" rows="4">{{ old('custom_css', $page->custom_css) }}</textarea>
                            </div>

                            <div class="mb-0">
                                <label class="form-label">Custom JS (without &lt;script&gt;)</label>
                                <textarea name="custom_js" class="form-control code-editor-area" rows="4">{{ old('custom_js', $page->custom_js) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<!-- Modal: Notice / Alert Dialog (Replaces browser alert()) -->
<div class="modal fade" id="pageEditorNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3" style="border-radius: 8px !important;">
            <div class="modal-body p-1">
                <div class="mb-2 text-warning" id="pageEditorNoticeIcon">
                    <i class="fas fa-circle-exclamation fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1" id="pageEditorNoticeTitle" style="font-size: 14px;">Notice</h6>

                <p class="text-muted small mb-3" id="pageEditorNoticeMsg" style="font-size: 12px;"></p>

                <button type="button" class="btn btn-dark btn-sm px-4 py-1 fw-semibold" style="font-size: 12px;" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>

<script>
    let activeMode = "{{ old('editor_type', $page->editor_type ?? 'visual') }}";
    let codeEditor = null;

    function showPageEditorNotice(message, title = 'Notice') {
        const titleEl = document.getElementById('pageEditorNoticeTitle');
        const msgEl = document.getElementById('pageEditorNoticeMsg');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;

        const modalEl = document.getElementById('pageEditorNoticeModal');

        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    $(document).ready(function() {

        // Custom Summernote Button for Global Media Library Integration
        const SummernoteMediaButton = function (context) {
            const ui = $.summernote.ui;

            const button = ui.button({
                contents: '<i class="note-icon-picture"></i>',
                tooltip: 'Insert Image from Media Library',

                click: function () {
                    if (typeof window.openMediaPicker === 'function') {
                        window.openMediaPicker({
                            multiple: false,

                            onSelect: function(media) {
                                if (media && media.url) {
                                    context.invoke('editor.insertImage', media.url, function ($image) {
                                        if (media.alt_text) {
                                            $image.attr('alt', media.alt_text);
                                        }

                                        $image.addClass('img-fluid rounded');
                                        $image.css('max-width', '100%');
                                    });
                                }
                            }
                        });
                    } else {
                        showPageEditorNotice('Media Library is loading, please try again.');
                    }
                }
            });

            return button.render();
        };

        // Initialize Summernote
        $('#summernoteEditor').summernote({
            tabsize: 2,
            height: 480,

            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph', 'height']],
                ['table', ['table']],
                ['insert', ['link', 'mediaImage', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],

            buttons: {
                mediaImage: SummernoteMediaButton
            },

            callbacks: {
                onImageUpload: function(files) {
                    if (files.length > 0) {
                        uploadSummernoteImageDirect(files[0]);
                    }
                }
            }
        });

        // Initialize CodeMirror
        codeEditor = CodeMirror.fromTextArea(document.getElementById("codeMirrorArea"), {
            lineNumbers: true,
            mode: "htmlmixed",
            theme: "dracula",
            lineWrapping: true,
            tabSize: 4
        });

        // Initial View set
        if (activeMode === 'code') {
            switchEditorMode('code');
        } else {
            switchEditorMode('visual');
        }

        // On Submit Sync
        document.getElementById('pageEditForm').addEventListener('submit', function() {
            if (activeMode === 'code') {
                document.getElementById('finalContentInput').value = codeEditor.getValue();
            } else {
                document.getElementById('finalContentInput').value = $('#summernoteEditor').summernote('code');
            }
        });
    });

    // Drag-and-drop into Summernote uploaded directly to Media Library
    function uploadSummernoteImageDirect(file) {
        const formData = new FormData();

        formData.append("files[]", file);

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        formData.append("_token", token);

        fetch("{{ route('admin.media.upload') }}", {
            method: "POST",

            headers: {
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },

            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && data.files && data.files.length > 0) {
                const imgUrl = data.files[0].url;

                $('#summernoteEditor').summernote('insertImage', imgUrl, function ($image) {
                    $image.addClass('img-fluid rounded');
                    $image.css('max-width', '100%');
                });
            } else {
                showPageEditorNotice('Image upload failed.', 'Upload Error');
            }
        })
        .catch(err => {
            console.error('Summernote upload error:', err);
            showPageEditorNotice('Failed to upload image into media library.', 'Upload Error');
        });
    }

    function switchEditorMode(mode) {
        activeMode = mode;

        document.getElementById('activeEditorType').value = mode;

        const btnVis = document.getElementById('btnModeVisual');
        const btnCod = document.getElementById('btnModeCode');

        if (mode === 'code') {
            const currentHtml = $('#summernoteEditor').summernote('code');

            codeEditor.setValue(currentHtml);

            document.getElementById('wrapperVisualEditor').style.display = 'none';
            document.getElementById('wrapperCodeEditor').style.display = 'block';

            btnCod.className = 'btn btn-primary btn-compact';
            btnVis.className = 'btn btn-outline-secondary btn-compact';

            setTimeout(() => {
                codeEditor.refresh();
            }, 150);

        } else {
            if (codeEditor) {
                const codeHtml = codeEditor.getValue();

                $('#summernoteEditor').summernote('code', codeHtml);
            }

            document.getElementById('wrapperCodeEditor').style.display = 'none';
            document.getElementById('wrapperVisualEditor').style.display = 'block';

            btnVis.className = 'btn btn-primary btn-compact';
            btnCod.className = 'btn btn-outline-secondary btn-compact';
        }
    }

    function insertShortcode(code) {
        if (activeMode === 'code') {
            codeEditor.replaceSelection(code);
            codeEditor.focus();
        } else {
            $('#summernoteEditor').summernote('pasteHTML', code);
        }
    }

    /* Media Library Trigger for Page Featured Image */
    function choosePageFeaturedMedia() {
        if (typeof window.openMediaPicker === 'function') {
            window.openMediaPicker({
                multiple: false,

                onSelect: function(media) {
                    if (media) {
                        document.getElementById('pageFeaturedImageInput').value = media.file_path;

                        const fileInput = document.getElementById('featuredImageFileInput');

                        if (fileInput) fileInput.value = '';

                        const previewImg = document.getElementById('imagePreview');
                        const previewContainer = document.getElementById('imagePreviewContainer');

                        if (previewImg && previewContainer) {
                            previewImg.src = media.url;
                            previewContainer.classList.remove('d-none');
                        }
                    }
                }
            });
        } else {
            showPageEditorNotice('Media Library is loading, please try again.');
        }
    }

    function clearFeaturedImage() {
        const fileInput = document.getElementById('featuredImageFileInput');

        if (fileInput) fileInput.value = '';

        const textInput = document.getElementById('pageFeaturedImageInput');

        if (textInput) textInput.value = '';

        const previewContainer = document.getElementById('imagePreviewContainer');

        if (previewContainer) previewContainer.classList.add('d-none');

        const previewImg = document.getElementById('imagePreview');

        if (previewImg) previewImg.src = '#';
    }

    function previewImage(input) {
        if (input.files && input.files[0]) {
            const textInput = document.getElementById('pageFeaturedImageInput');

            if (textInput) textInput.value = '';

            const reader = new FileReader();

            reader.onload = function(e) {
                document.getElementById('imagePreview').src = e.target.result;

                const container = document.getElementById('imagePreviewContainer');

                if (container) container.classList.remove('d-none');
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    function handleManualImageUrl(val) {
        const previewImg = document.getElementById('imagePreview');
        const container = document.getElementById('imagePreviewContainer');

        if (val && val.trim().length > 0) {
            let finalUrl = val.trim();

            if (!finalUrl.startsWith('http://') && !finalUrl.startsWith('https://')) {
                finalUrl = "{{ asset('') }}" + finalUrl.replace(/^\/+/, '');
            }

            previewImg.src = finalUrl;
            container.classList.remove('d-none');

        } else {
            container.classList.add('d-none');
        }
    }

    function syncSeoPreview() {
        const title = document.getElementById('seoTitle').value || document.getElementById('pageTitle').value || 'Page Title';
        const slug = document.getElementById('pageSlug').value || 'sample-page';
        const desc = document.getElementById('seoDesc').value || 'Meta description will appear here.';

        document.getElementById('preview-title').innerText = title + ' - {{ config("app.name") }}';
        document.getElementById('preview-slug').innerText = slug.toLowerCase().replace(/ /g, '-');
        document.getElementById('preview-desc').innerText = desc;
    }
</script>
@endsection
