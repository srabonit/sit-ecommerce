@extends('layouts.admin')

@section('title', 'Create Popup')

@php
    $indexUrl = Route::has('admin.theme.setting.display.index')
        ? route('admin.theme.setting.display.index')
        : (Route::has('admin.theme.display.index') ? route('admin.theme.display.index') : url('admin/theme-setting/display'));

    $storeUrl = Route::has('admin.theme.setting.display.popup.store')
        ? route('admin.theme.setting.display.popup.store')
        : (Route::has('admin.theme.setting.display.popups.store')
            ? route('admin.theme.setting.display.popups.store')
            : (Route::has('admin.theme.display.popup.store')
                ? route('admin.theme.display.popup.store')
                : (Route::has('admin.theme.display.popups.store')
                    ? route('admin.theme.display.popups.store')
                    : url('admin/theme-setting/display/popups/store'))));
@endphp

@section('admin_content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* =========================================
       POPUP CREATE - KAIADMIN CLASSIC UI
    ========================================= */

    #popup-create-root {
        --popup-primary: var(--bs-primary, #1572e8);
        --popup-success: var(--bs-success, #31ce36);
        --popup-danger: var(--bs-danger, #f25961);
        --popup-warning: var(--bs-warning, #ffad46);
        --popup-info: var(--bs-info, #48abf7);
        --popup-text: var(--bs-body-color, #2a2f5b);
        --popup-muted: #8d9498;
        --popup-border: #ebedf2;
        --popup-bg: #f8f9fa;
        --popup-card: #ffffff;

        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        color: var(--popup-text);
        font-size: 13px;
        letter-spacing: -0.01em;
        min-width: 0;
    }

    #popup-create-root *,
    #popup-create-root *::before,
    #popup-create-root *::after {
        box-sizing: border-box;
    }

    #popup-create-root .font-monospace {
        font-family: 'Inter', sans-serif !important;
        font-variant-numeric: tabular-nums;
    }

    /* Consistent Radius */

    #popup-create-root .btn,
    #popup-create-root button,
    #popup-create-root .badge,
    #popup-create-root .alert,
    #popup-create-root .form-control,
    #popup-create-root .form-select,
    #popup-create-root .input-group-text,
    #popup-create-root .card,
    #popup-create-root .device-check-card,
    #popup-create-root .popup-preview-box {
        border-radius: 5px !important;
    }

    /* Page Header */

    #popup-create-root .popup-create-header-wrap {
        padding: 0 0 15px;
        border-bottom: 1px solid var(--popup-border);
    }

    #popup-create-root .popup-create-header-wrap h5 {
        font-size: clamp(17px, 2vw, 20px) !important;
        font-weight: 700;
        letter-spacing: -0.5px;
        color: var(--popup-text) !important;
    }

    #popup-create-root .popup-create-header-wrap .text-muted {
        font-size: 12px !important;
        line-height: 1.5 !important;
    }

    /* Cards */

    #popup-create-root .card {
        border: 1px solid var(--popup-border) !important;
        background: var(--popup-card);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.035) !important;
        margin-bottom: 15px;
        overflow: hidden;
    }

    #popup-create-root .section-header-wrap {
        display: flex;
        align-items: center;
        min-height: 43px;
        padding: 10px 15px;
        background: #ffffff;
        border-bottom: 1px solid var(--popup-border);
    }

    #popup-create-root .section-header-wrap > span {
        font-size: 13px !important;
        font-weight: 700 !important;
        color: var(--popup-text) !important;
    }

    #popup-create-root .section-header-wrap i {
        font-size: 13px;
    }

    /* Labels */

    #popup-create-root .field-label {
        display: block;
        margin-bottom: 5px;
        color: #495057;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.4;
    }

    #popup-create-root .field-hint {
        display: block;
        margin-top: 5px;
        color: var(--popup-muted);
        font-size: 11px;
        line-height: 1.45;
    }

    /* Inputs */

    #popup-create-root .form-control,
    #popup-create-root .form-select {
        min-height: 36px;
        padding: 7px 10px;
        border: 1px solid #ebedf2 !important;
        background-color: #ffffff;
        color: #495057 !important;
        font-size: 12px !important;
        font-weight: 500;
        box-shadow: none !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    #popup-create-root .form-control::placeholder {
        color: #adb5bd;
        opacity: 1;
        font-weight: 400;
    }

    #popup-create-root .form-control:focus,
    #popup-create-root .form-select:focus {
        border-color: var(--popup-primary) !important;
        box-shadow: 0 0 0 2px rgba(21, 114, 232, 0.10) !important;
        outline: none;
    }

    #popup-create-root textarea.form-control {
        min-height: auto;
        line-height: 1.55;
        resize: vertical;
    }

    #popup-create-root .input-group > .input-group-text {
        border-color: var(--popup-border);
        background: #f8f9fa;
        color: #6c757d;
        font-size: 12px;
        font-weight: 600;
    }

    #popup-create-root .input-group > .form-control,
    #popup-create-root .input-group > .form-select {
        min-width: 0;
    }

    /* Compact Buttons */

    #popup-create-root .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        min-height: 34px;
        padding: 6px 12px !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        line-height: 1.3 !important;
        white-space: nowrap;
        transition: all 0.2s ease;
    }

    #popup-create-root .btn-primary {
        background-color: var(--popup-primary);
        border-color: var(--popup-primary);
    }

    #popup-create-root .btn-primary:hover {
        filter: brightness(0.95);
    }

    #popup-create-root .btn-outline-secondary,
    #popup-create-root .btn-outline-primary,
    #popup-create-root .btn-light {
        font-weight: 600;
    }

    #popup-create-root .btn:focus-visible,
    #popup-create-root .form-check-input:focus-visible {
        outline: 2px solid var(--popup-primary);
        outline-offset: 2px;
        box-shadow: none;
    }

    /* Input Groups */

    #popup-create-root .input-group > :first-child {
        border-top-left-radius: 5px !important;
        border-bottom-left-radius: 5px !important;
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #popup-create-root .input-group > :last-child {
        border-top-right-radius: 5px !important;
        border-bottom-right-radius: 5px !important;
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Preview */

    #popup-create-root .popup-preview-box {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        max-width: 100%;
        padding: 10px 12px;
        background: #f8f9fa;
        border: 1px dashed #d9dce3;
    }

    #popup-create-root .popup-preview-box img {
        max-width: 120px;
        max-height: 65px;
        object-fit: cover;
        border-radius: 4px;
    }

    #popup-create-root .popup-preview-box > div {
        min-width: 0;
    }

    /* Device Selection */

    #popup-create-root .device-check-card {
        display: flex;
        align-items: center;
        gap: 9px;
        min-height: 38px;
        padding: 7px 10px;
        background: #ffffff;
        border: 1px solid var(--popup-border);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    #popup-create-root .device-check-card:hover {
        background: #f8f9fa;
        border-color: var(--popup-primary);
    }

    #popup-create-root .device-check-card span {
        color: #495057;
        font-size: 12px !important;
        font-weight: 500;
    }

    /* Switches & Checkboxes */

    #popup-create-root .form-check-input {
        cursor: pointer;
    }

    #popup-create-root .form-check-input:checked {
        background-color: var(--popup-primary);
        border-color: var(--popup-primary);
    }

    #popup-create-root .form-check-label {
        cursor: pointer;
    }

    #popup-create-root .form-switch {
        min-height: 20px;
    }

    /* CTA Section */

    #popup-create-root #buttonInputsContainer {
        min-width: 0;
    }

    #popup-create-root .cta-settings-box {
        padding: 12px;
        background: #f8f9fa;
        border: 1px solid var(--popup-border) !important;
        border-radius: 5px;
    }

    /* Color Inputs */

    #popup-create-root input[type="color"].form-control {
        height: 38px;
        min-height: 38px;
        padding: 4px !important;
        cursor: pointer;
    }

    /* Validation */

    #popup-create-root .alert {
        border: 1px solid rgba(242, 89, 97, 0.15) !important;
        background: #fff8f8;
    }

    #popup-create-root .alert ul {
        overflow-wrap: anywhere;
    }

    /* Action Buttons */

    #popup-create-root .btn-submit-action {
        min-height: 40px;
        font-size: 13px !important;
        font-weight: 700 !important;
    }

    /* Responsive Layout */

    @media (min-width: 1200px) {
        #popup-create-root > .row {
            align-items: flex-start;
        }
    }

    @media (max-width: 991.98px) {
        #popup-create-root .section-header-wrap {
            min-height: 42px;
        }

        #popup-create-root .card {
            margin-bottom: 12px;
        }
    }

    @media (max-width: 767.98px) {
        #popup-create-root {
            padding-top: 12px !important;
        }

        #popup-create-root .popup-create-header-wrap {
            align-items: stretch !important;
            padding-bottom: 12px;
        }

        #popup-create-root .popup-create-header-wrap h5 {
            font-size: 17px !important;
        }

        #popup-create-root .top-action-btns {
            width: 100%;
        }

        #popup-create-root .top-action-btns .btn {
            width: 100%;
            min-height: 38px;
            justify-content: center;
        }

        #popup-create-root .card {
            margin-bottom: 12px !important;
        }

        #popup-create-root .card > .p-3 {
            padding: 12px !important;
        }

        #popup-create-root .section-header-wrap {
            padding: 10px 12px;
        }

        #popup-create-root .row.g-2 {
            --bs-gutter-x: 10px;
            --bs-gutter-y: 10px;
        }

        #popup-create-root .btn-submit-action {
            width: 100%;
            min-height: 42px;
        }

        #popup-create-root .d-grid > .btn {
            width: 100%;
        }

        #popup-create-root .popup-preview-box {
            width: 100%;
        }

        #popup-create-root .input-group .btn {
            min-height: 36px;
        }
    }

    @media (max-width: 575.98px) {
        #popup-create-root.container-fluid {
            padding-right: 10px !important;
            padding-left: 10px !important;
        }

        #popup-create-root .row.g-3 {
            --bs-gutter-x: 10px;
            --bs-gutter-y: 10px;
        }

        #popup-create-root .field-label {
            font-size: 11.5px;
        }

        #popup-create-root .form-control,
        #popup-create-root .form-select {
            min-height: 38px;
            font-size: 12px !important;
        }

        #popup-create-root textarea.form-control {
            min-height: auto;
        }

        #popup-create-root .input-group {
            flex-wrap: nowrap;
        }

        #popup-create-root .input-group > .form-control {
            min-width: 0;
        }

        #popup-create-root .input-group .btn {
            padding-right: 8px !important;
            padding-left: 8px !important;
            font-size: 11px !important;
        }

        #popup-create-root .popup-preview-box img {
            max-width: 90px;
        }

        #popup-create-root .form-check-label {
            font-size: 11px !important;
        }
    }

    @media (max-width: 360px) {
        #popup-create-root .input-group .btn {
            padding-right: 6px !important;
            padding-left: 6px !important;
            font-size: 10px !important;
        }

        #popup-create-root .popup-create-header-wrap h5 {
            font-size: 16px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="popup-create-root">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 popup-create-header-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="fas fa-bullhorn text-primary"></i>
                    <span>Create Popup</span>
                </h5>
            </div>

            <small class="text-muted d-block mt-1">
                Create promotional banners, forms and offers.
            </small>
        </div>

        <div class="top-action-btns d-flex gap-2">
            <a href="{{ $indexUrl }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold bg-white text-dark">
                <i class="fas fa-arrow-left me-1"></i> Back to Display
            </a>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small shadow-xs mb-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                <span class="fw-semibold text-dark">Please fix these errors:</span>
            </div>

            <ul class="mb-0 mt-1 ps-4 text-danger small font-monospace" style="font-size: 11px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>

            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Popup Form --}}
    <form action="{{ $storeUrl }}" method="POST">
        @csrf

        <div class="row g-3">

            {{-- Left Column --}}
            <div class="col-12 col-lg-8">

                {{-- Campaign Details --}}
                <div class="card mb-3">
                    <div class="section-header-wrap">
                        <span>
                            <i class="fas fa-pen-to-square text-primary me-1"></i>
                            Campaign Details
                        </span>
                    </div>

                    <div class="p-3">
                        <div class="row g-2">

                            {{-- Campaign Title --}}
                            <div class="col-12 col-md-8">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="field-label mb-0" for="popup-title">Campaign Title</label>

                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="show_title" id="show_title" value="1" {{ old('show_title') ? 'checked' : '' }}>
                                        <label class="form-check-label text-dark fw-bold font-monospace" for="show_title" style="font-size: 10px;">
                                            Show Title
                                        </label>
                                    </div>
                                </div>

                                <input
                                    type="text"
                                    name="title"
                                    id="popup-title"
                                    class="form-control shadow-none"
                                    placeholder="Special Weekend Offer"
                                    value="{{ old('title') }}"
                                >

                                <small class="field-hint">
                                    Turn off to hide the title.
                                </small>
                            </div>

                            {{-- Popup Type --}}
                            <div class="col-12 col-md-4">
                                <label class="field-label" for="popup-type">
                                    Popup Type <span class="text-danger">*</span>
                                </label>

                                <select name="popup_type" id="popup-type" class="form-select font-monospace shadow-none" onchange="handlePopupTypeChange(this.value)" required>
                                    <option value="promotion" {{ old('popup_type') == 'promotion' ? 'selected' : '' }}>Promotional Banner</option>
                                    <option value="welcome" {{ old('popup_type') == 'welcome' ? 'selected' : '' }}>Welcome Offer</option>
                                    <option value="newsletter" {{ old('popup_type') == 'newsletter' ? 'selected' : '' }}>Newsletter Form</option>
                                    <option value="offer" {{ old('popup_type') == 'offer' ? 'selected' : '' }}>Coupon Offer</option>
                                    <option value="custom_html" {{ old('popup_type') == 'custom_html' ? 'selected' : '' }}>Custom HTML</option>
                                </select>
                            </div>

                            {{-- Banner Image --}}
                            <div class="col-12 mt-2">
                                <label class="field-label" for="popup-image">Banner Image</label>

                                <div class="input-group">
                                    <input
                                        type="text"
                                        name="image"
                                        id="popup-image"
                                        class="form-control font-monospace shadow-none"
                                        placeholder="Image URL"
                                        value="{{ old('image') }}"
                                        oninput="updateImagePreview(this.value)"
                                    >

                                    <button type="button" class="btn btn-outline-primary btn-compact fw-bold" onclick="choosePopupImageMedia('popup-image')">
                                        <i class="fas fa-images me-1"></i> Choose Image
                                    </button>

                                    <button type="button" class="btn btn-outline-secondary btn-compact" onclick="clearSelectedImage('popup-image')" title="Clear Image">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <small class="field-hint">
                                    <strong>Tip:</strong> Turn off the title and button for image-only popups.
                                </small>

                                {{-- Image Preview --}}
                                <div id="popup-image-preview-wrap" class="mt-2 {{ old('image') ? '' : 'd-none' }}">
                                    <div class="popup-preview-box">
                                        <img
                                            id="popup-image-preview"
                                            src="{{ old('image') }}"
                                            alt="Preview"
                                        >

                                        <div>
                                            <span class="text-success fw-bold small d-block">
                                                <i class="fas fa-check-circle me-1"></i> Image Selected
                                            </span>

                                            <small class="text-muted" style="font-size: 10px;">
                                                Ready to display.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Popup Content --}}
                            <div class="col-12 mt-2">
                                <label class="field-label" for="popup-content">Content / HTML / Shortcode</label>

                                <textarea
                                    name="content"
                                    id="popup-content"
                                    rows="4"
                                    class="form-control font-monospace shadow-none"
                                    placeholder="Enter text, HTML or shortcode."
                                >{{ old('content') }}</textarea>

                                <small class="field-hint" id="content-hint">
                                    Supports text, HTML and form shortcodes.
                                </small>
                            </div>

                            {{-- CTA Button --}}
                            <div class="col-12 mt-2">
                                <div class="cta-settings-box">

                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="field-label mb-0">CTA Button</span>

                                        <div class="form-check form-switch mb-0">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="show_button"
                                                id="show_button"
                                                value="1"
                                                {{ old('show_button') ? 'checked' : '' }}
                                                onchange="toggleButtonControl(this.checked)"
                                            >

                                            <label class="form-check-label text-dark fw-bold font-monospace" for="show_button" style="font-size: 10px;">
                                                Show Button
                                            </label>
                                        </div>
                                    </div>

                                    <div class="row g-2 mt-1" id="buttonInputsContainer" style="{{ old('show_button') ? 'display: flex;' : 'display: none;' }}">

                                        <div class="col-12 col-md-6">
                                            <label class="field-label mb-1" for="popup-btn-text">Button Text</label>

                                            <input
                                                type="text"
                                                name="button_text"
                                                id="popup-btn-text"
                                                class="form-control shadow-none"
                                                placeholder="Shop Now"
                                                value="{{ old('button_text') }}"
                                            >
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label class="field-label mb-1" for="popup-btn-url">Button URL</label>

                                            <input
                                                type="text"
                                                name="button_url"
                                                id="popup-btn-url"
                                                class="form-control font-monospace shadow-none"
                                                placeholder="/shop or https://..."
                                                value="{{ old('button_url') }}"
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Page Targeting --}}
                <div class="card mb-3">
                    <div class="section-header-wrap">
                        <span>
                            <i class="fas fa-filter text-primary me-1"></i>
                            Page Targeting
                        </span>
                    </div>

                    <div class="p-3">
                        <div class="row g-2">

                            <div class="col-12 col-md-4">
                                <label class="field-label">Target Pages</label>

                                <select name="page_target_type" class="form-select font-monospace shadow-none mb-1">
                                    <option value="all" selected>All Pages</option>
                                    <option value="home_only">Homepage Only</option>
                                    <option value="products_only">Product Pages</option>
                                    <option value="specific_pages">Specific Routes</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="field-label">Frequency Scope</label>

                                <select name="frequency_scope" class="form-select font-monospace shadow-none mb-1">
                                    <option value="per_page" selected>Per Page (প্রতিটি পেজের জন্য আলাদা)</option>
                                    <option value="global">Global (পুরো সাইটের জন্য একবার)</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="field-label">Visitors</label>

                                <select name="user_type" class="form-select font-monospace shadow-none mb-1">
                                    <option value="all" selected>All Visitors</option>
                                    <option value="guest">Guests</option>
                                    <option value="logged_in">Logged-in Users</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6 mt-2">
                                <label class="field-label">Target Routes</label>

                                <textarea
                                    name="target_pages"
                                    rows="3"
                                    class="form-control font-monospace shadow-none"
                                    placeholder="One route per line&#10;shop&#10;category/*"
                                ></textarea>
                            </div>

                            <div class="col-12 col-md-6 mt-2">
                                <label class="field-label">Excluded Routes</label>

                                <textarea
                                    name="excluded_pages"
                                    rows="3"
                                    class="form-control font-monospace shadow-none"
                                    placeholder="One route per line&#10;cart&#10;checkout"
                                ></textarea>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column --}}
            <div class="col-12 col-lg-4">

                {{-- Trigger & Frequency --}}
                <div class="card mb-3">
                    <div class="section-header-wrap">
                        <span>
                            <i class="fas fa-stopwatch text-warning me-1"></i>
                            Trigger & Frequency
                        </span>
                    </div>

                    <div class="p-3">

                        <div class="mb-3">
                            <label class="field-label">
                                Trigger <span class="text-danger">*</span>
                            </label>

                            <select name="trigger_type" id="trigger-type" class="form-select font-monospace shadow-none mb-2" onchange="handleTriggerChange(this.value)">
                                <option value="delay" selected>Time Delay</option>
                                <option value="scroll">Scroll Depth</option>
                                <option value="immediate">On Page Load</option>
                            </select>

                            <div id="trigger-val-wrap">
                                <label class="field-label" id="trigger-val-label">Delay (Seconds)</label>

                                <input
                                    type="number"
                                    name="trigger_value"
                                    id="trigger-value"
                                    class="form-control font-monospace bn-digit shadow-none"
                                    value="3"
                                    min="0"
                                >
                            </div>
                        </div>

                        {{-- Repeat Frequency with Hours & Days Dropdown --}}
                        <div class="mb-3 border-top pt-2">
                            <label class="field-label">
                                Repeat Frequency <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">
                                <input
                                    type="number"
                                    name="frequency_value"
                                    id="frequency-value"
                                    class="form-control font-monospace bn-digit shadow-none"
                                    value="{{ old('frequency_value', 2) }}"
                                    min="0"
                                    step="any"
                                    placeholder="e.g. 2, 6"
                                    oninput="updateFrequencyHint()"
                                    required
                                >

                                <select name="frequency_unit" id="frequency-unit" class="form-select font-monospace shadow-none" style="max-width: 105px;" onchange="updateFrequencyHint()">
                                    <option value="hours" {{ old('frequency_unit', 'hours') == 'hours' ? 'selected' : '' }}>Hours</option>
                                    <option value="days" {{ old('frequency_unit') == 'days' ? 'selected' : '' }}>Days</option>
                                </select>
                            </div>

                            <small class="field-hint" id="frequency-hint">
                                একবার দেখার পর ২ ঘণ্টা এই পেজে আর পপআপ আসবে না। (0 = প্রতি রিলোডে আসবে)
                            </small>
                        </div>

                        <div class="form-check form-switch border-top pt-2 mb-0">
                            <input class="form-check-input" type="checkbox" name="is_active" id="popup-is-active" value="1" checked>

                            <label class="form-check-label fw-bold text-dark small font-monospace" for="popup-is-active">
                                Active Campaign
                            </label>
                        </div>

                    </div>
                </div>

                {{-- Device & Schedule --}}
                <div class="card mb-3">
                    <div class="section-header-wrap">
                        <span>
                            <i class="fas fa-calendar-alt text-info me-1"></i>
                            Devices & Schedule
                        </span>
                    </div>

                    <div class="p-3">

                        <label class="field-label">Show On Devices</label>

                        <div class="d-flex flex-column gap-2 mb-3">

                            <label class="device-check-card mb-0" for="p_desktop">
                                <input class="form-check-input mt-0" type="checkbox" name="device_desktop" id="p_desktop" value="1" checked>
                                <span>Desktop</span>
                            </label>

                            <label class="device-check-card mb-0" for="p_tablet">
                                <input class="form-check-input mt-0" type="checkbox" name="device_tablet" id="p_tablet" value="1" checked>
                                <span>Tablet</span>
                            </label>

                            <label class="device-check-card mb-0" for="p_mobile">
                                <input class="form-check-input mt-0" type="checkbox" name="device_mobile" id="p_mobile" value="1" checked>
                                <span>Mobile</span>
                            </label>

                        </div>

                        <div class="mb-3">
                            <label class="field-label">Start Date</label>
                            <input type="datetime-local" name="start_at" class="form-control font-monospace shadow-none">
                        </div>

                        <div class="mb-0">
                            <label class="field-label">End Date</label>
                            <input type="datetime-local" name="end_at" class="form-control font-monospace shadow-none">
                        </div>

                    </div>
                </div>

                {{-- Appearance --}}
                <div class="card mb-3">
                    <div class="section-header-wrap">
                        <span>
                            <i class="fas fa-palette text-secondary me-1"></i>
                            Appearance
                        </span>
                    </div>

                    <div class="p-3">
                        <div class="row g-2">

                            <div class="col-6">
                                <label class="field-label">Background</label>
                                <input type="color" name="bg_color" class="form-control form-control-color w-100 shadow-none p-1" value="#ffffff">
                            </div>

                            <div class="col-6">
                                <label class="field-label">Text Color</label>
                                <input type="color" name="text_color" class="form-control form-control-color w-100 shadow-none p-1" value="#0f172a">
                            </div>

                            <div class="col-6">
                                <label class="field-label">Button Color</label>
                                <input type="color" name="btn_bg_color" class="form-control form-control-color w-100 shadow-none p-1" value="#2563eb">
                            </div>

                            <div class="col-6">
                                <label class="field-label">Max Width</label>
                                <input type="text" name="max_width" class="form-control font-monospace shadow-none" value="480px">
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold py-2 btn-submit-action">
                        <i class="fas fa-save me-1"></i> Save & Publish
                    </button>

                    <a href="{{ $indexUrl }}" class="btn btn-light border btn-compact shadow-xs text-secondary py-2">
                        Cancel
                    </a>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
    // Toggle CTA fields
    function toggleButtonControl(checked) {
        const container = document.getElementById('buttonInputsContainer');

        if (container) {
            container.style.display = checked ? 'flex' : 'none';
        }
    }

    // Extract media data
    function extractMediaItem(mediaInput) {
        if (!mediaInput) return null;

        const item = Array.isArray(mediaInput) ? mediaInput[0] : mediaInput;

        if (!item) return null;

        if (typeof item === 'string') {
            return {
                path: item.trim(),
                url: item.trim()
            };
        }

        if (typeof item !== 'object') return null;

        const rawPath = item.file_path || item.path || item.url || '';
        let rawUrl = item.url || item.file_path || item.path || '';

        if (!rawUrl && rawPath) {
            rawUrl = rawPath;
        }

        if (!rawUrl || rawUrl === 'undefined') return null;

        let resolvedUrl = String(rawUrl).trim();

        if (
            !resolvedUrl.startsWith('http://') &&
            !resolvedUrl.startsWith('https://') &&
            !resolvedUrl.startsWith('/')
        ) {
            resolvedUrl = '/' + resolvedUrl;
        }

        return {
            path: String(rawPath).trim(),
            url: resolvedUrl
        };
    }

    // Open media picker
    function choosePopupImageMedia(targetInputId = 'popup-image') {
        if (typeof window.openMediaPicker === 'function') {
            window.openMediaPicker({
                multiple: false,

                onSelect: function(media) {
                    const picked = extractMediaItem(media);

                    if (picked && (picked.url || picked.path)) {
                        const finalVal = picked.url || picked.path;
                        const input = document.getElementById(targetInputId);

                        if (input) {
                            input.value = finalVal;
                            updateImagePreview(finalVal);
                        }
                    }
                }
            });
        } else {
            const manual = prompt('Enter image URL:');

            if (manual && manual.trim()) {
                const input = document.getElementById(targetInputId);

                if (input) {
                    input.value = manual.trim();
                    updateImagePreview(manual.trim());
                }
            }
        }
    }

    window.openPopupMediaModal = choosePopupImageMedia;

    // Update image preview
    function updateImagePreview(url) {
        const wrap = document.getElementById('popup-image-preview-wrap');
        const img = document.getElementById('popup-image-preview');

        if (!wrap || !img) return;

        if (url && url.trim() !== '') {
            img.src = url.trim();
            wrap.classList.remove('d-none');

            img.onerror = () => wrap.classList.add('d-none');
        } else {
            wrap.classList.add('d-none');
        }
    }

    // Clear selected image
    function clearSelectedImage(inputId) {
        const input = document.getElementById(inputId);

        if (input) {
            input.value = '';
        }

        updateImagePreview('');
    }

    // Trigger settings
    function handleTriggerChange(val) {
        const wrap = document.getElementById('trigger-val-wrap');
        const label = document.getElementById('trigger-val-label');
        const input = document.getElementById('trigger-value');

        if (!wrap || !label || !input) return;

        if (val === 'delay') {
            wrap.style.display = 'block';
            label.innerText = 'Delay (Seconds)';
            input.value = input.value || '3';
        } else if (val === 'scroll') {
            wrap.style.display = 'block';
            label.innerText = 'Scroll Depth (%)';
            input.value = input.value || '50';
        } else {
            wrap.style.display = 'none';
        }
    }

    // Live update frequency hint text
    function updateFrequencyHint() {
        const valInput = document.getElementById('frequency-value');
        const unitSelect = document.getElementById('frequency-unit');
        const hint = document.getElementById('frequency-hint');
        if (!valInput || !unitSelect || !hint) return;

        const val = parseFloat(valInput.value) || 0;
        const unit = unitSelect.value;
        if (val === 0) {
            hint.textContent = '0 = প্রতিবার পেজ রিলোডে পপআপটি আসবে।';
        } else {
            hint.textContent = `একবার দেখলে আগামী ${val} ${unit === 'hours' ? 'ঘণ্টা' : 'দিন'} এই পেজে আর পপআপ আসবে না।`;
        }
    }

    // Popup type presets
    function handlePopupTypeChange(val) {
        const btnText = document.getElementById('popup-btn-text');
        const showBtn = document.getElementById('show_button');
        const contentHint = document.getElementById('content-hint');

        if (val === 'newsletter') {
            if (btnText && !btnText.value) {
                btnText.value = 'Subscribe';
            }

            if (showBtn) {
                showBtn.checked = true;
                toggleButtonControl(true);
            }

            if (contentHint) {
                contentHint.textContent = 'Newsletter form will appear automatically.';
            }

        } else if (val === 'offer') {
            if (btnText && !btnText.value) {
                btnText.value = 'Claim Offer';
            }

            if (showBtn) {
                showBtn.checked = true;
                toggleButtonControl(true);
            }

            if (contentHint) {
                contentHint.textContent = 'Coupon offer with an optional CTA button.';
            }

        } else if (val === 'custom_html') {
            if (contentHint) {
                contentHint.textContent = 'Supports custom HTML and dynamic form shortcodes.';
            }

        } else {
            if (contentHint) {
                contentHint.textContent = 'Supports text, HTML and dynamic shortcodes.';
            }
        }
    }

    // Initialize controls
    document.addEventListener('DOMContentLoaded', function() {
        const btnSw = document.getElementById('show_button');

        if (btnSw) {
            toggleButtonControl(btnSw.checked);
        }

        const triggerType = document.getElementById('trigger-type');

        if (triggerType) {
            handleTriggerChange(triggerType.value);
        }

        const popupType = document.getElementById('popup-type');

        if (popupType && popupType.value) {
            handlePopupTypeChange(popupType.value);
        }

        updateFrequencyHint();
    });
</script>
@endsection