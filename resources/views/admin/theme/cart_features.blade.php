@extends('layouts.admin')

@section('title', 'Cart, Header & Sticky Navigation Settings - ' . config('app.name'))

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #cartFeaturesRoot .btn,
    #cartFeaturesRoot .btn-sm,
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

    /* Safe Bengali Digits & Monospace Formatting */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Compact Button Styling */
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
    .item-del-sq-btn {
        width: 28px !important;
        height: 28px !important;
        min-width: 28px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 11px !important;
        border-radius: 5px !important;
        transition: all 0.15s ease;
    }

    /* Enterprise Surface Cards */
    .settings-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
        overflow: hidden;
    }
    .settings-panel-header {
        padding: 12px 16px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    /* Feature Switch Row */
    .feature-switch-row {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 10px 14px;
        transition: all 0.15s ease;
    }
    .feature-switch-row:hover {
        border-color: #cbd5e1;
        background: #fafbfc;
    }

    /* Device Visibility Toggle Box */
    .device-toggle-box {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.15s ease;
    }
    .device-toggle-box:has(input:checked) {
        background: #eff6ff;
        border-color: #93c5fd;
    }

    /* Sticky Navigation Item Row */
    .sticky-item-row {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        padding: 10px 12px;
        transition: border-color 0.15s ease;
    }
    .sticky-item-row:hover {
        border-color: #2563eb;
    }

    /* Form Controls */
    #cartFeaturesRoot .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #cartFeaturesRoot .form-control,
    #cartFeaturesRoot .form-select {
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        height: 32px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #cartFeaturesRoot .form-control:focus,
    #cartFeaturesRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }
    #cartFeaturesRoot .form-control-color {
        height: 32px !important;
        padding: 3px 6px !important;
        cursor: pointer;
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

    /* Collapsible Trigger Buttons & Rotating Chevron */
    .collapse-toggle-btn {
        width: 26px;
        height: 26px;
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

    /* Mobile Responsive Optimizations (< 768px) */
    @media (max-width: 767.98px) {
        .top-header-bar {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 8px !important;
        }
        .top-save-btn-wrap .btn-compact {
            width: 100%;
            justify-content: center;
            padding: 8px 14px !important;
        }
        .settings-panel-header {
            padding: 10px 12px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="cartFeaturesRoot">
    <form action="{{ route('admin.theme.setting.cart_features.update') }}" method="POST" id="cartFeaturesForm">
        @csrf

        <!-- Top Header & Save Action -->
        <div class="d-flex top-header-bar justify-content-between align-items-sm-center gap-2 mb-3">
            <div>
                <h5 class="fw-bold mb-1 text-dark d-flex align-items-center" style="font-size: 18px;">
                    <i class="fas fa-layer-group text-primary me-2"></i>
                    <span>Header, Cart &amp; Sticky Navigation</span>
                </h5>
                <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                    Configure sticky header, floating drawer cart, bottom navigation bar and product action boosters.
                </small>
            </div>
            <div class="top-save-btn-wrap">
                <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-4" style="min-height: 35px; font-size: 12px;">
                    <i class="fas fa-check-circle me-1"></i>
                    <span>Save Feature Settings</span>
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-xs mb-3 small py-2 px-3 d-flex align-items-center justify-content-between">
                <div>
                    <i class="fas fa-check-circle text-success me-2"></i>
                    <span class="fw-semibold text-dark">{{ session('success') }}</span>
                </div>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-3">
            <!-- Left Main Column -->
            <div class="col-12 col-lg-8">
                
                <!-- Section 1: Header & Core Feature Rules (COLLAPSIBLE) -->
                <div class="settings-panel-card mb-3">
                    <div class="settings-panel-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-sliders text-primary"></i>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">Header &amp; Core Feature Rules</h6>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase" style="font-size: 9px;">GLOBAL UI</span>
                            <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseHeaderFeatures" aria-expanded="true" aria-controls="collapseHeaderFeatures" title="Toggle Section View">
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="collapse show" id="collapseHeaderFeatures">
                        <div class="p-3 bg-light bg-opacity-25 d-flex flex-column gap-2">
                            <div class="row g-2">
                                <!-- Sticky Header Mode -->
                                <div class="col-12 col-md-6">
                                    <div class="feature-switch-row d-flex justify-content-between align-items-center gap-2 h-100">
                                        <div class="pe-2">
                                            <label class="fw-bold text-dark d-block mb-0" for="sw_sticky_header" style="font-size: 12px; cursor: pointer;">Sticky Header Mode</label>
                                            <small class="text-muted d-block" style="font-size: 10.5px;">Locks navigation header at the top during page scroll.</small>
                                        </div>
                                        <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                            <input class="form-check-input ms-0" type="checkbox" id="sw_sticky_header" name="feature_sticky_header" value="1" {{ ($settings['feature_sticky_header'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>

                                <!-- Glassmorphism Design -->
                                <div class="col-12 col-md-6">
                                    <div class="feature-switch-row d-flex justify-content-between align-items-center gap-2 h-100">
                                        <div class="pe-2">
                                            <label class="fw-bold text-dark d-block mb-0" for="sw_glass_header" style="font-size: 12px; cursor: pointer;">Glassmorphism Blur Header</label>
                                            <small class="text-muted d-block" style="font-size: 10.5px;">Applies backdrop blur and translucent surface effect.</small>
                                        </div>
                                        <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                            <input class="form-check-input ms-0" type="checkbox" id="sw_glass_header" name="feature_glassmorphism_header" value="1" {{ ($settings['feature_glassmorphism_header'] ?? '0') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>

                                <!-- Floating Cart Drawer -->
                                <div class="col-12 col-md-6">
                                    <div class="feature-switch-row d-flex justify-content-between align-items-center gap-2 h-100">
                                        <div class="pe-2">
                                            <label class="fw-bold text-dark d-block mb-0" for="sw_float_cart" style="font-size: 12px; cursor: pointer;">Side Drawer Cart Modal</label>
                                            <small class="text-muted d-block" style="font-size: 10.5px;">Slides out real-time cart items without page refresh.</small>
                                        </div>
                                        <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                            <input class="form-check-input ms-0" type="checkbox" id="sw_float_cart" name="feature_floating_cart_modal" value="1" {{ ($settings['feature_floating_cart_modal'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>

                                <!-- AJAX Live Search -->
                                <div class="col-12 col-md-6">
                                    <div class="feature-switch-row d-flex justify-content-between align-items-center gap-2 h-100">
                                        <div class="pe-2">
                                            <label class="fw-bold text-dark d-block mb-0" for="sw_ajax_search" style="font-size: 12px; cursor: pointer;">AJAX Live Autocomplete Search</label>
                                            <small class="text-muted d-block" style="font-size: 10.5px;">Instant product dropdown while typing keywords.</small>
                                        </div>
                                        <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                            <input class="form-check-input ms-0" type="checkbox" id="sw_ajax_search" name="feature_ajax_search" value="1" {{ ($settings['feature_ajax_search'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>

                                <!-- Direct Buy Now Button -->
                                <div class="col-12">
                                    <div class="feature-switch-row d-flex justify-content-between align-items-center gap-2">
                                        <div class="pe-2">
                                            <label class="fw-bold text-dark d-block mb-0" for="sw_buy_now" style="font-size: 12px; cursor: pointer;">Direct Buy Now Button on Product Cards</label>
                                            <small class="text-muted d-block" style="font-size: 10.5px;">Skips intermediate step and takes buyers directly to instant checkout.</small>
                                        </div>
                                        <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                            <input class="form-check-input ms-0" type="checkbox" id="sw_buy_now" name="feature_buy_now_btn" value="1" {{ ($settings['feature_buy_now_btn'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Sticky Bottom Navigation Bar (COLLAPSIBLE) -->
                <div class="settings-panel-card mb-3">
                    <div class="settings-panel-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-shoe-prints text-success"></i>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">Sticky Bottom Navigation Bar (App Style)</h6>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace text-uppercase" style="font-size: 9px;">APP FOOTER</span>
                            <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseStickyFooter" aria-expanded="true" aria-controls="collapseStickyFooter" title="Toggle Section View">
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="collapse show" id="collapseStickyFooter">
                        <div class="p-3">
                            <!-- Master Switch -->
                            <div class="feature-switch-row d-flex justify-content-between align-items-center gap-2 mb-3">
                                <div class="pe-2">
                                    <label class="fw-bold text-dark d-block mb-0" for="sw_sticky_footer" style="font-size: 12px; cursor: pointer;">Enable Sticky Bottom Navigation</label>
                                    <small class="text-muted d-block" style="font-size: 10.5px;">Renders app-like floating bottom menu bar across pages.</small>
                                </div>
                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox" id="sw_sticky_footer" name="feature_sticky_footer" value="1" {{ ($settings['feature_sticky_footer'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>

                            <!-- Screen Visibility (3-Column Symmetric Grid) -->
                            <label class="form-label mb-2"><i class="fas fa-desktop text-primary me-1"></i> Device Visibility Matrix:</label>
                            <div class="row g-2 mb-3">
                                <div class="col-12 col-sm-4">
                                    <div class="device-toggle-box">
                                        <label class="small fw-bold text-dark mb-0" for="sfMobile" style="font-size: 11.5px; cursor: pointer;">
                                            <i class="fas fa-mobile-alt text-muted me-1"></i> Mobile Screen
                                        </label>
                                        <div class="form-check form-switch m-0 ps-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="sticky_footer_show_mobile" id="sfMobile" value="1" {{ ($settings['sticky_footer_show_mobile'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="device-toggle-box">
                                        <label class="small fw-bold text-dark mb-0" for="sfTablet" style="font-size: 11.5px; cursor: pointer;">
                                            <i class="fas fa-tablet-alt text-muted me-1"></i> Tablet Screen
                                        </label>
                                        <div class="form-check form-switch m-0 ps-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="sticky_footer_show_tablet" id="sfTablet" value="1" {{ ($settings['sticky_footer_show_tablet'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="device-toggle-box">
                                        <label class="small fw-bold text-dark mb-0" for="sfDesktop" style="font-size: 11.5px; cursor: pointer;">
                                            <i class="fas fa-laptop text-muted me-1"></i> Desktop Screen
                                        </label>
                                        <div class="form-check form-switch m-0 ps-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="sticky_footer_show_desktop" id="sfDesktop" value="1" {{ ($settings['sticky_footer_show_desktop'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Layout & Behavior Rules -->
                            <div class="row g-2 pb-3 mb-3 border-bottom">
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Desktop Bar Layout</label>
                                    <select name="sticky_footer_desktop_style" class="form-select">
                                        <option value="boxed_floating" {{ ($settings['sticky_footer_desktop_style'] ?? 'boxed_floating') == 'boxed_floating' ? 'selected' : '' }}>Boxed Floating Bar</option>
                                        <option value="full_width" {{ ($settings['sticky_footer_desktop_style'] ?? '') == 'full_width' ? 'selected' : '' }}>Full Width (100% Screen)</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Scroll Behavior</label>
                                    <div class="device-toggle-box h-auto" style="min-height: 32px;">
                                        <label class="small fw-semibold text-dark mb-0" for="hideOnScrollCheck" style="font-size: 11px; cursor: pointer;">Hide on scroll down</label>
                                        <div class="form-check form-switch m-0 ps-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="sticky_footer_hide_on_scroll" id="hideOnScrollCheck" value="1" {{ ($settings['sticky_footer_hide_on_scroll'] ?? '0') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Glass Effect</label>
                                    <div class="device-toggle-box h-auto" style="min-height: 32px;">
                                        <label class="small fw-semibold text-dark mb-0" for="glassFooterCheck" style="font-size: 11px; cursor: pointer;">Glassmorphism Blur</label>
                                        <div class="form-check form-switch m-0 ps-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="sticky_footer_glassmorphism" id="glassFooterCheck" value="1" {{ ($settings['sticky_footer_glassmorphism'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Color Palette Controls -->
                            <label class="form-label mb-2"><i class="fas fa-palette text-danger me-1"></i> Color Scheme Controls:</label>
                            <div class="row g-2 pb-3 mb-3 border-bottom">
                                <div class="col-4">
                                    <label class="form-label">Background</label>
                                    <input type="color" name="sticky_footer_bg" class="form-control form-control-color w-100" value="{{ $settings['sticky_footer_bg'] ?? '#ffffff' }}">
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Text / Icon</label>
                                    <input type="color" name="sticky_footer_text_color" class="form-control form-control-color w-100" value="{{ $settings['sticky_footer_text_color'] ?? '#64748b' }}">
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Active / Hover</label>
                                    <input type="color" name="sticky_footer_active_color" class="form-control form-control-color w-100" value="{{ $settings['sticky_footer_active_color'] ?? '#2563eb' }}">
                                </div>
                            </div>

                            <!-- Navigation Items Builder -->
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 13px;">Navigation Items Builder</h6>
                                    <small class="text-muted" style="font-size: 10.5px;">Configure bottom navigation shortcuts and actions</small>
                                </div>
                                <button type="button" class="btn btn-primary btn-compact shadow-xs" onclick="addStickyFooterItem()">
                                    <i class="fas fa-plus me-1" style="font-size: 10px;"></i>
                                    <span>Add Element</span>
                                </button>
                            </div>

                            <div id="sticky-footer-items-container" class="d-flex flex-column gap-2">
                                @foreach($footerItems as $index => $item)
                                    <div class="sticky-item-row" id="footer-row-{{ $index }}">
                                        <div class="row g-2 align-items-end">
                                            <div class="col-6 col-md-3">
                                                <label class="form-label">Title</label>
                                                <input type="text" name="item_title[]" class="form-control" value="{{ $item['title'] ?? '' }}" placeholder="Shop">
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <label class="form-label">Icon (FA Class)</label>
                                                <input type="text" name="item_icon[]" class="form-control font-monospace" value="{{ $item['icon'] ?? 'fas fa-circle' }}" placeholder="fas fa-store">
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <label class="form-label">Target URL</label>
                                                <input type="text" name="item_url[]" class="form-control font-monospace" value="{{ $item['url'] ?? '#' }}" placeholder="/shop">
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <label class="form-label">Action Type</label>
                                                <div class="d-flex align-items-center gap-1">
                                                    <select name="item_action_type[]" class="form-select flex-grow-1 font-monospace">
                                                        <option value="link" {{ ($item['action_type'] ?? '') == 'link' ? 'selected' : '' }}>Regular Link</option>
                                                        <option value="cart" {{ ($item['action_type'] ?? '') == 'cart' ? 'selected' : '' }}>Cart Drawer</option>
                                                        <option value="search" {{ ($item['action_type'] ?? '') == 'search' ? 'selected' : '' }}>Search Modal</option>
                                                        <option value="wishlist" {{ ($item['action_type'] ?? '') == 'wishlist' ? 'selected' : '' }}>Wishlist</option>
                                                    </select>
                                                    <button type="button" class="btn btn-outline-danger item-del-sq-btn flex-shrink-0" onclick="confirmRemoveStickyItem(this)" title="Remove Item">
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

                <!-- Section 3: Sticky Single Product Order Bar (COLLAPSIBLE) -->
                <div class="settings-panel-card mb-3">
                    <div class="settings-panel-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-cart-arrow-down text-danger"></i>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">Sticky Single Product Order Bar</h6>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace text-uppercase" style="font-size: 9px;">BOOSTER</span>
                            <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseProductBar" aria-expanded="true" aria-controls="collapseProductBar" title="Toggle Section View">
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="collapse show" id="collapseProductBar">
                        <div class="p-3">
                            <div class="feature-switch-row d-flex justify-content-between align-items-center gap-2 mb-3">
                                <div class="pe-2">
                                    <label class="fw-bold text-dark d-block mb-0" for="sw_sticky_prod_bar" style="font-size: 12px; cursor: pointer;">Enable Sticky Product Bar</label>
                                    <small class="text-muted d-block" style="font-size: 10.5px;">Shows persistent purchase bar when scrolling on product detail page.</small>
                                </div>
                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox" id="sw_sticky_prod_bar" name="feature_sticky_product_bar" value="1" {{ ($settings['feature_sticky_product_bar'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>

                            <label class="form-label mb-2"><i class="fas fa-desktop text-primary me-1"></i> Device Visibility Matrix:</label>
                            <div class="row g-2 mb-3">
                                <div class="col-12 col-sm-4">
                                    <div class="device-toggle-box">
                                        <label class="small fw-bold text-dark mb-0" for="spbMobile" style="font-size: 11.5px; cursor: pointer;">
                                            <i class="fas fa-mobile-alt text-muted me-1"></i> Mobile Screen
                                        </label>
                                        <div class="form-check form-switch m-0 ps-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="sticky_product_bar_show_mobile" id="spbMobile" value="1" {{ ($settings['sticky_product_bar_show_mobile'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="device-toggle-box">
                                        <label class="small fw-bold text-dark mb-0" for="spbTablet" style="font-size: 11.5px; cursor: pointer;">
                                            <i class="fas fa-tablet-alt text-muted me-1"></i> Tablet Screen
                                        </label>
                                        <div class="form-check form-switch m-0 ps-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="sticky_product_bar_show_tablet" id="spbTablet" value="1" {{ ($settings['sticky_product_bar_show_tablet'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="device-toggle-box">
                                        <label class="small fw-bold text-dark mb-0" for="spbDesktop" style="font-size: 11.5px; cursor: pointer;">
                                            <i class="fas fa-laptop text-muted me-1"></i> Desktop Screen
                                        </label>
                                        <div class="form-check form-switch m-0 ps-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="sticky_product_bar_show_desktop" id="spbDesktop" value="1" {{ ($settings['sticky_product_bar_show_desktop'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Position on Screen</label>
                                    <select name="sticky_product_bar_position" class="form-select">
                                        <option value="top" {{ ($settings['sticky_product_bar_position'] ?? 'top') == 'top' ? 'selected' : '' }}>Top of Screen</option>
                                        <option value="bottom" {{ ($settings['sticky_product_bar_position'] ?? '') == 'bottom' ? 'selected' : '' }}>Bottom of Screen</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Action Button</label>
                                    <div class="device-toggle-box h-auto" style="min-height: 32px;">
                                        <label class="small fw-semibold text-dark mb-0" for="spbBuyNow" style="font-size: 11px; cursor: pointer;">Direct Order Now Button</label>
                                        <div class="form-check form-switch m-0 ps-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="sticky_product_bar_show_buynow" id="spbBuyNow" value="1" {{ ($settings['sticky_product_bar_show_buynow'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Scroll to Top Button Settings (COLLAPSIBLE) -->
                <div class="settings-panel-card mb-3">
                    <div class="settings-panel-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-arrow-circle-up text-warning"></i>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">Scroll to Top Navigation Button</h6>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace text-uppercase" style="font-size: 9px;">NAVIGATION</span>
                            <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseScrollTop" aria-expanded="true" aria-controls="collapseScrollTop" title="Toggle Section View">
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="collapse show" id="collapseScrollTop">
                        <div class="p-3">
                            <div class="feature-switch-row d-flex justify-content-between align-items-center gap-2 mb-3">
                                <div class="pe-2">
                                    <label class="fw-bold text-dark d-block mb-0" for="sw_scroll_top" style="font-size: 12px; cursor: pointer;">Enable Scroll to Top Button</label>
                                    <small class="text-muted d-block" style="font-size: 10.5px;">Allows users to jump back smoothly to the top of the page.</small>
                                </div>
                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox" id="sw_scroll_top" name="feature_scroll_to_top" value="1" {{ ($settings['feature_scroll_to_top'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>

                            <label class="form-label mb-2"><i class="fas fa-desktop text-primary me-1"></i> Device Visibility Matrix:</label>
                            <div class="row g-2 mb-3">
                                <div class="col-12 col-sm-4">
                                    <div class="device-toggle-box">
                                        <label class="small fw-bold text-dark mb-0" for="stMobile" style="font-size: 11.5px; cursor: pointer;">
                                            <i class="fas fa-mobile-alt text-muted me-1"></i> Mobile Screen
                                        </label>
                                        <div class="form-check form-switch m-0 ps-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="scroll_top_show_mobile" id="stMobile" value="1" {{ ($settings['scroll_top_show_mobile'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="device-toggle-box">
                                        <label class="small fw-bold text-dark mb-0" for="stTablet" style="font-size: 11.5px; cursor: pointer;">
                                            <i class="fas fa-tablet-alt text-muted me-1"></i> Tablet Screen
                                        </label>
                                        <div class="form-check form-switch m-0 ps-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="scroll_top_show_tablet" id="stTablet" value="1" {{ ($settings['scroll_top_show_tablet'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="device-toggle-box">
                                        <label class="small fw-bold text-dark mb-0" for="stDesktop" style="font-size: 11.5px; cursor: pointer;">
                                            <i class="fas fa-laptop text-muted me-1"></i> Desktop Screen
                                        </label>
                                        <div class="form-check form-switch m-0 ps-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="scroll_top_show_desktop" id="stDesktop" value="1" {{ ($settings['scroll_top_show_desktop'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Button Design Style</label>
                                    <select name="scroll_top_style" class="form-select font-monospace">
                                        <option value="classic_rounded" {{ ($settings['scroll_top_style'] ?? 'classic_rounded') == 'classic_rounded' ? 'selected' : '' }}>Style 1: Classic Circle</option>
                                        <option value="square_modern" {{ ($settings['scroll_top_style'] ?? '') == 'square_modern' ? 'selected' : '' }}>Style 2: Square Modern</option>
                                        <option value="minimal_clean" {{ ($settings['scroll_top_style'] ?? '') == 'minimal_clean' ? 'selected' : '' }}>Style 3: Minimal Glass</option>
                                        <option value="pulse_glow" {{ ($settings['scroll_top_style'] ?? '') == 'pulse_glow' ? 'selected' : '' }}>Style 4: Pulse Glow</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Display Position</label>
                                    <select name="scroll_top_position" class="form-select font-monospace">
                                        <option value="right" {{ ($settings['scroll_top_position'] ?? 'right') == 'right' ? 'selected' : '' }}>Bottom Right</option>
                                        <option value="left" {{ ($settings['scroll_top_position'] ?? '') == 'left' ? 'selected' : '' }}>Bottom Left</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Save Button -->
                <div class="text-end mb-3">
                    <button type="submit" class="btn btn-primary btn-compact px-4 py-2 shadow-xs w-100 w-md-auto fw-bold" style="min-height: 35px; font-size: 12.5px;">
                        <i class="fas fa-check-circle me-1"></i>
                        <span>Save Feature Settings</span>
                    </button>
                </div>
            </div>

            <!-- Right Sidebar Column: Live Guide (COLLAPSIBLE) -->
            <div class="col-12 col-lg-4">
                <div class="settings-panel-card sticky-top" style="top: 20px;">
                    <div class="settings-panel-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-info-circle text-primary"></i>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">Architecture Guide</h6>
                        </div>
                        <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseGuideCard" aria-expanded="true" aria-controls="collapseGuideCard" title="Toggle Guide">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>

                    <div class="collapse show" id="collapseGuideCard">
                        <div class="p-3 text-secondary">
                            <ul class="small ps-3 mb-0 d-flex flex-column gap-2" style="font-size: 11.5px; line-height: 1.4;">
                                <li><strong class="text-dark">Sticky Product Bar:</strong> Product details page-e scroll korle persistent purchase bar auto trigger hoy[cite: 37]।</li>
                                <li><strong class="text-dark">Device Visibility:</strong> Mobile, tablet o desktop screen-er jonno navigation bars shorashori alada toggle kora jay[cite: 37]।</li>
                                <li><strong class="text-dark">Real-Time Sync:</strong> Shob color o style settings storefront-e reload-free vabe sync hoy[cite: 37]।</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal 1: Custom Enterprise Notice Modal -->
<div class="modal fade cart-modal-scope" id="cartFeaturesNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2" id="cartFeaturesNoticeIconWrap">
                    <i class="fas fa-circle-check text-success fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="cartFeaturesNoticeTitle" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="cartFeaturesNoticeMessage" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-primary btn-compact px-4" data-bs-dismiss="modal">Acknowledge</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Custom Enterprise Confirmation Modal -->
<div class="modal fade cart-modal-scope" id="cartFeaturesConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 108600;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2 text-danger">
                    <i class="fas fa-trash-can fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="cartFeaturesConfirmTitle" style="font-size: 14.5px;">Remove Item?</h6>
                <p class="text-muted small mb-3" id="cartFeaturesConfirmMessage" style="font-size: 12px; line-height: 1.45;">Are you sure you want to remove this navigation element?</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-compact shadow-xs" id="btnExecuteCartConfirmAction">Yes, Remove</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let cartPendingDeleteElement = null;

function showCartFeaturesModalNotice(message, title = 'Notice', type = 'info') {
    const titleEl = document.getElementById('cartFeaturesNoticeTitle');
    const msgEl   = document.getElementById('cartFeaturesNoticeMessage');
    const iconEl  = document.getElementById('cartFeaturesNoticeIconWrap');

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

    const modalEl = document.getElementById('cartFeaturesNoticeModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

function confirmRemoveStickyItem(btn) {
    cartPendingDeleteElement = btn.closest('.sticky-item-row');
    const modalEl = document.getElementById('cartFeaturesConfirmModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

document.getElementById('btnExecuteCartConfirmAction').addEventListener('click', function() {
    const modalEl = document.getElementById('cartFeaturesConfirmModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getInstance(modalEl)?.hide();
    }
    if (cartPendingDeleteElement) {
        cartPendingDeleteElement.remove();
        cartPendingDeleteElement = null;
        showCartFeaturesModalNotice('Navigation element removed successfully.', 'Element Removed', 'success');
    }
});

function addStickyFooterItem() {
    const container = document.getElementById('sticky-footer-items-container');
    const newIdx = Date.now();
    const html = `
        <div class="sticky-item-row" id="footer-row-${newIdx}">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="item_title[]" class="form-control" placeholder="Account">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">Icon (FA Class)</label>
                    <input type="text" name="item_icon[]" class="form-control font-monospace" value="fas fa-user" placeholder="fas fa-user">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">Target URL</label>
                    <input type="text" name="item_url[]" class="form-control font-monospace" value="/user/dashboard" placeholder="/dashboard">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">Action Type</label>
                    <div class="d-flex align-items-center gap-1">
                        <select name="item_action_type[]" class="form-select flex-grow-1 font-monospace">
                            <option value="link">Regular Link</option>
                            <option value="cart">Cart Drawer</option>
                            <option value="search">Search Modal</option>
                            <option value="wishlist">Wishlist</option>
                        </select>
                        <button type="button" class="btn btn-outline-danger item-del-sq-btn flex-shrink-0" onclick="confirmRemoveStickyItem(this)" title="Remove Item">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    showCartFeaturesModalNotice('New navigation item added. Configure titles and URLs then click Save.', 'Item Added', 'info');
}
</script>
@endsection