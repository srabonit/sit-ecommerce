@extends('layouts.admin')

@section('title', 'Edit Email Template - ' . $template->name)

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-template-edit-root .btn,
    #email-marketing-template-edit-root .btn-sm,
    #email-marketing-template-edit-root button,
    #email-marketing-template-edit-root .badge,
    #email-marketing-template-edit-root .alert,
    #email-marketing-template-edit-root .form-control,
    #email-marketing-template-edit-root .form-select,
    #email-marketing-template-edit-root .input-group-text,
    #email-marketing-template-edit-root .card,
    #email-marketing-template-edit-root .tag-chip,
    #email-marketing-template-edit-root .collapse-toggle-btn {
        border-radius: 5px !important;
    }

    #email-marketing-template-edit-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #email-marketing-template-edit-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Safe Monospace & Bengali Digit Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Compact Sleek Button Styling */
    .btn-compact {
        font-size: 11px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    /* Collapsible Trigger Buttons & Rotating Chevron */
    .collapse-toggle-btn {
        width: 24px;
        height: 24px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        color: #475569;
        transition: all 0.2s ease;
    }
    .collapse-toggle-btn:hover {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }
    .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }
    .collapse-icon {
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        font-size: 10px;
    }

    /* Enterprise Surface Cards */
    #email-marketing-template-edit-root .card {
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }
    .section-header-wrap {
        padding: 10px 14px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
    }

    /* Form Controls */
    #email-marketing-template-edit-root .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #email-marketing-template-edit-root .form-control,
    #email-marketing-template-edit-root .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #email-marketing-template-edit-root .form-control:focus,
    #email-marketing-template-edit-root .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Quick Tag Insertion Chips */
    .tag-chip {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 2px 7px;
        font-size: 10px;
        font-weight: 600;
        color: #475569;
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        user-select: none;
        transition: all 0.15s ease;
    }
    .tag-chip:hover {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }

    /* HTML Code Editor Textarea */
    .html-code-editor {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
        font-size: 11.5px !important;
        line-height: 1.45 !important;
        color: #0f172a;
        background-color: #f8fafc;
        border: 1px solid #cbd5e1;
        min-height: 480px;
        resize: vertical;
        padding: 10px;
    }
    .html-code-editor:focus {
        background-color: #ffffff;
        border-color: #2563eb;
        outline: none;
    }

    /* Live Preview Container */
    .preview-canvas-wrapper {
        background-color: #f1f5f9;
        border: 1px solid #cbd5e1;
        min-height: 480px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 12px;
    }

    /* Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .template-edit-header h5 {
            font-size: 16px !important;
        }
        .header-actions-wrap {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .header-actions-wrap .btn {
            width: 100%;
            padding: 8px 6px !important;
            font-size: 11px !important;
            justify-content: center;
        }
        .html-code-editor {
            min-height: 340px;
        }
        .preview-canvas-wrapper {
            min-height: 360px;
            padding: 6px;
        }
        #email-marketing-template-edit-root .card {
            margin-bottom: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-template-edit-root">

    {{-- 1. Header & Navigation (Original Clean Light Theme) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 template-edit-header">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-paintbrush text-primary"></i>
                    <span>Edit Template: <span class="text-secondary">{{ $template->name }}</span></span>
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1" style="font-size: 10px;">
                    {{ strtoupper($template->category) }}
                </span>
            </div>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Modify responsive HTML structure, styles, and dynamic merge tags.
            </small>
        </div>

        <div class="d-flex gap-2 header-actions-wrap">
            <a href="{{ route('admin.email_marketing.templates.index') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-arrow-left me-1"></i> <span>Back to Templates</span>
            </a>
            <button type="button" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold" onclick="document.getElementById('templateEditForm').submit()">
                <i class="fas fa-save me-1"></i> <span>Update Template</span>
            </button>
        </div>
    </div>

    {{-- 2. Validation Error Alerts --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3" role="alert">
            <div class="d-flex align-items-center gap-1 mb-1">
                <i class="fas fa-triangle-exclamation text-danger fs-5"></i>
                <strong class="text-dark">Please fix the following validation errors:</strong>
            </div>
            <ul class="mb-0 ps-3 text-dark">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.email_marketing.templates.update', $template->id) }}" method="POST" id="templateEditForm">
        @csrf
        <input type="hidden" name="builder_data" id="builderDataInput" value="{{ is_array($template->builder_data) ? json_encode($template->builder_data) : ($template->builder_data ?? '{}') }}">

        {{-- 3. Row 1: Basic Template Information (Collapsible) --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                    <i class="fas fa-id-card text-primary me-1"></i> 1. Template Identity &amp; Subject Details
                </span>
                <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseTemplateMeta" aria-expanded="true" aria-controls="collapseTemplateMeta" title="Toggle Section">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
            <div class="collapse show" id="collapseTemplateMeta">
                <div class="p-3">
                    <div class="row g-2">
                        <div class="col-12 col-md-5">
                            <label class="form-label" for="template_name">
                                Template Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" id="template_name" class="form-control" value="{{ old('name', $template->name) }}" required>
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label" for="templateCategorySelect">
                                Category <span class="text-danger">*</span>
                            </label>
                            <select name="category" id="templateCategorySelect" class="form-select font-monospace shadow-none" required>
                                <option value="promotional" {{ old('category', $template->category) === 'promotional' ? 'selected' : '' }}>Promotional / Sale</option>
                                <option value="abandoned_cart" {{ old('category', $template->category) === 'abandoned_cart' ? 'selected' : '' }}>Abandoned Cart Recovery</option>
                                <option value="newsletter" {{ old('category', $template->category) === 'newsletter' ? 'selected' : '' }}>General Newsletter</option>
                                <option value="transactional" {{ old('category', $template->category) === 'transactional' ? 'selected' : '' }}>Transactional / Order Update</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label" for="template_subject">Default Subject Line (Optional)</label>
                            <input type="text" name="subject" id="template_subject" class="form-control" value="{{ old('subject', $template->subject) }}" placeholder="e.g. Exclusive Weekend Deal Inside...">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Row 2: Split Editor & Live Responsive Previewer --}}
        <div class="row g-3">
            {{-- Left Column: HTML Code & Dynamic Tags --}}
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm h-100 mb-0">
                    <div class="section-header-wrap d-flex flex-wrap justify-content-between align-items-center gap-2 bg-white">
                        <span class="fw-bold small text-dark" style="font-size: 12.5px;">
                            <i class="fas fa-code text-primary me-1"></i> HTML Email Source Code
                        </span>

                        {{-- Merge Tags Dropdown --}}
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary btn-compact dropdown-toggle py-1 px-2 shadow-none font-monospace" type="button" data-bs-toggle="dropdown" style="font-size: 10.5px;">
                                <i class="fas fa-tags me-1 text-primary"></i> <span>Insert Tag</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm font-monospace" style="font-size: 11.5px; border-radius: 5px;">
                                <li><a class="dropdown-item py-1" href="javascript:void(0)" onclick="insertToken('{first_name}')"><strong>{first_name}</strong> - First Name</a></li>
                                <li><a class="dropdown-item py-1" href="javascript:void(0)" onclick="insertToken('{customer_name}')"><strong>{customer_name}</strong> - Full Name</a></li>
                                <li><a class="dropdown-item py-1" href="javascript:void(0)" onclick="insertToken('{email}')"><strong>{email}</strong> - Recipient Email</a></li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li><a class="dropdown-item py-1" href="javascript:void(0)" onclick="insertToken('{coupon_code}')"><strong>{coupon_code}</strong> - Promo Coupon</a></li>
                                <li><a class="dropdown-item py-1" href="javascript:void(0)" onclick="insertToken('{shop_url}')"><strong>{shop_url}</strong> - Store URL</a></li>
                                <li><a class="dropdown-item py-1" href="javascript:void(0)" onclick="insertToken('{unsubscribe_url}')"><strong>{unsubscribe_url}</strong> - 1-Click Unsubscribe</a></li>
                            </ul>
                        </div>
                    </div>

                    {{-- Quick Merge Tag Chips Bar --}}
                    <div class="p-2 border-bottom bg-light bg-opacity-50 d-flex flex-wrap align-items-center gap-1">
                        <span class="text-muted font-monospace small me-1" style="font-size: 10px;">Quick Tokens:</span>
                        <span class="tag-chip font-monospace" onclick="insertToken('{first_name}')">+{first_name}</span>
                        <span class="tag-chip font-monospace" onclick="insertToken('{customer_name}')">+{customer_name}</span>
                        <span class="tag-chip font-monospace" onclick="insertToken('{coupon_code}')">+{coupon_code}</span>
                        <span class="tag-chip font-monospace" onclick="insertToken('{shop_url}')">+{shop_url}</span>
                        <span class="tag-chip font-monospace text-danger" onclick="insertToken('{unsubscribe_url}')">+{unsubscribe_url}</span>
                    </div>

                    <div class="card-body p-2 d-flex flex-column">
                        <textarea name="html_content" id="htmlContentTextarea" class="form-control html-code-editor flex-grow-1 shadow-none" placeholder="Write or paste standard table-based responsive HTML email code here..." required>{{ old('html_content', $template->html_content) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Right Column: Live Viewport Preview --}}
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm h-100 mb-0">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold small text-dark" style="font-size: 12.5px;">
                                <i class="fas fa-desktop text-success me-1"></i> Real-Time Inbox Preview
                            </span>
                            <span class="badge bg-light text-secondary border font-monospace px-2 py-0" id="previewViewportIndicator" style="font-size: 9.5px;">
                                DESKTOP (100%)
                            </span>
                        </div>

                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary btn-compact active" id="btn-vp-desktop" onclick="setEditorViewport('desktop')" title="Desktop View (100%)">
                                <i class="fas fa-desktop me-1"></i> <span>Desktop</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-compact" id="btn-vp-mobile" onclick="setEditorViewport('mobile')" title="Mobile View (375px)">
                                <i class="fas fa-mobile-screen me-1"></i> <span>Mobile</span>
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-2 p-md-3 bg-light d-flex flex-column justify-content-center align-items-center">
                        <div class="preview-canvas-wrapper w-100 shadow-xs" style="border-radius: 5px;">
                            <iframe id="livePreviewIframe" style="width: 100%; height: 500px; border: 1px solid #cbd5e1; border-radius: 5px; background: #ffffff; transition: width 0.25s ease;"></iframe>
                        </div>
                        <div class="w-100 d-flex justify-content-between align-items-center pt-2 px-1">
                            <small class="text-muted font-monospace" style="font-size: 10px;">
                                <i class="fas fa-shield-halved text-success me-1"></i> Simulating recipient tags: John Doe, PROMO30, store domain
                            </small>
                            <button type="button" class="btn btn-link btn-compact p-0 text-decoration-none text-primary font-monospace" style="font-size: 10.5px;" onclick="updateLivePreview()">
                                <i class="fas fa-sync-alt me-1"></i> Refresh Preview
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    const textarea = document.getElementById('htmlContentTextarea');
    const iframe   = document.getElementById('livePreviewIframe');

    function updateLivePreview() {
        if (!iframe || !textarea) return;
        let content = textarea.value;

        if (!content.trim()) {
            content = '<div style="font-family:sans-serif; text-align:center; padding:50px 20px; color:#94a3b8;"><p style="font-size:14px; margin:0;">Template content is empty. Type HTML code to render a preview.</p></div>';
        }

        // Mock merge tag rendering in preview
        content = content.replace(/{first_name}/g, 'John')
                         .replace(/{customer_name}/g, 'John Doe')
                         .replace(/{email}/g, 'john.doe@example.com')
                         .replace(/{coupon_code}/g, '<strong style="color:#d97706; background:#fef3c7; padding:2px 6px; border-radius:3px;">PROMO30</strong>')
                         .replace(/{shop_url}/g, window.location.origin)
                         .replace(/{store_name}/g, 'Shop Now BD')
                         .replace(/{unsubscribe_url}/g, '#');

        iframe.srcdoc = content;
    }

    function setEditorViewport(mode) {
        const btnDesktop = document.getElementById('btn-vp-desktop');
        const btnMobile  = document.getElementById('btn-vp-mobile');
        const indicator  = document.getElementById('previewViewportIndicator');

        if (mode === 'mobile') {
            iframe.style.width = '375px';
            btnMobile.classList.add('active');
            btnDesktop.classList.remove('active');
            if (indicator) indicator.innerText = 'MOBILE (375PX)';
        } else {
            iframe.style.width = '100%';
            btnDesktop.classList.add('active');
            btnMobile.classList.remove('active');
            if (indicator) indicator.innerText = 'DESKTOP (100%)';
        }
    }

    function insertToken(token) {
        if (!textarea) return;
        const start = textarea.selectionStart || 0;
        const end   = textarea.selectionEnd || 0;
        const text  = textarea.value;
        textarea.value = text.substring(0, start) + token + text.substring(end);
        textarea.focus();
        textarea.selectionStart = textarea.selectionEnd = start + token.length;
        updateLivePreview();
    }

    document.addEventListener("DOMContentLoaded", function() {
        textarea.addEventListener('input', updateLivePreview);
        updateLivePreview();
    });
</script>
@endsection