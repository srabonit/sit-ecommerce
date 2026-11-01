@extends('layouts.admin')

@section('title', 'Security Dashboard')

@section('admin_content')
<style>
    /* Executive SaaS Theme & Micro-Styling */
    .sec-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 0.875rem;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .sec-card:hover {
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
    }
    
    /* Header Icon */
    .sec-header-icon {
        width: 38px;
        height: 38px;
        border-radius: 0.625rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* KPI Micro-Icon Box */
    .sec-kpi-icon {
        width: 36px;
        height: 36px;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.9375rem;
    }

    /* Segmented Date Pills */
    .sec-pill-group .btn {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border: none;
        color: #64748b;
        background: transparent;
    }
    .sec-pill-group .btn.active {
        background-color: #ffffff !important;
        color: #0f172a !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08) !important;
        border-radius: 0.5rem;
    }

    /* Desktop Table */
    .sec-table thead th {
        font-size: 0.6875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        color: #94a3b8;
        background-color: #f8fafc;
        border-bottom: 1px solid #f1f5f9;
        padding: 0.75rem 1rem;
    }
    .sec-table tbody td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #f8fafc;
        vertical-align: middle;
        font-size: 0.78125rem;
    }

    /* Mobile Native Card Items */
    .sec-mobile-card-item {
        background-color: #f8fafc;
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 0.625rem;
        padding: 10px 14px !important;
        margin-bottom: 8px;
    }

    @media (max-width: 575.98px) {
        .sec-kpi-card {
            padding: 0.75rem !important;
        }
        .sec-kpi-number {
            font-size: 1.35rem !important;
            line-height: 1.2 !important;
        }
    }
</style>

<div class="container-fluid px-2 px-sm-3 px-md-4 py-3" id="security-dashboard-wrapper" style="background-color: #f8fafc; min-height: 100vh;">
    <div class="mx-auto" style="max-width: 1600px;">

        <!-- ================= 1. TOP HEADER & CONTROLS ================= -->
        <header class="sec-card p-3 mb-3">
            <!-- Row 1: Title & Protection Badge -->
            <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom border-light-subtle">
                <div class="d-flex align-items-center gap-2 text-truncate me-2">
                    <div class="sec-header-icon bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20">
                        <i class="fas fa-shield-halved fs-6"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0 text-truncate" style="letter-spacing: -0.01em;">Security Overview</h5>
                        <p class="text-muted small mb-0 d-none d-sm-block" style="font-size: 11px;">Real-time threat view</p>
                    </div>
                </div>
            </div>

            <!-- Row 2: Controls Toolbar -->
            <div class="row g-2 align-items-center">
                <!-- Traffic Scope Dropdown -->
                <div class="col-6 col-md-auto">
                    <select id="sec-traffic-scope-select" class="form-select form-select-sm border-slate-200 fw-semibold text-dark shadow-sm" style="font-size: 11.5px;">
                        <option value="all" {{ ($trafficType ?? 'all') === 'all' ? 'selected' : '' }}>All Traffic</option>
                        <option value="visitors" {{ ($trafficType ?? '') === 'visitors' ? 'selected' : '' }}>Public Visitors</option>
                        <option value="staff" {{ ($trafficType ?? '') === 'staff' ? 'selected' : '' }}>Internal Staff</option>
                    </select>
                </div>

                <!-- Mobile Period Select (Visible on Mobile only) -->
                <div class="col-6 d-md-none">
                    <select id="sec-period-select-mobile" class="form-select form-select-sm border-slate-200 fw-semibold text-dark shadow-sm" style="font-size: 11.5px;">
                        <option value="today" {{ $filterPeriod === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ $filterPeriod === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="7days" {{ $filterPeriod === '7days' ? 'selected' : '' }}>7 Days</option>
                        <option value="30days" {{ $filterPeriod === '30days' ? 'selected' : '' }}>30 Days</option>
                        <option value="this_month" {{ $filterPeriod === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="this_year" {{ $filterPeriod === 'this_year' ? 'selected' : '' }}>This Year</option>
                        <option value="custom" {{ $filterPeriod === 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
                </div>

                <!-- Desktop Segmented Buttons (Hidden on Mobile) -->
                <div class="col-auto d-none d-md-inline-flex">
                    <div class="btn-group btn-group-sm bg-light p-1 rounded-3 sec-pill-group shadow-sm border border-slate-200" role="group" id="sec-period-btn-group">
                        <button type="button" class="btn sec-filter-btn {{ $filterPeriod === 'today' ? 'active' : '' }}" data-period="today">Today</button>
                        <button type="button" class="btn sec-filter-btn {{ $filterPeriod === 'yesterday' ? 'active' : '' }}" data-period="yesterday">Yesterday</button>
                        <button type="button" class="btn sec-filter-btn {{ $filterPeriod === '7days' ? 'active' : '' }}" data-period="7days">7D</button>
                        <button type="button" class="btn sec-filter-btn {{ $filterPeriod === '30days' ? 'active' : '' }}" data-period="30days">30D</button>
                        <button type="button" class="btn sec-filter-btn {{ $filterPeriod === 'this_month' ? 'active' : '' }}" data-period="this_month">Month</button>
                        <button type="button" class="btn sec-filter-btn {{ $filterPeriod === 'this_year' ? 'active' : '' }}" data-period="this_year">Year</button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="col-auto ms-auto d-flex gap-1.5">
                    <button type="button" class="btn btn-sm btn-outline-primary border-primary border-opacity-25 shadow-sm px-2.5" data-bs-toggle="modal" data-bs-target="#customDateRangeModal" id="btn-custom-date-modal" title="Custom Range">
                        <i class="fas fa-calendar-alt"></i>
                    </button>
                    <button type="button" class="btn ms-2 btn-sm btn-white border shadow-sm text-secondary px-2.5" id="btn-refresh-dashboard" title="Refresh Live Data">
                        <i class="fas fa-sync-alt" id="refresh-icon"></i>
                    </button>
                </div>
            </div>

            <!-- Row 3: Active Range Status -->
            <div class="mt-2.5 pt-2 border-top border-light-subtle d-flex align-items-center justify-content-between text-muted" style="font-size: 11px;">
                <div class="text-truncate">
                    <i class="fas fa-clock text-secondary me-1"></i>
                    <span>Telemetry: </span>
                    <strong class="text-dark" id="display-date-range">{{ $startDate->format('M d, Y') }} — {{ $endDate->format('M d, Y') }}</strong>
                </div>
                <span id="badge-active-period" class="badge bg-light text-secondary border px-2 py-0.5 text-uppercase font-monospace d-none d-sm-inline-block" style="font-size: 9.5px;">
                    {{ str_replace('_', ' ', $filterPeriod) }}
                </span>
            </div>
        </header>

        <!-- ================= 2. PRIMARY METRICS ================= -->
        <section class="row g-2 g-sm-3 mb-3">
            <!-- Total Requests -->
            <div class="col-6 col-lg-3">
                <div class="sec-card sec-kpi-card p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted fw-bold text-uppercase text-truncate" style="font-size: 10.5px; letter-spacing: 0.04em;">Total Requests</span>
                        <div class="sec-kpi-icon bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20">
                            <i class="fas fa-network-wired"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark mb-1 tracking-tight sec-kpi-number" id="m-total-requests">{{ number_format($metrics['total_requests']) }}</h3>
                    
                </div>
            </div>

            <!-- Unique IPs -->
            <div class="col-6 col-lg-3">
                <div class="sec-card sec-kpi-card p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted fw-bold text-uppercase text-truncate" style="font-size: 10.5px; letter-spacing: 0.04em;">Unique IPs</span>
                        <div class="sec-kpi-icon bg-info bg-opacity-10 text-info border border-info border-opacity-20">
                            <i class="fas fa-globe"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark mb-1 tracking-tight sec-kpi-number" id="m-unique-ips">{{ number_format($metrics['total_unique_ips']) }}</h3>
                    
                </div>
            </div>

            <!-- Unique Visitors -->
            <div class="col-6 col-lg-3">
                <div class="sec-card sec-kpi-card p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted fw-bold text-uppercase text-truncate" style="font-size: 10.5px; letter-spacing: 0.04em;">Visitors</span>
                        <div class="sec-kpi-icon bg-success bg-opacity-10 text-success border border-success border-opacity-20">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark mb-1 tracking-tight sec-kpi-number" id="m-unique-visitors">{{ number_format($metrics['total_unique_visitors']) }}</h3>
                   
                </div>
            </div>

            <!-- Active Alerts -->
            <div class="col-6 col-lg-3">
                <div class="sec-card sec-kpi-card p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted fw-bold text-uppercase text-truncate" style="font-size: 10.5px; letter-spacing: 0.04em;">Active Alerts</span>
                        <div class="sec-kpi-icon border {{ $metrics['active_security_alerts'] > 0 ? 'bg-danger bg-opacity-10 text-danger border-danger border-opacity-25' : 'bg-light text-secondary border-slate-200' }}">
                            <i class="fas fa-bell"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1 tracking-tight sec-kpi-number {{ $metrics['active_security_alerts'] > 0 ? 'text-danger' : 'text-dark' }}" id="m-active-alerts">
                        {{ number_format($metrics['active_security_alerts']) }}
                    </h3>
                    
                </div>
            </div>
        </section>

        <!-- ================= 3. SECONDARY INDICATORS ================= -->
        <section class="row g-2 mb-3">
            <div class="col-4 col-md-2">
                <div class="sec-card p-2 p-sm-2.5 h-100 text-center text-sm-start">
                    <span class="fw-bold text-muted text-uppercase d-block text-truncate" style="font-size: 9.5px;">Blocked IPs</span>
                    <h5 class="fw-bold text-danger mb-0 tracking-tight" id="m-blocked-ips">{{ number_format($metrics['total_blocked_ips']) }}</h5>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="sec-card p-2 p-sm-2.5 h-100 text-center text-sm-start">
                    <span class="fw-bold text-muted text-uppercase d-block text-truncate" style="font-size: 9.5px;">Blocked CIDR</span>
                    <h5 class="fw-bold text-danger mb-0 tracking-tight" id="m-blocked-networks">{{ number_format($metrics['total_blocked_networks']) }}</h5>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="sec-card p-2 p-sm-2.5 h-100 text-center text-sm-start">
                    <span class="fw-bold text-muted text-uppercase d-block text-truncate" style="font-size: 9.5px;">Flagged IPs</span>
                    <h5 class="fw-bold text-warning mb-0 tracking-tight" id="m-suspicious-ips">{{ number_format($metrics['total_suspicious_ips']) }}</h5>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="sec-card p-2 p-sm-2.5 h-100 text-center text-sm-start">
                    <span class="fw-bold text-muted text-uppercase d-block text-truncate" style="font-size: 9.5px;">Spam Actions</span>
                    <h5 class="fw-bold text-warning mb-0 tracking-tight" id="m-spam-attempts">{{ number_format($metrics['total_spam_attempts']) }}</h5>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="sec-card p-2 p-sm-2.5 h-100 text-center text-sm-start">
                    <span class="fw-bold text-muted text-uppercase d-block text-truncate" style="font-size: 9.5px;">Order Fraud</span>
                    <h5 class="fw-bold text-danger mb-0 tracking-tight" id="m-suspicious-orders">{{ number_format($metrics['total_suspicious_orders']) }}</h5>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="sec-card p-2 p-sm-2.5 h-100 text-center text-sm-start">
                    <span class="fw-bold text-muted text-uppercase d-block text-truncate" style="font-size: 9.5px;">Brute Force</span>
                    <h5 class="fw-bold text-danger mb-0 tracking-tight" id="m-brute-force">{{ number_format($metrics['brute_force_attempts']) }}</h5>
                </div>
            </div>
        </section>

        <!-- ================= 4. TRAFFIC CHART (FIXED LEGEND) ================= -->
        <section class="sec-card p-3 mb-3">
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-1.5 pb-2.5 border-bottom border-light-subtle">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13px;">
                    <i class="fas fa-chart-line text-primary"></i> Traffic &amp; Threat Velocity
                </h6>
                <!-- Fixed Non-colliding Legend -->
                <div class="d-flex align-items-center gap-3" style="font-size: 11px;">
                    <span class="d-inline-flex align-items-center">
                        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #2563eb; margin-right: 5px;"></span>
                        <span class="text-secondary fw-semibold">Clean</span>
                    </span>
                    <span class="d-inline-flex align-items-center">
                        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #dc2626; margin-right: 5px;"></span>
                        <span class="text-secondary fw-semibold">Blocked</span>
                    </span>
                    <span class="d-inline-flex align-items-center">
                        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #f59e0b; margin-right: 5px;"></span>
                        <span class="text-secondary fw-semibold">Flagged</span>
                    </span>
                </div>
            </div>
            <div class="pt-2" style="position: relative; height: 230px; width: 100%;">
                <canvas id="securityTrafficChart"></canvas>
            </div>
        </section>

        <!-- ================= 5. TARGETED ROUTES & ACTIVE IPS ================= -->
        <section class="row g-3 mb-3">
            <!-- Targeted Routes -->
            <div class="col-12 col-lg-6">
                <div class="sec-card h-100 overflow-hidden d-flex flex-column justify-content-between">
                    <div class="p-3 border-bottom border-light-subtle d-flex align-items-center justify-content-between bg-white">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13px;">
                            <i class="fas fa-bullseye text-danger"></i> Targeted Route Endpoints
                        </h6>
                        <a href="{{ route('admin.security.urls.index') }}" class="btn btn-sm btn-link text-primary text-decoration-none fw-semibold p-0 flex-shrink-0" style="font-size: 11.5px;">View All &rarr;</a>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table sec-table table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Endpoint</th>
                                    <th>Method</th>
                                    <th class="text-center">Total Hits</th>
                                    <th class="text-center">Blocked</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mostAttackedUrls as $url)
                                    <tr>
                                        <td class="font-monospace fw-semibold text-dark text-truncate" style="max-width: 200px;" title="{{ $url->url_path }}">
                                            {{ $url->url_path }}
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border px-2 py-0.5 font-monospace" style="font-size: 9.5px;">
                                                {{ $url->request_method }}
                                            </span>
                                        </td>
                                        <td class="text-center fw-bold text-dark">{{ number_format($url->total_requests) }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-0.5">
                                                {{ number_format($url->blocked_requests) }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('admin.security.urls.show', $url->id) }}" class="btn btn-sm btn-light border py-0 px-2 text-secondary" style="font-size: 11px;" title="Inspect Route">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 text-center text-muted small">No malicious route attacks recorded.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View (Generous Padding & Zero Squishing) -->
                    <div class="d-md-none p-3">
                        @forelse($mostAttackedUrls as $url)
                            <div class="sec-mobile-card-item">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2 overflow-hidden me-2" style="max-width: 82%;">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border font-monospace px-1.5 py-0.5 flex-shrink-0" style="font-size: 9px;">
                                            {{ strtoupper($url->request_method ?? 'GET') }}
                                        </span>
                                        <span class="font-monospace text-dark fw-bold text-truncate" style="font-size: 12px;" title="{{ $url->url_path }}">
                                            {{ $url->url_path }}
                                        </span>
                                    </div>
                                    <a href="{{ route('admin.security.urls.show', $url->id) }}" class="btn btn-sm btn-light border py-1 px-2 text-primary flex-shrink-0 shadow-sm" style="font-size: 11px;">
                                        <i class="fas fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                                <div class="d-flex align-items-center justify-content-between pt-1.5 border-top border-light-subtle" style="font-size: 11px;">
                                    <span class="text-muted">Total Hits: <strong class="text-dark font-monospace">{{ number_format($url->total_requests) }}</strong></span>
                                    <span class="text-danger fw-semibold">Blocked: <strong class="font-monospace">{{ number_format($url->blocked_requests) }}</strong></span>
                                </div>
                            </div>
                        @empty
                            <p class="py-3 text-center text-muted small mb-0">No attacks recorded.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Active IP Telemetry -->
            <div class="col-12 col-lg-6">
                <div class="sec-card h-100 overflow-hidden d-flex flex-column justify-content-between">
                    <div class="p-3 border-bottom border-light-subtle d-flex align-items-center justify-content-between bg-white">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13px;">
                            <i class="fas fa-tower-broadcast text-primary"></i> Active IP Telemetry
                        </h6>
                        <a href="{{ route('admin.security.ips.index') }}" class="btn btn-sm btn-link text-primary text-decoration-none fw-semibold p-0 flex-shrink-0" style="font-size: 11.5px;">View All &rarr;</a>
                    </div>

                    <!-- Desktop Table -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table sec-table table-hover mb-0 align-middle" id="table-most-active-ips">
                            <thead>
                                <tr>
                                    <th>Client IP</th>
                                    <th>Country</th>
                                    <th>Client Spec</th>
                                    <th class="text-center">Requests</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody id="table-most-active-ips-body">
                                @forelse($mostActiveIps as $ipRow)
                                    <tr>
                                        <td class="font-monospace fw-bold text-dark">
                                            <a href="{{ route('admin.security.ips.show', $ipRow->ip_address) }}" class="text-decoration-none text-dark hover-primary">
                                                {{ $ipRow->ip_address }}
                                            </a>
                                            @if($ipRow->is_blocked ?? false)
                                                <span class="badge bg-danger px-1 py-0 ms-1" style="font-size: 8px;">Blocked</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border px-2 py-0.5" style="font-size: 10px;">
                                                {{ $ipRow->country ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="text-muted small" style="font-size: 11px;">
                                            {{ $ipRow->device_type ?? 'Desktop' }} ({{ $ipRow->platform ?? 'OS' }})
                                        </td>
                                        <td class="text-center fw-bold text-dark">{{ number_format($ipRow->total_requests) }}</td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('admin.security.ips.show', $ipRow->ip_address) }}" class="btn btn-sm btn-light border py-0 px-2 text-primary" style="font-size: 11px;" title="Inspect IP">
                                                <i class="fas fa-search"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 text-center text-muted small">No IP traffic data available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View (Generous Padding & Zero Squishing) -->
                    <div class="d-md-none p-3" id="mobile-most-active-ips-list">
                        @forelse($mostActiveIps as $ipRow)
                            <div class="sec-mobile-card-item">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2 text-truncate me-2">
                                        <a href="{{ route('admin.security.ips.show', $ipRow->ip_address) }}" class="font-monospace fw-bold text-dark text-decoration-none" style="font-size: 12.5px;">
                                            {{ $ipRow->ip_address }}
                                        </a>
                                        @if($ipRow->is_blocked ?? false)
                                            <span class="badge bg-danger px-1.5 py-0.5 rounded-pill" style="font-size: 8px;">Blocked</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('admin.security.ips.show', $ipRow->ip_address) }}" class="btn btn-sm btn-light border py-1 px-2 text-primary flex-shrink-0 shadow-sm" style="font-size: 11px;" title="Inspect IP">
                                        <i class="fas fa-search"></i>
                                    </a>
                                </div>
                                <div class="d-flex align-items-center justify-content-between pt-1.5 border-top border-light-subtle text-muted" style="font-size: 11px;">
                                    <span><i class="fas fa-globe text-secondary me-1"></i>{{ $ipRow->country ?: 'Unknown' }}</span>
                                    <span>Requests: <strong class="text-dark font-monospace">{{ number_format($ipRow->total_requests) }}</strong></span>
                                </div>
                            </div>
                        @empty
                            <p class="py-3 text-center text-muted small mb-0">No IP traffic data available.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= 6. BLOCKED IPS & SECURITY ALERTS ================= -->
        <section class="row g-3 mb-4">
            <!-- Blocked IPs -->
            <div class="col-12 col-lg-6">
                <div class="sec-card h-100 overflow-hidden d-flex flex-column justify-content-between">
                    <div class="p-3 border-bottom border-light-subtle d-flex align-items-center justify-content-between bg-white">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13px;">
                            <i class="fas fa-shield-slash text-danger"></i> Firewall Enforced Blocks
                        </h6>
                        <a href="{{ route('admin.security.blocking.ips') }}" class="btn btn-sm btn-link text-primary text-decoration-none fw-semibold p-0 flex-shrink-0" style="font-size: 11.5px;">Manage &rarr;</a>
                    </div>

                    <!-- Desktop View -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table sec-table table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Blocked IP</th>
                                    <th>Reason</th>
                                    <th>Type</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentlyBlockedIps as $bIp)
                                    <tr>
                                        <td class="font-monospace fw-bold text-danger">{{ $bIp->ip_address }}</td>
                                        <td class="text-muted small text-truncate" style="max-width: 170px;" title="{{ $bIp->block_reason }}">{{ $bIp->block_reason }}</td>
                                        <td>
                                            <span class="badge {{ $bIp->block_type === 'permanent' ? 'bg-danger' : 'bg-warning text-dark' }} px-1.5 py-0.5 text-uppercase" style="font-size: 9px;">
                                                {{ $bIp->block_type }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 btn-unblock-ip" data-ip="{{ $bIp->ip_address }}" style="font-size: 11px;">
                                                Unblock
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-muted small">No IPs are currently restricted.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="d-md-none p-3">
                        @forelse($recentlyBlockedIps as $bIp)
                            <div class="sec-mobile-card-item d-flex align-items-center justify-content-between gap-2">
                                <div class="min-w-0" style="max-width: 75%;">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <span class="font-monospace fw-bold text-danger" style="font-size: 12px;">{{ $bIp->ip_address }}</span>
                                        <span class="badge {{ $bIp->block_type === 'permanent' ? 'bg-danger' : 'bg-warning text-dark' }}" style="font-size: 8px;">
                                            {{ $bIp->block_type }}
                                        </span>
                                    </div>
                                    <p class="text-muted small text-truncate mb-0 mt-0.5" style="font-size: 11px;">{{ $bIp->block_reason }}</p>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2.5 btn-unblock-ip flex-shrink-0 shadow-sm" data-ip="{{ $bIp->ip_address }}" style="font-size: 11px;">
                                    Unblock
                                </button>
                            </div>
                        @empty
                            <p class="py-3 text-center text-muted small mb-0">No IPs are currently restricted.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Security Alerts Feed (Cleaned Scannable Items) -->
            <div class="col-12 col-lg-6">
                <div class="sec-card h-100 overflow-hidden d-flex flex-column justify-content-between">
                    <div class="p-3 border-bottom border-light-subtle d-flex align-items-center justify-content-between bg-white">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13px;">
                            <i class="fas fa-bell text-warning"></i> Security Incident Feed
                        </h6>
                        <a href="{{ route('admin.security.alerts.index') }}" class="btn btn-sm btn-link text-primary text-decoration-none fw-semibold p-0 flex-shrink-0" style="font-size: 11.5px;">Alert Center &rarr;</a>
                    </div>

                    <div class="p-3">
                        <div class="list-group list-group-flush">
                            @forelse($recentAlerts as $alert)
                                @php
                                    $badgeStyle = match($alert->risk_level) {
                                        'critical' => 'bg-danger text-white',
                                        'high'     => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
                                        'medium'   => 'bg-warning bg-opacity-10 text-warning-emphasis border border-warning border-opacity-25',
                                        default    => 'bg-info bg-opacity-10 text-info border border-info border-opacity-25',
                                    };

                                    // Clean redundant string prefixes
                                    $cleanReason = preg_replace('/^IP\s+[^\s]+\s+has been blocked\s*\([^)]*\)\s*for reason:\s*/i', '', $alert->message);
                                @endphp
                                <div class="list-group-item px-1 py-2.5 border-bottom border-light-subtle d-flex align-items-center justify-content-between gap-3">
                                    <div class="overflow-hidden min-w-0" style="max-width: 85%;">
                                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                            <span class="badge {{ $badgeStyle }} text-uppercase px-2 py-0.5 flex-shrink-0" style="font-size: 9px; letter-spacing: 0.04em;">
                                                {{ $alert->risk_level }}
                                            </span>
                                            <span class="fw-bold text-dark text-truncate" style="font-size: 12px;">
                                                {{ $alert->title }}
                                            </span>
                                        </div>
                                        <p class="text-muted small mb-1 text-truncate" style="font-size: 11.5px;" title="{{ $cleanReason }}">
                                            {{ $cleanReason }}
                                        </p>
                                        <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 10px;">
                                            <span><i class="fas fa-clock text-secondary me-1"></i>{{ $alert->created_at ? $alert->created_at->diffForHumans() : 'N/A' }}</span>
                                            @if($alert->ip_address)
                                                <span>&bull;</span>
                                                <span class="font-monospace text-dark fw-bold">{{ $alert->ip_address }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if(!$alert->is_read)
                                        <button type="button" class="btn btn-sm btn-light border text-muted hover-success rounded-circle p-1 flex-shrink-0 btn-mark-read shadow-sm" data-id="{{ $alert->id }}" title="Acknowledge Alert" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                            <i class="far fa-circle-check text-success fs-6"></i>
                                        </button>
                                    @endif
                                </div>
                            @empty
                                <div class="py-4 text-center text-muted small">No security incidents detected.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
</div>

<!-- ================= 7. CUSTOM DATE RANGE MODAL ================= -->
<div class="modal fade" id="customDateRangeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header py-2.5 px-3 bg-light border-bottom border-light-subtle">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 13px;">
                    <i class="fas fa-calendar-alt text-primary"></i> Custom Range
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 10px;"></button>
            </div>
            <form id="customDateForm">
                <div class="modal-body p-3">
                    <div class="mb-2.5">
                        <label class="form-label small fw-semibold text-secondary mb-1">Start Date</label>
                        <input type="date" class="form-control form-control-sm" id="custom_start_date" required value="{{ $startDate->format('Y-m-d') }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-secondary mb-1">End Date</label>
                        <input type="date" class="form-control form-control-sm" id="custom_end_date" required value="{{ $endDate->format('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light border-top border-light-subtle">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3 shadow-sm">Apply</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= 8. LIVE JAVASCRIPT AJAX HANDLER ================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function() {
    let trafficChart = null;

    function renderChart(labels, totalData, blockedData, spamData) {
        const chartCanvas = document.getElementById('securityTrafficChart');
        if (!chartCanvas || typeof Chart === 'undefined') return;

        if (trafficChart) {
            trafficChart.destroy();
        }

        trafficChart = new Chart(chartCanvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Clean',
                        data: totalData,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.05)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 1.5,
                    },
                    {
                        label: 'Blocked',
                        data: blockedData,
                        borderColor: '#dc2626',
                        backgroundColor: 'rgba(220, 38, 38, 0.08)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 1.5,
                    },
                    {
                        label: 'Flagged',
                        data: spamData,
                        borderColor: '#f59e0b',
                        backgroundColor: 'transparent',
                        borderDash: [4, 4],
                        tension: 0.35,
                        borderWidth: 1.5,
                        pointRadius: 1.5,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        padding: 8,
                        cornerRadius: 6,
                        backgroundColor: '#0f172a'
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 9 }, color: '#94a3b8' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(241, 245, 249, 1)' },
                        ticks: { font: { size: 9 }, color: '#94a3b8', precision: 0 }
                    }
                }
            }
        });
    }

    function fetchSecurityData(params = {}) {
        const refreshIcon = document.getElementById('refresh-icon');
        if (refreshIcon) refreshIcon.classList.add('fa-spin');

        const activePeriodBtn = document.querySelector('.sec-filter-btn.active');
        const mobilePeriodSelect = document.getElementById('sec-period-select-mobile');
        const trafficScopeSelect = document.getElementById('sec-traffic-scope-select');

        if (!params.period) {
            if (activePeriodBtn) params.period = activePeriodBtn.getAttribute('data-period');
            else if (mobilePeriodSelect) params.period = mobilePeriodSelect.value;
        }

        if (!params.traffic_type && trafficScopeSelect) {
            params.traffic_type = trafficScopeSelect.value;
        }

        const queryStr = new URLSearchParams(params).toString();
        const url = `{{ route('admin.security.dashboard') }}?${queryStr}`;

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(res => {
            if (refreshIcon) refreshIcon.classList.remove('fa-spin');
            if (!res.success) return;

            const m = res.metrics;
            document.getElementById('m-total-requests').innerText = Number(m.total_requests).toLocaleString();
            document.getElementById('m-unique-ips').innerText = Number(m.total_unique_ips).toLocaleString();
            document.getElementById('m-unique-visitors').innerText = Number(m.total_unique_visitors).toLocaleString();
            document.getElementById('m-active-alerts').innerText = Number(m.active_security_alerts).toLocaleString();
            document.getElementById('m-blocked-ips').innerText = Number(m.total_blocked_ips).toLocaleString();
            document.getElementById('m-blocked-networks').innerText = Number(m.total_blocked_networks).toLocaleString();
            document.getElementById('m-suspicious-ips').innerText = Number(m.total_suspicious_ips).toLocaleString();
            document.getElementById('m-spam-attempts').innerText = Number(m.total_spam_attempts).toLocaleString();
            document.getElementById('m-suspicious-orders').innerText = Number(m.total_suspicious_orders).toLocaleString();
            document.getElementById('m-brute-force').innerText = Number(m.brute_force_attempts).toLocaleString();

            if (res.date_range_text) {
                document.getElementById('display-date-range').innerText = res.date_range_text;
            }
            if (res.period) {
                const badge = document.getElementById('badge-active-period');
                if (badge) badge.innerText = res.period.replace(/_/g, ' ').toUpperCase();
                if (mobilePeriodSelect) mobilePeriodSelect.value = res.period;
            }

            if (res.chart_data) {
                renderChart(res.chart_data.labels, res.chart_data.total_req, res.chart_data.blocked_req, res.chart_data.spam_req);
            }

            // Sync Desktop & Mobile Active IPs Lists
            if (res.most_active_ips) {
                const tbody = document.getElementById('table-most-active-ips-body');
                const mobileList = document.getElementById('mobile-most-active-ips-list');

                if (res.most_active_ips.length === 0) {
                    if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="py-4 text-center text-muted small">No IP traffic data available.</td></tr>';
                    if (mobileList) mobileList.innerHTML = '<p class="py-3 text-center text-muted small mb-0">No IP traffic data available.</p>';
                } else {
                    let tableHtml = '';
                    let mobileHtml = '';

                    res.most_active_ips.forEach(ipRow => {
                        const showUrl = `{{ url('/admin/security/ips/show') }}/${ipRow.ip_address}`;
                        const blockedBadge = ipRow.is_blocked ? '<span class="badge bg-danger px-1 py-0 ms-1" style="font-size: 8px;">Blocked</span>' : '';

                        // Desktop Table Row
                        tableHtml += `
                            <tr>
                                <td class="font-monospace fw-bold text-dark">
                                    <a href="${showUrl}" class="text-decoration-none text-dark hover-primary">${ipRow.ip_address}</a>
                                    ${blockedBadge}
                                </td>
                                <td><span class="badge bg-light text-dark border px-2 py-0.5" style="font-size: 10px;">${ipRow.country || 'N/A'}</span></td>
                                <td class="text-muted small" style="font-size: 11px;">${ipRow.device_type || 'Desktop'} (${ipRow.platform || 'OS'})</td>
                                <td class="text-center fw-bold text-dark">${Number(ipRow.total_requests).toLocaleString()}</td>
                                <td class="text-end pe-3">
                                    <a href="${showUrl}" class="btn btn-sm btn-light border py-0 px-2 text-primary" style="font-size: 11px;"><i class="fas fa-search"></i></a>
                                </td>
                            </tr>
                        `;

                        // Mobile Clean Card Item with Real Bootstrap Padding
                        mobileHtml += `
                            <div class="sec-mobile-card-item">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2 text-truncate me-2">
                                        <a href="${showUrl}" class="font-monospace fw-bold text-dark text-decoration-none" style="font-size: 12.5px;">${ipRow.ip_address}</a>
                                        ${ipRow.is_blocked ? '<span class="badge bg-danger px-1.5 py-0.5 rounded-pill" style="font-size: 8px;">Blocked</span>' : ''}
                                    </div>
                                    <a href="${showUrl}" class="btn btn-sm btn-light border py-1 px-2 text-primary flex-shrink-0 shadow-sm" style="font-size: 11px;"><i class="fas fa-search"></i></a>
                                </div>
                                <div class="d-flex align-items-center justify-content-between pt-1.5 border-top border-light-subtle text-muted" style="font-size: 11px;">
                                    <span><i class="fas fa-globe text-secondary me-1"></i>${ipRow.country || 'N/A'}</span>
                                    <span>Requests: <strong class="text-dark font-monospace">${Number(ipRow.total_requests).toLocaleString()}</strong></span>
                                </div>
                            </div>
                        `;
                    });

                    if (tbody) tbody.innerHTML = tableHtml;
                    if (mobileList) mobileList.innerHTML = mobileHtml;
                }
            }
        })
        .catch(err => {
            if (refreshIcon) refreshIcon.classList.remove('fa-spin');
            console.error('Security dashboard fetch error:', err);
        });
    }

    function initSecurityDashboard() {
        const initialLabels = @json($chartData['labels'] ?? []);
        const initialTotal = @json($chartData['total_req'] ?? []);
        const initialBlocked = @json($chartData['blocked_req'] ?? []);
        const initialSpam = @json($chartData['spam_req'] ?? []);
        renderChart(initialLabels, initialTotal, initialBlocked, initialSpam);

        document.querySelectorAll('.sec-filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.sec-filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const period = this.getAttribute('data-period');
                const mobileSelect = document.getElementById('sec-period-select-mobile');
                if (mobileSelect) mobileSelect.value = period;

                fetchSecurityData({ period: period });
            });
        });

        const mobilePeriodSelect = document.getElementById('sec-period-select-mobile');
        if (mobilePeriodSelect) {
            mobilePeriodSelect.addEventListener('change', function() {
                const period = this.value;
                if (period === 'custom') {
                    const modalEl = document.getElementById('customDateRangeModal');
                    if (modalEl && typeof bootstrap !== 'undefined') {
                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        modal.show();
                    }
                } else {
                    document.querySelectorAll('.sec-filter-btn').forEach(b => {
                        if (b.getAttribute('data-period') === period) {
                            b.classList.add('active');
                        } else {
                            b.classList.remove('active');
                        }
                    });
                    fetchSecurityData({ period: period });
                }
            });
        }

        const trafficScopeSelect = document.getElementById('sec-traffic-scope-select');
        if (trafficScopeSelect) {
            trafficScopeSelect.addEventListener('change', function() {
                fetchSecurityData({ traffic_type: this.value });
            });
        }

        const customForm = document.getElementById('customDateForm');
        if (customForm) {
            customForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const start = document.getElementById('custom_start_date').value;
                const end = document.getElementById('custom_end_date').value;

                const modalEl = document.getElementById('customDateRangeModal');
                if (modalEl && typeof bootstrap !== 'undefined') {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                }

                fetchSecurityData({ period: 'custom', start_date: start, end_date: end });
            });
        }

        const refreshBtn = document.getElementById('btn-refresh-dashboard');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => fetchSecurityData());
        }

        document.addEventListener('click', function(e) {
            const unblockBtn = e.target.closest('.btn-unblock-ip');
            if (unblockBtn) {
                const ip = unblockBtn.getAttribute('data-ip');
                if (confirm(`Unblock IP address: ${ip}?`)) {
                    unblockBtn.disabled = true;
                    unblockBtn.innerText = '...';

                    fetch(`{{ route('admin.security.ips.unblock') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ ip_address: ip, note: 'Quick unblock from dashboard' })
                    })
                    .then(r => r.json())
                    .then(res => {
                        alert(res.message);
                        if (res.success) {
                            fetchSecurityData();
                        } else {
                            unblockBtn.disabled = false;
                            unblockBtn.innerText = 'Unblock';
                        }
                    })
                    .catch(() => {
                        unblockBtn.disabled = false;
                        unblockBtn.innerText = 'Unblock';
                    });
                }
            }

            const markReadBtn = e.target.closest('.btn-mark-read');
            if (markReadBtn) {
                const id = markReadBtn.getAttribute('data-id');
                fetch(`{{ url('/admin/security/alerts/mark-read') }}/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        markReadBtn.closest('.list-group-item').remove();
                        const activeCountEl = document.getElementById('m-active-alerts');
                        if (activeCountEl) {
                            let cur = parseInt(activeCountEl.innerText.replace(/,/g, '')) || 0;
                            activeCountEl.innerText = Math.max(0, cur - 1).toLocaleString();
                        }
                    }
                });
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSecurityDashboard);
    } else {
        initSecurityDashboard();
    }
})();
</script>
@endsection