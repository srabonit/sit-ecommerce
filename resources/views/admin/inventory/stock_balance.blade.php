
@extends('layouts.admin')

@section('title', 'Stock Balance & Inventory Valuation Sheet - ' . config('app.name', 'Shop Now BD'))

@section('admin_content')

<style>
    /* =========================================================
       STOCK BALANCE — MODERN ADMIN UI
       Scoped styles | Strict 5px radius | Responsive layout
    ========================================================= */

    #stockBalanceRoot {
        --sb-primary: #2563eb;
        --sb-primary-soft: #eff6ff;
        --sb-success: #16a34a;
        --sb-danger: #dc2626;
        --sb-warning: #d97706;
        --sb-purple: #7c3aed;
        --sb-text: #0f172a;
        --sb-muted: #64748b;
        --sb-border: #e2e8f0;
        --sb-surface: #ffffff;
        --sb-soft: #f8fafc;
        color: var(--sb-text);
        font-size: 13px;
    }

    /* Global radius enforcement */
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
    #stockBalanceRoot .section-header-wrap,
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

    #stockBalanceRoot .font-monospace,
    #stockBalanceRoot .font-monospace * {
        font-weight: 700 !important;
    }

    #stockBalanceRoot .min-w-0 {
        min-width: 0;
    }

    /* Header */
    #stockBalanceRoot .balance-header-wrap {
        gap: 12px;
    }

    #stockBalanceRoot .balance-title-icon {
        width: 38px;
        height: 38px;
        min-width: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--sb-primary-soft);
        color: var(--sb-primary);
        border: 1px solid #dbeafe;
        border-radius: 5px;
        font-size: 16px;
    }

    #stockBalanceRoot .balance-page-title {
        font-size: 18px;
        font-weight: 800;
        letter-spacing: -0.35px;
        color: var(--sb-text);
        line-height: 1.3;
    }

    #stockBalanceRoot .balance-page-subtitle {
        color: var(--sb-muted);
        font-size: 11.5px;
        line-height: 1.5;
    }

    #stockBalanceRoot .top-action-btns {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 7px;
    }

    #stockBalanceRoot .btn-compact {
        min-height: 32px;
        padding: 6px 12px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        white-space: nowrap;
        font-size: 11px !important;
        font-weight: 700 !important;
        line-height: 1.3 !important;
        transition: all .18s ease;
    }

    #stockBalanceRoot .btn-compact:hover {
        transform: translateY(-1px);
    }

    /* Cards and section headers */
    #stockBalanceRoot .card,
    #stockBalanceRoot .metric-card,
    #stockBalanceRoot .filter-toolbar-card {
        border: 1px solid var(--sb-border);
        background: var(--sb-surface);
        box-shadow: 0 2px 7px rgba(15, 23, 42, .035);
        margin-bottom: 16px;
    }

    #stockBalanceRoot .section-header-wrap {
        min-height: 43px;
        padding: 9px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        background: #fff;
        border-bottom: 1px solid var(--sb-border);
    }

    #stockBalanceRoot .section-heading {
        display: flex;
        align-items: center;
        gap: 7px;
        color: var(--sb-text);
        font-size: 12px;
        font-weight: 800;
        min-width: 0;
    }

    #stockBalanceRoot .section-heading > i {
        color: var(--sb-primary);
        font-size: 13px;
    }

    #stockBalanceRoot .collapse-toggle-btn {
        width: 26px;
        height: 26px;
        min-width: 26px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #475569;
        transition: all .2s ease;
    }

    #stockBalanceRoot .collapse-toggle-btn:hover {
        background: var(--sb-primary);
        border-color: var(--sb-primary);
        color: #fff;
    }

    #stockBalanceRoot .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    #stockBalanceRoot .collapse-icon {
        font-size: 10px;
        transition: transform .25s ease;
    }

    /* KPI cards */
    #stockBalanceRoot .metric-card {
        min-width: 0;
        min-height: 112px;
        height: 100%;
        padding: 13px 14px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform .18s ease, box-shadow .18s ease;
    }

    #stockBalanceRoot .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(15, 23, 42, .07);
    }

    #stockBalanceRoot .metric-card-blue {
        background: linear-gradient(145deg, #fff 0%, #f0f7ff 100%);
        border-color: #bfdbfe;
    }

    #stockBalanceRoot .metric-card-amber {
        background: linear-gradient(145deg, #fff 0%, #fffbeb 100%);
        border-color: #fde68a;
    }

    #stockBalanceRoot .metric-card-purple {
        background: linear-gradient(145deg, #fff 0%, #faf5ff 100%);
        border-color: #e9d5ff;
    }

    #stockBalanceRoot .metric-card-green {
        background: linear-gradient(145deg, #fff 0%, #f0fdf4 100%);
        border-color: #bbf7d0;
    }

    #stockBalanceRoot .stat-label-title {
        color: var(--sb-muted);
        font-size: 10.5px;
        font-weight: 800;
        letter-spacing: .025em;
        line-height: 1.35;
        text-transform: uppercase;
    }

    #stockBalanceRoot .stat-val-num {
        margin: 0;
        font-size: 19px;
        line-height: 1.3;
        letter-spacing: -.45px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #stockBalanceRoot .stat-val-num span {
        font-size: 10px !important;
        letter-spacing: 0;
    }

    #stockBalanceRoot .stat-sub-text {
        margin-top: 3px;
        font-size: 10px;
        line-height: 1.4;
    }

    #stockBalanceRoot .metric-icon-box {
        width: 34px;
        height: 34px;
        min-width: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }

    /* Filters */
    #stockBalanceRoot .form-label {
        display: block;
        margin-bottom: 5px;
        color: #334155;
        font-size: 11px;
        font-weight: 800;
    }

    #stockBalanceRoot .form-control,
    #stockBalanceRoot .form-select {
        min-height: 35px;
        padding: 6px 10px;
        color: var(--sb-text);
        background-color: #fff;
        border: 1px solid #cbd5e1;
        box-shadow: none;
        font-size: 12px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #stockBalanceRoot .form-control::placeholder {
        color: #94a3b8;
        font-weight: 400;
    }

    #stockBalanceRoot .form-control:focus,
    #stockBalanceRoot .form-select:focus {
        border-color: var(--sb-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .11);
        outline: none;
    }

    #stockBalanceRoot .input-group-text {
        min-width: 36px;
        justify-content: center;
        border-color: #cbd5e1;
        font-size: 11px;
    }

    #stockBalanceRoot .filter-toolbar-card {
        overflow: hidden;
    }

    /* Table */
    #stockBalanceRoot .balance-table-wrap {
        width: 100%;
    }

    #stockBalanceRoot .custom-balance-table {
        width: 100%;
        margin-bottom: 0;
    }

    #stockBalanceRoot .custom-balance-table thead th {
        padding: 10px 7px;
        color: #475569;
        background: #f8fafc;
        border-bottom: 1px solid #dbe3ee;
        font-size: 9.5px;
        font-weight: 800;
        letter-spacing: .035em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    #stockBalanceRoot .custom-balance-table tbody td {
        padding: 9px 7px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        font-size: 11px;
        vertical-align: middle;
        white-space: nowrap;
    }

    #stockBalanceRoot .custom-balance-table tbody tr {
        transition: background-color .15s ease;
    }

    #stockBalanceRoot .custom-balance-table tbody tr:hover {
        background: #f8fbff;
    }

    #stockBalanceRoot .custom-balance-table tbody tr:last-child td {
        border-bottom: 0;
    }

    @media (min-width: 992px) {
        #stockBalanceRoot .custom-balance-table {
            table-layout: fixed !important;
            width: 100% !important;
        }

        #stockBalanceRoot .pc-fit-container {
            overflow-x: hidden !important;
        }

        #stockBalanceRoot .custom-balance-table th,
        #stockBalanceRoot .custom-balance-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        #stockBalanceRoot .col-b-id    { width: 4% !important; }
        #stockBalanceRoot .col-b-prod  { width: 23% !important; }
        #stockBalanceRoot .col-b-type  { width: 8% !important; }
        #stockBalanceRoot .col-b-sku   { width: 11% !important; }
        #stockBalanceRoot .col-b-stock { width: 10% !important; }
        #stockBalanceRoot .col-b-cost  { width: 9% !important; }
        #stockBalanceRoot .col-b-price { width: 9% !important; }
        #stockBalanceRoot .col-b-val   { width: 11% !important; }
        #stockBalanceRoot .col-b-stat  { width: 8% !important; }
        #stockBalanceRoot .col-b-act   { width: 7% !important; }
    }

    /* Product thumbnail */
    #stockBalanceRoot .proof-thumb-box {
        position: relative;
        width: 34px;
        height: 34px;
        min-width: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
        cursor: pointer;
        background: #fff;
        border: 1px solid #dbe3ee;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #stockBalanceRoot .proof-thumb-box:hover {
        border-color: var(--sb-primary);
        box-shadow: 0 0 0 2px rgba(37, 99, 235, .08);
    }

    #stockBalanceRoot .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #stockBalanceRoot .proof-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, .52);
        opacity: 0;
        transition: opacity .15s ease;
    }

    #stockBalanceRoot .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    /* Pagination */
    #stockBalanceRoot #balancePaginationLinks {
        display: flex;
        align-items: center;
    }

    #stockBalanceRoot #balancePaginationLinks nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }

    #stockBalanceRoot #balancePaginationLinks nav > div.d-sm-none,
    #stockBalanceRoot #balancePaginationLinks nav > .d-sm-none {
        display: none !important;
    }

    #stockBalanceRoot #balancePaginationLinks nav > div.d-none,
    #stockBalanceRoot #balancePaginationLinks nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #stockBalanceRoot #balancePaginationLinks nav p,
    #stockBalanceRoot #balancePaginationLinks nav .small.text-muted,
    #stockBalanceRoot #balancePaginationLinks nav div:has(> p),
    #stockBalanceRoot #balancePaginationLinks nav > div.d-none > div:first-child,
    #stockBalanceRoot #balancePaginationLinks nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }

    #stockBalanceRoot #balancePaginationLinks .pagination {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    #stockBalanceRoot #balancePaginationLinks .page-item {
        display: inline-flex !important;
    }

    #stockBalanceRoot #balancePaginationLinks .page-item .page-link,
    #stockBalanceRoot #balancePaginationLinks .page-item span {
        min-width: 27px !important;
        height: 27px !important;
        padding: 0 7px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #475569 !important;
        background: #fff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 5px !important;
        font-size: 10.5px !important;
        font-weight: 700 !important;
        line-height: 1 !important;
        text-decoration: none !important;
        transition: all .15s ease !important;
    }

    #stockBalanceRoot #balancePaginationLinks .page-item .page-link:hover {
        color: var(--sb-primary) !important;
        background: #eff6ff !important;
        border-color: #93c5fd !important;
    }

    #stockBalanceRoot #balancePaginationLinks .page-item.active .page-link,
    #stockBalanceRoot #balancePaginationLinks .page-item.active span {
        color: #fff !important;
        background: var(--sb-primary) !important;
        border-color: var(--sb-primary) !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, .2) !important;
    }

    #stockBalanceRoot #balancePaginationLinks .page-item.disabled .page-link,
    #stockBalanceRoot #balancePaginationLinks .page-item.disabled span {
        opacity: .45 !important;
        background: #f8fafc !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }

    #stockBalanceRoot #balancePaginationLinks nav svg {
        width: 11px !important;
        height: 11px !important;
    }

    /* Footer */
    #stockBalanceRoot .balance-table-footer {
        min-height: 47px;
        background: #fafbfc;
        border-top: 1px solid var(--sb-border);
    }

    /* Loading overlay */
    #stockBalanceRoot #balanceLoadingCover {
        z-index: 50;
        border-radius: 5px;
        backdrop-filter: blur(1px);
    }

    /* Image modal */
    .balance-modal-scope .modal-content {
        border: 1px solid #e2e8f0 !important;
        overflow: hidden;
    }

    .balance-modal-scope .modal-header {
        padding: 12px 14px;
    }

    .balance-modal-scope .modal-body {
        padding: 12px;
    }

    .balance-modal-scope .modal-body img {
        max-width: 100%;
        border-radius: 5px !important;
    }

    /* Responsive */
    @media (max-width: 991.98px) {
        #stockBalanceRoot .pc-fit-container {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        #stockBalanceRoot .custom-balance-table {
            min-width: 900px !important;
            table-layout: auto !important;
        }
    }

    @media (max-width: 767.98px) {
        #stockBalanceRoot {
            padding-top: 10px !important;
        }

        #stockBalanceRoot .balance-header-wrap {
            align-items: stretch !important;
        }

        #stockBalanceRoot .balance-page-title {
            font-size: 15px !important;
        }

        #stockBalanceRoot .balance-page-subtitle {
            font-size: 10.5px;
        }

        #stockBalanceRoot .top-action-btns {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 6px;
        }

        #stockBalanceRoot .top-action-btns .btn {
            width: 100%;
            min-width: 0;
            min-height: 34px;
            padding: 6px 4px !important;
            font-size: 10px !important;
        }

        #stockBalanceRoot .section-header-wrap {
            padding: 9px 11px;
        }

        #stockBalanceRoot .section-heading {
            font-size: 11px;
        }

        #stockBalanceRoot .metric-card {
            min-height: 91px;
            padding: 10px !important;
        }

        #stockBalanceRoot .stat-label-title {
            font-size: 9px;
        }

        #stockBalanceRoot .stat-val-num {
            font-size: 14px;
        }

        #stockBalanceRoot .stat-sub-text {
            font-size: 9px;
        }

        #stockBalanceRoot .metric-icon-box {
            width: 28px;
            height: 28px;
            min-width: 28px;
            font-size: 11px;
        }

        #stockBalanceRoot .filter-toolbar-card .p-3 {
            padding: 11px !important;
        }

        #stockBalanceRoot .form-label {
            font-size: 10px;
        }

        #stockBalanceRoot .form-control,
        #stockBalanceRoot .form-select {
            min-height: 34px;
            font-size: 11px;
        }

        #stockBalanceRoot .balance-table-heading {
            font-size: 12px !important;
        }

        #stockBalanceRoot .balance-table-footer {
            align-items: center !important;
            padding: 9px 10px !important;
        }

        #stockBalanceRoot #balancePaginationLinks {
            width: 100%;
            justify-content: center;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding: 4px 0;
        }

        #stockBalanceRoot #balancePaginationLinks nav {
            justify-content: center !important;
            width: 100% !important;
        }

        #stockBalanceRoot #balancePaginationLinks .pagination {
            flex-wrap: nowrap !important;
            gap: 3px !important;
        }
    }

    @media (max-width: 380px) {
        #stockBalanceRoot .top-action-btns {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        #stockBalanceRoot .top-action-btns .btn:last-child {
            grid-column: 1 / -1;
        }

        #stockBalanceRoot .stat-val-num {
            font-size: 12px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="stockBalanceRoot">

    <!-- AJAX Loading Overlay -->
    <div id="balanceLoadingCover"
         class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none justify-content-center align-items-center">
        <div class="text-center">
            <div class="spinner-border text-primary" style="width: 2.2rem; height: 2.2rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="small text-muted fw-semibold mt-2">Updating inventory...</div>
        </div>
    </div>

    <!-- =====================================================
         1. PAGE HEADER & ACTIONS
    ====================================================== -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 balance-header-wrap">

        <div class="d-flex align-items-center gap-2 min-w-0">
            <div class="balance-title-icon">
                <i class="fas fa-scale-balanced"></i>
            </div>

            <div class="min-w-0">
                <h5 class="balance-page-title mb-1">
                    Stock Balance &amp; Current Inventory Sheet
                </h5>
                <p class="balance-page-subtitle mb-0">
                    Real-time SKU-level stock positions, purchase valuation, selling value, and stock status.
                </p>
            </div>
        </div>

        <div class="top-action-btns">
            <a href="{{ route('admin.inventory.stock_in') }}"
               class="btn btn-sm btn-success btn-compact shadow-sm">
                <i class="fas fa-arrow-down-long"></i>
                <span>Stock In</span>
            </a>

            <a href="{{ route('admin.inventory.stock_out') }}"
               class="btn btn-sm btn-danger btn-compact shadow-sm">
                <i class="fas fa-arrow-up-long"></i>
                <span>Stock Out</span>
            </a>

            <button type="button"
                    class="btn btn-sm btn-outline-success btn-compact bg-white shadow-sm"
                    onclick="exportBalanceCsv()">
                <i class="fas fa-file-excel text-success"></i>
                <span>Export CSV</span>
            </button>
        </div>
    </div>

    <!-- =====================================================
         2. INVENTORY KPI SUMMARY
    ====================================================== -->
    <div class="card border-0 shadow-sm mb-3">

        <div class="section-header-wrap">
            <div class="section-heading">
                <i class="fas fa-chart-pie"></i>
                <span>Inventory Portfolio &amp; Valuation Summary</span>
            </div>

            <button type="button"
                    class="collapse-toggle-btn shadow-sm no-print"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseBalanceKpis"
                    aria-expanded="true"
                    aria-controls="collapseBalanceKpis"
                    title="Toggle Summary">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseBalanceKpis">
            <div class="p-3">
                <div class="row g-2">

                    <!-- Total Items -->
                    <div class="col-6 col-xl-3">
                        <div class="metric-card metric-card-blue h-100">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">Total Line Items</span>
                                <div class="metric-icon-box bg-primary text-white shadow-sm">
                                    <i class="fas fa-boxes-stacked"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-primary font-monospace stat-val-num"
                                    id="val-total-items">
                                    {{ number_format($summary->total_items ?? 0) }}
                                </h4>
                                <small class="text-muted d-block text-truncate stat-sub-text">
                                    Active line items
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Available Stock -->
                    <div class="col-6 col-xl-3">
                        <div class="metric-card metric-card-amber h-100">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">Available Stock</span>
                                <div class="metric-icon-box text-dark shadow-sm" style="background-color: #f59e0b;">
                                    <i class="fas fa-cubes"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark font-monospace stat-val-num"
                                    id="val-total-units">
                                    {{ number_format($summary->total_units ?? 0) }}
                                    <span>Units</span>
                                </h4>
                                <small class="text-muted d-block text-truncate stat-sub-text">
                                    Cumulative units
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Purchase Valuation -->
                    <div class="col-6 col-xl-3">
                        <div class="metric-card metric-card-purple h-100">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">Purchase Valuation</span>
                                <div class="metric-icon-box text-white shadow-sm" style="background-color: #7c3aed;">
                                    <i class="fas fa-vault"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark font-monospace stat-val-num"
                                    id="val-total-purchase">
                                    ৳ {{ number_format($summary->total_purchase_value ?? 0, 2) }}
                                </h4>
                                <small class="text-muted d-block text-truncate stat-sub-text">
                                    Weighted Cost
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Retail Value -->
                    <div class="col-6 col-xl-3">
                        <div class="metric-card metric-card-green h-100">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">Retail Value</span>
                                <div class="metric-icon-box bg-success text-white shadow-sm">
                                    <i class="fas fa-tag"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success font-monospace stat-val-num"
                                    id="val-total-selling">
                                    ৳ {{ number_format($summary->total_selling_value ?? 0, 2) }}
                                </h4>
                                <small class="text-muted d-block text-truncate stat-sub-text">
                                    Selling Price
                                </small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- =====================================================
         3. FILTER TOOLBAR
    ====================================================== -->
    <div class="filter-toolbar-card mb-3">

        <div class="section-header-wrap">
            <div class="section-heading">
                <i class="fas fa-filter"></i>
                <span>Filter Stock Positions</span>
            </div>

            <button type="button"
                    class="collapse-toggle-btn shadow-sm no-print"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseBalanceFilter"
                    aria-expanded="true"
                    aria-controls="collapseBalanceFilter"
                    title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseBalanceFilter">
            <div class="p-3">
                <form id="balance-filter-form" onsubmit="return false;">
                    <div class="row g-2 align-items-end">

                        <!-- Search -->
                        <div class="col-12 col-md-5">
                            <label class="form-label" for="balance-search">
                                <i class="fas fa-search text-primary me-1"></i>
                                Search Product / Variant
                            </label>

                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0">
                                    <i class="fas fa-search"></i>
                                </span>

                                <input type="text"
                                       name="search"
                                       id="balance-search"
                                       class="form-control border-start-0 ps-0 shadow-none"
                                       placeholder="Product title, SKU, ID, color, or size..."
                                       value="{{ request('search') }}"
                                       onkeyup="handleSearchKeyup(event)">
                            </div>
                        </div>

                        <!-- Stock Status -->
                        <div class="col-6 col-md-4">
                            <label class="form-label" for="balance-stock-status">
                                <i class="fas fa-layer-group text-warning me-1"></i>
                                Stock Status Filter
                            </label>

                            <select name="stock_status"
                                    id="balance-stock-status"
                                    class="form-select shadow-none font-monospace"
                                    onchange="applyBalanceFilter()">

                                <option value="all"
                                    {{ !request()->filled('stock_status') || request('stock_status') === 'all' ? 'selected' : '' }}>
                                    All Items
                                </option>

                                <option value="in_stock"
                                    {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>
                                    In Stock (Healthy)
                                </option>

                                <option value="low_stock"
                                    {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>
                                    Low Stock (&le; Alert Level)
                                </option>

                                <option value="out_of_stock"
                                    {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>
                                    Out of Stock (Zero / Negative)
                                </option>
                            </select>
                        </div>

                        <!-- Apply -->
                        <div class="col-6 col-md-3">
                            <button type="button"
                                    class="btn btn-dark btn-compact w-100 fw-bold shadow-sm"
                                    onclick="applyBalanceFilter()">
                                <i class="fas fa-filter"></i>
                                <span>Apply Filter</span>
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- =====================================================
         4. STOCK BALANCE TABLE
    ====================================================== -->
    <div class="card p-0 mb-3 overflow-hidden shadow-sm">

        <!-- Table Header -->
        <div class="p-2 px-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 bg-white">

            <div class="min-w-0">
                <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2 balance-table-heading">
                    <i class="fas fa-table-list text-primary"></i>
                    <span>Real-Time Stock Balance Sheet</span>
                </h6>

                <small class="text-muted font-monospace" style="font-size: 10px;" id="balance-entries-text">
                    Showing {{ $items->firstItem() ?? 0 }}
                    to {{ $items->lastItem() ?? 0 }}
                    of {{ $items->total() }} items
                </small>
            </div>

            <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0 font-monospace"
                       for="balance-per-page"
                       style="font-size: 10px;">
                    Show:
                </label>

                <select name="per_page"
                        id="balance-per-page"
                        class="form-select shadow-none font-monospace"
                        style="width: 78px; min-height: 31px; font-size: 11px;"
                        onchange="applyBalanceFilter()">

                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ $perPage == 100000 ? 'selected' : '' }}>All</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive pc-fit-container balance-table-wrap">
            <table class="table align-middle mb-0 custom-balance-table" id="balance-table">

                <thead>
                    <tr>
                        <th class="ps-3 col-b-id">ID</th>
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

        <!-- Pagination Footer -->
        <div class="balance-table-footer p-2 px-3 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2"
             id="balance-pagination-container">

            <div class="small text-muted font-monospace text-center text-sm-start"
                 style="font-size: 10.5px;">
                Total <strong>{{ $items->total() }}</strong> records matching filter
            </div>

            <div id="balancePaginationLinks">
                {{ $items->links('pagination::bootstrap-5') }}
            </div>
        </div>

    </div>
</div>

<!-- =========================================================
     IMAGE ZOOM MODAL
========================================================== -->
<div class="modal fade balance-modal-scope"
     id="balanceImageZoomModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 108000;">

    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content shadow-lg text-center p-2">

            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12px;" id="balanceZoomModalTitle">
                    <i class="fas fa-image text-primary me-1"></i>
                    Product / Variant Preview
                </span>

                <button type="button"
                        class="btn-close btn-sm shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <div class="modal-body p-2">
                <img id="balanceZoomModalImgTag"
                     src=""
                     alt="Product Photo"
                     class="img-fluid border"
                     style="max-height: 480px; width: 100%; object-fit: contain; border-radius: 5px;">
            </div>

        </div>
    </div>
</div>

<script>
    /* =========================================================
       STOCK BALANCE — JAVASCRIPT
       Existing AJAX, media picker, pagination and CSV behavior
    ========================================================= */

    /**
     * Search on Enter key.
     */
    function handleSearchKeyup(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            applyBalanceFilter();
        }
    }

    /**
     * Product / Variant image zoom.
     */
    function zoomBalanceImage(url, title = 'Product Image') {
        const titleEl = document.getElementById('balanceZoomModalTitle');
        const imgTag = document.getElementById('balanceZoomModalImgTag');
        const modalEl = document.getElementById('balanceImageZoomModal');

        if (url && imgTag && modalEl && typeof bootstrap !== 'undefined') {
            if (titleEl) {
                titleEl.innerText = title;
            }

            imgTag.src = url;

            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    /**
     * Normalize Media Library response.
     */
    function extractMediaItem(mediaInput) {
        if (!mediaInput) return null;

        const item = Array.isArray(mediaInput) ? mediaInput[0] : mediaInput;

        if (!item || typeof item !== 'object') return null;

        const rawPath = item.file_path || item.path || item.url || '';
        const rawUrl = item.url || item.file_path || item.path || '';

        if (!rawPath || rawPath === 'undefined') return null;

        return {
            path: String(rawPath).trim(),
            url: String(rawUrl).trim()
        };
    }

    /**
     * Select and update product image using Global Media Picker.
     */
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

                            body: JSON.stringify({
                                image_path: picked.path
                            })
                        })
                        .then(res => {
                            if (!res.ok) {
                                throw new Error('Unable to update product image.');
                            }

                            return res.json();
                        })
                        .then(res => {
                            applyBalanceFilter();
                        })
                        .catch(error => {
                            console.error('Product image update error:', error);
                            applyBalanceFilter();
                        });
                    }
                }
            });

        } else {
            alert('Global Media Library is loading, please try again.');
        }
    }

    /**
     * Apply stock balance filters using AJAX.
     */
    function applyBalanceFilter(targetUrl = null) {
        const form = document.getElementById('balance-filter-form');

        if (!form) return;

        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        const perPageElement = document.getElementById('balance-per-page');

        if (perPageElement) {
            params.set('per_page', perPageElement.value);
        }

        const cover = document.getElementById('balanceLoadingCover');

        if (cover) {
            cover.classList.remove('d-none');
            cover.classList.add('d-flex');
        }

        const url = targetUrl || (
            "{{ route('admin.inventory.balance') }}?" + params.toString()
        );

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) {
                throw new Error('Unable to load stock balance data.');
            }

            return res.json();
        })
        .then(data => {

            if (data.success) {

                const tbody = document.getElementById('balance-tbody');

                if (tbody) {
                    tbody.innerHTML = data.table_html;
                }

                const paginationContainer = document.getElementById('balance-pagination-container');

                if (paginationContainer) {
                    paginationContainer.innerHTML = `
                        <div class="small text-muted font-monospace text-center text-sm-start"
                             style="font-size: 10.5px;">
                            Total <strong>${Number(data.total_count ?? 0).toLocaleString()}</strong>
                            records matching filter
                        </div>

                        <div id="balancePaginationLinks">
                            ${data.pagination_html ?? ''}
                        </div>
                    `;
                }

                /* Update KPI summary */
                if (data.summary) {
                    const summary = data.summary;

                    const totalItems = document.getElementById('val-total-items');
                    const totalUnits = document.getElementById('val-total-units');
                    const totalPurchase = document.getElementById('val-total-purchase');
                    const totalSelling = document.getElementById('val-total-selling');

                    if (totalItems) {
                        totalItems.innerText = Number(summary.total_items ?? 0).toLocaleString();
                    }

                    if (totalUnits) {
                        totalUnits.innerHTML = `
                            ${Number(summary.total_units ?? 0).toLocaleString()}
                            <span>Units</span>
                        `;
                    }

                    if (totalPurchase) {
                        totalPurchase.innerText = `৳ ${Number(summary.total_purchase_value ?? 0).toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        })}`;
                    }

                    if (totalSelling) {
                        totalSelling.innerText = `৳ ${Number(summary.total_selling_value ?? 0).toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        })}`;
                    }
                }

                /* Update showing text if provided by server */
                const entriesText = document.getElementById('balance-entries-text');

                if (entriesText && data.first_item !== undefined) {
                    entriesText.innerText =
                        `Showing ${data.first_item ?? 0} to ${data.last_item ?? 0} of ${data.total_count ?? 0} items`;
                }
            }
        })
        .catch(error => {
            console.error('Balance filter error:', error);
        })
        .finally(() => {
            if (cover) {
                cover.classList.add('d-none');
                cover.classList.remove('d-flex');
            }
        });
    }

    /**
     * AJAX pagination.
     * Delegated event keeps working after pagination HTML replacement.
     */
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#balancePaginationLinks a');

        if (pageLink) {
            e.preventDefault();

            const href = pageLink.getAttribute('href');

            if (
                href &&
                href !== '#' &&
                !href.startsWith('javascript:')
            ) {
                applyBalanceFilter(href);
            }
        }
    });

    /**
     * Export current filtered stock balance as CSV.
     */
    function exportBalanceCsv() {
        const form = document.getElementById('balance-filter-form');

        if (!form) return;

        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        params.append('export', 'csv');

        const url = "{{ route('admin.inventory.balance') }}?" + params.toString();

        window.open(url, '_blank');
    }
</script>

@endsection
