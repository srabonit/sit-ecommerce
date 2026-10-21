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
    /* Global Strict 5px Element Radius */
    #courierConfigRoot .btn,
    #courierConfigRoot .btn-sm,
    #courierConfigRoot .btn-xs,
    #courierConfigRoot button,
    #courierConfigRoot .form-control,
    #courierConfigRoot .form-select,
    #courierConfigRoot .badge,
    #courierConfigRoot .alert,
    #courierConfigRoot .courier-collapsible-card,
    #courierNoticeModal .btn,
    #courierNoticeModal .modal-content {
        border-radius: 5px !important;
    }

    #courierConfigRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #courierConfigRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Compact & Sleek Button Sizing */
    #courierConfigRoot .btn-compact,
    #courierNoticeModal .btn-compact {
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

    /* Main Surface Header */
    .courier-hero-header {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        padding: 14px 18px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    /* Collapsible Gateway Accordion Cards */
    .courier-collapsible-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .courier-collapsible-card:hover {
        border-color: #cbd5e1;
    }
    .courier-collapsible-card.active-open {
        border-color: #2563eb !important;
        box-shadow: 0 3px 10px rgba(37, 99, 235, 0.08);
    }
    .courier-card-header {
        padding: 12px 16px;
        background: #f8fafc;
        cursor: pointer;
        user-select: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        transition: background-color 0.15s ease;
    }
    .courier-card-header:hover {
        background: #f1f5f9;
    }
    .courier-collapsible-card.active-open .courier-card-header {
        background: #eff6ff;
        border-bottom: 1px solid #dbeafe;
    }
    .courier-chevron-icon {
        width: 24px;
        height: 24px;
        border-radius: 5px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: #475569;
        transition: transform 0.2s ease, background-color 0.15s ease, color 0.15s ease;
    }
    .courier-collapsible-card.active-open .courier-chevron-icon {
        transform: rotate(180deg);
        background: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }
    .courier-card-body {
        padding: 16px;
        background: #ffffff;
    }

    /* Form Controls */
    #courierConfigRoot .form-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #courierConfigRoot .form-control,
    #courierConfigRoot .form-select {
        font-size: 12.5px;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #courierConfigRoot .form-control:focus,
    #courierConfigRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Global Media Chooser Surface Box inside Gateway Card */
    .courier-media-chooser-surface {
        background: #f8fafc;
        border: 1.5px dashed #cbd5e1;
        border-radius: 5px !important;
        padding: 10px 12px;
        transition: border-color 0.15s ease;
    }
    .courier-media-chooser-surface:hover {
        border-color: #2563eb;
    }
    .courier-logo-preview-box {
        width: 52px;
        height: 38px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 2px;
        flex-shrink: 0;
    }
    .courier-logo-preview-box img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* 100/100 Mobile Responsive Tuning */
    @media (max-width: 767.98px) {
        .courier-hero-header {
            padding: 12px;
        }
        .courier-card-body {
            padding: 12px;
        }
        .media-chooser-actions {
            flex-direction: column;
            align-items: stretch !important;
        }
        .media-chooser-actions .btn-compact {
            width: 100%;
        }
        .courier-card-footer {
            flex-direction: row !important;
            justify-content: space-between !important;
            align-items: center !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="courierConfigRoot">
    <!-- Top Header -->
    <div class="courier-hero-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-truck-ramp-box text-primary"></i>
                    <span>Courier Gateway Integration</span>
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 bn-digit font-monospace" style="font-size: 11px;">
                    {{ count($couriers) }} Gateways
                </span>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">Configure Steadfast, Pathao &amp; other API courier gateways with automated parcel booking.</small>
        </div>
        <div class="d-flex gap-2 w-100 w-sm-auto">
            <button type="button" class="btn btn-light border btn-compact px-3 flex-grow-1 flex-sm-grow-0" onclick="toggleAllCouriers(true)">
                <i class="fas fa-expand-alt me-1"></i>
                <span>Expand All</span>
            </button>
            <button type="button" class="btn btn-light border btn-compact px-3 flex-grow-1 flex-sm-grow-0" onclick="toggleAllCouriers(false)">
                <i class="fas fa-compress-alt me-1"></i>
                <span>Collapse All</span>
            </button>
        </div>
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

    <!-- Quick Jump Dropdown Selector -->
    <div class="bg-white border p-2 px-3 mb-3 d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center justify-content-between gap-2 shadow-xs" style="border-radius: 6px;">
        <div class="flex-grow-1">
            <select id="courierQuickDropdown" class="form-select form-select-sm fw-semibold" onchange="handleCourierDropdownSelect(this.value)">
                <option value="all">-- Select Courier Gateway to Configure (Show All {{ count($couriers) }} Gateways) --</option>
                @foreach($couriers as $c)
                    <option value="{{ $c->id }}">
                        {{ $c->courier_name }} — {{ $c->is_active ? 'Active' : 'Disabled' }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="button" class="btn btn-light border btn-compact px-3 text-secondary" onclick="resetCourierSelector()">
            <i class="fas fa-rotate-left me-1"></i>
            <span>Reset View</span>
        </button>
    </div>

    <!-- Collapsible Courier Gateways List -->
    <div class="row g-3" id="courierAccordionList">
        @foreach($couriers as $index => $c)
            @php
                $isSteadfast = str_contains(strtolower($c->courier_name), 'steadfast');
                $isPathao    = str_contains(strtolower($c->courier_name), 'pathao');
                $logoPath    = $c->logo ?? ($c->image ?? null);
                $logoUrl     = $resolveCourierLogo($logoPath, 'https://placehold.co/120x60?text=' . urlencode($c->courier_name));
            @endphp
            <div class="col-12 col-md-6 courier-col-item" data-courier-id="{{ $c->id }}">
                <div class="courier-collapsible-card {{ $index === 0 ? 'active-open' : '' }}" id="courierCard_{{ $c->id }}">
                    <!-- Collapsible Trigger Header -->
                    <div class="courier-card-header" onclick="toggleCourierPanel({{ $c->id }})">
                        <div class="d-flex align-items-center gap-2 min-width-0 flex-wrap">
                            <div class="courier-logo-preview-box shadow-xs">
                                <img src="{{ $logoUrl }}" id="courierHeaderLogoImg_{{ $c->id }}" alt="{{ $c->courier_name }}" onerror="this.src='https://placehold.co/120x60?text=Logo';">
                            </div>

                            <div class="min-width-0">
                                <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 13.5px;">{{ $c->courier_name }}</h6>
                                <small class="text-muted" style="font-size: 10.5px;">Automated API Booking</small>
                            </div>

                            <span class="badge {{ $c->is_active ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border' }} px-2 py-0.5 ms-1" style="font-size: 9.5px;">
                                {{ $c->is_active ? 'Active' : 'Disabled' }}
                            </span>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            @if($isSteadfast && $c->is_active)
                                <button type="button" class="btn btn-outline-primary btn-compact py-0 px-2 shadow-xs" onclick="event.stopPropagation(); checkCourierBalance({{ $c->id }})" title="Check live API wallet balance" style="font-size: 10.5px !important; height: 24px;">
                                    <i class="fas fa-wallet me-1"></i>
                                    <span>Balance</span>
                                </button>
                            @endif
                            <span class="courier-chevron-icon">
                                <i class="fas fa-chevron-down"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Collapsible Form Body -->
                    <div class="courier-card-body {{ $index === 0 ? '' : 'd-none' }}" id="courierBody_{{ $c->id }}">
                        <!-- Live Balance Container for Steadfast -->
                        <div id="balance_box_{{ $c->id }}" class="alert alert-info py-2 px-3 small mb-3 d-none align-items-center justify-content-between" style="border-radius: 5px;">
                            <div>
                                <i class="fas fa-coins text-warning me-1"></i>
                                <span>Current Balance:</span> <strong id="balance_val_{{ $c->id }}" class="font-monospace bn-digit">৳0.00</strong>
                            </div>
                            <button type="button" class="btn btn-link text-primary text-decoration-none p-0 fw-bold small" onclick="checkCourierBalance({{ $c->id }})">
                                <i class="fas fa-sync-alt me-1"></i>Refresh
                            </button>
                        </div>

                        <form action="{{ route('admin.system.setting.courier.update', $c->id) }}" method="POST" enctype="multipart/form-data" id="courierForm_{{ $c->id }}">
                            @csrf
                            <!-- Hidden inputs for Global Media Chooser -->
                            <input type="hidden" name="courier_logo_media" id="courierLogoMediaInput_{{ $c->id }}" value="">
                            <input type="hidden" name="remove_courier_logo" id="removeCourierLogoFlag_{{ $c->id }}" value="0">

                            <!-- Global Media Chooser for Courier Logo -->
                            <div class="mb-3">
                                <label class="form-label">Courier Branding Logo (Global Media Chooser)</label>
                                <div class="courier-media-chooser-surface">
                                    <div class="media-chooser-actions d-flex align-items-center gap-2 mb-2 flex-wrap">
                                        <button type="button" class="btn btn-primary btn-compact shadow-xs" onclick="chooseCourierLogoMedia({{ $c->id }})">
                                            <i class="fas fa-photo-video me-1"></i>
                                            <span>Choose from Media Library</span>
                                        </button>
                                        <label class="btn btn-outline-secondary btn-compact shadow-xs mb-0" style="cursor: pointer;">
                                            <i class="fas fa-upload me-1"></i>
                                            <span>Upload Local File</span>
                                            <input type="file" name="courier_logo_file" id="courierLogoFileInput_{{ $c->id }}" class="d-none" accept="image/*" onchange="previewCourierLocalLogo(this, {{ $c->id }})">
                                        </label>
                                    </div>

                                    <div id="courierLogoPreviewWrap_{{ $c->id }}" class="d-flex align-items-center gap-2 bg-white border p-2" style="border-radius: 5px;">
                                        <div class="courier-logo-preview-box">
                                            <img id="courierLogoPreviewImg_{{ $c->id }}" src="{{ $logoUrl }}" onerror="this.src='https://placehold.co/120x60?text=Logo';">
                                        </div>
                                        <div class="text-truncate">
                                            <span class="small text-success fw-bold d-block" style="font-size: 11.5px;"><i class="fas fa-check-circle me-1"></i>Active Brand Logo</span>
                                            <small class="text-muted text-truncate d-block font-monospace" id="courierLogoPathText_{{ $c->id }}" style="font-size: 10px; max-width: 220px;">{{ $logoPath ?: 'Default' }}</small>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger btn-compact py-1 px-2 ms-auto" style="font-size: 10.5px !important;" onclick="clearCourierLogo({{ $c->id }})" title="Remove logo">
                                            <i class="fas fa-trash-alt me-1"></i>Clear
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- API Key / Client ID -->
                            <div class="mb-3">
                                <label class="form-label" for="api_key_{{ $c->id }}">
                                    <span>API Key / Client ID</span>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="api_key" id="api_key_{{ $c->id }}" class="form-control font-monospace" value="{{ $c->api_key }}" placeholder="Enter API Key / Client ID" required>
                            </div>

                            <!-- Secret Key -->
                            <div class="mb-3">
                                <label class="form-label" for="secret_key_{{ $c->id }}">
                                    <span>Secret Key</span>
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <input type="password" name="secret_key" id="secret_key_{{ $c->id }}" class="form-control font-monospace" value="{{ $c->secret_key }}" placeholder="Enter Secret Key" required>
                                    <button class="btn btn-light border" type="button" onclick="togglePasswordVisibility('secret_key_{{ $c->id }}', this)">
                                        <i class="fas fa-eye text-muted" style="font-size: 11px;"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- API Base URL -->
                            <div class="mb-3">
                                <label class="form-label" for="api_url_{{ $c->id }}">API Base URL</label>
                                <input type="text" name="api_url" id="api_url_{{ $c->id }}" class="form-control font-monospace" value="{{ $c->api_url }}" placeholder="https://portal.steadfast.com.bd/api/v1">
                            </div>

                            <!-- Enable Switch -->
                            <div class="form-check form-switch p-2 px-3 bg-light border d-flex align-items-center justify-content-between mb-3" style="border-radius: 5px;">
                                <label class="form-check-label fw-bold small text-dark mb-0 ms-1" for="active_{{ $c->id }}" style="font-size: 12px; cursor: pointer;">Enable Automated Gateway</label>
                                <input class="form-check-input m-0" type="checkbox" name="is_active" id="active_{{ $c->id }}" value="1" {{ $c->is_active ? 'checked' : '' }} style="width: 38px; height: 19px; cursor: pointer;">
                            </div>

                            <!-- Webhook URL Info for Steadfast -->
                            @if($isSteadfast)
                                <div class="p-2 px-3 bg-light rounded-3 border mb-3" style="border-radius: 5px !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="text-dark d-block" style="font-size: 11px;"><i class="fas fa-link me-1 text-primary"></i> Webhook Callback URL:</strong>
                                        <button type="button" class="btn btn-link text-primary text-decoration-none p-0 fw-bold" style="font-size: 10.5px;" onclick="copyWebhookToClipboard('{{ url('/webhook/courier/steadfast') }}')">
                                            <i class="fas fa-copy me-1"></i>Copy
                                        </button>
                                    </div>
                                    <span class="text-muted font-monospace user-select-all d-block text-truncate" style="font-size: 10.5px;">{{ url('/webhook/courier/steadfast') }}</span>
                                </div>
                            @endif

                            <div class="courier-card-footer d-flex justify-content-end align-items-center pt-2 border-top">
                                <button type="submit" class="btn btn-primary btn-compact px-4 py-2 shadow-xs w-100 w-sm-auto">
                                    <i class="fas fa-save me-1"></i>
                                    <span>Save {{ $c->courier_name }} Settings</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal: Notice Dialog (Replaces native browser alert()) -->
<div class="modal fade" id="courierNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 44px; height: 44px;">
                    <i class="fas fa-circle-info fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="courierNoticeTitle" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="courierNoticeMsg" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
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