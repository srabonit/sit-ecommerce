@extends('layouts.admin')

@section('title', 'Featured Products Settings - ' . config('app.name'))

@section('admin_content')
<style>
    /* =====================================================
       FEATURED PRODUCTS SETTINGS
       Kaiadmin Lite - Classic White Design
    ===================================================== */

    #featured-settings-root {
        --featured-primary: var(--bs-primary, #1572e8);
        --featured-success: var(--bs-success, #31ce36);
        --featured-danger: var(--bs-danger, #f25961);
        --featured-warning: var(--bs-warning, #ffad46);
        --featured-info: var(--bs-info, #48abf7);

        --featured-bg: #f8f9fa;
        --featured-white: #ffffff;
        --featured-border: #ebedf2;
        --featured-heading: #2a2f5b;
        --featured-text: #495057;
        --featured-muted: #8d9498;

        color: var(--featured-text);
        font-size: 13px;
        min-width: 0;
    }

    /* Global Radius */

    #featured-settings-root .btn,
    #featured-settings-root button,
    #featured-settings-root .form-control,
    #featured-settings-root .form-select,
    #featured-settings-root .badge,
    #featured-settings-root .card,
    #featured-settings-root .settings-main-card,
    #featured-settings-root .settings-panel-box,
    #featured-settings-root .status-kpi-pill,
    #featured-settings-root .collapse-toggle-btn,
    .featured-modal-scope .modal-content,
    .featured-modal-scope .btn,
    .featured-modal-scope .badge {
        border-radius: 5px !important;
    }

    /* Typography */

    #featured-settings-root h5,
    #featured-settings-root h6 {
        color: var(--featured-heading);
        letter-spacing: -0.2px;
    }

    #featured-settings-root .font-monospace,
    #featured-settings-root .bn-digit {
        font-weight: 700;
    }

    /* Page Header */

    #featured-settings-root .header-top-wrap {
        min-width: 0;
    }

    #featured-settings-root .page-title {
        font-size: clamp(17px, 2vw, 21px);
        font-weight: 700;
        color: var(--featured-heading);
        line-height: 1.35;
    }

    #featured-settings-root .page-description {
        font-size: 12px;
        line-height: 1.6;
        color: var(--featured-muted);
    }

    #featured-settings-root .header-status {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.3px;
        padding: 5px 9px;
    }

    /* Compact Buttons */

    #featured-settings-root .btn-compact {
        min-height: 34px;
        padding: 6px 13px;
        font-size: 11.5px;
        line-height: 1.3;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 3px;
        white-space: nowrap;
        transition: all 0.2s ease;
    }

    #featured-settings-root .btn-compact:hover {
        transform: translateY(-1px);
    }

    #featured-settings-root .top-action-btns {
        flex-shrink: 0;
    }

    /* Main Settings Card */

    #featured-settings-root .settings-main-card {
        background: var(--featured-white);
        border: 1px solid var(--featured-border);
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.035);
        margin-bottom: 16px;
        overflow: hidden;
    }

    #featured-settings-root .settings-main-card > .card-body {
        min-width: 0;
    }

    /* Overview KPI Cards */

    #featured-settings-root .status-kpi-pill {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-height: 76px;
        height: 100%;
        padding: 13px 15px;
        background: var(--featured-white);
        border: 1px solid var(--featured-border);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.025);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    #featured-settings-root .status-kpi-pill:hover {
        border-color: rgba(21, 114, 232, 0.25);
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.045);
    }

    #featured-settings-root .kpi-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--featured-muted);
        margin-bottom: 4px;
    }

    #featured-settings-root .kpi-value {
        font-size: 14px;
        font-weight: 700;
        color: var(--featured-heading);
        line-height: 1.4;
    }

    #featured-settings-root .kpi-icon {
        width: 36px;
        height: 36px;
        min-width: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        font-size: 16px;
        background: var(--featured-bg);
    }

    /* Settings Panels */

    #featured-settings-root .settings-panel-box {
        background: var(--featured-white);
        border: 1px solid var(--featured-border);
        padding: 16px;
        margin-bottom: 15px;
        transition: border-color 0.2s ease;
    }

    #featured-settings-root .settings-panel-box:last-child {
        margin-bottom: 0;
    }

    #featured-settings-root .panel-heading {
        min-width: 0;
        padding-bottom: 11px;
        border-bottom: 1px solid var(--featured-border);
    }

    #featured-settings-root .panel-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--featured-heading);
        line-height: 1.4;
    }

    #featured-settings-root .panel-icon {
        width: 29px;
        height: 29px;
        min-width: 29px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(21, 114, 232, 0.08);
        color: var(--featured-primary);
        border-radius: 5px;
        font-size: 13px;
    }

    /* Collapse Button */

    #featured-settings-root .collapse-toggle-btn {
        width: 29px;
        height: 29px;
        min-width: 29px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--featured-white);
        border: 1px solid var(--featured-border);
        color: var(--featured-text);
        transition: all 0.2s ease;
    }

    #featured-settings-root .collapse-toggle-btn:hover {
        background: var(--featured-primary);
        border-color: var(--featured-primary);
        color: #fff;
    }

    #featured-settings-root .collapse-icon {
        font-size: 10px;
        transition: transform 0.25s ease;
    }

    #featured-settings-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* Switch Setting Cards */

    #featured-settings-root .setting-option-card {
        min-height: 85px;
        padding: 14px 15px;
        background: var(--featured-white);
        border: 1px solid var(--featured-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        height: 100%;
        transition: border-color 0.2s ease, background 0.2s ease;
    }

    #featured-settings-root .setting-option-card:hover {
        border-color: rgba(21, 114, 232, 0.3);
        background: #fcfdff;
    }

    #featured-settings-root .setting-option-card .form-check-label {
        font-size: 12px;
        font-weight: 700;
        line-height: 1.45;
        color: var(--featured-heading);
        cursor: pointer;
        user-select: none;
    }

    #featured-settings-root .setting-description {
        display: block;
        margin-top: 4px;
        font-size: 11px;
        line-height: 1.5;
        color: var(--featured-muted);
    }

    /* Form Labels */

    #featured-settings-root .form-label {
        display: block;
        margin-bottom: 6px;
        color: var(--featured-text);
        font-size: 12px;
        font-weight: 700;
    }

    /* Form Controls */

    #featured-settings-root .form-control,
    #featured-settings-root .form-select {
        width: 100%;
        min-height: 38px;
        padding: 7px 11px;
        font-size: 12.5px;
        font-weight: 500;
        color: #495057;
        background-color: #fff;
        border: 1px solid #e6e9ef;
        box-shadow: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    #featured-settings-root .form-control:hover,
    #featured-settings-root .form-select:hover {
        border-color: #cbd3df;
    }

    #featured-settings-root .form-control:focus,
    #featured-settings-root .form-select:focus {
        border-color: var(--featured-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, 0.10);
        outline: none;
    }

    #featured-settings-root .form-control::placeholder {
        color: #b1b8c1;
        opacity: 1;
    }

    #featured-settings-root input[type="number"] {
        font-variant-numeric: tabular-nums;
    }

    /* Toggle Switch */

    #featured-settings-root .form-switch {
        flex-shrink: 0;
    }

    #featured-settings-root .form-switch .form-check-input {
        width: 39px;
        height: 21px;
        margin-top: 0;
        cursor: pointer;
        border-color: #d5d9e0;
        background-color: #e9ecef;
        box-shadow: none;
        transition: background-color 0.2s ease, border-color 0.2s ease;
    }

    #featured-settings-root .form-switch .form-check-input:checked {
        background-color: var(--featured-primary);
        border-color: var(--featured-primary);
    }

    #featured-settings-root .form-switch .form-check-input:focus {
        box-shadow: 0 0 0 3px rgba(21, 114, 232, 0.12);
        border-color: var(--featured-primary);
    }

    /* Helper Text */

    #featured-settings-root .form-helper {
        display: block;
        margin-top: 6px;
        color: var(--featured-muted);
        font-size: 11px;
        line-height: 1.5;
    }

    /* Submit Area */

    #featured-settings-root .submit-action-bar {
        padding-top: 16px;
        margin-top: 16px;
        border-top: 1px solid var(--featured-border);
    }

    #featured-settings-root .save-settings-btn {
        min-height: 38px;
        padding: 8px 23px;
        font-size: 12px;
        font-weight: 700;
    }

    /* Modal */

    .featured-modal-scope .modal-content {
        border: 1px solid var(--featured-border, #ebedf2);
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12);
    }

    .featured-modal-scope .modal-body {
        padding: 12px;
    }

    .featured-modal-scope .modal-title {
        font-size: 14px;
        font-weight: 700;
    }

    .featured-modal-scope .notice-message {
        font-size: 12px;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    /* Tablet */

    @media (max-width: 991.98px) {
        #featured-settings-root .settings-main-card > .card-body {
            padding: 20px !important;
        }

        #featured-settings-root .settings-panel-box {
            padding: 14px;
        }
    }

    /* Mobile */

    @media (max-width: 767.98px) {
        #featured-settings-root {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #featured-settings-root .header-top-wrap {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 12px !important;
        }

        #featured-settings-root .page-title {
            font-size: 18px;
        }

        #featured-settings-root .page-description {
            font-size: 11.5px;
        }

        #featured-settings-root .top-action-btns {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
            gap: 8px;
        }

        #featured-settings-root .top-action-btns .btn {
            width: 100%;
            min-width: 0;
            padding-left: 7px;
            padding-right: 7px;
            font-size: 10.5px;
        }

        #featured-settings-root .status-kpi-pill {
            min-height: 68px;
            padding: 11px 12px;
        }

        #featured-settings-root .settings-main-card > .card-body {
            padding: 13px !important;
        }

        #featured-settings-root .settings-panel-box {
            padding: 12px;
            margin-bottom: 12px;
        }

        #featured-settings-root .panel-title {
            font-size: 12px;
        }

        #featured-settings-root .setting-option-card {
            min-height: 78px;
            padding: 12px;
        }

        #featured-settings-root .submit-action-bar {
            padding-top: 13px;
        }

        #featured-settings-root .save-settings-btn {
            width: 100%;
        }
    }

    /* Small Mobile */

    @media (max-width: 380px) {
        #featured-settings-root .header-status {
            font-size: 9px;
            padding: 4px 7px;
        }

        #featured-settings-root .top-action-btns .btn {
            font-size: 10px;
        }

        #featured-settings-root .kpi-value {
            font-size: 12px;
        }

        #featured-settings-root .kpi-icon {
            width: 32px;
            height: 32px;
            min-width: 32px;
            font-size: 14px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="featured-settings-root">

    <!-- 1. Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4 header-top-wrap">

        <div class="min-w-0">

            <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                <span class="badge {{ $showFeatured ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} header-status">
                    {{ $showFeatured ? 'Live on Store' : 'Hidden' }}
                </span>

            </div>

            <h5 class="page-title mb-1 d-flex align-items-center gap-2">
                <i class="fas fa-fire text-danger"></i>
                <span>Featured Products</span>
            </h5>

            <small class="page-description d-block">
                Manage Featured Products on the homepage.
            </small>

        </div>

        <div class="top-action-btns d-flex gap-2">

            <a href="{{ route('admin.product.index') }}"
               class="btn btn-light border btn-compact shadow-xs text-secondary">

                <i class="fas fa-boxes text-primary me-1"></i>
                <span>Manage Products</span>

            </a>

            <a href="{{ url('/') }}"
               target="_blank"
               class="btn btn-outline-primary btn-compact shadow-xs">

                <i class="fas fa-external-link-alt me-1"></i>
                <span>View Storefront</span>

            </a>

        </div>

    </div>

    <!-- 2. Overview Cards -->
    <div class="row g-2 g-md-3 mb-3">

        <!-- Visibility -->
        <div class="col-12 col-md-4">

            <div class="status-kpi-pill">

                <div class="min-w-0">

                    <small class="kpi-label d-block">
                        Section Visibility
                    </small>

                    <strong class="kpi-value d-block">
                        {{ $showFeatured ? 'Enabled' : 'Disabled' }}
                    </strong>

                </div>

                <div class="kpi-icon text-{{ $showFeatured ? 'success' : 'secondary' }}">
                    <i class="fas fa-toggle-{{ $showFeatured ? 'on' : 'off' }}"></i>
                </div>

            </div>

        </div>

        <!-- Pagination -->
        <div class="col-12 col-md-4">

            <div class="status-kpi-pill">

                <div class="min-w-0">

                    <small class="kpi-label d-block">
                        Pagination
                    </small>

                    <strong class="kpi-value d-block">
                        {{ $enablePagination ? 'Enabled' : 'Disabled' }}
                    </strong>

                </div>

                <div class="kpi-icon text-primary">
                    <i class="fas fa-list-ol"></i>
                </div>

            </div>

        </div>

        <!-- Product Count -->
        <div class="col-12 col-md-4">

            <div class="status-kpi-pill">

                <div class="min-w-0">

                    <small class="kpi-label d-block">
                        Display Quantity
                    </small>

                    <strong class="kpi-value d-block font-monospace bn-digit">
                        {{ $featuredCount }} Products
                    </strong>

                </div>

                <div class="kpi-icon text-warning">
                    <i class="fas fa-boxes-stacked"></i>
                </div>

            </div>

        </div>

    </div>

    <!-- 3. Main Settings Card -->
    <div class="card settings-main-card">

        <div class="card-body p-3 p-md-4">

            <form action="{{ route('admin.theme.setting.featured_products.update') }}"
                  method="POST"
                  id="featuredSettingsForm">

                @csrf

                <!-- Hidden Backend Parameters -->
                <input type="hidden" name="featured_grid_pc" value="{{ $gridPc ?? 4 }}">
                <input type="hidden" name="featured_section_banner" value="{{ $featuredBanner ?? '' }}">

                <!-- Panel 1: Visibility & Behavior -->
                <div class="settings-panel-box">

                    <div class="d-flex justify-content-between align-items-center panel-heading">

                        <div class="d-flex align-items-center gap-2 min-w-0">

                            <span class="panel-icon">
                                <i class="fas fa-sliders"></i>
                            </span>

                            <h6 class="panel-title mb-0">
                              Visibility
                            </h6>

                        </div>

                        <button type="button"
                                class="collapse-toggle-btn shadow-xs"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseVisibilityRules"
                                aria-expanded="true"
                                aria-controls="collapseVisibilityRules"
                                title="Toggle Section View">

                            <i class="fas fa-chevron-down collapse-icon"></i>

                        </button>

                    </div>

                    <div class="collapse show" id="collapseVisibilityRules">

                        <div class="row g-3 pt-3">

                            <!-- Switch 1: Featured Section -->
                            <div class="col-12 col-md-6">

                                <div class="setting-option-card">

                                    <div class="pe-2 min-w-0">

                                        <label class="form-check-label d-block"
                                               for="featSecSwitch">

                                            Homepage Featured Section

                                        </label>

                                        <small class="setting-description">
                                            Show featured products on your storefront.
                                        </small>

                                    </div>

                                    <div class="form-check form-switch m-0 ps-0">

                                        <input class="form-check-input ms-0"
                                               type="checkbox"
                                               name="show_featured_products_section"
                                               value="1"
                                               id="featSecSwitch"
                                               {{ $showFeatured ? 'checked' : '' }}>

                                    </div>

                                </div>

                            </div>

                            <!-- Switch 2: Pagination -->
                            <div class="col-12 col-md-6">

                                <div class="setting-option-card">

                                    <div class="pe-2 min-w-0">

                                        <label class="form-check-label d-block"
                                               for="featPagSwitch">

                                            Product Pagination

                                        </label>

                                        <small class="setting-description">
                                            Show navigation below featured products.
                                        </small>

                                    </div>

                                    <div class="form-check form-switch m-0 ps-0">

                                        <input class="form-check-input ms-0"
                                               type="checkbox"
                                               name="featured_enable_pagination"
                                               value="1"
                                               id="featPagSwitch"
                                               {{ $enablePagination ? 'checked' : '' }}>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- Panel 2: Product Display Limits -->
                <div class="settings-panel-box mb-3">

                    <div class="d-flex justify-content-between align-items-center panel-heading">

                        <div class="d-flex align-items-center gap-2 min-w-0">

                            <span class="panel-icon">
                                <i class="fas fa-boxes-stacked"></i>
                            </span>

                            <h6 class="panel-title mb-0">
                                Product Display Limits
                            </h6>

                        </div>

                        <button type="button"
                                class="collapse-toggle-btn shadow-xs"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseLimitRules"
                                aria-expanded="true"
                                aria-controls="collapseLimitRules"
                                title="Toggle Section View">

                            <i class="fas fa-chevron-down collapse-icon"></i>

                        </button>

                    </div>

                    <div class="collapse show" id="collapseLimitRules">

                        <div class="pt-3">

                            <label class="form-label">
                                Products Per Page
                                <span class="text-danger">*</span>
                            </label>

                            <input type="number"
                                   name="featured_products_count"
                                   id="featured_products_count_input"
                                   class="form-control font-monospace bn-digit"
                                   value="{{ $featuredCount }}"
                                   min="1"
                                   max="100"
                                   required>

                            <small class="form-helper">
                                Allowed range: 1–100. Suggested: 8, 10, 12, 16 or 24.
                            </small>

                        </div>

                    </div>

                </div>

                <!-- Submit Actions -->
                <div class="d-flex justify-content-end gap-2 submit-action-bar">

                    <button type="submit"
                            class="btn btn-primary btn-compact save-settings-btn shadow-xs">

                        <i class="fas fa-check-circle me-1"></i>
                        Save Settings

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<!-- Custom Notice Modal -->
<div class="modal fade featured-modal-scope"
     id="featuredNoticeModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 108500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content border-0 shadow-lg text-center p-3">

            <div class="modal-body p-1">

                <div class="mb-3" id="featuredNoticeIconWrap">
                    <i class="fas fa-circle-check text-success fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-2 modal-title"
                    id="featuredNoticeTitle">

                    Notification

                </h6>

                <p class="text-muted small mb-3 notice-message"
                   id="featuredNoticeMessage"></p>

                <button type="button"
                        class="btn btn-primary btn-compact px-4"
                        data-bs-dismiss="modal">

                    OK

                </button>

            </div>

        </div>

    </div>

</div>

<script>
    // Custom Enterprise Notice Modal Display
    function showFeaturedModalNotice(message, title = 'Notice', type = 'info') {
        const titleEl = document.getElementById('featuredNoticeTitle');
        const msgEl   = document.getElementById('featuredNoticeMessage');
        const iconEl  = document.getElementById('featuredNoticeIconWrap');

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

        const modalEl = document.getElementById('featuredNoticeModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    // Form Client-side Validation Guard
    document.getElementById('featuredSettingsForm').addEventListener('submit', function(e) {
        const countInput = document.getElementById('featured_products_count_input');
        const val = parseInt(countInput.value);

        if (isNaN(val) || val < 1 || val > 100) {
            e.preventDefault();
            showFeaturedModalNotice('Please enter a valid product count between 1 and 100.', 'Invalid Quantity', 'warning');
            return false;
        }
    });
</script>
@endsection