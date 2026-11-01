@extends('layouts.admin')

@section('title', 'Customer Review Profile - ' . ($customer->name ?? 'Customer #' . $customer->id))

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
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
    .c-header-card {
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

    .c-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        padding: 14px 16px;
        margin-bottom: 14px;
    }

    .customer-avatar-circle {
        width: 52px;
        height: 52px;
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
        color: #ffffff;
        font-weight: 700;
        font-size: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50% !important;
        border: 2px solid #e2e8f0;
        flex-shrink: 0;
    }

    /* Table Architecture: 100% Fit Screen with Zero Horizontal Scroll on Desktop */
    .c-reviews-table {
        width: 100% !important;
        margin-bottom: 0;
        border-collapse: collapse;
    }
    .c-reviews-table th {
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
    .c-reviews-table td {
        padding: 8px 10px;
        vertical-align: middle;
        font-size: 12px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .c-reviews-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .c-reviews-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .c-reviews-table th,
        .c-reviews-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 6 Columns Proportional Distribution Summing to Exactly 100% */
        .col-cust-prod     { width: 24% !important; }
        .col-cust-rating   { width: 12% !important; }
        .col-cust-feedback { width: 38% !important; }
        .col-cust-status   { width: 10% !important; }
        .col-cust-visible  { width: 6% !important; }
        .col-cust-actions  { width: 10% !important; }
    }

    .prod-thumb-mini {
        width: 34px;
        height: 34px;
        object-fit: contain;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        flex-shrink: 0;
    }

    .review-photo-thumb {
        width: 30px;
        height: 30px;
        object-fit: cover;
        border: 1px solid #cbd5e1;
        cursor: pointer;
        display: inline-block;
        flex-shrink: 0;
        transition: transform 0.15s ease, border-color 0.15s ease;
    }
    .review-photo-thumb:hover {
        transform: scale(1.1);
        border-color: #2563eb;
    }

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .c-header-card {
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
        .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .c-reviews-table {
            min-width: 780px !important;
            table-layout: auto !important;
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

<div class="container-fluid px-2 px-md-4 py-3" id="customerReviewsRoot">

    <!-- 1. Header Banner Card (Clean White Light Theme) -->
    <div class="c-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase" style="font-size: 10px;">
                    <i class="fas fa-user-pen me-1"></i> CUSTOMER AUDIT
                </span>
                <span class="badge bg-light text-secondary border font-monospace" style="font-size: 10px;">
                    VERIFIED BUYER HISTORY
                </span>
            </div>
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-user-check text-primary"></i>
                <span>Review Profile: {{ $customer->name ?? 'Customer #' . $customer->id }}</span>
            </h5>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                Inspect all product reviews, average rating sentiment, and verified buyer history for this customer.
            </small>
        </div>

        <div class="header-actions-group d-flex flex-wrap gap-1">
            <a href="{{ route('admin.product.reviews.index', ['user_id' => $customer->id]) }}" class="btn btn-primary btn-compact shadow-xs fw-bold">
                <i class="fas fa-list-check me-1"></i> Filter in Main Table
            </a>
            <a href="{{ route('admin.product.reviews.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs bg-white text-dark fw-semibold">
                <i class="fas fa-arrow-left me-1"></i> All Reviews
            </a>
        </div>
    </div>

    <!-- 2. Customer Reviews Telemetry KPI Status Cards -->
    <div class="row g-2 mb-3">
        <!-- KPI 1: Total Submitted Reviews -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-blue h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Submitted Reviews</span>
                    <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                        <i class="fas fa-comments"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                        {{ number_format($customerStats['total_reviews'] ?? 0) }}
                    </h4>
                    <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Total feedback entries</small>
                </div>
            </div>
        </div>

        <!-- KPI 2: Average Rating Score -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-amber h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Average Rating</span>
                    <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                        <i class="fas fa-star-half-stroke"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                        ⭐ {{ number_format($customerStats['avg_rating'] ?? 0, 1) }}
                    </h4>
                    <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Lifetime sentiment score</small>
                </div>
            </div>
        </div>

        <!-- KPI 3: Approved Reviews -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-green h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Approved Reviews</span>
                    <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                        <i class="fas fa-circle-check"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num">
                        {{ number_format($customerStats['approved'] ?? 0) }}
                    </h4>
                    <small class="text-success d-block text-truncate font-monospace stat-sub-text">
                        <i class="fas fa-bolt me-1"></i>Live on storefront
                    </small>
                </div>
            </div>
        </div>

        <!-- KPI 4: Rejected / Flagged -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-purple h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Rejected Feedback</span>
                    <div class="metric-icon-square bg-danger-subtle text-danger shadow-xs">
                        <i class="fas fa-circle-xmark"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-danger bn-digit font-monospace stat-val-num">
                        {{ number_format($customerStats['rejected'] ?? 0) }}
                    </h4>
                    <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Disapproved / spam</small>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Customer Master Profile Overview Banner Card -->
    <div class="c-panel-card p-3 mb-3">
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3 min-w-0">
                <div class="customer-avatar-circle shadow-xs">
                    {{ strtoupper(substr($customer->name ?? 'C', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 14px;">
                            {{ $customer->name ?? 'Guest Customer' }}
                        </h6>
                        <span class="badge bg-light text-muted border font-monospace" style="font-size: 9px;">User ID: #{{ $customer->id }}</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace" style="font-size: 9px;">Registered Customer</span>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-3 small text-muted font-monospace" style="font-size: 11px;">
                        <span><i class="fas fa-envelope me-1 text-primary"></i> {{ $customer->email ?? 'No email recorded' }}</span>
                        <span><i class="fas fa-phone me-1 text-success"></i> <strong class="text-dark">{{ $customer->phone ?? 'N/A' }}</strong></span>
                        <span><i class="fas fa-calendar-alt me-1 text-secondary"></i> Joined: {{ date('d M Y', strtotime($customer->created_at)) }}</span>
                    </div>
                </div>
            </div>
            <div class="text-sm-end flex-shrink-0">
                <div class="fs-4 fw-bold font-monospace text-warning mb-0">⭐ {{ number_format($customerStats['avg_rating'] ?? 0, 1) }}</div>
                <small class="text-muted font-monospace bn-digit">Lifetime Score</small>
            </div>
        </div>
    </div>

    <!-- 4. Customer Reviews Ledger Table (Desktop 100% Fit Screen with Zero Horizontal Scroll) -->
    <div class="c-panel-card p-0 overflow-hidden mb-3">
        <div class="py-2 px-3 border-bottom bg-light d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0 small" style="font-size: 12px;">
                <i class="fas fa-clock-rotate-left text-primary me-1"></i> Reviews History by {{ $customer->name ?? 'Customer' }}
            </h6>
            <span class="badge bg-light text-muted border font-monospace bn-digit" style="font-size: 10px;">{{ $customerReviews->total() }} Entries</span>
        </div>

        <div class="table-responsive pc-fit-container">
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
                                ? (class_exists(\App\Services\ImagePathService::class) ? \App\Services\ImagePathService::resolveUrl($rev->product_thumbnail, $svgSafeFallback) : asset($rev->product_thumbnail)) 
                                : $svgSafeFallback;

                            $photos = !empty($rev->images_json) ? (json_decode($rev->images_json, true) ?: []) : [];
                        @endphp
                        <tr>
                            <td class="col-cust-prod">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $pImg }}" alt="Product" class="prod-thumb-mini shadow-xs" onerror="this.onerror=null; this.src='{{ $svgSafeFallback }}'">
                                    <div class="overflow-hidden" style="max-width: 190px;">
                                        <strong class="d-block text-dark text-truncate" style="font-size: 12px;" title="{{ $rev->product_title }}">
                                            {{ $rev->product_title ?? 'Product #' . $rev->product_id }}
                                        </strong>
                                        <small class="text-muted font-monospace" style="font-size: 10px;">ID: #{{ $rev->product_id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="col-cust-rating">
                                <span class="font-monospace text-warning fw-bold small text-nowrap" style="font-size: 11px;">
                                    ⭐ {{ $rev->rating }}/5
                                </span>
                                <small class="text-muted font-monospace d-block" style="font-size: 10px;">{{ date('d M Y', strtotime($rev->created_at)) }}</small>
                            </td>
                            <td class="col-cust-feedback">
                                @if(!empty($rev->title))
                                    <strong class="d-block text-dark mb-1" style="font-size: 11px;">{{ $rev->title }}</strong>
                                @endif
                                <p class="mb-1 text-dark" style="font-size: 11px; line-height: 1.4; max-width: 420px;">
                                    {{ $rev->comment }}
                                </p>

                                @if(count($photos) > 0)
                                    <div class="d-flex gap-1 mt-1 mb-1 flex-wrap">
                                        @foreach($photos as $ph)
                                            <img src="{{ asset($ph) }}" class="review-photo-thumb shadow-xs" alt="Review Photo" onclick="previewCustomerPhoto('{{ asset($ph) }}')" onerror="this.onerror=null; this.style.display='none'">
                                        @endforeach
                                    </div>
                                @endif

                                @if(!empty($rev->admin_reply))
                                    <div class="mt-1 small text-primary p-2 bg-light border-start border-3 border-primary" style="font-size: 10px; max-width: 420px; line-height: 1.4; border-radius: 0 4px 4px 0 !important;">
                                        <i class="fas fa-reply me-1"></i> <strong>Store Reply:</strong> {{ Str::limit($rev->admin_reply, 85) }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-center col-cust-status">
                                @php
                                    $bClass = match($rev->status) {
                                        'approved' => 'bg-success-subtle text-success border border-success-subtle',
                                        'rejected' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                        default    => 'bg-warning-subtle text-dark border border-warning-subtle'
                                    };
                                @endphp
                                <span class="badge {{ $bClass }} text-uppercase font-monospace px-2 py-0" style="font-size: 9px;">
                                    {{ $rev->status }}
                                </span>
                            </td>
                            <td class="text-center col-cust-visible">
                                <i class="fas {{ $rev->is_visible ? 'fa-eye text-success' : 'fa-eye-slash text-muted' }}" style="font-size: 12px;" title="{{ $rev->is_visible ? 'Visible' : 'Hidden' }}"></i>
                            </td>
                            <td class="text-end pe-3 col-cust-actions">
                                <a href="{{ route('admin.product.reviews.index', ['search' => '#' . $rev->id]) }}" class="btn btn-light border btn-compact py-0 px-2 text-primary shadow-xs" title="Moderate in Main Table">
                                    <i class="fas fa-up-right-from-square me-1" style="font-size: 10px;"></i> Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted small font-monospace" style="font-size: 11px;">
                                <i class="fas fa-comment-slash fa-2x mb-2 d-block opacity-25"></i>
                                This customer has not submitted any product reviews yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customerReviews->hasPages())
            <div class="p-2 px-3 border-top d-flex justify-content-center bg-light bg-opacity-50">
                {{ $customerReviews->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

<!-- Modal: Customer Photo Lightbox Preview -->
<div class="modal fade c-review-modal-scope" id="customerPhotoPreviewModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2 bg-white">
            <div class="d-flex justify-content-end">
                <button type="button" class="btn-close shadow-none btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="p-2">
                <img id="customerPhotoPreviewImg" src="" alt="Customer Photo" class="img-fluid border shadow-xs" style="max-height: 480px; object-fit: contain; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<script>
function previewCustomerPhoto(imgUrl) {
    const imgEl = document.getElementById('customerPhotoPreviewImg');
    if (imgEl && imgUrl) {
        imgEl.src = imgUrl;
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('customerPhotoPreviewModal'));
        modal.show();
    }
}
</script>
@endsection