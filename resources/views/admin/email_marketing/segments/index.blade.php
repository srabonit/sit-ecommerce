@extends('layouts.admin')

@section('title', 'Audience Segments - Email Marketing')

@php
    $totalSegmentsCount    = $stats['total'] ?? $segments->total();
    $dynamicCount          = $stats['dynamic'] ?? $segments->where('type', 'dynamic')->count();
    $snapshotCount         = $stats['snapshot'] ?? $segments->where('type', 'snapshot')->count();
    $totalMatchingAudience = $stats['total_reach'] ?? $segments->sum(fn($s) => (int)($s->matching_subscribers_count ?? 0));
@endphp

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-segments-root .btn,
    #email-marketing-segments-root .btn-sm,
    #email-marketing-segments-root button,
    #email-marketing-segments-root .badge,
    #email-marketing-segments-root .alert,
    #email-marketing-segments-root .form-control,
    #email-marketing-segments-root .form-select,
    #email-marketing-segments-root .input-group-text,
    #email-marketing-segments-root .card,
    #email-marketing-segments-root .metric-card,
    #email-marketing-segments-root .page-link,
    #email-marketing-segments-root .page-item span,
    #email-marketing-segments-root .collapse-toggle-btn,
    .segment-modal-scope .modal-content,
    .segment-modal-scope .btn,
    .admin-segment-toast {
        border-radius: 5px !important;
    }

    #email-marketing-segments-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #email-marketing-segments-root .input-group > .form-control,
    #email-marketing-segments-root .input-group > .form-select {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Safe Monospace & Bengali Digit Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Compact Sleek Button Styling */
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

    .segment-filter-control {
        font-size: 12px !important;
        height: 32px !important;
        padding: 4px 8px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        box-shadow: none !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .segment-filter-control:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        outline: none;
    }

    .segment-addon-text {
        height: 32px !important;
        background-color: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: #64748b !important;
        font-size: 11px !important;
    }

    /* Collapsible Trigger Buttons & Rotating Chevron */
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

    /* Enterprise Surface Cards */
    #email-marketing-segments-root .card {
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }
    .section-header-wrap {
        padding: 10px 14px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
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
        font-size: 18px;
        line-height: 1.25;
        letter-spacing: -0.02em;
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
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-left: 3.5px solid #10b981 !important; }
    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-left: 3.5px solid #f59e0b !important; }
    .metric-card-cyan   { background: linear-gradient(180deg, #ffffff 0%, #ecfeff 100%); border-left: 3.5px solid #06b6d4 !important; }

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .custom-segments-table {
        width: 100%;
        margin-bottom: 0;
    }
    .custom-segments-table thead th {
        font-size: 10px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 8px;
        white-space: nowrap;
    }
    .custom-segments-table tbody td {
        padding: 8px 8px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        font-size: 11px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .custom-segments-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .custom-segments-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .custom-segments-table th,
        .custom-segments-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 8 Columns Proportional Distribution Summing to Exactly 100% */
        .col-sg-cb     { width: 4% !important; }
        .col-sg-id     { width: 5% !important; }
        .col-sg-name   { width: 33% !important; }
        .col-sg-type   { width: 12% !important; }
        .col-sg-match  { width: 14% !important; }
        .col-sg-camps  { width: 10% !important; }
        .col-sg-date   { width: 12% !important; }
        .col-sg-action { width: 10% !important; }
    }

    /* Bulletproof Responsive Pagination */
    #segments-pagination-links nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }
    #segments-pagination-links nav > div.d-sm-none,
    #segments-pagination-links nav > .d-sm-none,
    #segments-pagination-links nav p,
    #segments-pagination-links nav .small.text-muted,
    #segments-pagination-links nav div:has(> p) {
        display: none !important;
    }
    #segments-pagination-links .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 3px !important;
        padding: 0 !important;
        list-style: none !important;
    }
    #segments-pagination-links .page-item {
        display: inline-flex !important;
    }
    #segments-pagination-links .page-item .page-link,
    #segments-pagination-links .page-item span {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 25px !important;
        height: 25px !important;
        padding: 0 6px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        color: #475569 !important;
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 5px !important;
        text-decoration: none !important;
        line-height: 1 !important;
        transition: all 0.12s ease !important;
    }
    #segments-pagination-links .page-item .page-link:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }
    #segments-pagination-links .page-item.active .page-link,
    #segments-pagination-links .page-item.active span {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25) !important;
    }
    #segments-pagination-links .page-item.disabled .page-link,
    #segments-pagination-links .page-item.disabled span {
        opacity: 0.45 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }

    /* Floating Toast Box */
    .admin-segment-toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 108000;
        min-width: 280px;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.2);
        animation: slideInRight 0.25s ease;
    }
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .segments-header-wrap h5 {
            font-size: 16px !important;
        }
        .top-action-btns {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .top-action-btns .btn-create-main {
            grid-column: span 2;
        }
        .top-action-btns .btn {
            width: 100%;
            padding: 8px 6px !important;
            font-size: 11px !important;
            justify-content: center;
        }
        .metric-card {
            padding: 10px !important;
            min-height: 84px !important;
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
        .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .custom-segments-table {
            min-width: 820px !important;
            table-layout: auto !important;
        }
        #email-marketing-segments-root .card {
            margin-bottom: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-segments-root">

    {{-- 1. Header & Actions (Original Clean Light Theme) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 segments-header-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-filter-circle-dollar text-primary"></i>
                    <span>Audience Segments</span>
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace" id="segments-total-badge" style="font-size: 10px;">
                    {{ $totalSegmentsCount }} Segments
                </span>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                Target specific customer groups based on purchase history, cart abandonment, and customer engagement.
            </small>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 top-action-btns">
            <button type="button" class="btn btn-sm btn-outline-success btn-compact bg-white shadow-xs fw-semibold" onclick="exportSegmentsCsv()" title="Export filtered segments to CSV">
                <i class="fas fa-file-csv me-1 text-success"></i> <span>Export CSV</span>
            </button>
            <a href="{{ url('admin/email-marketing/subscribers') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-users me-1"></i> <span>All Subscribers</span>
            </a>
            <a href="{{ url('admin/email-marketing/segments/create') }}" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold btn-create-main">
                <i class="fas fa-plus-circle me-1"></i> <span>Create Segment</span>
            </a>
        </div>
    </div>

    {{-- 2. Feedback Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 3. Overview Stats Row (Collapsible Section) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-chart-pie text-primary me-1"></i> Audience Segmentation &amp; Engine Overview
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseSegmentsKpis" aria-expanded="true" aria-controls="collapseSegmentsKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseSegmentsKpis">
            <div class="p-3">
                <div class="row g-2">
                    {{-- Total Segments --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Total Segments</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num" id="stat-total-segments">
                                    {{ $totalSegmentsCount }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Configured audience groups</small>
                            </div>
                        </div>
                    </div>

                    {{-- Dynamic Rules --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Dynamic Rules</span>
                                <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                                    <i class="fas fa-arrows-spin"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num" id="stat-dynamic-rules">
                                    {{ $dynamicCount }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Auto-evaluating live criteria</small>
                            </div>
                        </div>
                    </div>

                    {{-- Static Snapshots --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Static Snapshots</span>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                                    <i class="fas fa-camera"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-warning-emphasis bn-digit font-monospace stat-val-num" id="stat-static-snapshots">
                                    {{ $snapshotCount }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Fixed audience records</small>
                            </div>
                        </div>
                    </div>

                    {{-- Cumulative Reach --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-cyan h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Target Reach</span>
                                <div class="metric-icon-square bg-info-subtle text-info shadow-xs">
                                    <i class="fas fa-users-viewfinder"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-info bn-digit font-monospace stat-val-num" id="stat-target-reach">
                                    {{ number_format($totalMatchingAudience) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Matching subscriber reach</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Advanced Multi-Dimensional Filter Toolbar (Collapsible) --}}
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0 !important;">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-filter text-primary me-1"></i> Multi-Dimensional Filter &amp; Search Toolbar
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseSegmentsFilter" aria-expanded="true" aria-controls="collapseSegmentsFilter" title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseSegmentsFilter">
            <div class="p-3">
                <form id="segment-filter-form" onsubmit="return false;">
                    {{-- Row 1: Search, Rule Type, Sorting, Per Page, Reset --}}
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-5">
                            <div class="input-group">
                                <span class="input-group-text segment-addon-text border-end-0"><i class="fas fa-search text-muted" style="font-size: 11px;"></i></span>
                                <input type="text" name="search" id="filter-search-input" class="form-control border-start-0 ps-0 segment-filter-control shadow-none font-monospace" placeholder="Search segment name or description...">
                            </div>
                        </div>

                        <div class="col-6 col-md-2">
                            <select name="type" id="filter-type" class="form-select segment-filter-control filter-trigger font-monospace shadow-none">
                                <option value="">Type: All Rules</option>
                                <option value="dynamic">Dynamic Criteria</option>
                                <option value="snapshot">Static Snapshot</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-2">
                            <select name="sort_by" id="filter-sort-by" class="form-select segment-filter-control filter-trigger font-monospace shadow-none">
                                <option value="latest">Newest Created</option>
                                <option value="oldest">Oldest Created</option>
                                <option value="name_asc">Name: A to Z</option>
                                <option value="name_desc">Name: Z to A</option>
                                <option value="audience_high">Audience: High to Low</option>
                                <option value="audience_low">Audience: Low to High</option>
                                <option value="most_campaigns">Most Campaigns Used</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-2">
                            <div class="input-group">
                                <span class="input-group-text segment-addon-text font-monospace" style="font-size: 10px;">Per Page</span>
                                <select name="per_page" id="filter-per-page" class="form-select segment-filter-control filter-trigger fw-bold font-monospace shadow-none">
                                    <option value="10">10</option>
                                    <option value="15" selected>15</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="all">Show All</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-6 col-md-1 d-flex justify-content-end">
                            <button type="button" class="btn btn-light border btn-compact w-100 text-secondary shadow-xs fw-semibold" onclick="resetAllFilters()" title="Reset Filters">
                                <i class="fas fa-rotate-left"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Row 2: Date Presets & Conditional Panels --}}
                    <div class="row g-2 align-items-center mt-2 pt-2 border-top">
                        <div class="col-12 col-md-4">
                            <select name="date_filter" id="filter-date-select" class="form-select segment-filter-control font-monospace shadow-none">
                                <option value="">Created Date: All Time</option>
                                <option value="today">Created Today</option>
                                <option value="yesterday">Created Yesterday</option>
                                <option value="this_week">This Week</option>
                                <option value="last_week">Last Week</option>
                                <option value="this_month">This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="this_year">This Year</option>
                                <option value="specific_date">Specific Single Date...</option>
                                <option value="specific_month">Specific Month (YYYY-MM)...</option>
                                <option value="specific_year">Specific Year...</option>
                                <option value="custom_range">Custom Date Range (From - To)...</option>
                            </select>
                        </div>
                    </div>

                    {{-- Option A: Specific Single Date Panel --}}
                    <div id="singleDateWrapper" class="row g-2 align-items-center mt-2 pt-2 border-top d-none">
                        <div class="col-12 col-sm-auto small fw-bold text-muted font-monospace" style="font-size: 11px;">
                            <i class="fas fa-calendar-day text-primary me-1"></i> Specific Creation Date:
                        </div>
                        <div class="col-12 col-sm-auto">
                            <input type="date" name="specific_date" id="filter-specific-date" class="form-control segment-filter-control font-monospace">
                        </div>
                        <div class="col-12 col-sm-auto d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" onclick="fetchFilteredSegments(1)">Apply Date</button>
                            <button type="button" class="btn btn-light border btn-compact shadow-xs" onclick="resetDatePanels()">Clear</button>
                        </div>
                    </div>

                    {{-- Option B: Specific Month Panel (YYYY-MM) --}}
                    <div id="specificMonthWrapper" class="row g-2 align-items-center mt-2 pt-2 border-top d-none">
                        <div class="col-12 col-sm-auto small fw-bold text-muted font-monospace" style="font-size: 11px;">
                            <i class="fas fa-calendar-week text-primary me-1"></i> Specific Creation Month:
                        </div>
                        <div class="col-12 col-sm-auto">
                            <input type="month" name="specific_month" id="filter-specific-month" class="form-control segment-filter-control font-monospace">
                        </div>
                        <div class="col-12 col-sm-auto d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" onclick="fetchFilteredSegments(1)">Apply Month</button>
                            <button type="button" class="btn btn-light border btn-compact shadow-xs" onclick="resetDatePanels()">Clear</button>
                        </div>
                    </div>

                    {{-- Option C: Specific Year Panel --}}
                    <div id="specificYearWrapper" class="row g-2 align-items-center mt-2 pt-2 border-top d-none">
                        <div class="col-12 col-sm-auto small fw-bold text-muted font-monospace" style="font-size: 11px;">
                            <i class="fas fa-calendar-alt text-primary me-1"></i> Specific Creation Year:
                        </div>
                        <div class="col-12 col-sm-auto">
                            <input type="number" min="2020" max="2035" name="specific_year" id="filter-specific-year" class="form-control segment-filter-control font-monospace bn-digit" placeholder="{{ date('Y') }}">
                        </div>
                        <div class="col-12 col-sm-auto d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" onclick="fetchFilteredSegments(1)">Apply Year</button>
                            <button type="button" class="btn btn-light border btn-compact shadow-xs" onclick="resetDatePanels()">Clear</button>
                        </div>
                    </div>

                    {{-- Option D: Custom Date Range Panel (From - To) --}}
                    <div id="customRangeWrapper" class="row g-2 align-items-center mt-2 pt-2 border-top d-none">
                        <div class="col-12 col-sm-auto small fw-bold text-muted font-monospace" style="font-size: 11px;">
                            <i class="fas fa-calendar-check text-primary me-1"></i> Custom Creation Range:
                        </div>
                        <div class="col-6 col-sm-auto">
                            <div class="input-group">
                                <span class="input-group-text segment-addon-text text-muted font-monospace" style="font-size: 10px;">From</span>
                                <input type="date" name="start_date" id="filter-start-date" class="form-control segment-filter-control font-monospace">
                            </div>
                        </div>
                        <div class="col-6 col-sm-auto">
                            <div class="input-group">
                                <span class="input-group-text segment-addon-text text-muted font-monospace" style="font-size: 10px;">To</span>
                                <input type="date" name="end_date" id="filter-end-date" class="form-control segment-filter-control font-monospace">
                            </div>
                        </div>
                        <div class="col-12 col-sm-auto d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" onclick="fetchFilteredSegments(1)">Apply Range</button>
                            <button type="button" class="btn btn-light border btn-compact shadow-xs" onclick="resetDatePanels()">Clear</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 5. Bulk Action Strip --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 px-1">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary btn-compact shadow-xs fw-bold bg-white" onclick="openBulkRecalculateModal()">
                <i class="fas fa-rotate me-1 text-primary"></i> <span>Bulk Recalculate Reach</span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger btn-compact shadow-xs fw-semibold bg-white" onclick="openBulkDeleteModal()">
                <i class="fas fa-trash-can me-1 text-danger"></i> <span>Bulk Delete</span>
            </button>
            <span class="badge bg-primary text-white font-monospace d-none px-2 py-1 shadow-xs" id="selected-segments-badge" style="font-size: 10.5px;">
                0 selected
            </span>
        </div>
        <div class="text-muted font-monospace bn-digit small" style="font-size: 11px;">
            <span id="showing-entries-text">Showing {{ $segments->firstItem() ?? 0 }} to {{ $segments->lastItem() ?? 0 }} of {{$segments->total() }} segments</span>
        </div>
    </div>

    {{-- 6. Segments Table Container (Desktop 100% Fit Screen with Zero Horizontal Scroll) --}}
    <div class="card border-0 shadow-sm overflow-hidden mb-3">
        <div class="table-responsive pc-fit-container" style="min-height: 250px;">
            <table class="table custom-segments-table align-middle mb-0" id="segments-main-table">
                <thead>
                    <tr>
                        <th class="text-center col-sg-cb">
                            <input type="checkbox" class="form-check-input mt-0 shadow-none" id="check-all-segments" style="cursor: pointer; width: 14px; height: 14px; border-radius: 4px;">
                        </th>
                        <th class="ps-2 col-sg-id text-center">#ID</th>
                        <th class="col-sg-name">Segment Name &amp; Strategy Description</th>
                        <th class="col-sg-type">Rule Type</th>
                        <th class="text-center col-sg-match">Matching Subscribers</th>
                        <th class="text-center col-sg-camps">Used in Campaigns</th>
                        <th class="col-sg-date">Last Calculated</th>
                        <th class="text-end pe-3 col-sg-action">Action</th>
                    </tr>
                </thead>
                <tbody id="segments-table-tbody">
                    @include('admin.email_marketing.segments.partials.table_rows', ['segments' => $segments])
                </tbody>
            </table>
        </div>
    </div>

    {{-- 7. Responsive Pagination Footer --}}
    <div class="d-flex justify-content-center justify-content-md-end mb-4" id="segments-pagination-footer" style="{{ $segments->hasPages() ? '' : 'display: none;' }}">
        <div id="segments-pagination-links">
            @if($segments->hasPages())
                {{ $segments->links('pagination::bootstrap-5') }}
            @endif
        </div>
    </div>

</div>

{{-- MODAL: Custom Reusable Delete & Bulk Confirm Modal --}}
<div class="modal fade segment-modal-scope" id="customConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 44px; height: 44px; border-radius: 50% !important;">
                    <i class="fas fa-trash-can fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="confirm-modal-title" style="font-size: 14px;">Delete Segment?</h6>
                <p class="text-muted small mb-3" id="confirm-modal-msg" style="font-size: 11px; line-height: 1.45;">
                    Are you sure you want to proceed? Existing campaigns linked to it will not be harmed.
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-compact shadow-xs fw-bold" id="btn-confirm-action-execute">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Floating Toast Feedback Notification Holder --}}
<div id="custom-toast-holder"></div>

{{-- JavaScript Engine --}}
<script>
    let searchTimeout = null;
    let pendingConfirmCallback = null;

    // 1. Live Reload-Free Fetch
    function fetchFilteredSegments(page = 1) {
        const form = document.getElementById('segment-filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.append('page', page);

        const tbody = document.getElementById('segments-table-tbody');
        if (tbody) tbody.style.opacity = '0.35';

        fetch("{{ url('admin/email-marketing/segments') }}?" + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (tbody) {
                tbody.innerHTML = data.table_html;
                tbody.style.opacity = '1';
            }

            // Pagination Handling
            const paginationFooter = document.getElementById('segments-pagination-footer');
            const paginationLinks  = document.getElementById('segments-pagination-links');
            const perPageVal       = document.getElementById('filter-per-page')?.value || '15';

            if (paginationLinks && paginationFooter) {
                if (perPageVal === 'all' || !data.pagination_html || data.pagination_html.trim() === '') {
                    paginationLinks.innerHTML = '';
                    paginationFooter.style.display = 'none';
                } else {
                    paginationLinks.innerHTML = data.pagination_html;
                    paginationFooter.style.display = 'flex';
                }
            }

            // Stats Update
            if (data.stats) {
                const totalEl    = document.getElementById('stat-total-segments');
                const dynamicEl  = document.getElementById('stat-dynamic-rules');
                const snapshotEl = document.getElementById('stat-static-snapshots');
                const reachEl    = document.getElementById('stat-target-reach');
                const badgeEl    = document.getElementById('segments-total-badge');

                if (totalEl)    totalEl.innerText = Number(data.stats.total).toLocaleString();
                if (dynamicEl)  dynamicEl.innerText = Number(data.stats.dynamic).toLocaleString();
                if (snapshotEl) snapshotEl.innerText = Number(data.stats.snapshot).toLocaleString();
                if (reachEl)    reachEl.innerText = Number(data.stats.total_reach).toLocaleString();
                if (badgeEl)    badgeEl.innerText = Number(data.stats.total).toLocaleString() + ' Segments';
            }

            if (data.entries_text) {
                const entriesEl = document.getElementById('showing-entries-text');
                if (entriesEl) entriesEl.innerText = data.entries_text;
            }

            const checkAll = document.getElementById('check-all-segments');
            if (checkAll) checkAll.checked = false;
            updateSelectionBadge();
        })
        .catch(err => {
            console.error('Fetch error:', err);
            if (tbody) tbody.style.opacity = '1';
        });
    }

    // 2. Search Debounce & Change Triggers
    document.getElementById('filter-search-input')?.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => fetchFilteredSegments(1), 350);
    });

    document.querySelectorAll('.filter-trigger').forEach(el => {
        el.addEventListener('change', () => fetchFilteredSegments(1));
    });

    // 3. Date Presets Switcher
    document.getElementById('filter-date-select')?.addEventListener('change', function() {
        const val = this.value;
        const singleBox  = document.getElementById('singleDateWrapper');
        const monthBox   = document.getElementById('specificMonthWrapper');
        const yearBox    = document.getElementById('specificYearWrapper');
        const rangeBox   = document.getElementById('customRangeWrapper');

        singleBox?.classList.add('d-none');
        monthBox?.classList.add('d-none');
        yearBox?.classList.add('d-none');
        rangeBox?.classList.add('d-none');

        if (val === 'specific_date') {
            singleBox?.classList.remove('d-none');
        } else if (val === 'specific_month') {
            monthBox?.classList.remove('d-none');
        } else if (val === 'specific_year') {
            yearBox?.classList.remove('d-none');
        } else if (val === 'custom_range') {
            rangeBox?.classList.remove('d-none');
        } else {
            fetchFilteredSegments(1);
        }
    });

    function resetDatePanels() {
        const elDate  = document.getElementById('filter-specific-date');
        const elMonth = document.getElementById('filter-specific-month');
        const elYear  = document.getElementById('filter-specific-year');
        const elStart = document.getElementById('filter-start-date');
        const elEnd   = document.getElementById('filter-end-date');
        const elSel   = document.getElementById('filter-date-select');

        if (elDate)  elDate.value = '';
        if (elMonth) elMonth.value = '';
        if (elYear)  elYear.value = '';
        if (elStart) elStart.value = '';
        if (elEnd)   elEnd.value = '';
        if (elSel)   elSel.value = '';

        document.getElementById('singleDateWrapper')?.classList.add('d-none');
        document.getElementById('specificMonthWrapper')?.classList.add('d-none');
        document.getElementById('specificYearWrapper')?.classList.add('d-none');
        document.getElementById('customRangeWrapper')?.classList.add('d-none');

        fetchFilteredSegments(1);
    }

    function resetAllFilters() {
        document.getElementById('segment-filter-form')?.reset();
        resetDatePanels();
    }

    // 4. Pagination Link Click Interceptor
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#segments-pagination-links .page-link');
        if (pageLink) {
            e.preventDefault();
            const href = pageLink.getAttribute('href');
            if (href && href !== '#' && !href.startsWith('javascript:')) {
                const url = new URL(href);
                const page = url.searchParams.get('page') || 1;
                fetchFilteredSegments(page);
                document.querySelector('.custom-segments-table')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    });

    // 5. Checkbox Selection & Badge Sync
    document.getElementById('check-all-segments')?.addEventListener('change', function() {
        document.querySelectorAll('.segment-select-cb').forEach(cb => cb.checked = this.checked);
        updateSelectionBadge();
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('segment-select-cb')) {
            updateSelectionBadge();
        }
    });

    function getSelectedSegmentIds() {
        return Array.from(document.querySelectorAll('.segment-select-cb:checked')).map(cb => cb.value);
    }

    function updateSelectionBadge() {
        const selected = getSelectedSegmentIds();
        const badge = document.getElementById('selected-segments-badge');
        if (badge) {
            if (selected.length > 0) {
                badge.innerText = `${selected.length} selected`;
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }
        }
    }

    // 6. CSV Exporter (Exports with Active Filters)
    function exportSegmentsCsv() {
        const form = document.getElementById('segment-filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        const url = "{{ url('admin/email-marketing/segments/export-csv') }}?" + params.toString();
        window.open(url, '_blank');
    }

    // 7. Single Segment Recalculation AJAX
    function recalculateSegmentAjax(id) {
        const icon = document.getElementById(`recalc-icon-${id}`);
        if (icon) icon.classList.add('fa-spin');

        fetch(`{{ url('admin/email-marketing/segments/recalculate') }}/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (icon) icon.classList.remove('fa-spin');
            if (data.success) {
                const countBadge = document.getElementById(`segment-count-${id}`);
                const dateBox = document.getElementById(`segment-last-calc-${id}`);
                if (countBadge) countBadge.innerText = Number(data.count).toLocaleString();
                if (dateBox) dateBox.innerHTML = `<span class="text-dark d-block font-monospace" style="font-size: 11px;">Just now</span><small class="text-muted font-monospace" style="font-size: 9.5px;">${data.last_calculated}</small>`;
                showToast('Recalculated', data.message, 'success');
            } else {
                showToast('Error', 'Failed to recalculate audience reach.', 'danger');
            }
        })
        .catch(() => {
            if (icon) icon.classList.remove('fa-spin');
            showToast('Error', 'Server error while recalculating.', 'danger');
        });
    }

    // 8. Bulk Recalculate
    function openBulkRecalculateModal() {
        const selected = getSelectedSegmentIds();
        if (selected.length === 0) {
            showToast('Warning', 'Please select at least one segment from the table checkbox.', 'warning');
            return;
        }

        fetch("{{ url('admin/email-marketing/segments/bulk-recalculate') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ segment_ids: selected })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Refreshed', data.message, 'success');
                fetchFilteredSegments(1);
            } else {
                showToast('Error', 'Failed to recalculate reach.', 'danger');
            }
        })
        .catch(() => {
            showToast('Error', 'Network or server error.', 'danger');
        });
    }

    // 9. Single Segment Deletion
    function deleteSegmentAjax(id, name) {
        const titleEl = document.getElementById('confirm-modal-title');
        const msgEl   = document.getElementById('confirm-modal-msg');
        if (titleEl) titleEl.innerText = 'Delete Segment?';
        if (msgEl) msgEl.innerHTML = `Are you sure you want to delete <strong>${name}</strong>? Existing campaigns linked to it will not be harmed.`;

        pendingConfirmCallback = function() {
            fetch(`{{ url('admin/email-marketing/segments/delete') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                bootstrap.Modal.getInstance(document.getElementById('customConfirmModal'))?.hide();
                showToast('Deleted', data.message, 'danger');
                fetchFilteredSegments(1);
            })
            .catch(() => {
                bootstrap.Modal.getInstance(document.getElementById('customConfirmModal'))?.hide();
                fetchFilteredSegments(1);
            });
        };

        bootstrap.Modal.getOrCreateInstance(document.getElementById('customConfirmModal')).show();
    }

    // 10. Bulk Segment Deletion
    function openBulkDeleteModal() {
        const selected = getSelectedSegmentIds();
        if (selected.length === 0) {
            showToast('Warning', 'Please select at least one segment to delete.', 'warning');
            return;
        }

        const titleEl = document.getElementById('confirm-modal-title');
        const msgEl   = document.getElementById('confirm-modal-msg');
        if (titleEl) titleEl.innerText = 'Bulk Delete Segments?';
        if (msgEl) msgEl.innerHTML = `Are you sure you want to delete <strong>${selected.length}</strong> selected segment(s)?`;

        pendingConfirmCallback = function() {
            fetch("{{ url('admin/email-marketing/segments/bulk-delete') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ segment_ids: selected })
            })
            .then(res => res.json())
            .then(data => {
                bootstrap.Modal.getInstance(document.getElementById('customConfirmModal'))?.hide();
                showToast('Deleted', data.message, 'danger');
                fetchFilteredSegments(1);
            })
            .catch(() => {
                bootstrap.Modal.getInstance(document.getElementById('customConfirmModal'))?.hide();
                fetchFilteredSegments(1);
            });
        };

        bootstrap.Modal.getOrCreateInstance(document.getElementById('customConfirmModal')).show();
    }

    document.getElementById('btn-confirm-action-execute')?.addEventListener('click', function() {
        if (typeof pendingConfirmCallback === 'function') {
            pendingConfirmCallback();
        }
    });

    // 11. Floating Toast Notifications
    function showToast(title, message, type = 'success') {
        const holder = document.getElementById('custom-toast-holder');
        if (!holder) return;

        const toastId = 'toast-' + Date.now();
        const typeConfigs = {
            success: { icon: 'fa-check-circle', color: 'bg-success text-white' },
            danger:  { icon: 'fa-trash-can',    color: 'bg-danger text-white' },
            warning: { icon: 'fa-triangle-exclamation', color: 'bg-warning text-dark' },
            info:    { icon: 'fa-circle-info',  color: 'bg-primary text-white' }
        };
        const config = typeConfigs[type] || typeConfigs.success;

        const html = `
            <div id="${toastId}" class="admin-segment-toast ${config.color} p-3 d-flex align-items-center justify-content-between shadow-lg" style="border-radius: 5px !important;">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas ${config.icon} fs-5"></i>
                    <div>
                        <strong class="d-block" style="font-size: 13px;">${title}</strong>
                        <small style="opacity: 0.95; font-size: 11px;">${message}</small>
                    </div>
                </div>
                <button type="button" class="btn-close ${type === 'warning' ? '' : 'btn-close-white'} btn-sm ms-2 shadow-none" onclick="document.getElementById('${toastId}')?.remove()"></button>
            </div>
        `;

        holder.insertAdjacentHTML('beforeend', html);
        setTimeout(() => {
            const el = document.getElementById(toastId);
            if (el) el.remove();
        }, 3500);
    }
</script>
@endsection