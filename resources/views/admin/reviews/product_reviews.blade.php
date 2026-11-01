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
    /* Strict 5px Element Radius Across All Controls */
    #productReviewsRoot .btn,
    #productReviewsRoot .btn-sm,
    #productReviewsRoot button,
    #productReviewsRoot .badge,
    #productReviewsRoot .form-control,
    #productReviewsRoot .form-select,
    #productReviewsRoot .card,
    #productReviewsRoot .metric-card,
    #productReviewsRoot .p-header-card,
    #productReviewsRoot .p-panel-card,
    #productReviewsRoot .alert,
    #productReviewsRoot .pinned-item-row,
    #productReviewsRoot .pin-rank-badge,
    #productReviewsRoot .prod-banner-thumb,
    .p-modal-scope .modal-content,
    .p-modal-scope .btn {
        border-radius: 5px !important;
    }

    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
    }

    .btn-compact {
        font-size: 11px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    /* Clean White Enterprise Header Card */
    .p-header-card {
        background: #ffffff;
        color: #0f172a;
        padding: 14px 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 14px;
    }

    /* Metric Cards - Zero Overflow Architecture */
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

    .p-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        padding: 14px 16px;
        margin-bottom: 14px;
    }

    .section-title-sm {
        font-size: 12px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .prod-banner-thumb {
        width: 60px;
        height: 60px;
        object-fit: contain;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        padding: 3px;
        flex-shrink: 0;
    }

    .star-bar-track {
        height: 6px;
        background: #f1f5f9;
        border-radius: 4px;
        overflow: hidden;
        flex-grow: 1;
    }
    .star-bar-fill {
        height: 100%;
        background: #f59e0b;
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    .pinned-item-row {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 10px 12px;
        margin-bottom: 8px;
        transition: all 0.15s ease;
    }
    .pinned-item-row:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .pin-rank-badge {
        width: 24px;
        height: 24px;
        background: #f59e0b;
        color: #0f172a;
        font-weight: 700;
        font-size: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    #productReviewsRoot .form-control,
    #productReviewsRoot .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 4px 8px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #productReviewsRoot .form-control:focus,
    #productReviewsRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }

    .form-check-input {
        width: 16px;
        height: 16px;
        cursor: pointer;
        border-radius: 4px !important;
    }
    .form-check-label {
        font-size: 11px;
        font-weight: 600;
        color: #1e293b;
        cursor: pointer;
    }

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .p-header-card {
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
        .header-actions-group .btn-view-all {
            grid-column: span 2;
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
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="productReviewsRoot">

    <!-- 1. Header Banner Card (Clean White Light Theme - Black Color Removed) -->
    <div class="p-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase" style="font-size: 10px;">
                    <i class="fas fa-boxes-packing me-1"></i> PRODUCT REVIEW HUB
                </span>
                <span class="badge bg-light text-secondary border font-monospace" style="font-size: 10px;">
                    ITEM #{{ $product->id }}
                </span>
            </div>
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-star-half-stroke text-warning"></i>
                <span>Review Hub: {{ Str::limit($product->title ?? 'Product #' . $product->id, 45) }}</span>
            </h5>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                Manage product-specific reviews, pin top 5 featured testimonials, and override global review policies.
            </small>
        </div>

        <div class="header-actions-group d-flex flex-wrap gap-1">
            <a href="{{ route('admin.product.reviews.index', ['product_id' => $product->id]) }}" class="btn btn-primary btn-compact shadow-xs fw-bold">
                <i class="fas fa-filter me-1"></i> Filter Reviews Table
            </a>
            <a href="{{ route('product.details', $product->slug ?? $product->id) }}" target="_blank" class="btn btn-outline-dark btn-compact shadow-xs bg-white fw-semibold">
                <i class="fas fa-arrow-up-right-from-square me-1 text-primary"></i> View Live Product
            </a>
            <a href="{{ route('admin.product.reviews.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs bg-white text-dark fw-semibold btn-view-all">
                <i class="fas fa-arrow-left me-1"></i> All Reviews
            </a>
        </div>
    </div>

    <!-- 2. Product Reviews Telemetry KPI Status Cards -->
    <div class="row g-2 mb-3">
        <!-- KPI 1: Overall Average Rating -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-amber h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Average Rating</span>
                    <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                        ⭐ {{ number_format($avgRatingScore, 1) }} <span class="text-muted fw-normal" style="font-size: 11px;">/ 5.0</span>
                    </h4>
                    <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Calculated score</small>
                </div>
            </div>
        </div>

        <!-- KPI 2: Total Reviews & Volume -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-blue h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Review Volume</span>
                    <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                        <i class="fas fa-comments"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                        {{ number_format($totalReviewsCount) }}
                    </h4>
                    <small class="text-muted d-block text-truncate font-monospace stat-sub-text">{{ $verifiedCount }} verified buyer(s)</small>
                </div>
            </div>
        </div>

        <!-- KPI 3: Pinned Testimonials -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-purple h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Featured Pinned</span>
                    <div class="metric-icon-square bg-purple-subtle text-primary shadow-xs" style="background-color: #f3e8ff; color: #7e22ce;">
                        <i class="fas fa-thumbtack"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                        {{ $pinnedCount }} <span class="text-muted fw-normal" style="font-size: 11px;">/ 5 max</span>
                    </h4>
                    <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Top storefront priority</small>
                </div>
            </div>
        </div>

        <!-- KPI 4: Policy Override Mode -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-green h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Policy Status</span>
                    <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-success font-monospace stat-val-num" style="font-size: 14px;">
                        @if(is_null($rawOverride?->reviews_enabled))
                            Global Default
                        @elseif($rawOverride->reviews_enabled == 1)
                            Force Enabled
                        @else
                            Force Disabled
                        @endif
                    </h4>
                    <small class="text-success d-block text-truncate font-monospace stat-sub-text">
                        <i class="fas fa-check-circle me-1"></i>Active rule policy
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small border-0 shadow-xs d-flex align-items-center justify-content-between mb-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-xs d-flex align-items-center justify-content-between mb-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- 3. Product Overview Banner Showcase Card -->
    <div class="p-panel-card p-3 mb-3">
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3">
            <img src="{{ $pImg }}" alt="{{ $product->title }}" class="prod-banner-thumb shadow-xs" onerror="this.onerror=null; this.src='{{ $svgFallback }}'">
            <div class="min-w-0 flex-grow-1">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 14px;" title="{{ $product->title }}">
                        {{ $product->title }}
                    </h6>
                    <span class="badge bg-light text-muted border font-monospace" style="font-size: 10px;">ID: #{{ $product->id }}</span>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-3 small text-muted">
                    <span>Code/SKU: <strong class="text-dark font-monospace">{{ $product->product_code ?? ($product->sku ?? '—') }}</strong></span>
                    <span>Price: <strong class="text-primary font-monospace bn-digit">৳{{ number_format((float)($product->sale_price ?? $product->regular_price ?? 0), 2) }}</strong></span>
                    <span>Stock: <strong class="font-monospace {{ $product->stock > 0 ? 'text-success' : 'text-danger' }}">{{ $product->stock }} units</strong></span>
                </div>
            </div>
            <div class="text-sm-end flex-shrink-0">
                <div class="fs-4 fw-bold font-monospace text-warning mb-0">⭐ {{ number_format($ratingStats['average_rating'], 1) }}</div>
                <small class="text-muted font-monospace bn-digit">{{ $ratingStats['total_reviews'] }} Ratings</small>
            </div>
        </div>
    </div>

    <!-- 4. Modular 3-Tier Content Layout -->
    <div class="row g-2 g-md-3">
        <!-- Left Column: Rating Breakdown & Top 5 Pinned Reviews -->
        <div class="col-12 col-lg-7">
            <!-- TIER 1: Star Rating Sentiment Distribution -->
            <div class="p-panel-card mb-3">
                <div class="section-title-sm">
                    <i class="fas fa-star-half-stroke text-warning"></i>
                    <span>Rating Sentiment Breakdown</span>
                </div>

                <div class="d-flex flex-column gap-2 pt-1 mb-2">
                    @for($s = 5; $s >= 1; $s--)
                        @php
                            $c = $ratingStats['distribution'][$s] ?? 0;
                            $pct = $ratingStats['percentages'][$s] ?? 0;
                        @endphp
                        <div class="d-flex align-items-center gap-2 small">
                            <span class="text-muted font-monospace" style="width: 52px; font-size: 11px;">{{ $s }} Star</span>
                            <div class="star-bar-track">
                                <div class="star-bar-fill" style="width: {{ $pct }}%;"></div>
                            </div>
                            <span class="text-dark font-monospace text-end bn-digit" style="width: 38px; font-size: 10px;">{{ $c }}</span>
                            <span class="text-muted font-monospace text-end" style="width: 34px; font-size: 9.5px;">{{ $pct }}%</span>
                        </div>
                    @endfor
                </div>

                <div class="border-top pt-2 mt-2 d-flex flex-wrap justify-content-between align-items-center small text-muted font-monospace" style="font-size: 10.5px;">
                    <span>Verified Buyers: <strong class="text-success bn-digit">{{ $ratingStats['verified_count'] }}</strong></span>
                    <span>Reviews with Photos: <strong class="text-primary bn-digit">{{ $ratingStats['with_images_count'] }}</strong></span>
                </div>
            </div>

            <!-- TIER 2: Featured Top 5 Pinned Reviews -->
            <div class="p-panel-card mb-0">
                <div class="d-flex justify-content-between align-items-center section-title-sm">
                    <div class="d-flex align-items-center gap-1">
                        <i class="fas fa-thumbtack text-warning"></i>
                        <span>Featured Top 5 Pinned Reviews</span>
                    </div>
                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle font-monospace" style="font-size: 9.5px;">
                        {{ count($pinnedReviews) }} / 5 Pinned
                    </span>
                </div>

                <p class="text-muted small mb-3 font-monospace" style="font-size: 11px; line-height: 1.4;">
                    These pinned reviews appear at the very beginning of the product details page review section regardless of standard pagination sorting.
                </p>

                <div id="pinnedReviewsList">
                    @forelse($pinnedReviews as $pin)
                        <div class="pinned-item-row d-flex align-items-start justify-content-between gap-2 shadow-xs" id="pinned-row-{{ $pin->id }}">
                            <div class="d-flex align-items-start gap-2 min-w-0">
                                <div class="pin-rank-badge font-monospace" title="Display Order">
                                    #{{ $pin->pin_order ?: 1 }}
                                </div>
                                <div class="min-w-0">
                                    <div class="d-flex align-items-center gap-1 mb-1">
                                        <strong class="text-dark small text-truncate" style="font-size: 12px;">{{ $pin->customer_name ?? 'Customer' }}</strong>
                                        <span class="font-monospace text-warning fw-bold small" style="font-size: 11px;">⭐ {{ $pin->rating }}/5</span>
                                    </div>
                                    <p class="mb-1 text-muted" style="font-size: 11px; line-height: 1.4;">
                                        {{ Str::limit($pin->comment, 120) }}
                                    </p>
                                    @if(!empty($pin->admin_reply))
                                        <small class="text-primary d-block font-monospace" style="font-size: 10px;">
                                            <i class="fas fa-reply me-1"></i> Replied: {{ Str::limit($pin->admin_reply, 60) }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                <button type="button" class="btn btn-light border btn-compact py-0 px-2 text-danger shadow-xs" onclick="triggerUnpinConfirm({{ $pin->id }})" title="Unpin Review">
                                    <i class="fas fa-thumbtack-slash me-1"></i> Unpin
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 bg-light border rounded text-muted small font-monospace" style="font-size: 11px;">
                            <i class="fas fa-thumbtack fa-2x mb-2 d-block opacity-25"></i>
                            No reviews are pinned for this product yet.<br>
                            Click the pin icon in the <a href="{{ route('admin.product.reviews.index', ['product_id' => $product->id]) }}" class="fw-semibold text-primary">Reviews Table</a> to pin up to 5 testimonials.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column: Product-Level Override Settings Form -->
        <div class="col-12 col-lg-5">
            <!-- TIER 3: Product-Specific Review Policies Form -->
            <div class="p-panel-card mb-0">
                <div class="section-title-sm">
                    <i class="fas fa-sliders text-primary"></i>
                    <span>Product-Specific Review Policies</span>
                </div>

                <form action="{{ route('admin.product.reviews.product.settings', $product->id) }}" method="POST">
                    @csrf

                    <!-- 1. Reviews Enabled Override -->
                    <div class="mb-3 pb-2 border-bottom">
                        <label class="form-label">Review Submission on This Product:</label>
                        <select name="reviews_enabled" class="form-select font-monospace">
                            <option value="" {{ is_null($rawOverride?->reviews_enabled) ? 'selected' : '' }}>Follow Global Setting (Default)</option>
                            <option value="1" {{ ($rawOverride?->reviews_enabled === 1 || $rawOverride?->reviews_enabled === '1') ? 'selected' : '' }}>Force Enabled for This Product</option>
                            <option value="0" {{ ($rawOverride?->reviews_enabled === 0 || $rawOverride?->reviews_enabled === '0') ? 'selected' : '' }}>Force Disabled (Disable Reviews)</option>
                        </select>
                        <small class="text-muted d-block mt-1 font-monospace" style="font-size: 10px;">Disabling prevents new review submissions for this item.</small>
                    </div>

                    <!-- 2. Existing Reviews Display Policy -->
                    <div class="form-check form-switch mb-3 pb-2 border-bottom">
                        <input class="form-check-input" type="checkbox" name="show_existing_reviews" id="setShowExisting" value="1" {{ !isset($rawOverride->show_existing_reviews) || $rawOverride->show_existing_reviews ? 'checked' : '' }}>
                        <label class="form-check-label" for="setShowExisting">Keep Existing Reviews Visible</label>
                        <small class="text-muted d-block font-monospace" style="font-size: 10px;">If review submission is turned off, existing approved ratings will still be displayed.</small>
                    </div>

                    <!-- 3. Image Uploads Policy -->
                    <div class="mb-3 pb-2 border-bottom">
                        <label class="form-label">Customer Photos Upload:</label>
                        <select name="image_reviews_enabled" class="form-select font-monospace">
                            <option value="" {{ is_null($rawOverride?->image_reviews_enabled) ? 'selected' : '' }}>Follow Global Setting</option>
                            <option value="1" {{ ($rawOverride?->image_reviews_enabled === 1 || $rawOverride?->image_reviews_enabled === '1') ? 'selected' : '' }}>Allow Photo Attachments</option>
                            <option value="0" {{ ($rawOverride?->image_reviews_enabled === 0 || $rawOverride?->image_reviews_enabled === '0') ? 'selected' : '' }}>Block Photo Attachments</option>
                        </select>
                    </div>

                    <!-- 4. Approval Policy Override -->
                    <div class="mb-3 pb-2 border-bottom">
                        <label class="form-label">Moderation Policy:</label>
                        <select name="approval_policy" class="form-select font-monospace">
                            <option value="default" {{ empty($rawOverride?->approval_policy) || $rawOverride?->approval_policy === 'default' ? 'selected' : '' }}>Follow Global Moderation Rule</option>
                            <option value="auto" {{ ($rawOverride?->approval_policy ?? '') === 'auto' ? 'selected' : '' }}>Auto-Approve Immediately</option>
                            <option value="manual" {{ ($rawOverride?->approval_policy ?? '') === 'manual' ? 'selected' : '' }}>Strict Manual Approval Required</option>
                        </select>
                    </div>

                    <!-- 5. Max Images, Image Size & Per Page Limits -->
                    <div class="row g-2 mb-3 pb-2 border-bottom">
                        <div class="col-4">
                            <label class="form-label" style="font-size: 10px;">Max Photos:</label>
                            <input type="number" name="max_images" class="form-control text-center font-monospace bn-digit" min="1" max="10" placeholder="Default" value="{{ $rawOverride->max_images ?? '' }}">
                        </div>
                        <div class="col-4">
                            <label class="form-label" style="font-size: 10px;">Max Size (KB):</label>
                            <input type="number" name="max_image_size_kb" class="form-control text-center font-monospace bn-digit" min="512" max="10240" step="512" placeholder="Default" value="{{ $rawOverride->max_image_size_kb ?? '' }}">
                        </div>
                        <div class="col-4">
                            <label class="form-label" style="font-size: 10px;">Page Size:</label>
                            <input type="number" name="per_page" class="form-control text-center font-monospace bn-digit" min="2" max="50" placeholder="Default" value="{{ $rawOverride->per_page ?? '' }}">
                        </div>
                    </div>

                    <!-- 6. Auto-Reply Override -->
                    <div class="mb-3">
                        <label class="form-label">Smart Auto-Reply Engine:</label>
                        <select name="auto_reply_enabled" class="form-select font-monospace">
                            <option value="" {{ is_null($rawOverride?->auto_reply_enabled) ? 'selected' : '' }}>Follow Global Automation Rules</option>
                            <option value="1" {{ ($rawOverride?->auto_reply_enabled === 1 || $rawOverride?->auto_reply_enabled === '1') ? 'selected' : '' }}>Enabled for This Product</option>
                            <option value="0" {{ ($rawOverride?->auto_reply_enabled === 0 || $rawOverride?->auto_reply_enabled === '0') ? 'selected' : '' }}>Disable Auto-Reply for This Item</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-compact py-2 shadow-xs fw-bold" style="min-height: 34px;">
                        <i class="fas fa-save me-1"></i> Save Product Override Policies
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Unpin Confirmation Modal -->
<div class="modal fade p-modal-scope" id="unpinConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-2">
                <div class="mb-2 text-warning" style="font-size: 32px;">
                    <i class="fas fa-thumbtack"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Unpin Review?</h6>
                <p class="text-muted small mb-3 font-monospace" style="font-size: 11px; line-height: 1.45;">Remove this review from the featured top position on this product page?</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-compact px-3 border" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-compact px-3 fw-bold shadow-xs" id="confirmUnpinExecuteBtn">Yes, Unpin</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Reusable Admin Alert Modal -->
<div class="modal fade p-modal-scope" id="productReviewAlertModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-2">
                <div id="pReviewAlertIcon" class="mb-2" style="font-size: 32px;"></div>
                <h6 id="pReviewAlertTitle" class="fw-bold mb-1 small text-dark"></h6>
                <p id="pReviewAlertMessage" class="text-muted small mb-3 font-monospace" style="font-size: 11px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-primary btn-compact px-4 fw-bold shadow-xs" data-bs-dismiss="modal" id="pReviewAlertCloseBtn">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
let targetUnpinId = null;

function showProductAlert(title, message, type = 'info', reloadOnClose = false) {
    const modalEl = document.getElementById('productReviewAlertModal');
    const iconEl  = document.getElementById('pReviewAlertIcon');
    const titleEl = document.getElementById('pReviewAlertTitle');
    const msgEl   = document.getElementById('pReviewAlertMessage');
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
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('unpinConfirmModal'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', () => {
    const execBtn = document.getElementById('confirmUnpinExecuteBtn');
    if (execBtn) {
        execBtn.addEventListener('click', () => {
            if (!targetUnpinId) return;

            const confirmModal = bootstrap.Modal.getInstance(document.getElementById('unpinConfirmModal'));
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
                    showProductAlert('Success', data.message || 'Review unpinned successfully.', 'success', true);
                } else {
                    showProductAlert('Notice', data.message || 'Failed to unpin review.', 'error', false);
                }
            })
            .catch(() => showProductAlert('Error', 'Network error occurred while unpinning.', 'error', false));
        });
    }
});
</script>
@endsection