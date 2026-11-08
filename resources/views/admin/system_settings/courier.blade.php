
@extends('layouts.admin')

@section('title', 'Courier Configuration - ' . config('app.name'))

@php
    $resolveCourierLogo = function($path, $fallback = 'https://placehold.co/120x60?text=Courier') {
        if (empty($path) || $path === 'undefined' || $path === 'null') return $fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) {
            return show_image($path, $fallback);
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset(ltrim($path, '/'));
    };
@endphp

@section('admin_content')

<style>
    /* =========================================================
       KAIADMIN LITE - CLASSIC WHITE DESIGN
       Courier Configuration - Page Scoped Styles
    ========================================================= */

    #courierConfigRoot {
        --courier-primary: var(--bs-primary, #1572e8);
        --courier-primary-soft: rgba(21, 114, 232, .08);
        --courier-success: #31ce36;
        --courier-warning: #ffad46;
        --courier-danger: #f25961;
        --courier-info: #48abf7;

        --courier-body: #f8f9fa;
        --courier-surface: #ffffff;
        --courier-soft: #f8f9fc;
        --courier-heading: #2a2f5b;
        --courier-text: #495057;
        --courier-muted: #8d9498;
        --courier-border: #e6e8eb;

        color: var(--courier-text);
        font-size: 13px;
        min-width: 0;
        width: 100%;
    }

    #courierConfigRoot *,
    #courierNoticeModal * {
        box-sizing: border-box;
    }

    /* Consistent Radius */

    #courierConfigRoot .btn,
    #courierConfigRoot .form-control,
    #courierConfigRoot .form-select,
    #courierConfigRoot .badge,
    #courierConfigRoot .alert,
    #courierConfigRoot .courier-collapsible-card,
    #courierConfigRoot .courier-media-chooser-surface,
    #courierNoticeModal .btn,
    #courierNoticeModal .modal-content {
        border-radius: 5px !important;
    }

    #courierConfigRoot .input-group > .input-group-text {
        border-radius: 5px 0 0 5px !important;
    }

    #courierConfigRoot .input-group > .form-control {
        border-radius: 0 !important;
    }

    #courierConfigRoot .input-group > .btn {
        border-radius: 0 5px 5px 0 !important;
    }

    /* Main Container */

    #courierConfigRoot {
        padding-top: 12px !important;
        padding-bottom: 20px !important;
    }

    /* =========================================================
       PAGE HEADER
    ========================================================= */

    #courierConfigRoot .courier-hero-header {
        background: var(--courier-surface);
        border: 1px solid var(--courier-border);
        border-radius: 5px !important;
        padding: 15px 18px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .025);
        min-width: 0;
    }

    #courierConfigRoot .courier-page-title {
        color: var(--courier-heading);
        font-size: clamp(16px, 1.5vw, 19px);
        font-weight: 700;
        line-height: 1.35;
        letter-spacing: -.25px;
    }

    #courierConfigRoot .courier-page-description {
        color: var(--courier-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    #courierConfigRoot .courier-count-badge {
        font-size: 10.5px;
        font-weight: 700;
        padding: 5px 9px !important;
        white-space: nowrap;
    }

    /* Compact Buttons */

    #courierConfigRoot .btn-compact,
    #courierNoticeModal .btn-compact {
        min-height: 32px;
        padding: 6px 12px !important;
        font-size: 11.5px !important;
        font-weight: 600 !important;
        line-height: 1.35 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 3px;
        white-space: nowrap;
        transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease;
    }

    #courierConfigRoot .btn-compact:focus-visible,
    #courierNoticeModal .btn-compact:focus-visible {
        outline: 2px solid var(--courier-primary);
        outline-offset: 2px;
    }

    #courierConfigRoot .btn-primary {
        background-color: var(--courier-primary);
        border-color: var(--courier-primary);
    }

    #courierConfigRoot .btn-primary:hover {
        filter: brightness(.94);
    }

    /* =========================================================
       QUICK SELECTOR
    ========================================================= */

    #courierConfigRoot .courier-quick-panel {
        background: var(--courier-surface);
        border: 1px solid var(--courier-border);
        border-radius: 5px;
        padding: 11px 13px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .02);
    }

    #courierConfigRoot .courier-quick-panel .form-select {
        min-height: 36px;
    }

    /* =========================================================
       COURIER ACCORDION CARD
    ========================================================= */

    #courierConfigRoot .courier-collapsible-card {
        background: var(--courier-surface);
        border: 1px solid var(--courier-border);
        overflow: hidden;
        min-width: 0;
        height: 100%;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #courierConfigRoot .courier-collapsible-card:hover {
        border-color: #d4d9e0;
    }

    #courierConfigRoot .courier-collapsible-card.active-open {
        border-color: var(--courier-primary);
        box-shadow: 0 2px 8px rgba(21, 114, 232, .055);
    }

    #courierConfigRoot .courier-card-header {
        min-height: 66px;
        padding: 12px 15px;
        background: #ffffff;
        cursor: pointer;
        user-select: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        transition: background-color .15s ease;
    }

    #courierConfigRoot .courier-card-header:hover {
        background: #fafbfd;
    }

    #courierConfigRoot .courier-collapsible-card.active-open .courier-card-header {
        background: #f8faff;
        border-bottom: 1px solid var(--courier-border);
    }

    #courierConfigRoot .courier-card-header > div:first-child {
        min-width: 0;
    }

    #courierConfigRoot .courier-card-header h6 {
        color: var(--courier-heading) !important;
        font-size: 13px !important;
        font-weight: 700;
        line-height: 1.4;
    }

    #courierConfigRoot .courier-card-header small {
        color: var(--courier-muted);
        font-size: 10.5px !important;
    }

    #courierConfigRoot .courier-chevron-icon {
        width: 26px;
        height: 26px;
        flex-shrink: 0;
        border-radius: 5px;
        background: #f5f6fa;
        border: 1px solid var(--courier-border);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: #6c757d;
        transition: transform .2s ease, background-color .15s ease, color .15s ease;
    }

    #courierConfigRoot .active-open .courier-chevron-icon {
        transform: rotate(180deg);
        background: var(--courier-primary);
        border-color: var(--courier-primary);
        color: #ffffff;
    }

    #courierConfigRoot .courier-card-body {
        padding: 16px;
        background: #ffffff;
        min-width: 0;
    }

    #courierConfigRoot .courier-card-footer {
        padding-top: 13px !important;
        margin-top: 3px;
    }

    /* Courier Logo */

    #courierConfigRoot .courier-logo-preview-box {
        width: 54px;
        height: 40px;
        flex-shrink: 0;
        background: #ffffff;
        border: 1px solid var(--courier-border);
        border-radius: 5px;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 3px;
    }

    #courierConfigRoot .courier-logo-preview-box img {
        max-width: 100%;
        max-height: 100%;
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    /* Status Badges */

    #courierConfigRoot .courier-card-header .badge {
        font-size: 9.5px;
        font-weight: 700;
        letter-spacing: .1px;
        padding: 4px 8px !important;
        border: 1px solid transparent;
    }

    /* =========================================================
       FORM CONTROLS
    ========================================================= */

    #courierConfigRoot .form-label {
        display: block;
        color: #495057;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.4;
        margin-bottom: 6px;
    }

    #courierConfigRoot .form-control,
    #courierConfigRoot .form-select {
        min-height: 36px;
        padding: 7px 10px;
        font-size: 12px;
        font-weight: 400;
        color: #495057;
        background-color: #ffffff;
        border: 1px solid #ebedf2;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #courierConfigRoot .form-control::placeholder {
        color: #adb5bd;
        opacity: 1;
    }

    #courierConfigRoot .form-control:focus,
    #courierConfigRoot .form-select:focus {
        border-color: var(--courier-primary);
        box-shadow: 0 0 0 2px rgba(21, 114, 232, .10);
        outline: none;
    }

    #courierConfigRoot .form-control.font-monospace {
        font-size: 11.5px;
        font-weight: 500 !important;
    }

    #courierConfigRoot .input-group .btn {
        min-width: 39px;
        border-color: #ebedf2;
        background: #f8f9fc;
    }

    #courierConfigRoot .input-group .btn:hover {
        background: #eef1f5;
    }

    #courierConfigRoot .form-check-input {
        border-color: #ced4da;
        box-shadow: none;
    }

    #courierConfigRoot .form-check-input:checked {
        background-color: var(--courier-primary);
        border-color: var(--courier-primary);
    }

    #courierConfigRoot .form-check-input:focus {
        border-color: var(--courier-primary);
        box-shadow: 0 0 0 2px rgba(21, 114, 232, .12);
    }

    /* =========================================================
       MEDIA LIBRARY
    ========================================================= */

    #courierConfigRoot .courier-media-chooser-surface {
        background: #fafbfd;
        border: 1px dashed #d5dbe3;
        border-radius: 5px;
        padding: 12px;
        transition: border-color .15s ease, background-color .15s ease;
    }

    #courierConfigRoot .courier-media-chooser-surface:hover {
        border-color: var(--courier-primary);
        background: #f8faff;
    }

    #courierConfigRoot .media-chooser-actions {
        gap: 8px !important;
    }

    #courierConfigRoot .courier-logo-preview-wrap {
        min-width: 0;
        border: 1px solid var(--courier-border);
        border-radius: 5px;
        background: #ffffff;
        padding: 8px;
    }

    #courierConfigRoot .courier-logo-path {
        display: block;
        max-width: 250px;
        overflow-wrap: anywhere;
        font-size: 10px;
    }

    /* =========================================================
       ENABLE GATEWAY
    ========================================================= */

    #courierConfigRoot .courier-enable-control {
        min-height: 48px;
        padding: 10px 13px !important;
        background: #f8f9fc !important;
        border: 1px solid var(--courier-border) !important;
        border-radius: 5px !important;
    }

    #courierConfigRoot .courier-enable-control .form-check-label {
        font-size: 12px;
        font-weight: 600;
        color: #495057;
    }

    /* =========================================================
       WEBHOOK INFORMATION
    ========================================================= */

    #courierConfigRoot .courier-webhook-panel {
        background: #f8faff !important;
        border: 1px solid #e4eaf5 !important;
        border-radius: 5px !important;
        padding: 11px 13px !important;
    }

    #courierConfigRoot .courier-webhook-panel strong {
        font-size: 11.5px !important;
        font-weight: 700;
    }

    #courierConfigRoot .courier-webhook-url {
        font-size: 11px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    /* =========================================================
       BALANCE INFORMATION
    ========================================================= */

    #courierConfigRoot .courier-balance-box {
        border: 1px solid #d8eaf8 !important;
        border-radius: 5px !important;
        background: #f1f8fe;
        min-height: 42px;
    }

    #courierConfigRoot .courier-balance-box strong {
        font-size: 13px;
    }

    /* =========================================================
       ALERTS
    ========================================================= */

    #courierConfigRoot .alert {
        border-radius: 5px !important;
        font-size: 12px;
    }

    /* =========================================================
       NOTICE MODAL
    ========================================================= */

    #courierNoticeModal .modal-content {
        border: 1px solid var(--courier-border) !important;
        border-radius: 6px !important;
        background: #ffffff;
    }

    #courierNoticeModal .modal-body {
        padding: 8px;
    }

    #courierNoticeModal #courierNoticeTitle {
        color: #2a2f5b;
        font-size: 14px;
        font-weight: 700;
    }

    #courierNoticeModal #courierNoticeMsg {
        color: #7d8389;
        font-size: 12px;
        line-height: 1.55;
        overflow-wrap: anywhere;
    }

    /* =========================================================
       UTILITY
    ========================================================= */

    #courierConfigRoot .bn-digit,
    #courierConfigRoot .font-monospace {
        font-weight: 600;
    }

    #courierConfigRoot .min-width-0 {
        min-width: 0;
    }

    #courierConfigRoot .courier-col-item {
        min-width: 0;
    }

    #courierConfigRoot .courier-card-header button {
        position: relative;
        z-index: 1;
    }

    /* =========================================================
       TABLET
    ========================================================= */

    @media (max-width: 991.98px) {
        #courierConfigRoot .courier-card-body {
            padding: 14px;
        }

        #courierConfigRoot .courier-hero-header {
            padding: 13px 15px;
        }
    }

    /* =========================================================
       MOBILE
    ========================================================= */

    @media (max-width: 767.98px) {
        #courierConfigRoot {
            padding-left: 10px !important;
            padding-right: 10px !important;
            padding-top: 10px !important;
        }

        #courierConfigRoot .courier-hero-header {
            padding: 12px;
            gap: 12px !important;
        }

        #courierConfigRoot .courier-page-title {
            font-size: 16px;
        }

        #courierConfigRoot .courier-page-description {
            font-size: 11.5px;
        }

        #courierConfigRoot .courier-card-body {
            padding: 12px;
        }

        #courierConfigRoot .courier-card-header {
            padding: 10px 11px;
            gap: 7px;
        }

        #courierConfigRoot .courier-logo-preview-box {
            width: 47px;
            height: 36px;
        }

        #courierConfigRoot .courier-card-header h6 {
            font-size: 12px !important;
        }

        #courierConfigRoot .courier-card-header small {
            font-size: 10px !important;
        }

        #courierConfigRoot .media-chooser-actions {
            flex-direction: column;
            align-items: stretch !important;
        }

        #courierConfigRoot .media-chooser-actions .btn-compact {
            width: 100%;
            min-height: 36px;
        }

        #courierConfigRoot .courier-logo-preview-wrap {
            flex-wrap: wrap;
        }

        #courierConfigRoot .courier-logo-preview-wrap .text-truncate {
            flex: 1;
            min-width: 0;
        }

        #courierConfigRoot .courier-logo-path {
            max-width: 100%;
        }

        #courierConfigRoot .courier-card-footer {
            flex-direction: row !important;
            justify-content: stretch !important;
            align-items: stretch !important;
        }

        #courierConfigRoot .courier-card-footer .btn {
            width: 100% !important;
            min-height: 38px;
        }

        #courierConfigRoot .courier-quick-panel {
            padding: 10px;
        }

        #courierConfigRoot .courier-quick-panel .btn {
            min-height: 36px;
        }
    }

    /* =========================================================
       SMALL MOBILE
    ========================================================= */

    @media (max-width: 400px) {
        #courierConfigRoot .courier-card-header {
            align-items: flex-start;
        }

        #courierConfigRoot .courier-card-header > div:first-child {
            flex-wrap: wrap;
        }

        #courierConfigRoot .courier-card-header .courier-chevron-icon {
            width: 23px;
            height: 23px;
        }

        #courierConfigRoot .courier-card-header .btn-compact {
            padding: 4px 7px !important;
            font-size: 10px !important;
        }

        #courierConfigRoot .courier-count-badge {
            font-size: 9px;
            padding: 4px 6px !important;
        }

        #courierConfigRoot .courier-page-title {
            font-size: 15px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="courierConfigRoot">

 <!-- Page Header -->
<div class="courier-hero-header d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-3 mb-3 bg-white p-3 border rounded-3 shadow-xs" style="width: 100% !important;">
    <div class="d-flex align-items-center gap-2 min-w-0">
        <div class="fs-3 text-primary">
            <i class="fas fa-truck-ramp-box"></i>
        </div>
        <div class="min-w-0">
            <h5 class="courier-page-title mb-1 fw-bold text-dark text-truncate" style="font-size: 16px;">
                Courier Integration
            </h5>
            <small class="courier-page-description text-secondary d-block mt-0 text-truncate" style="font-size: 11.5px; line-height: 1.35;">
                API &amp; Booking Automation
            </small>
        </div>
    </div>

    <div class="d-flex gap-2 flex-shrink-0 align-self-stretch align-self-md-center">
        <button type="button" class="btn btn-light border btn-compact px-3 flex-fill flex-md-grow-0 d-inline-flex align-items-center justify-content-center gap-1.5 text-nowrap shadow-xs" style="height: 34px; font-size: 11.5px; font-weight: 600;"
                onclick="toggleAllCouriers(true)">
            <i class="fas fa-expand-alt"></i>
            <span>Expand All</span>
        </button>

        <button type="button" class="btn btn-light border btn-compact px-3 flex-fill flex-md-grow-0 d-inline-flex align-items-center justify-content-center gap-1.5 text-nowrap shadow-xs" style="height: 34px; font-size: 11.5px; font-weight: 600;"
                onclick="toggleAllCouriers(false)">
            <i class="fas fa-compress-alt"></i>
            <span>Collapse All</span>
        </button>
    </div>
</div>
    <!-- Success Notification -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border py-2 px-3 mb-3 small d-flex align-items-center justify-content-between"
             role="alert">

            <div>
                <i class="fas fa-check-circle text-success me-2"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>

            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Quick Gateway Selector -->
    <div class="courier-quick-panel mb-3 d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center justify-content-between gap-2">

        <div class="flex-grow-1 min-width-0">

            <select id="courierQuickDropdown"
                    class="form-select form-select-sm fw-semibold"
                    onchange="handleCourierDropdownSelect(this.value)">

                <option value="all">All {{ count($couriers) }} Couriers</option>

                @foreach($couriers as $c)
                    <option value="{{ $c->id }}">
                        {{ $c->courier_name }} — {{ $c->is_active ? 'Active' : 'Disabled' }}
                    </option>
                @endforeach

            </select>

        </div>

        <button type="button"
                class="btn btn-light border btn-compact px-3 text-secondary"
                onclick="resetCourierSelector()">

            <i class="fas fa-rotate-left me-1"></i>
            <span>Reset View</span>

        </button>

    </div>

    <!-- Courier Gateway Cards -->
    <div class="row g-3" id="courierAccordionList">

        @foreach($couriers as $index => $c)

            @php
                $isSteadfast = str_contains(strtolower($c->courier_name), 'steadfast');
                $isPathao    = str_contains(strtolower($c->courier_name), 'pathao');
                $logoPath    = $c->logo ?? ($c->image ?? null);
                $logoUrl     = $resolveCourierLogo($logoPath, 'https://placehold.co/120x60?text=' . urlencode($c->courier_name));
            @endphp

            <div class="col-12 courier-col-item" data-courier-id="{{ $c->id }}">

                <div class="courier-collapsible-card"
                     id="courierCard_{{ $c->id }}">

                    <!-- Gateway Header -->
                    <div class="courier-card-header"
                         onclick="toggleCourierPanel({{ $c->id }})">

                        <div class="d-flex align-items-center gap-2 min-width-0 flex-wrap">

                            <div class="courier-logo-preview-box">
                                <img src="{{ $logoUrl }}"
                                     id="courierHeaderLogoImg_{{ $c->id }}"
                                     alt="{{ $c->courier_name }}"
                                     onerror="this.src='https://placehold.co/120x60?text=Logo';">
                            </div>

                            <div class="min-width-0">

                                <h6 class="mb-0 text-truncate">
                                    {{ $c->courier_name }}
                                </h6>

                                <small>Automated Booking</small>

                            </div>

                            <span class="badge {{ $c->is_active ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border' }} ms-1">

                                {{ $c->is_active ? 'Active' : 'Disabled' }}

                            </span>

                        </div>

                        <div class="d-flex align-items-center gap-2 flex-shrink-0">

                            @if($isSteadfast && $c->is_active)

                                <button type="button"
                                        class="btn btn-outline-primary btn-compact py-0 px-2"
                                        onclick="event.stopPropagation(); checkCourierBalance({{ $c->id }})"
                                        title="Check live API wallet balance"
                                        style="font-size: 10.5px !important; height: 26px;">

                                    <i class="fas fa-wallet me-1"></i>
                                    <span>Balance</span>

                                </button>

                            @endif

                            <span class="courier-chevron-icon">
                                <i class="fas fa-chevron-down"></i>
                            </span>

                        </div>

                    </div>

                    <!-- Gateway Configuration Body -->
                    <div class="courier-card-body d-none"
                         id="courierBody_{{ $c->id }}">

                        <!-- Live Balance -->
                        <div id="balance_box_{{ $c->id }}"
                             class="alert alert-info courier-balance-box py-2 px-3 small mb-3 d-none align-items-center justify-content-between">

                            <div>
                                <i class="fas fa-coins text-warning me-1"></i>
                                <span>Current Balance:</span>

                                <strong id="balance_val_{{ $c->id }}"
                                        class="font-monospace bn-digit">
                                    ৳0.00
                                </strong>
                            </div>

                            <button type="button"
                                    class="btn btn-link text-primary text-decoration-none p-0 fw-bold small"
                                    onclick="checkCourierBalance({{ $c->id }})">

                                <i class="fas fa-sync-alt me-1"></i>Refresh

                            </button>

                        </div>

                        <form action="{{ route('admin.system.setting.courier.update', $c->id) }}"
                              method="POST"
                              enctype="multipart/form-data"
                              id="courierForm_{{ $c->id }}">

                            @csrf

                            <!-- Hidden Media Inputs -->
                            <input type="hidden"
                                   name="courier_logo_media"
                                   id="courierLogoMediaInput_{{ $c->id }}"
                                   value="">

                            <input type="hidden"
                                   name="remove_courier_logo"
                                   id="removeCourierLogoFlag_{{ $c->id }}"
                                   value="0">

                            <!-- Courier Logo Management -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Courier Branding Logo
                                </label>

                                <div class="courier-media-chooser-surface">

                                    <div class="media-chooser-actions d-flex align-items-center gap-2 mb-2 flex-wrap">

                                        <button type="button"
                                                class="btn btn-primary btn-compact"
                                                onclick="chooseCourierLogoMedia({{ $c->id }})">

                                            <i class="fas fa-photo-video me-1"></i>
                                            <span>Media Library</span>

                                        </button>

                                        <label class="btn btn-outline-secondary btn-compact mb-0"
                                               style="cursor: pointer;">

                                            <i class="fas fa-upload me-1"></i>
                                            <span>Upload Logo</span>

                                            <input type="file"
                                                   name="courier_logo_file"
                                                   id="courierLogoFileInput_{{ $c->id }}"
                                                   class="d-none"
                                                   accept="image/*"
                                                   onchange="previewCourierLocalLogo(this, {{ $c->id }})">

                                        </label>

                                    </div>

                                    <!-- Logo Preview -->
                                    <div id="courierLogoPreviewWrap_{{ $c->id }}"
                                         class="courier-logo-preview-wrap d-flex align-items-center gap-2">

                                        <div class="courier-logo-preview-box">

                                            <img id="courierLogoPreviewImg_{{ $c->id }}"
                                                 src="{{ $logoUrl }}"
                                                 onerror="this.src='https://placehold.co/120x60?text=Logo';">

                                        </div>

                                        <div class="text-truncate">

                                            <span class="small text-success fw-bold d-block">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Active Brand Logo
                                            </span>

                                            <small class="text-muted font-monospace courier-logo-path"
                                                   id="courierLogoPathText_{{ $c->id }}">
                                                {{ $logoPath ?: 'Default' }}
                                            </small>

                                        </div>

                                        <button type="button"
                                                class="btn btn-outline-danger btn-compact py-1 px-2 ms-auto"
                                                onclick="clearCourierLogo({{ $c->id }})"
                                                title="Remove logo">

                                            <i class="fas fa-trash-alt me-1"></i>
                                            Clear

                                        </button>

                                    </div>

                                </div>

                            </div>

                            <!-- API Key -->
                            <div class="mb-3">

                                <label class="form-label" for="api_key_{{ $c->id }}">

                                    <span>API Key / Client ID</span>
                                    <span class="text-danger">*</span>

                                </label>

                                <input type="text"
                                       name="api_key"
                                       id="api_key_{{ $c->id }}"
                                       class="form-control font-monospace"
                                       value="{{ $c->api_key }}"
                                       placeholder="Enter API Key / Client ID"
                                       required>

                            </div>

                            <!-- Secret Key -->
                            <div class="mb-3">

                                <label class="form-label" for="secret_key_{{ $c->id }}">

                                    <span>Secret Key</span>
                                    <span class="text-danger">*</span>

                                </label>

                                <div class="input-group input-group-sm">

                                    <input type="password"
                                           name="secret_key"
                                           id="secret_key_{{ $c->id }}"
                                           class="form-control font-monospace"
                                           value="{{ $c->secret_key }}"
                                           placeholder="Enter Secret Key"
                                           required>

                                    <button class="btn btn-light border"
                                            type="button"
                                            onclick="togglePasswordVisibility('secret_key_{{ $c->id }}', this)">

                                        <i class="fas fa-eye text-muted" style="font-size: 12px;"></i>

                                    </button>

                                </div>

                            </div>

                            <!-- API Base URL -->
                            <div class="mb-3">

                                <label class="form-label" for="api_url_{{ $c->id }}">
                                    API Base URL
                                </label>

                                <input type="text"
                                       name="api_url"
                                       id="api_url_{{ $c->id }}"
                                       class="form-control font-monospace"
                                       value="{{ $c->api_url }}"
                                       placeholder="https://portal.steadfast.com.bd/api/v1">

                            </div>

                            <!-- Enable Gateway -->
                            <div class="form-check form-switch courier-enable-control d-flex align-items-center justify-content-between mb-3">

                                <label class="form-check-label mb-0 ms-1"
                                       for="active_{{ $c->id }}"
                                       style="cursor: pointer;">

                                    Enable Automated Gateway

                                </label>

                                <input class="form-check-input m-0"
                                       type="checkbox"
                                       name="is_active"
                                       id="active_{{ $c->id }}"
                                       value="1"
                                       {{ $c->is_active ? 'checked' : '' }}
                                       style="width: 38px; height: 20px; cursor: pointer;">

                            </div>

                            <!-- Steadfast Webhook -->
                            @if($isSteadfast)

                                {{-- <div class="courier-webhook-panel mb-3">

                                    {{-- <div class="d-flex justify-content-between align-items-center gap-2 mb-2">

                                        <strong class="text-dark d-block">

                                            <i class="fas fa-link me-1 text-primary"></i>
                                            Webhook Callback URL

                                        </strong>

                                        <button type="button"
                                                class="btn btn-link text-primary text-decoration-none p-0 fw-bold"
                                                onclick="copyWebhookToClipboard('{{ url('/webhook/courier/steadfast') }}')">

                                            <i class="fas fa-copy me-1"></i>
                                            Copy

                                        </button>

                                    </div>

                                    <span class="text-muted font-monospace user-select-all d-block courier-webhook-url">
                                        {{ url('/webhook/courier/steadfast') }}
                                    </span> 

                                </div> --}}

                            @endif

                            <!-- Save Settings -->
                            <div class="courier-card-footer d-flex justify-content-end align-items-center border-top">

                                <button type="submit"
                                        class="btn btn-primary btn-compact px-4 py-2 w-100 w-sm-auto">

                                    <i class="fas fa-save me-1"></i>
                                    <span>Save API Settings</span>

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        @endforeach

    </div>

</div>

<!-- Notice Modal -->
<div class="modal fade"
     id="courierNoticeModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 106500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content border-0 shadow-lg text-center p-3">

            <div class="modal-body p-1">

                <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center mx-auto mb-2"
                     style="width: 44px; height: 44px;">

                    <i class="fas fa-circle-info fs-4"></i>

                </div>

                <h6 class="fw-bold text-dark mb-1"
                    id="courierNoticeTitle">
                    Notification
                </h6>

                <p class="text-muted small mb-3"
                   id="courierNoticeMsg"
                   style="line-height: 1.5;"></p>

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
    function showCourierNotice(message, title = 'Notification') {
        const titleEl = document.getElementById('courierNoticeTitle');
        const msgEl   = document.getElementById('courierNoticeMsg');
        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;
        const modalEl = document.getElementById('courierNoticeModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    // Toggle Single Gateway Accordion Panel
    function toggleCourierPanel(cId, forceOpen = null) {
        const card = document.getElementById('courierCard_' + cId);
        const body = document.getElementById('courierBody_' + cId);
        if (!card || !body) return;

        const isOpen = !body.classList.contains('d-none');
        const nextState = forceOpen !== null ? forceOpen : !isOpen;

        if (nextState) {
            body.classList.remove('d-none');
            card.classList.add('active-open');
        } else {
            body.classList.add('d-none');
            card.classList.remove('active-open');
        }
    }

    // Handle Top Courier Dropdown Selector
    function handleCourierDropdownSelect(val) {
        const colItems = document.querySelectorAll('.courier-col-item');
        if (val === 'all') {
            colItems.forEach(item => {
                item.classList.remove('d-none');
                const cId = item.getAttribute('data-courier-id');
                toggleCourierPanel(cId, true);
            });
        } else {
            colItems.forEach(item => {
                const cId = item.getAttribute('data-courier-id');
                if (cId === String(val)) {
                    item.classList.remove('d-none');
                    toggleCourierPanel(cId, true);
                } else {
                    item.classList.add('d-none');
                    toggleCourierPanel(cId, false);
                }
            });
        }
    }

    function toggleAllCouriers(expand) {
        document.getElementById('courierQuickDropdown').value = 'all';
        document.querySelectorAll('.courier-col-item').forEach(item => {
            item.classList.remove('d-none');
            const cId = item.getAttribute('data-courier-id');
            toggleCourierPanel(cId, expand);
        });
    }

    function resetCourierSelector() {
        document.getElementById('courierQuickDropdown').value = 'all';
        toggleAllCouriers(true);
    }

    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (!input) return;

        if (input.type === 'password') {
            input.type = 'text';
            if (icon) icon.className = 'fas fa-eye-slash text-primary';
        } else {
            input.type = 'password';
            if (icon) icon.className = 'fas fa-eye text-muted';
        }
    }

    function copyWebhookToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            showCourierNotice('Webhook Callback URL copied to clipboard!', 'Copied');
        }).catch(() => {
            showCourierNotice('Could not copy automatically. Please select and copy manually.', 'Notice');
        });
    }

    // Universal Media Extractor
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

    // Global Media Library Chooser Trigger for Courier Logo
    function chooseCourierLogoMedia(cId) {
        if (typeof window.openMediaPicker === 'function') {
            window.openMediaPicker({
                multiple: false,
                onSelect: function(media) {
                    const picked = extractMediaItem(media);
                    if (picked && picked.path) {
                        document.getElementById('courierLogoMediaInput_' + cId).value = picked.path;
                        document.getElementById('removeCourierLogoFlag_' + cId).value = '0';
                        const fileInput = document.getElementById('courierLogoFileInput_' + cId);
                        if (fileInput) fileInput.value = '';

                        const previewImg = document.getElementById('courierLogoPreviewImg_' + cId);
                        const headerImg  = document.getElementById('courierHeaderLogoImg_' + cId);
                        const pathTxt    = document.getElementById('courierLogoPathText_' + cId);

                        if (previewImg) previewImg.src = picked.url;
                        if (headerImg) headerImg.src = picked.url;
                        if (pathTxt) pathTxt.innerText = picked.path;
                    }
                }
            });
        } else {
            showCourierNotice('Media Library is loading, please try again.');
        }
    }

    // Local File Upload Preview Handler
    function previewCourierLocalLogo(input, cId) {
        if (input.files && input.files[0]) {
            document.getElementById('courierLogoMediaInput_' + cId).value = '';
            document.getElementById('removeCourierLogoFlag_' + cId).value = '0';
            const fileName = input.files[0].name;

            const reader = new FileReader();
            reader.onload = function(e) {
                const previewImg = document.getElementById('courierLogoPreviewImg_' + cId);
                const headerImg  = document.getElementById('courierHeaderLogoImg_' + cId);
                const pathTxt    = document.getElementById('courierLogoPathText_' + cId);

                if (previewImg) previewImg.src = e.target.result;
                if (headerImg) headerImg.src = e.target.result;
                if (pathTxt) pathTxt.innerText = fileName;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Clear Selected Logo
    function clearCourierLogo(cId) {
        document.getElementById('courierLogoMediaInput_' + cId).value = '';
        document.getElementById('removeCourierLogoFlag_' + cId).value = '1';
        const fileInput = document.getElementById('courierLogoFileInput_' + cId);
        if (fileInput) fileInput.value = '';

        const placeholder = 'https://placehold.co/120x60?text=No+Logo';
        const previewImg = document.getElementById('courierLogoPreviewImg_' + cId);
        const headerImg  = document.getElementById('courierHeaderLogoImg_' + cId);
        const pathTxt    = document.getElementById('courierLogoPathText_' + cId);

        if (previewImg) previewImg.src = placeholder;
        if (headerImg) headerImg.src = placeholder;
        if (pathTxt) pathTxt.innerText = 'Logo removed';
    }

    // Live Wallet Balance Checker
    function checkCourierBalance(courierId) {
        const box = document.getElementById('balance_box_' + courierId);
        const val = document.getElementById('balance_val_' + courierId);
        if (!box || !val) return;

        val.innerText = 'Checking...';
        box.classList.remove('d-none');
        box.classList.add('d-flex');

        fetch("{{ url('admin/system-settings/courier/balance') }}/" + courierId, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                val.innerText = '৳' + Number(data.balance).toLocaleString('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } else {
                val.innerText = 'Error: ' + (data.message || 'Failed');
            }
        })
        .catch(() => {
            val.innerText = 'Server Error';
        });
    }
</script>

@endsection
