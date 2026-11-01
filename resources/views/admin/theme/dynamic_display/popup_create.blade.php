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
    #popup-create-root {
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        letter-spacing: -0.01em;
        color: #1e293b;
    }

    .font-monospace {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
    }

    #popup-create-root .btn,
    #popup-create-root .btn-sm,
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

    #popup-create-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
        font-size: 12px;
        background-color: #f8fafc;
        border-color: #cbd5e1;
    }

    #popup-create-root .input-group > .form-control,
    #popup-create-root .input-group > .form-select {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    .btn-compact {
        font-size: 11px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    #popup-create-root .card {
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }

    .section-header-wrap {
        padding: 10px 14px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
    }

    .field-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }

    .field-hint {
        font-size: 10px;
        color: #64748b;
        margin-top: 3px;
        display: block;
        line-height: 1.35;
    }

    .form-control,
    .form-select {
        min-height: 32px;
        font-size: 12px !important;
        border: 1px solid #cbd5e1 !important;
        color: #0f172a !important;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        outline: none;
    }

    .popup-preview-box {
        background: #f8fafc;
        border: 1.5px dashed #cbd5e1;
        padding: 8px 10px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .device-check-card {
        border: 1px solid #e2e8f0;
        background-color: #f8fafc;
        padding: 8px 10px;
        transition: all 0.15s ease;
    }

    .device-check-card:hover {
        background-color: #ffffff;
        border-color: #cbd5e1;
    }

    @media (max-width: 767.98px) {
        .popup-create-header-wrap h5 {
            font-size: 16px !important;
        }

        .top-action-btns {
            width: 100%;
        }

        .top-action-btns .btn {
            width: 100%;
            padding: 8px 12px !important;
            font-size: 11px !important;
            justify-content: center;
        }

        #popup-create-root .card {
            margin-bottom: 12px !important;
        }

        .btn-submit-action {
            width: 100% !important;
            padding: 10px 14px !important;
            font-size: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="popup-create-root">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 popup-create-header-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-bullhorn text-primary"></i>
                    <span>Create Popup</span>
                </h5>

                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace" style="font-size: 10px;">
                    New Campaign
                </span>
            </div>

            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                Create banners, custom HTML, newsletter forms or coupon popups.
            </small>
        </div>

        <div class="top-action-btns d-flex gap-2">
            <a href="{{ $indexUrl }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold bg-white text-dark">
                <i class="fas fa-arrow-left me-1"></i> Back to Display Hub
            </a>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3" role="alert">
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
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap">
                        <span class="fw-bold text-dark small" style="font-size: 12px;">
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
                                    Disable to hide the title.
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
                                    <strong>Tip:</strong> Disable the title and button for an image-only popup.
                                </small>

                                {{-- Image Preview --}}
                                <div id="popup-image-preview-wrap" class="mt-2 {{ old('image') ? '' : 'd-none' }}">
                                    <div class="popup-preview-box">
                                        <img
                                            id="popup-image-preview"
                                            src="{{ old('image') }}"
                                            alt="Preview"
                                            style="max-height: 60px; max-width: 120px; object-fit: cover; border-radius: 4px;"
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
                                    placeholder="Enter text, HTML or shortcode. Leave blank for image-only."
                                >{{ old('content') }}</textarea>

                                <small class="field-hint" id="content-hint">
                                    Supports text, HTML and dynamic form shortcodes.
                                </small>
                            </div>

                            {{-- CTA Button --}}
                            <div class="col-12 mt-2">
                                <div class="p-2 border rounded bg-light bg-opacity-50" style="border-radius: 5px !important;">

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
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap">
                        <span class="fw-bold text-dark small" style="font-size: 12px;">
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
                                    <option value="per_page" selected>Per Page</option>
                                    <option value="global">Global</option>
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
                                    placeholder="Never show on&#10;cart&#10;checkout"
                                ></textarea>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column --}}
            <div class="col-12 col-lg-4">

                {{-- Trigger & Frequency --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap">
                        <span class="fw-bold text-dark small" style="font-size: 12px;">
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

                        <div class="mb-3 border-top pt-2">
                            <label class="field-label">
                                Repeat Frequency <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">
                                <input
                                    type="number"
                                    name="frequency_days"
                                    class="form-control font-monospace bn-digit shadow-none"
                                    value="0"
                                    min="0"
                                >

                                <span class="input-group-text font-monospace small" style="font-size: 11px;">
                                    Days
                                </span>
                            </div>

                            <small class="field-hint">
                                0 = Every page load. 1 = Once every 24 hours.
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
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap">
                        <span class="fw-bold text-dark small" style="font-size: 12px;">
                            <i class="fas fa-calendar-alt text-info me-1"></i>
                            Devices & Schedule
                        </span>
                    </div>

                    <div class="p-3">

                        <label class="field-label">Show On Devices</label>

                        <div class="d-flex flex-column gap-1 mb-3">

                            <label class="device-check-card d-flex align-items-center gap-2 mb-0" for="p_desktop">
                                <input class="form-check-input mt-0" type="checkbox" name="device_desktop" id="p_desktop" value="1" checked>
                                <span class="small font-monospace" style="font-size: 11px;">Desktop</span>
                            </label>

                            <label class="device-check-card d-flex align-items-center gap-2 mb-0" for="p_tablet">
                                <input class="form-check-input mt-0" type="checkbox" name="device_tablet" id="p_tablet" value="1" checked>
                                <span class="small font-monospace" style="font-size: 11px;">Tablet</span>
                            </label>

                            <label class="device-check-card d-flex align-items-center gap-2 mb-0" for="p_mobile">
                                <input class="form-check-input mt-0" type="checkbox" name="device_mobile" id="p_mobile" value="1" checked>
                                <span class="small font-monospace" style="font-size: 11px;">Mobile</span>
                            </label>

                        </div>

                        <div class="mb-2">
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
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap">
                        <span class="fw-bold text-dark small" style="font-size: 12px;">
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
    });
</script>
@endsection