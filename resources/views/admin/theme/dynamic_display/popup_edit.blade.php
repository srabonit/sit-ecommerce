@extends('layouts.admin')

@section('title', 'Edit Popup')

@php
    $indexUrl = Route::has('admin.theme.setting.display.index')
        ? route('admin.theme.setting.display.index')
        : (Route::has('admin.theme.display.index')
            ? route('admin.theme.display.index')
            : url('admin/theme-setting/display'));

    $updateUrl = Route::has('admin.theme.setting.display.popup.update')
        ? route('admin.theme.setting.display.popup.update', $popup->id)
        : (Route::has('admin.theme.setting.display.popups.update')
            ? route('admin.theme.setting.display.popups.update', $popup->id)
            : (Route::has('admin.theme.display.popup.update')
                ? route('admin.theme.display.popup.update', $popup->id)
                : (Route::has('admin.theme.display.popups.update')
                    ? route('admin.theme.display.popups.update', $popup->id)
                    : url('admin/theme-setting/display/popups/' . $popup->id . '/update'))));

    $rule = $popup->rule;

    $settings = is_array($popup->settings)
        ? $popup->settings
        : (json_decode($popup->settings ?? '[]', true) ?: []);

    $showTitle = !empty($settings['show_title']);
    $showButton = !empty($settings['show_button']);
    $freqScope = $settings['frequency_scope'] ?? 'per_page';

    // ফ্রিকোয়েন্সি ইউনিট এবং মান রেজল্যুশন
    $savedUnit = $settings['frequency_unit'] ?? 'hours';
    if (isset($settings['frequency_value'])) {
        $savedVal = (float)$settings['frequency_value'];
    } elseif (isset($settings['frequency_hours'])) {
        $savedVal = ($savedUnit === 'days') ? ((float)$settings['frequency_hours'] / 24) : (float)$settings['frequency_hours'];
    } else {
        $savedVal = (float)($popup->frequency_days ?? 0);
        if ($savedVal > 0 && !isset($settings['frequency_unit'])) {
            $savedUnit = 'days';
        }
    }
@endphp

@section('admin_content')

<style>
    /* =========================================================
       POPUP EDIT — KAIADMIN CLASSIC DESIGN SYSTEM
    ========================================================= */

    #popup-edit-root {
        --popup-primary: var(--bs-primary, #1572e8);
        --popup-primary-rgb: var(--bs-primary-rgb, 21, 114, 232);
        --popup-body-bg: #f8f9fa;
        --popup-card-bg: #ffffff;
        --popup-heading: #2a2f5b;
        --popup-text: #495057;
        --popup-muted: #8d9498;
        --popup-border: #e6e9ed;
        --popup-soft: #f8f9fc;
        --popup-success: #31ce36;
        --popup-danger: #f25961;
        --popup-warning: #ffad46;
        --popup-info: #48abf7;

        width: 100%;
        min-width: 0;
        color: var(--popup-text);
        font-family: inherit;
        font-size: 13px;
        line-height: 1.5;
        letter-spacing: normal;
    }

    #popup-edit-root *,
    #popup-edit-root *::before,
    #popup-edit-root *::after {
        box-sizing: border-box;
    }

    /* Page Header */

    #popup-edit-root .popup-page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    #popup-edit-root .popup-page-title {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        color: var(--popup-heading);
        font-size: clamp(17px, 1.5vw, 20px);
        font-weight: 700;
        line-height: 1.35;
        margin: 0;
    }

    #popup-edit-root .popup-page-title > i {
        color: var(--popup-primary);
        font-size: 17px;
    }

    #popup-edit-root .popup-page-description {
        color: var(--popup-muted);
        font-size: 12px;
        margin: 5px 0 0;
        line-height: 1.5;
    }

    #popup-edit-root .popup-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        padding: 5px 9px;
        letter-spacing: 0;
    }

    /* Cards */

    #popup-edit-root .card {
        min-width: 0;
        background: var(--popup-card-bg);
        border: 1px solid var(--popup-border) !important;
        border-radius: 6px !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.035) !important;
        margin-bottom: 16px;
        overflow: hidden;
    }

    #popup-edit-root .section-header-wrap {
        display: flex;
        align-items: center;
        min-height: 45px;
        padding: 11px 16px;
        background: #fff;
        border-bottom: 1px solid var(--popup-border);
    }

    #popup-edit-root .section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--popup-heading);
        font-size: 13px;
        font-weight: 700;
        margin: 0;
        line-height: 1.4;
    }

    #popup-edit-root .section-title i {
        font-size: 14px;
        color: var(--popup-primary);
    }

    #popup-edit-root .section-body {
        padding: 17px;
        min-width: 0;
    }

    /* Form Elements */

    #popup-edit-root .field-label {
        display: block;
        color: #495057;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
        line-height: 1.4;
    }

    #popup-edit-root .field-hint {
        display: block;
        color: var(--popup-muted);
        font-size: 11px;
        font-weight: 400;
        line-height: 1.5;
        margin-top: 5px;
    }

    #popup-edit-root .form-control,
    #popup-edit-root .form-select {
        width: 100%;
        min-width: 0;
        min-height: 38px;
        padding: 8px 11px;
        border: 1px solid #ebedf2 !important;
        border-radius: 5px !important;
        background-color: #fff;
        color: #495057 !important;
        font-family: inherit;
        font-size: 13px !important;
        font-weight: 400;
        line-height: 1.5;
        box-shadow: none !important;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #popup-edit-root textarea.form-control {
        min-height: 90px;
        resize: vertical;
        line-height: 1.6;
    }

    #popup-edit-root .form-control::placeholder {
        color: #adb5bd;
        opacity: 1;
    }

    #popup-edit-root .form-control:focus,
    #popup-edit-root .form-select:focus {
        border-color: var(--popup-primary) !important;
        box-shadow: 0 0 0 3px rgba(var(--popup-primary-rgb), .10) !important;
        outline: none;
    }

    #popup-edit-root .form-control:disabled,
    #popup-edit-root .form-select:disabled {
        background-color: #f8f9fa;
        opacity: .75;
    }

    #popup-edit-root .font-monospace {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
        font-size: 12px !important;
        font-variant-numeric: tabular-nums;
    }

    #popup-edit-root .input-group {
        flex-wrap: nowrap;
        min-width: 0;
    }

    #popup-edit-root .input-group > :first-child {
        border-top-left-radius: 5px !important;
        border-bottom-left-radius: 5px !important;
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #popup-edit-root .input-group > :last-child {
        border-top-right-radius: 5px !important;
        border-bottom-right-radius: 5px !important;
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Switches and Checkboxes */

    #popup-edit-root .form-check {
        min-height: auto;
    }

    #popup-edit-root .form-check-input {
        cursor: pointer;
        border-color: #ced4da;
    }

    #popup-edit-root .form-check-input:checked {
        background-color: var(--popup-primary);
        border-color: var(--popup-primary);
    }

    #popup-edit-root .form-check-input:focus {
        box-shadow: 0 0 0 3px rgba(var(--popup-primary-rgb), .12);
    }

    #popup-edit-root .form-switch .form-check-input {
        width: 2.1em;
        height: 1.1em;
        margin-top: .15em;
    }

    #popup-edit-root .form-check-label {
        color: var(--popup-text);
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
    }

    /* Buttons */

    #popup-edit-root .btn,
    #popup-edit-root .btn-sm {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-height: 36px;
        padding: 7px 13px;
        border-radius: 5px !important;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.4;
        white-space: nowrap;
        transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease;
    }

    #popup-edit-root .btn:focus-visible {
        outline: 2px solid var(--popup-primary);
        outline-offset: 2px;
        box-shadow: none;
    }

    #popup-edit-root .btn-primary {
        background-color: var(--popup-primary);
        border-color: var(--popup-primary);
    }

    #popup-edit-root .btn-primary:hover {
        filter: brightness(.94);
    }

    #popup-edit-root .btn-outline-primary {
        color: var(--popup-primary);
        border-color: var(--popup-primary);
        background: #fff;
    }

    #popup-edit-root .btn-outline-primary:hover {
        color: #fff;
        background: var(--popup-primary);
        border-color: var(--popup-primary);
    }

    #popup-edit-root .btn-outline-secondary {
        color: #6c757d;
        border-color: #dce0e5;
        background: #fff;
    }

    #popup-edit-root .btn-outline-secondary:hover {
        background: #f1f3f5;
        border-color: #c9ced4;
        color: #495057;
    }

    #popup-edit-root .btn-light {
        background: #fff;
    }

    #popup-edit-root .btn-compact {
        min-height: 34px;
        padding: 6px 11px;
        font-size: 11px;
        font-weight: 600;
    }

    #popup-edit-root .btn-submit-action {
        min-height: 42px;
        font-size: 13px;
        font-weight: 700;
    }

    /* Preview */

    #popup-edit-root .popup-preview-box {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        max-width: 100%;
        padding: 10px 12px;
        border: 1px dashed #d9dee5;
        border-radius: 5px;
        background: var(--popup-soft);
    }

    #popup-edit-root .popup-preview-box img {
        display: block;
        max-width: 120px;
        max-height: 65px;
        object-fit: contain;
        border-radius: 4px;
    }

    #popup-edit-root .popup-preview-box .preview-status {
        color: var(--popup-success);
        font-size: 12px;
        font-weight: 700;
    }

    /* CTA Section */

    #popup-edit-root .popup-cta-box {
        padding: 14px;
        border: 1px solid var(--popup-border) !important;
        border-radius: 6px !important;
        background: #fafbfc;
    }

    #popup-edit-root .popup-cta-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    /* Device Selection */

    #popup-edit-root .device-check-card {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 40px;
        padding: 8px 12px;
        border: 1px solid var(--popup-border);
        border-radius: 5px;
        background: #fff;
        cursor: pointer;
        transition: border-color .15s ease, background-color .15s ease;
    }

    #popup-edit-root .device-check-card:hover {
        background: #f8faff;
        border-color: var(--popup-primary);
    }

    #popup-edit-root .device-check-card .form-check-input {
        flex-shrink: 0;
        margin: 0;
    }

    #popup-edit-root .device-check-card span {
        color: var(--popup-text);
        font-size: 12px;
        font-weight: 600;
    }

    /* Color Inputs */

    #popup-edit-root input[type="color"].form-control-color {
        height: 40px;
        min-height: 40px;
        padding: 5px;
        cursor: pointer;
    }

    /* Validation */

    #popup-edit-root .alert {
        border-radius: 6px;
        padding: 12px 14px;
        font-size: 12px;
    }

    #popup-edit-root .alert ul {
        line-height: 1.7;
    }

    /* Utilities */

    #popup-edit-root .text-muted {
        color: var(--popup-muted) !important;
    }

    #popup-edit-root .border-top {
        border-color: var(--popup-border) !important;
    }

    #popup-edit-root .popup-form-column,
    #popup-edit-root .popup-settings-column {
        min-width: 0;
    }

    #popup-edit-root .popup-actions {
        position: sticky;
        bottom: 12px;
        z-index: 5;
        padding: 12px;
        border: 1px solid var(--popup-border);
        border-radius: 6px;
        background: rgba(255, 255, 255, .97);
        box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
    }

    /* Large Desktop */

    @media (min-width: 1400px) {
        #popup-edit-root .section-body {
            padding: 19px;
        }
    }

    /* Tablet and Mobile */

    @media (max-width: 991.98px) {
        #popup-edit-root .popup-settings-column {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0 14px;
            align-items: start;
        }

        #popup-edit-root .popup-settings-column > .card {
            width: 100%;
        }

        #popup-edit-root .popup-settings-column > .popup-actions {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 767.98px) {
        #popup-edit-root {
            padding-top: 12px !important;
            padding-bottom: 16px !important;
        }

        #popup-edit-root .popup-page-header {
            align-items: stretch !important;
            gap: 12px;
            margin-bottom: 15px;
        }

        #popup-edit-root .popup-page-title {
            font-size: 17px;
        }

        #popup-edit-root .popup-page-description {
            font-size: 11px;
        }

        #popup-edit-root .top-action-btns {
            width: 100%;
        }

        #popup-edit-root .top-action-btns .btn {
            width: 100%;
            min-height: 40px;
        }

        #popup-edit-root .section-header-wrap {
            min-height: 43px;
            padding: 10px 13px;
        }

        #popup-edit-root .section-body {
            padding: 13px;
        }

        #popup-edit-root .card {
            margin-bottom: 13px;
        }

        #popup-edit-root .popup-settings-column {
            display: block;
        }

        #popup-edit-root .popup-cta-box {
            padding: 11px;
        }

        #popup-edit-root .btn-submit-action {
            width: 100%;
            min-height: 43px;
        }

        #popup-edit-root .popup-actions {
            position: static;
            padding: 0;
            border: 0;
            box-shadow: none;
            background: transparent;
        }

        #popup-edit-root .popup-actions .btn {
            width: 100%;
        }
    }

    @media (max-width: 575.98px) {
        #popup-edit-root .row {
            --bs-gutter-x: 12px;
        }

        #popup-edit-root .section-body {
            padding: 12px;
        }

        #popup-edit-root .form-control,
        #popup-edit-root .form-select {
            min-height: 40px;
            font-size: 13px !important;
        }

        #popup-edit-root textarea.form-control {
            min-height: 85px;
        }

        #popup-edit-root .input-group {
            flex-wrap: wrap;
        }

        #popup-edit-root .input-group > .form-control {
            flex: 1 1 0%;
            width: 1%;
        }

        #popup-edit-root .input-group > .btn {
            flex-shrink: 0;
        }

        #popup-edit-root .popup-preview-box {
            width: 100%;
        }

        #popup-edit-root .popup-preview-box img {
            max-width: 100px;
        }

        #popup-edit-root .popup-page-title {
            font-size: 16px;
        }

        #popup-edit-root .popup-status-badge {
            font-size: 10px;
        }

        #popup-edit-root .field-label {
            font-size: 12px;
        }
    }

    @media (max-width: 359.98px) {
        #popup-edit-root .section-body {
            padding: 10px;
        }

        #popup-edit-root .popup-page-title {
            font-size: 15px;
        }

        #popup-edit-root .btn {
            white-space: normal;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-3 px-xl-4 position-relative" id="popup-edit-root">

    {{-- Header --}}
    <div class="popup-page-header popup-edit-header-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="popup-page-title">
                    <i class="fas fa-pen-to-square"></i>
                    <span>Edit Popup #{{ $popup->id }}</span>
                </h5>

                <span class="popup-status-badge {{ $popup->is_active ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                    <i class="fas {{ $popup->is_active ? 'fa-check-circle' : 'fa-pause-circle' }}"></i>
                    {{ $popup->is_active ? 'Active' : 'Paused' }}
                </span>
            </div>

            <p class="popup-page-description">
                Edit popup content, images, triggers and display rules.
            </p>
        </div>

        <div class="top-action-btns d-flex gap-2">
            <a href="{{ $indexUrl }}" class="btn btn-outline-secondary btn-compact">
                <i class="fas fa-arrow-left"></i>
                Back to Display Hub
            </a>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3 border" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle fs-5"></i>
                <span class="fw-semibold">Please fix these errors:</span>
            </div>

            <ul class="mb-0 mt-2 ps-4 small">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>

            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Edit Form --}}
    <form action="{{ $updateUrl }}" method="POST">
        @csrf

        <div class="row g-3">

            {{-- Left Column --}}
            <div class="col-12 col-lg-8 popup-form-column">

                {{-- Campaign Details --}}
                <div class="card">
                    <div class="section-header-wrap">
                        <h6 class="section-title">
                            <i class="fas fa-pen-to-square"></i>
                            Campaign Details
                        </h6>
                    </div>

                    <div class="section-body">
                        <div class="row g-3">

                            {{-- Campaign Title --}}
                            <div class="col-12 col-md-8">
                                <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-2">
                                    <label class="field-label mb-0" for="popup-title">
                                        Campaign Title
                                    </label>

                                    <div class="form-check form-switch mb-0">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="show_title"
                                            id="show_title"
                                            value="1"
                                            {{ $showTitle ? 'checked' : '' }}
                                            onchange="toggleTitleControl(this.checked)"
                                        >

                                        <label class="form-check-label" for="show_title">
                                            Show Title
                                        </label>
                                    </div>
                                </div>

                                <div id="titleInputContainer" style="{{ $showTitle ? 'display: block;' : 'display: none;' }}">
                                    <input
                                        type="text"
                                        name="title"
                                        id="popup-title"
                                        class="form-control"
                                        placeholder="Special Weekend Offer"
                                        value="{{ old('title', $popup->title) }}"
                                    >

                                    <small class="field-hint">
                                        Disable to hide the popup title.
                                    </small>
                                </div>
                            </div>

                            {{-- Popup Type --}}
                            <div class="col-12 col-md-4">
                                <label class="field-label" for="popup-type">
                                    Popup Type <span class="text-danger">*</span>
                                </label>

                                <select
                                    name="popup_type"
                                    id="popup-type"
                                    class="form-select"
                                    onchange="handlePopupTypeChange(this.value)"
                                    required
                                >
                                    <option value="promotion" {{ old('popup_type', $popup->popup_type) == 'promotion' ? 'selected' : '' }}>Promotional Banner</option>
                                    <option value="welcome" {{ old('popup_type', $popup->popup_type) == 'welcome' ? 'selected' : '' }}>Welcome Offer</option>
                                    <option value="newsletter" {{ old('popup_type', $popup->popup_type) == 'newsletter' ? 'selected' : '' }}>Newsletter Form</option>
                                    <option value="offer" {{ old('popup_type', $popup->popup_type) == 'offer' ? 'selected' : '' }}>Coupon Offer</option>
                                    <option value="custom_html" {{ old('popup_type', $popup->popup_type) == 'custom_html' ? 'selected' : '' }}>Custom HTML</option>
                                </select>
                            </div>

                            {{-- Banner Image --}}
                            <div class="col-12">
                                <label class="field-label" for="popup-image">
                                    Banner Image
                                </label>

                                <div class="input-group">
                                    <input
                                        type="text"
                                        name="image"
                                        id="popup-image"
                                        class="form-control font-monospace"
                                        placeholder="Image URL"
                                        value="{{ old('image', $popup->image) }}"
                                        oninput="updateImagePreview(this.value)"
                                    >

                                    <button
                                        type="button"
                                        class="btn btn-outline-primary btn-compact"
                                        onclick="choosePopupImageMedia('popup-image')"
                                    >
                                        <i class="fas fa-images"></i>
                                        Choose Image
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary btn-compact"
                                        onclick="clearSelectedImage('popup-image')"
                                        title="Clear Image"
                                    >
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <small class="field-hint">
                                    <strong>Tip:</strong> Disable the title and button for an image-only popup.
                                </small>

                                {{-- Image Preview --}}
                                <div id="popup-image-preview-wrap" class="mt-3 {{ old('image', $popup->image) ? '' : 'd-none' }}">
                                    <div class="popup-preview-box">
                                        <img
                                            id="popup-image-preview"
                                            src="{{ old('image', $popup->image) }}"
                                            alt="Preview"
                                        >

                                        <div>
                                            <span class="preview-status d-block">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Image Selected
                                            </span>

                                            <small class="text-muted">
                                                Ready to display.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="col-12">
                                <label class="field-label" for="popup-content">
                                    Content / HTML / Shortcode
                                </label>

                                <textarea
                                    name="content"
                                    id="popup-content"
                                    rows="4"
                                    class="form-control font-monospace"
                                    placeholder="Enter text, HTML or shortcode. Leave blank for image-only."
                                >{{ old('content', $popup->content) }}</textarea>

                                <small class="field-hint" id="content-hint">
                                    Supports text, HTML and dynamic form shortcodes.
                                </small>
                            </div>

                            {{-- CTA Button --}}
                            <div class="col-12">
                                <div class="popup-cta-box">

                                    <div class="popup-cta-header">
                                        <span class="field-label mb-0">CTA Button</span>

                                        <div class="form-check form-switch mb-0">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="show_button"
                                                id="show_button"
                                                value="1"
                                                {{ $showButton ? 'checked' : '' }}
                                                onchange="toggleButtonControl(this.checked)"
                                            >

                                            <label class="form-check-label" for="show_button">
                                                Show Button
                                            </label>
                                        </div>
                                    </div>

                                    <div class="row g-3 mt-1" id="buttonInputsContainer" style="{{ $showButton ? 'display: flex;' : 'display: none;' }}">

                                        <div class="col-12 col-md-6">
                                            <label class="field-label" for="popup-btn-text">
                                                Button Text
                                            </label>

                                            <input
                                                type="text"
                                                name="button_text"
                                                id="popup-btn-text"
                                                class="form-control"
                                                placeholder="Shop Now"
                                                value="{{ old('button_text', $popup->button_text) }}"
                                            >
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label class="field-label" for="popup-btn-url">
                                                Button URL
                                            </label>

                                            <input
                                                type="text"
                                                name="button_url"
                                                id="popup-btn-url"
                                                class="form-control font-monospace"
                                                placeholder="/shop or https://..."
                                                value="{{ old('button_url', $popup->button_url) }}"
                                            >
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Page Targeting --}}
                <div class="card">
                    <div class="section-header-wrap">
                        <h6 class="section-title">
                            <i class="fas fa-filter"></i>
                            Page Targeting
                        </h6>
                    </div>

                    <div class="section-body">
                        <div class="row g-3">

                            <div class="col-12 col-md-4">
                                <label class="field-label">Target Pages</label>

                                <select name="page_target_type" class="form-select">
                                    <option value="all" {{ old('page_target_type', $rule->page_target_type ?? 'all') == 'all' ? 'selected' : '' }}>All Pages</option>
                                    <option value="home_only" {{ old('page_target_type', $rule->page_target_type ?? '') == 'home_only' ? 'selected' : '' }}>Homepage Only</option>
                                    <option value="products_only" {{ old('page_target_type', $rule->page_target_type ?? '') == 'products_only' ? 'selected' : '' }}>Product Pages</option>
                                    <option value="specific_pages" {{ old('page_target_type', $rule->page_target_type ?? '') == 'specific_pages' ? 'selected' : '' }}>Specific Routes</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="field-label">Frequency Scope</label>

                                <select name="frequency_scope" class="form-select">
                                    <option value="per_page" {{ $freqScope === 'per_page' ? 'selected' : '' }}>Per Page (প্রতিটি পেজের জন্য আলাদা)</option>
                                    <option value="global" {{ $freqScope === 'global' ? 'selected' : '' }}>Global (পুরো সাইটের জন্য একবার)</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="field-label">Visitors</label>

                                <select name="user_type" class="form-select">
                                    <option value="all" {{ old('user_type', $rule->user_type ?? 'all') == 'all' ? 'selected' : '' }}>All Visitors</option>
                                    <option value="guest" {{ old('user_type', $rule->user_type ?? '') == 'guest' ? 'selected' : '' }}>Guests</option>
                                    <option value="logged_in" {{ old('user_type', $rule->user_type ?? '') == 'logged_in' ? 'selected' : '' }}>Logged-in Users</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="field-label">Target Routes</label>

                                <textarea
                                    name="target_pages"
                                    rows="3"
                                    class="form-control font-monospace"
                                    placeholder="One route per line&#10;shop&#10;category/*"
                                >{{ !empty($rule->target_pages) ? implode("\n", $rule->target_pages) : '' }}</textarea>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="field-label">Excluded Routes</label>

                                <textarea
                                    name="excluded_pages"
                                    rows="3"
                                    class="form-control font-monospace"
                                    placeholder="Never show on&#10;cart&#10;checkout"
                                >{{ !empty($rule->excluded_pages) ? implode("\n", $rule->excluded_pages) : '' }}</textarea>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column --}}
            <div class="col-12 col-lg-4 popup-settings-column">

                {{-- Trigger & Frequency --}}
                <div class="card">
                    <div class="section-header-wrap">
                        <h6 class="section-title">
                            <i class="fas fa-stopwatch text-warning"></i>
                            Trigger & Frequency
                        </h6>
                    </div>

                    <div class="section-body">

                        <div class="mb-3">
                            <label class="field-label">
                                Trigger <span class="text-danger">*</span>
                            </label>

                            <select
                                name="trigger_type"
                                id="trigger-type"
                                class="form-select mb-2"
                                onchange="handleTriggerChange(this.value)"
                            >
                                <option value="delay" {{ old('trigger_type', $popup->trigger_type) == 'delay' ? 'selected' : '' }}>Time Delay</option>
                                <option value="scroll" {{ old('trigger_type', $popup->trigger_type) == 'scroll' ? 'selected' : '' }}>Scroll Depth</option>
                                <option value="immediate" {{ old('trigger_type', $popup->trigger_type) == 'immediate' ? 'selected' : '' }}>On Page Load</option>
                            </select>

                            <div id="trigger-val-wrap" style="{{ $popup->trigger_type === 'immediate' ? 'display: none;' : '' }}">
                                <label class="field-label" id="trigger-val-label">
                                    {{ $popup->trigger_type === 'scroll' ? 'Scroll Depth (%)' : 'Delay (Seconds)' }}
                                </label>

                                <input
                                    type="number"
                                    name="trigger_value"
                                    id="trigger-value"
                                    class="form-control font-monospace bn-digit"
                                    value="{{ old('trigger_value', $popup->trigger_value) }}"
                                    min="0"
                                >
                            </div>
                        </div>

                        {{-- Repeat Frequency with Hours & Days Dropdown --}}
                        <div class="mb-3 border-top pt-3">
                            <label class="field-label">
                                Repeat Frequency <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">
                                <input
                                    type="number"
                                    name="frequency_value"
                                    id="frequency-value"
                                    class="form-control font-monospace bn-digit shadow-none"
                                    value="{{ old('frequency_value', $savedVal) }}"
                                    min="0"
                                    step="any"
                                    placeholder="e.g. 2, 6"
                                    oninput="updateFrequencyHint()"
                                    required
                                >

                                <select name="frequency_unit" id="frequency-unit" class="form-select font-monospace shadow-none" style="max-width: 105px;" onchange="updateFrequencyHint()">
                                    <option value="hours" {{ old('frequency_unit', $savedUnit) == 'hours' ? 'selected' : '' }}>Hours</option>
                                    <option value="days" {{ old('frequency_unit', $savedUnit) == 'days' ? 'selected' : '' }}>Days</option>
                                </select>
                            </div>

                            <small class="field-hint" id="frequency-hint">
                                একবার দেখলে আগামী {{ $savedVal }} {{ $savedUnit === 'hours' ? 'ঘণ্টা' : 'দিন' }} এই পেজে আর পপআপ আসবে না। (0 = প্রতি রিলোডে আসবে)
                            </small>
                        </div>

                        <div class="form-check form-switch border-top pt-3 mb-0">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="is_active"
                                id="popup-is-active"
                                value="1"
                                {{ old('is_active', $popup->is_active) ? 'checked' : '' }}
                            >

                            <label class="form-check-label" for="popup-is-active">
                                Active Campaign
                            </label>
                        </div>

                    </div>
                </div>

                {{-- Device & Schedule --}}
                <div class="card">
                    <div class="section-header-wrap">
                        <h6 class="section-title">
                            <i class="fas fa-calendar-alt text-info"></i>
                            Devices & Schedule
                        </h6>
                    </div>

                    <div class="section-body">

                        <label class="field-label">Show On Devices</label>

                        <div class="d-flex flex-column gap-2 mb-4">

                            <label class="device-check-card mb-0" for="p_desktop">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="device_desktop"
                                    id="p_desktop"
                                    value="1"
                                    {{ ($rule->device_visibility['desktop'] ?? true) ? 'checked' : '' }}
                                >
                                <span>Desktop</span>
                            </label>

                            <label class="device-check-card mb-0" for="p_tablet">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="device_tablet"
                                    id="p_tablet"
                                    value="1"
                                    {{ ($rule->device_visibility['tablet'] ?? true) ? 'checked' : '' }}
                                >
                                <span>Tablet</span>
                            </label>

                            <label class="device-check-card mb-0" for="p_mobile">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="device_mobile"
                                    id="p_mobile"
                                    value="1"
                                    {{ ($rule->device_visibility['mobile'] ?? true) ? 'checked' : '' }}
                                >
                                <span>Mobile</span>
                            </label>

                        </div>

                        <div class="mb-3">
                            <label class="field-label">Start Date</label>

                            <input
                                type="datetime-local"
                                name="start_at"
                                class="form-control font-monospace"
                                value="{{ $rule && $rule->start_at ? $rule->start_at->format('Y-m-d\TH:i') : '' }}"
                            >
                        </div>

                        <div class="mb-0">
                            <label class="field-label">End Date</label>

                            <input
                                type="datetime-local"
                                name="end_at"
                                class="form-control font-monospace"
                                value="{{ $rule && $rule->end_at ? $rule->end_at->format('Y-m-d\TH:i') : '' }}"
                            >
                        </div>

                    </div>
                </div>

                {{-- Appearance --}}
                <div class="card">
                    <div class="section-header-wrap">
                        <h6 class="section-title">
                            <i class="fas fa-palette text-secondary"></i>
                            Appearance
                        </h6>
                    </div>

                    <div class="section-body">
                        <div class="row g-3">

                            <div class="col-6">
                                <label class="field-label">Background</label>

                                <input
                                    type="color"
                                    name="bg_color"
                                    class="form-control form-control-color w-100 shadow-none p-1"
                                    value="{{ $settings['bg_color'] ?? '#ffffff' }}"
                                >
                            </div>

                            <div class="col-6">
                                <label class="field-label">Text Color</label>

                                <input
                                    type="color"
                                    name="text_color"
                                    class="form-control form-control-color w-100 shadow-none p-1"
                                    value="{{ $settings['text_color'] ?? '#0f172a' }}"
                                >
                            </div>

                            <div class="col-6">
                                <label class="field-label">Button Color</label>

                                <input
                                    type="color"
                                    name="btn_bg_color"
                                    class="form-control form-control-color w-100 shadow-none p-1"
                                    value="{{ $settings['btn_bg_color'] ?? '#2563eb' }}"
                                >
                            </div>

                            <div class="col-6">
                                <label class="field-label">Max Width</label>

                                <input
                                    type="text"
                                    name="max_width"
                                    class="form-control font-monospace"
                                    value="{{ $settings['max_width'] ?? '480px' }}"
                                >
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="popup-actions d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-submit-action">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>

                    <a href="{{ $indexUrl }}" class="btn btn-light border text-secondary">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
    // Toggle title visibility
    function toggleTitleControl(checked) {
        const wrap = document.getElementById('titleInputContainer');

        if (wrap) {
            wrap.style.display = checked ? 'block' : 'none';
        }
    }

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

    // Clear image
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
        const titleSw = document.getElementById('show_title');
        const btnSw = document.getElementById('show_button');
        const triggerType = document.getElementById('trigger-type');
        const popupType = document.getElementById('popup-type');

        if (titleSw) {
            toggleTitleControl(titleSw.checked);
        }

        if (btnSw) {
            toggleButtonControl(btnSw.checked);
        }

        if (triggerType) {
            handleTriggerChange(triggerType.value);
        }

        if (popupType) {
            handlePopupTypeChange(popupType.value);
        }

        updateFrequencyHint();
    });
</script>
@endsection