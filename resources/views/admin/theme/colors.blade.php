@extends('layouts.admin')

@section('title', 'Theme Colors & Styles - ' . config('app.name'))

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #themeColorsRoot .btn,
    #themeColorsRoot .btn-sm,
    #themeColorsRoot button,
    #themeColorsRoot .form-control,
    #themeColorsRoot .badge,
    #themeColorsRoot .card,
    #themeColorsRoot .theme-header-card,
    #themeColorsRoot .theme-section-card,
    #themeColorsRoot .color-control-wrapper,
    #themeColorsRoot .color-input-container,
    #themeColorsRoot .collapse-toggle-btn,
    #themeColorsRoot .bottom-action-card,
    .colors-modal-scope .modal-content,
    .colors-modal-scope .btn,
    .colors-modal-scope .badge {
        border-radius: 5px !important;
    }

    /* Safe Monospace & Bengali Digit Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Compact Sleek Button Styling */
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

    /* Enterprise Surface Header Card */
    .theme-header-card {
        background: #0f172a;
        color: #ffffff;
        padding: 14px 18px;
        border: 1px solid #1e293b;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        margin-bottom: 14px;
    }

    /* Enterprise Section Card */
    .theme-section-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 14px;
        overflow: hidden;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .theme-section-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }

    .theme-section-header {
        padding: 10px 14px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .section-title-text {
        font-size: 12.5px;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Collapsible Trigger Buttons & Rotating Chevron */
    .collapse-toggle-btn {
        width: 24px;
        height: 24px;
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

    /* Color Control Wrapper */
    .color-control-wrapper {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 8px 10px;
        transition: all 0.15s ease;
        height: 100%;
    }
    .color-control-wrapper:hover {
        background: #ffffff;
        border-color: #cbd5e1;
    }
    .color-control-wrapper label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Color Swatch & Hex Input Container */
    .color-input-container {
        display: flex;
        align-items: center;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        overflow: hidden;
        height: 30px;
        transition: all 0.15s ease;
    }
    .color-input-container:focus-within {
        border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12);
    }

    .color-picker-widget {
        width: 32px;
        height: 30px;
        border: none;
        padding: 2px;
        background: transparent;
        cursor: pointer;
        flex-shrink: 0;
    }
    .color-picker-widget::-webkit-color-swatch-wrapper {
        padding: 0;
    }
    .color-picker-widget::-webkit-color-swatch {
        border: none;
        border-radius: 4px;
    }

    .color-hex-input {
        border: none;
        border-left: 1px solid #e2e8f0;
        outline: none;
        width: 100%;
        padding: 4px 8px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 11.5px;
        color: #0f172a;
        background: transparent;
        text-transform: lowercase;
        font-weight: 600;
    }

    /* Bottom Action Bar */
    .bottom-action-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 12px 16px;
        box-shadow: 0 -2px 10px rgba(15, 23, 42, 0.03);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }

    /* Mobile Responsive (< 768px) */
    @media (max-width: 767.98px) {
        .theme-header-card {
            padding: 12px 14px !important;
        }
        .header-top-wrap {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 8px !important;
        }
        .top-action-btns {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .top-action-btns .btn {
            width: 100%;
            justify-content: center;
        }
        .bottom-action-card {
            flex-direction: column !important;
            padding: 12px !important;
        }
        .bottom-action-card .btn-compact {
            width: 100% !important;
            justify-content: center !important;
            padding: 8px 14px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-2" id="themeColorsRoot">

    <!-- 1. Header & Quick Controls -->
    <div class="theme-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 header-top-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase" style="font-size: 9px;">
                    <i class="fas fa-palette me-1"></i> THEME ENGINE
                </span>
                <span class="badge bg-light text-white-50 border border-secondary font-monospace" style="font-size: 9px;">
                    LIVE STORE SYNC
                </span>
            </div>
            <h5 class="fw-bold mb-0 d-flex align-items-center gap-2" style="font-size: 17px;">
                <i class="fas fa-palette text-warning"></i>
                <span>Theme Colors &amp; Styles Control</span>
            </h5>
            <small class="text-white-50 d-block mt-1" style="font-size: 11.5px; line-height: 1.35;">
                Configure global storefront color schemes, navigation palettes, action buttons and typography.
            </small>
        </div>

        <div class="top-action-btns d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-compact text-white border-white-50 shadow-xs" onclick="toggleAllColorSections()">
                <i class="fas fa-arrows-up-down me-1"></i>
                <span id="expandAllBtnText">Collapse All</span>
            </button>
            <a href="{{ url('/') }}" target="_blank" class="btn btn-primary btn-compact shadow-xs fw-bold">
                <i class="fas fa-arrow-up-right-from-square me-1"></i>
                <span>Storefront View</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-xs py-2 px-3 mb-2 small d-flex align-items-center justify-content-between" style="border-radius: 5px;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.theme.setting.colors.update') }}" method="POST" id="themeColorsForm">
        @csrf
        <!-- Hidden input for modal reset defaults confirmation -->
        <input type="hidden" name="reset_defaults" id="reset_defaults_hidden_input" value="">

        <div class="row g-2 g-md-3 mb-2">
            
            <!-- 1. Global Backgrounds (COLLAPSIBLE) -->
            <div class="col-12 col-xl-6">
                <div class="theme-section-card">
                    <div class="theme-section-header d-flex justify-content-between align-items-center">
                        <div class="section-title-text">
                            <i class="fas fa-layer-group text-primary"></i>
                            <span>Global Backgrounds</span>
                        </div>
                        <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseGlobalBg" aria-expanded="true" aria-controls="collapseGlobalBg" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseGlobalBg">
                        <div class="p-3">
                            <div class="row g-2">
                                <div class="col-12 col-sm-6">
                                    <div class="color-control-wrapper">
                                        <label>Body Background</label>
                                        <div class="color-input-container">
                                            <input type="color" class="color-picker-widget" value="{{ $colors['color_site_bg'] ?? '#f8fafc' }}">
                                            <input type="text" name="color_site_bg" class="color-hex-input" value="{{ $colors['color_site_bg'] ?? '#f8fafc' }}" placeholder="#f8fafc">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="color-control-wrapper">
                                        <label>Container / Card BG</label>
                                        <div class="color-input-container">
                                            <input type="color" class="color-picker-widget" value="{{ $colors['color_card_bg'] ?? '#ffffff' }}">
                                            <input type="text" name="color_card_bg" class="color-hex-input" value="{{ $colors['color_card_bg'] ?? '#ffffff' }}" placeholder="#ffffff">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Header & Search (COLLAPSIBLE) -->
            <div class="col-12 col-xl-6">
                <div class="theme-section-card">
                    <div class="theme-section-header d-flex justify-content-between align-items-center">
                        <div class="section-title-text">
                            <i class="fas fa-heading text-success"></i>
                            <span>Header &amp; Search</span>
                        </div>
                        <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseHeaderSearch" aria-expanded="true" aria-controls="collapseHeaderSearch" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseHeaderSearch">
                        <div class="p-3">
                            <div class="row g-2">
                                <div class="col-12 col-sm-4">
                                    <div class="color-control-wrapper">
                                        <label>Header BG</label>
                                        <div class="color-input-container">
                                            <input type="color" class="color-picker-widget" value="{{ $colors['color_header_bg'] ?? '#ffffff' }}">
                                            <input type="text" name="color_header_bg" class="color-hex-input" value="{{ $colors['color_header_bg'] ?? '#ffffff' }}" placeholder="#ffffff">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="color-control-wrapper">
                                        <label>Header Text / Icon</label>
                                        <div class="color-input-container">
                                            <input type="color" class="color-picker-widget" value="{{ $colors['color_header_text'] ?? '#334155' }}">
                                            <input type="text" name="color_header_text" class="color-hex-input" value="{{ $colors['color_header_text'] ?? '#334155' }}" placeholder="#334155">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="color-control-wrapper">
                                        <label>Search Button BG</label>
                                        <div class="color-input-container">
                                            <input type="color" class="color-picker-widget" value="{{ $colors['color_header_search_btn_bg'] ?? '#2563eb' }}">
                                            <input type="text" name="color_header_search_btn_bg" class="color-hex-input" value="{{ $colors['color_header_search_btn_bg'] ?? '#2563eb' }}" placeholder="#2563eb">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Menu Links & Hover (COLLAPSIBLE) -->
            <div class="col-12 col-xl-6">
                <div class="theme-section-card">
                    <div class="theme-section-header d-flex justify-content-between align-items-center">
                        <div class="section-title-text">
                            <i class="fas fa-bars text-info"></i>
                            <span>Menu Links &amp; Navigation</span>
                        </div>
                        <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseMenuLinks" aria-expanded="true" aria-controls="collapseMenuLinks" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseMenuLinks">
                        <div class="p-3">
                            <div class="row g-2">
                                <div class="col-12 col-sm-4">
                                    <div class="color-control-wrapper">
                                        <label>Menu Text Color</label>
                                        <div class="color-input-container">
                                            <input type="color" class="color-picker-widget" value="{{ $colors['color_menu_text'] ?? '#334155' }}">
                                            <input type="text" name="color_menu_text" class="color-hex-input" value="{{ $colors['color_menu_text'] ?? '#334155' }}" placeholder="#334155">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="color-control-wrapper">
                                        <label>Menu Hover State</label>
                                        <div class="color-input-container">
                                            <input type="color" class="color-picker-widget" value="{{ $colors['color_menu_hover'] ?? '#00a8ff' }}">
                                            <input type="text" name="color_menu_hover" class="color-hex-input" value="{{ $colors['color_menu_hover'] ?? '#00a8ff' }}" placeholder="#00a8ff">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="color-control-wrapper">
                                        <label>Active Menu Pill BG</label>
                                        <div class="color-input-container">
                                            <input type="color" class="color-picker-widget" value="{{ $colors['color_menu_active_bg'] ?? '#e0f2fe' }}">
                                            <input type="text" name="color_menu_active_bg" class="color-hex-input" value="{{ $colors['color_menu_active_bg'] ?? '#e0f2fe' }}" placeholder="#e0f2fe">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Common Buttons (COLLAPSIBLE) -->
            <div class="col-12 col-xl-6">
                <div class="theme-section-card">
                    <div class="theme-section-header d-flex justify-content-between align-items-center">
                        <div class="section-title-text">
                            <i class="fas fa-check-circle text-success"></i>
                            <span>Common Site Buttons</span>
                        </div>
                        <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseCommonButtons" aria-expanded="true" aria-controls="collapseCommonButtons" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseCommonButtons">
                        <div class="p-3">
                            <div class="row g-2">
                                <div class="col-12 col-sm-6">
                                    <div class="color-control-wrapper">
                                        <label>Primary Button BG</label>
                                        <div class="color-input-container">
                                            <input type="color" class="color-picker-widget" value="{{ $colors['color_common_btn_bg'] ?? '#2563eb' }}">
                                            <input type="text" name="color_common_btn_bg" class="color-hex-input" value="{{ $colors['color_common_btn_bg'] ?? '#2563eb' }}" placeholder="#2563eb">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="color-control-wrapper">
                                        <label>Button Text Color</label>
                                        <div class="color-input-container">
                                            <input type="color" class="color-picker-widget" value="{{ $colors['color_common_btn_text'] ?? '#ffffff' }}">
                                            <input type="text" name="color_common_btn_text" class="color-hex-input" value="{{ $colors['color_common_btn_text'] ?? '#ffffff' }}" placeholder="#ffffff">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. Product Card Buttons (COLLAPSIBLE) -->
            <div class="col-12 col-xl-6">
                <div class="theme-section-card">
                    <div class="theme-section-header d-flex justify-content-between align-items-center">
                        <div class="section-title-text">
                            <i class="fas fa-th text-primary"></i>
                            <span>Product Card Buttons</span>
                        </div>
                        <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseCardButtons" aria-expanded="true" aria-controls="collapseCardButtons" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseCardButtons">
                        <div class="p-3">
                            <div class="row g-2">
                                <div class="col-12 col-sm-6">
                                    <div class="color-control-wrapper">
                                        <label>Add to Cart BG</label>
                                        <div class="color-input-container">
                                            <input type="color" class="color-picker-widget" value="{{ $colors['color_card_btn_cart_bg'] ?? '#2563eb' }}">
                                            <input type="text" name="color_card_btn_cart_bg" class="color-hex-input" value="{{ $colors['color_card_btn_cart_bg'] ?? '#2563eb' }}" placeholder="#2563eb">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="color-control-wrapper">
                                        <label>Add to Cart Text</label>
                                        <div class="color-input-container">
                                            <input type="color" class="color-picker-widget" value="{{ $colors['color_card_btn_cart_text'] ?? '#ffffff' }}">
                                            <input type="text" name="color_card_btn_cart_text" class="color-hex-input" value="{{ $colors['color_card_btn_cart_text'] ?? '#ffffff' }}" placeholder="#ffffff">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="color-control-wrapper">
                                        <label>Order Now BG</label>
                                        <div class="color-input-container">
                                            <input type="color" class="color-picker-widget" value="{{ $colors['color_card_btn_order_bg'] ?? '#0f172a' }}">
                                            <input type="text" name="color_card_btn_order_bg" class="color-hex-input" value="{{ $colors['color_card_btn_order_bg'] ?? '#0f172a' }}" placeholder="#0f172a">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="color-control-wrapper">
                                        <label>Order Now Text</label>
                                        <div class="color-input-container">
                                            <input type="color" class="color-picker-widget" value="{{ $colors['color_card_btn_order_text'] ?? '#ffffff' }}">
                                            <input type="text" name="color_card_btn_order_text" class="color-hex-input" value="{{ $colors['color_card_btn_order_text'] ?? '#ffffff' }}" placeholder="#ffffff">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 6. Single Product Details Buttons (COLLAPSIBLE) -->
            <div class="col-12 col-xl-6">
                <div class="theme-section-card">
                    <div class="theme-section-header d-flex justify-content-between align-items-center">
                        <div class="section-title-text">
                            <i class="fas fa-box-open text-warning"></i>
                            <span>Product Details Buttons</span>
                        </div>
                        <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseDetailsButtons" aria-expanded="true" aria-controls="collapseDetailsButtons" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseDetailsButtons">
                        <div class="p-3">
                            <div class="row g-2">
                                <div class="col-12 col-sm-6">
                                    <div class="color-control-wrapper">
                                        <label>Details Buy Now BG</label>
                                        <div class="color-input-container">
                                            <input type="color" class="color-picker-widget" value="{{ $colors['color_details_btn_order_bg'] ?? '#0dcaf0' }}">
                                            <input type="text" name="color_details_btn_order_bg" class="color-hex-input" value="{{ $colors['color_details_btn_order_bg'] ?? '#0dcaf0' }}" placeholder="#0dcaf0">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="color-control-wrapper">
                                        <label>Details Buy Now Text</label>
                                        <div class="color-input-container">
                                            <input type="color" class="color-picker-widget" value="{{ $colors['color_details_btn_order_text'] ?? '#ffffff' }}">
                                            <input type="text" name="color_details_btn_order_text" class="color-hex-input" value="{{ $colors['color_details_btn_order_text'] ?? '#ffffff' }}" placeholder="#ffffff">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="color-control-wrapper">
                                        <label>Details Cart BG</label>
                                        <div class="color-input-container">
                                            <input type="color" class="color-picker-widget" value="{{ $colors['color_details_btn_cart_bg'] ?? '#ffc107' }}">
                                            <input type="text" name="color_details_btn_cart_bg" class="color-hex-input" value="{{ $colors['color_details_btn_cart_bg'] ?? '#ffc107' }}" placeholder="#ffc107">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="color-control-wrapper">
                                        <label>Details Cart Text</label>
                                        <div class="color-input-container">
                                            <input type="color" class="color-picker-widget" value="{{ $colors['color_details_btn_cart_text'] ?? '#212529' }}">
                                            <input type="text" name="color_details_btn_cart_text" class="color-hex-input" value="{{ $colors['color_details_btn_cart_text'] ?? '#212529' }}" placeholder="#212529">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 7. Typography & Prices (COLLAPSIBLE) -->
            <div class="col-12 col-xl-6">
                <div class="theme-section-card">
                    <div class="theme-section-header d-flex justify-content-between align-items-center">
                        <div class="section-title-text">
                            <i class="fas fa-font text-danger"></i>
                            <span>Typography &amp; Price Colors</span>
                        </div>
                        <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseTypographyPrices" aria-expanded="true" aria-controls="collapseTypographyPrices" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseTypographyPrices">
                        <div class="p-3">
                            <div class="row g-2">
                                <div class="col-12 col-sm-4">
                                    <div class="color-control-wrapper">
                                        <label>Section Heading</label>
                                        <div class="color-input-container">
                                            <input type="color" class="color-picker-widget" value="{{ $colors['color_section_title'] ?? '#0f172a' }}">
                                            <input type="text" name="color_section_title" class="color-hex-input" value="{{ $colors['color_section_title'] ?? '#0f172a' }}" placeholder="#0f172a">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="color-control-wrapper">
                                        <label>Product Title</label>
                                        <div class="color-input-container">
                                            <input type="color" class="color-picker-widget" value="{{ $colors['color_product_title'] ?? '#1e293b' }}">
                                            <input type="text" name="color_product_title" class="color-hex-input" value="{{ $colors['color_product_title'] ?? '#1e293b' }}" placeholder="#1e293b">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="color-control-wrapper">
                                        <label>Product Price</label>
                                        <div class="color-input-container">
                                            <input type="color" class="color-picker-widget" value="{{ $colors['color_product_price'] ?? '#2563eb' }}">
                                            <input type="text" name="color_product_price" class="color-hex-input" value="{{ $colors['color_product_price'] ?? '#2563eb' }}" placeholder="#2563eb">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 8. Footer Styling (COLLAPSIBLE) -->
            <div class="col-12 col-xl-6">
                <div class="theme-section-card">
                    <div class="theme-section-header d-flex justify-content-between align-items-center">
                        <div class="section-title-text">
                            <i class="fas fa-shoe-prints text-secondary"></i>
                            <span>Footer Styling</span>
                        </div>
                        <button type="button" class="collapse-toggle-btn shadow-xs" data-bs-toggle="collapse" data-bs-target="#collapseFooterStyle" aria-expanded="true" aria-controls="collapseFooterStyle" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseFooterStyle">
                        <div class="p-3">
                            <div class="row g-2">
                                <div class="col-12 col-sm-6">
                                    <div class="color-control-wrapper">
                                        <label>Footer Background</label>
                                        <div class="color-input-container">
                                            <input type="color" class="color-picker-widget" value="{{ $colors['color_footer_bg'] ?? '#0f172a' }}">
                                            <input type="text" name="color_footer_bg" class="color-hex-input" value="{{ $colors['color_footer_bg'] ?? '#0f172a' }}" placeholder="#0f172a">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="color-control-wrapper">
                                        <label>Footer Text Color</label>
                                        <div class="color-input-container">
                                            <input type="color" class="color-picker-widget" value="{{ $colors['color_footer_text'] ?? '#94a3b8' }}">
                                            <input type="text" name="color_footer_text" class="color-hex-input" value="{{ $colors['color_footer_text'] ?? '#94a3b8' }}" placeholder="#94a3b8">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- 3. Bottom Action Bar -->
        <div class="bottom-action-card mb-3" style="border-radius: 5px;">
            <button type="button" class="btn btn-outline-danger btn-compact shadow-xs fw-bold px-3" onclick="triggerResetColorsModal()">
                <i class="fas fa-rotate-left me-1"></i>
                <span>Reset Default Colors</span>
            </button>
            <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-4" style="min-height: 35px; font-size: 12px;">
                <i class="fas fa-check-circle me-1"></i>
                <span>Save Color Changes</span>
            </button>
        </div>
    </form>
</div>

<!-- Modal: Custom Enterprise Reset Defaults Confirmation Modal -->
<div class="modal fade colors-modal-scope" id="colorsResetConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2 text-danger">
                    <i class="fas fa-triangle-exclamation fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Restore Default Colors?</h6>
                <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.45;">
                    Are you sure you want to revert all custom buttons, headers, backgrounds and text colors to system defaults?
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-compact shadow-xs fw-bold" id="btnExecuteResetColors">Yes, Restore</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Two-Way Sync between color picker widget and HEX text input
    document.querySelectorAll('.color-input-container').forEach(container => {
        const picker = container.querySelector('.color-picker-widget');
        const textInput = container.querySelector('.color-hex-input');

        if (picker && textInput) {
            // Picker to text
            picker.addEventListener('input', function() {
                textInput.value = this.value.toLowerCase();
            });

            // Text to picker
            textInput.addEventListener('input', function() {
                let val = this.value.trim();
                if (!val.startsWith('#') && val.length > 0) {
                    val = '#' + val;
                }
                const isValidHex = /^#([0-9A-F]{3}){1,2}$/i.test(val);
                if (isValidHex) {
                    let finalHex = val;
                    if (val.length === 4) {
                        finalHex = '#' + val[1]+val[1] + val[2]+val[2] + val[3]+val[3];
                    }
                    picker.value = finalHex.toLowerCase();
                }
            });

            // Format on blur
            textInput.addEventListener('blur', function() {
                let val = this.value.trim();
                if (!val.startsWith('#') && val.length > 0) {
                    val = '#' + val;
                }
                const isValidHex = /^#([0-9A-F]{3}){1,2}$/i.test(val);
                if (isValidHex) {
                    if (val.length === 4) {
                        val = '#' + val[1]+val[1] + val[2]+val[2] + val[3]+val[3];
                    }
                    this.value = val.toLowerCase();
                } else {
                    this.value = picker.value;
                }
            });
        }
    });
});

// Toggle All Color Sections (Expand All / Collapse All)
let allSectionsExpanded = true;
function toggleAllColorSections() {
    const collapses = document.querySelectorAll('#themeColorsRoot .theme-section-card .collapse');
    const toggleBtns = document.querySelectorAll('#themeColorsRoot .theme-section-card .collapse-toggle-btn');
    const btnText = document.getElementById('expandAllBtnText');

    allSectionsExpanded = !allSectionsExpanded;

    collapses.forEach(col => {
        if (allSectionsExpanded) {
            bootstrap.Collapse.getOrCreateInstance(col, { toggle: false }).show();
        } else {
            bootstrap.Collapse.getOrCreateInstance(col, { toggle: false }).hide();
        }
    });

    toggleBtns.forEach(btn => {
        btn.setAttribute('aria-expanded', allSectionsExpanded ? 'true' : 'false');
    });

    if (btnText) {
        btnText.innerText = allSectionsExpanded ? 'Collapse All' : 'Expand All';
    }
}

// Custom Modal Reset Defaults Handler
function triggerResetColorsModal() {
    const modalEl = document.getElementById('colorsResetConfirmModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

document.getElementById('btnExecuteResetColors').addEventListener('click', function() {
    document.getElementById('reset_defaults_hidden_input').value = '1';
    document.getElementById('themeColorsForm').submit();
});
</script>
@endsection