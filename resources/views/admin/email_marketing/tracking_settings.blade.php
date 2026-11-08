
@extends('layouts.admin')

@section('title', 'Tracking & Privacy Settings - Email Marketing')

@php
    $settings = $settings ?? ($trackingSettings ?? []);

    // Safe dynamic values with strict fallbacks
    $trackOpens     = (bool) (data_get($settings, 'track_opens') ?? true);
    $trackClicks    = (bool) (data_get($settings, 'track_clicks') ?? true);
    $logIpAddresses = (bool) (data_get($settings, 'log_ip_addresses') ?? true);
    $autoSuppress   = (bool) (data_get($settings, 'auto_suppress_on_unsubscribe') ?? true);
    $footerNotice   = data_get($settings, 'unsubscribe_footer_notice') ?? 'You are receiving this communication because you placed an order or subscribed at {shop_url}. You can unsubscribe at any time.';

    $generalSettingsRoute = Route::has('admin.email_marketing.settings') 
        ? route('admin.email_marketing.settings') 
        : url('admin/email-marketing/settings');

    $trackingUpdateRoute = Route::has('admin.email_marketing.tracking_settings.update') 
        ? route('admin.email_marketing.tracking_settings.update') 
        : url('admin/email-marketing/tracking-settings/update');
@endphp

@section('admin_content')

<style>
    /* =========================================================
       KAIADMIN LITE - WHITE CLASSIC
       Email Marketing / Tracking & Privacy Settings
    ========================================================= */

    #email-marketing-tracking-settings-root {
        --tracking-primary: #1572e8;
        --tracking-primary-rgb: 21, 114, 232;
        --tracking-success: #18a979;
        --tracking-info: #31a5c8;
        --tracking-warning: #e9a23b;
        --tracking-danger: #e65353;
        --tracking-text: #344054;
        --tracking-heading: #1d2939;
        --tracking-muted: #7b8494;
        --tracking-border: #e6eaf0;
        --tracking-surface: #ffffff;
        --tracking-soft-bg: #f7f9fc;
        --tracking-radius: 5px;

        width: 100%;
        max-width: 100%;
        min-width: 0;
        color: var(--tracking-text);
        font-size: 13px;
        overflow-x: clip;
    }

    #email-marketing-tracking-settings-root *,
    .tracking-modal-scope * {
        box-sizing: border-box;
    }

    /* Main Header */
    #email-marketing-tracking-settings-root .tracking-header-wrap {
        gap: 14px;
        margin-bottom: 20px !important;
    }

    #email-marketing-tracking-settings-root .tracking-page-title {
        color: var(--tracking-heading);
        font-size: 19px;
        font-weight: 700;
        letter-spacing: -0.35px;
        line-height: 1.4;
        margin: 0 0 4px;
    }

    #email-marketing-tracking-settings-root .tracking-page-subtitle {
        color: var(--tracking-muted);
        font-size: 12px;
        line-height: 1.6;
        margin: 0;
    }

    #email-marketing-tracking-settings-root .top-action-btns {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    /* Common Elements */
    #email-marketing-tracking-settings-root .btn,
    #email-marketing-tracking-settings-root .form-control,
    #email-marketing-tracking-settings-root .form-select,
    #email-marketing-tracking-settings-root .input-group-text,
    #email-marketing-tracking-settings-root .badge,
    #email-marketing-tracking-settings-root .alert,
    #email-marketing-tracking-settings-root .card,
    #email-marketing-tracking-settings-root .setting-switch-card,
    #email-marketing-tracking-settings-root .endpoint-box,
    #email-marketing-tracking-settings-root .tag-chip,
    #email-marketing-tracking-settings-root .collapse-toggle-btn,
    .tracking-modal-scope .modal-content,
    .tracking-modal-scope .btn,
    .admin-tracking-toast {
        border-radius: var(--tracking-radius) !important;
    }

    #email-marketing-tracking-settings-root .btn {
        font-size: 12px;
        font-weight: 600;
        min-height: 34px;
        padding: 7px 12px;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease, box-shadow .18s ease;
    }

    #email-marketing-tracking-settings-root .btn-primary {
        background-color: var(--tracking-primary);
        border-color: var(--tracking-primary);
    }

    #email-marketing-tracking-settings-root .btn-primary:hover {
        background-color: #0d63ce;
        border-color: #0d63ce;
        box-shadow: 0 3px 8px rgba(var(--tracking-primary-rgb), .16);
    }

    #email-marketing-tracking-settings-root .btn-outline-secondary {
        color: #596579;
        border-color: #d7dde6;
        background: #fff;
    }

    #email-marketing-tracking-settings-root .btn-outline-secondary:hover {
        color: var(--tracking-primary);
        background: #f4f8ff;
        border-color: #b9d3f7;
    }

    #email-marketing-tracking-settings-root .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        white-space: nowrap;
        font-size: 11.5px;
        line-height: 1.35;
        font-weight: 600;
    }

    #email-marketing-tracking-settings-root .text-muted {
        color: var(--tracking-muted) !important;
    }

    #email-marketing-tracking-settings-root .text-dark {
        color: var(--tracking-heading) !important;
    }

    /* Alerts */
    #email-marketing-tracking-settings-root .alert {
        padding: 11px 14px !important;
        border: 1px solid transparent !important;
        box-shadow: none !important;
        font-size: 12px;
    }

    #email-marketing-tracking-settings-root .alert-success {
        background: #f0faf6;
        border-color: #d4f1e3 !important;
    }

    #email-marketing-tracking-settings-root .alert-danger {
        background: #fff5f5;
        border-color: #f9dada !important;
    }

    /* Main Cards */
    #email-marketing-tracking-settings-root .card {
        background: var(--tracking-surface);
        border: 1px solid var(--tracking-border) !important;
        box-shadow: 0 1px 3px rgba(16, 24, 40, .035) !important;
        margin-bottom: 16px;
        overflow: hidden;
    }

    #email-marketing-tracking-settings-root .section-header-wrap {
        min-height: 47px;
        padding: 11px 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: #fff;
        border-bottom: 1px solid var(--tracking-border);
    }

    #email-marketing-tracking-settings-root .section-title {
        color: var(--tracking-heading);
        font-size: 12.5px;
        font-weight: 700;
        line-height: 1.5;
        margin: 0;
    }

    #email-marketing-tracking-settings-root .section-title i {
        margin-right: 5px;
    }

    #email-marketing-tracking-settings-root .collapse-toggle-btn {
        width: 28px;
        height: 28px;
        min-width: 28px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        background: #fff;
        border: 1px solid #e1e6ed;
        transition: all .2s ease;
    }

    #email-marketing-tracking-settings-root .collapse-toggle-btn:hover {
        color: var(--tracking-primary);
        background: #f2f7ff;
        border-color: #c9dcf8;
    }

    #email-marketing-tracking-settings-root .collapse-icon {
        font-size: 10px;
        transition: transform .25s ease;
    }

    #email-marketing-tracking-settings-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* KPI Cards */
    #email-marketing-tracking-settings-root .metric-card {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        min-height: 105px;
        padding: 14px;
        background: #fff;
        border: 1px solid var(--tracking-border) !important;
        border-left: 3px solid var(--tracking-primary) !important;
        box-shadow: none !important;
        transition: border-color .18s ease, transform .18s ease;
    }

    #email-marketing-tracking-settings-root .metric-card:hover {
        transform: translateY(-2px);
        border-color: #cbd9eb !important;
    }

    #email-marketing-tracking-settings-root .metric-card-blue {
        border-left-color: var(--tracking-primary) !important;
    }

    #email-marketing-tracking-settings-root .metric-card-green {
        border-left-color: var(--tracking-success) !important;
    }

    #email-marketing-tracking-settings-root .metric-card-amber {
        border-left-color: var(--tracking-warning) !important;
    }

    #email-marketing-tracking-settings-root .metric-card-cyan {
        border-left-color: var(--tracking-info) !important;
    }

    #email-marketing-tracking-settings-root .stat-label-title {
        color: var(--tracking-muted);
        font-size: 11px;
        font-weight: 600;
        line-height: 1.4;
    }

    #email-marketing-tracking-settings-root .stat-val-num {
        color: var(--tracking-heading);
        font-size: 17px;
        font-weight: 700;
        letter-spacing: -.35px;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    #email-marketing-tracking-settings-root .stat-sub-text {
        color: var(--tracking-muted);
        font-size: 10px;
        line-height: 1.5;
        margin-top: 3px;
    }

    #email-marketing-tracking-settings-root .metric-icon-square {
        width: 33px;
        height: 33px;
        min-width: 33px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px !important;
        font-size: 13px;
    }

    #email-marketing-tracking-settings-root .metric-card-blue .metric-icon-square {
        color: var(--tracking-primary);
        background: #edf4ff;
    }

    #email-marketing-tracking-settings-root .metric-card-green .metric-icon-square {
        color: var(--tracking-success);
        background: #eaf8f1;
    }

    #email-marketing-tracking-settings-root .metric-card-cyan .metric-icon-square {
        color: var(--tracking-info);
        background: #eaf8fb;
    }

    #email-marketing-tracking-settings-root .metric-card-amber .metric-icon-square {
        color: var(--tracking-warning);
        background: #fff6e8;
    }

    /* Settings Switch Cards */
    #email-marketing-tracking-settings-root .setting-switch-card {
        width: 100%;
        padding: 15px;
        background: #fff;
        border: 1px solid var(--tracking-border);
        transition: border-color .18s ease, background-color .18s ease;
    }

    #email-marketing-tracking-settings-root .setting-switch-card:hover {
        background: #fbfcfe;
        border-color: #cbd8e8;
    }

    #email-marketing-tracking-settings-root .setting-switch-card strong {
        font-size: 12.5px;
        line-height: 1.5;
    }

    #email-marketing-tracking-settings-root .setting-switch-card small {
        color: var(--tracking-muted);
        display: block;
        font-size: 11px;
        line-height: 1.6;
        margin-top: 3px;
    }

    #email-marketing-tracking-settings-root .setting-switch-card .form-check {
        padding-left: 0;
    }

    #email-marketing-tracking-settings-root .setting-switch-card .form-check-input {
        width: 38px;
        height: 21px;
        margin: 2px 0 0;
        cursor: pointer;
        border-color: #cbd5e1;
        box-shadow: none;
    }

    #email-marketing-tracking-settings-root .setting-switch-card .form-check-input:checked {
        background-color: var(--tracking-primary);
        border-color: var(--tracking-primary);
    }

    #email-marketing-tracking-settings-root .setting-switch-card .form-check-input:focus {
        border-color: var(--tracking-primary);
        box-shadow: 0 0 0 3px rgba(var(--tracking-primary-rgb), .12);
    }

    #email-marketing-tracking-settings-root .setting-icon {
        width: 36px;
        height: 36px;
        min-width: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px !important;
        font-size: 13px;
    }

    /* Form Controls */
    #email-marketing-tracking-settings-root .form-label {
        display: block;
        color: var(--tracking-text);
        font-size: 11.5px;
        font-weight: 700;
        line-height: 1.5;
        margin-bottom: 7px;
    }

    #email-marketing-tracking-settings-root .form-control,
    #email-marketing-tracking-settings-root .form-select {
        width: 100%;
        min-height: 37px;
        padding: 8px 11px;
        color: var(--tracking-heading);
        background-color: #fff;
        border: 1px solid #dce2ea;
        border-radius: 5px !important;
        box-shadow: none;
        font-size: 12px;
        line-height: 1.5;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    #email-marketing-tracking-settings-root textarea.form-control {
        min-height: 95px;
        resize: vertical;
        line-height: 1.65;
    }

    #email-marketing-tracking-settings-root .form-control:focus,
    #email-marketing-tracking-settings-root .form-select:focus {
        border-color: var(--tracking-primary);
        box-shadow: 0 0 0 3px rgba(var(--tracking-primary-rgb), .10);
        outline: none;
    }

    #email-marketing-tracking-settings-root .form-control::placeholder {
        color: #a0a8b5;
    }

    /* Template Tags */
    #email-marketing-tracking-settings-root .tag-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 8px;
        color: #52647c;
        background: #f4f7fb;
        border: 1px solid #e1e7ef;
        font-size: 10px;
        font-weight: 600;
        line-height: 1.3;
        cursor: pointer;
        user-select: none;
        transition: all .18s ease;
    }

    #email-marketing-tracking-settings-root .tag-chip:hover {
        color: var(--tracking-primary);
        background: #edf4ff;
        border-color: #c9dcf8;
    }

    /* Endpoint Boxes */
    #email-marketing-tracking-settings-root .endpoint-box {
        min-width: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px;
        background: #f8fafc;
        border: 1px solid var(--tracking-border);
    }

    #email-marketing-tracking-settings-root .endpoint-box > div {
        min-width: 0;
        overflow-wrap: anywhere;
        line-height: 1.6;
    }

    #email-marketing-tracking-settings-root .endpoint-copy-btn {
        width: 30px;
        height: 30px;
        min-width: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        color: #64748b;
        background: #fff;
        border: 1px solid #e1e6ed;
        border-radius: 5px !important;
    }

    #email-marketing-tracking-settings-root .endpoint-copy-btn:hover {
        color: var(--tracking-primary);
        background: #edf4ff;
        border-color: #c9dcf8;
    }

    /* Informational Alert */
    #email-marketing-tracking-settings-root .pipeline-alert {
        padding: 11px !important;
        color: #42677c;
        background: #f0f8fc;
        border: 1px solid #d9edf6 !important;
        font-size: 10.5px;
        line-height: 1.6;
    }

    /* Right Column Information */
    #email-marketing-tracking-settings-root .info-copy {
        color: var(--tracking-muted);
        font-size: 11.5px;
        line-height: 1.7;
        margin-bottom: 10px;
    }

    #email-marketing-tracking-settings-root .info-copy:last-child {
        margin-bottom: 0;
    }

    /* Toast */
    #custom-toast-holder {
        position: relative;
        z-index: 108000;
    }

    .admin-tracking-toast {
        position: fixed;
        right: 20px;
        bottom: 20px;
        z-index: 108000;
        width: max-content;
        max-width: min(380px, calc(100vw - 32px));
        min-width: 260px;
        box-shadow: 0 8px 25px rgba(15, 23, 42, .16);
        animation: trackingToastIn .25s ease;
    }

    @keyframes trackingToastIn {
        from {
            transform: translateX(25px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    /* Responsive */
    @media (max-width: 1199.98px) {
        #email-marketing-tracking-settings-root .metric-card {
            min-height: 100px;
        }
    }

    @media (max-width: 767.98px) {
        #email-marketing-tracking-settings-root {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        #email-marketing-tracking-settings-root .tracking-header-wrap {
            align-items: stretch !important;
            margin-bottom: 15px !important;
        }

        #email-marketing-tracking-settings-root .tracking-page-title {
            font-size: 16px;
        }

        #email-marketing-tracking-settings-root .tracking-page-subtitle {
            font-size: 11px;
        }

        #email-marketing-tracking-settings-root .top-action-btns {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
            gap: 7px;
        }

        #email-marketing-tracking-settings-root .top-action-btns .btn {
            width: 100%;
            min-width: 0;
            min-height: 37px;
            padding: 7px 5px;
            font-size: 10.5px;
            white-space: normal;
        }

        #email-marketing-tracking-settings-root .section-header-wrap {
            min-height: 44px;
            padding: 10px 12px;
        }

        #email-marketing-tracking-settings-root .section-title {
            font-size: 11.5px;
        }

        #email-marketing-tracking-settings-root .metric-card {
            min-height: 94px;
            padding: 11px;
        }

        #email-marketing-tracking-settings-root .stat-label-title {
            font-size: 10px;
        }

        #email-marketing-tracking-settings-root .stat-val-num {
            font-size: 14px;
        }

        #email-marketing-tracking-settings-root .stat-sub-text {
            font-size: 9px;
        }

        #email-marketing-tracking-settings-root .metric-icon-square {
            width: 28px;
            height: 28px;
            min-width: 28px;
            font-size: 11px;
        }

        #email-marketing-tracking-settings-root .setting-switch-card {
            padding: 12px;
        }

        #email-marketing-tracking-settings-root .setting-switch-card strong {
            font-size: 11.5px;
        }

        #email-marketing-tracking-settings-root .setting-switch-card small {
            font-size: 10px;
        }

        #email-marketing-tracking-settings-root .setting-icon {
            width: 32px;
            height: 32px;
            min-width: 32px;
        }

        #email-marketing-tracking-settings-root .card {
            margin-bottom: 12px;
        }

        #email-marketing-tracking-settings-root .p-3 {
            padding: 12px !important;
        }
    }

    @media (max-width: 380px) {
        #email-marketing-tracking-settings-root .top-action-btns {
            grid-template-columns: 1fr;
        }

        #email-marketing-tracking-settings-root .setting-switch-card > .d-flex {
            gap: 8px !important;
        }

        #email-marketing-tracking-settings-root .tag-chip {
            font-size: 9px;
            padding: 4px 6px;
        }

        #email-marketing-tracking-settings-root .stat-val-num {
            font-size: 12px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #email-marketing-tracking-settings-root *,
        .admin-tracking-toast {
            animation-duration: .01ms !important;
            transition-duration: .01ms !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-tracking-settings-root">

    {{-- 1. Header & Quick Actions Toolbar --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center tracking-header-wrap">
        <div class="min-w-0">
            <h5 class="tracking-page-title d-flex align-items-center gap-2">
                <i class="fas fa-chart-pie text-primary"></i>
                <span>Email Tracking &amp; Privacy Settings</span>
            </h5>
            <p class="tracking-page-subtitle">
                Configure email engagement tracking, privacy controls and unsubscribe compliance.
            </p>
        </div>

        <div class="top-action-btns">
            <a href="{{ $generalSettingsRoute }}" class="btn btn-sm btn-outline-secondary btn-compact">
                <i class="fas fa-arrow-left"></i>
                <span>General Settings</span>
            </a>

            <button type="button" class="btn btn-sm btn-primary btn-compact" id="btn-save-tracking-settings" onclick="submitTrackingForm()">
                <i class="fas fa-save"></i>
                <span>Save Settings</span>
            </button>
        </div>
    </div>

    {{-- 2. Feedback Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 3. Telemetry KPI Overview --}}
    <div class="card mb-3">
        <div class="section-header-wrap">
            <span class="section-title">
                <i class="fas fa-shield-halved text-primary"></i>
                Engagement Telemetry Overview
            </span>

            <button type="button" class="collapse-toggle-btn no-print" data-bs-toggle="collapse" data-bs-target="#collapseTrackingKpis" aria-expanded="true" aria-controls="collapseTrackingKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseTrackingKpis">
            <div class="p-3">
                <div class="row g-3">

                    {{-- Open Tracking Pixel --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">Open Tracking</span>
                                <div class="metric-icon-square">
                                    <i class="fas fa-envelope-open"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="my-0 stat-val-num font-monospace">1x1 GIF</h4>
                                <small class="d-block stat-sub-text">Invisible tracking pixel</small>
                            </div>
                        </div>
                    </div>

                    {{-- Link Click Redirection --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">Click Tracking</span>
                                <div class="metric-icon-square">
                                    <i class="fas fa-mouse-pointer"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="my-0 stat-val-num font-monospace text-success">302 Redirect</h4>
                                <small class="d-block stat-sub-text">Tracked link redirection</small>
                            </div>
                        </div>
                    </div>

                    {{-- Device Telemetry --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-cyan h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">Device Telemetry</span>
                                <div class="metric-icon-square">
                                    <i class="fas fa-fingerprint"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="my-0 stat-val-num font-monospace" style="font-size: 15px;">IP &amp; Agent</h4>
                                <small class="d-block stat-sub-text">Device and browser data</small>
                            </div>
                        </div>
                    </div>

                    {{-- Compliance Engine --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="stat-label-title text-truncate">Opt-Out Sync</span>
                                <div class="metric-icon-square">
                                    <i class="fas fa-user-slash"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="my-0 stat-val-num font-monospace" style="font-size: 15px;">Auto-Suppress</h4>
                                <small class="d-block stat-sub-text">Unsubscribe enforcement</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- 4. Main Configuration Form --}}
    <form action="{{ $trackingUpdateRoute }}" method="POST" id="trackingSettingsForm">
        @csrf

        <div class="row g-3">

            {{-- Left Column: Tracking Parameters & Compliance --}}
            <div class="col-12 col-xl-8">

                {{-- Card 1: Engagement Telemetry --}}
                <div class="card mb-3">
                    <div class="section-header-wrap">
                        <span class="section-title">
                            <i class="fas fa-bullseye text-primary"></i>
                            1. Engagement Tracking &amp; Pixel Settings
                        </span>

                        <button type="button" class="collapse-toggle-btn no-print" data-bs-toggle="collapse" data-bs-target="#collapseTelemetrySwitches" aria-expanded="true" aria-controls="collapseTelemetrySwitches" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>

                    <div class="collapse show" id="collapseTelemetrySwitches">
                        <div class="p-3">
                            <div class="d-flex flex-column gap-3">

                                {{-- Switch 1: Track Opens --}}
                                <div class="setting-switch-card">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div class="d-flex align-items-start gap-2">
                                            <div class="setting-icon bg-primary-subtle text-primary flex-shrink-0">
                                                <i class="fas fa-envelope-open"></i>
                                            </div>

                                            <div class="min-w-0">
                                                <strong class="text-dark d-block">Enable Email Open Tracking</strong>
                                                <small>
                                                    Uses an invisible 1x1 tracking pixel to record when a recipient opens a campaign email. Some email providers may preload images, so open data may not always represent a direct human open.
                                                </small>
                                            </div>
                                        </div>

                                        <div class="form-check form-switch m-0 pt-1 flex-shrink-0">
                                            <input class="form-check-input" type="checkbox" name="track_opens" id="trackOpensInput" value="1" {{ $trackOpens ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>

                                {{-- Switch 2: Track Clicks --}}
                                <div class="setting-switch-card">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div class="d-flex align-items-start gap-2">
                                            <div class="setting-icon bg-success-subtle text-success flex-shrink-0">
                                                <i class="fas fa-mouse-pointer"></i>
                                            </div>

                                            <div class="min-w-0">
                                                <strong class="text-dark d-block">Enable Link Click Tracking</strong>
                                                <small>
                                                    Uses tracked redirect links to record campaign link clicks and help measure engagement with your store content.
                                                </small>
                                            </div>
                                        </div>

                                        <div class="form-check form-switch m-0 pt-1 flex-shrink-0">
                                            <input class="form-check-input" type="checkbox" name="track_clicks" id="trackClicksInput" value="1" {{ $trackClicks ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>

                                {{-- Switch 3: Log IP & User Agent --}}
                                <div class="setting-switch-card">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div class="d-flex align-items-start gap-2">
                                            <div class="setting-icon bg-info-subtle text-info flex-shrink-0">
                                                <i class="fas fa-fingerprint"></i>
                                            </div>

                                            <div class="min-w-0">
                                                <strong class="text-dark d-block">Log IP Address &amp; User-Agent</strong>
                                                <small>
                                                    Records recipient network and browser information to support security reviews and help identify automated tracking activity. Ensure collection follows your privacy policy and applicable laws.
                                                </small>
                                            </div>
                                        </div>

                                        <div class="form-check form-switch m-0 pt-1 flex-shrink-0">
                                            <input class="form-check-input" type="checkbox" name="log_ip_addresses" id="logIpInput" value="1" {{ $logIpAddresses ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Legal Compliance & Unsubscribe --}}
                <div class="card mb-3">
                    <div class="section-header-wrap">
                        <span class="section-title">
                            <i class="fas fa-scale-balanced text-warning"></i>
                            2. Consent &amp; Unsubscribe Compliance
                        </span>

                        <button type="button" class="collapse-toggle-btn no-print" data-bs-toggle="collapse" data-bs-target="#collapseConsentSettings" aria-expanded="true" aria-controls="collapseConsentSettings" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>

                    <div class="collapse show" id="collapseConsentSettings">
                        <div class="p-3">

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                    <label class="form-label mb-0" for="unsubscribe_footer_notice">
                                        Unsubscribe Footer Notice <span class="text-danger">*</span>
                                    </label>

                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="tag-chip font-monospace" onclick="insertNoticeTag('{shop_url}')" title="Insert Shop URL">+{shop_url}</span>
                                        <span class="tag-chip font-monospace" onclick="insertNoticeTag('{unsubscribe_url}')" title="Insert Unsubscribe URL">+{unsubscribe_url}</span>
                                        <span class="tag-chip font-monospace" onclick="insertNoticeTag('{company_name}')" title="Insert Company Name">+{company_name}</span>
                                    </div>
                                </div>

                                <textarea name="unsubscribe_footer_notice" id="unsubscribe_footer_notice" rows="3" class="form-control font-monospace" style="font-size: 11.5px; line-height: 1.5;" required>{{ $footerNotice }}</textarea>

                                <small class="text-muted d-block mt-2" style="font-size: 10.5px; line-height: 1.6;">
                                    This notice is included in marketing email footers. Keep your unsubscribe instructions clear and aligned with applicable email marketing regulations.
                                </small>
                            </div>

                            <div class="setting-switch-card">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="setting-icon bg-danger-subtle text-danger flex-shrink-0">
                                            <i class="fas fa-ban"></i>
                                        </div>

                                        <div class="min-w-0">
                                            <strong class="text-dark d-block">Automatically Suppress Unsubscribed Recipients</strong>
                                            <small>
                                                Automatically adds recipients to the suppression list after an unsubscribe request, preventing them from receiving future marketing campaigns and automated emails.
                                            </small>
                                        </div>
                                    </div>

                                    <div class="form-check form-switch m-0 pt-1 flex-shrink-0">
                                        <input class="form-check-input" type="checkbox" name="auto_suppress_on_unsubscribe" id="autoSuppressInput" value="1" {{ $autoSuppress ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column: Telemetry Endpoints & Operational Insights --}}
            <div class="col-12 col-xl-4">

                {{-- Endpoints Card --}}
                <div class="card mb-3">
                    <div class="section-header-wrap">
                        <span class="section-title">
                            <i class="fas fa-link text-primary"></i>
                            Tracking Endpoints
                        </span>
                    </div>

                    <div class="p-3">
                        <p class="info-copy">
                            The following endpoints are used by your email tracking system:
                        </p>

                        <div class="d-flex flex-column gap-2 mb-3">

                            <div class="endpoint-box">
                                <div class="font-monospace" style="font-size: 10.5px;" id="endpointOpenUrl">
                                    <span class="text-success fw-bold">GET</span> /email/track/open/{token}
                                </div>

                                <button type="button" class="endpoint-copy-btn flex-shrink-0" onclick="copyEndpointText(this, '/email/track/open/{token}')" title="Copy Route" aria-label="Copy open tracking endpoint">
                                    <i class="fas fa-copy" style="font-size: 11px;"></i>
                                </button>
                            </div>

                            <div class="endpoint-box">
                                <div class="font-monospace" style="font-size: 10.5px;" id="endpointClickUrl">
                                    <span class="text-primary fw-bold">GET</span> /email/track/click/{token}
                                </div>

                                <button type="button" class="endpoint-copy-btn flex-shrink-0" onclick="copyEndpointText(this, '/email/track/click/{token}')" title="Copy Route" aria-label="Copy click tracking endpoint">
                                    <i class="fas fa-copy" style="font-size: 11px;"></i>
                                </button>
                            </div>

                        </div>

                        <div class="alert alert-info pipeline-alert mb-0 d-flex align-items-start gap-2">
                            <i class="fas fa-bolt text-info fs-5 flex-shrink-0"></i>
                            <div>
                                <strong>Tracking Pipeline</strong>
                                <div>
                                    Open tracking returns a transparent 1x1 GIF, while click tracking uses a redirect response. Actual response times depend on server and network conditions.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Privacy Best Practice Note --}}
                <div class="card mb-3">
                    <div class="section-header-wrap">
                        <span class="section-title">
                            <i class="fas fa-circle-info text-info"></i>
                            Privacy &amp; Tracking Notes
                        </span>
                    </div>

                    <div class="p-3">
                        <p class="info-copy">
                            <i class="fas fa-shield-halved text-primary me-1"></i>
                            <strong class="text-dark">Apple Mail Privacy Protection:</strong>
                            Some email clients preload tracking images through proxy servers. As a result, open tracking may not accurately reflect individual user activity.
                        </p>

                        <p class="info-copy">
                            <i class="fas fa-envelope-circle-check text-success me-1"></i>
                            <strong class="text-dark">Email Deliverability:</strong>
                            Provide clear unsubscribe instructions and promptly honor opt-out requests to maintain responsible email marketing practices.
                        </p>

                        <p class="info-copy">
                            <i class="fas fa-user-lock text-info me-1"></i>
                            <strong class="text-dark">Data Privacy:</strong>
                            Collect only the information necessary for your business purposes and apply suitable access controls and retention policies.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

{{-- Floating Toast Notification Holder --}}
<div id="custom-toast-holder"></div>

<script>
    function insertNoticeTag(tag) {
        const textarea = document.getElementById('unsubscribe_footer_notice');
        if (textarea) {
            const start = textarea.selectionStart || 0;
            const end   = textarea.selectionEnd || 0;
            const text  = textarea.value;
            textarea.value = text.substring(0, start) + tag + text.substring(end);
            textarea.focus();
            textarea.selectionStart = textarea.selectionEnd = start + tag.length;
        }
    }

    function copyEndpointText(btn, routeText) {
        const icon = btn ? btn.querySelector('i') : null;
        if (icon) icon.className = 'fas fa-check text-success';

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(routeText).then(() => {
                showToast('Copied', 'Endpoint copied to clipboard.', 'success');
            }).catch(() => {
                fallbackCopy(routeText);
            });
        } else {
            fallbackCopy(routeText);
        }

        setTimeout(() => {
            if (icon) icon.className = 'fas fa-copy';
        }, 1500);
    }

    function fallbackCopy(text) {
        const temp = document.createElement('input');
        temp.value = text;
        document.body.appendChild(temp);
        temp.select();
        try {
            document.execCommand('copy');
            showToast('Copied', 'Endpoint copied to clipboard.', 'success');
        } catch (e) {
            showToast('Notice', 'Please select and copy manually.', 'info');
        }
        document.body.removeChild(temp);
    }

    function submitTrackingForm() {
        const btn = document.getElementById('btn-save-tracking-settings');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
        }
        document.getElementById('trackingSettingsForm').submit();
    }

    function showToast(title, message, type = 'success') {
        const holder = document.getElementById('custom-toast-holder');
        if (!holder) return;

        const toastId = 'toast-' + Date.now();
        const typeConfigs = {
            success: { icon: 'fa-check-circle', color: 'bg-success text-white' },
            danger:  { icon: 'fa-trash-can',    color: 'bg-danger text-white' },
            warning: { icon: 'fa-triangle-exclamation', color: 'bg-warning text-dark' },
            info:    { icon: 'fa-circle-info',  color: 'bg-primary text-white' }
        };
        const config = typeConfigs[type] || typeConfigs.success;

        const html = `
            <div id="${toastId}" class="admin-tracking-toast ${config.color} p-3 d-flex align-items-center justify-content-between shadow-lg" style="border-radius: 5px !important;">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas ${config.icon} fs-5"></i>
                    <div>
                        <strong class="d-block" style="font-size: 13px;">${title}</strong>
                        <small style="opacity: 0.95; font-size: 11px;">${message}</small>
                    </div>
                </div>
                <button type="button" class="btn-close ${type === 'warning' ? '' : 'btn-close-white'} btn-sm ms-2 shadow-none" onclick="document.getElementById('${toastId}')?.remove()"></button>
            </div>
        `;

        holder.insertAdjacentHTML('beforeend', html);
        setTimeout(() => {
            const el = document.getElementById(toastId);
            if (el) el.remove();
        }, 3500);
    }
</script>
@endsection
