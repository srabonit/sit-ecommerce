
@php
    $pTitle = $product->title ?? ($product->name ?? 'Product #' . $product->id);$pSku   = $product->sku ?? ($product->product_code ?? 'NO-SKU');
    
    // Smart Price Resolution
    $salePrice = (float)($product->sale_price ?? ($product->offer_price ?? 0));
    $mrpPrice  = (float)($product->mrp ?? ($product->regular_price ?? 0));
    $pPrice    =$salePrice > 0 ? $salePrice : ($mrpPrice > 0 ? $mrpPrice : (float)($product->price ?? 0));
    
    $pImg = $product->thumbnail ?? ($product->image ?? null);

    // Fail-safe thumbnail resolver
    $resolveThumb = function($path,$fallback = 'https://placehold.co/60x60?text=Product') {
        if (empty($path)) return$fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path,$fallback);
        }
        if (function_exists('show_image')) return show_image($path,$fallback);
        if (filter_var($path, FILTER_VALIDATE_URL)) return$path;
        return asset(ltrim(str_replace(['public_html/', 'public/'], '', $path), '/'));
    };

    $thumbUrl = !empty($pImg) ? $resolveThumb($pImg) : 'https://placehold.co/60x60?text=Product';
@endphp

@extends('layouts.admin')

@section('title', 'Target Buyers - ' . $pTitle)

@section('admin_content')

<style>
    /* =========================================================
       KAIADMIN CLASSIC / WHITE THEME
       Product Target Buyers - Scoped Design System
    ========================================================= */

    #email-marketing-product-targeted-root {
        --target-primary: var(--bs-primary, #1572e8);
        --target-primary-soft: #eaf2ff;
        --target-secondary: #6c757d;
        --target-success: #31ce36;
        --target-success-soft: #e9f9eb;
        --target-warning: #ffad46;
        --target-danger: #f25961;
        --target-info: #48abf7;

        --target-body: #f5f7fd;
        --target-surface: #ffffff;
        --target-heading: #2a2f5b;
        --target-text: #495057;
        --target-muted: #8d9498;
        --target-border: #ebedf2;

        --target-radius: 5px;
        --target-shadow: 0 1px 3px rgba(0, 0, 0, .035);

        color: var(--target-text);
        font-size: 13px;
        min-width: 0;
        width: 100%;
    }

    #email-marketing-product-targeted-root *,
    #email-marketing-product-targeted-root *::before,
    #email-marketing-product-targeted-root *::after {
        box-sizing: border-box;
    }

    /* Main Layout */

    #email-marketing-product-targeted-root .target-header-wrap {
        margin-bottom: 22px !important;
    }

    #email-marketing-product-targeted-root .target-page-title {
        color: var(--target-heading);
        font-size: clamp(17px, 1.5vw, 21px);
        font-weight: 700;
        letter-spacing: -.35px;
        line-height: 1.35;
    }

    #email-marketing-product-targeted-root .target-page-description {
        color: var(--target-muted);
        font-size: 12px;
        line-height: 1.6;
        margin-top: 5px;
    }

    #email-marketing-product-targeted-root .target-page-description strong {
        color: var(--target-heading);
        font-weight: 600;
    }

    /* Consistent Radius */

    #email-marketing-product-targeted-root .btn,
    #email-marketing-product-targeted-root button,
    #email-marketing-product-targeted-root .badge,
    #email-marketing-product-targeted-root .alert,
    #email-marketing-product-targeted-root .form-control,
    #email-marketing-product-targeted-root .form-select,
    #email-marketing-product-targeted-root .input-group-text,
    #email-marketing-product-targeted-root .card,
    #email-marketing-product-targeted-root .metric-card,
    #email-marketing-product-targeted-root .product-thumb-box,
    #email-marketing-product-targeted-root .collapse-toggle-btn,
    .target-modal-scope .modal-content,
    .target-modal-scope .btn {
        border-radius: var(--target-radius) !important;
    }

    #email-marketing-product-targeted-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #email-marketing-product-targeted-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Buttons */

    #email-marketing-product-targeted-root .btn-compact {
        min-height: 34px;
        padding: 7px 13px !important;
        font-size: 11.5px !important;
        font-weight: 600 !important;
        line-height: 1.35 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 3px;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #email-marketing-product-targeted-root .btn-primary {
        background-color: var(--target-primary);
        border-color: var(--target-primary);
    }

    #email-marketing-product-targeted-root .btn-primary:hover {
        filter: brightness(.94);
        transform: translateY(-1px);
    }

    #email-marketing-product-targeted-root .btn-outline-secondary {
        border-color: #dce0e5;
        color: #59636e;
    }

    #email-marketing-product-targeted-root .btn-outline-secondary:hover {
        background: #f5f7fa;
        color: var(--target-heading);
        border-color: #cbd2dc;
    }

    #email-marketing-product-targeted-root .btn:focus-visible,
    #email-marketing-product-targeted-root button:focus-visible {
        outline: 2px solid var(--target-primary);
        outline-offset: 2px;
    }

    /* Cards */

    #email-marketing-product-targeted-root .card {
        background: var(--target-surface);
        border: 1px solid var(--target-border) !important;
        box-shadow: var(--target-shadow);
        margin-bottom: 16px;
        min-width: 0;
    }

    #email-marketing-product-targeted-root .metric-card {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 17px;
        height: 100%;
        min-height: 132px;
        overflow: hidden;
        transition: box-shadow .2s ease, transform .2s ease;
    }

    #email-marketing-product-targeted-root .metric-card:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, .055);
        transform: translateY(-2px);
    }

    #email-marketing-product-targeted-root .metric-card-blue {
        background: #fff;
        border-left: 3px solid #1572e8 !important;
    }

    #email-marketing-product-targeted-root .metric-card-green {
        background: #fff;
        border-left: 3px solid #31ce36 !important;
    }

    #email-marketing-product-targeted-root .metric-card-dark {
        background: #2a2f5b;
        color: #fff;
        border: 1px solid #2a2f5b !important;
    }

    #email-marketing-product-targeted-root .stat-label-title {
        font-size: 11.5px;
        font-weight: 700;
        color: var(--target-muted);
        letter-spacing: .25px;
        line-height: 1.4;
    }

    #email-marketing-product-targeted-root .stat-val-num {
        font-size: clamp(19px, 2vw, 24px);
        line-height: 1.3;
        font-weight: 700;
        letter-spacing: -.5px;
        color: var(--target-heading);
        overflow-wrap: anywhere;
    }

    #email-marketing-product-targeted-root .stat-sub-text {
        font-size: 10.5px;
        margin-top: 5px;
        line-height: 1.4;
    }

    #email-marketing-product-targeted-root .metric-icon-square {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 5px !important;
        font-size: 14px;
    }

    #email-marketing-product-targeted-root .metric-card-dark .stat-label-title {
        color: rgba(255,255,255,.7);
    }

    /* Product Thumbnail */

    #email-marketing-product-targeted-root .product-thumb-box {
        width: 54px;
        height: 54px;
        background: #fff;
        border: 1px solid var(--target-border);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        position: relative;
    }

    #email-marketing-product-targeted-root .product-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #email-marketing-product-targeted-root .product-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        background: rgba(42, 47, 91, .55);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity .18s ease;
    }

    #email-marketing-product-targeted-root .product-thumb-box:hover .product-thumb-hover-overlay {
        opacity: 1;
    }

    /* Section Headers */

    #email-marketing-product-targeted-root .section-header-wrap {
        min-height: 49px;
        padding: 12px 16px;
        background: #fff;
        border-bottom: 1px solid var(--target-border);
    }

    #email-marketing-product-targeted-root .section-header-wrap > div,
    #email-marketing-product-targeted-root .section-header-wrap > span {
        min-width: 0;
    }

    #email-marketing-product-targeted-root .section-heading {
        color: var(--target-heading);
        font-size: 13px;
        font-weight: 700;
        line-height: 1.4;
    }

    /* Collapse Button */

    #email-marketing-product-targeted-root .collapse-toggle-btn {
        width: 29px;
        height: 29px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: #fff;
        border: 1px solid var(--target-border);
        color: #737b85;
        transition: all .2s ease;
    }

    #email-marketing-product-targeted-root .collapse-toggle-btn:hover {
        background: var(--target-primary);
        border-color: var(--target-primary);
        color: #fff;
    }

    #email-marketing-product-targeted-root .collapse-icon {
        font-size: 10px;
        transition: transform .25s ease;
    }

    #email-marketing-product-targeted-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* Forms */

    #email-marketing-product-targeted-root .form-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--target-heading);
        margin-bottom: 6px;
        display: block;
    }

    #email-marketing-product-targeted-root .form-control,
    #email-marketing-product-targeted-root .form-select {
        min-height: 39px;
        font-size: 12px;
        border: 1px solid #e3e6ed;
        padding: 8px 11px;
        color: #495057;
        background-color: #fff;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #email-marketing-product-targeted-root textarea.form-control {
        min-height: 72px;
        resize: vertical;
        line-height: 1.55;
    }

    #email-marketing-product-targeted-root .form-control::placeholder {
        color: #adb5bd;
    }

    #email-marketing-product-targeted-root .form-control:focus,
    #email-marketing-product-targeted-root .form-select:focus {
        border-color: var(--target-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .09);
        outline: none;
    }

    #email-marketing-product-targeted-root .form-text {
        color: var(--target-muted);
        font-size: 10.5px;
        line-height: 1.5;
    }

    /* Buyer Search */

    #email-marketing-product-targeted-root .buyer-search-wrap {
        padding: 12px 15px;
        background: #fafbfc;
        border-bottom: 1px solid var(--target-border);
    }

    #email-marketing-product-targeted-root .buyer-search-wrap .input-group-text {
        background: #fff;
        border-color: #e3e6ed;
        color: #a1a8b3;
        padding: 0 12px;
    }

    #email-marketing-product-targeted-root .buyer-search-wrap .form-control {
        min-height: 36px;
        border-left: 0;
    }

    /* Buyer List */

    #email-marketing-product-targeted-root .buyer-list-scroll {
        max-height: 400px;
        overflow-y: auto;
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
    }

    #email-marketing-product-targeted-root .buyer-list-container {
        font-size: 12px;
        margin-bottom: 0;
    }

    #email-marketing-product-targeted-root .buyer-inbox-item {
        min-height: 48px;
        padding: 9px 15px;
        border-bottom: 1px solid #f0f1f4;
        transition: background .15s ease;
    }

    #email-marketing-product-targeted-root .buyer-inbox-item:last-child {
        border-bottom: 0;
    }

    #email-marketing-product-targeted-root .buyer-inbox-item:hover {
        background: #f8faff;
    }

    #email-marketing-product-targeted-root .buyer-email-text {
        overflow-wrap: anywhere;
        min-width: 0;
    }

    #email-marketing-product-targeted-root .btn-copy-email {
        width: 29px;
        height: 29px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        opacity: .75;
        transition: all .15s ease;
    }

    #email-marketing-product-targeted-root .buyer-inbox-item:hover .btn-copy-email {
        opacity: 1;
    }

    #email-marketing-product-targeted-root .buyer-status-badge {
        font-size: 9.5px;
        font-weight: 600;
        padding: 4px 7px;
    }

    /* Footer */

    #email-marketing-product-targeted-root .buyer-list-footer {
        padding: 11px 15px;
        background: #fafbfc;
        border-top: 1px solid var(--target-border);
    }

    /* Typography */

    #email-marketing-product-targeted-root .font-monospace,
    #email-marketing-product-targeted-root .bn-digit {
        font-weight: 600;
    }

    #email-marketing-product-targeted-root .text-muted {
        color: var(--target-muted) !important;
    }

    #email-marketing-product-targeted-root .min-w-0 {
        min-width: 0;
    }

    /* Modal */

    .target-modal-scope .modal-content {
        border: 1px solid #ebedf2;
        box-shadow: 0 12px 40px rgba(0, 0, 0, .13);
        overflow: hidden;
    }

    .target-modal-scope .modal-header {
        padding: 14px 16px 8px;
    }

    .target-modal-scope .modal-body {
        padding: 15px;
    }

    .target-modal-scope .modal-title {
        color: #2a2f5b;
        font-weight: 700;
    }

    .target-modal-scope .btn-close {
        font-size: 10px;
    }

    /* Scrollbar */

    #email-marketing-product-targeted-root .buyer-list-scroll::-webkit-scrollbar {
        width: 5px;
    }

    #email-marketing-product-targeted-root .buyer-list-scroll::-webkit-scrollbar-track {
        background: #f5f6f9;
    }

    #email-marketing-product-targeted-root .buyer-list-scroll::-webkit-scrollbar-thumb {
        background: #d5dae2;
        border-radius: 10px;
    }

    /* Tablet */

    @media (max-width: 1199.98px) {
        #email-marketing-product-targeted-root .metric-card {
            min-height: 125px;
        }
    }

    @media (max-width: 991.98px) {
        #email-marketing-product-targeted-root {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        #email-marketing-product-targeted-root .section-header-wrap {
            padding: 11px 13px;
        }
    }

    /* Mobile */

    @media (max-width: 767.98px) {
        #email-marketing-product-targeted-root .target-header-wrap {
            gap: 12px !important;
            margin-bottom: 16px !important;
        }

        #email-marketing-product-targeted-root .target-page-title {
            font-size: 17px;
        }

        #email-marketing-product-targeted-root .target-page-description {
            font-size: 11.5px;
        }

        #email-marketing-product-targeted-root .top-action-btns {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        #email-marketing-product-targeted-root .top-action-btns .btn {
            width: 100%;
            min-height: 38px;
            padding: 7px 5px !important;
            font-size: 10.5px !important;
            white-space: normal;
            text-align: center;
        }

        #email-marketing-product-targeted-root .metric-card {
            padding: 14px !important;
            min-height: 115px !important;
        }

        #email-marketing-product-targeted-root .stat-label-title {
            font-size: 10.5px;
        }

        #email-marketing-product-targeted-root .stat-val-num {
            font-size: 19px;
        }

        #email-marketing-product-targeted-root .stat-sub-text {
            font-size: 10px;
        }

        #email-marketing-product-targeted-root .metric-icon-square {
            width: 32px;
            height: 32px;
            font-size: 12px;
        }

        #email-marketing-product-targeted-root .card {
            margin-bottom: 12px;
        }

        #email-marketing-product-targeted-root .section-header-wrap {
            padding: 11px 12px;
        }

        #email-marketing-product-targeted-root .section-heading {
            font-size: 12px;
        }

        #email-marketing-product-targeted-root .segment-form-body {
            padding: 14px !important;
        }

        #email-marketing-product-targeted-root .buyer-list-scroll {
            max-height: 350px;
        }

        #email-marketing-product-targeted-root .buyer-inbox-item {
            padding: 10px 12px;
        }
    }

    /* Small Mobile */

    @media (max-width: 380px) {
        #email-marketing-product-targeted-root {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }

        #email-marketing-product-targeted-root .target-page-title {
            font-size: 15px;
        }

        #email-marketing-product-targeted-root .top-action-btns {
            grid-template-columns: 1fr;
        }

        #email-marketing-product-targeted-root .metric-card {
            padding: 12px !important;
        }

        #email-marketing-product-targeted-root .product-thumb-box {
            width: 45px;
            height: 45px;
        }

        #email-marketing-product-targeted-root .buyer-inbox-item {
            padding-left: 9px;
            padding-right: 9px;
        }

        #email-marketing-product-targeted-root .buyer-status-badge {
            font-size: 9px;
        }
    }

    /* Reduced Motion */

    @media (prefers-reduced-motion: reduce) {
        #email-marketing-product-targeted-root *,
        .target-modal-scope * {
            transition-duration: .01ms !important;
            animation-duration: .01ms !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-product-targeted-root">

    {{-- 1. Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 target-header-wrap">

        <div class="min-w-0">
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2 target-page-title">
                    <i class="fas fa-bullseye text-primary"></i>
                    <span>Target Previous Buyers</span>
                </h5>

                <span class="badge bg-light text-primary border font-monospace px-2 py-1" style="font-size: 10px;">
                    SKU: {{ $pSku }}
                </span>
            </div>

            <small class="d-block target-page-description">
                Create targeted audience segments and promotional campaigns for customers who purchased
                <strong>{{ $pTitle }}</strong>.
            </small>
        </div>

        <div class="d-flex gap-2 top-action-btns">
            <a href="{{ route('admin.email_marketing.product_marketing') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-arrow-left me-1"></i>
                <span>Back to Products</span>
            </a>

            <button type="button" class="btn btn-sm btn-outline-success btn-compact bg-white shadow-xs" onclick="exportBuyerEmailsCsv()">
                <i class="fas fa-file-csv text-success me-1"></i>
                <span>Export Buyers CSV</span>
            </button>
        </div>
    </div>

    {{-- 2. Product Summary & Audience Metrics --}}
    <div class="row g-3 mb-3">

        {{-- Selected Product --}}
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card metric-card metric-card-blue h-100 mb-0">

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="stat-label-title">Product Information</span>

                    <div class="metric-icon-square bg-primary-subtle text-primary">
                        <i class="fas fa-box"></i>
                    </div>
                </div>

                <div class="min-w-0">
                    <div class="d-flex align-items-center gap-3 mb-3">

                        <div class="product-thumb-box shadow-xs" onclick="zoomTargetProductImage('{{ $thumbUrl }}', '{{ addslashes($pTitle) }}')" title="Click to preview image">
                            <img src="{{ $thumbUrl }}" alt="{{ $pTitle }}" onerror="this.src='https://placehold.co/60x60?text=Product';">

                            <div class="product-thumb-hover-overlay">
                                <i class="fas fa-search-plus text-white" style="font-size: 12px;"></i>
                            </div>
                        </div>

                        <div class="min-w-0 flex-grow-1">
                            <h6 class="fw-bold mb-1 text-dark text-truncate" style="font-size: 13px; line-height: 1.4;" title="{{ $pTitle }}">
                                {{ $pTitle }}
                            </h6>

                            <small class="text-muted font-monospace d-block text-truncate" style="font-size: 10px;">
                                ID: #{{ $product->id }} | SKU: {{$pSku }}
                            </small>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                        <span class="text-muted small">Current Price</span>

                        <span class="fw-bold text-success font-monospace" style="font-size: 14px;">
                            ৳ {{ number_format($pPrice, 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Identified Buyers --}}
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card metric-card metric-card-green h-100 mb-0">

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="stat-label-title">Identified Product Buyers</span>

                    <div class="metric-icon-square bg-success-subtle text-success">
                        <i class="fas fa-users-viewfinder"></i>
                    </div>
                </div>

                <div class="min-w-0">
                    <h4 class="fw-bold my-0 stat-val-num">
                        {{ number_format($buyerEmails->count()) }}

                        <span class="fw-normal text-muted" style="font-size: 11px; letter-spacing: 0;">
                            Unique Inboxes
                        </span>
                    </h4>

                    <small class="text-success d-block stat-sub-text">
                        <i class="fas fa-circle-check me-1"></i>
                        Completed &amp; Delivered Orders
                    </small>
                </div>
            </div>
        </div>

        {{-- Retargeting Action --}}
        <div class="col-12 col-md-12 col-xl-4">
            <div class="card metric-card metric-card-dark h-100 mb-0">

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="stat-label-title">Automated Retargeting</span>

                    <div class="metric-icon-square bg-white bg-opacity-15 text-white">
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>

                <div class="min-w-0">
                    <strong class="text-white d-block mb-1" style="font-size: 13px;">
                        Create a Reusable Segment
                    </strong>

                    <p class="text-white text-opacity-75 small mb-3" style="font-size: 11px; line-height: 1.5;">
                        Save these buyers to send discount coupons, refill reminders, and product updates.
                    </p>

                    <button type="button" class="btn btn-warning btn-compact text-dark fw-bold w-100" onclick="document.getElementById('saveSegmentForm').submit()">
                        <i class="fas fa-layer-group me-1"></i>
                        <span>Save Audience Segment</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Main Working Area --}}
    <div class="row g-3">

        {{-- Left: Segment Configuration --}}
        <div class="col-12 col-xl-7">
            <div class="card shadow-sm mb-3">

                <div class="section-header-wrap d-flex justify-content-between align-items-center">

                    <span class="section-heading">
                        <i class="fas fa-sliders text-primary me-2"></i>
                        1. Configure Audience Segment
                    </span>

                    <button type="button" class="collapse-toggle-btn no-print" data-bs-toggle="collapse" data-bs-target="#collapseSegmentConfig" aria-expanded="true" aria-controls="collapseSegmentConfig" title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>

                <div class="collapse show" id="collapseSegmentConfig">
                    <div class="p-3 segment-form-body">

                        <form action="{{ route('admin.email_marketing.segments.store') }}" method="POST" id="saveSegmentForm">
                            @csrf

                            <input type="hidden" name="filter_criteria[purchased_product_id]" value="{{ $product->id }}">

                            <div class="mb-3">
                                <label class="form-label" for="segment_name">
                                    Segment Name <span class="text-danger">*</span>
                                </label>

                                <input type="text" name="name" id="segment_name" class="form-control" value="Buyers of {{ $pTitle }}" required>

                                <small class="form-text d-block mt-2">
                                    Use this segment when scheduling email campaigns or automations.
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="segment_type">
                                    Evaluation Type <span class="text-danger">*</span>
                                </label>

                                <select name="type" id="segment_type" class="form-select font-monospace shadow-none" required>
                                    <option value="dynamic" selected>Dynamic (Auto-updates when new customer orders are completed)</option>
                                    <option value="snapshot">Snapshot (Fixed to current {{ $buyerEmails->count() }} identified buyers)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="segment_description">
                                    Internal Notes &amp; Strategy
                                </label>

                                <textarea name="description" id="segment_description" rows="2" class="form-control shadow-none" style="font-size: 12px;">Targeted segment containing past customers who purchased {{ $pTitle }} (SKU: {{$pSku }}).</textarea>
                            </div>

                            <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                                <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3">
                                    <i class="fas fa-save me-1"></i>
                                    Create Segment
                                </button>

                                <a href="{{ route('admin.email_marketing.product_marketing') }}" class="btn btn-light border btn-compact shadow-xs text-secondary">
                                    <i class="fas fa-times me-1"></i>
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Buyer Email List --}}
        <div class="col-12 col-xl-5">
            <div class="card shadow-sm mb-3 overflow-hidden">

                <div class="section-header-wrap d-flex justify-content-between align-items-center">

                    <div class="min-w-0">
                        <span class="section-heading">
                            <i class="fas fa-envelope text-success me-2"></i>
                            Verified Buyer Emails
                        </span>

                        <span class="badge bg-light text-secondary border font-monospace ms-1 px-2 py-1" style="font-size: 9.5px;" id="buyerCountBadge">
                            {{ $buyerEmails->count() }} Found
                        </span>
                    </div>

                    <button type="button" class="collapse-toggle-btn no-print" data-bs-toggle="collapse" data-bs-target="#collapseBuyersList" aria-expanded="true" aria-controls="collapseBuyersList" title="Toggle Buyers List">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>

                <div class="collapse show" id="collapseBuyersList">

                    {{-- Live Search --}}
                    <div class="buyer-search-wrap">
                        <div class="input-group">

                            <span class="input-group-text">
                                <i class="fas fa-search" style="font-size: 12px;"></i>
                            </span>

                            <input type="text" id="buyerSearchInput" class="form-control shadow-none" placeholder="Search buyer email..." onkeyup="filterBuyersList()">
                        </div>
                    </div>

                    {{-- Buyer List --}}
                    <div class="buyer-list-scroll">
                        <ul class="list-group list-group-flush buyer-list-container" id="buyerListContainer">

                            @forelse($buyerEmails as $email)

                                <li class="list-group-item d-flex justify-content-between align-items-center buyer-inbox-item" data-email="{{ strtolower($email) }}">

                                    <div class="d-flex align-items-center gap-2 min-w-0 flex-grow-1">

                                        <i class="fas fa-user-check text-success flex-shrink-0" style="font-size: 12px;"></i>

                                        <span class="text-dark font-monospace buyer-email-text" title="{{ $email }}">
                                            {{ $email }}
                                        </span>
                                    </div>

                                    <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-2">

                                        <button type="button" class="btn btn-sm btn-light border btn-copy-email shadow-xs" onclick="copyBuyerEmail('{{ addslashes($email) }}')" title="Copy Email">
                                            <i class="fas fa-copy text-secondary" style="font-size: 11px;"></i>
                                        </button>

                                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace buyer-status-badge">
                                            Buyer
                                        </span>
                                    </div>
                                </li>

                            @empty

                                <li class="list-group-item text-center text-muted py-5 small" id="emptyBuyerNotice">
                                    <i class="fas fa-users-slash fa-2x mb-3 d-block text-secondary opacity-25"></i>
                                    <span>No completed orders found for this product yet.</span>
                                </li>

                            @endforelse

                        </ul>
                    </div>

                    {{-- Buyer List Footer --}}
                    @if($buyerEmails->count() > 0)

                        <div class="buyer-list-footer d-flex justify-content-between align-items-center gap-2 flex-wrap">

                            <small class="text-muted font-monospace bn-digit" style="font-size: 10px;">
                                Ready for segment dispatch
                            </small>

                            <button type="button" class="btn btn-sm btn-link text-success p-0 text-decoration-none fw-semibold font-monospace" style="font-size: 11px;" onclick="exportBuyerEmailsCsv()">
                                <i class="fas fa-file-csv me-1"></i>
                                Download CSV
                            </button>
                        </div>

                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Product Image Preview -->
<div class="modal fade target-modal-scope" id="targetProductZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-2">

            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">

                <span class="fw-bold text-dark small" style="font-size: 13px;" id="targetZoomModalTitle">
                    <i class="fas fa-image text-primary me-1"></i>
                    Product Preview
                </span>

                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-2">
                <img id="targetZoomModalImgTag" src="" alt="Product Photo" class="img-fluid border" style="max-height: 380px; max-width: 100%; object-fit: contain; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Notification Dialog -->
<div class="modal fade target-modal-scope" id="targetNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">

            <div class="modal-body p-1">

                <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-3" id="targetNoticeIconWrap" style="width: 46px; height: 46px; border-radius: 50% !important;">
                    <i class="fas fa-circle-info fs-4" id="targetNoticeIcon"></i>
                </div>

                <h6 class="fw-bold text-dark mb-2" id="targetNoticeTitle" style="font-size: 15px;">
                    Notification
                </h6>

                <p class="text-muted small mb-3" id="targetNoticeMsg" style="font-size: 12px; line-height: 1.55;"></p>

                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function zoomTargetProductImage(url, title = 'Product Preview') {
        const titleEl = document.getElementById('targetZoomModalTitle');
        const imgTag  = document.getElementById('targetZoomModalImgTag');
        if (url && imgTag) {
            if (titleEl) titleEl.innerText = title;
            imgTag.src = url;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('targetProductZoomModal')).show();
        }
    }

    function showTargetNotice(message, title = 'Notification', type = 'info') {
        document.getElementById('targetNoticeTitle').innerText = title;
        document.getElementById('targetNoticeMsg').innerText = message;
        const iconWrap = document.getElementById('targetNoticeIconWrap');
        const iconEl   = document.getElementById('targetNoticeIcon');

        if (type === 'success') {
            iconWrap.className = 'rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-2';
            iconEl.className = 'fas fa-circle-check fs-4';
        } else if (type === 'warning') {
            iconWrap.className = 'rounded-circle bg-warning-subtle text-warning-emphasis d-inline-flex align-items-center justify-content-center mx-auto mb-2';
            iconEl.className = 'fas fa-circle-exclamation fs-4';
        } else {
            iconWrap.className = 'rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-2';
            iconEl.className = 'fas fa-circle-info fs-4';
        }

        const modalEl = document.getElementById('targetNoticeModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function copyBuyerEmail(email) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(email).then(() => {
                showTargetNotice(`Email "${email}" copied to clipboard.`, 'Copied', 'success');
            }).catch(() => {
                showTargetNotice(`Email: ${email}`, 'Buyer Email');
            });
        } else {
            showTargetNotice(`Email: ${email}`, 'Buyer Email');
        }
    }

    function filterBuyersList() {
        const query = (document.getElementById('buyerSearchInput')?.value || '').toLowerCase().trim();
        const items = document.querySelectorAll('.buyer-inbox-item');
        let count = 0;

        items.forEach(item => {
            const email = item.getAttribute('data-email') || '';
            if (!query || email.includes(query)) {
                item.style.display = '';
                count++;
            } else {
                item.style.display = 'none';
            }
        });

        const badge = document.getElementById('buyerCountBadge');
        if (badge) {
            badge.innerText = `${count} Showing`;
        }
    }

    function exportBuyerEmailsCsv() {
        const emails = @json($buyerEmails);
        if (!emails || emails.length === 0) {
            showTargetNotice('No buyer emails available to export for this product.', 'Empty Audience', 'warning');
            return;
        }

        let csvContent = "data:text/csv;charset=utf-8,Email Address\n";
        emails.forEach(function(email) {
            csvContent += `"${email}"\n`;
        });

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "buyers_product_{{ $product->id }}_" + new Date().toISOString().slice(0, 10) + ".csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
@endsection
