@extends('layouts.admin')

@section('title', 'All Courier Orders - ' . config('app.name'))

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #courier-orders-root .btn,
    #courier-orders-root .btn-sm,
    #courier-orders-root button,
    #courier-orders-root .form-control,
    #courier-orders-root .form-select,
    #courier-orders-root .badge,
    #courier-orders-root .input-group-text,
    #courier-orders-root .courier-table-card,
    #courier-orders-root .stat-mini-pill,
    #courier-orders-root .bulk-action-bar,
    #courier-orders-root .proof-thumb-box,
    #courier-orders-root .proof-media-surface,
    .courier-modal-scope .modal-content,
    .courier-modal-scope .btn,
    .courier-modal-scope .form-control,
    .courier-modal-scope .form-select,
    .courier-modal-scope .input-group-text,
    .courier-modal-scope .badge,
    .courier-modal-scope .alert {
        border-radius: 5px !important;
    }

    #courier-orders-root .input-group > .input-group-text,
    .courier-modal-scope .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #courier-orders-root .input-group > .form-control,
    .courier-modal-scope .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Safe Bengali Digits & Monospace Formatting */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Compact Button Styling */
    .btn-compact {
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

    /* Main Container Cards */
    .courier-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }

    /* Stat Mini Cards - Safe Layout Without Icon Overflow */
    .stat-mini-pill {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 10px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
        height: 100%;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
        transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s ease;
        overflow: hidden;
        position: relative;
    }
    .stat-mini-pill:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
    }
    .stat-pill-blue   { background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%); border-color: #bae6fd; }
    .stat-pill-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-color: #bbf7d0; }
    .stat-pill-red    { background: linear-gradient(180deg, #ffffff 0%, #fef2f2 100%); border-color: #fecaca; }
    .stat-pill-slate  { background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); border-color: #e2e8f0; }

    .stat-icon-box {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }

    /* Filter Form Controls */
    #courier-orders-root .form-control,
    #courier-orders-root .form-select {
        min-height: 32px;
        height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        color: #0f172a;
        box-shadow: none;
        padding: 4px 8px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #courier-orders-root .form-control:focus,
    #courier-orders-root .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .custom-courier-table {
        width: 100%;
        margin-bottom: 0;
    }
    .custom-courier-table th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 4px;
        white-space: nowrap;
    }
    .custom-courier-table td {
        padding: 7px 4px;
        vertical-align: middle;
        font-size: 11px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .custom-courier-table tbody tr:hover {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .custom-courier-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .custom-courier-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 12 Precise Proportional Columns to Guarantee 100% Fit Screen */
        .col-w-chk    { width: 28px !important; }
        .col-w-id     { width: 36px !important; }
        .col-w-inv    { width: 115px !important; }
        .col-w-cust   { width: 145px !important; }
        .col-w-prov   { width: 95px !important; }
        .col-w-trk    { width: 135px !important; }
        .col-w-wt     { width: 50px !important; }
        .col-w-cod    { width: 85px !important; }
        .col-w-set    { width: 80px !important; }
        .col-w-dst    { width: 92px !important; }
        .col-w-cst    { width: 80px !important; }
        .col-w-act    { width: 98px !important; }
    }

    /* Soft Enterprise Badges */
    .badge-soft-success { background: #dcfce7 !important; color: #15803d !important; border: 1px solid #bbf7d0 !important; }
    .badge-soft-warning { background: #fef9c3 !important; color: #854d0e !important; border: 1px solid #fef08a !important; }
    .badge-soft-danger  { background: #fee2e2 !important; color: #b91c1c !important; border: 1px solid #fecaca !important; }
    .badge-soft-info    { background: #e0f2fe !important; color: #0369a1 !important; border: 1px solid #bae6fd !important; }

    /* Bulk Action Bar (Hidden Until Checkboxes Are Selected) */
    .bulk-action-bar {
        background: #0f172a;
        color: #ffffff;
        padding: 8px 14px;
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.16);
    }

    /* Global Media Chooser Component Styles */
    .proof-media-surface {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        padding: 10px;
        transition: all 0.2s ease;
    }
    .proof-media-surface:hover {
        border-color: #2563eb;
        background: #f1f5f9;
    }
    .proof-thumb-box {
        width: 22px;
        height: 22px;
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
        background: rgba(15, 23, 42, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.15s ease;
    }
    .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    /* Bulletproof Responsive Pagination */
    #courierPaginationWrapper {
        display: flex;
        align-items: center;
    }
    #courierPaginationWrapper nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }
    #courierPaginationWrapper nav > div.d-sm-none,
    #courierPaginationWrapper nav > .d-sm-none {
        display: none !important;
    }
    #courierPaginationWrapper nav > div.d-none,
    #courierPaginationWrapper nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    #courierPaginationWrapper nav p,
    #courierPaginationWrapper nav .small.text-muted,
    #courierPaginationWrapper nav div:has(> p),
    #courierPaginationWrapper nav > div.d-none > div:first-child,
    #courierPaginationWrapper nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }
    #courierPaginationWrapper .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 3px !important;
        padding: 0 !important;
        list-style: none !important;
    }
    #courierPaginationWrapper .page-item {
        display: inline-flex !important;
    }
    #courierPaginationWrapper .page-item .page-link,
    #courierPaginationWrapper .page-item span {
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
    #courierPaginationWrapper .page-item .page-link:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }
    #courierPaginationWrapper .page-item.active .page-link,
    #courierPaginationWrapper .page-item.active span {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25) !important;
    }
    #courierPaginationWrapper .page-item.disabled .page-link,
    #courierPaginationWrapper .page-item.disabled span {
        opacity: 0.45 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }
    #courierPaginationWrapper nav svg {
        width: 10px !important;
        height: 10px !important;
    }

    /* Mobile Responsive Optimizations (< 768px) */
    @media (max-width: 767.98px) {
        .stat-mini-pill {
            padding: 8px 10px !important;
            gap: 6px !important;
        }
        .stat-icon-box {
            width: 28px !important;
            height: 28px !important;
            font-size: 11px !important;
        }
        .top-action-bar-wrap {
            width: 100% !important;
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 6px !important;
        }
        .btn-new-booking {
            grid-column: span 2 !important;
        }
        .bulk-action-bar {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 8px !important;
        }
        .bulk-action-bar .ms-auto {
            margin-left: 0 !important;
            width: 100% !important;
            display: grid !important;
            grid-template-columns: 1fr auto !important;
            gap: 6px !important;
        }
        .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .custom-courier-table {
            min-width: 860px !important;
            table-layout: auto !important;
        }
        #courierPaginationWrapper {
            width: 100% !important;
            justify-content: center !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            padding: 4px 0 !important;
        }
        #courierPaginationWrapper nav {
            justify-content: center !important;
            width: 100% !important;
        }
        #courierPaginationWrapper .pagination {
            flex-wrap: nowrap !important;
            gap: 3px !important;
        }
        #courierPaginationWrapper .page-item .page-link,
        #courierPaginationWrapper .page-item span {
            min-width: 24px !important;
            height: 24px !important;
            font-size: 10.5px !important;
            padding: 0 5px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="courier-orders-root">
    <!-- Header & Action Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-boxes-packing text-primary"></i>
                <span>Courier Consignments &amp; Orders</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Real-time parcel ledger, live API tracking, Global Media proof attachments &amp; COD settlement.
            </small>
        </div>
        <div class="top-action-bar-wrap d-flex flex-wrap gap-2">
            <a href="{{ route('admin.courier.booking') }}" class="btn btn-primary btn-compact shadow-xs btn-new-booking">
                <i class="fas fa-plus me-1" style="font-size: 11px;"></i>
                <span>New Booking</span>
            </a>
            <button type="button" class="btn btn-outline-success btn-compact bg-white shadow-xs" onclick="exportCourierLedger('excel')">
                <i class="fas fa-file-excel text-success me-1" style="font-size: 11px;"></i>
                <span>Export Excel</span>
            </button>
            <button type="button" class="btn btn-outline-danger btn-compact bg-white shadow-xs" onclick="exportCourierLedger('pdf')">
                <i class="fas fa-file-pdf text-danger me-1" style="font-size: 11px;"></i>
                <span>Export PDF</span>
            </button>
        </div>
    </div>

    <!-- 4 Financial Audit KPI Pills -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-lg-3">
            <div class="stat-mini-pill stat-pill-blue">
                <div class="min-w-0 pe-1">
                    <small class="text-muted d-block fw-semibold text-truncate mb-1" style="font-size: 10.5px;">Total Collectible COD</small>
                    <h5 class="fw-bold mb-0 text-dark font-monospace bn-digit" style="font-size: 15px;">৳<span id="summaryTotalCod">{{ number_format($totalCodSum, 2) }}</span></h5>
                </div>
                <div class="stat-icon-box bg-primary text-white shadow-xs">
                    <i class="fas fa-hand-holding-dollar"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-mini-pill stat-pill-green">
                <div class="min-w-0 pe-1">
                    <small class="text-muted d-block fw-semibold text-truncate mb-1" style="font-size: 10.5px;">Received COD</small>
                    <h5 class="fw-bold mb-0 text-success font-monospace bn-digit" style="font-size: 15px;">৳<span id="summaryTotalSettled">{{ number_format($totalSettledSum, 2) }}</span></h5>
                </div>
                <div class="stat-icon-box bg-success text-white shadow-xs">
                    <i class="fas fa-circle-check"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-mini-pill stat-pill-red">
                <div class="min-w-0 pe-1">
                    <small class="text-muted d-block fw-semibold text-truncate mb-1" style="font-size: 10.5px;">COD Settlement Due</small>
                    <h5 class="fw-bold mb-0 text-danger font-monospace bn-digit" style="font-size: 15px;">৳<span id="summaryDueCod">{{ number_format($totalDueCodSum, 2) }}</span></h5>
                </div>
                <div class="stat-icon-box bg-danger text-white shadow-xs">
                    <i class="fas fa-clock-rotate-left"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-mini-pill stat-pill-slate">
                <div class="min-w-0 pe-1">
                    <small class="text-muted d-block fw-semibold text-truncate mb-1" style="font-size: 10.5px;">Total Delivery Cost</small>
                    <h5 class="fw-bold mb-0 text-secondary font-monospace bn-digit" style="font-size: 15px;">৳<span id="summaryTotalCost">{{ number_format($totalCostSum, 2) }}</span></h5>
                </div>
                <div class="stat-icon-box bg-secondary text-white shadow-xs">
                    <i class="fas fa-truck-ramp-box"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Multi-Filter Toolbar Card -->
    <div class="courier-table-card p-3 mb-3">
        <form id="courierFilterForm" onsubmit="return false;">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                        <input type="text" name="search" id="courierFilterSearch" class="form-control border-start-0 ps-0 shadow-none" 
                               placeholder="Invoice, Tracking, Phone, Name...">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <select name="courier" id="courierFilterProvider" class="form-select form-select-sm shadow-none">
                        <option value="all">All Couriers</option>
                        @foreach($couriersList as $cName)
                            <option value="{{ $cName }}">{{ $cName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="status" id="courierFilterStatus" class="form-select form-select-sm shadow-none">
                        <option value="all">Delivery: All</option>
                        <option value="booked">Booked (In Review)</option>
                        <option value="in_transit">In Transit</option>
                        <option value="delivered">Delivered</option>
                        <option value="returned">Returned</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="cod_status" id="courierFilterCod" class="form-select form-select-sm shadow-none">
                        <option value="all">COD: All</option>
                        <option value="unsettled">Unsettled (Due)</option>
                        <option value="partial">Partially Settled</option>
                        <option value="settled">Fully Settled</option>
                    </select>
                </div>

                <div class="col-6 col-md-1">
                    <select name="cost_status" id="courierFilterCostStatus" class="form-select form-select-sm shadow-none">
                        <option value="all">Cost: All</option>
                        <option value="due">Cost Due</option>
                        <option value="paid">Cost Paid</option>
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <select name="date_filter" id="courierFilterDate" class="form-select form-select-sm shadow-none">
                        <option value="">All Time</option>
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="7_days">Last 7 Days</option>
                        <option value="15_days">Last 15 Days</option>
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="custom">Custom Range...</option>
                    </select>
                </div>
            </div>

            <!-- Custom Date Row & Sorting Tools -->
            <div class="row g-2 align-items-center mt-2 pt-2 border-top">
                <div class="col-12 col-md-7 d-flex align-items-center gap-2 flex-wrap">
                    <div class="d-flex align-items-center gap-1 flex-grow-1 flex-sm-grow-0">
                        <span class="small fw-bold text-muted" style="font-size: 11px;">Sort:</span>
                        <select name="sort_by" id="courierSortBy" class="form-select form-select-sm shadow-none" style="min-width: 120px;">
                            <option value="latest">Latest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="cod_high">Highest COD</option>
                            <option value="cod_low">Lowest COD</option>
                            <option value="weight_high">Heavy Weight</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-1 flex-grow-1 flex-sm-grow-0">
                        <span class="small fw-bold text-muted" style="font-size: 11px;">Channel:</span>
                        <select name="order_source" id="courierOrderSource" class="form-select form-select-sm shadow-none" style="min-width: 110px;">
                            <option value="all">All Channels</option>
                            <option value="online">Online</option>
                            <option value="pos">POS</option>
                        </select>
                    </div>

                    <button type="button" class="btn btn-light btn-compact border px-3 text-secondary ms-auto shadow-xs" onclick="resetAllFilters()">
                        <i class="fas fa-rotate-left me-1"></i>
                        <span>Reset</span>
                    </button>
                </div>

                <div class="col-12 col-md-5 d-flex align-items-center justify-content-md-end gap-2 flex-wrap {{ request('date_filter') == 'custom' ? '' : 'd-none' }}" id="customDateRow">
                    <span class="small fw-bold text-muted" style="font-size: 11px;">From:</span>
                    <input type="date" name="start_date" id="courierStartDate" class="form-control form-control-sm shadow-none bn-digit font-monospace" style="width: 125px;">
                    <span class="small fw-bold text-muted" style="font-size: 11px;">To:</span>
                    <input type="date" name="end_date" id="courierEndDate" class="form-control form-control-sm shadow-none bn-digit font-monospace" style="width: 125px;">
                    <button type="button" class="btn btn-primary btn-compact shadow-xs" onclick="fetchCourierOrders()">Apply</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Bulk Action Toolbar (Appears Only When Rows Are Checked) -->
    <div class="bulk-action-bar shadow-xs" id="courierBulkActionBar" style="display: none;">
        <div class="d-flex align-items-center gap-2">
            <span class="small fw-bold text-white d-flex align-items-center" style="font-size: 12px;">
                <i class="fas fa-check-double text-warning me-2"></i>
                <span>Bulk Action:</span>
            </span>
            <span class="badge bg-warning text-dark px-2 py-1 bn-digit font-monospace" id="selectedCountBadge" style="font-size: 11px;">0 selected</span>
        </div>
        <div class="d-flex align-items-center gap-2 ms-auto flex-wrap">
            <select id="bulkActionType" class="form-select form-select-sm shadow-none bg-white text-dark" style="min-width: 180px;">
                <option value="">-- Choose Action --</option>
                <optgroup label="Danger Zone">
                    <option value="delete">Delete Selected Records</option>
                </optgroup>
                <optgroup label="Update Status">
                    <option value="status_in_transit">Mark as In Transit</option>
                    <option value="status_delivered">Mark as Delivered</option>
                    <option value="status_returned">Mark as Returned</option>
                    <option value="status_cancelled">Mark as Cancelled</option>
                </optgroup>
            </select>
            <button type="button" class="btn btn-primary btn-compact px-3 shadow-xs" onclick="handleBulkActionSubmit()">
                Apply Action
            </button>
            <button type="button" class="btn btn-outline-light btn-compact" onclick="clearAllBulkSelections()">
                Cancel
            </button>
        </div>
    </div>

    <!-- Main Data Table Card (100% Fit Screen on PC with Zero Horizontal Scrolling) -->
    <div class="courier-table-card overflow-hidden">
        <div class="table-responsive pc-fit-container">
            <table class="table custom-courier-table align-middle mb-0" id="courier-orders-table">
                <thead>
                    <tr>
                        <th class="text-center col-w-chk">
                            <input type="checkbox" id="selectAllBookings" class="form-check-input mt-0 shadow-none" onchange="toggleSelectAllBookings(this)" title="Select All" style="cursor: pointer; width: 14px; height: 14px; border-radius: 4px;">
                        </th>
                        <th class="col-w-id">#</th>
                        <th class="col-w-inv">INVOICE &amp; SOURCE</th>
                        <th class="col-w-cust">RECIPIENT INFO</th>
                        <th class="col-w-prov">COURIER</th>
                        <th class="col-w-trk">TRACKING &amp; PROOF</th>
                        <th class="text-center col-w-wt">WEIGHT</th>
                        <th class="text-end col-w-cod">COD AMT</th>
                        <th class="text-end col-w-set">SETTLED</th>
                        <th class="text-center col-w-dst">DELIVERY</th>
                        <th class="text-center col-w-cst">COD STATUS</th>
                        <th class="text-end pe-2 col-w-act">ACTION</th>
                    </tr>
                </thead>
                <tbody id="courier-table-tbody">
                    @include('admin.courier.partials.courier_order_rows', ['bookings' => $bookings])
                </tbody>
            </table>
        </div>

        <!-- Pagination & Footer (Exact Transaction Standard) -->
        <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50">
            <small class="text-muted bn-digit font-monospace" id="paginationEntryCount" style="font-size: 11px;">
                Showing {{ $bookings->firstItem() ?? 0 }} to {{ $bookings->lastItem() ?? 0 }} of {{ $bookings->total() }} entries
            </small>
            <div id="courierPaginationWrapper">
                {{ $bookings->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Alert Modal -->
<div class="modal fade courier-modal-scope" id="courierAlertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2" id="alertModalIcon">
                    <i class="fas fa-circle-check text-success fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="alertModalTitle" style="font-size: 14px;">Notice</h6>
                <p class="text-muted small mb-3" id="alertModalMessage" style="font-size: 12px;">Action completed.</p>
                <button type="button" class="btn btn-primary btn-compact px-4" data-bs-dismiss="modal">Acknowledge</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Manual Courier Status Update Modal (with Global Media Chooser Integration) -->
<div class="modal fade courier-modal-scope" id="manualStatusModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow p-3">
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13.5px;">
                    <i class="fas fa-truck-moving text-primary"></i>
                    <span>Update Status, Cost &amp; POD Slip</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            
            <input type="hidden" id="modal_manual_booking_id">
            <input type="hidden" id="modal_manual_consignment_slip" value="">

            <div class="p-2 bg-light border mb-3 small" style="border-radius: 5px;">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Order Invoice:</span>
                    <strong class="font-monospace text-primary bn-digit" id="modal_manual_invoice">#000</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Tracking Code:</span>
                    <strong class="font-monospace text-dark bn-digit" id="modal_manual_tracking">TRK-000</strong>
                </div>
            </div>

            <div class="mb-2">
                <label class="form-label small fw-semibold text-dark mb-1" style="font-size: 11.5px;">Delivery Status *</label>
                <select id="modal_manual_status_select" class="form-select form-select-sm fw-semibold shadow-none">
                    <option value="in_transit">In Transit / Shipped</option>
                    <option value="delivered">Delivered (Mark as Delivered &amp; Paid)</option>
                    <option value="returned">Returned / Return Requested</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="form-label small fw-semibold text-dark mb-1" style="font-size: 11.5px;">Courier Cost (৳) *</label>
                    <input type="number" step="1" min="0" id="modal_manual_cost_input" class="form-control form-control-sm font-monospace fw-bold bn-digit shadow-none" placeholder="120">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-semibold text-dark mb-1" style="font-size: 11.5px;">Cost Paid (৳)</label>
                    <input type="number" step="1" min="0" id="modal_manual_cost_paid_input" class="form-control form-control-sm font-monospace bn-digit shadow-none" value="0">
                </div>
            </div>

            <!-- Global Media Chooser Integration: Attach Consignment Voucher / Delivery Slip -->
            <div class="mb-3">
                <label class="form-label small fw-semibold text-dark mb-1 d-flex justify-content-between align-items-center" style="font-size: 11.5px;">
                    <span><i class="fas fa-paperclip text-primary me-1"></i> Delivery Proof / POD Slip</span>
                    <span class="badge bg-light text-muted border font-monospace" style="font-size: 9px;">MEDIA LIBRARY</span>
                </label>
                <div class="proof-media-surface">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-primary btn-compact shadow-xs" onclick="chooseCourierConsignmentSlipMedia()">
                            <i class="fas fa-photo-film me-1"></i>
                            <span>Choose from Media Library</span>
                        </button>
                    </div>

                    <div id="manual_pod_preview_wrap" class="mt-2 d-none align-items-center gap-2 bg-white border p-1" style="border-radius: 5px;">
                        <div class="proof-thumb-box" onclick="zoomConsignmentProof(document.getElementById('manual_pod_preview_img').src, 'Delivery Proof Slip')">
                            <img id="manual_pod_preview_img" src="" alt="Proof Preview">
                            <div class="proof-thumb-hover-overlay">
                                <i class="fas fa-search-plus text-white" style="font-size: 10px;"></i>
                            </div>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 9px;">
                                <i class="fas fa-check-circle me-1"></i>Slip Attached
                            </span>
                            <small class="text-muted d-block text-truncate font-monospace" id="manual_pod_preview_txt" style="font-size: 9.5px;"></small>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-auto text-decoration-none fw-bold" style="font-size: 11px;" onclick="clearManualConsignmentSlip()">&times; Clear</button>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-compact shadow-xs" id="btnConfirmManualStatus" onclick="submitManualStatusUpdate()">
                    Save &amp; Sync
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 3: Confirmation Modal for Delete -->
<div class="modal fade courier-modal-scope" id="courierConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2">
                    <i class="fas fa-trash-can text-danger fa-2x"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="confirmModalTitle" style="font-size: 14px;">Delete Confirmation</h6>
                <p class="text-muted small mb-2" id="confirmModalMessage" style="font-size: 12px;">Are you sure you want to delete this courier booking?</p>
                <div class="alert alert-info py-2 px-2 mb-3 text-start small border-0 bg-info-subtle text-info" style="font-size: 11px; border-radius: 5px;">
                    <i class="fas fa-shield-halved me-1"></i> <strong>Safe Delete:</strong> The original customer order in Order Management will <strong>NOT</strong> be affected!
                </div>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-compact" id="btnExecuteConfirmAction">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 4: Lightbox Zoom Modal for Consignment Slips & Media Proof -->
<div class="modal fade courier-modal-scope" id="courierProofZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 1080;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;" id="courierProofZoomTitle">
                    <i class="fas fa-image text-primary me-1"></i> Consignment Slip Proof
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="courierProofZoomImgTag" src="" alt="Proof Preview" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<script>
    let filterDebounce = null;
    let pendingConfirmCallback = null;

    function showCourierModalAlert(message, type = 'info', title = 'Notice') {
        const titleEl = document.getElementById('alertModalTitle');
        const msgEl = document.getElementById('alertModalMessage');
        const iconEl = document.getElementById('alertModalIcon');

        titleEl.innerText = title;
        msgEl.innerText = message;

        if (type === 'danger' || type === 'error') {
            iconEl.innerHTML = '<i class="fas fa-circle-xmark text-danger fa-2x"></i>';
        } else if (type === 'success') {
            iconEl.innerHTML = '<i class="fas fa-circle-check text-success fa-2x"></i>';
        } else if (type === 'warning') {
            iconEl.innerHTML = '<i class="fas fa-triangle-exclamation text-warning fa-2x"></i>';
        } else {
            iconEl.innerHTML = '<i class="fas fa-circle-info text-primary fa-2x"></i>';
        }

        const modal = new bootstrap.Modal(document.getElementById('courierAlertModal'));
        modal.show();
    }

    function showCourierConfirmModal(title, message, callback) {
        document.getElementById('confirmModalTitle').innerText = title;
        document.getElementById('confirmModalMessage').innerText = message;
        pendingConfirmCallback = callback;

        const modal = new bootstrap.Modal(document.getElementById('courierConfirmModal'));
        modal.show();
    }

    document.getElementById('btnExecuteConfirmAction').addEventListener('click', function() {
        const modalEl = document.getElementById('courierConfirmModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        if (typeof pendingConfirmCallback === 'function') {
            pendingConfirmCallback();
            pendingConfirmCallback = null;
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById('courierFilterSearch');
        const providerSelect = document.getElementById('courierFilterProvider');
        const statusSelect = document.getElementById('courierFilterStatus');
        const codSelect = document.getElementById('courierFilterCod');
        const costSelect = document.getElementById('courierFilterCostStatus');
        const sortSelect = document.getElementById('courierSortBy');
        const sourceSelect = document.getElementById('courierOrderSource');
        const dateSelect = document.getElementById('courierFilterDate');

        searchInput.addEventListener('input', () => {
            clearTimeout(filterDebounce);
            filterDebounce = setTimeout(fetchCourierOrders, 250);
        });

        [providerSelect, statusSelect, codSelect, costSelect, sortSelect, sourceSelect].forEach(el => {
            el.addEventListener('change', fetchCourierOrders);
        });

        dateSelect.addEventListener('change', function () {
            document.getElementById('customDateRow').classList.toggle('d-none', this.value !== 'custom');
            if (this.value !== 'custom') {
                fetchCourierOrders();
            }
        });

        document.addEventListener('click', function(e) {
            const pageLink = e.target.closest('#courierPaginationWrapper a');
            if (pageLink) {
                e.preventDefault();
                const href = pageLink.getAttribute('href');
                if (href && href !== '#') {
                    fetchCourierOrders(href);
                }
            }
        });

        updateSelectedBookingsCount();
    });

    function fetchCourierOrders(targetUrl = null) {
        const form = document.getElementById('courierFilterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        const url = targetUrl || ("{{ route('admin.courier.index') }}?" + params.toString());

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('courier-table-tbody').innerHTML = data.html;
                document.getElementById('courierPaginationWrapper').innerHTML = data.pagination_html;
                
                document.getElementById('summaryTotalCod').innerText = data.summary.total_cod;
                document.getElementById('summaryTotalCost').innerText = data.summary.total_cost;
                document.getElementById('summaryTotalSettled').innerText = data.summary.total_settled;
                document.getElementById('summaryDueCod').innerText = data.summary.due_cod;

                document.getElementById('selectAllBookings').checked = false;
                updateSelectedBookingsCount();
            }
        })
        .catch(err => console.error("Filter error:", err));
    }

    function resetAllFilters() {
        document.getElementById('courierFilterForm').reset();
        document.getElementById('customDateRow').classList.add('d-none');
        fetchCourierOrders();
    }

    function toggleSelectAllBookings(master) {
        const checkboxes = document.querySelectorAll('.booking-row-checkbox');
        checkboxes.forEach(cb => cb.checked = master.checked);
        updateSelectedBookingsCount();
    }

    function onRowCheckboxChange() {
        const checkboxes = document.querySelectorAll('.booking-row-checkbox');
        const allChecked = checkboxes.length > 0 && Array.from(checkboxes).every(cb => cb.checked);
        const selectAllEl = document.getElementById('selectAllBookings');
        if (selectAllEl) selectAllEl.checked = allChecked;
        updateSelectedBookingsCount();
    }

    function updateSelectedBookingsCount() {
        const selected = document.querySelectorAll('.booking-row-checkbox:checked').length;
        const badge = document.getElementById('selectedCountBadge');
        const bulkBar = document.getElementById('courierBulkActionBar');
        if (badge) badge.innerText = selected + ' selected';
        if (bulkBar) {
            bulkBar.style.display = selected > 0 ? 'flex' : 'none';
        }
    }

    function clearAllBulkSelections() {
        const master = document.getElementById('selectAllBookings');
        if (master) master.checked = false;
        document.querySelectorAll('.booking-row-checkbox').forEach(cb => cb.checked = false);
        updateSelectedBookingsCount();
    }

    function getSelectedBookingIds() {
        return Array.from(document.querySelectorAll('.booking-row-checkbox:checked')).map(cb => cb.value);
    }

    function handleBulkActionSubmit() {
        const selectedIds = getSelectedBookingIds();
        const actionType = document.getElementById('bulkActionType').value;

        if (selectedIds.length === 0) {
            showCourierModalAlert('Please select at least one courier consignment row.', 'warning', 'No Items Selected');
            return;
        }

        if (!actionType) {
            showCourierModalAlert('Please select a bulk action from the dropdown.', 'warning', 'Action Required');
            return;
        }

        if (actionType === 'delete') {
            showCourierConfirmModal(
                'Delete ' + selectedIds.length + ' Courier Records?',
                'Are you sure you want to permanently delete ' + selectedIds.length + ' courier consignments? Original customer orders will NOT be affected.',
                () => executeBulkActionApi(selectedIds, actionType)
            );
        } else {
            executeBulkActionApi(selectedIds, actionType);
        }
    }

    function executeBulkActionApi(bookingIds, actionType) {
        fetch("{{ route('admin.courier.bulk.action') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                booking_ids: bookingIds,
                action_type: actionType
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showCourierModalAlert(data.message, 'success', 'Completed');
                clearAllBulkSelections();
                fetchCourierOrders();
            } else {
                showCourierModalAlert(data.message || 'Action failed.', 'danger', 'Error');
            }
        })
        .catch(err => {
            console.error(err);
            showCourierModalAlert('Server communication error.', 'danger', 'Error');
        });
    }

    function confirmDeleteCourierBooking(bookingId, invoiceNo) {
        showCourierConfirmModal(
            'Delete Courier Booking #' + bookingId + '?',
            'Are you sure you want to remove courier record for Invoice #' + invoiceNo + '? Note: The order itself remains completely safe and untouched.',
            () => {
                executeBulkActionApi([bookingId], 'delete');
            }
        );
    }

    function syncLiveCourierStatus(bookingId) {
        const btn = document.getElementById('btn-sync-' + bookingId);
        if (btn) btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch("{{ url('admin/courier-management/sync-status') }}/" + bookingId, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            showCourierModalAlert(data.message, data.success ? 'success' : 'info', 'Courier Tracking Status');
            fetchCourierOrders();
        })
        .catch(err => {
            console.error(err);
            showCourierModalAlert('Failed to communicate with courier gateway server.', 'danger', 'Sync Error');
        })
        .finally(() => {
            if (btn) btn.innerHTML = '<i class="fas fa-rotate"></i>';
        });
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

    function chooseCourierConsignmentSlipMedia() {
        if (typeof window.openMediaPicker === 'function') {
            window.openMediaPicker({
                multiple: false,
                onSelect: function(media) {
                    const picked = extractMediaItem(media);
                    if (picked && picked.path) {
                        document.getElementById('modal_manual_consignment_slip').value = picked.path;
                        const wrap = document.getElementById('manual_pod_preview_wrap');
                        const img  = document.getElementById('manual_pod_preview_img');
                        const txt  = document.getElementById('manual_pod_preview_txt');
                        if (img) img.src = picked.url;
                        if (txt) txt.innerText = picked.path.split('/').pop() || picked.path;
                        if (wrap) {
                            wrap.classList.remove('d-none');
                            wrap.classList.add('d-flex');
                        }
                    }
                }
            });
        } else {
            showCourierModalAlert('Global Media Library is loading, please try again.', 'warning', 'Notice');
        }
    }

    function clearManualConsignmentSlip() {
        document.getElementById('modal_manual_consignment_slip').value = '';
        const wrap = document.getElementById('manual_pod_preview_wrap');
        if (wrap) {
            wrap.classList.add('d-none');
            wrap.classList.remove('d-flex');
        }
    }

    function zoomConsignmentProof(url, title = 'Consignment Slip Proof') {
        const titleEl = document.getElementById('courierProofZoomTitle');
        const imgTag  = document.getElementById('courierProofZoomImgTag');
        if (url && imgTag) {
            if (titleEl) titleEl.innerText = title;
            imgTag.src = url;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('courierProofZoomModal')).show();
        }
    }

    function openManualStatusModal(bookingId, currentStatus, trackingCode, invoiceNo, currentCost = 0, currentCostPaid = 0, currentSlip = '') {
        document.getElementById('modal_manual_booking_id').value = bookingId;
        document.getElementById('modal_manual_invoice').innerText = '#' + invoiceNo;
        document.getElementById('modal_manual_tracking').innerText = trackingCode;
        document.getElementById('modal_manual_status_select').value = currentStatus || 'in_transit';
        document.getElementById('modal_manual_cost_input').value = currentCost > 0 ? currentCost : 120;
        document.getElementById('modal_manual_cost_paid_input').value = currentCostPaid || 0;
        
        clearManualConsignmentSlip();
        if (currentSlip) {
            document.getElementById('modal_manual_consignment_slip').value = currentSlip;
            const wrap = document.getElementById('manual_pod_preview_wrap');
            const img  = document.getElementById('manual_pod_preview_img');
            const txt  = document.getElementById('manual_pod_preview_txt');
            if (img) img.src = currentSlip;
            if (txt) txt.innerText = currentSlip.split('/').pop();
            if (wrap) {
                wrap.classList.remove('d-none');
                wrap.classList.add('d-flex');
            }
        }

        const modal = new bootstrap.Modal(document.getElementById('manualStatusModal'));
        modal.show();
    }

    function submitManualStatusUpdate() {
        const bookingId = document.getElementById('modal_manual_booking_id').value;
        const newStatus = document.getElementById('modal_manual_status_select').value;
        const costVal = document.getElementById('modal_manual_cost_input').value;
        const costPaidVal = document.getElementById('modal_manual_cost_paid_input').value;
        const slipVal = document.getElementById('modal_manual_consignment_slip').value;
        const btn = document.getElementById('btnConfirmManualStatus');

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

        fetch("{{ url('admin/courier-management/manual-status-update') }}/" + bookingId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                delivery_status: newStatus,
                courier_cost: costVal,
                courier_cost_paid: costPaidVal,
                consignment_slip: slipVal
            })
        })
        .then(res => res.json())
        .then(data => {
            const modalEl = document.getElementById('manualStatusModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            showCourierModalAlert(data.message, 'success', 'Status & Cost Updated');
            fetchCourierOrders();
        })
        .catch(err => {
            console.error(err);
            showCourierModalAlert('Failed to update status. Please try again.', 'danger', 'Update Error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'Save & Sync';
        });
    }

    function copyTrackingCode(code) {
        navigator.clipboard.writeText(code).then(() => {
            showCourierModalAlert('Tracking Code "' + code + '" copied to clipboard!', 'success', 'Copied');
        });
    }

    function exportCourierLedger(type) {
        const form = document.getElementById('courierFilterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.append('export', type);
        window.open("{{ route('admin.courier.index') }}?" + params.toString(), '_blank');
    }
</script>
@endsection