@extends('layouts.admin')

@section('title', 'Product Reviews Analytics & Dashboard - ' . config('app.name'))

@section('admin_content')
<!-- Professional Google Fonts: Plus Jakarta Sans & Inter -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<!-- Chart.js for Interactive Trend & Distribution Charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    /* Strict 5px Element Radius Across All Controls */
    #reviewDashboardRoot,
    #reviewDashboardRoot * {
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        letter-spacing: -0.01em;
    }

    #reviewDashboardRoot .btn,
    #reviewDashboardRoot .btn-sm,
    #reviewDashboardRoot button,
    #reviewDashboardRoot .badge,
    #reviewDashboardRoot .card,
    #reviewDashboardRoot .metric-card,
    #reviewDashboardRoot .review-stat-card,
    #reviewDashboardRoot .dashboard-panel-card,
    #reviewDashboardRoot .stat-icon-square,
    #reviewDashboardRoot .review-mini-product-img,
    #reviewDashboardRoot .progress,
    #reviewDashboardRoot .progress-bar {
        border-radius: 5px !important;
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

    /* Fixed Button Hover Colors - No Color Clash */
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

    .btn-light {
        background-color: #ffffff !important;
        border-color: #cbd5e1 !important;
        color: #475569 !important;
    }
    .btn-light:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }

    /* Clean White Enterprise Header Card */
    .dashboard-header-card {
        background: #ffffff;
        color: #0f172a;
        padding: 14px 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 14px;
    }

    /* Metric Stat Cards */
    .review-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        padding: 12px 14px;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        gap: 10px;
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .review-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.07);
    }

    .stat-icon-square {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    .dashboard-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        padding: 14px 16px;
        margin-bottom: 14px;
    }

    .stat-label-title {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 2px;
        display: block;
    }
    .stat-val-num {
        font-size: 16px;
        font-weight: 700;
        line-height: 1.25;
        letter-spacing: -0.01em;
        margin-bottom: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
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

    .review-mini-product-img {
        width: 34px;
        height: 34px;
        object-fit: contain;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        flex-shrink: 0;
    }

    /* Soft Metric Highlights */
    .metric-border-blue   { border-left: 3.5px solid #2563eb !important; }
    .metric-border-amber  { border-left: 3.5px solid #f59e0b !important; }
    .metric-border-red    { border-left: 3.5px solid #ef4444 !important; }
    .metric-border-cyan   { border-left: 3.5px solid #06b6d4 !important; }
    .metric-border-green  { border-left: 3.5px solid #10b981 !important; }
    .metric-border-slate  { border-left: 3.5px solid #475569 !important; }

    /* Tables Architecture & Overlap Prevention */
    .dashboard-sub-table {
        width: 100% !important;
        margin-bottom: 0;
        border-collapse: collapse;
    }
    .dashboard-sub-table th {
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
    .dashboard-sub-table td {
        padding: 8px 10px;
        vertical-align: middle;
        font-size: 12px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }
    .dashboard-sub-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .dashboard-header-card {
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
        .review-stat-card {
            padding: 10px !important;
            gap: 8px !important;
        }
        .stat-icon-square {
            width: 30px !important;
            height: 30px !important;
            font-size: 12px !important;
        }
        .stat-val-num {
            font-size: 14px !important;
        }
        .table-responsive {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .table-responsive-feedback {
            min-width: 580px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="reviewDashboardRoot">

    <!-- 1. Header Banner Card (Clean White Light Theme) -->
    <div class="dashboard-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase" style="font-size: 10px;">
                    <i class="fas fa-chart-pie me-1"></i> ANALYTICS SUITE
                </span>
                <span class="badge bg-light text-secondary border font-monospace" style="font-size: 10px;">
                    STORE SENTIMENT ENGINE
                </span>
            </div>
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-chart-line text-primary"></i>
                <span>Product Reviews Analytics &amp; Dashboard</span>
            </h5>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                Real-time review volume, customer sentiment breakdown, response time and store performance.
            </small>
        </div>

        <div class="header-actions-group d-flex flex-wrap gap-1">
            <a href="{{ route('admin.product.reviews.index') }}" class="btn btn-primary btn-compact shadow-xs fw-bold">
                <i class="fas fa-list-check me-1"></i> All Reviews Table
            </a>
            <a href="{{ route('admin.product.reviews.auto_rules') }}" class="btn btn-outline-success btn-compact shadow-xs fw-semibold">
                <i class="fas fa-robot me-1"></i> Auto-Reply Rules
            </a>
            <a href="{{ route('admin.product.reviews.settings') }}" class="btn btn-outline-dark btn-compact shadow-xs fw-semibold">
                <i class="fas fa-gear me-1 text-secondary"></i> Review Settings
            </a>
            <button type="button" class="btn btn-success btn-compact shadow-xs fw-bold" onclick="location.href='{{ route('admin.product.reviews.index', ['export' => 'csv']) }}'">
                <i class="fas fa-file-csv me-1"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- 2. Summary Metrics 6-Grid Overview -->
    <div class="row g-2 mb-3">
        <!-- Card 1: Total Reviews -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="review-stat-card metric-border-blue">
                <div class="stat-icon-square bg-primary-subtle text-primary">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="min-w-0">
                    <span class="stat-label-title text-truncate">Total Reviews</span>
                    <h5 class="stat-val-num text-dark font-monospace bn-digit">
                        {{ number_format($summary['total_reviews'] ?? 0) }}
                    </h5>
                </div>
            </div>
        </div>

        <!-- Card 2: Average Rating -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="review-stat-card metric-border-amber">
                <div class="stat-icon-square bg-warning-subtle text-warning">
                    <i class="fas fa-star"></i>
                </div>
                <div class="min-w-0">
                    <span class="stat-label-title text-truncate">Avg Store Score</span>
                    <h5 class="stat-val-num text-dark font-monospace bn-digit">
                        ⭐ {{ number_format($summary['average_rating'] ?? 0, 1) }}
                    </h5>
                </div>
            </div>
        </div>

        <!-- Card 3: Pending Approval -->
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

        <!-- Card 4: Unanswered Feedback -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="review-stat-card metric-border-cyan">
                <div class="stat-icon-square bg-info-subtle text-info" style="color: #0891b2 !important;">
                    <i class="fas fa-reply-all"></i>
                </div>
                <div class="min-w-0">
                    <span class="stat-label-title text-truncate">Unanswered</span>
                    <h5 class="stat-val-num text-dark font-monospace bn-digit">
                        {{ number_format($summary['unanswered_count'] ?? 0) }}
                    </h5>
                </div>
            </div>
        </div>

        <!-- Card 5: With Customer Photos -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="review-stat-card metric-border-green">
                <div class="stat-icon-square bg-success-subtle text-success">
                    <i class="fas fa-camera"></i>
                </div>
                <div class="min-w-0">
                    <span class="stat-label-title text-truncate">With Photos</span>
                    <h5 class="stat-val-num text-dark font-monospace bn-digit">
                        {{ number_format($summary['with_images_count'] ?? 0) }}
                    </h5>
                </div>
            </div>
        </div>

        <!-- Card 6: Submitted Today -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="review-stat-card metric-border-slate">
                <div class="stat-icon-square bg-light text-secondary border">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="min-w-0">
                    <span class="stat-label-title text-truncate">Today's New</span>
                    <h5 class="stat-val-num text-dark font-monospace bn-digit">
                        {{ number_format($summary['today_count'] ?? 0) }}
                    </h5>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Charts Row: Trend Line & Star Rating Distribution -->
    <div class="row g-2 mb-3">
        <!-- Chart 1: 30-Day Volume & Trend -->
        <div class="col-12 col-lg-8">
            <div class="dashboard-panel-card h-100 mb-0">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13px;">
                        <i class="fas fa-chart-line text-primary"></i> 30-Day Review Submissions Trend
                    </h6>
                    <span class="badge bg-light text-secondary border font-monospace" style="font-size: 9.5px;">DAILY INFLOW</span>
                </div>
                <div style="height: 230px; width: 100%;">
                    <canvas id="reviewTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Chart 2: Star Rating Distribution -->
        <div class="col-12 col-lg-4">
            <div class="dashboard-panel-card h-100 mb-0">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13px;">
                        <i class="fas fa-star text-warning"></i> Rating Distribution
                    </h6>
                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle font-monospace" style="font-size: 9.5px;">1 TO 5 STARS</span>
                </div>

                @php
                    $totalRev = max(1, (int)($summary['total_reviews'] ?? 1));
                    $starCounts = $summary['star_counts'] ?? [5=>0, 4=>0, 3=>0, 2=>0, 1=>0];
                @endphp

                <div class="d-flex flex-column gap-2 pt-1 mb-3">
                    @for($s = 5; $s >= 1; $s--)
                        @php
                            $c = $starCounts[$s] ?? 0;
                            $pct = round(($c / $totalRev) * 100);
                        @endphp
                        <div class="d-flex align-items-center gap-2 small">
                            <span class="text-muted font-monospace" style="width: 46px; font-size: 11px;">{{ $s }} Star</span>
                            <div class="star-bar-track">
                                <div class="star-bar-fill" style="width: {{ $pct }}%;"></div>
                            </div>
                            <span class="text-dark font-monospace text-end bn-digit" style="width: 36px; font-size: 10px;">{{ $c }}</span>
                            <span class="text-muted font-monospace text-end" style="width: 30px; font-size: 9.5px;">{{ $pct }}%</span>
                        </div>
                    @endfor
                </div>

                <div class="border-top pt-2 d-flex justify-content-between align-items-center small">
                    <span class="text-muted" style="font-size: 11px;">Verified Buyer Rate:</span>
                    <strong class="font-monospace text-success bn-digit" style="font-size: 11px;">
                        {{ (!empty($summary['total_reviews']) && $summary['total_reviews'] > 0) ? round((($summary['verified_count'] ?? 0) / $summary['total_reviews']) * 100) : 0 }}%
                    </strong>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Bottom Data Tables: Top Reviewed Products & Recent Customer Submissions -->
    <div class="row g-2">
        <!-- Top Products -->
        <div class="col-12 col-lg-5">
            <div class="dashboard-panel-card h-100 mb-0 p-0 overflow-hidden">
                <div class="py-2 px-3 border-bottom bg-light d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0 small" style="font-size: 12px;">
                        <i class="fas fa-boxes-stacked text-primary me-1"></i> Most Reviewed Products
                    </h6>
                    <a href="{{ route('admin.product.index') }}" class="btn btn-sm btn-link text-decoration-none p-0 text-primary small fw-semibold font-monospace" style="font-size: 10.5px;">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle dashboard-sub-table mb-0">
                        <thead>
                            <tr>
                                <th style="min-width: 170px;">Product</th>
                                <th class="text-center" style="width: 70px; min-width: 70px;">Reviews</th>
                                <th class="text-end pe-3" style="width: 90px; min-width: 90px;">Avg Rating</th>
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
                                            <img src="{{ $pImg }}" alt="Thumb" class="review-mini-product-img shadow-xs" onerror="this.onerror=null; this.src='{{ $svgSafeFallback }}'">
                                            <div class="overflow-hidden min-w-0" style="max-width: 180px;">
                                                <strong class="d-block text-dark text-truncate" style="font-size: 12px;" title="{{ $tp->title }}">{{ $tp->title }}</strong>
                                                <a href="{{ route('admin.product.reviews.product', $tp->id) }}" class="text-primary text-decoration-none font-monospace" style="font-size: 10px;">Manage Reviews</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center font-monospace bn-digit" style="font-size: 11px;">{{ $tp->total_reviews_count }}</td>
                                    <td class="text-end pe-3 font-monospace text-warning fw-bold" style="font-size: 11px;">⭐ {{ number_format($tp->avg_rating, 1) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-3 text-muted small font-monospace" style="font-size: 11px;">No product reviews recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Customer Feedback (No Text Overlap, Clean Separation) -->
        <div class="col-12 col-lg-7">
            <div class="dashboard-panel-card h-100 mb-0 p-0 overflow-hidden">
                <div class="py-2 px-3 border-bottom bg-light d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0 small" style="font-size: 12px;">
                        <i class="fas fa-clock-rotate-left text-warning me-1"></i> Recent Customer Feedback
                    </h6>
                    <a href="{{ route('admin.product.reviews.index') }}" class="btn btn-sm btn-link text-decoration-none p-0 text-primary small fw-semibold font-monospace" style="font-size: 10.5px;">View All</a>
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
                                        <strong class="d-block text-dark text-truncate" style="max-width: 130px; font-size: 12px;">{{ $rr->customer_name ?? 'Guest Customer' }}</strong>
                                        <small class="text-muted d-block text-truncate font-monospace" style="max-width: 130px; font-size: 10px;">{{ $rr->product_title ?? 'Product #' . $rr->product_id }}</small>
                                    </td>
                                    <td class="font-monospace text-warning fw-bold" style="white-space: nowrap; font-size: 11px;">
                                        ⭐ {{ $rr->rating }}/5
                                    </td>
                                    <td>
                                        <p class="mb-0 text-dark text-truncate" style="font-size: 11px; line-height: 1.4; max-width: 210px;" title="{{ $rr->comment }}">
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
                                        <span class="badge {{ $bClass }} text-uppercase font-monospace px-1.5 py-0.5" style="font-size: 9px;">{{ $rr->status }}</span>
                                    </td>
                                    <td class="text-end pe-3" style="white-space: nowrap;">
                                        <a href="{{ route('admin.product.reviews.index', ['search' => '#' . $rr->id]) }}" class="btn btn-light border btn-compact py-0 px-1 text-primary shadow-xs" style="width: 26px; height: 26px; font-size: 10px;" title="Moderate">
                                            <i class="fas fa-up-right-from-square"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted small font-monospace" style="font-size: 11px;">No recent customer reviews found.</td>
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