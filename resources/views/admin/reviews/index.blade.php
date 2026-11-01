@extends('layouts.admin')

@section('title', 'Product Reviews Management - ' . config('app.name'))

@php
    $totalReviewsCount  = $tabCounts['all'] ?? 0;
    $pendingCount       = $tabCounts['pending'] ?? 0;
    $approvedCount      = $tabCounts['approved'] ?? 0;
    $pinnedCount        = $tabCounts['pinned'] ?? 0;
    $withImagesCount    = $tabCounts['with_images'] ?? 0;
@endphp

@section('admin_content')
<!-- Google Fonts: Plus Jakarta Sans & Inter -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* Professional Typography & Strict 5px Element Radius */
    #reviewsManagementRoot,
    .review-modal-scope {
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        letter-spacing: -0.01em;
        color: #1e293b;
    }

    #reviewsManagementRoot .btn,
    #reviewsManagementRoot .btn-sm,
    #reviewsManagementRoot button,
    #reviewsManagementRoot .form-control,
    #reviewsManagementRoot .form-select,
    #reviewsManagementRoot .badge,
    #reviewsManagementRoot .alert,
    #reviewsManagementRoot .input-group-text,
    #reviewsManagementRoot .card,
    #reviewsManagementRoot .metric-card,
    #reviewsManagementRoot .reviews-header-card,
    #reviewsManagementRoot .reviews-panel-card,
    #reviewsManagementRoot .status-tab,
    #reviewsManagementRoot .review-thumb-mini,
    #reviewsManagementRoot .page-link,
    #reviewsManagementRoot .page-item span,
    .review-modal-scope .btn,
    .review-modal-scope .form-control,
    .review-modal-scope .form-select,
    .review-modal-scope .badge,
    .review-modal-scope .modal-content {
        border-radius: 5px !important;
    }

    #reviewsManagementRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
        background-color: #f8fafc;
        border-color: #cbd5e1;
    }
    #reviewsManagementRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    .bn-digit,
    .font-monospace {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
    }

    .btn-compact {
        font-size: 11px !important;
        padding: 6px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    /* Fixed Button Hover Colors (Text & Background Never Clash) */
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

    .btn-success {
        background-color: #16a34a !important;
        border-color: #16a34a !important;
        color: #ffffff !important;
    }
    .btn-success:hover {
        background-color: #15803d !important;
        border-color: #15803d !important;
        color: #ffffff !important;
    }

    .btn-dark {
        background-color: #0f172a !important;
        border-color: #0f172a !important;
        color: #ffffff !important;
    }
    .btn-dark:hover:not(:disabled) {
        background-color: #1e293b !important;
        border-color: #1e293b !important;
        color: #ffffff !important;
    }

    .btn-outline-primary {
        background-color: #ffffff !important;
        border-color: #2563eb !important;
        color: #2563eb !important;
    }
    .btn-outline-primary:hover {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
    }

    .btn-outline-success {
        background-color: #ffffff !important;
        border-color: #16a34a !important;
        color: #16a34a !important;
    }
    .btn-outline-success:hover {
        background-color: #16a34a !important;
        border-color: #16a34a !important;
        color: #ffffff !important;
    }

    .btn-outline-dark {
        background-color: #ffffff !important;
        border-color: #334155 !important;
        color: #334155 !important;
    }
    .btn-outline-dark:hover {
        background-color: #0f172a !important;
        border-color: #0f172a !important;
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

    /* Clean White Enterprise Header Card */
    .reviews-header-card {
        background: #ffffff;
        color: #0f172a;
        padding: 14px 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 14px;
    }

    /* Metric Cards Architecture */
    .metric-card {
        padding: 12px 14px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }
    .stat-label-title {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.02em;
        line-height: 1.2;
    }
    .stat-val-num {
        font-size: 17px;
        line-height: 1.25;
        letter-spacing: -0.01em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .stat-sub-text {
        font-size: 10px;
        margin-top: 2px;
    }
    .metric-icon-square {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
        border-radius: 5px !important;
    }

    .metric-card-blue   { background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%); border-left: 3.5px solid #2563eb !important; }
    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-left: 3.5px solid #f59e0b !important; }
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-left: 3.5px solid #10b981 !important; }
    .metric-card-purple { background: linear-gradient(180deg, #ffffff 0%, #faf5ff 100%); border-left: 3.5px solid #a855f7 !important; }

    .reviews-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 12px;
    }

    /* Status Navigation Tabs */
    .status-tabs-scroll-wrap {
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
        padding: 2px 0;
    }
    .status-tabs-scroll-wrap::-webkit-scrollbar {
        display: none;
    }
    .status-tab {
        font-size: 11px !important;
        font-weight: 700 !important;
        padding: 6px 12px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff;
        color: #475569;
        transition: all 0.15s ease;
        white-space: nowrap;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
    }
    .status-tab:hover {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        border-color: #94a3b8 !important;
    }
    .status-tab.active-tab {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25);
    }
    .status-tab.active-tab:hover {
        background-color: #1d4ed8 !important;
        color: #ffffff !important;
    }

    #reviewsManagementRoot .form-control,
    #reviewsManagementRoot .form-select {
        min-height: 33px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #reviewsManagementRoot .form-control:focus,
    #reviewsManagementRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Table Architecture: 100% Fit Screen with Smooth Horizontal Scroll */
    .reviews-table-wrapper {
        width: 100%;
        margin-bottom: 0;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }
    .custom-reviews-table {
        width: 100% !important;
        margin-bottom: 0;
        border-collapse: collapse;
    }
    .custom-reviews-table th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 10px;
        white-space: nowrap;
    }
    .custom-reviews-table td {
        padding: 8px 10px;
        vertical-align: middle;
        font-size: 12px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .custom-reviews-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    /* Fixed Desktop Proportions */
    @media (min-width: 992px) {
        .custom-reviews-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .custom-reviews-table th,
        .custom-reviews-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        .col-rev-chk      { width: 4% !important; }
        .col-rev-product  { width: 22% !important; }
        .col-rev-customer { width: 16% !important; }
        .col-rev-rating   { width: 11% !important; }
        .col-rev-content  { width: 25% !important; }
        .col-rev-status   { width: 8% !important; }
        .col-rev-visible  { width: 6% !important; }
        .col-rev-actions  { width: 8% !important; }
    }

    /* Strict Mobile Responsiveness: Minimum width prevents squishing & text overlap */
    @media (max-width: 991.98px) {
        .reviews-header-card {
            padding: 12px 14px !important;
        }
        .header-actions-group {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .header-actions-group .btn {
            width: 100%;
            padding: 8px 6px !important;
            font-size: 11px !important;
            justify-content: center;
        }
        .custom-reviews-table {
            min-width: 980px !important;
            table-layout: auto !important;
        }
        .custom-reviews-table th,
        .custom-reviews-table td {
            white-space: nowrap !important;
        }
        .metric-card {
            padding: 10px !important;
            min-height: 80px !important;
        }
        .stat-label-title {
            font-size: 10px !important;
        }
        .stat-val-num {
            font-size: 14px !important;
        }
        .stat-sub-text {
            font-size: 9px !important;
        }
        .metric-icon-square {
            width: 28px !important;
            height: 28px !important;
            font-size: 11px !important;
        }
    }

    .review-thumb-mini {
        width: 30px;
        height: 30px;
        object-fit: cover;
        border: 1px solid #cbd5e1;
        cursor: pointer;
        display: inline-block;
        flex-shrink: 0;
    }

    .star-yellow {
        color: #f59e0b;
        font-size: 10px;
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="reviewsManagementRoot">

    <!-- 1. Header Banner Card (Clean White Light Theme) -->
    <div class="reviews-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase" style="font-size: 10px;">
                    <i class="fas fa-star-half-stroke me-1"></i> REVIEW MODERATION
                </span>
                <span class="badge bg-light text-secondary border font-monospace" style="font-size: 10px;">
                    CUSTOMER FEEDBACK ENGINE
                </span>
            </div>
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-star text-warning"></i>
                <span>Product Reviews Management</span>
            </h5>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                Moderate customer feedback, approve/hide reviews, manage auto replies &amp; top 5 pins.
            </small>
        </div>

        <div class="header-actions-group d-flex flex-wrap gap-1">
            <a href="{{ route('admin.product.reviews.dashboard') }}" class="btn btn-outline-primary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-chart-line me-1"></i> Analytics Dashboard
            </a>
            <a href="{{ route('admin.product.reviews.auto_rules') }}" class="btn btn-outline-success btn-compact shadow-xs fw-semibold">
                <i class="fas fa-robot me-1"></i> Auto-Reply Rules
            </a>
            <a href="{{ route('admin.product.reviews.settings') }}" class="btn btn-outline-dark btn-compact shadow-xs fw-semibold">
                <i class="fas fa-gear me-1"></i> Review Settings
            </a>
            <button type="button" class="btn btn-success btn-compact shadow-xs fw-bold" onclick="exportReviews('csv')">
                <i class="fas fa-file-csv me-1"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- 2. Reviews Status Telemetry KPI Overview -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-blue h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Total Reviews</span>
                    <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                        <i class="fas fa-comments"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                        {{ number_format($totalReviewsCount) }}
                    </h4>
                    <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Overall feedback logged</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-amber h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Pending Moderation</span>
                    <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-warning-emphasis bn-digit font-monospace stat-val-num">
                        {{ number_format($pendingCount) }}
                    </h4>
                    <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Awaiting admin review</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-green h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Approved &amp; Live</span>
                    <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                        <i class="fas fa-circle-check"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num">
                        {{ number_format($approvedCount) }}
                    </h4>
                    <small class="text-success d-block text-truncate font-monospace stat-sub-text">
                        <i class="fas fa-bolt me-1"></i>Visible on storefront
                    </small>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-purple h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Pinned &amp; Media</span>
                    <div class="metric-icon-square bg-purple-subtle text-primary shadow-xs" style="background-color: #f3e8ff; color: #7e22ce;">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                        {{ number_format($pinnedCount) }} <span class="text-muted fw-normal" style="font-size: 11px;">/ {{ number_format($withImagesCount) }} media</span>
                    </h4>
                    <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Featured customer proofs</small>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Status Tabs Navigation Strip -->
    <div class="reviews-panel-card mb-2 p-2">
        <div class="status-tabs-scroll-wrap">
            <div class="d-inline-flex gap-1 align-items-center" id="reviewStatusTabs">
                <button type="button" class="btn btn-compact status-tab {{ empty(request('status')) && empty(request('visibility')) ? 'active-tab fw-bold' : '' }}" data-status="" data-visibility="">
                    All Reviews (<span class="tab-all-count font-monospace bn-digit">{{ $tabCounts['all'] ?? 0 }}</span>)
                </button>
                <button type="button" class="btn btn-compact status-tab {{ request('status') === 'pending' ? 'active-tab fw-bold' : '' }}" data-status="pending" data-visibility="">
                    Pending (<span class="tab-pending-count font-monospace bn-digit">{{ $tabCounts['pending'] ?? 0 }}</span>)
                </button>
                <button type="button" class="btn btn-compact status-tab {{ request('status') === 'approved' && request('visibility') !== 'hidden' ? 'active-tab fw-bold' : '' }}" data-status="approved" data-visibility="visible">
                    Approved (<span class="tab-approved-count font-monospace bn-digit">{{ $tabCounts['approved'] ?? 0 }}</span>)
                </button>
                <button type="button" class="btn btn-compact status-tab {{ request('visibility') === 'hidden' ? 'active-tab fw-bold' : '' }}" data-status="" data-visibility="hidden">
                    Hidden (<span class="tab-hidden-count font-monospace bn-digit">{{ $tabCounts['hidden'] ?? 0 }}</span>)
                </button>
                <button type="button" class="btn btn-compact status-tab {{ request('status') === 'rejected' ? 'active-tab fw-bold' : '' }}" data-status="rejected" data-visibility="">
                    Rejected (<span class="tab-rejected-count font-monospace bn-digit">{{ $tabCounts['rejected'] ?? 0 }}</span>)
                </button>
                <button type="button" class="btn btn-compact status-tab" data-filter="has_images" data-val="yes">
                    With Images (<span class="tab-images-count font-monospace bn-digit">{{ $tabCounts['with_images'] ?? 0 }}</span>)
                </button>
                <button type="button" class="btn btn-compact status-tab" data-filter="pinned" data-val="yes">
                    Pinned (<span class="tab-pinned-count font-monospace bn-digit">{{ $tabCounts['pinned'] ?? 0 }}</span>)
                </button>
            </div>
        </div>
    </div>

    <!-- 4. Interactive AJAX Filters Bar -->
    <div class="reviews-panel-card mb-2 p-2">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-xl-3 col-md-4">
                <div class="input-group position-relative">
                    <span class="input-group-text border-end-0"><i class="fas fa-search text-muted" style="font-size: 10px;"></i></span>
                    <input type="text" id="reviewSearchInput" class="form-control border-start-0" placeholder="Product, customer, comment...">
                    <span id="searchSpinner" class="spinner-border spinner-border-sm text-primary position-absolute end-0 me-2 top-50 translate-middle-y d-none"></span>
                </div>
            </div>

            <div class="col-6 col-xl-2 col-md-2">
                <select id="filterRating" class="form-select font-monospace">
                    <option value="all">Star Rating (All)</option>
                    <option value="5">5 Stars</option>
                    <option value="4">4 Stars</option>
                    <option value="3">3 Stars</option>
                    <option value="2">2 Stars</option>
                    <option value="1">1 Star</option>
                </select>
            </div>

            <div class="col-6 col-xl-2 col-md-2">
                <select id="filterVerified" class="form-select">
                    <option value="all">Buyer Type (All)</option>
                    <option value="yes">Verified Buyer Only</option>
                    <option value="no">Non-Verified</option>
                </select>
            </div>

            <div class="col-6 col-xl-2 col-md-2">
                <select id="filterReplied" class="form-select">
                    <option value="all">Reply Status (All)</option>
                    <option value="yes">Replied</option>
                    <option value="no">Unanswered</option>
                </select>
            </div>

            <div class="col-6 col-xl-1 col-md-2">
                <select id="perPageSelect" class="form-select font-monospace fw-bold">
                    <option value="20" selected>20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="all">All</option>
                </select>
            </div>

            <div class="col-12 col-xl-2 col-md-12 d-flex justify-content-end align-items-center gap-1">
                <select id="bulkActionSelect" class="form-select font-monospace" style="max-width: 140px;" disabled>
                    <option value="">Bulk Actions</option>
                    <option value="approve">Approve</option>
                    <option value="hide">Hide</option>
                    <option value="unhide">Unhide</option>
                    <option value="reject">Reject</option>
                    <option value="delete">Delete</option>
                </select>
                <button type="button" id="bulkApplyBtn" class="btn btn-dark btn-compact px-3" onclick="applyBulkAction()" disabled>Apply</button>
            </div>
        </div>
    </div>

    <!-- 5. Main Reviews Ledger Table -->
    <div class="reviews-panel-card position-relative overflow-hidden mb-3">
        <div id="tableLoadingCover" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none justify-content-center align-items-center" style="z-index: 10;">
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        </div>

        <div class="reviews-table-wrapper pc-fit-container">
            <table class="table custom-reviews-table align-middle">
                <thead>
                    <tr>
                        <th class="ps-2 text-center col-rev-chk">
                            <input type="checkbox" class="form-check-input m-0" id="selectAllCheckbox">
                        </th>
                        <th class="col-rev-product">Product</th>
                        <th class="col-rev-customer">Customer</th>
                        <th class="col-rev-rating">Rating</th>
                        <th class="col-rev-content">Review Details</th>
                        <th class="text-center col-rev-status">Status</th>
                        <th class="text-center col-rev-visible">Visible</th>
                        <th class="text-end pe-2 col-rev-actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="reviewsTableBody">
                    @include('admin.reviews.partials.review_rows')
                </tbody>
            </table>
        </div>

        <div class="p-2 px-3 border-top d-flex justify-content-center bg-light bg-opacity-50" id="paginationWrapper">
            @if($reviews->hasPages())
                {{ $reviews->links('pagination::bootstrap-5') }}
            @endif
        </div>
    </div>
</div>

<!-- Modal 1: Details & Moderate Review -->
<div class="modal fade review-modal-scope" id="reviewDetailModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header py-2 px-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-1" id="modalReviewTitle" style="font-size: 13px;">
                    <i class="fas fa-magnifying-glass me-1 text-primary"></i> <span>Review Inspection &amp; Response</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="row g-3">
                    <div class="col-md-6 border-end">
                        <small class="text-muted text-uppercase fw-bold d-block mb-1 font-monospace" style="font-size: 10px;">Product &amp; Order</small>
                        <h6 class="fw-bold text-dark mb-1" id="mProductTitle" style="font-size: 13px;">Product Title</h6>
                        <p class="small text-muted mb-2 font-monospace" id="mOrderInvoice">Order: N/A</p>

                        <small class="text-muted text-uppercase fw-bold d-block mb-1 mt-3 font-monospace" style="font-size: 10px;">Customer Information</small>
                        <div class="small">
                            <span class="d-block text-dark fw-bold" id="mCustomerName" style="font-size: 12px;">Customer</span>
                            <span class="d-block text-muted font-monospace" id="mCustomerEmail">Email: N/A</span>
                            <span class="d-block text-muted font-monospace" id="mCustomerPhone">Phone: N/A</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle mt-1 font-monospace" id="mVerifiedBadge" style="font-size: 9.5px;">Verified Buyer</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted text-uppercase fw-bold d-block mb-1 font-monospace" style="font-size: 10px;">Review Content</small>
                        <div class="mb-2" id="mRatingStars"></div>
                        <p class="small text-dark p-2 bg-light border rounded mb-2 font-monospace" id="mReviewComment" style="font-size: 11.5px; line-height: 1.45;"></p>
                        
                        <div class="mb-3" id="mImagesBox"></div>

                        <div class="border-top pt-2">
                            <label class="form-label fw-bold small mb-1" style="font-size: 11px;">Admin Reply:</label>
                            <textarea id="mAdminReplyText" class="form-control form-control-sm mb-2 font-monospace" rows="2" placeholder="Write reply to customer..."></textarea>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold px-3" id="btnSaveReply" onclick="saveModalReply()">
                                    <i class="fas fa-paper-plane me-1"></i> Post Reply
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Reusable Admin Alert Modal -->
<div class="modal fade review-modal-scope" id="adminAlertModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-2">
                <div id="adminAlertIcon" class="mb-2" style="font-size: 32px;"></div>
                <h6 id="adminAlertTitle" class="fw-bold mb-1 small text-dark"></h6>
                <p id="adminAlertMessage" class="text-muted small mb-3 font-monospace" style="font-size: 11px;"></p>
                <button type="button" class="btn btn-primary btn-compact px-4 fw-bold" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 3: Reusable Admin Confirmation Modal -->
<div class="modal fade review-modal-scope" id="adminConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 108600;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-2">
                <div class="mb-2 text-warning" style="font-size: 32px;">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <h6 id="adminConfirmTitle" class="fw-bold mb-1 small text-dark">Confirm Action</h6>
                <p id="adminConfirmMessage" class="text-muted small mb-3 font-monospace" style="font-size: 11px;"></p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-compact px-3 fw-bold shadow-xs" id="adminConfirmExecuteBtn">Proceed</button>
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

function showAdminAlert(title, message, type = 'info') {
    const modalEl = document.getElementById('adminAlertModal');
    const iconEl  = document.getElementById('adminAlertIcon');
    const titleEl = document.getElementById('adminAlertTitle');
    const msgEl   = document.getElementById('adminAlertMessage');

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
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
}

function showAdminConfirm(title, message, onConfirm) {
    const modalEl = document.getElementById('adminConfirmModal');
    const titleEl = document.getElementById('adminConfirmTitle');
    const msgEl   = document.getElementById('adminConfirmMessage');

    if (!modalEl || !titleEl || !msgEl) return;

    titleEl.innerText = title;
    msgEl.innerText = message;
    confirmCallback = onConfirm;

    bootstrap.Modal.getOrCreateInstance(modalEl).show();
}

// 1. Unified Realtime Bulk State Handler
function syncBulkControls() {
    const bulkSelect = document.getElementById('bulkActionSelect');
    const bulkBtn    = document.getElementById('bulkApplyBtn');
    const checkedCount = document.querySelectorAll('.review-row-check:checked').length;

    if (bulkSelect) bulkSelect.disabled = (checkedCount === 0);
    if (bulkBtn) bulkBtn.disabled = (checkedCount === 0);

    const selectAll = document.getElementById('selectAllCheckbox');
    const allRows = document.querySelectorAll('.review-row-check');
    if (selectAll && allRows.length > 0) {
        selectAll.checked = (checkedCount === allRows.length);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const searchIn = document.getElementById('reviewSearchInput');
    const statusTabs = document.querySelectorAll('#reviewStatusTabs .status-tab');
    const confirmBtn = document.getElementById('adminConfirmExecuteBtn');

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('adminConfirmModal'));
            if (modal) modal.hide();
            if (typeof confirmCallback === 'function') {
                confirmCallback();
                confirmCallback = null;
            }
        });
    }

    if (searchIn) {
        searchIn.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchReviews(1), 300);
        });
    }

    statusTabs.forEach(t => {
        t.addEventListener('click', function() {
            statusTabs.forEach(x => x.classList.remove('active-tab', 'fw-bold'));
            this.classList.add('active-tab', 'fw-bold');

            currentStatus = this.getAttribute('data-status') || '';
            currentVisibility = this.getAttribute('data-visibility') || '';
            currentFilter = this.getAttribute('data-filter') || '';
            currentVal = this.getAttribute('data-val') || '';

            fetchReviews(1);
        });
    });

    document.getElementById('filterRating')?.addEventListener('change', () => fetchReviews(1));
    document.getElementById('filterVerified')?.addEventListener('change', () => fetchReviews(1));
    document.getElementById('filterReplied')?.addEventListener('change', () => fetchReviews(1));
    document.getElementById('perPageSelect')?.addEventListener('change', () => fetchReviews(1));

    // 2. Event Delegation for Master Checkbox & Rows (Works immediately on page load)
    const selectAll = document.getElementById('selectAllCheckbox');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.review-row-check').forEach(cb => {
                cb.checked = isChecked;
            });
            syncBulkControls();
        });
    }

    document.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('review-row-check')) {
            syncBulkControls();
        }
    });

    // Run initial state check
    syncBulkControls();
});

function fetchReviews(page = 1) {
    const tableBody = document.getElementById('reviewsTableBody');
    const spinner = document.getElementById('searchSpinner');
    const cover = document.getElementById('tableLoadingCover');
    const pagination = document.getElementById('paginationWrapper');

    if (spinner) spinner.classList.remove('d-none');
    if (cover) {
        cover.classList.remove('d-none');
        cover.classList.add('d-flex');
    }

    const url = new URL("{{ route('admin.product.reviews.index') }}", window.location.origin);
    url.searchParams.set('page', page);
    url.searchParams.set('search', document.getElementById('reviewSearchInput').value.trim());
    url.searchParams.set('rating', document.getElementById('filterRating').value);
    url.searchParams.set('verified', document.getElementById('filterVerified').value);
    url.searchParams.set('replied', document.getElementById('filterReplied').value);
    url.searchParams.set('per_page', document.getElementById('perPageSelect').value);

    if (currentStatus) url.searchParams.set('status', currentStatus);
    if (currentVisibility) url.searchParams.set('visibility', currentVisibility);
    if (currentFilter && currentVal) url.searchParams.set(currentFilter, currentVal);

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
    .then(res => res.json())
    .then(data => {
        tableBody.innerHTML = data.table_html;
        pagination.innerHTML = data.pagination_html;
        
        // Tab counters dynamic sync
        if (data.counts) {
            const mapCounts = {
                '.tab-all-count': data.counts.all,
                '.tab-pending-count': data.counts.pending,
                '.tab-approved-count': data.counts.approved,
                '.tab-hidden-count': data.counts.hidden,
                '.tab-rejected-count': data.counts.rejected,
                '.tab-images-count': data.counts.with_images,
                '.tab-pinned-count': data.counts.pinned,
            };
            for (const [selector, countVal] of Object.entries(mapCounts)) {
                const el = document.querySelector(selector);
                if (el && countVal !== undefined) el.innerText = countVal;
            }
        }

        syncBulkControls();
    })
    .catch(() => showAdminAlert('Error', 'Failed to retrieve reviews data.', 'error'))
    .finally(() => {
        if (spinner) spinner.classList.add('d-none');
        if (cover) {
            cover.classList.add('d-none');
            cover.classList.remove('d-flex');
        }
    });
}

function updateReviewStatus(id, status) {
    fetch("{{ url('admin/product/reviews/status') }}/" + id, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ status })
    })
    .then(res => res.json())
    .then(d => {
        if (d.status === 'success') {
            fetchReviews();
        } else {
            showAdminAlert('Status Update', d.message || 'Operation failed.', 'error');
        }
    })
    .catch(() => showAdminAlert('Error', 'Network error occurred.', 'error'));
}

function toggleVisibility(id) {
    fetch("{{ url('admin/product/reviews/toggle-visibility') }}/" + id, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(res => res.json())
    .then(d => {
        if (d.status === 'success') {
            fetchReviews();
        } else {
            showAdminAlert('Visibility', d.message || 'Could not update visibility.', 'error');
        }
    })
    .catch(() => showAdminAlert('Error', 'Network error occurred.', 'error'));
}

function togglePin(id) {
    fetch("{{ url('admin/product/reviews/toggle-pin') }}/" + id, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(res => res.json())
    .then(d => {
        showAdminAlert('Pin Status', d.message, d.status === 'success' ? 'success' : 'error');
        if (d.status === 'success') fetchReviews();
    })
    .catch(() => showAdminAlert('Error', 'Failed to update pin state.', 'error'));
}

function deleteReview(id) {
    showAdminConfirm('Delete Review', 'Are you sure you want to permanently delete this review?', () => {
        fetch("{{ url('admin/product/reviews/destroy') }}/" + id, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(res => res.json())
        .then(d => {
            showAdminAlert('Deleted', d.message || 'Review deleted successfully.', 'success');
            if (d.status === 'success') fetchReviews();
        })
        .catch(() => showAdminAlert('Error', 'Failed to delete review.', 'error'));
    });
}

function openInspectModal(id) {
    activeReviewId = id;
    fetch("{{ url('admin/product/reviews/show') }}/" + id)
    .then(res => res.json())
    .then(d => {
        if (d.status === 'success') {
            const r = d.review;
            document.getElementById('mProductTitle').innerText = r.product_title || 'Product #' + r.product_id;
            document.getElementById('mOrderInvoice').innerText = r.order_invoice_no ? ('Invoice: #' + r.order_invoice_no) : 'Order: Direct / N/A';
            document.getElementById('mCustomerName').innerText = r.customer_name || 'Guest';
            document.getElementById('mCustomerEmail').innerText = 'Email: ' + (r.customer_email || 'N/A');
            document.getElementById('mCustomerPhone').innerText = 'Phone: ' + (r.customer_phone || 'N/A');
            document.getElementById('mReviewComment').innerText = r.comment;
            document.getElementById('mAdminReplyText').value = r.admin_reply || '';

            let stars = '';
            for(let i = 1; i <= 5; i++) {
                stars += `<i class="fas fa-star ${i <= r.rating ? 'star-yellow' : 'text-muted opacity-25'}"></i>`;
            }
            document.getElementById('mRatingStars').innerHTML = stars;

            let imgsHtml = '';
            if (r.images && r.images.length > 0) {
                imgsHtml = '<small class="text-muted fw-bold d-block mb-1 font-monospace" style="font-size:10px;">ATTACHED IMAGES</small><div class="d-flex gap-1 flex-wrap">';
                r.images.forEach(img => {
                    const cleanImg = (img.startsWith('http') || img.startsWith('/')) ? img : '/' + img;
                    imgsHtml += `<a href="${cleanImg}" target="_blank"><img src="${cleanImg}" class="review-thumb-mini" onerror="this.onerror=null; this.style.display='none'"></a>`;
                });
                imgsHtml += '</div>';
            }
            document.getElementById('mImagesBox').innerHTML = imgsHtml;

            bootstrap.Modal.getOrCreateInstance(document.getElementById('reviewDetailModal')).show();
        } else {
            showAdminAlert('Error', d.message || 'Review details not found.', 'error');
        }
    })
    .catch(() => showAdminAlert('Error', 'Failed to inspect review.', 'error'));
}

function saveModalReply() {
    if (!activeReviewId) return;
    const text = document.getElementById('mAdminReplyText').value.trim();
    if (!text) {
        showAdminAlert('Validation', 'Reply text cannot be empty.', 'error');
        return;
    }

    fetch("{{ url('admin/product/reviews/reply') }}/" + activeReviewId, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ reply_text: text })
    })
    .then(res => res.json())
    .then(d => {
        const inspectModal = bootstrap.Modal.getInstance(document.getElementById('reviewDetailModal'));
        if (inspectModal) inspectModal.hide();

        showAdminAlert('Success', d.message || 'Reply posted successfully.', 'success');
        fetchReviews();
    })
    .catch(() => showAdminAlert('Error', 'Failed to post admin reply.', 'error'));
}

function applyBulkAction() {
    const action = document.getElementById('bulkActionSelect').value;
    const ids = Array.from(document.querySelectorAll('.review-row-check:checked')).map(c => c.value);
    if (!action || ids.length === 0) return;

    const executeBulk = () => {
        fetch("{{ route('admin.product.reviews.bulk_action') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ action, ids })
        })
        .then(res => res.json())
        .then(d => {
            showAdminAlert('Bulk Action Result', d.message || 'Action executed successfully.', 'success');
            fetchReviews();
        })
        .catch(() => showAdminAlert('Error', 'Bulk action failed.', 'error'));
    };

    if (action === 'delete') {
        showAdminConfirm('Bulk Delete', `Are you sure you want to permanently delete ${ids.length} selected reviews?`, executeBulk);
    } else {
        executeBulk();
    }
}

function exportReviews(type) {
    const url = new URL("{{ route('admin.product.reviews.index') }}", window.location.origin);
    url.searchParams.set('export', type);
    window.open(url.toString(), '_blank');
}
</script>
@endsection