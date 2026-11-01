@extends('layouts.admin')

@section('title', 'IP Directory - ' . config('app.name'))

@php
    $totalIps = $ips->total();
    $pageItems = collect($ips->items());
    $highRiskCount = $pageItems->filter(fn($i) => (int)($i->risk_score ?? $i->max_risk ?? 0) >= 75)->count();
    $blockedCount = $pageItems->filter(fn($i) => !empty($i->is_blocked))->count();
    $failedAuthCount = $pageItems->sum(fn($i) => (int)($i->failed_logins_count ?? $i->failed_logins ?? 0));
    $activeOrdersCount = $pageItems->sum(fn($i) => (int)($i->orders_count ?? 0));
@endphp

@section('admin_content')
<!-- Font Awesome 6.5.1 CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Google Fonts: Plus Jakarta Sans & Inter -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* Professional Enterprise Typography */
    #security-ips-wrapper,
    .sec-modal-scope {
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        letter-spacing: -0.01em;
        color: #1e293b;
    }

    /* FontAwesome Icon Protection */
    #security-ips-wrapper i,
    #security-ips-wrapper .fa,
    #security-ips-wrapper .fas,
    #security-ips-wrapper .far,
    #security-ips-wrapper .fab,
    #security-ips-wrapper .fa-solid,
    #security-ips-wrapper .fa-regular,
    .sec-modal-scope i,
    .sec-modal-scope .fa,
    .sec-modal-scope .fa-solid {
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome" !important;
        font-style: normal;
        font-weight: 900;
        line-height: 1;
        letter-spacing: normal !important;
        display: inline-block;
    }
    #security-ips-wrapper .far,
    .sec-modal-scope .far {
        font-weight: 400 !important;
    }

    #security-ips-wrapper {
        background-color: #f8fafc;
        min-height: 100vh;
    }

    /* Card Theme */
    .sec-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        transition: box-shadow 0.15s ease, border-color 0.15s ease;
    }
    .sec-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
    }

    /* Tabular Numeric Data */
    .bn-digit,
    .font-tabular {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
    }

    /* Header Icon */
    .sec-header-icon-box {
        width: 38px !important;
        height: 38px !important;
        min-width: 38px !important;
        border-radius: 8px !important;
        background-color: #eff6ff !important;
        border: 1px solid #bfdbfe !important;
        color: #2563eb !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
    }
    .sec-header-icon-box i {
        font-size: 15px !important;
    }

    /* Page Titles */
    .sec-page-title {
        font-size: 17.5px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        line-height: 1.25 !important;
        margin: 0 !important;
    }
    .sec-page-subtitle {
        font-size: 11.5px !important;
        color: #64748b !important;
        margin: 0 !important;
        line-height: 1.4 !important;
    }

    /* Protected Badge */
    .badge-status-protected {
        background-color: #ecfdf5 !important;
        color: #059669 !important;
        border: 1px solid #a7f3d0 !important;
        font-size: 10.5px !important;
        font-weight: 700 !important;
        padding: 3px 9px !important;
        border-radius: 20px !important;
        display: inline-flex;
        align-items: center;
        letter-spacing: 0.01em;
    }

    /* Metric Cards */
    .metric-card {
        padding: 12px 14px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }
    .stat-label-title {
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #64748b;
        line-height: 1.2;
    }
    .stat-val-num {
        font-size: 20px;
        font-weight: 800;
        line-height: 1.25;
        letter-spacing: -0.02em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #0f172a;
    }
    .stat-sub-text {
        font-size: 11px;
        font-weight: 600;
        margin-top: 2px;
    }
    .metric-icon-square {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
        border-radius: 8px !important;
    }

    /* Metric Icon Colors */
    .icon-box-blue   { background-color: #eff6ff !important; border: 1px solid #bfdbfe !important; color: #2563eb !important; }
    .icon-box-teal   { background-color: #f0fdfa !important; border: 1px solid #99f6e4 !important; color: #0d9488 !important; }
    .icon-box-green  { background-color: #f0fdf4 !important; border: 1px solid #bbf7d0 !important; color: #16a34a !important; }
    .icon-box-red    { background-color: #fef2f2 !important; border: 1px solid #fecaca !important; color: #dc2626 !important; }

    /* Sub-Metric Cards */
    .sub-metric-pill-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 6px !important;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
    }
    .sub-metric-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.02em;
    }
    .sub-metric-value {
        font-size: 14px;
        font-weight: 800;
        line-height: 1;
    }

    /* Form Controls */
    #security-ips-wrapper .form-control,
    #security-ips-wrapper .form-select,
    .sec-modal-scope .form-control,
    .sec-modal-scope .form-select {
        min-height: 34px;
        font-size: 12px;
        font-weight: 500;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        border-radius: 5px !important;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #security-ips-wrapper .form-control:focus,
    #security-ips-wrapper .form-select:focus,
    .sec-modal-scope .form-control:focus,
    .sec-modal-scope .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Buttons */
    .btn-compact {
        font-size: 11.5px !important;
        padding: 6px 14px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        border-radius: 5px !important;
        transition: all 0.15s ease;
    }

    .btn-primary {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
    }
    .btn-primary:hover {
        background-color: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
        color: #ffffff !important;
    }

    .btn-outline-secondary {
        background-color: #ffffff !important;
        border-color: #cbd5e1 !important;
        color: #334155 !important;
    }
    .btn-outline-secondary:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }

    /* Action Buttons */
    .sec-action-btn {
        width: 30px !important;
        height: 30px !important;
        min-width: 30px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 5px !important;
        padding: 0 !important;
        font-size: 11.5px !important;
        transition: all 0.15s ease !important;
        cursor: pointer;
    }

    .sec-action-btn-view {
        background-color: #eff6ff !important;
        border: 1px solid #bfdbfe !important;
        color: #2563eb !important;
    }
    .sec-action-btn-view:hover {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
    }

    .sec-action-btn-block {
        background-color: #fef2f2 !important;
        border: 1px solid #fecaca !important;
        color: #dc2626 !important;
    }
    .sec-action-btn-block:hover {
        background-color: #dc2626 !important;
        border-color: #dc2626 !important;
        color: #ffffff !important;
    }

    .sec-action-btn-unblock {
        background-color: #f0fdf4 !important;
        border: 1px solid #bbf7d0 !important;
        color: #16a34a !important;
    }
    .sec-action-btn-unblock:hover {
        background-color: #16a34a !important;
        border-color: #16a34a !important;
        color: #ffffff !important;
    }

    /* IP Address Tags */
    .ip-link-text {
        font-weight: 700;
        font-size: 12.5px;
        color: #0f172a;
        font-variant-numeric: tabular-nums;
        transition: color 0.15s ease;
    }
    .ip-link-text:hover {
        color: #2563eb !important;
    }
    .protocol-tag {
        font-size: 9.5px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
        line-height: 1.2;
        letter-spacing: 0.02em;
    }
    .tag-v4 {
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
    }
    .tag-v6 {
        background-color: #eff6ff;
        color: #2563eb;
        border: 1px solid #bfdbfe;
    }
    .badge-blocked-pill {
        background-color: #dc2626;
        color: #ffffff;
        font-size: 9px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 12px;
        line-height: 1.2;
    }

    /* Data Table */
    .sec-table-container {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .sec-table {
        margin-bottom: 0;
        width: 100%;
        border-collapse: collapse;
    }
    .sec-table thead th {
        font-size: 10.5px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        font-weight: 700 !important;
        color: #475569 !important;
        background-color: #f8fafc !important;
        border-bottom: 1.5px solid #e2e8f0 !important;
        padding: 10px 12px !important;
        white-space: nowrap !important;
    }
    .sec-table tbody td {
        padding: 10px 12px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        vertical-align: middle !important;
        font-size: 12px !important;
        white-space: nowrap !important;
        color: #1e293b;
    }
    .sec-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    /* Column Widths */
    .col-ip       { width: 22% !important; min-width: 175px !important; }
    .col-country  { width: 14% !important; min-width: 130px !important; }
    .col-device   { width: 15% !important; min-width: 140px !important; }
    .col-hits     { width: 6% !important; min-width: 65px !important; }
    .col-orders   { width: 6% !important; min-width: 65px !important; }
    .col-forms    { width: 6% !important; min-width: 65px !important; }
    .col-fails    { width: 7% !important; min-width: 75px !important; }
    .col-risk     { width: 7% !important; min-width: 75px !important; }
    .col-activity { width: 10% !important; min-width: 110px !important; }
    .col-actions  { width: 7% !important; min-width: 110px !important; }

    /* Modal */
    .minimal-modal-card {
        background: #ffffff !important;
        border: 1px solid #f1f5f9 !important;
        border-radius: 12px !important;
        box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.14), 0 0 1px 1px rgba(15, 23, 42, 0.04) !important;
        padding: 24px 26px !important;
        text-align: left !important;
    }
    .minimal-modal-title {
        font-size: 16px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        margin-bottom: 6px !important;
        letter-spacing: -0.02em !important;
    }
    .minimal-modal-subtext {
        font-size: 13px !important;
        color: #64748b !important;
        margin-bottom: 22px !important;
        line-height: 1.45 !important;
    }
    .btn-minimal-action {
        background-color: #c93424 !important;
        border: none !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        padding: 7px 22px !important;
        border-radius: 7px !important;
        box-shadow: 0 4px 12px rgba(201, 52, 36, 0.28) !important;
        transition: all 0.15s ease !important;
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .btn-minimal-action:hover {
        background-color: #b02717 !important;
        box-shadow: 0 6px 14px rgba(201, 52, 36, 0.38) !important;
        color: #ffffff !important;
    }
    .btn-minimal-cancel {
        background: transparent !important;
        border: none !important;
        color: #64748b !important;
        font-weight: 500 !important;
        font-size: 13.5px !important;
        padding: 7px 14px !important;
        box-shadow: none !important;
        transition: color 0.15s ease !important;
        cursor: pointer !important;
    }
    .btn-minimal-cancel:hover {
        color: #0f172a !important;
        background: transparent !important;
    }

    /* Scrollbar */
    .sec-table-container::-webkit-scrollbar {
        height: 5px;
    }
    .sec-table-container::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 4px;
    }

    /* Mobile Responsive */
    @media (max-width: 767.98px) {
        .header-top-wrap {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 12px !important;
        }
        .header-action-btns-wrap {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .header-action-btns-wrap .btn {
            width: 100%;
            justify-content: center;
        }
        .metric-card {
            padding: 10px 12px !important;
            min-height: 80px !important;
        }
        .stat-val-num {
            font-size: 17px !important;
        }
        .stat-label-title {
            font-size: 9.5px !important;
        }
        .stat-sub-text {
            font-size: 10px !important;
        }
        .sec-table thead th, .sec-table tbody td {
            padding: 8px 10px !important;
        }
        .minimal-modal-card {
            padding: 20px 20px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-sm-3 px-md-4 py-3" id="security-ips-wrapper">
    <div class="mx-auto" style="max-width: 1600px;">

        <!-- 1. HEADER -->
        <header class="sec-card p-3 mb-3">
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 header-top-wrap">

                <div class="d-flex align-items-center gap-2.5">
                    <span class="sec-header-icon-box shadow-xs">
                        <i class="fas fa-network-wired"></i>
                    </span>
                    <div class="ms-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                            <h4 class="sec-page-title">IP Directory</h4>
                        </div>
                        <p class="sec-page-subtitle mb-0 d-none d-sm-block ms-2">Visitor IP activity and risk data.</p>
                    </div>
                </div>

                <div class="header-action-btns-wrap d-flex align-items-center gap-2">
                    <a href="{{ route('admin.security.ips.export') }}" id="btn-export-ips-csv" class="btn btn-outline-secondary btn-compact shadow-xs bg-white text-dark fw-semibold" title="Export CSV">
                        <i class="fas fa-file-csv text-success me-1"></i>
                        <span>Export CSV</span>
                    </a>

                    <button type="button" class="btn ms-1 btn-primary btn-compact shadow-xs fw-bold px-3" data-bs-toggle="modal" data-bs-target="#modalManualBlockIp">
                        <i class="fas fa-plus me-1"></i>
                        <span>Block IP</span>
                    </button>
                </div>

            </div>
        </header>

        <!-- 2. SUMMARY CARDS -->
        <div class="row g-2 mb-2">

            <div class="col-6 col-xl-3">
                <div class="metric-card">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="stat-label-title text-truncate">Total IPs</span>
                        <div class="metric-icon-square icon-box-blue shadow-xs">
                            <i class="fas fa-network-wired"></i>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <h4 class="stat-val-num bn-digit font-tabular my-0" id="ip-total-counter">
                            {{ number_format($totalIps) }}
                        </h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="metric-card">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="stat-label-title">IP Records</span>
                        <div class="metric-icon-square icon-box-teal shadow-xs">
                            <i class="fas fa-globe"></i>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <h4 class="stat-val-num bn-digit font-tabular my-0">
                            {{ number_format($totalIps) }}
                        </h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="metric-card">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="stat-label-title">Orders</span>
                        <div class="metric-icon-square icon-box-green shadow-xs">
                            <i class="fas fa-bag-shopping"></i>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <h4 class="stat-val-num text-success bn-digit font-tabular my-0">
                            {{ number_format($activeOrdersCount) }}
                        </h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="metric-card">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="stat-label-title">High-Risk IPs</span>
                        <div class="metric-icon-square icon-box-red shadow-xs">
                            <i class="fas fa-bell"></i>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <h4 class="stat-val-num text-danger bn-digit font-tabular my-0">
                            {{ $highRiskCount }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. QUICK STATS -->
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="sub-metric-pill-card">
                    <span class="sub-metric-label">Blocked</span>
                    <strong class="sub-metric-value text-danger bn-digit font-tabular">{{ $blockedCount }}</strong>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="sub-metric-pill-card">
                    <span class="sub-metric-label">High Risk</span>
                    <strong class="sub-metric-value text-warning bn-digit font-tabular">{{ $highRiskCount }}</strong>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="sub-metric-pill-card">
                    <span class="sub-metric-label">Failed Logins</span>
                    <strong class="sub-metric-value text-danger bn-digit font-tabular">{{ $failedAuthCount }}</strong>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="sub-metric-pill-card">
                    <span class="sub-metric-label">On This Page</span>
                    <strong class="sub-metric-value text-primary bn-digit font-tabular">{{ $ips->count() }}</strong>
                </div>
            </div>
        </div>

        <!-- 4. FILTERS -->

        <!-- Mobile Search -->
        <div class="sec-card p-2.5 mb-3 d-md-none">
            <div class="d-flex align-items-center gap-2">
                <div class="input-group input-group-sm flex-grow-1">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-search" style="font-size: 10px;"></i></span>
                    <input type="text" class="form-control border-start-0" id="mobile_filter_search" placeholder="Search IP / Country..." value="{{ request('search') }}" oninput="syncMobileSearch(this.value)">
                </div>
                <button type="button" class="btn me-1 btn-outline-secondary btn-compact shadow-xs bg-white text-dark fw-semibold flex-shrink-0" data-bs-toggle="modal" data-bs-target="#modalMobileFilters">
                    <i class="fas fa-sliders me-1 text-primary"></i>
                    <span>Filters</span>
                    <span class="badge bg-primary text-white rounded-pill px-1.5 py-0.5 ms-1 d-none" id="mobileActiveFilterBadge" style="font-size: 8.5px;">0</span>
                </button>
            </div>
        </div>

        <!-- Desktop Filters -->
        <section class="sec-card p-3 mb-3 d-none d-md-block">
            <form id="ipFilterForm" method="GET" action="{{ route('admin.security.ips.index') }}" class="row g-2 align-items-end">

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary mb-1" style="font-size: 11px;">Search IP / Country</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-search" style="font-size: 10px;"></i></span>
                        <input type="text" class="form-control border-start-0" id="filter_search" name="search" placeholder="IP address or country..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary mb-1" style="font-size: 11px;">IP Version</label>
                    <select class="form-select form-select-sm" id="filter_ip_version" name="ip_version">
                        <option value="">All Versions</option>
                        <option value="v4" {{ request('ip_version') === 'v4' ? 'selected' : '' }}>IPv4</option>
                        <option value="v6" {{ request('ip_version') === 'v6' ? 'selected' : '' }}>IPv6</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary mb-1" style="font-size: 11px;">Device</label>
                    <select class="form-select form-select-sm" id="filter_device_type" name="device_type">
                        <option value="">All Devices</option>
                        <option value="Desktop" {{ request('device_type') === 'Desktop' ? 'selected' : '' }}>Desktop</option>
                        <option value="Mobile" {{ request('device_type') === 'Mobile' ? 'selected' : '' }}>Mobile</option>
                        <option value="Tablet" {{ request('device_type') === 'Tablet' ? 'selected' : '' }}>Tablet</option>
                        <option value="Bot" {{ request('device_type') === 'Bot' ? 'selected' : '' }}>Bot / Scanner</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary mb-1" style="font-size: 11px;">Risk Level</label>
                    <select class="form-select form-select-sm" id="filter_risk_level" name="risk_level">
                        <option value="">All Levels</option>
                        <option value="high" {{ request('risk_level') === 'high' ? 'selected' : '' }}>High (75-100)</option>
                        <option value="medium" {{ request('risk_level') === 'medium' ? 'selected' : '' }}>Medium (40-74)</option>
                        <option value="low" {{ request('risk_level') === 'low' ? 'selected' : '' }}>Low (Below 40)</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <label class="form-label small fw-bold text-secondary mb-1" style="font-size: 11px;">Per Page</label>
                    <select class="form-select form-select-sm bn-digit font-tabular" id="filter_per_page" name="per_page">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-secondary btn-compact w-100 shadow-xs" id="btn-reset-filters" style="height: 34px;">
                        <i class="fas fa-rotate-left me-1"></i>
                        <span>Reset</span>
                    </button>
                </div>

            </form>
        </section>

        <!-- 5. IP TABLE -->
        <section class="sec-card overflow-hidden">
            <div class="sec-table-container">
                <table class="table sec-table align-middle mb-0" id="main-ips-table">
                    <thead>
                        <tr>
                            <th class="ps-3 col-ip">IP Address</th>
                            <th class="col-country">Country</th>
                            <th class="col-device">Device / OS</th>
                            <th class="text-center col-hits">Hits</th>
                            <th class="text-center col-orders">Orders</th>
                            <th class="text-center col-forms">Forms</th>
                            <th class="text-center col-fails">Login Fails</th>
                            <th class="text-center col-risk">Risk</th>
                            <th class="col-activity">Last Seen</th>
                            <th class="text-end pe-3 pe-md-4 col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="ip-table-body">
                        @forelse($ips as $ip)
                            @php
                                $isV6 = filter_var($ip->ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
                                $risk = (int) ($ip->risk_score ?? $ip->max_risk ?? 0);
                                $riskBadge = match(true) {
                                    $risk >= 75 => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    $risk >= 40 => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                    default     => 'bg-success-subtle text-success border border-success-subtle',
                                };
                                $hits = (int) ($ip->total_requests ?? $ip->total_hits ?? $ip->hits_count ?? 0);
                                $orders = (int) ($ip->orders_count ?? 0);
                                $forms = (int) ($ip->forms_count ?? 0);
                                $failedLogins = (int) ($ip->failed_logins_count ?? $ip->failed_logins ?? 0);
                                $lastSeen = $ip->last_activity ?? $ip->last_seen ?? $ip->updated_at ?? $ip->created_at;
                                $isBlocked = (bool) ($ip->is_blocked ?? false);
                            @endphp
                            <tr class="{{ $isBlocked ? 'table-danger bg-opacity-25' : '' }}" data-ip="{{ $ip->ip_address }}">

                                <td class="ps-3 fw-bold text-dark col-ip">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <a href="{{ route('admin.security.ips.show', $ip->ip_address) }}" class="text-decoration-none ip-link-text">
                                            {{ $ip->ip_address }}
                                        </a>
                                        <span class="protocol-tag {{ $isV6 ? 'tag-v6' : 'tag-v4' }}">
                                            {{ $isV6 ? 'IPv6' : 'IPv4' }}
                                        </span>
                                        @if($isBlocked)
                                            <span class="badge-blocked-pill">Blocked</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="col-country">
                                    <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 10.5px; font-weight: 600;">
                                        <i class="fas fa-globe text-secondary me-1"></i>{{ $ip->country ?: 'Unknown' }}
                                    </span>
                                </td>

                                <td class="text-muted small col-device" style="font-size: 11.5px;">
                                    {{ $ip->device_type ?? 'Desktop' }} ({{ $ip->platform ?? 'OS' }})
                                </td>

                                <td class="text-center fw-bold text-dark bn-digit font-tabular col-hits">{{ number_format($hits) }}</td>
                                <td class="text-center fw-bold bn-digit font-tabular col-orders {{ $orders > 0 ? 'text-warning' : 'text-muted' }}">{{ number_format($orders) }}</td>
                                <td class="text-center fw-semibold bn-digit font-tabular text-secondary col-forms">{{ number_format($forms) }}</td>
                                <td class="text-center fw-bold bn-digit font-tabular col-fails {{ $failedLogins > 0 ? 'text-danger' : 'text-muted' }}">{{ number_format($failedLogins) }}</td>

                                <td class="text-center col-risk">
                                    <span class="badge {{ $riskBadge }} bn-digit font-tabular px-2 py-0.5" style="font-size: 9.5px; border-radius: 4px;">
                                        {{ $risk }}/100
                                    </span>
                                </td>

                                <td class="text-muted small col-activity" style="font-size: 11px;">
                                    {{ $lastSeen ? \Carbon\Carbon::parse($lastSeen)->diffForHumans() : 'N/A' }}
                                </td>

                                <td class="text-end pe-3 pe-md-4 col-actions">
                                    <div class="d-inline-flex align-items-center justify-content-end gap-1.5">
                                        <a href="{{ route('admin.security.ips.show', $ip->ip_address) }}" class="sec-action-btn sec-action-btn-view shadow-xs" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if(!$isBlocked)
                                            <button type="button" class="sec-action-btn ms-1 sec-action-btn-block shadow-xs btn-direct-block" data-ip="{{ $ip->ip_address }}" title="Block IP">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @else
                                            <button type="button" class="sec-action-btn sec-action-btn-unblock shadow-xs btn-direct-unblock" data-ip="{{ $ip->ip_address }}" title="Unblock IP">
                                                <i class="fas fa-lock-open"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-5 text-center text-muted">
                                    <i class="fas fa-network-wired fa-2x mb-2 text-secondary opacity-25 d-block"></i>
                                    <p class="small fw-semibold mb-0">No IP records found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer bg-white border-top border-light-subtle py-2.5 px-3 d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2">
                <span class="small text-muted" id="ip-showing-text" style="font-size: 11.5px;">
                    Showing <strong>{{ $ips->firstItem() ?? 0 }}</strong> to <strong>{{ $ips->lastItem() ?? 0 }}</strong> of <strong class="bn-digit font-tabular">{{ number_format($ips->total()) }}</strong> entries
                </span>
                <div id="ip-pagination-container">
                    {{ $ips->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </section>

    </div>
</div>

<!-- 6. MOBILE FILTER MODAL -->
<div class="modal fade sec-modal-scope" id="modalMobileFilters" tabindex="-1" aria-hidden="true" style="z-index: 107500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 10px !important;">
            <div class="modal-header py-2.5 px-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-1.5 mb-0" style="font-size: 13.5px;">
                    <i class="fas fa-sliders me-1 text-primary"></i> Filters
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-2.5">
                    <label class="form-label small fw-bold text-secondary mb-1">IP Version</label>
                    <select class="form-select form-select-sm" id="m_filter_ip_version" onchange="syncFromModalToDesktop('filter_ip_version', this.value)">
                        <option value="">All Versions</option>
                        <option value="v4" {{ request('ip_version') === 'v4' ? 'selected' : '' }}>IPv4</option>
                        <option value="v6" {{ request('ip_version') === 'v6' ? 'selected' : '' }}>IPv6</option>
                    </select>
                </div>

                <div class="mb-2.5">
                    <label class="form-label small fw-bold text-secondary mb-1">Device</label>
                    <select class="form-select form-select-sm" id="m_filter_device_type" onchange="syncFromModalToDesktop('filter_device_type', this.value)">
                        <option value="">All Devices</option>
                        <option value="Desktop" {{ request('device_type') === 'Desktop' ? 'selected' : '' }}>Desktop</option>
                        <option value="Mobile" {{ request('device_type') === 'Mobile' ? 'selected' : '' }}>Mobile</option>
                        <option value="Tablet" {{ request('device_type') === 'Tablet' ? 'selected' : '' }}>Tablet</option>
                        <option value="Bot" {{ request('device_type') === 'Bot' ? 'selected' : '' }}>Bot / Scanner</option>
                    </select>
                </div>

                <div class="mb-2.5">
                    <label class="form-label small fw-bold text-secondary mb-1">Risk Level</label>
                    <select class="form-select form-select-sm" id="m_filter_risk_level" onchange="syncFromModalToDesktop('filter_risk_level', this.value)">
                        <option value="">All Levels</option>
                        <option value="high" {{ request('risk_level') === 'high' ? 'selected' : '' }}>High (75-100)</option>
                        <option value="medium" {{ request('risk_level') === 'medium' ? 'selected' : '' }}>Medium (40-74)</option>
                        <option value="low" {{ request('risk_level') === 'low' ? 'selected' : '' }}>Low (Below 40)</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-bold text-secondary mb-1">Per Page</label>
                    <select class="form-select form-select-sm bn-digit font-tabular" id="m_filter_per_page" onchange="syncFromModalToDesktop('filter_per_page', this.value)">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer py-2 px-3 bg-light border-top d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-compact" onclick="resetModalFilters()">Reset</button>
                <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold px-3" onclick="applyModalFilters()">Apply</button>
            </div>
        </div>
    </div>
</div>

<!-- 7. MANUAL BLOCK IP MODAL -->
<div class="modal fade sec-modal-scope" id="modalManualBlockIp" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 8px !important;">
            <div class="modal-header py-2.5 px-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-danger d-flex align-items-center gap-1.5 mb-0" style="font-size: 13px;">
                    <i class="fas fa-ban"></i> Block IP
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formSubmitBlockIp" method="POST" action="javascript:void(0);">
                @csrf
                <div class="modal-body p-3">
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-dark mb-1">IP Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm bn-digit font-tabular" id="modal_block_ip_address" name="ip_address" required placeholder="203.0.113.195">
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark mb-1">Block Type</label>
                            <select class="form-select form-select-sm" id="modal_block_type" name="block_type" required>
                                <option value="temporary">Temporary</option>
                                <option value="permanent">Permanent</option>
                            </select>
                        </div>
                        <div class="col-6" id="duration_wrapper">
                            <label class="form-label small fw-bold text-dark mb-1">Duration</label>
                            <select class="form-select form-select-sm" id="modal_duration_minutes" name="duration_minutes">
                                <option value="15">15 Mins</option>
                                <option value="60" selected>1 Hour</option>
                                <option value="360">6 Hours</option>
                                <option value="1440">24 Hours</option>
                                <option value="10080">7 Days</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold text-dark mb-1">Reason <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="modal_block_reason" name="block_reason" required placeholder="Repeated suspicious activity">
                    </div>

                    <div class="mb-1">
                        <label class="form-label small fw-bold text-dark mb-1">Admin Note (Optional)</label>
                        <textarea class="form-control form-control-sm" id="modal_admin_note" name="admin_note" rows="2" placeholder="Internal note..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-compact shadow-xs fw-bold px-3" id="btn-confirm-block-submit">Block IP</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 8. CONFIRM MODAL -->
<div class="modal fade sec-modal-scope" id="ipConfirmMinimalModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content minimal-modal-card">
            <h6 class="minimal-modal-title" id="ipConfirmModalTitle">Block this IP?</h6>
            <p class="minimal-modal-subtext" id="ipConfirmModalMessage">
                This IP will be blocked from accessing the site.
            </p>
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn-minimal-action" id="btnExecuteIpConfirm">Block IP</button>
                <button type="button" class="btn-minimal-cancel" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- 9. NOTICE MODAL -->
<div class="modal fade sec-modal-scope" id="ipNoticeMinimalModal" tabindex="-1" aria-hidden="true" style="z-index: 108600;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content minimal-modal-card">
            <h6 class="minimal-modal-title" id="ipNoticeModalTitle">Notice</h6>
            <p class="minimal-modal-subtext" id="ipNoticeModalMessage" style="margin-bottom: 16px !important;"></p>
            <div class="d-flex align-items-center">
                <button type="button" class="btn-minimal-action" style="background-color: #2563eb !important; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.28) !important;" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- 10. AJAX HANDLERS -->
<script>
(function() {
    const tableBody = document.getElementById('ip-table-body');
    const paginationContainer = document.getElementById('ip-pagination-container');
    const filterForm = document.getElementById('ipFilterForm');
    const searchInput = document.getElementById('filter_search');
    const csrfToken = '{{ csrf_token() }}';

    let searchTimer = null;
    let pendingConfirmAction = null;

    // Notice Modal
    function showAppNotice(msg, title = 'Notice') {
        const titleEl = document.getElementById('ipNoticeModalTitle');
        const msgEl   = document.getElementById('ipNoticeModalMessage');
        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = msg;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('ipNoticeMinimalModal')).show();
    }

    // Confirmation Modal
    function showAppConfirm(title, msg, onConfirm, btnText = 'Block IP', btnColor = '#c93424') {
        const titleEl = document.getElementById('ipConfirmModalTitle');
        const msgEl   = document.getElementById('ipConfirmModalMessage');
        const btn     = document.getElementById('btnExecuteIpConfirm');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerHTML = msg;
        if (btn) {
            btn.innerText = btnText;
            btn.style.backgroundColor = btnColor;
        }

        pendingConfirmAction = onConfirm;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('ipConfirmMinimalModal')).show();
    }

    document.getElementById('btnExecuteIpConfirm')?.addEventListener('click', function() {
        bootstrap.Modal.getInstance(document.getElementById('ipConfirmMinimalModal'))?.hide();
        if (typeof pendingConfirmAction === 'function') {
            pendingConfirmAction();
            pendingConfirmAction = null;
        }
    });

    // 1. Fetch IP Data
    function fetchIpsData(url = null) {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        const targetUrl = url || `{{ route('admin.security.ips.index') }}?${params.toString()}`;

        if (tableBody) tableBody.style.opacity = '0.4';

        fetch(targetUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (tableBody) tableBody.style.opacity = '1';

            if (data.success) {
                if (tableBody && data.html) {
                    tableBody.innerHTML = data.html;
                }
                if (paginationContainer && data.pagination) {
                    paginationContainer.innerHTML = data.pagination;
                }
                const counter = document.getElementById('ip-total-counter');
                if (counter && data.total !== undefined) {
                    counter.innerText = Number(data.total).toLocaleString();
                }
                const showingText = document.getElementById('ip-showing-text');
                if (showingText && data.total !== undefined) {
                    showingText.innerHTML = `Showing <strong class="bn-digit font-tabular">${Number(data.total).toLocaleString()}</strong> entries`;
                }
            }
        })
        .catch(err => {
            if (tableBody) tableBody.style.opacity = '1';
            console.error('IPs fetch error:', err);
        });
    }

    // 2. Search
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                fetchIpsData();
            }, 350);
        });
    }

    // Mobile Search Sync
    window.syncMobileSearch = function(val) {
        if (searchInput) {
            searchInput.value = val;
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                fetchIpsData();
            }, 350);
        }
    };

    // Mobile Filter Sync
    window.syncFromModalToDesktop = function(desktopElemId, val) {
        const desktopEl = document.getElementById(desktopElemId);
        if (desktopEl) {
            desktopEl.value = val;
        }
    };

    window.applyModalFilters = function() {
        const modalEl = document.getElementById('modalMobileFilters');
        if (modalEl) {
            bootstrap.Modal.getInstance(modalEl)?.hide();
        }
        updateMobileFilterBadge();
        fetchIpsData();
    };

    window.resetModalFilters = function() {
        document.getElementById('m_filter_ip_version').value = '';
        document.getElementById('m_filter_device_type').value = '';
        document.getElementById('m_filter_risk_level').value = '';
        document.getElementById('m_filter_per_page').value = '20';

        document.getElementById('filter_ip_version').value = '';
        document.getElementById('filter_device_type').value = '';
        document.getElementById('filter_risk_level').value = '';
        document.getElementById('filter_per_page').value = '20';

        const mobSearch = document.getElementById('mobile_filter_search');
        if (mobSearch) mobSearch.value = '';
        if (searchInput) searchInput.value = '';

        updateMobileFilterBadge();
        fetchIpsData();
    };

    function updateMobileFilterBadge() {
        const badge = document.getElementById('mobileActiveFilterBadge');
        if (!badge) return;

        let activeCount = 0;
        if (document.getElementById('m_filter_ip_version').value) activeCount++;
        if (document.getElementById('m_filter_device_type').value) activeCount++;
        if (document.getElementById('m_filter_risk_level').value) activeCount++;

        if (activeCount > 0) {
            badge.innerText = activeCount;
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    }

    // 3. Auto Refresh Filters
    document.querySelectorAll('#filter_ip_version, #filter_device_type, #filter_risk_level, #filter_per_page').forEach(el => {
        el.addEventListener('change', () => fetchIpsData());
    });

    // 4. Reset Filters
    const resetBtn = document.getElementById('btn-reset-filters');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            filterForm.reset();
            const mobSearch = document.getElementById('mobile_filter_search');
            if (mobSearch) mobSearch.value = '';
            fetchIpsData(`{{ route('admin.security.ips.index') }}`);
        });
    }

    // 5. Pagination
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#ip-pagination-container .page-link');
        if (pageLink && pageLink.getAttribute('href')) {
            e.preventDefault();
            fetchIpsData(pageLink.getAttribute('href'));
        }
    });

    // 6. Block / Unblock IP
    document.addEventListener('click', function(e) {
        const blockBtn = e.target.closest('.btn-direct-block');
        if (blockBtn) {
            const ip = blockBtn.getAttribute('data-ip');
            showAppConfirm(
                'Block IP?',
                `Block IP <strong class="text-dark">[${ip}]</strong> from accessing the site?`,
                function() {
                    blockBtn.disabled = true;
                    fetch(`{{ route('admin.security.ips.block') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            ip_address: ip,
                            block_type: 'temporary',
                            duration_minutes: 1440,
                            block_reason: 'Direct 1-click restriction from IP Directory'
                        })
                    })
                    .then(r => r.json())
                    .then(res => {
                        showAppNotice(res.message, 'IP Block');
                        if (res.success) fetchIpsData();
                    })
                    .catch(() => {
                        blockBtn.disabled = false;
                    });
                },
                'Block IP',
                '#c93424'
            );
        }

        const unblockBtn = e.target.closest('.btn-direct-unblock');
        if (unblockBtn) {
            const ip = unblockBtn.getAttribute('data-ip');
            showAppConfirm(
                'Unblock IP?',
                `Remove the block for IP <strong class="text-dark">[${ip}]</strong>?`,
                function() {
                    unblockBtn.disabled = true;
                    fetch(`{{ route('admin.security.ips.unblock') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ ip_address: ip, note: 'Lifted via IP Directory' })
                    })
                    .then(r => r.json())
                    .then(res => {
                        showAppNotice(res.message, 'IP Unblocked');
                        if (res.success) fetchIpsData();
                    })
                    .catch(() => {
                        unblockBtn.disabled = false;
                    });
                },
                'Unblock IP',
                '#16a34a'
            );
        }
    });

    // 7. Block Type / Duration
    const blockTypeSelect = document.getElementById('modal_block_type');
    const durationWrapper = document.getElementById('duration_wrapper');
    if (blockTypeSelect && durationWrapper) {
        blockTypeSelect.addEventListener('change', function() {
            durationWrapper.style.display = this.value === 'permanent' ? 'none' : 'block';
        });
    }

    // 8. Manual Block Form
    const formSubmitBlockIp = document.getElementById('formSubmitBlockIp');
    if (formSubmitBlockIp) {
        formSubmitBlockIp.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('btn-confirm-block-submit');
            submitBtn.disabled = true;
            submitBtn.innerText = 'Blocking...';

            const payload = {
                ip_address: document.getElementById('modal_block_ip_address').value,
                block_type: document.getElementById('modal_block_type').value,
                duration_minutes: document.getElementById('modal_duration_minutes').value,
                block_reason: document.getElementById('modal_block_reason').value,
                admin_note: document.getElementById('modal_admin_note').value,
            };

            fetch(`{{ route('admin.security.ips.block') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(res => {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Block IP';

                const modalEl = document.getElementById('modalManualBlockIp');
                if (modalEl && typeof bootstrap !== 'undefined') {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                }
                formSubmitBlockIp.reset();
                showAppNotice(res.message, 'Firewall Updated');
                if (res.success) fetchIpsData();
            })
            .catch(() => {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Block IP';
                showAppNotice('Network error. Please try again.', 'Error');
            });
        });
    }
})();
</script>
@endsection