
@extends('layouts.admin')

@section('title', 'Create Form - ' . config('app.name'))

@section('admin_content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* =====================================================
       CREATE FORM — KAIADMIN CLASSIC / WHITE
    ===================================================== */

    #formCreateRoot {
        --form-primary: var(--bs-primary, #1572e8);
        --form-success: var(--bs-success, #31ce36);
        --form-warning: var(--bs-warning, #ffad46);
        --form-danger: var(--bs-danger, #f25961);
        --form-info: var(--bs-info, #48abf7);
        --form-text: #2a2f5b;
        --form-muted: #8d9498;
        --form-border: #ebedf2;
        --form-bg: #f8f9fa;
        --form-card: #ffffff;

        width: 100%;
        min-width: 0;
        color: var(--form-text);
        font-family: 'Inter', sans-serif;
        font-size: 13px;
        letter-spacing: 0;
    }

    #formCreateRoot *,
    #formCreateRoot *::before,
    #formCreateRoot *::after {
        box-sizing: border-box;
    }

    #formCreateRoot i,
    .fb-create-modal i {
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome" !important;
        font-style: normal;
        font-weight: 900;
        line-height: 1;
        letter-spacing: normal;
        display: inline-block;
    }

    #formCreateRoot .far,
    #formCreateRoot .fa-regular {
        font-weight: 400 !important;
    }

    #formCreateRoot .btn,
    #formCreateRoot button,
    #formCreateRoot .badge,
    #formCreateRoot .alert,
    #formCreateRoot .card,
    #formCreateRoot .form-control,
    #formCreateRoot .form-select,
    #formCreateRoot .metric-card,
    #formCreateRoot .builder-title-card,
    #formCreateRoot .builder-section-card,
    #formCreateRoot .proof-media-surface,
    #formCreateRoot .proof-thumb-box,
    #formCreateRoot .color-hex-tag,
    .fb-create-modal .modal-content,
    .fb-create-modal .btn {
        border-radius: 5px !important;
    }

    #formCreateRoot .bn-digit,
    #formCreateRoot .font-tabular {
        font-variant-numeric: tabular-nums;
    }

    #formCreateRoot .form-create-wrap {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        min-width: 0;
    }

    /* PAGE HEADER */

    #formCreateRoot .builder-title-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 16px 20px;
        margin-bottom: 16px;
        background: var(--form-card);
        border: 1px solid var(--form-border);
        box-shadow: 0 1px 2px rgba(0, 0, 0, .025);
    }

    #formCreateRoot .builder-title-card h5 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: clamp(16px, 1.5vw, 19px) !important;
        font-weight: 700;
        color: var(--form-text);
        letter-spacing: -.3px;
    }

    #formCreateRoot .builder-title-card small {
        font-size: 12px !important;
        color: var(--form-muted);
    }

    /* BUTTONS */

    #formCreateRoot .btn-compact {
        min-height: 34px;
        padding: 7px 12px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        font-family: 'Inter', sans-serif;
        font-size: 12px !important;
        font-weight: 600 !important;
        line-height: 1.3 !important;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #formCreateRoot .btn-primary {
        background-color: var(--form-primary) !important;
        border-color: var(--form-primary) !important;
        color: #fff !important;
    }

    #formCreateRoot .btn-primary:hover {
        filter: brightness(.93);
    }

    #formCreateRoot .btn-outline-secondary {
        background: #fff !important;
        border: 1px solid #dce0e5 !important;
        color: #6c757d !important;
    }

    #formCreateRoot .btn-outline-secondary:hover {
        background: #f5f6f8 !important;
        border-color: #c8cdd3 !important;
        color: var(--form-text) !important;
    }

    #formCreateRoot .btn-outline-danger {
        font-size: 11px !important;
    }

    #formCreateRoot .btn:focus-visible,
    #formCreateRoot button:focus-visible {
        outline: 2px solid var(--form-primary);
        outline-offset: 2px;
    }

    /* OVERVIEW CARDS */

    #formCreateRoot .metric-card {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        min-height: 88px;
        padding: 13px 15px;
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--form-border);
        border-left: 3px solid var(--form-primary) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .025);
        transition: box-shadow .18s ease, transform .18s ease;
    }

    #formCreateRoot .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, .055);
    }

    #formCreateRoot .metric-card-blue {
        border-left-color: var(--form-primary) !important;
    }

    #formCreateRoot .metric-card-green {
        border-left-color: var(--form-success) !important;
    }

    #formCreateRoot .metric-card-purple {
        border-left-color: #6861ce !important;
    }

    #formCreateRoot .metric-card-amber {
        border-left-color: var(--form-warning) !important;
    }

    #formCreateRoot .stat-label-title {
        font-size: 11px;
        font-weight: 600;
        line-height: 1.4;
        color: var(--form-muted);
    }

    #formCreateRoot .stat-val-num {
        margin: 0;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 15px;
        font-weight: 700;
        line-height: 1.4;
        color: var(--form-text);
        overflow-wrap: anywhere;
    }

    #formCreateRoot .stat-sub-text {
        margin-top: 3px;
        font-size: 11px;
        color: var(--form-muted);
    }

    #formCreateRoot .metric-icon-square {
        width: 33px;
        height: 33px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px !important;
        font-size: 13px;
    }

    /* MAIN SECTION CARDS */

    #formCreateRoot .builder-section-card {
        margin-bottom: 16px;
        overflow: hidden;
        background: var(--form-card);
        border: 1px solid var(--form-border);
        box-shadow: 0 1px 3px rgba(0, 0, 0, .025);
    }

    #formCreateRoot .section-header-wrap {
        min-height: 48px;
        padding: 11px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        background: #fff;
        border-bottom: 1px solid var(--form-border);
    }

    #formCreateRoot .section-header-wrap > span {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px !important;
        font-weight: 700;
        color: var(--form-text);
    }

    #formCreateRoot .builder-section-card > .p-3 {
        padding: 18px !important;
    }

    /* SUB SECTIONS */

    #formCreateRoot .sub-group-header {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 0;
        margin: 3px 0 12px;
        border-bottom: 1px solid var(--form-border);
        color: var(--form-text);
        font-size: 12px;
        font-weight: 700;
    }

    #formCreateRoot .sub-group-header i {
        font-size: 12px;
    }

    /* FORM CONTROLS */

    #formCreateRoot .form-label {
        display: block;
        margin-bottom: 6px;
        color: #495057;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.4;
    }

    #formCreateRoot .form-control,
    #formCreateRoot .form-select {
        width: 100%;
        min-height: 37px;
        padding: 8px 11px;
        border: 1px solid #e0e3e8;
        background-color: #fff;
        color: #495057;
        box-shadow: none;
        font-family: 'Inter', sans-serif;
        font-size: 12px;
        font-weight: 500;
        line-height: 1.4;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    #formCreateRoot .form-control::placeholder {
        color: #adb5bd;
        opacity: 1;
        font-weight: 400;
    }

    #formCreateRoot .form-control:hover,
    #formCreateRoot .form-select:hover {
        border-color: #c8cdd3;
    }

    #formCreateRoot .form-control:focus,
    #formCreateRoot .form-select:focus {
        border-color: var(--form-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
        outline: none;
    }

    #formCreateRoot textarea.form-control {
        min-height: 76px;
        resize: vertical;
        line-height: 1.55;
    }

    #formCreateRoot .form-check {
        min-height: 20px;
    }

    #formCreateRoot .form-check-input {
        width: 16px;
        height: 16px;
        margin-top: .15em;
        border: 1px solid #ced4da;
        border-radius: 4px !important;
        cursor: pointer;
        box-shadow: none;
    }

    #formCreateRoot .form-check-input:checked {
        background-color: var(--form-primary);
        border-color: var(--form-primary);
    }

    #formCreateRoot .form-check-input:focus {
        border-color: var(--form-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
    }

    #formCreateRoot .form-switch .form-check-input {
        width: 30px;
        height: 16px;
        border-radius: 20px !important;
    }

    #formCreateRoot .form-check-label {
        color: #6c757d;
        font-size: 11.5px;
        font-weight: 500;
        cursor: pointer;
        line-height: 1.5;
    }

    #formCreateRoot .form-switch .form-check-label {
        padding-left: 3px;
    }

    /* MEDIA LIBRARY */

    #formCreateRoot .proof-media-surface {
        padding: 14px;
        background: #fafbfc;
        border: 1px dashed #d5dbe2;
        transition: border-color .18s ease, background .18s ease;
    }

    #formCreateRoot .proof-media-surface:hover {
        border-color: var(--form-primary);
        background: #f7faff;
    }

    #formCreateRoot .proof-thumb-box {
        position: relative;
        width: 42px;
        height: 42px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--form-border);
        cursor: pointer;
    }

    #formCreateRoot .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #formCreateRoot .proof-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, .45);
        opacity: 0;
        transition: opacity .15s ease;
    }

    #formCreateRoot .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    #formCreateRoot .proof-media-surface .small {
        font-size: 11px !important;
    }

    /* COLOR PICKERS */

    #formCreateRoot .color-picker-input {
        width: 44px;
        min-width: 44px;
        height: 37px !important;
        padding: 3px !important;
        border: 1px solid #e0e3e8;
        background: #fff;
        cursor: pointer;
    }

    #formCreateRoot .color-hex-tag {
        min-width: 0;
        padding: 8px 7px;
        background: #f8f9fa;
        border: 1px solid var(--form-border);
        color: #6c757d;
        font-family: 'Inter', sans-serif;
        font-size: 11px;
        font-weight: 600;
        text-align: center;
        user-select: none;
        overflow-wrap: anywhere;
    }

    /* CHECKBOX GROUP */

    #formCreateRoot .form-check-group {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 14px 22px;
        padding: 12px !important;
        background: #f8f9fa !important;
        border: 1px solid var(--form-border) !important;
    }

    #formCreateRoot .form-check-group .form-check {
        display: flex;
        align-items: center;
        gap: 5px;
        margin: 0;
    }

    #formCreateRoot .form-check-group .form-check-label {
        color: #495057;
        font-weight: 600;
    }

    /* VALIDATION */

    #formCreateRoot .text-danger.small {
        font-size: 11px !important;
        font-weight: 500;
    }

    /* BOTTOM ACTIONS */

    #formCreateRoot .bottom-action-bar {
        gap: 12px;
    }

    #formCreateRoot .bottom-action-bar .btn {
        min-height: 37px;
    }

    /* MODAL */

    .fb-create-modal .modal-content {
        overflow: hidden;
        border: 1px solid #ebedf2 !important;
        box-shadow: 0 10px 35px rgba(0, 0, 0, .12) !important;
    }

    .fb-create-modal .modal-header {
        padding: 13px 16px;
    }

    .fb-create-modal .modal-body {
        padding: 12px !important;
    }

    .fb-create-modal .modal-body img {
        max-width: 100%;
        max-height: min(480px, 65vh);
        object-fit: contain;
    }

    /* RESPONSIVE */

    @media (min-width: 1400px) {
        #formCreateRoot .form-create-wrap {
            max-width: 1280px;
        }
    }

    @media (max-width: 991.98px) {
        #formCreateRoot .builder-section-card > .p-3 {
            padding: 15px !important;
        }

        #formCreateRoot .section-header-wrap {
            padding: 11px 14px;
        }
    }

    @media (max-width: 767.98px) {
        #formCreateRoot .builder-title-card {
            padding: 13px 14px;
        }

        #formCreateRoot .top-action-btns {
            width: 100%;
        }

        #formCreateRoot .top-action-btns .btn {
            width: 100%;
        }

        #formCreateRoot .metric-card {
            min-height: 80px;
            padding: 10px 11px;
        }

        #formCreateRoot .stat-val-num {
            font-size: 13px;
        }

        #formCreateRoot .stat-label-title {
            font-size: 10px;
        }

        #formCreateRoot .metric-icon-square {
            width: 29px;
            height: 29px;
            font-size: 12px;
        }

        #formCreateRoot .form-check-group {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        #formCreateRoot .section-header-wrap {
            align-items: flex-start;
        }

        #formCreateRoot .section-header-wrap .form-switch {
            max-width: 100%;
        }
    }

    @media (max-width: 575.98px) {
        #formCreateRoot {
            font-size: 12px;
        }

        #formCreateRoot .container-fluid {
            padding-right: 10px !important;
            padding-left: 10px !important;
        }

        #formCreateRoot .builder-title-card {
            padding: 12px;
            gap: 10px;
        }

        #formCreateRoot .builder-title-card h5 {
            font-size: 16px !important;
        }

        #formCreateRoot .builder-title-card small {
            font-size: 11px !important;
        }

        #formCreateRoot .builder-section-card > .p-3 {
            padding: 12px !important;
        }

        #formCreateRoot .section-header-wrap {
            padding: 11px 12px;
        }

        #formCreateRoot .section-header-wrap > span {
            font-size: 12px !important;
        }

        #formCreateRoot .form-label {
            font-size: 11.5px;
        }

        #formCreateRoot .form-control,
        #formCreateRoot .form-select {
            min-height: 37px;
            font-size: 12px;
        }

        #formCreateRoot .sub-group-header {
            font-size: 11.5px;
        }

        #formCreateRoot .bottom-action-bar {
            flex-direction: column !important;
            align-items: stretch !important;
            width: 100%;
        }

        #formCreateRoot .bottom-action-bar .btn {
            width: 100% !important;
            min-height: 40px;
            justify-content: center !important;
        }

        #formCreateRoot .proof-media-surface {
            padding: 10px;
        }

        #formCreateRoot .form-check-group {
            gap: 11px 8px;
        }

        #formCreateRoot .form-check-group .form-check-label {
            font-size: 10.5px;
        }

        #formCreateRoot .color-hex-tag {
            font-size: 10px;
        }

        .fb-create-modal .modal-dialog {
            margin: 12px;
        }
    }

    @media (max-width: 359.98px) {
        #formCreateRoot .form-check-group {
            grid-template-columns: 1fr;
        }

        #formCreateRoot .metric-icon-square {
            width: 25px;
            height: 25px;
        }

        #formCreateRoot .stat-val-num {
            font-size: 12px;
        }
    }

    /* ACCESSIBILITY */

    @media (prefers-reduced-motion: reduce) {
        #formCreateRoot *,
        .fb-create-modal * {
            transition-duration: .01ms !important;
            animation-duration: .01ms !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="formCreateRoot">
    <div class="form-create-wrap">

        <!-- PAGE HEADER -->
        <div class="builder-title-card d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-file-circle-plus text-primary"></i>
                    <span>Create Form</span>
                </h5>
                <small class="text-muted d-block mt-1">
                    Form details, design & notifications
                </small>
            </div>
            <div class="top-action-btns">
                <a href="{{ route('admin.forms.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs bg-white fw-semibold">
                    <i class="fa-solid fa-arrow-left me-1 text-primary"></i>
                    <span>Back to Forms</span>
                </a>
            </div>
        </div>

        <!-- OVERVIEW -->
        <div class="row g-2 mb-3">
            <div class="col-6 col-xl-3">
                <div class="card metric-card metric-card-blue h-100 mb-0">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted stat-label-title text-truncate">Form Type</span>
                        <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <h4 class="fw-bold my-0 text-dark stat-val-num">Custom Form</h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="card metric-card metric-card-green h-100 mb-0">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted stat-label-title text-truncate">Default Layout</span>
                        <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                            <i class="fa-solid fa-table-columns"></i>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <h4 class="fw-bold my-0 text-success stat-val-num">Inline Flow</h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="card metric-card metric-card-purple h-100 mb-0">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted stat-label-title text-truncate">Notifications</span>
                        <div class="metric-icon-square bg-purple-subtle text-primary shadow-xs" style="background-color: #f3e8ff; color: #7e22ce;">
                            <i class="fa-solid fa-envelope-circle-check"></i>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <h4 class="fw-bold my-0 text-dark stat-val-num">Admin Email</h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="card metric-card metric-card-amber h-100 mb-0">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted stat-label-title text-truncate">Storage</span>
                        <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                            <i class="fa-solid fa-database"></i>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <h4 class="fw-bold my-0 text-warning-emphasis stat-val-num">Database Log</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- FORM -->
        <form method="POST" action="{{ route('admin.forms.store') }}" id="formCreateEntityForm">
            @csrf
            <input type="hidden" name="logo" id="create_form_logo_media" value="{{ old('logo') }}">

            <!-- 1. FORM IDENTITY -->
            <div class="builder-section-card">
                <div class="section-header-wrap">
                    <span class="fw-bold text-dark small">
                        <i class="fa-solid fa-id-card text-primary me-1"></i>
                        1. Form Details
                    </span>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-2">
                                <label class="form-label mb-0">Form Name *</label>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="show_title" id="chk_show_title" value="1" {{ old('show_title', true) ? 'checked' : '' }}>
                                    <label class="form-check-label small text-muted" for="chk_show_title">Show Title</label>
                                </div>
                            </div>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Contact Us" required>
                            @error('name')
                                <div class="text-danger small mt-1" style="font-size: 11px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Form Slug</label>
                            <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" placeholder="contact-us">
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-2">
                                <label class="form-label mb-0">Description</label>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="show_description" id="chk_show_desc" value="1" {{ old('show_description', true) ? 'checked' : '' }}>
                                    <label class="form-check-label small text-muted" for="chk_show_desc">Show Description</label>
                                </div>
                            </div>
                            <textarea name="description" class="form-control" rows="2" placeholder="Enter a short form description...">{{ old('description') }}</textarea>
                        </div>

                        <!-- MEDIA -->
                        <div class="col-12">
                            <label class="form-label d-flex justify-content-between align-items-center">
                                <span><i class="fa-solid fa-paperclip text-primary me-1"></i> Header Image</span>
                                <span class="badge bg-light text-muted border" style="font-size: 9.5px; font-weight: 600;">MEDIA LIBRARY</span>
                            </label>
                            <div class="proof-media-surface">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" onclick="chooseFormLogoMedia()">
                                        <i class="fa-solid fa-photo-film me-1"></i>
                                        <span>Choose Image</span>
                                    </button>
                                    <span class="small text-muted" style="font-size: 11px;">Upload a logo or image</span>
                                </div>

                                <div id="form_logo_preview_wrap" class="mt-2 {{ old('logo') ? 'd-flex' : 'd-none' }} align-items-center gap-2 bg-white border p-2" style="border-radius: 5px;">
                                    <div class="proof-thumb-box shadow-xs" onclick="zoomFormLogoPreview()" title="Preview Image">
                                        <img id="form_logo_preview_img" src="{{ old('logo') }}" alt="Logo Preview">
                                        <div class="proof-thumb-hover-overlay">
                                            <i class="fas fa-search-plus text-white" style="font-size: 9px;"></i>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-grow-1">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 9.5px; font-weight: 600;">
                                            <i class="fas fa-check-circle me-1"></i>Attached
                                        </span>
                                        <small class="text-muted d-block text-truncate" id="form_logo_preview_txt" style="font-size: 10px;">{{ old('logo') }}</small>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger btn-compact py-0 px-2" style="font-size: 10px !important;" onclick="clearFormLogoMedia()">
                                        <i class="fa-solid fa-trash-can me-1"></i>Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. FORM DESIGN -->
            <div class="builder-section-card">
                <div class="section-header-wrap">
                    <span class="fw-bold text-dark small">
                        <i class="fa-solid fa-palette text-warning me-1"></i>
                        2. Layout & Styling
                    </span>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="styles[show_labels]" id="chk_show_labels" value="1" checked>
                        <label class="form-check-label fw-bold small text-dark" for="chk_show_labels" style="font-size: 11px;">Show Labels</label>
                    </div>
                </div>
                <div class="p-3">

                    <!-- LAYOUT SETTINGS -->
                    <div class="sub-group-header">
                        <i class="fa-solid fa-sliders text-primary"></i>
                        <span>Layout & Buttons</span>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label text-primary">Field Layout *</label>
                            <select name="styles[field_flow]" class="form-select fw-semibold">
                                <option value="inline" selected>Side by Side</option>
                                <option value="stacked">Stacked</option>
                                <option value="single_line">Single Line</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Button Alignment</label>
                            <select name="styles[button_align]" class="form-select">
                                <option value="left" selected>Left</option>
                                <option value="center">Center</option>
                                <option value="right">Right</option>
                                <option value="full">Full Width</option>
                                <option value="inline_last">Beside Field</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Button Size</label>
                            <select name="styles[button_size]" class="form-select">
                                <option value="sm">Small</option>
                                <option value="md" selected>Medium</option>
                                <option value="lg">Large</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Button Text</label>
                            <input type="text" name="styles[button_text]" class="form-control" value="Submit">
                        </div>

                        <div class="col-12 col-md-6 mt-1">
                            <label class="form-label">Custom HTML ID</label>
                            <input type="text" name="styles[custom_id]" class="form-control" placeholder="newsletter-popup-form">
                        </div>

                        <div class="col-12 col-md-6 mt-1">
                            <label class="form-label">Custom CSS Class</label>
                            <input type="text" name="styles[custom_class]" class="form-control" placeholder="custom-lead-form shadow-sm">
                        </div>
                    </div>

                    <!-- CONTAINER SETTINGS -->
                    <div class="sub-group-header">
                        <i class="fa-solid fa-box text-success"></i>
                        <span>Container & Spacing</span>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label">Background</label>
                            <div class="d-flex gap-1 align-items-center">
                                <input type="color" name="styles[form_bg]" class="form-control color-picker-input" value="#ffffff" style="width: 44px;">
                                <div class="color-hex-tag flex-grow-1">#ffffff</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Border Color</label>
                            <div class="d-flex gap-1 align-items-center">
                                <input type="color" name="styles[border_color]" class="form-control color-picker-input" value="#e2e8f0" style="width: 44px;">
                                <div class="color-hex-tag flex-grow-1">#e2e8f0</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Border Radius (px)</label>
                            <input type="number" name="styles[border_radius]" class="form-control bn-digit font-tabular" value="8" min="0" max="60">
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Padding (px)</label>
                            <input type="number" name="styles[form_padding]" class="form-control bn-digit font-tabular" value="24" min="0" max="60">
                        </div>
                    </div>

                    <!-- FIELD SETTINGS -->
                    <div class="sub-group-header">
                        <i class="fa-solid fa-i-cursor text-info"></i>
                        <span>Fields & Borders</span>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label">Label Color</label>
                            <div class="d-flex gap-1 align-items-center">
                                <input type="color" name="styles[label_color]" class="form-control color-picker-input" value="#1e293b" style="width: 44px;">
                                <div class="color-hex-tag flex-grow-1">#1e293b</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Input Background</label>
                            <div class="d-flex gap-1 align-items-center">
                                <input type="color" name="styles[input_bg]" class="form-control color-picker-input" value="#ffffff" style="width: 44px;">
                                <div class="color-hex-tag flex-grow-1">#ffffff</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Input Text Color</label>
                            <div class="d-flex gap-1 align-items-center">
                                <input type="color" name="styles[input_text]" class="form-control color-picker-input" value="#0f172a" style="width: 44px;">
                                <div class="color-hex-tag flex-grow-1">#0f172a</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Input Border</label>
                            <div class="d-flex gap-1 align-items-center">
                                <input type="color" name="styles[input_border]" class="form-control color-picker-input" value="#cbd5e1" style="width: 44px;">
                                <div class="color-hex-tag flex-grow-1">#cbd5e1</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Input Radius (px)</label>
                            <input type="number" name="styles[input_radius]" class="form-control bn-digit font-tabular" value="6" min="0" max="30">
                        </div>
                    </div>

                    <!-- BUTTON COLORS -->
                    <div class="sub-group-header">
                        <i class="fa-solid fa-square-check text-danger"></i>
                        <span>Button Colors</span>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label">Background</label>
                            <div class="d-flex gap-1 align-items-center">
                                <input type="color" name="styles[btn_bg]" class="form-control color-picker-input" value="#2563eb" style="width: 44px;">
                                <div class="color-hex-tag flex-grow-1">#2563eb</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Text Color</label>
                            <div class="d-flex gap-1 align-items-center">
                                <input type="color" name="styles[btn_text]" class="form-control color-picker-input" value="#ffffff" style="width: 44px;">
                                <div class="color-hex-tag flex-grow-1">#ffffff</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Hover Background</label>
                            <div class="d-flex gap-1 align-items-center">
                                <input type="color" name="styles[btn_hover_bg]" class="form-control color-picker-input" value="#1d4ed8" style="width: 44px;">
                                <div class="color-hex-tag flex-grow-1">#1d4ed8</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Hover Text</label>
                            <div class="d-flex gap-1 align-items-center">
                                <input type="color" name="styles[btn_hover_text]" class="form-control color-picker-input" value="#ffffff" style="width: 44px;">
                                <div class="color-hex-tag flex-grow-1">#ffffff</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Button Radius (px)</label>
                            <input type="number" name="styles[btn_radius]" class="form-control bn-digit font-tabular" value="6" min="0" max="40">
                        </div>
                    </div>

                    <!-- CUSTOM CSS -->
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label">Custom CSS</label>
                            <textarea name="custom_css" class="form-control" rows="3" placeholder="Write or paste custom CSS...">{{ old('custom_css') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. NOTIFICATIONS -->
            <div class="builder-section-card">
                <div class="section-header-wrap">
                    <span class="fw-bold text-dark small">
                        <i class="fa-solid fa-envelope-open-text text-success me-1"></i>
                        3. Notifications & Workflow
                    </span>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Admin Email</label>
                            <input type="email" name="admin_email" class="form-control" value="{{ old('admin_email', 'example@gmail.com') }}" placeholder="admin@example.com">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Email Subject</label>
                            <input type="text" name="email_subject" class="form-control" value="{{ old('email_subject', 'New Form Submission') }}" placeholder="New Contact Form Submission">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Success Message</label>
                            <input type="text" name="success_message" class="form-control" value="{{ old('success_message', 'Your message has been sent successfully.') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Redirect URL</label>
                            <input type="text" name="redirect_url" class="form-control" value="{{ old('redirect_url') }}" placeholder="/thank-you">
                        </div>

                        <!-- BEHAVIOR SETTINGS -->
                        <div class="col-12 mt-2">
                            <label class="form-label mb-2">Status & Email Settings</label>
                            <div class="d-flex flex-wrap gap-3 form-check-group p-2.5 border bg-light" style="border-radius: 6px;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="chk_active" value="1" checked>
                                    <label class="form-check-label fw-semibold" for="chk_active">Active</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="save_submissions" id="chk_save_subs" value="1" checked>
                                    <label class="form-check-label fw-semibold" for="chk_save_subs">Save Submissions</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="send_admin_email" id="chk_send_admin" value="1" checked>
                                    <label class="form-check-label fw-semibold" for="chk_send_admin">Admin Email</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="send_user_email" id="chk_send_user" value="1">
                                    <label class="form-check-label fw-semibold" for="chk_send_user">User Email</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACTIONS -->
            <div class="builder-section-card p-3 mb-4">
                <div class="d-flex justify-content-between align-items-center bottom-action-bar">
                    <a href="{{ route('admin.forms.index') }}" class="btn btn-outline-secondary btn-compact text-dark order-2 order-sm-1">
                        <i class="fa-solid fa-arrow-left me-1"></i>
                        <span>Cancel</span>
                    </a>
                    <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-4 order-1 order-sm-2" style="min-height: 36px;">
                        <i class="fa-solid fa-check-circle me-1"></i>
                        <span>Create Form</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- LOGO PREVIEW MODAL -->
<div class="modal fade fb-create-modal" id="formLogoZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2 bg-white" style="border-radius: 8px !important;">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12px;" id="formLogoZoomTitle">
                    <i class="fa-solid fa-image text-primary me-1"></i> Header Preview
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="formLogoZoomImgTag" src="" alt="Logo Preview" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<script>
    function extractMediaItem(mediaInput) {
        if (!mediaInput) return null;
        const item = Array.isArray(mediaInput) ? mediaInput[0] : mediaInput;
        if (!item || typeof item !== 'object') return null;
        const rawPath = item.file_path || item.path || item.url || '';
        const rawUrl  = item.url || item.file_path || item.path || '';
        if (!rawPath || rawPath === 'undefined') return null;
        return { path: String(rawPath).trim(), url: String(rawUrl).trim() };
    }

    function chooseFormLogoMedia() {
        if (typeof window.openMediaPicker === 'function') {
            window.openMediaPicker({
                multiple: false,
                onSelect: function(media) {
                    const picked = extractMediaItem(media);
                    if (picked && picked.path) {
                        document.getElementById('create_form_logo_media').value = picked.path;
                        const wrap = document.getElementById('form_logo_preview_wrap');
                        const img  = document.getElementById('form_logo_preview_img');
                        const txt  = document.getElementById('form_logo_preview_txt');
                        if (img) img.src = picked.url;
                        if (txt) txt.innerText = picked.path.split('/').pop() || picked.path;
                        if (wrap) {
                            wrap.classList.remove('d-none');
                            wrap.classList.add('d-flex');
                        }
                    }
                }
            });
        }
    }

    function clearFormLogoMedia() {
        document.getElementById('create_form_logo_media').value = '';
        const wrap = document.getElementById('form_logo_preview_wrap');
        if (wrap) {
            wrap.classList.add('d-none');
            wrap.classList.remove('d-flex');
        }
    }

    function zoomFormLogoPreview() {
        const img = document.getElementById('form_logo_preview_img');
        const zoomImgTag = document.getElementById('formLogoZoomImgTag');
        if (img && img.src && zoomImgTag) {
            zoomImgTag.src = img.src;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('formLogoZoomModal')).show();
        }
    }

    // Color Picker Sync
    document.querySelectorAll('.color-picker-input').forEach(input => {
        input.addEventListener('input', function() {
            const hexTag = this.parentElement.querySelector('.color-hex-tag');
            if (hexTag) hexTag.innerText = this.value;
        });
    });
</script>
@endsection
