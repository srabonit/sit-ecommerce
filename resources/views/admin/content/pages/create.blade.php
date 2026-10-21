@extends('layouts.admin')

@section('title', 'Add New Page - ' . config('app.name'))

@section('admin_content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/dracula.min.css">

<style>
    /* Global Strict 5px Element Radius */
    #pageCreateRoot .btn,
    #pageCreateRoot .btn-sm,
    #pageCreateRoot .btn-xs,
    #pageCreateRoot button:not(.btn-clear-featured):not(.note-btn),
    #pageCreateRoot .form-control,
    #pageCreateRoot .form-select,
    #pageCreateRoot .badge,
    #pageCreateRoot .input-group-text,
    #pageCreateRoot .alert,
    #pageCreateNoticeModal .btn {
        border-radius: 5px !important;
    }

    #pageCreateRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #pageCreateRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Compact & Sleek Button Styling */
    #pageCreateRoot .btn-compact {
        font-size: 11.5px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }

    /* Enterprise Card Surface */
    .panel-card {
        background: #ffffff;
        border-radius: 8px !important;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }
    .panel-header {
        padding: 11px 16px;
        font-weight: 700;
        font-size: 13px;
        color: #1e293b;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
        border-radius: 7px 7px 0 0;
    }
    .panel-body {
        padding: 16px;
    }

    /* Form Controls */
    #pageCreateRoot .form-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 4px;
    }
    #pageCreateRoot .form-control,
    #pageCreateRoot .form-select {
        font-size: 12.5px;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #pageCreateRoot .form-control:focus,
    #pageCreateRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }
    #pageCreateRoot #pageTitle {
        font-size: 14px;
        padding: 8px 12px;
    }

    /* Editors */
    .note-editor.note-frame {
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        margin-bottom: 0 !important;
    }
    .note-toolbar {
        background: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 6px 10px !important;
    }
    .note-editable {
        min-height: 480px !important;
        font-size: 14px;
        line-height: 1.65;
        background: #ffffff;
        color: #1e293b;
    }

    .CodeMirror {
        height: 500px !important;
        font-family: 'Fira Code', monospace !important;
        font-size: 13px !important;
        line-height: 1.6 !important;
        border-radius: 0 !important;
    }
    .code-editor-area {
        font-family: 'Fira Code', monospace !important;
        font-size: 12px !important;
        background: #f8fafc !important;
        color: #1e293b !important;
        border: 1px solid #cbd5e1 !important;
    }

    /* SEO Google Preview Card */
    .google-preview-card {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 12px;
        background: #f8fafc;
    }
    .google-url { font-size: 11px; color: #475569; line-height: 1.3; }
    .google-title { font-size: 15px; color: #1a0dab; font-weight: 600; margin-top: 2px; }
    .google-desc { font-size: 12px; color: #4d5156; line-height: 1.4; margin-top: 3px; }

    /* Media Preview Box Styling */
    .preview-featured-box {
        position: relative;
        display: inline-block;
        width: 100%;
        text-align: center;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 6px;
        padding: 8px;
    }
    .preview-featured-box img {
        max-height: 130px;
        max-width: 100%;
        object-fit: contain;
        border-radius: 5px;
    }
    .btn-clear-featured {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 24px;
        height: 24px;
        border-radius: 50% !important;
        background: #ef4444;
        color: #ffffff;
        border: 2px solid #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        transition: transform 0.15s ease;
    }
    .btn-clear-featured:hover {
        transform: scale(1.08);
        background: #dc2626;
    }

    /* Safe Bengali Digits */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 767.98px) {
        .header-top-actions {
            width: 100%;
        }
        .header-top-actions .btn-compact {
            width: 100%;
        }
        .editor-toolbar-header {
            flex-direction: column;
            align-items: stretch !important;
            gap: 8px;
        }
        .editor-mode-group {
            display: flex;
            width: 100%;
            gap: 6px;
        }
        .editor-mode-group .btn {
            flex: 1 1 0;
        }
        .shortcode-btn-wrap .btn {
            width: 100%;
        }
        .panel-body {
            padding: 14px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="pageCreateRoot">
    <!-- Top Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
        <div>
            <h5 class="mb-1 fw-bold text-dark" style="font-size: 18px;">Add New Page</h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">Create clean pages with visual rich text or raw HTML editor</small>
        </div>
        <div class="header-top-actions d-flex gap-2">
            <a href="{{ route('admin.content.pages.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-arrow-left me-1"></i>
                <span>Back to Pages</span>
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3" role="alert">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.content.pages.store') }}" method="POST" enctype="multipart/form-data" id="pageCreateForm">
        @csrf
        <input type="hidden" name="editor_type" id="activeEditorType" value="visual">

        <div class="row g-3">
            <!-- Left Side: Wide Body (col-xl-9 col-lg-8) -->
            <div class="col-12 col-lg-8 col-xl-9">
                <div class="panel-card">
                    <div class="panel-body">
                        <div class="mb-3">
                            <label class="form-label">Page Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="pageTitle" class="form-control fw-bold" placeholder=" About Us / Home" value="{{ old('title') }}" required oninput="syncSeoPreview()">
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Permalink Slug <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted small text-truncate" style="max-width: 180px;">{{ url('/') }}/</span>
                                <input type="text" name="slug" id="pageSlug" class="form-control font-monospace" placeholder="about-us" value="{{ old('slug') }}" required oninput="syncSeoPreview()">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dual Mode Editor Box -->
                <div class="panel-card overflow-hidden">
                    <div class="panel-header py-2 editor-toolbar-header">
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
                                + Products Shortcode
                            </button>
                        </div>
                    </div>

                    <!-- 1. Visual Editor Canvas -->
                    <div id="wrapperVisualEditor" class="p-0">
                        <textarea id="summernoteEditor">{{ old('content') }}</textarea>
                    </div>

                    <!-- 2. Pure Code Editor Canvas -->
                    <div id="wrapperCodeEditor" class="p-0" style="display: none;">
                        <textarea id="codeMirrorArea">{{ old('content') }}</textarea>
                    </div>

                    <textarea name="content" id="finalContentInput" style="display: none;">{{ old('content') }}</textarea>

                    <div class="py-2 px-3 bg-light border-top small text-muted" style="font-size: 11.5px;">
                        <span>Write using Visual Editor or write raw HTML code in Code Editor.</span>
                    </div>
                </div>
            </div>

            <!-- Right Side: Narrow Sidebar (col-xl-3 col-lg-4) -->
            <div class="col-12 col-lg-4 col-xl-3">
                <!-- Publish Options -->
                <div class="panel-card">
                    <div class="panel-header">Publish Options</div>
                    <div class="panel-body">
                        <div class="mb-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select form-select-sm fw-semibold">
                                <option value="published" selected>Published</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Page Width Layout</label>
                            <select name="page_width" class="form-select form-select-sm">
                                <option value="contained-wide" selected>Contained Wide</option>
                                <option value="full-width">Full Width (100% Canvas)</option>
                                <option value="boxed">Boxed (Narrow Container)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Page Title Font Size</label>
                            <input type="text" name="title_font_size" class="form-control form-control-sm font-monospace bn-digit" value="32px" placeholder=" 32px or 2rem">
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="show_title" id="showTitle" value="1" checked style="cursor: pointer;">
                            <label class="form-check-label small fw-semibold text-dark" for="showTitle" style="font-size: 12px; cursor: pointer;">Show Page Title</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="show_sidebar_widget" id="showSidebar" value="1" style="cursor: pointer;">
                            <label class="form-check-label small fw-semibold text-dark" for="showSidebar" style="font-size: 12px; cursor: pointer;">Show Right Sidebar</label>
                        </div>
                        <div class="d-grid pt-2 border-top">
                            <button type="submit" class="btn btn-primary btn-compact py-2 shadow-xs">
                                <i class="fas fa-save me-1"></i>
                                <span>Publish Page</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Featured Image with Media Library Integration -->
                <div class="panel-card">
                    <div class="panel-header">Featured Image</div>
                    <div class="panel-body">
                        <!-- Media Library Button -->
                        <button type="button" class="btn btn-outline-primary btn-compact w-100 mb-2 shadow-xs" onclick="choosePageFeaturedMedia()">
                            <i class="fas fa-images me-1"></i>
                            <span>Choose from Media Library</span>
                        </button>
                        <span class="small text-muted d-block text-center mb-2" style="font-size: 11px;">or upload local file:</span>

                        <div class="mb-2">
                            <input type="file" name="featured_image_file" id="featuredImageFileInput" class="form-control form-control-sm" accept="image/*" onchange="previewImage(this)">
                        </div>

                        <!-- Image Preview Box -->
                        <div id="imagePreviewContainer" class="preview-featured-box my-2 d-none">
                            <img id="imagePreview" src="#" alt="Featured Image Preview">
                            <button type="button" class="btn-clear-featured" onclick="clearFeaturedImage()" title="Remove Image">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Or Image Path / URL</label>
                            <input type="text" name="featured_image" id="pageFeaturedImageInput" class="form-control form-control-sm font-monospace" placeholder="uploads/media/... or https://" value="{{ old('featured_image') }}" oninput="handleManualImageUrl(this.value)">
                        </div>

                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="show_featured_image" id="showFeaturedImage" value="1" checked style="cursor: pointer;">
                            <label class="form-check-label small fw-semibold text-dark" for="showFeaturedImage" style="font-size: 12px; cursor: pointer;">Show In Page Header</label>
                        </div>
                    </div>
                </div>

                <!-- SEO (Optional) -->
                <div class="panel-card">
                    <div class="panel-header" data-bs-toggle="collapse" data-bs-target="#seoPanel" style="cursor: pointer;">
                        <span><i class="fas fa-search text-success me-1"></i> SEO (Optional)</span>
                        <i class="fas fa-chevron-down small text-muted"></i>
                    </div>
                    <div class="collapse" id="seoPanel">
                        <div class="panel-body">
                            <div class="google-preview-card mb-3">
                                <div class="google-url text-truncate">{{ url('/') }}/<span id="preview-slug">sample-page</span></div>
                                <div class="google-title text-truncate" id="preview-title">Page Title</div>
                                <div class="google-desc text-truncate" id="preview-desc">Meta description will appear here.</div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">SEO Title</label>
                                <input type="text" name="seo_title" id="seoTitle" class="form-control form-control-sm" placeholder="Page title" value="{{ old('seo_title') }}" oninput="syncSeoPreview()">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Focus Keyphrase</label>
                                <input type="text" name="focus_keyphrase" class="form-control form-control-sm" placeholder="main keyword" value="{{ old('focus_keyphrase') }}">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Meta Description</label>
                                <textarea name="seo_description" id="seoDesc" class="form-control form-control-sm" rows="3" placeholder="Search result description" oninput="syncSeoPreview()">{{ old('seo_description') }}</textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Meta Keywords</label>
                                <input type="text" name="seo_keywords" class="form-control form-control-sm" placeholder="keyword1, keyword2" value="{{ old('seo_keywords') }}">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Canonical URL</label>
                                <input type="url" name="seo_canonical_url" class="form-control form-control-sm font-monospace" placeholder="https://" value="{{ old('seo_canonical_url') }}">
                            </div>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="allow_indexing" id="allowIndexing" value="1" checked style="cursor: pointer;">
                                <label class="form-check-label small fw-semibold text-dark" for="allowIndexing" style="font-size: 12px; cursor: pointer;">Google Indexing</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Custom CSS & JS -->
                <div class="panel-card mb-0">
                    <div class="panel-header" data-bs-toggle="collapse" data-bs-target="#customCodePanel" style="cursor: pointer;">
                        <span><i class="fas fa-code text-info me-1"></i> Custom CSS &amp; JS</span>
                        <i class="fas fa-chevron-down small text-muted"></i>
                    </div>
                    <div class="collapse" id="customCodePanel">
                        <div class="panel-body">
                            <div class="mb-2">
                                <label class="form-label">Custom CSS (without &lt;style&gt;)</label>
                                <textarea name="custom_css" class="form-control code-editor-area" rows="4" placeholder=".custom-class { color: #e11d48; }">{{ old('custom_css') }}</textarea>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Custom JS (without &lt;script&gt;)</label>
                                <textarea name="custom_js" class="form-control code-editor-area" rows="4" placeholder="console.log('Page loaded');">{{ old('custom_js') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal: Notice / Alert Dialog (Replaces browser alert()) -->
<div class="modal fade" id="pageCreateNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3" style="border-radius: 8px !important;">
            <div class="modal-body p-1">
                <div class="mb-2 text-warning" id="pageCreateNoticeIcon">
                    <i class="fas fa-circle-exclamation fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="pageCreateNoticeTitle" style="font-size: 14px;">Notice</h6>
                <p class="text-muted small mb-3" id="pageCreateNoticeMsg" style="font-size: 12px;"></p>
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
    let activeMode = "visual";
    let codeEditor = null;

    function showPageCreateNotice(message, title = 'Notice') {
        const titleEl = document.getElementById('pageCreateNoticeTitle');
        const msgEl = document.getElementById('pageCreateNoticeMsg');
        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;
        const modalEl = document.getElementById('pageCreateNoticeModal');
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
                        showPageCreateNotice('Media Library is loading, please try again.');
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

        const titleInput = document.getElementById('pageTitle');
        const slugInput = document.getElementById('pageSlug');
        titleInput.addEventListener('keyup', function() {
            if(!slugInput.dataset.touched) {
                slugInput.value = titleInput.value.toLowerCase().replace(/[^\w ]+/g, '').replace(/ +/g, '-');
                syncSeoPreview();
            }
        });
        slugInput.addEventListener('input', function() {
            slugInput.dataset.touched = 'true';
        });

        document.getElementById('pageCreateForm').addEventListener('submit', function() {
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
                showPageCreateNotice('Image upload failed.', 'Upload Error');
            }
        })
        .catch(err => {
            console.error('Summernote upload error:', err);
            showPageCreateNotice('Failed to upload image into media library.', 'Upload Error');
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

            setTimeout(() => { codeEditor.refresh(); }, 150);
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
            showPageCreateNotice('Media Library is loading, please try again.');
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