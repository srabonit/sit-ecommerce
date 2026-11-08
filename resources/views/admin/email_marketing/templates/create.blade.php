
@extends('layouts.admin')

@section('title', 'Create Email Template - Email Marketing')

@section('admin_content')

<style>
    /* =====================================================
       EMAIL TEMPLATE BUILDER - MODERN ADMIN UI
       Theme: Clean Light / Enterprise
       Border Radius: Strict 5px
    ===================================================== */

    #email-marketing-template-create-root {
        --et-primary: #4361ee;
        --et-primary-dark: #3451d1;
        --et-primary-soft: #eef2ff;
        --et-success: #16a36a;
        --et-success-soft: #eaf8f1;
        --et-danger: #dc3545;
        --et-text: #253047;
        --et-muted: #8490a3;
        --et-border: #e7ebf2;
        --et-surface: #ffffff;
        --et-background: #f6f8fc;
        color: var(--et-text);
    }

    #email-marketing-template-create-root,
    #email-marketing-template-create-root *,
    .template-modal-scope,
    .template-modal-scope * {
        box-sizing: border-box;
    }

    #email-marketing-template-create-root .btn,
    #email-marketing-template-create-root button,
    #email-marketing-template-create-root .badge,
    #email-marketing-template-create-root .alert,
    #email-marketing-template-create-root .form-control,
    #email-marketing-template-create-root .form-select,
    #email-marketing-template-create-root .input-group-text,
    #email-marketing-template-create-root .card,
    #email-marketing-template-create-root .tag-chip,
    #email-marketing-template-create-root .collapse-toggle-btn,
    #email-marketing-template-create-root .dropdown-menu,
    #email-marketing-template-create-root .dropdown-item {
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

    /* Header */

    #email-marketing-template-create-root .template-create-header {
        padding: 17px 18px;
        background: var(--et-surface);
        border: 1px solid var(--et-border);
        border-radius: 5px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .025);
    }

    #email-marketing-template-create-root .page-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 5px;
        color: #202c42;
        font-size: 18px;
        font-weight: 800;
        letter-spacing: -.35px;
    }

    #email-marketing-template-create-root .page-title-icon {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--et-primary);
        background: var(--et-primary-soft);
        border-radius: 5px;
        font-size: 15px;
    }

    #email-marketing-template-create-root .page-description {
        margin: 0;
        color: var(--et-muted);
        font-size: 11.5px;
        line-height: 1.6;
    }

    #email-marketing-template-create-root .header-actions-wrap {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 7px;
    }

    #email-marketing-template-create-root .btn-compact {
        min-height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 6px 12px !important;
        font-size: 11px !important;
        line-height: 1.3;
        font-weight: 700 !important;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #email-marketing-template-create-root .btn-primary {
        background: var(--et-primary);
        border-color: var(--et-primary);
    }

    #email-marketing-template-create-root .btn-primary:hover {
        background: var(--et-primary-dark);
        border-color: var(--et-primary-dark);
        transform: translateY(-1px);
    }

    /* Cards */

    #email-marketing-template-create-root .card {
        overflow: hidden;
        margin-bottom: 15px;
        background: #fff;
        border: 1px solid var(--et-border) !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .035) !important;
    }

    #email-marketing-template-create-root .section-header-wrap {
        min-height: 45px;
        padding: 9px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        background: #fff;
        border-bottom: 1px solid var(--et-border);
    }

    #email-marketing-template-create-root .section-heading {
        display: flex;
        align-items: center;
        gap: 7px;
        color: #344158;
        font-size: 12px;
        font-weight: 800;
    }

    #email-marketing-template-create-root .section-heading > i {
        color: var(--et-primary);
        font-size: 13px;
    }

    #email-marketing-template-create-root .section-number {
        width: 21px;
        height: 21px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--et-primary-soft);
        color: var(--et-primary);
        border-radius: 5px;
        font-size: 10px;
        font-weight: 800;
    }

    /* Collapse Button */

    #email-marketing-template-create-root .collapse-toggle-btn {
        width: 25px;
        height: 25px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #dce2eb;
        color: #657187;
        transition: all .2s ease;
    }

    #email-marketing-template-create-root .collapse-toggle-btn:hover {
        background: var(--et-primary);
        border-color: var(--et-primary);
        color: #fff;
    }

    #email-marketing-template-create-root .collapse-icon {
        font-size: 10px;
        transition: transform .25s ease;
    }

    #email-marketing-template-create-root
    .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* Form */

    #email-marketing-template-create-root .form-label {
        display: block;
        margin-bottom: 6px;
        color: #455168;
        font-size: 11px;
        font-weight: 750;
    }

    #email-marketing-template-create-root .form-control,
    #email-marketing-template-create-root .form-select {
        min-height: 36px;
        padding: 7px 10px;
        color: #26334a;
        background-color: #fff;
        border: 1px solid #dce2eb;
        box-shadow: none;
        font-size: 12px;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    #email-marketing-template-create-root .form-control::placeholder {
        color: #a0a9b8;
        opacity: 1;
    }

    #email-marketing-template-create-root .form-control:focus,
    #email-marketing-template-create-root .form-select:focus {
        border-color: var(--et-primary);
        box-shadow: 0 0 0 3px rgba(67, 97, 238, .10);
    }

    #email-marketing-template-create-root .form-hint {
        display: block;
        margin-top: 5px;
        color: #929bad;
        font-size: 10px;
        line-height: 1.5;
    }

    /* Validation */

    #email-marketing-template-create-root .validation-alert {
        padding: 12px 14px;
        background: #fff5f5;
        border: 1px solid #f6d8db;
        color: #7f2932;
    }

    #email-marketing-template-create-root .validation-alert strong {
        font-size: 12px;
    }

    #email-marketing-template-create-root .validation-alert li {
        margin-top: 3px;
        font-size: 11px;
    }

    /* Editor Toolbar */

    #email-marketing-template-create-root .editor-toolbar {
        padding: 8px 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
        background: #fbfcfe;
        border-bottom: 1px solid var(--et-border);
    }

    #email-marketing-template-create-root .editor-toolbar-label {
        display: flex;
        align-items: center;
        gap: 7px;
        color: #344158;
        font-size: 11.5px;
        font-weight: 800;
    }

    #email-marketing-template-create-root .editor-toolbar-label i {
        color: var(--et-primary);
    }

    #email-marketing-template-create-root .editor-toolbar .dropdown-toggle {
        min-height: 29px;
        padding: 5px 9px;
        font-size: 10.5px;
        font-weight: 700;
    }

    #email-marketing-template-create-root .dropdown-menu {
        padding: 5px;
        border: 1px solid var(--et-border);
        box-shadow: 0 8px 25px rgba(15, 23, 42, .10);
    }

    #email-marketing-template-create-root .dropdown-item {
        padding: 7px 9px;
        color: #4a566c;
        font-size: 11px;
    }

    #email-marketing-template-create-root .dropdown-item:hover {
        background: var(--et-primary-soft);
        color: var(--et-primary);
    }

    #email-marketing-template-create-root .dropdown-item strong {
        color: #344158;
    }

    /* Merge Tags */

    #email-marketing-template-create-root .token-toolbar {
        padding: 9px 11px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        background: #f9faff;
        border-bottom: 1px solid var(--et-border);
    }

    #email-marketing-template-create-root .token-label {
        margin-right: 3px;
        color: #8792a5;
        font-size: 10px;
        font-weight: 700;
    }

    #email-marketing-template-create-root .tag-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 7px;
        color: #536078;
        background: #fff;
        border: 1px solid #e0e6f0;
        border-radius: 5px;
        font-family: monospace;
        font-size: 10px;
        font-weight: 700;
        cursor: pointer;
        user-select: none;
        transition: all .16s ease;
    }

    #email-marketing-template-create-root .tag-chip:hover {
        color: #fff;
        background: var(--et-primary);
        border-color: var(--et-primary);
        transform: translateY(-1px);
    }

    #email-marketing-template-create-root .tag-chip-danger {
        color: #c13b4a;
        background: #fff7f7;
        border-color: #f3dfe1;
    }

    #email-marketing-template-create-root .tag-chip-danger:hover {
        color: #fff;
        background: #dc3545;
        border-color: #dc3545;
    }

    /* HTML Editor */

    #email-marketing-template-create-root .editor-card-body {
        padding: 10px;
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
    }

    #email-marketing-template-create-root .html-code-editor {
        width: 100%;
        min-height: 520px;
        flex: 1;
        padding: 13px;
        resize: vertical;
        color: #26344c;
        background: #f8faff;
        border: 1px solid #dce3ef;
        border-radius: 5px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
        font-size: 11.5px !important;
        line-height: 1.65 !important;
        tab-size: 4;
        white-space: pre;
        overflow: auto;
    }

    #email-marketing-template-create-root .html-code-editor:focus {
        background: #fff;
        border-color: var(--et-primary);
        box-shadow: 0 0 0 3px rgba(67, 97, 238, .09);
        outline: none;
    }

    /* Preview Toolbar */

    #email-marketing-template-create-root .preview-toolbar {
        flex-wrap: wrap;
    }

    #email-marketing-template-create-root .preview-heading {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    #email-marketing-template-create-root .preview-heading > i {
        color: var(--et-success);
    }

    #email-marketing-template-create-root .preview-indicator {
        padding: 4px 7px;
        color: #63718a;
        background: #f3f5f9;
        border: 1px solid #e6eaf1;
        border-radius: 5px;
        font-family: monospace;
        font-size: 9px;
        font-weight: 700;
    }

    #email-marketing-template-create-root .viewport-buttons {
        display: inline-flex;
        gap: 4px;
    }

    #email-marketing-template-create-root .viewport-buttons .btn {
        min-height: 29px;
        padding: 5px 9px;
        font-size: 10px;
        font-weight: 700;
    }

    #email-marketing-template-create-root .viewport-buttons .btn.active {
        color: #fff;
        background: var(--et-primary);
        border-color: var(--et-primary);
    }

    /* Preview Canvas */

    #email-marketing-template-create-root .preview-card-body {
        min-height: 570px;
        padding: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #f3f6fb;
    }

    #email-marketing-template-create-root .preview-canvas-wrapper {
        width: 100%;
        min-height: 520px;
        padding: 12px;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        overflow: auto;
        background:
            linear-gradient(45deg, #e9edf4 25%, transparent 25%),
            linear-gradient(-45deg, #e9edf4 25%, transparent 25%),
            linear-gradient(45deg, transparent 75%, #e9edf4 75%),
            linear-gradient(-45deg, transparent 75%, #e9edf4 75%);
        background-size: 16px 16px;
        background-position: 0 0, 0 8px, 8px -8px, -8px 0;
        border: 1px solid #dce3ed;
        border-radius: 5px;
        -webkit-overflow-scrolling: touch;
    }

    #email-marketing-template-create-root #livePreviewIframe {
        display: block;
        width: 100%;
        height: 520px;
        flex: 0 0 auto;
        background: #fff;
        border: 1px solid #dce2eb;
        border-radius: 5px;
        box-shadow: 0 3px 12px rgba(15, 23, 42, .08);
        transition: width .25s ease;
    }

    #email-marketing-template-create-root .preview-footer {
        width: 100%;
        padding: 10px 2px 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
    }

    #email-marketing-template-create-root .preview-note {
        color: #8994a6;
        font-size: 10px;
        line-height: 1.5;
    }

    #email-marketing-template-create-root .preview-note i {
        color: var(--et-success);
    }

    #email-marketing-template-create-root .preview-refresh-btn {
        padding: 0;
        color: var(--et-primary);
        font-size: 10.5px;
        font-weight: 700;
        text-decoration: none;
    }

    #email-marketing-template-create-root .preview-refresh-btn:hover {
        color: var(--et-primary-dark);
    }

    /* Small Status Footer */

    #email-marketing-template-create-root .editor-status {
        padding: 8px 2px 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        color: #929bad;
        font-size: 10px;
    }

    #email-marketing-template-create-root .editor-status i {
        color: var(--et-success);
    }

    /* Responsive */

    @media (max-width: 1199.98px) {
        #email-marketing-template-create-root .html-code-editor {
            min-height: 430px;
        }

        #email-marketing-template-create-root .preview-card-body {
            min-height: 500px;
        }

        #email-marketing-template-create-root .preview-canvas-wrapper {
            min-height: 450px;
        }

        #email-marketing-template-create-root #livePreviewIframe {
            height: 450px;
        }
    }

    @media (max-width: 767.98px) {
        #email-marketing-template-create-root {
            padding-top: 12px !important;
        }

        #email-marketing-template-create-root .template-create-header {
            padding: 13px;
        }

        #email-marketing-template-create-root .page-title {
            font-size: 16px;
        }

        #email-marketing-template-create-root .page-title-icon {
            width: 31px;
            height: 31px;
        }

        #email-marketing-template-create-root .page-description {
            font-size: 10.5px;
        }

        #email-marketing-template-create-root .header-actions-wrap {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            margin-top: 5px;
        }

        #email-marketing-template-create-root .header-actions-wrap .btn {
            width: 100%;
            min-height: 35px;
            padding: 7px 5px !important;
            font-size: 10px !important;
        }

        #email-marketing-template-create-root .header-actions-wrap .btn-save-main {
            grid-column: 1 / -1;
        }

        #email-marketing-template-create-root .section-header-wrap {
            padding: 9px 11px;
        }

        #email-marketing-template-create-root .section-heading {
            font-size: 11px;
        }

        #email-marketing-template-create-root .card-body {
            padding: 11px !important;
        }

        #email-marketing-template-create-root .html-code-editor {
            min-height: 350px;
            font-size: 10.5px !important;
        }

        #email-marketing-template-create-root .preview-card-body {
            min-height: 400px;
            padding: 8px;
        }

        #email-marketing-template-create-root .preview-canvas-wrapper {
            min-height: 360px;
            padding: 6px;
        }

        #email-marketing-template-create-root #livePreviewIframe {
            height: 360px;
        }

        #email-marketing-template-create-root .preview-footer {
            align-items: flex-start;
            flex-direction: column;
        }

        #email-marketing-template-create-root .preview-note {
            font-size: 9px;
        }

        #email-marketing-template-create-root .token-toolbar {
            gap: 5px;
            padding: 8px;
        }

        #email-marketing-template-create-root .tag-chip {
            font-size: 9px;
            padding: 4px 6px;
        }
    }

    @media (max-width: 420px) {
        #email-marketing-template-create-root .preview-heading {
            gap: 5px;
        }

        #email-marketing-template-create-root .preview-heading .section-heading {
            font-size: 10.5px;
        }

        #email-marketing-template-create-root .viewport-buttons .btn {
            padding: 5px 7px;
            font-size: 9px;
        }

        #email-marketing-template-create-root .preview-indicator {
            font-size: 8px;
        }
    }
</style>


<div class="container-fluid py-3 px-2 px-md-4"
     id="email-marketing-template-create-root">

    {{-- PAGE HEADER --}}
    <div class="template-create-header mb-3">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">

            <div>
                <h5 class="page-title">
                    <span class="page-title-icon">
                        <i class="fas fa-paintbrush"></i>
                    </span>
                    Create Email Template
                </h5>

                <p class="page-description">
                    Build responsive HTML email templates with dynamic personalization tokens and live preview.
                </p>
            </div>

            <div class="header-actions-wrap">

                <a href="{{ route('admin.email_marketing.templates.index') }}"
                   class="btn btn-sm btn-outline-secondary btn-compact">
                    <i class="fas fa-arrow-left"></i>
                    Back to Templates
                </a>

                <button type="button"
                        class="btn btn-sm btn-outline-primary btn-compact"
                        onclick="loadStarterTemplate()">
                    <i class="fas fa-wand-magic-sparkles"></i>
                    Load Starter Preset
                </button>

                <button type="button"
                        class="btn btn-sm btn-primary btn-compact btn-save-main"
                        onclick="document.getElementById('templateCreateForm').requestSubmit()">
                    <i class="fas fa-save"></i>
                    Save Template
                </button>

            </div>
        </div>
    </div>


    {{-- VALIDATION ERRORS --}}
    @if ($errors->any())
        <div class="alert validation-alert alert-dismissible fade show mb-3"
             role="alert">

            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fas fa-triangle-exclamation text-danger"></i>
                <strong>Please fix the following validation errors:</strong>
            </div>

            <ul class="mb-0 ps-4">
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


    <form action="{{ route('admin.email_marketing.templates.store') }}"
          method="POST"
          id="templateCreateForm">

        @csrf

        <input type="hidden"
               name="builder_data"
               id="builderDataInput"
               value="{}">


        {{-- TEMPLATE INFORMATION --}}
        <div class="card mb-3">

            <div class="section-header-wrap">

                <div class="section-heading">
                    <span class="section-number">01</span>
                    <i class="fas fa-id-card"></i>
                    Template Identity &amp; Subject Details
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

                        <div class="col-12 col-md-5">
                            <label class="form-label" for="template_name">
                                Template Name <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                   name="name"
                                   id="template_name"
                                   class="form-control"
                                   placeholder="Black Friday Special Promo"
                                   value="{{ old('name') }}"
                                   required>

                            <small class="form-hint">
                                Enter a recognizable internal name for this template.
                            </small>
                        </div>


                        <div class="col-12 col-md-3">
                            <label class="form-label" for="templateCategorySelect">
                                Template Category <span class="text-danger">*</span>
                            </label>

                            <select name="category"
                                    id="templateCategorySelect"
                                    class="form-select"
                                    onchange="handleCategoryPresetChange()"
                                    required>

                                <option value="promotional" {{ old('category', 'promotional') === 'promotional' ? 'selected' : '' }}>
                                    Promotional / Sale
                                </option>

                                <option value="abandoned_cart" {{ old('category') === 'abandoned_cart' ? 'selected' : '' }}>
                                    Abandoned Cart Recovery
                                </option>

                                <option value="newsletter" {{ old('category') === 'newsletter' ? 'selected' : '' }}>
                                    General Newsletter
                                </option>

                                <option value="transactional" {{ old('category') === 'transactional' ? 'selected' : '' }}>
                                    Transactional / Order Update
                                </option>

                            </select>

                            <small class="form-hint">
                                Select the purpose of this email.
                            </small>
                        </div>


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
                                   value="{{ old('subject') }}">

                            <small class="form-hint">
                                The default subject used when this template is selected.
                            </small>
                        </div>

                    </div>
                </div>
            </div>
        </div>


        {{-- EDITOR AND PREVIEW --}}
        <div class="row g-3">

            {{-- HTML EDITOR --}}
            <div class="col-12 col-xl-6">

                <div class="card h-100 mb-0">

                    <div class="editor-toolbar">

                        <div class="editor-toolbar-label">
                            <i class="fas fa-code"></i>
                            HTML Email Source Code
                        </div>

                        <div class="dropdown">

                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">

                                <i class="fas fa-tags text-primary me-1"></i>
                                Insert Personalization Tag
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end">

                                <li>
                                    <a class="dropdown-item"
                                       href="javascript:void(0)"
                                       onclick="insertToken('{first_name}')">
                                        <strong>{first_name}</strong> — First Name
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item"
                                       href="javascript:void(0)"
                                       onclick="insertToken('{customer_name}')">
                                        <strong>{customer_name}</strong> — Full Name
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item"
                                       href="javascript:void(0)"
                                       onclick="insertToken('{email}')">
                                        <strong>{email}</strong> — Recipient Email
                                    </a>
                                </li>

                                <li><hr class="dropdown-divider my-1"></li>

                                <li>
                                    <a class="dropdown-item"
                                       href="javascript:void(0)"
                                       onclick="insertToken('{coupon_code}')">
                                        <strong>{coupon_code}</strong> — Promo Coupon
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item"
                                       href="javascript:void(0)"
                                       onclick="insertToken('{shop_url}')">
                                        <strong>{shop_url}</strong> — Store URL
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item"
                                       href="javascript:void(0)"
                                       onclick="insertToken('{unsubscribe_url}')">
                                        <strong>{unsubscribe_url}</strong> — Unsubscribe URL
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </div>


                    {{-- QUICK TOKENS --}}
                    <div class="token-toolbar">

                        <span class="token-label">
                            <i class="fas fa-bolt me-1"></i>
                            Quick Insert:
                        </span>

                        <button type="button"
                                class="tag-chip"
                                onclick="insertToken('{first_name}')">
                            + {first_name}
                        </button>

                        <button type="button"
                                class="tag-chip"
                                onclick="insertToken('{customer_name}')">
                            + {customer_name}
                        </button>

                        <button type="button"
                                class="tag-chip"
                                onclick="insertToken('{coupon_code}')">
                            + {coupon_code}
                        </button>

                        <button type="button"
                                class="tag-chip"
                                onclick="insertToken('{shop_url}')">
                            + {shop_url}
                        </button>

                        <button type="button"
                                class="tag-chip tag-chip-danger"
                                onclick="insertToken('{unsubscribe_url}')">
                            + {unsubscribe_url}
                        </button>

                    </div>


                    <div class="editor-card-body">

                        <textarea name="html_content"
                                  id="htmlContentTextarea"
                                  class="form-control html-code-editor"
                                  placeholder="Write or paste responsive HTML email code here..."
                                  spellcheck="false"
                                  autocapitalize="off"
                                  autocomplete="off"
                                  required>{{ old('html_content') }}</textarea>

                        <div class="editor-status">
                            <span>
                                <i class="fas fa-circle-check me-1"></i>
                                HTML Editor Ready
                            </span>

                            <span id="editorCharacterCount">0 characters</span>
                        </div>

                    </div>

                </div>
            </div>


            {{-- LIVE PREVIEW --}}
            <div class="col-12 col-xl-6">

                <div class="card h-100 mb-0">

                    <div class="section-header-wrap preview-toolbar">

                        <div class="preview-heading">

                            <div class="section-heading">
                                <span class="section-number">02</span>
                                <i class="fas fa-eye"></i>
                                Live Email Preview
                            </div>

                            <span class="preview-indicator"
                                  id="previewViewportIndicator">
                                DESKTOP · 100%
                            </span>

                        </div>


                        <div class="viewport-buttons">

                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary active"
                                    id="btn-vp-desktop"
                                    onclick="setEditorViewport('desktop')"
                                    title="Desktop Preview">

                                <i class="fas fa-desktop me-1"></i>
                                Desktop
                            </button>

                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    id="btn-vp-mobile"
                                    onclick="setEditorViewport('mobile')"
                                    title="Mobile Preview">

                                <i class="fas fa-mobile-screen me-1"></i>
                                Mobile
                            </button>

                        </div>
                    </div>


                    <div class="preview-card-body">

                        <div class="preview-canvas-wrapper">

                            <iframe id="livePreviewIframe"
                                    title="Email Template Live Preview"
                                    sandbox=""
                                    referrerpolicy="no-referrer">
                            </iframe>

                        </div>


                        <div class="preview-footer">

                            <small class="preview-note">
                                <i class="fas fa-shield-halved me-1"></i>
                                Preview uses sample data: John Doe, PROMO30 and store domain.
                            </small>

                            <button type="button"
                                    class="btn btn-link preview-refresh-btn"
                                    onclick="updateLivePreview()">

                                <i class="fas fa-sync-alt me-1"></i>
                                Refresh Preview
                            </button>

                        </div>

                    </div>
                </div>
            </div>

        </div>

    </form>
</div>


<script>
(function () {
    'use strict';

    const textarea = document.getElementById('htmlContentTextarea');
    const iframe = document.getElementById('livePreviewIframe');
    const root = document.getElementById('email-marketing-template-create-root');

    if (!textarea || !iframe || !root) return;


    /* ==========================================
       LIVE PREVIEW
    ========================================== */

    function updateCharacterCount() {
        const counter = document.getElementById('editorCharacterCount');

        if (counter) {
            counter.textContent =
                textarea.value.length.toLocaleString() + ' characters';
        }
    }


    function renderPreviewTokens(content) {

        const previewTokens = {
            '{first_name}': 'John',
            '{customer_name}': 'John Doe',
            '{email}': 'john.doe@example.com',
            '{coupon_code}': '<strong style="color:#d97706;background:#fef3c7;padding:2px 6px;border-radius:3px;">PROMO30</strong>',
            '{shop_url}': window.location.origin,
            '{store_name}': 'Shop Now BD',
            '{unsubscribe_url}': '#'
        };

        return content.replace(
            /\{first_name\}|\{customer_name\}|\{email\}|\{coupon_code\}|\{shop_url\}|\{store_name\}|\{unsubscribe_url\}/g,
            function (token) {
                return previewTokens[token] ?? token;
            }
        );
    }


    window.updateLivePreview = function () {

        let content = textarea.value;

        if (!content.trim()) {
            content = `
                <div style="
                    font-family:Arial,sans-serif;
                    text-align:center;
                    padding:60px 20px;
                    color:#94a3b8;
                ">
                    <div style="font-size:32px;margin-bottom:12px;">✉</div>
                    <h3 style="font-size:17px;color:#64748b;">
                        Email Preview
                    </h3>
                    <p style="font-size:13px;line-height:1.6;">
                        Start writing HTML code or load a starter preset
                        to see your email preview here.
                    </p>
                </div>
            `;
        } else {
            content = renderPreviewTokens(content);
        }

        iframe.srcdoc = content;
        updateCharacterCount();
    };


    /* ==========================================
       DESKTOP / MOBILE VIEWPORT
    ========================================== */

    window.setEditorViewport = function (mode) {

        const btnDesktop = document.getElementById('btn-vp-desktop');
        const btnMobile = document.getElementById('btn-vp-mobile');
        const indicator = document.getElementById('previewViewportIndicator');

        if (mode === 'mobile') {

            iframe.style.width = '375px';

            btnMobile.classList.add('active');
            btnDesktop.classList.remove('active');

            if (indicator) {
                indicator.textContent = 'MOBILE · 375PX';
            }

        } else {

            iframe.style.width = '100%';

            btnDesktop.classList.add('active');
            btnMobile.classList.remove('active');

            if (indicator) {
                indicator.textContent = 'DESKTOP · 100%';
            }
        }
    };


    /* ==========================================
       INSERT MERGE TAG
    ========================================== */

    window.insertToken = function (token) {

        const start = textarea.selectionStart ?? 0;
        const end = textarea.selectionEnd ?? 0;
        const currentValue = textarea.value;

        textarea.value =
            currentValue.substring(0, start) +
            token +
            currentValue.substring(end);

        textarea.dataset.isStarter = 'false';

        textarea.focus();

        const cursorPosition = start + token.length;

        textarea.selectionStart = cursorPosition;
        textarea.selectionEnd = cursorPosition;

        updateLivePreview();
    };


    /* ==========================================
       CATEGORY PRESET CHANGE
    ========================================== */

    window.handleCategoryPresetChange = function () {

        if (
            !textarea.value.trim() ||
            textarea.dataset.isStarter === 'true'
        ) {
            window.loadStarterTemplate();
        }
    };


    /* ==========================================
       STARTER TEMPLATE BUILDER
    ========================================== */

    window.loadStarterTemplate = function () {

        const category = document.getElementById('templateCategorySelect').value;

        const templates = {

            abandoned_cart: {
                title: 'Did you forget something?',
                heading: 'Hello {first_name},',
                message: 'You left items waiting in your shopping bag! We have saved them for you, but popular products can sell out quickly.',
                button: 'Return To My Bag →',
                buttonUrl: '{shop_url}/cart',
                buttonColor: '#f59e0b',
                buttonTextColor: '#03172e',
                label: 'Complete Your Purchase',
                footer: 'You received this email because you initiated an order on our store.'
            },

            transactional: {
                title: 'Order Confirmation',
                heading: 'Thank You for Your Order, {first_name}!',
                message: 'We have received your order and our logistics team is currently preparing your consignment.',
                button: 'Track My Order →',
                buttonUrl: '{shop_url}/orders',
                buttonColor: '#059669',
                buttonTextColor: '#ffffff',
                label: 'Order Confirmed',
                footer: 'Shop Now BD · Thank you for shopping with us.'
            },

            newsletter: {
                title: 'Latest Updates From Shop Now BD',
                heading: 'Hello {first_name},',
                message: 'Stay updated with our latest products, special collections and exclusive offers. Explore what is new at Shop Now BD.',
                button: 'Explore Our Store →',
                buttonUrl: '{shop_url}/shop',
                buttonColor: '#2563eb',
                buttonTextColor: '#ffffff',
                label: 'Latest News & Updates',
                footer: 'You are receiving this newsletter because you subscribed to our updates.'
            },

            promotional: {
                title: 'Exclusive Promo',
                heading: 'Special Discount For You, {first_name}!',
                message: 'Enjoy exclusive savings on our latest collection. Use your discount voucher below at checkout and make your shopping more affordable.',
                button: 'Shop The Collection →',
                buttonUrl: '{shop_url}/shop',
                buttonColor: '#2563eb',
                buttonTextColor: '#ffffff',
                label: 'Limited Time Offer',
                footer: 'Shop Now BD · All Rights Reserved.'
            }
        };

        const config = templates[category] || templates.promotional;

        const starterHtml = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <title>${config.title}</title>
</head>

<body style="
    margin:0;
    padding:0;
    background-color:#f4f6fa;
    font-family:Arial,Helvetica,sans-serif;
    color:#1e293b;
">

<table role="presentation"
       border="0"
       cellpadding="0"
       cellspacing="0"
       width="100%"
       style="background-color:#f4f6fa;">

    <tr>
        <td align="center" style="padding:30px 12px;">

            <table role="presentation"
                   border="0"
                   cellpadding="0"
                   cellspacing="0"
                   width="100%"
                   style="
                       max-width:600px;
                       background:#ffffff;
                       border:1px solid #e2e8f0;
                       border-radius:5px;
                       overflow:hidden;
                   ">

                <tr>
                    <td align="center"
                        style="
                            padding:28px 20px;
                            background-color:#03172e;
                        ">

                        <h2 style="
                            margin:0;
                            color:#ffffff;
                            font-size:21px;
                            font-weight:800;
                            letter-spacing:1px;
                        ">
                            SHOP NOW BD
                        </h2>

                        <p style="
                            margin:7px 0 0;
                            color:#b9c7d9;
                            font-size:11px;
                            letter-spacing:1px;
                        ">
                            YOUR TRUSTED ONLINE SHOPPING DESTINATION
                        </p>

                    </td>
                </tr>


                <tr>
                    <td style="padding:35px 28px;">

                        <div style="
                            display:inline-block;
                            margin-bottom:15px;
                            padding:5px 10px;
                            color:#2563eb;
                            background:#eff6ff;
                            border-radius:5px;
                            font-size:10px;
                            font-weight:bold;
                            text-transform:uppercase;
                            letter-spacing:.5px;
                        ">
                            ${config.label}
                        </div>

                        <h2 style="
                            margin:0 0 14px;
                            color:#0f172a;
                            font-size:21px;
                            line-height:1.4;
                        ">
                            ${config.heading}
                        </h2>

                        <p style="
                            margin:0;
                            color:#526075;
                            font-size:14px;
                            line-height:1.8;
                        ">
                            ${config.message}
                        </p>


                        <div style="
                            margin:25px 0;
                            padding:20px;
                            text-align:center;
                            background:#f8fafc;
                            border:1px solid #e8edf4;
                            border-radius:5px;
                        ">

                            ${
                                category === 'promotional'
                                ? `
                                    <p style="
                                        margin:0 0 9px;
                                        color:#64748b;
                                        font-size:11px;
                                        font-weight:bold;
                                        text-transform:uppercase;
                                    ">
                                        Your Promo Coupon
                                    </p>

                                    <div style="
                                        display:inline-block;
                                        padding:10px 18px;
                                        color:#b45309;
                                        background:#fffbeb;
                                        border:1px dashed #f59e0b;
                                        border-radius:5px;
                                        font-size:20px;
                                        font-weight:bold;
                                        letter-spacing:1px;
                                    ">
                                        {coupon_code}
                                    </div>
                                `
                                : `
                                    <p style="
                                        margin:0;
                                        color:#526075;
                                        font-size:13px;
                                        line-height:1.7;
                                    ">
                                        Visit your Shop Now BD account to explore
                                        more information and manage your shopping.
                                    </p>
                                `
                            }

                        </div>


                        <div style="text-align:center;margin-top:25px;">

                            <a href="${config.buttonUrl}"
                               style="
                                   display:inline-block;
                                   padding:13px 27px;
                                   color:${config.buttonTextColor};
                                   background-color:${config.buttonColor};
                                   border-radius:5px;
                                   text-decoration:none;
                                   font-size:13px;
                                   font-weight:bold;
                               ">
                                ${config.button}
                            </a>

                        </div>


                        <p style="
                            margin:28px 0 0;
                            color:#8792a5;
                            font-size:12px;
                            line-height:1.7;
                        ">
                            Need assistance? Reply directly to this email
                            or contact our customer support team.
                        </p>

                    </td>
                </tr>


                <tr>
                    <td align="center"
                        style="
                            padding:22px 25px;
                            background:#f8fafc;
                            border-top:1px solid #e8edf4;
                        ">

                        <p style="
                            margin:0 0 10px;
                            color:#8490a3;
                            font-size:11px;
                            line-height:1.6;
                        ">
                            ${config.footer}
                        </p>

                        <p style="
                            margin:0;
                            color:#94a3b8;
                            font-size:10px;
                        ">
                            © ${new Date().getFullYear()} Shop Now BD. All rights reserved.
                        </p>

                        <p style="margin:12px 0 0;">
                            <a href="{unsubscribe_url}"
                               style="
                                   color:#64748b;
                                   font-size:11px;
                                   text-decoration:underline;
                               ">
                                Unsubscribe from email updates
                            </a>
                        </p>

                    </td>
                </tr>

            </table>

        </td>
    </tr>

</table>

</body>
</html>`;

        textarea.value = starterHtml;
        textarea.dataset.isStarter = 'true';

        updateLivePreview();
    };


    /* ==========================================
       INITIALIZE
    ========================================== */

    textarea.addEventListener('input', function () {

        textarea.dataset.isStarter = 'false';

        updateLivePreview();
    });


    updateCharacterCount();

    if (!textarea.value.trim()) {
        window.loadStarterTemplate();
    } else {
        updateLivePreview();
    }

})();
</script>

@endsection
