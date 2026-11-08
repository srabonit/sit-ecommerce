@extends('layouts.admin')

@section('title', 'Date-wise Inventory & COGS Profit Reports - ' . config('app.name', 'Shop Now BD'))

@section('admin_content')

<style>
    /* =========================================================
       INVENTORY REPORT — MODERN UI
       Scoped Styles | Strict 5px Radius
    ========================================================= */

    #reportRoot {
        --rp-primary: #2563eb;
        --rp-primary-soft: #eff6ff;
        --rp-success: #16a34a;
        --rp-success-soft: #f0fdf4;
        --rp-danger: #dc2626;
        --rp-danger-soft: #fef2f2;
        --rp-warning: #d97706;
        --rp-warning-soft: #fffbeb;
        --rp-purple: #7c3aed;
        --rp-purple-soft: #faf5ff;
        --rp-dark: #0f172a;
        --rp-muted: #64748b;
        --rp-border: #e2e8f0;
        --rp-surface: #ffffff;
        --rp-bg: #f8fafc;
        color: var(--rp-dark);
        font-size: 13px;
    }

    /* Strict Radius */
    #reportRoot .btn,
    #reportRoot .btn-sm,
    #reportRoot button,
    #reportRoot .form-control,
    #reportRoot .form-select,
    #reportRoot .badge,
    #reportRoot .alert,
    #reportRoot .input-group-text,
    #reportRoot .card,
    #reportRoot .metric-card,
    #reportRoot .filter-toolbar-card,
    #reportRoot .metric-icon-box,
    #reportRoot .page-link,
    #reportRoot .page-item span,
    #reportRoot .collapse-toggle-btn,
    #reportRoot .report-icon,
    #reportRoot .report-status,
    .report-modal-scope .modal-content,
    .report-modal-scope .btn {
        border-radius: 5px !important;
    }

    #reportRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #reportRoot .input-group > .form-control,
    #reportRoot .input-group > .form-select {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    #reportRoot *,
    #reportRoot *::before,
    #reportRoot *::after {
        box-sizing: border-box;
    }

    #reportRoot .font-monospace {
        font-weight: 700 !important;
    }

    #reportRoot .min-w-0 {
        min-width: 0;
    }

    /* =========================================================
       HEADER
    ========================================================= */

    #reportRoot .report-header {
        gap: 12px;
    }

    #reportRoot .report-title-icon {
        width: 36px;
        height: 36px;
        min-width: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px !important;
        background: var(--rp-primary-soft);
        color: var(--rp-primary);
        font-size: 15px;
    }

    #reportRoot .report-header h5 {
        color: var(--rp-dark);
        font-size: 18px;
        font-weight: 800;
        letter-spacing: -0.35px;
        line-height: 1.35;
    }

    #reportRoot .report-header-description {
        color: var(--rp-muted);
        font-size: 11.5px;
        line-height: 1.6;
        margin-top: 3px;
        max-width: 760px;
    }

    #reportRoot .header-actions-wrap {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 7px;
    }

    #reportRoot .btn-compact {
        min-height: 32px;
        padding: 6px 12px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        white-space: nowrap;
        font-size: 11px !important;
        line-height: 1.3 !important;
        font-weight: 700 !important;
        border-radius: 5px !important;
        transition: all 0.18s ease;
    }

    #reportRoot .btn-compact:hover {
        transform: translateY(-1px);
    }

    #reportRoot .btn-compact:active {
        transform: translateY(0);
    }

    /* =========================================================
       COMMON CARDS
    ========================================================= */

    #reportRoot .card,
    #reportRoot .metric-card,
    #reportRoot .filter-toolbar-card {
        border: 1px solid var(--rp-border);
        background: var(--rp-surface);
        border-radius: 5px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.035);
        margin-bottom: 15px;
    }

    #reportRoot .report-card-header {
        min-height: 43px;
        padding: 10px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        background: #fff;
        border-bottom: 1px solid var(--rp-border);
    }

    #reportRoot .report-section-title {
        display: flex;
        align-items: center;
        gap: 7px;
        color: var(--rp-dark);
        font-size: 12px;
        font-weight: 800;
        line-height: 1.4;
    }

    #reportRoot .report-section-title > i {
        color: var(--rp-primary);
        font-size: 13px;
    }

    #reportRoot .report-section-subtitle {
        display: block;
        margin-top: 3px;
        color: var(--rp-muted);
        font-size: 10px;
        line-height: 1.4;
    }

    /* =========================================================
       COLLAPSE BUTTON
    ========================================================= */

    #reportRoot .collapse-toggle-btn {
        width: 27px;
        height: 27px;
        min-width: 27px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #cbd5e1;
        color: #64748b;
        transition: all 0.2s ease;
    }

    #reportRoot .collapse-toggle-btn:hover {
        background: var(--rp-primary);
        border-color: var(--rp-primary);
        color: #fff;
    }

    #reportRoot .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    #reportRoot .collapse-icon {
        font-size: 10px;
        transition: transform 0.25s ease;
    }

    /* =========================================================
       FILTER TOOLBAR
    ========================================================= */

    #reportRoot .filter-toolbar-card {
        overflow: hidden;
    }

    #reportRoot .filter-toolbar-card .filter-content {
        padding: 15px;
    }

    #reportRoot .form-label {
        display: block;
        margin-bottom: 5px;
        color: #334155;
        font-size: 11px;
        font-weight: 750;
    }

    #reportRoot .form-control,
    #reportRoot .form-select {
        min-height: 36px;
        padding: 6px 10px;
        color: #0f172a;
        background-color: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 5px !important;
        box-shadow: none;
        font-size: 12px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    #reportRoot .form-control:focus,
    #reportRoot .form-select:focus {
        border-color: var(--rp-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
        outline: none;
    }

    #reportRoot .filter-period-select {
        font-weight: 700;
    }

    #reportRoot .filter-generate-btn {
        min-height: 36px;
        font-size: 11px !important;
        letter-spacing: 0.1px;
    }

    #reportRoot .filter-hint {
        margin-top: 9px;
        color: #94a3b8;
        font-size: 10px;
    }

    /* =========================================================
       EXECUTIVE METRIC CARDS
    ========================================================= */

    #reportRoot .metric-card {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        min-height: 108px;
        padding: 13px 14px;
        overflow: hidden;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    #reportRoot .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(15, 23, 42, 0.065);
    }

    #reportRoot .metric-card::after {
        content: "";
        position: absolute;
        top: -23px;
        right: -22px;
        width: 70px;
        height: 70px;
        border-radius: 50%;
        opacity: 0.06;
        background: currentColor;
        pointer-events: none;
    }

    #reportRoot .metric-card-blue {
        background: linear-gradient(145deg, #fff 0%, #f0f9ff 100%);
        border-color: #bae6fd;
    }

    #reportRoot .metric-card-green {
        background: linear-gradient(145deg, #fff 0%, #f0fdf4 100%);
        border-color: #bbf7d0;
    }

    #reportRoot .metric-card-red {
        background: linear-gradient(145deg, #fff 0%, #fef2f2 100%);
        border-color: #fecaca;
    }

    #reportRoot .metric-card-amber {
        background: linear-gradient(145deg, #fff 0%, #fffbeb 100%);
        border-color: #fde68a;
    }

    #reportRoot .metric-card-purple {
        background: linear-gradient(145deg, #fff 0%, #faf5ff 100%);
        border-color: #e9d5ff;
    }

    #reportRoot .stat-label-title {
        display: block;
        color: #64748b;
        font-size: 10.5px;
        font-weight: 800;
        letter-spacing: 0.025em;
        line-height: 1.4;
    }

    #reportRoot .stat-val-num {
        margin: 0;
        font-size: 19px;
        line-height: 1.35;
        font-weight: 850;
        letter-spacing: -0.6px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #reportRoot .stat-val-num span {
        font-size: 10px !important;
        font-weight: 700;
        letter-spacing: 0;
    }

    #reportRoot .stat-sub-text {
        display: block;
        margin-top: 5px;
        color: #64748b;
        font-size: 10px;
        line-height: 1.5;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #reportRoot .metric-icon-box {
        width: 33px;
        height: 33px;
        min-width: 33px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px !important;
        font-size: 13px;
        box-shadow: 0 2px 5px rgba(15, 23, 42, 0.08);
    }

    /* =========================================================
       REPORT TABLE
    ========================================================= */

    #reportRoot .report-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #reportRoot .report-table thead th {
        padding: 10px 7px;
        color: #475569;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        font-size: 9.5px;
        font-weight: 850;
        letter-spacing: 0.035em;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    #reportRoot .report-table tbody td {
        padding: 9px 7px;
        color: #334155;
        background: #fff;
        border-bottom: 1px solid #f1f5f9;
        font-size: 10.5px;
        vertical-align: middle;
        white-space: nowrap;
    }

    #reportRoot .report-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #reportRoot .report-table tbody tr:hover td {
        background: #f8fbff;
    }

    #reportRoot .report-table .font-monospace {
        font-size: 10.5px;
    }

    #reportRoot .pc-fit-container {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* =========================================================
       TABLE COLUMN WIDTHS — DESKTOP
    ========================================================= */

    @media (min-width: 992px) {
        #reportRoot .report-table {
            table-layout: fixed !important;
            width: 100% !important;
        }

        #reportRoot .pc-fit-container {
            overflow-x: hidden !important;
        }

        #reportRoot .report-table th,
        #reportRoot .report-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        #reportRoot .col-rp-id     { width: 4% !important; }
        #reportRoot .col-rp-prod   { width: 22% !important; }
        #reportRoot .col-rp-sku    { width: 8% !important; }
        #reportRoot .col-rp-inqty  { width: 8% !important; }
        #reportRoot .col-rp-inval  { width: 9% !important; }
        #reportRoot .col-rp-outqty { width: 8% !important; }
        #reportRoot .col-rp-sqty   { width: 8% !important; }
        #reportRoot .col-rp-rev    { width: 10% !important; }
        #reportRoot .col-rp-cogs   { width: 9% !important; }
        #reportRoot .col-rp-profit { width: 9% !important; }
        #reportRoot .col-rp-action { width: 5% !important; }
    }

    /* =========================================================
       PAGINATION
    ========================================================= */

    #reportRoot #report-pagination-container nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }

    #reportRoot #report-pagination-container nav > div.d-sm-none,
    #reportRoot #report-pagination-container nav > .d-sm-none {
        display: none !important;
    }

    #reportRoot #report-pagination-container nav > div.d-none,
    #reportRoot #report-pagination-container nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #reportRoot #report-pagination-container nav p,
    #reportRoot #report-pagination-container nav .small.text-muted,
    #reportRoot #report-pagination-container nav div:has(> p),
    #reportRoot #report-pagination-container nav > div.d-none > div:first-child,
    #reportRoot #report-pagination-container nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }

    #reportRoot #report-pagination-container .pagination {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    #reportRoot #report-pagination-container .page-item {
        display: inline-flex !important;
    }

    #reportRoot #report-pagination-container .page-item .page-link,
    #reportRoot #report-pagination-container .page-item span {
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
        transition: all 0.15s ease !important;
    }

    #reportRoot #report-pagination-container .page-item .page-link:hover {
        color: var(--rp-primary) !important;
        background: var(--rp-primary-soft) !important;
        border-color: #93c5fd !important;
    }

    #reportRoot #report-pagination-container .page-item.active .page-link,
    #reportRoot #report-pagination-container .page-item.active span {
        color: #fff !important;
        background: var(--rp-primary) !important;
        border-color: var(--rp-primary) !important;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.18) !important;
    }

    #reportRoot #report-pagination-container .page-item.disabled .page-link,
    #reportRoot #report-pagination-container .page-item.disabled span {
        opacity: 0.45 !important;
        background: #f8fafc !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }

    #reportRoot #report-pagination-container nav svg {
        width: 11px !important;
        height: 11px !important;
    }

    #reportRoot .pagination-info {
        color: #64748b;
        font-size: 10.5px;
    }

    #reportRoot .pagination-info strong {
        color: #334155;
    }

    /* =========================================================
       LOADING OVERLAY
    ========================================================= */

    #reportRoot #reportLoadingCover {
        z-index: 50;
        border-radius: 5px !important;
        backdrop-filter: blur(1px);
    }

    #reportRoot #reportLoadingCover .spinner-border {
        width: 2.2rem;
        height: 2.2rem;
        border-width: 3px;
    }

    /* =========================================================
       REPORT TABLE HEADER AREA
    ========================================================= */

    #reportRoot .report-table-heading {
        min-height: 53px;
        padding: 10px 14px;
        background: #fff;
        border-bottom: 1px solid var(--rp-border);
    }

    #reportRoot .report-table-heading h6 {
        color: var(--rp-dark);
        font-size: 12px;
        font-weight: 850;
        line-height: 1.4;
    }

    #reportRoot .report-count-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 9px !important;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        font-size: 10px;
        font-weight: 750;
    }

    /* =========================================================
       IMAGE MODAL
    ========================================================= */

    .report-modal-scope .modal-content {
        border: 1px solid #e2e8f0 !important;
        border-radius: 5px !important;
        overflow: hidden;
    }

    .report-modal-scope .modal-header {
        padding: 12px 15px;
    }

    .report-modal-scope .modal-body {
        padding: 12px;
    }

    .report-modal-scope img {
        border-radius: 5px !important;
    }

    /* =========================================================
       TABLET
    ========================================================= */

    @media (max-width: 991.98px) {
        #reportRoot .report-table {
            min-width: 860px;
            table-layout: auto !important;
        }

        #reportRoot .pc-fit-container {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        #reportRoot .report-table thead th,
        #reportRoot .report-table tbody td {
            overflow: visible;
            text-overflow: clip;
        }
    }

    /* =========================================================
       MOBILE
    ========================================================= */

    @media (max-width: 767.98px) {
        #reportRoot {
            padding-top: 10px !important;
        }

        #reportRoot .report-header {
            align-items: flex-start !important;
        }

        #reportRoot .report-header h5 {
            font-size: 15px !important;
            line-height: 1.4;
        }

        #reportRoot .report-title-icon {
            width: 32px;
            height: 32px;
            min-width: 32px;
            font-size: 13px;
        }

        #reportRoot .report-header-description {
            font-size: 10.5px;
            line-height: 1.5;
        }

        #reportRoot .header-actions-wrap {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 6px;
        }

        #reportRoot .header-actions-wrap .btn {
            width: 100%;
            min-width: 0;
            padding: 7px 5px !important;
            font-size: 10px !important;
        }

        #reportRoot .report-card-header {
            padding: 9px 11px;
        }

        #reportRoot .report-section-title {
            font-size: 11px;
        }

        #reportRoot .filter-toolbar-card .filter-content {
            padding: 11px;
        }

        #reportRoot .form-control,
        #reportRoot .form-select {
            min-height: 35px;
            font-size: 11px;
        }

        #reportRoot .metric-card {
            min-height: 91px;
            padding: 10px !important;
        }

        #reportRoot .stat-label-title {
            font-size: 9.5px;
        }

        #reportRoot .stat-val-num {
            font-size: 14px;
            letter-spacing: -0.3px;
        }

        #reportRoot .stat-sub-text {
            font-size: 9px;
        }

        #reportRoot .metric-icon-box {
            width: 27px;
            height: 27px;
            min-width: 27px;
            font-size: 11px;
        }

        #reportRoot .report-table-heading {
            padding: 10px 11px;
        }

        #reportRoot .report-table-heading h6 {
            font-size: 11.5px;
        }

        #reportRoot .report-table {
            min-width: 860px !important;
            table-layout: auto !important;
        }

        #reportRoot .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        #reportRoot .report-table thead th {
            padding: 9px 7px;
            font-size: 9px;
        }

        #reportRoot .report-table tbody td {
            padding: 8px 7px;
            font-size: 10px;
        }

        #reportRoot #report-pagination-container {
            padding: 10px !important;
        }

        #reportRoot #report-pagination-container nav {
            justify-content: center !important;
        }

        #reportRoot .pagination-info {
            text-align: center;
            font-size: 10px;
        }
    }

    @media (max-width: 380px) {
        #reportRoot .report-header h5 {
            font-size: 14px !important;
        }

        #reportRoot .stat-val-num {
            font-size: 13px;
        }

        #reportRoot .metric-card {
            padding: 8px !important;
        }

        #reportRoot .header-actions-wrap .btn {
            font-size: 9.5px !important;
        }
    }

    /* =========================================================
       PRINT
    ========================================================= */

    @media print {
        #reportRoot {
            padding: 0 !important;
        }

        #reportRoot .header-actions-wrap,
        #reportRoot .collapse-toggle-btn,
        #reportRoot .filter-toolbar-card,
        #reportRoot #reportLoadingCover,
        #reportRoot #report-pagination-container {
            display: none !important;
        }

        #reportRoot .collapse:not(.show) {
            display: block !important;
        }

        #reportRoot .card,
        #reportRoot .metric-card {
            box-shadow: none !important;
            break-inside: avoid;
        }

        #reportRoot .pc-fit-container {
            overflow: visible !important;
        }

        #reportRoot .report-table {
            min-width: 0 !important;
            table-layout: auto !important;
        }

        #reportRoot .report-table th,
        #reportRoot .report-table td {
            overflow: visible !important;
            white-space: normal !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="reportRoot">

    {{-- AJAX Loading Overlay --}}
    <div id="reportLoadingCover"
         class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none justify-content-center align-items-center">

        <div class="text-center">
            <div class="spinner-border text-primary mb-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="small fw-bold text-primary">Generating Report...</div>
        </div>
    </div>

    {{-- =====================================================
         1. PAGE HEADER & ACTIONS
    ====================================================== --}}

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 report-header">

        <div class="d-flex align-items-start gap-2">

            <div class="report-title-icon">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>

            <div class="min-w-0">
                <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                    <span>Inventory Valuation, COGS &amp; Profit Reports</span>
                </h5>

                <small class="report-header-description d-block">
                    Audited timeline showing Opening Stock, Inbound consignments,
                    Outbound write-offs, COGS, and Closing Balance.
                </small>
            </div>

        </div>

        <div class="header-actions-wrap">

            <a href="{{ route('admin.inventory.balance') }}"
               class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">

                <i class="fas fa-scale-balanced"></i>
                <span>Stock Balance</span>
            </a>

            <a href="{{ route('admin.inventory.movements') }}"
               class="btn btn-sm btn-outline-primary btn-compact shadow-xs fw-semibold">

                <i class="fas fa-clipboard-list"></i>
                <span>Full Ledger</span>
            </a>

            <button type="button"
                    class="btn btn-sm btn-outline-success btn-compact shadow-xs fw-bold"
                    onclick="exportReportCsv()">

                <i class="fas fa-file-excel"></i>
                <span>Export CSV</span>
            </button>

        </div>

    </div>


    {{-- =====================================================
         2. DATE FILTER TOOLBAR
    ====================================================== --}}

    <div class="filter-toolbar-card">

        <div class="report-card-header">

            <div class="report-section-title">
                <i class="fas fa-calendar-days"></i>
                <span>Report Period &amp; Date Parameters</span>
            </div>

            <button type="button"
                    class="collapse-toggle-btn shadow-xs no-print"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseReportFilter"
                    aria-expanded="true"
                    aria-controls="collapseReportFilter"
                    title="Toggle Filters">

                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>

        </div>

        <div class="collapse show" id="collapseReportFilter">

            <div class="filter-content">

                <form id="report-filter-form" onsubmit="return false;">

                    <div class="row g-2 align-items-end">

                        {{-- Period Preset --}}
                        <div class="col-12 col-sm-6 col-md-3">

                            <label class="form-label" for="report-period-select">
                                <i class="fas fa-calendar-check me-1 text-primary"></i>
                                Period Preset
                            </label>

                            <select name="period"
                                    id="report-period-select"
                                    class="form-select font-monospace filter-period-select shadow-none"
                                    onchange="handlePeriodPresetChange()">

                                <option value="today" {{ $period === 'today' ? 'selected' : '' }}>
                                    Today
                                </option>

                                <option value="yesterday" {{ $period === 'yesterday' ? 'selected' : '' }}>
                                    Yesterday
                                </option>

                                <option value="this_week" {{ $period === 'this_week' ? 'selected' : '' }}>
                                    This Week
                                </option>

                                <option value="last_week" {{ $period === 'last_week' ? 'selected' : '' }}>
                                    Last Week
                                </option>

                                <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>
                                    This Month
                                </option>

                                <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>
                                    Last Month
                                </option>

                                <option value="this_year" {{ $period === 'this_year' ? 'selected' : '' }}>
                                    This Year
                                </option>

                                <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>
                                    Custom Date Range
                                </option>

                            </select>

                        </div>


                        {{-- Start Date --}}
                        <div class="col-6 col-sm-6 col-md-3" id="wrapper-start-date">

                            <label class="form-label" for="report-start-date">
                                <i class="far fa-calendar-alt me-1 text-muted"></i>
                                Start Date
                            </label>

                            <input type="date"
                                   name="start_date"
                                   id="report-start-date"
                                   class="form-control font-monospace shadow-none"
                                   value="{{ $startDate }}">

                        </div>


                        {{-- End Date --}}
                        <div class="col-6 col-sm-6 col-md-3" id="wrapper-end-date">

                            <label class="form-label" for="report-end-date">
                                <i class="far fa-calendar-check me-1 text-muted"></i>
                                End Date
                            </label>

                            <input type="date"
                                   name="end_date"
                                   id="report-end-date"
                                   class="form-control font-monospace shadow-none"
                                   value="{{ $endDate }}">

                        </div>


                        {{-- Generate Report --}}
                        <div class="col-12 col-md-3">

                            <button type="button"
                                    class="btn btn-dark btn-compact filter-generate-btn w-100 fw-bold shadow-xs"
                                    onclick="applyReportFilter()">

                                <i class="fas fa-filter"></i>
                                <span>Generate Report</span>
                            </button>

                        </div>

                    </div>

                    <div class="filter-hint">
                        <i class="fas fa-circle-info me-1"></i>
                        Select a predefined period or choose a custom date range to generate the inventory report.
                    </div>

                </form>

            </div>

        </div>

    </div>


    {{-- =====================================================
         3. EXECUTIVE TIMELINE & COGS SUMMARY
    ====================================================== --}}

    <div class="card border-0 shadow-sm mb-3">

        <div class="report-card-header">

            <div>
                <div class="report-section-title">
                    <i class="fas fa-chart-line"></i>
                    <span>Executive Timeline Balance &amp; COGS Summary</span>
                </div>

                <small class="report-section-subtitle">
                    Opening and closing valuation with stock movement and gross profit overview.
                </small>
            </div>

            <button type="button"
                    class="collapse-toggle-btn shadow-xs no-print"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseReportTimeline"
                    aria-expanded="true"
                    aria-controls="collapseReportTimeline"
                    title="Toggle Timeline">

                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>

        </div>


        <div class="collapse show" id="collapseReportTimeline">

            <div class="p-2 p-md-3">

                <div class="row g-2">

                    {{-- Opening Stock --}}
                    <div class="col-6 col-md-4 col-xl">

                        <div class="card metric-card metric-card-blue h-100">

                            <div class="d-flex justify-content-between align-items-start mb-2">

                                <span class="stat-label-title text-truncate">
                                    Opening Stock
                                </span>

                                <div class="metric-icon-box bg-primary text-white">
                                    <i class="fas fa-box-open"></i>
                                </div>

                            </div>

                            <div class="min-w-0">

                                <h5 class="fw-bold text-primary font-monospace stat-val-num"
                                    id="val-opening-qty">

                                    {{ number_format($ledger['opening_qty']) }}
                                    <span>Units</span>

                                </h5>

                                <small class="stat-sub-text font-monospace">
                                    Valuation:
                                    <strong class="text-dark" id="val-opening-val">
                                        ৳ {{ number_format($ledger['opening_value'], 2) }}
                                    </strong>
                                </small>

                            </div>

                        </div>

                    </div>


                    {{-- Stock In --}}
                    <div class="col-6 col-md-4 col-xl">

                        <div class="card metric-card metric-card-green h-100">

                            <div class="d-flex justify-content-between align-items-start mb-2">

                                <span class="stat-label-title text-truncate">
                                    Inbound (+Stock In)
                                </span>

                                <div class="metric-icon-box bg-success text-white">
                                    <i class="fas fa-arrow-down-long"></i>
                                </div>

                            </div>

                            <div class="min-w-0">

                                <h5 class="fw-bold text-success font-monospace stat-val-num"
                                    id="val-in-qty">

                                    +{{ number_format($ledger['stock_in_qty']) }}
                                    <span>Units</span>

                                </h5>

                                <small class="stat-sub-text font-monospace">
                                    Cost:
                                    <strong class="text-dark" id="val-in-val">
                                        ৳ {{ number_format($ledger['stock_in_value'], 2) }}
                                    </strong>
                                </small>

                            </div>

                        </div>

                    </div>


                    {{-- Stock Out --}}
                    <div class="col-6 col-md-4 col-xl">

                        <div class="card metric-card metric-card-red h-100">

                            <div class="d-flex justify-content-between align-items-start mb-2">

                                <span class="stat-label-title text-truncate">
                                    Outbound (-Stock Out)
                                </span>

                                <div class="metric-icon-box bg-danger text-white">
                                    <i class="fas fa-arrow-up-long"></i>
                                </div>

                            </div>

                            <div class="min-w-0">

                                <h5 class="fw-bold text-danger font-monospace stat-val-num"
                                    id="val-out-qty">

                                    -{{ number_format($ledger['stock_out_qty']) }}
                                    <span>Units</span>

                                </h5>

                                <small class="stat-sub-text font-monospace">
                                    Cost:
                                    <strong class="text-dark" id="val-out-val">
                                        ৳ {{ number_format($ledger['stock_out_value'], 2) }}
                                    </strong>
                                </small>

                            </div>

                        </div>

                    </div>


                    {{-- Gross Profit --}}
                    <div class="col-6 col-md-6 col-xl">

                        <div class="card metric-card metric-card-amber h-100">

                            <div class="d-flex justify-content-between align-items-start mb-2">

                                <span class="stat-label-title text-truncate">
                                    Gross Profit
                                </span>

                                <div class="metric-icon-box text-dark"
                                     style="background-color: #f59e0b;">

                                    <i class="fas fa-coins"></i>
                                </div>

                            </div>

                            <div class="min-w-0">

                                <h5 class="fw-bold text-dark font-monospace stat-val-num"
                                    id="val-gross-profit">

                                    ৳ {{ number_format($ledger['gross_profit'], 2) }}

                                </h5>

                                <small class="stat-sub-text font-monospace">

                                    Rev:
                                    <span id="val-sales-rev">
                                        ৳{{ number_format($ledger['sales_revenue'], 0) }}
                                    </span>

                                    <span class="mx-1">|</span>

                                    COGS:
                                    <span id="val-sales-cogs">
                                        ৳{{ number_format($ledger['sales_cogs'], 0) }}
                                    </span>

                                </small>

                            </div>

                        </div>

                    </div>


                    {{-- Closing Stock --}}
                    <div class="col-12 col-md-6 col-xl">

                        <div class="card metric-card metric-card-purple h-100">

                            <div class="d-flex justify-content-between align-items-start mb-2">

                                <span class="stat-label-title text-truncate">
                                    Closing Stock
                                </span>

                                <div class="metric-icon-box text-white"
                                     style="background-color: #7c3aed;">

                                    <i class="fas fa-vault"></i>
                                </div>

                            </div>

                            <div class="min-w-0">

                                <h5 class="fw-bold text-dark font-monospace stat-val-num"
                                    id="val-closing-qty">

                                    {{ number_format($ledger['closing_qty']) }}
                                    <span>Units</span>

                                </h5>

                                <small class="stat-sub-text font-monospace">
                                    Valuation:
                                    <strong class="text-dark" id="val-closing-val">
                                        ৳ {{ number_format($ledger['closing_value'], 2) }}
                                    </strong>
                                </small>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =====================================================
         4. PRODUCT-WISE ACTIVITY & PROFIT BREAKDOWN
    ====================================================== --}}

    <div class="card p-0 mb-3 overflow-hidden shadow-xs">

        {{-- Table Header --}}
        <div class="report-table-heading d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">

            <div class="min-w-0">

                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">

                    <i class="fas fa-table text-primary"></i>

                    <span>Product-wise Activity &amp; Profit Breakdown</span>

                </h6>

                <small class="text-muted d-block mt-1"
                       style="font-size: 10.5px;"
                       id="report-period-caption">

                    Period:
                    <strong class="text-dark">{{ $periodLabel }}</strong>

                </small>

            </div>


            <div class="d-flex align-items-center gap-2">

                <span class="badge report-count-badge font-monospace"
                      id="report-line-count">

                    <i class="fas fa-layer-group"></i>
                    {{ $reportItems->total() }} Line Items

                </span>

                <button type="button"
                        class="collapse-toggle-btn shadow-xs no-print"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseReportTable"
                        aria-expanded="true"
                        aria-controls="collapseReportTable"
                        title="Toggle Table">

                    <i class="fas fa-chevron-down collapse-icon"></i>

                </button>

            </div>

        </div>


        {{-- Table Content --}}
        <div class="collapse show" id="collapseReportTable">

            <div class="table-responsive pc-fit-container">

                <table class="table align-middle mb-0 report-table">

                    <thead>

                        <tr>

                            <th class="ps-3 col-rp-id">
                                ID
                            </th>

                            <th class="col-rp-prod">
                                Product &amp; Attributes
                            </th>

                            <th class="col-rp-sku">
                                SKU
                            </th>

                            <th class="text-center text-success col-rp-inqty">
                                Stock In (Qty)
                            </th>

                            <th class="text-end text-success col-rp-inval">
                                Stock In Val
                            </th>

                            <th class="text-center text-danger col-rp-outqty">
                                Stock Out (Qty)
                            </th>

                            <th class="text-center text-primary col-rp-sqty">
                                Sales Qty
                            </th>

                            <th class="text-end text-dark col-rp-rev">
                                Revenue
                            </th>

                            <th class="text-end text-secondary col-rp-cogs">
                                COGS
                            </th>

                            <th class="text-end text-success col-rp-profit">
                                Gross Profit
                            </th>

                            <th class="text-end pe-3 col-rp-action">
                                Ledger
                            </th>

                        </tr>

                    </thead>


                    <tbody id="report-tbody">

                        @include('admin.inventory.partials.report_rows', [
                            'items' => $reportItems
                        ])

                    </tbody>

                </table>

            </div>


            {{-- Pagination Footer --}}
            <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50"
                 id="report-pagination-container">

                <div class="pagination-info font-monospace">

                    Showing
                    <strong>{{ $reportItems->firstItem() ?? 0 }}</strong>
                    to
                    <strong>{{ $reportItems->lastItem() ?? 0 }}</strong>
                    of
                    <strong>{{ $reportItems->total() }}</strong>
                    records

                </div>

                <div>
                    {{ $reportItems->links('pagination::bootstrap-5') }}
                </div>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     5. PRODUCT IMAGE ZOOM MODAL
========================================================= --}}

<div class="modal fade report-modal-scope"
     id="reportImageZoomModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 108000;">

    <div class="modal-dialog modal-dialog-centered modal-md">

        <div class="modal-content border-0 shadow-lg text-center p-2">

            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">

                <span class="fw-bold text-dark small"
                      style="font-size: 12.5px;"
                      id="reportZoomModalTitle">

                    <i class="fas fa-image text-primary me-1"></i>
                    Product Preview

                </span>

                <button type="button"
                        class="btn-close btn-sm shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>

            </div>

            <div class="modal-body p-2">

                <img id="reportZoomModalImgTag"
                     src=""
                     alt="Product Photo"
                     class="img-fluid border"
                     style="max-height: 480px; width: 100%; object-fit: contain; border-radius: 5px;">

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     6. JAVASCRIPT
========================================================= --}}

<script>
(function () {

    'use strict';

    /* =====================================================
       IMAGE PREVIEW MODAL
    ====================================================== */

    window.zoomReportImage = function (url, title = 'Product Image') {

        const titleEl = document.getElementById('reportZoomModalTitle');
        const imgTag = document.getElementById('reportZoomModalImgTag');
        const modalEl = document.getElementById('reportImageZoomModal');

        if (!url || !imgTag || !modalEl) {
            return;
        }

        if (titleEl) {
            titleEl.innerText = title;
        }

        imgTag.src = url;

        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

    };


    /* =====================================================
       PERIOD PRESET CHANGE
    ====================================================== */

    window.handlePeriodPresetChange = function () {

        const periodEl = document.getElementById('report-period-select');
        const startEl = document.getElementById('report-start-date');
        const endEl = document.getElementById('report-end-date');

        if (!periodEl || !startEl || !endEl) {
            return;
        }

        const period = periodEl.value;

        const now = new Date();

        function formatDate(date) {

            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;

        }


        if (period === 'today') {

            const today = new Date();

            startEl.value = formatDate(today);
            endEl.value = formatDate(today);

        } else if (period === 'yesterday') {

            const yesterday = new Date();

            yesterday.setDate(yesterday.getDate() - 1);

            startEl.value = formatDate(yesterday);
            endEl.value = formatDate(yesterday);

        } else if (period === 'this_week') {

            const first = new Date(now);
            const last = new Date(now);

            first.setDate(now.getDate() - now.getDay());
            last.setDate(now.getDate() - now.getDay() + 6);

            startEl.value = formatDate(first);
            endEl.value = formatDate(last);

        } else if (period === 'last_week') {

            const first = new Date(now);
            const last = new Date(now);

            first.setDate(now.getDate() - now.getDay() - 7);
            last.setDate(now.getDate() - now.getDay() - 1);

            startEl.value = formatDate(first);
            endEl.value = formatDate(last);

        } else if (period === 'this_month') {

            const first = new Date(now.getFullYear(), now.getMonth(), 1);
            const last = new Date(now.getFullYear(), now.getMonth() + 1, 0);

            startEl.value = formatDate(first);
            endEl.value = formatDate(last);

        } else if (period === 'last_month') {

            const first = new Date(now.getFullYear(), now.getMonth() - 1, 1);
            const last = new Date(now.getFullYear(), now.getMonth(), 0);

            startEl.value = formatDate(first);
            endEl.value = formatDate(last);

        } else if (period === 'this_year') {

            const first = new Date(now.getFullYear(), 0, 1);
            const last = new Date(now.getFullYear(), 11, 31);

            startEl.value = formatDate(first);
            endEl.value = formatDate(last);

        }

        applyReportFilter();

    };


    /* =====================================================
       FORMAT NUMBER
    ====================================================== */

    function formatReportNumber(value, decimals = 2) {

        const number = Number(value ?? 0);

        return number.toLocaleString(undefined, {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });

    }


    /* =====================================================
       UPDATE LEDGER SUMMARY
    ====================================================== */

    function updateLedgerSummary(ledger) {

        if (!ledger) {
            return;
        }

        const elements = {
            openingQty: document.getElementById('val-opening-qty'),
            openingVal: document.getElementById('val-opening-val'),

            inQty: document.getElementById('val-in-qty'),
            inVal: document.getElementById('val-in-val'),

            outQty: document.getElementById('val-out-qty'),
            outVal: document.getElementById('val-out-val'),

            grossProfit: document.getElementById('val-gross-profit'),
            salesRevenue: document.getElementById('val-sales-rev'),
            salesCogs: document.getElementById('val-sales-cogs'),

            closingQty: document.getElementById('val-closing-qty'),
            closingVal: document.getElementById('val-closing-val')
        };


        if (elements.openingQty) {

            elements.openingQty.innerHTML =
                `${Number(ledger.opening_qty).toLocaleString()} <span>Units</span>`;

        }

        if (elements.openingVal) {

            elements.openingVal.innerText =
                `৳ ${formatReportNumber(ledger.opening_value)}`;

        }


        if (elements.inQty) {

            elements.inQty.innerHTML =
                `+${Number(ledger.stock_in_qty).toLocaleString()} <span>Units</span>`;

        }

        if (elements.inVal) {

            elements.inVal.innerText =
                `৳ ${formatReportNumber(ledger.stock_in_value)}`;

        }


        if (elements.outQty) {

            elements.outQty.innerHTML =
                `-${Number(ledger.stock_out_qty).toLocaleString()} <span>Units</span>`;

        }

        if (elements.outVal) {

            elements.outVal.innerText =
                `৳ ${formatReportNumber(ledger.stock_out_value)}`;

        }


        if (elements.grossProfit) {

            elements.grossProfit.innerText =
                `৳ ${formatReportNumber(ledger.gross_profit)}`;

        }

        if (elements.salesRevenue) {

            elements.salesRevenue.innerText =
                `৳${formatReportNumber(ledger.sales_revenue, 0)}`;

        }

        if (elements.salesCogs) {

            elements.salesCogs.innerText =
                `৳${formatReportNumber(ledger.sales_cogs, 0)}`;

        }


        if (elements.closingQty) {

            elements.closingQty.innerHTML =
                `${Number(ledger.closing_qty).toLocaleString()} <span>Units</span>`;

        }

        if (elements.closingVal) {

            elements.closingVal.innerText =
                `৳ ${formatReportNumber(ledger.closing_value)}`;

        }

    }


    /* =====================================================
       AJAX REPORT FILTER
    ====================================================== */

    window.applyReportFilter = function (targetUrl = null) {

        const form = document.getElementById('report-filter-form');

        if (!form) {
            return;
        }

        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        const cover = document.getElementById('reportLoadingCover');

        if (cover) {

            cover.classList.remove('d-none');
            cover.classList.add('d-flex');

        }

        const url = targetUrl ||
            ("{{ route('admin.inventory.reports') }}?" + params.toString());


        fetch(url, {

            method: 'GET',

            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }

        })

        .then(response => {

            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status}`);
            }

            return response.json();

        })

        .then(data => {

            if (!data.success) {
                return;
            }


            /* Update Report Rows */

            const tbody = document.getElementById('report-tbody');

            if (tbody) {
                tbody.innerHTML = data.table_html;
            }


            /* Update Pagination */

            const paginationContainer =
                document.getElementById('report-pagination-container');

            if (paginationContainer) {

                paginationContainer.innerHTML = `
                    <div class="pagination-info font-monospace">
                        Updated Report Results
                        (<strong>${Number(data.total_count ?? 0).toLocaleString()}</strong> records)
                    </div>

                    <div>
                        ${data.pagination ?? ''}
                    </div>
                `;

            }


            /* Update Line Item Counter */

            const lineCount = document.getElementById('report-line-count');

            if (lineCount) {

                lineCount.innerHTML = `
                    <i class="fas fa-layer-group"></i>
                    ${Number(data.total_count ?? 0).toLocaleString()} Line Items
                `;

            }


            /* Update Summary */

            if (data.ledger) {

                updateLedgerSummary(data.ledger);

            }


            /* Update URL Without Reload */

            if (window.history && window.history.replaceState && !targetUrl) {

                const newUrl = new URL(url, window.location.origin);

                window.history.replaceState(
                    {},
                    '',
                    newUrl.pathname + newUrl.search
                );

            }

        })

        .catch(error => {

            console.error('Report filter error:', error);

        })

        .finally(() => {

            if (cover) {

                cover.classList.add('d-none');
                cover.classList.remove('d-flex');

            }

        });

    };


    /* =====================================================
       AJAX PAGINATION
    ====================================================== */

    document.addEventListener('click', function (event) {

        const pageLink = event.target.closest(
            '#report-pagination-container a'
        );

        if (!pageLink) {
            return;
        }

        event.preventDefault();

        const href = pageLink.getAttribute('href');

        if (
            href &&
            href !== '#' &&
            !href.startsWith('javascript:')
        ) {

            applyReportFilter(href);

        }

    });


    /* =====================================================
       CSV EXPORT
    ====================================================== */

    window.exportReportCsv = function () {

        const form = document.getElementById('report-filter-form');

        if (!form) {
            return;
        }

        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        params.append('export', 'csv');

        const exportUrl =
            "{{ route('admin.inventory.reports') }}?" + params.toString();

        window.open(exportUrl, '_blank');

    };


    /* =====================================================
       DATE INPUT CHANGE
    ====================================================== */

    document.addEventListener('DOMContentLoaded', function () {

        const startDate = document.getElementById('report-start-date');
        const endDate = document.getElementById('report-end-date');
        const periodSelect = document.getElementById('report-period-select');

        function setCustomPeriod() {

            if (periodSelect && periodSelect.value !== 'custom') {
                periodSelect.value = 'custom';
            }

        }

        if (startDate) {
            startDate.addEventListener('change', setCustomPeriod);
        }

        if (endDate) {
            endDate.addEventListener('change', setCustomPeriod);
        }

    });

})();
</script>

@endsection