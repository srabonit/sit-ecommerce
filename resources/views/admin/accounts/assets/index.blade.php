@extends('layouts.admin')

@section('title', 'Fixed Assets - ' . config('app.name'))

@section('admin_content')

<style>
/* =========================================================
   FIXED ASSETS — KAIADMIN WHITE CLASSIC
   Scoped UI | Bootstrap 5 | Responsive
========================================================= */

#assets-root,
.asset-modal-scope {
    --asset-primary: var(--bs-primary, #1572e8);
    --asset-secondary: #686c78;
    --asset-success: #31ce36;
    --asset-warning: #ffad46;
    --asset-danger: #f25961;
    --asset-info: #48abf7;
    --asset-bg: #f8f9fa;
    --asset-card: #ffffff;
    --asset-border: #ebedf2;
    --asset-heading: #2a2f5b;
    --asset-text: #495057;
    --asset-muted: #8d9498;
    --asset-radius: 5px;
    --asset-shadow: 0 1px 3px rgba(0,0,0,.04);
}

/* Page */
#assets-root {
    width: 100%;
    min-width: 0;
    padding: 20px 20px 28px !important;
    color: var(--asset-text);
    font-size: 13px;
}

#assets-root *,
.asset-modal-scope * {
    box-sizing: border-box;
}

#assets-root .text-dark {
    color: var(--asset-heading) !important;
}

/* Common Radius */
#assets-root .btn,
#assets-root button,
#assets-root .badge,
#assets-root .alert,
#assets-root .form-control,
#assets-root .form-select,
#assets-root .page-link,
#assets-root .card,
#assets-root .asset-card,
#assets-root .kpi-stat-card,
.asset-modal-scope .modal-content,
.asset-modal-scope .btn,
.asset-modal-scope .form-control,
.asset-modal-scope .form-select,
.asset-modal-scope .badge {
    border-radius: var(--asset-radius) !important;
}

/* Header */
#assets-root .asset-page-header {
    margin-bottom: 20px;
}

#assets-root .asset-page-title {
    font-size: clamp(17px, 2vw, 21px);
    font-weight: 700;
    color: var(--asset-heading);
    letter-spacing: -.3px;
    line-height: 1.35;
}

#assets-root .asset-page-title i {
    color: var(--asset-primary);
}

#assets-root .asset-page-description {
    color: var(--asset-muted);
    font-size: 12px;
    line-height: 1.5;
}

#assets-root .top-action-btns {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 7px;
}

/* Buttons */
#assets-root .btn-compact,
.asset-modal-scope .btn-compact {
    min-height: 32px;
    padding: 6px 12px !important;
    font-size: 11.5px !important;
    line-height: 1.35 !important;
    font-weight: 600 !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    white-space: nowrap;
    transition: all .2s ease;
}

#assets-root .btn-primary,
.asset-modal-scope .btn-primary {
    background-color: var(--asset-primary);
    border-color: var(--asset-primary);
}

#assets-root .btn-primary:hover,
.asset-modal-scope .btn-primary:hover {
    filter: brightness(.94);
}

#assets-root .btn:focus-visible,
.asset-modal-scope .btn:focus-visible {
    outline: 2px solid var(--asset-primary);
    outline-offset: 2px;
}

/* Cards */
#assets-root .asset-card {
    background: var(--asset-card);
    border: 1px solid var(--asset-border) !important;
    box-shadow: var(--asset-shadow) !important;
    margin-bottom: 16px;
}

#assets-root .card-header {
    background: #fff !important;
    border-bottom: 1px solid var(--asset-border) !important;
}

#assets-root .card-header h6 {
    color: var(--asset-heading);
    font-size: 13px;
    font-weight: 700;
}

/* KPI Cards */
#assets-root .kpi-stat-card {
    position: relative;
    height: 100%;
    min-height: 105px;
    padding: 15px 16px 14px 19px;
    overflow: hidden;
    background: var(--asset-card);
    border: 1px solid var(--asset-border);
    box-shadow: var(--asset-shadow);
    transition: box-shadow .2s ease, transform .2s ease;
}

#assets-root .kpi-stat-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,.06);
    transform: translateY(-2px);
}

#assets-root .kpi-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    width: 3px;
}

#assets-root .kpi-valuation::before {
    background: var(--asset-success);
}

#assets-root .kpi-total-count::before {
    background: var(--asset-primary);
}

#assets-root .kpi-period-cost::before {
    background: var(--asset-warning);
}

#assets-root .kpi-period-count::before {
    background: #6861ce;
}

#assets-root .kpi-label {
    display: block;
    margin-bottom: 7px;
    color: var(--asset-muted);
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: .35px;
    text-transform: uppercase;
}

#assets-root .kpi-value {
    display: block;
    margin-bottom: 4px;
    color: var(--asset-heading);
    font-size: clamp(16px, 1.7vw, 21px);
    font-weight: 700;
    line-height: 1.3;
    overflow-wrap: anywhere;
}

#assets-root .kpi-description {
    display: block;
    color: var(--asset-muted);
    font-size: 10.5px;
    line-height: 1.4;
}

/* Typography */
#assets-root .bn-digit,
#assets-root .font-monospace {
    font-weight: 600;
}

#assets-root .form-label,
.asset-modal-scope .form-label {
    display: block;
    margin-bottom: 6px;
    color: #495057;
    font-size: 11.5px;
    font-weight: 600;
}

/* Form Controls */
#assets-root .form-control,
#assets-root .form-select,
.asset-modal-scope .form-control,
.asset-modal-scope .form-select {
    width: 100%;
    min-height: 36px;
    padding: 7px 10px;
    border: 1px solid #e4e6ef;
    background-color: #fff;
    color: var(--asset-text);
    font-size: 12px;
    box-shadow: none;
    transition: border-color .2s ease, box-shadow .2s ease;
}

#assets-root .form-control:focus,
#assets-root .form-select:focus,
.asset-modal-scope .form-control:focus,
.asset-modal-scope .form-select:focus {
    border-color: var(--asset-primary);
    box-shadow: 0 0 0 3px rgba(21,114,232,.10);
    outline: none;
}

#assets-root .form-control::placeholder,
.asset-modal-scope .form-control::placeholder {
    color: #adb5bd;
    opacity: 1;
}

#assets-root .input-group > .input-group-text {
    border-color: #e4e6ef;
    background: #f8f9fa;
}

#assets-root .input-group > .input-group-text {
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
}

#assets-root .input-group > .form-control,
#assets-root .input-group > .form-select {
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
}

/* Alerts */
#assets-root .alert {
    border: 1px solid var(--asset-border) !important;
    box-shadow: none !important;
}

#assets-root .alert-success {
    background: #f0faf1;
    border-left: 3px solid var(--asset-success) !important;
}

#assets-root .alert-danger {
    background: #fff5f5;
    border-left: 3px solid var(--asset-danger) !important;
}

/* Filter Panel */
#assets-root .asset-filter-card {
    padding: 17px !important;
}

#assets-root .asset-filter-card .form-label {
    margin-bottom: 6px;
}

/* Table */
#assets-root .asset-table {
    width: 100%;
    min-width: 1080px;
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
    table-layout: auto;
}

#assets-root .asset-table thead th {
    padding: 11px 9px;
    background: #f8f9fa;
    color: #686c78;
    border-bottom: 1px solid var(--asset-border);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .35px;
    text-transform: uppercase;
    white-space: nowrap;
    vertical-align: middle;
}

#assets-root .asset-table tbody td {
    padding: 10px 9px;
    color: var(--asset-text);
    border-bottom: 1px solid #f1f1f1;
    font-size: 11.5px;
    line-height: 1.45;
    vertical-align: middle;
}

#assets-root .asset-table tbody tr {
    transition: background-color .15s ease;
}

#assets-root .asset-table tbody tr:hover {
    background: #f8f9fa;
}

#assets-root .asset-table tbody tr:last-child td {
    border-bottom: 0;
}

#assets-root .asset-table .col-ast-name {
    min-width: 190px;
    white-space: normal;
}

#assets-root .asset-table .col-ast-code {
    min-width: 105px;
}

#assets-root .asset-table .col-ast-type {
    min-width: 120px;
}

#assets-root .asset-table .col-ast-account {
    min-width: 130px;
}

#assets-root .asset-table .col-ast-cost {
    min-width: 105px;
}

#assets-root .asset-table .col-ast-action,
#assets-root .asset-table .col-ast-receipt {
    min-width: 65px;
}

#assets-root .asset-table .badge {
    font-weight: 600;
    letter-spacing: 0;
}

#assets-root .asset-table .text-truncate {
    max-width: 100%;
}

#assets-root .table-responsive {
    width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: #ced4da #f8f9fa;
}

#assets-root .table-responsive::-webkit-scrollbar {
    height: 6px;
}

#assets-root .table-responsive::-webkit-scrollbar-track {
    background: #f8f9fa;
}

#assets-root .table-responsive::-webkit-scrollbar-thumb {
    background: #ced4da;
    border-radius: 5px;
}

/* Table Action */
#assets-root .asset-delete-btn {
    width: 29px;
    height: 29px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--asset-danger);
    border: 1px solid #f1d5d7;
    background: #fff;
}

#assets-root .asset-delete-btn:hover {
    color: #fff;
    background: var(--asset-danger);
    border-color: var(--asset-danger);
}

/* Pagination */
#assets-root .card-footer {
    padding: 11px 15px !important;
    background: #fff !important;
    border-top: 1px solid var(--asset-border) !important;
}

#assets-root .card-footer nav {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin: 0;
}

#assets-root .card-footer nav > div.d-sm-none,
#assets-root .card-footer nav > .d-sm-none {
    display: none !important;
}

#assets-root .card-footer nav p,
#assets-root .card-footer nav .small.text-muted,
#assets-root .card-footer nav div:has(> p),
#assets-root .card-footer nav > div.d-none > div:first-child,
#assets-root .card-footer nav .d-none.flex-sm-fill > div:first-child {
    display: none !important;
}

#assets-root .card-footer nav > div.d-none,
#assets-root .card-footer nav > .d-none.flex-sm-fill {
    display: flex !important;
    align-items: center;
    justify-content: center;
}

#assets-root .card-footer .pagination {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 4px;
    margin: 0;
    padding: 0;
}

#assets-root .card-footer .page-item .page-link,
#assets-root .card-footer .page-item span {
    min-width: 29px;
    height: 29px;
    padding: 0 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #686c78;
    background: #fff;
    border: 1px solid var(--asset-border);
    border-radius: 5px;
    font-size: 11px;
    font-weight: 600;
    line-height: 1;
    text-decoration: none;
}

#assets-root .card-footer .page-item .page-link:hover {
    color: var(--asset-primary);
    background: #f4f8ff;
    border-color: var(--asset-primary);
}

#assets-root .card-footer .page-item.active .page-link,
#assets-root .card-footer .page-item.active span {
    color: #fff;
    background: var(--asset-primary);
    border-color: var(--asset-primary);
}

#assets-root .card-footer .page-item.disabled .page-link,
#assets-root .card-footer .page-item.disabled span {
    opacity: .45;
    cursor: not-allowed;
}

#assets-root .card-footer nav svg {
    width: 12px;
    height: 12px;
}

/* Empty State */
#assets-root .asset-empty-state {
    padding: 45px 15px !important;
    color: var(--asset-muted);
}

#assets-root .asset-empty-state i {
    color: #c7cbd1;
}

/* Modal */
.asset-modal-scope {
    --asset-modal-radius: 5px;
}

.asset-modal-scope .modal-dialog {
    width: calc(100% - 30px);
    max-width: 720px;
    margin: 1.5rem auto;
}

.asset-modal-scope .modal-content {
    overflow: hidden;
    border: 1px solid var(--asset-border) !important;
    box-shadow: 0 12px 40px rgba(0,0,0,.15) !important;
}

.asset-modal-scope .modal-header {
    min-height: 52px;
    padding: 12px 17px !important;
    background: #fff !important;
    color: var(--asset-heading) !important;
    border-bottom: 1px solid var(--asset-border);
}

.asset-modal-scope .modal-title {
    color: var(--asset-heading);
    font-size: 14px !important;
    font-weight: 700;
}

.asset-modal-scope .modal-title i {
    color: var(--asset-primary) !important;
}

.asset-modal-scope .modal-body {
    padding: 20px !important;
    background: #fff;
}

.asset-modal-scope .modal-footer {
    padding: 12px 17px !important;
    background: #f8f9fa !important;
    border-top: 1px solid var(--asset-border);
}

/* Responsive */
@media (min-width: 1200px) {
    #assets-root {
        padding-left: 24px !important;
        padding-right: 24px !important;
    }
}

@media (max-width: 991.98px) {
    #assets-root {
        padding: 17px 15px 24px !important;
    }

    #assets-root .asset-filter-card {
        padding: 14px !important;
    }

    #assets-root .asset-page-header {
        align-items: flex-start !important;
    }

    #assets-root .top-action-btns {
        justify-content: flex-start;
    }

    #assets-root .kpi-stat-card {
        min-height: 100px;
    }
}

@media (max-width: 767.98px) {
    #assets-root {
        padding: 14px 12px 20px !important;
    }

    #assets-root .header-top-wrap {
        align-items: stretch !important;
        gap: 12px !important;
    }

    #assets-root .asset-page-title {
        font-size: 17px;
    }

    #assets-root .asset-page-description {
        font-size: 11.5px;
    }

    #assets-root .top-action-btns {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        width: 100%;
        gap: 7px;
    }

    #assets-root .top-action-btns .btn {
        width: 100%;
        min-height: 36px;
        padding: 7px 5px !important;
        font-size: 10.5px !important;
    }

    #assets-root .kpi-stat-card {
        min-height: 100px;
        padding: 12px 10px 12px 14px;
    }

    #assets-root .kpi-label {
        font-size: 9.5px;
    }

    #assets-root .kpi-value {
        font-size: 16px;
    }

    #assets-root .kpi-description {
        font-size: 9.5px;
    }

    #assets-root .asset-filter-card {
        padding: 13px !important;
    }

    #assets-root .asset-filter-card form > div {
        min-width: 0;
    }

    #assets-root .asset-table {
        min-width: 1080px;
    }

    #assets-root .card-header {
        align-items: flex-start !important;
        gap: 8px;
        flex-wrap: wrap;
    }

    #assets-root .card-header .badge {
        white-space: normal;
    }

    #assets-root .card-footer {
        flex-direction: column !important;
        align-items: center !important;
        gap: 10px !important;
    }

    #assets-root .card-footer nav {
        width: 100%;
        justify-content: center;
    }

    .asset-modal-scope .modal-dialog {
        width: calc(100% - 20px);
        margin: 10px auto;
    }

    .asset-modal-scope .modal-body {
        padding: 15px !important;
    }
}

@media (max-width: 400px) {
    #assets-root {
        padding-left: 9px !important;
        padding-right: 9px !important;
    }

    #assets-root .row.g-2 {
        --bs-gutter-x: .5rem;
        --bs-gutter-y: .5rem;
    }

    #assets-root .kpi-stat-card {
        padding: 10px 8px 10px 12px;
    }

    #assets-root .kpi-value {
        font-size: 14px;
    }

    #assets-root .top-action-btns .btn {
        font-size: 10px !important;
    }

    .asset-modal-scope .modal-dialog {
        width: calc(100% - 12px);
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

    #assets-root .no-print,
    .admin-dark-sidebar,
    .admin-header,
    .main-footer {
        display: none !important;
    }

    #assets-root {
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    #assets-root .asset-card,
    #assets-root .kpi-stat-card {
        border: 1px solid #ccc !important;
        box-shadow: none !important;
    }

    #assets-root .table-responsive {
        overflow: visible !important;
    }

    #assets-root .asset-table {
        min-width: 0 !important;
        width: 100% !important;
        table-layout: auto !important;
    }

    #assets-root .asset-table th,
    #assets-root .asset-table td {
        padding: 5px 4px !important;
        overflow: visible !important;
        white-space: normal !important;
    }
}
</style>

<div class="container-fluid" id="assets-root">

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 header-top-wrap asset-page-header no-print">
        <div>
            <h5 class="asset-page-title mb-1 d-flex align-items-center gap-2">
                <i class="fas fa-boxes-stacked"></i>
                <span>Fixed Assets</span>
            </h5>
            <small class="asset-page-description d-block">
                Manage company assets, equipment and purchase records.
            </small>
        </div>

        <div class="top-action-btns">
            <button type="button" class="btn btn-sm btn-dark btn-compact" onclick="window.print()">
                <i class="fas fa-print"></i>
                <span>Print</span>
            </button>

            <button type="button" class="btn btn-sm btn-primary btn-compact fw-bold" data-bs-toggle="modal" data-bs-target="#addAssetModal">
                <i class="fas fa-plus"></i>
                <span>Add Asset</span>
            </button>

            <a href="{{ url('/admin/accounts/assets/export-csv') }}" class="btn btn-sm btn-outline-success btn-compact">
                <i class="fas fa-file-csv"></i>
                <span>Export CSV</span>
            </a>

            <a href="{{ url('/admin/accounts/bank-balance') }}" class="btn btn-sm btn-outline-secondary btn-compact">
                <i class="fas fa-landmark"></i>
                <span>Bank Balances</span>
            </a>

            <a href="{{ route('admin.report.balance_sheet') }}" class="btn btn-sm btn-outline-secondary btn-compact">
                <i class="fas fa-scale-balanced"></i>
                <span>Balance Sheet</span>
            </a>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success py-2 px-3 mb-3 small d-flex align-items-center justify-content-between no-print">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger py-2 px-3 mb-3 small d-flex align-items-center justify-content-between no-print">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-triangle-exclamation text-danger"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- KPI Cards -->
    <div class="row g-2 g-lg-3 mb-3">

        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-valuation">
                <span class="kpi-label">Total Valuation</span>
                <h5 class="kpi-value font-monospace bn-digit">
                    ৳ {{ number_format($totalValuation, 2) }}
                </h5>
                <small class="kpi-description">Total company asset value</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-total-count">
                <span class="kpi-label">Total Assets</span>
                <h5 class="kpi-value font-monospace bn-digit">
                    {{ $totalAssetCount }} Items
                </h5>
                <small class="kpi-description">Registered assets</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-period-cost">
                <span class="kpi-label">Period Acquisitions</span>
                <h5 class="kpi-value font-monospace bn-digit">
                    ৳ {{ number_format($periodAdditions, 2) }}
                </h5>
                <small class="kpi-description font-monospace">
                    {{ $start->format('d M Y') }} — {{ $end->format('d M Y') }}
                </small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-stat-card kpi-period-count">
                <span class="kpi-label">Period Purchases</span>
                <h5 class="kpi-value font-monospace bn-digit">
                    {{ $periodCount }} Assets
                </h5>
                <small class="kpi-description">Purchased in this period</small>
            </div>
        </div>

    </div>

    <!-- Filters -->
    <div class="card asset-card asset-filter-card mb-3 no-print">
        <form action="{{ url('/admin/accounts/assets') }}" method="GET" class="row g-2 align-items-end">

            <div class="col-12 col-md-3">
                <label class="form-label">Reporting Period</label>
                <select name="date_filter" id="assetDateFilter" class="form-select font-monospace" onchange="toggleCustomAssetDates(this.value)">
                    <option value="all" {{ $dateFilter === 'all' ? 'selected' : '' }}>All Time</option>
                    <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ $dateFilter === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="this_week" {{ $dateFilter === 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ $dateFilter === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $dateFilter === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="this_year" {{ $dateFilter === 'this_year' ? 'selected' : '' }}>This Year</option>
                    <option value="custom" {{ $dateFilter === 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>

            <div class="col-6 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="assetStartCol">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control font-monospace bn-digit" value="{{ request('start_date', $start->format('Y-m-d')) }}">
            </div>

            <div class="col-6 col-md-2 {{ $dateFilter === 'custom' ? '' : 'd-none' }}" id="assetEndCol">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control font-monospace bn-digit" value="{{ request('end_date', $end->format('Y-m-d')) }}">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label">Category</label>
                <select name="asset_type" class="form-select font-monospace">
                    <option value="all">All Categories</option>
                    <option value="electronics" {{ request('asset_type') === 'electronics' ? 'selected' : '' }}>Electronics &amp; IT</option>
                    <option value="furniture" {{ request('asset_type') === 'furniture' ? 'selected' : '' }}>Office Furniture</option>
                    <option value="vehicle" {{ request('asset_type') === 'vehicle' ? 'selected' : '' }}>Vehicles &amp; Bikes</option>
                    <option value="office_equipment" {{ request('asset_type') === 'office_equipment' ? 'selected' : '' }}>Office Equipment</option>
                    <option value="machinery" {{ request('asset_type') === 'machinery' ? 'selected' : '' }}>Machinery &amp; Tools</option>
                    <option value="others" {{ request('asset_type') === 'others' ? 'selected' : '' }}>Others</option>
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label">Search Assets</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Name, code or note...">
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-compact flex-grow-1 fw-bold">
                    <i class="fas fa-filter"></i> Filter
                </button>

                @if(request()->anyFilled(['date_filter', 'asset_type', 'search', 'start_date', 'end_date']))
                    <a href="{{ url('/admin/accounts/assets') }}" class="btn btn-light border btn-compact">
                        Reset
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Assets Table -->
    <div class="card asset-card overflow-hidden">

        <div class="card-header py-3 px-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-boxes-stacked text-primary"></i>
                <span>Asset Register</span>
            </h6>

            <span class="badge bg-light text-secondary border font-monospace px-2 py-2" style="font-size: 10px;">
                {{ $assets->firstItem() ?? 0 }} - {{ $assets->lastItem() ?? 0 }} / {{ $assets->total() }}
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive pc-fit-container">

                <table class="table align-middle asset-table mb-0">

                    <thead>
                        <tr>
                            <th class="ps-3 col-ast-id text-center">#</th>
                            <th class="col-ast-code">Asset Code</th>
                            <th class="col-ast-name">Asset Name</th>
                            <th class="col-ast-type">Category</th>
                            <th class="col-ast-date">Purchase Date</th>
                            <th class="col-ast-warranty">Warranty Expiry</th>
                            <th class="col-ast-account">Paid Account</th>
                            <th class="text-end col-ast-cost">Cost (৳)</th>
                            <th class="text-center col-ast-receipt no-print">Receipt</th>
                            <th class="text-end pe-3 col-ast-action no-print">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($assets as $idx => $ast)

                            <tr id="asset-row-{{ $ast->id }}">

                                <td class="ps-3 col-ast-id text-center font-monospace bn-digit text-muted">
                                    {{ $assets->firstItem() + $idx }}
                                </td>

                                <td class="col-ast-code">
                                    <span class="badge bg-light text-dark border font-monospace text-truncate d-inline-block px-2 py-1" style="font-size: 10px; max-width: 100%;">
                                        {{ $ast->asset_code ?? 'AST-' . $ast->id }}
                                    </span>
                                </td>

                                <td class="col-ast-name">
                                    <div class="min-w-0">
                                        <strong class="text-dark d-block text-truncate" style="font-size: 12px; line-height: 1.4;" title="{{ $ast->name }}">
                                            {{ $ast->name }}
                                        </strong>

                                        @if(!empty($ast->note))
                                            <small class="text-muted d-block text-truncate" style="font-size: 10px;" title="{{ $ast->note }}">
                                                {{ $ast->note }}
                                            </small>
                                        @endif
                                    </div>
                                </td>

                                <td class="col-ast-type">
                                    <span class="badge bg-secondary-subtle text-secondary border font-monospace text-truncate d-inline-block px-2 py-1" style="font-size: 9.5px; max-width: 100%;">
                                        {{ strtoupper(str_replace('_', ' ', $ast->asset_type)) }}
                                    </span>
                                </td>

                                <td class="col-ast-date font-monospace bn-digit text-muted">
                                    {{ date('d M Y', strtotime($ast->purchase_date)) }}
                                </td>

                                <td class="col-ast-warranty font-monospace bn-digit">
                                    @if(!empty($ast->warranty_expiry))
                                        @php
                                            $isExpired = strtotime($ast->warranty_expiry) < time();
                                        @endphp

                                        <span class="{{ $isExpired ? 'text-danger' : 'text-success' }}" title="{{ $isExpired ? 'Expired' : 'Valid' }}">
                                            {{ date('d M Y', strtotime($ast->warranty_expiry)) }}
                                        </span>
                                    @else
                                        <span class="text-muted font-monospace">—</span>
                                    @endif
                                </td>

                                <td class="col-ast-account">
                                    <span class="badge bg-light text-primary border font-monospace px-2 py-1 text-truncate d-inline-block" style="font-size: 9.5px; max-width: 100%;" title="{{ $ast->bank_account_name ?? $ast->payment_method }}">
                                        {{ $ast->bank_account_name ?? $ast->payment_method }}
                                    </span>
                                </td>

                                <td class="text-end col-ast-cost font-monospace bn-digit fw-bold text-dark">
                                    ৳ {{ number_format((float)$ast->purchase_cost, 2) }}
                                </td>

                                <td class="text-center col-ast-receipt no-print">
                                    @if(!empty($ast->voucher_receipt))
                                        <a href="{{ asset($ast->voucher_receipt) }}" target="_blank" class="btn btn-light border btn-compact py-1 px-2 text-secondary" title="View Receipt">
                                            <i class="fas fa-file-invoice"></i>
                                        </a>
                                    @else
                                        <span class="text-muted font-monospace">—</span>
                                    @endif
                                </td>

                                <td class="text-end pe-3 col-ast-action no-print">
                                    <button type="button" class="btn asset-delete-btn" onclick="deleteAsset({{ $ast->id }})" title="Delete Asset">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="10" class="asset-empty-state text-center">
                                    <i class="fas fa-boxes-stacked fa-2x mb-3 d-block opacity-50"></i>
                                    <span class="fw-semibold">No assets found for this period.</span>
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>
        </div>

        @if($assets->hasPages())
            <div class="card-footer d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-white no-print">

                <small class="text-muted bn-digit font-monospace" style="font-size: 11px;">
                    Showing {{ $assets->firstItem() }} to {{ $assets->lastItem() }} of {{ $assets->total() }} entries
                </small>

                <div>
                    {{ $assets->links('pagination::bootstrap-5') }}
                </div>

            </div>
        @endif

    </div>

</div>

<!-- Add Asset Modal -->
<div class="modal fade asset-modal-scope" id="addAssetModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header d-flex justify-content-between align-items-center">

                <h6 class="modal-title fw-bold d-flex align-items-center gap-2 mb-0">
                    <i class="fas fa-plus-circle"></i>
                    <span>Register Asset</span>
                </h6>

                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>

            </div>

            <form action="{{ url('/admin/accounts/assets/store') }}" method="POST" enctype="multipart/form-data">

                @csrf

                <div class="modal-body">

                    <div class="row g-3">

                        <!-- Asset Name -->
                        <div class="col-12">
                            <label class="form-label">
                                Asset Name <span class="text-danger">*</span>
                            </label>

                            <input type="text" name="name" class="form-control" placeholder="Office PC, Printer, Chair" required>
                        </div>

                        <!-- Category -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Category <span class="text-danger">*</span>
                            </label>

                            <select name="asset_type" class="form-select font-monospace" required>
                                <option value="electronics" selected>Electronics &amp; Computers</option>
                                <option value="furniture">Furniture &amp; Fixtures</option>
                                <option value="vehicle">Vehicles &amp; Delivery Bikes</option>
                                <option value="office_equipment">Office Equipment / AC</option>
                                <option value="machinery">Machinery &amp; Tools</option>
                                <option value="others">Other Property</option>
                            </select>
                        </div>

                        <!-- Purchase Cost -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Purchase Cost (৳) <span class="text-danger">*</span>
                            </label>

                            <input type="number" name="purchase_cost" class="form-control font-monospace bn-digit text-end fw-bold" placeholder="0.00" min="1" step="any" required>
                        </div>

                        <!-- Purchase Date -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Purchase Date <span class="text-danger">*</span>
                            </label>

                            <input type="date" name="purchase_date" class="form-control font-monospace bn-digit" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <!-- Warranty -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Warranty Expiry</label>

                            <input type="date" name="warranty_expiry" class="form-control font-monospace bn-digit">
                        </div>

                        <!-- Account -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">
                                Paid From <span class="text-danger">*</span>
                            </label>

                            <select name="bank_account_id" id="assetBankAccountId" class="form-select font-monospace" onchange="handleAssetAccountChange(this)" required>

                                @if(isset($bankAccounts) && $bankAccounts->isNotEmpty())

                                    @foreach($bankAccounts as $acc)
                                        <option value="{{ $acc->id }}" data-type="{{ $acc->account_type }}" {{ $acc->account_type === 'bank' ? 'selected' : '' }}>
                                            {{ $acc->account_name }} (৳{{ number_format((float)$acc->current_balance, 2) }})
                                        </option>
                                    @endforeach

                                @else
                                    <option value="">No Accounts Available</option>
                                @endif

                            </select>
                        </div>

                        <!-- Payment Method -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Payment Method</label>

                            <select name="payment_method" id="assetPaymentMethod" class="form-select font-monospace">
                                <option value="Bank Transfer" selected>Bank Transfer</option>
                                <option value="Cash">Cash Handover</option>
                                <option value="Cheque">Bank Cheque</option>
                                <option value="bKash">bKash</option>
                                <option value="Nagad">Nagad</option>
                            </select>
                        </div>

                        <!-- Asset Code -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Asset Code / Serial No.</label>

                            <input type="text" name="asset_code" class="form-control font-monospace" placeholder="PC-001, SN-987293">
                        </div>

                        <!-- Receipt -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Invoice / Warranty Receipt</label>

                            <input type="file" name="receipt" class="form-control" accept="image/*,application/pdf">
                        </div>

                        <!-- Notes -->
                        <div class="col-12">
                            <label class="form-label">Notes &amp; Location</label>

                            <textarea name="note" rows="2" class="form-control" placeholder="Location, custodian, configuration..."></textarea>
                        </div>

                    </div>

                </div>

                <div class="modal-footer d-flex justify-content-between">

                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary btn-compact fw-bold px-3">
                        <i class="fas fa-check-circle"></i>
                        Register Asset
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script>
function toggleCustomAssetDates(val) {
    const s = document.getElementById('assetStartCol');
    const e = document.getElementById('assetEndCol');
    if (val === 'custom') {
        s.classList.remove('d-none');
        e.classList.remove('d-none');
    } else {
        s.classList.add('d-none');
        e.classList.add('d-none');
    }
}

function handleAssetAccountChange(select) {
    const selectedOption = select.options[select.selectedIndex];
    const type = selectedOption ? selectedOption.getAttribute('data-type') : null;
    const methodSelect = document.getElementById('assetPaymentMethod');
    if (methodSelect && type) {
        if (type === 'cash') methodSelect.value = 'Cash';
        else if (type === 'mobile_banking') methodSelect.value = 'bKash';
        else if (type === 'bank') methodSelect.value = 'Bank Transfer';
    }
}

function deleteAsset(id) {
    if (!confirm('Are you sure you want to permanently delete this fixed asset record? The deducted balance will be refunded to the account.')) {
        return;
    }

    const url = "{{ url('/admin/accounts/assets/delete') }}/" + id;

    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const row = document.getElementById('asset-row-' + id);
            if (row) row.remove();
        } else {
            alert('Failed to delete asset.');
        }
    })
    .catch(err => console.error(err));
}
</script>

@endsection