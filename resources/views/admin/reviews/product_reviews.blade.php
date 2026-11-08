@extends('layouts.admin')

@section('title', 'Product Reviews - ' . ($product->title ?? 'Product #' . $product->id))

@section('admin_content')
@php
    $rawOverride = \Illuminate\Support\Facades\Schema::hasTable('product_review_settings_overrides')
        ? \Illuminate\Support\Facades\DB::table('product_review_settings_overrides')->where('product_id', $product->id)->first()
        : null;

    $svgFallback = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120"><rect fill="%23f8fafc" width="120" height="120"/><text fill="%2394a3b8" font-family="sans-serif" font-size="12" dy="4" font-weight="bold" x="50%" y="50%" text-anchor="middle">No Image</text></svg>';

    $pImg = !empty($product->thumbnail)
        ? (class_exists(\App\Services\ImagePathService::class) ? \App\Services\ImagePathService::resolveUrl($product->thumbnail, $svgFallback) : asset($product->thumbnail))
        : $svgFallback;

    $totalReviewsCount = (int) ($ratingStats['total_reviews'] ?? 0);
    $avgRatingScore = (float) ($ratingStats['average_rating'] ?? 0.0);
    $pinnedCount = count($pinnedReviews ?? []);
    $verifiedCount = (int) ($ratingStats['verified_count'] ?? 0);
    $withPhotosCount = (int) ($ratingStats['with_images_count'] ?? 0);
@endphp

<style>
    /* =========================================================
       PRODUCT REVIEW HUB — ADMIN DESIGN SYSTEM
    ========================================================= */
    #productReviewsRoot {
        --pr-primary: #2563eb;
        --pr-primary-soft: #eff6ff;
        --pr-text: #172033;
        --pr-muted: #64748b;
        --pr-border: #e5eaf1;
        --pr-surface: #ffffff;
        --pr-page: #f8fafc;
        --pr-success: #059669;
        --pr-warning: #d97706;
        --pr-danger: #dc2626;
        color: var(--pr-text);
        font-family: 'Inter', 'Plus Jakarta Sans', sans-serif;
    }

    #productReviewsRoot *,
    #productReviewsRoot *::before,
    #productReviewsRoot *::after,
    .p-modal-scope *,
    .p-modal-scope *::before,
    .p-modal-scope *::after {
        box-sizing: border-box;
    }

    #productReviewsRoot .btn,
    #productReviewsRoot button,
    #productReviewsRoot .badge,
    #productReviewsRoot .form-control,
    #productReviewsRoot .form-select,
    #productReviewsRoot .card,
    #productReviewsRoot .p-header-card,
    #productReviewsRoot .p-panel-card,
    #productReviewsRoot .metric-card,
    #productReviewsRoot .prod-banner-thumb,
    #productReviewsRoot .pinned-item-row,
    #productReviewsRoot .pin-rank-badge,
    #productReviewsRoot .alert,
    .p-modal-scope .modal-content,
    .p-modal-scope .btn {
        border-radius: 5px !important;
    }

    #productReviewsRoot .font-monospace,
    #productReviewsRoot .bn-digit {
        font-variant-numeric: tabular-nums;
        font-weight: 700;
    }

    #productReviewsRoot .min-w-0 {
        min-width: 0;
    }

    #productReviewsRoot .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-height: 34px;
        padding: 7px 12px !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        line-height: 1.3;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #productReviewsRoot .btn-compact:hover {
        transform: translateY(-1px);
    }

    /* Header */
    #productReviewsRoot .p-header-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 17px 19px;
        margin-bottom: 14px;
        background: var(--pr-surface);
        border: 1px solid var(--pr-border);
        box-shadow: 0 2px 7px rgba(15, 23, 42, .035);
    }

    #productReviewsRoot .header-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 8px;
        color: #1d4ed8;
        background: var(--pr-primary-soft);
        border: 1px solid #dbeafe;
        border-radius: 5px;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .065em;
        text-transform: uppercase;
    }

    #productReviewsRoot .header-product-id {
        display: inline-flex;
        align-items: center;
        padding: 5px 8px;
        color: #64748b;
        background: #f8fafc;
        border: 1px solid var(--pr-border);
        border-radius: 5px;
        font-size: 9px;
        font-weight: 700;
    }

    #productReviewsRoot .page-heading {
        margin: 8px 0 0;
        color: var(--pr-text);
        font-size: 18px;
        font-weight: 800;
        line-height: 1.35;
        letter-spacing: -.035em;
    }

    #productReviewsRoot .page-description {
        margin: 5px 0 0;
        color: var(--pr-muted);
        font-size: 11px;
        line-height: 1.55;
    }

    #productReviewsRoot .header-actions-group {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 7px;
        flex-shrink: 0;
    }

    /* KPI Cards */
    #productReviewsRoot .metric-card {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        min-height: 105px;
        padding: 13px 14px;
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--pr-border);
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
        transition: transform .18s ease, box-shadow .18s ease;
    }

    #productReviewsRoot .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(15, 23, 42, .065);
    }

    #productReviewsRoot .metric-card::after {
        position: absolute;
        right: -18px;
        bottom: -30px;
        width: 75px;
        height: 75px;
        border-radius: 50%;
        background: currentColor;
        content: "";
        opacity: .035;
        pointer-events: none;
    }

    #productReviewsRoot .metric-card-blue {
        color: #2563eb;
        border-left: 3px solid #2563eb !important;
    }

    #productReviewsRoot .metric-card-amber {
        color: #d97706;
        border-left: 3px solid #f59e0b !important;
    }

    #productReviewsRoot .metric-card-green {
        color: #059669;
        border-left: 3px solid #10b981 !important;
    }

    #productReviewsRoot .metric-card-purple {
        color: #9333ea;
        border-left: 3px solid #a855f7 !important;
    }

    #productReviewsRoot .stat-label-title {
        color: #64748b;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .025em;
    }

    #productReviewsRoot .stat-val-num {
        margin: 0;
        color: #172033;
        font-size: 19px;
        font-weight: 800;
        line-height: 1.3;
        letter-spacing: -.035em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #productReviewsRoot .stat-sub-text {
        display: block;
        margin-top: 3px;
        color: #94a3b8;
        font-size: 9px;
        line-height: 1.35;
    }

    #productReviewsRoot .metric-icon-square {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        flex-shrink: 0;
        border-radius: 5px !important;
        font-size: 13px;
    }

    /* Main panels */
    #productReviewsRoot .p-panel-card {
        padding: 16px;
        margin-bottom: 14px;
        background: var(--pr-surface);
        border: 1px solid var(--pr-border);
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
    }

    #productReviewsRoot .section-title-sm {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding-bottom: 11px;
        margin-bottom: 13px;
        color: var(--pr-text);
        border-bottom: 1px solid #edf1f5;
        font-size: 12px;
        font-weight: 800;
    }

    #productReviewsRoot .section-title-content {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    #productReviewsRoot .section-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 27px;
        height: 27px;
        flex-shrink: 0;
        background: #f1f5f9;
        border-radius: 5px;
        font-size: 11px;
    }

    /* Product overview */
    #productReviewsRoot .product-overview {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 15px;
        margin-bottom: 14px;
        background: #fff;
        border: 1px solid var(--pr-border);
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
    }

    #productReviewsRoot .prod-banner-thumb {
        width: 66px;
        height: 66px;
        padding: 4px;
        flex-shrink: 0;
        object-fit: contain;
        background: #f8fafc;
        border: 1px solid var(--pr-border);
    }

    #productReviewsRoot .product-name {
        max-width: 100%;
        margin: 0;
        color: var(--pr-text);
        font-size: 13px;
        font-weight: 800;
        line-height: 1.4;
    }

    #productReviewsRoot .product-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 7px 16px;
        margin-top: 7px;
        color: #64748b;
        font-size: 10px;
    }

    #productReviewsRoot .product-rating-summary {
        min-width: 90px;
        padding-left: 14px;
        margin-left: auto;
        border-left: 1px solid var(--pr-border);
        text-align: right;
    }

    #productReviewsRoot .product-rating-score {
        color: #d97706;
        font-size: 20px;
        font-weight: 800;
        line-height: 1.3;
    }

    /* Rating distribution */
    #productReviewsRoot .star-breakdown {
        display: flex;
        flex-direction: column;
        gap: 11px;
        padding: 2px 0 5px;
    }

    #productReviewsRoot .star-breakdown-row {
        display: grid;
        grid-template-columns: 55px minmax(50px, 1fr) 35px 36px;
        align-items: center;
        gap: 9px;
    }

    #productReviewsRoot .star-label {
        color: #64748b;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    #productReviewsRoot .star-bar-track {
        height: 7px;
        overflow: hidden;
        background: #eef2f7;
        border-radius: 5px;
    }

    #productReviewsRoot .star-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #fbbf24, #f59e0b);
        border-radius: 5px;
        transition: width .3s ease;
    }

    #productReviewsRoot .star-count,
    #productReviewsRoot .star-percent {
        color: #64748b;
        font-size: 10px;
        text-align: right;
    }

    #productReviewsRoot .rating-footnote {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 8px 14px;
        padding-top: 11px;
        margin-top: 10px;
        color: #64748b;
        border-top: 1px solid #edf1f5;
        font-size: 10px;
    }

    /* Pinned reviews */
    #productReviewsRoot .pinned-intro {
        margin: -2px 0 13px;
        color: #8491a5;
        font-size: 10px;
        line-height: 1.55;
    }

    #productReviewsRoot .pinned-item-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 12px;
        margin-bottom: 8px;
        background: #fbfcfe;
        border: 1px solid var(--pr-border);
        transition: background .15s ease, border-color .15s ease;
    }

    #productReviewsRoot .pinned-item-row:last-child {
        margin-bottom: 0;
    }

    #productReviewsRoot .pinned-item-row:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    #productReviewsRoot .pin-rank-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 27px;
        height: 27px;
        flex-shrink: 0;
        color: #92400e;
        background: #fef3c7;
        border: 1px solid #fde68a;
        font-size: 10px;
        font-weight: 800;
    }

    #productReviewsRoot .pinned-customer {
        color: #172033;
        font-size: 11px;
        font-weight: 800;
    }

    #productReviewsRoot .pinned-comment {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 10px;
        line-height: 1.55;
        overflow-wrap: anywhere;
    }

    #productReviewsRoot .pinned-reply {
        display: block;
        margin-top: 6px;
        color: #2563eb;
        font-size: 9px;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    #productReviewsRoot .unpin-btn {
        min-height: 29px;
        padding: 5px 8px !important;
        font-size: 10px !important;
    }

    #productReviewsRoot .empty-pinned-state {
        padding: 28px 14px;
        color: #94a3b8;
        background: #f8fafc;
        border: 1px dashed #dbe3ed;
        border-radius: 5px;
        text-align: center;
        font-size: 11px;
        line-height: 1.6;
    }

    /* Override settings */
    #productReviewsRoot .settings-group {
        padding-bottom: 14px;
        margin-bottom: 14px;
        border-bottom: 1px solid #edf1f5;
    }

    #productReviewsRoot .settings-group:last-of-type {
        padding-bottom: 0;
        margin-bottom: 15px;
        border-bottom: 0;
    }

    #productReviewsRoot .form-label {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: 10px;
        font-weight: 800;
    }

    #productReviewsRoot .form-control,
    #productReviewsRoot .form-select {
        min-height: 36px;
        padding: 7px 10px;
        color: #172033;
        background-color: #fff;
        border: 1px solid #dbe2ea;
        box-shadow: none;
        font-size: 11px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #productReviewsRoot .form-control:focus,
    #productReviewsRoot .form-select:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
        outline: none;
    }

    #productReviewsRoot .form-control::placeholder {
        color: #a0aabc;
    }

    #productReviewsRoot .form-check {
        display: flex;
        align-items: center;
        gap: 9px;
        min-height: auto;
        padding-left: 0;
    }

    #productReviewsRoot .form-check-input {
        width: 17px;
        height: 17px;
        flex-shrink: 0;
        margin: 0;
        cursor: pointer;
        border-radius: 4px !important;
    }

    #productReviewsRoot .form-check-input:checked {
        background-color: var(--pr-primary);
        border-color: var(--pr-primary);
    }

    #productReviewsRoot .form-check-label {
        color: #334155;
        font-size: 10px;
        font-weight: 700;
        cursor: pointer;
    }

    #productReviewsRoot .form-switch .form-check-input {
        width: 31px;
        height: 17px;
        border-radius: 20px !important;
    }

    #productReviewsRoot .field-help {
        display: block;
        margin-top: 5px;
        color: #94a3b8;
        font-size: 9px;
        line-height: 1.5;
    }

    #productReviewsRoot .settings-submit {
        min-height: 39px;
        font-size: 11px !important;
        font-weight: 800 !important;
    }

    /* Alerts */
    #productReviewsRoot .alert {
        padding: 11px 13px;
        font-size: 11px;
    }

    /* Modals */
    .p-modal-scope .modal-content {
        border: 1px solid #e5eaf1 !important;
        box-shadow: 0 15px 45px rgba(15, 23, 42, .18);
    }

    .p-modal-scope .modal-body {
        padding: 17px 12px;
    }

    .p-modal-scope .btn {
        min-height: 34px;
        font-size: 11px;
        font-weight: 700;
    }

    /* Responsive */
    @media (max-width: 991.98px) {
        #productReviewsRoot .p-header-card {
            align-items: flex-start;
        }
    }

    @media (max-width: 767.98px) {
        #productReviewsRoot {
            padding-top: 10px !important;
        }

        #productReviewsRoot .p-header-card {
            padding: 14px;
            flex-direction: column;
            align-items: stretch;
            gap: 13px;
        }

        #productReviewsRoot .page-heading {
            font-size: 16px;
        }

        #productReviewsRoot .header-actions-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 7px;
        }

        #productReviewsRoot .header-actions-group .btn {
            width: 100%;
            min-width: 0;
            min-height: 36px;
            padding: 7px 5px !important;
            font-size: 10px !important;
        }

        #productReviewsRoot .header-actions-group .btn-view-all {
            grid-column: 1 / -1;
        }

        #productReviewsRoot .metric-card {
            min-height: 91px;
            padding: 10px;
        }

        #productReviewsRoot .stat-label-title {
            font-size: 9px;
        }

        #productReviewsRoot .stat-val-num {
            font-size: 16px;
        }

        #productReviewsRoot .stat-sub-text {
            font-size: 8px;
        }

        #productReviewsRoot .metric-icon-square {
            width: 27px;
            height: 27px;
            font-size: 11px;
        }

        #productReviewsRoot .p-panel-card {
            padding: 13px;
        }

        #productReviewsRoot .product-overview {
            align-items: flex-start;
            gap: 11px;
            padding: 12px;
        }

        #productReviewsRoot .prod-banner-thumb {
            width: 56px;
            height: 56px;
        }

        #productReviewsRoot .product-meta {
            gap: 6px 11px;
        }

        #productReviewsRoot .product-rating-summary {
            min-width: 70px;
            padding-left: 9px;
        }

        #productReviewsRoot .product-rating-score {
            font-size: 17px;
        }

        #productReviewsRoot .pinned-item-row {
            padding: 10px;
        }
    }

    @media (max-width: 420px) {
        #productReviewsRoot .product-overview {
            flex-wrap: wrap;
        }

        #productReviewsRoot .product-rating-summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 8px 0 0;
            margin: 0;
            border-top: 1px solid var(--pr-border);
            border-left: 0;
            text-align: left;
        }

        #productReviewsRoot .star-breakdown-row {
            grid-template-columns: 48px minmax(35px, 1fr) 28px 31px;
            gap: 6px;
        }

        #productReviewsRoot .pinned-item-row {
            gap: 7px;
        }

        #productReviewsRoot .unpin-btn {
            width: 30px;
            min-width: 30px;
            padding: 5px !important;
            font-size: 0 !important;
        }

        #productReviewsRoot .unpin-btn i {
            margin: 0 !important;
            font-size: 11px;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="productReviewsRoot">

    {{-- Page Header --}}
    <div class="p-header-card">
        <div class="min-w-0">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="header-eyebrow">
                    <i class="fas fa-boxes-stacked"></i>
                    Product Review Hub
                </span>
                <span class="header-product-id">
                    ITEM #{{ $product->id }}
                </span>
            </div>

            <h5 class="page-heading">
                <i class="fas fa-star-half-stroke text-warning me-1"></i>
                Review Hub: {{ Str::limit($product->title ?? 'Product #' . $product->id, 45) }}
            </h5>

            <p class="page-description">
                Manage product reviews, feature top testimonials and customize review policies for this product.
            </p>
        </div>

        <div class="header-actions-group">
            <a href="{{ route('admin.product.reviews.index', ['product_id' => $product->id]) }}"
               class="btn btn-primary btn-compact">
                <i class="fas fa-filter"></i>
                Filter Reviews
            </a>

            <a href="{{ route('product.details', $product->slug ?? $product->id) }}"
               target="_blank"
               class="btn btn-outline-primary btn-compact">
                <i class="fas fa-arrow-up-right-from-square"></i>
                View Live Product
            </a>

            <a href="{{ route('admin.product.reviews.index') }}"
               class="btn btn-light border btn-compact text-dark btn-view-all">
                <i class="fas fa-arrow-left"></i>
                All Reviews
            </a>
        </div>
    </div>

    {{-- Overview Metrics --}}
    <div class="row g-2 mb-3">

        <div class="col-6 col-xl-3">
            <div class="metric-card metric-card-amber">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <span class="stat-label-title text-truncate">Average Rating</span>
                    <div class="metric-icon-square bg-warning-subtle text-warning-emphasis">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div>
                    <h4 class="stat-val-num">
                        {{ number_format($avgRatingScore, 1) }}
                        <span class="text-muted fw-normal" style="font-size: 11px;">/ 5.0</span>
                    </h4>
                    <small class="stat-sub-text">Overall customer satisfaction</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="metric-card metric-card-blue">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <span class="stat-label-title text-truncate">Review Volume</span>
                    <div class="metric-icon-square bg-primary-subtle text-primary">
                        <i class="fas fa-comments"></i>
                    </div>
                </div>
                <div>
                    <h4 class="stat-val-num">{{ number_format($totalReviewsCount) }}</h4>
                    <small class="stat-sub-text">{{ number_format($verifiedCount) }} verified buyer(s)</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="metric-card metric-card-purple">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <span class="stat-label-title text-truncate">Featured Pinned</span>
                    <div class="metric-icon-square" style="background:#f3e8ff;color:#7e22ce;">
                        <i class="fas fa-thumbtack"></i>
                    </div>
                </div>
                <div>
                    <h4 class="stat-val-num">
                        {{ $pinnedCount }}
                        <span class="text-muted fw-normal" style="font-size: 11px;">/ 5 max</span>
                    </h4>
                    <small class="stat-sub-text">Priority storefront testimonials</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="metric-card metric-card-green">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <span class="stat-label-title text-truncate">Policy Status</span>
                    <div class="metric-icon-square bg-success-subtle text-success">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                </div>
                <div>
                    <h4 class="stat-val-num text-success" style="font-size: 13px;">
                        @if(is_null($rawOverride?->reviews_enabled))
                            Global Default
                        @elseif($rawOverride->reviews_enabled == 1)
                            Force Enabled
                        @else
                            Force Disabled
                        @endif
                    </h4>
                    <small class="stat-sub-text">Product-level review policy</small>
                </div>
            </div>
        </div>

    </div>

    {{-- Session Feedback --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center justify-content-between gap-2 mb-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center justify-content-between gap-2 mb-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Product Summary --}}
    <div class="product-overview">
        <img src="{{ $pImg }}"
             alt="{{ $product->title }}"
             class="prod-banner-thumb"
             onerror="this.onerror=null; this.src='{{ $svgFallback }}'">

        <div class="min-w-0 flex-grow-1">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <h6 class="product-name" title="{{ $product->title }}">{{ $product->title }}</h6>
                <span class="badge bg-light text-secondary border" style="font-size: 9px;">
                    ID: #{{ $product->id }}
                </span>
            </div>

            <div class="product-meta">
                <span>
                    Code/SKU:
                    <strong class="text-dark font-monospace">{{ $product->product_code ?? ($product->sku ?? '—') }}</strong>
                </span>
                <span>
                    Price:
                    <strong class="text-primary font-monospace">৳{{ number_format((float)($product->sale_price ?? $product->regular_price ?? 0), 2) }}</strong>
                </span>
                <span>
                    Stock:
                    <strong class="font-monospace {{ $product->stock > 0 ? 'text-success' : 'text-danger' }}">
                        {{ $product->stock }} units
                    </strong>
                </span>
            </div>
        </div>

        <div class="product-rating-summary">
            <div class="product-rating-score">
                <i class="fas fa-star" style="font-size: 14px;"></i>
                {{ number_format($ratingStats['average_rating'], 1) }}
            </div>
            <small class="text-muted font-monospace" style="font-size: 9px;">
                {{ $ratingStats['total_reviews'] }} Ratings
            </small>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="row g-2 g-md-3">

        {{-- Left: Rating Analysis & Featured Reviews --}}
        <div class="col-12 col-lg-7">

            {{-- Rating Distribution --}}
            <div class="p-panel-card">
                <div class="section-title-sm">
                    <div class="section-title-content">
                        <span class="section-icon text-warning">
                            <i class="fas fa-chart-bar"></i>
                        </span>
                        <span>Rating Sentiment Breakdown</span>
                    </div>
                </div>

                <div class="star-breakdown">
                    @for($s = 5; $s >= 1; $s--)
                        @php
                            $c = $ratingStats['distribution'][$s] ?? 0;
                            $pct = $ratingStats['percentages'][$s] ?? 0;
                        @endphp

                        <div class="star-breakdown-row">
                            <span class="star-label">{{ $s }} Star</span>

                            <div class="star-bar-track">
                                <div class="star-bar-fill" style="width: {{ $pct }}%;"></div>
                            </div>

                            <span class="star-count font-monospace">{{ $c }}</span>
                            <span class="star-percent font-monospace">{{ $pct }}%</span>
                        </div>
                    @endfor
                </div>

                <div class="rating-footnote">
                    <span>
                        <i class="fas fa-user-check text-success me-1"></i>
                        Verified Buyers:
                        <strong class="text-success font-monospace">{{ $ratingStats['verified_count'] }}</strong>
                    </span>
                    <span>
                        <i class="fas fa-images text-primary me-1"></i>
                        Reviews with Photos:
                        <strong class="text-primary font-monospace">{{ $ratingStats['with_images_count'] }}</strong>
                    </span>
                </div>
            </div>

            {{-- Featured Pinned Reviews --}}
            <div class="p-panel-card mb-0">
                <div class="section-title-sm">
                    <div class="section-title-content">
                        <span class="section-icon text-warning">
                            <i class="fas fa-thumbtack"></i>
                        </span>
                        <span>Featured Top 5 Pinned Reviews</span>
                    </div>

                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle"
                          style="font-size: 9px;">
                        {{ count($pinnedReviews) }} / 5 Pinned
                    </span>
                </div>

                <p class="pinned-intro">
                    Pinned testimonials appear at the beginning of the product review section, ahead of standard pagination and sorting.
                </p>

                <div id="pinnedReviewsList">
                    @forelse($pinnedReviews as $pin)
                        <div class="pinned-item-row" id="pinned-row-{{ $pin->id }}">

                            <div class="d-flex align-items-start gap-2 min-w-0 flex-grow-1">
                                <div class="pin-rank-badge" title="Display Order">
                                    #{{ $pin->pin_order ?: 1 }}
                                </div>

                                <div class="min-w-0 flex-grow-1">
                                    <div class="d-flex align-items-center flex-wrap gap-2">
                                        <strong class="pinned-customer">
                                            {{ $pin->customer_name ?? 'Customer' }}
                                        </strong>

                                        <span class="text-warning font-monospace fw-bold" style="font-size: 10px;">
                                            <i class="fas fa-star"></i> {{ $pin->rating }}/5
                                        </span>
                                    </div>

                                    <p class="pinned-comment">
                                        {{ Str::limit($pin->comment, 120) }}
                                    </p>

                                    @if(!empty($pin->admin_reply))
                                        <small class="pinned-reply">
                                            <i class="fas fa-reply me-1"></i>
                                            Replied: {{ Str::limit($pin->admin_reply, 60) }}
                                        </small>
                                    @endif
                                </div>
                            </div>

                            <button type="button"
                                    class="btn btn-outline-danger btn-compact unpin-btn flex-shrink-0"
                                    onclick="triggerUnpinConfirm({{ $pin->id }})"
                                    title="Unpin Review">
                                <i class="fas fa-thumbtack-slash"></i>
                                Unpin
                            </button>

                        </div>
                    @empty
                        <div class="empty-pinned-state">
                            <i class="fas fa-thumbtack fa-2x mb-2 d-block opacity-25"></i>
                            <strong class="d-block text-secondary mb-1">No Featured Reviews Yet</strong>
                            No reviews are pinned for this product.<br>
                            Visit the
                            <a href="{{ route('admin.product.reviews.index', ['product_id' => $product->id]) }}"
                               class="fw-bold text-primary text-decoration-none">Reviews Table</a>
                            to pin up to 5 testimonials.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- Right: Product-Specific Settings --}}
        <div class="col-12 col-lg-5">
            <div class="p-panel-card mb-0">

                <div class="section-title-sm">
                    <div class="section-title-content">
                        <span class="section-icon text-primary">
                            <i class="fas fa-sliders"></i>
                        </span>
                        <span>Product-Specific Review Policies</span>
                    </div>
                </div>

                <form action="{{ route('admin.product.reviews.product.settings', $product->id) }}" method="POST">
                    @csrf

                    {{-- Review Submission Policy --}}
                    <div class="settings-group">
                        <label class="form-label">Review Submission on This Product</label>

                        <select name="reviews_enabled" class="form-select font-monospace">
                            <option value="" {{ is_null($rawOverride?->reviews_enabled) ? 'selected' : '' }}>
                                Follow Global Setting (Default)
                            </option>
                            <option value="1" {{ ($rawOverride?->reviews_enabled === 1 || $rawOverride?->reviews_enabled === '1') ? 'selected' : '' }}>
                                Force Enabled for This Product
                            </option>
                            <option value="0" {{ ($rawOverride?->reviews_enabled === 0 || $rawOverride?->reviews_enabled === '0') ? 'selected' : '' }}>
                                Force Disabled (Disable Reviews)
                            </option>
                        </select>

                        <small class="field-help">
                            Disabling prevents new review submissions for this item.
                        </small>
                    </div>

                    {{-- Existing Reviews Visibility --}}
                    <div class="settings-group">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="show_existing_reviews"
                                   id="setShowExisting"
                                   value="1"
                                   {{ !isset($rawOverride->show_existing_reviews) || $rawOverride->show_existing_reviews ? 'checked' : '' }}>

                            <label class="form-check-label" for="setShowExisting">
                                Keep Existing Reviews Visible
                            </label>
                        </div>

                        <small class="field-help">
                            Approved ratings remain visible even when new submissions are disabled.
                        </small>
                    </div>

                    {{-- Customer Photo Uploads --}}
                    <div class="settings-group">
                        <label class="form-label">Customer Photo Upload</label>

                        <select name="image_reviews_enabled" class="form-select font-monospace">
                            <option value="" {{ is_null($rawOverride?->image_reviews_enabled) ? 'selected' : '' }}>
                                Follow Global Setting
                            </option>
                            <option value="1" {{ ($rawOverride?->image_reviews_enabled === 1 || $rawOverride?->image_reviews_enabled === '1') ? 'selected' : '' }}>
                                Allow Photo Attachments
                            </option>
                            <option value="0" {{ ($rawOverride?->image_reviews_enabled === 0 || $rawOverride?->image_reviews_enabled === '0') ? 'selected' : '' }}>
                                Block Photo Attachments
                            </option>
                        </select>
                    </div>

                    {{-- Moderation Policy --}}
                    <div class="settings-group">
                        <label class="form-label">Moderation Policy</label>

                        <select name="approval_policy" class="form-select font-monospace">
                            <option value="default" {{ empty($rawOverride?->approval_policy) || $rawOverride?->approval_policy === 'default' ? 'selected' : '' }}>
                                Follow Global Moderation Rule
                            </option>
                            <option value="auto" {{ ($rawOverride?->approval_policy ?? '') === 'auto' ? 'selected' : '' }}>
                                Auto-Approve Immediately
                            </option>
                            <option value="manual" {{ ($rawOverride?->approval_policy ?? '') === 'manual' ? 'selected' : '' }}>
                                Strict Manual Approval Required
                            </option>
                        </select>
                    </div>

                    {{-- Upload & Pagination Limits --}}
                    <div class="settings-group">
                        <label class="form-label mb-2">Review Limits & Display</label>

                        <div class="row g-2">
                            <div class="col-4">
                                <label class="form-label fw-semibold" style="font-size: 9px;">Max Photos</label>
                                <input type="number"
                                       name="max_images"
                                       class="form-control text-center font-monospace"
                                       min="1"
                                       max="10"
                                       placeholder="Default"
                                       value="{{ $rawOverride->max_images ?? '' }}">
                            </div>

                            <div class="col-4">
                                <label class="form-label fw-semibold" style="font-size: 9px;">Max Size (KB)</label>
                                <input type="number"
                                       name="max_image_size_kb"
                                       class="form-control text-center font-monospace"
                                       min="512"
                                       max="10240"
                                       step="512"
                                       placeholder="Default"
                                       value="{{ $rawOverride->max_image_size_kb ?? '' }}">
                            </div>

                            <div class="col-4">
                                <label class="form-label fw-semibold" style="font-size: 9px;">Page Size</label>
                                <input type="number"
                                       name="per_page"
                                       class="form-control text-center font-monospace"
                                       min="2"
                                       max="50"
                                       placeholder="Default"
                                       value="{{ $rawOverride->per_page ?? '' }}">
                            </div>
                        </div>

                        <small class="field-help">
                            Leave a field empty to use the global configuration.
                        </small>
                    </div>

                    {{-- Auto Reply --}}
                    <div class="settings-group">
                        <label class="form-label">Smart Auto-Reply Engine</label>

                        <select name="auto_reply_enabled" class="form-select font-monospace">
                            <option value="" {{ is_null($rawOverride?->auto_reply_enabled) ? 'selected' : '' }}>
                                Follow Global Automation Rules
                            </option>
                            <option value="1" {{ ($rawOverride?->auto_reply_enabled === 1 || $rawOverride?->auto_reply_enabled === '1') ? 'selected' : '' }}>
                                Enabled for This Product
                            </option>
                            <option value="0" {{ ($rawOverride?->auto_reply_enabled === 0 || $rawOverride?->auto_reply_enabled === '0') ? 'selected' : '' }}>
                                Disable Auto-Reply for This Item
                            </option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-compact w-100 settings-submit">
                        <i class="fas fa-save"></i>
                        Save Product Override Policies
                    </button>
                </form>

            </div>
        </div>

    </div>
</div>

{{-- Unpin Confirmation Modal --}}
<div class="modal fade p-modal-scope"
     id="unpinConfirmModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 108000;">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-3">
            <div class="modal-body">
                <div class="mb-3 text-warning" style="font-size: 32px;">
                    <i class="fas fa-thumbtack"></i>
                </div>

                <h6 class="fw-bold text-dark mb-2" style="font-size: 14px;">
                    Unpin This Review?
                </h6>

                <p class="text-muted small mb-3" style="font-size: 11px; line-height: 1.5;">
                    Remove this review from the featured position on this product page?
                </p>

                <div class="d-flex justify-content-center gap-2">
                    <button type="button"
                            class="btn btn-light border px-3"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="button"
                            class="btn btn-danger px-3"
                            id="confirmUnpinExecuteBtn">
                        <i class="fas fa-thumbtack-slash me-1"></i>
                        Yes, Unpin
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Reusable Admin Alert Modal --}}
<div class="modal fade p-modal-scope"
     id="productReviewAlertModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 108500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-3">
            <div class="modal-body">
                <div id="pReviewAlertIcon" class="mb-3" style="font-size: 32px;"></div>

                <h6 id="pReviewAlertTitle" class="fw-bold mb-2 text-dark small"></h6>

                <p id="pReviewAlertMessage"
                   class="text-muted small mb-3"
                   style="font-size: 11px; line-height: 1.5;"></p>

                <button type="button"
                        class="btn btn-primary px-4"
                        data-bs-dismiss="modal"
                        id="pReviewAlertCloseBtn">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let targetUnpinId = null;

    function showProductAlert(title, message, type = 'info', reloadOnClose = false) {
        const modalEl = document.getElementById('productReviewAlertModal');
        const iconEl = document.getElementById('pReviewAlertIcon');
        const titleEl = document.getElementById('pReviewAlertTitle');
        const msgEl = document.getElementById('pReviewAlertMessage');
        const closeBtn = document.getElementById('pReviewAlertCloseBtn');

        if (!modalEl || !iconEl || !titleEl || !msgEl) return;

        if (type === 'success') {
            iconEl.innerHTML = '<i class="fas fa-check-circle text-success"></i>';
        } else if (type === 'error') {
            iconEl.innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
        } else {
            iconEl.innerHTML = '<i class="fas fa-info-circle text-primary"></i>';
        }

        titleEl.innerText = title;
        msgEl.innerText = message;

        if (closeBtn) {
            closeBtn.onclick = function() {
                if (reloadOnClose) window.location.reload();
            };
        }

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    function triggerUnpinConfirm(id) {
        targetUnpinId = id;

        const modal = bootstrap.Modal.getOrCreateInstance(
            document.getElementById('unpinConfirmModal')
        );

        modal.show();
    }

    document.addEventListener('DOMContentLoaded', () => {
        const execBtn = document.getElementById('confirmUnpinExecuteBtn');

        if (execBtn) {
            execBtn.addEventListener('click', () => {
                if (!targetUnpinId) return;

                const confirmModal = bootstrap.Modal.getInstance(
                    document.getElementById('unpinConfirmModal')
                );

                if (confirmModal) confirmModal.hide();

                fetch("{{ url('admin/product/reviews/toggle-pin') }}/" + targetUnpinId, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        showProductAlert(
                            'Success',
                            data.message || 'Review unpinned successfully.',
                            'success',
                            true
                        );
                    } else {
                        showProductAlert(
                            'Notice',
                            data.message || 'Failed to unpin review.',
                            'error',
                            false
                        );
                    }
                })
                .catch(() => {
                    showProductAlert(
                        'Error',
                        'Network error occurred while unpinning.',
                        'error',
                        false
                    );
                });
            });
        }
    });
</script>
@endsection