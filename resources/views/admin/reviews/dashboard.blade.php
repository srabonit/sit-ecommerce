@extends('layouts.admin')

@section('title', 'Product Reviews Analytics - ' . config('app.name'))

@section('admin_content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    /* =========================================================
       PRODUCT REVIEWS DASHBOARD
       Kaiadmin Lite v1.2.0 - Classic White Design
    ========================================================= */

    #reviewDashboardRoot {
        /* Theme Variables */
        --review-primary: var(--bs-primary, #1572e8);
        --review-secondary: #6861ce;
        --review-success: var(--bs-success, #31ce36);
        --review-danger: var(--bs-danger, #f25961);
        --review-warning: var(--bs-warning, #ffad46);
        --review-info: var(--bs-info, #48abf7);

        --review-body-bg: #f8f9fa;
        --review-card-bg: #ffffff;
        --review-heading: #2a2f5b;
        --review-text: #495057;
        --review-muted: #8d9498;
        --review-border: #ebecec;
        --review-table-head: #f8f9fa;

        --review-radius: 5px;
        --review-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);

        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        color: var(--review-text);
        font-size: 13px;
        line-height: 1.5;
        min-width: 0;
        width: 100%;
    }

    #reviewDashboardRoot,
    #reviewDashboardRoot * {
        box-sizing: border-box;
    }

    #reviewDashboardRoot h1,
    #reviewDashboardRoot h2,
    #reviewDashboardRoot h3,
    #reviewDashboardRoot h4,
    #reviewDashboardRoot h5,
    #reviewDashboardRoot h6,
    #reviewDashboardRoot p,
    #reviewDashboardRoot span,
    #reviewDashboardRoot a,
    #reviewDashboardRoot label,
    #reviewDashboardRoot strong,
    #reviewDashboardRoot small,
    #reviewDashboardRoot td,
    #reviewDashboardRoot th {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
    }

    #reviewDashboardRoot .font-monospace,
    #reviewDashboardRoot .bn-digit {
        font-family: 'Inter', sans-serif !important;
        font-variant-numeric: tabular-nums;
        font-weight: 700 !important;
        letter-spacing: 0;
    }

    /* Consistent Radius */
    #reviewDashboardRoot .btn,
    #reviewDashboardRoot .badge,
    #reviewDashboardRoot .card,
    #reviewDashboardRoot .review-stat-card,
    #reviewDashboardRoot .dashboard-panel-card,
    #reviewDashboardRoot .stat-icon-square,
    #reviewDashboardRoot .review-mini-product-img,
    #reviewDashboardRoot .progress,
    #reviewDashboardRoot .progress-bar {
        border-radius: var(--review-radius);
    }

    /* =========================================================
       BUTTONS
    ========================================================= */

    #reviewDashboardRoot .btn {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    #reviewDashboardRoot .btn-compact {
        font-size: 11px;
        padding: 7px 12px;
        line-height: 1.4;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 3px;
        white-space: nowrap;
        min-height: 32px;
    }

    #reviewDashboardRoot .btn-primary {
        background-color: var(--review-primary);
        border-color: var(--review-primary);
        color: #fff;
    }

    #reviewDashboardRoot .btn-primary:hover {
        filter: brightness(0.92);
        color: #fff;
    }

    #reviewDashboardRoot .btn-success {
        background-color: var(--review-success);
        border-color: var(--review-success);
        color: #fff;
    }

    #reviewDashboardRoot .btn-success:hover {
        filter: brightness(0.92);
        color: #fff;
    }

    #reviewDashboardRoot .btn-outline-success {
        color: var(--review-success);
        border-color: var(--review-success);
        background: #fff;
    }

    #reviewDashboardRoot .btn-outline-success:hover {
        background: var(--review-success);
        color: #fff;
    }

    #reviewDashboardRoot .btn-outline-dark {
        background: #fff;
        border-color: #d5d8dc;
        color: #495057;
    }

    #reviewDashboardRoot .btn-outline-dark:hover {
        background: #495057;
        border-color: #495057;
        color: #fff;
    }

    #reviewDashboardRoot .btn-light {
        background: #fff;
        border-color: var(--review-border);
        color: #495057;
    }

    #reviewDashboardRoot .btn-light:hover {
        background: #f1f3f5;
        border-color: #d5d8dc;
    }

    #reviewDashboardRoot .btn:focus-visible,
    #reviewDashboardRoot a:focus-visible {
        outline: 2px solid var(--review-primary);
        outline-offset: 2px;
    }

    /* =========================================================
       DASHBOARD HEADER
    ========================================================= */

    #reviewDashboardRoot .dashboard-header-card {
        background: var(--review-card-bg);
        border: 1px solid var(--review-border);
        border-radius: var(--review-radius);
        box-shadow: var(--review-shadow);
        padding: 16px 18px;
        margin-bottom: 16px;
    }

    #reviewDashboardRoot .dashboard-header-card h5 {
        color: var(--review-heading) !important;
        font-size: clamp(16px, 1.5vw, 19px) !important;
        font-weight: 700;
        letter-spacing: -0.5px;
        line-height: 1.4;
    }

    #reviewDashboardRoot .dashboard-header-card small {
        font-size: 11px !important;
        color: var(--review-muted);
        line-height: 1.6;
    }

    #reviewDashboardRoot .header-actions-group {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 7px;
    }

    /* =========================================================
       SUMMARY STATISTICS
    ========================================================= */

    #reviewDashboardRoot .review-stat-card {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        height: 100%;
        min-height: 76px;
        padding: 13px;
        background: var(--review-card-bg);
        border: 1px solid var(--review-border);
        box-shadow: var(--review-shadow);
        overflow: hidden;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    #reviewDashboardRoot .review-stat-card:hover {
        border-color: #d5dce5;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
    }

    #reviewDashboardRoot .stat-icon-square {
        width: 39px;
        height: 39px;
        min-width: 39px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }

    #reviewDashboardRoot .stat-label-title {
        display: block;
        color: var(--review-muted);
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.35px;
        line-height: 1.4;
        margin-bottom: 4px;
    }

    #reviewDashboardRoot .stat-val-num {
        color: var(--review-heading) !important;
        font-size: clamp(15px, 1.3vw, 19px);
        font-weight: 800;
        line-height: 1.25;
        letter-spacing: -0.5px;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #reviewDashboardRoot .metric-border-blue {
        border-left: 3px solid #1572e8 !important;
    }

    #reviewDashboardRoot .metric-border-amber {
        border-left: 3px solid #ffad46 !important;
    }

    #reviewDashboardRoot .metric-border-red {
        border-left: 3px solid #f25961 !important;
    }

    #reviewDashboardRoot .metric-border-cyan {
        border-left: 3px solid #48abf7 !important;
    }

    #reviewDashboardRoot .metric-border-green {
        border-left: 3px solid #31ce36 !important;
    }

    #reviewDashboardRoot .metric-border-slate {
        border-left: 3px solid #6861ce !important;
    }

    /* =========================================================
       DASHBOARD PANELS
    ========================================================= */

    #reviewDashboardRoot .dashboard-panel-card {
        background: var(--review-card-bg);
        border: 1px solid var(--review-border);
        border-radius: var(--review-radius);
        box-shadow: var(--review-shadow);
        padding: 16px;
        margin-bottom: 16px;
        min-width: 0;
    }

    #reviewDashboardRoot .dashboard-panel-card h6 {
        color: var(--review-heading) !important;
        font-size: 13px !important;
        font-weight: 700;
        line-height: 1.5;
    }

    #reviewDashboardRoot .dashboard-panel-card > .border-bottom {
        border-color: var(--review-border) !important;
    }

    #reviewDashboardRoot .dashboard-panel-card > .border-top {
        border-color: var(--review-border) !important;
    }

    #reviewDashboardRoot .dashboard-panel-card .bg-light {
        background-color: var(--review-table-head) !important;
    }

    /* =========================================================
       CHARTS
    ========================================================= */

    #reviewDashboardRoot .review-chart-container {
        position: relative;
        width: 100%;
        height: 250px;
        min-width: 0;
    }

    #reviewDashboardRoot .review-chart-container canvas {
        max-width: 100%;
    }

    /* =========================================================
       STAR DISTRIBUTION
    ========================================================= */

    #reviewDashboardRoot .star-bar-track {
        height: 7px;
        background: #f0f1f3;
        border-radius: 10px;
        overflow: hidden;
        flex-grow: 1;
        min-width: 30px;
    }

    #reviewDashboardRoot .star-bar-fill {
        height: 100%;
        background: var(--review-warning);
        border-radius: 10px;
        transition: width 0.3s ease;
    }

    /* =========================================================
       PRODUCT IMAGE
    ========================================================= */

    #reviewDashboardRoot .review-mini-product-img {
        width: 38px;
        height: 38px;
        object-fit: contain;
        border: 1px solid var(--review-border);
        background: #fff;
        flex-shrink: 0;
    }

    /* =========================================================
       DATA TABLES
    ========================================================= */

    #reviewDashboardRoot .dashboard-sub-table {
        width: 100% !important;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #reviewDashboardRoot .dashboard-sub-table th {
        background: var(--review-table-head);
        color: #686c71;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.35px;
        border-bottom: 1px solid var(--review-border);
        border-top: 0;
        padding: 10px 11px;
        white-space: nowrap;
        vertical-align: middle;
    }

    #reviewDashboardRoot .dashboard-sub-table td {
        padding: 10px 11px;
        vertical-align: middle;
        font-size: 11.5px;
        color: var(--review-text);
        border-bottom: 1px solid #f0f1f3;
        line-height: 1.5;
    }

    #reviewDashboardRoot .dashboard-sub-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #reviewDashboardRoot .dashboard-sub-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    #reviewDashboardRoot .dashboard-sub-table strong {
        font-weight: 700;
    }

    #reviewDashboardRoot .dashboard-sub-table a {
        font-weight: 600;
    }

    #reviewDashboardRoot .dashboard-sub-table .badge {
        display: inline-block;
        font-weight: 700;
        letter-spacing: 0.2px;
        line-height: 1.5;
    }

    #reviewDashboardRoot .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #cbd0d7 transparent;
    }

    #reviewDashboardRoot .table-responsive::-webkit-scrollbar {
        height: 5px;
    }

    #reviewDashboardRoot .table-responsive::-webkit-scrollbar-thumb {
        background: #cbd0d7;
        border-radius: 10px;
    }

    #reviewDashboardRoot .table-responsive-feedback {
        min-width: 620px;
    }

    /* =========================================================
       LINKS & TEXT
    ========================================================= */

    #reviewDashboardRoot .text-muted {
        color: var(--review-muted) !important;
    }

    #reviewDashboardRoot .text-dark {
        color: var(--review-heading) !important;
    }

    #reviewDashboardRoot .text-primary {
        color: var(--review-primary) !important;
    }

    #reviewDashboardRoot .text-decoration-none:hover {
        text-decoration: none !important;
    }

    /* =========================================================
       BADGES
    ========================================================= */

    #reviewDashboardRoot .badge {
        padding: 4px 7px;
        font-size: 9px;
        font-weight: 700;
        line-height: 1.4;
    }

    /* =========================================================
       RESPONSIVE: LARGE DESKTOP
    ========================================================= */

    @media (min-width: 1400px) {
        #reviewDashboardRoot .review-stat-card {
            padding: 15px;
            gap: 13px;
        }

        #reviewDashboardRoot .stat-icon-square {
            width: 42px;
            height: 42px;
            min-width: 42px;
        }

        #reviewDashboardRoot .dashboard-panel-card {
            padding: 18px;
        }
    }

    /* =========================================================
       RESPONSIVE: TABLET & SMALL DESKTOP
    ========================================================= */

    @media (max-width: 991.98px) {
        #reviewDashboardRoot .dashboard-header-card {
            align-items: flex-start;
        }

        #reviewDashboardRoot .header-actions-group {
            justify-content: flex-start;
        }

        #reviewDashboardRoot .review-chart-container {
            height: 230px;
        }

        #reviewDashboardRoot .dashboard-panel-card {
            padding: 13px;
        }
    }

    /* =========================================================
       RESPONSIVE: MOBILE
    ========================================================= */

    @media (max-width: 767.98px) {
        #reviewDashboardRoot {
            font-size: 12px;
        }

        #reviewDashboardRoot .dashboard-header-card {
            padding: 13px;
            margin-bottom: 12px;
        }

        #reviewDashboardRoot .header-actions-group {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 7px;
        }

        #reviewDashboardRoot .header-actions-group .btn {
            width: 100%;
            min-width: 0;
            min-height: 36px;
            padding: 7px 5px;
            font-size: 10px;
            white-space: normal;
        }

        #reviewDashboardRoot .review-stat-card {
            padding: 10px;
            gap: 8px;
            min-height: 68px;
        }

        #reviewDashboardRoot .stat-icon-square {
            width: 31px;
            height: 31px;
            min-width: 31px;
            font-size: 12px;
        }

        #reviewDashboardRoot .stat-label-title {
            font-size: 9px;
            letter-spacing: 0;
        }

        #reviewDashboardRoot .stat-val-num {
            font-size: 14px;
        }

        #reviewDashboardRoot .dashboard-panel-card {
            padding: 12px;
        }

        #reviewDashboardRoot .review-chart-container {
            height: 210px;
        }

        #reviewDashboardRoot .dashboard-sub-table th {
            padding: 9px 8px;
            font-size: 9px;
        }

        #reviewDashboardRoot .dashboard-sub-table td {
            padding: 9px 8px;
            font-size: 11px;
        }

        #reviewDashboardRoot .review-mini-product-img {
            width: 32px;
            height: 32px;
        }

        #reviewDashboardRoot .table-responsive-feedback {
            min-width: 620px;
        }
    }

    /* =========================================================
       RESPONSIVE: SMALL MOBILE
    ========================================================= */

    @media (max-width: 380px) {
        #reviewDashboardRoot .container-fluid {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }

        #reviewDashboardRoot .dashboard-header-card {
            padding: 11px;
        }

        #reviewDashboardRoot .header-actions-group .btn {
            font-size: 9px;
            padding: 7px 3px;
        }

        #reviewDashboardRoot .review-stat-card {
            padding: 8px;
            gap: 6px;
        }

        #reviewDashboardRoot .stat-icon-square {
            width: 27px;
            height: 27px;
            min-width: 27px;
            font-size: 11px;
        }

        #reviewDashboardRoot .stat-label-title {
            font-size: 8px;
        }

        #reviewDashboardRoot .stat-val-num {
            font-size: 13px;
        }

        #reviewDashboardRoot .dashboard-panel-card {
            padding: 10px;
        }
    }

    /* =========================================================
       ACCESSIBILITY & MOTION
    ========================================================= */

    @media (prefers-reduced-motion: reduce) {
        #reviewDashboardRoot *,
        #reviewDashboardRoot *::before,
        #reviewDashboardRoot *::after {
            transition-duration: 0.01ms !important;
            animation-duration: 0.01ms !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="reviewDashboardRoot">

    {{-- 1. Dashboard Header --}}
    <div class="dashboard-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">

        <div class="min-w-0">
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-chart-line text-primary"></i>
                <span>Product Reviews Analytics</span>
            </h5>

            <small class="text-muted d-block mt-1">
                Monitor review activity, ratings and customer feedback.
            </small>
        </div>

        <div class="header-actions-group">

            <a href="{{ route('admin.product.reviews.index') }}"
               class="btn btn-primary btn-compact shadow-xs">
                <i class="fas fa-list-check me-1"></i> All Reviews
            </a>

            <a href="{{ route('admin.product.reviews.auto_rules') }}"
               class="btn btn-outline-success btn-compact shadow-xs">
                <i class="fas fa-robot me-1"></i> Auto-Reply Rules
            </a>

            <a href="{{ route('admin.product.reviews.settings') }}"
               class="btn btn-outline-dark btn-compact shadow-xs">
                <i class="fas fa-gear me-1"></i> Settings
            </a>

            <button type="button"
                    class="btn btn-success btn-compact shadow-xs"
                    onclick="location.href='{{ route('admin.product.reviews.index', ['export' => 'csv']) }}'">
                <i class="fas fa-file-csv me-1"></i> Export CSV
            </button>

        </div>
    </div>

    {{-- 2. Summary Statistics --}}
    <div class="row g-2 mb-3">

        {{-- Total Reviews --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="review-stat-card metric-border-blue">

                <div class="stat-icon-square bg-primary-subtle text-primary">
                    <i class="fas fa-comments"></i>
                </div>

                <div class="min-w-0">
                    <span class="stat-label-title text-truncate">Total Reviews</span>

                    <h5 class="stat-val-num font-monospace bn-digit">
                        {{ number_format($summary['total_reviews'] ?? 0) }}
                    </h5>
                </div>

            </div>
        </div>

        {{-- Average Rating --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="review-stat-card metric-border-amber">

                <div class="stat-icon-square bg-warning-subtle text-warning">
                    <i class="fas fa-star"></i>
                </div>

                <div class="min-w-0">
                    <span class="stat-label-title text-truncate">Avg. Store Rating</span>

                    <h5 class="stat-val-num font-monospace bn-digit">
                        ⭐ {{ number_format($summary['average_rating'] ?? 0, 1) }}
                    </h5>
                </div>

            </div>
        </div>

        {{-- Pending Approval --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="review-stat-card metric-border-red">

                <div class="stat-icon-square bg-danger-subtle text-danger">
                    <i class="fas fa-hourglass-half"></i>
                </div>

                <div class="min-w-0">
                    <span class="stat-label-title text-truncate">Pending Approval</span>

                    <h5 class="stat-val-num text-danger font-monospace bn-digit">
                        {{ number_format($summary['pending_count'] ?? 0) }}
                    </h5>
                </div>

            </div>
        </div>

        {{-- Unanswered --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="review-stat-card metric-border-cyan">

                <div class="stat-icon-square bg-info-subtle text-info">
                    <i class="fas fa-reply-all"></i>
                </div>

                <div class="min-w-0">
                    <span class="stat-label-title text-truncate">Unanswered</span>

                    <h5 class="stat-val-num font-monospace bn-digit">
                        {{ number_format($summary['unanswered_count'] ?? 0) }}
                    </h5>
                </div>

            </div>
        </div>

        {{-- Customer Photos --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="review-stat-card metric-border-green">

                <div class="stat-icon-square bg-success-subtle text-success">
                    <i class="fas fa-camera"></i>
                </div>

                <div class="min-w-0">
                    <span class="stat-label-title text-truncate">With Photos</span>

                    <h5 class="stat-val-num font-monospace bn-digit">
                        {{ number_format($summary['with_images_count'] ?? 0) }}
                    </h5>
                </div>

            </div>
        </div>

        {{-- Today's Reviews --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="review-stat-card metric-border-slate">

                <div class="stat-icon-square bg-light text-secondary border">
                    <i class="fas fa-calendar-day"></i>
                </div>

                <div class="min-w-0">
                    <span class="stat-label-title text-truncate">Today's Reviews</span>

                    <h5 class="stat-val-num font-monospace bn-digit">
                        {{ number_format($summary['today_count'] ?? 0) }}
                    </h5>
                </div>

            </div>
        </div>

    </div>

    {{-- 3. Analytics Charts --}}
    <div class="row g-2 mb-3">

        {{-- Review Trend --}}
        <div class="col-12 col-lg-8">
            <div class="dashboard-panel-card h-100 mb-0">

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom pb-2 mb-3">

                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fas fa-chart-line text-primary"></i>
                        Review Submission Trend
                    </h6>

                    <span class="badge bg-light text-secondary border font-monospace">
                        LAST 30 DAYS
                    </span>

                </div>

                <div class="review-chart-container">
                    <canvas id="reviewTrendChart"></canvas>
                </div>

            </div>
        </div>

        {{-- Rating Distribution --}}
        <div class="col-12 col-lg-4">
            <div class="dashboard-panel-card h-100 mb-0">

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom pb-2 mb-3">

                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fas fa-star text-warning"></i>
                        Rating Distribution
                    </h6>

                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle font-monospace">
                        1–5 STARS
                    </span>

                </div>

                @php
                    $totalRev = max(1, (int)($summary['total_reviews'] ?? 1));
                    $starCounts = $summary['star_counts'] ?? [5=>0, 4=>0, 3=>0, 2=>0, 1=>0];
                @endphp

                <div class="d-flex flex-column gap-3 pt-1 mb-3">

                    @for($s = 5; $s >= 1; $s--)
                        @php
                            $c = $starCounts[$s] ?? 0;
                            $pct = round(($c / $totalRev) * 100);
                        @endphp

                        <div class="d-flex align-items-center gap-2 small">

                            <span class="text-muted font-monospace"
                                  style="width: 46px; flex-shrink: 0; font-size: 10px;">
                                {{ $s }} Star
                            </span>

                            <div class="star-bar-track">
                                <div class="star-bar-fill" style="width: {{ $pct }}%;"></div>
                            </div>

                            <span class="text-dark font-monospace text-end bn-digit"
                                  style="width: 36px; font-size: 10px;">
                                {{ $c }}
                            </span>

                            <span class="text-muted font-monospace text-end"
                                  style="width: 30px; font-size: 9px;">
                                {{ $pct }}%
                            </span>

                        </div>
                    @endfor

                </div>

                <div class="border-top pt-3 d-flex justify-content-between align-items-center gap-2 small">

                    <span class="text-muted">
                        Verified Buyer Rate
                    </span>

                    <strong class="font-monospace text-success bn-digit">
                        {{ (!empty($summary['total_reviews']) && $summary['total_reviews'] > 0) ? round((($summary['verified_count'] ?? 0) / $summary['total_reviews']) * 100) : 0 }}%
                    </strong>

                </div>

            </div>
        </div>

    </div>

    {{-- 4. Product & Customer Review Tables --}}
    <div class="row g-2">

        {{-- Most Reviewed Products --}}
        <div class="col-12 col-lg-5">

            <div class="dashboard-panel-card h-100 mb-0 p-0 overflow-hidden">

                <div class="py-3 px-3 border-bottom bg-light d-flex justify-content-between align-items-center gap-2">

                    <h6 class="fw-bold text-dark mb-0">
                        <i class="fas fa-boxes-stacked text-primary me-1"></i>
                        Most Reviewed Products
                    </h6>

                    <a href="{{ route('admin.product.index') }}"
                       class="btn btn-sm btn-link text-decoration-none p-0 text-primary fw-semibold font-monospace">
                        View All
                    </a>

                </div>

                <div class="table-responsive">

                    <table class="table align-middle dashboard-sub-table mb-0">

                        <thead>
                            <tr>
                                <th style="min-width: 170px;">Product</th>
                                <th class="text-center" style="width: 70px; min-width: 70px;">Reviews</th>
                                <th class="text-end pe-3" style="width: 90px; min-width: 90px;">Avg. Rating</th>
                            </tr>
                        </thead>

                        <tbody>

                            @php
                                $svgSafeFallback = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80"><rect fill="%23f8fafc" width="80" height="80"/><text fill="%2394a3b8" font-family="sans-serif" font-size="10" dy="3.5" font-weight="bold" x="50%" y="50%" text-anchor="middle">Product</text></svg>';
                            @endphp

                            @forelse($topProducts as $tp)

                                <tr>

                                    <td>
                                        <div class="d-flex align-items-center gap-2">

                                            @php
                                                $pImg = !empty($tp->thumbnail)
                                                    ? (class_exists(\App\Services\ImagePathService::class) ? \App\Services\ImagePathService::resolveUrl($tp->thumbnail, $svgSafeFallback) : asset($tp->thumbnail))
                                                    : $svgSafeFallback;
                                            @endphp

                                            <img src="{{ $pImg }}"
                                                 alt="Thumb"
                                                 class="review-mini-product-img shadow-xs"
                                                 onerror="this.onerror=null; this.src='{{ $svgSafeFallback }}'">

                                            <div class="overflow-hidden min-w-0" style="max-width: 180px;">

                                                <strong class="d-block text-dark text-truncate"
                                                        style="font-size: 12px;"
                                                        title="{{ $tp->title }}">
                                                    {{ $tp->title }}
                                                </strong>

                                                <a href="{{ route('admin.product.reviews.product', $tp->id) }}"
                                                   class="text-primary text-decoration-none font-monospace"
                                                   style="font-size: 10px;">
                                                    Manage Reviews
                                                </a>

                                            </div>

                                        </div>
                                    </td>

                                    <td class="text-center font-monospace bn-digit">
                                        {{ $tp->total_reviews_count }}
                                    </td>

                                    <td class="text-end pe-3 font-monospace text-warning fw-bold">
                                        ⭐ {{ number_format($tp->avg_rating, 1) }}
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="3"
                                        class="text-center py-4 text-muted small">
                                        <i class="fas fa-inbox d-block mb-2 fs-5"></i>
                                        No product reviews recorded yet.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        {{-- Recent Customer Feedback --}}
        <div class="col-12 col-lg-7">

            <div class="dashboard-panel-card h-100 mb-0 p-0 overflow-hidden">

                <div class="py-3 px-3 border-bottom bg-light d-flex justify-content-between align-items-center gap-2">

                    <h6 class="fw-bold text-dark mb-0">
                        <i class="fas fa-clock-rotate-left text-warning me-1"></i>
                        Recent Customer Feedback
                    </h6>

                    <a href="{{ route('admin.product.reviews.index') }}"
                       class="btn btn-sm btn-link text-decoration-none p-0 text-primary fw-semibold font-monospace">
                        View All
                    </a>

                </div>

                <div class="table-responsive">

                    <table class="table align-middle dashboard-sub-table table-responsive-feedback mb-0">

                        <thead>
                            <tr>
                                <th style="width: 140px; min-width: 130px;">Customer &amp; Product</th>
                                <th style="width: 75px; min-width: 75px;">Rating</th>
                                <th style="min-width: 180px;">Comment</th>
                                <th class="text-center" style="width: 85px; min-width: 85px;">Status</th>
                                <th class="text-end pe-3" style="width: 45px; min-width: 45px;">Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($recentReviews as $rr)

                                <tr>

                                    <td>

                                        <strong class="d-block text-dark text-truncate"
                                                style="max-width: 130px; font-size: 12px;">
                                            {{ $rr->customer_name ?? 'Guest Customer' }}
                                        </strong>

                                        <small class="text-muted d-block text-truncate font-monospace"
                                               style="max-width: 130px; font-size: 10px;">
                                            {{ $rr->product_title ?? 'Product #' . $rr->product_id }}
                                        </small>

                                    </td>

                                    <td class="font-monospace text-warning fw-bold"
                                        style="white-space: nowrap;">
                                        ⭐ {{ $rr->rating }}/5
                                    </td>

                                    <td>

                                        <p class="mb-0 text-dark text-truncate"
                                           style="font-size: 11px; line-height: 1.5; max-width: 210px;"
                                           title="{{ $rr->comment }}">
                                            {{ $rr->comment }}
                                        </p>

                                    </td>

                                    <td class="text-center" style="white-space: nowrap;">

                                        @php
                                            $bClass = match($rr->status) {
                                                'approved' => 'bg-success-subtle text-success border border-success-subtle',
                                                'rejected' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                                default    => 'bg-warning-subtle text-dark border border-warning-subtle'
                                            };
                                        @endphp

                                        <span class="badge {{ $bClass }} text-uppercase font-monospace px-2 py-1">
                                            {{ $rr->status }}
                                        </span>

                                    </td>

                                    <td class="text-end pe-3" style="white-space: nowrap;">

                                        <a href="{{ route('admin.product.reviews.index', ['search' => '#' . $rr->id]) }}"
                                           class="btn btn-light border btn-compact p-0 text-primary shadow-xs"
                                           style="width: 30px; height: 30px;"
                                           title="Moderate">
                                            <i class="fas fa-up-right-from-square"></i>
                                        </a>

                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted small">
                                        <i class="fas fa-comments d-block mb-2 fs-5"></i>
                                        No recent customer reviews found.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('reviewTrendChart');
    if (!ctx) return;

    const chartDates  = {!! json_encode($chartDates ?? []) !!};
    const chartCounts = {!! json_encode($chartCounts ?? []) !!};

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartDates.length ? chartDates : ['No Data'],
            datasets: [{
                label: 'Reviews Count',
                data: chartCounts.length ? chartCounts : [0],
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.08)',
                borderWidth: 2,
                pointRadius: 2.5,
                pointBackgroundColor: '#2563eb',
                tension: 0.25,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return (context.parsed.y || 0) + ' Review(s)';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 }, maxRotation: 45 }
                },
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 10 } },
                    grid: { color: '#f1f5f9' }
                }
            }
        }
    });
});
</script>

@endsection