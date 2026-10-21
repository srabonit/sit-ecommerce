@extends('layouts.admin')

@section('title', 'Create Email Template - Email Marketing')

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-template-create-root .btn,
    #email-marketing-template-create-root .btn-sm,
    #email-marketing-template-create-root button,
    #email-marketing-template-create-root .badge,
    #email-marketing-template-create-root .alert,
    #email-marketing-template-create-root .form-control,
    #email-marketing-template-create-root .form-select,
    #email-marketing-template-create-root .input-group-text,
    #email-marketing-template-create-root .card,
    #email-marketing-template-create-root .tag-chip,
    #email-marketing-template-create-root .collapse-toggle-btn,
    .template-modal-scope .modal-content,
    .template-modal-scope .btn {
        border-radius: 5px !important;
    }

    #email-marketing-template-create-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #email-marketing-template-create-root .input-group > .form-control {
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
    #email-marketing-template-create-root .card {
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
    #email-marketing-template-create-root .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #email-marketing-template-create-root .form-control,
    #email-marketing-template-create-root .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #email-marketing-template-create-root .form-control:focus,
    #email-marketing-template-create-root .form-select:focus {
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
        .template-create-header h5 {
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
        .header-actions-wrap .btn-save-main {
            grid-column: span 2;
        }
        .html-code-editor {
            min-height: 340px;
        }
        .preview-canvas-wrapper {
            min-height: 360px;
            padding: 6px;
        }
        #email-marketing-template-create-root .card {
            margin-bottom: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-template-create-root">

    {{-- 1. Header & Navigation (Original Clean Light Theme) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 template-create-header">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-paintbrush text-primary"></i>
                <span>Create Email Template</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Design responsive, mobile-ready HTML email templates with dynamic personalization tokens.
            </small>
        </div>

        <div class="d-flex gap-2 header-actions-wrap">
            <a href="{{ route('admin.email_marketing.templates.index') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-arrow-left me-1"></i> <span>Back to Templates</span>
            </a>
            <button type="button" class="btn btn-sm btn-outline-primary btn-compact shadow-xs fw-semibold bg-white" onclick="loadStarterTemplate()">
                <i class="fas fa-wand-magic-sparkles me-1"></i> <span>Load Starter Preset</span>
            </button>
            <button type="button" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold btn-save-main" onclick="document.getElementById('templateCreateForm').submit()">
                <i class="fas fa-save me-1"></i> <span>Save Template</span>
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

    <form action="{{ route('admin.email_marketing.templates.store') }}" method="POST" id="templateCreateForm">
        @csrf
        <input type="hidden" name="builder_data" id="builderDataInput" value="{}">

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
                            <input type="text" name="name" id="template_name" class="form-control" placeholder="e.g. Black Friday Special Promo" value="{{ old('name') }}" required>
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label" for="templateCategorySelect">
                                Category <span class="text-danger">*</span>
                            </label>
                            <select name="category" id="templateCategorySelect" class="form-select font-monospace shadow-none" onchange="handleCategoryPresetChange()" required>
                                <option value="promotional" {{ old('category') === 'promotional' ? 'selected' : '' }}>Promotional / Sale</option>
                                <option value="abandoned_cart" {{ old('category') === 'abandoned_cart' ? 'selected' : '' }}>Abandoned Cart Recovery</option>
                                <option value="newsletter" {{ old('category') === 'newsletter' ? 'selected' : '' }}>General Newsletter</option>
                                <option value="transactional" {{ old('category') === 'transactional' ? 'selected' : '' }}>Transactional / Order Update</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label" for="template_subject">Default Subject Line (Optional)</label>
                            <input type="text" name="subject" id="template_subject" class="form-control" placeholder="e.g. Exclusive Weekend Deal Inside..." value="{{ old('subject') }}">
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
                        <textarea name="html_content" id="htmlContentTextarea" class="form-control html-code-editor flex-grow-1 shadow-none" placeholder="Write or paste standard table-based responsive HTML email code here..." required>{{ old('html_content') }}</textarea>
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
    const iframe = document.getElementById('livePreviewIframe');

    function updateLivePreview() {
        if (!iframe || !textarea) return;
        let content = textarea.value;

        if (!content.trim()) {
            content = '<div style="font-family:sans-serif; text-align:center; padding:50px 20px; color:#94a3b8;"><p style="font-size:14px; margin:0;">Start typing HTML code or click "Load Starter Preset" above to render a live preview here.</p></div>';
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

    function handleCategoryPresetChange() {
        // If textarea is still default or empty, auto-refresh starter template
        if (!textarea.value.trim() || textarea.dataset.isStarter === 'true') {
            loadStarterTemplate();
        }
    }

    function loadStarterTemplate() {
        const category = document.getElementById('templateCategorySelect').value;
        let starterHtml = '';

        if (category === 'abandoned_cart') {
            starterHtml = `<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Did you forget something?</title>
</head>
<body style="margin:0; padding:0; background-color:#f8fafc; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; color:#1e293b;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center" style="padding:30px 15px;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px; background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.06); border:1px solid #e2e8f0;">
                    <tr>
                        <td align="center" style="padding:28px 20px; background-color:#03172e;">
                            <h2 style="margin:0; color:#ffffff; font-size:20px; letter-spacing:0.5px;">SHOP NOW BD</h2>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:35px 30px;">
                            <h3 style="margin-top:0; color:#0f172a; font-size:18px;">Hello {first_name},</h3>
                            <p style="font-size:14px; line-height:1.6; color:#475569;">You left items waiting in your shopping bag! We've saved them for you, but popular products can sell out quickly.</p>
                            <div style="background-color:#f1f5f9; border-radius:6px; padding:18px; margin:25px 0; text-align:center;">
                                <p style="margin:0 0 10px 0; font-size:13px; color:#64748b;">Complete your purchase today and enjoy priority delivery:</p>
                                <a href="{shop_url}/cart" style="display:inline-block; background-color:#f59e0b; color:#03172e; padding:12px 28px; text-decoration:none; font-weight:bold; border-radius:5px; font-size:14px;">Return To My Bag &rarr;</a>
                            </div>
                            <p style="font-size:13px; color:#64748b; line-height:1.5;">Need assistance? Reply directly to this email or contact our customer support team.</p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:20px 30px; background-color:#f8fafc; border-top:1px solid #e2e8f0; font-size:11px; color:#94a3b8;">
                            <p style="margin:0 0 8px 0;">You received this email because you initiated an order on our store.</p>
                            <a href="{unsubscribe_url}" style="color:#64748b; text-decoration:underline;">Unsubscribe from reminders</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>`;
        } else if (category === 'transactional') {
            starterHtml = `<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
</head>
<body style="margin:0; padding:0; background-color:#f8fafc; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; color:#1e293b;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center" style="padding:30px 15px;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px; background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.06); border:1px solid #e2e8f0;">
                    <tr>
                        <td align="center" style="padding:28px 20px; background-color:#03172e;">
                            <h2 style="margin:0; color:#ffffff; font-size:20px; letter-spacing:0.5px;">SHOP NOW BD</h2>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:35px 30px;">
                            <span style="display:inline-block; background-color:#ecfdf5; color:#059669; font-size:11px; font-weight:bold; padding:4px 10px; border-radius:20px; text-transform:uppercase; margin-bottom:12px;">Order Confirmed</span>
                            <h2 style="margin:0 0 12px 0; color:#0f172a; font-size:20px;">Thank You for Your Order, {first_name}!</h2>
                            <p style="font-size:14px; line-height:1.6; color:#475569;">We have received your order and our logistics team is currently packing your consignment.</p>
                            
                            <div style="background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:16px; margin:20px 0;">
                                <p style="margin:0; font-size:13px; color:#475569;">You can track real-time parcel dispatch status directly from your store account dashboard.</p>
                            </div>
                            
                            <div style="text-align:center; margin-top:25px;">
                                <a href="{shop_url}/orders" style="display:inline-block; background-color:#059669; color:#ffffff; padding:12px 30px; text-decoration:none; font-weight:bold; border-radius:5px; font-size:14px;">Track My Order &rarr;</a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:20px 30px; background-color:#f8fafc; border-top:1px solid #e2e8f0; font-size:11px; color:#94a3b8;">
                            <p style="margin:0 0 8px 0;">Shop Now BD &bull; Premium Online Experience.</p>
                            <a href="{unsubscribe_url}" style="color:#64748b; text-decoration:underline;">Notification Preferences</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>`;
        } else {
            starterHtml = `<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exclusive Promo</title>
</head>
<body style="margin:0; padding:0; background-color:#f8fafc; font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; color:#1e293b;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center" style="padding:30px 15px;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px; background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.06); border:1px solid #e2e8f0;">
                    <tr>
                        <td align="center" style="padding:28px 20px; background-color:#03172e;">
                            <h2 style="margin:0; color:#ffffff; font-size:20px; letter-spacing:0.5px;">SHOP NOW BD</h2>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:35px 30px; text-align:center;">
                            <span style="display:inline-block; background-color:#eff6ff; color:#2563eb; font-size:11px; font-weight:bold; padding:4px 10px; border-radius:20px; text-transform:uppercase; margin-bottom:12px;">Limited Time Offer</span>
                            <h2 style="margin:0 0 12px 0; color:#0f172a; font-size:22px;">Special Discount For You, {first_name}!</h2>
                            <p style="font-size:14px; line-height:1.6; color:#475569; max-width:480px; margin:0 auto 20px auto;">Enjoy exclusive savings on our freshest collection this week. Use the discount voucher below at checkout:</p>
                            
                            <div style="background-color:#fffbeb; border:1px dashed #f59e0b; border-radius:6px; padding:14px; display:inline-block; margin-bottom:25px;">
                                <span style="font-size:11px; color:#92400e; display:block; text-transform:uppercase; font-weight:bold;">Your Promo Coupon</span>
                                <span style="font-size:20px; font-weight:bold; color:#b45309; letter-spacing:1px;">{coupon_code}</span>
                            </div>
                            
                            <div>
                                <a href="{shop_url}/shop" style="display:inline-block; background-color:#2563eb; color:#ffffff; padding:12px 32px; text-decoration:none; font-weight:bold; border-radius:5px; font-size:14px;">Shop The Collection &rarr;</a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:20px 30px; background-color:#f8fafc; border-top:1px solid #e2e8f0; font-size:11px; color:#94a3b8;">
                            <p style="margin:0 0 8px 0;">Best Online Shop in BD &bull; All Rights Reserved.</p>
                            <a href="{unsubscribe_url}" style="color:#64748b; text-decoration:underline;">Unsubscribe from marketing emails</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>`;
        }

        textarea.value = starterHtml;
        textarea.dataset.isStarter = 'true';
        updateLivePreview();
    }

    document.addEventListener("DOMContentLoaded", function() {
        textarea.addEventListener('input', function() {
            textarea.dataset.isStarter = 'false';
            updateLivePreview();
        });

        if (!textarea.value.trim()) {
            loadStarterTemplate();
        } else {
            updateLivePreview();
        }
    });
</script>
@endsection