@extends('layouts.admin')

@section('title', 'Customer Review Profile - ' . ($customer->name ?? 'Customer #' . $customer->id))

@section('admin_content')
<style>
    #customerReviewsRoot {
        --cr-primary: #2563eb;
        --cr-primary-soft: #eff6ff;
        --cr-border: #e2e8f0;
        --cr-muted: #64748b;
        --cr-heading: #0f172a;
        --cr-surface: #ffffff;
        --cr-bg: #f8fafc;
        color: var(--cr-heading);
        font-size: 13px;
    }

    /* Consistent 5px Radius */
    #customerReviewsRoot .btn,
    #customerReviewsRoot .btn-sm,
    #customerReviewsRoot button,
    #customerReviewsRoot .badge,
    #customerReviewsRoot .form-control,
    #customerReviewsRoot .form-select,
    #customerReviewsRoot .card,
    #customerReviewsRoot .metric-card,
    #customerReviewsRoot .c-header-card,
    #customerReviewsRoot .c-panel-card,
    #customerReviewsRoot .alert,
    #customerReviewsRoot .prod-thumb-mini,
    #customerReviewsRoot .review-photo-thumb,
    #customerReviewsRoot .page-link,
    #customerReviewsRoot .page-item span,
    .c-review-modal-scope .modal-content,
    .c-review-modal-scope .btn {
        border-radius: 5px !important;
    }

    #customerReviewsRoot .font-monospace,
    #customerReviewsRoot .bn-digit {
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
    }

    #customerReviewsRoot .btn {
        font-weight: 600;
        transition: all .18s ease;
    }

    #customerReviewsRoot .btn-compact {
        font-size: 11px !important;
        padding: 7px 11px !important;
        line-height: 1.3 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        gap: 5px;
    }

    #customerReviewsRoot .btn-compact:hover {
        transform: translateY(-1px);
    }

    /* Header */
    #customerReviewsRoot .c-header-card {
        background: var(--cr-surface);
        border: 1px solid var(--cr-border);
        padding: 16px 18px;
        margin-bottom: 14px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
    }

    #customerReviewsRoot .header-kicker {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .055em;
        padding: 5px 8px;
        border-radius: 5px;
        background: var(--cr-primary-soft);
        color: var(--cr-primary);
        border: 1px solid #dbeafe;
    }

    #customerReviewsRoot .header-meta {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .035em;
        color: #64748b;
        background: #f8fafc;
        border: 1px solid var(--cr-border);
        padding: 5px 8px;
        border-radius: 5px;
    }

    #customerReviewsRoot .header-title {
        font-size: 18px;
        font-weight: 750;
        letter-spacing: -.025em;
        color: #0f172a;
        line-height: 1.35;
    }

    #customerReviewsRoot .header-description {
        color: var(--cr-muted);
        font-size: 11px;
        line-height: 1.55;
        max-width: 620px;
    }

    #customerReviewsRoot .header-actions-group {
        display: flex;
        align-items: center;
        gap: 7px;
        flex-shrink: 0;
    }

    /* KPI Cards */
    #customerReviewsRoot .metric-card {
        background: #fff;
        border: 1px solid var(--cr-border);
        padding: 13px 14px;
        height: 100%;
        min-height: 105px;
        position: relative;
        overflow: hidden;
        transition: transform .18s ease, box-shadow .18s ease;
    }

    #customerReviewsRoot .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(15, 23, 42, .055);
    }

    #customerReviewsRoot .metric-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 10px;
    }

    #customerReviewsRoot .stat-label-title {
        font-size: 10px;
        font-weight: 750;
        color: #64748b;
        letter-spacing: .035em;
        text-transform: uppercase;
        line-height: 1.35;
    }

    #customerReviewsRoot .stat-val-num {
        font-size: 21px;
        line-height: 1.25;
        letter-spacing: -.035em;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #customerReviewsRoot .stat-sub-text {
        font-size: 10px;
        color: #94a3b8;
        margin-top: 3px;
        line-height: 1.4;
    }

    #customerReviewsRoot .metric-icon-square {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 5px !important;
        font-size: 13px;
    }

    #customerReviewsRoot .metric-card-blue {
        border-left: 3px solid #3b82f6 !important;
        background: linear-gradient(145deg, #fff 55%, #f0f7ff);
    }

    #customerReviewsRoot .metric-card-amber {
        border-left: 3px solid #f59e0b !important;
        background: linear-gradient(145deg, #fff 55%, #fffbeb);
    }

    #customerReviewsRoot .metric-card-green {
        border-left: 3px solid #10b981 !important;
        background: linear-gradient(145deg, #fff 55%, #f0fdf4);
    }

    #customerReviewsRoot .metric-card-purple {
        border-left: 3px solid #a855f7 !important;
        background: linear-gradient(145deg, #fff 55%, #faf5ff);
    }

    /* General Panels */
    #customerReviewsRoot .c-panel-card {
        background: #fff;
        border: 1px solid var(--cr-border);
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
        margin-bottom: 14px;
    }

    #customerReviewsRoot .panel-heading {
        padding: 12px 15px;
        border-bottom: 1px solid #edf1f5;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    #customerReviewsRoot .panel-title {
        font-size: 12px;
        font-weight: 750;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    #customerReviewsRoot .panel-title-icon {
        width: 27px;
        height: 27px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        background: #eff6ff;
        color: #2563eb;
        font-size: 11px;
        flex-shrink: 0;
    }

    /* Customer Profile */
    #customerReviewsRoot .customer-profile-card {
        padding: 15px 17px;
    }

    #customerReviewsRoot .customer-avatar-circle {
        width: 54px;
        height: 54px;
        background: linear-gradient(135deg, #1e40af, #3b82f6);
        color: #fff;
        font-weight: 800;
        font-size: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50% !important;
        border: 3px solid #eff6ff;
        flex-shrink: 0;
    }

    #customerReviewsRoot .customer-name {
        font-size: 14px;
        font-weight: 750;
        color: #0f172a;
    }

    #customerReviewsRoot .customer-info {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 7px 16px;
        color: #64748b;
        font-size: 10px;
    }

    #customerReviewsRoot .customer-info span {
        overflow-wrap: anywhere;
    }

    #customerReviewsRoot .customer-score {
        min-width: 105px;
        text-align: right;
        border-left: 1px solid #e2e8f0;
        padding-left: 16px;
    }

    #customerReviewsRoot .customer-score-value {
        color: #d97706;
        font-size: 23px;
        font-weight: 800;
        line-height: 1.2;
    }

    /* Review Table */
    #customerReviewsRoot .pc-fit-container {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #customerReviewsRoot .c-reviews-table {
        width: 100% !important;
        margin-bottom: 0;
        border-collapse: collapse;
    }

    #customerReviewsRoot .c-reviews-table th {
        background: #f8fafc;
        color: #64748b;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .045em;
        border-bottom: 1px solid #e2e8f0;
        padding: 11px 10px;
        white-space: nowrap;
    }

    #customerReviewsRoot .c-reviews-table td {
        padding: 11px 10px;
        vertical-align: middle;
        font-size: 11px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
    }

    #customerReviewsRoot .c-reviews-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #customerReviewsRoot .c-reviews-table tbody tr {
        transition: background .15s ease;
    }

    #customerReviewsRoot .c-reviews-table tbody tr:hover {
        background: #fafcff;
    }

    #customerReviewsRoot .prod-thumb-mini {
        width: 39px;
        height: 39px;
        object-fit: contain;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        flex-shrink: 0;
        padding: 2px;
    }

    #customerReviewsRoot .product-title-cell {
        font-size: 11px;
        font-weight: 700;
        line-height: 1.4;
        color: #1e293b;
    }

    #customerReviewsRoot .product-id-cell {
        font-size: 9px;
        color: #94a3b8;
        margin-top: 3px;
    }

    #customerReviewsRoot .review-rating {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: #d97706;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    #customerReviewsRoot .review-date {
        display: block;
        color: #94a3b8;
        font-size: 9px;
        margin-top: 4px;
        white-space: nowrap;
    }

    #customerReviewsRoot .review-feedback-title {
        display: block;
        color: #1e293b;
        font-size: 11px;
        font-weight: 750;
        margin-bottom: 4px;
    }

    #customerReviewsRoot .review-feedback-text {
        color: #475569;
        font-size: 11px;
        line-height: 1.55;
        overflow-wrap: anywhere;
        margin-bottom: 0;
    }

    #customerReviewsRoot .review-photo-thumb {
        width: 32px;
        height: 32px;
        object-fit: cover;
        border: 1px solid #cbd5e1;
        cursor: pointer;
        display: inline-block;
        flex-shrink: 0;
        transition: transform .15s ease, border-color .15s ease;
    }

    #customerReviewsRoot .review-photo-thumb:hover {
        transform: scale(1.08);
        border-color: #2563eb;
    }

    #customerReviewsRoot .store-reply {
        margin-top: 7px;
        padding: 7px 9px;
        background: #eff6ff;
        border-left: 3px solid #3b82f6;
        border-radius: 0 5px 5px 0;
        color: #475569;
        font-size: 10px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    #customerReviewsRoot .status-badge {
        display: inline-block;
        padding: 5px 7px !important;
        font-size: 8px !important;
        font-weight: 800;
        letter-spacing: .04em;
    }

    #customerReviewsRoot .visibility-indicator {
        font-size: 13px;
    }

    #customerReviewsRoot .empty-state {
        padding: 45px 15px !important;
        text-align: center;
        color: #94a3b8;
    }

    #customerReviewsRoot .empty-state-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: #94a3b8;
        font-size: 19px;
        margin-bottom: 12px;
    }

    /* Pagination */
    #customerReviewsRoot .pagination {
        margin-bottom: 0;
        gap: 3px;
        flex-wrap: wrap;
        justify-content: center;
    }

    #customerReviewsRoot .page-link {
        font-size: 11px;
        color: #475569;
        border-color: #e2e8f0;
        padding: 6px 10px;
    }

    #customerReviewsRoot .page-item.active .page-link {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }

    /* Modal */
    .c-review-modal-scope .modal-content {
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }

    .c-review-modal-scope .modal-header {
        border-bottom: 1px solid #edf1f5;
        padding: 10px 14px;
    }

    .c-review-modal-scope .modal-body {
        padding: 12px;
    }

    #customerPhotoPreviewImg {
        max-height: 480px;
        max-width: 100%;
        object-fit: contain;
        border-radius: 5px !important;
    }

    /* Desktop Fit */
    @media (min-width: 1200px) {
        #customerReviewsRoot .c-reviews-table {
            table-layout: fixed !important;
        }

        #customerReviewsRoot .col-cust-prod { width: 22%; }
        #customerReviewsRoot .col-cust-rating { width: 11%; }
        #customerReviewsRoot .col-cust-feedback { width: 39%; }
        #customerReviewsRoot .col-cust-status { width: 10%; }
        #customerReviewsRoot .col-cust-visible { width: 7%; }
        #customerReviewsRoot .col-cust-actions { width: 11%; }

        #customerReviewsRoot .c-reviews-table th,
        #customerReviewsRoot .c-reviews-table td {
            overflow: hidden;
            text-overflow: ellipsis;
        }
    }

    /* Tablet */
    @media (max-width: 991.98px) {
        #customerReviewsRoot .customer-info {
            gap: 7px 12px;
        }

        #customerReviewsRoot .c-reviews-table {
            min-width: 950px;
        }

        #customerReviewsRoot .pc-fit-container {
            overflow-x: auto !important;
        }
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        #customerReviewsRoot {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #customerReviewsRoot .c-header-card {
            padding: 13px !important;
        }

        #customerReviewsRoot .header-title {
            font-size: 16px;
        }

        #customerReviewsRoot .header-description {
            font-size: 10px;
        }

        #customerReviewsRoot .header-actions-group {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        #customerReviewsRoot .header-actions-group .btn {
            width: 100%;
            padding: 8px 5px !important;
            font-size: 10px !important;
        }

        #customerReviewsRoot .metric-card {
            padding: 11px !important;
            min-height: 94px;
        }

        #customerReviewsRoot .stat-label-title {
            font-size: 9px;
        }

        #customerReviewsRoot .stat-val-num {
            font-size: 17px;
        }

        #customerReviewsRoot .stat-sub-text {
            font-size: 9px;
        }

        #customerReviewsRoot .metric-icon-square {
            width: 27px;
            height: 27px;
            font-size: 11px;
        }

        #customerReviewsRoot .customer-profile-card {
            padding: 13px !important;
        }

        #customerReviewsRoot .customer-avatar-circle {
            width: 45px;
            height: 45px;
            font-size: 17px;
        }

        #customerReviewsRoot .customer-name {
            font-size: 13px;
        }

        #customerReviewsRoot .customer-info {
            display: grid;
            grid-template-columns: 1fr;
            gap: 6px;
        }

        #customerReviewsRoot .customer-score {
            min-width: auto;
            border-left: 0;
            border-top: 1px solid #e2e8f0;
            padding: 10px 0 0;
            text-align: left;
            width: 100%;
        }

        #customerReviewsRoot .customer-score-value {
            font-size: 19px;
        }

        #customerReviewsRoot .panel-heading {
            padding: 11px 12px;
        }

        #customerReviewsRoot .c-reviews-table {
            min-width: 950px !important;
            table-layout: auto !important;
        }

        #customerReviewsRoot .pc-fit-container {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        #customerReviewsRoot .pagination {
            gap: 2px;
        }

        #customerReviewsRoot .page-link {
            padding: 5px 8px;
        }
    }

    @media (max-width: 380px) {
        #customerReviewsRoot .metric-card {
            padding: 9px !important;
        }

        #customerReviewsRoot .stat-val-num {
            font-size: 15px;
        }

        #customerReviewsRoot .header-actions-group .btn {
            font-size: 9px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="customerReviewsRoot">

    {{-- Header --}}
    <div class="c-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div class="min-w-0">
            <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                <span class="header-kicker">
                    <i class="fas fa-user-pen"></i>
                    CUSTOMER AUDIT
                </span>
                <span class="header-meta">
                    <i class="fas fa-shield-check"></i>
                    VERIFIED BUYER HISTORY
                </span>
            </div>

            <h5 class="header-title mb-1 d-flex align-items-center gap-2">
                <i class="fas fa-user-check text-primary"></i>
                <span>Review Profile: {{ $customer->name ?? 'Customer #' . $customer->id }}</span>
            </h5>

            <p class="header-description mb-0">
                Inspect all product reviews, average rating sentiment, and verified buyer history for this customer.
            </p>
        </div>

        <div class="header-actions-group">
            <a href="{{ route('admin.product.reviews.index', ['user_id' => $customer->id]) }}"
               class="btn btn-primary btn-compact shadow-sm">
                <i class="fas fa-list-check"></i>
                Filter in Main Table
            </a>

            <a href="{{ route('admin.product.reviews.index') }}"
               class="btn btn-outline-secondary btn-compact bg-white">
                <i class="fas fa-arrow-left"></i>
                All Reviews
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-2 mb-3">

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-blue mb-0">
                <div class="metric-top">
                    <span class="stat-label-title">Submitted Reviews</span>
                    <div class="metric-icon-square bg-primary-subtle text-primary">
                        <i class="fas fa-comments"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-val-num font-monospace">
                        {{ number_format($customerStats['total_reviews'] ?? 0) }}
                    </div>
                    <div class="stat-sub-text">Total feedback entries</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-amber mb-0">
                <div class="metric-top">
                    <span class="stat-label-title">Average Rating</span>
                    <div class="metric-icon-square bg-warning-subtle text-warning-emphasis">
                        <i class="fas fa-star-half-stroke"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-val-num font-monospace">
                        <span class="text-warning">★</span>
                        {{ number_format($customerStats['avg_rating'] ?? 0, 1) }}
                    </div>
                    <div class="stat-sub-text">Lifetime sentiment score</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-green mb-0">
                <div class="metric-top">
                    <span class="stat-label-title">Approved Reviews</span>
                    <div class="metric-icon-square bg-success-subtle text-success">
                        <i class="fas fa-circle-check"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-val-num font-monospace text-success">
                        {{ number_format($customerStats['approved'] ?? 0) }}
                    </div>
                    <div class="stat-sub-text text-success">
                        <i class="fas fa-bolt me-1"></i>Live on storefront
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-purple mb-0">
                <div class="metric-top">
                    <span class="stat-label-title">Rejected Feedback</span>
                    <div class="metric-icon-square bg-danger-subtle text-danger">
                        <i class="fas fa-circle-xmark"></i>
                    </div>
                </div>
                <div>
                    <div class="stat-val-num font-monospace text-danger">
                        {{ number_format($customerStats['rejected'] ?? 0) }}
                    </div>
                    <div class="stat-sub-text">Disapproved / spam</div>
                </div>
            </div>
        </div>

    </div>

    {{-- Customer Master Profile --}}
    <div class="c-panel-card customer-profile-card">
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">

            <div class="d-flex align-items-center gap-3 min-w-0 flex-grow-1">
                <div class="customer-avatar-circle shadow-sm">
                    {{ strtoupper(substr($customer->name ?? 'C', 0, 1)) }}
                </div>

                <div class="min-w-0 flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="customer-name">
                            {{ $customer->name ?? 'Guest Customer' }}
                        </span>

                        <span class="badge bg-light text-secondary border font-monospace" style="font-size: 9px;">
                            ID: #{{ $customer->id }}
                        </span>

                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 9px;">
                            <i class="fas fa-circle-check me-1"></i>
                            Registered Customer
                        </span>
                    </div>

                    <div class="customer-info">
                        <span>
                            <i class="fas fa-envelope text-primary me-1"></i>
                            {{ $customer->email ?? 'No email recorded' }}
                        </span>

                        <span>
                            <i class="fas fa-phone text-success me-1"></i>
                            <strong class="text-dark">{{ $customer->phone ?? 'N/A' }}</strong>
                        </span>

                        <span>
                            <i class="fas fa-calendar-alt text-secondary me-1"></i>
                            Joined: {{ date('d M Y', strtotime($customer->created_at)) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="customer-score flex-shrink-0">
                <div class="customer-score-value font-monospace">
                    <i class="fas fa-star" style="font-size: 15px;"></i>
                    {{ number_format($customerStats['avg_rating'] ?? 0, 1) }}
                </div>
                <small class="text-muted font-monospace" style="font-size: 9px;">
                    Lifetime Score
                </small>
            </div>

        </div>
    </div>

    {{-- Review History --}}
    <div class="c-panel-card p-0 overflow-hidden">

        <div class="panel-heading">
            <h6 class="panel-title">
                <span class="panel-title-icon">
                    <i class="fas fa-clock-rotate-left"></i>
                </span>
                <span>Reviews History by {{ $customer->name ?? 'Customer' }}</span>
            </h6>

            <span class="badge bg-light text-secondary border font-monospace px-2 py-2" style="font-size: 10px;">
                <i class="fas fa-list-ul me-1"></i>
                {{ $customerReviews->total() }} Entries
            </span>
        </div>

        <div class="pc-fit-container">
            <table class="table c-reviews-table align-middle">
                <thead>
                    <tr>
                        <th class="col-cust-prod">Product</th>
                        <th class="col-cust-rating">Rating</th>
                        <th class="col-cust-feedback">Feedback &amp; Attached Photos</th>
                        <th class="text-center col-cust-status">Status</th>
                        <th class="text-center col-cust-visible">Visible</th>
                        <th class="text-end pe-3 col-cust-actions">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($customerReviews as $rev)
                        @php
                            $svgSafeFallback = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80"><rect fill="%23f8fafc" width="80" height="80"/><text fill="%2394a3b8" font-family="sans-serif" font-size="10" dy="3.5" font-weight="bold" x="50%" y="50%" text-anchor="middle">Product</text></svg>';

                            $pImg = !empty($rev->product_thumbnail)
                                ? (class_exists(\App\Services\ImagePathService::class)
                                    ? \App\Services\ImagePathService::resolveUrl($rev->product_thumbnail, $svgSafeFallback)
                                    : asset($rev->product_thumbnail))
                                : $svgSafeFallback;

                            $photos = !empty($rev->images_json)
                                ? (json_decode($rev->images_json, true) ?: [])
                                : [];
                        @endphp

                        <tr>
                            {{-- Product --}}
                            <td class="col-cust-prod">
                                <div class="d-flex align-items-center gap-2">
                                    <img
                                        src="{{ $pImg }}"
                                        alt="Product"
                                        class="prod-thumb-mini"
                                        onerror="this.onerror=null; this.src='{{ $svgSafeFallback }}'"
                                    >

                                    <div class="overflow-hidden flex-grow-1">
                                        <strong
                                            class="product-title-cell d-block text-truncate"
                                            title="{{ $rev->product_title }}"
                                        >
                                            {{ $rev->product_title ?? 'Product #' . $rev->product_id }}
                                        </strong>

                                        <div class="product-id-cell font-monospace">
                                            Product ID: #{{ $rev->product_id }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Rating --}}
                            <td class="col-cust-rating">
                                <span class="review-rating">
                                    <i class="fas fa-star"></i>
                                    {{ $rev->rating }}/5
                                </span>

                                <small class="review-date font-monospace">
                                    {{ date('d M Y', strtotime($rev->created_at)) }}
                                </small>
                            </td>

                            {{-- Feedback --}}
                            <td class="col-cust-feedback">
                                @if(!empty($rev->title))
                                    <strong class="review-feedback-title">
                                        {{ $rev->title }}
                                    </strong>
                                @endif

                                <p class="review-feedback-text">
                                    {{ $rev->comment }}
                                </p>

                                @if(count($photos) > 0)
                                    <div class="d-flex gap-2 mt-2 mb-1 flex-wrap">
                                        @foreach($photos as $ph)
                                            <img
                                                src="{{ asset($ph) }}"
                                                class="review-photo-thumb"
                                                alt="Review Photo"
                                                title="View review photo"
                                                onclick="previewCustomerPhoto('{{ asset($ph) }}')"
                                                onerror="this.onerror=null; this.style.display='none'"
                                            >
                                        @endforeach
                                    </div>
                                @endif

                                @if(!empty($rev->admin_reply))
                                    <div class="store-reply">
                                        <i class="fas fa-reply text-primary me-1"></i>
                                        <strong class="text-primary">Store Reply:</strong>
                                        {{ Str::limit($rev->admin_reply, 85) }}
                                    </div>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="text-center col-cust-status">
                                @php
                                    $bClass = match($rev->status) {
                                        'approved' => 'bg-success-subtle text-success border border-success-subtle',
                                        'rejected' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                        default    => 'bg-warning-subtle text-dark border border-warning-subtle'
                                    };
                                @endphp

                                <span class="badge status-badge {{ $bClass }} text-uppercase font-monospace">
                                    {{ $rev->status }}
                                </span>
                            </td>

                            {{-- Visibility --}}
                            <td class="text-center col-cust-visible">
                                <span
                                    class="visibility-indicator {{ $rev->is_visible ? 'text-success' : 'text-muted' }}"
                                    title="{{ $rev->is_visible ? 'Visible' : 'Hidden' }}"
                                >
                                    <i class="fas {{ $rev->is_visible ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="text-end pe-3 col-cust-actions">
                                <a
                                    href="{{ route('admin.product.reviews.index', ['search' => '#' . $rev->id]) }}"
                                    class="btn btn-light border btn-compact text-primary shadow-sm"
                                    title="Moderate in Main Table"
                                >
                                    <i class="fas fa-up-right-from-square"></i>
                                    Open
                                </a>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <span class="empty-state-icon">
                                    <i class="fas fa-comment-slash"></i>
                                </span>

                                <div class="fw-bold text-dark mb-1" style="font-size: 12px;">
                                    No Reviews Found
                                </div>

                                <div style="font-size: 11px;">
                                    This customer has not submitted any product reviews yet.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($customerReviews->hasPages())
            <div class="p-3 border-top d-flex justify-content-center bg-light bg-opacity-50">
                {{ $customerReviews->links('pagination::bootstrap-5') }}
            </div>
        @endif

    </div>

</div>

{{-- Customer Photo Lightbox Preview --}}
<div
    class="modal fade c-review-modal-scope"
    id="customerPhotoPreviewModal"
    tabindex="-1"
    aria-hidden="true"
    style="z-index: 108000;"
>
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content shadow-lg bg-white">

            <div class="modal-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="panel-title-icon">
                        <i class="fas fa-image"></i>
                    </span>
                    <h6 class="fw-bold text-dark mb-0" style="font-size: 12px;">
                        Customer Review Photo
                    </h6>
                </div>

                <button
                    type="button"
                    class="btn-close shadow-none"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>
            </div>

            <div class="modal-body text-center">
                <img
                    id="customerPhotoPreviewImg"
                    src=""
                    alt="Customer Photo"
                    class="img-fluid border shadow-sm"
                >
            </div>

        </div>
    </div>
</div>

<script>
    function previewCustomerPhoto(imgUrl) {
        const imgEl = document.getElementById('customerPhotoPreviewImg');

        if (imgEl && imgUrl) {
            imgEl.src = imgUrl;

            const modal = bootstrap.Modal.getOrCreateInstance(
                document.getElementById('customerPhotoPreviewModal')
            );

            modal.show();
        }
    }
</script>
@endsection