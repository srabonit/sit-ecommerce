@extends('layouts.admin')

@section('title', 'Footer Settings - ' . config('app.name'))

@php
    // Resolve footer images from the Global Media Library
    $resolveFooterImg = function($path, $fallback = '') {
        if (empty($path) || $path === 'undefined' || $path === 'null') return $fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) return show_image($path, $fallback);
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset(ltrim(str_replace(['public_html/', 'public/'], '', $path), '/'));
    };
@endphp

@section('admin_content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
    #footerBuilderRoot,
    .footer-modal-scope {
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        letter-spacing: -0.01em;
        color: #1e293b;
    }

    #footerBuilderRoot i,
    #footerBuilderRoot .fa,
    #footerBuilderRoot .fas,
    #footerBuilderRoot .far,
    #footerBuilderRoot .fab,
    #footerBuilderRoot .fa-solid,
    #footerBuilderRoot .fa-regular,
    .footer-modal-scope i,
    .footer-modal-scope .fa,
    .footer-modal-scope .fa-solid,
    .footer-modal-scope .fa-regular {
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome" !important;
        font-style: normal;
        font-weight: 900;
        line-height: 1;
        letter-spacing: normal !important;
        display: inline-block;
    }

    #footerBuilderRoot .far,
    .footer-modal-scope .far {
        font-weight: 400 !important;
    }

    #footerBuilderRoot .btn,
    #footerBuilderRoot .btn-sm,
    #footerBuilderRoot button,
    #footerBuilderRoot .form-control,
    #footerBuilderRoot .form-select,
    #footerBuilderRoot .input-group-text,
    #footerBuilderRoot .badge,
    #footerBuilderRoot .alert,
    #footerBuilderRoot .card,
    #footerBuilderRoot .builder-header-card,
    #footerBuilderRoot .builder-main-card,
    #footerBuilderRoot .footer-section-card,
    #footerBuilderRoot .custom-tab-btn,
    #footerBuilderRoot .builder-tabs-list,
    #footerBuilderRoot .dynamic-row-box,
    #footerBuilderRoot .payment-badge-row,
    #footerBuilderRoot .color-sync-card,
    #footerBuilderRoot .proof-media-surface,
    #footerBuilderRoot .proof-thumb-box,
    #footerBuilderRoot .collapse-toggle-btn,
    .footer-modal-scope .btn,
    .footer-modal-scope .form-control,
    .footer-modal-scope .badge {
        border-radius: 5px !important;
    }

    #footerBuilderRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #footerBuilderRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

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

    .btn-outline-primary {
        background-color: #ffffff !important;
        border-color: #2563eb !important;
        color: #2563eb !important;
    }

    .btn-outline-primary:hover {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
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

    .btn-row-delete-sq {
        width: 30px !important;
        height: 30px !important;
        min-width: 30px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 11px !important;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #ef4444;
        border-radius: 5px !important;
        transition: all 0.15s ease;
        cursor: pointer;
    }

    .btn-row-delete-sq:hover {
        background: #ef4444;
        color: #ffffff;
        border-color: #ef4444;
    }

    .builder-header-card {
        background: #ffffff;
        color: #0f172a;
        padding: 14px 18px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }

    .builder-main-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        padding: 18px;
        margin-bottom: 16px;
        border-radius: 6px !important;
    }

    .builder-nav-container {
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
        padding-bottom: 4px;
        -webkit-overflow-scrolling: touch;
    }

    .builder-nav-container::-webkit-scrollbar {
        display: none;
    }

    .builder-tabs-list {
        display: inline-flex;
        gap: 4px;
        background: #f8fafc;
        padding: 4px;
        border: 1px solid #e2e8f0;
        border-radius: 6px !important;
    }

    .custom-tab-btn {
        border: 1px solid transparent;
        background: transparent;
        color: #475569;
        font-weight: 600;
        font-size: 11.5px;
        padding: 6px 14px;
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 5px !important;
    }

    .custom-tab-btn:hover {
        color: #0f172a;
        background: #ffffff;
        border-color: #cbd5e1;
    }

    .custom-tab-btn.active {
        background-color: #2563eb !important;
        color: #ffffff !important;
        border-color: #2563eb !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25);
    }

    .custom-tab-pane {
        display: none;
    }

    .custom-tab-pane.active {
        display: block;
        animation: fadeInTab 0.15s ease-in-out;
    }

    @keyframes fadeInTab {
        from { opacity: 0; transform: translateY(2px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .footer-section-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
        margin-bottom: 14px;
        overflow: hidden;
        border-radius: 6px !important;
    }

    .footer-section-header {
        padding: 11px 16px;
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
    }

    .section-title-text {
        font-size: 12.5px;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 6px;
    }

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
        cursor: pointer;
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

    .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }

    #footerBuilderRoot .form-control,
    #footerBuilderRoot .form-select,
    .footer-modal-scope .form-control,
    .footer-modal-scope .form-select {
        min-height: 34px;
        font-size: 12px;
        font-weight: 500;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    #footerBuilderRoot .form-control:focus,
    #footerBuilderRoot .form-select:focus,
    .footer-modal-scope .form-control:focus,
    .footer-modal-scope .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    textarea.form-control {
        min-height: 80px;
        line-height: 1.45;
    }

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
        width: 38px;
        height: 38px;
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

    .dynamic-row-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 6px !important;
        padding: 6px 10px;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: border-color 0.15s ease;
    }

    .dynamic-row-box:hover {
        border-color: #cbd5e1;
    }

    .dynamic-row-box input {
        border-color: #cbd5e1;
        font-size: 12px;
    }

    .payment-badge-row {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 6px !important;
        padding: 8px 10px;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        transition: border-color 0.15s ease;
    }

    .payment-badge-row:hover {
        border-color: #cbd5e1;
    }

    .color-sync-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 5px !important;
        padding: 4px 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .color-picker-input {
        width: 32px;
        height: 28px;
        border: none;
        padding: 0;
        background: transparent;
        cursor: pointer;
        flex-shrink: 0;
    }

    .color-text-input {
        border: none;
        border-left: 1px solid #e2e8f0;
        padding: 2px 8px;
        font-size: 11.5px;
        outline: none;
        width: 100%;
        background: transparent;
        color: #0f172a;
        font-weight: 600;
    }

    .form-switch .form-check-input {
        width: 36px;
        height: 18px;
        cursor: pointer;
        border-color: #cbd5e1;
        border-radius: 20px !important;
    }

    .form-switch .form-check-input:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }

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

    .btn-minimal-action {
        background-color: #2563eb !important;
        border: none !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        padding: 7px 22px !important;
        border-radius: 7px !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.28) !important;
        transition: all 0.15s ease !important;
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .btn-minimal-action:hover {
        background-color: #1d4ed8 !important;
        box-shadow: 0 6px 14px rgba(37, 99, 235, 0.38) !important;
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

    @media (max-width: 767.98px) {
        .builder-header-card { padding: 12px 14px; }
        .builder-main-card { padding: 12px !important; }

        .dynamic-row-box {
            flex-direction: column;
            align-items: stretch;
            gap: 6px;
            padding: 8px;
        }

        .payment-badge-row {
            flex-direction: column;
            align-items: stretch;
            gap: 8px;
            padding: 8px;
        }

        .payment-badge-row .d-flex.align-items-center.justify-content-between {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: space-between !important;
            width: 100% !important;
        }

        .btn-row-delete-sq {
            width: 100% !important;
            height: 32px !important;
        }

        .minimal-modal-card {
            padding: 20px 20px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="footerBuilderRoot">

    <!-- Header -->
    <div class="builder-header-card d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-uppercase" style="font-size: 9.5px; font-weight: 700;">
                    <i class="fas fa-layer-group me-1"></i> Theme Settings
                </span>
                <span class="badge bg-light text-secondary border" style="font-size: 9.5px; font-weight: 600;">
                    Footer Builder
                </span>
            </div>

            <h5 class="fw-bold mb-0 d-flex align-items-center gap-2 text-dark" style="font-size: 18px;">
                <i class="fas fa-layer-group text-primary"></i>
                <span>Footer Builder</span>
            </h5>

            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                Manage your footer, brand details, links, payments and layout.
            </small>
        </div>

        <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold px-4" onclick="document.getElementById('footerBuilderForm').submit()" style="min-height: 36px; font-size: 12px;">
            <i class="fas fa-check-circle me-1"></i> Save Settings
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-xs py-2 px-3 mb-3 small d-flex align-items-center justify-content-between" style="border-radius: 5px;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form id="footerBuilderForm" action="{{ route('admin.theme.setting.footer.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <input type="hidden" name="remove_footer_logo" id="hiddenRemoveFooterLogo" value="0">
        <input type="hidden" name="footer_logo_media" id="footerLogoMediaInput" value="">

        <div class="builder-main-card">

            <!-- Navigation Tabs -->
            <div class="builder-nav-container mb-3">
                <div class="builder-tabs-list">
                    <button type="button" class="custom-tab-btn active" onclick="switchBuilderTab('tab-brand', this)">
                        <i class="fas fa-id-card"></i> Brand
                    </button>

                    <button type="button" class="custom-tab-btn" onclick="switchBuilderTab('tab-menus', this)">
                        <i class="fas fa-link"></i> Menus &amp; Links
                    </button>

                    <button type="button" class="custom-tab-btn" onclick="switchBuilderTab('tab-payments', this)">
                        <i class="fas fa-credit-card"></i> Payments &amp; Social
                    </button>

                    <button type="button" class="custom-tab-btn" onclick="switchBuilderTab('tab-styling', this)">
                        <i class="fas fa-paint-brush"></i> Style &amp; Layout
                    </button>
                </div>
            </div>

            <!-- TAB 1: BRAND -->
            <div class="custom-tab-pane active" id="tab-brand">
                <div class="row g-3">

                    <!-- Brand Logo -->
                    <div class="col-12 col-lg-5">
                        <div class="footer-section-card h-100">

                            <div class="footer-section-header d-flex justify-content-between align-items-center">
                                <div class="section-title-text">
                                    <i class="fas fa-image text-primary"></i>
                                    <span>Brand Logo</span>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <div class="form-check form-switch m-0 ps-0" title="Show Brand Logo">
                                        <input class="form-check-input ms-0" type="checkbox" name="footer_show_logo" value="1" {{ ($footerSettings['footer_show_logo'] ?? '1') == '1' ? 'checked' : '' }}>
                                    </div>

                                    <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseBrandLogo" aria-expanded="true" aria-controls="collapseBrandLogo" title="Toggle Section">
                                        <i class="fas fa-chevron-down collapse-icon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="collapse show" id="collapseBrandLogo">
                                <div class="p-3">

                                    <div class="proof-media-surface mb-2">
                                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                            <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" onclick="chooseFooterLogoMedia()">
                                                <i class="fas fa-photo-film me-1"></i> Media Library
                                            </button>

                                            <span class="small text-muted" style="font-size: 11px;">Or upload:</span>
                                        </div>

                                        <input type="file" name="footer_logo" id="footerLogoInput" class="form-control mb-2" accept="image/*" onchange="previewLogo(this)">

                                        @php
                                            $fLogoSrc = !empty($footerSettings['footer_logo']) ? $resolveFooterImg($footerSettings['footer_logo']) : null;
                                            $logoWidth = $footerSettings['footer_logo_width'] ?? '140';
                                            $logoHeight = $footerSettings['footer_logo_height'] ?? '40';
                                            $logoMobileWidth = $footerSettings['footer_logo_mobile_width'] ?? '110';
                                            $logoMobileHeight = $footerSettings['footer_logo_mobile_height'] ?? '36';
                                        @endphp

                                        <div id="logoPreviewWrapper" class="bg-white border p-2 {{ empty($fLogoSrc) ? 'd-none' : 'd-flex' }} align-items-center gap-2" style="border-radius: 5px;">

                                            <div class="proof-thumb-box shadow-xs" onclick="zoomThemeMedia(document.getElementById('logoPreviewImg').src, 'Footer Logo')" title="Preview">
                                                <img id="logoPreviewImg" src="{{ $fLogoSrc ?? '' }}" alt="Logo" onerror="this.src='https://placehold.co/120x40?text=Logo';">

                                                <div class="proof-thumb-hover-overlay">
                                                    <i class="fas fa-search-plus text-white" style="font-size: 9px;"></i>
                                                </div>
                                            </div>

                                            <div class="min-w-0 flex-grow-1">
                                                <span class="badge bg-success-subtle text-success border border-success-subtle text-uppercase" style="font-size: 9px; font-weight: 600;">
                                                    <i class="fas fa-check-circle me-1"></i> Active
                                                </span>

                                                <small class="text-muted d-block text-truncate" style="font-size: 10px;">Brand Logo</small>
                                            </div>

                                            <button type="button" class="btn btn-outline-danger btn-compact py-0 px-2" style="height: 24px; font-size: 10px !important;" onclick="openLogoDeleteModal()" title="Remove Logo">
                                                <i class="fas fa-times me-1"></i> Clear
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Logo Dimensions -->
                                    <div class="row g-2 pt-2 border-top">
                                        <div class="col-6">
                                            <label class="form-label">Desktop Width (px)</label>
                                            <input type="number" name="footer_logo_width" class="form-control bn-digit font-tabular" value="{{ $logoWidth }}">
                                        </div>

                                        <div class="col-6">
                                            <label class="form-label">Desktop Height (px)</label>
                                            <input type="number" name="footer_logo_height" class="form-control bn-digit font-tabular" value="{{ $logoHeight }}">
                                        </div>

                                        <div class="col-6">
                                            <label class="form-label">Mobile Width (px)</label>
                                            <input type="number" name="footer_logo_mobile_width" class="form-control bn-digit font-tabular" value="{{ $logoMobileWidth }}">
                                        </div>

                                        <div class="col-6">
                                            <label class="form-label">Mobile Height (px)</label>
                                            <input type="number" name="footer_logo_mobile_height" class="form-control bn-digit font-tabular" value="{{ $logoMobileHeight }}">
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Brand Details -->
                    <div class="col-12 col-lg-7">
                        <div class="footer-section-card h-100">

                            <div class="footer-section-header d-flex justify-content-between align-items-center">
                                <div class="section-title-text">
                                    <i class="fas fa-store text-primary"></i>
                                    <span>Brand Details</span>
                                </div>

                                <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseBrandIdentity" aria-expanded="true" aria-controls="collapseBrandIdentity" title="Toggle Section">
                                    <i class="fas fa-chevron-down collapse-icon"></i>
                                </button>
                            </div>

                            <div class="collapse show" id="collapseBrandIdentity">
                                <div class="p-3">
                                    <div class="row g-2">

                                        <div class="col-12 col-sm-6">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="form-label m-0">Brand Name</label>

                                                <div class="form-check form-switch m-0 ps-0">
                                                    <input class="form-check-input ms-0" type="checkbox" name="footer_show_brand_title" value="1" {{ ($footerSettings['footer_show_brand_title'] ?? '1') == '1' ? 'checked' : '' }}>
                                                </div>
                                            </div>

                                            <input type="text" name="footer_brand_title" class="form-control" value="{{ $footerSettings['footer_brand_title'] ?? config('app.name') }}" placeholder="Brand Name">
                                        </div>

                                        <div class="col-12 col-sm-6">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="form-label m-0">Tagline</label>

                                                <div class="form-check form-switch m-0 ps-0">
                                                    <input class="form-check-input ms-0" type="checkbox" name="footer_show_brand_tagline" value="1" {{ ($footerSettings['footer_show_brand_tagline'] ?? '1') == '1' ? 'checked' : '' }}>
                                                </div>
                                            </div>

                                            <input type="text" name="footer_brand_tagline" class="form-control" value="{{ $footerSettings['footer_brand_tagline'] ?? '' }}" placeholder="Tagline">
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">About Store</label>
                                            <textarea name="footer_about_text" class="form-control" rows="2" placeholder="Short store description...">{{ $footerSettings['footer_about_text'] ?? '' }}</textarea>
                                        </div>

                                        <div class="col-12 col-sm-6">
                                            <label class="form-label">Phone</label>

                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light text-muted">
                                                    <i class="fas fa-phone-alt"></i>
                                                </span>

                                                <input type="text" name="footer_phone" class="form-control bn-digit font-tabular" value="{{ $footerSettings['footer_phone'] ?? '' }}" placeholder="Phone Number">
                                            </div>
                                        </div>

                                        <div class="col-12 col-sm-6">
                                            <label class="form-label">Email</label>

                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light text-muted">
                                                    <i class="fas fa-envelope"></i>
                                                </span>

                                                <input type="text" name="footer_email" class="form-control" value="{{ $footerSettings['footer_email'] ?? '' }}" placeholder="Email Address">
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- TAB 2: MENUS & LINKS -->
            <div class="custom-tab-pane" id="tab-menus">
                <div class="row g-3">

                    <!-- Quick Links -->
                    <div class="col-12 col-lg-6">
                        <div class="footer-section-card">

                            <div class="footer-section-header d-flex justify-content-between align-items-center">
                                <div class="section-title-text">
                                    <i class="fas fa-link text-warning"></i>
                                    <span>Quick Links</span>
                                </div>

                                <div class="d-flex align-items-center gap-1">
                                    <button type="button" class="btn btn-primary btn-compact py-0 px-2 shadow-xs" style="height: 26px; font-size: 10.5px !important;" data-bs-toggle="modal" data-bs-target="#addQuickLinkModal">
                                        <i class="fas fa-plus me-1"></i> Add Link
                                    </button>

                                    <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseQuickLinks" aria-expanded="true" aria-controls="collapseQuickLinks" title="Toggle Section">
                                        <i class="fas fa-chevron-down collapse-icon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="collapse show" id="collapseQuickLinks">
                                <div class="p-3">

                                    <div class="mb-2">
                                        <label class="form-label">Column Title</label>
                                        <input type="text" name="footer_col2_title" class="form-control" value="{{ $footerSettings['footer_col2_title'] ?? 'Quick Links' }}" placeholder="Quick Links">
                                    </div>

                                    <div id="quickLinksContainer">
                                        @foreach($quickLinks as $ql)
                                            <div class="dynamic-row-box shadow-xs">
                                                <input type="text" name="quick_link_labels[]" class="form-control item-label-text" value="{{ $ql['label'] }}" placeholder="Label" required>

                                                <input type="text" name="quick_link_urls[]" class="form-control" value="{{ $ql['url'] }}" placeholder="URL" required>

                                                <button type="button" class="btn-row-delete-sq shadow-xs" onclick="confirmItemDelete(this, 'Quick Link')" title="Remove">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Support Links -->
                    <div class="col-12 col-lg-6">
                        <div class="footer-section-card">

                            <div class="footer-section-header d-flex justify-content-between align-items-center">
                                <div class="section-title-text">
                                    <i class="fas fa-headset text-success"></i>
                                    <span>Support Links</span>
                                </div>

                                <div class="d-flex align-items-center gap-1">
                                    <button type="button" class="btn btn-success btn-compact py-0 px-2 shadow-xs" style="height: 26px; font-size: 10.5px !important;" data-bs-toggle="modal" data-bs-target="#addSupportLinkModal">
                                        <i class="fas fa-plus me-1"></i> Add Link
                                    </button>

                                    <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseSupportLinks" aria-expanded="true" aria-controls="collapseSupportLinks" title="Toggle Section">
                                        <i class="fas fa-chevron-down collapse-icon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="collapse show" id="collapseSupportLinks">
                                <div class="p-3">

                                    <div class="mb-2">
                                        <label class="form-label">Column Title</label>
                                        <input type="text" name="footer_col3_title" class="form-control" value="{{ $footerSettings['footer_col3_title'] ?? 'Support' }}" placeholder="Support">
                                    </div>

                                    <div id="supportLinksContainer">
                                        @foreach($supportLinks as $sl)
                                            <div class="dynamic-row-box shadow-xs">
                                                <input type="text" name="support_link_labels[]" class="form-control item-label-text" value="{{ $sl['label'] }}" placeholder="Label" required>

                                                <input type="text" name="support_link_urls[]" class="form-control" value="{{ $sl['url'] }}" placeholder="URL" required>

                                                <button type="button" class="btn-row-delete-sq shadow-xs" onclick="confirmItemDelete(this, 'Support Link')" title="Remove">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- TAB 3: PAYMENTS & SOCIAL -->
            <div class="custom-tab-pane" id="tab-payments">
                <div class="row g-3">

                    <!-- Payment Methods -->
                    <div class="col-12 col-lg-7">
                        <div class="footer-section-card">

                            <div class="footer-section-header d-flex justify-content-between align-items-center">
                                <div class="section-title-text">
                                    <i class="fas fa-credit-card text-danger"></i>
                                    <span>Payment Methods</span>
                                </div>

                                <div class="d-flex align-items-center gap-1">
                                    <button type="button" class="btn btn-danger btn-compact py-0 px-2 shadow-xs" style="height: 26px; font-size: 10.5px !important;" data-bs-toggle="modal" data-bs-target="#addPaymentBadgeModal">
                                        <i class="fas fa-plus me-1"></i> Add Method
                                    </button>

                                    <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapsePaymentBadges" aria-expanded="true" aria-controls="collapsePaymentBadges" title="Toggle Section">
                                        <i class="fas fa-chevron-down collapse-icon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="collapse show" id="collapsePaymentBadges">
                                <div class="p-3">

                                    <div class="row g-2 mb-2">
                                        <div class="col-12 col-sm-6">
                                            <label class="form-label">Section Title</label>
                                            <input type="text" name="footer_col4_title" class="form-control" value="{{ $footerSettings['footer_col4_title'] ?? 'Payment Methods' }}" placeholder="Payment Methods">
                                        </div>

                                        <div class="col-12 col-sm-6 d-flex align-items-center gap-3 pt-sm-3">
                                            <div class="form-check form-switch m-0 ps-0">
                                                <input class="form-check-input ms-0" type="checkbox" name="footer_show_payment_badges" value="1" {{ ($footerSettings['footer_show_payment_badges'] ?? '1') == '1' ? 'checked' : '' }}>
                                                <label class="form-check-label small fw-semibold ms-1">Show Badges</label>
                                            </div>

                                            <div class="form-check form-switch m-0 ps-0">
                                                <input class="form-check-input ms-0" type="checkbox" name="footer_payment_only_logo" value="1" {{ ($footerSettings['footer_payment_only_logo'] ?? '0') == '1' ? 'checked' : '' }}>
                                                <label class="form-check-label small fw-semibold text-primary ms-1">Logo Only</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="paymentBadgesContainer">
                                        @foreach($paymentBadges as $idx => $pb)
                                            @php
                                                $pbSrc = !empty($pb['image']) ? $resolveFooterImg($pb['image']) : '';
                                            @endphp

                                            <div class="payment-badge-row shadow-xs">
                                                <div class="d-flex align-items-center gap-1 flex-grow-1 min-w-0">

                                                    <input type="text" name="payment_names[]" class="form-control item-label-text" value="{{ $pb['name'] ?? '' }}" placeholder="Method Name">

                                                    <button type="button" class="btn btn-outline-primary btn-compact py-0 px-2 shadow-xs flex-shrink-0" style="height: 34px;" onclick="chooseRowBadgeMedia(this)" title="Media Library">
                                                        <i class="fas fa-photo-film"></i>
                                                    </button>

                                                    <input type="file" name="payment_images[{{ $idx }}]" class="form-control payment-file-input" accept="image/*" onchange="previewPaymentBadge(this)">

                                                    <input type="hidden" name="payment_existing_images[{{ $idx }}]" class="payment-existing-image-input" value="{{ $pb['image'] ?? '' }}">
                                                </div>

                                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                                    <div class="proof-thumb-box shadow-xs" onclick="zoomThemeMedia('{{ $pbSrc }}', '{{ addslashes($pb['name'] ?? 'Icon') }}')" title="Preview">
                                                        @if(!empty($pbSrc))
                                                            <img src="{{ $pbSrc }}" alt="icon" onerror="this.src='https://placehold.co/44x24?text=Icon';">
                                                        @else
                                                            <span class="text-muted" style="font-size: 10px;">None</span>
                                                        @endif
                                                    </div>

                                                    <button type="button" class="btn-row-delete-sq shadow-xs" onclick="confirmItemDelete(this, 'Payment Method')" title="Remove">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Social Links -->
                    <div class="col-12 col-lg-5">
                        <div class="footer-section-card">

                            <div class="footer-section-header d-flex justify-content-between align-items-center">
                                <div class="section-title-text">
                                    <i class="fas fa-share-alt text-primary"></i>
                                    <span>Social Links</span>
                                </div>

                                <div class="d-flex align-items-center gap-1">
                                    <button type="button" class="btn btn-primary btn-compact py-0 px-2 shadow-xs" style="height: 26px; font-size: 10.5px !important;" data-bs-toggle="modal" data-bs-target="#addSocialIconModal">
                                        <i class="fas fa-plus me-1"></i> Add Icon
                                    </button>

                                    <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseSocialIcons" aria-expanded="true" aria-controls="collapseSocialIcons" title="Toggle Section">
                                        <i class="fas fa-chevron-down collapse-icon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="collapse show" id="collapseSocialIcons">
                                <div class="p-3">

                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label m-0">Show Social Links</label>

                                        <div class="form-check form-switch m-0 ps-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="footer_show_social" value="1" {{ ($footerSettings['footer_show_social'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>

                                    <div id="socialIconsContainer">
                                        @foreach($socialLinks as $soc)
                                            <div class="dynamic-row-box shadow-xs">
                                                <input type="text" name="social_icons[]" class="form-control item-label-text" style="max-width: 140px;" value="{{ $soc['icon'] }}" placeholder="fab fa-facebook" required>

                                                <input type="text" name="social_urls[]" class="form-control" value="{{ $soc['url'] }}" placeholder="URL" required>

                                                <button type="button" class="btn-row-delete-sq shadow-xs" onclick="confirmItemDelete(this, 'Social Icon')" title="Remove">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- TAB 4: STYLE & LAYOUT -->
            <div class="custom-tab-pane" id="tab-styling">
                <div class="row g-3">

                    <!-- Color Settings -->
                    <div class="col-12 col-lg-6">
                        <div class="footer-section-card">

                            <div class="footer-section-header d-flex justify-content-between align-items-center">
                                <div class="section-title-text">
                                    <i class="fas fa-paint-brush text-warning"></i>
                                    <span>Colors</span>
                                </div>

                                <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseColorControls" aria-expanded="true" aria-controls="collapseColorControls" title="Toggle Section">
                                    <i class="fas fa-chevron-down collapse-icon"></i>
                                </button>
                            </div>

                            <div class="collapse show" id="collapseColorControls">
                                <div class="p-3">
                                    <div class="row g-2">

                                        <div class="col-12 col-sm-6">
                                            <label class="form-label">Background</label>

                                            <div class="color-sync-card">
                                                <input type="color" class="color-picker-input" value="{{ $footerSettings['footer_bg'] ?? '#020b18' }}" oninput="syncColorValues(this, 'footerBgText')">

                                                <input type="text" id="footerBgText" name="footer_bg" class="color-text-input" value="{{ $footerSettings['footer_bg'] ?? '#020b18' }}" oninput="syncTextValues(this, 'footerBgText')">
                                            </div>
                                        </div>

                                        <div class="col-12 col-sm-6">
                                            <label class="form-label">Heading Color</label>

                                            <div class="color-sync-card">
                                                <input type="color" class="color-picker-input" value="{{ $footerSettings['footer_heading_color'] ?? '#ffffff' }}" oninput="syncColorValues(this, 'footerHeadingText')">

                                                <input type="text" id="footerHeadingText" name="footer_heading_color" class="color-text-input" value="{{ $footerSettings['footer_heading_color'] ?? '#ffffff' }}" oninput="syncTextValues(this, 'footerHeadingText')">
                                            </div>
                                        </div>

                                        <div class="col-12 col-sm-6">
                                            <label class="form-label">Text &amp; Link Color</label>

                                            <div class="color-sync-card">
                                                <input type="color" class="color-picker-input" value="{{ $footerSettings['footer_text_color'] ?? '#94a3b8' }}" oninput="syncColorValues(this, 'footerTextColor')">

                                                <input type="text" id="footerTextColor" name="footer_text_color" class="color-text-input" value="{{ $footerSettings['footer_text_color'] ?? '#94a3b8' }}" oninput="syncTextValues(this, 'footerTextColor')">
                                            </div>
                                        </div>

                                        <div class="col-12 col-sm-6">
                                            <label class="form-label">Bottom Bar</label>

                                            <div class="color-sync-card">
                                                <input type="color" class="color-picker-input" value="{{ $footerSettings['footer_bottom_bg'] ?? '#010813' }}" oninput="syncColorValues(this, 'footerBottomBg')">

                                                <input type="text" id="footerBottomBg" name="footer_bottom_bg" class="color-text-input" value="{{ $footerSettings['footer_bottom_bg'] ?? '#010813' }}" oninput="syncTextValues(this, 'footerBottomBg')">
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Settings -->
                    <div class="col-12 col-lg-6">
                        <div class="footer-section-card">

                            <div class="footer-section-header d-flex justify-content-between align-items-center">
                                <div class="section-title-text">
                                    <i class="fas fa-mobile-alt text-info"></i>
                                    <span>Mobile &amp; Footer Links</span>
                                </div>

                                <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseMobileBehavior" aria-expanded="true" aria-controls="collapseMobileBehavior" title="Toggle Section">
                                    <i class="fas fa-chevron-down collapse-icon"></i>
                                </button>
                            </div>

                            <div class="collapse show" id="collapseMobileBehavior">
                                <div class="p-3">

                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label m-0">Mobile Accordion</label>

                                        <div class="form-check form-switch m-0 ps-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="footer_show_accordion_mobile" value="1" {{ ($footerSettings['footer_show_accordion_mobile'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label mb-1">Mobile Alignment</label>

                                        <div class="d-flex gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="footer_mobile_align" id="alignLeft" value="left" {{ ($footerSettings['footer_mobile_align'] ?? 'left') == 'left' ? 'checked' : '' }}>

                                                <label class="form-check-label small fw-semibold" for="alignLeft">Left</label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="footer_mobile_align" id="alignCenter" value="center" {{ ($footerSettings['footer_mobile_align'] ?? 'left') == 'center' ? 'checked' : '' }}>

                                                <label class="form-check-label small fw-semibold" for="alignCenter">Center</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-2 pt-2 border-top">

                                        <div class="col-12 col-sm-6">
                                            <label class="form-label">Track Button Text</label>

                                            <input type="text" name="footer_track_btn_text" class="form-control" value="{{ $footerSettings['footer_track_btn_text'] ?? 'Track Order' }}">
                                        </div>

                                        <div class="col-12 col-sm-6">
                                            <label class="form-label">Track Button URL</label>

                                            <input type="text" name="footer_track_btn_url" class="form-control" value="{{ $footerSettings['footer_track_btn_url'] ?? '/track' }}">
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Copyright Text</label>

                                            <input type="text" name="footer_copyright_text" class="form-control" value="{{ $footerSettings['footer_copyright_text'] ?? 'All rights reserved.' }}">
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Save -->
            <div class="d-flex justify-content-end pt-3 border-top mt-3">
                <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-4" style="min-height: 36px; font-size: 12px;">
                    <i class="fas fa-check-circle me-1"></i> Save Settings
                </button>
            </div>

        </div>
    </form>
</div>

<!-- MODALS -->

<!-- Add Quick Link -->
<div class="modal fade footer-modal-scope" id="addQuickLinkModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 8px !important;">

            <div class="modal-header bg-light border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-1 mb-0" style="font-size: 13.5px;">
                    <i class="fas fa-link text-primary"></i>
                    <span>Add Quick Link</span>
                </h6>

                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-3">
                <div class="mb-2">
                    <label class="form-label">Label *</label>
                    <input type="text" id="modalQuickLabel" class="form-control" placeholder="Terms & Conditions">
                </div>

                <div>
                    <label class="form-label">URL *</label>
                    <input type="text" id="modalQuickUrl" class="form-control" placeholder="/terms">
                </div>
            </div>

            <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between border-top">
                <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>

                <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" onclick="addQuickLinkFromModal()">Add Link</button>
            </div>

        </div>
    </div>
</div>

<!-- Add Support Link -->
<div class="modal fade footer-modal-scope" id="addSupportLinkModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 8px !important;">

            <div class="modal-header bg-light border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-1 mb-0" style="font-size: 13.5px;">
                    <i class="fas fa-headset text-success"></i>
                    <span>Add Support Link</span>
                </h6>

                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-3">
                <div class="mb-2">
                    <label class="form-label">Label *</label>
                    <input type="text" id="modalSupportLabel" class="form-control" placeholder="Return Policy">
                </div>

                <div>
                    <label class="form-label">URL *</label>
                    <input type="text" id="modalSupportUrl" class="form-control" placeholder="/returns">
                </div>
            </div>

            <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between border-top">
                <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>

                <button type="button" class="btn btn-success btn-compact shadow-xs fw-bold text-white" style="background-color: #16a34a !important; border-color: #16a34a !important;" onclick="addSupportLinkFromModal()">Add Link</button>
            </div>

        </div>
    </div>
</div>

<!-- Add Social Icon -->
<div class="modal fade footer-modal-scope" id="addSocialIconModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 8px !important;">

            <div class="modal-header bg-light border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-1 mb-0" style="font-size: 13.5px;">
                    <i class="fas fa-share-alt text-primary"></i>
                    <span>Add Social Icon</span>
                </h6>

                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-3">
                <div class="mb-2">
                    <label class="form-label">Icon Class *</label>
                    <input type="text" id="modalSocialIcon" class="form-control" placeholder="fab fa-facebook">
                </div>

                <div>
                    <label class="form-label">Profile URL *</label>
                    <input type="text" id="modalSocialUrl" class="form-control" placeholder="https://facebook.com/page">
                </div>
            </div>

            <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between border-top">
                <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>

                <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" onclick="addSocialIconFromModal()">Add Icon</button>
            </div>

        </div>
    </div>
</div>

<!-- Add Payment Method -->
<div class="modal fade footer-modal-scope" id="addPaymentBadgeModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 8px !important;">

            <div class="modal-header bg-light border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-1 mb-0" style="font-size: 13.5px;">
                    <i class="fas fa-credit-card text-danger"></i>
                    <span>Add Payment Method</span>
                </h6>

                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-3">
                <div class="mb-2">
                    <label class="form-label">Method Name *</label>
                    <input type="text" id="modalPaymentName" class="form-control" placeholder="bKash / Visa">
                </div>

                <input type="hidden" id="modalPaymentMedia" value="">

                <button type="button" class="btn btn-primary btn-compact w-100 shadow-xs mb-2" onclick="chooseModalPaymentBadgeMedia()">
                    <i class="fas fa-photo-film me-1"></i> Media Library
                </button>

                <div class="mb-2">
                    <label class="form-label">Or Upload</label>
                    <input type="file" id="modalPaymentImage" class="form-control" accept="image/*" onchange="previewModalPaymentImage(this)">
                </div>

                <div id="modalPaymentPreviewWrapper" class="text-center p-2 border bg-light d-none" style="border-radius: 5px;">
                    <img id="modalPaymentPreviewImg" src="" style="max-height: 32px; max-width: 60px; object-fit: contain;">
                </div>
            </div>

            <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between border-top">
                <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>

                <button type="button" class="btn btn-danger btn-compact shadow-xs fw-bold" onclick="addPaymentBadgeFromModal()">Add Method</button>
            </div>

        </div>
    </div>
</div>

<!-- Delete Item Confirmation -->
<div class="modal fade footer-modal-scope" id="deleteItemConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content minimal-modal-card">

            <h6 class="minimal-modal-title">Delete this item?</h6>

            <p class="minimal-modal-subtext">
                <span id="deleteItemLabelPreview" class="fw-semibold text-dark">This item</span> will be removed from the footer.
            </p>

            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn-minimal-delete" onclick="executeItemDelete()">Delete</button>

                <button type="button" class="btn-minimal-cancel" data-bs-dismiss="modal">Cancel</button>
            </div>

        </div>
    </div>
</div>

<!-- Delete Logo Confirmation -->
<div class="modal fade footer-modal-scope" id="deleteLogoConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content minimal-modal-card">

            <h6 class="minimal-modal-title">Remove Footer Logo?</h6>

            <p class="minimal-modal-subtext">
                The logo will be removed from your footer when you save.
            </p>

            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn-minimal-delete" onclick="executeLogoRemove()">Remove</button>

                <button type="button" class="btn-minimal-cancel" data-bs-dismiss="modal">Cancel</button>
            </div>

        </div>
    </div>
</div>

<!-- Media Preview -->
<div class="modal fade footer-modal-scope" id="footerMediaZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2 bg-white" style="border-radius: 8px !important;">

            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;" id="footerMediaZoomTitle">
                    <i class="fas fa-image text-primary me-1"></i> Media Preview
                </span>

                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-2">
                <img id="footerMediaZoomImgTag" src="" alt="Media Preview" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 5px;">
            </div>

        </div>
    </div>
</div>

<!-- Notice -->
<div class="modal fade footer-modal-scope" id="footerNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content minimal-modal-card">

            <h6 class="minimal-modal-title" id="footerNoticeTitle">Notice</h6>

            <p class="minimal-modal-subtext" id="footerNoticeMessage" style="margin-bottom: 16px !important;"></p>

            <div class="d-flex align-items-center">
                <button type="button" class="btn-minimal-action" data-bs-dismiss="modal">OK</button>
            </div>

        </div>
    </div>
</div>

<script>
// Notice Modal
function showFooterModalNotice(message, title = 'Notice') {
    const titleEl = document.getElementById('footerNoticeTitle');
    const msgEl = document.getElementById('footerNoticeMessage');

    if (titleEl) titleEl.innerText = title;
    if (msgEl) msgEl.innerText = message;

    const modalEl = document.getElementById('footerNoticeModal');

    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

// Media Extractor
function extractMediaItem(mediaInput) {
    if (!mediaInput) return null;

    const item = Array.isArray(mediaInput) ? mediaInput[0] : mediaInput;

    if (!item || typeof item !== 'object') return null;

    const rawPath = item.file_path || item.path || item.url || '';
    const rawUrl = item.url || item.file_path || item.path || '';

    if (!rawPath || rawPath === 'undefined') return null;

    return {
        path: String(rawPath).trim(),
        url: String(rawUrl).trim()
    };
}

// Tab Switcher
function switchBuilderTab(tabId, btnElement) {
    document.querySelectorAll('.custom-tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.custom-tab-pane').forEach(pane => pane.classList.remove('active'));

    btnElement.classList.add('active');

    const targetPane = document.getElementById(tabId);

    if (targetPane) targetPane.classList.add('active');
}

// Color Sync
function syncColorValues(picker, targetId) {
    const textInput = document.getElementById(targetId);

    if (textInput) {
        textInput.value = picker.value.toLowerCase();
    }
}

function syncTextValues(input, targetId) {
    let val = input.value.trim();

    if (!val.startsWith('#') && val.length > 0) {
        val = '#' + val;
    }

    const isValid = /^#([0-9A-F]{3}){1,2}$/i.test(val);

    if (isValid) {
        const picker = input.closest('.color-sync-card').querySelector('.color-picker-input');

        if (picker) picker.value = val;
    }
}

// Footer Logo Media Picker
function chooseFooterLogoMedia() {
    if (typeof window.openMediaPicker === 'function') {
        window.openMediaPicker({
            multiple: false,

            onSelect: function(media) {
                const picked = extractMediaItem(media);

                if (picked && picked.path) {
                    document.getElementById('footerLogoMediaInput').value = picked.path;
                    document.getElementById('footerLogoInput').value = '';
                    document.getElementById('logoPreviewImg').src = picked.url;

                    document.getElementById('logoPreviewWrapper').classList.remove('d-none');
                    document.getElementById('logoPreviewWrapper').classList.add('d-flex');

                    document.getElementById('hiddenRemoveFooterLogo').value = '0';

                    showFooterModalNotice('Footer logo selected.', 'Logo Selected');
                }
            }
        });
    } else {
        showFooterModalNotice('Media Library is loading. Try again.', 'Notice');
    }
}

// Logo Preview
function previewLogo(input) {
    if (input.files && input.files[0]) {
        document.getElementById('footerLogoMediaInput').value = '';

        const reader = new FileReader();

        reader.onload = function(e) {
            const previewImg = document.getElementById('logoPreviewImg');

            previewImg.src = e.target.result;

            document.getElementById('logoPreviewWrapper').classList.remove('d-none');
            document.getElementById('logoPreviewWrapper').classList.add('d-flex');

            document.getElementById('hiddenRemoveFooterLogo').value = '0';
        };

        reader.readAsDataURL(input.files[0]);
    }
}

// Logo Delete Modal
function openLogoDeleteModal() {
    const modal = new bootstrap.Modal(document.getElementById('deleteLogoConfirmModal'));
    modal.show();
}

function executeLogoRemove() {
    document.getElementById('hiddenRemoveFooterLogo').value = '1';
    document.getElementById('footerLogoMediaInput').value = '';

    document.getElementById('logoPreviewWrapper').classList.add('d-none');
    document.getElementById('logoPreviewWrapper').classList.remove('d-flex');

    document.getElementById('footerLogoInput').value = '';

    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('deleteLogoConfirmModal'));

    if (modalInstance) modalInstance.hide();

    showFooterModalNotice('Footer logo removed.', 'Logo Removed');
}

let paymentBadgeIndex = {{ count($paymentBadges) }};

// Existing Payment Badge Media Picker
function chooseRowBadgeMedia(btn) {
    const row = btn.closest('.payment-badge-row');
    const existingInput = row.querySelector('.payment-existing-image-input');
    const fileInput = row.querySelector('.payment-file-input');
    const previewThumb = row.querySelector('.proof-thumb-box');

    if (typeof window.openMediaPicker === 'function') {
        window.openMediaPicker({
            multiple: false,

            onSelect: function(media) {
                const picked = extractMediaItem(media);

                if (picked && picked.path) {
                    existingInput.value = picked.path;

                    if (fileInput) fileInput.value = '';

                    if (previewThumb) {
                        previewThumb.innerHTML = `
                            <img src="${picked.url}" alt="icon" onerror="this.src='https://placehold.co/44x24?text=Icon';">
                            <div class="proof-thumb-hover-overlay">
                                <i class="fas fa-search-plus text-white" style="font-size: 8px;"></i>
                            </div>
                        `;
                    }

                    showFooterModalNotice('Payment image updated.', 'Updated');
                }
            }
        });
    } else {
        showFooterModalNotice('Media Library is loading. Try again.', 'Notice');
    }
}

// New Payment Badge Media Picker
function chooseModalPaymentBadgeMedia() {
    if (typeof window.openMediaPicker === 'function') {
        window.openMediaPicker({
            multiple: false,

            onSelect: function(media) {
                const picked = extractMediaItem(media);

                if (picked && picked.path) {
                    document.getElementById('modalPaymentMedia').value = picked.path;
                    document.getElementById('modalPaymentImage').value = '';
                    document.getElementById('modalPaymentPreviewImg').src = picked.url;

                    document.getElementById('modalPaymentPreviewWrapper').classList.remove('d-none');
                }
            }
        });
    } else {
        showFooterModalNotice('Media Library is loading. Try again.', 'Notice');
    }
}

// Add Quick Link
function addQuickLinkFromModal() {
    const label = document.getElementById('modalQuickLabel').value.trim();
    const url = document.getElementById('modalQuickUrl').value.trim();

    if (!label || !url) return;

    const wrap = document.getElementById('quickLinksContainer');
    const row = document.createElement('div');

    row.className = 'dynamic-row-box shadow-xs';

    row.innerHTML = `
        <input type="text" name="quick_link_labels[]" class="form-control item-label-text" value="${label}" placeholder="Label" required>

        <input type="text" name="quick_link_urls[]" class="form-control" value="${url}" placeholder="URL" required>

        <button type="button" class="btn-row-delete-sq shadow-xs" onclick="confirmItemDelete(this, 'Quick Link')" title="Remove">
            <i class="fas fa-trash-alt"></i>
        </button>
    `;

    wrap.insertBefore(row, wrap.firstChild);

    document.getElementById('modalQuickLabel').value = '';
    document.getElementById('modalQuickUrl').value = '';

    bootstrap.Modal.getInstance(document.getElementById('addQuickLinkModal')).hide();

    showFooterModalNotice('Quick link added.', 'Link Added');
}

// Add Support Link
function addSupportLinkFromModal() {
    const label = document.getElementById('modalSupportLabel').value.trim();
    const url = document.getElementById('modalSupportUrl').value.trim();

    if (!label || !url) return;

    const wrap = document.getElementById('supportLinksContainer');
    const row = document.createElement('div');

    row.className = 'dynamic-row-box shadow-xs';

    row.innerHTML = `
        <input type="text" name="support_link_labels[]" class="form-control item-label-text" value="${label}" placeholder="Label" required>

        <input type="text" name="support_link_urls[]" class="form-control" value="${url}" placeholder="URL" required>

        <button type="button" class="btn-row-delete-sq shadow-xs" onclick="confirmItemDelete(this, 'Support Link')" title="Remove">
            <i class="fas fa-trash-alt"></i>
        </button>
    `;

    wrap.insertBefore(row, wrap.firstChild);

    document.getElementById('modalSupportLabel').value = '';
    document.getElementById('modalSupportUrl').value = '';

    bootstrap.Modal.getInstance(document.getElementById('addSupportLinkModal')).hide();

    showFooterModalNotice('Support link added.', 'Link Added');
}

// Add Social Icon
function addSocialIconFromModal() {
    const icon = document.getElementById('modalSocialIcon').value.trim();
    const url = document.getElementById('modalSocialUrl').value.trim();

    if (!icon || !url) return;

    const wrap = document.getElementById('socialIconsContainer');
    const row = document.createElement('div');

    row.className = 'dynamic-row-box shadow-xs';

    row.innerHTML = `
        <input type="text" name="social_icons[]" class="form-control item-label-text" style="max-width: 140px;" value="${icon}" placeholder="fab fa-facebook" required>

        <input type="text" name="social_urls[]" class="form-control" value="${url}" placeholder="URL" required>

        <button type="button" class="btn-row-delete-sq shadow-xs" onclick="confirmItemDelete(this, 'Social Icon')" title="Remove">
            <i class="fas fa-trash-alt"></i>
        </button>
    `;

    wrap.insertBefore(row, wrap.firstChild);

    document.getElementById('modalSocialUrl').value = '';

    bootstrap.Modal.getInstance(document.getElementById('addSocialIconModal')).hide();

    showFooterModalNotice('Social link added.', 'Icon Added');
}

// Payment Image Preview
function previewModalPaymentImage(input) {
    if (input.files && input.files[0]) {
        document.getElementById('modalPaymentMedia').value = '';

        const reader = new FileReader();

        reader.onload = function(e) {
            document.getElementById('modalPaymentPreviewImg').src = e.target.result;

            document.getElementById('modalPaymentPreviewWrapper').classList.remove('d-none');
        };

        reader.readAsDataURL(input.files[0]);
    }
}

function previewPaymentBadge(input) {
    const previewContainer = input.closest('.payment-badge-row').querySelector('.proof-thumb-box');

    if (input.files && input.files[0] && previewContainer) {
        const reader = new FileReader();

        reader.onload = function(e) {
            previewContainer.innerHTML = `
                <img src="${e.target.result}" alt="icon" onerror="this.src='https://placehold.co/44x24?text=Icon';">
                <div class="proof-thumb-hover-overlay">
                    <i class="fas fa-search-plus text-white" style="font-size: 8px;"></i>
                </div>
            `;
        };

        reader.readAsDataURL(input.files[0]);
    }
}

// Add Payment Badge
function addPaymentBadgeFromModal() {
    const name = document.getElementById('modalPaymentName').value.trim();
    const fileInput = document.getElementById('modalPaymentImage');
    const mediaPath = document.getElementById('modalPaymentMedia').value.trim();

    if (!name) return;

    const wrap = document.getElementById('paymentBadgesContainer');
    const row = document.createElement('div');

    row.className = 'payment-badge-row shadow-xs';

    let previewHtml = `<span class="text-muted" style="font-size: 10px;">None</span>`;
    let previewUrl = '';

    if (mediaPath) {
        previewUrl = document.getElementById('modalPaymentPreviewImg').src;

        previewHtml = `
            <img src="${previewUrl}" alt="icon" onerror="this.src='https://placehold.co/44x24?text=Icon';">
            <div class="proof-thumb-hover-overlay">
                <i class="fas fa-search-plus text-white" style="font-size: 8px;"></i>
            </div>
        `;
    } else if (fileInput.files && fileInput.files[0]) {
        previewUrl = URL.createObjectURL(fileInput.files[0]);

        previewHtml = `
            <img src="${previewUrl}" alt="icon" onerror="this.src='https://placehold.co/44x24?text=Icon';">
            <div class="proof-thumb-hover-overlay">
                <i class="fas fa-search-plus text-white" style="font-size: 8px;"></i>
            </div>
        `;
    }

    row.innerHTML = `
        <div class="d-flex align-items-center gap-1 flex-grow-1 min-w-0">
            <input type="text" name="payment_names[]" class="form-control item-label-text" value="${name}" placeholder="Method Name">

            <button type="button" class="btn btn-outline-primary btn-compact py-0 px-2 shadow-xs flex-shrink-0" style="height: 34px;" onclick="chooseRowBadgeMedia(this)" title="Media Library">
                <i class="fas fa-photo-film"></i>
            </button>

            <input type="file" name="payment_images[${paymentBadgeIndex}]" class="form-control payment-file-input" accept="image/*" onchange="previewPaymentBadge(this)">

            <input type="hidden" name="payment_existing_images[${paymentBadgeIndex}]" class="payment-existing-image-input" value="${mediaPath}">
        </div>

        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <div class="proof-thumb-box shadow-xs" onclick="zoomThemeMedia('${previewUrl}', '${name}')" title="Preview">
                ${previewHtml}
            </div>

            <button type="button" class="btn-row-delete-sq shadow-xs" onclick="confirmItemDelete(this, 'Payment Method')" title="Remove">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    `;

    if (!mediaPath && fileInput.files && fileInput.files[0] && typeof DataTransfer !== 'undefined') {
        const dataTransfer = new DataTransfer();

        dataTransfer.items.add(fileInput.files[0]);

        row.querySelector('.payment-file-input').files = dataTransfer.files;
    }

    wrap.insertBefore(row, wrap.firstChild);

    paymentBadgeIndex++;

    document.getElementById('modalPaymentName').value = '';
    document.getElementById('modalPaymentMedia').value = '';

    fileInput.value = '';

    document.getElementById('modalPaymentPreviewWrapper').classList.add('d-none');

    bootstrap.Modal.getInstance(document.getElementById('addPaymentBadgeModal')).hide();

    showFooterModalNotice('Payment method added.', 'Method Added');
}

// Delete Item
let activeDeleteTarget = null;

function confirmItemDelete(btn, itemType) {
    activeDeleteTarget = btn.closest('.dynamic-row-box') || btn.closest('.payment-badge-row');

    const labelInput = activeDeleteTarget ? activeDeleteTarget.querySelector('.item-label-text') : null;

    const labelText = labelInput && labelInput.value.trim()
        ? `"${labelInput.value.trim()}"`
        : `this ${itemType}`;

    const preview = document.getElementById('deleteItemLabelPreview');

    if (preview) preview.textContent = labelText;

    const modal = new bootstrap.Modal(document.getElementById('deleteItemConfirmModal'));

    modal.show();
}

function executeItemDelete() {
    if (activeDeleteTarget) {
        activeDeleteTarget.remove();
        activeDeleteTarget = null;
    }

    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('deleteItemConfirmModal'));

    if (modalInstance) modalInstance.hide();

    showFooterModalNotice('Item removed.', 'Deleted');
}

// Media Zoom
function zoomThemeMedia(url, title = 'Media Preview') {
    const titleEl = document.getElementById('footerMediaZoomTitle');
    const imgTag = document.getElementById('footerMediaZoomImgTag');

    if (url && imgTag) {
        if (titleEl) titleEl.innerText = title;

        imgTag.src = url;

        bootstrap.Modal.getOrCreateInstance(document.getElementById('footerMediaZoomModal')).show();
    }
}
</script>
@endsection