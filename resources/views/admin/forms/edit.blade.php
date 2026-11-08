@extends('layouts.admin')

@section('title', 'Form Builder - ' . $form->name . ' - ' . config('app.name'))

@php
    $resolveFormMedia = function($path, $fallback = '') {
        if (empty($path)) return $fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) return show_image($path, $fallback);
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset(ltrim(str_replace(['public_html/', 'public/'], '', $path), '/'));
    };

    $currentLogoRaw = $form->logo ?? ($form->banner ?? null);
    $currentLogoUrl = !empty($currentLogoRaw) ? $resolveFormMedia($currentLogoRaw) : null;
    $styles = is_array($form->styles) ? $form->styles : (json_decode($form->styles ?? '', true) ?? []);

    // Existing and Default Style Parameters
    $fieldFlow    = $styles['field_flow'] ?? (($styles['layout'] ?? '') === 'horizontal' ? 'single_line' : 'inline');
    $showLabels   = isset($styles['show_labels']) ? (bool)$styles['show_labels'] : true;
    $btnAlign     = $styles['button_align'] ?? 'left';
    $btnSize      = $styles['button_size'] ?? 'md';
    $btnText      = !empty($styles['button_text']) ? $styles['button_text'] : ($form->submit_button_text ?? 'Submit Request');
    $customId     = $styles['custom_id'] ?? '';
    $customClass  = $styles['custom_class'] ?? '';
    $formBg       = $styles['form_bg'] ?? '#ffffff';
    $formBorder   = $styles['border_color'] ?? '#e2e8f0';
    $formRadius   = isset($styles['border_radius']) ? (int)$styles['border_radius'] : 8;
    $formPadding  = isset($styles['form_padding']) ? (int)$styles['form_padding'] : 24;
    $inputBg      = $styles['input_bg'] ?? '#ffffff';
    $inputText    = $styles['input_text'] ?? '#0f172a';
    $inputBorder  = $styles['input_border'] ?? '#cbd5e1';
    $inputRadius  = isset($styles['input_radius']) ? (int)$styles['input_radius'] : 6;
    $labelColor   = $styles['label_color'] ?? '#1e293b';
    $btnBg        = $styles['btn_bg'] ?? '#2563eb';
    $btnTextCol   = $styles['btn_text'] ?? '#ffffff';
    $btnHoverBg   = $styles['btn_hover_bg'] ?? '#1d4ed8';
    $btnHoverText = $styles['btn_hover_text'] ?? '#ffffff';
    $btnRadius    = isset($styles['btn_radius']) ? (int)$styles['btn_radius'] : 6;

    $formFieldsCount = $form->fields ? $form->fields->count() : 0;
    $formSubmissionsCount = $form->submissions_count ?? ($form->submissions ? $form->submissions->count() : 0);
@endphp

@section('admin_content')
<!-- Font Awesome 6.5.1 CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Google Fonts: Plus Jakarta Sans & Inter -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* Professional Enterprise Typography */
    #formBuilderStudioRoot,
    .builder-modal-scope {
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        letter-spacing: -0.01em;
        color: #1e293b;
    }

    /* FontAwesome Protection */
    #formBuilderStudioRoot i,
    #formBuilderStudioRoot .fa,
    #formBuilderStudioRoot .fas,
    #formBuilderStudioRoot .far,
    #formBuilderStudioRoot .fab,
    #formBuilderStudioRoot .fa-solid,
    #formBuilderStudioRoot .fa-regular,
    .builder-modal-scope i,
    .builder-modal-scope .fa,
    .builder-modal-scope .fa-solid {
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome" !important;
        font-style: normal;
        font-weight: 900;
        line-height: 1;
        letter-spacing: normal !important;
        display: inline-block;
    }
    #formBuilderStudioRoot .far, .builder-modal-scope .far {
        font-weight: 400 !important;
    }

    /* Strict 5px Element Radius Across All Controls */
    #formBuilderStudioRoot .btn,
    #formBuilderStudioRoot .btn-sm,
    #formBuilderStudioRoot button,
    #formBuilderStudioRoot .form-control,
    #formBuilderStudioRoot .form-select,
    #formBuilderStudioRoot .input-group-text,
    #formBuilderStudioRoot .badge,
    #formBuilderStudioRoot .card,
    #formBuilderStudioRoot .metric-card,
    #formBuilderStudioRoot .studio-header-card,
    #formBuilderStudioRoot .studio-panel-card,
    #formBuilderStudioRoot .studio-nav-pill,
    #formBuilderStudioRoot .field-draggable-item,
    #formBuilderStudioRoot .shortcode-pill,
    #formBuilderStudioRoot .proof-media-surface,
    #formBuilderStudioRoot .proof-thumb-box,
    #formBuilderStudioRoot .form-preview-card,
    #formBuilderStudioRoot .id-display-pill,
    #formBuilderStudioRoot .color-hex-tag,
    .builder-modal-scope .modal-content,
    .builder-modal-scope .btn,
    .builder-modal-scope .form-control,
    .builder-modal-scope .form-select,
    .builder-modal-scope .badge,
    .builder-modal-scope .nav-pills .nav-link {
        border-radius: 5px !important;
    }

    #formBuilderStudioRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #formBuilderStudioRoot .input-group > .form-control,
    #formBuilderStudioRoot .input-group > .form-select {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Clean Tabular Numbers */
    .bn-digit,
    .font-tabular {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
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

    /* Clean White Enterprise Header Card */
    .studio-header-card {
        background: #ffffff;
        color: #0f172a;
        padding: 14px 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 14px;
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
    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-left: 3.5px solid #f59e0b !important; }
    .metric-card-purple { background: linear-gradient(180deg, #ffffff 0%, #faf5ff 100%); border-left: 3.5px solid #a855f7 !important; }

    /* Modern Shortcode Pill */
    .shortcode-pill {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 600;
        color: #2563eb;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 5px !important;
        transition: all 0.15s ease;
    }
    .shortcode-pill:hover {
        background: #eff6ff;
        border-color: #bfdbfe;
    }

    /* Navigation Tabs Strip */
    .studio-nav-strip {
        display: flex;
        flex-wrap: nowrap;
        gap: 6px;
        overflow-x: auto;
        padding: 2px 0 8px;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }
    .studio-nav-strip::-webkit-scrollbar {
        display: none;
    }
    .studio-nav-pill {
        font-size: 11.5px;
        font-weight: 600;
        padding: 6px 14px;
        color: #475569;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        transition: all 0.15s ease;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
    }
    .studio-nav-pill:hover {
        background: #f8fafc;
        color: #0f172a;
        border-color: #94a3b8;
    }
    .studio-nav-pill.active {
        color: #ffffff !important;
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25);
    }

    .studio-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 14px;
        overflow: hidden;
    }
    .studio-card-header {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        background: #ffffff;
        font-weight: 700;
        font-size: 12.5px;
        color: #0f172a;
    }
    .studio-card-body {
        padding: 16px;
    }

    .form-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #formBuilderStudioRoot .form-control,
    #formBuilderStudioRoot .form-select,
    .builder-modal-scope .form-control,
    .builder-modal-scope .form-select {
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
    #formBuilderStudioRoot .form-control:focus,
    #formBuilderStudioRoot .form-select:focus,
    .builder-modal-scope .form-control:focus,
    .builder-modal-scope .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }
    textarea.form-control {
        min-height: 72px;
        line-height: 1.45;
    }

    /* Draggable Form Field Items */
    .field-draggable-item {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        margin-bottom: 8px;
        border-radius: 6px !important;
        transition: all 0.15s ease;
    }
    .field-draggable-item:hover {
        border-color: #2563eb;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
    }
    .field-header-row {
        padding: 10px 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: grab;
    }
    .drag-grip-icon {
        color: #94a3b8;
        font-size: 13px;
        cursor: grab;
        padding: 4px;
    }
    .field-type-icon {
        width: 32px;
        height: 32px;
        border-radius: 5px !important;
        background: #eff6ff;
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        flex-shrink: 0;
    }
    .field-main-title {
        font-weight: 700;
        color: #0f172a;
        font-size: 12.5px;
    }
    .field-meta-tags {
        font-size: 10.5px;
        color: #64748b;
        font-weight: 500;
    }
    .field-item-actions {
        margin-left: auto;
        display: flex;
        gap: 4px;
    }
    .field-action-square-btn {
        width: 26px;
        height: 26px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #475569;
        font-size: 10px;
        border-radius: 5px !important;
        transition: all 0.12s ease;
        cursor: pointer;
    }
    .field-action-square-btn:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    .add-field-action-btn {
        width: 100%;
        padding: 9px;
        border: 1.5px dashed #cbd5e1;
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
        font-size: 11.5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border-radius: 6px !important;
        transition: all 0.15s ease;
    }
    .add-field-action-btn:hover {
        border-color: #2563eb;
        color: #2563eb;
        background: #eff6ff;
    }

    /* Media Uploader Box */
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
        border-radius: 5px !important;
    }

    .form-preview-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 16px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        border-radius: 6px !important;
    }
    .preview-header-area {
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 10px;
        margin-bottom: 12px;
    }

    .id-display-pill {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 4px 8px;
        font-size: 11px;
        color: #475569;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 5px !important;
    }

    /* Minimal Modal System (Screenshot Reference) */
    .minimal-modal-card {
        background: #ffffff !important;
        border: 1px solid #f1f5f9 !important;
        border-radius: 12px !important;
        box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.14), 0 0 1px 1px rgba(15, 23, 42, 0.04) !important;
        padding: 24px 26px !important;
        text-align: left !important;
    }
    .minimal-modal-title {
        font-size: 16px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        margin-bottom: 6px !important;
        letter-spacing: -0.02em !important;
    }
    .minimal-modal-subtext {
        font-size: 13px !important;
        color: #64748b !important;
        margin-bottom: 22px !important;
        line-height: 1.45 !important;
    }
    .btn-minimal-delete {
        background-color: #c93424 !important;
        border: none !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        padding: 7px 22px !important;
        border-radius: 7px !important;
        box-shadow: 0 4px 12px rgba(201, 52, 36, 0.28) !important;
        transition: all 0.15s ease !important;
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .btn-minimal-delete:hover {
        background-color: #b02717 !important;
        box-shadow: 0 6px 14px rgba(201, 52, 36, 0.38) !important;
        color: #ffffff !important;
    }
    .btn-minimal-cancel {
        background: transparent !important;
        border: none !important;
        color: #64748b !important;
        font-weight: 500 !important;
        font-size: 13.5px !important;
        padding: 7px 14px !important;
        box-shadow: none !important;
        transition: color 0.15s ease !important;
        cursor: pointer !important;
    }
    .btn-minimal-cancel:hover {
        color: #0f172a !important;
        background: transparent !important;
    }

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .studio-header-card {
            padding: 12px 14px !important;
        }
        .shortcode-pill {
            width: 100%;
            justify-content: space-between;
        }
        .studio-card-body {
            padding: 10px !important;
        }
        .field-header-row {
            padding: 8px 10px;
        }
        .settings-toggle-grid {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .top-action-btns {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .top-action-btns .btn,
        .top-action-btns .shortcode-pill {
            width: 100%;
            justify-content: center;
        }
        .metric-card {
            padding: 10px !important;
            min-height: 80px !important;
        }
        .stat-label-title {
            font-size: 10px !important;
        }
        .stat-val-num {
            font-size: 13px !important;
        }
        .minimal-modal-card {
            padding: 20px 20px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="formBuilderStudioRoot">

    <!-- 1. Top Header Banner -->
    <div class="studio-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <a href="{{ route('admin.forms.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs bg-white text-dark fw-semibold">
                    <i class="fa-solid fa-arrow-left me-1 text-primary"></i> Directory
                </a>
                
            </div>
            <h5 class="fw-bold text-dark my-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fa-solid fa-pen-to-square text-primary"></i>
                <span>{{ $form->name }}</span>
            </h5>
        </div>
    </div>

    <!-- 2. Form Telemetry Status KPI Overview -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-blue h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Active Fields</span>
                    <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-dark bn-digit font-tabular stat-val-num">
                        {{ $formFieldsCount }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-green h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Form Status</span>
                    <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-success stat-val-num">
                        {{ $form->is_active ? 'Live Running' : 'Inactive' }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-purple h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Submissions Logged</span>
                    <div class="metric-icon-square bg-purple-subtle text-primary shadow-xs" style="background-color: #f3e8ff; color: #7e22ce;">
                        <i class="fa-solid fa-inbox"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-dark bn-digit font-tabular stat-val-num">
                        {{ number_format($formSubmissionsCount) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-amber h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Grid Flow Mode</span>
                    <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                        <i class="fa-solid fa-table-columns"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-warning-emphasis stat-val-num" style="font-size: 14px;">
                        {{ strtoupper($fieldFlow) }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

   <!-- 3. Studio Navigation Tabs Strip -->
<!-- 3. Studio Navigation Tabs Strip -->
<div class="studio-nav-strip mb-3">
    <!-- Desktop: 1 row flex, Mobile: 3 rows 2 columns grid -->
    <style>
        @media (max-width: 767.98px) {
            #studioMainTabNav {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 8px !important;
            }
            #studioMainTabNav .studio-nav-pill {
                width: 100% !important;
                min-width: 0 !important;
                font-size: 11px !important;
                padding: 8px 6px !important;
                text-align: center !important;
                justify-content: center !important;
            }
        }
    </style>
    
    <div class="status-tabs-scroll-wrap overflow-hidden">
        <div class="d-flex flex-nowrap align-items-center gap-2 w-100 nav nav-pills" id="studioMainTabNav" role="tablist">
            <button type="button" class="studio-nav-pill active shadow-xs flex-fill text-center d-inline-flex align-items-center justify-content-center gap-2 px-3 py-2 text-nowrap" id="btn-tab-fields" data-bs-target="#tab-fields" role="tab" aria-selected="true">
                <i class="fa-solid fa-layer-group text-primary"></i>
                <span>Form Builder</span>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle bn-digit font-tabular px-1.5 py-0.5" style="font-size: 10px;" id="badgeFieldsCount">
                    {{ $form->fields->count() }}
                </span>
            </button>
            <button type="button" class="studio-nav-pill shadow-xs flex-fill text-center d-inline-flex align-items-center justify-content-center gap-2 px-3 py-2 text-nowrap" id="btn-tab-settings" data-bs-target="#tab-settings" role="tab" aria-selected="false">
                <i class="fa-solid fa-gear text-secondary"></i>
                <span>General Settings</span>
            </button>
            <button type="button" class="studio-nav-pill shadow-xs flex-fill text-center d-inline-flex align-items-center justify-content-center gap-2 px-3 py-2 text-nowrap" id="btn-tab-notifications" data-bs-target="#tab-notifications" role="tab" aria-selected="false">
                <i class="fa-solid fa-envelope-open-text text-info"></i>
                <span>Emails &amp; Autoresponder</span>
            </button>
            <button type="button" class="studio-nav-pill shadow-xs flex-fill text-center d-inline-flex align-items-center justify-content-center gap-2 px-3 py-2 text-nowrap" id="btn-tab-appearance" data-bs-target="#tab-appearance" role="tab" aria-selected="false">
                <i class="fa-solid fa-palette text-warning"></i>
                <span>Form Style</span>
            </button>
            <button type="button" class="studio-nav-pill shadow-xs flex-fill text-center d-inline-flex align-items-center justify-content-center gap-2 px-3 py-2 text-nowrap" id="btn-tab-security" data-bs-target="#tab-security" role="tab" aria-selected="false">
                <i class="fa-solid fa-shield-halved text-danger"></i>
                <span>Spam Protection & Limits</span>
            </button>
            <button type="button" class="studio-nav-pill shadow-xs flex-fill text-center d-inline-flex align-items-center justify-content-center gap-2 px-3 py-2 text-nowrap" id="btn-tab-integrations" data-bs-target="#tab-integrations" role="tab" aria-selected="false">
                <i class="fa-solid fa-network-wired text-success"></i>
                <span>Integrations</span>
            </button>
        </div>
    </div>
</div>


    <!-- 4. Studio Tab Content Panes -->
    <div class="tab-content" id="studioMainTabContent">

        <!-- TAB 1: FORM FIELDS BUILDER & LIVE PREVIEW WORKSPACE -->
        <div class="tab-pane fade show active" id="tab-fields" role="tabpanel">
            <div class="row g-3">
                <div class="col-12 col-lg-7">
                    <div class="studio-panel-card">
                        <div class="studio-card-header d-flex justify-content-between align-items-center">
                            <span><i class="fa-solid fa-bars-staggered me-1 text-primary"></i> Form Fields </span>
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn btn-outline-secondary btn-compact py-0 px-2 shadow-xs bg-white text-dark" style="height: 26px; font-size: 10.5px !important;" onclick="refreshFieldListOrder()">
                                    <i class="fa-solid fa-rotate me-1 text-primary"></i> Sync
                                </button>
                                <button type="button" class="btn btn-primary btn-compact py-0 px-2 shadow-xs fw-bold" style="height: 26px; font-size: 10.5px !important;" data-bs-toggle="modal" data-bs-target="#fieldModal" onclick="prepareAddFieldModal()">
                                    <i class="fa-solid fa-plus me-1"></i> Add Field
                                </button>
                            </div>
                        </div>

                        <div class="studio-card-body">
                            <div id="field-list">
                                @forelse($form->fields as $field)
                                    <div class="field-draggable-item shadow-xs" data-id="{{ $field->id }}" id="field-card-{{ $field->id }}">
                                        <div class="field-header-row">
                                            <div class="drag-grip-icon" title="Drag to reorder position">
                                                <i class="fa-solid fa-grip-vertical"></i>
                                            </div>

                                            <div class="field-type-icon">
                                                <i class="fa-solid
                                                    @switch($field->type)
                                                        @case('text') fa-font @break
                                                        @case('email') fa-envelope @break
                                                        @case('phone') fa-phone @break
                                                        @case('number') fa-hashtag @break
                                                        @case('textarea') fa-align-left @break
                                                        @case('select') fa-list @break
                                                        @case('radio') fa-circle-dot @break
                                                        @case('checkbox') fa-square-check @break
                                                        @case('file') fa-file-arrow-up @break
                                                        @case('date') fa-calendar @break
                                                        @case('time') fa-clock @break
                                                        @case('url') fa-link @break
                                                        @default fa-i-cursor
                                                    @endswitch
                                                "></i>
                                            </div>

                                            <div class="min-w-0 flex-grow-1">
                                                <div class="field-main-title text-truncate">
                                                    {{ $field->label }}
                                                    @if($field->is_required)
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </div>
                                                <div class="field-meta-tags text-truncate">
                                                    <span class="badge bg-light text-dark border px-1.5 py-0.5">{{ strtoupper($field->type) }}</span>
                                                    <span>{{ $field->name }}</span>
                                                    <span>•</span>
                                                    <span>Width: {{ $field->column_width ?? 12 }}/12</span>
                                                </div>
                                            </div>

                                            <div class="field-item-actions flex-shrink-0">
                                                <button type="button" class="field-action-square-btn text-secondary shadow-xs" onclick="cloneExistingField({{ $field->id }})" title="Duplicate Field">
                                                    <i class="fa-regular fa-clone"></i>
                                                </button>
                                                <button type="button" class="field-action-square-btn text-primary edit-field shadow-xs" data-field='@json($field)' title="Configure Field">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <button type="button" class="field-action-square-btn text-danger delete-field shadow-xs" data-id="{{ $field->id }}" title="Delete Field">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-muted small border bg-light" id="empty-fields-placeholder" style="border-radius: 5px;">
                                        <i class="fa-regular fa-square-plus fa-2x mb-2 d-block opacity-50"></i>
                                        <strong class="d-block text-dark">No form fields configured yet.</strong>
                                        <p class="mb-2" style="font-size: 11px;">Start building your dynamic form by adding input fields below.</p>
                                        <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" data-bs-toggle="modal" data-bs-target="#fieldModal" onclick="prepareAddFieldModal()">
                                            <i class="fa-solid fa-plus me-1"></i> Add First Field
                                        </button>
                                    </div>
                                @endforelse
                            </div>

                            <button type="button" class="add-field-action-btn mt-2 shadow-xs" data-bs-toggle="modal" data-bs-target="#fieldModal" onclick="prepareAddFieldModal()">
                                <i class="fa-solid fa-plus me-1"></i> Add Another Field
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Live Simulator -->
                <div class="col-12 col-lg-5">
                    <div class="form-preview-card" style="border-radius: 5px;">
                        <div class="preview-header-area d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold text-dark mb-0 mt-1" style="font-size: 13px;">Preview</h6>
                            </div>
                        </div>

                        <div id="previewSimulatorLogoBox" class="text-center mb-2 {{ $currentLogoUrl ? 'd-block' : 'd-none' }}">
                            <img id="previewSimulatorLogoImg" src="{{ $currentLogoUrl }}" alt="Logo" class="img-fluid border p-1 shadow-xs" style="max-height: 48px; border-radius: 5px;">
                        </div>

                        <div class="text-center mb-3">
                            <h5 class="fw-bold text-dark mb-1 {{ ($form->show_title ?? true) ? '' : 'text-decoration-line-through text-muted' }}" id="previewSimTitle" style="font-size: 15px;">
                                {{ $form->name }}
                                @if(!($form->show_title ?? true))
                                    <span class="badge bg-secondary-subtle text-secondary" style="font-size: 9px;">Hidden</span>
                                @endif
                            </h5>
                            @if(!empty($form->description))
                                <small class="text-muted d-block {{ ($form->show_description ?? true) ? '' : 'text-decoration-line-through opacity-50' }}" id="previewSimDesc" style="font-size: 11px;">
                                    {{ $form->description }}
                                    @if(!($form->show_description ?? true))
                                        <span class="badge bg-secondary-subtle text-secondary" style="font-size: 8px;">Hidden</span>
                                    @endif
                                </small>
                            @endif
                        </div>

                        <form onsubmit="event.preventDefault(); showStudioModalNotice('This is an interactive live simulator preview. All fields validate dynamically.', 'Simulator Active', 'info');">
                            <div class="row g-2" id="liveFormFieldsSimulator">
                                @forelse($form->fields as $f)
                                    <div class="col-{{ $f->column_width ?? 12 }} mb-1">
                                        @if($showLabels)
                                            <label class="form-label mb-1" style="font-size: 11px;">
                                                {{ $f->label }}
                                                @if($f->is_required)<span class="text-danger">*</span>@endif
                                            </label>
                                        @endif
                                        @if($f->type === 'textarea')
                                            <textarea class="form-control" rows="2" placeholder="{{ $f->placeholder ?: (!$showLabels ? $f->label : '') }}" style="font-size: 11px;"></textarea>
                                        @elseif($f->type === 'select')
                                            <select class="form-select" style="font-size: 11px;">
                                                <option value="">{{ $f->placeholder ?: (!$showLabels ? 'Select ' . $f->label : '-- Select Option --') }}</option>
                                                @if(is_array($f->options))
                                                    @foreach($f->options as $opt)
                                                        <option value="{{ $opt['value'] ?? $opt }}">{{ $opt['label'] ?? $opt }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        @elseif($f->type === 'checkbox')
                                            <div class="form-check mt-1">
                                                <input class="form-check-input" type="checkbox">
                                                <label class="form-check-label" style="font-size: 11px;">{{ $f->placeholder ?: 'I accept and confirm' }}</label>
                                            </div>
                                        @else
                                            <input type="{{ in_array($f->type, ['text', 'email', 'phone', 'number', 'date', 'time', 'url']) ? $f->type : 'text' }}" class="form-control" placeholder="{{ $f->placeholder ?: (!$showLabels ? $f->label : '') }}" style="font-size: 11px;">
                                        @endif
                                        @if(!empty($f->help_text))
                                            <small class="text-muted d-block" style="font-size: 9.5px;">{{ $f->help_text }}</small>
                                        @endif
                                    </div>
                                @empty
                                    <div class="col-12 text-center py-4 text-muted small">
                                        Fields added on the left will render reactively here.
                                    </div>
                                @endforelse
                            </div>

                            <div class="mt-3 text-center {{ $btnAlign === 'center' ? 'text-center' : ($btnAlign === 'right' ? 'text-end' : '') }}">
                                <button type="submit" class="btn col-12 col-md-8  btn-primary btn-compact shadow-xs fw-bold {{ $btnAlign === 'full' ? 'w-100' : '' }}" style="min-height: 34px;">
                                    <span>{{ $btnText }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: GENERAL SETTINGS & BRAND MEDIA -->
        <div class="tab-pane fade" id="tab-settings" role="tabpanel">
            <div class="studio-panel-card">
                <div class="studio-card-header d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-gear me-1 text-primary"></i> General Settings</span>
                    <span class="badge bg-light text-muted border" style="font-size: 9.5px; font-weight: 600;">CONFIG</span>
                </div>

                <div class="studio-card-body">
                    <form method="POST" action="{{ route('admin.forms.update', $form) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="logo" id="edit_form_logo_media" value="{{ $form->logo ?? '' }}">

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label mb-0">Form Name *</label>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="show_title" id="chk_settings_show_title" value="1" {{ $form->show_title ? 'checked' : '' }}>
                                        <label class="form-check-label small text-muted" for="chk_settings_show_title">Show Form Title</label>
                                    </div>
                                </div>
                                <input type="text" name="name" class="form-control" value="{{ $form->name }}" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Form Slug</label>
                                <input type="text" name="slug" class="form-control" value="{{ $form->slug }}">
                            </div>

                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label mb-0">User Instructions</label>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="show_description" id="chk_settings_show_desc" value="1" {{ $form->show_description ? 'checked' : '' }}>
                                        <label class="form-check-label small text-muted" for="chk_settings_show_desc">Show Description</label>
                                    </div>
                                </div>
                                <textarea name="description" class="form-control" rows="2" placeholder="Brief note explaining the purpose of this form to visitors...">{{ $form->description }}</textarea>
                            </div>

                            <!-- Global Media Chooser Component: Form Logo / Header Banner -->
                            <div class="col-12">
                                <label class="form-label d-flex justify-content-between align-items-center">
                                    <span><i class="fa-solid fa-paperclip text-primary me-1"></i> Form Header Image</span>
                                    <span class="badge bg-light text-muted border" style="font-size: 9px; font-weight: 600;">GLOBAL MEDIA</span>
                                </label>
                                <div class="proof-media-surface">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" onclick="chooseFormEditLogoMedia()">
                                            <i class="fa-solid fa-photo-film me-1"></i>
                                            <span>Choose from Gellary</span>
                                        </button>
                                        <span class="small text-muted" style="font-size: 11px;">Attach image or logo</span>
                                    </div>

                                    <div id="edit_form_logo_preview_wrap" class="mt-2 {{ $currentLogoUrl ? 'd-flex' : 'd-none' }} align-items-center gap-2 bg-white border p-2" style="border-radius: 5px;">
                                        <div class="proof-thumb-box shadow-xs" onclick="zoomFormEditLogoPreview()" title="Click to Zoom Image">
                                            <img id="edit_form_logo_preview_img" src="{{ $currentLogoUrl }}" alt="Logo">
                                            <div class="proof-thumb-hover-overlay">
                                                <i class="fas fa-search-plus text-white" style="font-size: 9px;"></i>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-grow-1">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 9px; font-weight: 600;">
                                                <i class="fas fa-check-circle me-1"></i>Media Attached
                                            </span>
                                            <small class="text-muted d-block text-truncate" id="edit_form_logo_preview_txt" style="font-size: 10px;">{{ $form->logo ?? '' }}</small>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger btn-compact py-0 px-2" style="font-size: 10px !important;" onclick="clearFormEditLogoMedia()">
                                            <i class="fa-solid fa-trash-can me-1"></i>Clear
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Success Message</label>
                                <input type="text" name="success_message" class="form-control" value="{{ $form->success_message }}">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Redirect URL</label>
                                <input type="text" name="redirect_url" class="form-control" value="{{ $form->redirect_url }}" placeholder="/thank-you">
                            </div>

                            <div class="col-12">
                                <label class="form-label mb-2">Form Status</label>
                                <div class="d-flex flex-wrap gap-3 p-2.5 border bg-light settings-toggle-grid" style="border-radius: 6px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="chk_settings_active" value="1" {{ $form->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="chk_settings_active">Form Active</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="save_submissions" id="chk_settings_save" value="1" {{ $form->save_submissions ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="chk_settings_save">Save Form data</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-top justify-content-center d-flex justify-content-end">
                            <button type="submit" class="btn col-12 col-md-12 btn-primary btn-compact shadow-xs fw-bold px-3">
                                <i class="fa-solid fa-check-circle me-1"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- TAB 3: EMAILS, NOTIFICATIONS & AUTORESPONDER -->
        <div class="tab-pane fade" id="tab-notifications" role="tabpanel">
            <div class="studio-panel-card">
                <div class="studio-card-header d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-envelope-open-text me-1 text-info"></i>Notifications & User Confirmation</span>
                    <span class="badge bg-light text-muted border" style="font-size: 9.5px; font-weight: 600;">EMAIL TEMPLATES</span>
                </div>

                <div class="studio-card-body">
                    <form method="POST" action="{{ route('admin.forms.update', $form) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="name" value="{{ $form->name }}">
                        <input type="hidden" name="slug" value="{{ $form->slug }}">

                        <div class="row g-3">
                            <div class="col-12 col-md-6 border-end">
                                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3" style="font-size: 13px;">
                                    <i class="fa-solid fa-user-shield text-primary me-1"></i> Staff Alerts
                                </h6>
                                <div class="mb-2">
                                    <label class="form-label">Admin Email Recipients (Comma-Separated)</label>
                                    <input type="text" name="admin_email" class="form-control" value="{{ $form->admin_email }}" placeholder="admin@example.com, sales@example.com">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Admin Email Subject</label>
                                    <input type="text" name="email_subject" class="form-control" value="{{ $form->email_subject }}">
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="send_admin_email" id="chk_send_admin_notif" value="1" {{ $form->send_admin_email ? 'checked' : '' }}>
                                    <label class="form-check-label" for="chk_send_admin_notif">Email to  Admin or Staff</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3" style="font-size: 13px;">
                                    <i class="fa-solid fa-reply text-success me-1"></i> User Confirmation Email
                                </h6>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="send_user_email" id="chk_send_user_confirm" value="1" {{ $form->send_user_email ? 'checked' : '' }}>
                                    <label class="form-check-label" for="chk_send_user_confirm">Send Confirmation to User</label>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">User Email Subject</label>
                                    <input type="text" name="user_email_subject" class="form-control" value="{{ $form->user_email_subject ?? 'Thank you for contacting us!' }}">
                                </div>
                                <div class="p-2 bg-light border small text-muted" style="font-size: 11px; border-radius: 5px;">
                                   Dynamic Tags: <span class="text-primary fw-semibold">{form_name}</span>, <span class="text-primary fw-semibold">{all_fields}</span>, <span class="text-primary fw-semibold">{submission_id}</span>, <span class="text-primary fw-semibold">{date}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-top d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3">
                                <i class="fa-solid fa-check-circle me-1"></i> Save Notification Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- TAB 4: APPEARANCE & THEME STYLING (WITH REALTIME LIVE PREVIEW PANEL) -->
        <div class="tab-pane fade" id="tab-appearance" role="tabpanel">
            <div class="row g-3">
                
                {{-- Left Column: Style Controls --}}
                <div class="col-12 col-xl-7">
                    <div class="studio-panel-card">
                        <div class="studio-card-header d-flex justify-content-between align-items-center">
                            <span><i class="fa-solid fa-palette me-1 text-warning"></i> Form Layout & Styling</span>
                        </div>

                        <div class="studio-card-body">
                            <form method="POST" action="{{ route('admin.forms.update', $form) }}" id="appearanceSettingsForm">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="name" value="{{ $form->name }}">
                                <input type="hidden" name="slug" value="{{ $form->slug }}">

                                {{-- Section A: Layout & Field Flow Control --}}
                                <div class="p-3 border rounded bg-light bg-opacity-50 mb-3" style="border-radius: 6px !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold text-dark mb-0" style="font-size: 12.5px;">
                                            <i class="fa-solid fa-arrows-split-up-and-left text-primary me-1"></i>Layout & Labels
                                        </h6>
                                        <div class="form-check form-switch mb-0 col-6 col-md-4">
                                            <input class="form-check-input" type="checkbox" name="styles[show_labels]" id="chk_edit_show_labels" value="1" {{ $showLabels ? 'checked' : '' }} onchange="syncAppearanceLivePreview()">
                                            <label class="form-check-label fw-bold small text-dark" for="chk_edit_show_labels">Show Labels</label>
                                        </div>
                                    </div>

                                    <div class="row g-2">
                                        <div class="col-6 col-md-3 col-12 col-md-6">
                                            <label class="form-label text-primary">Field Layout *</label>
                                            <select name="styles[field_flow]" id="style_field_flow" class="form-select fw-semibold" onchange="syncAppearanceLivePreview()">
                                                <option value="inline" {{ $fieldFlow === 'inline' ? 'selected' : '' }}>Inline</option>
                                                <option value="stacked" {{ $fieldFlow === 'stacked' ? 'selected' : '' }}>Top to Bottom </option>
                                                <option value="single_line" {{ $fieldFlow === 'single_line' ? 'selected' : '' }}>Single Row</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Button Alignment</label>
                                            <select name="styles[button_align]" id="style_button_align" class="form-select" onchange="syncAppearanceLivePreview()">
                                                <option value="left" {{ $btnAlign === 'left' ? 'selected' : '' }}>Left Align</option>
                                                <option value="center" {{ $btnAlign === 'center' ? 'selected' : '' }}>Center Align</option>
                                                <option value="right" {{ $btnAlign === 'right' ? 'selected' : '' }}>Right Align</option>
                                                <option value="full" {{ $btnAlign === 'full' ? 'selected' : '' }}>Full Width </option>
                                                <option value="inline_last" {{ $btnAlign === 'inline_last' ? 'selected' : '' }}>Inline Beside Field</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Button Size</label>
                                            <select name="styles[button_size]" id="style_button_size" class="form-select" onchange="syncAppearanceLivePreview()">
                                                <option value="sm" {{ $btnSize === 'sm' ? 'selected' : '' }}>Small Compact</option>
                                                <option value="md" {{ $btnSize === 'md' ? 'selected' : '' }}>Medium Standard</option>
                                                <option value="lg" {{ $btnSize === 'lg' ? 'selected' : '' }}>Large Prominent</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Button Text Label</label>
                                            <input type="text" name="styles[button_text]" id="style_button_text" class="form-control" value="{{ $btnText }}" onkeyup="syncAppearanceLivePreview()">
                                        </div>
                                    </div>
                                </div>

                                {{-- Section B: Unique IDs & Container Styling --}}
                                <div class="p-3 border rounded bg-light bg-opacity-50 mb-3" style="border-radius: 6px !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                                        <h6 class="fw-bold text-dark mb-0 col-12 col-md-6" style="font-size: 12.5px;">
                                            <i class="fa-solid fa-box text-info me-1"></i> Container, CSS ID & Borders
                                        </h6>
                                        <div class="id-display-pill col-12 col-md-6" title="Default Auto Unique Scoped ID">
                                            <span>Default ID:</span>
                                            <strong class="text-primary">#form-wrapper-dynamic-form-{{ $form->id }}</strong>
                                        </div>
                                    </div>

                                    <div class="row g-2">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Custom Form ID</label>
                                            <input type="text" name="styles[custom_id]" class="form-control" value="{{ $customId }}" placeholder="newsletter-popup-form">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Container CSS Class</label>
                                            <input type="text" name="styles[custom_class]" class="form-control" value="{{ $customClass }}" placeholder="promo-form shadow-lg">
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Form Background Color</label>
                                            <div class="d-flex gap-1 align-items-center">
                                                <input type="color" name="styles[form_bg]" id="style_form_bg" class="form-control color-picker-input" value="{{ $formBg }}" style="width: 50%;" oninput="syncAppearanceLivePreview()">
                                                <div class="color-hex-tag flex-grow-1" id="hex_style_form_bg">{{ $formBg }}</div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Form Border Color</label>
                                            <div class="d-flex gap-1 align-items-center">
                                                <input type="color" name="styles[border_color]" id="style_border_color" class="form-control color-picker-input" value="{{ $formBorder }}" style="width: 50%;" oninput="syncAppearanceLivePreview()">
                                                <div class="color-hex-tag flex-grow-1" id="hex_style_border_color">{{ $formBorder }}</div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Form Border Radius (px)</label>
                                            <input type="number" name="styles[border_radius]" id="style_border_radius" class="form-control bn-digit font-tabular" value="{{ $formRadius }}" min="0" max="60" oninput="syncAppearanceLivePreview()">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Form Padding (px)</label>
                                            <input type="number" name="styles[form_padding]" id="style_form_padding" class="form-control bn-digit font-tabular" value="{{ $formPadding }}" min="0" max="60" oninput="syncAppearanceLivePreview()">
                                        </div>
                                    </div>
                                </div>

                                {{-- Section C: Input Fields Styling --}}
                                <div class="p-3 border rounded bg-light bg-opacity-50 mb-3" style="border-radius: 6px !important;">
                                    <h6 class="fw-bold text-dark mb-2" style="font-size: 12.5px;">
                                        <i class="fa-solid fa-i-cursor text-success me-1"></i> Form Fields &amp; Labels Styling
                                    </h6>
                                    <div class="row g-2">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Field Label Color</label>
                                            <div class="d-flex gap-1 align-items-center">
                                                <input type="color" name="styles[label_color]" id="style_label_color" class="form-control color-picker-input" value="{{ $labelColor }}" style="width: 50%;" oninput="syncAppearanceLivePreview()">
                                                <div class="color-hex-tag flex-grow-1" id="hex_style_label_color">{{ $labelColor }}</div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Field Background Color</label>
                                            <div class="d-flex gap-1 align-items-center">
                                                <input type="color" name="styles[input_bg]" id="style_input_bg" class="form-control color-picker-input" value="{{ $inputBg }}" style="width: 50%;" oninput="syncAppearanceLivePreview()">
                                                <div class="color-hex-tag flex-grow-1" id="hex_style_input_bg">{{ $inputBg }}</div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Field Text Color</label>
                                            <div class="d-flex gap-1 align-items-center">
                                                <input type="color" name="styles[input_text]" id="style_input_text" class="form-control color-picker-input" value="{{ $inputText }}" style="width: 50%;" oninput="syncAppearanceLivePreview()">
                                                <div class="color-hex-tag flex-grow-1" id="hex_style_input_text">{{ $inputText }}</div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Field Border Color</label>
                                            <div class="d-flex gap-1 align-items-center">
                                                <input type="color" name="styles[input_border]" id="style_input_border" class="form-control color-picker-input" value="{{ $inputBorder }}" style="width: 50%;" oninput="syncAppearanceLivePreview()">
                                                <div class="color-hex-tag flex-grow-1" id="hex_style_input_border">{{ $inputBorder }}</div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Field Border Radius (px)</label>
                                            <input type="number" name="styles[input_radius]" id="style_input_radius" class="form-control bn-digit font-tabular" value="{{ $inputRadius }}" min="0" max="30" oninput="syncAppearanceLivePreview()">
                                        </div>
                                    </div>
                                </div>

                                {{-- Section D: Submit Button Styling --}}
                                <div class="p-3 border rounded bg-light bg-opacity-50 mb-3" style="border-radius: 6px !important;">
                                    <h6 class="fw-bold text-dark mb-2" style="font-size: 12.5px;">
                                        <i class="fa-solid fa-hand-pointer text-warning me-1"></i> Submit Button Style
                                    </h6>
                                    <div class="row g-2">
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">Button Background Color</label>
                                            <div class="d-flex gap-1 align-items-center">
                                                <input type="color" name="styles[btn_bg]" id="style_btn_bg" class="form-control color-picker-input" value="{{ $btnBg }}" style="width: 50%;" oninput="syncAppearanceLivePreview()">
                                                <div class="color-hex-tag flex-grow-1" id="hex_style_btn_bg">{{ $btnBg }}</div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">Button Text Color</label>
                                            <div class="d-flex gap-1 align-items-center">
                                                <input type="color" name="styles[btn_text]" id="style_btn_text" class="form-control color-picker-input" value="{{ $btnTextCol }}" style="width: 50%;" oninput="syncAppearanceLivePreview()">
                                                <div class="color-hex-tag flex-grow-1" id="hex_style_btn_text">{{ $btnTextCol }}</div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">Button Hover BG Color</label>
                                            <div class="d-flex gap-1 align-items-center">
                                                <input type="color" name="styles[btn_hover_bg]" id="style_btn_hover_bg" class="form-control color-picker-input" value="{{ $btnHoverBg }}" style="width: 50%;" oninput="syncAppearanceLivePreview()">
                                                <div class="color-hex-tag flex-grow-1" id="hex_style_btn_hover_bg">{{ $btnHoverBg }}</div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Button Border Radius (px)</label>
                                            <input type="number" name="styles[btn_radius]" id="style_btn_radius" class="form-control bn-digit font-tabular" value="{{ $btnRadius }}" min="0" max="40" oninput="syncAppearanceLivePreview()">
                                        </div>
                                    </div>
                                </div>

                                {{-- Section E: Custom CSS Overrides --}}
                                <div class="col-12">
                                    <label class="form-label">Custom CSS</label>
                                    <textarea name="custom_css" class="form-control" rows="3" placeholder="Write custom css">{{ $form->custom_css ?? '' }}</textarea>
                                    </div>

                                <div class="mt-3 pt-3 border-top d-flex justify-content-end">
                                    <button type="submit" class="btn col-12 btn-primary btn-compact shadow-xs fw-bold px-3">
                                        <i class="fa-solid fa-check-circle me-1"></i> Save Styles
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Interactive Real-time Appearance Preview --}}
                <div class="col-12 col-xl-5">
                    <div class="form-preview-card" style="position: sticky; top: 15px; border-radius: 6px;">
                        <div class="preview-header-area d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold text-dark mb-0 mt-1" style="font-size: 13px;">Live Preview</h6>
                            </div>
                        </div>

                        {{-- Live Interactive Box (Reactive to Left Panel Controls) --}}
                        <div id="appearanceSimulatorBox" style="background-color: {{ $formBg }}; border: 1px solid {{ $formBorder }}; border-radius: {{ $formRadius }}px; padding: {{ $formPadding }}px; transition: all 0.2s ease;">
                            <div class="text-center mb-2 {{ $currentLogoUrl ? 'd-block' : 'd-none' }}">
                                <img src="{{ $currentLogoUrl }}" alt="Logo" class="img-fluid border p-1 shadow-xs" style="max-height: 45px; border-radius: 5px;">
                            </div>
                            <div class="text-center mb-3">
                                <h5 class="fw-bold mb-1" id="appearanceSimTitle" style="color: {{ $labelColor }}; font-size: 15px;">{{ $form->name }}</h5>
                                @if(!empty($form->description))
                                    <small class="text-muted d-block" style="font-size: 11px;">{{ $form->description }}</small>
                                @endif
                            </div>

                            <div class="row g-2" id="appearanceFieldsList">
                                @forelse($form->fields as $f)
                                    <div class="col-{{ $f->column_width ?? 12 }} mb-2 appearance-field-wrap">
                                        <label class="form-label mb-1 appearance-sim-label" style="color: {{ $labelColor }}; font-size: 11px; display: {{ $showLabels ? 'block' : 'none' }};">
                                            {{ $f->label }}
                                            @if($f->is_required)<span class="text-danger">*</span>@endif
                                        </label>
                                        <input type="text" class="form-control appearance-sim-input" placeholder="{{ $f->placeholder ?: (!$showLabels ? $f->label : '') }}" style="background-color: {{ $inputBg }}; color: {{ $inputText }}; border: 1px solid {{ $inputBorder }}; border-radius: {{ $inputRadius }}px; font-size: 11px; min-height: 32px;" readonly>
                                    </div>
                                @empty
                                    <div class="col-12 text-center py-3 text-muted small">No fields added yet.</div>
                                @endforelse
                            </div>

                            <div class="mt-3" id="appearanceBtnRow" style="text-align: {{ $btnAlign === 'center' ? 'center' : ($btnAlign === 'right' ? 'right' : 'left') }};">
                                <button type="button" class="btn fw-bold" id="appearanceSimBtn" style="background-color: {{ $btnBg }}; color: {{ $btnTextCol }}; border-radius: {{ $btnRadius }}px; width: {{ $btnAlign === 'full' ? '100%' : 'auto' }}; font-size: 12px; padding: 6px 18px; border: 0; width:100%">
                                    {{ $btnText }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- TAB 5: ANTI-SPAM & SECURITY -->
        <div class="tab-pane fade" id="tab-security" role="tabpanel">
            <div class="studio-panel-card">
                <div class="studio-card-header d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-shield-halved me-1 text-danger"></i> Anti-Spam &amp; Rate Limiting Rules</span>
                    <span class="badge bg-light text-muted border" style="font-size: 9.5px; font-weight: 600;">SECURITY SHIELD</span>
                </div>

                <div class="studio-card-body">
                    <form method="POST" action="{{ route('admin.forms.update', $form) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="name" value="{{ $form->name }}">
                        <input type="hidden" name="slug" value="{{ $form->slug }}">

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Spam Protection</label>
                                <select name="captcha_type" class="form-select">
                                    <option value="honeypot" selected>Silent Honeypot Trap (Recommended - Zero user friction)</option>
                                    <option value="recaptcha_v3">Google reCAPTCHA v3 (Invisible)</option>
                                    <option value="cloudflare_turnstile">Cloudflare Turnstile</option>
                                    <option value="none">No Spam Protection</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Hourly IP Submission Limit</label>
                                <input type="number" name="rate_limit_per_ip" class="form-control bn-digit font-tabular" value="{{ $form->rate_limit_per_ip ?? 15 }}" min="1">
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="prevent_duplicates" id="chk_prevent_dups" value="1" checked>
                                    <label class="form-check-label fw-semibold" for="chk_prevent_dups">Block rapid duplicate submissions with identical payload</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-top d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3">
                                <i class="fa-solid fa-check-circle me-1"></i> Save Security Rules
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- TAB 6: WEBHOOKS & INTEGRATIONS -->
        <div class="tab-pane fade" id="tab-integrations" role="tabpanel">
            <div class="studio-panel-card">
                <div class="studio-card-header d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-network-wired me-1 text-success"></i> Webhook Relays &amp; Zapier Relay</span>
                    <span class="badge bg-light text-muted border" style="font-size: 9.5px; font-weight: 600;">API WEBHOOK</span>
                </div>

                <div class="studio-card-body">
                    <form method="POST" action="{{ route('admin.forms.update', $form) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="name" value="{{ $form->name }}">
                        <input type="hidden" name="slug" value="{{ $form->slug }}">

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Outgoing Webhook URL</label>
                                <input type="url" name="webhook_url" class="form-control" value="{{ $form->webhook_url ?? '' }}" placeholder="https://hooks.zapier.com/hooks/catch/...">
                                <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                    Every submission will post a JSON payload to this endpoint in real-time.
                                </small>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">HTTP Method</label>
                                <select name="webhook_method" class="form-select">
                                    <option value="POST" selected>POST (Application/JSON)</option>
                                    <option value="PUT">PUT</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Custom Authorization Header (Optional)</label>
                                <input type="text" name="webhook_auth_header" class="form-control" value="{{ $form->webhook_auth_header ?? '' }}" placeholder="Bearer YOUR_API_TOKEN">
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-top d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3">
                                <i class="fa-solid fa-check-circle me-1"></i> Save Integrations
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- MODAL 1: ADVANCED FIELD BUILDER -->
<div class="modal fade builder-modal-scope" id="fieldModal" tabindex="-1" style="z-index: 107000;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 8px !important;">
            <div class="modal-header border-bottom py-2.5 px-3 bg-light d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-1 mb-0" id="fieldModalTitle" style="font-size: 13.5px;">
                    <i class="fa-solid fa-plus text-primary"></i> <span>Add New Form Field</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <form id="fieldForm">
                <div class="modal-body p-3">
                    <input type="hidden" id="field_id">

                    <ul class="nav nav-pills mb-3 border-bottom pb-2 gap-2" id="fieldModalTabNav" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active py-1.5 px-3 fw-bold" id="fm-tab-basic-btn" data-bs-toggle="pill" data-bs-target="#fm-tab-basic" type="button" role="tab" style="font-size: 11.5px;">
                                1. General &amp; Type
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-1.5 px-3 fw-bold" id="fm-tab-rules-btn" data-bs-toggle="pill" data-bs-target="#fm-tab-rules" type="button" role="tab" style="font-size: 11.5px;">
                                2. Validation Rules
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-1.5 px-3 fw-bold" id="fm-tab-logic-btn" data-bs-toggle="pill" data-bs-target="#fm-tab-logic" type="button" role="tab" style="font-size: 11.5px;">
                                3. Conditional Logic
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="fieldModalTabContent">
                        <div class="tab-pane fade show active" id="fm-tab-basic" role="tabpanel">
                            <div class="row g-2">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Field Type *</label>
                                    <select id="field_type" name="type" class="form-select" required>
                                        <optgroup label="Standard Inputs">
                                            <option value="text">Text Input</option>
                                            <option value="email">Email</option>
                                            <option value="phone">Phone / Mobile</option>
                                            <option value="number">Number</option>
                                            <option value="textarea">Textarea (Multiline)</option>
                                        </optgroup>
                                        <optgroup label="Choice & Select">
                                            <option value="select">Dropdown Select</option>
                                            <option value="radio">Radio Buttons</option>
                                            <option value="checkbox">Single Checkbox</option>
                                        </optgroup>
                                        <optgroup label="File & Date">
                                            <option value="file">File Upload</option>
                                            <option value="date">Date Picker</option>
                                            <option value="time">Time Picker</option>
                                            <option value="url">Website URL</option>
                                        </optgroup>
                                        <optgroup label="Special & Hidden">
                                            <option value="hidden">Hidden Input</option>
                                        </optgroup>
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Field Label *</label>
                                    <input type="text" id="field_label" name="label" class="form-control" placeholder="Your Full Name" required onkeyup="autoGenerateFieldName(this.value)">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Database Field Key Name *</label>
                                    <input type="text" id="field_name" name="name" class="form-control" placeholder="full_name" required>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Placeholder Text</label>
                                    <input type="text" id="field_placeholder" name="placeholder" class="form-control" placeholder="Enter your name...">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Grid Width (Bootstrap Col)</label>
                                    <select id="field_width" name="column_width" class="form-select">
                                        <option value="12">100% (12 Columns Full Width)</option>
                                        <option value="6">50% (6 Columns Half Width)</option>
                                        <option value="4">33% (4 Columns 1/3rd)</option>
                                        <option value="3">25% (3 Columns 1/4th)</option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Custom Input Class (Optional)</label>
                                    <input type="text" id="field_css_class" name="css_class" class="form-control" placeholder="form-control-modern">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Custom Wrapper Class (Optional)</label>
                                    <input type="text" id="field_wrapper_class" name="wrapper_class" class="form-control" placeholder="mb-3 custom-group">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Default Value</label>
                                    <input type="text" id="field_default" name="default_value" class="form-control">
                                </div>

                                <div class="col-12" id="options-wrapper" style="display:none;">
                                    <label class="form-label">Options (One option per line: Label|value)</label>
                                    <textarea id="field_options" name="options" class="form-control" rows="3" placeholder="Option 1|option_1&#10;Option 2|option_2"></textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Help / Instruction Text</label>
                                    <input type="text" id="field_help" name="help_text" class="form-control" placeholder="Instruction text displayed right below the input">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="fm-tab-rules" role="tabpanel">
                            <div class="row g-2">
                                <div class="col-6 col-md-3">
                                    <label class="form-label">Min Length</label>
                                    <input type="number" id="field_min_length" name="min_length" class="form-control bn-digit font-tabular">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label">Max Length</label>
                                    <input type="number" id="field_max_length" name="max_length" class="form-control bn-digit font-tabular">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label">Min Number Value</label>
                                    <input type="number" step="any" id="field_min_value" name="min_value" class="form-control bn-digit font-tabular">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label">Max Number Value</label>
                                    <input type="number" step="any" id="field_max_value" name="max_value" class="form-control bn-digit font-tabular">
                                </div>

                                <div class="col-12 mt-2">
                                    <div class="d-flex flex-wrap gap-3 p-2.5 border bg-light" style="border-radius: 6px;">
                                        <div class="form-check">
                                            <input type="checkbox" id="field_required" name="is_required" value="1" class="form-check-input">
                                            <label class="form-check-label fw-semibold" for="field_required">Mandatory Required Field</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" id="field_active" name="is_active" value="1" class="form-check-input" checked>
                                            <label class="form-check-label fw-semibold" for="field_active">Active &amp; Rendered</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="fm-tab-logic" role="tabpanel">
                            <div class="p-3 bg-light border text-muted small" style="border-radius: 6px;">
                                <div class="d-flex align-items-center gap-1 text-primary fw-bold mb-2">
                                    <i class="fa-solid fa-code-branch"></i> Conditional Field Display Logic
                                </div>
                                <p class="mb-2" style="font-size: 11px;">Show or hide this field based on applicant responses in previous fields.</p>
                                <div class="row g-2">
                                    <div class="col-12 col-md-4">
                                        <select class="form-select" id="field_cond_action">
                                            <option value="show">Show this field IF</option>
                                            <option value="hide">Hide this field IF</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <input type="text" class="form-control" id="field_cond_target" placeholder="Target Field Name">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <input type="text" class="form-control" id="field_cond_value" placeholder="Matches Value">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3" id="saveFieldBtn">
                        <i class="fa-solid fa-check-circle me-1"></i> Save Field
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: LIGHTBOX ZOOM PREVIEW -->
<div class="modal fade builder-modal-scope" id="formEditLogoZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2 bg-white" style="border-radius: 8px !important;">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12px;" id="formEditLogoZoomTitle">
                    <i class="fa-solid fa-image text-primary me-1"></i> Form Header Preview
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="formEditLogoZoomImgTag" src="" alt="Logo Preview" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<!-- MODAL 3: FULLSCREEN LIVE PREVIEW SIMULATOR -->
<div class="modal fade builder-modal-scope" id="studioFullscreenPreviewModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 8px !important;">
            <div class="modal-header border-bottom py-2.5 px-3 bg-light d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark mb-0" style="font-size: 13.5px;">
                    <i class="fa-solid fa-desktop text-primary me-1"></i> Live Form View
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 bg-light">
                <div class="bg-white border p-3 shadow-xs mx-auto" style="max-width: 680px; border-radius: 6px;">
                    <div class="text-center mb-3">
                        <h5 class="fw-bold text-dark mb-1">{{ $form->name }}</h5>
                        <p class="text-muted small mb-0">{{ $form->description }}</p>
                    </div>
                    <div id="fullscreenModalRenderZone"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 4: MINIMAL NOTICE MODAL -->
<div class="modal fade builder-modal-scope" id="studioNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 109000;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content minimal-modal-card">
            <h6 class="minimal-modal-title" id="studioNoticeTitle">Notification</h6>
            <p class="minimal-modal-subtext" id="studioNoticeMessage" style="margin-bottom: 16px !important;"></p>
            <div class="d-flex align-items-center">
                <button type="button" class="btn-minimal-action" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 5: MINIMAL CONFIRMATION MODAL (Exact Match with Screenshot) -->
<div class="modal fade builder-modal-scope" id="studioConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 109100;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content minimal-modal-card">
            <h6 class="minimal-modal-title" id="studioConfirmTitle">Are you sure you wanna delete this?</h6>
            <p class="minimal-modal-subtext" id="studioConfirmMessage">
                This item will be removed from your form definition.
            </p>
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn-minimal-delete" id="btnExecuteStudioConfirmAction">Delete</button>
                <button type="button" class="btn-minimal-cancel" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>

<script>
let studioPendingConfirmCallback = null;

function showStudioModalNotice(message, title = 'Notice') {
    const titleEl = document.getElementById('studioNoticeTitle');
    const msgEl   = document.getElementById('studioNoticeMessage');

    if (titleEl) titleEl.innerText = title;
    if (msgEl) msgEl.innerText = message;

    const modalEl = document.getElementById('studioNoticeModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

function showStudioModalConfirm(title, message, callback, btnClass = 'btn-minimal-delete', btnText = 'Delete') {
    document.getElementById('studioConfirmTitle').innerText = title;
    document.getElementById('studioConfirmMessage').innerHTML = message;
    
    const confirmBtn = document.getElementById('btnExecuteStudioConfirmAction');
    confirmBtn.className = btnClass;
    confirmBtn.innerText = btnText;

    studioPendingConfirmCallback = callback;

    const modalEl = document.getElementById('studioConfirmModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

document.getElementById('btnExecuteStudioConfirmAction')?.addEventListener('click', function() {
    const modalEl = document.getElementById('studioConfirmModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getInstance(modalEl)?.hide();
    }
    if (typeof studioPendingConfirmCallback === 'function') {
        studioPendingConfirmCallback();
        studioPendingConfirmCallback = null;
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const formId = {{ $form->id }};
    const fieldModalEl = document.getElementById('fieldModal');
    const fieldModal = new bootstrap.Modal(fieldModalEl);
    const fieldForm = document.getElementById('fieldForm');
    const fieldList = document.getElementById('field-list');
    let editMode = false;
    let editingFieldId = null;

    // Direct Native Tab Switcher Execution
    document.querySelectorAll('#studioMainTabNav .studio-nav-pill').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const targetSelector = this.getAttribute('data-bs-target');
            if (!targetSelector) return;

            document.querySelectorAll('#studioMainTabNav .studio-nav-pill').forEach(b => {
                b.classList.remove('active');
                b.setAttribute('aria-selected', 'false');
            });

            document.querySelectorAll('#studioMainTabContent .tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active');
            });

            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');

            const targetPane = document.querySelector(targetSelector);
            if (targetPane) {
                targetPane.classList.add('show', 'active');
            }
        });
    });

    // 1. Sortable Drag & Drop Reordering Engine
    if (fieldList) {
        new Sortable(fieldList, {
            animation: 180,
            handle: '.drag-grip-icon',
            filter: '#empty-fields-placeholder',
            onEnd: function () {
                const ids = [];
                fieldList.querySelectorAll('.field-draggable-item').forEach(function (item) {
                    ids.push(item.dataset.id);
                });
                if (!ids.length) return;

                fetch("{{ route('admin.forms.fields.reorder', $form) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ fields: ids })
                })
                .then(r => r.json())
                .then(() => {
                    showStudioModalNotice('Fields sequence updated successfully.', 'Reordered Successfully');
                });
            }
        });
    }

    // 2. Dynamic Field Type Option Display Toggle
    const fieldType = document.getElementById('field_type');
    function toggleOptionsDisplay() {
        const type = fieldType.value;
        const optionsWrapper = document.getElementById('options-wrapper');
        const optionTypes = ['select', 'radio', 'checkbox'];
        optionsWrapper.style.display = optionTypes.includes(type) ? 'block' : 'none';
    }
    fieldType?.addEventListener('change', toggleOptionsDisplay);

    // 3. Reset Modal for Adding Field
    window.prepareAddFieldModal = function () {
        fieldForm.reset();
        editMode = false;
        editingFieldId = null;
        document.getElementById('field_id').value = '';
        document.getElementById('field_width').value = '12';
        document.getElementById('field_active').checked = true;
        document.getElementById('field_required').checked = false;
        document.getElementById('fieldModalTitle').innerHTML = '<i class="fa-solid fa-plus me-1 text-primary"></i><span>Add New Form Field</span>';
        document.getElementById('saveFieldBtn').innerHTML = '<i class="fa-solid fa-check-circle me-1"></i>Save Field';
        bootstrap.Tab.getOrCreateInstance(document.getElementById('fm-tab-basic-btn')).show();
        toggleOptionsDisplay();
    };

    // 4. Auto-generate Database Field Name
    window.autoGenerateFieldName = function (label) {
        if (!editMode) {
            const nameInput = document.getElementById('field_name');
            nameInput.value = label.toLowerCase().trim().replace(/[^a-z0-9]/g, '_').replace(/_+/g, '_');
        }
    };

    // 5. Edit Field Population
    document.querySelectorAll('.edit-field').forEach(function (button) {
        button.addEventListener('click', function () {
            const field = JSON.parse(this.dataset.field);
            editMode = true;
            editingFieldId = field.id;

            document.getElementById('fieldModalTitle').innerHTML = '<i class="fa-solid fa-pen-to-square me-1 text-primary"></i><span>Edit Form Field</span>';
            document.getElementById('saveFieldBtn').innerHTML = '<i class="fa-solid fa-check-circle me-1"></i>Update Field';

            document.getElementById('field_id').value = field.id;
            document.getElementById('field_type').value = field.type;
            document.getElementById('field_label').value = field.label ?? '';
            document.getElementById('field_name').value = field.name ?? '';
            document.getElementById('field_placeholder').value = field.placeholder ?? '';
            document.getElementById('field_default').value = field.default_value ?? '';
            document.getElementById('field_help').value = field.help_text ?? '';
            document.getElementById('field_width').value = field.column_width ?? 12;
            document.getElementById('field_css_class').value = field.css_class ?? '';
            document.getElementById('field_wrapper_class').value = field.wrapper_class ?? '';
            document.getElementById('field_min_length').value = field.min_length ?? '';
            document.getElementById('field_max_length').value = field.max_length ?? '';
            document.getElementById('field_min_value').value = field.min_value ?? '';
            document.getElementById('field_max_value').value = field.max_value ?? '';
            document.getElementById('field_required').checked = !!field.is_required;
            document.getElementById('field_active').checked = !!field.is_active;

            let optionText = '';
            if (Array.isArray(field.options)) {
                optionText = field.options.map(o => (o.label || o) + '|' + (o.value || o)).join('\n');
            }
            document.getElementById('field_options').value = optionText;

            bootstrap.Tab.getOrCreateInstance(document.getElementById('fm-tab-basic-btn')).show();
            toggleOptionsDisplay();
            fieldModal.show();
        });
    });

    // 6. Save / Update Field via AJAX
    fieldForm?.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(fieldForm);
        formData.append('is_required', document.getElementById('field_required').checked ? 1 : 0);
        formData.append('is_active', document.getElementById('field_active').checked ? 1 : 0);

        let url = "{{ route('admin.forms.fields.store', $form) }}";
        if (editMode) {
            url = "{{ url('/admin/forms') }}/" + formId + "/fields/" + editingFieldId;
            formData.append('_method', 'PUT');
        }

        const saveBtn = document.getElementById('saveFieldBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...';

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Validation failed.');
            return data;
        })
        .then(data => {
            if (data.success) {
                fieldModal.hide();
                showStudioModalNotice('Field saved successfully.', 'Success');
                setTimeout(() => location.reload(), 800);
            }
        })
        .catch(err => {
            showStudioModalNotice(err.message, 'Validation Error');
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fa-solid fa-check-circle me-1"></i>Save Field';
        });
    });

    // 7. Delete Field Handler (Minimal Modal)
    document.querySelectorAll('.delete-field').forEach(function (button) {
        button.addEventListener('click', function () {
            const fieldId = this.dataset.id;
            showStudioModalConfirm(
                'Are you sure you wanna delete this?',
                'This field will be permanently removed from your form definition.',
                function () {
                    fetch("{{ url('/admin/forms') }}/" + formId + "/fields/" + fieldId, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Accept': 'application/json'
                        },
                        body: '_method=DELETE'
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showStudioModalNotice('Field deleted successfully.', 'Deleted');
                            setTimeout(() => location.reload(), 800);
                        }
                    });
                },
                'btn-minimal-delete',
                'Delete'
            );
        });
    });

    // 8. Clone Field Helper
    window.cloneExistingField = function (fieldId) {
        const itemCard = document.getElementById('field-card-' + fieldId);
        if (!itemCard) return;

        fetch("{{ url('/admin/forms') }}/" + formId + "/fields/" + fieldId + "/clone", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            showStudioModalNotice('Field duplicated successfully.', 'Field Cloned');
            setTimeout(() => location.reload(), 800);
        })
        .catch(() => location.reload());
    };
});

function copyStudioShortcode(code) {
    navigator.clipboard.writeText(code).then(() => {
        showStudioModalNotice('Embed shortcode ' + code + ' copied to clipboard successfully!', 'Copied Successfully');
    }).catch(() => {
        showStudioModalNotice('Could not copy automatically. Please copy manually.', 'Notice');
    });
}

function extractMediaItem(mediaInput) {
    if (!mediaInput) return null;
    const item = Array.isArray(mediaInput) ? mediaInput[0] : mediaInput;
    if (!item || typeof item !== 'object') return null;
    const rawPath = item.file_path || item.path || item.url || '';
    const rawUrl  = item.url || item.file_path || item.path || '';
    if (!rawPath || rawPath === 'undefined') return null;
    return { path: String(rawPath).trim(), url: String(rawUrl).trim() };
}

function chooseFormEditLogoMedia() {
    if (typeof window.openMediaPicker === 'function') {
        window.openMediaPicker({
            multiple: false,
            onSelect: function(media) {
                const picked = extractMediaItem(media);
                if (picked && picked.path) {
                    document.getElementById('edit_form_logo_media').value = picked.path;
                    const wrap = document.getElementById('edit_form_logo_preview_wrap');
                    const img  = document.getElementById('edit_form_logo_preview_img');
                    const txt  = document.getElementById('edit_form_logo_preview_txt');
                    const simWrap = document.getElementById('previewSimulatorLogoBox');
                    const simImg  = document.getElementById('previewSimulatorLogoImg');

                    if (img) img.src = picked.url;
                    if (txt) txt.innerText = picked.path.split('/').pop() || picked.path;
                    if (wrap) {
                        wrap.classList.remove('d-none');
                        wrap.classList.add('d-flex');
                    }
                    if (simImg) simImg.src = picked.url;
                    if (simWrap) {
                        simWrap.classList.remove('d-none');
                        simWrap.classList.add('d-block');
                    }
                    showStudioModalNotice('Brand asset attached from Global Media Library.', 'Media Attached');
                }
            }
        });
    } else {
        showStudioModalNotice('Global Media Library is loading, please try again.', 'Notice');
    }
}

function clearFormEditLogoMedia() {
    document.getElementById('edit_form_logo_media').value = '';
    const wrap = document.getElementById('edit_form_logo_preview_wrap');
    const simWrap = document.getElementById('previewSimulatorLogoBox');
    if (wrap) {
        wrap.classList.add('d-none');
        wrap.classList.remove('d-flex');
    }
    if (simWrap) {
        simWrap.classList.add('d-none');
        simWrap.classList.remove('d-block');
    }
}

function zoomFormEditLogoPreview() {
    const img = document.getElementById('edit_form_logo_preview_img');
    const zoomImgTag = document.getElementById('formEditLogoZoomImgTag');
    if (img && img.src && zoomImgTag) {
        zoomImgTag.src = img.src;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('formEditLogoZoomModal')).show();
    }
}

function openStudioFullscreenPreview() {
    const renderZone = document.getElementById('fullscreenModalRenderZone');
    const liveSim = document.getElementById('liveFormFieldsSimulator');
    if (renderZone && liveSim) {
        renderZone.innerHTML = liveSim.innerHTML;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('studioFullscreenPreviewModal')).show();
    }
}

function refreshFieldListOrder() {
    location.reload();
}

// Real-time Reactive Style Simulator Synchronizer
function syncAppearanceLivePreview() {
    const formBg       = document.getElementById('style_form_bg')?.value || '#ffffff';
    const formBorder   = document.getElementById('style_border_color')?.value || '#e2e8f0';
    const formRadius   = document.getElementById('style_border_radius')?.value || 8;
    const formPadding  = document.getElementById('style_form_padding')?.value || 24;
    const labelColor   = document.getElementById('style_label_color')?.value || '#1e293b';
    const inputBg      = document.getElementById('style_input_bg')?.value || '#ffffff';
    const inputText    = document.getElementById('style_input_text')?.value || '#0f172a';
    const inputBorder  = document.getElementById('style_input_border')?.value || '#cbd5e1';
    const inputRadius  = document.getElementById('style_input_radius')?.value || 6;
    const btnBg        = document.getElementById('style_btn_bg')?.value || '#2563eb';
    const btnTextCol   = document.getElementById('style_btn_text')?.value || '#ffffff';
    const btnRadius    = document.getElementById('style_btn_radius')?.value || 6;
    const btnAlign     = document.getElementById('style_button_align')?.value || 'left';
    const btnText      = document.getElementById('style_button_text')?.value || 'Submit Request';
    const showLabels   = document.getElementById('chk_edit_show_labels')?.checked;

    // 1. Box Container
    const simBox = document.getElementById('appearanceSimulatorBox');
    if (simBox) {
        simBox.style.backgroundColor = formBg;
        simBox.style.borderColor     = formBorder;
        simBox.style.borderRadius   = formRadius + 'px';
        simBox.style.padding        = formPadding + 'px';
    }

    // 2. Title & Labels
    const simTitle = document.getElementById('appearanceSimTitle');
    if (simTitle) simTitle.style.color = labelColor;

    document.querySelectorAll('.appearance-sim-label').forEach(el => {
        el.style.color   = labelColor;
        el.style.display = showLabels ? 'block' : 'none';
    });

    // 3. Inputs
    document.querySelectorAll('.appearance-sim-input').forEach(el => {
        el.style.backgroundColor = inputBg;
        el.style.color           = inputText;
        el.style.borderColor     = inputBorder;
        el.style.borderRadius   = inputRadius + 'px';
    });

    // 4. Button & Alignment
    const simBtn = document.getElementById('appearanceSimBtn');
    const btnRow = document.getElementById('appearanceBtnRow');
    if (simBtn) {
        simBtn.style.backgroundColor = btnBg;
        simBtn.style.color           = btnTextCol;
        simBtn.style.borderRadius   = btnRadius + 'px';
        simBtn.innerText             = btnText;
        simBtn.style.width           = (btnAlign === 'full') ? '100%' : 'auto';
    }
    if (btnRow) {
        btnRow.style.textAlign = (btnAlign === 'center') ? 'center' : ((btnAlign === 'right') ? 'right' : 'left');
    }
}

// Color Picker to Hex Tag Synchronizer
document.querySelectorAll('.color-picker-input').forEach(input => {
    input.addEventListener('input', function() {
        const hexTag = this.parentElement.querySelector('.color-hex-tag');
        if (hexTag) hexTag.innerText = this.value;
        syncAppearanceLivePreview();
    });
});
</script>
@endsection