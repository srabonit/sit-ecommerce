@extends('layouts.admin')

@section('title', 'Product Reviews Management - ' . config('app.name'))

@php
    $totalReviewsCount = $tabCounts['all'] ?? 0;
    $pendingCount = $tabCounts['pending'] ?? 0;
    $approvedCount = $tabCounts['approved'] ?? 0;
    $pinnedCount = $tabCounts['pinned'] ?? 0;
    $withImagesCount = $tabCounts['with_images'] ?? 0;
@endphp

@section('admin_content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* =========================================================
       PRODUCT REVIEWS — ADMIN DESIGN SYSTEM
    ========================================================= */

    #reviewsManagementRoot,
    .review-modal-scope {
        --rv-primary: #2563eb;
        --rv-primary-dark: #1d4ed8;
        --rv-text: #172033;
        --rv-muted: #64748b;
        --rv-border: #e2e8f0;
        --rv-surface: #ffffff;
        --rv-soft: #f8fafc;
        --rv-radius: 5px;

        font-family: 'Inter', sans-serif !important;
        color: var(--rv-text);
        font-size: 12px;
        letter-spacing: -0.01em;
    }

    #reviewsManagementRoot *,
    .review-modal-scope * {
        box-sizing: border-box;
    }

    #reviewsManagementRoot .btn,
    #reviewsManagementRoot button,
    #reviewsManagementRoot .form-control,
    #reviewsManagementRoot .form-select,
    #reviewsManagementRoot .badge,
    #reviewsManagementRoot .card,
    #reviewsManagementRoot .input-group-text,
    #reviewsManagementRoot .page-link,
    #reviewsManagementRoot .status-tab,
    #reviewsManagementRoot .review-thumb-mini,
    .review-modal-scope .btn,
    .review-modal-scope .form-control,
    .review-modal-scope .form-select,
    .review-modal-scope .modal-content,
    .review-modal-scope .badge {
        border-radius: var(--rv-radius) !important;
    }

    #reviewsManagementRoot .btn {
        font-family: inherit;
        font-weight: 600;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease, box-shadow .18s ease;
    }

    #reviewsManagementRoot .btn:focus,
    #reviewsManagementRoot button:focus,
    #reviewsManagementRoot .form-control:focus,
    #reviewsManagementRoot .form-select:focus {
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .12) !important;
    }

    #reviewsManagementRoot .btn-primary,
    .review-modal-scope .btn-primary {
        background: var(--rv-primary) !important;
        border-color: var(--rv-primary) !important;
        color: #fff !important;
    }

    #reviewsManagementRoot .btn-primary:hover,
    .review-modal-scope .btn-primary:hover {
        background: var(--rv-primary-dark) !important;
        border-color: var(--rv-primary-dark) !important;
        color: #fff !important;
    }

    #reviewsManagementRoot .btn-success {
        background: #16a34a !important;
        border-color: #16a34a !important;
        color: #fff !important;
    }

    #reviewsManagementRoot .btn-success:hover {
        background: #15803d !important;
        border-color: #15803d !important;
        color: #fff !important;
    }

    #reviewsManagementRoot .btn-dark {
        background: #172033 !important;
        border-color: #172033 !important;
        color: #fff !important;
    }

    #reviewsManagementRoot .btn-dark:hover {
        background: #29364d !important;
        border-color: #29364d !important;
        color: #fff !important;
    }

    #reviewsManagementRoot .btn-outline-primary {
        color: var(--rv-primary) !important;
        border-color: #bfdbfe !important;
        background: #fff !important;
    }

    #reviewsManagementRoot .btn-outline-primary:hover {
        color: #fff !important;
        background: var(--rv-primary) !important;
        border-color: var(--rv-primary) !important;
    }

    #reviewsManagementRoot .btn-outline-success {
        color: #15803d !important;
        border-color: #bbf7d0 !important;
        background: #fff !important;
    }

    #reviewsManagementRoot .btn-outline-success:hover {
        color: #fff !important;
        background: #16a34a !important;
        border-color: #16a34a !important;
    }

    #reviewsManagementRoot .btn-outline-dark {
        color: #334155 !important;
        border-color: #cbd5e1 !important;
        background: #fff !important;
    }

    #reviewsManagementRoot .btn-outline-dark:hover {
        color: #fff !important;
        background: #172033 !important;
        border-color: #172033 !important;
    }

    #reviewsManagementRoot .btn-outline-secondary {
        color: #475569 !important;
        border-color: #cbd5e1 !important;
        background: #fff !important;
    }

    #reviewsManagementRoot .btn-outline-secondary:hover {
        color: #172033 !important;
        background: #f1f5f9 !important;
        border-color: #94a3b8 !important;
    }

    #reviewsManagementRoot .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-height: 32px;
        padding: 6px 11px !important;
        font-size: 11px !important;
        line-height: 1.35;
        white-space: nowrap;
    }

    #reviewsManagementRoot .btn:disabled {
        opacity: .55;
        cursor: not-allowed;
    }

    #reviewsManagementRoot .font-monospace,
    #reviewsManagementRoot .bn-digit {
        font-family: 'Inter', sans-serif !important;
        font-variant-numeric: tabular-nums;
        letter-spacing: 0;
    }

    /* Header */

    #reviewsManagementRoot .reviews-header-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 16px 18px;
        background: #fff;
        border: 1px solid var(--rv-border);
        border-radius: 5px;
        margin-bottom: 14px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
    }

    #reviewsManagementRoot .page-heading {
        display: flex;
        align-items: center;
        gap: 11px;
    }

    #reviewsManagementRoot .page-heading-icon {
        width: 39px;
        height: 39px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 5px;
        background: #fff7ed;
        color: #ea580c;
        font-size: 16px;
    }

    #reviewsManagementRoot .page-heading h5 {
        margin: 0;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 16px;
        font-weight: 800;
        color: #172033;
        letter-spacing: -.35px;
    }

    #reviewsManagementRoot .page-heading p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 11px;
        line-height: 1.5;
    }

    #reviewsManagementRoot .header-actions-group {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 6px;
    }

    /* Metric Cards */

    #reviewsManagementRoot .metric-card {
        height: 100%;
        min-height: 105px;
        padding: 13px 14px;
        background: #fff;
        border: 1px solid var(--rv-border);
        border-left: 3px solid transparent !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
        transition: transform .18s ease, box-shadow .18s ease;
    }

    #reviewsManagementRoot .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(15, 23, 42, .07);
    }

    #reviewsManagementRoot .metric-card-blue {
        border-left-color: #2563eb !important;
        background: linear-gradient(120deg, #fff 60%, #eff6ff 100%);
    }

    #reviewsManagementRoot .metric-card-amber {
        border-left-color: #f59e0b !important;
        background: linear-gradient(120deg, #fff 60%, #fffbeb 100%);
    }

    #reviewsManagementRoot .metric-card-green {
        border-left-color: #16a34a !important;
        background: linear-gradient(120deg, #fff 60%, #f0fdf4 100%);
    }

    #reviewsManagementRoot .metric-card-purple {
        border-left-color: #9333ea !important;
        background: linear-gradient(120deg, #fff 60%, #faf5ff 100%);
    }

    #reviewsManagementRoot .metric-label {
        color: #64748b;
        font-size: 10.5px;
        font-weight: 700;
        line-height: 1.35;
    }

    #reviewsManagementRoot .metric-value {
        margin: 8px 0 3px;
        color: #172033;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 23px;
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -.5px;
    }

    #reviewsManagementRoot .metric-value small {
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0;
    }

    #reviewsManagementRoot .metric-description {
        color: #94a3b8;
        font-size: 9.5px;
        line-height: 1.4;
    }

    #reviewsManagementRoot .metric-icon-square {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 5px;
        font-size: 14px;
    }

    /* Shared panels */

    #reviewsManagementRoot .reviews-panel-card {
        background: #fff;
        border: 1px solid var(--rv-border);
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
    }

    /* Tabs */

    #reviewsManagementRoot .status-tabs-scroll-wrap {
        width: 100%;
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }

    #reviewsManagementRoot .status-tabs-scroll-wrap::-webkit-scrollbar {
        display: none;
    }

    #reviewsManagementRoot .status-tab {
        min-height: 33px;
        padding: 7px 12px !important;
        background: #fff;
        color: #64748b;
        border: 1px solid #e2e8f0 !important;
        font-size: 10.5px !important;
        font-weight: 700 !important;
        white-space: nowrap;
        transition: all .16s ease;
    }

    #reviewsManagementRoot .status-tab:hover {
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: #172033 !important;
    }

    #reviewsManagementRoot .status-tab.active-tab {
        background: #eff6ff !important;
        border-color: #93c5fd !important;
        color: #1d4ed8 !important;
        box-shadow: inset 0 -2px 0 #2563eb;
    }

    #reviewsManagementRoot .status-tab.active-tab:hover {
        background: #dbeafe !important;
        color: #1d4ed8 !important;
    }

    /* Filters */

    #reviewsManagementRoot .filter-panel {
        padding: 12px;
    }

    #reviewsManagementRoot .form-control,
    #reviewsManagementRoot .form-select {
        min-height: 35px;
        padding: 7px 10px;
        background-color: #fff;
        color: #334155;
        border: 1px solid #dbe2ea;
        font-family: inherit;
        font-size: 11px;
        box-shadow: none;
    }

    #reviewsManagementRoot .form-control::placeholder {
        color: #94a3b8;
    }

    #reviewsManagementRoot .form-control:focus,
    #reviewsManagementRoot .form-select:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10) !important;
    }

    #reviewsManagementRoot .input-group-text {
        min-width: 35px;
        justify-content: center;
        background: #f8fafc;
        border: 1px solid #dbe2ea;
        color: #64748b;
    }

    #reviewsManagementRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #reviewsManagementRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    #reviewsManagementRoot .search-spinner {
        z-index: 5;
        pointer-events: none;
    }

    #reviewsManagementRoot #bulkActionSelect {
        min-width: 130px;
    }

    /* Table */

    #reviewsManagementRoot .reviews-table-wrapper {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #reviewsManagementRoot .custom-reviews-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #reviewsManagementRoot .custom-reviews-table thead th {
        padding: 11px 10px;
        background: #f8fafc;
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
        font-size: 9.5px;
        font-weight: 800;
        letter-spacing: .045em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    #reviewsManagementRoot .custom-reviews-table tbody td {
        padding: 10px;
        color: #334155;
        font-size: 11px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }

    #reviewsManagementRoot .custom-reviews-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #reviewsManagementRoot .custom-reviews-table tbody tr {
        transition: background-color .15s ease;
    }

    #reviewsManagementRoot .custom-reviews-table tbody tr:hover td {
        background: #f8fbff;
    }

    #reviewsManagementRoot .custom-reviews-table .form-check-input {
        width: 15px;
        height: 15px;
        margin: 0;
        border-color: #cbd5e1;
        cursor: pointer;
    }

    #reviewsManagementRoot .custom-reviews-table .form-check-input:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    #reviewsManagementRoot .review-thumb-mini {
        width: 31px;
        height: 31px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: transform .15s ease;
    }

    #reviewsManagementRoot .review-thumb-mini:hover {
        transform: scale(1.06);
    }

    #reviewsManagementRoot .star-yellow {
        color: #f59e0b;
        font-size: 10px;
    }

    #reviewsManagementRoot .table-footer {
        min-height: 50px;
        padding: 10px 14px;
        background: #fbfcfe;
        border-top: 1px solid #e2e8f0;
    }

    #reviewsManagementRoot .pagination {
        margin: 0;
        gap: 3px;
        flex-wrap: wrap;
    }

    #reviewsManagementRoot .pagination .page-link {
        min-width: 29px;
        min-height: 29px;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 5px 9px;
        color: #475569;
        border: 1px solid #e2e8f0;
        font-size: 10px;
        box-shadow: none;
    }

    #reviewsManagementRoot .pagination .page-item.active .page-link {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }

    /* Loading */

    #reviewsManagementRoot #tableLoadingCover {
        z-index: 10;
        backdrop-filter: blur(1px);
    }

    /* Modal */

    .review-modal-scope .modal-content {
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 18px 55px rgba(15, 23, 42, .16) !important;
        overflow: hidden;
    }

    .review-modal-scope .modal-header {
        min-height: 48px;
        background: #f8fafc !important;
    }

    .review-modal-scope .modal-title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px !important;
    }

    .review-modal-scope .modal-body {
        background: #fff;
    }

    .review-modal-scope .form-control {
        border: 1px solid #dbe2ea;
        font-family: 'Inter', sans-serif;
        font-size: 11px;
    }

    .review-modal-scope .form-control:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
    }

    .review-modal-scope .modal-dialog {
        padding: 10px;
    }

    /* Desktop table proportions */

    @media (min-width: 1200px) {
        #reviewsManagementRoot .custom-reviews-table {
            table-layout: fixed;
        }

        #reviewsManagementRoot .col-rev-chk { width: 4%; }
        #reviewsManagementRoot .col-rev-product { width: 21%; }
        #reviewsManagementRoot .col-rev-customer { width: 15%; }
        #reviewsManagementRoot .col-rev-rating { width: 10%; }
        #reviewsManagementRoot .col-rev-content { width: 25%; }
        #reviewsManagementRoot .col-rev-status { width: 9%; }
        #reviewsManagementRoot .col-rev-visible { width: 7%; }
        #reviewsManagementRoot .col-rev-actions { width: 9%; }

        #reviewsManagementRoot .custom-reviews-table td,
        #reviewsManagementRoot .custom-reviews-table th {
            overflow: hidden;
            text-overflow: ellipsis;
        }
    }

    /* Tablet */

    @media (max-width: 1199.98px) {
        #reviewsManagementRoot .custom-reviews-table {
            min-width: 1050px;
        }
    }

    /* Mobile */

    @media (max-width: 767.98px) {
        #reviewsManagementRoot {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #reviewsManagementRoot .reviews-header-card {
            padding: 13px;
            align-items: flex-start !important;
        }

        #reviewsManagementRoot .page-heading h5 {
            font-size: 15px;
        }

        #reviewsManagementRoot .header-actions-group {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 6px;
        }

        #reviewsManagementRoot .header-actions-group .btn {
            width: 100%;
            min-height: 35px;
            padding: 7px 5px !important;
            font-size: 10px !important;
        }

        #reviewsManagementRoot .metric-card {
            min-height: 91px;
            padding: 10px;
        }

        #reviewsManagementRoot .metric-value {
            margin-top: 7px;
            font-size: 19px;
        }

        #reviewsManagementRoot .metric-label {
            font-size: 9.5px;
        }

        #reviewsManagementRoot .metric-icon-square {
            width: 28px;
            height: 28px;
            font-size: 11px;
        }

        #reviewsManagementRoot .filter-panel {
            padding: 10px;
        }

        #reviewsManagementRoot .filter-panel .form-control,
        #reviewsManagementRoot .filter-panel .form-select {
            min-height: 36px;
        }

        #reviewsManagementRoot .custom-reviews-table {
            min-width: 1050px;
        }

        #reviewsManagementRoot .table-footer {
            justify-content: center !important;
            padding: 10px;
        }

        .review-modal-scope .modal-dialog {
            margin: 8px auto;
        }

        .review-modal-scope .modal-body {
            padding: 12px !important;
        }

        .review-modal-scope .modal-body .border-end {
            border-right: 0 !important;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 14px;
        }
    }

    @media (max-width: 380px) {
        #reviewsManagementRoot .metric-value {
            font-size: 17px;
        }

        #reviewsManagementRoot .metric-label {
            font-size: 9px;
        }

        #reviewsManagementRoot .header-actions-group .btn {
            font-size: 9px !important;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #reviewsManagementRoot *,
        .review-modal-scope * {
            transition: none !important;
            animation-duration: .01ms !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="reviewsManagementRoot">

    {{-- =====================================================
         PAGE HEADER
    ====================================================== --}}
    <div class="reviews-header-card flex-column flex-md-row">

        <div class="page-heading">
            <div class="page-heading-icon">
                <i class="fas fa-star"></i>
            </div>

            <div>
                <h5>Product Reviews</h5>
                <p>Manage customer feedback, moderate reviews and respond to customers.</p>
            </div>
        </div>

        <div class="header-actions-group">
            <a href="{{ route('admin.product.reviews.dashboard') }}"
               class="btn btn-outline-primary btn-compact">
                <i class="fas fa-chart-line"></i>
                Analytics
            </a>

            <a href="{{ route('admin.product.reviews.auto_rules') }}"
               class="btn btn-outline-success btn-compact">
                <i class="fas fa-robot"></i>
                Auto-Reply Rules
            </a>

            <a href="{{ route('admin.product.reviews.settings') }}"
               class="btn btn-outline-dark btn-compact">
                <i class="fas fa-gear"></i>
                Settings
            </a>

            <button type="button"
                    class="btn btn-success btn-compact"
                    onclick="exportReviews('csv')">
                <i class="fas fa-file-csv"></i>
                Export CSV
            </button>
        </div>
    </div>

    {{-- =====================================================
         KPI OVERVIEW
    ====================================================== --}}
    <div class="row g-2 mb-3">

        <div class="col-6 col-xl-3">
            <div class="metric-card metric-card-blue">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <span class="metric-label">Total Reviews</span>
                    <div class="metric-icon-square bg-primary-subtle text-primary">
                        <i class="fas fa-comments"></i>
                    </div>
                </div>

                <div class="metric-value bn-digit">
                    {{ number_format($totalReviewsCount) }}
                </div>

                <div class="metric-description">
                    Overall feedback received
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="metric-card metric-card-amber">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <span class="metric-label">Pending Moderation</span>
                    <div class="metric-icon-square bg-warning-subtle text-warning-emphasis">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>

                <div class="metric-value bn-digit text-warning-emphasis">
                    {{ number_format($pendingCount) }}
                </div>

                <div class="metric-description">
                    Awaiting admin review
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="metric-card metric-card-green">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <span class="metric-label">Approved &amp; Live</span>
                    <div class="metric-icon-square bg-success-subtle text-success">
                        <i class="fas fa-circle-check"></i>
                    </div>
                </div>

                <div class="metric-value bn-digit text-success">
                    {{ number_format($approvedCount) }}
                </div>

                <div class="metric-description">
                    <i class="fas fa-bolt me-1"></i>
                    Visible on storefront
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="metric-card metric-card-purple">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <span class="metric-label">Pinned &amp; Media</span>
                    <div class="metric-icon-square"
                         style="background:#f3e8ff;color:#9333ea;">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>

                <div class="metric-value bn-digit">
                    {{ number_format($pinnedCount) }}
                    <small class="text-muted">
                        / {{ number_format($withImagesCount) }} media
                    </small>
                </div>

                <div class="metric-description">
                    Featured customer reviews
                </div>
            </div>
        </div>

    </div>

    {{-- =====================================================
         STATUS TABS
    ====================================================== --}}
    <div class="reviews-panel-card mb-2 p-2">
        <div class="status-tabs-scroll-wrap">
            <div class="d-inline-flex align-items-center gap-1" id="reviewStatusTabs">

                <button type="button"
                        class="btn status-tab {{ empty(request('status')) && empty(request('visibility')) ? 'active-tab fw-bold' : '' }}"
                        data-status=""
                        data-visibility="">
                    All Reviews
                    (<span class="tab-all-count bn-digit">{{ $tabCounts['all'] ?? 0 }}</span>)
                </button>

                <button type="button"
                        class="btn status-tab {{ request('status') === 'pending' ? 'active-tab fw-bold' : '' }}"
                        data-status="pending"
                        data-visibility="">
                    Pending
                    (<span class="tab-pending-count bn-digit">{{ $tabCounts['pending'] ?? 0 }}</span>)
                </button>

                <button type="button"
                        class="btn status-tab {{ request('status') === 'approved' && request('visibility') !== 'hidden' ? 'active-tab fw-bold' : '' }}"
                        data-status="approved"
                        data-visibility="visible">
                    Approved
                    (<span class="tab-approved-count bn-digit">{{ $tabCounts['approved'] ?? 0 }}</span>)
                </button>

                <button type="button"
                        class="btn status-tab {{ request('visibility') === 'hidden' ? 'active-tab fw-bold' : '' }}"
                        data-status=""
                        data-visibility="hidden">
                    Hidden
                    (<span class="tab-hidden-count bn-digit">{{ $tabCounts['hidden'] ?? 0 }}</span>)
                </button>

                <button type="button"
                        class="btn status-tab {{ request('status') === 'rejected' ? 'active-tab fw-bold' : '' }}"
                        data-status="rejected"
                        data-visibility="">
                    Rejected
                    (<span class="tab-rejected-count bn-digit">{{ $tabCounts['rejected'] ?? 0 }}</span>)
                </button>

                <button type="button"
                        class="btn status-tab"
                        data-filter="has_images"
                        data-val="yes">
                    <i class="fas fa-image me-1"></i>
                    With Images
                    (<span class="tab-images-count bn-digit">{{ $tabCounts['with_images'] ?? 0 }}</span>)
                </button>

                <button type="button"
                        class="btn status-tab"
                        data-filter="pinned"
                        data-val="yes">
                    <i class="fas fa-thumbtack me-1"></i>
                    Pinned
                    (<span class="tab-pinned-count bn-digit">{{ $tabCounts['pinned'] ?? 0 }}</span>)
                </button>

            </div>
        </div>
    </div>

    {{-- =====================================================
         FILTER & BULK ACTIONS
    ====================================================== --}}
    <div class="reviews-panel-card filter-panel mb-2">
        <div class="row g-2 align-items-center">

            <div class="col-12 col-md-4 col-xl-3">
                <div class="input-group position-relative">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>

                    <input type="text"
                           id="reviewSearchInput"
                           class="form-control"
                           placeholder="Search product, customer or comment...">

                    <span id="searchSpinner"
                          class="spinner-border spinner-border-sm text-primary position-absolute end-0 me-2 top-50 translate-middle-y d-none search-spinner">
                    </span>
                </div>
            </div>

            <div class="col-6 col-md-2 col-xl-2">
                <select id="filterRating" class="form-select">
                    <option value="all">All Ratings</option>
                    <option value="5">5 Stars</option>
                    <option value="4">4 Stars</option>
                    <option value="3">3 Stars</option>
                    <option value="2">2 Stars</option>
                    <option value="1">1 Star</option>
                </select>
            </div>

            <div class="col-6 col-md-2 col-xl-2">
                <select id="filterVerified" class="form-select">
                    <option value="all">All Buyers</option>
                    <option value="yes">Verified Buyer</option>
                    <option value="no">Non-Verified</option>
                </select>
            </div>

            <div class="col-6 col-md-2 col-xl-2">
                <select id="filterReplied" class="form-select">
                    <option value="all">All Replies</option>
                    <option value="yes">Replied</option>
                    <option value="no">Unanswered</option>
                </select>
            </div>

            <div class="col-6 col-md-2 col-xl-1">
                <select id="perPageSelect" class="form-select fw-bold">
                    <option value="20" selected>20 / Page</option>
                    <option value="50">50 / Page</option>
                    <option value="100">100 / Page</option>
                    <option value="all">All</option>
                </select>
            </div>

            <div class="col-12 col-xl-2">
                <div class="d-flex align-items-center gap-1 justify-content-end">
                    <select id="bulkActionSelect"
                            class="form-select"
                            disabled>
                        <option value="">Bulk Actions</option>
                        <option value="approve">Approve</option>
                        <option value="hide">Hide</option>
                        <option value="unhide">Unhide</option>
                        <option value="reject">Reject</option>
                        <option value="delete">Delete</option>
                    </select>

                    <button type="button"
                            id="bulkApplyBtn"
                            class="btn btn-dark btn-compact px-3"
                            onclick="applyBulkAction()"
                            disabled>
                        Apply
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- =====================================================
         REVIEWS TABLE
    ====================================================== --}}
    <div class="reviews-panel-card position-relative overflow-hidden mb-3">

        <div id="tableLoadingCover"
             class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none justify-content-center align-items-center"
             style="z-index:10;">
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        </div>

        <div class="reviews-table-wrapper">
            <table class="table custom-reviews-table align-middle">

                <thead>
                    <tr>
                        <th class="ps-3 text-center col-rev-chk">
                            <input type="checkbox"
                                   class="form-check-input"
                                   id="selectAllCheckbox">
                        </th>

                        <th class="col-rev-product">Product</th>
                        <th class="col-rev-customer">Customer</th>
                        <th class="col-rev-rating">Rating</th>
                        <th class="col-rev-content">Review Details</th>
                        <th class="text-center col-rev-status">Status</th>
                        <th class="text-center col-rev-visible">Visible</th>
                        <th class="text-end pe-3 col-rev-actions">Actions</th>
                    </tr>
                </thead>

                <tbody id="reviewsTableBody">
                    @include('admin.reviews.partials.review_rows')
                </tbody>

            </table>
        </div>

        <div class="table-footer d-flex justify-content-center align-items-center"
             id="paginationWrapper">
            @if($reviews->hasPages())
                {{ $reviews->links('pagination::bootstrap-5') }}
            @endif
        </div>

    </div>

</div>

{{-- =========================================================
     REVIEW DETAILS & REPLY MODAL
========================================================= --}}
<div class="modal fade review-modal-scope"
     id="reviewDetailModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index:108000;">

    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header py-3 px-3 border-bottom">
                <h6 class="modal-title fw-bold text-dark mb-0"
                    id="modalReviewTitle">
                    <i class="fas fa-magnifying-glass text-primary me-2"></i>
                    Review Inspection &amp; Response
                </h6>

                <button type="button"
                        class="btn-close shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>
            </div>

            <div class="modal-body p-3">
                <div class="row g-3">

                    <div class="col-md-6 border-end">

                        <div class="mb-3">
                            <small class="text-muted fw-bold d-block mb-2"
                                   style="font-size:10px;letter-spacing:.05em;">
                                PRODUCT &amp; ORDER
                            </small>

                            <h6 class="fw-bold text-dark mb-1"
                                id="mProductTitle"
                                style="font-size:13px;">
                                Product Title
                            </h6>

                            <p class="small text-muted mb-0"
                               id="mOrderInvoice">
                                Order: N/A
                            </p>
                        </div>

                        <div class="border-top pt-3">
                            <small class="text-muted fw-bold d-block mb-2"
                                   style="font-size:10px;letter-spacing:.05em;">
                                CUSTOMER INFORMATION
                            </small>

                            <div class="small">
                                <span class="d-block text-dark fw-bold mb-1"
                                      id="mCustomerName">
                                    Customer
                                </span>

                                <span class="d-block text-muted mb-1"
                                      id="mCustomerEmail">
                                    Email: N/A
                                </span>

                                <span class="d-block text-muted mb-2"
                                      id="mCustomerPhone">
                                    Phone: N/A
                                </span>

                                <span class="badge bg-success-subtle text-success border border-success-subtle"
                                      id="mVerifiedBadge"
                                      style="font-size:9.5px;">
                                    Verified Buyer
                                </span>
                            </div>
                        </div>

                    </div>

                    <div class="col-md-6">

                        <small class="text-muted fw-bold d-block mb-2"
                               style="font-size:10px;letter-spacing:.05em;">
                            REVIEW CONTENT
                        </small>

                        <div class="mb-2" id="mRatingStars"></div>

                        <p class="small text-dark p-3 bg-light border mb-3"
                           id="mReviewComment"
                           style="font-size:11.5px;line-height:1.7;white-space:pre-wrap;">
                        </p>

                        <div class="mb-3" id="mImagesBox"></div>

                        <div class="border-top pt-3">
                            <label class="form-label fw-bold small mb-2">
                                <i class="fas fa-reply text-primary me-1"></i>
                                Admin Reply
                            </label>

                            <textarea id="mAdminReplyText"
                                      class="form-control mb-2"
                                      rows="3"
                                      placeholder="Write your reply to the customer..."></textarea>

                            <div class="d-flex justify-content-end">
                                <button type="button"
                                        class="btn btn-primary btn-compact px-3"
                                        id="btnSaveReply"
                                        onclick="saveModalReply()">
                                    <i class="fas fa-paper-plane"></i>
                                    Post Reply
                                </button>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

{{-- =========================================================
     ADMIN ALERT MODAL
========================================================= --}}
<div class="modal fade review-modal-scope"
     id="adminAlertModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index:108500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-3">

            <div class="modal-body p-2">
                <div id="adminAlertIcon"
                     class="mb-3"
                     style="font-size:34px;">
                </div>

                <h6 id="adminAlertTitle"
                    class="fw-bold mb-2 text-dark">
                </h6>

                <p id="adminAlertMessage"
                   class="text-muted small mb-3"
                   style="font-size:11px;line-height:1.6;">
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

{{-- =========================================================
     ADMIN CONFIRMATION MODAL
========================================================= --}}
<div class="modal fade review-modal-scope"
     id="adminConfirmModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index:108600;">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-3">

            <div class="modal-body p-2">

                <div class="mb-3 text-warning" style="font-size:34px;">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>

                <h6 id="adminConfirmTitle"
                    class="fw-bold mb-2 text-dark">
                    Confirm Action
                </h6>

                <p id="adminConfirmMessage"
                   class="text-muted small mb-3"
                   style="font-size:11px;line-height:1.6;">
                </p>

                <div class="d-flex justify-content-center gap-2">
                    <button type="button"
                            class="btn btn-light btn-compact border"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="button"
                            class="btn btn-danger btn-compact px-3"
                            id="adminConfirmExecuteBtn">
                        Proceed
                    </button>
                </div>

            </div>

        </div>
    </div>
</div>

<script>
    let activeReviewId = null;
    let currentStatus = '';
    let currentVisibility = '';
    let currentFilter = '';
    let currentVal = '';
    let debounceTimer;
    let confirmCallback = null;

    // =========================================================
    // ALERT MODAL
    // =========================================================

    function showAdminAlert(title, message, type = 'info') {
        const modalEl = document.getElementById('adminAlertModal');
        const iconEl = document.getElementById('adminAlertIcon');
        const titleEl = document.getElementById('adminAlertTitle');
        const msgEl = document.getElementById('adminAlertMessage');

        if (!modalEl || !iconEl || !titleEl || !msgEl) return;

        const icons = {
            success: '<i class="fas fa-circle-check text-success"></i>',
            error: '<i class="fas fa-circle-xmark text-danger"></i>',
            info: '<i class="fas fa-circle-info text-primary"></i>'
        };

        iconEl.innerHTML = icons[type] || icons.info;
        titleEl.innerText = title;
        msgEl.innerText = message;

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    // =========================================================
    // CONFIRMATION MODAL
    // =========================================================

    function showAdminConfirm(title, message, onConfirm) {
        const modalEl = document.getElementById('adminConfirmModal');
        const titleEl = document.getElementById('adminConfirmTitle');
        const msgEl = document.getElementById('adminConfirmMessage');

        if (!modalEl || !titleEl || !msgEl) return;

        titleEl.innerText = title;
        msgEl.innerText = message;
        confirmCallback = onConfirm;

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    // =========================================================
    // BULK SELECTION STATE
    // =========================================================

    function syncBulkControls() {
        const bulkSelect = document.getElementById('bulkActionSelect');
        const bulkBtn = document.getElementById('bulkApplyBtn');

        const checkedCount = document.querySelectorAll(
            '.review-row-check:checked'
        ).length;

        if (bulkSelect) {
            bulkSelect.disabled = checkedCount === 0;
        }

        if (bulkBtn) {
            bulkBtn.disabled = checkedCount === 0;
        }

        const selectAll = document.getElementById('selectAllCheckbox');
        const allRows = document.querySelectorAll('.review-row-check');

        if (selectAll) {
            selectAll.checked =
                allRows.length > 0 &&
                checkedCount === allRows.length;

            selectAll.indeterminate =
                checkedCount > 0 &&
                checkedCount < allRows.length;
        }
    }

    // =========================================================
    // DOM READY
    // =========================================================

    document.addEventListener('DOMContentLoaded', function () {

        const searchIn = document.getElementById('reviewSearchInput');

        const statusTabs = document.querySelectorAll(
            '#reviewStatusTabs .status-tab'
        );

        const confirmBtn = document.getElementById(
            'adminConfirmExecuteBtn'
        );

        // Confirmation handler
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function () {

                const callback = confirmCallback;
                confirmCallback = null;

                const modal = bootstrap.Modal.getInstance(
                    document.getElementById('adminConfirmModal')
                );

                if (modal) {
                    modal.hide();
                }

                if (typeof callback === 'function') {
                    callback();
                }
            });
        }

        // Search debounce
        if (searchIn) {
            searchIn.addEventListener('input', function () {
                clearTimeout(debounceTimer);

                debounceTimer = setTimeout(function () {
                    fetchReviews(1);
                }, 300);
            });
        }

        // Status tabs
        statusTabs.forEach(function (tab) {

            tab.addEventListener('click', function () {

                statusTabs.forEach(function (item) {
                    item.classList.remove('active-tab', 'fw-bold');
                });

                this.classList.add('active-tab', 'fw-bold');

                currentStatus =
                    this.getAttribute('data-status') || '';

                currentVisibility =
                    this.getAttribute('data-visibility') || '';

                currentFilter =
                    this.getAttribute('data-filter') || '';

                currentVal =
                    this.getAttribute('data-val') || '';

                fetchReviews(1);
            });
        });

        // Filters
        document.getElementById('filterRating')
            ?.addEventListener('change', () => fetchReviews(1));

        document.getElementById('filterVerified')
            ?.addEventListener('change', () => fetchReviews(1));

        document.getElementById('filterReplied')
            ?.addEventListener('change', () => fetchReviews(1));

        document.getElementById('perPageSelect')
            ?.addEventListener('change', () => fetchReviews(1));

        // Master checkbox
        const selectAll = document.getElementById('selectAllCheckbox');

        if (selectAll) {
            selectAll.addEventListener('change', function () {

                const isChecked = this.checked;

                document.querySelectorAll('.review-row-check')
                    .forEach(function (checkbox) {
                        checkbox.checked = isChecked;
                    });

                syncBulkControls();
            });
        }

        // Row checkbox event delegation
        document.addEventListener('change', function (event) {

            if (
                event.target &&
                event.target.classList.contains('review-row-check')
            ) {
                syncBulkControls();
            }
        });

        syncBulkControls();
    });

    // =========================================================
    // FETCH REVIEWS
    // =========================================================

    function fetchReviews(page = 1) {

        const tableBody = document.getElementById('reviewsTableBody');
        const spinner = document.getElementById('searchSpinner');
        const cover = document.getElementById('tableLoadingCover');
        const pagination = document.getElementById('paginationWrapper');

        if (!tableBody || !pagination) return;

        if (spinner) {
            spinner.classList.remove('d-none');
        }

        if (cover) {
            cover.classList.remove('d-none');
            cover.classList.add('d-flex');
        }

        const url = new URL(
            "{{ route('admin.product.reviews.index') }}",
            window.location.origin
        );

        url.searchParams.set('page', page);

        url.searchParams.set(
            'search',
            document.getElementById('reviewSearchInput').value.trim()
        );

        url.searchParams.set(
            'rating',
            document.getElementById('filterRating').value
        );

        url.searchParams.set(
            'verified',
            document.getElementById('filterVerified').value
        );

        url.searchParams.set(
            'replied',
            document.getElementById('filterReplied').value
        );

        url.searchParams.set(
            'per_page',
            document.getElementById('perPageSelect').value
        );

        if (currentStatus) {
            url.searchParams.set('status', currentStatus);
        }

        if (currentVisibility) {
            url.searchParams.set('visibility', currentVisibility);
        }

        if (currentFilter && currentVal) {
            url.searchParams.set(currentFilter, currentVal);
        }

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function (res) {
            if (!res.ok) {
                throw new Error('Request failed');
            }

            return res.json();
        })
        .then(function (data) {

            tableBody.innerHTML = data.table_html;
            pagination.innerHTML = data.pagination_html;

            // Dynamic tab counters
            if (data.counts) {

                const mapCounts = {
                    '.tab-all-count': data.counts.all,
                    '.tab-pending-count': data.counts.pending,
                    '.tab-approved-count': data.counts.approved,
                    '.tab-hidden-count': data.counts.hidden,
                    '.tab-rejected-count': data.counts.rejected,
                    '.tab-images-count': data.counts.with_images,
                    '.tab-pinned-count': data.counts.pinned
                };

                Object.entries(mapCounts).forEach(function ([selector, countVal]) {

                    const element = document.querySelector(selector);

                    if (element && countVal !== undefined) {
                        element.innerText = countVal;
                    }
                });
            }

            syncBulkControls();
        })
        .catch(function () {
            showAdminAlert(
                'Error',
                'Failed to retrieve reviews data.',
                'error'
            );
        })
        .finally(function () {

            if (spinner) {
                spinner.classList.add('d-none');
            }

            if (cover) {
                cover.classList.add('d-none');
                cover.classList.remove('d-flex');
            }
        });
    }

    // =========================================================
    // UPDATE REVIEW STATUS
    // =========================================================

    function updateReviewStatus(id, status) {

        fetch("{{ url('admin/product/reviews/status') }}/" + id, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: status })
        })
        .then(res => res.json())
        .then(function (data) {

            if (data.status === 'success') {
                fetchReviews();
            } else {
                showAdminAlert(
                    'Status Update',
                    data.message || 'Operation failed.',
                    'error'
                );
            }
        })
        .catch(function () {
            showAdminAlert(
                'Error',
                'Network error occurred.',
                'error'
            );
        });
    }

    // =========================================================
    // TOGGLE VISIBILITY
    // =========================================================

    function toggleVisibility(id) {

        fetch("{{ url('admin/product/reviews/toggle-visibility') }}/" + id, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(function (data) {

            if (data.status === 'success') {
                fetchReviews();
            } else {
                showAdminAlert(
                    'Visibility',
                    data.message || 'Could not update visibility.',
                    'error'
                );
            }
        })
        .catch(function () {
            showAdminAlert(
                'Error',
                'Network error occurred.',
                'error'
            );
        });
    }

    // =========================================================
    // TOGGLE PIN
    // =========================================================

    function togglePin(id) {

        fetch("{{ url('admin/product/reviews/toggle-pin') }}/" + id, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(function (data) {

            showAdminAlert(
                'Pin Status',
                data.message || 'Pin status updated.',
                data.status === 'success' ? 'success' : 'error'
            );

            if (data.status === 'success') {
                fetchReviews();
            }
        })
        .catch(function () {
            showAdminAlert(
                'Error',
                'Failed to update pin state.',
                'error'
            );
        });
    }

    // =========================================================
    // DELETE REVIEW
    // =========================================================

    function deleteReview(id) {

        showAdminConfirm(
            'Delete Review',
            'Are you sure you want to permanently delete this review?',
            function () {

                fetch("{{ url('admin/product/reviews/destroy') }}/" + id, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(function (data) {

                    if (data.status === 'success') {

                        showAdminAlert(
                            'Deleted',
                            data.message || 'Review deleted successfully.',
                            'success'
                        );

                        fetchReviews();

                    } else {

                        showAdminAlert(
                            'Delete Failed',
                            data.message || 'Unable to delete review.',
                            'error'
                        );
                    }
                })
                .catch(function () {
                    showAdminAlert(
                        'Error',
                        'Failed to delete review.',
                        'error'
                    );
                });
            }
        );
    }

    // =========================================================
    // REVIEW DETAILS MODAL
    // =========================================================

    function openInspectModal(id) {

        activeReviewId = id;

        fetch("{{ url('admin/product/reviews/show') }}/" + id)
        .then(res => res.json())
        .then(function (data) {

            if (data.status === 'success') {

                const review = data.review;

                document.getElementById('mProductTitle').innerText =
                    review.product_title || 'Product #' + review.product_id;

                document.getElementById('mOrderInvoice').innerText =
                    review.order_invoice_no
                        ? 'Invoice: #' + review.order_invoice_no
                        : 'Order: Direct / N/A';

                document.getElementById('mCustomerName').innerText =
                    review.customer_name || 'Guest';

                document.getElementById('mCustomerEmail').innerText =
                    'Email: ' + (review.customer_email || 'N/A');

                document.getElementById('mCustomerPhone').innerText =
                    'Phone: ' + (review.customer_phone || 'N/A');

                document.getElementById('mReviewComment').innerText =
                    review.comment || '';

                document.getElementById('mAdminReplyText').value =
                    review.admin_reply || '';

                // Rating stars
                let stars = '';

                for (let i = 1; i <= 5; i++) {
                    stars += `
                        <i class="fas fa-star ${
                            i <= review.rating
                                ? 'star-yellow'
                                : 'text-muted opacity-25'
                        }"></i>
                    `;
                }

                document.getElementById('mRatingStars').innerHTML = stars;

                // Verified buyer badge
                const verifiedBadge = document.getElementById('mVerifiedBadge');

                if (verifiedBadge) {
                    verifiedBadge.innerText = review.is_verified
                        ? 'Verified Buyer'
                        : 'Unverified Buyer';

                    verifiedBadge.className = review.is_verified
                        ? 'badge bg-success-subtle text-success border border-success-subtle'
                        : 'badge bg-secondary-subtle text-secondary border border-secondary-subtle';
                }

                // Review images
                let imgsHtml = '';

                if (review.images && review.images.length > 0) {

                    imgsHtml = `
                        <small class="text-muted fw-bold d-block mb-2"
                               style="font-size:10px;">
                            ATTACHED IMAGES
                        </small>
                        <div class="d-flex gap-2 flex-wrap">
                    `;

                    review.images.forEach(function (img) {

                        const cleanImg = (
                            img.startsWith('http') || img.startsWith('/')
                        ) ? img : '/' + img;

                        imgsHtml += `
                            <a href="${cleanImg}" target="_blank" rel="noopener noreferrer">
                                <img src="${cleanImg}"
                                     class="review-thumb-mini"
                                     alt="Review image"
                                     onerror="this.onerror=null;this.style.display='none'">
                            </a>
                        `;
                    });

                    imgsHtml += '</div>';
                }

                document.getElementById('mImagesBox').innerHTML = imgsHtml;

                bootstrap.Modal.getOrCreateInstance(
                    document.getElementById('reviewDetailModal')
                ).show();

            } else {

                showAdminAlert(
                    'Error',
                    data.message || 'Review details not found.',
                    'error'
                );
            }
        })
        .catch(function () {
            showAdminAlert(
                'Error',
                'Failed to inspect review.',
                'error'
            );
        });
    }

    // =========================================================
    // SAVE ADMIN REPLY
    // =========================================================

    function saveModalReply() {

        if (!activeReviewId) return;

        const replyText = document.getElementById(
            'mAdminReplyText'
        ).value.trim();

        if (!replyText) {
            showAdminAlert(
                'Validation',
                'Reply text cannot be empty.',
                'error'
            );
            return;
        }

        const saveButton = document.getElementById('btnSaveReply');

        if (saveButton) {
            saveButton.disabled = true;
        }

        fetch("{{ url('admin/product/reviews/reply') }}/" + activeReviewId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                reply_text: replyText
            })
        })
        .then(res => res.json())
        .then(function (data) {

            if (data.status === 'success') {

                const inspectModal = bootstrap.Modal.getInstance(
                    document.getElementById('reviewDetailModal')
                );

                if (inspectModal) {
                    inspectModal.hide();
                }

                showAdminAlert(
                    'Success',
                    data.message || 'Reply posted successfully.',
                    'success'
                );

                fetchReviews();

            } else {

                showAdminAlert(
                    'Reply Failed',
                    data.message || 'Unable to post reply.',
                    'error'
                );
            }
        })
        .catch(function () {
            showAdminAlert(
                'Error',
                'Failed to post admin reply.',
                'error'
            );
        })
        .finally(function () {

            if (saveButton) {
                saveButton.disabled = false;
            }
        });
    }

    // =========================================================
    // BULK ACTIONS
    // =========================================================

    function applyBulkAction() {

        const action = document.getElementById(
            'bulkActionSelect'
        ).value;

        const ids = Array.from(
            document.querySelectorAll('.review-row-check:checked')
        ).map(function (checkbox) {
            return checkbox.value;
        });

        if (!action || ids.length === 0) return;

        const executeBulk = function () {

            fetch("{{ route('admin.product.reviews.bulk_action') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    action: action,
                    ids: ids
                })
            })
            .then(res => res.json())
            .then(function (data) {

                if (data.status === 'success') {

                    showAdminAlert(
                        'Bulk Action Result',
                        data.message || 'Action executed successfully.',
                        'success'
                    );

                    fetchReviews();

                } else {

                    showAdminAlert(
                        'Bulk Action Failed',
                        data.message || 'Unable to execute bulk action.',
                        'error'
                    );
                }
            })
            .catch(function () {
                showAdminAlert(
                    'Error',
                    'Bulk action failed.',
                    'error'
                );
            });
        };

        if (action === 'delete') {

            showAdminConfirm(
                'Bulk Delete',
                `Are you sure you want to permanently delete ${ids.length} selected reviews?`,
                executeBulk
            );

        } else {

            executeBulk();
        }
    }

    // =========================================================
    // CSV EXPORT
    // =========================================================

    function exportReviews(type) {

        const url = new URL(
            "{{ route('admin.product.reviews.index') }}",
            window.location.origin
        );

        url.searchParams.set('export', type);

        window.open(url.toString(), '_blank');
    }
</script>

@endsection