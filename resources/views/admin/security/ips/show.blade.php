@extends('layouts.admin')

@section('title', 'IP Security Audit: ' . $ip . ' - ' . config('app.name'))

@php
    $isBlocked = (($blockDetails['type'] ?? 'unknown') !== 'unknown');
    $riskScore = (int) ($summary?->max_risk_score ?? 0);
    $riskBadgeClass = match(true) {
        $riskScore >= 75 => 'bg-danger-subtle text-danger border border-danger-subtle',
        $riskScore >= 40 => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
        default          => 'bg-success-subtle text-success border border-success-subtle',
    };
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
    #security-ip-details-wrapper,
    .sec-modal-scope {
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        letter-spacing: -0.01em;
        color: #1e293b;
    }

    /* FontAwesome Icon Protection */
    #security-ip-details-wrapper i,
    #security-ip-details-wrapper .fa,
    #security-ip-details-wrapper .fas,
    #security-ip-details-wrapper .far,
    #security-ip-details-wrapper .fab,
    #security-ip-details-wrapper .fa-solid,
    #security-ip-details-wrapper .fa-regular,
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
    #security-ip-details-wrapper .far,
    .sec-modal-scope .far {
        font-weight: 400 !important;
    }

    #security-ip-details-wrapper {
        background-color: #f8fafc;
        min-height: 100vh;
    }

    /* Card Theme Matching Clean White Surface */
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

    /* Tabular Numeric Data Alignment */
    .bn-digit,
    .font-tabular {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
    }

    /* Standardized Header Action & Icon Squares (Exact Same 36x36px) */
    .sec-btn-square {
        width: 36px !important;
        height: 36px !important;
        min-width: 36px !important;
        border-radius: 8px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        color: #334155 !important;
        text-decoration: none !important;
        font-size: 13px !important;
        transition: all 0.15s ease;
    }
    .sec-btn-square:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }

    .sec-header-icon-box {
        width: 36px !important;
        height: 36px !important;
        min-width: 36px !important;
        border-radius: 8px !important;
        background-color: #eff6ff !important;
        border: 1px solid #bfdbfe !important;
        color: #2563eb !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        font-size: 14px !important;
    }

    /* Standardized Card Title Soft Icon Boxes (28x28px) */
    .card-title-icon {
        width: 28px !important;
        height: 28px !important;
        min-width: 28px !important;
        border-radius: 6px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 12px !important;
        flex-shrink: 0 !important;
        margin-right: 8px !important;
    }
    .icon-soft-blue   { background-color: #eff6ff !important; border: 1px solid #bfdbfe !important; color: #2563eb !important; }
    .icon-soft-teal   { background-color: #f0fdfa !important; border: 1px solid #99f6e4 !important; color: #0d9488 !important; }
    .icon-soft-green  { background-color: #f0fdf4 !important; border: 1px solid #bbf7d0 !important; color: #16a34a !important; }
    .icon-soft-amber  { background-color: #fffbeb !important; border: 1px solid #fef3c7 !important; color: #d97706 !important; }
    .icon-soft-red    { background-color: #fef2f2 !important; border: 1px solid #fecaca !important; color: #dc2626 !important; }
    .icon-soft-purple { background-color: #faf5ff !important; border: 1px solid #e9d5ff !important; color: #9333ea !important; }

    /* Page Titles & Subtitles */
    .sec-page-title {
        font-size: 17px !important;
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

    /* Status Protected & Blocked Pills */
    .badge-status-protected {
        background-color: #ecfdf5 !important;
        color: #059669 !important;
        border: 1px solid #a7f3d0 !important;
        font-size: 10px !important;
        font-weight: 700 !important;
        padding: 2.5px 8px !important;
        border-radius: 20px !important;
        display: inline-flex;
        align-items: center;
        letter-spacing: 0.01em;
    }
    .badge-status-blocked {
        background-color: #fef2f2 !important;
        color: #dc2626 !important;
        border: 1px solid #fecaca !important;
        font-size: 10px !important;
        font-weight: 700 !important;
        padding: 2.5px 8px !important;
        border-radius: 20px !important;
        display: inline-flex;
        align-items: center;
        letter-spacing: 0.01em;
    }

    /* Metric Cards Architecture */
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
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #64748b;
        line-height: 1.2;
    }
    .stat-val-num {
        font-size: 19px;
        font-weight: 800;
        line-height: 1.25;
        letter-spacing: -0.02em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #0f172a;
    }
    .stat-sub-text {
        font-size: 10.5px;
        font-weight: 600;
        margin-top: 2px;
    }
    .metric-icon-square {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
        border-radius: 7px !important;
    }

    /* Compact Buttons */
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

    /* Fixed Action Buttons (No Squish, Proper Spacing) */
    .sec-action-btn {
        width: 28px !important;
        height: 28px !important;
        min-width: 28px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 5px !important;
        padding: 0 !important;
        font-size: 11px !important;
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

    /* Form Controls */
    #security-ip-details-wrapper .form-control,
    #security-ip-details-wrapper .form-select,
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
    #security-ip-details-wrapper .form-control:focus,
    #security-ip-details-wrapper .form-select:focus,
    .sec-modal-scope .form-control:focus,
    .sec-modal-scope .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Protocol & Tag Badges */
    .protocol-tag {
        font-size: 9px;
        font-weight: 700;
        padding: 2px 5px;
        border-radius: 4px;
        line-height: 1.2;
        letter-spacing: 0.02em;
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
    }

    /* Unified Data Table */
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
        padding: 9px 12px !important;
        white-space: nowrap !important;
    }
    .sec-table tbody td {
        padding: 9px 12px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        vertical-align: middle !important;
        font-size: 12px !important;
        white-space: nowrap !important;
        color: #1e293b;
    }
    .sec-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    /* Minimal Card Modal System (Screenshot Reference) */
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

    /* Custom Scrollbar */
    .sec-table-container::-webkit-scrollbar {
        height: 5px;
    }
    .sec-table-container::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 4px;
    }

    /* Mobile Responsive Fixes (< 768px) */
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
            min-height: 78px !important;
        }
        .stat-val-num {
            font-size: 17px !important;
        }
        .stat-label-title {
            font-size: 9.5px !important;
        }
        .sec-table thead th, .sec-table tbody td {
            padding: 8px 10px !important;
        }
        .minimal-modal-card {
            padding: 20px 20px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-sm-3 px-md-4 py-3" id="security-ip-details-wrapper">
    <div class="mx-auto" style="max-width: 1600px;">

        <!-- ================= 1. HEADER & QUICK ACTIONS (Perfect Alignment on Desktop & Mobile) ================= -->
        <header class="sec-card p-3 mb-3">
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 header-top-wrap">
                
                <!-- Left Title, Navigation & Status Badge -->
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ Route::has('admin.security.ips.index') ? route('admin.security.ips.index') : (Route::has('admin.security.blocking.ips') ? route('admin.security.blocking.ips') : url('/admin/security/blocking/ips')) }}" class="sec-btn-square shadow-xs" title="Back to IP Directory">
                        <i class="fas fa-arrow-left"></i>
                    </a>

                    <span class="sec-header-icon-box shadow-xs">
                        <i class="fas fa-network-wired"></i>
                    </span>

                    <div class="min-w-0">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                            <h4 class="sec-page-title bn-digit font-tabular mb-0">{{ $ip }}</h4>
                            <span class="protocol-tag">
                                {{ strtoupper($latestLog?->ip_version ?? 'IPv4') }}
                            </span>
                            @if($isBlocked)
                                <span class="badge-status-blocked"><i class="fas fa-ban me-1"></i>Blocked</span>
                            @else
                                <span class="badge-status-protected"><i class="fas fa-shield-check me-1"></i>Active</span>
                            @endif
                        </div>
                        <p class="sec-page-subtitle mb-0 text-truncate">
                           <i class="fa-solid fa-eye me-1"></i><strong class="text-dark">{{ $latestLog?->country ?: 'Unknown' }}</strong> <span class="d-none d-sm-inline">|</span> <span class="d-block d-sm-inline mt-0.5 mt-sm-0">First seen {{ !empty($summary?->first_seen) ? \Carbon\Carbon::parse($summary->first_seen)->diffForHumans() : 'N/A' }}</span>
                        </p>
                    </div>
                </div>

                <!-- Right Action Buttons -->
                <div class="header-action-btns-wrap d-flex align-items-center gap-2">
                    @if($isBlocked)
                        <button type="button" class="btn btn-outline-success btn-compact shadow-xs fw-semibold px-3" id="btn-unblock-this-ip" data-ip="{{ $ip }}">
                            <i class="fas fa-lock-open me-1"></i> Lift Block
                        </button>
                    @else
                        <button type="button" class="btn btn-danger btn-compact shadow-xs fw-bold px-3" data-bs-toggle="modal" data-bs-target="#modalBlockIpAction">
                            <i class="fas fa-ban me-1"></i> Block This IP
                        </button>
                    @endif

                    <a href="{{ Route::has('admin.security.ips.index') ? route('admin.security.ips.index') : (Route::has('admin.security.blocking.ips') ? route('admin.security.blocking.ips') : url('/admin/security/blocking/ips')) }}" class="btn btn-outline-secondary btn-compact shadow-xs bg-white text-dark fw-semibold">
                        <i class="fas fa-list me-1 text-primary"></i> IP Directory
                    </a>
                </div>

            </div>
        </header>

        <!-- Restriction Notice Banner (If Blocked) -->
        @if($isBlocked)
            <div class="alert alert-danger border-0 shadow-xs p-3 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="border-radius: 8px !important; background-color: #fef2f2 !important; border: 1px solid #fecaca !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-2 bg-danger text-white d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fas fa-shield-virus fs-6"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-danger" style="font-size: 13px;">Firewall Enforcement Active on this IP</h6>
                        <p class="mb-0 small text-danger text-opacity-75" style="font-size: 11.5px;">
                            Restriction: <strong>{{ ucfirst($blockDetails['type'] ?? 'Firewall Restriction') }}</strong> | 
                            Reason: <strong>{{ $blockDetails['reason'] ?? 'Manually restricted by administrator' }}</strong> | 
                            Expires: <strong>{{ $blockDetails['expires_at'] ?? 'Permanent' }}</strong>
                        </p>
                    </div>
                </div>
                @if(!empty($blockRecord->admin_note))
                    <span class="small bg-white px-2.5 py-1 rounded border border-danger-subtle text-danger font-monospace" style="font-size: 11px;">
                        Note: {{ $blockRecord->admin_note }}
                    </span>
                @endif
            </div>
        @endif

        <!-- ================= 2. SUMMARY METRIC 6-GRID CARDS ================= -->
        <div class="row g-2 mb-3">
            <!-- Total Requests -->
            <div class="col-6 col-md-4 col-xl-2">
                <div class="metric-card">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="stat-label-title text-truncate">Requests</span>
                        <div class="metric-icon-square icon-soft-blue shadow-xs">
                            <i class="fas fa-network-wired"></i>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <h4 class="stat-val-num bn-digit font-tabular my-0">{{ number_format($summary?->total_requests ?? 0) }}</h4>
                    </div>
                </div>
            </div>

            <!-- Orders Created -->
            <div class="col-6 col-md-4 col-xl-2">
                <div class="metric-card">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="stat-label-title text-truncate">Orders</span>
                        <div class="metric-icon-square icon-soft-green shadow-xs">
                            <i class="fas fa-bag-shopping"></i>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <h4 class="stat-val-num text-success bn-digit font-tabular my-0">{{ number_format($summary?->total_orders ?? 0) }}</h4>
                      
                    </div>
                </div>
            </div>

            <!-- Failed Logins -->
            <div class="col-6 col-md-4 col-xl-2">
                <div class="metric-card">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="stat-label-title text-truncate">Auth Fails</span>
                        <div class="metric-icon-square icon-soft-red shadow-xs">
                            <i class="fas fa-key"></i>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <h4 class="stat-val-num {{ ($summary?->failed_logins ?? 0) > 0 ? 'text-danger' : 'text-dark' }} bn-digit font-tabular my-0">
                            {{ number_format($summary?->failed_logins ?? 0) }}
                        </h4>
                       
                    </div>
                </div>
            </div>

            <!-- Blocked Hits -->
            <div class="col-6 col-md-4 col-xl-2">
                <div class="metric-card">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="stat-label-title text-truncate">Blocked Hits</span>
                        <div class="metric-icon-square icon-soft-amber shadow-xs">
                            <i class="fas fa-ban"></i>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <h4 class="stat-val-num text-danger bn-digit font-tabular my-0">{{ number_format($summary?->blocked_requests ?? 0) }}</h4>
                       
                    </div>
                </div>
            </div>

            <!-- Max Risk Score -->
            <div class="col-6 col-md-4 col-xl-2">
                <div class="metric-card">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="stat-label-title text-truncate">Risk Score</span>
                        <div class="metric-icon-square icon-soft-teal shadow-xs">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <h4 class="stat-val-num bn-digit font-tabular my-0">
                            {{ $riskScore }}<span class="text-muted fw-normal" style="font-size: 11px;">/100</span>
                        </h4>
                       
                    </div>
                </div>
            </div>

            <!-- Form Submissions -->
            <div class="col-6 col-md-4 col-xl-2">
                <div class="metric-card">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="stat-label-title text-truncate">Forms</span>
                        <div class="metric-icon-square icon-soft-purple shadow-xs">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <h4 class="stat-val-num text-primary bn-digit font-tabular my-0">{{ number_format($summary?->total_forms ?? 0) }}</h4>
                    
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= 3. CLIENT TELEMETRY & FREQUENT ENDPOINTS ================= -->
        <div class="row g-3 mb-3">
            <!-- Client Fingerprint Card -->
            <div class="col-12 col-lg-5">
                <div class="sec-card h-100 p-0 overflow-hidden">
                    <div class="py-1 px-3 bg-white border-bottom d-flex align-items-center">
                        <span class="card-title-icon icon-soft-blue"><i class="fas fa-desktop"></i></span>
                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 13px;">
                            Device & Network Details
                        </h6>
                    </div>
                    <div class="p-3">
                        <table class="table table-sm table-borderless mb-0" style="font-size: 12px;">
                            <tbody>
                                <tr>
                                    <td class="text-muted" style="width: 140px;">IP Address:</td>
                                    <td class="fw-bold text-dark bn-digit font-tabular">{{ $ip }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Country / Region:</td>
                                    <td class="text-dark fw-semibold">
                                        <i class="fas fa-globe text-secondary me-1"></i> {{ $latestLog?->country ?: 'Unknown' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Device Category:</td>
                                    <td class="text-dark fw-semibold">
                                        <i class="fas {{ (($latestLog?->device_type ?? 'Desktop') === 'Mobile' ? 'fa-mobile-screen' : (($latestLog?->device_type ?? 'Desktop') === 'Tablet' ? 'fa-tablet-screen' : 'fa-laptop')) }} me-1 text-primary"></i>
                                        {{ $latestLog?->device_type ?? 'Desktop' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Browser &amp; Platform:</td>
                                    <td class="text-dark">
                                        <span class="badge bg-light text-dark border px-2 py-0.5" style="font-size: 10.5px;">
                                            {{ $latestLog?->browser ?? 'Browser' }}
                                        </span>
                                        on <strong>{{ $latestLog?->platform ?? 'OS' }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">First Seen:</td>
                                    <td class="text-dark bn-digit font-tabular">{{ !empty($summary?->first_seen) ? \Carbon\Carbon::parse($summary->first_seen)->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Last Activity:</td>
                                    <td class="text-dark bn-digit font-tabular">{{ !empty($summary?->last_seen) ? \Carbon\Carbon::parse($summary->last_seen)->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">User Agent:</td>
                                    <td class="text-break text-muted font-monospace" style="font-size: 10.5px; line-height: 1.4;">
                                        {{ $latestLog?->user_agent ?? 'N/A' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Top Requested URLs from this IP -->
            <div class="col-12 col-lg-7">
                <div class="sec-card h-100 p-0 overflow-hidden">
                    <div class="py-1 px-3 bg-white border-bottom d-flex align-items-center">
                        <span class="card-title-icon icon-soft-teal"><i class="fas fa-compass"></i></span>
                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 13px;">
                          Targeted Routes
                        </h6>
                    </div>
                    <div class="sec-table-container">
                        <table class="table sec-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">URL Endpoint</th>
                                    <th class="text-center" style="width: 95px;">Hit Count</th>
                                    <th class="text-center" style="width: 95px;">Last Status</th>
                                    <th class="pe-3 text-end" style="width: 75px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topUrls ?? [] as $urlRow)
                                    <tr>
                                        <td class="ps-3 text-dark fw-semibold" title="{{ $urlRow->url_path }}">
                                            {{ Str::limit($urlRow->url_path, 42) }}
                                        </td>
                                        <td class="text-center fw-bold bn-digit font-tabular">{{ number_format($urlRow->visit_count ?? 0) }}</td>
                                        <td class="text-center">
                                            @php
                                                $st = (int) ($urlRow->last_status ?? 200);
                                                $stBadge = match(true) {
                                                    $st >= 200 && $st < 300 => 'bg-success-subtle text-success border border-success-subtle',
                                                    $st === 403 || $st === 429 => 'bg-danger text-white',
                                                    $st >= 400 => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                                    default => 'bg-secondary text-white',
                                                };
                                            @endphp
                                            <span class="badge {{ $stBadge }} bn-digit font-tabular px-2 py-0.5" style="font-size: 9.5px; border-radius: 4px;">{{ $st }}</span>
                                        </td>
                                        <td class="pe-3 text-end">
                                            <a href="{{ url($urlRow->url_path) }}" target="_blank" class="sec-action-btn sec-action-btn-view shadow-xs" title="Open Route">
                                                <i class="fas fa-arrow-up-right-from-square" style="font-size: 10px;"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">No specific URL routes aggregated yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= 4. ASSOCIATED ORDERS ================= -->
        @if(isset($orders) && count($orders) > 0)
            <div class="sec-card mb-3 p-0 overflow-hidden">
                <div class="py-1 px-3 bg-white border-bottom d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2">
                    <div class="d-flex align-items-center">
                        <span class="card-title-icon icon-soft-green"><i class="fas fa-bag-shopping"></i></span>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 13px;">
                                Orders Generated from IP <span class="badge bg-light text-secondary border font-tabular ms-1">{{ count($orders) }}</span>
                            </h6>
                            <small class="text-muted d-block" style="font-size: 11px;">Cross-reference customer phones, checkout names, and risk indicators</small>
                        </div>
                    </div>
                    @if(Route::has('admin.security.orders.clusters'))
                        <a href="{{ route('admin.security.orders.clusters', ['search' => $ip]) }}" class="btn btn-sm btn-link text-primary text-decoration-none fw-semibold font-tabular align-self-end align-self-sm-center" style="font-size: 11px;">View Clusters &rarr;</a>
                    @endif
                </div>
                <div class="sec-table-container">
                    <table class="table sec-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Order #</th>
                                <th>Customer Name</th>
                                <th>Customer Phone</th>
                                <th>Order Amount</th>
                                <th>Source</th>
                                <th>Security Status</th>
                                <th>Date Placed</th>
                                <th class="pe-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $ord)
                                <tr>
                                    <td class="ps-3 fw-bold text-dark bn-digit font-tabular">
                                        #{{ $ord->order_number ?? $ord->id }}
                                    </td>
                                    <td>{{ $ord->customer_name ?? ($ord->customer?->name ?? 'Guest Customer') }}</td>
                                    <td class="bn-digit font-tabular">{{ $ord->customer_phone ?? 'N/A' }}</td>
                                    <td class="fw-bold text-dark bn-digit font-tabular">৳{{ number_format($ord->grand_total ?? $ord->total ?? $ord->total_amount ?? 0, 2) }}</td>
                                    <td>
                                        <span class="badge {{ ($ord->order_source ?? 'online') === 'pos' ? 'bg-info-subtle text-info border' : 'bg-light text-secondary border' }} px-2 py-0.5" style="font-size: 9.5px;">
                                            {{ strtoupper($ord->order_source ?? 'online') }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $secStatus = $ord->security_review_status ?? 'clean';
                                            $badgeClass = match($secStatus) {
                                                'under_review' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                                'flagged_spam', 'cancelled_fraud' => 'bg-danger text-white',
                                                'approved_by_admin' => 'bg-success text-white',
                                                default => 'bg-light text-dark border',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} px-2 py-0.5" style="font-size: 9.5px; border-radius: 4px;">
                                            {{ str_replace('_', ' ', ucfirst($secStatus)) }}
                                        </span>
                                    </td>
                                    <td class="text-muted small bn-digit font-tabular">{{ $ord->created_at ? $ord->created_at->format('M d, Y h:i A') : 'N/A' }}</td>
                                    <td class="pe-3 text-end">
                                        @if(Route::has('admin.security.orders.show'))
                                            <a href="{{ route('admin.security.orders.show', $ord->id) }}" class="btn btn-light border btn-compact py-0 px-2 text-primary shadow-xs" style="height: 26px;" title="Security Review">
                                                <i class="fas fa-shield-halved me-1"></i> Review
                                            </a>
                                        @elseif(Route::has('admin.orders.show'))
                                            <a href="{{ route('admin.orders.show', $ord->id) }}" class="btn btn-light border btn-compact py-0 px-2 text-secondary shadow-xs" style="height: 26px;" title="View Order">
                                                <i class="fas fa-eye me-1"></i> View
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- ================= 5. RECENT 50 ACTIVITY TIMELINE TABLE (Responsive Header Fixed) ================= -->
        <div class="sec-card mb-3 p-0 overflow-hidden">
            <div class="py-1 px-3 bg-white border-bottom d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2">
                <div class="d-flex align-items-center">
                    <span class="card-title-icon icon-soft-purple"><i class="fas fa-timeline"></i></span>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 13px;">
                            Recent Activity<span class="text-muted fw-normal font-tabular" style="font-size: 11.5px;">(Last 50 Events)</span>
                        </h6>
                        <small class="text-muted d-block mt-0.5" style="font-size: 11px;">Forensic Request Timeline</small>
                    </div>
                </div>
                <span class="badge bg-light text-secondary border font-tabular px-2.5 py-1 align-self-start align-self-sm-center" style="font-size: 10px;">
                    Showing up to 50 logs
                </span>
            </div>
            <div class="sec-table-container">
                <table class="table sec-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3" style="width: 130px;">Timestamp</th>
                            <th style="width: 80px;">Method</th>
                            <th>URL Path</th>
                            <th class="text-center" style="width: 80px;">Status</th>
                            <th style="width: 105px;">Activity Type</th>
                            <th class="text-center" style="width: 70px;">Risk</th>
                            <th class="pe-3">User Agent Snippet</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($timeline ?? [] as $event)
                            <tr class="{{ ($event->security_status ?? '') === 'blocked' ? 'table-danger bg-opacity-25' : '' }}">
                                <td class="ps-3 text-muted bn-digit font-tabular" style="font-size: 11px;">
                                    <strong class="text-dark d-block">{{ !empty($event->created_at) ? $event->created_at->format('H:i:s') : 'N/A' }}</strong>
                                    <small class="text-muted" style="font-size: 9.5px;">{{ !empty($event->created_at) ? $event->created_at->format('Y-m-d') : '' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-1.5 py-0.5 font-monospace" style="font-size: 9.5px;">{{ $event->request_method ?? 'GET' }}</span>
                                </td>
                                <td class="text-dark fw-semibold text-break" style="max-width: 260px; font-size: 11.5px;">
                                    {{ $event->url_path ?? '/' }}
                                </td>
                                <td class="text-center">
                                    @php
                                        $st = (int) ($event->response_status ?? 200);
                                        $stClass = match(true) {
                                            $st >= 200 && $st < 300 => 'bg-success-subtle text-success border border-success-subtle',
                                            $st === 403 || $st === 429 => 'bg-danger text-white',
                                            $st >= 400 => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                            default => 'bg-light text-dark border',
                                        };
                                    @endphp
                                    <span class="badge {{ $stClass }} bn-digit font-tabular px-2 py-0.5" style="font-size: 9.5px; border-radius: 4px;">{{ $st }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border px-2 py-0.5" style="font-size: 9.5px;">
                                        {{ $event->activity_type ?? 'traffic' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if(($event->risk_score ?? 0) > 0)
                                        <span class="badge {{ $event->risk_score >= 50 ? 'bg-danger text-white' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' }} bn-digit font-tabular px-2 py-0.5" style="font-size: 9.5px; border-radius: 4px;">
                                            {{ $event->risk_score }}
                                        </span>
                                    @else
                                        <span class="text-muted small bn-digit font-tabular">0</span>
                                    @endif
                                </td>
                                <td class="pe-3 text-muted small text-truncate" style="max-width: 220px; font-size: 11px;" title="{{ $event->user_agent }}">
                                    {{ $event->user_agent ?? 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted small">No request history found for this IP.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ================= 6. MODAL: BLOCK THIS IP ================= -->
<div class="modal fade sec-modal-scope" id="modalBlockIpAction" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 8px !important;">
            <div class="modal-header py-1 px-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-danger d-flex align-items-center gap-1.5 mb-0" style="font-size: 13px;">
                    <i class="fas fa-ban"></i> Block IP: {{ $ip }}
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formExecuteBlockIp" method="POST" action="javascript:void(0);">
                @csrf
                <div class="modal-body p-3">
                    <input type="hidden" name="ip_address" value="{{ $ip }}">
                    
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark mb-1">Block Type</label>
                            <select class="form-select form-select-sm" name="block_type" id="detail_block_type" required>
                                <option value="temporary">Temporary</option>
                                <option value="permanent">Permanent</option>
                            </select>
                        </div>
                        <div class="col-6" id="detail_duration_wrapper">
                            <label class="form-label small fw-bold text-dark mb-1">Duration</label>
                            <select class="form-select form-select-sm" name="duration_minutes" id="detail_duration_minutes">
                                <option value="60">1 Hour</option>
                                <option value="360">6 Hours</option>
                                <option value="1440" selected>24 Hours (1 Day)</option>
                                <option value="10080">7 Days</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold text-dark mb-1">Reason <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" id="detail_block_reason" name="block_reason" required value="Threat mitigation triggered from IP audit review">
                    </div>

                    <div class="mb-1">
                        <label class="form-label small fw-bold text-dark mb-1">Admin Audit Note</label>
                        <textarea class="form-control form-control-sm" id="detail_admin_note" name="admin_note" rows="2" placeholder="Note regarding this security incident..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-compact shadow-xs fw-bold px-3" id="btn-submit-detail-block">Enforce Block</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= 7. MINIMAL MODAL CONFIRMATION (Screenshot Reference) ================= -->
<div class="modal fade sec-modal-scope" id="detailConfirmMinimalModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content minimal-modal-card">
            <h6 class="minimal-modal-title" id="detailConfirmModalTitle">Are you sure?</h6>
            <p class="minimal-modal-subtext" id="detailConfirmModalMessage">
                This change will be enforced immediately.
            </p>
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn-minimal-action" id="btnExecuteDetailConfirm">Confirm</button>
                <button type="button" class="btn-minimal-cancel" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- ================= 8. MINIMAL NOTICE MODAL ================= -->
<div class="modal fade sec-modal-scope" id="detailNoticeMinimalModal" tabindex="-1" aria-hidden="true" style="z-index: 108600;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content minimal-modal-card">
            <h6 class="minimal-modal-title" id="detailNoticeModalTitle">Notification</h6>
            <p class="minimal-modal-subtext" id="detailNoticeModalMessage" style="margin-bottom: 16px !important;"></p>
            <div class="d-flex align-items-center">
                <button type="button" class="btn-minimal-action" style="background-color: #2563eb !important; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.28) !important;" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = '{{ csrf_token() }}';
    const blockType = document.getElementById('detail_block_type');
    const durWrap = document.getElementById('detail_duration_wrapper');
    let pendingConfirmAction = null;

    // Minimal Alert Function (Replaces native browser alert)
    function showAppNotice(msg, title = 'Notification') {
        const titleEl = document.getElementById('detailNoticeModalTitle');
        const msgEl   = document.getElementById('detailNoticeModalMessage');
        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = msg;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('detailNoticeMinimalModal')).show();
    }

    // Minimal Confirm Function (Replaces native browser confirm)
    function showAppConfirm(title, msg, onConfirm, btnText = 'Confirm', btnColor = '#c93424') {
        const titleEl = document.getElementById('detailConfirmModalTitle');
        const msgEl   = document.getElementById('detailConfirmModalMessage');
        const btn     = document.getElementById('btnExecuteDetailConfirm');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.textContent = msg;
        if (btn) {
            btn.innerText = btnText;
            btn.style.backgroundColor = btnColor;
        }

        pendingConfirmAction = onConfirm;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('detailConfirmMinimalModal')).show();
    }

    document.getElementById('btnExecuteDetailConfirm')?.addEventListener('click', function() {
        bootstrap.Modal.getInstance(document.getElementById('detailConfirmMinimalModal'))?.hide();
        if (typeof pendingConfirmAction === 'function') {
            pendingConfirmAction();
            pendingConfirmAction = null;
        }
    });

    if (blockType && durWrap) {
        blockType.addEventListener('change', function() {
            durWrap.style.display = this.value === 'permanent' ? 'none' : 'block';
        });
    }

    // IP Block Action
    const blockForm = document.getElementById('formExecuteBlockIp');
    if (blockForm) {
        blockForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-detail-block');
            btn.disabled = true;
            btn.innerText = 'Enforcing...';

            const payload = {
                ip_address: '{{ $ip }}',
                block_type: document.getElementById('detail_block_type').value,
                duration_minutes: document.getElementById('detail_duration_minutes').value,
                block_reason: document.getElementById('detail_block_reason').value,
                admin_note: document.getElementById('detail_admin_note').value,
            };

            const storeUrl = @json(Route::has('admin.security.blocking.ips.store') ? route('admin.security.blocking.ips.store') : (Route::has('admin.security.ips.block') ? route('admin.security.ips.block') : url('/admin/security/blocking/ips/store')));

            fetch(storeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(async r => {
                const data = await r.json();
                if (!r.ok) throw new Error(data.message || 'The server could not complete the request.');
                return data;
            })
            .then(res => {
                const modalEl = document.getElementById('modalBlockIpAction');
                if (modalEl) bootstrap.Modal.getInstance(modalEl)?.hide();

                showAppNotice(res.message || (res.success ? 'This IP has been blocked successfully.' : 'Unable to block this IP. Please try again.'), res.success ? 'Block Enforced' : 'Block Failed');
                if (res.success) {
                    setTimeout(() => location.reload(), 900);
                } else {
                    btn.disabled = false;
                    btn.innerText = 'Enforce Block';
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerText = 'Enforce Block';
                showAppNotice('Request failed. Check network connection.', 'Error');
            });
        });
    }

    // IP Unblock Action
    const unblockBtn = document.getElementById('btn-unblock-this-ip');
    if (unblockBtn) {
        unblockBtn.addEventListener('click', function() {
            const ip = this.getAttribute('data-ip');
            showAppConfirm(
                'Lift IP Restriction?',
                `Are you sure you want to lift the firewall restriction for IP address [${ip}]?`,
                function() {
                    unblockBtn.disabled = true;

                    const unblockUrl = @json(Route::has('admin.security.ips.unblock') ? route('admin.security.ips.unblock') : url('/admin/security/ips/unblock'));

                    fetch(unblockUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ ip_address: ip, note: 'Unblocked via IP Details Page' })
                    })
                    .then(async r => {
                        const data = await r.json();
                        if (!r.ok) throw new Error(data.message || 'The server could not complete the request.');
                        return data;
                    })
                    .then(res => {
                        showAppNotice(res.message || (res.success ? 'The IP restriction has been lifted.' : 'Unable to lift this restriction. Please try again.'), res.success ? 'Restriction Lifted' : 'Action Failed');
                        if (res.success) {
                            setTimeout(() => location.reload(), 900);
                        } else {
                            unblockBtn.disabled = false;
                        }
                    })
                    .catch(() => {
                        unblockBtn.disabled = false;
                        showAppNotice('Failed to communicate with firewall service.', 'Error');
                    });
                },
                'Lift Restriction',
                '#16a34a'
            );
        });
    }
});
</script>
@endsection