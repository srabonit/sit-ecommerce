@extends('layouts.admin')

@section('title', 'Site Identity - ' . config('app.name'))

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #siteIdentityRoot .btn,
    #siteIdentityRoot .btn-sm,
    #siteIdentityRoot button:not(.btn-remove-media),
    #siteIdentityRoot .form-control,
    #siteIdentityRoot .form-select,
    #siteIdentityRoot .nav-link,
    #siteIdentityRoot .alert,
    #siteIdentityRoot .img-upload-box,
    #siteIdentityRoot .display-choice-card,
    #siteIdentityRoot .wp-customizer-box,
    #siteIdentityNoticeModal .btn,
    #siteIdentityNoticeModal .modal-content {
        border-radius: 5px !important;
    }

    #siteIdentityRoot .btn-compact,
    #siteIdentityNoticeModal .btn-compact {
        font-size: 11.5px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    .settings-hero-card {
        border-radius: 8px !important;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    .settings-nav-tabs-wrapper {
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
        -ms-overflow-style: none;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 10px;
    }
    .settings-nav-tabs-wrapper::-webkit-scrollbar {
        display: none;
    }

    .settings-nav-tabs {
        background: #f1f5f9;
        padding: 4px;
        border-radius: 6px !important;
        display: inline-flex;
        gap: 4px;
    }

    .settings-nav-tabs .nav-link {
        border: none;
        color: #475569;
        font-weight: 600;
        font-size: 12px;
        padding: 6px 14px;
        transition: all 0.15s ease;
        background: transparent;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .settings-nav-tabs .nav-link:hover {
        color: #0f172a;
        background-color: rgba(255, 255, 255, 0.6);
    }

    .settings-nav-tabs .nav-link.active {
        background-color: #2563eb !important;
        color: #ffffff !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25);
    }

    .wp-customizer-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 14px;
        transition: border-color 0.15s ease;
    }
    .wp-customizer-box:hover {
        border-color: #cbd5e1;
    }

    .img-upload-box-wrapper {
        position: relative;
        display: inline-block;
        flex-shrink: 0;
    }

    .img-upload-box {
        border: 1px solid #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        overflow: hidden;
        padding: 4px;
        transition: border-color 0.15s ease;
    }

    .img-upload-box img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .btn-remove-media {
        position: absolute;
        top: -6px;
        right: -6px;
        width: 20px;
        height: 20px;
        background: #ef4444;
        color: #ffffff;
        border-radius: 50% !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9.5px;
        border: 2px solid #ffffff;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        transition: transform 0.15s ease, background-color 0.15s ease;
        z-index: 5;
        padding: 0;
    }
    .btn-remove-media:hover {
        background: #dc2626;
        transform: scale(1.08);
    }

    .display-choice-card {
        border: 1px solid #cbd5e1;
        padding: 10px 12px;
        cursor: pointer;
        transition: all 0.15s ease;
        background: #ffffff;
    }
    .display-choice-card:hover {
        border-color: #93c5fd;
        background: #f8fafc;
    }
    .display-choice-card input[type="radio"]:checked + .choice-label .choice-title {
        color: #2563eb !important;
    }
    .display-choice-card:has(input[type="radio"]:checked) {
        border-color: #2563eb;
        background-color: #eff6ff;
        box-shadow: 0 1px 4px rgba(37, 99, 235, 0.08);
    }

    #siteIdentityRoot .form-control,
    #siteIdentityRoot .form-select {
        border: 1px solid #cbd5e1;
        font-size: 12.5px;
        padding: 6px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #siteIdentityRoot .form-control:focus,
    #siteIdentityRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    #siteIdentityRoot .form-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 4px;
    }

    .section-title {
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        display: flex;
        align-items: center;
    }

    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    @media (max-width: 767.98px) {
        .settings-nav-tabs {
            display: flex;
            width: 100%;
        }
        .settings-nav-tabs .nav-item {
            flex: 1 1 0;
        }
        .settings-nav-tabs .nav-link {
            width: 100%;
            padding: 6px 8px;
            font-size: 11px;
        }
        .header-save-btn-wrap {
            width: 100%;
        }
        .header-save-btn-wrap .btn-compact {
            width: 100%;
            padding: 7px 14px !important;
        }
        .media-picker-trigger-bar {
            flex-direction: column;
            align-items: stretch !important;
        }
        .media-picker-trigger-bar .btn-compact {
            width: 100%;
        }
        .logo-upload-split {
            flex-direction: column;
            align-items: flex-start !important;
        }
    }
</style>

@php
    $resolveBrandMedia = function($path, $fallback = 'https://placehold.co/150x50?text=No+Image') {
        if (empty($path) || $path === 'undefined' || $path === 'null') return $fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) return show_image($path, $fallback);
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset(ltrim($path, '/'));
    };

    // Shokol Desher Timezone dynamically loaded
    $currentTimezone = $settings['timezone'] ?? 'Asia/Dhaka';
    $allTimezones = \DateTimeZone::listIdentifiers();
@endphp

<div class="container-fluid px-2 px-md-4 py-3" id="siteIdentityRoot">
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-xs mb-3 d-flex align-items-center justify-content-between small py-2 px-3">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.setting.general.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Hidden Flags for Media Removal -->
        <input type="hidden" name="remove_header_logo" id="removeHeaderLogo" value="0">
        <input type="hidden" name="remove_site_icon" id="removeSiteIcon" value="0">

        <!-- Hidden Inputs for Media Library Selection -->
        <input type="hidden" name="header_logo_media" id="headerLogoMediaInput" value="">
        <input type="hidden" name="site_icon_media" id="siteIconMediaInput" value="">

        <!-- Top Header & Save Button Layout -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
            <div>
                <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-sliders-h text-primary"></i>
                    <span>Site Identity</span>
                </h5>
                <p class="text-muted small mb-0" style="font-size: 12px; line-height: 1.35;">Configure branding, layout, timezone, and SEO metadata effortlessly.</p>
            </div>
            <div class="header-save-btn-wrap">
                <button type="submit" class="btn btn-primary btn-compact px-3 py-2 shadow-xs gap-1">
                    <i class="fas fa-save"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </div>

        <div class="settings-hero-card p-3 p-md-4">
            <!-- Tab Navigation -->
            <div class="settings-nav-tabs-wrapper mb-3">
                <ul class="nav nav-pills settings-nav-tabs flex-nowrap text-nowrap" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-branding" type="button" role="tab">
                            <i class="fas fa-paint-brush me-1"></i>
                            <span>Identity &amp; Logo</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-layout" type="button" role="tab">
                            <i class="fas fa-th-large me-1"></i>
                            <span>Layout &amp; Store</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-seo" type="button" role="tab">
                            <i class="fas fa-search me-1"></i>
                            <span>SEO &amp; Analytics</span>
                        </button>
                    </li>
                </ul>
            </div>

            <div class="tab-content">
                <!-- ================= 1. Identity & Logo ================= -->
                <div class="tab-pane fade show active" id="tab-branding" role="tabpanel">
                    <div class="row g-3 g-md-4">
                        <div class="col-12 col-lg-6">
                            <h6 class="section-title mb-3"><i class="fas fa-photo-video text-primary me-2"></i> Branding Media</h6>
                            
                            <!-- Header Logo -->
                            <div class="wp-customizer-box mb-3">
                                <label class="form-label d-flex justify-content-between align-items-center mb-2">
                                    <span>Header Logo</span>
                                    <small class="text-muted fw-normal" style="font-size: 10.5px;">PNG, SVG, WEBP, JPG</small>
                                </label>
                                
                                <div class="media-picker-trigger-bar d-flex align-items-center gap-2 mb-2">
                                    <button type="button" class="btn btn-outline-primary btn-compact shadow-xs" onclick="chooseHeaderLogoMedia()">
                                        <i class="fas fa-images me-1"></i>
                                        <span>Choose from Media Library</span>
                                    </button>
                                    <span class="small text-muted" style="font-size: 11px;">or upload local file:</span>
                                </div>

                                <div class="logo-upload-split d-flex align-items-center gap-3">
                                    <div class="img-upload-box-wrapper">
                                        <div class="img-upload-box shadow-xs" style="width: 135px; height: 70px;">
                                            @php
                                                $currLogo = $settings['header_logo'] ?? null;
                                                $hasRealLogo = !empty($currLogo);
                                                $logoSrc = $resolveBrandMedia($currLogo, 'https://placehold.co/150x50?text=No+Logo');
                                            @endphp
                                            <img id="logoPreview" src="{{ $logoSrc }}" alt="Header Logo" onerror="this.onerror=null; this.src='https://placehold.co/150x50?text=Upload+Logo';">
                                        </div>
                                        <button type="button" class="btn-remove-media {{ $hasRealLogo ? '' : 'd-none' }}" id="btnRemoveLogo" onclick="removeUploadedMedia('logo')" title="Remove Logo">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>

                                    <div class="flex-grow-1 w-100">
                                        <input type="file" name="header_logo" id="headerLogoInput" class="form-control form-control-sm mb-2" accept="image/*">
                                        <input type="text" name="logo_title" class="form-control form-control-sm" placeholder="Logo Alt text" value="{{ $settings['logo_title'] ?? '' }}">
                                    </div>
                                </div>

                                <!-- Logo Dimensions Slider -->
                                <div class="row g-3 mt-2 pt-2 border-top">
                                    <div class="col-6">
                                        <label class="form-label small text-muted mb-1">Width: <span id="widthVal" class="text-dark font-monospace bn-digit">{{ $settings['logo_width'] ?? '150' }}</span>px</label>
                                        <input type="range" name="logo_width" class="form-range" min="50" max="350" value="{{ $settings['logo_width'] ?? '150' }}" oninput="document.getElementById('widthVal').innerText = this.value">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small text-muted mb-1">Height: <span id="heightVal" class="text-dark font-monospace bn-digit">{{ $settings['logo_height'] ?? '40' }}</span>px</label>
                                        <input type="range" name="logo_height" class="form-range" min="20" max="150" value="{{ $settings['logo_height'] ?? '40' }}" oninput="document.getElementById('heightVal').innerText = this.value">
                                    </div>
                                </div>
                            </div>

                            <!-- Site Favicon -->
                            <div class="wp-customizer-box">
                                <label class="form-label d-flex justify-content-between align-items-center mb-2">
                                    <span>Site Favicon</span>
                                    <small class="text-muted fw-normal bn-digit" style="font-size: 10.5px;">Recommended: 32x32 px</small>
                                </label>

                                <div class="media-picker-trigger-bar d-flex align-items-center gap-2 mb-2">
                                    <button type="button" class="btn btn-outline-primary btn-compact shadow-xs" onclick="chooseSiteIconMedia()">
                                        <i class="fas fa-images me-1"></i>
                                        <span>Choose from Media Library</span>
                                    </button>
                                    <span class="small text-muted" style="font-size: 11px;">or upload local file:</span>
                                </div>

                                <div class="d-flex align-items-center gap-3">
                                    <div class="img-upload-box-wrapper">
                                        <div class="img-upload-box shadow-xs" style="width: 52px; height: 52px;">
                                            @php
                                                $currFav = $settings['site_icon'] ?? null;
                                                $hasRealFav = !empty($currFav);
                                                $favSrc = $resolveBrandMedia($currFav, 'https://placehold.co/40x40?text=Icon');
                                            @endphp
                                            <img id="favPreview" src="{{ $favSrc }}" alt="Favicon" onerror="this.onerror=null; this.src='https://placehold.co/40x40?text=Icon';">
                                        </div>
                                        <button type="button" class="btn-remove-media {{ $hasRealFav ? '' : 'd-none' }}" id="btnRemoveFav" onclick="removeUploadedMedia('fav')" title="Remove Favicon">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>

                                    <div class="flex-grow-1">
                                        <input type="file" name="site_icon" id="favInput" class="form-control form-control-sm mb-1" accept="image/*">
                                        <small class="text-muted d-block" style="font-size: 11px;">Appears on browser tabs and bookmarks (PNG, ICO).</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Titles & Header Display Preference -->
                        <div class="col-12 col-lg-6">
                            <h6 class="section-title mb-3"><i class="fas fa-eye text-primary me-2"></i> Site Identity &amp; Visibility</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">Site Title</label>
                                <input type="text" name="site_title" class="form-control" value="{{ $settings['site_title'] ?? '' }}" placeholder="Company or Store Name">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tagline</label>
                                <input type="text" name="site_tagline" class="form-control" value="{{ $settings['site_tagline'] ?? '' }}" placeholder="Store short tagline or motto">
                            </div>

                            <label class="form-label d-block mb-2">Header Display Preference</label>
                            @php $displayMode = $settings['header_display_mode'] ?? 'logo_title'; @endphp
                            <div class="d-flex flex-column gap-2 mb-2">
                                <label class="display-choice-card d-flex align-items-center gap-3 m-0">
                                    <input type="radio" name="header_display_mode" value="logo_only" class="form-check-input mt-0 flex-shrink-0" {{ $displayMode === 'logo_only' ? 'checked' : '' }}>
                                    <div class="choice-label">
                                        <div class="small fw-bold text-dark choice-title" style="font-size: 12.5px;">Only Logo</div>
                                        <small class="text-muted d-block" style="font-size: 11px;">Shows clean logo image without text in header.</small>
                                    </div>
                                </label>

                                <label class="display-choice-card d-flex align-items-center gap-3 m-0">
                                    <input type="radio" name="header_display_mode" value="logo_title" class="form-check-input mt-0 flex-shrink-0" {{ $displayMode === 'logo_title' ? 'checked' : '' }}>
                                    <div class="choice-label">
                                        <div class="small fw-bold text-dark choice-title" style="font-size: 12.5px;">Logo &amp; Site Title</div>
                                        <small class="text-muted d-block" style="font-size: 11px;">Displays brand logo accompanied by Title text.</small>
                                    </div>
                                </label>

                                <label class="display-choice-card d-flex align-items-center gap-3 m-0">
                                    <input type="radio" name="header_display_mode" value="logo_title_tagline" class="form-check-input mt-0 flex-shrink-0" {{ $displayMode === 'logo_title_tagline' ? 'checked' : '' }}>
                                    <div class="choice-label">
                                        <div class="small fw-bold text-dark choice-title" style="font-size: 12.5px;">Logo, Title &amp; Tagline</div>
                                        <small class="text-muted d-block" style="font-size: 11px;">Full branding layout with Title and Subtitle.</small>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= 2. Layout & Store Setup ================= -->
                <div class="tab-pane fade" id="tab-layout" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-12 col-sm-6 col-md-4">
                            <label class="form-label">Timezone (Global Country Match)</label>
                            <select name="timezone" class="form-select" style="font-size: 12px;">
                                @foreach($allTimezones as $tz)
                                    <option value="{{ $tz }}" {{ $currentTimezone === $tz ? 'selected' : '' }}>
                                        {{ $tz }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted" style="font-size: 10.5px;">Select your store's country timezone. Affects orders, invoices, and scheduling.</small>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <label class="form-label">Date Format</label>
                            <select name="date_format" class="form-select">
                                <option value="d-m-Y" {{ ($settings['date_format'] ?? '') == 'd-m-Y' ? 'selected' : '' }}>DD-MM-YYYY</option>
                                <option value="d M, Y" {{ ($settings['date_format'] ?? '') == 'd M, Y' ? 'selected' : '' }}>DD Mon, YYYY</option>
                                <option value="Y-m-d" {{ ($settings['date_format'] ?? '') == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                                <option value="m/d/Y" {{ ($settings['date_format'] ?? '') == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY (US)</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-12 col-md-4">
                            <label class="form-label">Products Per Page</label>
                            <input type="number" name="products_per_page" class="form-control font-monospace bn-digit" value="{{ $settings['products_per_page'] ?? '16' }}">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Mobile Grid (Cols)</label>
                            <input type="number" name="grid_mobile" class="form-control font-monospace bn-digit" value="{{ $settings['grid_mobile'] ?? '2' }}" min="1" max="4">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Tablet Grid (Cols)</label>
                            <input type="number" name="grid_tab" class="form-control font-monospace bn-digit" value="{{ $settings['grid_tab'] ?? '3' }}" min="1" max="6">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Desktop Grid (Cols)</label>
                            <input type="number" name="grid_pc" class="form-control font-monospace bn-digit" value="{{ $settings['grid_pc'] ?? '4' }}" min="1" max="8">
                        </div>
                    </div>
                </div>

                <!-- ================= 3. SEO & Analytics ================= -->
                <div class="tab-pane fade" id="tab-seo" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">SEO Meta Title</label>
                            <input type="text" name="seo_meta_title" class="form-control" value="{{ $settings['seo_meta_title'] ?? '' }}" placeholder="Search engine title tag">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">SEO Meta Keywords</label>
                            <input type="text" name="seo_meta_keywords" class="form-control" value="{{ $settings['seo_meta_keywords'] ?? '' }}" placeholder=" online shop, clothes, fashion">
                        </div>
                        <div class="col-12">
                            <label class="form-label">SEO Meta Description</label>
                            <textarea name="seo_meta_description" class="form-control" rows="3" placeholder="Brief summary of your website for search engines...">{{ $settings['seo_meta_description'] ?? '' }}</textarea>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Google Site Verification</label>
                            <input type="text" name="google_site_verification" class="form-control font-monospace" value="{{ $settings['google_site_verification'] ?? '' }}" placeholder="Google verification code">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Facebook Domain Verification</label>
                            <input type="text" name="facebook_site_verification" class="form-control font-monospace" value="{{ $settings['facebook_site_verification'] ?? '' }}" placeholder="Facebook verification code">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Facebook Pixel Script</label>
                            <textarea name="facebook_pixel_script" class="form-control font-monospace" rows="3" placeholder="Paste full Facebook Pixel script here...">{{ $settings['facebook_pixel_script'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal: Notice / Alert Dialog -->
<div class="modal fade" id="siteIdentityNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 44px; height: 44px;">
                    <i class="fas fa-exclamation-triangle fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="siteIdentityNoticeTitle" style="font-size: 14px;">Notice</h6>
                <p class="text-muted small mb-3" id="siteIdentityNoticeMsg" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showSiteIdentityNotice(message, title = 'Notice') {
        const titleEl = document.getElementById('siteIdentityNoticeTitle');
        const msgEl = document.getElementById('siteIdentityNoticeMsg');
        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;
        const modalEl = document.getElementById('siteIdentityNoticeModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function extractMediaItem(mediaInput) {
        if (!mediaInput) return null;
        const item = Array.isArray(mediaInput) ? mediaInput[0] : mediaInput;
        if (!item || typeof item !== 'object') return null;
        const rawPath = item.file_path || item.path || item.url || '';
        const rawUrl  = item.url || item.file_path || item.path || '';
        if (!rawPath || rawPath === 'undefined') return null;
        return {
            path: String(rawPath).trim(),
            url:  String(rawUrl).trim()
        };
    }

    function bindPreview(inputId, imgId, removeBtnId, mediaInputId, removeFlagId) {
        const input = document.getElementById(inputId);
        const img = document.getElementById(imgId);
        const removeBtn = document.getElementById(removeBtnId);

        if (input && img) {
            input.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    img.src = URL.createObjectURL(file);
                    if (removeBtn) removeBtn.classList.remove('d-none');
                    if (mediaInputId) document.getElementById(mediaInputId).value = '';
                    if (removeFlagId) document.getElementById(removeFlagId).value = '0';
                }
            });
        }
    }

    bindPreview('headerLogoInput', 'logoPreview', 'btnRemoveLogo', 'headerLogoMediaInput', 'removeHeaderLogo');
    bindPreview('favInput', 'favPreview', 'btnRemoveFav', 'siteIconMediaInput', 'removeSiteIcon');

    function chooseHeaderLogoMedia() {
        if (typeof window.openMediaPicker === 'function') {
            window.openMediaPicker({
                multiple: false,
                onSelect: function(media) {
                    const picked = extractMediaItem(media);
                    if (picked && picked.path) {
                        document.getElementById('headerLogoMediaInput').value = picked.path;
                        const fileInput = document.getElementById('headerLogoInput');
                        if (fileInput) fileInput.value = '';
                        
                        const img = document.getElementById('logoPreview');
                        if (img) img.src = picked.url;
                        
                        const removeBtn = document.getElementById('btnRemoveLogo');
                        if (removeBtn) removeBtn.classList.remove('d-none');
                        
                        document.getElementById('removeHeaderLogo').value = '0';
                    }
                }
            });
        } else {
            showSiteIdentityNotice('Media Library is loading, please try again.');
        }
    }

    function chooseSiteIconMedia() {
        if (typeof window.openMediaPicker === 'function') {
            window.openMediaPicker({
                multiple: false,
                onSelect: function(media) {
                    const picked = extractMediaItem(media);
                    if (picked && picked.path) {
                        document.getElementById('siteIconMediaInput').value = picked.path;
                        const fileInput = document.getElementById('favInput');
                        if (fileInput) fileInput.value = '';
                        
                        const img = document.getElementById('favPreview');
                        if (img) img.src = picked.url;
                        
                        const removeBtn = document.getElementById('btnRemoveFav');
                        if (removeBtn) removeBtn.classList.remove('d-none');
                        
                        document.getElementById('removeSiteIcon').value = '0';
                    }
                }
            });
        } else {
            showSiteIdentityNotice('Media Library is loading, please try again.');
        }
    }

    function removeUploadedMedia(type) {
        if (type === 'logo') {
            document.getElementById('logoPreview').src = 'https://placehold.co/150x50?text=No+Logo';
            document.getElementById('headerLogoInput').value = '';
            document.getElementById('headerLogoMediaInput').value = '';
            document.getElementById('removeHeaderLogo').value = '1';
            const btn = document.getElementById('btnRemoveLogo');
            if (btn) btn.classList.add('d-none');
        } else if (type === 'fav') {
            document.getElementById('favPreview').src = 'https://placehold.co/40x40?text=Icon';
            document.getElementById('favInput').value = '';
            document.getElementById('siteIconMediaInput').value = '';
            document.getElementById('removeSiteIcon').value = '1';
            const btn = document.getElementById('btnRemoveFav');
            if (btn) btn.classList.add('d-none');
        }
    }
</script>
@endsection