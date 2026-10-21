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
    /* Global Strict 5px Element Radius */
    #localPickupRoot .btn,
    #localPickupRoot .btn-sm,
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

    /* Compact & Sleek Button Sizing */
    #localPickupRoot .btn-compact,
    #pickupNoticeModal .btn-compact {
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

    /* Enterprise Surface Cards */
    .pickup-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }
    .pickup-panel-header {
        padding: 12px 16px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    /* Segmented Sub-Navigation Bar */
    .shipping-subnav-wrap {
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
        -ms-overflow-style: none;
        -webkit-overflow-scrolling: touch;
    }
    .shipping-subnav-wrap::-webkit-scrollbar {
        display: none;
    }
    .shipping-nav-pills {
        background: #f1f5f9;
        padding: 4px;
        border-radius: 6px !important;
        display: inline-flex;
        gap: 4px;
    }
    .shipping-nav-pills .nav-link {
        color: #475569;
        font-weight: 600;
        font-size: 12px;
        padding: 6px 14px;
        transition: all 0.15s ease;
        background: transparent;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .shipping-nav-pills .nav-link:hover {
        color: #0f172a;
        background-color: rgba(255, 255, 255, 0.6);
    }
    .shipping-nav-pills .nav-link.active {
        background-color: #2563eb !important;
        color: #ffffff !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25);
    }

    /* Interactive Enable Toggle Card */
    .pickup-toggle-card {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        padding: 12px 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        transition: all 0.15s ease;
    }
    .pickup-toggle-card:hover {
        border-color: #93c5fd;
        background: #ffffff;
    }
    .pickup-toggle-card:has(input:checked) {
        border-color: #2563eb;
        background-color: #eff6ff;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.08);
    }

    /* Form Controls */
    #localPickupRoot .form-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #localPickupRoot .form-control {
        font-size: 12.5px;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        height: 34px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #localPickupRoot .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Global Media Chooser Surface Box */
    .pickup-media-chooser-surface {
        background: #f8fafc;
        border: 1.5px dashed #cbd5e1;
        border-radius: 5px !important;
        padding: 12px;
        transition: border-color 0.15s ease;
    }
    .pickup-media-chooser-surface:hover {
        border-color: #2563eb;
    }

    /* Live Checkout Preview Card */
    .checkout-preview-box {
        border: 1.5px solid #2563eb;
        background: #eff6ff;
        padding: 12px 14px;
        transition: all 0.15s ease;
    }
    .checkout-preview-box.disabled-preview {
        border-color: #cbd5e1;
        background: #f8fafc;
        opacity: 0.65;
    }

    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* 100/100 Mobile Responsive Tuning */
    @media (max-width: 767.98px) {
        .shipping-nav-pills {
            display: flex;
            min-width: max-content;
        }
        .shipping-nav-pills .nav-link {
            padding: 6px 12px;
            font-size: 11.5px;
        }
        .top-save-btn-wrap {
            width: 100%;
        }
        .top-save-btn-wrap .btn-compact {
            width: 100%;
            padding: 7px 14px !important;
        }
        .media-chooser-actions {
            flex-direction: column;
            align-items: stretch !important;
        }
        .media-chooser-actions .btn-compact {
            width: 100%;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="localPickupRoot">
    <!-- Sub-Menu Navigation (Mobile Scrollable) -->
    <div class="shipping-subnav-wrap mb-3 pb-2 border-bottom">
        <ul class="nav nav-pills shipping-nav-pills flex-nowrap text-nowrap">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.shipping.index') }}">
                    <i class="fas fa-globe-asia" style="font-size: 11px;"></i>
                    <span>Shipping zones</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.shipping.settings') }}">
                    <i class="fas fa-sliders-h" style="font-size: 11px;"></i>
                    <span>Shipping settings</span>
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
                    <span>Local pickup</span>
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

        <!-- Hidden Flags for Global Media Chooser -->
        <input type="hidden" name="local_pickup_image_media" id="pickupImageMediaInput" value="">
        <input type="hidden" name="remove_pickup_image" id="removePickupImageFlag" value="0">

        <!-- Top Page Header & Quick Save -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                        <i class="fas fa-store text-primary"></i>
                        <span>Local Pickup Configuration</span>
                    </h5>
                    <span id="headerStatusBadge" class="badge {{ $isPickupEnabled ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border' }} px-2 py-1" style="font-size: 10.5px;">
                        {{ $isPickupEnabled ? 'Active' : 'Disabled' }}
                    </span>
                </div>
                <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">Allow customers to collect orders directly from your store or hub location.</small>
            </div>
            <div class="top-save-btn-wrap">
                <button type="submit" class="btn btn-primary btn-compact px-3 py-2 shadow-xs gap-1">
                    <i class="fas fa-save"></i>
                    <span>Save changes</span>
                </button>
            </div>
        </div>

        <div class="row g-3 g-md-4">
            <!-- Left Column: Configuration Form -->
            <div class="col-12 col-lg-8">
                <div class="pickup-panel-card mb-3">
                    <div class="pickup-panel-header d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark" style="font-size: 13.5px;">
                            <i class="fas fa-sliders-h text-primary me-1"></i> Pickup Method Settings
                        </span>
                        <small class="text-muted" style="font-size: 11px;">Storefront Checkout Option</small>
                    </div>

                    <div class="p-3 p-md-4">
                        <!-- Enable Switch Card -->
                        <label class="pickup-toggle-card mb-3" for="localPickupEnableSwitch">
                            <div>
                                <span class="fw-bold text-dark d-block" style="font-size: 13px;">Enable local pickup</span>
                                <small class="text-muted" style="font-size: 11.5px;">When enabled, customers can choose Local Pickup as a delivery option during checkout.</small>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input m-0" type="checkbox" id="localPickupEnableSwitch" name="local_pickup_enabled" value="1" {{ $isPickupEnabled ? 'checked' : '' }} onchange="syncPickupLivePreview()" style="width: 40px; height: 20px; cursor: pointer;">
                            </div>
                        </label>

                        <div class="row g-3 mb-3">
                            <!-- Pickup Title -->
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="pickupTitleInput">Pickup Title <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-muted"><i class="fas fa-tag" style="font-size: 11px;"></i></span>
                                    <input type="text" name="local_pickup_title" id="pickupTitleInput" class="form-control fw-semibold" value="{{ $pickupTitleVal }}" placeholder=" Store Pickup" oninput="syncPickupLivePreview()" required>
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 11px;">Label shown to customers on checkout and invoices.</small>
                            </div>

                            <!-- Pickup Cost -->
                            <div class="col-12 col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label mb-0" for="pickupCostInput">Pickup Cost (৳)</label>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-light border py-0 px-2 fw-bold text-success bn-digit" style="font-size: 10px;" onclick="setQuickPickupCost(0)">Free (৳0)</button>
                                        <button type="button" class="btn btn-light border py-0 px-2 fw-semibold text-secondary bn-digit" style="font-size: 10px;" onclick="setQuickPickupCost(30)">৳30</button>
                                        <button type="button" class="btn btn-light border py-0 px-2 fw-semibold text-secondary bn-digit" style="font-size: 10px;" onclick="setQuickPickupCost(50)">৳50</button>
                                    </div>
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-dark fw-bold bn-digit">৳</span>
                                    <input type="number" step="0.01" name="local_pickup_cost" id="pickupCostInput" class="form-control font-monospace bn-digit fw-bold" value="{{ $pickupCostVal }}" oninput="syncPickupLivePreview()">
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 11px;">Set <code>0</code> to offer free in-store pickup.</small>
                            </div>
                        </div>

                        <!-- Global Media Chooser for Store / Pickup Hub Badge -->
                        <div class="mb-2">
                            <label class="form-label mb-1">Store / Hub Location Image (Global Media Chooser)</label>
                            <div class="pickup-media-chooser-surface">
                                <div class="media-chooser-actions d-flex align-items-center gap-2 mb-2 flex-wrap">
                                    <button type="button" class="btn btn-primary btn-compact shadow-xs" onclick="choosePickupLocationMedia()">
                                        <i class="fas fa-photo-video me-1"></i>
                                        <span>Choose from Media Library</span>
                                    </button>
                                    <label class="btn btn-outline-secondary btn-compact shadow-xs mb-0" style="cursor: pointer;">
                                        <i class="fas fa-upload me-1"></i>
                                        <span>Upload Local File</span>
                                        <input type="file" name="local_pickup_image_file" id="pickupFileInput" class="d-none" accept="image/*" onchange="previewPickupLocalImage(this)">
                                    </label>
                                </div>
                                <small class="text-muted d-block mb-2" style="font-size: 10.5px;">Select an outlet photo or store badge icon from the Global Media Library.</small>

                                <div id="pickupImagePreviewContainer" class="{{ !empty($pickupImgSrc) ? 'd-flex' : 'd-none' }} align-items-center gap-2 bg-white border p-2" style="border-radius: 5px;">
                                    <img id="pickupImagePreviewTag" src="{{ $pickupImgSrc }}" style="width: 44px; height: 44px; object-fit: cover; border-radius: 4px;" onerror="this.src='https://placehold.co/100x100?text=Store';">
                                    <div class="text-truncate">
                                        <span class="small text-success fw-bold d-block" style="font-size: 11.5px;"><i class="fas fa-check-circle me-1"></i>Image Attached</span>
                                        <small class="text-muted text-truncate d-block font-monospace" id="pickupImagePreviewPath" style="font-size: 10px; max-width: 220px;">{{ $pickupImgRaw ?: 'Attached Media' }}</small>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger btn-compact py-1 px-2 ms-auto" style="font-size: 10.5px !important;" onclick="clearPickupMedia()">
                                        <i class="fas fa-trash-alt me-1"></i>Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mb-2">
                    <button type="submit" class="btn btn-primary btn-compact px-4 py-2 shadow-sm w-100 w-sm-auto" style="font-size: 13px !important;">
                        <i class="fas fa-save me-2"></i>
                        <span>Save changes</span>
                    </button>
                </div>
            </div>

            <!-- Right Column: Live Checkout Preview & Guide -->
            <div class="col-12 col-lg-4">
                <div class="pickup-panel-card">
                    <div class="pickup-panel-header d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13px;">
                            <i class="fas fa-eye text-primary"></i>
                            <span>Live Checkout Preview</span>
                        </h6>
                        <span class="badge bg-white text-secondary border px-2 py-0" style="font-size: 9.5px;">Customer View</span>
                    </div>

                    <div class="p-3">
                        <div id="livePreviewCard" class="checkout-preview-box {{ $isPickupEnabled ? '' : 'disabled-preview' }} mb-3">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <div class="d-flex align-items-center gap-2 min-width-0">
                                    <input type="radio" class="form-check-input m-0 flex-shrink-0" checked disabled>
                                    
                                    <!-- Store/Hub Thumbnail in Checkout Option -->
                                    <div id="previewStoreThumbWrap" class="{{ !empty($pickupImgSrc) ? '' : 'd-none' }} flex-shrink-0 border bg-white p-0.5" style="width: 32px; height: 32px; border-radius: 4px; overflow: hidden;">
                                        <img id="previewStoreThumbImg" src="{{ $pickupImgSrc }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='https://placehold.co/40x40?text=S';">
                                    </div>

                                    <div class="text-truncate">
                                        <strong class="text-dark d-block text-truncate" id="previewTitleText" style="font-size: 13px;">{{ $pickupTitleVal ?: 'Pickup' }}</strong>
                                        <small class="text-muted d-block text-truncate" id="previewSubText" style="font-size: 10.5px;">
                                            {{ $isPickupEnabled ? 'Collect in store' : 'Currently disabled on checkout' }}
                                        </small>
                                    </div>
                                </div>
                                <span class="badge {{ (float)$pickupCostVal <= 0 ? 'bg-success text-white' : 'bg-dark text-white' }} px-2 py-1 font-monospace bn-digit flex-shrink-0" id="previewCostBadge" style="font-size: 11px;">
                                    {{ (float)$pickupCostVal <= 0 ? 'FREE' : '৳' . number_format((float)$pickupCostVal, 2) }}
                                </span>
                            </div>
                        </div>

                        <div class="text-secondary" style="font-size: 12px; line-height: 1.5;">
                            <strong class="text-dark d-block mb-1"><i class="fas fa-circle-info text-primary me-1"></i> Quick Tips:</strong>
                            <ul class="ps-3 mb-0 d-flex flex-column gap-1">
                                <li>Offering <strong>Free (৳0)</strong> local pickup encourages nearby customers to collect orders directly.</li>
                                <li>Attaching a store logo or outlet photo helps customers easily identify your pickup station at checkout.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal: Notice (Replaces raw alert()) -->
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
            showPickupNotice('Media Library is loading, please try again.');
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
            headerBadge.innerText = isEnabled ? 'Active' : 'Disabled';
        }

        if (previewCard) {
            previewCard.classList.toggle('disabled-preview', !isEnabled);
        }
        if (previewTitle) {
            previewTitle.innerText = titleVal;
        }
        if (previewSub) {
            previewSub.innerText = isEnabled ? 'Collect in store' : 'Currently disabled on checkout';
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