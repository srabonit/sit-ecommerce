@extends('layouts.admin')

@section('title', 'Courier Management Dashboard - ' . config('app.name'))

@php
    // Safe media resolver supporting Global Media Library paths
    $resolveCourierMedia = function($path, $fallback = '') {
        if (empty($path)) return $fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) return show_image($path, $fallback);
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset(ltrim(str_replace(['public_html/', 'public/'], '', $path), '/'));
    };
@endphp

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
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
    #courier-dashboard-root .proof-media-surface,
    #courier-dashboard-root .proof-thumb-box,
    .courier-modal-scope .modal-content,
    .courier-modal-scope .btn,
    .courier-modal-scope .form-control,
    .courier-modal-scope .form-select {
        border-radius: 5px !important;
    }

    #courier-dashboard-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #courier-dashboard-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Safe Monospace & Bengali Digit Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Compact Button Styling */
    .btn-compact {
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

    /* Metric Soft Cards - Overflow Safe Layout */
    .metric-soft-card {
        padding: 12px 14px;
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        overflow: hidden;
        position: relative;
        transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s ease;
    }
    .metric-soft-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
    }
    .metric-icon-square {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }

    .metric-card-blue   { background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%); border-color: #bae6fd; }
    .metric-card-yellow { background: linear-gradient(180deg, #ffffff 0%, #fefce8 100%); border-color: #fef08a; }
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-color: #bbf7d0; }
    .metric-card-orange { background: linear-gradient(180deg, #ffffff 0%, #fff7ed 100%); border-color: #fed7aa; }
    .metric-card-purple { background: linear-gradient(180deg, #ffffff 0%, #faf5ff 100%); border-color: #e9d5ff; }

    /* Widget Containers */
    .dash-widget-box {
        background: #ffffff;
        padding: 14px 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    /* Form Controls */
    #courier-dashboard-root .form-control,
    #courier-dashboard-root .form-select {
        font-size: 12px;
        border: 1px solid #cbd5e1;
        color: #0f172a;
        box-shadow: none;
        padding: 5px 10px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #courier-dashboard-root .form-control:focus,
    #courier-dashboard-root .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* PC Screen 100% Fit Tables Architecture */
    .pc-fit-table {
        width: 100%;
        margin-bottom: 0;
    }
    .pc-fit-table th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 6px;
        white-space: nowrap;
    }
    .pc-fit-table td {
        padding: 7px 6px;
        vertical-align: middle;
        font-size: 11.5px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }

    @media (min-width: 992px) {
        .pc-fit-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }

        .col-prov-name   { width: 34%; }
        .col-prov-parc   { width: 18%; }
        .col-prov-deliv  { width: 20%; }
        .col-prov-cod    { width: 28%; }

        .col-rec-inv     { width: 18%; }
        .col-rec-cust    { width: 26%; }
        .col-rec-courier { width: 26%; }
        .col-rec-cod     { width: 15%; }
        .col-rec-status  { width: 15%; }
    }

    /* Soft Enterprise Status Badges */
    .badge-soft-success { background: #dcfce7 !important; color: #15803d !important; border: 1px solid #bbf7d0 !important; }
    .badge-soft-warning { background: #fef9c3 !important; color: #854d0e !important; border: 1px solid #fef08a !important; }
    .badge-soft-danger  { background: #fee2e2 !important; color: #b91c1c !important; border: 1px solid #fecaca !important; }
    .badge-soft-info    { background: #e0f2fe !important; color: #0369a1 !important; border: 1px solid #bae6fd !important; }

    /* Global Media Chooser Component Surface */
    .proof-thumb-box {
        width: 24px;
        height: 24px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        position: relative;
    }
    .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .proof-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.15s ease;
    }
    .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    /* Mobile Responsive Optimizations (< 768px) */
    @media (max-width: 767.98px) {
        .header-top-actions {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .header-top-actions .btn {
            width: 100%;
            justify-content: center;
            padding: 8px 6px !important;
            font-size: 11.5px !important;
        }
        .filter-btn-group {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .dash-widget-box {
            padding: 10px 12px !important;
        }
        .metric-soft-card {
            padding: 8px 10px !important;
            gap: 6px !important;
        }
        .metric-icon-square {
            width: 30px !important;
            height: 30px !important;
            font-size: 12px !important;
        }
        .metric-soft-card .stat-label-text {
            font-size: 10px !important;
            line-height: 1.2 !important;
            white-space: normal !important;
        }
        .metric-soft-card .stat-val-text {
            font-size: 15px !important;
        }
        .metric-soft-card .stat-sub-text {
            font-size: 9px !important;
        }
        .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3 position-relative" id="courier-dashboard-root">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-truck-fast text-primary"></i>
                <span>Courier Management Dashboard</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Real-time parcel status, shipping operations and COD reconciliation metrics.
            </small>
        </div>
        <div class="header-top-actions d-flex flex-wrap gap-2">
            <a href="{{ route('admin.courier.booking') }}" class="btn btn-primary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-plus-circle me-1"></i>
                <span>New Booking</span>
            </a>
            <a href="{{ route('admin.courier.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-list me-1"></i>
                <span>View All Shipments</span>
            </a>
        </div>
    </div>

    <!-- Date Filter Card -->
    <div class="dash-widget-box mb-3">
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
            <div class="col-12 col-sm-auto filter-btn-group">
                <button type="submit" class="btn btn-dark btn-compact px-4 shadow-xs">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if(request()->has('date_filter'))
                    <a href="{{ route('admin.courier.dashboard') }}" class="btn btn-light btn-compact border text-muted shadow-xs">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Top KPI Row: Parcels Volume -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-xl-3">
            <div class="metric-soft-card metric-card-blue h-100">
                <div class="min-w-0 flex-grow-1 pe-1">
                    <small class="text-muted d-block fw-semibold stat-label-text" style="font-size: 11px;">Total Booked</small>
                    <h4 class="fw-bold my-1 text-dark bn-digit font-monospace stat-val-text" style="font-size: 18px;">{{ $stats['total_bookings'] }}</h4>
                    <small class="text-muted d-block text-truncate stat-sub-text" style="font-size: 10px;">Shipments Handled</small>
                </div>
                <div class="metric-icon-square bg-primary text-white shadow-xs">
                    <i class="fas fa-boxes-packing"></i>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="metric-soft-card metric-card-yellow h-100">
                <div class="min-w-0 flex-grow-1 pe-1">
                    <small class="text-muted d-block fw-semibold stat-label-text" style="font-size: 11px;">In Transit</small>
                    <h4 class="fw-bold my-1 text-dark bn-digit font-monospace stat-val-text" style="font-size: 18px;">{{ $stats['in_transit'] }}</h4>
                    <small class="text-muted d-block text-truncate stat-sub-text" style="font-size: 10px;">With Riders</small>
                </div>
                <div class="metric-icon-square bg-warning text-dark shadow-xs">
                    <i class="fas fa-truck-fast"></i>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="metric-soft-card metric-card-green h-100">
                <div class="min-w-0 flex-grow-1 pe-1">
                    <small class="text-muted d-block fw-semibold stat-label-text" style="font-size: 11px;">Delivered</small>
                    <h4 class="fw-bold my-1 text-dark bn-digit font-monospace stat-val-text" style="font-size: 18px;">{{ $stats['delivered'] }}</h4>
                    <small class="text-success fw-semibold d-block bn-digit font-monospace text-truncate stat-sub-text" style="font-size: 10px;">
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
                    <small class="text-muted d-block fw-semibold stat-label-text" style="font-size: 11px;">Returned</small>
                    <h4 class="fw-bold my-1 text-danger bn-digit font-monospace stat-val-text" style="font-size: 18px;">{{ $stats['returned'] + $stats['cancelled'] }}</h4>
                    <small class="text-danger fw-semibold d-block bn-digit font-monospace text-truncate stat-sub-text" style="font-size: 10px;">
                        {{ $stats['returned'] }} R | {{ $stats['cancelled'] }} C
                    </small>
                </div>
                <div class="metric-icon-square bg-danger text-white shadow-xs">
                    <i class="fas fa-rotate-left"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary KPI Row: Financial & COD Settlement -->
    <div class="row g-2 mb-3">
        <div class="col-12 col-md-4">
            <div class="metric-soft-card metric-card-purple h-100">
                <div class="min-w-0 pe-2">
                    <small class="text-muted d-block fw-semibold" style="font-size: 11px;">Collectible COD</small>
                    <h4 class="fw-bold my-1 text-dark font-monospace bn-digit" style="font-size: 18px;">৳ {{ number_format($stats['total_cod'], 2) }}</h4>
                    <small class="text-muted d-block" style="font-size: 10px;">Customer Cash on Delivery</small>
                </div>
                <div class="metric-icon-square bg-secondary text-white shadow-xs">
                    <i class="fas fa-hand-holding-dollar"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="metric-soft-card metric-card-green h-100">
                <div class="min-w-0 pe-2">
                    <small class="text-muted d-block fw-semibold" style="font-size: 11px;">Settled Remittance (Received)</small>
                    <h4 class="fw-bold my-1 text-success font-monospace bn-digit" style="font-size: 18px;">৳ {{ number_format($stats['settled_cod'], 2) }}</h4>
                    <small class="text-muted d-block" style="font-size: 10px;">Transferred from Courier to Bank</small>
                </div>
                <div class="metric-icon-square bg-success text-white shadow-xs">
                    <i class="fas fa-money-bill-transfer"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="metric-soft-card metric-card-orange h-100">
                <div class="min-w-0 pe-2">
                    <small class="text-muted d-block fw-semibold" style="font-size: 11px;">Unsettled COD Due</small>
                    <h4 class="fw-bold my-1 text-danger font-monospace bn-digit" style="font-size: 18px;">৳ {{ number_format($stats['unsettled_cod'], 2) }}</h4>
                    <small class="text-muted d-block bn-digit font-monospace" style="font-size: 10px;">Cost Due: ৳ {{ number_format($stats['courier_cost_due'], 2) }}</small>
                </div>
                <div class="metric-icon-square bg-danger-subtle text-danger shadow-xs">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Row: Provider Breakdown & Recent Shipments -->
    <div class="row g-2 g-md-3">
        <!-- Courier Provider Performance -->
        <div class="col-12 col-lg-5">
            <div class="dash-widget-box h-100 p-0 overflow-hidden">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                        <i class="fas fa-chart-pie text-primary"></i>
                        <span>Courier Providers Breakdown</span>
                    </h6>
                    <span class="badge bg-light text-muted border font-monospace" style="font-size: 9.5px;">PARTNER MATRIX</span>
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
                                        <div class="d-flex align-items-center gap-1 min-w-0">
                                            @if(!empty($cLogo))
                                                <div class="proof-thumb-box" style="border-radius: 4px !important;" onclick="zoomConsignmentProof('{{ $cLogo }}', '{{ $cb->courier_name }} Logo')">
                                                    <img src="{{ $cLogo }}" alt="{{ $cb->courier_name }}">
                                                </div>
                                            @else
                                                <i class="fas fa-truck text-secondary" style="font-size: 11px;"></i>
                                            @endif
                                            <strong class="text-capitalize text-dark text-truncate" style="font-size: 12px;" title="{{ $cb->courier_name }}">{{ $cb->courier_name }}</strong>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border px-2 py-0.5 bn-digit font-monospace" style="font-size: 10px; border-radius: 5px !important;">{{ $cb->total_count }}</span>
                                    </td>
                                    <td class="text-center text-success fw-bold bn-digit font-monospace" style="font-size: 11.5px;">
                                        {{ $cb->delivered_count }}
                                    </td>
                                    <td class="text-end pe-3 font-monospace fw-bold text-primary bn-digit" style="font-size: 11.5px;">
                                        ৳ {{ number_format((float)$cb->total_cod, 0) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">No courier shipments recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Shipments Table -->
        <div class="col-12 col-lg-7">
            <div class="dash-widget-box h-100 p-0 overflow-hidden">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center gap-2">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                        <i class="fas fa-clock-rotate-left text-primary"></i>
                        <span class="text-truncate">Recent Booked Shipments</span>
                    </h6>
                    <div class="d-flex align-items-center gap-1">
                        <button type="button" class="btn btn-outline-primary btn-compact py-1 px-2 shadow-xs" onclick="chooseGlobalConsignmentMedia()" title="Attach Courier Slip from Media Library">
                            <i class="fas fa-paperclip me-1"></i><span>Slip Media</span>
                        </button>
                        <a href="{{ route('admin.courier.index') }}" class="btn btn-light border btn-compact py-1 px-2 text-dark shadow-xs">
                            <span>View All</span> <i class="fas fa-arrow-right ms-1"></i>
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
                                    $badge = match($st) {
                                        'delivered', 'completed' => 'badge-soft-success',
                                        'in_transit', 'shipped'   => 'badge-soft-info',
                                        'returned', 'cancelled'  => 'badge-soft-danger',
                                        default                  => 'badge-soft-warning'
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
                                        <div class="d-flex align-items-center gap-1 min-w-0">
                                            @if($slipUrl)
                                                <div class="proof-thumb-box flex-shrink-0" style="border-radius: 4px !important;" onclick="zoomConsignmentProof('{{ $slipUrl }}', 'Invoice #{{ $rb->invoice_no }} Slip')" title="Zoom Consignment Slip">
                                                    <img src="{{ $slipUrl }}" alt="Slip">
                                                </div>
                                            @endif
                                            <div class="min-w-0 flex-grow-1">
                                                <strong class="text-capitalize text-dark d-block text-truncate" style="font-size: 11px;" title="{{ $rb->courier_name }}">{{ $rb->courier_name }}</strong>
                                                <small class="text-muted font-monospace d-block text-truncate bn-digit" style="font-size: 9.5px;" title="{{ $rb->tracking_code }}">{{ $rb->tracking_code }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end font-monospace fw-bold text-dark bn-digit" style="font-size: 11.5px;">
                                        ৳ {{ number_format((float)$rb->cod_amount, 0) }}
                                    </td>
                                    <td class="text-center pe-3">
                                        <span class="badge {{ $badge }} text-uppercase px-2 py-0.5 font-monospace" style="font-size: 8.5px; border-radius: 5px !important;">
                                            {{ str_replace('_', ' ', $rb->delivery_status ?? 'booked') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted small">No recent bookings found.</td>
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