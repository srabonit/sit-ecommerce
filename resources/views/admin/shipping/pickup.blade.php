@extends('layouts.admin')
@section('title', 'Local Pickup - ' . config('app.name'))

@php
    $resolvePickupMedia = function($path, $fallback = 'https://placehold.co/100x100?text=Pickup') {
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
    /* Local Pickup Theme */
    #localPickupRoot {
        --pickup-primary: var(--bs-primary, #1572e8);
        --pickup-primary-soft: #eef5ff;
        --pickup-text: #344054;
        --pickup-heading: #1d2939;
        --pickup-muted: #667085;
        --pickup-border: #e4e7ec;
        --pickup-surface: #ffffff;
        --pickup-soft: #f8fafc;
        --pickup-success: #12b76a;
        color: var(--pickup-text);
        min-width: 0;
    }

    #localPickupRoot *,
    #pickupNoticeModal * {
        box-sizing: border-box;
    }

    /* Consistent Radius */
    #localPickupRoot .btn,
    #localPickupRoot button,
    #localPickupRoot .form-control,
    #localPickupRoot .form-select,
    #localPickupRoot .input-group-text,
    #localPickupRoot .nav-link,
    #localPickupRoot .alert,
    #localPickupRoot .badge,
    #localPickupRoot .pickup-toggle-card,
    #localPickupRoot .checkout-preview-box,
    #localPickupRoot .pickup-media-chooser-surface,
    #pickupNoticeModal .btn,
    #pickupNoticeModal .modal-content {
        border-radius: 5px !important;
    }

    #localPickupRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #localPickupRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Buttons */
    #localPickupRoot .btn-compact,
    #pickupNoticeModal .btn-compact {
        min-height: 32px;
        padding: 6px 13px !important;
        font-size: 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        white-space: nowrap;
        transition: all 0.18s ease;
    }

    #localPickupRoot .btn-primary {
        background: var(--pickup-primary);
        border-color: var(--pickup-primary);
    }

    #localPickupRoot .btn-primary:hover {
        filter: brightness(.94);
    }

    #localPickupRoot .btn:focus-visible,
    #localPickupRoot button:focus-visible {
        outline: 2px solid var(--pickup-primary);
        outline-offset: 2px;
    }

    /* Cards */
    #localPickupRoot .pickup-panel-card {
        background: var(--pickup-surface);
        border: 1px solid var(--pickup-border);
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(16, 24, 40, .05);
        overflow: hidden;
        min-width: 0;
    }

    #localPickupRoot .pickup-panel-header {
        min-height: 46px;
        padding: 11px 16px;
        background: var(--pickup-surface);
        border-bottom: 1px solid var(--pickup-border);
    }

    #localPickupRoot .pickup-panel-header span,
    #localPickupRoot .pickup-panel-header h6 {
        color: var(--pickup-heading);
    }

    /* Shipping Navigation */
    #localPickupRoot .shipping-subnav-wrap {
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
        -ms-overflow-style: none;
        -webkit-overflow-scrolling: touch;
    }

    #localPickupRoot .shipping-subnav-wrap::-webkit-scrollbar {
        display: none;
    }

    #localPickupRoot .shipping-nav-pills {
        background: #f2f4f7;
        padding: 4px;
        border-radius: 6px !important;
        display: inline-flex;
        gap: 4px;
        min-width: max-content;
    }

    #localPickupRoot .shipping-nav-pills .nav-link {
        color: #475467;
        font-size: 12px;
        font-weight: 600;
        padding: 7px 14px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: transparent;
        transition: all .18s ease;
    }

    #localPickupRoot .shipping-nav-pills .nav-link:hover {
        color: var(--pickup-primary);
        background: #ffffff;
    }

    #localPickupRoot .shipping-nav-pills .nav-link.active {
        color: #ffffff !important;
        background: var(--pickup-primary) !important;
        box-shadow: 0 1px 3px rgba(16, 24, 40, .12);
    }

    /* Enable Toggle */
    #localPickupRoot .pickup-toggle-card {
        background: var(--pickup-soft);
        border: 1px solid var(--pickup-border);
        padding: 13px 15px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        transition: all .18s ease;
    }

    #localPickupRoot .pickup-toggle-card:hover {
        border-color: #b2ccff;
        background: #ffffff;
    }

    #localPickupRoot .pickup-toggle-card:has(input:checked) {
        border-color: var(--pickup-primary);
        background: var(--pickup-primary-soft);
    }

    #localPickupRoot .pickup-toggle-card .form-check-input {
        width: 40px;
        height: 21px;
        margin: 0;
        cursor: pointer;
    }

    #localPickupRoot .pickup-toggle-card .form-check-input:checked {
        background-color: var(--pickup-primary);
        border-color: var(--pickup-primary);
    }

    /* Form Controls */
    #localPickupRoot .form-label {
        display: block;
        margin-bottom: 5px;
        color: var(--pickup-text);
        font-size: 12px;
        font-weight: 600;
    }

    #localPickupRoot .form-control,
    #localPickupRoot .form-select {
        min-height: 36px;
        padding: 7px 10px;
        color: var(--pickup-heading);
        background-color: #ffffff;
        border: 1px solid #d0d5dd;
        box-shadow: none;
        font-size: 12.5px;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    #localPickupRoot .form-control:focus,
    #localPickupRoot .form-select:focus {
        border-color: var(--pickup-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
        outline: none;
    }

    #localPickupRoot .form-control::placeholder {
        color: #98a2b3;
        opacity: 1;
    }

    #localPickupRoot .input-group-text {
        min-height: 36px;
        padding: 6px 11px;
        color: var(--pickup-muted);
        background: var(--pickup-soft);
        border-color: #d0d5dd;
        font-size: 12px;
    }

    #localPickupRoot .form-label + .input-group {
        width: 100%;
    }

    /* Quick Fee Buttons */
    #localPickupRoot .pickup-panel-card .btn-light {
        border-color: var(--pickup-border) !important;
        background: #f9fafb;
    }

    #localPickupRoot .pickup-panel-card .btn-light:hover {
        background: var(--pickup-primary-soft);
        border-color: #b2ccff !important;
    }

    /* Media Chooser */
    #localPickupRoot .pickup-media-chooser-surface {
        padding: 13px;
        background: var(--pickup-soft);
        border: 1px dashed #cbd5e1;
        transition: border-color .18s ease, background .18s ease;
    }

    #localPickupRoot .pickup-media-chooser-surface:hover {
        border-color: var(--pickup-primary);
        background: #f5f9ff;
    }

    #localPickupRoot .media-chooser-actions {
        flex-wrap: wrap;
    }

    #localPickupRoot #pickupImagePreviewContainer {
        min-width: 0;
        max-width: 100%;
        border-color: var(--pickup-border) !important;
    }

    #localPickupRoot #pickupImagePreviewTag {
        flex-shrink: 0;
        border-radius: 5px !important;
    }

    #localPickupRoot #pickupImagePreviewPath {
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Checkout Preview */
    #localPickupRoot .checkout-preview-box {
        padding: 13px;
        background: var(--pickup-primary-soft);
        border: 1px solid #b2ccff;
        transition: all .18s ease;
    }

    #localPickupRoot .checkout-preview-box.disabled-preview {
        border-color: var(--pickup-border);
        background: var(--pickup-soft);
        opacity: .65;
    }

    #localPickupRoot #previewTitleText,
    #localPickupRoot #previewSubText {
        overflow-wrap: anywhere;
    }

    #localPickupRoot .min-width-0 {
        min-width: 0;
    }

    /* Typography */
    #localPickupRoot h5,
    #localPickupRoot h6,
    #localPickupRoot strong {
        color: var(--pickup-heading);
    }

    #localPickupRoot .text-muted {
        color: var(--pickup-muted) !important;
    }

    #localPickupRoot .bn-digit,
    #localPickupRoot .font-monospace {
        font-weight: 700 !important;
    }

    #localPickupRoot small {
        line-height: 1.5;
    }

    /* Alerts */
    #localPickupRoot .alert {
        border: 1px solid transparent;
    }

    /* Modal */
    #pickupNoticeModal .modal-content {
        border: 1px solid var(--pickup-border) !important;
        box-shadow: 0 12px 32px rgba(16, 24, 40, .16);
    }

    #pickupNoticeModal .modal-dialog {
        margin-left: auto;
        margin-right: auto;
    }

    /* Responsive */
    @media (min-width: 1200px) {
        #localPickupRoot {
            padding-left: 20px !important;
            padding-right: 20px !important;
        }
    }

    @media (max-width: 991.98px) {
        #localPickupRoot .pickup-panel-card {
            height: auto;
        }
    }

    @media (max-width: 767.98px) {
        #localPickupRoot .shipping-nav-pills {
            display: flex;
            min-width: max-content;
        }

        #localPickupRoot .shipping-nav-pills .nav-link {
            padding: 7px 12px;
            font-size: 11.5px;
        }

        #localPickupRoot .top-save-btn-wrap {
            width: 100%;
        }

        #localPickupRoot .top-save-btn-wrap .btn-compact {
            width: 100%;
            min-height: 36px;
        }

        #localPickupRoot .media-chooser-actions {
            flex-direction: column;
            align-items: stretch !important;
        }

        #localPickupRoot .media-chooser-actions .btn-compact {
            width: 100%;
            min-height: 36px;
        }

        #localPickupRoot .pickup-panel-header {
            padding: 11px 13px;
        }

        #localPickupRoot .pickup-toggle-card {
            padding: 11px 12px;
        }

        #localPickupRoot .row.g-3 {
            --bs-gutter-x: 12px;
            --bs-gutter-y: 12px;
        }

        #localPickupRoot .pickup-panel-card > .p-3,
        #localPickupRoot .pickup-panel-card > .p-3.p-md-4 {
            padding: 13px !important;
        }

        #localPickupRoot .checkout-preview-box {
            padding: 11px;
        }

        #localPickupRoot #pickupImagePreviewContainer {
            flex-wrap: wrap;
        }

        #localPickupRoot #pickupImagePreviewContainer .ms-auto {
            margin-left: 0 !important;
        }

        #localPickupRoot .w-sm-auto {
            width: 100% !important;
        }
    }

    @media (max-width: 400px) {
        #localPickupRoot .pickup-panel-header {
            padding: 10px 11px;
        }

        #localPickupRoot .pickup-media-chooser-surface {
            padding: 10px;
        }

        #localPickupRoot .btn-compact {
            white-space: normal;
        }

        #pickupNoticeModal .modal-dialog {
            margin: 12px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="localPickupRoot">
    <!-- Shipping Navigation -->
    <div class="shipping-subnav-wrap mb-3 pb-2 border-bottom">
        <ul class="nav nav-pills shipping-nav-pills flex-nowrap text-nowrap">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.shipping.index') }}">
                    <i class="fas fa-globe-asia" style="font-size: 11px;"></i>
                    <span>Zones</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.shipping.settings') }}">
                    <i class="fas fa-sliders-h" style="font-size: 11px;"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.shipping.classes') }}">
                    <i class="fas fa-boxes" style="font-size: 11px;"></i>
                    <span>Classes</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.shipping.pickup') }}">
                    <i class="fas fa-store" style="font-size: 11px;"></i>
                    <span>Pickup</span>
                </a>
            </li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-xs py-2 px-3 mb-3 small d-flex align-items-center justify-content-between" role="alert">
            <div>
                <i class="fas fa-check-circle text-success me-2"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.shipping.pickup.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        @php
            $isPickupEnabled = ($pickupSettings['local_pickup_enabled'] ?? '0') == '1';
            $pickupTitleVal  = $pickupSettings['local_pickup_title'] ?? 'Pickup';
            $pickupCostVal   = $pickupSettings['local_pickup_cost'] ?? 0;
            $pickupImgRaw    = $pickupSettings['local_pickup_image'] ?? null;
            $pickupImgSrc    = $resolvePickupMedia($pickupImgRaw, '');
        @endphp

        <!-- Hidden Media Fields -->
        <input type="hidden" name="local_pickup_image_media" id="pickupImageMediaInput" value="">
        <input type="hidden" name="remove_pickup_image" id="removePickupImageFlag" value="0">

        <!-- Page Header -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                        <i class="fas fa-store text-primary"></i>
                        <span>Local Pickup</span>
                    </h5>
                    <span id="headerStatusBadge" class="badge {{ $isPickupEnabled ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border' }} px-2 py-1" style="font-size: 10.5px;">
                        {{ $isPickupEnabled ? 'Active' : 'Off' }}
                    </span>
                </div>
                <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">Allow customers to collect orders in-store.</small>
            </div>
            <div class="top-save-btn-wrap">
                <button type="submit" class="btn btn-primary btn-compact px-3 py-2 shadow-xs gap-1">
                    <i class="fas fa-save"></i>
                    <span>Save</span>
                </button>
            </div>
        </div>

        <div class="row g-3 g-md-4">
            <!-- Pickup Settings -->
            <div class="col-12 col-lg-8">
                <div class="pickup-panel-card mb-3">
                    <div class="pickup-panel-header d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark" style="font-size: 13.5px;">
                            <i class="fas fa-sliders-h text-primary me-1"></i> Pickup Settings
                        </span>
                        <small class="text-muted" style="font-size: 11px;">Checkout</small>
                    </div>

                    <div class="p-3 p-md-4">
                        <!-- Enable Pickup -->
                        <label class="pickup-toggle-card mb-3" for="localPickupEnableSwitch">
                            <div>
                                <span class="fw-bold text-dark d-block" style="font-size: 13px;">Enable Pickup</span>
                                <small class="text-muted" style="font-size: 11.5px;">Show this option at checkout.</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input m-0" type="checkbox" id="localPickupEnableSwitch" name="local_pickup_enabled" value="1" {{ $isPickupEnabled ? 'checked' : '' }} onchange="syncPickupLivePreview()" style="width: 40px; height: 20px; cursor: pointer;">
                            </div>
                        </label>

                        <div class="row g-3 mb-3">
                            <!-- Pickup Name -->
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="pickupTitleInput">Pickup Name <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-muted"><i class="fas fa-tag" style="font-size: 11px;"></i></span>
                                    <input type="text" name="local_pickup_title" id="pickupTitleInput" class="form-control fw-semibold" value="{{ $pickupTitleVal }}" placeholder="Store Pickup" oninput="syncPickupLivePreview()" required>
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 11px;">Shown at checkout and on invoices.</small>
                            </div>

                            <!-- Pickup Fee -->
                            <div class="col-12 col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-1">
                                    <label class="form-label mb-0" for="pickupCostInput">Pickup Fee (৳)</label>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-light border py-0 px-2 fw-bold text-success bn-digit" style="font-size: 10px;" onclick="setQuickPickupCost(0)">Free</button>
                                        <button type="button" class="btn btn-light border py-0 px-2 fw-semibold text-secondary bn-digit" style="font-size: 10px;" onclick="setQuickPickupCost(30)">৳30</button>
                                        <button type="button" class="btn btn-light border py-0 px-2 fw-semibold text-secondary bn-digit" style="font-size: 10px;" onclick="setQuickPickupCost(50)">৳50</button>
                                    </div>
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-dark fw-bold bn-digit">৳</span>
                                    <input type="number" step="0.01" name="local_pickup_cost" id="pickupCostInput" class="form-control font-monospace bn-digit fw-bold" value="{{ $pickupCostVal }}" oninput="syncPickupLivePreview()">
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 11px;">Enter 0 for free pickup.</small>
                            </div>
                        </div>

                        <!-- Store Image -->
                        <div class="mb-2">
                            <label class="form-label mb-1">Store Image</label>
                            <div class="pickup-media-chooser-surface">
                                <div class="media-chooser-actions d-flex align-items-center gap-2 mb-2 flex-wrap">
                                    <button type="button" class="btn btn-primary btn-compact shadow-xs" onclick="choosePickupLocationMedia()">
                                        <i class="fas fa-photo-video me-1"></i>
                                        <span>Media Library</span>
                                    </button>
                                    <label class="btn btn-outline-secondary btn-compact shadow-xs mb-0" style="cursor: pointer;">
                                        <i class="fas fa-upload me-1"></i>
                                        <span>Upload</span>
                                        <input type="file" name="local_pickup_image_file" id="pickupFileInput" class="d-none" accept="image/*" onchange="previewPickupLocalImage(this)">
                                    </label>
                                </div>
                                <small class="text-muted d-block mb-2" style="font-size: 10.5px;">Add a store photo or logo.</small>

                                <div id="pickupImagePreviewContainer" class="{{ !empty($pickupImgSrc) ? 'd-flex' : 'd-none' }} align-items-center gap-2 bg-white border p-2" style="border-radius: 5px;">
                                    <img id="pickupImagePreviewTag" src="{{ $pickupImgSrc }}" style="width: 44px; height: 44px; object-fit: cover; border-radius: 4px;" onerror="this.src='https://placehold.co/100x100?text=Store';">
                                    <div class="text-truncate">
                                        <span class="small text-success fw-bold d-block" style="font-size: 11.5px;"><i class="fas fa-check-circle me-1"></i>Image Added</span>
                                        <small class="text-muted text-truncate d-block font-monospace" id="pickupImagePreviewPath" style="font-size: 10px; max-width: 220px;">{{ $pickupImgRaw ?: 'Attached Media' }}</small>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger btn-compact py-1 px-2 ms-auto" style="font-size: 10.5px !important;" onclick="clearPickupMedia()">
                                        <i class="fas fa-trash-alt me-1"></i>Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mb-2">
                    <button type="submit" class="btn btn-primary btn-compact px-4 py-2 shadow-sm w-100 w-sm-auto" style="font-size: 13px !important;">
                        <i class="fas fa-save me-2"></i>
                        <span>Save</span>
                    </button>
                </div>
            </div>

            <!-- Live Preview -->
            <div class="col-12 col-lg-4">
                <div class="pickup-panel-card">
                    <div class="pickup-panel-header d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13px;">
                            <i class="fas fa-eye text-primary"></i>
                            <span>Live Preview</span>
                        </h6>
                        <span class="badge bg-white text-secondary border px-2 py-0" style="font-size: 9.5px;">Checkout</span>
                    </div>

                    <div class="p-3">
                        <div id="livePreviewCard" class="checkout-preview-box {{ $isPickupEnabled ? '' : 'disabled-preview' }} mb-3">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <div class="d-flex align-items-center gap-2 min-width-0">
                                    <input type="radio" class="form-check-input m-0 flex-shrink-0" checked disabled>

                                    <!-- Store Thumbnail -->
                                    <div id="previewStoreThumbWrap" class="{{ !empty($pickupImgSrc) ? '' : 'd-none' }} flex-shrink-0 border bg-white p-0.5" style="width: 32px; height: 32px; border-radius: 4px; overflow: hidden;">
                                        <img id="previewStoreThumbImg" src="{{ $pickupImgSrc }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='https://placehold.co/40x40?text=S';">
                                    </div>

                                    <div class="text-truncate">
                                        <strong class="text-dark d-block text-truncate" id="previewTitleText" style="font-size: 13px;">{{ $pickupTitleVal ?: 'Pickup' }}</strong>
                                        <small class="text-muted d-block text-truncate" id="previewSubText" style="font-size: 10.5px;">
                                            {{ $isPickupEnabled ? 'Collect in store' : 'Unavailable' }}
                                        </small>
                                    </div>
                                </div>
                                <span class="badge {{ (float)$pickupCostVal <= 0 ? 'bg-success text-white' : 'bg-dark text-white' }} px-2 py-1 font-monospace bn-digit flex-shrink-0" id="previewCostBadge" style="font-size: 11px;">
                                    {{ (float)$pickupCostVal <= 0 ? 'FREE' : '৳' . number_format((float)$pickupCostVal, 2) }}
                                </span>
                            </div>
                        </div>

                        <div class="text-secondary" style="font-size: 12px; line-height: 1.5;">
                            <strong class="text-dark d-block mb-1"><i class="fas fa-circle-info text-primary me-1"></i> Tips</strong>
                            <ul class="ps-3 mb-0 d-flex flex-column gap-1">
                                <li>Free pickup can attract nearby customers.</li>
                                <li>Add a store photo or logo.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Notice Modal -->
<div class="modal fade" id="pickupNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 44px; height: 44px;">
                    <i class="fas fa-exclamation-triangle fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="pickupNoticeTitle" style="font-size: 14px;">Notice</h6>
                <p class="text-muted small mb-3" id="pickupNoticeMsg" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showPickupNotice(message, title = 'Notice') {
        const titleEl = document.getElementById('pickupNoticeTitle');
        const msgEl   = document.getElementById('pickupNoticeMsg');
        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;
        const modalEl = document.getElementById('pickupNoticeModal');
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

    // Global Media Library Chooser Handler
    function choosePickupLocationMedia() {
        if (typeof window.openMediaPicker === 'function') {
            window.openMediaPicker({
                multiple: false,
                onSelect: function(media) {
                    const picked = extractMediaItem(media);
                    if (picked && picked.path) {
                        document.getElementById('pickupImageMediaInput').value = picked.path;
                        document.getElementById('removePickupImageFlag').value = '0';
                        const fileEl = document.getElementById('pickupFileInput');
                        if (fileEl) fileEl.value = '';

                        const wrap = document.getElementById('pickupImagePreviewContainer');
                        const img  = document.getElementById('pickupImagePreviewTag');
                        const pathTxt = document.getElementById('pickupImagePreviewPath');
                        if (img) img.src = picked.url;
                        if (pathTxt) pathTxt.innerText = picked.path;
                        if (wrap) {
                            wrap.classList.remove('d-none');
                            wrap.classList.add('d-flex');
                        }

                        // Sync to live checkout preview
                        const thumbWrap = document.getElementById('previewStoreThumbWrap');
                        const thumbImg  = document.getElementById('previewStoreThumbImg');
                        if (thumbImg) thumbImg.src = picked.url;
                        if (thumbWrap) thumbWrap.classList.remove('d-none');
                    }
                }
            });
        } else {
            showPickupNotice('Media Library unavailable. Try again.');
        }
    }

    // Local File Upload Preview Handler
    function previewPickupLocalImage(input) {
        if (input.files && input.files[0]) {
            document.getElementById('pickupImageMediaInput').value = '';
            document.getElementById('removePickupImageFlag').value = '0';
            const fileName = input.files[0].name;

            const reader = new FileReader();
            reader.onload = function(e) {
                const wrap = document.getElementById('pickupImagePreviewContainer');
                const img  = document.getElementById('pickupImagePreviewTag');
                const pathTxt = document.getElementById('pickupImagePreviewPath');
                if (img) img.src = e.target.result;
                if (pathTxt) pathTxt.innerText = fileName;
                if (wrap) {
                    wrap.classList.remove('d-none');
                    wrap.classList.add('d-flex');
                }

                // Sync to live checkout preview
                const thumbWrap = document.getElementById('previewStoreThumbWrap');
                const thumbImg  = document.getElementById('previewStoreThumbImg');
                if (thumbImg) thumbImg.src = e.target.result;
                if (thumbWrap) thumbWrap.classList.remove('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Clear Selected Media Handler
    function clearPickupMedia() {
        document.getElementById('pickupImageMediaInput').value = '';
        document.getElementById('removePickupImageFlag').value = '1';
        const fileEl = document.getElementById('pickupFileInput');
        if (fileEl) fileEl.value = '';

        const wrap = document.getElementById('pickupImagePreviewContainer');
        if (wrap) {
            wrap.classList.add('d-none');
            wrap.classList.remove('d-flex');
        }

        const thumbWrap = document.getElementById('previewStoreThumbWrap');
        if (thumbWrap) thumbWrap.classList.add('d-none');
    }

    function setQuickPickupCost(amount) {
        const costInput = document.getElementById('pickupCostInput');
        if (costInput) {
            costInput.value = amount;
            syncPickupLivePreview();
        }
    }

    function syncPickupLivePreview() {
        const isEnabled = document.getElementById('localPickupEnableSwitch').checked;
        const titleVal  = (document.getElementById('pickupTitleInput').value || '').trim() || 'Pickup';
        const costVal   = parseFloat(document.getElementById('pickupCostInput').value) || 0;

        const headerBadge  = document.getElementById('headerStatusBadge');
        const previewCard  = document.getElementById('livePreviewCard');
        const previewTitle = document.getElementById('previewTitleText');
        const previewSub   = document.getElementById('previewSubText');
        const previewCost  = document.getElementById('previewCostBadge');

        if (headerBadge) {
            headerBadge.className = isEnabled
                ? 'badge bg-success-subtle text-success border border-success-subtle px-2 py-1'
                : 'badge bg-secondary-subtle text-secondary border px-2 py-1';
            headerBadge.innerText = isEnabled ? 'Active' : 'Off';
        }

        if (previewCard) {
            previewCard.classList.toggle('disabled-preview', !isEnabled);
        }
        if (previewTitle) {
            previewTitle.innerText = titleVal;
        }
        if (previewSub) {
            previewSub.innerText = isEnabled ? 'Collect in store' : 'Unavailable';
        }
        if (previewCost) {
            if (costVal <= 0) {
                previewCost.className = 'badge bg-success text-white px-2 py-1 font-monospace bn-digit flex-shrink-0';
                previewCost.innerText = 'FREE';
            } else {
                previewCost.className = 'badge bg-dark text-white px-2 py-1 font-monospace bn-digit flex-shrink-0';
                previewCost.innerText = '৳' + costVal.toFixed(2);
            }
        }
    }
</script>
@endsection