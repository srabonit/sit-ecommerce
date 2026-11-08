@extends('layouts.admin')

@section('title', 'Cart & Navigation Settings - ' . config('app.name'))

@section('admin_content')
<style>
    /* =========================================
       CART & NAVIGATION SETTINGS
    ========================================= */

    #cartFeaturesRoot {
        --cart-primary: #2563eb;
        --cart-primary-soft: #eff6ff;
        --cart-success: #16a34a;
        --cart-border: #e2e8f0;
        --cart-border-dark: #cbd5e1;
        --cart-muted: #64748b;
        --cart-heading: #0f172a;
        --cart-surface: #ffffff;
        --cart-soft-bg: #f8fafc;
    }

    #cartFeaturesRoot .btn,
    #cartFeaturesRoot button,
    #cartFeaturesRoot .form-control,
    #cartFeaturesRoot .form-select,
    #cartFeaturesRoot .badge,
    #cartFeaturesRoot .alert,
    #cartFeaturesRoot .card,
    #cartFeaturesRoot .settings-panel-card,
    #cartFeaturesRoot .feature-switch-row,
    #cartFeaturesRoot .device-toggle-box,
    #cartFeaturesRoot .sticky-item-row,
    #cartFeaturesRoot .collapse-toggle-btn,
    .cart-modal-scope .modal-content,
    .cart-modal-scope .btn,
    .cart-modal-scope .badge {
        border-radius: 5px !important;
    }

    #cartFeaturesRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #cartFeaturesRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    #cartFeaturesRoot .btn-compact {
        min-height: 32px;
        padding: 6px 13px !important;
        font-size: 11.5px !important;
        font-weight: 600 !important;
        line-height: 1.3;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        white-space: nowrap;
        transition: all .15s ease;
    }

    #cartFeaturesRoot .btn-primary {
        background-color: var(--cart-primary);
        border-color: var(--cart-primary);
    }

    #cartFeaturesRoot .btn-primary:hover {
        background-color: #1d4ed8;
        border-color: #1d4ed8;
    }

    #cartFeaturesRoot .item-del-sq-btn {
        width: 30px !important;
        height: 32px !important;
        min-width: 30px;
        padding: 0 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        flex-shrink: 0;
    }

    /* Panel Cards */

    #cartFeaturesRoot .settings-panel-card {
        background: var(--cart-surface);
        border: 1px solid var(--cart-border);
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
        margin-bottom: 14px;
        overflow: hidden;
    }

    #cartFeaturesRoot .settings-panel-header {
        min-height: 45px;
        padding: 10px 14px;
        background: var(--cart-soft-bg);
        border-bottom: 1px solid var(--cart-border);
    }

    #cartFeaturesRoot .settings-panel-header h6 {
        font-size: 12.5px !important;
        letter-spacing: -.1px;
    }

    #cartFeaturesRoot .section-icon {
        width: 27px;
        height: 27px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eff6ff;
        color: var(--cart-primary);
        border-radius: 5px;
        font-size: 12px;
        flex-shrink: 0;
    }

    #cartFeaturesRoot .section-tag {
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .45px;
        padding: 4px 7px;
    }

    /* Feature Rows */

    #cartFeaturesRoot .feature-switch-row {
        background: #fff;
        border: 1px solid var(--cart-border);
        padding: 11px 13px;
        min-height: 62px;
        transition: border-color .15s ease, background .15s ease;
    }

    #cartFeaturesRoot .feature-switch-row:hover {
        border-color: #cbd5e1;
        background: #fbfdff;
    }

    #cartFeaturesRoot .feature-title {
        display: block;
        margin-bottom: 2px;
        color: var(--cart-heading);
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }

    #cartFeaturesRoot .feature-description {
        display: block;
        color: var(--cart-muted);
        font-size: 10.5px;
        line-height: 1.4;
    }

    /* Device Settings */

    #cartFeaturesRoot .device-toggle-box {
        min-height: 39px;
        background: #fff;
        border: 1px solid var(--cart-border-dark);
        padding: 7px 11px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        transition: all .15s ease;
    }

    #cartFeaturesRoot .device-toggle-box:has(input:checked) {
        background: #f5f9ff;
        border-color: #93c5fd;
    }

    #cartFeaturesRoot .device-toggle-box label {
        font-size: 11.5px;
        font-weight: 600;
        color: #334155;
        cursor: pointer;
    }

    /* Navigation Items */

    #cartFeaturesRoot .sticky-item-row {
        background: #fff;
        border: 1px solid var(--cart-border);
        padding: 12px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #cartFeaturesRoot .sticky-item-row:hover {
        border-color: #bfdbfe;
        box-shadow: 0 1px 3px rgba(37, 99, 235, .05);
    }

    #cartFeaturesRoot .sticky-items-empty {
        padding: 22px 12px;
        text-align: center;
        background: var(--cart-soft-bg);
        border: 1px dashed var(--cart-border-dark);
        border-radius: 5px;
        color: var(--cart-muted);
        font-size: 11.5px;
    }

    /* Form Controls */

    #cartFeaturesRoot .form-label {
        display: block;
        margin-bottom: 5px;
        color: #334155;
        font-size: 11px;
        font-weight: 700;
    }

    #cartFeaturesRoot .form-control,
    #cartFeaturesRoot .form-select {
        height: 34px;
        padding: 6px 10px;
        color: #0f172a;
        background-color: #fff;
        border: 1px solid var(--cart-border-dark);
        box-shadow: none;
        font-size: 11.5px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #cartFeaturesRoot .form-control::placeholder {
        color: #94a3b8;
        opacity: 1;
    }

    #cartFeaturesRoot .form-control:focus,
    #cartFeaturesRoot .form-select:focus {
        border-color: var(--cart-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
        outline: none;
    }

    #cartFeaturesRoot .form-control-color {
        height: 36px !important;
        padding: 3px 5px !important;
        cursor: pointer;
    }

    #cartFeaturesRoot .font-monospace {
        font-size: 11px !important;
    }

    /* Switches */

    #cartFeaturesRoot .form-switch .form-check-input {
        width: 35px;
        height: 18px;
        margin-top: 0;
        cursor: pointer;
        border-color: #cbd5e1;
        box-shadow: none;
        transition: background-color .15s ease, border-color .15s ease;
    }

    #cartFeaturesRoot .form-switch .form-check-input:checked {
        background-color: var(--cart-primary);
        border-color: var(--cart-primary);
    }

    #cartFeaturesRoot .form-switch .form-check-input:focus {
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
    }

    /* Collapse Button */

    #cartFeaturesRoot .collapse-toggle-btn {
        width: 27px;
        height: 27px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid var(--cart-border-dark);
        color: #475569;
        transition: all .2s ease;
    }

    #cartFeaturesRoot .collapse-toggle-btn:hover {
        background: var(--cart-primary);
        border-color: var(--cart-primary);
        color: #fff;
    }

    #cartFeaturesRoot .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    #cartFeaturesRoot .collapse-icon {
        font-size: 10px;
        transition: transform .25s ease;
    }

    /* Guide */

    #cartFeaturesRoot .guide-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 13px;
    }

    #cartFeaturesRoot .guide-list li {
        position: relative;
        padding-left: 22px;
        color: var(--cart-muted);
        font-size: 11.5px;
        line-height: 1.55;
    }

    #cartFeaturesRoot .guide-list li i {
        position: absolute;
        left: 0;
        top: 3px;
        color: var(--cart-primary);
        font-size: 11px;
    }

    #cartFeaturesRoot .guide-list strong {
        display: block;
        margin-bottom: 2px;
        color: #1e293b;
        font-size: 11.5px;
    }

    /* Modals */

    .cart-modal-scope .modal-content {
        border: 1px solid var(--cart-border);
        box-shadow: 0 15px 45px rgba(15, 23, 42, .15);
    }

    .cart-modal-scope .modal-body {
        padding: 8px 4px;
    }

    .cart-modal-scope .modal-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #eff6ff;
        color: var(--cart-primary);
        font-size: 20px;
    }

    /* Layout */

    @media (min-width: 992px) {
        #cartFeaturesRoot .settings-sidebar {
            position: sticky;
            top: 20px;
        }
    }

    @media (max-width: 767.98px) {
        #cartFeaturesRoot {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #cartFeaturesRoot .top-header-bar {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 12px !important;
        }

        #cartFeaturesRoot .top-save-btn-wrap .btn {
            width: 100%;
            min-height: 37px;
        }

        #cartFeaturesRoot .settings-panel-header {
            padding: 10px 11px;
        }

        #cartFeaturesRoot .settings-panel-body {
            padding: 12px !important;
        }

        #cartFeaturesRoot .section-tag {
            display: none;
        }

        #cartFeaturesRoot .sticky-item-row {
            padding: 10px;
        }
    }

    @media (max-width: 420px) {
        #cartFeaturesRoot .form-control,
        #cartFeaturesRoot .form-select {
            font-size: 11px;
        }

        #cartFeaturesRoot .feature-switch-row {
            padding: 10px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="cartFeaturesRoot">

    <form action="{{ route('admin.theme.setting.cart_features.update') }}" method="POST" id="cartFeaturesForm">
        @csrf

        {{-- Page Header --}}
        <div class="d-flex top-header-bar justify-content-between align-items-sm-center gap-3 mb-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="section-icon">
                        <i class="fas fa-layer-group"></i>
                    </span>
                    <h5 class="fw-bold mb-0 text-dark" style="font-size: 17px;">
                      Cart, Sticky Header, Footer & BT
                    </h5>
                </div>

                <small class="text-muted d-block" style="font-size: 11.5px; line-height: 1.5;">
                    Manage Cart, Sticky Header, Footer & Back to Top
                </small>
            </div>

            <div class="top-save-btn-wrap">
                <button type="submit" class="btn btn-primary btn-compact shadow-sm px-4" style="min-height: 36px;">
                    <i class="fas fa-save me-1"></i>
                    Save Changes
                </button>
            </div>
        </div>

        {{-- Success Message --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3 py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-circle-check text-success"></i>
                    <span class="fw-semibold text-dark" style="font-size: 12px;">
                        {{ session('success') }}
                    </span>
                </div>

                <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close" style="font-size: 9px;"></button>
            </div>
        @endif

        <div class="row g-3">

            {{-- =========================================
                 MAIN SETTINGS COLUMN
            ========================================== --}}
            <div class="col-12 col-lg-8">

                {{-- Section 1: Header Features --}}
                <div class="settings-panel-card">

                    <div class="settings-panel-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="section-icon">
                                <i class="fas fa-sliders"></i>
                            </span>

                            <h6 class="fw-bold text-dark mb-0">Header Features</h6>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle section-tag">
                                GLOBAL UI
                            </span>

                            <button type="button"
                                class="collapse-toggle-btn shadow-sm"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseHeaderFeatures"
                                aria-expanded="true"
                                aria-controls="collapseHeaderFeatures"
                                title="Toggle Section">
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="collapse show" id="collapseHeaderFeatures">
                        <div class="settings-panel-body p-3 bg-light bg-opacity-25">
                            <div class="row g-2">

                                {{-- Sticky Header --}}
                                <div class="col-12 col-md-6">
                                    <div class="feature-switch-row d-flex justify-content-between align-items-center gap-2 h-100">
                                        <div class="pe-2">
                                            <label class="feature-title" for="sw_sticky_header">Sticky Header</label>
                                            <small class="feature-description">Keeps the header visible while scrolling.</small>
                                        </div>

                                        <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                            <input class="form-check-input ms-0"
                                                type="checkbox"
                                                id="sw_sticky_header"
                                                name="feature_sticky_header"
                                                value="1"
                                                {{ ($settings['feature_sticky_header'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>

                                {{-- Glass Header --}}
                                <div class="col-12 col-md-6">
                                    <div class="feature-switch-row d-flex justify-content-between align-items-center gap-2 h-100">
                                        <div class="pe-2">
                                            <label class="feature-title" for="sw_glass_header">Glass Header Effect</label>
                                            <small class="feature-description">Adds blur and transparency to the header.</small>
                                        </div>

                                        <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                            <input class="form-check-input ms-0"
                                                type="checkbox"
                                                id="sw_glass_header"
                                                name="feature_glassmorphism_header"
                                                value="1"
                                                {{ ($settings['feature_glassmorphism_header'] ?? '0') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>

                                {{-- Cart Drawer --}}
                                <div class="col-12 col-md-6">
                                    <div class="feature-switch-row d-flex justify-content-between align-items-center gap-2 h-100">
                                        <div class="pe-2">
                                            <label class="feature-title" for="sw_float_cart">Cart Drawer</label>
                                            <small class="feature-description">Opens cart items without reloading the page.</small>
                                        </div>

                                        <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                            <input class="form-check-input ms-0"
                                                type="checkbox"
                                                id="sw_float_cart"
                                                name="feature_floating_cart_modal"
                                                value="1"
                                                {{ ($settings['feature_floating_cart_modal'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>

                                {{-- Live Search --}}
                                <div class="col-12 col-md-6">
                                    <div class="feature-switch-row d-flex justify-content-between align-items-center gap-2 h-100">
                                        <div class="pe-2">
                                            <label class="feature-title" for="sw_ajax_search">Live Search</label>
                                            <small class="feature-description">Shows product suggestions while typing.</small>
                                        </div>

                                        <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                            <input class="form-check-input ms-0"
                                                type="checkbox"
                                                id="sw_ajax_search"
                                                name="feature_ajax_search"
                                                value="1"
                                                {{ ($settings['feature_ajax_search'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>

                                {{-- Buy Now --}}
                                <div class="col-12">
                                    <div class="feature-switch-row d-flex justify-content-between align-items-center gap-2">
                                        <div class="pe-2">
                                            <label class="feature-title" for="sw_buy_now">Buy Now Button</label>
                                            <small class="feature-description">Adds a direct checkout button to product cards.</small>
                                        </div>

                                        <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                            <input class="form-check-input ms-0"
                                                type="checkbox"
                                                id="sw_buy_now"
                                                name="feature_buy_now_btn"
                                                value="1"
                                                {{ ($settings['feature_buy_now_btn'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Sticky Bottom Navigation --}}
                <div class="settings-panel-card">

                    <div class="settings-panel-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="section-icon" style="background:#f0fdf4;color:#16a34a;">
                                <i class="fas fa-shoe-prints"></i>
                            </span>

                            <h6 class="fw-bold text-dark mb-0">Sticky Bottom Navigation</h6>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success border border-success-subtle section-tag">
                                APP FOOTER
                            </span>

                            <button type="button"
                                class="collapse-toggle-btn shadow-sm"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseStickyFooter"
                                aria-expanded="true"
                                aria-controls="collapseStickyFooter"
                                title="Toggle Section">
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="collapse show" id="collapseStickyFooter">
                        <div class="settings-panel-body p-3">

                            {{-- Master Switch --}}
                            <div class="feature-switch-row d-flex justify-content-between align-items-center gap-2 mb-3">
                                <div class="pe-2">
                                    <label class="feature-title" for="sw_sticky_footer">Enable Bottom Navigation</label>
                                    <small class="feature-description">Displays a fixed navigation bar across pages.</small>
                                </div>

                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0"
                                        type="checkbox"
                                        id="sw_sticky_footer"
                                        name="feature_sticky_footer"
                                        value="1"
                                        {{ ($settings['feature_sticky_footer'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>

                            {{-- Device Visibility --}}
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-desktop text-primary me-1"></i>
                                    Device Visibility
                                </label>

                                <div class="row g-2">
                                    <div class="col-12 col-sm-4">
                                        <div class="device-toggle-box">
                                            <label for="sfMobile">
                                                <i class="fas fa-mobile-alt text-muted me-1"></i>
                                                Mobile
                                            </label>

                                            <div class="form-check form-switch m-0 ps-0">
                                                <input class="form-check-input ms-0"
                                                    type="checkbox"
                                                    name="sticky_footer_show_mobile"
                                                    id="sfMobile"
                                                    value="1"
                                                    {{ ($settings['sticky_footer_show_mobile'] ?? '1') == '1' ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-4">
                                        <div class="device-toggle-box">
                                            <label for="sfTablet">
                                                <i class="fas fa-tablet-alt text-muted me-1"></i>
                                                Tablet
                                            </label>

                                            <div class="form-check form-switch m-0 ps-0">
                                                <input class="form-check-input ms-0"
                                                    type="checkbox"
                                                    name="sticky_footer_show_tablet"
                                                    id="sfTablet"
                                                    value="1"
                                                    {{ ($settings['sticky_footer_show_tablet'] ?? '1') == '1' ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-4">
                                        <div class="device-toggle-box">
                                            <label for="sfDesktop">
                                                <i class="fas fa-laptop text-muted me-1"></i>
                                                Desktop
                                            </label>

                                            <div class="form-check form-switch m-0 ps-0">
                                                <input class="form-check-input ms-0"
                                                    type="checkbox"
                                                    name="sticky_footer_show_desktop"
                                                    id="sfDesktop"
                                                    value="1"
                                                    {{ ($settings['sticky_footer_show_desktop'] ?? '1') == '1' ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Layout & Behavior --}}
                            <div class="row g-2 pb-3 mb-3 border-bottom">
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Desktop Layout</label>

                                    <select name="sticky_footer_desktop_style" class="form-select">
                                        <option value="boxed_floating" {{ ($settings['sticky_footer_desktop_style'] ?? 'boxed_floating') == 'boxed_floating' ? 'selected' : '' }}>
                                            Floating Bar
                                        </option>
                                        <option value="full_width" {{ ($settings['sticky_footer_desktop_style'] ?? '') == 'full_width' ? 'selected' : '' }}>
                                            Full Width
                                        </option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label">Scroll Behavior</label>

                                    <div class="device-toggle-box h-auto" style="min-height:34px;">
                                        <label for="hideOnScrollCheck">Hide on Scroll Down</label>

                                        <div class="form-check form-switch m-0 ps-0">
                                            <input class="form-check-input ms-0"
                                                type="checkbox"
                                                name="sticky_footer_hide_on_scroll"
                                                id="hideOnScrollCheck"
                                                value="1"
                                                {{ ($settings['sticky_footer_hide_on_scroll'] ?? '0') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label">Glass Effect</label>

                                    <div class="device-toggle-box h-auto" style="min-height:34px;">
                                        <label for="glassFooterCheck">Enable Blur</label>

                                        <div class="form-check form-switch m-0 ps-0">
                                            <input class="form-check-input ms-0"
                                                type="checkbox"
                                                name="sticky_footer_glassmorphism"
                                                id="glassFooterCheck"
                                                value="1"
                                                {{ ($settings['sticky_footer_glassmorphism'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Color Settings --}}
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-palette text-danger me-1"></i>
                                    Color Settings
                                </label>

                                <div class="row g-2 pb-3 border-bottom">
                                    <div class="col-4">
                                        <label class="form-label">Background</label>
                                        <input type="color"
                                            name="sticky_footer_bg"
                                            class="form-control form-control-color w-100"
                                            value="{{ $settings['sticky_footer_bg'] ?? '#ffffff' }}">
                                    </div>

                                    <div class="col-4">
                                        <label class="form-label">Text &amp; Icons</label>
                                        <input type="color"
                                            name="sticky_footer_text_color"
                                            class="form-control form-control-color w-100"
                                            value="{{ $settings['sticky_footer_text_color'] ?? '#64748b' }}">
                                    </div>

                                    <div class="col-4">
                                        <label class="form-label">Active &amp; Hover</label>
                                        <input type="color"
                                            name="sticky_footer_active_color"
                                            class="form-control form-control-color w-100"
                                            value="{{ $settings['sticky_footer_active_color'] ?? '#2563eb' }}">
                                    </div>
                                </div>
                            </div>

                            {{-- Navigation Items --}}
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark" style="font-size:12.5px;">
                                        Navigation Items
                                    </h6>
                                    <small class="text-muted" style="font-size:10.5px;">
                                        Manage shortcuts and actions.
                                    </small>
                                </div>

                                <button type="button"
                                    class="btn btn-primary btn-compact shadow-sm"
                                    onclick="addStickyFooterItem()">
                                    <i class="fas fa-plus"></i>
                                    Add Item
                                </button>
                            </div>

                            <div id="sticky-footer-items-container" class="d-flex flex-column gap-2">

                                @foreach($footerItems as $index => $item)
                                    <div class="sticky-item-row" id="footer-row-{{ $index }}">
                                        <div class="row g-2 align-items-end">

                                            <div class="col-6 col-md-3">
                                                <label class="form-label">Title</label>
                                                <input type="text"
                                                    name="item_title[]"
                                                    class="form-control"
                                                    value="{{ $item['title'] ?? '' }}"
                                                    placeholder="Shop">
                                            </div>

                                            <div class="col-6 col-md-3">
                                                <label class="form-label">Icon Class</label>
                                                <input type="text"
                                                    name="item_icon[]"
                                                    class="form-control font-monospace"
                                                    value="{{ $item['icon'] ?? 'fas fa-circle' }}"
                                                    placeholder="fas fa-store">
                                            </div>

                                            <div class="col-6 col-md-3">
                                                <label class="form-label">Target URL</label>
                                                <input type="text"
                                                    name="item_url[]"
                                                    class="form-control font-monospace"
                                                    value="{{ $item['url'] ?? '#' }}"
                                                    placeholder="/shop">
                                            </div>

                                            <div class="col-6 col-md-3">
                                                <label class="form-label">Action</label>

                                                <div class="d-flex align-items-center gap-1">
                                                    <select name="item_action_type[]" class="form-select flex-grow-1 font-monospace">
                                                        <option value="link" {{ ($item['action_type'] ?? '') == 'link' ? 'selected' : '' }}>Link</option>
                                                        <option value="cart" {{ ($item['action_type'] ?? '') == 'cart' ? 'selected' : '' }}>Cart Drawer</option>
                                                        <option value="search" {{ ($item['action_type'] ?? '') == 'search' ? 'selected' : '' }}>Search Modal</option>
                                                        <option value="wishlist" {{ ($item['action_type'] ?? '') == 'wishlist' ? 'selected' : '' }}>Wishlist</option>
                                                    </select>

                                                    <button type="button"
                                                        class="btn btn-outline-danger item-del-sq-btn"
                                                        onclick="confirmRemoveStickyItem(this)"
                                                        title="Remove Item"
                                                        aria-label="Remove navigation item">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                @endforeach

                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section 3: Sticky Product Bar --}}
                <div class="settings-panel-card">

                    <div class="settings-panel-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="section-icon" style="background:#fff1f2;color:#e11d48;">
                                <i class="fas fa-cart-arrow-down"></i>
                            </span>

                            <h6 class="fw-bold text-dark mb-0">Sticky Product Bar</h6>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle section-tag">
                                PRODUCT
                            </span>

                            <button type="button"
                                class="collapse-toggle-btn shadow-sm"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseProductBar"
                                aria-expanded="true"
                                aria-controls="collapseProductBar"
                                title="Toggle Section">
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="collapse show" id="collapseProductBar">
                        <div class="settings-panel-body p-3">

                            {{-- Master Switch --}}
                            <div class="feature-switch-row d-flex justify-content-between align-items-center gap-2 mb-3">
                                <div class="pe-2">
                                    <label class="feature-title" for="sw_sticky_prod_bar">Enable Sticky Product Bar</label>
                                    <small class="feature-description">Keeps purchase actions visible while scrolling.</small>
                                </div>

                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0"
                                        type="checkbox"
                                        id="sw_sticky_prod_bar"
                                        name="feature_sticky_product_bar"
                                        value="1"
                                        {{ ($settings['feature_sticky_product_bar'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>

                            {{-- Device Visibility --}}
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-desktop text-primary me-1"></i>
                                    Device Visibility
                                </label>

                                <div class="row g-2">
                                    <div class="col-12 col-sm-4">
                                        <div class="device-toggle-box">
                                            <label for="spbMobile">
                                                <i class="fas fa-mobile-alt text-muted me-1"></i>
                                                Mobile
                                            </label>

                                            <div class="form-check form-switch m-0 ps-0">
                                                <input class="form-check-input ms-0"
                                                    type="checkbox"
                                                    name="sticky_product_bar_show_mobile"
                                                    id="spbMobile"
                                                    value="1"
                                                    {{ ($settings['sticky_product_bar_show_mobile'] ?? '1') == '1' ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-4">
                                        <div class="device-toggle-box">
                                            <label for="spbTablet">
                                                <i class="fas fa-tablet-alt text-muted me-1"></i>
                                                Tablet
                                            </label>

                                            <div class="form-check form-switch m-0 ps-0">
                                                <input class="form-check-input ms-0"
                                                    type="checkbox"
                                                    name="sticky_product_bar_show_tablet"
                                                    id="spbTablet"
                                                    value="1"
                                                    {{ ($settings['sticky_product_bar_show_tablet'] ?? '1') == '1' ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-4">
                                        <div class="device-toggle-box">
                                            <label for="spbDesktop">
                                                <i class="fas fa-laptop text-muted me-1"></i>
                                                Desktop
                                            </label>

                                            <div class="form-check form-switch m-0 ps-0">
                                                <input class="form-check-input ms-0"
                                                    type="checkbox"
                                                    name="sticky_product_bar_show_desktop"
                                                    id="spbDesktop"
                                                    value="1"
                                                    {{ ($settings['sticky_product_bar_show_desktop'] ?? '1') == '1' ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Position & Action --}}
                            <div class="row g-2">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Position</label>

                                    <select name="sticky_product_bar_position" class="form-select">
                                        {{-- <option value="top" {{ ($settings['sticky_product_bar_position'] ?? 'bottom') == 'bottom' ? 'selected' : '' }}>Top</option> --}}
                                        <option value="bottom" {{ ($settings['sticky_product_bar_position'] ?? '') == 'bottom' ? 'selected' : '' }}>Bottom</option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Action Button</label>

                                    <div class="device-toggle-box">
                                        <label for="spbBuyNow">Show Buy Now</label>

                                        <div class="form-check form-switch m-0 ps-0">
                                            <input class="form-check-input ms-0"
                                                type="checkbox"
                                                name="sticky_product_bar_show_buynow"
                                                id="spbBuyNow"
                                                value="1"
                                                {{ ($settings['sticky_product_bar_show_buynow'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Section 4: Scroll to Top --}}
                <div class="settings-panel-card">

                    <div class="settings-panel-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="section-icon" style="background:#fffbeb;color:#d97706;">
                                <i class="fas fa-arrow-circle-up"></i>
                            </span>

                            <h6 class="fw-bold text-dark mb-0">Scroll to Top Button</h6>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle section-tag">
                                NAVIGATION
                            </span>

                            <button type="button"
                                class="collapse-toggle-btn shadow-sm"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseScrollTop"
                                aria-expanded="true"
                                aria-controls="collapseScrollTop"
                                title="Toggle Section">
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="collapse show" id="collapseScrollTop">
                        <div class="settings-panel-body p-3">

                            {{-- Master Switch --}}
                            <div class="feature-switch-row d-flex justify-content-between align-items-center gap-2 mb-3">
                                <div class="pe-2">
                                    <label class="feature-title" for="sw_scroll_top">Enable Scroll to Top</label>
                                    <small class="feature-description">Adds a button to return to the top of the page.</small>
                                </div>

                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0"
                                        type="checkbox"
                                        id="sw_scroll_top"
                                        name="feature_scroll_to_top"
                                        value="1"
                                        {{ ($settings['feature_scroll_to_top'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>

                            {{-- Device Visibility --}}
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-desktop text-primary me-1"></i>
                                    Device Visibility
                                </label>

                                <div class="row g-2">
                                    <div class="col-12 col-sm-4">
                                        <div class="device-toggle-box">
                                            <label for="stMobile">
                                                <i class="fas fa-mobile-alt text-muted me-1"></i>
                                                Mobile
                                            </label>

                                            <div class="form-check form-switch m-0 ps-0">
                                                <input class="form-check-input ms-0"
                                                    type="checkbox"
                                                    name="scroll_top_show_mobile"
                                                    id="stMobile"
                                                    value="1"
                                                    {{ ($settings['scroll_top_show_mobile'] ?? '1') == '1' ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-4">
                                        <div class="device-toggle-box">
                                            <label for="stTablet">
                                                <i class="fas fa-tablet-alt text-muted me-1"></i>
                                                Tablet
                                            </label>

                                            <div class="form-check form-switch m-0 ps-0">
                                                <input class="form-check-input ms-0"
                                                    type="checkbox"
                                                    name="scroll_top_show_tablet"
                                                    id="stTablet"
                                                    value="1"
                                                    {{ ($settings['scroll_top_show_tablet'] ?? '1') == '1' ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-4">
                                        <div class="device-toggle-box">
                                            <label for="stDesktop">
                                                <i class="fas fa-laptop text-muted me-1"></i>
                                                Desktop
                                            </label>

                                            <div class="form-check form-switch m-0 ps-0">
                                                <input class="form-check-input ms-0"
                                                    type="checkbox"
                                                    name="scroll_top_show_desktop"
                                                    id="stDesktop"
                                                    value="1"
                                                    {{ ($settings['scroll_top_show_desktop'] ?? '1') == '1' ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Style & Position --}}
                            <div class="row g-2">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Button Style</label>

                                    <select name="scroll_top_style" class="form-select font-monospace">
                                        <option value="classic_rounded" {{ ($settings['scroll_top_style'] ?? 'classic_rounded') == 'classic_rounded' ? 'selected' : '' }}>
                                            Style 1: Classic Circle
                                        </option>
                                        <option value="square_modern" {{ ($settings['scroll_top_style'] ?? '') == 'square_modern' ? 'selected' : '' }}>
                                            Style 2: Modern Square
                                        </option>
                                        <option value="minimal_clean" {{ ($settings['scroll_top_style'] ?? '') == 'minimal_clean' ? 'selected' : '' }}>
                                            Style 3: Minimal Glass
                                        </option>
                                        <option value="pulse_glow" {{ ($settings['scroll_top_style'] ?? '') == 'pulse_glow' ? 'selected' : '' }}>
                                            Style 4: Pulse Glow
                                        </option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Position</label>

                                    <select name="scroll_top_position" class="form-select font-monospace">
                                        <option value="right" {{ ($settings['scroll_top_position'] ?? 'right') == 'right' ? 'selected' : '' }}>
                                            Bottom Right
                                        </option>
                                        <option value="left" {{ ($settings['scroll_top_position'] ?? '') == 'left' ? 'selected' : '' }}>
                                            Bottom Left
                                        </option>
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Bottom Save Button --}}
                <div class="d-flex justify-content-end mb-3">
                    <button type="submit"
                        class="btn btn-primary btn-compact shadow-sm px-4 w-100 w-md-auto"
                        style="min-height:37px;">
                        <i class="fas fa-check-circle me-1"></i>
                        Save Changes
                    </button>
                </div>

            </div>

            {{-- =========================================
                 SIDEBAR GUIDE
            ========================================== --}}
            <div class="col-12 col-lg-4">
                <div class="settings-sidebar">

                    <div class="settings-panel-card">

                        <div class="settings-panel-header d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <span class="section-icon">
                                    <i class="fas fa-info-circle"></i>
                                </span>

                                <h6 class="fw-bold text-dark mb-0">Settings Guide</h6>
                            </div>

                            <button type="button"
                                class="collapse-toggle-btn shadow-sm"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseGuideCard"
                                aria-expanded="true"
                                aria-controls="collapseGuideCard"
                                title="Toggle Guide">
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </button>
                        </div>

                        <div class="collapse show" id="collapseGuideCard">
                            <div class="p-3">

                                <ul class="guide-list">
                                    <li>
                                        <i class="fas fa-cart-shopping"></i>
                                        <strong>Sticky Product Bar</strong>
                                        Keeps purchase actions visible while browsing product pages.
                                    </li>

                                    <li>
                                        <i class="fas fa-mobile-screen-button"></i>
                                        <strong>Device Visibility</strong>
                                        Control visibility separately for mobile, tablet, and desktop.
                                    </li>

                                    <li>
                                        <i class="fas fa-palette"></i>
                                        <strong>Color Customization</strong>
                                        Customize the bottom navigation background, text, and active colors.
                                    </li>

                                    <li>
                                        <i class="fas fa-bolt"></i>
                                        <strong>Live Updates</strong>
                                        Changes apply to the storefront after saving.
                                    </li>
                                </ul>

                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </form>
</div>

{{-- =========================================
     NOTIFICATION MODAL
========================================== --}}
<div class="modal fade cart-modal-scope"
    id="cartFeaturesNoticeModal"
    tabindex="-1"
    aria-hidden="true"
    style="z-index:108500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-3">

            <div class="modal-body">
                <div class="modal-icon" id="cartFeaturesNoticeIconWrap">
                    <i class="fas fa-circle-check text-success"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1"
                    id="cartFeaturesNoticeTitle"
                    style="font-size:14px;">
                    Notification
                </h6>

                <p class="text-muted small mb-3"
                    id="cartFeaturesNoticeMessage"
                    style="font-size:12px;line-height:1.5;">
                </p>

                <button type="button"
                    class="btn btn-primary btn-compact px-4"
                    data-bs-dismiss="modal">
                    OK
                </button>
            </div>

        </div>
    </div>
</div>

{{-- =========================================
     CONFIRMATION MODAL
========================================== --}}
<div class="modal fade cart-modal-scope"
    id="cartFeaturesConfirmModal"
    tabindex="-1"
    aria-hidden="true"
    style="z-index:108600;">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-3">

            <div class="modal-body">
                <div class="modal-icon" style="background:#fff1f2;color:#dc2626;">
                    <i class="fas fa-trash-can"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1"
                    id="cartFeaturesConfirmTitle"
                    style="font-size:14px;">
                    Remove Item?
                </h6>

                <p class="text-muted small mb-3"
                    id="cartFeaturesConfirmMessage"
                    style="font-size:12px;line-height:1.5;">
                    Are you sure you want to remove this item?
                </p>

                <div class="d-flex justify-content-center gap-2">
                    <button type="button"
                        class="btn btn-light btn-compact border px-3"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="button"
                        class="btn btn-danger btn-compact px-3 shadow-sm"
                        id="btnExecuteCartConfirmAction">
                        <i class="fas fa-trash-alt me-1"></i>
                        Yes, Remove
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    let cartPendingDeleteElement = null;

    /**
     * Notification Modal
     */
    function showCartFeaturesModalNotice(message, title = 'Notice', type = 'info') {
        const titleEl = document.getElementById('cartFeaturesNoticeTitle');
        const msgEl = document.getElementById('cartFeaturesNoticeMessage');
        const iconEl = document.getElementById('cartFeaturesNoticeIconWrap');

        if (titleEl) {
            titleEl.innerText = title;
        }

        if (msgEl) {
            msgEl.innerText = message;
        }

        if (iconEl) {
            const icons = {
                danger: '<i class="fas fa-circle-xmark text-danger"></i>',
                error: '<i class="fas fa-circle-xmark text-danger"></i>',
                success: '<i class="fas fa-circle-check text-success"></i>',
                warning: '<i class="fas fa-triangle-exclamation text-warning"></i>',
                info: '<i class="fas fa-circle-info text-primary"></i>'
            };

            iconEl.innerHTML = icons[type] || icons.info;
        }

        const modalEl = document.getElementById('cartFeaturesNoticeModal');

        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    /**
     * Open Remove Confirmation
     */
    function confirmRemoveStickyItem(btn) {
        cartPendingDeleteElement = btn.closest('.sticky-item-row');

        if (!cartPendingDeleteElement) {
            return;
        }

        const modalEl = document.getElementById('cartFeaturesConfirmModal');

        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    /**
     * Execute Confirmed Removal
     */
    const cartConfirmButton = document.getElementById('btnExecuteCartConfirmAction');

    if (cartConfirmButton) {
        cartConfirmButton.addEventListener('click', function() {
            const modalEl = document.getElementById('cartFeaturesConfirmModal');

            if (modalEl && window.bootstrap) {
                bootstrap.Modal.getInstance(modalEl)?.hide();
            }

            if (cartPendingDeleteElement) {
                cartPendingDeleteElement.remove();
                cartPendingDeleteElement = null;

                showCartFeaturesModalNotice(
                    'Navigation item removed.',
                    'Item Removed',
                    'success'
                );
            }
        });
    }

    /**
     * Add New Sticky Footer Item
     */
    function addStickyFooterItem() {
        const container = document.getElementById('sticky-footer-items-container');

        if (!container) {
            return;
        }

        const newIdx = Date.now();

        const html = `
            <div class="sticky-item-row" id="footer-row-${newIdx}">
                <div class="row g-2 align-items-end">

                    <div class="col-6 col-md-3">
                        <label class="form-label">Title</label>
                        <input type="text"
                            name="item_title[]"
                            class="form-control"
                            placeholder="Account">
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label">Icon Class</label>
                        <input type="text"
                            name="item_icon[]"
                            class="form-control font-monospace"
                            value="fas fa-user"
                            placeholder="fas fa-user">
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label">Target URL</label>
                        <input type="text"
                            name="item_url[]"
                            class="form-control font-monospace"
                            value="/user/dashboard"
                            placeholder="/dashboard">
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label">Action</label>

                        <div class="d-flex align-items-center gap-1">
                            <select name="item_action_type[]"
                                class="form-select flex-grow-1 font-monospace">
                                <option value="link">Link</option>
                                <option value="cart">Cart Drawer</option>
                                <option value="search">Search Modal</option>
                                <option value="wishlist">Wishlist</option>
                            </select>

                            <button type="button"
                                class="btn btn-outline-danger item-del-sq-btn"
                                onclick="confirmRemoveStickyItem(this)"
                                title="Remove Item"
                                aria-label="Remove navigation item">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', html);

        showCartFeaturesModalNotice(
            'New item added. Set its title and URL, then save your changes.',
            'Item Added',
            'info'
        );
    }
</script>
@endsection