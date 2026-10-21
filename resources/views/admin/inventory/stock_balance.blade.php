@extends('layouts.admin')

@section('title', 'Stock Balance & Inventory Valuation Sheet - ' . config('app.name', 'Shop Now BD'))

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #stockBalanceRoot .btn,
    #stockBalanceRoot .btn-sm,
    #stockBalanceRoot button,
    #stockBalanceRoot .form-control,
    #stockBalanceRoot .form-select,
    #stockBalanceRoot .badge,
    #stockBalanceRoot .alert,
    #stockBalanceRoot .input-group-text,
    #stockBalanceRoot .card,
    #stockBalanceRoot .metric-card,
    #stockBalanceRoot .filter-toolbar-card,
    #stockBalanceRoot .metric-icon-box,
    #stockBalanceRoot .page-link,
    #stockBalanceRoot .page-item span,
    #stockBalanceRoot .proof-thumb-box,
    #stockBalanceRoot .collapse-toggle-btn,
    .balance-modal-scope .modal-content,
    .balance-modal-scope .btn,
    .balance-modal-scope .form-control,
    .balance-modal-scope .form-select {
        border-radius: 5px !important;
    }

    #stockBalanceRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #stockBalanceRoot .input-group > .form-control {
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
    #stockBalanceRoot .card,
    #stockBalanceRoot .metric-card,
    #stockBalanceRoot .filter-toolbar-card {
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

    /* KPI Summary Cards - Zero Overflow Architecture */
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
    .metric-icon-box {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }

    .metric-card-purple { background: linear-gradient(180deg, #ffffff 0%, #faf5ff 100%); border-color: #e9d5ff; }
    .metric-card-blue   { background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%); border-color: #bae6fd; }
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-color: #bbf7d0; }
    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-color: #fde68a; }

    /* Form Controls */
    #stockBalanceRoot .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #stockBalanceRoot .form-control,
    #stockBalanceRoot .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #stockBalanceRoot .form-control:focus,
    #stockBalanceRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Desktop View Optimization: 100% Fit Screen with Zero Horizontal Scroll */
    .custom-balance-table {
        width: 100%;
        margin-bottom: 0;
    }
    .custom-balance-table th {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 6px;
        white-space: nowrap;
    }
    .custom-balance-table td {
        padding: 8px 6px;
        vertical-align: middle;
        font-size: 11px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .custom-balance-table tbody tr:hover {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .custom-balance-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .custom-balance-table th,
        .custom-balance-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 10 Columns Proportional Distribution Summing to Exactly 100% */
        .col-b-id    { width: 4% !important; }
        .col-b-prod  { width: 23% !important; }
        .col-b-type  { width: 8% !important; }
        .col-b-sku   { width: 11% !important; }
        .col-b-stock { width: 10% !important; }
        .col-b-cost  { width: 9% !important; }
        .col-b-price { width: 9% !important; }
        .col-b-val   { width: 11% !important; }
        .col-b-stat  { width: 8% !important; }
        .col-b-act   { width: 7% !important; }
    }

    /* Global Media Chooser Thumbnail Box */
    .proof-thumb-box {
        width: 26px;
        height: 26px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        position: relative;
    }
    .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .proof-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.15s ease;
        border-radius: 5px !important;
    }
    .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    /* Bulletproof Responsive Pagination */
    #balancePaginationLinks {
        display: flex;
        align-items: center;
    }
    #balancePaginationLinks nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }
    #balancePaginationLinks nav > div.d-sm-none,
    #balancePaginationLinks nav > .d-sm-none {
        display: none !important;
    }
    #balancePaginationLinks nav > div.d-none,
    #balancePaginationLinks nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    #balancePaginationLinks nav p,
    #balancePaginationLinks nav .small.text-muted,
    #balancePaginationLinks nav div:has(> p),
    #balancePaginationLinks nav > div.d-none > div:first-child,
    #balancePaginationLinks nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }
    #balancePaginationLinks .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 3px !important;
        padding: 0 !important;
        list-style: none !important;
    }
    #balancePaginationLinks .page-item {
        display: inline-flex !important;
    }
    #balancePaginationLinks .page-item .page-link,
    #balancePaginationLinks .page-item span {
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
    #balancePaginationLinks .page-item .page-link:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }
    #balancePaginationLinks .page-item.active .page-link,
    #balancePaginationLinks .page-item.active span {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25) !important;
    }
    #balancePaginationLinks .page-item.disabled .page-link,
    #balancePaginationLinks .page-item.disabled span {
        opacity: 0.45 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }
    #balancePaginationLinks nav svg {
        width: 10px !important;
        height: 10px !important;
    }

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .balance-header-wrap h5 {
            font-size: 16px !important;
        }
        .top-action-btns {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .top-action-btns .btn {
            flex: 1 1 calc(33.333% - 6px);
            min-width: 95px;
            padding: 7px 4px !important;
            font-size: 10px !important;
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
        .metric-icon-box {
            width: 28px !important;
            height: 28px !important;
            font-size: 11px !important;
        }
        .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .custom-balance-table {
            min-width: 820px !important;
            table-layout: auto !important;
        }
        #balancePaginationLinks {
            width: 100% !important;
            justify-content: center !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            padding: 4px 0 !important;
        }
        #balancePaginationLinks nav {
            justify-content: center !important;
            width: 100% !important;
        }
        #balancePaginationLinks .pagination {
            flex-wrap: nowrap !important;
            gap: 3px !important;
        }
        #balancePaginationLinks .page-item .page-link,
        #balancePaginationLinks .page-item span {
            min-width: 24px !important;
            height: 24px !important;
            font-size: 10px !important;
            padding: 0 5px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="stockBalanceRoot">
    <!-- AJAX Loading Spinner Overlay -->
    <div id="balanceLoadingCover" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none justify-content-center align-items-center" style="z-index: 50; border-radius: 5px;">
        <div class="spinner-border text-primary" style="width: 2.2rem; height: 2.2rem;" role="status"></div>
    </div>

    <!-- 1. Header & Actions (Original Clean Light Theme) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 balance-header-wrap">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-scale-balanced text-primary"></i>
                <span>Stock Balance &amp; Current Inventory Sheet</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Real-time SKU-level stock positions, purchase valuation, selling value, and stock status.
            </small>
        </div>
        <div class="d-flex gap-2 top-action-btns">
            <a href="{{ route('admin.inventory.stock_in') }}" class="btn btn-sm btn-success btn-compact shadow-xs fw-bold">
                <i class="fas fa-arrow-down-long me-1"></i> <span>Stock In</span>
            </a>
            <a href="{{ route('admin.inventory.stock_out') }}" class="btn btn-sm btn-danger btn-compact shadow-xs fw-bold">
                <i class="fas fa-arrow-up-long me-1"></i> <span>Stock Out</span>
            </a>
            <button type="button" class="btn btn-sm btn-outline-success btn-compact bg-white shadow-xs fw-bold" onclick="exportBalanceCsv()">
                <i class="fas fa-file-excel text-success me-1"></i> <span>Export (CSV)</span>
            </button>
        </div>
    </div>

    <!-- 2. KPI Summary Cards (Collapsible Section) -->
    <div class="card border-0 shadow-sm mb-3" style="border: 1px solid #e2e8f0; background: #ffffff;">
        <div class="section-header-wrap d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-chart-pie text-primary me-1"></i> Inventory Portfolio &amp; Valuation Summary
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseBalanceKpis" aria-expanded="true" aria-controls="collapseBalanceKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseBalanceKpis">
            <div class="p-3">
                <div class="row g-2">
                    <!-- Total Distinct Items -->
                    <div class="col-6 col-xl-3">
                        <div class="metric-card metric-card-blue h-100">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Total Line Items</span>
                                <div class="metric-icon-box bg-primary text-white shadow-xs">
                                    <i class="fas fa-boxes-stacked"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-primary bn-digit font-monospace stat-val-num" id="val-total-items">
                                    {{ number_format($summary->total_items ?? 0) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Active line items</small>
                            </div>
                        </div>
                    </div>

                    <!-- Total Stock Units -->
                    <div class="col-6 col-xl-3">
                        <div class="metric-card metric-card-amber h-100">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Available Stock</span>
                                <div class="metric-icon-box text-dark shadow-xs" style="background-color: #f59e0b;">
                                    <i class="fas fa-cubes"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num" id="val-total-units">
                                    {{ number_format($summary->total_units ?? 0) }} <span style="font-size: 11px;">Units</span>
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Cumulative units</small>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Purchase Valuation -->
                    <div class="col-6 col-xl-3">
                        <div class="metric-card metric-card-purple h-100">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Purchase Valuation</span>
                                <div class="metric-icon-box text-white shadow-xs" style="background-color: #7c3aed;">
                                    <i class="fas fa-vault"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num" id="val-total-purchase">
                                    ৳ {{ number_format($summary->total_purchase_value ?? 0, 2) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Weighted Cost</small>
                            </div>
                        </div>
                    </div>

                    <!-- Total Retail Selling Value -->
                    <div class="col-6 col-xl-3">
                        <div class="metric-card metric-card-green h-100">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Retail Value</span>
                                <div class="metric-icon-box bg-success text-white shadow-xs">
                                    <i class="fas fa-tag"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num" id="val-total-selling">
                                    ৳ {{ number_format($summary->total_selling_value ?? 0, 2) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Selling Price</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Filter Toolbar (Collapsible) -->
    <div class="filter-toolbar-card overflow-hidden">
        <div class="section-header-wrap d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-filter text-primary me-1"></i> Filter Stock Positions
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseBalanceFilter" aria-expanded="true" aria-controls="collapseBalanceFilter" title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseBalanceFilter">
            <div class="p-3">
                <form id="balance-filter-form" onsubmit="return false;">
                    <div class="row g-2 align-items-end">
                        <!-- Search Input -->
                        <div class="col-12 col-md-5">
                            <label class="form-label" for="balance-search">
                                <i class="fas fa-search me-1 text-primary"></i> Search Product / Variant
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                                <input type="text" name="search" id="balance-search" class="form-control border-start-0 ps-0 shadow-none" placeholder="Product title, SKU, ID, color, or size..." value="{{ request('search') }}" onkeyup="handleSearchKeyup(event)">
                            </div>
                        </div>

                        <!-- Stock Status Filter -->
                        <div class="col-6 col-md-4">
                            <label class="form-label" for="balance-stock-status">
                                <i class="fas fa-filter me-1 text-warning"></i> Stock Status Filter
                            </label>
                            <select name="stock_status" id="balance-stock-status" class="form-select shadow-none font-monospace" onchange="applyBalanceFilter()">
                                <option value="all" {{ !request()->filled('stock_status') || request('stock_status') === 'all' ? 'selected' : '' }}>All Items</option>
                                <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock (Healthy)</option>
                                <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock (&le; Alert Level)</option>
                                <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock (Zero / Negative)</option>
                            </select>
                        </div>

                        <!-- Filter Button -->
                        <div class="col-6 col-md-3">
                            <button type="button" class="btn btn-dark btn-compact w-100 fw-bold shadow-xs" onclick="applyBalanceFilter()">
                                <i class="fas fa-sync-alt me-1" style="font-size: 10px;"></i>
                                <span>Apply Filter</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 4. Stock Balance Sheet Table (Desktop 100% Fit Screen with Zero Horizontal Scroll) -->
    <div class="card p-0 mb-3 overflow-hidden shadow-xs">
        <div class="p-2 px-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 bg-light bg-opacity-25">
            <div>
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13px;">
                    <i class="fas fa-table-list text-primary"></i>
                    <span>Real-Time Stock Balance Sheet</span>
                </h6>
                <small class="text-muted font-monospace bn-digit" style="font-size: 10px;" id="balance-entries-text">
                    Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{$items->total() }} items
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0 font-monospace" style="font-size: 11px;">Show:</label>
                <select name="per_page" id="balance-per-page" class="form-select shadow-none bn-digit font-monospace" style="width: 75px; font-size: 11px;" onchange="applyBalanceFilter()">
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ $perPage == 100000 ? 'selected' : '' }}>All</option>
                </select>
            </div>
        </div>

        <div class="table-responsive pc-fit-container">
            <table class="table align-middle mb-0 custom-balance-table" id="balance-table">
                <thead>
                    <tr>
                        <th class="ps-3 col-b-id bn-digit">ID</th>
                        <th class="col-b-prod">Product &amp; Attributes</th>
                        <th class="col-b-type">Type</th>
                        <th class="col-b-sku">SKU / Code</th>
                        <th class="text-center col-b-stock">Available</th>
                        <th class="text-end col-b-cost">Unit Cost</th>
                        <th class="text-end col-b-price">Price</th>
                        <th class="text-end col-b-val">Valuation</th>
                        <th class="text-center col-b-stat">Status</th>
                        <th class="text-end pe-3 col-b-act">Action</th>
                    </tr>
                </thead>
                <tbody id="balance-tbody">
                    @include('admin.inventory.partials.balance_rows', ['items' => $items])
                </tbody>
            </table>
        </div>

        <!-- Pagination & Footer (Exact Transaction Standard) -->
        <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50" id="balance-pagination-container">
            <div class="small text-muted font-monospace bn-digit" style="font-size: 11px;">
                Total <strong>{{ $items->total() }}</strong> records matching filter
            </div>
            <div id="balancePaginationLinks">
                {{ $items->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal: Lightbox Zoom Modal for Product & Variant Images -->
<div class="modal fade balance-modal-scope" id="balanceImageZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12px;" id="balanceZoomModalTitle">
                    <i class="fas fa-image text-primary me-1"></i> Product / Variant Preview
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="balanceZoomModalImgTag" src="" alt="Product Photo" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<script>
    function handleSearchKeyup(e) {
        if (e.key === 'Enter') {
            applyBalanceFilter();
        }
    }

    function zoomBalanceImage(url, title = 'Product Image') {
        const titleEl = document.getElementById('balanceZoomModalTitle');
        const imgTag  = document.getElementById('balanceZoomModalImgTag');
        if (url && imgTag) {
            if (titleEl) titleEl.innerText = title;
            imgTag.src = url;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('balanceImageZoomModal')).show();
        }
    }

    function extractMediaItem(mediaInput) {
        if (!mediaInput) return null;
        const item = Array.isArray(mediaInput) ? mediaInput[0] : mediaInput;
        if (!item || typeof item !== 'object') return null;
        const rawPath = item.file_path || item.path || item.url || '';
        const rawUrl  = item.url || item.file_path || item.path || '';
        if (!rawPath || rawPath === 'undefined') return null;
        return {
            path: String(rawPath).trim(),
            url:  String(rawUrl).trim()
        };
    }

    function chooseBalanceProductMedia(productId) {
        if (typeof window.openMediaPicker === 'function') {
            window.openMediaPicker({
                multiple: false,
                onSelect: function(media) {
                    const picked = extractMediaItem(media);
                    if (picked && picked.path) {
                        fetch("{{ url('admin/product/quick-update-image') }}/" + productId, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ image_path: picked.path })
                        })
                        .then(res => res.json())
                        .then(res => {
                            applyBalanceFilter();
                        })
                        .catch(() => {
                            applyBalanceFilter();
                        });
                    }
                }
            });
        } else {
            alert('Global Media Library is loading, please try again.');
        }
    }

    function applyBalanceFilter(targetUrl = null) {
        const form = document.getElementById('balance-filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.append('per_page', document.getElementById('balance-per-page').value);

        const cover = document.getElementById('balanceLoadingCover');
        if (cover) {
            cover.classList.remove('d-none');
            cover.classList.add('d-flex');
        }

        const url = targetUrl || ("{{ route('admin.inventory.balance') }}?" + params.toString());

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('balance-tbody').innerHTML = data.table_html;
                document.getElementById('balance-pagination-container').innerHTML = `
                    <div class="small text-muted font-monospace bn-digit" style="font-size: 11px;">
                        Total <strong>${data.total_count}</strong> records matching filter
                    </div>
                    <div id="balancePaginationLinks">${data.pagination_html}</div>
                `;

                if (data.summary) {
                    document.getElementById('val-total-items').innerText = data.summary.total_items;
                    document.getElementById('val-total-units').innerHTML = `${data.summary.total_units} <span style="font-size: 11px;">Units</span>`;
                    document.getElementById('val-total-purchase').innerText = `৳ ${data.summary.total_purchase_value}`;
                    document.getElementById('val-total-selling').innerText = `৳ ${data.summary.total_selling_value}`;
                }
            }
        })
        .catch(err => console.error('Balance filter error:', err))
        .finally(() => {
            if (cover) {
                cover.classList.add('d-none');
                cover.classList.remove('d-flex');
            }
        });
    }

    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#balancePaginationLinks a');
        if (pageLink) {
            e.preventDefault();
            const href = pageLink.getAttribute('href');
            if (href && href !== '#' && !href.startsWith('javascript:')) {
                applyBalanceFilter(href);
            }
        }
    });

    function exportBalanceCsv() {
        const form = document.getElementById('balance-filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.append('export', 'csv');
        window.open("{{ route('admin.inventory.balance') }}?" + params.toString(), '_blank');
    }
</script>
@endsection