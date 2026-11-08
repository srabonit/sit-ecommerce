@extends('layouts.admin')

@section('title', 'Inventory Movements & Stock Audit Ledger - ' . config('app.name', 'Shop Now BD'))

@php
    $siteName = config('app.name', 'Shop Now BD');

    $resolveThumb = function($path, $fallback = 'https://placehold.co/50x50?text=Item') {
        if (empty($path)) return $fallback;

        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }

        if (function_exists('show_image')) return show_image($path, $fallback);

        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;

        return asset(ltrim(str_replace(['public_html/', 'public/'], '', $path), '/'));
    };

    $valuation = $valuation ?? [
        'total_units' => 0,
        'total_valuation' => 0
    ];
@endphp

@section('admin_content')

<style>
    /* =========================================================
       INVENTORY MOVEMENTS — MODERN ADMIN UI
       Scoped styles | Strict 5px radius | Responsive layout
    ========================================================= */

    #inventoryMovementsRoot {
        --inv-primary: #2563eb;
        --inv-primary-dark: #1d4ed8;
        --inv-text: #172033;
        --inv-muted: #64748b;
        --inv-border: #e2e8f0;
        --inv-soft: #f8fafc;
        --inv-green: #15803d;
        --inv-red: #dc2626;
        --inv-radius: 5px;
        color: var(--inv-text);
        font-size: 12px;
    }

    #inventoryMovementsRoot *,
    .movement-modal-scope * {
        box-sizing: border-box;
    }

    #inventoryMovementsRoot .btn,
    #inventoryMovementsRoot .btn-sm,
    #inventoryMovementsRoot button,
    #inventoryMovementsRoot .form-control,
    #inventoryMovementsRoot .form-select,
    #inventoryMovementsRoot .badge,
    #inventoryMovementsRoot .alert,
    #inventoryMovementsRoot .input-group-text,
    #inventoryMovementsRoot .card,
    #inventoryMovementsRoot .metric-soft-card,
    #inventoryMovementsRoot .filter-toolbar-card,
    #inventoryMovementsRoot .audit-widget-card,
    #inventoryMovementsRoot .metric-icon-square,
    #inventoryMovementsRoot .page-link,
    #inventoryMovementsRoot .page-item span,
    #inventoryMovementsRoot .proof-media-surface,
    #inventoryMovementsRoot .proof-thumb-box,
    #inventoryMovementsRoot .collapse-toggle-btn,
    .movement-modal-scope .modal-content,
    .movement-modal-scope .btn,
    .movement-modal-scope .form-control,
    .movement-modal-scope .form-select {
        border-radius: 5px !important;
    }

    #inventoryMovementsRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #inventoryMovementsRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    #inventoryMovementsRoot .font-monospace,
    .movement-modal-scope .font-monospace {
        font-weight: 600 !important;
    }

    /* Page Header */

    #inventoryMovementsRoot .movement-header {
        padding: 2px 0 14px;
        border-bottom: 1px solid var(--inv-border);
    }

    #inventoryMovementsRoot .movement-header h5 {
        color: #172033 !important;
        font-size: 18px !important;
        font-weight: 750 !important;
        letter-spacing: -0.35px;
    }

    #inventoryMovementsRoot .movement-header .header-description {
        color: var(--inv-muted);
        font-size: 11px;
        line-height: 1.6;
    }

    #inventoryMovementsRoot .top-action-btns {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    #inventoryMovementsRoot .btn-compact {
        min-height: 32px;
        padding: 6px 12px !important;
        font-size: 11px !important;
        line-height: 1.3 !important;
        font-weight: 700 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        white-space: nowrap;
        transition: all 0.18s ease;
    }

    #inventoryMovementsRoot .btn-compact:hover {
        transform: translateY(-1px);
    }

    #inventoryMovementsRoot .btn-primary {
        background-color: var(--inv-primary);
        border-color: var(--inv-primary);
    }

    #inventoryMovementsRoot .btn-primary:hover {
        background-color: var(--inv-primary-dark);
        border-color: var(--inv-primary-dark);
    }

    /* Section Cards */

    #inventoryMovementsRoot .audit-widget-card,
    #inventoryMovementsRoot .filter-toolbar-card {
        background: #fff;
        border: 1px solid var(--inv-border);
        box-shadow: 0 2px 7px rgba(15, 23, 42, 0.035);
        margin-bottom: 14px;
        overflow: hidden;
    }

    #inventoryMovementsRoot .section-header-wrap {
        min-height: 42px;
        padding: 9px 14px;
        background: #fff;
        border-bottom: 1px solid var(--inv-border);
    }

    #inventoryMovementsRoot .section-heading {
        color: #253047;
        font-size: 12px;
        font-weight: 750;
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    #inventoryMovementsRoot .section-heading i {
        font-size: 13px;
    }

    #inventoryMovementsRoot .collapse-toggle-btn {
        width: 27px;
        height: 27px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #dbe3ed;
        color: #64748b;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    #inventoryMovementsRoot .collapse-toggle-btn:hover {
        background: var(--inv-primary);
        border-color: var(--inv-primary);
        color: #fff;
    }

    #inventoryMovementsRoot .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    #inventoryMovementsRoot .collapse-icon {
        font-size: 10px;
        transition: transform 0.25s ease;
    }

    /* KPI Cards */

    #inventoryMovementsRoot .metric-soft-card {
        min-height: 105px;
        height: 100%;
        padding: 13px 14px;
        border: 1px solid var(--inv-border);
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.035);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    #inventoryMovementsRoot .metric-soft-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 3px;
        height: 100%;
        background: #cbd5e1;
    }

    #inventoryMovementsRoot .metric-soft-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(15, 23, 42, 0.07);
    }

    #inventoryMovementsRoot .metric-card-green {
        background: linear-gradient(145deg, #fff 0%, #f0fdf4 100%);
        border-color: #bbf7d0;
    }

    #inventoryMovementsRoot .metric-card-green::before {
        background: #16a34a;
    }

    #inventoryMovementsRoot .metric-card-red {
        background: linear-gradient(145deg, #fff 0%, #fef2f2 100%);
        border-color: #fecaca;
    }

    #inventoryMovementsRoot .metric-card-red::before {
        background: #dc2626;
    }

    #inventoryMovementsRoot .metric-card-blue {
        background: linear-gradient(145deg, #fff 0%, #eff6ff 100%);
        border-color: #bfdbfe;
    }

    #inventoryMovementsRoot .metric-card-blue::before {
        background: #2563eb;
    }

    #inventoryMovementsRoot .metric-card-purple {
        background: linear-gradient(145deg, #fff 0%, #faf5ff 100%);
        border-color: #e9d5ff;
    }

    #inventoryMovementsRoot .metric-card-purple::before {
        background: #7c3aed;
    }

    #inventoryMovementsRoot .stat-label-title {
        color: #64748b;
        font-size: 10.5px;
        font-weight: 750;
        letter-spacing: 0.025em;
        line-height: 1.4;
        text-transform: uppercase;
    }

    #inventoryMovementsRoot .stat-val-num {
        font-size: 19px;
        font-weight: 800;
        line-height: 1.3;
        letter-spacing: -0.5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #inventoryMovementsRoot .stat-sub-text {
        font-size: 10px;
        color: #94a3b8;
        margin-top: 3px;
    }

    #inventoryMovementsRoot .metric-icon-square {
        width: 33px;
        height: 33px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }

    /* Filter Area */

    #inventoryMovementsRoot .filter-toolbar-card .filter-body {
        padding: 15px;
    }

    #inventoryMovementsRoot .form-label,
    .movement-modal-scope .form-label {
        display: block;
        margin-bottom: 5px;
        color: #475569;
        font-size: 11px;
        font-weight: 750;
    }

    #inventoryMovementsRoot .form-control,
    #inventoryMovementsRoot .form-select,
    .movement-modal-scope .form-control,
    .movement-modal-scope .form-select {
        min-height: 35px;
        padding: 6px 10px;
        color: #1e293b;
        background-color: #fff;
        border: 1px solid #d8e0ea;
        box-shadow: none;
        font-size: 11.5px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    #inventoryMovementsRoot .form-control::placeholder,
    .movement-modal-scope .form-control::placeholder {
        color: #a0acbb;
        font-size: 11px;
    }

    #inventoryMovementsRoot .form-control:focus,
    #inventoryMovementsRoot .form-select:focus,
    .movement-modal-scope .form-control:focus,
    .movement-modal-scope .form-select:focus {
        border-color: var(--inv-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
        outline: none;
    }

    #inventoryMovementsRoot .input-group-text {
        min-width: 36px;
        justify-content: center;
        color: #64748b;
        background: #f8fafc;
        border: 1px solid #d8e0ea;
    }

    #inventoryMovementsRoot .filter-submit-btn {
        min-height: 35px;
        font-size: 11px;
    }

    #inventoryMovementsRoot #invCustomDatePanel {
        padding-top: 12px;
        margin-top: 12px !important;
        border-top: 1px dashed #dbe3ed !important;
    }

    /* Ledger Table */

    #inventoryMovementsRoot .ledger-header {
        padding: 11px 14px;
        background: #fff;
        border-bottom: 1px solid var(--inv-border);
    }

    #inventoryMovementsRoot .ledger-title {
        color: #1e293b;
        font-size: 13px;
        font-weight: 750;
        margin: 0;
    }

    #inventoryMovementsRoot .ledger-subtitle {
        color: #94a3b8;
        font-size: 10px;
        margin-top: 4px;
    }

    #inventoryMovementsRoot .per-page-select {
        width: 80px;
        min-height: 30px;
        font-size: 11px;
    }

    #inventoryMovementsRoot .pc-fit-container {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #inventoryMovementsRoot .custom-movements-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #inventoryMovementsRoot .custom-movements-table thead th {
        padding: 10px 7px;
        color: #64748b;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        font-size: 9.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.045em;
        white-space: nowrap;
    }

    #inventoryMovementsRoot .custom-movements-table tbody td {
        padding: 9px 7px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        font-size: 10.5px;
        white-space: nowrap;
    }

    #inventoryMovementsRoot .custom-movements-table tbody tr {
        transition: background-color 0.15s ease;
    }

    #inventoryMovementsRoot .custom-movements-table tbody tr:hover {
        background-color: #f8fbff;
    }

    #inventoryMovementsRoot .custom-movements-table tbody tr:last-child td {
        border-bottom: 0;
    }

    @media (min-width: 992px) {
        #inventoryMovementsRoot .custom-movements-table {
            table-layout: fixed !important;
            width: 100% !important;
        }

        #inventoryMovementsRoot .pc-fit-container {
            overflow-x: hidden !important;
        }

        #inventoryMovementsRoot .custom-movements-table th,
        #inventoryMovementsRoot .custom-movements-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        #inventoryMovementsRoot .col-m-id     { width: 4% !important; }
        #inventoryMovementsRoot .col-m-date   { width: 9% !important; }
        #inventoryMovementsRoot .col-m-prod   { width: 22% !important; }
        #inventoryMovementsRoot .col-m-sku    { width: 8% !important; }
        #inventoryMovementsRoot .col-m-type   { width: 9% !important; }
        #inventoryMovementsRoot .col-m-change { width: 5% !important; }
        #inventoryMovementsRoot .col-m-flow   { width: 7% !important; }
        #inventoryMovementsRoot .col-m-cost   { width: 7% !important; }
        #inventoryMovementsRoot .col-m-imp    { width: 8% !important; }
        #inventoryMovementsRoot .col-m-ref    { width: 11% !important; }
        #inventoryMovementsRoot .col-m-user   { width: 10% !important; }
    }

    #inventoryMovementsRoot .movement-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 3px;
        padding: 3px 7px;
        border-radius: 5px !important;
        font-size: 9px;
        font-weight: 750;
        letter-spacing: 0.15px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    /* Proof Media */

    #inventoryMovementsRoot .proof-media-surface {
        padding: 12px;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        transition: all 0.2s ease;
    }

    #inventoryMovementsRoot .proof-media-surface:hover {
        background: #f1f5f9;
        border-color: var(--inv-primary);
    }

    #inventoryMovementsRoot .proof-empty-icon-box {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #e2e8f0;
        color: #64748b;
        border-radius: 5px !important;
        font-size: 15px;
    }

    #inventoryMovementsRoot .proof-thumb-box {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        flex-shrink: 0;
        overflow: hidden;
        background: #fff;
        border: 1px solid #cbd5e1;
        cursor: pointer;
    }

    #inventoryMovementsRoot .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #inventoryMovementsRoot .proof-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, 0.5);
        opacity: 0;
        transition: opacity 0.15s ease;
        border-radius: 5px !important;
    }

    #inventoryMovementsRoot .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    /* Pagination */

    #inventoryMovementsRoot #invPaginationLinks {
        display: flex;
        align-items: center;
    }

    #inventoryMovementsRoot #invPaginationLinks nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }

    #inventoryMovementsRoot #invPaginationLinks nav > div.d-sm-none,
    #inventoryMovementsRoot #invPaginationLinks nav > .d-sm-none {
        display: none !important;
    }

    #inventoryMovementsRoot #invPaginationLinks nav > div.d-none,
    #inventoryMovementsRoot #invPaginationLinks nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #inventoryMovementsRoot #invPaginationLinks nav p,
    #inventoryMovementsRoot #invPaginationLinks nav .small.text-muted,
    #inventoryMovementsRoot #invPaginationLinks nav div:has(> p),
    #inventoryMovementsRoot #invPaginationLinks nav > div.d-none > div:first-child,
    #inventoryMovementsRoot #invPaginationLinks nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }

    #inventoryMovementsRoot #invPaginationLinks .pagination {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    #inventoryMovementsRoot #invPaginationLinks .page-item {
        display: inline-flex !important;
    }

    #inventoryMovementsRoot #invPaginationLinks .page-item .page-link,
    #inventoryMovementsRoot #invPaginationLinks .page-item span {
        min-width: 27px !important;
        height: 27px !important;
        padding: 0 7px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #475569 !important;
        background: #fff !important;
        border: 1px solid #dbe3ed !important;
        border-radius: 5px !important;
        font-size: 10.5px !important;
        font-weight: 700 !important;
        line-height: 1 !important;
        text-decoration: none !important;
        transition: all 0.15s ease !important;
    }

    #inventoryMovementsRoot #invPaginationLinks .page-item .page-link:hover {
        color: var(--inv-primary) !important;
        background: #eff6ff !important;
        border-color: #93c5fd !important;
    }

    #inventoryMovementsRoot #invPaginationLinks .page-item.active .page-link,
    #inventoryMovementsRoot #invPaginationLinks .page-item.active span {
        color: #fff !important;
        background: var(--inv-primary) !important;
        border-color: var(--inv-primary) !important;
        box-shadow: 0 2px 5px rgba(37, 99, 235, 0.18) !important;
    }

    #inventoryMovementsRoot #invPaginationLinks .page-item.disabled .page-link,
    #inventoryMovementsRoot #invPaginationLinks .page-item.disabled span {
        opacity: 0.4 !important;
        background: #f8fafc !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }

    #inventoryMovementsRoot #invPaginationLinks nav svg {
        width: 11px !important;
        height: 11px !important;
    }

    /* Loading */

    #inventoryMovementsRoot #invLoadingCover {
        z-index: 50;
        border-radius: 5px;
        backdrop-filter: blur(1px);
    }

    #inventoryMovementsRoot #invLoadingCover .spinner-border {
        width: 2.2rem;
        height: 2.2rem;
    }

    /* Modal */

    .movement-modal-scope .modal-content {
        overflow: hidden;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 15px 45px rgba(15, 23, 42, 0.15) !important;
    }

    .movement-modal-scope .modal-header {
        min-height: 48px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0 !important;
    }

    .movement-modal-scope .modal-title {
        font-size: 13px !important;
        font-weight: 750 !important;
    }

    .movement-modal-scope .modal-body {
        background: #fff;
    }

    .movement-modal-scope .modal-footer {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0 !important;
    }

    .movement-modal-scope .form-control,
    .movement-modal-scope .form-select {
        min-height: 36px;
    }

    .movement-modal-scope textarea.form-control {
        min-height: 65px;
        resize: vertical;
    }

    /* Responsive */

    @media (max-width: 991.98px) {
        #inventoryMovementsRoot .pc-fit-container {
            overflow-x: auto !important;
        }

        #inventoryMovementsRoot .custom-movements-table {
            min-width: 1000px;
            table-layout: auto !important;
        }
    }

    @media (max-width: 767.98px) {
        #inventoryMovementsRoot {
            padding-top: 10px !important;
        }

        #inventoryMovementsRoot .movement-header {
            padding-bottom: 12px;
        }

        #inventoryMovementsRoot .movement-header h5 {
            font-size: 15px !important;
            line-height: 1.4;
        }

        #inventoryMovementsRoot .movement-header .header-description {
            font-size: 10.5px;
        }

        #inventoryMovementsRoot .top-action-btns {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 7px;
        }

        #inventoryMovementsRoot .top-action-btns .btn {
            width: 100%;
            min-height: 35px;
            padding: 7px 5px !important;
            font-size: 10px !important;
        }

        #inventoryMovementsRoot .section-header-wrap {
            padding: 9px 11px;
        }

        #inventoryMovementsRoot .metric-soft-card {
            min-height: 89px;
            padding: 10px !important;
        }

        #inventoryMovementsRoot .stat-label-title {
            font-size: 9px !important;
        }

        #inventoryMovementsRoot .stat-val-num {
            font-size: 15px !important;
        }

        #inventoryMovementsRoot .stat-sub-text {
            font-size: 9px;
        }

        #inventoryMovementsRoot .metric-icon-square {
            width: 27px;
            height: 27px;
            font-size: 11px;
        }

        #inventoryMovementsRoot .filter-toolbar-card .filter-body {
            padding: 11px;
        }

        #inventoryMovementsRoot .filter-submit-btn {
            width: 100%;
        }

        #inventoryMovementsRoot .ledger-header {
            padding: 10px;
        }

        #inventoryMovementsRoot .ledger-title {
            font-size: 12px;
        }

        #inventoryMovementsRoot .custom-movements-table {
            min-width: 1000px !important;
        }

        #inventoryMovementsRoot #inv-pagination-container {
            padding: 10px !important;
        }

        #inventoryMovementsRoot #invPaginationLinks {
            width: 100% !important;
            justify-content: center !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            padding: 4px 0 !important;
        }

        #inventoryMovementsRoot #invPaginationLinks nav {
            width: 100% !important;
            justify-content: center !important;
        }

        #inventoryMovementsRoot #invPaginationLinks .pagination {
            flex-wrap: nowrap !important;
            gap: 3px !important;
        }
    }

    @media (max-width: 420px) {
        #inventoryMovementsRoot .metric-soft-card {
            min-height: 82px;
            padding: 8px !important;
        }

        #inventoryMovementsRoot .stat-val-num {
            font-size: 13px !important;
        }

        #inventoryMovementsRoot .stat-label-title {
            font-size: 8.5px !important;
        }

        #inventoryMovementsRoot .metric-icon-square {
            width: 24px;
            height: 24px;
            font-size: 10px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #inventoryMovementsRoot *,
        .movement-modal-scope * {
            transition-duration: 0.01ms !important;
            animation-duration: 0.01ms !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="inventoryMovementsRoot">

    <!-- AJAX Loading Overlay -->
    <div id="invLoadingCover"
         class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none justify-content-center align-items-center">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- =====================================================
         1. PAGE HEADER & ACTIONS
    ====================================================== -->

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3 movement-header">

        <div class="min-w-0">
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="fas fa-boxes-stacked text-primary"></i>
                <span>Inventory Movements &amp; Stock Audit</span>
            </h5>

            <small class="header-description d-block">
                Track real-time stock deductions, restocks, cancellations, returns, and manual adjustments.
            </small>
        </div>

        <div class="top-action-btns">

            <button type="button"
                    class="btn btn-sm btn-primary shadow-sm fw-bold btn-compact"
                    data-bs-toggle="modal"
                    data-bs-target="#stockAdjustmentModal">

                <i class="fas fa-sliders"></i>
                <span>Manual Adjustment</span>
            </button>

            <button type="button"
                    class="btn btn-sm btn-outline-success bg-white fw-bold btn-compact"
                    onclick="exportMovementsCsv()">

                <i class="fas fa-file-csv text-success"></i>
                <span>Export CSV</span>
            </button>

        </div>
    </div>

    <!-- =====================================================
         2. KPI SUMMARY
    ====================================================== -->

    <div class="audit-widget-card mb-3">

        <div class="section-header-wrap d-flex justify-content-between align-items-center">

            <span class="section-heading">
                <i class="fas fa-chart-pie text-primary"></i>
                Movement Audits &amp; Valuation Summary
            </span>

            <button type="button"
                    class="collapse-toggle-btn shadow-sm"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseMovementKpis"
                    aria-expanded="true"
                    aria-controls="collapseMovementKpis"
                    title="Toggle Summary">

                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>

        </div>

        <div class="collapse show" id="collapseMovementKpis">

            <div class="p-3">

                <div class="row g-2">

                    <!-- Stock Additions -->

                    <div class="col-6 col-xl-3">

                        <div class="metric-soft-card metric-card-green">

                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">

                                <span class="stat-label-title text-truncate">
                                    Stock In / Additions
                                </span>

                                <div class="metric-icon-square bg-success text-white">
                                    <i class="fas fa-arrow-down-long"></i>
                                </div>

                            </div>

                            <div class="min-w-0">

                                <h5 class="fw-bold my-0 text-success font-monospace stat-val-num"
                                    id="val-inv-additions">
                                    +{{ number_format($totalAdditions) }}
                                </h5>

                                <small class="stat-sub-text d-block text-truncate">
                                    Restock, returns &amp; audits
                                </small>

                            </div>

                        </div>

                    </div>

                    <!-- Stock Deductions -->

                    <div class="col-6 col-xl-3">

                        <div class="metric-soft-card metric-card-red">

                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">

                                <span class="stat-label-title text-truncate">
                                    Stock Out / Deductions
                                </span>

                                <div class="metric-icon-square bg-danger text-white">
                                    <i class="fas fa-arrow-up-long"></i>
                                </div>

                            </div>

                            <div class="min-w-0">

                                <h5 class="fw-bold my-0 text-danger font-monospace stat-val-num"
                                    id="val-inv-deductions">
                                    -{{ number_format($totalDeductions) }}
                                </h5>

                                <small class="stat-sub-text d-block text-truncate">
                                    Sales, POS &amp; damages
                                </small>

                            </div>

                        </div>

                    </div>

                    <!-- Net Movement -->

                    <div class="col-6 col-xl-3">

                        <div class="metric-soft-card metric-card-blue">

                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">

                                <span class="stat-label-title text-truncate">
                                    Net Movement
                                </span>

                                <div class="metric-icon-square bg-primary text-white">
                                    <i class="fas fa-scale-balanced"></i>
                                </div>

                            </div>

                            <div class="min-w-0">

                                <h5 class="fw-bold my-0 text-dark font-monospace stat-val-num"
                                    id="val-inv-net">
                                    {{ ($netChange >= 0 ? '+' : '') . number_format($netChange) }}
                                </h5>

                                <small class="stat-sub-text d-block text-truncate">
                                    Net stock variation
                                </small>

                            </div>

                        </div>

                    </div>

                    <!-- Inventory Valuation -->

                    <div class="col-6 col-xl-3">

                        <div class="metric-soft-card metric-card-purple">

                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">

                                <span class="stat-label-title text-truncate">
                                    Inventory Valuation
                                </span>

                                <div class="metric-icon-square text-white"
                                     style="background:#7c3aed;">

                                    <i class="fas fa-vault"></i>
                                </div>

                            </div>

                            <div class="min-w-0">

                                <h5 class="fw-bold my-0 text-dark font-monospace stat-val-num"
                                    id="val-inv-valuation">
                                    ৳ {{ number_format($valuation['total_valuation'], 2) }}
                                </h5>

                                <small class="stat-sub-text d-block text-truncate">
                                    Units: {{ number_format($valuation['total_units']) }} in stock
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

        <div class="section-header-wrap d-flex justify-content-between align-items-center">

            <span class="section-heading">
                <i class="fas fa-filter text-primary"></i>
                Filter Audit Trails
            </span>

            <button type="button"
                    class="collapse-toggle-btn shadow-sm"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseFilterToolbar"
                    aria-expanded="true"
                    aria-controls="collapseFilterToolbar"
                    title="Toggle Filters">

                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>

        </div>

        <div class="collapse show" id="collapseFilterToolbar">

            <div class="filter-body">

                <form id="movement-filter-form" onsubmit="return false;">

                    <div class="row g-2 align-items-end">

                        <!-- Search -->

                        <div class="col-12 col-md-4">

                            <label class="form-label" for="inv-search">
                                <i class="fas fa-search me-1 text-primary"></i>
                                Search Audit Records
                            </label>

                            <div class="input-group">

                                <span class="input-group-text border-end-0">
                                    <i class="fas fa-search"></i>
                                </span>

                                <input type="text"
                                       name="search"
                                       id="inv-search"
                                       class="form-control border-start-0 ps-0"
                                       placeholder="Invoice #, SKU, Product, Notes..."
                                       value="{{ request('search') }}">

                            </div>

                        </div>

                        <!-- Movement Type -->

                        <div class="col-6 col-md-3">

                            <label class="form-label" for="inv-movement-type">
                                <i class="fas fa-tags me-1 text-success"></i>
                                Movement Type
                            </label>

                            <select name="movement_type"
                                    id="inv-movement-type"
                                    class="form-select font-monospace"
                                    onchange="applyMovementFilter()">

                                <option value="all" selected>All Movements</option>
                                <option value="checkout_sale">Online Checkout Sale</option>
                                <option value="pos_sale">POS Counter Sale</option>
                                <option value="order_processing_deduct">Order Packing Deduct</option>
                                <option value="order_cancel">Order Cancel (Restock)</option>
                                <option value="order_return">Order Return (Restock)</option>
                                <option value="purchase_in">Purchase Inbound / Restock</option>
                                <option value="purchase_receipt_in">GRN Consignment In</option>
                                <option value="purchase_return">Return to Supplier (RTV)</option>
                                <option value="exchange_in">Supplier Exchange In</option>
                                <option value="exchange_out">Supplier Exchange Out</option>
                                <option value="damage_out">Damage / Scrap / Loss</option>
                                <option value="adjustment">Manual Adjustment</option>

                            </select>

                        </div>

                        <!-- Date Filter -->

                        <div class="col-6 col-md-3">

                            <label class="form-label" for="inv-date-filter">
                                <i class="fas fa-calendar-alt me-1 text-info"></i>
                                Date Range
                            </label>

                            <select name="date_filter"
                                    id="inv-date-filter"
                                    class="form-select font-monospace"
                                    onchange="handleDateFilterChange(this.value)">

                                <option value="all" selected>All Time</option>
                                <option value="today">Today</option>
                                <option value="yesterday">Yesterday</option>
                                <option value="7_days">Last 7 Days</option>
                                <option value="30_days">Last 30 Days</option>
                                <option value="this_month">This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="custom">Custom Range...</option>

                            </select>

                        </div>

                        <!-- Filter Button -->

                        <div class="col-12 col-md-2">

                            <button type="button"
                                    class="btn btn-dark btn-compact filter-submit-btn w-100 fw-bold"
                                    onclick="applyMovementFilter()">

                                <i class="fas fa-filter"></i>
                                Apply Filter
                            </button>

                        </div>

                    </div>

                    <!-- Custom Date Range -->

                    <div id="invCustomDatePanel"
                         class="row g-2 align-items-center d-none">

                        <div class="col-12 col-sm-auto">

                            <span class="small fw-bold text-muted font-monospace">
                                <i class="fas fa-calendar-days me-1"></i>
                                Custom Dates:
                            </span>

                        </div>

                        <div class="col-6 col-sm-auto">

                            <input type="date"
                                   name="start_date"
                                   id="inv-start-date"
                                   class="form-control font-monospace">

                        </div>

                        <div class="col-6 col-sm-auto">

                            <input type="date"
                                   name="end_date"
                                   id="inv-end-date"
                                   class="form-control font-monospace">

                        </div>

                        <div class="col-12 col-sm-auto">

                            <button type="button"
                                    class="btn btn-primary btn-compact shadow-sm"
                                    onclick="applyMovementFilter()">

                                <i class="fas fa-check me-1"></i>
                                Apply Dates

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <!-- =====================================================
         4. STOCK MOVEMENT LEDGER
    ====================================================== -->

    <div class="audit-widget-card p-0 mb-3">

        <!-- Ledger Header -->

        <div class="ledger-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">

            <div class="min-w-0">

                <h6 class="ledger-title d-flex align-items-center gap-2">

                    <i class="fas fa-list-check text-primary"></i>

                    <span>Real-Time Stock Movement Audit Trail</span>

                </h6>

                <div class="ledger-subtitle font-monospace"
                     id="inv-entries-text">

                    Showing {{ $movements->firstItem() ?? 0 }}
                    to {{ $movements->lastItem() ?? 0 }}
                    of {{ $movements->total() }} movements

                </div>

            </div>

            <div class="d-flex align-items-center gap-2 flex-shrink-0">

                <label class="small text-muted mb-0 font-monospace">
                    Show:
                </label>

                <select name="per_page"
                        id="inv-per-page"
                        class="form-select per-page-select font-monospace"
                        onchange="applyMovementFilter()">

                    <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ $perPage == 100000 ? 'selected' : '' }}>All</option>

                </select>

            </div>

        </div>

        <!-- Table -->

        <div class="table-responsive pc-fit-container">

            <table class="table align-middle mb-0 custom-movements-table"
                   id="movements-table">

                <thead>

                    <tr>

                        <th class="ps-2 col-m-id text-center">#ID</th>

                        <th class="col-m-date">Date &amp; Time</th>

                        <th class="col-m-prod">Product &amp; Attributes</th>

                        <th class="col-m-sku">SKU / Code</th>

                        <th class="col-m-type">Movement</th>

                        <th class="text-center col-m-change">Qty</th>

                        <th class="text-center col-m-flow">Stock Flow</th>

                        <th class="text-end col-m-cost">Unit Cost</th>

                        <th class="text-end col-m-imp">Impact</th>

                        <th class="col-m-ref">Ref &amp; Proof</th>

                        <th class="pe-2 col-m-user">Initiated By</th>

                    </tr>

                </thead>

                <tbody id="movements-tbody">

                    @include('admin.inventory.partials.movement_rows', [
                        'movements' => $movements
                    ])

                </tbody>

            </table>

        </div>

        <!-- Pagination Footer -->

        <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50"
             id="inv-pagination-container">

            <div class="small text-muted font-monospace">

                Total
                <strong>{{ $movements->total() }}</strong>
                records logged

            </div>

            <div id="invPaginationLinks">

                {{ $movements->links('pagination::bootstrap-5') }}

            </div>

        </div>

    </div>

</div>

<!-- =========================================================
     5. MANUAL STOCK ADJUSTMENT MODAL
========================================================== -->

<div class="modal fade movement-modal-scope"
     id="stockAdjustmentModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index:106000;">

    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content">

            <!-- Header -->

            <div class="modal-header py-2 px-3">

                <h6 class="modal-title text-dark d-flex align-items-center gap-2 mb-0">

                    <i class="fas fa-sliders text-primary"></i>

                    <span>Manual Stock Adjustment &amp; Voucher Proof</span>

                </h6>

                <button type="button"
                        class="btn-close shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>

            </div>

            <form id="stockAdjustmentForm"
                  action="{{ route('admin.inventory.adjust') }}"
                  method="POST">

                @csrf

                <input type="hidden"
                       name="proof_attachment"
                       id="adj_proof_attachment"
                       value="">

                <div class="modal-body p-3">

                    <!-- Product -->

                    <div class="mb-3">

                        <label class="form-label" for="adj_product_id">
                            Select Product
                            <span class="text-danger">*</span>
                        </label>

                        <select name="product_id"
                                id="adj_product_id"
                                class="form-select"
                                required
                                onchange="loadProductVariantsForAdjustment(this.value)">

                            <option value="">— Select Product —</option>

                            @php
                                $allProducts = DB::table('products')
                                    ->orderBy('title', 'asc')
                                    ->get([
                                        'id',
                                        'title',
                                        'product_code',
                                        'stock',
                                        'product_struct'
                                    ]);
                            @endphp

                            @foreach($allProducts as $p)

                                <option value="{{ $p->id }}"
                                        data-struct="{{ $p->product_struct }}"
                                        data-stock="{{ $p->stock }}">

                                    #{{ $p->id }}
                                    — {{ Str::limit($p->title, 42) }}
                                    ({{ $p->product_code ?? 'No SKU' }})
                                    [Current: {{ $p->stock }}]

                                </option>

                            @endforeach

                        </select>

                    </div>

                    <!-- Product Variant -->

                    <div class="mb-3 d-none" id="adjVariantBox">

                        <label class="form-label text-primary" for="adj_variant_id">

                            <i class="fas fa-layer-group me-1"></i>

                            Select Variant
                            <span class="text-danger">*</span>

                        </label>

                        <select name="variant_id"
                                id="adj_variant_id"
                                class="form-select">

                            <option value="">— Choose Variant —</option>

                        </select>

                    </div>

                    <!-- Movement Reason & Stock -->

                    <div class="row g-2 mb-3">

                        <div class="col-12 col-sm-6">

                            <label class="form-label" for="adj_movement_type">

                                Movement Reason
                                <span class="text-danger">*</span>

                            </label>

                            <select name="movement_type"
                                    id="adj_movement_type"
                                    class="form-select"
                                    required>

                                <option value="adjustment" selected>
                                    Manual Adjustment
                                </option>

                                <option value="purchase_receive">
                                    Purchase Inbound / Restock
                                </option>

                                <option value="damage_loss">
                                    Damage / Scrap / Loss
                                </option>

                                <option value="inventory_audit">
                                    Periodic Audit Reconciliation
                                </option>

                            </select>

                        </div>

                        <div class="col-12 col-sm-6">

                            <label class="form-label" for="adj_new_stock">

                                New Total Stock Count
                                <span class="text-danger">*</span>

                            </label>

                            <input type="number"
                                   name="new_stock"
                                   id="adj_new_stock"
                                   class="form-control font-monospace"
                                   min="0"
                                   placeholder="50"
                                   required>

                        </div>

                    </div>

                    <!-- Purchase Cost -->

                    <div class="mb-3">

                        <label class="form-label" for="adj_purchase_cost">

                            Unit Purchase Cost (৳)

                            <span class="text-muted fw-normal">
                                (Optional)
                            </span>

                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                ৳
                            </span>

                            <input type="number"
                                   step="0.01"
                                   name="purchase_cost"
                                   id="adj_purchase_cost"
                                   class="form-control font-monospace"
                                   placeholder="350.00">

                        </div>

                    </div>

                    <!-- Global Media Chooser -->

                    <div class="mb-3">

                        <label class="form-label d-flex align-items-center justify-content-between gap-2 mb-2">

                            <span>
                                <i class="fas fa-paperclip text-primary me-1"></i>
                                Physical Count / Audit Slip Voucher
                            </span>

                            <span class="badge bg-light text-muted border font-monospace">
                                GLOBAL MEDIA
                            </span>

                        </label>

                        <div class="proof-media-surface">

                            <!-- Empty State -->

                            <div id="noAdjProofNotice" class="text-center py-2">

                                <div class="proof-empty-icon-box mx-auto mb-2">

                                    <i class="fas fa-cloud-arrow-up"></i>

                                </div>

                                <h6 class="fw-bold text-dark mb-1"
                                    style="font-size:12px;">

                                    No Audit Proof Attached

                                </h6>

                                <p class="text-muted small mb-3"
                                   style="font-size:10px;">

                                    Attach physical stock sheet, damaged items photo, or voucher copy.

                                </p>

                                <button type="button"
                                        class="btn btn-primary btn-compact shadow-sm"
                                        onclick="chooseAdjustmentMedia()">

                                    <i class="fas fa-photo-film"></i>

                                    Choose from Media Library

                                </button>

                            </div>

                            <!-- Preview State -->

                            <div id="adjProofPreviewContainer"
                                 class="d-none bg-white border p-2">

                                <div class="d-flex align-items-center gap-2 mb-2">

                                    <div class="proof-thumb-box"
                                         onclick="zoomAdjustmentMedia()"
                                         title="Click to Zoom Image">

                                        <img id="adjProofPreviewImg"
                                             src="https://placehold.co/120x120?text=Audit+Proof"
                                             alt="Proof"
                                             onerror="this.src='https://placehold.co/120x120?text=Audit+Proof';">

                                        <div class="proof-thumb-hover-overlay">

                                            <i class="fas fa-search-plus text-white"
                                               style="font-size:9px;">
                                            </i>

                                        </div>

                                    </div>

                                    <div class="min-w-0 flex-grow-1">

                                        <span class="badge bg-success-subtle text-success border border-success-subtle"
                                              style="font-size:9px;">

                                            <i class="fas fa-check-circle me-1"></i>
                                            Proof Attached

                                        </span>

                                        <strong class="d-block text-dark text-truncate font-monospace mt-1"
                                                id="adjProofPathText"
                                                style="font-size:10px;">

                                            audit_voucher.jpg

                                        </strong>

                                    </div>

                                </div>

                                <div class="d-flex align-items-center gap-1 border-top pt-2">

                                    <button type="button"
                                            class="btn btn-outline-secondary btn-compact flex-grow-1"
                                            style="height:26px;font-size:9px !important;"
                                            onclick="zoomAdjustmentMedia()">

                                        <i class="fas fa-expand me-1"></i>
                                        Zoom

                                    </button>

                                    <button type="button"
                                            class="btn btn-outline-primary btn-compact flex-grow-1"
                                            style="height:26px;font-size:9px !important;"
                                            onclick="chooseAdjustmentMedia()">

                                        <i class="fas fa-arrows-rotate me-1"></i>
                                        Change

                                    </button>

                                    <button type="button"
                                            class="btn btn-outline-danger btn-compact flex-grow-1"
                                            style="height:26px;font-size:9px !important;"
                                            onclick="clearAdjustmentMedia()">

                                        <i class="fas fa-trash-alt me-1"></i>
                                        Remove

                                    </button>

                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- Notes -->

                    <div class="mb-1">

                        <label class="form-label" for="adj_notes">
                            Adjustment Notes / Audit Memo
                        </label>

                        <textarea name="notes"
                                  id="adj_notes"
                                  class="form-control"
                                  rows="3"
                                  placeholder="Consignment or periodic physical count adjustment memo..."></textarea>

                    </div>

                </div>

                <!-- Footer -->

                <div class="modal-footer py-2 px-3 d-flex justify-content-between">

                    <button type="button"
                            class="btn btn-light border btn-compact"
                            data-bs-dismiss="modal">

                        <i class="fas fa-times me-1"></i>
                        Cancel

                    </button>

                    <button type="submit"
                            class="btn btn-primary btn-compact shadow-sm fw-bold"
                            id="btn-submit-adj">

                        <i class="fas fa-check me-1"></i>
                        Apply Adjustment

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<!-- =========================================================
     6. PROOF IMAGE LIGHTBOX MODAL
========================================================== -->

<div class="modal fade movement-modal-scope"
     id="proofZoomModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index:106000;">

    <div class="modal-dialog modal-dialog-centered modal-md">

        <div class="modal-content text-center p-2">

            <div class="modal-header border-0 pb-0">

                <span class="fw-bold text-dark small">

                    <i class="fas fa-image text-primary me-1"></i>

                    Audit Slip / Attachment Preview

                </span>

                <button type="button"
                        class="btn-close shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>

            </div>

            <div class="modal-body p-2">

                <img id="modalZoomProofImgTag"
                     src=""
                     alt="Proof Preview"
                     class="img-fluid border"
                     style="max-height:480px;max-width:100%;object-fit:contain;">

            </div>

        </div>

    </div>

</div>

<!-- =========================================================
     7. NOTIFICATION MODAL
========================================================== -->

<div class="modal fade movement-modal-scope"
     id="movementNoticeModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index:106500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content text-center p-3">

            <div class="modal-body p-1">

                <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-3"
                     id="movementNoticeIconWrap"
                     style="width:46px;height:46px;">

                    <i class="fas fa-circle-info fs-4"
                       id="movementNoticeIcon">
                    </i>

                </div>

                <h6 class="fw-bold text-dark mb-1"
                    id="movementNoticeTitle"
                    style="font-size:14px;">

                    Notification

                </h6>

                <p class="text-muted small mb-3"
                   id="movementNoticeMsg"
                   style="font-size:12px;line-height:1.5;">
                </p>

                <button type="button"
                        class="btn btn-dark btn-compact px-4"
                        data-bs-dismiss="modal">

                    <i class="fas fa-check me-1"></i>
                    OK

                </button>

            </div>

        </div>

    </div>

</div>

<!-- =========================================================
     8. JAVASCRIPT FUNCTIONALITY
========================================================== -->

<script>
(function () {

    'use strict';

    /* =====================================================
       NOTIFICATION
    ====================================================== */

    window.showMovementNotice = function(message, title = 'Notification', type = 'info') {

        const titleEl = document.getElementById('movementNoticeTitle');
        const msgEl = document.getElementById('movementNoticeMsg');
        const iconWrap = document.getElementById('movementNoticeIconWrap');
        const iconEl = document.getElementById('movementNoticeIcon');

        if (titleEl) {
            titleEl.innerText = title;
        }

        if (msgEl) {
            msgEl.innerText = message;
        }

        if (iconWrap && iconEl) {

            iconWrap.className = 'rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3';

            iconWrap.style.width = '46px';
            iconWrap.style.height = '46px';

            if (type === 'error' || type === 'danger') {

                iconWrap.classList.add('bg-danger-subtle', 'text-danger');
                iconEl.className = 'fas fa-circle-exclamation fs-4';

            } else if (type === 'success') {

                iconWrap.classList.add('bg-success-subtle', 'text-success');
                iconEl.className = 'fas fa-circle-check fs-4';

            } else {

                iconWrap.classList.add('bg-primary-subtle', 'text-primary');
                iconEl.className = 'fas fa-circle-info fs-4';

            }

        }

        const modalEl = document.getElementById('movementNoticeModal');

        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

    };


    /* =====================================================
       MEDIA ITEM EXTRACTOR
    ====================================================== */

    window.extractMediaItem = function(mediaInput) {

        if (!mediaInput) return null;

        const item = Array.isArray(mediaInput)
            ? mediaInput[0]
            : mediaInput;

        if (!item || typeof item !== 'object') {
            return null;
        }

        const rawPath = item.file_path || item.path || item.url || '';
        const rawUrl = item.url || item.file_path || item.path || '';

        if (!rawPath || rawPath === 'undefined') {
            return null;
        }

        return {
            path: String(rawPath).trim(),
            url: String(rawUrl).trim()
        };

    };


    /* =====================================================
       SELECT MEDIA FOR ADJUSTMENT
    ====================================================== */

    window.chooseAdjustmentMedia = function() {

        if (typeof window.openMediaPicker === 'function') {

            window.openMediaPicker({

                multiple: false,

                onSelect: function(media) {

                    const picked = window.extractMediaItem(media);

                    if (picked && picked.path) {

                        const inputHidden = document.getElementById('adj_proof_attachment');
                        const previewImg = document.getElementById('adjProofPreviewImg');
                        const pathText = document.getElementById('adjProofPathText');
                        const container = document.getElementById('adjProofPreviewContainer');
                        const emptyNotice = document.getElementById('noAdjProofNotice');

                        if (inputHidden) {
                            inputHidden.value = picked.path;
                        }

                        if (previewImg) {
                            previewImg.src = picked.url || picked.path;
                        }

                        if (pathText) {

                            pathText.innerText = picked.path.split('/').pop() || picked.path;
                            pathText.title = picked.path;

                        }

                        if (container) {

                            container.classList.remove('d-none');
                            container.classList.add('d-block');

                        }

                        if (emptyNotice) {

                            emptyNotice.classList.add('d-none');
                            emptyNotice.classList.remove('d-block');

                        }

                        window.showMovementNotice(
                            'Audit evidence voucher attached from Global Media Library.',
                            'Attached',
                            'success'
                        );

                    }

                }

            });

        } else {

            window.showMovementNotice(
                'Global Media Library is loading, please try again.',
                'Notice'
            );

        }

    };


    /* =====================================================
       CLEAR MEDIA
    ====================================================== */

    window.clearAdjustmentMedia = function() {

        const inputHidden = document.getElementById('adj_proof_attachment');
        const container = document.getElementById('adjProofPreviewContainer');
        const emptyNotice = document.getElementById('noAdjProofNotice');

        if (inputHidden) {
            inputHidden.value = '';
        }

        if (container) {

            container.classList.add('d-none');
            container.classList.remove('d-block');

        }

        if (emptyNotice) {

            emptyNotice.classList.remove('d-none');
            emptyNotice.classList.add('d-block');

        }

        window.showMovementNotice(
            'Audit evidence voucher cleared.',
            'Cleared',
            'info'
        );

    };


    /* =====================================================
       PROOF IMAGE ZOOM
    ====================================================== */

    window.zoomAdjustmentMedia = function() {

        const previewImg = document.getElementById('adjProofPreviewImg');
        const zoomImgTag = document.getElementById('modalZoomProofImgTag');

        if (previewImg && zoomImgTag && previewImg.src) {

            zoomImgTag.src = previewImg.src;

            bootstrap.Modal.getOrCreateInstance(
                document.getElementById('proofZoomModal')
            ).show();

        }

    };


    window.zoomProofAttachment = function(url, title = 'Audit Voucher') {

        const zoomImgTag = document.getElementById('modalZoomProofImgTag');

        if (url && zoomImgTag) {

            zoomImgTag.src = url;

            bootstrap.Modal.getOrCreateInstance(
                document.getElementById('proofZoomModal')
            ).show();

        }

    };


    /* =====================================================
       DATE FILTER
    ====================================================== */

    window.handleDateFilterChange = function(val) {

        const customPanel = document.getElementById('invCustomDatePanel');

        if (!customPanel) return;

        if (val === 'custom') {

            customPanel.classList.remove('d-none');

        } else {

            customPanel.classList.add('d-none');

            window.applyMovementFilter();

        }

    };


    /* =====================================================
       AJAX FILTER & PAGINATION
    ====================================================== */

    window.applyMovementFilter = function(targetUrl = null) {

        const form = document.getElementById('movement-filter-form');

        if (!form) return;

        const formData = new FormData(form);

        const params = new URLSearchParams(formData);

        params.set(
            'per_page',
            document.getElementById('inv-per-page').value
        );

        const cover = document.getElementById('invLoadingCover');

        if (cover) {

            cover.classList.remove('d-none');
            cover.classList.add('d-flex');

        }

        const url = targetUrl || (
            "{{ route('admin.inventory.movements') }}?" + params.toString()
        );

        fetch(url, {

            headers: {

                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'

            }

        })

        .then(res => {

            if (!res.ok) {
                throw new Error('Unable to load inventory movements.');
            }

            return res.json();

        })

        .then(data => {

            if (data.success) {

                const tbody = document.getElementById('movements-tbody');

                if (tbody) {
                    tbody.innerHTML = data.table_html;
                }

                /* Pagination */

                const paginationContainer = document.getElementById('inv-pagination-container');

                if (paginationContainer) {

                    paginationContainer.innerHTML = `

                        <div class="small text-muted font-monospace" style="font-size:11px;">

                            Total
                            <strong>${data.total_count}</strong>
                            records logged

                        </div>

                        <div id="invPaginationLinks">

                            ${data.pagination_html}

                        </div>

                    `;

                }

                /* Summary */

                if (data.summary) {

                    const additions = document.getElementById('val-inv-additions');
                    const deductions = document.getElementById('val-inv-deductions');
                    const net = document.getElementById('val-inv-net');

                    if (additions) {
                        additions.innerText = '+' + data.summary.additions;
                    }

                    if (deductions) {
                        deductions.innerText = '-' + data.summary.deductions;
                    }

                    if (net) {
                        net.innerText = data.summary.net_change;
                    }

                }

            }

        })

        .catch(err => {

            console.error('Inventory movement filter error:', err);

            window.showMovementNotice(
                'Unable to load inventory movement records. Please try again.',
                'Loading Error',
                'error'
            );

        })

        .finally(() => {

            if (cover) {

                cover.classList.add('d-none');
                cover.classList.remove('d-flex');

            }

        });

    };


    /* =====================================================
       PAGINATION CLICK DELEGATION
    ====================================================== */

    document.addEventListener('click', function(e) {

        const pageLink = e.target.closest('#invPaginationLinks a');

        if (pageLink) {

            e.preventDefault();

            const href = pageLink.getAttribute('href');

            if (
                href &&
                href !== '#' &&
                !href.startsWith('javascript:')
            ) {

                window.applyMovementFilter(href);

            }

        }

    });


    /* =====================================================
       CSV EXPORT
    ====================================================== */

    window.exportMovementsCsv = function() {

        const form = document.getElementById('movement-filter-form');

        if (!form) return;

        const formData = new FormData(form);

        const params = new URLSearchParams(formData);

        params.append('export', 'csv');

        const exportUrl =
            "{{ route('admin.inventory.movements') }}?" +
            params.toString();

        window.open(exportUrl, '_blank');

    };


    /* =====================================================
       LOAD PRODUCT VARIANTS
    ====================================================== */

    window.loadProductVariantsForAdjustment = function(productId) {

        const variantBox = document.getElementById('adjVariantBox');
        const variantSelect = document.getElementById('adj_variant_id');

        if (!variantBox || !variantSelect) return;

        variantSelect.innerHTML = '<option value="">— Choose Variant —</option>';

        if (!productId) {

            variantBox.classList.add('d-none');
            variantSelect.removeAttribute('required');

            return;

        }

        fetch(`/admin/api/products/${productId}/variants`, {

            headers: {
                'Accept': 'application/json'
            }

        })

        .then(res => {

            if (!res.ok) {
                throw new Error('Unable to load product variants.');
            }

            return res.json();

        })

        .then(variants => {

            if (Array.isArray(variants) && variants.length > 0) {

                variants.forEach(v => {

                    const label =
                        (v.color ? v.color + ' ' : '') +
                        (v.size ? '/ ' + v.size + ' ' : '') +
                        `[Stock: ${v.stock}] (SKU: ${v.sku || v.product_code || 'N/A'})`;

                    const option = document.createElement('option');

                    option.value = v.id;
                    option.dataset.stock = v.stock;
                    option.textContent = label;

                    variantSelect.appendChild(option);

                });

                variantBox.classList.remove('d-none');

                variantSelect.setAttribute('required', 'required');

            } else {

                variantBox.classList.add('d-none');

                variantSelect.removeAttribute('required');

            }

        })

        .catch(err => {

            console.error('Variant loading error:', err);

            variantBox.classList.add('d-none');

            variantSelect.removeAttribute('required');

        });

    };


    /* =====================================================
       MANUAL STOCK ADJUSTMENT SUBMIT
    ====================================================== */

    const adjustmentForm = document.getElementById('stockAdjustmentForm');

    if (adjustmentForm) {

        adjustmentForm.addEventListener('submit', function(e) {

            e.preventDefault();

            const form = this;

            const btn = document.getElementById('btn-submit-adj');

            if (!btn || btn.disabled) return;

            const originalButtonHtml = btn.innerHTML;

            btn.disabled = true;

            btn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-1"
                      style="width:12px;height:12px;"
                      role="status">
                </span>
                Applying...
            `;

            const formData = new FormData(form);

            fetch(form.action, {

                method: 'POST',

                body: formData,

                headers: {

                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'

                }

            })

            .then(res => {

                if (!res.ok && res.status !== 422) {
                    throw new Error('Server returned an error.');
                }

                return res.json();

            })

            .then(data => {

                if (data.success) {

                    const modalEl = document.getElementById('stockAdjustmentModal');

                    const modalInstance = bootstrap.Modal.getInstance(modalEl);

                    if (modalInstance) {
                        modalInstance.hide();
                    }

                    form.reset();

                    window.clearAdjustmentMedia();

                    const variantBox = document.getElementById('adjVariantBox');

                    const variantSelect = document.getElementById('adj_variant_id');

                    if (variantBox) {
                        variantBox.classList.add('d-none');
                    }

                    if (variantSelect) {

                        variantSelect.innerHTML =
                            '<option value="">— Choose Variant —</option>';

                        variantSelect.removeAttribute('required');

                    }

                    window.applyMovementFilter();

                    window.showMovementNotice(
                        'Stock adjustment applied successfully.',
                        'Adjustment Confirmed',
                        'success'
                    );

                } else {

                    window.showMovementNotice(
                        data.message || 'Failed to adjust stock.',
                        'Adjustment Error',
                        'error'
                    );

                }

            })

            .catch(err => {

                console.error('Adjustment error:', err);

                window.showMovementNotice(
                    'Server connection error while applying adjustment.',
                    'Server Error',
                    'error'
                );

            })

            .finally(() => {

                btn.disabled = false;

                btn.innerHTML = originalButtonHtml;

            });

        });

    }

})();
</script>

@endsection