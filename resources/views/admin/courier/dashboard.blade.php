@extends('layouts.admin')

@section('title', 'Courier Dashboard - ' . config('app.name'))

@php
// Safe media resolver supporting Global Media Library paths
$resolveCourierMedia = function ($path, $fallback = '') {
    if (empty($path))
        return $fallback;
    if (class_exists(\App\Services\ImagePathService::class)) {
        return \App\Services\ImagePathService::resolveUrl($path, $fallback);
    }
    if (function_exists('show_image'))
        return show_image($path, $fallback);
    if (filter_var($path, FILTER_VALIDATE_URL))
        return $path;
    return asset(ltrim(str_replace(['public_html/', 'public/'], '', $path), '/'));
};
@endphp

@section('admin_content')
    <style>
        /* =========================================================
           COURIER DASHBOARD - KAIADMIN CLASSIC WHITE
        ========================================================= */

        #courier-dashboard-root {
            --cd-primary: var(--bs-primary, #1572e8);
            --cd-text: #1d2939;
            --cd-muted: #667085;
            --cd-border: #e5e7eb;
            --cd-surface: #ffffff;
            --cd-soft: #f8fafc;
            --cd-radius: 5px;
            --cd-shadow: 0 1px 3px rgba(16, 24, 40, 0.05);
            color: var(--cd-text);
            min-width: 0;
        }

        /* Consistent 5px radius */
        #courier-dashboard-root .btn,
        #courier-dashboard-root .btn-sm,
        #courier-dashboard-root button,
        #courier-dashboard-root .form-control,
        #courier-dashboard-root .form-select,
        #courier-dashboard-root .badge,
        #courier-dashboard-root .input-group-text,
        #courier-dashboard-root .card,
        #courier-dashboard-root .dash-widget-box,
        #courier-dashboard-root .metric-soft-card,
        #courier-dashboard-root .metric-icon-square,
        #courier-dashboard-root .proof-thumb-box,
        .courier-modal-scope .modal-content,
        .courier-modal-scope .btn,
        .courier-modal-scope .form-control,
        .courier-modal-scope .form-select {
            border-radius: var(--cd-radius) !important;
        }

        #courier-dashboard-root .input-group > .input-group-text {
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }

        #courier-dashboard-root .input-group > .form-control {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }

        /* Typography */
        #courier-dashboard-root,
        #courier-dashboard-root button,
        #courier-dashboard-root input,
        #courier-dashboard-root select {
            font-family: inherit;
        }

        #courier-dashboard-root .bn-digit,
        #courier-dashboard-root .font-monospace {
            font-weight: 700 !important;
        }

        #courier-dashboard-root .text-muted {
            color: var(--cd-muted) !important;
        }

        /* Compact buttons */
        #courier-dashboard-root .btn-compact {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
            min-height: 34px;
            padding: 6px 12px !important;
            font-size: 11.5px !important;
            line-height: 1.3 !important;
            font-weight: 600 !important;
            white-space: nowrap;
            transition: all 0.18s ease;
        }

        #courier-dashboard-root .btn-compact:hover {
            transform: translateY(-1px);
        }

        /* Dashboard header */
        #courier-dashboard-root .dashboard-heading {
            color: var(--cd-text);
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        #courier-dashboard-root .dashboard-subtitle {
            color: var(--cd-muted);
            font-size: 12px;
            line-height: 1.5;
        }

        /* Widget containers */
        #courier-dashboard-root .dash-widget-box {
            background: var(--cd-surface);
            padding: 14px 16px;
            border: 1px solid var(--cd-border);
            box-shadow: var(--cd-shadow);
            min-width: 0;
        }

        /* Date filter */
        #courier-dashboard-root .filter-widget {
            background: #fff;
            border: 1px solid var(--cd-border);
            border-radius: var(--cd-radius);
            padding: 12px 14px;
            box-shadow: var(--cd-shadow);
        }

        /* Form controls */
        #courier-dashboard-root .form-control,
        #courier-dashboard-root .form-select {
            min-height: 36px;
            padding: 6px 10px;
            font-size: 12px;
            color: var(--cd-text);
            background-color: #fff;
            border: 1px solid #d0d5dd;
            box-shadow: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        #courier-dashboard-root .form-control:focus,
        #courier-dashboard-root .form-select:focus {
            border-color: var(--cd-primary);
            box-shadow: 0 0 0 3px rgba(21, 114, 232, 0.12);
            outline: none;
        }

        /* KPI cards */
        #courier-dashboard-root .metric-soft-card {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            min-width: 0;
            padding: 14px 15px;
            background: #fff;
            border: 1px solid var(--cd-border);
            box-shadow: var(--cd-shadow);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        #courier-dashboard-root .metric-soft-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 14px rgba(16, 24, 40, 0.08);
        }

        #courier-dashboard-root .metric-icon-square {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            font-size: 15px;
        }

        #courier-dashboard-root .metric-card-blue {
            background: linear-gradient(145deg, #fff 0%, #f0f7ff 100%);
            border-color: #cfe4ff;
        }

        #courier-dashboard-root .metric-card-yellow {
            background: linear-gradient(145deg, #fff 0%, #fffbeb 100%);
            border-color: #f8e7a3;
        }

        #courier-dashboard-root .metric-card-green {
            background: linear-gradient(145deg, #fff 0%, #f0fdf4 100%);
            border-color: #c7ecd2;
        }

        #courier-dashboard-root .metric-card-orange {
            background: linear-gradient(145deg, #fff 0%, #fff7ed 100%);
            border-color: #f8dcc2;
        }

        #courier-dashboard-root .metric-card-purple {
            background: linear-gradient(145deg, #fff 0%, #faf5ff 100%);
            border-color: #e9d5ff;
        }

        #courier-dashboard-root .stat-label-text {
            color: var(--cd-muted);
            font-size: 11px;
            font-weight: 600;
        }

        #courier-dashboard-root .stat-val-text {
            color: var(--cd-text);
            font-size: 19px;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        #courier-dashboard-root .stat-sub-text {
            color: var(--cd-muted);
            font-size: 10px;
            line-height: 1.4;
        }

        /* Table containers - retain every column */
        #courier-dashboard-root .pc-fit-container {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
        }

        #courier-dashboard-root .pc-fit-container::-webkit-scrollbar {
            height: 6px;
        }

        #courier-dashboard-root .pc-fit-container::-webkit-scrollbar-track {
            background: #f8fafc;
        }

        #courier-dashboard-root .pc-fit-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        #courier-dashboard-root .pc-fit-table {
            width: 100%;
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        #courier-dashboard-root .pc-fit-table th {
            padding: 10px 8px;
            color: #667085;
            background: #f8fafc;
            border-bottom: 1px solid var(--cd-border);
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.035em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        #courier-dashboard-root .pc-fit-table td {
            padding: 9px 8px;
            color: #344054;
            border-bottom: 1px solid #f0f1f3;
            vertical-align: middle;
            font-size: 11.5px;
        }

        #courier-dashboard-root .pc-fit-table tbody tr:last-child td {
            border-bottom: 0;
        }

        #courier-dashboard-root .pc-fit-table tbody tr:hover td {
            background-color: #fafbfc;
        }

        /* Keep table columns readable on desktop */
        @media (min-width: 992px) {
            #courier-dashboard-root .col-lg-5 .pc-fit-table {
                min-width: 510px;
                table-layout: fixed;
            }

            #courier-dashboard-root .col-lg-7 .pc-fit-table {
                min-width: 760px;
                table-layout: fixed;
            }

            #courier-dashboard-root .col-prov-name {
                width: 34%;
            }

            #courier-dashboard-root .col-prov-parc {
                width: 18%;
            }

            #courier-dashboard-root .col-prov-deliv {
                width: 20%;
            }

            #courier-dashboard-root .col-prov-cod {
                width: 28%;
            }

            #courier-dashboard-root .col-rec-inv {
                width: 18%;
            }

            #courier-dashboard-root .col-rec-cust {
                width: 26%;
            }

            #courier-dashboard-root .col-rec-courier {
                width: 26%;
            }

            #courier-dashboard-root .col-rec-cod {
                width: 15%;
            }

            #courier-dashboard-root .col-rec-status {
                width: 15%;
            }
        }

        /* Status badges */
        #courier-dashboard-root .badge-soft-success {
            color: #15803d !important;
            background: #dcfce7 !important;
            border: 1px solid #bbf7d0 !important;
        }

        #courier-dashboard-root .badge-soft-warning {
            color: #854d0e !important;
            background: #fef9c3 !important;
            border: 1px solid #fef08a !important;
        }

        #courier-dashboard-root .badge-soft-danger {
            color: #b91c1c !important;
            background: #fee2e2 !important;
            border: 1px solid #fecaca !important;
        }

        #courier-dashboard-root .badge-soft-info {
            color: #0369a1 !important;
            background: #e0f2fe !important;
            border: 1px solid #bae6fd !important;
        }

        /* Media thumbnails */
        #courier-dashboard-root .proof-thumb-box {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            flex: 0 0 28px;
            overflow: hidden;
            background: #fff;
            border: 1px solid #d0d5dd;
            cursor: pointer;
        }

        #courier-dashboard-root .proof-thumb-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #courier-dashboard-root .proof-thumb-hover-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.45);
            opacity: 0;
            transition: opacity 0.15s ease;
        }

        #courier-dashboard-root .proof-thumb-box:hover .proof-thumb-hover-overlay {
            opacity: 1;
        }

        /* Panel headings */
        #courier-dashboard-root .panel-heading {
            min-height: 52px;
            padding: 12px 14px;
            background: #fff;
            border-bottom: 1px solid var(--cd-border);
        }

        #courier-dashboard-root .panel-title {
            color: var(--cd-text);
            font-size: 13px;
            font-weight: 700;
            margin: 0;
        }

        /* Modal styling */
        .courier-modal-scope .modal-content {
            border: 1px solid var(--cd-border, #e5e7eb);
            box-shadow: 0 12px 35px rgba(16, 24, 40, 0.18);
        }

        .courier-modal-scope .modal-header {
            padding: 12px 14px;
        }

        .courier-modal-scope .modal-body {
            padding: 14px;
        }

        /* Responsive */
        @media (max-width: 767.98px) {
            #courier-dashboard-root {
                padding-right: 10px !important;
                padding-left: 10px !important;
            }

            #courier-dashboard-root .header-top-actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
                width: 100%;
                gap: 7px;
            }

            #courier-dashboard-root .header-top-actions .btn {
                width: 100%;
                min-height: 36px;
                justify-content: center;
                padding: 7px 5px !important;
                font-size: 11px !important;
            }

            #courier-dashboard-root .filter-btn-group {
                display: grid;
                grid-template-columns: 1fr 1fr;
                width: 100%;
                gap: 7px;
            }

            #courier-dashboard-root .filter-btn-group .btn {
                width: 100%;
            }

            #courier-dashboard-root .dash-widget-box {
                padding: 10px 12px !important;
            }

            #courier-dashboard-root .metric-soft-card {
                min-height: 76px;
                padding: 10px !important;
                gap: 6px !important;
            }

            #courier-dashboard-root .metric-icon-square {
                width: 32px !important;
                height: 32px !important;
                flex-basis: 32px;
                font-size: 12px !important;
            }

            #courier-dashboard-root .metric-soft-card .stat-label-text {
                font-size: 10px !important;
                line-height: 1.25 !important;
                white-space: normal !important;
            }

            #courier-dashboard-root .metric-soft-card .stat-val-text {
                font-size: 16px !important;
            }

            #courier-dashboard-root .metric-soft-card .stat-sub-text {
                font-size: 9px !important;
            }

            #courier-dashboard-root .panel-heading {
                padding: 10px 12px;
            }

            #courier-dashboard-root .pc-fit-container {
                overflow-x: auto !important;
            }

            #courier-dashboard-root .col-lg-5 .pc-fit-table {
                min-width: 490px;
            }

            #courier-dashboard-root .col-lg-7 .pc-fit-table {
                min-width: 740px;
            }
        }

        @media (max-width: 380px) {
            #courier-dashboard-root .metric-soft-card {
                padding: 8px !important;
            }

            #courier-dashboard-root .metric-soft-card .stat-val-text {
                font-size: 14px !important;
            }

            #courier-dashboard-root .metric-icon-square {
                width: 28px !important;
                height: 28px !important;
                flex-basis: 28px;
                font-size: 11px !important;
            }

            #courier-dashboard-root .header-top-actions .btn {
                font-size: 10px !important;
            }
        }
    </style>

    <div class="container-fluid px-2 px-md-4 py-3 position-relative" id="courier-dashboard-root">

        <!-- Dashboard Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
            <div class="min-w-0">
                <h5 class="dashboard-heading mb-1 d-flex align-items-center gap-2">
                    <i class="fas fa-truck-fast text-primary"></i>
                    <span>Courier Dashboard</span>
                </h5>
                <small class="dashboard-subtitle d-block">
                   Parcel & COD Summary
                </small>
            </div>

            <div class="header-top-actions d-flex flex-wrap gap-2">
                <a href="{{ route('admin.courier.booking') }}" class="btn btn-primary btn-compact shadow-xs fw-semibold">
                    <i class="fas fa-plus-circle me-1"></i>
                    <span>New Booking</span>
                </a>

                <a href="{{ route('admin.courier.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs fw-semibold">
                    <i class="fas fa-list me-1"></i>
                    <span>All Shipments</span>
                </a>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="filter-widget mb-3">
            <form method="GET" action="{{ route('admin.courier.dashboard') }}" class="row g-2 align-items-center">

                <div class="col-12 col-sm-5 col-md-3">
                    <select name="date_filter" id="dash_date_filter" class="form-select form-select-sm fw-semibold" onchange="onDateFilterChange(this)">
                        <option value="">All Time</option>
                        <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="this_week" {{ request('date_filter') == 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="this_month" {{ request('date_filter') == 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="custom" {{ request('date_filter') == 'custom' ? 'selected' : '' }}>Custom Range...</option>
                    </select>
                </div>

                <div class="col-12 col-sm-7 col-md-4 d-flex gap-2 {{ request('date_filter') == 'custom' ? '' : 'd-none' }}" id="customDateWrap">
                    <input type="date" name="start_date" class="form-control form-control-sm bn-digit font-monospace" value="{{ request('start_date') }}">
                    <input type="date" name="end_date" class="form-control form-control-sm bn-digit font-monospace" value="{{ request('end_date') }}">
                </div>

                <div class="col-12 col-sm-auto filter-btn-group d-flex gap-2">
                    <button type="submit" class="btn btn-dark btn-compact px-4 shadow-xs w-100 w-sm-auto">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>

                    @if(request()->has('date_filter') && request('date_filter') != '')
                        <a href="{{ route('admin.courier.dashboard') }}" class="btn btn-light btn-compact border text-muted shadow-xs w-100 w-sm-auto text-center">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Parcel KPI Cards -->
        <div class="row g-2 mb-3">

            <div class="col-6 col-xl-3">
                <div class="metric-soft-card metric-card-blue h-100">
                    <div class="min-w-0 flex-grow-1 pe-1">
                        <small class="text-muted d-block fw-semibold stat-label-text">Total Booked</small>
                        <h4 class="fw-bold my-1 text-dark bn-digit font-monospace stat-val-text">{{ $stats['total_bookings'] }}</h4>
                        <small class="text-muted d-block text-truncate stat-sub-text">Total Shipments</small>
                    </div>
                    <div class="metric-icon-square bg-primary text-white shadow-xs">
                        <i class="fas fa-boxes-packing"></i>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="metric-soft-card metric-card-yellow h-100">
                    <div class="min-w-0 flex-grow-1 pe-1">
                        <small class="text-muted d-block fw-semibold stat-label-text">In Transit</small>
                        <h4 class="fw-bold my-1 text-dark bn-digit font-monospace stat-val-text">{{ $stats['in_transit'] }}</h4>
                        <small class="text-muted d-block text-truncate stat-sub-text">On the Way</small>
                    </div>
                    <div class="metric-icon-square bg-warning text-dark shadow-xs">
                        <i class="fas fa-truck-fast"></i>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="metric-soft-card metric-card-green h-100">
                    <div class="min-w-0 flex-grow-1 pe-1">
                        <small class="text-muted d-block fw-semibold stat-label-text">Delivered</small>
                        <h4 class="fw-bold my-1 text-dark bn-digit font-monospace stat-val-text">{{ $stats['delivered'] }}</h4>
                        <small class="text-success fw-semibold d-block bn-digit font-monospace text-truncate stat-sub-text">
                            {{ $stats['total_bookings'] > 0 ? round(($stats['delivered'] / $stats['total_bookings']) * 100, 1) : 0 }}% Rate
                        </small>
                    </div>
                    <div class="metric-icon-square bg-success text-white shadow-xs">
                        <i class="fas fa-circle-check"></i>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="metric-soft-card metric-card-orange h-100">
                    <div class="min-w-0 flex-grow-1 pe-1">
                        <small class="text-muted d-block fw-semibold stat-label-text">Returned</small>
                        <h4 class="fw-bold my-1 text-danger bn-digit font-monospace stat-val-text">{{ $stats['returned'] + $stats['cancelled'] }}</h4>
                        <small class="text-danger fw-semibold d-block bn-digit font-monospace text-truncate stat-sub-text">
                            {{ $stats['returned'] }} R | {{ $stats['cancelled'] }} C
                        </small>
                    </div>
                    <div class="metric-icon-square bg-danger text-white shadow-xs">
                        <i class="fas fa-rotate-left"></i>
                    </div>
                </div>
            </div>

        </div>

        <!-- Financial KPI Cards -->
        <div class="row g-2 mb-3">

            <div class="col-12 col-md-4">
                <div class="metric-soft-card metric-card-purple h-100">
                    <div class="min-w-0 pe-2">
                        <small class="text-muted d-block fw-semibold" style="font-size: 11px;">Collectible COD</small>
                        <h4 class="fw-bold my-1 text-dark font-monospace bn-digit" style="font-size: 18px;">৳ {{ number_format($stats['total_cod'], 2) }}</h4>
                        <small class="text-muted d-block" style="font-size: 10px;">Customer COD Amount</small>
                    </div>
                    <div class="metric-icon-square bg-secondary text-white shadow-xs">
                        <i class="fas fa-hand-holding-dollar"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="metric-soft-card metric-card-green h-100">
                    <div class="min-w-0 pe-2">
                        <small class="text-muted d-block fw-semibold" style="font-size: 11px;">Settled COD</small>
                        <h4 class="fw-bold my-1 text-success font-monospace bn-digit" style="font-size: 18px;">৳ {{ number_format($stats['settled_cod'], 2) }}</h4>
                        <small class="text-muted d-block" style="font-size: 10px;">Received from Courier</small>
                    </div>
                    <div class="metric-icon-square bg-success text-white shadow-xs">
                        <i class="fas fa-money-bill-transfer"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="metric-soft-card metric-card-orange h-100">
                    <div class="min-w-0 pe-2">
                        <small class="text-muted d-block fw-semibold" style="font-size: 11px;">Unsettled COD</small>
                        <h4 class="fw-bold my-1 text-danger font-monospace bn-digit" style="font-size: 18px;">৳ {{ number_format($stats['unsettled_cod'], 2) }}</h4>
                        <small class="text-muted d-block bn-digit font-monospace" style="font-size: 10px;">Cost Due: ৳ {{ number_format($stats['courier_cost_due'], 2) }}</small>
                    </div>
                    <div class="metric-icon-square bg-danger-subtle text-danger shadow-xs">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>

        </div>

        <!-- Provider Breakdown & Recent Shipments -->
        <div class="row g-2 g-md-3">

            <!-- Courier Provider Performance -->
            <div class="col-12 col-lg-5">
                <div class="dash-widget-box h-100 p-0 overflow-hidden">

                    <div class="panel-heading d-flex justify-content-between align-items-center gap-2">
                        <h6 class="panel-title d-flex align-items-center gap-2">
                            <i class="fas fa-chart-pie text-primary"></i>
                            <span>Provider Breakdown</span>
                        </h6>
                        <span class="badge bg-light text-muted border font-monospace" style="font-size: 9px;">PROVIDERS</span>
                    </div>

                    <div class="pc-fit-container">
                        <table class="table table-sm pc-fit-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3 col-prov-name">Provider</th>
                                    <th class="text-center col-prov-parc">Parcels</th>
                                    <th class="text-center col-prov-deliv">Delivered</th>
                                    <th class="text-end pe-3 col-prov-cod">Total COD</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($courierBreakdown as $cb)
                                    @php
        $rawLogo = $cb->logo ?? null;
        $cLogo = !empty($rawLogo) ? $resolveCourierMedia($rawLogo) : null;
                                    @endphp

                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center gap-2 min-w-0">
                                                @if(!empty($cLogo))
                                                    <div class="proof-thumb-box" style="border-radius: 4px !important;" onclick="zoomConsignmentProof('{{ $cLogo }}', '{{ $cb->courier_name }} Logo')">
                                                        <img src="{{ $cLogo }}" alt="{{ $cb->courier_name }}">
                                                    </div>
                                                @else
                                                    <i class="fas fa-truck text-secondary" style="font-size: 11px;"></i>
                                                @endif

                                                <strong class="text-capitalize text-dark text-truncate" style="font-size: 12px;" title="{{ $cb->courier_name }}">
                                                    {{ $cb->courier_name }}
                                                </strong>
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border px-2 py-1 bn-digit font-monospace" style="font-size: 10px; border-radius: 5px !important;">
                                                {{ $cb->total_count }}
                                            </span>
                                        </td>

                                        <td class="text-center text-success fw-bold bn-digit font-monospace" style="font-size: 11.5px;">
                                            {{ $cb->delivered_count }}
                                        </td>

                                        <td class="text-end pe-3 font-monospace fw-bold text-primary bn-digit" style="font-size: 11.5px;">
                                            ৳ {{ number_format((float) $cb->total_cod, 0) }}
                                        </td>
                                    </tr>

                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">
                                            No courier shipments found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
    <!-- Recent Shipments -->
    <div class="col-12 col-lg-7">
        <div class="dash-widget-box h-100 p-0 overflow-hidden">

            <div class="panel-heading d-flex justify-content-between align-items-center gap-1 gap-sm-2">
                <!-- Title -->
                <h6 class="panel-title d-flex align-items-center gap-1 gap-sm-2 min-w-0 mb-0">
                    <i class="fas fa-clock-rotate-left text-primary"></i>
                    <span class="text-truncate" style="font-size: 0.9rem;">Recent Shipments</span>
                </h6>

                <!-- Buttons -->
                <div class="d-flex align-items-center gap-1 flex-shrink-0">
                    <!-- Documents Button -->
                    <button type="button" class="btn btn-outline-primary btn-compact py-1 px-1 px-sm-2 shadow-xs"
                        onclick="chooseGlobalConsignmentMedia()" title="Attach Courier Slip from Media Library">
                        <i class="fas fa-paperclip"></i>
                        <span class="d-none d-sm-inline ms-1">Documents</span>
                    </button>

                    <!-- View All Button -->
                    <a href="{{ route('admin.courier.index') }}"
                        class="btn btn-light border btn-compact py-1 px-1 px-sm-2 text-dark shadow-xs" title="View All">
                        <span class="d-none d-sm-inline">View All</span>
                        <span class="d-inline d-sm-none">All</span>
                        <i class="fas fa-arrow-right ms-sm-1"></i>
                    </a>
                </div>
            </div>

            <div class="pc-fit-container">
                <table class="table table-sm pc-fit-table align-middle table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3 col-rec-inv">Invoice</th>
                            <th class="col-rec-cust">Recipient</th>
                            <th class="col-rec-courier">Courier &amp; Tracking</th>
                            <th class="text-end col-rec-cod">COD</th>
                            <th class="text-center pe-3 col-rec-status">Status</th>
                        </tr>
                    </thead>

                            <tbody>
                                @forelse($recentBookings as $rb)
                                    @php
        $st = strtolower($rb->delivery_status ?? 'in_review');
        $badge = match ($st) {
            'delivered', 'completed' => 'badge-soft-success',
            'in_transit', 'shipped' => 'badge-soft-info',
            'returned', 'cancelled' => 'badge-soft-danger',
            default => 'badge-soft-warning'
        };

        $slipRaw = $rb->consignment_slip ?? ($rb->screenshot ?? null);
        $slipUrl = !empty($slipRaw) ? $resolveCourierMedia($slipRaw) : null;
                                    @endphp

                                    <tr>
                                        <td class="ps-3">
                                            <a href="{{ route('admin.order.view', $rb->order_id) }}" class="font-monospace fw-bold text-primary text-decoration-none bn-digit d-block text-truncate" style="font-size: 11px;" title="#{{ $rb->invoice_no ?? $rb->order_id }}">
                                                #{{ $rb->invoice_no ?? $rb->order_id }}
                                            </a>
                                        </td>

                                        <td>
                                            <strong class="text-dark d-block text-truncate mb-1" style="font-size: 11.5px;" title="{{ $rb->shipping_name }}">
                                                {{ $rb->shipping_name }}
                                            </strong>

                                            <small class="text-muted font-monospace bn-digit d-block text-truncate" style="font-size: 10px;">
                                                {{ $rb->shipping_phone }}
                                            </small>
                                        </td>

                                        <td>
                                            <div class="d-flex align-items-center gap-2 min-w-0">
                                                @if($slipUrl)
                                                    <div class="proof-thumb-box flex-shrink-0" style="border-radius: 4px !important;" onclick="zoomConsignmentProof('{{ $slipUrl }}', 'Invoice #{{ $rb->invoice_no }} Slip')" title="Zoom Consignment Slip">
                                                        <img src="{{ $slipUrl }}" alt="Slip">
                                                    </div>
                                                @endif

                                                <div class="min-w-0 flex-grow-1">
                                                    <strong class="text-capitalize text-dark d-block text-truncate" style="font-size: 11px;" title="{{ $rb->courier_name }}">
                                                        {{ $rb->courier_name }}
                                                    </strong>

                                                    <small class="text-muted font-monospace d-block text-truncate bn-digit" style="font-size: 9.5px;" title="{{ $rb->tracking_code }}">
                                                        {{ $rb->tracking_code }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="text-end font-monospace fw-bold text-dark bn-digit" style="font-size: 11.5px;">
                                            ৳ {{ number_format((float) $rb->cod_amount, 0) }}
                                        </td>

                                        <td class="text-center pe-3">
                                            <span class="badge {{ $badge }} text-uppercase px-2 py-1 font-monospace" style="font-size: 8.5px; border-radius: 5px !important;">
                                                {{ str_replace('_', ' ', $rb->delivery_status ?? 'booked') }}
                                            </span>
                                        </td>
                                    </tr>

                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted small">
                                            No recent bookings found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Lightbox Zoom Modal for Consignment Slips & Media Proof -->
    <div class="modal fade courier-modal-scope" id="courierProofZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content border-0 shadow-lg text-center p-2">
                <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark small" style="font-size: 12.5px;" id="courierProofZoomTitle">
                        <i class="fas fa-image text-primary me-1"></i> Courier Proof / Consignment Slip
                    </span>
                    <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-2">
                    <img id="courierProofZoomImgTag" src="" alt="Proof Preview" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 4px;">
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Custom Notice Modal -->
    <div class="modal fade courier-modal-scope" id="courierNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg text-center p-3">
                <div class="modal-body p-1">
                    <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-2" id="courierNoticeIconWrap" style="width: 44px; height: 44px; border-radius: 50% !important;">
                        <i class="fas fa-circle-info fs-4" id="courierNoticeIcon"></i>
                    </div>

                    <h6 class="fw-bold text-dark mb-1" id="courierNoticeTitle" style="font-size: 14px;">Notification</h6>
                    <p class="text-muted small mb-3" id="courierNoticeMsg" style="font-size: 12px; line-height: 1.45;"></p>

                    <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function onDateFilterChange(select) {
            const customWrap = document.getElementById('customDateWrap');
            if (select.value === 'custom') {
                customWrap.classList.remove('d-none');
            } else {
                customWrap.classList.add('d-none');
                select.form.submit();
            }
        }

        function showCourierNotice(message, title = 'Notification', type = 'info') {
            const titleEl  = document.getElementById('courierNoticeTitle');
            const msgEl    = document.getElementById('courierNoticeMsg');
            const iconWrap = document.getElementById('courierNoticeIconWrap');
            const iconEl   = document.getElementById('courierNoticeIcon');

            if (titleEl) titleEl.innerText = title;
            if (msgEl) msgEl.innerText = message;

            if (iconWrap && iconEl) {
                if (type === 'error' || type === 'danger') {
                    iconWrap.className = 'rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2';
                    iconEl.className = 'fas fa-circle-exclamation fs-4';
                } else if (type === 'success') {
                    iconWrap.className = 'rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-2';
                    iconEl.className = 'fas fa-circle-check fs-4';
                } else {
                    iconWrap.className = 'rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-2';
                    iconEl.className = 'fas fa-circle-info fs-4';
                }
            }

            const modalEl = document.getElementById('courierNoticeModal');
            if (modalEl && window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
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

        function chooseGlobalConsignmentMedia() {
            if (typeof window.openMediaPicker === 'function') {
                window.openMediaPicker({
                    multiple: false,
                    onSelect: function(media) {
                        const picked = extractMediaItem(media);
                        if (picked && picked.url) {
                            zoomConsignmentProof(picked.url, 'Selected Consignment Slip');
                            showCourierNotice('Media selected from Global Media Library. You can attach this slip directly to the corresponding booking or order.', 'Media Selected', 'success');
                        }
                    }
                });
            } else {
                showCourierNotice('Global Media Library is loading or not available on this layout.', 'Notice');
            }
        }

        function zoomConsignmentProof(url, title = 'Courier Proof') {
            const titleEl = document.getElementById('courierProofZoomTitle');
            const imgTag  = document.getElementById('courierProofZoomImgTag');
            if (url && imgTag) {
                if (titleEl) titleEl.innerText = title;
                imgTag.src = url;
                bootstrap.Modal.getOrCreateInstance(document.getElementById('courierProofZoomModal')).show();
            }
        }
    </script>
@endsection