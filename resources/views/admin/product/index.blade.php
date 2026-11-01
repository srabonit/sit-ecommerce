@extends('layouts.admin')

@section('title', 'Products Management - ' . config('app.name'))

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #admin-product-view-root .btn,
    #admin-product-view-root .btn-sm,
    #admin-product-view-root button,
    #admin-product-view-root .form-control,
    #admin-product-view-root .form-select,
    #admin-product-view-root .badge,
    #admin-product-view-root .input-group-text,
    #admin-product-view-root .page-link,
    #admin-product-view-root .page-item span,
    #admin-product-view-root .product-main-card,
    #admin-product-view-root .woo-table-container,
    #admin-product-view-root .filter-bar-box,
    #admin-product-view-root .proof-thumb-box,
    #customConfirmModal .btn,
    #customConfirmModal .modal-content,
    .product-modal-scope .modal-content,
    .product-modal-scope .btn,
    .admin-toast-box {
        border-radius: 5px !important;
    }

    #admin-product-view-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #admin-product-view-root .input-group > .form-control,
    #admin-product-view-root .input-group > .form-select {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Safe Bengali Digit & Monospace Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Compact & Sleek Button Sizing */
    #admin-product-view-root .btn-compact,
    #customConfirmModal .btn-compact,
    .product-modal-scope .btn-compact {
        font-size: 11.5px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    /* Enterprise Surface Container */
    .product-main-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    /* Advanced Table Container */
    .woo-table-container {
        position: relative;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    /* Desktop View Optimization: 100% Fit Screen with Zero Horizontal Scroll */
    .woo-table {
        width: 100%;
        margin-bottom: 0;
    }
    .woo-table th {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        white-space: nowrap;
        padding: 8px 5px;
    }
    .woo-table td {
        padding: 7px 5px;
        vertical-align: middle;
        font-size: 11px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .woo-table tbody tr:hover {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .woo-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .woo-table th,
        .woo-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 13 Columns Proportional Distribution Summing to Exactly 100% */
        .col-checkbox       { width: 2.5%; }
        .col-id             { width: 3.5%; }
        .col-thumb          { width: 4.5%; }
        .col-title          { width: 20%; }
        .col-sku            { width: 8.5%; }
        .col-category       { width: 8.5%; }
        .col-brand          { width: 7%; }
        .col-stock          { width: 7%; }
        .col-price          { width: 8%; }
        .col-purchase-price { width: 7.5%; }
        .col-tax            { width: 8%; }
        .col-featured       { width: 4.5%; }
        .col-action         { width: 8.5%; }
    }

    /* Filter Bar Box */
    .filter-bar-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 12px 14px;
    }
    .column-dropdown-menu {
        min-width: 220px;
        max-height: 340px;
        overflow-y: auto;
        font-size: 12px;
        border-radius: 5px !important;
    }

    /* Form Filter Controls */
    .product-filter-control {
        font-size: 12px !important;
        height: 32px !important;
        padding: 4px 8px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        box-shadow: none !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .product-filter-control:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        outline: none;
    }
    .product-addon-text {
        height: 32px !important;
        background-color: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: #64748b !important;
    }

    /* Bottom Summary Strip */
    .product-summary-strip {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 10px 14px;
    }
    .summary-badge-item {
        font-size: 11.5px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    /* Global Media Chooser Thumbnail Box */
    .proof-thumb-box {
        width: 30px;
        height: 30px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
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
    }
    .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    /* Floating Feedback Toast */
    .admin-toast-box {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1080;
        min-width: 290px;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.15);
        overflow: hidden;
        animation: slideInRight 0.25s ease;
    }
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    /* Bulletproof Responsive Pagination */
    #product-pagination-links {
        display: flex;
        align-items: center;
    }
    #product-pagination-links nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }
    #product-pagination-links nav > div.d-sm-none,
    #product-pagination-links nav > .d-sm-none {
        display: none !important;
    }
    #product-pagination-links nav > div.d-none,
    #product-pagination-links nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    #product-pagination-links nav p,
    #product-pagination-links nav .small.text-muted,
    #product-pagination-links nav div:has(> p),
    #product-pagination-links nav > div.d-none > div:first-child,
    #product-pagination-links nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }
    #product-pagination-links .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 3px !important;
        padding: 0 !important;
        list-style: none !important;
    }
    #product-pagination-links .page-item {
        display: inline-flex !important;
    }
    #product-pagination-links .page-item .page-link,
    #product-pagination-links .page-item span {
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
    #product-pagination-links .page-item .page-link:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }
    #product-pagination-links .page-item.active .page-link,
    #product-pagination-links .page-item.active span {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25) !important;
    }
    #product-pagination-links .page-item.disabled .page-link,
    #product-pagination-links .page-item.disabled span {
        opacity: 0.45 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }
    #product-pagination-links nav svg {
        width: 10px !important;
        height: 10px !important;
    }

    /* Mobile Responsive Tuning (< 768px) */
    @media (max-width: 767.98px) {
        #product-top-header h5 {
            font-size: 16px !important;
        }
        .header-action-group {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .header-action-group .btn-compact,
        .header-action-group .dropdown,
        .header-action-group .dropdown > button {
            width: 100% !important;
            justify-content: center;
        }
        .header-action-group a.btn-warning {
            grid-column: span 2;
        }
        #product-bulk-action-bar {
            flex-direction: column;
            align-items: stretch !important;
            gap: 8px;
        }
        #bulk-action-selector {
            width: 100% !important;
        }
        .product-summary-strip {
            padding: 10px 12px;
        }
        .summary-badge-item {
            width: 100%;
            border-left: none !important;
            padding-left: 0 !important;
            font-size: 11px;
        }
        .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .woo-table {
            min-width: 960px !important;
            table-layout: auto !important;
        }
        #product-pagination-links {
            width: 100% !important;
            justify-content: center !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            padding: 4px 0 !important;
        }
        #product-pagination-links nav {
            justify-content: center !important;
            width: 100% !important;
        }
        #product-pagination-links .pagination {
            flex-wrap: nowrap !important;
            gap: 3px !important;
        }
        #product-pagination-links .page-item .page-link,
        #product-pagination-links .page-item span {
            min-width: 24px !important;
            height: 24px !important;
            font-size: 10.5px !important;
            padding: 0 5px !important;
        }
    }
</style>

<div class="product-main-card p-3 p-md-4 mb-4" id="admin-product-view-root">
    
    <!-- Top Bar: Header & Actions -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 pb-3 border-bottom" id="product-top-header">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center" style="font-size: 18px;">
                    <i class="fas fa-boxes text-primary me-2"></i>
                    <span>Products Directory</span>
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 bn-digit font-monospace" id="product-total-counter" style="font-size: 11px;">
                    {{ $totalProductsCount ?? $products->total() }}
                </span>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">Manage catalog inventory, tax &amp; VAT filters, pricing and 1-click exports.</small>
        </div>

        <div class="header-action-group d-flex flex-wrap gap-2 align-self-start align-self-md-auto">
            <!-- 1-Click Excel Export Button -->
            <button type="button" class="btn btn-outline-success btn-compact shadow-xs" onclick="exportProductsData('excel')" title="Export filtered catalog to Excel/CSV">
                <i class="fas fa-file-excel me-1 text-success"></i>
                <span>Export Excel</span>
            </button>

            <!-- 1-Click PDF Export Button -->
            <button type="button" class="btn btn-outline-danger btn-compact shadow-xs" onclick="exportProductsData('pdf')" title="Export filtered catalog to PDF/Print">
                <i class="fas fa-file-pdf me-1 text-danger"></i>
                <span>Export PDF</span>
            </button>

            <!-- Column Toggle Dropdown -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-compact dropdown-toggle shadow-xs" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                    <i class="fas fa-columns me-1"></i>
                    <span>Screen Options</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow border column-dropdown-menu p-3">
                    <h6 class="fw-bold small mb-2 border-bottom pb-1" style="font-size: 11.5px;">Toggle Columns</h6>
                    <div class="form-check mb-1">
                        <input class="form-check-input col-toggle-cb" type="checkbox" data-target="col-id" id="toggle-col-id" checked>
                        <label class="form-check-label" for="toggle-col-id">Product ID</label>
                    </div>
                    <div class="form-check mb-1">
                        <input class="form-check-input col-toggle-cb" type="checkbox" data-target="col-thumb" id="toggle-col-thumb" checked>
                        <label class="form-check-label" for="toggle-col-thumb">Thumbnail</label>
                    </div>
                    <div class="form-check mb-1">
                        <input class="form-check-input col-toggle-cb" type="checkbox" data-target="col-sku" id="toggle-col-sku" checked>
                        <label class="form-check-label" for="toggle-col-sku">SKU / Code</label>
                    </div>
                    <div class="form-check mb-1">
                        <input class="form-check-input col-toggle-cb" type="checkbox" data-target="col-category" id="toggle-col-category" checked>
                        <label class="form-check-label" for="toggle-col-category">Category</label>
                    </div>
                    <div class="form-check mb-1">
                        <input class="form-check-input col-toggle-cb" type="checkbox" data-target="col-brand" id="toggle-col-brand" checked>
                        <label class="form-check-label" for="toggle-col-brand">Brand</label>
                    </div>
                    <div class="form-check mb-1">
                        <input class="form-check-input col-toggle-cb" type="checkbox" data-target="col-stock" id="toggle-col-stock" checked>
                        <label class="form-check-label" for="toggle-col-stock">Stock</label>
                    </div>
                    <div class="form-check mb-1">
                        <input class="form-check-input col-toggle-cb" type="checkbox" data-target="col-price" id="toggle-col-price" checked>
                        <label class="form-check-label" for="toggle-col-price">Price</label>
                    </div>
                    <div class="form-check mb-1">
                        <input class="form-check-input col-toggle-cb" type="checkbox" data-target="col-purchase-price" id="toggle-col-purch-price" checked>
                        <label class="form-check-label" for="toggle-col-purch-price">Purchase Price</label>
                    </div>
                    <!-- VAT / Tax Column Toggle -->
                    <div class="form-check mb-1">
                        <input class="form-check-input col-toggle-cb" type="checkbox" data-target="col-tax" id="toggle-col-tax" checked>
                        <label class="form-check-label fw-bold text-primary" for="toggle-col-tax">VAT / Tax</label>
                    </div>
                    <div class="form-check mb-1">
                        <input class="form-check-input col-toggle-cb" type="checkbox" data-target="col-featured" id="toggle-col-featured" checked>
                        <label class="form-check-label" for="toggle-col-featured">Featured Star</label>
                    </div>
                </div>
            </div>

            <!-- Add Product Action -->
            <a href="{{ route('admin.product.create') }}" class="btn btn-warning btn-compact text-dark fw-bold shadow-xs">
                <i class="fas fa-plus me-1"></i>
                <span>Add New Product</span>
            </a>
        </div>
    </div>

    <!-- Multi-Dimensional Advanced Filter Toolbar -->
    <div class="filter-bar-box mb-3" id="product-filter-bar">
        <form id="product-filter-form" onsubmit="return false;">
            <div class="row g-2 align-items-center">
                <!-- 1. Search Input -->
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text product-addon-text border-end-0"><i class="fas fa-search text-muted" style="font-size: 11px;"></i></span>
                        <input type="text" name="search" id="filter-search-input" class="form-control border-start-0 ps-0 product-filter-control" placeholder="Title, SKU, code, barcode, #ID...">
                    </div>
                </div>

                <!-- 2. Category Filter -->
                <div class="col-6 col-md-2">
                    <select name="category_id" id="filter-category" class="form-select product-filter-control filter-trigger">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name ?? $cat->title }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 3. Brand Filter -->
                <div class="col-6 col-md-2">
                    <select name="brand_id" id="filter-brand" class="form-select product-filter-control filter-trigger">
                        <option value="">All Brands</option>
                        @foreach($brands as $b)
                            <option value="{{ $b->id }}">{{ $b->name ?? $b->title }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 4. Stock Status -->
                <div class="col-6 col-md-2">
                    <select name="stock_status" id="filter-stock" class="form-select product-filter-control filter-trigger">
                        <option value="">Stock: All</option>
                        <option value="instock">In Stock</option>
                        <option value="low_stock">Low Stock (&lt; 6 items)</option>
                        <option value="outofstock">Out of Stock (0)</option>
                    </select>
                </div>

                <!-- 5. Featured Filter -->
                <div class="col-6 col-md-1">
                    <select name="is_featured" id="filter-featured" class="form-select product-filter-control filter-trigger">
                        <option value="all">Featured</option>
                        <option value="1">Star Only</option>
                        <option value="0">Regular</option>
                    </select>
                </div>

                <!-- 6. Date Range / Preset Filter -->
                <div class="col-12 col-md-2">
                    <select name="date_filter" id="filter-date-select" class="form-select product-filter-control">
                        <option value="">Date: All Time</option>
                        <option value="today">Added Today</option>
                        <option value="single_date">Specific Single Date...</option>
                        <option value="7_days">Last 7 Days</option>
                        <option value="1_month">Last 30 Days</option>
                        <option value="last_month">Last Month</option>
                        <option value="custom">Custom Range (From - To)...</option>
                    </select>
                </div>
            </div>

            <!-- Second Filter Row: Sorting, VAT & Tax Controls, Product Type & Per Page -->
            <div class="row g-2 align-items-center mt-2 pt-2 border-top">
                <!-- Sorting Controller with VAT Sorting Options -->
                <div class="col-6 col-md-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text product-addon-text text-muted small"><i class="fas fa-sort me-1" style="font-size: 10px;"></i> Sort</span>
                        <select name="sort_by" id="filter-sort-by" class="form-select product-filter-control filter-trigger">
                            <option value="latest">Newest First (Desc)</option>
                            <option value="oldest">Oldest First (Asc)</option>
                            <option value="name_asc">Name: A to Z</option>
                            <option value="name_desc">Name: Z to A</option>
                            <option value="high_mrp">Price: High to Low</option>
                            <option value="low_mrp">Price: Low to High</option>
                            <option value="vat_high">VAT: High to Low</option>
                            <option value="vat_low">VAT: Low to High</option>
                        </select>
                    </div>
                </div>

                <!-- VAT / Tax Status Filter (With Tax vs Tax Free) -->
                <div class="col-6 col-md-2">
                    <select name="vat_status" id="filter-vat-status" class="form-select product-filter-control filter-trigger">
                        <option value="">VAT: All Products</option>
                        <option value="with_vat">With VAT / Tax</option>
                        <option value="without_vat">Without VAT (Zero/None)</option>
                    </select>
                </div>

                <!-- VAT Calculation Type Filter (% vs Flat) -->
                <div class="col-6 col-md-2">
                    <select name="vat_type" id="filter-vat-type" class="form-select product-filter-control filter-trigger">
                        <option value="">VAT Type: All</option>
                        <option value="percent">Percentage (%)</option>
                        <option value="flat">Flat Amount (৳)</option>
                    </select>
                </div>

                <!-- Product Type Filter -->
                <div class="col-6 col-md-2">
                    <select name="product_type" id="filter-type" class="form-select product-filter-control filter-trigger">
                        <option value="">All Types</option>
                        <option value="physical">Physical</option>
                        <option value="virtual">Virtual</option>
                        <option value="downloadable">Downloadable</option>
                    </select>
                </div>

                <!-- Per Page Controller -->
                <div class="col-6 col-md-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text product-addon-text text-muted small">Per Page</span>
                        <select name="per_page" id="filter-per-page" class="form-select product-filter-control filter-trigger fw-bold">
                            <option value="10">10 </option>
                            <option value="20" selected>20 </option>
                            <option value="50">50 </option>
                            <option value="100">100 </option>
                            <option value="all">Show All</option>
                        </select>
                    </div>
                </div>

                <!-- Reset Filters Button -->
                <div class="col-6 col-md-2 d-flex justify-content-md-end">
                    <button type="button" class="btn btn-light border btn-compact w-100 text-secondary shadow-xs" onclick="resetAllProductFilters()">
                        <i class="fas fa-rotate-left me-1"></i>
                        <span>Reset All</span>
                    </button>
                </div>
            </div>

            <!-- Third Row: Expandable Specific Range Panels (Price & VAT Rate) -->
            <div class="row g-2 align-items-center mt-2 pt-2 border-top">
                <div class="col-6 col-md-3">
                    <button type="button" class="btn btn-outline-secondary btn-compact w-100 d-flex justify-content-between align-items-center" onclick="togglePriceRangePanel()">
                        <span><i class="fas fa-money-bill-wave text-success me-1"></i> Filter by Price Range</span>
                        <i class="fas fa-chevron-down small" id="price-panel-chevron"></i>
                    </button>
                </div>

                <div class="col-6 col-md-3">
                    <button type="button" class="btn btn-outline-primary btn-compact w-100 d-flex justify-content-between align-items-center" onclick="toggleVatRangePanel()">
                        <span><i class="fas fa-percent text-primary me-1"></i> Filter by VAT Rate / Amount</span>
                        <i class="fas fa-chevron-down small" id="vat-panel-chevron"></i>
                    </button>
                </div>
            </div>

            <!-- Option A: Specific Single Date Panel -->
            <div id="singleDateWrapper" class="row g-2 align-items-center mt-2 pt-2 border-top d-none">
                <div class="col-12 col-sm-auto small fw-bold text-muted" style="font-size: 11.5px;">
                    <i class="fas fa-calendar-day text-primary me-1"></i> Specific Creation Date:
                </div>
                <div class="col-12 col-sm-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text product-addon-text text-muted small"><i class="fas fa-calendar-alt"></i></span>
                        <input type="date" name="single_date" id="filter-single-date" class="form-control product-filter-control">
                    </div>
                </div>
                <div class="col-12 col-sm-auto d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-compact" onclick="applySingleDateFilter()">
                        <i class="fas fa-check me-1"></i>
                        <span>Filter Date</span>
                    </button>
                    <button type="button" class="btn btn-light border btn-compact" onclick="resetDateFields()">
                        Reset
                    </button>
                </div>
            </div>

            <!-- Option B: Custom Date Range Panel (From - To) -->
            <div id="customDateRangeWrapper" class="row g-2 align-items-center mt-2 pt-2 border-top d-none">
                <div class="col-12 col-sm-auto small fw-bold text-muted" style="font-size: 11.5px;">
                    <i class="fas fa-calendar-check text-primary me-1"></i> Custom Creation Range:
                </div>
                <div class="col-6 col-sm-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text product-addon-text text-muted small">From</span>
                        <input type="date" name="start_date" id="filter-start-date" class="form-control product-filter-control">
                    </div>
                </div>
                <div class="col-6 col-sm-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text product-addon-text text-muted small">To</span>
                        <input type="date" name="end_date" id="filter-end-date" class="form-control product-filter-control">
                    </div>
                </div>
                <div class="col-12 col-sm-auto d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-compact" onclick="applyCustomDateFilter()">
                        <i class="fas fa-check me-1"></i>
                        <span>Filter Range</span>
                    </button>
                    <button type="button" class="btn btn-light border btn-compact" onclick="resetDateFields()">
                        Reset
                    </button>
                </div>
            </div>

            <!-- Option C: Price Range Filter Panel (Min MRP - Max MRP) -->
            <div id="priceRangeWrapper" class="row g-2 align-items-center mt-2 pt-2 border-top d-none">
                <div class="col-12 col-sm-auto small fw-bold text-muted" style="font-size: 11.5px;">
                    <i class="fas fa-tags text-success me-1"></i> Specific Price Range (MRP):
                </div>
                <div class="col-6 col-sm-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text product-addon-text text-muted small bn-digit">Min ৳</span>
                        <input type="number" step="0.01" name="min_price" id="filter-min-price" class="form-control product-filter-control bn-digit font-monospace" placeholder="0">
                    </div>
                </div>
                <div class="col-6 col-sm-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text product-addon-text text-muted small bn-digit">Max ৳</span>
                        <input type="number" step="0.01" name="max_price" id="filter-max-price" class="form-control product-filter-control bn-digit font-monospace" placeholder="10000">
                    </div>
                </div>
                <div class="col-12 col-sm-auto d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-compact" onclick="fetchFilteredProducts(1)">
                        <i class="fas fa-check me-1"></i>
                        <span>Apply Price</span>
                    </button>
                    <button type="button" class="btn btn-light border btn-compact" onclick="resetPriceFields()">
                        Reset
                    </button>
                </div>
            </div>

            <!-- Option D: Specific VAT Rate / Amount Range Panel (Min VAT - Max VAT) -->
            <div id="vatRangeWrapper" class="row g-2 align-items-center mt-2 pt-2 border-top d-none">
                <div class="col-12 col-sm-auto small fw-bold text-muted" style="font-size: 11.5px;">
                    <i class="fas fa-percent text-primary me-1"></i> Specific VAT Rate / Amount Range:
                </div>
                <div class="col-6 col-sm-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text product-addon-text text-muted small bn-digit">Min</span>
                        <input type="number" step="0.01" min="0" name="min_vat" id="filter-min-vat" class="form-control product-filter-control bn-digit font-monospace" placeholder="0">
                    </div>
                </div>
                <div class="col-6 col-sm-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text product-addon-text text-muted small bn-digit">Max</span>
                        <input type="number" step="0.01" min="0" name="max_vat" id="filter-max-vat" class="form-control product-filter-control bn-digit font-monospace" placeholder="100">
                    </div>
                </div>
                <div class="col-12 col-sm-auto d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-compact" onclick="fetchFilteredProducts(1)">
                        <i class="fas fa-check me-1"></i>
                        <span>Apply VAT Range</span>
                    </button>
                    <button type="button" class="btn btn-light border btn-compact" onclick="resetVatFields()">
                        Reset
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Bulk Action Bar -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-2 px-1 gap-2" id="product-bulk-action-bar">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <select id="bulk-action-selector" class="form-select product-filter-control" style="width: 180px;">
                <option value="">Bulk Actions</option>
                <option value="delete">Delete Permanently</option>
                <option value="mark_featured">Mark as Featured</option>
                <option value="unmark_featured">Unmark Featured</option>
                <option value="out_of_stock">Set Out of Stock</option>
            </select>
            <button type="button" class="btn btn-dark btn-compact shadow-xs" onclick="triggerBulkActionModal()">Apply</button>
            <span class="badge bg-light text-primary border bn-digit ms-1" id="selected-items-badge" style="display: none; font-size: 11px;">0 items selected</span>
        </div>
        <div class="small text-muted d-none d-lg-block" style="font-size: 11.5px;">
            <i class="fas fa-check-circle text-success me-1"></i> Screen optimized: 13 columns fit directly without horizontal scroll
        </div>
    </div>

    <!-- Table Container: Desktop 100% Fit Screen & Mobile Touch Scroll -->
    <div class="woo-table-container">
        <div class="pc-fit-container" id="draggable-scroll-wrapper">
            <table class="table align-middle woo-table" id="admin-products-table">
                <thead>
                    <tr>
                        <th class="col-checkbox text-center">
                            <input type="checkbox" class="form-check-input mt-0 shadow-none" id="check-all-products" style="cursor: pointer; width: 14px; height: 14px;">
                        </th>
                        <th class="col-id">#ID</th>
                        <th class="col-thumb text-center">Thumbnail</th>
                        <th class="col-title">Product Name</th>
                        <th class="col-sku">SKU</th>
                        <th class="col-category">Category</th>
                        <th class="col-brand">Brand</th>
                        <th class="col-stock">Stock</th>
                        <th class="col-price">Price</th>
                        <th class="col-purchase-price">Cost</th>
                        <th class="col-tax">VAT / Tax</th>
                        <th class="col-featured text-center">Featured</th>
                        <th class="col-action text-end pe-2">Action</th>
                    </tr>
                </thead>
                <tbody id="product-table-tbody">
                    @include('admin.product.partials.table_rows', ['products' => $products])
                </tbody>
            </table>
        </div>

        <!-- Live Bottom Highlight Strip for Catalog Stats -->
        <div class="product-summary-strip d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex flex-wrap align-items-center gap-2 gap-md-3">
                <span class="summary-badge-item text-dark">
                    <i class="fas fa-boxes text-primary"></i> Total Products: <strong id="summaryTotalProducts" class="text-primary bn-digit font-monospace ms-1">{{ $totalProductsCount ?? $products->total() }}</strong>
                </span>
                <span class="summary-badge-item text-dark border-start ps-2 ps-md-3">
                    <i class="fas fa-layer-group text-secondary"></i> In Stock Qty: <strong id="summaryTotalStock" class="text-dark bn-digit font-monospace ms-1">{{ $totalStockQty ?? 0 }} units</strong>
                </span>
                <span class="summary-badge-item text-success border-start ps-2 ps-md-3">
                    <i class="fas fa-wallet"></i> Total Retail Value: <strong id="summaryRetailValue" class="bn-digit font-monospace ms-1">৳{{ number_format($totalRetailValue ?? 0, 2) }}</strong>
                </span>
                <span class="summary-badge-item text-danger border-start ps-2 ps-md-3">
                    <i class="fas fa-coins"></i> Total Purchase Cost: <strong id="summaryPurchaseCost" class="bn-digit font-monospace ms-1">৳{{ number_format($totalPurchaseCost ?? 0, 2) }}</strong>
                </span>
            </div>
            <div>
                <small class="text-muted bn-digit font-monospace" id="showing-entries-text" style="font-size: 11px;">
                    Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} entries
                </small>
            </div>
        </div>
    </div>

    <!-- Responsive Pagination Footer -->
    <div class="mt-3 pt-2 border-top" id="product-pagination-footer" style="{{ $products->hasPages() ? '' : 'display: none;' }}">
        <div id="product-pagination-links" class="w-100 d-flex justify-content-center justify-content-md-end">
            @if($products->hasPages())
                {{ $products->links('pagination::bootstrap-5') }}
            @endif
        </div>
    </div>

</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="customConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div id="modal-icon-container" class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2 bg-danger-subtle text-danger" style="width: 44px; height: 44px;">
                    <i class="fas fa-trash-alt fs-5" id="modal-icon-graphic"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="modal-title-text" style="font-size: 14.5px;">Confirm Delete</h6>
                <p class="text-secondary small mb-3" id="modal-body-text" style="font-size: 12px; line-height: 1.45;">Are you sure you want to proceed?</p>
                
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger btn-compact px-3 shadow-xs" id="btn-modal-confirm-action">
                        Yes, Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Lightbox Zoom Modal for Product Images -->
<div class="modal fade product-modal-scope" id="productImageZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;" id="productZoomModalTitle">
                    <i class="fas fa-image text-primary me-1"></i> Product Thumbnail Preview
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="productZoomModalImgTag" src="" alt="Product Photo" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="custom-toast-holder"></div>

<!-- JavaScript Logic Engine -->
<script>
    let currentDeleteCallback = null;
    let searchTimeout = null;

    function openModalSafely(modalId) {
        const el = document.getElementById(modalId);
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalInstance = bootstrap.Modal.getOrCreateInstance(el);
            modalInstance.show();
        } else {
            el.classList.add('show');
            el.style.display = 'block';
        }
    }

    function closeModalSafely(modalId) {
        const el = document.getElementById(modalId);
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalInstance = bootstrap.Modal.getInstance(el);
            if (modalInstance) modalInstance.hide();
        } else {
            el.classList.remove('show');
            el.style.display = 'none';
        }
    }

    // 1. Live Filtering & AJAX Fetch (100% Reload-free)
    function fetchFilteredProducts(page = 1) {
        const form = document.getElementById('product-filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.append('page', page);

        const tbody = document.getElementById('product-table-tbody');
        tbody.style.opacity = '0.35';

        fetch("{{ route('admin.product.index') }}?" + params.toString(), {
            headers: { 
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            tbody.innerHTML = data.table_html;
            tbody.style.opacity = '1';

            // Pagination handling
            const paginationContainer = document.getElementById('product-pagination-footer');
            const paginationLinks = document.getElementById('product-pagination-links');
            const perPageVal = document.getElementById('filter-per-page').value;

            if (perPageVal === 'all' || !data.pagination_html || data.pagination_html.trim() === '') {
                paginationLinks.innerHTML = '';
                paginationContainer.style.display = 'none';
            } else {
                paginationLinks.innerHTML = data.pagination_html;
                paginationContainer.style.display = 'block';
            }

            document.getElementById('product-total-counter').innerText = data.total_count;

            if (data.entries_text) {
                document.getElementById('showing-entries-text').innerText = data.entries_text;
            }

            // Live bottom summary update
            if (data.summary) {
                document.getElementById('summaryTotalProducts').innerText = data.summary.total_products;
                document.getElementById('summaryTotalStock').innerText = data.summary.total_stock + ' units';
                document.getElementById('summaryRetailValue').innerText = '৳' + data.summary.retail_value;
                document.getElementById('summaryPurchaseCost').innerText = '৳' + data.summary.purchase_cost;
            }

            // Column visibility state
            document.querySelectorAll('.col-toggle-cb').forEach(cb => {
                if (!cb.checked) {
                    const targetClass = cb.getAttribute('data-target');
                    document.querySelectorAll('.' + targetClass).forEach(cell => cell.style.display = 'none');
                }
            });

            document.getElementById('check-all-products').checked = false;
            updateSelectedBadge();
        })
        .catch(err => {
            console.error('Fetch error:', err);
            tbody.style.opacity = '1';
        });
    }

    // 2. 1-Click EXCEL / PDF Export Generator
    function exportProductsData(type) {
        const form = document.getElementById('product-filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.append('export', type);

        const url = "{{ route('admin.product.index') }}?" + params.toString();
        window.open(url, '_blank');
    }

    // 3. Date Dropdown & Panel Handler
    document.getElementById('filter-date-select').addEventListener('change', function () {
        const val = this.value;
        const singleBox = document.getElementById('singleDateWrapper');
        const customBox = document.getElementById('customDateRangeWrapper');

        if (val === 'single_date') {
            singleBox.classList.remove('d-none');
            customBox.classList.add('d-none');
        } else if (val === 'custom') {
            customBox.classList.remove('d-none');
            singleBox.classList.add('d-none');
        } else {
            singleBox.classList.add('d-none');
            customBox.classList.add('d-none');
            fetchFilteredProducts(1);
        }
    });

    function applySingleDateFilter() {
        if (!document.getElementById('filter-single-date').value) {
            showNotificationToast('Required', 'Please choose a single date first.', 'warning');
            return;
        }
        fetchFilteredProducts(1);
    }

    function applyCustomDateFilter() {
        if (!document.getElementById('filter-start-date').value && !document.getElementById('filter-end-date').value) {
            showNotificationToast('Required', 'Please select at least a start or end date.', 'warning');
            return;
        }
        fetchFilteredProducts(1);
    }

    function resetDateFields() {
        document.getElementById('filter-single-date').value = '';
        document.getElementById('filter-start-date').value = '';
        document.getElementById('filter-end-date').value = '';
        document.getElementById('filter-date-select').value = '';
        document.getElementById('singleDateWrapper').classList.add('d-none');
        document.getElementById('customDateRangeWrapper').classList.add('d-none');
        fetchFilteredProducts(1);
    }

    // 4. Price Range Panel Toggle
    function togglePriceRangePanel() {
        const pBox = document.getElementById('priceRangeWrapper');
        const chevron = document.getElementById('price-panel-chevron');
        pBox.classList.toggle('d-none');
        if (pBox.classList.contains('d-none')) {
            chevron.className = 'fas fa-chevron-down small';
        } else {
            chevron.className = 'fas fa-chevron-up small';
        }
    }

    function resetPriceFields() {
        document.getElementById('filter-min-price').value = '';
        document.getElementById('filter-max-price').value = '';
        fetchFilteredProducts(1);
    }

    // 5. VAT Range Panel Toggle & Reset
    function toggleVatRangePanel() {
        const vBox = document.getElementById('vatRangeWrapper');
        const chevron = document.getElementById('vat-panel-chevron');
        vBox.classList.toggle('d-none');
        if (vBox.classList.contains('d-none')) {
            chevron.className = 'fas fa-chevron-down small';
        } else {
            chevron.className = 'fas fa-chevron-up small';
        }
    }

    function resetVatFields() {
        document.getElementById('filter-min-vat').value = '';
        document.getElementById('filter-max-vat').value = '';
        fetchFilteredProducts(1);
    }

    // 6. Reset All Filters
    function resetAllProductFilters() {
        document.getElementById('product-filter-form').reset();
        document.getElementById('singleDateWrapper').classList.add('d-none');
        document.getElementById('customDateRangeWrapper').classList.add('d-none');
        document.getElementById('priceRangeWrapper').classList.add('d-none');
        document.getElementById('vatRangeWrapper').classList.add('d-none');
        document.getElementById('price-panel-chevron').className = 'fas fa-chevron-down small';
        document.getElementById('vat-panel-chevron').className = 'fas fa-chevron-down small';
        fetchFilteredProducts(1);
    }

    // 7. Live Search & Trigger Listeners
    document.getElementById('filter-search-input').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => fetchFilteredProducts(1), 350);
    });

    document.querySelectorAll('.filter-trigger').forEach(el => {
        el.addEventListener('change', () => fetchFilteredProducts(1));
    });

    // 8. Pagination Interceptor
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#product-pagination-links .page-link');
        if (pageLink) {
            e.preventDefault();
            const href = pageLink.getAttribute('href');
            if (href && href !== '#' && !href.startsWith('javascript:')) {
                const url = new URL(href);
                const page = url.searchParams.get('page') || 1;
                fetchFilteredProducts(page);
                document.querySelector('.woo-table-container')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    });

    // 9. Delete Button Click Handler
    document.addEventListener('click', function(e) {
        const delBtn = e.target.closest('.btn-delete-product');
        if (delBtn) {
            e.preventDefault();
            const id = delBtn.dataset.id;
            const title = delBtn.dataset.title;

            document.getElementById('modal-title-text').innerText = 'Delete Product';
            document.getElementById('modal-body-text').innerHTML = `Are you sure you want to permanently delete "<strong>${title}</strong>"?`;
            document.getElementById('modal-icon-container').className = 'rounded-circle d-inline-flex align-items-center justify-content-center mb-2 bg-danger-subtle text-danger';
            document.getElementById('modal-icon-graphic').className = 'fas fa-trash-alt fs-5';
            document.getElementById('btn-modal-confirm-action').className = 'btn btn-danger btn-compact px-3 shadow-xs';

            currentDeleteCallback = function() {
                fetch("{{ url('admin/product/delete') }}/" + id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    closeModalSafely('customConfirmModal');
                    fetchFilteredProducts(1);
                    showNotificationToast('Deleted', 'Product deleted successfully', 'danger');
                })
                .catch(err => {
                    closeModalSafely('customConfirmModal');
                    fetchFilteredProducts(1);
                    showNotificationToast('Deleted', 'Product deleted successfully', 'danger');
                });
            };

            openModalSafely('customConfirmModal');
        }
    });

    document.getElementById('btn-modal-confirm-action').addEventListener('click', function() {
        if (typeof currentDeleteCallback === 'function') {
            currentDeleteCallback();
        }
    });

    // 10. Bulk Action Handler
    function triggerBulkActionModal() {
        const action = document.getElementById('bulk-action-selector').value;
        if (!action) {
            showNotificationToast('Warning', 'Please select an action from dropdown.', 'warning');
            return;
        }

        const selectedIds = Array.from(document.querySelectorAll('.product-select-checkbox:checked')).map(cb => cb.value);
        if (selectedIds.length === 0) {
            showNotificationToast('Warning', 'Please select at least one product.', 'warning');
            return;
        }

        let actionTitle = 'Confirm Bulk Action';
        let actionMsg = `Apply "${action}" to ${selectedIds.length} selected products?`;
        let btnColor = 'btn-primary';
        let iconClass = 'fas fa-cogs fs-5';
        let iconBg = 'bg-primary-subtle text-primary';

        if (action === 'delete') {
            actionTitle = 'Delete Selected Products';
            actionMsg = `Are you sure you want to permanently delete <strong>${selectedIds.length}</strong> selected products?`;
            btnColor = 'btn-danger';
            iconClass = 'fas fa-trash-alt fs-5';
            iconBg = 'bg-danger-subtle text-danger';
        }

        document.getElementById('modal-title-text').innerText = actionTitle;
        document.getElementById('modal-body-text').innerHTML = actionMsg;
        document.getElementById('modal-icon-container').className = `rounded-circle d-inline-flex align-items-center justify-content-center mb-2 ${iconBg}`;
        document.getElementById('modal-icon-graphic').className = iconClass;
        document.getElementById('btn-modal-confirm-action').className = `btn ${btnColor} btn-compact px-3 shadow-xs`;

        currentDeleteCallback = function() {
            fetch("{{ route('admin.product.bulk.action') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: action, ids: selectedIds })
            })
            .then(res => res.json())
            .then(data => {
                closeModalSafely('customConfirmModal');
                fetchFilteredProducts(1);
                showNotificationToast('Success', data.message, 'success');
            })
            .catch(err => {
                closeModalSafely('customConfirmModal');
                showNotificationToast('Error', 'Failed to execute bulk action.', 'danger');
            });
        };

        openModalSafely('customConfirmModal');
    }

    // 11. Selection Binding
    document.getElementById('check-all-products').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.product-select-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateSelectedBadge();
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('product-select-checkbox')) {
            updateSelectedBadge();
        }
    });

    function updateSelectedBadge() {
        const selected = document.querySelectorAll('.product-select-checkbox:checked').length;
        const badge = document.getElementById('selected-items-badge');
        if (selected > 0) {
            badge.innerText = `${selected} items selected`;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }

    // 12. Column Toggle Filter
    document.querySelectorAll('.col-toggle-cb').forEach(cb => {
        cb.addEventListener('change', function() {
            const targetClass = this.getAttribute('data-target');
            const cells = document.querySelectorAll('.' + targetClass);
            cells.forEach(cell => {
                cell.style.display = this.checked ? '' : 'none';
            });
        });
    });

    // 13. Featured Star Toggle
    function toggleFeaturedAjax(productId, btn) {
        fetch("{{ url('admin/product/toggle-featured') }}/" + productId, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.is_featured) {
                    btn.innerHTML = '<i class="fas fa-star fa-lg text-warning"></i>';
                    showNotificationToast('Success', 'Product added to Featured spotlight', 'success');
                } else {
                    btn.innerHTML = '<i class="far fa-star fa-lg text-secondary opacity-50"></i>';
                    showNotificationToast('Updated', 'Product removed from Featured', 'info');
                }
            }
        });
    }

    // 14. Global Media Chooser & Lightbox Helpers
    function zoomProductImage(url, title = 'Product Image') {
        const titleEl = document.getElementById('productZoomModalTitle');
        const imgTag  = document.getElementById('productZoomModalImgTag');
        if (url && imgTag) {
            if (titleEl) titleEl.innerText = title;
            imgTag.src = url;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('productImageZoomModal')).show();
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

    function chooseProductThumbnailMedia(productId) {
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
                            if (res.success) {
                                showNotificationToast('Updated', 'Product thumbnail updated from Media Library', 'success');
                                fetchFilteredProducts(1);
                            } else {
                                showNotificationToast('Notice', res.message || 'Image selected', 'info');
                            }
                        })
                        .catch(() => {
                            fetchFilteredProducts(1);
                        });
                    }
                }
            });
        } else {
            showNotificationToast('Notice', 'Global Media Library is loading, please try again.', 'warning');
        }
    }

    function showNotificationToast(title, message, type = 'success') {
        const holder = document.getElementById('custom-toast-holder');
        const toastId = 'toast-' + Date.now();

        const typeConfigs = {
            success: { icon: 'fa-check-circle', color: 'bg-success text-white' },
            danger:  { icon: 'fa-trash-alt',    color: 'bg-danger text-white' },
            warning: { icon: 'fa-exclamation-triangle', color: 'bg-warning text-dark' },
            info:    { icon: 'fa-info-circle',   color: 'bg-primary text-white' }
        };
        const config = typeConfigs[type] || typeConfigs.success;

        const html = `
            <div id="${toastId}" class="admin-toast-box ${config.color} p-3 d-flex align-items-center justify-content-between shadow-lg">
                <div class="d-flex align-items-center gap-3">
                    <i class="fas ${config.icon} fa-lg"></i>
                    <div>
                        <strong class="d-block" style="font-size: 13px;">${title}</strong>
                        <small style="opacity: 0.95; font-size: 11.5px;">${message}</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-3" onclick="document.getElementById('${toastId}').remove()"></button>
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