@extends('layouts.admin')

@section('title', 'Topbar Settings - ' . config('app.name'))

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #topbarSettingsRoot .btn,
    #topbarSettingsRoot .btn-sm,
    #topbarSettingsRoot button,
    #topbarSettingsRoot .form-control,
    #topbarSettingsRoot .badge,
    #topbarSettingsRoot .alert,
    #topbarSettingsRoot .card,
    #topbarSettingsRoot .topbar-header-card,
    #topbarSettingsRoot .topbar-section-card,
    #topbarSettingsRoot .status-kpi-pill,
    #topbarSettingsRoot .toggle-item-box,
    #topbarSettingsRoot .color-sync-card,
    #topbarSettingsRoot .notice-item-card,
    #topbarSettingsRoot .collapse-toggle-btn,
    .topbar-modal-scope .modal-content,
    .topbar-modal-scope .btn,
    .topbar-modal-scope .form-control,
    .topbar-modal-scope .badge {
        border-radius: 5px !important;
    }

    #topbarSettingsRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #topbarSettingsRoot .input-group > .form-control {
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
        font-size: 11.5px !important;
        padding: 5px 14px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    /* Square Delete Button */
    .btn-notice-delete-sq {
        width: 28px !important;
        height: 28px !important;
        min-width: 28px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 11px !important;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #ef4444;
        transition: all 0.15s ease;
    }
    .btn-notice-delete-sq:hover {
        background: #ef4444;
        color: #ffffff;
        border-color: #ef4444;
    }

    /* Enterprise Surface Header Card */
    .topbar-header-card {
        background: #0f172a;
        color: #ffffff;
        padding: 14px 18px;
        border: 1px solid #1e293b;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        margin-bottom: 14px;
    }

    /* Section Cards */
    .topbar-section-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 14px;
        overflow: hidden;
    }
    .topbar-section-header {
        padding: 11px 16px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .section-title-text {
        font-size: 12.5px;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Symmetric Mini KPI Pill */
    .status-kpi-pill {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 10px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        height: 100%;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
    }

    /* Toggle Item Box */
    .toggle-item-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 10px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        transition: all 0.15s ease;
        height: 100%;
    }
    .toggle-item-box:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }

    /* Two-Way Color Sync Card */
    .color-sync-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        padding: 4px 8px;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .color-sync-card:focus-within {
        border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12);
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
    .color-code-input {
        border: none;
        border-left: 1px solid #e2e8f0;
        padding: 3px 8px;
        font-family: ui-monospace, SFMono-Regular, monospace;
        font-size: 11.5px;
        outline: none;
        width: 100%;
        background: transparent;
        color: #0f172a;
        font-weight: 600;
    }

    /* Notice Item Card */
    .notice-item-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .notice-icon-input {
        max-width: 150px;
        font-family: ui-monospace, SFMono-Regular, monospace;
        font-size: 11.5px;
    }
    .notice-text-input {
        font-size: 11.5px;
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

    /* Form Controls */
    .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #topbarSettingsRoot .form-control,
    .topbar-modal-scope .form-control {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #topbarSettingsRoot .form-control:focus,
    .topbar-modal-scope .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }
    textarea.form-control {
        min-height: 70px;
    }

    /* Enhanced Toggle Switches */
    .form-switch .form-check-input {
        width: 36px;
        height: 18px;
        cursor: pointer;
        border-color: #cbd5e1;
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }
    .form-switch .form-check-input:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }
    .form-check-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #1e293b;
        cursor: pointer;
        user-select: none;
    }

    /* Mobile Responsive (< 768px) */
    @media (max-width: 767.98px) {
        .topbar-header-card { padding: 12px 14px !important; }
        .header-top-wrap {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 8px !important;
        }
        .top-action-btns {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .top-action-btns .btn {
            width: 100%;
            justify-content: center;
        }
        .topbar-section-header { padding: 10px 12px; }
        .notice-item-card {
            flex-direction: column;
            align-items: stretch;
            gap: 6px;
            padding: 8px;
        }
        .notice-icon-input {
            max-width: 100%;
        }
        .btn-notice-delete-sq {
            width: 100% !important;
            height: 30px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-2" id="topbarSettingsRoot">

    <!-- 1. Top Header & Quick Controls -->
    <div class="topbar-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 header-top-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase" style="font-size: 9px;">
                    <i class="fas fa-bullhorn me-1"></i> STOREFRONT RIBBON
                </span>
                <span class="badge bg-light text-white-50 border border-secondary font-monospace" style="font-size: 9px;">
                    LIVE BROADCAST
                </span>
            </div>
            <h5 class="fw-bold mb-0 d-flex align-items-center gap-2" style="font-size: 17px;">
                <i class="fas fa-bullhorn text-primary"></i>
                <span>Topbar Notification Ribbon Settings</span>
            </h5>
            <small class="text-white-50 d-block mt-1" style="font-size: 11.5px; line-height: 1.35;">
                Configure announcement ribbon visibility, two-way color palette, sliding ticker and fallback content.
            </small>
        </div>

        <div class="top-action-btns d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-compact text-white border-white-50 shadow-xs" onclick="toggleAllTopbarSections()">
                <i class="fas fa-arrows-up-down me-1"></i>
                <span id="expandAllTopbarBtnText">Collapse All</span>
            </button>
            <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold px-3" onclick="document.getElementById('topbarSettingsForm').submit()">
                <i class="fas fa-check-circle me-1"></i>
                <span>Save Topbar Settings</span>
            </button>
        </div>
    </div>

    <!-- 2. Status Summary KPI Row (Symmetric 3-Column Grid) -->
    @php
        $desktopOn = ($settings['show_topbar_desktop'] ?? '1') == '1';
        $mobileOn  = ($settings['show_topbar_mobile'] ?? '1') == '1';
        $noticesCount = count($notices ?? []);
    @endphp

    <div class="row g-2 mb-3">
        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 10.5px;">Desktop Screen View</small>
                    <strong class="text-{{ $desktopOn ? 'success' : 'secondary' }} d-block" style="font-size: 13px;">
                        {{ $desktopOn ? 'Enabled (Active)' : 'Disabled (Hidden)' }}
                    </strong>
                </div>
                <div class="text-{{ $desktopOn ? 'success' : 'secondary' }} fs-5">
                    <i class="fas fa-laptop"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 10.5px;">Mobile &amp; Tablet Screen</small>
                    <strong class="text-{{ $mobileOn ? 'success' : 'secondary' }} d-block" style="font-size: 13px;">
                        {{ $mobileOn ? 'Enabled (Active)' : 'Disabled (Hidden)' }}
                    </strong>
                </div>
                <div class="text-{{ $mobileOn ? 'success' : 'secondary' }} fs-5">
                    <i class="fas fa-mobile-alt"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 10.5px;">Ticker Notices Queue</small>
                    <strong class="text-primary d-block font-monospace bn-digit" style="font-size: 13px;">
                        {{ $noticesCount }} Active {{ Str::plural('Notice', $noticesCount) }}
                    </strong>
                </div>
                <div class="text-primary fs-5">
                    <i class="fas fa-list-check"></i>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-xs py-2 px-3 mb-2 small d-flex align-items-center justify-content-between" style="border-radius: 5px;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.theme.setting.topbar.update') }}" method="POST" id="topbarSettingsForm">
        @csrf

        <!-- Section 1: Display Visibility Rules (COLLAPSIBLE) -->
        <div class="topbar-section-card">
            <div class="topbar-section-header d-flex justify-content-between align-items-center">
                <div class="section-title-text">
                    <i class="fas fa-eye text-primary"></i>
                    <span>Display Visibility &amp; Device Matrix</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase" style="font-size: 8.5px;">DEVICE ACCESS</span>
                    <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseVisibilitySection" aria-expanded="true" aria-controls="collapseVisibilitySection" title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseVisibilitySection">
                <div class="p-3 bg-light bg-opacity-25">
                    <div class="row g-2">
                        <!-- Desktop View Toggle -->
                        <div class="col-12 col-md-6">
                            <div class="toggle-item-box">
                                <div class="pe-2">
                                    <label class="form-check-label d-block fw-bold text-dark" for="sw_show_desktop" style="font-size: 12px; cursor: pointer;">
                                        Desktop Monitor View
                                    </label>
                                    <small class="text-muted d-block font-monospace" style="font-size: 10.5px;">
                                        Display notification ribbon across standard laptop and desktop screens.
                                    </small>
                                </div>
                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox" name="show_topbar_desktop" id="sw_show_desktop" value="1" {{ ($settings['show_topbar_desktop'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile View Toggle -->
                        <div class="col-12 col-md-6">
                            <div class="toggle-item-box">
                                <div class="pe-2">
                                    <label class="form-check-label d-block fw-bold text-dark" for="sw_show_mobile" style="font-size: 12px; cursor: pointer;">
                                        Mobile &amp; Tablet Handheld View
                                    </label>
                                    <small class="text-muted d-block font-monospace" style="font-size: 10.5px;">
                                        Display compact announcement ribbon on handheld touch devices.
                                    </small>
                                </div>
                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox" name="show_topbar_mobile" id="sw_show_mobile" value="1" {{ ($settings['show_topbar_mobile'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Ribbon Palette & Color Controls (COLLAPSIBLE) -->
        <div class="topbar-section-card">
            <div class="topbar-section-header d-flex justify-content-between align-items-center">
                <div class="section-title-text">
                    <i class="fas fa-palette text-primary"></i>
                    <span>Ribbon Color Palette &amp; Contrast</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase" style="font-size: 8.5px;">PALETTE SYNC</span>
                    <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseColorsSection" aria-expanded="true" aria-controls="collapseColorsSection" title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseColorsSection">
                <div class="p-3 bg-light bg-opacity-25">
                    <div class="row g-2">
                        <!-- Background Color -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Background Color</label>
                            <div class="color-sync-card">
                                <input type="color" id="topbarBgPicker" class="color-picker-input" value="{{ $settings['topbar_bg'] ?? '#0066ff' }}" oninput="syncColorValues(this, 'topbarBgText')">
                                <input type="text" id="topbarBgText" name="topbar_bg" class="color-code-input" value="{{ $settings['topbar_bg'] ?? '#0066ff' }}" placeholder="#0066ff" oninput="syncTextValues(this, 'topbarBgPicker')">
                            </div>
                        </div>

                        <!-- Text Color -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Text &amp; Icon Color</label>
                            <div class="color-sync-card">
                                <input type="color" id="topbarColorPicker" class="color-picker-input" value="{{ $settings['topbar_color'] ?? '#ffffff' }}" oninput="syncColorValues(this, 'topbarColorText')">
                                <input type="text" id="topbarColorText" name="topbar_color" class="color-code-input" value="{{ $settings['topbar_color'] ?? '#ffffff' }}" placeholder="#ffffff" oninput="syncTextValues(this, 'topbarColorPicker')">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Text Slider Animation Control (COLLAPSIBLE) -->
        <div class="topbar-section-card">
            <div class="topbar-section-header d-flex justify-content-between align-items-center">
                <div class="section-title-text">
                    <i class="fas fa-sliders-h text-warning"></i>
                    <span>Text Slider Ticker Animation Control</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace text-uppercase" style="font-size: 8.5px;">TICKER MOTION</span>
                    <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseAnimationSection" aria-expanded="true" aria-controls="collapseAnimationSection" title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseAnimationSection">
                <div class="p-3 bg-light bg-opacity-25">
                    <div class="row g-2">
                        <!-- Desktop Slider Toggle -->
                        <div class="col-12 col-md-6">
                            <div class="toggle-item-box">
                                <div class="pe-2">
                                    <label class="form-check-label d-block fw-bold text-dark" for="sw_slider_desktop" style="font-size: 12px; cursor: pointer;">
                                        Desktop Screen Slider
                                    </label>
                                    <small class="text-muted d-block font-monospace" style="font-size: 10.5px;">
                                        Enables auto-sliding announcement ticker on widescreen monitors.
                                    </small>
                                </div>
                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox" name="topbar_slider_desktop" id="sw_slider_desktop" value="1" {{ ($settings['topbar_slider_desktop'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Slider Toggle -->
                        <div class="col-12 col-md-6">
                            <div class="toggle-item-box">
                                <div class="pe-2">
                                    <label class="form-check-label d-block fw-bold text-dark" for="sw_slider_mobile" style="font-size: 12px; cursor: pointer;">
                                        Mobile Screen Slider
                                    </label>
                                    <small class="text-muted d-block font-monospace" style="font-size: 10.5px;">
                                        Enables smooth ticker cycling on phone and tablet devices.
                                    </small>
                                </div>
                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox" name="topbar_slider_mobile" id="sw_slider_mobile" value="1" {{ ($settings['topbar_slider_mobile'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: Slider Notice Items & Announcements (COLLAPSIBLE) -->
        <div class="topbar-section-card">
            <div class="topbar-section-header d-flex justify-content-between align-items-center">
                <div class="section-title-text">
                    <i class="fas fa-list-check text-primary"></i>
                    <span>Slider Notice Items &amp; Announcements</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <button type="button" class="btn btn-primary btn-compact py-0 px-2 shadow-xs" style="height: 24px; font-size: 10px !important;" data-bs-toggle="modal" data-bs-target="#addNoticeModal">
                        <i class="fas fa-plus me-1"></i> Add Notice
                    </button>
                    <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseNoticesSection" aria-expanded="true" aria-controls="collapseNoticesSection" title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseNoticesSection">
                <div class="p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted font-monospace" style="font-size: 10.5px;">
                            Announcement queue items (displayed sequentially in the ticker):
                        </small>
                        <span class="badge bg-light text-dark border font-monospace bn-digit px-1.5 py-0" style="font-size: 9px;" id="noticesCountBadge">
                            {{ count($notices) }} Items
                        </span>
                    </div>

                    <div id="noticesContainer">
                        @foreach($notices as $item)
                            <div class="notice-item-card notice-item-row shadow-xs">
                                <input type="text" name="notice_icons[]" class="form-control notice-icon-input" value="{{ $item['icon'] ?? 'fas fa-check-circle' }}" placeholder="fas fa-bullhorn" title="FontAwesome Icon Class">
                                <input type="text" name="notice_texts[]" class="form-control notice-text-input" value="{{ $item['text'] }}" placeholder="Write announcement notice text here..." required>
                                <button type="button" class="btn-notice-delete-sq shadow-xs flex-shrink-0" onclick="confirmDeleteNotice(this)" title="Remove item">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 5: Static HTML / Text Fallback (COLLAPSIBLE) -->
        <div class="topbar-section-card">
            <div class="topbar-section-header d-flex justify-content-between align-items-center">
                <div class="section-title-text">
                    <i class="fas fa-code text-secondary"></i>
                    <span>Static HTML / Text Content Fallback</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-secondary-subtle text-secondary border font-monospace text-uppercase" style="font-size: 8.5px;">STATIC MODE</span>
                    <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseFallbackSection" aria-expanded="true" aria-controls="collapseFallbackSection" title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseFallbackSection">
                <div class="p-3 bg-light bg-opacity-25">
                    <small class="text-muted d-block mb-2 font-monospace" style="font-size: 10.5px;">
                        This static dual-column content displays whenever the animated slider ticker is turned OFF.
                    </small>

                    <div class="row g-2">
                        <!-- Left Column HTML -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Left Column Content (HTML Allowed)</label>
                            <textarea name="topbar_left_html" class="form-control font-monospace" rows="3" placeholder="Left contact hotline or HTML tags...">{{ $settings['topbar_left_html'] ?? '' }}</textarea>
                        </div>

                        <!-- Right Column HTML -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Right Column Content (HTML Allowed)</label>
                            <textarea name="topbar_right_html" class="form-control font-monospace" rows="3" placeholder="Right links or social handles...">{{ $settings['topbar_right_html'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Action Bar -->
        <div class="d-flex justify-content-end pt-2 pb-3">
            <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-4 w-100 w-md-auto" style="min-height: 35px; font-size: 12px;">
                <i class="fas fa-check-circle me-1"></i> Save Topbar Settings
            </button>
        </div>
    </form>
</div>

<!-- ================= MODAL: ADD NOTICE ITEM ================= -->
<div class="modal fade topbar-modal-scope" id="addNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-1 mb-0" style="font-size: 13.5px;">
                    <i class="fas fa-plus-circle text-primary"></i> <span>Add Announcement</span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-3">
                <div class="mb-2">
                    <label class="form-label">FontAwesome Icon Class</label>
                    <input type="text" id="modalNoticeIcon" class="form-control font-monospace" placeholder="fas fa-check-circle" value="fas fa-check-circle">
                    <small class="text-muted font-monospace" style="font-size: 10px;">e.g. <code>fas fa-truck</code>, <code>fas fa-bolt</code>, <code>fas fa-headset</code></small>
                </div>
                <div>
                    <label class="form-label">Announcement Text *</label>
                    <textarea id="modalNoticeText" class="form-control" rows="3" placeholder="Enter notice announcement text here..." required></textarea>
                </div>
            </div>

            <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between">
                <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold px-3" onclick="addNoticeFromModal()">Add to List</button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL: DELETE NOTICE CONFIRMATION ================= -->
<div class="modal fade topbar-modal-scope" id="deleteNoticeConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2 text-danger">
                    <i class="fas fa-trash-can fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Remove Notice Item?</h6>
                <p class="text-muted small mb-2" style="font-size: 12px; line-height: 1.45;">
                    Are you sure you want to remove <strong id="deleteNoticePreviewText" class="text-dark">this notice</strong>?
                </p>
                
                <!-- Guard Warning: At least 1 notice required -->
                <div id="deleteNoticeWarning" class="alert alert-warning border-0 py-1 px-2 mt-2 mb-2 small d-none" style="font-size: 10.5px;">
                    <i class="fas fa-exclamation-triangle me-1"></i> You must keep at least one announcement item.
                </div>

                <div class="d-flex justify-content-center gap-2 pt-2">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmDeleteNoticeBtn" class="btn btn-danger btn-compact shadow-xs fw-bold" onclick="executeNoticeDelete()">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL: CUSTOM NOTICE / ALERT ================= -->
<div class="modal fade topbar-modal-scope" id="topbarNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2" id="topbarNoticeIconWrap">
                    <i class="fas fa-circle-check text-success fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="topbarNoticeTitle" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="topbarNoticeMessage" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-primary btn-compact px-4" data-bs-dismiss="modal">Acknowledge</button>
            </div>
        </div>
    </div>
</div>

<script>
// Universal Notice Modal (Replaces native browser alert)
function showTopbarModalNotice(message, title = 'Notice', type = 'info') {
    const titleEl = document.getElementById('topbarNoticeTitle');
    const msgEl   = document.getElementById('topbarNoticeMessage');
    const iconEl  = document.getElementById('topbarNoticeIconWrap');

    if (titleEl) titleEl.innerText = title;
    if (msgEl) msgEl.innerText = message;

    if (iconEl) {
        if (type === 'danger' || type === 'error') {
            iconEl.innerHTML = '<i class="fas fa-circle-xmark text-danger fa-2x"></i>';
        } else if (type === 'success') {
            iconEl.innerHTML = '<i class="fas fa-circle-check text-success fa-2x"></i>';
        } else if (type === 'warning') {
            iconEl.innerHTML = '<i class="fas fa-triangle-exclamation text-warning fa-2x"></i>';
        } else {
            iconEl.innerHTML = '<i class="fas fa-circle-info text-primary fa-2x"></i>';
        }
    }

    const modalEl = document.getElementById('topbarNoticeModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

// Two-way Color Sync Handlers
function syncColorValues(colorPicker, textInputId) {
    const textInput = document.getElementById(textInputId);
    if (textInput) {
        textInput.value = colorPicker.value.toLowerCase();
    }
}

function syncTextValues(textInput, colorPickerId) {
    const colorPicker = document.getElementById(colorPickerId);
    let val = textInput.value.trim();
    if (!val.startsWith('#') && val.length > 0) {
        val = '#' + val;
    }
    const hexPattern = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/;
    if (hexPattern.test(val) && colorPicker) {
        colorPicker.value = val;
    }
}

// Add Notice From Modal
function addNoticeFromModal() {
    const iconInput = document.getElementById('modalNoticeIcon');
    const textInput = document.getElementById('modalNoticeText');
    const textVal = textInput.value.trim();
    const iconVal = iconInput.value.trim() || 'fas fa-check-circle';

    if (!textVal) {
        textInput.focus();
        showTopbarModalNotice('Please provide announcement text before saving.', 'Required Field', 'warning');
        return;
    }

    const container = document.getElementById('noticesContainer');
    const newRow = document.createElement('div');
    newRow.className = 'notice-item-card notice-item-row shadow-xs';
    newRow.innerHTML = `
        <input type="text" name="notice_icons[]" class="form-control notice-icon-input" value="${iconVal}" placeholder="fas fa-tag">
        <input type="text" name="notice_texts[]" class="form-control notice-text-input" value="${textVal}" placeholder="Write announcement notice text here..." required>
        <button type="button" class="btn-notice-delete-sq shadow-xs flex-shrink-0" onclick="confirmDeleteNotice(this)" title="Remove item">
            <i class="fas fa-trash-alt"></i>
        </button>
    `;

    // Prepend to top of container
    container.insertBefore(newRow, container.firstChild);
    updateNoticeItemCount();

    // Reset & close modal
    textInput.value = '';
    const modalElem = document.getElementById('addNoticeModal');
    if (modalElem && window.bootstrap) {
        bootstrap.Modal.getInstance(modalElem)?.hide();
    }
    showTopbarModalNotice('New announcement notice item added to the ticker queue.', 'Item Added', 'success');
}

// Delete Confirmation Modal Handling
let targetNoticeRow = null;

function confirmDeleteNotice(btn) {
    targetNoticeRow = btn.closest('.notice-item-row');
    const textInput = targetNoticeRow ? targetNoticeRow.querySelector('.notice-text-input') : null;
    const previewText = textInput && textInput.value.trim() ? '"' + textInput.value.trim() + '"' : 'this notice';
    
    const previewElem = document.getElementById('deleteNoticePreviewText');
    if (previewElem) {
        previewElem.textContent = previewText.length > 32 ? previewText.substring(0, 32) + '..."' : previewText;
    }

    const rows = document.querySelectorAll('.notice-item-row');
    const warningAlert = document.getElementById('deleteNoticeWarning');
    const confirmBtn = document.getElementById('confirmDeleteNoticeBtn');

    if (rows.length <= 1) {
        if (warningAlert) warningAlert.classList.remove('d-none');
        if (confirmBtn) confirmBtn.classList.add('d-none');
    } else {
        if (warningAlert) warningAlert.classList.add('d-none');
        if (confirmBtn) confirmBtn.classList.remove('d-none');
    }

    const deleteModal = new bootstrap.Modal(document.getElementById('deleteNoticeConfirmModal'));
    deleteModal.show();
}

function executeNoticeDelete() {
    const rows = document.querySelectorAll('.notice-item-row');
    if (rows.length > 1 && targetNoticeRow) {
        targetNoticeRow.remove();
        targetNoticeRow = null;
        updateNoticeItemCount();
        showTopbarModalNotice('Notice item removed from ticker queue.', 'Deleted', 'info');
    }
    const modalElem = document.getElementById('deleteNoticeConfirmModal');
    if (modalElem && window.bootstrap) {
        bootstrap.Modal.getInstance(modalElem)?.hide();
    }
}

function updateNoticeItemCount() {
    const count = document.querySelectorAll('.notice-item-row').length;
    const badge = document.getElementById('noticesCountBadge');
    if (badge) {
        badge.innerText = count + ' Items';
    }
}

// Toggle All Topbar Sections (Expand All / Collapse All)
let allTopbarSectionsExpanded = true;
function toggleAllTopbarSections() {
    const collapses = document.querySelectorAll('#topbarSettingsRoot .topbar-section-card .collapse');
    const toggleBtns = document.querySelectorAll('#topbarSettingsRoot .topbar-section-card .collapse-toggle-btn');
    const btnText = document.getElementById('expandAllTopbarBtnText');

    allTopbarSectionsExpanded = !allTopbarSectionsExpanded;

    collapses.forEach(col => {
        if (allTopbarSectionsExpanded) {
            bootstrap.Collapse.getOrCreateInstance(col, { toggle: false }).show();
        } else {
            bootstrap.Collapse.getOrCreateInstance(col, { toggle: false }).hide();
        }
    });

    toggleBtns.forEach(btn => {
        btn.setAttribute('aria-expanded', allTopbarSectionsExpanded ? 'true' : 'false');
    });

    if (btnText) {
        btnText.innerText = allTopbarSectionsExpanded ? 'Collapse All' : 'Expand All';
    }
}
</script>
@endsection