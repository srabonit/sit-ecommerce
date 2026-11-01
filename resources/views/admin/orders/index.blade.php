@extends('layouts.admin')

@section('title', 'Orders Management - ' . config('app.name'))

@section('admin_content')
<style>
    /* Strict 5px Element Radius */
    #ordersManagementRoot .btn,
    #ordersManagementRoot .btn-sm,
    #ordersManagementRoot button,
    #ordersManagementRoot .form-control,
    #ordersManagementRoot .form-select,
    #ordersManagementRoot .badge,
    #ordersManagementRoot .input-group-text,
    #ordersManagementRoot .page-link,
    #ordersManagementRoot .page-item span,
    #ordersManagementRoot .status-tab,
    .order-modal-scope .btn,
    .order-modal-scope .modal-content {
        border-radius: 5px !important;
    }

    #ordersManagementRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #ordersManagementRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Compact UI Buttons */
    #ordersManagementRoot .btn-compact,
    .order-modal-scope .btn-compact {
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

    .table-action-btn {
        width: 25px !important;
        height: 25px !important;
        min-width: 25px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 10.5px !important;
        border-radius: 4px !important;
        transition: all 0.15s ease;
    }

    /* Enterprise Surface Cards */
    .orders-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    /* Horizontal Scrollable Status Tabs */
    .status-tabs-scroll-wrap {
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
        -ms-overflow-style: none;
        -webkit-overflow-scrolling: touch;
    }
    .status-tabs-scroll-wrap::-webkit-scrollbar {
        display: none;
    }
    .status-tab {
        font-size: 11px !important;
        font-weight: 600 !important;
        padding: 4px 10px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #f8fafc;
        color: #475569;
        transition: all 0.15s ease;
        white-space: nowrap;
    }
    .status-tab:hover {
        background-color: #f1f5f9;
        color: #0f172a;
        border-color: #94a3b8 !important;
    }
    .status-tab.active-tab {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25);
    }

    /* Filter Toolbar Form Controls */
    #ordersManagementRoot .form-control,
    #ordersManagementRoot .form-select {
        font-size: 11.5px;
        height: 32px;
        padding: 4px 8px;
        border: 1px solid #cbd5e1;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #ordersManagementRoot .form-control:focus,
    #ordersManagementRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* PC 100% Fit & Responsive Table Architecture */
    .orders-table-wrapper {
        width: 100%;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
    }
    .orders-table-wrapper::-webkit-scrollbar {
        height: 6px;
    }
    .orders-table-wrapper::-webkit-scrollbar-track {
        background: #f8fafc;
    }
    .orders-table-wrapper::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .custom-orders-table {
        width: 100% !important;
        margin-bottom: 0;
        border-collapse: collapse;
    }
    .custom-orders-table th {
        background-color: #f8fafc;
        color: #64748b;
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 6px;
        white-space: nowrap;
    }
    .custom-orders-table td {
        padding: 7px 6px;
        vertical-align: middle;
        font-size: 12px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }
    .custom-orders-table tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Desktop Column Width Controls (Fits perfectly on PC screens) */
    @media (min-width: 992px) {
        .col-cell-order    { width: 12%; }
        .col-cell-date     { width: 11%; }
        .col-cell-customer { width: 23%; max-width: 220px; }
        .col-cell-courier  { width: 12%; }
        .col-cell-total    { width: 13%; }
        .col-cell-status   { width: 11%; }
        .col-cell-payment  { width: 9%; }
        .col-cell-action   { width: 9%; }
    }

    /* Overflow Text Handling for Customer Name & Address */
    .cust-name-text {
        max-width: 170px;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .cust-address-box {
        max-width: 180px;
        word-break: break-all !important;
        overflow-wrap: anywhere !important;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Bottom Summary Strip */
    .orders-summary-strip {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        padding: 8px 14px;
    }
    .summary-badge-item {
        font-size: 11.5px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    /* Pagination */
    #paginationWrapper nav {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
    }
    #paginationWrapper nav > div:first-child.d-sm-none,
    #paginationWrapper nav p.text-muted,
    #paginationWrapper nav div:has(> p.text-muted) {
        display: none !important;
    }
    #paginationWrapper nav > div:last-child,
    #paginationWrapper nav > div.d-sm-flex {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
    }
    #paginationWrapper .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
        gap: 3px !important;
        padding: 0 !important;
        list-style: none !important;
    }
    #paginationWrapper .page-item .page-link,
    #paginationWrapper .page-item span {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 26px !important;
        height: 26px !important;
        padding: 2px 6px !important;
        font-size: 11px !important;
        color: #475569 !important;
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 4px !important;
        text-decoration: none !important;
    }
    #paginationWrapper .page-item.active .page-link,
    #paginationWrapper .page-item.active span {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700 !important;
    }
    #paginationWrapper .page-item.disabled .page-link,
    #paginationWrapper .page-item.disabled span {
        color: #cbd5e1 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        pointer-events: none !important;
    }
    #paginationWrapper nav svg {
        width: 11px !important;
        height: 11px !important;
    }

    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Mobile Responsive 100/100 */
    @media (max-width: 767.98px) {
        .custom-orders-table {
            min-width: 780px !important;
        }
        .orders-page-header h5 {
            font-size: 16px !important;
        }
        .header-top-actions {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .header-top-actions .btn-compact {
            width: 100%;
        }
        .header-top-actions a.btn-outline-primary {
            grid-column: span 2;
        }
        .orders-summary-strip {
            padding: 8px 10px;
        }
        .summary-badge-item {
            width: 100%;
            border-left: none !important;
            padding-left: 0 !important;
            font-size: 11px;
        }
    }
</style>

<div class="container-fluid py-2 px-2 px-md-3" id="ordersManagementRoot">
    <!-- Header & Top Action Controls -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-2 orders-page-header">
        <div>
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 17px;">
                <i class="fas fa-boxes-packing text-primary"></i>
                <span>Orders Management</span>
            </h5>
            <small class="text-muted d-block mt-0.5" style="font-size: 11.5px; line-height: 1.35;">Manage web and POS orders, export data, live payment verification &amp; fulfillments.</small>
        </div>
        <div class="header-top-actions d-flex flex-wrap gap-1.5">
            <button type="button" class="btn btn-outline-success me-2 btn-compact shadow-xs" onclick="exportOrdersData('excel')" title="Export filtered orders to Excel/CSV">
                <i class="fas fa-file-excel me-1 text-success"></i>
                <span>Export Excel</span>
            </button>
            <button type="button" class="btn me-2 btn-outline-danger btn-compact shadow-xs" onclick="exportOrdersData('pdf')" title="Export filtered orders to PDF">
                <i class="fas fa-file-pdf me-1 text-danger"></i>
                <span>Export PDF</span>
            </button>
            <a href="{{ route('admin.order.fraud') }}" class="btn btn-outline-primary btn-compact shadow-xs">
                <i class="fas fa-shield-alt me-1"></i>
                <span>Fraud Checker</span>
            </a>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="orders-panel-card mb-2 p-1.5">
        <div class="status-tabs-scroll-wrap">
            <div class="d-inline-flex gap-1 align-items-center" id="statusTabsWrapper">
                <button type="button" class="btn btn-compact status-tab {{ empty(request('status')) ? 'active-tab fw-bold' : '' }}" data-status="">
                    All Orders (<span id="count-tab-all" class="bn-digit">{{ $counts['all'] ?? 0 }}</span>)
                </button>
                <button type="button" class="btn btn-compact status-tab {{ request('status') === 'pending' ? 'active-tab fw-bold' : '' }}" data-status="pending">
                    Pending (<span id="count-tab-pending" class="bn-digit">{{ $counts['pending'] ?? 0 }}</span>)
                </button>
                <button type="button" class="btn btn-compact status-tab {{ request('status') === 'received' ? 'active-tab fw-bold' : '' }}" data-status="received">
                    Received (<span id="count-tab-received" class="bn-digit">{{ $counts['received'] ?? 0 }}</span>)
                </button>
                <button type="button" class="btn btn-compact status-tab {{ request('status') === 'confirmed' ? 'active-tab fw-bold' : '' }}" data-status="confirmed">
                    Confirmed (<span id="count-tab-confirmed" class="bn-digit">{{ $counts['confirmed'] ?? 0 }}</span>)
                </button>
                <button type="button" class="btn btn-compact status-tab {{ request('status') === 'processing' ? 'active-tab fw-bold' : '' }}" data-status="processing">
                    Processing (<span id="count-tab-processing" class="bn-digit">{{ $counts['processing'] ?? 0 }}</span>)
                </button>
                <button type="button" class="btn btn-compact status-tab {{ request('status') === 'shipped' ? 'active-tab fw-bold' : '' }}" data-status="shipped">
                    Shipped (<span id="count-tab-shipped" class="bn-digit">{{ $counts['shipped'] ?? 0 }}</span>)
                </button>
                <button type="button" class="btn btn-compact status-tab {{ request('status') === 'attempt_to_deliver' ? 'active-tab fw-bold' : '' }}" data-status="attempt_to_deliver">
                    Attempt to Deliver (<span id="count-tab-attempt_to_deliver" class="bn-digit">{{ $counts['attempt_to_deliver'] ?? 0 }}</span>)
                </button>
                <button type="button" class="btn btn-compact status-tab {{ request('status') === 'delivered' ? 'active-tab fw-bold' : '' }}" data-status="delivered">
                    Delivered (<span id="count-tab-delivered" class="bn-digit">{{ $counts['delivered'] ?? 0 }}</span>)
                </button>
                <button type="button" class="btn btn-compact status-tab {{ request('status') === 'returned' ? 'active-tab fw-bold' : '' }}" data-status="returned">
                    Returned (<span id="count-tab-returned" class="bn-digit">{{ $counts['returned'] ?? 0 }}</span>)
                </button>
                <button type="button" class="btn btn-compact status-tab {{ request('status') === 'exchange' ? 'active-tab fw-bold' : '' }}" data-status="exchange">
                    Exchanged (<span id="count-tab-exchange" class="bn-digit">{{ $counts['exchange'] ?? 0 }}</span>)
                </button>
                <button type="button" class="btn btn-compact status-tab {{ request('status') === 'cancelled' ? 'active-tab fw-bold' : '' }}" data-status="cancelled">
                    Cancelled (<span id="count-tab-cancelled" class="bn-digit">{{ $counts['cancelled'] ?? 0 }}</span>)
                </button>
            </div>
        </div>
    </div>

    <!-- Multi-Dimensional Advanced Filter Toolbar -->
    <div class="orders-panel-card mb-2 p-2">
        <div class="row g-1.5 align-items-center">
            <!-- 1. Live Search Input -->
            <div class="col-12 col-xl-2 col-md-3">
                <div class="input-group input-group-sm position-relative">
                    <span class="input-group-text bg-white border-end-0" style="height: 32px;"><i class="fas fa-search text-muted" style="font-size: 10.5px;"></i></span>
                    <input type="text" id="ajaxSearchInput" class="form-control border-start-0" placeholder="Order ID, phone...">
                    <span id="searchSpinner" class="spinner-border spinner-border-sm text-primary position-absolute end-0 me-2 top-50 translate-middle-y d-none" style="z-index: 5;"></span>
                </div>
            </div>

            <!-- 2. Date Filter Preset -->
            <div class="col-6 col-xl-2 col-md-2">
                <select id="dateFilterSelect" class="form-select">
                    <option value="all">All Dates</option>
                    <option value="today">Today's Orders</option>
                    <option value="single_date">Specific Date...</option>
                    <option value="3_days">Last 3 Days</option>
                    <option value="7_days">Last 7 Days</option>
                    <option value="15_days">Last 15 Days</option>
                    <option value="1_month">Last 1 Month</option>
                    <option value="3_months">Last 3 Months</option>
                    <option value="6_months">Last 6 Months</option>
                    <option value="custom">Date to Date...</option>
                </select>
            </div>

            <!-- 3. Channel Filter (Web / POS) -->
            <div class="col-6 col-xl-2 col-md-2">
                <select id="orderSourceSelect" class="form-select">
                    <option value="all">All Channels</option>
                    <option value="web">Web Orders</option>
                    <option value="pos">POS Orders</option>
                </select>
            </div>

            <!-- 4. Payment Status Filter (Paid / Unpaid) -->
            <div class="col-6 col-xl-1 col-md-2">
                <select id="paymentStatusSelect" class="form-select">
                    <option value="all">Pay Status</option>
                    <option value="paid">Paid</option>
                    <option value="unpaid">Unpaid</option>
                </select>
            </div>

            <!-- 5. Payment Method Filter -->
            <div class="col-6 col-xl-2 col-md-3">
                <select id="paymentMethodSelect" class="form-select">
                    <option value="all">Gateway: All Methods</option>
                    @if(isset($availablePaymentMethods) && $availablePaymentMethods->isNotEmpty())
                        @foreach($availablePaymentMethods as $pMethod)
                            <option value="{{ $pMethod }}">{{ strtoupper($pMethod) }}</option>
                        @endforeach
                    @else
                        <option value="cod">COD</option>
                        <option value="cash">CASH</option>
                        <option value="bkash">BKASH</option>
                        <option value="nagad">NAGAD</option>
                        <option value="rocket">ROCKET</option>
                    @endif
                </select>
            </div>

            <!-- 6. Per-Page Display Controller -->
            <div class="col-6 col-xl-1 col-md-2">
                <select id="perPageSelect" class="form-select fw-bold bn-digit" title="Items per page">
                    <option value="20" {{ ($perPage ?? 20) == 20 ? 'selected' : '' }}>20</option>
                    <option value="40" {{ ($perPage ?? 20) == 40 ? 'selected' : '' }}>40</option>
                    <option value="100" {{ ($perPage ?? 20) == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ ($perPage ?? 20) == 100000 ? 'selected' : '' }}>Show All</option>
                </select>
            </div>

            <!-- 7. Bulk Actions -->
            <div class="col-12 col-xl-2 col-md-12 d-flex justify-content-end align-items-center gap-1">
                <span id="bulkSelectedCount" class="badge bg-primary-subtle text-primary border px-2 py-1 small d-none bn-digit">
                    <span id="selectedCountNumber">0</span>
                </span>
                <select id="bulkActionSelect" class="form-select flex-grow-1" style="max-width: 140px;" disabled>
                    <option value="">Bulk Actions</option>
                    <option value="status_pending">Mark Pending</option>
                    <option value="status_received">Mark Received</option>
                    <option value="status_confirmed">Mark Confirmed</option>
                    <option value="status_processing">Mark Processing</option>
                    <option value="status_shipped">Mark Shipped</option>
                    <option value="status_attempt_to_deliver">Mark Attempt to Deliver</option>
                    <option value="status_delivered">Mark Delivered</option>
                    <option value="status_returned">Mark Returned</option>
                    <option value="status_exchange">Mark Exchanged</option>
                    <option value="status_cancelled">Mark Cancelled</option>
                    <option value="delete">Delete Selected</option>
                </select>
                <button type="button" id="bulkApplyBtn" class="btn btn-dark btn-compact px-3 shadow-xs" onclick="handleBulkActionClick()" disabled>
                    <span id="bulkBtnText">Apply</span>
                    <span id="bulkSpinner" class="spinner-border spinner-border-sm d-none"></span>
                </button>
            </div>
        </div>

        <!-- Option A: Specific Single Date Picker Panel -->
        <div id="singleDateWrapper" class="row g-1.5 align-items-center mt-2 pt-2 border-top d-none">
            <div class="col-12 col-sm-auto small fw-bold text-muted" style="font-size: 11px;">
                <i class="fas fa-calendar-day text-primary me-1"></i> Specific Date:
            </div>
            <div class="col-12 col-sm-auto">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted small"><i class="fas fa-calendar-alt"></i></span>
                    <input type="date" id="specificSingleDate" class="form-control">
                </div>
            </div>
            <div class="col-12 col-sm-auto d-flex gap-1">
                <button type="button" class="btn btn-primary btn-compact" onclick="applySingleDateFilter()">
                    <i class="fas fa-check me-1"></i>Filter
                </button>
                <button type="button" class="btn btn-light border btn-compact" onclick="resetDateFilter()">
                    Reset
                </button>
            </div>
        </div>

        <!-- Option B: Custom Date Range Picker Panel -->
        <div id="customDateRangeWrapper" class="row g-1.5 align-items-center mt-2 pt-2 border-top d-none">
            <div class="col-12 col-sm-auto small fw-bold text-muted" style="font-size: 11px;">
                <i class="fas fa-calendar-check text-primary me-1"></i> Custom Range:
            </div>
            <div class="col-6 col-sm-auto">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted small">From</span>
                    <input type="date" id="customStartDate" class="form-control">
                </div>
            </div>
            <div class="col-6 col-sm-auto">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted small">To</span>
                    <input type="date" id="customEndDate" class="form-control">
                </div>
            </div>
            <div class="col-12 col-sm-auto d-flex gap-1">
                <button type="button" class="btn btn-primary btn-compact" onclick="applyCustomDateFilter()">
                    <i class="fas fa-check me-1"></i>Filter Range
                </button>
                <button type="button" class="btn btn-light border btn-compact" onclick="resetDateFilter()">
                    Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Orders Table Card -->
    <div class="orders-panel-card position-relative overflow-hidden mb-3">
        <div id="tableLoadingCover" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none justify-content-center align-items-center" style="z-index: 10;">
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        </div>

        <div class="orders-table-wrapper">
            <table class="table custom-orders-table align-middle">
                <thead>
                    <tr>
                        <th class="ps-2 pe-1 text-center" style="width: 32px;">
                            <input type="checkbox" class="form-check-input m-0" id="selectAllCheckbox" style="cursor: pointer; width: 15px; height: 15px;">
                        </th>
                        <th class="col-cell-order">Order Number</th>
                        <th class="col-cell-date">Date</th>
                        <th class="col-cell-customer">Customer</th>
                        <th class="col-cell-courier">Courier</th>
                        <th class="col-cell-total">Total Amount</th>
                        <th class="col-cell-status">Order Status</th>
                        <th class="col-cell-payment">Payment</th>
                        <th class="col-cell-action text-end pe-2">Action</th>
                    </tr>
                </thead>
                <tbody id="orderTableBody" class="small">
                    @include('admin.orders.partials.order_rows')
                </tbody>
                <!-- Bottom Total Summary Row in Table -->
                <tfoot class="bg-light border-top" style="border-top: 1.5px solid #cbd5e1 !important;">
                    <tr class="fw-bold">
                        <td colspan="5" class="ps-2 text-uppercase text-secondary small py-1.5" style="font-size: 11px;">
                            <i class="fas fa-calculator text-primary me-1"></i> Total Summary:
                        </td>
                        <td class="text-dark font-monospace bn-digit py-1.5" style="font-size: 12px;">
                            <span id="summaryTotalAmount">৳{{ number_format($totalAmountSum ?? 0, 2) }}</span>
                        </td>
                        <td></td>
                        <td class="py-1.5">
                            <span class="text-success small d-block font-monospace bn-digit" id="summaryTotalPaid" style="font-size: 10.5px;">
                                Paid: ৳{{ number_format($totalPaidSum ?? 0, 2) }}
                            </span>
                            <span class="text-danger small d-block font-monospace bn-digit" id="summaryTotalDue" style="font-size: 10.5px;">
                                Due: ৳{{ number_format($totalDueSum ?? 0, 2) }}
                            </span>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Live Bottom Highlight Strip -->
        <div class="orders-summary-strip d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex flex-wrap align-items-center gap-2 gap-md-3">
                <span class="summary-badge-item text-dark">
                    <i class="fas fa-receipt text-primary"></i> Total: <strong id="summaryCountOrders" class="text-primary bn-digit font-monospace ms-1">{{ $orders->total() }}</strong>
                </span>
                <span class="summary-badge-item text-dark border-start ps-2 ps-md-3">
                    <i class="fas fa-wallet text-secondary"></i> Gross Sales: <strong id="stripTotalAmount" class="text-dark bn-digit font-monospace ms-1">৳{{ number_format($totalAmountSum ?? 0, 2) }}</strong>
                </span>
                <span class="summary-badge-item text-success border-start ps-2 ps-md-3">
                    <i class="fas fa-check-circle"></i> Paid: <strong id="stripTotalPaid" class="bn-digit font-monospace ms-1">৳{{ number_format($totalPaidSum ?? 0, 2) }}</strong>
                </span>
                <span class="summary-badge-item text-danger border-start ps-2 ps-md-3">
                    <i class="fas fa-exclamation-circle"></i> Due: <strong id="stripTotalDue" class="bn-digit font-monospace ms-1">৳{{ number_format($totalDueSum ?? 0, 2) }}</strong>
                </span>
            </div>
            <div>
                <small class="text-muted bn-digit" id="entriesInfoText" style="font-size: 11px;">
                    Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} entries
                </small>
            </div>
        </div>

        <!-- Pagination Wrapper -->
        <div class="p-2 border-0 bg-white d-flex justify-content-center overflow-auto" id="paginationWrapper" style="{{ $orders->hasPages() ? '' : 'display: none;' }}">
            @if($orders->hasPages())
                {{ $orders->links('pagination::bootstrap-5') }}
            @endif
        </div>
    </div>
</div>

<!-- Modal 1: Confirmation Modal -->
<div class="modal fade order-modal-scope" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 44px; height: 44px;">
                    <i class="fas fa-triangle-exclamation fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="modalConfirmTitle" style="font-size: 14px;">Delete Order?</h6>
                <p class="text-muted small mb-3" id="modalConfirmMessage" style="font-size: 12px; line-height: 1.45;">Are you sure you want to proceed?</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-compact px-3 shadow-xs" id="confirmProceedBtn">
                        <span id="modalProceedText">Confirm</span>
                        <span id="modalProceedSpinner" class="spinner-border spinner-border-sm d-none"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Info/Notification Modal -->
<div class="modal fade order-modal-scope" id="infoNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div id="infoModalIcon" class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 44px; height: 44px;">
                    <i class="fas fa-info-circle fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="infoModalTitle" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="infoModalBody" style="font-size: 12px; line-height: 1.45;"></p>
                <div>
                    <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('ajaxSearchInput');
    const tableBody = document.getElementById('orderTableBody');
    const spinner = document.getElementById('searchSpinner');
    const tableCover = document.getElementById('tableLoadingCover');
    const paginationWrapper = document.getElementById('paginationWrapper');
    const statusTabs = document.querySelectorAll('.status-tab');
    
    const dateFilterSelect = document.getElementById('dateFilterSelect');
    const orderSourceSelect = document.getElementById('orderSourceSelect');
    const paymentStatusSelect = document.getElementById('paymentStatusSelect');
    const paymentMethodSelect = document.getElementById('paymentMethodSelect');
    const perPageSelect = document.getElementById('perPageSelect');

    const singleDateWrapper = document.getElementById('singleDateWrapper');
    const specificSingleDate = document.getElementById('specificSingleDate');
    const customDateWrapper = document.getElementById('customDateRangeWrapper');
    const customStartDate = document.getElementById('customStartDate');
    const customEndDate = document.getElementById('customEndDate');

    const summaryTotalAmount = document.getElementById('summaryTotalAmount');
    const summaryTotalPaid   = document.getElementById('summaryTotalPaid');
    const summaryTotalDue    = document.getElementById('summaryTotalDue');
    const stripTotalAmount   = document.getElementById('stripTotalAmount');
    const stripTotalPaid     = document.getElementById('stripTotalPaid');
    const stripTotalDue      = document.getElementById('stripTotalDue');
    const summaryCountOrders = document.getElementById('summaryCountOrders');
    const entriesInfoText    = document.getElementById('entriesInfoText');

    let currentStatus = "{{ request('status') }}";
    let currentDateFilter = 'all';
    let currentOrderSource = 'all';
    let currentPaymentStatus = 'all';
    let currentPaymentMethod = 'all';
    let currentPerPage = "{{ $perPage ?? 20 }}";
    let currentPage = 1;
    let debounceTimer;

    window.fetchOrders = function (page = 1) {
        currentPage = page;
        const query = searchInput.value.trim();

        if (spinner) spinner.classList.remove('d-none');
        if (tableCover) {
            tableCover.classList.remove('d-none');
            tableCover.classList.add('d-flex');
        }

        const url = new URL("{{ route('admin.order.online') }}", window.location.origin);
        
        if (query) url.searchParams.set('search', query);
        if (currentStatus) url.searchParams.set('status', currentStatus);
        if (currentPage > 1) url.searchParams.set('page', currentPage);
        if (currentPerPage) url.searchParams.set('per_page', currentPerPage);

        if (currentDateFilter && currentDateFilter !== 'all') {
            url.searchParams.set('date_filter', currentDateFilter);
            if (currentDateFilter === 'single_date') {
                if (specificSingleDate.value) url.searchParams.set('single_date', specificSingleDate.value);
            } else if (currentDateFilter === 'custom') {
                if (customStartDate.value) url.searchParams.set('start_date', customStartDate.value);
                if (customEndDate.value) url.searchParams.set('end_date', customEndDate.value);
            }
        }

        if (currentOrderSource && currentOrderSource !== 'all') {
            url.searchParams.set('order_source', currentOrderSource);
        }
        if (currentPaymentStatus && currentPaymentStatus !== 'all') {
            url.searchParams.set('payment_status', currentPaymentStatus);
        }
        if (currentPaymentMethod && currentPaymentMethod !== 'all') {
            url.searchParams.set('payment_method', currentPaymentMethod);
        }

        url.searchParams.set('format', 'json');

        fetch(url, {
            headers: { 
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.includes("application/json")) {
                return response.json();
            } else {
                return response.text().then(html => ({ html: html, pagination_html: '' }));
            }
        })
        .then(data => {
            tableBody.innerHTML = data.html || data;
            
            if (paginationWrapper) {
                if (currentPerPage === 'all' || !data.pagination_html || data.pagination_html.trim() === '') {
                    paginationWrapper.innerHTML = '';
                    paginationWrapper.style.display = 'none';
                } else {
                    paginationWrapper.innerHTML = data.pagination_html;
                    paginationWrapper.style.display = 'block';
                }
            }

            if (data.counts) {
                updateStatusTabsCounts(data.counts);
            }

            if (data.summary) {
                if (summaryTotalAmount) summaryTotalAmount.innerText = '৳' + data.summary.total_amount;
                if (stripTotalAmount) stripTotalAmount.innerText = '৳' + data.summary.total_amount;
                
                if (summaryTotalPaid) summaryTotalPaid.innerText = 'Paid: ৳' + data.summary.total_paid;
                if (stripTotalPaid) stripTotalPaid.innerText = '৳' + data.summary.total_paid;

                if (summaryTotalDue) summaryTotalDue.innerText = 'Due: ৳' + data.summary.total_due;
                if (stripTotalDue) stripTotalDue.innerText = '৳' + data.summary.total_due;
            }

            if (data.total_count !== undefined && summaryCountOrders) {
                summaryCountOrders.innerText = data.total_count;
            }

            if (data.entries_text && entriesInfoText) {
                entriesInfoText.innerText = data.entries_text;
            }

            bindCheckboxEvents();
        })
        .catch(err => console.error('Fetch error:', err))
        .finally(() => {
            if (spinner) spinner.classList.add('d-none');
            if (tableCover) {
                tableCover.classList.add('d-none');
                tableCover.classList.remove('d-flex');
            }
        });
    };

    function updateStatusTabsCounts(counts) {
        if (!counts) return;

        const mapping = {
            'count-tab-all': counts.all ?? 0,
            'count-tab-pending': counts.pending ?? 0,
            'count-tab-received': counts.received ?? 0,
            'count-tab-confirmed': counts.confirmed ?? 0,
            'count-tab-processing': counts.processing ?? 0,
            'count-tab-shipped': counts.shipped ?? 0,
            'count-tab-attempt_to_deliver': counts.attempt_to_deliver ?? 0,
            'count-tab-delivered': counts.delivered ?? 0,
            'count-tab-returned': counts.returned ?? 0,
            'count-tab-exchange': counts.exchange ?? 0,
            'count-tab-cancelled': counts.cancelled ?? 0,
        };

        for (const [id, count] of Object.entries(mapping)) {
            const el = document.getElementById(id);
            if (el) el.innerText = count;
        }
    }

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => window.fetchOrders(1), 300);
    });

    statusTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            statusTabs.forEach(t => t.classList.remove('active-tab', 'fw-bold'));
            this.classList.add('active-tab', 'fw-bold');
            currentStatus = this.getAttribute('data-status');
            window.fetchOrders(1);
        });
    });

    dateFilterSelect.addEventListener('change', function () {
        currentDateFilter = this.value;

        if (currentDateFilter === 'single_date') {
            singleDateWrapper.classList.remove('d-none');
            customDateWrapper.classList.add('d-none');
        } else if (currentDateFilter === 'custom') {
            customDateWrapper.classList.remove('d-none');
            singleDateWrapper.classList.add('d-none');
        } else {
            singleDateWrapper.classList.add('d-none');
            customDateWrapper.classList.add('d-none');
            window.fetchOrders(1);
        }
    });

    window.applySingleDateFilter = function () {
        if (!specificSingleDate.value) {
            showInfoModal('Date Required', 'Please choose a specific date first.', false);
            return;
        }
        currentDateFilter = 'single_date';
        window.fetchOrders(1);
    };

    window.applyCustomDateFilter = function () {
        if (!customStartDate.value && !customEndDate.value) {
            showInfoModal('Date Required', 'Please select at least a Start Date or End Date.', false);
            return;
        }
        currentDateFilter = 'custom';
        window.fetchOrders(1);
    };

    window.resetDateFilter = function () {
        specificSingleDate.value = '';
        customStartDate.value = '';
        customEndDate.value = '';
        dateFilterSelect.value = 'all';
        currentDateFilter = 'all';
        singleDateWrapper.classList.add('d-none');
        customDateWrapper.classList.add('d-none');
        window.fetchOrders(1);
    };

    orderSourceSelect.addEventListener('change', function () {
        currentOrderSource = this.value;
        window.fetchOrders(1);
    });

    paymentStatusSelect.addEventListener('change', function () {
        currentPaymentStatus = this.value;
        window.fetchOrders(1);
    });

    paymentMethodSelect.addEventListener('change', function () {
        currentPaymentMethod = this.value;
        window.fetchOrders(1);
    });

    perPageSelect.addEventListener('change', function () {
        currentPerPage = this.value;
        window.fetchOrders(1);
    });

    window.exportOrdersData = function (type) {
        const query = searchInput.value.trim();
        const url = new URL("{{ route('admin.order.online') }}", window.location.origin);
        
        if (query) url.searchParams.set('search', query);
        if (currentStatus) url.searchParams.set('status', currentStatus);
        if (currentOrderSource && currentOrderSource !== 'all') url.searchParams.set('order_source', currentOrderSource);
        if (currentPaymentStatus && currentPaymentStatus !== 'all') url.searchParams.set('payment_status', currentPaymentStatus);
        if (currentPaymentMethod && currentPaymentMethod !== 'all') url.searchParams.set('payment_method', currentPaymentMethod);

        if (currentDateFilter && currentDateFilter !== 'all') {
            url.searchParams.set('date_filter', currentDateFilter);
            if (currentDateFilter === 'single_date') {
                if (specificSingleDate.value) url.searchParams.set('single_date', specificSingleDate.value);
            } else if (currentDateFilter === 'custom') {
                if (customStartDate.value) url.searchParams.set('start_date', customStartDate.value);
                if (customEndDate.value) url.searchParams.set('end_date', customEndDate.value);
            }
        }

        url.searchParams.set('export', type);
        window.open(url.toString(), '_blank');
    };

    document.addEventListener('click', function (e) {
        const pageLink = e.target.closest('#paginationWrapper .page-link');
        if (pageLink) {
            e.preventDefault();
            const href = pageLink.getAttribute('href');
            if (href && href !== '#' && !href.startsWith('javascript:')) {
                const urlObj = new URL(href, window.location.origin);
                const pageNum = urlObj.searchParams.get('page') || 1;
                window.fetchOrders(pageNum);

                const tableCard = document.querySelector('.orders-table-wrapper');
                if (tableCard) {
                    tableCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }
        }
    });

    const selectAll = document.getElementById('selectAllCheckbox');
    const bulkSelect = document.getElementById('bulkActionSelect');
    const bulkBtn = document.getElementById('bulkApplyBtn');
    const bulkCountWrap = document.getElementById('bulkSelectedCount');
    const bulkCountNum = document.getElementById('selectedCountNumber');

    window.bindCheckboxEvents = function () {
        const rowCheckboxes = document.querySelectorAll('.order-checkbox');
        if (selectAll) selectAll.checked = false;
        updateBulkState();

        if (selectAll) {
            selectAll.onchange = function () {
                rowCheckboxes.forEach(cb => cb.checked = selectAll.checked);
                updateBulkState();
            };
        }

        rowCheckboxes.forEach(cb => {
            cb.onchange = updateBulkState;
        });
    };

    function updateBulkState() {
        const checked = document.querySelectorAll('.order-checkbox:checked');
        const count = checked.length;

        if (count > 0) {
            bulkSelect.removeAttribute('disabled');
            bulkBtn.removeAttribute('disabled');
            bulkCountWrap.classList.remove('d-none');
            bulkCountNum.textContent = count;
        } else {
            bulkSelect.setAttribute('disabled', 'disabled');
            bulkBtn.setAttribute('disabled', 'disabled');
            bulkCountWrap.classList.add('d-none');
        }
    }

    bindCheckboxEvents();
});

function showInfoModal(title, message, isSuccess = true) {
    const modalEl = document.getElementById('infoNoticeModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    document.getElementById('infoModalTitle').textContent = title;
    document.getElementById('infoModalBody').textContent = message;
    
    const icon = document.getElementById('infoModalIcon');
    icon.className = isSuccess 
        ? 'rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-2' 
        : 'rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2';
    icon.innerHTML = isSuccess 
        ? '<i class="fas fa-check-circle fs-4"></i>' 
        : '<i class="fas fa-times-circle fs-4"></i>';

    modal.show();
}

let pendingDeleteAction = null;

function confirmDeleteOrder(orderId) {
    const modalEl = document.getElementById('deleteConfirmModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    document.getElementById('modalConfirmTitle').textContent = 'Delete Order?';
    document.getElementById('modalConfirmMessage').textContent = `Are you sure you want to permanently delete order #${orderId}?`;

    pendingDeleteAction = function () {
        const proceedBtn = document.getElementById('confirmProceedBtn');
        const proceedText = document.getElementById('modalProceedText');
        const proceedSpinner = document.getElementById('modalProceedSpinner');

        proceedBtn.disabled = true;
        proceedText.classList.add('d-none');
        proceedSpinner.classList.remove('d-none');

        fetch(`/admin/orders/delete/${orderId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            modal.hide();
            showInfoModal('Success', data.message || 'Order deleted successfully.', true);
            window.fetchOrders();
        })
        .catch(err => {
            modal.hide();
            showInfoModal('Error', 'Failed to delete order.', false);
        })
        .finally(() => {
            proceedBtn.disabled = false;
            proceedText.classList.remove('d-none');
            proceedSpinner.classList.add('d-none');
        });
    };

    modal.show();
}

/**
 * 1-Click Manual SMS Dispatcher for Orders
 */
window.sendManualOrderSms = function (orderId, btnElement) {
    if (!orderId) return;

    let originalHtml = '';
    if (btnElement) {
        btnElement.disabled = true;
        originalHtml = btnElement.innerHTML;
        btnElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }

    fetch(`/admin/orders/send-manual-sms/${orderId}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showInfoModal('SMS Sent', data.message || 'SMS dispatched successfully.', true);
        } else {
            showInfoModal('SMS Failed', data.message || 'Failed to send SMS. Check settings or gateway balance.', false);
        }
    })
    .catch(err => {
        showInfoModal('Error', 'Gateway connection error while triggering SMS.', false);
    })
    .finally(() => {
        if (btnElement) {
            btnElement.disabled = false;
            btnElement.innerHTML = originalHtml;
        }
    });
};

function handleBulkActionClick() {
    const bulkSelect = document.getElementById('bulkActionSelect');
    const checked = Array.from(document.querySelectorAll('.order-checkbox:checked')).map(cb => cb.value);

    if (checked.length === 0) return;
    if (!bulkSelect.value) {
        showInfoModal('Action Required', 'Please select an action from the dropdown.', false);
        return;
    }

    if (bulkSelect.value === 'delete') {
        const modalEl = document.getElementById('deleteConfirmModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

        document.getElementById('modalConfirmTitle').textContent = 'Delete Selected Orders?';
        document.getElementById('modalConfirmMessage').textContent = `Are you sure you want to delete ${checked.length} selected orders?`;

        pendingDeleteAction = function () {
            executeBulkAjax(bulkSelect.value, checked, () => modal.hide());
        };

        modal.show();
    } else {
        executeBulkAjax(bulkSelect.value, checked);
    }
}

function executeBulkAjax(action, orderIds, callback = null) {
    const bulkBtn = document.getElementById('bulkApplyBtn');
    const bulkBtnText = document.getElementById('bulkBtnText');
    const bulkSpinner = document.getElementById('bulkSpinner');

    bulkBtn.disabled = true;
    bulkBtnText.classList.add('d-none');
    bulkSpinner.classList.remove('d-none');

    fetch("{{ route('admin.order.bulk.action') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            action_type: action,
            order_ids: orderIds
        })
    })
    .then(res => res.json())
    .then(data => {
        if (callback) callback();
        showInfoModal('Updated', data.message || 'Bulk action executed successfully.', true);
        window.fetchOrders();
    })
    .catch(err => {
        showInfoModal('Error', 'Bulk action failed. Please try again.', false);
    })
    .finally(() => {
        bulkBtn.disabled = false;
        bulkBtnText.classList.remove('d-none');
        bulkSpinner.classList.add('d-none');
    });
}

document.getElementById('confirmProceedBtn').addEventListener('click', function () {
    if (typeof pendingDeleteAction === 'function') {
        pendingDeleteAction();
    }
});
</script>
@endsection