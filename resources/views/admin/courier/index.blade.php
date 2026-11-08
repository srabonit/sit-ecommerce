@extends('layouts.admin')

@section('title', 'All Courier Orders - ' . config('app.name'))

@section('admin_content')
<style>
    /* =========================================================
       COURIER ORDERS — KAIADMIN CLASSIC / WHITE THEME
    ========================================================= */

    #courier-orders-root {
        --courier-primary: #1572e8;
        --courier-primary-soft: #eaf2ff;
        --courier-success: #31ce77;
        --courier-danger: #f25961;
        --courier-warning: #ffad46;
        --courier-text: #2a2f5b;
        --courier-muted: #6b7280;
        --courier-border: #e5e7eb;
        --courier-bg: #f8f9fa;
        color: var(--courier-text);
        font-size: 13px;
        min-width: 0;
    }

    /* Consistent 5px radius */
    #courier-orders-root .btn,
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

    #courier-orders-root .bn-digit,
    #courier-orders-root .font-monospace,
    .courier-modal-scope .bn-digit,
    .courier-modal-scope .font-monospace {
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    /* Header */
    #courier-orders-root h5 {
        color: #2a2f5b !important;
        font-size: 18px !important;
        font-weight: 700 !important;
        letter-spacing: -0.35px;
    }

    #courier-orders-root .text-muted {
        color: #858b99 !important;
    }

    #courier-orders-root .top-action-bar-wrap {
        align-items: center;
    }

    /* Buttons */
    #courier-orders-root .btn-compact {
        min-height: 32px;
        padding: 6px 12px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        font-size: 11.5px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        white-space: nowrap;
        transition: background-color .18s ease, border-color .18s ease,
                    box-shadow .18s ease, transform .18s ease;
    }

    #courier-orders-root .btn-primary,
    .courier-modal-scope .btn-primary {
        background: #1572e8;
        border-color: #1572e8;
    }

    #courier-orders-root .btn-primary:hover,
    .courier-modal-scope .btn-primary:hover {
        background: #1264cc;
        border-color: #1264cc;
        box-shadow: 0 3px 8px rgba(21, 114, 232, .16);
    }

    #courier-orders-root .btn-compact:active,
    .courier-modal-scope .btn:active {
        transform: translateY(1px);
    }

    /* Main cards */
    #courier-orders-root .courier-table-card {
        background: #fff;
        border: 1px solid var(--courier-border);
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035);
        margin-bottom: 16px;
        overflow: hidden;
    }

    /* KPI cards */
    #courier-orders-root .stat-mini-pill {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        height: 100%;
        min-height: 72px;
        padding: 12px 13px;
        background: #fff;
        border: 1px solid var(--courier-border);
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035);
        overflow: hidden;
        transition: box-shadow .2s ease, transform .2s ease;
    }

    #courier-orders-root .stat-mini-pill:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(0, 0, 0, .065);
    }

    #courier-orders-root .stat-pill-blue {
        background: #f5f9ff;
        border-color: #d9e8ff;
    }

    #courier-orders-root .stat-pill-green {
        background: #f4fbf7;
        border-color: #d5f0df;
    }

    #courier-orders-root .stat-pill-red {
        background: #fff7f7;
        border-color: #f7dddd;
    }

    #courier-orders-root .stat-pill-slate {
        background: #f8f9fb;
        border-color: #e5e7eb;
    }

    #courier-orders-root .stat-mini-pill small {
        font-size: 10.5px !important;
        line-height: 1.35;
    }

    #courier-orders-root .stat-mini-pill h5 {
        font-size: 15px !important;
        letter-spacing: -.2px;
    }

    #courier-orders-root .stat-icon-box {
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        font-size: 14px;
    }

    /* Filters */
    #courier-orders-root .courier-table-card:has(#courierFilterForm) {
        padding: 15px !important;
    }

    #courier-orders-root .form-control,
    #courier-orders-root .form-select,
    .courier-modal-scope .form-control,
    .courier-modal-scope .form-select {
        min-height: 34px;
        height: 34px;
        padding: 5px 9px;
        border: 1px solid #dce1e7;
        background-color: #fff;
        color: #343a40;
        font-size: 11.5px;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #courier-orders-root .form-control:focus,
    #courier-orders-root .form-select:focus,
    .courier-modal-scope .form-control:focus,
    .courier-modal-scope .form-select:focus {
        border-color: #1572e8;
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
        outline: none;
    }

    #courier-orders-root .input-group-text {
        min-height: 34px;
        border-color: #dce1e7;
        background: #f8f9fa !important;
        color: #858b99 !important;
    }

    #courier-orders-root input::placeholder,
    .courier-modal-scope input::placeholder {
        color: #a0a6b1;
        opacity: 1;
    }

    #courier-orders-root .form-select {
        padding-right: 25px;
    }

    #courier-orders-root #courierFilterForm .border-top {
        border-color: #edf0f3 !important;
    }

    #courier-orders-root #courierFilterForm .small {
        font-size: 11px !important;
    }

    /* Bulk action bar */
    #courier-orders-root .bulk-action-bar {
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 14px;
        margin-bottom: 12px;
        background: #2a2f5b;
        color: #fff;
        border: 1px solid #2a2f5b;
        box-shadow: 0 3px 10px rgba(42, 47, 91, .12);
    }

    #courier-orders-root .bulk-action-bar .form-select {
        min-width: 180px;
        background-color: #fff !important;
        color: #343a40 !important;
    }

    /* Table: keep every column accessible */
    #courier-orders-root .pc-fit-container,
    #courier-orders-root .table-responsive {
        width: 100%;
        overflow-x: auto !important;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #cbd2dc #f5f6f8;
    }

    #courier-orders-root .pc-fit-container::-webkit-scrollbar {
        height: 7px;
    }

    #courier-orders-root .pc-fit-container::-webkit-scrollbar-track {
        background: #f5f6f8;
    }

    #courier-orders-root .pc-fit-container::-webkit-scrollbar-thumb {
        background: #cbd2dc;
        border-radius: 5px;
    }

    #courier-orders-root .custom-courier-table {
        width: 100%;
        min-width: 1120px;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: fixed;
    }

    #courier-orders-root .custom-courier-table thead th {
        padding: 11px 7px;
        background: #f8f9fa;
        color: #6b7280;
        border-top: 0;
        border-bottom: 1px solid #e7eaf0;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .035em;
        line-height: 1.4;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }

    #courier-orders-root .custom-courier-table tbody td {
        padding: 9px 7px;
        color: #4b5563;
        border-bottom: 1px solid #f0f1f4;
        font-size: 11.5px;
        line-height: 1.45;
        vertical-align: middle;
        white-space: nowrap;
    }

    #courier-orders-root .custom-courier-table tbody tr {
        transition: background-color .15s ease;
    }

    #courier-orders-root .custom-courier-table tbody tr:hover {
        background: #f8faff;
    }

    #courier-orders-root .custom-courier-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #courier-orders-root .custom-courier-table .col-w-chk { width: 38px; }
    #courier-orders-root .custom-courier-table .col-w-id { width: 45px; }
    #courier-orders-root .custom-courier-table .col-w-inv { width: 125px; }
    #courier-orders-root .custom-courier-table .col-w-cust { width: 155px; }
    #courier-orders-root .custom-courier-table .col-w-prov { width: 105px; }
    #courier-orders-root .custom-courier-table .col-w-trk { width: 150px; }
    #courier-orders-root .custom-courier-table .col-w-wt { width: 65px; }
    #courier-orders-root .custom-courier-table .col-w-cod { width: 100px; }
    #courier-orders-root .custom-courier-table .col-w-set { width: 95px; }
    #courier-orders-root .custom-courier-table .col-w-dst { width: 105px; }
    #courier-orders-root .custom-courier-table .col-w-cst { width: 105px; }
    #courier-orders-root .custom-courier-table .col-w-act { width: 110px; }

    #courier-orders-root .custom-courier-table .form-check-input {
        width: 14px;
        height: 14px;
        margin: 0;
        cursor: pointer;
        border-color: #cbd2dc;
        box-shadow: none;
    }

    #courier-orders-root .custom-courier-table .form-check-input:checked {
        background-color: #1572e8;
        border-color: #1572e8;
    }

    /* Badges */
    #courier-orders-root .badge {
        padding: 4px 7px;
        font-size: 10px;
        font-weight: 600;
        line-height: 1.3;
    }

    #courier-orders-root .badge-soft-success {
        color: #17834d !important;
        background: #e8f7ee !important;
        border: 1px solid #ccebd8 !important;
    }

    #courier-orders-root .badge-soft-warning {
        color: #956313 !important;
        background: #fff6df !important;
        border: 1px solid #f5e5b7 !important;
    }

    #courier-orders-root .badge-soft-danger {
        color: #c03943 !important;
        background: #fff0f0 !important;
        border: 1px solid #f4d2d4 !important;
    }

    #courier-orders-root .badge-soft-info {
        color: #1672a5 !important;
        background: #eaf6fc !important;
        border: 1px solid #cde8f5 !important;
    }

    /* Proof media */
    #courier-orders-root .proof-media-surface {
        padding: 12px;
        background: #f8f9fb;
        border: 1px dashed #cbd2dc;
        transition: border-color .15s ease, background-color .15s ease;
    }

    #courier-orders-root .proof-media-surface:hover {
        border-color: #1572e8;
        background: #f4f8ff;
    }

    #courier-orders-root .proof-thumb-box {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        overflow: hidden;
        background: #fff;
        border: 1px solid #dce1e7;
        cursor: pointer;
    }

    #courier-orders-root .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #courier-orders-root .proof-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(42, 47, 91, .55);
        opacity: 0;
        transition: opacity .15s ease;
    }

    #courier-orders-root .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    /* Pagination */
    #courierPaginationWrapper {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        min-width: 0;
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
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-wrap: wrap !important;
        gap: 4px !important;
        margin: 0 !important;
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
        min-width: 28px !important;
        height: 28px !important;
        padding: 0 7px !important;
        color: #6b7280 !important;
        background: #fff !important;
        border: 1px solid #e1e5eb !important;
        border-radius: 5px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        line-height: 1 !important;
        text-decoration: none !important;
        transition: all .15s ease !important;
    }

    #courierPaginationWrapper .page-item .page-link:hover {
        color: #1572e8 !important;
        background: #f1f6ff !important;
        border-color: #bcd5fb !important;
    }

    #courierPaginationWrapper .page-item.active .page-link,
    #courierPaginationWrapper .page-item.active span {
        color: #fff !important;
        background: #1572e8 !important;
        border-color: #1572e8 !important;
        box-shadow: 0 2px 5px rgba(21, 114, 232, .18) !important;
    }

    #courierPaginationWrapper .page-item.disabled .page-link,
    #courierPaginationWrapper .page-item.disabled span {
        color: #aeb4be !important;
        background: #f8f9fa !important;
        border-color: #edf0f3 !important;
        cursor: not-allowed !important;
    }

    #courierPaginationWrapper nav svg {
        width: 11px !important;
        height: 11px !important;
    }

    /* Modals */
    .courier-modal-scope .modal-content {
        overflow: hidden;
        border: 1px solid #e7eaf0 !important;
        background: #fff;
        box-shadow: 0 12px 35px rgba(25, 35, 58, .15) !important;
    }

    .courier-modal-scope .modal-header {
        border-color: #edf0f3 !important;
    }

    .courier-modal-scope h6 {
        color: #2a2f5b !important;
    }

    .courier-modal-scope .form-label {
        color: #4b5563 !important;
        font-size: 11.5px !important;
    }

    .courier-modal-scope .alert {
        border: 1px solid #d8eaf5 !important;
    }

    .courier-modal-scope .modal-body p {
        line-height: 1.5;
    }

    .courier-modal-scope .btn-close {
        padding: 7px;
        box-shadow: none !important;
    }

    #courierProofZoomImgTag {
        max-width: 100%;
    }

    /* Footer */
    #courier-orders-root #paginationEntryCount {
        color: #858b99 !important;
        font-size: 11px !important;
    }

    /* Tablet */
    @media (max-width: 991.98px) {
        #courier-orders-root .top-action-bar-wrap {
            width: 100%;
        }

        #courier-orders-root .stat-mini-pill {
            min-height: 68px;
        }

        #courier-orders-root .bulk-action-bar {
            align-items: flex-start;
        }

        #courier-orders-root .bulk-action-bar > div:last-child {
            justify-content: flex-end;
        }
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        #courier-orders-root {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #courier-orders-root h5 {
            font-size: 16px !important;
        }

        #courier-orders-root .top-action-bar-wrap {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 7px !important;
        }

        #courier-orders-root .top-action-bar-wrap .btn-new-booking {
            grid-column: 1 / -1;
        }

        #courier-orders-root .top-action-bar-wrap .btn {
            width: 100%;
        }

        #courier-orders-root .stat-mini-pill {
            min-height: 66px;
            padding: 9px 10px;
            gap: 5px;
        }

        #courier-orders-root .stat-mini-pill small {
            font-size: 9.5px !important;
        }

        #courier-orders-root .stat-mini-pill h5 {
            font-size: 13px !important;
        }

        #courier-orders-root .stat-icon-box {
            width: 28px;
            height: 28px;
            flex-basis: 28px;
            font-size: 11px;
        }

        #courier-orders-root .courier-table-card:has(#courierFilterForm) {
            padding: 11px !important;
        }

        #courier-orders-root #courierFilterForm .row {
            row-gap: 8px;
        }

        #courier-orders-root #courierFilterForm .form-control,
        #courier-orders-root #courierFilterForm .form-select {
            min-height: 36px;
            height: 36px;
            font-size: 11px;
        }

        #courier-orders-root #courierFilterForm .border-top {
            margin-top: 10px !important;
            padding-top: 10px !important;
        }

        #courier-orders-root #courierFilterForm .col-12.col-md-7 {
            align-items: flex-end !important;
        }

        #courier-orders-root #courierFilterForm .col-12.col-md-7 > div {
            flex: 1 1 calc(50% - 6px);
            min-width: 0;
        }

        #courier-orders-root #courierFilterForm .col-12.col-md-7 select {
            width: 100%;
            min-width: 0 !important;
        }

        #courier-orders-root #courierFilterForm .col-12.col-md-7 > button {
            flex: 0 0 auto;
            margin-left: auto !important;
        }

        #courier-orders-root #customDateRow {
            align-items: center !important;
            gap: 6px !important;
        }

        #courier-orders-root #customDateRow input {
            flex: 1 1 100px;
            width: auto !important;
            min-width: 0;
        }

        #courier-orders-root .bulk-action-bar {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
            padding: 11px;
        }

        #courier-orders-root .bulk-action-bar > div:last-child {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 7px !important;
        }

        #courier-orders-root .bulk-action-bar select {
            grid-column: 1 / -1;
            width: 100%;
        }

        #courier-orders-root .bulk-action-bar button {
            width: 100%;
        }

        #courier-orders-root .pc-fit-container {
            overflow-x: auto !important;
        }

        #courier-orders-root .custom-courier-table {
            min-width: 1120px !important;
            table-layout: fixed !important;
        }

        #courier-orders-root .custom-courier-table th {
            padding: 10px 7px;
            font-size: 9.5px;
        }

        #courier-orders-root .custom-courier-table td {
            padding: 8px 7px;
            font-size: 11px;
        }

        #courier-orders-root .courier-table-card > .p-2.px-3 {
            align-items: flex-start !important;
            padding: 10px !important;
        }

        #courier-orders-root #paginationEntryCount {
            width: 100%;
            text-align: center;
        }

        #courierPaginationWrapper {
            justify-content: center;
            width: 100%;
            overflow-x: auto;
            padding: 3px 0;
            -webkit-overflow-scrolling: touch;
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
            min-width: 26px !important;
            height: 26px !important;
            font-size: 10px !important;
            padding: 0 5px !important;
        }

        .courier-modal-scope .modal-dialog {
            margin: 10px;
        }

        .courier-modal-scope .modal-content {
            padding: 12px !important;
        }

        #manualStatusModal .modal-dialog {
            max-width: none;
        }
    }

    @media (max-width: 380px) {
        #courier-orders-root .stat-mini-pill {
            padding: 8px;
        }

        #courier-orders-root .stat-mini-pill h5 {
            font-size: 12px !important;
        }

        #courier-orders-root .stat-icon-box {
            width: 25px;
            height: 25px;
            flex-basis: 25px;
        }

        #courier-orders-root #courierFilterForm .col-12.col-md-7 {
            gap: 7px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="courier-orders-root">

    <!-- Header & Actions -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-boxes-packing text-primary"></i>
                <span>Courier Consignments & Orders</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
               Shipment & COD Tracking
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

    <!-- Financial Summary -->
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

    <!-- Filters -->
<div class="courier-table-card p-3 mb-3">
    <form id="courierFilterForm" onsubmit="return false;">
        <!-- Top Filter Row -->
        <div class="row g-2 align-items-end">

            <!-- Search -->
            <div class="col-12 col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted border-end-0">
                        <i class="fas fa-search" style="font-size: 11px;"></i>
                    </span>
                    <input type="text" name="search" id="courierFilterSearch" class="form-control border-start-0 ps-0 shadow-none"
                           placeholder="Invoice, Tracking, Phone, Name...">
                </div>
            </div>

            <!-- Couriers -->
            <div class="col-6 col-sm-4 col-md-2">
                <select name="courier" id="courierFilterProvider" class="form-select form-select-sm shadow-none">
                    <option value="all">All Couriers</option>
                    @foreach($couriersList as $cName)
                        <option value="{{ $cName }}">{{ $cName }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status -->
            <div class="col-6 col-sm-4 col-md-2">
                <select name="status" id="courierFilterStatus" class="form-select form-select-sm shadow-none">
                    <option value="all">Delivery: All</option>
                    <option value="booked">Booked (In Review)</option>
                    <option value="in_transit">In Transit</option>
                    <option value="delivered">Delivered</option>
                    <option value="returned">Returned</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <!-- COD Status -->
            <div class="col-6 col-sm-4 col-md-2">
                <select name="cod_status" id="courierFilterCod" class="form-select form-select-sm shadow-none">
                    <option value="all">COD: All</option>
                    <option value="unsettled">Unsettled (Due)</option>
                    <option value="partial">Partially Settled</option>
                    <option value="settled">Fully Settled</option>
                </select>
            </div>

            <!-- Cost Status -->
            <div class="col-6 col-sm-6 col-md-1">
                <select name="cost_status" id="courierFilterCostStatus" class="form-select form-select-sm shadow-none">
                    <option value="all">Cost: All</option>
                    <option value="due">Cost Due</option>
                    <option value="paid">Cost Paid</option>
                </select>
            </div>

            <!-- Date Filter -->
            <div class="col-12 col-sm-6 col-md-2">
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

        <!-- Date & Sorting Row -->
        <div class="row g-2 align-items-center mt-2 pt-2 border-top">
            <div class="col-12 col-md-7 d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">

                <!-- Sort -->
                <div class="d-flex align-items-center gap-1 w-100 w-md-auto">
                    <span class="small fw-bold text-muted flex-shrink-0" style="font-size: 11px;">Sort:</span>
                    <select name="sort_by" id="courierSortBy" class="form-select form-select-sm shadow-none w-100" style="min-width: 110px;">
                        <option value="latest">Latest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="cod_high">Highest COD</option>
                        <option value="cod_low">Lowest COD</option>
                        <option value="weight_high">Heavy Weight</option>
                    </select>
                </div>

                <!-- Channel -->
                <div class="d-flex align-items-center gap-1 w-100 w-md-auto">
                    <span class="small fw-bold text-muted flex-shrink-0" style="font-size: 11px;">Channel:</span>
                    <select name="order_source" id="courierOrderSource" class="form-select form-select-sm shadow-none w-100" style="min-width: 100px;">
                        <option value="all">All Channels</option>
                        <option value="online">Online</option>
                        <option value="pos">POS</option>
                    </select>
                </div>

                <!-- Reset Button -->
                <button type="button" class="btn btn-light btn-compact border px-3 text-secondary shadow-xs text-nowrap ms-md-auto w-100 w-md-auto" onclick="resetAllFilters()">
                    <i class="fas fa-rotate-left me-1"></i>
                    <span>Reset</span>
                </button>
            </div>

            <!-- Custom Date Range -->
            <div class="col-12 col-md-5 d-flex align-items-center justify-content-start justify-content-md-end gap-2 flex-wrap {{ request('date_filter') == 'custom' ? '' : 'd-none' }}" id="customDateRow">
                <span class="small fw-bold text-muted" style="font-size: 11px;">From:</span>
                <input type="date" name="start_date" id="courierStartDate" class="form-control form-control-sm shadow-none bn-digit font-monospace" style="width: 125px;">
                <span class="small fw-bold text-muted" style="font-size: 11px;">To:</span>
                <input type="date" name="end_date" id="courierEndDate" class="form-control form-control-sm shadow-none bn-digit font-monospace" style="width: 125px;">
                <button type="button" class="btn btn-primary btn-compact shadow-xs" onclick="fetchCourierOrders()">Apply</button>
            </div>
        </div>
    </form>
</div>
    <!-- Bulk Actions -->
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

    <!-- Courier Orders Table -->
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

        <!-- Pagination -->
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

<!-- Modal 1: Alert -->
<div class="modal fade courier-modal-scope" id="courierAlertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow text-center p-3">
            <div class="modal-body p-1">
                <div class="mb-2" id="alertModalIcon">
                    <i class="fas fa-circle-check text-success fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1" id="alertModalTitle" style="font-size: 14px;">Notice</h6>
                <p class="text-muted small mb-3" id="alertModalMessage" style="font-size: 12px;">Action completed.</p>

                <button type="button" class="btn btn-primary btn-compact px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Manual Status Update -->
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

            <!-- Delivery Proof -->
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

<!-- Modal 3: Delete Confirmation -->
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
                    <i class="fas fa-shield-halved me-1"></i>
                    <strong>Safe Delete:</strong> The original customer order in Order Management will <strong>NOT</strong> be affected!
                </div>

                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-compact" id="btnExecuteConfirmAction">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 4: Proof Preview -->
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