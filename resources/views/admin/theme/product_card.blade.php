@extends('layouts.admin')

@section('title', 'Product Card Settings - ' . config('app.name'))

@section('admin_content')

@php
    $settings = is_array($settings ?? null)
        ? $settings
        : (json_decode($settings ?? '[]', true) ?: []);

    $cardCheckKeys = [
        'card_show_price_decimal',
        'card_show_review',
        'card_show_wishlist',
        'card_show_sale_badge',
        'card_enable_quick_view',
        'card_show_stockout_btn'
    ];

    $cardActiveCount = 0;

    foreach ($cardCheckKeys as $ck) {
        if (($settings[$ck] ?? '1') == '1') {
            $cardActiveCount++;
        }
    }

    $currentHover = $settings['card_hover_style'] ?? 'gallery_flip';

    $hoverLabel = match($currentHover) {
        'none' => 'No Animation',
        'zoom' => 'Smooth Zoom',
        'slide_up' => 'Slide Up',
        default => 'Gallery Flip',
    };

    $detailReviewsOn = ($settings['detail_show_reviews'] ?? '1') == '1';
@endphp

<style>
    /* =========================================
       PRODUCT CARD SETTINGS — DESIGN SYSTEM
    ========================================= */

    #productCardSettingsRoot,
    #productCardNoticeModal {
        --pc-primary: var(--bs-primary, #2563eb);
        --pc-primary-rgb: var(--bs-primary-rgb, 37, 99, 235);
        --pc-text: var(--bs-body-color, #1e293b);
        --pc-muted: var(--bs-secondary-color, #64748b);
        --pc-border: var(--bs-border-color, #e2e8f0);
        --pc-surface: var(--bs-body-bg, #ffffff);
        --pc-soft: var(--bs-tertiary-bg, #f8fafc);

        color: var(--pc-text);
        font-family: inherit;
        letter-spacing: -0.01em;
    }

    #productCardSettingsRoot *,
    #productCardNoticeModal * {
        box-sizing: border-box;
    }

    #productCardSettingsRoot .btn,
    #productCardSettingsRoot button,
    #productCardSettingsRoot .form-control,
    #productCardSettingsRoot .form-select,
    #productCardSettingsRoot .badge,
    #productCardSettingsRoot .alert,
    #productCardSettingsRoot .settings-header-card,
    #productCardSettingsRoot .settings-panel-card,
    #productCardSettingsRoot .status-kpi-pill,
    #productCardSettingsRoot .feature-switch-card,
    #productCardSettingsRoot .collapse-toggle-btn,
    #productCardNoticeModal .modal-content,
    #productCardNoticeModal .btn-minimal-action {
        border-radius: 5px !important;
    }

    /* Header */

    #productCardSettingsRoot .settings-header-card {
        background: var(--pc-surface);
        border: 1px solid var(--pc-border);
        padding: 14px 18px;
        margin-bottom: 14px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.035);
    }

    #productCardSettingsRoot .page-heading {
        font-size: 17px;
        font-weight: 750;
        line-height: 1.35;
        color: var(--pc-text);
        letter-spacing: -0.45px;
    }

    #productCardSettingsRoot .page-description {
        font-size: 11.5px;
        line-height: 1.5;
        color: var(--pc-muted);
    }

    /* Buttons */

    #productCardSettingsRoot .btn-compact {
        min-height: 34px;
        padding: 6px 13px;
        font-size: 11px;
        line-height: 1.35;
        font-weight: 650;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        white-space: nowrap;
        transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease;
    }

    #productCardSettingsRoot .btn-primary {
        background: var(--pc-primary);
        border-color: var(--pc-primary);
        color: #fff;
    }

    #productCardSettingsRoot .btn-primary:hover {
        background: var(--bs-primary-text-emphasis, #1d4ed8);
        border-color: var(--bs-primary-text-emphasis, #1d4ed8);
        color: #fff;
    }

    #productCardSettingsRoot .btn-outline-secondary {
        background: var(--pc-surface);
        border-color: var(--pc-border);
        color: var(--pc-text);
    }

    #productCardSettingsRoot .btn-outline-secondary:hover {
        background: var(--pc-soft);
        border-color: var(--pc-muted);
        color: var(--pc-text);
    }

    /* Summary Cards */

    #productCardSettingsRoot .status-kpi-pill {
        background: var(--pc-surface);
        border: 1px solid var(--pc-border);
        padding: 11px 14px;
        min-height: 68px;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.025);
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #productCardSettingsRoot .status-kpi-pill:hover {
        border-color: rgba(var(--pc-primary-rgb), .35);
        box-shadow: 0 2px 7px rgba(15, 23, 42, .045);
    }

    #productCardSettingsRoot .kpi-label {
        font-size: 10.5px;
        font-weight: 600;
        color: var(--pc-muted);
        margin-bottom: 4px;
    }

    #productCardSettingsRoot .kpi-value {
        font-size: 13px;
        font-weight: 750;
        color: var(--pc-text);
        line-height: 1.35;
    }

    #productCardSettingsRoot .kpi-icon {
        width: 34px;
        height: 34px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        background: rgba(var(--pc-primary-rgb), .09);
        color: var(--pc-primary);
        font-size: 15px;
    }

    #productCardSettingsRoot .kpi-icon.warning {
        background: rgba(245, 158, 11, .10);
        color: #d97706;
    }

    #productCardSettingsRoot .kpi-icon.success {
        background: rgba(16, 185, 129, .10);
        color: #059669;
    }

    /* Section Panels */

    #productCardSettingsRoot .settings-panel-card {
        background: var(--pc-surface);
        border: 1px solid var(--pc-border);
        margin-bottom: 13px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
    }

    #productCardSettingsRoot .settings-panel-header {
        min-height: 48px;
        padding: 10px 15px;
        background: var(--pc-surface);
        border-bottom: 1px solid var(--pc-border);
    }

    #productCardSettingsRoot .section-title-text {
        font-size: 12px;
        font-weight: 750;
        color: var(--pc-text);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    #productCardSettingsRoot .section-title-icon {
        width: 27px;
        height: 27px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(var(--pc-primary-rgb), .09);
        color: var(--pc-primary);
        border-radius: 5px;
        font-size: 12px;
    }

    #productCardSettingsRoot .section-title-icon.warning {
        background: rgba(245, 158, 11, .10);
        color: #d97706;
    }

    #productCardSettingsRoot .section-title-icon.success {
        background: rgba(16, 185, 129, .10);
        color: #059669;
    }

    #productCardSettingsRoot .section-badge {
        font-size: 9px;
        font-weight: 750;
        padding: 4px 7px;
        letter-spacing: .35px;
    }

    #productCardSettingsRoot .section-body {
        padding: 14px;
        background: var(--pc-soft);
        background: color-mix(in srgb, var(--pc-soft) 55%, var(--pc-surface));
    }

    /* Feature Switch Cards */

    #productCardSettingsRoot .feature-switch-card {
        height: 100%;
        min-height: 68px;
        padding: 12px 13px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: var(--pc-surface);
        border: 1px solid var(--pc-border);
        transition: border-color .15s ease, background-color .15s ease;
    }

    #productCardSettingsRoot .feature-switch-card:hover {
        border-color: rgba(var(--pc-primary-rgb), .4);
        background: var(--pc-surface);
    }

    #productCardSettingsRoot .feature-title {
        font-size: 11.5px;
        font-weight: 700;
        line-height: 1.4;
        color: var(--pc-text);
        margin-bottom: 3px;
        cursor: pointer;
    }

    #productCardSettingsRoot .feature-description {
        font-size: 10.5px;
        color: var(--pc-muted);
        line-height: 1.45;
        margin: 0;
    }

    /* Form Controls */

    #productCardSettingsRoot .form-label {
        display: block;
        font-size: 10.5px;
        font-weight: 700;
        color: var(--pc-text);
        margin-bottom: 5px;
    }

    #productCardSettingsRoot .form-control,
    #productCardSettingsRoot .form-select {
        min-height: 35px;
        padding: 7px 10px;
        font-size: 11.5px;
        font-weight: 500;
        color: var(--pc-text);
        background-color: var(--pc-surface);
        border: 1px solid var(--pc-border);
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #productCardSettingsRoot .form-control:focus,
    #productCardSettingsRoot .form-select:focus {
        border-color: var(--pc-primary);
        box-shadow: 0 0 0 3px rgba(var(--pc-primary-rgb), .12);
        outline: none;
    }

    #productCardSettingsRoot .form-check-label {
        font-size: 11.5px;
        font-weight: 650;
        color: var(--pc-text);
        cursor: pointer;
        user-select: none;
    }

    #productCardSettingsRoot .form-switch .form-check-input {
        width: 35px;
        height: 18px;
        margin: 0;
        cursor: pointer;
        border: 1px solid var(--pc-border);
        border-radius: 20px !important;
        box-shadow: none;
        transition: background-color .15s ease, border-color .15s ease;
    }

    #productCardSettingsRoot .form-switch .form-check-input:checked {
        background-color: var(--pc-primary);
        border-color: var(--pc-primary);
    }

    #productCardSettingsRoot .form-switch .form-check-input:focus {
        box-shadow: 0 0 0 3px rgba(var(--pc-primary-rgb), .12);
    }

    /* Collapse Toggle */

    #productCardSettingsRoot .collapse-toggle-btn {
        width: 27px;
        height: 27px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: var(--pc-surface);
        border: 1px solid var(--pc-border);
        color: var(--pc-muted);
        cursor: pointer;
        transition: all .15s ease;
    }

    #productCardSettingsRoot .collapse-toggle-btn:hover {
        background: var(--pc-primary);
        border-color: var(--pc-primary);
        color: #fff;
    }

    #productCardSettingsRoot .collapse-toggle-btn .collapse-icon {
        font-size: 10px;
        transition: transform .2s ease;
    }

    #productCardSettingsRoot .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* Sale Badge Field */

    #productCardSettingsRoot .sale-badge-feature {
        flex-direction: column;
        align-items: stretch;
        justify-content: space-between;
    }

    #productCardSettingsRoot .sale-badge-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 11px;
    }

    /* Save Footer */

    #productCardSettingsRoot .settings-save-footer {
        display: flex;
        justify-content: flex-end;
        padding: 3px 0 14px;
    }

    #productCardSettingsRoot .settings-save-footer .btn {
        min-height: 37px;
        min-width: 145px;
        font-size: 11.5px;
    }

    /* Notice Modal */

    #productCardNoticeModal {
        z-index: 108500;
    }

    #productCardNoticeModal .modal-dialog {
        max-width: 380px;
    }

    #productCardNoticeModal .minimal-modal-card {
        padding: 22px 24px;
        background: var(--pc-surface);
        border: 1px solid var(--pc-border);
        box-shadow: 0 18px 40px rgba(15, 23, 42, .15);
    }

    #productCardNoticeModal .minimal-modal-title {
        font-size: 15px;
        font-weight: 750;
        color: var(--pc-text);
        margin-bottom: 7px;
    }

    #productCardNoticeModal .minimal-modal-subtext {
        font-size: 12px;
        color: var(--pc-muted);
        line-height: 1.55;
        margin-bottom: 17px;
    }

    #productCardNoticeModal .btn-minimal-action {
        min-height: 33px;
        padding: 7px 22px;
        background: var(--pc-primary);
        border: 1px solid var(--pc-primary);
        color: #fff;
        font-size: 11.5px;
        font-weight: 700;
        cursor: pointer;
        transition: background-color .15s ease;
    }

    #productCardNoticeModal .btn-minimal-action:hover {
        filter: brightness(.92);
    }

    /* Responsive */

    @media (max-width: 767.98px) {
        #productCardSettingsRoot {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #productCardSettingsRoot .settings-header-card {
            padding: 12px;
        }

        #productCardSettingsRoot .header-top-wrap {
            align-items: stretch !important;
        }

        #productCardSettingsRoot .page-heading {
            font-size: 16px;
        }

        #productCardSettingsRoot .top-action-btns {
            width: 100%;
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 7px !important;
        }

        #productCardSettingsRoot .top-action-btns .btn {
            width: 100%;
            min-width: 0;
            padding-left: 7px;
            padding-right: 7px;
        }

        #productCardSettingsRoot .settings-panel-header {
            padding: 9px 11px;
        }

        #productCardSettingsRoot .section-body {
            padding: 10px;
        }

        #productCardSettingsRoot .feature-switch-card {
            padding: 11px;
        }

        #productCardSettingsRoot .settings-save-footer .btn {
            width: 100%;
        }

        #productCardNoticeModal .minimal-modal-card {
            padding: 19px;
        }
    }

    @media (max-width: 380px) {
        #productCardSettingsRoot .section-title-text {
            font-size: 11px;
            gap: 5px;
        }

        #productCardSettingsRoot .section-badge {
            display: none;
        }

        #productCardSettingsRoot .status-kpi-pill {
            padding: 9px 11px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-2" id="productCardSettingsRoot">

    {{-- Page Header --}}
    <div class="settings-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 header-top-wrap">

        <div class="min-w-0">
            <h5 class="page-heading mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-th-large text-primary"></i>
                <span>Product Card Settings</span>
            </h5>

            <small class="page-description d-block mt-1">
                Manage product cards, badges, hover effects, and reviews.
            </small>
        </div>

        <div class="top-action-btns d-flex align-items-center gap-2 flex-shrink-0">

            <button type="button"
                    class="btn btn-outline-secondary btn-compact"
                    onclick="toggleAllCardSections()">
                <i class="fas fa-arrows-up-down"></i>
                <span id="expandAllCardBtnText">Collapse All</span>
            </button>

            <button type="button"
                    class="btn btn-primary btn-compact fw-bold"
                    onclick="document.getElementById('productCardSettingsForm').requestSubmit()">
                <i class="fas fa-check-circle"></i>
                <span>Save Settings</span>
            </button>

        </div>
    </div>

    {{-- Summary --}}
    <div class="row g-2 mb-3">

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <div class="kpi-label">Active Card Features</div>
                    <div class="kpi-value text-primary">
                        {{ $cardActiveCount }} / 6 Enabled
                    </div>
                </div>

                <div class="kpi-icon">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <div class="kpi-label">Hover Effect</div>
                    <div class="kpi-value text-truncate">
                        {{ $hoverLabel }}
                    </div>
                </div>

                <div class="kpi-icon warning">
                    <i class="fas fa-wand-magic-sparkles"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <div class="kpi-label">Product Reviews</div>

                    <div class="kpi-value text-{{ $detailReviewsOn ? 'success' : 'secondary' }}">
                        {{ $detailReviewsOn ? 'Enabled' : 'Hidden' }}
                    </div>
                </div>

                <div class="kpi-icon {{ $detailReviewsOn ? 'success' : '' }}">
                    <i class="fas fa-star"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm py-2 px-3 mb-3 d-flex align-items-center justify-content-between gap-2"
             role="alert">

            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <span class="fw-semibold small">{{ session('success') }}</span>
            </div>

            <button type="button"
                    class="btn-close shadow-none"
                    data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.theme.setting.product_card.update') }}"
          method="POST"
          id="productCardSettingsForm">

        @csrf

        {{-- =========================================
             SECTION 1: PRODUCT CARD CONTROLS
        ========================================== --}}
        <div class="settings-panel-card">

            <div class="settings-panel-header d-flex justify-content-between align-items-center gap-2">

                <div class="section-title-text">
                    <span class="section-title-icon">
                        <i class="fas fa-th-large"></i>
                    </span>
                    <span>Product Card Controls</span>
                </div>

                <div class="d-flex align-items-center gap-2">

                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle section-badge text-uppercase">
                        Card Features
                    </span>

                    <button type="button"
                            class="collapse-toggle-btn"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseCardControls"
                            aria-expanded="true"
                            aria-controls="collapseCardControls"
                            title="Toggle Section"
                            aria-label="Toggle Product Card Controls">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>

                </div>
            </div>

            <div class="collapse show" id="collapseCardControls">
                <div class="section-body">
                    <div class="row g-2">

                        {{-- Price Decimal --}}
                        <div class="col-12 col-md-6">
                            <div class="feature-switch-card">

                                <div class="pe-2 min-w-0">
                                    <label class="feature-title d-block" for="sw_price_decimal">
                                        Show Price Decimals
                                    </label>

                                    <p class="feature-description">
                                        Example: ৳500.00 instead of ৳500.
                                    </p>
                                </div>

                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="card_show_price_decimal"
                                           id="sw_price_decimal"
                                           value="1"
                                           {{ ($settings['card_show_price_decimal'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>

                            </div>
                        </div>

                        {{-- Card Reviews --}}
                        <div class="col-12 col-md-6">
                            <div class="feature-switch-card">

                                <div class="pe-2 min-w-0">
                                    <label class="feature-title d-block" for="sw_card_review">
                                        Show Star Ratings
                                    </label>

                                    <p class="feature-description">
                                        Display ratings and review counts.
                                    </p>
                                </div>

                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="card_show_review"
                                           id="sw_card_review"
                                           value="1"
                                           {{ ($settings['card_show_review'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>

                            </div>
                        </div>

                        {{-- Wishlist --}}
                        <div class="col-12 col-md-6">
                            <div class="feature-switch-card">

                                <div class="pe-2 min-w-0">
                                    <label class="feature-title d-block" for="sw_card_wishlist">
                                        Wishlist Button
                                    </label>

                                    <p class="feature-description">
                                        Let shoppers save products.
                                    </p>
                                </div>

                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="card_show_wishlist"
                                           id="sw_card_wishlist"
                                           value="1"
                                           {{ ($settings['card_show_wishlist'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>

                            </div>
                        </div>

                        {{-- Quick View --}}
                        <div class="col-12 col-md-6">
                            <div class="feature-switch-card">

                                <div class="pe-2 min-w-0">
                                    <label class="feature-title d-block" for="sw_quick_view">
                                        Quick View
                                    </label>

                                    <p class="feature-description">
                                        Preview products without reloading.
                                    </p>
                                </div>

                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="card_enable_quick_view"
                                           id="sw_quick_view"
                                           value="1"
                                           {{ ($settings['card_enable_quick_view'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>

                            </div>
                        </div>

                        {{-- Stock Out Button --}}
                        <div class="col-12 col-md-6">
                            <div class="feature-switch-card">

                                <div class="pe-2 min-w-0">
                                    <label class="feature-title d-block" for="sw_stockout_btn">
                                        Show Stock Out Label
                                    </label>

                                    <p class="feature-description">
                                        Replace purchase buttons when unavailable.
                                    </p>
                                </div>

                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="card_show_stockout_btn"
                                           id="sw_stockout_btn"
                                           value="1"
                                           {{ ($settings['card_show_stockout_btn'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>

                            </div>
                        </div>

                        {{-- Sale Badge --}}
                        <div class="col-12 col-md-6">
                            <div class="feature-switch-card sale-badge-feature">

                                <div class="sale-badge-top">
                                    <div class="min-w-0">
                                        <label class="feature-title d-block" for="sw_sale_badge">
                                            Discount Badge
                                        </label>

                                        <p class="feature-description">
                                            Highlight sale products.
                                        </p>
                                    </div>

                                    <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="card_show_sale_badge"
                                               id="sw_sale_badge"
                                               value="1"
                                               {{ ($settings['card_show_sale_badge'] ?? '1') == '1' ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label" for="card_sale_badge_text">
                                        Badge Text
                                    </label>

                                    <input type="text"
                                           name="card_sale_badge_text"
                                           id="card_sale_badge_text"
                                           class="form-control"
                                           value="{{ $settings['card_sale_badge_text'] ?? 'Sale!' }}"
                                           placeholder="Sale!">
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        {{-- =========================================
             SECTION 2: HOVER EFFECTS
        ========================================== --}}
        <div class="settings-panel-card">

            <div class="settings-panel-header d-flex justify-content-between align-items-center gap-2">

                <div class="section-title-text">
                    <span class="section-title-icon warning">
                        <i class="fas fa-wand-magic-sparkles"></i>
                    </span>
                    <span>Hover Effects</span>
                </div>

                <div class="d-flex align-items-center gap-2">

                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle section-badge text-uppercase">
                        Animation
                    </span>

                    <button type="button"
                            class="collapse-toggle-btn"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseHoverAnimation"
                            aria-expanded="true"
                            aria-controls="collapseHoverAnimation"
                            title="Toggle Section"
                            aria-label="Toggle Hover Effects">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>

                </div>
            </div>

            <div class="collapse show" id="collapseHoverAnimation">
                <div class="section-body">

                    <div class="row g-3 align-items-center">

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="card_hover_style">
                                Hover Animation
                            </label>

                            <select name="card_hover_style"
                                    id="card_hover_style"
                                    class="form-select">

                                <option value="none" {{ ($settings['card_hover_style'] ?? '') == 'none' ? 'selected' : '' }}>
                                    None
                                </option>

                                <option value="zoom" {{ ($settings['card_hover_style'] ?? '') == 'zoom' ? 'selected' : '' }}>
                                    Smooth Zoom
                                </option>

                                <option value="gallery_flip" {{ ($settings['card_hover_style'] ?? 'gallery_flip') == 'gallery_flip' ? 'selected' : '' }}>
                                    Gallery Flip
                                </option>

                                <option value="slide_up" {{ ($settings['card_hover_style'] ?? '') == 'slide_up' ? 'selected' : '' }}>
                                    Slide Up
                                </option>

                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="d-flex align-items-start gap-2">
                                <span class="text-primary mt-1">
                                    <i class="fas fa-circle-info"></i>
                                </span>

                                <div>
                                    <div class="form-label mb-1">How It Works</div>

                                    <p class="text-muted small mb-0" style="font-size: 11px; line-height: 1.55;">
                                        Gallery effects use the second product image on hover.
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

        </div>

        {{-- =========================================
             SECTION 3: PRODUCT REVIEWS
        ========================================== --}}
        <div class="settings-panel-card">

            <div class="settings-panel-header d-flex justify-content-between align-items-center gap-2">

                <div class="section-title-text">
                    <span class="section-title-icon success">
                        <i class="fas fa-star"></i>
                    </span>
                    <span>Product Review Settings</span>
                </div>

                <div class="d-flex align-items-center gap-2">

                    <span class="badge bg-success-subtle text-success border border-success-subtle section-badge text-uppercase">
                        Reviews
                    </span>

                    <button type="button"
                            class="collapse-toggle-btn"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseDetailReviews"
                            aria-expanded="true"
                            aria-controls="collapseDetailReviews"
                            title="Toggle Section"
                            aria-label="Toggle Product Review Settings">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>

                </div>
            </div>

            <div class="collapse show" id="collapseDetailReviews">
                <div class="section-body">

                    <div class="row g-2">

                        {{-- Detail Reviews --}}
                        <div class="col-12 col-md-6">
                            <div class="feature-switch-card">

                                <div class="pe-2 min-w-0">
                                    <label class="feature-title d-block" for="sw_detail_reviews">
                                        Show Product Reviews
                                    </label>

                                    <p class="feature-description">
                                        Display ratings and reviews on product pages.
                                    </p>
                                </div>

                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="detail_show_reviews"
                                           id="sw_detail_reviews"
                                           value="1"
                                           {{ ($settings['detail_show_reviews'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>

                            </div>
                        </div>

                        {{-- Verified Buyers --}}
                        <div class="col-12 col-md-6">
                            <div class="feature-switch-card">

                                <div class="pe-2 min-w-0">
                                    <label class="feature-title d-block" for="sw_allow_review">
                                        Verified Buyers Only
                                    </label>

                                    <p class="feature-description">
                                        Only delivered customers can submit reviews.
                                    </p>
                                </div>

                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="detail_allow_review"
                                           id="sw_allow_review"
                                           value="1"
                                           {{ ($settings['detail_allow_review'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>

                            </div>
                        </div>

                    </div>

                </div>
            </div>

        </div>

        {{-- Save Changes --}}
        <div class="settings-save-footer">
            <button type="submit"
                    class="btn btn-primary btn-compact fw-bold px-4">
                <i class="fas fa-check-circle"></i>
                Save Changes
            </button>
        </div>

    </form>
</div>

{{-- =========================================
     NOTICE MODAL
========================================== --}}
<div class="modal fade"
     id="productCardNoticeModal"
     tabindex="-1"
     aria-hidden="true"
     aria-labelledby="productCardNoticeTitle"
     style="z-index: 108500;">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content minimal-modal-card">

            <h6 class="minimal-modal-title" id="productCardNoticeTitle">
                Notification
            </h6>

            <p class="minimal-modal-subtext"
               id="productCardNoticeMessage"></p>

            <div class="d-flex align-items-center">
                <button type="button"
                        class="btn-minimal-action"
                        data-bs-dismiss="modal">
                    OK
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    /**
     * Product Card Notice Modal
     */
    function showProductCardModalNotice(message, title = 'Notice') {
        const titleEl = document.getElementById('productCardNoticeTitle');
        const msgEl = document.getElementById('productCardNoticeMessage');
        const modalEl = document.getElementById('productCardNoticeModal');

        if (titleEl) {
            titleEl.textContent = title;
        }

        if (msgEl) {
            msgEl.textContent = message;
        }

        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }


    /**
     * Expand / Collapse All Sections
     */
    let allCardSectionsExpanded = true;

    function toggleAllCardSections() {
        const root = document.getElementById('productCardSettingsRoot');

        if (!root) {
            return;
        }

        const collapses = root.querySelectorAll(
            '.settings-panel-card .collapse'
        );

        const toggleBtns = root.querySelectorAll(
            '.settings-panel-card .collapse-toggle-btn'
        );

        const btnText = document.getElementById('expandAllCardBtnText');

        if (!window.bootstrap) {
            return;
        }

        allCardSectionsExpanded = !allCardSectionsExpanded;

        collapses.forEach(col => {
            const collapseInstance = bootstrap.Collapse.getOrCreateInstance(
                col,
                { toggle: false }
            );

            if (allCardSectionsExpanded) {
                collapseInstance.show();
            } else {
                collapseInstance.hide();
            }
        });

        toggleBtns.forEach(btn => {
            btn.setAttribute(
                'aria-expanded',
                allCardSectionsExpanded ? 'true' : 'false'
            );
        });

        if (btnText) {
            btn.textContent = allCardSectionsExpanded
                ? 'Collapse All'
                : 'Expand All';
        }
    }


    /**
     * Keep Expand / Collapse All Button
     * Synchronized With Individual Sections
     */
    document.addEventListener('DOMContentLoaded', function () {
        const root = document.getElementById('productCardSettingsRoot');

        if (!root) {
            return;
        }

        const collapses = root.querySelectorAll(
            '.settings-panel-card .collapse'
        );

        collapses.forEach(collapseEl => {
            collapseEl.addEventListener('shown.bs.collapse', function () {
                updateCardSectionState();
            });

            collapseEl.addEventListener('hidden.bs.collapse', function () {
                updateCardSectionState();
            });
        });

        function updateCardSectionState() {
            const expandedCount = Array.from(collapses).filter(
                item => item.classList.contains('show')
            ).length;

            allCardSectionsExpanded = expandedCount === collapses.length;

            const btnText = document.getElementById('expandAllCardBtnText');

            if (btnText) {
                btnText.textContent = allCardSectionsExpanded
                    ? 'Collapse All'
                    : 'Expand All';
            }
        }
    });
</script>

@endsection