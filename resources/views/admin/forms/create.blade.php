@extends('layouts.admin')

@section('title', 'Create Form - ' . config('app.name'))

@section('admin_content')
<!-- Font Awesome 6.5.1 CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Google Fonts: Plus Jakarta Sans & Inter -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* Professional Enterprise Typography */
    #formCreateRoot,
    .fb-create-modal {
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        letter-spacing: -0.01em;
        color: #1e293b;
    }

    /* FontAwesome Protection */
    #formCreateRoot i,
    #formCreateRoot .fa,
    #formCreateRoot .fas,
    #formCreateRoot .far,
    #formCreateRoot .fab,
    #formCreateRoot .fa-solid,
    #formCreateRoot .fa-regular,
    .fb-create-modal i,
    .fb-create-modal .fa-solid {
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome" !important;
        font-style: normal;
        font-weight: 900;
        line-height: 1;
        letter-spacing: normal !important;
        display: inline-block;
    }
    #formCreateRoot .far,
    #formCreateRoot .fa-regular {
        font-weight: 400 !important;
    }

    /* Strict 5px Element Radius Across Controls */
    #formCreateRoot .btn,
    #formCreateRoot .btn-sm,
    #formCreateRoot button,
    #formCreateRoot .form-control,
    #formCreateRoot .form-select,
    #formCreateRoot .badge,
    #formCreateRoot .alert,
    #formCreateRoot .card,
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

    /* Clean Tabular Numbers */
    .bn-digit,
    .font-tabular {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
    }

    .form-create-wrap {
        max-width: 1040px;
        margin: auto;
    }

    /* Header Banner Card */
    .builder-title-card {
        background: #ffffff;
        color: #0f172a;
        padding: 14px 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }

    .btn-compact {
        font-size: 11px !important;
        padding: 6px 14px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    .btn-primary {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
    }
    .btn-primary:hover {
        background-color: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
        color: #ffffff !important;
    }

    .btn-outline-secondary {
        background-color: #ffffff !important;
        border-color: #cbd5e1 !important;
        color: #334155 !important;
    }
    .btn-outline-secondary:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }

    /* Metric Cards Architecture */
    .metric-card {
        padding: 12px 14px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }
    .stat-label-title {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.02em;
        line-height: 1.2;
    }
    .stat-val-num {
        font-size: 16px;
        font-weight: 800;
        line-height: 1.25;
        letter-spacing: -0.01em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .stat-sub-text {
        font-size: 10px;
        margin-top: 2px;
        color: #64748b;
    }
    .metric-icon-square {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
        border-radius: 5px !important;
    }

    .metric-card-blue   { background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%); border-left: 3.5px solid #2563eb !important; }
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-left: 3.5px solid #10b981 !important; }
    .metric-card-purple { background: linear-gradient(180deg, #ffffff 0%, #faf5ff 100%); border-left: 3.5px solid #a855f7 !important; }
    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-left: 3.5px solid #f59e0b !important; }

    /* Builder Section Cards */
    .builder-section-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
        overflow: hidden;
    }

    .section-header-wrap {
        padding: 12px 16px;
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .sub-group-header {
        background: transparent;
        padding: 6px 0;
        font-size: 11.5px;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 6px;
        margin-bottom: 8px;
        border-bottom: 1px dashed #e2e8f0;
    }

    .form-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }

    #formCreateRoot .form-control,
    #formCreateRoot .form-select {
        min-height: 34px;
        font-size: 12px;
        font-weight: 500;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #formCreateRoot .form-control:focus,
    #formCreateRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    textarea.form-control {
        min-height: 72px;
        line-height: 1.45;
    }

    .form-check-input {
        width: 16px;
        height: 16px;
        cursor: pointer;
        border-radius: 4px !important;
    }
    .form-check-input:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }
    .form-check-label {
        font-size: 11.5px;
        font-weight: 500;
        color: #334155;
        cursor: pointer;
    }

    /* Media Uploader Surface */
    .proof-media-surface {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 6px !important;
        padding: 12px;
        transition: all 0.2s ease;
    }
    .proof-media-surface:hover {
        border-color: #2563eb;
        background: #eff6ff;
    }
    .proof-thumb-box {
        width: 36px;
        height: 36px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        position: relative;
    }
    .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .proof-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.15s ease;
    }
    .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    .color-picker-input {
        padding: 2px 4px !important;
        height: 34px !important;
        cursor: pointer;
        border-radius: 5px !important;
    }
    .color-hex-tag {
        font-size: 11px;
        font-weight: 600;
        background-color: #f8fafc;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        color: #475569;
        text-align: center;
        user-select: none;
    }

    /* Responsive Mobile Tweaks */
    @media (max-width: 767.98px) {
        .builder-title-card {
            padding: 12px 14px !important;
        }
        .form-check-group {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .metric-card {
            padding: 10px !important;
            min-height: 80px !important;
        }
        .top-action-btns {
            width: 100%;
        }
        .top-action-btns .btn {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 575.98px) {
        .bottom-action-bar {
            flex-direction: column !important;
            width: 100%;
            gap: 8px !important;
        }
        .bottom-action-bar .btn {
            width: 100% !important;
            justify-content: center !important;
            padding: 9px 12px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="formCreateRoot">
    <div class="form-create-wrap">

        <!-- 1. Header Banner Card -->
        <div class="builder-title-card d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fa-solid fa-file-circle-plus text-primary"></i>
                    <span>Create New Form</span>
                </h5>
                <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                   Form ID, Display, Styling & Alerts
                </small>
            </div>
            <div class="top-action-btns">
                <a href="{{ route('admin.forms.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs bg-white fw-semibold">
                    <i class="fa-solid fa-arrow-left me-1 text-primary"></i> <span>Back to Directory</span>
                </a>
            </div>
        </div>

        <!-- 2. Form Setup Telemetry KPI Overview -->
        <div class="row g-2 mb-3">
            <div class="col-6 col-xl-3">
                <div class="card metric-card metric-card-blue h-100 mb-0">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted stat-label-title text-truncate">Workflow Type</span>
                        <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <h4 class="fw-bold my-0 text-dark stat-val-num">
                            Custom Form
                        </h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="card metric-card metric-card-green h-100 mb-0">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted stat-label-title text-truncate">Default Flow</span>
                        <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                            <i class="fa-solid fa-table-columns"></i>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <h4 class="fw-bold my-0 text-success stat-val-num">
                            Inline Flow
                        </h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="card metric-card metric-card-purple h-100 mb-0">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted stat-label-title text-truncate">Lead Routing</span>
                        <div class="metric-icon-square bg-purple-subtle text-primary shadow-xs" style="background-color: #f3e8ff; color: #7e22ce;">
                            <i class="fa-solid fa-envelope-circle-check"></i>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <h4 class="fw-bold my-0 text-dark stat-val-num">
                            Admin Email
                        </h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="card metric-card metric-card-amber h-100 mb-0">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted stat-label-title text-truncate">Storage Policy</span>
                        <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                            <i class="fa-solid fa-database"></i>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <h4 class="fw-bold my-0 text-warning-emphasis stat-val-num">
                            Database Log
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Form Setup Body: 3-Tier Enterprise Card System -->
        <form method="POST" action="{{ route('admin.forms.store') }}" id="formCreateEntityForm">
            @csrf
            <input type="hidden" name="logo" id="create_form_logo_media" value="{{ old('logo') }}">

            <!-- TIER 1: Form Identity, Slug & Branding Asset -->
            <div class="builder-section-card">
                <div class="section-header-wrap">
                    <span class="fw-bold text-dark small" style="font-size: 13px;">
                        <i class="fa-solid fa-id-card text-primary me-1"></i> 1. Form Identity & Branding
                    </span>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <!-- Form Name & Show Title Switch -->
                        <div class="col-12 col-md-8">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0">Form Name *</label>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="show_title" id="chk_show_title" value="1" {{ old('show_title', true) ? 'checked' : '' }}>
                                    <label class="form-check-label small text-muted" for="chk_show_title">Display Form Title</label>
                                </div>
                            </div>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Contact Us" required>
                            @error('name')
                                <div class="text-danger small mt-1" style="font-size: 11px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Slug -->
                        <div class="col-12 col-md-4">
                            <label class="form-label">Form Slug</label>
                            <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" placeholder="contact-us">
                        </div>

                        <!-- Description & Show Description Switch -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0">User Instructions</label>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="show_description" id="chk_show_desc" value="1" {{ old('show_description', true) ? 'checked' : '' }}>
                                    <label class="form-check-label small text-muted" for="chk_show_desc">Show Description on Form</label>
                                </div>
                            </div>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief note explaining the purpose of this form to users...">{{ old('description') }}</textarea>
                        </div>

                        <!-- Global Media Chooser: Logo / Header Banner -->
                        <div class="col-12">
                            <label class="form-label d-flex justify-content-between align-items-center">
                                <span><i class="fa-solid fa-paperclip text-primary me-1"></i> Form Header Image</span>
                                <span class="badge bg-light text-muted border" style="font-size: 9.5px; font-weight: 600;">MEDIA LIBRARY</span>
                            </label>
                            <div class="proof-media-surface">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" onclick="chooseFormLogoMedia()">
                                        <i class="fa-solid fa-photo-film me-1"></i>
                                        <span>Choose from Media Library</span>
                                    </button>
                                    <span class="small text-muted" style="font-size: 11px;">Attach Image or logo</span>
                                </div>

                                <div id="form_logo_preview_wrap" class="mt-2 {{ old('logo') ? 'd-flex' : 'd-none' }} align-items-center gap-2 bg-white border p-2" style="border-radius: 5px;">
                                    <div class="proof-thumb-box shadow-xs" onclick="zoomFormLogoPreview()" title="Click to Zoom Logo">
                                        <img id="form_logo_preview_img" src="{{ old('logo') }}" alt="Logo Preview">
                                        <div class="proof-thumb-hover-overlay">
                                            <i class="fas fa-search-plus text-white" style="font-size: 9px;"></i>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-grow-1">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 9.5px; font-weight: 600;">
                                            <i class="fas fa-check-circle me-1"></i>Logo Attached
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

            <!-- TIER 2: Live Layout, Flow & Visual Form Styler -->
            <div class="builder-section-card">
                <div class="section-header-wrap">
                    <span class="fw-bold text-dark small" style="font-size: 13px;">
                        <i class="fa-solid fa-palette text-warning me-1"></i> 2. Form Layout & Styling
                    </span>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="styles[show_labels]" id="chk_show_labels" value="1" checked>
                        <label class="form-check-label fw-bold small text-dark" for="chk_show_labels" style="font-size: 11px;">Display Field Labels</label>
                    </div>
                </div>
                <div class="p-3">
                    <!-- Flow & Buttons Group -->
                    <div class="sub-group-header">
                        <i class="fa-solid fa-sliders text-primary"></i>
                        <span>Grid, Buttons & Selectors</span>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label text-primary">Field Flow Layout *</label>
                            <select name="styles[field_flow]" class="form-select fw-semibold">
                                <option value="inline" selected>Side by Side (Inline Grid Flow)</option>
                                <option value="stacked">Top to Bottom (Stacked Vertical)</option>
                                <option value="single_line">Single Line (1-Row Box)</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Button Alignment</label>
                            <select name="styles[button_align]" class="form-select">
                                <option value="left" selected>Left Align</option>
                                <option value="center">Center Align</option>
                                <option value="right">Right Align</option>
                                <option value="full">Full Width (100%)</option>
                                <option value="inline_last">Inline Beside Field</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Button Size</label>
                            <select name="styles[button_size]" class="form-select">
                                <option value="sm">Small Compact</option>
                                <option value="md" selected>Medium Standard</option>
                                <option value="lg">Large Prominent</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Submit Button Label</label>
                            <input type="text" name="styles[button_text]" class="form-control" value="Submit">
                        </div>

                        <div class="col-12 col-md-6 mt-1">
                            <label class="form-label">Custom Form HTML ID (Optional)</label>
                            <input type="text" name="styles[custom_id]" class="form-control" placeholder="newsletter-popup-form">
                        </div>
                        <div class="col-12 col-md-6 mt-1">
                            <label class="form-label">Custom Container CSS Class (Optional)</label>
                            <input type="text" name="styles[custom_class]" class="form-control" placeholder="custom-lead-form shadow-sm">
                        </div>
                    </div>

                    <!-- Container Colors & Box Model -->
                    <div class="sub-group-header">
                        <i class="fa-solid fa-box text-success"></i>
                        <span>Container Box Model &amp; Spacings</span>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label">Form Background</label>
                            <div class="d-flex gap-1 align-items-center">
                                <input type="color" name="styles[form_bg]" class="form-control color-picker-input" value="#ffffff" style="width: 44px;">
                                <div class="color-hex-tag flex-grow-1">#ffffff</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Form Border Color</label>
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
                            <label class="form-label">Form Padding (px)</label>
                            <input type="number" name="styles[form_padding]" class="form-control bn-digit font-tabular" value="24" min="0" max="60">
                        </div>
                    </div>

                    <!-- Field Input Stylings -->
                    <div class="sub-group-header">
                        <i class="fa-solid fa-i-cursor text-info"></i>
                        <span>Labels, Inputs & Borders</span>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label">Field Labels Color</label>
                            <div class="d-flex gap-1 align-items-center">
                                <input type="color" name="styles[label_color]" class="form-control color-picker-input" value="#1e293b" style="width: 44px;">
                                <div class="color-hex-tag flex-grow-1">#1e293b</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Field Input Background</label>
                            <div class="d-flex gap-1 align-items-center">
                                <input type="color" name="styles[input_bg]" class="form-control color-picker-input" value="#ffffff" style="width: 44px;">
                                <div class="color-hex-tag flex-grow-1">#ffffff</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Field Text Color</label>
                            <div class="d-flex gap-1 align-items-center">
                                <input type="color" name="styles[input_text]" class="form-control color-picker-input" value="#0f172a" style="width: 44px;">
                                <div class="color-hex-tag flex-grow-1">#0f172a</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Field Border Color</label>
                            <div class="d-flex gap-1 align-items-center">
                                <input type="color" name="styles[input_border]" class="form-control color-picker-input" value="#cbd5e1" style="width: 44px;">
                                <div class="color-hex-tag flex-grow-1">#cbd5e1</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Field Border Radius (px)</label>
                            <input type="number" name="styles[input_radius]" class="form-control bn-digit font-tabular" value="6" min="0" max="30">
                        </div>
                    </div>

                    <!-- Button Stylings -->
                    <div class="sub-group-header">
                        <i class="fa-solid fa-square-check text-danger"></i>
                        <span>Button Colors & Hover</span>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label">Button Background</label>
                            <div class="d-flex gap-1 align-items-center">
                                <input type="color" name="styles[btn_bg]" class="form-control color-picker-input" value="#2563eb" style="width: 44px;">
                                <div class="color-hex-tag flex-grow-1">#2563eb</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Button Text Color</label>
                            <div class="d-flex gap-1 align-items-center">
                                <input type="color" name="styles[btn_text]" class="form-control color-picker-input" value="#ffffff" style="width: 44px;">
                                <div class="color-hex-tag flex-grow-1">#ffffff</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Button Hover Background</label>
                            <div class="d-flex gap-1 align-items-center">
                                <input type="color" name="styles[btn_hover_bg]" class="form-control color-picker-input" value="#1d4ed8" style="width: 44px;">
                                <div class="color-hex-tag flex-grow-1">#1d4ed8</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Button Hover Text Color</label>
                            <div class="d-flex gap-1 align-items-center">
                                <input type="color" name="styles[btn_hover_text]" class="form-control color-picker-input" value="#ffffff" style="width: 44px;">
                                <div class="color-hex-tag flex-grow-1">#ffffff</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Button Border Radius (px)</label>
                            <input type="number" name="styles[btn_radius]" class="form-control bn-digit font-tabular" value="6" min="0" max="40">
                        </div>
                    </div>

                    <!-- Custom Scoped CSS Textarea -->
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label">Custom CSS Overrides</label>
                            <textarea name="custom_css" class="form-control" rows="3" placeholder="Write or paste custom css">{{ old('custom_css') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TIER 3: Submission Handlers & Notification Engine -->
            <div class="builder-section-card">
                <div class="section-header-wrap">
                    <span class="fw-bold text-dark small" style="font-size: 13px;">
                        <i class="fa-solid fa-envelope-open-text text-success me-1"></i>3. Notifications & Workflow
                    </span>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Admin Notification Email</label>
                            <input type="email" name="admin_email" class="form-control" value="{{ old('admin_email', 'example@gmail.com') }}" placeholder="admin@example.com">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Submission Email Subject</label>
                            <input type="text" name="email_subject" class="form-control" value="{{ old('email_subject', 'New Form Submission') }}" placeholder="New Contact Form Submission">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Success Message</label>
                            <input type="text" name="success_message" class="form-control" value="{{ old('success_message', 'Your message has been sent successfully.') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Redirect URL (Optional)</label>
                            <input type="text" name="redirect_url" class="form-control" value="{{ old('redirect_url') }}" placeholder="/thank-you">
                        </div>

                        <!-- Behavior Toggles -->
                        <div class="col-12 mt-2">
                            <label class="form-label mb-2">Activation & Email Rules</label>
                            <div class="d-flex flex-wrap gap-3 form-check-group p-2.5 border bg-light" style="border-radius: 6px;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="chk_active" value="1" checked>
                                    <label class="form-check-label fw-semibold" for="chk_active">Active Status</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="save_submissions" id="chk_save_subs" value="1" checked>
                                    <label class="form-check-label fw-semibold" for="chk_save_subs">Save Submissions</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="send_admin_email" id="chk_send_admin" value="1" checked>
                                    <label class="form-check-label fw-semibold" for="chk_send_admin">Send Admin Email</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="send_user_email" id="chk_send_user" value="1">
                                    <label class="form-check-label fw-semibold" for="chk_send_user">Send User Email</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Actions Bar -->
            <div class="builder-section-card p-3 mb-4">
                <div class="d-flex justify-content-between align-items-center bottom-action-bar">
                    <a href="{{ route('admin.forms.index') }}" class="btn btn-outline-secondary btn-compact text-dark order-2 order-sm-1">
                        <i class="fa-solid fa-arrow-left me-1"></i> <span>Cancel & Return</span>
                    </a>
                    <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-4 order-1 order-sm-2" style="min-height: 36px;">
                        <i class="fa-solid fa-check-circle me-1"></i> <span>Create Form & Fields</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Lightbox Zoom Modal for Form Logo -->
<div class="modal fade fb-create-modal" id="formLogoZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2 bg-white" style="border-radius: 8px !important;">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12px;" id="formLogoZoomTitle">
                    <i class="fa-solid fa-image text-primary me-1"></i> Form Header Preview
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

    // Color Pickers Realtime Input Sync
    document.querySelectorAll('.color-picker-input').forEach(input => {
        input.addEventListener('input', function() {
            const hexTag = this.parentElement.querySelector('.color-hex-tag');
            if (hexTag) hexTag.innerText = this.value;
        });
    });
</script>
@endsection