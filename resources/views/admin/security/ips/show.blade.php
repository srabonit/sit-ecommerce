
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

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* =========================================================
       IP SECURITY DETAILS - DESIGN SYSTEM
    ========================================================= */
    #security-ip-details-wrapper {
        --sec-primary: #2563eb;
        --sec-primary-soft: #eff6ff;
        --sec-primary-border: #bfdbfe;
        --sec-success: #16a34a;
        --sec-danger: #dc2626;
        --sec-warning: #d97706;
        --sec-text: #1e293b;
        --sec-heading: #0f172a;
        --sec-muted: #64748b;
        --sec-border: #e2e8f0;
        --sec-border-light: #f1f5f9;
        --sec-surface: #ffffff;
        --sec-background: #f8fafc;
        --sec-radius: 5px;
        --sec-shadow: 0 1px 3px rgba(15, 23, 42, .045);

        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        color: var(--sec-text);
        background: var(--sec-background);
        min-height: 100vh;
        font-size: 12px;
        letter-spacing: -.01em;
    }

    #security-ip-details-wrapper *,
    .sec-modal-scope * {
        box-sizing: border-box;
    }

    #security-ip-details-wrapper i,
    .sec-modal-scope i {
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome" !important;
        font-style: normal;
        font-weight: 900;
        line-height: 1;
        letter-spacing: normal;
    }

    #security-ip-details-wrapper .far,
    .sec-modal-scope .far {
        font-weight: 400 !important;
    }

    #security-ip-details-wrapper .fa-brands,
    .sec-modal-scope .fa-brands {
        font-family: "Font Awesome 6 Brands", "Font Awesome 5 Brands" !important;
        font-weight: 400 !important;
    }

    #security-ip-details-wrapper .min-w-0 {
        min-width: 0;
    }

    #security-ip-details-wrapper .sec-card {
        background: var(--sec-surface);
        border: 1px solid var(--sec-border);
        border-radius: var(--sec-radius) !important;
        box-shadow: var(--sec-shadow);
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    #security-ip-details-wrapper .sec-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 3px 10px rgba(15, 23, 42, .045);
    }

    #security-ip-details-wrapper .font-tabular,
    #security-ip-details-wrapper .bn-digit {
        font-family: 'Inter', sans-serif !important;
        font-variant-numeric: tabular-nums;
        font-feature-settings: "tnum";
    }

    /* Header */
    #security-ip-details-wrapper .sec-page-title {
        margin: 0;
        color: var(--sec-heading);
        font-size: 16px;
        font-weight: 800;
        line-height: 1.35;
        letter-spacing: -.035em;
        overflow-wrap: anywhere;
    }

    #security-ip-details-wrapper .sec-page-subtitle {
        color: var(--sec-muted);
        font-size: 10.5px;
        font-weight: 500;
        line-height: 1.5;
    }

    #security-ip-details-wrapper .sec-btn-square,
    #security-ip-details-wrapper .sec-header-icon-box {
        width: 36px;
        height: 36px;
        min-width: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: var(--sec-radius);
        font-size: 13px;
    }

    #security-ip-details-wrapper .sec-btn-square {
        background: #fff;
        border: 1px solid var(--sec-border);
        color: #475569;
        text-decoration: none;
        transition: .18s ease;
    }

    #security-ip-details-wrapper .sec-btn-square:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: var(--sec-heading);
    }

    #security-ip-details-wrapper .sec-header-icon-box {
        color: var(--sec-primary);
        background: var(--sec-primary-soft);
        border: 1px solid var(--sec-primary-border);
    }

    #security-ip-details-wrapper .protocol-tag {
        display: inline-flex;
        align-items: center;
        padding: 3px 6px;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        font-size: 9px;
        font-weight: 800;
        line-height: 1.2;
        letter-spacing: .035em;
    }

    #security-ip-details-wrapper .badge-status-protected,
    #security-ip-details-wrapper .badge-status-blocked {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 20px;
        font-size: 9px;
        font-weight: 800;
        line-height: 1.3;
        white-space: nowrap;
    }

    #security-ip-details-wrapper .badge-status-protected {
        color: #047857;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
    }

    #security-ip-details-wrapper .badge-status-blocked {
        color: #dc2626;
        background: #fef2f2;
        border: 1px solid #fecaca;
    }

    /* Buttons */
    #security-ip-details-wrapper .btn-compact {
        min-height: 32px;
        padding: 6px 11px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        border-radius: var(--sec-radius) !important;
        font-size: 10.5px !important;
        font-weight: 700 !important;
        line-height: 1.3 !important;
        white-space: nowrap;
        transition: .18s ease;
    }

    #security-ip-details-wrapper .btn {
        border-radius: var(--sec-radius) !important;
    }

    #security-ip-details-wrapper .btn:focus,
    .sec-modal-scope .btn:focus {
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .12) !important;
    }

    #security-ip-details-wrapper .header-action-btns-wrap {
        flex-shrink: 0;
    }

    /* Metric Cards */
    #security-ip-details-wrapper .metric-card {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        min-height: 88px;
        padding: 12px;
        background: #fff;
        border: 1px solid var(--sec-border);
        border-radius: var(--sec-radius);
        box-shadow: var(--sec-shadow);
        transition: .18s ease;
        overflow: hidden;
    }

    #security-ip-details-wrapper .metric-card:hover {
        transform: translateY(-2px);
        border-color: #cbd5e1;
        box-shadow: 0 5px 14px rgba(15, 23, 42, .06);
    }

    #security-ip-details-wrapper .stat-label-title {
        color: var(--sec-muted);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .055em;
        text-transform: uppercase;
        line-height: 1.35;
    }

    #security-ip-details-wrapper .stat-val-num {
        margin: 0;
        color: var(--sec-heading);
        font-size: 19px;
        font-weight: 800;
        line-height: 1.3;
        letter-spacing: -.045em;
        overflow-wrap: anywhere;
    }

    #security-ip-details-wrapper .metric-icon-square {
        width: 30px;
        height: 30px;
        min-width: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: var(--sec-radius);
        font-size: 12px;
    }

    #security-ip-details-wrapper .icon-soft-blue {
        color: #2563eb;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
    }

    #security-ip-details-wrapper .icon-soft-teal {
        color: #0d9488;
        background: #f0fdfa;
        border: 1px solid #99f6e4;
    }

    #security-ip-details-wrapper .icon-soft-green {
        color: #16a34a;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
    }

    #security-ip-details-wrapper .icon-soft-amber {
        color: #d97706;
        background: #fffbeb;
        border: 1px solid #fde68a;
    }

    #security-ip-details-wrapper .icon-soft-red {
        color: #dc2626;
        background: #fef2f2;
        border: 1px solid #fecaca;
    }

    #security-ip-details-wrapper .icon-soft-purple {
        color: #9333ea;
        background: #faf5ff;
        border: 1px solid #e9d5ff;
    }

    #security-ip-details-wrapper .card-title-icon {
        width: 28px;
        height: 28px;
        min-width: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-right: 9px;
        border-radius: var(--sec-radius);
        font-size: 11px;
    }

    /* Section Headers */
    #security-ip-details-wrapper .sec-section-heading {
        min-height: 42px;
        padding: 7px 13px;
        background: #fff;
        border-bottom: 1px solid var(--sec-border-light);
    }

    #security-ip-details-wrapper .sec-section-title {
        margin: 0;
        color: var(--sec-heading);
        font-size: 12px;
        font-weight: 800;
    }

    /* Device Information */
    #security-ip-details-wrapper .sec-info-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 11px;
    }

    #security-ip-details-wrapper .sec-info-table td {
        padding: 8px 5px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: top;
    }

    #security-ip-details-wrapper .sec-info-table tr:last-child td {
        border-bottom: 0;
    }

    #security-ip-details-wrapper .sec-info-table td:first-child {
        width: 140px;
        min-width: 115px;
        color: var(--sec-muted);
        font-weight: 600;
    }

    #security-ip-details-wrapper .sec-info-table td:last-child {
        color: var(--sec-text);
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    /* Data Tables */
    #security-ip-details-wrapper .sec-table-container {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }

    #security-ip-details-wrapper .sec-table-container::-webkit-scrollbar {
        height: 5px;
    }

    #security-ip-details-wrapper .sec-table-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 5px;
    }

    #security-ip-details-wrapper .sec-table {
        width: 100%;
        min-width: 650px;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #security-ip-details-wrapper .sec-table thead th {
        padding: 10px 12px !important;
        color: #475569 !important;
        background: #f8fafc !important;
        border-bottom: 1px solid var(--sec-border) !important;
        font-size: 9px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: .055em !important;
        white-space: nowrap !important;
    }

    #security-ip-details-wrapper .sec-table tbody td {
        padding: 10px 12px !important;
        color: var(--sec-text);
        background: #fff;
        border-bottom: 1px solid #f1f5f9 !important;
        vertical-align: middle !important;
        font-size: 10.5px !important;
        white-space: nowrap !important;
    }

    #security-ip-details-wrapper .sec-table tbody tr:last-child td {
        border-bottom: 0 !important;
    }

    #security-ip-details-wrapper .sec-table tbody tr:hover td {
        background: #f8fafc;
    }

    #security-ip-details-wrapper .sec-table .text-break {
        white-space: normal !important;
        overflow-wrap: anywhere;
    }

    #security-ip-details-wrapper .sec-action-btn {
        width: 28px;
        height: 28px;
        min-width: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: 1px solid #bfdbfe;
        border-radius: var(--sec-radius);
        background: #eff6ff;
        color: #2563eb;
        font-size: 10px;
        text-decoration: none;
        transition: .18s ease;
    }

    #security-ip-details-wrapper .sec-action-btn:hover {
        color: #fff;
        background: #2563eb;
        border-color: #2563eb;
    }

    /* Restriction Banner */
    #security-ip-details-wrapper .sec-restriction-banner {
        padding: 13px 15px;
        background: #fff7f7;
        border: 1px solid #fecaca;
        border-radius: var(--sec-radius);
    }

    /* Form Controls */
    #security-ip-details-wrapper .form-control,
    #security-ip-details-wrapper .form-select,
    .sec-modal-scope .form-control,
    .sec-modal-scope .form-select {
        min-height: 34px;
        padding: 6px 10px;
        color: #1e293b;
        background-color: #fff;
        border: 1px solid #cbd5e1;
        border-radius: var(--sec-radius) !important;
        font-family: inherit;
        font-size: 11px;
        font-weight: 500;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #security-ip-details-wrapper .form-control:focus,
    #security-ip-details-wrapper .form-select:focus,
    .sec-modal-scope .form-control:focus,
    .sec-modal-scope .form-select:focus {
        border-color: var(--sec-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .11);
        outline: none;
    }

    .sec-modal-scope .form-label {
        font-size: 10.5px;
    }

    /* Modal */
    .sec-modal-scope {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
    }

    .sec-modal-scope .modal-content {
        border: 1px solid #e2e8f0;
        border-radius: 5px !important;
        overflow: hidden;
    }

    .sec-modal-scope .modal-header,
    .sec-modal-scope .modal-footer {
        background: #f8fafc;
    }

    .sec-modal-scope .modal-title {
        font-size: 12px !important;
    }

    .sec-modal-scope .minimal-modal-card {
        padding: 24px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 5px !important;
        box-shadow: 0 20px 45px rgba(15, 23, 42, .16);
    }

    .sec-modal-scope .minimal-modal-title {
        margin: 0 0 7px;
        color: #0f172a;
        font-size: 15px;
        font-weight: 800;
        letter-spacing: -.025em;
    }

    .sec-modal-scope .minimal-modal-subtext {
        margin: 0 0 20px;
        color: #64748b;
        font-size: 11.5px;
        line-height: 1.6;
    }

    .sec-modal-scope .btn-minimal-action {
        min-height: 34px;
        padding: 7px 17px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: #dc2626;
        border: 0;
        border-radius: 5px;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        transition: .18s ease;
    }

    .sec-modal-scope .btn-minimal-action:hover {
        filter: brightness(.92);
    }

    .sec-modal-scope .btn-minimal-cancel {
        min-height: 34px;
        padding: 7px 13px;
        color: #64748b;
        background: transparent;
        border: 0;
        border-radius: 5px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
    }

    .sec-modal-scope .btn-minimal-cancel:hover {
        color: #0f172a;
        background: #f8fafc;
    }

    /* Responsive */
    @media (max-width: 991.98px) {
        #security-ip-details-wrapper .sec-table {
            min-width: 700px;
        }
    }

    @media (max-width: 767.98px) {
        #security-ip-details-wrapper {
            padding-top: 10px !important;
        }

        #security-ip-details-wrapper .header-top-wrap {
            align-items: stretch !important;
        }

        #security-ip-details-wrapper .header-action-btns-wrap {
            width: 100%;
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 7px !important;
        }

        #security-ip-details-wrapper .header-action-btns-wrap .btn {
            width: 100%;
            min-height: 34px;
            padding-left: 7px !important;
            padding-right: 7px !important;
            font-size: 10px !important;
        }

        #security-ip-details-wrapper .sec-page-title {
            font-size: 14px;
        }

        #security-ip-details-wrapper .metric-card {
            min-height: 78px;
            padding: 10px;
        }

        #security-ip-details-wrapper .stat-val-num {
            font-size: 17px;
        }

        #security-ip-details-wrapper .stat-label-title {
            font-size: 8px;
        }

        #security-ip-details-wrapper .metric-icon-square {
            width: 27px;
            height: 27px;
            min-width: 27px;
            font-size: 11px;
        }

        #security-ip-details-wrapper .sec-info-table td {
            padding: 7px 3px;
        }

        #security-ip-details-wrapper .sec-info-table td:first-child {
            width: 115px;
            min-width: 105px;
        }

        #security-ip-details-wrapper .sec-section-heading {
            padding: 8px 10px;
        }

        #security-ip-details-wrapper .sec-table thead th,
        #security-ip-details-wrapper .sec-table tbody td {
            padding: 8px 10px !important;
        }

        .sec-modal-scope .minimal-modal-card {
            padding: 20px;
        }
    }

    @media (max-width: 420px) {
        #security-ip-details-wrapper .container-fluid {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }

        #security-ip-details-wrapper .metric-card {
            min-height: 74px;
        }

        #security-ip-details-wrapper .stat-val-num {
            font-size: 16px;
        }

        #security-ip-details-wrapper .sec-info-table {
            font-size: 10px;
        }

        #security-ip-details-wrapper .sec-info-table td:first-child {
            width: 105px;
            min-width: 95px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #security-ip-details-wrapper *,
        .sec-modal-scope * {
            transition-duration: .01ms !important;
            animation-duration: .01ms !important;
        }
    }
</style>

<div class="container-fluid px-2 px-sm-3 px-md-4 py-3" id="security-ip-details-wrapper">
    <div class="mx-auto" style="max-width: 1600px;">

        {{-- ================= HEADER ================= --}}
        <header class="sec-card p-3 mb-3">
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 header-top-wrap">

                <div class="d-flex align-items-center gap-2 min-w-0">
                    <a href="{{ Route::has('admin.security.ips.index') ? route('admin.security.ips.index') : (Route::has('admin.security.blocking.ips') ? route('admin.security.blocking.ips') : url('/admin/security/blocking/ips')) }}"
                       class="sec-btn-square"
                       title="Back to IP Directory">
                        <i class="fas fa-arrow-left"></i>
                    </a>

                    <span class="sec-header-icon-box">
                        <i class="fas fa-network-wired"></i>
                    </span>

                    <div class="min-w-0">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <h4 class="sec-page-title bn-digit font-tabular">{{ $ip }}</h4>

                            <span class="protocol-tag">
                                {{ strtoupper($latestLog?->ip_version ?? 'IPv4') }}
                            </span>

                            @if($isBlocked)
                                <span class="badge-status-blocked">
                                    <i class="fas fa-ban"></i> Blocked
                                </span>
                            @else
                                <span class="badge-status-protected">
                                    <i class="fas fa-shield-alt"></i> Active
                                </span>
                            @endif
                        </div>

                        <p class="sec-page-subtitle mb-0">
                            <i class="fas fa-globe me-1"></i>
                            <strong class="text-dark">{{ $latestLog?->country ?: 'Unknown' }}</strong>
                            <span class="mx-1">·</span>
                            First seen
                            {{ !empty($summary?->first_seen) ? \Carbon\Carbon::parse($summary->first_seen)->diffForHumans() : 'N/A' }}
                        </p>
                    </div>
                </div>

                <div class="header-action-btns-wrap d-flex align-items-center gap-2">
                    @if($isBlocked)
                        <button type="button"
                                class="btn btn-outline-success btn-compact shadow-sm px-3"
                                id="btn-unblock-this-ip"
                                data-ip="{{ $ip }}">
                            <i class="fas fa-lock-open"></i> Lift Block
                        </button>
                    @else
                        <button type="button"
                                class="btn btn-danger btn-compact shadow-sm px-3"
                                data-bs-toggle="modal"
                                data-bs-target="#modalBlockIpAction">
                            <i class="fas fa-ban"></i> Block This IP
                        </button>
                    @endif

                    <a href="{{ Route::has('admin.security.ips.index') ? route('admin.security.ips.index') : (Route::has('admin.security.blocking.ips') ? route('admin.security.blocking.ips') : url('/admin/security/blocking/ips')) }}"
                       class="btn btn-outline-secondary btn-compact shadow-sm bg-white text-dark">
                        <i class="fas fa-list text-primary"></i> IP Directory
                    </a>
                </div>
            </div>
        </header>

        {{-- ================= BLOCK RESTRICTION NOTICE ================= --}}
        @if($isBlocked)
            <div class="sec-restriction-banner mb-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded d-flex align-items-center justify-content-center bg-danger text-white"
                         style="width:36px;height:36px;min-width:36px;">
                        <i class="fas fa-shield-virus"></i>
                    </div>

                    <div>
                        <h6 class="fw-bold mb-1 text-danger" style="font-size:12px;">
                            Firewall Enforcement Active on this IP
                        </h6>

                        <p class="mb-0 text-danger" style="font-size:10.5px;line-height:1.6;">
                            Restriction:
                            <strong>{{ ucfirst($blockDetails['type'] ?? 'Firewall Restriction') }}</strong>
                            <span class="mx-1">·</span>
                            Reason:
                            <strong>{{ $blockDetails['reason'] ?? 'Manually restricted by administrator' }}</strong>
                            <span class="mx-1">·</span>
                            Expires:
                            <strong>{{ $blockDetails['expires_at'] ?? 'Permanent' }}</strong>
                        </p>
                    </div>
                </div>

                @if(!empty($blockRecord->admin_note))
                    <span class="small bg-white px-2 py-1 rounded border border-danger-subtle text-danger"
                          style="font-size:10px;">
                        <i class="fas fa-sticky-note me-1"></i>
                        Note: {{ $blockRecord->admin_note }}
                    </span>
                @endif
            </div>
        @endif

        {{-- ================= SUMMARY METRICS ================= --}}
        <div class="row g-2 mb-3">

            <div class="col-6 col-md-4 col-xl-2">
                <div class="metric-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="stat-label-title">Requests</span>
                        <span class="metric-icon-square icon-soft-blue">
                            <i class="fas fa-network-wired"></i>
                        </span>
                    </div>
                    <h4 class="stat-val-num bn-digit">{{ number_format($summary?->total_requests ?? 0) }}</h4>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="metric-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="stat-label-title">Orders</span>
                        <span class="metric-icon-square icon-soft-green">
                            <i class="fas fa-shopping-bag"></i>
                        </span>
                    </div>
                    <h4 class="stat-val-num text-success bn-digit">{{ number_format($summary?->total_orders ?? 0) }}</h4>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="metric-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="stat-label-title">Auth Fails</span>
                        <span class="metric-icon-square icon-soft-red">
                            <i class="fas fa-key"></i>
                        </span>
                    </div>
                    <h4 class="stat-val-num {{ ($summary?->failed_logins ?? 0) > 0 ? 'text-danger' : 'text-dark' }} bn-digit">
                        {{ number_format($summary?->failed_logins ?? 0) }}
                    </h4>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="metric-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="stat-label-title">Blocked Hits</span>
                        <span class="metric-icon-square icon-soft-amber">
                            <i class="fas fa-ban"></i>
                        </span>
                    </div>
                    <h4 class="stat-val-num text-danger bn-digit">
                        {{ number_format($summary?->blocked_requests ?? 0) }}
                    </h4>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="metric-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="stat-label-title">Risk Score</span>
                        <span class="metric-icon-square icon-soft-teal">
                            <i class="fas fa-shield-alt"></i>
                        </span>
                    </div>
                    <h4 class="stat-val-num bn-digit">
                        {{ $riskScore }}<span class="text-muted fw-normal" style="font-size:11px;">/100</span>
                    </h4>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="metric-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="stat-label-title">Forms</span>
                        <span class="metric-icon-square icon-soft-purple">
                            <i class="fas fa-envelope-open-text"></i>
                        </span>
                    </div>
                    <h4 class="stat-val-num text-primary bn-digit">
                        {{ number_format($summary?->total_forms ?? 0) }}
                    </h4>
                </div>
            </div>

        </div>

        {{-- ================= DEVICE & TARGETED ROUTES ================= --}}
        <div class="row g-3 mb-3">

            {{-- Device Fingerprint --}}
            <div class="col-12 col-lg-5">
                <div class="sec-card h-100 overflow-hidden">

                    <div class="sec-section-heading d-flex align-items-center">
                        <span class="card-title-icon icon-soft-blue">
                            <i class="fas fa-desktop"></i>
                        </span>
                        <h6 class="sec-section-title">Device & Network Details</h6>
                    </div>

                    <div class="p-3">
                        <table class="sec-info-table">
                            <tbody>
                                <tr>
                                    <td>IP Address</td>
                                    <td class="fw-bold bn-digit">{{ $ip }}</td>
                                </tr>

                                <tr>
                                    <td>Country / Region</td>
                                    <td>
                                        <i class="fas fa-globe text-secondary me-1"></i>
                                        {{ $latestLog?->country ?: 'Unknown' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td>Device Category</td>
                                    <td>
                                        <i class="fas {{ (($latestLog?->device_type ?? 'Desktop') === 'Mobile' ? 'fa-mobile-screen' : (($latestLog?->device_type ?? 'Desktop') === 'Tablet' ? 'fa-tablet-screen' : 'fa-laptop')) }} me-1 text-primary"></i>
                                        {{ $latestLog?->device_type ?? 'Desktop' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td>Browser & Platform</td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1" style="font-size:9px;">
                                            {{ $latestLog?->browser ?? 'Browser' }}
                                        </span>
                                        <span class="text-muted mx-1">on</span>
                                        <strong>{{ $latestLog?->platform ?? 'OS' }}</strong>
                                    </td>
                                </tr>

                                <tr>
                                    <td>First Seen</td>
                                    <td class="bn-digit">
                                        {{ !empty($summary?->first_seen) ? \Carbon\Carbon::parse($summary->first_seen)->format('Y-m-d H:i:s') : 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td>Last Activity</td>
                                    <td class="bn-digit">
                                        {{ !empty($summary?->last_seen) ? \Carbon\Carbon::parse($summary->last_seen)->format('Y-m-d H:i:s') : 'N/A' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td>User Agent</td>
                                    <td class="text-break font-monospace text-muted" style="font-size:10px;line-height:1.6;">
                                        {{ $latestLog?->user_agent ?? 'N/A' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            {{-- Targeted Routes --}}
            <div class="col-12 col-lg-7">
                <div class="sec-card h-100 overflow-hidden">

                    <div class="sec-section-heading d-flex align-items-center">
                        <span class="card-title-icon icon-soft-teal">
                            <i class="fas fa-compass"></i>
                        </span>
                        <h6 class="sec-section-title">Targeted Routes</h6>
                    </div>

                    <div class="sec-table-container">
                        <table class="table sec-table align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-3">URL Endpoint</th>
                                    <th class="text-center" style="width:95px;">Hit Count</th>
                                    <th class="text-center" style="width:95px;">Last Status</th>
                                    <th class="pe-3 text-end" style="width:75px;">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($topUrls ?? [] as $urlRow)
                                    <tr>
                                        <td class="ps-3 text-dark fw-semibold"
                                            title="{{ $urlRow->url_path }}"
                                            style="max-width:280px;overflow:hidden;text-overflow:ellipsis;">
                                            {{ Str::limit($urlRow->url_path, 42) }}
                                        </td>

                                        <td class="text-center fw-bold bn-digit">
                                            {{ number_format($urlRow->visit_count ?? 0) }}
                                        </td>

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

                                            <span class="badge {{ $stBadge }} bn-digit px-2 py-1"
                                                  style="font-size:9px;border-radius:4px;">
                                                {{ $st }}
                                            </span>
                                        </td>

                                        <td class="pe-3 text-end">
                                            <a href="{{ url($urlRow->url_path) }}"
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               class="sec-action-btn"
                                               title="Open Route">
                                                <i class="fas fa-arrow-up-right-from-square"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="fas fa-route d-block mb-2" style="font-size:18px;"></i>
                                            No specific URL routes aggregated yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

        {{-- ================= ASSOCIATED ORDERS ================= --}}
        @if(isset($orders) && count($orders) > 0)
            <div class="sec-card mb-3 overflow-hidden">

                <div class="sec-section-heading d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2">
                    <div class="d-flex align-items-center">
                        <span class="card-title-icon icon-soft-green">
                            <i class="fas fa-shopping-bag"></i>
                        </span>

                        <div>
                            <h6 class="sec-section-title">
                                Orders Generated from IP
                                <span class="badge bg-light text-secondary border bn-digit ms-1">
                                    {{ count($orders) }}
                                </span>
                            </h6>

                            <small class="text-muted" style="font-size:10px;">
                                Cross-reference customer phones, checkout names, and risk indicators
                            </small>
                        </div>
                    </div>

                    @if(Route::has('admin.security.orders.clusters'))
                        <a href="{{ route('admin.security.orders.clusters', ['search' => $ip]) }}"
                           class="btn btn-sm btn-link text-primary text-decoration-none fw-bold align-self-end align-self-sm-center"
                           style="font-size:10px;">
                            View Clusters <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    @endif
                </div>

                <div class="sec-table-container">
                    <table class="table sec-table align-middle">
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
                                    <td class="ps-3 fw-bold text-dark bn-digit">
                                        #{{ $ord->order_number ?? $ord->id }}
                                    </td>

                                    <td>{{ $ord->customer_name ?? ($ord->customer?->name ?? 'Guest Customer') }}</td>

                                    <td class="bn-digit">{{ $ord->customer_phone ?? 'N/A' }}</td>

                                    <td class="fw-bold text-dark bn-digit">
                                        ৳{{ number_format($ord->grand_total ?? $ord->total ?? $ord->total_amount ?? 0, 2) }}
                                    </td>

                                    <td>
                                        <span class="badge {{ ($ord->order_source ?? 'online') === 'pos' ? 'bg-info-subtle text-info border' : 'bg-light text-secondary border' }} px-2 py-1"
                                              style="font-size:9px;">
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

                                        <span class="badge {{ $badgeClass }} px-2 py-1"
                                              style="font-size:9px;border-radius:4px;">
                                            {{ str_replace('_', ' ', ucfirst($secStatus)) }}
                                        </span>
                                    </td>

                                    <td class="text-muted bn-digit" style="font-size:10px;">
                                        {{ $ord->created_at ? $ord->created_at->format('M d, Y h:i A') : 'N/A' }}
                                    </td>

                                    <td class="pe-3 text-end">
                                        @if(Route::has('admin.security.orders.show'))
                                            <a href="{{ route('admin.security.orders.show', $ord->id) }}"
                                               class="btn btn-light border btn-compact py-0 px-2 text-primary"
                                               style="height:27px;">
                                                <i class="fas fa-shield-halved"></i> Review
                                            </a>
                                        @elseif(Route::has('admin.orders.show'))
                                            <a href="{{ route('admin.orders.show', $ord->id) }}"
                                               class="btn btn-light border btn-compact py-0 px-2 text-secondary"
                                               style="height:27px;">
                                                <i class="fas fa-eye"></i> View
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

        {{-- ================= RECENT ACTIVITY TIMELINE ================= --}}
        <div class="sec-card mb-3 overflow-hidden">

            <div class="sec-section-heading d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2">
                <div class="d-flex align-items-center">
                    <span class="card-title-icon icon-soft-purple">
                        <i class="fas fa-clock-rotate-left"></i>
                    </span>

                    <div>
                        <h6 class="sec-section-title">
                            Recent Activity
                            <span class="text-muted fw-normal" style="font-size:10px;">
                                (Last 50 Events)
                            </span>
                        </h6>

                        <small class="text-muted" style="font-size:10px;">
                            Forensic Request Timeline
                        </small>
                    </div>
                </div>

                <span class="badge bg-light text-secondary border bn-digit px-2 py-1 align-self-start align-self-sm-center"
                      style="font-size:9px;">
                    <i class="fas fa-list me-1"></i> Showing up to 50 logs
                </span>
            </div>

            <div class="sec-table-container">
                <table class="table sec-table align-middle">
                    <thead>
                        <tr>
                            <th class="ps-3" style="width:130px;">Timestamp</th>
                            <th style="width:80px;">Method</th>
                            <th>URL Path</th>
                            <th class="text-center" style="width:80px;">Status</th>
                            <th style="width:105px;">Activity Type</th>
                            <th class="text-center" style="width:70px;">Risk</th>
                            <th class="pe-3">User Agent Snippet</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($timeline ?? [] as $event)
                            <tr class="{{ ($event->security_status ?? '') === 'blocked' ? 'table-danger bg-opacity-25' : '' }}">
                                <td class="ps-3 text-muted bn-digit">
                                    <strong class="text-dark d-block">
                                        {{ !empty($event->created_at) ? $event->created_at->format('H:i:s') : 'N/A' }}
                                    </strong>

                                    <small class="text-muted" style="font-size:9px;">
                                        {{ !empty($event->created_at) ? $event->created_at->format('Y-m-d') : '' }}
                                    </small>
                                </td>

                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1 font-monospace"
                                          style="font-size:9px;">
                                        {{ $event->request_method ?? 'GET' }}
                                    </span>
                                </td>

                                <td class="text-dark fw-semibold text-break"
                                    style="max-width:260px;font-size:10.5px;">
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

                                    <span class="badge {{ $stClass }} bn-digit px-2 py-1"
                                          style="font-size:9px;border-radius:4px;">
                                        {{ $st }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge bg-light text-secondary border px-2 py-1"
                                          style="font-size:9px;">
                                        {{ $event->activity_type ?? 'traffic' }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    @if(($event->risk_score ?? 0) > 0)
                                        <span class="badge {{ $event->risk_score >= 50 ? 'bg-danger text-white' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' }} bn-digit px-2 py-1"
                                              style="font-size:9px;border-radius:4px;">
                                            {{ $event->risk_score }}
                                        </span>
                                    @else
                                        <span class="text-muted bn-digit">0</span>
                                    @endif
                                </td>

                                <td class="pe-3 text-muted"
                                    style="max-width:220px;font-size:10px;"
                                    title="{{ $event->user_agent }}">
                                    <div class="text-truncate" style="max-width:220px;">
                                        {{ $event->user_agent ?? 'N/A' }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-clock d-block mb-2" style="font-size:18px;"></i>
                                    No request history found for this IP.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

{{-- ================= BLOCK IP MODAL ================= --}}
<div class="modal fade sec-modal-scope"
     id="modalBlockIpAction"
     tabindex="-1"
     aria-hidden="true"
     style="z-index:108000;">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg">

            <div class="modal-header py-2 px-3 border-bottom">
                <h6 class="modal-title fw-bold text-danger d-flex align-items-center gap-2 mb-0">
                    <i class="fas fa-ban"></i>
                    Block IP: {{ $ip }}
                </h6>

                <button type="button"
                        class="btn-close btn-sm shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <form id="formExecuteBlockIp" method="POST" action="javascript:void(0);">
                @csrf

                <div class="modal-body p-3">
                    <input type="hidden" name="ip_address" value="{{ $ip }}">

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold text-dark mb-1">Block Type</label>

                            <select class="form-select form-select-sm"
                                    name="block_type"
                                    id="detail_block_type"
                                    required>
                                <option value="temporary">Temporary</option>
                                <option value="permanent">Permanent</option>
                            </select>
                        </div>

                        <div class="col-6" id="detail_duration_wrapper">
                            <label class="form-label fw-bold text-dark mb-1">Duration</label>

                            <select class="form-select form-select-sm"
                                    name="duration_minutes"
                                    id="detail_duration_minutes">
                                <option value="60">1 Hour</option>
                                <option value="360">6 Hours</option>
                                <option value="1440" selected>24 Hours (1 Day)</option>
                                <option value="10080">7 Days</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-1">
                            Reason <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               class="form-control form-control-sm"
                               id="detail_block_reason"
                               name="block_reason"
                               required
                               value="Threat mitigation triggered from IP audit review">
                    </div>

                    <div class="mb-1">
                        <label class="form-label fw-bold text-dark mb-1">
                            Admin Audit Note
                        </label>

                        <textarea class="form-control form-control-sm"
                                  id="detail_admin_note"
                                  name="admin_note"
                                  rows="3"
                                  placeholder="Note regarding this security incident..."></textarea>
                    </div>
                </div>

                <div class="modal-footer py-2 px-3 border-top d-flex justify-content-between">
                    <button type="button"
                            class="btn btn-light btn-compact border"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-danger btn-compact px-3"
                            id="btn-submit-detail-block">
                        <i class="fas fa-shield-alt me-1"></i>
                        Enforce Block
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

{{-- ================= CONFIRMATION MODAL ================= --}}
<div class="modal fade sec-modal-scope"
     id="detailConfirmMinimalModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index:108500;">

    <div class="modal-dialog modal-dialog-centered mx-auto px-2" style="max-width:400px;">
        <div class="modal-content minimal-modal-card">

            <h6 class="minimal-modal-title" id="detailConfirmModalTitle">
                Are you sure?
            </h6>

            <p class="minimal-modal-subtext" id="detailConfirmModalMessage">
                This change will be enforced immediately.
            </p>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button"
                        class="btn-minimal-action"
                        id="btnExecuteDetailConfirm">
                    Confirm
                </button>

                <button type="button"
                        class="btn-minimal-cancel"
                        data-bs-dismiss="modal">
                    Cancel
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ================= NOTICE MODAL ================= --}}
<div class="modal fade sec-modal-scope"
     id="detailNoticeMinimalModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index:108600;">

    <div class="modal-dialog modal-dialog-centered mx-auto px-2" style="max-width:380px;">
        <div class="modal-content minimal-modal-card">

            <h6 class="minimal-modal-title" id="detailNoticeModalTitle">
                Notification
            </h6>

            <p class="minimal-modal-subtext"
               id="detailNoticeModalMessage"
               style="margin-bottom:16px;"></p>

            <div class="d-flex align-items-center">
                <button type="button"
                        class="btn-minimal-action"
                        style="background:#2563eb;"
                        data-bs-dismiss="modal">
                    OK
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ================= IP SECURITY ACTIONS ================= --}}
<script>
document.addEventListener('DOMContentLoaded', function() {

    const csrfToken = '{{ csrf_token() }}';
    const blockType = document.getElementById('detail_block_type');
    const durWrap = document.getElementById('detail_duration_wrapper');

    let pendingConfirmAction = null;

    // ==========================================
    // APPLICATION NOTICE
    // ==========================================
    function showAppNotice(msg, title = 'Notification') {

        const titleEl = document.getElementById('detailNoticeModalTitle');
        const msgEl = document.getElementById('detailNoticeModalMessage');
        const modalEl = document.getElementById('detailNoticeMinimalModal');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = msg;

        if (modalEl) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    // ==========================================
    // APPLICATION CONFIRMATION
    // ==========================================
    function showAppConfirm(title, msg, onConfirm, btnText = 'Confirm', btnColor = '#dc2626') {

        const titleEl = document.getElementById('detailConfirmModalTitle');
        const msgEl = document.getElementById('detailConfirmModalMessage');
        const btn = document.getElementById('btnExecuteDetailConfirm');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.textContent = msg;

        if (btn) {
            btn.innerText = btnText;
            btn.style.backgroundColor = btnColor;
        }

        pendingConfirmAction = onConfirm;

        const modalEl = document.getElementById('detailConfirmMinimalModal');

        if (modalEl) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    // ==========================================
    // CONFIRM BUTTON
    // ==========================================
    document.getElementById('btnExecuteDetailConfirm')?.addEventListener('click', function() {

        const modalEl = document.getElementById('detailConfirmMinimalModal');

        if (modalEl) {
            bootstrap.Modal.getInstance(modalEl)?.hide();
        }

        if (typeof pendingConfirmAction === 'function') {

            const action = pendingConfirmAction;
            pendingConfirmAction = null;

            action();
        }
    });

    // ==========================================
    // BLOCK DURATION TOGGLE
    // ==========================================
    if (blockType && durWrap) {

        blockType.addEventListener('change', function() {

            durWrap.style.display = this.value === 'permanent' ? 'none' : 'block';

        });
    }

    // ==========================================
    // BLOCK IP ACTION
    // ==========================================
    const blockForm = document.getElementById('formExecuteBlockIp');

    if (blockForm) {

        blockForm.addEventListener('submit', function(e) {

            e.preventDefault();

            const btn = document.getElementById('btn-submit-detail-block');

            if (!btn || btn.disabled) return;

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

                if (!r.ok) {
                    throw new Error(data.message || 'The server could not complete the request.');
                }

                return data;

            })
            .then(res => {

                const modalEl = document.getElementById('modalBlockIpAction');

                if (modalEl) {
                    bootstrap.Modal.getInstance(modalEl)?.hide();
                }

                showAppNotice(
                    res.message || (
                        res.success
                            ? 'This IP has been blocked successfully.'
                            : 'Unable to block this IP. Please try again.'
                    ),
                    res.success ? 'Block Enforced' : 'Block Failed'
                );

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

                showAppNotice(
                    'Request failed. Check network connection.',
                    'Error'
                );

            });

        });
    }

    // ==========================================
    // UNBLOCK IP ACTION
    // ==========================================
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

                        body: JSON.stringify({
                            ip_address: ip,
                            note: 'Unblocked via IP Details Page'
                        })

                    })
                    .then(async r => {

                        const data = await r.json();

                        if (!r.ok) {
                            throw new Error(data.message || 'The server could not complete the request.');
                        }

                        return data;

                    })
                    .then(res => {

                        showAppNotice(
                            res.message || (
                                res.success
                                    ? 'The IP restriction has been lifted.'
                                    : 'Unable to lift this restriction. Please try again.'
                            ),
                            res.success ? 'Restriction Lifted' : 'Action Failed'
                        );

                        if (res.success) {

                            setTimeout(() => location.reload(), 900);

                        } else {

                            unblockBtn.disabled = false;

                        }

                    })
                    .catch(() => {

                        unblockBtn.disabled = false;

                        showAppNotice(
                            'Failed to communicate with firewall service.',
                            'Error'
                        );

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
