
@extends('layouts.admin')

@section('title', 'Edit Email Template - ' . $template->name)

@section('admin_content')

<style>
    /* =========================================================
       EMAIL TEMPLATE EDITOR - MODERN ADMIN UI
       Strict 5px Radius | Scoped Styling | Responsive
    ========================================================= */

    #email-marketing-template-edit-root {
        --et-primary: #2563eb;
        --et-primary-soft: #eff6ff;
        --et-success: #059669;
        --et-text: #0f172a;
        --et-muted: #64748b;
        --et-border: #e2e8f0;
        --et-surface: #ffffff;
        --et-bg: #f8fafc;
        color: var(--et-text);
    }

    #email-marketing-template-edit-root *,
    #email-marketing-template-edit-root *::before,
    #email-marketing-template-edit-root *::after {
        box-sizing: border-box;
    }

    /* Global Radius */
    #email-marketing-template-edit-root .btn,
    #email-marketing-template-edit-root button,
    #email-marketing-template-edit-root .badge,
    #email-marketing-template-edit-root .alert,
    #email-marketing-template-edit-root .form-control,
    #email-marketing-template-edit-root .form-select,
    #email-marketing-template-edit-root .card,
    #email-marketing-template-edit-root .tag-chip,
    #email-marketing-template-edit-root .collapse-toggle-btn,
    #email-marketing-template-edit-root .dropdown-menu,
    #email-marketing-template-edit-root .preview-canvas-wrapper,
    #email-marketing-template-edit-root iframe {
        border-radius: 5px !important;
    }

    #email-marketing-template-edit-root .input-group > .input-group-text {
        border-radius: 5px 0 0 5px !important;
    }

    #email-marketing-template-edit-root .input-group > .form-control {
        border-radius: 0 5px 5px 0 !important;
    }

    #email-marketing-template-edit-root .font-monospace {
        font-weight: 600;
    }

    /* Page Header */
    .et-page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 20px;
    }

    .et-page-title {
        font-size: 19px;
        font-weight: 750;
        color: #0f172a;
        letter-spacing: -0.35px;
        margin: 0;
    }

    .et-page-subtitle {
        color: #64748b;
        font-size: 12px;
        margin-top: 5px;
        line-height: 1.5;
    }

    .et-header-icon {
        width: 38px;
        height: 38px;
        min-width: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid #dbeafe;
        border-radius: 5px;
        font-size: 16px;
    }

    .et-header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .et-compact-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 34px;
        padding: 6px 12px !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        white-space: nowrap;
        transition: all .2s ease;
    }

    #email-marketing-template-edit-root .btn-primary {
        background: #2563eb;
        border-color: #2563eb;
    }

    #email-marketing-template-edit-root .btn-primary:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
        transform: translateY(-1px);
    }

    /* Cards */
    #email-marketing-template-edit-root .card {
        background: #fff;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .035) !important;
        overflow: hidden;
    }

    .et-section-header {
        min-height: 43px;
        padding: 10px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
    }

    .et-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 750;
        color: #1e293b;
        margin: 0;
    }

    .et-section-title i {
        color: #2563eb;
        font-size: 13px;
    }

    .et-section-number {
        width: 22px;
        height: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eff6ff;
        color: #2563eb;
        border-radius: 5px;
        font-size: 10px;
        font-weight: 800;
    }

    /* Collapse Button */
    .collapse-toggle-btn {
        width: 25px;
        height: 25px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #64748b;
        transition: all .2s ease;
    }

    .collapse-toggle-btn:hover {
        background: #2563eb;
        color: #fff;
        border-color: #2563eb;
    }

    .collapse-icon {
        font-size: 10px;
        transition: transform .2s ease;
    }

    .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* Form */
    #email-marketing-template-edit-root .form-label {
        display: block;
        color: #334155;
        font-size: 11px;
        font-weight: 750;
        margin-bottom: 6px;
    }

    #email-marketing-template-edit-root .form-control,
    #email-marketing-template-edit-root .form-select {
        min-height: 36px;
        padding: 7px 10px;
        border: 1px solid #cbd5e1;
        background-color: #fff;
        color: #0f172a;
        font-size: 12px;
        box-shadow: none;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    #email-marketing-template-edit-root .form-control:focus,
    #email-marketing-template-edit-root .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
    }

    #email-marketing-template-edit-root .form-control::placeholder {
        color: #94a3b8;
    }

    /* Category Badge */
    .et-category-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 9px !important;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #dbeafe;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .35px;
    }

    /* Editor and Preview Cards */
    .et-workspace-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        min-width: 0;
    }

    .et-workspace-card .card-body {
        flex: 1;
        min-width: 0;
    }

    .et-editor-toolbar {
        min-height: 40px;
        padding: 7px 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
    }

    .et-toolbar-label {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 11px;
        font-weight: 750;
        color: #334155;
    }

    .et-toolbar-label i {
        color: #2563eb;
    }

    /* Merge Tags */
    .et-quick-tags {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 5px;
        padding: 10px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .et-quick-tags-label {
        font-size: 9px;
        font-weight: 800;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-right: 3px;
    }

    .tag-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 7px;
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #475569;
        font-size: 9px;
        font-weight: 700;
        cursor: pointer;
        user-select: none;
        transition: all .18s ease;
    }

    .tag-chip:hover {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
        transform: translateY(-1px);
    }

    .tag-chip.tag-danger {
        color: #dc2626;
        border-color: #fecaca;
        background: #fff7f7;
    }

    .tag-chip.tag-danger:hover {
        color: #fff;
        background: #dc2626;
        border-color: #dc2626;
    }

    /* HTML Editor */
    .html-code-editor {
        width: 100%;
        min-height: 520px !important;
        height: 100%;
        resize: vertical;
        padding: 14px !important;
        background: #f8fafc !important;
        border: 1px solid #cbd5e1 !important;
        color: #0f172a !important;
        font-family: "SFMono-Regular", Consolas, "Liberation Mono", monospace !important;
        font-size: 11px !important;
        line-height: 1.65 !important;
        tab-size: 4;
        white-space: pre;
        overflow: auto;
    }

    .html-code-editor:focus {
        background: #fff !important;
        border-color: #3b82f6 !important;
    }

    .et-editor-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        background: #fff;
        border-top: 1px solid #e2e8f0;
    }

    .et-editor-hint {
        font-size: 10px;
        color: #94a3b8;
    }

    /* Dropdown */
    #email-marketing-template-edit-root .dropdown-menu {
        border: 1px solid #e2e8f0;
        padding: 5px;
        box-shadow: 0 8px 25px rgba(15, 23, 42, .10);
        max-height: 300px;
        overflow-y: auto;
    }

    #email-marketing-template-edit-root .dropdown-item {
        padding: 7px 9px;
        font-size: 11px;
        border-radius: 5px;
        color: #475569;
    }

    #email-marketing-template-edit-root .dropdown-item:hover {
        background: #eff6ff;
        color: #1d4ed8;
    }

    /* Preview */
    .et-preview-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 7px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        color: #64748b;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .3px;
    }

    .et-preview-status::before {
        content: "";
        width: 6px;
        height: 6px;
        background: #10b981;
        border-radius: 50%;
    }

    .et-viewport-controls {
        display: inline-flex;
        gap: 4px;
    }

    .et-viewport-controls .btn {
        min-height: 29px;
        padding: 4px 8px !important;
        font-size: 10px !important;
    }

    .et-viewport-controls .btn.active {
        background: #2563eb;
        color: #fff;
        border-color: #2563eb;
    }

    .preview-canvas-wrapper {
        width: 100%;
        min-height: 520px;
        padding: 12px;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        overflow: auto;
        background-color: #e9eef5;
        border: 1px solid #dbe3ed;
        -webkit-overflow-scrolling: touch;
    }

    #livePreviewIframe {
        display: block;
        flex: 0 0 auto;
        width: 100%;
        height: 520px;
        border: 1px solid #dbe3ed;
        background: #fff;
        transition: width .25s ease;
    }

    .et-preview-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        padding: 10px 2px 0;
    }

    .et-preview-note {
        color: #64748b;
        font-size: 9px;
        line-height: 1.5;
    }

    .et-refresh-btn {
        padding: 0 !important;
        font-size: 10px !important;
        color: #2563eb !important;
        text-decoration: none !important;
    }

    /* Error Alert */
    #email-marketing-template-edit-root .alert {
        border: 1px solid #fecaca !important;
        background: #fff7f7;
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        #email-marketing-template-edit-root {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .et-page-header {
            align-items: flex-start;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 15px;
        }

        .et-page-title {
            font-size: 16px;
        }

        .et-page-subtitle {
            font-size: 11px;
        }

        .et-header-actions {
            display: grid;
            width: 100%;
            grid-template-columns: 1fr 1fr;
            gap: 7px;
        }

        .et-header-actions .btn {
            width: 100%;
            min-height: 36px;
            font-size: 10px !important;
        }

        .et-header-actions .btn:last-child {
            grid-column: span 2;
        }

        .et-section-header {
            padding: 10px;
        }

        .et-section-title {
            font-size: 10.5px;
        }

        .html-code-editor {
            min-height: 360px !important;
            font-size: 10px !important;
        }

        .preview-canvas-wrapper {
            min-height: 380px;
            padding: 6px;
        }

        #livePreviewIframe {
            height: 400px;
        }

        .et-editor-footer {
            flex-wrap: wrap;
        }

        .et-preview-footer {
            align-items: flex-start;
        }

        .et-quick-tags {
            padding: 8px;
        }

        .tag-chip {
            font-size: 8.5px;
        }

        #email-marketing-template-edit-root .card {
            margin-bottom: 12px !important;
        }
    }

    @media (max-width: 380px) {
        .et-viewport-controls .btn {
            padding: 4px 6px !important;
            font-size: 9px !important;
        }

        .et-preview-status {
            font-size: 8px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative"
     id="email-marketing-template-edit-root">

    {{-- PAGE HEADER --}}
    <div class="et-page-header">

        <div class="d-flex align-items-center gap-3">

            <div class="et-header-icon">
                <i class="fas fa-paintbrush"></i>
            </div>

            <div class="min-w-0">

                <div class="d-flex align-items-center flex-wrap gap-2">

                    <h5 class="et-page-title">
                        Edit Email Template
                    </h5>

                    <span class="et-category-badge badge">
                        <i class="fas fa-layer-group"></i>
                        {{ strtoupper($template->category) }}
                    </span>

                </div>

                <p class="et-page-subtitle mb-0">
                    Update your email design, HTML source code and dynamic personalization tags.
                </p>

            </div>

        </div>

        <div class="et-header-actions">

            <a href="{{ route('admin.email_marketing.templates.index') }}"
               class="btn btn-sm btn-outline-secondary et-compact-btn">

                <i class="fas fa-arrow-left"></i>
                Back to Templates

            </a>

            <button type="button"
                    class="btn btn-sm btn-primary et-compact-btn"
                    onclick="document.getElementById('templateEditForm').requestSubmit()">

                <i class="fas fa-save"></i>
                Update Template

            </button>

        </div>

    </div>


    {{-- VALIDATION ERRORS --}}
    @if ($errors->any())

        <div class="alert alert-danger alert-dismissible fade show py-3 px-3 mb-3"
             role="alert">

            <div class="d-flex align-items-center gap-2 mb-2">

                <i class="fas fa-triangle-exclamation text-danger"></i>

                <strong class="text-dark small">
                    Please fix the following validation errors:
                </strong>

            </div>

            <ul class="mb-0 ps-4 small text-dark">

                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach

            </ul>

            <button type="button"
                    class="btn-close shadow-none"
                    data-bs-dismiss="alert"
                    aria-label="Close">
            </button>

        </div>

    @endif


    {{-- MAIN FORM --}}
    <form action="{{ route('admin.email_marketing.templates.update', $template->id) }}"
          method="POST"
          id="templateEditForm">

        @csrf

        <input type="hidden"
               name="builder_data"
               id="builderDataInput"
               value="{{ is_array($template->builder_data) ? json_encode($template->builder_data) : ($template->builder_data ?? '{}') }}">


        {{-- TEMPLATE INFORMATION --}}
        <div class="card mb-3">

            <div class="et-section-header">

                <div class="et-section-title">

                    <span class="et-section-number">01</span>

                    <i class="fas fa-id-card"></i>

                    Template Identity & Subject Details

                </div>

                <button type="button"
                        class="collapse-toggle-btn"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseTemplateMeta"
                        aria-expanded="true"
                        aria-controls="collapseTemplateMeta"
                        title="Toggle Section">

                    <i class="fas fa-chevron-down collapse-icon"></i>

                </button>

            </div>


            <div class="collapse show" id="collapseTemplateMeta">

                <div class="card-body p-3">

                    <div class="row g-3">

                        {{-- Template Name --}}
                        <div class="col-12 col-md-5">

                            <label class="form-label" for="template_name">
                                Template Name
                                <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                   name="name"
                                   id="template_name"
                                   class="form-control"
                                   placeholder="Enter template name"
                                   value="{{ old('name', $template->name) }}"
                                   required>

                        </div>


                        {{-- Category --}}
                        <div class="col-12 col-md-3">

                            <label class="form-label" for="templateCategorySelect">
                                Template Category
                                <span class="text-danger">*</span>
                            </label>

                            <select name="category"
                                    id="templateCategorySelect"
                                    class="form-select"
                                    required>

                                <option value="promotional"
                                    {{ old('category', $template->category) === 'promotional' ? 'selected' : '' }}>
                                    Promotional / Sale
                                </option>

                                <option value="abandoned_cart"
                                    {{ old('category', $template->category) === 'abandoned_cart' ? 'selected' : '' }}>
                                    Abandoned Cart
                                </option>

                                <option value="newsletter"
                                    {{ old('category', $template->category) === 'newsletter' ? 'selected' : '' }}>
                                    Newsletter
                                </option>

                                <option value="transactional"
                                    {{ old('category', $template->category) === 'transactional' ? 'selected' : '' }}>
                                    Transactional
                                </option>

                            </select>

                        </div>


                        {{-- Subject --}}
                        <div class="col-12 col-md-4">

                            <label class="form-label" for="template_subject">
                                Default Subject Line
                                <span class="text-muted fw-normal">(Optional)</span>
                            </label>

                            <input type="text"
                                   name="subject"
                                   id="template_subject"
                                   class="form-control"
                                   placeholder="Exclusive Weekend Deal Inside..."
                                   value="{{ old('subject', $template->subject) }}">

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- EDITOR & PREVIEW WORKSPACE --}}
        <div class="row g-3">

            {{-- HTML SOURCE EDITOR --}}
            <div class="col-12 col-xl-6">

                <div class="card et-workspace-card">

                    {{-- Editor Header --}}
                    <div class="et-section-header">

                        <div class="et-section-title">

                            <span class="et-section-number">02</span>

                            <i class="fas fa-code"></i>

                            HTML Email Source Code

                        </div>

                        <div class="dropdown">

                            <button class="btn btn-sm btn-outline-secondary et-compact-btn dropdown-toggle"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">

                                <i class="fas fa-tags text-primary"></i>
                                Insert Tag

                            </button>

                            <ul class="dropdown-menu dropdown-menu-end">

                                <li>
                                    <a class="dropdown-item"
                                       href="javascript:void(0)"
                                       onclick="insertToken('{first_name}')">

                                        <strong>{first_name}</strong>
                                        <span class="text-muted">- First Name</span>

                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item"
                                       href="javascript:void(0)"
                                       onclick="insertToken('{customer_name}')">

                                        <strong>{customer_name}</strong>
                                        <span class="text-muted">- Full Name</span>

                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item"
                                       href="javascript:void(0)"
                                       onclick="insertToken('{email}')">

                                        <strong>{email}</strong>
                                        <span class="text-muted">- Recipient Email</span>

                                    </a>
                                </li>

                                <li><hr class="dropdown-divider"></li>

                                <li>
                                    <a class="dropdown-item"
                                       href="javascript:void(0)"
                                       onclick="insertToken('{coupon_code}')">

                                        <strong>{coupon_code}</strong>
                                        <span class="text-muted">- Promo Coupon</span>

                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item"
                                       href="javascript:void(0)"
                                       onclick="insertToken('{shop_url}')">

                                        <strong>{shop_url}</strong>
                                        <span class="text-muted">- Store URL</span>

                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item"
                                       href="javascript:void(0)"
                                       onclick="insertToken('{unsubscribe_url}')">

                                        <strong>{unsubscribe_url}</strong>
                                        <span class="text-muted">- Unsubscribe URL</span>

                                    </a>
                                </li>

                            </ul>

                        </div>

                    </div>


                    {{-- Quick Tokens --}}
                    <div class="et-quick-tags">

                        <span class="et-quick-tags-label">
                            <i class="fas fa-bolt me-1"></i>
                            Quick Insert
                        </span>

                        <span class="tag-chip font-monospace"
                              onclick="insertToken('{first_name}')">
                            + {first_name}
                        </span>

                        <span class="tag-chip font-monospace"
                              onclick="insertToken('{customer_name}')">
                            + {customer_name}
                        </span>

                        <span class="tag-chip font-monospace"
                              onclick="insertToken('{coupon_code}')">
                            + {coupon_code}
                        </span>

                        <span class="tag-chip font-monospace"
                              onclick="insertToken('{shop_url}')">
                            + {shop_url}
                        </span>

                        <span class="tag-chip font-monospace tag-danger"
                              onclick="insertToken('{unsubscribe_url}')">
                            + {unsubscribe_url}
                        </span>

                    </div>


                    {{-- Editor Body --}}
                    <div class="card-body p-2 d-flex flex-column">

                        <textarea name="html_content"
                                  id="htmlContentTextarea"
                                  class="form-control html-code-editor shadow-none flex-grow-1"
                                  placeholder="Write or paste responsive HTML email code here..."
                                  spellcheck="false"
                                  autocapitalize="off"
                                  autocomplete="off"
                                  required>{{ old('html_content', $template->html_content) }}</textarea>

                    </div>


                    {{-- Editor Footer --}}
                    <div class="et-editor-footer">

                        <span class="et-editor-hint">
                            <i class="fas fa-code me-1"></i>
                            HTML Source Editor
                        </span>

                        <span class="et-editor-hint">
                            <i class="fas fa-keyboard me-1"></i>
                            Use merge tags for personalization
                        </span>

                    </div>

                </div>

            </div>


            {{-- LIVE PREVIEW --}}
            <div class="col-12 col-xl-6">

                <div class="card et-workspace-card">

                    {{-- Preview Header --}}
                    <div class="et-section-header flex-wrap">

                        <div class="d-flex align-items-center flex-wrap gap-2">

                            <div class="et-section-title">

                                <span class="et-section-number">03</span>

                                <i class="fas fa-eye text-success"></i>

                                Live Email Preview

                            </div>

                            <span class="et-preview-status"
                                  id="previewViewportIndicator">

                                DESKTOP VIEW

                            </span>

                        </div>


                        {{-- Viewport Switcher --}}
                        <div class="et-viewport-controls">

                            <button type="button"
                                    class="btn btn-outline-secondary active"
                                    id="btn-vp-desktop"
                                    onclick="setEditorViewport('desktop')"
                                    title="Desktop Preview">

                                <i class="fas fa-desktop me-1"></i>
                                Desktop

                            </button>

                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    id="btn-vp-mobile"
                                    onclick="setEditorViewport('mobile')"
                                    title="Mobile Preview">

                                <i class="fas fa-mobile-screen me-1"></i>
                                Mobile

                            </button>

                        </div>

                    </div>


                    {{-- Preview Body --}}
                    <div class="card-body p-2 p-md-3 bg-light">

                        <div class="preview-canvas-wrapper">

                            <iframe id="livePreviewIframe"
                                    title="Email Template Live Preview"
                                    sandbox="allow-same-origin"
                                    referrerpolicy="no-referrer">
                            </iframe>

                        </div>


                        {{-- Preview Footer --}}
                        <div class="et-preview-footer">

                            <div class="et-preview-note">

                                <i class="fas fa-shield-halved text-success me-1"></i>

                                Preview uses sample recipient data:
                                John Doe, PROMO30

                            </div>

                            <button type="button"
                                    class="btn btn-link et-refresh-btn"
                                    onclick="updateLivePreview()">

                                <i class="fas fa-rotate me-1"></i>
                                Refresh Preview

                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </form>

</div>


{{-- =========================================================
     JAVASCRIPT - EDITOR & LIVE PREVIEW
========================================================= --}}

<script>
(function () {
    'use strict';

    const textarea = document.getElementById('htmlContentTextarea');
    const iframe = document.getElementById('livePreviewIframe');

    if (!textarea || !iframe) return;

    /**
     * Render merge tags for preview only.
     * Actual textarea content remains unchanged.
     */
    function renderPreviewContent(content) {

        const previewValues = {
            '{first_name}': 'John',
            '{customer_name}': 'John Doe',
            '{email}': 'john.doe@example.com',
            '{coupon_code}': '<strong style="color:#b45309;background:#fef3c7;padding:2px 6px;border-radius:3px;">PROMO30</strong>',
            '{shop_url}': window.location.origin,
            '{store_name}': 'Shop Now BD',
            '{unsubscribe_url}': '#'
        };

        return content.replace(
            /\{first_name\}|\{customer_name\}|\{email\}|\{coupon_code\}|\{shop_url\}|\{store_name\}|\{unsubscribe_url\}/g,
            function (match) {
                return previewValues[match] ?? match;
            }
        );
    }


    /**
     * Live HTML Preview
     */
    window.updateLivePreview = function () {

        let content = textarea.value;

        if (!content.trim()) {

            content = `
                <!DOCTYPE html>
                <html>
                <head>
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                </head>
                <body style="margin:0;background:#f8fafc;font-family:Arial,sans-serif;">
                    <div style="text-align:center;padding:60px 20px;color:#94a3b8;">
                        <div style="font-size:32px;margin-bottom:12px;">&#9993;</div>
                        <p style="font-size:14px;margin:0;">
                            Your email preview will appear here.
                        </p>
                        <p style="font-size:12px;margin-top:8px;">
                            Start writing HTML code in the editor.
                        </p>
                    </div>
                </body>
                </html>
            `;

        } else {

            content = renderPreviewContent(content);

        }

        iframe.srcdoc = content;
    };


    /**
     * Desktop / Mobile Preview Switch
     */
    window.setEditorViewport = function (mode) {

        const btnDesktop = document.getElementById('btn-vp-desktop');
        const btnMobile = document.getElementById('btn-vp-mobile');
        const indicator = document.getElementById('previewViewportIndicator');

        if (!btnDesktop || !btnMobile || !indicator) return;

        if (mode === 'mobile') {

            iframe.style.width = '375px';

            btnMobile.classList.add('active');
            btnDesktop.classList.remove('active');

            indicator.innerText = 'MOBILE · 375PX';

        } else {

            iframe.style.width = '100%';

            btnDesktop.classList.add('active');
            btnMobile.classList.remove('active');

            indicator.innerText = 'DESKTOP VIEW';

        }

    };


    /**
     * Insert Merge Tag at Cursor Position
     */
    window.insertToken = function (token) {

        const start = textarea.selectionStart ?? textarea.value.length;
        const end = textarea.selectionEnd ?? start;

        const currentValue = textarea.value;

        textarea.value =
            currentValue.substring(0, start) +
            token +
            currentValue.substring(end);

        textarea.focus();

        const cursorPosition = start + token.length;

        textarea.setSelectionRange(cursorPosition, cursorPosition);

        updateLivePreview();

    };


    /**
     * Live Preview on Editor Input
     */
    textarea.addEventListener('input', function () {

        updateLivePreview();

    });


    /**
     * Initialize
     */
    updateLivePreview();

})();
</script>

@endsection
