@extends('layouts.admin')

@section('title', 'Edit Blog Post - ' . $post->title)

@section('admin_content')

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/dracula.min.css">

<style>
    /* =========================================================
       BLOG EDIT — KAIADMIN CLASSIC / WHITE THEME
    ========================================================= */

    #blogEditRoot {
        --blog-primary: var(--bs-primary, #1572e8);
        --blog-primary-soft: rgba(21, 114, 232, .08);
        --blog-text: #2a2f5b;
        --blog-muted: #8d9498;
        --blog-border: #ebedf2;
        --blog-surface: #ffffff;
        --blog-soft: #f8f9fa;
        --blog-success: #31ce36;
        --blog-danger: #f25961;

        color: var(--blog-text);
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
    }

    #blogEditRoot *,
    #blogEditNoticeModal * {
        box-sizing: border-box;
    }

    #blogEditRoot .btn,
    #blogEditRoot .form-control,
    #blogEditRoot .form-select,
    #blogEditRoot .input-group-text,
    #blogEditRoot .badge,
    #blogEditNoticeModal .btn {
        border-radius: 5px !important;
    }

    #blogEditRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #blogEditRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Page Header */
    #blogEditRoot .page-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    #blogEditRoot .page-title {
        font-size: 18px;
        font-weight: 800;
        color: var(--blog-text);
        margin: 0 0 6px;
        letter-spacing: -.35px;
    }

    #blogEditRoot .page-subtitle {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        color: var(--blog-muted);
        font-size: 11px;
    }

    #blogEditRoot .page-subtitle .slug-badge {
        display: inline-block;
        background: var(--blog-primary-soft);
        color: var(--blog-primary);
        border: 1px solid rgba(21, 114, 232, .15);
        border-radius: 4px;
        padding: 3px 7px;
        font-family: 'Fira Code', monospace;
        font-size: 10px;
        font-weight: 500;
        overflow-wrap: anywhere;
    }

    #blogEditRoot .header-top-actions {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    #blogEditRoot .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 7px 12px !important;
        min-height: 32px;
        font-size: 11px !important;
        font-weight: 700 !important;
        line-height: 1.35;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #blogEditRoot .btn-compact:hover {
        transform: translateY(-1px);
    }

    /* Cards */
    #blogEditRoot .panel-card {
        background: var(--blog-surface);
        border: 1px solid var(--blog-border);
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035);
        margin-bottom: 15px;
        min-width: 0;
    }

    #blogEditRoot .panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px 15px;
        min-height: 43px;
        background: #fff;
        border-bottom: 1px solid var(--blog-border);
        color: var(--blog-text);
        font-size: 12px;
        font-weight: 800;
    }

    #blogEditRoot .panel-header .panel-heading-icon {
        width: 27px;
        height: 27px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--blog-primary-soft);
        color: var(--blog-primary);
        border-radius: 5px;
        margin-right: 7px;
        font-size: 11px;
    }

    #blogEditRoot .panel-body {
        padding: 16px;
    }

    /* Form Controls */
    #blogEditRoot .form-label {
        display: block;
        margin-bottom: 6px;
        color: #495057;
        font-size: 11px;
        font-weight: 700;
    }

    #blogEditRoot .form-control,
    #blogEditRoot .form-select {
        min-height: 34px;
        padding: 7px 10px;
        border: 1px solid #e0e3e8;
        background-color: #fff;
        color: #495057;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 11.5px;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #blogEditRoot textarea.form-control {
        min-height: auto;
        line-height: 1.6;
    }

    #blogEditRoot .form-control::placeholder {
        color: #adb5bd;
        font-weight: 400;
    }

    #blogEditRoot .form-control:focus,
    #blogEditRoot .form-select:focus {
        border-color: var(--blog-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .09);
        outline: none;
    }

    #blogEditRoot #blogTitle {
        min-height: 42px;
        font-size: 14px;
        font-weight: 700;
    }

    #blogEditRoot .input-group-text {
        border-color: #e0e3e8;
        color: #8d9498;
        font-size: 10px;
        background: #f8f9fa;
    }

    #blogEditRoot .font-monospace,
    #blogEditRoot .code-editor-area {
        font-family: 'Fira Code', monospace !important;
        font-size: 11px !important;
    }

    #blogEditRoot .form-text {
        color: #9aa0a6;
        font-size: 10px;
    }

    /* Editor */
    #blogEditRoot .editor-toolbar-header {
        background: #fff;
        padding: 10px 13px;
    }

    #blogEditRoot .editor-mode-group {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    #blogEditRoot .editor-mode-group .btn {
        font-size: 10.5px !important;
    }

    #blogEditRoot .shortcode-btn-wrap {
        flex-shrink: 0;
    }

    #blogEditRoot .note-editor.note-frame {
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        margin-bottom: 0 !important;
    }

    #blogEditRoot .note-toolbar {
        padding: 8px 10px !important;
        background: #f8f9fa !important;
        border-bottom: 1px solid var(--blog-border) !important;
    }

    #blogEditRoot .note-btn {
        border-radius: 4px !important;
        font-size: 11px !important;
    }

    #blogEditRoot .note-editable {
        min-height: 480px !important;
        padding: 16px !important;
        color: #343a40;
        background: #fff;
        font-size: 14px;
        line-height: 1.75;
    }

    #blogEditRoot .note-statusbar {
        border-top: 1px solid var(--blog-border) !important;
    }

    #blogEditRoot .CodeMirror {
        height: 500px !important;
        font-family: 'Fira Code', monospace !important;
        font-size: 12px !important;
        line-height: 1.65 !important;
        border-radius: 0 !important;
    }

    #blogEditRoot .code-editor-area {
        background: #f8f9fa !important;
        color: #343a40 !important;
        border: 1px solid #e0e3e8 !important;
    }

    /* Editor Footer */
    #blogEditRoot .editor-footer {
        padding: 9px 13px;
        background: #f8f9fa;
        border-top: 1px solid var(--blog-border);
        color: #8d9498;
        font-size: 10.5px;
    }

    /* Sidebar */
    #blogEditRoot .sidebar-card .panel-body {
        padding: 14px;
    }

    #blogEditRoot .form-check {
        display: flex;
        align-items: center;
        gap: 7px;
        min-height: 23px;
        padding-left: 0;
    }

    #blogEditRoot .form-check .form-check-input {
        float: none;
        flex-shrink: 0;
        margin: 0;
        width: 30px;
        height: 16px;
        cursor: pointer;
        border-color: #ced4da;
        box-shadow: none;
    }

    #blogEditRoot .form-check .form-check-input:checked {
        background-color: var(--blog-primary);
        border-color: var(--blog-primary);
    }

    #blogEditRoot .form-check .form-check-label {
        color: #495057;
        font-size: 10.5px;
        font-weight: 600;
        cursor: pointer;
    }

    #blogEditRoot .publish-submit {
        padding-top: 12px;
        margin-top: 13px;
        border-top: 1px solid var(--blog-border);
    }

    #blogEditRoot .publish-submit .btn {
        min-height: 38px;
        font-size: 11.5px !important;
        font-weight: 800 !important;
    }

    /* Featured Image */
    #blogEditRoot .preview-featured-box {
        position: relative;
        display: block;
        width: 100%;
        min-height: 100px;
        padding: 12px;
        text-align: center;
        background: #f8f9fa;
        border: 1px dashed #d6dbe1;
        border-radius: 5px;
    }

    #blogEditRoot .preview-featured-box img {
        max-width: 100%;
        max-height: 150px;
        object-fit: contain;
        border-radius: 4px;
    }

    #blogEditRoot .btn-clear-featured {
        position: absolute;
        top: 6px;
        right: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 25px;
        height: 25px;
        border: 2px solid #fff;
        border-radius: 50% !important;
        background: var(--blog-danger);
        color: #fff;
        font-size: 10px;
        cursor: pointer;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .15);
    }

    #blogEditRoot .btn-clear-featured:hover {
        background: #dc3545;
    }

    /* SEO Preview */
    #blogEditRoot .google-preview-card {
        padding: 13px;
        background: #fff;
        border: 1px solid var(--blog-border);
        border-radius: 5px;
    }

    #blogEditRoot .google-url {
        color: #5f6368;
        font-size: 10px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    #blogEditRoot .google-title {
        margin-top: 3px;
        color: #1a0dab;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.4;
    }

    #blogEditRoot .google-desc {
        margin-top: 4px;
        color: #4d5156;
        font-size: 11px;
        line-height: 1.55;
    }

    /* Collapse Sections */
    #blogEditRoot .collapsible-header {
        cursor: pointer;
        user-select: none;
    }

    #blogEditRoot .collapsible-header:hover {
        background: #fafbfc;
    }

    #blogEditRoot .collapsible-header .collapse-icon {
        color: #9aa0a6;
        font-size: 10px;
        transition: transform .2s ease;
    }

    #blogEditRoot .collapsible-header[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* Validation */
    #blogEditRoot .alert {
        border-radius: 5px !important;
        font-size: 11px;
    }

    #blogEditRoot .bn-digit {
        font-weight: 700;
    }

    /* Notice Modal */
    #blogEditNoticeModal .modal-content {
        border: 0;
        border-radius: 7px !important;
        box-shadow: 0 10px 35px rgba(0, 0, 0, .15);
    }

    #blogEditNoticeModal .modal-body {
        padding: 10px;
    }

    /* Responsive */
    @media (max-width: 991.98px) {
        #blogEditRoot .panel-body {
            padding: 14px;
        }

        #blogEditRoot .note-editable {
            min-height: 420px !important;
        }

        #blogEditRoot .CodeMirror {
            height: 440px !important;
        }
    }

    @media (max-width: 767.98px) {
        #blogEditRoot {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #blogEditRoot .page-heading {
            align-items: flex-start;
        }

        #blogEditRoot .header-top-actions {
            display: flex;
            width: 100%;
        }

        #blogEditRoot .header-top-actions .btn-compact {
            flex: 1 1 0;
            padding-left: 7px !important;
            padding-right: 7px !important;
        }

        #blogEditRoot .editor-toolbar-header {
            flex-direction: column;
            align-items: stretch !important;
            gap: 9px;
        }

        #blogEditRoot .editor-mode-group {
            display: flex;
            width: 100%;
        }

        #blogEditRoot .editor-mode-group .btn {
            flex: 1 1 0;
            justify-content: center;
            padding: 7px 5px !important;
        }

        #blogEditRoot .shortcode-btn-wrap .btn {
            width: 100%;
        }

        #blogEditRoot .panel-body {
            padding: 13px;
        }

        #blogEditRoot .note-editable {
            min-height: 360px !important;
        }

        #blogEditRoot .CodeMirror {
            height: 380px !important;
        }

        #blogEditRoot .page-title {
            font-size: 17px;
        }
    }

    @media (max-width: 380px) {
        #blogEditRoot .header-top-actions .btn-compact {
            font-size: 10px !important;
        }

        #blogEditRoot .page-subtitle {
            gap: 5px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-3 py-3" id="blogEditRoot">

    {{-- Page Header --}}
    <div class="page-heading">
        <div>
            <h5 class="page-title">
                <i class="fas fa-pen-to-square text-primary me-1"></i>
                Edit Blog Post
            </h5>

            <div class="page-subtitle">
                <span>
                    <i class="fas fa-link me-1"></i>
                    Slug:
                    <span class="slug-badge">/blog/{{ $post->slug }}</span>
                </span>

                <span class="d-none d-sm-inline">•</span>

                <span class="bn-digit">
                    <i class="far fa-calendar me-1"></i>
                    Created: {{ date('d M, Y', strtotime($post->created_at)) }}
                </span>

                @if(!empty($post->updated_at) && $post->updated_at != $post->created_at)
                    <span class="d-none d-sm-inline">•</span>
                    <span class="bn-digit">
                        <i class="fas fa-clock me-1"></i>
                        Updated: {{ date('d M, Y', strtotime($post->updated_at)) }}
                    </span>
                @endif
            </div>
        </div>

        <div class="header-top-actions">
            <a href="{{ route('blogs.show', $post->slug) }}"
               target="_blank"
               class="btn btn-outline-primary btn-compact">
                <i class="fas fa-eye"></i>
                Preview Live
            </a>

            <a href="{{ route('admin.content.blogs.index') }}"
               class="btn btn-outline-secondary btn-compact">
                <i class="fas fa-arrow-left"></i>
                Back to Posts
            </a>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 mb-3" role="alert">
            <div class="d-flex align-items-start gap-2">
                <i class="fas fa-circle-exclamation mt-1"></i>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>

            <button type="button"
                    class="btn-close btn-sm shadow-none"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.content.blogs.update', $post->id) }}"
          method="POST"
          enctype="multipart/form-data"
          id="blogEditForm">

        @csrf
        @method('PUT')

        <input type="hidden"
               name="editor_type"
               id="activeEditorType"
               value="{{ old('editor_type', $post->editor_type ?? 'visual') }}">

        <div class="row g-3">

            {{-- Main Content --}}
            <div class="col-12 col-lg-8 col-xl-9">

                {{-- Post Information --}}
                <div class="panel-card">
                    <div class="panel-header">
                        <span>
                            <span class="panel-heading-icon">
                                <i class="fas fa-file-lines"></i>
                            </span>
                            Post Information
                        </span>
                    </div>

                    <div class="panel-body">

                        <div class="mb-3">
                            <label class="form-label">
                                Post Title <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                   name="title"
                                   id="blogTitle"
                                   class="form-control fw-bold"
                                   value="{{ old('title', $post->title) }}"
                                   required
                                   oninput="syncSeoPreview()">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Permalink Slug <span class="text-danger">*</span>
                            </label>

                            <div class="input-group input-group-sm">
                                <span class="input-group-text text-truncate"
                                      style="max-width: 180px;">
                                    {{ url('/blog') }}/
                                </span>

                                <input type="text"
                                       name="slug"
                                       id="blogSlug"
                                       class="form-control font-monospace"
                                       value="{{ old('slug', $post->slug) }}"
                                       required
                                       oninput="syncSeoPreview()">
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Category</label>

                                <input type="text"
                                       name="category"
                                       value="{{ old('category', $post->category ?? 'General') }}"
                                       class="form-control form-control-sm"
                                       placeholder="Post category">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Tags (Comma-separated)</label>

                                <input type="text"
                                       name="tags"
                                       value="{{ old('tags', $post->tags ?? '') }}"
                                       class="form-control form-control-sm"
                                       placeholder="news, updates, lifestyle">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Short Excerpt</label>

                            <textarea name="excerpt"
                                      rows="2"
                                      class="form-control form-control-sm"
                                      placeholder="Brief summary of the post">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                        </div>

                    </div>
                </div>

                {{-- Content Editor --}}
                <div class="panel-card overflow-hidden">

                    <div class="panel-header editor-toolbar-header">
                        <div class="editor-mode-group d-flex gap-1" role="group">

                            <button type="button"
                                    class="btn btn-primary btn-compact"
                                    id="btnModeVisual"
                                    onclick="switchEditorMode('visual')">
                                <i class="fas fa-eye"></i>
                                <span>Visual Editor</span>
                            </button>

                            <button type="button"
                                    class="btn btn-outline-secondary btn-compact"
                                    id="btnModeCode"
                                    onclick="switchEditorMode('code')">
                                <i class="fas fa-code"></i>
                                <span>HTML / Code Editor</span>
                            </button>

                        </div>

                        <div class="shortcode-btn-wrap">
                            <button type="button"
                                    class="btn btn-outline-primary btn-compact"
                                    onclick="insertShortcode('[products limit=\'4\']')">
                                <i class="fas fa-cubes me-1"></i>
                                Products Shortcode
                            </button>
                        </div>
                    </div>

                    {{-- Visual Editor --}}
                    <div id="wrapperVisualEditor" class="p-0">
                        <textarea id="summernoteEditor">{{ old('content', $post->content) }}</textarea>
                    </div>

                    {{-- Code Editor --}}
                    <div id="wrapperCodeEditor" class="p-0" style="display: none;">
                        <textarea id="codeMirrorArea">{{ old('content', $post->content) }}</textarea>
                    </div>

                    <textarea name="content"
                              id="finalContentInput"
                              style="display: none;">{{ old('content', $post->content) }}</textarea>

                    <div class="editor-footer">
                        <i class="fas fa-circle-info me-1"></i>
                        Switch between Visual Editor and Raw Code Editor without formatting loss.
                    </div>

                </div>

            </div>

            {{-- Right Sidebar --}}
            <div class="col-12 col-lg-4 col-xl-3">

                {{-- Publish Options --}}
                <div class="panel-card sidebar-card">

                    <div class="panel-header">
                        <span>
                            <span class="panel-heading-icon">
                                <i class="fas fa-paper-plane"></i>
                            </span>
                            Publish Options
                        </span>
                    </div>

                    <div class="panel-body">

                        <div class="mb-3">
                            <label class="form-label">Status</label>

                            <select name="status"
                                    class="form-select form-select-sm fw-semibold">
                                <option value="published" {{ old('status', $post->status ?? '') === 'published' ? 'selected' : '' }}>
                                    Published
                                </option>

                                <option value="draft" {{ old('status', $post->status ?? '') === 'draft' ? 'selected' : '' }}>
                                    Draft
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Page Width Layout</label>

                            <select name="page_width" class="form-select form-select-sm">
                                <option value="contained-wide" {{ old('page_width', $post->page_width ?? 'contained-wide') === 'contained-wide' ? 'selected' : '' }}>
                                    Contained Wide
                                </option>

                                <option value="full-width" {{ old('page_width', $post->page_width ?? '') === 'full-width' ? 'selected' : '' }}>
                                    Full Width (100% Canvas)
                                </option>

                                <option value="boxed" {{ old('page_width', $post->page_width ?? '') === 'boxed' ? 'selected' : '' }}>
                                    Boxed
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Article Title Font Size</label>

                            <input type="text"
                                   name="title_font_size"
                                   class="form-control form-control-sm font-monospace bn-digit"
                                   value="{{ old('title_font_size', $post->title_font_size ?? '32px') }}"
                                   placeholder="32px">
                        </div>

                        <div class="border-top pt-3">

                            <div class="form-check mb-2">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="show_title"
                                       id="showTitle"
                                       value="1"
                                       {{ old('show_title', $post->show_title ?? 1) ? 'checked' : '' }}
                                       style="cursor: pointer;">

                                <label class="form-check-label"
                                       for="showTitle">
                                    Show Article Title
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="show_sidebar_left"
                                       id="showSidebarLeft"
                                       value="1"
                                       {{ old('show_sidebar_left', $post->show_sidebar_left ?? 0) ? 'checked' : '' }}
                                       style="cursor: pointer;">

                                <label class="form-check-label"
                                       for="showSidebarLeft">
                                    Show Left Sidebar (20%)
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="show_sidebar_right"
                                       id="showSidebarRight"
                                       value="1"
                                       {{ old('show_sidebar_right', $post->show_sidebar_right ?? 1) ? 'checked' : '' }}
                                       style="cursor: pointer;">

                                <label class="form-check-label"
                                       for="showSidebarRight">
                                    Show Right Sidebar (20%)
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="enable_comments"
                                       id="enableComments"
                                       value="1"
                                       {{ old('enable_comments', $post->enable_comments ?? 1) ? 'checked' : '' }}
                                       style="cursor: pointer;">

                                <label class="form-check-label"
                                       for="enableComments">
                                    Allow Comments
                                </label>
                            </div>

                        </div>

                        <div class="publish-submit">
                            <button type="submit"
                                    class="btn btn-primary btn-compact w-100 py-2">
                                <i class="fas fa-save"></i>
                                Update Post
                            </button>
                        </div>

                    </div>
                </div>

                {{-- Featured Image --}}
                <div class="panel-card sidebar-card">

                    <div class="panel-header">
                        <span>
                            <span class="panel-heading-icon">
                                <i class="fas fa-image"></i>
                            </span>
                            Featured Image
                        </span>
                    </div>

                    <div class="panel-body">

                        <button type="button"
                                class="btn btn-outline-primary btn-compact w-100 mb-3"
                                onclick="chooseBlogFeaturedMedia()">
                            <i class="fas fa-images"></i>
                            Choose from Media Library
                        </button>

                        <div class="text-center text-muted mb-2" style="font-size: 10px;">
                            OR UPLOAD LOCAL FILE
                        </div>

                        <div class="mb-3">
                            <input type="file"
                                   name="featured_image_file"
                                   id="featuredImageFileInput"
                                   class="form-control form-control-sm"
                                   accept="image/*"
                                   onchange="previewImage(this)">
                        </div>

                        @php
                            $blogImgSrc = null;

                            if (!empty($post->featured_image)) {
                                if (function_exists('show_image')) {
                                    $blogImgSrc = show_image($post->featured_image);
                                } elseif (filter_var($post->featured_image, FILTER_VALIDATE_URL)) {
                                    $blogImgSrc = $post->featured_image;
                                } else {
                                    $bi = ltrim(str_replace(['storage/', 'public/'], '', $post->featured_image), '/');

                                    $publicRoot = is_dir(base_path('../public_html'))
                                        ? base_path('../public_html')
                                        : public_path();

                                    if (file_exists($publicRoot . '/uploads/' . $bi)) {
                                        $blogImgSrc = asset('uploads/' . $bi);
                                    } elseif (file_exists($publicRoot . '/' . $bi)) {
                                        $blogImgSrc = asset($bi);
                                    } else {
                                        $blogImgSrc = asset('uploads/' . $bi);
                                    }
                                }
                            }
                        @endphp

                        {{-- Image Preview --}}
                        <div id="imagePreviewContainer"
                             class="preview-featured-box my-2 {{ empty($blogImgSrc) ? 'd-none' : '' }}">

                            <img id="imagePreview"
                                 src="{{ $blogImgSrc ?? '#' }}"
                                 alt="Featured Image Preview"
                                 onerror="this.onerror=null; this.src='https://placehold.co/150x100?text=No+Image';">

                            <button type="button"
                                    class="btn-clear-featured"
                                    onclick="clearFeaturedImage()"
                                    title="Remove Image">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Or Image Path / URL</label>

                            <input type="text"
                                   name="featured_image"
                                   id="blogFeaturedImageInput"
                                   class="form-control form-control-sm font-monospace"
                                   placeholder="uploads/media/... or https://"
                                   value="{{ old('featured_image', $post->featured_image) }}"
                                   oninput="handleManualImageUrl(this.value)">
                        </div>

                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="show_featured_image"
                                   id="showFeaturedImage"
                                   value="1"
                                   {{ old('show_featured_image', $post->show_featured_image ?? 1) ? 'checked' : '' }}
                                   style="cursor: pointer;">

                            <label class="form-check-label"
                                   for="showFeaturedImage">
                                Show In Article Header
                            </label>
                        </div>

                    </div>
                </div>

                {{-- SEO --}}
                <div class="panel-card sidebar-card">

                    <div class="panel-header collapsible-header"
                         data-bs-toggle="collapse"
                         data-bs-target="#seoPanel"
                         aria-expanded="false">

                        <span>
                            <span class="panel-heading-icon">
                                <i class="fas fa-search"></i>
                            </span>
                            SEO Settings
                            <span class="text-muted fw-normal">(Optional)</span>
                        </span>

                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </div>

                    <div class="collapse" id="seoPanel">
                        <div class="panel-body">

                            <div class="google-preview-card mb-3">
                                <div class="google-url text-truncate">
                                    {{ url('/blog') }}/<span id="preview-slug">{{ $post->slug }}</span>
                                </div>

                                <div class="google-title text-truncate"
                                     id="preview-title">
                                    {{ $post->seo_title ?: $post->title }}
                                </div>

                                <div class="google-desc text-truncate"
                                     id="preview-desc">
                                    {{ $post->seo_description ?: 'Meta description will appear here.' }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">SEO Title</label>

                                <input type="text"
                                       name="seo_title"
                                       id="seoTitle"
                                       class="form-control form-control-sm"
                                       value="{{ old('seo_title', $post->seo_title) }}"
                                       oninput="syncSeoPreview()">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Focus Keyphrase</label>

                                <input type="text"
                                       name="focus_keyphrase"
                                       class="form-control form-control-sm"
                                       value="{{ old('focus_keyphrase', $post->focus_keyphrase) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Meta Description</label>

                                <textarea name="seo_description"
                                          id="seoDesc"
                                          class="form-control form-control-sm"
                                          rows="3"
                                          oninput="syncSeoPreview()">{{ old('seo_description', $post->seo_description) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Meta Keywords</label>

                                <input type="text"
                                       name="seo_keywords"
                                       class="form-control form-control-sm"
                                       value="{{ old('seo_keywords', $post->seo_keywords) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Canonical URL</label>

                                <input type="url"
                                       name="seo_canonical_url"
                                       class="form-control form-control-sm font-monospace"
                                       value="{{ old('seo_canonical_url', $post->seo_canonical_url) }}">
                            </div>

                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="allow_indexing"
                                       id="allowIndexing"
                                       value="1"
                                       {{ old('allow_indexing', $post->allow_indexing ?? 1) ? 'checked' : '' }}
                                       style="cursor: pointer;">

                                <label class="form-check-label"
                                       for="allowIndexing">
                                    Allow Google Indexing
                                </label>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Custom CSS & JS --}}
                <div class="panel-card sidebar-card mb-0">

                    <div class="panel-header collapsible-header"
                         data-bs-toggle="collapse"
                         data-bs-target="#customCodePanel"
                         aria-expanded="false">

                        <span>
                            <span class="panel-heading-icon">
                                <i class="fas fa-code"></i>
                            </span>
                            Custom CSS &amp; JS
                        </span>

                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </div>

                    <div class="collapse" id="customCodePanel">
                        <div class="panel-body">

                            <div class="mb-3">
                                <label class="form-label">Custom CSS (without style tags)</label>

                                <textarea name="custom_css"
                                          class="form-control code-editor-area"
                                          rows="4">{{ old('custom_css', $post->custom_css) }}</textarea>
                            </div>

                            <div class="mb-0">
                                <label class="form-label">Custom JS (without script tags)</label>

                                <textarea name="custom_js"
                                          class="form-control code-editor-area"
                                          rows="4">{{ old('custom_js', $post->custom_js) }}</textarea>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

{{-- Notice Modal --}}
<div class="modal fade"
     id="blogEditNoticeModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 106500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-3">

            <div class="modal-body p-1">

                <div class="mb-2 text-warning" id="blogEditNoticeIcon">
                    <i class="fas fa-circle-exclamation fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1"
                    id="blogEditNoticeTitle"
                    style="font-size: 14px;">
                    Notice
                </h6>

                <p class="text-muted small mb-3"
                   id="blogEditNoticeMsg"
                   style="font-size: 12px; line-height: 1.45;"></p>

                <button type="button"
                        class="btn btn-dark btn-compact px-4"
                        data-bs-dismiss="modal">
                    OK
                </button>

            </div>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>

<script>
    let activeMode = "{{ old('editor_type', $post->editor_type ?? 'visual') }}";
    let codeEditor = null;

    function showBlogEditNotice(message, title = 'Notice') {
        const titleEl = document.getElementById('blogEditNoticeTitle');
        const msgEl = document.getElementById('blogEditNoticeMsg');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;

        const modalEl = document.getElementById('blogEditNoticeModal');

        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    $(document).ready(function() {

        // Custom Summernote Media Library Button
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
                        showBlogEditNotice('Media Library is loading, please try again.');
                    }
                }
            });

            return button.render();
        };

        // Summernote Initialization
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

        // CodeMirror Initialization
        codeEditor = CodeMirror.fromTextArea(
            document.getElementById("codeMirrorArea"),
            {
                lineNumbers: true,
                mode: "htmlmixed",
                theme: "dracula",
                lineWrapping: true,
                tabSize: 4
            }
        );

        // Restore Editor Mode
        if (activeMode === 'code') {
            switchEditorMode('code');
        } else {
            switchEditorMode('visual');
        }

        // Sync Editor Content Before Submit
        document.getElementById('blogEditForm').addEventListener('submit', function() {

            if (activeMode === 'code') {
                document.getElementById('finalContentInput').value = codeEditor.getValue();
            } else {
                document.getElementById('finalContentInput').value =
                    $('#summernoteEditor').summernote('code');
            }

        });
    });

    // Upload Summernote Image Directly to Media Library
    function uploadSummernoteImageDirect(file) {

        const formData = new FormData();

        formData.append("files[]", file);

        const token =
            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || '{{ csrf_token() }}';

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
                showBlogEditNotice('Image upload failed.', 'Upload Error');
            }

        })
        .catch(err => {

            console.error('Summernote upload error:', err);

            showBlogEditNotice(
                'Failed to upload image into media library.',
                'Upload Error'
            );

        });
    }

    // Switch Between Visual and Code Editor
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

    // Insert Shortcode
    function insertShortcode(code) {

        if (activeMode === 'code') {

            codeEditor.replaceSelection(code);
            codeEditor.focus();

        } else {

            $('#summernoteEditor').summernote('pasteHTML', code);
        }
    }

    // Featured Image Media Library
    function chooseBlogFeaturedMedia() {

        if (typeof window.openMediaPicker === 'function') {

            window.openMediaPicker({
                multiple: false,

                onSelect: function(media) {

                    if (media) {

                        document.getElementById('blogFeaturedImageInput').value = media.file_path;

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

            showBlogEditNotice('Media Library is loading, please try again.');
        }
    }

    // Remove Featured Image
    function clearFeaturedImage() {

        const fileInput = document.getElementById('featuredImageFileInput');

        if (fileInput) fileInput.value = '';

        const textInput = document.getElementById('blogFeaturedImageInput');

        if (textInput) textInput.value = '';

        const previewContainer = document.getElementById('imagePreviewContainer');

        if (previewContainer) previewContainer.classList.add('d-none');

        const previewImg = document.getElementById('imagePreview');

        if (previewImg) previewImg.src = '#';
    }

    // Local Image Preview
    function previewImage(input) {

        if (input.files && input.files[0]) {

            const textInput = document.getElementById('blogFeaturedImageInput');

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

    // Manual Image URL Preview
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

    // SEO Preview Synchronization
    function syncSeoPreview() {

        const title =
            document.getElementById('seoTitle').value
            || document.getElementById('blogTitle').value
            || 'Post Title';

        const slug =
            document.getElementById('blogSlug').value
            || 'sample-post';

        const desc =
            document.getElementById('seoDesc').value
            || 'Meta description will appear here.';

        document.getElementById('preview-title').innerText =
            title + ' - {{ config("app.name") }}';

        document.getElementById('preview-slug').innerText =
            slug.toLowerCase().replace(/ /g, '-');

        document.getElementById('preview-desc').innerText = desc;
    }
</script>

@endsection