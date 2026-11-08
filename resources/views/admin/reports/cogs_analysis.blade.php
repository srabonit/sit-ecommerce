
@extends('layouts.admin')

@section('title', 'COGS Analysis - ' . config('app.name'))

@section('admin_content')
<style>
    /* =========================================================
       COGS ANALYSIS — KAIADMIN LITE CLASSIC / WHITE CLASSIC
       Scoped styles only | Strict 5px radius
    ========================================================= */

    #cogs-report-root {
        --cogs-primary: #1572e8;
        --cogs-primary-soft: #eaf2ff;
        --cogs-navy: #03172e;
        --cogs-text: #2a2f36;
        --cogs-muted: #6b7280;
        --cogs-border: #e5e7eb;
        --cogs-light: #f8f9fa;
        --cogs-success: #28a745;
        --cogs-warning: #f0ad4e;
        --cogs-danger: #dc3545;
        min-width: 0;
        color: var(--cogs-text);
    }

    /* Consistent Radius */
    #cogs-report-root .btn,
    #cogs-report-root button,
    #cogs-report-root .badge,
    #cogs-report-root .alert,
    #cogs-report-root .form-control,
    #cogs-report-root .input-group-text,
    #cogs-report-root .page-link,
    #cogs-report-root .page-item span,
    #cogs-report-root .card,
    #cogs-report-root .cogs-card,
    #cogs-report-root .collapse-toggle-btn {
        border-radius: 5px !important;
    }

    #cogs-report-root .input-group > .input-group-text {
        border-radius: 5px 0 0 5px !important;
    }

    #cogs-report-root .input-group > .form-control {
        border-radius: 0 5px 5px 0 !important;
    }

    #cogs-report-root .bn-digit,
    #cogs-report-root .font-monospace {
        font-weight: 600 !important;
        font-variant-numeric: tabular-nums;
    }

    /* Page Header */
    #cogs-report-root .header-top-wrap {
        padding: 2px 0 15px;
        border-bottom: 1px solid var(--cogs-border);
    }

    #cogs-report-root .header-top-wrap h5 {
        color: var(--cogs-text) !important;
        font-size: 18px !important;
        font-weight: 700 !important;
        letter-spacing: -.25px;
    }

    #cogs-report-root .header-top-wrap h5 > i {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff5e6;
        color: #e69b20 !important;
        border-radius: 5px;
        font-size: 14px;
    }

    #cogs-report-root .header-top-wrap small {
        color: var(--cogs-muted) !important;
        font-size: 11.5px !important;
    }

    /* Compact Buttons */
    #cogs-report-root .btn-compact {
        min-height: 34px;
        padding: 6px 12px !important;
        font-size: 11.5px !important;
        line-height: 1.35 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        white-space: nowrap;
        transition: background-color .15s ease, border-color .15s ease, color .15s ease;
        box-shadow: none !important;
    }

    #cogs-report-root .btn-dark {
        background: var(--cogs-navy) !important;
        border-color: var(--cogs-navy) !important;
        color: #fff !important;
    }

    #cogs-report-root .btn-dark:hover {
        background: #102b49 !important;
        border-color: #102b49 !important;
    }

    #cogs-report-root .btn-outline-primary {
        color: var(--cogs-primary) !important;
        border-color: #b9d4fa !important;
        background: #fff !important;
    }

    #cogs-report-root .btn-outline-primary:hover {
        background: var(--cogs-primary-soft) !important;
        border-color: var(--cogs-primary) !important;
        color: var(--cogs-primary) !important;
    }

    #cogs-report-root .btn-outline-secondary {
        color: #596579 !important;
        border-color: #d8dee8 !important;
        background: #fff !important;
    }

    #cogs-report-root .btn-outline-secondary:hover {
        background: #f4f6f9 !important;
        border-color: #aeb8c6 !important;
        color: #263445 !important;
    }

    #cogs-report-root .top-action-btns {
        align-items: center;
    }

    /* Cards */
    #cogs-report-root .cogs-card {
        border: 1px solid var(--cogs-border) !important;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035) !important;
        margin-bottom: 15px;
        min-width: 0;
        overflow: hidden;
    }

    #cogs-report-root .cogs-card-header {
        min-height: 46px;
        padding: 10px 15px;
        background: #fff;
        border-bottom: 1px solid var(--cogs-border);
    }

    #cogs-report-root .cogs-card-header h6,
    #cogs-report-root .cogs-card-header > span {
        color: var(--cogs-text) !important;
        font-size: 12.5px !important;
        font-weight: 700 !important;
    }

    #cogs-report-root .cogs-card-header h6 > i,
    #cogs-report-root .cogs-card-header > span > i {
        color: var(--cogs-primary) !important;
    }

    #cogs-report-root .cogs-card .card-body {
        background: #fff;
    }

    /* Collapse Button */
    #cogs-report-root .collapse-toggle-btn {
        width: 28px;
        height: 28px;
        min-width: 28px;
        padding: 0;
        flex: 0 0 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #dce2ea;
        color: #657184;
        transition: all .15s ease;
    }

    #cogs-report-root .collapse-toggle-btn:hover {
        background: var(--cogs-primary-soft);
        border-color: #b7d2fa;
        color: var(--cogs-primary);
    }

    #cogs-report-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    #cogs-report-root .collapse-icon {
        font-size: 10px;
        transition: transform .2s ease;
    }

    /* Search Form */
    #cogs-report-root .form-control {
        min-height: 36px;
        padding: 7px 11px;
        font-size: 12px;
        color: var(--cogs-text);
        background-color: #fff;
        border: 1px solid #dce2ea;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #cogs-report-root .form-control::placeholder {
        color: #9aa3b2;
        opacity: 1;
    }

    #cogs-report-root .form-control:focus {
        border-color: var(--cogs-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
        outline: none;
    }

    #cogs-report-root .input-group-text {
        min-height: 36px;
        padding: 7px 11px;
        color: #7b8493 !important;
        background: #f8f9fb !important;
        border: 1px solid #dce2ea !important;
        font-size: 12px;
    }

    #cogs-report-root .input-group > .form-control {
        border-left: 0 !important;
    }

    #cogs-report-root .input-group:focus-within .input-group-text {
        border-color: var(--cogs-primary) !important;
        color: var(--cogs-primary) !important;
    }

    /* Item Count */
    #cogs-report-root .cogs-card-header .badge {
        font-size: 10px !important;
        font-weight: 600 !important;
        padding: 5px 9px !important;
        color: #596579 !important;
        background: #f4f6f9 !important;
        border: 1px solid #e2e6ec !important;
    }

    /* Table */
    #cogs-report-root .cogs-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #cogs-report-root .cogs-table thead th {
        padding: 11px 8px;
        color: #596579;
        background: #f7f8fa;
        border-bottom: 1px solid #e4e8ee;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .035em;
        vertical-align: middle;
        white-space: nowrap;
    }

    #cogs-report-root .cogs-table tbody td {
        padding: 10px 8px;
        color: #394353;
        background: #fff;
        border-bottom: 1px solid #edf0f3;
        font-size: 11.5px;
        vertical-align: middle;
        white-space: nowrap;
    }

    #cogs-report-root .cogs-table tbody tr:last-child td {
        border-bottom-color: #e4e8ee;
    }

    #cogs-report-root .cogs-table tbody tr:hover td {
        background: #f9fbfe;
    }

    #cogs-report-root .cogs-table tfoot td {
        padding: 11px 8px;
        color: #394353;
        background: #f7f8fa !important;
        border-top: 1px solid #e1e6ed;
        border-bottom: 0;
        font-size: 11px;
        vertical-align: middle;
        white-space: nowrap;
    }

    #cogs-report-root .cogs-table .text-primary {
        color: #2474d2 !important;
    }

    #cogs-report-root .cogs-table .text-success {
        color: #239653 !important;
    }

    #cogs-report-root .cogs-table .text-secondary {
        color: #697586 !important;
    }

    #cogs-report-root .product-title {
        display: block;
        max-width: 100%;
        color: #263445;
        font-size: 11.5px;
        font-weight: 700;
        line-height: 1.4;
    }

    #cogs-report-root .product-sku {
        display: block;
        max-width: 100%;
        margin-top: 3px;
        color: #8993a2;
        font-size: 9.5px;
        font-weight: 500 !important;
    }

    /* Margin Status */
    #cogs-report-root .cogs-table .badge {
        min-width: 43px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 6px !important;
        font-size: 9.5px !important;
        font-weight: 700 !important;
        line-height: 1.2;
    }

    /* Empty State */
    #cogs-report-root .cogs-table tbody td[colspan="9"] {
        padding: 35px 15px !important;
        color: #8993a2 !important;
        font-size: 12px !important;
    }

    /* Desktop Table — all 9 columns remain visible */
    @media (min-width: 992px) {
        #cogs-report-root .cogs-table {
            table-layout: fixed !important;
            width: 100% !important;
        }

        #cogs-report-root .pc-fit-container {
            width: 100%;
            overflow-x: auto;
        }

        #cogs-report-root .cogs-table th,
        #cogs-report-root .cogs-table td {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #cogs-report-root .col-cg-id     { width: 4% !important; }
        #cogs-report-root .col-cg-desc   { width: 24% !important; }
        #cogs-report-root .col-cg-units  { width: 9% !important; }
        #cogs-report-root .col-cg-cost   { width: 10% !important; }
        #cogs-report-root .col-cg-price  { width: 10% !important; }
        #cogs-report-root .col-cg-sales  { width: 13% !important; }
        #cogs-report-root .col-cg-cogs   { width: 12% !important; }
        #cogs-report-root .col-cg-profit { width: 12% !important; }
        #cogs-report-root .col-cg-margin { width: 6% !important; }
    }

    /* Pagination */
    #cogs-report-root .card-footer {
        background: #fafbfc !important;
        border-top: 1px solid var(--cogs-border) !important;
    }

    #cogs-report-root .card-footer nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }

    #cogs-report-root .card-footer nav > div.d-sm-none,
    #cogs-report-root .card-footer nav > .d-sm-none {
        display: none !important;
    }

    #cogs-report-root .card-footer nav > div.d-none,
    #cogs-report-root .card-footer nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #cogs-report-root .card-footer nav p,
    #cogs-report-root .card-footer nav .small.text-muted,
    #cogs-report-root .card-footer nav div:has(> p),
    #cogs-report-root .card-footer nav > div.d-none > div:first-child,
    #cogs-report-root .card-footer nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }

    #cogs-report-root .card-footer .pagination {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
        margin: 0 !important;
        padding: 0 !important;
        list-style: none !important;
    }

    #cogs-report-root .card-footer .page-item {
        display: inline-flex !important;
    }

    #cogs-report-root .card-footer .page-item .page-link,
    #cogs-report-root .card-footer .page-item span {
        min-width: 29px !important;
        height: 29px !important;
        padding: 0 7px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #596579 !important;
        background: #fff !important;
        border: 1px solid #dce2ea !important;
        border-radius: 5px !important;
        font-size: 10.5px !important;
        font-weight: 600 !important;
        line-height: 1 !important;
        text-decoration: none !important;
        transition: all .12s ease !important;
    }

    #cogs-report-root .card-footer .page-item .page-link:hover {
        color: var(--cogs-primary) !important;
        background: var(--cogs-primary-soft) !important;
        border-color: #b9d4fa !important;
    }

    #cogs-report-root .card-footer .page-item.active .page-link,
    #cogs-report-root .card-footer .page-item.active span {
        color: #fff !important;
        background: var(--cogs-navy) !important;
        border-color: var(--cogs-navy) !important;
        font-weight: 700 !important;
    }

    #cogs-report-root .card-footer .page-item.disabled .page-link,
    #cogs-report-root .card-footer .page-item.disabled span {
        color: #aab2bf !important;
        background: #f7f8fa !important;
        border-color: #e6e9ee !important;
        opacity: .7 !important;
        cursor: not-allowed !important;
    }

    #cogs-report-root .card-footer nav svg {
        width: 11px !important;
        height: 11px !important;
    }

    /* Tablet */
    @media (max-width: 991.98px) {
        #cogs-report-root .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        #cogs-report-root .cogs-table {
            min-width: 950px;
            table-layout: auto;
        }

        #cogs-report-root .cogs-card-header {
            padding: 10px 12px;
        }
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        #cogs-report-root {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #cogs-report-root .header-top-wrap {
            align-items: stretch !important;
            gap: 12px !important;
            padding-bottom: 13px;
        }

        #cogs-report-root .header-top-wrap h5 {
            font-size: 16px !important;
        }

        #cogs-report-root .header-top-wrap > div:first-child small {
            font-size: 11px !important;
        }

        #cogs-report-root .top-action-btns {
            width: 100%;
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 7px !important;
        }

        #cogs-report-root .top-action-btns .btn {
            width: 100%;
            min-width: 0;
            min-height: 34px;
            padding: 6px 5px !important;
            font-size: 10.5px !important;
        }

        #cogs-report-root .cogs-card {
            margin-bottom: 12px;
        }

        #cogs-report-root .cogs-card-header {
            min-height: 43px;
            padding: 9px 10px;
        }

        #cogs-report-root .cogs-card-header h6,
        #cogs-report-root .cogs-card-header > span {
            font-size: 11.5px !important;
        }

        #cogs-report-root .cogs-table {
            min-width: 950px;
        }

        #cogs-report-root .cogs-table thead th {
            padding: 10px 7px;
            font-size: 9.5px;
        }

        #cogs-report-root .cogs-table tbody td {
            padding: 9px 7px;
            font-size: 11px;
        }

        #cogs-report-root .cogs-table tfoot td {
            padding: 10px 7px;
        }

        #cogs-report-root .card-footer {
            flex-direction: column !important;
            gap: 9px !important;
            justify-content: center !important;
            padding: 10px !important;
        }

        #cogs-report-root .card-footer nav {
            width: 100% !important;
            justify-content: center !important;
        }

        #cogs-report-root .card-footer .pagination {
            gap: 3px !important;
        }

        #cogs-report-root .card-footer .page-item .page-link,
        #cogs-report-root .card-footer .page-item span {
            min-width: 27px !important;
            height: 27px !important;
            font-size: 10px !important;
        }
    }

    @media (max-width: 380px) {
        #cogs-report-root .top-action-btns .btn {
            font-size: 10px !important;
        }

        #cogs-report-root .cogs-card-header {
            padding-left: 8px;
            padding-right: 8px;
        }
    }

    /* Print */
    @media print {
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        body {
            background: #fff !important;
            color: #000 !important;
        }

        #cogs-report-root .no-print,
        .admin-dark-sidebar,
        .admin-header,
        .main-footer {
            display: none !important;
        }

        #cogs-report-root {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            color: #000 !important;
        }

        #cogs-report-root .cogs-card {
            margin-bottom: 10px !important;
            border: 1px solid #999 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            page-break-inside: avoid;
        }

        #cogs-report-root .cogs-card-header {
            background: #f3f3f3 !important;
            border-bottom: 1px solid #999 !important;
        }

        #cogs-report-root .collapse:not(.show) {
            display: block !important;
            height: auto !important;
        }

        #cogs-report-root .pc-fit-container {
            overflow: visible !important;
        }

        #cogs-report-root .cogs-table {
            width: 100% !important;
            min-width: 0 !important;
            table-layout: fixed !important;
        }

        #cogs-report-root .cogs-table th,
        #cogs-report-root .cogs-table td {
            overflow: visible !important;
            text-overflow: clip !important;
            color: #000 !important;
            white-space: normal !important;
        }

        #cogs-report-root .cogs-table thead th,
        #cogs-report-root .cogs-table tfoot td {
            background: #eee !important;
        }

        #cogs-report-root .cogs-table .badge {
            border: 1px solid #999 !important;
            background: #fff !important;
            color: #000 !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="cogs-report-root">

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 header-top-wrap no-print">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-boxes-stacked text-warning"></i>
                <span>COGS Analysis</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 11px;">
                Product cost, sales &amp; profit
            </small>
        </div>

        <div class="top-action-btns d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-dark btn-compact shadow-xs" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>

            <a href="{{ route('admin.report.profit_loss') }}"
               class="btn btn-sm btn-outline-primary btn-compact shadow-xs">
                <i class="fas fa-file-invoice-dollar"></i> Profit &amp; Loss
            </a>

            <a href="{{ route('admin.report.balance_sheet') }}"
               class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-scale-balanced"></i> Balance Sheet
            </a>

            <a href="{{ route('admin.report.cash_flow') }}"
               class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-money-bill-trend-up text-primary"></i> Cash Flow
            </a>
        </div>
    </div>

    <!-- Product Search -->
    <div class="card border-0 shadow-sm cogs-card mb-3 no-print">
        <div class="cogs-card-header d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                <i class="fas fa-filter text-primary me-1"></i> Product Search
            </span>

            <button type="button" class="collapse-toggle-btn shadow-xs"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseCogsSearch"
                    aria-expanded="true"
                    aria-controls="collapseCogsSearch"
                    title="Toggle">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseCogsSearch">
            <div class="p-3">
                <form action="{{ route('admin.report.cogs') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-12 col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" name="search"
                                   class="form-control border-start-0"
                                   placeholder="Product name or SKU"
                                   value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="col-12 col-md-auto d-flex gap-1">
                        <button type="submit" class="btn btn-dark btn-compact shadow-xs px-3">
                            <i class="fas fa-search"></i> Search
                        </button>

                        @if(request('search'))
                            <a href="{{ route('admin.report.cogs') }}"
                               class="btn btn-light border btn-compact shadow-xs">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Product Profitability -->
    <div class="card border-0 shadow-sm cogs-card overflow-hidden">
        <div class="cogs-card-header d-flex justify-content-between align-items-center gap-2">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13px;">
                <i class="fas fa-table-list text-primary"></i>
                <span>Product Profitability</span>
            </h6>

            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border font-monospace px-2 py-1"
                      style="font-size: 10px;">
                    {{ $items->total() }} Items
                </span>

                <button type="button" class="collapse-toggle-btn shadow-xs no-print"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseCogsTable"
                        aria-expanded="true"
                        aria-controls="collapseCogsTable"
                        title="Toggle">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseCogsTable">
            <div class="card-body p-0">
                <div class="table-responsive pc-fit-container">
                    <table class="table align-middle cogs-table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3 col-cg-id text-center">#</th>
                                <th class="col-cg-desc">Product / SKU</th>
                                <th class="text-center col-cg-units">Sold</th>
                                <th class="text-end col-cg-cost">Avg Cost (৳)</th>
                                <th class="text-end col-cg-price">Avg Price (৳)</th>
                                <th class="text-end col-cg-sales">Sales (৳)</th>
                                <th class="text-end col-cg-cogs">COGS (৳)</th>
                                <th class="text-end col-cg-profit">Profit (৳)</th>
                                <th class="text-end pe-3 col-cg-margin">Margin</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php
                                $pageTotalUnits = 0;
                                $pageTotalSales = 0.00;
                                $pageTotalCogs  = 0.00;
                            @endphp

                            @forelse($items as $idx => $row)
                                @php
                                    $sales = (float) $row->total_sales;
                                    $cost = (float) $row->total_cogs;
                                    $profit = max(0.00, $sales - $cost);
                                    $margin = $sales > 0 ? round(($profit / $sales) * 100, 1) : 0;

                                    $pageTotalUnits += (int)$row->units_sold;
                                    $pageTotalSales += $sales;
                                    $pageTotalCogs  += $cost;
                                @endphp

                                <tr>
                                    <td class="ps-3 col-cg-id text-center font-monospace bn-digit text-muted" style="font-size: 10.5px;">
                                        {{ $items->firstItem() + $idx }}
                                    </td>

                                    <td class="col-cg-desc">
                                        <div class="min-w-0">
                                            <strong class="product-title text-truncate"
                                                    title="{{ $row->product_title ?? 'Product #' . $row->product_id }}">
                                                {{ $row->product_title ?? 'Product #' . $row->product_id }}
                                            </strong>
                                            <small class="product-sku font-monospace text-truncate">
                                                SKU: {{ $row->sku ?? 'N/A' }}
                                            </small>
                                        </div>
                                    </td>

                                    <td class="text-center col-cg-units font-monospace bn-digit" style="font-size: 11px;">
                                        &times; {{ $row->units_sold }}
                                    </td>

                                    <td class="text-end col-cg-cost font-monospace bn-digit text-muted" style="font-size: 11px;">
                                        ৳ {{ number_format((float)$row->avg_cost, 2) }}
                                    </td>

                                    <td class="text-end col-cg-price font-monospace bn-digit text-dark" style="font-size: 11px;">
                                        ৳ {{ number_format((float)$row->avg_price, 2) }}
                                    </td>

                                    <td class="text-end col-cg-sales font-monospace bn-digit text-primary fw-bold" style="font-size: 11px;">
                                        ৳ {{ number_format($sales, 2) }}
                                    </td>

                                    <td class="text-end col-cg-cogs font-monospace bn-digit text-secondary" style="font-size: 11px;">
                                        ৳ {{ number_format($cost, 2) }}
                                    </td>

                                    <td class="text-end col-cg-profit font-monospace bn-digit text-success fw-bold" style="font-size: 11px;">
                                        ৳ {{ number_format($profit, 2) }}
                                    </td>

                                    <td class="text-end pe-3 col-cg-margin">
                                        <span class="badge {{ $margin >= 25 ? 'bg-success-subtle text-success border border-success-subtle' : ($margin >= 10 ? 'bg-warning-subtle text-dark border border-warning-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle') }} font-monospace px-1 py-0"
                                              style="font-size: 9.5px;">
                                            {{ $margin }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted small">
                                        No products found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        @if($items->isNotEmpty())
                            @php
                                $pageTotalProfit = max(0.00, $pageTotalSales - $pageTotalCogs);
                                $pageOverallMargin = $pageTotalSales > 0 ? round(($pageTotalProfit / $pageTotalSales) * 100, 1) : 0;
                            @endphp

                            <tfoot>
                                <tr class="bg-light fw-bold" style="font-size: 11px;">
                                    <td colspan="2" class="ps-3 text-secondary">
                                        Page Total ({{ $items->count() }})
                                    </td>

                                    <td class="text-center font-monospace bn-digit">
                                        &times; {{ $pageTotalUnits }}
                                    </td>

                                    <td colspan="2" class="text-end text-muted font-monospace">—</td>

                                    <td class="text-end font-monospace bn-digit text-primary">
                                        ৳ {{ number_format($pageTotalSales, 2) }}
                                    </td>

                                    <td class="text-end font-monospace bn-digit text-secondary">
                                        ৳ {{ number_format($pageTotalCogs, 2) }}
                                    </td>

                                    <td class="text-end font-monospace bn-digit text-success">
                                        ৳ {{ number_format($pageTotalProfit, 2) }}
                                    </td>

                                    <td class="text-end pe-3 font-monospace bn-digit text-dark">
                                        {{ $pageOverallMargin }}%
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            @if($items->hasPages())
                <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">
                    <small class="text-muted bn-digit font-monospace" style="font-size: 11px;">
                        {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ $items->total() }}
                    </small>

                    <div>
                        {{ $items->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
