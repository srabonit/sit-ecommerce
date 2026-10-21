@extends('layouts.admin')

@section('title', 'Cart & Abandoned Recovery - Email Marketing')

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-cart-wishlist-root .btn,
    #email-marketing-cart-wishlist-root .btn-sm,
    #email-marketing-cart-wishlist-root button,
    #email-marketing-cart-wishlist-root .form-control,
    #email-marketing-cart-wishlist-root .form-select,
    #email-marketing-cart-wishlist-root .badge,
    #email-marketing-cart-wishlist-root .input-group-text,
    #email-marketing-cart-wishlist-root .page-link,
    #email-marketing-cart-wishlist-root .card,
    #email-marketing-cart-wishlist-root .metric-card,
    #email-marketing-cart-wishlist-root .tag-chip,
    #email-marketing-cart-wishlist-root .collapse-toggle-btn,
    .recovery-modal-scope .modal-content,
    .recovery-modal-scope .btn,
    .admin-cart-toast {
        border-radius: 5px !important;
    }

    #email-marketing-cart-wishlist-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #email-marketing-cart-wishlist-root .input-group > .form-control,
    #email-marketing-cart-wishlist-root .input-group > .form-select {
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
    #email-marketing-cart-wishlist-root .card {
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

    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-left: 3.5px solid #f59e0b !important; }
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-left: 3.5px solid #10b981 !important; }
    .metric-card-blue   { background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%); border-left: 3.5px solid #2563eb !important; }
    .metric-card-cyan   { background: linear-gradient(180deg, #ffffff 0%, #ecfeff 100%); border-left: 3.5px solid #06b6d4 !important; }
    .metric-card-emerald{ background: linear-gradient(180deg, #ffffff 0%, #ecfdf5 100%); border-left: 3.5px solid #059669 !important; }

    /* Form Controls */
    .cart-filter-control {
        font-size: 12px !important;
        height: 32px !important;
        padding: 4px 8px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        box-shadow: none !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .cart-filter-control:focus {
        border-color: #f59e0b !important;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15) !important;
        outline: none;
    }

    .cart-addon-text {
        height: 32px !important;
        background-color: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: #64748b !important;
        font-size: 11px !important;
    }

    /* Quick Tag Insertion Chips */
    .tag-chip {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 2px 7px;
        font-size: 10px;
        font-weight: 600;
        color: #475569;
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        user-select: none;
        transition: all 0.15s ease;
    }
    .tag-chip:hover {
        background-color: #f59e0b;
        border-color: #f59e0b;
        color: #ffffff;
    }

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .cart-table-container {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 5px !important;
        overflow: hidden;
    }
    .custom-carts-table {
        width: 100%;
        margin-bottom: 0;
    }
    .custom-carts-table thead th {
        font-size: 10px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 8px;
        white-space: nowrap;
    }
    .custom-carts-table tbody td {
        padding: 8px 8px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        font-size: 11px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .custom-carts-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .custom-carts-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .custom-carts-table th,
        .custom-carts-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 9 Columns Proportional Distribution Summing to Exactly 100% */
        .col-cb-select { width: 3% !important; }
        .col-cb-id     { width: 5% !important; }
        .col-cb-user   { width: 22% !important; }
        .col-cb-items  { width: 15% !important; }
        .col-cb-val    { width: 12% !important; }
        .col-cb-status { width: 12% !important; }
        .col-cb-sent   { width: 10% !important; }
        .col-cb-date   { width: 10% !important; }
        .col-cb-action { width: 11% !important; }
    }

    /* Bulletproof Responsive Pagination */
    #carts-pagination-links nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }
    #carts-pagination-links nav > div.d-sm-none,
    #carts-pagination-links nav > .d-sm-none,
    #carts-pagination-links nav p,
    #carts-pagination-links nav .small.text-muted,
    #carts-pagination-links nav div:has(> p) {
        display: none !important;
    }
    #carts-pagination-links .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 3px !important;
        padding: 0 !important;
        list-style: none !important;
    }
    #carts-pagination-links .page-item {
        display: inline-flex !important;
    }
    #carts-pagination-links .page-item .page-link,
    #carts-pagination-links .page-item span {
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
    #carts-pagination-links .page-item .page-link:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }
    #carts-pagination-links .page-item.active .page-link,
    #carts-pagination-links .page-item.active span {
        background-color: #f59e0b !important;
        border-color: #f59e0b !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 3px rgba(245, 158, 11, 0.25) !important;
    }
    #carts-pagination-links .page-item.disabled .page-link,
    #carts-pagination-links .page-item.disabled span {
        opacity: 0.45 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }

    /* Floating Toast Box */
    .admin-cart-toast {
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
        .cart-header-wrap h5 {
            font-size: 16px !important;
        }
        .header-actions-wrap {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .header-actions-wrap .btn {
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
        .custom-carts-table {
            min-width: 820px !important;
            table-layout: auto !important;
        }
        #email-marketing-cart-wishlist-root .card {
            margin-bottom: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-cart-wishlist-root">
    
    {{-- 1. Header & Navigation (Original Clean Light Theme) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 cart-header-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-cart-shopping text-warning"></i>
                    <span>Abandoned Cart Recovery Engine</span>
                </h5>
                <span class="badge bg-warning-subtle text-dark border border-warning-subtle font-monospace px-2 py-1" id="carts-total-badge" style="font-size: 10px;">
                    {{ $stats['total'] ?? $abandonedCarts->total() }} Sessions
                </span>
            </div>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Track checkout abandonments, trigger precision recovery emails, and monitor converted revenue.
            </small>
        </div>

        <div class="d-flex gap-2 header-actions-wrap">
            <button type="button" class="btn btn-sm btn-outline-success btn-compact bg-white shadow-xs fw-semibold" onclick="exportCartsCsv()" title="Export current filtered cart list to CSV">
                <i class="fas fa-file-csv me-1 text-success"></i> <span>Export CSV</span>
            </button>
            <a href="{{ route('admin.email_marketing.automations.index') }}" class="btn btn-sm btn-outline-primary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-robot me-1"></i> <span>Automations</span>
            </a>
            <a href="{{ route('admin.email_marketing.dashboard') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-chart-pie me-1"></i> <span>Dashboard</span>
            </a>
        </div>
    </div>

    {{-- 2. Live Analytics Stat Badges (Collapsible Section) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-chart-pie text-warning me-1"></i> Abandoned Cart Pipeline &amp; Recovery Performance
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseCartKpis" aria-expanded="true" aria-controls="collapseCartKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseCartKpis">
            <div class="p-3">
                <div class="row g-2">
                    {{-- Total Abandoned --}}
                    <div class="col-6 col-xl">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Total Abandoned</span>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                                    <i class="fas fa-cart-arrow-down"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num" id="stat-total-abandoned">
                                    {{ number_format($stats['total'] ?? 0) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Recorded sessions</small>
                            </div>
                        </div>
                    </div>

                    {{-- Recovered Orders --}}
                    <div class="col-6 col-xl">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Recovered Orders</span>
                                <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                                    <i class="fas fa-circle-check"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num" id="stat-recovered-orders">
                                    {{ number_format($stats['recovered'] ?? 0) }}
                                </h4>
                                <small class="text-success d-block text-truncate font-monospace stat-sub-text">Successfully converted</small>
                            </div>
                        </div>
                    </div>

                    {{-- Pending Recovery --}}
                    <div class="col-6 col-xl">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Pending Recovery</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-primary bn-digit font-monospace stat-val-num" id="stat-pending-recovery">
                                    {{ number_format($stats['pending'] ?? 0) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Open opportunities</small>
                            </div>
                        </div>
                    </div>

                    {{-- Monitored Cart Value --}}
                    <div class="col-6 col-xl">
                        <div class="card metric-card metric-card-cyan h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Monitored Value</span>
                                <div class="metric-icon-square bg-info-subtle text-info shadow-xs">
                                    <i class="fas fa-coins"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num" id="stat-monitored-value">
                                    ৳ {{ number_format($stats['monitored_value'] ?? 0, 2) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Unrecovered value</small>
                            </div>
                        </div>
                    </div>

                    {{-- Recovered Revenue --}}
                    <div class="col-12 col-xl">
                        <div class="card metric-card metric-card-emerald h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Recovered Sales</span>
                                <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                                    <i class="fas fa-bag-shopping"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num" id="stat-recovered-revenue">
                                    ৳ {{ number_format($stats['recovered_revenue'] ?? 0, 2) }}
                                </h4>
                                <small class="text-success d-block text-truncate font-monospace stat-sub-text">Direct converted sales</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Advanced Multi-Dimensional Filter Toolbar (Collapsible Section) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-filter text-primary me-1"></i> Multi-Dimensional Abandoned Cart Filters
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseCartFilters" aria-expanded="true" aria-controls="collapseCartFilters" title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseCartFilters">
            <div class="p-3">
                <form id="cart-filter-form" onsubmit="return false;">
                    {{-- Row 1: Search, Recovery Status, Reminder Status, Sorting, Per Page --}}
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-4">
                            <div class="input-group">
                                <span class="input-group-text cart-addon-text border-end-0"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                                <input type="text" name="search" id="filter-search-input" class="form-control border-start-0 ps-0 cart-filter-control shadow-none font-monospace" placeholder="Search customer, email, phone...">
                            </div>
                        </div>

                        <div class="col-6 col-md-2">
                            <select name="status" id="filter-status" class="form-select cart-filter-control filter-trigger font-monospace shadow-none">
                                <option value="">Status: All Carts</option>
                                <option value="pending">Pending Recovery</option>
                                <option value="recovered">Recovered (Converted)</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-2">
                            <select name="reminder_status" id="filter-reminder-status" class="form-select cart-filter-control filter-trigger font-monospace shadow-none">
                                <option value="">Reminders: All</option>
                                <option value="never_sent">Never Sent (0)</option>
                                <option value="sent">Already Sent (1+)</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-2">
                            <select name="sort_by" id="filter-sort-by" class="form-select cart-filter-control filter-trigger font-monospace shadow-none">
                                <option value="latest">Newest Abandoned</option>
                                <option value="oldest">Oldest Abandoned</option>
                                <option value="value_high">Value: High to Low</option>
                                <option value="value_low">Value: Low to High</option>
                                <option value="most_reminders">Most Reminders Sent</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-2">
                            <div class="input-group">
                                <span class="input-group-text cart-addon-text font-monospace" style="font-size: 10px;">Per Page</span>
                                <select name="per_page" id="filter-per-page" class="form-select cart-filter-control filter-trigger fw-bold font-monospace shadow-none">
                                    <option value="10">10</option>
                                    <option value="25" selected>25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="all">Show All</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Row 2: Date Presets & Reset --}}
                    <div class="row g-2 align-items-center mt-2 pt-2 border-top">
                        <div class="col-12 col-md-4">
                            <select name="date_filter" id="filter-date-select" class="form-select cart-filter-control font-monospace shadow-none">
                                <option value="">Abandoned Date: All Time</option>
                                <option value="today">Abandoned Today</option>
                                <option value="yesterday">Abandoned Yesterday</option>
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

                        <div class="col-12 col-md-8 d-flex justify-content-md-end">
                            <button type="button" class="btn btn-light border btn-compact text-secondary shadow-xs fw-semibold" onclick="resetAllFilters()">
                                <i class="fas fa-rotate-left me-1"></i>
                                <span>Reset All Filters</span>
                            </button>
                        </div>
                    </div>

                    {{-- Option A: Specific Single Date Panel --}}
                    <div id="singleDateWrapper" class="row g-2 align-items-center mt-2 pt-2 border-top d-none">
                        <div class="col-12 col-sm-auto small fw-bold text-muted font-monospace" style="font-size: 11px;">
                            <i class="fas fa-calendar-day text-warning me-1"></i> Specific Abandoned Date:
                        </div>
                        <div class="col-12 col-sm-auto">
                            <input type="date" name="specific_date" id="filter-specific-date" class="form-control cart-filter-control font-monospace">
                        </div>
                        <div class="col-12 col-sm-auto d-flex gap-2">
                            <button type="button" class="btn btn-warning text-dark btn-compact shadow-xs fw-bold" onclick="fetchFilteredCarts(1)">Apply Date</button>
                            <button type="button" class="btn btn-light border btn-compact shadow-xs" onclick="resetDatePanels()">Clear</button>
                        </div>
                    </div>

                    {{-- Option B: Specific Month Panel (YYYY-MM) --}}
                    <div id="specificMonthWrapper" class="row g-2 align-items-center mt-2 pt-2 border-top d-none">
                        <div class="col-12 col-sm-auto small fw-bold text-muted font-monospace" style="font-size: 11px;">
                            <i class="fas fa-calendar-week text-warning me-1"></i> Specific Abandoned Month:
                        </div>
                        <div class="col-12 col-sm-auto">
                            <input type="month" name="specific_month" id="filter-specific-month" class="form-control cart-filter-control font-monospace">
                        </div>
                        <div class="col-12 col-sm-auto d-flex gap-2">
                            <button type="button" class="btn btn-warning text-dark btn-compact shadow-xs fw-bold" onclick="fetchFilteredCarts(1)">Apply Month</button>
                            <button type="button" class="btn btn-light border btn-compact shadow-xs" onclick="resetDatePanels()">Clear</button>
                        </div>
                    </div>

                    {{-- Option C: Specific Year Panel --}}
                    <div id="specificYearWrapper" class="row g-2 align-items-center mt-2 pt-2 border-top d-none">
                        <div class="col-12 col-sm-auto small fw-bold text-muted font-monospace" style="font-size: 11px;">
                            <i class="fas fa-calendar-alt text-warning me-1"></i> Specific Abandoned Year:
                        </div>
                        <div class="col-12 col-sm-auto">
                            <input type="number" min="2020" max="2035" name="specific_year" id="filter-specific-year" class="form-control cart-filter-control font-monospace bn-digit" placeholder="{{ date('Y') }}">
                        </div>
                        <div class="col-12 col-sm-auto d-flex gap-2">
                            <button type="button" class="btn btn-warning text-dark btn-compact shadow-xs fw-bold" onclick="fetchFilteredCarts(1)">Apply Year</button>
                            <button type="button" class="btn btn-light border btn-compact shadow-xs" onclick="resetDatePanels()">Clear</button>
                        </div>
                    </div>

                    {{-- Option D: Custom Date Range Panel (From - To) --}}
                    <div id="customRangeWrapper" class="row g-2 align-items-center mt-2 pt-2 border-top d-none">
                        <div class="col-12 col-sm-auto small fw-bold text-muted font-monospace" style="font-size: 11px;">
                            <i class="fas fa-calendar-check text-warning me-1"></i> Custom Abandoned Range:
                        </div>
                        <div class="col-6 col-sm-auto">
                            <div class="input-group">
                                <span class="input-group-text cart-addon-text text-muted font-monospace" style="font-size: 10px;">From</span>
                                <input type="date" name="start_date" id="filter-start-date" class="form-control cart-filter-control font-monospace">
                            </div>
                        </div>
                        <div class="col-6 col-sm-auto">
                            <div class="input-group">
                                <span class="input-group-text cart-addon-text text-muted font-monospace" style="font-size: 10px;">To</span>
                                <input type="date" name="end_date" id="filter-end-date" class="form-control cart-filter-control font-monospace">
                            </div>
                        </div>
                        <div class="col-12 col-sm-auto d-flex gap-2">
                            <button type="button" class="btn btn-warning text-dark btn-compact shadow-xs fw-bold" onclick="fetchFilteredCarts(1)">Apply Range</button>
                            <button type="button" class="btn btn-light border btn-compact shadow-xs" onclick="resetDatePanels()">Clear</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 4. Bulk Action Strip --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 px-1">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-warning text-dark btn-compact shadow-xs fw-bold bg-white" onclick="openBulkRecoveryModal()">
                <i class="fas fa-paper-plane me-1 text-warning"></i> <span>Bulk Send Recovery</span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger btn-compact shadow-xs fw-semibold bg-white" onclick="openBulkDeleteModal()">
                <i class="fas fa-trash-can me-1 text-danger"></i> <span>Bulk Delete</span>
            </button>
            <span class="badge bg-warning text-dark font-monospace d-none px-2 py-1 shadow-xs" id="selected-carts-badge" style="font-size: 10.5px;">
                0 selected
            </span>
        </div>
        <div class="text-muted font-monospace bn-digit small" style="font-size: 11px;">
            <span id="showing-entries-text">Showing {{ $abandonedCarts->firstItem() ?? 0 }} to {{ $abandonedCarts->lastItem() ?? 0 }} of {{ $abandonedCarts->total() }} carts</span>
        </div>
    </div>

    {{-- 5. Abandoned Carts Table Container (Desktop 100% Fit Screen with Zero Horizontal Scroll) --}}
    <div class="cart-table-container shadow-sm mb-3">
        <div class="table-responsive pc-fit-container" style="min-height: 250px;">
            <table class="table custom-carts-table align-middle mb-0" id="abandoned-carts-main-table">
                <thead>
                    <tr>
                        <th class="text-center col-cb-select">
                            <input type="checkbox" class="form-check-input mt-0 shadow-none" id="check-all-carts" style="cursor: pointer; width: 14px; height: 14px; border-radius: 4px;">
                        </th>
                        <th class="ps-2 col-cb-id text-center">#ID</th>
                        <th class="col-cb-user">Customer / Contact</th>
                        <th class="col-cb-items">Cart Items Overview</th>
                        <th class="col-cb-val">Cart Value</th>
                        <th class="text-center col-cb-status">Recovery Status</th>
                        <th class="text-center col-cb-sent">Reminders Sent</th>
                        <th class="col-cb-date">Abandoned Date</th>
                        <th class="text-end pe-3 col-cb-action">Action</th>
                    </tr>
                </thead>
                <tbody id="abandoned-carts-table-tbody">
                    @include('admin.email_marketing.cart_wishlist_partials.table_rows', ['abandonedCarts' => $abandonedCarts])
                </tbody>
            </table>
        </div>
    </div>

    {{-- 6. Responsive Pagination Footer --}}
    <div class="d-flex justify-content-center justify-content-md-end mb-4" id="carts-pagination-footer" style="{{ $abandonedCarts->hasPages() ? '' : 'display: none;' }}">
        <div id="carts-pagination-links">
            @if($abandonedCarts->hasPages())
                {{ $abandonedCarts->links('pagination::bootstrap-5') }}
            @endif
        </div>
    </div>

</div>

{{-- MODAL 1: Bulk Cart Recovery Email Modal --}}
<div class="modal fade recovery-modal-scope" id="bulkRecoveryModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom py-2 px-3 bg-light d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                    <i class="fas fa-paper-plane text-warning"></i> <span>Bulk Cart Recovery Email Dispatch</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkRecoveryForm" onsubmit="submitBulkRecoveryForm(event)">
                <div class="modal-body p-3">
                    <div class="alert alert-warning py-2 px-3 small mb-3 border-0 text-dark d-flex align-items-center gap-2" style="border-radius: 5px; background-color: #fffbeb;">
                        <i class="fas fa-cart-shopping text-warning fs-5"></i>
                        <div>Selected Sessions: <strong id="bulk-modal-cart-count" class="font-monospace">0</strong> cart(s).</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="bulk-recovery-subject">Email Subject <span class="text-danger">*</span></label>
                        <input type="text" id="bulk-recovery-subject" class="form-control font-monospace shadow-none" placeholder="e.g. Complete your order at Shop Now BD - Items waiting in your cart!" required>
                    </div>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-1">
                            <label class="form-label mb-0" for="bulk-recovery-body">Email Message (HTML / Text) <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-1">
                                <span class="tag-chip font-monospace" onclick="insertPlaceholderToBody('{name}')" title="Insert Customer Name">+{name}</span>
                                <span class="tag-chip font-monospace" onclick="insertPlaceholderToBody('{cart_value}')" title="Insert Total Value">+{cart_value}</span>
                                <span class="tag-chip font-monospace" onclick="insertPlaceholderToBody('{recovery_url}')" title="Insert Recovery Link">+{recovery_url}</span>
                                <span class="tag-chip font-monospace" onclick="insertPlaceholderToBody('{store_name}')" title="Insert Store Name">+{store_name}</span>
                            </div>
                        </div>
                        <textarea id="bulk-recovery-body" rows="6" class="form-control font-monospace shadow-none" style="font-size: 11.5px;" placeholder="Hello {name},&#10;&#10;We noticed you left items in your shopping cart valued at ৳ {cart_value}.&#10;Click below to complete your purchase:&#10;{recovery_url}" required></textarea>
                        <small class="text-muted d-block mt-1 font-monospace" style="font-size: 10px;">
                            <i class="fas fa-info-circle text-primary me-1"></i> A stylized 'Complete Purchase' button is automatically appended if {recovery_url} is omitted.
                        </small>
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark btn-compact shadow-xs fw-bold px-3" id="btn-submit-bulk-recovery">
                        <i class="fas fa-paper-plane me-1"></i> <span>Send Reminders</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL 2: Custom Confirmation Modal (Delete & Bulk Delete) --}}
<div class="modal fade recovery-modal-scope" id="customConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2 bg-danger-subtle text-danger" style="width: 44px; height: 44px; border-radius: 50% !important;">
                    <i class="fas fa-trash-can fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="confirm-modal-title" style="font-size: 14px;">Delete Session</h6>
                <p class="text-secondary small mb-3" id="confirm-modal-msg" style="font-size: 11px; line-height: 1.45;">Are you sure you want to proceed?</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light border btn-compact" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-compact shadow-xs fw-bold" id="btn-confirm-action-execute">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Toast Feedback Notification Holder --}}
<div id="custom-toast-holder"></div>

{{-- JavaScript Engine --}}
<script>
    let searchTimeout = null;
    let pendingConfirmCallback = null;

    // 1. Live Reload-free Fetch
    function fetchFilteredCarts(page = 1) {
        const form = document.getElementById('cart-filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.append('page', page);

        const tbody = document.getElementById('abandoned-carts-table-tbody');
        if (tbody) tbody.style.opacity = '0.35';

        fetch("{{ url('admin/email-marketing/cart-wishlist') }}?" + params.toString(), {
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
            const paginationFooter = document.getElementById('carts-pagination-footer');
            const paginationLinks  = document.getElementById('carts-pagination-links');
            const perPageVal       = document.getElementById('filter-per-page')?.value || '25';

            if (paginationLinks && paginationFooter) {
                if (perPageVal === 'all' || !data.pagination_html || data.pagination_html.trim() === '') {
                    paginationLinks.innerHTML = '';
                    paginationFooter.style.display = 'none';
                } else {
                    paginationLinks.innerHTML = data.pagination_html;
                    paginationFooter.style.display = 'flex';
                }
            }

            // Stats Update with Clean Taka Symbol
            if (data.stats) {
                const totalEl     = document.getElementById('stat-total-abandoned');
                const recoveredEl = document.getElementById('stat-recovered-orders');
                const pendingEl   = document.getElementById('stat-pending-recovery');
                const valueEl     = document.getElementById('stat-monitored-value');
                const revenueEl   = document.getElementById('stat-recovered-revenue');
                const badgeEl     = document.getElementById('carts-total-badge');

                if (totalEl)     totalEl.innerText = Number(data.stats.total).toLocaleString();
                if (recoveredEl) recoveredEl.innerText = Number(data.stats.recovered).toLocaleString();
                if (pendingEl)   pendingEl.innerText = Number(data.stats.pending).toLocaleString();
                if (valueEl)     valueEl.innerText = '৳ ' + Number(data.stats.monitored_value || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                if (revenueEl)   revenueEl.innerText = '৳ ' + Number(data.stats.recovered_revenue || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                if (badgeEl)     badgeEl.innerText = Number(data.stats.total).toLocaleString() + ' Sessions';
            }

            if (data.entries_text) {
                const entriesEl = document.getElementById('showing-entries-text');
                if (entriesEl) entriesEl.innerText = data.entries_text;
            }

            const checkAll = document.getElementById('check-all-carts');
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
        searchTimeout = setTimeout(() => fetchFilteredCarts(1), 350);
    });

    document.querySelectorAll('.filter-trigger').forEach(el => {
        el.addEventListener('change', () => fetchFilteredCarts(1));
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
            fetchFilteredCarts(1);
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

        fetchFilteredCarts(1);
    }

    function resetAllFilters() {
        document.getElementById('cart-filter-form')?.reset();
        resetDatePanels();
    }

    // 4. Pagination Link Click Interceptor
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#carts-pagination-links .page-link');
        if (pageLink) {
            e.preventDefault();
            const href = pageLink.getAttribute('href');
            if (href && href !== '#' && !href.startsWith('javascript:')) {
                const url = new URL(href);
                const page = url.searchParams.get('page') || 1;
                fetchFilteredCarts(page);
                document.querySelector('.cart-table-container')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    });

    // 5. Checkbox Selection & Badge Sync
    document.getElementById('check-all-carts')?.addEventListener('change', function() {
        document.querySelectorAll('.cart-select-cb').forEach(cb => cb.checked = this.checked);
        updateSelectionBadge();
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('cart-select-cb')) {
            updateSelectionBadge();
        }
    });

    function getSelectedCartIds() {
        return Array.from(document.querySelectorAll('.cart-select-cb:checked')).map(cb => cb.value);
    }

    function updateSelectionBadge() {
        const selected = getSelectedCartIds();
        const badge = document.getElementById('selected-carts-badge');
        if (badge) {
            if (selected.length > 0) {
                badge.innerText = `${selected.length} selected`;
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }
        }
    }

    // 6. CSV Exporter (Exports with Current Active Filters)
    function exportCartsCsv() {
        const form = document.getElementById('cart-filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        const url = "{{ url('admin/email-marketing/cart-wishlist/export-csv') }}?" + params.toString();
        window.open(url, '_blank');
    }

    // 7. Direct Recovery Reminder Trigger AJAX
    function sendCartRecoveryDirectAjax(cartId, email) {
        fetch(`{{ url('admin/email-marketing/cart-wishlist/send-recovery') }}/${cartId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Dispatched', data.message, 'success');
                fetchFilteredCarts(1);
            } else {
                showToast('Failed', data.message || 'Could not send reminder', 'danger');
            }
        })
        .catch(() => {
            showToast('Error', 'Server or mailer delivery error.', 'danger');
        });
    }

    // 8. Bulk Recovery Broadcast
    function openBulkRecoveryModal() {
        const selected = getSelectedCartIds();
        if (selected.length === 0) {
            showToast('Warning', 'Please select at least one cart from the table checkbox.', 'warning');
            return;
        }

        const countEl = document.getElementById('bulk-modal-cart-count');
        if (countEl) countEl.innerText = selected.length;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('bulkRecoveryModal')).show();
    }

    function insertPlaceholderToBody(tag) {
        const textarea = document.getElementById('bulk-recovery-body');
        if (textarea) {
            const start = textarea.selectionStart || 0;
            const end   = textarea.selectionEnd || 0;
            const text  = textarea.value;
            textarea.value = text.substring(0, start) + tag + text.substring(end);
            textarea.focus();
            textarea.selectionStart = textarea.selectionEnd = start + tag.length;
        }
    }

    function submitBulkRecoveryForm(e) {
        e.preventDefault();
        const selected = getSelectedCartIds();
        const subject = document.getElementById('bulk-recovery-subject')?.value.trim();
        const message = document.getElementById('bulk-recovery-body')?.value.trim();
        const btn = document.getElementById('btn-submit-bulk-recovery');

        if (!subject || !message) {
            showToast('Warning', 'Subject and message are required.', 'warning');
            return;
        }

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Broadcasting...';
        }

        fetch("{{ url('admin/email-marketing/cart-wishlist/bulk-send') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                cart_ids: selected,
                subject: subject,
                message: message
            })
        })
        .then(res => res.json())
        .then(data => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Reminders';
            }
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('bulkRecoveryModal'))?.hide();
                document.getElementById('bulkRecoveryForm')?.reset();
                showToast('Success', data.message, 'success');
                fetchFilteredCarts(1);
            } else {
                showToast('Error', data.message || 'Failed to dispatch reminders', 'danger');
            }
        })
        .catch(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Reminders';
            }
            showToast('Error', 'Network or server error while dispatching recovery emails.', 'danger');
        });
    }

    // 9. Single Cart Deletion
    function deleteCartAjax(cartId, email) {
        const titleEl = document.getElementById('confirm-modal-title');
        const msgEl   = document.getElementById('confirm-modal-msg');
        if (titleEl) titleEl.innerText = 'Delete Cart Session';
        if (msgEl) msgEl.innerHTML = `Are you sure you want to delete abandoned session <strong>#${cartId}</strong> (${email})?`;

        pendingConfirmCallback = function() {
            fetch(`{{ url('admin/email-marketing/cart-wishlist/delete') }}/${cartId}`, {
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
                fetchFilteredCarts(1);
            })
            .catch(() => {
                bootstrap.Modal.getInstance(document.getElementById('customConfirmModal'))?.hide();
                fetchFilteredCarts(1);
            });
        };

        bootstrap.Modal.getOrCreateInstance(document.getElementById('customConfirmModal')).show();
    }

    // 10. Bulk Cart Deletion
    function openBulkDeleteModal() {
        const selected = getSelectedCartIds();
        if (selected.length === 0) {
            showToast('Warning', 'Please select at least one cart session to delete.', 'warning');
            return;
        }

        const titleEl = document.getElementById('confirm-modal-title');
        const msgEl   = document.getElementById('confirm-modal-msg');
        if (titleEl) titleEl.innerText = 'Bulk Delete Sessions';
        if (msgEl) msgEl.innerHTML = `Are you sure you want to delete <strong>${selected.length}</strong> selected abandoned session(s)?`;

        pendingConfirmCallback = function() {
            fetch("{{ url('admin/email-marketing/cart-wishlist/bulk-delete') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    cart_ids: selected
                })
            })
            .then(res => res.json())
            .then(data => {
                bootstrap.Modal.getInstance(document.getElementById('customConfirmModal'))?.hide();
                showToast('Deleted', data.message, 'danger');
                fetchFilteredCarts(1);
            })
            .catch(() => {
                bootstrap.Modal.getInstance(document.getElementById('customConfirmModal'))?.hide();
                fetchFilteredCarts(1);
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
            <div id="${toastId}" class="admin-cart-toast ${config.color} p-3 d-flex align-items-center justify-content-between shadow-lg" style="border-radius: 5px !important;">
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