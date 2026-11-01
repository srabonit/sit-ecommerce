@extends('layouts.admin')

@section('title', 'Product Card Settings - ' . config('app.name'))

@section('admin_content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
    #productCardSettingsRoot,
    .pc-modal-scope {
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        letter-spacing: -0.01em;
        color: #1e293b;
    }

    #productCardSettingsRoot i,
    #productCardSettingsRoot .fa,
    #productCardSettingsRoot .fas,
    #productCardSettingsRoot .far,
    #productCardSettingsRoot .fab,
    #productCardSettingsRoot .fa-solid,
    #productCardSettingsRoot .fa-regular,
    .pc-modal-scope i,
    .pc-modal-scope .fa,
    .pc-modal-scope .fa-solid,
    .pc-modal-scope .fa-regular {
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome" !important;
        font-style: normal;
        font-weight: 900;
        line-height: 1;
        letter-spacing: normal !important;
        display: inline-block;
    }

    #productCardSettingsRoot .far,
    .pc-modal-scope .far {
        font-weight: 400 !important;
    }

    #productCardSettingsRoot .btn,
    #productCardSettingsRoot .btn-sm,
    #productCardSettingsRoot button,
    #productCardSettingsRoot .form-control,
    #productCardSettingsRoot .form-select,
    #productCardSettingsRoot .badge,
    #productCardSettingsRoot .alert,
    #productCardSettingsRoot .card,
    #productCardSettingsRoot .settings-header-card,
    #productCardSettingsRoot .settings-panel-card,
    #productCardSettingsRoot .status-kpi-pill,
    #productCardSettingsRoot .feature-switch-card,
    #productCardSettingsRoot .collapse-toggle-btn {
        border-radius: 5px !important;
    }

    #productCardSettingsRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #productCardSettingsRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    .bn-digit,
    .font-tabular {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
    }

    .btn-compact {
        font-size: 11px !important;
        padding: 6px 14px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    .btn-primary {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
    }

    .btn-primary:hover {
        background-color: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
        color: #ffffff !important;
    }

    .btn-outline-secondary {
        background-color: #ffffff !important;
        border-color: #cbd5e1 !important;
        color: #334155 !important;
    }

    .btn-outline-secondary:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }

    .settings-header-card {
        background: #ffffff;
        color: #0f172a;
        padding: 14px 18px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 14px;
    }

    .settings-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 14px;
        overflow: hidden;
        border-radius: 6px !important;
    }

    .settings-panel-header {
        padding: 11px 16px;
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
    }

    .section-title-text {
        font-size: 12.5px;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .status-kpi-pill {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 10px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        height: 100%;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
        border-radius: 6px !important;
    }

    .feature-switch-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 12px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        transition: all 0.15s ease;
        height: 100%;
        border-radius: 6px !important;
    }

    .feature-switch-card:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }

    .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }

    #productCardSettingsRoot .form-control,
    #productCardSettingsRoot .form-select {
        min-height: 34px;
        font-size: 12px;
        font-weight: 500;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        color: #0f172a;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    #productCardSettingsRoot .form-control:focus,
    #productCardSettingsRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

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
        cursor: pointer;
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

    .form-switch .form-check-input {
        width: 36px;
        height: 18px;
        cursor: pointer;
        border-color: #cbd5e1;
        border-radius: 20px !important;
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }

    .form-switch .form-check-input:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    .form-check-label {
        font-size: 12px;
        font-weight: 600;
        color: #1e293b;
        cursor: pointer;
        user-select: none;
    }

    .minimal-modal-card {
        background: #ffffff !important;
        border: 1px solid #f1f5f9 !important;
        border-radius: 12px !important;
        box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.14), 0 0 1px 1px rgba(15, 23, 42, 0.04) !important;
        padding: 24px 26px !important;
        text-align: left !important;
    }

    .minimal-modal-title {
        font-size: 16px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        margin-bottom: 6px !important;
        letter-spacing: -0.02em !important;
    }

    .minimal-modal-subtext {
        font-size: 13px !important;
        color: #64748b !important;
        margin-bottom: 20px !important;
        line-height: 1.45 !important;
    }

    .btn-minimal-action {
        background-color: #2563eb !important;
        border: none !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        padding: 7px 22px !important;
        border-radius: 7px !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.28) !important;
        transition: all 0.15s ease !important;
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .btn-minimal-action:hover {
        background-color: #1d4ed8 !important;
        box-shadow: 0 6px 14px rgba(37, 99, 235, 0.38) !important;
        color: #ffffff !important;
    }

    @media (max-width: 767.98px) {
        .settings-header-card {
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

        .settings-panel-header {
            padding: 10px 12px;
        }

        .minimal-modal-card {
            padding: 20px !important;
        }
    }
</style>

@php
    $settings = is_array($settings ?? null)
        ? $settings
        : (json_decode($settings ?? '[]', true) ?: []);

    $cardActiveCount = 0;

    $cardCheckKeys = [
        'card_show_price_decimal',
        'card_show_review',
        'card_show_wishlist',
        'card_show_sale_badge',
        'card_enable_quick_view',
        'card_show_stockout_btn'
    ];

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

<div class="container-fluid px-2 px-md-4 py-2" id="productCardSettingsRoot">

    {{-- Header --}}
    <div class="settings-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 header-top-wrap">
        <div>
             <h5 class="fw-bold mb-0 d-flex align-items-center gap-2 text-dark" style="font-size: 18px;">
                <i class="fas fa-th-large text-primary"></i>
                <span>Product Card Settings</span>
            </h5>

            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                Manage product cards, badges, hover effects, and reviews.
            </small>
        </div>

        <div class="top-action-btns d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-compact shadow-xs" onclick="toggleAllCardSections()">
                <i class="fas fa-arrows-up-down me-1"></i>
                <span id="expandAllCardBtnText">Collapse All</span>
            </button>

            <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold px-3" onclick="document.getElementById('productCardSettingsForm').submit()">
                <i class="fas fa-check-circle me-1"></i>
                <span>Save Settings</span>
            </button>
        </div>
    </div>

    {{-- Summary --}}
    <div class="row g-2 mb-3">
        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 11px;">Active Card Features</small>
                    <strong class="text-primary d-block bn-digit font-tabular" style="font-size: 13.5px;">
                        {{ $cardActiveCount }} / 6 Enabled
                    </strong>
                </div>
                <div class="text-primary fs-5"><i class="fas fa-tags"></i></div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 11px;">Hover Effect</small>
                    <strong class="text-dark d-block text-truncate" style="font-size: 13.5px;">
                        {{ $hoverLabel }}
                    </strong>
                </div>
                <div class="text-warning fs-5"><i class="fas fa-wand-magic-sparkles"></i></div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="status-kpi-pill">
                <div class="min-w-0">
                    <small class="text-muted d-block fw-semibold text-truncate" style="font-size: 11px;">Product Reviews</small>
                    <strong class="text-{{ $detailReviewsOn ? 'success' : 'secondary' }} d-block" style="font-size: 13.5px;">
                        {{ $detailReviewsOn ? 'Enabled' : 'Hidden' }}
                    </strong>
                </div>
                <div class="text-{{ $detailReviewsOn ? 'success' : 'secondary' }} fs-5">
                    <i class="fas fa-star"></i>
                </div>
            </div>
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

    <form action="{{ route('admin.theme.setting.product_card.update') }}" method="POST" id="productCardSettingsForm">
        @csrf

        {{-- Section 1: Product Cards --}}
        <div class="settings-panel-card">
            <div class="settings-panel-header d-flex justify-content-between align-items-center">
                <div class="section-title-text">
                    <i class="fas fa-th-large text-primary"></i>
                    <span>Product Card Controls</span>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-uppercase" style="font-size: 9px; font-weight: 700;">
                        Card Features
                    </span>

                    <button type="button" class="collapse-toggle-btn shadow-xs"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseCardControls"
                            aria-expanded="true"
                            aria-controls="collapseCardControls"
                            title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseCardControls">
                <div class="p-3 bg-light bg-opacity-25">
                    <div class="row g-2">

                        {{-- Price Decimal --}}
                        <div class="col-12 col-md-6">
                            <div class="feature-switch-card">
                                <div class="pe-2">
                                    <label class="form-check-label d-block text-dark" for="sw_price_decimal" style="font-size: 12px;">
                                        Show Price Decimals
                                    </label>
                                    <small class="text-muted d-block" style="font-size: 11px;">
                                        Example: ৳500.00 instead of ৳500.
                                    </small>
                                </div>

                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox"
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
                                <div class="pe-2">
                                    <label class="form-check-label d-block text-dark" for="sw_card_review" style="font-size: 12px;">
                                        Show Star Ratings
                                    </label>
                                    <small class="text-muted d-block" style="font-size: 11px;">
                                        Display ratings and review counts.
                                    </small>
                                </div>

                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox"
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
                                <div class="pe-2">
                                    <label class="form-check-label d-block text-dark" for="sw_card_wishlist" style="font-size: 12px;">
                                        Wishlist Button
                                    </label>
                                    <small class="text-muted d-block" style="font-size: 11px;">
                                        Let shoppers save products.
                                    </small>
                                </div>

                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox"
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
                                <div class="pe-2">
                                    <label class="form-check-label d-block text-dark" for="sw_quick_view" style="font-size: 12px;">
                                        Quick View
                                    </label>
                                    <small class="text-muted d-block" style="font-size: 11px;">
                                        Preview products without reloading.
                                    </small>
                                </div>

                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox"
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
                                <div class="pe-2">
                                    <label class="form-check-label d-block text-dark" for="sw_stockout_btn" style="font-size: 12px;">
                                        Show Stock Out Label
                                    </label>
                                    <small class="text-muted d-block" style="font-size: 11px;">
                                        Replace purchase buttons when unavailable.
                                    </small>
                                </div>

                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox"
                                           name="card_show_stockout_btn"
                                           id="sw_stockout_btn"
                                           value="1"
                                           {{ ($settings['card_show_stockout_btn'] ?? '1') == '1' ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>

                        {{-- Sale Badge --}}
                        <div class="col-12 col-md-6">
                            <div class="feature-switch-card flex-column align-items-stretch justify-content-between">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <label class="form-check-label d-block text-dark" for="sw_sale_badge" style="font-size: 12px;">
                                            Discount Badge
                                        </label>
                                        <small class="text-muted" style="font-size: 11px;">
                                            Highlight sale products.
                                        </small>
                                    </div>

                                    <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                        <input class="form-check-input ms-0" type="checkbox"
                                               name="card_show_sale_badge"
                                               id="sw_sale_badge"
                                               value="1"
                                               {{ ($settings['card_show_sale_badge'] ?? '1') == '1' ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label mb-1" style="font-size: 11px;">Badge Text</label>
                                    <input type="text"
                                           name="card_sale_badge_text"
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

        {{-- Section 2: Hover Effects --}}
        <div class="settings-panel-card">
            <div class="settings-panel-header d-flex justify-content-between align-items-center">
                <div class="section-title-text">
                    <i class="fas fa-wand-magic-sparkles text-warning"></i>
                    <span>Hover Effects</span>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle text-uppercase" style="font-size: 9px; font-weight: 700;">
                        Animation
                    </span>

                    <button type="button" class="collapse-toggle-btn shadow-xs"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseHoverAnimation"
                            aria-expanded="true"
                            aria-controls="collapseHoverAnimation"
                            title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseHoverAnimation">
                <div class="p-3 bg-light bg-opacity-25">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Hover Animation</label>

                            <select name="card_hover_style" class="form-select">
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
                            <label class="form-label text-muted">How It Works</label>

                            <p class="text-muted small mb-0" style="font-size: 11.5px; line-height: 1.45;">
                                Gallery effects use the second product image on hover.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 3: Reviews --}}
        <div class="settings-panel-card">
            <div class="settings-panel-header d-flex justify-content-between align-items-center">
                <div class="section-title-text">
                    <i class="fas fa-star text-warning"></i>
                    <span>Product Review Settings</span>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success border border-success-subtle text-uppercase" style="font-size: 9px; font-weight: 700;">
                        Reviews
                    </span>

                    <button type="button" class="collapse-toggle-btn shadow-xs"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseDetailReviews"
                            aria-expanded="true"
                            aria-controls="collapseDetailReviews"
                            title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="collapseDetailReviews">
                <div class="p-3 bg-light bg-opacity-25">
                    <div class="row g-2">

                        {{-- Detail Reviews --}}
                        <div class="col-12 col-md-6">
                            <div class="feature-switch-card">
                                <div class="pe-2">
                                    <label class="form-check-label d-block text-dark" for="sw_detail_reviews" style="font-size: 12px;">
                                        Show Product Reviews
                                    </label>

                                    <small class="text-muted d-block" style="font-size: 11px;">
                                        Display ratings and reviews on product pages.
                                    </small>
                                </div>

                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox"
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
                                <div class="pe-2">
                                    <label class="form-check-label d-block text-dark" for="sw_allow_review" style="font-size: 12px;">
                                        Verified Buyers Only
                                    </label>

                                    <small class="text-muted d-block" style="font-size: 11px;">
                                        Only delivered customers can submit reviews.
                                    </small>
                                </div>

                                <div class="form-check form-switch m-0 ps-0 flex-shrink-0">
                                    <input class="form-check-input ms-0" type="checkbox"
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

        {{-- Save --}}
        <div class="d-flex justify-content-end pt-2 pb-3">
            <button type="submit"
                    class="btn btn-primary btn-compact shadow-xs fw-bold px-4 w-100 w-md-auto"
                    style="min-height: 36px; font-size: 12px;">
                <i class="fas fa-check-circle me-1"></i>
                Save Changes
            </button>
        </div>

    </form>
</div>

{{-- Notice Modal --}}
<div class="modal fade pc-modal-scope"
     id="productCardNoticeModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 108500;">

    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content minimal-modal-card">
            <h6 class="minimal-modal-title" id="productCardNoticeTitle">Notification</h6>

            <p class="minimal-modal-subtext"
               id="productCardNoticeMessage"
               style="margin-bottom: 16px !important;"></p>

            <div class="d-flex align-items-center">
                <button type="button" class="btn-minimal-action" data-bs-dismiss="modal">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Notice Modal
function showProductCardModalNotice(message, title = 'Notice') {
    const titleEl = document.getElementById('productCardNoticeTitle');
    const msgEl = document.getElementById('productCardNoticeMessage');

    if (titleEl) titleEl.innerText = title;
    if (msgEl) msgEl.innerText = message;

    const modalEl = document.getElementById('productCardNoticeModal');

    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

// Expand / Collapse All Sections
let allCardSectionsExpanded = true;

function toggleAllCardSections() {
    const collapses = document.querySelectorAll(
        '#productCardSettingsRoot .settings-panel-card .collapse'
    );

    const toggleBtns = document.querySelectorAll(
        '#productCardSettingsRoot .settings-panel-card .collapse-toggle-btn'
    );

    const btnText = document.getElementById('expandAllCardBtnText');

    allCardSectionsExpanded = !allCardSectionsExpanded;

    if (!window.bootstrap) return;

    collapses.forEach(col => {
        if (allCardSectionsExpanded) {
            bootstrap.Collapse.getOrCreateInstance(col, {
                toggle: false
            }).show();
        } else {
            bootstrap.Collapse.getOrCreateInstance(col, {
                toggle: false
            }).hide();
        }
    });

    toggleBtns.forEach(btn => {
        btn.setAttribute(
            'aria-expanded',
            allCardSectionsExpanded ? 'true' : 'false'
        );
    });

    if (btnText) {
        btnText.innerText = allCardSectionsExpanded
            ? 'Collapse All'
            : 'Expand All';
    }
}
</script>
@endsection