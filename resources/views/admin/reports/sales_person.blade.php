
@extends('layouts.admin')

@section('title', 'Sales Person Performance - ' . ($branch->name ?? 'Branch'))

@section('admin_content')
<style>
    /* ==========================================
       Sales Person Performance
       Kaiadmin Lite v1.2.0 - Classic White
    ========================================== */

    #salesPersonReportRoot {
        --sp-primary: var(--bs-primary, #1572e8);
        --sp-success: #31ce36;
        --sp-warning: #ffad46;
        --sp-danger: #f25961;
        --sp-info: #48abf7;
        --sp-text: #2a2f5b;
        --sp-muted: #8d9498;
        --sp-border: #ebedf2;
        --sp-light: #f8f9fa;
        --sp-radius: 5px;

        color: var(--sp-text);
        font-size: 13px;
        min-width: 0;
        max-width: 100%;
    }

    #salesPersonReportRoot,
    #salesPersonReportRoot * {
        box-sizing: border-box;
    }

    /* Consistent Radius */

    #salesPersonReportRoot .card,
    #salesPersonReportRoot .btn,
    #salesPersonReportRoot .badge,
    #salesPersonReportRoot .form-control,
    #salesPersonReportRoot .form-select,
    #salesPersonReportRoot .input-group-text,
    #salesPersonReportRoot .table,
    #salesPersonReportRoot .page-link {
        border-radius: var(--sp-radius) !important;
    }

    /* Cards */

    #salesPersonReportRoot .card {
        background: #fff;
        border: 1px solid var(--sp-border);
        border-radius: var(--sp-radius);
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035);
        min-width: 0;
    }

    /* Typography */

    #salesPersonReportRoot .font-monospace,
    #salesPersonReportRoot .bn-digit {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }

    /* Buttons */

    #salesPersonReportRoot .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-height: 34px;
        padding: 6px 11px;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.3;
        white-space: nowrap;
        box-shadow: none;
        transition: background-color .15s ease, border-color .15s ease;
    }

    #salesPersonReportRoot .btn-compact:hover {
        filter: brightness(.96);
    }

    /* Page Header */

    #salesPersonReportRoot .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding: 13px 15px;
        margin-bottom: 14px;
        background: #fff;
        border: 1px solid var(--sp-border);
        border-radius: var(--sp-radius);
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035);
        min-width: 0;
    }

    #salesPersonReportRoot .page-header-info,
    #salesPersonReportRoot .page-header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        min-width: 0;
    }

    #salesPersonReportRoot .page-title-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        background: #fff5e6;
        color: #b7791f;
        border: 1px solid #ffe2b8;
        border-radius: var(--sp-radius);
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    #salesPersonReportRoot .branch-name {
        color: var(--sp-text);
        font-size: 14px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    #salesPersonReportRoot .meta-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 8px;
        background: #f8f9fa;
        color: #6c757d;
        border: 1px solid var(--sp-border);
        border-radius: var(--sp-radius);
        font-size: 10px;
        white-space: normal;
        line-height: 1.35;
    }

    /* KPI Cards */

    #salesPersonReportRoot .kpi-stat-card {
        height: 100%;
        min-height: 104px;
        padding: 13px 14px;
        background: #fff;
        border: 1px solid var(--sp-border);
        border-left-width: 3px !important;
        border-radius: var(--sp-radius);
        transition: box-shadow .15s ease, transform .15s ease;
        overflow: hidden;
    }

    #salesPersonReportRoot .kpi-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, .06);
    }

    #salesPersonReportRoot .kpi-label {
        display: block;
        margin-bottom: 7px;
        color: var(--sp-muted);
        font-size: 10.5px;
        font-weight: 600;
        line-height: 1.4;
    }

    #salesPersonReportRoot .kpi-value {
        margin: 0;
        color: var(--sp-text);
        font-size: clamp(15px, 1.5vw, 18px);
        font-weight: 700;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    #salesPersonReportRoot .kpi-note {
        display: block;
        margin-top: 5px;
        font-size: 10px;
        font-weight: 500;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    /* Section Card */

    #salesPersonReportRoot .section-card {
        padding: 14px;
        margin-bottom: 14px;
        background: #fff;
    }

    /* Timeline Filter */

    #salesPersonReportRoot .filter-heading {
        color: #6c757d;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    #salesPersonReportRoot .period-tabs {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px;
        min-width: 0;
    }

    #salesPersonReportRoot .period-tab-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 31px;
        padding: 5px 10px;
        color: #596579;
        background: #fff;
        border: 1px solid #e2e6ea;
        border-radius: var(--sp-radius);
        font-size: 10.5px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        transition: all .15s ease;
    }

    #salesPersonReportRoot .period-tab-btn:hover {
        color: var(--sp-primary);
        border-color: var(--sp-primary);
        background: #f5f9ff;
    }

    #salesPersonReportRoot .period-tab-btn.active {
        color: #fff;
        background: var(--sp-primary);
        border-color: var(--sp-primary);
    }

    #salesPersonReportRoot .custom-date-form {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 7px;
        min-width: 0;
    }

    #salesPersonReportRoot .custom-date-form .form-control {
        width: 132px;
        min-width: 0;
        min-height: 34px;
        border: 1px solid var(--sp-border);
        background: #fff;
        color: #495057;
        font-size: 11px;
        box-shadow: none;
    }

    #salesPersonReportRoot .custom-date-form .form-control:focus {
        border-color: var(--sp-primary);
        box-shadow: 0 0 0 2px rgba(21, 114, 232, .09);
    }

    /* Performance Table */

    #salesPersonReportRoot .sp-table-wrapper {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        border-radius: var(--sp-radius);
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8f9fa;
    }

    #salesPersonReportRoot .table {
        width: 100%;
        min-width: 1120px;
        margin-bottom: 0;
        color: #4b5563;
        vertical-align: middle;
        border-collapse: separate;
        border-spacing: 0;
    }

    #salesPersonReportRoot .table thead th {
        padding: 11px 10px;
        color: #596579;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        font-size: 10.5px;
        font-weight: 700;
        white-space: nowrap;
        vertical-align: middle;
    }

    #salesPersonReportRoot .table tbody td {
        padding: 10px;
        border-bottom: 1px solid #f0f1f3;
        color: #4b5563;
        font-size: 11px;
        vertical-align: middle;
        white-space: nowrap;
    }

    #salesPersonReportRoot .table tbody tr:last-child td {
        border-bottom: 0;
    }

    #salesPersonReportRoot .table tbody tr {
        transition: background-color .15s ease;
    }

    #salesPersonReportRoot .table tbody tr:hover {
        background: #f9fbff;
    }

    /* Staff Information */

    #salesPersonReportRoot .staff-name {
        color: var(--sp-text);
        font-size: 11.5px;
        font-weight: 700;
    }

    #salesPersonReportRoot .staff-meta {
        display: inline-block;
        margin-top: 4px;
        padding: 3px 6px;
        color: #7c8495;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        font-size: 9px;
    }

    /* Rank Badges */

    #salesPersonReportRoot .rank-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 31px;
        min-height: 25px;
        padding: 4px 7px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 700;
    }

    #salesPersonReportRoot .rank-badge.bg-warning {
        background: #fff5df !important;
        color: #946200 !important;
        border: 1px solid #f5e3b8;
    }

    #salesPersonReportRoot .rank-badge.bg-secondary {
        background: #eef0f3 !important;
        color: #596579 !important;
        border: 1px solid #dfe3e8;
    }

    #salesPersonReportRoot .rank-badge.bg-light {
        background: #f8f9fa !important;
        color: #596579 !important;
        border: 1px solid #e5e7eb;
    }

    /* Table Badges */

    #salesPersonReportRoot .table .badge {
        padding: 5px 7px;
        font-size: 10px;
        font-weight: 600;
        line-height: 1.25;
    }

    /* Empty State */

    #salesPersonReportRoot .empty-state {
        padding: 38px 15px !important;
        color: var(--sp-muted);
        white-space: normal !important;
        text-align: center;
        line-height: 1.6;
    }

    #salesPersonReportRoot .empty-state i {
        display: block;
        margin-bottom: 12px;
        color: #c4c9d0;
    }

    /* Responsive */

    @media (max-width: 991.98px) {
        #salesPersonReportRoot .page-header {
            align-items: flex-start;
        }

        #salesPersonReportRoot .page-header-actions {
            justify-content: flex-start;
        }

        #salesPersonReportRoot .kpi-stat-card {
            min-height: 100px;
        }

        #salesPersonReportRoot .timeline-filter-content {
            align-items: flex-start !important;
        }
    }

    @media (max-width: 767.98px) {
        #salesPersonReportRoot .page-header {
            align-items: flex-start;
            padding: 11px;
        }

        #salesPersonReportRoot .page-header-info,
        #salesPersonReportRoot .page-header-actions {
            width: 100%;
        }

        #salesPersonReportRoot .page-header-actions {
            padding-top: 10px;
            border-top: 1px solid var(--sp-border);
        }

        #salesPersonReportRoot .page-header-actions .btn {
            flex: 1 1 auto;
        }

        #salesPersonReportRoot .kpi-stat-card {
            min-height: 96px;
            padding: 11px;
        }

        #salesPersonReportRoot .kpi-label {
            font-size: 10px;
        }

        #salesPersonReportRoot .kpi-value {
            font-size: 15px;
        }

        #salesPersonReportRoot .section-card {
            padding: 11px;
        }

        #salesPersonReportRoot .timeline-filter-content {
            align-items: flex-start !important;
        }

        #salesPersonReportRoot .custom-date-form {
            width: 100%;
        }

        #salesPersonReportRoot .custom-date-form .form-control {
            flex: 1 1 110px;
            width: auto;
        }

        #salesPersonReportRoot .sp-table-wrapper {
            border-radius: var(--sp-radius);
        }
    }

    @media (max-width: 575.98px) {
        #salesPersonReportRoot .page-header-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 7px;
        }

        #salesPersonReportRoot .page-header-actions > a,
        #salesPersonReportRoot .page-header-actions > .dropdown {
            width: 100%;
            min-width: 0;
        }

        #salesPersonReportRoot .page-header-actions .btn {
            width: 100%;
            min-width: 0;
            padding-right: 6px;
            padding-left: 6px;
            font-size: 10.5px;
        }

        #salesPersonReportRoot .period-tabs {
            width: 100%;
        }

        #salesPersonReportRoot .period-tab-btn {
            flex: 1 1 auto;
        }

        #salesPersonReportRoot .custom-date-form .btn {
            flex: 0 0 auto;
        }
    }

    @media (max-width: 420px) {
        #salesPersonReportRoot .meta-badge {
            font-size: 9.5px;
        }

        #salesPersonReportRoot .period-tab-btn {
            padding-right: 8px;
            padding-left: 8px;
        }

        #salesPersonReportRoot .kpi-value {
            font-size: 14px;
        }

        #salesPersonReportRoot .kpi-note {
            font-size: 9px;
        }
    }

    @media (max-width: 374.98px) {
        #salesPersonReportRoot .branch-name {
            font-size: 12px;
        }

        #salesPersonReportRoot .page-title-badge {
            font-size: 10px;
            padding: 5px 7px;
        }

        #salesPersonReportRoot .custom-date-form {
            gap: 5px;
        }

        #salesPersonReportRoot .custom-date-form .form-control {
            flex-basis: 100%;
            width: 100%;
        }

        #salesPersonReportRoot .custom-date-form > span {
            display: none;
        }

        #salesPersonReportRoot .custom-date-form .btn {
            width: 100%;
        }
    }
</style>

<div class="container-fluid py-2 px-2 px-md-3" id="salesPersonReportRoot">

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-info">
            <span class="page-title-badge">
                <i class="fas fa-user-check"></i>
                Staff Performance
            </span>

            <span class="branch-name">{{ $branch->name ?? 'Branch' }}</span>

            <span class="meta-badge font-monospace">
                Code: {{ $branch->code ?? ($branch->branch_code ?? 'BR-' . ($branch->id ?? '')) }}
            </span>

            <span class="meta-badge font-monospace">
                <i class="fas fa-users"></i>
                Active Staff: {{ is_countable($performance) ? count($performance) : 0 }}
            </span>
        </div>

        <div class="page-header-actions">
            @if(isset($selectableBranches) && $selectableBranches->count() > 1)
                <div class="dropdown d-inline-block">
                    <button class="btn btn-outline-primary btn-compact dropdown-toggle"
                            type="button"
                            data-bs-toggle="dropdown">
                        <i class="fas fa-store-alt"></i>
                        Switch Branch
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="font-size: 11px;">
                        @foreach($selectableBranches as $sb)
                            <li>
                                <a class="dropdown-item py-2 {{ $sb->id == $branch->id ? 'active fw-bold' : '' }}"
                                   href="{{ route('admin.branch_report.sales_person', ['branch_id' => $sb->id, 'period' => $filters['period'] ?? 'this_month']) }}">
                                    {{ $sb->name }} ({{ $sb->code ?? $sb->branch_code }})
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <a href="{{ route('admin.branch_report.sales', ['branch_id' => $branch->id]) }}"
               class="btn btn-outline-secondary btn-compact">
                <i class="fas fa-file-invoice-dollar"></i>
                Sales Ledger
            </a>

            <a href="{{ route('admin.branch_report.dashboard', ['branch_id' => $branch->id]) }}"
               class="btn btn-light border btn-compact">
                <i class="fas fa-chart-pie"></i>
                Analytics
            </a>

            <a href="{{ route('admin.pos.terminal', $branch->id) }}"
               class="btn btn-warning text-white btn-compact">
                <i class="fas fa-desktop"></i>
                POS Terminal
            </a>
        </div>
    </div>

    @php
        $perfColl = collect($performance);
        $topStaff = $perfColl->first();
        $totalOrdersSum = $perfColl->sum('total_orders');
        $totalSalesSum = $perfColl->sum('total_sales');
        $totalProfitSum = $perfColl->sum('gross_profit');
    @endphp

    <!-- KPI Summary -->
    <div class="row g-2 mb-3">

        <!-- Top Performing Representative -->
        <div class="col-6 col-md-3">
            <div class="kpi-stat-card border-start border-warning">
                <span class="kpi-label">Top Performing Representative</span>

                <h5 class="kpi-value text-truncate"
                    title="{{ $topStaff->sales_person_name ?? 'N/A' }}">
                    {{ $topStaff->sales_person_name ?? 'No Sales Logged' }}
                </h5>

                <small class="kpi-note text-warning font-monospace bn-digit">
                    {{ $topStaff ? '৳' . number_format($topStaff->total_sales ?? 0, 0) . ' (' . ($topStaff->total_orders ?? 0) . ' orders)' : 'None' }}
                </small>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="col-6 col-md-3">
            <div class="kpi-stat-card border-start border-primary">
                <span class="kpi-label">Orders Attributed to Staff</span>

                <h5 class="kpi-value font-monospace bn-digit">
                    {{ number_format($totalOrdersSum ?? 0) }}
                </h5>

                <small class="kpi-note text-primary">
                    Closed by store personnel
                </small>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="col-6 col-md-3">
            <div class="kpi-stat-card border-start border-success">
                <span class="kpi-label">Staff Generated Revenue</span>

                <h5 class="kpi-value text-success font-monospace bn-digit">
                    ৳{{ number_format($totalSalesSum ?? 0, 2) }}
                </h5>

                <small class="kpi-note text-muted">
                    Gross billed turnover
                </small>
            </div>
        </div>

        <!-- Gross Profit -->
        <div class="col-6 col-md-3">
            <div class="kpi-stat-card border-start border-info">
                <span class="kpi-label">Total Gross Margin Contributed</span>

                <h5 class="kpi-value font-monospace bn-digit">
                    ৳{{ number_format($totalProfitSum ?? 0, 2) }}
                </h5>

                <small class="kpi-note text-info">
                    Sales less cost of goods (COGS)
                </small>
            </div>
        </div>
    </div>

    <!-- Period Filters -->
    <div class="card section-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 timeline-filter-content">

            <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="filter-heading">
                    <i class="fas fa-calendar-alt me-1"></i>
                    Timeline:
                </span>

                @php
                    $curPeriod = $filters['period'] ?? 'this_month';
                @endphp

                <div class="period-tabs">
                    @foreach([
                        'today'      => 'Today',
                        'yesterday'  => 'Yesterday',
                        'this_week'  => 'This Week',
                        'this_month' => 'This Month',
                        'last_month' => 'Last Month',
                        'this_year'  => 'This Year'
                    ] as $pKey => $pLabel)
                        <a href="{{ route('admin.branch_report.sales_person', ['branch_id' => $branch->id, 'period' => $pKey]) }}"
                           class="period-tab-btn {{ $curPeriod === $pKey ? 'active' : '' }}">
                            {{ $pLabel }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Custom Date Range -->
            <form method="GET"
                  action="{{ route('admin.branch_report.sales_person') }}"
                  class="custom-date-form">

                <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                <input type="hidden" name="period" value="custom">

                <input type="date"
                       name="start_date"
                       class="form-control form-control-sm font-monospace"
                       value="{{ $filters['start_date'] ?? date('Y-m-01') }}">

                <span class="text-muted small">&ndash;</span>

                <input type="date"
                       name="end_date"
                       class="form-control form-control-sm font-monospace"
                       value="{{ $filters['end_date'] ?? date('Y-m-d') }}">

                <button type="submit"
                        class="btn btn-primary btn-sm btn-compact"
                        title="Filter Custom Range">
                    <i class="fas fa-filter"></i>
                    <span class="d-none d-sm-inline">Apply</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Performance Details -->
    <div class="card p-2">
        <div class="sp-table-wrapper">
            <table class="table table-hover table-sm align-middle">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 55px;">Rank</th>
                        <th>Representative Name</th>
                        <th>Contact Details</th>
                        <th class="text-center">Orders</th>
                        <th class="text-center">Sold Units</th>
                        <th class="text-end">Total Sales</th>
                        <th class="text-end">Discount</th>
                        <th class="text-end">Collected</th>
                        <th class="text-end">Due</th>
                        <th class="text-end">AOV</th>
                        <th class="text-end">Gross Profit</th>
                        <th class="text-end" style="width: 100px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($performance as $index => $row)
                        @php
                            $profitVal = (float) ($row->gross_profit ?? 0);
                            $profitClass = $profitVal >= 0 ? 'text-success' : 'text-danger';
                        @endphp

                        <tr>
                            <!-- Rank -->
                            <td class="text-center">
                                @if($index == 0)
                                    <span class="rank-badge bg-warning text-dark">
                                        <i class="fas fa-crown me-1"></i> #1
                                    </span>
                                @elseif($index == 1)
                                    <span class="rank-badge bg-secondary text-white">
                                        #2
                                    </span>
                                @elseif($index == 2)
                                    <span class="rank-badge bg-light text-dark border">
                                        #3
                                    </span>
                                @else
                                    <span class="font-monospace text-muted">
                                        #{{ $index + 1 }}
                                    </span>
                                @endif
                            </td>

                            <!-- Representative Name -->
                            <td>
                                <div class="staff-name">{{ $row->sales_person_name ?? 'Unknown Staff' }}</div>
                                <span class="staff-meta font-monospace">
                                    ID: #{{ $row->sales_person_id ?? '—' }}
                                </span>
                            </td>

                            <!-- Contact Details -->
                            <td>
                                <div class="font-monospace text-muted bn-digit" style="font-size: 10.5px;">
                                    {{ $row->sales_person_phone ?? 'N/A' }}
                                </div>
                                <small class="text-muted" style="font-size: 9.5px;">
                                    {{ $row->sales_person_email ?? '' }}
                                </small>
                            </td>

                            <!-- Orders -->
                            <td class="text-center font-monospace bn-digit fw-bold text-dark">
                                {{ number_format($row->total_orders ?? 0) }}
                            </td>

                            <!-- Sold Units -->
                            <td class="text-center font-monospace bn-digit text-muted">
                                <span class="badge bg-light text-dark border">
                                    {{ number_format($row->total_quantity_sold ?? 0) }} pcs
                                </span>
                            </td>

                            <!-- Total Sales -->
                            <td class="text-end font-monospace bn-digit fw-bold text-dark">
                                ৳{{ number_format($row->total_sales ?? 0, 2) }}
                            </td>

                            <!-- Discount -->
                            <td class="text-end font-monospace bn-digit text-danger">
                                {{ ($row->total_discount ?? 0) > 0 ? '-৳' . number_format($row->total_discount, 2) : '৳0.00' }}
                            </td>

                            <!-- Collected -->
                            <td class="text-end font-monospace bn-digit fw-bold text-success">
                                ৳{{ number_format($row->total_collected ?? 0, 2) }}
                            </td>

                            <!-- Due -->
                            <td class="text-end font-monospace bn-digit fw-bold {{ ($row->total_due ?? 0) > 0 ? 'text-danger' : 'text-muted' }}">
                                ৳{{ number_format($row->total_due ?? 0, 2) }}
                            </td>

                            <!-- Average Order Value -->
                            <td class="text-end font-monospace bn-digit text-muted">
                                ৳{{ number_format($row->avg_order_value ?? 0, 2) }}
                            </td>

                            <!-- Gross Profit -->
                            <td class="text-end font-monospace bn-digit fw-bold {{ $profitClass }}">
                                ৳{{ number_format($profitVal, 2) }}
                            </td>

                            <!-- View Orders -->
                            <td class="text-end">
                                <a href="{{ route('admin.branch_report.sales', ['branch_id' => $branch->id, 'sales_person_id' => $row->sales_person_id ?? null, 'period' => $curPeriod]) }}"
                                   class="btn btn-outline-primary btn-sm btn-compact"
                                   title="View Invoices">
                                    <i class="fas fa-list-ul"></i>
                                    Orders
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="empty-state">
                                <i class="fas fa-user-slash fa-2x"></i>
                                No staff sales activity logged for {{ $branch->name ?? 'this branch' }} during this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
