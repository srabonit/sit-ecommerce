@extends('layouts.admin')

@section('title', 'Site Identity - ' . config('app.name'))

@section('admin_content')
@php
    $resolveBrandMedia = function($path, $fallback = 'https://placehold.co/150x50?text=No+Image') {
        if (empty($path) || $path === 'undefined' || $path === 'null') {
            return $fallback;
        }

        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }

        if (function_exists('show_image')) {
            return show_image($path, $fallback);
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    };

    $currentTimezone = $settings['timezone'] ?? 'Asia/Dhaka';
    $allTimezones = \DateTimeZone::listIdentifiers();

    $currLogo = $settings['header_logo'] ?? null;
    $hasRealLogo = !empty($currLogo);
    $logoSrc = $resolveBrandMedia($currLogo, 'https://placehold.co/150x50?text=No+Logo');

    $currFav = $settings['site_icon'] ?? null;
    $hasRealFav = !empty($currFav);
    $favSrc = $resolveBrandMedia($currFav, 'https://placehold.co/40x40?text=Icon');

    $displayMode = $settings['header_display_mode'] ?? 'logo_title';
@endphp

<style>
    /* =====================================================
       SITE IDENTITY — MODERN ADMIN DESIGN
    ===================================================== */

    #siteIdentityRoot {
        --si-primary: #2563eb;
        --si-primary-soft: #eff6ff;
        --si-text: #0f172a;
        --si-muted: #64748b;
        --si-border: #e2e8f0;
        --si-surface: #f8fafc;
        --si-radius: 5px;
        color: var(--si-text);
    }

    #siteIdentityRoot *,
    #siteIdentityNoticeModal * {
        box-sizing: border-box;
    }

    #siteIdentityRoot .btn,
    #siteIdentityRoot .form-control,
    #siteIdentityRoot .form-select,
    #siteIdentityRoot .nav-link,
    #siteIdentityRoot .alert,
    #siteIdentityRoot .img-upload-box,
    #siteIdentityRoot .display-choice-card,
    #siteIdentityRoot .wp-customizer-box,
    #siteIdentityNoticeModal .btn,
    #siteIdentityNoticeModal .modal-content {
        border-radius: var(--si-radius) !important;
    }

    /* Page Header */

    #siteIdentityRoot .page-heading {
        font-size: 18px;
        font-weight: 700;
        color: var(--si-text);
        letter-spacing: -0.3px;
    }

    #siteIdentityRoot .page-description {
        color: var(--si-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    #siteIdentityRoot .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 7px 13px !important;
        font-size: 11.5px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #siteIdentityRoot .btn-primary {
        background: var(--si-primary);
        border-color: var(--si-primary);
    }

    #siteIdentityRoot .btn-primary:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
        transform: translateY(-1px);
    }

    /* Main Container */

    #siteIdentityRoot .settings-card {
        background: #fff;
        border: 1px solid var(--si-border);
        border-radius: var(--si-radius);
        box-shadow: 0 2px 8px rgba(15, 23, 42, .035);
        overflow: hidden;
    }

    #siteIdentityRoot .settings-card-body {
        padding: 20px;
    }

    /* Navigation */

    #siteIdentityRoot .settings-nav-wrapper {
        padding: 14px 18px;
        border-bottom: 1px solid var(--si-border);
        background: #fff;
        overflow-x: auto;
        scrollbar-width: none;
    }

    #siteIdentityRoot .settings-nav-wrapper::-webkit-scrollbar {
        display: none;
    }

    #siteIdentityRoot .settings-nav-tabs {
        display: inline-flex;
        flex-wrap: nowrap;
        gap: 5px;
        padding: 4px;
        margin: 0;
        background: #f1f5f9;
        border-radius: var(--si-radius);
        min-width: max-content;
    }

    #siteIdentityRoot .settings-nav-tabs .nav-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 8px 15px;
        border: 0;
        background: transparent;
        color: #475569;
        font-size: 12px;
        font-weight: 600;
        transition: all .18s ease;
    }

    #siteIdentityRoot .settings-nav-tabs .nav-link:hover {
        color: var(--si-primary);
        background: rgba(255,255,255,.7);
    }

    #siteIdentityRoot .settings-nav-tabs .nav-link.active {
        color: #fff !important;
        background: var(--si-primary) !important;
        box-shadow: 0 2px 5px rgba(37,99,235,.18);
    }

    /* Section Headings */

    #siteIdentityRoot .section-heading {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eef2f7;
        color: var(--si-text);
        font-size: 12.5px;
        font-weight: 700;
        letter-spacing: .2px;
        text-transform: uppercase;
    }

    #siteIdentityRoot .section-heading i {
        color: var(--si-primary);
        font-size: 14px;
    }

    /* Settings Groups */

    #siteIdentityRoot .wp-customizer-box {
        padding: 15px;
        background: var(--si-surface);
        border: 1px solid var(--si-border);
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    #siteIdentityRoot .wp-customizer-box:hover {
        border-color: #cbd5e1;
    }

    /* Form Controls */

    #siteIdentityRoot .form-label {
        display: block;
        margin-bottom: 5px;
        color: #334155;
        font-size: 11.5px;
        font-weight: 600;
    }

    #siteIdentityRoot .form-control,
    #siteIdentityRoot .form-select {
        min-height: 34px;
        padding: 6px 10px;
        border: 1px solid #cbd5e1;
        background-color: #fff;
        color: var(--si-text);
        font-size: 12px;
        box-shadow: none;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    #siteIdentityRoot textarea.form-control {
        min-height: 78px;
        resize: vertical;
    }

    #siteIdentityRoot .form-control::placeholder {
        color: #94a3b8;
        font-size: 11.5px;
    }

    #siteIdentityRoot .form-control:focus,
    #siteIdentityRoot .form-select:focus {
        border-color: var(--si-primary);
        box-shadow: 0 0 0 3px rgba(37,99,235,.10);
        outline: none;
    }

    #siteIdentityRoot .form-text {
        display: block;
        margin-top: 5px;
        color: var(--si-muted);
        font-size: 10.5px;
        line-height: 1.5;
    }

    /* Media Picker */

    #siteIdentityRoot .media-picker-trigger-bar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 9px;
        margin-bottom: 12px;
    }

    #siteIdentityRoot .img-upload-box-wrapper {
        position: relative;
        display: inline-flex;
        flex-shrink: 0;
    }

    #siteIdentityRoot .img-upload-box {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 5px;
        overflow: hidden;
        border: 1px solid #cbd5e1;
        background: #fff;
    }

    #siteIdentityRoot .img-upload-box img {
        display: block;
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    #siteIdentityRoot .btn-remove-media {
        position: absolute;
        top: -7px;
        right: -7px;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 21px;
        height: 21px;
        padding: 0;
        border: 2px solid #fff;
        border-radius: 50% !important;
        background: #ef4444;
        color: #fff;
        font-size: 9px;
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(0,0,0,.15);
        transition: all .18s ease;
    }

    #siteIdentityRoot .btn-remove-media:hover {
        background: #dc2626;
        transform: scale(1.08);
    }

    /* Display Options */

    #siteIdentityRoot .display-choice-card {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 12px;
        border: 1px solid #cbd5e1;
        background: #fff;
        cursor: pointer;
        transition: all .18s ease;
    }

    #siteIdentityRoot .display-choice-card:hover {
        border-color: #93c5fd;
        background: #f8fafc;
    }

    #siteIdentityRoot .display-choice-card:has(input:checked) {
        border-color: var(--si-primary);
        background: var(--si-primary-soft);
        box-shadow: 0 1px 4px rgba(37,99,235,.07);
    }

    #siteIdentityRoot .display-choice-card input {
        flex-shrink: 0;
        margin: 0;
        accent-color: var(--si-primary);
    }

    #siteIdentityRoot .choice-title {
        color: var(--si-text);
        font-size: 12px;
        font-weight: 700;
    }

    #siteIdentityRoot .choice-description {
        display: block;
        margin-top: 3px;
        color: var(--si-muted);
        font-size: 10.5px;
        line-height: 1.45;
    }

    /* Range Controls */

    #siteIdentityRoot .form-range {
        height: 20px;
        cursor: pointer;
        accent-color: var(--si-primary);
    }

    #siteIdentityRoot .range-value {
        color: var(--si-primary);
        font-family: monospace;
        font-weight: 700;
    }

    /* Utility */

    #siteIdentityRoot .helper-text {
        color: var(--si-muted);
        font-size: 10.5px;
        line-height: 1.5;
    }

    #siteIdentityRoot .font-monospace {
        font-size: 12px;
        font-weight: 600;
    }

    #siteIdentityRoot .tab-content {
        min-height: 250px;
    }

    /* Notice Modal */

    #siteIdentityNoticeModal .modal-content {
        border: 1px solid var(--si-border);
        box-shadow: 0 15px 40px rgba(15,23,42,.15);
    }

    #siteIdentityNoticeModal .modal-title-text {
        color: var(--si-text);
        font-size: 14px;
        font-weight: 700;
    }

    #siteIdentityNoticeModal .modal-message {
        color: var(--si-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    /* Responsive */

    @media (max-width: 767.98px) {
        #siteIdentityRoot .settings-card-body {
            padding: 14px;
        }

        #siteIdentityRoot .settings-nav-wrapper {
            padding: 12px;
        }

        #siteIdentityRoot .settings-nav-tabs {
            display: flex;
            width: 100%;
            min-width: 0;
        }

        #siteIdentityRoot .settings-nav-tabs .nav-item {
            flex: 1 1 0;
            min-width: 0;
        }

        #siteIdentityRoot .settings-nav-tabs .nav-link {
            width: 100%;
            padding: 8px 5px;
            gap: 4px;
            font-size: 10px;
        }

        #siteIdentityRoot .settings-nav-tabs .nav-link i {
            display: none;
        }

        #siteIdentityRoot .header-save-btn-wrap {
            width: 100%;
        }

        #siteIdentityRoot .header-save-btn-wrap .btn {
            width: 100%;
        }

        #siteIdentityRoot .media-picker-trigger-bar {
            align-items: stretch;
            flex-direction: column;
        }

        #siteIdentityRoot .media-picker-trigger-bar .btn {
            width: 100%;
        }

        #siteIdentityRoot .logo-upload-split {
            align-items: flex-start !important;
            flex-direction: column;
        }

        #siteIdentityRoot .logo-upload-split > .flex-grow-1 {
            width: 100%;
        }
    }

    @media (max-width: 400px) {
        #siteIdentityRoot .settings-nav-tabs .nav-link {
            font-size: 9.5px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="siteIdentityRoot">

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-3 d-flex align-items-center justify-content-between py-2 px-3">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success"></i>
                <span class="fw-semibold text-dark small">{{ session('success') }}</span>
            </div>

            <button type="button"
                    class="btn-close shadow-none"
                    data-bs-dismiss="alert"
                    aria-label="Close">
            </button>
        </div>
    @endif

    <form action="{{ route('admin.setting.general.update') }}"
          method="POST"
          enctype="multipart/form-data">

        @csrf

        {{-- Media Removal Flags --}}
        <input type="hidden" name="remove_header_logo" id="removeHeaderLogo" value="0">
        <input type="hidden" name="remove_site_icon" id="removeSiteIcon" value="0">

        {{-- Media Library Selection --}}
        <input type="hidden" name="header_logo_media" id="headerLogoMediaInput" value="">
        <input type="hidden" name="site_icon_media" id="siteIconMediaInput" value="">

        {{-- Page Header --}}
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-3">

            <div>
                <h5 class="page-heading mb-1 d-flex align-items-center gap-2">
                    <i class="fas fa-sliders-h text-primary"></i>
                    Site Identity
                </h5>

                <p class="page-description mb-0">
                    Manage branding, layout, timezone & SEO.
                </p>
            </div>

            <div class="header-save-btn-wrap">
                <button type="submit" class="btn btn-primary btn-compact shadow-sm">
                    <i class="fas fa-save"></i>
                    Save Changes
                </button>
            </div>

        </div>

        {{-- Main Settings Card --}}
        <div class="settings-card">

            {{-- Navigation Tabs --}}
            <div class="settings-nav-wrapper">
                <ul class="nav nav-pills settings-nav-tabs"
                    role="tablist">

                    <li class="nav-item" role="presentation">
                        <button class="nav-link active"
                                data-bs-toggle="tab"
                                data-bs-target="#tab-branding"
                                type="button"
                                role="tab">
                            <i class="fas fa-paint-brush"></i>
                            Identity &amp; Logo
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link"
                                data-bs-toggle="tab"
                                data-bs-target="#tab-layout"
                                type="button"
                                role="tab">
                            <i class="fas fa-th-large"></i>
                            Layout &amp; Store
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link"
                                data-bs-toggle="tab"
                                data-bs-target="#tab-seo"
                                type="button"
                                role="tab">
                            <i class="fas fa-search"></i>
                            SEO &amp; Analytics
                        </button>
                    </li>

                </ul>
            </div>

            <div class="settings-card-body">
                <div class="tab-content">

                    {{-- ==========================================
                         1. IDENTITY & LOGO
                    =========================================== --}}
                    <div class="tab-pane fade show active"
                         id="tab-branding"
                         role="tabpanel">

                        <div class="row g-3 g-lg-4">

                            {{-- Branding Media --}}
                            <div class="col-12 col-lg-6">

                                <h6 class="section-heading">
                                    <i class="fas fa-photo-video"></i>
                                    Branding Media
                                </h6>

                                {{-- Header Logo --}}
                                <div class="wp-customizer-box mb-3">

                                    <label class="form-label d-flex justify-content-between align-items-center">
                                        <span>Header Logo</span>
                                        <span class="helper-text">PNG, SVG, WEBP, JPG</span>
                                    </label>

                                    <div class="media-picker-trigger-bar">

                                        <button type="button"
                                                class="btn btn-outline-primary btn-compact"
                                                onclick="chooseHeaderLogoMedia()">
                                            <i class="fas fa-images"></i>
                                            Choose from Media Library
                                        </button>

                                        <span class="helper-text">
                                            Or upload a local file
                                        </span>

                                    </div>

                                    <div class="logo-upload-split d-flex align-items-center gap-3">

                                        <div class="img-upload-box-wrapper">

                                            <div class="img-upload-box shadow-sm"
                                                 style="width:135px;height:70px;">

                                                <img id="logoPreview"
                                                     src="{{ $logoSrc }}"
                                                     alt="Header Logo"
                                                     onerror="this.onerror=null;this.src='https://placehold.co/150x50?text=Upload+Logo';">

                                            </div>

                                            <button type="button"
                                                    class="btn-remove-media {{ $hasRealLogo ? '' : 'd-none' }}"
                                                    id="btnRemoveLogo"
                                                    onclick="removeUploadedMedia('logo')"
                                                    title="Remove Logo">
                                                <i class="fas fa-times"></i>
                                            </button>

                                        </div>

                                        <div class="flex-grow-1 w-100">

                                            <input type="file"
                                                   name="header_logo"
                                                   id="headerLogoInput"
                                                   class="form-control form-control-sm mb-2"
                                                   accept="image/*">

                                            <input type="text"
                                                   name="logo_title"
                                                   class="form-control form-control-sm"
                                                   placeholder="Logo Alt Text"
                                                   value="{{ $settings['logo_title'] ?? '' }}">

                                        </div>

                                    </div>

                                    {{-- Logo Dimensions --}}
                                    <div class="row g-3 mt-3 pt-3 border-top">

                                        <div class="col-6">

                                            <label class="form-label mb-1">
                                                Logo Width:
                                                <span id="widthVal" class="range-value">
                                                    {{ $settings['logo_width'] ?? '150' }}
                                                </span>px
                                            </label>

                                            <input type="range"
                                                   name="logo_width"
                                                   class="form-range"
                                                   min="50"
                                                   max="350"
                                                   value="{{ $settings['logo_width'] ?? '150' }}"
                                                   oninput="document.getElementById('widthVal').innerText = this.value">

                                        </div>

                                        <div class="col-6">

                                            <label class="form-label mb-1">
                                                Logo Height:
                                                <span id="heightVal" class="range-value">
                                                    {{ $settings['logo_height'] ?? '40' }}
                                                </span>px
                                            </label>

                                            <input type="range"
                                                   name="logo_height"
                                                   class="form-range"
                                                   min="20"
                                                   max="150"
                                                   value="{{ $settings['logo_height'] ?? '40' }}"
                                                   oninput="document.getElementById('heightVal').innerText = this.value">

                                        </div>

                                    </div>

                                </div>

                                {{-- Site Favicon --}}
                                <div class="wp-customizer-box">

                                    <label class="form-label d-flex justify-content-between align-items-center">
                                        <span>Site Favicon</span>
                                        <span class="helper-text">Recommended: 32 × 32 px</span>
                                    </label>

                                    <div class="media-picker-trigger-bar">

                                        <button type="button"
                                                class="btn btn-outline-primary btn-compact"
                                                onclick="chooseSiteIconMedia()">
                                            <i class="fas fa-images"></i>
                                            Choose from Media Library
                                        </button>

                                        <span class="helper-text">
                                            Or upload a local file
                                        </span>

                                    </div>

                                    <div class="d-flex align-items-center gap-3">

                                        <div class="img-upload-box-wrapper">

                                            <div class="img-upload-box shadow-sm"
                                                 style="width:52px;height:52px;">

                                                <img id="favPreview"
                                                     src="{{ $favSrc }}"
                                                     alt="Favicon"
                                                     onerror="this.onerror=null;this.src='https://placehold.co/40x40?text=Icon';">

                                            </div>

                                            <button type="button"
                                                    class="btn-remove-media {{ $hasRealFav ? '' : 'd-none' }}"
                                                    id="btnRemoveFav"
                                                    onclick="removeUploadedMedia('fav')"
                                                    title="Remove Favicon">
                                                <i class="fas fa-times"></i>
                                            </button>

                                        </div>

                                        <div class="flex-grow-1">

                                            <input type="file"
                                                   name="site_icon"
                                                   id="favInput"
                                                   class="form-control form-control-sm mb-1"
                                                   accept="image/*">

                                            <small class="helper-text">
                                                Appears on browser tabs and bookmarks. PNG or ICO recommended.
                                            </small>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            {{-- Site Identity & Visibility --}}
                            <div class="col-12 col-lg-6">

                                <h6 class="section-heading">
                                    <i class="fas fa-eye"></i>
                                    Site Identity &amp; Visibility
                                </h6>

                                <div class="mb-3">

                                    <label class="form-label">Site Title</label>

                                    <input type="text"
                                           name="site_title"
                                           class="form-control"
                                           value="{{ $settings['site_title'] ?? '' }}"
                                           placeholder="Company or Store Name">

                                </div>

                                <div class="mb-4">

                                    <label class="form-label">Tagline</label>

                                    <input type="text"
                                           name="site_tagline"
                                           class="form-control"
                                           value="{{ $settings['site_tagline'] ?? '' }}"
                                           placeholder="Store short tagline or motto">

                                </div>

                                <div class="mb-2">

                                    <label class="form-label">
                                        Header Display Preference
                                    </label>

                                    <small class="helper-text mb-3">
                                        Choose how your brand identity appears in the website header.
                                    </small>

                                    <div class="d-flex flex-column gap-2">

                                        <label class="display-choice-card">

                                            <input type="radio"
                                                   name="header_display_mode"
                                                   value="logo_only"
                                                   class="form-check-input"
                                                   {{ $displayMode === 'logo_only' ? 'checked' : '' }}>

                                            <div>
                                                <div class="choice-title">Only Logo</div>
                                                <span class="choice-description">
                                                    Displays only the logo image in the header.
                                                </span>
                                            </div>

                                        </label>

                                        <label class="display-choice-card">

                                            <input type="radio"
                                                   name="header_display_mode"
                                                   value="logo_title"
                                                   class="form-check-input"
                                                   {{ $displayMode === 'logo_title' ? 'checked' : '' }}>

                                            <div>
                                                <div class="choice-title">Logo &amp; Site Title</div>
                                                <span class="choice-description">
                                                    Displays your brand logo with the site title.
                                                </span>
                                            </div>

                                        </label>

                                        <label class="display-choice-card">

                                            <input type="radio"
                                                   name="header_display_mode"
                                                   value="logo_title_tagline"
                                                   class="form-check-input"
                                                   {{ $displayMode === 'logo_title_tagline' ? 'checked' : '' }}>

                                            <div>
                                                <div class="choice-title">Logo, Title &amp; Tagline</div>
                                                <span class="choice-description">
                                                    Displays the complete branding layout with title and tagline.
                                                </span>
                                            </div>

                                        </label>

                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>

                    {{-- ==========================================
                         2. LAYOUT & STORE SETUP
                    =========================================== --}}
                    <div class="tab-pane fade"
                         id="tab-layout"
                         role="tabpanel">

                        <h6 class="section-heading">
                            <i class="fas fa-th-large"></i>
                            Store Layout &amp; Regional Settings
                        </h6>

                        <div class="row g-3">

                            <div class="col-12 col-sm-6 col-md-4">

                                <label class="form-label">
                                    Timezone
                                </label>

                                <select name="timezone" class="form-select">

                                    @foreach($allTimezones as $tz)
                                        <option value="{{ $tz }}"
                                            {{ $currentTimezone === $tz ? 'selected' : '' }}>
                                            {{ $tz }}
                                        </option>
                                    @endforeach

                                </select>

                                <small class="form-text">
                                    Controls order timestamps, invoices and scheduling.
                                </small>

                            </div>

                            <div class="col-12 col-sm-6 col-md-4">

                                <label class="form-label">
                                    Date Format
                                </label>

                                <select name="date_format" class="form-select">

                                    <option value="d-m-Y" {{ ($settings['date_format'] ?? '') == 'd-m-Y' ? 'selected' : '' }}>
                                        DD-MM-YYYY
                                    </option>

                                    <option value="d M, Y" {{ ($settings['date_format'] ?? '') == 'd M, Y' ? 'selected' : '' }}>
                                        DD Mon, YYYY
                                    </option>

                                    <option value="Y-m-d" {{ ($settings['date_format'] ?? '') == 'Y-m-d' ? 'selected' : '' }}>
                                        YYYY-MM-DD
                                    </option>

                                    <option value="m/d/Y" {{ ($settings['date_format'] ?? '') == 'm/d/Y' ? 'selected' : '' }}>
                                        MM/DD/YYYY (US)
                                    </option>

                                </select>

                            </div>

                            <div class="col-12 col-sm-6 col-md-4">

                                <label class="form-label">
                                    Products Per Page
                                </label>

                                <input type="number"
                                       name="products_per_page"
                                       class="form-control font-monospace"
                                       value="{{ $settings['products_per_page'] ?? '16' }}">

                                <small class="form-text">
                                    Number of products displayed per listing page.
                                </small>

                            </div>

                        </div>

                        <div class="mt-4 pt-3 border-top">

                            <h6 class="section-heading">
                                <i class="fas fa-mobile-alt"></i>
                                Responsive Product Grid
                            </h6>

                            <div class="row g-3">

                                <div class="col-4">

                                    <label class="form-label">
                                        Mobile Grid (Cols)
                                    </label>

                                    <input type="number"
                                           name="grid_mobile"
                                           class="form-control font-monospace"
                                           value="{{ $settings['grid_mobile'] ?? '2' }}"
                                           min="1"
                                           max="4">

                                    <small class="form-text">
                                        Mobile devices
                                    </small>

                                </div>

                                <div class="col-4">

                                    <label class="form-label">
                                        Tablet Grid (Cols)
                                    </label>

                                    <input type="number"
                                           name="grid_tab"
                                           class="form-control font-monospace"
                                           value="{{ $settings['grid_tab'] ?? '3' }}"
                                           min="1"
                                           max="6">

                                    <small class="form-text">
                                        Tablet devices
                                    </small>

                                </div>

                                <div class="col-4">

                                    <label class="form-label">
                                        Desktop Grid (Cols)
                                    </label>

                                    <input type="number"
                                           name="grid_pc"
                                           class="form-control font-monospace"
                                           value="{{ $settings['grid_pc'] ?? '4' }}"
                                           min="1"
                                           max="8">

                                    <small class="form-text">
                                        Desktop devices
                                    </small>

                                </div>

                            </div>

                        </div>

                    </div>

                    {{-- ==========================================
                         3. SEO & ANALYTICS
                    =========================================== --}}
                    <div class="tab-pane fade"
                         id="tab-seo"
                         role="tabpanel">

                        <h6 class="section-heading">
                            <i class="fas fa-search"></i>
                            Search Engine Optimization
                        </h6>

                        <div class="row g-3">

                            <div class="col-12 col-md-6">

                                <label class="form-label">
                                    SEO Meta Title
                                </label>

                                <input type="text"
                                       name="seo_meta_title"
                                       class="form-control"
                                       value="{{ $settings['seo_meta_title'] ?? '' }}"
                                       placeholder="Search engine title tag">

                            </div>

                            <div class="col-12 col-md-6">

                                <label class="form-label">
                                    SEO Meta Keywords
                                </label>

                                <input type="text"
                                       name="seo_meta_keywords"
                                       class="form-control"
                                       value="{{ $settings['seo_meta_keywords'] ?? '' }}"
                                       placeholder="online shop, clothes, fashion">

                            </div>

                            <div class="col-12">

                                <label class="form-label">
                                    SEO Meta Description
                                </label>

                                <textarea name="seo_meta_description"
                                          class="form-control"
                                          rows="3"
                                          placeholder="Brief summary of your website for search engines...">{{ $settings['seo_meta_description'] ?? '' }}</textarea>

                            </div>

                        </div>

                        <div class="mt-4 pt-3 border-top">

                            <h6 class="section-heading">
                                <i class="fas fa-chart-line"></i>
                                Search Verification &amp; Analytics
                            </h6>

                            <div class="row g-3">

                                <div class="col-12 col-md-6">

                                    <label class="form-label">
                                        Google Site Verification
                                    </label>

                                    <input type="text"
                                           name="google_site_verification"
                                           class="form-control font-monospace"
                                           value="{{ $settings['google_site_verification'] ?? '' }}"
                                           placeholder="Google verification code">

                                </div>

                                <div class="col-12 col-md-6">

                                    <label class="form-label">
                                        Facebook Domain Verification
                                    </label>

                                    <input type="text"
                                           name="facebook_site_verification"
                                           class="form-control font-monospace"
                                           value="{{ $settings['facebook_site_verification'] ?? '' }}"
                                           placeholder="Facebook verification code">

                                </div>

                                <div class="col-12">

                                    <label class="form-label">
                                        Facebook Pixel Script
                                    </label>

                                    <textarea name="facebook_pixel_script"
                                              class="form-control font-monospace"
                                              rows="4"
                                              placeholder="Paste full Facebook Pixel script here...">{{ $settings['facebook_pixel_script'] ?? '' }}</textarea>

                                    <small class="form-text">
                                        Paste your Facebook Pixel tracking script.
                                    </small>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>
            </div>
        </div>

    </form>
</div>

{{-- ==========================================
     NOTICE MODAL
=========================================== --}}
<div class="modal fade"
     id="siteIdentityNoticeModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index:106500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content border-0 shadow-lg text-center p-3">

            <div class="modal-body p-1">

                <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center mx-auto mb-3"
                     style="width:44px;height:44px;">

                    <i class="fas fa-exclamation-triangle fs-4"></i>

                </div>

                <h6 class="modal-title-text mb-2"
                    id="siteIdentityNoticeTitle">
                    Notice
                </h6>

                <p class="modal-message mb-3"
                   id="siteIdentityNoticeMsg"></p>

                <button type="button"
                        class="btn btn-dark btn-compact px-4"
                        data-bs-dismiss="modal">
                    OK
                </button>

            </div>

        </div>

    </div>
</div>

<script>
    /* ==========================================
       NOTICE MODAL
    =========================================== */

    function showSiteIdentityNotice(message, title = 'Notice') {
        const titleEl = document.getElementById('siteIdentityNoticeTitle');
        const msgEl = document.getElementById('siteIdentityNoticeMsg');
        const modalEl = document.getElementById('siteIdentityNoticeModal');

        if (titleEl) {
            titleEl.innerText = title;
        }

        if (msgEl) {
            msgEl.innerText = message;
        }

        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }


    /* ==========================================
       MEDIA ITEM EXTRACTION
    =========================================== */

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


    /* ==========================================
       IMAGE PREVIEW
    =========================================== */

    function bindPreview(inputId, imgId, removeBtnId, mediaInputId, removeFlagId) {
        const input = document.getElementById(inputId);
        const img = document.getElementById(imgId);
        const removeBtn = document.getElementById(removeBtnId);

        if (!input || !img) return;

        input.addEventListener('change', function () {
            const file = this.files[0];

            if (file) {
                img.src = URL.createObjectURL(file);

                if (removeBtn) {
                    removeBtn.classList.remove('d-none');
                }

                if (mediaInputId) {
                    document.getElementById(mediaInputId).value = '';
                }

                if (removeFlagId) {
                    document.getElementById(removeFlagId).value = '0';
                }
            }
        });
    }

    bindPreview(
        'headerLogoInput',
        'logoPreview',
        'btnRemoveLogo',
        'headerLogoMediaInput',
        'removeHeaderLogo'
    );

    bindPreview(
        'favInput',
        'favPreview',
        'btnRemoveFav',
        'siteIconMediaInput',
        'removeSiteIcon'
    );


    /* ==========================================
       HEADER LOGO MEDIA PICKER
    =========================================== */

    function chooseHeaderLogoMedia() {
        if (typeof window.openMediaPicker === 'function') {

            window.openMediaPicker({
                multiple: false,

                onSelect: function(media) {
                    const picked = extractMediaItem(media);

                    if (picked && picked.path) {

                        document.getElementById('headerLogoMediaInput').value = picked.path;

                        const fileInput = document.getElementById('headerLogoInput');

                        if (fileInput) {
                            fileInput.value = '';
                        }

                        const img = document.getElementById('logoPreview');

                        if (img) {
                            img.src = picked.url;
                        }

                        const removeBtn = document.getElementById('btnRemoveLogo');

                        if (removeBtn) {
                            removeBtn.classList.remove('d-none');
                        }

                        document.getElementById('removeHeaderLogo').value = '0';
                    }
                }
            });

        } else {
            showSiteIdentityNotice('Media Library is loading, please try again.');
        }
    }


    /* ==========================================
       FAVICON MEDIA PICKER
    =========================================== */

    function chooseSiteIconMedia() {
        if (typeof window.openMediaPicker === 'function') {

            window.openMediaPicker({
                multiple: false,

                onSelect: function(media) {
                    const picked = extractMediaItem(media);

                    if (picked && picked.path) {

                        document.getElementById('siteIconMediaInput').value = picked.path;

                        const fileInput = document.getElementById('favInput');

                        if (fileInput) {
                            fileInput.value = '';
                        }

                        const img = document.getElementById('favPreview');

                        if (img) {
                            img.src = picked.url;
                        }

                        const removeBtn = document.getElementById('btnRemoveFav');

                        if (removeBtn) {
                            removeBtn.classList.remove('d-none');
                        }

                        document.getElementById('removeSiteIcon').value = '0';
                    }
                }
            });

        } else {
            showSiteIdentityNotice('Media Library is loading, please try again.');
        }
    }


    /* ==========================================
       REMOVE UPLOADED MEDIA
    =========================================== */

    function removeUploadedMedia(type) {

        if (type === 'logo') {

            document.getElementById('logoPreview').src =
                'https://placehold.co/150x50?text=No+Logo';

            document.getElementById('headerLogoInput').value = '';
            document.getElementById('headerLogoMediaInput').value = '';
            document.getElementById('removeHeaderLogo').value = '1';

            const btn = document.getElementById('btnRemoveLogo');

            if (btn) {
                btn.classList.add('d-none');
            }

        } else if (type === 'fav') {

            document.getElementById('favPreview').src =
                'https://placehold.co/40x40?text=Icon';

            document.getElementById('favInput').value = '';
            document.getElementById('siteIconMediaInput').value = '';
            document.getElementById('removeSiteIcon').value = '1';

            const btn = document.getElementById('btnRemoveFav');

            if (btn) {
                btn.classList.add('d-none');
            }
        }
    }
</script>
@endsection