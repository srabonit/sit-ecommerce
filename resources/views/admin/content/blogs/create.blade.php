@extends('layouts.admin')

@section('title', 'Add New Blog Post - ' . config('app.name'))

@section('admin_content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/dracula.min.css">

<style>
    /* =========================================================
       BLOG CREATE — KAIADMIN LITE v1.2.0 CLASSIC / WHITE
    ========================================================= */

    #blogCreateRoot {
        --bc-primary: var(--bs-primary, #3b82f6);
        --bc-primary-soft: #eff6ff;
        --bc-text: #344054;
        --bc-heading: #1f2937;
        --bc-muted: #667085;
        --bc-border: #e5e7eb;
        --bc-light: #f8fafc;
        --bc-white: #ffffff;
        --bc-success: #16a34a;
        --bc-info: #0891b2;
        color: var(--bc-text);
        font-size: 13px;
        min-width: 0;
    }

    #blogCreateRoot *,
    #blogCreateNoticeModal * {
        box-sizing: border-box;
    }

    /* Consistent Radius */
    #blogCreateRoot .btn,
    #blogCreateRoot button:not(.btn-clear-featured):not(.note-btn),
    #blogCreateRoot .form-control,
    #blogCreateRoot .form-select,
    #blogCreateRoot .badge,
    #blogCreateRoot .input-group-text,
    #blogCreateRoot .alert,
    #blogCreateNoticeModal .btn {
        border-radius: 5px !important;
    }

    #blogCreateRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #blogCreateRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Buttons */
    #blogCreateRoot .btn-compact,
    #blogCreateNoticeModal .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 6px 12px !important;
        font-size: 11.5px !important;
        line-height: 1.4 !important;
        font-weight: 600 !important;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #blogCreateRoot .btn-compact:hover {
        transform: translateY(-1px);
    }

    /* Page Header */
    #blogCreateRoot .blog-create-title {
        color: var(--bc-heading);
        font-size: 18px;
        font-weight: 700;
        letter-spacing: -.3px;
    }

    #blogCreateRoot .blog-create-subtitle {
        color: var(--bc-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    /* Cards */
    #blogCreateRoot .panel-card {
        background: var(--bc-white);
        border: 1px solid var(--bc-border);
        border-radius: 7px !important;
        box-shadow: 0 1px 3px rgba(16, 24, 40, .04);
        margin-bottom: 15px;
        min-width: 0;
    }

    #blogCreateRoot .panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        min-height: 43px;
        padding: 10px 15px;
        background: #f8fafc;
        border-bottom: 1px solid var(--bc-border);
        border-radius: 7px 7px 0 0;
        color: var(--bc-heading);
        font-size: 12.5px;
        font-weight: 700;
    }

    #blogCreateRoot .panel-body {
        padding: 16px;
    }

    /* Form Labels */
    #blogCreateRoot .form-label {
        display: block;
        margin-bottom: 6px;
        color: #475467;
        font-size: 11.5px;
        font-weight: 650;
    }

    #blogCreateRoot .form-control,
    #blogCreateRoot .form-select {
        min-height: 35px;
        padding: 7px 10px;
        border: 1px solid #d0d5dd;
        background: #fff;
        color: var(--bc-text);
        font-size: 12.5px;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #blogCreateRoot .form-control::placeholder {
        color: #98a2b3;
        opacity: 1;
    }

    #blogCreateRoot .form-control:focus,
    #blogCreateRoot .form-select:focus {
        border-color: var(--bc-primary);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .11);
        outline: none;
    }

    #blogCreateRoot textarea.form-control {
        min-height: unset;
        resize: vertical;
        line-height: 1.55;
    }

    #blogCreateRoot #blogTitle {
        min-height: 42px;
        padding: 9px 12px;
        color: var(--bc-heading);
        font-size: 15px;
        font-weight: 650;
    }

    #blogCreateRoot .input-group-text {
        border-color: #d0d5dd;
        background: var(--bc-light);
        color: var(--bc-muted);
        font-size: 11px;
    }

    /* Main Editor */
    #blogCreateRoot .editor-toolbar-header {
        flex-wrap: wrap;
        padding: 9px 12px;
    }

    #blogCreateRoot .editor-mode-group {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    #blogCreateRoot .shortcode-btn-wrap {
        flex-shrink: 0;
    }

    #blogCreateRoot #wrapperVisualEditor,
    #blogCreateRoot #wrapperCodeEditor {
        min-width: 0;
        background: #fff;
    }

    #blogCreateRoot .note-editor.note-frame {
        margin-bottom: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    #blogCreateRoot .note-toolbar {
        padding: 7px 9px !important;
        border-bottom: 1px solid var(--bc-border) !important;
        background: #f8fafc !important;
    }

    #blogCreateRoot .note-btn-group .note-btn {
        border-radius: 4px !important;
        font-size: 11px !important;
    }

    #blogCreateRoot .note-editable {
        min-height: 480px !important;
        padding: 18px !important;
        background: #fff;
        color: #344054;
        font-size: 14px;
        line-height: 1.75;
    }

    #blogCreateRoot .note-statusbar {
        border-top: 1px solid var(--bc-border) !important;
        background: #f8fafc !important;
    }

    #blogCreateRoot .CodeMirror {
        height: 500px !important;
        font-family: 'Fira Code', monospace !important;
        font-size: 12.5px !important;
        line-height: 1.65 !important;
        border-radius: 0 !important;
    }

    #blogCreateRoot .code-editor-area {
        font-family: 'Fira Code', monospace !important;
        font-size: 12px !important;
        background: #f8fafc !important;
        color: #344054 !important;
        border: 1px solid #d0d5dd !important;
    }

    #blogCreateRoot .editor-footer {
        padding: 9px 13px;
        border-top: 1px solid var(--bc-border);
        background: #f8fafc;
        color: var(--bc-muted);
        font-size: 11px;
        line-height: 1.5;
    }

    /* Sidebar Form Switches */
    #blogCreateRoot .form-check {
        display: flex;
        align-items: center;
        gap: 8px;
        min-height: 25px;
        padding-left: 0;
    }

    #blogCreateRoot .form-check .form-check-input {
        float: none;
        flex-shrink: 0;
        margin: 0;
        cursor: pointer;
    }

    #blogCreateRoot .form-check .form-check-label {
        color: #475467;
        font-size: 11.5px !important;
        font-weight: 600;
        line-height: 1.4;
        cursor: pointer;
    }

    #blogCreateRoot .form-switch .form-check-input {
        width: 2em;
        height: 1.05em;
        margin-top: 0;
    }

    #blogCreateRoot .publish-submit-btn {
        min-height: 38px;
        font-size: 12px !important;
    }

    /* Featured Image */
    #blogCreateRoot .preview-featured-box {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 95px;
        padding: 10px;
        border: 1px dashed #cbd5e1;
        border-radius: 6px;
        background: #f8fafc;
        text-align: center;
    }

    #blogCreateRoot .preview-featured-box img {
        display: block;
        max-width: 100%;
        max-height: 150px;
        object-fit: contain;
        border-radius: 5px;
    }

    #blogCreateRoot .btn-clear-featured {
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
        background: #dc3545;
        color: #fff;
        font-size: 10px;
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(0, 0, 0, .15);
        transition: transform .15s ease;
    }

    #blogCreateRoot .btn-clear-featured:hover {
        transform: scale(1.08);
        background: #b91c1c;
    }

    /* SEO Preview */
    #blogCreateRoot .google-preview-card {
        padding: 13px;
        border: 1px solid var(--bc-border);
        border-radius: 6px;
        background: #fff;
    }

    #blogCreateRoot .google-url {
        color: #475467;
        font-size: 11px;
        line-height: 1.4;
    }

    #blogCreateRoot .google-title {
        margin-top: 3px;
        color: #1a0dab;
        font-size: 15px;
        font-weight: 600;
        line-height: 1.4;
    }

    #blogCreateRoot .google-desc {
        margin-top: 4px;
        color: #4d5156;
        font-size: 11.5px;
        line-height: 1.5;
    }

    /* Custom Code Fields */
    #blogCreateRoot .code-editor-area {
        min-height: 100px;
        padding: 9px 10px;
        font-family: 'Fira Code', monospace !important;
        font-size: 11.5px !important;
        line-height: 1.6;
    }

    /* Collapse Headers */
    #blogCreateRoot .panel-header[data-bs-toggle="collapse"] {
        cursor: pointer;
        user-select: none;
        transition: background .15s ease;
    }

    #blogCreateRoot .panel-header[data-bs-toggle="collapse"]:hover {
        background: #f1f5f9;
    }

    #blogCreateRoot .panel-header[data-bs-toggle="collapse"] > i:last-child {
        transition: transform .2s ease;
    }

    /* Validation */
    #blogCreateRoot .alert {
        border-radius: 5px !important;
        font-size: 12px;
    }

    #blogCreateRoot .alert ul {
        line-height: 1.7;
    }

    /* Notice Modal */
    #blogCreateNoticeModal .modal-content {
        border-radius: 8px !important;
        border: 1px solid var(--bc-border);
        box-shadow: 0 12px 35px rgba(16, 24, 40, .16);
    }

    /* Safe Numeric Typography */
    #blogCreateRoot .bn-digit,
    #blogCreateRoot .font-monospace {
        font-weight: 600 !important;
    }

    /* Responsive */
    @media (max-width: 991.98px) {
        #blogCreateRoot .panel-body {
            padding: 14px;
        }
    }

    @media (max-width: 767.98px) {
        #blogCreateRoot {
            padding-right: 10px !important;
            padding-left: 10px !important;
        }

        #blogCreateRoot .blog-create-title {
            font-size: 16px;
        }

        #blogCreateRoot .header-top-actions {
            width: 100%;
        }

        #blogCreateRoot .header-top-actions .btn-compact {
            width: 100%;
            min-height: 36px;
        }

        #blogCreateRoot .editor-toolbar-header {
            flex-direction: column;
            align-items: stretch !important;
            gap: 9px;
        }

        #blogCreateRoot .editor-mode-group {
            display: flex;
            width: 100%;
            gap: 6px;
        }

        #blogCreateRoot .editor-mode-group .btn {
            flex: 1 1 0;
            min-width: 0;
            padding: 7px 6px !important;
            font-size: 10.5px !important;
        }

        #blogCreateRoot .shortcode-btn-wrap,
        #blogCreateRoot .shortcode-btn-wrap .btn {
            width: 100%;
        }

        #blogCreateRoot .CodeMirror {
            height: 380px !important;
        }

        #blogCreateRoot .note-editable {
            min-height: 380px !important;
            padding: 13px !important;
        }

        #blogCreateRoot .panel-body {
            padding: 13px;
        }

        #blogCreateRoot .panel-header {
            padding: 10px 13px;
        }
    }

    @media (max-width: 380px) {
        #blogCreateRoot .editor-mode-group {
            flex-direction: column;
        }

        #blogCreateRoot .editor-mode-group .btn {
            width: 100%;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="blogCreateRoot">

    {{-- Page Header --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
        <div>
            <h5 class="mb-1 fw-bold text-dark blog-create-title">Add New Blog Post</h5>
            <small class="blog-create-subtitle d-block">
                Create and publish articles using the visual or HTML editor.
            </small>
        </div>

        <div class="header-top-actions d-flex gap-2">
            <a href="{{ route('admin.content.blogs.index') }}"
               class="btn btn-outline-secondary btn-compact">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Posts</span>
            </a>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 mb-3" role="alert">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.content.blogs.store') }}"
          method="POST"
          enctype="multipart/form-data"
          id="blogCreateForm">

        @csrf
        <input type="hidden" name="editor_type" id="activeEditorType" value="visual">

        <div class="row g-3">

            {{-- Main Content --}}
            <div class="col-12 col-lg-8 col-xl-9">

                {{-- Post Information --}}
                <div class="panel-card">
                    <div class="panel-body">

                        <div class="mb-3">
                            <label class="form-label">
                                Post Title <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                   name="title"
                                   id="blogTitle"
                                   class="form-control fw-bold"
                                   placeholder="Enter post title"
                                   value="{{ old('title') }}"
                                   required
                                   oninput="syncSeoPreview()">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Permalink Slug <span class="text-danger">*</span>
                            </label>

                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted small text-truncate"
                                      style="max-width: 180px;">
                                    {{ url('/blog') }}/
                                </span>

                                <input type="text"
                                       name="slug"
                                       id="blogSlug"
                                       class="form-control font-monospace"
                                       placeholder="post-slug"
                                       value="{{ old('slug') }}"
                                       required
                                       oninput="syncSeoPreview()">
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Category</label>
                                <input type="text"
                                       name="category"
                                       value="{{ old('category', 'General') }}"
                                       class="form-control form-control-sm"
                                       placeholder="Post category">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Tags (Comma-separated)</label>
                                <input type="text"
                                       name="tags"
                                       value="{{ old('tags') }}"
                                       class="form-control form-control-sm"
                                       placeholder="news, updates, lifestyle">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Short Excerpt</label>
                            <textarea name="excerpt"
                                      rows="2"
                                      class="form-control form-control-sm"
                                      placeholder="Brief summary of the post">{{ old('excerpt') }}</textarea>
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
                        <textarea id="summernoteEditor">{{ old('content') }}</textarea>
                    </div>

                    {{-- Code Editor --}}
                    <div id="wrapperCodeEditor" class="p-0" style="display: none;">
                        <textarea id="codeMirrorArea">{{ old('content') }}</textarea>
                    </div>

                    <textarea name="content"
                              id="finalContentInput"
                              style="display: none;">{{ old('content') }}</textarea>

                    <div class="editor-footer">
                        <i class="fas fa-circle-info me-1 text-primary"></i>
                        Write using the Visual Editor or switch to HTML / Code Editor for direct markup editing.
                    </div>

                </div>

            </div>

            {{-- Settings Sidebar --}}
            <div class="col-12 col-lg-4 col-xl-3">

                {{-- Publish Options --}}
                <div class="panel-card">
                    <div class="panel-header">
                        <span><i class="fas fa-sliders me-2 text-primary"></i>Publish Options</span>
                    </div>

                    <div class="panel-body">

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select form-select-sm fw-semibold">
                                <option value="published" selected>Published</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Page Width Layout</label>
                            <select name="page_width" class="form-select form-select-sm">
                                <option value="contained-wide" selected>Contained Wide</option>
                                <option value="full-width">Full Width (100% Canvas)</option>
                                <option value="boxed">Boxed (Narrow Container)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Article Title Font Size</label>
                            <input type="text"
                                   name="title_font_size"
                                   class="form-control form-control-sm font-monospace bn-digit"
                                   value="32px"
                                   placeholder="32px">
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="show_title"
                                   id="showTitle"
                                   value="1"
                                   checked
                                   style="cursor: pointer;">
                            <label class="form-check-label" for="showTitle">
                                Show Article Title
                            </label>
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="show_sidebar_left"
                                   id="showSidebarLeft"
                                   value="1"
                                   style="cursor: pointer;">
                            <label class="form-check-label" for="showSidebarLeft">
                                Show Left Sidebar (20%)
                            </label>
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="show_sidebar_right"
                                   id="showSidebarRight"
                                   value="1"
                                   checked
                                   style="cursor: pointer;">
                            <label class="form-check-label" for="showSidebarRight">
                                Show Right Sidebar (20%)
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="enable_comments"
                                   id="enableComments"
                                   value="1"
                                   checked
                                   style="cursor: pointer;">
                            <label class="form-check-label" for="enableComments">
                                Allow Comments
                            </label>
                        </div>

                        <div class="d-grid pt-3 border-top">
                            <button type="submit"
                                    class="btn btn-primary btn-compact publish-submit-btn">
                                <i class="fas fa-save"></i>
                                <span>Publish Post</span>
                            </button>
                        </div>

                    </div>
                </div>

                {{-- Featured Image --}}
                <div class="panel-card">
                    <div class="panel-header">
                        <span><i class="fas fa-image me-2 text-primary"></i>Featured Image</span>
                    </div>

                    <div class="panel-body">

                        <button type="button"
                                class="btn btn-outline-primary btn-compact w-100 mb-2"
                                onclick="chooseBlogFeaturedMedia()">
                            <i class="fas fa-images"></i>
                            <span>Choose from Media Library</span>
                        </button>

                        <span class="small text-muted d-block text-center mb-2"
                              style="font-size: 11px;">
                            Or upload a local file
                        </span>

                        <div class="mb-3">
                            <input type="file"
                                   name="featured_image_file"
                                   id="featuredImageFileInput"
                                   class="form-control form-control-sm"
                                   accept="image/*"
                                   onchange="previewImage(this)">
                        </div>

                        <div id="imagePreviewContainer" class="preview-featured-box my-2 d-none">
                            <img id="imagePreview" src="#" alt="Featured Image Preview">

                            <button type="button"
                                    class="btn-clear-featured"
                                    onclick="clearFeaturedImage()"
                                    title="Remove Image">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Or Image Path / URL</label>
                            <input type="text"
                                   name="featured_image"
                                   id="blogFeaturedImageInput"
                                   class="form-control form-control-sm font-monospace"
                                   placeholder="uploads/media/... or https://"
                                   value="{{ old('featured_image') }}"
                                   oninput="handleManualImageUrl(this.value)">
                        </div>

                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="show_featured_image"
                                   id="showFeaturedImage"
                                   value="1"
                                   checked
                                   style="cursor: pointer;">

                            <label class="form-check-label" for="showFeaturedImage">
                                Show In Article Header
                            </label>
                        </div>

                    </div>
                </div>

                {{-- SEO --}}
                <div class="panel-card">
                    <div class="panel-header"
                         data-bs-toggle="collapse"
                         data-bs-target="#seoPanel"
                         style="cursor: pointer;">

                        <span><i class="fas fa-search text-success me-2"></i>SEO (Optional)</span>
                        <i class="fas fa-chevron-down small text-muted"></i>
                    </div>

                    <div class="collapse" id="seoPanel">
                        <div class="panel-body">

                            <div class="google-preview-card mb-3">
                                <div class="google-url text-truncate">
                                    {{ url('/blog') }}/<span id="preview-slug">sample-post</span>
                                </div>

                                <div class="google-title text-truncate" id="preview-title">
                                    Post Title
                                </div>

                                <div class="google-desc text-truncate" id="preview-desc">
                                    Meta description will appear here.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">SEO Title</label>
                                <input type="text"
                                       name="seo_title"
                                       id="seoTitle"
                                       class="form-control form-control-sm"
                                       placeholder="Search result title"
                                       value="{{ old('seo_title') }}"
                                       oninput="syncSeoPreview()">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Focus Keyphrase</label>
                                <input type="text"
                                       name="focus_keyphrase"
                                       class="form-control form-control-sm"
                                       placeholder="Main keyword"
                                       value="{{ old('focus_keyphrase') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Meta Description</label>
                                <textarea name="seo_description"
                                          id="seoDesc"
                                          class="form-control form-control-sm"
                                          rows="3"
                                          placeholder="Search result summary"
                                          oninput="syncSeoPreview()">{{ old('seo_description') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Meta Keywords</label>
                                <input type="text"
                                       name="seo_keywords"
                                       class="form-control form-control-sm"
                                       placeholder="Keywords"
                                       value="{{ old('seo_keywords') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Canonical URL</label>
                                <input type="url"
                                       name="seo_canonical_url"
                                       class="form-control form-control-sm font-monospace"
                                       placeholder="https://"
                                       value="{{ old('seo_canonical_url') }}">
                            </div>

                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="allow_indexing"
                                       id="allowIndexing"
                                       value="1"
                                       checked
                                       style="cursor: pointer;">

                                <label class="form-check-label" for="allowIndexing">
                                    Google Indexing
                                </label>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Custom CSS & JS --}}
                <div class="panel-card mb-0">
                    <div class="panel-header"
                         data-bs-toggle="collapse"
                         data-bs-target="#customCodePanel"
                         style="cursor: pointer;">

                        <span><i class="fas fa-code text-info me-2"></i>Custom CSS &amp; JS</span>
                        <i class="fas fa-chevron-down small text-muted"></i>
                    </div>

                    <div class="collapse" id="customCodePanel">
                        <div class="panel-body">

                            <div class="mb-3">
                                <label class="form-label">Custom CSS (without style tags)</label>
                                <textarea name="custom_css"
                                          class="form-control code-editor-area"
                                          rows="4">{{ old('custom_css') }}</textarea>
                            </div>

                            <div class="mb-0">
                                <label class="form-label">Custom JS (without script tags)</label>
                                <textarea name="custom_js"
                                          class="form-control code-editor-area"
                                          rows="4">{{ old('custom_js') }}</textarea>
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
     id="blogCreateNoticeModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 106500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">

            <div class="modal-body p-1">

                <div class="mb-2 text-warning" id="blogCreateNoticeIcon">
                    <i class="fas fa-circle-exclamation fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1"
                    id="blogCreateNoticeTitle"
                    style="font-size: 14px;">
                    Notice
                </h6>

                <p class="text-muted small mb-3"
                   id="blogCreateNoticeMsg"
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
    let activeMode = "visual";
    let codeEditor = null;

    function showBlogCreateNotice(message, title = 'Notice') {
        const titleEl = document.getElementById('blogCreateNoticeTitle');
        const msgEl = document.getElementById('blogCreateNoticeMsg');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;

        const modalEl = document.getElementById('blogCreateNoticeModal');

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
                        showBlogCreateNotice('Media Library is loading, please try again.');
                    }
                }
            });

            return button.render();
        };

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

        codeEditor = CodeMirror.fromTextArea(document.getElementById("codeMirrorArea"), {
            lineNumbers: true,
            mode: "htmlmixed",
            theme: "dracula",
            lineWrapping: true,
            tabSize: 4
        });

        const titleInput = document.getElementById('blogTitle');
        const slugInput = document.getElementById('blogSlug');

        titleInput.addEventListener('keyup', function() {
            if (!slugInput.dataset.touched) {
                slugInput.value = titleInput.value.toLowerCase().replace(/[^\w ]+/g, '').replace(/ +/g, '-');
                syncSeoPreview();
            }
        });

        slugInput.addEventListener('input', function() {
            slugInput.dataset.touched = 'true';
        });

        document.getElementById('blogCreateForm').addEventListener('submit', function() {
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
        .then(res => {
            if (res.status === 'success' && res.files && res.files.length > 0) {
                const imgUrl = res.files[0].url;

                $('#summernoteEditor').summernote('insertImage', imgUrl, function ($image) {
                    $image.addClass('img-fluid rounded');
                    $image.css('max-width', '100%');
                });
            } else {
                showBlogCreateNotice('Image upload failed.', 'Upload Error');
            }
        })
        .catch(err => {
            console.error('Summernote upload error:', err);
            showBlogCreateNotice('Failed to upload image into media library.', 'Upload Error');
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

    /* Media Library Trigger for Featured Image */
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
            showBlogCreateNotice('Media Library is loading, please try again.');
        }
    }

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
        const title = document.getElementById('seoTitle').value || document.getElementById('blogTitle').value || 'Post Title';
        const slug = document.getElementById('blogSlug').value || 'sample-post';
        const desc = document.getElementById('seoDesc').value || 'Meta description will appear here.';

        document.getElementById('preview-title').innerText = title + ' - {{ config("app.name") }}';
        document.getElementById('preview-slug').innerText = slug.toLowerCase().replace(/ /g, '-');
        document.getElementById('preview-desc').innerText = desc;
    }
</script>
@endsection