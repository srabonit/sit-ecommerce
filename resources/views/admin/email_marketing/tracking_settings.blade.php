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
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-tracking-settings-root .btn,
    #email-marketing-tracking-settings-root .btn-sm,
    #email-marketing-tracking-settings-root button,
    #email-marketing-tracking-settings-root .badge,
    #email-marketing-tracking-settings-root .alert,
    #email-marketing-tracking-settings-root .form-control,
    #email-marketing-tracking-settings-root .form-select,
    #email-marketing-tracking-settings-root .input-group-text,
    #email-marketing-tracking-settings-root .card,
    #email-marketing-tracking-settings-root .metric-card,
    #email-marketing-tracking-settings-root .tag-chip,
    #email-marketing-tracking-settings-root .setting-switch-card,
    #email-marketing-tracking-settings-root .endpoint-box,
    #email-marketing-tracking-settings-root .collapse-toggle-btn,
    .tracking-modal-scope .modal-content,
    .tracking-modal-scope .btn,
    .admin-tracking-toast {
        border-radius: 5px !important;
    }

    #email-marketing-tracking-settings-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #email-marketing-tracking-settings-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Safe Monospace & Bengali Digit Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Compact Sleek Button Styling */
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

    /* Collapsible Trigger Buttons & Rotating Chevron */
    .collapse-toggle-btn {
        width: 24px;
        height: 24px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        color: #475569;
        transition: all 0.2s ease;
    }
    .collapse-toggle-btn:hover {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }
    .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }
    .collapse-icon {
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        font-size: 10px;
    }

    /* Enterprise Surface Cards */
    #email-marketing-tracking-settings-root .card {
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }
    .section-header-wrap {
        padding: 10px 14px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
    }

    /* Metric Cards - Zero Overflow Architecture */
    .metric-card {
        padding: 12px 14px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }
    .stat-label-title {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.02em;
        line-height: 1.2;
    }
    .stat-val-num {
        font-size: 18px;
        line-height: 1.25;
        letter-spacing: -0.02em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .stat-sub-text {
        font-size: 10px;
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
        border-radius: 5px !important;
    }

    .metric-card-blue   { background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%); border-left: 3.5px solid #2563eb !important; }
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-left: 3.5px solid #10b981 !important; }
    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-left: 3.5px solid #f59e0b !important; }
    .metric-card-cyan   { background: linear-gradient(180deg, #ffffff 0%, #ecfeff 100%); border-left: 3.5px solid #06b6d4 !important; }

    /* Interactive Switch Setting Card */
    .setting-switch-card {
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        padding: 12px;
        transition: all 0.15s ease;
    }
    .setting-switch-card:hover {
        border-color: #cbd5e1;
        background-color: #f8fafc;
    }
    .setting-switch-card .form-check-input {
        cursor: pointer;
        width: 36px;
        height: 20px;
        margin-top: 2px;
    }

    /* Tag Chip */
    .tag-chip {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 2px 7px;
        font-size: 10px;
        font-weight: 600;
        color: #475569;
        background-color: #f1f5f9;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        user-select: none;
        transition: all 0.15s ease;
    }
    .tag-chip:hover {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }

    /* Endpoint Box */
    .endpoint-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 8px 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
    }

    /* Form Controls */
    #email-marketing-tracking-settings-root .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #email-marketing-tracking-settings-root .form-control {
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #email-marketing-tracking-settings-root .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Floating Toast Box */
    .admin-tracking-toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 108000;
        min-width: 280px;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.2);
        animation: slideInRight 0.25s ease;
    }
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .tracking-header-wrap h5 {
            font-size: 16px !important;
        }
        .top-action-btns {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .top-action-btns .btn {
            width: 100%;
            padding: 8px 6px !important;
            font-size: 11px !important;
            justify-content: center;
        }
        .metric-card {
            padding: 10px !important;
            min-height: 84px !important;
        }
        .stat-label-title {
            font-size: 10px !important;
        }
        .stat-val-num {
            font-size: 14px !important;
        }
        .stat-sub-text {
            font-size: 9px !important;
        }
        .metric-icon-square {
            width: 28px !important;
            height: 28px !important;
            font-size: 11px !important;
        }
        #email-marketing-tracking-settings-root .card {
            margin-bottom: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-tracking-settings-root">

    {{-- 1. Header & Quick Actions Toolbar --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 tracking-header-wrap">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-chart-pie text-primary"></i>
                <span>Email Tracking &amp; Privacy Settings</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Manage 1x1 transparent tracking pixel injection, link redirect wrappers, and unsubscribe compliance.
            </small>
        </div>

        <div class="d-flex gap-2 top-action-btns">
            <a href="{{ $generalSettingsRoute }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-arrow-left me-1"></i> <span>General Settings</span>
            </a>
            <button type="button" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold" id="btn-save-tracking-settings" onclick="submitTrackingForm()">
                <i class="fas fa-save me-1"></i> <span>Save Tracking Parameters</span>
            </button>
        </div>
    </div>

    {{-- 2. Feedback Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 3. Telemetry KPI Overview (Collapsible Section) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-shield-halved text-primary me-1"></i> Engagement Telemetry &amp; Privacy Infrastructure
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseTrackingKpis" aria-expanded="true" aria-controls="collapseTrackingKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseTrackingKpis">
            <div class="p-3">
                <div class="row g-2">
                    {{-- Open Tracking Pixel --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Open Tracking</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                                    <i class="fas fa-envelope-open"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark font-monospace stat-val-num">
                                    1x1 GIF
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Invisible pixel ping</small>
                            </div>
                        </div>
                    </div>

                    {{-- Link Click Redirection (CTR) --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Click Wrapping</span>
                                <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                                    <i class="fas fa-mouse-pointer"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success font-monospace stat-val-num">
                                    302 Redirect
                                </h4>
                                <small class="text-success d-block text-truncate font-monospace stat-sub-text">Token link wrapper</small>
                            </div>
                        </div>
                    </div>

                    {{-- Forensics & Fraud Audit --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-cyan h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Device Telemetry</span>
                                <div class="metric-icon-square bg-info-subtle text-info shadow-xs">
                                    <i class="fas fa-fingerprint"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark font-monospace stat-val-num" style="font-size: 15px;">
                                    IP &amp; Agent
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Bot / fraud audit trail</small>
                            </div>
                        </div>
                    </div>

                    {{-- Compliance Engine --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Opt-Out Sync</span>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                                    <i class="fas fa-user-slash"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-warning-emphasis font-monospace stat-val-num" style="font-size: 15px;">
                                    Auto-Blacklist
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Suppression enforcement</small>
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
                
                {{-- Card 1: Engagement Telemetry (Collapsible) --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <span class="fw-bold text-dark small" style="font-size: 12px;">
                            <i class="fas fa-bullseye text-primary me-1"></i> 1. Engagement Telemetry &amp; Pixel Injection
                        </span>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseTelemetrySwitches" aria-expanded="true" aria-controls="collapseTelemetrySwitches" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseTelemetrySwitches">
                        <div class="p-3">
                            <div class="d-flex flex-column gap-2">
                                
                                {{-- Switch 1: Track Opens --}}
                                <div class="setting-switch-card">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div class="d-flex align-items-start gap-2.5">
                                            <div class="rounded bg-primary-subtle text-primary p-2 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; border-radius: 5px !important;">
                                                <i class="fas fa-envelope-open" style="font-size: 12px;"></i>
                                            </div>
                                            <div>
                                                <strong class="text-dark d-block" style="font-size: 12.5px;">Inject Invisible 1x1 Open Tracking Pixel</strong>
                                                <small class="text-muted d-block font-monospace" style="font-size: 10.5px; line-height: 1.4;">
                                                    Logs exact timestamp when a customer opens your broadcast email in their mail client (Apple Mail, Gmail, Outlook).
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
                                        <div class="d-flex align-items-start gap-2.5">
                                            <div class="rounded bg-success-subtle text-success p-2 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; border-radius: 5px !important;">
                                                <i class="fas fa-mouse-pointer" style="font-size: 12px;"></i>
                                            </div>
                                            <div>
                                                <strong class="text-dark d-block" style="font-size: 12.5px;">Track Outbound Click-Throughs (CTR)</strong>
                                                <small class="text-muted d-block font-monospace" style="font-size: 10.5px; line-height: 1.4;">
                                                    Rewrites hyperlinks in your broadcast via safe redirection tokens to register verified store visits and sales conversions.
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
                                        <div class="d-flex align-items-start gap-2.5">
                                            <div class="rounded bg-info-subtle text-info p-2 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; border-radius: 5px !important;">
                                                <i class="fas fa-fingerprint" style="font-size: 12px;"></i>
                                            </div>
                                            <div>
                                                <strong class="text-dark d-block" style="font-size: 12.5px;">Log IP Address &amp; Client User-Agent</strong>
                                                <small class="text-muted d-block font-monospace" style="font-size: 10.5px; line-height: 1.4;">
                                                    Records client network and browser environment to accurately isolate automated security scanner pings from real human shoppers.
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

                {{-- Card 2: Legal Compliance & 1-Click Unsubscribe (Collapsible) --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <span class="fw-bold text-dark small" style="font-size: 12px;">
                            <i class="fas fa-scale-balanced text-warning me-1"></i> 2. Consent, Unsubscribe Notice &amp; Legal Compliance
                        </span>
                        <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseConsentSettings" aria-expanded="true" aria-controls="collapseConsentSettings" title="Toggle Section">
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="collapseConsentSettings">
                        <div class="p-3">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-1">
                                    <label class="form-label mb-0" for="unsubscribe_footer_notice">
                                        Mandatory Footer Unsubscribe Notice <span class="text-danger">*</span>
                                    </label>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="tag-chip font-monospace" onclick="insertNoticeTag('{shop_url}')" title="Insert Shop URL">+{shop_url}</span>
                                        <span class="tag-chip font-monospace" onclick="insertNoticeTag('{unsubscribe_url}')" title="Insert Unsubscribe URL">+{unsubscribe_url}</span>
                                        <span class="tag-chip font-monospace" onclick="insertNoticeTag('{company_name}')" title="Insert Company Name">+{company_name}</span>
                                    </div>
                                </div>
                                <textarea name="unsubscribe_footer_notice" id="unsubscribe_footer_notice" rows="3" class="form-control font-monospace" style="font-size: 11.5px; line-height: 1.5;" required>{{ $footerNotice }}</textarea>
                                <small class="text-muted font-monospace d-block mt-1" style="font-size: 10px;">
                                    Required by CAN-SPAM and international mail laws. Included automatically in the footer of all marketing broadcast templates.
                                </small>
                            </div>

                            <div class="setting-switch-card">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div class="d-flex align-items-start gap-2.5">
                                        <div class="rounded bg-danger-subtle text-danger p-2 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; border-radius: 5px !important;">
                                            <i class="fas fa-ban" style="font-size: 12px;"></i>
                                        </div>
                                        <div>
                                            <strong class="text-dark d-block" style="font-size: 12.5px;">Auto-Add to Suppression Blacklist on Opt-Out</strong>
                                            <small class="text-muted d-block font-monospace" style="font-size: 10.5px; line-height: 1.4;">
                                                Instantly suppresses recipients across all future marketing broadcasts and automated drip pipelines once an unsubscribe request is received.
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
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <span class="fw-bold text-dark small" style="font-size: 12px;">
                            <i class="fas fa-link text-primary me-1"></i> Active Telemetry Endpoints
                        </span>
                    </div>
                    <div class="p-3">
                        <p class="text-muted small mb-2" style="font-size: 11px; line-height: 1.45;">
                            Your system exposes these high-speed cached endpoints for real-time tracking:
                        </p>

                        <div class="d-flex flex-column gap-2 mb-3">
                            <div class="endpoint-box">
                                <div class="text-truncate font-monospace" style="font-size: 10.5px;" id="endpointOpenUrl">
                                    <span class="text-success fw-bold">GET</span> /email/track/open/{token}
                                </div>
                                <button type="button" class="btn btn-sm btn-light border p-0 text-secondary shadow-none d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 24px; height: 24px;" onclick="copyEndpointText(this, '/email/track/open/{token}')" title="Copy Route">
                                    <i class="fas fa-copy" style="font-size: 10px;"></i>
                                </button>
                            </div>

                            <div class="endpoint-box">
                                <div class="text-truncate font-monospace" style="font-size: 10.5px;" id="endpointClickUrl">
                                    <span class="text-primary fw-bold">GET</span> /email/track/click/{token}
                                </div>
                                <button type="button" class="btn btn-sm btn-light border p-0 text-secondary shadow-none d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 24px; height: 24px;" onclick="copyEndpointText(this, '/email/track/click/{token}')" title="Copy Route">
                                    <i class="fas fa-copy" style="font-size: 10px;"></i>
                                </button>
                            </div>
                        </div>

                        <div class="alert alert-info border-0 shadow-xs p-2.5 mb-0 small font-monospace d-flex align-items-start gap-2" style="border-radius: 5px; font-size: 10.5px; line-height: 1.4;">
                            <i class="fas fa-bolt text-info fs-5 flex-shrink-0"></i>
                            <div>
                                <strong>Zero-Latency Pipeline:</strong> Routes return a 1x1 transparent GIF or issue a 302 redirect in under <strong>15ms</strong> without stalling the user.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Privacy Best Practice Note --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                        <span class="fw-bold text-dark small" style="font-size: 12px;">
                            <i class="fas fa-circle-info text-info me-1"></i> Privacy &amp; Apple Mail Notice
                        </span>
                    </div>
                    <div class="p-3">
                        <p class="text-muted small mb-2" style="font-size: 11px; line-height: 1.45;">
                            &bull; <strong>Apple Mail Privacy Protection (MPP):</strong> Apple pre-fetches images via proxies. IP logging helps identify proxy traffic vs genuine customer store clicks.
                        </p>
                        <p class="text-muted small mb-0" style="font-size: 11px; line-height: 1.45;">
                            &bull; <strong>Deliverability Protection:</strong> Always keep the mandatory unsubscribe notice active to minimize spam button clicks in Gmail and Yahoo Mail.
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