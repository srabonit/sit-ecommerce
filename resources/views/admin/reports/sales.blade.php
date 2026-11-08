
@extends('layouts.admin')

@section('title', 'Branch Sales Ledger - ' . $branch->name)

@section('admin_content')

<style>
    /* ==========================================
       Branch Sales Ledger
       Kaiadmin Lite v1.2.0 - Classic White
    ========================================== */

    #branchSalesLedgerRoot {
        --bsl-primary: var(--bs-primary, #1572e8);
        --bsl-border: #ebedf2;
        --bsl-heading: #2a2f5b;
        --bsl-text: #495057;
        --bsl-muted: #8d9498;
        --bsl-bg: #f8f9fa;
        --bsl-radius: 5px;

        color: var(--bsl-text);
        font-size: 13px;
        min-width: 0;
        max-width: 100%;
    }

    #branchSalesLedgerRoot,
    #branchSalesLedgerRoot * {
        box-sizing: border-box;
    }

    /* Consistent Radius */

    #branchSalesLedgerRoot .btn,
    #branchSalesLedgerRoot .badge,
    #branchSalesLedgerRoot .form-control,
    #branchSalesLedgerRoot .form-select,
    #branchSalesLedgerRoot .input-group-text,
    #branchSalesLedgerRoot .card,
    #branchSalesLedgerRoot .alert,
    #branchSalesLedgerRoot .table,
    #branchSalesLedgerRoot .dropdown-menu,
    #branchSalesLedgerRoot .page-link {
        border-radius: var(--bsl-radius) !important;
    }

    /* Typography */

    #branchSalesLedgerRoot .bsl-heading {
        color: var(--bsl-heading);
        font-size: 18px;
        font-weight: 700;
        letter-spacing: -.3px;
        line-height: 1.35;
    }

    #branchSalesLedgerRoot .bsl-subtitle {
        color: var(--bsl-muted);
        font-size: 11px;
    }

    #branchSalesLedgerRoot .font-monospace,
    #branchSalesLedgerRoot .bn-digit {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
        font-weight: 700 !important;
    }

    /* Cards */

    #branchSalesLedgerRoot .bsl-card {
        background: #fff;
        border: 1px solid var(--bsl-border) !important;
        border-radius: var(--bsl-radius) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035) !important;
        min-width: 0;
    }

    /* Header */

    #branchSalesLedgerRoot .bsl-header {
        background: #fff;
        border: 1px solid var(--bsl-border);
        border-radius: var(--bsl-radius);
        padding: 13px 15px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035);
        min-width: 0;
    }

    #branchSalesLedgerRoot .bsl-branch-name {
        color: var(--bsl-heading);
        font-size: 14px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    #branchSalesLedgerRoot .bsl-meta-badge {
        font-size: 10px;
        font-weight: 600;
        padding: 5px 8px;
        white-space: normal;
        line-height: 1.35;
    }

    #branchSalesLedgerRoot .bsl-header-actions {
        min-width: 0;
    }

    /* Buttons */

    #branchSalesLedgerRoot .btn-compact {
        min-height: 34px;
        padding: 6px 11px !important;
        font-size: 11px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        gap: 2px;
        box-shadow: none !important;
        transition: background-color .15s ease, border-color .15s ease;
    }

    #branchSalesLedgerRoot .btn-compact:hover {
        filter: brightness(.95);
    }

    #branchSalesLedgerRoot .btn-group-sm > .btn {
        min-height: 29px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: none;
    }

    /* KPI Cards */

    #branchSalesLedgerRoot .kpi-mini-card {
        position: relative;
        height: 100%;
        min-height: 94px;
        padding: 12px 13px;
        background: #fff;
        border: 1px solid var(--bsl-border);
        border-left-width: 3px !important;
        border-radius: var(--bsl-radius);
        transition: box-shadow .15s ease, transform .15s ease;
        overflow: hidden;
    }

    #branchSalesLedgerRoot .kpi-mini-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, .045);
    }

    #branchSalesLedgerRoot .kpi-label {
        display: block;
        margin-bottom: 6px;
        color: var(--bsl-muted);
        font-size: 11px;
        font-weight: 600;
        line-height: 1.4;
    }

    #branchSalesLedgerRoot .kpi-value {
        margin: 0 0 5px;
        color: var(--bsl-heading);
        font-size: clamp(15px, 1.5vw, 19px);
        font-weight: 700;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    #branchSalesLedgerRoot .kpi-footer {
        display: block;
        font-size: 10px;
        line-height: 1.4;
    }

    /* Section Titles */

    #branchSalesLedgerRoot .bsl-section-title {
        color: var(--bsl-heading);
        font-size: 13px;
        font-weight: 700;
        line-height: 1.4;
    }

    /* Filter Panel */

    #branchSalesLedgerRoot .form-label {
        display: block;
        margin-bottom: 6px;
        color: #495057;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.4;
    }

    #branchSalesLedgerRoot .form-control,
    #branchSalesLedgerRoot .form-select {
        min-height: 36px;
        border: 1px solid #ebedf2;
        background-color: #fff;
        color: #495057;
        font-size: 11px;
        box-shadow: none;
        max-width: 100%;
    }

    #branchSalesLedgerRoot .form-control:focus,
    #branchSalesLedgerRoot .form-select:focus {
        border-color: var(--bsl-primary);
        box-shadow: 0 0 0 2px rgba(21, 114, 232, .09);
    }

    #branchSalesLedgerRoot .input-group-text {
        min-height: 36px;
        padding: 6px 10px;
        background: #f8f9fa;
        border-color: #ebedf2;
        font-size: 11px;
    }

    #branchSalesLedgerRoot .input-group {
        min-width: 0;
    }

    #branchSalesLedgerRoot .input-group .form-control {
        min-width: 0;
    }

    #branchSalesLedgerRoot input[type="date"] {
        min-width: 0;
        width: 100%;
    }

    #branchSalesLedgerRoot .bsl-date-range {
        display: flex;
        align-items: center;
        gap: 7px;
        min-width: 0;
    }

    #branchSalesLedgerRoot .bsl-date-range input {
        flex: 1 1 0;
        min-width: 0;
    }

    #branchSalesLedgerRoot .bsl-filter-card {
        min-width: 0;
    }

    /* Dropdown */

    #branchSalesLedgerRoot .dropdown-menu {
        min-width: 180px;
        max-width: min(280px, calc(100vw - 30px));
        padding: 5px;
        border: 1px solid var(--bsl-border) !important;
        box-shadow: 0 5px 20px rgba(0, 0, 0, .08) !important;
        z-index: 1050;
    }

    #branchSalesLedgerRoot .dropdown-item {
        padding: 8px 10px;
        border-radius: 4px;
        font-size: 11px;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    #branchSalesLedgerRoot .dropdown-item.active {
        background: var(--bsl-primary);
        color: #fff;
    }

    /* Sales Table */

    #branchSalesLedgerRoot .bsl-table-wrapper {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8f9fa;
        -webkit-overflow-scrolling: touch;
    }

    #branchSalesLedgerRoot .bsl-sales-table {
        width: 100%;
        min-width: 1250px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 11px;
    }

    #branchSalesLedgerRoot .bsl-sales-table thead th {
        padding: 11px 9px;
        background: #f8f9fa;
        color: #6c757d;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .2px;
        white-space: nowrap;
        vertical-align: middle;
        border-bottom: 1px solid var(--bsl-border);
    }

    #branchSalesLedgerRoot .bsl-sales-table tbody td {
        padding: 10px 9px;
        color: var(--bsl-text);
        font-size: 11px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f1f5;
    }

    #branchSalesLedgerRoot .bsl-sales-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #branchSalesLedgerRoot .bsl-sales-table tbody tr {
        transition: background .15s ease;
    }

    #branchSalesLedgerRoot .bsl-sales-table tbody tr:hover {
        background: #fafbfc;
    }

    #branchSalesLedgerRoot .bsl-sales-table .text-nowrap {
        white-space: nowrap;
    }

    #branchSalesLedgerRoot .bsl-sales-table .badge {
        display: inline-block;
        padding: 5px 7px;
        font-size: 10px;
        font-weight: 600;
        line-height: 1.25;
    }

    #branchSalesLedgerRoot .bsl-sales-table .btn-group {
        white-space: nowrap;
    }

    /* Soft Status Styling */

    #branchSalesLedgerRoot .bsl-sales-table .bg-success {
        background-color: #e8f7ee !important;
        color: #198754 !important;
        border: 1px solid #ccebd8;
    }

    #branchSalesLedgerRoot .bsl-sales-table .bg-danger {
        background-color: #fdecec !important;
        color: #dc3545 !important;
        border: 1px solid #f7d1d4;
    }

    #branchSalesLedgerRoot .bsl-sales-table .bg-warning {
        background-color: #fff5df !important;
        color: #946200 !important;
        border: 1px solid #f5e3b8;
    }

    #branchSalesLedgerRoot .bsl-sales-table .bg-primary {
        background-color: #eaf2ff !important;
        color: #1572e8 !important;
        border: 1px solid #d4e4ff;
    }

    #branchSalesLedgerRoot .bsl-sales-table .bg-info {
        background-color: #e6f7fb !important;
        color: #087990 !important;
        border: 1px solid #c9edf4;
    }

    /* Pagination */

    #branchSalesLedgerRoot .bsl-pagination {
        border-top: 1px solid var(--bsl-border);
        min-width: 0;
    }

    #branchSalesLedgerRoot .bsl-pagination nav {
        display: flex;
        justify-content: flex-end;
        max-width: 100%;
    }

    #branchSalesLedgerRoot .bsl-pagination .pagination {
        margin-bottom: 0;
        flex-wrap: wrap;
        gap: 3px;
    }

    #branchSalesLedgerRoot .bsl-pagination .page-link {
        min-width: 30px;
        min-height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 9px;
        border-color: var(--bsl-border);
        color: #495057;
        font-size: 11px;
        box-shadow: none;
    }

    #branchSalesLedgerRoot .bsl-pagination .active > .page-link {
        background: var(--bsl-primary);
        border-color: var(--bsl-primary);
        color: #fff;
    }

    /* Alerts */

    #branchSalesLedgerRoot .alert {
        font-size: 12px;
    }

    /* Responsive */

    @media (max-width: 991.98px) {
        #branchSalesLedgerRoot .bsl-header {
            align-items: flex-start !important;
        }

        #branchSalesLedgerRoot .bsl-header-actions {
            justify-content: flex-start;
            flex-wrap: wrap;
        }

        #branchSalesLedgerRoot .kpi-mini-card {
            min-height: 90px;
        }
    }

    @media (max-width: 767.98px) {
        #branchSalesLedgerRoot {
            padding-top: 10px !important;
        }

        #branchSalesLedgerRoot .bsl-heading {
            font-size: 16px;
        }

        #branchSalesLedgerRoot .bsl-header {
            padding: 11px;
        }

        #branchSalesLedgerRoot .bsl-header-actions {
            width: 100%;
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 7px !important;
        }

        #branchSalesLedgerRoot .bsl-header-actions > a,
        #branchSalesLedgerRoot .bsl-header-actions > .dropdown {
            width: 100%;
            min-width: 0;
        }

        #branchSalesLedgerRoot .bsl-header-actions .btn {
            width: 100%;
        }

        #branchSalesLedgerRoot .bsl-header-actions .dropdown > button {
            width: 100%;
        }

        #branchSalesLedgerRoot .kpi-mini-card {
            padding: 10px;
            min-height: 86px;
        }

        #branchSalesLedgerRoot .kpi-label {
            font-size: 10px;
        }

        #branchSalesLedgerRoot .kpi-footer {
            font-size: 9px;
        }

        #branchSalesLedgerRoot .bsl-filter-card {
            padding: 12px !important;
        }

        #branchSalesLedgerRoot .bsl-sales-table {
            min-width: 1250px;
        }

        #branchSalesLedgerRoot .bsl-pagination {
            justify-content: center !important;
        }

        #branchSalesLedgerRoot .bsl-pagination nav {
            justify-content: center;
            width: 100%;
        }

        #branchSalesLedgerRoot .bsl-pagination > small {
            text-align: center;
            width: 100%;
        }
    }

    @media (max-width: 575.98px) {
        #branchSalesLedgerRoot .bsl-date-range {
            gap: 6px;
        }

        #branchSalesLedgerRoot .bsl-header-actions .btn {
            font-size: 10.5px !important;
            padding-left: 6px !important;
            padding-right: 6px !important;
        }

        #branchSalesLedgerRoot .bsl-filter-card .btn-compact {
            min-width: 0;
        }
    }

    @media (max-width: 374.98px) {
        #branchSalesLedgerRoot .bsl-heading {
            font-size: 15px;
        }

        #branchSalesLedgerRoot .bsl-branch-name {
            font-size: 12px;
        }

        #branchSalesLedgerRoot .bsl-header-actions .btn {
            font-size: 10px !important;
            padding: 6px 5px !important;
        }

        #branchSalesLedgerRoot .kpi-value {
            font-size: 14px;
        }

        #branchSalesLedgerRoot .bsl-date-range {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-3" id="branchSalesLedgerRoot">

    <!-- Page Header -->
    <div class="bsl-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">

        <div class="d-flex flex-column gap-2 min-w-0">

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-primary text-white fw-bold bsl-meta-badge">
                    <i class="fas fa-file-invoice-dollar me-1"></i>
                    Sales Ledger
                </span>

                <span class="bsl-branch-name">
                    {{ $branch->name }}
                </span>
            </div>

            <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="badge bg-light text-dark border font-monospace bsl-meta-badge">
                    Code: {{ $branch->code ?? $branch->branch_code }}
                </span>

                <span class="badge bg-light text-secondary border font-monospace bsl-meta-badge">
                    <i class="far fa-calendar-alt me-1"></i>
                    {{ date('d M Y', strtotime($start_date ?? now())) }} &ndash; {{ date('d M Y', strtotime($end_date ?? now())) }}
                </span>
            </div>

        </div>

        <div class="bsl-header-actions d-flex align-items-center flex-wrap gap-2">

            @if(isset($selectableBranches) && $selectableBranches->count() > 1)
                <div class="dropdown d-inline-block">
                    <button class="btn btn-outline-primary btn-compact dropdown-toggle"
                            type="button"
                            data-bs-toggle="dropdown">
                        <i class="fas fa-store-alt me-1"></i>
                        Switch Branch
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end">
                        @foreach($selectableBranches as $sb)
                            <li>
                                <a class="dropdown-item py-2 {{ $sb->id == $branch->id ? 'active fw-bold' : '' }}"
                                   href="{{ route('admin.branch_report.sales', array_merge(request()->except('branch_id'), ['branch_id' => $sb->id])) }}">
                                    {{ $sb->name }} ({{ $sb->code ?? $sb->branch_code }})
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- CSV Export -->
            <a href="{{ route('admin.branch_report.export_sales_csv', array_merge(request()->all(), ['branch_id' => $branch->id])) }}"
               class="btn btn-success text-white btn-compact">
                <i class="fas fa-file-csv me-1"></i>
                Export CSV
            </a>

            <!-- Analytics -->
            <a href="{{ route('admin.branch_report.dashboard', ['branch_id' => $branch->id]) }}"
               class="btn btn-outline-secondary btn-compact">
                <i class="fas fa-chart-pie me-1"></i>
                Analytics
            </a>

            <!-- POS Terminal -->
            <a href="{{ route('admin.pos.terminal', $branch->id) }}"
               class="btn btn-warning text-white btn-compact">
                <i class="fas fa-desktop me-1"></i>
                POS Terminal
            </a>

        </div>
    </div>

    <!-- KPI Summary -->
    <div class="row g-2 mb-3">

        <!-- Total Sales -->
        <div class="col-6 col-md-3">
            <div class="kpi-mini-card border-start border-primary border-3">
                <span class="kpi-label">Total Sales</span>

                <h5 class="kpi-value font-monospace bn-digit">
                    ৳{{ number_format($total_sales ?? 0, 2) }}
                </h5>

                <small class="kpi-footer text-primary fw-semibold">
                    {{ number_format($total_orders ?? 0) }} Orders
                </small>
            </div>
        </div>

        <!-- Total Paid -->
        <div class="col-6 col-md-3">
            <div class="kpi-mini-card border-start border-success border-3">
                <span class="kpi-label">Total Paid</span>

                <h5 class="kpi-value text-success font-monospace bn-digit">
                    ৳{{ number_format($total_paid ?? 0, 2) }}
                </h5>

                <small class="kpi-footer text-muted">
                    Cash &amp; Digital
                </small>
            </div>
        </div>

        <!-- Customer Due -->
        <div class="col-6 col-md-3">
            <div class="kpi-mini-card border-start border-danger border-3">
                <span class="kpi-label">Customer Due</span>

                <h5 class="kpi-value text-danger font-monospace bn-digit">
                    ৳{{ number_format($total_due ?? 0, 2) }}
                </h5>

                <small class="kpi-footer text-danger fw-semibold">
                    Receivables
                </small>
            </div>
        </div>

        <!-- Average Order Value -->
        <div class="col-6 col-md-3">
            <div class="kpi-mini-card border-start border-warning border-3">
                <span class="kpi-label">Average Order Value</span>

                @php
                    $totOrders = (int)($total_orders ?? 0);
                    $totSales = (float)($total_sales ?? 0);
                    $aov = $totOrders > 0 ? round($totSales / $totOrders, 2) : 0.00;
                @endphp

                <h5 class="kpi-value font-monospace bn-digit">
                    ৳{{ number_format($aov, 2) }}
                </h5>

                <small class="kpi-footer text-muted">
                    Per Order
                </small>
            </div>
        </div>

    </div>

    <!-- Advanced Filters -->
    <div class="card bsl-card bg-white mb-3">

        <div class="card-header bg-white border-bottom py-3 px-3">
            <h6 class="bsl-section-title mb-0">
                <i class="fas fa-filter text-primary me-2"></i>
                Sales Filters
            </h6>
        </div>

        <div class="card-body p-3 bsl-filter-card">

            <form method="GET"
                  action="{{ route('admin.branch_report.sales') }}"
                  id="salesFilterForm">

                <input type="hidden" name="branch_id" value="{{ $branch->id }}">

                <div class="row g-3 align-items-end">

                    <!-- Search -->
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label">
                            Search Invoice / Customer
                        </label>

                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>

                            <input type="text"
                                   name="search"
                                   class="form-control border-start-0"
                                   placeholder="Invoice, phone or name..."
                                   value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>

                    <!-- Timeline Preset -->
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label">
                            Period
                        </label>

                        <select name="period"
                                id="periodSelect"
                                class="form-select form-select-sm"
                                onchange="toggleCustomDates(this.value)">

                            <option value="today" {{ ($filters['period'] ?? '') === 'today' ? 'selected' : '' }}>
                                Today
                            </option>

                            <option value="yesterday" {{ ($filters['period'] ?? '') === 'yesterday' ? 'selected' : '' }}>
                                Yesterday
                            </option>

                            <option value="this_week" {{ ($filters['period'] ?? '') === 'this_week' ? 'selected' : '' }}>
                                This Week
                            </option>

                            <option value="this_month" {{ ($filters['period'] ?? '') === 'this_month' ? 'selected' : '' }}>
                                This Month
                            </option>

                            <option value="last_month" {{ ($filters['period'] ?? '') === 'last_month' ? 'selected' : '' }}>
                                Last Month
                            </option>

                            <option value="this_year" {{ ($filters['period'] ?? '') === 'this_year' ? 'selected' : '' }}>
                                This Year
                            </option>

                            <option value="custom" {{ ($filters['period'] ?? '') === 'custom' ? 'selected' : '' }}>
                                Custom Range
                            </option>

                        </select>
                    </div>

                    <!-- Custom Date Range -->
                    <div class="col-12 col-md-6 col-lg-3"
                         id="customDateCol"
                         style="{{ ($filters['period'] ?? '') === 'custom' ? '' : 'display: none;' }}">

                        <label class="form-label">
                            Date Range
                        </label>

                        <div class="bsl-date-range">
                            <input type="date"
                                   name="start_date"
                                   class="form-control form-control-sm font-monospace"
                                   value="{{ $filters['start_date'] ?? date('Y-m-01') }}">

                            <input type="date"
                                   name="end_date"
                                   class="form-control form-control-sm font-monospace"
                                   value="{{ $filters['end_date'] ?? date('Y-m-d') }}">
                        </div>
                    </div>

                    <!-- Sales Representative -->
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label">
                            Sales Representative
                        </label>

                        <select name="sales_person_id"
                                class="form-select form-select-sm">

                            <option value="">All Personnel</option>

                            @foreach($salesPersons ?? [] as $sp)
                                <option value="{{ $sp->id }}" {{ ($filters['sales_person_id'] ?? '') == $sp->id ? 'selected' : '' }}>
                                    {{ $sp->name }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <!-- Payment Method -->
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label">
                            Payment Method
                        </label>

                        <select name="payment_method"
                                class="form-select form-select-sm">

                            <option value="all" {{ ($filters['payment_method'] ?? 'all') === 'all' ? 'selected' : '' }}>
                                All Methods
                            </option>

                            <option value="cash" {{ ($filters['payment_method'] ?? '') === 'cash' ? 'selected' : '' }}>
                                Cash
                            </option>

                            <option value="bkash" {{ ($filters['payment_method'] ?? '') === 'bkash' ? 'selected' : '' }}>
                                bKash
                            </option>

                            <option value="nagad" {{ ($filters['payment_method'] ?? '') === 'nagad' ? 'selected' : '' }}>
                                Nagad
                            </option>

                            <option value="rocket" {{ ($filters['payment_method'] ?? '') === 'rocket' ? 'selected' : '' }}>
                                Rocket
                            </option>

                            <option value="card" {{ ($filters['payment_method'] ?? '') === 'card' ? 'selected' : '' }}>
                                Card
                            </option>

                            <option value="bank_transfer" {{ ($filters['payment_method'] ?? '') === 'bank_transfer' ? 'selected' : '' }}>
                                Bank Transfer
                            </option>

                        </select>
                    </div>

                    <!-- Channel -->
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label">
                            Channel
                        </label>

                        <select name="order_type"
                                class="form-select form-select-sm">

                            <option value="all" {{ ($filters['order_type'] ?? 'all') === 'all' ? 'selected' : '' }}>
                                All Channels
                            </option>

                            <option value="pos" {{ ($filters['order_type'] ?? '') === 'pos' ? 'selected' : '' }}>
                                In-Store POS
                            </option>

                            <option value="online" {{ ($filters['order_type'] ?? '') === 'online' ? 'selected' : '' }}>
                                Online Delivery
                            </option>

                        </select>
                    </div>

                    <!-- Filter Actions -->
                    <div class="col-12 col-md-6 col-lg-2">
                        <label class="form-label d-none d-lg-block">
                            &nbsp;
                        </label>

                        <div class="d-flex gap-2">

                            <button type="submit"
                                    class="btn btn-primary btn-sm btn-compact flex-grow-1">
                                <i class="fas fa-filter me-1"></i>
                                Apply
                            </button>

                            <a href="{{ route('admin.branch_report.sales', ['branch_id' => $branch->id]) }}"
                               class="btn btn-light border btn-sm btn-compact px-3"
                               title="Reset Filters">
                                <i class="fas fa-undo"></i>
                            </a>

                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- Sales Records -->
    <div class="card bsl-card bg-white overflow-hidden">

        <!-- Table Header -->
        <div class="card-header bg-white border-bottom py-3 px-3">
            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">

                <h6 class="bsl-section-title mb-0">
                    <i class="fas fa-list-alt text-primary me-2"></i>
                    Sales Records
                </h6>

                <span class="badge bg-light text-secondary border px-2 py-1">
                    {{ number_format($orders->total()) }} Records
                </span>

            </div>
        </div>

        <!-- Sales Table -->
        <div class="card-body p-0">

            <div class="bsl-table-wrapper table-responsive">

                <table class="table table-hover table-sm align-middle bsl-sales-table">

                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Date &amp; Time</th>
                            <th>Customer Info</th>
                            <th>Sales Staff</th>
                            <th class="text-center">Channel</th>
                            <th class="text-center">Items</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Due</th>
                            <th class="text-center">Payment</th>
                            <th class="text-center">Status</th>
                            <th class="text-end" style="width: 100px;">Action</th>
                        </tr>
                    </thead>

                    <tbody id="salesTableBody">
                        @forelse($orders as $order)
                            <tr>
                                <td class="text-nowrap">
                                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.order.view') ? route('admin.order.view', $order->id) : url('admin/orders/' . $order->id) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                                        {{ $order->invoice_no ?? ('#' . $order->id) }}
                                    </a>
                                </td>
                                <td class="font-monospace text-muted text-nowrap" style="font-size: 10.5px;">
                                    {{ $order->created_at ? $order->created_at->format('d M Y, h:i A') : '—' }}
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $order->user?->name ?? 'Walk-in Customer' }}</div>
                                    <small class="text-muted font-monospace bn-digit">{{ $order->user?->phone ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">
                                        {{ $order->salesPerson?->name ?? 'Cashier' }}
                                    </span>
                                </td>
                                <td class="text-center text-nowrap">
                                    <span class="badge {{ ($order->order_type ?? '') === 'pos' ? 'bg-warning text-dark' : 'bg-info text-white' }}">
                                        {{ strtoupper($order->order_type ?? 'ONLINE') }}
                                    </span>
                                </td>
                                <td class="text-center font-monospace bn-digit fw-bold">
                                    {{ $order->items_count ?? ($order->items ? $order->items->count() : 1) }}
                                </td>
                                <td class="text-end font-monospace bn-digit text-muted text-nowrap">
                                    ৳{{ number_format($order->subtotal ?? $order->grand_total, 2) }}
                                </td>
                                <td class="text-end font-monospace bn-digit text-danger text-nowrap">
                                    {{ $order->discount_amount > 0 ? '-৳' . number_format($order->discount_amount, 2) : '৳0.00' }}
                                </td>
                                <td class="text-end font-monospace bn-digit fw-bold text-dark text-nowrap">
                                    ৳{{ number_format($order->grand_total, 2) }}
                                </td>
                                <td class="text-end font-monospace bn-digit fw-bold text-success text-nowrap">
                                    ৳{{ number_format($order->paid_amount, 2) }}
                                </td>
                                <td class="text-end font-monospace bn-digit fw-bold text-danger text-nowrap">
                                    ৳{{ number_format($order->due_amount, 2) }}
                                </td>
                                <td class="text-center text-nowrap">
                                    <span class="badge bg-light text-dark border font-monospace">
                                        {{ strtoupper($order->payment_method ?? 'N/A') }}
                                    </span>
                                </td>
                                <td class="text-center text-nowrap">
                                    @php
                                        $st = strtolower($order->order_status ?? 'completed');
                                        $stBadge = 'bg-primary';
                                        if (in_array($st, ['delivered', 'completed'])) $stBadge = 'bg-success';
                                        elseif (in_array($st, ['cancelled', 'failed', 'returned'])) $stBadge = 'bg-danger';
                                        elseif ($st === 'pending') $stBadge = 'bg-warning text-dark';
                                    @endphp
                                    <span class="badge {{ $stBadge }}">
                                        {{ ucfirst($order->order_status ?? 'completed') }}
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ \Illuminate\Support\Facades\Route::has('admin.pos.print.thermal') ? route('admin.pos.print.thermal', $order->id) : (\Illuminate\Support\Facades\Route::has('admin.pos.print_thermal') ? route('admin.pos.print_thermal', $order->id) : url('admin/pos/print-thermal/' . $order->id)) }}" target="_blank" class="btn btn-outline-dark px-2 py-1" title="Thermal Receipt" style="font-size: 10px;">
                                            <i class="fas fa-receipt"></i>
                                        </a>
                                        <a href="{{ \Illuminate\Support\Facades\Route::has('admin.pos.print.a5') ? route('admin.pos.print.a5', $order->id) : (\Illuminate\Support\Facades\Route::has('admin.pos.print_a5') ? route('admin.pos.print_a5', $order->id) : url('admin/pos/print-a5/' . $order->id)) }}" target="_blank" class="btn btn-outline-primary px-2 py-1" title="A5 Memo" style="font-size: 10px;">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center py-4 text-muted">
                                    No sales records found matching your filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>

            </div>
        </div>

        <!-- Pagination -->
        <div class="bsl-pagination d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-3">

            <small class="text-muted" style="font-size: 11px;">
                Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} sales records
            </small>

            <div>
                {{ $orders->links() }}
            </div>

        </div>

    </div>

</div>

<script>
function toggleCustomDates(val) {
    const col = document.getElementById('customDateCol');
    if (val === 'custom') {
        col.style.display = 'block';
    } else {
        col.style.display = 'none';
        document.getElementById('salesFilterForm').submit();
    }
}
</script>

@endsection
